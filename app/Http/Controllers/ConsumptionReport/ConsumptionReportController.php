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

    $consumedItems = DB::table('consumption_items')
        ->join('manage_items', 'consumption_items.item_id', '=', 'manage_items.id')
        ->where('consumption_items.company_id', $company_id)
        ->select(
            'consumption_items.item_id',
            'manage_items.name'
        )
        ->distinct()
        ->orderBy('manage_items.name')
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

    $reportData = [];

    $consumedItems = DB::table('consumption_report_settings')
        ->join('manage_items', 'consumption_report_settings.consumed_item_id', '=', 'manage_items.id')
        ->where('consumption_report_settings.company_id', $company_id)
        ->select(
            'consumption_report_settings.consumed_item_id',
            'manage_items.name'
        )
        ->distinct()
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

        $reportData[] = [
            'item_name'      => $consumedItem->name,
            'qty'            => $totalQty,
            'amount'         => $totalAmount,
            'avg_price'      => $avgPrice,
            'generated_qty'  => $generatedQty,
            'per_kg'         => $perKg,
        ];
    }

    return view(
        'ConsumptionReport.Report',
        compact(
            'from_date',
            'to_date',
            'reportData'
        )
    );
}
}