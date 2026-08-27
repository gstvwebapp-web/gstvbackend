<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "Testing newsorder-simple API Logic\n";
echo "===========================================\n\n";

// Get some recent news IDs
$newsIds = DB::table('news')
    ->where('status', 'Active')
    ->orderBy('created_at', 'DESC')
    ->limit(15)
    ->pluck('id')
    ->toArray();

echo "Found " . count($newsIds) . " news IDs:\n";
foreach ($newsIds as $index => $id) {
    echo ($index + 1) . ". News ID: {$id}\n";
}
echo "\n";

// Check current state
echo "===========================================\n";
echo "BEFORE: Current State\n";
echo "===========================================\n\n";

$currentState = DB::table('news')
    ->whereIn('id', $newsIds)
    ->select('id', 'title', 'is_home', 'topnewsOrder')
    ->orderBy('topnewsOrder', 'ASC')
    ->get();

foreach ($currentState as $news) {
    echo "ID: {$news->id}\n";
    echo "  Title: " . substr($news->title, 0, 50) . "...\n";
    echo "  is_home: {$news->is_home}\n";
    echo "  topnewsOrder: {$news->topnewsOrder}\n\n";
}

// Simulate API call
echo "===========================================\n";
echo "SIMULATING API CALL\n";
echo "===========================================\n\n";

$topCount = 13;

echo "Request:\n";
echo "  newsIds: [" . implode(', ', $newsIds) . "]\n";
echo "  topCount: {$topCount}\n\n";

// Simulate the logic
DB::beginTransaction();

try {
    // Fetch current updated_at values
    $newsTimestamps = DB::table('news')
        ->whereIn('id', $newsIds)
        ->pluck('updated_at', 'id');

    // Update top N news
    $updateData = [];
    for ($i = 0; $i < min($topCount, count($newsIds)); $i++) {
        $updateData[] = [
            'id' => $newsIds[$i],
            'topnewsOrder' => $i + 1,
            'is_home' => 1,
            'catOrder' => 0,
            'userID' => 1,
            'updated_at' => $newsTimestamps[$newsIds[$i]] ?? now(),
        ];
    }

    if (!empty($updateData)) {
        DB::table('news')->upsert($updateData, ['id'], ['topnewsOrder', 'is_home', 'catOrder', 'updated_at']);
        echo "✓ Updated " . count($updateData) . " news with is_home=1\n";
    }

    // Reset remaining news
    $resetData = [];
    for ($i = $topCount; $i < count($newsIds); $i++) {
        $resetData[] = [
            'id' => $newsIds[$i],
            'topnewsOrder' => 0,
            'is_home' => 0,
            'userID' => 1,
            'updated_at' => $newsTimestamps[$newsIds[$i]] ?? now(),
        ];
    }

    if (!empty($resetData)) {
        DB::table('news')->upsert($resetData, ['id'], ['topnewsOrder', 'is_home', 'updated_at']);
        echo "✓ Reset " . count($resetData) . " news with is_home=0\n\n";
    }

    DB::commit();
    echo "✓ Transaction committed\n\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Check new state
echo "===========================================\n";
echo "AFTER: New State\n";
echo "===========================================\n\n";

$newState = DB::table('news')
    ->whereIn('id', $newsIds)
    ->select('id', 'title', 'is_home', 'topnewsOrder')
    ->orderBy('topnewsOrder', 'ASC')
    ->orderBy('id', 'DESC')
    ->get();

foreach ($newState as $news) {
    $status = $news->is_home == 1 ? "✓ TOP" : "  ---";
    echo "{$status} ID: {$news->id}\n";
    echo "       Title: " . substr($news->title, 0, 50) . "...\n";
    echo "       is_home: {$news->is_home}\n";
    echo "       topnewsOrder: {$news->topnewsOrder}\n\n";
}

echo "===========================================\n";
echo "Summary\n";
echo "===========================================\n\n";

$topNews = DB::table('news')
    ->whereIn('id', $newsIds)
    ->where('is_home', 1)
    ->count();

$resetNews = DB::table('news')
    ->whereIn('id', $newsIds)
    ->where('is_home', 0)
    ->count();

echo "Top news (is_home=1): {$topNews}\n";
echo "Reset news (is_home=0): {$resetNews}\n";
echo "Total: " . ($topNews + $resetNews) . "\n\n";

if ($topNews == $topCount) {
    echo "✓ Correct! {$topCount} news marked as top.\n";
} else {
    echo "✗ Error! Expected {$topCount} top news, got {$topNews}.\n";
}

echo "\n===========================================\n";
echo "API Test Complete!\n";
echo "===========================================\n\n";

echo "To test via Postman:\n";
echo "1. POST http://localhost:8088/gstvnews/backend2/api/v13/mobile/admin/newsorder-simple\n";
echo "2. Headers: Content-Type: application/json, Authorization: Bearer {token}\n";
echo "3. Body:\n";
echo json_encode([
    'newsIds' => array_slice($newsIds, 0, 13),
    'topCount' => 13
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Expected response:\n";
echo json_encode([
    'message' => 'Order updated successfully',
    'updated_count' => 13,
    'reset_count' => count($newsIds) - 13
], JSON_PRETTY_PRINT) . "\n";
