<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dateTime('delivered_at')->nullable()->after('status');
            $table->foreignId('delivered_by')->nullable()->after('delivered_at')->constrained('users')->nullOnDelete();
            $table->string('received_by')->nullable()->after('delivered_by');
            $table->text('delivery_notes')->nullable()->after('received_by');
            $table->decimal('delivery_payment_amount', 15, 2)->default(0)->after('payment_methods');
            $table->json('delivery_payment_methods')->nullable()->after('delivery_payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropForeign(['delivered_by']);
            $table->dropColumn([
                'delivered_at',
                'delivered_by',
                'received_by',
                'delivery_notes',
                'delivery_payment_amount',
                'delivery_payment_methods',
            ]);
        });
    }
};
