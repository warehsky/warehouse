<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseByDirectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouseByDirection', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('warehouseId');
            $table->integer('tradeDirectionId');
            $table->index('tradeDirectionId', 'FK_wd_warehouse');
            $table->index('warehouseId', 'FK_wd_direction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouseByDirection');
    }
}
