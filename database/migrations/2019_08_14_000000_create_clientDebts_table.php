<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientDebtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientDebts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('clientId');
            $table->integer('userId');
            $table->double('value');
            $table->double('expiredValue');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('clientId', 'clientId');
            $table->index('userId', 'userId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientDebts');
    }
}
