<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScanOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_areas', function (Blueprint $table) {
            $table->tinyInteger('radius')->unsigned()->nullable();
            $table->tinyInteger('db_threads')->unsigned()->nullable();
            $table->boolean('speed_scan')->nullable();
            $table->boolean('beehive')->nullable();
            $table->smallInteger('workers')->unsigned()->nullable();
            $table->smallInteger('workers_per_hive')->unsigned()->nullable();
            $table->smallInteger('scan_duration')->unsigned()->nullable();
            $table->smallInteger('rest_interval')->unsigned()->nullable();
            $table->tinyInteger('max_empty')->unsigned()->nullable();
            $table->tinyInteger('max_failures')->unsigned()->nullable();
            $table->tinyInteger('max_retries')->unsigned()->nullable();
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
            $table->dropColumn([
                'max_retries', 'max_failures', 'max_empty',
                'rest_interval', 'scan_duration',
                'workers_per_hive', 'workers',
                'beehive', 'speed_scan',
                'db_threads', 'radius',
            ]);
        });
    }
}
