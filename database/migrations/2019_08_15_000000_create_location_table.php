<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('coord_lat');
            $table->double('coord_lng');
            $table->integer('user_id');
            $table->timestamp('date_time');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 3');
            $table->index('user_id', 'FK_location_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location');
    }
}
