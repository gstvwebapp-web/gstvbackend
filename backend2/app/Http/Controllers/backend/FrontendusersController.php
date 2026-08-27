<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Frontendusers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Route;
use App\Models\Log;

class FrontendusersController extends Controller
{
    // Get all pages
    public function index()
    {
        $user = DB::table('userregister')->get();
        $data =  array();
        $data['user'] =  $user;
        return view('frontendusers.list', compact('data'));
    }

}
