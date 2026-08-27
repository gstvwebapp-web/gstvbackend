<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Usernews;
use App\Models\Frontendusers;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class UsernewsController extends Controller
{
    // Get all pages
    public function index(Request $request)
    { 
        return view('usernews.alllist');
          $userActivenews = DB::table('usernews as c1')
            ->select('c1.*', 
                    DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
                    DB::raw("COUNT(n.id) as news_count"))
            ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
            ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
            ->where('c1.status','Active')
            ->groupBy('c1.id', 'u1.firstname', 'u1.lastname')
            ->get();
        $userRejectednews = DB::table('usernews as c1')
              ->select('c1.*', 
                      DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
                      DB::raw("COUNT(n.id) as news_count"))
              ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
              ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
          	  ->where('c1.status','Inactive')
              ->groupBy('c1.id', 'u1.firstname', 'u1.lastname')
          		
              ->get();
        $usernews = DB::table('usernews as c1')
              ->select('c1.*', 
                    DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
                    DB::raw("COUNT(n.id) as news_count"))
            ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
            ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
            ->where('c1.status','Under Review')
            ->groupBy('c1.id', 'u1.firstname', 'u1.lastname')
            ->get();
         
        $data =  array();
        $data['usernews'] =  $usernews;
        $data['userRejectednews'] =  $userRejectednews;
      	$data['userActivenews'] =  $userActivenews;
        return view('usernews.list', compact('data'));
    }

  
    // Status Active/Inactive
    public function updateStatus(Request $request)
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $Usernews = Usernews::find($id); 
        $Usernewstitle = $Usernews->title;
        $Usernews->status = $status;
        $Usernews->update();
        Redis::del('usernews');
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Usernews published/Unpublished: ' . $Usernewstitle,
            'action' => $status,
            'contents' => json_encode($Usernewstitle),
        ]);
        return redirect('/usernews/')->with('success', 'Usernews Status has been updated successfully');
    }
    public function rejectnews(Request $request)
    {
        $id = $request->newsId;
        $Usernews = Usernews::find($id); 
        if (!$Usernews) {
            return response()->json([
                'success' => false,
                'message' => 'News not found.'
            ]);
        }

        $Usernewstitle = $Usernews->title;
        $Usernews->rejectnews = $request->get('rejectnews');
        $Usernews->status = "Inactive";
        $Usernews->update();

        Redis::del('usernews');
        Log::create([
            'userID' => Auth::user()->id,
            'message' => 'Usernews Rejected: ' . $Usernewstitle,
            'action' => 'Inactive',
            'contents' => json_encode($Usernewstitle),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usernews status has been updated successfully.'
        ]);
    }

    // Dalete the page
    public function destroy($id,Request $request)
    {
        $id = Route::current()->parameter('id');
        $Usernews = Usernews::find($id); 
        $Usernewstitle = $Usernews->title;
        $Usernews->delete();
        Redis::del('usernews');
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Usernews deleted: ' . $Usernewstitle,
            'action' => 'Delete',
            'contents' => json_encode($Usernewstitle),
        ]);
        return redirect('/usernews/')->with('success', 'Usernews has been deleted successfully');
    }
    public function userActivenewsfetchdata(Request $request)
    {
        $columns = ['id', 'featureImage', 'title', 'authorName', 'status'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a Redis key
        $redisKey = "userActivenews";

        // Check if the data exists in Redis
        if (!Redis::exists($redisKey)) {
            // If not, fetch data from the database
            $query = DB::table('usernews as c1')
                ->select(
                    'c1.*',
                    DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
                    DB::raw("COUNT(n.id) as news_count")
                )
                ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
                ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
                ->where('c1.status', 'Active')
                ->groupBy('c1.id', 'u1.firstname', 'u1.lastname')
                ->get();

            // Save the data to Redis
            Redis::set($redisKey, json_encode($query));
        }

        // Retrieve the data from Redis
        $data = json_decode(Redis::get($redisKey));

        // Apply filtering and sorting
        if (!empty($searchValue)) {
            $data = collect($data)->filter(function ($item) use ($searchValue) {
                return stripos($item->title, $searchValue) !== false ||
                    stripos($item->authorName, $searchValue) !== false;
            });
        }

        // Total records after filtering
        $totalRecords = count($data);

        // Sort the data
        $data = collect($data)->sortBy($orderColumn, SORT_REGULAR, $orderDirection === 'DESC')->values();

        // Paginate the data
        $paginatedData = $data->slice($start, $length);

        $counter = $start + 1;

        // Format data
        $formattedData = $paginatedData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $images = json_decode($row->featureImage, true);
            $imgpath = '';
            $extension = '';
            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv'];

            if (is_array($images) && isset($images[0])) {
                $videopath = $images[0];
                $imgpath = config('app.backendnewsurl') . '/public/media/' . $images[0];
                $extension = strtolower(pathinfo($videopath, PATHINFO_EXTENSION));
            }

            if (in_array($extension, $videoExtensions)) {
                $row->featureImage = '<video class="img-thumbnail" style="width: 100%; height: 80px;" controls>
                        <source src="' . $imgpath . '" type="video/' . $extension . '">
                        Your browser does not support the video tag.
                    </video>';
            } else {
                $row->featureImage = '<img src="' . $imgpath . '" alt="Image Preview" style="width:30px;">';
            }

            return $row;
        });

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }

    public function userRejectednewsfetchdata(Request $request)
    {
        $columns = ['id', 'featureImage', 'title', 'authorName', 'rejectnews', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Increment fetch count for tracking purposes in Redis
        Redis::incr('rejected_news_fetch_count');

        // Base query without pagination for total count
        $baseQuery = DB::table('usernews as c1')
            ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
            ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
            ->where('c1.status', 'Inactive');

        // Apply search filter for total count query
        if (!empty($searchValue)) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('c1.title', 'like', '%' . $searchValue . '%')
                ->orWhere(DB::raw("CONCAT(u1.firstname, ' ', u1.lastname)"), 'like', '%' . $searchValue . '%');
            });
        }

        $totalRecords = $baseQuery->count(); // Get the total number of records

        // Query for paginated data with GROUP BY
        $filteredQuery = $baseQuery->select(
            'c1.*',
            DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
            DB::raw("COUNT(n.id) as news_count")
        )
        ->groupBy('c1.id', 'u1.firstname', 'u1.lastname');

        // Apply sorting and pagination
        $filteredData = $filteredQuery->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $images = json_decode($row->featureImage, true);
            $imgpath = '';
            $extension = '';
            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv'];

            if (is_array($images) && isset($images[0])) {
                $videopath = $images[0];
                $imgpath = config('app.backendnewsurl') . '/public/media/' . $images[0];
                $extension = strtolower(pathinfo($videopath, PATHINFO_EXTENSION));
            }
            if (in_array($extension, $videoExtensions)) {
                $row->featureImage = '<video class="img-thumbnail" style="width: 100%; height: 80px;" controls>
                    <source src="' . $imgpath . '" type="video/' . $extension . '">
                    Your browser does not support the video tag.
                </video>';
            } else {
                $row->featureImage = '<img src="' . $imgpath . '" alt="Image Preview" style="width:30px;">';
            }
            if ($row->news_count <= 0) {
                $row->action = '<a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/usernews/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                    <i class="bi bi-trash"></i>
                </a>
                <form id="delete-form-' . $row->id . '" action="' . url('/usernews/destroy', $row->id) . '" method="POST" style="display: none;">
                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="DELETE">
                </form>';
            }

            return $row;
        });

        // Optionally, store the last retrieved draw number in Redis for tracking purposes
        Redis::set('rejected_news_last_draw', $draw);

        // Return DataTable response
        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ]);
    }

    public function usernewsfetchdata(Request $request)
    {
        $columns = ['id', 'featureImage', 'title', 'authorName', 'status', 'action'];
    
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
    
        // Generate a unique cache key
        $cacheKey = "usernews_data:draw={$draw}:start={$start}:length={$length}:search={$searchValue}:orderCol={$orderColumn}:orderDir={$orderDirection}";
    
        // Check if data exists in Redis
        $cachedData = Redis::get($cacheKey);
    
        if ($cachedData) {
            // If cached data exists, return it
            return response()->json(json_decode($cachedData, true));
        }
    
        // Base query
        $query = DB::table('usernews as c1')
            ->select('c1.*', 
                DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"),
                DB::raw("COUNT(n.id) as news_count"))
            ->leftJoin('userregister as u1', 'c1.userID', '=', 'u1.id')
            ->leftJoin('news as n', 'c1.id', '=', 'n.usernews_userID')
            ->where('c1.status', 'Under Review')
            ->groupBy('c1.id', 'u1.firstname', 'u1.lastname');
    
        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c1.title', 'like', '%' . $searchValue . '%')
                  ->orWhere(DB::raw("CONCAT(u1.firstname, ' ', u1.lastname)"), 'like', '%' . $searchValue . '%');
            });
        }
    
        // Clone the query to calculate total records
        $totalRecordsQuery = clone $query;
        $totalRecords = $totalRecordsQuery->get()->count();
    
        // Apply ordering and pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();
    
        $counter = $start + 1;
    
        // Map the data to add 'result' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $images = json_decode($row->featureImage, true);
            $imgpath = '';
            $extension = '';
            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv'];
    
            if (is_array($images) && isset($images[0])) {
                $videopath = $images[0];
                $imgpath = config('app.backendnewsurl') . '/public/media/' . $images[0];
                $extension = strtolower(pathinfo($videopath, PATHINFO_EXTENSION));
            }
    
            if (in_array($extension, $videoExtensions)) {
                $row->featureImage = '<video class="img-thumbnail" style="width: 100%; height: 80px;" controls>
                        <source src="' . $imgpath . '" type="video/' . $extension . '">
                        Your browser does not support the video tag.
                    </video>';
            } else {
                $row->featureImage = '<img src="' . $imgpath . '" alt="Image Preview" style="width:30px;">';
            }
    
            if ($row->news_count <= 0) {
                $row->action = '
                    <a class="btn iconbtn editCls btn-primary" onclick="openmodalreject(' . $row->id . ')" title="Rejected"><i class="bi bi-house"></i></a>
                    <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/news/editusernews', $row->id) . '">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/usernews/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                        <i class="bi bi-trash"></i>
                    </a>
                    <form id="delete-form-' . $row->id . '" action="' . url('/usernews/destroy', $row->id) . '" method="POST" style="display: none;">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                    </form>';
            }
    
            return $row;
        });
    
        // Prepare the response data
        $responseData = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ];
    
        // Cache the data in Redis
        Redis::set($cacheKey, json_encode($responseData), 'EX', 600); // Cache for 10 minutes
    
        return response()->json($responseData);
    }
    


    
}
