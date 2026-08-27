# V18 CategoryNews - Quick Reference Guide

## What to Change

**File**: `backend2/app/Http/Controllers/Api/v18/HomeAppController.php`

**Function**: `CategoryNews()` - Line 614

**Lines to Modify**: 660-720

## The Logic

```
IF category is in filtered list (1, 47, 48, 146, 147, 149, 151):
    Count = news from last 3 days
    IF Count > 0:
        Use filter: last 3 days
    ELSE:
        Use filter: last 12 months
ELSE:
    No date filter (show all)
```

## Key Code Addition

Add this NEW fallback check AFTER line 659:

```php
// ===== NEW: FALLBACK LOGIC ===== //
$dateFilter = null;
if ($applyThreeDayFilter) {
    $lastThreeDaysCount = DB::table('news')
        ->whereIn('id', $newsIdQuery)
        ->where('status', 'Active')
        ->where('created_at', '>=', now()->subDays(3))
        ->count();

    if ($lastThreeDaysCount > 0) {
        $dateFilter = now()->subDays(3);
    } else {
        $dateFilter = now()->subDays(365);
    }
}
// ===== END: FALLBACK LOGIC ===== //
```

## Then Replace ALL Conditions

Replace all occurrences of:
```php
if ($applyThreeDayFilter) {
    ->where('created_at', '>=', now()->subDays(3))
}
```

With:
```php
if ($dateFilter !== null) {
    ->where('created_at', '>=', $dateFilter)
}
```

Find and replace in 3 locations:
1. Line ~676 (totalQuery)
2. Line ~685 (newsIdsQuery)  
3. Line ~707 (newsItemsQuery)

## Result

✅ Last 3 days: Show if exists
✅ Fallback: Last 12 months if no 3-day data
✅ Ordered: Always DESC by date
✅ Empty: Only if truly no data exists

---

## Reference Files

1. **V18_EXACT_CODE_CHANGE.md** - Copy/paste exact code
2. **V18_COMPLETE_FUNCTION_WITH_CHANGES.md** - Full function
3. **CATEGORY_NEWS_V18_FALLBACK_LOGIC.md** - Detailed explanation

---

## Implementation Time: ~5 minutes
