<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\Helpers\CommonHelper;
use Str;
class SaleRegisterController extends Controller
{
     public function index(Request $request) {
    $company_id = Session::get('user_company_id');

    // Filters
    $from_date    = $request->input('from_date');
    $to_date      = $request->input('to_date');
    $series       = $request->input('series');
    $party        = $request->input('party');
    $material     = $request->input('material_center');
    $voucher_no   = $request->input('voucher_no');
    $itemFilter   = $request->input('item_id');
    $sundryFilter = $request->input('bill_sundry');
    $availableColumns = [
        'series'          => 'Series',
        'date'            => 'Date',
        'voucher_no'      => 'Voucher No',
        'party'           => 'Party',
        'material_center' => 'Material Center',
        'total'           => 'Total',
        'item'            => 'Item',
        'qty'             => 'Qty',
        'price'           => 'Price',
        'amount'          => 'Amount',
    ];
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
    // Add Bill Sundry columns dynamically
    foreach ($allSundries as $sundry) {
        $availableColumns['sundry_' . $sundry] = $sundry;
    }
    $selectedColumns = $request->input('columns', array_keys($availableColumns));
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
        ->where('s.status','1');
        if ($from_date && $to_date) {
            // Date passed → apply filter
            $query->whereBetween('s.date', [$from_date, $to_date]);
        } else {
            // Date not passed → fetch last 10 records
            $query->orderBy('s.date', 'desc');
            $query->orderBy('s.id', 'desc')
                  ->limit(10);
        }

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

       foreach ($sundries as $s) {
        
            // Determine key (CGST, SGST, IGST or OTHER sundries)
            $key = ($s->nature_of_sundry === 'OTHER') ? $s->name : $s->nature_of_sundry;
        
            // SUM values instead of overwriting
            if (!isset($mapped[$key])) {
                $mapped[$key] = 0;
            }
        
            $mapped[$key] += $s->amount;
        }
        
        $mapped['Taxable Amt'] = $sale->taxable_amt;


        foreach ($allSundries as $sundry) $sale->sundries[$sundry] = $mapped[$sundry] ?? 0;
        return $sale;
        
        $mapped = [];

        
    });

    // Dropdown lists
    $seriesList   = DB::table('sales')->where('company_id',$company_id)->distinct()->pluck('series_no');
   $top_groups = [3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $all_groups = array_unique($all_groups);

    $partyList    = DB::table('accounts')->whereIn('company_id', [$company_id,0])->whereIn('under_group',$all_groups)->pluck('account_name');
    $materialList = DB::table('sales')->where('company_id',$company_id)->distinct()->pluck('material_center');
    $itemList     = DB::table('manage_items')->where('company_id',$company_id)->pluck('name','id');
    $sundryList   = $allSundriesRaw->pluck('name','id');

    $isItemFilter = !empty($itemFilter);
    if ($request->get('download') === 'csv') {
        return $this->downloadSalesCsv(
            $sales,
            $selectedColumns,
            $availableColumns,
            $allSundries
        );
    }

    return view('salesReport', compact(
        'sales','allSundries','seriesList','partyList','materialList',
        'itemList','sundryList','from_date','to_date','isItemFilter','itemFilter','availableColumns',
    'selectedColumns',
    ));
}
private function downloadSalesCsv($sales, $selectedColumns, $availableColumns, $allSundries)
{
    $fileName = 'sales_report_' . date('Ymd_His') . '.csv';

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
    ];

    $callback = function () use ($sales, $selectedColumns, $availableColumns, $allSundries) {

        $file = fopen('php://output', 'w');

        /* ---------------- CSV HEADER ---------------- */
        $header = [];
        foreach ($selectedColumns as $col) {
            $header[] = $availableColumns[$col] ?? '';
        }
        fputcsv($file, $header);

        /* ---------------- TOTALS INIT ---------------- */
        $totalQty = 0;
        $totalAmount = 0;
        $totalAmountValue = 0;
        $sundryTotals = [];

        foreach ($allSundries as $s) {
            $sundryTotals[$s] = 0;
        }

        /* ---------------- CSV ROWS ---------------- */
        foreach ($sales as $sale) {

            $isFirstItem = true;

            foreach ($sale->items as $item) {

                $row = [];

                foreach ($selectedColumns as $col) {

                    switch (true) {

                        case $col === 'series':
                            $row[] = $sale->series_no;
                            break;

                        case $col === 'date':
                            $row[] = $sale->date;
                            break;

                        case $col === 'voucher_no':
                            $row[] = $sale->voucher_no;
                            break;

                        case $col === 'party':
                            $row[] = $sale->party;
                            break;

                        case $col === 'material_center':
                            $row[] = $sale->material_center;
                            break;

                        case $col === 'total':
                            $row[] = $isFirstItem ? $sale->total : '';
                            $totalAmountValue+= $isFirstItem ? $sale->total : 0;
                            break;

                        case $col === 'item':
                            $row[] = $item->item_name;
                            break;

                        case $col === 'qty':
                            $row[] = $item->qty;
                            $totalQty += $item->qty;
                            break;

                        case $col === 'price':
                            $row[] = $item->price;
                            break;

                        case $col === 'amount':
                            $row[] = $item->amount;
                            $totalAmount += $item->amount;
                            break;

                        case Str::startsWith($col, 'sundry_'):
                            $key = str_replace('sundry_', '', $col);

                            if ($isFirstItem) {
                                $val = $sale->sundries[$key] ?? 0;
                                $row[] = $val;
                                $sundryTotals[$key] += $val;
                            } else {
                                $row[] = '';
                            }
                            break;

                        default:
                            $row[] = '';
                    }
                }

                fputcsv($file, $row);
                $isFirstItem = false;
            }
        }

        /* ---------------- TOTAL ROW ---------------- */
        $totalRow = [];

        foreach ($selectedColumns as $col) {

            if ($col === 'qty') {
                $totalRow[] = $totalQty;

            } elseif ($col === 'amount') {
                $totalRow[] = $totalAmount;
            } elseif ($col === 'total') {
                $totalRow[] = $totalAmountValue;
            } elseif (Str::startsWith($col, 'sundry_')) {
                $key = str_replace('sundry_', '', $col);
                $totalRow[] = $sundryTotals[$key] ?? 0;

            } else {
                $totalRow[] = '';
            }
        }

        fputcsv($file, $totalRow);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}



}
