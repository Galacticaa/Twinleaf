<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartAndUptimeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->datetime('started_at')->nullable();
            $table->integer('uptime_max')->default(0);
        });

        Schema::table('map_areas', function (Blueprint $table) {
            $table->datetime('started_at')->nullable();
            $table->integer('uptime_max')->default(0);
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
            $table->dropColumn(['uptime_max', 'started_at']);
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn(['uptime_max', 'started_at']);
        });
    }
}
