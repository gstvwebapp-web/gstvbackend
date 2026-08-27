<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
  
    protected $commands = [
        Commands\CallDailyFunction::class,
        Commands\UserRashiFunction::class,
     ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('call:daily-function')->everyMinute();
      	$schedule->command('call:userrashi-function')->dailyAt('07:00');
      
        $schedule->command('call:visitordaily-function')->dailyAt('00:15');
        $schedule->command('call:userexpireplan-function')->dailyAt('00:10');
        $schedule->command('call:giftexpireplan-function')->dailyAt('00:00');
        $schedule->command('call:upcomingdaily-function')->dailyAt('00:15');
        $schedule->command('call:livecricketdaily-function')->everyMinute();
        $schedule->command('call:stockmarket-function')->everyTenMinutes()->between('9:15', '15:30');
        $schedule->command('call:rashi-function')->dailyAt('00:10');
        $schedule->command('call:cleardata-function')->dailyAt('00:00');
        $schedule->command('call:cleardata-function')->dailyAt('06:30');
        $schedule->command('call:logs-move')->everyMinute();
    }

    
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}