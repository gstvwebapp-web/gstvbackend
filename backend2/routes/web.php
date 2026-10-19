<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\backend\DashboardController;
use App\Http\Controllers\backend\CategoryController;
use App\Http\Controllers\backend\PageController;
use App\Http\Controllers\backend\PollController;
use App\Http\Controllers\backend\VotegameController;
use App\Http\Controllers\backend\NewsController;
use App\Http\Controllers\backend\UserController;
use App\Http\Controllers\backend\ModuleController;
use App\Http\Controllers\backend\TagController;
use App\Http\Controllers\backend\WebstoryController;
use App\Http\Controllers\backend\RolesController;
use App\Http\Controllers\backend\MediaController;
use App\Http\Controllers\backend\SettingController;
use App\Http\Controllers\backend\ChangePasswordController;
use App\Http\Controllers\backend\CsvfileuploadController;
use App\Http\Controllers\backend\GeneralController;
use App\Http\Controllers\Auth\FeedController;
use App\Http\Controllers\Auth\IfeedController;
use App\Http\Controllers\backend\ViewexportController;
use App\Http\Controllers\backend\AdsController;
use App\Http\Controllers\backend\AdsgraphicsController;
use App\Http\Controllers\backend\AdsimpressionController;
use App\Http\Controllers\backend\IframeController;
use App\Http\Controllers\backend\FeedbackController;
use App\Http\Controllers\backend\FrontusersController;
use App\Http\Controllers\backend\ElectionresultsController;
use App\Http\Controllers\backend\EpapercategoryController;
use App\Http\Controllers\backend\EpaperController;
use App\Http\Controllers\backend\UsernewsController;
use App\Http\Controllers\backend\GiftController;
use App\Http\Controllers\backend\SidemenuController;
use App\Http\Controllers\backend\LanguageController;
use App\Http\Controllers\backend\RashiController;
use App\Http\Controllers\backend\RashidataController;
use App\Http\Controllers\backend\CricketController;
use App\Http\Controllers\backend\NewsflashController;
use App\Http\Controllers\backend\PodcastauthorController;
use App\Http\Controllers\backend\PodcastController;
use App\Http\Controllers\backend\VideoadsController;
use App\Http\Controllers\backend\FestivalController;
use App\Http\Controllers\backend\MxfVideoController;
use App\Http\Controllers\backend\EventController;
use App\Http\Controllers\backend\AdspriceController;
use App\Http\Controllers\backend\UserscoinController;
use App\Http\Controllers\backend\AthaitapController;
use App\Http\Controllers\backend\GanapatiController;
use App\Http\Controllers\backend\MarugujaratController;
use App\Http\Controllers\backend\CampuscornerController;
use App\Http\Controllers\backend\CareerController;
use App\Http\Controllers\backend\PaymentController;
use App\Http\Controllers\backend\MagazinereviewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\backend\NewsOrderLogController;
use App\Http\Controllers\backend\ElectionResultModuleController;

// MXF Video Test Route (No Auth Required)
Route::get('/test-mxf', function() {
    $exists = class_exists('App\Http\Controllers\backend\MxfVideoController');
    return 'MXF Video Module Routes are working! Controller exists: ' . ($exists ? 'YES' : 'NO');
});

Route::get('/clear', function () {
    try {
        Artisan::call('cache:clear');
        $messages[] = '✓ Laravel cache cleared';
        
        Artisan::call('view:clear');
        $messages[] = '✓ View cache cleared';
        
        Artisan::call('route:clear');
        $messages[] = '✓ Route cache cleared';
        
    } catch (\Exception $e) {
        $messages[] = '✗ Laravel cache clear error: ' . $e->getMessage();
    }
    
    $messages[] = '';
    $messages[] = '<strong>✓ All caches cleared!</strong>';
    $messages[] = '<strong>📊 Google Analytics data will be fetched automatically on next page load.</strong>';
    $messages[] = '<strong>⏱ First load may take 8-10 seconds, then cached for 6 hours.</strong>';
    $messages[] = '';
    $messages[] = '<a href="' . url('/news') . '" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">Go to News Page</a>';
    
    return implode('<br>', $messages);
});

Route::get('/redisclear', function () {

    $allowedTables = ['newscount', 'webstorycount', 'epapercount', 'pollcount'];

    foreach ($allowedTables as $type) {

        $cacheKey = "view_counts:{$type}_" . config('app.webtype');
        $counts = Cache::get($cacheKey, []);

        if (!$counts) continue;

        $existing = DB::table($type)
            ->whereIn('content_id', array_keys($counts))
            ->pluck('view_count', 'content_id');

        $insertData = [];

        foreach ($counts as $contentId => $count) {

            $insertData[] = [
                'content_id' => $contentId,
                'view_count' => ($existing[$contentId] ?? 0) + $count,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        // ✅ Chunk to avoid placeholder overflow
        foreach (array_chunk($insertData, 300) as $chunk) {

            DB::table($type)->upsert(
                $chunk,
                ['content_id'],
                ['view_count', 'updated_at']
            );
        }

        Cache::forget($cacheKey);
    }

    // LOGIN COUNTS
    foreach ($allowedTables as $type) {

        $cacheKey = "view_logincounts:{$type}_" . config('app.webtype');
        $counts = Cache::get($cacheKey, []);

        if (!$counts) continue;

        $existing = DB::table($type)
            ->whereIn('content_id', array_keys($counts))
            ->pluck('logincount', 'content_id');

        $insertData = [];

        foreach ($counts as $contentId => $count) {

            $insertData[] = [
                'content_id' => $contentId,
                'logincount' => ($existing[$contentId] ?? 0) + $count,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach (array_chunk($insertData, 300) as $chunk) {

            DB::table($type)->upsert(
                $chunk,
                ['content_id'],
                ['logincount', 'updated_at']
            );
        }

        Cache::forget($cacheKey);
    }

    Redis::flushall();

    return redirect()->back()->with('status', 'Cache Cleared!');
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/error/404', function () {
  return view('errors.404');
})->name('error.404');
// Route::get('/phpinfo', function () {
//     return phpinfo();
// });

Route::middleware(['guest'])->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/home', [LoginController::class, 'showLoginForm'])->name('home');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/{slug}/feed', [FeedController::class, 'getRssCategoryData'])->name('feed')->where('slug', '^(?!izooto).*$');
    Route::get('/web/webstory/feed', [FeedController::class, 'getRsswebstory'])->name('feed');
  	Route::get('/rss', [FeedController::class, 'getrss'])->name('rss');
  	
  	Route::get('/izooto/feed', [IfeedController::class, 'index'])->name('izooto.feed');
    Route::get('/izooto/{slug}/feed', [IfeedController::class, 'getRssCategoryData'])->name('izooto.feed.category');
    Route::get('/izooto/web/webstory/feed', [IfeedController::class, 'getRsswebstory'])->name('izooto.feed.webstory');
});

Route::middleware(['auth'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/top10ajax', [DashboardController::class, 'getTodayTop10Ajax'])->name('dashboard.top10ajax');
    Route::get('/getalldatas', [DashboardController::class, 'getalldatas'])->name('getalldatas');
    Route::middleware(['check.user.permission','check.userRole.permission'])->group(function () {
        Route::get('/category', [CategoryController::class, 'index'])->name('category');
        Route::get('/category/add', [CategoryController::class, 'add'])->name('category.add');
        Route::post('/category/store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/update', [CategoryController::class, 'update'])->name('category.update');
        Route::post('/category/catorder', [CategoryController::class, 'catorder'])->name('category.catorder');
        Route::delete('/category/destroy/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
        Route::get('/category/updateStatus/{status}/{id}', [CategoryController::class, 'updateStatus'])->name('category.updateStatus');
        Route::get('/category/categoryfetchData', [CategoryController::class, 'categoryfetchData']);
      
      	Route::get('/sidemenu', [SidemenuController::class, 'index'])->name('sidemenu');
        Route::get('/sidemenu/add', [SidemenuController::class, 'add'])->name('sidemenu.add');
        Route::post('/sidemenu/store', [SidemenuController::class, 'store'])->name('sidemenu.store');
        Route::get('/sidemenu/edit/{id}', [SidemenuController::class, 'edit'])->name('sidemenu.edit');
        Route::post('/sidemenu/update', [SidemenuController::class, 'update'])->name('sidemenu.update');
        Route::post('/sidemenu/catorder', [SidemenuController::class, 'catorder'])->name('sidemenu.catorder');
        Route::delete('/sidemenu/destroy/{id}', [SidemenuController::class, 'destroy'])->name('sidemenu.destroy');
        Route::get('/sidemenu/updateStatus/{id}/{status}', [SidemenuController::class, 'updateStatus'])->name('sidemenu.updateStatus');
        Route::get('/sidemenu/sidemenufetchData', [SidemenuController::class, 'sidemenufetchData']);

        Route::get('/language', [LanguageController::class, 'index'])->name('language');
        Route::get('/language/add', [LanguageController::class, 'add'])->name('language.add');
        Route::post('/language/store', [LanguageController::class, 'store'])->name('language.store');
        Route::get('/language/edit/{id}', [LanguageController::class, 'edit'])->name('language.edit');
        Route::post('/language/update', [LanguageController::class, 'update'])->name('language.update');
        Route::delete('/language/destroy/{id}', [LanguageController::class, 'destroy'])->name('language.destroy');
        Route::get('/language/updateStatus/{status}/{id}', [LanguageController::class, 'updateStatus'])->name('language.updateStatus');
        Route::get('/language/languagefetchData', [LanguageController::class, 'languagefetchData']);
        
        Route::get('/mediagallery', [MediaController::class, 'index'])->name('mediagallery');
        Route::post('/mediagallery/media-items', [MediaController::class, 'store'])->name('mediagallery.media-items');
        Route::post('/mediagallery/media-update', [MediaController::class, 'update'])->name('mediagallery.media-update');
        Route::get('/mediagallery/delete-image/{id}', [MediaController::class, 'deleteImage']);
        Route::post('/mediagallery/mediaSearch', [MediaController::class, 'mediaSearch'])->name('mediagallery.mediaSearch');
		Route::post('/mediagallery/getImgTime', [MediaController::class, 'getImgTime'])->name('mediagallery.getImgTime');
      
        Route::get('/module', [ModuleController::class, 'index'])->name('module');
        Route::get('/module/add', [ModuleController::class, 'add'])->name('module.add');
        Route::post('/module/store', [ModuleController::class, 'store'])->name('module.store');
        Route::get('/module/edit/{id}', [ModuleController::class, 'edit'])->name('module.edit');
        Route::post('/module/update', [ModuleController::class, 'update'])->name('module.update');
        Route::delete('/module/destroy/{id}', [ModuleController::class, 'destroy'])->name('module.destroy');
        Route::get('/module/updateStatus/{status}/{id}', [ModuleController::class, 'updateStatus'])->name('module.updateStatus');
        Route::get('/module/modulefetchData', [ModuleController::class, 'modulefetchData']);

        Route::get('/tags', [TagController::class, 'index'])->name('tags');
        Route::get('/tags/add', [TagController::class, 'add'])->name('tags.add');
        Route::post('/tags/store', [TagController::class, 'store'])->name('tags.store');
        Route::get('/tags/edit/{id}', [TagController::class, 'edit'])->name('tags.edit');
        Route::post('/tags/update', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/tags/destroy/{id}', [TagController::class, 'destroy'])->name('tags.destroy');
        Route::get('/tags/updateStatus/{status}/{id}', [TagController::class, 'updateStatus'])->name('tags.updateStatus');
        Route::get('/tags/tagsfetchData', [TagController::class, 'tagsfetchData']);

        Route::get('/roles', [RolesController::class, 'index'])->name('roles');
        Route::get('/roles/add', [RolesController::class, 'add'])->name('roles.add');
        Route::post('/roles/store', [RolesController::class, 'store'])->name('roles.store');
        Route::get('/roles/edit/{id}', [RolesController::class, 'edit'])->name('roles.edit');
        Route::post('/roles/update', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('/roles/destroy/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
        Route::get('/roles/updateStatus/{status}/{id}', [RolesController::class, 'updateStatus'])->name('roles.updateStatus');
        Route::get('/roles/rolesfetchData', [RolesController::class, 'rolesfetchData']);

        Route::get('/pages', [PageController::class, 'index'])->name('pages');
        Route::get('/pages/add', [PageController::class, 'add'])->name('pages.add');
        Route::post('/pages/store', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/edit/{id}', [PageController::class, 'edit'])->name('pages.edit');
        Route::post('/pages/update', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/destroy/{id}', [PageController::class, 'destroy'])->name('pages.destroy');
        Route::get('/pages/updateStatus/{status}/{id}', [PageController::class, 'updateStatus'])->name('pages.updateStatus');
        Route::get('/pages/pagesfetchData', [PageController::class, 'pagesfetchData']);
    
        Route::get('/changepassword', [ChangePasswordController::class, 'index'])->name('password.change');
        Route::post('/changepassword/update', [ChangePasswordController::class, 'update']);

        Route::get('/csvfileupload', [CsvfileuploadController::class, 'index'])->name('csvfileupload');
        Route::post('/csvfileupload/csvupload', [CsvfileuploadController::class, 'NewsCSVupload'])->name('csvfileupload.csvupload');

        Route::get('/news', [NewsController::class, 'index'])->name('news');
        Route::Post('/news', [NewsController::class, 'index'])->name('news');
        Route::get('/news/loadmoredata', [NewsController::class, 'loadmoredata'])->name('news.loadmoredata');
        Route::get('/news/loadmorevideodata', [NewsController::class, 'loadmorevideodata'])->name('news.loadmorevideodata');
        Route::get('/news/publish', [NewsController::class, 'publish'])->name('news.publish');
        
        Route::get('/news/PublishedNews', [NewsController::class, 'PublishedNews'])->name('news.PublishedNews');
        Route::get('/news/fetchData', [NewsController::class, 'fetchData'])->name('news.fetchData');
        Route::get('/news/searchRelatedNews', [NewsController::class, 'searchRelatedNews']);
        Route::get('/news/translateTitle', [NewsController::class, 'translateTitle']);



        Route::Post('/news/publish', [NewsController::class, 'publish'])->name('news.publish');
        Route::get('/news/unpublish', [NewsController::class, 'unpublish'])->name('news.unpublish');
        Route::Post('/news/unpublish', [NewsController::class, 'unpublish'])->name('news.unpublish');
        Route::get('/news/schedule', [NewsController::class, 'schedule'])->name('news.schedule');
        Route::Post('/news/schedule', [NewsController::class, 'schedule'])->name('news.schedule');
        Route::get('/news/draft', [NewsController::class, 'draft'])->name('news.draft');
        Route::Post('/news/draft', [NewsController::class, 'draft'])->name('news.draft');
        Route::get('/news/add', [NewsController::class, 'add'])->name('news.add');
        Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');
        Route::post('/news/autosaveDraft', [NewsController::class, 'autosaveDraft'])->name('news.autosaveDraft');
        Route::get('/news/edit/{id}', [NewsController::class, 'edit'])->name('news.edit');
        Route::post('/news/update', [NewsController::class, 'update'])->name('news.update');
        Route::delete('/news/destroy/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
        Route::get('/news/updateStatus/{status}/{id}', [NewsController::class, 'updateStatus'])->name('news.updateStatus');
        Route::get('/news/tagSuggestions', [NewsController::class, 'tagSuggestions'])->name('news.tagSuggestions');
        Route::Post('/news/taginserts', [NewsController::class, 'taginserts'])->name('news.taginserts');
        Route::Post('/news/subCategory', [NewsController::class, 'subCategory'])->name('news.subCategory');
        Route::post('/news/newsorder', [NewsController::class, 'newsorder'])->name('news.newsorder');
        Route::post('/news/tophome', [NewsController::class, 'tophome'])->name('news.tophome');
      	Route::get('/news/export-csv', [NewsController::class, 'exportCsv'])->name('export-csv');
      	Route::get('/news/videos', [NewsController::class, 'videonews'])->name('news.videonews');
		    Route::Post('/news/videos', [NewsController::class, 'videonews'])->name('news.videonews');
        Route::get('/news/editusernews/{id}', [NewsController::class, 'editusernews'])->name('user.editusernews');
        Route::get('/news/cloudflarecache', [NewsController::class, 'cloudflarecache'])->name('news.cloudflarecache');

        
        

        Route::get('/user', [UserController::class, 'index'])->name('user');
        Route::get('/user/add', [UserController::class, 'add'])->name('user.add');
        Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/user/update', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');
        Route::get('/user/updateStatus/{status}/{id}', [UserController::class, 'updateStatus'])->name('user.updateStatus');
      	Route::get('/user/profile/{id}', [UserController::class, 'profile'])->name('user.profile');
        Route::get('/user/userprofilefetchData', [UserController::class, 'userprofilefetchData'])->name('user.userprofilefetchData');
        Route::get('/viewexport', [ViewexportController::class, 'viewexport']);
        Route::get('/viewexport/viewexportfetchData', [ViewexportController::class, 'viewexportfetchData']);
        Route::get('/user/userfetchData', [UserController::class, 'userfetchData']);
        
        Route::get('/poll', [PollController::class, 'index'])->name('poll');
        Route::get('/poll/add', [PollController::class, 'add'])->name('poll.add');
        Route::post('/poll/store', [PollController::class, 'store'])->name('poll.store');
        Route::get('/poll/edit/{id}', [PollController::class, 'edit'])->name('poll.edit');
        Route::post('/poll/update', [PollController::class, 'update'])->name('poll.update');
        Route::delete('/poll/destroy/{id}', [PollController::class, 'destroy'])->name('poll.destroy');
        Route::get('/poll/updateStatus/{status}/{id}', [PollController::class, 'updateStatus'])->name('poll.updateStatus');
        Route::get('/poll/pollfetchData', [PollController::class, 'pollfetchData']);
        Route::get('/poll/viewuserresult/{id}', [PollController::class, 'viewuserresult']);
        Route::get('/poll/userpollfetchData', [PollController::class, 'userpollfetchData']);
      	Route::post('/poll/luckydraw', [PollController::class, 'userpollLuckyDraw']);
      	Route::get('/poll/userpollcount', [PollController::class, 'userpollcount']);
        Route::get('/poll/userpollcountfetchData', [PollController::class, 'userpollcountfetchData']);
        Route::get('/poll/exportUserPollpoint', [PollController::class, 'exportUserPollpoint']);
        Route::get('/poll/exportUserPoll', [PollController::class, 'exportUserPoll']);

        Route::get('/iframe', [IframeController::class, 'index'])->name('iframe');
        Route::get('/iframe/add', [IframeController::class, 'add'])->name('iframe.add');
        Route::post('/iframe/store', [IframeController::class, 'store'])->name('iframe.store');
        Route::get('/iframe/edit/{id}', [IframeController::class, 'edit'])->name('iframe.edit');
        Route::post('/iframe/update', [IframeController::class, 'update'])->name('iframe.update');
        Route::delete('/iframe/destroy/{id}', [IframeController::class, 'destroy'])->name('iframe.destroy');
        Route::get('/iframe/updateStatus/{status}/{id}', [IframeController::class, 'updateStatus'])->name('iframe.updateStatus');
        Route::get('/iframe/iframefetchData', [IframeController::class, 'iframefetchData']);
      
        Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback');
        Route::delete('/feedback/destroy/{id}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');
        Route::get('/feedback/feedbackfetchData', [FeedbackController::class, 'feedbackfetchData']);

        Route::get('/frontend-users', [FrontusersController::class, 'index']);
        Route::get('/frontend-users/updateStatus/{status}/{id}', [FrontusersController::class, 'updateStatus']);
        Route::get('/frontend-users/view/{id}', [FrontusersController::class, 'view']);
        Route::get('/frontend-users/frontendusersfetchData', [FrontusersController::class, 'frontendusersfetchData']);
      
      	Route::get('/users-point', [UserscoinController::class, 'index']);
        Route::get('/users-point/view/{id}', [UserscoinController::class, 'view']);
        Route::get('/users-point/usercoinfetchData', [UserscoinController::class, 'usercoinfetchData']);
      
      	Route::get('/votegame', [VotegameController::class, 'index'])->name('votegame');
        Route::get('/votegame/add', [VotegameController::class, 'add'])->name('votegame.add');
        Route::post('/votegame/store', [VotegameController::class, 'store'])->name('votegame.store');
        Route::get('/votegame/edit/{id}', [VotegameController::class, 'edit'])->name('votegame.edit');
        Route::post('/votegame/update', [VotegameController::class, 'update'])->name('votegame.update');
        Route::delete('/votegame/destroy/{id}', [VotegameController::class, 'destroy'])->name('votegame.destroy');
        Route::get('/votegame/updateStatus/{status}/{id}', [VotegameController::class, 'updateStatus'])->name('votegame.updateStatus');
        Route::get('/votegame/votegamefetchData', [VotegameController::class, 'votegamefetchData']);

        Route::get('/setting', [SettingController::class, 'index'])->name('setting');
        Route::post('/setting/update', [SettingController::class, 'update'])->name('setting.update');
      	Route::post('/setting/scriptupdate', [SettingController::class, 'scriptupdate'])->name('setting.scriptupdate');
        Route::post('/setting/pointupdate', [SettingController::class, 'pointupdate'])->name('setting.pointupdate');
        Route::post('/setting/membership', [SettingController::class, 'membership'])->name('setting.membership');
      	Route::post('/setting/liveapp', [SettingController::class, 'liveapp'])->name('setting.liveapp');
        Route::post('/setting/updatebanner', [SettingController::class, 'updatebanner'])->name('setting.updatebanner');
        Route::post('/setting/election', [SettingController::class, 'election'])->name('setting.election');
        Route::post('/setting/newfeatures', [SettingController::class, 'newfeatures'])->name('setting.newfeatures');
        Route::post('/setting/trending', [SettingController::class, 'trending'])->name('setting.trending');
      
        Route::get('/webstory', [WebstoryController::class, 'index'])->name('webstory');
        Route::get('/webstory/add', [WebstoryController::class, 'add'])->name('webstory.add');
        Route::post('/webstory/store', [WebstoryController::class, 'store'])->name('webstory.store');
        Route::get('/webstory/edit/{id}', [WebstoryController::class, 'edit'])->name('webstory.edit');
        Route::post('/webstory/update', [WebstoryController::class, 'update'])->name('webstory.update');
        Route::delete('/webstory/destroy/{id}', [WebstoryController::class, 'destroy'])->name('webstory.destroy');
        Route::get('/webstory/updateStatus/{status}/{id}', [WebstoryController::class, 'updateStatus'])->name('webstory.updateStatus');
        Route::post('/webstory/webstoryorder', [WebstoryController::class, 'webstoryorder'])->name('webstory.webstoryorder');
        Route::get('/webstory/webstoryfetchData', [WebstoryController::class, 'WebstoryfetchData'])->name('webstory.webstoryfetchData');
      
      	Route::get('/general', [GeneralController::class, 'index'])->name('general');
        Route::get('/general/generalfetchData', [GeneralController::class, 'generalfetchData'])->name('generalfetchData');
        Route::get('/ads', [AdsController::class, 'index'])->name('ads.index');
        Route::get('/ads/add', [AdsController::class, 'add'])->name('ads.add');
        Route::post('/ads/store', [AdsController::class, 'store'])->name('ads.store');
        Route::get('/ads/edit/{id}', [AdsController::class, 'edit'])->name('ads.edit');
        Route::post('/ads/update', [AdsController::class, 'update'])->name('ads.update');
        Route::get('/ads/updateStatus/{status}/{id}', [AdsController::class, 'updateStatus'])->name('ads.updateStatus');
        Route::get('/ads/adsfetchData', [AdsController::class, 'adsfetchData']);

        Route::get('/adsimpression', [AdsimpressionController::class, 'index']);
        Route::get('/adsimpression/graph', [AdsimpressionController::class, 'graph'])->name('graph'); 
        Route::post('/adsimpression/pdf', [AdsimpressionController::class, 'generatePdf'])->name('generatePdf');
        Route::get('/adsimpression/adsimpressionfetchData', [AdsimpressionController::class, 'adsimpressionfetchData']);

        Route::get('/graphic', [AdsgraphicsController::class, 'index'])->name('graphic');
        Route::get('/graphic/add', [AdsgraphicsController::class, 'add'])->name('graphic.add');
        Route::post('/graphic/store', [AdsgraphicsController::class, 'store'])->name('graphic.store');
        Route::get('/graphic/edit/{id}', [AdsgraphicsController::class, 'edit'])->name('graphic.edit');
        Route::post('/graphic/update', [AdsgraphicsController::class, 'update'])->name('graphic.update');
        Route::get('/graphic/updateStatus/{status}/{id}', [AdsgraphicsController::class, 'updateStatus'])->name('graphic.updateStatus');
        Route::get('/graphic/adsgraphicsfetchData', [AdsgraphicsController::class, 'adsgraphicsfetchData']);
      	
      	Route::match(['get', 'post'], '/user/deletemediaImages', [UserController::class, 'deletemediaImages'])->name('deletemediaImages');
		Route::match(['get', 'post'], '/user/deletenews', [UserController::class, 'deletenewsImages'])->name('deletenewsImages');
      
      	Route::get('/electionresults/all', [ElectionresultsController::class, 'index'])->name('electionresults');
        Route::get('/electionresults/add', [ElectionresultsController::class, 'add'])->name('electionresults.add');
        Route::post('/electionresults/store', [ElectionresultsController::class, 'store'])->name('electionresults.store');
        Route::get('/electionresults/edit/{id}', [ElectionresultsController::class, 'edit'])->name('electionresults.edit');
        Route::post('/electionresults/update', [ElectionresultsController::class, 'update'])->name('electionresults.update');
        Route::get('/electionresults/updateStatus/{status}/{id}', [ElectionresultsController::class, 'updateStatus'])->name('electionresults.updateStatus');
        Route::delete('/electionresults/destroy/{id}', [ElectionresultsController::class, 'destroy'])->name('electionresults.destroy');
        Route::get('/electionresults/electionresultsfetchData', [ElectionresultsController::class, 'electionresultsfetchData']);

        Route::get('/electionresults/electionstar', [ElectionresultsController::class, 'electionstar']);
        Route::get('/electionresults/addelectionstar', [ElectionresultsController::class, 'addelectionstar']);
        Route::post('/electionresults/submitelectionstar', [ElectionresultsController::class, 'submitelectionstar']);
        Route::get('/electionresults/editelectionstar/{id}', [ElectionresultsController::class, 'editelectionstar']);
        Route::post('/electionresults/updateelectionstar', [ElectionresultsController::class, 'updateelectionstar']);
        Route::get('/electionresults/updateelectionstarStatus/{status}/{id}', [ElectionresultsController::class, 'updateelectionstarStatus']);
        Route::delete('/electionresults/destroyelectionstar/{id}', [ElectionresultsController::class, 'destroyelectionstar']);
        Route::get('/electionresults/electionstarfetchData', [ElectionresultsController::class, 'electionstarfetchData']);
      
        Route::get('/usernews', [UsernewsController::class, 'index'])->name('usernews');
        Route::get('/usernews/add', [UsernewsController::class, 'add'])->name('usernews.add');
        Route::post('/usernews/store', [UsernewsController::class, 'store'])->name('usernews.store');
        Route::get('/usernews/edit/{id}', [UsernewsController::class, 'edit'])->name('usernews.edit');
        Route::post('/usernews/update', [UsernewsController::class, 'update'])->name('usernews.update');
        Route::delete('/usernews/destroy/{id}', [UsernewsController::class, 'destroy'])->name('usernews.destroy');
        Route::get('/usernews/updateStatus/{status}/{id}', [UsernewsController::class, 'updateStatus'])->name('usernews.updateStatus');
        Route::post('/usernews/rejectnews', [UsernewsController::class, 'rejectnews'])->name('usernews.rejectnews');
        Route::get('/usernews/userActivenewsfetchdata', [UsernewsController::class, 'userActivenewsfetchdata'])->name('userActivenewsfetchdata');
        Route::get('/usernews/userRejectednewsfetchdata', [UsernewsController::class, 'userRejectednewsfetchdata'])->name('userRejectednewsfetchdata');
        Route::get('/usernews/usernewsfetchdata', [UsernewsController::class, 'usernewsfetchdata'])->name('usernewsfetchdata');
        
      
      	Route::get('/epapercategory', [EpapercategoryController::class, 'index'])->name('epapercategory');
        Route::get('/epapercategory/add', [EpapercategoryController::class, 'add'])->name('epapercategory.add');
        Route::post('/epapercategory/store', [EpapercategoryController::class, 'store'])->name('epapercategory.store');
        Route::get('/epapercategory/edit/{id}', [EpapercategoryController::class, 'edit'])->name('epapercategory.edit');
        Route::post('/epapercategory/update', [EpapercategoryController::class, 'update'])->name('epapercategory.update');
        Route::post('/epapercategory/catorder', [EpapercategoryController::class, 'catorder'])->name('epapercategory.catorder');
        Route::delete('/epapercategory/destroy/{id}', [EpapercategoryController::class, 'destroy'])->name('epapercategory.destroy');
        Route::get('/epapercategory/updateStatus/{status}/{id}', [EpapercategoryController::class, 'updateStatus'])->name('epapercategory.updateStatus');
        Route::get('/epapercategory/epapercategoryfetchData', [EpapercategoryController::class, 'epapercategoryfetchData']);

        Route::get('/epaper', [EpaperController::class, 'index'])->name('epaper');
        Route::get('/epaper/add', [EpaperController::class, 'add'])->name('epaper.add');
        Route::post('/epaper/store', [EpaperController::class, 'store'])->name('epaper.store');
        Route::get('/epaper/edit/{id}', [EpaperController::class, 'edit'])->name('epaper.edit');
        Route::post('/epaper/update', [EpaperController::class, 'update'])->name('epaper.update');
        Route::get('/epaper/getepapercat', [EpaperController::class, 'getepapercat'])->name('epaper.getepapercat');
        Route::get('/epaper/deletepdfepaper', [EpaperController::class, 'deletepdfepaper'])->name('epaper.deletepdfepaper');
        Route::delete('/epaper/destroy/{id}', [EpaperController::class, 'destroy'])->name('epaper.destroy');
        Route::get('/epaper/updateStatus/{status}/{id}', [EpaperController::class, 'updateStatus'])->name('epaper.updateStatus');
        Route::get('/epaper/epaperfetchData', [EpaperController::class, 'epaperfetchData']);

        Route::get('/gift', [GiftController::class, 'index'])->name('category');
        Route::get('/gift/add', [GiftController::class, 'add'])->name('gift.add');
        Route::post('/gift/store', [GiftController::class, 'store'])->name('gift.store');
        Route::get('/gift/edit/{id}', [GiftController::class, 'edit'])->name('gift.edit');
        Route::post('/gift/update', [GiftController::class, 'update'])->name('gift.update');
        Route::delete('/gift/destroy/{id}', [GiftController::class, 'destroy'])->name('gift.destroy');
        Route::get('/gift/updateStatus/{status}/{id}', [GiftController::class, 'updateStatus'])->name('gift.updateStatus');
        Route::get('/giftreedem', [GiftController::class, 'giftreedem'])->name('giftreedem');
        Route::get('/gift/giftfetchData', [GiftController::class, 'giftfetchData']);
        Route::get('/giftreedem/giftreedemfetchData', [GiftController::class, 'giftreedemfetchData']);
        
        Route::get('/rashi', [RashiController::class, 'index']);
        Route::get('/rashi/add', [RashiController::class, 'add'])->name('rashi.add');
        Route::post('/rashi/store', [RashiController::class, 'store'])->name('rashi.store');
        Route::get('/rashi/edit/{id}', [RashiController::class, 'edit'])->name('rashi.edit');
        Route::post('/rashi/update', [RashiController::class, 'update'])->name('rashi.update');
        Route::delete('/rashi/destroy/{id}', [RashiController::class, 'destroy'])->name('rashi.destroy');
        Route::get('/rashi/updateStatus/{status}/{id}', [RashiController::class, 'updateStatus'])->name('rashi.updateStatus');
        Route::get('/rashi/rashifetchData', [RashiController::class, 'rashifetchData']);

        Route::get('/rashidata', [RashidataController::class, 'index']);
        Route::get('/rashidata/add', [RashidataController::class, 'add'])->name('rashidata.add');
        Route::post('/rashidata/store', [RashidataController::class, 'store'])->name('rashidata.store');
        Route::get('/rashidata/edit/{id}', [RashidataController::class, 'edit'])->name('rashidata.edit');
        Route::post('/rashidata/update', [RashidataController::class, 'update'])->name('rashidata.update');
        Route::post('/rashidata/catorder', [RashidataController::class, 'catorder'])->name('rashidata.catorder');
        Route::delete('/rashidata/destroy/{id}', [RashidataController::class, 'destroy'])->name('rashidata.destroy');
        Route::get('/rashidata/updateStatus/{status}/{id}', [RashidataController::class, 'updateStatus'])->name('rashidata.updateStatus');
        Route::get('/rashidata/rashidatafetchData', [RashidataController::class, 'rashidatafetchData']);

        Route::get('/cricket', [CricketController::class, 'index']);
        Route::get('/cricket/updateStatus/{status}/{id}', [CricketController::class, 'updateStatus']);
        Route::get('/cricket/cricketfetchData', [CricketController::class, 'cricketfetchData']);
        Route::delete('/cricket/destroy/{id}', [CricketController::class, 'destroy']);

        Route::get('/newsflash', [NewsflashController::class, 'index'])->name('newsflash');
        Route::get('/newsflash/add', [NewsflashController::class, 'add'])->name('newsflash.add');
        Route::post('/newsflash/store', [NewsflashController::class, 'store'])->name('newsflash.store');
        Route::get('/newsflash/edit/{id}', [NewsflashController::class, 'edit'])->name('newsflash.edit');
        Route::post('/newsflash/update', [NewsflashController::class, 'update'])->name('newsflash.update');
        Route::delete('/newsflash/destroy/{id}', [NewsflashController::class, 'destroy'])->name('newsflash.destroy');
        Route::get('/newsflash/updateStatus/{id}/{status}', [NewsflashController::class, 'updateStatus'])->name('newsflash.updateStatus');
        Route::get('/newsflash/newsflashfetchData', [NewsflashController::class, 'newsflashfetchData']);
        
        Route::get('/podcastauthor', [PodcastauthorController::class, 'index'])->name('podcastauthor');
        Route::get('/podcastauthor/add', [PodcastauthorController::class, 'add'])->name('podcastauthor.add');
        Route::post('/podcastauthor/store', [PodcastauthorController::class, 'store'])->name('podcastauthor.store');
        Route::get('/podcastauthor/edit/{id}', [PodcastauthorController::class, 'edit'])->name('podcastauthor.edit');
        Route::post('/podcastauthor/update', [PodcastauthorController::class, 'update'])->name('podcastauthor.update');
        Route::delete('/podcastauthor/destroy/{id}', [PodcastauthorController::class, 'destroy'])->name('podcastauthor.destroy');
        Route::get('/podcastauthor/updateStatus/{status}/{id}', [PodcastauthorController::class, 'updateStatus'])->name('podcastauthor.updateStatus');
        Route::get('/podcastauthor/podcastauthorfetchData', [PodcastauthorController::class, 'podcastauthorfetchData']);
      
      
      	Route::get('/podcast', [PodcastController::class, 'index'])->name('podcast');
        Route::get('/podcast/add', [PodcastController::class, 'add'])->name('podcast.add');
        Route::post('/podcast/store', [PodcastController::class, 'store'])->name('podcast.store');
        Route::get('/podcast/edit/{id}', [PodcastController::class, 'edit'])->name('podcast.edit');
        Route::post('/podcast/update', [PodcastController::class, 'update'])->name('podcast.update');
        Route::delete('/podcast/destroy/{id}', [PodcastController::class, 'destroy'])->name('podcast.destroy');
        Route::get('/podcast/updateStatus/{status}/{id}', [PodcastController::class, 'updateStatus'])->name('podcast.updateStatus');
        Route::get('/podcast/podcastfetchData', [PodcastController::class, 'podcastfetchData']);

        // Video Ads Routes
        Route::get('/videoads', [VideoadsController::class, 'index'])->name('videoads');
        Route::get('/videoads/add', [VideoadsController::class, 'add'])->name('videoads.add');
        Route::post('/videoads/store', [VideoadsController::class, 'store'])->name('videoads.store');
        Route::get('/videoads/edit/{id}', [VideoadsController::class, 'edit'])->name('videoads.edit');
        Route::post('/videoads/update', [VideoadsController::class, 'update'])->name('videoads.update');
        Route::delete('/videoads/destroy/{id}', [VideoadsController::class, 'destroy'])->name('videoads.destroy');
        Route::get('/videoads/updateStatus/{status}/{id}', [VideoadsController::class, 'updateStatus'])->name('videoads.updateStatus');
        Route::get('/videoads/videoadsfetchData', [VideoadsController::class, 'videoadsfetchData']);

        // Festival Routes
        Route::get('/festival', [FestivalController::class, 'index'])->name('festival');
        
        // Festival Type Management
        Route::get('/festival/manage-types', [FestivalController::class, 'manageTypes'])->name('festival.manageTypes');
        Route::post('/festival/store-type', [FestivalController::class, 'storeType'])->name('festival.storeType');
        Route::get('/festival/edit-type/{id}', [FestivalController::class, 'editType'])->name('festival.editType');
        Route::post('/festival/update-type/{id}', [FestivalController::class, 'updateType'])->name('festival.updateType');
        
        // Festival-Specific CRUD (by festival type slug)
        Route::get('/festival/{festivalSlug}', [FestivalController::class, 'festivalList'])->name('festival.list');
        Route::get('/festival/{festivalSlug}/add', [FestivalController::class, 'festivalAdd'])->name('festival.festivalAdd');
        Route::post('/festival/{festivalSlug}/store', [FestivalController::class, 'festivalStore'])->name('festival.festivalStore');
        Route::get('/festival/{festivalSlug}/edit/{id}', [FestivalController::class, 'festivalEdit'])->name('festival.festivalEdit');
        Route::post('/festival/{festivalSlug}/update', [FestivalController::class, 'festivalUpdate'])->name('festival.festivalUpdate');
        Route::delete('/festival/{festivalSlug}/destroy/{id}', [FestivalController::class, 'festivalDestroy'])->name('festival.festivalDestroy');
        Route::get('/festival/{festivalSlug}/updateStatus/{status}/{id}', [FestivalController::class, 'festivalUpdateStatus'])->name('festival.festivalUpdateStatus');
        Route::get('/festival/{festivalSlug}/fetchData', [FestivalController::class, 'festivalFetchData'])->name('festival.festivalFetchData');

        // MXF Video Routes
        Route::get('/mxf-videos', [MxfVideoController::class, 'index'])->name('mxf-videos');
        Route::get('/mxf-videos/add', [MxfVideoController::class, 'add'])->name('mxf-videos.add');
        Route::post('/mxf-videos/store', [MxfVideoController::class, 'store'])->name('mxf-videos.store');
        Route::get('/mxf-videos/edit/{id}', [MxfVideoController::class, 'edit'])->name('mxf-videos.edit');
        Route::post('/mxf-videos/update', [MxfVideoController::class, 'update'])->name('mxf-videos.update');
        Route::delete('/mxf-videos/destroy/{id}', [MxfVideoController::class, 'destroy'])->name('mxf-videos.destroy');
        Route::get('/mxf-videos/updateStatus/{status}/{id}', [MxfVideoController::class, 'updateStatus'])->name('mxf-videos.updateStatus');
        Route::get('/mxf-videos/mxfvideosfetchData', [MxfVideoController::class, 'mxfvideosfetchData']);
        Route::get('/mxf-videos/reprocess/{id}', [MxfVideoController::class, 'reprocess'])->name('mxf-videos.reprocess');
        Route::get('/mxf-videos/status/{id}', [MxfVideoController::class, 'status'])->name('mxf-videos.status');
        Route::post('/mxf-videos/share', [MxfVideoController::class, 'share'])->name('mxf-videos.share');

        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/eventfetchData', [EventController::class, 'eventfetchData']);
        Route::get('/events/view/{id}', [EventController::class, 'viewdata']);
        Route::post('/events/update-status', [EventController::class, 'updateStatus']);
        Route::get('/events/edit/{id}', [EventController::class, 'edit']);
        Route::post('/events/update', [EventController::class, 'update']);
        Route::delete('/events/destroy/{id}', [EventController::class, 'destroy']);

        Route::get('/adsprice', [AdspriceController::class, 'index'])->name('adsprice');
        Route::get('/adsprice/add', [AdspriceController::class, 'add'])->name('adsprice.add');
        Route::post('/adsprice/store', [AdspriceController::class, 'store'])->name('adsprice.store');
        Route::get('/adsprice/edit/{id}', [AdspriceController::class, 'edit'])->name('adsprice.edit');
        Route::post('/adsprice/update', [AdspriceController::class, 'update'])->name('adsprice.update');
        Route::delete('/adsprice/destroy/{id}', [AdspriceController::class, 'destroy'])->name('adsprice.destroy');
        Route::get('/adsprice/updateStatus/{status}/{id}', [AdspriceController::class, 'updateStatus'])->name('adsprice.updateStatus');
        Route::get('/adsprice/adspricefetchData', [AdspriceController::class, 'adspricefetchData']);
      
        Route::get('/athaitap', [AthaitapController::class, 'index']);
        Route::get('/athaitap/athaitapfetchData', [AthaitapController::class, 'athaitapfetchData']);
        // Route::post('/athaitap/update-status', [AthaitapController::class, 'updateStatus']);
        Route::get('/athaitap/updateStatus/{status}/{id}', [AthaitapController::class, 'updateStatus']);
        Route::delete('/athaitap/destroy/{id}', [AthaitapController::class, 'destroy']);
      
        Route::get('/ganapatiutsav', [GanapatiController::class, 'index']);
        Route::get('/ganapatiutsav/ganapatiutsavfetchData', [GanapatiController::class, 'ganapatiutsavfetchData']);
        Route::get('/ganapatiutsav/updateStatus/{status}/{id}', [GanapatiController::class, 'updateStatus']);
        Route::delete('/ganapatiutsav/destroy/{id}', [GanapatiController::class, 'destroy']);
		Route::get('/ganapatiutsav/rotateImage', [GanapatiController::class, 'rotateImage']);

		Route::get('/marugujarat', [MarugujaratController::class, 'index']);
        Route::get('/marugujarat/marugujaratfetchData', [MarugujaratController::class, 'marugujaratfetchData']);
        Route::get('/marugujarat/updateStatus/{status}/{id}', [MarugujaratController::class, 'updateStatus']);
        Route::delete('/marugujarat/destroy/{id}', [MarugujaratController::class, 'destroy']);
        Route::get('/marugujarat/edit/{id}', [MarugujaratController::class, 'edit']);
        Route::post('/marugujarat/update', [MarugujaratController::class, 'update']);

		Route::get('/campuscorner', [CampuscornerController::class, 'index']);
        Route::get('/campuscorner/campuscornerfetchData', [CampuscornerController::class, 'campuscornerfetchData']);
        Route::get('/campuscorner/updateStatus/{status}/{id}', [CampuscornerController::class, 'updateStatus']);
        Route::delete('/campuscorner/destroy/{id}', [CampuscornerController::class, 'destroy']);
        Route::get('/campuscorner/edit/{id}', [CampuscornerController::class, 'edit']);
        Route::post('/campuscorner/update', [CampuscornerController::class, 'update']);
		
		Route::get('/career', [CareerController::class, 'index']);
        Route::get('/career/careerfetchData', [CareerController::class, 'careerfetchData']);
        Route::delete('/career/destroy/{id}', [CareerController::class, 'destroy']);
      
        Route::get('/payment-history', [PaymentController::class, 'index']);
        Route::get('/payment-history/view/{id}', [PaymentController::class, 'view']);
        Route::get('/payment-history/paymentfetchData', [PaymentController::class, 'paymentfetchData']);
  
		Route::get('/magazinereview', [MagazinereviewController::class, 'index']);
        Route::get('/magazinereview/magazinereviewfetchData', [MagazinereviewController::class, 'magazinereviewfetchData']);
        Route::delete('/magazinereview/destroy/{id}', [MagazinereviewController::class, 'destroy']);
      
        Route::get('/newsorderlog', [NewsOrderLogController::class, 'index']);
        Route::get('/newsorderlog/fetch', [NewsOrderLogController::class, 'fetchData']);
        Route::get('/newsorderlog/view/{file}', [NewsOrderLogController::class, 'viewLog']);

        Route::prefix('electionresultmodule')->group(function () {
           Route::get('/', [ElectionResultModuleController::class, 'index']);
           Route::get('/fetchData', [ElectionResultModuleController::class, 'fetchData']);
           Route::get('/add', [ElectionResultModuleController::class, 'add']);
           Route::post('/store', [ElectionResultModuleController::class, 'store']);
           Route::get('/edit/{groupId}', [ElectionResultModuleController::class, 'edit']);
           Route::post('/update/{groupId}', [ElectionResultModuleController::class, 'update']);
           Route::get('/groupStatus/{status}/{id}', [ElectionResultModuleController::class, 'groupStatus']);
           Route::get('/updateStatus/{status}/{id}', [ElectionResultModuleController::class, 'updateStatus']);
           Route::get('/delete/{id}', [ElectionResultModuleController::class, 'delete']);
        });

    });
});

Route::get('/electionresultsChart', [ElectionresultsController::class, 'getelectionresultsAppChart']);
Route::get('/electionresults', [ElectionresultsController::class, 'getelectionresultsApp']);
Route::get('/sudoku', [ElectionresultsController::class, 'sudoku']);
Route::get('/help', [ElectionresultsController::class, 'helpfun']);