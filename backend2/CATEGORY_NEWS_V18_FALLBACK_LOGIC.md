# Category News v18 - Last 3 Days with 12-Month Fallback

## Current Issue
Currently, the `CategoryNews()` function in v18 filters news by last 3 days for specific categories. If no news exists in last 3 days, it returns empty results.

## Required Change
If last 3 days has NO news (count = 0), fallback to showing last 12 months data instead. Always show data ordered by date DESC.

## Solution: Modify CategoryNews Function

### Location
`backend2/app/Http/Controllers/Api/v18/HomeAppController.php` - Lines 614-780 (function `CategoryNews`)

### Code Changes Required

Replace the query building logic (from line 660 onwards) with this fallback approach:

```php
/* ================= STEP 1: GET NEWS IDS (FAST) ================= */
$newsIdQuery = DB::table('news_category as nc')
    ->where('nc.category_id', $category->id)
    ->pluck('nc.news_id');

if ($category->id == 9) {
    $videoIds = DB::table('news')
        ->where('is_video_horizontal', 1)
        ->where('videoURL',"!=",'')
        ->pluck('id');
    $newsIdQuery = $newsIdQuery->merge($videoIds)->unique();
}

// Determine if we need to apply date filtering
$applyThreeDayFilter = in_array($category->id, $lastThreeDayCategories) ||  in_array($category->parentId, $lastThreeDayCategories);

// ===== NEW FALLBACK LOGIC =====
if ($applyThreeDayFilter) {
    // First, try to get count for last 3 days
    $lastThreeDaysCount = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active')
        ->where('created_at', '>=', now()->subDays(3))
        ->count();

    // If last 3 days has data, use it. Otherwise fallback to last 12 months
    if ($lastThreeDaysCount > 0) {
        // Use last 3 days data
        $dateFilter = now()->subDays(3);
    } else {
        // Fallback to last 12 months
        $dateFilter = now()->subDays(365);
    }
} else {
    $dateFilter = null; // No date filter for other categories
}

// ===== BUILD TOTAL QUERY WITH FALLBACK DATE =====
$totalQuery = DB::table('news')
    ->whereIn('id', $newsIdQuery)
    ->where('status', 'Active');

if ($dateFilter !== null) {
    $totalQuery->where('created_at', '>=', $dateFilter);
}

$total = $totalQuery->count();

// ===== BUILD NEWS IDS QUERY WITH FALLBACK DATE =====
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
```

## Logic Explanation

**Before (Current)**:
```
if (applyThreeDayFilter) {
    Filter by last 3 days
}
// If no data, returns empty results
```

**After (New with Fallback)**:
```
if (applyThreeDayFilter) {
    lastThreeDaysCount = count news from last 3 days
    
    if (lastThreeDaysCount > 0) {
        dateFilter = last 3 days
    } else {
        dateFilter = last 12 months  // FALLBACK
    }
} else {
    dateFilter = null  // No filtering
}

// Use dateFilter for all queries
```

## Key Changes

1. **Lines 660-680**: Added fallback check before building queries
2. **Check Count First**: `where('created_at', '>=', now()->subDays(3))` returns count
3. **Set DateFilter Variable**: If count > 0, use 3 days; else use 365 days
4. **Apply to All Queries**: Both `$totalQuery` and `$newsIdsQuery` use same `$dateFilter`
5. **Consistent Ordering**: All results ordered by `created_at DESC`

## Implementation Steps

1. Open: `backend2/app/Http/Controllers/Api/v18/HomeAppController.php`
2. Go to: Line 614 (function CategoryNews)
3. Replace: Lines 660-700 with the new fallback logic code above
4. Save file
5. Test by calling API with category that has < 3 days data

## Test Cases

**Test 1: Category with 3+ days of news**
- Expected: Shows last 3 days news ✓

**Test 2: Category with NO last 3 days news**
- Expected: Fallback shows last 12 months news ✓

**Test 3: Category with NO 12 months news either**
- Expected: Returns empty (as expected) ✓

**Test 4: Non-filtered categories**
- Expected: No date filter applied, shows all active news ✓

## Query Performance

- First query checks last 3 days count: `O(1)` - simple count
- If fallback triggered: Queries run for last 12 months instead
- Total queries: Same as before (no additional DB hits for non-fallback cases)
- Redis cache still applied to final results

## Testing Query Manually

Test the fallback logic:
```sql
-- Check last 3 days count for category 1
SELECT COUNT(*) as count_3_days 
FROM news n
JOIN news_category nc ON n.id = nc.news_id
WHERE nc.category_id = 1 
  AND n.status = 'Active'
  AND n.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY);

-- If above is 0, this would execute (last 12 months)
SELECT COUNT(*) as count_12_months
FROM news n
JOIN news_category nc ON n.id = nc.news_id
WHERE nc.category_id = 1
  AND n.status = 'Active'
  AND n.created_at >= DATE_SUB(NOW(), INTERVAL 365 DAY);
```

## Summary

This change adds intelligent fallback:
- **Primary**: Last 3 days (for featured categories)
- **Fallback**: Last 12 months (if no recent data)
- **Default**: All news (for non-featured categories)
- **Benefit**: Never returns empty when older data exists
