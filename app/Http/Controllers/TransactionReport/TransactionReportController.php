<?php

namespace App\Http\Controllers\TransactionReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DB;

class TransactionReportController extends Controller
{

public function index(Request $request)
{

    $company_id = Session::get('user_company_id');

    $from_date = $request->from_date ?? Carbon::today()->format('Y-m-d');
    $to_date   = $request->to_date ?? Carbon::today()->format('Y-m-d');
    $module_filter = $request->module_filter;

    /*
    |---------------------------------------
    | Sales
    |---------------------------------------
    */

    $sales = DB::table('sales')
    
    ->select(
        'sales.date',
        'approved_status',
        'approved_at',
        DB::raw("'Sale' as module"),
        DB::raw("'sale' as module_type"),
        'series_no as series',
        DB::raw("CONCAT(voucher_no_prefix,'') as reference"),
        'billing_name as party',
        'sales.id as transaction_id',
        'total as debit',
        DB::raw("0 as credit")
            )
    ->where('sales.company_id',$company_id)
    ->where('sales.approved_status',0)
    ->where('sales.delete','0')
    ->whereBetween('sales.date',[$from_date,$to_date])
    ->get();

    /*
    |---------------------------------------
    | Purchase
    |---------------------------------------
    */

    $purchases = DB::table('purchases')
    ->leftJoin('supplier_purchase_vehicle_details','purchases.id','=','supplier_purchase_vehicle_details.map_purchase_id')
    ->leftJoin(DB::raw('
        (SELECT item_id, MAX(group_type) as group_type 
         FROM `sale-order-settings`
         GROUP BY item_id
        ) as sos
    '), function ($join) {
        $join->on('supplier_purchase_vehicle_details.group_id', '=', 'sos.item_id');
    })
    ->select(
        'date',
        'approved_status',
        'approved_at',
        DB::raw("'Purchase' as module"),
        DB::raw("'purchase' as module_type"),
        'series_no as series',
        'purchases.voucher_no as reference',
        'billing_name as party',
        'purchases.id as transaction_id',
        DB::raw("0 as debit"),
        'total as credit',
        'sos.group_type' // ✅ now works
    )
    ->when($module_filter == 'WASTE KRAFT' || $module_filter == 'BOILER FUEL' || $module_filter == 'SPARE PART', function ($q) use ($request) {
            if(!empty($request->module_filter)){
                $q->where('sos.group_type', $request->module_filter);
            }
        })
    ->where('purchases.company_id',$company_id)
    ->where('purchases.delete','0')
    ->where('purchases.approved_status',0)
    ->whereBetween('date',[$from_date,$to_date])
    ->get();
    //echo "<pre>";print_r($purchases->toArray());die;
/*
|---------------------------------------
| Credit Note (Sales Return)
|---------------------------------------
*/

$credit_notes = DB::table('sales_returns')
    ->leftJoin('accounts','accounts.id','=','sales_returns.party')
    ->select(
        'sales_returns.date',
        'approved_status',
        'approved_at',
        DB::raw("'Credit Note' as module"),
        DB::raw("'credit_note' as module_type"),
        'series_no as series',
        DB::raw("CONCAT(sales_returns.sr_prefix,'') as reference"),
        'accounts.account_name as party',
        'sales_returns.id as transaction_id',
        'sales_returns.sr_nature',
        'sales_returns.sr_type',

        DB::raw("0 as debit"),
        'sales_returns.total as credit'
    )
    ->where('sales_returns.company_id',$company_id)
    ->where('sales_returns.delete','0')
    ->where('sales_returns.status','1')
    ->where('sales_returns.approved_status',0)
    ->whereBetween('sales_returns.date',[$from_date,$to_date])
    ->get();

/*
|---------------------------------------
| Debit Note (Purchase Return)
|---------------------------------------
*/

$debit_notes = DB::table('purchase_returns')
    ->leftJoin('accounts','accounts.id','=','purchase_returns.party')
    ->select(
        'purchase_returns.date',
        'approved_status',
        'approved_at',
        DB::raw("'Debit Note' as module"),
        DB::raw("'debit_note' as module_type"),
        'series_no as series',
        DB::raw("CONCAT(purchase_returns.sr_prefix,'') as reference"),
        DB::raw("COALESCE(accounts.account_name,'Unknown Party') as party"),
        'purchase_returns.id as transaction_id',

        'purchase_returns.sr_nature',
        'purchase_returns.sr_type',

        'purchase_returns.total as debit',
        DB::raw("0 as credit")
    )
    ->where('purchase_returns.company_id',$company_id)
    ->where('purchase_returns.delete','0')
    ->where('purchase_returns.status','1')
    ->where('purchase_returns.approved_status',0)
    ->whereBetween('purchase_returns.date',[$from_date,$to_date])
    ->get();
/*
|---------------------------------------
| Payment
|---------------------------------------
*/

$payments = DB::table('payments')
    ->join('payment_details','payment_details.payment_id','=','payments.id')
    ->leftJoin('accounts','accounts.id','=','payment_details.account_name')
    ->select(
        'payments.date',
        'approved_status',
        'approved_at',
        DB::raw("'Payment' as module"),
        DB::raw("'payment' as module_type"),
        'series_no as series',
        'payments.voucher_no as reference',
        DB::raw("COALESCE(accounts.account_name,'Unknown Party') as party"),
        'payments.id as transaction_id',
        'payment_details.debit as debit',
        DB::raw("0 as credit")
    )
    ->where('payments.company_id',$company_id)
    ->where('payments.delete','0')
    ->where('payments.status','1')
    ->where('payment_details.delete','0')
    ->where('payment_details.type','Debit')
    ->where('payments.approved_status',0)
    ->whereBetween('payments.date',[$from_date,$to_date])
    ->get();

/*
|---------------------------------------
| Receipt
|---------------------------------------
*/

$receipts = DB::table('receipts')
    ->join('receipt_details','receipt_details.receipt_id','=','receipts.id')
    ->join('accounts','accounts.id','=','receipt_details.account_name')
    ->select(
    'receipts.date',
    'approved_status',
        'approved_at',
    DB::raw("'Receipt' as module"),
    DB::raw("'receipt' as module_type"),
    'series_no as series',
    'receipts.voucher_no as reference',
    'accounts.account_name as party',
    'receipts.id as transaction_id',
    DB::raw("0 as debit"),
    'receipt_details.credit as credit'
)
    ->where('receipts.company_id',$company_id)
    ->where('receipts.delete','0')
    ->where('receipts.status','1')
    ->where('receipt_details.type','Credit')
    ->where('receipt_details.delete','0')
    ->where('receipts.approved_status',0)
    ->whereNotNull('receipt_details.account_name')
    ->whereBetween('receipts.date',[$from_date,$to_date])
    ->get();


/*
|---------------------------------------
| Journal
|---------------------------------------
*/

$journals = DB::table('journals')
    ->join('journal_details','journal_details.journal_id','=','journals.id')
    ->join('accounts','accounts.id','=','journal_details.account_name')
    ->select(
        'journals.date',
        'approved_status',
        'approved_at',
        DB::raw("'Journal' as module"),
        DB::raw("'journal' as module_type"),
        'series_no as series',
        'journals.voucher_no as reference',
        'accounts.account_name as party',
        'journals.id as transaction_id',
        'journal_details.debit as debit',
        'journal_details.credit as credit'
    )
    ->where('journals.company_id',$company_id)
    ->where('journals.delete','0')
    ->where('journals.status','1')
    ->where('journal_details.delete','0')

    ->where(function($q){
        $q->where('journal_details.debit','>',0)
          ->orWhere('journal_details.credit','>',0);
    })

    ->whereBetween('journals.date',[$from_date,$to_date])
    ->where('journals.approved_status',0)
    ->orderBy('journals.date','desc')
    ->orderBy('journals.id','desc')
    ->get();

/*
|---------------------------------------
| Contra
|---------------------------------------
*/

$contras = DB::table('contras')
    ->join('contra_details','contra_details.contra_id','=','contras.id')
    ->join('accounts','accounts.id','=','contra_details.account_name')
    ->select(
        'contras.date',
        'approved_status',
        'approved_at',
        DB::raw("'Contra' as module"),
        DB::raw("'contra' as module_type"),
        'series_no as series',
        'contras.voucher_no as reference',
        'accounts.account_name as party',
        'contras.id as transaction_id',
        'contra_details.debit as debit',
        'contra_details.credit as credit'
    )
    ->where('contras.company_id',$company_id)
    ->where('contras.delete','0')
    ->where('contras.status','1')
    ->where('contra_details.delete','0')
    ->where('contras.approved_status',0)
    ->where(function($q){
        $q->where('contra_details.debit','>',0)
          ->orWhere('contra_details.credit','>',0);
    })

    ->whereBetween('contras.date',[$from_date,$to_date])

    ->orderBy('contras.date','desc')
    ->orderBy('contras.id','desc')
    ->orderBy('contra_details.id')

    ->get();


/*
|---------------------------------------
| Stock Journal
|---------------------------------------
*/
$stockJournals = DB::table('stock_journal')
    ->join('stock_journal_detail','stock_journal_detail.parent_id','=','stock_journal.id')

    ->leftJoin('manage_items as consume_item','consume_item.id','=','stock_journal_detail.consume_item')
    ->leftJoin('manage_items as new_item','new_item.id','=','stock_journal_detail.new_item')
    ->select(
        'stock_journal.jdate as date',
        'approved_status',
        'approved_at',
        DB::raw("'Stock Journal' as module"),
        DB::raw("'stock_journal' as module_type"),
        'series_no as series',
        'stock_journal.voucher_no as reference',

        DB::raw("
            CASE 
                WHEN stock_journal_detail.consume_item IS NOT NULL 
                THEN consume_item.name
                ELSE new_item.name
            END as party
        "),

        'stock_journal.id as transaction_id',

        DB::raw("
            CASE 
                WHEN stock_journal_detail.new_item IS NOT NULL
                THEN CONCAT(stock_journal_detail.new_weight,' ',stock_journal_detail.new_item_unit_name)
                ELSE NULL
            END as debit
        "),

        DB::raw("
            CASE 
                WHEN stock_journal_detail.consume_item IS NOT NULL
                THEN CONCAT(stock_journal_detail.consume_weight,' ',stock_journal_detail.consume_item_unit_name)
                ELSE NULL
            END as credit
        ")
    )

    ->where('stock_journal.company_id',$company_id)
    ->where('stock_journal.status',1)
    ->where('stock_journal.approved_status',0)
    ->where(function($q){
        $q->whereNotNull('stock_journal_detail.consume_item')
          ->orWhereNotNull('stock_journal_detail.new_item');
    })

    ->whereBetween('stock_journal.jdate',[$from_date,$to_date])

    ->orderBy('stock_journal.jdate','desc')
    ->orderBy('stock_journal.id','desc')
    ->orderBy('stock_journal_detail.id')

    ->get();

/*
|---------------------------------------
| Stock Transfer
|---------------------------------------
*/

$stockTransfers = DB::table('stock_transfers')

->join(
    'stock_transfer_descriptions',
    'stock_transfer_descriptions.stock_transfer_id',
    '=',
    'stock_transfers.id'
)

->leftJoin('manage_items',
    'manage_items.id',
    '=',
    'stock_transfer_descriptions.goods_discription'
)

->leftJoin('units',
    'units.id',
    '=',
    'stock_transfer_descriptions.unit'
)

->select(
    'stock_transfers.transfer_date as date',
    'approved_status',
        'approved_at',
    DB::raw("'Stock Transfer' as module"),
    DB::raw("'stock_transfer' as module_type"),
    'series_no as series',

    'stock_transfers.voucher_no as reference',

    'manage_items.name as party',

    'stock_transfers.id as transaction_id',

    DB::raw("NULL as debit"),

    DB::raw("
        CONCAT(
            stock_transfer_descriptions.qty,
            ' ',
            units.name
        ) as credit
    ")
)

->where('stock_transfers.company_id',$company_id)
->where('stock_transfers.status',1)
->where('stock_transfers.delete_status',0)
->where('stock_transfers.approved_status',0)
->where('stock_transfer_descriptions.delete_status',0)

->whereBetween('stock_transfers.transfer_date',[$from_date,$to_date])

->orderBy('stock_transfers.transfer_date','desc')
->orderBy('stock_transfers.id','desc')
->orderBy('stock_transfer_descriptions.id')

->get();

    /*
    |---------------------------------------
    | Merge Transactions
    |---------------------------------------
    */

    $transactions = $sales
        ->merge($purchases)
        ->merge($credit_notes)
        ->merge($debit_notes)
        ->merge($payments)
        ->merge($receipts)
        ->merge($journals)
        ->merge($contras)
        ->merge($stockJournals)
        ->merge($stockTransfers);

if(!empty($module_filter))
{
    if($module_filter=="WASTE KRAFT" || $module_filter=="BOILER FUEL" || $module_filter=="SPARE PART"){
        $transactions = $transactions->where('module_type','purchase');
    }else{
        $transactions = $transactions->where('module_type',$module_filter);
    }
    
}

$transactions = $transactions
    ->sortBy([
        ['date', 'desc'],
        ['group_type', 'asc'],
        ['module_type', 'asc']
    ])
    ->values();


    return view('TransactionReport.transaction_report',
        compact('transactions','from_date','to_date', 'module_filter')
    );

}


public function viewTransaction(Request $request)
{

$id = $request->id;
$module = $request->module;
$long_narration = "";
if($module == "payment")
{

$voucher = DB::table('payments')
->where('id',$id)
->first();

$details = DB::table('payment_details')
->leftJoin('accounts','accounts.id','=','payment_details.account_name')
->select(
'payment_details.type',
'accounts.account_name as account',
'payment_details.debit',
'payment_details.credit'
)
->where('payment_id',$id)
->get();

$date = $voucher->date;

}

elseif($module == "receipt")
{

$voucher = DB::table('receipts')
->where('id',$id)
->first();

$details = DB::table('receipt_details')
->leftJoin('accounts','accounts.id','=','receipt_details.account_name')
->select(
'receipt_details.type',
'accounts.account_name as account',
'receipt_details.debit',
'receipt_details.credit'
)
->where('receipt_id',$id)
->get();

$date = $voucher->date;

}

elseif($module == "contra")
{

$voucher = DB::table('contras')
->where('id',$id)
->first();

$details = DB::table('contra_details')
->leftJoin('accounts','accounts.id','=','contra_details.account_name')
->select(
'contra_details.type',
'accounts.account_name as account',
'contra_details.debit',
'contra_details.credit'
)
->where('contra_id',$id)
->get();

$date = $voucher->date;

}
elseif($module == "journal")
{

$voucher = DB::table('journals')
->where('id',$id)
->first();

$details = DB::table('journal_details')
->leftJoin('accounts','accounts.id','=','journal_details.account_name')
->select(
'journal_details.type',
'accounts.account_name as account',
'journal_details.debit',
'journal_details.credit',
'journal_details.narration as remark'
)
->where('journal_id',$id)
->get();

$date = $voucher->date;
$long_narration = $voucher->long_narration;

}

elseif($module == "stock_journal")
{

$voucher = DB::table('stock_journal')
->where('id',$id)
->first();

$details = DB::table('stock_journal_detail')
->leftJoin('manage_items as consume_item','consume_item.id','=','stock_journal_detail.consume_item')
->leftJoin('manage_items as new_item','new_item.id','=','stock_journal_detail.new_item')
->select(
DB::raw("
CASE 
WHEN stock_journal_detail.consume_item IS NOT NULL 
THEN consume_item.name
ELSE new_item.name
END as item
"),
'stock_journal_detail.consume_weight as consume_qty',
'stock_journal_detail.consume_amount',
'stock_journal_detail.new_weight as production_qty',
'stock_journal_detail.new_amount as production_amount'
)
->where('stock_journal_detail.parent_id',$id)
->get();

$total_consume_qty = $details->sum('consume_qty');
$total_consume_amount = $details->sum('consume_amount');

$total_production_qty = $details->sum('production_qty');
$total_production_amount = $details->sum('production_amount');

$date = $voucher->jdate;

}
elseif($module == "stock_transfer")
{

$voucher = DB::table('stock_transfers')
->where('id',$id)
->first();

$details = DB::table('stock_transfer_descriptions')

->leftJoin('manage_items','manage_items.id','=','stock_transfer_descriptions.goods_discription')

->select(
'manage_items.name as item',
'stock_transfer_descriptions.qty',
'stock_transfer_descriptions.price',
'stock_transfer_descriptions.amount'
)

->where('stock_transfer_descriptions.stock_transfer_id',$id)
->where('stock_transfer_descriptions.delete_status',0)
->get();

$total_qty = $details->sum('qty');
$total_amount = $details->sum('amount');

$date = $voucher->transfer_date;
$sundries = DB::table('stock_transfer_sundries')

->leftJoin('bill_sundrys','bill_sundrys.id','=','stock_transfer_sundries.bill_sundry')

->select(
'bill_sundrys.name',
'stock_transfer_sundries.amount'
)

->where('stock_transfer_sundries.stock_transfer_id',$id)
->where('stock_transfer_sundries.delete_status',0)

->get();
}
return response()->json([
"module"=>$module,
"voucher"=>$voucher->voucher_no ?? '',
"date"=>date('d-m-Y',strtotime($date)),
"series"=>$voucher->series_no ?? '',
"claim_gst"=>$voucher->claim_gst_status ?? 'NO',
"invoice_no"=>$voucher->invoice_no ?? '',
"narration"=>$voucher->narration ?? '',
"details"=>$details ?? [],
"sundries"=>$sundries ?? [],
"total_consume_qty"=>$total_consume_qty ?? '',
"total_consume_amount"=>$total_consume_amount ?? '',
"total_production_qty"=>$total_production_qty ?? '',
"total_production_amount"=>$total_production_amount ?? '',
"total_qty"=>$total_qty ?? '',
"total_amount"=>$total_amount ?? '',
"grand_total"=>$voucher->grand_total ?? '',
"from"=>$voucher->material_center_from ?? '',
"to"=>$voucher->material_center_to ?? '',
"long_narration"=>$long_narration
]);

}


public function approveTransaction(Request $request)
{
    $id = $request->id;
    $module = $request->module;

    $user_id = Session::get('user_id'); 

    $tables = [
        "sale" => "sales",
        "purchase" => "purchases",
        "credit_note" => "sales_returns",
        "debit_note" => "purchase_returns",
        "payment" => "payments",
        "receipt" => "receipts",
        "journal" => "journals",
        "contra" => "contras",
        "stock_journal" => "stock_journal",
        "stock_transfer" => "stock_transfers"
    ];

    if(!isset($tables[$module])){
        return response()->json(["status"=>false]);
    }

    DB::table($tables[$module])
        ->where('id',$id)
        ->update([
            'approved_status'=>1,
            'approved_by'=>$user_id,
            'approved_at'=>now()
        ]);

    return response()->json([
        "status"=>true,
        "message"=>"Approved Successfully"
    ]);
}
}