<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\State;
use Session;
use App\Models\Bank;
use App\Models\Owner;

class CompanyController extends Controller
{
    public function companyListing()
    {
        if (Auth::check()) {

            $company = Companies::where('user_id', Auth::id())->get();
            return view('companyListing')->with('company', $company);
        }

        return redirect("password-login")->withSuccess('You are not allowed to access');
    }
    public function viewCompany()
    {
        //if(Auth::check()){
        $id = '16';
        //$inserted_company_id = Session::get('inserted_company_id');
        $inserted_business_type = Session::get('inserted_business_type');
        $company = Companies::where('id', $id)->first();
        $owner_data = Owner::where('company_id', $id)->where('deleted', 0)->get();
        $owner_delete_data = Owner::where('company_id', $id)->where('deleted', 1)->get();
        $bank_data = Bank::where('company_id', $id)->get();
        return view('viewCompany')->with('company', $company)->with('owner_data',$owner_data)->with('owner_delete_data',$owner_delete_data)->with('business_type',$inserted_business_type)->with('bank_data', $bank_data);
        //}

        // return redirect("password-login")->withSuccess('You are not allowed to access');
    }
    public function addCompany()
    {
        if (Auth::check()) {

            $state_list = State::all();
            return view('addCompany')->with('state_list', $state_list);
        }
        return redirect("password-login")->withSuccess('You are not allowed to access');
    }
    public function submitAddCompany(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'company_name' => 'required',
            'business_type' => 'required|string',
        ], [
            'company_name.required' => 'Company name is required.',
            'business_type' => 'Business type is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $company = new Companies;
        $company->user_id = Auth::id();
        $company->business_type = $request->business_type;
        $company->company_name = $request->company_name;
        $company->gst_applicable = $request->gst_applicable;
        $company->gst = $request->gst;
        $company->pan = $request->pan;
        $company->date_of_incorporation = $request->date_of_incorporation;
        $company->address = $request->address;
        $company->state = $request->state;
        $company->country_name = $request->country_name;
        $company->pin_code = $request->pin_code;
        $company->current_finacial_year = $request->current_finacial_year;
        $company->books_start_from = $request->books_start_from;
        $company->email_id = $request->email_id;
        $company->mobile_no = $request->mobile_no;
        $company->save();
        Session::put('inserted_company_id', $company->id);
        Session::put('inserted_business_type', $request->business_type);
        if ($company) {
            return redirect('add-owner')->withSuccess('Company Created successfully!');
        } else {
            return redirect("add-company")->withError('Something went wrong, please try after some time!');
        }
    }
    public function checkGst(Request $request)
    {
        $data = $request->all();
        // dd($data);
        $data = [
            'gst_no' => '07AAJCK4433F1ZM'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://kraftpaperz.com/stage/api/public/index.php/api/v1/gst-info",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return response()->json(['type' => 'error']);
        } else {
            return response()->json(['type' => 'success', 'response' => $response]);
        }
    }
}
