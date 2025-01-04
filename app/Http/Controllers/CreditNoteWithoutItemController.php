<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\Accounts;
use App\Models\AccountGroups;
use Session;
use DB;
class CreditNoteWithoutItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      $financial_year = Session::get('default_fy');
      $com_id = Session::get('user_company_id');
      
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
      return view('credit_note_without_item')->with('date', $bill_date)->with('mat_series', $mat_series)->with('vendors', $vendors)->with('items', $items)->with('company_gst', $companyData->gst);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      die;
      $financial_year = Session::get('default_fy');
      $journal = new Journal;
      $journal->date = $request->input('date');
      $journal->voucher_no = $request->input('voucher_no');
      $journal->series_no = $request->input('series_no');
      $journal->long_narration = $request->input('long_narration');
      $journal->company_id = Session::get('user_company_id');
      $journal->financial_year = $financial_year; 
      $journal->net_total = $request->input('net_amount');
      $journal->cgst = $request->input('cgst');
      $journal->sgst = $request->input('sgst');
      $journal->igst = $request->input('igst');
      $journal->total_amount = $request->input('total_amount');
      $journal->remark = $request->input('remark');
      $journal->vendor = $request->input('vendor');
      $journal->save();
      if($journal->id){         
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
         return redirect('journal')->withSuccess('Journal added successfully!');
      }else{
         $this->failedMessage();
      }
   }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
