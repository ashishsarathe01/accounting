<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Receipt;
use App\Models\ReceiptDetails;
use DB;

class ReceiptController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $receipt = DB::table('receipt_details')
        ->select('receipts.id', 'receipts.date','accounts.account_name as acc_name','receipt_details.*')
        ->join('receipts', 'receipt_details.receipt_id', '=', 'receipts.id')
        ->join('accounts', 'receipt_details.account_name', '=', 'accounts.id')
        ->get();
        return view('receipt')->with('receipt', $receipt);
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $party_list = Accounts::where('delete', '=', '0')->get();
        return view('addReceipt')->with('party_list', $party_list);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // print_r($request->all());
        // die;
        /* $validator = Validator::make($request->all(), [
            'series_no' => 'required|string',
        ], [
            'series_no.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } */

        $receipt = new Receipt;

        $receipt->date = $request->input('date');
        $receipt->company_id = '2';
        //$sale->status = $request->input('status');
        $receipt->save();

        if ($receipt->id) {

            $types = $request->input('type');
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');
            $credits = $request->input('credit');
            $modes = $request->input('mode');
            $narrations = $request->input('narration');

            foreach ($types as $key => $type) {

                $rectype = new ReceiptDetails;

                $rectype->receipt_id = $receipt->id;
                $rectype->company_id = '2';
                $rectype->type = $type;
                $rectype->account_name = $account_names[$key];
                $rectype->debit = isset($debits[$key]) ? $debits[$key] :'0';
                $rectype->credit = isset($credits[$key]) ? $credits[$key] :'0';
                $rectype->mode = $modes[$key];
                $rectype->narration = $narrations[$key];
                $rectype->status = '1';
                $rectype->save();
            }

            return redirect('payment')->withSuccess('Receipt added successfully!');
        } else {
            $this->failedMessage();
        }
    }
}