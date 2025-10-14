<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class DayBookController extends Controller
{
    public function index(Request $request)
    {

        $from_date = '2025-08-01';
            $to_date = '2025-08-01';
            $series = "Main";
            $company_id = Session::get('user_company_id');

            // 🔹 Fetch Sales (with & without GST)
            $sales = DB::table('sales')
                ->select(
                    'sales.id',
                    'sales.voucher_no',
                    'sales.date',
                    'manage_items.name as item_name',
                    'sale_descriptions.qty',
                    'sale_descriptions.amount'
                )
                ->join('sale_descriptions', 'sale_descriptions.sale_id', '=', 'sales.id')
                ->join('manage_items', 'manage_items.id', '=', 'sale_descriptions.goods_discription')
                ->where('series_no',$series)
                ->whereBetween('sales.date', [$from_date, $to_date]) // ✅ add '->' and fully qualify column
                ->where('sales.company_id',$company_id)
                ->get();


        // 🔹 Purchases
        $purchases = DB::table('purchases')
            ->select(
                'purchases.id',
                'purchases.voucher_no',
                'purchases.date',
                'manage_items.name as item_name',
                'purchase_descriptions.qty',
                'purchase_descriptions.amount'
            )
            ->join('purchase_descriptions', 'purchase_descriptions.purchase_id', '=', 'purchases.id')
            ->join('manage_items', 'manage_items.id', '=', 'purchase_descriptions.goods_discription')
            ->where('series_no',$series)
            ->whereBetween('purchases.date', [$from_date, $to_date])
            ->where('purchases.company_id',$company_id)
            ->get();

        // 🔹 Journal
        $journals = DB::table('journals')
            ->select('journals.date', 'accounts.account_name', 'journal_details.type', 'journal_details.debit')
            ->join('journal_details', 'journal_details.journal_id', '=', 'journals.id')
            ->join('accounts', 'accounts.id', '=', 'journal_details.account_name')
            ->where('series_no',$series)
            ->whereBetween('journals.date', [$from_date, $to_date])
            ->where('journals.company_id',$company_id)
            ->get();

        // 🔹 Receipts
        $receipts = DB::table('receipts')
                            ->join('receipt_details', 'receipt_details.receipt_id', '=', 'receipts.id')
                            ->join('accounts', 'accounts.id', '=', 'receipt_details.account_name')
                            ->where('series_no',$series)
                             ->whereBetween('receipts.date', [$from_date, $to_date])
                             ->where('receipts.company_id',$company_id)
                            ->select(
                                'receipts.id',
                                'receipts.date',
                                'receipts.voucher_no',
                                'receipt_details.type',
                                'accounts.account_name',
                                'receipt_details.debit',
                                'receipt_details.credit'
                            )
                            ->orderBy('receipts.date')
                            ->get();


        // 🔹 Payments
        $payments = DB::table('payments')
                            ->join('payment_details', 'payment_details.payment_id', '=', 'payments.id')
                            ->join('accounts', 'accounts.id', '=', 'payment_details.account_name')
                            ->where('series_no',$series)
                             ->whereBetween('payments.date', [$from_date, $to_date])
                             ->where('payments.company_id',$company_id)
                            ->select(
                                'payments.id',
                                'payments.date',
                                'payments.voucher_no',
                                'payments.series_no',
                                'payments.financial_year',
                                'payment_details.type',
                                'accounts.account_name',
                                'payment_details.debit',
                                'payment_details.credit'
                            )
                            ->orderBy('payments.date')
                            ->get();


        // 🔹 Debit Notes
        $debitNotes = DB::table('purchase_returns')
                            ->where('series_no',$series)
                             ->whereBetween('date', [$from_date, $to_date])
                             ->where('company_id',$company_id)
                             ->get();

        // 🔹 Credit Notes
        $creditNotes = DB::table('sales_returns')
                             ->where('series_no',$series)
                             ->whereBetween('date', [$from_date, $to_date])
                             ->where('company_id',$company_id)
                             ->get();

        // 🔹 Contra
       $contra = DB::table('contras')
                ->join('contra_details', 'contra_details.contra_id', '=', 'contras.id')
                ->join('accounts', 'accounts.id', '=', 'contra_details.account_name')
                ->where('series_no',$series)
                ->whereBetween('contras.date', [$from_date, $to_date])
                ->where('contras.company_id',$company_id)
                ->select(
                    'contras.id',
                    'contras.date',
                    'contras.voucher_no',
                    'contras.series_no',
                    'contras.financial_year',
                    'contra_details.type',
                    'accounts.account_name',
                    'contra_details.debit',
                    'contra_details.credit'
                )
                ->orderBy('contras.date')
                ->get();


        // 🔹 Stock Transfer
       $stockTransfers = DB::table('stock_transfers')
                        ->join('stock_transfer_descriptions', 'stock_transfer_descriptions.stock_transfer_id', '=', 'stock_transfers.id')
                        ->join('manage_items', 'manage_items.id', '=', 'stock_transfer_descriptions.goods_discription')
                        ->where('series_no',$series)
                        ->whereBetween('stock_transfers.transfer_date', [$from_date, $to_date])
                        ->where('stock_transfers.company_id',$company_id)
                        ->select(
                            'stock_transfers.transfer_date',
                            'stock_transfers.voucher_no',
                            'manage_items.name as item_name',
                            'stock_transfer_descriptions.qty',
                            'stock_transfer_descriptions.amount',
                            'stock_transfers.series_no',
                            'stock_transfers.series_no_to'
                        )
                        ->orderBy('stock_transfers.transfer_date')
                        ->get();

    return view('DayBook', compact(
    'sales',
    'purchases',
    'journals',
    'receipts',
    'payments',
    'creditNotes',
    'debitNotes',
    'contra',
    'stockTransfers'
));

    }
}
