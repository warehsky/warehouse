0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradePointMMLDirectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradePointMMLDirections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tpId');
            $table->integer('tradeDirectionId');
            $table->integer('mmlClass');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('tpId', 'FK_tpMML_tp');
            $table->index('tradeDirectionId', 'FK_tpMML_td');
            $table->index('mmlClass', 'FK_tpMML_class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tradePointMMLDirections');
    }
}
