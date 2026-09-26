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
        Schema::create('product_currency_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amount', 15, 4)->default(0);
            $table->integer('product_id')->unsigned();
            $table->integer('currency_id')->unsigned();
            $table->timestamps();

            $table->unique(['product_id', 'currency_id']);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_currency_prices');
    }
};
