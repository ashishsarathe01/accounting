<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\Accounts;
use App\Models\Receipt;
use App\Models\ReceiptDetails;
use App\Models\AccountLedger;
use App\Models\AccountGroups;
use App\Models\Companies;
use App\Models\GstBranch;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
use Gate;

class ReceiptController extends Controller
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
    $query = DB::table('receipt_details')
        ->select(
            'receipts.series_no',
            'receipts.id as receipt_id',
           DB::raw("DATE_FORMAT(receipts.date, '%d/%m/%Y') as date"),
            'accounts.account_name as account_name',
            'receipts.mode as mode',
            'receipt_details.debit',
            'receipt_details.credit',
            'receipt_details.id as receipt_detail_id',
            'receipts.voucher_no'
        )
        ->join('receipts', 'receipt_details.receipt_id', '=', 'receipts.id')
        ->join('accounts', 'receipt_details.account_name', '=', 'accounts.id')
        ->where('receipt_details.company_id', $company_id)
        ->where('receipts.delete', '0')
        ->where('receipt_details.credit', '!=', '')
        ->where('receipt_details.credit', '!=', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filtering Logic
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('receipts.date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('receipts.date', 'ASC')
              ->orderBy('receipts.voucher_no', 'ASC');

    } else {

        $query->orderBy(DB::raw("CAST(receipts.voucher_no AS SIGNED)"), 'DESC')
              ->orderBy('receipts.date', 'DESC')
              ->limit(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Query
    |--------------------------------------------------------------------------
    */
    $receipt = $query->get();
    $receipt = $receipt->map(function ($row) {

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

    // Reverse only when showing latest 10 (same as web behavior)
    if (empty($from_date) && empty($to_date)) {
        $receipt = $receipt->reverse()->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Return JSON Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'        => 200,
        'message'       => 'Receipt list fetched successfully',
        'month_arr'     => $month_arr,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'total_records' => $receipt->count(),
        'data'          => $receipt
    ]);
}

public function store(Request $request)
{
    try {
        
        $user_id = $request->user_id;
        $company_id = $request->company_id;
        
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
                    ->where('company_id', $company_id)
                    ->first();

    if (!$permission) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403);
    }
}

        $financial_year = $request->financial_year;
       

        // Basic Validation
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

        $receipt = new Receipt;
        $receipt->date = $request->input('date');
        $receipt->voucher_no = $request->input('voucher_no');
        $receipt->mode = $request->input('mode');
        $receipt->series_no = $request->input('series_no');
        $receipt->cheque_no = $request->input('cheque_no');
        $receipt->long_narration = $request->input('long_narration');
        $receipt->company_id = $company_id;
        $receipt->financial_year = $financial_year;
        $receipt->save();

        if ($receipt->id) {

            $types = $request->input('type');
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');
            $credits = $request->input('credit');
            $narrations = $request->input('narration');

            $debit_id = "";
            $debit_narration = "";

            foreach ($types as $key => $type) {

                if ($type == "Debit") {
                    $debit_id = $account_names[$key];
                    $debit_narration = isset($narrations[$key]) ? $narrations[$key] : '';
                }

                $rectype = new ReceiptDetails;
                $rectype->receipt_id = $receipt->id;
                $rectype->company_id = $company_id;
                $rectype->type = $type;
                $rectype->account_name = $account_names[$key];
                $rectype->debit = isset($debits[$key]) ? $debits[$key] : '0';
                $rectype->credit = isset($credits[$key]) ? $credits[$key] : '0';
                $rectype->narration = $narrations[$key] ?? '';
                $rectype->status = '1';
                $rectype->save();
            }

            // Ledger Entry Logic (UNCHANGED)

            $debit_arr = [];
            $credit_arr = [];

            foreach ($types as $key => $type) {

                if ($type == "Credit") {

                    $credit_arr[] = [
                        'type' => $type,
                        'account_name' => $account_names[$key],
                        'credit' => $credits[$key],
                        'debit' => 0,
                        'narration' => $narrations[$key] ?? '',
                        'mapped_account_id' => $debit_id
                    ];

                    $accountName = $account_names[$key];
                    $creditValue = $credits[$key];

                    if (isset($debit_arr[$accountName])) {

                        $debit_arr[$accountName]['debit'] += $creditValue;

                    } else {

                        $debit_arr[$accountName] = [
                            'type' => 'Debit',
                            'account_name' => $debit_id,
                            'debit' => $creditValue,
                            'credit' => 0,
                            'narration' => $debit_narration,
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
                $ledger->company_id = $company_id;
                $ledger->financial_year = $financial_year;
                $ledger->entry_type = 6;
                $ledger->entry_type_id = $receipt->id;
                $ledger->entry_narration = $value['narration'];
                $ledger->map_account_id = $value['mapped_account_id'];
                $ledger->created_by = $request->user_id;
                $ledger->created_at = now();
                $ledger->save();
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => 'Receipt added successfully!',
                'receipt_id' => $receipt->id
            ], 200);

        } else {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Receipt not saved'
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

public function update(Request $request)
{
    try {

        $user_id = $request->user_id;
        $company_id = $request->company_id;
        $receipt_id = $request->receipt_id;

        $user_data = DB::table('users')
                        ->where('id',$user_id)
                        ->first();

        if(!$user_data){
            return response()->json([
                'status'=>false,
                'message'=>'User not found'
            ],404);
        }

        /* OWNER permission bypass */
        if($user_data->type != "OWNER"){

            $permission = DB::table('privileges_module_mappings')
                            ->where('employee_id',$user_id)
                            ->where('module_id',84)
                            ->where('company_id',$company_id)
                            ->first();

            if(!$permission){
                return response()->json([
                    'status'=>false,
                    'message'=>'Unauthorized'
                ],403);
            }
        }

        /* Validation */

        $validator = Validator::make($request->all(),[
            'receipt_id' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'mode' => 'required',
            'series_no' => 'required',
            'type' => 'required|array',
            'account_name' => 'required|array',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'Validation Error',
                'errors'=>$validator->errors()
            ],422);
        }

        DB::beginTransaction();

        /* Update Receipt */

        $receipt = Receipt::find($receipt_id);

        if(!$receipt){
            return response()->json([
                'status'=>false,
                'message'=>'Receipt not found'
            ],404);
        }

        $receipt->date = $request->date;
        $receipt->voucher_no = $request->voucher_no;
        $receipt->mode = $request->mode;
        $receipt->series_no = $request->series_no;
        $receipt->cheque_no = $request->cheque_no;
        $receipt->long_narration = $request->long_narration;
        $receipt->save();

        /* Delete old details */

        ReceiptDetails::where('receipt_id',$receipt_id)->delete();

        AccountLedger::where('entry_type',6)
                        ->where('entry_type_id',$receipt_id)
                        ->delete();

        /* Insert new details */

        $types = $request->type;
        $account_names = $request->account_name;
        $debits = $request->debit;
        $credits = $request->credit;
        $narrations = $request->narration;

        $debit_id = "";
        $debit_narration = "";

        foreach($types as $key=>$type){

            if($type=="Debit"){
                $debit_id = $account_names[$key];
                $debit_narration = $narrations[$key] ?? '';
            }

            $detail = new ReceiptDetails();
            $detail->receipt_id = $receipt_id;
            $detail->company_id = $company_id;
            $detail->type = $type;
            $detail->account_name = $account_names[$key];
            $detail->debit = $debits[$key] ?? 0;
            $detail->credit = $credits[$key] ?? 0;
            $detail->narration = $narrations[$key] ?? '';
            $detail->status = 1;
            $detail->save();
        }

        /* Ledger Logic */

        $debit_arr = [];
        $credit_arr = [];

        foreach($types as $key=>$type){

            if($type=="Credit"){

                $credit_arr[]=[
                    'type'=>$type,
                    'account_name'=>$account_names[$key],
                    'credit'=>$credits[$key],
                    'debit'=>0,
                    'narration'=>$narrations[$key] ?? '',
                    'mapped_account_id'=>$debit_id
                ];

                $accountName = $account_names[$key];
                $creditValue = $credits[$key];

                if(isset($debit_arr[$accountName])){

                    $debit_arr[$accountName]['debit'] += $creditValue;

                }else{

                    $debit_arr[$accountName]=[
                        'type'=>'Debit',
                        'account_name'=>$debit_id,
                        'debit'=>$creditValue,
                        'credit'=>0,
                        'narration'=>$debit_narration,
                        'mapped_account_id'=>$accountName
                    ];
                }
            }
        }

        $final_arr = array_merge($debit_arr,array_values($credit_arr));

        foreach($final_arr as $value){

            $ledger = new AccountLedger();

            $ledger->account_id = $value['account_name'];

            if(!empty($value['debit']) && $value['debit'] != 0){
                $ledger->debit = $value['debit'];
            }else{
                $ledger->credit = $value['credit'];
            }

            $ledger->series_no = $request->series_no;
            $ledger->txn_date = $request->date;
            $ledger->company_id = $company_id;
            $ledger->financial_year = $request->financial_year;
            $ledger->entry_type = 6;
            $ledger->entry_type_id = $receipt_id;
            $ledger->entry_narration = $value['narration'];
            $ledger->map_account_id = $value['mapped_account_id'];
            $ledger->created_by = $user_id;
            $ledger->created_at = now();
            $ledger->save();
        }

        DB::commit();

        return response()->json([
            'code'=>200,
            'message'=>'Receipt updated successfully',
            'receipt_id'=>$receipt_id
        ]);

    }
    catch(\Exception $e){

        DB::rollBack();

        return response()->json([
            'status'=>false,
            'message'=>'Something went wrong',
            'error'=>$e->getMessage()
        ],500);
    }
}

}
