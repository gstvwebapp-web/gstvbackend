# Category List - Only News from Last 30 Days

## Requirement
Get all active categories, but ONLY include categories that have at least 1 active news item created in the last 30 days (last month).

## Current Code (WITHOUT 30-DAY FILTER)
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

## NEW CODE (WITH LAST 30 DAYS FILTER)

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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ NEW: Last 30 days only
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

## What Changed

**Added 1 line** after the news status filter:
```php
->where('n.created_at', '>=', now()->subDays(30))  // Last 30 days only
```

## Key Line to Add

```php
->where('n.created_at', '>=', now()->subDays(30))
```

## Explanation

| Part | Value |
|------|-------|
| **Field** | `n.created_at` (news creation date) |
| **Operator** | `>=` (greater than or equal) |
| **Filter** | `now()->subDays(30)` (today minus 30 days) |
| **Result** | Only news from last 30 days included |

## SQL Equivalent

```sql
SELECT 
    c1.id, 
    c1.title,
    c1.category_name_guj, 
    c1.slug,
    ... (other fields)
FROM categories c1
LEFT JOIN categories c2 ON c1.parentID = c2.id
LEFT JOIN news_category nc ON c1.id = nc.category_id
LEFT JOIN news n ON nc.news_id = n.id
WHERE c1.status = 'Active' 
  AND n.status = 'Active'
  AND n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)  -- ✅ Last 30 days
GROUP BY c1.id, c1.userID, c1.parentID, ...
HAVING COUNT(DISTINCT n.id) > 0
ORDER BY c1.catOrder ASC;
```

## Example

**Scenario**:
- News category has 50 total articles
- 8 articles created in last 30 days
- 42 articles created more than 30 days ago

**Result**:
- ✅ Category appears in list (has 8 recent articles)
- Only counts the 8 recent articles

**Scenario 2**:
- Sports category has 25 total articles
- 0 articles created in last 30 days
- Last article is 40 days old

**Result**:
- ❌ Category hidden from list (no recent articles)

## All 4 Locations to Update

Apply this same filter to all 4 category query locations in the file:

1. **Line 1674** - Add the filter
2. **Line 1903** - Add the filter
3. **Line 2145** - Add the filter
4. **Line 2267** - Add the filter

Each location gets the same addition.

## Complete Code for All Locations

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
    ->where('n.created_at', '>=', now()->subDays(30))  // ✅ Last 30 days
    ->groupBy('c1.id', 'c1.userID', 'c1.parentID','c1.title', 'c1.category_name_guj', 'c1.slug','c1.featureImage', 'c1.icon', 'c1.description', 'c1.metatitle', 'c1.metakeyword', 'c1.metadesc', 'c1.catOrder', 'c1.eventCat', 'c1.setHome', 'c1.status','c1.created_at', 'c1.updated_at','c2.title')
    ->having(DB::raw('COUNT(DISTINCT n.id)'), '>', 0)
    ->orderBy('c1.catOrder', 'ASC')
    ->get()
```

## Implementation Steps

1. Open: `backend2/app/Http/Controllers/Api/v18/HomeAppController.php`
2. Find all 4 category query locations (lines 1674, 1903, 2145, 2267)
3. Add this line after `->where('n.status', 'Active')`:
   ```php
   ->where('n.created_at', '>=', now()->subDays(30))
   ```
4. Save file
5. Clear Redis cache:
   ```bash
   php artisan tinker
   Redis::flushAll()
   ```
6. Test API - should show only categories with recent news

## Testing Query

Test with this SQL query:
```sql
-- Check categories with news from last 30 days
SELECT 
    c1.id,
    c1.title,
    COUNT(DISTINCT n.id) as recent_news_count
FROM categories c1
LEFT JOIN news_category nc ON c1.id = nc.category_id
LEFT JOIN news n ON nc.news_id = n.id
WHERE c1.status = 'Active' 
  AND n.status = 'Active'
  AND n.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY c1.id, c1.title
HAVING COUNT(DISTINCT n.id) > 0
ORDER BY c1.catOrder ASC;
```

## Summary

✅ Add 1 line to filter by last 30 days
✅ Apply to all 4 category query locations
✅ Only categories with recent news appear
✅ News older than 30 days ignored
✅ Clear Redis cache after changes
✅ Implementation time: ~2 minutes
