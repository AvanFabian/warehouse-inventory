<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_in_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained('stock_ins')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_in_details');
    }
};
