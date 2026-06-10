<?php

namespace App\Http\Controllers\ConsumptionReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;

class ConsumptionReportController extends Controller
{
public function settings()
{
    $company_id = Session::get('user_company_id');

    $consumedItems = DB::table('manage_items')
        ->whereIn('id', function ($query) use ($company_id) {
            $query->select('item_id')
                ->from('consumption_items')
                ->where('company_id', $company_id)
                ->union(
                    DB::table('account_production_details')
                        ->select('consume_item')
                        ->where('company_id', $company_id)
                        ->whereNotNull('consume_item')
                );
        })
        ->select('id as item_id', 'name')
        ->orderBy('name')
        ->get();

    $generatedItems = DB::table('production_items')
        ->join('manage_items', 'production_items.item_id', '=', 'manage_items.id')
        ->where('production_items.company_id', $company_id)
        ->where('production_items.status', 1)
        ->select(
            'production_items.id',
            'production_items.item_id',
            'manage_items.name'
        )
        ->orderBy('manage_items.name')
        ->get();

    $savedMappings = DB::table('consumption_report_settings')
        ->where('company_id', $company_id)
        ->pluck(
            'generated_item_id',
            DB::raw("CONCAT(consumed_item_id,'_',generated_item_id)")
        )
        ->toArray();

    return view(
        'ConsumptionReport.Settings',
        compact(
            'consumedItems',
            'generatedItems',
            'savedMappings'
        )
    );
}

public function saveSettings(Request $request)
{
    $company_id = Session::get('user_company_id');

    DB::beginTransaction();

    try {

        DB::table('consumption_report_settings')
            ->where('company_id', $company_id)
            ->delete();

        if ($request->has('settings')) {

            foreach ($request->settings as $consumed_item_id => $generated_items) {

                foreach ($generated_items as $generated_item_id) {

                    DB::table('consumption_report_settings')->insert([
                        'consumed_item_id'  => $consumed_item_id,
                        'generated_item_id' => $generated_item_id,
                        'company_id'        => $company_id,
                        'status'            => 1,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);

                }
            }
        }

        DB::commit();

        return redirect()
            ->back()
            ->with('success', 'Settings saved successfully.');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    }
}
public function report(Request $request)
{
    $company_id = Session::get('user_company_id');

    $from_date = $request->from_date ?: date('Y-m-01');
    $to_date   = $request->to_date ?: date('Y-m-d');
    $groupedReport = [];
    $reportData = [];

    $consumedItems = DB::table('consumption_report_settings')
        ->join('manage_items', 'consumption_report_settings.consumed_item_id', '=', 'manage_items.id')
        ->leftJoin('item_groups', 'manage_items.g_name', '=', 'item_groups.id')
        ->where('consumption_report_settings.company_id', $company_id)
        ->select(
            'consumption_report_settings.consumed_item_id',
            'manage_items.name',
            'item_groups.id as group_id',
            'item_groups.group_name'
        )
        ->distinct()
        ->orderBy('item_groups.group_name')
        ->orderBy('manage_items.name')
        ->get();

    foreach ($consumedItems as $consumedItem) {

        $consumed = DB::table('account_production_details')
            ->where('company_id', $company_id)
            ->where('consume_item', $consumedItem->consumed_item_id)
            ->whereBetween('production_date', [$from_date, $to_date])
            ->selectRaw('
                SUM(consume_weight) as total_qty,
                SUM(consume_amount) as total_amount
            ')
            ->first();

        $totalQty = $consumed->total_qty ?? 0;
        $totalAmount = $consumed->total_amount ?? 0;

        $avgPrice = 0;

        if ($totalQty > 0) {
            $avgPrice = $totalAmount / $totalQty;
        }

        $generatedItemIds = DB::table('consumption_report_settings')
            ->where('company_id', $company_id)
            ->where('consumed_item_id', $consumedItem->consumed_item_id)
            ->pluck('generated_item_id')
            ->toArray();

        $manageItemIds = DB::table('production_items')
            ->whereIn('id', $generatedItemIds)
            ->pluck('item_id')
            ->toArray();

        $generatedQty = 0;

        if (!empty($manageItemIds)) {

            $generatedQty = DB::table('account_production_details')
                ->where('company_id', $company_id)
                ->whereIn('new_item', $manageItemIds)
                ->whereBetween('production_date', [$from_date, $to_date])
                ->sum('new_weight');
        }

        $perKg = 0;

        if ($generatedQty > 0) {
            $perKg = $totalAmount / $generatedQty;
        }

        if (!isset($groupedReport[$consumedItem->group_id])) {

            $groupedReport[$consumedItem->group_id] = [
                'group_id' => $consumedItem->group_id,
                'group_name' => $consumedItem->group_name ?? 'Others',
                'qty' => 0,
                'amount' => 0,
                'generated_qty' => 0,
                'items' => []
            ];
        }

        $groupedReport[$consumedItem->group_id]['qty'] += $totalQty;
        $groupedReport[$consumedItem->group_id]['amount'] += $totalAmount;
        $groupedReport[$consumedItem->group_id]['generated_qty'] += $generatedQty;

        $groupedReport[$consumedItem->group_id]['items'][] = [
            'item_name' => $consumedItem->name,
            'qty' => $totalQty,
            'amount' => $totalAmount,
            'avg_price' => $avgPrice,
            'generated_qty' => $generatedQty,
            'per_kg' => $perKg
        ];
    }
    $totalProduction = DB::table('account_production_details')
        ->whereBetween('production_date', [$from_date, $to_date])
        ->where('company_id', $company_id)
        ->sum('new_weight');

    $productionDetails = DB::table('account_production_details as apd')
        ->join('manage_items as mi', 'mi.id', '=', 'apd.new_item')
        ->select(
            'apd.new_item as item_id',
            'mi.name as item_name',
            DB::raw('SUM(apd.new_weight) as total')
        )
        ->whereBetween('apd.production_date', [$from_date, $to_date])
        ->where('apd.company_id', $company_id)
        ->groupBy('apd.new_item', 'mi.name')
        ->get();

    $electricity = DB::table('consumption')
        ->where('company_id', $company_id)
        ->whereBetween('date', [$from_date, $to_date])
        ->selectRaw('
            SUM(COALESCE(electricity_units,0) + COALESCE(electricity_unit_night,0)) as total_units,
            SUM(
                (COALESCE(electricity_units,0) + COALESCE(electricity_unit_night,0))
                * COALESCE(unit_price,0)
            ) as total_amount
        ')
        ->first();

    $electricityQty = $electricity->total_units ?? 0;
    $electricityAmount = $electricity->total_amount ?? 0;

    $electricityAvgPrice = 0;

    if ($electricityQty > 0) {
        $electricityAvgPrice = $electricityAmount / $electricityQty;
    }

    $electricityPerKg = 0;

    if ($totalProduction > 0) {
        $electricityPerKg = $electricityAmount / $totalProduction;
    }
    $reportData[] = [
        'item_name'      => 'ELECTRICITY',
        'qty'            => $electricityQty,
        'amount'         => $electricityAmount,
        'avg_price'      => $electricityAvgPrice,
        'generated_qty'  => $totalProduction,
        'per_kg'         => $electricityPerKg,
    ];
    return view(
        'ConsumptionReport.Report',
        compact(
            'from_date',
            'to_date',
            'groupedReport',
            'productionDetails',
            'totalProduction'
        )
    );
}
}