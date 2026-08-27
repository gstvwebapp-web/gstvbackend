<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->Integer('parentID')->default(0);
            $table->String('title');
            $table->String('slug');
            $table->String('featureImage')->nullable();
            $table->String('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('metatitle')->nullable();
            $table->string('metakeyword')->nullable();
            $table->string('metadesc')->nullable();
			$table->Integer('catOrder')->default(0);
            $table->Integer('eventCat')->default(0);  
            $table->Integer('setHome')->default(0);            
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
        Schema::dropIfExists('categories');
    }
}
