<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Units;
use Carbon\Carbon;
use DB;

class UnitsController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function unitList(Request $request)
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

        $accountunit = Units::select('units.*',DB::raw('(select company_name from companies where companies.id = units.company_id limit 1) as company_name'))->where(['delete'=>'0','company_id'=>$request->company_id])->get();
        if ($accountunit) {
            return response()->json([
                'code' => 200,
                'data' => $accountunit,
                'dataCount' => $accountunit->count(),
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
    public function createUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'company_id' => 'required',
        ], [
            'name.required' => 'Name is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $unit = new Units;
        $unit->company_id = $request->company_id;
        $unit->name = $request->name;
        $unit->s_name = $request->s_name;
        $unit->status = $request->status;
        $unit->save();


        if ($unit) {
            return response()->json(['code' => 200, 'message' => 'Unit added successfully!','UnitData'=> $unit,'UnitId'=> $unit->id]);
         } 
         else 
         {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         }
    }

    public function GetUnitbyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'Unit id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $Units = Units::select('units.*',DB::raw('(select company_name from companies where companies.id = units.company_id limit 1) as company_name'))->where(['id'=>$request->id,'delete'=>"0"])->first();

        if ($Units) {
            return response()->json([
                'code' => 200,
                'data' => $Units,
                'dataCount' => $Units->count(),
            ]);
        } else {
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
    public function updateUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|numeric',
            'name' => 'required|string',

        ], [
            'unit_id.required' => 'unit id is required.',
            'name.required' => 'Name is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $unit =  Units::find($request->unit_id);
        $unit->name = $request->name;
        $unit->s_name = $request->s_name;
        $unit->status = $request->status;
        $unit->updated_at = Carbon::now();
        $unit->update();

        if (!$unit) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Unit updated successfully','UnitData'=> $unit,'UnitId'=> $unit->id]);
         }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function deleteUnit(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|numeric'
        ], [
            'unit_id.required' => 'Unit id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $unit =  Units::find($request->unit_id);
        $unit->delete = '1';
        $unit->deleted_at = Carbon::now();
        $unit->update();

        if ($unit) {

            return response()->json(['code' => 200, 'message' => 'Unit deleted successfully!']);
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
