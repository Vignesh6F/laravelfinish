<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateDocumentsLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_langs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('documents_id')->unsigned();
            $table->string('document_name');             
            $table->string('locale',5)->index();
            $table->unique(['documents_id','locale']);            
            $table->foreign('documents_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents_langs');
    }
}
