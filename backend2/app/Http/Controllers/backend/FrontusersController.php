<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\UserDuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class FrontusersController extends Controller
{
    // Get all pages
    public function index1()
    {
        //$users = DB::table('userregister')->orderBy('id','DESC')->get();
        $users = DB::table('userregister')
        ->leftJoin('user_plan', 'userregister.id', '=', 'user_plan.user_id')
        ->select('userregister.*', 'user_plan.plan_status')
        ->get();
        

        $data =  array();
        $data['user'] =  $users;
        return view('userregister.list', compact('data'));
    }
    public function index()
    {
        return view('userregister.alllist');   
        // Fetch user data with plan status
        $users = DB::table('userregister')
            ->leftJoin('user_plan', 'userregister.id', '=', 'user_plan.user_id')
            ->select('userregister.*', 'user_plan.plan_status')
            ->orderBy('created_at', 'DESC')
            ->get();

        // Fetch total points and total spent coins for each user
        foreach ($users as $user) {
            $user_id = $user->id;

            // Fetch total earned points for the user
            $userpoints = DB::table('user_durations as ud')
                ->join('settings as s', DB::raw('1 = 1'), '>', DB::raw('0'))
                ->select(
                    DB::raw('SUM(CAST(FLOOR(ud.total_duration * s.point) / s.spendtime AS UNSIGNED)) AS total_points')
                )
                ->where('ud.user_id', $user_id)
                ->first();

            // Add registration points to total points, if applicable
            $userreg = DB::table('userregister')->find($user_id);
            $registration_points = $userreg && $userreg->user_points ? $userreg->user_points : 0;
            $total_points = ($userpoints->total_points ?? 0) + $registration_points;

            // Fetch total spent coins for the user
            $spentpoints = DB::table('giftredeemcoins')
                ->where('user_id', $user_id)
                ->sum('spentcoins');

            // Attach total points and spent points to the user object
            $user->total_points = $total_points;
            $user->spent_points = $spentpoints;
            $user->remain_points = $total_points - $spentpoints;
        }

        // Prepare data for the view
        $data = [];
        $data['user'] = $users;

        return view('userregister.list', compact('data'));
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $user = Userregister::find($id);
        $user->status = $status;
        $user->update();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Users published/Unpublished: ' . $user->title,
            'action' => $status,
            'contents' => json_encode($user->title),
        ]);
        return redirect('/frontend-users/')->with('success', 'Iframe Status has been updated successfully');
    }
    public function view($id)
    {
        $user = DB::table('userregister')
        ->leftJoin('categories', 'categories.id', '=', 'userregister.city')
        ->select(
            'userregister.*',
            'categories.title as city_name'
        )
        ->where('userregister.id', $id)
        ->first();

        if (!$user) {
            return redirect()->route('userregister.index')->with('error', 'User not found');
        }
        $userduration = DB::table('user_durations')
            ->where('user_id', $id)
            ->orderBy('date', 'DESC')
            ->get();
        $userdurations = DB::table('user_durations')->where('user_id', $id)->orderBy('date', 'DESC')->get();
        // $data = [
        //     'user' => $user,
        //     'userduration' => $userduration
        // ];

       // Daily Data for the last 7 days in Minutes
       
        // Daily Data for the last 7 days in Minutes
        $dailyData = $userdurations->filter(function($date) {
            return Carbon::parse($date->date)->gte(Carbon::now()->subDays(7));
        })->groupBy(function($date) {
            return Carbon::parse($date->date)->format('Y-m-d');
        })->map(function($group) {
            return $group->sum('total_duration') / 60; // Convert seconds to minutes
        })->toArray();

        // Calculate total minutes for daily data
        $totalDailyMinutes = round(array_sum($dailyData));

        // Weekly Data for the current month in Minutes
        $currentMonth = Carbon::now()->format('Y-m');
        $weeklyData = $userdurations->filter(function($date) use ($currentMonth) {
            return Carbon::parse($date->date)->format('Y-m') == $currentMonth;
        })->groupBy(function($date) {
            return 'Week ' . Carbon::parse($date->date)->weekOfMonth;
        })->map(function($week) {
            return $week->sum('total_duration') / 60; // Convert seconds to minutes
        })->toArray();

        // Calculate total minutes for weekly data
        $totalWeeklyMinutes = round(array_sum($weeklyData));

        // Monthly Data with Month Names and Years in Hours
        $monthlyData = $userdurations->groupBy(function($date) {
            return Carbon::parse($date->date)->format('F Y'); // 'F Y' gives month and year
        })->map(function($month) {
            return $month->sum('total_duration') / 3600; // Convert seconds to hours
        })->toArray();

        // Calculate total hours for monthly data
        $totalMonthlyHours = round(array_sum($monthlyData));

    
        $data = [
                'user' => $user,
                'userduration' => $userdurations,
                'dailyData' => $dailyData,
                'weeklyData' => $weeklyData,
                'monthlyData' => $monthlyData,
                'totalDailyMinutes' => $totalDailyMinutes,
                'totalWeeklyMinutes' => $totalWeeklyMinutes,
                'totalMonthlyHours' => $totalMonthlyHours,
        ];

        return view('userregister.view', compact('data'));
    }


    public function frontendusersfetchData11(Request $request)
    {
        $columns = ['id', 'firstname', 'email', 'mobile', 'total_points', 'spentpoints', 'remain_points', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        $query = DB::table('userregister as c1')
            ->leftJoinSub(
                DB::table('user_plan as up')
                    ->select('user_id', 'plan_status')
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('MAX(id)')
                            ->from('user_plan')
                            ->groupBy('user_id');
                    })
                    ->orderBy('id', 'desc'),
                'up',
                'up.user_id',
                '=',
                'c1.id'
            )
            ->leftJoinSub(
                DB::table('user_durations as ud')
                    ->crossJoin('settings as s')
                    ->select(
                        'ud.user_id',
                        DB::raw('SUM(CAST(FLOOR(ud.total_duration * s.point) / s.spendtime AS UNSIGNED)) AS total_points')
                    )
                    ->groupBy('ud.user_id'),
                'user_points',
                'user_points.user_id',
                '=',
                'c1.id'
            )
            ->leftJoinSub(
                DB::table('giftredeemcoins')
                    ->select('user_id', DB::raw('SUM(spentcoins) AS spentpoints'))
                    ->groupBy('user_id'),
                'spent_points',
                'spent_points.user_id',
                '=',
                'c1.id'
            )
            ->select(
                'c1.id',
                'c1.firstname',
                'c1.email',
                'c1.mobile',
                DB::raw('COALESCE(user_points.total_points, 0) + IFNULL(c1.user_points, 0) AS total_points'),
                DB::raw('COALESCE(spent_points.spentpoints, 0) AS spentpoints'),
                DB::raw('(COALESCE(user_points.total_points, 0) + IFNULL(c1.user_points, 0)) - COALESCE(spent_points.spentpoints, 0) AS remain_points'),
                'c1.status',
                'up.plan_status' // Get the plan status from the latest user_plan record
            ); 
        $totalRecords = DB::table('userregister')->count();
        $filteredRecords = $query->count();
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('c1.firstname', 'like', "%$searchValue%")
                        ->orWhere('c1.email', 'like', "%$searchValue%")
                        ->orWhere('c1.mobile', 'like', "%$searchValue%");
            });
        }
        $data = $query->orderBy($orderColumn, $orderDirection)
                    ->offset($start)
                    ->limit($length)
                    ->get()
                    ->toArray();

        $rowNumber = $start + 1;
        foreach ($data as &$row) {
            $row->rowNumber = $rowNumber++;
            $row->action = '<a class="btn iconbtn editCls btn-primary" title="View" href="' . url('/frontend-users/view/', $row->id) . '">
                                <i class="bi bi-view-list"></i>
                            </a>';
        }

        return response()->json([
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ]);
    }
 
    public function frontendusersfetchData(Request $request)
    {
        $columns = ['id', 'firstname', 'email', 'mobile', 'total_points', 'spentpoints', 'remain_points', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'asc');

        // Define a unique Redis key based on the request parameters
        $redisKey = "frontend-users-fetch-data:draw-{$draw}:start-{$start}:length-{$length}:order-{$orderColumn}-{$orderDirection}:search-{$searchValue}";

        // Check if data exists in Redis
        $cachedData = Redis::get($redisKey);

        if ($cachedData) {
            return response()->json(json_decode($cachedData, true));
        }

        // If not cached, run the query
        $query = DB::table('userregister as c1')
            ->leftJoinSub(
                DB::table('user_plan as up')
                    ->select('user_id', 'plan_status')
                    ->whereIn('id', function ($query) {
                        $query->selectRaw('MAX(id)')
                            ->from('user_plan')
                            ->groupBy('user_id');
                    })
                    ->orderBy('id', 'desc'),
                'up',
                'up.user_id',
                '=',
                'c1.id'
            )
            ->leftJoinSub(
                DB::table('user_durations as ud')
                    ->crossJoin('settings as s')
                    ->select(
                        'ud.user_id',
                        DB::raw('SUM(CAST(FLOOR(ud.total_duration * s.point) / s.spendtime AS UNSIGNED)) AS total_points')
                    )
                    ->groupBy('ud.user_id'),
                'user_points',
                'user_points.user_id',
                '=',
                'c1.id'
            )
            ->leftJoinSub(
                DB::table('giftredeemcoins')
                    ->select('user_id', DB::raw('SUM(spentcoins) AS spentpoints'))
                    ->groupBy('user_id'),
                'spent_points',
                'spent_points.user_id',
                '=',
                'c1.id'
            )
            ->select(
                'c1.id',
                'c1.firstname',
                'c1.email',
                'c1.mobile',
                DB::raw('COALESCE(user_points.total_points, 0) + IFNULL(c1.user_points, 0) AS total_points'),
                DB::raw('COALESCE(spent_points.spentpoints, 0) AS spentpoints'),
                DB::raw('(COALESCE(user_points.total_points, 0) + IFNULL(c1.user_points, 0)) - COALESCE(spent_points.spentpoints, 0) AS remain_points'),
                'c1.status',
                'up.plan_status'
            );

        // Total records
        $totalRecords = DB::table('userregister')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('c1.firstname', 'like', "%$searchValue%")
                        ->orWhere('c1.email', 'like', "%$searchValue%")
                        ->orWhere('c1.mobile', 'like', "%$searchValue%");
            });
        }

        // Get the data
        $data = $query->orderBy($orderColumn, $orderDirection)
                    ->offset($start)
                    ->limit($length)
                    ->get()
                    ->toArray();

        // Format the data
        $rowNumber = $start + 1;
        foreach ($data as &$row) {
            $row->rowNumber = $rowNumber++;
            $row->action = '<a class="btn iconbtn editCls btn-primary" title="View" href="' . url('/frontend-users/view', $row->id) . '">
                                <i class="bi bi-view-list"></i>
                            </a>';
        }

        // Prepare the response data
        $responseData = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ];

        // Store the data in Redis with a 60-minute expiration time
        Redis::setex($redisKey, 3600, json_encode($responseData));

        return response()->json($responseData);
    }

}
