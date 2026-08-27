<?php
/**
 * Check current news status in database
 * Access via: https://staging.gstv.in/backend2/check-news-status.php
 * 
 * DELETE THIS FILE after use!
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$newsIds = [78106, 15, 26, 22, 25, 16, 24, 2, 23, 4, 3, 6, 18, 5, 78147, 20, 19, 9, 17, 8];

echo "<h2>Current News Status in Database</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>Title</th>";
echo "<th>is_home</th>";
echo "<th>topnewsOrder</th>";
echo "<th>is_live_news</th>";
echo "<th>updated_at</th>";
echo "</tr>";

foreach ($newsIds as $id) {
    $news = DB::table('news')
        ->select('id', 'title', 'is_home', 'topnewsOrder', 'is_live_news', 'updated_at')
        ->where('id', $id)
        ->first();
    
    if ($news) {
        echo "<tr>";
        echo "<td>{$news->id}</td>";
        echo "<td>" . substr($news->title, 0, 50) . "...</td>";
        echo "<td>{$news->is_home}</td>";
        echo "<td>{$news->topnewsOrder}</td>";
        echo "<td>{$news->is_live_news}</td>";
        echo "<td>{$news->updated_at}</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<br><br>";
echo "<h3>Expected Values (if API worked correctly):</h3>";
echo "<ul>";
echo "<li>ID 78106: NOT UPDATED (live news)</li>";
echo "<li>IDs 15, 26, 22, 25, 16, 24, 2, 23, 4, 3: is_home=1, topnewsOrder=1-10</li>";
echo "<li>IDs 6, 18, 5, 78147, 20, 19, 9, 17, 8: is_home=0, topnewsOrder=0</li>";
echo "</ul>";

echo "<br><strong>⚠️ DELETE THIS FILE after checking!</strong>";
