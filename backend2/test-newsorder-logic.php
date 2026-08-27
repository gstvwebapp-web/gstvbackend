<?php
/**
 * Test script to verify newsorder logic
 * Run: php test-newsorder-logic.php
 */

$newOrder = [78106, 15, 26, 22, 25, 16, 24, 2, 23, 4, 3, 6, 18, 5, 78147, 20, 19, 9, 17, 8];
$newliveOrder = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

// Count live news
$cntlive = 0;
foreach ($newliveOrder as $live) {
    if ($live == 1) {
        $cntlive++;
    }
}

echo "Live news count: $cntlive\n\n";

// Initialize loop limits
$cnt = 0;
$cntloop = 9;
$cntotherloop = 10;

// Adjust loop limits if live news exist
if ($cntlive > 0) {
    $cnt = $cntlive;
    $cntloop = 10 + $cntlive - 1;
    $cntotherloop = 11 + $cntlive - 1;
}

echo "Start position (cnt): $cnt\n";
echo "End position (cntloop): $cntloop\n";
echo "Reset start position (cntotherloop): $cntotherloop\n\n";

echo "===================================\n";
echo "NEWS TO UPDATE (is_home=1):\n";
echo "===================================\n";

$k = 0;
for ($i = $cnt; $i <= $cntloop; $i++) {
    $topOrder = $k + 1;
    echo "Position $i - ID {$newOrder[$i]} - topnewsOrder=$topOrder, is_home=1\n";
    $k++;
}

echo "\n===================================\n";
echo "NEWS TO RESET (is_home=0):\n";
echo "===================================\n";

for ($i = $cntotherloop; $i <= 19; $i++) {
    echo "Position $i - ID {$newOrder[$i]} - topnewsOrder=0, is_home=0\n";
}

echo "\n===================================\n";
echo "LIVE NEWS (NOT UPDATED):\n";
echo "===================================\n";
echo "Position 0 - ID {$newOrder[0]} - NOT UPDATED (live news)\n";
