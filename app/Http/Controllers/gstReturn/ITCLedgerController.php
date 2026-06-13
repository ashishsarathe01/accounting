<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Session;
use DB;
use App\Models\PurchaseReturn;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\Journal;
use App\Models\SaleReturnSundry;
use App\Models\PurchaseSundry;
use App\Models\PurchaseReturnSundry;
use App\Models\State;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\gstToken;
use Carbon\Carbon;
use App\Helpers\CommonHelper;

class ITCLedgerController extends Controller
{
    protected $gstCredentials;

    public function __construct()
    {
        $this->gstCredentials = json_decode(
            CommonHelper::gstApiCredentials('GST')
        );
    }

   
public function itcLedger(Request $request)
{
    try {

        $company = Companies::select('gst_config_type')
            ->where('id', Session::get('user_company_id'))
            ->first();

        // =========================
        // GST LIST
        // =========================

        if ($company->gst_config_type == "single_gst") {

            $gst = DB::table('gst_settings')
                ->select('gst_no')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_type'   => 'single_gst',
                    'delete'     => '0',
                    'status'     => '1'
                ])
                ->get();

        } else {

            $gst = DB::table('gst_settings_multiple')
                ->select('gst_no')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_type'   => 'multiple_gst',
                    'delete'     => '0',
                    'status'     => '1'
                ])
                ->get();
        }

        // =========================
        // DEFAULT VIEW
        // =========================

        if (!$request->filled('gst_no')) {

            return view(
                'gstReturn.itcLedger',
                [
                    'gst'    => $gst,
                    'ledger' => []
                ]
            );
        }

        // =========================
        // DATE VALIDATION
        // =========================

        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        if ($fromDate->gt($toDate)) {

            return redirect()
                ->route('itc.ledger')
                ->withInput()
                ->with(
                    'error',
                    'From Date should not be greater than To Date.'
                );
        }

        $monthDiff =
            ($toDate->year - $fromDate->year) * 12 +
            ($toDate->month - $fromDate->month);

        if ($monthDiff > 12) {

            return redirect()
                ->route('itc.ledger')
                ->withInput()
                ->with(
                    'error',
                    'From Date and To Date should not have more than 12 months gap.'
                );
        }

        // =========================
        // GST USERNAME
        // =========================

        if ($company->gst_config_type == "single_gst") {

            $gstData = DB::table('gst_settings')
                ->select('gst_username')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_no'     => $request->gst_no
                ])
                ->first();

        } else {

            $gstData = DB::table('gst_settings_multiple')
                ->select('gst_username')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_no'     => $request->gst_no
                ])
                ->first();
        }

        $gst_user_name = $gstData->gst_username ?? '';

        if ($gst_user_name == "") {

            return back()->with(
                'error',
                'Please Enter GST Username In GST Configuration.'
            );
        }

        $state_code = substr(trim($request->gst_no), 0, 2);

        // =========================
        // TOKEN CHECK
        // =========================

        // $gst_token = gstToken::where(
        //         'company_gstin',
        //         $request->gst_no
        //     )
        //     ->where(
        //         'company_id',
        //         Session::get('user_company_id')
        //     )
        //     ->where('status', 1)
        //     ->latest()
        //     ->first();

        // $txn = '';

        // if ($gst_token) {

        //     $token_expiry = Carbon::parse(
        //         $gst_token->created_at
        //     )->addHours(6);

        //     if (Carbon::now()->gt($token_expiry)) {

        //         $token_res = CommonHelper::gstTokenOtpRequest(
        //             $state_code,
        //             $gst_user_name,
        //             $request->gst_no
        //         );

        //         if ($token_res == 0) {

        //             return back()->with(
        //                 'error',
        //                 'Something Went Wrong In Token Generation'
        //             );
        //         }

        //         return back()->with(
        //             'error',
        //             'TOKEN-OTP'
        //         );
        //     }

        //     $txn = $gst_token->txn;

        // } else {

        //     $token_res = CommonHelper::gstTokenOtpRequest(
        //         $state_code,
        //         $gst_user_name,
        //         $request->gst_no
        //     );

        //     if ($token_res == 0) {

        //         return back()->with(
        //             'error',
        //             'Something Went Wrong In Token Generation'
        //         );
        //     }

        //     return back()->with(
        //         'error',
        //         'TOKEN-OTP'
        //     );
        // }

        // =========================
        // GST API CONFIG
        // =========================

        if (!$this->gstCredentials) {

            return back()->with(
                'error',
                'API Credentials Not Found'
            );
        }

        if ($this->gstCredentials->status != 1) {

            return back()->with(
                'error',
                'API Credentials Not Active'
            );
        }

        $base_url     = $this->gstCredentials->base_url;
        $email_id     = $this->gstCredentials->email_id;
        $client_id    = $this->gstCredentials->client_id;
        $client_secret= $this->gstCredentials->client_secret;
        $ip_address   = $this->gstCredentials->ip_address;

        // =========================
        // DATE FORMAT
        // =========================

        $from_date = $fromDate->format('d-m-Y');
        $to_date   = $toDate->format('d-m-Y');

        // =========================
        // API HIT
        // =========================

        $url = rtrim($base_url, '/') . "/ledgers/itc";

        $response = Http::withHeaders([
            'accept' => '*/*',
            'env' => 'production',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,

        ])->get($url, [

            'gstin' => $request->gst_no,
            'fr_dt'  => $from_date,
            'to_dt'  => $to_date,
            'email' => $email_id,

        ]);
        if($request->gst_no=="07CBRPG8169G1Z4"){
            // echo "<pre>";
            // dd($response->status(), $response->body());
        }
        
        if (!$response->successful()) {
            if($response->status()==500){
                $errors = json_decode($response->body());
                if($errors->details=="Request failed with status code 403"){
                    $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                    if($token_res==1){
                        return back()->with([
                            'error' => 'Please Generate Token.',
                            'merchant_gst' => $request->gst_no,
                            'from_date' => $request->from_date,
                            'to_date' => $request->to_date
                        ]);
                    }else{
                        return back()->with(
                            'error',
                            'Issue With Generate Token.'
                        );
                    }
                }else{
                    return back()->with(
                        'error',
                        $errors->details
                    );
                }
                
            }
            return back()->with(
                'error',
                'Unable To Fetch ITC Ledger'
            );
        }

        $result = $response->json();

        // =========================
        // TOKEN EXPIRED FROM API
        // =========================

        if (
            isset($result['status_cd']) &&
            $result['status_cd'] == 0
        ) {
            if(isset($result->error->message)){
                return back()->with(
                    'error',
                    $result->error->message
                );
            }else{
                return back()->with(
                'error',
                'something went wrong'
            );
            }

            
            // $message = strtolower(
            //     $result['message'] ?? ''
            // );

            // if (
            //     str_contains($message, 'token') ||
            //     str_contains($message, 'otp')
            // ) {

            //     $token_res = CommonHelper::gstTokenOtpRequest(
            //         $state_code,
            //         $gst_user_name,
            //         $request->gst_no
            //     );

            //     return back()->with(
            //         'error',
            //         'TOKEN-OTP'
            //     );
            // }
        }

        // =========================
        // UPDATE TXN
        // =========================

        if (isset($result['txn'])) {

            // gstToken::where(
            //     'company_gstin',
            //     $request->gst_no
            // )
            // ->where(
            //     'company_id',
            //     Session::get('user_company_id')
            // )
            // ->update([
            //     'txn' => $result['txn']
            // ]);
        }

        $ledger = $result['data'] ?? [];

        return view(
            'gstReturn.itcLedger',
            [
                'gst'          => $gst,
                'ledger'       => $ledger,
                'from_date'    => $request->from_date,
                'to_date'      => $request->to_date,
                'selected_gst' => $request->gst_no,
            ]
        );

    } catch (\Exception $e) {

        return back()->with(
            'error',
            $e->getMessage()
        );
    }
}

}