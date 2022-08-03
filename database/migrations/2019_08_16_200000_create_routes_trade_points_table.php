0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoutesTradePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes_trade_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('route_id');
            $table->integer('trade_point_id');
            $table->timestamp('date_time_start')->nullable();
            $table->timestamp('date_time_end')->nullable();
            $table->double('coord_start_lat');
            $table->double('coord_start_lng');
            $table->double('coord_end_lat');
            $table->double('coord_end_lng');
            $table->string('image_start', 37);
            $table->string('image_end', 37);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 4');
            $table->index('route_id', 'FK_Routes_trade_points_routes');
            $table->index('trade_point_id', 'FK_routes_trade_points_trade_points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes_trade_points');
    }
}
