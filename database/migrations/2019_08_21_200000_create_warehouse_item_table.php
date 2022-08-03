0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('warehouse_id');
            $table->integer('item_id');
            $table->decimal('quantity', 10, 2);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 4');
            $table->index('item_id', 'FK_warhouse_item_items');
            $table->index('warehouse_id', 'FK_warhouse_item_warehouses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_item');
    }
}
