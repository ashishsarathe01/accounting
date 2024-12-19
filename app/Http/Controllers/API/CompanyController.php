<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
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
        $company = Companies::where('user_id', $request->user_id)->get();
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
}
