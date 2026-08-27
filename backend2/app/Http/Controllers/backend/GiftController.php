<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Gift;
use Illuminate\Support\Facades\Auth;
use Route;
use Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class GiftController extends Controller
{
    
    // Get all Categories
    public function index()
    {
        return view('gift.alllist');
        $giftlist = DB::table('gift')
                    ->orderBy('id','DESC')
                    ->get();
        $data =  array();
        $data['gift'] =  $giftlist;
        return view('gift.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
        $data =  array();
        return view('gift.add', compact('data'));
    }
    public function store(Request $request)
    {
        
        $rules = [
            'gifttitle' => 'required',
            'giftimage' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'coins'=>'required'
        ];

        $customMessages = [
            'gifttitle.required' => 'The Gift title field is required.',
            'giftimage.image' => 'The uploaded file must be an image.',
            'giftimage.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'giftimage.max' => 'The image size must not exceed 2 MB.',
            'coins.required' => 'The Meta title field is required.',
        ];
        $request->validate($rules, $customMessages);

        $iconImg = '';
        if ($files = $request->file('giftimage')) {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/$year/$month/$day/";
            $iconImg = 'img_'.time().'.'.$files->extension();  
            $files->move($destinationPath, $iconImg);
            $path = config('app.backendnewsurl').'/public/uploads/giftimage/'.$year.'/'.$month.'/'.$day;
            $fullpath = $path.'/'.$iconImg;
        }
        $gift = new Gift([
            'gifttitle' => $request->get('gifttitle'),
            'coins'=> $request->get('coins'),
            'qty'=> $request->get('qty'),
            'expiredate'=> $request->get('expiredate'),
            'giftimage'=> $fullpath,
        ]);
        
        $gift->save();
        $gift->expiredate = $request->get('expiredate');
        $gift->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Gift added: ' . $request->get('gifttitle'),
            'action' => 'Add',
            'contents' => json_encode($request->all()),
        ]);

        return redirect('/gift/')->with('success', 'Gift has been added');
    }

    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $Gift = Gift::find($id); 
        $data =  array();
        $data['gift'] =  $Gift;
        return view('gift.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $rules = [
            'gifttitle' => 'required',
            'giftimage' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'coins'=>'required'
        ];

        $customMessages = [
            'gifttitle.required' => 'The Gift title field is required.',
            'giftimage.image' => 'The uploaded file must be an image.',
            'giftimage.mimes' => 'Only JPEG, PNG, JPG, SVG and GIF images are allowed.',
            'giftimage.max' => 'The image size must not exceed 2 MB.',
            'coins.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
         
        if ($files = $request->file('giftimage')) {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/$year/$month/$day/";
            if($request->get('oldImg')!='') {
                $oldimgdata = explode('giftimage',$request->get('oldImg'));
                if (isset($oldimgdata[1])) {
                    $oldImgPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/" . $oldimgdata[1];
                    if (file_exists($oldImgPath)) {
                        unlink($oldImgPath);
                    }
                }
                // unlink(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/".$oldimgdata[1]);
            }
            $iconImg = 'img_'.time().'.'.$files->extension();   
            $files->move($destinationPath, $iconImg);
            $path = config('app.backendnewsurl').'/public/uploads/giftimage/'.$year.'/'.$month.'/'.$day;
            $fullpath = $path.'/'.$iconImg;
        } else {
            $fullpath =  $request->get('oldImg');
        }
         
        $gift = Gift::find($request->giftid);
        $gift->gifttitle = $request->get('gifttitle');
        $gift->coins = $request->get('coins');
        $gift->giftimage = $fullpath;
      	$gift->qty = $request->get('qty');
        $gift->expiredate = $request->get('expiredate');
        $gift->update();
 
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Gift updated: ' . $request->get('gifttitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/gift/')->with('success', 'Gift has been updated successfully.');
    }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $gift = Gift::find($id); 
        $gifttitle = $gift->gifttitle;
        $gift->status = $status;
        $gift->update();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Gift published/Unpublished: ' . $gifttitle,
            'action' => $status,
            'contents' => json_encode($gifttitle),
        ]);
        return redirect('/gift/')->with('success', 'Gift Status has been updated successfully');
    }

    

    public function destroy(Request $request)
    {
        if($request->get('oldImg')) {
            // $imagePath = public_path('/uploads/category/icon/'.$request->get('oldImg'));
            $oldimgdata = explode('giftimage',$request->get('oldImg'));
            if (file_exists(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/".$oldimgdata[1])) {
                unlink(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/giftimage/".$oldimgdata[1]);
            }
        }
        $id = Route::current()->parameter('id');
        $gift = Gift::find($id); 
        $gifttitle = $gift->gifttitle;
        $gift->delete();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Gift deleted: ' . $gifttitle,
            'action' => 'Delete',
            'contents' => json_encode($gifttitle),
        ]);
        return redirect('/gift/')->with('success', 'Gift has been deleted successfully');
    }
    public function giftreedem()
    {
        return view('gift.reedemalllist');
        // $giftlist = DB::table('giftredeemcoins')
        //             ->orderBy('id','DESC')
        //             ->get();
        $giftlist = DB::table('giftredeemcoins')
            ->leftJoin('gift', 'giftredeemcoins.giftid', '=', 'gift.id')
            ->leftJoin('userregister', 'giftredeemcoins.user_id', '=', 'userregister.id')
            ->select(
                'giftredeemcoins.*',
                'gift.giftimage AS gift_image',
                'gift.gifttitle',
                DB::raw("CONCAT(userregister.firstname, ' ', userregister.lastname) AS username"),
                'userregister.mobile'
            )
            ->orderBy('giftredeemcoins.id', 'DESC')
            ->get();
        $data =  array();
        $data['gift'] =  $giftlist;
        return view('gift.reedemlist', compact('data'));
    }
    public function giftfetchData(Request $request)
    {
        $columns = ['id', 'giftimage','gifttitle', 'coins','qty','expiredate', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query =DB::table('gift');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('gifttitle', 'like', '%' . $searchValue . '%')
                ->orWhere('coins', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add 'result' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
             if($row->giftimage !=''){
                $row->giftimage='<img src="'.$row->giftimage.'" style="width:30px;"/>';
            }
            $row->expiredate = date('d-m-Y', strtotime($row->expiredate));
            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/gift/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/gift/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/gift/destroy', $row->id) . '" onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { document.getElementById(\'delete-form-' . $row->id . '\').submit(); }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/iframe/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';

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
    public function giftreedemfetchData33(Request $request)
    {
        $columns = ['id','gifttitle','giftimage', 'username','drawcode','spentcoins','address','mobile'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        $query =DB::table('giftredeemcoins')
        ->leftJoin('gift', 'giftredeemcoins.giftid', '=', 'gift.id')
        ->leftJoin('userregister', 'giftredeemcoins.user_id', '=', 'userregister.id')
        ->select(
            'giftredeemcoins.*',
            'gift.giftimage AS giftimage',
            'gift.gifttitle',
            DB::raw("CONCAT(userregister.firstname, ' ', userregister.lastname) AS username"),
            'userregister.mobile'
        );

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('gift.gifttitle', 'like', '%' . $searchValue . '%')
                  ->orWhere('giftredeemcoins.drawcode', 'like', '%' . $searchValue . '%')
                  ->orWhere(DB::raw("CONCAT(userregister.firstname, ' ', userregister.lastname)"), 'like', '%' . $searchValue . '%')
                  ->orWhere('userregister.address', 'like', '%' . $searchValue . '%')
                  ->orWhere('userregister.mobile', 'like', '%' . $searchValue . '%');
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add 'result' and 'action' columns
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            if($row->giftimage !=''){
                $row->giftimage='<img src="'.$row->giftimage.'" style="width:30px;"/>';
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
    public function giftreedemfetchData(Request $request)
{
    $columns = ['id', 'gifttitle', 'giftimage', 'username', 'drawcode', 'spentcoins', 'address', 'mobile'];

    $draw = $request->input('draw', 1);
    $start = $request->input('start', 0);
    $length = $request->input('length', 20); // Default 20 records per page
    $searchValue = $request->input('search.value', '');
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderColumn = $columns[$orderColumnIndex] ?? 'id';
    $orderDirection = $request->input('order.0.dir', 'DESC');

    $query = DB::table('giftredeemcoins')
        ->leftJoin('gift', 'giftredeemcoins.giftid', '=', 'gift.id')
        ->leftJoin('userregister', 'giftredeemcoins.user_id', '=', 'userregister.id')
        ->select(
            'giftredeemcoins.*',
            'gift.giftimage AS giftimage',
            'gift.gifttitle',
            DB::raw("CONCAT(userregister.firstname, ' ', userregister.lastname) AS username"),
            'userregister.mobile',
            'giftredeemcoins.address'
        );

    // Apply search filter
    if (!empty($searchValue)) {
        $query->where(function ($q) use ($searchValue) {
            $q->where('gift.gifttitle', 'like', '%' . $searchValue . '%')
                ->orWhere('giftredeemcoins.drawcode', 'like', '%' . $searchValue . '%')
                ->orWhere(DB::raw("CONCAT(userregister.firstname, ' ', userregister.lastname)"), 'like', '%' . $searchValue . '%')
                ->orWhere('giftredeemcoins.address', 'like', '%' . $searchValue . '%')
                ->orWhere('userregister.mobile', 'like', '%' . $searchValue . '%');
        });
    }

    // Get the total number of filtered records
    $totalRecords = $query->count();

    // Apply pagination
    $filteredData = $query->orderBy($orderColumn, $orderDirection)
        ->skip($start)
        ->take($length)
        ->get();

    $counter = $start + 1;

    // Map the data to add 'result' and 'action' columns
    $formattedData = $filteredData->map(function ($row) use (&$counter) {
        // Assign row number and increment counter
        $row->rowNumber = $counter++;
        if (!empty($row->giftimage)) {
            $row->giftimage = '<img src="' . $row->giftimage . '" style="width:30px;"/>';
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

}

