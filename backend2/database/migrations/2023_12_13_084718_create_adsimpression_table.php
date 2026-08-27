<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdsimpressionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adsimpression', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->String('image')->nullable();
            $table->String('ip')->nullable();
            $table->Integer('view')->nullable();
            $table->Integer('click')->nullable();
            $table->String('pageurl')->nullable();
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
        Schema::dropIfExists('adsimpression');
    }
}
