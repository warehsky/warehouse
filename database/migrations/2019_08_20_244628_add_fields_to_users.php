<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login', 40);
            $table->integer('areaId');
            $table->integer('priceTypes')->default(2);
            $table->integer('tradeDirection')->default(0);
            $table->string('session', 64);
            $table->string('client', 40);
            $table->bigInteger('login_time');
            $table->bigInteger('session_end_time');
            $table->string('fio', 200);
            $table->tinyInteger('is_locked')->default(2);
            $table->string('guid', 36);
            $table->string('week', 7);
            $table->integer('daySchedule')->default(0);
            $table->unsignedTinyInteger('is_new');
            $table->integer('gen');
            $table->tinyInteger('f2percent');
            $table->integer('f2time');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('guid', 'Индекс 3');
            $table->index('login', 'login');
            $table->index('areaId', 'FK_user_areaId');
            $table->index('tradeDirection', 'FK_user_tradeDirection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
