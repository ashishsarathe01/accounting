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
use App\Models\Journal;
use App\Models\BillSundrys;
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
        }else {
            $last_created_date = "";
            $gstr2a_arr = [];
        
            $gstr2a = GSTR2A::where('company_gstin', $request->gstin)
                ->where('company_id', Session::get('user_company_id'))
                ->where('res_month', $request->month)
                ->get();

            foreach ($gstr2a as $value) {
                $last_created_date = $value->created_at;
                /* ---------------- B2B & B2BA ---------------- */
                if ($value->res_type == "B2B" || $value->res_type == "B2BA") {
        
                    $res_type = ($value->res_type == "B2B") ? "b2b" : "b2ba";
                    $b2b_data = json_decode($value->res_data);
                    foreach ($b2b_data->$res_type as $b2b) {
                        /* Account Name */
                        $account = Accounts::select('account_name')
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('gstin', $b2b->ctin)
                            ->first();
                            $account_name = '';
                        if ($account) {
                            // ✅ Found in DB
                            $account_name = $account->account_name;
                        } else {
                            // ❌ Not found → Fetch from GST API
                            $email = 'pram92500@gmail.com';
                        
                                $curl = curl_init();
                        
                                curl_setopt_array($curl, [
                                    CURLOPT_URL => "https://api.mastergst.com/public/search?email={$email}&gstin={$b2b->ctin}",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => [
                                       'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                                        'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                                    ],
                                ]);
                        
                                $response = curl_exec($curl);
                                curl_close($curl);
                        
                                $result = json_decode($response, true);
                        
                                if (
                                    isset($result['status_cd']) &&
                                    $result['status_cd'] === '1' &&
                                    !empty($result['data']['tradeNam'])
                                ) {
                                    // ✅ Correct GST Legal Name
                                    $account_name = $result['data']['tradeNam'];
                                }
                        
                        }
                        /* Portal Invoice Amount */
                        $portal_amt = 0;
                        foreach ($b2b->inv as $inv) {
                            $portal_amt += $inv->val;
                        }
        
                        /* ---------------- BOOK VALUE ---------------- */

                        $purchase_book_data = Purchase::where('billing_gst', $b2b->ctin)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('merchant_gst', $request->gstin)
                            ->where('date', 'like', $request->month . '%')
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->sum('total');
        
                        $journal_book_data = Journal::where('vendor_gstin', $b2b->ctin)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('merchant_gst', $request->gstin)
                            ->where('claim_gst_status', 'YES')
                            ->where('date', 'like', $request->month . '%')
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->sum('total_amount');

                        $sale_return_book_data = SalesReturn::where('company_id',Session::get('user_company_id'))
                                                ->where('merchant_gst',$request->gstin)
                                                ->where('billing_gst',$b2b->ctin)
                                                ->where('voucher_type','PURCHASE')
                                                ->where('sr_nature','WITH GST')
                                                ->where('delete', '0')
                                                ->where('status', '1')
                                                ->where('date', 'like', $request->month.'%')
                                                ->select('total')
                                                ->sum('total');
                        $purchase_return_data = PurchaseReturn::where('company_id',Session::get('user_company_id'))
                                                ->where('merchant_gst',$request->gstin)
                                                ->where('billing_gst',$b2b->ctin)
                                                ->where('voucher_type','PURCHASE')
                                                ->where('delete', '0')
                                                 ->where('status', '1')
                                                ->where('date', 'like', $request->month.'%')
                                                ->where('sr_nature','WITH GST')
                                                ->select('total')
                                                ->sum('total');
                                                
                        $book_amt = $purchase_book_data
                            + $journal_book_data
                            + $sale_return_book_data
                            - $purchase_return_data;
        
                        /* ---------------- MERGE ---------------- */
        
                        if (isset($gstr2a_arr[$b2b->ctin])) {
                            $gstr2a_arr[$b2b->ctin]['portal_amt'] += $portal_amt;
                            $gstr2a_arr[$b2b->ctin]['book_amt']   += $book_amt;
                        } else {
                            $gstr2a_arr[$b2b->ctin] = [
                                'name'        => $account_name,
                                'portal_amt'  => round($portal_amt, 2),
                                'book_amt'    => round($book_amt, 2),
                                'diff_amt'    => 0
                            ];
                        }

                        $gstr2a_arr[$b2b->ctin]['diff_amt'] =
                        round(
                            $gstr2a_arr[$b2b->ctin]['portal_amt']
                            - $gstr2a_arr[$b2b->ctin]['book_amt'],
                            2
                        );
                    }
            }

        /* ---------------- CDN & CDNA ---------------- */
        else if ($value->res_type == "CDN" || $value->res_type == "CDNA") {

            $res_type = ($value->res_type == "CDN") ? "cdn" : "cdna";
            $cdn_data = json_decode($value->res_data);

            foreach ($cdn_data->$res_type as $b2b) {

                $portal_amt = 0;
                foreach ($b2b->nt as $nt) {
                    if ($nt->ntty == "D") {
                        $portal_amt += $nt->val;
                    } else if ($nt->ntty == "C") {
                        $portal_amt -= $nt->val;
                    }
                }

                if (isset($gstr2a_arr[$b2b->ctin])) {
                    $gstr2a_arr[$b2b->ctin]['portal_amt'] += $portal_amt;
                    $gstr2a_arr[$b2b->ctin]['diff_amt'] =
                        round(
                            $gstr2a_arr[$b2b->ctin]['portal_amt']
                            - $gstr2a_arr[$b2b->ctin]['book_amt'],
                            2
                        );
                }
            }
        }
    }

    uasort($gstr2a_arr, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    $last_created_date = Carbon::parse($last_created_date)->format('d-m-Y H:i:s');

    return json_encode([
        'status' => true,
        'message' => 'GSTR2A',
        'data' => $gstr2a_arr,
        'last_created_date' => $last_created_date
    ]);
}

    }
    public function gstr2aAllInfo(Request $request){
        $account = Accounts::select('account_name')
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstin',$request->ctin)
                            ->first();
        $account_name = "";
        if ($account) {
            // ✅ Found in DB
            $account_name = $account->account_name;
        } else {
            // ❌ Not found → Fetch from GST API
            $email = 'pram92500@gmail.com';
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.mastergst.com/public/search?email={$email}&gstin={$b2b->ctin}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                       'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                        'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                    ],
                ]);
                $response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($response, true);
                if (
                    isset($result['status_cd']) &&
                    $result['status_cd'] === '1' &&
                    !empty($result['data']['tradeNam'])
                ) {
                    // ✅ Correct GST Legal Name
                    $account_name = $result['data']['tradeNam'];
                }
        
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
                        foreach ($inv_array as $inv_key=>$inv) {
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
                                <td><input type='checkbox' checked class='check_action' data-key='".$inv_key."' data-type='b2b_invoices_rej_btn_'></td>
                                <td>{$inv->inum}</td>
                                <td>{$inv->idt}</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                <td><button class='btn btn-danger reject_btn' data-type='b2b_invoices' data-invoice='".$inv->inum."' data-date='".$inv->idt."' data-total_amount='".$inv->val."' data-taxable_amount='".$inv->itms[0]->itm_det->txval."' data-igst='".$iamt."' data-cgst='".$camt."' data-sgst='".$samt."' data-cess='' data-irn='".$inv->irn."' id='b2b_invoices_rej_btn_".$inv_key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> <button class='btn btn-success link_invoice_btn b2b_invoices_rej_btn_".$inv_key."' data-type='b2b_invoices' data-invoice='".$inv->inum."' data-date='".$inv->idt."' data-total_amount='".$inv->val."' data-ctin='".$request->ctin."' data-gstin='".$request->gstin."' data-month='".$request->month."'  style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Link</button></td>
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
                        foreach ($inv_array as $cd_key=>$inv) {
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
                                $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='link' data-invoice_no='".$inv->nt_num."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                                if($bookData->count>0){
                                    $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='unlink' data-invoice_no='".$inv->nt_num."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                                }
                                $b2b_debit_note .= "<tr>
                                    <td><input type='checkbox' checked class='check_action' data-key='".$cd_key."' data-type='b2b_debit_rej_btn_' ></td>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                    <td><button class='btn btn-danger reject_btn' data-type='b2b_debit_note' data-invoice='".$inv->nt_num."' data-date='".$inv->nt_dt."' data-total_amount='".$inv->val."' data-taxable_amount='".$inv->itms[0]->itm_det->txval."' data-igst='".$iamt."' data-cgst='".$camt."' data-sgst='".$samt."' data-cess='".$csamt."' data-irn='' id='b2b_debit_rej_btn_".$cd_key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
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
                                $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='link' data-invoice_no='".$inv->nt_num."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                                if($bookData->count>0){
                                    $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='unlink' data-invoice_no='".$inv->nt_num."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                                }
                                $b2b_credit_note .= "<tr>
                                    <td><input type='checkbox' checked class='check_action' data-key='".$cd_key."' data-type='b2b_credit_rej_btn_'></td>
                                    <td>{$inv->nt_num}</td>
                                    <td>{$inv->nt_dt}</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($inv->val)."</td>
                                    <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($inv->itms[0]->itm_det->txval)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($iamt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($camt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($samt)."</td>
                                    <td style='text-align: right;'>".formatIndianNumber($csamt)."</td>
                                    <td><button class='btn btn-danger reject_btn' data-type='b2b_credit_note' data-invoice='".$inv->nt_num."' data-date='".$inv->nt_dt."' data-total_amount='".$inv->val."' data-taxable_amount='".$inv->itms[0]->itm_det->txval."' data-igst='".$iamt."' data-cgst='".$csamt."' data-sgst='".$samt."' data-cess='".$samt."' data-irn='' id='b2b_credit_rej_btn_".$cd_key."'  style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
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
                        
                        <th colspan='3' style='text-align: right'><strong>Total</strong></th>
                        <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                        <th></th>
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
                                <th colspan='3' style='text-align: right'><strong>Total</strong></th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_val)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_book_value)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_txval)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_igst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_cgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_sgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($debit_total_cess)."</th>
                                <th></th>
                            </tr>";
            $b2b_credit_note .= "<tr>
                                <th colspan='3' style='text-align: right'><strong>Total</strong></th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_val)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_book_value)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_txval)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_igst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_cgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_sgst)."</th>
                                <th style='text-align: right'>".formatIndianNumber($credit_total_cess)."</th>
                                <th></th>
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
        $bill_sundry_igst = BillSundrys::where('company_id', Session::get('user_company_id'))
                                                ->where('nature_of_sundry', 'IGST')
                                                ->where('delete','0')
                                                ->value('id');
                                           

                $bill_sundry_cgst = BillSundrys::where('company_id', Session::get('user_company_id'))
                                                ->where('nature_of_sundry', 'CGST')
                                                ->where('delete','0')
                                                ->value('id');

                $bill_sundry_sgst = BillSundrys::where('company_id', Session::get('user_company_id'))
                                                ->where('nature_of_sundry', 'SGST')
                                                ->where('delete','0')
                                                ->value('id');

               $b2b_credit_note_unlinked_current_month = SalesReturn::whereNull('sales_returns.gstr2b_invoice_id')
                                                                    ->where('sales_returns.company_id', Session::get('user_company_id'))
                                                                    ->where('sales_returns.merchant_gst', $request->gstin)
                                                                    ->where('sales_returns.billing_gst', $request->ctin)
                                                                    ->where('sales_returns.voucher_type', 'PURCHASE')
                                                                    ->where('sales_returns.sr_nature', 'WITH GST')
                                                                    ->where('sales_returns.delete', '0')
                                                                    ->where('sales_returns.status', '1')
                                                                    ->where('sales_returns.date', 'like', $request->month . '%')

                                                                    // IGST
                                                                    ->leftJoin('sale_return_sundries as igst', function ($join) use ($bill_sundry_igst) {
                                                                        $join->on('sales_returns.id', '=', 'igst.sale_return_id')
                                                                            ->where('igst.bill_sundry', $bill_sundry_igst);
                                                                    })

                                                                    // CGST
                                                                    ->leftJoin('sale_return_sundries as cgst', function ($join) use ($bill_sundry_cgst) {
                                                                        $join->on('sales_returns.id', '=', 'cgst.sale_return_id')
                                                                            ->where('cgst.bill_sundry', $bill_sundry_cgst);
                                                                    })

                                                                    // SGST
                                                                    ->leftJoin('sale_return_sundries as sgst', function ($join) use ($bill_sundry_sgst) {
                                                                        $join->on('sales_returns.id', '=', 'sgst.sale_return_id')
                                                                            ->where('sgst.bill_sundry', $bill_sundry_sgst);
                                                                    })

                                                                    ->select(
                                                                        'sales_returns.total',
                                                                        'sales_returns.sr_prefix',
                                                                        'sales_returns.taxable_amt',
                                                                        'sales_returns.date',
                                                                        DB::raw('IFNULL(igst.amount, 0) as igst_amount'),
                                                                        DB::raw('IFNULL(cgst.amount, 0) as cgst_amount'),
                                                                        DB::raw('IFNULL(sgst.amount, 0) as sgst_amount')
                                                                    )
                                                                    ->get();

                $b2b_debit_note_unlinked_current_month = PurchaseReturn::where(function ($q) use ($request) {

        // Case 1: Unlinked → gstr2b_invoice_id IS NULL (no month condition)
                                                                                $q->whereNull('purchase_returns.gstr2b_invoice_id')
                                                                        
                                                                                  // OR
                                                                        
                                                                                  // Case 2: Linked → gstr2b_invoice_id IS NOT NULL AND month matches
                                                                                  ->orWhere(function ($q2) use ($request) {
                                                                                      $q2->whereNotNull('purchase_returns.gstr2b_invoice_id')
                                                                                         ->where('purchase_returns.linked_month','!=', $request->month);
                                                                                  });
                                                                            })
                                                                        ->where('purchase_returns.company_id', Session::get('user_company_id'))
                                                                        ->where('purchase_returns.merchant_gst', $request->gstin)
                                                                        ->where('purchase_returns.billing_gst', $request->ctin)
                                                                        ->where('purchase_returns.voucher_type', 'PURCHASE')
                                                                        ->where('purchase_returns.sr_nature', 'WITH GST')
                                                                        ->where('purchase_returns.delete', '0')
                                                                        ->where('purchase_returns.status', '1')
                                                                        ->where('purchase_returns.date', 'like', $request->month . '%')

                                                                        // IGST
                                                                        ->leftJoin('purchase_return_sundries as igst', function ($join) use ($bill_sundry_igst) {
                                                                            $join->on('purchase_returns.id', '=', 'igst.purchase_return_id')
                                                                                ->where('igst.bill_sundry', $bill_sundry_igst);
                                                                        })

                                                                        // CGST
                                                                        ->leftJoin('purchase_return_sundries as cgst', function ($join) use ($bill_sundry_cgst) {
                                                                            $join->on('purchase_returns.id', '=', 'cgst.purchase_return_id')
                                                                                ->where('cgst.bill_sundry', $bill_sundry_cgst);
                                                                        })

                                                                        // SGST
                                                                        ->leftJoin('purchase_return_sundries as sgst', function ($join) use ($bill_sundry_sgst) {
                                                                            $join->on('purchase_returns.id', '=', 'sgst.purchase_return_id')
                                                                                ->where('sgst.bill_sundry', $bill_sundry_sgst);
                                                                        })

                                                                        ->select(
                                                                            'purchase_returns.total',
                                                                            'purchase_returns.sr_prefix',
                                                                            'purchase_returns.taxable_amt',
                                                                            'purchase_returns.date',
                                                                            DB::raw('igst.amount as igst_amount'),
                                                                            DB::raw('IFNULL(cgst.amount, 0) as cgst_amount'),
                                                                            DB::raw('IFNULL(sgst.amount, 0) as sgst_amount')
                                                                        )
                                                                        ->get();
                                                                       
                                                                           
                $b2b_debit_note_unlinked="";
                $total_txval_unlink = 0; $total_igst_unlink = 0; $total_cgst_unlink = 0; $total_sgst_unlink = 0; $total_cess_unlink = 0;$total_book_value_unlink = 0;
                $credit_total_txval_unlink = 0; $credit_total_igst_unlink = 0; $credit_total_cgst_unlink = 0; $credit_total_sgst_unlink = 0; $credit_total_cess_unlink = 0; $credit_total_book_value_unlink = 0;
                foreach($b2b_debit_note_unlinked_current_month as $key=>$v){
                    $total_txval_unlink += $v->taxable_amt;
                    $total_igst_unlink  += $v->igst_amount;
                    $total_cgst_unlink  += $v->cgst_amount;
                    $total_sgst_unlink  += $v->sgst_amount;
                    $total_book_value_unlink += $v->total;
                    
                $b2b_debit_note_unlinked.="<tr>
                                                <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_debit_rej_btn_' ></td>
                                                <td>".$v->sr_prefix."</td>
                                                <td>".date('d-m-Y',strtotime($v->date))."</td>
                                                <td style='text-align: right;".$style."'>".formatIndianNumber($v->total)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->taxable_amt)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->igst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->cgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->sgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber(0)."</td>
                                                <td></td>
                                            </tr>";
                }
                
                $b2b_debit_note_unlinked .= "<tr style='font-weight:bold;background:#f3f6fa'>
                                                <td colspan='3' style='text-align:right'>TOTAL</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_book_value_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_txval_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_igst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_cgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_sgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_cess_unlink)."</td>
                                                
                                                <td></td>
                                            </tr>";

             
               $b2b_credit_note_unlinked="";
                foreach($b2b_credit_note_unlinked_current_month as $key=> $v){
                     $credit_total_txval_unlink += $v->taxable_amt;
                    $credit_total_igst_unlink  += $v->igst_amount;
                    $credit_total_cgst_unlink  += $v->cgst_amount;
                    $credit_total_sgst_unlink  += $v->sgst_amount;
                    $credit_total_book_value_unlink += $v->total;
                    
                $b2b_credit_note_unlinked.="<tr>
                                                <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_debit_rej_btn_' ></td>
                                                <td>".$v->sr_prefix."</td>
                                                <td>".date('d-m-Y',strtotime($v->date))."</td>
                                                <td style='text-align: right;".$style."'>".formatIndianNumber($v->total)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->taxable_amt)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->igst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->cgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->sgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber(0)."</td>
                                                <td></td>
                                            </tr>";
                }
                $b2b_credit_note_unlinked = "<tr style='font-weight:bold;background:#f8f9fa'>
                                                <td colspan='3' style='text-align:right'>TOTAL (Credit Notes)</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_book_value_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_txval_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_igst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_cgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_sgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_cess_unlink)."</td>
                                                <td></td>
                                                
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
            'b2ba_debit_note' => $b2ba_debit_note,
            'b2b_credit_note_unlinked' => $b2b_credit_note_unlinked,
            'b2b_debit_note_unlinked' => $b2b_debit_note_unlinked,
        ]);
    }
}
