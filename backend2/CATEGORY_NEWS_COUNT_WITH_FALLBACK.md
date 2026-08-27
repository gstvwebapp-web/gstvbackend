# Category News Count - Last 3 Days or Last 12 Months Fallback

## Problem
- If last 3 days has no data → show last 12 months data instead
- Don't show empty results

## Solution

### Option 1: Check First, Then Fallback (Recommended)

```php
public function getCategoryNewsCountWithFallback()
{
    // First try: Last 3 days data
    $categoryNewsCount = DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->where('n.created_at', '>=', now()->subDays(3))
        ->select(
            'c.id',
            'c.slug',
            'c.title',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->groupBy('c.id', 'c.slug', 'c.title')
        ->orderByDesc('news_count')
        ->get();

    // If no data in last 3 days, use last 12 months
    if ($categoryNewsCount->isEmpty()) {
        $categoryNewsCount = DB::table('categories as c')
            ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
            ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
            ->where('c.status', 'Active')
            ->where('n.status', 'Active')
            ->where('n.created_at', '>=', now()->subDays(365))  // Last 12 months
            ->select(
                'c.id',
                'c.slug',
                'c.title',
                DB::raw('COUNT(DISTINCT n.id) as news_count')
            )
            ->groupBy('c.id', 'c.slug', 'c.title')
            ->orderByDesc('news_count')
            ->get();
    }

    return response()->json($categoryNewsCount);
}
```

---

### Option 2: Single Query with CASE (Better Performance)

```php
public function getCategoryNewsCountWithFallback()
{
    // Get data from last 3 days, fallback to 12 months if empty
    $threeDAysAgo = now()->subDays(3);
    $twelveMonthsAgo = now()->subDays(365);

    // First get count for last 3 days
    $last3DaysCount = DB::table('news')
        ->where('status', 'Active')
        ->where('created_at', '>=', $threeDAysAgo)
        ->count();

    // If no data in last 3 days, use last 12 months
    $dateFilter = $last3DaysCount > 0 ? $threeDAysAgo : $twelveMonthsAgo;

    $categoryNewsCount = DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->where('n.created_at', '>=', $dateFilter)
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

### Option 3: With Time Period Indicator (Show User What Data They're Seeing)

```php
public function getCategoryNewsCountWithFallback()
{
    // Check last 3 days
    $last3DaysAgo = now()->subDays(3);
    $last3DaysCount = DB::table('news')
        ->where('status', 'Active')
        ->where('created_at', '>=', $last3DaysAgo)
        ->count();

    if ($last3DaysCount > 0) {
        // Use last 3 days data
        $categoryNewsCount = DB::table('categories as c')
            ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
            ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
            ->where('c.status', 'Active')
            ->where('n.status', 'Active')
            ->where('n.created_at', '>=', $last3DaysAgo)
            ->select(
                'c.id',
                'c.slug',
                'c.title',
                DB::raw('COUNT(DISTINCT n.id) as news_count')
            )
            ->groupBy('c.id', 'c.slug', 'c.title')
            ->orderByDesc('news_count')
            ->get();

        $timePeriod = 'last_3_days';
    } else {
        // Use last 12 months data
        $categoryNewsCount = DB::table('categories as c')
            ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
            ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
            ->where('c.status', 'Active')
            ->where('n.status', 'Active')
            ->where('n.created_at', '>=', now()->subDays(365))
            ->select(
                'c.id',
                'c.slug',
                'c.title',
                DB::raw('COUNT(DISTINCT n.id) as news_count')
            )
            ->groupBy('c.id', 'c.slug', 'c.title')
            ->orderByDesc('news_count')
            ->get();

        $timePeriod = 'last_12_months';
    }

    return response()->json([
        'data' => $categoryNewsCount,
        'period' => $timePeriod
    ]);
}
```

---

## Comparison

| Option | Best For | Performance |
|--------|----------|-------------|
| **Option 1** | Simple, easy to read | 2 queries (slower) |
| **Option 2** | Performance optimized | 2 queries (same, but faster logic) |
| **Option 3** | User knows data period | Shows what user is seeing |

---

## What Changes in Code

### For Last 3 Days:
```php
->where('n.created_at', '>=', now()->subDays(3))
```

### For Last 12 Months (Fallback):
```php
->where('n.created_at', '>=', now()->subDays(365))
```

### Check If Data Exists:
```php
$count = DB::table('news')
    ->where('status', 'Active')
    ->where('created_at', '>=', now()->subDays(3))
    ->count();

if ($count > 0) {
    // Use 3 days data
} else {
    // Use 12 months data
}
```

---

## Usage Example

```php
// Add this to your controller
public function getCategoryNewsCountWithFallback()
{
    // Try last 3 days first
    $dateFilter = now()->subDays(3);
    
    $categoryNewsCount = DB::table('categories as c')
        ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
        ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
        ->where('c.status', 'Active')
        ->where('n.status', 'Active')
        ->where('n.created_at', '>=', $dateFilter)
        ->select(
            'c.id',
            'c.slug',
            'c.title',
            DB::raw('COUNT(DISTINCT n.id) as news_count')
        )
        ->groupBy('c.id', 'c.slug', 'c.title')
        ->orderByDesc('news_count')
        ->get();

    // If empty, fallback to 12 months
    if ($categoryNewsCount->isEmpty()) {
        $categoryNewsCount = DB::table('categories as c')
            ->leftJoin('news_category as nc', 'c.id', '=', 'nc.category_id')
            ->leftJoin('news as n', 'nc.news_id', '=', 'n.id')
            ->where('c.status', 'Active')
            ->where('n.status', 'Active')
            ->where('n.created_at', '>=', now()->subDays(365))
            ->select(
                'c.id',
                'c.slug',
                'c.title',
                DB::raw('COUNT(DISTINCT n.id) as news_count')
            )
            ->groupBy('c.id', 'c.slug', 'c.title')
            ->orderByDesc('news_count')
            ->get();
    }

    return response()->json($categoryNewsCount);
}
```

---

## Key Changes

1. **Check if data exists** for last 3 days
2. **If empty** → use last 12 months instead
3. **Never show empty** results to user

---

## Result

**If last 3 days has data:**
- Shows last 3 days data

**If last 3 days has NO data:**
- Shows last 12 months data

**User always gets data** (no empty results)
