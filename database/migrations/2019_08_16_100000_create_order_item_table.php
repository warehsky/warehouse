0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id');
            $table->integer('item_id');
            $table->integer('warehouse_id');
            $table->double('quantity');
            $table->double('price');
            $table->integer('priceType');
            $table->integer('percent');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 5');
            $table->index('item_id', 'FK_order_item_items');
            $table->index('warehouse_id', 'FK_order_item_warehouse');
            $table->index('order_id', 'FK_order_item_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item');
    }
}
