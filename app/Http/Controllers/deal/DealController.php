<?php

namespace App\Http\Controllers\deal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Deal;
use Session;

class DealController extends Controller
{
    public function create()
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Fetch allowed items for company
        $allowedItemIds = DB::table('sale_order_setting')
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

        $deal_types = ['TON', 'Vehicle'];
        $freights = ['Yes', 'No'];

        // Fetch party list
        $groups = DB::table('account_groups')
                    ->whereIn('heading', [3,11])
                    ->where('heading_type','group')
                    ->where('status','1')
                    ->where('delete','0')
                    ->where('company_id',Session::get('user_company_id'))
                    ->pluck('id');
        $groups->push(3);
        $groups->push(11);

        $party_list = DB::table('accounts')
                        ->leftJoin('states','accounts.state','=','states.id')
                        ->where('delete', '=', '0')
                        ->where('status', '=', '1')
                        ->whereIn('company_id', [Session::get('user_company_id'),0])
                        ->whereIn('under_group',$groups)
                        ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
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

     

       
            $dealId = new Deal;
            $dealId->deal_no  =>  $request->input('deal_no'); $deal_no,
                'deal_type' => $request->type,
                'qty' => strval($request->quantity),
                'party_id' => $request->party_id,
                'freight' => $request->freight,
                'comp_id' => $company_id,
                'status' => 0, // pending
                'final_complete' => 0,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'created_by' => $user_id,
           

            // Insert each item into manage_deal_items
            foreach ($request->items as $item) {
                DB::table('manage_deal_items')->insert([
                    'manage_deal_id' => $dealId,
                    'item_id' => $item['item_id'],
                    'rate' => $item['rate'],
                    'status' => 1, // active
                    'comp_id' => $company_id,
                    'created_by' => $user_id,
                    // created_at auto-handled by table
                ]);
            }

            return redirect()->route('deal.create')->with('success', 'Deal added successfully.');

      
    }
}
