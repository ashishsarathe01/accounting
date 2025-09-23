<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleOrderSetting;
use App\Models\ItemGroups;
use App\Models\ManageItems;
use App\Models\Units;
use App\Models\SaleOrder;
use App\Models\Accounts;
use Session;
use DB;


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

        $sales = $query->orderBy('id', 'desc')->get();

        return view('sale_order', compact('sales', 'from_date', 'to_date'));
    }

    /**
     * Show Add Sale Order page
     */
    public function create()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Get only items allowed in sale-order-settings
        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        $groups = ItemGroups::with(['items' => function($query) use ($company_id, $allowedItemIds) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1')
                  ->whereIn('id', $allowedItemIds);
        }])
        ->where('company_id', $company_id)
        ->get();

         $groups1 = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
      $groups1->push(3);
      $groups1->push(11);
      $party_list = Accounts::with('otherAddress')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups1)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get(); 
        // Get only units allowed in sale-order-settings
        $allowedUnitIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();

        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->whereIn('id', $allowedUnitIds)
                      ->get();

        return view('add_sale_order', compact('groups', 'units','party_list'));
    }

    /**
     * Store a new Sale Order
     */
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
        $items  = $request->item;
        $prices = $request->price;
        $units  = $request->unit;
        $gsms   = $request->gsm;
        $sizes  = $request->size;
        $reels  = $request->reel;

        $gsmIndex = 0;
        for($i = 0; $i < count($items); $i++) {
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

    /**
     * Show Sale Order Settings page
     */
    public function saleOrderSetting()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $groups = ItemGroups::with(['items' => function($query) use ($company_id) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1');
        }])->where('company_id', $company_id)->get();

        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->get();

        $selectedItems = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

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
