# Quick: Category List - Show Only Categories With News

## Location
`backend2/app/Http/Controllers/Api/v18/HomeAppController.php`

## Replace THIS:

```php
$category = DB::table('categories as c1')
    ->select('c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title as parent_category_name')
    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
    ->where('c1.status', 'Active')
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

## WITH THIS:

```php
$category = DB::table('categories as c1')
    ->select(
        'c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title as parent_category_name'
    )
    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
    ->leftJoin('news_category as nc', 'c1.id', '=', 'nc.category_id')
    ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
    ->where('c1.status', 'Active')
    ->where('n.status', 'Active')
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

## Changes Made

✅ **Added 2 new JOINs**:
- `news_category` table (connects categories to news)
- `news` table (actual news records)

✅ **Added WHERE clause**:
- `where('n.status', 'Active')` - Only active news counts

✅ **Added GROUP BY**:
- Groups by all category fields

✅ **Added HAVING clause**:
- `having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)` - Only categories with news > 0

## Result

**Before**: All 20 active categories appear
**After**: Only categories with at least 1 active news appear

**Example**:
- News: 25 items → ✅ Shows
- Sports: 12 items → ✅ Shows
- Business: 0 items → ❌ Hidden
- Tech: 5 items → ✅ Shows

---

See `CATEGORY_NEWS_WITH_COUNT_FILTER.md` for full details and SQL query
