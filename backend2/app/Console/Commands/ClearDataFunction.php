<?php
// app/Console/Commands/UserExpirePlanFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Services\CloudflareService;
use Carbon\Carbon;

class ClearDataFunction extends Command
{
    protected $signature = 'call:cleardata-function';
    protected $description = 'Call clear data function';
    protected $cloudflareService;

    public function __construct(CloudflareService $cloudflareService)
    {
        parent::__construct();
        $this->cloudflareService = $cloudflareService;
    }
	public function handle()
    {
        Redis::flushall();
        $this->cloudflareService->clearCacheall();
        // $todaydate = Carbon::now()->format('Y-m-d');

        // // Find all users in the userregister table who have an active plan that expired
        // DB::table('user_plan')
        //     ->where('plan_status', 'Active')
        //     ->whereDate('plan_expire', '<=', $todaydate)
        //     ->update(['plan_status' => 'Inactive']);
    }
}
