<?php
/**
 * Temporary Cache Clearing Script
 * 
 * Upload this file to your staging server and access it via browser:
 * https://staging.gstv.in/backend2/clear-cache.php
 * 
 * DELETE THIS FILE after use for security!
 */

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared<br>";
} else {
    echo "⚠️ OPcache not available<br>";
}

// Clear APCu cache
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache cleared<br>";
} else {
    echo "⚠️ APCu not available<br>";
}

// Clear Laravel caches (if artisan is accessible)
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear',
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan optimize:clear'
];

echo "<br><strong>Laravel Cache Commands:</strong><br>";
foreach ($commands as $cmd) {
    echo "Run: <code>$cmd</code><br>";
}

echo "<br><strong>⚠️ IMPORTANT: Delete this file after use!</strong>";
