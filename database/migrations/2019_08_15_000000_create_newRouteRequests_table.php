<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewRouteRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newRouteRequests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('guid', 36);
            $table->integer('user_id');
            $table->unsignedTinyInteger('dayOfWeek');
            $table->unsignedTinyInteger('is_new');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('guid', 'guid');
            $table->index('id', 'user_id');
            $table->index('user_id', 'FK_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newRouteRequests');
    }
}
