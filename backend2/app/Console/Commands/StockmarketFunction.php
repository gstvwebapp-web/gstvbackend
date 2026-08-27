<?php
// app/Console/Commands/UpcomingCricketFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockmarketFunction extends Command
{
    protected $signature = 'call:stockmarket-function';
    protected $description = 'Call daily function to update cricket status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //Log::info('StockmarketFunction command started.');

        $sensexData = $this->fetchYahooFinanceData("^BSESN");
        $niftyData = $this->fetchYahooFinanceData("^NSEI");

        $sensexClose = $sensexData['chart']['result'][0]['meta']['regularMarketPrice'] ?? 'N/A';
        $niftyClose = $niftyData['chart']['result'][0]['meta']['regularMarketPrice'] ?? 'N/A';

        $sensextimestamp = $sensexData['chart']['result'][0]['meta']['regularMarketTime'] ?? null;
        $niftytimestamp = $niftyData['chart']['result'][0]['meta']['regularMarketTime'] ?? null;

        $sensexPreviousClose = $sensexData['chart']['result'][0]['meta']['chartPreviousClose'] ?? null;
        $sensexCurrentClose = $sensexData['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;

        if ($sensexPreviousClose && $sensexCurrentClose) {
            $sensexChange = $sensexCurrentClose - $sensexPreviousClose;
            $sensexChangePercent = ($sensexChange / $sensexPreviousClose) * 100;
            
            $sensexChangeFormatted = number_format($sensexChange, 2);
            $sensexChangePercentFormatted = number_format(abs($sensexChangePercent), 2) . '%'; // abs() to avoid double negative sign
            $sensexTrend = $sensexChange >= 0 ? 1 : 0; 
        } else {
            $sensexChangeFormatted = 'N/A';
            $sensexChangePercentFormatted = 'N/A';
            $sensexTrend = null;
        }

        $niftyPreviousClose = $niftyData['chart']['result'][0]['meta']['chartPreviousClose'] ?? null;
        $niftyCurrentClose = $niftyData['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;

        if ($niftyPreviousClose && $niftyCurrentClose) {
            $niftyChange = $niftyCurrentClose - $niftyPreviousClose;
            $niftyChangePercent = ($niftyChange / $niftyPreviousClose) * 100;
            
            $niftyChangeFormatted = number_format($niftyChange, 2);
            $niftyChangePercentFormatted = number_format(abs($niftyChangePercent), 2) . '%';
            $niftyTrend = $niftyChange >= 0 ? 1 : 0; 
        } else {
            $niftyChangeFormatted = 'N/A';
            $niftyChangePercentFormatted = 'N/A';
            $niftyTrend = null;
        }

        $sensexTime = $sensextimestamp
            ? Carbon::createFromTimestamp($sensextimestamp, 'UTC')->setTimezone('Asia/Kolkata')->format('h:i A')
            : 'N/A';
        $niftyTime = $niftytimestamp
            ? Carbon::createFromTimestamp($niftytimestamp, 'UTC')->setTimezone('Asia/Kolkata')->format('h:i A')
            : 'N/A';

        DB::table('settings')
            ->where('id', 1)
            ->update([
                'sensex' => $sensexClose,
                'sensex_time' => $sensexTime,
                'sensex_compareprice' => $sensexChangeFormatted,
                'sensex_percentage' => $sensexChangePercentFormatted,
                'sensexTrend'=>$niftyTrend,
                'nifty' => $niftyClose,
                'nifty_time' => $niftyTime,
                'nifty_compareprice' => $niftyChangeFormatted,
                'nifty_percentage' => $niftyChangePercentFormatted,
                'niftyTrend'=>$niftyTrend,
                'updated_at' => now(),
            ]);

        /*Log::info('StockmarketFunction completed.', [
            'sensex' => $sensexClose,
            'sensex_time' => $sensexTime,
            'nifty' => $niftyClose,
            'nifty_time' => $niftyTime,
        ]);*/
    }
    function fetchYahooFinanceData($ticker) {
        $url = "https://query2.finance.yahoo.com/v8/finance/chart/$ticker?interval=1d";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        
        // Use a browser-like User-Agent to avoid blocking
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }
}
