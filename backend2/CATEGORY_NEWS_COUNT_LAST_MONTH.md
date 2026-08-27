# Category-Wise News Count - Last 1 Month Only

## Query

```php
$categoryNewsCount = DB::table('categories as c')
    ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c.status', 'Active')
    ->where('n.status', 'Active')
    ->where('n.created_at', '>=', now()->subDays(30))  // Last 30 days (1 month)
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

---

## Key Addition

```php
->where('n.created_at', '>=', now()->subDays(30))
```

This filters to only include news created in the **last 30 days (1 month)**.

---

## What It Does

| Step | Description |
|------|-------------|
| Active Categories | `where('c.status', 'Active')` |
| Active News | `where('n.status', 'Active')` |
| **Last 1 Month** | `where('n.created_at', '>=', now()->subDays(30))` |
| Count | `COUNT(DISTINCT n.id)` |
| Sort | `orderByDesc('news_count')` |

---

## Result Example

```json
[
    {
        "id": 1,
        "slug": "news",
        "title": "News",
        "news_count": 45
    },
    {
        "id": 47,
        "slug": "videos",
        "title": "Videos",
        "news_count": 28
    },
    {
        "id": 48,
        "slug": "sports",
        "title": "Sports",
        "news_count": 15
    }
]
```

---

## Usage in Function

```php
public function getCategoryNewsCountLastMonth()
{
    $categoryNewsCount = DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->where('n.created_at', '>=', now()->subDays(30))
        ->select(
            'c.id',
            'c.slug',
            'c.title',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->groupBy('c.id', 'c.slug', 'c.title')
        ->orderByDesc('news_count')
        ->get();

    return response()->json($categoryNewsCount);
}
```

---

## With Caching (Recommended)

```php
$categoryNewsCount = Cache::remember('category_news_count_last_month', 3600, function() {
    return DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->where('n.created_at', '>=', now()->subDays(30))
        ->select(
            'c.id',
            'c.slug',
            'c.title',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->groupBy('c.id', 'c.slug', 'c.title')
        ->orderByDesc('news_count')
        ->get();
});
```

Cache updates every 1 hour.

---

## Time Filters

### Last 1 Month (30 days)
```php
->where('n.created_at', '>=', now()->subDays(30))
```

### Last 7 Days (1 week)
```php
->where('n.created_at', '>=', now()->subDays(7))
```

### Last 3 Days
```php
->where('n.created_at', '>=', now()->subDays(3))
```

### Last 24 Hours (1 day)
```php
->where('n.created_at', '>=', now()->subDay())
```

### Custom Date Range
```php
->where('n.created_at', '>=', Carbon::parse('2026-06-23'))
->where('n.created_at', '<=', Carbon::parse('2026-07-23'))
```

---

## Generated SQL

```sql
SELECT 
    c.id,
    c.slug,
    c.title,
    COUNT(DISTINCT n.id) as news_count
FROM categories c
LEFT JOIN news_category nc ON c.id = nc.category_id
LEFT JOIN news n ON nc.news_id = n.id
WHERE c.status = 'Active'
  AND n.status = 'Active'
  AND n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY c.id, c.slug, c.title
ORDER BY news_count DESC;
```

---

## Summary

✅ Filters active categories
✅ Filters active news
✅ **Only last 1 month (30 days) data**
✅ Returns news count per category
✅ Ready to use
