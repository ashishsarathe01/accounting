<?php

namespace App\Http\Controllers\Retail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\RetailRateChangeDate;
use App\Models\RetailItemRate;
use Session;

class ManageRateController extends Controller
{
        public function index(Request $request)
{
    $company_id = Session::get('user_company_id');

    // ✅ If filter applied → only that date
    if ($request->from_date && $request->to_date){
        $dates = DB::table('retail_rate_change_date')
            ->where('company_id', $company_id)
            ->whereBetween('date', [$request->from_date, $request->to_date])
            ->orderByDesc('time')
             ->orderByDesc('time')
            ->get();
    } else {
        // ✅ Last 10 latest dates
        $dates = DB::table('retail_rate_change_date')
            ->where('company_id', $company_id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(10)
            ->get();
    }

    // ✅ Get all items
    $items = DB::table('manage_items')
        ->where('delete', '0')
        ->where('status', '1')
        ->where('company_id', $company_id)
        ->orderBy('name')
        ->get();

    // ✅ Get all rates
    $rates = DB::table('retail_item_rate')
        ->whereIn('retail_rate_change_date_id', $dates->pluck('id'))
        ->get()
        ->groupBy('item_id');

    return view('retailManagement.index', compact('items', 'dates', 'rates'));
}

   public function create()
{
    $company_id = Session::get('user_company_id');

    // Get all items
    $items = DB::table('manage_items')
        ->where('delete', '0')
        ->where('status', '1')
        ->where('company_id', $company_id)
        ->orderBy('name')
        ->select('id','name')
        ->get();

    // 🔥 Get latest rate change (last entry)
    $latestRate = DB::table('retail_rate_change_date')
        ->where('company_id', $company_id)
        ->orderByDesc('date')
        ->orderByDesc('time')
        ->first();

    $rates = [];

    if ($latestRate) {
        // Get previous rates mapped [item_id => rate]
        $rates = DB::table('retail_item_rate')
            ->where('retail_rate_change_date_id', $latestRate->id)
            ->pluck('rate', 'item_id')
            ->toArray();
    }

    return view('retailManagement.create', compact('items', 'rates'));
}


   public function store(Request $request)
{
    // Validation
    $request->validate([
        'rate_date' => 'required|date',
        'rate_time' => 'required',
        'items' => 'required|array',
        'items.*.item_id' => 'required|integer',
        'items.*.price' => 'required|numeric',
    ]);

    $company_id = Session::get('user_company_id');

    $latest = DB::table('retail_rate_change_date')
    ->where('company_id', $company_id)
    ->orderByDesc('date')
    ->orderByDesc('time')
    ->first();

    if ($latest) {
        $selected = strtotime($request->rate_date . ' ' . $request->rate_time);
        $latestTime = strtotime($latest->date . ' ' . $latest->time);

        if ($selected <= $latestTime) {
            return back()->with('error', 'Date & Time must be greater than last entry');
        }
    }

    $company_id = Session::get('user_company_id');
    $user_id = Session::get('user_id');
    // Step 1: Insert into retail_rate_change_date
    $rateChange = new RetailRateChangeDate();
    $rateChange->date = $request->rate_date;
    $rateChange->time = $request->rate_time;
    $rateChange->company_id = $company_id;
    $rateChange->created_by = $user_id;
    $rateChange->status = 1;
    $rateChange->save();

    // Step 2: Insert item rates
    foreach ($request->items as $item) {

        // Skip empty rows (safety)
        if (empty($item['item_id']) || $item['price'] === null) {
            continue;
        }

        $rate = new RetailItemRate();
        $rate->retail_rate_change_date_id = $rateChange->id;
        $rate->item_id = $item['item_id'];
        $rate->rate = $item['price'];
        $rate->company_id = $company_id;
        $rate->status = 1;
        $rate->created_by = $user_id;
        $rate->save();
    }

    return redirect()->route('retail-item-rate.index')
       ->with('success', 'Rates Saved successfully!');
}
   

public function edit($id)
{
    $company_id = Session::get('user_company_id');

    $rateChange = RetailRateChangeDate::findOrFail($id);

    // Get all items
    $items = DB::table('manage_items')
        ->where('delete', '0')
        ->where('status', '1')
        ->where('company_id', $company_id)
        ->orderBy('name')
        ->select('id','name')
        ->get();

    // Get existing rates mapped [item_id => rate]
    $rates = RetailItemRate::where('retail_rate_change_date_id', $id)
                ->pluck('rate', 'item_id')
                ->toArray();

    return view('retailManagement.edit', compact('rateChange', 'items', 'rates'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'rate_date' => 'required|date',
        'rate_time' => 'required',
        'items' => 'required|array',
    ]);

    $company_id = Session::get('user_company_id');

    $latest = DB::table('retail_rate_change_date')
    ->where('company_id', $company_id)
    ->orderByDesc('date')
    ->orderByDesc('time')
    ->first();

    if ($latest) {
        $selected = strtotime($request->rate_date . ' ' . $request->rate_time);
        $latestTime = strtotime($latest->date . ' ' . $latest->time);

        if ($selected < $latestTime) {
            return back()->with('error', 'Date & Time must be greater than last entry');
        }
    }

    // Step 1: Update main record
    $rateChange = RetailRateChangeDate::findOrFail($id);
    $rateChange->date = $request->rate_date;
    $rateChange->time = $request->rate_time;
    $rateChange->updated_by = auth()->id() ?? 1;
    $rateChange->save();

    // Step 2: Delete old item rates
    RetailItemRate::where('retail_rate_change_date_id', $id)->delete();

    // Step 3: Insert new updated rates
    foreach ($request->items as $item) {

        if (empty($item['item_id']) || $item['price'] === null) {
            continue;
        }

        RetailItemRate::create([
            'retail_rate_change_date_id' => $id,
            'item_id' => $item['item_id'],
            'rate' => $item['price'],
            'company_id' => auth()->user()->company_id ?? 1,
            'status' => 1,
            'created_by' => auth()->id() ?? 1,
        ]);
    }

   return redirect()->route('retail-item-rate.index')
       ->with('success', 'Rates updated successfully!');
}

public function destroy($id)
{
    $company_id = Session::get('user_company_id');

    DB::beginTransaction();

    try {

        // Delete child records first
        DB::table('retail_item_rate')
            ->where('retail_rate_change_date_id', $id)
            ->where('company_id', $company_id)
            ->delete();

        // Delete parent record
        DB::table('retail_rate_change_date')
            ->where('id', $id)
            ->where('company_id', $company_id)
            ->delete();

        DB::commit();

        return redirect()->route('retail-item-rate.index')
            ->with('success', 'Rate record deleted successfully');

    } catch (\Exception $e) {

        DB::rollback();

        return redirect()->back()
            ->with('error', 'Something went wrong while deleting');
    }
}


public function checkLatestDateTime(Request $request)
{
    $company_id = Session::get('user_company_id');

    $latest = DB::table('retail_rate_change_date')
        ->where('company_id', $company_id)
        ->orderByDesc('date')
        ->orderByDesc('time')
        ->first();

    if (!$latest) {
        return response()->json(['status' => true]);
    }

    $selected = strtotime($request->date . ' ' . $request->time);
    $latestTime = strtotime($latest->date . ' ' . $latest->time);

    if ($selected > $latestTime) {
        return response()->json(['status' => true]);
    }

    return response()->json([
        'status' => false,
        'message' => 'Date & Time must be greater than last entry (' 
            . $latest->date . ' ' . $latest->time . ')'
    ]);
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

public function getItemRateByDate(Request $request)
{
    $company_id = Session::get('user_company_id');

    $date = $request->date;
    $item_id = $request->item_id;

    // 🔥 Get latest rate <= selected date
    $rate = DB::table('retail_rate_change_date as rcd')
        ->join('retail_item_rate as rir', 'rir.retail_rate_change_date_id', '=', 'rcd.id')
        ->where('rcd.company_id', $company_id)
        ->where('rir.item_id', $item_id)
        ->where('rcd.date', '<=', $date)
        ->orderByDesc('rcd.date')
        ->orderByDesc('rcd.time')
        ->select('rir.rate', 'rcd.date', 'rcd.time')
        ->first();

    if (!$rate) {
        return response()->json([
            'status' => false
        ]);
    }

    return response()->json([
        'status' => true,
        'price_with_gst' => $rate->rate
    ]);
}
}
