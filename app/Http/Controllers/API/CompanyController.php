<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\PrivilegesModuleMapping;
use Carbon\Carbon;
use App\Models\User;

class CompanyController extends Controller
{
    
    public function createCompany(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_name' => 'required',
            'business_type' => 'required|string',    
        ], [            
            'user_id.required' => 'User id is required.',
            'company_name.required' => 'Company name is required.',
            'business_type' => 'Business type is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $checkcompanies   = Companies::where('company_name', $request->company_name)->first();
  
        if ($checkcompanies) {
            return response()->json(['code' => 422, 'message' => 'This Company already exits!']);
        }
        $user = User::whereId($request->user_id)->first();
  
        if($user){

            $company = new Companies;
            $company->user_id = $user->id;
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
        
    }
    if (!$company) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Company Created successfully!','companyData'=> $company,'companyId'=> $company->id]);
 }
}
public function companyListing(Request $request)
{
    $company_list = collect();

    $user_id   = $request->user_id;
    $user_detail = User::find($user_id);
    $user_type = $user_detail->type;

    if($user_type == "OWNER"){

       
            $login_user_mobile = User::find($user_id);

            if(!$login_user_mobile){
                return response()->json([
                    'code' => 404,
                    'message' => 'User not found'
                ]);
            }

            $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                                ->where('type','OWNER')
                                ->where('status','1')
                                ->where('delete_status','0')
                                ->pluck('id');

            $login_user_emp_comp = User::where('mobile_no',$login_user_mobile->mobile_no)
                                ->where('type','!=','OWNER')
                                ->where('status','1')
                                ->where('delete_status','0')
                                ->pluck('company_id');

            $company_list = Companies::whereIn('user_id', $login_user_id)->get();

            $company_list_emp = Companies::whereIn('id', $login_user_emp_comp)->get();

            $company_list = $company_list
                            ->merge($company_list_emp)
                            ->unique('id')
                            ->values();
        

    } 
    else if(in_array($user_type, ["EMPLOYEE","OTHER","ACCOUNTANT","CA"])){

        $login_user_mobile = User::find($user_id);

        if(!$login_user_mobile){
            return response()->json([
                'code' => 404,
                'message' => 'User not found'
            ]);
        }

        $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                        ->where('status','1')
                        ->where('delete_status','0')
                        ->pluck('id');

        $login_user_id_owner = User::where('mobile_no',$login_user_mobile->mobile_no)
                            ->where('type','OWNER')
                            ->where('status','1')
                            ->where('delete_status','0')
                            ->pluck('id');

        $company_list_owner = Companies::whereIn('user_id', $login_user_id_owner)->get();

        $assign_company = PrivilegesModuleMapping::whereIn('employee_id',$login_user_id)
                            ->pluck('company_id')
                            ->toArray();

        $company_list = Companies::whereIn('id',$assign_company)->get();

        $company_list = $company_list
                        ->merge($company_list_owner)
                        ->unique('id')
                        ->values();
    }

    return response()->json([
        'code' => 200,
        'data' => $company_list,
        'dataCount' => $company_list->count(),
    ]);
}

    public function companyDetail(Request $request)
    {
       // $company = Companies::where('id', $request->company_id)->get();
        $company = Companies::where('id', $request->company_id)->with(['companyOwner'])->with(['companyOwnerdelted'])->with(['companyShareholder'])->with(['companySharetransfer'])->with(['companyBank'])->get();

        if ($company) {
            return response()->json([
                'code' => 200,
                'data' => $company,
                'dataCount' => $company->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    public function updateCompanyDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'company_name' => 'required',
            'business_type' => 'required|string',    
        ], [            
            'company_id.required' => 'Company id is required.',
            'user_id.required' => 'User id is required.',
            'company_name.required' => 'Company name is required.',
            'business_type' => 'Business type is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $checkcompanies   = Companies::where('company_name', $request->company_name)->where('id','!=',$request->company_id)->first();
  
        if ($checkcompanies) {
            return response()->json(['code' => 422, 'message' => 'This Company already exits!']);
        }
        $user = User::whereId($request->user_id)->first();
        if($user)
        {
            $company =  Companies::find($request->company_id);
            $company->user_id = $user->id;
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
            $company->updated_at = Carbon::now();
            $company->update();
        }

        if (!$company) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Company details updated successfully!','CompanyData'=> $company,'CompanyId'=> $company->id]);
         }
        


    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
    
    public function manageFinancialYearApi(Request $request)
{
    $request->validate([
        'company_id' => 'required|integer|exists:companies,id'
    ]);

    $comp = Companies::select('current_finacial_year', 'default_fy')
                ->where('id', $request->company_id)
                ->first();

    $default_fy = $comp->current_finacial_year;

    if (!empty($comp->default_fy)) {
        $default_fy = $comp->default_fy;
    }

    return response()->json([
        'code' => 200,
        'default_fy' => $default_fy,
        'starting_financial_year' => $comp->current_finacial_year
    ]);
}

public function changeDefaultFYApi(Request $request)
{
    $request->validate([
        'company_id' => 'required|integer|exists:companies,id',
        'financial_year' => 'required|string'
    ]);

    $comp = Companies::find($request->company_id);

    if (!$comp) {
        return response()->json([
            'status' => false,
            'message' => 'Company not found'
        ], 404);
    }

    $fy = $request->financial_year;

    $y = explode("-", $fy);

    if (count($y) != 2 || strlen($y[0]) != 2 || strlen($y[1]) != 2) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid financial year format. Expected format: 23-24'
        ], 400);
    }

    // Convert 23 → 2023
    $startYear = 2000 + (int)$y[0];
    $endYear   = 2000 + (int)$y[1];

    // FY always starts 1st April and ends 31st March
    $from_date = $startYear . "-04-01";
    $to_date   = $endYear . "-03-31";

    // Update default financial year
    $comp->default_fy = $fy;
    $comp->save();

    return response()->json([
        'code' => 200,
        'message' => 'Financial Year Changed Successfully!',
        'data' => [
            'default_fy' => $fy,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]
    ]);
}


    
}
