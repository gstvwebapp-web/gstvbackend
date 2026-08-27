<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Epaper;
use App\Models\Epapercategory;
use Illuminate\Support\Facades\Auth;
use Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use File;
use Image;
use Illuminate\Support\Facades\Redis;
use Imagick;
use App\Services\FirebaseService;

class EpaperController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    // Get all Categories
    public function index()
    {
        return view('epaper.alllist');
        $epaper = DB::table('epaper')
        ->select('epaper.*', 'u1.name as authorName')
        ->selectRaw('(SELECT title FROM epapercategory WHERE FIND_IN_SET(epapercategory.id, epaper.ecatID)) as categoryNames') 
        ->leftJoin('users as u1', 'epaper.userID', '=', 'u1.id')
        ->orderBy('created_at', 'DESC')
        ->get();   
        $data =  array();
        $data['epaper'] =  $epaper;
        return view('epaper.list', compact('data'));
    }

    // New Category Add
    public function add()
    {
        $epapercategory = DB::table('epapercategory')->where('status', 'Active')->get();
        $data =  array();
        $data['epapercategory'] =  $epapercategory;
        return view('epaper.add', compact('data'));
    }
    public function store(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 1200);

        $request->validate([
            'etitle'    => 'required',
            'engtitle'  => 'required',
            'metatitle' => 'required'
        ]);

        $jsonDataArray = [];

        $year  = date('Y');
        $month = date('m');
        $day   = date('d');

        // PATHS UNCHANGED
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) .
            "/backend/public/uploads/epaper/$year/$month/$day/";

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $path = config('app.backendnewsurl') .
            "/public/uploads/epaper/$year/$month/$day";

        $randomCode = Str::random(8) . time();
        $pdfname = "";

        /* ---------------- PDF PROCESSING (SAFE MODE) ---------------- */
        if (isset($_FILES['pdf']['name']) && $_FILES['pdf']['error'] == 0) {

            $pdf = $_FILES['pdf'];

            if (is_uploaded_file($pdf['tmp_name'])) {

                $pdfname = $destinationPath .
                    pathinfo($pdf['name'], PATHINFO_FILENAME) .
                    '_' . $randomCode . '.pdf';

                move_uploaded_file($pdf['tmp_name'], $pdfname);

                // Page count (lightweight)
                $ping = new Imagick();
                $ping->pingImage($pdfname);
                $pageCount = $ping->getNumberImages();
                $ping->clear();
                $ping->destroy();

                for ($i = 0; $i < $pageCount; $i++) {

                    $imagick = new Imagick();

                    // SAFE SETTINGS (NO PNM ERROR)
                    $imagick->setResolution(170, 170);
                    $imagick->setColorspace(Imagick::COLORSPACE_RGB);

                    // Read only one page
                    $imagick->readImage($pdfname . '[' . $i . ']');
                    $imagick->setIteratorIndex(0);

                    // Set white background
                    $imagick->setImageBackgroundColor('white');

                    // Remove transparency and flatten onto white background
                    $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                    // Clean heavy data
                    $imagick->stripImage();

                    // Output
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(90);

                    // Size (safe)
                    $imagick->resizeImage(2000, 0, Imagick::FILTER_LANCZOS, 1);

                    $fileName = 'page-' . $randomCode . '-' . ($i + 1) . '.webp';
                    $imagick->writeImage($destinationPath . $fileName);

                    $jsonDataArray[] = [
                        'image' => $path . '/' . $fileName
                    ];
					/* ---------------- JPG THUMBNAIL (ONLY FIRST PAGE) ---------------- */
                    if ($i == 0) {

                        $thumb = new Imagick();
                        $thumb->readImage($pdfname . '[0]');
                        $thumb->setIteratorIndex(0);
                        $thumb->stripImage();

                        $thumb->setImageFormat('jpeg');
                        $thumb->setImageCompressionQuality(85);

                        // Small thumbnail size for WhatsApp preview
                        $thumb->resizeImage(1200, 630, Imagick::FILTER_LANCZOS, 1,true);

                        $thumbName = 'thumb-' . $randomCode . '.jpg';
                        $thumb->writeImage($destinationPath . $thumbName);

                        // 👉 Save thumbnail path in DB (VERY IMPORTANT)
                        $thumbnailPath = $path . '/' . $thumbName;

                        $thumb->clear();
                        $thumb->destroy();
                    }
                    $imagick->clear();
                    $imagick->destroy();
                }
            }
        }

        /* ---------------- DATABASE ---------------- */
        $epaper = new Epaper([
            'ecatID'        => $request->get('ecatID'),
            'etitle'        => $request->get('etitle'),
            'engtitle'      => $request->get('engtitle'),
            'slug'          => Str::slug($request->get('engtitle')),
            'Story_data'    => json_encode($jsonDataArray),
            'pdf'           => $pdfname,
            'thumbnail'     => $thumbnailPath ?? null,
            'userID'        => Auth::user()->id,
            'newspaperdate' => $request->get('newspaperdate'),
            'notification'  => $request->has('notification') ? 1 : 0,
            'metatitle'     => $request->get('metatitle'),
            'metakeyword'   => $request->get('metakeyword'),
            'metadesc'      => $request->get('metadesc')
        ]);

        $epaper->save();

        return redirect('/epaper/')->with('success', 'Epaper Category has been added');
    }

    public function store_old(Request $request)
    {
      	ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB
   		ini_set('max_execution_time', 600); // Increase max execution time to 5 minutes
        // Validation rules and custom messages
        $rules = [
            'etitle' => 'required',
            'engtitle' => 'required',
            'metatitle' => 'required',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480'
        ];

        $customMessages = [
            'etitle.required' => 'The Category title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'metatitle.required' => 'The Meta title field is required.',
            'files.image' => 'The uploaded file must be an image.',
            'files.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'files.max' => 'The image size must not exceed 2 MB.'
        ];

        $request->validate($rules, $customMessages);

        // Get the file data
        $webimgs = $_FILES['files'];
        $jsonDataArray = [];

        // Get current date details for folder structure
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/epaper/$year/$month/$day/";

        // Ensure the upload directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true); // Create directory with proper permissions
        }

        // Define the path for file access
        $path = config('app.backendnewsurl') . '/public/uploads/epaper/' . $year . '/' . $month . '/' . $day;

        // Generate a random code for filenames
        $randomCode = Str::random(8) . time();
        $pdfname = "";

        // Handle PDF file upload if exists
        if (isset($_FILES['pdf']['name']) && $_FILES['pdf']['error'] == 0) {
            $pdf = $_FILES['pdf'];
            if (is_uploaded_file($pdf['tmp_name'])) {
                // $pdfname = $path . '/' . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.pdf';
                $pdfname = $destinationPath . '/' . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.pdf';
                move_uploaded_file($pdf['tmp_name'], $destinationPath . '/' . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.pdf');
                        $tempImagick = new Imagick();
                        $tempImagick->pingImage($pdfname); // Only pings, not fully loads
                        $pageCount = $tempImagick->getNumberImages();
                        $tempImagick->clear();
                        $tempImagick->destroy();
                         
                         //$imagick->readImage($pdfname);
                        //$pageCount = $imagick->getNumberImages();
                        // Loop through each page in the PDF
                        for ($i = 0; $i < $pageCount; $i++) {
                            $imagick = new Imagick(); 
                            $imagick->setResolution(300, 300); // or 350 for very high quality
                            $imagick->readImage($pdfname."[$i]");
                            //$imagick->readImage($pdfname . '[0]');
                            $imagick->setImageCompressionQuality(100); // Max quality
                            $imagick->setIteratorIndex(0); // Set the iterator to the current page
                            $page = $imagick->getImage(); // Get the specific page
                            $page->setImageFormat('webp'); // Set the image format to WEBP
                            $page->resizeImage(1920, 0, Imagick::FILTER_LANCZOS, 1); // Resize to 1920px width while maintaining aspect ratio
                    		$page->unsharpMaskImage(1, 0.5, 1, 0.05);
                            // Generate the output filename and path
                            $outputFilename = 'page-' . $randomCode . '-' . ($i + 1) . '.webp';
                            $outputPath = $destinationPath . '/' . $outputFilename;
                    		//$page->setImageCompressionQuality(95);
                            // Save the image
                            $page->writeImage($outputPath);
                    
                            // Add the generated image to the array
                            $fullpath = $path . '/' . $outputFilename;
                            $jsonDataArray[] = ['image' => $fullpath];
                    
                            // Clear the current page to free memory
                            $page->clear();
                            $page->destroy();
                        }
                        $imagick->clear();
                        $imagick->destroy();
                    
              } else {
                  Log::error('PDF file upload failed.');
              }
        }

        // Handle image uploads
        if (isset($webimgs['name'])) {
            for ($i = 0; $i < count($webimgs['name']); $i++) {
                $fileTmpPath = $webimgs['tmp_name'][$i];
                $filename = $webimgs['name'][$i];

                // Skip if the file is not uploaded correctly
                if (!is_uploaded_file($fileTmpPath)) {
                    Log::error("Image file upload failed for: " . $filename);
                    continue;
                }

                // Handle image file upload
                $filesdata = new \Illuminate\Http\UploadedFile($fileTmpPath, $filename, null, null, false, true);
                $filesdata->move($destinationPath, $filename);

                // Process and convert image to WebP format
                $imgname = pathinfo($filename, PATHINFO_FILENAME);
                $img = Image::make($destinationPath . $filename);

                // Generate WebP version
                $webpimg = $imgname . '_' . $randomCode . '.webp';
                $webpPath = $destinationPath . $webpimg;
                $img->save($webpPath, 100);

                // Resize image for thumbnail
                $img->resize(350, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $smallImgPath = $destinationPath . $imgname . '_' . $randomCode . '_small.webp';
                $img->save($smallImgPath, 100);

                // Store the full path to the image
                $fullpath = $path . '/' . $webpimg;
                $item = ['image' => $fullpath];

                $jsonDataArray[] = $item;
            }
        }

        // Encode image data to JSON
        $jsonEncodedData = json_encode($jsonDataArray);

        // Store the data in the database
        $epaper = new Epaper([
            'ecatID' => $request->get('ecatID'),
            'etitle' => $request->get('etitle'),
            'engtitle' => $request->get('engtitle'),
            'slug' => Str::slug($request->get('engtitle'), '-'),
            'Story_data' => $jsonEncodedData, // Store as JSON string
            'pdf' => $pdfname,
            'userID' => Auth::user()->id,
            'newspaperdate' => $request->get('newspaperdate'),
            'notification' => $request->has('notification') ? 1 : 0,
            'metatitle' => $request->get('metatitle'),
            'metakeyword' => $request->get('metakeyword'),
            'metadesc' => $request->get('metadesc')
        ]);
        $epaper->save();
        $epaper->notification = $request->has('notification') ? 1 : 0;
        $epaper->update();
        
        $adsCat = DB::table('epapercategory')
        ->select('slug')
        ->where('id', $request->get('ecatID'))
        ->first();


        $epaperurl = config('app.front_url').'/epaper/'.$adsCat->slug.'/'.date('d-m-Y',strtotime($request->get('newspaperdate')));
        $epaperimage = json_decode($jsonEncodedData);
        $imageUrl = $epaperimage[0]->image;
        $newFeatureImage = preg_replace('/(\.webp)$/', '_small$1', $imageUrl);

        if($request->has('notification') == 1){
            $notification = DB::table('notification')->get();
                $type = 'epaper';
                $topic = 'epaper'; // Define your topic name
                $body = $request->get('engtitle');
                    $title = $request->get('etitle').' Epaper';
                    $body = $request->get('etitle');
                    $response = $this->firebaseService->sendNotification($title, $body, $topic,$type,$newFeatureImage,$epaperurl,$adsCat->slug);
        }
        // Log the action
        \App\Models\Log::create([
            'userID' => Auth::user()->id,
            'message' => 'Epaper Category added: ' . $request->get('etitle'),
            'action' => 'Add',
            'contents' => json_encode($request->all())
        ]);

        return redirect('/epaper/')->with('success', 'Epaper Category has been added');
    }

    
    
    // Update Category
    public function edit()
    {
        $id = Route::current()->parameter('id');
        $epaper = Epaper::where('id', $id)->first();
        
        $epapertitle = DB::table('epapercategory')->select('cattype')->where('id', $epaper->ecatID)->first();
        $epapercategory = DB::table('epapercategory')->where('status', 'Active')->where('cattype',$epapertitle->cattype)->get();
        $data =  array();
        $data['epaper'] =  $epaper;
        $data['epapertitle'] =  $epapertitle->cattype;
        $data['epapercategory'] =  $epapercategory;
        return view('epaper.edit', compact('data'));
    }
    
public function update(Request $request)
 {
      	ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB
   		ini_set('max_execution_time', 600); // Increase max execution time to 5 minutes
        $rules = [
            'etitle' => 'required',
            'engtitle' => 'required',
            'metatitle'=>'required',
        ];

        $customMessages = [
            'etitle.required' => 'The Category title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
		$jsonDataArray = [];
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/epaper/$year/$month/$day/";
        $randomCode = Str::random(8).time();
        $path = config('app.backendnewsurl').'/public/uploads/epaper/'.$year.'/'.$month.'/'.$day;
        $pdfname= "";
        if ($_FILES['pdf']['name'] != "") {

            $pdf = $_FILES['pdf'];
            $pdfname = $destinationPath .
                pathinfo($pdf['name'], PATHINFO_FILENAME) .
                '_' . $randomCode . '.pdf';

            $filesdata = new \Illuminate\Http\UploadedFile(
                $pdf['tmp_name'],
                $pdf['name'],
                null,
                null,
                false,
                true
            );
            $filesdata->move($destinationPath, $pdfname);

            try {

                // ✅ SAFE PAGE COUNT
                $ping = new Imagick();
                $ping->pingImage($pdfname);
                $pageCount = $ping->getNumberImages();
                $ping->clear();
                $ping->destroy();

                for ($i = 0; $i < $pageCount; $i++) {

                    $imagick = new Imagick();

                    // ✅ SAFE SETTINGS (SAME AS STORE)
                    $imagick->setResolution(170, 170);
                    $imagick->setColorspace(Imagick::COLORSPACE_RGB);

                    // Read single page
                    $imagick->readImage($pdfname . '[' . $i . ']');
                    $imagick->setIteratorIndex(0);
                    // Set white background
                    $imagick->setImageBackgroundColor('white');

                    // Remove transparency and flatten onto white background
                    $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                    // Remove heavy metadata
                    $imagick->stripImage();

                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(90);

                    // Resize safely
                    $imagick->resizeImage(2000, 0, Imagick::FILTER_LANCZOS, 1);

                    $outputFilename = 'page-' . $randomCode . '-' . ($i + 1) . '.webp';
                    $imagick->writeImage($destinationPath . $outputFilename);

                    $jsonDataArray[] = [
                        'image' => $path . '/' . $outputFilename
                    ];
					if ($i == 0) {

                        $thumb = new Imagick();
                        $thumb->readImage($pdfname . '[0]');
                        $thumb->setIteratorIndex(0);
                        $thumb->stripImage();

                        $thumb->setImageFormat('jpeg');
                        $thumb->setImageCompressionQuality(85);

                        // Small thumbnail size for WhatsApp preview
                        $thumb->resizeImage(1200, 630, Imagick::FILTER_LANCZOS, 1,true);

                        $thumbName = 'thumb-' . $randomCode . '.jpg';
                        $thumb->writeImage($destinationPath . $thumbName);

                        // 👉 Save thumbnail path in DB (VERY IMPORTANT)
                        $thumbnailPath = $path . '/' . $thumbName;

                        $thumb->clear();
                        $thumb->destroy();
                    }
                    $imagick->clear();
                    $imagick->destroy();
                }

            } catch (\Exception $e) {
                Log::error('Update_old PDF Error: ' . $e->getMessage());
            }
        } 
      
        if($_FILES['pdf']['name'] == "" && $request->get('oldpdf') != ""){
            $pdfname = $request->get('oldpdf');
        }
     	 
        // $iconImg = '';
        $webimgs = '';
        $oldwebimgs = [];
        if($_FILES['files']['name'][0]!='') {  
        	$webimgs = $_FILES['files'];
        }
        else if (isset($_POST['exitsfiles'])) {
        	$oldwebimgs = $_POST['exitsfiles']; 
        }
        $allFiles = $oldwebimgs;
		  
        if (!empty($webimgs['name'][0])) {
            for ($i = 0; $i < count($webimgs['name']); $i++) {
                
                $fileTmpPath = $webimgs['tmp_name'][$i];
                $filename = $webimgs['name'][$i];
                
                //$destinationPath = public_path('/webstory/'); // upload path
                $imgname = str_replace(' ', '-', $filename);
                $lastDotPosition = strrpos($imgname, '.');
                if ($lastDotPosition !== false) {
                    $extension = substr($imgname, $lastDotPosition + 1);
                    $filenameWithoutExtension = substr($imgname, 0, $lastDotPosition);
                }

                $featureImage = pathinfo($filename, PATHINFO_EXTENSION);
                $imgname = pathinfo($filenameWithoutExtension, PATHINFO_FILENAME);
            
                // Create a new UploadedFile instance
                $filesdata = new \Illuminate\Http\UploadedFile($fileTmpPath, $filename, null, null, false, true);
            
                $filesdata->move($destinationPath, $featureImage);
            
                $img = Image::make($destinationPath . $featureImage);
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.webp';
                $webpPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.webp';
                $img->save($webpPath, 100);

                $img->resize(350, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $smallImgPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'_small.'.$extension;
                $img->save($smallImgPath, 100);
                $fullpath = $path.'/'.$webpimg;
                $allFiles[] = $fullpath;

            }
        }
 
	 if(!empty($allFiles)){
       foreach ($allFiles as $index => $img) {
        $jsonDataArray[] = [
          'image' => $img,
        ];
      }
     }
     
        $jsonDataArray = array_filter($jsonDataArray, function($item) {
            return !empty($item['image']);
        });
        
        // Reindex the array if necessary
        $jsonDataArray = array_values($jsonDataArray);
      
        $jsonEncodedData = json_encode($jsonDataArray);
        $epaper = Epaper::find($request->get('eid'));
        $epaper->ecatID = $request->get('ecatID');
        $epaper->etitle = $request->get('etitle');
        $epaper->engtitle = $request->get('engtitle');
        $epaper->newspaperdate = $request->get('newspaperdate');
        $epaper->pdf = $pdfname;
        $epaper->thumbnail = $thumbnailPath ?? null;
        $epaper->slug = Str::slug($request->get('engtitle'), '-');
        $epaper->Story_data = $jsonEncodedData;
        $epaper->metatitle = $request->get('metatitle');
        $epaper->metakeyword = $request->get('metakeyword');
        $epaper->metadesc = $request->get('metadesc');
        $epaper->notification = $request->has('notification') ? 1 : 0;
       
        $epaper->update();
        $adsCat = DB::table('epapercategory')
        ->select('slug')
        ->where('id', $request->get('ecatID'))
        ->first();


        $epaperurl = config('app.front_url').'/epaper/'.$adsCat->slug.'/'.date('d-m-Y',strtotime($request->get('newspaperdate')));
        $epaperimage = json_decode($jsonEncodedData);
        $imageUrl = $epaperimage[0]->image;
        $newFeatureImage = preg_replace('/(\.webp)$/', '_small$1', $imageUrl);

        if($request->has('notification') == 1){
            
            $notification = DB::table('notification')->get();
           // if(!empty($notification)){
                $type = 'epaper';
                $topic = 'epaper'; // Define your topic name
                $body = $request->get('engtitle');
               // foreach($notification as $not){
                   // $token = $not->token;//'dhxPXWMVbUx7rSoLGumWzU:APA91bH59tvVu06HWpluRovsEqFMJHEeG81hX3GlVsShtsXM10gFaQpRBxbTG_VVynnbDdW3w8U8kfQ1XsaG_arEhBp-c_K5lFDpkvUYT5IWbOMxylcASFBsbQedLErPj6TWLOdMlfUR';//$not['token'];
                    $title = $request->get('etitle').' Epaper';
                    $body = $request->get('etitle');
                    
                    //$response = $this->firebaseService->sendNotification123($title, $body, $token,$type,$newFeatureImage,$epaperurl,$adsCat->slug);
                    $response = $this->firebaseService->sendNotification($title, $body, $topic,$type,$newFeatureImage,$epaperurl,$adsCat->slug);
               // }
           // }
        } 
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper Category updated: ' . $request->get('etitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/epaper/')->with('success', 'Epaper Category has been updated successfully.');
    }
    public function update_old(Request $request)
    {
      	ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB
   		ini_set('max_execution_time', 600); // Increase max execution time to 5 minutes
        $rules = [
            'etitle' => 'required',
            'engtitle' => 'required',
            'metatitle'=>'required',
        ];

        $customMessages = [
            'etitle.required' => 'The Category title field is required.',
            'engtitle.required' => 'The English title field is required.',
            'metatitle.required' => 'The Meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);
		$jsonDataArray = [];
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $destinationPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/epaper/$year/$month/$day/";
        $randomCode = Str::random(8).time();
        $path = config('app.backendnewsurl').'/public/uploads/epaper/'.$year.'/'.$month.'/'.$day;
        $pdfname= "";
        if($_FILES['pdf']['name'] != ""){
            $pdf = $_FILES['pdf'];
            // $pdfname = $path.'/'.''.pathinfo($pdf['name'], PATHINFO_EXTENSION).'_'.$randomCode.'.pdf';
            $pdfname = $destinationPath . '/' . pathinfo($pdf['name'], PATHINFO_FILENAME) . '_' . $randomCode . '.pdf';
            $filesdata = new \Illuminate\Http\UploadedFile($pdf['tmp_name'], $pdf['name'], null, null, false, true);
            $filesdata->move($destinationPath, $pdfname);
          	
          	try {
             $tempImagick = new Imagick();
                        $tempImagick->pingImage($pdfname); // Only pings, not fully loads
                        $pageCount = $tempImagick->getNumberImages();
                        $tempImagick->clear();
                        $tempImagick->destroy();
                         
                         //$imagick->readImage($pdfname);
                        //$pageCount = $imagick->getNumberImages();
                        // Loop through each page in the PDF
                        for ($i = 0; $i < $pageCount; $i++) {
                            $imagick = new Imagick(); 
                            $imagick->setResolution(600, 600); // or 350 for very high quality
                            $imagick->readImage($pdfname."[$i]");
                            //$imagick->readImage($pdfname . '[0]');
                            $imagick->setImageCompressionQuality(100); // Max quality
                            $imagick->setIteratorIndex(0); // Set the iterator to the current page
                            $page = $imagick->getImage(); // Get the specific page
                            $page->setImageFormat('webp'); // Set the image format to WEBP
                            $page->resizeImage(1920, 0, Imagick::FILTER_LANCZOS, 1); // Resize to 1920px width while maintaining aspect ratio
                    		$page->unsharpMaskImage(1, 0.5, 1, 0.05);
                            // Generate the output filename and path
                            $outputFilename = 'page-' . $randomCode . '-' . ($i + 1) . '.webp';
                            $outputPath = $destinationPath . '/' . $outputFilename;
                    		//$page->setImageCompressionQuality(95);
                            // Save the image
                            $page->writeImage($outputPath);
                    
                            // Add the generated image to the array
                            $fullpath = $path . '/' . $outputFilename;
                            $jsonDataArray[] = ['image' => $fullpath];
                    
                            // Clear the current page to free memory
                            $page->clear();
                            $page->destroy();
                        }
                $imagick->clear();
                $imagick->destroy();
            } catch (Exception $e) {
              echo 'Error: ' . $e->getMessage();
            }
          
        }   
      
        if($_FILES['pdf']['name'] == "" && $request->get('oldpdf') != ""){
            $pdfname = $request->get('oldpdf');
        }
     	 
        // $iconImg = '';
        $webimgs = '';
        $oldwebimgs = [];
        if($_FILES['files']['name'][0]!='') {  
        	$webimgs = $_FILES['files'];
        }
        else if (isset($_POST['exitsfiles'])) {
        	$oldwebimgs = $_POST['exitsfiles']; 
        }
        $allFiles = $oldwebimgs;
		  
        if (!empty($webimgs['name'][0])) {
            for ($i = 0; $i < count($webimgs['name']); $i++) {
                
                $fileTmpPath = $webimgs['tmp_name'][$i];
                $filename = $webimgs['name'][$i];
                
                //$destinationPath = public_path('/webstory/'); // upload path
                $imgname = str_replace(' ', '-', $filename);
                $lastDotPosition = strrpos($imgname, '.');
                if ($lastDotPosition !== false) {
                    $extension = substr($imgname, $lastDotPosition + 1);
                    $filenameWithoutExtension = substr($imgname, 0, $lastDotPosition);
                }

                $featureImage = pathinfo($filename, PATHINFO_EXTENSION);
                $imgname = pathinfo($filenameWithoutExtension, PATHINFO_FILENAME);
            
                // Create a new UploadedFile instance
                $filesdata = new \Illuminate\Http\UploadedFile($fileTmpPath, $filename, null, null, false, true);
            
                $filesdata->move($destinationPath, $featureImage);
            
                $img = Image::make($destinationPath . $featureImage);
                $webpimg = pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.webp';
                $webpPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'.webp';
                $img->save($webpPath, 100);

                $img->resize(350, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $smallImgPath = $destinationPath . pathinfo($imgname, PATHINFO_FILENAME).'_'.$randomCode.'_small.'.$extension;
                $img->save($smallImgPath, 100);
                $fullpath = $path.'/'.$webpimg;
                $allFiles[] = $fullpath;

            }
        }
 
	 if(!empty($allFiles)){
       foreach ($allFiles as $index => $img) {
        $jsonDataArray[] = [
          'image' => $img,
        ];
      }
     }
     
        $jsonDataArray = array_filter($jsonDataArray, function($item) {
            return !empty($item['image']);
        });
        
        // Reindex the array if necessary
        $jsonDataArray = array_values($jsonDataArray);
      
        $jsonEncodedData = json_encode($jsonDataArray);
        $epaper = Epaper::find($request->get('eid'));
        $epaper->ecatID = $request->get('ecatID');
        $epaper->etitle = $request->get('etitle');
        $epaper->engtitle = $request->get('engtitle');
        $epaper->newspaperdate = $request->get('newspaperdate');
        $epaper->pdf = $pdfname;
        $epaper->slug = Str::slug($request->get('engtitle'), '-');
        $epaper->Story_data = $jsonEncodedData;
        $epaper->metatitle = $request->get('metatitle');
        $epaper->metakeyword = $request->get('metakeyword');
        $epaper->metadesc = $request->get('metadesc');
        $epaper->notification = $request->has('notification') ? 1 : 0;
       
        $epaper->update();
        $adsCat = DB::table('epapercategory')
        ->select('slug')
        ->where('id', $request->get('ecatID'))
        ->first();


        $epaperurl = config('app.front_url').'/epaper/'.$adsCat->slug.'/'.date('d-m-Y',strtotime($request->get('newspaperdate')));
        $epaperimage = json_decode($jsonEncodedData);
        $imageUrl = $epaperimage[0]->image;
        $newFeatureImage = preg_replace('/(\.webp)$/', '_small$1', $imageUrl);

        if($request->has('notification') == 1){
            
            $notification = DB::table('notification')->get();
           // if(!empty($notification)){
                $type = 'epaper';
                $topic = 'epaper'; // Define your topic name
                $body = $request->get('engtitle');
               // foreach($notification as $not){
                   // $token = $not->token;//'dhxPXWMVbUx7rSoLGumWzU:APA91bH59tvVu06HWpluRovsEqFMJHEeG81hX3GlVsShtsXM10gFaQpRBxbTG_VVynnbDdW3w8U8kfQ1XsaG_arEhBp-c_K5lFDpkvUYT5IWbOMxylcASFBsbQedLErPj6TWLOdMlfUR';//$not['token'];
                    $title = $request->get('etitle').' Epaper';
                    $body = $request->get('etitle');
                    
                    //$response = $this->firebaseService->sendNotification123($title, $body, $token,$type,$newFeatureImage,$epaperurl,$adsCat->slug);
                    $response = $this->firebaseService->sendNotification($title, $body, $topic,$type,$newFeatureImage,$epaperurl,$adsCat->slug);
               // }
           // }
        } 
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper Category updated: ' . $request->get('etitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
        return redirect('/epaper/')->with('success', 'Epaper Category has been updated successfully.');
    }
  function deletepdfepaper(Request $request){
    $epapers = Epaper::where('id', $request->id)->first();
    $filesdata = json_decode($epapers ->Story_data);
    
    foreach($filesdata as $urls){
      $url = $urls->image;
      $path = parse_url($url, PHP_URL_PATH);
      $imagePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . $path;
      if (file_exists($imagePath)) {
        unlink($imagePath);
      }
    }
     $epaper = Epaper::find($request->get('id'));
     $epaper->Story_data = [];
     $epaper->pdf = '';
     $epaper->update();
     for ($k = 0; $k < 2000; $k++) {
        $cacheKey = 'epaperfetchData:search_' . config('app.webtype') . ':' . $k;
        Redis::del($cacheKey);
    }
     return response()->json(array('msg'=>'pdf image delted successfully'));
  }

    // Status Active/Inactive
    public function updateStatus()
    {
        $id = Route::current()->parameter('id');
        $status = Route::current()->parameter('status');
        $epapercategory = Epaper::find($id); 
        $cattitle = $epapercategory->etitle;
        $epapercategory->status = $status;
        $epapercategory->update();
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epaperfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        \App\Models\Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Epaper  published/Unpublished: ' . $cattitle,
            'action' => $status,
            'contents' => json_encode($cattitle),
        ]);
        return redirect('/epaper/')->with('success', 'Epaper Status has been updated successfully');
    }

    public function catorder(Request $request)
    {
        // Redis::del('epapercatlist');
        
        $newOrder = $request->input('newOrder');
        foreach ($newOrder as $position => $itemId) {
            $epaper = Epaper::find($itemId);
            $epaper->catOrder = $position + 1;
            $epaper->update();
        }
        for ($k = 0; $k < 2000; $k++) {
            $cacheKey = 'epaperfetchData:search_' . config('app.webtype') . ':' . $k;
            Redis::del($cacheKey);
        }
        return response()->json(['message' => 'Order updated successfully']);
    }

    public function destroy(Request $request)
    {
        $id = Route::current()->parameter('id');
        $epaper = Epaper::find($id);

        if ($epaper) {
            $title = $epaper->etitle;
            $storydata = json_decode($epaper->Story_data);

            // Extract year, month, day from created_at
            $createdDate = Carbon::parse($epaper->created_at);
            $year = $createdDate->format('Y');   // 2025
            $month = $createdDate->format('m');  // 01
            $day = $createdDate->format('d');    // 04
         

            // Base directory for epaper files
            $baseDir = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/backend/public/uploads/epaper/$year/$month/$day/";

            // Handle image deletion
            foreach ($storydata as $webdata) {
                $webimage = explode('/', $webdata->image);
                $lastElement = end($webimage);
                $filenameWithoutExt = pathinfo($lastElement, PATHINFO_FILENAME);

                $imagePath = $baseDir . $lastElement;
                $newFilename = $baseDir . $filenameWithoutExt . "_small.jpg";

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                if (file_exists($newFilename)) {
                    unlink($newFilename);
                }
            }

            // Handle PDF deletion
            if ($epaper->pdf) {
                $pdfnmurl = $epaper->pdf;
                $pdfnm = basename($pdfnmurl);
                $pdfPath = $baseDir . $pdfnm;

                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }

            // Delete epaper record
            $epaper->delete();
            for ($k = 0; $k < 2000; $k++) {
                $cacheKey = 'epaperfetchData:search_' . config('app.webtype') . ':' . $k;
                Redis::del($cacheKey);
            }
            // Log the deletion
            \App\Models\Log::create([
                'userID' => Auth::user()->id,
                'message' => 'Epaper Category deleted: ' . $title,
                'action' => 'Delete',
                'contents' => json_encode($title),
            ]);

            return redirect('/epaper/')->with('success', 'Epaper has been deleted successfully');
        }

        return redirect('/epaper/')->with('error', 'Epaper not found');
    }
    public function getepapercat(Request $request)
    {
        $categories = Epapercategory::where('cattype', $request->cattype)->get(['id', 'title','slug','cattype']);
        return response()->json($categories);
    }
    public function epaperfetchData(Request $request)
    {
        $columns = ['id', 'etitle', 'categoryNames','viewer', 'action'];

        $draw = $request->input('draw', 1);
        $start = $request->input('start', 0);
        $length = $request->input('length', 20); // Default 20 records per page
        $searchValue = $request->input('search.value', '');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDirection = $request->input('order.0.dir', 'DESC');

        // Generate a unique Redis cache key
        $cacheKey = 'epaperfetchData:search_' . config('app.webtype') . ':' . $draw;

        // Check if data exists in Redis
        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            // Return cached data if exists
            return response()->json(json_decode($cachedData));
        }

        // Base query
        $query = DB::table('epaper')
            ->select(
                'epaper.*',
                'u1.name as authorName',
                'epapercount.view_count as viewer', // ✅ direct from epapercount table
                DB::raw('(SELECT GROUP_CONCAT(epapercategory.slug) 
                        FROM epapercategory 
                        WHERE FIND_IN_SET(epapercategory.id, epaper.ecatID)) as ecatslug')
            )
            ->leftJoin('users as u1', 'epaper.userID', '=', 'u1.id')
            ->leftJoin('epapercount', 'epaper.id', '=', 'epapercount.content_id');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('etitle', 'like', '%' . $searchValue . '%')
                    ->orWhereRaw('(SELECT GROUP_CONCAT(title) 
                                    FROM epapercategory 
                                    WHERE FIND_IN_SET(epapercategory.id, epaper.ecatID)) LIKE ?', ['%' . $searchValue . '%']);
            });
        }

        // Get the total number of filtered records
        $totalRecords = $query->count();

        // Apply pagination and order
        $filteredData = $query->orderBy($orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $counter = $start + 1;

        // Map the data to add row numbers and action buttons
        $formattedData = $filteredData->map(function ($row) use (&$counter) {
            // Assign row number and increment counter
            $row->rowNumber = $counter++;
            // $row->viewer = $row->viewer + $row->viewer_app;
            $row->viewer = $row->viewer ?? 0;
            // Generate category names
            $row->categoryNames = DB::table('epapercategory')
                ->whereRaw('FIND_IN_SET(epapercategory.id, ?)', [$row->ecatID])
                ->pluck('title')
                ->implode(', ');

            // Generate etitle link
            // $epaperUrl = config('app.front_url') . '/epaper/' . $row->categoryNames . '/' . \Carbon\Carbon::parse($row->newspaperdate)->format('d-m-Y');
            $epaperUrl = config('app.front_url') . '/epaper/' . $row->ecatslug . '/' . \Carbon\Carbon::parse($row->newspaperdate)->format('d-m-Y');
            $row->etitle = '<a target="_blank" href="' . $epaperUrl . '">' . htmlspecialchars($row->etitle) . '</a>';

            // Add action buttons
            $row->action = '
            <a class="btn iconbtn showhideCls ' . ($row->status == 'Active' ? 'btn-success' : 'btn-warning') . '" 
            title="' . ($row->status == 'Active' ? 'Show' : 'Hidden') . '" 
            href="' . url('/epaper/updateStatus/' . ($row->status == 'Active' ? 'Inactive' : 'Active'), $row->id) . '">
                <i class="bi ' . ($row->status == 'Active' ? 'bi-eye' : 'bi-eye-slash') . '" aria-hidden="true"></i>
            </a>
            <a class="btn iconbtn editCls btn-primary" title="Edit" href="' . url('/epaper/edit', $row->id) . '">
                <i class="bi bi-pencil"></i>
            </a>
            <a class="btn iconbtn delCls btn-danger" title="Delete" href="' . url('/epaper/destroy', $row->id) . '" 
            onclick="event.preventDefault(); if(confirm(\'Are you sure you want to delete this record?\')) { 
                document.getElementById(\'delete-form-' . $row->id . '\').submit(); 
            }">
                <i class="bi bi-trash"></i>
            </a>
            <form id="delete-form-' . $row->id . '" action="' . url('/epaper/destroy', $row->id) . '" method="POST" style="display: none;">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="hidden" name="_method" value="DELETE">
            </form>';

            return $row;
        });

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $formattedData,
        ];

        // Store the result in Redis with a 5-minute expiration
        Redis::setex($cacheKey, 300, json_encode($response));

        // Return the response
        return response()->json($response);
    }


}

