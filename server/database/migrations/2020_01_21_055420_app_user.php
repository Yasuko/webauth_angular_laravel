<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 255)->comment('ユーザー名');
            $table->string('userid', 255)->comment('ユーザーID');
            $table->timestamp('registration_start')->useCurrent()->comment('登録開始時間');
            $table->string('registration_token', 255)->useCurrent()->comment('登録Id');
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('app_user');
    }
}
