<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Contra;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\ContraDetails;
use App\Models\AccountLedger;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\ActivityLog;
use App\Models\VoucherSeriesConfiguration;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
use Gate;
use App\Helpers\CommonHelper;
class ContraController extends Controller
{
    /**
     * Display a listing of the contra resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
{
    $input = $request->all();

    $from_date = null;
    $to_date = null;

    // Date range filter
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
    }

    // Get values from request only
    $financial_year = $request->input('financial_year');
    $com_id = $request->input('company_id');

    // Validation
    if (!$financial_year) {
        return response()->json([
            'success' => false,
            'message' => 'Financial year is required.'
        ], 400);
    }

    if (!$com_id) {
        return response()->json([
            'success' => false,
            'message' => 'Company ID is required.'
        ], 400);
    }

    // Generate Financial Year Months Array
    $y = explode("-", $financial_year);

    $fromFormat = strlen($y[0]) === 4 ? 'Y' : 'y';
    $toFormat = strlen($y[1]) === 4 ? 'Y' : 'y';

    $from = DateTime::createFromFormat($fromFormat, $y[0])->format('Y');
    $to = DateTime::createFromFormat($toFormat, $y[1])->format('Y');

    $month_arr = [
        $from . '-04',
        $from . '-05',
        $from . '-06',
        $from . '-07',
        $from . '-08',
        $from . '-09',
        $from . '-10',
        $from . '-11',
        $from . '-12',
        $to . '-01',
        $to . '-02',
        $to . '-03'
    ];

    // Build query
    $query = DB::table('contra_details')
        ->select(
            'contras.series_no',
            'contras.id as con_id',
            'contras.date',
            'accounts.account_name as acc_name',
            'contra_details.*',
            'contras.mode as m',
            'contras.voucher_no',
            'contras.created_by',
            'contras.approved_by',
            'contras.approved_at',
            'contras.approved_status',
            'created_user.name as created_by_name',
            'approved_user.name as approved_by_name'
        )
        ->join('contras', 'contra_details.contra_id', '=', 'contras.id')
        ->join('accounts', 'contra_details.account_name', '=', 'accounts.id')
        ->leftJoin('users as created_user', 'created_user.id', '=', 'contras.created_by')
        ->leftJoin('users as approved_user', 'approved_user.id', '=', 'contras.approved_by')
        ->where('contra_details.company_id', $com_id)
        ->where('contras.delete', '0');

    // Apply date filter if provided
    if ($from_date && $to_date) {

        $query->whereRaw(
            "
            STR_TO_DATE(contras.date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')
            AND STR_TO_DATE(contras.date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')
            ",
            [
                date('Y-m-d', strtotime($from_date)),
                date('Y-m-d', strtotime($to_date))
            ]
        );

        $query->orderBy('contras.date', 'asc')
              ->orderBy('contras.voucher_no', 'asc');

    } else {

        // Same behavior as web version
        $query->orderBy(DB::raw("CAST(contras.voucher_no AS SIGNED)"), 'desc')
              ->orderBy('contras.date', 'desc')
              ->limit(10);
    }

    $contra = $query->get()->reverse()->values();

    return response()->json([
        'success' => true,
        'data' => [
            'contra' => $contra,
            'month_arr' => $month_arr,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]
    ], 200);
}
}