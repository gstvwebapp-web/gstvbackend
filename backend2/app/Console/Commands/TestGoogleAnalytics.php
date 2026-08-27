<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleAnalyticsService;

class TestGoogleAnalytics extends Command
{
    protected $signature = 'ga:test {url?}';
    protected $description = 'Test Google Analytics integration';
    protected $googleAnalyticsService;

    public function __construct(GoogleAnalyticsService $googleAnalyticsService)
    {
        parent::__construct();
        $this->googleAnalyticsService = $googleAnalyticsService;
    }

    public function handle()
    {
        $this->info('Testing Google Analytics Integration...');
        $this->newLine();

        // Check configuration
        $propertyId = env('GOOGLE_ANALYTICS_PROPERTY_ID');
        $credentialsPath = storage_path('app/analytics/google-analytics.json');

        $this->info('Configuration:');
        $this->line('Property ID: ' . ($propertyId ?: 'NOT SET'));
        $this->line('Credentials Path: ' . $credentialsPath);
        $this->newLine();

        if (!$propertyId) {
            $this->error('❌ GOOGLE_ANALYTICS_PROPERTY_ID is not set in .env');
            return 1;
        }

        if (!file_exists($credentialsPath)) {
            $this->error('❌ Credentials file not found at: ' . $credentialsPath);
            $this->info('Please download your service account JSON file and place it at the path above.');
            return 1;
        }

        $this->info('✓ Configuration looks good!');
        $this->newLine();

        // Test with a URL
        $url = $this->argument('url');
        
        if (!$url) {
            $this->info('Fetching all page views from Google Analytics (last 30 days)...');
            $this->newLine();
            
            try {
                $pageViews = $this->googleAnalyticsService->getPageTotalViews(30);
                
                if (empty($pageViews)) {
                    $this->warn('No page views found in Google Analytics for the last 30 days.');
                    return 0;
                }

                $this->info('Found ' . count($pageViews) . ' pages with views:');
                $this->newLine();

                // Show top 20
                arsort($pageViews);
                $top20 = array_slice($pageViews, 0, 20, true);

                $this->table(
                    ['Page Path', 'Views'],
                    collect($top20)->map(function($views, $path) {
                        return [$path, number_format($views)];
                    })->toArray()
                );
                
                $this->newLine();
                $this->info('✓ Google Analytics is working!');
                $this->newLine();
                
                // Check for the specific URL you mentioned
                $testUrl = '/news/india/no-need-to-worry-about-lpg-cylinder-shortage-govts-assurance-amid-middle-east-crisis';
                if (isset($pageViews[$testUrl])) {
                    $this->info('✓ Found your test URL:');
                    $this->line($testUrl);
                    $this->line('Views: ' . number_format($pageViews[$testUrl]));
                } else {
                    $this->warn('⚠ Your test URL not found in GA data.');
                    $this->line('Looking for: ' . $testUrl);
                    $this->newLine();
                    
                    // Search for similar
                    $this->info('Searching for similar URLs with "lpg-cylinder"...');
                    $found = false;
                    foreach ($pageViews as $path => $count) {
                        if (stripos($path, 'lpg-cylinder') !== false) {
                            $this->line($path . ' → ' . number_format($count) . ' views');
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $this->line('No similar URLs found.');
                    }
                }
                
            } catch (\Exception $e) {
                $this->error('✗ Error: ' . $e->getMessage());
                return 1;
            }
        } else {
            // Test specific URL
            $this->info('Testing URL: ' . $url);
            $this->newLine();
            
            try {
                $allViews = $this->googleAnalyticsService->getPageTotalViews(30);
                
                if (isset($allViews[$url])) {
                    $views = $allViews[$url];
                    $this->info('✓ URL Found!');
                    $this->line('Views: ' . number_format($views));
                } else {
                    $this->warn('✗ URL not found in Google Analytics');
                    $this->newLine();
                    
                    // Try to find similar
                    $this->info('Searching for similar URLs...');
                    $parts = explode('/', trim($url, '/'));
                    $slug = end($parts);
                    
                    $found = false;
                    foreach ($allViews as $path => $count) {
                        if (stripos($path, $slug) !== false) {
                            $this->line($path . ' → ' . number_format($count) . ' views');
                            $found = true;
                        }
                    }
                    
                    if (!$found) {
                        $this->line('No similar URLs found.');
                    }
                }
                
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->error('✗ Error: ' . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }
}
