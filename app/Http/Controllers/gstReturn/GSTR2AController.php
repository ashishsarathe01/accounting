<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\gstToken;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\GSTR2A;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
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
        if($request->refresh==1){
            GSTR2A::where('company_gstin',$request->gstin)
                    ->where('company_id',Session::get('user_company_id'))
                    ->where('res_month',$request->month)
                    ->delete();
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
            $last_created_date = "";
            $gstr2a_arr = [];
            $gstr2a = GSTR2A::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->get();            
            foreach($gstr2a as $key=>$value){   
                $last_created_date = $value->created_at;          
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
            uasort($gstr2a_arr, function ($a, $b) {
                return strcmp($a['name'], $b['name']); // Ascending order
            });
           
            $last_created_date = Carbon::parse($last_created_date)->format('d-m-Y H:i:s');
            $response = array(
                'status' => true,
                'message' => 'GSTR2A',
                'data' => $gstr2a_arr,
                'last_created_date'=>$last_created_date
            );
            return json_encode($response);
            
        }
    }
    public function gstr2aAllInfo(Request $request){
        $account = Accounts::select('account_name')
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstin',$request->ctin)
                            ->first();
        $account_name = "";
        if($account){
            $account_name = $account->account_name;
        }
        $b2b_invoices = "";$b2b_credit_note = "";$b2b_debit_note = "";$b2ba_invoices = "";$b2ba_credit_note = "";$b2ba_debit_note = "";
        $gstr2a = GSTR2A::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->get();   
        $total_val = 0;$total_book_value = 0;$total_txval = 0;$total_igst = 0;$total_cgst = 0;$total_sgst = 0;$total_cess = 0;
        $b2ba_total_val = 0;$b2ba_total_book_value = 0;$b2ba_total_txval = 0;$b2ba_total_igst = 0;$b2ba_total_cgst = 0;$b2ba_total_sgst = 0;$b2ba_total_cess = 0;
        $debit_total_val = 0;$debit_total_book_value = 0;$debit_total_txval = 0;$debit_total_igst = 0;$debit_total_cgst = 0;$debit_total_sgst = 0;$debit_total_cess = 0;
        $credit_total_val = 0;$credit_total_book_value = 0;$credit_total_txval = 0;$credit_total_igst = 0;$credit_total_cgst = 0;$credit_total_sgst = 0;$credit_total_cess = 0;
        $cdna_debit_total_val = 0;$cdna_debit_total_book_value = 0;$cdna_debit_total_txval = 0;$cdna_debit_total_igst = 0;$cdna_debit_total_cgst = 0;$cdna_debit_total_sgst = 0;$cdna_debit_total_cess = 0;
        $cdna_credit_total_val = 0;$cdna_credit_total_book_value = 0;$cdna_credit_total_txval = 0;$cdna_credit_total_igst = 0;$cdna_credit_total_cgst = 0;$cdna_credit_total_sgst = 0;$cdna_credit_total_cess = 0;
        foreach($gstr2a as $key=>$value){
            if($value->res_type=="B2B"){
                $res_type = "b2b";
            }else if($value->res_type=="B2BA"){
                $res_type = "b2ba";
            }else if($value->res_type=="CDN"){
                $res_type = "cdn";
            }else if($value->res_type=="CDNA"){
                $res_type = "cdna";
            }            
            $b2b_data = json_decode($value->res_data);
            foreach ($b2b_data->$res_type as $k => $b2b) {
                if($b2b->ctin === $request->ctin) {
                    if ($value->res_type == "B2B") {                    
                        // Convert to array if not already
                        $inv_array = is_array($b2b->inv) ? $b2b->inv : (array) $b2b->inv;
                        // Sort by idt (invoice date)
                        usort($inv_array, function ($a, $b) {
                            return strtotime($a->idt) - strtotime($b->idt);
                        });
                        foreach ($inv_array as $inv) {
                            $iamt = $camt = $samt = $csamt = 0;
                            if (isset($inv->itms[0]->itm_det->iamt)) {
                                $iamt = $inv->itms[0]->itm_det->iamt;
                            }
                            if (isset($inv->itms[0]->itm_det->camt)) {
                                $camt = $inv->itms[0]->itm_det->camt;
                            }
                            if (isset($inv->itms[0]->itm_det->samt)) {
                                $samt = $inv->itms[0]->itm_det->samt;
                            }
                            if (isset($inv->itms[0]->itm_det->csamt)) {
                                $csamt = $inv->itms[0]->itm_det->csamt;
                            }
                            //Book Value
                            $book_data = Purchase::select('total')
                                                ->where('billing_gst',$request->ctin)
                                                ->where('company_id',Session::get('user_company_id'))
                                                ->where('merchant_gst',$request->gstin)
                                                ->where('voucher_no',$inv->inum)
                                                ->where('status','1')
                                                ->where('delete','0')
                                                ->first();
                            $book_value = 0;
                            if($book_data){
                                $book_value = $book_data->total;
                            }
                            $style = "";
                            if($book_value!=$inv->val){
                                $style = "color: red;";
                            }
                            $total_val += $inv->val;
                            $total_txval += $inv->itms[0]->itm_det->txval;
                            $total_igst += $iamt;
                            $total_cgst += $camt;
                            $total_sgst += $samt;
                            $total_cess += $csamt;
                            $total_book_value += $book_value;
                            $b2b_invoices .= "<tr>
                                <td>{$inv->inum}</td>
                                <td>{$inv->idt}</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                            </tr>";
                        }
                        
                    }else if($value->res_type == "B2BA"){                    
                        // Convert to array if not already
                        $inv_array = is_array($b2b->inv) ? $b2b->inv : (array) $b2b->inv;
                        // Sort by idt (invoice date)
                        usort($inv_array, function ($a, $b) {
                            return strtotime($a->idt) - strtotime($b->idt);
                        });
                        foreach ($inv_array as $inv) {
                            $iamt = $camt = $samt = $csamt = 0;
                            if (isset($inv->itms[0]->itm_det->iamt)) {
                                $iamt = $inv->itms[0]->itm_det->iamt;
                            }
                            if (isset($inv->itms[0]->itm_det->camt)) {
                                $camt = $inv->itms[0]->itm_det->camt;
                            }
                            if (isset($inv->itms[0]->itm_det->samt)) {
                                $samt = $inv->itms[0]->itm_det->samt;
                            }
                            if (isset($inv->itms[0]->itm_det->csamt)) {
                                $csamt = $inv->itms[0]->itm_det->csamt;
                            }
                            //Book Value
                            $book_data = Purchase::select('total')
                                                ->where('billing_gst',$request->ctin)
                                                ->where('company_id',Session::get('user_company_id'))
                                                ->where('merchant_gst',$request->gstin)
                                                ->where('voucher_no',$inv->inum)
                                                ->where('status','1')
                                                ->where('delete','0')
                                                ->first();
                            $book_value = 0;
                            if($book_data){
                                $book_value = $book_data->total;
                            }
                            $style = "";
                            if($book_value!=$inv->val){
                                $style = "color: red;";
                            }
                            $b2ba_total_val += $inv->val;
                            $b2ba_total_txval += $inv->itms[0]->itm_det->txval;
                            $b2ba_total_igst += $iamt;
                            $b2ba_total_cgst += $camt;
                            $b2ba_total_sgst += $samt;
                            $b2ba_total_cess += $csamt;
                            $b2ba_total_book_value += $book_value;
                            $b2ba_invoices .= "<tr>
                                <td>{$inv->inum}</td>
                                <td>{$inv->idt}</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                            </tr>";
                        }
                    }else if($value->res_type == "CDN"){
                        
                        // Convert to array if not already
                        $inv_array = is_array($b2b->nt) ? $b2b->nt : (array) $b2b->nt;
                        // Sort by idt (invoice date)
                        usort($inv_array, function ($a, $b) {
                            return strtotime($a->nt_dt) - strtotime($b->nt_dt);
                        });
                        foreach ($inv_array as $inv) {
                            $iamt = $camt = $samt = $csamt = 0;
                            if (isset($inv->itms[0]->itm_det->iamt)) {
                                $iamt = $inv->itms[0]->itm_det->iamt;
                            }
                            if (isset($inv->itms[0]->itm_det->camt)) {
                                $camt = $inv->itms[0]->itm_det->camt;
                            }
                            if (isset($inv->itms[0]->itm_det->samt)) {
                                $samt = $inv->itms[0]->itm_det->samt;
                            }
                            if (isset($inv->itms[0]->itm_det->csamt)) {
                                $csamt = $inv->itms[0]->itm_det->csamt;
                            }
                            if($inv->ntty=="D"){
                                $bookData = SalesReturn::where('gstr2b_invoice_id',$inv->nt_num)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                            ->first(); 
                                $book_value = 0;
                                if($bookData->total!=''){
                                    $book_value = $bookData->total;
                                }   
                                $style = "";
                                if($book_value!=$inv->val){
                                    $style = "color: red;";
                                }
                                $debit_total_val += $inv->val;
                                $debit_total_txval += $inv->itms[0]->itm_det->txval;
                                $debit_total_igst += $iamt;
                                $debit_total_cgst += $camt;
                                $debit_total_sgst += $samt;
                                $debit_total_cess += $csamt;
                                $debit_total_book_value += $book_value;
                                $b2b_debit_note .= "<tr>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                </tr>";
                            }else if($inv->ntty=="C"){                            
                                $bookData = PurchaseReturn::where('gstr2b_invoice_id',$inv->nt_num)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                            ->first();
                                $book_value = 0;
                                if($bookData->total!=''){
                                    $book_value = $bookData->total;
                                } 
                                $style = "";
                                if($book_value!=$inv->val){
                                    $style = "color: red;";
                                }
                                $credit_total_val += $inv->val;
                                $credit_total_txval += $inv->itms[0]->itm_det->txval;
                                $credit_total_igst += $iamt;
                                $credit_total_cgst += $camt;
                                $credit_total_sgst += $samt;
                                $credit_total_cess += $csamt;
                                $credit_total_book_value += $book_value;
                                $b2b_credit_note .= "<tr>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                </tr>";

                            }                            
                        }
                    }else if($value->res_type == "CDNA"){                    
                        // Convert to array if not already
                        $inv_array = is_array($b2b->nt) ? $b2b->nt : (array) $b2b->nt;
                        // Sort by idt (invoice date)
                        usort($inv_array, function ($a, $b) {
                            return strtotime($a->idt) - strtotime($b->idt);
                        });
                        foreach ($inv_array as $inv) {
                            $iamt = $camt = $samt = $csamt = 0;
                            if (isset($inv->itms[0]->itm_det->iamt)) {
                                $iamt = $inv->itms[0]->itm_det->iamt;
                            }
                            if (isset($inv->itms[0]->itm_det->camt)) {
                                $camt = $inv->itms[0]->itm_det->camt;
                            }
                            if (isset($inv->itms[0]->itm_det->samt)) {
                                $samt = $inv->itms[0]->itm_det->samt;
                            }
                            if (isset($inv->itms[0]->itm_det->csamt)) {
                                $csamt = $inv->itms[0]->itm_det->csamt;
                            }
                            if($inv->ntty=="D"){
                                $cdna_debit_total_val += $inv->val;
                                $cdna_debit_total_txval += $inv->itms[0]->itm_det->txval;
                                $cdna_debit_total_igst += $iamt;
                                $cdna_debit_total_cgst += $camt;
                                $cdna_debit_total_sgst += $samt;
                                $cdna_debit_total_cess += $csamt;
                                $cdna_debit_total_book_value += $book_value;
                                $b2ba_debit_note .= "<tr>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                </tr>";
                            }else if($inv->ntty=="C"){
                                
                                $bookData = PurchaseReturn::where('gstr2b_invoice_id',$inv->nt_num)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                            ->first();
                                $book_value = 0;
                                if($bookData->total!=''){
                                    $book_value = $bookData->total;
                                }
                                $style = "";
                                if($book_value!=$inv->val){
                                    $style = "color: red;";
                                }
                                $cdna_credit_total_val += $inv->val;
                                $cdna_credit_total_txval += $inv->itms[0]->itm_det->txval;
                                $cdna_credit_total_igst += $iamt;
                                $cdna_credit_total_cgst += $camt;
                                $cdna_credit_total_sgst += $samt;
                                $cdna_credit_total_cess += $csamt;
                                $cdna_credit_total_book_value += $book_value;
                                $b2ba_credit_note .= "<tr>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                </tr>";
                            }
                        }
                        
                    }
                }
            }
        }
        $b2b_invoices .= "<tr>
                        
                        <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                        <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                    </tr>";
        $b2ba_invoices .= "<tr>
                        <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_val)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_book_value)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_txval)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_igst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_cgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_sgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($b2ba_total_cess)."</th>
                    </tr>";
        $b2b_debit_note .= "<tr>
                                <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_val)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_book_value)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_txval)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_igst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_cgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_sgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_cess)."</th>
                            </tr>";
            $b2b_credit_note .= "<tr>
                                <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_val)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_book_value)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_txval)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_igst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_cgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_sgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_cess)."</th>
                            </tr>";
        $b2ba_debit_note .= "<tr>
                                <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_val)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_book_value)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_txval)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_igst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_cgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_sgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($cdna_debit_total_cess)."</th>
                            </tr>";
        $b2ba_credit_note .= "<tr>
                            <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_val)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_book_value)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_txval)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_igst)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_cgst)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_sgst)."</th>
                            <th style='text-align: right'>".formatIndianNumber($cdna_credit_total_cess)."</th>
                        </tr>";
        return view('gstReturn.gstr2a_all_info',[
            'account_name' => $account_name,
            'ctin' => $request->ctin,
            'month' => $request->month,
            'gstin' => $request->gstin,
            'b2b_invoices' => $b2b_invoices,
            'b2b_credit_note' => $b2b_credit_note,
            'b2b_debit_note' => $b2b_debit_note,
            'b2ba_invoices' => $b2ba_invoices,
            'b2ba_credit_note' => $b2ba_credit_note,
            'b2ba_debit_note' => $b2ba_debit_note
        ]);
    }
}
