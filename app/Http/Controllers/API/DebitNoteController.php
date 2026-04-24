<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDescription;
use App\Models\PurchaseReturnSundry;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchase;
use App\Models\PurchaseSundry;
use App\Models\PurchaseDescription;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\AccountGroups;
use App\Models\PurchaseReturnEntry;
use App\Models\ItemAverageDetail;
use App\Helpers\CommonHelper;
use App\Models\State;
use App\Models\ItemParameterStock;
use App\Models\Sales;
use App\Models\ManageItems;
use App\Models\ActivityLog;
use App\Models\SupplierPurchaseVehicleDetail;
use Carbon\Carbon;
use DB;
use Session;
use DateTime;
use Gate;

class DebitNoteController extends Controller
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
    $query = DB::table('purchase_returns')
        ->select(
            'purchase_returns.id as purchase_return_id',
            'purchase_returns.date',
            'purchase_returns.sr_prefix',
            'purchase_returns.total',
            'purchase_returns.purchase_return_no',
            'purchase_returns.series_no',
            'purchase_returns.financial_year',
            'purchase_returns.sr_nature',
            'purchase_returns.sr_type',
            DB::raw('(select account_name from accounts where accounts.id = purchase_returns.party limit 1) as account_name'),
            DB::raw('(select manual_numbering 
                      from voucher_series_configurations 
                      where voucher_series_configurations.company_id = '.$company_id.' 
                      and configuration_for="DEBIT NOTE" 
                      and voucher_series_configurations.status=1 
                      and voucher_series_configurations.series = purchase_returns.series_no 
                      limit 1) as manual_numbering_status'),
            DB::raw('(select max(purchase_return_no) 
                      from purchase_returns as s 
                      where s.company_id = '.$company_id.' 
                      and s.delete="0" 
                      and s.series_no = purchase_returns.series_no) as max_voucher_no')
        )
        ->where('purchase_returns.company_id', $company_id)
        ->where('purchase_returns.delete', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filtering
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('purchase_returns.date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('purchase_returns.date', 'ASC')
              ->orderBy(DB::raw("CAST(purchase_return_no AS SIGNED)"), 'ASC')
              ->orderBy('purchase_returns.created_at', 'ASC');

    } else {

        $query->orderBy('financial_year', 'DESC')
              ->orderBy(DB::raw("CAST(purchase_return_no AS SIGNED)"), 'DESC')
              ->limit(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Query
    |--------------------------------------------------------------------------
    */
    $purchaseReturn = $query->get();

    if (empty($from_date) && empty($to_date)) {
        $purchaseReturn = $purchaseReturn->reverse()->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Return JSON Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'        => 200,
        'message'       => 'Purchase Return list fetched successfully',
        'month_arr'     => $month_arr,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'total_records' => $purchaseReturn->count(),
        'data'          => $purchaseReturn
    ]);
}

}
