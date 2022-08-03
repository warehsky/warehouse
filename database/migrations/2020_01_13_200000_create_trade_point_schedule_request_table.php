0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradePointScheduleRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradePointScheduleRequest', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('userId');
            $table->integer('tpId');
            $table->integer('scheduleTypeId');
            $table->unsignedTinyInteger('dayOfWeek');
            $table->integer('schedule');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('tpId', 'trade_point');
            $table->index('updateTm', 'updateTm');
            $table->index('scheduleTypeId', 'FK_tpsr_scheduleType');
            $table->index('userId', 'FK_tpsr_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tradePointScheduleRequest');
    }
}
