<?php

namespace App\Http\Controllers\receipt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\Accounts;
use App\Models\Receipt;
use App\Models\ReceiptDetails;
use App\Models\AccountLedger;
use App\Models\AccountGroups;
use App\Models\Companies;
use App\Models\GstBranch;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
use Gate;
class ReceiptController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    Gate::authorize('action-module', 16);
    $input = $request->all();
    
    $from_date = null;
    $to_date = null;

    // Date selection from input or session
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['receipt_from_date' => $from_date, 'receipt_to_date' => $to_date]);
    } elseif (session()->has('receipt_from_date') && session()->has('receipt_to_date')) {
        $from_date = session('receipt_from_date');
        $to_date = session('receipt_to_date');
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

    // Base query
    $query = DB::table('receipt_details')
        ->select(
            'receipts.series_no',
            'receipts.id as rec_id',
            'receipts.date',
            'accounts.account_name as acc_name',
            'receipts.mode as m',
            'receipt_details.*',
            'receipts.voucher_no'
        )
        ->join('receipts', 'receipt_details.receipt_id', '=', 'receipts.id')
        ->join('accounts', 'receipt_details.account_name', '=', 'accounts.id')
        ->where('receipt_details.company_id', $com_id)
        ->where('receipts.delete', '0')
        ->where('receipt_details.credit', '!=', '')
        ->where('receipt_details.credit', '!=', '0');

    // If date range is selected, apply filtering
    if ($from_date && $to_date) {
        $query->whereRaw("
            STR_TO_DATE(receipts.date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "','%Y-%m-%d')
            AND STR_TO_DATE(receipts.date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "','%Y-%m-%d')
        ");
        $query->orderBy('receipts.date', 'asc')
              ->orderBy('receipts.voucher_no', 'asc');
    } else {
        // No date filter: Show last 10 entries
        $query->orderBy(DB::raw("cast(receipts.voucher_no as SIGNED)"), 'desc')
              ->orderBy('receipts.date', 'desc')
              ->limit(10);
    }

    $receipt = $query->get()->reverse()->values();

    return view('receipt/receipt')
        ->with('receipt', $receipt)
        ->with('month_arr', $month_arr)
        ->with("from_date", $from_date)
        ->with("to_date", $to_date);
}

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',84);
      $financial_year = Session::get('default_fy');
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

      $debit_bank_accounts = Accounts::where('delete', '=', '0')
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

                     $debit_cash_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $group_ids)//BANK ACCOUNTS,CASH-IN-HAND
                              ->orderBy('account_name')
                              ->get();


      $bill_date = Receipt::where('company_id',Session::get('user_company_id'))
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
      return view('receipt/addReceipt')->with('party_list', $party_list)->with('debit_bank_accounts', $debit_bank_accounts)->with('debit_cash_accounts', $debit_cash_accounts)->with('date', $bill_date)->with('mat_series', $mat_series);
   }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){  
      Gate::authorize('action-module',84);
      $financial_year = Session::get('default_fy');      
      $receipt = new Receipt;
      $receipt->date = $request->input('date');
      $receipt->voucher_no = $request->input('voucher_no');
      $receipt->mode = $request->input('mode');
      $receipt->series_no = $request->input('series_no');
      $receipt->cheque_no = $request->input('cheque_no');
      $receipt->long_narration = $request->input('long_narration');
      $receipt->company_id = Session::get('user_company_id');
      $receipt->financial_year = $financial_year;
      $receipt->save();
      if($receipt->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');
         $narrations = $request->input('narration');
         $debit_id = "";$debit_narration = "";
         foreach($types as $key => $type){
            if($type=="Debit"){
               $debit_id = $request->input('account_name')[$key];
               $debit_narration = isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '';
            }
            $rectype = new ReceiptDetails;
            $rectype->receipt_id = $receipt->id;
            $rectype->company_id = Session::get('user_company_id');;
            $rectype->type = $type;
            $rectype->account_name = $account_names[$key];
            $rectype->debit = isset($debits[$key]) ? $debits[$key] :'0';
            $rectype->credit = isset($credits[$key]) ? $credits[$key] :'0';
            $rectype->narration = $narrations[$key];
            $rectype->status = '1';
            $rectype->save();            
         }
         //Account Ledger Entry
         $debit_arr = [];$credit_arr = [];
         foreach($request->input('type') as $key => $type){
            if($type=="Credit"){
               array_push($credit_arr,array(
                  'type' => $type,
                  'account_name' => $request->input('account_name')[$key],
                  'credit' => $request->input('credit')[$key],
                  'debit' => 0,
                  'narration' => isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '',
                  'mapped_account_id' => $debit_id
               ));
               //Debit Array
               $accountName = $request->input('account_name')[$key];
               $creditValue  = $request->input('credit')[$key];
               if(isset($debit_arr[$accountName])) {
                     // If already exists, add credit
                     $debit_arr[$accountName]['debit'] += $creditValue;
               } else {
                     // Otherwise, create new
                     $debit_arr[$accountName] = [
                        'type' => 'Debit',
                        'account_name' => $debit_id,
                        'debit' => $creditValue,
                        'credit' => 0,
                        'narration' => $debit_narration,
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
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 6;
            $ledger->entry_type_id = $receipt->id;
            $ledger->entry_narration = $value['narration'];
            $ledger->map_account_id = $value['mapped_account_id'];
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
         session(['previous_url_receipt' => URL::previous()]);
         return redirect('receipt')->withSuccess('Receipt added successfully!');
      }else{
         $this->failedMessage();
      }
   }

   public function edit($id){
      Gate::authorize('action-module',59);
      $receipt = Receipt::find($id);
      $com_id = Session::get('user_company_id');
      $receipt_detail = ReceiptDetails::where('receipt_id', '=', $id)->where('delete', '=', '0')->get();
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

      $debit_bank_accounts = Accounts::where('delete', '=', '0')
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

                     $debit_cash_accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->where('under_group_type','group')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $group_ids)//BANK ACCOUNTS,CASH-IN-HAND
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
      return view('receipt/editReceipt')->with('receipt', $receipt)->with('party_list', $party_list)->with('receipt_detail', $receipt_detail)->with('debit_bank_accounts', $debit_bank_accounts)->with('debit_cash_accounts', $debit_cash_accounts)->with('mat_series', $mat_series);
   }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      Gate::authorize('action-module',59);
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',

      ], [
         'date.required' => 'Date is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $receipt =  Receipt::find($request->receipt_id);
      $receipt->date = $request->input('date');
      $receipt->voucher_no = $request->input('voucher_no');
      $receipt->mode = $request->input('mode');
      $receipt->series_no = $request->input('series_no');
      $receipt->cheque_no = $request->input('cheque_no');
      $receipt->long_narration = $request->input('long_narration');
      $receipt->save();
      $receipt_detail = ReceiptDetails::where('receipt_id', '=', $request->receipt_id)->delete();
      AccountLedger::where('entry_type',6)
                     ->where('entry_type_id',$request->receipt_id)
                     ->delete();
      $types = $request->input('type');
      $account_names = $request->input('account_name');
      $debits = $request->input('debit');
      $credits = $request->input('credit');
      $narrations = $request->input('narration');
      $debit_id = "";$debit_narration = "";
      foreach ($types as $key => $type){
         if($type=="Debit"){
            $debit_id = $request->input('account_name')[$key];
            $debit_narration = isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '';
         }
         $paytype = new ReceiptDetails;
         $paytype->receipt_id = $request->receipt_id;
         $paytype->company_id = Session::get('user_company_id');;
         $paytype->type = $type;
         $paytype->account_name = $account_names[$key];
         $paytype->debit = isset($debits[$key]) ? $debits[$key] : '0';
         $paytype->credit = isset($credits[$key]) ? $credits[$key] : '0';
         $paytype->narration = $narrations[$key];
         $paytype->status = '1';
         $paytype->save();
         
      }
      //Account Ledger Entry
      $debit_arr = [];$credit_arr = [];
      foreach($request->input('type') as $key => $type){
         if($type=="Credit"){
            array_push($credit_arr,array(
               'type' => $type,
               'account_name' => $request->input('account_name')[$key],
               'credit' => $request->input('credit')[$key],
               'debit' => 0,
               'narration' => isset($request->input('narration')[$key]) ? $request->input('narration')[$key] : '',
               'mapped_account_id' => $debit_id
            ));
               //Debit Array
               $accountName = $request->input('account_name')[$key];
               $creditValue  = $request->input('credit')[$key];
               if(isset($debit_arr[$accountName])) {
                     // If already exists, add credit
                     $debit_arr[$accountName]['debit'] += $creditValue;
               } else {
                     // Otherwise, create new
                     $debit_arr[$accountName] = [
                        'type' => 'Debit',
                        'account_name' => $debit_id,
                        'debit' => $creditValue,
                        'credit' => 0,
                        'narration' => $debit_narration,
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
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 6;
         $ledger->entry_type_id = $receipt->id;
         $ledger->entry_narration = $value['narration'];
         $ledger->map_account_id = $value['mapped_account_id'];
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
      }
      if(!empty(Session::get('redirect_url'))){
         return redirect(Session::get('redirect_url'));
      }else{
         return redirect('receipt')->withSuccess('Payment detail updated successfully!');
      }      
   }
    public function delete(Request $request){
      Gate::authorize('action-module',60);
       $receipt =  Receipt::find($request->receipt_id);
       $receipt->delete = '1';
       $receipt->deleted_at = Carbon::now();
       $receipt->deleted_by = Session::get('user_id');
       $receipt->update();
       if($receipt){
          ReceiptDetails::where('receipt_id',$request->receipt_id)
                         ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
          AccountLedger::where('entry_type',6)
                         ->where('entry_type_id',$request->receipt_id)
                         ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
          return redirect('receipt')->withSuccess('Receipt deleted successfully!');
       }
    }
    public function receiptImportView(Request $request){      
      return view('receipt/receipt_view');
   }
   public function receiptImportProcess(Request $request) {
      ini_set('max_execution_time', 600); 
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
         $all_receipts = [];
         $csv_rows = [];
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
                  $receipt = Receipt::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($receipt){
                     array_push($already_exists_error_arr, 'Receipt on bill no. - '.$bill_no.' already exists');
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
               $debit_count = 0;
               $txn_arr = [];
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
                  $check_receipt = Receipt::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($check_receipt){
                     array_push($error_arr, 'Receipt on bill no. - '.$bill_no.' already exists');
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
             if($debit!="" && $debit!=0){
               $debit_count++;
            }
            if($debit_count>1){
               array_push($error_arr, 'More than one debit entry found - Row '.$index);
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
         
         if (count($data_arr) > 0) {
            $company_id = Session::get('user_company_id');
            $user_id = Session::get('user_id');
            $financial_year = Session::get('default_fy');
            $now = Carbon::now();
            foreach (collect($data_arr)->chunk(500) as $chunk) {
               $receiptInsert = [];
               $detailsInsert = [];
               $ledgerInsert = [];
               foreach ($chunk as $value) {
                  if (!empty($value['error_arr'])) {
                     $all_error_arr[] = $value['error_arr'];
                     $failed_invoice_count++;
                     continue;
                  }
                  $bill_date = date('Y-m-d', strtotime($value['bill_date']));
                  $series = $value['series'];
                  $bill_no = $value['bill_no'];
                  $remark = $value['remark'];
                  $mode_text = strtoupper(trim($value['mode']));
                  $txn_arr = $value['txn_arr'];
                  // Skip CHEQUE mode
                  if($mode_text === 'CHEQUE') {
                     $success_invoice_count++;
                     continue;
                  }
                  // Delete old receipt if duplicate exists
                  if($duplicate_voucher_status == 2) {
                     $check_rec = Receipt::select('id')
                        ->where('voucher_no', $bill_no)
                        ->where('series_no', trim($series))
                        ->where('financial_year', $financial_year)
                        ->where('delete', '0')
                        ->where('company_id', $company_id)
                        ->get();
                     foreach ($check_rec as $rec) {
                        // Soft delete receipt
                        Receipt::where('id', $rec->id)->update([
                              'delete' => '1',
                              'deleted_at' => $now,
                              'deleted_by' => $user_id,
                        ]);
                        // Soft delete related details
                        ReceiptDetails::where('receipt_id', $rec->id)->update([
                              'delete' => '1',
                              'deleted_at' => $now,
                              'deleted_by' => $user_id,
                        ]);
                        // Soft delete ledger entries
                        AccountLedger::where('entry_type', 6)
                              ->where('entry_type_id', $rec->id)
                              ->update([
                                 'delete_status' => '1',
                                 'deleted_at' => $now,
                                 'deleted_by' => $user_id,
                              ]);
                     }
                  }
                  if($mode_text=="IMPS" || $mode_text=="NEFT" || $mode_text=="RTGS"){
                     $mode = 0;
                  }else if($mode_text=="CASH"){
                     $mode = 1;
                  }else{
                     $mode = 0;
                  }
                  // Add main receipt entry
                  $receiptInsert[] = [
                     'date' => $bill_date,
                     'voucher_no' => $bill_no,
                     'mode' => $mode,
                     'series_no' => $series,
                     'long_narration' => $remark,
                     'company_id' => $company_id,
                     'financial_year' => $financial_year,
                     'created_at' => $now,
                  ];
               }
               // Insert receipts & get last inserted ID
               DB::table('receipts')->insert($receiptInsert);
               $receipt_ids = DB::table('receipts')
                     ->latest('id')
                     ->take(count($receiptInsert))
                     ->pluck('id')
                     ->reverse()
                     ->values()
                     ->toArray();
               // Now insert related details and ledgers
               $i = 0;
               foreach ($chunk as $value) {
                  if (!empty($value['error_arr']) || $value['mode'] === 'CHEQUE') {
                     continue;
                  }
                  $receipt_id = $receipt_ids[$i];
                  $txn_arr = $value['txn_arr'];
                  $series = $value['series'];
                  $bill_date = date('Y-m-d', strtotime($value['bill_date']));
                  $debit_id = "";$debit_narration = "";
                  foreach ($txn_arr as $k => $txn) {
                     $type = (!empty($txn['debit']) && $txn['debit'] != "0") ? 'Debit' : 'Credit';
                     if($type=="Debit"){
                        $debit_id = $txn['account'];
                        $debit_narration =  '';
                     }
                     $detailsInsert[] = [
                        'receipt_id' => $receipt_id,
                        'company_id' => $company_id,
                        'type' => $type,
                        'account_name' => $txn['account'],
                        'debit' => $txn['debit'],
                        'credit' => $txn['credit'],
                        'status' => '1',
                        'created_at' => $now,
                     ];
                  }
                  // Ledger entries
                  $debit_arr = [];$credit_arr = [];
                  foreach ($txn_arr as $j => $txn) {
                     $type = (!empty($txn['debit']) && $txn['debit'] != "0") ? 'Debit' : 'Credit';
                     if($type=="Credit"){
                        array_push($credit_arr,array(
                           'type' => $type,
                           'account_name' => $txn['account'],
                           'credit' => $txn['credit'],
                           'debit' => 0,
                           'narration' => '',
                           'mapped_account_id' => $debit_id
                        ));
                        //Debit Array
                        $accountName = $txn['account'];
                        $creditValue  = $txn['credit'];
                        if(isset($debit_arr[$accountName])) {
                              // If already exists, add credit
                              $debit_arr[$accountName]['debit'] += $creditValue;
                        }else{
                           // Otherwise, create new
                           $debit_arr[$accountName] = [
                              'type' => 'Debit',
                              'account_name' => $debit_id,
                              'debit' => $creditValue,
                              'credit' => 0,
                              'narration' => $debit_narration,
                              'mapped_account_id' => $accountName
                           ];
                        }
                     }
                  }
                  $final_arr = array_merge($debit_arr, array_values($credit_arr));
                  foreach ($final_arr as $key => $value) {
                     $ledgerInsert[] = [
                        'debit' => !empty($value['debit']) ? $value['debit'] : null,
                        'credit' => !empty($value['credit']) ? $value['credit'] : null,
                        'series_no' => $series,
                        'account_id' => $value['account_name'],
                        'txn_date' => $bill_date,
                        'company_id' => $company_id,
                        'financial_year' => $financial_year,
                        'entry_type' => 6,
                        'entry_type_id' => $receipt_id,
                        'entry_narration' => $value['narration'],
                        'map_account_id' => $value['mapped_account_id'],
                        'created_by' => $user_id,
                        'created_at' => now(),
                     ];
                  }
                  $success_invoice_count++;
                  $i++;
               }
               // Insert all in batches
               if (!empty($detailsInsert)) {
                  DB::table('receipt_details')->insert($detailsInsert);
               }

               if (!empty($ledgerInsert)) {
                  DB::table('account_ledger')->insert($ledgerInsert);
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