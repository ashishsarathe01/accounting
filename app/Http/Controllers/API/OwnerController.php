<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\User;
use App\Models\Owner;
use Carbon\Carbon;

class OwnerController extends Controller
{
    public function createOwner(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'owner_name' => 'required',
            'pan' => 'required|string',    
        ], [            
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'owner_name.required' => 'Owner name is required.',
            'pan' => 'Pan is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();
  
        if($user){

            $owner = new Owner;
            $owner->user_id = $user->id;
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
            $owner->save();
    }
    if (!$owner) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Owner Created successfully!']);
 }
}

public function ownerListing(Request $request)
{

    $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id' 
        ], [            
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $owner = Owner::where('user_id', $request->user_id)->where('company_id', $request->company_id)->where('deleted',"0")->get();
        if ($owner) {
            return response()->json([
                'code' => 200,
                'data' => $owner,
                'dataCount' => $owner->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    public function jointPartnerListing(Request $request)
    {
        $partner_data = Owner::where('company_id', $request->company_id)->where('deleted', 0)->get();
        if ($partner_data) {
            return response()->json([
                'code' => 200,
                'data' => $partner_data,
                'dataCount' => $partner_data->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }

    public function resignedPartnerListing(Request $request)
    {
        $resigned_partner_data = Owner::where('company_id', $request->company_id)->where('deleted', 1)->get();
        if ($resigned_partner_data) {
            return response()->json([
                'code' => 200,
                'data' => $resigned_partner_data,
                'dataCount' => $resigned_partner_data->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    public function updateOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|exists:owners,id',
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'owner_name' => 'required',
            'pan' => 'required|string',    
        ], [            
            'owner_id.required' => 'Owner id is required.',
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'owner_name.required' => 'Owner name is required.',
            'pan' => 'Pan is required.',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

            $owner =  Owner::find($request->owner_id);
            $owner->user_id = $request->user_id;
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
            $owner->updated_at = Carbon::now();
            $owner->update();

            if (!$owner) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             } else {
                return response()->json(['code' => 200, 'message' => "Owner's details updated successfully",'OwnerData'=> $owner,'Owner_id'=> $owner->id]);
             }


    }

    public function ownerResigning(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'date_of_resigning' => 'required'  
        ], [            
            'date_of_resigning.required' => 'Date of resigning is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
       
            $owner =  Owner::find($request->id);
            $owner->date_of_resigning = $request->date_of_resigning;
            $owner->deleted = 1;
            $owner->deleted_at = Carbon::now();
            $owner->update();
    
            if (!$owner) {
                return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
            } else {
               return response()->json(['code' => 200, 'message' => 'Resigning successfully!']);
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
