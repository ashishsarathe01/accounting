<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserOtp;
use App\Models\Companies;
use App\Models\PrivilegesModuleMapping;
use App\Helpers\CommonHelper;
use Hash;
use Session;
class AuthController extends Controller{
   public function index(){
      if(Session::get('user_id') && !empty(Session::get('user_id'))){
         return redirect()->intended('dashboard');
      }else{
         return view('auth.passwordLogin');
      }
      
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
        
        $length_mobile = strlen($request->mobile_no);
        $mobileNumber = $request->mobile_no;
        $template = "customer_otp_verify";
        $mobile = $request->mobile_no;
        $var1 = $otp;
        $req = '{
                "countryCode": "+91",
                "phoneNumber": '.$mobile.',
                "callbackData": "some text here",
                "type": "Template",
                "template": {
                    "name": "'.$template.'",
                    "languageCode": "en",
                    "bodyValues": ["'
                    .$var1.'"
                    ]
                }
        }';
        CommonHelper::sendWhatsappMessage($req);

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

   public function passwordLoginCheck(Request $request){
      $validator = Validator::make($request->all(), [
         'email' => 'required|string',
         'password' => 'required|string',
      ],[
         'email.required' => 'Email is required.',
         'password.required' => 'Password is required.',
      ]);
      if($validator->fails()) {
         return redirect()->route('password.login')->withErrors($validator)->withInput();
         return response()->json($validator->errors(), 422);
      }
      $credentials = $request->only('email', 'password');
      if(Auth::attempt($credentials)){
         $user_data  = auth()->user();  
         if($user_data->status=='0' || $user_data->delete_status=='1'){
            return redirect("password-login")->withError('Login details are not valid!');
         }
         $from_date = "";$to_date = "";
         if($user_data->type=="OWNER"){
            $company = Companies::where('user_id', $user_data->id)->where('default_company', '1')->first();
            if($company){
               $y = explode("-",$company->default_fy);
               $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
               $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
            }
         }else if($user_data->type=="EMPLOYEE"){
            $company = Companies::where('id', $user_data->company_id)->first();
            if($company){
               $y = explode("-",$company->default_fy);
               $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
               $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
            }
         }
         Session::put([
            'user_id' => $user_data->id,
            'user_name' => $user_data->name,
            'user_email' => $user_data->email,
            'user_mobile_no' => $user_data->mobile_no,
            'business_type' => isset($company) ? $company->business_type : '',
            'default_fy' => isset($company) ? $company->default_fy : '',
            'from_date' => $from_date,
            'user_type' => $user_data->type,
            'to_date' => $to_date,
            'user_company_id' => isset($company) ? $company->id : '',
         ]);
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
        if(!Session::get('registration_otp_verify') || Session::get('registration_otp_verify')=='' || Session::get('registration_otp_verify')==0){
            return redirect()->route('register.user')->withErrors("Please Verify Mobile No.");
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'ip_address' => $request->ip(),
            'password' => \Hash::make($request->password)
        ]);

        $token = $user->createToken('Token')->accessToken;
        if (!$token) {
            return redirect("password-login")->withSuccess('You have signed-in');
        } else {
            Session::put('registration_otp','');
            Session::put('registration_otp_verify',0);
            //send message to admin
            $template = "ma_admin_registration_message";
            $mobile = "9255104995";
            $var1 = $request->name;
            $var2 = $request->email;
            $var3 = $request->mobile_no;
            $var4 = date('d-m-Y H:i');
            $req = '{
                    "countryCode": "+91",
                    "phoneNumber": '.$mobile.',
                    "callbackData": "some text here",
                    "type": "Template",
                    "template": {
                        "name": "'.$template.'",
                        "languageCode": "en_GB",
                        "bodyValues": ["'
                        .$var1.'","'
                        .$var2.'","'
                        .$var3.'","'
                        .$var4.'"
                        ]
                    }
            }';
            CommonHelper::sendWhatsappMessage($req);

            //send message to user
            $template = "ma_user_registration_message";
            $mobile = $request->mobile_no;
            $var1 = $request->name;
            $req = '{
                    "countryCode": "+91",
                    "phoneNumber": '.$mobile.',
                    "callbackData": "some text here",
                    "type": "Template",
                    "template": {
                        "name": "'.$template.'",
                        "languageCode": "en_GB",
                        "bodyValues": ["'
                        .$var1.'"
                        ]
                    }
            }';
            CommonHelper::sendWhatsappMessage($req);
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
            return view('auth.otpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobileNumber, 'errorMessage' => 'Some thing went wrong, please try after some time!']);
        }
        /* Validation Logic */
        $otp = $request->otp1 . '' . $request->otp2 . '' . $request->otp3 . '' . $request->otp4;
        $userOtp   = UserOtp::where('user_id', $request->user_id)->where('otp', $otp)->first();

        $now = now();
        if (!$userOtp) {
            return view('auth.otpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobileNumber, 'errorMessage' => 'Your OTP is not correct.']);
            
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return view('auth.otpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobileNumber, 'errorMessage' => 'Your OTP has been expired.']);
        }

        $user = User::whereId($request->user_id)->first();

        if ($user) {
            $userOtp->update([
                'expire_at' => now()
            ]);
            Auth::login($user);
            $user_data  = auth()->user();  
            if($user_data->status=='0' || $user_data->delete_status=='1'){
                return redirect("password-login")->withError('Login details are not valid!');
            }
            $from_date = "";$to_date = "";
            if($user_data->type=="OWNER"){
                $company = Companies::where('user_id', $user_data->id)->where('default_company', '1')->first();
                if($company){
                $y = explode("-",$company->default_fy);
                $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
                $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
                }
            }else if($user_data->type=="EMPLOYEE"){
                $company = Companies::where('id', $user_data->company_id)->first();
                if($company){
                $y = explode("-",$company->default_fy);
                $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
                $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
                }
            }
            Session::put([
                'user_id' => $user_data->id,
                'user_name' => $user_data->name,
                'user_email' => $user_data->email,
                'user_mobile_no' => $user_data->mobile_no,
                'business_type' => isset($company) ? $company->business_type : '',
                'default_fy' => isset($company) ? $company->default_fy : '',
                'from_date' => $from_date,
                'user_type' => $user_data->type,
                'to_date' => $to_date,
                'user_company_id' => isset($company) ? $company->id : '',
            ]);
            return redirect()->intended('dashboard')->withSuccess('Signed in Successfully!');
            
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
         if(Session::get('user_type')=="OWNER"){
            $user_id = Session::get('user_id');
            $company_list = Companies::where('user_id', $user_id)->get();
         }else if(Session::get('user_type')=="EMPLOYEE"){
            $assign_company = PrivilegesModuleMapping::where('employee_id',Session('user_id'))->pluck('company_id')->toArray();
            $user = Companies::select('user_id')->where('id', Session::get('user_company_id'))->first();
            $company_list = Companies::where('user_id', $user->user_id)->whereIn('id',$assign_company)->get();
         }
            
            
            return view('dashboard/dashboard')->with('company_list', $company_list);
        }
        return redirect("password-login")->withSuccess('Please Login Again!');
    }
    public function changeCompany(Request $request){
      if(Session::get('user_type')=="OWNER"){
         Companies::where('user_id',Session::get('user_id'))->update(['default_company'=>'0']);
      }else if(Session::get('user_type')=="EMPLOYEE"){
         $user = Companies::select('user_id')->where('id', Session::get('user_company_id'))->first();
         Companies::where('user_id',$user->user_id)->update(['default_company'=>'0']);
      }
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
        $template = "customer_otp_verify";
        $mobile = $request->mobile_no;
        $var1 = $otp;
        $req = '{
                "countryCode": "+91",
                "phoneNumber": '.$mobile.',
                "callbackData": "some text here",
                "type": "Template",
                "template": {
                    "name": "'.$template.'",
                    "languageCode": "en",
                    "bodyValues": ["'
                    .$var1.'"
                    ]
                }
        }';
        CommonHelper::sendWhatsappMessage($req);
        return view('auth.forgotOtpVerification')->with(['user_id' => $userOtp->user_id, 'mobile_no' => $mobile, 'successMessage' => 'OTP has been sent on Your Mobile Number for change password!']);
        
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
            return view('auth.forgotOtpVerification')->with(['user_id' => $request->user_id, 'mobile_no' => $request->mobile_no, 'errorMessage' => 'Your OTP is not correct!']);
            
            //return response()->json(['code' => 422, 'message' => 'Your OTP is not correct.']);
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {
            return view('auth.forgotOtpVerification')->with(['user_id' => $request->user_id, 'mobile_no' => $request->mobile_no, 'errorMessage' => 'Your OTP has been expired.!']);
            
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
    public function sendOtp(Request $request){
        $request->validate([
            'mobile_no' => 'required|string|min:10|max:10|unique:users',
        ]);
        $otp =  rand(1234, 9999);
        $template = "customer_otp_verify";
        $mobile = $request->mobile_no;
        $var1 = $otp;
        $req = '{
                "countryCode": "+91",
                "phoneNumber": '.$mobile.',
                "callbackData": "some text here",
                "type": "Template",
                "template": {
                    "name": "'.$template.'",
                    "languageCode": "en",
                    "bodyValues": ["'
                    .$var1.'"
                    ]
                }
        }';
        CommonHelper::sendWhatsappMessage($req);
        Session::put('registration_otp', $otp);
        Session::put('registration_otp_verify',0);
        $response = array(
            'status' => 1,
            'message' => 'OTP sent successfully!'
        );
        return json_encode($response);
    }
    public function verifyOtp(Request $request){        
        if($request->otp == Session::get('registration_otp')){
            Session::put('registration_otp_verify',1);
            $response = array(
                'status' => 1,
                'message' => 'OTP verified successfully!'
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'OTP not matched!'
            );
        }
        return json_encode($response);
    }
    public function changeOtpVerifyStatus(Request $request){
        Session::put('registration_otp_verify',1);
    }
    public function changePasswordView(Request $request){
        return view('change_password');
    }
    public function changePasswordUpdate(Request $request)
    {        
        $validated = $request->validate([
            'password' => 'required',
        ]); 
        User::where('id',Session::get('user_id'))->update(['password' => \Hash::make($request->password)]);
        return redirect('change-password-view')->withSuccess('Password reset successfully!');
        
        
    }
    
}
