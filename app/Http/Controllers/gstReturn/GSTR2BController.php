<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GSTR2B;
use App\Models\Companies;
use App\Models\gstToken;
use App\Models\Accounts;
use App\Models\RejectedGstr2b;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Purchase;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GSTR2BController extends Controller
{
    /**
     * Display the GSTR2B view.
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
        return view('gstReturn.gstr2b',['gst'=>$gst]);
    }
    
    public function gstr2bDetail(Request $request){
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
        $GSTR2B = GSTR2B::select('id')
                    ->where('company_gstin',$request->gstin)
                    ->where('company_id',Session::get('user_company_id'))
                    ->where('res_month',$request->month)
                    ->first();
        if(!$GSTR2B){
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
                $GSTR2B = new GSTR2B;
                $GSTR2B->res_month = $request->month;
                $GSTR2B->res_data = json_encode($result->data);
                $GSTR2B->company_gstin = $request->gstin;
                $GSTR2B->company_id = Session::get('user_company_id');
                $GSTR2B->created_at = Carbon::now(); 
                $GSTR2B->save();                                       
            }                  
            $response = array(
                'status' => true,
                'message' => 'SUCCESS',
                'data' => ""
            );
            return json_encode($response);                
        }else{
            $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->first(); 
            
            $gstr2b = json_decode($gstr2b->res_data);
            $uniqueSuppliers = [];$supplier_amount = [];
            $sections = ['b2b', 'cdnr', 'b2ba', 'cdnra'];
            foreach ($sections as $section) {
                if (!empty($gstr2b->data->docdata->$section)) {
                    foreach ($gstr2b->data->docdata->$section as $record) {
                        if (!isset($uniqueSuppliers[$record->ctin])) {
                            $uniqueSuppliers[$record->ctin] = $record->trdnm;
                        }
                        if($section=="b2b" || $section=="cdnr" || $section=="b2ba" || $section=="cdnra"){
                            $invoice_amount = 0;
                            if($section=="b2b"){
                                foreach ($record->inv as $invoice) {
                                    $invoice_amount += $invoice->val;
                                }
                            }else if($section=="cdnr"){
                                foreach ($record->nt as $invoice) {
                                    if($invoice->typ == "C"){
                                        $invoice_amount -= $invoice->val;
                                    }else if($invoice->typ == "D"){
                                        $invoice_amount += $invoice->val;
                                    }
                                }
                            }else if($section=="b2ba"){
                                foreach ($record->inv as $invoice) {
                                    $invoice_amount += $invoice->val;
                                }
                            }else if($section=="cdnra"){
                                foreach ($record->nt as $invoice) {
                                    if($invoice->typ == "C"){
                                        $invoice_amount -= $invoice->val;
                                    }else if($invoice->typ == "D"){
                                        $invoice_amount += $invoice->val;
                                    }
                                }
                            }
                            if (!isset($supplier_amount[$record->ctin])) {
                                $supplier_amount[$record->ctin] = $invoice_amount;
                            }else{
                                $supplier_amount[$record->ctin] += $invoice_amount;
                            }
                        }
                        
                    }
                }
            }
            // Convert to array of objects if needed
            $suppliers = [];
            foreach ($uniqueSuppliers as $ctin => $trdnm) {
                $rejected_invoice_debit_note = RejectedGstr2b::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('ctin',$ctin)
                            ->whereIn('type',['b2b_invoices','b2b_debit_note','b2ba_invoices','b2ba_debit_note'])
                            ->where('gstr2b_month',$request->month)
                            ->sum('total_amount');
                $rejected_credit_note = RejectedGstr2b::where('company_gstin',$request->gstin)
                ->where('company_id',Session::get('user_company_id'))
                ->where('ctin',$ctin)
                ->whereIn('type',['b2b_credit_note','b2ba_credit_note'])
                ->where('gstr2b_month',$request->month)
                ->sum('total_amount');
                if(!isset($supplier_amount[$ctin])){
                    $supplier_amount[$ctin] = 0;
                }
                $supplier_amount[$ctin] = $supplier_amount[$ctin]- $rejected_invoice_debit_note + $rejected_credit_note;
                $suppliers[] = (object)[
                    'ctin' => $ctin,
                    'trdnm' => $trdnm,
                    'amount' => isset($supplier_amount[$ctin]) ? $supplier_amount[$ctin] : 0
                ];
            }
            // Sort by trdnm (case-insensitive)
            usort($suppliers, function ($a, $b) {
                return strcasecmp($a->trdnm, $b->trdnm);
            });
            $response = array(
                'status' => true,
                'message' => 'GSTR2B',
                'data' => $suppliers
            );
            return json_encode($response);
        }
    }
    public function gstr2bAllInfo(Request $request){
        $account = Accounts::select('account_name')
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstin',$request->ctin)
                            ->first();
        $account_name = "";
        if($account){
            $account_name = $account->account_name;
        }
        $RejectedGstr2b = RejectedGstr2b::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('ctin',$request->ctin)
                            ->where('gstr2b_month',$request->month)
                            ->get();
        $rejectedIrns = $RejectedGstr2b->pluck('invoice_number')->toArray();
        $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('res_month',$request->month)
                            ->first();        
        $gstr2b = json_decode($gstr2b->res_data);
        $b2b_invoices = "";$total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0; $total_book_value = 0;
        foreach ($gstr2b->data->docdata->b2b as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->inv as $key=>$invoice) {
                    if (in_array($invoice->inum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                    $book_data = Purchase::select('total')
                                            ->where('billing_gst',$request->ctin)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->where('voucher_no',$invoice->inum)
                                            ->where('status','1')
                                            ->where('delete','0')
                                            ->first();
                    $book_value = 0;
                    if($book_data){
                        $book_value = $book_data->total;
                    }
                    $total_val += $invoice->val;
                    $total_txval += $invoice->txval;
                    $total_igst += $invoice->igst;
                    $total_cgst += $invoice->cgst;
                    $total_sgst += $invoice->sgst;
                    $total_cess += $invoice->cess;
                    $total_book_value += $book_value;
                    if(!isset($invoice->irn)){
                        $invoice->irn = "";
                    }                    
                    $style = "";
                    if($book_value!=$invoice->val){
                        $style = "color: red;";
                    }
                    $b2b_invoices.="<tr>
                        <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_invoices_rej_btn_'></td>
                        <td>".$invoice->inum."</td>
                        <td>".$invoice->dt."</td>
                        <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                        <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                        <td><button class='btn btn-danger reject_btn' data-type='b2b_invoices' data-invoice='".$invoice->inum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='".$invoice->irn."' id='b2b_invoices_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button></td>
                    </tr>";
                }
                $b2b_invoices .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                    <td></td>
                </tr>";
                //Rejected GSTR2B
                $rejected_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2b_invoices"){
                        if($rejected_total==0){
                            $b2b_invoices.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Invoice</strong></td></tr>";
                        }
                        $rejected_total = $rejected_total + $v->total_amount;
                        $b2b_invoices.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                if($rejected_total>0){
                    $b2b_invoices.="<tr >
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_total)."</th>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                break; // Stop after first match
            }
        }
        $b2b_debit_note = "";$b2b_credit_note = "";
        $total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;$total_book_value = 0;
        $debit_total_val = 0; $debit_total_txval = 0; $debit_total_igst = 0; $debit_total_cgst = 0; $debit_total_sgst = 0; $debit_total_cess = 0; $total_debit_book_value = 0;
        foreach ($gstr2b->data->docdata->cdnr as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->nt as $key=>$invoice) {
                    if (in_array($invoice->ntnum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                    if($invoice->typ == "C"){
                        $bookData = PurchaseReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                        ->first();
                        $book_value = 0;
                        if($bookData->total!=''){
                            $book_value = $bookData->total;
                        }                  
                        $total_val += $invoice->val;
                        $total_txval += $invoice->txval;
                        $total_igst += $invoice->igst;
                        $total_cgst += $invoice->cgst;
                        $total_sgst += $invoice->sgst;
                        $total_cess += $invoice->cess;
                        $total_book_value += $book_value;
                        $style = "";
                        if($book_value!=$invoice->val){
                            $style = "color: red;";
                        }
                        $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='link' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                        if($bookData->count>0){
                            $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='unlink' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                        }
                        $b2b_credit_note.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_credit_rej_btn_'></td>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td><button class='btn btn-danger reject_btn' data-type='b2b_credit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2b_credit_rej_btn_".$key."'  style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
                        </tr>";
                    }else if($invoice->typ == "D"){
                        $bookData = SalesReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                        ->first();
                        $book_value = $bookData->total;
                        $debit_total_val += $invoice->val;
                        $debit_total_txval += $invoice->txval;
                        $debit_total_igst += $invoice->igst;
                        $debit_total_cgst += $invoice->cgst;
                        $debit_total_sgst += $invoice->sgst;
                        $debit_total_cess += $invoice->cess;
                        $total_debit_book_value += $book_value;
                        $style = "";
                        if($book_value!=$invoice->val){
                            $style = "color: red;";
                        }
                        $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='link' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                        if($bookData->count>0){
                            $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='unlink' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                        }
                        $b2b_debit_note.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_debit_rej_btn_' ></td>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td><button class='btn btn-danger reject_btn' data-type='b2b_debit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2b_debit_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
                        </tr>";
                    }                    
                }
                $b2b_credit_note .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                    <td></td>
                </tr>";
                $b2b_debit_note .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_debit_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cess)."</th>
                    <td></td>
                </tr>";
                //Rejected GSTR2B
                $rejected_debit_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2b_debit_note"){
                        if($rejected_debit_total==0){
                            $b2b_debit_note.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Debit Note</strong></td></tr>";
                        }
                        $rejected_debit_total = $rejected_debit_total + $v->total_amount;                        
                        $b2b_debit_note.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                $rejected_credit_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2b_credit_note"){
                        if($rejected_credit_total==0){
                            $b2b_credit_note.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Credit Note</strong></td></tr>";
                        }                        
                        $rejected_credit_total = $rejected_credit_total + $v->total_amount;
                        $b2b_credit_note.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."  <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></strong></td>
                        </tr>";
                    }
                }
                if($rejected_debit_total>0){
                    $b2b_debit_note.="<tr>
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_debit_total)."</th>
                            <td></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                if($rejected_credit_total>0){
                    $b2b_credit_note.="<tr>
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_credit_total)."</th>
                            <td></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                break; // Stop after first match
            }
        }
        //Get B2BA Invoices
        $b2ba_invoices = "";
        $total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;$total_book_value = 0;
        if(!isset($gstr2b->data->docdata->b2ba)){
            $gstr2b->data->docdata->b2ba = [];
        }
        if(!isset($gstr2b->data->docdata->cdnra)){
            $gstr2b->data->docdata->cdnra = [];
        }
        foreach ($gstr2b->data->docdata->b2ba as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->inv as $key=>$invoice) {
                    if (in_array($invoice->inum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                    $book_data = Purchase::select('total')
                                            ->where('billing_gst',$request->ctin)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->where('voucher_no',$invoice->inum)
                                            ->where('status','1')
                                            ->where('delete','0')
                                            ->first();
                    $book_value = 0;
                    if($book_data){
                        $book_value = $book_data->total;
                    }
                    $total_val += $invoice->val;
                    $total_txval += $invoice->txval;
                    $total_igst += $invoice->igst;
                    $total_cgst += $invoice->cgst;
                    $total_sgst += $invoice->sgst;
                    $total_cess += $invoice->cess;
                    $total_book_value += $book_value;
                    if(!isset($invoice->irn)){
                        $invoice->irn = "";
                    }
                    $style = "";
                    if($book_value!=$invoice->val){
                        $style = "color: red;";
                    }
                    $b2ba_invoices.="<tr>
                        <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2ba_invoices_rej_btn_'></td>
                        <td>".$invoice->inum."</td>
                        <td>".$invoice->dt."</td>
                        <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                        <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                        <td><button class='btn btn-danger reject_btn' data-type='b2ba_invoices' data-invoice='".$invoice->inum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='".$invoice->irn."' id='b2ba_invoices_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button></td>
                    </tr>";
                }
                $b2ba_invoices .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                    <td></td>
                </tr>";
                //Rejected GSTR2B
                $rejected_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2ba_invoices"){
                        if($rejected_total==0){
                            $b2ba_invoices.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Invoices</strong></td></tr>";
                        }
                        $rejected_total = $rejected_total + $v->total_amount;
                        $b2ba_invoices.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                if($rejected_total>0){
                    $b2ba_invoices.="<tr>
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_total)."</th>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                break; // Stop after first match
            }
        }
        //Get B2BA Credit Note
        $b2ba_credit_note = "";$b2ba_debit_note = "";
        $total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;$total_book_value = 0;
        $debit_total_val = 0; $debit_total_txval = 0; $debit_total_igst = 0; $debit_total_cgst = 0; $debit_total_sgst = 0; $debit_total_cess = 0;$total_debit_book_value = 0;
        foreach ($gstr2b->data->docdata->cdnra as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->nt as $key=>$invoice) {
                    if (in_array($invoice->ntnum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                    if($invoice->typ == "C"){
                        $bookData = PurchaseReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                        ->first();
                        $book_value = 0;
                        if($bookData->total!=''){
                            $book_value = $bookData->total;
                        }
                        $total_book_value = $book_value;
                        $total_val += $invoice->val;
                        $total_txval += $invoice->txval;
                        $total_igst += $invoice->igst;
                        $total_cgst += $invoice->cgst;
                        $total_sgst += $invoice->sgst;
                        $total_cess += $invoice->cess;
                        $style = "";
                        if($book_value!=$invoice->val){
                            $style = "color: red;";
                        }
                        $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='link' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                        if($bookData->count>0){
                            $link_btn = "<button class='btn btn-primary link_btn' data-type='credit_note' data-action_type='unlink' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                        }
                        $b2ba_credit_note.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2ba_credit_rej_btn_'></td>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right';".$style.">".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td><button class='btn btn-danger reject_btn' data-type='b2ba_credit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2ba_credit_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
                        </tr>";
                    }else if($invoice->typ == "D"){
                        $bookData = SalesReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->selectRaw('COUNT(*) as count, SUM(total) as total')
                                        ->first();
                        $book_value = $bookData->total;
                        if($book_value==''){
                            $book_value = 0;
                        }
                        $total_debit_book_value = $book_value;
                        
                        $debit_total_val += $invoice->val;
                        $debit_total_txval += $invoice->txval;
                        $debit_total_igst += $invoice->igst;
                        $debit_total_cgst += $invoice->cgst;
                        $debit_total_sgst += $invoice->sgst;
                        $debit_total_cess += $invoice->cess;
                        $style = "";
                        if($book_value!=$invoice->val){
                            $style = "color: red;";
                        }
                        $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='link' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Link</button>";
                        if($bookData->count>0){
                            $link_btn = "<button class='btn btn-primary link_btn' data-type='debit_note' data-action_type='unlink' data-invoice_no='".$invoice->ntnum."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>UnLink</button>";
                        }
                        $b2ba_debit_note.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2ba_debit_rej_btn_'></td>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td><button class='btn btn-danger reject_btn' data-type='b2ba_credit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2ba_debit_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button></td>
                        </tr>";
                    }
                }
                $b2ba_credit_note .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                    <td></td>
                </tr>";
                $b2ba_debit_note .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_debit_book_value)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cess)."</th>
                    <td></td>
                </tr>";
                //Rejected GSTR2B
                $rejected_debit_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2ba_debit_note"){
                        if($rejected_debit_total==0){
                            $b2ba_debit_note.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Debit Note</strong></td></tr>";
                        }
                        $rejected_debit_total = $rejected_debit_total + $v->total_amount;
                        $b2ba_debit_note.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                $rejected_credit_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2ba_credit_note"){
                        if($rejected_credit_total==0){
                            $b2ba_credit_note.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Credit Note</strong></td></tr>";
                        }
                        $rejected_credit_total = $rejected_credit_total + $v->total_amount;
                        $b2ba_credit_note.="<tr>
                            <td></td>
                            <td>".$v->invoice_number."</td>
                            <td>".date('d-m-Y',strtotime($v->invoice_date))."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->total_amount)."</td>
                            <td></td>
                            <td style='text-align: right'>".formatIndianNumber($v->taxable_amount)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($v->cess)."</td>
                            <td><strong>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                if($rejected_debit_total>0){
                    $b2ba_debit_note.="<tr>
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_debit_total)."</th>
                            <td></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                if($rejected_credit_total>0){
                    $b2ba_credit_note.="<tr >
                            <td></td>
                            <td></td>
                            <th style='text-align: right'>Total</th>
                            <th style='text-align: right'>".formatIndianNumber($rejected_credit_total)."</th>
                            <td></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td style='text-align: right'></td>
                            <td></td>
                        </tr>";
                }
                break; // Stop after first match
            }
        }
        // echo "<pre>";
        // print_r($b2b_invoices);
        return view('gstReturn.gstr2b_all_info',[
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
    public function rejectEntry(Request $request){
        $request->validate([
            'gstin' => 'required',
            'ctin' => 'required',
            'type' => 'required',
            'invoice' => 'required',
            'date' => 'required',
            'total_amount' => 'required|numeric|min:0',
            'taxable_amount' => 'required|numeric|min:0',
            'igst' => 'nullable|numeric|min:0',
            'cgst' => 'nullable|numeric|min:0',
            'sgst' => 'nullable|numeric|min:0',
            'cess' => 'nullable|numeric|min:0',
            'irn' => 'nullable|string'
        ]);
        RejectedGstr2b::create([
            'company_id' => Session::get('user_company_id'),
            'company_gstin' => $request->gstin,
            'ctin' => $request->ctin,
            'type' => $request->type,
            'invoice_number' => $request->invoice,
            'invoice_date' => date('Y-m-d',strtotime($request->date)),
            'total_amount' => $request->total_amount,
            'taxable_amount' => $request->taxable_amount,
            'igst' => $request->igst,
            'cgst' => $request->cgst,
            'sgst' => $request->sgst,
            'cess' => $request->cess,
            'irn' => $request->irn,
            'remark' => $request->remark,
            'gstr2b_month' => $request->gstr2b_month,
        ]);
        $response = array(
            'status' => true,
            'message' => 'Rejected Successfully'
        );
        return json_encode($response);
    }
    public function getUnlinkedCdnr(Request $request){
        $credit_note = SalesReturn::select('sr_prefix','series_no','total','date','id','gstr2b_invoice_id')
            ->where('company_id', Session::get('user_company_id'))
            ->where('merchant_gst', $request->gstin)
            ->where('billing_gst', $request->ctin)
            ->where('voucher_type','PURCHASE')
            ->whereNull('gstr2b_invoice_id')
            ->orWhere('gstr2b_invoice_id',$request->invoice_no)
            ->orderBy('id', 'desc')
            ->get();
        $debit_note = PurchaseReturn::select('sr_prefix','series_no','total','date','id','gstr2b_invoice_id')
            ->where('company_id', Session::get('user_company_id'))
            ->where('merchant_gst', $request->gstin)
            ->where('billing_gst', $request->ctin)
            ->where('voucher_type','PURCHASE')
            ->whereNull('gstr2b_invoice_id')
            ->orWhere('gstr2b_invoice_id',$request->invoice_no)
            ->orderBy('id', 'desc')
            ->get();
        $response = array(
            'status' => true,
            'message' => 'Unlinked CDNRA fetched successfully',
            'credit_note' => $credit_note,
            'debit_note' => $debit_note
        );
        return json_encode($response);
    }
    public function linkCdnr(Request $request){
        if($request->type == "credit_note"){
            SalesReturn::where('gstr2b_invoice_id', $request->invoice_no)
                ->update(['gstr2b_invoice_id' =>null]);
            SalesReturn::whereIn('id', $request->ids)
                ->update(['gstr2b_invoice_id' => $request->invoice_no]);
        }else if($request->type == "debit_note"){
            PurchaseReturn::where('gstr2b_invoice_id', $request->invoice_no)
                ->update(['gstr2b_invoice_id' => null]);
            PurchaseReturn::whereIn('id', $request->ids)
                ->update(['gstr2b_invoice_id' => $request->invoice_no]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid type'
            ]);
        }
        $response = array(
            'status' => true,
            'message' => 'Linked Successfully',
        );
        return json_encode($response);
    }
    public function acceptGstr2bEntry(Request $request){        
        $rejectedEntry = RejectedGstr2b::find($request->id);
        if($rejectedEntry){
            $rejectedEntry->delete();
            $response = array(
                'status' => true,
                'message' => 'Accepted Successfully'
            );
        }else{
            $response = array(
                'status' => false,
                'message' => 'Entry not found'
            );
        }
        return json_encode($response);
    }
    
}
