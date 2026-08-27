<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\WebstoryController;
use App\Http\Controllers\Api\HomeAppController;
use App\Http\Controllers\Api\UserAppController;
use App\Http\Controllers\Api\HomeWebController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\NotificationAppController;

use App\Http\Controllers\Api\v1\CategoryController  as V1CategoryController;
use App\Http\Controllers\Api\v1\PageController  as V1PageController;
use App\Http\Controllers\Api\v1\PollController  as V1PollController;
use App\Http\Controllers\Api\v1\HomeController  as V1HomeController;
use App\Http\Controllers\Api\v1\FeedbackController  as V1FeedbackController;
use App\Http\Controllers\Api\v1\SettingController  as V1SettingController;
use App\Http\Controllers\Api\v1\TokenController  as V1TokenController;
use App\Http\Controllers\Api\v1\WebstoryController  as V1WebstoryController;
use App\Http\Controllers\Api\v1\HomeAppController as V1HomeAppController;
use App\Http\Controllers\Api\v1\UserAppController as V1UserAppController;
use App\Http\Controllers\Api\v1\HomeWebController  as V1HomeWebController;
use App\Http\Controllers\Api\v1\GeneralController  as V1GeneralController;
use App\Http\Controllers\Api\v1\NotificationAppController as V1NotificationAppController;

use App\Http\Controllers\Api\v2\CategoryController  as V2CategoryController;
use App\Http\Controllers\Api\v2\PageController  as V2PageController;
use App\Http\Controllers\Api\v2\PollController  as V2PollController;
use App\Http\Controllers\Api\v2\HomeController  as V2HomeController;
use App\Http\Controllers\Api\v2\FeedbackController  as V2FeedbackController;
use App\Http\Controllers\Api\v2\SettingController  as V2SettingController;
use App\Http\Controllers\Api\v2\TokenController  as V2TokenController;
use App\Http\Controllers\Api\v2\WebstoryController  as V2WebstoryController;
use App\Http\Controllers\Api\v2\HomeAppController as V2HomeAppController;
use App\Http\Controllers\Api\v2\UserAppController as V2UserAppController;
use App\Http\Controllers\Api\v2\HomeWebController  as V2HomeWebController;
use App\Http\Controllers\Api\v2\GeneralController  as V2GeneralController;
use App\Http\Controllers\Api\v2\NotificationAppController as V2NotificationAppController;

use App\Http\Controllers\Api\v3\CategoryController  as V3CategoryController;
use App\Http\Controllers\Api\v3\PageController  as V3PageController;
use App\Http\Controllers\Api\v3\PollController  as V3PollController;
use App\Http\Controllers\Api\v3\HomeController  as V3HomeController;
use App\Http\Controllers\Api\v3\FeedbackController  as V3FeedbackController;
use App\Http\Controllers\Api\v3\SettingController  as V3SettingController;
use App\Http\Controllers\Api\v3\TokenController  as V3TokenController;
use App\Http\Controllers\Api\v3\WebstoryController  as V3WebstoryController;
use App\Http\Controllers\Api\v3\HomeAppController as V3HomeAppController;
use App\Http\Controllers\Api\v3\UserAppController as V3UserAppController;
use App\Http\Controllers\Api\v3\HomeWebController  as V3HomeWebController;
use App\Http\Controllers\Api\v3\GeneralController  as V3GeneralController;
use App\Http\Controllers\Api\v3\NotificationAppController as V3NotificationAppController;
use App\Http\Controllers\Api\v3\ReportController as V3ReportController;


use App\Http\Controllers\Api\v4\CategoryController  as V4CategoryController;
use App\Http\Controllers\Api\v4\PageController  as V4PageController;
use App\Http\Controllers\Api\v4\PollController  as V4PollController;
use App\Http\Controllers\Api\v4\HomeController  as V4HomeController;
use App\Http\Controllers\Api\v4\FeedbackController  as V4FeedbackController;
use App\Http\Controllers\Api\v4\SettingController  as V4SettingController;
use App\Http\Controllers\Api\v4\TokenController  as V4TokenController;
use App\Http\Controllers\Api\v4\WebstoryController  as V4WebstoryController;
use App\Http\Controllers\Api\v4\HomeAppController as V4HomeAppController;
use App\Http\Controllers\Api\v4\UserAppController as V4UserAppController;
use App\Http\Controllers\Api\v4\HomeWebController  as V4HomeWebController;
use App\Http\Controllers\Api\v4\GeneralController  as V4GeneralController;
use App\Http\Controllers\Api\v4\NotificationAppController as V4NotificationAppController;
use App\Http\Controllers\Api\v4\ReportController as V4ReportController;

use App\Http\Controllers\Api\v5\HomeAppController as V5HomeAppController;
use App\Http\Controllers\Api\v5\UserAppController as V5UserAppController;
use App\Http\Controllers\Api\v5\NotificationAppController as V5NotificationAppController;
use App\Http\Controllers\Api\v5\ReportController as V5ReportController;

use App\Http\Controllers\Api\v6\HomeAppController as V6HomeAppController;
use App\Http\Controllers\Api\v6\UserAppController as V6UserAppController;
use App\Http\Controllers\Api\v6\NotificationAppController as V6NotificationAppController;
use App\Http\Controllers\Api\v6\ReportController as V6ReportController;
use App\Http\Controllers\Api\v6\AdminAppController as V6AdminAppController;

use App\Http\Controllers\Api\v7\HomeAppController as V7HomeAppController;
use App\Http\Controllers\Api\v7\UserAppController as V7UserAppController;
use App\Http\Controllers\Api\v7\NotificationAppController as V7NotificationAppController;
use App\Http\Controllers\Api\v7\ReportController as V7ReportController;
use App\Http\Controllers\Api\v7\AdminAppController as V7AdminAppController;

use App\Http\Controllers\Api\v8\HomeAppController as V8HomeAppController;
use App\Http\Controllers\Api\v8\UserAppController as V8UserAppController;
use App\Http\Controllers\Api\v8\NotificationAppController as V8NotificationAppController;
use App\Http\Controllers\Api\v8\ReportController as V8ReportController;
use App\Http\Controllers\Api\v8\AdminAppController as V8AdminAppController;
use App\Http\Controllers\Api\v8\GeneralAppController as V8GeneralAppController;

use App\Http\Controllers\Api\v9\HomeAppController as V9HomeAppController;
use App\Http\Controllers\Api\v9\UserAppController as V9UserAppController;
use App\Http\Controllers\Api\v9\NotificationAppController as V9NotificationAppController;
use App\Http\Controllers\Api\v9\ReportController as V9ReportController;
use App\Http\Controllers\Api\v9\AdminAppController as V9AdminAppController;
use App\Http\Controllers\Api\v9\GeneralAppController as V9GeneralAppController;

use App\Http\Controllers\Api\v10\HomeAppController as V10HomeAppController;
use App\Http\Controllers\Api\v10\UserAppController as V10UserAppController;
use App\Http\Controllers\Api\v10\NotificationAppController as V10NotificationAppController;
use App\Http\Controllers\Api\v10\ReportController as V10ReportController;
use App\Http\Controllers\Api\v10\AdminAppController as V10AdminAppController;
use App\Http\Controllers\Api\v10\GeneralAppController as V10GeneralAppController;

use App\Http\Controllers\Api\v11\HomeAppController as V11HomeAppController;
use App\Http\Controllers\Api\v11\UserAppController as V11UserAppController;
use App\Http\Controllers\Api\v11\NotificationAppController as V11NotificationAppController;
use App\Http\Controllers\Api\v11\ReportController as V11ReportController;
use App\Http\Controllers\Api\v11\AdminAppController as V11AdminAppController;
use App\Http\Controllers\Api\v11\GeneralAppController as V11GeneralAppController;
use App\Http\Controllers\Api\v11\SitemapAppController as V11SitemapAppController;
use App\Http\Controllers\Api\v11\LivetvAppController as V11LivetvAppController;

use App\Http\Controllers\Api\v12\HomeAppController as V12HomeAppController;
use App\Http\Controllers\Api\v12\UserAppController as V12UserAppController;
use App\Http\Controllers\Api\v12\NotificationAppController as V12NotificationAppController;
use App\Http\Controllers\Api\v12\ReportController as V12ReportController;
use App\Http\Controllers\Api\v12\AdminAppController as V12AdminAppController;
use App\Http\Controllers\Api\v12\GeneralAppController as V12GeneralAppController;
use App\Http\Controllers\Api\v12\SitemapAppController as V12SitemapAppController;
use App\Http\Controllers\Api\v12\LivetvAppController as V12LivetvAppController;

use App\Http\Controllers\Api\v13\HomeAppController as V13HomeAppController;
use App\Http\Controllers\Api\v13\UserAppController as V13UserAppController;
use App\Http\Controllers\Api\v13\NotificationAppController as V13NotificationAppController;
use App\Http\Controllers\Api\v13\ReportController as V13ReportController;
use App\Http\Controllers\Api\v13\AdminAppController as V13AdminAppController;
use App\Http\Controllers\Api\v13\GeneralAppController as V13GeneralAppController;
use App\Http\Controllers\Api\v13\SitemapAppController as V13SitemapAppController;
use App\Http\Controllers\Api\v13\LivetvAppController as V13LivetvAppController;

use App\Http\Controllers\Api\v14\HomeAppController as V14HomeAppController;
use App\Http\Controllers\Api\v14\UserAppController as V14UserAppController;
use App\Http\Controllers\Api\v14\NotificationAppController as V14NotificationAppController;
use App\Http\Controllers\Api\v14\ReportController as V14ReportController;
use App\Http\Controllers\Api\v14\AdminAppController as V14AdminAppController;
use App\Http\Controllers\Api\v14\GeneralAppController as V14GeneralAppController;
use App\Http\Controllers\Api\v14\SitemapAppController as V14SitemapAppController;
use App\Http\Controllers\Api\v14\LivetvAppController as V14LivetvAppController;

use App\Http\Controllers\Api\v15\HomeAppController as V15HomeAppController;
use App\Http\Controllers\Api\v15\UserAppController as V15UserAppController;
use App\Http\Controllers\Api\v15\NotificationAppController as V15NotificationAppController;
use App\Http\Controllers\Api\v15\ReportController as V15ReportController;
use App\Http\Controllers\Api\v15\AdminAppController as V15AdminAppController;
use App\Http\Controllers\Api\v15\GeneralAppController as V15GeneralAppController;
use App\Http\Controllers\Api\v15\SitemapAppController as V15SitemapAppController;
use App\Http\Controllers\Api\v15\LivetvAppController as V15LivetvAppController;

use App\Http\Controllers\Api\v16\HomeAppController as V16HomeAppController;
use App\Http\Controllers\Api\v16\UserAppController as V16UserAppController;
use App\Http\Controllers\Api\v16\NotificationAppController as V16NotificationAppController;
use App\Http\Controllers\Api\v16\ReportController as V16ReportController;
use App\Http\Controllers\Api\v16\AdminAppController as V16AdminAppController;
use App\Http\Controllers\Api\v16\GeneralAppController as V16GeneralAppController;
use App\Http\Controllers\Api\v16\SitemapAppController as V16SitemapAppController;
use App\Http\Controllers\Api\v16\LivetvAppController as V16LivetvAppController;

use App\Http\Controllers\Api\v17\HomeAppController as V17HomeAppController;
use App\Http\Controllers\Api\v17\UserAppController as V17UserAppController;
use App\Http\Controllers\Api\v17\NotificationAppController as V17NotificationAppController;
use App\Http\Controllers\Api\v17\ReportController as V17ReportController;
use App\Http\Controllers\Api\v17\AdminAppController as V17AdminAppController;
use App\Http\Controllers\Api\v17\GeneralAppController as V17GeneralAppController;
use App\Http\Controllers\Api\v17\SitemapAppController as V17SitemapAppController;
use App\Http\Controllers\Api\v17\LivetvAppController as V17LivetvAppController;

use App\Http\Controllers\Api\v18\HomeAppController as V18HomeAppController;
use App\Http\Controllers\Api\v18\UserAppController as V18UserAppController;
use App\Http\Controllers\Api\v18\NotificationAppController as V18NotificationAppController;
use App\Http\Controllers\Api\v18\ReportController as V18ReportController;
use App\Http\Controllers\Api\v18\AdminAppController as V18AdminAppController;
use App\Http\Controllers\Api\v18\GeneralAppController as V18GeneralAppController;
use App\Http\Controllers\Api\v18\SitemapAppController as V18SitemapAppController;
use App\Http\Controllers\Api\v18\LivetvAppController as V18LivetvAppController;

use App\Http\Controllers\Api\v19\HomeAppController as V19HomeAppController;
use App\Http\Controllers\Api\v19\UserAppController as V19UserAppController;
use App\Http\Controllers\Api\v19\NotificationAppController as V19NotificationAppController;
use App\Http\Controllers\Api\v19\ReportController as V19ReportController;
use App\Http\Controllers\Api\v19\AdminAppController as V19AdminAppController;
use App\Http\Controllers\Api\v19\GeneralAppController as V19GeneralAppController;
use App\Http\Controllers\Api\v19\SitemapAppController as V19SitemapAppController;
use App\Http\Controllers\Api\v19\LivetvAppController as V19LivetvAppController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::group(['middleware' => ['auth:api']], function(){
Route::group(['middleware' => ['cors']], function () {
//Route::group(['middleware' => 'cors'], function () {
//Route::middleware('unlimited')->group(function () {
    Route::post('/generate-token', [TokenController::class, 'generateToken']);
    Route::post('/feedback', [FeedbackController::class, 'index']);
    Route::get('/home/{slug}', [HomeController::class, 'index'])->name('home');
  	Route::get('/videos/{slug}', [HomeController::class, 'videoDetail'])->name('videos');
    Route::get('/topslider', [HomeController::class, 'TopSlider'])->name('topslider');
    Route::get('/topwebslider', [HomeController::class, 'TopWebSlider'])->name('topwebslider');
  	Route::get('/homecategory', [HomeController::class, 'HomeCategory'])->name('homecategory');
    Route::get('/homecat/{id}', [HomeController::class, 'HomeCategorybyId'])->name('homecat.HomeCategorybyId');
    Route::get('/category', [CategoryController::class, 'index'])->name('category');
    Route::get('/category/relatednews/{catid}/{id}', [CategoryController::class, 'relatednews'])->name('category.relatednews');
    Route::get('/category/{slug}/{subslug}/{pageNumber}', [CategoryController::class, 'CategoryNews'])->name('category.CategoryNews');
    Route::get('/news/{slug}', [CategoryController::class, 'NewsDetail'])->name('news.NewsDetail');
    Route::get('/preview/{slug}', [CategoryController::class, 'NewsPreview'])->name('news.NewsPreview');
    Route::post('/news/{id}', [CategoryController::class, 'newsViewer'])->name('news.newsViewer');
    Route::get('/tags/{slug}/{pageNumber}', [CategoryController::class, 'Newstags'])->name('tags.Newstags');
    Route::get('/pages', [PageController::class, 'index'])->name('pages');
    Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
    Route::get('/poll', [PollController::class, 'index'])->name('poll');
    Route::post('/poll/vote', [PollController::class, 'votes'])->name('poll.vote');
  	Route::get('/poll/votegame', [PollController::class, 'votegame'])->name('poll.votegame');
    Route::post('/poll/voteresult', [PollController::class, 'voteResult'])->name('poll.voteresult');
    Route::get('/poll/pollResult', [PollController::class, 'pollResult'])->name('poll.pollResult');
    Route::get('/setting', [SettingController::class, 'index'])->name('setting');
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/webstory/{pageNumber}', [WebstoryController::class, 'index'])->name('webstory');
    Route::get('/web-stories/preview/{slug}', [WebstoryController::class, 'webPreview'])->name('news.webPreview');
  
  	Route::get('/mobile/home', [HomeAppController::class, 'index'])->name('home');
  	Route::get('/mobile/topvideos', [HomeAppController::class, 'topvideos'])->name('topvideos');
    Route::get('/mobile/topnews', [HomeAppController::class, 'topnews'])->name('topnews');
    Route::get('/mobile/topwebstories', [HomeAppController::class, 'topwebstories'])->name('topwebstories');
    Route::get('/mobile/tophomecategory', [HomeAppController::class, 'tophomecategory'])->name('tophomecategory');
    Route::get('/mobile/categorysetting', [HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::get('/mobile/categorynews/{slug}/{subslug}/{pageNumber}', [HomeAppController::class, 'CategoryNews'])->name('categorynews');
    Route::get('/mobile/news/{slug}', [HomeAppController::class, 'NewsDetails'])->name('news');
    Route::get('/mobile/webstory/{pageNumber}', [HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/citylist', [HomeAppController::class, 'citylist'])->name('citylist');
  	Route::post('/mobile/astrology', [HomeWebController::class, 'AstrologyData'])->name('astrology');
    Route::post('/mobile/sendotp', [UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/updateprofile', [UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/viewProfile', [UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::post('/mobile/send-notification', [NotificationAppController::class, 'send']);
    Route::post('/mobile/adddevice', [NotificationAppController::class, 'adddevice']);
  
  	Route::get('/web/home', [HomeWebController::class, 'index'])->name('home');
    Route::get('/web/categorysetting', [HomeWebController::class, 'CategorySetting'])->name('categorysetting');
    Route::get('/web/categorynews/{slug}/{subslug}/{pageNumber}', [HomeWebController::class, 'CategoryNews'])->name('categorynews');
    Route::get('/web/news/{slug}', [HomeWebController::class, 'NewsDetails'])->name('news');
    Route::get('/web/webstory/{pageNumber}', [HomeWebController::class, 'webstory'])->name('webstory');
    Route::get('/web/web-stories/preview/{slug}', [HomeWebController::class, 'webstoryDetail'])->name('webstorydetail');
  	Route::post('/web/astrology', [HomeWebController::class, 'AstrologyData'])->name('astrology');
  	
  	Route::get('/web/topvideo', [HomeWebController::class, 'topvideo'])->name('topvideo');
  	Route::get('/web/topnews', [HomeWebController::class, 'topnews'])->name('topnews');
  	Route::get('/web/topwebstory', [HomeWebController::class, 'topwebstory'])->name('topwebstory');
  
  	Route::post('/general', [GeneralController::class, 'index'])->name('general');
    Route::post('/adsimpression', [HomeWebController::class, 'adsimpression'])->name('adsimpression');  
    Route::get('/web/satrangcategory', [HomeWebController::class, 'categorysatrang'])->name('satrangcategory');
    Route::get('/web/satarangCat', [HomeWebController::class, 'satarangCat'])->name('satarangCat');
    

    // Route::post('/usernotification', [UserAppController::class, 'usernotification'])->name('usernotification');
});


Route::prefix('v1')->middleware('cors')->group(function () {
  	
  	Route::post('/generate-token', [V1TokenController::class, 'generateToken']);
    Route::post('/feedback', [V1FeedbackController::class, 'index']);
    Route::get('/home/{slug}', [V1HomeController::class, 'index'])->name('home');
  	Route::get('/videos/{slug}', [V1HomeController::class, 'videoDetail'])->name('videos');
    Route::get('/topslider', [V1HomeController::class, 'TopSlider'])->name('topslider');
    Route::get('/topwebslider', [V1HomeController::class, 'TopWebSlider'])->name('topwebslider');
  	Route::get('/homecategory', [V1HomeController::class, 'HomeCategory'])->name('homecategory');
    Route::get('/homecat/{id}', [V1HomeController::class, 'HomeCategorybyId'])->name('homecat.HomeCategorybyId');
    Route::get('/category', [V1CategoryController::class, 'index'])->name('category');
    Route::get('/category/relatednews/{catid}/{id}', [V1CategoryController::class, 'relatednews'])->name('category.relatednews');
    Route::get('/category/{slug}/{subslug}/{pageNumber}', [V1CategoryController::class, 'CategoryNews'])->name('category.CategoryNews');
    Route::get('/news/{slug}', [V1CategoryController::class, 'NewsDetail'])->name('news.NewsDetail');
    Route::get('/preview/{slug}', [V1CategoryController::class, 'NewsPreview'])->name('news.NewsPreview');
    Route::post('/news/{id}', [V1CategoryController::class, 'newsViewer'])->name('news.newsViewer');
    Route::get('/tags/{slug}/{pageNumber}', [V1CategoryController::class, 'Newstags'])->name('tags.Newstags');
    Route::get('/pages', [V1PageController::class, 'index'])->name('pages');
    Route::get('/pages/{slug}', [V1PageController::class, 'show'])->name('pages.show');
    Route::get('/poll', [V1PollController::class, 'index'])->name('poll');
    Route::post('/poll/vote', [V1PollController::class, 'votes'])->name('poll.vote');
  	Route::get('/poll/votegame', [V1PollController::class, 'votegame'])->name('poll.votegame');
    Route::post('/poll/voteresult', [V1PollController::class, 'voteResult'])->name('poll.voteresult');
    Route::get('/poll/pollResult', [V1PollController::class, 'pollResult'])->name('poll.pollResult');
    Route::get('/setting', [V1SettingController::class, 'index'])->name('setting');
    Route::get('/feed', [V1FeedController::class, 'index'])->name('feed');
    Route::get('/webstory/{pageNumber}', [V1WebstoryController::class, 'index'])->name('webstory');
    Route::get('/web-stories/preview/{slug}', [V1WebstoryController::class, 'webPreview'])->name('news.webPreview');
  
  	Route::get('/mobile/home', [V1HomeAppController::class, 'index'])->name('home');
  	Route::get('/mobile/topvideos', [V1HomeAppController::class, 'topvideos'])->name('topvideos');
    Route::get('/mobile/topnews', [V1HomeAppController::class, 'topnews'])->name('topnews');
    Route::get('/mobile/topwebstories', [V1HomeAppController::class, 'topwebstories'])->name('topwebstories');
    Route::get('/mobile/tophomecategory', [V1HomeAppController::class, 'tophomecategory'])->name('tophomecategory');
    Route::get('/mobile/categorysetting', [V1HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::get('/mobile/categorynews/{slug}/{subslug}/{pageNumber}', [V1HomeAppController::class, 'CategoryNews'])->name('categorynews');
    Route::post('/mobile/categorynews', [V1HomeAppController::class, 'CategoryNews'])->name('categorynews');
    Route::post('/mobile/news', [V1HomeAppController::class, 'NewsDetails'])->name('news');
    Route::get('/mobile/webstory/{pageNumber}', [V1HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V1HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/citylist', [V1HomeAppController::class, 'citylist'])->name('citylist');
  	Route::post('/mobile/astrology', [V1HomeWebController::class, 'AstrologyData'])->name('astrology');
    Route::post('/mobile/sendotp', [V1UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V1UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V1UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V1UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V1UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V1UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V1UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V1UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V1UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/viewProfile', [V1UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::post('/mobile/send-notification', [V1NotificationAppController::class, 'send']);
    Route::post('/mobile/adddevice', [V1NotificationAppController::class, 'adddevice']);
    Route::get('/mobile/getNotificationList', [V1NotificationAppController::class, 'getNotificationList']);
    //Route::post('/mobile/getNotificationList', [V1NotificationAppController::class, 'getNotificationList']);

    Route::post('/mobile/getVideoSlug', [V1NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/getWebStorySlug', [V1NotificationAppController::class, 'getWebStorySlug']);
    Route::get('/mobile/breakingnews', [V1HomeAppController::class, 'breakingnews']);
    Route::post('/mobile/newslike', [V1HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V1HomeAppController::class, 'videolike']);
    Route::post('/mobile/newsbookmark', [V1HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V1HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/removebookmark', [V1HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/feedback', [V1HomeAppController::class, 'feedback']);
    Route::get('/mobile/poll', [V1HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V1HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/addpoints', [V1HomeAppController::class, 'addpoints']);
    Route::post('/mobile/userpoints', [V1HomeAppController::class, 'userpoints']);
    Route::get('/mobile/pollresult', [V1HomeAppController::class, 'pollresult']);
    Route::get('/mobile/iframe', [V1HomeAppController::class, 'iframe']);
    Route::get('/mobile/inshorts', [V1HomeAppController::class, 'inshorts']);
  	Route::post('/mobile/deleteAccount', [V1HomeAppController::class, 'deleteAccount']);
  	Route::get('/mobile/showlogin', [V1HomeAppController::class, 'showlogin']);
  	Route::get('/mobile/electionresults', [V1HomeAppController::class, 'electionresults']);

  	Route::get('/web/home', [V1HomeWebController::class, 'index'])->name('home');
    Route::get('/web/categorysetting', [V1HomeWebController::class, 'CategorySetting'])->name('categorysetting');
    Route::get('/web/categorynews/{slug}/{subslug}/{pageNumber}', [V1HomeWebController::class, 'CategoryNews'])->name('categorynews');
    Route::get('/web/news/{slug}', [V1HomeWebController::class, 'NewsDetails'])->name('news');
    Route::get('/web/webstory/{pageNumber}', [V1HomeWebController::class, 'webstory'])->name('webstory');
    Route::get('/web/web-stories/preview/{slug}', [V1HomeWebController::class, 'webstoryDetail'])->name('webstorydetail');
  	Route::post('/web/astrology', [V1HomeWebController::class, 'AstrologyData'])->name('astrology');
  	
  	Route::get('/web/topvideo', [V1HomeWebController::class, 'topvideo'])->name('topvideo');
  	Route::get('/web/topnews', [V1HomeWebController::class, 'topnews'])->name('topnews');
  	Route::get('/web/topwebstory', [V1HomeWebController::class, 'topwebstory'])->name('topwebstory');
  
  	Route::post('/general', [V1GeneralController::class, 'index'])->name('general');
    Route::post('/adsimpression', [V1HomeWebController::class, 'adsimpression'])->name('adsimpression');  
    
    Route::get('/web/satrangcategory', [V1HomeWebController::class, 'categorysatrang'])->name('satrangcategory');
    Route::get('/web/satarangCat', [V1HomeWebController::class, 'satarangCat'])->name('satarangCat');
  
});

Route::prefix('v2')->middleware('cors')->group(function () {
 	Route::post('/mobile/tophomecategory', [V2HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
  	Route::get('/mobile/electionresults', [V2HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V2HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V2HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V2HomeAppController::class, 'getusernews']);
    Route::post('/mobile/updateprofile', [V2UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V2HomeAppController::class, 'CategoryNews'])->name('categorynews');
 
    Route::get('/mobile/categorysetting', [V2HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/adddevice', [V2NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V2UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V2HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V2HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V2HomeAppController::class, 'topnews'])->name('topnews');
    Route::get('/mobile/breakingnews', [V2HomeAppController::class, 'breakingnews']);
    Route::get('/web/satrangcategory', [V2HomeWebController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/addpoints', [V2HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V2NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V2HomeAppController::class, 'NewsDetails'])->name('news');
    
    Route::post('/mobile/sendotp', [V2UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V2UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V2UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V2UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V2UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V2UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V2UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V2UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V2UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V2HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V2HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/removebookmark', [V2HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V2HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V2HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V2HomeAppController::class, 'userpoints']);
    Route::post('/mobile/feedback', [V2HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V2HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/poll', [V2HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V2HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/addpoints', [V2HomeAppController::class, 'addpoints']);
    Route::get('/mobile/pollresult', [V2HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V2HomeAppController::class, 'iframe']);
  	Route::post('/mobile/deleteAccount', [V2HomeAppController::class, 'deleteAccount']);
  	Route::get('/mobile/showlogin', [V2HomeAppController::class, 'showlogin']);
  	Route::get('/mobile/electionresults', [V2HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V2NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V2NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V2HomeWebController::class, 'AstrologyData'])->name('astrology'); 
    Route::get('/mobile/epaper', [V2HomeAppController::class, 'epaper']);
    Route::get('/mobile/magazine', [V2HomeAppController::class, 'magazine']);
    Route::post('/mobile/epaperdetail', [V2HomeAppController::class, 'epaperdetail']);
 
    Route::post('/mobile/reedemPointsByUser', [V2HomeAppController::class, 'reedemPointsByUser']);
});


Route::prefix('v3')->middleware('cors')->group(function () {
 	Route::post('/mobile/tophomecategory', [V3HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
  	Route::get('/mobile/electionresults', [V3HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V3HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V3HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V3HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V3HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V3HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V3HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V3UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V3HomeAppController::class, 'CategoryNews'])->name('categorynews');
 
    Route::get('/mobile/categorysetting', [V3HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/adddevice', [V3NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V3UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V3HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V3HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V3HomeAppController::class, 'topnews'])->name('topnews');
    Route::post('/mobile/addpoints', [V3HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V3NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V3HomeAppController::class, 'NewsDetails'])->name('news');
    Route::get('/mobile/poll', [V3HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V3HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/addpoints', [V3HomeAppController::class, 'addpoints']);
    Route::get('/mobile/pollresult', [V3HomeAppController::class, 'pollresult']);
    
    Route::post('/mobile/sendotp', [V3UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V3UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V3UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V3UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V3UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V3UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V3UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V3UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V3UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V3HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V3HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/removebookmark', [V3HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V3HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V3HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V3HomeAppController::class, 'userpoints']);
    Route::post('/mobile/feedback', [V3HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V3HomeAppController::class, 'breakingnews']);

    Route::get('/mobile/iframe', [V3HomeAppController::class, 'iframe']);
  	Route::post('/mobile/deleteAccount', [V3HomeAppController::class, 'deleteAccount']);
  	Route::get('/mobile/showlogin', [V3HomeAppController::class, 'showlogin']);
  	Route::get('/mobile/electionresults', [V3HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V3NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V3NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V3HomeWebController::class, 'AstrologyData'])->name('astrology'); 
    Route::get('/mobile/epaper', [V3HomeAppController::class, 'epaper']);
    Route::get('/mobile/epaper/{date}', [V3HomeAppController::class, 'epaper']);
    Route::get('/mobile/magazine', [V3HomeAppController::class, 'magazine']);
    Route::post('/mobile/epaperdetail', [V3HomeAppController::class, 'epaperdetail']);
 
    Route::post('/mobile/reedemPointsByUser', [V3HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V3HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V3HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V3HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
  	Route::get('/mobile/reports', [V3ReportController::class, 'totalreport']);
  	Route::post('/mobile/newsdaterange', [V3ReportController::class, 'newsdaterange']);
  	Route::post('/mobile/videosdaterange', [V3ReportController::class, 'videosdaterange']);
  	Route::post('/mobile/webstorydaterange', [V3ReportController::class, 'webstorydaterange']);
  	Route::post('/mobile/usersdaterange', [V3ReportController::class, 'usersdaterange']);
  	Route::post('/mobile/login', [V3ReportController::class, 'login']);
  
  
});

Route::prefix('v4')->middleware('cors')->group(function () {
  Route::post('/mobile/tophomecategory', [V4HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::get('/mobile/electionresults', [V4HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V4HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V4HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V4HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V4HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V4HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V4HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V4UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V4HomeAppController::class, 'CategoryNews']);

    Route::get('/mobile/categorysetting', [V4HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/adddevice', [V4NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V4UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V4HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V4HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V4HomeAppController::class, 'topnews'])->name('topnews');
    Route::post('/mobile/addpoints', [V4HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V4NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V4HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V4HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/tagdetail', [V4HomeAppController::class, 'tagdetail'])->name('tagdetail');

    Route::post('/mobile/sendotp', [V4UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V4UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V4UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V4UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V4UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V4UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V4UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V4UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V4UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V4HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V4HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/removebookmark', [V4HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V4HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V4HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V4HomeAppController::class, 'userpoints']);
    Route::post('/mobile/feedback', [V4HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V4HomeAppController::class, 'breakingnews']);
    Route::post('/mobile/poll', [V4HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V4HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/addpoints', [V4HomeAppController::class, 'addpoints']);
    Route::get('/mobile/pollresult', [V4HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V4HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V4HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V4HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V4HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V4NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V4NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V4HomeWebController::class, 'AstrologyData'])->name('astrology'); 
    Route::get('/mobile/epaper', [V4HomeAppController::class, 'epaper']);
    Route::get('/mobile/epaper/{date}', [V4HomeAppController::class, 'epaper']);
    Route::get('/mobile/magazine', [V4HomeAppController::class, 'magazine']);
    Route::post('/mobile/epaperdetail', [V4HomeAppController::class, 'epaperdetail']);

    Route::post('/mobile/reedemPointsByUser', [V4HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V4HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V4HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V4HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V4ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V4ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V4ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V4ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V4ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V4ReportController::class, 'login']);

    Route::post('/mobile/getlanguages', [V4HomeAppController::class, 'getlanguages'])->name('getlanguages');

    Route::get('/mobile/rashifal', [V4HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V4HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V4HomeAppController::class, 'rashifalalldata']);

    Route::get('/mobile/livecricket', [V4HomeAppController::class, 'livecricket']);

});

Route::prefix('v5')->middleware('cors')->group(function () {
  Route::post('/mobile/tophomecategory', [V5HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::get('/mobile/electionresults', [V5HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V5HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V5HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V5HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V5HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V5HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V5HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V5UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V5HomeAppController::class, 'CategoryNews']);

    Route::get('/mobile/categorysetting', [V5HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingbyuser', [V5HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V5HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V5NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V5UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V5HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V5HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V5HomeAppController::class, 'topnews'])->name('topnews');
    Route::post('/mobile/addpoints', [V5HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V5NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V5HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V5HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V5HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V5HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V5HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V5HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V5UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V5UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V5UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V5UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V5UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V5UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V5UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V5UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V5UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V5HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V5HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V5HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V5HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V5HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V5HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V5HomeAppController::class, 'userpoints']);
    Route::post('/mobile/userviewpoints', [V5HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V5HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V5HomeAppController::class, 'breakingnews']);
    Route::post('/mobile/poll', [V5HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V5HomeAppController::class, 'submitpoll']);
    Route::get('/mobile/pollresult', [V5HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V5HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V5HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V5HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V5HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V5NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V5NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V5HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::get('/mobile/epaper', [V5HomeAppController::class, 'epaper']);
    Route::get('/mobile/epaper/{date}', [V5HomeAppController::class, 'epaper']);
    Route::get('/mobile/magazine', [V5HomeAppController::class, 'magazine']);
    Route::post('/mobile/epaperdetail', [V5HomeAppController::class, 'epaperdetail']);

    Route::post('/mobile/reedemPointsByUser', [V5HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V5HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V5HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V5HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V5ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V5ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V5ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V5ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V5ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V5ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V5HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V5HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V5HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V5HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V5HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V5HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V5HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V5HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V5HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V5HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V5HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V5HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V5HomeAppController::class, 'videoDetail']);

});

Route::prefix('v6')->middleware('cors')->group(function () {
  Route::post('/mobile/tophomecategory', [V6HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::get('/mobile/electionresults', [V6HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V6HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V6HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V6HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V6HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V6HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V6HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V6UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V6HomeAppController::class, 'CategoryNews']);

    Route::get('/mobile/categorysetting', [V6HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingbyuser', [V6HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V6HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V6NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V6UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V6HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V6HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V6HomeAppController::class, 'topnews'])->name('topnews');
    Route::post('/mobile/addpoints', [V6HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V6NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V6HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V6HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V6HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V6HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V6HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V6HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V6UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V6UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V6UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V6UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V6UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V6UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V6UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V6UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V6UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V6HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V6HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V6HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V6HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V6HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V6HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V6HomeAppController::class, 'userpoints']);
    Route::post('/mobile/userviewpoints', [V6HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V6HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V6HomeAppController::class, 'breakingnews']);
    Route::post('/mobile/poll', [V6HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V6HomeAppController::class, 'submitpoll']);
    Route::get('/mobile/pollresult', [V6HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V6HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V6HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V6HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V6HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V6NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V6NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V6HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V6HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V6HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V6HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V6HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V6HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V6HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V6HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V6HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V6ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V6ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V6ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V6ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V6ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V6ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V6HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V6HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V6HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V6HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V6HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V6HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V6HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V6HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V6HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V6HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V6HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V6HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V6HomeAppController::class, 'videoDetail']);


    Route::post('/mobile/admin/login', [V6AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V6AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V6AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V6AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V6AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V6AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V6AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V6AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V6AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V6AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V6AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V6AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V6AdminAppController::class, 'deletemedia']);
 
});

Route::prefix('v7')->middleware('cors')->group(function () {
  Route::post('/mobile/tophomecategory', [V7HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::get('/mobile/electionresults', [V7HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V7HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V7HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V7HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V7HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V7HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V7HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V7UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V7HomeAppController::class, 'CategoryNews']);

    Route::get('/mobile/categorysetting', [V7HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingbyuser', [V7HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V7HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V7NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V7UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V7HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V7HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V7HomeAppController::class, 'topnews'])->name('topnews');
    Route::post('/mobile/addpoints', [V7HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V7NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V7HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V7HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V7HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V7HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V7HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V7HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V7UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V7UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V7UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V7UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V7UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V7UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V7UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V7UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V7UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V7HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V7HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V7HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V7HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V7HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V7HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V7HomeAppController::class, 'userpoints']);
    Route::post('/mobile/userviewpoints', [V7HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V7HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V7HomeAppController::class, 'breakingnews']);
    Route::post('/mobile/poll', [V7HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V7HomeAppController::class, 'submitpoll']);
    Route::get('/mobile/pollresult', [V7HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V7HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V7HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V7HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V7HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V7NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V7NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V7HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V7HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V7HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V7HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V7HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V7HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V7HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V7HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V7HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V7ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V7ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V7ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V7ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V7ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V7ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V7HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V7HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V7HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V7HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V7HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V7HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V7HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V7HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V7HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V7HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V7HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V7HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V7HomeAppController::class, 'videoDetail']);


    Route::post('/mobile/admin/login', [V7AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V7AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V7AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V7AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V7AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V7AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V7AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V7AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V7AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V7AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V7AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V7AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V7AdminAppController::class, 'deletemedia']);
});


Route::prefix('v8')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V8HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V8HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V8HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V8HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V8HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V8HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V8HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V8HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V8HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V8UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V8HomeAppController::class, 'CategoryNews']);

    Route::get('/mobile/categorysetting', [V8HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingbyuser', [V8HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V8HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V8NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V8UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V8HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V8HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V8HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V8HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V8HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V8NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V8HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V8HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V8HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V8HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V8HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V8HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V8UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V8UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V8UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V8UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V8UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V8UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V8UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V8UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V8UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V8HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V8HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V8HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V8HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V8HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V8HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V8HomeAppController::class, 'userpoints']);
    Route::post('/mobile/userviewpoints', [V8HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V8HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V8HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V8HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V8HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V8HomeAppController::class, 'submitpoll']);
    Route::get('/mobile/pollresult', [V8HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V8HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V8HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V8HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V8HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V8NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V8NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V8HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V8HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V8HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V8HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V8HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V8HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V8HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V8HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V8HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V8ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V8ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V8ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V8ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V8ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V8ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V8HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V8HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V8HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V8HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V8HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V8HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V8HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V8HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V8HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V8HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V8HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V8HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V8HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V8AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V8AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V8AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V8AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V8AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V8AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V8AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V8AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V8AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V8AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V8AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V8AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V8AdminAppController::class, 'deletemedia']);

    Route::post('/mobile/submitekasana', [V8GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V8GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V8GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V8GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V8GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V8GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V8GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V8GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V8GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V8GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V8GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V8GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V8GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V8GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V8GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V8GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V8GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V8GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V8GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V8GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V8GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V8GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V8GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V8GeneralAppController::class, 'campuscornerdetails']);

});

Route::prefix('v9')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V9HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V9HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V9HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V9HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V9HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V9HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V9HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V9HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V9HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V9UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V9HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V9HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::get('/mobile/categorysettingweb', [V9HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V9HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V9HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V9NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V9UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V9HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V9HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V9HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V9HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V9HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V9NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V9HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V9HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V9HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V9HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V9HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V9HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V9UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V9UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V9UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V9UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V9UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V9UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V9UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V9UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V9UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V9HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V9HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V9HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V9HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V9HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V9HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V9HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V9HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V9HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V9HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V9HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V9HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V9HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V9HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V9HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V9HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V9HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V9HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V9HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V9HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V9NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V9NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V9HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V9HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V9HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V9HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V9HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V9HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V9HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V9HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V9HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V9ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V9ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V9ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V9ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V9ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V9ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V9HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V9HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V9HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V9HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V9HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V9HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V9HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V9HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V9HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V9HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V9HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V9HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V9HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V9AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V9AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V9AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V9AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V9AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V9AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V9AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V9AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V9AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V9AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V9AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V9AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V9AdminAppController::class, 'deletemedia']);

    Route::post('/mobile/submitekasana', [V9GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V9GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V9GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V9GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V9GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V9GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V9GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V9GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V9GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V9GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V9GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V9GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V9GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V9GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V9GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V9GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V9GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V9GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V9GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V9GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V9GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V9GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V9GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V9GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V9HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V9HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V9HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V9HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V9HomeAppController::class, 'webhook']);

    Route::post('/mobile/careersubmit', [V9HomeAppController::class, 'careersubmit']);
});

Route::prefix('v10')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V10HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V10HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V10HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V10HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V10HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V10HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V10HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V10HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V10HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V10UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V10HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V10HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V10HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V10HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V10HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V10HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V10HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V10HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V10HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V10NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V10UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V10HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V10HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V10HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V10HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V10HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V10NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V10HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V10HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentweb', [V10HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V10HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V10HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V10HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V10UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V10UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V10UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V10UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V10UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V10UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V10UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V10UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V10UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V10HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V10HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V10HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V10HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V10HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V10HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V10HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V10HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V10HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V10HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V10HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V10HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V10HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V10HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V10HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V10HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V10HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V10HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V10HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V10HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V10NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V10NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V10HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V10HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V10HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V10HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V10HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V10HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V10HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V10HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V10HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V10ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V10ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V10ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V10ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V10ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V10ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V10HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V10HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V10HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V10HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V10HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V10HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V10HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V10HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V10HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V10HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V10HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V10HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V10HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V10AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V10AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V10AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V10AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V10AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V10AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V10AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V10AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V10AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V10AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V10AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V10AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V10AdminAppController::class, 'deletemedia']);

    Route::post('/mobile/submitekasana', [V10GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V10GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V10GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V10GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V10GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V10GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V10GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V10GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V10GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V10GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V10GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V10GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V10GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V10GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V10GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V10GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V10GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V10GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V10GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V10GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V10GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V10GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V10GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V10GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V10HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V10HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V10HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V10HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V10HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V10HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V10HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V10HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V10HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V10HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V10HomeAppController::class, 'magazinereviewsubmit']);
});

Route::prefix('v11')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V11HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V11HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V11HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V11HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V11HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V11HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V11HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V11HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V11HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V11UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V11HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V11HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V11HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V11HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V11HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V11HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V11HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V11HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V11HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V11NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V11UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V11HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V11HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V11HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V11HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V11HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V11NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V11HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V11HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V11HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V11HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V11HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V11HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V11HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V11UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V11UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V11UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V11UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V11UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V11UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V11UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V11UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V11UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V11HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V11HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V11HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V11HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V11HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V11HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V11HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V11HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V11HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V11HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V11HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V11HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V11HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V11HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V11HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V11HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V11HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V11HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V11HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V11HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V11NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V11NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V11HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V11HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V11HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V11HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V11HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V11HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V11HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V11HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V11HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V11ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V11ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V11ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V11ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V11ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V11ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V11HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V11HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V11HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V11HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V11HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V11HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V11HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V11HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V11HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V11HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V11HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V11HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V11HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V11AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V11AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V11AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V11AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V11AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V11AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V11AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V11AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V11AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V11AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V11AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V11AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V11AdminAppController::class, 'deletemedia']);

    Route::post('/mobile/submitekasana', [V11GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V11GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V11GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V11GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V11GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V11GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V11GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V11GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V11GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V11GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V11GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V11GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V11GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V11GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V11GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V11GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V11GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V11GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V11GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V11GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V11GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V11GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V11GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V11GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V11HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V11HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V11HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V11HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V11HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V11HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V11HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V11HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V11HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V11HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V11HomeAppController::class, 'magazinereviewsubmit']);

	Route::get('/mobile/newscount', [V11SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V11SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V11SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V11SitemapAppController::class, 'webstorySitemap']);
    Route::get('/mobile/livetvlink', [V11LivetvAppController::class, 'livetvlink']);
});

Route::prefix('v12')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V12HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V12HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V12HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V12HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V12HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V12HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V12HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V12HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V12HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V12UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V12HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V12HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V12HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V12HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V12HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V12HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V12HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V12HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V12HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V12NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V12UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V12HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V12HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V12HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V12HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V12HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V12NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V12HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V12HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V12HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V12HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V12HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V12HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V12HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V12UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V12UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V12UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V12UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V12UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V12UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V12UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V12UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V12UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V12HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V12HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V12HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V12HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V12HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V12HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V12HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V12HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V12HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V12HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V12HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V12HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V12HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V12HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V12HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V12HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V12HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V12HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V12HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V12HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V12NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V12NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V12HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V12HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V12HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V12HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V12HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V12HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V12HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V12HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V12HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V12ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V12ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V12ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V12ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V12ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V12ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V12HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V12HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V12HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V12HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V12HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V12HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V12HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V12HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V12HomeAppController::class, 'gstvfasttrack']);
    Route::get('/mobile/topVideos', [V12HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V12HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V12HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V12HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V12AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V12AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V12AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V12AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V12AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V12AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V12AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V12AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V12AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V12AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V12AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V12AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V12AdminAppController::class, 'deletemedia']);

    Route::post('/mobile/submitekasana', [V12GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V12GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V12GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V12GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V12GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V12GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V12GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V12GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V12GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V12GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V12GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V12GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V12GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V12GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V12GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V12GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V12GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V12GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V12GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V12GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V12GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V12GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V12GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V12GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V12HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V12HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V12HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V12HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V12HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V12HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V12HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V12HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V12HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V12HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V12HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V12SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V12SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V12SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V12SitemapAppController::class, 'webstorySitemap']);
    Route::get('/mobile/livetvlink', [V12LivetvAppController::class, 'livetvlink']);
  
    Route::get('/mobile/getCategoryOneVideos', [V12HomeAppController::class, 'getCategoryOneVideos']);
});

Route::prefix('v13')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V13HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V13HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V13HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V13HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V13HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V13HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V13HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V13HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V13HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V13UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V13HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V13HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V13HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V13HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V13HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V13HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V13HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V13HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V13HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V13NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V13UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V13HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V13HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V13HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V13HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V13HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V13NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V13HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V13HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V13HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V13HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V13HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V13HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V13HomeAppController::class, 'getelectionresultsApp']);

    Route::post('/mobile/sendotp', [V13UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V13UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V13UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V13UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V13UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V13UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V13UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V13UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V13UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V13HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V13HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V13HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V13HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V13HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V13HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V13HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V13HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V13HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V13HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V13HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V13HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V13HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V13HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V13HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V13HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V13HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V13HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V13HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V13HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V13NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V13NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V13HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V13HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V13HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V13HomeAppController::class, 'magazine']);
    Route::get('/mobile/getepapercat', [V13HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V13HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V13HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V13HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V13HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V13ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V13ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V13ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V13ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V13ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V13ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V13HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V13HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V13HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V13HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V13HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V13HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V13HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V13HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V13HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V13HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V13HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V13HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V13HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V13AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V13AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V13AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V13AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V13AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V13AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V13AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V13AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V13AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V13AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V13AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V13AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V13AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V13AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V13AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V13AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V13AdminAppController::class, 'reporterforceupdate']);

    Route::post('/mobile/submitekasana', [V13GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V13GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V13GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V13GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V13GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V13GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V13GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V13GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V13GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V13GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V13GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V13GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V13GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V13GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V13GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V13GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V13GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V13GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V13GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V13GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V13GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V13GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V13GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V13GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V13HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V13HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V13HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V13HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V13HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V13HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V13HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V13HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V13HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V13HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V13HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V13SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V13SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V13SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V13SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V13LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V13HomeAppController::class, 'getCategoryOneVideos']);
});

Route::prefix('v14')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V14HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V14HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V14HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V14HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V14HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V14HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V14HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V14HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V14HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V14UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V14HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V14HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V14HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V14HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V14HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V14HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V14HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V14HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V14HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V14NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V14UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V14HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V14HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V14HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V14HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V14HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V14NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V14HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V14HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V14HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V14HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V14HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V14HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V14HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V14HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V14UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V14UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V14UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V14UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V14UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V14UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V14UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V14UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V14UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V14HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V14HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V14HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V14HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V14HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V14HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V14HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V14HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V14HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V14HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V14HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V14HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V14HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V14HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V14HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V14HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V14HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V14HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V14HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V14HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V14NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V14NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V14HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V14HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V14HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V14HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V14HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V14HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V14HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V14HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V14HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V14HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V14ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V14ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V14ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V14ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V14ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V14ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V14HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V14HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V14HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V14HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V14HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V14HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V14HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V14HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V14HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V14HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V14HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V14HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V14HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V14AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V14AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V14AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V14AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V14AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V14AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V14AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V14AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V14AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V14AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V14AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V14AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V14AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V14AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V14AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V14AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V14AdminAppController::class, 'reporterforceupdate']);

    Route::post('/mobile/submitekasana', [V14GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V14GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V14GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V14GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V14GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V14GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V14GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V14GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V14GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V14GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V14GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V14GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V14GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V14GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V14GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V14GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V14GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V14GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V14GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V14GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V14GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V14GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V14GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V14GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V14HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V14HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V14HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V14HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V14HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V14HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V14HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V14HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V14HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V14HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V14HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V14SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V14SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V14SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V14SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V14LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V14HomeAppController::class, 'getCategoryOneVideos']);
});

Route::prefix('v15')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V15HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V15HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V15HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V15HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V15HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V15HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V15HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V15HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V15HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V15UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V15HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V15HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V15HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V15HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V15HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V15HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V15HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V15HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V15HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V15NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V15UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V15HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V15HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V15HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V15HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V15HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V15NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V15HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V15HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V15HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V15HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V15HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V15HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V15HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V15HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V15UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V15UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V15UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V15UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V15UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V15UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V15UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V15UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V15UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V15HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V15HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V15HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V15HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V15HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V15HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V15HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V15HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V15HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V15HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V15HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V15HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V15HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V15HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V15HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V15HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V15HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V15HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V15HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V15HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V15NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V15NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V15HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V15HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V15HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V15HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V15HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V15HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V15HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V15HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V15HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V15HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V15ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V15ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V15ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V15ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V15ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V15ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V15HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V15HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V15HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V15HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V15HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V15HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V15HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V15HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V15HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V15HomeAppController::class, 'topVideos']);
    Route::get('/mobile/topwebstory', [V15HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V15HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V15HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V15AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V15AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V15AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V15AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V15AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V15AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V15AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V15AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V15AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V15AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V15AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V15AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V15AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V15AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V15AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V15AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V15AdminAppController::class, 'reporterforceupdate']);

    Route::post('/mobile/submitekasana', [V15GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V15GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V15GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V15GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V15GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V15GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V15GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V15GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V15GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V15GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V15GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V15GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V15GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V15GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V15GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V15GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V15GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V15GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V15GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V15GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V15GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V15GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V15GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V15GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V15HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V15HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V15HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V15HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V15HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V15HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V15HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V15HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V15HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V15HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V15HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V15SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V15SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V15SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V15SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V15LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V15HomeAppController::class, 'getCategoryOneVideos']);
});

Route::prefix('v16')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V16HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V16HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V16HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V16HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V16HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V16HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V16HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V16HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V16HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V16UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V16HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V16HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V16HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V16HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V16HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V16HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V16HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V16HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V16HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V16NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V16UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V16HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V16HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V16HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V16HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V16HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V16NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V16HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V16HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V16HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V16HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V16HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V16HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V16HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V16HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V16UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V16UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V16UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V16UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V16UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V16UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V16UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V16UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V16UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V16HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V16HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V16HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V16HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V16HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V16HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V16HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V16HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V16HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V16HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V16HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V16HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V16HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V16HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V16HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V16HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V16HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V16HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V16HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V16HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V16NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V16NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V16HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V16HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V16HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V16HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V16HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V16HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V16HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V16HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V16HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V16HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V16ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V16ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V16ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V16ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V16ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V16ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V16HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V16HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V16HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V16HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V16HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V16HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V16HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V16HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V16HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V16HomeAppController::class, 'topVideos']);
    Route::post('/mobile/topVideosBySlug', [V16HomeAppController::class, 'topVideosBySlug']);
    Route::get('/mobile/topwebstory', [V16HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V16HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V16HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V16AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V16AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V16AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V16AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V16AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V16AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V16AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V16AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V16AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V16AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V16AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V16AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V16AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V16AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V16AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V16AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V16AdminAppController::class, 'reporterforceupdate']);

    Route::post('/mobile/submitekasana', [V16GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V16GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V16GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V16GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V16GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V16GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V16GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V16GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V16GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V16GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V16GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V16GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V16GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V16GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V16GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V16GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V16GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V16GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V16GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V16GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V16GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V16GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V16GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V16GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V16HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V16HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V16HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V16HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V16HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V16HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V16HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V16HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V16HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V16HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V16HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V16SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V16SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V16SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V16SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V16LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V16HomeAppController::class, 'getCategoryOneVideos']);
    
    Route::get('/mobile/getStaticMenuHomepage', [V16HomeAppController::class, 'getStaticMenuHomepage']);
    Route::post('/mobile/texttoaudio', [V16HomeAppController::class, 'texttoaudio']);
    Route::get('/mobile/getYoutubeShorts', [V16HomeAppController::class, 'getYoutubeShorts']);
});

Route::prefix('v17')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V17HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V17HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V17HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V17HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V17HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V17HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V17HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V17HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V17HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V17UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V17HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V17HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V17HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V17HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V17HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V17HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V17HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::post('/mobile/categorysettingbyuser', [V17HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V17HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V17NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V17UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V17HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V17HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V17HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V17HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V17HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V17NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V17HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V17HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V17HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V17HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V17HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V17HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V17HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V17HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V17UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V17UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V17UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V17UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V17UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V17UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V17UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V17UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V17UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V17HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V17HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V17HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V17HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V17HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V17HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V17HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V17HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V17HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V17HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V17HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V17HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V17HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V17HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V17HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V17HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V17HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V17HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V17HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V17HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V17NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V17NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V17HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V17HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V17HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V17HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V17HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V17HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V17HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V17HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V17HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V17HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V17ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V17ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V17ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V17ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V17ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V17ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V17HomeAppController::class, 'rashifal']);
    Route::post('/mobile/rashifaldata', [V17HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V17HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V17HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V17HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V17HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V17HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V17HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V17HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V17HomeAppController::class, 'topVideos']);
    Route::post('/mobile/topVideosBySlug', [V17HomeAppController::class, 'topVideosBySlug']);
    Route::get('/mobile/topwebstory', [V17HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V17HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V17HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V17AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V17AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V17AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V17AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V17AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V17AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V17AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V17AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V17AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V17AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V17AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V17AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V17AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V17AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V17AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V17AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V17AdminAppController::class, 'reporterforceupdate']);

    Route::post('/mobile/submitekasana', [V17GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V17GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V17GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V17GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V17GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V17GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V17GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V17GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V17GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V17GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V17GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V17GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V17GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V17GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V17GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V17GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V17GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V17GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V17GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V17GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V17GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V17GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V17GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V17GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V17HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V17HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V17HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V17HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V17HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V17HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V17HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V17HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V17HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V17HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V17HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V17SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V17SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V17SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V17SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V17LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V17HomeAppController::class, 'getCategoryOneVideos']);
    
    Route::get('/mobile/getStaticMenuHomepage', [V17HomeAppController::class, 'getStaticMenuHomepage']);
    Route::post('/mobile/texttoaudio', [V17HomeAppController::class, 'texttoaudio']);
    Route::get('/mobile/getYoutubeShorts', [V17HomeAppController::class, 'getYoutubeShorts']);
});

Route::prefix('v18')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V18HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V18HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V18HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V18HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V18HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V18HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V18HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V18HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V18HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V18UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V18HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V18HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V18HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V18HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V18HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V18HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V18HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::get('/mobile/categorysettingwebForTopMenu', [V18HomeAppController::class, 'categorysettingwebForTopMenu'])->name('categorysettingwebForTopMenu');
    Route::post('/mobile/categorysettingbyuser', [V18HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V18HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V18NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V18UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V18HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V18HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V18HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V18HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V18HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V18NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V18HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V18HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V18HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V18HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V18HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V18HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V18HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V18HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V18UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V18UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V18UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V18UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V18UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V18UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V18UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V18UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V18UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V18HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V18HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V18HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V18HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V18HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V18HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V18HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V18HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V18HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V18HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V18HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V18HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V18HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V18HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V18HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V18HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V18HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V18HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V18HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V18HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V18NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V18NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V18HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V18HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V18HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V18HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V18HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V18HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V18HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V18HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V18HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V18HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V18ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V18ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V18ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V18ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V18ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V18ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V18HomeAppController::class, 'rashifal']);
    Route::get('/mobile/rashifalwithDharmCat', [V18HomeAppController::class, 'rashifalwithDharmCat']);
    Route::post('/mobile/rashifaldata', [V18HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V18HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V18HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V18HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V18HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V18HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V18HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V18HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V18HomeAppController::class, 'topVideos']);
    Route::post('/mobile/topVideosBySlug', [V18HomeAppController::class, 'topVideosBySlug']);
    Route::post('/mobile/topVideosForMofileApp', [V18HomeAppController::class, 'topVideosForMofileApp']);
    Route::get('/mobile/topwebstory', [V18HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V18HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V18HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V18AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V18AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V18AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V18AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V18AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V18AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V18AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V18AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V18AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V18AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V18AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V18AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V18AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V18AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V18AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V18AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V18AdminAppController::class, 'reporterforceupdate']);
    Route::post('/mobile/admin/getenglishnews', [V18AdminAppController::class, 'getenglishnews']);

    Route::post('/mobile/submitekasana', [V18GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V18GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V18GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V18GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V18GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V18GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V18GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V18GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V18GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V18GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V18GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V18GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V18GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V18GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V18GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V18GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V18GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V18GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V18GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V18GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V18GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V18GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V18GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V18GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V18HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V18HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V18HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V18HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V18HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V18HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V18HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V18HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V18HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V18HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V18HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V18SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V18SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V18SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V18SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V18LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V18HomeAppController::class, 'getCategoryOneVideos']);
    
    Route::get('/mobile/getStaticMenuHomepage', [V18HomeAppController::class, 'getStaticMenuHomepage']);
    Route::post('/mobile/texttoaudio', [V18HomeAppController::class, 'texttoaudio']);
    Route::get('/mobile/getYoutubeShorts', [V18HomeAppController::class, 'getYoutubeShorts']);
    Route::get('/mobile/getReligionWithRashi', [V18HomeAppController::class, 'getReligionWithRashi']);
    Route::get('/mobile/forceupdate', [V18HomeAppController::class, 'forceupdate']);
});

Route::prefix('v19')->middleware('cors')->group(function () {
    Route::post('/mobile/tophomecategory', [V19HomeAppController::class, 'tophomecategory'])->name('tophomecategory'); 
    Route::post('/mobile/tophomecategoryweb', [V19HomeAppController::class, 'tophomecategoryweb'])->name('tophomecategoryweb'); 
    Route::get('/mobile/electionresults', [V19HomeAppController::class, 'electionresults']);
    Route::get('/mobile/category', [V19HomeAppController::class, 'getcategorylist']);
    Route::post('/mobile/addusernews', [V19HomeAppController::class, 'addusernews']);
    Route::post('/mobile/getusernews', [V19HomeAppController::class, 'getusernews']);
    Route::post('/mobile/editusernews', [V19HomeAppController::class, 'editusernews']);
    Route::post('/mobile/updateusernews', [V19HomeAppController::class, 'updateusernews']);
    Route::post('/mobile/deleteusernewsimg', [V19HomeAppController::class, 'deleteusernewsimg']);
    Route::post('/mobile/updateprofile', [V19UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/categorynews', [V19HomeAppController::class, 'CategoryNews']);

    Route::post('/mobile/categorysetting', [V19HomeAppController::class, 'CategorySetting'])->name('categorysetting');
    Route::post('/mobile/categorysettingwithcategory', [V19HomeAppController::class, 'categorysettingwithcategory'])->name('categorysettingwithcategory');
    Route::get('/mobile/categorysettingwithmenu', [V19HomeAppController::class, 'categorysettingwithmenu'])->name('categorysettingwithmenu');
    Route::post('/mobile/categorysettingwithplan', [V19HomeAppController::class, 'categorysettingwithplan'])->name('categorysettingwithplan');
    Route::get('/mobile/categorysettingwithlanguage', [V19HomeAppController::class, 'categorysettingwithlanguage'])->name('categorysettingwithlanguage');
    Route::get('/mobile/categorysettingweb', [V19HomeAppController::class, 'CategorySettingWeb'])->name('CategorySettingWeb');
    Route::get('/mobile/categorysettingwebForTopMenu', [V19HomeAppController::class, 'categorysettingwebForTopMenu'])->name('categorysettingwebForTopMenu');
    Route::post('/mobile/categorysettingbyuser', [V19HomeAppController::class, 'categorysettingbyuser'])->name('categorysettingbyuser');
    Route::get('/mobile/getallCity', [V19HomeAppController::class, 'getallCity'])->name('getallCity');
    Route::post('/mobile/adddevice', [V19NotificationAppController::class, 'adddevice']);
    Route::post('/mobile/viewProfile', [V19UserAppController::class, 'viewProfile'])->name('viewProfile');
    Route::get('/mobile/webstory/{pageNumber}', [V19HomeAppController::class, 'webstory'])->name('webstory');
    Route::get('/mobile/webstorydetail/{slug}', [V19HomeAppController::class, 'webstoryDetail'])->name('webstorydetail');
    Route::get('/mobile/topnews', [V19HomeAppController::class, 'topnews'])->name('topnews');
     Route::get('/mobile/topnewsweb', [V19HomeAppController::class, 'topnewsweb'])->name('topnewsweb');
    Route::post('/mobile/addpoints', [V19HomeAppController::class, 'addpoints']);
    Route::get('/mobile/getNotificationList', [V19NotificationAppController::class, 'getNotificationList']);
    Route::post('/mobile/news', [V19HomeAppController::class, 'NewsDetails'])->name('news');
    Route::post('/mobile/newsnextContent', [V19HomeAppController::class, 'newsnextContent'])->name('newsnextContent');
    Route::post('/mobile/newsnextContentrelatednews', [V19HomeAppController::class, 'newsnextContentrelatednews'])->name('newsnextContentrelatednews');
    Route::post('/mobile/newsnextContentweb', [V19HomeAppController::class, 'newsnextContentweb'])->name('newsnextContentweb');
    Route::post('/mobile/tagdetail', [V19HomeAppController::class, 'tagdetail'])->name('tagdetail');
    Route::get('/mobile/getelectionresults', [V19HomeAppController::class, 'getelectionresults']);
    Route::get('/mobile/getelectionresultsApp', [V19HomeAppController::class, 'getelectionresultsApp']);

    // ── Election Module (dynamic groups with columns & party data) ──────────────
    // Route::get('/mobile/electionmodule', [\App\Http\Controllers\Api\v14\ElectionModuleController::class, 'index']);
    Route::get('/mobile/electionmodule', [V19HomeAppController::class, 'electionmodule']);

    Route::post('/mobile/sendotp', [V19UserAppController::class, 'sendOtp'])->name('sendOtp');
    Route::post('/mobile/resendotp', [V19UserAppController::class, 'resendOtp'])->name('resendOtp');
    Route::post('/mobile/resendotpnull', [V19UserAppController::class, 'resendOtpnull'])->name('resendOtpnull');
    Route::post('/mobile/verifyotp', [V19UserAppController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('/mobile/verifyMPIN', [V19UserAppController::class, 'verifyMPIN'])->name('verifyMPIN');
    Route::post('/mobile/updateprofile', [V19UserAppController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/mobile/usercategory', [V19UserAppController::class, 'userCategory'])->name('userCategory');
    Route::post('/mobile/usercity', [V19UserAppController::class, 'userCity'])->name('userCity');
    Route::post('/mobile/getcategorycity', [V19UserAppController::class, 'getCategoryCity'])->name('getCategoryCity');
    Route::post('/mobile/newsbookmark', [V19HomeAppController::class, 'newsbookmark']);
    Route::post('/mobile/bookmarklist', [V19HomeAppController::class, 'bookmarklist']);
    Route::post('/mobile/bookmarklistWithpagination', [V19HomeAppController::class, 'bookmarklistWithpagination']);
    Route::post('/mobile/removebookmark', [V19HomeAppController::class, 'removebookmark']);
    Route::post('/mobile/newslike', [V19HomeAppController::class, 'newslike']);
    Route::post('/mobile/videolike', [V19HomeAppController::class, 'videolike']);
    Route::post('/mobile/userpoints', [V19HomeAppController::class, 'userpoints']);
    Route::post('/mobile/getUserPoints', [V19HomeAppController::class, 'getUserPoints']);
    Route::post('/mobile/userviewpoints', [V19HomeAppController::class, 'userviewpoints']);
    Route::post('/mobile/feedback', [V19HomeAppController::class, 'feedback']);
    Route::get('/mobile/breakingnews', [V19HomeAppController::class, 'breakingnews']);
    Route::get('/mobile/topbreakingnewsWeb', [V19HomeAppController::class, 'topbreakingnewsWeb']);
    Route::post('/mobile/poll', [V19HomeAppController::class, 'poll']);
    Route::post('/mobile/submitpoll', [V19HomeAppController::class, 'submitpoll']);
    Route::post('/mobile/submitdrawpoll', [V19HomeAppController::class, 'submitdrawpoll']);
    Route::get('/mobile/pollresult', [V19HomeAppController::class, 'pollresult']);

    Route::get('/mobile/iframe', [V19HomeAppController::class, 'iframe']);
    Route::post('/mobile/deleteAccount', [V19HomeAppController::class, 'deleteAccount']);
    Route::get('/mobile/showlogin', [V19HomeAppController::class, 'showlogin']);
    Route::get('/mobile/electionresults', [V19HomeAppController::class, 'electionresults']);
    Route::post('/mobile/getWebStorySlug', [V19NotificationAppController::class, 'getWebStorySlug']);
    Route::post('/mobile/getVideoSlug', [V19NotificationAppController::class, 'getVideoSlug']);
    Route::post('/mobile/astrology', [V19HomeAppController::class, 'AstrologyData'])->name('astrology'); 
    Route::post('/mobile/epaper', [V19HomeAppController::class, 'epaper']);
    Route::post('/mobile/epaperdetail', [V19HomeAppController::class, 'epaperdetail']);
    Route::post('/mobile/magazine', [V19HomeAppController::class, 'magazine']);
    Route::post('/mobile/buddhiprakash', [V19HomeAppController::class, 'buddhiprakash']);
    Route::get('/mobile/getepapercat', [V19HomeAppController::class, 'getepapercat']);

    Route::post('/mobile/reedemPointsByUser', [V19HomeAppController::class, 'reedemPointsByUser']);
    Route::get('/mobile/satrangcategory', [V19HomeAppController::class, 'categorysatrang'])->name('satrangcategory');
    Route::post('/mobile/submitplanpayment', [V19HomeAppController::class, 'submitplanpayment'])->name('submitplanpayment');
    Route::post('/mobile/paymenthistory', [V19HomeAppController::class, 'paymenthistory'])->name('paymenthistory');
    Route::get('/mobile/reports', [V19ReportController::class, 'totalreport']);
    Route::post('/mobile/newsdaterange', [V19ReportController::class, 'newsdaterange']);
    Route::post('/mobile/videosdaterange', [V19ReportController::class, 'videosdaterange']);
    Route::post('/mobile/webstorydaterange', [V19ReportController::class, 'webstorydaterange']);
    Route::post('/mobile/usersdaterange', [V19ReportController::class, 'usersdaterange']);
    Route::post('/mobile/login', [V19ReportController::class, 'login']);   
    Route::get('/mobile/rashifal', [V19HomeAppController::class, 'rashifal']);
    Route::get('/mobile/rashifalwithDharmCat', [V19HomeAppController::class, 'rashifalwithDharmCat']);
    Route::post('/mobile/rashifaldata', [V19HomeAppController::class, 'rashifaldata']);
    Route::post('/mobile/rashifalalldata', [V19HomeAppController::class, 'rashifalalldata']);
    Route::get('/mobile/livecricket', [V19HomeAppController::class, 'livecricket']);
    Route::post('/mobile/searchresult', [V19HomeAppController::class, 'searchresult']);
    Route::get('/mobile/getnews', [V19HomeAppController::class, 'getnews']);
    Route::post('/mobile/podcast', [V19HomeAppController::class, 'podcast']);
    Route::post('/mobile/podcastdetail', [V19HomeAppController::class, 'podcastdetail']);
    Route::get('/mobile/gstvfasttrack', [V19HomeAppController::class, 'gstvfasttrack']);
    Route::post('/mobile/topVideos', [V19HomeAppController::class, 'topVideos']);
    Route::post('/mobile/topVideosBySlug', [V19HomeAppController::class, 'topVideosBySlug']);
    Route::post('/mobile/topVideosForMofileApp', [V19HomeAppController::class, 'topVideosForMofileApp']);
    Route::get('/mobile/topwebstory', [V19HomeAppController::class, 'topwebstory']);
    Route::get('/mobile/page/{slug}', [V19HomeAppController::class, 'pageShow']);
    Route::post('/mobile/videoDetail', [V19HomeAppController::class, 'videoDetail']);

    Route::post('/mobile/admin/login', [V19AdminAppController::class, 'login']);
    Route::post('/mobile/admin/newslist', [V19AdminAppController::class, 'newslist']);
    Route::get('/mobile/admin/addnews', [V19AdminAppController::class, 'addnews']);
    Route::post('/mobile/admin/medialist', [V19AdminAppController::class, 'medialist']);
    Route::post('/mobile/admin/storenews', [V19AdminAppController::class, 'storenews']);
    Route::post('/mobile/admin/editnews', [V19AdminAppController::class, 'editnews']);
    Route::post('/mobile/admin/updatenews', [V19AdminAppController::class, 'updatenews']);
    Route::post('/mobile/admin/searchRelatedNews', [V19AdminAppController::class, 'searchRelatedNews']);
    Route::post('/mobile/admin/subCategory', [V19AdminAppController::class, 'subCategory']);
    Route::post('/mobile/admin/addmedia', [V19AdminAppController::class, 'addmedia']);
    Route::post('/mobile/admin/updateStatus', [V19AdminAppController::class, 'updateStatus']);
    Route::post('/mobile/admin/destroy', [V19AdminAppController::class, 'destroy']);
    Route::post('/mobile/admin/deletemedia', [V19AdminAppController::class, 'deletemedia']);
    Route::post('/mobile/admin/newsorder', [V19AdminAppController::class, 'newsorder']);
    Route::post('/mobile/admin/userslist', [V19AdminAppController::class, 'userslist']);
    Route::post('/mobile/admin/userProfileNewsList', [V19AdminAppController::class, 'userProfileNewsList']);
    Route::get('/mobile/admin/reporterforceupdate', [V19AdminAppController::class, 'reporterforceupdate']);
    Route::post('/mobile/admin/getenglishnews', [V19AdminAppController::class, 'getenglishnews']);

    Route::post('/mobile/submitekasana', [V19GeneralAppController::class, 'submitekasana']);
    Route::post('/mobile/getekasana', [V19GeneralAppController::class, 'getekasana']);
    Route::post('/mobile/editekasana', [V19GeneralAppController::class, 'editekasana']);
    Route::post('/mobile/updateekasana', [V19GeneralAppController::class, 'updateekasana']);
    Route::post('/mobile/athaitaplist', [V19GeneralAppController::class, 'athaitaplist']);
    Route::post('/mobile/athaitapdetails', [V19GeneralAppController::class, 'athaitapdetails']);

    Route::post('/mobile/submitganapati', [V19GeneralAppController::class, 'submitganapati']);
    Route::post('/mobile/getganapati', [V19GeneralAppController::class, 'getganapati']);
    Route::post('/mobile/editganapati', [V19GeneralAppController::class, 'editganapati']);
    Route::post('/mobile/updateganapati', [V19GeneralAppController::class, 'updateganapati']);
    Route::post('/mobile/ganapatilist', [V19GeneralAppController::class, 'ganapatilist']);
    Route::post('/mobile/ganapatidetails', [V19GeneralAppController::class, 'ganapatidetails']);


    Route::post('/mobile/submitmarugujarat', [V19GeneralAppController::class, 'submitmarugujarat']);
    Route::post('/mobile/getmarugujarat', [V19GeneralAppController::class, 'getmarugujarat']);
    Route::post('/mobile/editmarugujarat', [V19GeneralAppController::class, 'editmarugujarat']);
    Route::post('/mobile/updatemarugujarat', [V19GeneralAppController::class, 'updatemarugujarat']);
    Route::post('/mobile/marugujaratlist', [V19GeneralAppController::class, 'marugujaratlist']);
    Route::post('/mobile/marugujaratdetails', [V19GeneralAppController::class, 'marugujaratdetails']);

    Route::post('/mobile/submitcampuscorner', [V19GeneralAppController::class, 'submitcampuscorner']);
    Route::post('/mobile/getcampuscorner', [V19GeneralAppController::class, 'getcampuscorner']);
    Route::post('/mobile/editcampuscorner', [V19GeneralAppController::class, 'editcampuscorner']);
    Route::post('/mobile/updatecampuscorner', [V19GeneralAppController::class, 'updatecampuscorner']);
    Route::post('/mobile/campuscornerlist', [V19GeneralAppController::class, 'campuscornerlist']);
    Route::post('/mobile/campuscornerdetails', [V19GeneralAppController::class, 'campuscornerdetails']);
    // Route::get('/mobile/getMagazineCategories', [V19HomeAppController::class, 'getMagazineCategories']);
    Route::get('/mobile/epaperrigthsidebar', [V19HomeAppController::class, 'epaperrigthsidebar']);
    Route::get('/mobile/getElectionResultsApi', [V19HomeAppController::class, 'getElectionResultsApi']);

    Route::post('/mobile/createSubscription', [V19HomeAppController::class, 'createSubscription']);
    Route::post('/mobile/razorpay-webhook', [V19HomeAppController::class, 'webhook']);
    Route::post('/mobile/adduserplansubscribe', [V19HomeAppController::class, 'adduserplansubscribe']);

    Route::post('/mobile/careersubmit', [V19HomeAppController::class, 'careersubmit']);
    Route::post('/mobile/PlanSetting', [V19HomeAppController::class, 'PlanSetting']);
    Route::get('/mobile/gstvmagazinenews', [V19HomeAppController::class, 'gstvmagazinenews']);
    Route::get('/mobile/getTopSatrangCategories', [V19HomeAppController::class, 'getTopSatrangCategories']);
    Route::post('/mobile/magazinereviewsubmit', [V19HomeAppController::class, 'magazinereviewsubmit']);
  
    Route::get('/mobile/newscount', [V19SitemapAppController::class, 'newsCount']);
    Route::get('/mobile/newssitemap', [V19SitemapAppController::class, 'newsSitemap']);
    Route::get('/mobile/categoriessitemap', [V19SitemapAppController::class, 'categorySitemap']);
    Route::get('/mobile/webstorysitemap', [V19SitemapAppController::class, 'webstorySitemap']);

    Route::get('/mobile/livetvlink', [V19LivetvAppController::class, 'livetvlink']);
    Route::get('/mobile/getCategoryOneVideos', [V19HomeAppController::class, 'getCategoryOneVideos']);
    
    Route::get('/mobile/getStaticMenuHomepage', [V19HomeAppController::class, 'getStaticMenuHomepage']);
    Route::post('/mobile/texttoaudio', [V19HomeAppController::class, 'texttoaudio']);
    Route::get('/mobile/getYoutubeShorts', [V19HomeAppController::class, 'getYoutubeShorts']);
    Route::get('/mobile/getReligionWithRashi', [V19HomeAppController::class, 'getReligionWithRashi']);
    Route::get('/mobile/forceupdate', [V19HomeAppController::class, 'forceupdate']);
});
/*Route::middleware('apiauth')->group(function () {
    Route::get('/category', [CategoryController::class, 'index'])->name('category');
});*/