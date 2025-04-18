<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\Units;
use Carbon\Carbon;
use DB;


class ManageItemsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function itemList(Request $request)
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

        $Items = ManageItems::select('manage_items.*',DB::raw('(select name from units where units.id=manage_items.u_name limit 1 ) as unit_name'),DB::raw('(select group_name from item_groups where item_groups.id=manage_items.g_name limit 1 ) as group_name'),DB::raw('(select company_name from companies where companies.id = manage_items.company_id limit 1) as company_name'))->where(['delete'=>'0','company_id'=>$request->company_id])->get();

         if ($Items) 
         {
            return response()->json([
                'code' => 200,
                'data' => $Items,
                'dataCount' => $Items->count(),
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
    public function createItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string',
            'company_id' => 'required',
        ], [
            'item_name.required' => 'Name is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $items = new ManageItems;
        $items->company_id = $request->company_id;
        $items->name = $request->item_name;
        $items->p_name = $request->print_name;
        $items->u_name = $request->unit_id;
        $items->hsn_code = $request->hsn_code;
        $items->gst_rate = $request->gst_rate;
        $items->opening_balance_qty = $request->opening_balance_qty;
        $items->opening_balance_qt_type = $request->opening_balance_qty_type;
        $items->opening_balance = $request->opening_balance;
        $items->opening_balance_type = $request->opening_balance_type;
        $items->g_name = $request->group_id;
        $items->status = $request->status;
        $items->save();


        if ($items->id) {
            return response()->json(['code' => 200, 'message' => 'Items added successfully!','ItemData'=> $items,'ItemId'=> $items->id]);
         } 
         else 
         {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         }
    }

    public function GetItembyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
        ], [
            'item_id.required' => 'Item id is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $manageitems = ManageItems::select('manage_items.*',DB::raw('(select name from units where units.id=manage_items.u_name limit 1 ) as unit_name'),DB::raw('(select group_name from item_groups where item_groups.id=manage_items.g_name limit 1 ) as group_name'),DB::raw('(select company_name from companies where companies.id = manage_items.company_id limit 1) as company_name'))->where(['id'=>$request->item_id,'delete'=>"0"])->first();

        if ($manageitems) {
            return response()->json([
                'code' => 200,
                'data' => $manageitems,
                'dataCount' => $manageitems->count(),
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
    public function updateItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'item_name' => 'required|string',

        ], [
            'item_id.required' => 'Item id is required.',
            'item_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $items =  ManageItems::find($request->item_id);
        $items->name = $request->item_name;
        $items->p_name = $request->print_name;
        $items->u_name = $request->unit_id;
        $items->hsn_code = $request->hsn_code;
        $items->gst_rate = $request->gst_rate;
        $items->opening_balance_qty = $request->opening_balance_qty;
        $items->opening_balance_qt_type = $request->opening_balance_qty_type;
        $items->opening_balance = $request->opening_balance;
        $items->opening_balance_type = $request->opening_balance_type;
        $items->g_name = $request->group_id;
        $items->status = $request->status;
        $items->updated_at = Carbon::now();
        $items->update();

        if (!$items) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Item updated successfully!','ItemData'=> $items,'ItemId'=> $items->id]);
         }
    }

    public function deleteItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required'
        ], [
            'item_id.required' => 'Item id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $items =  ManageItems::find($request->item_id);
        $items->delete = '1';
        $items->deleted_at = Carbon::now();
        $items->update();
        if ($items) 
        {

            return response()->json(['code' => 200, 'message' => 'Item deleted successfully!']);
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
