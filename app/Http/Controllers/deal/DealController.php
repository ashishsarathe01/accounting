<?php

namespace App\Http\Controllers\deal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;
use Session;

class DealController extends Controller
{
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

            return redirect()->route('deal.create')->with('success', 'Deal added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving deal: ' . $e->getMessage());
        }
    }
}
