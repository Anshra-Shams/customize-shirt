<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'source_id',
        'source_type',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount(int $userId): int
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get recent notifications for a user
     */
    public static function getRecent(int $userId, int $limit = 10)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create notification for multiple users
     */
    public static function createForUsers(array $userIds, array $data): void
    {
        foreach ($userIds as $userId) {
            self::create(array_merge($data, ['user_id' => $userId]));
        }
    }

    /**
     * Create sale return notification for super admins
     */
    public static function createSaleReturnNotification($saleReturn, $sale): void
    {
        // Get all users
        $targetUsers = \App\Models\User::pluck('id')->toArray();

        if (empty($targetUsers)) {
            return;
        }

        $customer = \App\Models\Customer::find($saleReturn->customer);
        $customerName = $customer ? $customer->name : 'Unknown Customer';
        
        $data = [
            'title' => '🔄 New Sale Return Request',
            'message' => "Sale Return #{$saleReturn->id} created for Invoice #{$sale->invoice_no} by {$customerName}. Amount: PKR " . number_format($saleReturn->total_net, 2),
            'type' => 'sale_return',
            'source_id' => $saleReturn->id,
            'source_type' => 'App\Models\SalesReturn',
            'action_url' => route('sale.return.detail', $saleReturn->id),
            'is_read' => false,
        ];

        self::createForUsers($targetUsers, $data);
    }

    /**
     * Create stock alert notification for all users
     */
    public static function createStockAlertNotification($product, $currentStock): void
    {
        // Get all users
        $targetUsers = \App\Models\User::pluck('id')->toArray();

        if (empty($targetUsers)) {
            return;
        }

        $isRaw = (bool) ($product->is_raw_material || $product->is_part);
        $typeLabel = $isRaw ? 'Raw Material' : 'Product';
        $title = $isRaw ? '⚠️ Raw Material Alert: Low Stock' : '⚠️ Stock Alert: Low Stock';
        $actionUrl = $isRaw ? url('raw-materials') : url('product');
        $unitName = $product->unit->name ?? 'pieces';

        $message = "{$typeLabel} '{$product->item_name}' (SKU: {$product->item_code}) is low on stock. Current quantity: {$currentStock} {$unitName}, Alert threshold: {$product->alert_quantity} {$unitName}.";

        // Check if there is already an unread notification for this product
        $existing = self::whereIn('user_id', $targetUsers)
            ->where('source_type', 'App\Models\Product')
            ->where('source_id', $product->id)
            ->where('is_read', false)
            ->exists();

        if ($existing) {
            // Update message and action URL on existing unread notifications
            self::where('source_type', 'App\Models\Product')
                ->where('source_id', $product->id)
                ->where('is_read', false)
                ->update([
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                ]);
            return;
        }

        $data = [
            'title' => $title,
            'message' => $message,
            'type' => 'critical',
            'source_id' => $product->id,
            'source_type' => 'App\Models\Product',
            'action_url' => $actionUrl,
            'is_read' => false,
        ];

        self::createForUsers($targetUsers, $data);
    }

    /**
     * Resolve/mark read stock alert notification when stock is replenished
     */
    public static function resolveStockAlertNotification(int $productId): void
    {
        self::where('source_type', 'App\Models\Product')
            ->where('source_id', $productId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Create delivery notification for a Customer Order
     */
    public static function createDeliveryNotification($order, string $eventType = 'due_today'): void
    {
        $targetUsers = \App\Models\User::pluck('id')->toArray();
        if (empty($targetUsers)) {
            return;
        }

        $customerName = $order->customer_display_name;
        $orderNo = $order->order_no;
        $deliveryDate = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') : 'N/A';
        $actionUrl = url("sales/from-order/{$order->id}");

        switch ($eventType) {
            case 'overdue':
                $title = "🚨 Overdue Delivery: Order #{$orderNo}";
                $message = "Customer Order #{$orderNo} for '{$customerName}' was due on {$deliveryDate} and is OVERDUE for delivery!";
                $type = 'critical';
                break;

            case 'due_today':
                $title = "🚚 Delivery Alert: Order #{$orderNo} Due Today";
                $message = "Customer Order #{$orderNo} for '{$customerName}' is scheduled for delivery today ({$deliveryDate})!";
                $type = 'warning';
                break;

            case 'ready':
                $title = "📦 Ready for Delivery: Order #{$orderNo}";
                $message = "Customer Order #{$orderNo} for '{$customerName}' is packed & READY for delivery!";
                $type = 'info';
                break;

            case 'booked':
            default:
                $title = "📋 New Delivery Order Booked: #{$orderNo}";
                $message = "New Customer Order #{$orderNo} booked for '{$customerName}'. Scheduled delivery: {$deliveryDate}.";
                $type = 'info';
                break;
        }

        // Avoid duplicate unread notification with identical title and order
        $exists = self::whereIn('user_id', $targetUsers)
            ->where('source_type', 'App\Models\CustomerOrder')
            ->where('source_id', $order->id)
            ->where('title', $title)
            ->where('is_read', false)
            ->exists();

        if ($exists) {
            return;
        }

        $data = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'source_id' => $order->id,
            'source_type' => 'App\Models\CustomerOrder',
            'action_url' => $actionUrl,
            'is_read' => false,
        ];

        self::createForUsers($targetUsers, $data);
    }

    /**
     * Resolve/mark as read all notifications for a given CustomerOrder (e.g. when delivered or cancelled)
     */
    public static function resolveOrderNotifications(int $orderId): void
    {
        self::where('source_type', 'App\Models\CustomerOrder')
            ->where('source_id', $orderId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Automatically scan and sync system alerts (Low Stock & Customer Order Deliveries)
     */
    public static function syncSystemAlerts(): void
    {
        // 0. Clean up orphan notifications for soft-deleted products
        $deletedProductIds = \App\Models\Product::onlyTrashed()->pluck('id')->toArray();
        if (!empty($deletedProductIds)) {
            self::where('source_type', 'App\Models\Product')
                ->whereIn('source_id', $deletedProductIds)
                ->delete();
        }

        // 1. Sync Low Stock Alerts (Raw Materials & Finished Goods)
        $alertProducts = \App\Models\Product::with('unit')
            ->whereNotNull('alert_quantity')
            ->where('alert_quantity', '>', 0)
            ->get();

        foreach ($alertProducts as $product) {
            $totalPieces = (float) \App\Models\WarehouseStock::where('product_id', $product->id)->sum('total_pieces');
            if ($totalPieces <= (float) $product->alert_quantity) {
                self::createStockAlertNotification($product, $totalPieces);
            } else {
                self::resolveStockAlertNotification($product->id);
            }
        }

        // 2. Sync Delivery Alerts for undelivered Customer Orders
        $today = now()->format('Y-m-d');
        $undeliveredOrders = \App\Models\CustomerOrder::with('customer')
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->get();

        foreach ($undeliveredOrders as $order) {
            if (!$order->delivery_date) {
                continue;
            }
            $dDate = \Carbon\Carbon::parse($order->delivery_date)->format('Y-m-d');

            if ($dDate < $today) {
                self::createDeliveryNotification($order, 'overdue');
            } elseif ($dDate === $today) {
                self::createDeliveryNotification($order, 'due_today');
            } elseif ($order->status === 'ready') {
                self::createDeliveryNotification($order, 'ready');
            }
        }
    }
}
