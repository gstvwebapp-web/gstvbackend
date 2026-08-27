<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->String('catID');
            $table->text('title');
            $table->text('englishTitle');
            $table->text('slug');
            $table->String('featureImage')->nullable();
            $table->String('imageURL')->nullable();
            $table->String('uploadVideo')->nullable();
            $table->String('vidoeURL')->nullable();
            $table->String('scheduleDate')->nullable();
            $table->String('tags')->nullable();
            $table->longText('description')->nullable();
            $table->string('metatitle')->nullable();
            $table->string('metakeyword')->nullable();
            $table->string('metadesc')->nullable();
            $table->String('is_home')->nullable();
            $table->String('related_news')->nullable();
            $table->integer('viewer')->nullable();
            $table->unsignedBigInteger('newsOrder')->default(0);
            $table->unsignedBigInteger('topnewsOrder')->default(0);
            $table->String('status');
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
        Schema::dropIfExists('news');
    }
}
