<?php

namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\News;
use Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Services\GoogleAnalyticsService;
use Google\Client;
use Barryvdh\DomPDF\Facade as PDF;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }
  	
  	public function index()
    {  
        $today = Carbon::today()->format('Y-m-d');
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // REAL-TIME DATA (60-second caching)
        // Active users in last 30 minutes
        $activeUsersResponse = $this->analyticsService->getActiveUsers();
        $data = $activeUsersResponse->getData();
        $activeUsers = $data->activeUsersLast30Minutes;

        // Active users in last 5 minutes
        $activeUsersResponsebyminutes = $this->analyticsService->getActiveUsersFor5minutes();
        $databyminutes = $activeUsersResponsebyminutes->getData();
        $activeUsersbyminutes = $databyminutes->activeUsersLast5Minutes;

        // Real-time active users (alternative method)
        $realtimeActiveUsers = $this->analyticsService->getRealtimeActiveUsers();

        // User sources
        $sourceUsers = $this->analyticsService->getUsersByFirstUserSource();
        
        // Real-time views by source
        $realtimeViewsBySource = $this->analyticsService->getRealtimeViewsBySource();

        // REAL-TIME: Views by Page Title and Screen Name (TODAY's data, 60s cache)
        $ViewsByPageTitleAndScreenName = $this->analyticsService->getViewsByPageTitleAndScreenName();

        // REAL-TIME: Views by Page Title and Screen Name (LAST 30 MINUTES, 60s cache)
        $ViewsByPageTitleLast30Minutes = $this->analyticsService->getViewsByPageTitleLast30Minutes();

        // NEW: Get top 10 latest news with real-time GA counts
        $latestNewsWithRealtimeViews = $this->getLatestNewsWithRealtimeViews(10);

        // Today Top 10 Stories now loaded via AJAX (/dashboard/top10ajax)
        // No longer pre-computed on page load

        // Prepare data array
        $data = [
            'activeUsers' => $activeUsers,
            'activeUsersbyminutes' => $activeUsersbyminutes,
            'realtimeActiveUsers' => $realtimeActiveUsers,
            'sourceUsers' => $sourceUsers,
            'realtimeViewsBySource' => $realtimeViewsBySource,
            'ViewsByPageTitleAndScreenName' => $ViewsByPageTitleAndScreenName,
            'ViewsByPageTitleLast30Minutes' => $ViewsByPageTitleLast30Minutes,
            'latestNewsWithRealtimeViews' => $latestNewsWithRealtimeViews,
            'lastUpdated' => now()->format('Y-m-d H:i:s')
        ]; 

        return view('dashboard.dashboard', compact('data'));
    }
    
    /**
     * Get Today's Top 10 Stories for News, Videos, and Web Stories tabs
     * Views = GA website + GA app + DB count, sorted descending, today only
     */
    private function getTodayTop10(): array
    {
        $today     = Carbon::today()->toDateString();
        $baseUrl   = config('app.front_url');
        $backendUrl = config('app.backend_url');

        // ── Fetch GA data (cached — same keys as NewsController) ─────────────
        $pageViews       = [];
        $titleToAppCount = [];
        try {
            $pageViews365    = \Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
            $pathTitleMap365 = \Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
            $pageViews30     = \Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
            $pathTitleMap30  = \Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

            // Merge page views
            $pageViews = $pageViews365;
            foreach ($pageViews30 as $path => $cnt) {
                $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $cnt) : $cnt;
            }

            // Build title → app GA count map (O(1) lookup)
            $merged = array_merge($pathTitleMap365, $pathTitleMap30);
            foreach ($merged as $path => $title) {
                if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                    $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                    $titleToAppCount[$title] = ($titleToAppCount[$title] ?? 0) + $c;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard getTodayTop10 GA error: ' . $e->getMessage());
        }

        // ── Helper: compute total views for a row ─────────────────────────────
        $computeTotal = function (string $pagePath, string $title, int $dbCount) use ($pageViews, $titleToAppCount): int {
            return (int)($pageViews[$pagePath] ?? 0)
                 + (int)($titleToAppCount[$title] ?? 0)
                 + $dbCount;
        };

        // ── 1. NEWS (today, Active, not video category) ───────────────────────
        $newsRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->whereDate('n.created_at', $today)
            ->whereNotIn('n.id', function ($q) {
                $q->select('news_id')->from('news_category')->where('category_id', 9);
            })
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $news = $newsRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'      => $r->title,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();

        // ── 2. VIDEOS (today, Active, category_id = 9) ───────────────────────
        $videoRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->whereDate('n.created_at', $today)
            ->whereIn('n.id', function ($q) {
                $q->select('news_id')->from('news_category')->where('category_id', 9);
            })
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $videos = $videoRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'       => $r->id,
                'title'    => $r->title,
                'views'    => $total,
                'url'      => $baseUrl . $pp,
                'edit_url' => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();

        // ── 3. WEB STORIES (today, Active) ───────────────────────────────────
        $storyRows = DB::table('webstory as ws')
            ->select(
                'ws.id', 'ws.title', 'ws.slug',
                DB::raw('IFNULL(wsc.view_count, 0) as db_count')
            )
            ->leftJoin('webstorycount as wsc', 'ws.id', '=', 'wsc.content_id')
            ->where('ws.status', 'Active')
            ->where('ws.created_at', '>=', Carbon::now()->subDays(5)->startOfDay())
            ->get();

        $webstories = $storyRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/web-stories/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'       => $r->id,
                'title'    => $r->title,
                'views'    => $total,
                'url'      => $baseUrl . $pp,
                'edit_url' => $backendUrl . '/webstory',
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();

        return [
            'news'       => $news,
            'videos'     => $videos,
            'webstories' => $webstories,
        ];
    }

    /**
     * Get latest news with real-time Google Analytics view counts
     * 
     * @param int $limit Number of latest news to fetch
     * @return array
     */
    private function getLatestNewsWithRealtimeViews($limit = 10)
    {
        // Fetch latest news from database - FIXED to avoid duplicates
        $latestNews = DB::table('news as n')
            ->select(
                'n.id',
                'n.title',
                'n.slug',
                'n.created_at',
                'n.status'
            )
            ->where('n.status', 'Active')
            ->whereNotIn('n.id', function ($query) {
                $query->select('news_id')
                    ->from('news_category')
                    ->where('category_id', 9); // Exclude videos
            })
            ->orderBy('n.created_at', 'DESC')
            ->limit($limit)
            ->get();

        // Get categories for each news separately to avoid duplicates
        $newsIds = $latestNews->pluck('id')->toArray();
        
        $categories = DB::table('news_category as nc')
            ->join('categories as c', 'nc.category_id', '=', 'c.id')
            ->whereIn('nc.news_id', $newsIds)
            ->select('nc.news_id', 'c.slug', 'c.title')
            ->get()
            ->groupBy('news_id');

        // Get real-time GA data (cached for 60 seconds)
        $gaPageViews = $this->analyticsService->getViewsByPageTitleAndScreenName();
        
        // Create a map of page paths to view counts
        $gaViewsMap = [];
        foreach ($gaPageViews as $page) {
            $gaViewsMap[$page['pagePath']] = $page['views'];
        }
        
        // Combine news with GA data
        $newsWithViews = [];
        $baseUrl = config('app.front_url');
        
        foreach ($latestNews as $news) {
            // Get categories for this news
            $newsCategories = $categories->get($news->id, collect());
            $categorySlugs = $newsCategories->pluck('slug')->implode('/');
            $categoryNames = $newsCategories->pluck('title')->implode(', ');
            
            $isVideo = strpos($categoryNames, 'Videos') !== false;
            
            // Construct URL paths
            $websitePath = $isVideo
                ? "/{$categorySlugs}/{$news->slug}"
                : "/news/{$categorySlugs}/{$news->slug}";
            
            // Get website views
            $websiteViews = $gaViewsMap[$websitePath] ?? 0;
            
            // Get app views by matching title
            $appViews = 0;
            foreach ($gaPageViews as $page) {
                // Check if this is an app URL and title matches
                if (preg_match('#^/(GSTV|gstv)/#', $page['pagePath'])) {
                    // Match by title (case-insensitive)
                    if (strcasecmp($page['pageTitle'], $news->title) === 0) {
                        $appViews += $page['views'];
                    }
                }
            }
            
            // Total views = website + app
            $totalViews = $websiteViews + $appViews;
            
            $newsWithViews[] = [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'categoryNames' => $categoryNames ?: 'Uncategorized',
                'categorySlugs' => $categorySlugs ?: 'uncategorized',
                'created_at' => Carbon::parse($news->created_at)->format('d M Y h:i A'),
                'url' => $baseUrl . $websitePath,
                'websiteViews' => $websiteViews,
                'appViews' => $appViews,
                'totalViews' => $totalViews,
                'isRealtime' => true // Flag to indicate this is real-time data
            ];
        }
        
        // Ensure we return exactly $limit items (no duplicates)
        return array_slice($newsWithViews, 0, $limit);
    }
    
    /**
     * AJAX endpoint for Today Top 10 Stories (News / Videos / Web Stories)
     * Called from dashboard JS on tab click
     */
    public function getTodayTop10Ajax(Request $request)
    {
        try {
            $type = $request->get('type', 'news'); // news | videos | webstories
            $data = $this->getTodayTop10();

            $result = match($type) {
                'videos'     => $data['videos'],
                'webstories' => $data['webstories'],
                default      => $data['news'],
            };

            return response()->json([
                'success' => true,
                'type'    => $type,
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX endpoint for real-time data refresh
     * This can be called every 60 seconds to update dashboard
     */
    public function getRealtimeData()
    {
        try {
            // Fetch real-time data
            $realtimeActiveUsers = $this->analyticsService->getRealtimeActiveUsers();
            $realtimeViewsBySource = $this->analyticsService->getRealtimeViewsBySource();
            $ViewsByPageTitleAndScreenName = $this->analyticsService->getViewsByPageTitleAndScreenName();
            $ViewsByPageTitleLast30Minutes = $this->analyticsService->getViewsByPageTitleLast30Minutes();
            
            // Get latest news with real-time views
            $latestNewsWithRealtimeViews = $this->getLatestNewsWithRealtimeViews(10);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'realtimeActiveUsers' => $realtimeActiveUsers,
                    'realtimeViewsBySource' => $realtimeViewsBySource,
                    'ViewsByPageTitleAndScreenName' => $ViewsByPageTitleAndScreenName,
                    'ViewsByPageTitleLast30Minutes' => $ViewsByPageTitleLast30Minutes,
                    'latestNewsWithRealtimeViews' => $latestNewsWithRealtimeViews,
                    'lastUpdated' => now()->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getalldatas1()
    {
        $baseUrl   = config('app.front_url');
        $backendUrl = config('app.backend_url');
        $pageViews       = [];
        $titleToAppCount = [];
        try {
            $pageViews365    = \Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
            $pathTitleMap365 = \Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
            $pageViews30     = \Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
            $pathTitleMap30  = \Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

            // Merge page views
            $pageViews = $pageViews365;
            foreach ($pageViews30 as $path => $cnt) {
                $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $cnt) : $cnt;
            }

            // Build title → app GA count map (O(1) lookup)
            $merged = array_merge($pathTitleMap365, $pathTitleMap30);
            foreach ($merged as $path => $title) {
                if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                    $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                    $titleToAppCount[$title] = ($titleToAppCount[$title] ?? 0) + $c;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard getTodayTop10 GA error: ' . $e->getMessage());
        }

        // ── Helper: compute total views for a row ─────────────────────────────
        $computeTotal = function (string $pagePath, string $title, int $dbCount) use ($pageViews, $titleToAppCount): int {
            return (int)($pageViews[$pagePath] ?? 0)
                 + (int)($titleToAppCount[$title] ?? 0)
                 + $dbCount;
        };
        $yesterday = Carbon::yesterday()->toDateString();
        $newsRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug','u.name as user_name',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
          ->leftJoin('users as u', 'u.id', '=', 'n.userID')   // Join users table
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->whereDate('n.created_at', $yesterday)           
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $news = $newsRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'      => $r->title,
                'user_name' => $r->user_name,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();


        $query = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = CURDATE()) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [3])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal
            // Get all filtered data
        $userData = $query->get();

        $query1 = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = CURDATE()) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [4])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal
            // Get all filtered data
        $tvData = $query1->get();

      
    
        $yesterday = Carbon::yesterday()->toDateString();

        $newsSourceCount = DB::table('news as n')
            ->join('tags as t', 't.id', '=', 'n.newssource')
            ->select(
                'n.newssource',
                't.title as source_name',
                DB::raw('COUNT(n.id) as total_news')
            )
            ->whereDate('n.created_at', $yesterday)
            ->whereIn('n.newssource', [1, 2])
            ->where('n.status', 'Active')
            ->groupBy('n.newssource', 't.title')
            ->get()
            ->toArray();

       
        $pdf = Pdf::loadView('pdf.daily-report', [
            'newsSourceCount' => $newsSourceCount,
            'news'            => $news,
            'userData'        => $userData,
            'tvData'        => $tvData,
            'reportDate'      => Carbon::yesterday()->format('d-m-Y')
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Yesterday_Report_' . Carbon::yesterday()->format('d-m-Y') . '.pdf');
    }

    public function getalldatas_latest()
    {
        $baseUrl   = config('app.front_url');
        $backendUrl = config('app.backend_url');
        $pageViews       = [];
        $titleToAppCount = [];
        try {
            $pageViews365    = \Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
            $pathTitleMap365 = \Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
            $pageViews30     = \Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
            $pathTitleMap30  = \Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

            // Merge page views
            $pageViews = $pageViews365;
            foreach ($pageViews30 as $path => $cnt) {
                $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $cnt) : $cnt;
            }

            // Build title → app GA count map (O(1) lookup)
            $merged = array_merge($pathTitleMap365, $pathTitleMap30);
            foreach ($merged as $path => $title) {
                if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                    $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                    $titleToAppCount[$title] = ($titleToAppCount[$title] ?? 0) + $c;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard getTodayTop10 GA error: ' . $e->getMessage());
        }

        // ── Helper: compute total views for a row ─────────────────────────────
        $computeTotal = function (string $pagePath, string $title, int $dbCount) use ($pageViews, $titleToAppCount): int {
            return (int)($pageViews[$pagePath] ?? 0)
                 + (int)($titleToAppCount[$title] ?? 0)
                 + $dbCount;
        };
        $yesterday = Carbon::yesterday()->toDateString();
        $newsRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug','u.name as user_name',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
          ->leftJoin('users as u', 'u.id', '=', 'n.userID')   // Join users table
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->whereDate('n.created_at', $yesterday)
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $news = $newsRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'      => $r->title,
                'user_name' => $r->user_name,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();


        $query = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [3])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal
                 // Get all filtered data
        $userData = $query->get();
    
    
        $query1 = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [4])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal
               
            // Get all filtered data
        $tvData = $query1->get();

    
        $yesterday = Carbon::yesterday()->toDateString();

        $newsSourceCount = DB::table('news as n')
            ->join('tags as t', 't.id', '=', 'n.newssource')
            ->select(
                'n.newssource',
                't.title as source_name',
                DB::raw('COUNT(n.id) as total_news')
            )
            ->whereDate('n.created_at', $yesterday)
            ->whereIn('n.newssource', [1, 2])
            ->where('n.status', 'Active')
            ->groupBy('n.newssource', 't.title')
            ->get()
            ->toArray();

       
        $pdf = Pdf::loadView('pdf.daily-report', [
          'newsSourceCount' => $newsSourceCount,
          'news'            => $news,
          'userData'        => $userData,
          'tvData'        => $tvData,
          'reportDate'      => Carbon::yesterday()->format('d-m-Y')
        ]);

      $pdf->setPaper('A4', 'portrait');

      return $pdf->download('Yesterday_Report_' . Carbon::yesterday()->format('d-m-Y') . '.pdf');
    }
    public function getalldatas()
    {
        $baseUrl   = config('app.front_url');
        $backendUrl = config('app.backend_url');
        $pageViews       = [];
        $titleToAppCount = [];
        try {
            $pageViews365    = \Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
            $pathTitleMap365 = \Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
            $pageViews30     = \Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
            $pathTitleMap30  = \Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

            // Merge page views
            $pageViews = $pageViews365;
            foreach ($pageViews30 as $path => $cnt) {
                $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $cnt) : $cnt;
            }

            // Build title → app GA count map (O(1) lookup)
            $merged = array_merge($pathTitleMap365, $pathTitleMap30);
            foreach ($merged as $path => $title) {
                if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                    $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                    $titleToAppCount[$title] = ($titleToAppCount[$title] ?? 0) + $c;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard getTodayTop10 GA error: ' . $e->getMessage());
        }

        // ── Helper: compute total views for a row ─────────────────────────────
        $computeTotal = function (string $pagePath, string $title, int $dbCount) use ($pageViews, $titleToAppCount): int {
            return (int)($pageViews[$pagePath] ?? 0)
                 + (int)($titleToAppCount[$title] ?? 0)
                 + $dbCount;
        };
        $yesterday = Carbon::yesterday()->toDateString();
        $newsRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug','u.name as user_name',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
          ->leftJoin('users as u', 'u.id', '=', 'n.userID')   // Join users table
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->whereDate('n.created_at', $yesterday)
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $news = $newsRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'     => $r->title,
                'user_name' => $r->user_name,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();
    
    
    $newsTVRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug','u.name as user_name',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
          ->leftJoin('users as u', 'u.id', '=', 'n.userID')   // Join users table
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->where('n.newssource', '1')
            ->whereDate('n.created_at', $yesterday)
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $newsTV = $newsTVRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'      => $r->title,
                'user_name' => $r->user_name,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();
    
    $newsDirectRows = DB::table('news as n')
            ->select(
                'n.id', 'n.title', 'n.slug','u.name as user_name',
                DB::raw('GROUP_CONCAT(DISTINCT c.slug  ORDER BY c.id ASC SEPARATOR "/") as categorySlugs'),
                DB::raw('GROUP_CONCAT(DISTINCT c.title ORDER BY c.id ASC SEPARATOR ", ") as categoryNames'),
                DB::raw('IFNULL(nc2.view_count, 0) as db_count')
            )
          ->leftJoin('users as u', 'u.id', '=', 'n.userID')   // Join users table
            ->leftJoin('news_category as nc', 'n.id', '=', 'nc.news_id')
            ->leftJoin('categories as c', 'nc.category_id', '=', 'c.id')
            ->leftJoin('newscount as nc2', 'n.id', '=', 'nc2.content_id')
            ->where('n.status', 'Active')
            ->where('n.newssource', '2')
            ->whereDate('n.created_at', $yesterday)
            ->groupBy('n.id', 'n.title', 'n.slug', 'nc2.view_count')
            ->get();

        $newsDirect = $newsDirectRows->map(function ($r) use ($computeTotal, $baseUrl, $backendUrl) {
            $pp    = "/news/{$r->categorySlugs}/{$r->slug}";
            $total = $computeTotal($pp, $r->title, (int)$r->db_count);
            return [
                'id'         => $r->id,
                'title'      => $r->title,
                'user_name' => $r->user_name,
                'views'      => $total,
                'url'        => $baseUrl . $pp,
                'edit_url'   => $backendUrl . '/news/edit/' . $r->id,
            ];
        })->sortByDesc('views')->values()->take(10)->toArray();


        $query = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [3])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal
                 // Get all filtered data
            $userData = $query->get();
    
    
        $query1 = DB::table('users')
            ->select(
                'users.name',
                'role.title as title',
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id) as total_news_count"),
                DB::raw("(SELECT COUNT(*) FROM news WHERE news.userID = users.id AND DATE(news.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as today_news_count")
            )
            ->leftJoin('roles as role', 'role.id', '=', 'users.roleID')
            ->where('users.status', 'Active')
            ->whereIn('users.roleID', [4])
            ->orderByDesc('today_news_count')
            ->orderBy('users.name'); // Optional: sort by name if counts are equal

            // Get all filtered data
            $tvData = $query1->get();

      
    
        $yesterday = Carbon::yesterday()->toDateString();

        $newsSourceCount = DB::table('news as n')
            ->join('tags as t', 't.id', '=', 'n.newssource')
            ->select(
                'n.newssource',
                't.title as source_name',
                DB::raw('COUNT(n.id) as total_news')
            )
            ->whereDate('n.created_at', $yesterday)
            ->whereIn('n.newssource', [1, 2])
            ->where('n.status', 'Active')
            ->groupBy('n.newssource', 't.title')
            ->get()
            ->toArray();

       
        $pdf = Pdf::loadView('pdf.daily-report', [
          'newsSourceCount' => $newsSourceCount,
          'news'            => $news,
          'newsDirect'  =>$newsDirect,
          'newsTV'            => $newsTV,
          'userData'        => $userData,
          'tvData'        => $tvData,
          'reportDate'      => Carbon::yesterday()->format('d-m-Y')
      ]);

      $pdf->setPaper('A4', 'portrait');

      return $pdf->download('Yesterday_Report_' . Carbon::yesterday()->format('d-m-Y') . '.pdf');
    }
}
