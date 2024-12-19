<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Owner;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class OwnerController extends Controller
{
    public function addOwner()
    {
        $inserted_company_id = Session::get('inserted_company_id');
        $inserted_business_type = Session::get('inserted_business_type');
        $owner_data = Owner::where('company_id', $inserted_company_id)->where('delete', '0')->get();
        $owner_delete_data = Owner::where('company_id', $inserted_company_id)->where('delete', '1')->get();
        return view('company/addOwner')->with('company_id', $inserted_company_id)->with('business_type', $inserted_business_type)->with('owner_data', $owner_data)->with('owner_delete_data', $owner_delete_data);
    }

    public function submitAddOwner(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'owner_name' => 'required',
            'pan' => 'required|string'
        ], [
            'owner_name.required' => 'Owner name is required.',
            'pan' => 'Pan is required.'

        ]);
        if ($validator->fails()) {
            return redirect()->route('add-owner')->withErrors($validator)->withInput();
        }

        $owner = new Owner;
        $owner->user_id =  Auth::id();
        $owner->company_id = $request->company_id;
        $owner->owner_name = $request->owner_name;
        $owner->father_name = $request->father_name;
        $owner->date_of_birth = $request->date_of_birth;
        $owner->address = $request->address;
        $owner->pan = $request->pan;
        $owner->designation = $request->designation;
        $owner->date_of_joining = $request->date_of_joining;
        $owner->mobile_no = $request->mobile_no;
        $owner->email_id = $request->email_id;
        $owner->din = $request->din;
        $owner->share_percentage = $request->share_percentage;
        $owner->authorized_signatory = $request->authorized_signatory;
        $owner->status = 1;
        $owner->save();
        if ($owner) {
            if(isset($request['shareholder_id']) && $request['shareholder_id']==""){
               Session::put('tab_id','fill-tab-1');
               return redirect('company-edit')->withSuccess('Owner Added successfully!');
            }else{
               return redirect('add-owner')->withSuccess('Owner Created successfully!');
            }            
        } else {
            if(isset($request['shareholder_id']) && $request['shareholder_id']==""){
               Session::put('tab_id','fill-tab-1');
               return redirect("company-edit")->withError('Something went wrong, please try after some time!');
            }else{
               return redirect("add-owner")->withError('Something went wrong, please try after some time!');
            }  
            
        }
    }

    public function ownerEdit($id)
    {

        $owner_data = Owner::find($id);

        return view('company/editOwner')->with('owner_data', $owner_data);
    }

    public function viewOwner($id)
    {

        $owner_data = Owner::find($id);

        return view('company/viewOwner')->with('owner_data', $owner_data);
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
            'owner_name' => 'required|string',

        ], [
            'owner_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $owner =  Owner::find($request->owner_id);
        $owner->owner_name = $request->owner_name;
        $owner->father_name = $request->father_name;
        $owner->date_of_birth = $request->date_of_birth;
        $owner->address = $request->address;
        $owner->pan = $request->pan;
        $owner->designation = $request->designation;
        $owner->date_of_joining = $request->date_of_joining;
        $owner->mobile_no = $request->mobile_no;
        $owner->email_id = $request->email_id;
        $owner->din = $request->din;
        $owner->share_percentage = $request->share_percentage;
        $owner->authorized_signatory = $request->authorized_signatory;
        $owner->updated_at = Carbon::now();
        $owner->update();
        Session::put('tab_id','fill-tab-1');
        return redirect('company-edit')->withSuccess('Owner updated successfully!');
    }

    public function submitDeleteOwner(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'date_of_resigning' => 'required'
        ], [
            'date_of_resigning.required' => 'Date of resigning is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $owner =  Owner::find($request->del_id);
        $owner->date_of_resigning = $request->date_of_resigning;
        $owner->deleted = 1;
        $owner->deleted_at = Carbon::now();
        $owner->update();
        Session::put('tab_id','fill-tab-1');
        if ($owner) {
         
            return redirect('company-edit')->withSuccess('Deleted successfully!');
        } else {
            return redirect("company-edit")->withError('Something went wrong, please try after some time!');
        }
    }
}
