<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accounts;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\State;
use App\Models\AccountGroups;

class AccountsController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $account = Accounts::where('delete', '=', '0')->get();

        return view('account')->with('account', $account);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $accountgroup = AccountGroups::where('delete', '=', '0')->get();
        $state_list = State::all();
        return view('addAccount')->with('state_list', $state_list)->with('accountgroup', $accountgroup);
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
            'account_name' => 'required|string',
        ], [
            'account_name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account = new Accounts;
        $account->account_name = $request->input('account_name');
        $account->print_name = $request->input('print_name');
        $account->under_group = $request->input('under_group');
        $account->under_group_s = $request->input('under_group_s');
        $account->opening_balance = $request->input('opening_balance');
        $account->address = $request->input('address');
        $account->gstin = $request->input('gstin');
        $account->country = $request->input('country');
        $account->state = $request->input('state');
        $account->pin_code = $request->input('pin_code');
        $account->pan = $request->input('pan');
        $account->email = $request->input('email');
        $account->mobile = $request->input('mobile');
        $account->contact_person = $request->input('contact_person');
        $account->whatsup_number = $request->input('whatsup_number');
        $account->maintain_bill_by_details = $request->input('maintain_bill_by_details');
        $account->credit_days = $request->input('credit_days');
        $account->limit = $request->input('limit');
        $account->price_change_sms = $request->input('price_change_sms');
        $account->bank_name = $request->input('bank_name');
        $account->bank_account_no = $request->input('bank_account_no');
        $account->ifsc_code = $request->input('ifsc_code');
        $account->depreciation_rate = $request->input('depreciation_rate');
        $account->yearly = $request->input('yearly');
        $account->per_tax = $request->input('per_tax');
        $account->company_act = $request->input('company_act');
        $account->gst_rate = $request->input('gst_rate');
        $account->hsn_code = $request->input('hsn_code');
        $account->status = $request->input('status');
        $account->save();

        if ($account->id) {
            return redirect('account')->withSuccess('Account added successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $account = Accounts::find($id);
        return view('editAccount')->with('account', $account);
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

        $unit =  Units::find($request->unit_id);
        $unit->name = $request->input('name');
        $unit->s_name = $request->input('s_name');
        $unit->status = $request->input('status');
        $unit->updated_at = Carbon::now();
        $unit->update();

        return redirect('account-unit')->withSuccess('Unit updated successfully!');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $unit =  Units::find($request->unit_id);
        $unit->delete = '1';
        $unit->deleted_at = Carbon::now();
        $unit->update();
        if ($unit) {
            return redirect('account-unit')->withSuccess('Account unit deleted successfully!');
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return redirect('account-unit')->withError('Something went wrong, please try again after some time.');
    }
}
