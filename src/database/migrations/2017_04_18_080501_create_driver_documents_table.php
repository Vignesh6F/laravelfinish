<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriverDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('driver_documents');
        
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['Driver','Vehicle'])->default('Driver');
            $table->integer('vehicle_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('document_id',50);
            $table->text('document');
            $table->enum('status', [0,1,2])->default(0);
            $table->date('expired_date')->nullable();
            $table->index(['type','vehicle_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('driver_documents');
    }
}
