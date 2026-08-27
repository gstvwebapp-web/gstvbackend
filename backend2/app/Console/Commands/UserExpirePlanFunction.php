<?php
// app/Console/Commands/UserExpirePlanFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserExpirePlanFunction extends Command
{
    protected $signature = 'call:userexpireplan-function';
    protected $description = 'Call user expire function to update news status';

    public function __construct()
    {
        parent::__construct();
    }
	public function handle()
    {
        $todaydate = Carbon::now()->format('Y-m-d');

        // Find all users in the userregister table who have an active plan that expired
        DB::table('user_plan')
            ->where('plan_status', 'Active')
            ->whereDate('plan_expire', '<=', $todaydate)
            ->update(['plan_status' => 'Inactive']);
    }
}
