<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Storage;
use File;
use Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\Gif;
use FFMpeg\Format\Video\X264;
use App\Jobs\ProcessVideoPreviewgif;

class MediaController extends Controller
{
    
    // Get all Media
    public function index(Request $request)
    {
        $value = $request->query('page');
        $pageNumber = Route::current()->parameter('pageNumber'); 
        $perPage = $request->input('per_page', 12); // Default to 10 items per page
        $page = $request->input('page', $pageNumber);
         
        $media = DB::table('medias')->orderBy('id','DESC')->paginate($perPage, ['*'], 'page', $page);
        $data =  array();
        $data['media'] =  $media;
        if($media->currentPage()==1)
        {
            return view('media.list', compact('data'));
        } else {
            return response()->json($media, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function mediaSearch1(Request $request)
    {
        $txtval = $request->input('txtval');
        $optionSearch = $request->input('optionSearch');
      	$media = '';
        if($optionSearch=='image') {
            $media = Media::where('photos', 'like', '%' . $txtval . '%')->orderBy('id', 'DESC')->get();
        } else if($optionSearch=='video') {
            $media = Media::where('photos', 'like', '%' . $txtval . '%')->where('photos', 'LIKE', '%.mp4')->orderBy('id', 'DESC')->get();
        }
      	return response()->json($media, 200, ['success' => 'Sucess'], JSON_UNESCAPED_UNICODE);
    }
    public function mediaSearch(Request $request)
    {
        $txtval = $request->input('txtval');
        $optionSearch = $request->input('optionSearch');
        $perPage = $request->input('per_page', 12); // Default to 12 items per page
        $page = $request->query('page', 1); // Get the page number from request, default to 1

        $query = Media::orderBy('id', 'DESC');

        if ($optionSearch == 'image') {
            $query->where('photos', 'like', '%' . $txtval . '%');
        } elseif ($optionSearch == 'video') {
            $query->where('photos', 'like', '%' . $txtval . '%')
                ->where('photos', 'LIKE', '%.mp4');
        }

        // Apply pagination
        $media = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($media, 200, ['success' => 'Success'], JSON_UNESCAPED_UNICODE);
    }

    private function getUniqueFileName($originalFileName, $extension)
    {
        
        $newFileName = $originalFileName . '.' . $extension;
        $counter = 1;
        
        $fileExistsInDatabase = Media::where('photos', $newFileName)->exists();
        
        if($fileExistsInDatabase) {
            $newFileName = $originalFileName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $newFileName;
    }
    public function store(Request $request)
    {
        if ($files = $request->file('images')) { 
            $video_start = $request->get('video_start');
            $year = date('Y');
            $month = date('m');
            $day = date('d');
    
            // Create the folder structure (year/month/date) if it doesn't exist
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/media/$year/$month/$day/";
    
            // Check and create directories
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $cleanFilename = preg_replace('/[^A-Za-z0-9\-]/', '', $files->getClientOriginalName());
            $extension = pathinfo($files->getClientOriginalName(), PATHINFO_EXTENSION);
            $imgname = str_replace(' ', '-',$cleanFilename);
            $fileNameWithoutExtension = pathinfo($imgname, PATHINFO_FILENAME);
            $featureImage = $this->getUniqueFileName($fileNameWithoutExtension, $extension);
            $imgname = pathinfo($featureImage, PATHINFO_FILENAME);
            $randomCode = Str::random(8).time();
            $mimeType = $files->getMimeType();
            if (str_starts_with($mimeType, 'image')  && $extension !== 'gif') { 
                $files->move($destinationPath, $featureImage);
                $img = Image::make($destinationPath . $featureImage);
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$extension;
                $webpPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$extension;
                $img->save($webpPath, 100);

                $img->resize(350, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $smallImgPath = $destinationPath . pathinfo($featureImage, PATHINFO_FILENAME).'_'.$randomCode.'_small.'.$extension;
                $img->save($smallImgPath, 100);

                $img = Image::make($destinationPath . $featureImage); // Resetting the image
                $img->resize(700, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $mediumImgPath = $destinationPath . pathinfo($featureImage, PATHINFO_FILENAME).'_'.$randomCode.'_medium.'.$extension;
                $img->save($mediumImgPath, 100);
              
                unlink($destinationPath.$featureImage);
            } elseif ($extension === 'gif') {
            // Handle direct GIF uploads
                $originalGif = $imgname . '_' . $randomCode . '_video.gif';
                $smallGif    = $imgname . '_' . $randomCode . '_video_small.gif';
                $mediumGif   = $imgname . '_' . $randomCode . '_video_medium.gif';
                $smallJpg    = $imgname . '_' . $randomCode . '_video_small.jpg';

                // Move original GIF
                $files->move($destinationPath, $originalGif);

                // ✅ Just copy the original GIF without resizing
                try {
                    copy($destinationPath . $originalGif, $destinationPath . $smallGif);
                    copy($destinationPath . $originalGif, $destinationPath . $mediumGif);
                } catch (\Exception $e) {
                    \Log::error("GIF copy failed: " . $e->getMessage());
                }

                // ✅ Create JPG thumbnail (first frame only)
                try {
                    $thumb = Image::make($destinationPath . $originalGif);
                    $thumb->resize(320, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save($destinationPath . $smallJpg, 100);
                } catch (\Exception $e) {
                    \Log::error("Thumbnail JPG creation failed: " . $e->getMessage());
                }

                // Save to DB
                $webpimg = $originalGif;
               

            } 
            elseif (str_starts_with($mimeType, 'video')) {

                $uploadVideo = $imgname . '_' . $randomCode . '.' . $files->extension();
                $files->move($destinationPath, $uploadVideo);

                // Save only video initially
                $webpimg = $uploadVideo;
                $featureImage = null;
				
                // Dispatch background job
                ProcessVideoPreviewgif::dispatch(
                    $destinationPath,
                    $uploadVideo,
                    $imgname,
                    $randomCode,
                  	$video_start ?? 0
                );
            } elseif (str_starts_with($mimeType, 'application/pdf')) {
                $featureImage = $imgname.'_'.$randomCode.'.'.$files->extension(); 
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.'.$files->extension();  
                $files->move($destinationPath, $featureImage);
            } else {
                return response()->json(['message' => 'Unsupported file type'], 400);
            }
            //$path = url('/public/media/');
          	//$path = 'https://www.gstv.in/backend/public/media';
            $path = config('app.front_url')."/backend/public/media/$year/$month/$day";
            $fullpath = $path.'/'.$webpimg;
            $media = new Media([
                'userID' => Auth::user()->id,
                'photos'=> $webpimg,
                'photoTitle' => $request->get('photoTitle'),
                'photoAlt' => $request->get('photoAlt'),
                'fullpath' => $fullpath,
              	'img_time' => $video_start
            ]);
            $media->save();
            $imageId = $media->id;
        }
        return response()->json(['imageId' => $imageId], 200);
        //return response()->json(['message' => 'File uploaded successfully'], 200);

    }
  	
  	public function getImgTime(Request $request)
    {
        $imgTime = Media::where('id', $request->id)->value('img_time');

        return response()->json([
            'img_time' => $imgTime
        ]);
    }
    public function update(Request $request)
    {
        $id = $request->get('photoid');
        $media = Media::find($id); 
        $media->photoTitle = $request->get('photoTitle');
        $media->photoAlt = $request->get('photoAlt');
        $media->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Media updated: ' . $request->get('photoTitle'),
            'action' => 'edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/mediagallery/')->with('success', 'Media has been updated successfully');
    }
    public function deleteImage($id)
    { 
        $media = Media::find($id); 
         if (!$media) {                   // ✅ null check FIRST — returns 404 immediately
            return response()->json(['message' => 'Media not found'], 404);
        }
        // $photos = $media->photos;
        $mediapath =  explode('media',$media->fullpath); 
        $path =  dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/media'.$mediapath[1];
 
        if ($media) {
            $filePath = $path;
            //unlink($filePath);
            // Generate paths for other image sizes
            $pathInfo = pathinfo($filePath);
            $smallImagePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_small.' . $pathInfo['extension'];
            $mediumImagePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_medium.' . $pathInfo['extension'];
            $mediumviedogifPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_video.gif';
            $mediumviedogifPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_video.png';

            // Delete the original and other sized images
            File::delete($filePath);
            File::delete($smallImagePath);
            File::delete($mediumImagePath);
            File::delete($mediumviedogifPath);

            // Delete the media record
            $media->delete();
            return response()->json(['message' => 'Image deleted successfully'], 200);
        }

        return response()->json(['message' => 'Image not found'], 404);
    }
}

