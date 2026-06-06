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
class JournalController extends Controller
{public function index(Request $request)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer',
            'default_fy' => 'required|string',
            'from_date'  => 'nullable|date',
            'to_date'    => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            return response()->json([
                'status' => false,
                'message' => 'to_date is required when from_date is provided'
            ], 422);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            return response()->json([
                'status' => false,
                'message' => 'from_date is required when to_date is provided'
            ], 422);
        }

        $com_id = $request->company_id;
        $financial_year = $request->default_fy;

        /*
        |--------------------------------------------------------------------------
        | Date Formatting
        |--------------------------------------------------------------------------
        */

        $from_date = null;
        $to_date = null;

        if ($request->filled('from_date') && $request->filled('to_date')) {

            $from_date = date(
                'd-m-Y',
                strtotime($request->from_date)
            );

            $to_date = date(
                'd-m-Y',
                strtotime($request->to_date)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Financial Year Months
        |--------------------------------------------------------------------------
        */

        $y = explode("-", $financial_year);

        if (count($y) != 2) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid default_fy format. Example: 24-25'
            ], 422);
        }

        $from = DateTime::createFromFormat('y', $y[0]);

        $to = DateTime::createFromFormat('y', $y[1]);

        if (!$from || !$to) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid financial year'
            ], 422);
        }

        $fromYear = $from->format('Y');
        $toYear = $to->format('Y');

        $month_arr = [
            $fromYear . '-04',
            $fromYear . '-05',
            $fromYear . '-06',
            $fromYear . '-07',
            $fromYear . '-08',
            $fromYear . '-09',
            $fromYear . '-10',
            $fromYear . '-11',
            $fromYear . '-12',
            $toYear . '-01',
            $toYear . '-02',
            $toYear . '-03'
        ];

        /*
        |--------------------------------------------------------------------------
        | Journal Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('journal_details')
            ->select(
                'journals.series_no',
                'journals.voucher_no',
                'journals.id as jon_id',
                'journals.date',
                'accounts.account_name as acc_name',
                'journal_details.*',
                'journals.created_by',
                'journals.approved_by',
                'journals.approved_at',
                'journals.approved_status',
                'created_user.name as created_by_name',
                'approved_user.name as approved_by_name'
            )
            ->join(
                'journals',
                'journal_details.journal_id',
                '=',
                'journals.id'
            )
            ->join(
                'accounts',
                'journal_details.account_name',
                '=',
                'accounts.id'
            )
            ->leftJoin(
                'users as created_user',
                'created_user.id',
                '=',
                'journals.created_by'
            )
            ->leftJoin(
                'users as approved_user',
                'approved_user.id',
                '=',
                'journals.approved_by'
            )
            ->where('journal_details.company_id', $com_id)
            ->where('journals.delete', '0');

        /*
        |--------------------------------------------------------------------------
        | Date Filter OR Last 10 Journals
        |--------------------------------------------------------------------------
        */

        if ($from_date && $to_date) {

            $query->whereRaw("
                STR_TO_DATE(journals.date,'%Y-%m-%d')
                >= STR_TO_DATE(?, '%Y-%m-%d')
                AND
                STR_TO_DATE(journals.date,'%Y-%m-%d')
                <= STR_TO_DATE(?, '%Y-%m-%d')
            ", [
                date('Y-m-d', strtotime($from_date)),
                date('Y-m-d', strtotime($to_date))
            ]);

        } else {

            $last10Ids = DB::table('journals')
                ->where('company_id', $com_id)
                ->where('delete', '0')
                ->orderByRaw("STR_TO_DATE(date,'%Y-%m-%d') DESC")
                ->limit(10)
                ->pluck('id');

            $query->whereIn(
                'journal_details.journal_id',
                $last10Ids
            );
        }

        $journal = $query
            ->orderBy('journals.date', 'asc')
            ->orderBy('journal_details.journal_id', 'asc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Sundries
        |--------------------------------------------------------------------------
        */

        $journal_ids = $journal
            ->pluck('jon_id')
            ->unique()
            ->toArray();

        $sundries = DB::table('journal_sundries as js')
            ->join(
                'bill_sundrys as bs',
                'bs.id',
                '=',
                'js.bill_sundry'
            )
            ->select(
                'js.journal_id',
                'js.amount',
                'bs.name',
                'bs.bill_sundry_type'
            )
            ->whereIn('js.journal_id', $journal_ids)
            ->where('js.delete', '0')
            ->where('js.company_id', $com_id)
            ->get()
            ->groupBy('journal_id');

        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'message' => 'Journal data fetched successfully',
            'data' => [
                'journal' => $journal,
                'sundries' => $sundries,
                'month_arr' => $month_arr,
                'from_date' => $from_date,
                'to_date' => $to_date
            ]
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
