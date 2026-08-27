<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\news;
use App\Models\Category;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Route;


class IfeedController extends Controller
{
    protected $websiteURL;

    public function __construct()
    {
        $this->websiteURL = 'https://www.gstv.in/';
    }
    
    public function index()
    {
        $topvideos = DB::table('news')->where('status','Active')->where('catID',9)->orderBy('newsOrder','DESC')->inRandomOrder()->limit(10)->get();
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

        $topnews = DB::table('news')
                    ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
                    ->leftJoin('categories', function ($join) {
                        $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                    })
                    ->where('news.status', 'Active')
                    ->where('news.catID', '!=', 9)
                    ->where('news.is_home', 1)
                    ->groupBy('news.id')
                    ->orderBy('news.topnewsOrder', 'ASC')
                    ->limit(10)
                    ->get();
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
        
        $topwebstory = DB::table('webstory')->where('status','Active')->orderBy('id','DESC')->limit(10)->get();
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

        $category = DB::table('categories as c1')
                    ->select('c1.title as category_name', 'c1.*', 'c2.title as parent_category_name')
                    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
                    ->where('c1.status','Active')
                    ->where('c1.setHome',1)
                    ->where('c1.id','!=',9)
                    ->orderBy('c1.catOrder', 'ASC')
                    ->get();
        foreach($category as $catnews)
        {
            $id = $catnews->id;
            $newsDatas = DB::table('news')
                        ->select('news.*', DB::raw('GROUP_CONCAT(categories.slug ORDER BY categories.id) as category_slugs'))
                        ->join('categories', function ($join) use ($id) {
                            $join->on(DB::raw('FIND_IN_SET(categories.id, news.catID)'), '>', DB::raw('0'));
                        })
                        ->whereRaw('FIND_IN_SET(?, news.catID) > 0', [$id])
                        ->where('news.status', 'Active')
                        ->where('news.is_home', '!=', 1)
                        ->groupBy('news.id')
                        ->orderBy('news.newsOrder', 'DESC')
                        ->limit(4)
                        ->get();
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

        $rss = view('izooto.irss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
    
    public function getRssData()
    {
        $news = DB::table('news')->where('status', 'Active')->whereDate('created_at', '>=', Carbon::yesterday())->orderBy('newsOrder','DESC')->get();
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
        $rss = view('izooto.irss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getRssCategoryData($slug)
    {
        $slug = Route::current()->parameter('slug');
        $newcategory = DB::table('categories')->where('slug',$slug)->where('status','Active')->first();
        
        $news = DB::table('news')->whereRaw('FIND_IN_SET(?, catID) > 0', [$newcategory->id])->where('status', 'Active')->whereDate('created_at', '>=', Carbon::yesterday())->orderBy('newsOrder','DESC')->get();
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
        $rss = view('izooto.irss-feed', compact('rssData'))->render();
        return Response::make($rss, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function getRsswebstory()
    {
        $rssData = [];
        
        $topwebstory = DB::table('webstory')->where('status','Active')->whereDate('created_at', '>=', Carbon::yesterday())->orderBy('id','DESC')->get();
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

        $rss = view('izooto.irss-feed', compact('rssData'))->render();
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
