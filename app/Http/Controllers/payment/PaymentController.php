<?php

namespace App\Http\Controllers\payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

class PaymentController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
      $input = $request->all();
      $from_date = "01-".date('m-Y');
      $to_date = date('d-m-Y');
      if(!empty($input['from_date']) && !empty($input['to_date'])){
         $from_date = date('d-m-Y',strtotime($input['from_date']));
         $to_date = date('d-m-Y',strtotime($input['to_date']));
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
                     ->select('payments.series_no','payments.id as pay_id', 'payments.date','payments.mode as m', 'accounts.account_name as acc_name', 'payment_details.*')
                     ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
                     ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
                     ->where('payment_details.company_id', $com_id)
                     ->whereRaw("STR_TO_DATE(payments.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(payments.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
                     ->where('payments.delete','0')
                     ->where('payment_details.debit','!=','')
                     ->where('payment_details.debit','!=','0')
                     ->orderBy('payments.date', 'asc')
                     ->get();
        return view('payment/payment')->with('payment', $payment)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      $financial_year = Session::get('default_fy');
      $com_id = Session::get('user_company_id');
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])                              
                              ->orderBy('account_name')
                              ->get();
      $credit_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)) {
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
      return view('payment/addPayment')->with('party_list', $party_list)->with('credit_accounts', $credit_accounts)->with('date', $bill_date)->with('mat_series', $mat_series);
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
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
            $paytype->company_id = Session::get('user_company_id');;
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
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 5;
            $ledger->entry_type_id = $payment->id;
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
         return redirect('payment')->withSuccess('Payment voucher added successfully!');
      }else{
         $this->failedMessage();
      }
   }
   public function edit($id){
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
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', [7,8])//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)) {
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
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
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 5;
         $ledger->entry_type_id = $payment->id;
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
}
