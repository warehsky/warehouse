<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('trade_mark_id');
            $table->integer('unit_id');
            $table->string('item', 100);
            $table->unsignedTinyInteger('must_sku');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->string('guid_parent', 36);
            $table->unsignedTinyInteger('is_group');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->integer('licenseCode');
            $table->index('guid', 'Индекс 3');
            $table->index('trade_mark_id', 'FK_items_categories');
            $table->index('unit_id', 'unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
