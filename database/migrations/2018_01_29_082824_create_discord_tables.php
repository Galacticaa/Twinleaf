<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscordTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discord_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->bigInteger('discord_id')->unsigned();
            $table->string('type');
            $table->integer('position');
            $table->string('parent_id');
            $table->timestamps();
        });

        Schema::create('discord_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->bigInteger('discord_id')->unsigned();
            $table->integer('position');
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
        Schema::dropIfExists('discord_roles');
        Schema::dropIfExists('discord_channels');
    }
}
