<?php

namespace App\Services;

use Spatie\Analytics\AnalyticsFacade as Analytics;
use Spatie\Analytics\Period;
/*
class AnalyticsService
{
    public function getActiveUsersBySource()
    {
        // Define the start and end time for the last 30 minutes
        $startTime = Carbon::now()->subMinutes(30)->toIso8601String();
        $endTime = Carbon::now()->toIso8601String();

        $analyticsData = Analytics::performQuery(
            ['start-date' => $startTime, 'end-date' => $endTime], // Last 30 minutes
            'ga:activeUsers',
            [
                'metrics' => 'ga:activeUsers',
                'dimensions' => 'ga:sourceMedium',
                'sort' => '-ga:activeUsers',
            ]
        );

        return collect($analyticsData['rows'] ?? [])->map(function ($row) {
            return [
                'source' => $row[0], 
                'active_users' => (int) $row[1], 
            ];
        });
    }
}*/

class AnalyticsService
{
    public function getActiveUsersBySource()
    {
        $analyticsData = Analytics::performQuery(
            Period::days(1), // Last 7 days
            'ga:activeUsers',
            [
                'metrics' => 'ga:activeUsers',
                'dimensions' => 'ga:sourceMedium', // First user source
                'sort' => '-ga:activeUsers', // Sort by most active users
            ]
        );
        return collect($analyticsData['rows'] ?? [])->map(function ($row) {
            return [
                'source' => $row[0], // Source (e.g., google / organic)
                'active_users' => (int) $row[1], // Number of active users
            ];
        });
    }
}
