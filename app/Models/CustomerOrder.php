<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'customer_id',
        'walkin_name',
        'customer_phone',
        'customer_address',
        'order_date',
        'delivery_date',
        'status',
        'description',
        'total_qty',
        'subtotal',
        'discount',
        'total_amount',
        'advance_amount',
        'payment_methods',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_qty' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'payment_methods' => 'array',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In Preparing',
        'ready' => 'Ready',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItem::class, 'customer_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->customer_name ?? 'Customer #' . $this->customer_id;
        }

        return $this->walkin_name ?: 'Walk-in Customer';
    }

    public static function generateOrderNo(): string
    {
        $lastOrder = self::orderByDesc('id')->first();
        $nextNumber = 1;

        if ($lastOrder && preg_match('/(\d+)$/', (string) $lastOrder->order_no, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } elseif ($lastOrder) {
            $nextNumber = $lastOrder->id + 1;
        }

        do {
            $candidate = 'ORD-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = self::where('order_no', $candidate)->exists();
            $nextNumber++;
        } while ($exists);

        return $candidate;
    }
}
