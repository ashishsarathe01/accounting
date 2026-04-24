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
public function index(Request $request)
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

        foreach($series as $s){

            $openingAvg = ItemAverage::where('item_id',$item->id)
                ->where('series_no',$s->series)
                ->where('company_id',$company_id)
                ->where('stock_date','<',$from_date)
                ->orderBy('stock_date','desc')
                ->first();

            if($openingAvg){
                $opening += $openingAvg->amount;
            } else {
                $openingEntry = ItemLedger::where('item_id',$item->id)
                    ->where('series_no',$s->series)
                    ->where('company_id',$company_id)
                    ->where('source','-1')
                    ->where('delete_status','0')
                    ->first();

                if($openingEntry){
                    $opening += $openingEntry->total_price;
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

    foreach($series as $s){

        $openingAvg = ItemAverage::where('item_id',$item->id)
            ->where('series_no',$s->series)
            ->where('company_id',$company_id)
            ->where('stock_date','<',$from_date)
            ->orderBy('stock_date','desc')
            ->first();

        if($openingAvg){
            $opening += $openingAvg->amount;
        } else {

            $openingEntry = ItemLedger::where('item_id',$item->id)
                ->where('series_no',$s->series)
                ->where('company_id',$company_id)
                ->where('source','-1')
                ->where('delete_status','0')
                ->first();

            if($openingEntry){
                $opening += $openingEntry->total_price;
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

    return view('ItemSummary.items', compact(
        'group',
        'items',
        'from_date',
        'to_date'
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
}