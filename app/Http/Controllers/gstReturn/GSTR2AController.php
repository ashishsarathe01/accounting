<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\gstToken;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\GSTR2A;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GSTR2AController extends Controller
{
    /**
     * Display the GSTR2A view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }else if($company->gst_config_type == "multiple_gst"){
            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }
        return view('gstReturn.gstr2a',['gst'=>$gst]);
    }    
    public function gstr2aDetail(Request $request){
        $month = date('mY',strtotime($request->month));
        $state_code = substr(trim($request->gstin),0,2);
        $txn = "";
        //Get GST Username
        $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->gstin
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }else if($company->gst_config_type == "multiple_gst"){            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->gstin
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }
        if($gst_user_name==""){
            $response = array(
                    'status' => false,
                    'message' => 'Please Enter GST User Name In GST Configuration.'
                );
            return json_encode($response);
        }
        $GSTR2A = GSTR2A::select('id')
                    ->where('company_gstin',$request->gstin)
                    ->where('company_id',Session::get('user_company_id'))
                    ->where('res_month',$request->month)
                    ->first();
        if(!$GSTR2A){
            //Check and generate token
            $gst_token = gstToken::select('txn','created_at')
                                ->where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('id','desc')
                                ->first();
            if($gst_token){
                $token_expiry = date('d-m-Y H:i:s',strtotime('+6 hour',strtotime($gst_token->created_at)));
                $current_time = date('d-m-Y H:i:s');
                if(strtotime($token_expiry)<strtotime($current_time)){
                    $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                    if($token_res==0){
                        $response = array(
                            'status' => false,
                            'message' => 'Something Went Wrong In Token Generation'
                        );
                        return json_encode($response);
                    }
                    $response = array(
                        'status' => true,
                        'message' => 'TOKEN-OTP'
                    );
                    return json_encode($response);
                }
                $txn = $gst_token->txn;
            }else{
                $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                if($token_res==0){
                        $response = array(
                            'status' => false,
                            'message' => 'Something Went Wrong In Token Generation'
                        );
                        return json_encode($response);
                    }
                $response = array(
                        'status' => true,
                        'message' => 'TOKEN-OTP'
                );
                return json_encode($response);
            }
            //B2B Data
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.mastergst.com/gstr2a/b2b?email=pram92500@gmail.com&gstin='.$request->gstin.'&retperiod='.$month,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_user_name,
                'state_cd: '.$state_code,  
                'ip_address: 162.215.254.201',
                'txn: '.$txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd==0){
                $response = array(
                    'status' => false,
                    'message' => $result->error->message,
                    'data' => ""
                );
                return json_encode($response);
            }
            if(isset($result->data)){
                $GSTR2A = new GSTR2A;
                $GSTR2A->res_month = $request->month;
                $GSTR2A->res_data = json_encode($result->data);
                $GSTR2A->company_gstin = $request->gstin;
                $GSTR2A->res_type = "B2B";
                $GSTR2A->company_id = Session::get('user_company_id');
                $GSTR2A->created_at = Carbon::now(); 
                $GSTR2A->save();
                
            }
            //B2BA
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.mastergst.com/gstr2a/b2ba?email=pram92500@gmail.com&gstin='.$request->gstin.'&retperiod='.$month,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_user_name,
                'state_cd: '.$state_code,  
                'ip_address: 162.215.254.201',
                'txn: '.$txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd==1){
                if(isset($result->data)){
                    $GSTR2A = new GSTR2A;
                    $GSTR2A->res_month = $request->month;
                    $GSTR2A->res_data = json_encode($result->data);
                    $GSTR2A->company_gstin = $request->gstin;
                    $GSTR2A->res_type = "B2BA";
                    $GSTR2A->company_id = Session::get('user_company_id');
                    $GSTR2A->created_at = Carbon::now(); 
                    $GSTR2A->save();
                    
                }
            }
            //CDN
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.mastergst.com/gstr2a/cdn?email=pram92500@gmail.com&gstin='.$request->gstin.'&retperiod='.$month,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_user_name,
                'state_cd: '.$state_code,  
                'ip_address: 162.215.254.201',
                'txn: '.$txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd==1){
                if(isset($result->data)){
                    $GSTR2A = new GSTR2A;
                    $GSTR2A->res_month = $request->month;
                    $GSTR2A->res_data = json_encode($result->data);
                    $GSTR2A->company_gstin = $request->gstin;
                    $GSTR2A->res_type = "CDN";
                    $GSTR2A->company_id = Session::get('user_company_id');
                    $GSTR2A->created_at = Carbon::now(); 
                    $GSTR2A->save();
                    
                }
            }
            //CDNA
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.mastergst.com/gstr2a/cdna?email=pram92500@gmail.com&gstin='.$request->gstin.'&retperiod='.$month,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_user_name,
                'state_cd: '.$state_code,  
                'ip_address: 162.215.254.201',
                'txn: '.$txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd==1){
                if(isset($result->data)){
                    $GSTR2A = new GSTR2A;
                    $GSTR2A->res_month = $request->month;
                    $GSTR2A->res_data = json_encode($result->data);
                    $GSTR2A->company_gstin = $request->gstin;
                    $GSTR2A->res_type = "CDNA";
                    $GSTR2A->company_id = Session::get('user_company_id');
                    $GSTR2A->created_at = Carbon::now(); 
                    $GSTR2A->save();
                    
                }
            }
            $response = array(
                'status' => true,
                'message' => 'SUCCESS'
            );
            return json_encode($response);
        }else{
            $gstr2a_arr = [];
            $gstr2a = GSTR2A::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->get();            
            foreach($gstr2a as $key=>$value){                
                if($value->res_type=="B2B" || $value->res_type=="B2BA"){
                    if($value->res_type=="B2B"){
                        $res_type = "b2b";
                    }else if($value->res_type=="B2BA"){
                        $res_type = "b2ba";
                    }
                    $b2b_data = json_decode($value->res_data);
                    foreach ($b2b_data->$res_type as $k => $b2b) {
                        $account_name = "";
                        $account = Accounts::select('account_name')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('gstin',$b2b->ctin)
                                        ->first();
                        if($account){
                            $account_name = $account->account_name;
                        }
                        $invoice_amount = 0;
                        foreach ($b2b->inv as $k => $inv) {
                            $invoice_amount = $invoice_amount + $inv->val;
                        }
                        if (array_key_exists($b2b->ctin, $gstr2a_arr)) {
                            $invoice_amount = $gstr2a_arr[$b2b->ctin]['amount'] + $invoice_amount;
                            $gstr2a_arr[$b2b->ctin] = array("name"=>$account_name,"amount"=>$invoice_amount);
                        }else{
                            $gstr2a_arr[$b2b->ctin] = array("name"=>$account_name,"amount"=>$invoice_amount);
                        }                        
                    }
                }else if($value->res_type=="CDN" || $value->res_type=="CDNA"){
                    if($value->res_type=="CDN"){
                        $res_type = "cdn";
                    }else if($value->res_type=="CDNA"){
                        $res_type = "cdna";
                    }
                    $b2b_data = json_decode($value->res_data);
                    foreach ($b2b_data->$res_type as $k => $b2b) {
                        $account_name = "";
                        $account = Accounts::select('account_name')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('gstin',$b2b->ctin)
                                        ->first();
                        if($account){
                            $account_name = $account->account_name;
                        }
                        $invoice_amount = 0;
                        foreach ($b2b->nt as $k => $inv) {
                            if($inv->ntty=="D"){
                                $invoice_amount = $invoice_amount + $inv->val;
                            }else if($inv->ntty=="C"){
                                $invoice_amount = $invoice_amount - $inv->val;
                            }                            
                        }
                        if (array_key_exists($b2b->ctin, $gstr2a_arr)) {
                            $invoice_amount = $gstr2a_arr[$b2b->ctin]['amount'] + $invoice_amount;
                            $gstr2a_arr[$b2b->ctin] = array("name"=>$account_name,"amount"=>$invoice_amount);
                        }else{
                            $gstr2a_arr[$b2b->ctin] = array("name"=>$account_name,"amount"=>$invoice_amount);
                        }                        
                    }
                }
            }
            $response = array(
                'status' => true,
                'message' => 'GSTR2A',
                'data' => $gstr2a_arr
            );
            return json_encode($response);
            
        }
    }
}
