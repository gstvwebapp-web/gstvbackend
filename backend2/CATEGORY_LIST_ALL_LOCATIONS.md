# Category List Filter - ALL Locations in v18 Controller

## Overview
There are 4 places in `HomeAppController.php` where category lists are fetched. We need to add news count filter to each.

## All Locations

### Location 1: Line 1672-1692
**Function**: Unknown (around line 1672)
**Redis Key**: `categoriesapp`

Replace:
```php
$category = DB::table('categories as c1')
    ->select(
        'c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
        'c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 
        'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status',
        'c1.created_at', 'c1.updated_at',
        'c2.title as parent_category_name'
    )
    ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
    ->where('c1.status', 'Active')
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

With:
```php
$category = DB::table('categories as c1')
    ->select(
        'c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug',
        'c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 
        'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status',
        'c1.created_at', 'c1.updated_at',
        'c2.title as parent_category_name'
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

---

### Location 2: Line 1900-1920
**Function**: Unknown (around line 1900)
**Redis Key**: `categoriesapp1`

Same changes as Location 1, but replace in this section too.

---

### Location 3: Line 2142-2162
**Function**: Unknown (around line 2142)
**Redis Key**: `categoriesapptopmenu`

Same changes as Location 1, but replace in this section too.

---

### Location 4: Line 2264-2284
**Function**: Unknown (around line 2264)
**Redis Key**: `categoriesapp`

Same changes as Location 1, but replace in this section too.

---

## The Changes to Apply (Same to All 4 Locations)

### Add These 2 JOINs (After leftJoin with c2):
```php
->leftJoin('news_category as nc', 'c1.id', '=', 'nc.category_id')
->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
```

### Change WHERE clause:
**From:**
```php
->where('c1.status', 'Active')
```

**To:**
```php
->where('c1.status', 'Active')
->where('n.status', 'Active')
```

### Add GROUP BY (Before orderBy):
```php
->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
```

### Add HAVING (Before orderBy):
```php
->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
```

---

## Summary of All Changes

| Location | Line | Redis Key | Change |
|----------|------|-----------|--------|
| 1 | 1672-1692 | categoriesapp | Add 2 JOINs, add WHERE n.status, add GROUP BY, add HAVING |
| 2 | 1900-1920 | categoriesapp1 | Add 2 JOINs, add WHERE n.status, add GROUP BY, add HAVING |
| 3 | 2142-2162 | categoriesapptopmenu | Add 2 JOINs, add WHERE n.status, add GROUP BY, add HAVING |
| 4 | 2264-2284 | categoriesapp | Add 2 JOINs, add WHERE n.status, add GROUP BY, add HAVING |

---

## Order of Implementation

1. **Location 1** (Line 1672) - Main category list
2. **Location 2** (Line 1900) - Secondary category list
3. **Location 3** (Line 2142) - Top menu categories
4. **Location 4** (Line 2264) - Another category list

---

## Testing After Changes

Clear Redis cache:
```bash
php artisan tinker
Redis::flushAll()
```

Then test API calls - categories with 0 news should not appear.

---

## Performance Impact

- **Before**: 1 query (categories only)
- **After**: 1 query with 3 JOINs (categories + news_category + news)
- **Impact**: Minimal - JOINs are indexed
- **Redis Cache**: Still applied (same as before)
- **Update Frequency**: Cache expires when Redis key expires (same as before)

---

## Reference

See these files for full details:
- `CATEGORY_NEWS_WITH_COUNT_FILTER.md` - Full explanation
- `CATEGORY_LIST_FILTER_QUICK.md` - Quick reference
