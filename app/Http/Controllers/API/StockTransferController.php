<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\ManageItems;
use App\Models\StockTransfer;
use App\Models\StockTransferDescription;
use App\Models\StockTransferSundry;
use App\Models\AccountLedger;
use App\Models\VoucherSeriesConfiguration;
use App\Models\ItemLedger;
use App\Models\ItemAverageDetail;
use App\Models\SaleInvoiceConfiguration;
use App\Models\ItemAverage;
use App\Models\ActivityLog;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\URL;
use Session;
use DB;
use DateTime;
use Gate;
use Validator;

class StockTransferController extends Controller
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
    | Financial Year Processing
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
    | Base Query
    |--------------------------------------------------------------------------
    */
    $query = StockTransfer::where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete_status', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filtering
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('transfer_date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('transfer_date', 'ASC')
              ->orderBy('voucher_no_prefix', 'ASC');

    } else {

        $query->orderBy('id', 'DESC')
              ->limit(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Query
    |--------------------------------------------------------------------------
    */
    $stock_transfers = $query->get();

    if (empty($from_date) && empty($to_date)) {
        $stock_transfers = $stock_transfers->reverse()->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Return JSON Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'        => 200,
        'message'       => 'Stock Transfer list fetched successfully',
        'month_arr'     => $month_arr,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'total_records' => $stock_transfers->count(),
        'data'          => $stock_transfers
    ]);
}

}
