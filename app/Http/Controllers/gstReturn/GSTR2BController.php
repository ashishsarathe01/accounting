<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GSTR2B;
use App\Models\Companies;
use App\Models\gstToken;
use App\Models\Accounts;
use App\Models\RejectedGstr2b; 
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Purchase;
use App\Models\Journal;
use App\Models\BillSundrys;
use App\Models\PurchaseSundry;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GSTR2BController extends Controller
{
    protected $gstCredentials;

    public function __construct()
    {
        $this->gstCredentials = json_decode(
            CommonHelper::gstApiCredentials('GST')
        );
    }
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
           
            //Gst Credenatial
            if(!$this->gstCredentials){
                $response = [
                                'success' => false,
                                'data'    => "",
                                'message' => "Api Credentails Not Found ",
                            ];
                return response()->json($response, 200);
            }
            if($this->gstCredentials->status != 1){
                $response = [
                                'success' => false,
                                'data'    => "",
                                'message' => "Api Credentails Not Found ",
                            ];
                return response()->json($response, 200);
            }
            $base_url = $this->gstCredentials->base_url;
            $email_id = $this->gstCredentials->email_id;
            $client_id = $this->gstCredentials->client_id;
            $client_secret = $this->gstCredentials->client_secret;
            $ip_address = $this->gstCredentials->ip_address;
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $base_url.'/gstr2b/all?email='.$email_id.'&gstin='.$request->gstin.'&rtnprd='.$month,
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
                'ip_address: '.$ip_address,
                'txn: '.$txn,
                'client_id: '.$client_id,
                'client_secret: '.$client_secret
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            // echo "<pre>";
            // print_r($result);die;
            if(isset($result->details)){
                if($result->details=='Request failed with status code 403'){
                    $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                    $response = array(
                        'status' => true,
                        'message' => "TOKEN-OTP",
                        'data' => ""
                    );
                    return json_encode($response);
                }else{
                    $response = array(
                        'status' => false,
                        'message' => $result->details,
                        'data' => ""
                    );
                    return json_encode($response);
                }
            }
            if(isset($result->status_cd) && $result->status_cd==0){
                echo json_encode(array("status"=>0,"message"=>$result->error->message));
                exit();
            }
           

            // echo "<pre>";
            // print_r($result);die;
            if(isset($result->data)){
                $GSTR2B = new GSTR2B;
                $GSTR2B->res_month = $request->month;
                $GSTR2B->res_data = json_encode($result->data);
                $GSTR2B->company_gstin = $request->gstin;
                $GSTR2B->company_id = Session::get('user_company_id');
                $GSTR2B->created_at = Carbon::now(); 
                $GSTR2B->save();   
                $response = array(
                    'status' => true,
                    'message' => 'SUCCESS',
                    'data' => ""
                );
                return json_encode($response);
            }else{
                $response = array(
                    'status' => false,
                    'message' => 'Data Not Found',
                    'data' => ""
                );
                return json_encode($response); 
            }                 
                            
        }else{
            $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->first(); 
            $verify_by_status = $gstr2b->verify_by_status;
            $verify_by = $gstr2b->verify_by;
            $verify_date = $gstr2b->verify_date;

            $gstr2b = json_decode($gstr2b->res_data);
            $uniqueSuppliers = [];$supplier_amount = [];$supplier_crdr_amount = [];
            $sections = ['b2b', 'cdnr', 'b2ba', 'cdnra'];
            $supplierFullyMatched = [];
            foreach ($sections as $section) {
                if (!empty($gstr2b->data->docdata->$section)) {
                    foreach ($gstr2b->data->docdata->$section as $record) {
                        if (!isset($uniqueSuppliers[$record->ctin])) {
                            $uniqueSuppliers[$record->ctin] = $record->trdnm;
                        }
                        if($section=="b2b" || $section=="cdnr" || $section=="b2ba" || $section=="cdnra"){
                            $invoice_amount = 0;$invoice_crdr_amount = 0;
                            if($section=="b2b"){
                                foreach ($record->inv as $invoice) {
                                    $invoice_amount += $invoice->val;
                                    //New Code Start
                                    $invoice_match_with = "";$invoice_match_with_id = "";
                                    $book_data = Purchase::select('total','id')
                                                    ->where('billing_gst', $record->ctin)
                                                    ->where('company_id', Session::get('user_company_id'))
                                                    ->where('merchant_gst', $request->gstin)
                                                    ->where('date', 'like', $request->month.'%')
                                                    ->where('status', '1')
                                                    ->where('delete', '0')
                                                    ->where(function($q) use ($invoice, $request) {
                                                        $q->where('voucher_no', $invoice->inum);
                                                        $q->orWhere('gstr2b_invoice_id', $invoice->inum); // add your OR here
                                                    })
                                                    ->first();

                                    $book_value = 0;
                                    if($book_data){
                                        $invoice_match_with = "PURCHASE";
                                        $invoice_match_with_id = $book_data->id;
                                        $book_value = $book_data->total;
                                    }else{
                                        $journal_book_data = Journal::select('total_amount','id')
                                                            ->where('vendor_gstin',$record->ctin)
                                                            ->where('company_id',Session::get('user_company_id'))
                                                            ->where('merchant_gst',$request->gstin)
                                                            ->where('claim_gst_status','YES')
                                                            ->where('invoice_no',$invoice->inum)
                                                            ->where('status','1')
                                                            ->where('delete','0')
                                                            ->first();
                                        if($journal_book_data){
                                            $invoice_match_with = "JOURNAL";
                                            $invoice_match_with_id = $journal_book_data->id;
                                            $book_value = $journal_book_data->total_amount;
                                        }
                                    }
                                    if($book_value==$invoice->val){
                                        if($invoice_match_with=="PURCHASE" && $invoice_match_with_id!=""){
                                            Purchase::where('id',$invoice_match_with_id)->update(["gstr2b_invoice_id"=>$invoice->inum,"gstr2b_invoice_month"=>$request->month]);
                                        }else if($invoice_match_with=="JOURNAL" && $invoice_match_with_id!=""){
                                            Journal::where('id',$invoice_match_with_id)->update(["gstr2b_invoice_id"=>$invoice->inum,"gstr2b_invoice_month"=>$request->month]);
                                        }
                                    }
                                    $book_igst_sum = 0; $book_cgst_sum = 0; $book_sgst_sum = 0; $book_taxable_val = 0;
                                    if($invoice_match_with == "PURCHASE" && $invoice_match_with_id != ""){
                                        $bill_sundry_igst_id = BillSundrys::where('company_id', Session::get('user_company_id'))->where('nature_of_sundry','IGST')->where('delete','0')->value('id');
                                        $bill_sundry_cgst_id = BillSundrys::where('company_id', Session::get('user_company_id'))->where('nature_of_sundry','CGST')->where('delete','0')->value('id');
                                        $bill_sundry_sgst_id = BillSundrys::where('company_id', Session::get('user_company_id'))->where('nature_of_sundry','SGST')->where('delete','0')->value('id');
                                        $book_igst_sum = PurchaseSundry::where('purchase_id',$invoice_match_with_id)->where('bill_sundry',$bill_sundry_igst_id)->sum('amount');
                                        $book_cgst_sum = PurchaseSundry::where('purchase_id',$invoice_match_with_id)->where('bill_sundry',$bill_sundry_cgst_id)->sum('amount');
                                        $book_sgst_sum = PurchaseSundry::where('purchase_id',$invoice_match_with_id)->where('bill_sundry',$bill_sundry_sgst_id)->sum('amount');
                                        $purch = Purchase::select('taxable_amt')->find($invoice_match_with_id);
                                        $book_taxable_val = $purch ? $purch->taxable_amt : 0;
                                    } else if($invoice_match_with == "JOURNAL" && $invoice_match_with_id != ""){
                                        $jrn = Journal::select('net_total','igst','cgst','sgst')->find($invoice_match_with_id);
                                        if($jrn){
                                            $book_taxable_val = $jrn->net_total;
                                            $book_igst_sum    = $jrn->igst;
                                            $book_cgst_sum    = $jrn->cgst;
                                            $book_sgst_sum    = $jrn->sgst;
                                        }
                                    }
                                    $taxableMatch = round($book_taxable_val,2) == round($invoice->txval,2);
                                    $igstMatch    = round($book_igst_sum,2)    == round($invoice->igst,2);
                                    $cgstMatch    = round($book_cgst_sum,2)    == round($invoice->cgst,2);
                                    $sgstMatch    = round($book_sgst_sum,2)    == round($invoice->sgst,2);
                                    $invoiceAllMatched = $taxableMatch && $igstMatch && $cgstMatch && $sgstMatch;
                                    if(!$invoiceAllMatched){
                                        $supplierFullyMatched[$record->ctin] = false;
                                    } else {
                                        if(!isset($supplierFullyMatched[$record->ctin])){
                                            $supplierFullyMatched[$record->ctin] = true;
                                        }
                                    }
                                }
                            }else if($section=="cdnr"){
                                foreach ($record->nt as $invoice) {
                                    if($invoice->typ == "C"){
                                        //$invoice_amount -= $invoice->val;
                                        $invoice_crdr_amount -= $invoice->val;
                                    }else if($invoice->typ == "D"){
                                        //$invoice_amount += $invoice->val;
                                        $invoice_crdr_amount += $invoice->val;
                                    }
                                }
                            }else if($section=="b2ba"){
                                foreach ($record->inv as $invoice) {
                                    $invoice_amount += $invoice->val;
                                }
                            }else if($section=="cdnra"){
                                foreach ($record->nt as $invoice) {
                                    if($invoice->typ == "C"){
                                        //$invoice_amount -= $invoice->val;
                                        $invoice_crdr_amount -= $invoice->val;
                                    }else if($invoice->typ == "D"){
                                        //$invoice_amount += $invoice->val;
                                        $invoice_crdr_amount += $invoice->val;
                                    }
                                }
                            }
                            if (!isset($supplier_amount[$record->ctin])) {
                                $supplier_amount[$record->ctin] = $invoice_amount;
                                $supplier_crdr_amount[$record->ctin] = $invoice_crdr_amount;
                                
                            }else{
                                $supplier_amount[$record->ctin] += $invoice_amount;
                                $supplier_crdr_amount[$record->ctin] += $invoice_crdr_amount;
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
                $supplier_amount[$ctin] = $supplier_amount[$ctin];
                if(!isset($supplier_crdr_amount[$ctin])){
                    $supplier_crdr_amount[$ctin] = 0;
                }
                $supplier_crdr_amount[$ctin] = $supplier_crdr_amount[$ctin]- $rejected_invoice_debit_note + $rejected_credit_note;
                //Book Value Calculation
                //Linked Invoice Data
                $purchase_book_data = Purchase::select('total')
                                        ->where('billing_gst',$ctin)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->where('date', 'like', $request->month.'%')
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->sum('total');
                $journal_book_data = Journal::select('total_amount')
                                        ->where('vendor_gstin',$ctin)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->where('claim_gst_status','YES')
                                        ->where('date', 'like', $request->month.'%')
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->sum('total_amount');
                $sale_return_book_data = SalesReturn::where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->where('billing_gst',$ctin)
                                        ->where('voucher_type','PURCHASE')
                                        ->where('sr_nature','WITH GST')
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->where('date', 'like', $request->month.'%')
                                        ->select('total')
                                        ->sum('total');
                $purchase_return_data = PurchaseReturn::where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->where('billing_gst',$ctin)
                                        ->where('voucher_type','PURCHASE')
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->where('date', 'like', $request->month.'%')
                                        ->where('sr_nature','WITH GST')
                                        ->select('total')
                                        ->sum('total');
                
                $book_value = $purchase_book_data + $journal_book_data + $sale_return_book_data - $purchase_return_data;
                $suppliers[] = (object)[
                    'ctin' => $ctin,
                    'trdnm' => $trdnm,
                    'amount' => isset($supplier_amount[$ctin]) ? $supplier_amount[$ctin] : 0,
                    'book_value' => $book_value,
                    'b2b_books'=>$purchase_book_data + $journal_book_data,
                    'b2b_portal'=>isset($supplier_amount[$ctin]) ? $supplier_amount[$ctin] : 0,
                    'cdnr_books'=>$sale_return_book_data - $purchase_return_data,
                    'cdnr_portal'=>isset($supplier_crdr_amount[$ctin]) ? $supplier_crdr_amount[$ctin] : 0,
                    'b2b_fully_matched' => isset($supplierFullyMatched[$ctin]) ? $supplierFullyMatched[$ctin] : false, // NEW
                ];
            }
            // Sort by trdnm (case-insensitive)
            usort($suppliers, function ($a, $b) {
                return strcasecmp($a->trdnm, $b->trdnm);
            });
            foreach ($suppliers as $ctin => $row) {

                $total_portal = $row->b2b_portal + $row->cdnr_portal;
                $total_books  = $row->b2b_books  + $row->cdnr_books;
            
                $suppliers[$ctin]->diff_amt = round(
                    $total_portal - $total_books,
                    2
                );
            }
            
            
            /*
            |--------------------------------------------------------------------------
            | ADD BOOK ONLY PARTIES HERE
            |--------------------------------------------------------------------------
            */
            
            $existingCtins = collect($suppliers)->pluck('ctin')->map(function($v){
                return strtoupper(trim($v));
            })->toArray();
            
            $bookParties = Purchase::select(
                    'billing_gst as ctin',
                    DB::raw('SUM(total) as b2b_books')
                )
                ->where('company_id', Session::get('user_company_id'))
                ->where('merchant_gst', $request->gstin)
                ->where('status', '1')
                ->where('delete', '0')
                ->where('date', 'like', $request->month.'%')
                ->groupBy('billing_gst')
                ->get();
            foreach($bookParties as $party){
                if(!in_array(strtoupper(trim($party->ctin)), $existingCtins)){   
                    if($party->ctin==""){
                        continue;
                    }
                    $account = Accounts::where('gstin', $party->ctin)
                                ->where('company_id', Session::get('user_company_id'))
                                ->first();
                    $suppliers[] = (object)[
                        'ctin'             => $party->ctin,
                        'trdnm'            => $account ? $account->account_name : $party->ctin,
                        'amount'           => 0,
                        'book_value'       => (float)$party->b2b_books,
                        'b2b_books'        => (float)$party->b2b_books,
                        'b2b_portal'       => 0,
                        'cdnr_books'       => 0,
                        'cdnr_portal'      => 0,
                        'diff_amt'         => -(float)$party->b2b_books,
                        'b2b_fully_matched'=> false, 
                    ];
                }
            }
            $bookJournals = Journal::select(
                    'vendor_gstin as ctin',
                    DB::raw('SUM(total_amount) as b2b_books')
                )
                ->where('company_id', Session::get('user_company_id'))
                ->where('merchant_gst', $request->gstin)
                ->where('status', '1')
                ->where('delete', '0')
                ->where('claim_gst_status','YES')
                ->where('date', 'like', $request->month.'%')
                ->groupBy('vendor_gstin')
                ->get();
            foreach($bookJournals as $party){
                if(!in_array(strtoupper(trim($party->ctin)), $existingCtins)){
                    if($party->ctin==""){
                        continue;
                    }
                    $account = Accounts::where('gstin', $party->ctin)
                                ->where('company_id', Session::get('user_company_id'))
                                ->first();
                    $suppliers[] = (object)[
                        'ctin'             => $party->ctin,
                        'trdnm'            => $account ? $account->account_name : $party->ctin,
                        'amount'           => 0,
                        'book_value'       => (float)$party->b2b_books,
                        'b2b_books'        => (float)$party->b2b_books,
                        'b2b_portal'       => 0,
                        'cdnr_books'       => 0,
                        'cdnr_portal'      => 0,
                        'diff_amt'         => -(float)$party->b2b_books,
                        'b2b_fully_matched'=> false, // NEW: only in book = never fully matched
                    ];
                }
            }
            //Pending Notes
            $pending_notes = [];
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
            $sr = 1;
            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);
            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');
            $fy_start = $from . '-04-01';
            $month_end = date('Y-m-t', strtotime($request->month));
            $debit = PurchaseReturn::whereNull('purchase_returns.gstr2b_invoice_id')
                                    ->leftJoin('accounts', 'accounts.id', '=', 'purchase_returns.party')
                                    ->leftJoin('purchase_return_sundries as igst', function ($join) use ($bill_sundry_igst) {
                                        $join->on('purchase_returns.id', '=', 'igst.purchase_return_id')
                                            ->where('igst.bill_sundry', $bill_sundry_igst);
                                    })

                                    ->leftJoin('purchase_return_sundries as cgst', function ($join) use ($bill_sundry_cgst) {
                                        $join->on('purchase_returns.id', '=', 'cgst.purchase_return_id')
                                            ->where('cgst.bill_sundry', $bill_sundry_cgst);
                                    })

                                    ->leftJoin('purchase_return_sundries as sgst', function ($join) use ($bill_sundry_sgst) {
                                        $join->on('purchase_returns.id', '=', 'sgst.purchase_return_id')
                                            ->where('sgst.bill_sundry', $bill_sundry_sgst);
                                    })
                                    ->where('purchase_returns.company_id', Session::get('user_company_id'))
                                    ->where('purchase_returns.merchant_gst', $request->gstin)
                                    ->whereBetween('purchase_returns.date', [$fy_start, $month_end])
                                    ->where('purchase_returns.voucher_type', 'PURCHASE')
                                    ->where('purchase_returns.sr_nature', 'WITH GST')
                                    ->where('purchase_returns.delete','0')
                                    ->where('purchase_returns.status','1')
                                    ->select(
                                        'purchase_returns.*',
                                        'accounts.account_name as party_name',
                                        DB::raw('IFNULL(igst.amount,0) as igst_amount'),
                                        DB::raw('IFNULL(cgst.amount,0) as cgst_amount'),
                                        DB::raw('IFNULL(sgst.amount,0) as sgst_amount')
                                    )
                                    ->get();
            foreach($debit as $d){
                $pending_notes[] = [
                    'sr_no' => $sr++,
                    'party' => $d->party_name ?? '',
                    'type' => 'DR',
                    'invoice_no' => $d->sr_prefix ?? $d->voucher_no ?? '-',
                    'date' => date('d-m-Y', strtotime($d->date)),
                    'book_value' => $d->total,
                    'taxable' => $d->taxable_amt,
                    'igst' => $d->igst_amount,
                    'cgst' => $d->cgst_amount,
                    'sgst' => $d->sgst_amount,
                    'cess' => 0
                ];
            }
            $credit = SalesReturn::whereNull('sales_returns.gstr2b_invoice_id')
                                ->leftJoin('accounts', 'accounts.id', '=', 'sales_returns.party')
                                ->leftJoin('sale_return_sundries as igst', function ($join) use ($bill_sundry_igst) {
                                    $join->on('sales_returns.id', '=', 'igst.sale_return_id')
                                        ->where('igst.bill_sundry', $bill_sundry_igst);
                                })
                                ->leftJoin('sale_return_sundries as cgst', function ($join) use ($bill_sundry_cgst) {
                                    $join->on('sales_returns.id', '=', 'cgst.sale_return_id')
                                        ->where('cgst.bill_sundry', $bill_sundry_cgst);
                                })

                                ->leftJoin('sale_return_sundries as sgst', function ($join) use ($bill_sundry_sgst) {
                                    $join->on('sales_returns.id', '=', 'sgst.sale_return_id')
                                        ->where('sgst.bill_sundry', $bill_sundry_sgst);
                                })
                                ->where('sales_returns.company_id', Session::get('user_company_id'))
                                ->where('sales_returns.merchant_gst', $request->gstin)
                                ->whereBetween('sales_returns.date', [$fy_start, $month_end])
                                ->where('sales_returns.voucher_type', 'PURCHASE')
                                ->where('sales_returns.sr_nature', 'WITH GST')
                                ->where('sales_returns.delete','0')
                                ->where('sales_returns.status','1')
                                ->select(
                                    'sales_returns.*',
                                    'accounts.account_name as party_name',
                                    DB::raw('IFNULL(igst.amount,0) as igst_amount'),
                                    DB::raw('IFNULL(cgst.amount,0) as cgst_amount'),
                                    DB::raw('IFNULL(sgst.amount,0) as sgst_amount')
                                )
                                ->get();
            foreach($credit as $c){
                $pending_notes[] = [
                    'sr_no' => $sr++,
                    'party' => $c->party_name ?? '',
                    'type' => 'CR',
                    'invoice_no' => $c->sr_prefix ?? $c->voucher_no ?? '-',
                    'date' => date('d-m-Y', strtotime($c->date)),
                    'book_value' => $c->total,
                    'taxable' => $c->taxable_amt,
                    'igst' => $c->igst_amount,
                    'cgst' => $c->cgst_amount,
                    'sgst' => $c->sgst_amount,
                    'cess' => 0
                ];
            }
            //Pending Invoice
            $sr_inv = 1;
            $pending_invoice = [];
            $purchase_invoices = Purchase::whereNull('purchases.gstr2b_invoice_id')
                                    ->leftJoin('accounts', 'accounts.id', '=', 'purchases.party')
                                    ->leftJoin('purchase_sundries  as igst', function ($join) use ($bill_sundry_igst) {
                                        $join->on('purchases.id', '=', 'igst.purchase_id')
                                            ->where('igst.bill_sundry', $bill_sundry_igst);
                                    })

                                    ->leftJoin('purchase_sundries as cgst', function ($join) use ($bill_sundry_cgst) {
                                        $join->on('purchases.id', '=', 'cgst.purchase_id')
                                            ->where('cgst.bill_sundry', $bill_sundry_cgst);
                                    })

                                    ->leftJoin('purchase_sundries as sgst', function ($join) use ($bill_sundry_sgst) {
                                        $join->on('purchases.id', '=', 'sgst.purchase_id')
                                            ->where('sgst.bill_sundry', $bill_sundry_sgst);
                                    })
                                    ->where('purchases.company_id', Session::get('user_company_id'))
                                    ->where('purchases.merchant_gst', $request->gstin)
                                    ->whereBetween('purchases.date', [$fy_start, $month_end])
                                    ->where('purchases.delete','0')
                                    ->where('purchases.status','1')
                                    ->select(
                                        'purchases.*',
                                        'accounts.account_name as party_name',
                                        DB::raw('IFNULL(igst.amount,0) as igst_amount'),
                                        DB::raw('IFNULL(cgst.amount,0) as cgst_amount'),
                                        DB::raw('IFNULL(sgst.amount,0) as sgst_amount')
                                    )
                                    ->get();
            foreach($purchase_invoices as $d){
                $pending_invoice[] = [
                    'sr_no' => $sr_inv++,
                    'party' => $d->party_name ?? '',
                    'type' => 'PURCHASE',
                    'invoice_no' => $d->voucher_no ?? '-',
                    'date' => date('d-m-Y', strtotime($d->date)),
                    'book_value' => $d->total,
                    'taxable' => $d->taxable_amt,
                    'igst' => $d->igst_amount,
                    'cgst' => $d->cgst_amount,
                    'sgst' => $d->sgst_amount,
                    'cess' => 0
                ];
            }
            $journal_invoices = Journal::whereNull('journals.gstr2b_invoice_id')
                                    ->leftJoin('accounts', 'accounts.id', '=', 'journals.vendor')
                                    ->where('journals.company_id', Session::get('user_company_id'))
                                    ->where('journals.merchant_gst', $request->gstin)
                                    ->whereBetween('journals.date', [$fy_start, $month_end])
                                    ->where('journals.delete','0')
                                    ->where('journals.status','1')
                                    ->where('journals.claim_gst_status','YES')
                                    ->select(
                                        'journals.*',
                                        'accounts.account_name as party_name',
                                        DB::raw('IFNULL(journals.igst,0) as igst_amount'),
                                        DB::raw('IFNULL(journals.cgst,0) as cgst_amount'),
                                        DB::raw('IFNULL(journals.sgst,0) as sgst_amount')
                                    )
                                    ->get();
            foreach($journal_invoices as $d){
                $pending_invoice[] = [
                    'sr_no' => $sr_inv++,
                    'party' => $d->party_name ?? '',
                    'type' => 'JOURNAL',
                    'invoice_no' => $d->voucher_no_prefix ?? '-',
                    'date' => date('d-m-Y', strtotime($d->date)),
                    'book_value' => $d->total_amount,
                    'taxable' => $d->net_total,
                    'igst' => $d->igst_amount,
                    'cgst' => $d->cgst_amount,
                    'sgst' => $d->sgst_amount,
                    'cess' => 0
                ];
            }
            if($verify_by!=""){
                $user = DB::table('users')
                        ->select('name')
                        ->where('id',$verify_by)
                        ->first();
                if($user){
                    $verify_by = $user->name;
                }
            }
            if($verify_date!=""){
                $verify_date = date('d-m-Y H:i:s',strtotime($verify_date));
            }
            //Tax Summary Book Data
            //Purchase Data
            $bill_sundry = DB::table('bill_sundrys')
                            ->whereIn('nature_of_sundry',['CGST','SGST','IGST'])
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('status','1')
                            ->where('delete','0')
                            ->pluck('nature_of_sundry','id');
            $purchase_ids = DB::table('purchases')
                            ->where('merchant_gst', $request->gstin)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('date', 'like', $request->month.'%')
                            ->where('delete', '0')
                            ->where('status', '1')
                            ->pluck('id');
            $purchase_sundry = DB::table('purchase_sundries')
                                ->select('bill_sundry', DB::raw('SUM(amount) as total_amount'))
                                ->whereIn('purchase_id', $purchase_ids)
                                ->groupBy('bill_sundry')
                                ->get();
            
            $purchase_arr = [];
            foreach($purchase_sundry as $sundry){
                if(isset($bill_sundry[$sundry->bill_sundry])){
                    $purchase_arr[$bill_sundry[$sundry->bill_sundry]] = $sundry->total_amount;
                }
            }
            //Credit Note
            $sale_return_ids = DB::table('sales_returns')
                            ->where('merchant_gst', $request->gstin)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('date', 'like', $request->month.'%')
                            ->where('delete', '0')
                            ->where('status', '1')
                            ->where('voucher_type', 'PURCHASE')
                            ->where('sr_nature', 'WITH GST')                            
                            ->pluck('id');
            $sale_return_sundry = DB::table('sale_return_sundries')
                                ->select('bill_sundry', DB::raw('SUM(amount) as total_amount'))
                                ->whereIn('sale_return_id', $sale_return_ids)
                                ->groupBy('bill_sundry')
                                ->get();
            
            $sale_return_arr = [];
            foreach($sale_return_sundry as $sundry){
                if(isset($bill_sundry[$sundry->bill_sundry])){
                    $sale_return_arr[$bill_sundry[$sundry->bill_sundry]] = $sundry->total_amount;
                }
            }
            
            //Debit Note
            $purchase_return_ids = DB::table('purchase_returns')
                            ->where('merchant_gst', $request->gstin)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('date', 'like', $request->month.'%')
                            ->where('delete', '0')
                            ->where('status', '1')
                            ->where('voucher_type', 'PURCHASE')
                            ->where('sr_nature', 'WITH GST')
                            
                            ->pluck('id');
            $purchase_return_sundry = DB::table('purchase_return_sundries')
                                ->select('bill_sundry', DB::raw('SUM(amount) as total_amount'))
                                ->whereIn('purchase_return_id', $purchase_return_ids)
                                ->groupBy('bill_sundry')
                                ->get();
            
            $purchase_return_arr = [];
            foreach($purchase_return_sundry as $sundry){
                if(isset($bill_sundry[$sundry->bill_sundry])){
                    $purchase_return_arr[$bill_sundry[$sundry->bill_sundry]] = $sundry->total_amount;
                }
            }
            //Journal Data
            $journal_ids = DB::table('journals')
                                ->where('merchant_gst', $request->gstin)
                                ->where('company_id', Session::get('user_company_id'))
                                ->where('claim_gst_status', 'YES')
                                ->where('date', 'like', $request->month.'%')
                                ->where('delete', '0')
                                ->where('status', '1')
                                ->selectRaw('
                                    SUM(cgst) as total_cgst,
                                    SUM(sgst) as total_sgst,
                                    SUM(igst) as total_igst
                                ')
                                ->first();
                                
            $journal_arr = [];
            foreach ($bill_sundry as $key => $value) {
                if ($value == 'CGST') {
                    $journal_arr[$value] = $journal_ids->total_cgst;
                } elseif ($value == 'SGST') {
                    $journal_arr[$value] = $journal_ids->total_sgst;
                } elseif ($value == 'IGST') {
                    $journal_arr[$value] = $journal_ids->total_igst;
                }
            }
            $itcBookData = [];
            $tax_arr = ['CGST','SGST','IGST'];
            foreach ($tax_arr as $key => $value) {
                $itcBookData[$value] =
                    ($purchase_arr[$value] ?? 0) +
                    ($journal_arr[$value] ?? 0) +
                    ($sale_return_arr[$value] ?? 0) -
                    ($purchase_return_arr[$value] ?? 0);
            }
            
            $base_url = $this->gstCredentials->base_url;
            $email_id = $this->gstCredentials->email_id;
            $client_id = $this->gstCredentials->client_id;
            $client_secret = $this->gstCredentials->client_secret;
            $ip_address = $this->gstCredentials->ip_address;
            $url = $base_url."/gstr3b/retsum";
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ])->get($url, [
                'gstin' => $request->gstin,
                'ret_period' => $month,
                'email' => $email_id,
            ]);
            $data = $response->json();
            if(!isset($data['data'])){
                $data['data'] = [];
            }
            $response = array(
                'status' => true,
                'message' => 'GSTR2B',
                'pending_notes' => $pending_notes ?? [],
                'pending_invoice' => $pending_invoice ?? [],
                'data' => $suppliers,
                'verify_status' => $verify_by_status,
                'verify_date' => $verify_date,
                'verify_by' => $verify_by,
                'itcApiData' => $data['data'],
                'itcBookData' => $itcBookData,
            );
            return json_encode($response);
        }
    }
    public function gstr2bAllInfo(Request $request){
        $account = Accounts::select('account_name')
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstin',$request->ctin)
                            ->first();
       $account_name = '';
        if ($account) {
            // ✅ Found in DB
            $account_name = $account->account_name;
        } else {
            //Gst Credenatial
            if(!$this->gstCredentials){
                $response = [
                                'success' => false,
                                'data'    => "",
                                'message' => "Api Credentails Not Found ",
                            ];
                return response()->json($response, 200);
            }
            if($this->gstCredentials->status != 1){
                $response = [
                                'success' => false,
                                'data'    => "",
                                'message' => "Api Credentails Not Found ",
                            ];
                return response()->json($response, 200);
            }
            $base_url = $this->gstCredentials->base_url;
            $email_id = $this->gstCredentials->email_id;
            $client_id = $this->gstCredentials->client_id;
            $client_secret = $this->gstCredentials->client_secret;
            $ip_address = $this->gstCredentials->ip_address;
            // ❌ Not found → Fetch from GST API

            
                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => $base_url."/public/search?email={$email_id}&gstin={$request->ctin}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => [
                        'client_id: '.$client_id,
                        'client_secret: '.$client_secret
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
        $b2b_invoices_matched = "";$total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0; $total_book_value = 0;

        $b2b_invoices_on_portal_but_not_in_book = "";$total_val_on_portal_but_not_in_book = 0; $total_txval_on_portal_but_not_in_book = 0; $total_igst_on_portal_but_not_in_book = 0; $total_cgst_on_portal_but_not_in_book = 0; $total_sgst_on_portal_but_not_in_book = 0; $total_cess_on_portal_but_not_in_book = 0; $total_book_value_on_portal_but_not_in_book = 0;

        //Only in books but not portal code
        $book_vouchers = Purchase::select('voucher_no')
            ->where('billing_gst', $request->ctin)
            ->where('company_id', Session::get('user_company_id'))
            ->where('merchant_gst', $request->gstin)
            ->where('status', '1')
            ->where('date', 'like', $request->month.'%')
            ->where('delete', '0')
            ->where(function($q) {
                $q->whereNull('gstr2b_invoice_id')
                ->orWhere('gstr2b_invoice_id', '');
            })
            ->pluck('voucher_no')
            ->toArray();



        $journal_vouchers = Journal::select('voucher_no')
            ->where('vendor_gstin', $request->ctin)
            ->where('company_id', Session::get('user_company_id'))
            ->where('merchant_gst', $request->gstin)
            ->where('claim_gst_status', 'YES')
            ->where('status', '1')
            ->where('delete', '0')
            ->where('date', 'like', $request->month.'%')
            ->where(function($q) {
                $q->whereNull('gstr2b_invoice_id')
                ->orWhere('gstr2b_invoice_id', '');
            })
            ->pluck('voucher_no')
            ->toArray();



        $book_vouchers = array_unique(
            array_merge($book_vouchers, $journal_vouchers)
        );
        $portal_vouchers = [];
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

        foreach ($gstr2b->data->docdata->b2b as $record) {
            
            if($record->ctin === $request->ctin) {
                foreach ($record->inv as $key=>$invoice) {
                    $portal_vouchers[] = $invoice->inum;
                    if (in_array($invoice->inum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                   
                    $invoice_match_with = "";$invoice_match_with_id = "";
                    $book_data = Purchase::select('total','id','taxable_amt')
                                            ->where('billing_gst', $request->ctin)
                                            ->where('company_id', Session::get('user_company_id'))
                                            ->where('merchant_gst', $request->gstin)
                                            ->where('date', 'like', $request->month.'%')
                                            ->where('status', '1')
                                            ->where('delete', '0')
                                            ->where(function($q) use ($invoice, $request) {
                                                $q->where('voucher_no', $invoice->inum);
                                                $q->orWhere('gstr2b_invoice_id', $invoice->inum); // add your OR here
                                            })
                                            ->first();

                    $book_value = 0;
                    $book_taxable = 0;
                    $book_igst = 0;
                    $book_cgst = 0;
                    $book_sgst = 0;
                    if($book_data){
                        $invoice_match_with = "PURCHASE";
                        $invoice_match_with_id = $book_data->id;
                        $book_value = $book_data->total;
                        $book_taxable = $book_data->taxable_amt;
                        $book_igst += PurchaseSundry::where('purchase_id', $book_data->id)
                                        ->where('bill_sundry', $bill_sundry_igst)
                                        ->sum('amount');
                        $book_cgst += PurchaseSundry::where('purchase_id', $book_data->id)
                                        ->where('bill_sundry', $bill_sundry_cgst)
                                        ->sum('amount');
                        $book_sgst += PurchaseSundry::where('purchase_id', $book_data->id)
                                        ->where('bill_sundry', $bill_sundry_sgst)
                                        ->sum('amount');
                    }else{
                        $journal_book_data = Journal::select('total_amount','id','net_total','igst','cgst','sgst')
                                            ->where('vendor_gstin',$request->ctin)
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->where('claim_gst_status','YES')
                                            ->where('invoice_no',$invoice->inum)
                                            ->where('status','1')
                                            ->where('delete','0')
                                            ->first();
                        if($journal_book_data){
                            $invoice_match_with = "JOURNAL";
                            $invoice_match_with_id = $journal_book_data->id;
                            $book_value = $journal_book_data->total_amount;
                            $book_taxable = $journal_book_data->net_total;
                            $book_igst = $journal_book_data->igst;
                            $book_cgst = $journal_book_data->cgst;
                            $book_sgst = $journal_book_data->sgst;
                        }
                    }
                    $edit_btn = '';
                    if($invoice_match_with=="PURCHASE" && $invoice_match_with_id!=""){
                        $edit_btn = "<a href='".url('purchase-edit/'.$invoice_match_with_id)."'
                                        class='btn btn-warning btn-sm'
                                        style='padding:0.2rem 0.4rem;font-size:0.75rem;line-height:1.2;border-radius:0.2rem;'>
                                        Edit
                                    </a>";
                    } elseif($invoice_match_with=="JOURNAL" && $invoice_match_with_id!=""){
                        $edit_btn = "<a href='".url('journal/'.$invoice_match_with_id.'/edit')."'
                                        class='btn btn-warning btn-sm'
                                        style='padding:0.2rem 0.4rem;font-size:0.75rem;line-height:1.2;border-radius:0.2rem;'>
                                        Edit
                                    </a>";
                    }
                    $taxableMatch = round($book_taxable,2) == round($invoice->txval,2);
                    $igstMatch    = round($book_igst,2)    == round($invoice->igst,2);
                    $cgstMatch    = round($book_cgst,2)    == round($invoice->cgst,2);
                    $sgstMatch    = round($book_sgst,2)    == round($invoice->sgst,2);
                    $allMatched = $taxableMatch && $igstMatch && $cgstMatch && $sgstMatch;
                    if ($allMatched) {
                        $taxableStyle = "color:green;font-weight:bold;";
                        $igstStyle    = "color:green;font-weight:bold;";
                        $cgstStyle    = "color:green;font-weight:bold;";
                        $sgstStyle    = "color:green;font-weight:bold;";
                    } else {
                        $taxableStyle = $taxableMatch ? "" : "color:red;font-weight:bold;";
                        $igstStyle    = $igstMatch ? "" : "color:red;font-weight:bold;";
                        $cgstStyle    = $cgstMatch ? "" : "color:red;font-weight:bold;";
                        $sgstStyle    = $sgstMatch ? "" : "color:red;font-weight:bold;";
                    }
                    $style = "";
                    if(abs($book_value - $invoice->val) >= 1){
                        $style = "color: red;";
                        $total_val_on_portal_but_not_in_book += $invoice->val;
                        $total_txval_on_portal_but_not_in_book += $invoice->txval;
                        $total_igst_on_portal_but_not_in_book += $invoice->igst;
                        $total_cgst_on_portal_but_not_in_book += $invoice->cgst;
                        $total_sgst_on_portal_but_not_in_book += $invoice->sgst;
                        $total_cess_on_portal_but_not_in_book += $invoice->cess;
                        $total_book_value_on_portal_but_not_in_book += $book_value;
                    }else{
                        $total_val += $invoice->val;
                        $total_txval += $invoice->txval;
                        $total_igst += $invoice->igst;
                        $total_cgst += $invoice->cgst;
                        $total_sgst += $invoice->sgst;
                        $total_cess += $invoice->cess;
                        $total_book_value += $book_value;
                    }                    
                    if(!isset($invoice->irn)){
                        $invoice->irn = "";
                    }                    
                   if(abs($book_value - $invoice->val) >= 1){
                        $b2b_invoices_on_portal_but_not_in_book.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_invoices_rej_btn_'></td>
                            <td>".$invoice->inum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right;{$taxableStyle}'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right;{$igstStyle}'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right;{$cgstStyle}'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right;{$sgstStyle}'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td>".$edit_btn."
                            <button class='btn btn-danger reject_btn' data-type='b2b_invoices' data-invoice='".$invoice->inum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='".$invoice->irn."' id='b2b_invoices_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> <button class='btn btn-success link_invoice_btn b2b_invoices_rej_btn_".$key."' data-type='b2b_invoices' data-invoice='".$invoice->inum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-ctin='".$request->ctin."' data-gstin='".$request->gstin."' data-month='".$request->month."'  style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Link</button></td>
                        </tr>";
                    }else{
                        if($invoice_match_with=="PURCHASE" && $invoice_match_with_id!=""){
                            Purchase::where('id',$invoice_match_with_id)->update(["gstr2b_invoice_id"=>$invoice->inum,"gstr2b_invoice_month"=>$request->month]);
                        }else if($invoice_match_with=="JOURNAL" && $invoice_match_with_id!=""){
                            Journal::where('id',$invoice_match_with_id)->update(["gstr2b_invoice_id"=>$invoice->inum,"gstr2b_invoice_month"=>$request->month]);
                        }
                        $b2b_invoices_matched.="<tr>
                            <td><input type='checkbox' checked class='check_action' data-key='".$key."' data-type='b2b_invoices_rej_btn_'></td>
                            <td>".$invoice->inum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                            <td style='text-align: right;{$taxableStyle}'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right;{$igstStyle}'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right;{$cgstStyle}'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right;{$sgstStyle}'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                            <td>".$edit_btn."
                            <button class='btn btn-danger reject_btn' data-type='b2b_invoices' data-invoice='".$invoice->inum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='".$invoice->irn."' id='b2b_invoices_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button></td>
                        </tr>";
                    }                    
                }
                
                if(abs($book_value - $invoice->val) >= 1){
                    $b2b_invoices_on_portal_but_not_in_book .= "<tr>
                        <td></td>
                        <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                        <th style='text-align: right'>".formatIndianNumber($total_val_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_book_value_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_txval_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_igst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cgst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_sgst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cess_on_portal_but_not_in_book)."</th>
                        <td></td>
                    </tr>";
                }else{
                    $b2b_invoices_matched .= "<tr>
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
                }
                
                //Rejected GSTR2B
                $rejected_total = 0;
                foreach ($RejectedGstr2b as $k => $v) {
                    if($v->type == "b2b_invoices"){
                        if($rejected_total==0){
                            $b2b_invoices_matched.="<tr><td colspan='11' style='text-align:center;color:red'><strong>Rejected Invoice</strong></td></tr>";
                        }
                        $rejected_total = $rejected_total + $v->total_amount;
                        $b2b_invoices_matched.="<tr>
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
                            <td><strong style='color:red'>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
                        </tr>";
                    }
                }
                if($rejected_total>0){
                    $b2b_invoices_matched.="<tr >
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
                 // Stop after first match
            }
        }
      
        $portal_upper = array_map('strtoupper', $portal_vouchers);

        $only_in_book_voucher = array_filter($book_vouchers, function($val) use ($portal_upper) {
                                    return !in_array(strtoupper($val), $portal_upper);
                                });
                                
        $total_book_value_only_in_book = 0; $total_val_only_in_book = 0; $total_txval_only_in_book = 0; $total_igst_only_in_book = 0; $total_cgst_only_in_book = 0; $total_sgst_only_in_book = 0; $total_cess_only_in_book = 0;
        $b2b_invoices_only_in_book = "";

        foreach ($only_in_book_voucher as $key => $invoice_no) {

            $book_data = Purchase::with(['purchaseSundry'])
                ->where('voucher_no', $invoice_no)
                ->where('billing_gst', $request->ctin)
                ->where('company_id', Session::get('user_company_id'))
                ->where('merchant_gst', $request->gstin)
                ->where('status', '1')
                ->where('delete', '0')
                ->first();

            $is_journal = false;

            // IF NOT FOUND IN PURCHASE THEN SEARCH IN JOURNAL
            if (!$book_data) {

                
                    $book_data = Journal::where('vendor_gstin', $request->ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('merchant_gst', $request->gstin)
                    ->where('claim_gst_status', 'YES')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->first();   
                    

                $is_journal = true;
            }

            // SKIP IF STILL NOT FOUND
            if (!$book_data) {
                continue;
            }

            $book_value  = 0;
            $igst_amount = 0;
            $cgst_amount = 0;
            $sgst_amount = 0;
            $taxable_amt = 0;

            if ($is_journal) {

                $book_value  = (float)$book_data->total_amount;
                $taxable_amt = (float)$book_data->net_total;

                $total_book_value_only_in_book += $book_value;
                $total_val_only_in_book += $book_value;
                $total_txval_only_in_book += $taxable_amt;

                $igst_amount = (float)$book_data->igst;
                $cgst_amount = (float)$book_data->cgst;
                $sgst_amount = (float)$book_data->sgst;

                $total_igst_only_in_book += $igst_amount;
                $total_cgst_only_in_book += $cgst_amount;
                $total_sgst_only_in_book += $sgst_amount;

            } else {

                $book_value = (float)$book_data->total;
                $taxable_amt = (float)$book_data->taxable_amt;

                $total_book_value_only_in_book += $book_value;
                $total_val_only_in_book += $book_value;
                $total_txval_only_in_book += $taxable_amt;

                if ($book_data->purchaseSundry && count($book_data->purchaseSundry) > 0) {

                    foreach ($book_data->purchaseSundry as $sundry) {

                        if ($sundry->nature_of_sundry == "CGST") {

                            $cgst_amount = (float)$sundry->amount;
                            $total_cgst_only_in_book += $cgst_amount;

                        } else if ($sundry->nature_of_sundry == "SGST") {

                            $sgst_amount = (float)$sundry->amount;
                            $total_sgst_only_in_book += $sgst_amount;

                        } else if ($sundry->nature_of_sundry == "IGST") {

                            $igst_amount = (float)$sundry->amount;
                            $total_igst_only_in_book += $igst_amount;
                        }
                    }
                }
            }

            $edit_btn = '';
            if ($is_journal) {
                $edit_btn = "<a href='".url('journal/'.$book_data->id.'/edit')."'
                                class='btn btn-warning btn-sm'
                                style='padding:0.2rem 0.4rem;font-size:0.75rem;'>
                                Edit
                            </a>";
            } else {
                $edit_btn = "<a href='".url('purchase-edit/'.$book_data->id)."'
                                class='btn btn-warning btn-sm'
                                style='padding:0.2rem 0.4rem;font-size:0.75rem;'>
                                Edit
                            </a>";
            }
            $style = "";
            $b2b_invoices_only_in_book .= "
            <tr>
                <td></td>
                <td>".$invoice_no."</td>
                <td>".(!empty($book_data->date) ? date('d-m-Y', strtotime($book_data->date)) : '')."</td>
                <td style='text-align: right;".$style."'>0</td>
                <td style='text-align: right;".$style."'>".formatIndianNumber($book_value)."</td>
                <td style='text-align: right'>".formatIndianNumber($taxable_amt)."</td>
                <td style='text-align: right'>".formatIndianNumber($igst_amount)."</td>
                <td style='text-align: right'>".formatIndianNumber($cgst_amount)."</td>
                <td style='text-align: right'>".formatIndianNumber($sgst_amount)."</td>
                <td style='text-align: right'>0.00</td>
                <td>".$edit_btn."</td>
            </tr>";
        }

        if($total_book_value_only_in_book > 0){

            $b2b_invoices_only_in_book .= "
            <tr>
                <td></td>
                <td></td>
                <th style='text-align: right'>Total</th>

                <th style='text-align: right'>
                0 
                </th>

                <th style='text-align: right'>
                ".formatIndianNumber($total_val_only_in_book)."
                </th>

                <th style='text-align: right'>
                    ".formatIndianNumber($total_txval_only_in_book)."
                </th>

                <th style='text-align: right'>
                    ".formatIndianNumber($total_igst_only_in_book)."
                </th>

                <th style='text-align: right'>
                    ".formatIndianNumber($total_cgst_only_in_book)."
                </th>

                <th style='text-align: right'>
                    ".formatIndianNumber($total_sgst_only_in_book)."
                </th>

                <th style='text-align: right'>
                    ".formatIndianNumber($total_cess_only_in_book)."
                </th>

                <td></td>
            </tr>";
        }

        $b2b_debit_note = "";$b2b_credit_note = "";
        $total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;$total_book_value = 0;
        $debit_total_val = 0; $debit_total_txval = 0; $debit_total_igst = 0; $debit_total_cgst = 0; $debit_total_sgst = 0; $debit_total_cess = 0; $total_debit_book_value = 0;
        if (isset($gstr2b->data->docdata->cdnr) && is_array($gstr2b->data->docdata->cdnr)) {
        foreach ($gstr2b->data->docdata->cdnr as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->nt as $key=>$invoice) {
                    if (in_array($invoice->ntnum, $rejectedIrns)) {
                       continue; // Skip rejected invoices
                    }
                    if($invoice->typ == "C"){
                        $purchaseReturn = PurchaseReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->first();
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
                        $edit_btn = '';
                        if($purchaseReturn){
                            $edit_btn = "<a href='".url('purchase-return-edit/'.$purchaseReturn->id)."'
                                            class='btn btn-warning'
                                            style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;margin-right:2px;'>
                                            Edit
                                        </a>";
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
                            <td>".$edit_btn."
                            <button class='btn btn-danger reject_btn' data-type='b2b_credit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2b_credit_rej_btn_".$key."'  style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
                        </tr>";
                    }else if($invoice->typ == "D"){
                        $salesReturn = SalesReturn::where('gstr2b_invoice_id',$invoice->ntnum)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->first();
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
                        $edit_btn = '';
                        if($salesReturn){
                            $edit_btn = "<a href='".url('sale-return-edit/'.$salesReturn->id)."'
                                            class='btn btn-warning'
                                            style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;margin-right:2px;'>
                                            Edit
                                        </a>";
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
                            <td>".$edit_btn."
                            <button class='btn btn-danger reject_btn' data-type='b2b_debit_note' data-invoice='".$invoice->ntnum."' data-date='".$invoice->dt."' data-total_amount='".$invoice->val."' data-taxable_amount='".$invoice->txval."' data-igst='".$invoice->igst."' data-cgst='".$invoice->cgst."' data-sgst='".$invoice->sgst."' data-cess='".$invoice->cess."' data-irn='' id='b2b_debit_rej_btn_".$key."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;display:none'>Reject</button> ".$link_btn."</td>
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
                            <td><strong style='color:red'>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
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
                            <td><strong style='color:red'>Rejected - ".($v->remark)." </strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
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
        }
        
        
        
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
                                                                        'sales_returns.id',
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
                                                                            'purchase_returns.id',
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
                                                <td>".$v->date."</td>
                                                <td style='text-align: right;".$style."'>".formatIndianNumber($v->total)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->taxable_amt)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->igst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->cgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->sgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber(0)."</td>
                                                <td>
                                                    <a href='".url('purchase-return-edit/'.$v->id)."'
                                                    class='btn btn-warning btn-sm'
                                                    style='padding:0.2rem 0.4rem;font-size:0.75rem;'>
                                                    Edit
                                                    </a>
                                                </td>
                                            </tr>";
                }
                
                $b2b_debit_note_unlinked .= "<tr style='font-weight:bold;background:#f3f6fa'>
                                                <td colspan='4' style='text-align:right'>TOTAL</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_txval_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_igst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_cgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_sgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_cess_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($total_book_value_unlink)."</td>
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
                                                <td>".$v->date."</td>
                                                <td style='text-align: right;".$style."'>".formatIndianNumber($v->total)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->taxable_amt)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->igst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->cgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber($v->sgst_amount)."</td>
                                                <td style='text-align: right'>".formatIndianNumber(0)."</td>
                                                <td>
                                                    <a href='".url('sale-return-edit/'.$v->id)."'
                                                    class='btn btn-warning btn-sm'
                                                    style='padding:0.2rem 0.4rem;font-size:0.75rem;'>
                                                    Edit
                                                    </a>
                                                </td>
                                            </tr>";
                }
                $b2b_credit_note_unlinked = "<tr style='font-weight:bold;background:#f8f9fa'>
                                                <td colspan='4' style='text-align:right'>TOTAL (Credit Notes)</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_txval_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_igst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_cgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_sgst_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_cess_unlink)."</td>
                                                <td style='text-align:right'>".formatIndianNumber($credit_total_book_value_unlink)."</td>
                                            </tr>";

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
                            <td><strong style='color:red'>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
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
                            <td><strong style='color:red'>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
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
                            <td><strong style='color:red'>Rejected - ".($v->remark)."</strong> <br> <button class='btn btn-success accept' data-id='".$v->id."' style='padding: 0.2rem 0.4rem;font-size: 0.75rem;line-height: 1.2;border-radius: 0.2rem;'>Accept</button></td>
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
            'b2b_invoices_matched' => $b2b_invoices_matched,
            'b2b_invoices_on_portal_but_not_in_book' => $b2b_invoices_on_portal_but_not_in_book,
            'b2b_invoices_only_in_book' => $b2b_invoices_only_in_book,
            'b2b_credit_note_unlinked' => $b2b_credit_note_unlinked,
            'b2b_debit_note_unlinked' => $b2b_debit_note_unlinked,
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
            ->where('delete','0')
            ->where('status','1')
            ->whereNull('gstr2b_invoice_id')
            ->orWhere('gstr2b_invoice_id',$request->invoice_no)
            ->orderBy('id', 'desc')
            ->get();
        $debit_note = PurchaseReturn::select('sr_prefix','series_no','total','date','id','gstr2b_invoice_id')
            ->where('company_id', Session::get('user_company_id'))
            ->where('merchant_gst', $request->gstin)
            ->where('billing_gst', $request->ctin)
            ->where('delete','0')
            ->where('status','1')
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

        // First unlink old linked entries
        SalesReturn::where('gstr2b_invoice_id', $request->invoice_no)
            ->update([
                'gstr2b_invoice_id' => null,
                'linked_month'      => null
            ]);

        // Link new selected entries only if selected
        if(!empty($request->ids)){
            SalesReturn::whereIn('id', $request->ids)
                ->update([
                    'gstr2b_invoice_id' => $request->invoice_no,
                    'linked_month'      => $request->month
                ]);
        }

    }else if($request->type == "debit_note"){

        PurchaseReturn::where('gstr2b_invoice_id', $request->invoice_no)
            ->update([
                'gstr2b_invoice_id' => null,
                'linked_month'      => null
            ]);

        if(!empty($request->ids)){
            PurchaseReturn::whereIn('id', $request->ids)
                ->update([
                    'gstr2b_invoice_id' => $request->invoice_no,
                    'linked_month'      => $request->month
                ]);
        }

    }else{

        return response()->json([
            'status' => false,
            'message' => 'Invalid type'
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Updated Successfully',
    ]);
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
    
    public function getUnlinkInvoiceEntry(Request $request){
        $selectedDate = $request->month . '-01'; // 2026-05-01
        $year = date('Y', strtotime($selectedDate));
        $month = date('m', strtotime($selectedDate));

        if ($month >= 4) {
            $fyStart = $year . '-04-01';
            $fyEnd   = ($year + 1) . '-03-31';
        } else {
            $fyStart = ($year - 1) . '-04-01';
            $fyEnd   = $year . '-03-31';
        }
        $purchase = Purchase::select('voucher_no','date','total','id','gstr2b_invoice_id')
                                ->where('billing_gst',$request->ctin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('merchant_gst',$request->gstin)
                                ->where('status','1')
                                ->whereBetween('date', [$fyStart, $fyEnd])
                                ->where(function($query) use ($request) {
                                    $query->whereNull('gstr2b_invoice_id')
                                          ->orWhere('gstr2b_invoice_id', $request->invoice);
                                })
                                ->where('delete','0')
                                ->orderBy('date')
                                ->get();
        $journal = Journal::select('invoice_no as voucher_no','date','total_amount as total','id','gstr2b_invoice_id')
                                ->where('vendor_gstin',$request->ctin)
                                ->where('claim_gst_status',"YES")
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('merchant_gst',$request->gstin)
                                ->where('status','1')
                                ->whereBetween('date', [$fyStart, $fyEnd])
                                ->where(function($query) use ($request) {
                                    $query->whereNull('gstr2b_invoice_id')
                                          ->orWhere('gstr2b_invoice_id', $request->invoice);
                                })                                
                                ->where('delete','0')
                                ->orderBy('date')
                                ->get();
        $response = array(
            'status' => true,
            'purchase' => $purchase,
            'journal' => $journal
        );
        return json_encode($response);
    }
    
    public function linkGstr2bInvoiceEntry(Request $request){   
        if(empty($request->ids)){
            Purchase::where('gstr2b_invoice_id', $request->invoice_no)
                ->where('billing_gst',$request->ctin)
                ->where('company_id',Session::get('user_company_id'))
                ->where('merchant_gst',$request->gstin)
                ->update([
                    'gstr2b_invoice_id' => null,
                    'gstr2b_invoice_month' => null
                ]);
            Journal::where('gstr2b_invoice_id', $request->invoice_no)
                ->where('vendor_gstin',$request->ctin)
                ->where('company_id',Session::get('user_company_id'))
                ->where('merchant_gst',$request->gstin)
                ->update([
                    'gstr2b_invoice_id' => null,
                    'gstr2b_invoice_month' => null
                ]);
            return json_encode([
                'status' => true,
                'message' => 'Unlinked Successfully'
            ]);
        }    
        foreach($request->ids as $value){
            if($value['type']=="PURCHASE"){
                Purchase::where('gstr2b_invoice_id', $request->invoice_no)
                        ->where('billing_gst',$request->ctin)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->update(['gstr2b_invoice_id' =>null]);
                Purchase::where('id', $value['id'])
                            ->update([
                                'gstr2b_invoice_id' => $request->invoice_no,
                                "gstr2b_invoice_month"=>$request->month
                            ]);
            }else if($value['type']=="JOURNAL"){
                Journal::where('gstr2b_invoice_id', $request->invoice_no)
                        ->where('vendor_gstin',$request->ctin)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->update(['gstr2b_invoice_id' =>null]);
                Journal::where('id', $value['id'])
                        ->update([
                            'gstr2b_invoice_id' => $request->invoice_no,
                            "gstr2b_invoice_month"=>$request->month
                        ]);
            }
        }        
        $response = array(
            'status' => true,
            'message' => 'Linked Successfully',
        );
        return json_encode($response);
    }
    public function gstr2bReconciliationData(Request $request,$month,$gstin){
        $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('res_month',$request->month)
                            ->first();
        $gstr2b = json_decode($gstr2b->res_data);
        $portal_cgst_amount = 0;
        $portal_sgst_amount = 0;
        $portal_igst_amount = 0;
        $portal_invoice_amount = 0;       
        
        $previous_month_invoice_amount = 0;
        $previous_month_invoice_cgst_amount = 0;
        $previous_month_invoice_sgst_amount = 0;
        $previous_month_invoice_igst_amount = 0;

        $previous_month_journal_amount = 0;
        $previous_month_journal_cgst_amount = 0;
        $previous_month_journal_sgst_amount = 0;
        $previous_month_journal_igst_amount = 0;

        $previous_month_credit_note_amount = 0;
        $previous_month_credit_note_cgst_amount = 0;
        $previous_month_credit_note_sgst_amount = 0;
        $previous_month_credit_note_igst_amount = 0;

        $previous_month_debit_note_amount = 0;
        $previous_month_debit_note_cgst_amount = 0;
        $previous_month_debit_note_sgst_amount = 0;
        $previous_month_debit_note_igst_amount = 0;

        $only_on_portal_purchase_amount = 0;
        $only_on_portal_purchase_cgst_amount = 0;
        $only_on_portal_purchase_sgst_amount = 0;
        $only_on_portal_purchase_igst_amount = 0;

        $only_on_portal_credit_note_amount = 0;
        $only_on_portal_credit_note_cgst_amount = 0;
        $only_on_portal_credit_note_sgst_amount = 0;
        $only_on_portal_credit_note_igst_amount = 0;

        $only_on_portal_debit_note_amount = 0;
        $only_on_portal_debit_note_cgst_amount = 0;
        $only_on_portal_debit_note_sgst_amount = 0;
        $only_on_portal_debit_note_igst_amount = 0;

        $only_on_book_purchase_amount = 0;
        $only_on_book_purchase_cgst_amount = 0;
        $only_on_book_purchase_sgst_amount = 0;
        $only_on_book_purchase_igst_amount = 0;

        $only_on_book_credit_note_amount = 0;
        $only_on_book_credit_note_cgst_amount = 0;
        $only_on_book_credit_note_sgst_amount = 0;
        $only_on_book_credit_note_igst_amount = 0;

        $only_on_book_debit_note_amount = 0;
        $only_on_book_debit_note_cgst_amount = 0;
        $only_on_book_debit_note_sgst_amount = 0;
        $only_on_book_debit_note_igst_amount = 0;
        //cdnr
        // echo "<pre>";
        // print_r($gstr2b);
        // echo "</pre>";
        // die();
        
        //$total_invoice_amount = array_sum($total_invoice_amount);
        foreach($gstr2b->data->docdata->b2b as $record){
            foreach($record->inv as $invoice){
                //Portal              
                $portal_cgst_amount += $invoice->cgst;
                $portal_sgst_amount += $invoice->sgst;
                $portal_igst_amount += $invoice->igst;
                $portal_invoice_amount += $invoice->val;
                //Previous Month Invoice
                $purchase = Purchase::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('billing_gst',$record->ctin)
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('date', 'not like', $request->month.'%')
                        ->where('gstr2b_invoice_id',$invoice->inum)
                        ->where('gstr2b_invoice_month','=',$request->month)
                        ->first();
                if($purchase){
                    $previous_month_invoice_amount += $invoice->val;
                    $previous_month_invoice_cgst_amount += $invoice->cgst;
                    $previous_month_invoice_sgst_amount += $invoice->sgst;
                    $previous_month_invoice_igst_amount += $invoice->igst;
                }else{
                    $idate = date('Y-m',strtotime($invoice->dt));
                    $journal = Journal::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('vendor_gstin',$record->ctin)
                        ->where('claim_gst_status',"YES")
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('date', 'not like', $request->month.'%')
                        ->where('gstr2b_invoice_id',$invoice->inum)
                        ->where('gstr2b_invoice_month','=',$request->month)
                        ->first();
                    if($journal){
                        $previous_month_journal_amount += $invoice->val;
                        $previous_month_journal_cgst_amount += $invoice->cgst;
                        $previous_month_journal_sgst_amount += $invoice->sgst;
                        $previous_month_journal_igst_amount += $invoice->igst;
                        
                    }
                }
                //Only On Portal Invoice
                $purchase = Purchase::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('billing_gst',$record->ctin)
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('gstr2b_invoice_id',$invoice->inum)
                        ->orderBy('id','desc')
                        ->first();
                if(!$purchase){
                    $journal = Journal::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('vendor_gstin',$record->ctin)
                        ->where('claim_gst_status',"YES")
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('gstr2b_invoice_id',$invoice->inum)
                        ->orderBy('id','desc')
                        ->first();
                    if(!$journal){
                        $only_on_portal_purchase_amount += $invoice->val;
                        $only_on_portal_purchase_cgst_amount += $invoice->cgst;
                        $only_on_portal_purchase_sgst_amount += $invoice->sgst;
                        $only_on_portal_purchase_igst_amount += $invoice->igst;
                    }                    
                }
                
            }
        }
        if(isset($gstr2b->data->docdata->cdnr)){
        foreach($gstr2b->data->docdata->cdnr as $record){
            foreach ($record->nt as $invoice) {
                if($invoice->typ == "C"){
                    //Portal  
                    $portal_cgst_amount -= $invoice->cgst;
                    $portal_sgst_amount -= $invoice->sgst;
                    $portal_igst_amount -= $invoice->igst;
                    $portal_invoice_amount -= $invoice->val;
                    
                    //Previous Month Debit Note
                    $purchase_return = PurchaseReturn::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('billing_gst',$record->ctin)
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('gstr2b_invoice_id',$invoice->ntnum)
                        // ->where('linked_month','!=',$request->month)
                        ->where(function ($q) use ($request) {
                            $q->where('linked_month', '!=', $request->month)
                            ->orwhere('date', 'not like', $request->month.'%');
                        })

                        ->first();
                    if($purchase_return){
                        $previous_month_debit_note_amount += $invoice->val;
                        $previous_month_debit_note_cgst_amount += $invoice->cgst;
                        $previous_month_debit_note_sgst_amount += $invoice->sgst;
                        $previous_month_debit_note_igst_amount += $invoice->igst;
                    }
                     //Only On Portal Debit Note
                    $purchase_return = PurchaseReturn::select('id')
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->where('merchant_gst',$request->gstin)
                                    ->where('billing_gst',$record->ctin)
                                    ->where('status','1')
                                    ->where('delete','0')
                                    ->where('gstr2b_invoice_id',$invoice->ntnum)
                                    ->where('linked_month',$request->month)
                                    ->first();
                    if(!$purchase_return){
                        $only_on_portal_debit_note_amount += $invoice->val;
                        $only_on_portal_debit_note_cgst_amount += $invoice->cgst;
                        $only_on_portal_debit_note_sgst_amount += $invoice->sgst;
                        $only_on_portal_debit_note_igst_amount += $invoice->igst;                                        
                    }
                    
                }else if($invoice->typ == "D"){
                    //Portal  
                    $portal_cgst_amount += $invoice->cgst;
                    $portal_sgst_amount += $invoice->sgst;
                    $portal_igst_amount += $invoice->igst;
                    $portal_invoice_amount += $invoice->val;
                    //Previous Month Credit Note
                    $sales_return = SalesReturn::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('billing_gst',$record->ctin)
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('gstr2b_invoice_id',$invoice->ntnum)
                        // ->where('linked_month','!=',$request->month)
                        ->where(function ($q) use ($request) {
                            $q->where('linked_month', '!=', $request->month)
                            ->orwhere('date', 'not like', $request->month.'%');
                        })
                        ->first();
                    if($sales_return){
                        $previous_month_credit_note_amount += $invoice->val;
                        $previous_month_credit_note_cgst_amount += $invoice->cgst;
                        $previous_month_credit_note_sgst_amount += $invoice->sgst;
                        $previous_month_credit_note_igst_amount += $invoice->igst;
                    }
                    //Only On Portal Credit Note
                    $sales_return = SalesReturn::select('id')
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->where('merchant_gst',$request->gstin)
                                    ->where('billing_gst',$record->ctin)
                                    ->where('status','1')
                                    ->where('delete','0')
                                    ->where('gstr2b_invoice_id',$invoice->ntnum)
                                    ->where('linked_month',$request->month)
                                    ->first();
                    if(!$sales_return){
                        $only_on_portal_credit_note_amount += $invoice->val;
                        $only_on_portal_credit_note_cgst_amount += $invoice->cgst;
                        $only_on_portal_credit_note_sgst_amount += $invoice->sgst;
                        $only_on_portal_credit_note_igst_amount += $invoice->igst;
                    }
                }
            }
        }  
        }
        //Only On Book Purchase
        $purchase_only_on_book = Purchase::where('company_id', Session::get('user_company_id'))
                                ->where('merchant_gst', $request->gstin)
                                ->where('status', '1')
                                ->where('delete', '0')
                                ->whereNull('gstr2b_invoice_id')
                                ->where('date', 'like', $request->month . '%')
                                ->selectRaw('
                                    SUM(total) as total_sum,
                                    SUM(taxable_amt)  as tax_sum,
                                    (
                                        SELECT SUM(ps.amount)
                                        FROM purchase_sundries ps
                                        join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                                        WHERE ps.purchase_id = purchases.id
                                        AND bill_sundrys.nature_of_sundry IN ("CGST")
                                    ) as cgst_sum,
                                    (
                                        SELECT SUM(ps.amount)
                                        FROM purchase_sundries ps
                                        join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                                        WHERE ps.purchase_id = purchases.id
                                        AND bill_sundrys.nature_of_sundry IN ("SGST")
                                    ) as sgst_sum,
                                    (
                                        SELECT SUM(ps.amount)
                                        FROM purchase_sundries ps
                                        join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                                        WHERE ps.purchase_id = purchases.id
                                        AND bill_sundrys.nature_of_sundry IN ("IGST")
                                    ) as igst_sum
                                ')
                                ->first();
        $purchase_only_on_book_detail = Purchase::join('purchase_sundries as ps', 'ps.purchase_id', '=', 'purchases.id')
                                    ->join('bill_sundrys as bs', 'bs.id', '=', 'ps.bill_sundry')
                                    ->join('accounts', 'purchases.party', '=', 'accounts.id')
                                    ->where('purchases.company_id', Session::get('user_company_id'))
                                    ->where('purchases.merchant_gst', $request->gstin)
                                    ->where('purchases.status', '1')
                                    ->where('purchases.delete', '0')
                                    ->whereNull('purchases.gstr2b_invoice_id')
                                    ->where('purchases.date', 'like', $request->month . '%')
                                    ->whereIn('bs.nature_of_sundry', ['CGST','SGST','IGST'])
                                    ->select([
                                        'purchases.voucher_no',
                                        'purchases.date',
                                        'accounts.account_name',
                                        'purchases.total as amount'
                                    ])
                                    ->orderBy('purchases.date')
                                    ->get();

        // echo "<pre>";
        // print_r($purchase_only_on_book_detail->toArray());
        // echo "</pre>";
        // die();
        //Only On Book Journal
        $journal = Journal::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)                        
                        ->where('claim_gst_status',"YES")
                        ->where('status','1')
                        ->where('delete','0')
                        ->whereNull('gstr2b_invoice_id')
                        ->where('date', 'like', $request->month . '%')
                        ->selectRaw('
                            SUM(total_amount)        as total_sum,
                            SUM(net_total)  as tax_sum,
                            SUM(cgst)        as cgst_sum,
                            SUM(sgst)        as sgst_sum,
                            SUM(igst)        as igst_sum
                        ')
                        ->first();
        $journal_only_on_book_detail = Journal::select('total_amount as amount','invoice_no as voucher_no','date','accounts.account_name','claim_gst_status')
                        ->join('accounts', 'journals.vendor', '=', 'accounts.id')
                        ->where('journals.company_id',Session::get('user_company_id'))
                        ->where('merchant_gst',$request->gstin)
                        ->where('claim_gst_status',"YES")
                        ->where('journals.status','1')
                        ->where('journals.delete','0')
                        ->whereNull('gstr2b_invoice_id')
                        ->where('journals.date', 'like', $request->month . '%')                        
                        ->get();
        $debit_note_only_on_book_detail = PurchaseReturn::join('purchase_return_sundries as ps', 'ps.purchase_return_id', '=', 'purchase_returns.id')
                                    ->join('bill_sundrys as bs', 'bs.id', '=', 'ps.bill_sundry')
                                    ->join('accounts', 'purchase_returns.party', '=', 'accounts.id')
                                    ->where('voucher_type','PURCHASE')
                                    ->where('purchase_returns.company_id', Session::get('user_company_id'))
                                    ->where('purchase_returns.merchant_gst', $request->gstin)
                                    ->where('purchase_returns.status', '1')
                                    ->where('purchase_returns.delete', '0')
                                    ->whereNull('purchase_returns.gstr2b_invoice_id')
                                    ->where('purchase_returns.date', 'like', $request->month . '%')
                                    ->whereIn('bs.nature_of_sundry', ['CGST','SGST','IGST'])
                                    ->select([
                                        'purchase_returns.sr_prefix as voucher_no',
                                        'purchase_returns.date',
                                        'accounts.account_name',
                                        'purchase_returns.total as amount'
                                    ])
                                    ->orderBy('purchase_returns.date')
                                    ->get();
        $credit_note_only_on_book_detail = SalesReturn::join('sale_return_sundries as ps', 'ps.sale_return_id', '=', 'sales_returns.id')
                                    ->join('bill_sundrys as bs', 'bs.id', '=', 'ps.bill_sundry')
                                    ->join('accounts', 'sales_returns.party', '=', 'accounts.id')
                                    ->where('voucher_type','PURCHASE')
                                    ->where('sales_returns.company_id', Session::get('user_company_id'))
                                    ->where('sales_returns.merchant_gst', $request->gstin)
                                    ->where('sales_returns.status', '1')
                                    ->where('sales_returns.delete', '0')
                                    ->whereNull('sales_returns.gstr2b_invoice_id')
                                    ->where('sales_returns.date', 'like', $request->month . '%')
                                    ->whereIn('bs.nature_of_sundry', ['CGST','SGST','IGST'])
                                    ->select([
                                        'sales_returns.sr_prefix as voucher_no',
                                        'sales_returns.date',
                                        'accounts.account_name',
                                        'sales_returns.total as amount'
                                    ])
                                    ->orderBy('sales_returns.date')
                                    ->get();
        $only_on_book_purchase_amount = $purchase_only_on_book->total_sum + $journal->total_sum;
        $only_on_book_purchase_cgst_amount = $purchase_only_on_book->cgst_sum + $journal->cgst_sum;
        $only_on_book_purchase_sgst_amount = $purchase_only_on_book->sgst_sum + $journal->sgst_sum;
        $only_on_book_purchase_igst_amount = $purchase_only_on_book->igst_sum + $journal->igst_sum;
        //Only On Book Credit Note
        $sales_return_only_on_book = SalesReturn::where('company_id', Session::get('user_company_id'))
                    ->where('merchant_gst', $request->gstin)
                    ->where('voucher_type','PURCHASE')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->whereNull('gstr2b_invoice_id')
                    ->where('date', 'like', $request->month . '%')
                    ->selectRaw('
                        SUM(total)        as total_sum,
                        SUM(taxable_amt)  as tax_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM sale_return_sundries  ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.sale_return_id = sales_returns .id
                            AND bill_sundrys.nature_of_sundry IN ("CGST")
                        ) as cgst_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM sale_return_sundries  ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.sale_return_id = sales_returns .id
                            AND bill_sundrys.nature_of_sundry IN ("SGST")
                        ) as sgst_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM sale_return_sundries  ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.sale_return_id = sales_returns .id
                            AND bill_sundrys.nature_of_sundry IN ("IGST")
                        ) as igst_sum
                    ')
                    ->first();
        $only_on_book_credit_note_amount = $sales_return_only_on_book->total_sum ?? 0;
        $only_on_book_credit_note_cgst_amount = $sales_return_only_on_book->cgst_sum ?? 0;
        $only_on_book_credit_note_sgst_amount = $sales_return_only_on_book->sgst_sum ?? 0;
        $only_on_book_credit_note_igst_amount = $sales_return_only_on_book->igst_sum ?? 0;
        //Only On Book Debit Note
        $purchase_return_only_on_book = PurchaseReturn::where('company_id', Session::get('user_company_id'))
                    ->where('merchant_gst', $request->gstin)
                    ->where('voucher_type','PURCHASE')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->whereNull('gstr2b_invoice_id')
                    ->where('date', 'like', $request->month . '%')
                    ->selectRaw('
                        SUM(total) as total_sum,
                        SUM(taxable_amt)  as tax_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM purchase_return_sundries   ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.purchase_return_id =  purchase_returns  .id
                            AND bill_sundrys.nature_of_sundry IN ("CGST")
                        ) as cgst_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM purchase_return_sundries   ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.purchase_return_id =  purchase_returns  .id
                            AND bill_sundrys.nature_of_sundry IN ("SGST")
                        ) as sgst_sum,
                        (
                            SELECT SUM(ps.amount)
                            FROM purchase_return_sundries   ps
                            join  bill_sundrys on ps.bill_sundry=bill_sundrys.id
                            WHERE ps.purchase_return_id =  purchase_returns  .id
                            AND bill_sundrys.nature_of_sundry IN ("IGST")
                        ) as igst_sum
                    ')
                    ->first();
                    
        $only_on_book_debit_note_amount = $purchase_return_only_on_book->total_sum ?? 0;
        // echo $only_on_book_debit_note_amount;
        //             die();
        $only_on_book_debit_note_cgst_amount = $purchase_return_only_on_book->cgst_sum ?? 0;
        $only_on_book_debit_note_sgst_amount = $purchase_return_only_on_book->sgst_sum ?? 0;
        $only_on_book_debit_note_igst_amount = $purchase_return_only_on_book->igst_sum ?? 0;
        $res = array(
            "month"=>$month,
            "gstin"=>$gstin,
            "portal_cgst_amount"=>$portal_cgst_amount,
            "portal_sgst_amount"=>$portal_sgst_amount,
            "portal_igst_amount"=>$portal_igst_amount,
            "portal_invoice_amount"=>$portal_invoice_amount,

            "previous_month_invoice_amount"=>$previous_month_invoice_amount,
            "previous_month_invoice_cgst_amount"=>$previous_month_invoice_cgst_amount,
            "previous_month_invoice_sgst_amount"=>$previous_month_invoice_sgst_amount,
            "previous_month_invoice_igst_amount"=>$previous_month_invoice_igst_amount,

            "previous_month_journal_amount"=>$previous_month_journal_amount,
            "previous_month_journal_cgst_amount"=>$previous_month_journal_cgst_amount,
            "previous_month_journal_sgst_amount"=>$previous_month_journal_sgst_amount,
            "previous_month_journal_igst_amount"=>$previous_month_journal_igst_amount,

            "previous_month_credit_note_amount"=>$previous_month_credit_note_amount,
            "previous_month_credit_note_cgst_amount"=>$previous_month_credit_note_cgst_amount,
            "previous_month_credit_note_sgst_amount"=>$previous_month_credit_note_sgst_amount,
            "previous_month_credit_note_igst_amount"=>$previous_month_credit_note_igst_amount,

            "previous_month_debit_note_amount"=>$previous_month_debit_note_amount,
            "previous_month_debit_note_cgst_amount"=>$previous_month_debit_note_cgst_amount,
            "previous_month_debit_note_sgst_amount"=>$previous_month_debit_note_sgst_amount,
            "previous_month_debit_note_igst_amount"=>$previous_month_debit_note_igst_amount,

            "only_on_portal_purchase_amount"=>$only_on_portal_purchase_amount,
            "only_on_portal_purchase_cgst_amount"=>$only_on_portal_purchase_cgst_amount,
            "only_on_portal_purchase_sgst_amount"=>$only_on_portal_purchase_sgst_amount,
            "only_on_portal_purchase_igst_amount"=>$only_on_portal_purchase_igst_amount,

            "only_on_portal_credit_note_amount"=>$only_on_portal_credit_note_amount,
            "only_on_portal_credit_note_cgst_amount"=>$only_on_portal_credit_note_cgst_amount,
            "only_on_portal_credit_note_sgst_amount"=>$only_on_portal_credit_note_sgst_amount,
            "only_on_portal_credit_note_igst_amount"=>$only_on_portal_credit_note_igst_amount,

            "only_on_portal_debit_note_amount"=>$only_on_portal_debit_note_amount,
            "only_on_portal_debit_note_cgst_amount"=>$only_on_portal_debit_note_cgst_amount,
            "only_on_portal_debit_note_sgst_amount"=>$only_on_portal_debit_note_sgst_amount,
            "only_on_portal_debit_note_igst_amount"=>$only_on_portal_debit_note_igst_amount,

            "only_on_book_purchase_amount"=>$only_on_book_purchase_amount,
            "only_on_book_purchase_cgst_amount"=>$only_on_book_purchase_cgst_amount,
            "only_on_book_purchase_sgst_amount"=>$only_on_book_purchase_sgst_amount,
            "only_on_book_purchase_igst_amount"=>$only_on_book_purchase_igst_amount,

            "only_on_book_credit_note_amount"=>$only_on_book_credit_note_amount,
            "only_on_book_credit_note_cgst_amount"=>$only_on_book_credit_note_cgst_amount,
            "only_on_book_credit_note_sgst_amount"=>$only_on_book_credit_note_sgst_amount,
            "only_on_book_credit_note_igst_amount"=>$only_on_book_credit_note_igst_amount,

            "only_on_book_debit_note_amount"=>$only_on_book_debit_note_amount,
            "only_on_book_debit_note_cgst_amount"=>$only_on_book_debit_note_cgst_amount,
            "only_on_book_debit_note_sgst_amount"=>$only_on_book_debit_note_sgst_amount,
            "only_on_book_debit_note_igst_amount"=>$only_on_book_debit_note_igst_amount,

            "purchase_only_on_book_detail"=>$purchase_only_on_book_detail,
            "journal_only_on_book_detail"=>$journal_only_on_book_detail,
            "debit_note_only_on_book_detail" => $debit_note_only_on_book_detail,
            "credit_note_only_on_book_detail" => $credit_note_only_on_book_detail
        );
        return json_encode($res);
        return view('gstReturn/gstr2b_reconcilation',[
            "month"=>$month,
            "gstin"=>$gstin,
            "portal_cgst_amount"=>$portal_cgst_amount,
            "portal_sgst_amount"=>$portal_sgst_amount,
            "portal_igst_amount"=>$portal_igst_amount,
            "portal_invoice_amount"=>$portal_invoice_amount,

            "previous_month_invoice_amount"=>$previous_month_invoice_amount,
            "previous_month_invoice_cgst_amount"=>$previous_month_invoice_cgst_amount,
            "previous_month_invoice_sgst_amount"=>$previous_month_invoice_sgst_amount,
            "previous_month_invoice_igst_amount"=>$previous_month_invoice_igst_amount,

            "previous_month_journal_amount"=>$previous_month_journal_amount,
            "previous_month_journal_cgst_amount"=>$previous_month_journal_cgst_amount,
            "previous_month_journal_sgst_amount"=>$previous_month_journal_sgst_amount,
            "previous_month_journal_igst_amount"=>$previous_month_journal_igst_amount,

            "previous_month_credit_note_amount"=>$previous_month_credit_note_amount,
            "previous_month_credit_note_cgst_amount"=>$previous_month_credit_note_cgst_amount,
            "previous_month_credit_note_sgst_amount"=>$previous_month_credit_note_sgst_amount,
            "previous_month_credit_note_igst_amount"=>$previous_month_credit_note_igst_amount,

            "previous_month_debit_note_amount"=>$previous_month_debit_note_amount,
            "previous_month_debit_note_cgst_amount"=>$previous_month_debit_note_cgst_amount,
            "previous_month_debit_note_sgst_amount"=>$previous_month_debit_note_sgst_amount,
            "previous_month_debit_note_igst_amount"=>$previous_month_debit_note_igst_amount,

            "only_on_portal_purchase_amount"=>$only_on_portal_purchase_amount,
            "only_on_portal_purchase_cgst_amount"=>$only_on_portal_purchase_cgst_amount,
            "only_on_portal_purchase_sgst_amount"=>$only_on_portal_purchase_sgst_amount,
            "only_on_portal_purchase_igst_amount"=>$only_on_portal_purchase_igst_amount,

            "only_on_portal_credit_note_amount"=>$only_on_portal_credit_note_amount,
            "only_on_portal_credit_note_cgst_amount"=>$only_on_portal_credit_note_cgst_amount,
            "only_on_portal_credit_note_sgst_amount"=>$only_on_portal_credit_note_sgst_amount,
            "only_on_portal_credit_note_igst_amount"=>$only_on_portal_credit_note_igst_amount,

            "only_on_portal_debit_note_amount"=>$only_on_portal_debit_note_amount,
            "only_on_portal_debit_note_cgst_amount"=>$only_on_portal_debit_note_cgst_amount,
            "only_on_portal_debit_note_sgst_amount"=>$only_on_portal_debit_note_sgst_amount,
            "only_on_portal_debit_note_igst_amount"=>$only_on_portal_debit_note_igst_amount,

            "only_on_book_purchase_amount"=>$only_on_book_purchase_amount,
            "only_on_book_purchase_cgst_amount"=>$only_on_book_purchase_cgst_amount,
            "only_on_book_purchase_sgst_amount"=>$only_on_book_purchase_sgst_amount,
            "only_on_book_purchase_igst_amount"=>$only_on_book_purchase_igst_amount,

            "only_on_book_credit_note_amount"=>$only_on_book_credit_note_amount,
            "only_on_book_credit_note_cgst_amount"=>$only_on_book_credit_note_cgst_amount,
            "only_on_book_credit_note_sgst_amount"=>$only_on_book_credit_note_sgst_amount,
            "only_on_book_credit_note_igst_amount"=>$only_on_book_credit_note_igst_amount,

            "only_on_book_debit_note_amount"=>$only_on_book_debit_note_amount,
            "only_on_book_debit_note_cgst_amount"=>$only_on_book_debit_note_cgst_amount,
            "only_on_book_debit_note_sgst_amount"=>$only_on_book_debit_note_sgst_amount,
            "only_on_book_debit_note_igst_amount"=>$only_on_book_debit_note_igst_amount,

            "purchase_only_on_book_detail"=>$purchase_only_on_book_detail,
            "journal_only_on_book_detail"=>$journal_only_on_book_detail
        ]);
        //Single Motnth Data
        // $RejectedGstr2b = RejectedGstr2b::where('company_gstin',$request->gstin)
        //                     ->where('company_id',Session::get('user_company_id'))
        //                     ->where('gstr2b_month',$request->month)
        //                     ->get();
        // $rejectedIrns = $RejectedGstr2b->pluck('invoice_number')->toArray();
        // $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
        //                     ->where('company_id',Session::get('user_company_id'))
        //                     ->where('res_month',$request->month)
        //                     ->first();
        // $gstr2b = json_decode($gstr2b->res_data);
        // $total_val_on_portal_but_not_in_book = 0;
        // $total_val_only_in_book = 0;
        // //Only in books but not portal code
        // $book_vouchers = Purchase::select('voucher_no')
        //                         ->where('company_id',Session::get('user_company_id'))
        //                         ->where('merchant_gst',$request->gstin)
        //                         ->where('status','1')
        //                         ->where('date', 'like', $request->month.'%')
        //                         ->where('delete','0')
        //                         ->where(function($q) use ($request) {
        //                             $q->where('gstr2b_invoice_id',null);
        //                             $q->orWhere('gstr2b_invoice_id','');
        //                         })                                
        //                         ->pluck('voucher_no');
        // $book_vouchers = $book_vouchers->toArray();
        // $portal_vouchers = [];
        // foreach ($gstr2b->data->docdata->b2b as $record) {            
        //     foreach ($record->inv as $key=>$invoice) {
        //         $portal_vouchers[] = $invoice->inum;
        //         if (in_array($invoice->inum, $rejectedIrns)) {
        //             continue; // Skip rejected invoices
        //         }
        //         $book_data = Purchase::select('total')
        //                                 ->where('company_id', Session::get('user_company_id'))
        //                                 ->where('merchant_gst', $request->gstin)
        //                                 ->where('date', 'like', $request->month.'%')
        //                                 ->where('status', '1')
        //                                 ->where('delete', '0')
        //                                 ->where(function($q) use ($invoice, $request) {
        //                                     $q->where('voucher_no', $invoice->inum);
        //                                     $q->orWhere('gstr2b_invoice_id', $invoice->inum); // add your OR here
        //                                 })
        //                                 ->first();

        //         $book_value = 0;
        //         if($book_data){
        //             $book_value = $book_data->total;
        //         }else{
        //             $journal_book_data = Journal::select('total_amount')
        //                                 ->where('company_id',Session::get('user_company_id'))
        //                                 ->where('merchant_gst',$request->gstin)
        //                                 ->where('claim_gst_status','YES')
        //                                 ->where('invoice_no',$invoice->inum)
        //                                 ->where('status','1')
        //                                 ->where('delete','0')
        //                                 ->first();
        //             if($journal_book_data){
        //                 $book_value = $journal_book_data->total_amount;
        //             }
        //         }
        //         if($book_value!=$invoice->val){
        //             $total_val_on_portal_but_not_in_book += $invoice->val;
        //         }
        //     }            
        // }
        // $portal_upper = array_map('strtoupper', $portal_vouchers);
        // $only_in_book_voucher = array_filter($book_vouchers, function($val) use ($portal_upper) {
        //                             return !in_array(strtoupper($val), $portal_upper);
        //                         });        
        // foreach ($only_in_book_voucher as $key => $invoice_no) {
        //     $book_data = Purchase::with(['purchaseSundry'])->where('voucher_no',$invoice_no)
        //                             ->where('company_id',Session::get('user_company_id'))
        //                             ->where('merchant_gst',$request->gstin)
        //                             ->where('status','1')
        //                             ->where('delete','0')
        //                             ->first();
        //     if($book_data){
        //          $total_val_only_in_book = $total_val_only_in_book + $book_data->total;
        //     }
        // }
        // //Data from April upto date
        // // 1. Get financial year range
        // $currentYear = date('Y');
        // $currentMonth = date('n'); // 1–12

        // if ($currentMonth >= 4) {
        //     $fyStart = $currentYear . '-04-01';
        //     $fyEnd   = ($currentYear + 1) . '-03-31';
        // } else {
        //     $fyStart = ($currentYear - 1) . '-04-01';
        //     $fyEnd   = $currentYear . '-03-31';
        // }
        // $RejectedGstr2b = RejectedGstr2b::where('company_gstin',$request->gstin)
        //                     ->where('company_id',Session::get('user_company_id'))
        //                     ->whereBetween('gstr2b_month', [$fyStart, $fyEnd])
        //                     ->get();
        // $rejectedIrns = $RejectedGstr2b->pluck('invoice_number')->toArray();

        // $gstr2b = GSTR2B::where('company_gstin', $request->gstin)
        // ->where('company_id', Session::get('user_company_id'))
        // ->whereBetween('res_month', [$fyStart, $fyEnd])
        // ->get();

        // // Flatten all portal invoices into one list
        // $allPortalInvoices = [];
        // foreach ($gstr2b as $monthData) {
        //     $data = json_decode($monthData->res_data);
        //     if (!empty($data->data->docdata->b2b)) {
        //         foreach ($data->data->docdata->b2b as $record) {
        //             foreach ($record->inv as $invoice) {
        //                 $allPortalInvoices[] = [
        //                     'ctin' => $record->ctin,
        //                     'inum' => $invoice->inum,
        //                     'dt'   => $invoice->dt,
        //                     'val'  => $invoice->val,
        //                     'txval'=> $invoice->txval,
        //                     'igst' => $invoice->igst,
        //                     'cgst' => $invoice->cgst,
        //                     'sgst' => $invoice->sgst,
        //                     'cess' => $invoice->cess,
        //                 ];
        //             }
        //         }
        //     }
        // }
        // // 4. Book vouchers for FY
        // $book_vouchers = Purchase::select('voucher_no')
        //     ->where('company_id', Session::get('user_company_id'))
        //     ->where('merchant_gst', $request->gstin)
        //     ->where('status', '1')
        //     ->whereBetween('date', [$fyStart, $fyEnd])
        //     ->where('delete', '0')
        //     ->where(function ($q) {
        //         $q->whereNull('gstr2b_invoice_id')
        //         ->orWhere('gstr2b_invoice_id', '');
        //     })
        //     ->pluck('voucher_no')
        //     ->toArray();
        //     $b2b_invoices_on_portal_but_not_in_book = "";
        //     $total_val_on_portal_but_not_in_book1 = $total_txval_on_portal_but_not_in_book = 0;
        //     $total_igst_on_portal_but_not_in_book = $total_cgst_on_portal_but_not_in_book = 0;
        //     $total_sgst_on_portal_but_not_in_book = $total_cess_on_portal_but_not_in_book = 0;
        //     $total_book_value_on_portal_but_not_in_book = 0;

        //     $portal_vouchers = [];

        //     foreach ($allPortalInvoices as $invoice) {
        //         $portal_vouchers[] = $invoice['inum'];

        //         if (in_array($invoice['inum'], $rejectedIrns)) {
        //             continue; // skip rejected invoices
        //         }

        //         // Find matching book data
        //         $book_data = Purchase::select('total')
        //             ->where('company_id', Session::get('user_company_id'))
        //             ->where('merchant_gst', $request->gstin)
        //             ->whereBetween('date', [$fyStart, $fyEnd])
        //             ->where('status', '1')
        //             ->where('delete', '0')
        //             ->where(function ($q) use ($invoice) {
        //                 $q->where('voucher_no', $invoice['inum'])
        //                 ->orWhere('gstr2b_invoice_id', $invoice['inum']);
        //             })
        //             ->first();

        //         $book_value = $book_data ? $book_data->total : 0;

        //         // If not found in purchases, check journals
        //         if (!$book_value) {
        //             $journal_book_data = Journal::select('total_amount')
        //                 ->where('company_id', Session::get('user_company_id'))
        //                 ->where('merchant_gst', $request->gstin)
        //                 ->where('claim_gst_status', 'YES')
        //                 ->where('invoice_no', $invoice['inum'])
        //                 ->where('status', '1')
        //                 ->where('delete', '0')
        //                 ->first();

        //             if ($journal_book_data) {
        //                 $book_value = $journal_book_data->total_amount;
        //             }
        //         }

        //         // If mismatch
        //         if ($book_value != $invoice['val']) {
        //             $account = Accounts::select('account_name')
        //                 ->where('company_id', Session::get('user_company_id'))
        //                 ->where('gstin', $invoice['ctin'])
        //                 ->first();

        //             $account_name = $account ? $account->account_name : $invoice['ctin'];

        //             $total_val_on_portal_but_not_in_book1 += $invoice['val'];
        //             $total_txval_on_portal_but_not_in_book += $invoice['txval'];
        //             $total_igst_on_portal_but_not_in_book += $invoice['igst'];
        //             $total_cgst_on_portal_but_not_in_book += $invoice['cgst'];
        //             $total_sgst_on_portal_but_not_in_book += $invoice['sgst'];
        //             $total_cess_on_portal_but_not_in_book += $invoice['cess'];
        //             if(!empty($book_value)){
                        
        //                 $total_book_value_on_portal_but_not_in_book += $book_value;
        //             }
                   

        //             $b2b_invoices_on_portal_but_not_in_book .= "<tr>
        //                 <td>".$account_name."</td>
        //                 <td>".$invoice['inum']."</td>
        //                 <td>".$invoice['dt']."</td>
        //                 <td style='text-align: right;'>".formatIndianNumber($invoice['val'])."</td>
        //                 <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
        //                 <td style='text-align: right'>".formatIndianNumber($invoice['txval'])."</td>
        //                 <td style='text-align: right'>".formatIndianNumber($invoice['igst'])."</td>
        //                 <td style='text-align: right'>".formatIndianNumber($invoice['cgst'])."</td>
        //                 <td style='text-align: right'>".formatIndianNumber($invoice['sgst'])."</td>
        //                 <td style='text-align: right'>".formatIndianNumber($invoice['cess'])."</td>
        //             </tr>";
        //         }
        //     }
        //     if ($total_val_on_portal_but_not_in_book1 > 0) {
        //         $b2b_invoices_on_portal_but_not_in_book .= "<tr>
        //             <td></td>
        //             <th colspan='2' style='text-align: right'><strong>Total</strong></th>
        //             <th style='text-align: right'>".formatIndianNumber($total_val_on_portal_but_not_in_book1)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_book_value_on_portal_but_not_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_txval_on_portal_but_not_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_igst_on_portal_but_not_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_cgst_on_portal_but_not_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_sgst_on_portal_but_not_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_cess_on_portal_but_not_in_book)."</th>
        //         </tr>";
        //     }
        //     // ================================
        //     // ONLY IN BOOK
        //     // ================================
        //     $portal_upper = array_map('strtoupper', $portal_vouchers);
        //     $only_in_book_voucher = array_filter($book_vouchers, function ($val) use ($portal_upper) {
        //         return !in_array(strtoupper($val), $portal_upper);
        //     });

        //     $total_book_value_only_in_book = $total_val_only_in_book1 = $total_txval_only_in_book = 0;
        //     $total_igst_only_in_book = $total_cgst_only_in_book = $total_sgst_only_in_book = $total_cess_only_in_book = 0;
        //     $b2b_invoices_only_in_book = "";

        //     foreach ($only_in_book_voucher as $invoice_no) {
        //         $book_data = Purchase::with(['purchaseSundry', 'account' => function ($q) {
        //                 $q->select('id','account_name');
        //             }])
        //             ->where('voucher_no', $invoice_no)
        //             ->where('company_id', Session::get('user_company_id'))
        //             ->where('merchant_gst', $request->gstin)
        //             ->where('status', '1')
        //             ->whereBetween('date', [$fyStart, $fyEnd])
        //             ->where('delete', '0')
        //             ->first();

        //         $book_value = 0; $igst_amount=0; $cgst_amount=0; $sgst_amount=0;

        //         if ($book_data) {
        //             $book_value = $book_data->total;
        //             if(!empty($book_value)){
        //                 //echo $book_value."<br>";
        //                 $total_book_value_only_in_book += $book_value;
        //             }
                    
        //             $total_txval_only_in_book += $book_data->taxable_amt;

        //             if ($book_data->purchaseSundry && count($book_data->purchaseSundry) > 0) {
        //                 foreach ($book_data->purchaseSundry as $sundry) {
        //                     if ($sundry->nature_of_sundry == "CGST") {
        //                         $total_cgst_only_in_book += $sundry->amount;
        //                         $cgst_amount = $sundry->amount;
        //                     } elseif ($sundry->nature_of_sundry == "SGST") {
        //                         $total_sgst_only_in_book += $sundry->amount;
        //                         $sgst_amount = $sundry->amount;
        //                     } elseif ($sundry->nature_of_sundry == "IGST") {
        //                         $total_igst_only_in_book += $sundry->amount;
        //                         $igst_amount = $sundry->amount;
        //                     }
        //                 }
        //             }
        //         }

        //         $b2b_invoices_only_in_book .= "<tr>
        //             <td>".$book_data->account->account_name."</td>
        //             <td>".$invoice_no."</td>
        //             <td>".date('d-m-Y', strtotime($book_data->date))."</td>
        //             <td style='text-align: right;'>0.00</td>
        //             <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
        //             <td style='text-align: right'>".formatIndianNumber($book_data->taxable_amt)."</td>
        //             <td style='text-align: right'>".formatIndianNumber($igst_amount)."</td>
        //             <td style='text-align: right'>".formatIndianNumber($cgst_amount)."</td>
        //             <td style='text-align: right'>".formatIndianNumber($sgst_amount)."</td>
        //             <td style='text-align: right'>0.00</td>
        //         </tr>";
        //     }
        //     if ($total_book_value_only_in_book > 0) {
        //         $b2b_invoices_only_in_book .= "<tr>
        //             <td></td><td></td>
        //             <th style='text-align: right'>Total</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_val_only_in_book1)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_book_value_only_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_txval_only_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_igst_only_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_cgst_only_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_sgst_only_in_book)."</th>
        //             <th style='text-align: right'>".formatIndianNumber($total_cess_only_in_book)."</th>
        //         </tr>";
        //     }

        // return view('gstReturn/gstr2b_reconcilation',["month"=>$month,"gstin"=>$gstin,"total_val_on_portal_but_not_in_book"=>$total_val_on_portal_but_not_in_book,"total_val_only_in_book"=>$total_val_only_in_book,'total_book_value_only_in_book'=>$total_book_value_only_in_book,'total_val_on_portal_but_not_in_book1'=>$total_val_on_portal_but_not_in_book1]);
        return view('gstReturn/gstr2b_reconcilation',["month"=>$month,"gstin"=>$gstin]);
    }
    public function gstr2bReconciliationDetail(Request $request){
        //Gst Credenatial
        if(!$this->gstCredentials){
            $response = [
                            'success' => false,
                            'data'    => "",
                            'message' => "Api Credentails Not Found ",
                        ];
            return response()->json($response, 200);
        }
        if($this->gstCredentials->status != 1){
            $response = [
                            'success' => false,
                            'data'    => "",
                            'message' => "Api Credentails Not Found ",
                        ];
            return response()->json($response, 200);
        }
        $base_url = $this->gstCredentials->base_url;
        $email_id = $this->gstCredentials->email_id;
        $client_id = $this->gstCredentials->client_id;
        $client_secret = $this->gstCredentials->client_secret;
        $ip_address = $this->gstCredentials->ip_address;
        if($request->type=="only_in_portal" || $request->type=="only_in_book"){
            $RejectedGstr2b = RejectedGstr2b::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstr2b_month',$request->month)
                            ->get();
            $rejectedIrns = $RejectedGstr2b->pluck('invoice_number')->toArray();
            $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->first();
            $gstr2b = json_decode($gstr2b->res_data);
            $b2b_invoices_on_portal_but_not_in_book = "";$total_val_on_portal_but_not_in_book = 0; $total_txval_on_portal_but_not_in_book = 0; $total_igst_on_portal_but_not_in_book = 0; $total_cgst_on_portal_but_not_in_book = 0; $total_sgst_on_portal_but_not_in_book = 0; $total_cess_on_portal_but_not_in_book = 0; $total_book_value_on_portal_but_not_in_book = 0;
            //Only in books but not portal code
            $book_vouchers = Purchase::select('voucher_no')
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->where('merchant_gst',$request->gstin)
                                    ->where('status','1')
                                    ->where('date', 'like', $request->month.'%')
                                    ->where('delete','0')
                                    ->where(function($q) use ($request) {
                                        $q->where('gstr2b_invoice_id',null);
                                        $q->orWhere('gstr2b_invoice_id','');
                                    })                                
                                    ->pluck('voucher_no');
            $book_vouchers = $book_vouchers->toArray();
            $portal_vouchers = [];
            foreach ($gstr2b->data->docdata->b2b as $record) {
                foreach ($record->inv as $key=>$invoice) {
                    $portal_vouchers[] = $invoice->inum;
                    if (in_array($invoice->inum, $rejectedIrns)) {
                        continue; // Skip rejected invoices
                    }
                    $book_data = Purchase::select('total')
                                            ->where('company_id', Session::get('user_company_id'))
                                            ->where('merchant_gst', $request->gstin)
                                            ->where('date', 'like', $request->month.'%')
                                            ->where('status', '1')
                                            ->where('delete', '0')
                                            ->where(function($q) use ($invoice, $request) {
                                                $q->where('voucher_no', $invoice->inum);
                                                $q->orWhere('gstr2b_invoice_id', $invoice->inum); // add your OR here
                                            })
                                            ->first();

                    $book_value = 0;
                    if($book_data){
                        $book_value = $book_data->total;
                    }else{
                        $journal_book_data = Journal::select('total_amount')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('merchant_gst',$request->gstin)
                                            ->where('claim_gst_status','YES')
                                            ->where('invoice_no',$invoice->inum)
                                            ->where('status','1')
                                            ->where('delete','0')
                                            ->first();
                        if($journal_book_data){
                            $book_value = $journal_book_data->total_amount;
                        }
                    }
                    if($book_value!=$invoice->val){
                        $account = Accounts::select('account_name')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('gstin',$record->ctin)
                                ->first();
                        $account_name = '';
                        if ($account) {
                            // ✅ Found in DB
                            $account_name = $account->account_name;
                        } else {
                            // ❌ Not found → Fetch from GST API
                                $curl = curl_init();
                                curl_setopt_array($curl, [
                                    CURLOPT_URL => $base_url."/public/search?email={$email_id}&gstin={$b2b->ctin}",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_TIMEOUT => 30,
                                    CURLOPT_CUSTOMREQUEST => "GET",
                                    CURLOPT_HTTPHEADER => [
                                        'client_id: '.$client_id,
                                        'client_secret: '.$client_secret
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

                        $total_val_on_portal_but_not_in_book += $invoice->val;
                        $total_txval_on_portal_but_not_in_book += $invoice->txval;
                        $total_igst_on_portal_but_not_in_book += $invoice->igst;
                        $total_cgst_on_portal_but_not_in_book += $invoice->cgst;
                        $total_sgst_on_portal_but_not_in_book += $invoice->sgst;
                        $total_cess_on_portal_but_not_in_book += $invoice->cess;
                        $total_book_value_on_portal_but_not_in_book += $book_value;
                        $b2b_invoices_on_portal_but_not_in_book.="<tr>
                                <td>".$account_name."</td>
                                <td>".$invoice->inum."</td>
                                <td>".$invoice->dt."</td>
                                <td style='text-align: right;'>".formatIndianNumber($invoice->val)."</td>
                                <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
                                <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                                <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                                <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                                <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                                <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                                
                            </tr>";
                    }
                }
            }
            if($total_val_on_portal_but_not_in_book>0){
                $b2b_invoices_on_portal_but_not_in_book .= "<tr>
                        <td></td>
                        <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                        <th style='text-align: right'>".formatIndianNumber($total_val_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_book_value_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_txval_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_igst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cgst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_sgst_on_portal_but_not_in_book)."</th>
                        <th style='text-align: right'>".formatIndianNumber($total_cess_on_portal_but_not_in_book)."</th>
                    </tr>";
            }
            $portal_upper = array_map('strtoupper', $portal_vouchers);

            $only_in_book_voucher = array_filter($book_vouchers, function($val) use ($portal_upper) {
                                        return !in_array(strtoupper($val), $portal_upper);
                                    });
            $total_book_value_only_in_book = 0; $total_val_only_in_book = 0; $total_txval_only_in_book = 0; $total_igst_only_in_book = 0; $total_cgst_only_in_book = 0; $total_sgst_only_in_book = 0; $total_cess_only_in_book = 0;
            $b2b_invoices_only_in_book = "";
            foreach ($only_in_book_voucher as $key => $invoice_no) {
                $book_data = Purchase::with(['purchaseSundry','account'=>function($q){
                    $q->select('id','account_name');
                }])->where('voucher_no',$invoice_no)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('merchant_gst',$request->gstin)
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->first();
                // echo "<pre>";
                // print_r($book_data->toArray());die;
                $book_value = 0;$igst_amount=0;$cgst_amount=0;$sgst_amount=0;
                if($book_data){
                    $book_value = $book_data->total;
                    $total_book_value_only_in_book += $book_value;
                    //$total_val_only_in_book += $book_data->total;
                    $total_txval_only_in_book += $book_data->taxable_amt;
                    if($book_data->purchaseSundry && count($book_data->purchaseSundry)>0){
                        foreach($book_data->purchaseSundry as $sundry){
                            if($sundry->nature_of_sundry == "CGST"){
                                $total_cgst_only_in_book += $sundry->amount;
                                $cgst_amount = $sundry->amount;
                            }else if($sundry->nature_of_sundry == "SGST"){
                                $total_sgst_only_in_book += $sundry->amount;
                                $sgst_amount = $sundry->amount;
                            }else if($sundry->nature_of_sundry == "IGST"){
                                $total_igst_only_in_book += $sundry->amount;
                                $igst_amount = $sundry->amount;
                            }
                        }
                    }
                }            
                $b2b_invoices_only_in_book.="<tr>
                    <td>".$book_data->account->account_name."</td>
                    <td>".$invoice_no."</td>
                    <td>".date('d-m-Y',strtotime($book_data->date))."</td>
                    <td style='text-align: right;'>0.00</td>
                    <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
                    <td style='text-align: right'>".formatIndianNumber($book_data->taxable_amt)."</td>
                    <td style='text-align: right'>".formatIndianNumber($igst_amount)."</td>
                    <td style='text-align: right'>".formatIndianNumber($cgst_amount)."</td>
                    <td style='text-align: right'>".formatIndianNumber($sgst_amount)."</td>
                    <td style='text-align: right'>0.00</td>
                </tr>";
            }
            if($total_book_value_only_in_book>0){
                $b2b_invoices_only_in_book.="<tr >
                    <td></td>
                    <td></td>
                    <th style='text-align: right'>Total</th>
                    <th style='text-align: right'>".formatIndianNumber($total_val_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess_only_in_book)."</th>
                </tr>";
            }
        }else if($request->type=="only_in_portal_all" || $request->type=="only_in_book_all"){
             // 1. Get financial year range
            $currentYear = date('Y');
            $currentMonth = date('n'); // 1–12

            if ($currentMonth >= 4) {
                $fyStart = $currentYear . '-04-01';
                $fyEnd   = ($currentYear + 1) . '-03-31';
            } else {
                $fyStart = ($currentYear - 1) . '-04-01';
                $fyEnd   = $currentYear . '-03-31';
            }

            // 2. Rejected invoices
            $RejectedGstr2b = RejectedGstr2b::where('company_gstin', $request->gstin)
                ->where('company_id', Session::get('user_company_id'))
                ->whereBetween('gstr2b_month', [$fyStart, $fyEnd])
                ->get();
            $rejectedIrns = $RejectedGstr2b->pluck('invoice_number')->toArray();

            // 3. GSTR2B data for FY
            $gstr2b = GSTR2B::where('company_gstin', $request->gstin)
                ->where('company_id', Session::get('user_company_id'))
                ->whereBetween('res_month', [$fyStart, $fyEnd])
                ->get();

            // Flatten all portal invoices into one list
            $allPortalInvoices = [];
            foreach ($gstr2b as $monthData) {
                $data = json_decode($monthData->res_data);
                if (!empty($data->data->docdata->b2b)) {
                    foreach ($data->data->docdata->b2b as $record) {
                        foreach ($record->inv as $invoice) {
                            $allPortalInvoices[] = [
                                'ctin' => $record->ctin,
                                'inum' => $invoice->inum,
                                'dt'   => $invoice->dt,
                                'val'  => $invoice->val,
                                'txval'=> $invoice->txval,
                                'igst' => $invoice->igst,
                                'cgst' => $invoice->cgst,
                                'sgst' => $invoice->sgst,
                                'cess' => $invoice->cess,
                            ];
                        }
                    }
                }
            }

            // 4. Book vouchers for FY
            $book_vouchers = Purchase::select('voucher_no')
                ->where('company_id', Session::get('user_company_id'))
                ->where('merchant_gst', $request->gstin)
                ->where('status', '1')
                ->where('date', 'like', $request->month.'%')
                ->where('delete', '0')
                ->where(function ($q) {
                    $q->whereNull('gstr2b_invoice_id')
                    ->orWhere('gstr2b_invoice_id', '');
                })
                ->pluck('voucher_no')
                ->toArray();

            // ================================
            // ONLY IN PORTAL (or mismatched)
            // ================================
            $b2b_invoices_on_portal_but_not_in_book = "";
            $total_val_on_portal_but_not_in_book = $total_txval_on_portal_but_not_in_book = 0;
            $total_igst_on_portal_but_not_in_book = $total_cgst_on_portal_but_not_in_book = 0;
            $total_sgst_on_portal_but_not_in_book = $total_cess_on_portal_but_not_in_book = 0;
            $total_book_value_on_portal_but_not_in_book = 0;

            $portal_vouchers = [];

            foreach ($allPortalInvoices as $invoice) {
                $portal_vouchers[] = $invoice['inum'];

                if (in_array($invoice['inum'], $rejectedIrns)) {
                    continue; // skip rejected invoices
                }

                // Find matching book data
                $book_data = Purchase::select('total')
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('merchant_gst', $request->gstin)
                    ->where('date', 'like', $request->month.'%')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->where(function ($q) use ($invoice) {
                        $q->where('voucher_no', $invoice['inum'])
                        ->orWhere('gstr2b_invoice_id', $invoice['inum']);
                    })
                    ->first();

                $book_value = $book_data ? $book_data->total : 0;

                // If not found in purchases, check journals
                if (!$book_value) {
                    $journal_book_data = Journal::select('total_amount')
                        ->where('company_id', Session::get('user_company_id'))
                        ->where('merchant_gst', $request->gstin)
                        ->where('claim_gst_status', 'YES')
                        ->where('invoice_no', $invoice['inum'])
                        ->where('status', '1')
                        ->where('delete', '0')
                        ->first();

                    if ($journal_book_data) {
                        $book_value += $journal_book_data->total_amount;
                    }
                }

                // If mismatch
                if ($book_value != $invoice['val']) {
                    $account = Accounts::select('account_name')
                        ->where('company_id', Session::get('user_company_id'))
                        ->where('gstin', $invoice['ctin'])
                        ->first();

                    $account_name = '';

                                if ($account) {
                                
                                    // ✅ Found in DB
                                    $account_name = $account->account_name;
                                
                                } else {
                                
                                    // ❌ Not found → Fetch from GST API
                                        $curl = curl_init();
                                
                                        curl_setopt_array($curl, [
                                            CURLOPT_URL => $base_url."/public/search?email={$email_id}&gstin={$b2b->ctin}",
                                            CURLOPT_RETURNTRANSFER => true,
                                            CURLOPT_TIMEOUT => 30,
                                            CURLOPT_CUSTOMREQUEST => "GET",
                                            CURLOPT_HTTPHEADER => [
                                               'client_id: '.$client_id,
                                                'client_secret: '.$client_secret
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


                    $total_val_on_portal_but_not_in_book += $invoice['val'];
                    $total_txval_on_portal_but_not_in_book += $invoice['txval'];
                    $total_igst_on_portal_but_not_in_book += $invoice['igst'];
                    $total_cgst_on_portal_but_not_in_book += $invoice['cgst'];
                    $total_sgst_on_portal_but_not_in_book += $invoice['sgst'];
                    $total_cess_on_portal_but_not_in_book += $invoice['cess'];
                    $total_book_value_on_portal_but_not_in_book += $book_value;

                    $b2b_invoices_on_portal_but_not_in_book .= "<tr>
                        <td>".$account_name."</td>
                        <td>".$invoice['inum']."</td>
                        <td>".$invoice['dt']."</td>
                        <td style='text-align: right;'>".formatIndianNumber($invoice['val'])."</td>
                        <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice['txval'])."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice['igst'])."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice['cgst'])."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice['sgst'])."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice['cess'])."</td>
                    </tr>";
                }
            }

            if ($total_val_on_portal_but_not_in_book > 0) {
                $b2b_invoices_on_portal_but_not_in_book .= "<tr>
                    <td></td>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst_on_portal_but_not_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess_on_portal_but_not_in_book)."</th>
                </tr>";
            }

            // ================================
            // ONLY IN BOOK
            // ================================
            $portal_upper = array_map('strtoupper', $portal_vouchers);
            $only_in_book_voucher = array_filter($book_vouchers, function ($val) use ($portal_upper) {
                return !in_array(strtoupper($val), $portal_upper);
            });

            $total_book_value_only_in_book = $total_val_only_in_book = $total_txval_only_in_book = 0;
            $total_igst_only_in_book = $total_cgst_only_in_book = $total_sgst_only_in_book = $total_cess_only_in_book = 0;
            $b2b_invoices_only_in_book = "";

            foreach ($only_in_book_voucher as $invoice_no) {
                $book_data = Purchase::with(['purchaseSundry', 'account' => function ($q) {
                        $q->select('id','account_name');
                    }])
                    ->where('voucher_no', $invoice_no)
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('merchant_gst', $request->gstin)
                    ->where('status', '1')
                    ->whereBetween('date', [$fyStart, $fyEnd])
                    ->where('delete', '0')
                    ->first();

                $book_value = 0; $igst_amount=0; $cgst_amount=0; $sgst_amount=0;

                if ($book_data) {
                    $book_value = $book_data->total;
                    $total_book_value_only_in_book += $book_value;
                    $total_txval_only_in_book += $book_data->taxable_amt;

                    if ($book_data->purchaseSundry && count($book_data->purchaseSundry) > 0) {
                        foreach ($book_data->purchaseSundry as $sundry) {
                            if ($sundry->nature_of_sundry == "CGST") {
                                $total_cgst_only_in_book += $sundry->amount;
                                $cgst_amount = $sundry->amount;
                            } elseif ($sundry->nature_of_sundry == "SGST") {
                                $total_sgst_only_in_book += $sundry->amount;
                                $sgst_amount = $sundry->amount;
                            } elseif ($sundry->nature_of_sundry == "IGST") {
                                $total_igst_only_in_book += $sundry->amount;
                                $igst_amount = $sundry->amount;
                            }
                        }
                    }
                }

                $b2b_invoices_only_in_book .= "<tr>
                    <td>".$book_data->account->account_name."</td>
                    <td>".$invoice_no."</td>
                    <td>".date('d-m-Y', strtotime($book_data->date))."</td>
                    <td style='text-align: right;'>0.00</td>
                    <td style='text-align: right;'>".formatIndianNumber($book_value)."</td>
                    <td style='text-align: right'>".formatIndianNumber($book_data->taxable_amt)."</td>
                    <td style='text-align: right'>".formatIndianNumber($igst_amount)."</td>
                    <td style='text-align: right'>".formatIndianNumber($cgst_amount)."</td>
                    <td style='text-align: right'>".formatIndianNumber($sgst_amount)."</td>
                    <td style='text-align: right'>0.00</td>
                </tr>";
            }

            if ($total_book_value_only_in_book > 0) {
                $b2b_invoices_only_in_book .= "<tr>
                    <td></td><td></td>
                    <th style='text-align: right'>Total</th>
                    <th style='text-align: right'>".formatIndianNumber($total_val_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_book_value_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst_only_in_book)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess_only_in_book)."</th>
                </tr>";
            }
        }
        return json_encode(array("status"=>true,"only_in_portal"=>$b2b_invoices_on_portal_but_not_in_book,"only_in_book"=>$b2b_invoices_only_in_book));
    }
    public function verifyGst2b(Request $request){
        //echo $request->month;die;
        $update = GSTR2B::where('company_id',Session::get('user_company_id'))
                ->where('company_gstin',$request->gstin)
                ->where('res_month',$request->month)
                ->update([
                        'verify_by_status'=>1,
                        'verify_by'=>Session::get('user_id'),
                        'verify_date'=>date('Y-m-d H:i:s')
                        ]);
        if($update){
             return json_encode(array("status"=>true));
        }
        return json_encode(array("status"=>false));
    }
    
}
