<?php

namespace App\Http\Controllers\SparePartLifeChart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
// Models
use App\Models\ItemGroups;
use App\Models\SaleOrderSetting;

class SparePartLifeChartController extends Controller
{
    /**
     * Display Spare Part Items Group-wise
     */
    public function index()
    {
        // Get company id (same pattern you already use)
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $spareGroupIds = SaleOrderSetting::where('company_id', $company_id)
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'SPARE PART')
            ->pluck('item_id');

        $groups = ItemGroups::with(['items' => function ($query) use ($company_id) {
                $query->where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->orderBy('name');
            }])
            ->whereIn('id', $spareGroupIds)
            ->where('delete', '0')
            ->where('status', '1')
            ->orderBy('group_name')
            ->get();

        $groups = $groups->filter(function ($group) {
            return $group->items->count() > 0;
        });
        $savedItems = DB::table('part_life_chart_items')
            ->where('company_id', $company_id)
            ->pluck('item_id');
        return view('SparePartLifeChart.index', compact('groups', 'savedItems'));
    }

    public function store(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $items = $request->items ?? [];

        DB::transaction(function () use ($items, $company_id) {

            $existing = DB::table('part_life_chart_items')
                ->where('company_id', $company_id)
                ->get()
                ->keyBy('item_id');

            foreach ($items as $itemId => $data) {

                if (isset($data['selected'])) {

                    if (isset($existing[$itemId])) {

                        DB::table('part_life_chart_items')
                            ->where('id', $existing[$itemId]->id)
                            ->update([
                                'updated_at' => now(),
                            ]);

                        unset($existing[$itemId]);

                    } else {

                        DB::table('part_life_chart_items')->insert([
                            'company_id' => $company_id,
                            'item_id'    => $itemId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($existing->count()) {
                DB::table('part_life_chart_items')
                    ->whereIn('id', $existing->pluck('id'))
                    ->delete();
            }
        });

        return redirect()->back()->with('success', 'Part Life Chart updated successfully!');
    }

    public function viewLocations()
    {
        $companyId = Session::get('user_company_id');

        $locations = DB::table('part_life_locations as l')
            ->leftJoin('part_life_entries as e', function ($join) use ($companyId) {
                $join->on('l.id', '=', 'e.location_id')
                    ->where('e.company_id', '=', $companyId);
            })
            ->select(
                'l.*',
                DB::raw('COUNT(e.id) as usage_count')
            )
            ->where('l.company_id', $companyId)
            ->groupBy('l.id')
            ->orderBy('l.id', 'desc')
            ->get();

        return view('SparePartLifeChart.view_locations', compact('locations'));
    }
    public function addLocation()
    {
        return view('SparePartLifeChart.add_location');
    }
    public function storeLocation(Request $request)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
        ]);

        DB::table('part_life_locations')->insert([
            'company_id'   => Session::get('user_company_id'),
            'location_name'=> $request->location_name,
            'created_by'   => Session::get('user_id'),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()
            ->route('part-life.locations')
            ->with('success', 'Location added successfully');
    }
    public function editLocation($id)
    {
        $location = DB::table('part_life_locations')
            ->where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->first();

        if (!$location) {
            abort(404);
        }

        return view('SparePartLifeChart.edit_location', compact('location'));
    }
    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
        ]);

        $location = DB::table('part_life_locations')
            ->where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->first();

        if (!$location) {
            abort(404);
        }

        DB::table('part_life_locations')
            ->where('id', $id)
            ->update([
                'location_name' => $request->location_name,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('part-life.locations')
            ->with('success', 'Location updated successfully');
    }
    public function deleteLocation($id)
    {
        $companyId = Session::get('user_company_id');

        $location = DB::table('part_life_locations')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$location) {
            abort(404);
        }

        $isUsed = DB::table('part_life_entries')
            ->where('location_id', $id)
            ->where('company_id', $companyId)
            ->exists();

        if ($isUsed) {
            return redirect()
                ->route('part-life.locations')
                ->with('error', 'This location is already used in entries, cannot delete.');
        }

        DB::table('part_life_locations')
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('part-life.locations')
            ->with('success', 'Location deleted successfully');
    }

    public function viewBrands()
    {
        $companyId = Session::get('user_company_id');

        $brands = DB::table('part_life_brands as b')
            ->leftJoin('part_life_entries as e', function ($join) use ($companyId) {
                $join->on('b.id', '=', 'e.brand_id')
                    ->where('e.company_id', '=', $companyId);
            })
            ->select(
                'b.*',
                DB::raw('COUNT(e.id) as usage_count')
            )
            ->where('b.company_id', $companyId)
            ->groupBy('b.id')
            ->orderBy('b.id', 'desc')
            ->get();

        return view('SparePartLifeChart.view_brands', compact('brands'));
    }
    public function addBrand()
    {
        return view('SparePartLifeChart.add_brand');
    }
    public function storeBrand(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        DB::table('part_life_brands')->insert([
            'company_id' => Session::get('user_company_id'),
            'brand_name' => $request->brand_name,
            'created_by' => Session::get('user_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('part-life.brands')
            ->with('success', 'Brand added successfully');
    }

    public function editBrand($id)
    {
        $brand = DB::table('part_life_brands')
            ->where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->first();

        if (!$brand) {
            abort(404);
        }

        return view('SparePartLifeChart.edit_brand', compact('brand'));
    }
    public function updateBrand(Request $request, $id)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        $brand = DB::table('part_life_brands')
            ->where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->first();

        if (!$brand) {
            abort(404);
        }

        DB::table('part_life_brands')
            ->where('id', $id)
            ->update([
                'brand_name' => $request->brand_name,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('part-life.brands')
            ->with('success', 'Brand updated successfully');
    }
    public function deleteBrand($id)
    {
        $companyId = Session::get('user_company_id');

        $brand = DB::table('part_life_brands')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$brand) {
            abort(404);
        }

        $isUsed = DB::table('part_life_entries')
            ->where('brand_id', $id)
            ->where('company_id', $companyId)
            ->exists();

        if ($isUsed) {
            return redirect()
                ->route('part-life.brands')
                ->with('error', 'This brand is already used in entries, cannot delete.');
        }

        DB::table('part_life_brands')
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('part-life.brands')
            ->with('success', 'Brand deleted successfully');
    }
    public function viewEntries(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $query = DB::table('part_life_entries as ple')
            ->join('manage_items as mi', 'mi.id', '=', 'ple.item_id')
            ->join('part_life_brands as pb', 'pb.id', '=', 'ple.brand_id')
            ->leftJoin('part_life_locations as pl', 'pl.id', '=', 'ple.location_id')
            ->where('ple.company_id', $company_id);

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereBetween('ple.entry_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        if (!empty($request->item_id) && $request->item_id != 'all') {
            $query->where('ple.item_id', $request->item_id);
        }

        if (!empty($request->location_id) && $request->location_id != 'all') {
            $query->where('ple.location_id', $request->location_id);
        }

        $entries = $query->select(
                'ple.*',
                'mi.name as item_name',
                'pb.brand_name',
                'pl.location_name'
            )
            ->orderBy('ple.entry_group_id', 'desc')
            ->orderBy('ple.id', 'asc')
            ->get();

            $items = DB::table('part_life_chart_items as pci')
                ->join('manage_items as mi', 'mi.id', '=', 'pci.item_id')
                ->where('pci.company_id', $company_id)
                ->select('mi.id', 'mi.name')
                ->distinct()
                ->pluck('mi.name', 'mi.id');


            $locations = DB::table('part_life_locations')
                ->where('company_id', $company_id)
                ->pluck('location_name', 'id');

        return view('SparePartLifeChart.index_entries', compact('entries', 'items', 'locations'));
    }

    public function addEntry()
    {
        $company_id = Session::get('user_company_id');

        $items = DB::table('part_life_chart_items as pli')
        ->join('manage_items as mi', 'mi.id', '=', 'pli.item_id')
        ->leftJoin('units as u', 'u.id', '=', 'mi.u_name')
        ->where('pli.company_id', $company_id)
        ->where('mi.delete', '0')
        ->where('mi.status', '1')
        ->select(
            'mi.id',
            'mi.name',
            'mi.u_name',          
            'u.s_name as unit'
        )
        ->orderBy('mi.name')
        ->get();


        $brands = DB::table('part_life_brands')
            ->where('company_id', $company_id)
            ->orderBy('brand_name')
            ->get();

        $locations = DB::table('part_life_locations')
            ->where('company_id', $company_id)
            ->orderBy('location_name')
            ->get();

        return view('SparePartLifeChart.add_entry', compact('items', 'brands', 'locations'));
    }
    public function storeEntry(Request $request)
    {
        $company_id = Session::get('user_company_id');

        DB::transaction(function () use ($request, $company_id) {

            $entryDate = $request->entry_date;

            $entryGroupId = DB::table('part_life_entries')->max('entry_group_id') + 1;
            if (!$entryGroupId) {
                $entryGroupId = 1;
            }

            if (!empty($request->items)) {

                foreach ($request->items as $row) {

                    if (empty($row['item_id'])) continue;

                    DB::table('part_life_entries')->insert([
                        'entry_group_id' => $entryGroupId,
                        'company_id'     => $company_id,

                        'item_id'        => $row['item_id'],
                        'brand_id'       => $row['brand_id'] ?? null,
                        'location_id'    => $row['location_id'] ?? null,

                        'entry_date'     => $entryDate,

                        'qty'            => $row['qty'] ?? 0,

                        'unit_id' => $row['unit_id'] 
                            ?? DB::table('manage_items')
                                ->where('id', $row['item_id'])
                                ->value('u_name'),

                        'unit' => $row['unit'] ?? null,
                        'rate' => $row['rate'] ?? 0,

                        'replace_date' => null,
                        'duration'     => null,
                        'required_by'  => $row['required_by'] ?? null,
                        'reason'       => $row['reason'] ?? null,

                        'created_by' => Session::get('user_id'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->recalculatePartLife(
                        $row['item_id'],
                        $row['location_id'],
                        $company_id
                    );
                }
            }
        });

        return redirect()
            ->route('part-life.entries')
            ->with('success', 'Entries saved successfully!');
    }
    public function editEntry($group_id)
    {
        $company_id = Session::get('user_company_id');

        $items = DB::table('part_life_chart_items as pli')
            ->join('manage_items as mi', 'mi.id', '=', 'pli.item_id')
            ->leftJoin('units as u', 'u.id', '=', 'mi.u_name')
            ->where('pli.company_id', $company_id)
            ->where('mi.delete', '0')
            ->where('mi.status', '1')
            ->select(
                'mi.id',
                'mi.name',
                'mi.u_name',
                'u.s_name as unit'
            )
            ->orderBy('mi.name')
            ->get();

        $brands = DB::table('part_life_brands')
            ->where('company_id', $company_id)
            ->orderBy('brand_name')
            ->get();

        $locations = DB::table('part_life_locations')
            ->where('company_id', $company_id)
            ->orderBy('location_name')
            ->get();

        $entries = DB::table('part_life_entries')
            ->where('company_id', $company_id)
            ->where('entry_group_id', $group_id)
            ->orderBy('id')
            ->get();

        $entryDate = optional($entries->first())->entry_date;

        $itemsData = $entries;

        return view('SparePartLifeChart.edit_entry', compact(
            'items',
            'brands',
            'locations',
            'itemsData',
            'group_id',
            'entryDate'
        ));
    }
    public function updateEntry(Request $request, $group_id)
    {
        $company_id = Session::get('user_company_id');

        try {

            DB::transaction(function () use ($request, $company_id, $group_id) {

                $entryDate = $request->entry_date;

                $items = $request->items ?? [];

                $existingIds = DB::table('part_life_entries')
                    ->where('company_id', $company_id)
                    ->where('entry_group_id', $group_id)
                    ->pluck('id')
                    ->toArray();

                $submittedIds = [];
                $affected = [];

                foreach ($items as $row) {

                    if (
                        empty($row['item_id']) ||
                        empty($row['qty']) ||
                        empty($row['rate'])
                    ) continue;

                    $id = $row['id'] ?? null;

                    if ($id) {
                        $submittedIds[] = $id;
                    }

                    // track for recalculation
                    if (!empty($row['item_id']) && !empty($row['location_id'])) {
                        $affected[] = $row['item_id'] . '_' . $row['location_id'];
                    }

                    if ($id) {

                        DB::table('part_life_entries')
                            ->where('id', $id)
                            ->update([
                                'item_id'     => $row['item_id'],
                                'brand_id'    => $row['brand_id'] ?? null,
                                'location_id' => $row['location_id'] ?? null,
                                'entry_date'  => $entryDate,

                                'qty' => $row['qty'],
                                'unit_id' => $row['unit_id'] 
                                    ?? DB::table('manage_items')
                                        ->where('id', $row['item_id'])
                                        ->value('u_name'),

                                'unit' => $row['unit'] ?? '',
                                'rate' => $row['rate'],
                                'required_by' => $row['required_by'] ?? null,
                                'reason' => $row['reason'] ?? null,

                                'updated_at' => now(),
                            ]);

                    } else {

                        DB::table('part_life_entries')->insert([
                            'entry_group_id' => $group_id,
                            'company_id'     => $company_id,

                            'item_id'     => $row['item_id'],
                            'brand_id'    => $row['brand_id'] ?? null,
                            'location_id' => $row['location_id'] ?? null,

                            'entry_date' => $entryDate,

                            'qty' => $row['qty'],
                            'unit_id' => $row['unit_id'] 
                                ?? DB::table('manage_items')
                                    ->where('id', $row['item_id'])
                                    ->value('u_name'),

                            'unit' => $row['unit'] ?? '',
                            'rate' => $row['rate'],

                            'replace_date' => null,
                            'duration'     => null,
                            'required_by'  => $row['required_by'] ?? null,
                            'reason'       => $row['reason'] ?? null,

                            'created_by' => Session::get('user_id'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $toDelete = array_diff($existingIds, array_filter($submittedIds));

                if (!empty($toDelete)) {
                    DB::table('part_life_entries')
                        ->whereIn('id', $toDelete)
                        ->delete();
                }

                foreach (array_unique($affected) as $key) {

                    [$item_id, $location_id] = explode('_', $key);

                    $this->recalculatePartLife(
                        $item_id,
                        $location_id,
                        $company_id
                    );
                }

            });

            return redirect()
                ->route('part-life.entries')
                ->with('success', 'Entries updated successfully!');

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
    public function deleteEntry($group_id)
    {
        $company_id = Session::get('user_company_id');

        DB::transaction(function () use ($group_id, $company_id) {

            $entries = DB::table('part_life_entries')
                ->where('company_id', $company_id)
                ->where('entry_group_id', $group_id)
                ->get();

            $affected = [];

            foreach ($entries as $entry) {

                if ($entry->item_id && $entry->location_id) {
                    $affected[] = $entry->item_id . '_' . $entry->location_id;
                }
            }

            DB::table('part_life_entries')
                ->where('company_id', $company_id)
                ->where('entry_group_id', $group_id)
                ->delete();

            foreach (array_unique($affected) as $key) {

                [$item_id, $location_id] = explode('_', $key);

                $this->recalculatePartLife(
                    $item_id,
                    $location_id,
                    $company_id
                );
            }
        });

        return redirect()
            ->route('part-life.entries')
            ->with('success', 'Entries deleted successfully!');
    }


    public function checkDate(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $item_id     = $request->item_id;
        $location_id = $request->location_id;
        $entry_date  = $request->entry_date; 

        $exists = DB::table('part_life_entries')
            ->where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->where('location_id', $location_id)
            ->whereDate('entry_date', $entry_date)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Entry already exists for this Date + Part + Location'
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function getPartLifeItems(Request $request)
    {
        $group_id = $request->group_id;

        $rows = DB::table('part_life_entries')
            ->where('entry_group_id', $group_id)
            ->get();

        $items = [];

        foreach ($rows as $row) {
            if ($row->item_id && $row->qty) {
                $items[] = [
                    'item_id' => $row->item_id,
                    'qty'     => $row->qty,
                    'unit_id' => $row->unit_id,
                    'rate'    => $row->rate
                ];
            }
        }

        return response()->json([
            'entry_date' => $rows->first()->entry_date ?? date('Y-m-d'),
            'items' => $items
        ]);
    }
    private function recalculatePartLife($item_id, $location_id, $company_id)
    {
        $entries = DB::table('part_life_entries')
            ->where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->where('location_id', $location_id)
            ->orderBy('entry_date', 'asc')
            ->get();

        for ($i = 0; $i < count($entries); $i++) {

            $current = $entries[$i];

            if (isset($entries[$i + 1])) {

                $next = $entries[$i + 1];

                $duration = \Carbon\Carbon::parse($current->entry_date)
                    ->diffInDays($next->entry_date);

                DB::table('part_life_entries')
                    ->where('id', $current->id)
                    ->update([
                        'replace_date' => $next->entry_date,
                        'duration'     => $duration
                    ]);

            } else {

                DB::table('part_life_entries')
                    ->where('id', $current->id)
                    ->update([
                        'replace_date' => null,
                        'duration'     => null
                    ]);
            }
        }
    }
}