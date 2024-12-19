<?php

namespace App\Http\Controllers\unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Units;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;

class UnitsController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $com_id = Session::get('user_company_id');
        $accountunit = Units::where('company_id', $com_id)->where('delete', '=', '0')->get();
        return view('unit/accountUnit')->with('accountunit', $accountunit);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('unit/addAccountUnit');
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

        $unit = new Units;
        $unit->name = $request->input('name');
        $unit->company_id =  Session::get('user_company_id');
        $unit->s_name = $request->input('s_name');
        $unit->status = $request->input('status');
        $unit->save();

        if ($unit->id) {
            return redirect('account-unit')->withSuccess('Unit added successfully!');
        } else {
            $this->failedMessage();
        }
    }

    public function edit($id)
    {

        $editunit = Units::find($id);
        return view('unit/editAccountUnit')->with('editunit', $editunit);
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
