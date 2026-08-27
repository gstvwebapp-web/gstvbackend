<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Feedback;
use Route;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        
        $submitedata = $request->all();
        $feedback = new Feedback([
            'name' => $submitedata['formData']['name'],
            'email'=> $submitedata['formData']['email'],
            'phone'=> $submitedata['formData']['phone'],
            'comment'=> $submitedata['formData']['comment']
        ]);
        
        $feedback->save();
       
        $data =  array();
        $data['feedback'] =  $submitedata['formData']['name'];
        if (!$data['feedback']) {
            return response()->json($data,404, ['error' => 'Feedback is not stored'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }   
}
