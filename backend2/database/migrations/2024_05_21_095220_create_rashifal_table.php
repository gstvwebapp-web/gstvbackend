<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRashifalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rashifal', function (Blueprint $table) {
            $table->id();
            $table->Integer('user_id')->nullable();
            $table->String('title')->nullable();
            $table->String('engtitle')->nullable();
            $table->String('slug')->nullable();
            $table->String('rashiword')->nullable();
            $table->string('rashiicon')->nullable();
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
        Schema::dropIfExists('rashifal');
    }
}
