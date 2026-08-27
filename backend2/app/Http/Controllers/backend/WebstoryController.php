<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Webstory;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\CloudflareService;
use File;
use Image;
use App\Services\FirebaseService;
use App\Services\GoogleAnalyticsService;
use App\Models\Notification;
use Illuminate\Support\Facades\Redis;

class WebstoryController extends Controller
{
    protected $firebaseService;
    protected $cloudflareService;
    protected $analyticsService;

    public function __construct(FirebaseService $firebaseService, CloudflareService $cloudflareService, GoogleAnalyticsService $analyticsService)
    {
        $this->firebaseService   = $firebaseService;
        $this->cloudflareService = $cloudflareService;
        $this->analyticsService  = $analyticsService;
    }
    
    // Get all Webstory
    public function index()
    {
        return view('webstory.alllist');   
        $webstory = DB::table('webstory as c1')->select('c1.*', 'u1.name as authorName')->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')->orderby('c1.webOrder','DESC')->get();
        $data =  array();
        $data['webstory'] =  $webstory;
        return view('webstory.list', compact('data'));
    }
    
    // New WebStory Add
    public function add()
    {
        return view('webstory.add');
    }
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'englishTitle' => 'required',
            'files' => 'required|array',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480',
        ];

        $customMessages = [
            'title.required' => 'The title field is required.',
            'englishTitle.required' => 'The english title field is required.',
            'files.*.image' => 'The uploaded file must be an image.',
            'files.*.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'files.*.max' => 'The image size must not exceed 20 MB.',
        ];

        $request->validate($rules, $customMessages);

        $titles = $request->input('titles');
        $titles_credit = $request->input('titles_credit');
        $webimgs = $_FILES['files'];
        $jsonDataArray = [];

        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/backend/public/webstory/';
        $publicPath = config('app.front_url').'/backend/public/webstory';

        $startTime = microtime(true); // ✅ Start time for processing

        for ($i = 0; $i < count($webimgs['name']); $i++) {
            $randomCode = Str::random(8) . time();
            $tmpPath = $webimgs['tmp_name'][$i];
            $originalName = $webimgs['name'][$i];

            $imgname = str_replace(' ', '-', $originalName);
            $extension = pathinfo($imgname, PATHINFO_EXTENSION);
            $filenameWithoutExt = pathinfo($imgname, PATHINFO_FILENAME);
            $baseName = pathinfo($filenameWithoutExt, PATHINFO_FILENAME);
            $file = new \Illuminate\Http\UploadedFile($tmpPath, $originalName, null, null, true);

            // Move original file
            $file->move($destinationPath, $originalName);

            // Load and convert to WebP
            $img = Image::make($destinationPath . $originalName);
            $webpName = "{$baseName}_{$randomCode}.webp";
            $webpPath = $destinationPath . $webpName;
            $img->save($webpPath, 100);

            // Small Image
            $img->resize(220, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($destinationPath . "{$baseName}_{$randomCode}_small.webp", 100);

            // Medium Image
            $img = Image::make($destinationPath . $originalName); // reload original
            $img->resize(420, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($destinationPath . "{$baseName}_{$randomCode}_medium.webp", 100);

            // Delete original
            unlink($destinationPath . $originalName);

            $jsonDataArray[] = [
                'webtitles' => $titles[$i],
                'webimage' => "{$publicPath}/{$webpName}",
                'webtitlescredit' => $titles_credit[$i],
            ];
        }

        $endTime = microtime(true); // ✅ End time
        $totalDuration = $endTime - $startTime; // in seconds (float)
        $uploadTime = round($totalDuration, 2); // optional rounding

        // Meta desc logic
        $englishTitle = $request->input('englishTitle');
        $plainTitle = $request->input('title');
        $remainingword = 155 - Str::length($englishTitle);
        $limitedText = Str::limit($plainTitle, $remainingword, '');
        $metadesc = "{$englishTitle} | {$limitedText}";

        $dataToInsert = [
            'title' => $plainTitle,
            'englishTitle' => $englishTitle,
            'slug' => Str::slug($englishTitle),
            'userID' => Auth::id(),
            'viewer' =>0,
            'Story_data' => $jsonDataArray,
            'notification' => $request->has('notification') ? 1 : 0,
            'status' => 'Active',
            'metatitle' => $englishTitle,
            'metakeyword' => $request->input('metakeyword'),
            'metadesc' => $metadesc,
            'upload_time' => $uploadTime, // ✅ Save time in seconds
        ];

        $webstory = Webstory::firstOrNew(['englishTitle' => $englishTitle]);
        $webstory->fill($dataToInsert);
        $webstory->save();

        $webstory->webOrder = $webstory->id;
        $webstory->save();

        // Clear Redis cache
        for ($k = 0; $k < 2000; $k++) {
            Redis::del('webstory:search_' . config('app.webtype') . ":$k");
        }

        // Log the action
        Log::create([
            'userID' => Auth::id(),
            'message' => 'Web Stories added: ' . $plainTitle,
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/webstory/')
            ->with('success', 'Web Story has been added');
    }

    // Update Web Story
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $webstory = Webstory::find($id); 
        $data =  array();
        $data['webstory'] =  $webstory;
        return view('webstory.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'englishTitle' => 'required',
        ], [
            'title.required' => 'The title field is required.',
            'englishTitle.required' => 'The english title field is required.',
        ]);

        // Redis cache clearing
        Redis::del('topwebstory');
        for ($i = 1; $i <= 5; $i++) {
            Redis::del('webstorylist' . $i);
        }

        // Image paths
        $uploadPath = dirname(__FILE__, 6) . '/backend/public/webstory/';
        $webPath = config('app.front_url') . '/backend/public/webstory';

        $webimgs = $request->file('files');
        $oldwebimgs = $request->input('exitsfiles', []);
        $oldtitles = $request->input('exitstitles', []);
        $oldtitlescredits = $request->input('existtitles_credit', []);
        $newTitles = $request->input('titles', []);
        $newTitlesCredits = $request->input('titles_credit', []);

        $allFiles = $oldwebimgs;
        $allTitles = $oldtitles;
        $allTitlescredits = $oldtitlescredits;

        if (!empty($webimgs)) {
            foreach ($webimgs as $i => $file) {
                $randomCode = Str::random(8) . time();
                $originalName = $file->getClientOriginalName();
                $filenameClean = str_replace(' ', '-', $originalName);
                $ext = $file->getClientOriginalExtension();
                $baseName = pathinfo($filenameClean, PATHINFO_FILENAME);

                // Move uploaded original
                $file->move($uploadPath, $filenameClean);

                $imagePath = $uploadPath . $filenameClean;
                $img = Image::make($imagePath);

                $webpName = "{$baseName}_{$randomCode}.webp";
                $webpFullPath = $uploadPath . $webpName;
                $img->save($webpFullPath, 100);

                // Small
                $img->resize(220, null, fn($constraint) => $constraint->aspectRatio());
                $img->save($uploadPath . "{$baseName}_{$randomCode}_small.webp", 100);

                // Medium
                $img = Image::make($imagePath); // Reset original
                $img->resize(420, null, fn($constraint) => $constraint->aspectRatio());
                $img->save($uploadPath . "{$baseName}_{$randomCode}_medium.webp", 100);

                // Delete original
                unlink($imagePath);

                // Store data
                $allFiles[] = "{$webPath}/{$webpName}";
                $allTitles[] = $newTitles[$i] ?? '';
                $allTitlescredits[] = $newTitlesCredits[$i] ?? '';
            }
        }

        $jsonDataArray = array_map(function ($title, $file,$titles_credit) {
            return ['webtitles' => $title, 'webimage' => $file, 'webtitlescredit' => $titles_credit];
        }, $allTitles, $allFiles,$allTitlescredits);

        // Meta Description
        $wordCount = Str::length($request->englishTitle);
        $limitedText = Str::limit($request->title, 155 - $wordCount, '');
        $metadesc = $request->englishTitle . ' | ' . $limitedText;

        $webstory = Webstory::find($request->catid);
        if (!$webstory) {
            return redirect()->back()->with('error', 'Web Story not found.');
        }

        $webstory->fill([
            'title' => $request->title,
            'notification' => $request->has('notification') ? 1 : 0,
            'englishTitle' => $request->englishTitle,
            'slug' => Str::slug($request->englishTitle),
            'Story_data' => $jsonDataArray,
            'status' => 'Active',
            'metatitle' => $request->englishTitle,
            'metakeyword' => $request->metakeyword,
            'metadesc' => $metadesc,
        ])->save();

        // Cloudflare cache clear
        $url = config('app.front_url') . '/web-stories/' . $webstory->slug;
        $this->cloudflareService->clearCache([
            $url,
            config('app.front_url'),
            config('app.front_url') . '/web-stories'
        ]);

        // Firebase notification
        if ($request->has('notification')) {
            $firstImage = $jsonDataArray[0]['webimage'] ?? '';
            $firstTitle = $jsonDataArray[0]['webtitles'] ?? '';
            $description = Str::limit($request->description ?? $firstTitle, 120, '...');

            foreach (DB::table('notification')->pluck('token') as $token) {
                $this->firebaseService->sendNotification(
                    $request->title,
                    $description,
                    $token,
                    'Webstory',
                    $firstImage,
                    $url,
                    $webstory->slug
                );
            }
        }

        // Save log
        Log::create([
            'userID' => Auth::id(),
            'message' => 'Web Story updated: ' . $request->title,
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/webstory/')->with('success', 'Web Story has been updated successfully.');
    }
    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $webstory = Webstory::find($id); 
        $webtitle = $webstory->title;
        $webstory->status = $status;
        $webstory->update();
      
		Redis::del('webstory_' . $webstory->slug);
        Redis::del('webstory_more_' . $webstory->slug);
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'webstory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Web Story published/Unpublished: ' . $webtitle,
            'action' => $status,
            'contents' => json_encode($webtitle),
        ]);
        return redirect('/webstory/')->with('success', 'Web Story Status has been updated successfully');
    }
    // Web Story Delete 
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $webstory = Webstory::find($id); 
        $webtitle = $webstory->title;
        $storydata = $webstory->Story_data;
        foreach ($storydata as $webdata) {
            $webimage = explode('/',$webdata['webimage']);
            $lastElement = end($webimage);
          	$imagePath  = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/webstory/'.$lastElement;
            //$imagePath = public_path('/webstory/'.$lastElement);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $webstory->delete();
        Redis::del('webstory_' . $webstory->slug);
        Redis::del('webstory_more_' . $webstory->slug);
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'webstory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Web Story deleted: ' . $webtitle,
            'action' => 'Delete',
            'contents' => json_encode($webtitle),
        ]);

        return redirect('/webstory/')->with('success', 'Web Story has been deleted successfully');
    }
    public function webstoryorder(Request $request)
    {
        $newOrder = $request->input('newOrder');
        $oldOrder = $request->input('oldOrder');
        $tabletop = $request->input('tabletop');
        $pageNum = $request->input('pageNum');
        
        foreach ($newOrder as $position => $itemId) {
            $news = Webstory::find($itemId);
            $news->webOrder = $newOrder[0] - $position;
            $news->update();          
        }
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'webstory:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        // Find the index of the dragged item in the new order
    
        return response()->json(['message' => 'Order updated successfully']);
    }

    public function WebstoryfetchData4(Request $request)
    {
        $columns = ['id', 'title',  'viewer', 'userID', 'created_at', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);        
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        
        $orderDirection = $request->input('order.0.dir', 'asc');
         
       
        $status = $request->input('status', 'Active'); // Default to Active
        
        // Base query
        $query = DB::table('webstory as c1')->select('c1.*', 'u1.name as authorName')->leftJoin('users as u1', 'c1.userID', '=', 'u1.id');
    
        

        $totalRecords = DB::table('webstory')->count();
        $filteredRecords = $query->count();
         
        $data = $query
        ->when($orderColumn === 'id', function ($query) {
            $query->orderBy('c1.webOrder', 'DESC'); // Default sorting for 'id'
        })
        ->when($orderColumn !== 'id', function ($query) use ($orderColumn, $orderDirection) {
            $query->orderBy($orderColumn, $orderDirection); // Sorting for other columns
        })
        ->offset($start)
        ->limit($length)
        ->get()
        ->toArray();
        
        
   
        $rowNumber = $start + 1;
        foreach ($data as &$row) {
            $row->rowNumber = $rowNumber++; // Add row number
           
           
            $row->action = '
            <form action="' . url('/webstory/destroy', $row->id) . '" method="POST" style="display:inline;">
                ' . csrf_field() . method_field('DELETE') . '
                ' . ($row->status == 'Active' ? 
                    '<a class="btn iconbtn showhideCls btn-success" title="Publish" href="' . url('/webstory/updateStatus/Inactive', $row->id) . '">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </a>' :
                    ($row->status == 'Inactive' ? 
                    '<a class="btn iconbtn unpublishcls showhideCls btn-success" title="Unpublish" href="' . url('/webstory/updateStatus/Active', $row->id) . '">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </a>' :
                    ($row->status == 'Schedule' ? 
                    '<a class="btn iconbtn schedulecls showhideCls btn-success" title="Schedule" href="' . url('/webstory/edit', $row->id) . '">
                        <i class="bi bi-calendar-date" aria-hidden="true"></i>
                    </a>' : ''))) . '
                <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/webstory/edit', $row->id) . '">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>';
        }

        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ]);
    }
    
    public function WebstoryfetchData(Request $request)
    {
        // Column index → actual DB column
        // 0=No.(id/order), 1=title, 2=viewer(count), 3=author, 4=created_at, 5=action
        $columnMap = [
            0 => 'c1.webOrder',
            1 => 'c1.title',
            2 => 'wsc.view_count',   // count column → sort by DB view_count
            3 => 'u1.name',
            4 => 'c1.created_at',
            5 => 'c1.created_at',
        ];

        $draw             = $request->input('draw', 1);
        $start            = (int) $request->input('start', 0);
        $length           = (int) $request->input('length', 20);
        $searchValue      = $request->input('search.value', '');
        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDirection   = strtoupper($request->input('order.0.dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $orderColumn      = $columnMap[$orderColumnIndex] ?? 'c1.created_at';
        $sortByCount      = ($orderColumn === 'wsc.view_count');

        // Date filter params (passed from the filter form via DataTable ajax.data)
        $startDate = $request->input('start_date', '');
        $endDate   = $request->input('end_date', '');

        // Base query
        $query = DB::table('webstory as c1')
            ->select(
                'c1.id', 'c1.title', 'c1.slug', 'c1.status',
                'c1.webOrder', 'c1.created_at', 'c1.userID',
                'u1.name as authorName',
                DB::raw('IFNULL(wsc.view_count, 0) as total_view_count')
            )
            ->leftJoin('users as u1', 'c1.userID', '=', 'u1.id')
            ->leftJoin('webstorycount as wsc', 'c1.id', '=', 'wsc.content_id');

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.title', 'like', '%' . $searchValue . '%')
                  ->orWhere('u1.name', 'like', '%' . $searchValue . '%');
            });
        }

        // ── Date range filter ─────────────────────────────────────────────────
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('c1.created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59',
            ]);
        } elseif (!empty($startDate)) {
            $query->where('c1.created_at', '>=', $startDate . ' 00:00:00');
        } elseif (!empty($endDate)) {
            $query->where('c1.created_at', '<=', $endDate . ' 23:59:59');
        }

        $totalRecords    = DB::table('webstory')->count();
        $filteredRecords = $query->count();

        // ── Fetch GA data (same cache keys as NewsController) ─────────────────
        $pageViews       = [];
        $titleToAppCount = [];
        try {
            $pageViews365    = \Cache::remember('ga_page_views_365days',     43200, fn() => $this->analyticsService->getPageTotalViews(365));
            $pathTitleMap365 = \Cache::remember('ga_path_title_map_365days', 43200, fn() => $this->analyticsService->getPathTitleMap(365));
            $pageViews30     = \Cache::remember('ga_page_views_30days',      3600,  fn() => $this->analyticsService->getPageTotalViews(30));
            $pathTitleMap30  = \Cache::remember('ga_path_title_map_30days',  3600,  fn() => $this->analyticsService->getPathTitleMap(30));

            $pageViews = $pageViews365;
            foreach ($pageViews30 as $path => $cnt) {
                $pageViews[$path] = isset($pageViews[$path]) ? max($pageViews[$path], $cnt) : $cnt;
            }

            $merged = array_merge($pathTitleMap365, $pathTitleMap30);
            foreach ($merged as $path => $title) {
                if (preg_match('#^/(GSTV|gstv)/#', $path)) {
                    $c = max($pageViews365[$path] ?? 0, $pageViews30[$path] ?? 0);
                    $titleToAppCount[$title] = ($titleToAppCount[$title] ?? 0) + $c;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Webstory GA fetch error: ' . $e->getMessage());
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        if ($sortByCount) {
            // Two-phase: lightweight fetch → PHP sort by GA+DB → fetch full page rows
            $lightRows = (clone $query)
                ->select('c1.id', 'c1.slug', 'c1.title',
                         DB::raw('IFNULL(wsc.view_count, 0) as total_view_count'))
                ->get();

            $scored = $lightRows->map(function ($r) use ($pageViews, $titleToAppCount) {
                $pp       = "/web-stories/{$r->slug}";
                $r->_total = (int)($pageViews[$pp] ?? 0)
                           + (int)($titleToAppCount[$r->title] ?? 0)
                           + (int)$r->total_view_count;
                return $r;
            });

            $sortedIds = $scored->sortBy(
                fn($r) => $r->_total,
                SORT_NUMERIC,
                $orderDirection === 'DESC'
            )->values()->slice($start, $length)->pluck('id')->toArray();

            if (empty($sortedIds)) {
                $data = collect();
            } else {
                $idList = implode(',', array_map('intval', $sortedIds));
                $data   = $query->whereIn('c1.id', $sortedIds)
                                ->orderByRaw("FIELD(c1.id, {$idList})")
                                ->get();
            }
        } else {
            if ($orderColumn === 'c1.webOrder') {
                $query->orderBy('c1.webOrder', 'DESC');
            } else {
                $query->orderBy($orderColumn, $orderDirection);
            }
            $data = $query->offset($start)->limit($length)->get();
        }

        // ── Format rows ───────────────────────────────────────────────────────
        $baseUrl   = config('app.front_url');
        $rowNumber = $start + 1;

        foreach ($data as &$row) {
            $row->rowNumber = $rowNumber++;

            // GA+DB total for display
            $pp          = "/web-stories/{$row->slug}";
            $gaWeb       = $pageViews[$pp] ?? 0;
            $gaApp       = $titleToAppCount[$row->title] ?? 0;
            $row->viewer = number_format((int)$gaWeb + (int)$gaApp + (int)($row->total_view_count ?? 0));

            $newsUrl    = $baseUrl . $pp;
            $row->title = '<a href="' . $newsUrl . '" target="_blank">' . htmlspecialchars($row->title) . '</a>';
            $row->action = '
                <form action="' . url('/webstory/destroy', $row->id) . '" method="POST" style="display:inline;">
                    ' . csrf_field() . method_field('DELETE') . '
                    ' . ($row->status == 'Active' ?
                    '<a class="btn iconbtn showhideCls btn-success" title="Publish" href="' . url('/webstory/updateStatus/Inactive', $row->id) . '">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </a>' :
                    ($row->status == 'Inactive' ?
                    '<a class="btn iconbtn unpublishcls showhideCls btn-success" title="Unpublish" href="' . url('/webstory/updateStatus/Active', $row->id) . '">
                        <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    </a>' :
                    ($row->status == 'Schedule' ?
                    '<a class="btn iconbtn schedulecls showhideCls btn-success" title="Schedule" href="' . url('/webstory/edit', $row->id) . '">
                        <i class="bi bi-calendar-date" aria-hidden="true"></i>
                    </a>' : ''))) . '
                    <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/webstory/edit', $row->id) . '">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>';
        }

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => ($sortByCount ? collect($data)->values() : $data),
        ]);
    }
    
}

