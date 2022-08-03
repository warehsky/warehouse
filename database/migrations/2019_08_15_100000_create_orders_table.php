<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number', 32);
            $table->string('contractType');
            $table->integer('trade_point_id');
            $table->integer('user_id');
            $table->timestamp('date_time_created');
            $table->timestamp('date_time_shipment');
            $table->integer('deliveryType');
            $table->double('coord_created_lat');
            $table->double('coord_created_lng');
            $table->unsignedTinyInteger('status')->default(0);
            $table->double('sum_total');
            $table->string('note', 200);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->string('attributes', 200)->default("");
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('guid', 'guid');
            $table->index('trade_point_id', 'trade_point_id');
            $table->index('user_id', 'user_id');
            $table->index('updateTm', 'updateTm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
