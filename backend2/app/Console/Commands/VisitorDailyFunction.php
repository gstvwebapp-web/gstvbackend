<?php
// app/Console/Commands/VisitorDailyFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitorDailyFunction extends Command
{
    protected $signature = 'call:visitordaily-function';
    protected $description = 'Call daily function to update news status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
	{
        // Fetch raw data for the previous day
        $currentDateTime = Carbon::now();
        $previousDay = $currentDateTime->copy()->subDay()->format('Y-m-d');

        // Fetch raw data for the previous day
        $sessions = DB::table('visitor_sessions')
            ->select('user_id', 'end_time', 'start_time', DB::raw('DATE(created_at) as date'))
           // ->whereDate('created_at', $previousDay)
            ->orderBy('user_id')
            ->orderBy('end_time')
            ->get();
        
        // Calculate durations using PHP
        $durations = [];
        $prevUserId = null;
        $prevEndTime = null;

        foreach ($sessions as $session) {
            // Ensure end_time and start_time are not null before calculating difference
            if (!empty($session->start_time) && !empty($session->end_time)) {
                $timeDifference = Carbon::parse($session->start_time)->diffInSeconds(Carbon::parse($session->end_time));

                if ($prevUserId === $session->user_id) {
                    $durations[$session->date][$session->user_id] = ($durations[$session->date][$session->user_id] ?? 0) + $timeDifference;
                } else {
                    $durations[$session->date][$session->user_id] = $timeDifference;
                }
            }
            if (isset($durations[$session->date][$session->user_id]) && $durations[$session->date][$session->user_id] < 0) {
                $durations[$session->date][$session->user_id] = 0;
            }

            $prevUserId = $session->user_id;
            $prevEndTime = $session->end_time;
        }

		 $settingdata = DB::table('settings')->select('spendtime', 'point')->get();

        // Update or insert durations into user_durations table
        DB::transaction(function () use ($durations, $settingdata) {
            foreach ($durations as $date => $users) {
                foreach ($users as $userId => $totalDuration) {
                    $totalcoin = (int) round(($totalDuration * $settingdata[0]->point) / $settingdata[0]->spendtime);
                    DB::table('user_durations')->updateOrInsert(
                        ['user_id' => $userId, 'date' => $date],
                        [
                            'total_duration' => $totalDuration,
                            'points' => $totalcoin,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });
        

        // Delete entries older than 2 days
        $twoDaysAgo = Carbon::now()->subDays(2)->format('Y-m-d');
        DB::table('visitor_sessions')
            ->where('created_at', '<', $twoDaysAgo)
            ->delete();

        return 0;
    }
}
