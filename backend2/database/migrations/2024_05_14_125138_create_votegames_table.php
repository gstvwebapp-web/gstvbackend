<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVotegamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('votegames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('userID');
            $table->String('question');
            $table->text('answerOption');
            $table->text('gameDetail')->nullable();
            $table->String('whowin')->nullable();
            $table->String('voteforwin')->nullable();
            $table->String('totalvote')->nullable();
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
        Schema::dropIfExists('votegames');
    }
}
