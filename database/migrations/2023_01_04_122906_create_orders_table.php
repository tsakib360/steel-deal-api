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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_number')->unique();
            $table->foreignId('user_id')->index()->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->float('subtotal')->default(0);
            $table->float('delivery_charge')->default(0);
            $table->float('discount')->default(0);
            $table->float('grand_total')->default(0);
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
        Schema::dropIfExists('orders');
    }
};
