<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->integer('map_area_id')->unsigned()->nullable();
            $table->boolean('ptc_ban')->nullable();
            $table->boolean('pogo_ban')->nullable();
            $table->timestamps();
        });

        Schema::table('map_areas', function (Blueprint $table) {
            $table->integer('proxy_target')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('map_areas', function (Blueprint $table) {
            $table->dropColumn('proxy_target');
        });

        Schema::dropIfExists('proxies');
    }
}
