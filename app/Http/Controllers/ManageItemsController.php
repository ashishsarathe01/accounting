<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\Units;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ManageItemsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $manageitems = ManageItems::all();
        return view('accountManageItem')->with('manageitems', $manageitems);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accountgroup = AccountGroups::where('delete', '=', '0')->get();
        $accountunit = Units::where('delete', '=', '0')->get();
        return view('addAccountManageItem')->with('accountunit', $accountunit)->with('accountgroup', $accountgroup);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ], [
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $items = new ManageItems;
        $items->name = $request->input('name');
        $items->p_name = $request->input('p_name');
        $items->g_name = $request->input('g_name');
        $items->u_name = $request->input('u_name');
        $items->hsn_code = $request->input('hsn_code');
        $items->gst_rate = $request->input('gst_rate');
        $items->opening_balance_qty = $request->input('opening_balance_qty');
        $items->opening_balance_qt_type = $request->input('opening_balance_qt_type');
        $items->opening_balance = $request->input('opening_balance');
        $items->opening_balance_type = $request->input('opening_balance_type');
      
        $items->status = $request->input('status');
        $items->save();

        if ($items->id) {
            return redirect('account-manage-item')->withSuccess('Items added successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $manageitems = ManageItems::find($id);
        return view('editAccountManageItems')->with('manageitems', $manageitems);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',

        ], [
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $items =  ManageItems::find($request->mangeitem_id);
        $items->name = $request->input('name');
        $items->p_name = $request->input('p_name');
        $items->g_name = $request->input('g_name');
        $items->u_name = $request->input('u_name');
        $items->opening_balance_cr = $request->input('opening_balance_cr');
        $items->opening_balance_qty = $request->input('opening_balance_qty');
        $items->gst_rate = $request->input('gst_rate');
        $items->hsn_code = $request->input('hsn_code');
        $items->status = $request->input('status');
        $items->updated_at = Carbon::now();
        $items->update();

        return redirect('account-manage-item')->withSuccess('item updated successfully!');
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
