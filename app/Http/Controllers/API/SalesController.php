<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\BillSundrys;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ParameterInfoValue;
use App\Models\SaleParameterInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\State;
use App\Models\Units;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Bank;
use App\Models\SaleInvoiceTermCondition;
use App\Models\EinvoiceToken;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\AccountOtherAddress;
use App\Models\ItemParameterStock;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SaleOrderItemGsm;
use App\Models\SaleOrderItemWeight;
use App\Models\SaleOrderItemGsmSize;
use App\Models\ItemSizeStock;
use App\Models\AccountGroups;
use App\Models\MerchantModuleMapping;
use App\Models\ActivityLog;
use App\Helpers\CommonHelper;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\GdImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\URL;
use DB;
use Session;
use DateTime;
use Gate;


class SalesController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */


            public function SalesVoucherList(Request $request)
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
                $query = DB::table('sales')
                    ->select(
                        'sales.id as sales_id',
                        DB::raw("DATE_FORMAT(sales.date, '%d/%m/%Y') as date"),
                        'sales.voucher_no',
                        'sales.voucher_no_prefix',
                        'sales.total',
                        'sales.financial_year',
                        'sales.series_no',
                        'sales.e_invoice_status',
                        'sales.e_waybill_status',
                        'sales.status',
                        'sales.sale_order_id',
                        DB::raw('(select account_name from accounts where accounts.id = sales.party limit 1) as account_name'),
                        DB::raw('(select manual_numbering from voucher_series_configurations 
                                  where voucher_series_configurations.company_id = '.$company_id.' 
                                  and configuration_for="SALE" 
                                  and voucher_series_configurations.status=1 
                                  and voucher_series_configurations.series = sales.series_no 
                                  limit 1) as manual_numbering_status'),
                        DB::raw('(select max(voucher_no) from sales as s 
                                  where s.company_id = '.$company_id.' 
                                  and s.delete="0" 
                                  and s.series_no = sales.series_no 
                                  and entry_source=1) as max_voucher_no')
                    )
                    ->where('sales.company_id', $company_id)
                    ->where('sales.delete', '0');
            
                /*
                |--------------------------------------------------------------------------
                | Date Filtering
                |--------------------------------------------------------------------------
                */
                if (!empty($from_date) && !empty($to_date)) {
            
                    $query->whereBetween('sales.date', [
                        date('Y-m-d', strtotime($from_date)),
                        date('Y-m-d', strtotime($to_date))
                    ]);
            
                    $query->orderBy('sales.date', 'ASC')
                          ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
            
                } else {
            
                    $query->orderBy('sales.date', 'DESC')
                          ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
                          ->limit(10);
                }
            
                /*
                |--------------------------------------------------------------------------
                | Execute Query
                |--------------------------------------------------------------------------
                */
                $sales = $query->get();
            
                if (empty($from_date) && empty($to_date)) {
                    $sales = $sales->reverse()->values();
                }
            
                /*
                |--------------------------------------------------------------------------
                | Return API Response
                |--------------------------------------------------------------------------
                */
                return response()->json([
                    'code'        => 200,
                    'message'       => 'Sales fetched successfully',
                    'month_arr'     => $month_arr,
                    'from_date'     => $from_date,
                    'to_date'       => $to_date,
                    'total_records' => $sales->count(),
                    'data'          => $sales
                ]);
            }
            
            public function SalesVoucherListToday(Request $request)
            {
                $company_id     = $request->company_id;
                $financial_year = $request->financial_year;
                $from_date      = $request->from_date;
                $to_date        = $request->to_date;
            
                if (!$company_id) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'company_id  are required'
                    ], 400);
                }
            
                /*
                |--------------------------------------------------------------------------
                | Financial Year Processing
                |--------------------------------------------------------------------------
                */
                // $y = explode("-", $financial_year);
                // $from = DateTime::createFromFormat('y', $y[0])->format('Y');
                // $to   = DateTime::createFromFormat('y', $y[1])->format('Y');
            
                // $month_arr = [
                //     $from.'-04', $from.'-05', $from.'-06', $from.'-07',
                //     $from.'-08', $from.'-09', $from.'-10', $from.'-11',
                //     $from.'-12', $to.'-01', $to.'-02', $to.'-03'
                // ];
            
                /*
                |--------------------------------------------------------------------------
                | Base Query
                |--------------------------------------------------------------------------
                */
                
                    
                    $query = DB::table('sales')
    ->select(
        'sales.id as sales_id',
        DB::raw("DATE_FORMAT(sales.date, '%d/%m/%Y') as date"),
        'sales.voucher_no',
        'sales.voucher_no_prefix',
        'sales.total',
        'sales.financial_year',
        'sales.series_no',
        'sales.e_invoice_status',
        'sales.e_waybill_status',
        'sales.status',
        'sales.sale_order_id',

        DB::raw('(select account_name from accounts where accounts.id = sales.party limit 1) as account_name'),

        DB::raw('(select manual_numbering from voucher_series_configurations
            where voucher_series_configurations.company_id = '.$company_id.'
            and configuration_for="SALE"
            and voucher_series_configurations.status=1
            and voucher_series_configurations.series = sales.series_no
            limit 1) as manual_numbering_status'),

        DB::raw('(select max(voucher_no) from sales as s
            where s.company_id = '.$company_id.'
            and s.delete="0"
            and s.series_no = sales.series_no
            and entry_source=1) as max_voucher_no'),

        DB::raw('(SELECT SUM(qty) FROM sale_descriptions WHERE sale_descriptions.sale_id = sales.id) as total_quantity')
    )
    ->where('sales.company_id', $company_id)
    ->where('sales.delete', '0');
            
                /*
                |--------------------------------------------------------------------------
                | Date Filtering
                |--------------------------------------------------------------------------
                */
                if (!empty($from_date) && !empty($to_date)) {
            
                    $query->whereBetween('sales.date', [
                        date('Y-m-d', strtotime($from_date)),
                        date('Y-m-d', strtotime($to_date))
                    ]);
            
                    $query->orderBy('sales.date', 'ASC')
                          ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
            
                } else {
            
                    $query->orderBy('sales.date', 'DESC')
                          ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
                          ->limit(10);
                }
            
                /*
                |--------------------------------------------------------------------------
                | Execute Query
                |--------------------------------------------------------------------------
                */
                $sales = $query->get();
            
                if (empty($from_date) && empty($to_date)) {
                    $sales = $sales->reverse()->values();
                }
            
                /*
                |--------------------------------------------------------------------------
                | Return API Response
                |--------------------------------------------------------------------------
                */
                return response()->json([
                    'code'        => 200,
                    'message'       => 'Sales fetched successfully',
                    'from_date'     => $from_date,
                    'to_date'       => $to_date,
                    'total_records' => $sales->count(),
                    'data'          => $sales
                ]);
            }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createSalesVoucher1(Request $request)
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
    
    
    public function createSalesVoucher(Request $request)
{
    try {

        DB::beginTransaction();

        $company_id     = $request->company_id;
        $financial_year = $request->financial_year;
        
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


        /* ===============================
           VALIDATION
        =============================== */

        $request->validate([
            'series_no' => 'required',
            'date' => 'required',
            'party_id' => 'required',
            'material_center' => 'required',
            'total' => 'required',
            'items' => 'required|array|min:1'
        ]);

        /* ===============================
           DUPLICATE CHECK
        =============================== */

        $check_invoice = Sales::where('company_id', $company_id)
            ->where('voucher_no', $request->voucher_no)
            ->where('series_no', $request->series_no)
            ->where('financial_year', $financial_year)
            ->where('delete', 0)
            ->first();

        if ($check_invoice) {
            return response()->json([
                'status' => false,
                'message' => 'Duplicate Invoice No.'
            ], 422);
        }

        /* ===============================
           VOUCHER NUMBER LOGIC
        =============================== */

        $voucher_no = $request->voucher_no;

        if ($request->manual_enter_invoice_no == 0) {

            $lastVoucher = Sales::where('company_id', $company_id)
                ->where('series_no', $request->series_no)
                ->where('financial_year', $financial_year)
                ->max(DB::raw("cast(voucher_no as SIGNED)"));

            if (!$lastVoucher) {
                $voucher_no = "001";
            } else {
                $voucher_no = sprintf("%03d", $lastVoucher + 1);
            }
        }

        /* ===============================
           CREATE SALE MASTER
        =============================== */

        $account = Accounts::find($request->party_id);
$billing_address = $account->address;
      $billing_pincode = $account->pin_code;
       $sale = new Sales;

            $sale->series_no       = $request->input('series_no');
            $sale->company_id      = $company_id;
            $sale->date            = $request->input('date');
            
            $voucher_prefix        = $request->input('voucher_prefix');
            $sale->voucher_no_prefix = $voucher_prefix;
            $sale->voucher_no      = $voucher_no; // already calculated above
            
            $sale->party           = $request->input('party_id');
            $sale->material_center = $request->input('material_center');
            $sale->address_id      = $request->input('address');
            
            $sale->taxable_amt     = $request->input('taxable_amt');
            $sale->total           = $request->input('total');
            
            $sale->self_vehicle    = $request->input('self_vehicle');
            $sale->vehicle_no      = $request->input('vehicle_no');
            $sale->merchant_gst    = $request->input('merchant_gst');
            $sale->transport_name  = $request->input('transport_name');
            $sale->reverse_charge  = $request->input('reverse_charge');
            $sale->gr_pr_no        = $request->input('gr_pr_no');
            $sale->station         = $request->input('station');
            $sale->ewaybill_no     = $request->input('ewaybill_no');
            
            /* ===============================
               BILLING DETAILS (FROM ACCOUNT)
            =============================== */
            
            $sale->billing_name     = $account->print_name;
            $sale->billing_address  = $billing_address;
            $sale->billing_pincode  = $billing_pincode;
            $sale->billing_gst      = $account->gstin;
            $sale->billing_pan      = $account->pan;
            $sale->billing_state    = $account->state;
            
            /* ===============================
               SHIPPING DETAILS
            =============================== */
            
            $sale->shipping_name     = $request->input('shipping_name');
            $sale->shipping_state    = $request->input('shipping_state');
            $sale->shipping_address  = $request->input('shipping_address');
            $sale->shipping_pincode  = $request->input('shipping_pincode');
            $sale->shipping_gst      = $request->input('shipping_gst');
            $sale->shipping_pan      = $request->input('shipping_pan');
            
            $sale->financial_year    = $financial_year;
            
            /* ===============================
               ROUND OFF CALCULATION
            =============================== */
            
            $roundoff = $request->input('total') - $request->input('taxable_amt');
            
            $sale->save();


        /* ===============================
           STORE ITEMS
        =============================== */

        $SaleLgr = 0;

        foreach ($request->items as $item) {

            $desc = new SaleDescription();
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $item['item_id'];
            $desc->qty = $item['qty'];
            $desc->unit = $item['unit'];
            $desc->price = $item['price'];
            $desc->amount = $item['amount'];
            $desc->company_id = $company_id;
            $desc->status = '1';
            $desc->save();

            $SaleLgr += $item['amount'];

            /* ITEM LEDGER */

            $itemLedger = new ItemLedger();
            $itemLedger->item_id = $item['item_id'];
            $itemLedger->out_weight = $item['qty'];
            $itemLedger->txn_date = $request->date;
            $itemLedger->price = $item['price'];
            $itemLedger->total_price = $item['amount'];
            $itemLedger->company_id = $company_id;
            $itemLedger->source = 1;
            $itemLedger->source_id = $sale->id;
            $itemLedger->created_by = $request->user_id;
            $itemLedger->created_at = date('Y-m-d H:i:s');
            $itemLedger->save();

            /* SIZE STOCK UPDATE */

            if (!empty($item['size_stock_ids'])) {
                ItemSizeStock::whereIn('id', $item['size_stock_ids'])
                    ->update([
                        'status' => 0,
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id
                    ]);
            }

            /* PARAMETER UPDATE */

            if (!empty($item['parameter_ids'])) {
                ItemParameterStock::whereIn('id', $item['parameter_ids'])
                    ->update([
                        'status' => 0,
                        'stock_out_id' => $sale->id,
                        'stock_out_type' => 'SALE'
                    ]);
            }
        }

        /* ===============================
           BILL SUNDRY
        =============================== */

        if (!empty($request->bill_sundry)) {

            foreach ($request->bill_sundry as $key => $sundry) {

                $billsundry = BillSundrys::find($sundry['id']);

                $saleSundry = new SaleSundry();
                $saleSundry->sale_id = $sale->id;
                $saleSundry->bill_sundry = $billsundry->id;
                $saleSundry->rate = $sundry['rate'];
                $saleSundry->amount = $sundry['amount'];
                $saleSundry->company_id = $company_id;
                $saleSundry->status = '1';
                $saleSundry->save();

                if ($billsundry->adjust_sale_amt == 'No') {

                    $ledger = new AccountLedger();
                    $ledger->account_id = $billsundry->sale_amt_account;

                    if ($billsundry->bill_sundry_type == 'subtractive') {
                        $ledger->debit = $sundry['amount'];
                    } else {
                        $ledger->credit = $sundry['amount'];
                    }

                    $ledger->txn_date = $request->date;
                    $ledger->series_no = $request->series_no;
                    $ledger->company_id = $company_id;
                    $ledger->financial_year = $financial_year;
                    $ledger->entry_type = 1;
                    $ledger->entry_type_id = $sale->id;
                    $ledger->map_account_id = $request->party_id;
                    $ledger->created_by = $request->user_id;
                    $ledger->created_at = date('Y-m-d H:i:s');
                    $ledger->save();

                } else {

                    if ($billsundry->bill_sundry_type == "additive") {
                        $SaleLgr += $sundry['amount'];
                    } else {
                        $SaleLgr -= $sundry['amount'];
                    }
                }
            }
        }

        /* ===============================
           PARTY LEDGER (DEBIT)
        =============================== */

        $partyLedger = new AccountLedger();
        $partyLedger->account_id = $request->party_id;
        $partyLedger->debit = $request->total;
        $partyLedger->txn_date = $request->date;
        $partyLedger->series_no = $request->series_no;
        $partyLedger->company_id = $company_id;
        $partyLedger->financial_year = $financial_year;
        $partyLedger->entry_type = 1;
        $partyLedger->entry_type_id = $sale->id;
        $partyLedger->map_account_id = 35;
        $partyLedger->created_by = $request->user_id;
        $partyLedger->created_at = date('Y-m-d H:i:s');
        $partyLedger->save();

        /* ===============================
           SALES LEDGER (CREDIT)
        =============================== */

        $salesLedger = new AccountLedger();
        $salesLedger->account_id = 35;
        $salesLedger->credit = $SaleLgr;
        $salesLedger->txn_date = $request->date;
        $salesLedger->series_no = $request->series_no;
        $salesLedger->company_id = $company_id;
        $salesLedger->financial_year = $financial_year;
        $salesLedger->entry_type = 1;
        $salesLedger->entry_type_id = $sale->id;
        $salesLedger->map_account_id = $request->party_id;
        $salesLedger->created_by = $request->user_id;
        $salesLedger->created_at = date('Y-m-d H:i:s');
        $salesLedger->save();

        /* ===============================
           ITEM AVERAGE
        =============================== */

        foreach ($request->items as $item) {

            $avg = new ItemAverageDetail();
            $avg->entry_date = $request->date;
            $avg->series_no = $request->series_no;
            $avg->item_id = $item['item_id'];
            $avg->type = 'SALE';
            $avg->sale_id = $sale->id;
            $avg->sale_weight = $item['qty'];
            $avg->company_id = $company_id;
            $avg->created_at = Carbon::now();
            $avg->save();

            CommonHelper::RewriteItemAverageByItemApi(
                $request->date,
                $item['item_id'],
                $request->series_no,
                $company_id
            );
        }

        DB::commit();

        return response()->json([
            'Code' => 200,
            'message' => 'Sale Stored Successfully',
            'sale_id' => $sale->id,
            'voucher_no' => $voucher_no
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
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
    
    public function saleInvoicePdfApi1(Request $request)
{
    $sale_id        = $request->sale_id;
    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;

    if(!$sale_id || !$company_id || !$financial_year){
        return response()->json([
            'code' => 400,
            'message' => 'sale_id, company_id and financial_year required'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Company Data
    |--------------------------------------------------------------------------
    */
    $company_data = Companies::join('states','companies.state','=','states.id')
        ->where('companies.id', $company_id)
        ->select(['companies.*','states.name as sname'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Items Detail
    |--------------------------------------------------------------------------
    */
    $items_detail = DB::table('sale_descriptions')
        ->where('sale_id', $sale_id)
        ->join('units', 'sale_descriptions.unit', '=', 'units.id')
        ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('accounts', 'accounts.id', '=', 'sales.party')
        ->select(
            'units.s_name as unit',
            'units.id as unit_id',
            'sale_descriptions.qty',
            'sale_descriptions.price',
            'sale_descriptions.amount',
            'manage_items.p_name',
            'manage_items.name',
            'manage_items.id as item_id',
            'sales.*',
            'accounts.*',
            'manage_items.hsn_code',
            'manage_items.gst_rate'
        )
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Sale Detail
    |--------------------------------------------------------------------------
    */
    $sale_detail = Sales::leftjoin('states','sales.billing_state','=','states.id')
        ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
        ->where('sales.id', $sale_id)
        ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Party Detail
    |--------------------------------------------------------------------------
    */
    $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
        ->where('accounts.id', $sale_detail->party)
        ->select(['accounts.*','states.name as sname'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Sale Sundry
    |--------------------------------------------------------------------------
    */
    $sale_sundry = DB::table('sale_sundries')
        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
        ->where('sale_id', $sale_id)
        ->select(
            'sale_sundries.bill_sundry',
            'sale_sundries.rate',
            'sale_sundries.amount',
            'bill_sundrys.name',
            'nature_of_sundry',
            'bill_sundry_type'
        )
        ->orderBy('sequence')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | GST DETAIL (Important Fix)
    |--------------------------------------------------------------------------
    */
    $gst_detail = DB::table('sale_sundries')
        ->select('rate','amount')
        ->where('sale_id', $sale_id)
        ->where('rate','!=',0)
        ->distinct()
        ->get();

    $max_gst = DB::table('sale_sundries')
        ->where('sale_id', $sale_id)
        ->where('rate','!=',0)
        ->max(DB::raw("cast(rate as SIGNED)"));

    if(count($gst_detail)>0){

        foreach ($gst_detail as $key => $value){

            $rate = $value->rate;

            // Intra state → double rate
            if(substr($sale_detail->merchant_gst,0,2) == substr($sale_detail->billing_gst,0,2)){
                $rate = $rate * 2;
                $max_gst = $max_gst * 2;
            }

            $taxable_amount = 0;

            foreach($items_detail as $item){
                if($item->gst_rate == $rate){
                    $taxable_amount += $item->amount;
                }
            }

            $gst_detail[$key]->rate = $rate;

            // Add OTHER sundry to max GST slab
            if($max_gst == $rate){

                $other_sundry = DB::table('sale_sundries')
                    ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                    ->where('sale_id',$sale_id)
                    ->where('nature_of_sundry','OTHER')
                    ->select('amount','bill_sundry_type')
                    ->get();

                foreach ($other_sundry as $v1) {
                    if($v1->bill_sundry_type=="additive"){
                        $taxable_amount += $v1->amount;
                    } else {
                        $taxable_amount -= $v1->amount;
                    }
                }
            }

            $gst_detail[$key]->taxable_amount = $taxable_amount;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bank Detail
    |--------------------------------------------------------------------------
    */
    $bank_detail = DB::table('banks')
        ->where('company_id', $company_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */
    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
        ->where('company_id',$company_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | QR & E-Invoice
    |--------------------------------------------------------------------------
    */
    $einvoice_data = null;
    $qrBase64 = null;

    if ($sale_detail && $sale_detail->e_invoice_status == 1 && !empty($sale_detail->einvoice_response)) {

        $einvoice_data = json_decode($sale_detail->einvoice_response);

        if (!empty($einvoice_data->SignedQRCode)) {

            $svgQr = QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->generate($einvoice_data->SignedQRCode);

            $qrBase64 = base64_encode($svgQr);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Seller GST Info
    |--------------------------------------------------------------------------
    */
    $seller_info = DB::table('gst_settings')
        ->join('states','gst_settings.state','=','states.id')
        ->where([
            'company_id' => $company_id,
            'gst_no' => $sale_detail->merchant_gst
        ])
        ->select(['gst_no','address','pincode','states.name as sname'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Financial Year Month Array
    |--------------------------------------------------------------------------
    */
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04',$from.'-05',$from.'-06',$from.'-07',
        $from.'-08',$from.'-09',$from.'-10',$from.'-11',
        $from.'-12',$to.'-01',$to.'-02',$to.'-03'
    ];

    /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */
    $pdf = Pdf::loadView('saleInvoicePdf', [
        'items_detail' => $items_detail,
        'sale_sundry' => $sale_sundry,
        'party_detail' => $party_detail,
        'month_arr' => $month_arr,
        'company_data' => $company_data,
        'sale_detail' => $sale_detail,
        'bank_detail' => $bank_detail,
        'configuration' => $configuration,
        'seller_info' => $seller_info,
        'gst_detail' => $gst_detail,
        'qrBase64' => $qrBase64,
        'einvoice_data' => $einvoice_data,
    ])->setPaper('A4');

    return $pdf->stream('SaleInvoice-'.$sale_detail->voucher_no.'.pdf');
}

public function saleInvoicePdfApi(Request $request)
{   
    
     $sale_id        = $request->sale_id;
    $company_id     = $request->company_id;
    
    // 🔁 SAME DATA LOGIC (no change)
    $company_data = Companies::join('states','companies.state','=','states.id')
        ->where('companies.id', $request->company_id)
        ->select(['companies.*','states.name as sname'])
        ->first();

    $items_detail = DB::table('sale_descriptions')->where('sale_id', $sale_id)
         ->select(
            'sale_descriptions.id as sale_description_id',
            'units.s_name as unit',
            'units.id as unit_id',
            'sale_descriptions.qty',
            'sale_descriptions.price',
            'sale_descriptions.amount',
            'manage_items.p_name',
            'manage_items.name',
            'manage_items.id as item_id',
            'sales.*',
            'accounts.*',
            'manage_items.hsn_code',
            'manage_items.gst_rate'
         )
        ->join('units', 'sale_descriptions.unit', '=', 'units.id')
        ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('accounts', 'accounts.id', '=', 'sales.party')
        ->get();
      foreach ($items_detail as $item) {
         $item->lines = DB::table('sale_description_lines')
            ->where('sale_description_id', $item->sale_description_id)
            ->orderBy('sort_order')
            ->get();
      }
    $sale_detail = Sales::leftjoin('states','sales.billing_state','=','states.id')
        ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
        ->where('sales.id', $sale_id)
        ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name'])
        ->first();
        $financial_year = $sale_detail->financial_year;
     $einvoice_data = null;
    $qrBase64 = null;
    
    if ($sale_detail && $sale_detail->e_invoice_status == 1 && !empty($sale_detail->einvoice_response)) {
    
        $einvoice_data = json_decode($sale_detail->einvoice_response);
    
        if (!empty($einvoice_data->SignedQRCode)) {
    
            // ✅ SVG QR (no imagick, no gd)
            $svgQr = QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->generate($einvoice_data->SignedQRCode);
    
            // base64 encode SVG
            $qrBase64 = base64_encode($svgQr);
        }
    }
    

    $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
        ->where('accounts.id', $sale_detail->party)
        ->select(['accounts.*','states.name as sname'])
        ->first();

    $sale_sundry = DB::table('sale_sundries')
        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
        ->where('sale_id', $sale_id)
        ->select('sale_sundries.bill_sundry','sale_sundries.rate','sale_sundries.amount','bill_sundrys.name','nature_of_sundry','bill_sundry_type')
        ->orderBy('sequence')
        ->get();

    $gst_detail = DB::table('sale_sundries')
        ->select('rate','amount')
        ->where('sale_id', $sale_id)
        ->where('rate','!=','0')
        ->distinct('rate')
        ->get();

    $max_gst = DB::table('sale_sundries')
        ->select('rate')
        ->where('sale_id', $sale_id)
        ->where('rate','!=','0')
        ->max(\DB::raw("cast(rate as SIGNED)"));

    if(count($gst_detail)>0){
        foreach ($gst_detail as $key => $value){
            $rate = $value->rate;
            if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
                $rate = $rate*2;
                $max_gst = $max_gst*2;
            }
            $taxable_amount = 0;
            foreach($items_detail as $item) {
                if($item->gst_rate==$rate){
                    $taxable_amount += $item->amount;
                }
            }
            $gst_detail[$key]->rate = $rate;

            if($max_gst==$rate){
                $sun = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                    ->select('amount','bill_sundry_type')
                    ->where('sale_id', $sale_id)
                    ->where('nature_of_sundry','OTHER')
                    ->get();

                foreach ($sun as $v1) {
                    if($v1->bill_sundry_type=="additive"){
                        $taxable_amount += $v1->amount;
                    } else if($v1->bill_sundry_type=="subtractive"){
                        $taxable_amount -= $v1->amount;
                    }
                }
            }
            $gst_detail[$key]->taxable_amount = $taxable_amount;
        }
    }

    $bank_detail = DB::table('banks')->where('company_id', $request->company_id)
        ->first();

    if($company_data->gst_config_type == "single_gst") {
        $GstSettings = DB::table('gst_settings')->where(['company_id' => $request->company_id, 'gst_type' => "single_gst"])->first();
        $seller_info = DB::table('gst_settings')
            ->join('states','gst_settings.state','=','states.id')
            ->where(['company_id' => $request->company_id, 'gst_type' => "single_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
            ->select(['gst_no','address','pincode','states.name as sname'])
            ->first();

        if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                ->where(['delete' => '0', 'company_id' => $sale_detail->company_id,'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                ->first();
            $state_info = DB::table('states')->where('id',$GstSettings->state)->first();
            $seller_info->sname = $state_info->name;
        }

    } else if($company_data->gst_config_type == "multiple_gst") {

        $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => $request->company_id, 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst])->first();

        $seller_info = DB::table('gst_settings_multiple')
            ->join('states','gst_settings_multiple.state','=','states.id')
            ->where(['company_id' => $request->company_id, 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
            ->select(['gst_no','address','pincode','states.name as sname'])
            ->first();

        if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                ->where(['delete' => '0', 'company_id' => $sale_detail->company_id,'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                ->first();
            $state_info = DB::table('states')->where('id',$GstSettings->state)->first();
            $seller_info->sname = $state_info->name;
        }
    }

    if($GstSettings){
        if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
            if($sale_detail->total<100000){
                $GstSettings->ewaybill = 0;
            }
        } else {
            if($sale_detail->total<50000){
                $GstSettings->ewaybill = 0;
            }
        }
    } else {
        $GstSettings = (object)[];
        $GstSettings->ewaybill = 0;
        $GstSettings->einvoice = 0;
    }

    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',$request->company_id)->first();

    Session::put('redirect_url', '');

    
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04', $from.'-05', $from.'-06', $from.'-07', $from.'-08', $from.'-09',
        $from.'-10', $from.'-11', $from.'-12', $to.'-01', $to.'-02', $to.'-03'
    ];

    $saleOrder = \App\Models\SaleOrder::with([
        'billTo:id,account_name,gstin,address,pin_code,state,pan',
        'shippTo:id,account_name,gstin,address,pin_code,state,pan',
        'orderCreatedBy:id,name',
        'items.item:id,name,hsn_code',
        'items.unitMaster:id,s_name',
    ])
    ->where('id', $sale_detail->sale_order_id)
    ->first();

    if ($saleOrder) {
        foreach ($saleOrder->items as $item) {
            $item->itemSize = \DB::table('item_size_stocks')
                ->where('item_id', $item->item_id)
                ->where(function ($query) use ($saleOrder, $sale_id) {
                    $query->where('sale_order_id', $saleOrder->id)
                          ->Where('sale_id', $sale_id);
                })
                ->select('reel_no', 'size', 'gsm', 'bf', 'weight', 'unit')
                ->get();
        }
    }
    
    $comp = Companies::select('user_id')->where('id',$request->company_id)->first();
          $production_module_status = MerchantModuleMapping::where('module_id',4)->where('merchant_id',$comp->user_id)->where('company_id', $request->company_id)->first();
          $production_module_status = $production_module_status ? 1 : 0;
    $pdf = Pdf::loadView('saleInvoicePdf', [
        'items_detail' => $items_detail,
        'sale_sundry' => $sale_sundry,
        'party_detail' => $party_detail,
        'month_arr' => $month_arr,
        'company_data' => $company_data,
        'sale_detail' => $sale_detail,
        'bank_detail' => $bank_detail,
        'gst_detail'=>$gst_detail,
        'einvoice_status'=>$GstSettings->einvoice,
        'ewaybill_status'=>$GstSettings->ewaybill,
        'configuration'=>$configuration,
        'seller_info'=>$seller_info,
        'saleOrder' => $saleOrder,
        'production_module_status'=>$production_module_status,
         'qrBase64' => $qrBase64,
         'einvoice_data' => $einvoice_data,

    ])->setPaper('A4')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

   

    // ✅ RETURN AS BASE64 (BEST FOR MOBILE APPS)
    $pdfContent = $pdf->output();

    return $pdf->download('SaleInvoice-' . $sale_detail->voucher_no . '.pdf');
}

    
    public function partyWiseSummary(Request $request)
{
    $request->validate([
        'from_date' => 'required',
        'to_date'   => 'required',
        'view_by'   => 'required'
    ]);

    if ($request->view_by == 'party') {

            
          
    $data = DB::table('sales')
    ->join('accounts', 'accounts.id', '=', 'sales.party')
    ->where('sales.company_id', $request->company_id)
    ->whereBetween('sales.date', [$request->from_date, $request->to_date])
    ->where('sales.delete', '0')
    ->where('sales.status','1')
    ->groupBy('sales.party')
    ->select(
        'sales.party',
        'accounts.account_name as party_name',
        DB::raw('(SELECT SUM(qty) FROM sale_descriptions WHERE sale_descriptions.sale_id = sales.id) as total_quantity'),
        DB::raw('SUM(sales.total) as total_sale_amount')
    )
    ->get();
    
    
                foreach ($data as $row) {
            
                $details = DB::table('sales')
                    ->select(
                        'sales.id as sales_id',
                        'sales.date',
                        'sales.voucher_no_prefix',
                        'sales.total',
                        'sales.sale_order_id',
                        DB::raw('(SELECT SUM(qty) FROM sale_descriptions WHERE sale_descriptions.sale_id = sales.id) as total_quantity')
                    )
                    ->where('sales.party', $row->party)
                    ->where('sales.company_id', $request->company_id)
                    ->whereBetween('sales.date', [$request->from_date, $request->to_date])
                    ->get();
            
                $row->details = $details; // attach to object
            }



    } elseif ($request->view_by == 'item') {

        // ITEM WISE SUMMARY
        $data = DB::table('item_size_stocks')
            ->join('manage_items','item_size_stocks.item_id','manage_items.id')
            ->leftJoin('sales','item_size_stocks.sale_id','sales.id')
            ->whereNotNull('sale_id')
            ->where('item_size_stocks.status','0')
            ->where('item_size_stocks.company_id', $request->company_id)
            ->whereBetween(
                DB::raw("STR_TO_DATE(sales.date, '%Y-%m-%d')"),
                [$request->from_date, $request->to_date]
            )
            ->groupBy('item_size_stocks.item_id','manage_items.name')
            ->select(
                'item_size_stocks.item_id',
                'manage_items.name',
                DB::raw('COUNT(item_size_stocks.id) as total_reels'),
                DB::raw('SUM(item_size_stocks.weight) as total_quantity')
            )
            ->get();

    } else {
        return response()->json([
            'status' => false,
            'message' => 'Invalid view_by value (allowed: party, item)'
        ], 400);
    }

    return response()->json([
        'code' => 200,
        'view_by' => $request->view_by,
        'from_date' => $request->from_date,
        'to_date' => $request->to_date,
        'data' => $data
    ]);
}



    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
    
    
    public function getSaleSeriesList1(Request $request)
{
      $company_id     = $request->company_id;
    $financial_year = $request->financial_year;

    if (!$company_id || !$financial_year) {
        return response()->json([
            'status' => false,
            'message' => 'Company or Financial Year missing.'
        ], 400);
    }

    $companyData = Companies::find($company_id);
    if (!$companyData || !$companyData->gst_config_type) {
        return response()->json([
            'status' => false,
            'message' => 'GST Configuration not found.'
        ], 400);
    }

    $seriesList = collect();

    // ==============================
    // SINGLE GST
    // ==============================
    if ($companyData->gst_config_type == "single_gst") {

        $gstSetting = DB::table('gst_settings')
            ->where('company_id', $company_id)
            ->where('gst_type', 'single_gst')
            ->first();

        if ($gstSetting) {
            $seriesList = GstBranch::select(
                'branch_series as series',
                'branch_matcenter as material_center',
                'gst_number as gst_no'
            )
            ->where('company_id', $company_id)
            ->where('gst_setting_id', $gstSetting->id)
            ->where('delete', '0')
            ->get();
        }
        
    }

    // ==============================
    // MULTIPLE GST
    // ==============================
    if ($companyData->gst_config_type == "multiple_gst") {

        $seriesList = DB::table('gst_settings_multiple')
            ->select(
                'series',
                'mat_center as material_center',
                'gst_no'
            )
            ->where('company_id', $company_id)
            ->where('gst_type', 'multiple_gst')
            ->get();
    }

    if ($seriesList->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No Series Found.'
        ], 404);
    }

    $finalData = [];

    foreach ($seriesList as $item) {

        // Get last voucher
        $lastVoucher = Sales::where('company_id', $company_id)
            ->where('financial_year', $financial_year)
            ->where('series_no', $item->series)
            ->where('delete', '0')
            ->max(DB::raw("CAST(voucher_no as SIGNED)"));

        $nextVoucher = $lastVoucher
            ? sprintf("%03d", $lastVoucher + 1)
            : "001";

        // Get series configuration
        $seriesConfig = VoucherSeriesConfiguration::where('company_id', $company_id)
            ->where('series', $item->series)
            ->where('configuration_for', 'SALE')
            ->where('status', '1')
            ->first();

        $manual = 0;
        $duplicate = 0;
        $blank = 0;

        if ($seriesConfig) {
            $manual = $seriesConfig->manual_numbering == "YES" ? 1 : 0;
            $duplicate = $seriesConfig->duplicate_voucher ?? 0;
            $blank = $seriesConfig->blank_voucher ?? 0;
        }

        $finalData[] = [
            'series' => $item->series,
            'material_center' => $item->material_center,
            'gst_no' => $item->gst_no,
            'next_voucher_number' => $nextVoucher,
            'manual_numbering' => $manual,
            'duplicate_voucher_allowed' => $duplicate,
            'blank_voucher_allowed' => $blank
        ];
    }

    return response()->json([
        'code' => 200,
        'message' => 'Series list fetched successfully.',
        'data' => $finalData
    ]);
}

public function getSaleSeriesList(Request $request)
{
    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;

    if (!$company_id || !$financial_year) {
        return response()->json([
            'status' => false,
            'message' => 'Company or Financial Year missing.'
        ], 400);
    }

    [$startYY, $endYY] = explode('-', $financial_year);

    $fy_start_date = '20' . $startYY . '-04-01';
    $fy_end_date   = '20' . $endYY   . '-03-31';

    $companyData = Companies::where('id', $company_id)->first();

    if (!$companyData || !$companyData->gst_config_type) {
        return response()->json([
            'status' => false,
            'message' => 'Please Enter GST Configuration!'
        ], 400);
    }

    // ===============================
    // GST SETTINGS FETCH
    // ===============================

    if($companyData->gst_config_type == "single_gst"){

        $GstSettings = DB::table('gst_settings')
            ->where([
                'company_id' => $company_id,
                'gst_type' => "single_gst"
            ])->get();

        $branch = GstBranch::select(
                'id',
                'gst_number as gst_no',
                'branch_matcenter as mat_center',
                'branch_series as series'
            )
            ->where([
                'delete' => '0',
                'company_id' => $company_id,
                'gst_setting_id' => $GstSettings[0]->id
            ])->get();

        if(count($branch) > 0){
            $GstSettings = $GstSettings->merge($branch);
        }

    } else {

        $GstSettings = DB::table('gst_settings_multiple')
            ->select('id','gst_no','mat_center','series')
            ->where([
                'company_id' => $company_id,
                'gst_type' => "multiple_gst"
            ])->get();

        foreach ($GstSettings as $key => $value) {

            $branch = GstBranch::select(
                'id',
                'gst_number as gst_no',
                'branch_matcenter as mat_center',
                'branch_series as series'
            )
            ->where([
                'delete' => '0',
                'company_id' => $company_id,
                'gst_setting_multiple_id' => $value->id
            ])->get();

            if(count($branch) > 0){
                $GstSettings = $GstSettings->merge($branch);
            }
        }
    }

    $finalData = [];

    // ===============================
    // SERIES LOOP (UNCHANGED LOGIC)
    // ===============================

    foreach ($GstSettings as $key => $value) {

        $series_configuration = VoucherSeriesConfiguration::where('company_id',$company_id)
            ->where('series',$value->series)
            ->where('configuration_for','SALE')
            ->where('status','1')
            ->first();

        $voucher_no = Sales::where('company_id',$company_id)
            ->where('financial_year',$financial_year)
            ->where('series_no',$value->series)
            ->where('delete','0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));

        $last_bill_date = Sales::where('company_id',$company_id)
            ->where('financial_year',$financial_year)
            ->where('series_no',$value->series)
            ->where('delete','0')
            ->max("date");

        if(!$voucher_no){
            if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                $invoice_start_from = sprintf("%'03d",$series_configuration->invoice_start);
            } else {
                $invoice_start_from = "001";
            }
        } else {
            $invoice_start_from = sprintf("%'03d", $voucher_no + 1);
        }

        // ===============================
        // PREFIX LOGIC (UNCHANGED)
        // ===============================

        $invoice_prefix = "";
        $duplicate_voucher = "";
        $blank_voucher = "";
        $manual_enter_invoice_no = "0";

        if($series_configuration && $series_configuration->manual_numbering=="YES"){
            $manual_enter_invoice_no = "1";
            $duplicate_voucher = $series_configuration->duplicate_voucher;
            $blank_voucher = $series_configuration->blank_voucher;
        }

        if($series_configuration && $series_configuration->manual_numbering=="NO"){

            if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!=""){
                $invoice_prefix .= $series_configuration->prefix_value;
            }

            if($series_configuration->prefix=="ENABLE" && $series_configuration->separator_1!=""){
                $invoice_prefix .= $series_configuration->separator_1;
            }

            if($series_configuration->year=="PREFIX TO NUMBER"){
                if($series_configuration->year_format=="YY-YY"){
                    $invoice_prefix .= $financial_year;
                } else if($series_configuration->year_format=="YYYY-YY"){
                    $fy_parts = explode('-', $financial_year);
                    $invoice_prefix .= '20'.$fy_parts[0].'-'.$fy_parts[1];
                }
            }

            if($series_configuration->separator_2!=""){
                $invoice_prefix .= $series_configuration->separator_2;
            }

            $invoice_prefix .= $invoice_start_from;

            if($series_configuration->year=="SUFFIX TO NUMBER"){
                if($series_configuration->year_format=="YY-YY"){
                    $invoice_prefix .= $financial_year;
                } else if($series_configuration->year_format=="YYYY-YY"){
                    $fy_parts = explode('-', $financial_year);
                    $invoice_prefix .= '20'.$fy_parts[0].'-'.$fy_parts[1];
                }
            }

            if($series_configuration->suffix=="ENABLE"){
                if($series_configuration->separator_3!=""){
                    $invoice_prefix .= $series_configuration->separator_3;
                }
                $invoice_prefix .= $series_configuration->suffix_value;
            }
        }

        $finalData[] = [
            'series' => $value->series,
            'material_center' => $value->mat_center,
            'gst_no' => $value->gst_no,
            'invoice_start_from' => $invoice_start_from,
            'invoice_prefix' => $invoice_prefix,
            'manual_enter_invoice_no' => $manual_enter_invoice_no,
            'duplicate_voucher' => $duplicate_voucher,
            'blank_voucher' => $blank_voucher,
            'last_bill_date' => $last_bill_date
        ];
    }

    return response()->json([
        'code' => 200,
        'financial_year' => $financial_year,
        'fy_start_date' => $fy_start_date,
        'fy_end_date' => $fy_end_date,
        'series_data' => $finalData
    ]);
}

public function calculateGst(Request $request)
{
    try {

        DB::beginTransaction();

        /* ===============================
           1️⃣ BASIC VALIDATION
        ===============================*/

        if(empty($request->partyId) || empty($request->merchant_gst) || empty($request->items)){
            return response()->json([
                'code' => 400,
                'message' => 'Required fields missing'
            ],400);
        }

        /* ===============================
           2️⃣ STATE CHECK
        ===============================*/

        $party = DB::table('accounts')
                    ->where('id', $request->partyId)
                    ->first();

        if(!$party){
            return response()->json([
                'code' => 404,
                'message' => 'Invalid Party'
            ],404);
        }

        $customerStateCode = DB::table('states')
                                ->where('id',$party->state)
                                ->value('state_code');

        $merchantStateCode = substr($request->merchant_gst, 0, 2);

        $isIntraState = ($customerStateCode == $merchantStateCode);

        /* ===============================
           3️⃣ COLLECT ITEMS
        ===============================*/

        $totalItemAmount = 0;
        $itemsData = [];

        foreach ($request->items as $item) {

            $itemData = DB::table('manage_items')
                            ->where('id', $item['itemId'])
                            ->first();

            if(!$itemData) continue;

            $amount  = floatval($item['amount']);
            $percent = floatval($itemData->gst_rate);

            $totalItemAmount += $amount;

            $itemsData[] = [
                'amount'  => $amount,
                'percent' => $percent
            ];
        }

        /* ===============================
           4️⃣ BILL SUNDRY NET
        ===============================*/

        $billSundryTotal = 0;

        if(!empty($request->sundry_detail)){

            foreach ($request->sundry_detail as $sundry){

                $sundryData = DB::table('bill_sundrys')
                                ->where('id',$sundry['sundry_Id'])
                                ->first();

                if(!$sundryData) continue;

                if($sundryData->nature_of_sundry == 'OTHER'){

                    if($sundryData->bill_sundry_type == 'additive'){
                        $billSundryTotal += floatval($sundry['amount']);
                    }else{
                        $billSundryTotal -= floatval($sundry['amount']);
                    }
                }
            }
        }

        /* ===============================
           5️⃣ PROPORTIONATE TAXABLE
        ===============================*/

        $rateWiseTaxable = [];
        $totalTaxable = 0;

        foreach ($itemsData as $item){

            $originalAmount = $item['amount'];
            $percent = $item['percent'];

            $proportion = 0;

            if($totalItemAmount > 0){
                $proportion = ($originalAmount / $totalItemAmount) * $billSundryTotal;
            }

            $taxable = $originalAmount + $proportion;

            $totalTaxable += $taxable;

            if(!isset($rateWiseTaxable[$percent])){
                $rateWiseTaxable[$percent] = 0;
            }

            $rateWiseTaxable[$percent] += $taxable;
        }

        /* ===============================
           6️⃣ GST CALCULATION
        ===============================*/

        $gstBreakupArray = [];
        $totalCgst = 0;
        $totalSgst = 0;
        $totalIgst = 0;

        foreach ($rateWiseTaxable as $percent => $taxable){

            if($isIntraState){

                $halfPercent = $percent / 2;

                $cgst = ($taxable * $halfPercent) / 100;
                $sgst = ($taxable * $halfPercent) / 100;

                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $cgst_id = DB::table('bill_sundrys')
                                ->where('nature_of_sundry',"CGST")
                                ->where('company_id',$request->company_id)
                                ->where('status','1')
                                ->where('delete','0')
                                ->value('id');
                 $sgst_id = DB::table('bill_sundrys')
                                ->where('nature_of_sundry',"SGST")
                                ->where('company_id',$request->company_id)
                                ->where('status','1')
                                ->where('delete','0')
                                ->value('id');

                $gstBreakupArray[] = [
                    "type" => "CGST",
                    "id" => $cgst_id,
                    "value" => round($cgst,2),
                    "tax_percent" => $halfPercent
                ];

                $gstBreakupArray[] = [
                    "type" => "SGST",
                    "id" => $sgst_id,
                    "value" => round($sgst,2),
                    "tax_percent" => $halfPercent
                ];

            } else {

                $igst = ($taxable * $percent) / 100;
                $totalIgst += $igst;
                 $igst_id = DB::table('bill_sundrys')
                                ->where('nature_of_sundry',"IGST")
                                ->where('company_id',$request->company_id)
                                ->where('status','1')
                                ->where('delete','0')
                                ->value('id');

                $gstBreakupArray[] = [
                    "type" => "IGST",
                    "id" => $igst_id,
                    "value" => round($igst,2),
                    "tax_percent" => $percent
                ];
            }
        }

        /* ===============================
           7️⃣ FINAL TOTAL
        ===============================*/

        $finalTotal = $totalTaxable + $totalCgst + $totalSgst + $totalIgst;

        DB::commit();

        return response()->json([
            "code" => 200,
            "is_intra_state" => $isIntraState,
            "total_taxable_amount" => round($totalTaxable,2),
            "gst_breakup_rate_wise" => $gstBreakupArray,
            "total_cgst" => round($totalCgst,2),
            "total_sgst" => round($totalSgst,2),
            "total_igst" => round($totalIgst,2),
            "final_total" => round($finalTotal,2)
        ]);

    } catch (\Exception $e){

        DB::rollBack();

        return response()->json([
            "code" => 500,
            "message" => $e->getMessage()
        ],500);
    }
}


public function getSeriesAndMaterialCenter(Request $request)
{
    $company = Companies::find($request->company_id);

    if (!$company) {
        return response()->json([
            'status' => false,
            'message' => 'Company not found'
        ], 404);
    }

    $companyId = $company->id;
    $finalCollection = collect();

    if ($company->gst_config_type == "single_gst") {

        $GstSettings = DB::table('gst_settings')
            ->select('id','gst_no','mat_center','series')
            ->where([
                'company_id' => $companyId,
                'gst_type'   => "single_gst"
            ])
            ->get();

        $finalCollection = $GstSettings;

        if ($GstSettings->count()) {

            $branch = GstBranch::select(
                    'id',
                    'gst_number as gst_no',
                    'branch_matcenter as mat_center',
                    'branch_series as series'
                )
                ->where([
                    'delete'         => '0',
                    'company_id'     => $companyId,
                    'gst_setting_id' => $GstSettings->first()->id
                ])
                ->get();

            $finalCollection = $finalCollection->merge($branch);
        }

    } else {

        $GstSettings = DB::table('gst_settings_multiple')
            ->select('id','gst_no','mat_center','series')
            ->where([
                'company_id' => $companyId,
                'gst_type'   => "multiple_gst"
            ])
            ->get();

        $finalCollection = $GstSettings;

        foreach ($GstSettings as $value) {

            $branch = GstBranch::select(
                    'id',
                    'gst_number as gst_no',
                    'branch_matcenter as mat_center',
                    'branch_series as series'
                )
                ->where([
                    'delete'                    => '0',
                    'company_id'                => $companyId,
                    'gst_setting_multiple_id'   => $value->id
                ])
                ->get();

            $finalCollection = $finalCollection->merge($branch);
        }
    }

    // Remove duplicates (if any)
    $finalCollection = $finalCollection->unique(function ($item) {
        return $item->series . '_' . $item->mat_center;
    })->values();

    return response()->json([
        'code' => 200,
        'message' => 'Series & Material Center fetched successfully',
        'data' => [
            'combined_list' => $finalCollection,
            'series_list' => $finalCollection->pluck('series')->unique()->values(),
            'material_center_list' => $finalCollection->pluck('mat_center')->unique()->values()
        ]
    ]);
}

public function getInvoiceDetails(Request $request)
{
    $request->validate([
        'company_id'     => 'required|integer',
        'series'         => 'required|string',
        'financial_year' => 'required|string'
    ]);

    $companyId      = $request->company_id;
    $series         = $request->series;
    $financial_year = $request->financial_year;

    // 🔹 Get Series Configuration
    $series_configuration = VoucherSeriesConfiguration::where('company_id', $companyId)
        ->where('series', $series)
        ->where('configuration_for', 'SALE')
        ->where('status', '1')
        ->first();

    if (!$series_configuration) {
        return response()->json([
            'status' => false,
            'message' => 'Series configuration not found'
        ], 404);
    }

    // 🔹 Get Last Voucher Number
    $lastVoucher = Sales::where('company_id', $companyId)
        ->where('financial_year', $financial_year)
        ->where('series_no', $series)
        ->where('delete', '0')
        ->max(\DB::raw("CAST(voucher_no as SIGNED)"));

    // 🔹 Get Last Bill Date
    $last_bill_date = Sales::where('company_id', $companyId)
        ->where('financial_year', $financial_year)
        ->where('series_no', $series)
        ->where('delete', '0')
        ->max('date');

    // 🔹 Generate Next Voucher Number
    if (!$lastVoucher) {

        if (
            $series_configuration->manual_numbering == "NO" &&
            !empty($series_configuration->invoice_start)
        ) {
            $nextVoucher = sprintf("%03d", $series_configuration->invoice_start);
        } else {
            $nextVoucher = "001";
        }

    } else {
        $nextVoucher = sprintf("%03d", $lastVoucher + 1);
    }

    // 🔹 Manual Numbering
    $manual_enter_invoice_no =
        $series_configuration->manual_numbering == "YES" ? "1" : "0";

    $invoice_prefix = "";

    if ($manual_enter_invoice_no == "0") {

        // 🔹 Format Financial Year Once
        $formattedYear = "";

        if ($series_configuration->year_format == "YY-YY") {
            $formattedYear = $financial_year;
        } elseif ($series_configuration->year_format == "YYYY-YY") {
            $fy_parts = explode('-', $financial_year);
            if (count($fy_parts) == 2) {
                $formattedYear = '20' . $fy_parts[0] . '-' . $fy_parts[1];
            }
        }

        // 🔹 1️⃣ Prefix
        if (
            $series_configuration->prefix == "ENABLE" &&
            !empty($series_configuration->prefix_value)
        ) {
            $invoice_prefix .= $series_configuration->prefix_value;

            if (!empty($series_configuration->separator_1)) {
                $invoice_prefix .= $series_configuration->separator_1;
            }
        }

        // 🔹 2️⃣ Year as PREFIX
        if (
            $series_configuration->year == "PREFIX TO NUMBER" &&
            !empty($formattedYear)
        ) {
            $invoice_prefix .= $formattedYear;

            if (!empty($series_configuration->separator_2)) {
                $invoice_prefix .= $series_configuration->separator_2;
            }
        }

        // 🔹 3️⃣ Voucher Number
        $invoice_prefix .= $nextVoucher;

        // 🔹 4️⃣ Year as SUFFIX
        if (
            $series_configuration->year == "SUFFIX TO NUMBER" &&
            !empty($formattedYear)
        ) {
            if (!empty($series_configuration->separator_2)) {
                $invoice_prefix .= $series_configuration->separator_2;
            }

            $invoice_prefix .= $formattedYear;
        }

        // 🔹 5️⃣ Suffix
        if (
            $series_configuration->suffix == "ENABLE" &&
            !empty($series_configuration->suffix_value)
        ) {
            if (!empty($series_configuration->separator_3)) {
                $invoice_prefix .= $series_configuration->separator_3;
            }

            $invoice_prefix .= $series_configuration->suffix_value;
        }
    }

    return response()->json([
        'code' => 200,
        'message' => 'Invoice details fetched successfully',
        'data' => [
            'voucher_no' => $nextVoucher,
            'invoice_prefix' => $invoice_prefix,
            'manual_enter_invoice_no' => $manual_enter_invoice_no,
            'last_bill_date' => $last_bill_date
        ]
    ]);
}

private function formatFinancialYear($series_configuration, $financial_year)
{
    if (!$financial_year) {
        return "";
    }

    if ($series_configuration->year_format == "YY-YY") {
        return $financial_year;
    }

    if ($series_configuration->year_format == "YYYY-YY") {

        $fy_parts = explode('-', $financial_year);

        if (count($fy_parts) == 2) {
            return '20' . $fy_parts[0] . '-' . $fy_parts[1];
        }
    }

    return "";
}

public function salesDashboard(Request $request)
{
    $request->validate([
        'company_id' => 'required'
    ]);

    $companyId = $request->company_id;
    $today = date('Y-m-d');

    // Base Query
    $baseSalesQuery = DB::table('sales')
        ->where('company_id', (string) $companyId)
        ->where('date', $today)
        ->where('status', '1')
        ->where('delete', '0');

    // Metrics
    $totalSalesCount = (clone $baseSalesQuery)->count();

    $totalSalesAmount = (clone $baseSalesQuery)->sum('total');

    $salesWithGstAmount = (clone $baseSalesQuery)
        ->whereNotNull('taxable_amt')
        ->whereColumn('total', '>', 'taxable_amt')
        ->sum('total');

    $salesWithoutGstAmount = (clone $baseSalesQuery)
        ->whereNotNull('taxable_amt')
        ->sum('taxable_amt');

    // Quantity (separate to avoid duplication issue)
    $totalSalesQty = DB::table('sale_descriptions')
        ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
        ->where('sales.company_id', (string) $companyId)
        ->where('sales.date', $today)
        ->where('sales.status', '1')
        ->where('sales.delete', '0')
        ->where('sale_descriptions.status', '1')
        ->where('sale_descriptions.delete', '0')
        ->sum('sale_descriptions.qty');

    // Config (optional - if you store settings)
    $salesConfigData = [
        'total_sales_count' => true,
        'total_sales_qty' => true,
        'total_sales_amount' => true,
        'sales_with_gst_amount' => true,
        'sales_without_gst_amount' => true,
    ];

    return response()->json([
        'status' => true,
        'code' => 200,
        'message' => 'Sales dashboard data',

        'data' => [
            'date' => $today,

            'show' => [
                'total_sales_count'   => $salesConfigData['total_sales_count'],
                'total_sales_qty'     => $salesConfigData['total_sales_qty'],
                'total_sales_amount'  => $salesConfigData['total_sales_amount'],
                'sales_with_gst'      => $salesConfigData['sales_with_gst_amount'],
                'sales_without_gst'   => $salesConfigData['sales_without_gst_amount'],
            ],

            'values' => [
                'total_sales_count'        => (int) $totalSalesCount,
                'total_sales_qty'          => (float) $totalSalesQty,
                'total_sales_amount'       => (float) $totalSalesAmount,
                'sales_with_gst_amount'    => (float) $salesWithGstAmount,
                'sales_without_gst_amount' => (float) $salesWithoutGstAmount,
            ]
        ]
    ]);
}

}
