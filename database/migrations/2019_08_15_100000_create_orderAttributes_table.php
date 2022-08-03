<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderAttributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tradeDirection');
            $table->string('title', 200);
            $table->string('value');
            $table->string('guid', 36);
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('tradeDirection', 'FK_orderAttribute_tradeDirection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderAttributes');
    }
}
