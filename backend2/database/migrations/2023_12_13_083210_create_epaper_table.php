<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpaperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epaper', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->Integer('ecatID');
            $table->String('etitle');
            $table->String('engtitle');
            $table->String('slug');
            $table->String('featureImage')->nullable();
            $table->String('featurePDF')->nullable();
            $table->string('metatitle')->nullable();
            $table->string('metakeyword')->nullable();
            $table->string('metadesc')->nullable();
            $table->String('status')->default('Active');
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
        Schema::dropIfExists('epaper');
    }
}
