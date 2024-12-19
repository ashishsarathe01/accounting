<?php

namespace App\Http\Controllers\itemgroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemGroups;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;

class ItemGroupsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $com_id = Session::get('user_company_id');
        $itemgroups = ItemGroups::where('company_id', $com_id)->where('delete', '=', '0')->get();
        return view('itemgroup/accountItemGroup')->with('itemgroups', $itemgroups);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('itemgroup/addAccountItemGroup');
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
            'group_name' => 'required|string',
        ], [
            'group_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $items = new ItemGroups;
        $items->company_id =  Session::get('user_company_id');
        $items->group_name = $request->input('group_name');
        $items->status = $request->input('status');
        $items->save();

        if ($items->id) {
            return redirect('account-item-group')->withSuccess('Items group added successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $itemsgroup = ItemGroups::find($id);
        return view('itemgroup/editAccountItemsGroup')->with('itemsgroup', $itemsgroup);
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
            'group_name' => 'required|string',

        ], [
            'group_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $items =  ItemGroups::find($request->itemgroup_id);
        $items->group_name = $request->input('group_name');
        $items->status = $request->input('status');
        $items->updated_at = Carbon::now();
        $items->update();

        return redirect('account-item-group')->withSuccess('group item updated successfully!');
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $items =  ItemGroups::find($request->item_id);
        $items->delete = '1';
        $items->deleted_at = Carbon::now();
        $items->update();
        if ($items) {
            return redirect('account-item-group')->withSuccess('Group item deleted successfully!');
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return redirect('account-item-group')->withError('Something went wrong, please try again after some time.');
    }
}
