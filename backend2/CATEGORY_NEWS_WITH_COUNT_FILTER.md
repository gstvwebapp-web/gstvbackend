# Category List with News Count Filter

## Requirement
Get all active categories, but ONLY include categories that have at least 1 active news item. Filter out categories with 0 news count.

## Current Code (WITHOUT NEWS COUNT FILTER)
```php
$category = Redis::get('categoriesapp'.$this->webtype);
if (!$category) {
    $category = DB::table('categories as c1')
        ->select('c1.id', 'c1.userID', 'c1.parentID','c1.title as title', 'c1.title as category_name', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title as parent_category_name')
        ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
        ->where('c1.status', 'Active')
        ->orderBy('c1.catOrder', 'ASC')
        ->get()
        ->map(function ($item) use ($iconBaseUrl) {
            $item->icon = $iconBaseUrl . $item->icon;
            $item->category_name_guj = $item->category_name_guj.'--ffff';
            return $item;
        })
        ->toArray();
    Redis::set('categoriesapp'.$this->webtype, json_encode($category));
} else {
    $category = json_decode($category, true);
}
```

## NEW CODE (WITH NEWS COUNT FILTER - ONLY CATEGORIES WITH NEWS > 0)

### Option 1: Using JOIN with COUNT (Recommended - Best Performance)

```php
$category = Redis::get('categoriesapp'.$this->webtype);
if (!$category) {
    $category = DB::table('categories as c1')
        ->select(
            'c1.id', 
            'c1.userID', 
            'c1.parentID',
            'c1.title as title', 
            'c1.title as category_name', 
            'c1.category_name_guj', 
            'c1.slug',
            'c1.featureImage', 
            'c1.icon', 
            'c1.description', 
            'c1.metatitle', 
            'c1.metakeyword', 
            'c1.metadesc', 
            'c1.catOrder', 
            'c1.eventCat', 
            'c1.setHome', 
            'c1.status',
            'c1.created_at', 
            'c1.updated_at',
            'c2.title as parent_category_name',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->leftJoin('categories as c2', 'c1.parentID', '=', 'c2.id')
        ->leftJoin('news_category as nc', 'c1.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c1.status', 'Active')
        ->where('n.status', 'Active')  // Only active news
        ->groupBy(
            'c1.id', 
            'c1.userID', 
            'c1.parentID',
            'c1.title', 
            'c1.category_name_guj', 
            'c1.slug',
            'c1.featureImage', 
            'c1.icon', 
            'c1.description', 
            'c1.metatitle', 
            'c1.metakeyword', 
            'c1.metadesc', 
            'c1.catOrder', 
            'c1.eventCat', 
            'c1.setHome', 
            'c1.status',
            'c1.created_at', 
            'c1.updated_at',
            'c2.title'
        )
        ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)  // Only categories with news_count > 0
        ->orderBy('c1.catOrder', 'ASC')
        ->get()
        ->map(function ($item) use ($iconBaseUrl) {
            $item->icon = $iconBaseUrl . $item->icon;
            $item->category_name_guj = $item->category_name_guj.'--ffff';
            return $item;
        })
        ->toArray();
    Redis::set('categoriesapp'.$this->webtype, json_encode($category));
} else {
    $category = json_decode($category, true);
}
```

## How It Works

1. **JOIN news_category**: Connect categories to their news items
2. **JOIN news**: Connect to actual news table
3. **WHERE n.status = 'Active'**: Only count active news
4. **GROUP BY c1.id...**: Group by all category fields
5. **HAVING COUNT(DISTINCT n.id) > 0**: Filter out categories with 0 news
6. **SELECT with COUNT**: Includes news_count in result
7. **Result**: Only categories that have at least 1 active news

## Output Example

**Before (All 20 categories)**:
```
[
  { id: 1, title: 'News', news_count: 0 },      ❌ Excluded now
  { id: 2, title: 'Sports', news_count: 25 },   ✅ Included
  { id: 3, title: 'Business', news_count: 0 },  ❌ Excluded now
  { id: 4, title: 'Tech', news_count: 12 }      ✅ Included
]
```

**After (Only 2 categories with news > 0)**:
```
[
  { id: 2, title: 'Sports', news_count: 25 },   ✅ Included
  { id: 4, title: 'Tech', news_count: 12 }      ✅ Included
]
```

## Key Changes

| Part | Before | After |
|------|--------|-------|
| **JOINs** | c1 → c2 | c1 → c2 → nc → n |
| **WHERE** | c1.status = Active | c1.status = Active AND n.status = Active |
| **GROUP BY** | None | All c1 fields |
| **HAVING** | None | COUNT(n.id) > 0 |
| **SELECT** | No count | Includes news_count |
| **Result** | All categories | Only categories with news |

## Database Query (Equivalent SQL)

```sql
SELECT 
    c1.id, 
    c1.userID, 
    c1.parentID,
    c1.title,
    c1.category_name_guj, 
    c1.slug,
    c1.featureImage, 
    c1.icon, 
    c1.description, 
    c1.metatitle, 
    c1.metakeyword, 
    c1.metadesc, 
    c1.catOrder, 
    c1.eventCat, 
    c1.setHome, 
    c1.status,
    c1.created_at, 
    c1.updated_at,
    c2.title as parent_category_name,
    COUNT(DISTINCT n.id) as news_count
FROM categories c1
LEFT JOIN categories c2 ON c1.parentID = c2.id
LEFT JOIN news_category nc ON c1.id = nc.category_id
LEFT JOIN news n ON nc.news_id = n.id
WHERE c1.status = 'Active' 
  AND n.status = 'Active'
GROUP BY c1.id, c1.userID, c1.parentID, c1.title, c1.category_name_guj, 
         c1.slug, c1.featureImage, c1.icon, c1.description, c1.metatitle, 
         c1.metakeyword, c1.metadesc, c1.catOrder, c1.eventCat, c1.setHome, 
         c1.status, c1.created_at, c1.updated_at, c2.title
HAVING COUNT(DISTINCT n.id) > 0
ORDER BY c1.catOrder ASC;
```

## Performance Optimization

**If you have many categories and need speed, add this index**:

```sql
CREATE INDEX idx_news_category_status ON news(id, status);
CREATE INDEX idx_news_category_news_id ON news_category(category_id, news_id);
```

## Alternative: If You Want to Keep news_count Hidden

If you DON'T want to include `news_count` in the response, remove this line:

```php
DB::raw('COUNT(DISTINCT n.id) as news_count')
```

And update the SELECT to:

```php
->select(
    'c1.id', 
    'c1.userID', 
    'c1.parentID',
    'c1.title as title', 
    'c1.title as category_name', 
    'c1.category_name_guj', 
    'c1.slug',
    'c1.featureImage', 
    'c1.icon', 
    'c1.description', 
    'c1.metatitle', 
    'c1.metakeyword', 
    'c1.metadesc', 
    'c1.catOrder', 
    'c1.eventCat', 
    'c1.setHome', 
    'c1.status',
    'c1.created_at', 
    'c1.updated_at',
    'c2.title as parent_category_name'
)
```

The HAVING clause will still filter, but news_count won't appear in results.

## Testing

**Test Query**:
```sql
-- Check how many categories have active news
SELECT 
    c1.id,
    c1.title,
    COUNT(DISTINCT n.id) as news_count
FROM categories c1
LEFT JOIN news_category nc ON c1.id = nc.category_id
LEFT JOIN news n ON nc.news_id = n.id
WHERE c1.status = 'Active' 
  AND n.status = 'Active'
GROUP BY c1.id, c1.title
HAVING COUNT(DISTINCT n.id) > 0
ORDER BY c1.catOrder ASC;
```

**Expected Result**: Only categories with at least 1 active news item

## Summary

✅ Only categories with active news appear in list
✅ Categories with 0 news are filtered out
✅ Maintains all original category data
✅ Includes news_count for debugging
✅ Ordered by catOrder
✅ Cached in Redis (updates when cache expires)
