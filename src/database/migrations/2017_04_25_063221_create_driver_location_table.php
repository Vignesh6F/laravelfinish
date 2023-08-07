<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('latitude',100);
            $table->string('longitude',100);
            $table->integer('car_id')->unsigned();
            $table->foreign('car_id')->references('id')->on('car_type');
            $table->integer('pool_trip_id')->unsigned()->nullable();
            $table->enum('status',['Online', 'Offline','Trip','Pool Trip'])->default('Offline');
            $table->timestamps();
            $table->index(['status', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('driver_location');
    }
}
