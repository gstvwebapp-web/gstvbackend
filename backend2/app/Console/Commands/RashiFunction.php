<?php
// app/Console/Commands/UserExpirePlanFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RashiFunction extends Command
{
    protected $signature = 'call:rashi-function';
    protected $description = 'Call user rashi description';

    public function __construct()
    {
        parent::__construct();
    }
	public function handle()
    {
        $rashidataList = DB::table('rashifal')
            ->where('status', 'Active')
            ->get();

        $gujaratiLabels = [
            'physique' => 'શરીર',
            'status' => 'સ્થિતિ',
            'finances' => 'નાણાકીય',
            'relationship' => 'સંબંધ',
            'career' => 'કારકિર્દી',
            'travel' => 'મુસાફરી',
            'family' => 'પરિવાર',
            'friends' => 'મિત્રો',
            'health' => 'આરોગ્ય',
            'total_score' => 'કુલ સ્કોર',
            'lucky_color' => 'શુભ રંગ',
            'lucky_color_code' => 'શુભ રંગ કોડ',
            'lucky_number' => 'શુભ નંબર',
        ];

        foreach ($rashidataList as $rashi) {

            $zodiac = $rashi->id;
            $date = Carbon::today()->format('d/m/Y');

            $sunurl = "https://api.vedicastroapi.com/v3-json/prediction/daily-sun?zodiac=$zodiac&date=$date&show_same=true&api_key=86515592-f424-589f-8f62-c06b08e36b4a&lang=gu&split=true&type=big";

            $moonurl = "https://api.vedicastroapi.com/v3-json/prediction/daily-moon?zodiac=$zodiac&date=$date&show_same=true&api_key=86515592-f424-589f-8f62-c06b08e36b4a&lang=gu&split=true&type=big";

            // API function
            $getApiData = function ($url) {

                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 30,
                ]);

                $response = curl_exec($curl);
                curl_close($curl);

                return json_decode($response, true);
            };

            $sunData = $getApiData($sunurl);
            $moonData = $getApiData($moonurl);

            $rashidata = '';

            $appendResponses = function ($data, $labels) use (&$rashidata) {

                if (isset($data['response']['bot_response'])) {

                    foreach ($data['response']['bot_response'] as $category => $details) {

                        if (isset($labels[$category])) {

                            $translated = $labels[$category];
                            $response = $details['split_response'] ?? '';

                            $rashidata .= "<p><b>$translated:</b> $response</p>";
                        }
                    }
                }
            };

            if ($sunData) {

                $rashidata .= '<h4 class="text-center pb-3">દૈનિક સૂર્ય રાશિફળ</h4>';

                $appendResponses($sunData, $gujaratiLabels);

                $luckyColorSun = $sunData['response']['lucky_color'] ?? '';
                $luckyNumberSun = implode(', ', $sunData['response']['lucky_number'] ?? []);

                $rashidata .= "<p><b>લકી કલર:</b> $luckyColorSun</p>";
                $rashidata .= "<p><b>લકી નંબર:</b> $luckyNumberSun</p>";
            }

            $rashidata .= '<hr>';

            if ($moonData) {

                $rashidata .= '<h4 class="text-center pb-3">દૈનિક ચંદ્ર રાશિફળ</h4>';

                $appendResponses($moonData, $gujaratiLabels);

                $luckyColorMoon = $moonData['response']['lucky_color'] ?? '';
                $luckyNumberMoon = implode(', ', $moonData['response']['lucky_number'] ?? []);

                $rashidata .= "<p><b>લકી કલર:</b> $luckyColorMoon</p>";
                $rashidata .= "<p><b>લકી નંબર:</b> $luckyNumberMoon</p>";
            }

            /*
            |--------------------------------------------------------------------------
            | Update Existing Record Only
            |--------------------------------------------------------------------------
            */

            DB::table('rashifaldata')
                ->where('rashifal_id', $zodiac)
                ->update([
                    'rashidescription' => $rashidata,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        }

    }
}
