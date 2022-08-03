0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradePointsVisitDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_points_visit_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('day_week');
            $table->integer('trade_point_id');
            $table->integer('user_id');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 3');
            $table->index('trade_point_id', 'FK_trade_points_visit_days_trade_points');
            $table->index('user_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_points_visit_days');
    }
}
