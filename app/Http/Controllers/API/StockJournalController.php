<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\ItemGroups;
use App\Models\Units;
use App\Models\item_gst_rate;
use App\Models\ItemLedger;
use App\Models\StockJournal;
use App\Models\StockJournalDetail;
use App\Models\Companies;
use App\Models\VoucherSeriesConfiguration;
use App\Models\GstBranch;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemAverageDetail;
use App\Models\SubItem;
use App\Models\ActivityLog;
use App\Models\MerchantModuleMapping;
use App\Models\ProductionItem;
use App\Models\ItemSizeStock;
use App\Models\DeckleProcess;
use App\Models\Consumption;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Gate;

class StockJournalController extends Controller
{
    public function index(Request $request)
{
    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;
    $from_date      = $request->from_date;
    $to_date        = $request->to_date;

    if (!$company_id || !$financial_year) {
        return response()->json([
            'status'  => false,
            'message' => 'company_id and financial_year are required'
        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Year Month Array
    |--------------------------------------------------------------------------
    */
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04', $from.'-05', $from.'-06', $from.'-07',
        $from.'-08', $from.'-09', $from.'-10', $from.'-11',
        $from.'-12', $to.'-01', $to.'-02', $to.'-03'
    ];

    /*
    |--------------------------------------------------------------------------
    | Subquery for Size Status
    |--------------------------------------------------------------------------
    */
    $sizeStatusSub = DB::table('item_size_stocks')
        ->select(
            'sj_generated_detail_id',
            DB::raw('MIN(status) as size_status')
        )
        ->groupBy('sj_generated_detail_id');

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */
    $query = DB::table('stock_journal_detail')
        ->leftJoin('manage_items', 'stock_journal_detail.consume_item', '=', 'manage_items.id')
        ->leftJoin('manage_items as new', 'stock_journal_detail.new_item', '=', 'new.id')
        ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
        ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
        ->leftJoinSub($sizeStatusSub, 'iss', function ($join) {
            $join->on('stock_journal_detail.id', '=', 'iss.sj_generated_detail_id');
        })
        ->join('stock_journal', 'stock_journal_detail.parent_id', '=', 'stock_journal.id')
        ->select(
            'stock_journal_detail.parent_id as id',
            'stock_journal_detail.id as detail_id',
            'journal_date',
            'consume_weight',
            'new_weight',
            'manage_items.name as consume_item',
            'units.s_name as consume_unit',
            'new_unit.s_name as new_unit',
            'new.name as new_item',
            'consume_price',
            'consume_amount',
            'new_price',
            'new_amount',
            'consumption_entry_status',
            DB::raw('COALESCE(iss.size_status, 1) as size_status')
        )
        ->where('stock_journal_detail.status', 1)
        ->where('stock_journal_detail.company_id', $company_id);

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('journal_date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('journal_date', 'ASC')
              ->orderBy('stock_journal.id', 'ASC');

    } else {

        $query->orderBy('journal_date', 'DESC')
              ->limit(10);
    }

  $journalCollection = $query->get();

if (empty($from_date) && empty($to_date)) {
    $journalCollection = $journalCollection->reverse()->values();
}

/*
|--------------------------------------------------------------------------
| Group By Stock Journal (parent_id)
|--------------------------------------------------------------------------
*/

$journal = $journalCollection
    ->groupBy('id')
    ->map(function ($rows) {

        return [
            'id' => $rows->first()->id,
            'journal_date' => $rows->first()->journal_date,
            'details' => $rows->map(function ($row) {
                return [
                    'detail_id' => $row->detail_id,
                    'consume_weight' => $row->consume_weight,
                    'new_weight' => $row->new_weight,
                    'consume_item' => $row->consume_item,
                    'consume_unit' => $row->consume_unit,
                    'new_unit' => $row->new_unit,
                    'new_item' => $row->new_item,
                    'consume_price' => $row->consume_price,
                    'consume_amount' => $row->consume_amount,
                    'new_price' => $row->new_price,
                    'new_amount' => $row->new_amount,
                    'consumption_entry_status' => $row->consumption_entry_status,
                    'size_status' => $row->size_status,
                ];
            })->values()
        ];
    })
    ->values();


    /*
    |--------------------------------------------------------------------------
    | Hide Delete Logic
    |--------------------------------------------------------------------------
    */
   $hideDeleteFor = $journalCollection
    ->filter(function ($row) {
        return $row->size_status == 0;
    })
    ->pluck('id')
    ->unique()
    ->values();


    /*
    |--------------------------------------------------------------------------
    | Return JSON
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'          => 200,
        'message'         => 'Stock Journal fetched successfully',
        'month_arr'       => $month_arr,
        'from_date'       => $from_date,
        'to_date'         => $to_date,
        'total_records' => $journal->count(),
        'hide_delete_for' => $hideDeleteFor,
        'data'            => $journal
    ]);
}

}
