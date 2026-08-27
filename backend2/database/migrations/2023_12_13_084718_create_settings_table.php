<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->String('siteTitle');
            $table->String('email');
            $table->String('logo');
            $table->longText('headerScript')->nullable();
            $table->longText('bodyScript')->nullable();
            $table->longText('footerScript')->nullable();
            $table->longText('newsdefaulttxt')->nullable();
            $table->string('metatitle')->nullable();
            $table->string('metakeyword')->nullable();
            $table->string('metadesc')->nullable();
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
        Schema::dropIfExists('settings');
    }
}
