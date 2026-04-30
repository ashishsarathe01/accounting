<?php

namespace App\Http\Controllers\YieldReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class YieldReportController extends Controller
{
    public function index()
    {
        $data = DB::table('yield_reports as yr')
            ->join('yield_report_items as yri', 'yr.id', '=', 'yri.yield_report_id')
            ->join('manage_items as mi', 'yri.item_id', '=', 'mi.id')
            ->select(
                'yr.id as report_id',
                'yri.type',
                'yri.recovery_status',
                'yri.recovery_percent',
                'mi.name as item_name'
            )
            ->orderBy('yr.id', 'desc')
            ->get();

        return view('YieldReport.Settings', compact('data'));
    }
    public function create()
    {
        $items = DB::table('manage_items')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id', Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select('id', 'name') 
            ->get();

        return view('YieldReport.AddSettings', compact('items'));
    }
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();

            /* ================= VALIDATION ================= */

            $allItems = [];

            // Collect items from material section
            if (!empty($request->material)) {
                foreach ($request->material as $row) {
                    if (!empty($row['item_id'])) {
                        $allItems[] = $row['item_id'];
                    }
                }
            }

            if (!empty($request->main_material)) {
                foreach ($request->main_material as $row) {
                    if (!empty($row['item_id'])) {
                        $allItems[] = $row['item_id'];
                    }
                }
            }

            if (count($allItems) !== count(array_unique($allItems))) {
                return back()->with('error', 'Duplicate items are not allowed.')->withInput();
            }

            if (empty($allItems)) {
                return back()->with('error', 'Please add at least one item.')->withInput();
            }

            /* ================= INSERT MAIN ================= */

            $yieldReportId = DB::table('yield_reports')->insertGetId([
                'company_id' => Session::get('user_company_id'),
                'created_by' => Session::get('user_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /* ================= INSERT ITEMS ================= */

            $insertData = [];

            // ðŸ”¹ SECTION 1: MATERIAL REQUIRED
            if (!empty($request->material)) {
                foreach ($request->material as $row) {

                    if (empty($row['item_id'])) continue;

                    $recoveryStatus = $row['recovery_status'] ?? 0;

                    $insertData[] = [
                        'yield_report_id' => $yieldReportId,
                        'item_id' => $row['item_id'],
                        'type' => 'material_required',
                        'recovery_status' => $recoveryStatus,
                        'recovery_percent' => $recoveryStatus == 1
                            ? ($row['recovery_percent'] ?? 0)
                            : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // SECTION 2: MAIN RAW MATERIAL
            if (!empty($request->main_material)) {
                foreach ($request->main_material as $row) {

                    if (empty($row['item_id'])) continue;

                    $insertData[] = [
                        'yield_report_id' => $yieldReportId,
                        'item_id' => $row['item_id'],
                        'type' => 'main_raw_material',
                        'recovery_status' => 1, // always yes
                        'recovery_percent' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert
            if (!empty($insertData)) {
                DB::table('yield_report_items')->insert($insertData);
            }

            DB::commit();

            return redirect()
                ->route('yield-report.index')
                ->with('success', 'Yield Report created successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->with('error', 'Something went wrong.')
                ->withInput();
        }
    }
    public function edit($id)
    {
        $items = DB::table('manage_items')
            ->where('delete', '0')
            ->where('status', '1')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        $reportItems = DB::table('yield_report_items')
            ->where('yield_report_id', $id)
            ->get();

        $materialItems = $reportItems->where('type', 'material_required')->values();
        $mainItems     = $reportItems->where('type', 'main_raw_material')->values();

        return view('YieldReport.EditSettings', compact(
            'items',
            'materialItems',
            'mainItems',
            'id'
        ));
    }
    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();

            $existingIds = DB::table('yield_report_items')
                ->where('yield_report_id', $id)
                ->pluck('id')
                ->toArray();

            $submittedIds = [];

            /* ================= MATERIAL ================= */
            foreach ($request->material ?? [] as $row) {

                if (empty($row['item_id'])) continue;

                $data = [
                    'item_id' => $row['item_id'],
                    'type' => 'material_required',
                    'recovery_status' => $row['recovery_status'] ?? 0,
                    'recovery_percent' => ($row['recovery_status'] ?? 0)
                        ? ($row['recovery_percent'] ?? 0)
                        : null,
                    'updated_at' => now(),
                ];

                if (!empty($row['id'])) {

                    DB::table('yield_report_items')
                        ->where('id', $row['id'])
                        ->update($data);

                    $submittedIds[] = $row['id'];

                } else {

                    $newId = DB::table('yield_report_items')->insertGetId([
                        'yield_report_id' => $id,
                        'created_at' => now(),
                        ...$data
                    ]);

                    $submittedIds[] = $newId;
                }
            }

            /* ================= MAIN MATERIAL ================= */
            foreach ($request->main_material ?? [] as $row) {

                if (empty($row['item_id'])) continue;

                $data = [
                    'item_id' => $row['item_id'],
                    'type' => 'main_raw_material',
                    'recovery_status' => 1,
                    'recovery_percent' => null,
                    'updated_at' => now(),
                ];

                if (!empty($row['id'])) {

                    DB::table('yield_report_items')
                        ->where('id', $row['id'])
                        ->update($data);

                    $submittedIds[] = $row['id'];

                } else {

                    $newId = DB::table('yield_report_items')->insertGetId([
                        'yield_report_id' => $id,
                        'created_at' => now(),
                        ...$data
                    ]);

                    $submittedIds[] = $newId;
                }
            }


            $idsToDelete = array_diff($existingIds, $submittedIds);

            if (!empty($idsToDelete)) {
                DB::table('yield_report_items')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }

            DB::commit();

            return redirect()
                ->route('yield-report.index')
                ->with('success', 'Updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Something went wrong');
        }
    }
    public function report(Request $request)
    {
        $from_date = $request->from_date ?? date('Y-m-01');
        $to_date   = $request->to_date ?? date('Y-m-d');

        $companyId = Session::get('user_company_id');

        $yieldReport = DB::table('yield_reports')
            ->where('company_id', $companyId)
            ->latest()
            ->first();

        if (!$yieldReport) {
            $totalProduction = 0;
            $totalAdjustedConsumption = 0;
            $totalWaste = 0;
            $yieldLoss = 0;
            $yieldPercent = 0;
            $productionDetails = collect();
            $consumptionDetails = collect();
            $wasteDetails = collect();
            return view('YieldReport.YieldReport', compact('from_date',
            'to_date',
            'totalProduction',
            'totalAdjustedConsumption',
            'totalWaste',
            'yieldLoss',
            'yieldPercent',
            'productionDetails',
            'consumptionDetails',
            'wasteDetails'))
                ->with('error', 'No Yield Report settings found.');
        }

        $yieldReportId = $yieldReport->id;

       
        /* ================= PRODUCTION ================= */
        $totalProduction = DB::table('deckle_processes as dp')
        ->join('item_size_stocks as iss', 'iss.deckle_id', '=', 'dp.id')
        ->whereDate('dp.end_time_stamp', '>=', $from_date)
        ->whereDate('dp.end_time_stamp', '<=', $to_date)
        ->where('dp.status', 4)
        ->where('dp.company_id', $companyId)
        ->where('iss.company_id', $companyId)
        ->sum('iss.weight');
        $productionDetails = DB::table('deckle_processes as dp')
            ->join('item_size_stocks as iss', 'iss.deckle_id', '=', 'dp.id')
            ->join('manage_items as mi', 'mi.id', '=', 'iss.item_id')
            ->select(
                'iss.item_id',
                'mi.name as item_name',
                DB::raw('SUM(iss.weight) as total')
            )
            ->whereDate('dp.end_time_stamp', '>=', $from_date)
            ->whereDate('dp.end_time_stamp', '<=', $to_date)
            ->where('dp.status', 4)
            ->where('dp.company_id', $companyId)
             ->where('iss.company_id', $companyId)
            ->groupBy('iss.item_id', 'mi.name')
            ->get();

        /* ================= MATERIAL REQUIRED ================= */
        $materials = DB::table('yield_report_items')
            ->where('yield_report_id', $yieldReportId)
            ->where('type', 'material_required')
            ->where('recovery_status', 1)
            ->pluck('recovery_percent', 'item_id');

        $totalAdjustedConsumption = 0;

        if ($materials->isNotEmpty()) {

            $consumptions = DB::table('account_production_details')
                ->select('consume_item', DB::raw('SUM(consume_weight) as total'))
                ->whereIn('consume_item', $materials->keys())
                ->whereBetween('production_date', [$from_date, $to_date])
                ->where('company_id', $companyId)
                ->groupBy('consume_item')
                ->get();

            foreach ($consumptions as $row) {
                $percent = $materials[$row->consume_item];
                $totalAdjustedConsumption += ($row->total * $percent) / 100;
            }
        }
        $consumptionDetails = [];

        if ($materials->isNotEmpty()) {

            $consumptionDetails = DB::table('account_production_details as apd')
                ->join('yield_report_items as yri', function ($join) use ($yieldReportId) {
                    $join->on('yri.item_id', '=', 'apd.consume_item')
                        ->where('yri.yield_report_id', '=', $yieldReportId)
                        ->where('yri.type', '=', 'material_required')
                        ->where('yri.recovery_status', '=', 1);
                })
                ->join('manage_items as mi', 'mi.id', '=', 'apd.consume_item')
                ->select(
                    'apd.consume_item',
                    'mi.name as item_name',
                    'yri.recovery_percent as percent',
                    DB::raw('SUM(apd.consume_weight) as total')
                )
                ->whereBetween('apd.production_date', [$from_date, $to_date])
                ->where('apd.company_id', $companyId)
                ->groupBy('apd.consume_item', 'mi.name', 'yri.recovery_percent')
                ->get();
        }

        /* ================= WASTE ================= */
        $wasteItems = DB::table('yield_report_items')
            ->where('yield_report_id', $yieldReportId)
            ->where('type', 'main_raw_material')
            ->pluck('item_id');

        $totalWaste = 0;

        if ($wasteItems->isNotEmpty()) {
            $totalWaste = DB::table('account_production_details')
                ->whereIn('consume_item', $wasteItems)
                ->whereBetween('production_date', [$from_date, $to_date])
                ->where('company_id', $companyId)
                ->sum('consume_weight');
        }
        $wasteDetails = [];

        if ($wasteItems->isNotEmpty()) {
            $wasteDetails = DB::table('account_production_details as apd')
                ->join('manage_items as mi', 'mi.id', '=', 'apd.consume_item')
                ->select(
                    'apd.consume_item',
                    'mi.name as item_name',
                    DB::raw('SUM(apd.consume_weight) as total')
                )
                ->whereIn('apd.consume_item', $wasteItems)
                ->whereBetween('apd.production_date', [$from_date, $to_date])
                ->where('apd.company_id', $companyId)
                ->groupBy('apd.consume_item', 'mi.name')
                ->get();
        }

        /* ================= FINAL ================= */
        $yieldLoss = $totalProduction - $totalAdjustedConsumption;

        $yieldPercent = $totalWaste > 0
            ? ($yieldLoss / $totalWaste) * 100
            : 0;


        return view('YieldReport.YieldReport', compact(
            'from_date',
            'to_date',
            'totalProduction',
            'totalAdjustedConsumption',
            'totalWaste',
            'yieldLoss',
            'yieldPercent',
            'productionDetails',
            'consumptionDetails',
            'wasteDetails'
        ));
    }
}