<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Bank;
use App\Models\State;
use App\Models\Accounts;
use Session;
use Carbon\Carbon;
class BankController extends Controller{
   public function addBank(){
      $inserted_company_id = Session::get('inserted_company_id');
      $inserted_business_type = Session::get('inserted_business_type');
      $bank_data = Bank::where('company_id', $inserted_company_id)->get();
      $state_list = State::all();
      return view('company/addBank')->with('company_id', $inserted_company_id)->with('business_type', $inserted_business_type)->with('bank_data', $bank_data)->with('state_list', $state_list);
   }
   public function submitAddBank(Request $request){
      $validator = Validator::make($request->all(), [
         'bank_name' => 'required',
         'account_no' => 'required|string',
      ],[
         'bank_name.required' => 'Bank name is required.',
         'account_no' => 'Account no is required.',
      ]);  
      $bank = new Bank;
      $bank->user_id = Auth::id();
      $bank->company_id = $request->company_id;
      $bank->name = $request->account_name;
      $bank->bank_name = $request->bank_name;
      $bank->account_no = $request->account_no;
      $bank->ifsc = $request->ifsc;
      $bank->branch = $request->branch;
      $bank->save();
      if($bank){
         $account = new Accounts;
         $account->account_name = $request->account_name;
         $account->company_id =  $request->company_id;
         $account->print_name = $request->account_name;
         $account->under_group = 7;
         $account->under_group_type = 'group';
         $account->bank_account_no = $request->account_no;
         $account->ifsc_code = $request->ifsc;      
         $account->bank_name = $request->bank_name; 
         $account->branch = $request->branch;
         $account->bank_map_id = $bank->id;
         $account->status = '1';
         $account->save();
         if(isset($request['bank_id']) && $request['bank_id']==""){
            Session::put('tab_id','fill-tab-3');
            return redirect('company-edit')->withSuccess('bank Added successfully!');
         }else{
            return redirect('add-bank')->withSuccess('bank Added successfully!');
         }
         
      }else {
        if(isset($request['bank_id']) && $request['bank_id']==""){
            Session::put('tab_id','fill-tab-3');
            return redirect('company-edit')->withError('Something went wrong, please try after some time!');
         }else{
            return redirect("add-shareholder")->withError('Something went wrong, please try after some time!');
         }
         
      }
   }
   public function bankEdit($id){
      $bank_data = Bank::find($id);
      return view('company/editBank')->with('bank_data', $bank_data);
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
      ], [
         'account_name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $bank =  Bank::find($request->bank_id);
      $bank->name = $request->account_name;
      $bank->bank_name = $request->bank_name;
      $bank->account_no = $request->account_no;
      $bank->ifsc = $request->ifsc;
      $bank->branch = $request->branch;
      $bank->updated_at = Carbon::now();
      if($bank->update()){
         Accounts::where('bank_map_id',$request->bank_id)->update(['account_name'=>$request->account_name,'bank_account_no'=>$request->account_no,'ifsc_code'=>$request->ifsc,'bank_name'=>$request->bank_name,'branch'=>$request->branch]);
      }
      Session::put('tab_id','fill-tab-3');
      return redirect('company-edit')->withSuccess('Bank Detail updated successfully!');
   }
   public function viewBank($id){
      $bank_data = Bank::find($id);
      return view('company/viewBank')->with('bank_data', $bank_data);
   }
   public function deleteBank(Request $request){      
      Bank::where('id',$request->bank_delete_id)->update(['delete'=>'1','deleted_at'=>Carbon::now()]);
      Accounts::where('bank_map_id',$request->bank_delete_id)->update(['delete'=>'1','deleted_at'=>Carbon::now()]);
      Session::put('tab_id','fill-tab-3');
      return redirect('company-edit')->withSuccess('Bank Deleted Successfully!');
   }
   
}
