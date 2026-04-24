<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\Helpers\CommonHelper;

class PurchaseRegisterController extends Controller
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
    $exportCsv = $request->get('export') === 'csv';
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
    'freight'         => 'FREIGHT & FORWARD CHARGE',
    'discount'        => 'DISCOUNT',
    'taxable'         => 'Taxable Amt',
    'cgst'            => 'CGST',
    'sgst'            => 'SGST',
    'igst'            => 'IGST',
    'roff_neg'        => 'ROUNDED OFF (-)',
    'roff_pos'        => 'ROUNDED OFF (+)',
];
$selectedColumns = $request->input('columns', array_keys($availableColumns));

    // Base sales query
    $query = DB::table('purchases as s')
        ->join('accounts','accounts.id','=','s.party')
        ->select(
            's.id','s.series_no','s.date','s.taxable_amt',
            'accounts.account_name as party',
            's.series_no as series','s.voucher_no',
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
            $q->select('purchase_id')->from('purchase_descriptions')
              ->where('goods_discription', $itemFilter);
        });
    }

    // Bill Sundry filter
    if ($sundryFilter) {
        $query->whereIn('s.id', function($q) use ($sundryFilter) {
            $q->select('purchase_id')->from('purchase_sundries')
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
        $sale->items = DB::table('purchase_descriptions')
            ->where('purchase_id', $sale->id)
            ->join('manage_items','manage_items.id','=','purchase_descriptions.goods_discription')
            ->select('manage_items.name as item_name','qty','price','amount','goods_discription as id')
            ->get();

        $sundries = DB::table('purchase_sundries as ss')
            ->join('bill_sundrys as bs','bs.id','=','ss.bill_sundry')
            ->where('ss.purchase_id',$sale->id)
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
    if ($exportCsv) {

    $filename = 'purchase_report_' . date('d-m-Y') . '.csv';

    return response()->stream(function () use ($sales, $selectedColumns, $availableColumns) {

        $handle = fopen('php://output', 'w');

        /* ---------------- HEADER ---------------- */
        $header = [];
        foreach ($selectedColumns as $col) {
            $header[] = $availableColumns[$col];
        }
        fputcsv($handle, $header);

        /* ---------------- TOTALS INIT ---------------- */
        $totalQty = 0;
        $totalAmount = 0;
        $totalTotal = 0;
        $sundryTotals = [];

        /* ---------------- ROWS ---------------- */
        foreach ($sales as $sale) {

            $firstItem = true;

            foreach ($sale->items as $item) {

                $row = [];

                foreach ($selectedColumns as $col) {

                    switch ($col) {

                        case 'series':
                            $row[] = $sale->series_no;
                            break;

                        case 'date':
                            $row[] = $sale->date;
                            break;

                        case 'voucher_no':
                            $row[] = $sale->voucher_no;
                            break;

                        case 'party':
                            $row[] = $sale->party;
                            break;

                        case 'material_center':
                            $row[] = $sale->material_center;
                            break;

                        case 'total':
                            $row[] = $firstItem ? $sale->total : '';
                            if ($firstItem) $totalTotal += $sale->total;
                            break;

                        case 'item':
                            $row[] = $item->item_name;
                            break;

                        case 'qty':
                            $row[] = $item->qty;
                            $totalQty += $item->qty;
                            break;

                        case 'price':
                            $row[] = $item->price;
                            break;

                        case 'amount':
                            $row[] = $item->amount;
                            $totalAmount += $item->amount;
                            break;

                        default:
                            // Sundries
                            $label = $availableColumns[$col];

                            if ($firstItem) {
                                $val = $sale->sundries[$label] ?? 0;
                                $row[] = $val;
                                $sundryTotals[$label] = ($sundryTotals[$label] ?? 0) + $val;
                            } else {
                                $row[] = '';
                            }
                    }
                }

                fputcsv($handle, $row);
                $firstItem = false;
            }
        }

        /* ---------------- TOTAL ROW ---------------- */
        $footer = [];

        foreach ($selectedColumns as $col) {

            switch ($col) {

                case 'qty':
                    $footer[] = $totalQty;
                    break;

                case 'amount':
                    $footer[] = $totalAmount;
                    break;

                case 'total':
                    $footer[] = $totalTotal;
                    break;

                default:
                    $label = $availableColumns[$col] ?? null;
                    $footer[] = $label && isset($sundryTotals[$label])
                        ? $sundryTotals[$label]
                        : '';
            }
        }

        fputcsv($handle, $footer);

        fclose($handle);

    }, 200, [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename={$filename}"
    ]);
}



    // Dropdown lists
    $seriesList   = DB::table('purchases')->where('company_id',$company_id)->distinct()->pluck('series_no');

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
    $materialList = DB::table('purchases')->where('company_id',$company_id)->distinct()->pluck('material_center');
    $itemList     = DB::table('manage_items')->where('company_id',$company_id)->pluck('name','id');
    $sundryList   = $allSundriesRaw->pluck('name','id');

    $isItemFilter = !empty($itemFilter);

    return view('PurchaseReport', compact(
        'sales','allSundries','seriesList','partyList','materialList',
        'itemList','sundryList','from_date','to_date','isItemFilter','itemFilter','availableColumns',
'selectedColumns'
    ));
}

}
