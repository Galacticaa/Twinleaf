<?php

use Twinleaf\Discord\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscordConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discord_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bot_token')->nullable();
            $table->bigInteger('guild_id')->nullable();
            $table->timestamps();
        });

        Config::create();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_config');
    }
}
