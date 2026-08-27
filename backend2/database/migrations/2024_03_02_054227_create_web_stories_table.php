<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webstory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->String('catID')->nullable();
            $table->text('title');
            $table->text('englishTitle');
            $table->text('slug');
            $table->json('Story_data');
          	$table->integer('viewer')->nullable();
            $table->String('status');
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
        Schema::dropIfExists('webstory');
    }
}
