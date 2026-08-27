# Category-Wise News Count Query

## Requirement
Get news count list by category with filtering:
- Only show categories with **status = 'Active'**
- Only count news with **status = 'Active'**
- Include category ID, slug, title, and news count

---

## Query Solution

### Option 1: Simple - Count News per Category (Active Only)

```php
$newsCountByCategory = DB::table('categories as c')
    ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c.status', 'Active')
    ->where('n.status', 'Active')
    ->select(
        'c.id',
        'c.slug',
        'c.title',
        DB::raw('COUNT(DISTINCT n.id) as news_count')
    )
    ->groupBy('c.id', 'c.slug', 'c.title')
    ->orderByDesc('news_count')
    ->get();
```

**Returns:**
```
[
    {
        "id": 1,
        "slug": "news",
        "title": "News",
        "news_count": 150
    },
    {
        "id": 47,
        "slug": "videos",
        "title": "Videos",
        "news_count": 85
    }
]
```

---

### Option 2: With Additional Filters

```php
// Only categories that have active news
$newsCountByCategory = DB::table('categories as c')
    ->innerJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->innerJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c.status', 'Active')
    ->where('n.status', 'Active')
    ->select(
        'c.id',
        'c.slug',
        'c.title',
        'c.parentId',
        DB::raw('COUNT(DISTINCT n.id) as news_count')
    )
    ->groupBy('c.id', 'c.slug', 'c.title', 'c.parentId')
    ->having('news_count', '>', 0)  // Only categories with at least 1 news
    ->orderByDesc('news_count')
    ->get();
```

---

### Option 3: With Category Details

```php
$newsCountByCategory = DB::table('categories as c')
    ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c.status', 'Active')
    ->where(function($query) {
        $query->where('n.status', 'Active')
              ->orWhereNull('n.id');
    })
    ->select(
        'c.id',
        'c.slug',
        'c.title',
        'c.metatitle',
        'c.metadesc',
        'c.image',
        'c.parentId',
        DB::raw('COUNT(DISTINCT n.id) as news_count')
    )
    ->groupBy('c.id', 'c.slug', 'c.title', 'c.metatitle', 'c.metadesc', 'c.image', 'c.parentId')
    ->orderByDesc('news_count')
    ->get();
```

---

### Option 4: News Count with Last Updated Date

```php
$newsCountByCategory = DB::table('categories as c')
    ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c.status', 'Active')
    ->where('n.status', 'Active')
    ->select(
        'c.id',
        'c.slug',
        'c.title',
        DB::raw('COUNT(DISTINCT n.id) as news_count'),
        DB::raw('MAX(n.created_at) as last_updated')
    )
    ->groupBy('c.id', 'c.slug', 'c.title')
    ->orderByDesc('last_updated')
    ->get();
```

---

### Option 5: Raw SQL (if preferred)

```php
$newsCountByCategory = DB::select("
    SELECT 
        c.id,
        c.slug,
        c.title,
        c.metatitle,
        c.metadesc,
        COUNT(DISTINCT n.id) as news_count
    FROM categories c
    LEFT JOIN news_category nc ON c.id = nc.category_id
    LEFT JOIN news n ON nc.news_id = n.id AND n.status = 'Active'
    WHERE c.status = 'Active'
    GROUP BY c.id, c.slug, c.title, c.metatitle, c.metadesc
    ORDER BY news_count DESC
");
```

---

## Key Points

### Category Filter
```php
where('c.status', 'Active')  // Only active categories
```

### News Filter
```php
where('n.status', 'Active')  // Only active news
```

### Join Types
- **INNER JOIN**: Only categories with active news
- **LEFT JOIN**: All active categories (even if no news)

### Grouping
```php
->groupBy('c.id', 'c.slug', 'c.title', ...)  // Must include all non-aggregated columns
```

### Counting
```php
DB::raw('COUNT(DISTINCT n.id) as news_count')  // DISTINCT avoids duplicates
```

---

## Usage in Controller

```php
public function getCategoryNewsCount()
{
    $newsCountByCategory = DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->select(
            'c.id',
            'c.slug',
            'c.title',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->groupBy('c.id', 'c.slug', 'c.title')
        ->orderByDesc('news_count')
        ->get();

    return response()->json([
        'categories' => $newsCountByCategory,
        'total_categories' => count($newsCountByCategory)
    ]);
}
```

---

## Expected Response

```json
{
    "categories": [
        {
            "id": 1,
            "slug": "news",
            "title": "News",
            "news_count": 250
        },
        {
            "id": 47,
            "slug": "videos",
            "title": "Videos",
            "news_count": 150
        },
        {
            "id": 48,
            "slug": "sports",
            "title": "Sports",
            "news_count": 85
        }
    ],
    "total_categories": 3
}
```

---

## Optimization Tips

1. **Add Index** on `categories.status` and `news.status`
2. **Cache Results** if data doesn't change frequently:
   ```php
   $cached = Cache::remember('category_news_count', 3600, function() {
       return DB::table('categories as c')
           ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
           ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
           ->where('c.status', 'Active')
           ->where('n.status', 'Active')
           ->select(
               'c.id', 'c.slug', 'c.title',
               DB::raw('COUNT(DISTINCT n.id) as news_count')
           )
           ->groupBy('c.id', 'c.slug', 'c.title')
           ->get();
   });
   ```

3. **Use Pagination** for large datasets:
   ```php
   ->paginate(20)
   ```

---

## Filtering Additional Conditions

### Only Categories with News
```php
->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
```

### Categories Created in Last 30 Days
```php
->where('c.created_at', '>=', now()->subDays(30))
```

### Exclude Specific Categories
```php
->whereNotIn('c.id', [1, 2, 3])
```

### Only Parent Categories
```php
->where('c.parentId', 0)  // or IS NULL
```

### Include only News from Last 7 Days
```php
->where('n.created_at', '>=', now()->subDays(7))
```

---

## Summary

**Choose Option 1** for simple, fast category news count.
**Choose Option 3** for complete category information.
**Use caching** if called frequently.
**No code changes** to CategoryNews() function needed - this is a new query.
