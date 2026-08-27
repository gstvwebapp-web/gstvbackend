<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\General;
use Route;

class GeneralController extends Controller
{
    
    public function index(Request $request)
    {
        $formData = $request->input('formData'); // Extract form data from the request
        
        // Check if the email already exists in the database
        $existingGeneral = General::where('email', $formData['email'])->first();
        $data =  array();
        if ($existingGeneral) {
            // If the email already exists, return an error response
            $data['msg'] =  'Email already exists';
            $data['success'] =  'error';
            return response()->json($data,200, [], JSON_UNESCAPED_UNICODE);
        }
        
        // Create a new General model instance
        $general = new General([
            'email' => $formData['email'], // Assuming email is a field in your form data
            'formdata' => json_encode($formData), // Store serialized form data
            'type' => 'Election' // Assuming 'type' is a fixed value
        ]);
        
        // Save the General model to the database
        $general->save();
        
        // Return a success response
        $data['msg'] = 'Form data stored successfully';
        $data['success'] =  'success';
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

   
}
