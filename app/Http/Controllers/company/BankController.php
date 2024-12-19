<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Bank;
use Session;
use Carbon\Carbon;
class BankController extends Controller{
   public function addBank(){
      $inserted_company_id = Session::get('inserted_company_id');
      $inserted_business_type = Session::get('inserted_business_type');
      $bank_data = Bank::where('company_id', $inserted_company_id)->get();
      return view('company/addBank')->with('company_id', $inserted_company_id)->with('business_type', $inserted_business_type)->with('bank_data', $bank_data);
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
      $bank->update();
      Session::put('tab_id','fill-tab-3');
      return redirect('company-edit')->withSuccess('Bank Detail updated successfully!');
   }
   public function viewBank($id){
      $bank_data = Bank::find($id);
      return view('company/viewBank')->with('bank_data', $bank_data);
   }
   public function deleteBank(Request $request){
      Bank::where('id',$request->bank_delete_id)->delete();
      Session::put('tab_id','fill-tab-3');
      return redirect('company-edit')->withSuccess('Bank Deleted Successfully!');
   }
   
}
