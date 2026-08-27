<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpapercategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epapercategory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->String('title');
            $table->String('engtitle');
            $table->String('slug');
            $table->String('featureImage')->nullable();
            $table->string('metatitle')->nullable();
            $table->string('metakeyword')->nullable();
            $table->string('metadesc')->nullable();
			$table->Integer('catOrder')->default(0);
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
        Schema::dropIfExists('epapercategory');
    }
}
