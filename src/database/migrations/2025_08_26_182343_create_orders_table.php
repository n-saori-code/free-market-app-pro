<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
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
            // 購入者
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            // 出品者
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('postal_code');
            $table->string('address');
            $table->string('building')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['buyer_id', 'product_id']);
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
}
