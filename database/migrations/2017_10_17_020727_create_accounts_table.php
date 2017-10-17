<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('map_area_id')->unsigned()->nullable();
            $table->string('username', 20);
            $table->string('password');
            $table->string('email');
            $table->date('birthday');
            $table->string('country', 2);
            $table->boolean('is_blind')->nullable();
            $table->boolean('is_banned')->nullable();
            $table->dateTime('registered_at')->nullable();
            $table->dateTime('activated_at')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
