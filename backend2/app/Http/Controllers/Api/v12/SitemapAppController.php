<?php

namespace App\Http\Controllers\Api\v12;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\Usercategory;
use App\Models\Usercity;
use App\Models\Category;
use Illuminate\Support\Str;
use Route;

class SitemapAppController extends Controller
{
    public function newsCount()
    {
        $total = DB::table('news')
            ->where('status', 'Active')
            ->count();

        return response()->json([
            'total' => $total
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function newsSitemap(Request $request)
    {
        $offset = (int) $request->input('offset', 0);
        $limit  = (int) $request->input('limit', 1000);

        $newsItems = DB::table('news')
            ->select(
                'news.slug',
                'news.updated_at',
                DB::raw("GROUP_CONCAT(DISTINCT categories.slug ORDER BY categories.id SEPARATOR '/') as categorySlugs"),
                DB::raw("GROUP_CONCAT(DISTINCT news_category.category_id) as catIDs")
            )
            ->join('news_category', 'news.id', '=', 'news_category.news_id')
            ->join('categories', 'news_category.category_id', '=', 'categories.id')
            ->where('news.status', 'Active')
            ->groupBy('news.id')
            ->orderBy('news.created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = [];

        foreach ($newsItems as $news) {
            $data[] = [
                'slug'          => $news->slug,
                'updated_at'    => $news->updated_at,
                'categorySlugs' => $news->categorySlugs,
                'catIDs'        => array_map('intval', explode(',', $news->catIDs)),
            ];
        }

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function categorySitemap()
    {
        $categories = DB::table('categories as c1')
            ->select('c1.id', 'c1.slug', 'c1.updated_at', 'c1.parentID')
            ->where('c1.status', 'Active')
            ->orderBy('c1.parentID', 'ASC')
            ->orderBy('c1.catOrder', 'ASC')
            ->get();

        $grouped = $categories->groupBy('parentID');
        $result = [];

        foreach ($grouped[0] ?? [] as $parent) {
            // Parent category
            $result[] = [
                'slugPath'  => $parent->slug,
                'updated_at'=> $parent->updated_at,
            ];

            // Child categories
            foreach ($grouped[$parent->id] ?? [] as $child) {
                $result[] = [
                    'slugPath'  => $parent->slug . '/' . $child->slug,
                    'updated_at'=> $child->updated_at,
                ];
            }
        }

        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function webstorySitemap()
    {
        $webstories = DB::table('webstory')
            ->select('slug', 'updated_at')
            ->orderBy('webOrder', 'DESC')
            ->get();

        $data = [];

        foreach ($webstories as $story) {
            $data[] = [
                'slug'       => $story->slug,
                'updated_at' => $story->updated_at,
            ];
        }

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
