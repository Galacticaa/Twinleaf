<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetadataToMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('description')->default('')->after('code');
            $table->string('image_url')->default('/static/cover.png')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn('image_url');
            $table->dropColumn('description');
        });
    }
}
