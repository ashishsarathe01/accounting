<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
{
    $company_id = $request->company_id;

    if (!$company_id) {
        return response()->json([
            'status' => false,
            'message' => 'company_id is required'
        ], 400);
    }

    $from_date = null;
    $to_date   = null;

    // Date filter (optional)
    if (!empty($request->from_date) && !empty($request->to_date)) {
        $from_date = date('Y-m-d', strtotime($request->from_date));
        $to_date   = date('Y-m-d', strtotime($request->to_date));
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Year Month Array
    |--------------------------------------------------------------------------
    */
    $financial_year = $request->financial_year;

    $month_arr = [];
    if ($financial_year) {
        $y = explode("-", $financial_year);
        $from = DateTime::createFromFormat('y', $y[0])->format('Y');
        $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

        $month_arr = [
            $from . '-04', $from . '-05', $from . '-06', $from . '-07',
            $from . '-08', $from . '-09', $from . '-10', $from . '-11',
            $from . '-12', $to . '-01', $to . '-02', $to . '-03'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */
    $query = DB::table('journal_details')
        ->select(
            'journals.series_no',
            'journals.id as journal_id',
            'journals.date',
            'accounts.account_name as acc_name',
            'journal_details.*'
        )
        ->join('journals', 'journal_details.journal_id', '=', 'journals.id')
        ->join('accounts', 'journal_details.account_name', '=', 'accounts.id')
        ->where('journal_details.company_id', $company_id)
        ->where('journals.delete', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */
    if ($from_date && $to_date) {

        $query->whereBetween(DB::raw("STR_TO_DATE(journals.date,'%Y-%m-%d')"), [
            $from_date,
            $to_date
        ]);

    } else {

        // Last 10 journal IDs
        $last10Ids = DB::table('journals')
            ->where('company_id', $company_id)
            ->where('delete', '0')
            ->orderByRaw("STR_TO_DATE(date,'%Y-%m-%d') DESC")
            ->limit(10)
            ->pluck('id');

        $query->whereIn('journal_details.journal_id', $last10Ids);
    }

    $journal = $query
        ->orderBy('journals.date', 'asc')
        ->orderBy('journal_details.journal_id', 'asc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | API Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'status' => true,
        'message' => 'Journal list fetched successfully',
        'data' => [
            'journals'   => $journal,
            'month_arr'  => $month_arr,
            'from_date'  => $from_date,
            'to_date'    => $to_date,
        ]
    ]);
}

}
