<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPokestopColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('map_areas', function (Blueprint $table) {
            $table->boolean('spin_pokestops')->default(true)->nullable();
            $table->integer('max_stop_spins')->default(0)->nullable();
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
                'max_stop_spins',
                'spin_pokestops',
            ]);
        });
    }
}
