<?php

namespace App\Http\Controllers\BoxManagement; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Accounts;
use App\Models\ManageItems;
use Session;

class PartyItemRateController extends Controller
{
    
    public function index(Request $request)
{
    $company_id = Session::get('user_company_id');

    // Get Parties who have rates
    $query = DB::table('party_item_rates as pir')
        ->join('accounts as a', 'a.id', '=', 'pir.party_id')
        ->select(
            'pir.party_id',
            'a.account_name',
            DB::raw('COUNT(pir.item_id) as total_items'),
            DB::raw('MAX(pir.updated_at) as last_updated')
        )
        ->where('pir.company_id', $company_id)
        ->groupBy('pir.party_id', 'a.account_name');

    // Filter by party
    if ($request->party_id) {
        $query->where('pir.party_id', $request->party_id);
    }

    $data = $query->orderBy('a.account_name')->get();

    // Party list for filter dropdown
    $party_list = Accounts::where('delete', '0')
        ->where('status', '1')
        ->whereIn('company_id', [$company_id, 0])
        ->select('id','account_name')
        ->orderBy('account_name')
        ->get();

    return view('BoxManagement.PartyItemRate.index', compact('data','party_list'));
}

    public function create()
    {
        $top_groups = [3, 11];

        $all_groups = [];
        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id'))
            );
        }

        $groups = array_unique($all_groups);

        $party_list = Accounts::with('otherAddress')
            ->leftJoin('states','accounts.state','=','states.id')
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [Session::get('user_company_id'),0])
            ->whereIn('under_group',$groups)
            ->select('accounts.id','accounts.account_name')
            ->orderBy('account_name')
            ->get();

        $items = DB::table('manage_items')
            ->where('delete', '0')
            ->where('status', '1')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('name')
            ->select('id','name')
            ->get();

        return view('BoxManagement.PartyItemRate.create', compact('party_list','items'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'required',
            'items.*.item_id' => 'required',
            'items.*.price' => 'required|numeric'
        ]);

        $company_id = Session::get('user_company_id');
        $user_id = Session::get('user_id');

        foreach ($request->items as $row) {

            DB::table('party_item_rates')->updateOrInsert(
                [
                    'party_id' => $request->party_id,
                    'item_id' => $row['item_id'],
                    'company_id' => $company_id
                ],
                [
                    'price' => $row['price'],
                    'user_id' => $user_id,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }

        return redirect()->route('party-item-rate.index')->with('success','Rates saved successfully');
    }
    
    public function edit($party_id)
{
    $company_id = Session::get('user_company_id');

    // Party list (same as create)
    $party_list = Accounts::where('delete', '0')
        ->where('status', '1')
        ->whereIn('company_id', [$company_id, 0])
        ->select('id','account_name')
        ->orderBy('account_name')
        ->get();

    // Item list
    $items = DB::table('manage_items')
        ->where('delete', '0')
        ->where('status', '1')
        ->where('company_id', $company_id)
        ->orderBy('name')
        ->select('id','name')
        ->get();

    // Existing saved rates
    $rates = DB::table('party_item_rates')
        ->where('company_id', $company_id)
        ->where('party_id', $party_id)
        ->get();

    return view('BoxManagement.PartyItemRate.edit', compact('party_list','items','rates','party_id'));
}

public function update(Request $request, $party_id)
{
    $request->validate([
        'items.*.item_id' => 'required',
        'items.*.price' => 'required|numeric'
    ]);

    $company_id = Session::get('user_company_id');
    $user_id = Session::get('user_id');

    // DELETE OLD DATA (simple approach)
    DB::table('party_item_rates')
        ->where('party_id', $party_id)
        ->where('company_id', $company_id)
        ->delete();

    // INSERT NEW
    foreach ($request->items as $row) {
        DB::table('party_item_rates')->insert([
            'party_id'   => $party_id,
            'item_id'    => $row['item_id'],
            'price'      => $row['price'],
            'company_id' => $company_id,
            'user_id'    => $user_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    return redirect()->route('party-item-rate.index')->with('success','Updated successfully');
}

public function destroy($party_id)
{
    $company_id = Session::get('user_company_id');

    DB::table('party_item_rates')
        ->where('party_id', $party_id)
        ->where('company_id', $company_id)
        ->delete();

    return redirect()->route('party-item-rate.index')
        ->with('success', 'Party rates deleted successfully');
}

public function getPrice(Request $request)
{
    $company_id = Session::get('user_company_id');

    $data = DB::table('party_item_rates')
        ->where('company_id', $company_id)
        ->where('party_id', $request->party_id)
        ->where('item_id', $request->item_id)
        ->first();

    if ($data) {
        return response()->json([
            'status' => true,
            'price' => $data->price
        ]);
    }

    return response()->json([
        'status' => false
    ]);
}
}