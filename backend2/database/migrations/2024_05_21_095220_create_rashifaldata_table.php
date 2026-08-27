<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRashifaldataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rashifaldata', function (Blueprint $table) {
            $table->id();
            $table->Integer('user_id')->nullable();
            $table->String('rashifal_id')->nullable();
            $table->String('authorname')->nullable();
            $table->String('authoricon')->nullable();
            $table->String('slug')->nullable();
            $table->text('rashidescription')->nullable();
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
        Schema::dropIfExists('rashifaldata');
    }
}
