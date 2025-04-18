<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\User;
use App\Models\Shareholder;
use App\Models\Owner;
use Session;
use Carbon\Carbon;

class ShareholderController extends Controller
{
    public function addShareholder()
    {
        $inserted_company_id = Session::get('inserted_company_id');
        $inserted_business_type = Session::get('inserted_business_type');
        $owner_data = Owner::where('company_id', $inserted_company_id)->get();
        $shareholder_data = Shareholder::where('company_id', $inserted_company_id)->get();
        if (!$owner_data) {
            return redirect('add-owner');
        }
        //if($inserted_business_type==3){
        return view('company/addShareholder')->with('shareholder_data', $shareholder_data)->with('company_id', $inserted_company_id)->with('business_type', $inserted_business_type)->with('owner_data', $owner_data);
    }

   public function submitAddShareholder(Request $request){
      $validator = Validator::make($request->all(), [
         'shareholders_name' => 'required',
         'pan' => 'required|string',
      ],[
         'shareholders_name.required' => 'Shareholders name is required.',
         'pan' => 'Pan is required.',
      ]);
      if ($validator->fails()) {
         return redirect()->route('add-shareholder')->withErrors($validator)->withInput();
      }
      $shareholder = new Shareholder;
      $shareholder->user_id = Auth::id();
      $shareholder->company_id = $request->company_id;
      $shareholder->shareholders_name = $request->shareholders_name;
      $shareholder->father_name = $request->father_name;
      $shareholder->date_of_birth = $request->date_of_birth;
      $shareholder->address = $request->address;
      $shareholder->pan = $request->pan;
      $shareholder->date_of_purchase = $request->date_of_purchase;
      $shareholder->no_of_share = $request->no_of_share;
      $shareholder->mobile_no = $request->mobile_no;
      $shareholder->email_id = $request->email_id;
      $shareholder->save();
      if($shareholder){
         if(isset($request['shareholder_id']) && $request['shareholder_id']==""){
            Session::put('tab_id','fill-tab-2');
            return redirect('company-edit')->withSuccess('Shareholder Added successfully!');
         }else{
            return redirect('add-bank')->withSuccess('Shareholder Created successfully!');
         }         
      }else{
         if(isset($request['shareholder_id']) && $request['shareholder_id']==""){
            Session::put('tab_id','fill-tab-2');
            return redirect("company-company")->withError('Something went wrong, please try after some time!');
         }else{
            return redirect("add-company")->withError('Something went wrong, please try after some time!');
         }
      }
   }

    public function shareholderEdit($id)
    {

        $shareholder_data = Shareholder::find($id);

        return view('company/editShareholder')->with('shareholder_data', $shareholder_data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shareholders_name' => 'required|string',

        ], [
            'shareholders_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $shareholder =  Shareholder::find($request->shareholder_id);
        $shareholder->shareholders_name = $request->shareholders_name;
        $shareholder->father_name = $request->father_name;
        $shareholder->date_of_birth = $request->date_of_birth;
        $shareholder->address = $request->address;
        $shareholder->pan = $request->pan;
        $shareholder->date_of_purchase = $request->date_of_purchase;
        $shareholder->no_of_share = $request->no_of_share;
        $shareholder->mobile_no = $request->mobile_no;
        $shareholder->email_id = $request->email_id;
        $shareholder->updated_at = Carbon::now();
        $shareholder->update();
        Session::put('tab_id','fill-tab-2');
        return redirect('company-edit')->withSuccess('Shareholder updated successfully!');
    }


    public function viewShareholder($id)
    {

        $shareholder_data = Shareholder::find($id);

        return view('company/viewShareholder')->with('shareholder_data', $shareholder_data);
    }
}
