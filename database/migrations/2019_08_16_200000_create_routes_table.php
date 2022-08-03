0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('route', 200);
            $table->timestamp('date_time_created');
            $table->date('date_begin');
            $table->timestamp('date_time_start')->default('1970-01-01 08:00:00');
            $table->timestamp('date_time_end')->default('1970-01-01 08:00:00');
            $table->double('coord_start_lat');
            $table->double('coord_start_lng');
            $table->double('coord_end_lat');
            $table->double('coord_end_lng');
            $table->integer('user_id');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 3');
            $table->index('user_id', 'FK_routes_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }
}
