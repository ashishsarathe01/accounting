<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Bank;
use Carbon\Carbon;

class BankController extends Controller
{
    public function createBank(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'bank_name' => 'required',
            'account_no' => 'required|string',    
        ], [            
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'bank_name.required' => 'Bank name is required.',
            'account_no' => 'Account no is required.',

        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $bankData = Bank::where(['user_id'=>$request->user_id,'company_id'=>$request->company_id,'account_no'=>$request->account_no])->count();
        if($bankData>0)
        {
            return response()->json(['code' => 422, 'message' => 'Bank account already has been added, try with different account.']);
        }
        else
        {
            $user = User::whereId($request->user_id)->first();
            if($user){

                $bank = new Bank;
                $bank->user_id = $user->id;
                $bank->company_id = $request->company_id;
                $bank->name = $request->name;
                $bank->bank_name = $request->bank_name;
                $bank->account_no = $request->account_no;
                $bank->ifsc = $request->ifsc;
                $bank->branch = $request->branch;
                $bank->save();

                }
                if (!$bank) {
                 return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             } else {
                return response()->json(['code' => 200, 'message' => 'bank added successfully!']);
             }
        }
}

    public function bankListing(Request $request)
    {
        $bank = Bank::where('user_id', $request->user_id)->where('company_id', $request->company_id)->get();
        if ($bank) {
            return response()->json([
                'code' => 200,
                'data' => $bank,
                'dataCount' => $bank->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }

    public function updateBank(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'bank_id' => 'required|exists:banks,id',
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'bank_name' => 'required',
            'account_no' => 'required|string',    
        ], [            
            'bank_id.required' => 'Bank id is required.',
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
            'bank_name.required' => 'Bank name is required.',
            'account_no' => 'Account no is required.',

        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $bankData = Bank::where('company_id','!=',$request->company_id)->where('account_no',$request->account_no)->count();

        if($bankData>0)
        {
            return response()->json(['code' => 422, 'message' => 'Bank account already has been added, try with different account.']);
        }
        else
        {
           
                $bank = Bank::find($request->bank_id);
                $bank->user_id = $request->user_id;
                $bank->company_id = $request->company_id;
                $bank->name = $request->name;
                $bank->bank_name = $request->bank_name;
                $bank->account_no = $request->account_no;
                $bank->ifsc = $request->ifsc;
                $bank->branch = $request->branch;
                $bank->updated_at = Carbon::now();
                $bank->update();

                if (!$bank) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Bank details updated successfully','BankData'=> $bank,'BankId'=> $bank->id]);
         }

              
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
