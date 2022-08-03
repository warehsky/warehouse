0<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('client_id');
            $table->integer('areaId');
            $table->string('trade_point', 200);
            $table->string('address', 200);
            $table->string('phone', 20);
            $table->string('email', 50);
            $table->string('contact_fio', 200);
            $table->string('contact_phone', 20);
            $table->integer('trade_point_type_id');
            $table->double('coord_lat');
            $table->double('coord_lng');
            $table->text('note');
            $table->string('guid', 36);
            $table->unsignedTinyInteger('is_new');
            $table->unsignedTinyInteger('is_locked');
            $table->string('reference_point', 150);
            $table->string('director_contacts', 150);
            $table->string('contacts_lpr', 150);
            $table->string('sellers_contacts', 150);
            $table->unsignedTinyInteger('potential');
            $table->unsignedTinyInteger('collects_collector');
            $table->string('working_hours_mon_from', 8);
            $table->string('working_hours_mon_to', 8);
            $table->string('working_hours_tue_from', 8);
            $table->string('working_hours_tue_to', 8);
            $table->string('working_hours_wed_from', 8);
            $table->string('working_hours_wed_to', 8);
            $table->string('working_hours_thu_from', 8);
            $table->string('working_hours_thu_to', 8);
            $table->string('working_hours_fri_from', 8);
            $table->string('working_hours_fri_to', 8);
            $table->string('working_hours_sut_from', 8);
            $table->string('working_hours_sut_to', 8);
            $table->string('working_hours_sun_from', 8);
            $table->string('working_hours_sun_to', 8);
            $table->integer('location_id');
            $table->integer('trade_area_id');
            $table->timestamp('updateTm')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->index('trade_point_type_id', 'FK_trade_points_trade_points_types');
            $table->index('client_id', 'FK_trade_points_clients');
            $table->index('location_id', 'location_id');
            $table->index('trade_area_id', 'trade_area_id');
            $table->index('guid', 'guid');
            $table->index('client_id', 'client_id');
            $table->index('areaId', 'FK_trade_points_area');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_points');
    }
}
