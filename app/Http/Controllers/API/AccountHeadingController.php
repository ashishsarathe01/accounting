<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AccountHeading;
use App\Models\User;
use Carbon\Carbon;
use DB;

class AccountHeadingController extends Controller
{

// List

    public function headingList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|numeric'
        ], [
            'company_id.required' => 'Company id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } 

        $AccountHeading = AccountHeading::select('account_headings.*',DB::raw('(CASE WHEN account_headings.bs_profile = 1 THEN "Liabilities" ELSE "Assets" END) AS bs_profile_name'),DB::raw('(CASE WHEN account_headings.bs_profile_three = 1 THEN "Equity And Liabilities" ELSE "Assets" END) AS bs_profile_three_name'),DB::raw('(select company_name from companies where companies.id = account_headings.company_id limit 1) as company_name'))->where(['delete'=>"0",'company_id'=>$request->company_id])->get();

        if ($AccountHeading) {
            return response()->json([
                'code' => 200,
                'data' => $AccountHeading,
                'dataCount' => $AccountHeading->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }

// Store

    public function createAccountHeading(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'company_id' => 'required',
            'bs_profile' => 'required|string'

        ], [
            'name.required' => 'Name is required.',
            'company_id.required' => 'Company id is required.',
            'bs_profile.required' => 'B/S Profile is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $checkHeading   = AccountHeading::where(['name'=> $request->name,'company_id'=>$request->company_id])->first();
  
        if ($checkHeading) {
            return response()->json(['code' => 422, 'message' => 'This heading name already exits!']);
        }


        $account = new AccountHeading;

        $account->company_id = $request->company_id;
        $account->name = $request->name;
        $account->bs_profile = $request->bs_profile;
        $account->name_sch_three = $request->name_sch_three;
        $account->bs_profile_three = $request->bs_profile_three;
        $account->status = $request->status;
        $account->save();

        if (!$account) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Heading Created successfully!','headingData'=> $account,'HeadingId'=> $account->id]);
         }
    }


// get by Id
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => 'Heading id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $AccountHeading = AccountHeading::select('account_headings.*',DB::raw('(CASE WHEN account_headings.bs_profile = 1 THEN "Liabilities" ELSE "Assets" END) AS bs_profile_name'),DB::raw('(CASE WHEN account_headings.bs_profile_three = 1 THEN "Equity And Liabilities" ELSE "Assets" END) AS bs_profile_three_name'),DB::raw('(select company_name from companies where companies.id = account_headings.company_id limit 1) as company_name'))->where(['id'=>$request->id,'delete'=>"0"])->first();

        if ($AccountHeading) {
            return response()->json([
                'code' => 200,
                'data' => $AccountHeading,
                'dataCount' => $AccountHeading->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountHeading  $fooditem
     * @return \Illuminate\Http\Response
     */

// Update
    public function updateAccountHeading(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string',
            'bs_profile' => 'required|string',
            'name_sch_three' => 'sometimes',
            'bs_profile_three' => 'sometimes',

        ], [
            'name.required' => 'Name is required.',
            'bs_profile.required' => 'B/S Profile is required.',
            'name_sch_three.required' => 'Name As Sch is required.',
            'bs_profile_three.required' => 'B/S Profile is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account =  AccountHeading::find($request->id);
        $account->name = $request->name;
        $account->bs_profile = $request->bs_profile;
        $account->name_sch_three = $request->name_sch_three;
        $account->bs_profile_three = $request->bs_profile_three;
        $account->status = $request->status;
        $account->updated_at = Carbon::now();
        $account->update();

        if (!$account) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Account Heading updated successfully!','headingData'=> $account,'HeadingId'=> $account->id]);
         }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function deleteAccountHeading(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => 'Heading id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account =  AccountHeading::find($request->id);
        $account->delete = '1';
        $account->deleted_at = Carbon::now();
        $account->update();

        if ($account) {

            return response()->json(['code' => 200, 'message' => 'Account Heading deleted successfully!']);
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
