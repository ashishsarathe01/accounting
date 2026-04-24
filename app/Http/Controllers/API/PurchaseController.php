<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseSundry;
use App\Models\PurchaseDescription;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ManageItems;
use App\Models\ParameterInfo;
use App\Models\ParameterInfoValue;
use App\Models\ParameterInfoValueDetail;
use App\Models\ItemAverage;
use App\Models\SparePart;
use App\Models\SparePartItem;
use App\Models\ItemAverageDetail;
use App\Models\PurchaseParameterInfo;
use App\Models\ItemParameterStock;
use App\Models\SupplierPurchaseVehicleDetail;
use App\Models\SaleOrderSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use DB;
use Session;
use DateTime;
use Gate;


class PurchaseController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   

        public function PurchaseVoucherList(Request $request)
        {
            $company_id     = $request->company_id;
            $financial_year = $request->financial_year;
            $from_date      = $request->from_date;
            $to_date        = $request->to_date;
        
            if (!$company_id || !$financial_year) {
                return response()->json([
                    'status'  => false,
                    'message' => 'company_id and financial_year are required'
                ], 400);
            }
        
            /*
            |--------------------------------------------------------------------------
            | Financial Year Processing
            |--------------------------------------------------------------------------
            */
            $y = explode("-", $financial_year);
            $from = DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = DateTime::createFromFormat('y', $y[1])->format('Y');
        
            $month_arr = [
                $from.'-04', $from.'-05', $from.'-06', $from.'-07',
                $from.'-08', $from.'-09', $from.'-10', $from.'-11',
                $from.'-12', $to.'-01', $to.'-02', $to.'-03'
            ];
        
            /*
            |--------------------------------------------------------------------------
            | Base Query
            |--------------------------------------------------------------------------
            */
            $query = Purchase::with([
                'purchaseDescription' => function ($query) {
                    $query->with([
                        'item:id,name',
                        'units:id,name',
                        'parameterColumnInfo' => function ($q2) {
        
                            $q2->leftJoin('item_paremeter_list as param1', 'purchase_parameter_info.parameter1_id', '=', 'param1.id');
                            $q2->leftJoin('item_paremeter_list as param2', 'purchase_parameter_info.parameter2_id', '=', 'param2.id');
                            $q2->leftJoin('item_paremeter_list as param3', 'purchase_parameter_info.parameter3_id', '=', 'param3.id');
                            $q2->leftJoin('item_paremeter_list as param4', 'purchase_parameter_info.parameter4_id', '=', 'param4.id');
                            $q2->leftJoin('item_paremeter_list as param5', 'purchase_parameter_info.parameter5_id', '=', 'param5.id');
        
                            $q2->select(
                                'purchase_parameter_info.id',
                                'purchase_desc_row_id',
                                'parameter1_id', 'parameter2_id', 'parameter3_id', 'parameter4_id', 'parameter5_id',
                                'parameter1_value', 'parameter2_value', 'parameter3_value', 'parameter4_value', 'parameter5_value',
                                'param1.paremeter_name as paremeter_name1',
                                'param2.paremeter_name as paremeter_name2',
                                'param3.paremeter_name as paremeter_name3',
                                'param4.paremeter_name as paremeter_name4',
                                'param5.paremeter_name as paremeter_name5'
                            );
                        }
                    ]);
        
                    $query->select(
                        'id',
                        'goods_discription',
                        'qty',
                        'purchase_id',
                        'unit'
                    );
                },
                'account:id,account_name'
            ])
            ->select([
                'id',
                DB::raw("DATE_FORMAT(purchases.date, '%d/%m/%Y') as date"),
                'voucher_no',
                'total',
                'party',
                DB::raw("(SELECT voucher_no 
                          FROM supplier_purchase_vehicle_details 
                          WHERE map_purchase_id = purchases.id 
                          LIMIT 1) AS vehicle_voucher_no")
            ])
            ->where('company_id', $company_id)
            ->where('delete', '0');
        
            /*
            |--------------------------------------------------------------------------
            | Date Filtering
            |--------------------------------------------------------------------------
            */
            if (!empty($from_date) && !empty($to_date)) {
        
                $query->whereBetween('purchases.date', [
                    date('Y-m-d', strtotime($from_date)),
                    date('Y-m-d', strtotime($to_date))
                ]);
        
                $query->orderBy('purchases.date', 'ASC')
                      ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
        
            } else {
        
                $query->orderBy('purchases.date', 'DESC')
                      ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
                      ->limit(10);
            }
        
            /*
            |--------------------------------------------------------------------------
            | Execute Query
            |--------------------------------------------------------------------------
            */
            $purchase = $query->get();
        
            if (empty($from_date) && empty($to_date)) {
                $purchase = $purchase->reverse()->values();
            }
        
            /*
            |--------------------------------------------------------------------------
            | Return JSON
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'code'        => 200,
                'message'       => 'Purchase list fetched successfully',
                'month_arr'     => $month_arr,
                'from_date'     => $from_date,
                'to_date'       => $to_date,
                'total_records' => $purchase->count(),
                'data'          => $purchase
            ]);
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
    public function purchaseItemType(Request $request)
    {
        $setting  = SaleOrderSetting::join("item_groups","sale-order-settings.item_id","=","item_groups.id")
                        ->where('sale-order-settings.company_id',$request->company_id)
                        ->where('setting_type','PURCHASE GROUP')
                        ->where('setting_for','PURCHASE ORDER')
                        ->where('sale-order-settings.status','1')
                        ->select('group_name','group_type')
                        ->get();
        return response()->json(['code' => 200, 'message' => 'Purchase Item Type','data'=> $setting]);
    }
    
}
