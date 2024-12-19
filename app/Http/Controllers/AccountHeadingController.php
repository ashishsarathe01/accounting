<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountHeading;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;

class AccountHeadingController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accountheading = AccountHeading::where('delete', '=', '0')->get();
        return view('accountHeading')->with('accountheading', $accountheading);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('addAccountHeading');
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
            'bs_profile' => 'required|string',
            'name_sch_three' => 'required|string',
            'bs_profile_three' => 'required|string',

        ], [
            'name.required' => 'Name is required.',
            'bs_profile.required' => 'B/S Profile is required.',
            'name_sch_three.required' => 'Name As Sch is required.',
            'bs_profile_three.required' => 'B/S Profile is required.',
        ]);
        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }
        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->route('account-heading.create')
                ->withErrors($validator)
                ->withInput();
        }
        $account = new AccountHeading;

        $account->name = $request->input('name');
        $account->bs_profile = $request->input('bs_profile');
        $account->name_sch_three = $request->input('name_sch_three');
        $account->bs_profile_three = $request->input('bs_profile_three');
        $account->status = $request->input('status');
        $account->save();

        if ($account->id) {
            return redirect('account-heading')->withSuccess('Account Heading Created successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $editheading = AccountHeading::find($id);
        return view('editAccountHeading')->with('editheading', $editheading);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountHeading  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'bs_profile' => 'required|string',
            'name_sch_three' => 'required|string',
            'bs_profile_three' => 'required|string',

        ], [
            'name.required' => 'Name is required.',
            'bs_profile.required' => 'B/S Profile is required.',
            'name_sch_three.required' => 'Name As Sch is required.',
            'bs_profile_three.required' => 'B/S Profile is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account =  AccountHeading::find($request->heading_id);
        $account->name = $request->input('name');
        $account->bs_profile = $request->input('bs_profile');
        $account->name_sch_three = $request->input('name_sch_three');
        $account->bs_profile_three = $request->input('bs_profile_three');
        $account->status = $request->input('status');
        $account->updated_at = Carbon::now();
        $account->update();

        return redirect('account-heading')->withSuccess('Account Heading updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $account =  AccountHeading::find($request->heading_id);
        $account->delete = '1';
        $account->deleted_at = Carbon::now();
        $account->update();
        if ($account) {
            return redirect('account-heading')->withSuccess('Account Heading deleted successfully!');
        }
    }
    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return redirect('account-heading')->withError('Something went wrong, please try again after some time.');
    }
}
