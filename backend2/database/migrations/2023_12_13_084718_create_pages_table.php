<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->Integer('parentID')->default(0);
            $table->String('title');
            $table->String('slug');
            $table->String('featureImage')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('pages');
    }
}
