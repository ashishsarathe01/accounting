<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use Carbon\Carbon;
use DB;


class SalesController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function SalesVoucherList(Request $request)
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

         $sales = DB::table('sales')
            ->select('sales.id as sales_id','sales.date','sales.voucher_no','sale_descriptions.*')
            ->join('sale_descriptions', 'sale_descriptions.sale_id', '=', 'sales.id')->where('sales.company_id',$request->company_id)
            ->get();

        if ($sales) {
            return response()->json([
                'code' => 200,
                'SalesData' => $sales,
                'dataCount' => $sales->count(),
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
    public function createSalesVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'party' => 'required',
            'material_center' => 'required',
            'taxable_amt' => 'required',
            'total' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'amount' => 'required',
            'bill_sundry' => 'required',
            'tax_amt' => 'required',
            'bill_sundry_amount' => 'required',
            'self_vehicle' => 'required',
            'vehicle_no' => 'sometimes',
            'transport_name' => 'sometimes',
            'reverse_charge' => 'sometimes',
            'gr_pr_no' => 'sometimes',
            'station' => 'sometimes',
            'station' => 'sometimes',
            'shipping_name' => 'sometimes',
            'shipping_address' => 'sometimes',
            'shipping_pincode' => 'sometimes',
            'shipping_gst' => 'sometimes',
            'shipping_pan' => 'sometimes',
            'ewaybill_no' => 'sometimes',
        ], [
            'company_id.required' => 'Company id is required.',
            'series_no.required' => 'Series number is required.',
            'date.required' => 'Date is required.',
            'voucher_no.required' => 'Voucher number is required.',
            'party.required' => 'Party id is required.',
            'material_center.required' => 'Material center is required.',
            'tax_rate.required' => 'Tax rate is required.',
            'tax.required' => 'Tax is required.',
            'total.required' => 'Total is required.',
            'goods_discription.required' => 'Goods discription is required.',
            'qty.required' => 'Goods qty is required.',
            'unit.required' => 'Goods unit is required.',
            'price.required' => 'Goods price is required.',
            'amount.required' => 'Goods amounts is required.',
            'bill_sundry.required' => 'Bill sundry is required.',
            'tax_amt.required' => 'Bill sundry tax amount is required.',
            'bill_sundry_amount.required' => 'Bill sundry amount is required.',
            'self_vehicle.required' => 'Self vehicle is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        // print_r($request->goods_discription);
        // die;

        $sale = new Sales;
        $sale->company_id = $request->company_id;
        $sale->series_no = $request->series_no;
        $sale->date = $request->date;
        $sale->voucher_no = $request->voucher_no;
        $sale->party = $request->party;
        $sale->material_center = $request->material_center;


        //$sale->tax_rate = $request->tax_rate;
        $sale->taxable_amt = $request->taxable_amt;
        //$sale->tax = $request->tax;
        $sale->total = $request->total;

        $sale->self_vehicle = $request->self_vehicle;
        $sale->transport_name = $request->transport_name;
        $sale->reverse_charge = $request->reverse_charge;
        $sale->gr_pr_no = $request->gr_pr_no;
        $sale->station = $request->station;

        $sale->shipping_name = $request->shipping_name;
        $sale->shipping_address = $request->shipping_address;
        $sale->shipping_pincode = $request->shipping_pincode;
        $sale->shipping_gst = $request->shipping_gst;
        $sale->shipping_pan = $request->shipping_pan;
        //$sale->status = $request->status;
        $sale->save();

        if ($sale->id) {

            $goods_discriptions = $request->goods_discription;
            $qtys = $request->qty;
            $units = $request->unit;
            $prices = $request->price;
            $amounts = $request->amount;

            foreach ($goods_discriptions as $key => $good) {

                $desc = new SaleDescription;

                $desc->sale_id = $sale->id;
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

            foreach ($bill_sundrys as $key => $bill) {

                $sundry = new SaleSundry;

                $sundry->sale_id = $sale->id;
                $sundry->bill_sundry = $bill;
                $sundry->rate = $tax_amts[$key];
                $sundry->amount = $bill_sundry_amounts[$key];
                $sundry->status = '1';
                $sundry->save();
            }

            return response()->json(['code' => 200, 'message' => 'Sale voucher added successfully!','SalesData'=> $sale,'Salesid'=> $sale->id]);

        } else {
            $this->failedMessage();
        }
    }

    public function GetSalesVoucherbyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|integer',
        ], [
            'sales_id.required' => 'Sales voucher id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $saleData = Sales::select('sales.id as sales_id','sales.date','sales.voucher_no','sale_descriptions.*')->join('sale_descriptions', 'sale_descriptions.sale_id', '=', 'sales.id')->where('sales.id',$request->sales_id)->first();
            

        if ($saleData) {
            return response()->json([
                'code' => 200,
                'data' =>$saleData,
                'dataCount' => $saleData->count(),
            ]);
        } else {
            $this->failedMessage();
        }

    }

    public function updateSalesVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'party' => 'required',
            'material_center' => 'required',
            'tax_rate' => 'required',
            'taxable_amt' => 'required',
            'tax' => 'required',
            'total' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'price' => 'required',
            'amount' => 'required',
            'bill_sundry' => 'required',
            'tax_amt' => 'required',
            'bill_sundry_amount' => 'required',
            'self_vehicle' => 'required',
            'transport_name' => 'required',
        ], [
            'company_id.required' => 'Company id is required.',
            'series_no.required' => 'Series number is required.',
            'date.required' => 'Date is required.',
            'voucher_no.required' => 'Voucher number is required.',
            'party.required' => 'Party id is required.',
            'material_center.required' => 'Material center is required.',
            'tax_rate.required' => 'Tax rate is required.',
            'tax.required' => 'Tax is required.',
            'total.required' => 'Total is required.',
            'goods_discription.required' => 'Goods discription is required.',
            'qty.required' => 'Goods qty is required.',
            'unit.required' => 'Goods unit is required.',
            'price.required' => 'Goods price is required.',
            'amount.required' => 'Goods amounts is required.',
            'bill_sundry.required' => 'Bill sundry is required.',
            'tax_amt.required' => 'Bill sundry tax amount is required.',
            'bill_sundry_amount.required' => 'Bill sundry amount is required.',
            'self_vehicle.required' => 'Self vehicle is required.',
            'transport_name.required' => 'Transport name is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        // print_r($request->goods_discription);
        // die;

        $sale = new Sales;
        $sale->company_id = $request->company_id;
        $sale->series_no = $request->series_no;
        $sale->date = $request->date;
        $sale->voucher_no = $request->voucher_no;
        $sale->party = $request->party;
        $sale->material_center = $request->material_center;


        $sale->tax_rate = $request->tax_rate;
        $sale->taxable_amt = $request->taxable_amt;
        $sale->tax = $request->tax;
        $sale->total = $request->total;

        $sale->self_vehicle = $request->self_vehicle;
        $sale->transport_name = $request->transport_name;
        $sale->reverse_charge = $request->reverse_charge;
        $sale->gr_pr_no = $request->gr_pr_no;
        $sale->station = $request->station;

        $sale->shipping_name = $request->shipping_name;
        $sale->shipping_address = $request->shipping_address;
        $sale->shipping_pincode = $request->shipping_pincode;
        $sale->shipping_gst = $request->shipping_gst;
        $sale->shipping_pan = $request->shipping_pan;
        //$sale->status = $request->status;
        $sale->save();

        if ($sale->id) {

            $goods_discriptions = $request->goods_discription;
            $qtys = $request->qty;
            $units = $request->unit;
            $prices = $request->price;
            $amounts = $request->amount;

            foreach ($goods_discriptions as $key => $good) {

                $desc = new SaleDescription;

                $desc->sale_id = $sale->id;
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

            foreach ($bill_sundrys as $key => $bill) {

                $sundry = new SaleSundry;

                $sundry->sale_id = $sale->id;
                $sundry->bill_sundry = $bill;
                $sundry->rate = $tax_amts[$key];
                $sundry->amount = $bill_sundry_amounts[$key];
                $sundry->status = '1';
                $sundry->save();
            }

            return response()->json(['code' => 200, 'message' => 'Sale voucher added successfully!','SalesData'=> $sale,'Salesid'=> $sale->id]);

        } else {
            $this->failedMessage();
        }
    }

    public function deleteSalesVoucher(Request $request)
    {
        //
    }

    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
