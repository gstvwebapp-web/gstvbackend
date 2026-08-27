# Exact Code Change for v18 CategoryNews Fallback

## File Location
`backend2/app/Http/Controllers/Api/v18/HomeAppController.php`

## Function
`CategoryNews()` - Line 614

## Replace THIS (Lines 660-680):

```php
$applyThreeDayFilter = in_array($category->id, $lastThreeDayCategories) ||  in_array($category->parentId, $lastThreeDayCategories);
$totalQuery = DB::table('news')
    ->whereIn('id', $newsIdQuery)
    ->where('status', 'Active');

if ($applyThreeDayFilter) {
    $totalQuery->where('created_at', '>=', now()->subDays(3));
}

$total = $totalQuery->count();

$newsIdsQuery = DB::table('news')
    ->whereIn('id', $newsIdQuery)
    ->where('status', 'Active');

if ($applyThreeDayFilter) {
    $newsIdsQuery->where('created_at', '>=', now()->subDays(3));
}
```

## WITH THIS (New Fallback Logic):

```php
$applyThreeDayFilter = in_array($category->id, $lastThreeDayCategories) ||  in_array($category->parentId, $lastThreeDayCategories);

// ===== FALLBACK LOGIC: Check if last 3 days has data =====
$dateFilter = null;
if ($applyThreeDayFilter) {
    $lastThreeDaysCount = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active')
        ->where('created_at', '>=', now()->subDays(3))
        ->count();

    if ($lastThreeDaysCount > 0) {
        $dateFilter = now()->subDays(3);  // Has 3-day data
    } else {
        $dateFilter = now()->subDays(365);  // Fallback to 12 months
    }
}

$totalQuery = DB::table('news')
    ->whereIn('id', $newsIdQuery)
    ->where('status', 'Active');

if ($dateFilter !== null) {
    $totalQuery->where('created_at', '>=', $dateFilter);
}

$total = $totalQuery->count();

$newsIdsQuery = DB::table('news')
    ->whereIn('id', $newsIdQuery)
    ->where('status', 'Active');

if ($dateFilter !== null) {
    $newsIdsQuery->where('created_at', '>=', $dateFilter);
}
```

## Also Update (Lines 698-703):

Replace THIS:
```php
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

if ($applyThreeDayFilter) {
    $newsItemsQuery->where('created_at', '>=', now()->subDays(3));
}
```

With THIS:
```php
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
```

## How It Works

1. **Define dateFilter = null** at start
2. **If filtered categories**:
   - Count news from last 3 days
   - If count > 0: Set `$dateFilter = last 3 days`
   - If count = 0: Set `$dateFilter = last 12 months`
3. **Apply dateFilter** to all three queries:
   - `$totalQuery`
   - `$newsIdsQuery`
   - `$newsItemsQuery`

## Result

- ✓ If last 3 days has data → Show last 3 days
- ✓ If last 3 days empty → Show last 12 months
- ✓ Always ordered by date DESC
- ✓ Non-filtered categories unaffected
