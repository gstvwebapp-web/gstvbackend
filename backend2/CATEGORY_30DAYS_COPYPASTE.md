# Last 30 Days Category Filter - Copy/Paste

## The Change - One Line Addition

Add this line to each of the 4 category query locations:

```php
->where('n.created_at', '>=', now()->subDays(30))
```

---

## Location 1: Line 1674-1691

### AFTER THIS LINE:
```php
->where('n.status', 'Active')
```

### ADD THIS LINE:
```php
->where('n.created_at', '>=', now()->subDays(30))
```

### Full Code Section:
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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ ADD THIS
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

---

## Location 2: Line 1903-1918

### AFTER THIS LINE:
```php
->where('n.status', 'Active')
```

### ADD THIS LINE:
```php
->where('n.created_at', '>=', now()->subDays(30))
```

### Full Code Section:
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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ ADD THIS
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

---

## Location 3: Line 2145-2160

### AFTER THIS LINE:
```php
->where('n.status', 'Active')
```

### ADD THIS LINE:
```php
->where('n.created_at', '>=', now()->subDays(30))
```

### Full Code Section:
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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ ADD THIS
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

---

## Location 4: Line 2267-2282

### AFTER THIS LINE:
```php
->where('n.status', 'Active')
```

### ADD THIS LINE:
```php
->where('n.created_at', '>=', now()->subDays(30))
```

### Full Code Section:
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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ ADD THIS
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

---

## Summary

✅ Add 1 line to each of 4 locations
✅ Filter: `->where('n.created_at', '>=', now()->subDays(30))`
✅ Only categories with news from last 30 days appear
✅ Implementation time: ~2 minutes
✅ Clear Redis after changes

---

## Testing

After changes, clear cache:
```bash
php artisan tinker
Redis::flushAll()
exit
```

Then test - should only see categories with recent (last 30 days) news.
