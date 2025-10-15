<?php

namespace App\Http\Controllers\deal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;
use App\Models\manage_deal_items;
use Session;

class DealController extends Controller
{


public function index()
{
    $company_id = Session::get('user_company_id');

    // Get all deals with account and items
    $deals = Deal::where('manage_deal.comp_id', $company_id)
        ->join('accounts', 'accounts.id', '=', 'manage_deal.party_id')
        ->select('manage_deal.*', 'accounts.account_name')
        ->with(['items' => function ($q) {
            $q->join('manage_items', 'manage_items.id', '=', 'manage_deal_items.item_id')
                ->select('manage_deal_items.*', 'manage_items.name');
        }])
        ->get();

    // Now calculate total, complete, pending & balance qty (without relationships or raw SQL)
    foreach ($deals as $deal) {

        if ($deal->deal_type == 'TON') {

            // ✅ 1. Get all sale orders linked to this deal
            $saleOrders = DB::table('sale_orders')
                ->where('deal_id', $deal->id)
                ->pluck('id'); // only IDs

            // ✅ 2. If sale orders exist, calculate quantities
            if ($saleOrders->count() > 0) {

                // Total quantity (sum of 'quantity' for all sale orders under this deal)
                $totalQty = DB::table('sale_order_item_gsm_sizes')
                    ->whereIn('sale_orders_id', $saleOrders)
                    ->sum('quantity');

                // Completed quantity (sum of 'sale_order_qty' where not null)
                $completeQty = DB::table('sale_order_item_gsm_sizes')
                    ->whereIn('sale_orders_id', $saleOrders)
                    ->whereNotNull('sale_order_qty')
                    ->sum('sale_order_qty');

                // Pending quantity (sum of 'quantity' where sale_order_qty is null)
                $pendingQty = DB::table('sale_order_item_gsm_sizes')
                    ->whereIn('sale_orders_id', $saleOrders)
                    ->whereNull('sale_order_qty')
                    ->sum('quantity');

            } else {
                $totalQty = 0;
                $completeQty = 0;
                $pendingQty = 0;
            }

            // ✅ 3. Balance = deal qty - total quantity used in sale orders
            $balanceQty = $deal->qty - $totalQty;

            // ✅ 4. Assign all values to the deal object
            $deal->total_quantity = $totalQty;
            $deal->total_complete = $completeQty;
            $deal->total_pending = $pendingQty;
            $deal->balance_qty = $balanceQty;

        } else {
            // We'll handle non-TON types later as you said
            $deal->total_quantity = 0;
            $deal->total_complete = 0;
            $deal->total_pending = 0;
            $deal->balance_qty = 0;
        }
    }

    return view('deal.index', compact('deals'));
}



    public function create()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $allowedItemIdsRaw = DB::select("
            SELECT item_id 
            FROM `sale-order-settings`
            WHERE company_id = ? AND setting_type = 'ITEM'
        ", [$company_id]);

        $allowedItemIds = collect($allowedItemIdsRaw)->pluck('item_id')->toArray();

        // Fetch only allowed items
        $items = DB::table('manage_items')
                    ->where('company_id', $company_id)
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->whereIn('id', $allowedItemIds)
                    ->get();

        $deal_types = ['TON', 'Vehicle'];
        $freights = ['Yes', 'No'];

        // Fetch party list
        $groups = DB::table('account_groups')
                    ->whereIn('heading', [3, 11])
                    ->where('heading_type', 'group')
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->where('company_id', $company_id)
                    ->pluck('id');
        $groups->push(3);
        $groups->push(11);

        $party_list = DB::table('accounts')
                        ->leftJoin('states', 'accounts.state', '=', 'states.id')
                        ->where('delete', '=', '0')
                        ->where('status', '=', '1')
                        ->whereIn('company_id', [$company_id, 0])
                        ->whereIn('under_group', $groups)
                        ->select('accounts.id', 'accounts.gstin', 'accounts.address', 'accounts.pin_code', 'accounts.account_name', 'states.state_code')
                        ->orderBy('account_name')
                        ->get();

        return view('deal.create', compact('party_list', 'items', 'deal_types', 'freights'));
    }

    public function store(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $user_id = auth()->user()->id ?? Session::get('user_id');

        // Validation
        $request->validate([
            'party_id' => 'required|exists:accounts,id',
            'type' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'freight' => 'nullable|string',
            'short_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:manage_items,id',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate a new deal number
            
            $dealNo = Deal::where('party_id', $request->party_id)->max('deal_no');

                    if ($dealNo) {
                        $dealNo = $dealNo + 1;
                    } else {
                        $dealNo = 1;
                    }

            // Insert into manage_deal
            $deal = Deal::create([
                'deal_no' => $dealNo,
                'deal_type' => $request->type,
                'qty' => strval($request->quantity),
                'party_id' => $request->party_id,
                'freight' => $request->freight,
                'comp_id' => $company_id,
                'status' => 0, // pending
                'final_complete' => 0,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'created_by' => $user_id,
            ]);

            // Insert related items into manage_deal_items
            foreach ($request->items as $item) {
                DB::table('manage_deal_items')->insert([
                    'manage_deal_id' => $deal->id,
                    'item_id' => $item['item_id'],
                    'rate' => $item['rate'],
                    'status' => 1, // active
                    'comp_id' => $company_id,
                    'created_by' => $user_id,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('deal.index')->with('success', 'Deal added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving deal: ' . $e->getMessage());
        }
    }


   public function edit($id)
{
    $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

    $deal = Deal::with(['items' => function($q){
        $q->join('manage_items', 'manage_items.id', '=', 'manage_deal_items.item_id')
          ->select('manage_deal_items.*', 'manage_items.name');
    }, 'party'])->findOrFail($id);

    $deal_types = ['TON', 'Vehicle'];
    $freights = ['Yes', 'No'];

    $allowedItemIds = DB::table('sale-order-settings')
                        ->where('company_id', $company_id)
                        ->where('setting_type', 'ITEM')
                        ->pluck('item_id')
                        ->toArray();

    $items = DB::table('manage_items')
                ->where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete', '0')
                ->whereIn('id', $allowedItemIds)
                ->get();

    $groups = DB::table('account_groups')
                ->whereIn('heading', [3,11])
                ->where('heading_type', 'group')
                ->where('status', '1')
                ->where('delete', '0')
                ->where('company_id', $company_id)
                ->pluck('id');
    $groups->push(3);
    $groups->push(11);

    $party_list = DB::table('accounts')
                    ->leftJoin('states', 'accounts.state', '=', 'states.id')
                    ->where('delete', '=', '0')
                    ->where('status', '=', '1')
                    ->whereIn('company_id', [$company_id, 0])
                    ->whereIn('under_group', $groups)
                    ->select('accounts.id', 'accounts.gstin', 'accounts.address', 'accounts.pin_code', 'accounts.account_name', 'states.state_code')
                    ->orderBy('account_name')
                    ->get();

    return view('deal.edit', compact('deal', 'items', 'party_list', 'deal_types', 'freights'));
}

public function update(Request $request, $id)
{
    $company_id = Session::get('user_company_id');
    $user_id = Session::get('user_id');

    $request->validate([
        'party_id' => 'required|exists:accounts,id',
        'type' => 'required|string',
        'quantity' => 'required|numeric|min:1',
        'freight' => 'nullable|string',
        'short_name' => 'nullable|string|max:255',
        'items' => 'required|array|min:1',
        'items.*.item_id' => 'required|exists:manage_items,id',
        'items.*.rate' => 'required|numeric|min:0',
    ]);

    try {
        DB::beginTransaction();

        $deal = Deal::findOrFail($id);

        $deal->update([
            'deal_type' => $request->type,
            'qty' => strval($request->quantity),
            'party_id' => $request->party_id,
            'freight' => $request->freight,
            'short_name' => $request->short_name,
            'updated_at' => now(),
            'updated_by' => $user_id,
        ]);

        // Delete old items
        DB::table('manage_deal_items')->where('manage_deal_id', $deal->id)->delete();

        // Insert new items
        foreach ($request->items as $item) {
            DB::table('manage_deal_items')->insert([
                'manage_deal_id' => $deal->id,
                'item_id' => $item['item_id'],
                'rate' => $item['rate'],
                'status' => 1,
                'comp_id' => $company_id,
                'created_by' => $user_id,
                'created_at' => now(),
            ]);
        }

        DB::commit();
        return redirect()->route('deal.index')->with('success', 'Deal updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error updating deal: ' . $e->getMessage());
    }
}



public function destroy($id)
{
    try {
        $saleOrder = DB::table('sale_orders')->where('deal_id', $id)->first();

        if ($saleOrder) {
            return response()->json(['status' => 'error', 'message' => 'Cannot delete: Sale Order already exists.']);
        }

        $deal = Deal::findOrFail($id);
        $deal->items()->delete();
        $deal->delete();

        return response()->json(['status' => 'success', 'message' => 'Deal deleted successfully.']);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Failed to delete: ' . $e->getMessage()]);
    }
}



    public function getDealsByParty(Request $request)
{
    $party_id = $request->party_id;
    $comp_id = Session::get('user_company_id');

    // Fetch pending deals for selected party
    $deals = Deal::where('party_id', $party_id)
        ->where('comp_id', $comp_id)
        ->where('status', 0) // pending
        ->select('id', 'deal_no',)
        ->get();

    return response()->json(['deals' => $deals]);
}

public function getDealDetails(Request $request)
{
    $deal_id = $request->deal_id;

    // Fetch deal freight and item details
    $deal = Deal::select('freight')->find($deal_id);

    $items = manage_deal_items::where('manage_deal_id', $deal_id)
        ->join('manage_items', 'manage_items.id', '=', 'manage_deal_items.item_id')
        ->select('manage_deal_items.item_id', 'manage_items.name as item_name', 'manage_deal_items.rate')
        ->get();

    return response()->json([
        'freight' => $deal ? $deal->freight : null,
        'items' => $items
    ]);
}


}