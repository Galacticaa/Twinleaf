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
            $table->boolean('speed_scan')->default(false);
            $table->boolean('beehive')->default(false);
            $table->smallInteger('workers')->unsigned()->nullable();
            $table->smallInteger('workers_per_hive')->unsigned()->nullable();
            $table->smallInteger('scan_duration')->unsigned()->nullable();
            $table->smallInteger('rest_interval')->unsigned()->nullable();
            $table->tinyInteger('max_empty')->unsigned()->nullable();
            $table->tinyInteger('max_failures')->unsigned()->nullable();
            $table->tinyInteger('max_retries')->unsigned()->nullable();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('captcha_solving');
            $table->boolean('automatic_captchas')->default(false);
            $table->boolean('manual_captchas')->default(false);
            $table->tinyInteger('captcha_refresh')->nullable();
            $table->tinyInteger('captcha_timeout')->nullable();

            $table->tinyInteger('login_delay')->nullable();
            $table->tinyInteger('login_retries')->nullable();
            $table->boolean('altitude_cache')->default(false);
            $table->boolean('disable_version_check')->default(false);
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
            $table->boolean('captcha_solving')->nullable();
        });
    }
}
