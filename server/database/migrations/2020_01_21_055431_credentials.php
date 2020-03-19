<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Credentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->string('id', 128);
            $table->unsignedBigInteger('app_user_id')->comment('ユーザーID');
            $table->bigInteger('count')->comment('カウンター');
            $table->text('credential_id')->comment('認証ID');
            $table->text('public_key')->comment('公開鍵');
            $table->primary('app_user_id');
            $table->unique('app_user_id');
            $table->foreign('app_user_id')->references('id')->on('app_user');
        });
        /*
        // カラム属性指定に「VARBINARY」が存在しないので直接クエリを叩く
        DB::statement(
            'ALTER TABLE `credentials` ADD `public_key_cose` VARBINARY(500) AFTER `count`'
        ); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::statement('ALTER TABLE `credentials` DROP COLUMN `public_key_cose`');

        Schema::drop('credentials');
    }
}
