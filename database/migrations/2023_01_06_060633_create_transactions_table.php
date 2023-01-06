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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->nullable()->references('id')->on('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->float('amount')->default(0);
            $table->float('charge')->default(0);
            $table->string('trx_type', 10)->comment('if the transaction amount get platform the it shows + else -.');
            $table->string('trx');
            $table->text('details');
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
        Schema::dropIfExists('transactions');
    }
};
