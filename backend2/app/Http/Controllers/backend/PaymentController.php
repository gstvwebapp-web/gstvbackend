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

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment.alllist');   
    }

    public function view($id)
    {
        $user = Userregister::find($id);
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
 
    public function paymentfetchData(Request $request)
   {
        $columns = ['id','authorName','mobile','plan','plan_amount', 'plan_order_id','plan_payment_id','plan_expire'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');
 
        // Create a unique cache key based on the search query parameters
        $cacheKey = 'payment:search_' . config('app.webtype') . ':' . $draw;

        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Data found in cache, return it
            $data = json_decode($cachedData, true);
        } else {
            // Query the database if data is not cached
            $query = DB::table('user_plan as c1')
                ->select('c1.*','u1.mobile',DB::raw("CONCAT(u1.firstname, ' ', u1.lastname) as authorName"))
                ->leftJoin('userregister as u1', 'c1.user_id', '=', 'u1.id');
            
            // Apply search filter if any search value is provided
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('c1.plan', 'like', '%' . $searchValue . '%')
                    ->orWhere('u1.firstname', 'like', '%' . $searchValue . '%');
                });
            }

            // if (!empty($status)) {
            //     $query->where('c1.status', $status);
            // }

            // Get the total number of filtered records
            $totalRecords = $query->count();

            // Apply pagination
            $filteredData = $query
            ->orderBy("c1.$orderColumn", $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

            $counter = $start + 1;
            // Map the data to add the 'action' column and other transformations
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                // Assign row number and increment counter
                $row->rowNumber = $counter++;
                $row->plan_expire = Carbon::parse($row->plan_expire)->format('d-m-Y');
                return $row;
            });

            // Prepare the data for response
            $data = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords, // Send the total filtered records count
                "data" => $formattedData,
            ];

            // Cache the data for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode($data));
        }

        // Return the data (either from cache or from database)
        return response()->json($data);
    }

}
