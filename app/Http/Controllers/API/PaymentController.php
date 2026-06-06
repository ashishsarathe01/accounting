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

public function edit(Request $request)
{
    try {

        $payment_id = $request->payment_id;
        $company_id = $request->company_id;

        if (!$payment_id || !$company_id) {

            return response()->json([
                'status' => false,
                'message' => 'payment_id and company_id are required'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Master
        |--------------------------------------------------------------------------
        */

        $payment = Payment::where('id', $payment_id)
                    ->where('company_id', $company_id)
                    ->where('delete', '0')
                    ->first();

        if (!$payment) {

            return response()->json([
                'status' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Details
        |--------------------------------------------------------------------------
        */

        $details = PaymentDetails::select(
                        'payment_details.id',
                        'payment_details.type',
                        'payment_details.account_name',
                        'accounts.account_name as account_name_text',
                        'payment_details.debit',
                        'payment_details.credit',
                        'payment_details.narration'
                    )
                    ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
                    ->where('payment_details.payment_id', $payment_id)
                    ->where('payment_details.company_id', $company_id)
                    ->get();

        /*
        |--------------------------------------------------------------------------
        | Mode Text
        |--------------------------------------------------------------------------
        */

        $mode_text = 'IMPS/NEFT/RTGS';

        if ($payment->mode == '1') {
            $mode_text = 'Cash';
        } elseif ($payment->mode == '2') {
            $mode_text = 'Cheque';
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'code' => 200,
            'message' => 'Payment edit data fetched successfully',

            'data' => [

                'payment_id' => $payment->id,
                'date' => $payment->date,
                'voucher_no' => $payment->voucher_no,
                'series_no' => $payment->series_no,
                'mode' => $payment->mode,
                'mode_text' => $mode_text,
                'cheque_no' => $payment->cheque_no,
                'long_narration' => $payment->long_narration,
                'financial_year' => $payment->financial_year,

                'details' => $details
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function update(Request $request)
{
    try {

        $payment_id = $request->payment_id;
        $com_id = $request->company_id;
        $financial_year = $request->financial_year;
        $user_id = $request->user_id;

        /*
        |--------------------------------------------------------------------------
        | User Validation
        |--------------------------------------------------------------------------
        */

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
        |--------------------------------------------------------------------------
        | Permission Check
        |--------------------------------------------------------------------------
        */

        if($user_data->type != "OWNER"){

            $permission = DB::table('privileges_module_mappings')
                            ->where('employee_id', $user_id)
                            ->where('module_id', 84)
                            ->where('company_id', $com_id)
                            ->first();

            if(!$permission){

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [

            'payment_id' => 'required',
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

        /*
        |--------------------------------------------------------------------------
        | Payment Master Update
        |--------------------------------------------------------------------------
        */

        $payment = Payment::where('id', $payment_id)
                            ->where('company_id', $com_id)
                            ->first();

        if(!$payment){

            return response()->json([
                'status' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->date = $request->date;
        $payment->voucher_no = $request->voucher_no;
        $payment->mode = $request->mode;
        $payment->series_no = $request->series_no;
        $payment->cheque_no = $request->cheque_no;
        $payment->long_narration = $request->long_narration;
        $payment->financial_year = $financial_year;
        $payment->save();

        /*
        |--------------------------------------------------------------------------
        | Delete Old Details
        |--------------------------------------------------------------------------
        */

        PaymentDetails::where('payment_id', $payment_id)->delete();

        AccountLedger::where('entry_type', 5)
                        ->where('entry_type_id', $payment_id)
                        ->delete();

        /*
        |--------------------------------------------------------------------------
        | Insert New Details
        |--------------------------------------------------------------------------
        */

        $types = $request->type;
        $account_names = $request->account_name;
        $debits = $request->debit;
        $credits = $request->credit;
        $narrations = $request->narration;

        $credit_id = "";
        $credit_narration = "";

        foreach ($types as $key => $type) {

            if ($type == "Credit") {

                $credit_id = $account_names[$key];
                $credit_narration = $narrations[$key] ?? '';
            }

            $detail = new PaymentDetails();
            $detail->payment_id = $payment_id;
            $detail->company_id = $com_id;
            $detail->type = $type;
            $detail->account_name = $account_names[$key];
            $detail->debit = $debits[$key] ?? 0;
            $detail->credit = $credits[$key] ?? 0;
            $detail->narration = $narrations[$key] ?? '';
            $detail->status = 1;
            $detail->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Ledger Logic
        |--------------------------------------------------------------------------
        */

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

            $ledger->series_no = $request->series_no;
            $ledger->txn_date = $request->date;
            $ledger->company_id = $com_id;
            $ledger->financial_year = $financial_year;
            $ledger->entry_type = 5;
            $ledger->entry_type_id = $payment_id;
            $ledger->entry_narration = $value['narration'];
            $ledger->map_account_id = $value['mapped_account_id'];
            $ledger->created_by = $user_id;
            $ledger->created_at = now();
            $ledger->save();
        }

        DB::commit();

        return response()->json([

            'status' => true,
            'message' => 'Payment updated successfully!',
            'payment_id' => $payment_id

        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([

            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()

        ], 500);
    }
}


public function bulkStore(Request $request)
{
    try {

        $com_id = $request->company_id;
        $financial_year = $request->financial_year;
        $user_id = $request->user_id;

        $user_data = DB::table('users')
            ->where('id', $user_id)
            ->first();

        if (!$user_data) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user_data->type != "OWNER") {

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

        DB::beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Fetch all aliases once
        |--------------------------------------------------------------------------
        */

        $allAliases = [];

        foreach ($request->vouchers as $voucher) {

            if (!empty($voucher['party_alias'])) {
                $allAliases = array_merge(
                    $allAliases,
                    $voucher['party_alias']
                );
            }
        }

        $accounts = Accounts::where('company_id', $com_id)
            ->whereIn('alias', array_unique($allAliases))
            ->where('delete', '0')
            ->get()
            ->keyBy('alias');

        $createdVoucherIds = [];
        $errors = [];

        foreach ($request->vouchers as $rowNo => $voucher) {

            try {

                $types = $voucher['type'];
                $aliases = $voucher['party_alias'];
                $debits = $voucher['debit'];
                $credits = $voucher['credit'];
                $narrations = $voucher['narration'];

                /*
                |--------------------------------------------------------------------------
                | Convert Alias To Account ID
                |--------------------------------------------------------------------------
                */

                $account_names = [];

                foreach ($aliases as $alias) {

                    $account = $accounts[$alias] ?? null;

                    if (!$account) {

                        $errors[] = [
                            'voucher_no' => $voucher['voucher_no'] ?? '',
                            'alias' => $alias,
                            'message' => "Party alias '{$alias}' not found"
                        ];

                        continue 2;
                    }

                    $account_names[] = $account->id;
                }

                /*
                |--------------------------------------------------------------------------
                | Create Payment
                |--------------------------------------------------------------------------
                */

                $payment = new Payment();
                $payment->date = $voucher['date'];
                $payment->voucher_no = $voucher['voucher_no'];
                $payment->mode = $voucher['mode'];
                $payment->series_no = $voucher['series_no'];
                $payment->cheque_no = $voucher['cheque_no'] ?? '';
                $payment->long_narration = $voucher['long_narration'] ?? '';
                $payment->company_id = $com_id;
                $payment->financial_year = $financial_year;
                $payment->save();

                $credit_id = "";
                $credit_narration = "";

                /*
                |--------------------------------------------------------------------------
                | Payment Details
                |--------------------------------------------------------------------------
                */

                foreach ($types as $key => $type) {

                    if ($type == "Credit") {
                        $credit_id = $account_names[$key];
                        $credit_narration = $narrations[$key] ?? '';
                    }

                    $paytype = new PaymentDetails();
                    $paytype->payment_id = $payment->id;
                    $paytype->company_id = $com_id;
                    $paytype->type = $type;
                    $paytype->account_name = $account_names[$key];
                    $paytype->debit = $debits[$key] ?? 0;
                    $paytype->credit = $credits[$key] ?? 0;
                    $paytype->narration = $narrations[$key] ?? '';
                    $paytype->status = 1;
                    $paytype->save();
                }

                /*
                |--------------------------------------------------------------------------
                | Ledger Logic
                |--------------------------------------------------------------------------
                */

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
                        $debitValue = $debits[$key];

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

                $final_arr = array_merge(
                    $debit_arr,
                    array_values($credit_arr)
                );

                foreach ($final_arr as $value) {

                    $ledger = new AccountLedger();
                    $ledger->account_id = $value['account_name'];

                    if (!empty($value['debit']) && $value['debit'] != 0) {
                        $ledger->debit = $value['debit'];
                    } else {
                        $ledger->credit = $value['credit'];
                    }

                    $ledger->series_no = $voucher['series_no'];
                    $ledger->txn_date = $voucher['date'];
                    $ledger->company_id = $com_id;
                    $ledger->financial_year = $financial_year;
                    $ledger->entry_type = 5;
                    $ledger->entry_type_id = $payment->id;
                    $ledger->entry_narration = $value['narration'];
                    $ledger->map_account_id = $value['mapped_account_id'];
                    $ledger->created_by = $user_id;
                    $ledger->created_at = now();
                    $ledger->save();
                }

                $createdVoucherIds[] = $payment->id;

            } catch (\Exception $e) {

                $errors[] = [
                    'voucher_no' => $voucher['voucher_no'] ?? '',
                    'message' => $e->getMessage()
                ];

                continue;
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => count($createdVoucherIds) . ' payment vouchers imported successfully',
            'payment_ids' => $createdVoucherIds,
            'errors' => $errors
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}                                                                                                                                                                                                                                                

}
