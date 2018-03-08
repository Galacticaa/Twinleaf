<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsageFieldsToProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proxies', function (Blueprint $table) {
            $table->boolean('for_scanning')->default(false);
            $table->boolean('for_creation')->default(false);
            $table->boolean('for_activation')->default(false);
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
            $table->dropColumn([
                'for_activation',
                'for_creation',
                'for_scanning',
            ]);
        });
    }
}
