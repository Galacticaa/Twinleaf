<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProxyCheckTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proxies', function (Blueprint $table) {
            $table->datetime('checked_at')->nullable();
            $table->string('ptc_status')->nullable()->after('ptc_ban');
            $table->string('pogo_status')->nullable()->after('pogo_ban');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proxies', function (Blueprint $table) {
            $table->dropColumn(['pogo_status', 'ptc_status', 'checked_at']);
        });
    }
}
