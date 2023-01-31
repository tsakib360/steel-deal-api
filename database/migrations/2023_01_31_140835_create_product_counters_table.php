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
        Schema::create('product_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stock_id')->index()->references('id')->on('instocks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('size_id')->index()->references('id')->on('sizes')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('buyer_id')->index()->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('seller_id')->index()->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->float('counter_price')->default(0);
            $table->float('counter_qty')->default(0);
            $table->text('history')->nullable();
            $table->tinyInteger('is_accepted')->default(0);
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
        Schema::dropIfExists('product_counters');
    }
};
