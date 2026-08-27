# Category Filter - Copy/Paste Ready Code

## File
`backend2/app/Http/Controllers/Api/v18/HomeAppController.php`

---

## Location 1: Line 1674-1691

### FIND THIS:
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

### REPLACE WITH:
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

## Location 2: Line 1903-1918

### FIND THIS:
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

### REPLACE WITH:
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

## Location 3: Line 2145-2160

### FIND THIS:
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

### REPLACE WITH:
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

## Location 4: Line 2267-2282

### FIND THIS:
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

### REPLACE WITH:
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

## Summary

✅ 4 locations need same change
✅ Add 2 JOINs (news_category, news)
✅ Add WHERE n.status = Active
✅ Add GROUP BY all c1 fields + c2.title
✅ Add HAVING COUNT > 0
✅ Result: Only categories with news appear

---

## Testing

Clear cache and test:
```bash
php artisan tinker
Redis::flushAll()
exit
```

Then call your API - should only see categories with active news.
