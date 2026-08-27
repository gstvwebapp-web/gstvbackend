<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Campuscorner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Route;
use File;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\Gif;
use FFMpeg\Format\Video\X264;

class CampuscornerController extends Controller
{
    // Get all pages
   
    public function index()
    {
        return view('campuscorner.alllist');   
        
    }
 
    public function updateStatus(Request $request)
    {

         $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');

        $Usermygujarat = Campuscorner::find($id);

        if ($Usermygujarat) {
            $Usermygujarat->status = $status;
            $Usermygujarat->update();
        }
        return redirect('/campuscorner/')->with('success', 'Campus corner Status has been updated successfully');
    } 
 
 
    public function campuscornerfetchData(Request $request)
    {
        $columns = ['id', 'name', 'title', 'city','author_name','created_at', 'status', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        
        // If not cached, run the query
        $query = DB::table('campuscorner as e');
        	$query = DB::table('campuscorner as c1')
            ->select(
                'c1.*', 
                'u1.name as author_name'
            )
            ->leftJoin('users as u1', 'c1.adminid', '=', 'u1.id') // Join for author info (user)
            ->orderBy('c1.id', 'DESC');

        // Total records
        $totalRecords = DB::table('campuscorner')->count();
        $filteredRecords = $query->count();

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($subquery) use ($searchValue) {
                $subquery->where('name', 'like', "%$searchValue%")
                        ->orWhere('title', 'like', "%$searchValue%")
                        ->orWhere('city', 'like', "%$searchValue%");
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
            $images = json_decode($row->featureImage, true);
            $firstImage = $images[0] ?? null; // Get the first image if available 
            
            if($firstImage == ""){
                $newFeatureImage = config('app.url')."/public/assets/images/news-default.png";
            } else { 
                $newFeatureImage = config('app.backendnewsurl') . "/public/uploads/campuscorner/" . $firstImage;
            }
            $row->featureImage = '<img src="' . $newFeatureImage . '" width="120" />';
            
            $row->created_at = Carbon::parse($row->created_at)->format('d M Y h:i A');
            if($row->status == "Drop"){
                $row->status = '<span class="badge bg-danger">'.$row->drop_reason.'</span>';
            }
			
             $row->action = ' <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" href="' . url('/campuscorner/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
             <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/campuscorner/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
               <form action="' . url('/campuscorner/destroy', $row->id) . '" method="POST" style="display:inline;">
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
     
    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $Usermygujarat = Campuscorner::find($id); 
        $Usermygujarat->delete();
  
        return redirect('/campuscorner/')->with('success', 'Campus corner has been deleted successfully');
    }
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $marugujarat = Campuscorner::find($id); 
        $data =  array();
        $data['marugujarat'] =  $marugujarat;
        return view('campuscorner.edit', compact('data'));
    }
    public function update(Request $request)
    {
        $gujarat = Campuscorner::find($request->input('gujaratid'));

        if (!$gujarat) {
            return redirect()->back()->withErrors(['msg' => 'Record not found!']);
        }

        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/backend/public/uploads/campuscorner';

        // Step 1: Get existing images
        $existingImages = $request->input('old_images', []);

        // Step 2: Handle deletion of old images
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $img) {
                $filePath = $mediaPath . '/' . $img;
                if (is_file($filePath)) {
                    unlink($filePath);
                }
                // Remove from array
                $existingImages = array_diff($existingImages, [$img]);
            }
        }

        // Step 3: Handle new image uploads
        if ($request->hasFile('uploadimages')) {
            foreach ($request->file('uploadimages') as $file) {
                if ($file->isValid()) {
                    $filename = 'img_' . time() . '_' . str_replace(' ', '-', $file->getClientOriginalName());
                    $file->move($mediaPath, $filename);
                    $existingImages[] = $filename;
                }
            }
        }

        if ($request->has('rotations')) {
            foreach ($request->input('rotations') as $imgName => $rotation) {
                $filePath = $mediaPath . '/' . $imgName;
                if (file_exists($filePath) && intval($rotation) > 0) {
                    try {
                        // Use Intervention Image to rotate
                        $image = Image::make($filePath)->rotate(-intval($rotation)); // clockwise rotation
                        $image->save($filePath);
                    } catch (\Exception $e) {
                        \Log::error("Image rotation failed for {$imgName}: " . $e->getMessage());
                    }
                }
            }
        }
        

        // Step 4: Handle video
        $uploadedVideo = $gujarat->video;
        if ($request->hasFile('uploadvideo')) {
            if ($uploadedVideo && file_exists($mediaPath . '/' . $uploadedVideo)) {
                unlink($mediaPath . '/' . $uploadedVideo);
            }
            $video = $request->file('uploadvideo');
            $videoName = 'video_' . time() . '_' . str_replace(' ', '-', $video->getClientOriginalName());
            $video->move($mediaPath, $videoName);
            $uploadedVideo = $videoName;

            // ✅ Generate .webp from uploaded video
            $videoPath = $mediaPath . '/' . $videoName;
            $webpName  = pathinfo($videoName, PATHINFO_FILENAME) . '.webp';
            $webpPath  = $mediaPath . '/' . $webpName;

            // Run ffmpeg command (1-second animated webp)
            $cmd = "ffmpeg -y -ss 0 -t 1 -i " . escapeshellarg($videoPath) . " -vf 'scale=480:-1,fps=10' -loop 0 -an -vsync 0 " . escapeshellarg($webpPath);
            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0) {
                \Log::error('FFmpeg WebP generation failed', ['video' => $videoPath, 'cmd' => $cmd]);
            }
        }

        // Step 5: Update record
        $gujarat->title = $request->input('title');
        $gujarat->description = $request->input('description');
        $gujarat->city = $request->input('city');
        $gujarat->school = $request->input('school');
        $gujarat->name = $request->input('name');
        $gujarat->featureImage = json_encode(array_values($existingImages)); // reset keys
        $gujarat->video = $uploadedVideo;
        $gujarat->status = $request->input('status');
        $gujarat->drop_reason = $request->input('drop_reason');
        $gujarat->adminid = Auth::id();
        $gujarat->updated_at = now();
        $gujarat->save();

        // Log
        Log::create([
            'userID' => Auth::id(),
            'message' => 'Campus corner updated: ' . $request->input('title'),
            'action'  => 'Edit',
            'contents'=> json_encode($request->all()),
        ]);

        return redirect('/campuscorner/')->with('success', 'Campus corner updated successfully!');
    }

}
