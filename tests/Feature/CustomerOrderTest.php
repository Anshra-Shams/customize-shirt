<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeAdminUser(): User
    {
        $user = User::where('email', 'test_super_order_admin@example.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Admin',
                'email' => 'test_super_order_admin@example.com',
                'password' => bcrypt('password'),
                'usertype' => 'admin',
            ]);
        }

        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'sales.create']);
        if (!$user->hasPermissionTo('sales.create')) {
            $user->givePermissionTo($perm);
        }

        $permView = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'sales.view']);
        if (!$user->hasPermissionTo('sales.view')) {
            $user->givePermissionTo($permView);
        }

        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'dashboard.view']);
        if (!$user->hasPermissionTo('dashboard.view')) {
            $user->givePermissionTo($perm);
        }

        return $user;
    }

    public function test_guest_is_redirected_from_customer_orders(): void
    {
        $this->get(route('customer_orders.index'))->assertRedirect();
        $this->get(route('customer_orders.create'))->assertRedirect();
    }

    public function test_authenticated_user_can_view_order_pages_and_home_links(): void
    {
        $user = $this->makeAdminUser();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertStatus(200)
            ->assertSee(route('customer_orders.create'))
            ->assertSee(route('customer_orders.index'));

        $this->actingAs($user)
            ->get(route('customer_orders.index'))
            ->assertStatus(200)
            ->assertSee('Customer Orders');

        $this->actingAs($user)
            ->get(route('customer_orders.create'))
            ->assertStatus(200)
            ->assertSee('Take Customer Order');
    }

    public function test_can_create_order_for_existing_customer_without_affecting_sales_stock_or_ledger(): void
    {
        $user = $this->makeAdminUser();

        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'customer_id' => 'CUST-TEST-' . uniqid(),
                'customer_name' => 'Test Customer',
                'mobile' => '03001234567',
                'address' => 'Test Street',
                'opening_balance' => 0,
            ]);
        }

        $product = Product::where('is_active', true)->first();

        $salesBefore = DB::table('sales')->count();
        $movementsBefore = DB::table('stock_movements')->count();
        $journalBefore = DB::table('journal_entries')->count();

        $response = $this->actingAs($user)->post(route('customer_orders.store'), [
            'customer_mode' => 'existing',
            'customer_id' => $customer->id,
            'customer_phone' => '03009998877',
            'customer_address' => 'Main Market Road',
            'order_date' => '2026-11-12',
            'delivery_date' => '2026-11-20',
            'status' => 'pending',
            'description' => 'Urgent customer order, pack neatly',
            'discount' => 100,
            'advance_amount' => 500,
            'product_id' => [$product?->id, null],
            'item_name' => [$product?->item_name ?? 'Catalog Suit', 'Custom Embroidery Shawl'],
            'item_description' => ['Size Large, Blue', 'Handwork border with gold thread'],
            'qty' => [2, 3],
            'unit' => ['Pcs', 'Pcs'],
            'price' => [1500, 1000],
        ]);

        $order = CustomerOrder::latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer_orders.print', $order->id));

        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('2026-11-20', $order->delivery_date->format('Y-m-d'));
        $this->assertEquals(5.00, (float) $order->total_qty);
        $this->assertEquals(6000.00, (float) $order->subtotal);
        $this->assertEquals(100.00, (float) $order->discount);
        $this->assertEquals(5900.00, (float) $order->total_amount);
        $this->assertEquals(500.00, (float) $order->advance_amount);
        $this->assertCount(2, $order->items);

        // Ensure ZERO impact on sales and stock movements, and advance posts balanced journal entries
        $this->assertEquals($salesBefore, DB::table('sales')->count());
        $this->assertEquals($movementsBefore, DB::table('stock_movements')->count());
        $this->assertEquals($journalBefore + 2, DB::table('journal_entries')->count());

        // Verify show, edit, print, status update, full update, and delete
        $this->actingAs($user)
            ->get(route('customer_orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee($order->order_no)
            ->assertSee('Custom Embroidery Shawl');

        $this->actingAs($user)
            ->get(route('customer_orders.print', $order->id))
            ->assertStatus(200)
            ->assertSee('Customer Order Slip');

        $this->actingAs($user)
            ->post(route('customer_orders.status', $order->id), ['status' => 'ready'])
            ->assertRedirect();
        $this->assertEquals('ready', $order->fresh()->status);

        $this->actingAs($user)
            ->put(route('customer_orders.update', $order->id), [
                'order_no' => $order->order_no,
                'customer_mode' => 'walkin',
                'walkin_name' => 'Updated Walkin Client',
                'customer_phone' => '03111112222',
                'order_date' => '2026-11-12',
                'delivery_date' => '2026-11-25',
                'status' => 'in_progress',
                'description' => 'Updated description',
                'discount' => 0,
                'advance_amount' => 0,
                'product_id' => [null],
                'item_name' => ['Updated Custom Item'],
                'item_description' => ['Red color'],
                'qty' => [4],
                'unit' => ['Box'],
                'price' => [500],
            ])
            ->assertRedirect(route('customer_orders.index'));

        $order->refresh();
        $this->assertNull($order->customer_id);
        $this->assertEquals('Updated Walkin Client', $order->walkin_name);
        $this->assertEquals('in_progress', $order->status);
        $this->assertEquals(2000.00, (float) $order->total_amount);
        $this->assertCount(1, $order->items);

        $this->actingAs($user)
            ->delete(route('customer_orders.destroy', $order->id))
            ->assertRedirect(route('customer_orders.index'));

        $this->assertDatabaseMissing('customer_orders', ['id' => $order->id]);
    }

    public function test_order_item_materials_and_custom_fields_save_for_order_only(): void
    {
        $user = $this->makeAdminUser();

        $catalogMaterials = [
            ['name' => 'Fabric', 'amount' => 100],
            ['name' => 'Stitching', 'amount' => 50],
        ];
        $originalColorJson = json_encode([
            [
                'name' => 'Template Shirt',
                'size' => 'M',
                'color' => 'White',
                'stock' => 10,
                'sale_price' => 500,
                'purch_price' => 150,
                'materials' => $catalogMaterials,
            ],
        ]);

        $product = Product::create([
            'creater_id' => $user->id,
            'item_code' => 'ORD-MAT-' . uniqid(),
            'item_name' => 'Order Template Product ' . uniqid(),
            'size_mode' => 'by_pieces',
            'height' => 0,
            'width' => 0,
            'pieces_per_box' => 1,
            'pieces_per_m2' => 0,
            'total_m2' => 0,
            'price_per_m2' => 0,
            'purchase_price_per_m2' => 0,
            'sale_price_per_box' => 500,
            'sale_price_per_piece' => 500,
            'purchase_price_per_piece' => 150,
            'purchase_price_per_box' => 150,
            'is_active' => 1,
            'color' => $originalColorJson,
        ]);

        $customer = Customer::create([
            'customer_id' => 'CUST-MAT-' . uniqid(),
            'customer_name' => 'Mat Test Customer',
            'mobile' => '03009998888',
            'opening_balance' => 0,
        ]);

        // Create page renders clean items grid without materials column or modal
        $this->actingAs($user)
            ->get(route('customer_orders.create'))
            ->assertStatus(200)
            ->assertDontSee('orderMaterialsModal');

        // Submit order with catalog materials + added custom order-only material
        $submittedMaterials = [
            ['name' => 'Fabric', 'amount' => 100, 'is_custom' => false],
            ['name' => 'Stitching', 'amount' => 50, 'is_custom' => false],
            ['name' => 'Extra Golden Lace', 'amount' => 75, 'is_custom' => true],
        ];

        $response = $this->actingAs($user)
            ->post(route('customer_orders.store'), [
                'order_no' => 'ORD-MAT-TEST-' . uniqid(),
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => '2026-11-12',
                'status' => 'pending',
                'product_id' => [$product->id],
                'item_name' => [$product->item_name],
                'item_description' => ['Special lace order'],
                'item_materials' => [json_encode($submittedMaterials)],
                'qty' => [2],
                'unit' => ['Pcs'],
                'price' => [500],
            ]);
        $order = CustomerOrder::latest('id')->first();
        $response->assertRedirect(route('customer_orders.print', $order->id));
        $item = $order->items()->first();

        $this->assertNotNull($item);
        $this->assertCount(3, $item->materials);
        $this->assertFalse($item->materials[0]['is_custom']);
        $this->assertTrue($item->materials[2]['is_custom']);
        $this->assertEquals('Extra Golden Lace', $item->materials[2]['name']);
        $this->assertEquals(75, $item->materials[2]['amount']);

        // Catalog product remains 100% unchanged
        $this->assertEquals($originalColorJson, $product->fresh()->color);

        // Show and Edit render the saved materials & customize marker, while Print hides raw materials
        $this->actingAs($user)
            ->get(route('customer_orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee('Extra Golden Lace')
            ->assertSee('customize');

        $this->actingAs($user)
            ->get(route('customer_orders.print', $order->id))
            ->assertStatus(200)
            ->assertDontSee('Extra Golden Lace')
            ->assertDontSee('Materials:');

        $this->actingAs($user)
            ->get(route('customer_orders.edit', $order->id))
            ->assertStatus(200)
            ->assertDontSee('orderMaterialsModal');

        // Customer orders index uses "In Preparing" and links to confirmation page
        $prepareUrl = route('customer_orders.prepare', $order->id);
        $this->actingAs($user)
            ->get(route('customer_orders.index'))
            ->assertStatus(200)
            ->assertSee('In Preparing')
            ->assertSee($prepareUrl);

        // Confirmation page renders order form pre-set for "In Preparing" with Materials button and modal (without the blue scope note)
        $this->actingAs($user)
            ->get($prepareUrl)
            ->assertStatus(200)
            ->assertSee('Confirm Order Preparation #' . $order->order_no)
            ->assertSee('Mark Ready')
            ->assertSee('orderMaterialsModal')
            ->assertSee('Materials')
            ->assertSee('Raw Material')
            ->assertSee('rawMaterialCategories')
            ->assertDontSee('Changes made here are saved only for this customer order');

        // Confirming preparation updates status to in_progress and redirects to index
        $this->actingAs($user)
            ->put(route('customer_orders.update', $order->id), [
                'order_no' => $order->order_no,
                'confirm_preparing' => 1,
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => '2026-11-12',
                'status' => 'in_progress',
                'product_id' => [$product->id],
                'item_name' => [$product->item_name],
                'item_description' => ['Confirmed for preparation'],
                'item_materials' => [json_encode($submittedMaterials)],
                'qty' => [2],
                'unit' => ['Pcs'],
                'price' => [500],
            ])
            ->assertRedirect(route('customer_orders.index'));

        $this->assertEquals('ready', $order->fresh()->status);
    }

    public function test_advance_and_payment_methods_work_across_create_edit_confirm_show_print(): void
    {
        $user = $this->makeAdminUser();
        $account = \App\Models\Account::whereHas('head', function ($q) {
            $q->whereIn('name', ['Cash', 'Bank']);
        })->where('status', 1)->first();
        if (! $account) {
            $head = \App\Models\AccountHead::firstOrCreate(['name' => 'Cash']);
            $account = \App\Models\Account::create([
                'head_id' => $head->id,
                'title' => 'Cash Counter',
                'status' => 1,
                'opening_balance' => 0,
            ]);
        }

        $customer = Customer::create([
            'customer_id' => 'CUST-PAY-' . uniqid(),
            'customer_name' => 'Pay Test Customer',
            'mobile' => '03007776666',
            'opening_balance' => 0,
        ]);

        // Create page renders Advance Paid, Remaining Balance, Payment Methods
        $this->actingAs($user)
            ->get(route('customer_orders.create'))
            ->assertStatus(200)
            ->assertSee('Advance Paid')
            ->assertSee('Remaining Balance')
            ->assertSee('Payment Methods')
            ->assertSee('Add Account');

        $response = $this->actingAs($user)
            ->post(route('customer_orders.store'), [
                'order_no' => 'ORD-PAY-' . uniqid(),
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => '2026-11-12',
                'status' => 'pending',
                'item_name' => ['Custom Jacket'],
                'qty' => [1],
                'unit' => ['Pcs'],
                'price' => [2000],
                'payment_account_id' => [$account->id, null],
                'payment_amount' => [300, 200],
            ]);
        $order = CustomerOrder::latest('id')->first();
        $response->assertRedirect(route('customer_orders.print', $order->id));
        $this->assertEquals(500.00, (float) $order->advance_amount);
        $this->assertCount(2, $order->payment_methods);
        $this->assertEquals($account->title, $order->payment_methods[0]['account_title']);

        foreach ([
            route('customer_orders.edit', $order->id),
            route('customer_orders.prepare', $order->id),
            route('customer_orders.show', $order->id),
        ] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertStatus(200)
                ->assertSee('Advance Paid')
                ->assertSee('Remaining Balance')
                ->assertSee('Payment Methods')
                ->assertSee($account->title);
        }

        // Print slip displays financial totals (Advance Paid, Remaining Balance) but hides internal bank account titles
        $this->actingAs($user)
            ->get(route('customer_orders.print', $order->id))
            ->assertStatus(200)
            ->assertSee('Advance Paid')
            ->assertSee('Remaining Balance')
            ->assertDontSee('Advance Payment Methods:')
            ->assertDontSee($account->title);
    }

    public function test_order_advance_posts_updates_and_reverses_in_account_ledger(): void
    {
        $user = $this->makeAdminUser();

        $head = \App\Models\AccountHead::first() ?? \App\Models\AccountHead::create(['name' => 'Bank Accounts']);
        $account = \App\Models\Account::create([
            'head_id' => $head->id,
            'account_code' => 'TEST-BANK-' . uniqid(),
            'title' => 'Test Bank Ledger Account',
            'type' => 'Debit',
            'opening_balance' => 100.00,
            'current_balance' => 100.00,
            'status' => 1,
        ]);

        $customer = Customer::create([
            'customer_id' => 'CUST-LEDG-' . uniqid(),
            'customer_name' => 'Ledger Advance Customer',
            'mobile' => '03005554433',
            'opening_balance' => 0,
        ]);

        $salesBefore = DB::table('sales')->count();
        $movementsBefore = DB::table('stock_movements')->count();

        // 1. Create order with Rs 500 advance into $account
        $orderNo = 'ORD-LED-' . uniqid();
        $response = $this->actingAs($user)
            ->post(route('customer_orders.store'), [
                'order_no' => $orderNo,
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => '2026-11-12',
                'status' => 'pending',
                'item_name' => ['Bridal Dress'],
                'qty' => [1],
                'unit' => ['Pcs'],
                'price' => [2500],
                'payment_account_id' => [$account->id],
                'payment_amount' => [500],
            ]);

        $order = CustomerOrder::where('order_no', $orderNo)->firstOrFail();
        $response->assertRedirect(route('customer_orders.print', $order->id));

        $debitEntry = \App\Models\JournalEntry::where('source_type', CustomerOrder::class)
            ->where('source_id', $order->id)
            ->where('account_id', $account->id)
            ->first();

        $this->assertNotNull($debitEntry);
        $this->assertEquals(500.00, (float) $debitEntry->debit);
        $this->assertEquals(0.00, (float) $debitEntry->credit);
        $this->assertEquals(600.00, (float) $account->fresh()->current_balance);

        // Verify /accounts/{id}/ledger renders the order advance entry cleanly
        $this->actingAs($user)
            ->get(route('accounts.ledger', $account->id))
            ->assertStatus(200)
            ->assertSee($orderNo)
            ->assertSee('500.00');

        // 2. Update order advance from 500 -> 800 (should reverse 500 and post 800)
        $this->actingAs($user)
            ->put(route('customer_orders.update', $order->id), [
                'order_no' => $orderNo,
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => '2026-11-12',
                'status' => 'in_progress',
                'item_name' => ['Bridal Dress'],
                'qty' => [1],
                'unit' => ['Pcs'],
                'price' => [2500],
                'payment_account_id' => [$account->id],
                'payment_amount' => [800],
            ])
            ->assertRedirect(route('customer_orders.index'));

        $this->assertEquals(900.00, (float) $account->fresh()->current_balance);
        $entriesForOrder = \App\Models\JournalEntry::where('source_type', CustomerOrder::class)
            ->where('source_id', $order->id)
            ->get();
        $this->assertEquals(800.00, (float) $entriesForOrder->sum('debit'));
        $this->assertEquals(800.00, (float) $entriesForOrder->sum('credit'));

        // 3. Delete order (should reverse advance journal entries and restore balance to 100.00)
        $this->actingAs($user)
            ->delete(route('customer_orders.destroy', $order->id))
            ->assertRedirect(route('customer_orders.index'));

        $this->assertEquals(100.00, (float) $account->fresh()->current_balance);
        $this->assertEquals(0, \App\Models\JournalEntry::where('source_type', CustomerOrder::class)
            ->where('source_id', $order->id)
            ->count());

        // Ensure Sales & Stock remain completely untouched
        $this->assertEquals($salesBefore, DB::table('sales')->count());
        $this->assertEquals($movementsBefore, DB::table('stock_movements')->count());
    }

    public function test_can_view_delivery_page_and_process_delivery_with_settlement(): void
    {
        $user = $this->makeAdminUser();

        $head = \App\Models\AccountHead::first() ?? \App\Models\AccountHead::create(['name' => 'Cash']);
        $account = \App\Models\Account::create([
            'head_id' => $head->id,
            'account_code' => 'DELIV-CASH-' . uniqid(),
            'title' => 'Delivery Cash Counter',
            'type' => 'Debit',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'status' => 1,
        ]);

        $customer = Customer::first() ?? Customer::create([
            'customer_id' => 'CUST-DELIV-' . uniqid(),
            'customer_name' => 'Delivery Client',
            'mobile' => '03331122334',
            'opening_balance' => 0,
        ]);

        $orderNo = 'ORD-DELIV-' . uniqid();
        $response = $this->actingAs($user)->post(route('customer_orders.store'), [
            'order_no' => $orderNo,
            'customer_mode' => 'existing',
            'customer_id' => $customer->id,
            'order_date' => '2026-11-12',
            'delivery_date' => '2026-11-15',
            'status' => 'ready',
            'description' => 'Ready for customer collection',
            'discount' => 200,
            'advance_amount' => 1000,
            'payment_account_id' => [$account->id],
            'payment_amount' => [1000],
            'item_name' => ['Executive 3-Piece Suit'],
            'item_description' => ['Charcoal Grey, Italian Cut'],
            'qty' => [1],
            'unit' => ['Pcs'],
            'price' => [5000],
        ]);

        $order = CustomerOrder::where('order_no', $orderNo)->firstOrFail();
        $response->assertRedirect(route('customer_orders.print', $order->id));
        // Total = 5000 - 200 = 4800. Advance = 1000. Remaining = 3800.
        $this->assertEquals(4800.00, (float) $order->total_amount);
        $this->assertEquals(1000.00, (float) $order->advance_amount);
        $this->assertEquals(3800.00, $order->remaining_balance);

        // 1. Visit the deliver route which redirects to sales.from.order
        $deliverUrl = route('customer_orders.deliver', $order->id);
        $fromOrderUrl = route('sales.from.order', $order->id);

        $this->actingAs($user)
            ->get($deliverUrl)
            ->assertRedirect($fromOrderUrl);

        // 2. Visiting the Sale Delivery page loads the prefilled sale view with order details
        $this->actingAs($user)
            ->get($fromOrderUrl)
            ->assertStatus(200)
            ->assertSee('Delivering Customer Order #' . $orderNo)
            ->assertSee('Executive 3-Piece Suit')
            ->assertSee('3,800.00');

        // Also verify the deliver link appears in index and show
        $this->actingAs($user)
            ->get(route('customer_orders.index'))
            ->assertStatus(200)
            ->assertSee($fromOrderUrl);

        // Verify direct delivery page /sales/from-order without ID renders properly with pending orders dropdown
        $this->actingAs($user)
            ->get(route('sales.from.order'))
            ->assertStatus(200)
            ->assertSee('SELECT UNDELIVERED ORDER')
            ->assertSee('noDeliveryOrderPlaceholder');

        $this->actingAs($user)
            ->get(route('customer_orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee($fromOrderUrl)
            ->assertSee('Deliver Order');

        // 3. Complete delivery by generating/processing sale linked to this order
        $sale = \App\Models\Sale::create([
            'invoice_no' => 'INV-DELIV-' . uniqid(),
            'order_type' => 'counter_sale',
            'customer_id' => $customer->id,
            'total_bill_amount' => 4800,
            'total_net' => 4800,
            'cash' => 3800,
            'status' => 'completed',
        ]);

        $order->status = 'delivered';
        $order->delivered_at = now();
        $order->delivered_by = $user->id;
        $order->received_by = 'Ali Raza (Brother)';
        $order->delivery_notes = 'Delivered via Sale Screen. Invoice #' . $sale->id;
        $order->delivery_payment_amount = 3800;
        $order->sale_id = $sale->id;
        $order->save();

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertTrue($order->is_delivered);
        $this->assertEquals($sale->id, $order->sale_id);
        $this->assertEquals($sale->id, $order->sale->id);
        $this->assertEquals('Ali Raza (Brother)', $order->received_by);

        // 4. Verify show page renders completed delivery information and invoice link
        $invoiceUrl = route('sales.invoice', $sale->id);
        $this->actingAs($user)
            ->get(route('customer_orders.show', $order->id))
            ->assertStatus(200)
            ->assertSee('Ali Raza (Brother)')
            ->assertSee('View Sale Invoice')
            ->assertSee($invoiceUrl);

        // 5. Verify invoice and receipt load without any error
        $this->actingAs($user)
            ->get($invoiceUrl)
            ->assertStatus(200);

        $this->actingAs($user)
            ->get(route('sales.receipt', $sale->id))
            ->assertStatus(200);
    }

    public function test_delivering_order_via_pos_sale_bypasses_insufficient_stock_error(): void
    {
        $user = $this->makeAdminUser();
        $customer = Customer::create([
            'customer_id' => 'CUST-DELIV-' . uniqid(),
            'customer_name' => 'Delivery Test Customer',
            'mobile' => '03001234567',
            'opening_balance' => 0,
        ]);

        $account = \App\Models\Account::where('status', 1)->first();
        if (! $account) {
            $head = \App\Models\AccountHead::firstOrCreate(['name' => 'Cash']);
            $account = \App\Models\Account::create([
                'head_id' => $head->id,
                'title' => 'Cash Counter',
                'status' => 1,
                'opening_balance' => 0,
            ]);
        }

        $warehouse = \App\Models\Warehouse::first() ?: \App\Models\Warehouse::create(['warehouse_name' => 'Main Store']);

        $product = Product::create([
            'item_name' => 'Custom Blazer ' . uniqid(),
            'is_active' => true,
            'is_raw_material' => 0,
            'is_part' => 0,
        ]);

        // Ensure warehouse stock for this product is 0.00
        \App\Models\WarehouseStock::updateOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
            ['quantity' => 0, 'total_pieces' => 0]
        );

        $order = CustomerOrder::create([
            'order_no' => 'ORD-BLZ-' . uniqid(),
            'customer_id' => $customer->id,
            'order_date' => '2026-11-14',
            'delivery_date' => '2026-11-20',
            'status' => 'ready',
            'total_qty' => 1,
            'subtotal' => 3000,
            'total_amount' => 3000,
            'advance_amount' => 500,
            'created_by' => $user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'item_name' => $product->item_name,
            'qty' => 1,
            'unit' => 'Pcs',
            'price' => 3000,
            'total' => 3000,
        ]);

        // Post delivery sale via AJAX/JSON like POS does
        $response = $this->actingAs($user)->postJson(route('sales.store'), [
            'action' => 'post',
            'is_walkin' => 0,
            'customer' => $customer->id,
            'customer_order_id' => $order->id,
            'reference' => 'Customer Order #' . $order->order_no,
            'warehouse_id' => [$warehouse->id],
            'product_id' => [$product->id],
            'qty' => [1],
            'total_pieces' => [1],
            'price' => [3000],
            'sales_amount' => [3000],
            'receipt_account_id' => [$account->id],
            'receipt_amount' => [2500],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->sale_id);
        $this->assertEquals(2500, $order->delivery_payment_amount);

        // Invoice view reflects Delivery title and Back button points to customer orders index
        $this->actingAs($user)
            ->get(route('sales.invoice', $order->sale_id))
            ->assertStatus(200)
            ->assertSee('Delivery Invoice')
            ->assertSee(route('customer_orders.index'));

        // Receipt view reflects ORDER DELIVERY RECEIPT and Back button points to customer orders index
        $this->actingAs($user)
            ->get(route('sales.receipt', $order->sale_id))
            ->assertStatus(200)
            ->assertSee('ORDER DELIVERY RECEIPT')
            ->assertSee(route('customer_orders.index'));
    }

    public function test_preparing_order_with_raw_materials_deducts_warehouse_stock_and_logs_stock_movement(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'customer_id' => 'CUST-STK-' . uniqid(),
            'customer_name' => 'Stock Test Customer ' . uniqid(),
            'mobile' => '03001234567',
            'opening_balance' => 0,
        ]);
        $warehouse = \App\Models\Warehouse::first() ?? \App\Models\Warehouse::create(['warehouse_name' => 'Main Store']);

        // Create a raw material with initial stock
        $rawMaterial = Product::create([
            'item_name' => 'Cotton Fabric ' . uniqid(),
            'item_code' => 'RM-' . rand(1000, 9999),
            'is_raw_material' => 1,
            'is_active' => 1,
            'purchase_price_per_piece' => 100,
        ]);

        \App\Models\WarehouseStock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $rawMaterial->id,
            'total_pieces' => 10,
            'quantity' => 10,
        ]);

        $this->assertEquals(10, \App\Models\WarehouseStock::where('product_id', $rawMaterial->id)->sum('total_pieces'));

        // Create a customer order
        $order = CustomerOrder::create([
            'order_no' => 'ORD-STOCK-' . uniqid(),
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);
        $item = $order->items()->create([
            'item_name' => 'Custom Shirt',
            'qty' => 1,
            'price' => 500,
            'total' => 500,
        ]);

        // Prepare order with 3 units of raw material
        $materials = [
            [
                'name' => $rawMaterial->item_name,
                'product_id' => $rawMaterial->id,
                'qty' => 3,
                'unit' => 'meter',
                'rate' => 100,
                'amount' => 300,
                'is_custom' => false,
            ]
        ];

        $this->actingAs($user)->put(route('customer_orders.update', $order->id), [
            'order_no' => $order->order_no,
            'customer_mode' => 'existing',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => 'in_progress',
            'confirm_preparing' => '1',
            'item_name' => [$item->item_name],
            'product_id' => [null],
            'qty' => [1],
            'unit' => ['Pcs'],
            'price' => [500],
            'item_materials' => [json_encode($materials)],
        ])->assertRedirect(route('customer_orders.index'));

        // Assert stock was deducted: 10 - 3 = 7
        $stockAfter = \App\Models\WarehouseStock::where('product_id', $rawMaterial->id)->sum('total_pieces');
        $this->assertEquals(7, $stockAfter);

        // Assert StockMovement 'out' was recorded
        $movement = \App\Models\StockMovement::where('ref_type', 'CUSTOMER_ORDER')
            ->where('ref_id', $order->id)
            ->where('product_id', $rawMaterial->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(-3, (float)$movement->qty);

        // Assert /report/item-stock-fetch shows consumed quantity under sold
        $stockPerm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'item.stock.report.view']);
        if (!$user->hasPermissionTo('item.stock.report.view')) {
            $user->givePermissionTo($stockPerm);
        }

        $reportRes = $this->actingAs($user)->postJson(route('report.item_stock.fetch'), [
            'category_id' => 'all',
            'product_id' => $rawMaterial->id,
            'warehouse_id' => 'all',
            'unit_type' => 'all',
            'report_mode' => 'ledger',
        ])->assertStatus(200);

        $reportData = $reportRes->json('data');
        $this->assertNotEmpty($reportData);
        $this->assertEquals(3, (float) $reportData[0]['sold']);
        $this->assertEquals(7, (float) $reportData[0]['balance']);

        // Cancel order -> Assert stock is restored
        $this->actingAs($user)->post(route('customer_orders.status', $order->id), [
            'status' => 'cancelled',
        ]);
        $stockRestored = \App\Models\WarehouseStock::where('product_id', $rawMaterial->id)->sum('total_pieces');
        $this->assertEquals(10, $stockRestored);
    }

    public function test_order_delivery_page_auto_loads_first_pending_order_and_completes_delivery(): void
    {
        $user = $this->makeAdminUser();
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'customer_id' => 'CUST-TEST-' . uniqid(),
                'customer_name' => 'Auto Delivery Test Customer',
                'mobile' => '03001234567',
                'opening_balance' => 0,
            ]);
        }

        $order = CustomerOrder::create([
            'order_no' => 'ORD-DELIV-' . uniqid(),
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => 'ready',
            'subtotal' => 1200,
            'total_amount' => 1200,
            'advance_amount' => 200,
            'created_by' => $user->id,
        ]);

        \App\Models\CustomerOrderItem::create([
            'customer_order_id' => $order->id,
            'item_name' => 'Tailored Kurta',
            'qty' => 2,
            'unit' => 'Pcs',
            'price' => 600,
            'total' => 1200,
        ]);

        // 1. Visit /sales/from-order without passing ID in URL -> Should auto-load first pending order
        $response = $this->actingAs($user)->get(route('sales.from.order'));
        $response->assertStatus(200);
        $response->assertSee('Tailored Kurta');
        $response->assertSee($order->order_no);
        $response->assertSee($customer->customer_name);

        // 2. Simulate Save & Complete Delivery submission
        $payload = [
            'action' => 'sale',
            'customer_order_id' => $order->id,
            'order_advance_amount' => 200,
            'reference' => 'Customer Order #' . $order->order_no,
            'credit_days' => 0,
            'is_walkin' => 0,
            'customer' => $customer->id,
            'Invoice_no' => 'DEL-TEST-' . uniqid(),
            'sale_date' => now()->toDateString(),
            'product_id' => [null],
            'is_manual' => ['1'],
            'manual_product_name' => ['Tailored Kurta'],
            'color' => [''],
            'warehouse_id' => [1],
            'carton_qty' => [2],
            'loose_qty' => [0],
            'pack_qty' => [1],
            'qty' => [2],
            'total_pieces' => [2],
            'visible_price' => [600],
            'price_per_piece' => [600],
            'item_disc' => [0],
            'discount_type' => ['percent'],
            'total' => [1200],
            'gross_amount' => [1200],
            'receipt_account_id' => [1],
            'receipt_amount' => [1000],
        ];

        $storeRes = $this->actingAs($user)->postJson(route('sales.store'), $payload);
        $storeRes->assertStatus(200);
        $this->assertTrue($storeRes->json('ok'));
        $saleId = $storeRes->json('booking_id');
        $this->assertNotEmpty($saleId);

        $payload['booking_id'] = $saleId;
        $postRes = $this->actingAs($user)->postJson(route('sales.post_final'), $payload);
        $postRes->assertStatus(200);
        $this->assertTrue($postRes->json('ok'));
        $this->assertStringContainsString((string)$saleId, $postRes->json('invoice_url'));

        // 3. Verify order is now delivered
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals($saleId, $order->sale_id);
        $this->assertNotNull($order->delivered_at);
        $this->assertEquals($user->id, $order->delivered_by);
    }

    public function test_user_without_sales_permission_can_deliver_customer_order_and_view_invoice(): void
    {
        // User 2 (admin / branch) does NOT have sales.create or sales.view
        $branchUser = User::find(2);
        if (!$branchUser) {
            $branchUser = User::create([
                'name' => 'Branch Staff No Sale Perm',
                'email' => 'branch_' . uniqid() . '@example.com',
                'password' => bcrypt('secret123'),
            ]);
        }
        $this->assertFalse($branchUser->can('sales.create'));
        $this->assertFalse($branchUser->can('sales.view'));

        $customer = Customer::first();
        $order = CustomerOrder::create([
            'order_no' => 'ORD-BRANCH-' . uniqid(),
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => 'ready',
            'subtotal' => 800,
            'total_amount' => 800,
            'advance_amount' => 300,
            'created_by' => $branchUser->id,
        ]);

        \App\Models\CustomerOrderItem::create([
            'customer_order_id' => $order->id,
            'item_name' => 'Cotton Shirt',
            'qty' => 1,
            'unit' => 'Pcs',
            'price' => 800,
            'total' => 800,
        ]);

        // Assert regular sale without customer_order_id is blocked with 403
        $regularSalePayload = [
            'action' => 'sale',
            'is_walkin' => 0,
            'customer' => $customer->id,
            'Invoice_no' => 'SALE-TEST-' . uniqid(),
            'product_id' => [null],
            'is_manual' => ['1'],
            'manual_product_name' => ['Some Product'],
            'qty' => [1],
            'total_pieces' => [1],
            'visible_price' => [500],
            'price_per_piece' => [500],
            'total' => [500],
        ];
        $blockedRes = $this->actingAs($branchUser)->postJson(route('sales.store'), $regularSalePayload);
        $blockedRes->assertStatus(403);

        // Assert customer order delivery succeeds with 200
        $deliveryPayload = [
            'action' => 'sale',
            'customer_order_id' => $order->id,
            'order_advance_amount' => 300,
            'reference' => 'Customer Order #' . $order->order_no,
            'credit_days' => 0,
            'is_walkin' => 0,
            'customer' => $customer->id,
            'Invoice_no' => 'DEL-BRANCH-' . uniqid(),
            'sale_date' => now()->toDateString(),
            'product_id' => [null],
            'is_manual' => ['1'],
            'manual_product_name' => ['Cotton Shirt'],
            'color' => [''],
            'warehouse_id' => [1],
            'carton_qty' => [1],
            'loose_qty' => [0],
            'pack_qty' => [1],
            'qty' => [1],
            'total_pieces' => [1],
            'visible_price' => [800],
            'price_per_piece' => [800],
            'item_disc' => [0],
            'discount_type' => ['percent'],
            'total' => [800],
            'gross_amount' => [800],
            'receipt_account_id' => [1],
            'receipt_amount' => [500],
        ];

        // Test via dedicated customer order delivery endpoint
        $deliveryRes = $this->actingAs($branchUser)->postJson(route('customer_orders.delivery.post_final'), $deliveryPayload);
        $deliveryRes->assertStatus(200);
        $this->assertTrue($deliveryRes->json('ok'));
        $saleId = $deliveryRes->json('booking_id');

        // Assert delivery invoice is accessible without sales.view permission
        $invoiceRes = $this->actingAs($branchUser)->get(route('customer_orders.delivery.invoice', $saleId));
        $invoiceRes->assertStatus(200);
        $invoiceRes->assertSee('Cotton Shirt');

        // Assert standard /sales/{id}/invoice is also accessible for delivery
        $stdInvoiceRes = $this->actingAs($branchUser)->get(route('sales.invoice', $saleId));
        $stdInvoiceRes->assertStatus(200);
        $stdInvoiceRes->assertSee('Cotton Shirt');

        // Assert thermal receipt is accessible
        $receiptRes = $this->actingAs($branchUser)->get(route('customer_orders.delivery.receipt', $saleId));
        $receiptRes->assertStatus(200);

        // Verify order is marked delivered
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals($saleId, $order->sale_id);

        // Clean up
        \App\Models\SaleItem::where('sale_id', $saleId)->delete();
        \App\Models\Sale::where('id', $saleId)->delete();
        $order->items()->delete();
        $order->delete();
    }

    public function test_customer_order_delivery_cogs_and_profit_loss_calculation(): void
    {
        $user = User::first() ?? User::factory()->create();
        $customer = Customer::create([
            'customer_id' => 'CUST-' . rand(1000, 9999),
            'customer_name' => 'Profit Test Customer ' . uniqid(),
            'customer_type' => 'Main Customer',
            'opening_balance' => 0,
        ]);

        $product = Product::create([
            'item_code' => 'TEST-PROD-' . uniqid(),
            'item_name' => 'Custom Suit ' . uniqid(),
            'size_mode' => 'by_pieces',
            'purchase_price_per_piece' => 0, // catalog purch price is 0
            'sale_price_per_piece' => 1000,
            'is_raw_material' => 0,
            'is_part' => 0,
        ]);

        $order = CustomerOrder::create([
            'order_no' => 'ORD-TEST-' . rand(1000, 9999),
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'total_qty' => 4,
            'subtotal' => 4000,
            'discount' => 0,
            'total_amount' => 4000,
            'advance_amount' => 1000,
            'created_by' => $user->id,
        ]);

        // Materials: 800 fabric + 800 tailoring = 1600 total (unit cost = 400)
        \App\Models\CustomerOrderItem::create([
            'customer_order_id' => $order->id,
            'product_id' => $product->id,
            'item_name' => $product->item_name,
            'qty' => 4,
            'price' => 1000,
            'total' => 4000,
            'materials' => [
                ['name' => 'Fabric', 'amount' => 800, 'qty' => 4],
                ['name' => 'Tailoring', 'amount' => 800, 'qty' => 4],
            ],
        ]);

        // Deliver order
        $payload = [
            'customer_order_id' => $order->id,
            'customer' => $customer->id,
            'reference' => 'Customer Order #' . $order->order_no,
            'product_id' => [$product->id],
            'is_manual' => ['0'],
            'manual_product_name' => [$product->item_name],
            'carton_qty' => [4],
            'qty' => [4],
            'total_pieces' => [4],
            'price_per_piece' => [1000],
            'visible_price' => [1000],
            'item_disc' => [0],
            'discount_type' => ['percent'],
            'total' => [4000],
            'gross_amount' => [4000],
            'receipt_account_id' => [1],
            'receipt_amount' => [3000],
        ];

        $delRes = $this->actingAs($user)->postJson(route('customer_orders.delivery.post_final'), $payload);
        $delRes->assertStatus(200);
        $saleId = $delRes->json('booking_id');

        // 1. Verify sale_item has purchase_price set to 400 (1600 / 4)
        $saleItem = \App\Models\SaleItem::where('sale_id', $saleId)->first();
        $this->assertNotNull($saleItem);
        $this->assertEquals(400.00, (float) $saleItem->purchase_price);

        // 2. Verify Profit & Loss Report calculates COGS and Profit correctly
        $reportCtrl = app(\App\Http\Controllers\ReportingController::class);
        $req = new \Illuminate\Http\Request([
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'product_id' => $product->id,
        ]);
        $reportRes = $reportCtrl->fetchProfitLoss($req);
        $data = $reportRes->getData();

        $this->assertNotEmpty($data->products);
        $stat = collect($data->products)->firstWhere('item_code', $product->item_code);
        $this->assertNotNull($stat);
        $this->assertEquals(4, (float) $stat->sold_qty);
        $this->assertEquals(4000, (float) $stat->revenue);
        $this->assertEquals(1600, (float) $stat->cogs);
        $this->assertEquals(2400, (float) $stat->profit);

        // Clean up
        \App\Models\SaleItem::where('sale_id', $saleId)->delete();
        \App\Models\Sale::where('id', $saleId)->delete();
        $order->items()->delete();
        $order->delete();
        $product->delete();
        $customer->delete();
    }
}
