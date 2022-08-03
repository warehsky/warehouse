0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradeMarkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_mark', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('supplier', 100);
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->string('guid_parent', 36);
            $table->unsignedTinyInteger('is_group');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('guid', 'Индекс 3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_mark');
    }
}
