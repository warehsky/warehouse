0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitingDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visiting_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('trade_point_id');
            $table->integer('user_id');
            $table->string('week1', 7);
            $table->string('week2', 7);
            $table->string('week3', 7);
            $table->string('week4', 7);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('trade_point_id', 'trade_point_id');
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
        Schema::dropIfExists('visiting_days');
    }
}
