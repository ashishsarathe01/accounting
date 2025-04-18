<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\User;
use Carbon\Carbon;
use DB;

class AccountGroupsController extends Controller
{

// List

    public function accountGroupsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'company_id' => 'required',

        ], 
        [
            'company_id.required' => 'Company id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $AccountGroups = AccountGroups::select('account_groups.*',DB::raw('(select name from account_headings where account_headings.id=account_groups.heading limit 1 ) AS heading_name'),DB::raw('(CASE WHEN account_groups.bs_profile = 1 THEN "Liabilities" ELSE "Assets" END) AS bs_profile_name'),DB::raw('(select name_sch_three from account_headings where account_headings.id=account_groups.heading_as_sch limit 1 ) AS heading_as_sch_name'),DB::raw('(CASE WHEN account_groups.bs_profile_as_sch = 1 THEN "Equity And Liabilities" ELSE "Assets" END) AS bs_profile_as_sch_name'),DB::raw('(select company_name from companies where companies.id = account_groups.company_id limit 1) as company_name'))->where(['delete'=> "0",'company_id'=>$request->company_id])->get();

        if ($AccountGroups) {
            return response()->json([
                'code' => 200,
                'data' => $AccountGroups,
                'dataCount' => $AccountGroups->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }

// Store

    public function createAccountGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|string',
           'company_id' => 'required'

        ], 
        [
            'name.required' => 'Name is required.',
            'company_id.required' => 'Company id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $accountGroups = new AccountGroups;
        $accountGroups->name = $request->name;
        $accountGroups->company_id = $request->company_id;
        $accountGroups->primary = $request->primary;
        $accountGroups->heading = $request->heading;
        $accountGroups->bs_profile = $request->bs_profile;
        $accountGroups->name_as_sch = $request->name_as_sch;
        $accountGroups->primary_as_sch = $request->primary_as_sch;
        $accountGroups->heading_as_sch = $request->heading_as_sch;
        $accountGroups->bs_profile_as_sch = $request->bs_profile_as_sch;
        $accountGroups->status = $request->status;
        $accountGroups->save();

        if (!$accountGroups) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Account Group Created successfully!','AccountGroupsData'=> $accountGroups,'AccountGroupId'=> $accountGroups->id]);
         }
    }


// get by Id
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => 'Account GroupId id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

      //  print_r($request->all()); die;

        $AccountGroups = AccountGroups::select('account_groups.*',DB::raw('(select name from account_headings where account_headings.id=account_groups.heading limit 1 ) AS heading_name'),DB::raw('(CASE WHEN account_groups.bs_profile = 1 THEN "Liabilities" ELSE "Assets" END) AS bs_profile_name'),DB::raw('(select name_sch_three from account_headings where account_headings.id=account_groups.heading_as_sch limit 1 ) AS heading_as_sch_name'),DB::raw('(CASE WHEN account_groups.bs_profile_as_sch = 1 THEN "Equity And Liabilities" ELSE "Assets" END) AS bs_profile_as_sch_name'),DB::raw('(select company_name from companies where companies.id = account_groups.company_id limit 1) as company_name'))->where(['id'=>$request->id,'delete'=>"0"])->first();

        if ($AccountGroups) {
            return response()->json([
                'code' => 200,
                'data' => $AccountGroups,
                'dataCount' => $AccountGroups->count(),
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
    public function updateAccountGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required|string'

        ], [
            'id.required' => 'Account GroupId is required.',
            'name.required' => 'Name is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $accountGroups =  AccountGroups::find($request->id);
        $accountGroups->name = $request->name;
        $accountGroups->primary = $request->primary;
        $accountGroups->heading = $request->heading;
        $accountGroups->bs_profile = $request->bs_profile;
        $accountGroups->name_as_sch = $request->name_as_sch;
        $accountGroups->primary_as_sch = $request->primary_as_sch;
        $accountGroups->heading_as_sch = $request->heading_as_sch;
        $accountGroups->bs_profile_as_sch = $request->bs_profile_as_sch;
        $accountGroups->status = $request->status;
        $accountGroups->updated_at = Carbon::now();
        $accountGroups->update();

        if (!$accountGroups) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Account Group updated successfully!','AccountGroupsData'=> $accountGroups,'AccountGroupId'=> $accountGroups->id]);
         }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function deleteAccountGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], [
            'id.required' => 'Account Group id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $accountGroups =  AccountGroups::find($request->id);
        $accountGroups->delete = '1';
        $accountGroups->deleted_at = Carbon::now();
        $accountGroups->update();

        if ($accountGroups) {

            return response()->json(['code' => 200, 'message' => 'Account Group deleted successfully!']);
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
