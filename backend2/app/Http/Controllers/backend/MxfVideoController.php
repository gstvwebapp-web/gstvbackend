<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\MxfVideo;
use App\Jobs\ProcessMxfVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Route;
use Carbon\Carbon;

class MxfVideoController extends Controller
{
    // Get all MXF Videos
    public function index()
    {
        return view('mxf-videos.alllist');
    }

    // New MXF Video Add
    public function add()
    {
        // Fetch categories (parent categories only)
        $category = DB::table('categories')
            ->where('status', 'Active')
            ->where(function($query) {
                $query->where('parentID', 0)
                      ->orWhereRaw('parentID = id');
            })
            ->orderBy('catOrder', 'ASC')
            ->get();
        
        return view('mxf-videos.add', ['category' => $category]);
    }

    public function store(Request $request)
    {
        // Validation rules and custom messages
        $rules = [
            'title' => 'nullable|string|max:255',
            'av_video' => 'required|file|mimes:mxf,mp4,mov,avi|max:2048000', // 2GB max
            'ab_videos.*' => 'nullable|file|mimes:mxf,mp4,mov,avi|max:2048000',
            'one_to_one_video' => 'nullable|file|mimes:mxf,mp4,mov,avi|max:2048000',
            'walk_through_video' => 'nullable|file|mimes:mxf,mp4,mov,avi|max:2048000',
            'document_file' => 'nullable|file|mimes:doc,docx,txt,pdf|max:10240', // 10MB
            'document_text' => 'nullable|string',
            'voice_id' => 'required|string',
        ];

        $customMessages = [
            'av_video.required' => 'The AV video file is required.',
            'av_video.mimes' => 'Only MXF, MP4, MOV, and AVI video formats are allowed.',
            'av_video.max' => 'The video size must not exceed 2 GB.',
            'voice_id.required' => 'Please select a voice for voice-over.',
        ];

        $request->validate($rules, $customMessages);

        try {
            $data = [];
            $timestamp = time();
            
            // Create year/month/date folder structure
            $now = Carbon::now();
            $year = $now->format('Y');
            $month = $now->format('m');
            $date = $now->format('d');
            
            $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/';
            
            // Create directory if it doesn't exist
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Upload AV video (required)
            if ($files = $request->file('av_video')) {
                $avVideo = 'AV_'.$timestamp.'.'.$files->extension();
                $files->move($destinationPath, $avVideo);
                // Store full URL for display/access
                $data['av_video'] = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$avVideo;
            }

            // Upload multiple AB videos
            if ($request->hasFile('ab_videos')) {
                $abVideos = [];
                foreach ($request->file('ab_videos') as $index => $file) {
                    $abVideo = 'AB_'.($index + 1).'_'.$timestamp.'.'.$file->extension();
                    $file->move($destinationPath, $abVideo);
                    // Store full URL for display/access
                    $abVideos[] = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$abVideo;
                }
                $data['ab_videos'] = $abVideos;
            }

            // Upload 121 video
            if ($files = $request->file('one_to_one_video')) {
                $oneToOneVideo = '121_'.$timestamp.'.'.$files->extension();
                $files->move($destinationPath, $oneToOneVideo);
                // Store full URL for display/access
                $data['one_to_one_video'] = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$oneToOneVideo;
            }

            // Upload WT video
            if ($files = $request->file('walk_through_video')) {
                $wtVideo = 'WT_'.$timestamp.'.'.$files->extension();
                $files->move($destinationPath, $wtVideo);
                // Store full URL for display/access
                $data['walk_through_video'] = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$wtVideo;
            }

            // Handle document
            if ($files = $request->file('document_file')) {
                $docFile = 'DOC_'.$timestamp.'.'.$files->extension();
                $files->move($destinationPath, $docFile);
                // Store full URL for display/access
                $docPath = 'https://staging.gstv.in/backend/public/uploads/mxf-videos/'.$year.'/'.$month.'/'.$date.'/'.$docFile;
                $data['document_file'] = $docPath;
                
                // Extract text from document if it's a txt file
                if ($files->extension() === 'txt') {
                    $data['document_text'] = file_get_contents($destinationPath . $docFile);
                }
            }
            
            if ($request->filled('document_text')) {
                $data['document_text'] = $request->input('document_text');
            }

            // Other fields
            $data['title'] = $request->get('title') ?? 'MXF Video ' . date('Y-m-d H:i:s');
            $data['category_id'] = $request->get('category_id');
            $data['is_jacket'] = $request->get('is_jacket') ? 1 : 0;
            $data['voice_id'] = $request->get('voice_id');
            $data['status'] = 'pending';
            
            // Handle social media platforms selection
            $socialMedia = $request->get('social_media', []);
            $data['social_media_platforms'] = is_array($socialMedia) ? $socialMedia : [];

            // Create record
            $mxfVideo = MxfVideo::create($data);

            // Log the action
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'MXF Video added: ' . $data['title'],
                'action' => 'Add',
                'contents' => json_encode($request->all())
            ]);

            // Dispatch job for video processing
            ProcessMxfVideo::dispatch($mxfVideo->id);

            return redirect('/mxf-videos/')->with('success', 'MXF Video has been uploaded successfully. Processing has started.');

        } catch (\Exception $e) {
            Log::error('MXF Video Upload Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload video: ' . $e->getMessage())->withInput();
        }
    }

    // Edit MXF Video
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $mxfvideo = MxfVideo::where('id', $id)->first();
        
        // Fetch categories (parent categories only)
        $category = DB::table('categories')
            ->where('status', 'Active')
            ->where(function($query) {
                $query->where('parentID', 0)
                      ->orWhereRaw('parentID = id');
            })
            ->orderBy('catOrder', 'ASC')
            ->get();
        
        $data = array();
        $data['mxfvideo'] = $mxfvideo;
        $data['category'] = $category;
        return view('mxf-videos.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $mxfvideo = MxfVideo::find($id);

        if (!$mxfvideo) {
            return redirect('/mxf-videos/')->with('error', 'MXF Video not found');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'voice_id' => 'required|string',
            'document_text' => 'nullable|string',
        ]);

        try {
            $mxfvideo->title = $request->get('title') ?? $mxfvideo->title;
            $mxfvideo->category_id = $request->get('category_id');
            $mxfvideo->is_jacket = $request->get('is_jacket') ? 1 : 0;
            $mxfvideo->voice_id = $request->get('voice_id');
            $mxfvideo->document_text = $request->get('document_text') ?? $mxfvideo->document_text;
            
            // Handle social media platforms selection
            $socialMedia = $request->get('social_media', []);
            $mxfvideo->social_media_platforms = is_array($socialMedia) ? $socialMedia : [];
            
            $mxfvideo->save();

            // Log the action
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'MXF Video updated: ' . $mxfvideo->title,
                'action' => 'Update',
                'contents' => json_encode($request->all())
            ]);

            return redirect('/mxf-videos/')->with('success', 'MXF Video has been updated successfully');

        } catch (\Exception $e) {
            Log::error('MXF Video Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update video: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        
        try {
            $mxfvideo = MxfVideo::find($id);
            
            if (!$mxfvideo) {
                return response()->json([
                    'status' => 0,
                    'message' => 'MXF Video not found'
                ]);
            }

            // Delete all associated files
            $this->deleteVideoFiles($mxfvideo);

            // Log the action
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'MXF Video deleted: ' . $mxfvideo->title,
                'action' => 'Delete',
                'contents' => json_encode($mxfvideo->toArray())
            ]);

            $mxfvideo->delete();

            return response()->json([
                'status' => 1,
                'message' => 'MXF Video has been deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('MXF Video Delete Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Failed to delete video: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        
        $mxfvideo = MxfVideo::find($id);
        
        if (!$mxfvideo) {
            return redirect('/mxf-videos/')->with('error', 'MXF Video not found');
        }
        
        $mxfvideo->status = $status;
        $mxfvideo->save();
        
        // Log the action
        \App\Models\Log::create([
            'userID' => Auth::user()->id,
            'message' => 'MXF Video status changed to ' . $status . ': ' . $mxfvideo->title,
            'action' => 'Status Update',
            'contents' => json_encode(['id' => $id, 'status' => $status])
        ]);
        
        return redirect('/mxf-videos/')->with('success', 'Status updated successfully');
    }

    public function mxfvideosfetchData(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'title',
            2 => 'status',
            3 => 'progress',
            4 => 'created_at',
        );

        $totalData = MxfVideo::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            $mxfvideos = MxfVideo::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');

            $mxfvideos = MxfVideo::where('id', 'LIKE', "%{$search}%")
                ->orWhere('title', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = MxfVideo::where('id', 'LIKE', "%{$search}%")
                ->orWhere('title', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = array();
        if (!empty($mxfvideos)) {
            foreach ($mxfvideos as $mxfvideo) {
                $nestedData['id'] = $mxfvideo->id;
                $nestedData['title'] = $mxfvideo->title;
                $nestedData['status'] = $mxfvideo->status;
                $nestedData['progress'] = $mxfvideo->progress . '%';
                $nestedData['created_at'] = date('d-m-Y H:i', strtotime($mxfvideo->created_at));
                $nestedData['options'] = '<a href="/mxf-videos/edit/'.$mxfvideo->id.'" class="btn btn-sm btn-primary">Edit</a>';
                $data[] = $nestedData;
            }
        }

        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        );

        echo json_encode($json_data);
    }

    /**
     * Reprocess video
     */
    public function reprocess()
    {
        $id = Route::current()->parameter('id');
        
        try {
            $mxfvideo = MxfVideo::find($id);
            
            if (!$mxfvideo) {
                return redirect('/mxf-videos/')->with('error', 'MXF Video not found');
            }
            
            // If stuck at 97%, force complete it
            if ($mxfvideo->progress == 97 && $mxfvideo->status == 'processing') {
                Log::info("🔧 Force completing stuck video at 97%: ID {$id}");
                
                $mxfvideo->status = 'completed';
                $mxfvideo->progress = 100;
                $mxfvideo->save();
                
                Log::info("✅ Video force completed successfully");
                
                return redirect('/mxf-videos/')->with('success', 'Video has been force completed successfully.');
            }
            
            // Reset status
            $mxfvideo->status = 'pending';
            $mxfvideo->progress = 0;
            $mxfvideo->error_message = null;
            $mxfvideo->save();

            // Dispatch processing job
            ProcessMxfVideo::dispatch($mxfvideo->id);

            // Log the action
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'MXF Video reprocessing started: ' . $mxfvideo->title,
                'action' => 'Reprocess',
                'contents' => json_encode(['id' => $id])
            ]);

            return redirect('/mxf-videos/')->with('success', 'Video reprocessing started.');

        } catch (\Exception $e) {
            Log::error('MXF Video Reprocess Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reprocess video: ' . $e->getMessage());
        }
    }

    /**
     * Get processing status (for AJAX polling)
     */
    public function status()
    {
        $id = Route::current()->parameter('id');
        $mxfvideo = MxfVideo::find($id);
        
        if (!$mxfvideo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Video not found'
            ]);
        }
        
        return response()->json([
            'status' => $mxfvideo->status,
            'progress' => $mxfvideo->progress,
            'error_message' => $mxfvideo->error_message,
            'final_video' => $mxfvideo->final_video ? $mxfvideo->final_video : null,
        ]);
    }

    /**
     * Share video on social media platforms
     */
    public function share(Request $request)
    {
        $id = $request->get('id');
        $mxfvideo = MxfVideo::find($id);

        if (!$mxfvideo) {
            return response()->json(['success' => false, 'message' => 'MXF Video not found'], 404);
        }

        if (!$mxfvideo->final_video) {
            return response()->json(['success' => false, 'message' => 'Video not processed yet'], 400);
        }

        if (empty($mxfvideo->social_media_platforms)) {
            return response()->json(['success' => false, 'message' => 'No social media platforms selected'], 400);
        }

        try {
            $videoUrl = url('uploads/' . $mxfvideo->final_video);
            $title = $mxfvideo->title;
            $shareResults = [];

            foreach ($mxfvideo->social_media_platforms as $platform) {
                switch ($platform) {
                    case 'facebook':
                        $shareUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($videoUrl);
                        $shareResults['facebook'] = $shareUrl;
                        break;

                    case 'twitter':
                        $shareUrl = "https://twitter.com/intent/tweet?url=" . urlencode($videoUrl) . "&text=" . urlencode($title);
                        $shareResults['twitter'] = $shareUrl;
                        break;

                    case 'youtube':
                        $shareResults['youtube'] = 'https://www.youtube.com/upload';
                        break;

                    case 'instagram':
                        $shareResults['instagram'] = 'manual_upload_required';
                        break;
                }
            }

            // Mark as shared
            $mxfvideo->is_shared = 1;
            $mxfvideo->shared_at = now();
            $mxfvideo->save();

            // Log the action
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'MXF Video shared on: ' . implode(', ', $mxfvideo->social_media_platforms),
                'action' => 'Share',
                'contents' => json_encode(['id' => $id, 'platforms' => $mxfvideo->social_media_platforms])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video sharing links generated successfully',
                'share_urls' => $shareResults,
                'video_url' => $videoUrl
            ]);

        } catch (\Exception $e) {
            Log::error('MXF Video Share Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to share video: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete all video files associated with MXF video
     */
    private function deleteVideoFiles($video)
    {
        $files = [
            $video->av_video,
            $video->one_to_one_video,
            $video->walk_through_video,
            $video->document_file,
            $video->voice_over_audio,
            $video->final_video,
        ];

        // Add AB videos
        if (!empty($video->ab_videos)) {
            $files = array_merge($files, $video->ab_videos);
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($files as $file) {
            if ($file) {
                // Convert URL to local path
                // From: https://staging.gstv.in/backend/public/uploads/...
                // To: /home/gstv-staging/htdocs/staging.gstv.in/public/backend/public/uploads/...
                
                if (str_starts_with($file, 'http')) {
                    // Extract path from URL
                    $urlPath = parse_url($file, PHP_URL_PATH);
                    
                    // Get document root and construct full path
                    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
                    $fullPath = rtrim($documentRoot, '/') . $urlPath;
                } else {
                    // Already a local path
                    $fullPath = $file;
                }
                
                if (File::exists($fullPath)) {
                    try {
                        File::delete($fullPath);
                        $deletedCount++;
                        Log::info("🗑️ Deleted file: " . basename($fullPath));
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("❌ Failed to delete file: " . basename($fullPath) . " - " . $e->getMessage());
                    }
                } else {
                    Log::warning("⚠️ File not found for deletion: " . basename($fullPath));
                }
            }
        }

        Log::info("✅ File deletion complete: {$deletedCount} deleted, {$failedCount} failed");
    }
}
