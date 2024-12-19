<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseSundry;
use App\Models\PurchaseDescription;
use Carbon\Carbon;
use DB;


class PurchaseController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function PurchaseVoucherList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer',
        ], [
            'company_id.required' => 'Company id is required.',
        ]);
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        }

         $purchase = DB::table('purchases')
            ->select('purchases.id as purchases_id', 'purchases.date', 'purchases.voucher_no', 'purchase_descriptions.*')
            ->join('purchase_descriptions', 'purchase_descriptions.purchase_id', '=', 'purchases.id')
            ->where('purchases.company_id',$request->company_id)
            ->get();

            if ($purchase) {
            return response()->json([
                'code' => 200,
                'PurchaseData' => $purchase,
                'dataCount' => $purchase->count(),
            ]);
            } 
            else 
            {
                $this->failedMessage();
            }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createPurchaseVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
            'series_no' => 'required|string',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'series_no.required' => 'Series no. is required.',
        ]);
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        } 

        $purchase = new Purchase;
        $purchase->company_id = $request->company_id;
        $purchase->series_no = $request->series_no;
        $purchase->date = $request->date;
        $purchase->voucher_no = $request->voucher_no;
        $purchase->party = $request->party_id;
        $purchase->material_center = $request->material_center;
        $purchase->tax_rate = $request->tax_rate;
        $purchase->taxable_amt = $request->taxable_amt;
        $purchase->total = $request->total;
        $purchase->self_vehicle = $request->self_vehicle;
        $purchase->vehicle_no = $request->vehicle_no;
        $purchase->invoice_date = $request->invoice_date;
        $purchase->save();

        if ($purchase->id) {

            $goods_discriptions = $request->goods_discription;
            $qtys = $request->qty;
            $units = $request->unit;
            $prices = $request->price;
            $amounts = $request->amount;

            foreach ($goods_discriptions as $key => $good) {

                $desc = new PurchaseDescription;

                $desc->purchase_id = $purchase->id;
                $desc->goods_discription = $good;
                $desc->qty = $qtys[$key];
                $desc->unit = $units[$key];
                $desc->price = $prices[$key];
                $desc->amount = $amounts[$key];
                $desc->status = '1';
                $desc->save();
            }

            $bill_sundrys = $request->bill_sundry;
            $tax_amts = $request->tax_amt;
            $bill_sundry_amounts = $request->bill_sundry_amount;

            foreach ($bill_sundrys as $key => $bill) 
            {

                $sundry = new PurchaseSundry;

                $sundry->purchase_id = $purchase->id;
                $sundry->bill_sundry = $bill;
                $sundry->rate = $tax_amts[$key];
                $sundry->amount = $bill_sundry_amounts[$key];
                $sundry->status = '1';
                $sundry->save();
            }

             return response()->json(['code' => 200, 'message' => 'Purchase voucher added successfully!','PurchaseData'=> $purchase,'Purchaseid'=> $purchase->id]);
        } 
        else 
        {
            $this->failedMessage();
        }
    }

    public function GetPurchaseVoucherbyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ], [
            'id.required' => 'Id is required.',
        ]);
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        }
    }

    public function updatePurchaseVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'series_no' => 'required|string',
        ], [
            'series_no.required' => 'Series no. is required.',
        ]);
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        } 
    }


    public function deletePurchaseVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ], [
            'id.required' => 'Id is required.',
        ]);
        if ($validator->fails()) 
        {
            return response()->json($validator->errors(), 422);
        }
    }


    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
