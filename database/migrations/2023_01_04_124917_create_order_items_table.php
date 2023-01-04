<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->nullable()->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stock_id')->index()->references('id')->on('instocks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('size_id')->index()->references('id')->on('sizes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('qty')->default(0);
            $table->float('price')->default(0);
            $table->float('total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
