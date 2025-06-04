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
use App\Models\Bank;
use App\Models\AccountOtherAddress;
use Session;
use DB;

class AccountsController extends Controller{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
      $incomplete_status = ['1','0'];
      $status = ['1','0'];
     if($request->filter && !empty($request->filter)){
         if($request->filter=="Enable"){
            $status = ['1'];
         }else if($request->filter=="Disable"){
            $status = ['0'];
         }else if($request->filter=="InComplete"){
            $incomplete_status = ['1'];
         }
     }
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
               ->whereIn('accounts.status',$status)
               ->whereIn('incomplete_status',$incomplete_status)             
               ->orderBy('account_name')
               ->whereIn('accounts.company_id', [$com_id,0])                 
               ->where('accounts.delete', '=', '0')
               ->get();
               
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
      $account->branch = $request->input('branch');
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
         if($request->input('under_group')==7){
            $bank = new Bank;
            $bank->user_id = Session::get('user_id');
            $bank->company_id =  Session::get('user_company_id');
            $bank->name = $request->input('account_name');
            $bank->bank_name = $request->input('bank_name');
            $bank->account_no = $request->input('account_no');
            $bank->ifsc = $request->input('ifsc_code');
            $bank->branch = $request->input('branch');
            if($bank->save()){
               $upaccount = Accounts::find($account->id);
               $upaccount->bank_map_id = $bank->id;
               $upaccount->save();
            }            
         }
         if($request->input('form_type') && $request->input('form_type')=="bank"){
            $res = array(
               'status' => true,
               'data' => "",
               "message"=>"Account added successfully!"
            );
            return json_encode($res);
         }
         //Other Address
          
         if(!empty($request->input('other_address'))  && !empty($request->input('other_pincode'))){
            $other_address = $request->input('other_address');
            $other_pincode = $request->input('other_pincode');
            if(count($other_address)>0 && count($other_pincode)>0){
               foreach($other_address as $key => $val){
                  if(!empty($val) && !empty($other_pincode[$key])){
                     DB::table('account_other_addresses')->insert([
                        'account_id' => $account->id,
                        'address' => $val,
                        'pincode' => $other_pincode[$key],
                        'created_at' => Carbon::now(),
                        'company_id' => Session::get('user_company_id'),
                     ]);
                  }
               }
            }            
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
      $other_address = AccountOtherAddress::where('account_id',$id)
         ->where('status','1')
         ->where('company_id',Session::get('user_company_id'))
         ->get();    
      
      return view('account/add_account')->with('state_list', $state_list)->with('accountheading', $accountheading)->with('accountgroup', $accountgroup)->with('account', $account)->with('id', $id)->with('other_address', $other_address);
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
      $incomplete_status = $account->incomplete_status;
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
      $account->branch = $request->input('branch');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->incomplete_status = 0;
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
      if($request->input('under_group')==7){
         if(!empty($account->bank_map_id)){
            $bank = Bank::find($account->bank_map_id);
         }else{
            $bank = new Bank;
         }         
         $bank->user_id = Session::get('user_id');
         $bank->company_id =  Session::get('user_company_id');
         $bank->name = $request->input('account_name');
         $bank->bank_name = $request->input('bank_name');
         $bank->account_no = $request->input('account_no');
         $bank->ifsc = $request->input('ifsc_code');
         $bank->branch = $request->input('branch');
         $bank->save();
      }else{
         if(!empty($account->bank_map_id)){
            Bank::where('id',$account->bank_map_id)->delete();
         }
      }
      //Other Address
      AccountOtherAddress::where('account_id',$request->account_id)
      ->where('company_id',Session::get('user_company_id'))
      ->update(['status'=>'0','updated_at'=>Carbon::now()]); 
      if(!empty($request->input('other_address'))  && !empty($request->input('other_pincode'))){
         $other_address = $request->input('other_address');
         $other_pincode = $request->input('other_pincode');
         if(count($other_address)>0 && count($other_pincode)>0){
            foreach($other_address as $key => $val){
               if(!empty($val) && !empty($other_pincode[$key])){
                  DB::table('account_other_addresses')->insert([
                     'account_id' => $account->id,
                     'address' => $val,
                     'pincode' => $other_pincode[$key],
                     'created_at' => Carbon::now(),
                     'company_id' => Session::get('user_company_id'),
                  ]);
               }
            }
         }            
      }
      if($incomplete_status==1){
         return redirect('account?filter=InComplete')->withSuccess('Account updated successfully!');
      }else{
         return redirect('account')->withSuccess('Account updated successfully!');
      }
      
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
         AccountOtherAddress::where('account_id',$id)
         ->where('company_id',Session::get('user_company_id'))
         ->update(['status'=>'0','updated_at'=>Carbon::now()]); 
         if(!empty($account->bank_map_id)){
            Bank::where('id',$account->bank_map_id)->update(['delete'=>'1','deleted_at'=>Carbon::now()]);
         }
         
         return redirect('account')->withSuccess('Account deleted successfully!');
      }
   }
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
   public function importAccountView(Request $request){
      return view('account/account_import')->with('upload_log',0);
   }
   public function importAccountProcess(Request $request) { 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $financial_year = Session::get('default_fy');    
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_invoice_count = $total_row - 1;
         $success_row = 0;
         $index = 1;
         $error_arr = [];$all_error_arr = [];
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         $incomplete_status_count = 0;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $name = $data[0];
            $under_group = $data[1];
            $debit = $data[2];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[3];
            $credit = trim(str_replace(",","",$credit));
            $gstin = $data[4];
            $address = $data[5];
            $state = $data[6];
            $pincode = $data[7];
            if($name=="" || $under_group==""){
               array_push($all_error_arr,array("error_title"=>"Required","mesaage"=>'Name,under group empty - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $check_group = AccountGroups::select('id')
                                       ->where('name',$under_group)
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            $check_head = AccountHeading::select('id')
                                       ->where('delete', '=', '0')
                                       ->where('status','1')
                                       ->where('name',$under_group)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            if(!$check_group && !$check_head){
               array_push($all_error_arr,array("error_title"=>"Group Not Found","mesaage"=>'Under Group '.$under_group.' not found - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $under_group_id = "";
            $under_group_type = "";
            if($check_group){
               $under_group_id = $check_group->id;
               $under_group_type = 'group';
            }else if($check_head){
               $under_group_id = $check_head->id;
               $under_group_type = 'head';
            }            
            $check_account = Accounts::select('id')
                                       ->where('account_name',$name)
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
            if($check_account){
               array_push($all_error_arr,array("error_title"=>"Already Exists","mesaage"=>'Account Name '.$name.' already exists - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $opening_balance = 0;$opening_balance_type = "debit";
            if($debit!="" && $debit!="0" && $debit!="0.00"){
               $opening_balance = $debit;
               $opening_balance_type = "debit";
            }else if($credit!="" && $credit!="0" && $credit!="0.00"){
               $opening_balance = $credit;
               $opening_balance_type = "credit";
            }
            $state_id = State::select('id')->where('name',$state)->first();
            if(!$state_id){
               $state = "";
            }else{
               $state = $state_id->id;
            }
            $incomplete_status = 0;
            if($check_group){
               if($check_group->id==1 || $check_group->id==7 || $under_group=='Fixed Assets'){
                  $incomplete_status = 1;
                  $incomplete_status_count++;
               }
               if($check_group->id==3 || $check_group->id==11){
                  if($address=="" || $pincode=="" || $state==""){
                     $incomplete_status = 1;
                     $incomplete_status_count++;
                  }
               }
            }
                 
            //3,11    
            $account = new Accounts;
            $account->account_name = $name;
            $account->company_id =  Session::get('user_company_id');
            $account->print_name = $name;
            $account->under_group = $under_group_id;
            $account->under_group_type = $under_group_type;
            $account->opening_balance = $opening_balance;
            $account->dr_cr = $opening_balance_type;
            $account->gstin = $gstin;
            $account->state = $state;
            $account->address = $address;
            $account->pin_code = $pincode;
            $account->status = '1';
            $account->incomplete_status = $incomplete_status;
            $account->save();
            if($account->id) {
               //Account Ledger Update
               if(!empty($opening_balance) && $opening_balance!=0){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $account->id;
                  if($opening_balance_type=='debit'){
                     $ledger->debit = $opening_balance;
                  }else if($opening_balance_type=='credit'){
                     $ledger->credit = $opening_balance;
                  }
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = -1;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
               }
               $success_invoice_count++;
            }            
            $index++;
         } 
         fclose($handle);
      }
      $return = array();
      foreach($all_error_arr as $val) {
         $return[$val['error_title']][] = $val;
      }
      return view('account/account_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$return)->with('incomplete_status_count',$incomplete_status_count);
   }
}
