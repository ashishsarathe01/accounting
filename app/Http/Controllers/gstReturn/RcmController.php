<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\Accounts;
use App\Models\AccountLedger;
use App\Models\Companies;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\GstBranch;
use App\Models\AccountGroups;
use App\Models\BillSundrys;
use DB;
use App\Models\State;
use Carbon\Carbon;
use Session;

class RcmController extends Controller
{
    public function RcmReport(Request $request)
{
    $month = $request->month ?? '12-2025';
    $from_date = Carbon::createFromFormat('m-Y', $month)->startOfMonth()->toDateString();
    $to_date   = Carbon::createFromFormat('m-Y', $month)->endOfMonth()->toDateString();

    $company_id = Session::get('user_company_id');
    $gstNo = $request->gst_no ?? '02AACCH1322B2ZN';

    $exist = DB::table('rcm')
                ->where('company_id',Session::get('user_company_id'))
                ->where('gst_no', $gstNo)
                ->where('month',$month)
                ->where('status','1')
                ->select('journal_id')
                ->first();
                

    /* ---------------- SERIES FETCH LOGIC (UNCHANGED) ---------------- */
    $series = collect();
    $companyData = Companies::find($company_id);

    if ($companyData->gst_config_type == "single_gst") {

        $GstSettings = DB::table('gst_settings')
            ->where(compact('company_id'))
            ->where('gst_type', 'single_gst')
            ->where('gst_no', $gstNo)
            ->get();

        if ($GstSettings->count()) {
            $series = $series->merge($GstSettings->pluck('series'));

            $branch = GstBranch::where([
                'delete' => 0,
                'company_id' => $company_id,
                'gst_number' => $gstNo,
                'gst_setting_id' => $GstSettings[0]->id
            ])->pluck('branch_series');

            $series = $series->merge($branch);
        }

    } else {

        $GstSettings = DB::table('gst_settings_multiple')
            ->where(compact('company_id'))
            ->where('gst_type', 'multiple_gst')
            ->where('gst_no', $gstNo)
            ->get();

        foreach ($GstSettings as $value) {
            $series->push($value->series);

            $branch = GstBranch::where([
                'delete' => 0,
                'company_id' => $company_id,
                'gst_number' => $gstNo,
                'gst_setting_multiple_id' => $value->id
            ])->pluck('branch_series');

            $series = $series->merge($branch);
        }
    }

    $series = $series->filter()->unique()->values()->toArray();

    /* ---------------- RCM LEDGER DATA ---------------- */

    $accounts = Accounts::where('company_id', $company_id)
        ->where('rcm', 1)
        ->get();

    $rcmData = [];
    $total_amt = $total_cgst = $total_sgst = $total_igst = 0;

    foreach ($accounts as $acc) {

        $ledgers = AccountLedger::join('accounts','account_ledger.account_id','=','accounts.id')
            ->where('account_ledger.company_id', $company_id)
            ->where('account_ledger.account_id', $acc->id)
            ->whereIn('series_no', $series)
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->select('account_ledger.*','accounts.account_name')
            ->get();


        foreach ($ledgers as $row) {

            $cgst = $sgst = $igst = 0;
            $accounts_map = Accounts::where('company_id', $company_id)
                            ->select('state')
                                ->where('id', $row->map_account_id)
                                ->first();

$stateCode = State::where('id', $accounts_map->state)->value('state_code');
            if (!empty($gstNo) && substr($gstNo, 0, 2) == $stateCode) {
                $cgst = ($row->debit * $acc->rcm_rate) / 200;
                $sgst = ($row->debit * $acc->rcm_rate) / 200;
            } else {
                $igst = ($row->debit * $acc->rcm_rate) / 100;
            }

            $rcmData[] = [
                'date' => $row->txn_date,
                'account' => $row->account_name,
                'amount' => $row->debit,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst
            ];

            $total_amt += $row->debit;
            $total_cgst += $cgst;
            $total_sgst += $sgst;
            $total_igst += $igst;
        }
    }

    return view('gstReturn.rcm_report', compact(
        'rcmData','month','total_amt','total_cgst','total_sgst','total_igst','gstNo','exist'
    ));
}


   public function storeRCM(Request $request)
{
    $company_id = Session::get('user_company_id');

    // deactivate previous record
    


$to_date   = Carbon::createFromFormat('m-Y', $request->month)->endOfMonth()->toDateString();

    DB::beginTransaction();
try {

     $financial_year = Session::get('default_fy');
      $journal = new Journal;
      $journal->date = $to_date;
      $journal->voucher_no = '';
      $journal->series_no = $request->series;
      $journal->long_narration = $request->input('long_narration');
      $journal->company_id = Session::get('user_company_id');
      $journal->financial_year = $financial_year;
      $journal->claim_gst_status = "No";
      $journal->merchant_gst = $request->input('merchant_gst') ?? '02AACCH1322B2ZN'; 
      
      $journal->save();

      DB::table('rcm')
        ->where('company_id', $company_id)
        ->where('month', $request->month)
        ->update(['status' => 0]);

    DB::table('rcm')->insert([
        'gst_no' => implode(',', $request->gstNo ?? []),
        'company_id' => $company_id,
        'month' => $request->month,
        'total_amt' => $request->total_amt,
        'cgst' => $request->cgst,
        'sgst' => $request->sgst,
        'igst' => $request->igst,
        'journal_id' => $journal->id,
        'status' => 1,
        'created_at' => now(),
        'created_by' => Session::get('user_id')
    ]);
    
      if($journal->id){
         
            //Journal Entry
            
            if(!empty($request->igst)){
               $sundry = BillSundrys::select('purchase_amt_account','sale_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
               $account_name = "";
               $output_acc_name = "";
               if($sundry){
                  $account_name = $sundry->purchase_amt_account;
                  $output_acc_name = $sundry->sale_amt_account;
               }
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $account_name;
               $joundetail->debit = $request->igst;
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $account_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->debit = $request->igst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $output_acc_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();

               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Credit";
               $joundetail->account_name = $output_acc_name;
               $joundetail->credit = $request->igst;
               $joundetail->status = '1';
               $joundetail->save();
               //Ledger Entry

               $ledger = new AccountLedger();
               $ledger->account_id = $output_acc_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->credit = $request->igst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            if(!empty($request->cgst)){
               $cgst_account_name = "";
               $output_cgst_account_name = "";
               $cgst_sundry = BillSundrys::select('purchase_amt_account','sale_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('delete','0')
                        ->where('status','1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
               if($cgst_sundry){
                  $cgst_account_name = $cgst_sundry->purchase_amt_account;
                  $output_cgst_account_name = $cgst_sundry->sale_amt_account;
               }
               $sgst_sundry = BillSundrys::select('purchase_amt_account','sale_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('delete','0')
                           ->where('status','1')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();
               $sgst_account_name = "";
               $output_sgst_account_name = "";
               if($sgst_sundry){
                  $sgst_account_name = $sgst_sundry->purchase_amt_account;
                  $output_sgst_account_name = $sgst_sundry->sale_amt_account;

               }
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $cgst_account_name;
               $joundetail->debit = $request->cgst;
               $joundetail->status = '1';
               $joundetail->save();

               
               //Ledger Entry


               $ledger = new AccountLedger();
               $ledger->account_id = $cgst_account_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->debit = $request->cgst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $output_cgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();


               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Credit";
               $joundetail->account_name = $output_cgst_account_name;
               $joundetail->credit = $request->cgst;
               $joundetail->status = '1';
               $joundetail->save();

                $ledger = new AccountLedger();
               $ledger->account_id = $output_cgst_account_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->credit = $request->cgst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();


               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Debit";
               $joundetail->account_name = $sgst_account_name;
               $joundetail->debit = $request->sgst;
               $joundetail->status = '1';
               $joundetail->save();


               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->debit = $request->sgst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $output_sgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();


               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Credit";
               $joundetail->account_name = $output_sgst_account_name;
               $joundetail->credit = $request->sgst;
               $joundetail->status = '1';
               $joundetail->save();

               $ledger = new AccountLedger();
               $ledger->account_id = $output_sgst_account_name;
               $ledger->series_no = $request->input('series_no') ?? implode(',', $request->series ?? []);
               $ledger->credit = $request->sgst;                       
               $ledger->txn_date = $to_date;
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 7;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_type_detail_id = $joundetail->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
          
            }
         session(['previous_url_journal' => URL::previous()]);
         if($request->input('form_source') && !empty($request->input('form_source'))){
            return redirect('profitloss')->withSuccess('Journal added successfully!');
         }else{
            return redirect('journal')->withSuccess('Journal added successfully!');
         }
         
      }else{
         $this->failedMessage();
      }
         DB::commit();
} catch (\Exception $e) {
   DB::rollBack();
   return back()->withErrors($e->getMessage());
}
    return back()->with('success','RCM saved successfully');
}



public function updateRCM(Request $request)
{
    $company_id = Session::get('user_company_id');
    $journalId  = $request->journal_id;

    $to_date = Carbon::createFromFormat('m-Y', $request->month)
                ->endOfMonth()
                ->toDateString();

    DB::beginTransaction();

    try {

        /* =====================================================
         | 1️⃣ DELETE PREVIOUS DATA
         =====================================================*/
        AccountLedger::where('entry_type', 7)
            ->where('entry_type_id', $journalId)
            ->delete();

        JournalDetails::where('journal_id', $journalId)->delete();

        Journal::where('id', $journalId)->delete();

        /* =====================================================
         | 2️⃣ CREATE NEW JOURNAL
         =====================================================*/
        $journal = new Journal();
        $journal->date             = $to_date;
        $journal->voucher_no       = '';
        $journal->series_no        = $request->series;
        $journal->long_narration   = $request->long_narration;
        $journal->company_id       = $company_id;
        $journal->financial_year  = Session::get('default_fy');
        $journal->claim_gst_status = 'No';
        $journal->merchant_gst     = $request->merchant_gst ?? '02AACCH1322B2ZN';
        $journal->save();

        /* =====================================================
         | 3️⃣ DEACTIVATE OLD RCM
         =====================================================*/
        DB::table('rcm')
            ->where('company_id', $company_id)
            ->where('month', $request->month)
            ->where('gst_no', $request->gstNo)
             ->delete();


        /* =====================================================
         | 4️⃣ INSERT NEW RCM
         =====================================================*/
        DB::table('rcm')->insert([
            'gst_no'      => $request->gstNo ?? "",
            'company_id'  => $company_id,
            'month'       => $request->month,
            'total_amt'   => $request->total_amt,
            'cgst'        => $request->cgst,
            'sgst'        => $request->sgst,
            'igst'        => $request->igst,
            'journal_id'  => $journal->id,
            'status'      => 1,
            'created_at'  => now(),
            'created_by'  => Session::get('user_id'),
        ]);

        /* =====================================================
         | 5️⃣ IGST ENTRIES
         =====================================================*/
        if (!empty($request->igst)) {

            $igst = BillSundrys::where('nature_of_sundry', 'IGST')
                ->where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete', '0')
                ->first();

            if ($igst) {

                // Debit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Debit',
                    'account_name' => $igst->purchase_amt_account,
                    'debit'      => $request->igst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $igst->purchase_amt_account,
                    'debit'      => $request->igst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $igst->sale_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);

                // Credit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Credit',
                    'account_name' => $igst->sale_amt_account,
                    'credit'     => $request->igst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $igst->sale_amt_account,
                    'credit'     => $request->igst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $igst->purchase_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);
            }
        }

        /* =====================================================
         | 6️⃣ CGST + SGST ENTRIES
         =====================================================*/
        if (!empty($request->cgst) || !empty($request->sgst)) {

            $cgst = BillSundrys::where('nature_of_sundry', 'CGST')
                ->where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete', '0')
                ->first();

            $sgst = BillSundrys::where('nature_of_sundry', 'SGST')
                ->where('company_id', $company_id)
                ->where('status', '1')
                ->where('delete', '0')
                ->first();

            /* ---------- CGST ---------- */
            if (!empty($request->cgst) && $cgst) {

                // Debit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Debit',
                    'account_name' => $cgst->purchase_amt_account,
                    'debit'      => $request->cgst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $cgst->purchase_amt_account,
                    'debit'      => $request->cgst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $cgst->sale_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);

                // Credit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Credit',
                    'account_name' => $cgst->sale_amt_account,
                    'credit'     => $request->cgst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $cgst->sale_amt_account,
                    'credit'     => $request->cgst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $cgst->purchase_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);
            }

            /* ---------- SGST ---------- */
            if (!empty($request->sgst) && $sgst) {

                // Debit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Debit',
                    'account_name' => $sgst->purchase_amt_account,
                    'debit'      => $request->sgst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $sgst->purchase_amt_account,
                    'debit'      => $request->sgst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $sgst->sale_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);

                // Credit
                $jd = JournalDetails::create([
                    'journal_id' => $journal->id,
                    'company_id' => $company_id,
                    'type'       => 'Credit',
                    'account_name' => $sgst->sale_amt_account,
                    'credit'     => $request->sgst,
                    'status'     => 1,
                ]);

                AccountLedger::create([
                    'account_id' => $sgst->sale_amt_account,
                    'credit'     => $request->sgst,
                    'txn_date'   => $to_date,
                    'company_id' => $company_id,
                    'financial_year' => Session::get('default_fy'),
                    'entry_type' => 7,
                    'map_account_id' => $sgst->purchase_amt_account,
                    'entry_type_id' => $journal->id,
                    'entry_type_detail_id' => $jd->id,
                    'created_by' => Session::get('user_id'),
                ]);
            }
        }

        DB::commit();
        return back()->with('success', 'RCM updated successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors($e->getMessage());
    }
}



}
