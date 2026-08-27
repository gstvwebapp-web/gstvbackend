<?php

namespace App\Services;
use Carbon\Carbon;
use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\RunRealtimeReportRequest;
use Google\Service\AnalyticsData\RunReportRequest;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\AnalyticsData\DimensionOrderBy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    protected $analytics;
    protected $propertyId;

    public function __construct()
    {
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/analytics/google-analytics.json'));
            $client->addScope('https://www.googleapis.com/auth/analytics.readonly');

            $this->analytics = new AnalyticsData($client);
            
            // Get Property ID from config (not env) to avoid NULL issue
            $propertyIdFromEnv = config('app.google_analytics_property_id') ?: env('GOOGLE_ANALYTICS_PROPERTY_ID');
            
            // If still null, try to load from .env file directly
            if (empty($propertyIdFromEnv)) {
                $envPath = base_path('.env');
                if (file_exists($envPath)) {
                    $envContent = file_get_contents($envPath);
                    if (preg_match('/GOOGLE_ANALYTICS_PROPERTY_ID=(.+)/', $envContent, $matches)) {
                        $propertyIdFromEnv = trim($matches[1]);
                    }
                }
            }
            
            $this->propertyId = "properties/" . $propertyIdFromEnv;
            
            Log::info('GoogleAnalyticsService initialized', [
                'property_id_from_env' => $propertyIdFromEnv,
                'full_property_id' => $this->propertyId
            ]);
            
            if (empty($propertyIdFromEnv)) {
                Log::error('GOOGLE_ANALYTICS_PROPERTY_ID is not set in environment');
            }
        } catch (\Exception $e) {
            Log::error('Google Analytics Service Init Error: ' . $e->getMessage());
        }
    }

    /**
     * Fetch URL-wise Page Views with caching
     * Returns array with pagePath as key and view count as value
     * NEVER returns empty - always fetches from API if cache is empty
     * 
     * @param int|string $days Number of days (30, 28, etc.) or 'all' for all-time data
     */
    public function getPageTotalViews($days = 30)
    {
        // Increase memory limit for large GA responses
        ini_set('memory_limit', '512M');
        
        if (!$this->analytics || !$this->propertyId) {
            Log::error('Google Analytics not initialized');
            return [];
        }
        
        // Check if property ID is valid (not just "properties/")
        if ($this->propertyId === 'properties/' || empty(str_replace('properties/', '', $this->propertyId))) {
            Log::error('Google Analytics Property ID is empty or invalid', [
                'property_id' => $this->propertyId,
                'env_value' => config('app.google_analytics_property_id')
            ]);
            return [];
        }

        $cacheKey = 'ga_page_views_' . ($days === 'all' ? 'alltime' : $days . '_days');
        $cacheDuration = ($days === 'all') ? 12 * 3600 : 6 * 3600; // 12 hours for all-time, 6 hours for specific days
        
        // Try to get from cache first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null && is_array($cachedData) && count($cachedData) > 0) {
            Log::info('Returning GA data from cache', ['count' => count($cachedData)]);
            return $cachedData;
        }
        
        // If not in cache, check if we're currently fetching (to prevent multiple simultaneous API calls)
        $fetchingKey = $cacheKey . '_fetching';
        $isFetching = Cache::get($fetchingKey);
        
        if ($isFetching) {
            // Another process is fetching, wait a bit and try to get cache again
            Log::info('Another process is fetching GA data, waiting...');
            sleep(3); // Wait 3 seconds
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null && is_array($cachedData) && count($cachedData) > 0) {
                Log::info('GA data now available from cache after waiting');
                return $cachedData;
            }
            // If still not available after waiting, proceed to fetch ourselves
            Log::warning('GA data still not available after waiting, fetching ourselves');
        }
        
        // Mark that we're fetching
        Cache::put($fetchingKey, true, 120); // 120 seconds lock (2 minutes)
        
        // Fetch from API
        try {
            Log::info("Fetching Google Analytics data for " . ($days === 'all' ? 'all-time' : "last {$days} days"), [
                'property_id' => $this->propertyId
            ]);
            
            // Determine date range based on $days parameter
            // For all-time, use 365 days (1 year) to get comprehensive data without hitting API limits
            $startDate = ($days === 'all') ? '365daysAgo' : $days . 'daysAgo';
            
            $request = new RunReportRequest([
                'dateRanges' => [
                    new DateRange([
                        'startDate' => $startDate,
                        'endDate' => 'today'
                    ])
                ],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews'])
                ],
                'dimensions' => [
                    new Dimension(['name' => 'pagePath']),
                    new Dimension(['name' => 'pageTitle'])
                ],
                'limit' => 10000 // Reduced from 50000 to avoid memory issues
            ]);

            $response = $this->analytics->properties->runReport(
                $this->propertyId,
                $request
            );

            $views = [];
            $titleViews = [];
            $pathTitleMap = []; // Map path to title

            if ($response->getRows()) {
                foreach ($response->getRows() as $row) {
                    $path = $row->getDimensionValues()[0]->getValue();
                    $title = $row->getDimensionValues()[1]->getValue();
                    $count = (int) $row->getMetricValues()[0]->getValue();
                    
                    // Store by path
                    if (!isset($views[$path])) {
                        $views[$path] = 0;
                    }
                    $views[$path] += $count;
                    
                    // Store path-to-title mapping
                    $pathTitleMap[$path] = $title;
                    
                    // Also store by title for matching
                    if (!isset($titleViews[$title])) {
                        $titleViews[$title] = 0;
                    }
                    $titleViews[$title] += $count;
                }
                
                Log::info('Google Analytics data fetched successfully. Total URLs: ' . count($views) . ', Total Titles: ' . count($titleViews));
            } else {
                Log::warning('No rows returned from Google Analytics');
            }

            // Store all in cache with explicit duration
            if (count($views) > 0) {
                Cache::put($cacheKey, $views, $cacheDuration);
                Cache::put('ga_title_views_' . ($days === 'all' ? 'alltime' : $days . '_days'), $titleViews, $cacheDuration);
                Cache::put('ga_path_title_map_' . ($days === 'all' ? 'alltime' : $days . '_days'), $pathTitleMap, $cacheDuration);
                Log::info('GA data stored in cache', ['count' => count($views), 'duration_seconds' => $cacheDuration, 'type' => ($days === 'all' ? 'all-time' : $days . ' days')]);
            }
            
            // Remove fetching lock
            Cache::forget($fetchingKey);

            return $views;
        } catch (\Exception $e) {
            Log::error('GA getPageTotalViews Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Remove fetching lock
            Cache::forget($fetchingKey);
            
            // Return empty array - better to show 0 than crash
            return [];
        }
    }
    
    /**
     * Get path-to-title mapping
     * @param int|string $days Number of days or 'all' for all-time
     */
    public function getPathTitleMap($days = 30)
    {
        $cacheKey = 'ga_path_title_map_' . ($days === 'all' ? 'alltime' : $days . '_days');
        
        // Try to get from cache first
        $pathTitleMap = Cache::get($cacheKey);
        
        if ($pathTitleMap === null || !is_array($pathTitleMap) || count($pathTitleMap) === 0) {
            // If not in cache, fetch all data which will populate it
            $this->getPageTotalViews($days);
            $pathTitleMap = Cache::get($cacheKey, []);
        }
        
        return $pathTitleMap;
    }

    /**
     * Get page views by title
     * @param int|string $days Number of days or 'all' for all-time
     */
    public function getPageViewsByTitle($days = 30)
    {
        $cacheKey = 'ga_title_views_' . ($days === 'all' ? 'alltime' : $days . '_days');
        
        // Try to get from cache first
        $titleViews = Cache::get($cacheKey);
        
        if ($titleViews === null || !is_array($titleViews) || count($titleViews) === 0) {
            // If not in cache, fetch all data which will populate it
            $this->getPageTotalViews($days);
            $titleViews = Cache::get($cacheKey, []);
        }
        
        return $titleViews;
    }

    /**
     * Get view count for a specific URL
     */
    public function getPageViewsForUrl($url, $days = 7)
    {
        $allViews = $this->getPageTotalViews($days);
        return $allViews[$url] ?? 0;
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        Cache::forget('ga_page_views_7_days');
        Cache::forget('ga_page_views_30_days');
        Cache::forget('ga_page_views_90_days');
        Cache::forget('ga_title_views_7_days');
        Cache::forget('ga_title_views_30_days');
        Cache::forget('ga_title_views_90_days');
        Cache::forget('ga_path_title_map_7_days');
        Cache::forget('ga_path_title_map_30_days');
        Cache::forget('ga_path_title_map_90_days');
        Log::info('Google Analytics cache cleared');
    }

    // Keep existing methods for backward compatibility
    public function getPageTotalViews1()
    {
        $request = new RunReportRequest([
            'dateRanges' => [
                new DateRange([
                    'startDate' => '7daysAgo',
                    'endDate' => 'today'
                ])
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews'])
            ],
            'dimensions' => [
                new Dimension(['name' => 'pagePath'])
            ]
        ]);

        $response = $this->analytics->properties->runReport(
            $this->propertyId,
            $request
        );
        
        $views = [];

        foreach ($response->getRows() as $row) {
            $path = $row->getDimensionValues()[0]->getValue();
            $count = $row->getMetricValues()[0]->getValue();
            $views[$path] = $count;
        }

        return $views;
    }
    
    public function getPageViews()
    {
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => '1daysAgo',
                    'endDate' => 'today'
                ])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'screenPageViews'])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'pagePath'])
            ]
        ]);

        return $this->analytics->properties->runReport($this->propertyId, $request);
    }

     public function getUsersByCountry()
    {
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => '1daysAgo',
                    'endDate' => 'today'
                ])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'countryId'])
            ]
        ]);

        return $this->analytics->properties->runReport($this->propertyId, $request);
    }

    // Fetch Active Users by Date
    public function getUsersByDate()
    {
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => '1daysAgo',
                    'endDate' => 'today'
                ])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'date'])
            ]
        ]);

        return $this->analytics->properties->runReport($this->propertyId, $request);
    }

    public function getActiveUsers()
    {
        $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
                'metrics' => [
                    new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
                ]
        ]);

        $response = $this->analytics->properties->runRealtimeReport($this->propertyId, $request);

        $activeUsers = 0;
        if (!empty($response->getRows())) {
            $activeUsers = (int) $response->getRows()[0]->getMetricValues()[0]->getValue();
        }

        return response()->json([
            "activeUsersLast30Minutes" => $activeUsers
        ]);
    }
    
    public function getActiveUsersFor5minutes()
    {
        $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'minuteRanges' => [
                new \Google\Service\AnalyticsData\MinuteRange([
                    'startMinutesAgo' => 4,
                    'endMinutesAgo' => 0
                ])
            ]
        ]);

        $response = $this->analytics->properties->runRealtimeReport($this->propertyId, $request);

        $activeUsers = 0;
        if (!empty($response->getRows())) {
            $activeUsers = (int) $response->getRows()[0]->getMetricValues()[0]->getValue();
        }

        return response()->json([
            "activeUsersLast5Minutes" => $activeUsers
        ]);
    }

    public function getUsersByFirstUserSource()
    {
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => '1daysAgo',
                    'endDate' => 'today'
                ])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'firstUserSource'])
            ],
            'limit' => 10
        ]);

        $response = $this->analytics->properties->runReport($this->propertyId, $request);
    
        if (!($response instanceof \Google\Service\AnalyticsData\RunReportResponse)) {
            throw new \Exception("Invalid response from Google Analytics API");
        }

        $formattedData = [];

        if ($response->getRows()) {
            foreach ($response->getRows() as $row) {
                $dimensions = $row->getDimensionValues();
                $metrics = $row->getMetricValues();

                $formattedData[] = [
                    'pageTitle'   => $dimensions[0]->getValue(),
                    'views'       => $metrics[0]->getValue(),
                ];
            }
        }
        
        return $formattedData;
    }

 

    /**
     * Get real-time views by page title and screen name
     * This fetches TODAY's data with SHORT caching (60 seconds) to avoid API quota issues
     * Uses pageTitle AND screenName dimensions to match GA dashboard exactly
     */
    public function getViewsByPageTitleAndScreenName()
    {
        // Increase memory limit
        ini_set('memory_limit', '512M');
        
        if (!$this->analytics || !$this->propertyId) {
            Log::error('Google Analytics not initialized for real-time data');
            return [];
        }
        
        // SHORT-TERM CACHE: 60 seconds to avoid high volume errors
        $cacheKey = 'ga_realtime_page_views';
        $cacheDuration = 60; // 60 seconds only
        
        // Try to get from cache first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null && is_array($cachedData) && count($cachedData) > 0) {
            Log::info('Returning real-time page views from cache', ['count' => count($cachedData)]);
            return $cachedData;
        }
        
        // Check if another process is fetching
        $fetchingKey = $cacheKey . '_fetching';
        $isFetching = Cache::get($fetchingKey);
        
        if ($isFetching) {
            Log::info('Another process is fetching real-time data, waiting...');
            sleep(1);
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null && is_array($cachedData)) {
                return $cachedData;
            }
        }
        
        // Mark that we're fetching
        Cache::put($fetchingKey, true, 30); // 30 seconds lock
        
        try {
            Log::info("Fetching REAL-TIME Views by Page Title and Screen Name from GA API");
            
            $request = new \Google\Service\AnalyticsData\RunReportRequest([
                'dateRanges' => [
                    new \Google\Service\AnalyticsData\DateRange([
                        'startDate' => 'today',
                        'endDate' => 'today'
                    ])
                ],
                'metrics' => [
                    new \Google\Service\AnalyticsData\Metric(['name' => 'screenPageViews'])
                ],
                'dimensions' => [
                    new \Google\Service\AnalyticsData\Dimension(['name' => 'pageTitle']),
                    new \Google\Service\AnalyticsData\Dimension(['name' => 'unifiedScreenName']) // Screen name for app/web
                ],
                'limit' => 1000 // Get more data to group by title, then we'll limit to 10 after grouping
            ]);

            $response = $this->analytics->properties->runReport($this->propertyId, $request);
            
            if (!($response instanceof \Google\Service\AnalyticsData\RunReportResponse)) {
                throw new \Exception("Invalid response from Google Analytics API");
            }

            // GROUP BY TITLE to remove duplicates (combine website + app)
            $titleGroups = [];

            if ($response->getRows()) {
                foreach ($response->getRows() as $row) {
                    $dimensions = $row->getDimensionValues();
                    $metrics = $row->getMetricValues();

                    $title = $dimensions[0]->getValue();
                    $screenName = $dimensions[1]->getValue();
                    $views = (int) $metrics[0]->getValue();
                    
                    // Initialize if not exists
                    if (!isset($titleGroups[$title])) {
                        $titleGroups[$title] = [
                            'pageTitle' => $title,
                            'screenName' => $screenName,
                            'pagePath' => '', // Will be set below
                            'views' => 0,
                            'websiteViews' => 0,
                            'appViews' => 0,
                            'screenNames' => []
                        ];
                    }
                    
                    // Add to total views
                    $titleGroups[$title]['views'] += $views;
                    
                    // Track screen names
                    if (!in_array($screenName, $titleGroups[$title]['screenNames'])) {
                        $titleGroups[$title]['screenNames'][] = $screenName;
                    }
                    
                    // Set pagePath (use first non-empty screen name as path)
                    if (empty($titleGroups[$title]['pagePath']) && !empty($screenName)) {
                        $titleGroups[$title]['pagePath'] = $screenName;
                    }
                    
                    // Categorize as website or app based on screen name
                    // Web screens usually have domain names, app screens have app identifiers
                    if (stripos($screenName, 'gstv') !== false || stripos($screenName, 'app') !== false) {
                        $titleGroups[$title]['appViews'] += $views;
                    } else {
                        $titleGroups[$title]['websiteViews'] += $views;
                    }
                }
                
                Log::info('GA data fetched. Total unique titles: ' . count($titleGroups));
            } else {
                Log::warning('No real-time data returned from Google Analytics');
            }

            // Convert to array and sort by views descending
            $formattedData = array_values($titleGroups);
            usort($formattedData, function($a, $b) {
                return $b['views'] - $a['views'];
            });
            
            // Limit to top 10 unique titles
            $formattedData = array_slice($formattedData, 0, 10);
            
            Log::info('Real-time page views processed. Unique titles: ' . count($formattedData));
            
            // Cache for 60 seconds
            if (count($formattedData) > 0) {
                Cache::put($cacheKey, $formattedData, $cacheDuration);
                Log::info('Real-time data cached for 60 seconds');
            }
            
            // Remove fetching lock
            Cache::forget($fetchingKey);
            
            return $formattedData;
            
        } catch (\Exception $e) {
            Log::error('GA getViewsByPageTitleAndScreenName Error: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            
            // Remove fetching lock
            Cache::forget($fetchingKey);
            
            // Return cached data even if expired
            $oldCache = Cache::get($cacheKey);
            if ($oldCache !== null && is_array($oldCache)) {
                Log::warning('Returning expired cache due to API error');
                return $oldCache;
            }
            
            return [];
        }
    }
    
    /**
     * Get real-time views by page title (LAST 30 MINUTES)
     * Fetches actual real-time data from Google Analytics for last 30 minutes
     * This matches the "Views by Page title and screen name" in GA Real-time dashboard
     */
    public function getViewsByPageTitleLast30Minutes()
    {
        // Increase memory limit
        ini_set('memory_limit', '512M');
        
        if (!$this->analytics || !$this->propertyId) {
            Log::error('Google Analytics not initialized for real-time 30-minute data');
            return [];
        }
        
        // SHORT-TERM CACHE: 60 seconds to avoid high volume errors
        $cacheKey = 'ga_realtime_page_views_30min';
        $cacheDuration = 60; // 60 seconds only
        
        // Try to get from cache first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null && is_array($cachedData) && count($cachedData) > 0) {
            Log::info('Returning real-time 30-minute page views from cache', ['count' => count($cachedData)]);
            return $cachedData;
        }
        
        // Check if another process is fetching
        $fetchingKey = $cacheKey . '_fetching';
        $isFetching = Cache::get($fetchingKey);
        
        if ($isFetching) {
            Log::info('Another process is fetching real-time 30-minute data, waiting...');
            sleep(1);
            $cachedData = Cache::get($cacheKey);
            if ($cachedData !== null && is_array($cachedData)) {
                return $cachedData;
            }
        }
        
        // Mark that we're fetching
        Cache::put($fetchingKey, true, 30); // 30 seconds lock
        
        try {
            Log::info("Fetching REAL-TIME (Last 30 Minutes) Views using unifiedScreenName dimension");
            
            // Use runRealtimeReport with correct dimensions
            // Real-time API supports: unifiedScreenName, but not pageTitle
            // We'll use unifiedScreenName which contains the page path/title
            $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
                'metrics' => [
                    new \Google\Service\AnalyticsData\Metric(['name' => 'screenPageViews'])
                ],
                'dimensions' => [
                    new \Google\Service\AnalyticsData\Dimension(['name' => 'unifiedScreenName'])
                ],
                'limit' => 15, // Fetch 15 to ensure we have 10 after filtering out "(other)"
                'orderBys' => [
                    new \Google\Service\AnalyticsData\OrderBy([
                        'metric' => new \Google\Service\AnalyticsData\MetricOrderBy([
                            'metricName' => 'screenPageViews'
                        ]),
                        'desc' => true
                    ])
                ]
            ]);

            $response = $this->analytics->properties->runRealtimeReport($this->propertyId, $request);
            
            if (!($response instanceof \Google\Service\AnalyticsData\RunRealtimeReportResponse)) {
                throw new \Exception("Invalid response from Google Analytics Realtime API");
            }

            // Process data - filter out "(other)" and get top 10
            $formattedData = [];

            if ($response->getRows()) {
                foreach ($response->getRows() as $row) {
                    $dimensions = $row->getDimensionValues();
                    $metrics = $row->getMetricValues();

                    $screenName = $dimensions[0]->getValue();
                    $views = (int) $metrics[0]->getValue();
                    
                    // Skip "(other)" entries
                    if (strtolower($screenName) === '(other)' || $screenName === '(other)') {
                        Log::info('Skipping "(other)" entry with ' . $views . ' views');
                        continue;
                    }
                    
                    // Use screenName as the title
                    $formattedData[] = [
                        'pageTitle' => $screenName,
                        'screenName' => $screenName,
                        'pagePath' => $screenName,
                        'views' => $views,
                        'websiteViews' => $views, // Can't differentiate in real-time
                        'appViews' => 0,
                        'screenNames' => [$screenName]
                    ];
                }
                
                Log::info('GA 30-minute real-time data fetched. Total entries (excluding other): ' . count($formattedData));
            } else {
                Log::warning('No real-time 30-minute data returned from Google Analytics');
            }
            
            // Limit to top 10 (after filtering out "other")
            $formattedData = array_slice($formattedData, 0, 10);
            
            Log::info('Real-time 30-minute page views processed. Entries: ' . count($formattedData));
            
            // Cache for 60 seconds
            if (count($formattedData) > 0) {
                Cache::put($cacheKey, $formattedData, $cacheDuration);
                Log::info('Real-time 30-minute data cached for 60 seconds');
            }
            
            // Remove fetching lock
            Cache::forget($fetchingKey);
            
            return $formattedData;
            
        } catch (\Exception $e) {
            Log::error('GA getViewsByPageTitleLast30Minutes Error: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            
            // Remove fetching lock
            Cache::forget($fetchingKey);
            
            // Return cached data even if expired
            $oldCache = Cache::get($cacheKey);
            if ($oldCache !== null && is_array($oldCache)) {
                Log::warning('Returning expired cache due to API error');
                return $oldCache;
            }
            
            return [];
        }
    }
    
    /**
     * Get real-time active users (last 30 minutes)
     * SHORT caching (60 seconds) to avoid API quota issues
     */
    public function getRealtimeActiveUsers()
    {
        // SHORT-TERM CACHE: 60 seconds
        $cacheKey = 'ga_realtime_active_users';
        $cacheDuration = 60; // 60 seconds
        
        // Try cache first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            Log::info('Returning real-time active users from cache: ' . $cachedData);
            return $cachedData;
        }
        
        try {
            $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
                'metrics' => [
                    new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
                ]
            ]);

            $response = $this->analytics->properties->runRealtimeReport($this->propertyId, $request);

            $activeUsers = 0;
            if (!empty($response->getRows())) {
                $activeUsers = (int) $response->getRows()[0]->getMetricValues()[0]->getValue();
            }

            Log::info('Real-time active users fetched from API: ' . $activeUsers);
            
            // Cache for 60 seconds
            Cache::put($cacheKey, $activeUsers, $cacheDuration);
            
            return $activeUsers;
            
        } catch (\Exception $e) {
            Log::error('GA getRealtimeActiveUsers Error: ' . $e->getMessage());
            
            // Return cached data even if expired
            $oldCache = Cache::get($cacheKey);
            if ($oldCache !== null) {
                Log::warning('Returning expired cache due to API error');
                return $oldCache;
            }
            
            return 0;
        }
    }
    
    /**
     * Get real-time page views by source
     * SHORT caching (60 seconds) to avoid API quota issues
     */
    public function getRealtimeViewsBySource()
    {
        // SHORT-TERM CACHE: 60 seconds
        $cacheKey = 'ga_realtime_views_by_source';
        $cacheDuration = 60; // 60 seconds
        
        // Try cache first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null && is_array($cachedData) && count($cachedData) > 0) {
            Log::info('Returning real-time views by source from cache', ['count' => count($cachedData)]);
            return $cachedData;
        }
        
        try {
            $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
                'metrics' => [
                    new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
                ],
                'dimensions' => [
                    new \Google\Service\AnalyticsData\Dimension(['name' => 'firstUserSource'])
                ],
                'limit' => 10
            ]);

            $response = $this->analytics->properties->runRealtimeReport($this->propertyId, $request);

            $formattedData = [];

            if ($response->getRows()) {
                foreach ($response->getRows() as $row) {
                    $dimensions = $row->getDimensionValues();
                    $metrics = $row->getMetricValues();

                    $formattedData[] = [
                        'source' => $dimensions[0]->getValue(),
                        'activeUsers' => (int) $metrics[0]->getValue(),
                    ];
                }
            }
            
            Log::info('Real-time views by source fetched from API: ' . count($formattedData) . ' sources');
            
            // Cache for 60 seconds
            if (count($formattedData) > 0) {
                Cache::put($cacheKey, $formattedData, $cacheDuration);
            }
            
            return $formattedData;
            
        } catch (\Exception $e) {
            Log::error('GA getRealtimeViewsBySource Error: ' . $e->getMessage());
            
            // Return cached data even if expired
            $oldCache = Cache::get($cacheKey);
            if ($oldCache !== null && is_array($oldCache)) {
                Log::warning('Returning expired cache due to API error');
                return $oldCache;
            }
            
            return [];
        }
    }
 
  
  
}
