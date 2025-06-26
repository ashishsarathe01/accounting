<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\gstToken;
use App\Models\Companies;
use App\Models\Accounts;
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
            $res_html = "";
            foreach ($result->data->b2b as $key => $value) {
                $account_name = "";
                $account = Accounts::select('account_name')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('gstin',$value->ctin)
                        ->first();
                if($account){
                    $account_name = $account->account_name;
                }
                $amount = 0;
                foreach ($value->inv as $k => $v) {
                    $amount = $amount + $v->val;
                }
                $res_html.="<tr><td>".$account_name." (".$value->ctin.")</td><td>".$amount."</td></tr>";
            }
            $response = array(
                'status' => true,
                'message' => 'GSTR2A',
                'data' => $res_html
            );
            return json_encode($response);
        }
    }
}
