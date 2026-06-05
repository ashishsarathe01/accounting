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
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
  public function index(Request $request)
   {
        // For a POST request, this automatically grabs the payload from the request body
        $input = $request->all();
        
        // Handle Date Filters
        $from_date = "01-" . date('m-Y');
        $to_date = date('d-m-Y');
        
        if (!empty($input['from_date']) && !empty($input['to_date'])) {
            $from_date = date('d-m-Y', strtotime($input['from_date']));
            $to_date = date('d-m-Y', strtotime($input['to_date']));
        }

        // Replacing Session Data with Body/Token Input
        $financial_year = $request->input('financial_year', $request->user()->default_fy ?? '25-26'); 
        $com_id = $request->input('company_id', $request->user()->user_company_id ?? null);

        if (!$com_id) {
            return response()->json([
                'success' => false,
                'message' => 'Company ID is missing or unauthorized.'
            ], 400);
        }

        // Generate Financial Year Months Array
        $y = explode("-", $financial_year);
        $from = DateTime::createFromFormat('y', $y[0])->format('Y');
        $to = DateTime::createFromFormat('y', $y[1])->format('Y');
        
        $month_arr = [
            $from.'-04', $from.'-05', $from.'-06', $from.'-07', $from.'-08', $from.'-09',
            $from.'-10', $from.'-11', $from.'-12', $to.'-01', $to.'-02', $to.'-03'
        ];

        // Fetch Data from Database
        $contra = DB::table('contra_details')
            ->select('contras.series_no', 'contras.id as con_id', 'contras.date', 'accounts.account_name as acc_name', 'contra_details.*', 'contras.mode as m')
            ->join('contras', 'contra_details.contra_id', '=', 'contras.id')
            ->join('accounts', 'contra_details.account_name', '=', 'accounts.id')
            ->where('contra_details.company_id', $com_id)
            ->where('contras.delete', '0')
            ->whereRaw("STR_TO_DATE(contras.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(contras.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->orderBy('contras.date', 'asc')
            ->get();

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