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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, IDR, EUR
            $table->string('name', 50); // US Dollar, Indonesian Rupiah
            $table->string('symbol', 10); // $, Rp, €
            $table->boolean('is_base')->default(false); // IDR is base
            $table->decimal('exchange_rate', 15, 8)->default(1.00000000); // Rate to base
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
