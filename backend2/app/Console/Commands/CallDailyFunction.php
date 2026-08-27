<?php
// app/Console/Commands/CallDailyFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CallDailyFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:daily-function';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call daily function to update news status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now();  // Current datetime with timezone
        $currentDateTimeFormatted = $currentDateTime->format('Y-m-d\TH:i');  

        DB::table('news')
            ->where('status', 'Schedule')
            ->whereRaw("DATE_FORMAT(scheduleDate, '%Y-%m-%dT%H:%i') = ?", [$currentDateTimeFormatted])
            ->update([
                'status' => 'Active',
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
                'scheduleDate' => null
            ]);
      
        return 0;
    }
}