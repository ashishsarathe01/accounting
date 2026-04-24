<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GSTR2A;
use App\Models\gstr2aInvoice;
use App\Models\gstr2aInvoiceItem;
use App\Models\GSTR2B;
use App\Models\gstr2bInvoice;
use App\Models\gstr2bInvoiceItem;
use App\Models\gstToken;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\Purchase;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GstDetailController extends Controller
{
    public function index(){
        
    }
    public function gstr2A(){
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
    public function gstr2B(){
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
        return view('gstReturn.gstr2b',['gst'=>$gst]);
    }
    public function gstDetailByType(Request $request){
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

        if($request->type=="GSTR-2A"){
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
        }else if($request->type=="GSTR-2B"){            
            $GSTR2B = GSTR2B::select('id')
                    ->where('company_gstin',$request->gstin)
                    ->where('company_id',Session::get('user_company_id'))
                    ->where('sup_prd',$month)
                    ->first();
            if(!$GSTR2B){          
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.mastergst.com/gstr2b/all?email=pram92500@gmail.com&gstin='.$request->gstin.'&rtnprd='.$month,
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
                // echo "<pre>";
                // print_r($result);die;
                if(isset($result->status_cd) && $result->status_cd==0){
                    echo json_encode(array("status"=>0,"message"=>$result->error->message));
                    exit();
                }
                if(isset($result->data)){
                    foreach ($result->data->data->docdata as $key => $value){
                        foreach ($value as $k => $v) {
                            $GSTR2B = new GSTR2B;
                            $GSTR2B->ctin = $v->ctin;
                            $GSTR2B->account_name = $v->trdnm;
                            $GSTR2B->sup_fill_date = $v->supfildt;
                            $GSTR2B->sup_prd = $v->supprd;
                            $GSTR2B->company_gstin = $request->gstin;
                            $GSTR2B->company_id = Session::get('user_company_id');
                            $GSTR2B->created_at = Carbon::now();
                            if($GSTR2B->save()){
                                if(isset($v->inv) && $v->inv){
                                    foreach ($v->inv as $k1 => $v1) {
                                        $srctyp = "";$irn = "";$irngendate = "";
                                        if(isset($v1->srctyp) && $v1->srctyp){
                                            $srctyp = $v1->srctyp;
                                        }
                                        if(isset($v1->irn) && $v1->irn){
                                            $irn = $v1->irn;
                                        }
                                        if(isset($v1->irngendate) && $v1->irngendate){
                                            $irngendate = $v1->irngendate;
                                        }
                                        $gstr2bInvoice = new gstr2bInvoice;
                                        $gstr2bInvoice->parent_id = $GSTR2B->id;
                                        $gstr2bInvoice->account_name = $v->trdnm;
                                        $gstr2bInvoice->account_gstin = $v->ctin;
                                        $gstr2bInvoice->invoice_no = $v1->inum;
                                        $gstr2bInvoice->type = $v1->typ;
                                        $gstr2bInvoice->idate = $v1->dt;
                                        $gstr2bInvoice->amount = $v1->val;
                                        $gstr2bInvoice->srctyp = $srctyp;
                                        $gstr2bInvoice->irn = $irn;
                                        $gstr2bInvoice->irngendate = $irngendate;
                                        $gstr2bInvoice->company_gstin = $request->gstin;
                                        $gstr2bInvoice->company_id = Session::get('user_company_id');
                                        $gstr2bInvoice->created_at = Carbon::now();
                                        if($gstr2bInvoice->save()){
                                            if(isset($v1->items) && $v1->items){
                                                foreach ($v1->items as $k2 => $v2) {
                                                    $gstr2bInvoiceItem = new gstr2bInvoiceItem;
                                                    $gstr2bInvoiceItem->parent_id = $gstr2bInvoice->id;
                                                    $gstr2bInvoiceItem->sparent_id = $GSTR2B->id;
                                                    $gstr2bInvoiceItem->snum = $v2->num;
                                                    $gstr2bInvoiceItem->rate = $v2->rt;
                                                    $gstr2bInvoiceItem->taxable_amount = $v2->txval;
                                                    $gstr2bInvoiceItem->igst = $v2->igst;
                                                    $gstr2bInvoiceItem->cgst = $v2->cgst;
                                                    $gstr2bInvoiceItem->sgst = $v2->sgst;
                                                    $gstr2bInvoiceItem->cess = $v2->cess;
                                                    $gstr2bInvoiceItem->company_gstin = $request->gstin;
                                                    $gstr2bInvoiceItem->company_id = Session::get('user_company_id');
                                                    $gstr2bInvoiceItem->created_at = Carbon::now();
                                                    $gstr2bInvoiceItem->save();
                                                }
                                            }
                                            
                                        }
                                    }
                                }
                                
                            }                
                        }
                    }
                    $response = array(
                        'status' => true,
                        'message' => 'SUCCESS',
                        'data' => ""
                    );
                    return json_encode($response);
                }
            }else{
                $gstr2b = GSTR2B::select('account_name','ctin')
                                    ->where('company_gstin',$request->gstin)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->where('sup_prd',$month)
                                    ->groupBy('ctin','account_name')
                                    ->orderBy('account_name')
                                    ->get();
                $idate = date('m-Y',strtotime($request->month));
                foreach ($gstr2b as $key => $value) {
                    $gstr2bInvoice = gstr2bInvoice::where('account_gstin',$value->ctin)
                                    ->where('company_gstin',$request->gstin)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->where('idate','like','%'.$idate)
                                    ->sum('amount');
                    $gstr2b[$key]->amount =  round($gstr2bInvoice,2);
                    // $purchase = Purchase::where('billing_gst',$value->ctin)
                    //             ->where('date','like',$idate.'%')
                    //             ->where('merchant_gstin',$request->gstin)
                    //             ->where('company_id',Session::get('user_company_id'))
                    //             ->sum('total');
                    // $gstr2b[$key]->book_amount =  round($purchase,2);

                }
                $response = array(
                    'status' => true,
                    'message' => 'GSTR2B',
                    'data' => $gstr2b
                );
                return json_encode($response);
            }
        }
    }
    public function verifyGstTokenOtp(Request $request){
        $state_code = substr($request->gstin,0,2);
        $gst_token = gstToken::select('txn','created_at','id')
                            ->where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->orderBy('id','desc')
                            ->first();
        $txn = $gst_token->txn;
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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mastergst.com/authentication/authtoken?email=pram92500@gmail.com&otp='.$request->otp,
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
                'txn:'.$txn,
                'ip_address: 162.215.254.201',
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        if(isset($result->status_cd) && $result->status_cd=='1'){
            $gst_token = gstToken::find($gst_token->id);
            $gst_token->status = 1;
            $gst_token->updated_at = Carbon::now();
            $gst_token->update();
            $response = array(
                        'status' => true,
                        'message' => '',
                        'data' => ""
            );
            return json_encode($response);        
        }else{
            if(isset($result->error)){
                $response = array(
                        'status' => false,
                        'message' => $result->error->message,
                        'data' => ""
                );
                return json_encode($response);
            }else{
                $response = array(
                        'status' => false,
                        'message' => 'Something Went Wrong.',
                        'data' => ""
                );
                return json_encode($response);
            }
        }
    }
}
