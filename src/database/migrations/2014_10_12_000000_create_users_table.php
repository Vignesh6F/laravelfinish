<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->string('email',100);
            $table->string('country_code',100);
            $table->enum('gender',[1,2])->nullable();
            $table->string('mobile_number',20);
            $table->string('password')->nullable();
            $table->enum('user_type',['Rider', 'Driver'])->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->rememberToken();
            $table->text('firebase_token')->nullable();
            $table->string('fb_id', 50)->unique()->nullable();
            $table->string('google_id', 50)->unique()->nullable();
            $table->string('apple_id')->unique()->nullable();
            $table->enum('status',['Active', 'Inactive','Pending','Car_details','Document_details'])->nullable();
            $table->enum('device_type',['1', '2'])->nullable();
            $table->text('device_id');
            $table->string('referral_code',12);
            $table->string('used_referral_code',12)->nullable();
            $table->string('currency_code',10)->nullable();
            $table->string('language',50);
            $table->integer('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('country')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'user_type']);
        });

        $statement = "ALTER TABLE users AUTO_INCREMENT = 10001;";

        DB::unprepared($statement);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
