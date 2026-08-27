# Complete CategoryNews Function with Fallback (v18)

This shows the complete function with the fallback logic integrated.

## Complete Function Code

```php
public function CategoryNews(Request $request)
{
    $slug      = $request->slug;
    $subslug   = $request->subslug;
    $device_id = $request->device_id;
    $user_id   = $request->user_id;
    $perPage   = (int) $request->input('per_page', 6);
    $page      = (int) $request->input('page', $request->input('pageNumber', 1));
    $webType   = $this->webtype;

    $cacheSlug = ($subslug === 'undefined' || empty($subslug)) ? $slug : $subslug;

    /* ================= CATEGORY (CACHE) ================= */
    $categoryKey = "category_detailsnews:{$cacheSlug}_{$webType}";
    $category = Redis::get($categoryKey);
    $lastThreeDayCategories = [1, 47, 48, 146, 147, 149, 151];
    if (!$category) {
        $category = DB::table('categories')
            ->select('id','parentId','title as catTitle','slug as catSlug','metatitle','metadesc')
            ->where('slug', $cacheSlug)
            ->where('status', 'Active')
            ->first();

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        Redis::setex($categoryKey, 1800, json_encode($category));
    } else {
        $category = json_decode($category);
    }

    /* ================= FINAL CACHE ================= */
    $redisKey = "category_news_fast:{$cacheSlug}:p{$page}:{$perPage}_{$webType}";
    if ($cached = Redis::get($redisKey)) {
        return response()->json(json_decode($cached, true));
    }

    /* ================= STEP 1: GET NEWS IDS (FAST) ================= */
    $newsIdQuery = DB::table('news_category as nc')
        ->where('nc.category_id', $category->id)
        ->pluck('nc.news_id');

    if ($category->id == 9) {
        $videoIds = DB::table('news')
             //->where('is_vertical_video', 9)
             ->where('is_video_horizontal', 1)
             ->where('videoURL',"!=",'')
            ->pluck('id');
        $newsIdQuery = $newsIdQuery->merge($videoIds)->unique();
    }
    
    // ===== NEW: FALLBACK LOGIC ===== //
    $applyThreeDayFilter = in_array($category->id, $lastThreeDayCategories) ||  in_array($category->parentId, $lastThreeDayCategories);

    // Check if last 3 days has data for filtered categories
    $dateFilter = null;
    if ($applyThreeDayFilter) {
        $lastThreeDaysCount = DB::table('news')
            ->whereIn('id', $newsIdQuery)
            ->where('status', 'Active')
            ->where('created_at', '>=', now()->subDays(3))
            ->count();

        if ($lastThreeDaysCount > 0) {
            $dateFilter = now()->subDays(3);  // Use last 3 days
        } else {
            $dateFilter = now()->subDays(365);  // Fallback to last 12 months
        }
    }
    // ===== END: FALLBACK LOGIC ===== //

    $totalQuery = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active');

    if ($dateFilter !== null) {
        $totalQuery->where('created_at', '>=', $dateFilter);
    }

    $total = $totalQuery->count();

    $newsIdsQuery = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active');

    if ($dateFilter !== null) {
        $newsIdsQuery->where('created_at', '>=', $dateFilter);
    }

    $newsIds = $newsIdsQuery
        ->orderByDesc('created_at')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->pluck('id');

    /* ================= STEP 2: FETCH NEWS ================= */
    $newsItemsQuery = DB::table('news')
        ->whereIn('id', $newsIds)
        ->select(
            'id',
            'slug',
            'title',
            'description',
            'featureImage',
            'videoURL',
            'created_at',
            'is_vertical_video',
            'video_webp',
            'is_video_horizontal'
        );

    if ($dateFilter !== null) {
        $newsItemsQuery->where('created_at', '>=', $dateFilter);
    }

    $newsItems = $newsItemsQuery
        ->orderByDesc('created_at')
        ->get();

    /* ================= STEP 3: CATEGORY MAP ================= */
    $categoryMap = DB::table('news_category as nc')
        ->join('categories as c', 'c.id', '=', 'nc.category_id')
        ->whereIn('nc.news_id', $newsIds)
        ->select('nc.news_id','nc.category_id','c.slug')
        ->get()
        ->groupBy('news_id');

    /* ================= STEP 4: BOOKMARK ================= */
    $bookmarkMap = $user_id
        ? DB::table('bookmark')
            ->where('user_id', $user_id)
            ->whereIn('news_id', $newsIds)
            ->pluck('status','news_id')
            ->toArray()
        : [];

    /* ================= STEP 5: LIKE / DISLIKE ================= */
    $likeMap = [];
    if ($slug === 'videos') {
        $likeMap = DB::table('videolike')
            ->whereIn('video_id', $newsIds)
            ->select(
                'video_id',
                DB::raw('SUM(status=1) as like_count'),
                DB::raw('SUM(status=0) as dislike_count'),
                DB::raw("SUM(status=1 AND ipAddress='{$device_id}') as liked_by_user"),
                DB::raw("SUM(status=0 AND ipAddress='{$device_id}') as disliked_by_user")
            )
            ->groupBy('video_id')
            ->get()
            ->keyBy('video_id');
    }

    /* ================= STEP 6: TRANSFORM ================= */
    $newsItems->transform(function ($item) use ($categoryMap, $bookmarkMap, $likeMap) {

        $cats = $categoryMap[$item->id] ?? collect();
        $item->catID = $cats->pluck('category_id')->implode(',');
        $item->category_slugs = $cats->pluck('slug')->implode(',');

        $item->bookmark = (int) ($bookmarkMap[$item->id] ?? 0);

        $like = $likeMap[$item->id] ?? null;
        $item->like_count       = (int) ($like->like_count ?? 0);
        $item->dislike_count    = (int) ($like->dislike_count ?? 0);
        $item->liked_by_user    = (int) ($like->liked_by_user ?? 0);
        $item->disliked_by_user = (int) ($like->disliked_by_user ?? 0);

        $item->description = \Str::limit($item->description, 120);
        return $item;
    });

    /* ================= MANUAL PAGINATOR ================= */
    $news = new \Illuminate\Pagination\LengthAwarePaginator(
        $newsItems,
        $total,
        $perPage,
        $page,
        ['path' => $request->url()]
    );

    /* ================= FINAL RESPONSE ================= */
    $data = [
        'news'         => $news,
        'catTitle'     => $category->catTitle,
        'catSlug'      => $category->catSlug,
        'catMetatitle' => $category->metatitle,
        'catMetadesc'  => $category->metadesc,
    ];

    Redis::setex($redisKey, 600, json_encode($data));
    return response()->json($data);
}
```

## What Changed

### Lines 62-74 (NEW SECTION):
```php
// ===== NEW: FALLBACK LOGIC ===== //
$applyThreeDayFilter = in_array($category->id, $lastThreeDayCategories) ||  in_array($category->parentId, $lastThreeDayCategories);

// Check if last 3 days has data for filtered categories
$dateFilter = null;
if ($applyThreeDayFilter) {
    $lastThreeDaysCount = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active')
        ->where('created_at', '>=', now()->subDays(3))
        ->count();

    if ($lastThreeDaysCount > 0) {
        $dateFilter = now()->subDays(3);  // Use last 3 days
    } else {
        $dateFilter = now()->subDays(365);  // Fallback to last 12 months
    }
}
// ===== END: FALLBACK LOGIC ===== //
```

### Lines 76-80 (CHANGED):
Before:
```php
if ($applyThreeDayFilter) {
    $totalQuery->where('created_at', '>=', now()->subDays(3));
}
```

After:
```php
if ($dateFilter !== null) {
    $totalQuery->where('created_at', '>=', $dateFilter);
}
```

### Lines 87-91 (CHANGED):
Before:
```php
if ($applyThreeDayFilter) {
    $newsIdsQuery->where('created_at', '>=', now()->subDays(3));
}
```

After:
```php
if ($dateFilter !== null) {
    $newsIdsQuery->where('created_at', '>=', $dateFilter);
}
```

### Lines 116-120 (CHANGED):
Before:
```php
if ($applyThreeDayFilter) {
    $newsItemsQuery->where('created_at', '>=', now()->subDays(3));
}
```

After:
```php
if ($dateFilter !== null) {
    $newsItemsQuery->where('created_at', '>=', $dateFilter);
}
```

## Summary of Changes

| Part | Before | After |
|------|--------|-------|
| **Variable Usage** | Uses `$applyThreeDayFilter` directly | Uses `$dateFilter` variable |
| **Logic** | Filters by 3 days if true | Checks 3-day count first, fallback to 12 months |
| **Empty Results** | Can be empty if no 3-day data | Always has data if any exists within 12 months |
| **Conditions** | 1 condition check | 1 initial check + 1 count query + 1 date assignment |
| **Performance** | 2 DB queries for pagination | 3 DB queries (1 count + 2 for pagination) |

## Testing

**Test API Call:**
```
GET /api/v18/CategoryNews
?slug=news
&subslug=undefined
&device_id=12345
&user_id=1
&page=1
&per_page=6
```

**Expected Behavior:**
1. If category 1 has news from last 3 days → Shows last 3 days
2. If category 1 has NO news from last 3 days but has from last 12 months → Shows last 12 months
3. If category 1 has NO news at all → Returns empty (as before)
4. All results ordered by `created_at DESC`
