<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CacheUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cache_user', function (Blueprint $table) {
            $table->string('cache_id', 255)->comment('キャッシュID');
            $table->text('cache_data')->comment('キャッシュDデータ');
            $table->primary('cache_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cache_user');
    }
}
