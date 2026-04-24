<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccountLedger;
use App\Models\Accounts;
use App\Models\Sales;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Payment;
use App\Models\Companies;
use App\Models\PaymentDetails;
use App\Models\Receipt;
use App\Models\ReceiptDetails;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\Contra;
use App\Models\SaleInvoiceConfiguration;
use DB;
use Carbon\Carbon;


class AccountLedgerController extends Controller
{
  
    public function filter(Request $request)
{
    $request->validate([
        'party_id'   => 'required|integer',
        'company_id' => 'required|integer',
        'from_date'  => 'nullable|date',
        'to_date'    => 'nullable|date'
    ]);

    $party_id   = $request->party_id;
    $company_id = $request->company_id;
    $from_date  = $request->from_date;
    $to_date    = $request->to_date;
    $to_date1   = Carbon::parse($to_date)->addDay()->format('Y-m-d');

    /** ================= FETCH LEDGER ================= */
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $ledger = AccountLedger::where('account_id', $party_id)
            ->where('entry_type', '!=', -1)
            ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d') >= ?", [$from_date])
            ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d') <= ?", [$to_date])
            ->where('status', 1)
            ->where('delete_status', '0')
            ->where('company_id', $company_id)
            ->orderByRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')")
            ->orderBy('entry_type')
            ->orderBy('entry_type_id')
            ->get();
    } else {
        $ledger = AccountLedger::where('account_id', $party_id)
            ->where('company_id', $company_id)
            ->where('entry_type', '!=', -1)
            ->where('delete_status', '0')
            ->orderByRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')")
            ->get();
    }

    /** ================= OPENING BALANCE ================= */
    $opening = (float) $this->calculateOpeningBalance($party_id, $from_date, $company_id);
    $runningBalance = $opening;

    /** ================= ENRICH + RUNNING BALANCE ================= */
    foreach ($ledger as $row) {

        /** --- Defaults --- */
        $row->bill_no = '';
        $row->narration = '';
        $row->long_narration = '';
        $row->einvoice_status = 0;

        /** --- Debit / Credit calculation --- */
        $debit  = (float) ($row->debit ?? 0);
        $credit = (float) ($row->credit ?? 0);

        $runningBalance += $debit;
        $runningBalance -= $credit;

        /** --- Attach Running Balance --- */
        $row->running_balance = round($runningBalance, 2);
        $row->running_balance_abs = abs(round($runningBalance, 2));
        $row->running_balance_type = $runningBalance < 0 ? 'Cr' : 'Dr';

        /** ================= ENTRY TYPE DETAILS ================= */
        switch ($row->entry_type) {

            case 1:
                $sale = Sales::select('voucher_no_prefix','e_invoice_status','e_waybill_status')
                    ->find($row->entry_type_id);
                $row->bill_no = $sale->voucher_no_prefix ?? '';
                $row->einvoice_status = ($sale && ($sale->e_invoice_status || $sale->e_waybill_status)) ? 1 : 0;
                break;

            case 2:
                $row->bill_no = Purchase::where('id',$row->entry_type_id)->value('voucher_no') ?? '';
                break;

            case 3: case 9: case 10:
                $row->bill_no = SalesReturn::where('id',$row->entry_type_id)->value('sr_prefix') ?? '';
                break;

            case 4: case 12: case 13:
                $row->bill_no = PurchaseReturn::where('id',$row->entry_type_id)->value('sr_prefix') ?? '';
                break;

            case 5:
                $row->bill_no = Payment::where('id',$row->entry_type_id)->value('voucher_no') ?? '';
                $row->long_narration = Payment::where('id',$row->entry_type_id)->value('long_narration') ?? '';
                $row->narration = PaymentDetails::where('id',$row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 6:
                $row->bill_no = Receipt::where('id',$row->entry_type_id)->value('voucher_no') ?? '';
                $row->long_narration = Receipt::where('id',$row->entry_type_id)->value('long_narration') ?? '';
                $row->narration = ReceiptDetails::where('id',$row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 7:
                $row->bill_no = Journal::where('id',$row->entry_type_id)->value('voucher_no') ?? '';
                $row->long_narration = Journal::where('id',$row->entry_type_id)->value('long_narration') ?? '';
                $row->narration = JournalDetails::where('id',$row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 8:
                $row->bill_no = Contra::where('id',$row->entry_type_id)->value('voucher_no') ?? '';
                $row->long_narration = Contra::where('id',$row->entry_type_id)->value('long_narration') ?? '';
                break;
        }
    }

    /** ================= CLOSING ================= */
    $closing = $runningBalance;

    return response()->json([
        "status" => true,
        "message" => "Ledger fetched successfully",
        "opening_balance" => $opening,
        "closing_balance" => $closing,
        "ledger" => $ledger,
        "party_id" => $party_id
    ]);
}



public function exportPdf(Request $request)
{
    // Validate request
    $request->validate([
        'party'     => 'required|integer',
        'from_date' => 'nullable|date',
        'to_date'   => 'nullable|date',
        'company_id' => 'required|integer'
    ]);

    $party_id  = $request->input('party');
    $from_date = $request->input('from_date');
    $to_date   = $request->input('to_date');
    $company_id = $request->input('company_id');

    // Fetch account and company details
    $account = Accounts::findOrFail($party_id);
    $comp    = Companies::findOrFail($company_id);
    $configuration = SaleInvoiceConfiguration::where('company_id', $comp->id)->first();

    // Fetch ledger entries within date range
    $ledger = DB::table('account_ledger')
        ->where('account_id', $party_id)
        ->where('entry_type', '!=', -1)
        ->where('status', 1)
        ->where('delete_status', '0')
        ->where('company_id', $comp->id);

    if ($from_date && $to_date) {
        $ledger = $ledger
            ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$from_date])
            ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$to_date]);
    }

    $ledger = $ledger->orderByRaw("STR_TO_DATE(txn_date, '%Y-%m-%d')")
                     ->orderBy('entry_type')
                     ->orderBy('entry_type_id')
                     ->get();

    // Enrich ledger entries
    foreach ($ledger as $key => $row) {
        // Map account
        $row->account = $row->map_account_id ? Accounts::where('id', $row->map_account_id)->value('account_name') : '';

        $row->bill_no = '';
        $row->narration = '';
        $row->long_narration = '';

        switch ($row->entry_type) {
            case 1: // Sales
                $action = Sales::find($row->entry_type_id);
                if ($action) {
                    $row->bill_no = $action->series_no . '/' . $action->financial_year . '/' . $action->voucher_no;
                }
                break;

            case 2: // Purchase
                $row->bill_no = Purchase::where('id', $row->entry_type_id)->value('voucher_no') ?? '';
                break;

            case 3: case 9: case 10: // Sales Return
                $action = SalesReturn::find($row->entry_type_id);
                if ($action) {
                    $row->bill_no = $action->sr_prefix . $action->sale_return_no;
                }
                break;

            case 4: case 12: case 13: // Purchase Return
                $action = PurchaseReturn::find($row->entry_type_id);
                if ($action) {
                    $row->bill_no = $action->series_no . '/' . $action->financial_year . '/DR' . $action->invoice_no;
                }
                break;

            case 5: // Payment
                $action = Payment::find($row->entry_type_id);
                $row->bill_no = $action->voucher_no ?? '';
                $row->long_narration = $action->long_narration ?? '';
                $row->narration = PaymentDetails::where('id', $row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 6: // Receipt
                $action = Receipt::find($row->entry_type_id);
                $row->bill_no = $action->voucher_no ?? '';
                $row->long_narration = $action->long_narration ?? '';
                $row->narration = ReceiptDetails::where('id', $row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 7: // Journal
                $action = Journal::find($row->entry_type_id);
                $row->bill_no = $action->voucher_no ?? '';
                $row->long_narration = $action->long_narration ?? '';
                $row->narration = JournalDetails::where('id', $row->entry_type_detail_id)->value('narration') ?? '';
                break;

            case 8: // Contra
                $action = Contra::find($row->entry_type_id);
                $row->bill_no = $action->voucher_no ?? '';
                $row->long_narration = $action->long_narration ?? '';
                $row->narration = ContraDetails::where('id', $row->entry_type_detail_id)->value('narration') ?? '';
                break;
        }
    }

    // Calculate opening balance
    $opening = $this->calculateOpeningBalance($party_id,$from_date,$company_id);

    // Generate PDF using Blade
    $pdf = Pdf::loadView('accountledger.ledger', [
        'ledger'        => $ledger,
        'opening'       => $opening,
        'accounts'       => $account,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'comp'          => $comp,
        'configuration' => $configuration,
    ]);

    $fileName = 'Account-Ledger-' . $account->account_name . '.pdf';
    return $pdf->download($fileName);
}

/**
 * Helper function to calculate opening balance
 */
private function calculateOpeningBalance($party_id, $from_date,$company_id)
{
    $opening = 0;

    if ($from_date) {
        $open_ledger = DB::table('account_ledger')
            ->select(DB::raw("SUM(debit) as debit, SUM(credit) as credit"))
            ->where('account_id', $party_id)
            ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') < STR_TO_DATE(?, '%Y-%m-%d')", [$from_date])
            ->where('status', 1)
            ->where('delete_status', '0')
            ->where('company_id', $company_id)
            ->first();

        $debit  = $open_ledger->debit ?? 0;
        $credit = $open_ledger->credit ?? 0;

        $opening = $debit - $credit;

        // Add default opening from entry_type -1 if exists
        $default_opening = AccountLedger::where('account_id', $party_id)
            ->where('company_id', $company_id)
            ->where('entry_type', -1)
            ->first();

        if ($default_opening) {
            if ($default_opening->credit) {
                $opening -= $default_opening->credit;
            } elseif ($default_opening->debit) {
                $opening += $default_opening->debit;
            }
        }
    } else {
        $default_opening = AccountLedger::where('account_id', $party_id)
            ->where('company_id', $company_id)
            ->where('entry_type', -1)
            ->first();

        if ($default_opening) {
            if ($default_opening->credit) {
                $opening = -$default_opening->credit;
            } elseif ($default_opening->debit) {
                $opening = $default_opening->debit;
            }
        }
    }

    return $opening;
}

}
