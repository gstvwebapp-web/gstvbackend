<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "Testing v13 API newsorder Function\n";
echo "===========================================\n\n";

// Get 20 recent news IDs
$newsIds = DB::table('news')
    ->where('status', 'Active')
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->pluck('id')
    ->toArray();

if (count($newsIds) < 20) {
    echo "✗ Error: Need at least 20 news in database. Found: " . count($newsIds) . "\n";
    exit;
}

echo "Found 20 news IDs for testing:\n";
for ($i = 0; $i < 20; $i++) {
    echo ($i + 1) . ". News ID: {$newsIds[$i]}\n";
}
echo "\n";

// Check current state
echo "===========================================\n";
echo "BEFORE: Current State (First 10)\n";
echo "===========================================\n\n";

$currentState = DB::table('news')
    ->whereIn('id', array_slice($newsIds, 0, 10))
    ->select('id', 'title', 'is_home', 'topnewsOrder', 'is_live_news')
    ->get();

foreach ($currentState as $news) {
    echo "ID: {$news->id}\n";
    echo "  Title: " . substr($news->title, 0, 50) . "...\n";
    echo "  is_home: {$news->is_home}\n";
    echo "  topnewsOrder: {$news->topnewsOrder}\n";
    echo "  is_live_news: {$news->is_live_news}\n\n";
}

// Prepare request data (simulating what Postman would send)
$newOrder = $newsIds; // Same order
$oldOrder = $newsIds; // Same order
$newliveOrder = array_fill(0, 20, 0); // All non-live
$tabletop = ""; // Home page
$pageNum = 1;
$entriesPerPage = 20;
$catID = null;

echo "===========================================\n";
echo "SIMULATING API REQUEST\n";
echo "===========================================\n\n";

echo "Request Parameters:\n";
echo "  newOrder: [" . implode(', ', array_slice($newOrder, 0, 5)) . ", ...] (20 items)\n";
echo "  oldOrder: [" . implode(', ', array_slice($oldOrder, 0, 5)) . ", ...] (20 items)\n";
echo "  newliveOrder: [" . implode(', ', array_slice($newliveOrder, 0, 5)) . ", ...] (20 items)\n";
echo "  tabletop: \"{$tabletop}\"\n";
echo "  pageNum: {$pageNum}\n";
echo "  entriesPerPage: {$entriesPerPage}\n";
echo "  catID: " . ($catID === null ? 'null' : $catID) . "\n\n";

// Simulate the API logic
DB::beginTransaction();

try {
    // Clear Redis cache
    \Illuminate\Support\Facades\Redis::del('topnews');
    
    if ($pageNum == 1) {
        if ($tabletop == '') {
            $cnt = 0;
            $cntloop = 9;
            $cntotherloop = 10;
            $k = 0;
            $cntlive = 0;

            // Count live news
            foreach ($newliveOrder as $live) {
                if ($live == 1) {
                    $cntlive++;
                }
            }

            // Adjust loop limits if live news exist
            if ($cntlive > 0) {
                $cnt = $cntlive;
                $cntloop = 10 + $cntlive - 1;
                $cntotherloop = 11 + $cntlive - 1;
            }

            echo "Logic:\n";
            echo "  Live news count: {$cntlive}\n";
            echo "  Update range: positions {$cnt} to {$cntloop} (10 news)\n";
            echo "  Reset range: positions {$cntotherloop} to 19 (10 news)\n\n";

            // Fetch current updated_at values
            $newsTimestamps = DB::table('news')
                ->whereIn('id', $newOrder)
                ->pluck('updated_at', 'id');

            // Bulk update
            $updateData = [];
            for ($i = $cnt; $i <= $cntloop; $i++) {
                $updateData[] = [
                    'id' => $newOrder[$i],
                    'topnewsOrder' => $k + 1,
                    'is_home' => 1,
                    'catOrder' => 0,
                    'userID' => 1,
                    'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                ];
                $k++;
            }

            DB::table('news')->upsert($updateData, ['id'], ['topnewsOrder', 'is_home', 'catOrder', 'updated_at']);
            echo "✓ Updated " . count($updateData) . " news with is_home=1\n";

            // Reset remaining news
            $resetData = [];
            for ($i = $cntotherloop; $i <= 19; $i++) {
                $resetData[] = [
                    'id' => $newOrder[$i],
                    'topnewsOrder' => 0,
                    'is_home' => 0,
                    'userID' => 1,
                    'updated_at' => $newsTimestamps[$newOrder[$i]] ?? now(),
                ];
            }

            DB::table('news')->upsert($resetData, ['id'], ['topnewsOrder', 'is_home', 'updated_at']);
            echo "✓ Reset " . count($resetData) . " news with is_home=0\n\n";
        }
    }

    DB::commit();
    echo "✓ Transaction committed\n\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Check new state
echo "===========================================\n";
echo "AFTER: New State (First 10)\n";
echo "===========================================\n\n";

$newState = DB::table('news')
    ->whereIn('id', array_slice($newsIds, 0, 10))
    ->select('id', 'title', 'is_home', 'topnewsOrder')
    ->orderBy('topnewsOrder', 'ASC')
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

if ($topNews == 10) {
    echo "✓ Correct! 10 news marked as top.\n";
} else {
    echo "✗ Error! Expected 10 top news, got {$topNews}.\n";
}

echo "\n===========================================\n";
echo "Postman Request Example\n";
echo "===========================================\n\n";

echo "POST https://staging.gstv.in/backend2/api/v13/mobile/admin/newsorder\n\n";

echo "Headers:\n";
echo "  Content-Type: application/json\n";
echo "  Authorization: Bearer {your_token}\n\n";

echo "Body (raw JSON):\n";
echo json_encode([
    'newOrder' => $newsIds,
    'oldOrder' => $newsIds,
    'newliveOrder' => $newliveOrder,
    'tabletop' => $tabletop,
    'pageNum' => $pageNum,
    'entriesPerPage' => $entriesPerPage,
    'catID' => $catID
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "Expected Response:\n";
echo json_encode([
    'message' => 'Order updated successfully'
], JSON_PRETTY_PRINT) . "\n\n";

echo "===========================================\n";
echo "✓ Test Complete!\n";
echo "===========================================\n";
