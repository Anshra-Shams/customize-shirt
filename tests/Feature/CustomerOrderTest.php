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
        $user = User::where('usertype', 'admin')->first();
        if ($user) {
            return $user;
        }

        return User::create([
            'name' => 'Test Admin',
            'email' => 'test_order_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'usertype' => 'admin',
        ]);
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
        $response->assertRedirect(route('customer_orders.index'));

        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('2026-11-20', $order->delivery_date->format('Y-m-d'));
        $this->assertEquals(5.00, (float) $order->total_qty);
        $this->assertEquals(6000.00, (float) $order->subtotal);
        $this->assertEquals(100.00, (float) $order->discount);
        $this->assertEquals(5900.00, (float) $order->total_amount);
        $this->assertEquals(500.00, (float) $order->advance_amount);
        $this->assertCount(2, $order->items);

        // Ensure ZERO impact on sales, stock movements, and accounting journal entries
        $this->assertEquals($salesBefore, DB::table('sales')->count());
        $this->assertEquals($movementsBefore, DB::table('stock_movements')->count());
        $this->assertEquals($journalBefore, DB::table('journal_entries')->count());

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

        $customer = Customer::first() ?? Customer::create([
            'customer_name' => 'Mat Test Customer',
            'mobile' => '03009998888',
            'opening_balance' => 0,
        ]);

        // Create page renders Materials button column, modal, and catalog product data-materials
        $this->actingAs($user)
            ->get(route('customer_orders.create'))
            ->assertStatus(200)
            ->assertSee('Materials')
            ->assertSee('orderMaterialsModal')
            ->assertSee('Fabric');

        // Submit order with catalog materials + added custom order-only material
        $submittedMaterials = [
            ['name' => 'Fabric', 'amount' => 100, 'is_custom' => false],
            ['name' => 'Stitching', 'amount' => 50, 'is_custom' => false],
            ['name' => 'Extra Golden Lace', 'amount' => 75, 'is_custom' => true],
        ];

        $this->actingAs($user)
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
            ])
            ->assertRedirect(route('customer_orders.index'));

        $order = CustomerOrder::latest('id')->first();
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
            ->assertSee('Extra Golden Lace');

        // Customer orders index uses "In Preparing" and links to confirmation page
        $prepareUrl = route('customer_orders.prepare', $order->id);
        $this->actingAs($user)
            ->get(route('customer_orders.index'))
            ->assertStatus(200)
            ->assertSee('In Preparing')
            ->assertSee($prepareUrl);

        // Confirmation page renders order form pre-set for "In Preparing"
        $this->actingAs($user)
            ->get($prepareUrl)
            ->assertStatus(200)
            ->assertSee('Confirm Order Preparation #' . $order->order_no)
            ->assertSee('Confirm In Preparing')
            ->assertSee('Extra Golden Lace');

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

        $this->assertEquals('in_progress', $order->fresh()->status);
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

        $customer = Customer::first() ?? Customer::create([
            'customer_name' => 'Pay Test Customer',
            'mobile' => '03007776666',
            'opening_balance' => 0,
        ]);

        // Create page renders Advance Paid, Remaining Balance & Payment Methods
        $this->actingAs($user)
            ->get(route('customer_orders.create'))
            ->assertStatus(200)
            ->assertSee('Advance Paid')
            ->assertSee('Remaining Balance')
            ->assertSee('Payment Methods')
            ->assertSee('Add Account');

        $this->actingAs($user)
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
            ])
            ->assertRedirect(route('customer_orders.index'));

        $order = CustomerOrder::latest('id')->first();
        $this->assertEquals(500.00, (float) $order->advance_amount);
        $this->assertCount(2, $order->payment_methods);
        $this->assertEquals($account->title, $order->payment_methods[0]['account_title']);

        foreach ([
            route('customer_orders.edit', $order->id),
            route('customer_orders.prepare', $order->id),
            route('customer_orders.show', $order->id),
            route('customer_orders.print', $order->id),
        ] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertStatus(200)
                ->assertSee('Advance Paid')
                ->assertSee('Remaining Balance')
                ->assertSee('Payment Methods')
                ->assertSee($account->title);
        }
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

        $customer = Customer::first() ?? Customer::create([
            'customer_name' => 'Ledger Advance Customer',
            'mobile' => '03005554433',
            'opening_balance' => 0,
        ]);

        $salesBefore = DB::table('sales')->count();
        $movementsBefore = DB::table('stock_movements')->count();

        // 1. Create order with Rs 500 advance into $account
        $orderNo = 'ORD-LED-' . uniqid();
        $this->actingAs($user)
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
            ])
            ->assertRedirect(route('customer_orders.index'));

        $order = CustomerOrder::where('order_no', $orderNo)->firstOrFail();

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
}
