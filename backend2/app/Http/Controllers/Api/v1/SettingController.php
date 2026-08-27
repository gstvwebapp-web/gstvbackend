<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Route;

class SettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('settings')->get();
        $data =  array();
        $data['setting'] =  $setting;
        if (!$data['setting']) {
            return response()->json($data,404, ['error' => 'Setting is not found'], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }   
}
