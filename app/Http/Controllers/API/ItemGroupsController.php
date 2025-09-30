<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ItemGroups;
use Carbon\Carbon;
use DB;


class ItemGroupsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function itemGroupList(Request $request)
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

        $ItemGroups = ItemGroups::select('item_groups.id','item_groups.company_id','item_groups.group_name','item_groups.parameterized_stock_status','item_groups.config_status','item_groups.no_of_parameter','item_groups.alternative_qty',DB::raw('(select company_name from companies where companies.id = item_groups.company_id limit 1) as company_name'))->where(['delete'=>'0','company_id'=>$request->company_id])->get();

         if ($ItemGroups) {
            return response()->json([
                'code' => 200,
                'data' => $ItemGroups,
                'dataCount' => $ItemGroups->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createItemGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string',
            'company_id' => 'required',
        ], [
            'group_name.required' => 'Group Name is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ItemGroup = new ItemGroups;
        $ItemGroup->company_id = $request->company_id;
        $ItemGroup->group_name = $request->group_name;
        $ItemGroup->status = $request->status;
        $ItemGroup->save();

        if ($ItemGroup->id) {
            return response()->json(['code' => 200, 'message' => 'Items group added successfully!','UnitData'=> $ItemGroup,'UnitId'=> $ItemGroup->id]);
         } 
         else 
         {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         }

    }

    public function GetItemGroupbyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_group_id' => 'required|numeric',
        ], [
            'item_group_id.required' => 'Item group id is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $itemsgroup = ItemGroups::select('item_groups.*',DB::raw('(select company_name from companies where companies.id = item_groups.company_id limit 1) as company_name'))->where(['id'=>$request->item_group_id,'delete'=>"0"])->first();

        if ($itemsgroup) {
            return response()->json([
                'code' => 200,
                'data' => $itemsgroup,
                'dataCount' => $itemsgroup->count(),
            ]);
        }
         else {
            $this->failedMessage();
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function updateItemGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_group_id' => 'required',
            'group_name' => 'required|string',

        ], [
            'item_group_id.required' => 'Item group id is required.',
            'group_name.required' => 'Group Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $ItemGroup =  ItemGroups::find($request->item_group_id);
        $ItemGroup->group_name = $request->group_name;
        $ItemGroup->status = $request->status;
        $ItemGroup->updated_at = Carbon::now();
        $ItemGroup->update();


        if (!$ItemGroup) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Group item updated successfully!','GroupData'=> $ItemGroup,'GroupItemId'=> $ItemGroup->id]);
         }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function deleteItemGroup(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'item_group_id' => 'required|numeric'
        ], [
            'item_group_id.required' => 'Item group id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $ItemGroups =  ItemGroups::find($request->item_group_id);
        $ItemGroups->delete = '1';
        $ItemGroups->deleted_at = Carbon::now();
        $ItemGroups->update();
        if ($ItemGroups) 
        {

            return response()->json(['code' => 200, 'message' => 'Item group deleted successfully!']);
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
