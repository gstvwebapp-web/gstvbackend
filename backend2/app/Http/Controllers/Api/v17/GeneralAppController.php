<?php

namespace App\Http\Controllers\Api\v17;
use File;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Userregister;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use App\Models\Useraksana;
use App\Models\Usermygujarat;
use Route;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\Userganapati;
use App\Models\Campuscorner;
use Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\Gif;
use FFMpeg\Format\Video\X264;

class GeneralAppController extends Controller
{ 
    public function submitekasana(Request $request)
    {
        $userId = $request->input('user_id');
        $uploadedImage = null;
        $uploadedVideo = null; 
       
        if ($request->hasFile('uploadimage')) {
            $file = $request->file('uploadimage');
            if ($file->isValid()) {
                $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/ekasana';
                // Always save as .webp
                $filenameekasana = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME);
                $filenameWebp = $filenameekasana . '.webp';
                $destinationWebp = $mediaPath . '/' . $filenameWebp;

                // ✅ Compress & convert to WebP
                $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);

                $uploadedImage = $filenameWebp;

                // ✅ Create 200px PNG for WhatsApp
                $filenamePng = $filenameekasana . '_200.png';
                $destinationPng = $mediaPath . '/' . $filenamePng;

                $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
            }
        }

        // Handle video
        if ($request->hasFile('uploadvideo')) {
            $file = $request->file('uploadvideo');
            if ($file->isValid()) {
                if ($file->getSize() > 25 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
                }
                $filename = time() . '_vid_' .  str_replace(' ', '-', $file->getClientOriginalName());
                //$mediaPath = public_path('uploads/aksana');
                $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/ekasana';
                $file->move($mediaPath, $filename);
                $uploadedVideo = $filename;
            }
        }
        $name = $request->input('name');
        $mobile = $request->input('mobile');
        $days = $request->input('days');
        $address = $request->input('address');
        $id = $request->input('id');
        // $featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $uploadedImage;
        // $featureVideo = '';
        // if($uploadedVideo != ""){
        //     $featureVideo = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $uploadedVideo;
        // }
        $data = [
            'userID'      => $userId,
            'name'        => $request->name,
            'mobile'      => $request->mobile,
            'days'      => $request->days,
            'address'     => $request->address,
            'featureImage'=> $uploadedImage,
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'created_at'  => now(),
            'updated_at'  => now()
        ];

        DB::table('user_aksana')->insert($data);
        return response()->json([
                'success' => true,
                'message' => 'Added successfully',
                // 'data' => $data
        ], 200);
       
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
    private function compressImageToWebp1($source, $destination, $quality = 80)
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
            return false; // unsupported
        }

        // Ensure directory exists
        $dir = dirname($destination);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Save as WebP with given quality
        if (!imagewebp($image, $destination, $quality)) {
            imagedestroy($image);
            return false; // failed to save
        }

        imagedestroy($image);
        return true;
    }
    public function getekasana(Request $request)
    {      
            $perPage = 10;
            $pageNumber = $request->input('pageNumber', 1);
            $ekasanalist = [];
            $userId = $request->input('user_id'); 
            $totalCount = DB::table('user_aksana')
            ->where('userID', $userId)
            ->count();
            if($totalCount > 0){
            $ekasanalist =  DB::table('user_aksana')
            ->where('userID', $userId)
            ->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $pageNumber);

            // ✅ Append full URL for images and videos
            $ekasanalist->getCollection()->transform(function ($row) {
                if (!empty($row->featureImage)) {
                    // if .webp → replace with _200.png
                    if (\Illuminate\Support\Str::endsWith($row->featureImage, '.webp')) {
                        $row->featureImageThumb = config('app.backendnewsurl') . '/public/uploads/ekasana/' . preg_replace('/\.webp$/i', '', $row->featureImage) . '_200.png';
                    }
                    $row->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->featureImage;
                }

                if (!empty($row->video)) {
                    $row->video = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->video;
                }

                return $row;
            });
            }
             // $data['totalCount'] = $totalCount;
            // $user = Userregister::find($userId);
            // $data['user'] = $user;
           return response()->json([
            'success' => true,
            'data' => $ekasanalist
        ], 200);
    }
    public function editekasana(Request $request)
    {
        $userId = $request->input('user_id'); 
        $akasanaid = $request->input('akasanaid'); 

        // Fetch user data using the user ID
        $akasanalist = Useraksana::where('id', $akasanaid)
                    ->where('userID', $userId)
                    ->first();

        if ($akasanalist) {
            // Add full URL for featureImage (with special case for .webp → _200.png)
            if ($akasanalist->featureImage) {
                if (str_ends_with($akasanalist->featureImage, '.webp')) {
                    $akasanalist->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $akasanalist->featureImage;
                } else {
                    $akasanalist->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $akasanalist->featureImage;
                }
            } 
            // Add full URL for video
            if ($akasanalist->video) {
                $akasanalist->video = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $akasanalist->video;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $akasanalist
        ], 200);
    }

     public function updateekasana(Request $request)
    {
        $userId = $request->input('user_id'); 
        $akasanaId = $request->input('akasanaid'); 
        // Fetch the existing entry
        $ekasana = DB::table('user_aksana')->where('id', $akasanaId)->first();
        if (!$ekasana) {
            return response()->json(['success' => false, 'message' => 'Akasana not found!'], 404);
        }

        // Keep old files by default
        $uploadedImage = $ekasana->featureImage;
        $uploadedVideo = $ekasana->video;

        $oldImage  = $request->input('oldImage', null);
        $uploadedImage = null;
        if ($oldImage && str_contains($oldImage, '/')) {
            $uploadedImage = basename($oldImage); // keep filename only
        }

        $oldVideo  = $request->input('oldVideo', null);
        $uploadedVideo = null;
        if ($oldVideo && str_contains($oldVideo, '/')) {
            $uploadedVideo = basename($oldVideo); // keep filename only
        }


        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/ekasana';
        $mediaPathSlash = $mediaPath . '/';

        // Upload new feature image if provided
        if ($request->hasFile('uploadimage')) {
            // Delete old image if exists
            if ($uploadedImage && file_exists($mediaPathSlash . $uploadedImage)) {
                unlink($mediaPathSlash . $uploadedImage);
            }

            $image = $request->file('uploadimage');

            // Always save as .webp
            $imageNameeksana = time() . '_' . pathinfo(str_replace(' ', '-', $image->getClientOriginalName()), PATHINFO_FILENAME);
            $imageName = $imageNameeksana . '.webp';
            $destination = $mediaPathSlash . $imageName;

            // ✅ Compress & convert to WebP
            $this->compressImageToWebp($image->getRealPath(), $destination, 80);

            $uploadedImage = $imageName;

            // ✅ Create 200y
            $pngName = $imageNameeksana . '_200.png';
            $destinationPng = $mediaPathSlash . $pngName;

            $this->createResizedPng($image->getRealPath(), $destinationPng, 200);
        }

        // Upload new video if provided
        if ($request->hasFile('uploadvideo')) {
            // Delete old video if exists
            if ($uploadedVideo && file_exists($mediaPathSlash . $uploadedVideo)) {
                unlink($mediaPathSlash . $uploadedVideo);
            }

            $video = $request->file('uploadvideo');
            $videoName = time() . '_' . str_replace(' ', '-', $video->getClientOriginalName());
            $video->move($mediaPath, $videoName);
            $uploadedVideo = $videoName;
        }

        // Prepare update data
        $updateData = [
            'name'        => $request->name,
            'mobile'      => $request->mobile,
            'days'      => $request->days,
            'address'     => $request->address,
            'featureImage'=> $uploadedImage,
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'updated_at'  => now()
        ];

        DB::table('user_aksana')->where('id', $akasanaId)->update($updateData);


        
         $responseData = $updateData;
        if ($uploadedImage) {
            if (str_ends_with($uploadedImage, '.webp')) {
                $responseData['featureImage'] = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $uploadedImage;
            } else {
                $responseData['featureImage'] = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $uploadedImage;
            }
        } else {
            $responseData['featureImage'] = null;
        }

        if ($uploadedVideo) {
            $responseData['video'] = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $uploadedVideo;
        } else {
            $responseData['video'] = null;
        }

        return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'data' => $responseData
        ], 200);
         
    }
    public function athaitaplist(Request $request)
    { 
            $perPage = 4;
            $page = $request->input('pageNumber', 1);
            $minutes = 30;

            // Redis cache key (page-based + app type)
            $newsKey = "ekasana_pageapp_{$page}_" . config('app.webtype');

            if (Redis::exists($newsKey)) {
                // Get from Redis (decode as objects, not arrays)
                $newsData = json_decode(Redis::get($newsKey));

                // Rebuild paginator from cached data
                $news = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect($newsData->data),   // make it a collection of objects
                    $newsData->total,
                    $newsData->per_page,
                    $newsData->current_page,
                    ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
                );
            } else {
                // Query DB
                $newsQuery = DB::table('user_aksana')
                    ->where('status', 'Active')
                    ->orderBy('created_at', 'desc');

                $news = $newsQuery->paginate($perPage, ['*'], 'page', $page);

                $news->getCollection()->transform(function ($row) {
                    if (!empty($row->featureImage)) {
                        // if .webp → replace with _200.png
                        if (\Illuminate\Support\Str::endsWith($row->featureImage, '.webp')) {
                            $row->featureImageThumb = config('app.backendnewsurl') . '/public/uploads/ekasana/' . preg_replace('/\.webp$/i', '', $row->featureImage) . '_200.png';
                        }
                        $row->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->featureImage;
                    }

                    if (!empty($row->video)) {
                        $row->video = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $row->video;
                    }

                    return $row;
                });

                // Save to Redis (store items as plain objects)
                Redis::setex($newsKey, $minutes * 60, json_encode([
                    'data'         => $news->items(),   // stdClass objects
                    'total'        => $news->total(),
                    'per_page'     => $news->perPage(),
                    'current_page' => $news->currentPage(),
                ]));
            }
 
               return response()->json([
                'success' => true,
                'data' => $news
        ], 200);
            
    }
    public function athaitapdetails(Request $request)
    {
        $akasanaId = $request->input('akasanaid'); 
       
        $minutes = 30;

        // Redis cache key (id-based + app type)
        $ekasnaKey = "ekasanadetailapp_{$akasanaId}_" . config('app.webtype');

        if (Redis::exists($ekasnaKey)) {
            // Get from Redis (decode as object, not array)
            $ekasanadetail = json_decode(Redis::get($ekasnaKey));
        } else {
            // Query DB for single record
            $ekasanadetail = DB::table('user_aksana')
                ->where('status', 'Active')
                ->where('id', $akasanaId)
                ->first();
            if ($ekasanadetail) {
            // Add full URL for featureImage (with special case for .webp → _200.png)
                if ($ekasanadetail->featureImage) {
                    if (str_ends_with($ekasanadetail->featureImage, '.webp')) {
                        $ekasanadetail->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $ekasanadetail->featureImage;
                    } else {
                        $ekasanadetail->featureImage = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $ekasanadetail->featureImage;
                    }
                } 
                // Add full URL for video
                if ($ekasanadetail->video) {
                    $ekasanadetail->video = config('app.backendnewsurl') . '/public/uploads/ekasana/' . $ekasanadetail->video;
                }
            }
            // Save to Redis if found
            if ($ekasanadetail) {
                Redis::setex($ekasnaKey, $minutes * 60, json_encode($ekasanadetail));
            }
        } 
         
         return response()->json([
            'success' => true,
            'data' => $ekasanadetail
        ], 200);            
    }

    //ganapati
    public function submitganapati(Request $request)
    {
        $userId = $request->input('user_id');
        $uploadedImage = null;
        $uploadedVideo = null; 
       
        if ($request->hasFile('uploadimage')) {
            $file = $request->file('uploadimage');
            if ($file->isValid()) {
                $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/ganapati';

                // Always save as .webp
                $filenameGanapati = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME);
                $filenameWebp = $filenameGanapati . '.webp';
                $destinationWebp = $mediaPath . '/' . $filenameWebp;

                // ✅ Compress & convert to WebP
                $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);

                $uploadedImage = $filenameWebp;

                // ✅ Create 200px PNG for WhatsApp
                $filenamePng = $filenameGanapati . '_200.png';
                $destinationPng = $mediaPath . '/' . $filenamePng;

                $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
            }
        }

         
        $name = $request->input('name');
        $address = $request->input('address');
        $data = [
            'userID'      => $userId,
            'name'        => $request->name,
            'address'     => $request->address,
            'featureImage'=> $uploadedImage,
            'status'      => 'Inactive',
            'created_at'  => now(),
            'updated_at'  => now()
        ];

        DB::table('user_ganapati')->insert($data);
        return response()->json([
                'success' => true,
                'message' => 'Added successfully',
                'data' => $data
        ], 200);
       
    }
    public function getganapati(Request $request)
    {      
            $perPage = 10;
            $pageNumber = $request->input('pageNumber', 1);
            $ekasanalist = [];
            $userId = $request->input('user_id'); 
            $totalCount = DB::table('user_ganapati')
            ->where('userID', $userId)
            ->count();
            if($totalCount > 0){
            $ekasanalist =  DB::table('user_ganapati')
            ->where('userID', $userId)
            ->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $pageNumber);

            // ✅ Append full URL for images and videos
            $ekasanalist->getCollection()->transform(function ($row) {
                if (!empty($row->featureImage)) {
                    // if .webp → replace with _200.png
                    if (\Illuminate\Support\Str::endsWith($row->featureImage, '.webp')) {
                        $row->featureImageThumb = config('app.backendnewsurl') . '/public/uploads/ganapati/' . preg_replace('/\.webp$/i', '', $row->featureImage) . '_200.png';
                    }
                    $row->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $row->featureImage;
                }

                return $row;
            });
            }
             // $data['totalCount'] = $totalCount;
            // $user = Userregister::find($userId);
            // $data['user'] = $user;
           return response()->json([
            'success' => true,
            'data' => $ekasanalist
        ], 200);
    }
    public function editganapati(Request $request)
    {
        $userId = $request->input('user_id'); 
        $akasanaid = $request->input('akasanaid'); 

        // Fetch user data using the user ID
        $akasanalist = Userganapati::where('id', $akasanaid)
                    ->where('userID', $userId)
                    ->first();

        if ($akasanalist) {
            // Add full URL for featureImage (with special case for .webp → _200.png)
            if ($akasanalist->featureImage) {
                if (str_ends_with($akasanalist->featureImage, '.webp')) {
                    $akasanalist->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $akasanalist->featureImage;
                } else {
                    $akasanalist->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $akasanalist->featureImage;
                }
            } 
            
        }

        return response()->json([
            'success' => true,
            'data' => $akasanalist
        ], 200);
    }

     public function updateganapati(Request $request)
    {
        $userId = $request->input('user_id'); 
        $akasanaId = $request->input('ganpatiid'); 
        // Fetch the existing entry
        $ekasana = DB::table('user_ganapati')->where('id', $akasanaId)->first();
        if (!$ekasana) {
            return response()->json(['success' => false, 'message' => 'Akasana not found!'], 404);
        }

        // Keep old files by default
        $uploadedImage = $ekasana->featureImage;

        $oldImage  = $request->input('oldImage', null);
        $uploadedImage = null;
        if ($oldImage && str_contains($oldImage, '/')) {
            $uploadedImage = basename($oldImage); // keep filename only
        }


        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/ganapati';
        $mediaPathSlash = $mediaPath . '/';

        // Upload new feature image if provided
        if ($request->hasFile('uploadimage')) {
            // Delete old image if exists
            if ($uploadedImage && file_exists($mediaPathSlash . $uploadedImage)) {
                unlink($mediaPathSlash . $uploadedImage);
            }

            $image = $request->file('uploadimage');

            // Always save as .webp
            $imageNameganapati = time() . '_' . pathinfo(str_replace(' ', '-', $image->getClientOriginalName()), PATHINFO_FILENAME);
            $imageName = $imageNameganapati . '.webp';
            $destination = $mediaPathSlash . $imageName;

            // ✅ Compress & convert to WebP
            $this->compressImageToWebp($image->getRealPath(), $destination, 80);

            $uploadedImage = $imageName;

            // ✅ Create 200px PNG copy
            $pngName = $imageNameganapati . '_200.png';
            $destinationPng = $mediaPathSlash . $pngName;

            $this->createResizedPng($image->getRealPath(), $destinationPng, 200);
        }

      
        // Prepare update data
        $updateData = [
            'name'        => $request->name,
            'address'     => $request->address,
            'featureImage'=> $uploadedImage,
            'status'      => 'Inactive',
            'updated_at'  => now()
        ];

        DB::table('user_ganapati')->where('id', $akasanaId)->update($updateData);

         $responseData = $updateData;
        if ($uploadedImage) {
            if (str_ends_with($uploadedImage, '.webp')) {
                $responseData['featureImage'] = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $uploadedImage;
            } else {
                $responseData['featureImage'] = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $uploadedImage;
            }
        } else {
            $responseData['featureImage'] = null;
        }

        return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
                'data' => $responseData
        ], 200);
         
    }
    public function ganapatilist(Request $request)
    { 
            $perPage = 4;
            $page = $request->input('pageNumber', 1);
            $minutes = 30;

            // Redis cache key (page-based + app type)
            $newsKey = "ganapati_pageapp_{$page}_" . config('app.webtype');

            if (Redis::exists($newsKey)) {
                // Get from Redis (decode as objects, not arrays)
                $newsData = json_decode(Redis::get($newsKey));

                // Rebuild paginator from cached data
                $news = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect($newsData->data),   // make it a collection of objects
                    $newsData->total,
                    $newsData->per_page,
                    $newsData->current_page,
                    ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
                );
            } else {
                // Query DB
                $newsQuery = DB::table('user_ganapati')
                    ->where('status', 'Active')
                    ->orderBy('created_at', 'desc');

                $news = $newsQuery->paginate($perPage, ['*'], 'page', $page);
                $news->getCollection()->transform(function ($row) {
                    if (!empty($row->featureImage)) {
                        // if .webp → replace with _200.png
                        if (\Illuminate\Support\Str::endsWith($row->featureImage, '.webp')) {
                            $row->featureImageThumb = config('app.backendnewsurl') . '/public/uploads/ganapati/' . preg_replace('/\.webp$/i', '', $row->featureImage) . '_200.png';
                        }
                        $row->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $row->featureImage;
                    }

                    return $row;
                });
                // Save to Redis (store items as plain objects)
                Redis::setex($newsKey, $minutes * 60, json_encode([
                    'data'         => $news->items(),   // stdClass objects
                    'total'        => $news->total(),
                    'per_page'     => $news->perPage(),
                    'current_page' => $news->currentPage(),
                ]));
            }
 
               return response()->json([
                'success' => true,
                'data' => $news
        ], 200);
            
    }
    public function ganapatidetails(Request $request)
    {
        $akasanaId = $request->input('akasanaid'); 
       
        $minutes = 30;

        // Redis cache key (id-based + app type)
        $ekasnaKey = "ganapatidetailapp_{$akasanaId}_" . config('app.webtype');

        if (Redis::exists($ekasnaKey)) {
            // Get from Redis (decode as object, not array)
            $ekasanadetail = json_decode(Redis::get($ekasnaKey));
        } else {
            // Query DB for single record
            $ekasanadetail = DB::table('user_ganapati')
                ->where('status', 'Active')
                ->where('id', $akasanaId)
                ->first();
            if ($ekasanadetail) {
            // Add full URL for featureImage (with special case for .webp → _200.png)
                if ($ekasanadetail->featureImage) {
                    if (str_ends_with($ekasanadetail->featureImage, '.webp')) {
                        $ekasanadetail->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $ekasanadetail->featureImage;
                    } else {
                        $ekasanadetail->featureImage = config('app.backendnewsurl') . '/public/uploads/ganapati/' . $ekasanadetail->featureImage;
                    }
                } 
            }
            // Save to Redis if found
            if ($ekasanadetail) {
                Redis::setex($ekasnaKey, $minutes * 60, json_encode($ekasanadetail));
            }
        } 
         
         return response()->json([
            'success' => true,
            'data' => $ekasanadetail
        ], 200);            
    }

    // maru gujarat
    public function submitmarugujarat(Request $request)
    {
        $userId = $request->input('user_id');
        $uploadedVideo = null;  

        $uploadedImages = [];

        if ($request->hasFile('uploadimages')) {
            foreach ($request->file('uploadimages') as $file) {
                if ($file->isValid()) {
                    $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/backend/public/uploads/gujarat';
                     // Always save as .webp
                    $filenamejornalist = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME);
                    $filenameWebp = $filenamejornalist . '.webp';
                    $destinationWebp = $mediaPath . '/' . $filenameWebp;

                    // ✅ Compress & convert to WebP
                    $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);

                    $uploadedImages[] = $filenameWebp;

                    // ✅ Create 200px PNG for WhatsApp
                    $filenamePng = $filenamejornalist . '_200.png';
                    $destinationPng = $mediaPath . '/' . $filenamePng;

                    $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
                }
            }
        }

        // Handle video
        // if ($request->hasFile('uploadvideo')) {
        //     $file = $request->file('uploadvideo');
        //     if ($file->isValid()) {
        //         if ($file->getSize() > 25 * 1024 * 1024) {
        //             return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
        //         }
        //         $filename = time() . '_vid_' .  str_replace(' ', '-', $file->getClientOriginalName());
        //         //$mediaPath = public_path('uploads/aksana');
        //         $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/backend/public/uploads/gujarat';
        //         $file->move($mediaPath, $filename);
        //         $uploadedVideo = $filename;
        //     }
        // }
        if ($request->hasFile('uploadvideo')) {
            $file = $request->file('uploadvideo');
            if ($file->isValid()) {
                if ($file->getSize() > 25 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
                }
                $filename = time() . '_vid_' . str_replace(' ', '-', $file->getClientOriginalName());
                $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/gujarat';
                $videoPath = $mediaPath . '/' . $filename;

                // Move video
                $file->move($mediaPath, $filename);
                $uploadedVideo = $filename;

                // ✅ Generate WebP preview (1 second clip)
                $gifName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                $gifPath = $mediaPath . '/' . $gifName;

                try {
                    $cmdWebp = "/usr/bin/ffmpeg -y -ss 0 -t 1 -i " . escapeshellarg($videoPath) . " -vf 'scale=480:-1,fps=10' -loop 0 -an -vsync 0 " . escapeshellarg($gifPath);
                    shell_exec($cmdWebp);
                } catch (\Exception $e) {
                    \Log::error("WebP generation failed: " . $e->getMessage());
                }
            }
        }
        
        $name = $request->input('name');
        $title = $request->input('title');
        $description = $request->input('description');
        $city = $request->input('city');
        $agree_status = $request->input('agree_status');
        $data = [
            'userID'      => $userId,
            'name'        => $name,
            'title'       => $title,
            'description' => $description,
            'city'        => $city,
            'agree_status' => $agree_status,
            'featureImage'=> json_encode($uploadedImages),
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'created_at'  => now(),
            'updated_at'  => now()
        ];

        DB::table('user_mygujarat')->insert($data);
        return response()->json([
                'success' => true,
                'message' => 'Added successfully',
                // 'data' => $data
        ], 200);
       
    }
    
    public function getmarugujarat(Request $request)
    {      
            $perPage = 10;
            $pageNumber = $request->input('pageNumber', 1);
            $gujaratlist = [];
            $userId = $request->input('user_id'); 
            $totalCount = DB::table('user_mygujarat')
            ->where('userID', $userId)
            ->count();
            if($totalCount > 0){
                $gujaratlist =  DB::table('user_mygujarat')
                ->where('userID', $userId)
                ->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $pageNumber);

                // ✅ Append full URL for images and videos
                  $gujaratlist->getCollection()->transform(function ($row) {
                // Multiple images (JSON array)
                if (!empty($row->featureImage)) {
                    $images = json_decode($row->featureImage, true);
                    if (is_array($images)) {
                        $row->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/gujarat/' . $img;
                        }, $images);
                    } else {
                        $row->featureImage = [];
                    }
                } else {
                    $row->featureImage = [];
                }

                // Video
                if (!empty($row->video)) {
                    $row->video = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $row->video;

                    // Generate same name with .webp
                    $videoFilename = pathinfo($row->video, PATHINFO_FILENAME) . '.webp';
                    $row->video_img = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $videoFilename;
                } else {
                    $row->video_img = null;
                }

                return $row;
            });
                // $gujaratlist->getCollection()->transform(function ($row) {
                //     if (!empty($row->featureImage)) {
                //         // if .webp → replace with _200.png
                //         if (\Illuminate\Support\Str::endsWith($row->featureImage, '.webp')) {
                //             $row->featureImageThumb = config('app.backendnewsurl') . '/public/uploads/gujarat/' . preg_replace('/\.webp$/i', '', $row->featureImage) . '_200.png';
                //         }
                //         $row->featureImage = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $row->featureImage;
                //     }

                //     if (!empty($row->video)) {
                //         $row->video = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $row->video;
                //     }

                //     return $row;
                // });
            }
           return response()->json([
            'success' => true,
            'data' => $gujaratlist
        ], 200);
    }
    public function editmarugujarat(Request $request)
    {
        $userId = $request->input('user_id'); 
        $gujaratid = $request->input('gujaratid'); 

        // Fetch user data using the user ID
        $gujaratlist = Usermygujarat::where('id', $gujaratid)
                    ->where('userID', $userId)
                    ->first();

        if ($gujaratlist) {
            // Handle multiple images
            if ($gujaratlist->featureImage) {
                $images = json_decode($gujaratlist->featureImage, true); // decode array from DB
                if (is_array($images)) {
                    $gujaratlist->featureImage = array_map(function ($img) {
                        return config('app.backendnewsurl') . '/public/uploads/gujarat/' . $img;
                    }, $images);
                } else {
                    $gujaratlist->featureImage = [];
                }
            } else {
                $gujaratlist->featureImage = [];
            }

            // Add full URL for video
            if ($gujaratlist->video) {
                $gujaratlist->video = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $gujaratlist->video;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $gujaratlist
        ], 200);
    }

    public function updatemarugujarat(Request $request)
    {
        $userId    = $request->input('user_id'); 
        $gujaratId = $request->input('gujaratid'); 

        // Fetch the existing entry
        $ekasana = DB::table('user_mygujarat')->where('id', $gujaratId)->first();
        if (!$ekasana) {
            return response()->json(['success' => false, 'message' => 'Journalist not found!'], 404);
        }

        // Old images/videos coming from frontend
        $oldImages = $request->input('oldImages', []); 
        $oldVideo  = $request->input('oldVideo', null); 

        if (!is_array($oldImages)) {
            $oldImages = json_decode($oldImages, true) ?? [];
        }

        // ✅ Start fresh with only the old images user wants to keep
        $uploadedImages = [];
        if (!empty($oldImages)) {
            foreach ($oldImages as $imgUrl) {
                $uploadedImages[] = basename($imgUrl); // keep only filename
            }
        }

        // ✅ Keep old video if user passed it
        $uploadedVideo = null;
        if ($oldVideo && str_contains($oldVideo, '/')) {
            $uploadedVideo = basename($oldVideo); // keep filename only
        }

        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/gujarat';
        $mediaPathSlash = $mediaPath . '/';

        // ✅ Handle new images (append to old ones)
        if ($request->hasFile('uploadimage')) {
            foreach ($request->file('uploadimage') as $file) {
                if ($file->isValid()) {
                    // Save as .webp
                    $filenameWebpjournalist = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME) . '.webp';
                    $filenameWebp = $filenameWebpjournalist . '.webp';
                    $destinationWebp = $mediaPathSlash . $filenameWebp;

                    $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);
                    $uploadedImages[] = $filenameWebp;

                    // Create 200px PNG
                    $filenamePng = $filenameWebpjournalist . '_200.png';
                    $destinationPng = $mediaPathSlash . $filenamePng;

                    $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
                }
            }
        }

        // ✅ Handle video (single string only)
        // if ($request->hasFile('uploadvideo')) {
        //     $video = $request->file('uploadvideo');

        //     if ($video->getSize() > 25 * 1024 * 1024) {
        //         return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
        //     }

        //     $videoName = time() . '_' . str_replace(' ', '-', $video->getClientOriginalName());
        //     $video->move($mediaPath, $videoName);

        //     $uploadedVideo = $videoName; // replace old video
        // }
        if ($request->hasFile('uploadvideo')) {
            $video = $request->file('uploadvideo');

            if ($video->getSize() > 25 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
            }

            $videoName = time() . '_' . str_replace(' ', '-', $video->getClientOriginalName());
            $videoPath = $mediaPathSlash . $videoName;

            $video->move($mediaPath, $videoName);
            $uploadedVideo = $videoName; // replace old video

            // ✅ Generate WebP preview (1 second)
            $gifName = pathinfo($videoName, PATHINFO_FILENAME) . '.webp';
            $gifPath = $mediaPathSlash . $gifName;

            try {
                $cmdWebp = "/usr/bin/ffmpeg -y -ss 0 -t 1 -i " . escapeshellarg($videoPath) . " -vf 'scale=480:-1,fps=10' -loop 0 -an -vsync 0 " . escapeshellarg($gifPath);
                shell_exec($cmdWebp);
            } catch (\Exception $e) {
                \Log::error("WebP generation failed: " . $e->getMessage());
            }
        }

        // ✅ Update DB
        $updateData = [
            'name'        => $request->input('name'),
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'city'        => $request->input('city'),
            'agree_status'=> $request->input('agree_status'),
            'featureImage'=> json_encode($uploadedImages),
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'updated_at'  => now()
        ];

        DB::table('user_mygujarat')->where('id', $gujaratId)->update($updateData);

        // ✅ Prepare response
        $responseData = $updateData;
        $responseData['featureImage'] = array_map(function ($img) {
            return config('app.backendnewsurl') . '/public/uploads/gujarat/' . $img;
        }, $uploadedImages);

        $responseData['video'] = $uploadedVideo 
            ? config('app.backendnewsurl') . '/public/uploads/gujarat/' . $uploadedVideo 
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
            'data'    => $responseData
        ], 200);
    }

   
    public function marugujaratlist(Request $request)
    {
        $perPage = 4;
        $pageNumber = $request->input('pageNumber', 1);
        $minutes = 30;

        // Redis cache key
        $newsKey = "marugujarat_pageapp_{$pageNumber}_" . config('app.webtype');

        if (Redis::exists($newsKey)) {
            // ✅ Load from cache
            $newsData = json_decode(Redis::get($newsKey));

            $gujaratlist = new \Illuminate\Pagination\LengthAwarePaginator(
                collect($newsData->data),   // convert back to collection
                $newsData->total,
                $newsData->per_page,
                $newsData->current_page,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            // ✅ Query DB
            $gujaratlist = DB::table('user_mygujarat')
                ->where('status', 'Active')
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            // ✅ Transform images & videos
            $gujaratlist->getCollection()->transform(function ($row) {
                // Multiple images (JSON array)
                if (!empty($row->featureImage)) {
                    $images = json_decode($row->featureImage, true);
                    if (is_array($images)) {
                        $row->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/gujarat/' . $img;
                        }, $images);
                    } else {
                        $row->featureImage = [];
                    }
                } else {
                    $row->featureImage = [];
                }

                // Video
                if (!empty($row->video)) {
                    $row->video = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $row->video;

                    // Generate same name with .webp
                    $videoFilename = pathinfo($row->video, PATHINFO_FILENAME) . '.webp';
                    $row->video_img = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $videoFilename;
                } else {
                    $row->video_img = null;
                }

                return $row;
            });

            // ✅ Save to Redis
            Redis::setex($newsKey, $minutes * 60, json_encode([
                'data'         => $gujaratlist->items(),
                'total'        => $gujaratlist->total(),
                'per_page'     => $gujaratlist->perPage(),
                'current_page' => $gujaratlist->currentPage(),
            ]));
        }

        return response()->json([
            'success' => true,
            'data'    => $gujaratlist
        ], 200);
    }

    public function marugujaratdetails(Request $request)
    {
        $gujaratid = $request->input('gujaratid'); 
       
        $minutes = 30;

        // Redis cache key (id-based + app type)
        $gujaratKey = "gujdetailapp1_{$gujaratid}_" . config('app.webtype');

        if (Redis::exists($gujaratKey)) {
            // Get from Redis (decode as object, not array)
            $gujaratdetail = json_decode(Redis::get($gujaratKey));
        } else {
            // Query DB for single record
            $gujaratdetail = DB::table('user_mygujarat')
                ->where('status', 'Active')
                ->where('id', $gujaratid)
                ->first();
            if ($gujaratdetail) {
                // Handle multiple images
                if ($gujaratdetail->featureImage) {
                    $images = json_decode($gujaratdetail->featureImage, true); // decode array from DB
                    if (is_array($images)) {
                        $gujaratdetail->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/gujarat/' . $img;
                        }, $images);
                    } else {
                        $gujaratdetail->featureImage = [];
                    }
                } else {
                    $gujaratdetail->featureImage = [];
                }

                // Add full URL for video
                if ($gujaratdetail->video) {
                    $gujaratdetail->video = config('app.backendnewsurl') . '/public/uploads/gujarat/' . $gujaratdetail->video;
                }
            }
            // Save to Redis if found
            if ($gujaratdetail) {
                Redis::setex($gujaratKey, $minutes * 60, json_encode($gujaratdetail));
            }
        } 

        $record = DB::table('user_mygujarat')
            ->where('status', 'Active')
            ->where('id', '<', $gujaratid)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();
          return response()->json([
            'success' => true,
            'data' => $gujaratdetail,
            'last_id' => $record ? $record->id : null,
        ], 200);              
    }


     // Campuscorner
    public function submitcampuscorner(Request $request)
    {
        $userId = $request->input('user_id');
        $uploadedVideo = null;
        $uploadedImages = [];

        if ($request->hasFile('uploadimages')) {
            foreach ($request->file('uploadimages') as $file) {
                if ($file->isValid()) {
                    $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/backend/public/uploads/campuscorner';
                     // Always save as .webp
                    $filenameWebpcampus = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME);
                    $filenameWebp = $filenameWebpcampus . '.webp';
                    $destinationWebp = $mediaPath . '/' . $filenameWebp;

                    // ✅ Compress & convert to WebP
                    $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);

                    $uploadedImages[] = $filenameWebp;

                    // ✅ Create 200px PNG for WhatsApp
                    $filenamePng = $filenameWebpcampus . '_200.png';
                    $destinationPng = $mediaPath . '/' . $filenamePng;

                    $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
                }
            }
        }

        // Handle video
        if ($request->hasFile('uploadvideo')) {
            $file = $request->file('uploadvideo');
            if ($file->isValid()) {
                if ($file->getSize() > 25 * 1024 * 1024) {
                    return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
                }
                $filename = time() . '_vid_' . str_replace(' ', '-', $file->getClientOriginalName());
                $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/campuscorner';
                $videoPath = $mediaPath . '/' . $filename;

                // Move video
                $file->move($mediaPath, $filename);
                $uploadedVideo = $filename;

                // ✅ Generate WebP preview (1 second clip)
                $gifName = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                $gifPath = $mediaPath . '/' . $gifName;

                try {
                    $cmdWebp = "/usr/bin/ffmpeg -y -ss 0 -t 1 -i " . escapeshellarg($videoPath) . " -vf 'scale=480:-1,fps=10' -loop 0 -an -vsync 0 " . escapeshellarg($gifPath);
                    shell_exec($cmdWebp);
                } catch (\Exception $e) {
                    \Log::error("WebP generation failed: " . $e->getMessage());
                }
            }
        }
        
        $name = $request->input('name');
        $title = $request->input('title');
        $description = $request->input('description');
        $city = $request->input('city');
        $school = $request->input('school');
        $agree_status = $request->input('agree_status');
        $data = [
            'userID'      => $userId,
            'name'        => $name,
            'title'       => $title,
            'description' => $description,
            'city'        => $city,
            'school'        => $school,
            'agree_status' => $agree_status,
            'featureImage'=> json_encode($uploadedImages),
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'created_at'  => now(),
            'updated_at'  => now()
        ];

        DB::table('campuscorner')->insert($data);
        return response()->json([
                'success' => true,
                'message' => 'Added successfully',
                // 'data' => $data
        ], 200);
       
    }
    
    public function getcampuscorner(Request $request)
    {      
            $perPage = 10;
            $pageNumber = $request->input('pageNumber', 1);
            $gujaratlist = [];
            $userId = $request->input('user_id'); 
            $totalCount = DB::table('campuscorner')
            ->where('userID', $userId)
            ->count();
            if($totalCount > 0){
                $gujaratlist =  DB::table('campuscorner')
                ->where('userID', $userId)
                ->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $pageNumber);

                // ✅ Append full URL for images and videos
                  $gujaratlist->getCollection()->transform(function ($row) {
                // Multiple images (JSON array)
                if (!empty($row->featureImage)) {
                    $images = json_decode($row->featureImage, true);
                    if (is_array($images)) {
                        $row->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $img;
                        }, $images);
                    } else {
                        $row->featureImage = [];
                    }
                } else {
                    $row->featureImage = [];
                }

                // Video
                if (!empty($row->video)) {
                    $row->video = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $row->video;

                    // Generate same name with .webp
                    $videoFilename = pathinfo($row->video, PATHINFO_FILENAME) . '.webp';
                    $row->video_img = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $videoFilename;
                } else {
                    $row->video_img = null;
                }

                return $row;
            });
            }
           return response()->json([
            'success' => true,
            'data' => $gujaratlist
        ], 200);
    }
    public function editcampuscorner(Request $request)
    {
        $userId = $request->input('user_id'); 
        $gujaratid = $request->input('campuscornerid'); 

        // Fetch user data using the user ID
        $gujaratlist = Campuscorner::where('id', $gujaratid)
                    ->where('userID', $userId)
                    ->first();

        if ($gujaratlist) {
            // Handle multiple images
            if ($gujaratlist->featureImage) {
                $images = json_decode($gujaratlist->featureImage, true); // decode array from DB
                if (is_array($images)) {
                    $gujaratlist->featureImage = array_map(function ($img) {
                        return config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $img;
                    }, $images);
                } else {
                    $gujaratlist->featureImage = [];
                }
            } else {
                $gujaratlist->featureImage = [];
            }

            // Add full URL for video
            if ($gujaratlist->video) {
                $gujaratlist->video = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $gujaratlist->video;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $gujaratlist
        ], 200);
    }

    public function updatecampuscorner(Request $request)
    {
        $userId    = $request->input('user_id'); 
        $gujaratId = $request->input('campuscornerid'); 

        // Fetch the existing entry
        $ekasana = DB::table('campuscorner')->where('id', $gujaratId)->first();
        if (!$ekasana) {
            return response()->json(['success' => false, 'message' => 'Campus Corner not found!'], 404);
        }

        // Old images/videos coming from frontend
        $oldImages = $request->input('oldImages', []); 
        $oldVideo  = $request->input('oldVideo', null); 

        if (!is_array($oldImages)) {
            $oldImages = json_decode($oldImages, true) ?? [];
        }

        // ✅ Start fresh with only the old images user wants to keep
        $uploadedImages = [];
        if (!empty($oldImages)) {
            foreach ($oldImages as $imgUrl) {
                $uploadedImages[] = basename($imgUrl); // keep only filename
            }
        }

        // ✅ Keep old video if user passed it
        $uploadedVideo = null;
        if ($oldVideo && str_contains($oldVideo, '/')) {
            $uploadedVideo = basename($oldVideo); // keep filename only
        }

        $mediaPath = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/backend/public/uploads/campuscorner';
        $mediaPathSlash = $mediaPath . '/';

        // ✅ Handle new images (append to old ones)
        if ($request->hasFile('uploadimage')) {
            foreach ($request->file('uploadimage') as $file) {
                if ($file->isValid()) {
                    // Save as .webp
                    $filenameWebpcampus = time() . '_' . pathinfo(str_replace(' ', '-', $file->getClientOriginalName()), PATHINFO_FILENAME);
                    $filenameWebp = $filenameWebpcampus . '.webp';
                    $destinationWebp = $mediaPathSlash . $filenameWebp;

                    $this->compressImageToWebp($file->getRealPath(), $destinationWebp, 80);
                    $uploadedImages[] = $filenameWebp;

                    // Create 200px PNG
                    $filenamePng = $filenameWebpcampus . '_200.png';
                    $destinationPng = $mediaPathSlash . $filenamePng;

                    $this->createResizedPng($file->getRealPath(), $destinationPng, 200);
                }
            }
        }

        // ✅ Handle video (single string only)
        if ($request->hasFile('uploadvideo')) {
            $video = $request->file('uploadvideo');

            if ($video->getSize() > 25 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'Video must not exceed 25MB']);
            }

            $videoName = time() . '_' . str_replace(' ', '-', $video->getClientOriginalName());
            $videoPath = $mediaPathSlash . $videoName;

            $video->move($mediaPath, $videoName);
            $uploadedVideo = $videoName; // replace old video

            // ✅ Generate WebP preview (1 second)
            $gifName = pathinfo($videoName, PATHINFO_FILENAME) . '.webp';
            $gifPath = $mediaPathSlash . $gifName;

            try {
                $cmdWebp = "/usr/bin/ffmpeg -y -ss 0 -t 1 -i " . escapeshellarg($videoPath) . " -vf 'scale=480:-1,fps=10' -loop 0 -an -vsync 0 " . escapeshellarg($gifPath);
                shell_exec($cmdWebp);
            } catch (\Exception $e) {
                \Log::error("WebP generation failed: " . $e->getMessage());
            }
        }

        // ✅ Update DB
        $updateData = [
            'name'        => $request->input('name'),
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'city'        => $request->input('city'),
            'school'        => $request->input('school'),
            'agree_status'=> $request->input('agree_status'),
            'featureImage'=> json_encode($uploadedImages),
            'video'       => $uploadedVideo,
            'status'      => 'Inactive',
            'updated_at'  => now()
        ];

        DB::table('campuscorner')->where('id', $gujaratId)->update($updateData);

        // ✅ Prepare response
        $responseData = $updateData;
        $responseData['featureImage'] = array_map(function ($img) {
            return config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $img;
        }, $uploadedImages);

        $responseData['video'] = $uploadedVideo 
            ? config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $uploadedVideo 
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Updated successfully',
            'data'    => $responseData
        ], 200);
    }

   
    public function campuscornerlist(Request $request)
    {
        $perPage = 4;
        $pageNumber = $request->input('pageNumber', 1);
        $minutes = 30;

        // Redis cache key
        $newsKey = "campuscorner_pageapp_{$pageNumber}_" . config('app.webtype');

        if (Redis::exists($newsKey)) {
            // ✅ Load from cache
            $newsData = json_decode(Redis::get($newsKey));

            $gujaratlist = new \Illuminate\Pagination\LengthAwarePaginator(
                collect($newsData->data),   // convert back to collection
                $newsData->total,
                $newsData->per_page,
                $newsData->current_page,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            // ✅ Query DB
            $gujaratlist = DB::table('campuscorner')
                ->where('status', 'Active')
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            // ✅ Transform images & videos
            $gujaratlist->getCollection()->transform(function ($row) {
                // Multiple images (JSON array)
                if (!empty($row->featureImage)) {
                    $images = json_decode($row->featureImage, true);
                    if (is_array($images)) {
                        $row->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $img;
                        }, $images);
                    } else {
                        $row->featureImage = [];
                    }
                } else {
                    $row->featureImage = [];
                }

                // Video
                if (!empty($row->video)) {
                    $row->video = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $row->video;

                    // Generate same name with .webp
                    $videoFilename = pathinfo($row->video, PATHINFO_FILENAME) . '.webp';
                    $row->video_img = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $videoFilename;
                } else {
                    $row->video_img = null;
                }

                return $row;
            });

            // ✅ Save to Redis
            Redis::setex($newsKey, $minutes * 60, json_encode([
                'data'         => $gujaratlist->items(),
                'total'        => $gujaratlist->total(),
                'per_page'     => $gujaratlist->perPage(),
                'current_page' => $gujaratlist->currentPage(),
            ]));
        }

        return response()->json([
            'success' => true,
            'data'    => $gujaratlist
        ], 200);
    }

    public function campuscornerdetails(Request $request)
    {
        $campuscornerid = $request->input('campuscornerid'); 
       
        $minutes = 30;

        // Redis cache key (id-based + app type)
        $gujaratKey = "campuscornerdetailapp1_{$campuscornerid}_" . config('app.webtype');

        if (Redis::exists($gujaratKey)) {
            // Get from Redis (decode as object, not array)
            $gujaratdetail = json_decode(Redis::get($gujaratKey));
        } else {
            // Query DB for single record
            $gujaratdetail = DB::table('campuscorner')
                ->where('status', 'Active')
                ->where('id', $campuscornerid)
                ->first();
                
            if ($gujaratdetail) {
                // Handle multiple images
                if ($gujaratdetail->featureImage) {
                    $images = json_decode($gujaratdetail->featureImage, true); // decode array from DB
                    if (is_array($images)) {
                        $gujaratdetail->featureImage = array_map(function ($img) {
                            return config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $img;
                        }, $images);
                    } else {
                        $gujaratdetail->featureImage = [];
                    }
                } else {
                    $gujaratdetail->featureImage = [];
                }

                // Add full URL for video
                if ($gujaratdetail->video) {
                    $gujaratdetail->video = config('app.backendnewsurl') . '/public/uploads/campuscorner/' . $gujaratdetail->video;
                }
            }
            // Save to Redis if found
            if ($gujaratdetail) {
                Redis::setex($gujaratKey, $minutes * 60, json_encode($gujaratdetail));
            }
        } 
        $record = DB::table('campuscorner')
            ->where('status', 'Active')
            ->where('id', '<', $campuscornerid)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();
          return response()->json([
            'success' => true,
            'data' => $gujaratdetail,
            'last_id' => $record ? $record->id : null,
        ], 200);            
    }
}
