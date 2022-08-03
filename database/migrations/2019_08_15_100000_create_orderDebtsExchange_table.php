<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDebtsExchangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderDebtsExchange', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('orderGuid', 36);
            $table->double('value');
            $table->timestamp('expired');
            $table->index('orderGuid', 'orderGuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderDebtsExchange');
    }
}
