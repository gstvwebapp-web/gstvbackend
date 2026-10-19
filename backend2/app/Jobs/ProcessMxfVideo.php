<?php

namespace App\Jobs;

use App\Models\MxfVideo;
use App\Services\SarvamService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ProcessMxfVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1; // Don't retry on failure

    protected $mxfVideoId;

    public function __construct($mxfVideoId)
    {
        $this->mxfVideoId = $mxfVideoId;
    }

    public function handle()
    {
        $video = MxfVideo::find($this->mxfVideoId);

        if (!$video) {
            Log::error("MXF Video not found: {$this->mxfVideoId}");
            return;
        }

        try {
            Log::info("🎬 Starting MXF video processing for ID: {$video->id}");
            
            // Update status to processing
            $video->update(['status' => 'processing', 'progress' => 0]);

            // Step 1: Generate voice-over from document text (10%)
            $this->updateProgress($video, 10, 'Generating voice-over...');
            $voiceOverPath = $this->generateVoiceOver($video);

            // Step 2: Get video durations and calculate segments (20%)
            $this->updateProgress($video, 20, 'Analyzing videos...');
            $segments = $this->calculateVideoSegments($video, $voiceOverPath);

            // Step 3: Create temporary video segments (40%)
            $this->updateProgress($video, 40, 'Creating video segments...');
            $segmentFiles = $this->createVideoSegments($video, $segments, $voiceOverPath);

            // Step 4: Concatenate all segments into final video (80%)
            $this->updateProgress($video, 80, 'Merging videos...');
            $finalVideoData = $this->concatenateVideos($segmentFiles, $video);
            
            // Extract URL and local path
            $finalVideo = $finalVideoData['url'];
            $finalVideoLocal = $finalVideoData['local_path'];

            // Step 5: Cleanup and finalize (100%)
            $this->updateProgress($video, 95, 'Cleaning up...');
            $this->cleanup($segmentFiles, $voiceOverPath);

            // Get final video duration
            $duration = $this->getVideoDuration($finalVideoLocal);

            // Step 6: Mark video as completed FIRST (for faster user experience)
            // Then apply jacket asynchronously if requested
            Log::info("📊 Marking video as completed with duration: {$duration}s");
            
            $video->update([
                'final_video' => $finalVideo,
                'video_duration' => $duration,
                'status' => 'completed',
                'progress' => 100,
            ]);

            Log::info("✅ MXF video processing completed for ID: {$video->id}");

            // Apply jacket AFTER marking complete (non-blocking for user)
            if ($video->is_jacket == 1 && $video->category_id) {
                try {
                    Log::info("🎨 Starting post-completion jacket application...");
                    Log::info("📌 Jacket enabled: Yes, Category ID: {$video->category_id}");
                    Log::info("📁 Local video path: {$finalVideoLocal}");
                    Log::info("🔍 File exists: " . (file_exists($finalVideoLocal) ? 'YES' : 'NO'));
                    
                    if (file_exists($finalVideoLocal)) {
                        Log::info("✓ Video file found, applying jacket...");
                        
                        if ($this->applyJacket($finalVideoLocal, $video->category_id, $video->title)) {
                            Log::info("✅ Category jacket applied successfully");
                            // Update duration after jacket (may change slightly)
                            $newDuration = $this->getVideoDuration($finalVideoLocal);
                            if ($newDuration != $duration) {
                                $video->update(['video_duration' => $newDuration]);
                                Log::info("📊 Duration updated: {$duration}s → {$newDuration}s");
                            }
                        } else {
                            Log::warning("⚠️ Jacket application failed, video completed without jacket");
                        }
                    } else {
                        Log::error("❌ Final video file not found for jacket: {$finalVideoLocal}");
                        Log::error("💡 Original URL: {$finalVideo}");
                    }
                } catch (\Exception $jacketException) {
                    // Jacket failure is non-critical since video is already complete
                    Log::error("❌ Jacket application EXCEPTION (non-fatal): " . $jacketException->getMessage());
                    Log::error("Stack trace: " . $jacketException->getTraceAsString());
                }
            } else {
                if ($video->is_jacket == 1) {
                    Log::info("ℹ️ Jacket requested but no category selected (category_id: " . ($video->category_id ?? 'NULL') . ")");
                } else {
                    Log::info("ℹ️ Jacket not requested (is_jacket: " . $video->is_jacket . ")");
                }
            }

        } catch (\Exception $e) {
            Log::error("❌ MXF video processing failed for ID: {$video->id}");
            Log::error("Error: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());

            $video->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate voice-over audio from document text using Sarvam AI
     */
    private function generateVoiceOver($video)
    {
        if (empty($video->document_text)) {
            Log::info("No document text, skipping voice-over generation");
            return null;
        }

        try {
            $sarvamService = new SarvamService();
            
            // Create year/month/date folder structure for voice-over
            $now = Carbon::now();
            $year = $now->format('Y');
            $month = $now->format('m');
            $date = $now->format('d');
            
            // Get document root and construct proper path
            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(dirname(__DIR__)));
            $voiceDir = rtrim($documentRoot, '/') . '/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date;
            
            Log::info("📁 Voice-over directory: {$voiceDir}");
            
            if (!File::exists($voiceDir)) {
                File::makeDirectory($voiceDir, 0755, true);
                Log::info("✅ Created directory: {$voiceDir}");
            }
            
            $filename = 'voice_' . $video->id . '_' . time() . '.mp3';
            $outputPath = $voiceDir . '/' . $filename;
            
            Log::info("💾 Voice-over path: {$outputPath}");
            Log::info("🎤 Generating voice-over with Sarvam AI");
            Log::info("📝 Text length: " . strlen($video->document_text) . " characters");

            // Use the voice_id directly (it's already the correct Sarvam voice ID)
            // Valid Gujarati voices: pooja_gu_conversational, pooja_gu_customer, bhavik_gu_conversation
            $sarvamVoiceId = $video->voice_id ?? 'pooja_gu_conversational';
            
            Log::info("🎙️ Using Sarvam voice: {$sarvamVoiceId}");

            $success = $sarvamService->generateSpeechChunked(
                $video->document_text,
                $outputPath,
                500,  // Chunk size (characters)
                $sarvamVoiceId
            );

            if ($success && file_exists($outputPath)) {
                // Store as full URL
                $voiceUrl = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$filename;
                
                $video->update(['voice_over_audio' => $voiceUrl]);
                
                Log::info("✅ Voice-over generated with Sarvam AI: {$voiceUrl}");
                return $outputPath; // Return local path for FFmpeg
            }

            Log::warning("⚠️ Voice-over generation failed");
            return null;

        } catch (\Exception $e) {
            Log::error("❌ Voice-over error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate video segments based on document timing
     * Sequence: AV+Voice → 1sec gap → AB videos → 121 → WT
     */
    private function calculateVideoSegments($video, $voiceOverPath)
    {
        $segments = [];

        // Get voice-over duration if available
        $voiceOverDuration = $voiceOverPath ? $this->getVideoDuration($voiceOverPath) : 0;
        
        Log::info("🎬 Voice-over duration: {$voiceOverDuration}s");

        // Segment 1: AV video with voice-over overlay
        if ($video->av_video) {
            $avPath = $this->urlToLocalPath($video->av_video);
            
            if (!file_exists($avPath)) {
                Log::error("❌ AV video file not found: {$avPath}");
                throw new \Exception("AV video file not found at: {$avPath}");
            }
            
            $avDuration = $this->getVideoDuration($avPath);
            
            // AV segment duration = voice-over duration (will loop or trim as needed)
            $segmentDuration = $voiceOverDuration > 0 ? $voiceOverDuration : $avDuration;
            
            $segments[] = [
                'type' => 'av_with_voice',
                'video' => $avPath,
                'audio' => $voiceOverPath,
                'duration' => $segmentDuration,
            ];
            
            Log::info("📹 AV segment: {$segmentDuration}s (AV file: {$avDuration}s, Voice: {$voiceOverDuration}s)");
        }

        // Segment 2: AB videos (multiple, sequential)
        if (!empty($video->ab_videos)) {
            foreach ($video->ab_videos as $index => $abVideoUrl) {
                $abPath = $this->urlToLocalPath($abVideoUrl);
                
                if (file_exists($abPath)) {
                    $abDuration = $this->getVideoDuration($abPath);
                    
                    $segments[] = [
                        'type' => 'ab_' . ($index + 1),
                        'video' => $abPath,
                        'audio' => null,
                        'duration' => $abDuration,
                    ];
                    
                    Log::info("📹 AB_" . ($index + 1) . " segment: {$abDuration}s (file: {$abPath})");
                } else {
                    Log::warning("⚠️ AB_" . ($index + 1) . " file not found: {$abPath}");
                }
            }
        }

        // Segment 3: 121 video
        if ($video->one_to_one_video) {
            $path = $this->urlToLocalPath($video->one_to_one_video);
            
            if (file_exists($path)) {
                $duration = $this->getVideoDuration($path);
                
                $segments[] = [
                    'type' => '121',
                    'video' => $path,
                    'audio' => null,
                    'duration' => $duration,
                ];
                
                Log::info("📹 121 segment: {$duration}s (file: {$path})");
            } else {
                Log::warning("⚠️ 121 file not found: {$path}");
            }
        }

        // Segment 4: WT video
        if ($video->walk_through_video) {
            $path = $this->urlToLocalPath($video->walk_through_video);
            
            if (file_exists($path)) {
                $duration = $this->getVideoDuration($path);
                
                $segments[] = [
                    'type' => 'wt',
                    'video' => $path,
                    'audio' => null,
                    'duration' => $duration,
                ];
                
                Log::info("📹 WT segment: {$duration}s (file: {$path})");
            } else {
                Log::warning("⚠️ WT file not found: {$path}");
            }
        }

        Log::info("📊 Total segments: " . count($segments));
        Log::info("🎬 Sequence: AV+Voice → AB videos → 121 → WT");
        
        return $segments;
    }
    
    /**
     * Convert URL to local file system path
     * From: https://staging.gstv.in/backend/public/uploads/mxf-videos/2026/10/08/AV_123.mxf
     * To: /home/gstv-staging/htdocs/staging.gstv.in/public/backend/public/uploads/mxf-videos/2026/10/08/AV_123.mxf
     */
    private function urlToLocalPath($url)
    {
        // If already a local path, return as is
        if (!str_starts_with($url, 'http')) {
            return $url;
        }
        
        // Extract path from URL
        // From: https://staging.gstv.in/backend/public/uploads/...
        // Get: /backend/public/uploads/...
        $urlPath = parse_url($url, PHP_URL_PATH);
        
        // The file is actually at: DOCUMENT_ROOT + /backend/public/uploads/...
        // DOCUMENT_ROOT is: /home/gstv-staging/htdocs/staging.gstv.in/public
        // So we need: /home/gstv-staging/htdocs/staging.gstv.in/public + /backend/public/uploads/...
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__DIR__));
        
        // Construct full local path
        $localPath = rtrim($documentRoot, '/') . $urlPath;
        
        Log::info("🔄 URL to Path conversion:");
        Log::info("   URL: {$url}");
        Log::info("   URL Path: {$urlPath}");
        Log::info("   Document Root: {$documentRoot}");
        Log::info("   Local: {$localPath}");
        Log::info("   File exists: " . (file_exists($localPath) ? 'YES' : 'NO'));
        
        return $localPath;
    }

    /**
     * Create temporary processed video segments
     */
    private function createVideoSegments($video, $segments, $voiceOverPath)
    {
        $tempDir = public_path('uploads/mxf-videos/temp_' . $video->id);
        
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0777, true);
        }

        $processedSegments = [];
        $segmentCount = count($segments);

        foreach ($segments as $index => $segment) {
            try {
                $outputFile = $tempDir . '/segment_' . $index . '.mp4';
                
                Log::info("🎬 Processing segment {$index}/{$segmentCount}: {$segment['type']}");

                if ($segment['type'] === 'av_with_voice' && $segment['audio']) {
                    // AV video with voice-over overlay
                    $this->processAVWithVoiceOver(
                        $segment['video'],
                        $segment['audio'],
                        $outputFile,
                        $segment['duration']
                    );
                } else {
                    // Regular video (no voice-over) - normalize format
                    $this->convertToMP4($segment['video'], $outputFile);
                }

                if (file_exists($outputFile) && filesize($outputFile) > 1024) {
                    $processedSegments[] = $outputFile;
                    Log::info("✅ Segment {$index} processed: " . basename($outputFile) . " (" . $this->formatBytes(filesize($outputFile)) . ")");
                } else {
                    Log::error("❌ Segment {$index} failed: File not created or too small");
                    throw new \Exception("Failed to process segment {$index}: {$segment['type']}");
                }
                
                // Update progress incrementally
                $progressIncrement = 40 / $segmentCount;
                $currentProgress = 40 + ($index * $progressIncrement);
                $this->updateProgress($video, (int)$currentProgress, "Processing segment " . ($index + 1) . "/{$segmentCount}");
                
            } catch (\Exception $e) {
                Log::error("❌ Error processing segment {$index}: " . $e->getMessage());
                throw $e;
            }
        }

        Log::info("✅ All {$segmentCount} segments processed successfully");
        return $processedSegments;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Create a black gap video with silence (for transitions)
     */
    private function createGapVideo($outputPath, $duration)
    {
        $ffmpegPath = $this->getFFmpegPath();
        
        Log::info("⏸️ Creating {$duration}s gap (black screen + silence)");
        
        // Create black video with silent audio
        $command = "\"{$ffmpegPath}\" -y " .
                   "-f lavfi -i color=c=black:s=1920x1080:d={$duration} " .
                   "-f lavfi -i anullsrc=channel_layout=stereo:sample_rate=48000:d={$duration} " .
                   "-c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p " .
                   "-c:a aac -b:a 192k " .
                   "-t {$duration} " .
                   escapeshellarg($outputPath) . " 2>&1";
        
        $output = shell_exec($command);
        
        if (!file_exists($outputPath) || filesize($outputPath) < 1024) {
            Log::error("❌ Gap video creation failed");
            Log::error("FFmpeg output: " . substr($output, 0, 500));
            throw new \Exception("Failed to create gap video");
        }
        
        Log::info("✅ Gap video created successfully");
    }

    /**
     * Process AV video with voice-over overlay
     * 
     * Rules:
     * - If AV is 30s and voice is 15s: Use first 15s of AV, discard remaining 15s
     * - If AV is 30s and voice is 45s: Loop AV (30s + 15s from start) to match 45s voice
     * - Final AV section MUST match full voice-over duration
     */
    private function processAVWithVoiceOver($videoPath, $audioPath, $outputPath, $voiceDuration)
    {
        $ffmpegPath = $this->getFFmpegPath();
        
        // Get actual video duration
        $videoDuration = $this->getVideoDuration($videoPath);
        
        Log::info("🎬 AV video duration: {$videoDuration}s, Voice duration: {$voiceDuration}s");
        
        if ($voiceDuration > $videoDuration) {
            // Voice is longer - need to loop video using stream_loop
            $loopCount = ceil($voiceDuration / $videoDuration) - 1;
            Log::info("🔁 Voice is longer, looping video " . ($loopCount + 1) . " times");
            
            // Method: Use stream_loop for input, then trim and mix audio
            $command = "\"{$ffmpegPath}\" -y " .
                       "-stream_loop {$loopCount} -i " . escapeshellarg($videoPath) . " " .
                       "-i " . escapeshellarg($audioPath) . " " .
                       "-filter_complex \"" .
                       "[0:v]setpts=PTS-STARTPTS[v];" .
                       "[0:a]volume=0.3[original];" .
                       "[1:a]volume=1.0[voice];" .
                       "[original][voice]amix=inputs=2:duration=first:dropout_transition=2[audio]" .
                       "\" " .
                       "-map \"[v]\" -map \"[audio]\" " .
                       "-c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p -r 30 " .
                       "-c:a aac -b:a 192k -ar 48000 -ac 2 " .
                       "-t {$voiceDuration} " .
                       escapeshellarg($outputPath) . " 2>&1";
        } else {
            // Video is longer or equal - trim to voice duration
            Log::info("✂️ Video is longer/equal, trimming to voice duration");
            
            $command = "\"{$ffmpegPath}\" -y " .
                       "-i " . escapeshellarg($videoPath) . " " .
                       "-i " . escapeshellarg($audioPath) . " " .
                       "-filter_complex \"" .
                       "[0:v]setpts=PTS-STARTPTS[v];" .
                       "[0:a]volume=0.3[original];" .
                       "[1:a]volume=1.0[voice];" .
                       "[original][voice]amix=inputs=2:duration=first:dropout_transition=2[audio]" .
                       "\" " .
                       "-map \"[v]\" -map \"[audio]\" " .
                       "-c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p -r 30 " .
                       "-c:a aac -b:a 192k -ar 48000 -ac 2 " .
                       "-t {$voiceDuration} " .
                       escapeshellarg($outputPath) . " 2>&1";
        }

        Log::info("🎬 Processing AV with voice-over...");
        Log::info("💻 Command: " . substr($command, 0, 300) . "...");
        $output = shell_exec($command);
        
        if (!file_exists($outputPath) || filesize($outputPath) < 1024) {
            Log::error("❌ AV processing failed");
            Log::error("FFmpeg output: " . substr($output, 0, 1000));
            throw new \Exception("Failed to process AV with voice-over");
        }
        
        // Verify output duration
        $actualDuration = $this->getVideoDuration($outputPath);
        Log::info("✅ AV with voice-over processed: Expected {$voiceDuration}s, Got {$actualDuration}s");
        
        if (abs($actualDuration - $voiceDuration) > 2) {
            Log::warning("⚠️ Duration mismatch: Expected {$voiceDuration}s but got {$actualDuration}s");
        }
    }

    /**
     * Convert video to MP4 format with standardized settings
     * This ensures all segments have compatible codecs/formats for concatenation
     */
    private function convertToMP4($inputPath, $outputPath)
    {
        $ffmpegPath = $this->getFFmpegPath();
        
        // Get original duration to preserve it
        $originalDuration = $this->getVideoDuration($inputPath);
        
        // Standardize all videos to same format:
        // - H.264 video codec
        // - AAC audio codec
        // - Same pixel format (yuv420p)
        // - Same frame rate (30fps)
        // - Same audio settings (48kHz stereo)
        $command = "\"{$ffmpegPath}\" -y " .
                   "-i " . escapeshellarg($inputPath) . " " .
                   "-c:v libx264 " .
                   "-preset fast " .
                   "-crf 23 " .
                   "-pix_fmt yuv420p " .
                   "-r 30 " .
                   "-c:a aac " .
                   "-b:a 192k " .
                   "-ar 48000 " .
                   "-ac 2 " .
                   "-t {$originalDuration} " .
                   escapeshellarg($outputPath) . " 2>&1";

        Log::info("🔄 Converting to MP4: " . basename($inputPath) . " (Duration: {$originalDuration}s)");
        $output = shell_exec($command);
        
        if (!file_exists($outputPath) || filesize($outputPath) < 1024) {
            Log::error("❌ Conversion failed for: " . basename($inputPath));
            Log::error("FFmpeg output: " . substr($output, 0, 500));
            throw new \Exception("Failed to convert video: " . basename($inputPath));
        }
        
        // Verify output duration
        $actualDuration = $this->getVideoDuration($outputPath);
        Log::info("✅ Converted: " . basename($outputPath) . " (Expected: {$originalDuration}s, Got: {$actualDuration}s)");
    }

    /**
     * Concatenate all video segments into one final video
     */
    private function concatenateVideos($segmentFiles, $video)
    {
        if (empty($segmentFiles)) {
            throw new \Exception("No video segments to concatenate");
        }

        // Create year/month/date folder structure for final video
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $date = $now->format('d');
        
        // Get document root and construct proper path
        // Document root: /home/gstv-staging/htdocs/staging.gstv.in/public
        // We need: /home/gstv-staging/htdocs/staging.gstv.in/public/backend/public/uploads/...
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(dirname(__DIR__)));
        $finalDir = rtrim($documentRoot, '/') . '/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date;
        
        Log::info("📁 Final video directory: {$finalDir}");
        
        if (!File::exists($finalDir)) {
            File::makeDirectory($finalDir, 0755, true);
            Log::info("✅ Created directory: {$finalDir}");
        }
        
        $filename = 'final_' . $video->id . '_' . time() . '.mp4';
        $finalPath = $finalDir . '/' . $filename;
        
        Log::info("💾 Final video path: {$finalPath}");

        // If only one segment, just copy it as final video
        if (count($segmentFiles) === 1) {
            copy($segmentFiles[0], $finalPath);
            
            $finalVideoUrl = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$filename;
            
            Log::info("✅ Single segment, copied as final video: {$finalVideoUrl}");
            return [
                'url' => $finalVideoUrl,
                'local_path' => $finalPath
            ];
        }

        // Log all segment durations before concatenation
        Log::info("📊 Segment durations before concatenation:");
        foreach ($segmentFiles as $index => $segmentFile) {
            $segDuration = $this->getVideoDuration($segmentFile);
            Log::info("   Segment {$index}: {$segDuration}s (" . basename($segmentFile) . ")");
        }

        $ffmpegPath = $this->getFFmpegPath();

        // Method 1: Try concat demuxer (fastest, but requires same codec/format)
        $tempDir = dirname($segmentFiles[0]);
        $concatFile = $tempDir . '/concat.txt';
        $concatList = '';

        foreach ($segmentFiles as $file) {
            // Use forward slashes and escape properly
            $normalizedPath = str_replace('\\', '/', $file);
            $concatList .= "file '{$normalizedPath}'\n";
        }

        file_put_contents($concatFile, $concatList);

        Log::info("🔗 Attempting concat demuxer with " . count($segmentFiles) . " segments...");
        
        $command = "\"{$ffmpegPath}\" -y " .
                   "-f concat -safe 0 " .
                   "-i " . escapeshellarg($concatFile) . " " .
                   "-c copy " .
                   escapeshellarg($finalPath) . " 2>&1";

        $output = shell_exec($command);

        // Check if concat demuxer worked
        if (file_exists($finalPath) && filesize($finalPath) > 1024) {
            Log::info("✅ Concat demuxer successful");
            Log::info("📊 Final video size: " . $this->formatBytes(filesize($finalPath)));
            Log::info("💾 File saved at: {$finalPath}");
            
            $finalVideoUrl = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$filename;
            Log::info("✅ Final video URL: {$finalVideoUrl}");
            return [
                'url' => $finalVideoUrl,
                'local_path' => $finalPath
            ];
        }
        
        // Log why concat demuxer failed
        Log::warning("⚠️ Concat demuxer check failed:");
        Log::warning("   File exists: " . (file_exists($finalPath) ? 'YES' : 'NO'));
        Log::warning("   File size: " . (file_exists($finalPath) ? filesize($finalPath) : '0') . " bytes");
        Log::warning("   Expected path: {$finalPath}");

        // Method 2: Concat demuxer failed, try filter complex (slower but more compatible)
        Log::warning("⚠️ Concat demuxer failed, trying filter complex method...");
        
        if (file_exists($finalPath)) {
            unlink($finalPath);
        }

        // Build filter complex command
        $inputs = '';
        $filterComplex = '';
        
        for ($i = 0; $i < count($segmentFiles); $i++) {
            $inputs .= "-i " . escapeshellarg($segmentFiles[$i]) . " ";
            $filterComplex .= "[{$i}:v:0][{$i}:a:0]";
        }
        
        $filterComplex .= "concat=n=" . count($segmentFiles) . ":v=1:a=1[outv][outa]";

        $command = "\"{$ffmpegPath}\" -y " .
                   $inputs .
                   "-filter_complex \"{$filterComplex}\" " .
                   "-map \"[outv]\" -map \"[outa]\" " .
                   "-c:v libx264 -preset fast -crf 23 " .
                   "-c:a aac -b:a 192k " .
                   escapeshellarg($finalPath) . " 2>&1";

        Log::info("🔗 Running filter complex concatenation...");
        Log::info("Command: " . substr($command, 0, 300) . "...");
        
        $output = shell_exec($command);

        if (!file_exists($finalPath) || filesize($finalPath) < 1024) {
            Log::error("❌ FFmpeg concat error (both methods failed)");
            Log::error("   File exists: " . (file_exists($finalPath) ? 'YES' : 'NO'));
            Log::error("   File size: " . (file_exists($finalPath) ? filesize($finalPath) : '0') . " bytes");
            Log::error("   Expected path: {$finalPath}");
            Log::error("Output: " . substr($output, 0, 1000));
            throw new \Exception("Failed to create final video. FFmpeg error: " . substr($output, 0, 200));
        }

        Log::info("✅ Filter complex concatenation successful");
        Log::info("📊 Final video size: " . $this->formatBytes(filesize($finalPath)));
        Log::info("💾 File saved at: {$finalPath}");
        
        $finalVideoUrl = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$filename;
        Log::info("✅ Final video URL: {$finalVideoUrl}");
        
        return [
            'url' => $finalVideoUrl,
            'local_path' => $finalPath
        ];
    }

    /**
     * Get video duration using FFprobe
     */
    private function getVideoDuration($filePath)
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $ffprobePath = $this->getFFprobePath();

        $command = "\"{$ffprobePath}\" -v error " .
                   "-show_entries format=duration " .
                   "-of default=noprint_wrappers=1:nokey=1 " .
                   escapeshellarg($filePath);

        $duration = trim(shell_exec($command));
        
        return (int) round(floatval($duration));
    }
    
    /**
     * Get FFmpeg executable path (Windows/Linux compatible)
     */
    private function getFFmpegPath()
    {
        // Check if Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common Windows paths
            $paths = [
                'C:\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
                'ffmpeg.exe', // If in PATH
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // If ffmpeg is in PATH, just use 'ffmpeg'
            exec('where ffmpeg 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
            
            // Default fallback
            return 'ffmpeg';
        }
        
        // Linux paths
        return file_exists('/usr/bin/ffmpeg') ? '/usr/bin/ffmpeg' : 'ffmpeg';
    }
    
    /**
     * Get FFprobe executable path (Windows/Linux compatible)
     */
    private function getFFprobePath()
    {
        // Check if Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common Windows paths
            $paths = [
                'C:\\ffmpeg\\bin\\ffprobe.exe',
                'C:\\Program Files\\ffmpeg\\bin\\ffprobe.exe',
                'ffprobe.exe', // If in PATH
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // If ffprobe is in PATH, just use 'ffprobe'
            exec('where ffprobe 2>&1', $output, $returnCode);
            if ($returnCode === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
            
            // Default fallback
            return 'ffprobe';
        }
        
        // Linux paths
        return file_exists('/usr/bin/ffprobe') ? '/usr/bin/ffprobe' : 'ffprobe';
    }

    /**
     * Cleanup temporary files
     */
    private function cleanup($segmentFiles, $voiceOverPath = null)
    {
        // Delete temp directory
        foreach ($segmentFiles as $file) {
            $tempDir = dirname($file);
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
                Log::info("🗑️ Cleaned up temp directory");
                break;
            }
        }
    }

    /**
     * Update processing progress
     */
    private function updateProgress($video, $progress, $message = '')
    {
        $video->update(['progress' => $progress]);
        Log::info("📊 Progress: {$progress}% - {$message}");
    }

    /**
     * Failed job handler
     */
    public function failed(\Throwable $exception)
    {
        $video = MxfVideo::find($this->mxfVideoId);
        
        if ($video) {
            $video->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }

        Log::error("❌ Job failed for MXF Video ID: {$this->mxfVideoId}");
        Log::error("Exception: " . $exception->getMessage());
    }

    /**
     * Apply category jacket overlay to final video
     * Optimized version - copied from ProcessVideoPreview.php
     */
    private function applyJacket($videoPath, $categoryId, $title)
    {
        try {
            Log::info("🎨 Starting jacket application...");
            
            // Check if video file exists
            if (!file_exists($videoPath)) {
                Log::error("❌ Video file not found: {$videoPath}");
                return false;
            }
            
            // Get category slug
            $category = \DB::table('categories')->where('id', $categoryId)->first();
            if (!$category) {
                Log::warning("⚠️ Category not found for ID: {$categoryId}");
                return false;
            }

            $catSlug = $category->slug ?? 'default';
            Log::info("📂 Category: {$catSlug}");
            
            // Use EXACT same path construction as ProcessVideoPreview.php
            $jacketPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/jacket/' . $catSlug . '.png';
            $fontPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/fonts/HindVadodara-Bold.ttf';

            if (!file_exists($jacketPath)) {
                Log::warning("⚠️ Jacket not found: {$jacketPath}");
                return false;
            }

            if (!file_exists($fontPath)) {
                Log::warning("⚠️ Font not found: {$fontPath}");
                return false;
            }

            Log::info("✓ Jacket and font found");

            // Create temporary files
            $dir = dirname($videoPath) . '/';
            $file = pathinfo($videoPath, PATHINFO_FILENAME);
            $tempVideo = $dir . $file . '_temp.mp4';

            // Render date text
            $date = date('d-m-Y');
            $days = [
                'Sunday' => 'રવિવાર', 'Monday' => 'સોમવાર', 'Tuesday' => 'મંગળવાર',
                'Wednesday' => 'બુધવાર', 'Thursday' => 'ગુરુવાર', 'Friday' => 'શુક્રવાર', 'Saturday' => 'શનિવાર',
            ];
            $dayGujarati = $days[date('l')];
            $dateText = $date . " | " . $dayGujarati;

            $dateTextPng = sys_get_temp_dir() . "/date_" . uniqid() . ".png";
            $titleTextPng = sys_get_temp_dir() . "/title_" . uniqid() . ".png";

            // Render Gujarati text overlays
            $this->renderGujaratiTextPng($dateText, $dateTextPng, 24, $fontPath, "white", "rgba(0,0,0,0.5)", 340, 60);
            $this->renderGujaratiTextPng($title, $titleTextPng, 60, $fontPath, "#800707", null, 1920, 120);
            
            if (!file_exists($dateTextPng) || !file_exists($titleTextPng)) {
                Log::error("❌ Text rendering failed");
                return false;
            }

            // Use getFFmpegPath() for Windows/Linux compatibility
            $ffmpegPath = $this->getFFmpegPath();

            // OPTIMIZED FFmpeg command for FASTEST processing
            // Key optimizations:
            // 1. -threads 0 = use all CPU cores
            // 2. -preset ultrafast = fastest encoding
            // 3. -tune fastdecode = optimize for playback speed
            // 4. -crf 30 = slightly lower quality for smaller file size
            // 5. -g 60 = keyframe every 2 seconds (faster seeking)
            $command = "\"{$ffmpegPath}\" -y " .
                "-threads 0 " .
                "-i " . escapeshellarg($videoPath) . " " .
                "-stream_loop -1 -i " . escapeshellarg($jacketPath) . " " .
                "-i " . escapeshellarg($dateTextPng) . " " .
                "-i " . escapeshellarg($titleTextPng) . " " .
                "-filter_complex " .
                "\"[0:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,setsar=1[video];" .
                "[1:v]scale=1920:1080[jacket];" .
                "[video][jacket]overlay=0:0[tmp1];" .
                "[tmp1][2:v]overlay=x=55:y=125[tmp2];" .
                "[tmp2][3:v]overlay=x=0:y=915[out]\" " .
                "-map \"[out]\" -map 0:a? " .
                "-c:v libx264 -preset ultrafast -tune fastdecode -crf 30 -g 60 -pix_fmt yuv420p " .
                "-c:a copy " .
                "-movflags +faststart " .
                escapeshellarg($tempVideo) . " 2>&1";

            Log::info("🎬 Applying jacket with optimized settings...");
            
            $startTime = microtime(true);
            $output = shell_exec($command);
            $elapsed = round(microtime(true) - $startTime, 2);
            
            Log::info("⏱️ Jacket processing completed in {$elapsed} seconds");

            // Cleanup temp PNG files
            @unlink($dateTextPng);
            @unlink($titleTextPng);

            if (file_exists($tempVideo) && filesize($tempVideo) > 1024) {
                // Replace original with jacketed version
                @unlink($videoPath);
                rename($tempVideo, $videoPath);
                Log::info("✅ Jacket applied: " . $this->formatBytes(filesize($videoPath)));
                return true;
            } else {
                Log::error("❌ Jacket failed - temp video not created");
                if (file_exists($tempVideo)) {
                    @unlink($tempVideo);
                }
                return false;
            }

        } catch (\Exception $e) {
            Log::error("❌ Jacket exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Render Gujarati text to PNG using ImageMagick
     */
    private function renderGujaratiTextPng(
        $text,
        $outputPath,
        $fontSize,
        $fontPath,
        $textColor,
        $bgColor = null,
        $width = null,
        $height = null
    ) {
        $bgOption = $bgColor ? "-background \"{$bgColor}\"" : "-background none";
        $sizeOption = ($width && $height) ? "-size {$width}x{$height}" : "";

        $command = "/usr/bin/convert {$bgOption} {$sizeOption} " .
                   "-font " . escapeshellarg($fontPath) . " " .
                   "-pointsize {$fontSize} " .
                   "-fill \"{$textColor}\" " .
                   "-gravity center " .
                   "label:" . escapeshellarg($text) . " " .
                   escapeshellarg($outputPath) . " 2>&1";

        $output = shell_exec($command);

        if (!file_exists($outputPath)) {
            Log::error("❌ ImageMagick text render failed");
            Log::error("Output: " . $output);
        }
    }
}
