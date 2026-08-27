<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Image;

class ProcessVideoPreviewgif implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $path,
        public string $video,
        public string $name,
        public string $code,
        public ?int $videostart = 0
    ) {}

    public function handle()
    {
        $videoPath = $this->path . $this->video;
 
        $jpg  = "{$this->name}_{$this->code}_video_small.jpg";
 
        $jpgPath  = $this->path . $jpg;

        // ===== detect duration =====
        $duration = (int) shell_exec(
            "/usr/bin/ffprobe -v error -show_entries format=duration ".
            "-of default=noprint_wrappers=1:nokey=1 {$videoPath}"
        );

        //$start = $duration > 10 ? 10 : 0;
        if($this->videostart != "0" || $this->videostart != 0 ){
            $start = $this->videostart;
        } else {
            $start = 1;
        }

       
        // ===== JPG FRAME =====
        // shell_exec(
        //     "/usr/bin/ffmpeg -y -ss {$start} -i {$videoPath} ".
        //     "-vframes 1 -vf 'scale=320:-1' {$jpgPath}"
        // );

        shell_exec(
    "/usr/bin/ffmpeg -y -ss {$start} -i {$videoPath} -vframes 1 " .
    "-filter_complex \"[0:v]scale=640:360:force_original_aspect_ratio=increase,boxblur=20:10,crop=640:360[bg];" .
    "[0:v]scale=640:360:force_original_aspect_ratio=decrease[fg];" .
    "[bg][fg]overlay=(W-w)/2:(H-h)/2\" {$jpgPath}"
);

    }
}