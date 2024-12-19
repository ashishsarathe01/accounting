<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\ShareTransfer;
use App\Models\User;
use App\Models\Shareholder;
use App\Models\Owner;
use Carbon\Carbon;

class ShareholderController extends Controller
{
    public function createShareholder(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'shareholders_name' => 'required',
            'pan' => 'required|string',    
        ], [            
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'shareholders_name.required' => 'Shareholders name is required.',
            'pan' => 'Pan is required.',

        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();
  
        if($user){

            $shareholder = new Shareholder;
            $shareholder->user_id = $user->id;
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

    }
    if (!$shareholder) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Shareholder Created successfully!']);
 }
}

public function shareholderListing(Request $request)
    {


        $shareholder = Shareholder::where('user_id', $request->user_id)->where('company_id', $request->company_id)->get();

        if ($shareholder) {
            return response()->json([
                'code' => 200,
                'data' => $shareholder,
                'dataCount' => $shareholder->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    public function updateShareholder(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'shareholder_id' => 'required|exists:shareholders,id',
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'shareholders_name' => 'required',
            'pan' => 'required|string',    
        ], [            
            'shareholder_id.required' => 'Shareholder id is required.',
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'shareholders_name.required' => 'Shareholders name is required.',
            'pan' => 'Pan is required.',

        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

            $shareholder = Shareholder::find($request->shareholder_id);;
            $shareholder->user_id = $request->user_id;
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
            $shareholder->updated_at = Carbon::now();
            $shareholder->update();

            if (!$shareholder) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             } else {
                return response()->json(['code' => 200, 'message' => "Shareholder details updated successfully",'shareholderData'=> $shareholder,'shareholder_id'=> $shareholder->id]);
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
