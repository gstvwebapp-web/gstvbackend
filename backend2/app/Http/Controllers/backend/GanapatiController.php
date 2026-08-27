<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Userganapati;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class GanapatiController extends Controller
{
    // Get all pages
   
    public function index()
    {
        return view('ganapatiutsav.alllist');     
    }
 
    public function updateStatus(Request $request)
    {
        // $athai_id = $request->input('athai_id');
        // $status = $request->input('status');

         $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');

        $Useraksana = Userganapati::find($id);

        if ($Useraksana) {
            $Useraksana->status = $status;
            $Useraksana->update();
        }
        return redirect('/ganapatiutsav/')->with('success', 'Ganapatu Utsav Status has been updated successfully');
    } 
 
 
    public function ganapatiutsavfetchData(Request $request)
    {
        $columns = ['id', 'name', 'address', 'featureImage','mobile','created_at', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        
        // If not cached, run the query
        $query = DB::table('user_ganapati as g')->leftJoin('userregister as u', 'g.userID', '=', 'u.id') ->select('g.*','u.mobile as mobile');

        // Total records
        $totalRecords = DB::table('user_ganapati')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('name', 'like', "%$searchValue%")
                        ->orWhere('address', 'like', "%$searchValue%");
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
            // $row->featureImage = '<img src="' . config('app.backendnewsurl') . '/public/uploads/ganapati/' . $row->featureImage . '" width="120" />';
             
            $row->featureImage = '
    <div style="position:relative; display:inline-block;">
        <img id="img_'.$row->id.'" 
             src="' . config('app.backendnewsurl') . '/public/uploads/ganapati/' . $row->featureImage . '" 
             width="120" style="transition: transform 0.3s;" />

        <button type="button" 
            class="btn btn-sm btn-info rotate-btn" 
            data-id="'.$row->id.'" 
            data-file="'.$row->featureImage.'" 
            style="position:absolute; bottom:5px; right:5px;">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>';
            $row->created_at = Carbon::parse($row->created_at)->format('d M Y h:i A');
            

             $row->action = ' <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/ganapatiutsav/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>   <form action="' . url('/ganapatiutsav/destroy', $row->id) . '" method="POST" style="display:inline;">
                     ' . csrf_field() . method_field('DELETE') . '
                   
                    <button type="submit" onclick="return confirm(\'Are you sure you want to delete this record?\')" title="Delete" class="btn delCls iconbtn btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                   
                </form>';
            
        }
        

        // Prepare the response data
        $responseData = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        ]; 

        return response()->json($responseData);
    }
     
public function rotateImage(Request $request)
{
    $id   = $request->input('id');
    $file = $request->input('file');

    $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/ganapati/";
    $filePath  = $mediaPath . $file;

    if (!file_exists($filePath)) {
        return response()->json(['success' => false, 'message' => 'File not found!'], 404);
    }

    // load image
    $info = getimagesize($filePath);
    switch ($info['mime']) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return response()->json(['success' => false, 'message' => 'Unsupported image type'], 400);
    }

    // rotate 90°
    $bg = imagecolorallocatealpha($source, 0, 0, 0, 127);
    $rotated = imagerotate($source, -90, $bg);
    imagealphablending($rotated, false);
    imagesavealpha($rotated, true);

    // new unique name to avoid cache
    $filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
    $webpName = $filenameWithoutExt . time() . '.webp';
    $webpPath = $mediaPath . $webpName;

    // save WebP
    imagewebp($rotated, $webpPath, 80);

    // generate 200px PNG thumbnail
    $thumbName = $filenameWithoutExt . '_200.png';
    $thumbPath = $mediaPath . $thumbName;
    $w = imagesx($rotated);
    $h = imagesy($rotated);
    $newW = 200;
    $newH = intval($newW * $h / $w);
    $thumb = imagecreatetruecolor($newW, $newH);
    imagealphablending($thumb, false);
    imagesavealpha($thumb, true);
    $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
    imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
    imagecopyresampled($thumb, $rotated, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagepng($thumb, $thumbPath, 9);

    // cleanup
    imagedestroy($source);
    imagedestroy($rotated);
    imagedestroy($thumb);

    // update DB
    DB::table('user_ganapati')->where('id', $id)->update([
        'featureImage' => $webpName,
        'updated_at'   => now()
    ]);
	return redirect('/ganapatiutsav/')->with('success', 'Ganapti Utsav Data has been deleted successfully');
    return response()->json([
        'success' => true,
        'message' => 'Image rotated and saved',
        'file'    => $webpName,
        'thumb'   => $thumbName
    ]);
}

public function rotateImage29(Request $request)
{
    $id = $request->input('id');
    $file = $request->input('file');

    $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/ganapati/";
    // $filePath = $file;
    $filePath = $mediaPath . $file;

    if (!file_exists($filePath)) {
        return response()->json(['success' => false, 'message' => 'File not found!'], 404);
    }

    // Detect extension and base name
    $filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME);

    // Open image depending on type
    $info = getimagesize($filePath);
    switch ($info['mime']) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return response()->json(['success' => false, 'message' => 'Unsupported image type'], 400);
    }

    // ✅ Rotate the image
    $rotated = imagerotate($source, -90, 0);

    // Temporary rotated file (in memory)
    //$tmpPath = $mediaPath . $filenameWithoutExt . '_rotated.png';
   // imagepng($rotated, $tmpPath, 9);
  
  	$tmpPath = $mediaPath . $filenameWithoutExt . '_rotated.webp';
    imagewebp($rotated, $tmpPath, 90);
  	 //$webpName = $filenameWithoutExt . '.webp';
    //imagewebp($rotated, $mediaPath . $webpName, 80);

    // ✅ Now re-use your helpers

    // Main image → .webp compressed
    $webpName = $filenameWithoutExt . '.webp';
    $this->compressImageToWebp($tmpPath, $mediaPath . $webpName, 80);

    // Thumbnail → 200px PNG
    $thumbName = $filenameWithoutExt . '_200.png';
    $this->createResizedPng($tmpPath, $mediaPath . $thumbName, 200);

    // Clean up
    unlink($tmpPath);
    imagedestroy($source);
    imagedestroy($rotated);

    // ✅ Update DB so new name is linked
    DB::table('user_ganapati')->where('id', $id)->update([
        'featureImage' => $webpName,
        'updated_at'   => now()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Image rotated and saved as WebP + 200px PNG',
        'file'    => $webpName,
        'thumb'   => $thumbName
    ]);
}
   
 private function createResizedPng($source, $destination, $newWidth)
    {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } else {
            copy($source, $destination);
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $newHeight = floor($height * ($newWidth / $width));

        $tmp = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);

        imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagepng($tmp, $destination, 9); // PNG quality (0–9), 9 = max compression

        imagedestroy($image);
        imagedestroy($tmp);
    }
    private function compressImageToWebp($source, $destination, $quality = 80)
    {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
            // Preserve transparency for PNG
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } else {
            // If unsupported format, just return false
            return false;
        }

        // Save as WebP with given quality
        imagewebp($image, $destination, $quality);

        imagedestroy($image);

        return true;
    }
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $Useraksana = Userganapati::find($id); 
        $Useraksana->delete();
  
        return redirect('/ganapatiutsav/')->with('success', 'Ganapti Utsav Data has been deleted successfully');
    }
}
