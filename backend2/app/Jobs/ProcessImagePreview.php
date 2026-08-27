<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Image;
use Normalizer;

class ProcessImagePreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $baseImagePath,
        public int $catID = 0,
        public string $title = '',
        public int $isJacket = 0
    ) {}

    /**
     * Create image variant (small/medium) from original
     */
    private function createImageVariant(string $originalPath, string $outputPath, string $variant): bool
    {
        try {
            $targetWidth = match($variant) {
                'small'  => 350,
                'medium' => 700,
                default  => 1920
            };

            Log::info("🖼️ Creating {$variant} variant: {$targetWidth}px width");

            $image = Image::make($originalPath);
            $image->resize($targetWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $image->save($outputPath, 100);

            Log::info("✅ {$variant} variant created: {$outputPath}");
            return true;

        } catch (\Exception $e) {
            Log::error("✗ Error creating {$variant} variant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Renders Gujarati text with precise Complex Text Layout (CTL) and vertical offset tuning
     */
  	/**
     * Renders Gujarati text using temporary Fontconfig XML and Pango CTL rendering
     */
    private function renderGujaratiOverlay(
        string $text,
        string $fontPath,
        int $fontSize,
        string $textColorHex,
        int $width,
        int $height,
        string $outputPath,
        ?string $bgColorHex = null,
        int $vOffset = 0
    ): bool {
        $tempFontConf = '';

        try {
            // 1. Normalize Unicode text (NFC) so matras bind correctly to base glyphs
            if (class_exists('Normalizer')) {
                $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
            }

            $escapedText = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

            // 2. Generate Fontconfig XML mapping the exact font directory
            $fontDir = dirname($fontPath);
            $fontConfXml = '<?xml version="1.0"?>' . "\n" .
                '<!DOCTYPE fontconfig SYSTEM "fonts.dtd">' . "\n" .
                '<fontconfig>' . "\n" .
                '  <dir>' . htmlspecialchars($fontDir) . '</dir>' . "\n" .
                '  <cachedir>' . sys_get_temp_dir() . '</cachedir>' . "\n" .
                '</fontconfig>';

            $tempFontConf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'font_conf_' . uniqid() . '.conf';
            file_put_contents($tempFontConf, $fontConfXml);

            // 3. Prepare Pango markup
            // Using precise font family name with explicit size in points
            $pangoText = sprintf(
                '<span font_family="Hind Vadodara" font_weight="bold" size="%d">%s</span>',
                $fontSize * 1024, // Pango uses 1/1024ths of a point
                $escapedText
            );

            $bgCanvas = $bgColorHex ? escapeshellarg($bgColorHex) : 'none';

            // 4. Render using Pango with ImageMagick CLI
            // Directly rendering pango: with explicit foreground color to avoid color parsing conflicts
            $cmd = sprintf(
                'FONTCONFIG_FILE=%s convert -background %s -fill %s -size %dx%d -gravity center pango:%s -geometry +0+%d %s',
                escapeshellarg($tempFontConf),
                $bgCanvas,
                escapeshellarg($textColorHex),
                $width,
                $height,
                escapeshellarg($pangoText),
                $vOffset,
                escapeshellarg($outputPath)
            );

            shell_exec($cmd . " 2>&1");

            // Clean up temporary XML font config file
            if (file_exists($tempFontConf)) {
                @unlink($tempFontConf);
            }

            // 5. Fallback check: If CLI didn't produce file, fallback to GD
            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                $im = imagecreatetruecolor($width, $height);
                imagealphablending($im, false);
                imagesavealpha($im, true);

                if ($bgColorHex) {
                    $bgR = hexdec(substr($bgColorHex, 1, 2));
                    $bgG = hexdec(substr($bgColorHex, 3, 2));
                    $bgB = hexdec(substr($bgColorHex, 5, 2));
                    $bgColor = imagecolorallocatealpha($im, $bgR, $bgG, $bgB, 0);
                    imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);
                } else {
                    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
                    imagefill($im, 0, 0, $transparent);
                }

                $textR = hexdec(substr($textColorHex, 1, 2));
                $textG = hexdec(substr($textColorHex, 3, 2));
                $textB = hexdec(substr($textColorHex, 5, 2));
                $fontColor = imagecolorallocate($im, $textR, $textG, $textB);

                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
                $textWidth  = abs($bbox[4] - $bbox[0]);
                $textHeight = abs($bbox[5] - $bbox[1]);

                $x = (int) (($width - $textWidth) / 2);
                $y = (int) (($height / 2) + ($textHeight / 2)) + $vOffset;

                imagettftext($im, $fontSize, 0, $x, $y, $fontColor, $fontPath, $text);
                imagepng($im, $outputPath);
                imagedestroy($im);
            }

            return file_exists($outputPath) && filesize($outputPath) > 0;

        } catch (\Exception $e) {
            if (!empty($tempFontConf) && file_exists($tempFontConf)) {
                @unlink($tempFontConf);
            }
            Log::error("✗ Gujarati Rendering Error: " . $e->getMessage());
            return false;
        }
    }
    private function renderGujaratiOverlay27(
        string $text,
        string $fontPath,
        int $fontSize,
        string $textColorHex,
        int $width,
        int $height,
        string $outputPath,
        ?string $bgColorHex = null,
        int $vOffset = 0
    ): bool {
        try {
            // 1. Normalize Unicode text (NFC) so matras bind correctly to base glyphs
            if (class_exists('Normalizer')) {
                $text = Normalizer::normalize($text, Normalizer::FORM_C) ?: $text;
            }

            $escapedText = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
          
          
          

            // 2. Prepare Pango markup string
            $pangoText = "<span font_family=\"Hind Vadodara\" size=\"" . ($fontSize * 1000) . "\" foreground=\"{$textColorHex}\" weight=\"bold\">{$escapedText}</span>";
            $bgCanvas  = $bgColorHex ? escapeshellarg($bgColorHex) : 'none';

            // 3. Composite Pango text over canvas using ImageMagick CLI with font path specification
            $cmd = sprintf(
                'convert -env FONTCONFIG_FILE="" -size %dx%d xc:%s ( -background none -font %s pango:%s ) -gravity center -geometry +0+%d -composite %s',
                $width,
                $height,
                $bgCanvas,
                escapeshellarg($fontPath),
                escapeshellarg($pangoText),
                $vOffset,
                escapeshellarg($outputPath)
            );

            shell_exec($cmd);

            // 4. Fallback to GD if ImageMagick CLI fails
            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                $im = imagecreatetruecolor($width, $height);
                imagealphablending($im, false);
                imagesavealpha($im, true);

                if ($bgColorHex) {
                    $bgR = hexdec(substr($bgColorHex, 1, 2));
                    $bgG = hexdec(substr($bgColorHex, 3, 2));
                    $bgB = hexdec(substr($bgColorHex, 5, 2));
                    $bgColor = imagecolorallocatealpha($im, $bgR, $bgG, $bgB, 0);
                    imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);
                } else {
                    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
                    imagefill($im, 0, 0, $transparent);
                }

                $textR = hexdec(substr($textColorHex, 1, 2));
                $textG = hexdec(substr($textColorHex, 3, 2));
                $textB = hexdec(substr($textColorHex, 5, 2));
                $fontColor = imagecolorallocate($im, $textR, $textG, $textB);

                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
                $textWidth  = abs($bbox[4] - $bbox[0]);
                $textHeight = abs($bbox[5] - $bbox[1]);

                $x = (int) (($width - $textWidth) / 2);
                $y = (int) (($height / 2) + ($textHeight / 2)) + $vOffset;

                imagettftext($im, $fontSize, 0, $x, $y, $fontColor, $fontPath, $text);
                imagepng($im, $outputPath);
                imagedestroy($im);
            }

            return file_exists($outputPath) && filesize($outputPath) > 0;

        } catch (\Exception $e) {
            Log::error("✗ Gujarati Rendering Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add jacket frame, date badge, and title to image
     */
    private function addJacketAndTitle(string $imagePath, string $catSlug, string $variant = 'original'): bool
    {
        try {
            $basePublic = base_path('../backend/public');
            $jacketPath = $basePublic . '/jacket/' . $catSlug . '.png';
            $fontPath   = $basePublic . '/fonts/HindVadodara-Bold.ttf';

            if (!file_exists($jacketPath) || !file_exists($fontPath)) {
                Log::warning("Jacket or Font missing: {$jacketPath}");
                return false;
            }

            // 1. Load jacket frame
            $jacket = Image::make($jacketPath);
            $jacketWidth  = $jacket->width();
            $jacketHeight = $jacket->height();

            // Target dimensions for current variant
            $targetWidth = match($variant) {
                'small'  => 350,
                'medium' => 700,
                default  => 1920
            };

            $scaleFactor  = $targetWidth / $jacketWidth;
            $targetHeight = (int) ($jacketHeight * $scaleFactor);

            // Resize jacket frame
            $jacket->resize($targetWidth, $targetHeight);

            // 2. Define upper visible area & lower footer area (~15% height)
            $footerHeight  = (int) ($targetHeight * 0.15); 
            $visibleHeight = $targetHeight - $footerHeight;

            // 3. Stretch source image to fill visible body area
            $sourceImage = Image::make($imagePath);
            $sourceImage->resize($targetWidth, $visibleHeight);

            // 4. Create canvas and combine layers
            $finalCanvas = Image::canvas($targetWidth, $targetHeight, '#ffffff');
            $finalCanvas->insert($sourceImage, 'top-left', 0, 0);
            $finalCanvas->insert($jacket, 'top-left', 0, 0);

            // -------------------------------------------------------------
            // 5. RENDER DATE BADGE
            // -------------------------------------------------------------
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
            $dayGujarati = $days[date('l')] ?? '';
            $dateText    = $date . " | " . $dayGujarati;

            [$dateFontSize, $dateBoxW, $dateBoxH, $dateX, $dateY, $dateVOffset] = match($variant) {
                'small'  => [6,  85,  15, 10, 24, -2],
                'medium' => [10, 140, 23, 20, 46, -4],
                default  => [25, 330, 50, 54, 122, -8]
            };

            $tempDatePng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'date_' . uniqid() . '.png';

            $dateSuccess = $this->renderGujaratiOverlay(
                $dateText,
                $fontPath,
                $dateFontSize,
                '#FFFFFF',
                $dateBoxW,
                $dateBoxH,
                $tempDatePng,
                "rgba(0,0,0,0.5)",
                $dateVOffset
            );

            if ($dateSuccess && file_exists($tempDatePng)) {
                $dateOverlay = Image::make($tempDatePng);
                $finalCanvas->insert($dateOverlay, 'top-left', $dateX, $dateY);
                @unlink($tempDatePng);
            }

            // -------------------------------------------------------------
            // 6. RENDER GUJARATI TITLE IN FOOTER
            // -------------------------------------------------------------
            if (!empty($this->title)) {
                $fontSize = match($variant) {
                    'small'  => 10,
                    'medium' => 22,
                    default  => 60
                };
                $bottomPadding = match($variant) {
                    'small'  => -2,
                    'medium' => 0,
                    default  => -5
                };

                $tempTitlePng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'title_' . uniqid() . '.png';

                // Fix spelling error if present in string before rendering
                $cleanTitle = str_replace(
                    ['દવિસ', 'ગૂરુવાર'],
                    ['દિવસ', 'ગુરુવાર'],
                    $this->title
                );

                $titleSuccess = $this->renderGujaratiOverlay(
                    $cleanTitle,
                    $fontPath,
                    $fontSize,
                    '#800707',
                    $targetWidth,
                    $footerHeight,
                    $tempTitlePng,
                    null,
                    0
                );

                if ($titleSuccess && file_exists($tempTitlePng)) {
                    $titleOverlay = Image::make($tempTitlePng);
                    $footerY = ($targetHeight - $footerHeight) - $bottomPadding;
                    $finalCanvas->insert($titleOverlay, 'top-left', 0, (int)$footerY);
                    @unlink($tempTitlePng);
                }
            }

            // 7. Save final image
            $finalCanvas->save($imagePath, 100);
            Log::info("✅ Image successfully framed with Date & Gujarati title.");

            return true;

        } catch (\Exception $e) {
            Log::error("✗ Error adding jacket: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert local URL path to absolute system filesystem path
     */
    private function getFilePath(string $imagePath): string
    {
        if (!str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        Log::info("🔄 Converting URL to file path: {$imagePath}");

        $parsedUrl = parse_url($imagePath, PHP_URL_PATH);
        $parsedUrl = preg_replace('/\?.*$/', '', $parsedUrl);

        if (strpos($parsedUrl, '/gstvnews/') !== false) {
            $parts = explode('/gstvnews/', $parsedUrl);
            $relativePath = $parts[1];

            $baseGstvDir = base_path('..');
            $fullPath = $baseGstvDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (file_exists($fullPath)) {
                return $fullPath;
            }

            $wampPath = 'C:\\wamp\\www\\gstvnews\\' . str_replace('/', '\\', $relativePath);
            if (file_exists($wampPath)) {
                return $wampPath;
            }
        }

        Log::error("❌ Failed to resolve URL to existing local file: {$imagePath}");
        return '';
    }

    /**
     * Get Category Slug
     */
    private function getCategorySlug(int $catID): string
    {
        try {
            $category = \DB::table('categories')
                ->select('slug')
                ->where('id', $catID)
                ->first();

            return $category ? $category->slug : '';
        } catch (\Exception $e) {
            Log::error("Error getting category: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Main handle function
     */
    public function handle(): void
    {
        try {
            Log::info("🚀 START: Image preview generation with jacket, date and title");

            // $imagePath = $this->getFilePath($this->baseImagePath);
            $imagePath1 = $this->baseImagePath;//;$this->getFilePath($this->baseImagePath);
          
            $iiim = explode('media', $imagePath1);
        
            $imagePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/backend/public/media' . $iiim[1];
            if (empty($imagePath)) {
                Log::error("✗ Base image not found: {$imagePath}");
                return;
            }

            $catSlug = $this->getCategorySlug($this->catID);

            if (empty($catSlug)) {
                Log::warning("⚠ Category not found, skipping jacket");
                return;
            }

            if ($this->isJacket == 1 && !empty($this->title)) {
                Log::info("👔 Processing images with jacket and title...");

                $baseDir = dirname($imagePath) . '/';
                $baseFilename = pathinfo($imagePath, PATHINFO_FILENAME);
                $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

                $imagePaths = [];

                if (file_exists($imagePath)) {
                    $imagePaths['original'] = $imagePath;

                    $smallPath = $baseDir . $baseFilename . '_small.' . $extension;
                    $imagePaths['small'] = $smallPath;

                    $mediumPath = $baseDir . $baseFilename . '_medium.' . $extension;
                    $imagePaths['medium'] = $mediumPath;
                } else {
                    Log::error("❌ Original image doesn't exist: {$imagePath}");
                    return;
                }

                $processedCount = 0;
                foreach ($imagePaths as $variant => $variantPath) {
                    if ($variant !== 'original' && !file_exists($variantPath)) {
                        $this->createImageVariant($imagePath, $variantPath, $variant);
                    }

                    if ($this->addJacketAndTitle($variantPath, $catSlug, $variant)) {
                        $processedCount++;
                    }
                }

                Log::info("✅ Jacket, date badge, and title added to {$processedCount} image(s)");
            }

            Log::info("✅ COMPLETE: Image processing finished!");

        } catch (\Exception $e) {
            Log::error("✗ Error: " . $e->getMessage());
            throw $e;
        }
    }
}