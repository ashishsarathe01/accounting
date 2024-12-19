<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillSundrys;
use App\Models\Accounts;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BillSundrysController extends Controller
{
     /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $billsundry = BillSundrys::where('delete', '=', '0')->get();
        return view('billSundrys')->with('billsundry', $billsundry);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $account = Accounts::where('delete', '=', '0')->get();
        return view('addbillSundrys')->with('account', $account);
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

        $account = new BillSundrys;
        $account->name = $request->input('name');
        $account->bill_sundry_type = $request->input('bill_sundry_type');
        $account->adjust_sale_amt = $request->input('adjust_sale_amt');
        $account->sale_amt_account = $request->input('sale_amt_account');
        $account->adjust_purchase_amt = $request->input('adjust_purchase_amt');
        $account->purchase_amt_account = $request->input('purchase_amt_account');
        $account->status = $request->input('status');
        $account->save();

        if ($account->id) {
            return redirect('account-bill-sundry')->withSuccess('Bill sundry created successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $editbill = BillSundrys::find($id);
        $account = Accounts::where('delete', '=', '0')->get();
        return view('editBillSundrys')->with('editbill', $editbill)->with('account', $account);
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
        
        $account =  BillSundrys::find($request->bill_id);
        $account->name = $request->input('name');
        $account->bill_sundry_type = $request->input('bill_sundry_type');
        $account->adjust_sale_amt = $request->input('adjust_sale_amt');
        $account->sale_amt_account = $request->input('sale_amt_account');
        $account->adjust_purchase_amt = $request->input('adjust_purchase_amt');
        $account->purchase_amt_account = $request->input('purchase_amt_account');
        $account->status = $request->input('status');
        $account->updated_at = Carbon::now();
        $account->update();

        return redirect('account-bill-sundry')->withSuccess('Account bill sundry updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $bill =  BillSundrys::find($request->bill_id);
        $bill->delete = '1';
        $bill->deleted_at = Carbon::now();
        $bill->update();
        if ($bill) {
            return redirect('account-bill-sundry')->withSuccess('Account bill sundry deleted successfully!');
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
