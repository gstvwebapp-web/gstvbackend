<?php

namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ViewCountService
{
    protected $allowedTables = ['newscount', 'webstorycount', 'epapercount','pollcount'];
    public function incrementViewCount(string $type, int $id)
    {
        if (!in_array($type, $this->allowedTables)) {
            throw new \Exception("Invalid table type provided.");
        }

        $cacheKey = "view_counts:{$type}_".config('app.webtype');

        // Increment in cache
        $counts = Cache::get($cacheKey, []);
        $counts[$id] = ($counts[$id] ?? 0) + 1;

        Cache::put($cacheKey, $counts, now()->addHours(12)); // expire in 12h
    }
   public function incrementViewLoginCount(string $type, int $id)
    {
        if (!in_array($type, $this->allowedTables)) {
            throw new \Exception("Invalid table type provided.");
        }

        $cacheKey = "view_logincounts:{$type}_".config('app.webtype');

        // Increment in cache
        $counts = Cache::get($cacheKey, []);
        $counts[$id] = ($counts[$id] ?? 0) + 1;

        Cache::put($cacheKey, $counts, now()->addHours(12)); // expire in 12h
    }
}
