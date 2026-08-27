<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class SettingController extends Controller
{
    // Get all Categories
    public function index()
    {
        $setting = Setting::find(1); ;
        $data =  array();
        $data['setting'] =  $setting;
        return view('setting.edit', compact('data'));
    }

    
    // Update Category
    public function update(Request $request)
    {
      	Redis::del('setting');
        $rules = [
            'siteTitle' => 'required',
            'email' => 'required|email',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'metatitle'=>'required'
        ];

        $customMessages = [
            'siteTitle.required' => 'The site title field is required.',
            'logo.image' => 'The uploaded file must be an image.',
            'logo.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'logo.max' => 'The image size must not exceed 2 MB.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'metatitle.required' => 'The meta title field is required.',
        ];
    
        $request->validate($rules, $customMessages);

       
        if ($files = $request->file('logo')) {
            //$destinationPath = public_path('/uploads/logo/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            $oldImg = $request->get('oldImg');
            if (!empty($oldImg) && file_exists($destinationPath . $oldImg) && is_file($destinationPath . $oldImg)) {
                unlink($destinationPath . $oldImg);
            }
            $logo = rand().'.'.$files->extension();     
            $files->move($destinationPath, $logo);
        } else {
            $logo =  $request->get('oldImg');
        }
        if ($files = $request->file('logo_dark')) {
            //$destinationPath = public_path('/uploads/logo/'); // upload path
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            $oldImgDark = $request->get('oldImgdark');
            // if (!empty($oldImgDark) && file_exists($destinationPath . $oldImgDark) && is_file($destinationPath . $oldImgDark)) {
            //     unlink($destinationPath . $oldImgDark);
            // }
            $logo_dark = rand().'.'.$files->extension();     
            $files->move($destinationPath, $logo_dark);
        } else {
            $logo_dark =  $request->get('oldImgdark');
        }
        
       
        $setting = Setting::find(1);
        $setting->siteTitle = $request->get('siteTitle');
        $setting->userID = Auth::user()->id;
        $setting->email = $request->get('email');
        $setting->logo = $logo;
        $setting->logo_dark = $logo_dark;
        $setting->newsdefaulttxt = $request->get('newsdefaulttxt');
        $setting->newsdefaultapptxt = $request->get('newsdefaultapptxt');
        $setting->metatitle = $request->get('metatitle');
        $setting->metakeyword = $request->get('metakeyword');
        $setting->metadesc = $request->get('metadesc');
       
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    // Update Category
    public function scriptupdate(Request $request)
    {
      	Redis::del('setting');
        $setting = Setting::find(1);   
        $setting->headerScript = $request->get('headerScript');
        $setting->bodyScript = $request->get('bodyScript');
        $setting->footerScript = $request->get('footerScript');
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Scripts updated successfully.');
    }

    // Update Category
    public function pointupdate(Request $request)
    {
      	Redis::del('setting');
        $setting = Setting::find(1);
        $setting->spendtime = $request->get('spendtime');
        $setting->point = $request->get('point'); 
      	$setting->pointtitle = $request->get('pointtitle');
        $setting->pointtxt = $request->get('pointtxt');
        $setting->pointinfo = $request->get('pointinfo');
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Points updated successfully.');
    }
    public function membership(Request $request)
    {
      	Redis::del('setting');
        $setting = Setting::find(1);
        $setting->pricemonth = $request->get('pricemonth');
        $setting->pricemonthDiscount = $request->get('pricemonthDiscount'); 
        $setting->priceyear = $request->get('priceyear'); 
        $setting->priceyearDiscount = $request->get('priceyearDiscount'); 
        $setting->fullcontent_status = 0;
        if($request->get('fullcontent_status') == "on"){
          $setting->fullcontent_status = 1;
        }
        $setting->fullcontent_login = 0;
        if($request->get('fullcontent_login') == "on"){
          $setting->fullcontent_login = 1;
        }
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Membership Plan updated successfully.');
    }
  
  	public function liveapp(Request $request)
    {
        Redis::del('setting');
        $setting = Setting::find(1);
        $setting->livetvnews = $request->get('livetvnews');
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Live Script updated successfully.');
    }
    public function updatebanner(Request $request)
    {
      
      	Redis::del('setting');

        if ($files = $request->file('middlebanner')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            /*if($request->get('oldmiddleBanner')!='') {
                unlink($destinationPath.$request->get('oldmiddleBanner'));
            }*/
            $middlebanner = rand().'.'.$files->extension();     
            $files->move($destinationPath, $middlebanner);
            $middlebanner = config('app.backendnewsurl').'/public/uploads/logo/'.$middlebanner;
        } else {
            $middlebanner =  $request->get('oldmiddleBanner');
        }
        if($request->get('oldmiddleBanner') == "1"){
          $middlebanner = "";
        }
      
      	if ($files = $request->file('matterbanner')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            /*if($request->get('oldmiddleBanner')!='') {
                unlink($destinationPath.$request->get('oldmiddleBanner'));
            }*/
            $matterbanner = rand().'.'.$files->extension();     
            $files->move($destinationPath, $matterbanner);
            $matterbanner = config('app.backendnewsurl').'/public/uploads/logo/'.$matterbanner;
        } else {
            $matterbanner =  $request->get('oldmatterBanner');
        }
        if($request->get('oldmatterBanner') == "1"){
          $matterbanner = "";
        }
      
      	if ($files = $request->file('rightbanner')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            /*if($request->get('oldrightBanner')!='') {
                unlink($destinationPath.$request->get('oldrightBanner'));
            }*/
            $rightbanner = rand().'.'.$files->extension();     
            $files->move($destinationPath, $rightbanner);
            $rightbanner = config('app.backendnewsurl').'/public/uploads/logo/'.$rightbanner;
        } else {
            $rightbanner =  $request->get('oldrightBanner');
        }
      
      	if($request->get('oldrightBanner') == "1"){
          $rightbanner = "";
        }
      
       if ($files = $request->file('announcement')) {
            $destinationPath  =dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/backend/public/uploads/logo/';
            /*if($request->get('oldannouncement')!='') {
                unlink($destinationPath.$request->get('oldannouncement'));
            }*/
            $announcement = rand().'.'.$files->extension();     
            $files->move($destinationPath, $announcement);
            $announcement = config('app.backendnewsurl').'/public/uploads/logo/'.$announcement;
        } else {
            $announcement =  $request->get('oldannouncement');
        }
        if($request->get('oldannouncement') == "1"){
          $announcement = "";
        }
        $setting = Setting::find(1);
        $setting->middlebanner = $middlebanner;
        $setting->matterbanner = $matterbanner;
        $setting->rightbanner = $rightbanner;
        $setting->announcement = $announcement;
        $setting->middlebanner_link = $request->get('middlebanner_link');
        $setting->matterbanner_link = $request->get('matterbanner_link');
        $setting->rightbanner_link = $request->get('rightbanner_link');
        $setting->announcement_link = $request->get('announcement_link');
        $setting->middlebanner_ios_link = $request->get('middlebanner_ios_link');
        $setting->matterbanner_ios_link = $request->get('matterbanner_ios_link');
        $setting->rightbanner_ios_link = $request->get('rightbanner_ios_link');
        $setting->announcement_ios_link = $request->get('announcement_ios_link');
      	$setting->middlebanner_status = 0;
        if($request->get('middlebanner_status') == "on"){
          $setting->middlebanner_status = 1;
        }
        $setting->matterbanner_status = 0;
        if($request->get('matterbanner_status') == "on"){
          $setting->matterbanner_status = 1;
        }
        $setting->rightbanner_status = 0;
        if($request->get('rightbanner_status') == "on"){
          $setting->rightbanner_status = 1;
        }
        $setting->announcement_status = 0;
        if($request->get('announcement_status') == "on"){
          $setting->announcement_status = 1;
        }
       
        $setting->update();
        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('siteTitle'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));;
        return redirect()->back()->with('success', 'Banner updated successfully.');
    }
    public function election(Request $request)
    {
        Redis::del('setting');
        $setting = Setting::find(1);
        $setting->election_height = $request->get('election_height');
        $setting->election_link = $request->get('election_link');
        $setting->sudoku_link = $request->get('sudoku_link');
        $setting->election_status = 0;
        if($request->get('election_status') == "on"){
          $setting->election_status = 1;
        }
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('election_status'),
            'action' => 'Election',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Election updated successfully.');
    }
    public function newfeatures(Request $request)
    {
        Redis::del('setting');
        $setting = Setting::find(1);
        $setting->newfeatures = $request->get('newfeatures');
        $setting->newfeatures_status = 0;
        if($request->get('newfeatures_status') == "on"){
          $setting->newfeatures_status = 1;
        }
        $setting->stockmarket = 0;
        if($request->get('stockmarket') == "on"){
          $setting->stockmarket = 1;
        }
        $setting->livescore = 0;
        if($request->get('livescore') == "on"){
          $setting->livescore = 1;
        }
        $setting->rashi_status = 0;
        if($request->get('rashi_status') == "on"){
          $setting->rashi_status = 1;
        }
        $setting->podcast_status = 0;
        if($request->get('podcast_status') == "on"){
          $setting->podcast_status = 1;
        }
        $setting->rashi_language = $request->get('rashi_language');
        $setting->home_page_video_big = $request->get('home_page_video_big');
        $setting->rashi_api = $request->get('rashi_api');
        $setting->toemail = $request->get('toemail');
        $setting->ccemail = $request->get('ccemail');
        $setting->applypost = $request->get('applypost');
        $setting->whatsupchannellink = $request->get('whatsupchannellink');
        $setting->update();

        Log::create([
            'userID'=> Auth::user()->id,
            'message' => 'Setting updated: ' . $request->get('newfeatures'),
            'action' => 'Edit',
            'contents' => json_encode($request->all()),
        ]);
 
        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'New Features updated successfully.');
    }
     public function trending(Request $request)
    {
        Redis::del('setting');
        $setting = Setting::find(1);

        // Build clean JSON array from submitted trending rows
        $rows    = $request->input('trending', []);
        $cleaned = [];
        foreach ($rows as $row) {
            $en = trim($row['en'] ?? '');
            $gu = trim($row['gu'] ?? '');
            // Only save rows that have at least one value
            if (!empty($en) || !empty($gu)) {
                $cleaned[] = ['en' => $en, 'gu' => $gu,'slug'=>strtolower($en)];
            }
        }
        $setting->trending = json_encode($cleaned, JSON_UNESCAPED_UNICODE);
        $setting->update();

        Log::create([
            'userID'   => Auth::user()->id,
            'message'  => 'Setting updated: Trending',
            'action'   => 'Trending',
            'contents' => json_encode($request->all()),
        ]);

        session()->flash('activeTab', $request->input('activeTab'));
        return redirect()->back()->with('success', 'Trending updated successfully.');
    }

}

