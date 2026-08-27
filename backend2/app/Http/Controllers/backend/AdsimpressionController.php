<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Adsimpression;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Log;
use Carbon\Carbon;
use App\Models\Module;
use Illuminate\Support\Facades\Redis;

class AdsimpressionController extends Controller
{
    public function index()
    {
        return view('adsimpression.alllist');
        $ads = DB::table('adsimpression')
        ->select('image', DB::raw('DATE(created_at) as date'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        ->groupBy('date', 'image') // Adjusted groupBy to include 'image'
        ->orderBy('date', 'DESC')
        ->get();
        $data =  array();
        $data['ads'] =  $ads;
        return view('adsimpression.list', compact('data'));
    }
    public function graph1(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        if($start_date == ""){
            $start_date = date('Y-m-d'); // Replace with the desired date or get from request
        }
        if($end_date == ""){
            $end_date = date('Y-m-d'); // Replace with the desired date or get from request
        }

        $ads = DB::table('adsimpression')
        ->select('image', DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        ->whereDate('created_at', '>=', $start_date)
        ->whereDate('created_at', '<=', $end_date)
        ->groupBy('image')
        ->orderBy('total_views', 'DESC')
        ->get();

        $adsimg = DB::table('adsimpression')
        ->select('image', DB::raw('MAX(created_at) as created_at'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        ->whereDate('created_at', $start_date)
        ->groupBy('image')
        ->orderBy('total_views', 'DESC')
        ->get();

    return view('adsimpression.graph-3', [
        'ads' => $ads,
        'adsimg' =>$adsimg,
        'date' => $start_date
    ]);
 
    }
    public function graph(Request $request)
    { 
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $imgtype = $request->input('imgtype');
        if($start_date == ""){
            $start_date = date('Y-m-d', strtotime('-1 month')); // Replace with the desired date or get from request
        }
        if($end_date == ""){
            $end_date = date('Y-m-d'); // Replace with the desired date or get from request
        } 
        // if($imgtype != ""){
        //     $adsQuery = DB::table('adsimpression')
        //     ->select('image', 'created_at', DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        //     ->whereDate('created_at', '>=', $start_date)
        //     ->whereDate('created_at', '<=', $end_date);
        //     $adsQuery->where('image', $imgtype); // Assuming $imgtype is a variable holding the image type
        //     ->orderBy('created_at', 'asc') 
        //     $ads = $adsQuery->groupBy('image', 'created_at') // Add GROUP BY clause for 'image' 
        //     ->get();
        // } else { 
        //     $ads = DB::table('adsimpression')
        //         ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        //         ->whereDate('created_at', '>=', $start_date)
        //         ->whereDate('created_at', '<=', $end_date)
        //         ->groupBy(DB::raw('DATE(created_at)'))
        //         ->get();
        // }
        if ($imgtype != "") {
            $ads = DB::table('adsimpression')
                ->select('image', 'created_at', DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->where('image', $imgtype) // Assuming $imgtype is a variable holding the image type
                ->groupBy('image', 'created_at')
                ->orderBy('created_at', 'asc') // Sort by created_at in ascending order
                ->get();
        } else {
            $ads = DB::table('adsimpression')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc') // Sort by date in ascending order
                ->get();
        }
 
         $adsimg = DB::table('adsimpression')
        ->select('image', DB::raw('MAX(created_at) as created_at'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
        ->whereDate('created_at', $start_date)
        ->groupBy('image')
        ->orderBy('total_views', 'DESC')
        ->get();

        $total_views = $ads->sum('total_views');
        $total_clicks = $ads->sum('total_clicks');
        $CTR = ($total_views > 0) ? round(($total_clicks / $total_views) * 100, 2) : 0;

        // Calculate CTR for each date
        $ads = $ads->map(function ($ad) {
            $ad->CTR = ($ad->total_views > 0) ? ($ad->total_clicks / $ad->total_views) * 100 : 0;
            return $ad;
        }); 

        return view('adsimpression.graph', [
            'ads' => $ads,
            'adsimg' =>$adsimg,
            'date' => $start_date,
            'end_date' => $end_date,
            'imgtype'=>$imgtype,
            'viewcount'=>$total_views,
            'clickscount'=>$total_clicks,
            'CTR' =>$CTR
        ]);
 
    }
     
    public function generatePdf(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $imgtype = $request->input('imgtype');
    
        if ($start_date == "") {
            $start_date = date('Y-m-d');
        }
        if ($end_date == "") {
            $end_date = date('Y-m-d');
        }
        if ($imgtype != "") {
            $adsQuery = DB::table('adsimpression')
                ->select('image', 'created_at', DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date);
            $adsQuery->where('image', $imgtype);
            $ads = $adsQuery->groupBy('image', 'created_at')
                ->get();
        } else {
            $ads = DB::table('adsimpression')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
                ->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get();
        }
    
        $adsimg = DB::table('adsimpression')
            ->select('image', DB::raw('MAX(created_at) as created_at'), DB::raw('SUM(view) as total_views'), DB::raw('SUM(click) as total_clicks'))
            ->whereDate('created_at', $start_date)
            ->groupBy('image')
            ->orderBy('total_views', 'DESC')
            ->get();
    
        $total_views = $ads->sum('total_views');
        $total_clicks = $ads->sum('total_clicks');
        $CTR = ($total_views > 0) ? round(($total_clicks / $total_views) * 100, 2) : 0;
    
        // Calculate CTR for each date
        $ads = $ads->map(function ($ad) {
            $ad->CTR = ($ad->total_views > 0) ? ($ad->total_clicks / $ad->total_views) * 100 : 0;
            return $ad;
        });

        // Generate the HTML for the PDF
        $html = view('adsimpression.pdf', [
            'ads' => $ads,
            'adsimg' => '', // You can pass the relevant data here
            'date' => date("F d, Y",strtotime($start_date)),
            'end_date' => date("F d, Y",strtotime($end_date)),
            'imgtype' => str_replace('_',' ',$imgtype),
            'viewcount' => $total_views,
            'clickscount' => $total_clicks,
            'CTR' => $CTR,
            'viewChartImage' => $request->viewChartImage,
            'clickChartImage' => $request->clickChartImage,
            'ctrChartImage' => $request->ctrChartImage,
        ])->render();

        // Save the HTML to a file for debugging
        file_put_contents(storage_path('app/public/debug.html'), $html);

        // Generate the PDF
        //$pdf = PDF::loadHTML($html);
      	$pdf = PDF::loadHTML($html)->setPaper('a4', 'landscape');

        return $pdf->download('ads_report.pdf');
    }
    
    public function adsimpressionfetchData1(Request $request)
    {
        $columns = ['id', 'image', 'total_views', 'total_clicks', 'date'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'date';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Main query with grouping
        $query = DB::table('adsimpression')
            ->select(
                'image',
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(view) as total_views'),
                DB::raw('SUM(click) as total_clicks'),
                DB::raw('MAX(id) as max_id')
            )
            ->groupBy(DB::raw('DATE(created_at)'), 'image');

        // Apply date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('created_at', 'like', '%' . $searchValue . '%')
                    ->orWhere('image', 'like', '%' . $searchValue . '%');
            });
        }

        // Calculate total records
        $totalQuery = DB::table('adsimpression')
            ->select(
                DB::raw('COUNT(DISTINCT DATE(created_at), image) as total')
            );

        if (!empty($startDate) && !empty($endDate)) {
            $totalQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (!empty($searchValue)) {
            $totalQuery->where(function ($q) use ($searchValue) {
                $q->where('created_at', 'like', '%' . $searchValue . '%')
                    ->orWhere('image', 'like', '%' . $searchValue . '%');
            });
        }

        $totalRecords = $totalQuery->value('total');

        // Apply pagination
        $filteredData = $query->orderBy('max_id', $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Format the data
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            $row->rowNumber = $counter++;
            $row->date = \Carbon\Carbon::createFromTimestamp(strtotime($row->date))->format('d-m-Y');
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
    public function adsimpressionfetchData(Request $request)
    {
        $columns = ['id', 'image', 'total_views', 'total_clicks', 'date'];
    
        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'date';
        $orderDirection = $request->input('order.0.dir', 'DESC');
    
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }
    
        // Create a unique cache key based on the search, date range, pagination, and sorting parameters
        $cacheKey = 'adsimpressionfetchData:' . md5($searchValue . $startDate . $endDate . $start . $length . $orderColumn . $orderDirection);
    
        // Check if the data is cached
        $cachedData = Redis::get($cacheKey);
    
        if ($cachedData) {
            // Data found in cache, return it
            $data = json_decode($cachedData, true);
        } else {
            // Main query with grouping
            $query = DB::table('adsimpression')
                ->select(
                    'image',
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(view) as total_views'),
                    DB::raw('SUM(click) as total_clicks'),
                    DB::raw('MAX(id) as max_id')
                )
                ->groupBy(DB::raw('DATE(created_at)'), 'image');
    
            // Apply date range filter
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
    
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('created_at', 'like', '%' . $searchValue . '%')
                        ->orWhere('image', 'like', '%' . $searchValue . '%');
                });
            }
    
            // Calculate total records
            $totalQuery = DB::table('adsimpression')
                ->select(
                    DB::raw('COUNT(DISTINCT DATE(created_at), image) as total')
                );
    
            if (!empty($startDate) && !empty($endDate)) {
                $totalQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
    
            if (!empty($searchValue)) {
                $totalQuery->where(function ($q) use ($searchValue) {
                    $q->where('created_at', 'like', '%' . $searchValue . '%')
                        ->orWhere('image', 'like', '%' . $searchValue . '%');
                });
            }
    
            $totalRecords = $totalQuery->value('total');
    
            // Apply pagination
            $filteredData = $query->orderBy('max_id', $orderDirection)
                ->skip($start)
                ->take($length)
                ->get();
    
            $counter = $start + 1;
    
            // Format the data
            $formattedData = $filteredData->map(function ($row) use (&$counter) {
                $row->rowNumber = $counter++;
                $row->date = \Carbon\Carbon::createFromTimestamp(strtotime($row->date))->format('d-m-Y');
                return $row;
            });
    
            // Prepare the data for response
            $data = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords,
                "data" => $formattedData,
            ];
    
            // Cache the data for 5 minutes (300 seconds)
            Redis::setex($cacheKey, 300, json_encode($data));
        }
    
        // Return the data (either from cache or from database)
        return response()->json($data);
    }
    
    
    
}
