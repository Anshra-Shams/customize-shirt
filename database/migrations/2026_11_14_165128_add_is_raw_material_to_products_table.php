<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_raw_material')) {
                $table->boolean('is_raw_material')->default(0)->index()->after('is_part');
            }
        });

        // Ensure total_m2 has default value of 0 so inserts without tile sizing succeed
        if (Schema::hasColumn('products', 'total_m2')) {
            try {
                DB::statement('ALTER TABLE products MODIFY total_m2 DECIMAL(10,2) NULL DEFAULT 0');
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_raw_material')) {
                $table->dropColumn('is_raw_material');
            }
        });
    }
};
