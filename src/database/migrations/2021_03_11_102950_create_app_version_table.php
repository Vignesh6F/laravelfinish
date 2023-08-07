<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppVersionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_version', function (Blueprint $table) {
            $table->increments('id');
            $table->string('version',10);
            $table->tinyInteger('device_type');//1=Apple,2=Andriod
            $table->tinyInteger('user_type');//0=Rider,1=Driver
            $table->tinyInteger('force_update');//0=No,1=Yes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_version');
    }
}
