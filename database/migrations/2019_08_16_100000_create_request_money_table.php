0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_money', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('number', 32);
            $table->integer('status');
            $table->integer('trade_point_id');
            $table->string('note', 200);
            $table->double('sum');
            $table->timestamp('date_time_created')->default(DB::raw('CURRENT_TIMESTAMP'));;
            $table->double('coord_created_lat');
            $table->double('coord_created_lng');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->string('orderGuid', 36);
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('guid', 'Индекс 5');
            $table->index('user_id', 'FK_request_money_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_money');
    }
}
