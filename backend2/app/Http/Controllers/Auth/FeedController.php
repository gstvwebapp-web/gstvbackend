<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\news;
use App\Models\Category;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Route;


class FeedController extends Controller
{
    protected $websiteURL;

    public function __construct()
    {
        $this->websiteURL = 'https://www.gstv.in/';
    }
    
    public function index()
    {
        $topvideos = Redis::get('topvideos'. config('app.webtype'));
        if (!$topvideos) {
            //$topvideos = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
            $topvideos = DB::table('news')
                        ->select('news.*', 'news_category.category_id as catID')
                        ->join('news_category', 'news.id', '=', 'news_category.news_id')
                        ->where('news.status', 'Active')
                        ->where('news_category.category_id', 9)
                        ->orderBy('news.newsOrder', 'DESC')
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
            Redis::set('topvideos'. config('app.webtype'), json_encode($topvideos));
        } else {
            $topvideos = collect(json_decode($topvideos));  
        }
        
        $rssData = [];
        foreach($topvideos as $newsdata) {
            $carbonDate = Carbon::parse($newsdata->created_at);
          	$filePathWithoutExtension = pathinfo($newsdata->videoURL, PATHINFO_DIRNAME) . '/' . pathinfo($newsdata->videoURL, PATHINFO_FILENAME);
            $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
          	$videoplay = '<p><video controls autoplay><source src="'.$newsdata->videoURL.'" type="video/mp4"></video></p>';
            $rssData[] = [
                'title' => $newsdata->title,
                'link' => 'https://www.gstv.in/videos/'.$newsdata->slug,
                'imgURL' => $filePathWithoutExtension,
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $videoplay.$newsdata->description,
                'categories' => 'Videos',
                'categoriesSlug' => 'videos',
            ];
        }

        $topnews = Redis::get('topnews'. config('app.webtype'));
        if (!$topnews) {
            $topnews = DB::table('news')
    ->select(
        'news.id',
        'news.slug',
        'news.title',
        'news.description',
        'news.featureImage',
        'news.videoURL',
        'news.topnewsOrder',
        'news.created_at',
        DB::raw('MAX(news_category.category_id) as catID'), // aggregated to fix SQL mode
        DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id) as category_slugs')
    )
    ->join('news_category', 'news.id', '=', 'news_category.news_id')
    ->leftJoin('categories', 'news_category.category_id', '=', 'categories.id')
    ->where('news.status', 'Active')
    ->where('news.is_home', 1)
    ->where('news_category.category_id', '!=', 9)
    ->groupBy(
        'news.id',
        'news.slug',
        'news.title',
        'news.description',
        'news.featureImage',
        'news.videoURL',
        'news.topnewsOrder',
        'news.created_at'
    )
    ->orderBy('news.topnewsOrder', 'ASC')
    ->limit(10)
    ->get();

            Redis::set('topnews'. config('app.webtype'), json_encode($topnews));
        } else {
            $topnews = collect(json_decode($topnews));  
        }
        
        foreach($topnews as $newsdata) {
            $rsscatData = [];
            $rsscatSlugData = [];
            $carbonDate = Carbon::parse($newsdata->created_at);
            $ids = explode(',', $newsdata->catID);
            $newcategories = DB::table('categories')->whereIn('id', $ids)->where('status', 'Active')->get();
            foreach ($newcategories as $newcategory) {
                $rsscatData[] = $newcategory->title;
                $rsscatSlugData[] = $newcategory->slug;
            }
            $categoryString = implode(', ', $rsscatData);
            $categoryslug = implode('/', $rsscatSlugData);
            $links = 'https://www.gstv.in/news/'.$categoryslug.'/'.$newsdata->slug;
          	$filePathWithoutExtension = $newsdata->featureImage;
          	$videoplay = '';
          	if($newsdata->videoURL!='') {
          		$filePathWithoutExtension = pathinfo($newsdata->videoURL, PATHINFO_DIRNAME) . '/' . pathinfo($newsdata->videoURL, PATHINFO_FILENAME);
            	$filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
              	$videoplay = '<p><video controls autoplay><source src="'.$newsdata->videoURL.'" type="video/mp4"></video></p>';
            }
            $rssData[] = [
                'title' => $newsdata->title,
                'link' => $links,
                'imgURL' => $filePathWithoutExtension,
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $videoplay.$newsdata->description,
                'categories' => $categoryString,
                'categoriesSlug' => $categoryslug,
            ];
        }
        
        /*$topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
        foreach($topwebstory as $newsdata) {
            $carbonDate = Carbon::parse($newsdata->created_at);
            $storyData = json_decode($newsdata->Story_data, true);
            $rssData[] = [
                'title' => $newsdata->title,
                'link' => 'https://www.gstv.in/web-stories/'.$newsdata->slug,
                'imgURL' => $storyData[0]['webimage'],
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $storyData[0]['webtitles'],
                'categories' => '',
                'categoriesSlug' => '',
            ];
        }*/
        $category = Redis::get('category'. config('app.webtype'));
        if (!$category) {
            $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->where('c1.setHome',1)
                    ->where('c1.id','!=',9)
                    ->orderBy('c1.catOrder', 'ASC')
                    ->get();
            Redis::set('category'. config('app.webtype'), json_encode($category));
        } else {
            $category = collect(json_decode($category));  
        }            
        foreach($category as $catnews)
        {
            $id = $catnews->id;
            $newsDatas = Redis::get('newsDatas'. config('app.webtype').$id);
            if (!$newsDatas) {
              $newsDatas = DB::table('news')
    ->select(
        'news.id',
        'news.slug',
        'news.title',
        'news.description',
        'news.featureImage',
        'news.videoURL',
        'news.newsOrder',
        'news.created_at',
        DB::raw('MIN(news_category.category_id) as catID'), // Lighter than MAX
        DB::raw('GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR "/") as category_slugs')
    )
    ->join('news_category', 'news.id', '=', 'news_category.news_id')
    ->join('categories', function ($join) {
        $join->on('news_category.category_id', '=', 'categories.id')
             ->where('categories.status', '=', 'Active');
    })
    ->where('news.status', 'Active')
    ->where('news.is_home', '!=', 1)
    ->where('news_category.category_id', $id)
    ->groupBy('news.id')
    ->orderByDesc('news.created_at')
    ->limit(4)
    ->get();


                Redis::set('newsDatas'. config('app.webtype').$id, json_encode($newsDatas));
            } else {
                $newsDatas = collect(json_decode($newsDatas));  
            }
            foreach($newsDatas as $newsdata) {
            $rsscatData = [];
            $rsscatSlugData = [];
            $carbonDate = Carbon::parse($newsdata->created_at);
            $ids = explode(',', $newsdata->catID);
            $newcategories = DB::table('categories')->whereIn('id', $ids)->where('status', 'Active')->get();
            foreach ($newcategories as $newcategory) {
                $rsscatData[] = $newcategory->title;
                $rsscatSlugData[] = $newcategory->slug;
            }
            $categoryString = implode(', ', $rsscatData);
            $categoryslug = implode('/', $rsscatSlugData);
                $links = 'https://www.gstv.in/news/'.$categoryslug.'/'.$newsdata->slug;
              	$filePathWithoutExtension = $newsdata->featureImage;
              	$videoplay = '';
                if($newsdata->videoURL!='') {
                    $filePathWithoutExtension = pathinfo($newsdata->videoURL, PATHINFO_DIRNAME) . '/' . pathinfo($newsdata->videoURL, PATHINFO_FILENAME);
                    $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
                  	$videoplay = '<p><video controls autoplay><source src="'.$newsdata->videoURL.'" type="video/mp4"></video></p>';
                }
                $rssData[] = [
                    'title' => $newsdata->title,
                    'link' => $links,
                    'imgURL' => $filePathWithoutExtension,
                    'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                    'description' => $videoplay.$newsdata->description,
                    'categories' => $catnews->category_name,
                    'categoriesSlug' => $catnews->slug,
                ];
            }

        }

        $rss = view('rss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
    
    public function getRssData()
    {
        $news = Redis::get('news'. config('app.webtype'));
        if (!$news) {
           $news = DB::table('news')
                    ->select(
                        'news.*',
                        'news_category.category_id as catID'
                    )
                    ->join('news_category', 'news.id', '=', 'news_category.news_id')
                    ->where('news.status', 'Active')
                    ->whereDate('news.created_at', '>=', Carbon::yesterday())
                    ->orderBy('news.newsOrder', 'DESC')
                    ->get();
            Redis::set('news'. config('app.webtype'), json_encode($news));
        } else {
            $news = collect(json_decode($news));  
        }
        $rssData = [];
        foreach($news as $newsdata) {
            $rsscatData = [];
            $rsscatSlugData = [];
            $carbonDate = Carbon::parse($newsdata->created_at);
            $ids = explode(',', $newsdata->catID);
            $newcategories = DB::table('categories')->whereIn('id', $ids)->where('status', 'Active')->get();
            foreach ($newcategories as $newcategory) {
                $rsscatData[] = $newcategory->title;
                $rsscatSlugData[] = $newcategory->slug;
            }
            $categoryString = implode(', ', $rsscatData);
            $categoryslug = implode('/', $rsscatSlugData);
          	$links = 'https://www.gstv.in/news/'.$categoryslug.'/'.$newsdata->slug;
          	if($newsdata->catID==9) {
          		$links = 'https://www.gstv.in/videos/'.$newsdata->slug;
            }
          	$filePathWithoutExtension = $newsdata->featureImage;
          	$videoplay = '';
            if($newsdata->videoURL!='') {
              $filePathWithoutExtension = pathinfo($newsdata->videoURL, PATHINFO_DIRNAME) . '/' . pathinfo($newsdata->videoURL, PATHINFO_FILENAME);
              $filePathWithoutExtension = $filePathWithoutExtension.'_video.gif';
              $videoplay = '<p><video controls autoplay><source src="'.$newsdata->videoURL.'" type="video/mp4"></video></p>';
            }
          
            $rssData[] = [
                'title' => $newsdata->title,
                'link' => $links,
                'imgURL' => $filePathWithoutExtension,
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $videoplay.$newsdata->description,
                'categories' => $categoryString,
                'categoriesSlug' => $categoryslug,
            ];
        }
        $rss = view('rss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getRssCategoryData($slug)
    {
        $slug = Route::current()->parameter('slug'); // Optional: $slug is already passed
        $newcategory = Redis::get('category_' . $slug);
        $news = null;
	
        if (!$newcategory) {
            $newcategory = DB::table('categories')
                ->where('slug', $slug)
                ->where('status', 'Active')
                ->first();

            if ($newcategory) {
                Redis::set('category_' . $slug, json_encode($newcategory));
            }
        } else {
            $newcategory = json_decode($newcategory);
        }
		
        $rssData = [];

        if ($newcategory) {
            $hourRange = 12;
            $news = collect();
            
            while ($hourRange <= 200 && $news->count() < 10) {
               
           $news = DB::table('news')
              ->select(
                  'news.id',
                  'news.slug',
                  'news.title',
                  'news.description',
                  'news.featureImage',
                  'news.videoURL',
                  'news.newsOrder',
                  'news.created_at',
                  DB::raw('MAX(news_category.category_id) as catID') // or GROUP_CONCAT(...)
              )
              ->join('news_category', 'news.id', '=', 'news_category.news_id')
              ->where('news_category.category_id', $newcategory->id)
              ->where('news.status', 'Active')
              ->where('news.created_at', '>=', Carbon::now()->subHours($hourRange))
              ->groupBy(
                  'news.id',
                  'news.slug',
                  'news.title',
                  'news.description',
                  'news.featureImage',
                  'news.videoURL',
                  'news.newsOrder',
                  'news.created_at'
              )
              ->orderBy('news.created_at', 'DESC')
              ->get();


                $hourRange += 12;
            }
         
            if ($news->isNotEmpty()) {
                Redis::set('news_' . $slug, json_encode($news));
            }
        } else {
            $news = collect(); // fallback to empty
        }

        foreach ($news as $newsdata) {
            $rsscatData = [];
            $rsscatSlugData = [];
            $carbonDate = Carbon::parse($newsdata->created_at);
            $ids = explode(',', $newsdata->catID);

            $newcategories = DB::table('categories')
                ->whereIn('id', $ids)
                ->where('status', 'Active')
                ->get();

            foreach ($newcategories as $cat) {
                $rsscatData[] = $cat->title;
                $rsscatSlugData[] = $cat->slug;
            }

            $categoryString = implode(', ', $rsscatData);
            $categoryslug = implode('/', $rsscatSlugData);

            // Build news or video link
            $links = ($newsdata->catID == 9)
                ? 'https://www.gstv.in/videos/' . $newsdata->slug
                : 'https://www.gstv.in/news/' . $categoryslug . '/' . $newsdata->slug;

            // Video or Image
            $filePathWithoutExtension = $newsdata->featureImage;
            $videoplay = '';

            if (!empty($newsdata->videoURL)) {
                $videoPath = pathinfo($newsdata->videoURL);
                $filePathWithoutExtension = $videoPath['dirname'] . '/' . $videoPath['filename'] . '_video.gif';
                $videoplay = '<p><video controls autoplay><source src="' . $newsdata->videoURL . '" type="video/mp4"></video></p>';
            }

            $rssData[] = [
                'title' => $newsdata->title,
                'link' => $links,
                'imgURL' => $filePathWithoutExtension,
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $videoplay . $newsdata->description,
                'categories' => $categoryString,
                'categoriesSlug' => $categoryslug,
            ];
        }

        $rss = view('rss-feed', compact('rssData'))->render();

        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getRsswebstory()
    {
        $rssData = [];
        $topwebstory = Redis::get('topwebstory'. config('app.webtype'));
        if (!$topwebstory) {
            $topwebstory = DB::table('webstory')->where('status','Active')->whereDate('created_at', '>=', Carbon::yesterday())->orderBy('id','DESC')->get();
            Redis::set('topwebstory'. config('app.webtype'), json_encode($topwebstory));
        } else {
            $topwebstory = collect(json_decode($topwebstory));
        }
        foreach($topwebstory as $newsdata) {
            $carbonDate = Carbon::parse($newsdata->created_at);
            $storyData = json_decode($newsdata->Story_data, true);
            $rssData[] = [
                'title' => $newsdata->title,
                'link' => 'https://www.gstv.in/web-stories/'.$newsdata->slug,
                'imgURL' => $storyData[0]['webimage'],
                'pubDate' => $carbonDate->format('D, d M Y H:i:s O'),
                'description' => $storyData[0]['webtitles'],
                'categories' => '',
                'categoriesSlug' => '',
            ];
        }

        $rss = view('rss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
	public function getrss()
    {
      	$newcategory = DB::table('categories')->where('status','Active')->get();
      	$data =  array();
        $data['newcategory'] =  $newcategory;
      	return view('rss', compact('data'));
    }
}
