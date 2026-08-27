<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameresultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gameresults', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID')->nullable();
            $table->Integer('votegameID');
            $table->String('selectedAnswer');
            $table->String('IPaddress')->nullable();
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
        Schema::dropIfExists('gameresults');
    }
}
