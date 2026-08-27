<?php
// app/Console/Commands/UserRashiFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseMessageService;
use Carbon\Carbon;

class UserRashiFunction extends Command
{
    protected $signature = 'call:userrashi-function';
    protected $description = 'Call user rashi function';
    protected $firebasemessageService;
    public function __construct(FirebaseMessageService $firebasemessageService)
    {
        parent::__construct();
        $this->firebasemessageService = $firebasemessageService;
    }
  public function handle()
{
    Log::info('UserRashiFunction command started at: ' . now());

    try {
        $todayDate = Carbon::now()->toDateString();

        // Fetch tokens with user details
        $tokens = DB::table('notification')
            ->join('userregister', 'notification.user_id', '=', 'userregister.id')
            //->whereNotNull('notification.user_id
         	->whereRaw('notification.user_id IS NOT NULL')
         // ->where('notification.user_id',85)
          //->whereIn('notification.user_id', [85, 7914, 6035])
            ->select(
                'notification.token',
                'userregister.birthdate',
                'userregister.id as user_id',
                'userregister.firstname',
                'userregister.lastname'
            )
            ->distinct()
            ->get();

        $type = 'rashifal';
        $newFeatureImage = '';
        $url = '';
        $news = '';

        foreach ($tokens as $row) {
            // Skip if token is empty
            if (empty($row->token)) {
                Log::warning("Skipped user_id: {$row->user_id} because token is empty.");
                continue;
            }

            $title = 'આજનું રાશિફળ';
            $body = ucfirst($row->firstname) . ' ' . ucfirst($row->lastname) . ' વાંચો તમારુ આજનું રાશિફળ';

            $this->firebasemessageService->sendNotification1(
                $title,
                $body,
                $row->token,
                $type,
                $newFeatureImage,
                $url,
                $news
            );

            //Log::info("Notification sent to user_id: {$row->user_id}, token: {$row->token}, birthdate: {$row->birthdate}");
        }

    } catch (\Exception $e) {
        Log::error('Error in UserRashiFunction command: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
    }

    Log::info('UserRashiFunction command ended at: ' . now());
}

	

}

