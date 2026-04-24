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
use App\Models\AccountGroups;
use App\Models\ActivityLog;
use App\Models\VoucherSeriesConfiguration;
use App\Helpers\CommonHelper;
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
      Gate::authorize('action-module', 15);
      $input = $request->all();
      $from_date = null;
      $to_date = null;
      // If user selected date range
      if(!empty($input['from_date']) && !empty($input['to_date'])) {
         $from_date = date('d-m-Y', strtotime($input['from_date']));
         $to_date = date('d-m-Y', strtotime($input['to_date']));
         session(['payment_from_date' => $from_date, 'payment_to_date' => $to_date]);
      }elseif (session()->has('payment_from_date') && session()->has('payment_to_date')) {
         $from_date = session('payment_from_date');
         $to_date = session('payment_to_date');
      }
      Session::put('redirect_url', '');
      // Financial Year logic
      $financial_year = Session::get('default_fy');
      $y = explode("-", $financial_year);
      $from = DateTime::createFromFormat('y', $y[0])->format('Y');
      $to = DateTime::createFromFormat('y', $y[1])->format('Y');
      $month_arr = [
         $from . '-04', $from . '-05', $from . '-06', $from . '-07',
         $from . '-08', $from . '-09', $from . '-10', $from . '-11',
         $from . '-12', $to . '-01', $to . '-02', $to . '-03'
      ];
      $com_id = Session::get('user_company_id');
      // Start query
      $query = DB::table('payment_details')
         ->select(
            'payments.series_no',
            'payments.id as pay_id',
            'payments.date',
            'payments.mode as m',
            'accounts.account_name as acc_name',
            'payment_details.*',
            'payments.voucher_no',
            'payments.created_by',
            'payments.approved_by',
            'payments.approved_at',
            'payments.approved_status',
            'created_user.name as created_by_name',
            'approved_user.name as approved_by_name'
         )
         ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
         ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
         ->leftJoin('users as created_user', 'created_user.id', '=', 'payments.created_by')
         ->leftJoin('users as approved_user', 'approved_user.id', '=', 'payments.approved_by')
         ->where('payment_details.company_id', $com_id)
         ->where('payments.delete', '0')
         ->where('payment_details.debit', '!=', '')
         ->where('payment_details.debit', '!=', '0');
      // Apply date filter if dates are provided
      if($from_date && $to_date) {
         $query->whereRaw("
            STR_TO_DATE(payments.date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "','%Y-%m-%d')
            AND STR_TO_DATE(payments.date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "','%Y-%m-%d')
         ");
         $query->orderBy('payments.date', 'asc')
              ->orderBy('payments.voucher_no', 'asc');
      }else{
         // Show last 10 entries if no date is selected
         $query->orderBy('payments.date', 'desc')->orderBy(DB::raw("cast(payments.voucher_no as SIGNED)"), 'desc')
              
              ->limit(10);
      }
      // Fetch data
      $payment = $query->get()->reverse()->values();
      return view('payment/payment')
         ->with('payment', $payment)
         ->with('month_arr', $month_arr)
         ->with('from_date', $from_date)
         ->with('to_date', $to_date);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',82);
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';   
      $com_id = Session::get('user_company_id');
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])                              
                              ->orderBy('account_name')
                              ->get();

                              $sub_group_ids_bank = AccountGroups::where('heading', 7)
                                                                  ->where('heading_type', 'group')
                                                                  ->pluck('id')->toArray();

                     // Step 2: Combine group 8 and its sub-groups
                     $group_ids_bank = array_merge([7], $sub_group_ids_bank);

      $credit_bank_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $group_ids_bank)//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();

     // Step 1: Get sub-group IDs where parent is group 8
                     $sub_group_ids = AccountGroups::where('heading', 8)
                                                   ->where('heading_type', 'group')
                                                   ->pluck('id')->toArray();

                     // Step 2: Combine group 8 and its sub-groups
                     $group_ids = array_merge([8], $sub_group_ids);

                     // Step 3: Fetch accounts under those group IDs
                     $credit_cash_accounts = Accounts::where('delete', '0')
                        ->where('status', '1')
                        ->where('under_group_type', 'group')
                        ->whereIn('company_id', [Session::get('user_company_id'), 0])
                        ->whereIn('under_group', $group_ids)
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
      foreach ($mat_series as $key => $value) {

         $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
            ->where('series', $value->series)
            ->where('configuration_for', 'PAYMENT') 
            ->where('status', '1')
            ->first();

         $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
         $lastNumber = DB::table('payments')
            ->where('company_id', Session::get('user_company_id'))
            ->where('financial_year', $financial_year)
            ->where('series_no', $value->series)
            ->where('delete', '0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));

         if (!$lastNumber) {
            if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
               $next = (int)$series_configuration->invoice_start;
            } else {
               $next = 1;
            }
         } else {
            $next = ((int)$lastNumber) + 1;
         }

         $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d", $next);

         $invoice_prefix = "";
         $manual_enter_invoice_no = "";

         if ($series_configuration) {
            $manual_enter_invoice_no = ($series_configuration->manual_numbering == "YES") ? "1" : "0";
         }

         if ($series_configuration && $series_configuration->manual_numbering == "NO") {

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
               $invoice_prefix .= $series_configuration->prefix_value;
            }

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
               $invoice_prefix .= $series_configuration->separator_1;
            }

            if ($series_configuration->year == "PREFIX TO NUMBER") {

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
               }

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }
            }

            $invoice_prefix .= $mat_series[$key]->invoice_start_from;

            if ($series_configuration->year == "SUFFIX TO NUMBER") {

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
               }
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
               $invoice_prefix .= $series_configuration->separator_3;
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
               $invoice_prefix .= $series_configuration->suffix_value;
            }
         }

         $mat_series[$key]->invoice_prefix = $invoice_prefix;
         $mat_series[$key]->manual_enter_invoice_no = $manual_enter_invoice_no;
      }
      
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      return view('payment/addPayment')->with('fy_start_date', $fy_start_date)->with('fy_end_date', $fy_end_date)->with('party_list', $party_list)->with('credit_bank_accounts', $credit_bank_accounts)->with('credit_cash_accounts', $credit_cash_accounts)->with('date', $bill_date)->with('mat_series', $mat_series);
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
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      
      $series_configuration = VoucherSeriesConfiguration::where('company_id', $com_id)
         ->where('series', $request->input('series_no'))
         ->where('configuration_for', 'PAYMENT')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } else {
         $last_voucher_no = DB::table('payments')
            ->where('company_id', $com_id)
            ->where('series_no', $request->input('series_no'))
            ->where('financial_year', $financial_year)
            ->where('delete', '0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));
         if (!$last_voucher_no) {
            if ($series_configuration && $series_configuration->invoice_start != "") {
               $voucher_no = sprintf("%0" . $number_digit . "d", (int)$series_configuration->invoice_start);
            } else {
               $voucher_no = sprintf("%0" . $number_digit . "d", 1);
            }
         } else {
            $voucher_no = sprintf("%0" . $number_digit . "d", ((int)$last_voucher_no + 1));
         }
      }
      $payment = new Payment;
      $payment->date = $request->input('date');
      $payment->voucher_no_prefix = $request->input('voucher_prefix');
      $payment->voucher_no = $voucher_no;
      $payment->mode = $request->input('mode');
      $payment->series_no = $request->input('series_no');
      $payment->cheque_no = $request->input('cheque_no');
      $payment->long_narration = $request->input('long_narration');
      $payment->company_id = $com_id;
      $payment->created_by = Session::get('user_id');
      $payment->financial_year = $financial_year;
      $payment->save();
      if($payment->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');         
         $narrations = $request->input('narration');
         //Details
         $credit_id = "";$credit_narration = "";
         foreach($types as $key => $type){
            if($type=="Credit"){
               $credit_id = $request->input('account_name')[$key];
               $credit_narration = isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '';
            }
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
         }
         //Account Ledger Entry
         $debit_arr = [];$credit_arr = [];
         foreach($request->input('type') as $key => $type){
            if($type=="Debit"){
               array_push($debit_arr,array(
                  'type' => $type,
                  'account_name' => $request->input('account_name')[$key],
                  'debit' => $request->input('debit')[$key],
                  'credit' => 0,
                  'narration' => isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '',
                  'mapped_account_id' => $credit_id
                  ));
                  //Credit Array
                  $accountName = $request->input('account_name')[$key];
                  $debitValue  = $request->input('debit')[$key];
                  if(isset($credit_arr[$accountName])) {
                        // If already exists, add credit
                        $credit_arr[$accountName]['credit'] += $debitValue;
                  } else {
                        // Otherwise, create new
                        $credit_arr[$accountName] = [
                           'type' => 'Credit',
                           'account_name' => $credit_id,
                           'debit' => 0,
                           'credit' => $debitValue,
                           'narration' => $credit_narration,
                           'mapped_account_id' => $accountName
                        ];
                  }
            }
         }
         $final_arr = array_merge($debit_arr, array_values($credit_arr));
         foreach ($final_arr as $key => $value) {
            $ledger = new AccountLedger();
            $ledger->account_id = $value['account_name'];
            if(isset($value['debit']) && !empty($value['debit']) && $value['debit'] != 0){
               $ledger->debit = $value['debit'];
            }else{
               $ledger->credit = $value['credit'];
            }
            $ledger->series_no = $request->input('series_no');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = $financial_year;
            $ledger->entry_type = 5;
            $ledger->entry_type_id = $payment->id;
            $ledger->entry_narration = $value['narration'];
            $ledger->map_account_id = $value['mapped_account_id'];
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
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
                            

      $sub_group_ids_bank = AccountGroups::where('heading', 7)
                                                                  ->where('heading_type', 'group')
                                                                  ->pluck('id')->toArray();

                     // Step 2: Combine group 8 and its sub-groups
                     $group_ids_bank = array_merge([7], $sub_group_ids_bank);

      $credit_bank_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $group_ids_bank)//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();

     // Step 1: Get sub-group IDs where parent is group 8
                     $sub_group_ids = AccountGroups::where('heading', 8)
                                                   ->where('heading_type', 'group')
                                                   ->pluck('id')->toArray();

                     // Step 2: Combine group 8 and its sub-groups
                     $group_ids = array_merge([8], $sub_group_ids);

                     // Step 3: Fetch accounts under those group IDs
                     $credit_cash_accounts = Accounts::where('delete', '0')
                        ->where('status', '1')
                        ->where('under_group_type', 'group')
                        ->whereIn('company_id', [Session::get('user_company_id'), 0])
                        ->whereIn('under_group', $group_ids)
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
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';   
     
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }

      foreach ($mat_series as $key => $value) {

         $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
            ->where('series', $value->series)
            ->where('configuration_for', 'PAYMENT')
            ->where('status', '1')
            ->first();

         if ($payment->series_no == $value->series) {

            $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
            $currentNumber = (int)$payment->voucher_no;
            $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d", $currentNumber);

         } else {

            $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
            $lastNumber = DB::table('payments')
               ->where('company_id', Session::get('user_company_id'))
               ->where('financial_year', $financial_year)
               ->where('series_no', $value->series)
               ->where('delete', '0')
               ->max(DB::raw("cast(voucher_no as SIGNED)"));

            if (!$lastNumber) {
               if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
                  $next = (int)$series_configuration->invoice_start;
               } else {
                  $next = 1;
               }
            } else {
               $next = ((int)$lastNumber) + 1;
            }

            $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d", $next);
         }

         $invoice_prefix = "";
         $manual_enter_invoice_no = "";

         if (!$series_configuration) {
            $manual_enter_invoice_no = "";
         } else if ($series_configuration->manual_numbering == "YES") {
            $manual_enter_invoice_no = "1";
         } else {
            $manual_enter_invoice_no = "0";
         }

         if ($series_configuration && $series_configuration->manual_numbering == "NO") {

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
               $invoice_prefix .= $series_configuration->prefix_value;
            }

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
               $invoice_prefix .= $series_configuration->separator_1;
            }

            if ($series_configuration->year == "PREFIX TO NUMBER") {

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
               }

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }
            }

            $invoice_prefix .= $mat_series[$key]->invoice_start_from;

            if ($series_configuration->year == "SUFFIX TO NUMBER") {

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
               }
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
               $invoice_prefix .= $series_configuration->separator_3;
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
               $invoice_prefix .= $series_configuration->suffix_value;
            }
         }

         $mat_series[$key]->invoice_prefix = $invoice_prefix;
         $mat_series[$key]->manual_enter_invoice_no = $manual_enter_invoice_no;
      }
      return view('payment/editPayment')->with('fy_start_date', $fy_start_date)->with('fy_end_date', $fy_end_date)->with('payment', $payment)->with('party_list', $party_list)->with('payment_detail', $payment_detail)->with('credit_bank_accounts', $credit_bank_accounts)->with('credit_cash_accounts', $credit_cash_accounts)->with('mat_series', $mat_series);
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
      $oldSnapshot = [
         'payment' => $payment->toArray(),
         'details' => PaymentDetails::where('payment_id', $payment->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $payment->date = $request->input('date');
      $payment->voucher_no_prefix = $request->input('voucher_prefix');
      $series_changed = ($payment->series_no != $request->input('series_no'));
      $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
         ->where('series', $request->input('series_no'))
         ->where('configuration_for', 'PAYMENT')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      $voucher_no = $payment->voucher_no;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } elseif ($series_changed) {
         $last_voucher_no = DB::table('payments')
            ->where('company_id', Session::get('user_company_id'))
            ->where('series_no', $request->input('series_no'))
            ->where('financial_year', $financial_year)
            ->where('delete', '0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));
         if (!$last_voucher_no) {
            if ($series_configuration && $series_configuration->invoice_start != "") {
               $voucher_no = sprintf("%0" . $number_digit . "d", (int)$series_configuration->invoice_start);
            } else {
               $voucher_no = sprintf("%0" . $number_digit . "d", 1);
            }
         } else {
            $voucher_no = sprintf("%0" . $number_digit . "d", ((int)$last_voucher_no + 1));
         }
      }
      $payment->voucher_no = $voucher_no;
      $payment->mode = $request->input('mode');
      $payment->cheque_no = $request->input('cheque_no');
      $payment->series_no = $request->input('series_no');
      $payment->long_narration = $request->input('long_narration');
      $payment->updated_by = Session::get('user_id');
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
      //Details
      $credit_id = "";$credit_narration = "";
      foreach($types as $key => $type) {
         if($type=="Credit"){
            $credit_id = $request->input('account_name')[$key];
            $credit_narration = isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '';
         }
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
      }
      //Account Ledger Entry
      $debit_arr = [];$credit_arr = [];
      foreach($request->input('type') as $key => $type){
         if($type=="Debit"){
            array_push($debit_arr,array(
               'type' => $type,
               'account_name' => $request->input('account_name')[$key],
               'debit' => $request->input('debit')[$key],
               'credit' => 0,
               'narration' => isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '',
               'mapped_account_id' => $credit_id
               ));
               //Credit Array
               $accountName = $request->input('account_name')[$key];
               $debitValue  = $request->input('debit')[$key];
               if(isset($credit_arr[$accountName])) {
                     // If already exists, add credit
                     $credit_arr[$accountName]['credit'] += $debitValue;
               } else {
                     // Otherwise, create new
                     $credit_arr[$accountName] = [
                        'type' => 'Credit',
                        'account_name' => $credit_id,
                        'debit' => 0,
                        'credit' => $debitValue,
                        'narration' => $credit_narration,
                        'mapped_account_id' => $accountName
                     ];
               }
         }
      }
      $final_arr = array_merge($debit_arr, array_values($credit_arr));
      foreach ($final_arr as $key => $value) {
         $ledger = new AccountLedger();
         $ledger->account_id = $value['account_name'];
         if(isset($value['debit']) && !empty($value['debit']) && $value['debit'] != 0){
            $ledger->debit = $value['debit'];
         }else{
            $ledger->credit = $value['credit'];
         }
         $ledger->series_no = $request->input('series_no');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 5;
         $ledger->entry_type_id = $payment->id;
         $ledger->entry_narration = $value['narration'];
         $ledger->map_account_id = $value['mapped_account_id'];
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
      }
      $newSnapshot = [
         'payment' => Payment::find($payment->id)->toArray(),
         'details' => PaymentDetails::where('payment_id', $payment->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];

      ActivityLog::create([
         'module_type' => 'payment',
         'module_id'   => $payment->id,
         'action'      => 'edit',
         'old_data'    => $oldSnapshot,
         'new_data'    => $newSnapshot,
         'action_by'   => Session::get('user_id'),
         'company_id'  => Session::get('user_company_id'),
         'action_at'   => now(),
      ]);
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
      $oldSnapshot = [
         'payment' => $payment->toArray(),
         'details' => PaymentDetails::where('payment_id', $payment->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];
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
                        ActivityLog::create([
                           'module_type' => 'payment',
                           'module_id'   => $payment->id,
                           'action'      => 'delete',
                           'old_data'    => $oldSnapshot,
                           'new_data'    => null,
                           'action_by'   => Session::get('user_id'),
                           'company_id'  => Session::get('user_company_id'),
                           'action_at'   => now(),
                        ]);
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
                $data = array_map('trim', $data);
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
             $data = array_map('trim', $data);
            if($data[0]=="" && $data[1]=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;                  
            }
            if($data[0]!="" && $data[1]!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"remark"=>$remark,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $txn_arr = [];
               $credit_count = 0;
               $error_arr = [];
               $bill_date = $data[0];
               $series = $data[1];
               $bill_no = $data[2];
               $mode = $data[3];
               $remark = $data[4];
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
            $account = $data[5];
            $check_account = Accounts::where('account_name',trim($account))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_account){
               array_push($error_arr, 'Account Name '.$account.' Not Found - Row '.$index);
            }
            $debit = $data[6];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[7];
            $credit = trim(str_replace(",","",$credit));
            if($debit=="" && $credit==""){
               array_push($error_arr, 'Debit/Credit Cannot - Row '.$index);
            }
            if($credit!="" && $credit!=0){
               $credit_count++;
            }
            if($credit_count>1){
               array_push($error_arr, 'More than one credit entry found - Row '.$index);
            }
            if($check_account){
               array_push($txn_arr,array("account"=>$check_account->id,"debit"=>$debit,"credit"=>$credit));
            }else{
               array_push($txn_arr,array("account"=>$account,"debit"=>$debit,"credit"=>$credit));
            }            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"remark"=>$remark,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
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
               $remark = $value['remark'];
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
               $payment->long_narration = $remark;
               $payment->company_id = Session::get('user_company_id');
               $payment->financial_year = $financial_year;
               $i = 0;
               if($payment->save()){
                  $credit_id = "";$credit_narration = "";
                  foreach($txn_arr as $key => $data){
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $type = "Debit";
                     }else{
                        $type = "Credit";
                     }
                     if($type=="Credit"){
                        $credit_id = $data['account'];
                        $credit_narration = '';
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
                  }
                  //Account Ledger Entry
                  $debit_arr = [];$credit_arr = [];
                  foreach($txn_arr as $key => $data){
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $type = "Debit";
                     }else{
                        $type = "Credit";
                     }
                     if($type=="Debit"){
                        array_push($debit_arr,array(
                           'type' => $type,
                           'account_name' => $data['account'],
                           'debit' => $data['debit'],
                           'credit' => 0,
                           'narration' =>'',
                           'mapped_account_id' => $credit_id
                           ));
                           //Credit Array
                           $accountName = $data['account'];
                           $debitValue  = $data['debit'];
                           if(isset($credit_arr[$accountName])) {
                              // If already exists, add credit
                              $credit_arr[$accountName]['credit'] += $debitValue;
                           } else {
                                 // Otherwise, create new
                                 $credit_arr[$accountName] = [
                                    'type' => 'Credit',
                                    'account_name' => $credit_id,
                                    'debit' => 0,
                                    'credit' => $debitValue,
                                    'narration' => $credit_narration,
                                    'mapped_account_id' => $accountName
                                 ];
                           }
                     }
                  }
                  $final_arr = array_merge($debit_arr, array_values($credit_arr));
                  foreach ($final_arr as $key => $value) {
                     $ledger = new AccountLedger();
                     $ledger->account_id = $value['account_name'];
                     if(isset($value['debit']) && !empty($value['debit']) && $value['debit'] != 0){
                        $ledger->debit = $value['debit'];
                     }else{
                        $ledger->credit = $value['credit'];
                     }
                     $ledger->series_no = $series;
                     $ledger->txn_date = date('Y-m-d',strtotime($bill_date));
                     $ledger->company_id = Session::get('user_company_id');
                     $ledger->financial_year = Session::get('default_fy');
                     $ledger->entry_type = 5;
                     $ledger->entry_type_id = $payment->id;
                     $ledger->entry_narration = $value['narration'];
                     $ledger->map_account_id = $value['mapped_account_id'];
                     $ledger->created_by = Session::get('user_id');
                     $ledger->created_at = date('d-m-Y H:i:s');
                     $ledger->save();
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
   public function exportPaymentView()
   {
      return view('payment.export_payment');
   }



  public function exportPayment(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'required|date',
    ]);

    $exportType = $request->export_type;

    $company_id = Session::get('user_company_id');
    $from = $request->from_date;
    $to   = $request->to_date;

    $payments = DB::table('payments')
        ->where('company_id', $company_id)
        ->where('delete', '0')
        ->whereBetween('date', [$from, $to])
        ->orderBy('date', 'asc')
        ->get();

    $filename = "payment_export_{$from}_to_{$to}.csv";

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($payments, $company_id, $exportType) {

        $out = fopen('php://output', 'w');

        // ✅ HEADER
        if ($exportType == 'old') {
            fputcsv($out, [
                'Series',
                'Voucher No',
                'Date',
                'Party Name',
                'Party Alias',
                'Amount DR',
                'Amount CR',
                'Bank'
            ]);
        } else {
            fputcsv($out, [
                'vch_series',
                'vch/bill_date',
                'vch/bill_no',
                'party_name',
                'acc_alias',
                'amount_dr',
                'payment/receipt_mode',
                'long_narration1'
            ]);
        }

        foreach ($payments as $p) {

            $details = DB::table('payment_details')
                ->where('payment_id', $p->id)
                ->where('company_id', $company_id)
                ->where('delete', '0')
                ->get();

            if ($details->isEmpty()) continue;

            // ✅ GET BANK FROM CREDIT ENTRY
            $creditRow = $details->firstWhere('type', 'Credit');

            $bankName = "";
            if ($creditRow) {
                $bankAcc = DB::table('accounts')
                    ->where('id', $creditRow->account_name)
                    ->first();

                $bankName = $bankAcc->account_name ?? "";
            }

            // ================= OLD EXPORT =================
            if ($exportType == 'old') {

                foreach ($details as $d) {

                    $acc = DB::table('accounts')
                        ->where('id', $d->account_name)
                        ->first();

                    fputcsv($out, [
                        $p->series_no,
                        $p->voucher_no,
                        "'" . $p->date,
                        $acc->account_name ?? '',
                        $acc->id ?? '',
                        $d->debit,
                        $d->credit,
                        $bankName
                    ]);
                }

            }

            // ================= NEW EXPORT (FIXED) =================
            else {

                // ✅ ONLY DEBIT ENTRIES (NO CREDIT ROW)
                foreach ($details->where('type', 'Debit') as $d) {

                    $acc = DB::table('accounts')
                        ->where('id', $d->account_name)
                        ->first();

                    fputcsv($out, [
                        $p->series_no,
                        date('Y-m-d', strtotime($p->date)), // better format
                        $p->voucher_no,
                        $acc->account_name ?? '',          // party name
                        "",
                        $d->debit,
                        $bankName,                         // 🔥 bank repeated in every row
                        $d->narration ?? ''
                    ]);
                }
            }
        }

        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}


}
