<?php

namespace App\Http\Controllers\ItemSummary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\ItemLedger;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\StockJournal;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;
class ItemSummaryController extends Controller
{
    
public function index1(Request $request)
{
    $company_id = Session::get('user_company_id');
    $financial_year = Session::get('default_fy');

    if ($request->from_date && $request->to_date) {
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    } else {
        $y = explode("-", $financial_year);
        $from_date = date('Y-m-d', strtotime($y[0] . "-04-01"));
        $to_date   = date('Y-m-d', strtotime($y[1] . "-03-31"));
    }

    $companyData = Companies::where('id', $company_id)->first();

    if($companyData->gst_config_type == "single_gst"){
        $series = DB::table('gst_settings')
            ->where(['company_id'=>$company_id,'gst_type'=>"single_gst"])
            ->get();

        if($series->count()>0){
            $branch = GstBranch::select('branch_series as series')
                ->where([
                    'delete'=>'0',
                    'company_id'=>$company_id,
                    'gst_setting_id'=>$series[0]->id
                ])->get();

            $series = $series->merge($branch);
        }

    } else {

        $series = DB::table('gst_settings_multiple')
            ->select('id','series')
            ->where(['company_id'=>$company_id,'gst_type'=>"multiple_gst"])
            ->get();

        foreach ($series as $value) {
            $branch = GstBranch::select('branch_series as series')
                ->where([
                    'delete'=>'0',
                    'company_id'=>$company_id,
                    'gst_setting_multiple_id'=>$value->id
                ])->get();

            $series = $series->merge($branch);
        }
    }

    $allItems = DB::table('manage_items')
        ->where('company_id',$company_id)
        ->where('status','1')
        ->where('delete','0')
        ->get();

    foreach ($allItems as $item) {

        $opening = 0;
        $closing = 0;
        $opening_qty = 0;   // âœ… NEW
        $closing_qty = 0; 
        foreach($series as $s){

            $openingAvg = ItemAverage::where('item_id',$item->id)
                ->where('series_no',$s->series)
                ->where('company_id',$company_id)
                ->where('stock_date','<',$from_date)
                ->orderBy('stock_date','desc')
                ->first();

            if($openingAvg){
                $opening += $openingAvg->amount;
                $opening_qty += $openingAvg->average_weight; // âœ… ADD THIS
            } else {
                $openingEntry = ItemLedger::where('item_id',$item->id)
                    ->where('series_no',$s->series)
                    ->where('company_id',$company_id)
                    ->where('source','-1')
                    ->where('delete_status','0')
                    ->first();

                if($openingEntry){
                    $opening += $openingEntry->total_price;
                    $opening_qty += ($openingEntry->in_weight ?? 0) - ($openingEntry->out_weight ?? 0);
                }
            }

            $closingAvg = ItemAverage::where('item_id',$item->id)
                ->where('series_no',$s->series)
                ->where('company_id',$company_id)
                ->where('stock_date','<=',$to_date)
                ->orderBy('stock_date','desc')
                ->first();

            if($closingAvg){
                $closing += $closingAvg->amount;
                $closing_qty += $closingAvg->average_weight;
            } else {
                $closingEntry = ItemLedger::where('item_id',$item->id)
                    ->where('series_no',$s->series)
                    ->where('company_id',$company_id)
                    ->where('source','-1')
                    ->where('delete_status','0')
                    ->first();

                if($closingEntry){
                    $closing += $closingEntry->total_price;
                }
            }
        }

        $difference = $closing - $opening;

        $item->opening = $opening;
        $item->closing = $closing;
        $item->debit   = $difference > 0 ? $difference : 0;
        $item->credit  = $difference < 0 ? abs($difference) : 0;
    }

    $groups = DB::table('item_groups')
        ->where('company_id',$company_id)
        ->where('status','1')
        ->where('delete','0')
        ->orderBy('group_name')
        ->get();

    foreach ($groups as $group) {

        $groupItems = $allItems->where('g_name',$group->id);

        $group->opening = $groupItems->sum('opening');
        $group->closing = $groupItems->sum('closing');
        $group->debit   = $groupItems->sum('debit');
        $group->credit  = $groupItems->sum('credit');
    }

    return view('ItemSummary.index', compact(
        'groups',
        'from_date',
        'to_date'
    ));
}

public function index(Request $request)
{
    $company_id = Session::get('user_company_id');
    $financial_year = Session::get('default_fy');

    if ($request->from_date && $request->to_date) {
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    } else {
        $y = explode("-", $financial_year);
        $from_date = $y[0] . "-04-01";
        $to_date   = $y[1] . "-03-31";
    }

    // âœ… SERIES
    $companyData = Companies::where('id', $company_id)->first();

    if ($companyData->gst_config_type == "single_gst") {
        $series = DB::table('gst_settings')
            ->where(['company_id'=>$company_id,'gst_type'=>"single_gst"])
            ->pluck('series');

    } else {
        $series = DB::table('gst_settings_multiple')
            ->where(['company_id'=>$company_id,'gst_type'=>"multiple_gst"])
            ->pluck('series');
    }

    // âœ… ITEMS
    $allItems = DB::table('manage_items')
        ->where('company_id',$company_id)
        ->where('status','1')
        ->where('delete','0')
        ->get();

    foreach ($allItems as $item) {

        $opening = 0;
        $closing = 0;
        $opening_qty = 0;
        $closing_qty = 0;

        foreach($series as $s){

            // ðŸ”¹ OPENING
            $openingAvg = ItemAverage::where('item_id',$item->id)
                ->where('series_no',$s)
                ->where('company_id',$company_id)
                ->where('stock_date','<',$from_date)
                ->orderBy('stock_date','desc')
                ->first();

            if($openingAvg){
                $opening += $openingAvg->amount;
                $opening_qty += $openingAvg->average_weight;
            } else {
                $ledger = ItemLedger::where('item_id',$item->id)
                    ->where('series_no',$s)
                    ->where('company_id',$company_id)
                    ->where('txn_date','<',$from_date)
                    ->where('delete_status','0')
                    ->get();

                $opening += $ledger->sum('total_price');
                $opening_qty += $ledger->sum(function($row){
                   return (float) ($row->in_weight ?: 0) - (float) ($row->out_weight ?: 0);
                });
            }

            // ðŸ”¹ CLOSING
            $closingAvg = ItemAverage::where('item_id',$item->id)
                ->where('series_no',$s)
                ->where('company_id',$company_id)
                ->where('stock_date','<=',$to_date)
                ->orderBy('stock_date','desc')
                ->first();

            if($closingAvg){
                $closing += $closingAvg->amount;
                $closing_qty += $closingAvg->average_weight;
            } else {
                $ledger = ItemLedger::where('item_id',$item->id)
                    ->where('series_no',$s)
                    ->where('company_id',$company_id)
                    ->where('txn_date','<=',$to_date)
                    ->where('delete_status','0')
                    ->get();

                $closing += $ledger->sum('total_price');
                $closing_qty += $ledger->sum(function($row){
                    return (float) ($row->in_weight ?: 0) - (float) ($row->out_weight ?: 0);
                });
            }
        }

        // âœ… AMOUNT
        $diff = $closing - $opening;

                 $debitData = ItemAverageDetail::where('item_id', $item->id)
                 ->where('series_no', $s)
                    ->where('company_id', $company_id)
                    ->whereBetween('entry_date', [$from_date, $to_date])
                    ->where('status', '1')
                    ->get();


                // =========================
                // INWARD QTY TOTAL
                // =========================
                $debit_qty =
                    $debitData->sum(function ($row) {
                            return
                                (float)($row->purchase_weight ?? 0)
                            + (float)($row->sale_return_weight ?? 0)
                            + (float)($row->stock_transfer_in_weight ?? 0)
                            + (float)($row->stock_journal_in_weight ?? 0)
                            + (float)($row->production_in_weight ?? 0);
                    });


                // =========================
                // INWARD VALUE TOTAL
                // =========================
                $debit =
                    $debitData->sum(function ($row) {

                            $purchaseValue =
                                (
                                    (float)($row->purchase_amount ?? 0)
                                + (float)($row->purchase_bill_sundry_additive_amount ?? 0)
                                - (float)($row->purchase_bill_sundry_subtractive_amount ?? 0)
                                );

                            return
                                $purchaseValue
                                + (float)($row->sale_return_amount ?? 0)
                                + (float)($row->stock_transfer_in_amount ?? 0)
                                + (float)($row->stock_journal_in_amount ?? 0)
                                + (float)($row->production_in_amount ?? 0);
                    });


                // =========================
                // OUTWARD QTY TOTAL
                // =========================
                $credit_qty =
                    $debitData->sum(function ($row) {
                            return
                                (float)($row->sale_weight ?? 0)
                            + (float)($row->purchase_return_weight ?? 0)
                            + (float)($row->stock_transfer_weight ?? 0)
                            + (float)($row->stock_journal_out_weight ?? 0)
                            + (float)($row->production_out_weight ?? 0);
                    });
        $item->opening = $opening;
        $item->closing = $closing;
        $item->debit   = $debit;
        $item->credit  = $diff < 0 ? abs($diff) : 0;

        // âœ… QTY
        $item->opening_qty = $opening_qty;
        $item->closing_qty = $closing_qty;
        $item->debit_qty   = $debit_qty;
        $item->credit_qty  = $credit_qty;
    }

    // âœ… GROUP
    $groups = DB::table('item_groups')
        ->where('company_id',$company_id)
        ->where('status','1')
        ->where('delete','0')
        ->get();

    foreach ($groups as $group) {

        $groupItems = $allItems->where('g_name',$group->id);

        $group->opening = $groupItems->sum('opening');
        $group->closing = $groupItems->sum('closing');
        $group->debit   = $groupItems->sum('debit');
        $group->credit  = $groupItems->sum('credit');

        // âœ… QTY TOTAL
        $group->opening_qty = $groupItems->sum('opening_qty');
        $group->closing_qty = $groupItems->sum('closing_qty');
        $group->debit_qty   = $groupItems->sum('debit_qty');
        $group->credit_qty  = $groupItems->sum('credit_qty');
    }

    return view('ItemSummary.index', compact('groups','from_date','to_date'));
}

public function items(Request $request, $group_id)
{
    $company_id = Session::get('user_company_id');
    $financial_year = Session::get('default_fy');

    if ($request->from_date && $request->to_date) {
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    } else {
        $y = explode("-", $financial_year);
        $from_date = date('Y-m-d', strtotime($y[0] . "-04-01"));
        $to_date   = date('Y-m-d', strtotime($y[1] . "-03-31"));
    }

    $group = DB::table('item_groups')
        ->where('id', $group_id)
        ->where('company_id', $company_id)
        ->first();

    if (!$group) {
        return redirect()->route('item-summary.index')
            ->with('error', 'Group not found.');
    }

    $companyData = Companies::where('id', $company_id)->first();

    if($companyData->gst_config_type == "single_gst"){

        $series = DB::table('gst_settings')
            ->where([
                'company_id' => $company_id,
                'gst_type'   => "single_gst"
            ])
            ->get();

        if(count($series) > 0){
            $branch = GstBranch::select('id','branch_series as series')
                ->where([
                    'delete' => '0',
                    'company_id' => $company_id,
                    'gst_setting_id' => $series[0]->id
                ])
                ->get();

            if($branch->count() > 0){
                $series = $series->merge($branch);
            }
        }

    }else{

        $series = DB::table('gst_settings_multiple')
            ->select('id','series')
            ->where([
                'company_id' => $company_id,
                'gst_type'   => "multiple_gst"
            ])
            ->get();

        foreach ($series as $value) {

            $branch = GstBranch::select('id','branch_series as series')
                ->where([
                    'delete' => '0',
                    'company_id' => $company_id,
                    'gst_setting_multiple_id' => $value->id
                ])
                ->get();

            if($branch->count() > 0){
                $series = $series->merge($branch);
                             }
        }
    }

    $items = DB::table('manage_items')
        ->where('g_name', $group_id)
        ->where('company_id', $company_id)
        ->where('status', '1')
        ->where('delete', '0')
        ->orderBy('name')
        ->get();

foreach ($items as $item) {

    $opening = 0;
    $closing = 0;
    $opening_qty = 0;
    $closing_qty = 0;

    foreach($series as $s){

        $openingAvg = ItemAverage::where('item_id',$item->id)
            ->where('series_no',$s->series)
            ->where('company_id',$company_id)
            ->where('stock_date','<',$from_date)
            ->orderBy('stock_date','desc')
            ->first();

        if($openingAvg){
            $opening += $openingAvg->amount;
            $opening_qty += $openingAvg->average_weight;
        }

        $closingAvg = ItemAverage::where('item_id',$item->id)
            ->where('series_no',$s->series)
            ->where('company_id',$company_id)
            ->where('stock_date','<=',$to_date)
            ->orderBy('stock_date','desc')
            ->first();

        if($closingAvg){
            $closing += $closingAvg->amount;
            $closing_qty += $closingAvg->average_weight;
        }
    }

    $diff = $closing - $opening;
    
$debit = 0;
$debit_qty = 0;
$credit_qty = 0;

foreach($series as $s){

    $debitData = ItemAverageDetail::where('item_id', $item->id)
        ->where('series_no', $s->series)
         ->whereBetween('entry_date', [$from_date, $to_date])
        ->where('company_id', $company_id)
        ->where('status', '1')
        ->get();

    // =========================
    // INWARD QTY TOTAL
    // =========================
    $debit_qty +=
        $debitData->sum(function ($row) {
            return
                (float)($row->purchase_weight ?? 0)
              + (float)($row->sale_return_weight ?? 0)
              + (float)($row->stock_transfer_in_weight ?? 0)
              + (float)($row->stock_journal_in_weight ?? 0)
              + (float)($row->production_in_weight ?? 0);
        });

    // =========================
    // INWARD VALUE TOTAL
    // =========================
    $debit +=
        $debitData->sum(function ($row) {

            $purchaseValue =
                (float)($row->purchase_amount ?? 0)
              + (float)($row->purchase_bill_sundry_additive_amount ?? 0)
              - (float)($row->purchase_bill_sundry_subtractive_amount ?? 0);

            return
                $purchaseValue
              + (float)($row->sale_return_amount ?? 0)
              + (float)($row->stock_transfer_in_amount ?? 0)
              + (float)($row->stock_journal_in_amount ?? 0)
              + (float)($row->production_in_amount ?? 0);
        });

    // =========================
    // OUTWARD QTY TOTAL
    // =========================
    $credit_qty +=
        $debitData->sum(function ($row) {
            return
                (float)($row->sale_weight ?? 0)
              + (float)($row->purchase_return_weight ?? 0)
              + (float)($row->stock_transfer_weight ?? 0)
              + (float)($row->stock_journal_out_weight ?? 0)
              + (float)($row->production_out_weight ?? 0);
        });
}

    $item->opening = $opening;
    $item->closing = $closing;
    $item->debit   = $debit;
    $item->credit  = $diff < 0 ? abs($diff) : 0;

    $item->opening_qty = $opening_qty;
    $item->closing_qty = $closing_qty;
    $item->debit_qty   = $debit_qty;
    $item->credit_qty  = $credit_qty;
}
$items = $items->filter(function($item){
    return 
        (float)$item->opening != 0 ||
        (float)$item->closing != 0 ||
        (float)$item->debit != 0 ||
        (float)$item->credit != 0 ||
        (float)$item->opening_qty != 0 ||
        (float)$item->closing_qty != 0;
});
    return view('ItemSummary.items', compact(
        'group',
        'items',
        'from_date',
        'to_date'
    ));
}

public function monthly1(Request $request, $item_id)
{
    $company_id = Session::get('user_company_id');
    $financial_year = Session::get('default_fy');

    if(isset($request->from_date) && isset($request->to_date)){
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    } else {
        $y = explode("-", $financial_year);
        $from_date = date('Y-m-d', strtotime($y[0] . "-04-01"));
        $to_date   = date('Y-m-d', strtotime($y[1] . "-03-31"));
    }

    $item = ManageItems::where('id', $item_id)
        ->where('company_id', $company_id)
        ->first();

    if (!$item) {
        return redirect()->route('item-summary.index')
            ->with('error', 'Item not found.');
    }

    $companyData = Companies::where('id', $company_id)->first();

if($companyData->gst_config_type == "single_gst"){

    $series = DB::table('gst_settings')
        ->where([
            'company_id' => $company_id,
            'gst_type'   => "single_gst"
        ])
        ->get();

    if(count($series) > 0){
        $branch = GstBranch::select('id','branch_series as series')
            ->where([
                'delete' => '0',
                'company_id' => $company_id,
                'gst_setting_id' => $series[0]->id
            ])
            ->get();

        if($branch->count() > 0){
            $series = $series->merge($branch);
        }
    }

}else if($companyData->gst_config_type == "multiple_gst"){

    $series = DB::table('gst_settings_multiple')
        ->select('id','series')
        ->where([
            'company_id' => $company_id,
            'gst_type'   => "multiple_gst"
        ])
        ->get();

    foreach ($series as $value) {

        $branch = GstBranch::select('id','branch_series as series')
            ->where([
                'delete' => '0',
                'company_id' => $company_id,
                'gst_setting_multiple_id' => $value->id
            ])
            ->get();

        if($branch->count() > 0){
            $series = $series->merge($branch);
        }
    }
}

    $monthly = collect();



$previousClosing = 0;

foreach($series as $s){

    $openingAvg = ItemAverage::where('item_id',$item_id)
        ->where('series_no',$s->series)
        ->where('company_id',$company_id)
        ->where('stock_date','<',$from_date)
        ->orderBy('stock_date','desc')
        ->first();

    if($openingAvg){
        $previousClosing += $openingAvg->amount;
    } else {

        $openingEntry = ItemLedger::where('item_id',$item_id)
            ->where('series_no',$s->series)
            ->where('company_id',$company_id)
            ->where('source','-1')
            ->where('delete_status','0')
            ->first();

        if($openingEntry){
            $previousClosing += $openingEntry->total_price;
        }
    }
}

$current = strtotime($from_date);

    while ($current <= strtotime($to_date)) {

        $naturalMonthEnd = date('Y-m-t', $current);
$monthEnd = strtotime($naturalMonthEnd) > strtotime($to_date)
    ? $to_date
    : $naturalMonthEnd;

        $closing = 0;

        foreach($series as $s){

    $avg = ItemAverage::where('item_id',$item_id)
        ->where('series_no',$s->series)
        ->where('company_id',$company_id)
        ->where('stock_date','<=',$monthEnd)
        ->orderBy('stock_date','desc')
        ->first();

    if($avg){
        $closing += $avg->amount;
    } else {

        $openingEntry = ItemLedger::where('item_id',$item_id)
            ->where('series_no',$s->series)
            ->where('company_id',$company_id)
            ->where('source','-1')
            ->where('delete_status','0')
            ->first();

        if($openingEntry){
            $closing += $openingEntry->total_price;
        }
    }
}

        $opening = $previousClosing;

        $difference = $closing - $opening;

        $debit  = $difference > 0 ? $difference : 0;
        $credit = $difference < 0 ? abs($difference) : 0;

        $monthly->push((object)[
            'month_key'  => date('Y-m', $current),
            'month_name' => date('M Y', $current),
            'opening'    => $opening,
            'debit'      => $debit,
            'credit'     => $credit,
            'closing'    => $closing,
        ]);

        $previousClosing = $closing;
        $current = strtotime("+1 month", $current);
    }

    return view('ItemSummary.monthly', compact(
        'item', 'monthly', 'from_date', 'to_date'
    ));
}




public function monthly(Request $request, $item_id)
{
    $company_id = Session::get('user_company_id');
    $financial_year = Session::get('default_fy');

    if(isset($request->from_date) && isset($request->to_date)){
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    } else {
        $y = explode("-", $financial_year);
        $from_date = $y[0] . "-04-01";
        $to_date   = $y[1] . "-03-31";
    }

    $item = ManageItems::where('id', $item_id)
        ->where('company_id', $company_id)
        ->first();

    if (!$item) {
        return redirect()->route('item-summary.index')
            ->with('error', 'Item not found.');
    }

    $companyData = Companies::where('id', $company_id)->first();

    // âœ… SERIES
    if($companyData->gst_config_type == "single_gst"){
        $series = DB::table('gst_settings')
            ->where(['company_id'=>$company_id,'gst_type'=>"single_gst"])
            ->pluck('series');
    } else {
        $series = DB::table('gst_settings_multiple')
            ->where(['company_id'=>$company_id,'gst_type'=>"multiple_gst"])
            ->pluck('series');
    }

    $monthly = collect();

    // âœ… PREVIOUS OPENING (AMOUNT + QTY)
    $previousClosing = 0;
    $previousClosingQty = 0;

    foreach($series as $s){

        $openingAvg = ItemAverage::where('item_id',$item_id)
            ->where('series_no',$s)
            ->where('company_id',$company_id)
            ->where('stock_date','<',$from_date)
            ->orderBy('stock_date','desc')
            ->first();

        if($openingAvg){
            $previousClosing += $openingAvg->amount;
            $previousClosingQty += $openingAvg->average_weight;
        }
    }

    $current = strtotime($from_date);

    while ($current <= strtotime($to_date)) {

        $naturalMonthEnd = date('Y-m-t', $current);
        $monthEnd = strtotime($naturalMonthEnd) > strtotime($to_date)
            ? $to_date
            : $naturalMonthEnd;

        $closing = 0;
        $closing_qty = 0;

        foreach($series as $s){

            $avg = ItemAverage::where('item_id',$item_id)
                ->where('series_no',$s)
                ->where('company_id',$company_id)
                ->where('stock_date','<=',$monthEnd)
                ->orderBy('stock_date','desc')
                ->first();

            if($avg){
                $closing += $avg->amount;
                $closing_qty += $avg->average_weight;
            }
        }
        
        // ✅ RESET MONTH TOTALS
$debit = 0;
$credit = 0;

$debit_qty = 0;
$credit_qty = 0;


// ✅ MONTH START DATE
$monthStart = date('Y-m-01', $current);


foreach($series as $s){

    $monthData = ItemAverageDetail::where('item_id', $item_id)
        ->where('series_no', $s)
        ->where('company_id', $company_id)
        ->where('status', '1')
        ->whereBetween('entry_date', [$monthStart, $monthEnd])
        ->get();


    // =========================
    // INWARD QTY
    // =========================
    $debit_qty += $monthData->sum(function ($row) {

        return
            (float)($row->purchase_weight ?? 0)
          + (float)($row->sale_return_weight ?? 0)
          + (float)($row->stock_transfer_in_weight ?? 0)
          + (float)($row->stock_journal_in_weight ?? 0)
          + (float)($row->production_in_weight ?? 0);

    });


    // =========================
    // INWARD VALUE
    // =========================
    $debit += $monthData->sum(function ($row) {

        $purchaseValue =
            (float)($row->purchase_amount ?? 0)
          + (float)($row->purchase_bill_sundry_additive_amount ?? 0)
          - (float)($row->purchase_bill_sundry_subtractive_amount ?? 0);

        return
            $purchaseValue
          + (float)($row->sale_return_amount ?? 0)
          + (float)($row->stock_transfer_in_amount ?? 0)
          + (float)($row->stock_journal_in_amount ?? 0)
          + (float)($row->production_in_amount ?? 0);

    });


    // =========================
    // OUTWARD QTY
    // =========================
    $credit_qty += $monthData->sum(function ($row) {

        return
            (float)($row->sale_weight ?? 0)
          + (float)($row->purchase_return_weight ?? 0)
          + (float)($row->stock_transfer_weight ?? 0)
          + (float)($row->stock_journal_out_weight ?? 0)
          + (float)($row->production_out_weight ?? 0);

    });




}

        // âœ… OPENING
        $opening = $previousClosing;
        $opening_qty = $previousClosingQty;

        // âœ… DIFFERENCE
        $difference = $closing - $opening;
        $qty_diff   = $closing_qty - $opening_qty;

        $debit  = $debit;
        $credit = $difference < 0 ? abs($difference) : 0;

        $debit_qty  = $debit_qty;
        $credit_qty = $credit_qty;

        // âœ… PUSH DATA
        $monthly->push((object)[
            'month_key'   => date('Y-m', $current),
            'month_name'  => date('M Y', $current),

            'opening'     => $opening,
            'opening_qty' => $opening_qty,

            'debit'       => $debit,
            'debit_qty'   => $debit_qty,

            'credit'      => $credit,
            'credit_qty'  => $credit_qty,

            'closing'     => $closing,
            'closing_qty' => $closing_qty,
        ]);

        // âœ… MOVE FORWARD
        $previousClosing = $closing;
        $previousClosingQty = $closing_qty;

        $current = strtotime("+1 month", $current);
    }

    return view('ItemSummary.monthly', compact(
        'item', 'monthly', 'from_date', 'to_date'
    ));
}
}