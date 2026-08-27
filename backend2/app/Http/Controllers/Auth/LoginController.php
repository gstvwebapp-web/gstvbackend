<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    
    public function showLoginForm()
    {
        try{
            return view('login');
        } catch (\ErrorException $e) {
            // Redirect to the custom 404 page
            return redirect()->route('error.404');
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Check if the user is active
            if (Auth::user()->status === 'Active') {
                // User is authenticated and active
                return redirect('/dashboard');
            } else {
                // User is inactive
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is inactive.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'You have been successfully logged out.');
    }
}
