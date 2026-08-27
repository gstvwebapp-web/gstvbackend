<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CustomDailyLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('daily_text');
        $logPath = storage_path('logs/' . date('Y-m-d') . '.txt');
        $logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        
        return $logger;
    }
}
