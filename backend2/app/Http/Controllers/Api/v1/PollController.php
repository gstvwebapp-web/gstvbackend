<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;
use App\Models\Vote;
use App\Models\Votegame;
use App\Models\Gameresult;
use Route;

class PollController extends Controller
{
    public function index()
    {
        $poll = DB::table('polls')->where('status','Active')->get();
        $data =  array();
        $data['poll'] =  $poll;
        if (!$data['poll']) {
            return response()->json($data,404, ['error' => 'Poll is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } 
    public function votes(Request $request)
    {
        $submitedata = $request->all();
        $ipAddress = $request->ip();
        $existingVote = Vote::where('IPaddress', $ipAddress)
                            ->where('pollID', $submitedata['formData']['pollID'])
                            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'You have already voted for this poll.']);
        }
        $vote = new Vote([
            'pollID' => $submitedata['formData']['pollID'],
            'userID'=> $submitedata['formData']['userID'],
            'selectedAnswer'=> $submitedata['formData']['selectedAnswer'],
            'IPaddress'=> $ipAddress
        ]);
        
        $vote->save();
        
        return response()->json(['message' => 'Vote submitted successfully']);
    }  
	public function votegame()
    {
        $votegame = DB::table('votegames')->where('status','Active')->get();
      	$voteresults = DB::table('gameresults')
                  ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                  ->where('votegameID', $votegame[0]->id)
                  ->groupBy('selectedAnswer')
                  ->get();
		$data =  array();
        $data['voteresults'] =  $voteresults;
        $data['votegame'] =  $votegame;
        if (!$data['votegame']) {
            return response()->json($data,404, ['error' => 'Vote Game is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } 

    public function voteResult(Request $request)
    {
        $submitedata = $request->all();
        $ipAddress = $request->ip();
        $existingVote = Gameresult::where('IPaddress', $ipAddress)
                            ->where('votegameID', $submitedata['formData']['votegameID'])
                            ->first();
      	$data =  array();     	
        
        if ($existingVote) {
          	$voteresults = DB::table('gameresults')
                  ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                  ->where('votegameID', $submitedata['formData']['votegameID'])
                  ->groupBy('selectedAnswer')
                  ->get();
            
            $data['voteresults'] =  $voteresults;
          	$data['msgvote'] =  'You have already submitted vote.';
            //return response()->json(['message' => 'You have already submitted vote.']);
          	return response()->json($data, 200, ['message' => 'You have already submitted vote.'], JSON_UNESCAPED_UNICODE);
          	//return response()->json($data,404, ['message' => 'You have already submitted vote.'], JSON_UNESCAPED_UNICODE);
        }
      	
      	$votegame = DB::table('votegames')
                    ->join('gameresults', 'votegames.id', '=', 'gameresults.votegameID')
                    ->where('votegames.status', 'Active')
                    ->where('votegames.id', $submitedata['formData']['votegameID'])
                    ->select('votegames.*', DB::raw('COUNT(*) as totalCount'))
    				->groupBy('votegames.id')
                    ->get();
      
      	if($votegame[0]->totalCount > $votegame[0]->voteforwin)	
        {
          $voteresult = new Gameresult([
              'votegameID' => $submitedata['formData']['votegameID'],
              'userID'=> $submitedata['formData']['userID'],
              'selectedAnswer'=> $votegame[0]->whowin,
              'IPaddress'=> $ipAddress
          ]); 
          $voteresult->save();
        } //else {
          $voteresult = new Gameresult([
              'votegameID' => $submitedata['formData']['votegameID'],
              'userID'=> $submitedata['formData']['userID'],
              'selectedAnswer'=> $submitedata['formData']['selectedAnswer'],
              'IPaddress'=> $ipAddress
          ]); 
          $voteresult->save();
        //}
        
      	
      	$voteresults = DB::table('gameresults')
                  ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                  ->where('votegameID', $submitedata['formData']['votegameID'])
                  ->groupBy('selectedAnswer')
                  ->get();
            
        $data['voteresults'] =  $voteresults;
      	$data['msgvote'] =  'Vote submitted successfully.';
        return response()->json($data, 200, ['message' => 'Vote submitted successfully.'], JSON_UNESCAPED_UNICODE);
        //return response()->json(['message' => 'Vote submitted successfully']);
    } 
  	public function pollResult()
    {      
     $pollresults = DB::table('polls')
      ->select('question', 'answerOption', 'id')
      ->orderByDesc('id')
      ->limit(10)
      ->get();

        if (!empty($pollresults)) {
            foreach ($pollresults as $val) {
                $voteresults[$val->id] = DB::table('votes')
                    ->select('selectedAnswer', DB::raw('COUNT(*) as answerCount'))
                    ->where('pollID', $val->id)
                    ->groupBy('pollID', 'selectedAnswer')
                    ->get();
            }
        }
        $data['pollresults'] =  $pollresults;
        $data['voteresults'] =  $voteresults;
      	$data['msgvote'] =  'Vote submitted successfully.';
        return response()->json($data, 200, ['message' => 'Vote submitted successfully.'], JSON_UNESCAPED_UNICODE);
    } 
}

