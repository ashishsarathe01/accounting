<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserMpin;
use App\Models\UserOtp;

class AuthController extends Controller
{
     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    
    public function register(Request $request){
        
        $validator = Validator::make($request->all(), [

            'name' => 'required|string|between:2,100',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|same:confirm_password|min:6|max:32',
            'mobile_no' => 'required|string|min:10|max:12|unique:users',
            
        ], [            
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password length should be at least 8 characters.',
            'password.max' => 'Password length should not exceed 32 characters.',
            'mobile_no.required' => 'Mobile no is required.',
            'mobile_no.min' => 'Mobile no length should be at least 10 characters.',
            'mobile_no.max' => 'Mobile no length should not exceed 12 characters.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::create([
            'name' =>$request->name,
            'email' =>$request->email,
            'mobile_no' =>$request->mobile_no,
            'password' =>\Hash::make($request->password)
        ]);

       $token = $user->createToken('Token')->accessToken;
       if (!$token) {
        return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
    } else {
       return response()->json(['code' => 200, 'message' => 'User Created successfully!','token'=> $token,'userData'=>$user]);
    }
    }
    public function login(Request $request){
        $data = [
            'email' =>$request->email,
            'password' =>$request->password
        ];

        if(auth()->attempt($data)){
        $token = auth()->user()->createToken('Token')->accessToken;
       return response()->json(['token'=> $token,'error'=>'Login Successfully'],200);
    } else {
        return response()->json(['error'=> 'unauthoriesd'],404);
    }
}

public function sendOtp(Request $request)
    {
        /* Validate Data */
  
        $validator = Validator::make($request->all(), [

            'mobile_no' => 'required|exists:users,mobile_no',  
        ], [            
            'mobile_no.required' => 'Mobile no is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        /* Generate An OTP */
        $userOtp = $this->generateOtp($request->mobile_no);
       // $userOtp->sendSMS($request->mobile_no);
        if($this->sendSMS($request->mobile_no,$userOtp->otp))
        {
            return response()->json(['code' => 200, 'message' => 'OTP has been sent on Your Mobile Number.','user_id'=> $userOtp->user_id]);
        }
        else
        {
            return response()->json(['code' => 422, 'message' => 'Error in sending otp, try again!']);
        }
        
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function generateOtp($mobile_no)
    {
        $user = User::where('mobile_no', $mobile_no)->first();
  
        /* User Does not Have Any Existing OTP */
        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();
  
        $now = now();
  
        if($userOtp && $now->isBefore($userOtp->expire_at)){
            return $userOtp;
        }
  
        /* Create a New OTP */
        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => rand(1234, 9999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function verification($user_id)
    {
        return view('auth.otpVerification')->with([
            'user_id' => $user_id
        ]);
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function loginWithOtp(Request $request)
    {
        /* Validation */
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required'
        ]);  
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'otp' => 'required',
            
        ], [            
            'user_id.required' => 'User id is required.',
            'otp.required' => 'OTP is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        /* Validation Logic */
        $userOtp   = UserOtp::where('user_id', $request->user_id)->where('otp', $request->otp)->first();
  
        $now = now();
        if (!$userOtp) {
            return response()->json(['code' => 422, 'message' => 'Your OTP is not correct.']);
        }else if($userOtp && $now->isAfter($userOtp->expire_at)){
            return response()->json(['code' => 422, 'message' => 'Your OTP has been expired.']);
        }
    
        $user = User::whereId($request->user_id)->first();
  
        if($user){
              
            $userOtp->update([
                'expire_at' => now()
            ]);
  
            Auth::login($user);
            $token = auth()->user()->createToken('Token')->accessToken;
            return response()->json(['code' => 200, 'message' => 'Login Successfully!','token'=> $token]);
        }
        return response()->json(['code' => 422, 'message' => 'Unauthoriesd access!']);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['code' => 200,'message' => 'User successfully signed out']);
    }

    public function verifyMobileNo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|between:4,100',
        ], [
            'username.required' => 'Username is required.',
            'username.min' => 'Username length should be at least 4 characters.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }
        $username = User::where('username', '=', $request->input('username'))->first();
        if ($username) {
            return response()->json(['code' => 422, 'message' => 'This username is unavailable!']);
        } else {
            return response()->json(['code' => 200, 'message' => 'Username is available!']);
        }
    }

public function userInfo(Request $request){
        
    $user = auth()->user();
   return response()->json(['user'=> $user],200);
}



public function generateMpin(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'device_id' => 'required',
            'mpin' => 'required|string|same:confirm_mpin|min:4|max:6',    
        ], [            
            'user_id.required' => 'User id is required.',
            'device_id.required' => 'Device id is required.',
            'mpin' => 'Mpin is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();
  
        if($user){
            
            $resetmpin   = UserMpin::where('device_id', $request->device_id)->where('user_id', $user->id)->first();
            if($resetmpin){
            $reset = UserMpin::find($resetmpin->id);
            $reset->mpin =        $request->mpin;
            $reset->user_id =     $user->id;
            $reset->device_id =   $request->device_id;
            $reset->device_type = $request->device_type;
            $reset->device_name = $request->device_name;
            $reset->update();
    } else{
        $mpin = UserMpin::create([
            'user_id' => $user->id,
            'mpin' => $request->mpin,
            'device_id' => $request->device_id,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name
        ]);
    }
}
    $token = $user->createToken('Token')->accessToken;
    if (!$token) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Mpin Created successfully!','token'=> $token,'userData'=>$user]);
 }
}

public function resetMpin(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'device_id' => 'required',
            'mpin' => 'required|string|same:confirm_mpin|min:4|max:6',    
        ], [            
            'user_id.required' => 'User id is required.',
            'device_id.required' => 'Device id is required.',
            'mpin' => 'Mpin is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();
  
        if($user){
            
            $resetmpin   = UserMpin::where('device_id', $request->device_id)->where('user_id', $user->id)->first();
            if($resetmpin){
            $reset = UserMpin::find($resetmpin->id);
            $reset->mpin =        $request->mpin;
            $reset->user_id =     $user->id;
            $reset->device_id =   $request->device_id;
            $reset->device_type = $request->device_type;
            $reset->device_name = $request->device_name;
            $reset->update();
    } else{
        $mpin = UserMpin::create([
            'user_id' => $user->id,
            'mpin' => $request->mpin,
            'device_id' => $request->device_id,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name
        ]);
    }
}
    $token = $user->createToken('Token')->accessToken;
    if (!$token) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Mpin Reset successfully!','token'=> $token,'userData'=>$user]);
 }
}

  /**
     * Write code on Method
     *
     * @return response()
     */
    public function loginWithMpin(Request $request)
    {
        /* Validation */
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mpin' => 'required'
        ]);  
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'mpin' => 'required',
            
        ], [            
            'user_id.required' => 'User id is required.',
            'mpin.required' => 'MPIN is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        /* Validation Logic */
        $usermpin   = UserMpin::where('user_id', $request->user_id)->where('device_id', $request->device_id)->where('mpin', $request->mpin)->first();
  
        $now = now();
        if (!$usermpin) {
            return response()->json(['code' => 422, 'message' => 'Your Mpin is not correct.']);
        }
    
        $user = User::whereId($request->user_id)->first();
        if($user){
            Auth::login($user);
            $token = auth()->user()->createToken('Token')->accessToken;
            return response()->json(['code' => 200, 'message' => 'Login Successfully!','token'=> $token,'userData'=>$user]);
        }
        return response()->json(['code' => 422, 'message' => 'Unauthoriesd access!']);
    }

    public function sendSMS($mobile,$otp)
    {

        $request_body = array(
            "sender"=>"KRAFTZ",
            "route" =>"4",
            "country" =>"91",
            "sms" => [ array(
                "message"=> "KRAFTPAPER Your mobile verification code is : ".$otp,
                "to"=> [$mobile] )]
        );

        // print_r($request_body);
        // die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.msg91.com/api/v2/sendsms?DLT_TE_ID=1307161717926311553');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($request_body,true));

        $headers = array();
        $headers[] = 'Authkey: 252256AwyPQCtcYQbR5c17926c';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Cookie: PHPSESSID=m5mfte94tvftolkehu34nq0vd2';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        
        if (curl_errno($ch)) 
        {
            return false;
        }
        curl_close($ch);

        $response = json_decode($result, true);
        if($response['type']=="success")
        {
            return true;
        }
        else
        {
            return false;
        }


    }
        
}