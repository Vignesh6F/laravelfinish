<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('vehicle');
        
        Schema::create('vehicle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->integer('vehicle_make_id');
            $table->integer('vehicle_model_id');
            $table->string('vehicle_id',255);
            $table->string('vehicle_type',100);
            $table->string('vehicle_name',100);
            $table->string('vehicle_number');
            $table->boolean('is_active')->default(0);
            $table->string('year');
            $table->string('color');
            $table->enum('default_type',['0', '1'])->default(0);
            $table->enum('status',['Active', 'Inactive']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vehicle');
    }
}
