<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserOtp;
use App\Models\Companies;
use Hash;
use Session;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.passwordLogin');
    }

    public function otpLogin()
    {
        return view('auth.otpLogin');
    }

    public function generate(Request $request)
    {
        /* Validate Data */
        $validator = Validator::make($request->all(), [

            'mobile_no' => 'required|exists:users,mobile_no',
        ], [
            'mobile_no.required' => 'Mobile no is required.',

        ]);
        if ($validator->fails()) {
            return redirect("otp-login")->withError('Mobile number does not exists!');
        }
        $otp =  rand(1234, 9999);
        /* Generate An OTP */
        $userOtp = $this->generateOtp($request->mobile_no, $otp);
        $authKey = "252256AwyPQCtcYQbR5c17926c";
        //Multiple mobiles numbers separated by comma
        $length_mobile = strlen($request->mobile_no);
        if ($length_mobile == 10) {
            $mobileNumber = '+91' . $request->mobile_no;
        } else {
            $mobileNumber = $request->mobile_no;
        }
        $senderId = "KRAFTZ";
        $message = urlencode("KRAFTPAPER Your mobile verification code is : $otp");
        $route = "4";
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route
        );

        //API URL
        $url = "https://api.msg91.com/api/v2/sendsms?DLT_TE_ID=1307161717926311553";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //get response
        $output = curl_exec($ch);
        //Print error if any
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        curl_close($ch);
        //$userOtp->sendSMS($request->mobile_no, $otp);
        return view('auth.otpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobileNumber, 'successMessage' => 'OTP has been sent on Your Mobile Number!']);
        //return redirect()->route('otp.verification', ['user_id' => $userOtp->user_id])
        //        ->with('success',  "OTP has been sent on Your Mobile Number."); 
    }

    public function generateOtp($mobile_no, $otp)
    {
        $user = User::where('mobile_no', $mobile_no)->first();
        /* User Does not Have Any Existing OTP */
        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();

        $now = now();

        if ($userOtp && $now->isBefore($userOtp->expire_at)) {
            return $userOtp;
        }
        /* Create a New OTP */
        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function passwordLoginCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'email' => 'required|string',
            'password' => 'required|string',

        ], [
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('password.login')->withErrors($validator)->withInput();
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {

            $user_data  = auth()->user();            
            $company = Companies::where('user_id', $user_data->id)->where('default_company', '1')->first();
            Session::put([
                'user_id' => $user_data->id,
                'user_name' => $user_data->name,
                'user_email' => $user_data->email,
                'user_mobile_no' => $user_data->mobile_no,
                'business_type' => $company->business_type,
                'user_company_id' => isset($company) ? $company->id : '',
            ]);
            //$allSessionData = session()->all();
            return redirect()->intended('dashboard')->withSuccess('Signed in Successfully!');
        }

        return redirect("password-login")->withError('Login details are not valid!');
    }



    public function registration()
    {
        return view('auth.registration');
    }

    public function register(Request $request)
    {

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
            return redirect()->route('register.user')->withErrors($validator)->withInput();
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'password' => \Hash::make($request->password)
        ]);

        $token = $user->createToken('Token')->accessToken;
        if (!$token) {
            return redirect("password-login")->withSuccess('You have signed-in');
        } else {
            return redirect("password-login")->withSuccess('User Created successfully!');
        }
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function loginWithOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'otp1' => 'required',

        ], [
            'user_id.required' => 'User id is required.',
            'otp1.required' => 'OTP is required.',
        ]);
        if ($validator->fails()) {
            //return response()->json($validator->errors(), 422);
            return redirect("otp-login")->withError('Some thing went wrong, please try after some time!');
        }
        /* Validation Logic */
        $otp = $request->otp1 . '' . $request->otp2 . '' . $request->otp3 . '' . $request->otp4;
        $userOtp   = UserOtp::where('user_id', $request->user_id)->where('otp', $otp)->first();

        $now = now();
        if (!$userOtp) {
            return response()->json(['code' => 422, 'message' => 'Your OTP is not correct.']);
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return response()->json(['code' => 422, 'message' => 'Your OTP has been expired.']);
        }

        $user = User::whereId($request->user_id)->first();

        if ($user) {

            $userOtp->update([
                'expire_at' => now()
            ]);

            Auth::login($user);
            $user_data  = auth()->user();
            $company = Companies::where('user_id', $user_data->id)->where('default_company', '1')->first();
            Session::put([
                'user_id' => $user_data->id,
                'user_name' => $user_data->name,
                'user_email' => $user_data->email,
                'user_mobile_no' => $user_data->mobile_no,
                'user_company_id' => isset($company) ? $company->id : '',
            ]);
            //$allSessionData = session()->all();
            return redirect()->intended('dashboard')->withSuccess('Signed in Successfully!');
            //$token = auth()->user()->createToken('Token')->accessToken;
            //return response()->json(['code' => 200, 'message' => 'Login Successfully!']);
        }
        return redirect("otp-login")->withError('Login details are not valid!');
        //return response()->json(['code' => 422, 'message' => 'Unauthoriesd access!']);
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }


    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }


    public function dashboard()
    {
        if (Auth::check()) {
            $user_id = Session::get('user_id');
            $company_list = Companies::where('user_id', $user_id)->get();
            return view('dashboard/dashboard')->with('company_list', $company_list);
        }
        return redirect("password-login")->withSuccess('Please Login Again!');
    }

    public function changeCompany(Request $request)
    {

        $comp =  Companies::find($request->change_company);
        $comp->default_company = '1';
        $comp->update();
        Session::put('user_company_id', $request->change_company);
        return redirect("dashboard")->withSuccess('Company change successfully!');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return Redirect('password-login')->withSuccess('Logout Successfully!');;
    }

    public function forgotPassword()
    {
        return view('auth.forgotPassword');
    }

    public function forgotOtp(Request $request)
    {
        /* Validate Data */
        $validator = Validator::make($request->all(), [

            'mobile_no' => 'required|exists:users,mobile_no',
        ], [
            'mobile_no.required' => 'Mobile no is required.',

        ]);
        if ($validator->fails()) {
            return redirect("otp-login")->withError('Mobile number does not exists!');
        }
        $otp =  rand(1234, 9999);
        /* Generate An OTP */
        $userOtp = $this->generateForgotOtp($request->mobile_no, $otp);
        $authKey = "252256AwyPQCtcYQbR5c17926c";
        //Multiple mobiles numbers separated by comma
        $length_mobile = strlen($request->mobile_no);
        if ($length_mobile == 10) {
            $mobileNumber = '+91' . $request->mobile_no;
        } else {
            $mobileNumber = $request->mobile_no;
        }
        $senderId = "KRAFTZ";
        $message = urlencode("KRAFTPAPER Your mobile verification code is : $otp");
        $route = "4";
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route
        );

        //API URL
        $url = "https://api.msg91.com/api/v2/sendsms?DLT_TE_ID=1307161717926311553";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //get response
        $output = curl_exec($ch);
        //Print error if any
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        curl_close($ch);
        //$userOtp->sendSMS($request->mobile_no, $otp);
        return view('auth.forgotOtpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobileNumber, 'successMessage' => 'OTP has been sent on Your Mobile Number for change password!']);
        //return redirect()->route('otp.verification', ['user_id' => $userOtp->user_id])
        //        ->with('success',  "OTP has been sent on Your Mobile Number."); 
    }

    public function generateForgotOtp($mobile_no, $otp)
    {
        $user = User::where('mobile_no', $mobile_no)->first();
        /* User Does not Have Any Existing OTP */
        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();

        $now = now();

        if ($userOtp && $now->isBefore($userOtp->expire_at)) {
            return $userOtp;
        }
        /* Create a New OTP */
        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'otp1' => 'required',

        ], [
            'user_id.required' => 'User id is required.',
            'otp1.required' => 'OTP is required.',
        ]);
        if ($validator->fails()) {
            //return response()->json($validator->errors(), 422);
            return redirect("otp-login")->withError('Some thing went wrong, please try after some time!');
        }
        /* Validation Logic */
        $otp = $request->otp1 . '' . $request->otp2 . '' . $request->otp3 . '' . $request->otp4;
        $userOtp   = UserOtp::where('user_id', $request->user_id)->where('otp', $otp)->latest()->first();

        $now = now();
        if (!$userOtp) {
            return response()->json(['code' => 422, 'message' => 'Your OTP is not correct.']);
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return response()->json(['code' => 422, 'message' => 'Your OTP has been expired.']);
        }

        $user = User::whereId($request->user_id)->first();

        if ($user) {

            $userOtp->update([
                'expire_at' => now()
            ]);
            return view('auth.changePassword')->with(['user_id' => $request->user_id]);
        }
        return redirect("otp-login")->withError('Login details are not valid!');
        //return response()->json(['code' => 422, 'message' => 'Unauthoriesd access!']);
    }

    public function submitChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'password' => 'required|string',

        ], [
            'password.required' => 'Password is required.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('password.login')->withErrors($validator)->withInput();
        }
        $user = User::whereId($request->user_id)->first();

        if ($user) {

            $user->update([
                'password' => \Hash::make($request->password)
            ]);
            return redirect("password-login")->withSuccess('Password reset successfully!');
        }
        return redirect("password-login")->withErrors('Something went wrong, please try after some time!');
    }
}
