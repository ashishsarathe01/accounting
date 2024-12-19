<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\User;
use App\Models\Shareholder;
use App\Models\Owner;
use Session;

class ShareholderController extends Controller
{
    public function addShareholder()
    {
        $inserted_company_id = Session::get('inserted_company_id');
        $inserted_business_type = Session::get('inserted_business_type');
        $owner_data = Owner::where('company_id', $inserted_company_id)->get();
        $shareholder_data = Shareholder::where('company_id', $inserted_company_id)->get();
        
        if(!$owner_data){
        return redirect('add-owner');
        }
        //if($inserted_business_type==3){
        return view('addShareholder')->with('shareholder_data',$shareholder_data)->with('company_id',$inserted_company_id)->with('business_type',$inserted_business_type)->with('owner_data',$owner_data);
        
    }

    public function submitAddShareholder(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'shareholders_name' => 'required',
            'pan' => 'required|string',    
        ], [            
            'shareholders_name.required' => 'Shareholders name is required.',
            'pan' => 'Pan is required.',

        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

            $shareholder = new Shareholder;
            $shareholder->user_id =Auth::id();
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

            if ($shareholder) {
                return redirect('add-bank')->withSuccess('Shareholder Created successfully!');
         } else {
            return redirect("add-company")->withError('Something went wrong, please try after some time!');
         }
}
}
