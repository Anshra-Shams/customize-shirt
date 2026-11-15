<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\SystemNotification;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemNotificationTest extends TestCase
{
    public function test_raw_material_low_stock_notification_is_generated_and_links_to_raw_materials(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::first() ?? Warehouse::create(['warehouse_name' => 'Main Store']);

        // Create raw material with alert threshold 5
        $rm = Product::create([
            'item_name' => 'Test Silk Thread ' . uniqid(),
            'item_code' => 'RM-NOTIF-' . rand(100, 999),
            'is_raw_material' => 1,
            'is_active' => 1,
            'alert_quantity' => 5,
            'purchase_price_per_piece' => 50,
        ]);

        // Initial stock = 10 (above threshold 5)
        $stock = WarehouseStock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $rm->id,
            'total_pieces' => 10,
            'quantity' => 10,
        ]);

        // Run sync
        SystemNotification::syncSystemAlerts();
        $this->assertFalse(
            SystemNotification::where('source_type', Product::class)
                ->where('source_id', $rm->id)
                ->where('is_read', false)
                ->exists()
        );

        // Reduce stock to 4 (below threshold 5)
        $stock->total_pieces = 4;
        $stock->save();

        SystemNotification::syncSystemAlerts();

        $notif = SystemNotification::where('source_type', Product::class)
            ->where('source_id', $rm->id)
            ->where('is_read', false)
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('Raw Material Alert', $notif->title);
        $this->assertStringContainsString($rm->item_name, $notif->message);
        $this->assertStringContainsString('raw-materials', $notif->action_url);

        // Replenish stock to 15 (above threshold) -> Alert should auto-resolve
        $stock->total_pieces = 15;
        $stock->save();

        SystemNotification::syncSystemAlerts();

        $this->assertFalse(
            SystemNotification::where('source_type', Product::class)
                ->where('source_id', $rm->id)
                ->where('is_read', false)
                ->exists()
        );
    }

    public function test_customer_order_delivery_alerts_are_generated_and_auto_resolved_on_delivery(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'customer_id' => 'CUST-DLV-' . uniqid(),
            'customer_name' => 'Delivery Notif Customer',
            'mobile' => '03001234567',
            'opening_balance' => 0,
        ]);

        // 1. Order due today
        $orderToday = CustomerOrder::create([
            'order_no' => 'ORD-TODAY-' . rand(100, 999),
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'delivery_date' => now()->toDateString(),
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        // 2. Order overdue
        $orderOverdue = CustomerOrder::create([
            'order_no' => 'ORD-PAST-' . rand(100, 999),
            'customer_id' => $customer->id,
            'order_date' => now()->subDays(5)->toDateString(),
            'delivery_date' => now()->subDays(2)->toDateString(),
            'status' => 'in_progress',
            'created_by' => $user->id,
        ]);

        SystemNotification::syncSystemAlerts();

        // Check Today alert
        $todayNotif = SystemNotification::where('source_type', CustomerOrder::class)
            ->where('source_id', $orderToday->id)
            ->where('is_read', false)
            ->first();
        $this->assertNotNull($todayNotif);
        $this->assertStringContainsString('Due Today', $todayNotif->title);
        $this->assertStringContainsString("sales/from-order/{$orderToday->id}", $todayNotif->action_url);

        // Check Overdue alert
        $overdueNotif = SystemNotification::where('source_type', CustomerOrder::class)
            ->where('source_id', $orderOverdue->id)
            ->where('is_read', false)
            ->first();
        $this->assertNotNull($overdueNotif);
        $this->assertStringContainsString('Overdue Delivery', $overdueNotif->title);

        // Deliver the today order -> Notif resolves
        SystemNotification::resolveOrderNotifications($orderToday->id);
        $this->assertFalse(
            SystemNotification::where('source_type', CustomerOrder::class)
                ->where('source_id', $orderToday->id)
                ->where('is_read', false)
                ->exists()
        );

        // Cleanup test orders & customer so dev DB stays clean
        $orderToday->delete();
        $orderOverdue->delete();
        $customer->delete();
    }
}
