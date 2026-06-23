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
            'material_center' => 'required',
            'taxable_amt' => 'required',
            'total' => 'required',
            // 'goods_discription' => 'required',
            // 'qty' => 'required',
            // 'unit' => 'required',
            // 'price' => 'required',
            // 'amount' => 'required',
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




public function BulkSalesVoucherApi(Request $request)
{
    $successEntries = [];
    $errorEntries   = [];

    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;

    if (empty($request->sales)) {

        return response()->json([
            'status'  => false,
            'message' => 'Sales array is required'
        ], 422);
    }

    foreach ($request->sales as $index => $saleData) {

        DB::beginTransaction();

        try {

            /* =====================================================
               BASIC VALIDATION
            ===================================================== */

            if (empty($saleData['party_alias'])) {

                $errorEntries[] = [
                    'voucher_no' => $saleData['voucher_no'] ?? '',
                    'message'    => 'Party alias missing'
                ];

                continue;
            }

            /* =====================================================
               FIND ACTIVE PARTY
            ===================================================== */

            $party = Accounts::where('company_id', $company_id)
                ->where('alias', $saleData['party_alias'])
                ->where('delete', "0")
                ->first();

            if (!$party) {

                $errorEntries[] = [
                    'voucher_no' => $saleData['voucher_no'] ?? '',
                    'party_alias'=> $saleData['party_alias'],
                    'message'    => 'Party not found or inactive'
                ];

                continue;
            }

            $party_id = $party->id;

            /* =====================================================
               DUPLICATE CHECK
            ===================================================== */

            $duplicate = Sales::where('company_id', $company_id)
                ->where('voucher_no', $saleData['voucher_no'])
                ->where('series_no', $saleData['series_no'])
                ->where('financial_year', $financial_year)
                ->where('delete', "0")
                ->first();

            if ($duplicate) {

                $errorEntries[] = [
                    'voucher_no' => $saleData['voucher_no'],
                    'message'    => 'Duplicate voucher number'
                ];

                continue;
            }

            /* =====================================================
               CREATE SALE
            ===================================================== */

            $sale = new Sales();

            $sale->series_no         = $saleData['series_no'];
            $sale->company_id        = $company_id;
            $sale->date              = $saleData['date'];
            $sale->voucher_no        = $saleData['voucher_no'];
            $sale->voucher_no_prefix = $saleData['voucher_no_prefix'] ?? '';
            $sale->party             = $party_id;
            $sale->material_center   = $saleData['material_center'];
            $sale->taxable_amt       = $saleData['taxable_amt'];
            $sale->total             = $saleData['total'];
            $sale->merchant_gst       = $saleData['merchant_gst'];

            $sale->billing_name      = $party->print_name;
            $sale->billing_address   = $party->address;
            $sale->billing_pincode   = $party->pin_code;
            $sale->billing_gst       = $party->gstin;
            $sale->billing_pan       = $party->pan;
            $sale->billing_state     = $party->state;

            $sale->financial_year    = $financial_year;

            $sale->save();

            $SaleLgr = 0;

            /* =====================================================
               ITEMS
            ===================================================== */

            foreach ($saleData['items'] as $item) {

                $manageItem = ManageItems::where('company_id', $company_id)
                    ->where('name', $item['item_name'])
                    ->where('delete', "0")
                    ->first();

                if (!$manageItem) {

                    throw new \Exception(
                        'Item not found or inactive : ' . $item['item_name']
                    );
                }

                $item_id = $manageItem->id;
                $unit_id = $manageItem->u_name;

                $desc = new SaleDescription();

                $desc->sale_id           = $sale->id;
                $desc->goods_discription = $item_id;
                $desc->qty               = $item['qty'];
                $desc->unit              = $unit_id;
                $desc->price             = $item['price'];
                $desc->amount            = $item['amount'];
                $desc->company_id        = $company_id;
                $desc->status            = 1;

                $desc->save();

                $SaleLgr += $item['amount'];

                /* ITEM LEDGER */

                $itemLedger = new ItemLedger();

                $itemLedger->item_id      = $item_id;
                $itemLedger->out_weight   = $item['qty'];
                $itemLedger->txn_date     = $saleData['date'];
                $itemLedger->price        = $item['price'];
                $itemLedger->total_price  = $item['amount'];
                $itemLedger->company_id   = $company_id;
                $itemLedger->source       = 1;
                $itemLedger->source_id    = $sale->id;
                $itemLedger->created_by   = 1;

                $itemLedger->save();

                /* ITEM AVERAGE */

                $avg = new ItemAverageDetail();

                $avg->entry_date  = $saleData['date'];
                $avg->series_no   = $saleData['series_no'];
                $avg->item_id     = $item_id;
                $avg->type        = 'SALE';
                $avg->sale_id     = $sale->id;
                $avg->sale_weight = $item['qty'];
                $avg->company_id  = $company_id;

                $avg->save();
            }

            /* =====================================================
               BILL SUNDRY
            ===================================================== */

            if (!empty($saleData['bill_sundry'])) {

                foreach ($saleData['bill_sundry'] as $sundry) {

                    $billSundry = BillSundrys::whereIn('company_id',[ $company_id,0])
                        ->where('name', $sundry['name'])
                        ->where('status', "1")
                        ->where('delete', "0")
                        ->first();

                    if (!$billSundry) {

                        throw new \Exception(
                            'Bill sundry not found or inactive : ' . $sundry['name']
                        );
                    }

                    $saleSundry = new SaleSundry();

                    $saleSundry->sale_id      = $sale->id;
                    $saleSundry->bill_sundry  = $billSundry->id;
                    $saleSundry->rate         = $sundry['rate'];
                    $saleSundry->amount       = $sundry['amount'];
                    $saleSundry->company_id   = $company_id;
                    $saleSundry->status       = 1;

                    $saleSundry->save();

                    if ($billSundry->adjust_sale_amt == 'No') {

                        $ledger = new AccountLedger();

                        $ledger->account_id = $billSundry->sale_amt_account;

                        if ($billSundry->bill_sundry_type == 'subtractive') {
                            $ledger->debit = $sundry['amount'];
                        } else {
                            $ledger->credit = $sundry['amount'];
                        }

                        $ledger->txn_date        = $saleData['date'];
                        $ledger->series_no       = $saleData['series_no'];
                        $ledger->company_id      = $company_id;
                        $ledger->financial_year  = $financial_year;
                        $ledger->entry_type      = 1;
                        $ledger->entry_type_id   = $sale->id;
                        $ledger->map_account_id  = $party_id;
                        $ledger->created_by      = 1;

                        $ledger->save();

                    } else {

                        if ($billSundry->bill_sundry_type == "additive") {
                            $SaleLgr += $sundry['amount'];
                        } else {
                            $SaleLgr -= $sundry['amount'];
                        }
                    }
                }
            }

            /* =====================================================
               PARTY LEDGER
            ===================================================== */

            $partyLedger = new AccountLedger();

            $partyLedger->account_id      = $party_id;
            $partyLedger->debit           = $saleData['total'];
            $partyLedger->txn_date        = $saleData['date'];
            $partyLedger->series_no       = $saleData['series_no'];
            $partyLedger->company_id      = $company_id;
            $partyLedger->financial_year  = $financial_year;
            $partyLedger->entry_type      = 1;
            $partyLedger->entry_type_id   = $sale->id;
            $partyLedger->map_account_id  = 35;
            $partyLedger->created_by      = 1;

            $partyLedger->save();

            /* SALES LEDGER */

            $salesLedger = new AccountLedger();

            $salesLedger->account_id      = 35;
            $salesLedger->credit          = $SaleLgr;
            $salesLedger->txn_date        = $saleData['date'];
            $salesLedger->series_no       = $saleData['series_no'];
            $salesLedger->company_id      = $company_id;
            $salesLedger->financial_year  = $financial_year;
            $salesLedger->entry_type      = 1;
            $salesLedger->entry_type_id   = $sale->id;
            $salesLedger->map_account_id  = $party_id;
            $salesLedger->created_by      = 1;

            $salesLedger->save();

            DB::commit();

            $successEntries[] = [
                'voucher_no' => $sale->voucher_no,
                'sale_id'    => $sale->id,
                'message'    => 'Sale stored successfully'
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            $errorEntries[] = [
                'voucher_no' => $saleData['voucher_no'] ?? '',
                'message'    => $e->getMessage()
            ];
        }
    }

    return response()->json([

        'status'         => true,

        'success_count'  => count($successEntries),

        'failed_count'   => count($errorEntries),

        'success_entries'=> $successEntries,

        'error_entries'  => $errorEntries

    ]);
}

public function GetSalesVoucherbyId(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'sales_id' => 'required|integer',
        ], [
            'sales_id.required' => 'Sales voucher id is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $sale = Sales::find($request->sales_id);

        if (!$sale) {
            return response()->json([
                'code' => 404,
                'message' => 'Sales voucher not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPANY ID
        |--------------------------------------------------------------------------
        */

        $companyId = $request->input("company_id");

        if (!$companyId) {
            $companyId = $sale->company_id ?? null;
        }

        if (!$companyId) {
            return response()->json([
                'code' => 404,
                'message' => 'Company id not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPANY
        |--------------------------------------------------------------------------
        */

        $comp = Companies::select(
                'id',
                'user_id',
                'company_sale_type'
            )
            ->where('id', $companyId)
            ->first();

        if (!$comp) {
            return response()->json([
                'code' => 404,
                'message' => 'Company not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCTION MODULE STATUS
        |--------------------------------------------------------------------------
        */

        $productionModule = MerchantModuleMapping::where('module_id', 4)
            ->where('merchant_id', $comp->user_id)
            ->where('company_id', $companyId)
            ->first();

        $production_module_status = $productionModule ? 1 : 0;

        /*
        |--------------------------------------------------------------------------
        | SALE DESCRIPTION
        |--------------------------------------------------------------------------
        */

        $saleDescription = SaleDescription::join(
                'units',
                'sale_descriptions.unit',
                '=',
                'units.id'
            )
            ->where('sale_id', $sale->id)
            ->select([
                'sale_descriptions.*',
                'units.s_name'
            ])
            ->get();

        foreach ($saleDescription as $desc) {

            $desc->selected_sizes = DB::table('item_size_stocks')
                ->where('sale_id', $sale->id)
                ->where('item_id', $desc->goods_discription)
                ->select(
                    'id',
                    'size',
                    'weight',
                    'reel_no'
                )
                ->get();

            $desc->lines = DB::table('sale_description_lines')
                ->where(
                    'sale_description_id',
                    $desc->id
                )
                ->orderBy('sort_order')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | SALE SUNDRY
        |--------------------------------------------------------------------------
        */

        $saleSundry = SaleSundry::join(
                'bill_sundrys',
                'sale_sundries.bill_sundry',
                '=',
                'bill_sundrys.id'
            )
            ->select([
                'bill_sundrys.effect_gst_calculation',
                'bill_sundrys.nature_of_sundry',
                'sale_sundries.*'
            ])
            ->where('sale_sundries.sale_id', $sale->id)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | BOX SALE ORDERS
        |--------------------------------------------------------------------------
        */

        $boxSaleOrders = DB::table('box_sale_orders')
            ->where('company_id', $companyId)
            ->where('party_id', $sale->party)
            ->where('delete', '0')
            ->orderBy('id', 'DESC')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SELECTED BOX SALE ORDERS
        |--------------------------------------------------------------------------
        */

        $selectedBoxSaleOrders = DB::table('sale_box_sale_orders')
            ->join(
                'box_sale_orders',
                'box_sale_orders.id',
                '=',
                'sale_box_sale_orders.box_sale_order_id'
            )
            ->where(
                'sale_box_sale_orders.sale_id',
                $sale->id
            )
            ->select(
                'box_sale_orders.id',
                'box_sale_orders.sale_order_no as text'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CONFIG
        |--------------------------------------------------------------------------
        */

        // $config = SaleInvoiceConfiguration::where(
        //         'company_id',
        //         $companyId
        //     )
        //     ->first();

        /*
        |--------------------------------------------------------------------------
        | CREDIT DAYS
        |--------------------------------------------------------------------------
        */

        $creditDays = DB::table('manage_credit_days')
            ->where('status', '1')
            ->where('company_id', $companyId)
            ->orderBy('days')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STATES
        |--------------------------------------------------------------------------
        */

        $stateList = DB::table('states')
            ->orderBy('state_code')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ITEM GROUPS
        |--------------------------------------------------------------------------
        */

        $itemGroups = DB::table('item_groups')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('group_name')
            ->get();
        

    //          $mat_center = array();
    //   $mat_center = GstBranch::select('branch_matcenter')->where('delete', '0')
    //         ->where('company_id', $companyId)->get()->toArray();
    //   if(!empty($GstSettings->mat_center)) {
    //      $mat_center[] = array("branch_matcenter" => $GstSettings->mat_center);
    //   }
      

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT UNITS
        |--------------------------------------------------------------------------
        */

        $accountUnits = DB::table('units')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'code' => 200,
            'message' => 'Sales voucher fetched successfully',

            'sale' => $sale,

            'sale_description' => $saleDescription,

            // 'mat_center' => $mat_center,

            'sale_sundry' => $saleSundry,

            'box_sale_orders' => $boxSaleOrders,

            'selected_box_sale_orders' => $selectedBoxSaleOrders,

            'item_groups' => $itemGroups,

            'account_units' => $accountUnits,

            'credit_days' => $creditDays,

            'state_list' => $stateList,

            // 'config' => $config,

            'company_sale_type' => $comp->company_sale_type,

            'production_module_status' => $production_module_status
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'code' => 500,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

   public function update(Request $request){
    //   Gate::authorize('action-module',61);
      $validated = $request->validate([
        'company_id' => 'required',
        'user_id' => 'required',
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ]); 

        $company_id = $request->input('company_id');
      $userId = $request->input('user_id');
      //Check Item Empty or not
      if($request->input('goods_discription')[0]==""){
         return $this->failedMessage('Plases Select Item','sale/create');
      }
      $default_fy = $request->input('default_fy');

      [$startYY, $endYY] = explode('-', $default_fy);

      $fy_start_date = '20'.$startYY.'-04-01';

      $fy_end_date = '20'.$endYY.'-03-31';

      if(
         $request->input('date') < $fy_start_date
         ||
         $request->input('date') > $fy_end_date
      ){
        return response()->json([
            'code' => 422,
            'message' => 'Selected date is outside current financial year.'
        ]);
        
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));

         $sale = Sales::find($request->input('sale_edit_id'));
         $oldBoxSaleOrderIds = DB::table('sale_box_sale_orders')

            ->where('sale_id', $sale->id)

            ->pluck('box_sale_order_id')

            ->toArray();
      //Check Dulicate Invoice Number
     //dd($request->all());
      // echo "<pre>";
      // print_r($request->all());
      // $sale_enter_data = json_decode($request->sale_enter_data,true);
      //       $grouped = [];
      //       foreach ($sale_enter_data as $item) {
      //          $key = $item['detail_row_id'];
      //          $grouped[$key][] = $item;
      //       }
      //       print_r($grouped);
      //die;

    $account = Accounts::where('id', $request->input('party'))->first();

if (!$account) {
    return response()->json([
        'code' => 404,
        'message' => 'Account not found'
    ], 404);
}

if (empty($account->pin_code)) {
    return response()->json([
        'code' => 200,
        'message' => 'Pincode is null or empty'
    ]);
}
      $oldSnapshot = [
         'sale' => $sale->toArray(),

         'items' => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),

         'sundries' => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),

         'item_ledgers' => ItemLedger::where('source', 1)
            ->where('source_id', $sale->id)
            ->get()->toArray(),

         'account_ledgers' => AccountLedger::where('entry_type', 1)
            ->where('entry_type_id', $sale->id)
            ->get()->toArray(),

         'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)
            ->where('type', 'SALE')
            ->get()->toArray(),
      ];
      $last_date = $sale->date; 
      //If Same Series Edit
      $sale->series_no = $request->input('series_no');
      $sale->date = $request->input('date');
      $voucher_prefix = "";
      if(!empty($request->input('voucher_prefix'))){
         $voucher_prefix_arr = explode("/",$request->input('voucher_prefix'));
         if(count($voucher_prefix_arr)>1){
            $voucher_prefix = $voucher_prefix_arr[0]."/".$voucher_prefix_arr[1]."/";
         }else if(count($voucher_prefix_arr)==1){
            $voucher_prefix = "";
         }
      }
      $billing_address = $account->address;
      $billing_pincode = $account->pin_code;
      $account = Accounts::where('id', $request->input('party'))->first();

if (!$billing_pincode) {
    return response()->json([
        'code' => 201,
        'message' => 'Billing pincoed not found'
    ],);
}

if (empty($billing_pincode)) {
    return response()->json([
        'code' => 201,
        'message' => 'Pincode is null or empty'
    ]);
}
      if($request->input('address') && !empty($request->input('address'))){
         $add = AccountOtherAddress::find($request->input('address'));
         $billing_address = $add->address.",".$add->pincode;
         $billing_pincode = $add->pincode;
      } 
      $sale->party = $request->input('party');
      $sale->material_center = $request->input('material_center');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->self_vehicle = $request->input('self_vehicle');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->address_id = $request->input('address');
      $sale->ewaybill_no = $request->input('ewaybill_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->reverse_charge = $request->input('reverse_charge');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->station = $request->input('station');
      $sale->billing_name = $account->print_name;
      $sale->billing_address = $billing_address;
      $sale->billing_pincode = $billing_pincode;
      $sale->billing_gst = $account->gstin;
      $sale->billing_pan = $account->pan;
      $sale->billing_state = $account->state;
      $sale->shipping_name = $request->input('shipping_name');
      $sale->shipping_state = $request->input('shipping_state');
      $sale->shipping_address = $request->input('shipping_address');
      $sale->shipping_pincode = $request->input('shipping_pincode');
      $sale->shipping_gst = $request->input('shipping_gst');
      $sale->shipping_pan = $request->input('shipping_pan');
      $sale->financial_year = $financial_year;
      $sale->updated_by = $userId;
      $sale->narration = $request->input('narration');
      $sale->po_no = $request->input('po_no');
      $sale->po_date = $request->input('po_date');
      $sale->save();

      DB::table('sale_box_sale_orders')
         ->where('sale_id', $sale->id)
         ->delete();

      if(
         $request->filled('box_sale_order_ids')
         &&
         is_array($request->box_sale_order_ids)
      )
      {
         foreach(
            array_unique($request->box_sale_order_ids)
            as $boxSaleOrderId
         )
         {
            DB::table('sale_box_sale_orders')
               ->insert([

                  'sale_id' =>
                     $sale->id,

                  'box_sale_order_id' =>
                     $boxSaleOrderId,

                  'company_id' =>
                     $company_id,

                  'created_at' =>
                     now(),

                  'updated_at' =>
                     now()

               ]);
         }
      }

      if(
         $request->filled('goods_discription')
         &&
         is_array($request->goods_discription)
         &&
         $request->filled('box_sale_order_ids')
         &&
         is_array($request->box_sale_order_ids)
      )
      {

         $actualGoodsDescriptions = [];

         foreach(
            $request->goods_discription
            as $soItemId
         )
         {

            $soItem = DB::table('box_sale_order_items')

               ->where('id',$soItemId)

               ->select('item_id')

               ->first();

            $actualGoodsDescriptions[] =
               $soItem
               ? $soItem->item_id
               : null;
         }

         $request->merge([

            'box_sale_order_item_id' =>
               $request->goods_discription,

            'goods_discription' =>
               $actualGoodsDescriptions

         ]);

      }

      if ($sale->id) {
         $goods_discriptions = $request->input('goods_discription');
         $item_descriptions = $request->input('item_description');
         $description_lines = $request->input('description_lines');
         $qtys   = $request->input('qty');
         $total_weights = $request->input('total_weight');
         $units  = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         DB::table('sale_description_lines')
            ->where('sale_id', $sale->id)
            ->delete();
         $desc_item_arr = SaleDescription::where('sale_id',$sale->id)->pluck('goods_discription')->toArray();
         $old_size_ids = ItemSizeStock::where('sale_id', $sale->id)
                              ->pluck('id')
                              ->toArray();
         $oldDescriptions = SaleDescription::where('sale_id', $sale->id)
            ->get();

         foreach ($oldDescriptions as $oldRow) {

            $old_reel_count = ItemSizeStock::where('sale_description_id', $oldRow->id)
               ->count();

            CommonHelper::updateDailyReelStock(
               $company_id,
               $oldRow->goods_discription,

               $last_date,

               0,
               0,

               -$old_reel_count,
               -(
                  $oldRow->dual_unit == 1
                  ? $oldRow->taarobaar_qty
                  : $oldRow->qty
               )
            );
         }
         SaleDescription::where('sale_id', $sale->id)->delete();
         DB::table('taarobar_sale_description_piece_weights')
            ->where('sale_id', $sale->id)
            ->delete();
         ItemLedger::where('source_id', $sale->id)->where('source', 1)->delete();
         ItemAverageDetail::where('sale_id', $sale->id)
                           ->where('type', 'SALE')
                           ->delete();
         $new_size_ids = [];$desc_id_arr = [];$item_quantity_total = 0;
         $pricewithgst = $request->input('pricewithgst');
         $profit = $request->input('profit');
         foreach ($goods_discriptions as $key => $good) {
            $boxSaleOrderItemId =
               $request->box_sale_order_item_id[$key] ?? null;
            if($boxSaleOrderItemId)
            {
               $boxItem =
                  DB::table('box_sale_order_items')
                     ->where('id', $boxSaleOrderItemId)
                     ->first();
               if($boxItem)
               {
                  $oldQty =
                     DB::table('sale_descriptions')
                        ->where('sale_id', $sale->id)
                        ->where(
                           'box_sale_order_item_id',
                           $boxSaleOrderItemId
                        )
                        ->sum('qty');
                  $consumedQty =
                     DB::table('sale_descriptions')
                        ->where(
                           'box_sale_order_item_id',
                           $boxSaleOrderItemId
                        )
                        ->where('sale_id', '!=', $sale->id)
                        ->where('delete', '0')
                        ->sum('qty');
                  $allowedQty =
                     ($boxItem->qty - $consumedQty);
                  if($qtys[$key] > $allowedQty)
                  {
                    return response()->json([
                        'code' => 422,
                        'message' => 'Qty cannot exceed pending '
                    ]);
                  }
               }
            }
            if ($good=="" || $qtys[$key]=="" || $units[$key]=="" || 
               $prices[$key]=="" || $amounts[$key]=="") {
               continue;
            }
            $item_quantity_total = $item_quantity_total + $qtys[$key];
            $desc = new SaleDescription;
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $good;
            if(!empty($request->box_sale_order_ids))
            {
               $desc->box_sale_order_item_id =
                  $request->box_sale_order_item_id[$key] ?? null;
            }
            else
            {
               $desc->box_sale_order_item_id = null;
            }
            $desc->item_description = $item_descriptions[$key] ?? '';
            $itemData = ManageItems::find($good);

            if($itemData && $itemData->dual_unit == 1){

               // Store Total Wt in qty
               $desc->qty = rtrim(rtrim(number_format((float)($total_weights[$key] ?? 0), 2, '.', ''), '0'), '.');

               // Store Qty in taarobaar_qty
               $desc->taarobaar_qty = rtrim(rtrim(number_format((float)$qtys[$key], 2, '.', ''), '0'), '.');

            }else{

               // Normal items
               $desc->qty = rtrim(rtrim(number_format((float)$qtys[$key], 2, '.', ''), '0'), '.');

               $desc->taarobaar_qty =
                  rtrim(rtrim(number_format((float)($total_weights[$key] ?? 0), 2, '.', ''), '0'), '.');
            }

            $desc->dual_unit =
               ($itemData && $itemData->dual_unit == 1)
               ? 1
               : 0;

            $desc->unit = $units[$key];
            $desc->pricewithgst = $pricewithgst[$key] ?? 0;
            $desc->profit = $profit[$key] ?? 0;
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->company_id = $company_id;
            $desc->status = '1';
            $desc->save();
            if($boxSaleOrderItemId)
            {
               $dispatchedQty = DB::table('sale_descriptions')
                  ->where(
                        'box_sale_order_item_id',
                        $boxSaleOrderItemId
                  )
                  ->where(
                        'company_id',
                        $company_id
                  )
                  ->where(
                        'delete',
                        '0'
                  )
                  ->sum('qty');
               $orderItem = DB::table('box_sale_order_items')
                  ->where(
                        'id',
                        $boxSaleOrderItemId
                  )
                  ->first();
               if($orderItem)
               {
                  if(
                        (float)$dispatchedQty
                        >=
                        (float)$orderItem->qty
                  )
                  {
                        DB::table('box_sale_order_items')
                           ->where(
                              'id',
                              $boxSaleOrderItemId
                           )
                           ->update([
                              'status' => 2
                           ]);
                  }
                  else
                  {
                        DB::table('box_sale_order_items')
                           ->where(
                              'id',
                              $boxSaleOrderItemId
                           )
                           ->update([
                              'status' => 1
                           ]);
                  }
               }
            }
            array_push($desc_id_arr,$desc->id);
            $row_no = $key + 1;
            $piece_weights =
               $request->input('piece_weight_'.$row_no);

            if(is_array($piece_weights)){

               foreach($piece_weights as $piece_no => $weight){

                  if($weight == '' || $weight == 0){
                     continue;
                  }

                  DB::table('taarobar_sale_description_piece_weights')
                     ->insert([

                        'sale_id' => $sale->id,

                        'sale_description_id' => $desc->id,

                        'item_id' => $good,

                        'piece_no' => $piece_no + 1,

                        'weight' => $weight,

                        'company_id' => $company_id,

                        'created_at' => now(),

                        'updated_at' => now()
                     ]);
               }
            }
            // Description lines
             if (isset($description_lines[$key]) && is_array($description_lines[$key])) {
               foreach ($description_lines[$key] as $lineIndex => $lineText) {
                  if (!empty($lineText)) {
                     DB::table('sale_description_lines')->insert([
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id,
                        'line_text' => $lineText,
                        'sort_order' => $lineIndex + 1,
                        'company_id' => $company_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                     ]);
                  }
               }
            }
            // Item ledger
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $good;
            if($itemData && $itemData->dual_unit == 1){
               $item_ledger->out_weight =
                  $total_weights[$key] ?? 0;
            }else{
               $item_ledger->out_weight =
                  $qtys[$key];
            }
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $prices[$key];
            $item_ledger->total_price = $amounts[$key];
            $item_ledger->company_id = $company_id;
            $item_ledger->source = 1;
            $item_ledger->source_id = $sale->id;
            $item_ledger->created_by = $userId;
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            $sizes = [];
            if(isset($request->input('item_size_info')[$key])){
               $item_size_info_raw = $request->input('item_size_info')[$key] ?? "[]";
               $sizes = json_decode($item_size_info_raw, true);
               if (is_array($sizes)) {
                  foreach ($sizes as $row) {
                     if (!isset($row['id'])) continue;

                     $sid = (int)$row['id'];
                     $new_size_ids[] = $sid;

                     ItemSizeStock::where('id', $sid)->update([
                        'status' => 0,
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id
                     ]);
                  }
               }
            } 
            $reel_count = count($sizes);
            CommonHelper::updateDailyReelStock(
               $company_id,
               $good,

               $request->input('date'),

               0,
               0,

               $reel_count,
               (
                  $itemData && $itemData->dual_unit == 1
                  ? ($total_weights[$key] ?? 0)
                  : $qtys[$key]
               )
            );           
         }

         $allBoxSaleOrderIds = array_unique(

            array_merge(

               $oldBoxSaleOrderIds,

               $request->box_sale_order_ids ?? []

            )

         );

         foreach($allBoxSaleOrderIds as $boxSaleOrderId)
         {
            $this->updateBoxSaleOrderStatus(
               $boxSaleOrderId
            );
         }

         $removed_size_ids = array_diff($old_size_ids, $new_size_ids);
         if (!empty($removed_size_ids)) {
            ItemSizeStock::whereIn('id', $removed_size_ids)->update([
                  'status' => 1,
                  'sale_id' => null,
                  'sale_description_id' => null
            ]);
         }
         $bill_sundrys = $request->input('bill_sundry');
         $tax_amts = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');
         SaleSundry::where('sale_id',$sale->id)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',1)->delete();
     
         foreach($bill_sundrys as $key => $bill){
            if($bill_sundry_amounts[$key]=="" || $bill==""){
               continue;
            }
            $sundry = new SaleSundry;
            $sundry->sale_id = $sale->id;
            $sundry->bill_sundry = $bill;
            $sundry->rate = $tax_amts[$key];
            $sundry->company_id = $company_id;
            $sundry->amount = $bill_sundry_amounts[$key];
            $sundry->status = '1';
            $sundry->save();
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();
          
            if($billsundry->adjust_sale_amt=='No'){
               $ledger = new AccountLedger();
               $ledger->account_id = $billsundry->sale_amt_account;
               if($billsundry->bill_sundry_type=='subtractive'){
               $ledger->debit = $bill_sundry_amounts[$key];
            }else{
               $ledger->credit = $bill_sundry_amounts[$key];
            }   
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = $company_id;
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 1;
               $ledger->entry_type_id = $sale->id;
               $ledger->map_account_id = $request->input('party');
               $ledger->created_by = $userId;
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
            }
         }
            
         //Average Calculation
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $sale_item_array = [];
         foreach($goods_discriptions as $key => $good){
            if($good=="" || $qtys[$key]==""){
               continue;
            }
            $itemData = ManageItems::find($good);
            $avg_qty = $qtys[$key];
            if($itemData && $itemData->dual_unit == 1){
               $avg_qty =
                  $total_weights[$key] ?? 0;
            }
            if(array_key_exists($good,$sale_item_array)){
               $sale_item_array[$good] += $avg_qty;
            }else{
               $sale_item_array[$good] = $avg_qty;
            }    
         }
           
         foreach ($sale_item_array as $key => $value) {
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $key;
            $average_detail->type = 'SALE';
            $average_detail->sale_id = $sale->id;
            $average_detail->sale_weight = $value;

            $average_detail->company_id = $company_id;
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$key,$request->input('series_no'));               
         }
    
         foreach ($desc_item_arr as $key => $value) {
            if(!array_key_exists($value, $sale_item_array)){
               CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
            }
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = $company_id;
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = 35;//Sales Account
         $ledger->created_by = $userId;
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 35;//Sales Account
         $ledger->credit = $request->input('taxable_amt');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = $company_id;
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = $userId;
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
     
         //Update Sale Order Id Code ...................
         if($request->sale_order_id!=""){
            SaleOrderItemWeight::where('sale_order_id',$request->sale_order_id)->delete();
            ItemSizeStock::where('sale_order_id',$request->sale_order_id)
                           ->where('sale_id',$sale->id)
                           ->update(['status'=>1,'sale_order_id'=>null,'sale_id'=>null,'sale_description_id'=>null]);
            Sales::where('id',$sale->id)->update(['sale_order_id'=>$request->sale_order_id]);
            $saleOrder = SaleOrder::with('items.gsms.details')
                                 ->where('id', $request->sale_order_id)
                                 ->first();
            if ($saleOrder) {
               // Update sale order
               $saleOrder->update(['status' => 1,'updated_at'=>Carbon::now(),"updated_by"=>$userId]);
               // Update items
               foreach ($saleOrder->items as $item) {
                  $item->update(['status' => 1]);
                  // Update GSMs
                  foreach ($item->gsms as $gsm) {
                        $gsm->update(['status' => 1]);
                        // Update GSM details
                        foreach ($gsm->details as $detail) {
                           $detail->update(['status' => 1]);
                        }
                  }
               }
            }
            $sale_enter_data = json_decode($request->sale_enter_data,true);
            $grouped = [];
            foreach ($sale_enter_data as $item) {
               $key = $item['detail_row_id'];
               $grouped[$key][] = $item;
            }
            
            $new_order_arr = [];$group_index = 0;$group_index_arr = [];$max_groups = count($desc_id_arr);
            foreach($grouped as $k=>$val){
               $enter_qty = 0;
               foreach($val as $k1=>$val1){
                  // Assign group index only once per unique index
                     if (!isset($group_index_arr[$val1['index']])) {
                        $group_index_arr[$val1['index']] = $group_index;
                        $group_index++;
                        //$group_index = ($group_index + 1) % $max_groups;
                     }

                     $current_group_index = $group_index_arr[$val1['index']];
                  if(!empty($val1['enter_qty'])){
                     if($val1['unit_type']=="REEL"){
                        $enter_qty = $enter_qty + $val1['enter_qty'];
                     }else if($val1['unit_type']=="KG"){
                        $enter_qty = $enter_qty + array_sum($val1['reel_weight_arr']);
                     }                        
                     foreach($val1['reel_weight_arr'] as $k3=>$val2){
                        $sale_order_item_weight = new SaleOrderItemWeight;
                        $sale_order_item_weight->sale_order_id = $request->sale_order_id;
                        $sale_order_item_weight->sale_order_item_row_id = $val1['detail_row_id'];
                        $sale_order_item_weight->weight = $val2;
                        $sale_order_item_weight->weight_id = $val1['reel_weight_id'][$k3];
                        $sale_order_item_weight->company_id = $company_id;
                        $sale_order_item_weight->created_at = Carbon::now();
                        $sale_order_item_weight->save();
                        // print_r($group_index);
                        if(isset($val1['reel_weight_id'][$k3])){
                        ItemSizeStock::where('id',$val1['reel_weight_id'][$k3])->update(['status'=>0,'sale_order_id'=>$request->sale_order_id,'sale_id'=>$sale->id,"sale_description_id"=>$desc_id_arr[$current_group_index]]);
                        }
                     }
                  }
                  
               }
               
               $sale_order_gsm_size = SaleOrderItemGsmSize::find($k);
               $sale_order_gsm_size->sale_order_qty = $enter_qty;
               $sale_order_gsm_size->update();
               $remaining_qty = $sale_order_gsm_size->quantity - $enter_qty;
               if($remaining_qty>0){
                  array_push($new_order_arr,array("id"=>$k,"sale_order_item_id"=>$sale_order_gsm_size->sale_order_item_id,"sale_order_item_gsm_id"=>$sale_order_gsm_size->sale_order_item_gsm_id,"quantity"=>$remaining_qty));
               }
            }
            if($request->new_order==1){
               if(count($new_order_arr)>0){
                  $sale_order = SaleOrder::find($request->sale_order_id);
                  
                  if (preg_match('/-(\d+)$/', $sale_order->sale_order_no, $matches)) {
                     // If found, increment the number
                     $nextNumber = $matches[1] + 1;
                     // Replace the old suffix with the new one
                     $new_sale_order_no = preg_replace('/-\d+$/', '-' . $nextNumber, $sale_order->sale_order_no);
                  } else {
                     // If no suffix found, start with -1
                     $new_sale_order_no = $sale_order->sale_order_no . '-1';
                  }
                  
                  $new_sale_order = new SaleOrder;
                  $new_sale_order->sale_order_no = $new_sale_order_no;
                  $new_sale_order->purchase_order_no = $sale_order->purchase_order_no;
                  $new_sale_order->purchase_order_date = $sale_order->purchase_order_date;
                  $new_sale_order->bill_to = $sale_order->bill_to;
                  $new_sale_order->shipp_to = $sale_order->shipp_to;
                  $new_sale_order->freight = $sale_order->freight;
                  $new_sale_order->parent_order_no = $sale_order->sale_order_no;
                  $new_sale_order->company_id = $company_id;
                  $new_sale_order->created_by = $userId;
                  $new_sale_order->created_at = Carbon::now();
                  if($new_sale_order->save()){
                     $item_check_arr = [];$gsm_check_arr = [];
                     foreach($new_order_arr as $nk=>$nval){
                        if(isset($item_check_arr[$nval['sale_order_item_id']]) && $item_check_arr[$nval['sale_order_item_id']]!=""){
                           $new_sale_order_item_id = $item_check_arr[$nval['sale_order_item_id']];
                        }else{
                           $sale_order_item = SaleOrderItem::find($nval['sale_order_item_id']);
                           $new_sale_order_item = new SaleOrderItem;
                           $new_sale_order_item->sale_order_id = $new_sale_order->id;
                           $new_sale_order_item->item_id = $sale_order_item->item_id;
                           $new_sale_order_item->price = $sale_order_item->price;
                           $new_sale_order_item->bill_price = $sale_order_item->bill_price;
                           $new_sale_order_item->unit = $sale_order_item->unit;
                           $new_sale_order_item->sub_unit = $sale_order_item->sub_unit;
                           $new_sale_order_item->company_id = $company_id;
                           $new_sale_order_item->created_at = Carbon::now();
                           $new_sale_order_item->save();
                           $item_check_arr[$nval['sale_order_item_id']] = $new_sale_order_item->id;
                           $new_sale_order_item_id = $new_sale_order_item->id;
                        }                  
                        if($new_sale_order_item_id){
                           if(isset($gsm_check_arr[$nval['sale_order_item_gsm_id']]) && $gsm_check_arr[$nval['sale_order_item_gsm_id']]!=""){
                              $new_sale_order_item_gsm_id = $gsm_check_arr[$nval['sale_order_item_gsm_id']];
                           }else{
                              $sale_order_item_gsm = SaleOrderItemGSM::find($nval['sale_order_item_gsm_id']);
                              $new_sale_order_item_gsm = new SaleOrderItemGSM;
                              $new_sale_order_item_gsm->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm->sale_order_item_id = $new_sale_order_item_id;
                              $new_sale_order_item_gsm->gsm = $sale_order_item_gsm->gsm;
                              $new_sale_order_item_gsm->company_id = $company_id;
                              $new_sale_order_item_gsm->created_at = Carbon::now();
                              $new_sale_order_item_gsm->save();
                              $gsm_check_arr[$nval['sale_order_item_gsm_id']] = $new_sale_order_item_gsm->id;
                              $new_sale_order_item_gsm_id = $new_sale_order_item_gsm->id;
                           }
                           if($new_sale_order_item_gsm_id){                        
                              $sale_order_item_gsm_size = SaleOrderItemGsmSize::find($nval['id']);
                              $new_sale_order_item_gsm_size = new SaleOrderItemGsmSize;
                              $new_sale_order_item_gsm_size->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm_size->sale_order_item_id = $new_sale_order_item->id;
                              $new_sale_order_item_gsm_size->sale_order_item_gsm_id = $new_sale_order_item_gsm_id;
                              $new_sale_order_item_gsm_size->size = $sale_order_item_gsm_size->size;
                              $new_sale_order_item_gsm_size->quantity = $nval['quantity'];
                              $new_sale_order_item_gsm_size->company_id = $company_id;
                              $new_sale_order_item_gsm_size->created_at = Carbon::now();
                              $new_sale_order_item_gsm_size->save();
                           }
                        }
                     }
                  }
               }
            }
            //Store Vehicle Details
            SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>'',
                           'freight_price'=>'',
                           'freight_transporter_id'=>'',
                           'other_freight_amount'=>'',
                           'freight_vehicle_id'=>'',
                        ]);
            SaleVehicleTxn::where('sale_order_id',$request->sale_order_id)->delete();
            if($sale->transporter_journal_id){
               JournalDetails::where('journal_id',$sale->transporter_journal_id)->delete();
               Journal::where('id',$sale->transporter_journal_id)->delete();
               AccountLedger::where('entry_type',7)->where('entry_type_id',$sale->transporter_journal_id)->delete();

            }
            Sales::where('id',$sale->id)->update(['transporter_journal_id'=>null]);

            
            if($request->input('vehicle_info_type')=="vehicle" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('vehicle_freight'),
                           'freight_vehicle_id'=>$request->input('vehicle_info'),
                           'other_freight_amount'=>''
                        ]);
               $vehicle_info = new SaleVehicleTxn;
               $vehicle_info->sale_id = $sale->id;
               $vehicle_info->sale_order_id = $request->sale_order_id;
               $vehicle_info->vehicle_id = $request->input('vehicle_info');
               $vehicle_info->vehicle_freight_price = $request->input('vehicle_freight');
               $vehicle_info->vehicle_freight_amount = $item_quantity_total * $request->input('vehicle_freight');
               $vehicle_info->company_id = $company_id;
               $vehicle_info->created_at = Carbon::now();
               $vehicle_info->created_by = $userId;
               $vehicle_info->save();
            }
            if($request->input('vehicle_info_type')=="to_pay" && $request->sale_order_id!="" ){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('to_pay_freight'),
                           'other_freight_amount'=>$request->input('to_pay_other_charges')
                        ]);
            }
            if($request->input('vehicle_info_type')=="party_vehicle" && $request->sale_order_id!="" ){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>"",
                           'other_freight_amount'=>""
                        ]);
            }
            //Transporter Journal Entry
            if($request->input('vehicle_info_type')=="transporter" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){
               $transporter_total_amount = ($item_quantity_total * $request->input('transporter_freight'))+$request->input('transporter_other_charges');
               $transporter_total_amount = round($transporter_total_amount);
               $location_name = $account->location;
               if(!empty($request->input('shipping_name'))){
                  $shipp_account = Accounts::select('location')->find($request->input('shipping_name'));
                  $location_name = $shipp_account->location;
               }
               
               //Journal Entry For Transporter Voucher No
               $series_configuration = VoucherSeriesConfiguration::where('company_id', $company_id)
                                                                  ->where('series', $request->input('series_no'))
                                                                  ->where('configuration_for', 'JOURNAL') 
                                                                  ->where('status', '1')
                                                                  ->first();
               $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
               $lastNumber = DB::table('journals')
                                 ->where('company_id', $company_id)
                                 ->where('financial_year', $financial_year)
                                 ->where('series_no', $request->input('series_no'))
                                 ->where('delete', '0')
                                 ->max(DB::raw("cast(voucher_no as SIGNED)"));
               if (!$lastNumber) {
                  if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
                     $journal_voucher_no = (int)$series_configuration->invoice_start;
                  } else {
                     $journal_voucher_no = 1;
                  }
               } else {
                  $journal_voucher_no = ((int)$lastNumber) + 1;
               }
               //Voucher Series With Prefix/Suffix
               $journal_invoice_prefix = "";
               if ($series_configuration && $series_configuration->manual_numbering == "NO") {
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                     $journal_invoice_prefix .= $series_configuration->prefix_value;
                  }
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_1;
                  }
                  if ($series_configuration->year == "PREFIX TO NUMBER") {
                     if ($series_configuration->year_format == "YY-YY") {
                        $journal_invoice_prefix .= $financial_year;
                     } else {
                        $fy = explode('-', $financial_year);
                        $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                     }
                     if ($series_configuration->separator_2 != "") {
                        $journal_invoice_prefix .= $series_configuration->separator_2;
                     }
                  }
                  $journal_invoice_prefix .= $journal_voucher_no;
                  if ($series_configuration->year == "SUFFIX TO NUMBER") {
                     if ($series_configuration->separator_2 != "") {
                        $journal_invoice_prefix .= $series_configuration->separator_2;
                     }
                     if ($series_configuration->year_format == "YY-YY") {
                        $journal_invoice_prefix .= $financial_year;
                     } else {
                        $fy = explode('-', $financial_year);
                        $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                     }
                  }
                  if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_3;
                  }
                  if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
                     $journal_invoice_prefix .= $series_configuration->suffix_value;
                  }
               }
               $journal_voucher_no = sprintf("%0" . $number_digit . "d", $journal_voucher_no);
               if($journal_invoice_prefix==""){
                  $journal_invoice_prefix = $journal_voucher_no;
               }
               $journal = new Journal;
               $journal->date = $request->input('date');
               $journal->voucher_no = $journal_voucher_no;
               $journal->voucher_no_prefix = $journal_invoice_prefix;
               $journal->series_no = $request->input('series_no');
               $journal->long_narration = "Bill No : ".$sale->voucher_no_prefix.", Vehicle No. : ".$request->input('vehicle_no').", Location : ".$location_name.", GR/PR No. : ".$request->input('gr_pr_no');
               $journal->company_id = $company_id;
               $journal->financial_year = $financial_year;
               $journal->claim_gst_status = 'NO';
               $journal->merchant_gst = $request->input('merchant_gst');
               if($journal->save()){
                  SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('transporter_freight'),
                           'freight_transporter_id'=>$request->input('vehicle_info'),
                           'other_freight_amount'=>$request->input('transporter_other_charges')
                        ]);
                  Sales::where('id',$sale->id)->update(['transporter_journal_id'=>$journal->id]);
                  //Add Transpoeter Account Credit
                  $expense = DB::table('sale-order-settings')
                                    ->where('setting_type','EXPENSE_ACCOUNT')
                                    ->where('setting_for','SALE ORDER')
                                    ->where('company_id',$company_id)
                                    ->first();
                  $joundetail = new JournalDetails;
                  $joundetail->journal_id = $journal->id;
                  $joundetail->company_id = $company_id;
                  $joundetail->type = "Credit";
                  $joundetail->account_name = $request->input('vehicle_info');
                  $joundetail->debit = '0';
                  $joundetail->credit = $transporter_total_amount;            
                  $joundetail->narration = "";
                  $joundetail->status = '1';
                  $joundetail->save();
                  //Account Ledger
                  $ledger = new AccountLedger();
                  $ledger->account_id = $request->input('vehicle_info');               
                  $ledger->credit = $transporter_total_amount;
                  $ledger->map_account_id = $expense->expense_account_id;
                  $ledger->series_no = $request->input('series_no');
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = $company_id;
                  $ledger->financial_year = $financial_year;
                  $ledger->entry_type = 7;
                  $ledger->entry_type_id = $journal->id;
                  $ledger->entry_narration = "";               
                  $ledger->created_by = $userId;
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //Add Freight Account Debit
                  
                  $joundetail = new JournalDetails;
                  $joundetail->journal_id = $journal->id;
                  $joundetail->company_id = $company_id;
                  $joundetail->type = "Debit";
                  $joundetail->account_name = $expense->expense_account_id;
                  $joundetail->debit = $transporter_total_amount;
                  $joundetail->credit = '0';
                  $joundetail->narration = "";
                  $joundetail->status = '1';
                  $joundetail->save();
                  //Account Ledger
                  $ledger = new AccountLedger();
                  $ledger->account_id = $expense->expense_account_id;
                  $ledger->debit = $transporter_total_amount;
                  $ledger->map_account_id = $request->input('vehicle_info');
                  $ledger->series_no = $request->input('series_no');
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = $company_id;
                  $ledger->financial_year = $financial_year;
                  $ledger->entry_type = 7;
                  $ledger->entry_type_id = $journal->id;
                  $ledger->entry_narration = "";               
                  $ledger->created_by = $userId;
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                     
               }
            }
            //
         }
         $newSnapshot = [
            'sale' => Sales::find($sale->id)->toArray(),

            'items' => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),

            'sundries' => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),

            'item_ledgers' => ItemLedger::where('source', 1)
               ->where('source_id', $sale->id)
               ->get()->toArray(),

            'account_ledgers' => AccountLedger::where('entry_type', 1)
               ->where('entry_type_id', $sale->id)
               ->get()->toArray(),

            'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)
               ->where('type', 'SALE')
               ->get()->toArray(),
         ];
         ActivityLog::create([
            'module_type' => 'sale',
            'module_id'   => $sale->id,
            'action'      => 'edit',
            'old_data'    => $oldSnapshot,
            'new_data'    => $newSnapshot,
            'action_by'   => $userId,
            'company_id'  => $company_id,
            'action_at'   => now(),
         ]);
        
         return response()->json([
            'code'  => 200,
            'message' => 'Sale voucher updated successfully!',
            'data'    => [
                'sale_id'    => $sale->id,
                'voucher_no' => $sale->voucher_no
            ]
        ]);
         
      }else{
       return response()->json([
            'code'  => 201,
            'message' => 'Something went wrong processing your request'
        ]);
      }
      
   }
// public function update(Request $request)
// {

//     // Gate::authorize('action-module', 61);

//     // 2. Request Validation
//       $validator = Validator::make($request->all(), [
//             'user_id' => 'required',
//             'company_id' => 'required',
//             'default_fy'   => 'required',
//             'sale_edit_id'    => 'required', 
            
//             'series_no' => 'required',
//             'date' => 'required',
//             'voucher_no' => 'required',
//             'party' => 'required',
//             'material_center' => 'required',
//             'total' => 'required',
//             'goods_discription' => 'required',
//             'qty' => 'required',
//             'units' => 'required',
//             'price' => 'required',
//             'amount' => 'required',
//             'bill_sundry' => 'required',
//             'bill_sundry_amount' => 'required',
//             'self_vehicle' => 'required',
//             'transport_name' => 'required',
//         ], [
//             'sale_edit_id.required' => 'Sale edit id is required.',
//             'default_fy.required' => 'Default financial year is required.',
//             'company_id.required' => 'Company id is required.',
//             'series_no.required' => 'Series number is required.',
//             'date.required' => 'Date is required.',
//             'voucher_no.required' => 'Voucher number is required.',
//             'party.required' => 'Party id is required.',
//             'material_center.required' => 'Material center is required.',
//             'tax_rate.required' => 'Tax rate is required.',
//             'tax.required' => 'Tax is required.',
//             'total.required' => 'Total is required.',
//             'goods_discription.required' => 'Goods discription is required.',
//             'qty.required' => 'Goods qty is required.',
//             'units.required' => 'Goods unit is required.',
//             'price.required' => 'Goods price is required.',
//             'amount.required' => 'Goods amounts is required.',
//             'bill_sundry.required' => 'Bill sundry is required.',
//             // 'tax_amt.required' => 'Bill sundry tax amount is required.',
//             'bill_sundry_amount.required' => 'Bill sundry amount is required.',
//             'self_vehicle.required' => 'Self vehicle is required.',
//             'transport_name.required' => 'Transport name is required.'
//         ]);

//         if ($validator->fails()) {
//             return response()->json($validator->errors(), 422);
//         }

//     // $validated = $request->validate([
//     //     'user_id'           => 'required',
//     //     'company_id'        => 'required',
//     //     'default_fy'        => 'required',
//     //     'sale_edit_id'      => 'required', 
//     //     'series_no'         => 'required',
//     //     'date'              => 'required|date',
//     //     'voucher_no'        => 'required',
//     //     'party'             => 'required',
//     //     'material_center'   => 'required',
//     //     'total'             => 'required',
//     //     'goods_discription' => 'required|array|min:1',
//     // ]);

//     // Check Item Empty or not
//     if ($request->input('goods_discription')[0] == "") {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Plases Select Item' // Preserved original developer spelling
//         ], 400);
//     }

//     // Stateless Context: Resolve active company, user, and financial year details from request/user profile
//     $userId     = $request->input('user_id');
//     $companyId  = $request->input('company_id');
//     $default_fy = $request->input('default_fy');
//     if (!$default_fy) {
//         return response()->json(['status' => false, 'message' => 'Financial year context is required.'], 400);
//     }

//     [$startYY, $endYY] = explode('-', $default_fy);
//     $fy_start_date = '20' . $startYY . '-04-01';
//     $fy_end_date   = '20' . $endYY . '-03-31';

//     if ($request->input('date') < $fy_start_date || $request->input('date') > $fy_end_date) {
//         return response()->json([
//             'message' => 'The given data was invalid.',
//             'errors'  => ['date' => ['Selected date is outside current financial year.']]
//         ], 422);
//     }

//     $financial_year = CommonHelper::getFinancialYear($request->input('date'));
//     $sale           = Sales::find($request->input('sale_edit_id'));

//     if (!$sale) {
//         return response()->json(['status' => false, 'message' => 'Sale record not found.'], 444);
//     }

//     $oldBoxSaleOrderIds = DB::table('sale_box_sale_orders')
//         ->where('sale_id', $sale->id)
//         ->pluck('box_sale_order_id')
//         ->toArray();

//     $account = Accounts::where('id', $request->input('party'))->first();
//     if (!$account) {
//         return response()->json(['status' => false, 'message' => 'Party account not found.'], 444);
//     }
        
//     // 3. Generate Historical Data Snapshot
//     $oldSnapshot = [
//         'sale'                 => $sale->toArray(),
//         'items'                => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),
//         'sundries'             => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),
//         'item_ledgers'         => ItemLedger::where('source', 1)->where('source_id', $sale->id)->get()->toArray(),
//         'account_ledgers'      => AccountLedger::where('entry_type', 1)->where('entry_type_id', $sale->id)->get()->toArray(),
//         'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)->where('type', 'SALE')->get()->toArray(),
//     ];

//     $last_date = $sale->date; 
    
//     // Update Master Sale Voucher Entry
//     $sale->series_no = $request->input('series_no');
//     $sale->date      = $request->input('date');

//     $voucher_prefix = "";
//     if (!empty($request->input('voucher_prefix'))) {
//         $voucher_prefix_arr = explode("/", $request->input('voucher_prefix'));
//         if (count($voucher_prefix_arr) > 1) {
//             $voucher_prefix = $voucher_prefix_arr[0] . "/" . $voucher_prefix_arr[1] . "/";
//         } else if (count($voucher_prefix_arr) == 1) {
//             $voucher_prefix = "";
//         }
//     }

//  $billing_address = $account->address; // Default fallback 
//     $billing_pincode = $account->pin_code; // Default fallback 

//     if ($request->input('address') && !empty($request->input('address'))) { // 
//         $add = AccountOtherAddress::find($request->input('address')); // 
        
//         // Ensure the record actually exists in the database
//         if ($add) {
//             $billing_address = $add->address . "," . $add->pincode; // 
//             $billing_pincode = $add->pincode; // 
//         }
//     }

//     $sale->party            = $request->input('party');
//     $sale->material_center  = $request->input('material_center');
//     $sale->taxable_amt      = $request->input('taxable_amt');
//     $sale->total            = $request->input('total');
//     $sale->self_vehicle     = $request->input('self_vehicle');
//     $sale->vehicle_no       = $request->input('vehicle_no');
//     $sale->address_id       = $request->input('address');
//     $sale->ewaybill_no      = $request->input('ewaybill_no');
//     $sale->transport_name   = $request->input('transport_name');
//     $sale->reverse_charge   = $request->input('reverse_charge');
//     $sale->gr_pr_no         = $request->input('gr_pr_no');
//     $sale->station          = $request->input('station');
//     $sale->billing_name     = $account->print_name;
//     $sale->billing_address  = $billing_address;
//     $sale->billing_pincode  = $billing_pincode;
//     $sale->billing_gst      = $account->gstin;
//     $sale->billing_pan      = $account->pan;
//     $sale->billing_state    = $account->state;
//     $sale->shipping_name    = $request->input('shipping_name');
//     $sale->shipping_state   = $request->input('shipping_state');
//     $sale->shipping_address = $request->input('shipping_address');
//     $sale->shipping_pincode = $request->input('shipping_pincode');
//     $sale->shipping_gst     = $request->input('shipping_gst');
//     $sale->shipping_pan     = $request->input('shipping_pan');
//     $sale->financial_year   = $financial_year;
//     $sale->updated_by       = $userId;
//     $sale->narration        = $request->input('narration');
//     $sale->po_no            = $request->input('po_no');
//     $sale->po_date          = $request->input('po_date');
//     $sale->save();

//     // 4. Update Box Sale Orders mappings
//     DB::table('sale_box_sale_orders')->where('sale_id', $sale->id)->delete();
//     if ($request->filled('box_sale_order_ids') && is_array($request->box_sale_order_ids)) {
//         foreach (array_unique($request->box_sale_order_ids) as $boxSaleOrderId) {
//             DB::table('sale_box_sale_orders')->insert([
//                 'sale_id'           => $sale->id,
//                 'box_sale_order_id' => $boxSaleOrderId,
//                 'company_id'        => $companyId,
//                 'created_at'        => now(),
//                 'updated_at'        => now()
//             ]);
//         }
//     }

//     if ($request->filled('goods_discription') && is_array($request->goods_discription) &&
//         $request->filled('box_sale_order_ids') && is_array($request->box_sale_order_ids)) {

//         $actualGoodsDescriptions = [];
//         foreach ($request->goods_discription as $soItemId) {
//             $soItem = DB::table('box_sale_order_items')->where('id', $soItemId)->select('item_id')->first();
//             $actualGoodsDescriptions[] = $soItem ? $soItem->item_id : null;
//         }

//         $request->merge([
//             'box_sale_order_item_id' => $request->goods_discription,
//             'goods_discription'      => $actualGoodsDescriptions
//         ]);
//     }

//     if ($sale->id) {
//         $goods_discriptions = $request->input('goods_discription');
//         $item_descriptions  = $request->input('item_description');
//         $description_lines  = $request->input('description_lines');
//         $qtys               = $request->input('qty');
//         $total_weights      = $request->input('total_weight');
//         $units              = $request->input('units');
//         $prices             = $request->input('price');
//         $amounts            = $request->input('amount');

//         DB::table('sale_description_lines')->where('sale_id', $sale->id)->delete();
//         $desc_item_arr = SaleDescription::where('sale_id', $sale->id)->pluck('goods_discription')->toArray();
//         $old_size_ids  = ItemSizeStock::where('sale_id', $sale->id)->pluck('id')->toArray();
        
//         // Revert inventory stock logs back to original levels prior to edit
//         $oldDescriptions = SaleDescription::where('sale_id', $sale->id)->get();
//         foreach ($oldDescriptions as $oldRow) {
//             $old_reel_count = ItemSizeStock::where('sale_description_id', $oldRow->id)->count();
//             CommonHelper::updateDailyReelStock(
//                 $companyId,
//                 $oldRow->goods_discription,
//                 $last_date,
//                 0,
//                 0,
//                 -$old_reel_count,
//                 -($oldRow->dual_unit == 1 ? $oldRow->taarobaar_qty : $oldRow->qty)
//             );
//         }

//         SaleDescription::where('sale_id', $sale->id)->delete();
//         DB::table('taarobar_sale_description_piece_weights')->where('sale_id', $sale->id)->delete();
//         ItemLedger::where('source_id', $sale->id)->where('source', 1)->delete();
//         ItemAverageDetail::where('sale_id', $sale->id)->where('type', 'SALE')->delete();

//         $new_size_ids        = [];
//         $desc_id_arr         = [];
//         $item_quantity_total = 0;
//         $pricewithgst        = $request->input('pricewithgst');
//         $profit              = $request->input('profit');

//         // Loop over updated descriptions
//         foreach ($goods_discriptions as $key => $good) {
//             $boxSaleOrderItemId = $request->box_sale_order_item_id[$key] ?? null;
//             if ($boxSaleOrderItemId) {
//                 $boxItem = DB::table('box_sale_order_items')->where('id', $boxSaleOrderItemId)->first();
//                 if ($boxItem) {
//                     $oldQty = DB::table('sale_descriptions')
//                         ->where('sale_id', $sale->id)
//                         ->where('box_sale_order_item_id', $boxSaleOrderItemId)
//                         ->sum('qty');
//                     $consumedQty = DB::table('sale_descriptions')
//                         ->where('box_sale_order_item_id', $boxSaleOrderItemId)
//                         ->where('sale_id', '!=', $sale->id)
//                         ->where('delete', '0')
//                         ->sum('qty');
//                     $allowedQty = ($boxItem->qty - $consumedQty);
                    
//                     if ($qtys[$key] > $allowedQty) {
//                         return response()->json([
//                             'message' => 'The given data was invalid.',
//                             'errors'  => ['qty' => ['Qty cannot exceed pending qty.']]
//                         ], 422);
//                     }
//                 }
//             }

//             if ($good == "" || $qtys[$key] == "" || $units[$key] == "" || $prices[$key] == "" || $amounts[$key] == "") {
//                 continue;
//             }

//             $item_quantity_total += $qtys[$key];
//             $desc                = new SaleDescription;
//             $desc->sale_id       = $sale->id;
//             $desc->goods_discription = $good;
//             $desc->box_sale_order_item_id = !empty($request->box_sale_order_ids) ? ($request->box_sale_order_item_id[$key] ?? null) : null;
//             $desc->item_description = $item_descriptions[$key] ?? '';
            
//             $itemData = ManageItems::find($good);
//             if ($itemData && $itemData->dual_unit == 1) {
//                 $desc->qty           = rtrim(rtrim(number_format((float)($total_weights[$key] ?? 0), 2, '.', ''), '0'), '.');
//                 $desc->taarobaar_qty = rtrim(rtrim(number_format((float)$qtys[$key], 2, '.', ''), '0'), '.');
//             } else {
//                 $desc->qty           = rtrim(rtrim(number_format((float)$qtys[$key], 2, '.', ''), '0'), '.');
//                 $desc->taarobaar_qty = rtrim(rtrim(number_format((float)($total_weights[$key] ?? 0), 2, '.', ''), '0'), '.');
//             }

//             $desc->dual_unit    = ($itemData && $itemData->dual_unit == 1) ? 1 : 0;
//             $desc->unit         = $units[$key];
//             $desc->pricewithgst = $pricewithgst[$key] ?? 0;
//             $desc->profit       = $profit[$key] ?? 0;
//             $desc->price        = $prices[$key];
//             $desc->amount       = $amounts[$key];
//             $desc->company_id   = $companyId;
//             $desc->status       = '1';
//             $desc->save();

//             if ($boxSaleOrderItemId) {
//                 $dispatchedQty = DB::table('sale_descriptions')
//                     ->where('box_sale_order_item_id', $boxSaleOrderItemId)
//                     ->where('company_id', $companyId)
//                     ->where('delete', '0')
//                     ->sum('qty');
//                 $orderItem = DB::table('box_sale_order_items')->where('id', $boxSaleOrderItemId)->first();
//                 if ($orderItem) {
//                     $statusValue = ((float)$dispatchedQty >= (float)$orderItem->qty) ? 2 : 1;
//                     DB::table('box_sale_order_items')->where('id', $boxSaleOrderItemId)->update(['status' => $statusValue]);
//                 }
//             }

//             array_push($desc_id_arr, $desc->id);
//             $row_no        = $key + 1;
//             $piece_weights = $request->input('piece_weight_' . $row_no);

//             if (is_array($piece_weights)) {
//                 foreach ($piece_weights as $piece_no => $weight) {
//                     if ($weight == '' || $weight == 0) {
//                         continue;
//                     }
//                     DB::table('taarobar_sale_description_piece_weights')->insert([
//                         'sale_id'             => $sale->id,
//                         'sale_description_id' => $desc->id,
//                         'item_id'             => $good,
//                         'piece_no'            => $piece_no + 1,
//                         'weight'              => $weight,
//                         'company_id'          => $companyId,
//                         'created_at'          => now(),
//                         'updated_at'          => now()
//                     ]);
//                 }
//             }

//             if (isset($description_lines[$key]) && is_array($description_lines[$key])) {
//                 foreach ($description_lines[$key] as $lineIndex => $lineText) {
//                     if (!empty($lineText)) {
//                         DB::table('sale_description_lines')->insert([
//                             'sale_id'             => $sale->id,
//                             'sale_description_id' => $desc->id,
//                             'line_text'           => $lineText,
//                             'sort_order'          => $lineIndex + 1,
//                             'company_id'          => $companyId,
//                             'created_at'          => now(),
//                             'updated_at'          => now(),
//                         ]);
//                     }
//                 }
//             }

//             // Create Item Ledger entries
//             $item_ledger = new ItemLedger();
//             $item_ledger->item_id = $good;
//             $item_ledger->out_weight = ($itemData && $itemData->dual_unit == 1) ? ($total_weights[$key] ?? 0) : $qtys[$key];
//             $item_ledger->series_no   = $request->input('series_no');
//             $item_ledger->txn_date    = $request->input('date');
//             $item_ledger->price       = $prices[$key];
//             $item_ledger->total_price = $amounts[$key];
//             $item_ledger->company_id  = $companyId;
//             $item_ledger->source      = 1;
//             $item_ledger->source_id   = $sale->id;
//             $item_ledger->created_by  = $userId;
//             $item_ledger->created_at  = date('d-m-Y H:i:s');
//             $item_ledger->save();

//             $sizes = [];
//             if (isset($request->input('item_size_info')[$key])) {
//                 $item_size_info_raw = $request->input('item_size_info')[$key] ?? "[]";
//                 $sizes = json_decode($item_size_info_raw, true);
//                 if (is_array($sizes)) {
//                     foreach ($sizes as $row) {
//                         if (!isset($row['id'])) continue;
//                         $sid = (int)$row['id'];
//                         $new_size_ids[] = $sid;
//                         ItemSizeStock::where('id', $sid)->update([
//                             'status'              => 0,
//                             'sale_id'             => $sale->id,
//                             'sale_description_id' => $desc->id
//                         ]);
//                     }
//                 }
//             } 

//             $reel_count = count($sizes);
//             CommonHelper::updateDailyReelStock(
//                 $companyId,
//                 $good,
//                 $request->input('date'),
//                 0,
//                 0,
//                 $reel_count,
//                 ($itemData && $itemData->dual_unit == 1 ? ($total_weights[$key] ?? 0) : $qtys[$key])
//             );
//         }

//         $allBoxSaleOrderIds = array_unique(array_merge($oldBoxSaleOrderIds, $request->box_sale_order_ids ?? []));
//         foreach ($allBoxSaleOrderIds as $boxSaleOrderId) {
//             $this->updateBoxSaleOrderStatus($boxSaleOrderId);
//         }

//         $removed_size_ids = array_diff($old_size_ids, $new_size_ids);
//         if (!empty($removed_size_ids)) {
//             ItemSizeStock::whereIn('id', $removed_size_ids)->update([
//                 'status'              => 1,
//                 'sale_id'             => null,
//                 'sale_description_id' => null
//             ]);
//         }

//         // 5. Account Ledgers and Sundry Calculations
//         $bill_sundrys         = $request->input('bill_sundry');
//         $tax_amts             = $request->input('tax_rate');
//         $bill_sundry_amounts  = $request->input('bill_sundry_amount');
        
//         SaleSundry::where('sale_id', $sale->id)->delete();
//         AccountLedger::where('entry_type_id', $sale->id)->where('entry_type', 1)->delete();

//         foreach ($bill_sundrys as $key => $bill) {
//             if ($bill_sundry_amounts[$key] == "" || $bill == "") {
//                 continue;
//             }
//             $sundry          = new SaleSundry;
//             $sundry->sale_id = $sale->id;
//             $sundry->bill_sundry = $bill;
//             $sundry->rate    = $tax_amts[$key];
//             $sundry->company_id = $companyId;
//             $sundry->amount  = $bill_sundry_amounts[$key];
//             $sundry->status  = '1';
//             $sundry->save();

//             $billsundry = BillSundrys::where('id', $bill)->first();
//             if ($billsundry->adjust_sale_amt == 'No') {
//                 $ledger             = new AccountLedger();
//                 $ledger->account_id = $billsundry->sale_amt_account;
//                 $ledger->debit      = ($billsundry->bill_sundry_type == 'subtractive') ? $bill_sundry_amounts[$key] : 0;
//                 $ledger->credit     = ($billsundry->bill_sundry_type != 'subtractive') ? $bill_sundry_amounts[$key] : 0;
//                 $ledger->txn_date   = $request->input('date');
//                 $ledger->series_no  = $request->input('series_no');
//                 $ledger->company_id = $companyId;
//                 $ledger->financial_year = $financial_year;
//                 $ledger->entry_type     = 1;
//                 $ledger->entry_type_id  = $sale->id;
//                 $ledger->map_account_id = $request->input('party');
//                 $ledger->created_by     = $userId;
//                 $ledger->created_at     = date('d-m-Y H:i:s');
//                 $ledger->save();
//             }
//         }

//         // 6. Running Inventory Average Corrections
//         $goods_discriptions = $request->input('goods_discription');
//         $qtys               = $request->input('qty');
//         $sale_item_array    = [];

//         foreach ($goods_discriptions as $key => $good) {
//             if ($good == "" || $qtys[$key] == "") {
//                 continue;
//             }
//             $itemData = ManageItems::find($good);
//             $avg_qty  = ($itemData && $itemData->dual_unit == 1) ? ($total_weights[$key] ?? 0) : $qtys[$key];

//             if (array_key_exists($good, $sale_item_array)) {
//                 $sale_item_array[$good] += $avg_qty;
//             } else {
//                 $sale_item_array[$good] = $avg_qty;
//             }    
//         }

//         foreach ($sale_item_array as $key => $value) {
//             $average_detail = new ItemAverageDetail;
//             $average_detail->entry_date = $request->date;
//             $average_detail->series_no  = $request->input('series_no');
//             $average_detail->item_id    = $key;
//             $average_detail->type       = 'SALE';
//             $average_detail->sale_id    = $sale->id;
//             $average_detail->sale_weight = $value;
//             $average_detail->company_id = $companyId;
//             $average_detail->created_at = Carbon::now();
//             $average_detail->save();

//             $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
//             CommonHelper::RewriteItemAverageByItem($lower_date, $key, $request->input('series_no'));
//         }
         
//         foreach ($desc_item_arr as $key => $value) {
//             if (!array_key_exists($value, $sale_item_array)) {
//                 CommonHelper::RewriteItemAverageByItem($last_date, $value, $request->input('series_no'));
//             }
//         }

//         // Customer Account Balancing Transaction (Debit Entry)
//         $ledger                 = new AccountLedger();
//         $ledger->account_id     = $request->input('party');
//         $ledger->debit          = $request->input('total');
//         $ledger->txn_date       = $request->input('date');
//         $ledger->series_no      = $request->input('series_no');
//         $ledger->company_id     = $companyId;
//         $ledger->financial_year = $financial_year;
//         $ledger->entry_type     = 1;
//         $ledger->entry_type_id  = $sale->id;
//         $ledger->map_account_id = 35; // Sales Account Identifier
//         $ledger->created_by     = $userId;
//         $ledger->created_at     = date('d-m-Y H:i:s');
//         $ledger->save();

//         // Main Sales Revenue Account Entry (Credit Entry)
//         $ledger                 = new AccountLedger();
//         $ledger->account_id     = 35; 
//         $ledger->credit         = $request->input('taxable_amt');
//         $ledger->txn_date       = $request->input('date');
//         $ledger->series_no      = $request->input('series_no');
//         $ledger->company_id     = $companyId;
//         $ledger->financial_year = $financial_year;
//         $ledger->entry_type     = 1;
//         $ledger->entry_type_id  = $sale->id;
//         $ledger->map_account_id = $request->input('party');
//         $ledger->created_by     = $userId;
//         $ledger->created_at     = date('d-m-Y H:i:s');
//         $ledger->save();

//         // 7. Advanced Sale Order Updates & Suffix Incrementation Engine
//         if ($request->sale_order_id != "") {
//             SaleOrderItemWeight::where('sale_order_id', $request->sale_order_id)->delete();
//             ItemSizeStock::where('sale_order_id', $request->sale_order_id)
//                 ->where('sale_id', $sale->id)
//                 ->update(['status' => 1, 'sale_order_id' => null, 'sale_id' => null, 'sale_description_id' => null]);

//             Sales::where('id', $sale->id)->update(['sale_order_id' => $request->sale_order_id]);
//             $saleOrder = SaleOrder::with('items.gsms.details')->where('id', $request->sale_order_id)->first();
            
//             if ($saleOrder) {
//                 $saleOrder->update(['status' => 1, 'updated_at' => Carbon::now(), "updated_by" => $userId]);
//                 foreach ($saleOrder->items as $item) {
//                     $item->update(['status' => 1]);
//                     foreach ($item->gsms as $gsm) {
//                         $gsm->update(['status' => 1]);
//                         foreach ($gsm->details as $detail) {
//                             $detail->update(['status' => 1]);
//                         }
//                     }
//                 }
//             }

//             $sale_enter_data = json_decode($request->sale_enter_data, true) ?? [];
//             $grouped = [];
//             foreach ($sale_enter_data as $item) {
//                 $key = $item['detail_row_id'];
//                 $grouped[$key][] = $item;
//             }
            
//             $new_order_arr = []; $group_index = 0; $group_index_arr = []; $max_groups = count($desc_id_arr);
//             foreach ($grouped as $k => $val) {
//                 $enter_qty = 0;
//                 foreach ($val as $k1 => $val1) {
//                     if (!isset($group_index_arr[$val1['index']])) {
//                         $group_index_arr[$val1['index']] = $group_index;
//                         $group_index++;
//                     }

//                     $current_group_index = $group_index_arr[$val1['index']];
//                     if (!empty($val1['enter_qty'])) {
//                         if ($val1['unit_type'] == "REEL") {
//                             $enter_qty += $val1['enter_qty'];
//                         } else if ($val1['unit_type'] == "KG") {
//                             $enter_qty += array_sum($val1['reel_weight_arr']);
//                         }                        
//                         foreach ($val1['reel_weight_arr'] as $k3 => $val2) {
//                             $sale_order_item_weight = new SaleOrderItemWeight;
//                             $sale_order_item_weight->sale_order_id = $request->sale_order_id;
//                             $sale_order_item_weight->sale_order_item_row_id = $val1['detail_row_id'];
//                             $sale_order_item_weight->weight     = $val2;
//                             $sale_order_item_weight->weight_id   = $val1['reel_weight_id'][$k3];
//                             $sale_order_item_weight->company_id  = $companyId;
//                             $sale_order_item_weight->created_at  = Carbon::now();
//                             $sale_order_item_weight->save();

//                             if (isset($val1['reel_weight_id'][$k3])) {
//                                 ItemSizeStock::where('id', $val1['reel_weight_id'][$k3])->update([
//                                     'status' => 0, 
//                                     'sale_order_id' => $request->sale_order_id, 
//                                     'sale_id' => $sale->id, 
//                                     "sale_description_id" => $desc_id_arr[$current_group_index]
//                                 ]);
//                             }
//                         }
//                     }
//                 }
               
//                 $sale_order_gsm_size = SaleOrderItemGsmSize::find($k);
//                 if ($sale_order_gsm_size) {
//                     $sale_order_gsm_size->sale_order_qty = $enter_qty;
//                     $sale_order_gsm_size->update();
//                     $remaining_qty = $sale_order_gsm_size->quantity - $enter_qty;
//                     if ($remaining_qty > 0) {
//                         array_push($new_order_arr, [
//                             "id" => $k, 
//                             "sale_order_item_id" => $sale_order_gsm_size->sale_order_item_id, 
//                             "sale_order_item_gsm_id" => $sale_order_gsm_size->sale_order_item_gsm_id, 
//                             "quantity" => $remaining_qty
//                         ]);
//                     }
//                 }
//             }

//             if ($request->new_order == 1 && count($new_order_arr) > 0) {
//                 $sale_order = SaleOrder::find($request->sale_order_id);
//                 if ($sale_order) {
//                     if (preg_match('/-(\d+)$/', $sale_order->sale_order_no, $matches)) {
//                         $nextNumber = $matches[1] + 1;
//                         $new_sale_order_no = preg_replace('/-\d+$/', '-' . $nextNumber, $sale_order->sale_order_no);
//                     } else {
//                         $new_sale_order_no = $sale_order->sale_order_no . '-1';
//                     }
                    
//                     $new_sale_order = new SaleOrder;
//                     $new_sale_order->sale_order_no        = $new_sale_order_no;
//                     $new_sale_order->purchase_order_no    = $sale_order->purchase_order_no;
//                     $new_sale_order->purchase_order_date  = $sale_order->purchase_order_date;
//                     $new_sale_order->bill_to              = $sale_order->bill_to;
//                     $new_sale_order->shipp_to             = $sale_order->shipp_to;
//                     $new_sale_order->freight              = $sale_order->freight;
//                     $new_sale_order->parent_order_no      = $sale_order->sale_order_no;
//                     $new_sale_order->company_id           = $companyId;
//                     $new_sale_order->created_by           = auth()->id() ?? $userId;
//                     $new_sale_order->created_at           = Carbon::now();

//                     if ($new_sale_order->save()) {
//                         $item_check_arr = []; $gsm_check_arr = [];
//                         foreach ($new_order_arr as $nk => $nval) {
//                             if (isset($item_check_arr[$nval['sale_order_item_id']]) && $item_check_arr[$nval['sale_order_item_id']] != "") {
//                                 $new_sale_order_item_id = $item_check_arr[$nval['sale_order_item_id']];
//                             } else {
//                                 $sale_order_item = SaleOrderItem::find($nval['sale_order_item_id']);
//                                 $new_sale_order_item = new SaleOrderItem;
//                                 $new_sale_order_item->sale_order_id = $new_sale_order->id;
//                                 $new_sale_order_item->item_id       = $sale_order_item->item_id;
//                                 $new_sale_order_item->price         = $sale_order_item->price;
//                                 $new_sale_order_item->bill_price    = $sale_order_item->bill_price;
//                                 $new_sale_order_item->unit          = $sale_order_item->unit;
//                                 $new_sale_order_item->sub_unit       = $sale_order_item->sub_unit;
//                                 $new_sale_order_item->company_id    = $companyId;
//                                 $new_sale_order_item->created_at    = Carbon::now();
//                                 $new_sale_order_item->save();
//                                 $item_check_arr[$nval['sale_order_item_id']] = $new_sale_order_item->id;
//                                 $new_sale_order_item_id = $new_sale_order_item->id;
//                             }                  
//                             if ($new_sale_order_item_id) {
//                                 if (isset($gsm_check_arr[$nval['sale_order_item_gsm_id']]) && $gsm_check_arr[$nval['sale_order_item_gsm_id']] != "") {
//                                     $new_sale_order_item_gsm_id = $gsm_check_arr[$nval['sale_order_item_gsm_id']];
//                                 } else {
//                                     $sale_order_item_gsm = SaleOrderItemGSM::find($nval['sale_order_item_gsm_id']);
//                                     $new_sale_order_item_gsm = new SaleOrderItemGSM;
//                                     $new_sale_order_item_gsm->sale_orders_id      = $new_sale_order->id;
//                                     $new_sale_order_item_gsm->sale_order_item_id  = $new_sale_order_item_id;
//                                     $new_sale_order_item_gsm->gsm                 = $sale_order_item_gsm->gsm;
//                                     $new_sale_order_item_gsm->company_id          = $companyId;
//                                     $new_sale_order_item_gsm->created_at          = Carbon::now();
//                                     $new_sale_order_item_gsm->save();
//                                     $gsm_check_arr[$nval['sale_order_item_gsm_id']] = $new_sale_order_item_gsm->id;
//                                     $new_sale_order_item_gsm_id = $new_sale_order_item_gsm->id;
//                                 }
//                                 if ($new_sale_order_item_gsm_id) {                        
//                                     $sale_order_item_gsm_size = SaleOrderItemGsmSize::find($nval['id']);
//                                     $new_sale_order_item_gsm_size = new SaleOrderItemGsmSize;
//                                     $new_sale_order_item_gsm_size->sale_orders_id      = $new_sale_order->id;
//                                     $new_sale_order_item_gsm_size->sale_order_item_id  = $new_sale_order_item->id;
//                                     $new_sale_order_item_gsm_size->sale_order_item_gsm_id = $new_sale_order_item_gsm_id;
//                                     $new_sale_order_item_gsm_size->size                 = $sale_order_item_gsm_size->size;
//                                     $new_sale_order_item_gsm_size->quantity             = $nval['quantity'];
//                                     $new_sale_order_item_gsm_size->company_id           = $companyId;
//                                     $new_sale_order_item_gsm_size->created_at           = Carbon::now();
//                                     $new_sale_order_item_gsm_size->save();
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }

//             // 8. Vehicle & Logistics Management Logic
//             SaleOrder::where('id', $request->sale_order_id)->update([
//                 'freight_type'           => '',
//                 'freight_price'          => '',
//                 'freight_transporter_id' => '',
//                 'other_freight_amount'   => '',
//                 'freight_vehicle_id'     => '',
//             ]);
//             SaleVehicleTxn::where('sale_order_id', $request->sale_order_id)->delete();
            
//             if ($sale->transporter_journal_id) {
//                 JournalDetails::where('journal_id', $sale->transporter_journal_id)->delete();
//                 Journal::where('id', $sale->transporter_journal_id)->delete();
//                 AccountLedger::where('entry_type', 7)->where('entry_type_id', $sale->transporter_journal_id)->delete();
//             }
//             Sales::where('id', $sale->id)->update(['transporter_journal_id' => null]);

//             if ($request->input('vehicle_info_type') == "vehicle" && $request->sale_order_id != "" && $request->input('vehicle_info') != "") {
//                 SaleOrder::where('id', $request->sale_order_id)->update([
//                     'freight_type'       => $request->input('vehicle_info_type'),
//                     'freight_price'      => $request->input('vehicle_freight'),
//                     'freight_vehicle_id' => $request->input('vehicle_info'),
//                     'other_freight_amount' => ''
//                 ]);
//                 $vehicle_info = new SaleVehicleTxn;
//                 $vehicle_info->sale_id               = $sale->id;
//                 $vehicle_info->sale_order_id         = $request->sale_order_id;
//                 $vehicle_info->vehicle_id            = $request->input('vehicle_info');
//                 $vehicle_info->vehicle_freight_price = $request->input('vehicle_freight');
//                 $vehicle_info->vehicle_freight_amount = $item_quantity_total * $request->input('vehicle_freight');
//                 $vehicle_info->company_id            = $companyId;
//                 $vehicle_info->created_at            = Carbon::now();
//                 $vehicle_info->created_by            = $userId;
//                 $vehicle_info->save();
//             }
//             if ($request->input('vehicle_info_type') == "to_pay" && $request->sale_order_id != "") {
//                 SaleOrder::where('id', $request->sale_order_id)->update([
//                     'freight_type'         => $request->input('vehicle_info_type'),
//                     'freight_price'        => $request->input('to_pay_freight'),
//                     'other_freight_amount' => $request->input('to_pay_other_charges')
//                 ]);
//             }
//             if ($request->input('vehicle_info_type') == "party_vehicle" && $request->sale_order_id != "") {
//                 SaleOrder::where('id', $request->sale_order_id)->update([
//                     'freight_type'         => $request->input('vehicle_info_type'),
//                     'freight_price'        => "",
//                     'other_freight_amount' => ""
//                 ]);
//             }
            
//             // Third Party Transporter Journal Invoicing
//             if ($request->input('vehicle_info_type') == "transporter" && $request->sale_order_id != "" && $request->input('vehicle_info') != "") {
//                 $transporter_total_amount = ($item_quantity_total * $request->input('transporter_freight')) + $request->input('transporter_other_charges');
//                 $transporter_total_amount = round($transporter_total_amount);
//                 $location_name = $account->location;
//                 if (!empty($request->input('shipping_name'))) {
//                     $shipp_account = Accounts::select('location')->find($request->input('shipping_name'));
//                     if ($shipp_account) $location_name = $shipp_account->location;
//                 }
               
//                 $series_configuration = VoucherSeriesConfiguration::where('company_id', $companyId)
//                     ->where('series', $request->input('series_no'))
//                     ->where('configuration_for', 'JOURNAL') 
//                     ->where('status', '1')
//                     ->first();

//                 $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
//                 $lastNumber = DB::table('journals')
//                     ->where('company_id', $companyId)
//                     ->where('financial_year', $financial_year)
//                     ->where('series_no', $request->input('series_no'))
//                     ->where('delete', '0')
//                     ->max(DB::raw("cast(voucher_no as SIGNED)"));

//                 if (!$lastNumber) {
//                     $journal_voucher_no = ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") ? (int)$series_configuration->invoice_start : 1;
//                 } else {
//                     $journal_voucher_no = ((int)$lastNumber) + 1;
//                 }

//                 $journal_invoice_prefix = "";
//                 if ($series_configuration && $series_configuration->manual_numbering == "NO") {
//                     if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
//                         $journal_invoice_prefix .= $series_configuration->prefix_value;
//                     }
//                     if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
//                         $journal_invoice_prefix .= $series_configuration->separator_1;
//                     }
//                     if ($series_configuration->year == "PREFIX TO NUMBER") {
//                         if ($series_configuration->year_format == "YY-YY") {
//                             $journal_invoice_prefix .= $default_fy;
//                         } else {
//                             $fy = explode('-', $default_fy);
//                             $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
//                         }
//                         if ($series_configuration->separator_2 != "") {
//                             $journal_invoice_prefix .= $series_configuration->separator_2;
//                         }
//                     }
//                     $journal_invoice_prefix .= $journal_voucher_no;
//                     if ($series_configuration->year == "SUFFIX TO NUMBER") {
//                         if ($series_configuration->separator_2 != "") {
//                             $journal_invoice_prefix .= $series_configuration->separator_2;
//                         }
//                         if ($series_configuration->year_format == "YY-YY") {
//                             $journal_invoice_prefix .= $default_fy;
//                         } else {
//                             $fy = explode('-', $default_fy);
//                             $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
//                         }
//                     }
//                     if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
//                         $journal_invoice_prefix .= $series_configuration->separator_3;
//                     }
//                     if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
//                         $journal_invoice_prefix .= $series_configuration->suffix_value;
//                     }
//                 }
//                 $journal_voucher_no = sprintf("%0" . $number_digit . "d", $journal_voucher_no);
//                 if ($journal_invoice_prefix == "") {
//                     $journal_invoice_prefix = $journal_voucher_no;
//                 }

//                 $journal = new Journal;
//                 $journal->date               = $request->input('date');
//                 $journal->voucher_no         = $journal_voucher_no;
//                 $journal->voucher_no_prefix  = $journal_invoice_prefix;
//                 $journal->series_no          = $request->input('series_no');
//                 $journal->long_narration     = "Bill No : " . $sale->voucher_no_prefix . ", Vehicle No. : " . $request->input('vehicle_no') . ", Location : " . $location_name . ", GR/PR No. : " . $request->input('gr_pr_no');
//                 $journal->company_id         = $companyId;
//                 $journal->financial_year     = $financial_year;
//                 $journal->claim_gst_status   = 'NO';
//                 $journal->merchant_gst       = $request->input('merchant_gst');

//                 if ($journal->save()) {
//                     SaleOrder::where('id', $request->sale_order_id)->update([
//                         'freight_type'           => $request->input('vehicle_info_type'),
//                         'freight_price'          => $request->input('transporter_freight'),
//                         'freight_transporter_id' => $request->input('vehicle_info'),
//                         'other_freight_amount'   => $request->input('transporter_other_charges')
//                     ]);
//                     Sales::where('id', $sale->id)->update(['transporter_journal_id' => $journal->id]);
                    
//                     $expense = DB::table('sale-order-settings')
//                         ->where('setting_type', 'EXPENSE_ACCOUNT')
//                         ->where('setting_for', 'SALE ORDER')
//                         ->where('company_id', $companyId)
//                         ->first();

//                     $joundetail = new JournalDetails;
//                     $joundetail->journal_id   = $journal->id;
//                     $joundetail->company_id   = $companyId;
//                     $joundetail->type         = "Credit";
//                     $joundetail->account_name = $request->input('vehicle_info');
//                     $joundetail->debit        = '0';
//                     $joundetail->credit       = $transporter_total_amount;            
//                     $joundetail->narration    = "";
//                     $joundetail->status       = '1';
//                     $joundetail->save();

//                     $ledger = new AccountLedger();
//                     $ledger->account_id     = $request->input('vehicle_info');               
//                     $ledger->credit         = $transporter_total_amount;
//                     $ledger->map_account_id = $expense->expense_account_id;
//                     $ledger->series_no      = $request->input('series_no');
//                     $ledger->txn_date       = $request->input('date');
//                     $ledger->company_id     = $companyId;
//                     $ledger->financial_year = $financial_year;
//                     $ledger->entry_type     = 7;
//                     $ledger->entry_type_id  = $journal->id;
//                     $ledger->entry_narration = "";               
//                     $ledger->created_by     = $userId;
//                     $ledger->created_at     = date('d-m-Y H:i:s');
//                     $ledger->save();
                  
//                     $joundetail = new JournalDetails;
//                     $joundetail->journal_id   = $journal->id;
//                     $joundetail->company_id   = $companyId;
//                     $joundetail->type         = "Debit";
//                     $joundetail->account_name = $expense->expense_account_id;
//                     $joundetail->debit        = $transporter_total_amount;
//                     $joundetail->credit       = '0';
//                     $joundetail->narration    = "";
//                     $joundetail->status       = '1';
//                     $joundetail->save();

//                     $ledger = new AccountLedger();
//                     $ledger->account_id     = $expense->expense_account_id;
//                     $ledger->debit          = $transporter_total_amount;
//                     $ledger->map_account_id = $request->input('vehicle_info');
//                     $ledger->series_no      = $request->input('series_no');
//                     $ledger->txn_date       = $request->input('date');
//                     $ledger->company_id     = $companyId;
//                     $ledger->financial_year = $financial_year;
//                     $ledger->entry_type     = 7;
//                     $ledger->entry_type_id  = $journal->id;
//                     $ledger->entry_narration = "";               
//                     $ledger->created_by     = $userId;
//                     $ledger->created_at     = date('d-m-Y H:i:s');
//                     $ledger->save();
//                 }
//             }
//         }

//         // 9. Take Updated Snapshot & Write Activity Log
//         $newSnapshot = [
//             'sale'                 => Sales::find($sale->id)->toArray(),
//             'items'                => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),
//             'sundries'             => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),
//             'item_ledgers'         => ItemLedger::where('source', 1)->where('source_id', $sale->id)->get()->toArray(),
//             'account_ledgers'      => AccountLedger::where('entry_type', 1)->where('entry_type_id', $sale->id)->get()->toArray(),
//             'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)->where('type', 'SALE')->get()->toArray(),
//         ];

//         ActivityLog::create([
//             'module_type' => 'sale',
//             'module_id'   => $sale->id,
//             'action'      => 'edit',
//             'old_data'    => $oldSnapshot,
//             'new_data'    => $newSnapshot,
//             'action_by'   => $userId,
//             'company_id'  => $companyId,
//             'action_at'   => now(),
//         ]);

//         // 10. API Success Response Flow
//         return response()->json([
//             'status'  => true,
//             'message' => 'Sale voucher updated successfully!',
//             'data'    => [
//                 'sale_id'    => $sale->id,
//                 'voucher_no' => $sale->voucher_no
//             ]
//         ], 200);
         
//     } else {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Something went wrong processing your request'
//         ], 500);
//     }
// }

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
            $validator = Validator::make($request->all(), [
         
         'partyId' => 'required',
         'company_id' => 'required',
         'series' => 'required',
         'merchant_gst' => 'required',
         
      ], [
          
            
            'company_id.required' => 'Company ID is required.',
            'partyId.required' => 'party is required',
            'series.required' => 'series is required',
            'merchant_gst.required' => 'party is reqired'
      ]);
   if ($validator->fails()) {
    return response()->json([
        'code' => 201,
        'message' => $validator->errors()->first()
    ], 201);
}

        // if(empty($request->partyId) || empty($request->merchant_gst) || empty($request->items)){
        //     return response()->json([
        //         'code' => 400,
        //         'message' => 'Required fields missing'
        //     ],201);
        // }

        /* ===============================
           2️⃣ STATE CHECK
        ===============================*/

        $party = DB::table('accounts')
                    ->where('id', $request->partyId)
                    ->first();

        if(!$party){
            return response()->json([
                'code' => 201,
                'message' => 'Invalid Party'
            ],201);
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
        ]);
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

   public function updateBoxSaleOrderStatus($boxSaleOrderId)
   {

      $companyId =
         Session::get('user_company_id');

      $items = DB::table('box_sale_order_items')

         ->where(
               'box_sale_order_id',
               $boxSaleOrderId
         )

         ->where(
               'company_id',
               $companyId
         )

         ->where(
               'delete',
               '0'
         )

         ->get();

      $allCompleted = true;


      foreach($items as $item)
      {

         $dispatchedQty = DB::table('sale_descriptions')

               ->where(
                  'box_sale_order_item_id',
                  $item->id
               )

               ->where(
                  'company_id',
                  $companyId
               )

               ->where(
                  'delete',
                  '0'
               )

               ->sum('qty');

         if(
               (float)$dispatchedQty
               >=
               (float)$item->qty
         )
         {

               DB::table('box_sale_order_items')

                  ->where(
                     'id',
                     $item->id
                  )

                  ->update([

                     'status' => 2

                  ]);

         }
         else
         {

               DB::table('box_sale_order_items')

                  ->where(
                     'id',
                     $item->id
                  )

                  ->update([

                     'status' => 1

                  ]);


               $allCompleted = false;

         }

      }

      DB::table('box_sale_orders')

         ->where(
               'id',
               $boxSaleOrderId
         )

         ->update([

               'status' =>

                  $allCompleted
                  ? 2
                  : 1

         ]);

   }

}
