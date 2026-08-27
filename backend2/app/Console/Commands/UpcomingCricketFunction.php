<?php
// app/Console/Commands/UpcomingCricketFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpcomingCricketFunction extends Command
{
    protected $signature = 'call:upcomingdaily-function';
    protected $description = 'Call daily function to update cricket status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
	{
        // Fetch raw data for the previous day
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://cricket-live-line1.p.rapidapi.com/upcomingMatches',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cache-Control: no-cache',
            'Postman-Token: <calculated when request is sent>',
            'Host: <calculated when request is sent>',
            'User-Agent: PostmanRuntime/7.39.1',
            'Accept: */*',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'x-rapidapi-host: cricket-live-line1.p.rapidapi.com',
            'x-rapidapi-key: 7805cb17fdmshde92ba71bbc3f30p1a2fd4jsn74493de73920'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response);
        if($data->status == true){
            $cricketdata = $data->data;
            $validSeriesIds = array_column(
                array_filter($cricketdata, function ($match) {
                    return isset($match->series_type) && ($match->series_type === "International" || $match->series_type === "League");
                }),
                'series_id'
            );
            
            // Step 2: Filter all matches where series_id exists in the above list
            $filteredData = array_filter($cricketdata, function ($match) use ($validSeriesIds) {
                return in_array($match->series_id, $validSeriesIds);
            });
            
            // Convert to indexed array
            $filteredData = array_values($filteredData);
            /*$filteredData = array_filter($cricketdata, function ($match) {
                return isset($match->series_type) && ($match->series_type === "International" || $match->series_type === "League");
            }); 
            $matches = array_values($filteredData);
            $seenSeries = [];
            $filteredMatches = [];

            foreach ($matches as $match) {
                $key = $match->series_id; 

                if (!in_array($key, $seenSeries)) {
                    $seenSeries[] = $key;
                    $filteredMatches[] = $match;
                }
            }*/

            foreach($filteredData as $key=>$val){
                $exists = DB::table('upcoming_cricket')
                    ->where('series', $val->series)
                    ->where('series_id', $val->series_id)
                    ->where('match_id',$val->match_id)
                    ->exists();
                $date_wise = explode(',',$val->date_wise);
                $formattedDate = date('Y-m-d', strtotime($date_wise[0]));
                if ($exists) {
                    // Update only `updated_at`
                    DB::table('upcoming_cricket')
                        ->where('series', $val->series)
                        ->where('series_id', $val->series_id)
                        ->where('match_id', $val->match_id)
                        ->update([
                            'date_wise' => $val->date_wise,
                            'cricketdate'=>$formattedDate,
                            'match_time' => $val->match_time,
                            'team_a' => $val->team_a.' ('.$val->team_a_short.')',
                            'team_b' => $val->team_b.' ('.$val->team_b_short.')',
                            'venue' => $val->venue,
                            'match_type' => $val->match_type,
                            'match_id'=>$val->match_id,
                            'status' => 0,
                            'updated_at' => now(), // Only update this field
                        ]);
                } else {
                    // Insert with both `created_at` and `updated_at`
                    DB::table('upcoming_cricket')->insert([
                        'series' => $val->series,
                        'series_id' => $val->series_id,
                        'date_wise' => $val->date_wise,
                        'cricketdate'=>$formattedDate,
                        'match_time' => $val->match_time,
                        'team_a' =>$val->team_a.' ('.$val->team_a_short.')',
                        'team_b' => $val->team_b.' ('.$val->team_b_short.')',
                        'venue' => $val->venue,
                        'match_id'=>$val->match_id,
                        'match_type' => $val->match_type,
                        'status' => 0,
                        'created_at' => now(), // Insert this only on creation
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
