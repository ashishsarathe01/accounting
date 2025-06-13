<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\Payment;
use App\Models\PaymentDetails;
use App\Models\AccountLedger;
use App\Models\GstBranch;
use App\Models\Companies;
use DB;
use Session;
use DateTime;
use Gate;
class PaymentController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
      Gate::authorize('action-module',15);
    $input = $request->all();
          // Default date range (first day of current month to today)
    $from_date = session('payment_from_date', "01-" . date('m-Y'));
    $to_date = session('payment_to_date', date('d-m-Y'));

    // Check if user has selected a date range
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        
        // Store in session so it persists after refresh
        session(['payment_from_date' => $from_date, 'payment_to_date' => $to_date]);
    }
      Session::put('redirect_url','');
      $financial_year = Session::get('default_fy');      
      $y =  explode("-",$financial_year);
      $from = $y[0];
      $from = DateTime::createFromFormat('y', $from);
      $from = $from->format('Y');
      $to = $y[1];
      $to = DateTime::createFromFormat('y', $to);
      $to = $to->format('Y');
      $month_arr = array($from.'-04',$from.'-05',$from.'-06',$from.'-07',$from.'-08',$from.'-09',$from.'-10',$from.'-11',$from.'-12',$to.'-01',$to.'-02',$to.'-03');
      $com_id = Session::get('user_company_id');
      $payment = DB::table('payment_details')
                     ->select('payments.series_no','payments.id as pay_id', 'payments.date','payments.mode as m', 'accounts.account_name as acc_name', 'payment_details.*','payments.voucher_no')
                     ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
                     ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
                     ->where('payment_details.company_id', $com_id)
                     ->whereRaw("STR_TO_DATE(payments.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(payments.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
                     ->where('payments.delete','0')
                     ->where('payment_details.debit','!=','')
                     ->where('payment_details.debit','!=','0')
                     ->orderBy('payments.date', 'asc')
                     ->orderBy('payments.voucher_no','asc')
                     ->get();
        return view('payment/payment')->with('payment', $payment)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',82);
      $financial_year = Session::get('default_fy');
      $com_id = Session::get('user_company_id');
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])                              
                              ->orderBy('account_name')
                              ->get();
      $credit_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', [7,8])//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();

      $bill_date = Payment::where('company_id',Session::get('user_company_id'))
                           ->where('delete','0')
                           ->where('financial_year',$financial_year)
                           ->max('date');
      if(!$bill_date){
         $bill_date = date('Y-m-d');
         if(date('m')<=3){
            $current_year = (date('y')-1) . '-' . date('y');
         }else{
            $current_year = date('y') . '-' . (date('y') + 1);
         }
         if($financial_year!=$current_year){
            $y =  explode("-",$financial_year);
            $bill_date = $y[1]."-03-31";
            $bill_date = date('Y-m-d',strtotime($bill_date));
         }
      }
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();

         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         } 
      }

      
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      return view('payment/addPayment')->with('party_list', $party_list)->with('credit_accounts', $credit_accounts)->with('date', $bill_date)->with('mat_series', $mat_series);
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      Gate::authorize('action-module',82);
      $com_id = Session::get('user_company_id');
      $financial_year = Session::get('default_fy');
      $payment = new Payment;
      $payment->date = $request->input('date');
      $payment->voucher_no = $request->input('voucher_no');
      $payment->mode = $request->input('mode');
      $payment->series_no = $request->input('series_no');
      $payment->cheque_no = $request->input('cheque_no');
      $payment->long_narration = $request->input('long_narration');
      $payment->company_id = $com_id;
      $payment->financial_year = $financial_year;
      $payment->save();
      $i=0;
      if($payment->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');
         
         $narrations = $request->input('narration');
         foreach($types as $key => $type){
            $paytype = new PaymentDetails;
            $paytype->payment_id = $payment->id;
            $paytype->company_id = $com_id;
            $paytype->type = $type;
            $paytype->account_name = $account_names[$key];
            $paytype->debit = isset($debits[$key]) ? $debits[$key] : 0;
            $paytype->credit = isset($credits[$key]) ? $credits[$key] : 0;
            $paytype->narration = isset($narrations[$key]) ? $narrations[$key] : '';  // safe handling
            $paytype->status = '1';
            $paytype->save();
         
            //ADD DATA IN Customer ACCOUNT
            if($i==0){
               $map_account_id = $account_names['1'];
            }else{
               $map_account_id = $account_names['0'];
            }
            $ledger = new AccountLedger();
            $ledger->account_id = $account_names[$key];
            if(isset($debits[$key]) && !empty($debits[$key])){
               $ledger->debit = $debits[$key];
            }else{
               $ledger->credit = $credits[$key];
            }            
            $ledger->series_no = $request->input('series_no');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 5;
            $ledger->entry_type_id = $payment->id;
            $ledger->entry_type_detail_id = $paytype->id;
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
         session(['previous_url_payment' => URL::previous()]);
         return redirect('payment')->withSuccess('Payment voucher added successfully!');
      }else{
         $this->failedMessage();
      }
   }
   public function edit($id){
      Gate::authorize('action-module',55);
      $com_id = Session::get('user_company_id');
      $payment = Payment::find($id);
      $payment_detail = PaymentDetails::where('payment_id', '=', $id)->where('delete', '=', '0')->get();
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])                              
                              ->orderBy('account_name')
                              ->get();
      $credit_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', [7,8])//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      return view('payment/editPayment')->with('payment', $payment)->with('party_list', $party_list)->with('payment_detail', $payment_detail)->with('credit_accounts', $credit_accounts)->with('mat_series', $mat_series);
   }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      Gate::authorize('action-module',55);
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',

      ], [
         'date.required' => 'Date is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $payment =  Payment::find($request->payment_id);
      $payment->date = $request->input('date');
      $payment->voucher_no = $request->input('voucher_no');
      $payment->mode = $request->input('mode');
      $payment->cheque_no = $request->input('cheque_no');
      $payment->series_no = $request->input('series_no');
      $payment->long_narration = $request->input('long_narration');
      $payment->save();
      $payment_detail = PaymentDetails::where('payment_id', '=', $request->payment_id)->delete();
      AccountLedger::where('entry_type',5)
                     ->where('entry_type_id',$request->payment_id)
                     ->delete();
      $types = $request->input('type');
      $account_names = $request->input('account_name');
      $debits = $request->input('debit');
      $credits = $request->input('credit');
      $narrations = $request->input('narration');
      $i=0;
      foreach($types as $key => $type) {
         $paytype = new PaymentDetails;
         $paytype->payment_id = $request->payment_id;
         $paytype->company_id = Session::get('user_company_id');
         $paytype->type = $type;
         $paytype->account_name = $account_names[$key];
         $paytype->debit = isset($debits[$key]) ? $debits[$key] : 0;
         $paytype->credit = isset($credits[$key]) ? $credits[$key] : 0;         
         $paytype->narration = $narrations[$key];
         $paytype->status = '1';
         $paytype->save();
         //ADD DATA IN Customer ACCOUNT
         if($i==0){
            $map_account_id = $account_names['1'];
         }else{
            $map_account_id = $account_names['0'];
         }
         $ledger = new AccountLedger();
         $ledger->account_id = $account_names[$key];
         if(isset($debits[$key]) && !empty($debits[$key])){
            $ledger->debit = $debits[$key];
         }else{
            $ledger->credit = $credits[$key];
         }
         $ledger->series_no = $request->input('series_no');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 5;
         $ledger->entry_type_id = $payment->id;
         $ledger->entry_type_detail_id = $paytype->id;
         $ledger->map_account_id = $map_account_id;
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         $i++;
      }
      if(!empty(Session::get('redirect_url'))){
         return redirect(Session::get('redirect_url'));
      }else{
         return redirect('payment')->withSuccess('Payment detail updated successfully!');
      }
      
   }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
   public function delete(Request $request){
      Gate::authorize('action-module',56);
      $payment =  Payment::find($request->payment_id);
      $payment->delete = '1';
      $payment->deleted_at = Carbon::now();
      $payment->deleted_by = Session::get('user_id');
      $payment->update();
      if($payment){
         PaymentDetails::where('payment_id',$request->payment_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',5)
                        ->where('entry_type_id',$request->payment_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         return redirect('payment')->withSuccess('Payment deleted successfully!');
      }
   }
   public function paymentImportView(Request $request){      
      return view('payment/payment_view');
   }
   public function paymentImportProcess(Request $request) {       
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;
      $financial_year = Session::get('default_fy');
      $fy = explode('-',$financial_year);
      $from_date = $fy[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $fy[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      $company_data = Companies::where('id', Session::get('user_company_id'))->first(); 
      $already_exists_error_arr = [];
      $already_exists_item_arr = [];
      $error_arr = [];
      $data_arr = [];
      $all_error_arr = [];
      $mode_arr = ['NEFT','RGTS','IMPS','CHEQUE','CASH'];
      if($duplicate_voucher_status==0){
         $file = $request->file('csv_file');  
         $filePath = $file->getRealPath();      
         $final_result = array();
         if(($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ",");
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $index = 1;
            $series_no = "";
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
               if($data[0]!="" && $data[1]!="" && $data[2]!=""){                  
                  $series = $data[1];
                  $bill_no = $data[2];
                  $payment = Payment::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($payment){
                     array_push($already_exists_error_arr, 'Payment on bill - '.$bill_no.' already exists');
                  }
               }
            }
         }
         if(count($already_exists_error_arr)>0){
            $res = array(
               'status' => false,
               'data' => $already_exists_error_arr,
               "message"=>"Already Exists."
            );
            return json_encode($res);
         }
      }
      if($company_data->gst_config_type == "single_gst"){
         $gst_data = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$gst_data[0]->id])
                           ->get();
         if(count($branch)>0){
            $gst_data = $gst_data->merge($branch);
         }         
      }else if($company_data->gst_config_type == "multiple_gst"){
         $gst_data = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($gst_data as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $gst_data = $gst_data->merge($branch);
            }
         }         
      }
      foreach ($gst_data as $key => $value) {
         $series_arr[] = $value->series;
         $material_center_arr[] = $value->mat_center;
         $gst_no_arr[] = $value->gst_no;
      }
      $bill_date = "";
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_row = $total_row - 1;
         $success_row = 0;
         $index = 1;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if($data[0]=="" && $data[1]=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;                  
            }
            if($data[0]!="" && $data[1]!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $txn_arr = [];
               $error_arr = [];
               $bill_date = $data[0];
               $series = $data[1];
               $bill_no = $data[2];
               $mode = $data[3];
               if($mode!=""){
                  if(!in_array($mode,$mode_arr)){
                     array_push($error_arr, "Mode should be ['NEFT','IMPS','RTGS','CASH','CHEQUE'] - Row ".$index);
                  }
               }
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($bill_date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($bill_date)))){                  
                  array_push($error_arr, 'Date '.$bill_date.' Not In Financial Year - Row '.$index);                  
               }
               if(!in_array($series, $series_arr)){
                  array_push($error_arr, 'Series No. '.$series.' Not Found - Row '.$index); 
               }
               if($duplicate_voucher_status!=2 && !empty($bill_no)){
                  $check_payment = Payment::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($check_payment){
                     array_push($error_arr, 'Payment on bill - '.$bill_no.' already exists');
                  }
               }
            }
            $account = $data[4];
            $check_account = Accounts::where('account_name',trim($account))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_account){
               array_push($error_arr, 'Account Name '.$account.' Not Found - Row '.$index);
            }
            $debit = $data[5];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[6];
            $credit = trim(str_replace(",","",$credit));
            if($debit=="" && $credit==""){
               array_push($error_arr, 'Debit/Credit Cannot - Row '.$index);
            }
            if($check_account){
               array_push($txn_arr,array("account"=>$check_account->id,"debit"=>$debit,"credit"=>$credit));
            }else{
               array_push($txn_arr,array("account"=>$account,"debit"=>$debit,"credit"=>$credit));
            }            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
            }   
            $index++;
         }
         fclose($handle);
         $total_invoice_count = count($data_arr);
         // echo "<pre>";
         // print_r($data_arr);
         // die;
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         if(count($data_arr)>0){
            foreach ($data_arr as $key => $value) {
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }               
               $bill_date = $value['bill_date'];
               $series = $value['series'];
               $bill_no = $value['bill_no'];
               $mode = $value['mode'];
               $txn_arr = $value['txn_arr'];
               if($mode=="CHEQUE"){

                  $success_invoice_count++;
                  continue;
               }
               if($duplicate_voucher_status==2){
                  $check_pay = Payment::select('id')
                                             ->where('voucher_no',$bill_no)
                                             ->where('series_no',trim($series))
                                             ->where('financial_year',$financial_year)
                                             ->where('company_id',trim(Session::get('user_company_id')))
                                             ->first();
                  if($check_pay){              
                     $updated_payment = Payment::find($check_pay->id);
                     $updated_payment->delete = '1';
                     $updated_payment->deleted_at = Carbon::now();
                     $updated_payment->deleted_by = Session::get('user_id');
                     $updated_payment->update();
                     if($updated_payment){
                        PaymentDetails::where('payment_id',$check_pay->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',5)
                        ->where('entry_type_id',$check_pay->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                     }
                  }                  
               }
               if($mode=="IMPS" || $mode=="NEFT" || $mode=="RTGS"){
                  $mode = 0;
               }else if($mode=="CASH"){
                  $mode = 1;
               }else{
                  $mode = 0;
               }
               $payment = new Payment;
               $payment->date = date('Y-m-d',strtotime($bill_date));
               $payment->voucher_no = $bill_no;
               $payment->mode = $mode;
               $payment->series_no = $series;  
               $payment->company_id = Session::get('user_company_id');
               $payment->financial_year = $financial_year;
               $i = 0;
               if($payment->save()){
                  foreach($txn_arr as $key => $data){
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $type = "Debit";
                     }else{
                        $type = "Credit";
                     }
                     $paytype = new PaymentDetails;
                     $paytype->payment_id = $payment->id;
                     $paytype->company_id = Session::get('user_company_id');;
                     $paytype->type = $type;
                     $paytype->account_name = $data['account'];
                     $paytype->debit = $data['debit'];
                     $paytype->credit = $data['credit'];
                     $paytype->status = '1';
                     $paytype->save();
                     //ADD DATA IN Customer ACCOUNT
                     if($i==0){
                        $map_account_id = $txn_arr[1]['account'];
                     }else{
                        $map_account_id = $txn_arr[0]['account'];
                     }                    
                     $ledger = new AccountLedger();
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $ledger->debit = $data['debit'];
                     }else{
                        $ledger->credit = $data['credit'];
                     }
                     $ledger->series_no = $series;
                     $ledger->account_id = $data['account'];                                 
                     $ledger->txn_date = date('Y-m-d',strtotime($bill_date));
                     $ledger->company_id = Session::get('user_company_id');
                     $ledger->financial_year = Session::get('default_fy');
                     $ledger->entry_type = 5;
                     $ledger->entry_type_id = $payment->id;
                     $ledger->entry_type_detail_id = $paytype->id;
                     $ledger->map_account_id = $map_account_id;
                     $ledger->created_by = Session::get('user_id');
                     $ledger->created_at = date('d-m-Y H:i:s');
                     $ledger->save();
                     $i++;
                  }
                  $success_invoice_count++;
               }         
            }
         }
      }
      $res = array("total_count"=>$total_invoice_count,"success_count"=>$success_invoice_count,"failed_count"=>$failed_invoice_count,"error_message"=>$all_error_arr);
      $res = array(
         'status' => true,
         'data' => $res,
         "message"=>"Uploaded Successfully."
      );
      return json_encode($res);
      
   }
}
