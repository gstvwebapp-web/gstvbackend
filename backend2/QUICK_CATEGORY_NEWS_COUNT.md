# Quick Query - Category-Wise News Count

## Requirement
Get news count by category where:
- Category status = Active
- News status = Active

---

## Best Query (Recommended)

```php
$categoryNewsCount = DB::table('categories as c')
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

---

## What It Does

| Step | Description |
|------|-------------|
| **Join 1** | `categories` ← `news_category` |
| **Join 2** | `news_category` ← `news` |
| **Filter 1** | Only `categories.status = 'Active'` |
| **Filter 2** | Only `news.status = 'Active'` |
| **Count** | `COUNT(DISTINCT news.id)` per category |
| **Sort** | By news_count DESC |

---

## Result Format

```
[
    { id: 1, slug: "news", title: "News", news_count: 250 },
    { id: 47, slug: "videos", title: "Videos", news_count: 150 },
    { id: 48, slug: "sports", title: "Sports", news_count: 85 }
]
```

---

## Alternative - Only Show Categories with News

```php
$categoryNewsCount = DB::table('categories as c')
    ->innerJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
    ->innerJoin('news as n', 'nc.news_id', '=', 'n.id')
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

**Difference**: `INNER JOIN` excludes categories with zero news

---

## With Caching (Recommended for Performance)

```php
$categoryNewsCount = Cache::remember('category_news_count', 3600, function() {
    return DB::table('categories as c')
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
});
```

---

## Key Filters

### Both Must Be Active
```php
->where('c.status', 'Active')     // Category is active
->where('n.status', 'Active')     // News is active
```

### Count Distinct to Avoid Duplicates
```php
DB::raw('COUNT(DISTINCT n.id) as news_count')
```

### Group By All Selected Columns
```php
->groupBy('c.id', 'c.slug', 'c.title')
```

---

## Usage

```php
// In Controller
public function categoryNewsCount()
{
    $categoryNewsCount = DB::table('categories as c')
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

    return response()->json($categoryNewsCount);
}
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
GROUP BY c.id, c.slug, c.title
ORDER BY news_count DESC;
```

---

## No Code Changes
✅ This query is **NEW** - no existing code modified
✅ Can be used as **separate function**
✅ Works alongside **CategoryNews()** function
✅ Ready to **use immediately**
