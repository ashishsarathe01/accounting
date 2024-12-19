<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Journal;
use App\Models\JournalDetails;
use DB;

class JournalController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $journal = DB::table('journal_details')
            ->select('journals.id', 'journals.date', 'accounts.account_name as acc_name', 'journal_details.*')
            ->join('journals', 'journal_details.journal_id', '=', 'journals.id')
            ->join('accounts', 'journal_details.account_name', '=', 'accounts.id')
            ->get();
        return view('journal')->with('journal', $journal);
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $party_list = Accounts::where('delete', '=', '0')->get();
        return view('addJournal')->with('party_list', $party_list);
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
        $journal = new Journal;

        $journal->date = $request->input('date');
        $journal->company_id = '2';
        //$sale->status = $request->input('status');
        $journal->save();

        if ($journal->id) {

            $types = $request->input('type');
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');
            $credits = $request->input('credit');
            $modes = $request->input('mode');
            $narrations = $request->input('narration');

            foreach ($types as $key => $type) {

                $joundetail = new JournalDetails;

                $joundetail->journal_id = $journal->id;
                $joundetail->company_id = '2';
                $joundetail->type = $type;
                $joundetail->account_name = $account_names[$key];
                $joundetail->debit = isset($debits[$key]) ? $debits[$key] : '0';
                $joundetail->credit = isset($credits[$key]) ? $credits[$key] : '0';
                $joundetail->mode = $modes[$key];
                $joundetail->narration = $narrations[$key];
                $joundetail->status = '1';
                $joundetail->save();
            }

            return redirect('payment')->withSuccess('Journal added successfully!');
        } else {
            $this->failedMessage();
        }
    }
}
