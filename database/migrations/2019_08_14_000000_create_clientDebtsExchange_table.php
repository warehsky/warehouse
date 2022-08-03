<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientDebtsExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientDebtsExchange', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('clientId');
            $table->integer('userId');
            $table->double('value');
            $table->double('expiredValue');
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
        Schema::dropIfExists('clientDebtsExchange');
    }
}
