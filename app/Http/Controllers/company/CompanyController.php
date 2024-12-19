<?php

namespace App\Http\Controllers\company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\State;
use Session;
use App\Models\Bank;
use App\Models\Owner;
use App\Models\Shareholder;
use DB;
class CompanyController extends Controller{
   public function companyListing(){
      if(Auth::check()) {
         $company = Companies::where('user_id', Auth::id())->get();
         return view('companyListing')->with('company', $company);
      }
      return redirect("password-login")->withSuccess('Please login again.');
   }
   public function viewCompany(){
      if(Auth::check()) {
         $id = Session::get('user_company_id');
         $company = DB::table('companies')
               ->select('states.name as state_name', 'companies.*')
               ->join('states', 'states.id', '=', 'companies.state')->where('companies.id', $id)->first();
         $owner_data = Owner::where('company_id', $id)->where('deleted', '0')->get();

         $owner_delete_data = Owner::where('company_id', $id)->where('deleted', '1')->get();
         $bank_data = Bank::where('company_id', $id)->get();
         $shareholder_data = Shareholder::where('company_id', $id)->get();
         return view('company/viewCompany')->with('company', $company)->with('owner_data', $owner_data)->with('owner_delete_data', $owner_delete_data)->with('bank_data', $bank_data)->with('shareholder_data', $shareholder_data);
      }
      return redirect("password-login")->withSuccess('Please login again.');
   }
   public function addCompany(){
      if(Auth::check()) {
         $state_list = State::all();
         return view('company/addCompany')->with('state_list', $state_list);
      }
      return redirect("password-login")->withSuccess('You are not allowed to access');
   }
   public function submitAddCompany(Request $request){
      $validator = Validator::make($request->all(), [
         'company_name' => 'required',
         'business_type' => 'required|string',
      ], [
         'company_name.required' => 'Company name is required.',
         'business_type' => 'Business type is required.',
      ]);
      if($validator->fails()) {
         return redirect()->route('add-company')->withErrors($validator)->withInput();
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
      $company->default_fy = $request->current_finacial_year;
      $company->books_start_from = $request->books_start_from;
      $company->email_id = $request->email_id;
      $company->mobile_no = $request->mobile_no;
      $company->save();
      Session::put('inserted_company_id', $company->id);
      Session::put('inserted_business_type', $request->business_type);
      Session::put('user_company_id', $company->id);
      Session::put('default_fy', $request->current_finacial_year);
      if($company) {
         return redirect('add-owner')->withSuccess('Company Created successfully!');
      }else{
         return redirect("add-company")->withError('Something went wrong, please try after some time!');
      }
   }
   public function submitEditCompany(Request $request){
      $validator = Validator::make($request->all(), [
         'company_name' => 'required',
         'business_type' => 'required|string',
      ], [
         'company_name.required' => 'Company name is required.',
         'business_type' => 'Business type is required.',
      ]);
      if($validator->fails()) {
         return redirect()->route('add-company')->withErrors($validator)->withInput();
      }
      $company = Companies::find(Session::get('user_company_id'));
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
      if($company) {
         return redirect('company-edit')->withSuccess('Company Updated successfully!');
      }else{
         return redirect("company-edit")->withError('Something went wrong, please try after some time!');
      }
   }
   public function companyEdit(){
      $company_id = Session::get('user_company_id');
      //Company Data
      $company = Companies::find($company_id);
      $state_list = State::all();
      //Owner Data
      $owner_data = Owner::where('company_id', $company_id)
                           ->where('deleted', '0')
                           ->get();
      $owner_delete_data = Owner::where('company_id', $company_id)
                                 ->where('deleted', '1')
                                 ->get();
      //Share Holder
      $shareholder_data = Shareholder::where('company_id', $company_id)
                                    ->get();
      //Bank Data
      $bank_data = Bank::where('company_id', $company_id)->get();
      return view('company/editCompany')->with('company', $company)->with('state_list', $state_list)->with('owner_data', $owner_data)->with('owner_delete_data', $owner_delete_data)->with('shareholder_data', $shareholder_data)->with('bank_data', $bank_data);
   }
   public function checkGst(Request $request){
      $data = $request->all();
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
            "accept: */*",
            "accept-language: en-US,en;q=0.8",
            "content-type: application/json",
         ),
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if($err) {
         return response()->json(['type' => 'error']);
      }else{
         return response()->json(['type' => 'success', 'response' => $response]);
      }
   }
   public function manageFinancialYear(Request $request){
      $comp = Companies::select('current_finacial_year','default_fy')
                        ->where('id',Session::get('user_company_id'))
                        ->first();
      $default_fy = $comp->current_finacial_year;
      if($comp->default_fy!=""){
         $default_fy = $comp->default_fy;
      }
      return view('company/managefy',['default_fy'=>$default_fy,'current_finacial_year'=>$comp->current_finacial_year]);
   }
   public function changeDefaultFY(Request $request){
      $comp =  Companies::find(Session::get('user_company_id'));
      $comp->default_fy = $request->current_finacial_year;
      $comp->update();
      $y = explode("-",$request->current_finacial_year);
      $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
      $to_date = date('Y-m-d',strtotime($y[1]."-03-31"));
      Session::put('from_date', $from_date);
      Session::put('to_date', $to_date);
      Session::put('default_fy', $request->current_finacial_year);
      return redirect("manage-financial-year")->withSuccess('Financial Year Changed Successfully!');
   }
}
