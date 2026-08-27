<?php
// app/Console/Commands/LiveCricketFunction.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiveCricketFunction extends Command
{
    protected $signature = 'call:livecricketdaily-function';
    protected $description = 'Call daily function to update cricket status';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
	{
        // Fetch raw data for the previous day
        $livedata = DB::table('upcoming_cricket')
            ->where('status', 1)
            ->get();
        $curl = curl_init();
        
        if(!empty($livedata)){
            foreach($livedata as $livekey=>$liveval){
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://cricket-live-line1.p.rapidapi.com/liveMatches',
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
                $livescorearr = array();
                if($data->status == 1){
                    $livecricketdata = $data->data;
                    foreach($livecricketdata as $key11=>$val11){
                        if($val11->series == $liveval->series || $val11->series_id == $liveval->series_id || $val11->match_id == $liveval->match_id){
                            
                            $livescorearr = array('team_a_short'=>$val11->team_a_short,'team_b_short'=>$val11->team_b_short,'team_a_scores'=>$val11->team_a_scores,'team_b_scores'=>$val11->team_b_scores,'team_a_img'=>$val11->team_a_img,'team_b_img'=>$val11->team_b_img,'team_a_over'=>$val11->team_a_over,'team_b_over'=>$val11->team_b_over,'team_a'=>$val11->team_a,'team_b'=>$val11->team_b);
                            // DB::table('live_cricket')->insert([
                            //     'livecricket' => json_encode($livescorearr), // Convert array to JSON before inserting
                            //     'series_id' => $liveval->series_id,
                            //     'series' => $liveval->series,
                            //     'status' => 1,
                            //     'match_id'=> $val11->match_id;
                            //     'created_at' => now(),
                            //     'updated_at' => now(),
                            // ]);

                            $exists = DB::table('live_cricket')
                            ->where('series_id', $val11->series_id)
                            ->where('series', $val11->series)
                            ->where('match_id', $val11->match_id)
                            ->exists();
                          
                            if (!$exists) {
                                // First-time insert (all fields)
                                DB::table('live_cricket')->insert([
                                    'series_id' => $val11->series_id,
                                    'series' => $val11->series,
                                    'match_id' => $val11->match_id,
                                    'livecricket' => json_encode($livescorearr),
                                    'status' => 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                // If record exists, only update necessary fields
                                DB::table('live_cricket')
                                    ->where('series_id', $val11->series_id)
                                    ->where('series', $val11->series)
                                    ->where('match_id', $val11->match_id)
                                    ->update([
                                        'livecricket' => json_encode($livescorearr),
                                        'status' => 1,
                                        'updated_at' => now(),
                                    ]);
                            }

                            $curl11 = curl_init();
                            curl_setopt_array($curl11, array(
                                CURLOPT_URL => 'https://cricket-live-line1.p.rapidapi.com/match/'.$val11->match_id.'/scorecard',
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
                            
                            $response11 = curl_exec($curl11);
                            
                            curl_close($curl11);
                            $data11 = json_decode($response11); 
                            if($data11->status == 1){
                                $livecricketdata = $data11->data;
                                DB::table('live_cricket')
                                ->where('series_id', $val11->series_id)
                                ->where('series', $val11->series)
                                ->where('match_id', $val11->match_id)
                                ->update([
                                    'scorecard' => json_encode($livecricketdata),
                                    'updated_at' => now(),
                                ]);
                            }

                        }
                    }
                }
            }
        }
    }
}
