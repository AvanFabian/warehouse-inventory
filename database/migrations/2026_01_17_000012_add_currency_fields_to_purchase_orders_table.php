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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('IDR')->after('total_amount');
            $table->decimal('exchange_rate_at_transaction', 15, 8)->default(1.00000000)->after('currency_code');
            $table->decimal('transaction_fees', 15, 2)->default(0)->after('exchange_rate_at_transaction');
            $table->string('fee_currency_code', 3)->nullable()->after('transaction_fees');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fee_currency_code');
            
            $table->index('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['currency_code']);
            $table->dropColumn([
                'currency_code',
                'exchange_rate_at_transaction',
                'transaction_fees',
                'fee_currency_code',
                'net_amount',
            ]);
        });
    }
};
