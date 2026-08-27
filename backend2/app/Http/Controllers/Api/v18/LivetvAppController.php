<?php
namespace App\Http\Controllers\Api\v18;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;


class LivetvAppController extends Controller
{
    public function livetvlink()
    {
      $data =  array('livetv_js'=>'https://cdnplay.peanutsquare.com/560828843292539228453915.m3u8','policy'=>'https://www.gstv.in/privacy-policy','contactus'=>'https://www.gstv.in/contact-us','terms'=>'https://www.gstv.in/terms-condition');
      return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
   }
