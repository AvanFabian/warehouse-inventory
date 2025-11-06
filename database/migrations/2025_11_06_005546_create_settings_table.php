<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'company_name', 'value' => 'Warehouse Management System', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_address', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_phone', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_email', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency', 'value' => 'IDR', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'low_stock_alert', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'items_per_page', 'value' => '20', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
