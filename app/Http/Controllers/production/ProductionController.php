<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionItem;
use App\Models\ItemGroups;
use App\Models\SaleOrderSetting;
use Illuminate\Support\Facades\Session;

class ProductionController extends Controller
{
    /**
     * Display all saved set items for this company
     */
    public function setItems()
    {
        $company_id = Session::get('user_company_id');

        $setItems = ProductionItem::where('company_id', $company_id)
                                  ->with('item') // eager load item details
                                  ->get();

        return view('production.set_item', compact('setItems'));
    }

    /**
     * Show the add set item page with only allowed and not yet added items
     */
    public function create()
    {
        $company_id = Session::get('user_company_id');

        $addedItemIds = ProductionItem::where('company_id', $company_id)->pluck('item_id')->toArray();

        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        $availableItemIds = array_diff($allowedItemIds, $addedItemIds);

        $groups = ItemGroups::where('company_id', $company_id)
            ->with(['items' => function($query) use ($availableItemIds) {
                $query->whereIn('id', $availableItemIds)
                      ->where('status', '1');
            }])
            ->get()
            ->filter(fn($group) => $group->items->count() > 0);

        return view('production.add_set_item', compact('groups'));
    }

    /**
     * Store a new set item
     */
    public function store(Request $request)
    {
        $company_id = Session::get('user_company_id');
        $created_by = Session::get('user_id');

        $request->validate([
            'item_id' => 'required|integer',
            'bf' => 'required|integer',
            'gsm' => 'required|integer',
            'speed' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        // Prevent duplicate item
        $exists = ProductionItem::where('company_id', $company_id)
                    ->where('item_id', $request->item_id)
                    ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Item already added.');
        }

        ProductionItem::create([
            'item_id' => $request->item_id,
            'bf' => $request->bf,
            'gsm' => $request->gsm,
            'speed' => $request->speed,
            'status' => intval($request->status),
            'company_id' => $company_id,
            'created_by' => $created_by,
            'created_at' => now(),
        ]);

        return redirect()->route('production.set_item')->with('success', 'Item added successfully.');
    }

    /**
     * Edit set item
     */
    public function edit($id)
    {
        $company_id = Session::get('user_company_id');
        $item = ProductionItem::findOrFail($id);

        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        // Exclude items already added except the current one
        $addedItemIds = ProductionItem::where('company_id', $company_id)
                                ->where('id', '!=', $id)
                                ->pluck('item_id')
                                ->toArray();

        $availableItemIds = array_diff($allowedItemIds, $addedItemIds);

        $groups = ItemGroups::where('company_id', $company_id)
            ->with(['items' => function($query) use ($availableItemIds) {
                $query->whereIn('id', $availableItemIds)
                      ->where('status', '1');
            }])
            ->get()
            ->filter(fn($group) => $group->items->count() > 0);

        return view('production.edit_set_item', compact('item', 'groups'));
    }

    /**
     * Update set item
     */
    public function update(Request $request, $id)
    {
        $company_id = Session::get('user_company_id');
        $item = ProductionItem::findOrFail($id);

        $request->validate([
            'item_id' => 'required|integer',
            'bf' => 'required|integer',
            'gsm' => 'required|integer',
            'speed' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        // Prevent duplicate item except current
        $exists = ProductionItem::where('company_id', $company_id)
                    ->where('item_id', $request->item_id)
                    ->where('id', '!=', $id)
                    ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Item already added.');
        }

        $item->update([
            'item_id' => $request->item_id,
            'bf' => $request->bf,
            'gsm' => $request->gsm,
            'speed' => $request->speed,
            'status' => intval($request->status),
            'updated_at' => now(),
        ]);

        return redirect()->route('production.set_item')->with('success', 'Item updated successfully.');
    }

    /**
     * Delete set item
     */
    public function destroy($id)
    {
        $item = ProductionItem::find($id);
        if ($item) {
            $item->delete();
            return redirect()->route('production.set_item')->with('success', 'Item deleted successfully.');
        }
        return redirect()->route('production.set_item')->with('error', 'Item not found.');
    }
}
