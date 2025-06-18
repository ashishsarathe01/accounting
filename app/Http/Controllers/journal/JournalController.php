<?php

namespace App\Http\Controllers\journal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\Accounts;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\AccountLedger;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\AccountGroups;
use App\Models\BillSundrys;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
use Gate;
class JournalController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
{
    $input = $request->all();
    $from_date = null;
    $to_date = null;

    // Default: first of current month to today
    $default_from = "01-" . date('m-Y');
    $default_to = date('d-m-Y');

    // Check if user submitted a date range
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['journal_from_date' => $from_date, 'journal_to_date' => $to_date]);
    } elseif (session()->has('journal_from_date') && session()->has('journal_to_date')) {
        $from_date = session('journal_from_date');
        $to_date = session('journal_to_date');
    }

    Session::put('redirect_url', '');

    // Financial year month array
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
    $query = DB::table('journal_details')
        ->select('journals.series_no', 'journals.id as jon_id', 'journals.date', 'accounts.account_name as acc_name', 'journal_details.*')
        ->join('journals', 'journal_details.journal_id', '=', 'journals.id')
        ->join('accounts', 'journal_details.account_name', '=', 'accounts.id')
        ->where('journal_details.company_id', $com_id)
        ->where('journals.delete', '0');

    // Apply date filter if set
    if ($from_date && $to_date) {
        $query->whereRaw("
            STR_TO_DATE(journals.date,'%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')
            AND STR_TO_DATE(journals.date,'%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')
        ", [date('Y-m-d', strtotime($from_date)), date('Y-m-d', strtotime($to_date))]);
    } else {
        // Show last 10 distinct journal entries
        $last10Ids = DB::table('journals')
            ->where('company_id', $com_id)
            ->where('delete', '0')
            ->orderByRaw("STR_TO_DATE(date,'%Y-%m-%d') DESC")
            ->limit(10)
            ->pluck('id');

        $query->whereIn('journal_details.journal_id', $last10Ids);
    }

    $journal = $query
        ->orderBy('journal_details.journal_id', 'asc')
        ->orderBy('journals.date', 'asc')
        ->get();

    // Fallback values if not set
    $from_date = $from_date ;
    $to_date = $to_date ;

    return view('journal/journal')
        ->with('journal', $journal)
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
      Gate::authorize('action-module',80);
      $financial_year = Session::get('default_fy');
      $com_id = Session::get('user_company_id');
      $party_list = Accounts::whereIn('company_id', [$com_id,0])
                                ->where('delete', '=', '0')
                                ->orderBy('account_name')
                                ->get();
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
      $vendors = Accounts::select('id','account_name','gstin')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->where('status','1')
               ->where('delete','0')
               ->where('gstin','!=','')
               ->orderBy('account_name')
               ->get();
      $fixed_asset_group = AccountGroups::where('heading','6')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type',null)
                                       ->where('heading_type','')
                                       ->pluck('id');
      $fixed_asset_group->push(12);//DIRECT EXPENSE
      $fixed_asset_group->push(15);//INDIRECT EXPENSE
      $fixed_asset_group->push(6);//UNSECURED LOANS
      $fixed_asset_group->push(13);//DIRECT INCOME
      $fixed_asset_group->push(14);//INDIRECT INCOME
      $sub_group = AccountGroups::whereIn('heading',$fixed_asset_group)
                                       //->where('heading_type',"group")
                                       ->pluck('id');
                                       
      $fixed_asset_group = $fixed_asset_group->merge($sub_group);
      
      $items = Accounts::select('id','account_name')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->whereIn('under_group',$fixed_asset_group)
               ->where('status','1')
               ->where('delete','0')
               ->orderBy('account_name')
               ->get();

      return view('journal/addJournal')->with('party_list', $party_list)->with('date', $bill_date)->with('mat_series', $mat_series)->with('vendors', $vendors)->with('items', $items)->with('company_gst', $companyData->gst);
   }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      Gate::authorize('action-module',80);
      // echo "<pre>";
      // print_r($request->all());die;
      $financial_year = Session::get('default_fy');
      $journal = new Journal;
      $journal->date = $request->input('date');
      $journal->voucher_no = $request->input('voucher_no');
      $journal->series_no = $request->input('series_no');
      $journal->long_narration = $request->input('long_narration');
      $journal->company_id = Session::get('user_company_id');
      $journal->financial_year = $financial_year;
      $journal->claim_gst_status = $request->input('flexRadioDefault');
      if($request->input('form_source') && !empty($request->input('form_source'))){
         $journal->form_source = $request->input('form_source');
      }     
      if($request->input('flexRadioDefault')=="YES"){
         $journal->invoice_no = $request->input('invoice_no');
         $journal->net_total = $request->input('net_amount');
         $journal->cgst = $request->input('cgst');
         $journal->sgst = $request->input('sgst');
         $journal->igst = $request->input('igst');
         $journal->total_amount = $request->input('total_amount');
         $journal->remark = $request->input('remark');
         $journal->vendor = $request->input('vendor');
      }
      $journal->save();
      if($journal->id){
         if($request->input('flexRadioDefault')=="YES"){
            //Journal Entry
            $joundetail = new JournalDetails;
            $joundetail->journal_id = $journal->id;
            $joundetail->company_id = Session::get('user_company_id');
            $joundetail->type = "Credit";
            $joundetail->account_name = $request->input('vendor');
            $joundetail->credit = $request->input('total_amount');
            $joundetail->status = '1';
            $joundetail->save();
            //Ledger Entry
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('vendor');
            $ledger->series_no = $request->input('series_no');
            $ledger->credit = $request->input('total_amount');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $request->input('vendor');
            $ledger->entry_type_id = $journal->id;
            $ledger->entry_type_detail_id = $joundetail->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($request->input('item') as $key => $item){
               $percentage = $request->input('percentage')[$key];
               $amount = $request->input('amount')[$key];
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $item;
               $joundetail->debit = $amount;
               $joundetail->percentage = $percentage;   
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $item;
               $ledger->debit = $amount;                       
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $item;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            if(!empty($request->input('igst'))){
               $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
               $account_name = "";
               if($sundry){
                  $account_name = $sundry->purchase_amt_account;
               }
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $account_name;
               $joundetail->debit = $request->input('igst');
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $account_name;
               $ledger->series_no = $request->input('series_no');
               $ledger->debit = $request->input('igst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }else{
               $cgst_account_name = "";
               $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('delete','0')
                        ->where('status','1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
               if($cgst_sundry){
                  $cgst_account_name = $cgst_sundry->purchase_amt_account;
               }
               $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('delete','0')
                           ->where('status','1')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();
               $sgst_account_name = "";
               if($sgst_sundry){
                  $sgst_account_name = $sgst_sundry->purchase_amt_account;
               }
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $cgst_account_name;
               $joundetail->debit = $request->input('cgst');
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $cgst_account_name;
               $ledger->series_no = $request->input('series_no');
               $ledger->debit = $request->input('cgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $sgst_account_name;
               $joundetail->debit = $request->input('sgst');
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->series_no = $request->input('series_no');
               $ledger->debit = $request->input('sgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
         }else{
            $types = $request->input('type');
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');
            $credits = $request->input('credit');         
            $narrations = $request->input('narration');
            $i = 0;
            foreach ($types as $key => $type){
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');;
               $joundetail->type = $type;
               $joundetail->account_name = $account_names[$key];
               $joundetail->debit = isset($debits[$key]) ? $debits[$key] : '0';
               $joundetail->credit = isset($credits[$key]) ? $credits[$key] : '0';            
               $joundetail->narration = $narrations[$key];
               $joundetail->status = '1';
               $joundetail->save();
               //ADD DATA IN Customer ACCOUNT
               if($type=="Credit"){
                  if($i==0){
                     $map_account_id = $account_names['1'];
                  }else{
                     $map_account_id = $account_names['0'];
                  }
               }else if($type=="Debit"){
                  if($i==0){
                     $map_account_id = $account_names['1'];
                  }else{
                     $map_account_id = $account_names['0'];
                  }
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
               $ledger->entry_type = 7;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;

               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $i++;
            }
         }
         session(['previous_url_journal' => URL::previous()]);
         if($request->input('form_source') && !empty($request->input('form_source'))){
            return redirect('profitloss')->withSuccess('Journal added successfully!');
         }else{
            return redirect('journal')->withSuccess('Journal added successfully!');
         }
         
      }else{
         $this->failedMessage();
      }
   }

   public function edit($id){
      Gate::authorize('action-module',53);
      $journal = Journal::find($id);
      $com_id = Session::get('user_company_id');
      $journal_detail = JournalDetails::where('journal_id', '=', $id)->where('delete', '=', '0')->get();
      $party_list = Accounts::whereIn('company_id', [$com_id,0])
                                ->where('delete', '=', '0')
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
      $vendors = Accounts::select('id','account_name','gstin')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->where('status','1')
               ->where('delete','0')
               ->where('gstin','!=','')
               ->orderBy('account_name')
               ->get();
      $fixed_asset_group = AccountGroups::where('heading','6')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type',null)
                                       ->where('heading_type','')
                                       ->pluck('id');
      $fixed_asset_group->push(12);
      $fixed_asset_group->push(15);
      $fixed_asset_group->push(6);
      $sub_group = AccountGroups::whereIn('heading',$fixed_asset_group)
                                       ->where('heading_type',"group")
                                       ->pluck('id');
      $fixed_asset_group->merge($sub_group);
      $items = Accounts::select('id','account_name')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->whereIn('under_group',$fixed_asset_group)
               ->where('status','1')
               ->where('delete','0')
               ->orderBy('account_name')
               ->get();

      $sundry = BillSundrys::select('purchase_amt_account')
                           ->whereIn('nature_of_sundry',['IGST','CGST','SGST'])
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('delete','0')
                           ->where('status','1')
                           ->pluck('purchase_amt_account');  
                           
      $sundry_arr = $sundry->toArray();
      return view('journal/editJournal')->with('journal', $journal)->with('party_list', $party_list)->with('journal_detail', $journal_detail)->with('mat_series', $mat_series)->with('vendors', $vendors)->with('items', $items)->with('company_gst', $companyData->gst)->with('sundry', $sundry_arr);
   }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      Gate::authorize('action-module',53);
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',
        ], [
            'date.required' => 'Date is required.',
      ]);
      if($validator->fails()){
         return response()->json($validator->errors(), 422);
      }
      $receipt =  Journal::find($request->journal_id);
      $receipt->voucher_no = $request->input('voucher_no');
      $receipt->series_no = $request->input('series_no');
      $receipt->long_narration = $request->input('long_narration');
      $receipt->date = $request->input('date');
      $receipt->claim_gst_status = $request->input('flexRadioDefault');
      if($request->input('flexRadioDefault')=="YES"){
         $receipt->invoice_no = $request->input('invoice_no');
         $receipt->net_total = $request->input('net_amount');
         $receipt->cgst = $request->input('cgst');
         $receipt->sgst = $request->input('sgst');
         $receipt->igst = $request->input('igst');
         $receipt->total_amount = $request->input('total_amount');
         $receipt->remark = $request->input('remark');
         $receipt->vendor = $request->input('vendor');
      }
      $receipt->save();
      $receipt_detail = JournalDetails::where('journal_id', '=', $request->journal_id)->delete();
      AccountLedger::where('entry_type',7)
                       ->where('entry_type_id',$request->journal_id)
                       ->delete();
      if($request->input('flexRadioDefault')=="YES"){
         //Journal Entry
         $joundetail = new JournalDetails;
         $joundetail->journal_id = $request->journal_id;
         $joundetail->company_id = Session::get('user_company_id');
         $joundetail->type = "Credit";
         $joundetail->account_name = $request->input('vendor');
         $joundetail->credit = $request->input('total_amount');
         $joundetail->status = '1';
         $joundetail->save();
         //Ledger Entry
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('vendor');
         $ledger->credit = $request->input('total_amount');  
         $ledger->series_no = $request->input('series_no');                     
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 7;
         $ledger->map_account_id = $request->input('vendor');
         $ledger->entry_type_id = $request->journal_id;
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         foreach ($request->input('item') as $key => $item){
            $percentage = $request->input('percentage')[$key];
            $amount = $request->input('amount')[$key];
            $joundetail = new JournalDetails;
            $joundetail->journal_id = $request->journal_id;
            $joundetail->company_id = Session::get('user_company_id');
            $joundetail->type = "Debit";
            $joundetail->account_name = $item;
            $joundetail->debit = $amount;
            $joundetail->percentage = $percentage;   
            $joundetail->status = '1';
            $joundetail->save();
            //Ledger Entry
            $ledger = new AccountLedger();
            $ledger->account_id = $item;
            $ledger->debit = $amount;                       
            $ledger->txn_date = $request->input('date');
            $ledger->series_no = $request->input('series_no');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $item;
            $ledger->entry_type_id = $request->journal_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
         if(!empty($request->input('igst'))){
            $sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','IGST')
                        ->where('delete','0')
                        ->where('status','1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
            
            $account_name = "";
            if($sundry){
               $account_name = $sundry->purchase_amt_account;
            }
            $joundetail = new JournalDetails;
            $joundetail->journal_id = $request->journal_id;
            $joundetail->company_id = Session::get('user_company_id');
            $joundetail->type = "Debit";
            $joundetail->account_name = $account_name;
            $joundetail->debit = $request->input('igst');
            $joundetail->status = '1';
            $joundetail->save();
            //Ledger Entry
            $ledger = new AccountLedger();
            $ledger->account_id = $account_name;
            $ledger->series_no = $request->input('series_no');
            $ledger->debit = $request->input('igst');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $account_name;
            $ledger->entry_type_id = $request->journal_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }else{
            $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('delete','0')
                        ->where('status','1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
            $cgst_account_name = "";
            if($cgst_sundry){
               $cgst_account_name = $cgst_sundry->purchase_amt_account;
            }
            $sgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','SGST')
                        ->where('delete','0')
                        ->where('status','1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
            $sgst_account_name = "";
            if($sgst_sundry){
               $sgst_account_name = $sgst_sundry->purchase_amt_account;
            }
            $joundetail = new JournalDetails;
            $joundetail->journal_id = $request->journal_id;
            $joundetail->company_id = Session::get('user_company_id');
            $joundetail->type = "Debit";
            $joundetail->account_name = $cgst_account_name;
            $joundetail->debit = $request->input('cgst');
            $joundetail->status = '1';
            $joundetail->save();
            //Ledger Entry
            $ledger = new AccountLedger();
            $ledger->account_id = $cgst_account_name;
            $ledger->series_no = $request->input('series_no');
            $ledger->debit = $request->input('cgst');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $cgst_account_name;
            $ledger->entry_type_id = $request->journal_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $joundetail = new JournalDetails;
            $joundetail->journal_id = $request->journal_id;
            $joundetail->company_id = Session::get('user_company_id');
            $joundetail->type = "Debit";
            $joundetail->account_name = $sgst_account_name;
            $joundetail->debit = $request->input('sgst');
            $joundetail->status = '1';
            $joundetail->save();
            //Ledger Entry
            $ledger = new AccountLedger();
            $ledger->account_id = $sgst_account_name;
            $ledger->series_no = $request->input('series_no');
            $ledger->debit = $request->input('sgst');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $sgst_account_name;
            $ledger->entry_type_id = $request->journal_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
      }else{
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');        
         $narrations = $request->input('narration');
         $i = 0;
         foreach ($types as $key => $type){
            $paytype = new JournalDetails;
            $paytype->journal_id = $request->journal_id;
            $paytype->company_id = Session::get('user_company_id');;
            $paytype->type = $type;
            $paytype->account_name = $account_names[$key];
            $paytype->debit = isset($debits[$key]) ? $debits[$key] : '0';
            $paytype->credit = isset($credits[$key]) ? $credits[$key] : '0';            
            $paytype->narration = $narrations[$key];
            $paytype->status = '1';
            $paytype->save();
            //ADD DATA IN Customer ACCOUNT
            if($type=="Credit"){
               if($i==0){
                  $map_account_id = $account_names['1'];
               }else{
                  $map_account_id = $account_names['0'];
               }
            }else if($type=="Debit"){
               if($i==0){
                  $map_account_id = $account_names['1'];
               }else{
                  $map_account_id = $account_names['0'];
               }
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
            $ledger->entry_type = 7;
            $ledger->entry_type_id = $request->journal_id;
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
      }
      if(!empty(Session::get('redirect_url'))){
         return redirect(Session::get('redirect_url'));
      }else{
         return redirect('journal')->withSuccess('Journal detail updated successfully!');
      } 
   }
   public function delete(Request $request){
      Gate::authorize('action-module',54);
      $journal =  Journal::find($request->journal_id);
      $journal->delete = '1';
      $journal->deleted_at = Carbon::now();
      $journal->deleted_by = Session::get('user_id');
      $journal->update();
      if($journal){
         JournalDetails::where('journal_id',$request->journal_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',7)
                        ->where('entry_type_id',$request->journal_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         return redirect('journal')->withSuccess('Journal deleted successfully!');
      }
   }
   public function journalImportView(Request $request){      
      return view('journal/import_journal_view');
   }
   public function journalImportProcess(Request $request) {       
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
               if(trim($data[0])!="" && trim($data[1])!="" && $data[2]!=""){                  
                  $series = trim($data[1]);
                  $bill_no = trim($data[2]);
                  $receipt = Journal::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($receipt){
                     array_push($already_exists_error_arr, 'Journal on journal no. - '.$bill_no.' already exists');
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
            if(trim($data[0])=="" && trim($data[1])=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;                  
            }
            if(trim($data[0])!="" && trim($data[1])!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $txn_arr = [];
               $error_arr = [];
               $bill_date = trim($data[0]);
               $series = trim($data[1]);
               $bill_no = $data[2];               
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($bill_date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($bill_date)))){                  
                  array_push($error_arr, 'Date '.$bill_date.' Not In Financial Year - Row '.$index);                  
               }
               if(!in_array($series, $series_arr)){
                  array_push($error_arr, 'Series No. '.$series.' Not Found - Row '.$index); 
               }
               if($duplicate_voucher_status!=2 && !empty($bill_no)){
                  $check_receipt = Journal::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($check_receipt){
                     array_push($error_arr, 'Journal on journal no. - '.$bill_no.' already exists');
                  }
               }
            }
            $account = $data[3];
            $check_account = Accounts::where('account_name',trim($account))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_account){
               array_push($error_arr, 'Account Name '.$account.' Not Found - Row '.$index);
            }
            $debit = $data[4];
            $debit = str_replace(",","",$debit);
            $credit = $data[5];
            $credit = str_replace(",","",$credit);
            if($debit=="" && $credit==""){
               array_push($error_arr, 'Debit/Credit Cannot - Row '.$index);
            }
            if($check_account){
               array_push($txn_arr,array("account"=>$check_account->id,"debit"=>$debit,"credit"=>$credit));
            }else{
               array_push($txn_arr,array("account"=>$account,"debit"=>$debit,"credit"=>$credit));
            }            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
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
               $txn_arr = $value['txn_arr'];               
               if($duplicate_voucher_status==2){
                  $check_rec = Journal::select('id')
                                             ->where('voucher_no',$bill_no)
                                             ->where('series_no',trim($series))
                                             ->where('financial_year',$financial_year)
                                             ->where('company_id',trim(Session::get('user_company_id')))
                                             ->first();
                  if($check_rec){              
                     $updated_payment = Journal::find($check_rec->id);
                     $updated_payment->delete = '1';
                     $updated_payment->deleted_at = Carbon::now();
                     $updated_payment->deleted_by = Session::get('user_id');
                     $updated_payment->update();
                     if($updated_payment){
                        JournalDetails::where('journal_id',$check_rec->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',7)
                        ->where('entry_type_id',$check_rec->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                     }
                  }                  
               }
               $receipt = new Journal;
               $receipt->date = date('Y-m-d',strtotime($bill_date));
               $receipt->voucher_no = $bill_no;
               $receipt->series_no = $series;  
               $receipt->company_id = Session::get('user_company_id');
               $receipt->financial_year = $financial_year;
               $i = 0;
              
               if($receipt->save()){
                  foreach($txn_arr as $key => $data){
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $type = "Debit";
                     }else{
                        $type = "Credit";
                     }
                     $paytype = new JournalDetails;
                     $paytype->journal_id = $receipt->id;
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
                     $ledger->entry_type = 7;
                     $ledger->entry_type_id = $receipt->id;
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
