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
use App\Models\ConfigurationSetting;
use App\Helpers\CommonHelper;
use Hash;
use Session;
use DB;
use Carbon\Carbon;
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
                $company = Companies::where('user_id', $user_data->id)
                                    ->where('delete', '0')
                                    //->where('default_company', '1')
                                    ->first();
                if($company){
                    $y = explode("-",$company->default_fy);
                    $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
                    $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
                }
            }else if($user_data->type=="EMPLOYEE" || $user_data->type=="OTHER" || $user_data->type=="ACCOUNTANT" || $user_data->type=="CA"){
                $company = Companies::where('id', $user_data->company_id)->where('delete', '0')->first();
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
            'mobile_no' => 'required|string|min:10|max:12',

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
            }else if($user_data->type=="EMPLOYEE" || $user_data->type=="OTHER" || $user_data->type=="ACCOUNTANT" || $user_data->type=="CA"){
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
            $company_list = collect();
            
            if(Session::get('user_type')=="OWNER"){ 
              
                $user_id = Session::get('user_id');
                if(Session::get('admin_id') && Session::get('admin_id')!=''){ 
                    
                    $assign_company = DB::table('assign_companies')
                                ->where('admin_users_id', Session::get('admin_id'))
                                ->where('merchant_id', $user_id)
                                ->pluck('comp_id')
                                ->toArray();
                    $company_list = Companies::where('user_id', $user_id)->where('delete','0')->whereIn('id',$assign_company)->get();
                    
                }else{
                      
                    $login_user_mobile = User::find( $user_id);
                    $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                                    ->where('type','OWNER')
                                    ->where('status','1')
                                    ->where('delete_status','0')
                                    ->pluck('id');
                    $login_user_emp_comp = User::where('mobile_no',$login_user_mobile->mobile_no)
                                    ->where('type','!=','OWNER')
                                    ->where('status','1')
                                    ->where('delete_status','0')
                                    ->pluck('company_id');                                    
                    //$company_list = Companies::where('user_id', $user_id)->get();
                    $company_list = Companies::whereIn('user_id', $login_user_id)->where('delete','0')->get();
                    $company_list_emp = Companies::whereIn('id', $login_user_emp_comp)->where('delete','0')->get();
                    $company_list = $company_list
                                    ->merge($company_list_emp)
                                    ->unique()
                                    ->values();
                    // echo "<pre>";
                    // print_r($company_list->toArray());

                }
         }else if(Session::get('user_type')=="EMPLOYEE" || Session::get('user_type')=="OTHER" || Session::get('user_type')=="ACCOUNTANT" || Session::get('user_type')=="CA"){
             //die;
            $login_user_mobile = User::find(Session('user_id'));
            $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                            ->where('status','1')
                            ->where('delete_status','0')
                            ->pluck('id');
            $login_user_id_owner = User::where('mobile_no',$login_user_mobile->mobile_no)
                            ->where('type','OWNER')
                            ->where('status','1')
                            ->where('delete_status','0')
                            ->pluck('id');    
            $company_list_owner = Companies::whereIn('user_id', $login_user_id_owner)->where('delete','0')->get();    
            $assign_company = PrivilegesModuleMapping::whereIn('employee_id',$login_user_id)
                                                        ->pluck('company_id')
                                                        ->toArray();
                                                        
            $user = Companies::select('user_id')
                                ->where('id', Session::get('user_company_id'))
                                ->where('delete','0')
                                ->first();
            $company_list = Companies::whereIn('id',$assign_company)->where('delete','0')->get();
            $company_list = $company_list
                                    ->merge($company_list_owner)
                                    ->unique()
                                    ->values();
            
         }
        
            $companyId = Session::get('user_company_id');

            $today = \Carbon\Carbon::today()->toDateString();

            /* ============================================================
            ======================= SALES ===============================
            ============================================================ */

            $salesConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'sales')
                ->first();

            $salesConfigData = $salesConfig ? $salesConfig->config_json : [];
            $showSalesCard = !empty(array_filter($salesConfigData));
            $salesShowAdd  = $salesConfigData['show_add']  ?? false;
            $salesShowView = $salesConfigData['show_view'] ?? false;

            /* ================= SALES ================= */

            $baseSalesQuery = DB::table('sales')
                ->where('company_id', (string) $companyId)
                ->where('date', $today)
                ->where('status', '1')
                ->where('delete', '0');

            $totalSalesCount = (clone $baseSalesQuery)->count();

            $totalSalesAmount = (clone $baseSalesQuery)->sum('total');

            $salesWithGstAmount = (clone $baseSalesQuery)
                ->whereNotNull('taxable_amt')
                ->whereColumn('total', '>', 'taxable_amt')
                ->sum('total');

            $salesWithoutGstAmount = (clone $baseSalesQuery)
                ->whereNotNull('taxable_amt')
                ->sum('taxable_amt');

            $totalSalesQty = DB::table('sale_descriptions')
                ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
                ->where('sales.company_id', (string) $companyId)
                ->where('sales.date', $today)
                ->where('sales.status', '1')
                ->where('sales.delete', '0')
                ->where('sale_descriptions.status', '1')
                ->where('sale_descriptions.delete', '0')
                ->sum('sale_descriptions.qty');


            $salesDashboardData = [
                'show_total_sales_count'   => $salesConfigData['total_sales_count'] ?? false,
                'show_total_sales_qty'     => $salesConfigData['total_sales_qty'] ?? false,
                'show_total_sales_amount'  => $salesConfigData['total_sales_amount'] ?? false,
                'show_sales_with_gst'      => $salesConfigData['sales_with_gst_amount'] ?? false,
                'show_sales_without_gst'   => $salesConfigData['sales_without_gst_amount'] ?? false,

                'total_sales_count'        => $totalSalesCount,
                'total_sales_qty'          => $totalSalesQty,
                'total_sales_amount'       => $totalSalesAmount,
                'sales_with_gst_amount'    => $salesWithGstAmount,
                'sales_without_gst_amount' => $salesWithoutGstAmount,
            ];

            /* ============================================================
            ======================= PURCHASE ============================
            ============================================================ */

            $purchaseConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'purchase')
                ->first();

            $purchaseConfigData = $purchaseConfig ? $purchaseConfig->config_json : [];
            $showPurchaseCard = !empty(array_filter($purchaseConfigData));
            $purchaseShowAdd  = $purchaseConfigData['show_add']  ?? false;
            $purchaseShowView = $purchaseConfigData['show_view'] ?? false;

            /* ================= PURCHASE ================= */

            $basePurchaseQuery = DB::table('purchases')
                ->where('company_id', (string) $companyId)
                ->where('date', $today)
                ->where('status', '1')
                ->where('delete', '0');

            $totalPurchaseCount = (clone $basePurchaseQuery)->count();

            $totalPurchaseAmount = (clone $basePurchaseQuery)->sum('total');

            $purchaseWithGstAmount = (clone $basePurchaseQuery)
                ->whereNotNull('taxable_amt')
                ->whereColumn('total', '>', 'taxable_amt')
                ->sum('total');

            $purchaseWithoutGstAmount = (clone $basePurchaseQuery)
                ->whereNotNull('taxable_amt')
                ->sum('taxable_amt');

            $totalPurchaseQty = DB::table('purchase_descriptions')
                ->join('purchases', 'purchases.id', '=', 'purchase_descriptions.purchase_id')
                ->where('purchases.company_id', (string) $companyId)
                ->where('purchases.date', $today)
                ->where('purchases.status', '1')
                ->where('purchases.delete', '0')
                ->where('purchase_descriptions.status', '1')
                ->where('purchase_descriptions.delete', '0')
                ->sum('purchase_descriptions.qty');


            $purchaseDashboardData = [
                'show_total_purchase_count'   => $purchaseConfigData['total_purchase_count'] ?? false,
                'show_total_purchase_qty'     => $purchaseConfigData['total_purchase_qty'] ?? false,
                'show_total_purchase_amount'  => $purchaseConfigData['total_purchase_amount'] ?? false,
                'show_purchase_with_gst'      => $purchaseConfigData['purchase_with_gst_amount'] ?? false,
                'show_purchase_without_gst'   => $purchaseConfigData['purchase_without_gst_amount'] ?? false,

                'total_purchase_count'        => $totalPurchaseCount,
                'total_purchase_qty'          => $totalPurchaseQty,
                'total_purchase_amount'       => $totalPurchaseAmount,
                'purchase_with_gst_amount'    => $purchaseWithGstAmount,
                'purchase_without_gst_amount' => $purchaseWithoutGstAmount,
            ];

            /* ============================================================
            ==================== SALE RETURN ===========================
            ============================================================ */

            $saleReturnConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'sale_return')
                ->first();

            $saleReturnConfigData = $saleReturnConfig ? $saleReturnConfig->config_json : [];
            $showSaleReturnCard = !empty(array_filter($saleReturnConfigData));
            $saleReturnShowAdd  = $saleReturnConfigData['show_add']  ?? false;
            $saleReturnShowView = $saleReturnConfigData['show_view'] ?? false;

            $baseSaleReturnQuery = DB::table('sales_returns')
                ->where('company_id', (string) $companyId)
                ->where('date', $today)
                ->where('status', '1')
                ->where('delete', '0');

            $totalSaleReturnCount = (clone $baseSaleReturnQuery)->count();

            $totalSaleReturnAmount = (clone $baseSaleReturnQuery)->sum('total');

            $saleReturnWithGstAmount = (clone $baseSaleReturnQuery)
                ->whereNotNull('taxable_amt')
                ->whereColumn('total', '>', 'taxable_amt')
                ->sum('total');

            $saleReturnWithoutGstAmount = (clone $baseSaleReturnQuery)
                ->where(function ($q) {
                    $q->whereNull('taxable_amt')
                    ->orWhereColumn('total', '=', 'taxable_amt');
                })
                ->sum('total');

            $totalSaleReturnQty = DB::table('sale_return_descriptions')
                ->join('sales_returns', 'sales_returns.id', '=', 'sale_return_descriptions.sale_return_id')
                ->where('sales_returns.company_id', (string) $companyId)
                ->where('sales_returns.date', $today)
                ->where('sales_returns.status', '1')
                ->where('sales_returns.delete', '0')
                ->where('sale_return_descriptions.status', '1')
                ->where('sale_return_descriptions.delete', '0')
                ->sum('sale_return_descriptions.qty');

            $saleReturnDashboardData = [
                'show_total_sale_return_count'   => $saleReturnConfigData['total_sale_return_count'] ?? false,
                'show_total_sale_return_qty'     => $saleReturnConfigData['total_sale_return_qty'] ?? false,
                'show_total_sale_return_amount'  => $saleReturnConfigData['total_sale_return_amount'] ?? false,
                'show_sale_return_with_gst'      => $saleReturnConfigData['sale_return_with_gst_amount'] ?? false,
                'show_sale_return_without_gst'   => $saleReturnConfigData['sale_return_without_gst_amount'] ?? false,

                'total_sale_return_count'        => $totalSaleReturnCount,
                'total_sale_return_qty'          => $totalSaleReturnQty,
                'total_sale_return_amount'       => $totalSaleReturnAmount,
                'sale_return_with_gst_amount'    => $saleReturnWithGstAmount,
                'sale_return_without_gst_amount' => $saleReturnWithoutGstAmount,
            ];

            /* ============================================================
            =================== PURCHASE RETURN ========================
            ============================================================ */

            $purchaseReturnConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'purchase_return')
                ->first();

            $purchaseReturnConfigData = $purchaseReturnConfig ? $purchaseReturnConfig->config_json : [];
            $showPurchaseReturnCard = !empty(array_filter($purchaseReturnConfigData));
            $purchaseReturnShowAdd  = $purchaseReturnConfigData['show_add']  ?? false;
            $purchaseReturnShowView = $purchaseReturnConfigData['show_view'] ?? false;

            $basePurchaseReturnQuery = DB::table('purchase_returns')
                ->where('company_id', (string) $companyId)
                ->where('date', $today)
                ->where('status', '1')
                ->where('delete', '0');

            $totalPurchaseReturnCount = (clone $basePurchaseReturnQuery)->count();

            $totalPurchaseReturnAmount = (clone $basePurchaseReturnQuery)->sum('total');

            $purchaseReturnWithGstAmount = (clone $basePurchaseReturnQuery)
                ->whereNotNull('taxable_amt')
                ->whereColumn('total', '>', 'taxable_amt')
                ->sum('total');

            $purchaseReturnWithoutGstAmount = (clone $basePurchaseReturnQuery)
                ->where(function ($q) {
                    $q->whereNull('taxable_amt')
                    ->orWhereColumn('total', '=', 'taxable_amt');
                })
                ->sum('total');

            $totalPurchaseReturnQty = DB::table('purchase_return_descriptions')
                ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_descriptions.purchase_return_id')
                ->where('purchase_returns.company_id', (string) $companyId)
                ->where('purchase_returns.date', $today)
                ->where('purchase_returns.status', '1')
                ->where('purchase_returns.delete', '0')
                ->where('purchase_return_descriptions.status', '1')
                ->where('purchase_return_descriptions.delete', '0')
                ->sum('purchase_return_descriptions.qty');

            $purchaseReturnDashboardData = [
                'show_total_purchase_return_count'   => $purchaseReturnConfigData['total_purchase_return_count'] ?? false,
                'show_total_purchase_return_qty'     => $purchaseReturnConfigData['total_purchase_return_qty'] ?? false,
                'show_total_purchase_return_amount'  => $purchaseReturnConfigData['total_purchase_return_amount'] ?? false,
                'show_purchase_return_with_gst'      => $purchaseReturnConfigData['purchase_return_with_gst_amount'] ?? false,
                'show_purchase_return_without_gst'   => $purchaseReturnConfigData['purchase_return_without_gst_amount'] ?? false,

                'total_purchase_return_count'        => $totalPurchaseReturnCount,
                'total_purchase_return_qty'          => $totalPurchaseReturnQty,
                'total_purchase_return_amount'       => $totalPurchaseReturnAmount,
                'purchase_return_with_gst_amount'    => $purchaseReturnWithGstAmount,
                'purchase_return_without_gst_amount' => $purchaseReturnWithoutGstAmount,
            ];

            /* ============================================================
            =================== PAYMENT ========================
            ============================================================ */
            $paymentConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'payment')
                ->first();

            $paymentConfigData = $paymentConfig ? $paymentConfig->config_json : [];
            $showPaymentCard = !empty(array_filter($paymentConfigData));
            $paymentShowAdd  = $paymentConfigData['show_add']  ?? false;
            $paymentShowView = $paymentConfigData['show_view'] ?? false;

            $basePaymentQuery = DB::table('payment_details')
                ->join('payments', 'payments.id', '=', 'payment_details.payment_id')
                ->where('payment_details.company_id', (string)$companyId)
                ->where('payments.date', $today)
                ->where('payments.status', '1')
                ->where('payments.delete', '0')
                ->where('payment_details.status', '1')
                ->where('payment_details.delete', '0');

            $totalPaidCount = (clone $basePaymentQuery)
                ->where('payment_details.type', 'Debit')
                ->count();

            $totalReceivedCount = (clone $basePaymentQuery)
                ->where('payment_details.type', 'Credit')
                ->count();

            $totalPaidAmount = (clone $basePaymentQuery)
                ->where('payment_details.type', 'Debit')
                ->sum('payment_details.debit');

            $totalReceivedAmount = (clone $basePaymentQuery)
                ->where('payment_details.type', 'Credit')
                ->sum('payment_details.credit');

            $paymentDashboardData = [
                'show_total_paid_count'      => $paymentConfigData['total_paid_count'] ?? false,
                'show_total_received_count'  => $paymentConfigData['total_received_count'] ?? false,
                'show_total_paid_amount'     => $paymentConfigData['total_paid_amount'] ?? false,
                'show_total_received_amount' => $paymentConfigData['total_received_amount'] ?? false,

                'total_paid_count'           => $totalPaidCount,
                'total_received_count'       => $totalReceivedCount,
                'total_paid_amount'          => $totalPaidAmount,
                'total_received_amount'      => $totalReceivedAmount,
            ];

            /* ============================================================
            =================== RECEIPT ========================
            ============================================================ */

            $receiptConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'receipt')
                ->first();

            $receiptConfigData = $receiptConfig ? $receiptConfig->config_json : [];
            $showReceiptCard = !empty(array_filter($receiptConfigData));
            $receiptShowAdd  = $receiptConfigData['show_add']  ?? false;
            $receiptShowView = $receiptConfigData['show_view'] ?? false;

            $baseReceiptQuery = DB::table('receipt_details')
                ->join('receipts', 'receipts.id', '=', 'receipt_details.receipt_id')
                ->where('receipt_details.company_id', (string)$companyId)
                ->where('receipts.date', $today)
                ->where('receipts.status', '1')
                ->where('receipts.delete', '0')
                ->where('receipt_details.status', '1')
                ->where('receipt_details.delete', '0');

            $totalReceivedCount = (clone $baseReceiptQuery)
                ->where('receipt_details.type', 'Credit')
                ->count();

            $totalPaidCount = (clone $baseReceiptQuery)
                ->where('receipt_details.type', 'Debit')
                ->count();

            $totalReceivedAmount = (clone $baseReceiptQuery)
                ->where('receipt_details.type', 'Credit')
                ->sum('receipt_details.credit');

            $totalPaidAmount = (clone $baseReceiptQuery)
                ->where('receipt_details.type', 'Debit')
                ->sum('receipt_details.debit');

            $receiptDashboardData = [
                'show_total_received_count'  => $receiptConfigData['total_received_count'] ?? false,
                'show_total_paid_count'      => $receiptConfigData['total_paid_count'] ?? false,
                'show_total_received_amount' => $receiptConfigData['total_received_amount'] ?? false,
                'show_total_paid_amount'     => $receiptConfigData['total_paid_amount'] ?? false,

                'total_received_count'       => $totalReceivedCount,
                'total_paid_count'           => $totalPaidCount,
                'total_received_amount'      => $totalReceivedAmount,
                'total_paid_amount'          => $totalPaidAmount,
            ];

            $journalConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'journal')
                ->first();

            $journalActions = $journalConfig->config_json ?? [];
            $journalShowAdd  = $journalActions['show_add']  ?? false;
            $journalShowView = $journalActions['show_view'] ?? false;
            $showJournalCard = ($journalShowAdd || $journalShowView);

            $contraConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'contra')
                ->first();

            $contraActions = $contraConfig->config_json ?? [];
            $contraShowAdd  = $contraActions['show_add']  ?? false;
            $contraShowView = $contraActions['show_view'] ?? false;
            $showContraCard = ($contraShowAdd || $contraShowView);

            $stockJournalConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'stock_journal')
                ->first();

            $stockJournalActions = $stockJournalConfig->config_json ?? [];
            $stockJournalShowAdd  = $stockJournalActions['show_add']  ?? false;
            $stockJournalShowView = $stockJournalActions['show_view'] ?? false;
            $showStockJournalCard = ($stockJournalShowAdd || $stockJournalShowView);

            $stockTransferConfig = ConfigurationSetting::where('company_id', $companyId)
                ->where('module', 'stock_transfer')
                ->first();

            $stockTransferActions = $stockTransferConfig->config_json ?? [];
            $stockTransferShowAdd  = $stockTransferActions['show_add']  ?? false;
            $stockTransferShowView = $stockTransferActions['show_view'] ?? false;
            $showStockTransferCard = ($stockTransferShowAdd || $stockTransferShowView);
            return view('dashboard/dashboard')->with('company_list', $company_list)->with('salesDashboardData', $salesDashboardData)->with('purchaseDashboardData', $purchaseDashboardData)->with('saleReturnDashboardData', $saleReturnDashboardData)->with('purchaseReturnDashboardData', $purchaseReturnDashboardData)->with('paymentDashboardData', $paymentDashboardData)->with('receiptDashboardData', $receiptDashboardData)->with('showSalesCard', $showSalesCard)->with('showPurchaseCard', $showPurchaseCard)->with('showSaleReturnCard', $showSaleReturnCard)->with('showPurchaseReturnCard', $showPurchaseReturnCard)->with('showPaymentCard', $showPaymentCard)->with('showReceiptCard', $showReceiptCard)->with('salesShowAdd', $salesShowAdd)->with('salesShowView', $salesShowView)->with('purchaseShowAdd', $purchaseShowAdd)->with('purchaseShowView', $purchaseShowView)->with('saleReturnShowAdd', $saleReturnShowAdd)->with('saleReturnShowView', $saleReturnShowView)->with('purchaseReturnShowAdd', $purchaseReturnShowAdd)->with('purchaseReturnShowView', $purchaseReturnShowView)->with('paymentShowAdd', $paymentShowAdd)->with('paymentShowView', $paymentShowView)->with('receiptShowAdd', $receiptShowAdd)->with('receiptShowView', $receiptShowView)->with('journalShowAdd', $journalShowAdd)->with('journalShowView', $journalShowView)->with('contraShowAdd', $contraShowAdd)->with('contraShowView', $contraShowView)->with('stockJournalShowAdd', $stockJournalShowAdd)->with('stockJournalShowView', $stockJournalShowView)->with('stockTransferShowAdd', $stockTransferShowAdd)->with('stockTransferShowView', $stockTransferShowView)->with('showJournalCard', $showJournalCard)->with('showContraCard', $showContraCard)->with('showStockJournalCard', $showStockJournalCard)->with('showStockTransferCard', $showStockTransferCard);
        }
        return redirect("password-login")->withSuccess('Please Login Again!');
    }
    public function changeCompany(Request $request){
        if(Session::get('user_type')=="OWNER"){
            Companies::where('user_id',Session::get('user_id'))->update(['default_company'=>'0']);
        }else if(Session::get('user_type')=="EMPLOYEE" || Session::get('user_type')=="OTHER" || Session::get('user_type')=="ACCOUNTANT" || Session::get('user_type')=="CA"){
            $user = Companies::select('user_id')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
            Companies::where('user_id',$user->user_id)->update(['default_company'=>'0']);
        }
        $comp =  Companies::find($request->change_company);
        $comp->default_company = '1';
        $comp->update();
        Session::put('user_company_id', $request->change_company);
        $comps = \App\Models\Companies::where('id',$request->change_company)
                                                ->first();
        $comp_ids = \App\Models\Companies::where('user_id', $comps->user_id)
                                                ->pluck('id');
        $user_data = User::whereIn('company_id',$comp_ids)
            ->whereIN('type',['EMPLOYEE','OTHER','ACCOUNTANT','CA'])
            ->where('mobile_no',Session::get('user_mobile_no'))
            ->first();
        if($user_data){
            Companies::where('user_id',Session::get('user_id'))->update(['default_company'=>'1']);
            $y = explode("-",$comps->default_fy);
            $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
      $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
            Session::put([
                'user_id' => $user_data->id,
                'user_name' => $user_data->name,
                'user_email' => $user_data->email,
                'user_mobile_no' => $user_data->mobile_no,
                'user_type' => $user_data->type,
                'default_fy' => $comps->default_fy,
                'from_date'=> $from_date,
                'to_date'=> $to_date
            ]);
        }else{
            $user_data = User::where('id',$comp->user_id)
                        ->where('mobile_no',Session::get('user_mobile_no'))
                        ->first();
            if($user_data){
                Companies::where('user_id',Session::get('user_id'))->update(['default_company'=>'1']);
                 $y = explode("-",$comps->default_fy);
            $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
      $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
                Session::put([
                    'user_id' => $user_data->id,
                    'user_name' => $user_data->name,
                    'user_email' => $user_data->email,
                    'user_mobile_no' => $user_data->mobile_no,
                    'user_type' => $user_data->type,
                    'default_fy' => $comps->default_fy,
                    'from_date'=> $from_date,
                'to_date'=> $to_date
                ]);
            }           
        }
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
            'mobile_no' => 'required|string|min:10|max:10',
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
