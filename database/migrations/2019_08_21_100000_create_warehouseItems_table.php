0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouseItems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('warehouseId');
            $table->integer('itemId');
            $table->decimal('quantity', 10, 2);
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('warehouseId', 'FK_warehouseItems_warehouse');
            $table->index('itemId', 'FK_warehouseItems_item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouseItems');
    }
}
