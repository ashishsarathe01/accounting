<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\Payment;
use App\Models\PaymentDetails;
use App\Models\AccountLedger;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\AccountGroups;
use App\Models\ActivityLog;
use DB;
use Session;
use DateTime;
use Gate;

class PaymentController extends Controller
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
    $query = DB::table('payment_details')
        ->select(
            'payments.series_no',
            'payments.id as payment_id',
            DB::raw("DATE_FORMAT(payments.date, '%d/%m/%Y') as date"),
            'payments.mode as mode',
            'accounts.account_name as account_name',
            'payment_details.debit',
            'payment_details.credit',
            'payment_details.id as payment_detail_id',
            'payments.voucher_no'
        )
        ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
        ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
        ->where('payment_details.company_id', $company_id)
        ->where('payments.delete', '0')
        ->where('payment_details.debit', '!=', '')
        ->where('payment_details.debit', '!=', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filtering
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('payments.date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('payments.date', 'ASC')
              ->orderBy('payments.voucher_no', 'ASC');

    } else {

        $query->orderBy('payments.date', 'DESC')
              ->orderBy(DB::raw("CAST(payments.voucher_no AS SIGNED)"), 'DESC')
              ->limit(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Query
    |--------------------------------------------------------------------------
    */
    $payment = $query->get();
    
    $payment = $payment->map(function ($row) {

    switch ($row->mode) {
        case '1':
            $row->mode = 'Cash';
            break;
        case '0':
            $row->mode = 'IMPS/NEFT/RTGS';
            break;
        case '2':
            $row->mode = 'Cheque';
            break;
        default:
            $row->mode = 'IMPS/NEFT/RTGS';
    }

    return $row;
});

    // Reverse only when showing latest 10 (same as web)
    if (empty($from_date) && empty($to_date)) {
        $payment = $payment->reverse()->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Return JSON
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'        => 200,
        'message'       => 'Payment list fetched successfully',
        'month_arr'     => $month_arr,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'total_records' => $payment->count(),
        'data'          => $payment
    ]);
}

public function store(Request $request)
{
    try {

        $com_id = $request->company_id;
        $financial_year = $request->financial_year;
        $user_id = $request->user_id;
        
        $user_data = DB::table('users')
                ->where('id', $user_id)
                ->first();

            if(!$user_data){
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            /*
            |--------------------------------------------------
            | If user is OWNER → skip permission check
            |--------------------------------------------------
            */
            if($user_data->type != "OWNER"){
            
                $permission = DB::table('privileges_module_mappings')
                                ->where('employee_id', $user_id)
                                ->where('module_id', 84)
                                ->where('company_id', $com_id)
                                ->first();
            
                if (!$permission) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }
            
         $validator = Validator::make($request->all(), [
            'date' => 'required',
            'voucher_no' => 'required',
            'mode' => 'required',
            'series_no' => 'required',
            'type' => 'required|array',
            'account_name' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $payment = new Payment;
        $payment->date = $request->input('date');
        $payment->voucher_no = $request->input('voucher_no');
        $payment->mode = $request->input('mode');
        $payment->series_no = $request->input('series_no');
        $payment->cheque_no = $request->input('cheque_no');
        $payment->long_narration = $request->input('long_narration');
        $payment->company_id = $com_id;
        $payment->financial_year = $financial_year;
        $payment->save();

        if ($payment->id) {

            $types = $request->input('type');
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');
            $credits = $request->input('credit');
            $narrations = $request->input('narration');

            $credit_id = "";
            $credit_narration = "";

            // Save Payment Details
            foreach ($types as $key => $type) {

                if ($type == "Credit") {
                    $credit_id = $account_names[$key];
                    $credit_narration = $narrations[$key] ?? '';
                }

                $paytype = new PaymentDetails;
                $paytype->payment_id = $payment->id;
                $paytype->company_id = $com_id;
                $paytype->type = $type;
                $paytype->account_name = $account_names[$key];
                $paytype->debit = $debits[$key] ?? 0;
                $paytype->credit = $credits[$key] ?? 0;
                $paytype->narration = $narrations[$key] ?? '';
                $paytype->status = '1';
                $paytype->save();
            }

            // ===============================
            // Ledger Logic (UNCHANGED)
            // ===============================

            $debit_arr = [];
            $credit_arr = [];

            foreach ($types as $key => $type) {

                if ($type == "Debit") {

                    $debit_arr[] = [
                        'type' => $type,
                        'account_name' => $account_names[$key],
                        'debit' => $debits[$key],
                        'credit' => 0,
                        'narration' => $narrations[$key] ?? '',
                        'mapped_account_id' => $credit_id
                    ];

                    $accountName = $account_names[$key];
                    $debitValue  = $debits[$key];

                    if (isset($credit_arr[$accountName])) {

                        $credit_arr[$accountName]['credit'] += $debitValue;

                    } else {

                        $credit_arr[$accountName] = [
                            'type' => 'Credit',
                            'account_name' => $credit_id,
                            'debit' => 0,
                            'credit' => $debitValue,
                            'narration' => $credit_narration,
                            'mapped_account_id' => $accountName
                        ];
                    }
                }
            }

            $final_arr = array_merge($debit_arr, array_values($credit_arr));

            foreach ($final_arr as $value) {

                $ledger = new AccountLedger();
                $ledger->account_id = $value['account_name'];

                if (!empty($value['debit']) && $value['debit'] != 0) {
                    $ledger->debit = $value['debit'];
                } else {
                    $ledger->credit = $value['credit'];
                }

                $ledger->series_no = $request->input('series_no');
                $ledger->txn_date = $request->input('date');
                $ledger->company_id = $com_id;
                $ledger->financial_year = $financial_year;
                $ledger->entry_type = 5; // Payment Entry
                $ledger->entry_type_id = $payment->id;
                $ledger->entry_narration = $value['narration'];
                $ledger->map_account_id = $value['mapped_account_id'];
                $ledger->created_by = $request->user_id;
                $ledger->created_at = now();
                $ledger->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment voucher added successfully!',
                'payment_id' => $payment->id
            ], 200);

        } else {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Payment not saved'
            ], 500);
        }

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
