0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradePointDebtsExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradePointDebtsExchange', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tpId');
            $table->integer('userId');
            $table->double('value');
            $table->double('expiredValue');
            $table->index('tpId', 'clientId');
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
        Schema::dropIfExists('tradePointDebtsExchange');
    }
}
