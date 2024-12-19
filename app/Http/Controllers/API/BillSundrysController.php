<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BillSundrys;
use App\Models\Accounts;
use Carbon\Carbon;
use DB;

class BillSundrysController extends Controller
{
     /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function billSundryList(Request $request)
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

        $billsundry = BillSundrys::select('bill_sundrys.*',DB::raw('(select company_name from companies where companies.id = bill_sundrys.company_id limit 1) as company_name'))->where(['delete'=>'0','company_id'=>$request->company_id])->get();

        if ($billsundry) {
            return response()->json([
                'code' => 200,
                'data' => $billsundry,
                'dataCount' => $billsundry->count(),
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
    public function createBillSundry(Request $request)
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

        $BillSundry = new BillSundrys;
        $BillSundry->company_id = $request->company_id;
        $BillSundry->name = $request->name;
        $BillSundry->bill_sundry_type = $request->bill_sundry_type;
        $BillSundry->adjust_sale_amt = $request->adjust_sale_amt;
        $BillSundry->sale_amt_account = $request->sale_amt_account;
        $BillSundry->adjust_purchase_amt = $request->adjust_purchase_amt;
        $BillSundry->purchase_amt_account = $request->purchase_amt_account;
        $BillSundry->status = $request->status;
        $BillSundry->save();


        if ($BillSundry->id) {
            return response()->json(['code' => 200, 'message' => 'Bill sundry created successfully!','BillSundryData'=> $BillSundry,'BillSundryId'=> $BillSundry->id]);
         } 
         else 
         {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         }

    }

    public function GetBillSundrybyId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'Bill Sundry id is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $BillSundrydata = BillSundrys::select('bill_sundrys.*',DB::raw('(select company_name from companies where companies.id = bill_sundrys.company_id limit 1) as company_name'))->find($request->id);
        

        if ($BillSundrydata) {

            $accountData = Accounts::where(['delete'=>'0','company_id'=>$BillSundrydata->company_id])->get();
            return response()->json([
                'code' => 200,
                'BillSundrydata' => $BillSundrydata,
                'BillSundrydataCount' => $BillSundrydata->count(),
                'accountData' => $accountData,
                'accountDataCount' => $accountData->count(),
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
    public function updateBillSundry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required',
            'name' => 'required|string',

        ], [
            'bill_id.required' => 'Bill id is required.',
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $BillSundry =  BillSundrys::find($request->bill_id);
        $BillSundry->name = $request->name;
        $BillSundry->bill_sundry_type = $request->bill_sundry_type;
        $BillSundry->adjust_sale_amt = $request->adjust_sale_amt;
        $BillSundry->sale_amt_account = $request->sale_amt_account;
        $BillSundry->adjust_purchase_amt = $request->adjust_purchase_amt;
        $BillSundry->purchase_amt_account = $request->purchase_amt_account;
        $BillSundry->status = $request->status;
        $BillSundry->updated_at = Carbon::now();
        $BillSundry->update();

        if (!$BillSundry) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         } else {
            return response()->json(['code' => 200, 'message' => 'Account bill sundry updated successfully!','BillSundrydata'=> $BillSundry,'BillSundryId'=> $BillSundry->id]);
         }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function deleteBillSundry(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'bill_id' => 'required'

        ], [
            'bill_id.required' => 'Bill id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $bill =  BillSundrys::find($request->bill_id);
        $bill->delete = '1';
        $bill->deleted_at = Carbon::now();
        $bill->update();

        if ($bill) 
        {

            return response()->json(['code' => 200, 'message' => 'Account bill sundry deleted successfully!']);
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
