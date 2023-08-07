<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilterObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_objects', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type',['vehicle','rider']);
            $table->integer('object_id');
            $table->integer('filter_id')->unsigned();
            $table->foreign('filter_id')->references('id')->on('filter_options')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filter_objects');
    }
}
