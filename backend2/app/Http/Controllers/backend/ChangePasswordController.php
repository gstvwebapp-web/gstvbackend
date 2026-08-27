<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;

class ChangePasswordController extends Controller
{
    public function index()
    {
        return view('changepassword.changepassword');
    }

    public function update(Request $request)
    {
        $rules = [
            'currentpassword' => 'required',
            'newpassword' => 'required',
            'newpassword_confirmation'=>'required|same:newpassword'
        ];

        $customMessages = [
            'currentpassword.required' => 'The Current password field is required.',
            'newpassword.required' => 'The New password field is required.',
            'newpassword_confirmation.required' => 'The Confirm password field is required.',
            'newpassword_confirmation.same' => 'The confirmation password does not match the new password.',
        ];
    
        $request->validate($rules, $customMessages);   

        $user = auth()->user();
        

        if (!Hash::check($request->currentpassword, $user->password)) {
            return redirect()->back()->with('error', 'The current password is incorrect.');
        }
        
        $user->update([
            'password' => Hash::make($request->newpassword),
        ]);

        // Log::create([
        //     'userID'=> Auth::user()->id,
        //     'message' => 'Change Password',
        //     'action' => 'Edit',
        //     'contents' => json_encode($request->all()),
        // ]);

        return redirect()->back()->with('success', 'Password changed successfully!');
    }
}
