<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMxfVideosTable extends Migration
{
    public function up()
    {
        Schema::create('mxf_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            
            // Main video files
            $table->string('av_video')->nullable()->comment('AV.mxf main video');
            $table->json('ab_videos')->nullable()->comment('Multiple AB_1.mxf, AB_2.mxf, etc.');
            $table->string('one_to_one_video')->nullable()->comment('121.mxf');
            $table->string('walk_through_video')->nullable()->comment('WT.mxf');
            
            // Document/Text content
            $table->text('document_text')->nullable()->comment('Text from textarea or extracted from doc');
            $table->string('document_file')->nullable()->comment('Uploaded doc file path');
            
            // Voice over
            $table->string('voice_id')->default('ZsGLGrFQfgRJUAKsDtXX')->comment('ElevenLabs voice ID');
            $table->string('voice_over_audio')->nullable()->comment('Generated voice over audio path');
            
            // Final output
            $table->string('final_video')->nullable()->comment('Final compiled MP4 video');
            $table->integer('video_duration')->default(0)->comment('Final video duration in seconds');
            
            // Processing status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('progress')->default(0)->comment('Processing progress percentage');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mxf_videos');
    }
}
