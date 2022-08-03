0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouteEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('RouteEvents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('coord_created_lat');
            $table->double('coord_created_lng');
            $table->integer('user_id');
            $table->timestamp('createTm')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('tradePointId');
            $table->string('title', 32);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('user_id', 'FK_RouteEvents_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('RouteEvents');
    }
}
