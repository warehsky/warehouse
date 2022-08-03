<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('item_id');
            $table->integer('filter_id');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->index('guid', 'Индекс 4');
            $table->index('item_id', 'FK_items_filters_items');
            $table->index('filter_id', 'FK_items_filters_filters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items_filters');
    }
}
