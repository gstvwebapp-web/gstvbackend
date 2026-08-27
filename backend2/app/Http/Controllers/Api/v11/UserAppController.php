<?php

namespace App\Http\Controllers\Api\v11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Userregister;
use App\Models\Usercategory;
use App\Models\Usercity;
use App\Models\Category;
use Illuminate\Support\Str;
use Route;

class UserAppController extends Controller
{

    public function sendOtp(Request $request)
    {
        $mobile = $request->input('phone');
        $name = $request->input('name','');
        $last_name = $request->input('last_name','');
        $city = $request->input('city','');
        $token = $request->input('token');
        $code = rand(100000, 999999); // generate a random 6-digit OTP
        $auth_key = urlencode('3557umaVX0RUUuGojlzhsn');
        // Check if the mobile number already exists in the database
        $mobileExist = Userregister::where('mobile', $mobile)->first();
    	if($mobile=='9898988021')
        {
            $code = '988021'; 
        }
       // Message details
       $mobiles = array($mobile);
       $sender = urlencode('GSTVIN');
       $message =  rawurlencode($code.' is your GSTV Web/App OTP. Please do not share this code with anyone else. 2zLdVN23quJ - GSTV (SHREYARTH AASPAS LIMITED)');
       $mobiles = implode(',', $mobiles);
       $datasms = array('auth_key' => $auth_key, 'mobiles' => $mobiles, "sender" => $sender, "message" => $message,'templateid'=>1207172414206272455);

       // Send the POST request with cURL
       $ch = curl_init('https://sms.shreetripada.com/api/sendapi.php'); // Check the endpoint
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datasms)); // Use http_build_query to encode the data
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $response = curl_exec($ch);
       $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $referralcode = substr(str_shuffle($characters), 0, 6); 

        if ($httpCode == 200) {
            if ($mobileExist) {
                $data = [
                    'mobile' => $mobile,
                    'code' => $code,
                    'token'=>$token,
                ];
                $update = [
                    'code' => $code, // always update code
                ];

                // only set firstname if $name is not empty (not null and not only whitespace)
                if (isset($name) && trim((string)$name) !== '') {
                    $update['firstname'] = $name;
                  	$data['firstname'] = $name;
                }

                // only set address if $city is not empty
                if (isset($city) && trim((string)$city) !== '') {
                    $update['address'] = $city;
                    $data['address'] = $city;
                }

                // only set lastname if $last_name is not empty
                if (isset($last_name) && trim((string)$last_name) !== '') {
                    $update['lastname'] = $last_name;
                  	$data['lastname'] = $last_name;
                }

                // perform update (no fields will be set to blank if user passed empty values)
                DB::table('userregister')->where('mobile', $mobile)->update($update);
                //DB::table('userregister')->where('mobile', $mobile)->update(['code' => $code,'firstname' => $name,'address' => $city,'lastname'=>$last_name]);
                $data['msg'] = 'OTP sent successfully.';
                return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                // Mobile does not exist, send SMS and insert into database
                $data = [
                    'mobile' => $mobile,
                    'code' => $code,
                    'token'=>$token,
                    'firstname' => $name,
                    'lastname'=>$last_name,
                    'address' => $city,
                    'user_points' => 100,
                    'referralcode' => $referralcode,
                    'created_at'=>now()
                ];
                DB::table('userregister')->insert($data);
                $data['msg'] = 'OTP sent successfully.';
                return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $data['msg'] = 'Failed to connect to SMS gateway. Please try again.';
                    return response()->json($data, 400, [], JSON_UNESCAPED_UNICODE);
            return response()->json(['success' => false,'msg' => 'Failed to connect to SMS gateway. Please try again.', 'http_code' => $httpCode, 'error' => $response], 400);
        }
    }
    public function resendOtp(Request $request)
{
    $mobile = $request->input('phone');
    $code = rand(100000, 999999); // generate a random 6-digit OTP

    // Check if the mobile number exists in the database
    $mobileExist = Userregister::where('mobile', $mobile)->first();

    if (!$mobileExist) {
        return response()->json(['success' => false, 'msg' => 'Mobile number not registered.'], 404);
    }

    $apiKey = urlencode('3557umaVX0RUUuGojlzhsn');
    $sender = urlencode('GSTVIN');
    $message =  rawurlencode($code.' is your GSTV Web/App OTP. Please do not share this code with anyone else. 2zLdVN23quJ - GSTV (SHREYARTH AASPAS LIMITED)');
    $mobiles = implode(',',  [$mobile]);
    $datasms = array('auth_key' => $apiKey, 'mobiles' => $mobiles, "sender" => $sender, "message" => $message,'templateid'=>1207172414206272455);

    // Send the POST request with cURL
    $ch = curl_init('https://sms.shreetripada.com/api/sendapi.php'); // Check the endpoint
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datasms)); // Use http_build_query to encode the data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // // Check the HTTP response code and the API response
    if ($httpCode == 200) {
            DB::table('userregister')->where('mobile', $mobile)->update(['code' => $code]);
            $data = [
                'mobile' => $mobile,
                'code' => $code,
                'msg' => 'OTP sent successfully.'
            ];
            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } else {
        $data['msg'] = 'Failed to connect to SMS gateway. Please try again.';
        return response()->json($data, 400, [], JSON_UNESCAPED_UNICODE);
    }
}
public function resendOtpnull(Request $request)
{
    $mobile = $request->input('phone');

    // Check if the OTP matches
    $user = Userregister::where('mobile', $mobile)->first();
    if (!empty($user)) {
        DB::table('userregister')->where('mobile', $user->mobile)->update(['code' => '']);
        // OTP is correct, proceed with further actions (e.g., login the user)
        $data = [
            'mobile' => $mobile,
            'user_id' => $user->id,
            'msg' => 'OTP send agian.'
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        // return response()->json(['success' => true, 'msg' => 'OTP verified successfully.'], 200);
    }
}
public function verifyOtp(Request $request)
{
    $otp = $request->input('otp');
    $mobile = $request->input('phone');

    // Check if the OTP matches
    $user = Userregister::where('mobile', $mobile)->where('code', $otp)->first();
    if (!empty($user)) {
        DB::table('userregister')->where('mobile', $user->mobile)->update(['code' => '']);
        // OTP is correct, proceed with further actions (e.g., login the user)
        $profileSts = 0;
        if ($user->firstname !="" && $user->email !="" && $user->birthdate !="" && $user->gender !="") {
            $profileSts = 1;
        }
        $data = [
            'mobile' => $mobile,
            'user_id' => $user->id,
            'profileSts'=>$profileSts,
            'referralcode' => $user->referralcode,
            'firstname'=>$user->firstname,
            'lastname'=>$user->lastname,
            'profileImg'=>$user->profileImg,
            'msg' => 'OTP verified successfully.'
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        // return response()->json(['success' => true, 'msg' => 'OTP verified successfully.'], 200);
    } else {
        $data = [
            'mobile' => $mobile,
            'msg' => 'Invalid OTP. Please try again.'
        ];
        return response()->json($data, 400, [], JSON_UNESCAPED_UNICODE);
    }
}
public function verifyMPIN(Request $request)
{
    $mpin = $request->input('mpin');
    $phone = $request->input('phone');
   $user = Userregister::where('mpin', $mpin)->where('mobile', $phone)->first();
    if (!empty($user)) {
        $profileSts = 0;
        if ($user->firstname !="" && $user->email !="" && $user->birthdate !="" && $user->gender !="") {
            $profileSts = 1;
        }
        $data = [
            'mobile' => $user->mobile,
            'user_id' => $user->id,
            'profileSts'=>$profileSts,
            'firstname'=>$user->firstname,
            'lastname'=>$user->lastname,
            'profileImg'=>$user->profileImg,
            'referralcode' => $user->referralcode,
            'msg' => 'User login successfully.'
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    } else {
        $data = [
            'msg' => 'Invalid MPIN. Please try again.'
        ];
        return response()->json($data, 400, [], JSON_UNESCAPED_UNICODE);
    }
}
public function updateProfile(Request $request)
{
    try {
        // Retrieve user ID from the request
        $userId = $request->input('user_id');

        // Fetch user data using the user ID
        $user = Userregister::find($userId);

        if (!$user) {
            return response()->json([
                'error' => 'User not found.'
            ], 404);
        }

        // Validate request inputs with unique email check (excluding current user's email)
        $validator = \Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:userregister,email,' . $userId . ',id', // Unique email excluding current user
            'birthdate' => 'required|date',
            'gender' => 'required|string',
           // 'profileImg' => 'nullable|image|max:2048'
        ]);
        // if ($request->has('email')) {
        //     if ($validator->fails()) {
        //         return response()->json([
        //             'msg' => 'The email has already been taken.'
        //         ], 400);
        //     }
        // }

        // Update user data
        if ($request->has('firstname')) $user->firstname = $request->input('firstname');
        if ($request->has('lastname')) $user->lastname = $request->input('lastname');
        if ($request->has('city')) $user->city = $request->input('city');
        if ($request->has('email')) $user->email = $request->input('email');
        if ($request->has('birthdate')) $user->birthdate = $request->input('birthdate');
        if ($request->has('bdaytime')) $user->bdaytime = $request->input('bdaytime');
        if ($request->has('bdaytimeampm')) $user->bdaytimeampm = $request->input('bdaytimeampm');
        if ($request->has('birthdateplace')) $user->birthdateplace = $request->input('birthdateplace');
        if ($request->has('gender')) $user->gender = $request->input('gender');
        if ($request->has('mpin')) $user->mpin = $request->input('mpin');
        if ($request->has('latitude')) $user->latitude = $request->input('latitude');
        if ($request->has('longitude')) $user->longitude = $request->input('longitude');
        // Handle profile image upload
        if ($request->hasFile('profileImg')) {
            $imageName = time() . '.' . $request->profileImg->extension();
            $path = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/public/assets/users';
           // public_path('assets/users'), $imageName;exit;
            $request->profileImg->move($path,$imageName);
            $user->profileImg = config('app.front_url').'/public/assets/users/'.$imageName;
        }
	
        // Update profile status based on conditions
        $user->profileSts = 0;
        if ($user->firstname != "" && $user->email != "" && $user->birthdate != "" && $user->gender != "") {
            $user->profileSts = 1;
        }

        // Save updated user data
        if ($user->save()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'error' => 'Failed to update profile.'
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception occurred: ' . $e->getMessage()
        ], 500);
    }
}


    public function userCategory(Request $request)
    {
        $user_id = $request->input('user_id');
        $categoryIdsJson = $request->input('category_id');
        $categoryIds = json_decode($categoryIdsJson);
        DB::table('user_category')->where('user_id', $user_id)->delete();
        if(!empty($categoryIds)){
            foreach($categoryIds as $cat_id){
                $datacat = [
                    'user_id' => $user_id,
                    'category_id' => $cat_id,
                ];
                $usercat = new Usercategory($datacat);
                $usercat->save();
            }
        }
        $data = [
            'msg' => 'Category Updated Successfully'
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function userCity(Request $request)
    {
        $user_id = $request->input('user_id');
        $cityIdsJson = $request->input('city_id');
        $cityIds = json_decode($cityIdsJson);
        DB::table('user_city')->where('user_id', $user_id)->delete();
        if(!empty($cityIds)){
            foreach($cityIds as $cat_id){
                $datacity = [
                    'user_id' => $user_id,
                    'city_id' => $cat_id,
                ];
                $usercat = new Usercity($datacity);
                $usercat->save();
            }
        }
        $data = [
            'msg' => 'City Updated Successfully'
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function getCategoryCity(Request $request)
    {
        $user_id = $request->input('user_id');  
        $categories = Usercategory::where('user_id', $user_id)->pluck('category_id')->map(function($item) {
            return (int) $item;
        });
        $cities = Usercity::where('user_id', $user_id)->pluck('city_id')->map(function($item) {
            return (int) $item;
        });
        $data = [
            'category' => $categories,
            'city' => $cities,
        ];
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
    public function viewProfile(Request $request)
    {
        $userId = $request->input('user_id');
        // Fetch user data using the user ID
        $user = Userregister::find($userId);

        if ($user) {
            // Fetch the city category if it exists
            $category = Category::find($user->city);
            
            // Ensure the category is not null before accessing the title property
            if ($category) {
                $user->city = array('id' => $category->id, 'title' => $category->title);
            } else {
                $user->city = array('id' => null, 'title' => 'City not found');
            }

            return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([], 400, [], JSON_UNESCAPED_UNICODE);
        }
    }
    // public function usernotification(Request $request)
    // {
    //     $user = Userregister::find(1); // Replace with your user retrieval logic
    //     if ($user && $user->token) {
    //         $sent = $user->notify(new FirebaseNotification());
    //         if($sent) {
    //             echo "true"; die;
    //         } else {
    //             echo "false"; die;
    //         }

    //         return response()->json(['success' => true, 'msg' => 'Notification sent.'], 200);
    //     } else {
    //         return response()->json(['success' => false, 'msg' => 'User not found or missing device token.'], 404);
    //     }
    // }
}
