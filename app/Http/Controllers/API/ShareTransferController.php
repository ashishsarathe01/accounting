<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Companies;
use App\Models\User;
use App\Models\ShareTransfer;
use Carbon\Carbon;

class ShareTransferController extends Controller
{
    
public function shareTransferListing(Request $request)
    {
        $sharetransfer = ShareTransfer::select('share_transfers.*','share_transfers.created_at as date_of_transfer')->where('user_id', $request->user_id)->where('company_id', $request->company_id)->get();
        if ($sharetransfer) {
            return response()->json([
                'code' => 200,
                'data' => $sharetransfer,
                'dataCount' => $sharetransfer->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }
    public function createShareTransfer(Request $request)
    {
  
        $validator = Validator::make($request->all(), [

            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',    
        ], [            
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::whereId($request->user_id)->first();
  
        if($user){

            $sharetransfers = new ShareTransfer;
            $sharetransfers->user_id = $user->id;
            $sharetransfers->company_id = $request->company_id;
            $sharetransfers->share_transfer_from = $request->share_transfer_from;
            $sharetransfers->share_transfer_to = $request->share_transfer_to;
            $sharetransfers->no_of_share = $request->no_of_share;
            $sharetransfers->save();

    }
    if (!$sharetransfers) {
     return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
 } else {
    return response()->json(['code' => 200, 'message' => 'Share Transfer added Successfully!']);
 }
}

    public function updateShareTransfer(Request $request)
    {
         $validator = Validator::make($request->all(), [

            'sharetransfer_id' => 'required|exists:share_transfers,id',
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',    
        ], [            
            'sharetransfer_id.required' => 'Share Transfer id is required.',
            'user_id.required' => 'User id is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        }

        $sharetransfers = ShareTransfer::find($request->sharetransfer_id);
        $sharetransfers->user_id = $request->user_id;
        $sharetransfers->company_id = $request->company_id;
        $sharetransfers->share_transfer_from = $request->share_transfer_from;
        $sharetransfers->share_transfer_to = $request->share_transfer_to;
        $sharetransfers->no_of_share = $request->no_of_share;
        $sharetransfers->updated_at = Carbon::now();
        $sharetransfers->update();

        if (!$sharetransfers) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             } else {
                return response()->json(['code' => 200, 'message' => "Share transfer details updated successfully",'SharetransferData'=> $sharetransfers,'sharetransfer_id'=> $sharetransfers->id]);
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
