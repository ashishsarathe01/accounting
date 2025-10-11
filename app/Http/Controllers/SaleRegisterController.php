<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class SaleRegisterController extends Controller
{
     public function index(Request $request) {
    $company_id = Session::get('user_company_id');

    // Filters
    $from_date    = $request->input('from_date', date('Y-m-01'));
    $to_date      = $request->input('to_date', date('Y-m-d'));
    $series       = $request->input('series');
    $party        = $request->input('party');
    $material     = $request->input('material_center');
    $voucher_no   = $request->input('voucher_no');
    $itemFilter   = $request->input('item_id');
    $sundryFilter = $request->input('bill_sundry');

    // Base sales query
    $query = DB::table('sales as s')
        ->join('accounts','accounts.id','=','s.party')
        ->select(
            's.id','s.series_no','s.date','s.taxable_amt',
            'accounts.account_name as party',
            's.voucher_no_prefix as series','s.voucher_no',
            's.material_center','s.total'
        )
        ->where('s.company_id',$company_id)
        ->where('s.delete','0')
        ->where('s.status','1')
        ->whereBetween('s.date', [$from_date, $to_date]);

    if ($series) $query->where('s.series_no', $series);
    if ($party) $query->where('accounts.account_name', 'like', "%$party%");
    if ($material) $query->where('s.material_center', 'like', "%$material%");
    if ($voucher_no) $query->where('s.voucher_no', $voucher_no);

    // Item filter
    if ($itemFilter) {
        $query->whereIn('s.id', function($q) use ($itemFilter) {
            $q->select('sale_id')->from('sale_descriptions')
              ->where('goods_discription', $itemFilter);
        });
    }

    // Bill Sundry filter
    if ($sundryFilter) {
        $query->whereIn('s.id', function($q) use ($sundryFilter) {
            $q->select('sale_id')->from('sale_sundries')
              ->where('bill_sundry', $sundryFilter)
              ->where('amount','!=',0);
        });
    }

    $sales = $query->get();

    // Sundries for table
    $allSundriesRaw = DB::table('bill_sundrys')
        ->where('company_id', $company_id)
        ->where('delete','0')->where('status','1')
        ->select('id','name','nature_of_sundry')
        ->get();

    $otherSundries = $allSundriesRaw
        ->where('nature_of_sundry','OTHER')
        ->pluck('name')
        ->toArray();

    $fixedOrder = [
        ...$otherSundries,
        'Taxable Amt','CGST','SGST','IGST',
        'ROUNDED OFF (-)','ROUNDED OFF (+)',
    ];
    $allSundries = $fixedOrder;

    // Attach items + sundries
    $sales->map(function ($sale) use ($allSundries) {
        $sale->items = DB::table('sale_descriptions')
            ->where('sale_id', $sale->id)
            ->join('manage_items','manage_items.id','=','sale_descriptions.goods_discription')
            ->select('manage_items.name as item_name','qty','price','amount','goods_discription as id')
            ->get();

        $sundries = DB::table('sale_sundries as ss')
            ->join('bill_sundrys as bs','bs.id','=','ss.bill_sundry')
            ->where('ss.sale_id',$sale->id)
            ->select('bs.name','bs.nature_of_sundry','ss.amount')
            ->get();

        $mapped = [];
        foreach ($sundries as $s) {
            $mapped[$s->nature_of_sundry === 'OTHER' ? $s->name : $s->nature_of_sundry] = $s->amount;
        }
        $mapped['Taxable Amt'] = $sale->taxable_amt;

        foreach ($allSundries as $sundry) $sale->sundries[$sundry] = $mapped[$sundry] ?? 0;
        return $sale;
    });

    // Dropdown lists
    $seriesList   = DB::table('sales')->where('company_id',$company_id)->distinct()->pluck('series_no');
    $groups = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
      $groups->push(3);
      $groups->push(11);

    $partyList    = DB::table('accounts')->whereIn('company_id', [$company_id,0])->whereIn('under_group',$groups)->pluck('account_name');
    $materialList = DB::table('sales')->where('company_id',$company_id)->distinct()->pluck('material_center');
    $itemList     = DB::table('manage_items')->where('company_id',$company_id)->pluck('name','id');
    $sundryList   = $allSundriesRaw->pluck('name','id');

    $isItemFilter = !empty($itemFilter);

    return view('salesReport', compact(
        'sales','allSundries','seriesList','partyList','materialList',
        'itemList','sundryList','from_date','to_date','isItemFilter','itemFilter'
    ));
}

}
