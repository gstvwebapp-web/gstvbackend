<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MoveLogFunction extends Command
{
    protected $signature = 'call:logs-move';
    protected $description = 'Move log data from main DB to staging DB';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 1. Get the first 10 log entries by ID
        $logs = DB::table('logs')
                  ->orderBy('id', 'ASC')
                  ->limit(500)
                  ->get();

        if ($logs->isEmpty()) {
            $this->info('No logs found to move.');
            return;
        }

        // Extract IDs for deletion after insert
        $ids = $logs->pluck('id');

        // 2. Insert into staging DB
        DB::connection('mysql_livelog')->table('logs')->insert(
            $logs->map(fn ($log) => (array) $log)->toArray()
        );

        // 3. Delete from main DB
        DB::table('logs')->whereIn('id', $ids)->delete();

        Log::info('Log table moved to staginglog DB at ' . Carbon::now());
        $this->info('Moved ' . $logs->count() . ' log(s) to staging.');
    }
}
