<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleOrderSetting;
use App\Models\ItemGroups;
use App\Models\ManageItems;
use App\Models\Units;
use App\Models\SaleOrder;
use Session;

class SaleOrderController extends Controller
{
    /**
     * Show list of sale orders
     */
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $from_date = $request->input('from_date');
        $to_date   = $request->input('to_date');

        $query = SaleOrder::where('company_id', $company_id);

        if ($from_date) {
            $query->whereDate('date', '>=', $from_date);
        }
        if ($to_date) {
            $query->whereDate('date', '<=', $to_date);
        }

        $sales = $query->orderBy('date', 'desc')->get();

        return view('sale_order', compact('sales', 'from_date', 'to_date'));
    }

    /**
     * Show Sale Order Settings page
     */
    public function saleOrderSetting()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Get groups with their items
        $groups = ItemGroups::with(['items' => function($query) use ($company_id) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1');
        }])
        ->where('company_id', $company_id)
        ->get();

        // Units
        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->get();

        // Selected items
        $selectedItems = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        // Selected units
        $selectedUnits = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();

        return view('saleOrderSetting', compact('groups', 'units', 'selectedItems', 'selectedUnits'));
    }

    /**
     * Update Sale Order Settings (items + units)
     */
    public function updateSaleOrderSettings(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // --- Handle Items ---
        $newItems = $request->input('items', []);
        $existingItems = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        // Insert newly checked items
        $toAdd = array_diff($newItems, $existingItems);
        foreach ($toAdd as $item_id) {
            SaleOrderSetting::create([
                'company_id'   => $company_id,
                'item_id'      => $item_id,
                'setting_type' => 'ITEM',
                'setting_for'  => 'SALE ORDER',
                'status'       => 1,
            ]);
        }

        // Remove unchecked items
        $toRemove = array_diff($existingItems, $newItems);
        if (!empty($toRemove)) {
            SaleOrderSetting::where('company_id', $company_id)
                ->where('setting_type', 'ITEM')
                ->whereIn('item_id', $toRemove)
                ->delete();
        }

        // --- Handle Units ---
        $newUnits = $request->input('units', []);
        $existingUnits = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();

        $toAddUnits = array_diff($newUnits, $existingUnits);
        foreach ($toAddUnits as $unit_id) {
            SaleOrderSetting::create([
                'company_id'   => $company_id,
                'item_id'      => $unit_id,
                'setting_type' => 'UNIT',
                'setting_for'  => 'SALE ORDER',
                'status'       => 1,
            ]);
        }

        $toRemoveUnits = array_diff($existingUnits, $newUnits);
        if (!empty($toRemoveUnits)) {
            SaleOrderSetting::where('company_id', $company_id)
                ->where('setting_type', 'UNIT')
                ->whereIn('item_id', $toRemoveUnits)
                ->delete();
        }

        return redirect()->route('sale-order.settings')
            ->with('success', 'Settings updated successfully.');
    }
}
