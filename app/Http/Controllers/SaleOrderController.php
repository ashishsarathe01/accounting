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

        $sales = $query->where('company_id', $company_id)->orderBy('id', 'desc')->get();
      

        return view('sale_order', compact('sales', 'from_date', 'to_date'));
    }

    public function create()
    {
        return view('add_sale_order');
    }


     public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_to' => 'required',
            'ship_to' => 'required',
            'item.*'  => 'required',
            'price.*' => 'required|numeric',
            'unit.*'  => 'required',
            'gsm.*'   => 'required',
            'size.*'  => 'required',
            'reel.*'  => 'required|numeric',
        ]);

        $saleOrder = new SaleOrder();
        $saleOrder->bill_to = $request->bill_to;
        $saleOrder->ship_to = $request->ship_to;
        $saleOrder->deal    = $request->deal;
        $saleOrder->company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $saleOrder->save();

        // Save Items
        $items = $request->item;
        $prices = $request->price;
        $units = $request->unit;
        $gsms = $request->gsm;
        $sizes = $request->size;
        $reels = $request->reel;

        $gsmIndex = 0;
        for($i = 0; $i < count($items); $i++) {
            // Each item
            $saleOrder->items()->create([
                'item_id' => $items[$i],
                'price'   => $prices[$i],
                'unit_id' => $units[$i],
                'gsm'     => $gsms[$gsmIndex],
                'size'    => $sizes[$gsmIndex],
                'reel'    => $reels[$gsmIndex],
            ]);
            $gsmIndex++;
        }

        return redirect()->route('sale-order.index')->with('success', 'Sale order added successfully!');
    }

    // Sale Order List
 
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
