<?php

namespace App\Http\Controllers\journal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
class JournalController extends Controller
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
      $journal = DB::table('journal_details')
            ->select('journals.series_no','journals.id as jon_id', 'journals.date', 'accounts.account_name as acc_name', 'journal_details.*')
            ->join('journals', 'journal_details.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_details.account_name', '=', 'accounts.id')
            ->where('journal_details.company_id', $com_id)
            ->where('journals.delete','0')
            ->whereRaw("STR_TO_DATE(journals.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(journals.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->orderBy('journal_details.journal_id', 'asc')
            ->orderBy('journals.date', 'asc')
            ->get();
      return view('journal/journal')->with('journal', $journal)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
   }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
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
      return view('journal/addJournal')->with('party_list', $party_list)->with('date', $bill_date)->with('mat_series', $mat_series)->with('vendors', $vendors)->with('items', $items)->with('company_gst', $companyData->gst);
   }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      
      $financial_year = Session::get('default_fy');
      $journal = new Journal;
      $journal->date = $request->input('date');
      $journal->voucher_no = $request->input('voucher_no');
      $journal->series_no = $request->input('series_no');
      $journal->long_narration = $request->input('long_narration');
      $journal->company_id = Session::get('user_company_id');
      $journal->financial_year = $financial_year;
      $journal->claim_gst_status = $request->input('flexRadioDefault');
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
            $ledger->credit = $request->input('total_amount');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 7;
            $ledger->map_account_id = $request->input('vendor');
            $ledger->entry_type_id = $journal->id;
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
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $item;
               $ledger->entry_type_id = $journal->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            if(!empty($request->input('igst'))){
               $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
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
               $ledger->debit = $request->input('igst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }else{
               $cgst_account_name = "";
               if($cgst_sundry){
                  $cgst_account_name = $cgst_sundry->purchase_amt_account;
               }
               $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
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
               $ledger->debit = $request->input('cgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $journal->id;
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
               $ledger->debit = $request->input('sgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $journal->id;
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
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->entry_type_id = $journal->id;
               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $i++;
            }
         }
         return redirect('journal')->withSuccess('Journal added successfully!');
      }else{
         $this->failedMessage();
      }
   }

   public function edit($id){
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
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
            $cgst_account_name = "";
            if($cgst_sundry){
               $cgst_account_name = $cgst_sundry->purchase_amt_account;
            }
            $sgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','SGST')
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
}
