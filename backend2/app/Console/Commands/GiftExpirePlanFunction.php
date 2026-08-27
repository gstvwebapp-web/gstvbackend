<?php
// app/Console/CommagiftUserExpirePlanFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GiftExpirePlanFunction extends Command
{
    protected $signature = 'call:giftexpireplan-function';
    protected $description = 'Call gift expire function to update news status';

    public function __construct()
    {
        parent::__construct();
    }
	public function handle()
    {
        DB::table('gift')->update(['plan_status' => 'Inactive']);
    }
}
