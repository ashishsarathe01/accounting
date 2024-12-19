<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\State;
use App\Models\AccountGroups;
use App\Models\AccountLedger;
use App\Models\AccountHeading;
use Session;
use DB;

class AccountsController extends Controller{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(){
      $com_id = Session::get('user_company_id');
      $account = DB::table('accounts')
               ->select('states.name as state_name', 'account_groups.name as group_name','account_headings.name as head_name','accounts.*')
               ->leftjoin('states', 'states.id', '=', 'accounts.state')
               ->leftjoin('account_groups',function($join){
                  $join->on('account_groups.id', '=', 'accounts.under_group');
                  $join->where('accounts.under_group_type', '=', 'group');
               })
               ->leftjoin('account_headings',function($join){
                  $join->on('account_headings.id', '=', 'accounts.under_group');
                  $join->where('accounts.under_group_type', '=', 'head');
               })
               ->orderBy('account_name')
               ->whereIn('accounts.company_id', [$com_id,0])
               ->where('accounts.delete', '=', '0')->get();
               
      return view('account/account')->with('account', $account);
   }

   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function create(){
      $com_id = Session::get('user_company_id');
      $accountgroup = AccountGroups::where('delete', '=', '0')
                     ->whereIn('company_id', [$com_id,0])
                     ->where('status','1')
                     ->orderBy('name')
                     ->get();
      $state_list = State::all();
      return view('account/addAccount')->with('state_list', $state_list)->with('accountgroup', $accountgroup);
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      $validator = Validator::make($request->all(), [
         'account_name' => 'required|string',
      ], [
         'account_name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $com_id = Session::get('user_company_id');
      $check = Accounts::select('id')
                        ->where('account_name',$request->input('account_name'))
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [$com_id,0])
                        ->get();
      if(count($check)>0){
         return $this->failedMessage('Account Already Created.');
         exit();
      }
      $account = new Accounts;
      $account->account_name = $request->input('account_name');
      $account->company_id =  Session::get('user_company_id');
      $account->print_name = $request->input('print_name');
      $account->under_group = $request->input('under_group');
      $account->opening_balance = $request->input('opening_balance');
      $account->dr_cr = $request->input('opening_balance_type');
      $account->tax_type = $request->input('tax_type');      
      $account->gstin = $request->input('gstin');
      $account->state = $request->input('state');
      $account->address = $request->input('address');
      $account->address2 = $request->input('address2');
      $account->address3 = $request->input('address3');
      $account->pan = $request->input('pan');
      $account->pin_code = $request->input('pincode');
      $account->due_day = $request->input('due_day');      
      $account->credit_limit = $request->input('credit_limit');
      $account->contact_person = $request->input('contact_person');
      $account->mobile = $request->input('mobile_no');
      $account->whatsup_number = $request->input('whatsapp_no');
      $account->email = $request->input('email');
      $account->under_group_type = $request->input('under_group_type');
      $account->bank_account_no = $request->input('account_no');
      $account->ifsc_code = $request->input('ifsc_code');      
      $account->bank_name = $request->input('bank_name');       
      $account->nature_of_account = $request->input('nature_of_account');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->save();
      if($account->id) {
         //Account Ledger Update
         if(!empty($request->input('opening_balance')) && !empty($request->input('opening_balance_type'))){
            $ledger = new AccountLedger();
            $ledger->account_id = $account->id;
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
            }
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
         return redirect('account')->withSuccess('Account added successfully!');
      }else{
         return $this->failedMessage('Something went wrong, please try again after some time.');
      }
   }
   public function edit($id){
      $account = Accounts::find($id);
      $com_id = Session::get('user_company_id');
      $accountgroup = AccountGroups::where('delete', '=', '0')
                     ->whereIn('company_id', [$com_id,0])
                     ->orderBy('name')
                     ->get();
      $accountheading = AccountHeading::where('delete', '=', '0')
                     ->whereIn('company_id', [$com_id,0])
                     ->orderBy('name')
                     ->get();
      $state_list = State::all();      
      return view('account/add_account')->with('state_list', $state_list)->with('accountheading', $accountheading)->with('accountgroup', $accountgroup)->with('account', $account)->with('id', $id);
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
         'account_name' => 'required|string',
      ],[
         'account_name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $account =  Accounts::find($request->account_id);
      $account->account_name = $request->input('account_name');
      $account->print_name = $request->input('print_name');
      $account->under_group = $request->input('under_group');
      $account->opening_balance = $request->input('opening_balance');
      $account->dr_cr = $request->input('opening_balance_type');
      $account->tax_type = $request->input('tax_type');      
      $account->gstin = $request->input('gstin');
      $account->state = $request->input('state');
      $account->address = $request->input('address');
      $account->address2 = $request->input('address2');
      $account->address3 = $request->input('address3');
      $account->pan = $request->input('pan');
      $account->pin_code = $request->input('pincode');
      $account->due_day = $request->input('due_day'); 
      $account->under_group_type = $request->input('under_group_type');     
      $account->credit_limit = $request->input('credit_limit');
      $account->contact_person = $request->input('contact_person');
      $account->mobile = $request->input('mobile_no');
      $account->whatsup_number = $request->input('whatsapp_no');
      $account->email = $request->input('email');
      $account->bank_account_no = $request->input('account_no');
      $account->ifsc_code = $request->input('ifsc_code');      
      $account->bank_name = $request->input('bank_name');       
      $account->nature_of_account = $request->input('nature_of_account');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->update();
      //Account Ledger Update
      if(!empty($request->input('opening_balance')) && !empty($request->input('opening_balance_type'))){
         $check = AccountLedger::where('account_id',$request->account_id)
                                 ->where('entry_type','-1')
                                 ->first();
         if($check){
            $ledger = AccountLedger::find($check->id);
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
               $ledger->credit = "";
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
               $ledger->debit = "";
            }
            $ledger->updated_by = Session::get('user_id');
            $ledger->updated_at = date('d-m-Y H:i:s');
            $ledger->save();
         }else{
            $ledger = new AccountLedger();
            $ledger->account_id = $request->account_id;
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
            }
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }         
      }else{
         $check = AccountLedger::where('account_id',$request->account_id)
                                 ->where('entry_type','-1')
                                 ->first();
         if($check){
            AccountLedger::where('entry_type','-1')
                           ->where('account_id',$request->account_id)
                           ->delete();
         }
      }    
      return redirect('account')->withSuccess('Account updated successfully!');
   }
   /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
   */
   public function delete(Request $request){
      $account =  Accounts::find($request->account_id);
      $account->delete = '1';
      $account->deleted_at = Carbon::now();
      $account->update();
      if($account) {
         return redirect('account')->withSuccess('Account deleted successfully!');
      }
   }
   /**
   * Generates failed response and message.
   */
   public function failedMessage($msg){
      return redirect('account')->withError($msg);
   }
   public function addAccount(){
      $com_id = Session::get('user_company_id');
      $accountgroup = AccountGroups::where('delete', '=', '0')
                     ->whereIn('company_id', [$com_id,0])
                     ->orderBy('name')
                     ->get();
      $accountheading = AccountHeading::where('delete', '=', '0')
                     ->whereIn('company_id', [$com_id,0])
                     ->orderBy('name')
                     ->get();
      $state_list = State::orderBy('state_code')->get();
      return view('account/add_account')->with('state_list', $state_list)->with('accountgroup', $accountgroup)->with('accountheading', $accountheading);
   }
   
}
