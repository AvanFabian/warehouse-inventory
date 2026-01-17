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
        Schema::create('warehouse_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('warehouse_zones')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 100)->nullable();
            $table->integer('levels')->default(1); // Number of vertical levels
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Rack code must be unique within each zone
            $table->unique(['zone_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_racks');
    }
};
