<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\PersonalAccessTokenFactory;
use App\Models\User;

class TokenController extends Controller
{
    public function generateToken(Request $request)
    {
       
        
        $request->request->add([
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => 'whdp8srOssdsNo6tebQ8KFQlgdsAIqdoNTMuk5u8',
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ]);
    
        $tokenRequest = $request->create('/oauth/token', 'post');
    
        return app()->handle($tokenRequest);
       
    }
}
