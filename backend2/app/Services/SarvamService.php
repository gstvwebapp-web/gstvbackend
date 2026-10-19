<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SarvamService
{
    private $apiKey;
    private $apiUrl = 'https://api.sarvam.ai/text-to-speech';

    public function __construct()
    {
        $this->apiKey = 'sk_26yvfjn8_6RNN6alTyz8fWKMI0EotYFpy';
    }

    /**
     * Generate speech from text using Sarvam AI
     * 
     * @param string $text The text to convert to speech
     * @param string $outputPath Full path where to save the audio file
     * @param string $voiceId Voice ID (default: meera for Gujarati female voice)
     * @return bool Success status
     */
    public function generateSpeech($text, $outputPath, $voiceId = 'pooja_gu_conversational')
    {
        try {
            Log::info("🔊 Sarvam AI: Generating speech", [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100),
                'voice_id' => $voiceId,
                'output_path' => basename($outputPath)
            ]);

            $response = Http::timeout(30)->withHeaders([
                'api-subscription-key' => $this->apiKey,
            ])->post($this->apiUrl, [
                'inputs' => [$text],
                'target_language_code' => 'gu-IN',
                'speaker' => $voiceId,
                'pitch' => 0,
                'pace' => 1.0,
                'loudness' => 1.5,
                'speech_sample_rate' => 8000,
                'enable_preprocessing' => true,
                'model' => 'bulbul:v4-flash'
            ]);

            Log::info("📡 Sarvam API Response", [
                'status' => $response->status(),
                'headers' => $response->headers(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("📦 Sarvam Response Data", [
                    'keys' => array_keys($data ?? []),
                    'has_audios' => isset($data['audios']),
                    'audios_count' => isset($data['audios']) ? count($data['audios']) : 0
                ]);
                
                // Sarvam returns base64 encoded audio in 'audios' array
                if (isset($data['audios']) && count($data['audios']) > 0) {
                    $base64Audio = $data['audios'][0];
                    
                    // Decode and save
                    $audioData = base64_decode($base64Audio);
                    
                    if ($audioData === false || strlen($audioData) < 100) {
                        Log::error("❌ Sarvam AI: Base64 decode failed or audio too small");
                        return false;
                    }
                    
                    file_put_contents($outputPath, $audioData);
                    
                    Log::info("✅ Sarvam AI: Audio generated successfully", [
                        'output' => $outputPath,
                        'bytes' => strlen($audioData),
                        'file_exists' => file_exists($outputPath)
                    ]);
                    
                    return true;
                } else {
                    Log::error("❌ Sarvam AI: No audio data in response", [
                        'full_response' => $data
                    ]);
                    return false;
                }
            } else {
                Log::error("❌ Sarvam AI: API request failed", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("❌ Sarvam AI: Exception occurred", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Generate speech with chunking for long text
     * 
     * @param string $text The full text to convert
     * @param string $outputPath Where to save final MP3
     * @param int $chunkSize Max characters per chunk
     * @param string $voiceId Voice ID
     * @return bool Success status
     */
    public function generateSpeechChunked($text, $outputPath, $chunkSize = 500, $voiceId = 'pooja_gu_conversational')
    {
        try {
            // If text is short enough, generate directly
            if (strlen($text) <= $chunkSize) {
                return $this->generateSpeech($text, $outputPath, $voiceId);
            }

            Log::info("🔊 Sarvam AI: Processing long text with chunking", [
                'total_length' => strlen($text),
                'chunk_size' => $chunkSize
            ]);

            // Split text into sentences (Gujarati sentence enders: । ? !)
            $sentences = preg_split('/([।?!.]\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            $chunks = [];
            $currentChunk = '';

            for ($i = 0; $i < count($sentences); $i++) {
                $sentence = $sentences[$i];
                
                // If adding this sentence would exceed chunk size
                if (strlen($currentChunk . $sentence) > $chunkSize) {
                    // Save current chunk if not empty
                    if (!empty(trim($currentChunk))) {
                        $chunks[] = trim($currentChunk);
                    }
                    
                    // If single sentence is too long, force split by words
                    if (strlen($sentence) > $chunkSize) {
                        $words = preg_split('/\s+/u', $sentence);
                        $wordChunk = '';
                        
                        foreach ($words as $word) {
                            if (strlen($wordChunk . ' ' . $word) > $chunkSize) {
                                if (!empty(trim($wordChunk))) {
                                    $chunks[] = trim($wordChunk);
                                }
                                $wordChunk = $word;
                            } else {
                                $wordChunk .= (empty($wordChunk) ? '' : ' ') . $word;
                            }
                        }
                        
                        $currentChunk = $wordChunk;
                    } else {
                        $currentChunk = $sentence;
                    }
                } else {
                    $currentChunk .= $sentence;
                }
            }

            // Add final chunk
            if (!empty(trim($currentChunk))) {
                $chunks[] = trim($currentChunk);
            }
            
            // Filter empty chunks
            $chunks = array_filter($chunks, function($chunk) {
                return !empty(trim($chunk));
            });
            
            $chunks = array_values($chunks); // Re-index

            Log::info("📝 Split into " . count($chunks) . " chunks");

            // Generate audio for each chunk
            $tempFiles = [];
            $tempDir = sys_get_temp_dir();

            foreach ($chunks as $index => $chunk) {
                if (empty(trim($chunk))) continue;

                $tempFile = $tempDir . '/sarvam_chunk_' . $index . '_' . uniqid() . '.mp3';
                
                if ($this->generateSpeech($chunk, $tempFile, $voiceId)) {
                    $tempFiles[] = $tempFile;
                    Log::info("✓ Chunk {$index} generated");
                } else {
                    Log::warning("⚠ Chunk {$index} failed, skipping");
                }
            }

            if (empty($tempFiles)) {
                Log::error("❌ No chunks were successfully generated");
                return false;
            }

            // Merge all chunks using FFmpeg
            if (count($tempFiles) === 1) {
                // Only one chunk, just rename/copy
                if (!copy($tempFiles[0], $outputPath)) {
                    Log::error("❌ Failed to copy single chunk file");
                    return false;
                }
                Log::info("✓ Single chunk copied");
            } else {
                // Multiple chunks, concatenate with FFmpeg
                if (!$this->concatenateAudioFiles($tempFiles, $outputPath)) {
                    Log::error("❌ Failed to concatenate audio chunks");
                    return false;
                }
                Log::info("✓ Multiple chunks concatenated");
            }

            // Cleanup temp files
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }

            Log::info("✅ Sarvam AI: Chunked speech generation completed", [
                'chunks' => count($chunks),
                'output' => $outputPath
            ]);

            return file_exists($outputPath) && filesize($outputPath) > 0;

        } catch (\Exception $e) {
            Log::error("❌ Sarvam AI chunked generation failed", [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Concatenate multiple audio files into one using FFmpeg and convert to MP3
     */
    private function concatenateAudioFiles($files, $outputPath)
    {
        // Create concat list file
        $concatFile = sys_get_temp_dir() . '/concat_' . uniqid() . '.txt';
        $concatList = '';

        foreach ($files as $file) {
            $concatList .= "file '" . str_replace('\\', '/', $file) . "'\n";
        }

        file_put_contents($concatFile, $concatList);

        // Detect FFmpeg path
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Try common Windows paths
            $possiblePaths = [
                'C:\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
                'ffmpeg.exe', // If in PATH
                'ffmpeg' // Generic fallback
            ];
            
            $ffmpegPath = null;
            foreach ($possiblePaths as $path) {
                if ($path === 'ffmpeg' || $path === 'ffmpeg.exe' || file_exists($path)) {
                    $ffmpegPath = $path;
                    break;
                }
            }
            
            if (!$ffmpegPath) {
                $ffmpegPath = 'ffmpeg'; // Final fallback
            }
        } else {
            $ffmpegPath = '/usr/bin/ffmpeg';
        }

        // Concatenate and convert to MP3 using FFmpeg
        $command = "\"{$ffmpegPath}\" -y -f concat -safe 0 -i " . 
                   escapeshellarg($concatFile) . 
                   " -c:a libmp3lame -b:a 128k " . 
                   escapeshellarg($outputPath) . " 2>&1";

        Log::info("🔗 Concatenating audio chunks", ['files' => count($files)]);
        $output = shell_exec($command);

        // Cleanup concat file
        @unlink($concatFile);

        if (!file_exists($outputPath) || filesize($outputPath) < 100) {
            Log::error("❌ FFmpeg concatenation failed", [
                'command' => substr($command, 0, 200),
                'output' => substr($output, 0, 500),
                'file_exists' => file_exists($outputPath),
                'file_size' => file_exists($outputPath) ? filesize($outputPath) : 0
            ]);
            return false;
        }

        Log::info("✅ Audio chunks concatenated successfully", [
            'output' => $outputPath,
            'size' => filesize($outputPath)
        ]);

        return true;
    }
}
