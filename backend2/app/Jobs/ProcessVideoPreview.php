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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Image;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;

/**
 * ProcessVideoPreview Job
 * 
 * Optimized video preview generation with reduced FFmpeg executions
 * Part 1A: FFmpeg/FFProbe utilities
 * Part 1B: Gujarati text rendering and jacket creation
 * Part 2: Consolidated preview file generation (2 FFmpeg calls instead of 5+)
 * Part 3: Main handle() orchestration
 */
class ProcessVideoPreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $videoUrl,
        public int $isVerticalVideo = 0,
        public int $catID = 0,
        public ?int $videostart = 0,
        public string $title,
        public ?int $isJacket = 0,
        public ?string $voiceText
    ) {}

    // ============================================================================
    // PART 1A: FFmpeg/FFProbe Helper Functions
    // ============================================================================

    /**
     * Run FFmpeg command with error handling
     */
    private function runFFmpeg(string $command): string
    {
        Log::info("FFmpeg executing: " . substr($command, 0, 100) . "...");
        $output = shell_exec($command . " 2>&1");
        Log::info("FFmpeg output: " . ($output ? substr($output, 0, 200) : "success"));
        return $output ?? '';
    }

    /**
     * Run FFProbe to get video information
     */
    private function runFFProbe(string $command): string
    {
        return shell_exec($command . " 2>&1") ?? '';
    }

    /**
     * Get video information (dimensions, duration)
     */
    private function getVideoInfo(string $videoPath): array
    {
        // Get dimensions
        $dimensions = $this->runFFProbe(
            "/usr/bin/ffprobe -v error -select_streams v:0 " .
            "-show_entries stream=width,height " .
            "-of csv=p=0:s=x " . escapeshellarg($videoPath)
        );

        list($width, $height) = explode('x', trim($dimensions));

        // Get duration
        $duration = (int) $this->runFFProbe(
            "/usr/bin/ffprobe -v error -show_entries format=duration " .
            "-of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($videoPath)
        );

        return [
            'width'    => (int) $width,
            'height'   => (int) $height,
            'duration' => $duration
        ];
    }

    /**
     * Delete file if exists
     */
    private function deleteIfExists(string $filePath): void
    {
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Check if video is vertical (height > width)
     */
    private function isVerticalVideo(int $width, int $height): bool
    {
        return $height > $width;
    }

    /**
     * Get full video file path from URL
     */
    private function getVideoPath(string $videoUrl): string
    {
        $parsedPath = parse_url($videoUrl, PHP_URL_PATH);
        return dirname(dirname(dirname(dirname(__FILE__)))) . $parsedPath;
    }

    /**
     * Build output file paths for all preview assets
     */
    private function buildOutputFiles(string $dir, string $file): array
    {
        return [
            'webp'        => $dir . $file . '_video.webp',
            'gif'         => $dir . $file . '_video.gif',
            'gif_small'   => $dir . $file . '_video_small.gif',
            'jpg_small'   => $dir . $file . '_video_small.jpg',
            'jpg'         => $dir . $file . '_video.jpg',
            'vert_webp'   => $dir . $file . '_video_vert.webp'
        ];
    }

    // ============================================================================
    // PART 1B: Gujarati Text Rendering & Jacket Video Creation
    // ============================================================================

    /**
     * Render Gujarati text to PNG using ImageMagick
     */
    private function renderGujaratiTextPng(
        string $text,
        string $outputPng,
        int $fontSizePx = 38,
        string $fontPath = "",
        string $textColor = "black",
        ?string $bgColor = null,
        int $canvasWidth = 1920,
        int $canvasHeight = 120
    ): void {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_XML1);

        // Create temporary font config for Pango
        $fontDir = dirname($fontPath);
        $fontConfXml = '<?xml version="1.0"?><!DOCTYPE fontconfig SYSTEM "fonts.dtd"><fontconfig><dir>' . $fontDir . '</dir></fontconfig>';

        $tempFontConf = sys_get_temp_dir() . '/font_conf_' . uniqid() . '.conf';
        file_put_contents($tempFontConf, $fontConfXml);

        $markup = sprintf(
            '<span font="Achite" weight="bold" size="%dpt" foreground="%s">%s</span>',
            $fontSizePx,
            $textColor,
            $escaped
        );

        $bg = $bgColor ? escapeshellarg($bgColor) : "none";

        $cmd = "FONTCONFIG_FILE=" . escapeshellarg($tempFontConf) .
               " convert -background {$bg} -size {$canvasWidth}x {$canvasHeight} " .
               "-gravity center pango:" . escapeshellarg($markup) . " " .
               escapeshellarg($outputPng);

        shell_exec($cmd . " 2>&1");
        @unlink($tempFontConf);

        Log::info("Gujarati text PNG generated: {$outputPng}");
    }
private function compressVideo(string $videoPath, int $crf = 28, string $preset = 'medium'): bool
    {
        $dir = dirname($videoPath) . '/';
        $file = pathinfo($videoPath, PATHINFO_FILENAME);
        $compressedPath = $dir . $file . '_compressed.mp4';

        Log::info("🎬 Compressing video: {$videoPath}");

        // FFmpeg command optimized for high compression and web delivery
        $command = "/usr/bin/ffmpeg -y " .
            "-i " . escapeshellarg($videoPath) . " " .
            "-c:v libx264 " .
            "-preset " . $preset . " " .      // 'medium' gives the best speed-to-compression ratio
            "-crf " . $crf . " " .             // 28 = high compression with good visual quality
            "-maxrate 1.8M -bufsize 3.6M " .   // Prevents high bitrate spikes
            "-pix_fmt yuv420p " .
            "-c:a aac -b:a 96k " .             // Compress audio track to 96kbps AAC
            "-movflags +faststart " .          // Optimizes MP4 metadata for fast web streaming
            escapeshellarg($compressedPath);

        $this->runFFmpeg($command);

        if (file_exists($compressedPath) && filesize($compressedPath) > 0) {
            $originalSize = filesize($videoPath);
            $newSize = filesize($compressedPath);
            $savings = round((($originalSize - $newSize) / $originalSize) * 100, 2);

            $this->deleteIfExists($videoPath);
            rename($compressedPath, $videoPath);

            Log::info("✓ Video compression completed. Reduced file size by {$savings}%");
            return true;
        }

        Log::error("✗ Video compression failed, keeping original file.");
        return false;
    }
    /**
     * Create jacket video with Gujarati text overlay
     * Replaces the original video with jacketed version
     */
  private function createJacketVideo(string $videoPath, string $catSlug, string $jackettitle): bool
    {
        $dir = dirname($videoPath) . '/';
        $file = pathinfo($videoPath, PATHINFO_FILENAME);
        $tempVideo = $dir . $file . '_temp.mp4';

        $jacketPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/jacket/' . $catSlug . '.png';
        $fontPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/fonts/HindVadodara-Bold.ttf';

        if (!file_exists($jacketPath)) {
            Log::warning("Jacket not found: {$jacketPath}");
            return false;
        }

        $date = date('d-m-Y');
        $days = [
            'Sunday'    => 'રવિવાર',
            'Monday'    => 'સોમવાર',
            'Tuesday'   => 'મંગળવાર',
            'Wednesday' => 'બુધવાર',
            'Thursday'  => 'ગુરુવાર',
            'Friday'    => 'શુક્રવાર',
            'Saturday'  => 'શનિવાર',
        ];
        $dayGujarati = $days[date('l')];
        $dateText = $date . " | " . $dayGujarati;

        $dateTextPng = sys_get_temp_dir() . "/date_" . uniqid() . ".png";
        $titleTextPng = sys_get_temp_dir() . "/title_" . uniqid() . ".png";

        $this->renderGujaratiTextPng(
            $dateText,
            $dateTextPng,
            24,
            $fontPath,
            "white",
            "rgba(0,0,0,0.5)",
            340,
            60
        );

        $this->renderGujaratiTextPng(
            $jackettitle,
            $titleTextPng,
            60,
            $fontPath,
            "#800707",
            null,
            1920,
            120
        );

        // Get voice-over duration to trim video to match
        $voiceOverDuration = $this->getVoiceOverDuration();
        Log::info("🎬 Video will be trimmed to voice-over duration: {$voiceOverDuration} seconds");

        // FIX: Added `-stream_loop -1` before the video input `-i $videoPath`
        // This makes FFmpeg seamlessly loop the video if audio is longer than original video length.
        $command = "/usr/bin/ffmpeg -y " .
            "-stream_loop -1 -i " . escapeshellarg($videoPath) . " " .
            "-stream_loop -1 -i " . escapeshellarg($jacketPath) . " " .
            "-i " . escapeshellarg($dateTextPng) . " " .
            "-i " . escapeshellarg($titleTextPng) . " " .
            "-filter_complex " .
            "\"" .
            "[0:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,setsar=1[video];" .
            "[1:v]scale=1920:1080[jacket];" .
            "[video][jacket]overlay=0:0[tmp1];" .
            "[tmp1][2:v]overlay=x=55:y=125[tmp2];" .
            "[tmp2][3:v]overlay=x=0:y=915[out]" .
            "\" " .
            "-map \"[out]\" " .
            "-map 0:a? " .
            "-c:v libx264 " .
            "-preset ultrafast " .
            "-crf 28 " .
            "-pix_fmt yuv420p " .
            "-c:a aac " .
            "-b:a 128k " .
            "-t " . $voiceOverDuration . " " .
            escapeshellarg($tempVideo);

        $this->runFFmpeg($command);

        $this->deleteIfExists($dateTextPng);
        $this->deleteIfExists($titleTextPng);

        if (file_exists($tempVideo) && filesize($tempVideo) > 0) {
            $this->deleteIfExists($videoPath);
            rename($tempVideo, $videoPath);
            Log::info("✓ Jacket video created and TRIMMED to {$voiceOverDuration} seconds");
            return true;
        } else {
            Log::error("✗ Jacket creation failed");
            return false;
        }
    }
    
    private function createJacketVideoWithoutVoice(string $videoPath, string $catSlug, string $jackettitle): bool
    {
        $dir = dirname($videoPath) . '/';
        $file = pathinfo($videoPath, PATHINFO_FILENAME);
        $tempVideo = $dir . $file . '_temp.mp4';

        $jacketPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/jacket/' . $catSlug . '.png';
        $fontPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/fonts/HindVadodara-Bold.ttf';

        if (!file_exists($jacketPath)) {
            Log::warning("Jacket file not found: {$jacketPath}");
            return false;
        }

        // Generate Gujarati text PNGs
        $date = date('d-m-Y');
        $days = [
            'Sunday'    => 'રવિવાર',
            'Monday'    => 'સોમવાર',
            'Tuesday'   => 'મંગળવાર',
            'Wednesday' => 'બુધવાર',
            'Thursday'  => 'ગુરુવાર',
            'Friday'    => 'શુક્રવાર',
            'Saturday'  => 'શનિવાર',
        ];
        $dayGujarati = $days[date('l')];
        $dateText = $date . " | " . $dayGujarati;

        $dateTextPng = sys_get_temp_dir() . "/date_" . uniqid() . ".png";
        $titleTextPng = sys_get_temp_dir() . "/title_" . uniqid() . ".png";

        // Render date text (24px, white, semi-transparent black background)
        $this->renderGujaratiTextPng(
            $dateText,
            $dateTextPng,
            24,
            $fontPath,
            "white",
            "rgba(0,0,0,0.5)",
            340,
            60
        );

        // Render title text (60px, dark red, no background)
        $this->renderGujaratiTextPng(
            $jackettitle,
            $titleTextPng,
            60,
            $fontPath,
            "#800707",
            null,
            1920,
            120
        );

        // Build FFmpeg command for jacket video
        $command = "/usr/bin/ffmpeg -y " .
            "-i " . escapeshellarg($videoPath) . " " .
            "-stream_loop -1 -i " . escapeshellarg($jacketPath) . " " .
            "-i " . escapeshellarg($dateTextPng) . " " .
            "-i " . escapeshellarg($titleTextPng) . " " .
            "-filter_complex " .
            "\"" .
            "[0:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,setsar=1[video];" .
            "[1:v]scale=1920:1080[jacket];" .
            "[video][jacket]overlay=0:0[tmp1];" .
            "[tmp1][2:v]overlay=x=55:y=125[tmp2];" .
            "[tmp2][3:v]overlay=x=0:y=915[out]" .
            "\" " .
            "-map \"[out]\" " .
            "-map 0:a? " .
            "-c:v libx264 " .
            "-preset fast " .
            "-crf 23 " .
            "-pix_fmt yuv420p " .
            "-c:a aac " .
            "-b:a 128k " .
            "-shortest " .
            escapeshellarg($tempVideo);

        $result = $this->runFFmpeg($command);

        // Cleanup text PNGs
        $this->deleteIfExists($dateTextPng);
        $this->deleteIfExists($titleTextPng);

        // Verify and replace original
        if (file_exists($tempVideo) && filesize($tempVideo) > 0) {
            $this->deleteIfExists($videoPath);
            rename($tempVideo, $videoPath);
            Log::info("Jacket video created successfully: {$videoPath}");
            return true;
        } else {
            Log::error("Jacket video generation failed.");
            return false;
        }
    }
    private function generateVoiceOverAudio(string $gujaratiText, string $outputAudioPath): bool
    {
        try {
            Log::info("🔊 Generating voice-over for text...");

            // Initialize Google Cloud TTS Client (v1.8 API)
            $client = new TextToSpeechClient([
                'credentials' => storage_path('app/google-tts.json')
            ]);

            // Create synthesis input
            $input = new \Google\Cloud\TextToSpeech\V1\SynthesisInput();
            $input->setText($gujaratiText);

            // Select Gujarati female voice
            $voice = new VoiceSelectionParams();
            $voice->setLanguageCode('gu-IN');  // Gujarati - India
            $voice->setSsmlGender(\Google\Cloud\TextToSpeech\V1\SsmlVoiceGender::FEMALE);
            $voice->setName('gu-IN-Wavenet-A');  // Google's high-quality female voice

            // Configure audio encoding
            $audioConfig = new AudioConfig();
            $audioConfig->setAudioEncoding(AudioEncoding::MP3);
            $audioConfig->setPitch(1.0);              // Normal pitch
            $audioConfig->setSpeakingRate(0.9);       // 90% speed (natural paced)

            // Call TTS API - v1.8 GAPIC signature: (input, voice, audioConfig)
            $response = $client->synthesizeSpeech($input, $voice, $audioConfig);
            $audioContent = $response->getAudioContent();

            // Save audio to file
            if (file_put_contents($outputAudioPath, $audioContent)) {
                Log::info("✓ Voice-over generated: {$outputAudioPath}");
                $client->close();
                return true;
            } else {
                Log::error("✗ Failed to save voice-over audio");
                $client->close();
                return false;
            }

        } catch (\Exception $e) {
            Log::error("✗ TTS Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mix voice-over audio with video using FFmpeg
     * Use ONLY voice-over audio (ignore original video audio)
     * This ensures video duration matches audio duration
     */
    private function mixVoiceOverWithVideo(string $videoPath, string $audioPath, string $outputVideoPath): bool
    {
        try {
            Log::info("🎬 Mixing voice-over with video...");

            // Get video duration
            $videoDuration = (int) $this->runFFProbe(
                "/usr/bin/ffprobe -v error -show_entries format=duration " .
                "-of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($videoPath)
            );

            Log::info("📹 Video duration: {$videoDuration} seconds");

            // IMPORTANT: Use ONLY voice-over audio, NOT original video audio
            // This ensures the final video has same duration as voice-over
            // FFmpeg command - simple copy: video from first input, audio from second input
            $command = "/usr/bin/ffmpeg -y " .
                "-i " . escapeshellarg($videoPath) . " " .
                "-i " . escapeshellarg($audioPath) . " " .
                "-map 0:v:0 " .
                "-map 1:a:0 " .
                "-c:v copy " .
                "-c:a aac " .
                "-b:a 128k " .
                "-shortest " .
                escapeshellarg($outputVideoPath);

            $output = $this->runFFmpeg($command);

            if (file_exists($outputVideoPath) && filesize($outputVideoPath) > 0) {
                Log::info("✓ Voice-over mixed successfully (audio is primary source)");
                return true;
            } else {
                Log::error("✗ Failed to mix voice-over with video");
                Log::error("FFmpeg output: " . substr($output, 0, 200));
                return false;
            }

        } catch (\Exception $e) {
            Log::error("✗ Audio mixing error: " . $e->getMessage());
            return false;
        }
    }
    private function getVoiceOverText(): string
    {
        // Default Gujarati voice-over text about Trump and Netanyahu
        return trim($this->voiceText ?? '');
        // return "અમેરિકાના પ્રમુખ ડોનાલ્ડ ટ્રમ્પે સોમવારે સ્પષ્ટ કર્યું હતું કે ઇઝરાયેલના વડાપ્રધાન બેન્જામિન નેતન્યાહૂની અમેરિકી ધરતી પર કોઇપણ સંજોગોમાં ધરપકડ કરવામાં આવશે નહીં. ટ્રમ્પનું આ નિવેદન ન્યૂયોર્ક સિટીના મેયર ઝોહરાન મમદાનીના એ નિવેદન પછી આવ્યું છે, જેમાં તેમણે કહ્યું હતું કે જો નેતન્યાહૂ ન્યૂયોર્ક આવે છે તો તેમની ધરપકડના કાનૂની વિકલ્પો પર વિચાર કરવામાં આવી રહ્યો છે. મમદાનીએ ધ ન્યૂયોર્ક ટાઇમ્સ સાથેની વાતચીતમાં નેતન્યાહૂને યુદ્ધ અપરાધી ગણાવ્યા હતા અને કહ્યું હતું કે તેમનું સ્થાન હેગ આઇસીસીમાં છે.";
    }
    private function getVoiceOverDuration(): int
    {
        $text = $this->getVoiceOverText();
        $charCount = strlen(trim($text));
        
        // Gujarati text estimation:
        // At 0.9 speaking rate (90% speed):
        // ~160 characters = ~10 seconds
        // Approximate: 16 chars per second
        $estimatedSeconds = ceil($charCount / 16);
        
        Log::info("📊 Voice-over text length: {$charCount} chars ≈ {$estimatedSeconds} seconds");
        
        return $estimatedSeconds;
    }
    // ============================================================================
    // PART 2: Consolidated Preview Files Generation (2 FFmpeg Executions)
    // ============================================================================

    /**
     * Generate all preview files efficiently
     * Uses single filter_complex with multiple outputs - OPTIMIZED for speed
     */
    private function generatePreviewFiles(
        string $videoPath,
        string $dir,
        string $file,
        array $outputFiles,
        int $startTime,
        int $previewDuration,
        bool $isVertical
    ): void {
        $start = $startTime;
        $t = $previewDuration;

        // ===== FFmpeg Execution 1: Animation assets (webp, gif, gif_small) =====
        // Generate animated preview formats from video clip
        $filterComplex1 = "[0:v]split=3[v1][v2][v3];" .
            "[v1]scale=480:-1,fps=10[webp];" .
            "[v2]scale=480:-1,fps=10[gif];" .
            "[v3]scale=320:-1,fps=8[smallgif]";

        $cmd1 = "/usr/bin/ffmpeg -y " .
            "-ss {$start} " .
            "-t {$t} " .
            "-i " . escapeshellarg($videoPath) . " " .
            "-filter_complex '{$filterComplex1}' " .
            "-map '[webp]' -c:v libwebp -lossless 0 -q:v 80 -loop 0 " . escapeshellarg($outputFiles['webp']) . " " .
            "-map '[gif]' -c:v gif " . escapeshellarg($outputFiles['gif']) . " " .
            "-map '[smallgif]' -c:v gif " . escapeshellarg($outputFiles['gif_small']);

        $this->runFFmpeg($cmd1);
        Log::info("Animation assets generated (webp, gif, gif_small)");

        // ===== FFmpeg Execution 2: High-quality JPG thumbnail (jpg_small) =====
        // Extract single frame at 640x360 - CLEAN, NO BLUR
        $cmd2 = "/usr/bin/ffmpeg -y " .
            "-ss {$start} " .
            "-i " . escapeshellarg($videoPath) . " " .
            "-vframes 1 " .
            "-vf \"scale=640:360:force_original_aspect_ratio=decrease\" " .
            "-c:v mjpeg " .
            "-q:v 4 " .
            escapeshellarg($outputFiles['jpg_small']);

        $this->runFFmpeg($cmd2);
        Log::info("High-quality JPG thumbnail generated (jpg_small) - CLEAN");

        // ===== FFmpeg Execution 3: Full-size JPG (jpg) - CLEAN, NO BLUR =====
        // Extract single frame without any blur effect
        $cmd3 = "/usr/bin/ffmpeg -y " .
            "-ss {$start} " .
            "-i " . escapeshellarg($videoPath) . " " .
            "-vframes 1 " .
            "-c:v mjpeg " .
            "-q:v 4 " .
            escapeshellarg($outputFiles['jpg']);

        $this->runFFmpeg($cmd3);
        Log::info("Full-size JPG generated (jpg) - CLEAN");

        // ===== FFmpeg Execution 4: Vertical preview (vert_webp) =====
        // For vertical videos: simple scale + pad, no blur
        // For horizontal videos: scale + blur background + overlay (for aesthetic)
        if ($isVertical) {
            $verticalFilter = "scale=320:570:force_original_aspect_ratio=decrease," .
                "pad=320:570:(ow-iw)/2:(oh-ih)/2";
            
            $cmd4 = "/usr/bin/ffmpeg -y " .
                "-ss {$start} " .
                "-i " . escapeshellarg($videoPath) . " " .
                "-vframes 1 " .
                "-vf \"{$verticalFilter}\" " .
                "-c:v libwebp -lossless 0 -q:v 80 " .
                escapeshellarg($outputFiles['vert_webp']);
        } else {
            // For horizontal: create blurred background version for aesthetic vertical preview
            $verticalFilter = "[0:v]scale=320:570:force_original_aspect_ratio=increase," .
                "boxblur=20:10,crop=320:570[bg];" .
                "[0:v]scale=320:570:force_original_aspect_ratio=decrease[fg];" .
                "[bg][fg]overlay=(W-w)/2:(H-h)/2";
            
            $cmd4 = "/usr/bin/ffmpeg -y " .
                "-ss {$start} " .
                "-i " . escapeshellarg($videoPath) . " " .
                "-vframes 1 " .
                "-filter_complex \"{$verticalFilter}\" " .
                "-c:v libwebp -lossless 0 -q:v 80 " .
                escapeshellarg($outputFiles['vert_webp']);
        }

        $this->runFFmpeg($cmd4);
        Log::info("Vertical preview asset generated (vert_webp)");
    }

    // ============================================================================
    // PART 3: Main Handle Function - Orchestrates All Operations
    // ============================================================================

    /**
     * Main job handler
     * Orchestrates video preview generation with category-specific logic
     * OPTIMIZED: Fast processing with minimal FFmpeg calls
     */
    public function handle(): void
    {
        try {
            // Get category slug
            $category = DB::table('categories')
                ->select('slug')
                ->where('id', $this->catID)
                ->first();

            $catSlug = $category ? $category->slug : '';

            // Get video path
            $videoPath = $this->getVideoPath($this->videoUrl);

            if (!file_exists($videoPath)) {
                Log::error("Video file not found: {$videoPath}");
                return;
            }

            $dir = dirname($videoPath) . '/';
            $file = pathinfo($videoPath, PATHINFO_FILENAME);

            // Get video information (single FFProbe call)
            $videoInfo = $this->getVideoInfo($videoPath);
            $width = $videoInfo['width'];
            $height = $videoInfo['height'];
            $isVertical = $this->isVerticalVideo($width, $height);

            $startTime = $this->videostart ?? 10;
            $previewDuration = 3;

            // ===== JACKET VIDEO CREATION (Optional, before category 149 logic) =====
            if ($this->isJacket == 1) {
                Log::info("Creating jacket video for category: {$this->catID}");
                $jackettitle = trim($this->title);
                if (!empty(trim($this->voiceText ?? ''))) {
                    $this->createJacketVideo($videoPath, $catSlug, $jackettitle);
                    Log::info("🔊 Generating voice-over audio...");
                    $voiceOverText = $this->getVoiceOverText();
                    $audioPath = $dir . $file . '_voiceover.mp3';
                    
                    if ($this->generateVoiceOverAudio($voiceOverText, $audioPath)) {
                        // ===== MIX VOICE-OVER WITH VIDEO =====
                        Log::info("🎬 Mixing voice-over with jacket video...");
                        $tempVideoWithAudio = $dir . $file . '_with_audio.mp4';
                        
                        if ($this->mixVoiceOverWithVideo($videoPath, $audioPath, $tempVideoWithAudio)) {
                            // Replace original video with audio-mixed version
                            $this->deleteIfExists($videoPath);
                            rename($tempVideoWithAudio, $videoPath);
                            Log::info("✓ Voice-over audio mixed with jacket video");
                        } else {
                            Log::warning("⚠ Failed to mix voice-over, continuing without audio");
                        }
                        
                        // Clean up temporary audio file
                        $this->deleteIfExists($audioPath);
                    } else {
                        Log::warning("⚠ Voice-over generation failed, continuing without audio");
                    }
                } else {
                    $this->createJacketVideoWithoutVoice($videoPath, $catSlug, $jackettitle);
                }

                // Refresh video info after jacket processing
                $videoInfo = $this->getVideoInfo($videoPath);
                $width = $videoInfo['width'];
                $height = $videoInfo['height'];
                $isVertical = $this->isVerticalVideo($width, $height);
                
                Log::info("Jacket video created successfully");
            }
            $this->compressVideo($videoPath, 28, 'medium');
            // ===== CATEGORY 149 SPECIAL HANDLING =====
            if ($this->catID == 149) {
                Log::info("Category 149 detected - generating single webp preview FAST");

                $outputFiles = $this->buildOutputFiles($dir, $file);
                $this->deleteIfExists($outputFiles['webp']);

                if ($isVertical) {
                    $verticalFilter = "scale=320:570:force_original_aspect_ratio=decrease," .
                        "pad=320:570:(ow-iw)/2:(oh-ih)/2";
                    $cmd = "/usr/bin/ffmpeg -y " .
                        "-ss {$startTime} " .
                        "-i " . escapeshellarg($videoPath) . " " .
                        "-vframes 1 " .
                        "-vf \"{$verticalFilter}\" " .
                        "-c:v libwebp -lossless 0 -q:v 80 " .
                        escapeshellarg($outputFiles['webp']);
                } else {
                    $verticalFilter = "[0:v]scale=320:570:force_original_aspect_ratio=increase," .
                        "boxblur=20:10,crop=320:570[bg];" .
                        "[0:v]scale=320:570:force_original_aspect_ratio=decrease[fg];" .
                        "[bg][fg]overlay=(W-w)/2:(H-h)/2";
                    $cmd = "/usr/bin/ffmpeg -y " .
                        "-ss {$startTime} " .
                        "-i " . escapeshellarg($videoPath) . " " .
                        "-vframes 1 " .
                        "-filter_complex \"{$verticalFilter}\" " .
                        "-c:v libwebp -lossless 0 -q:v 80 " .
                        escapeshellarg($outputFiles['webp']);
                }

                $this->runFFmpeg($cmd);
                Log::info("Category 149 preview generated - FAST");
                return;
            }

            // ===== NORMAL PREVIEW GENERATION (All other categories) =====
            $outputFiles = $this->buildOutputFiles($dir, $file);

            // Cleanup existing previews to avoid conflicts
            foreach ($outputFiles as $outputFile) {
                $this->deleteIfExists($outputFile);
            }

            // Generate all preview files with optimized 3 FFmpeg executions
            $this->generatePreviewFiles(
                $videoPath,
                $dir,
                $file,
                $outputFiles,
                $startTime,
                $previewDuration,
                $isVertical
            );

            Log::info("✓ Video preview generation completed SUCCESSFULLY in {$videoPath}");

        } catch (\Exception $e) {
            Log::error("Error in ProcessVideoPreview: " . $e->getMessage());
            throw $e;
        }
    }
}
