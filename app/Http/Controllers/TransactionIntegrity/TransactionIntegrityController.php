<?php

namespace App\Http\Controllers\TransactionIntegrity;
use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use App\Models\ItemLedger;
use App\Models\AccountLedger;
use App\Models\Accounts;
use App\Models\BillSundrys;
use App\Http\Controllers\Controller;
use Session;

class TransactionIntegrityController extends Controller
{
    private function canEditTransaction($module, $model)
    {
        $merchantId = Session::get('merchant_id') ?? Session::get('user_id');
        $companyId  = Session::get('user_company_id');

        $canEditAfterApprove = \DB::table('merchant_privilege_mappings')
            ->where('merchant_id', $merchantId)
            ->where('company_id', $companyId)
            ->where('module_id', 256)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->exists();

        if (isset($model->delete) && $model->delete == 1) return false;

        if (isset($model->approved_status) && $model->approved_status == 1) {
            if (!$canEditAfterApprove) return false;
        }

        if ($module === 'sale') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) return false;

            if ($model->e_invoice_status != 0) return false;
            if ($model->e_waybill_status != 0) return false;
            if ($model->status != 1) return false;
            if (!empty($model->sale_order_id)) return false;

            if (!\Gate::allows('action-module', 61)) return false;
        }
        if ($module === 'purchase') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if (!\Gate::allows('action-module', 57)) {
                return false;
            }
        }
        if ($module === 'credit_note') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if ($model->status != '1') {
                return false;
            }

            if ($model->e_invoice_status != 0) {
                return false;
            }

            if (!\Gate::allows('action-module', 69)) {
                return false;
            }
        }
        if ($module === 'debit_note') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if ($model->status != '1') {
                return false;
            }

            if ($model->e_invoice_status != 0) {
                return false;
            }

            if (!\Gate::allows('action-module', 47)) {
                return false;
            }
        }
        if ($module === 'payment') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if (!\Gate::allows('action-module', 55)) {
                return false;
            }
        }
        if ($module === 'receipt') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if (!\Gate::allows('action-module', 59)) {
                return false;
            }
        }
        if ($module === 'journal') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if (!\Gate::allows('action-module', 53)) {
                return false;
            }

        }
        if ($module === 'contra') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->date)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if (!\Gate::allows('action-module', 45)) {
                return false;
            }

        }
        if ($module === 'stock_journal') {

            $financial_year = Session::get('default_fy');
            $y = explode("-", $financial_year);

            $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
            $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

            $month_arr = [
                $from . '-04', $from . '-05', $from . '-06', $from . '-07',
                $from . '-08', $from . '-09', $from . '-10', $from . '-11',
                $from . '-12', $to . '-01', $to . '-02', $to . '-03'
            ];

            if (!in_array(date('Y-m', strtotime($model->jdate)), $month_arr)) {
                return false;
            }

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if ($model->consumption_entry_status != 0) {
                return false;
            }

            if (!\Gate::allows('action-module', 63)) {
                return false;
            }
        }
        if ($module === 'stock_transfer') {

            if ($model->approved_status == 1 && !$canEditAfterApprove) {
                return false;
            }

            if ($model->e_waybill_status != 0) {
                return false;
            }

            if (!\Gate::allows('action-module', 65)) {
                return false;
            }

            return true;
        }
        return true;
    }
    private function getEditUrl($module, $id)
    {
        switch ($module) {
            case 'sale':
                return url('edit-sale/' . $id);
            case 'purchase':
                return url('purchase-edit/' . $id);
            case 'credit_note':
                return url('sale-return-edit/' . $id);
            case 'debit_note':
                return url('purchase-return-edit/' . $id);
            case 'payment':
                return url('payment/' . $id . '/edit');
            case 'receipt':
                return url('receipt/' . $id . '/edit');
            case 'journal':
                return url('journal/' . $id . '/edit');
            case 'contra':
                return url('contra/' . $id . '/edit');
            case 'stock_journal':
                return url('edit-stock-journal/' . $id);
            case 'stock_transfer':
                return url('stock-transfer/' . $id . '/edit');
        }

        return null;
    }
    public function index(Request $request)
    {
            $from = $request->from_date;
            $to   = $request->to_date;
            $type = $request->type;
            $company_id = Session::get('user_company_id');
            $data = [];
            $reason = "";
            $canEditAfterApprove = \DB::table('merchant_privilege_mappings')
                ->where('merchant_id', Session::get('user_id')) // or merchant_id session key
                ->where('company_id', Session::get('user_company_id'))
                ->where('module_id', 256)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->exists();
        if ($type == 'sale') {
            $sales = Sales::where('company_id', $company_id)
                //->where('id',45953)
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                ->get();

            if ($sales->isEmpty()) {
                return [];
            }
            $saleIds = $sales->pluck('id')->toArray();

            $saleItems = SaleDescription::whereIn('sale_id', $saleIds)
                ->get()
                ->groupBy('sale_id');
            
            $itemLedgers = ItemLedger::where('source', 1)
                ->whereIn('source_id', $saleIds)
                ->get()
                ->groupBy('source_id');
            
            $saleSundries = SaleSundry::whereIn('sale_id', $saleIds)
                ->get()
                ->groupBy('sale_id');
            
            $accountLedgers = AccountLedger::where('entry_type', 1)
                ->whereIn('entry_type_id', $saleIds)
                ->get()
                ->groupBy('entry_type_id');
            
            $billIds = SaleSundry::whereIn('sale_id', $saleIds)
                ->pluck('bill_sundry')
                ->unique()
                ->toArray();
            
            $bills = BillSundrys::whereIn('id', $billIds)
                ->get()
                ->keyBy('id');
            
            $partyIds = $sales->pluck('party')->unique()->toArray();
            
            $parties = Accounts::whereIn('id', $partyIds)
                ->pluck('account_name', 'id');

            // echo "<pre>";
            // print_r($sales->toArray());
            foreach ($sales as $sale) {
            
                $isMismatch = false;
                $reasons = [];
            if ($sale->status == 2 || $sale->delete == 1) {

                $hasActiveItems = SaleDescription::where('sale_id', $sale->id)
                    ->where('delete', '0')
                    ->exists();

                $hasActiveSundries = SaleSundry::where('sale_id', $sale->id)
                    ->where('delete', '0')
                    ->exists();

                $hasActiveItemLedger = ItemLedger::where('source', 1)
                    ->where('source_id', $sale->id)
                    ->where('delete_status', '0')
                    ->exists();

                $hasActiveAccountLedger = AccountLedger::where('entry_type', 1)
                    ->where('entry_type_id', $sale->id)
                    ->where('delete_status', '0')
                    ->exists();

                if ($sale->status == 2) {
                    if (
                        $hasActiveItems ||
                        $hasActiveSundries ||
                        $hasActiveItemLedger ||
                        $hasActiveAccountLedger ||
                        round((float)$sale->total, 2) != 0
                    ) {
                        $isMismatch = true;
                    }

                } elseif ($sale->delete == 1) {
                    if (
                        $hasActiveItems ||
                        $hasActiveSundries ||
                        $hasActiveItemLedger ||
                        $hasActiveAccountLedger
                    ) {
                        $isMismatch = true;
                    }
                }

                    if ($isMismatch) {

                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                        $reasons = array_unique($reasons);

                        $sale->party_name = $parties[$sale->party] ?? '';
                        $sale->module = 'Sale';
                        $sale->reference = $sale->voucher_no_prefix ?? '';
                        $sale->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('sale', $sale)) {
                            $sale->edit_url = $this->getEditUrl('sale', $sale->id);
                        } else {
                            $sale->edit_url = null;
                        }
                        $data[] = $sale;
                    }

                    continue;
            }
                $items = $saleItems[$sale->id] ?? collect();
                $itemLedger = $itemLedgers[$sale->id] ?? collect();
                $sundries = $saleSundries[$sale->id] ?? collect();
                $ledgers = $accountLedgers[$sale->id] ?? collect();
                // ensure ledger exists for active sale
                if ($sale->status != 2 && $sale->delete != 1 && $ledgers->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }
                $totalDebit = round((float)$ledgers->sum('debit'), 2);
                $totalCredit = round((float)$ledgers->sum('credit'), 2);

                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }
                if ($items->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                if ($itemLedger->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Item ledger missing';
                }

                if ($items->count() !== $itemLedger->count()) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                $itemsArr = $items->map(fn($r) => [
                    'item_id' => (int)$r->goods_discription,
                    'qty'     => round((float)$r->qty, 2),
                    'price'   => round((float)$r->price, 2),
                    'amount'  => round((float)$r->amount, 2),
                ])->sortBy('item_id')->values()->toArray();
            
                $ledgerArr = $itemLedger->map(fn($r) => [
                    'item_id' => (int)$r->item_id,
                    'qty'     => round((float)$r->out_weight, 2),
                    'price'   => round((float)$r->price, 2),
                    'amount'  => round((float)$r->total_price, 2),
                ])->sortBy('item_id')->values()->toArray();
            
                if (json_encode($itemsArr) !== json_encode($ledgerArr)) {
                
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                $itemTotal = round((float)$items->sum('amount'), 2);
                $itemLedgerTotal = round((float)$itemLedger->sum('total_price'), 2);

                if ($itemTotal !== $itemLedgerTotal) {
                    $isMismatch = true;
                    $reasons[] = 'Item total mismatch';
                }
                $uiTotal = 0;
                $expectedSales = $itemTotal;
                $expectedSundryLedger = [];
            
                foreach ($sundries as $s) {
                    $bill = $bills[$s->bill_sundry] ?? null;
                    if (!$bill) {
                        
                        $isMismatch = true;
                        $reasons[] = 'Bill sundry not found';
                        continue;
                    }
                    $amt = round((float)$s->amount, 2);
                    if ($bill->bill_sundry_type == 'additive') {
                        $uiTotal += $amt;
                    } else {
                        $uiTotal -= $amt;
                    }
                    if ($bill->adjust_sale_amt == 'Yes') {
                        if ($bill->bill_sundry_type == 'additive') {
                            $expectedSales += $amt;
                        } else {
                            $expectedSales -= $amt;
                        }
                    } else {
                        $expectedSundryLedger[] = [
                            'account_id' => (int)$bill->sale_amt_account,
                            'debit'      => $bill->bill_sundry_type == 'subtractive' ? $amt : 0,
                            'credit'     => $bill->bill_sundry_type == 'additive' ? $amt : 0,
                        ];
                    }
                }
            
                $calculatedTotal = round($itemTotal + $uiTotal, 2);
                //echo $uiTotal;
                if (round((float)$sale->total, 2) != $calculatedTotal) {
                    
                    $isMismatch = true;
                    $reasons[] = 'Total mismatch with items and sundries';
                }

                $partyLedgers = $ledgers->where('account_id', $sale->party);

                if ($partyLedgers->count() !== 1) {
                    $isMismatch = true;
                    $reasons[] = 'Party ledger missing or duplicate';
                } else {
                    $partyLedger = $partyLedgers->first();

                    if (
                        round((float)$partyLedger->debit, 2) != round((float)$sale->total, 2) ||
                        round((float)$partyLedger->credit, 2) != 0
                    ) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger amount mismatch';
                    }
                }

                $salesLedgers = $ledgers->where('account_id', 35);

                if ($salesLedgers->count() !== 1) {
                    $isMismatch = true;
                    $reasons[] = 'Sales ledger missing or duplicate';
                } else {
                    $salesLedger = $salesLedgers->first();

                    if (
                        round((float)$salesLedger->debit, 2) != 0 ||
                        round((float)$salesLedger->credit, 2) != round($expectedSales, 2)
                    ) {
                        $isMismatch = true;
                        $reasons[] = 'Sales ledger mismatch';
                    }
                }

                $actualSundry = $ledgers
                    ->whereNotIn('account_id', [$sale->party, 35])
                    ->map(fn($r) => [
                        'account_id' => (int)$r->account_id,
                        'debit'      => round((float)$r->debit, 2),
                        'credit'     => round((float)$r->credit, 2),
                    ])
                    ->sortBy('account_id')
                    ->values()
                    ->toArray();
            
                $expectedSundry = collect($expectedSundryLedger)
                    ->sortBy('account_id')
                    ->values()
                    ->toArray();
                $normalize = function ($rows) {
                    return collect($rows)
                        ->groupBy('account_id')
                        ->map(function ($group, $accountId) {
                            return [
                                'account_id' => (int)$accountId,
                                'debit'  => round($group->sum('debit'), 2),
                                'credit' => round($group->sum('credit'), 2),
                            ];
                        })
                        ->sortBy('account_id')
                        ->values()
                        ->toArray();
                };
                
                $actualCheck   = $normalize($actualSundry);
                $expectedCheck = $normalize($expectedSundry);
                
                if ($actualCheck != $expectedCheck) {
                
                    $isMismatch = true;
                    $reasons[] = 'Bill sundry ledger mismatch';
                }
            $validAccounts = collect([$sale->party, 35])
                ->merge(collect($expectedSundryLedger)->pluck('account_id'))
                ->merge(
                    $sundries->map(function ($s) use ($bills) {
                        $bill = $bills[$s->bill_sundry] ?? null;
                        if (!$bill) return null;

                        return $bill->sale_amt_account;
                    })
                )
                ->filter()
                ->unique();

            $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

            if ($invalidLedger->isNotEmpty()) {
                $isMismatch = true;

                // only show if NOT already sundry mismatch
                if (!in_array('Bill sundry ledger mismatch', $reasons)) {
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }
            }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);

                    $sale->party_name = $parties[$sale->party] ?? '';
                    $sale->module = 'Sale';
                    $sale->reference = $sale->voucher_no_prefix ?? '';
                    $sale->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('sale', $sale)) {
                        $sale->edit_url = $this->getEditUrl('sale', $sale->id);
                    } else {
                        $sale->edit_url = null;
                    }
                    $data[] = $sale;
                }
            }
        }
        if ($type == 'sale1') {
            $sales = Sales::where('delete', '0')
                            //->where('id',43390)
                            ->where('company_id', $company_id);

            if ($from) $sales->whereDate('date', '>=', $from);
            if ($to)   $sales->whereDate('date', '<=', $to);
            $sales = $sales->get();
            foreach ($sales as $sale) {
                $isMismatch = false;

                // =========================
                // 1. ITEMS vs ITEM LEDGER (STRICT ARRAY MATCH)
                // =========================
                $items = SaleDescription::where('sale_id', $sale->id)->get();

                $itemLedger = ItemLedger::where([
                    'source'    => 1,
                    'source_id' => $sale->id
                ])->get();

                $itemsArr = $items->map(function ($i) {
                    return [
                        'item_id' => (int)$i->goods_discription,
                        'qty'     => (float)$i->qty,
                        'price'   => (float)$i->price,
                        'amount'  => (float)$i->amount,
                    ];
                })->sort()->values()->toArray();

                $ledgerArr = $itemLedger->map(function ($l) {
                    return [
                        'item_id' => (int)$l->item_id,
                        'qty'     => (float)$l->out_weight,
                        'price'   => (float)$l->price,
                        'amount'  => (float)$l->total_price,
                    ];
                })->sort()->values()->toArray();

                if ($itemsArr !== $ledgerArr) {
                    $isMismatch = true;
                }

                // =========================
                // 2. SUNDRY TOTAL (UI EXACT)
                // =========================
                $sundries = SaleSundry::where('sale_id', $sale->id)->get();

                $uiTotal = 0;
                $expectedSundryLedger = [];

                foreach ($sundries as $s) {

                    $bill = \App\Models\BillSundrys::find($s->bill_sundry);

                    if (!$bill) {
                        $isMismatch = true;
                        continue;
                    }

                    // UI calculation
                    if ($bill->bill_sundry_type == 'additive') {
                        $uiTotal += (float)$s->amount;
                    } else {
                        $uiTotal -= (float)$s->amount;
                    }

                    // Expected ledger (adjust_sale_amt = No)
                    if ($bill->adjust_sale_amt == 'No') {

                        $expectedSundryLedger[] = [
                            'account_id' => (int)$bill->sale_amt_account,
                            'debit'  => $bill->bill_sundry_type == 'subtractive' ? (float)$s->amount : 0.0,
                            'credit' => $bill->bill_sundry_type == 'additive' ? (float)$s->amount : 0.0,
                        ];
                    }
                }

                // =========================
                // 3. SUNDRY LEDGER STRICT MATCH
                // =========================
                $actualSundryLedger = AccountLedger::where([
                    'entry_type'    => 1,
                    'entry_type_id' => $sale->id
                ])
                ->whereNotIn('account_id', [$sale->party, 35])
                ->get()
                ->map(function ($l) {
                    return [
                        'account_id' => (int)$l->account_id,
                        'debit'      => (float)$l->debit,
                        'credit'     => (float)$l->credit,
                    ];
                })->sort()->values()->toArray();

                $expectedSundryLedger = collect($expectedSundryLedger)
                    ->sort()->values()->toArray();

                if ($expectedSundryLedger !== $actualSundryLedger) {
                    $isMismatch = true;
                    
                }

                // =========================
                // 4. TOTAL STRICT
                // =========================
                $itemTotal = (float)$items->sum('amount');
                $calculatedTotal = $itemTotal + $uiTotal;
                
                if (round($sale->total, 2) != round($calculatedTotal, 2)) {
                    $isMismatch = true;
                }

                // =========================
                // 5. PARTY LEDGER STRICT
                // =========================
                $partyLedger = AccountLedger::where([
                    'entry_type'    => 1,
                    'entry_type_id' => $sale->id,
                    'account_id'    => $sale->party
                ])->get();

                $partyArr = $partyLedger->map(function ($l) {
                    return [
                        'debit'  => (float)$l->debit,
                        'credit' => (float)$l->credit,
                    ];
                })->values()->toArray();

                $expectedParty = [[
                    'debit'  => (float)$sale->total,
                    'credit' => 0.0
                ]];

                if ($partyArr !== $expectedParty) {
                    $isMismatch = true;
                }

                // =========================
                // 6. SALES LEDGER STRICT
                // =========================
                $expectedSales = $itemTotal;

                foreach ($sundries as $s) {

                    $bill = \App\Models\BillSundrys::find($s->bill_sundry);
                    if (!$bill) continue;

                    if ($bill->adjust_sale_amt == 'Yes') {

                        if ($bill->bill_sundry_type == 'additive') {
                            $expectedSales += (float)$s->amount;
                        } else {
                            $expectedSales -= (float)$s->amount;
                        }
                    }
                }

                $salesLedger = AccountLedger::where([
                    'entry_type'    => 1,
                    'entry_type_id' => $sale->id,
                    'account_id'    => 35
                ])->get();

                $salesArr = $salesLedger->map(function ($l) {
                    return [
                        'debit'  => (float)$l->debit,
                        'credit' => (float)$l->credit,
                    ];
                })->values()->toArray();

                $expectedSalesArr = [[
                    'debit'  => 0.0,
                    'credit' => (float)$expectedSales
                ]];

                if (round($salesArr,2) !== round($expectedSalesArr,2)) {
                    
                    $isMismatch = true;
                }

                // =========================
                // FINAL PUSH
                // =========================
                if ($isMismatch) {

                    $party = Accounts::select('account_name')->find($sale->party);
                    $sale->party_name = $party->account_name ?? '';
                    $sale->module = 'Sale';
                    $sale->reference = $sale->voucher_no_prefix ?? '';
                    $data[] = $sale;
                }
            }
        }
        // =========================
        // PURCHASE CHECK
        // =========================
        if ($type == 'purchase') {
            $purchases = \App\Models\Purchase::where('company_id', $company_id)
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                ->get();

            if ($purchases->isEmpty()) {
                return [];
            }

            $purchaseIds = $purchases->pluck('id')->toArray();

            $purchaseItems = \App\Models\PurchaseDescription::whereIn('purchase_id', $purchaseIds)
                ->get()
                ->groupBy('purchase_id');

            $itemLedgers = ItemLedger::where('source', 2) // DIFF FROM SALE: source=2
                ->whereIn('source_id', $purchaseIds)
                ->get()
                ->groupBy('source_id');

            $purchaseSundries = \App\Models\PurchaseSundry::whereIn('purchase_id', $purchaseIds)
                ->get()
                ->groupBy('purchase_id');

            $accountLedgers = AccountLedger::where('entry_type', 2) 
                ->whereIn('entry_type_id', $purchaseIds)
                ->get()
                ->groupBy('entry_type_id');

            $billIds = \App\Models\PurchaseSundry::whereIn('purchase_id', $purchaseIds)
                ->pluck('bill_sundry')
                ->unique()
                ->toArray();

            $bills = BillSundrys::whereIn('id', $billIds)
                ->get()
                ->keyBy('id');

            $partyIds = $purchases->pluck('party')->unique()->toArray();

            $parties = Accounts::whereIn('id', $partyIds)
                ->pluck('account_name', 'id');

            foreach ($purchases as $purchase) {
                $isMismatch = false;
                $reasons = [];

                if ($purchase->delete == 1) { // DIFF FROM SALE: no status=2 check
                    $hasActiveItems = \App\Models\PurchaseDescription::where('purchase_id', $purchase->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveSundries = \App\Models\PurchaseSundry::where('purchase_id', $purchase->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveItemLedger = ItemLedger::where('source', 2) // DIFF FROM SALE: source=2
                        ->where('source_id', $purchase->id)
                        ->where('delete_status', '0')
                        ->exists();

                    $hasActiveAccountLedger = AccountLedger::where('entry_type', 2) // DIFF FROM SALE: entry_type=2
                        ->where('entry_type_id', $purchase->id)
                        ->where('delete_status', '0')
                        ->exists();

                    if (
                        $hasActiveItems ||
                        $hasActiveSundries ||
                        $hasActiveItemLedger ||
                        $hasActiveAccountLedger
                    ) {
                        $isMismatch = true;
                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $purchase->party_name = $parties[$purchase->party] ?? '';
                        $purchase->module = 'Purchase';
                        $purchase->reference = $purchase->voucher_no ?? '';
                        $purchase->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('purchase', $purchase)) {
                            $purchase->edit_url = $this->getEditUrl('purchase', $purchase->id);
                        } else {
                            $purchase->edit_url = null;
                        }
                        $data[] = $purchase;
                    }

                    continue;
                }

                $items = $purchaseItems[$purchase->id] ?? collect();
                $itemLedger = $itemLedgers[$purchase->id] ?? collect();
                $sundries = $purchaseSundries[$purchase->id] ?? collect();
                $ledgers = $accountLedgers[$purchase->id] ?? collect();

                if ($purchase->delete != 1 && $ledgers->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                $totalDebit = round((float)$ledgers->sum('debit'), 2);
                $totalCredit = round((float)$ledgers->sum('credit'), 2);
                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }

                if ($items->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                if ($itemLedger->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Item ledger missing';
                }

                if ($items->count() !== $itemLedger->count()) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                $itemsArr = $items->map(fn($r) => [
                    'item_id' => (int)$r->goods_discription,
                    'qty'     => round((float)$r->qty, 2),
                    'price'   => round((float)$r->price, 2),
                    'amount'  => round((float)$r->amount, 2),
                ])->sortBy('item_id')->values()->toArray();

                $ledgerArr = $itemLedger->map(fn($r) => [
                    'item_id' => (int)$r->item_id,
                    'qty'     => round((float)$r->in_weight, 2), // DIFF FROM SALE: in_weight
                    'price'   => round((float)$r->price, 2),
                    'amount'  => round((float)$r->total_price, 2),
                ])->sortBy('item_id')->values()->toArray();

                if (json_encode($itemsArr) !== json_encode($ledgerArr)) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                $itemTotal = round((float)$items->sum('amount'), 2);
                $itemLedgerTotal = round((float)$itemLedger->sum('total_price'), 2);

                if ($itemTotal !== $itemLedgerTotal) {
                    $isMismatch = true;
                    $reasons[] = 'Item total mismatch';
                }

                $uiTotal = 0;
                $expectedPurchase = $itemTotal;
                $expectedSundryLedger = [];

                foreach ($sundries as $s) {
                    $bill = $bills[$s->bill_sundry] ?? null;
                    if (!$bill) {
                        $isMismatch = true;
                        $reasons[] = 'Bill sundry not found';
                        continue;
                    }

                    $amt = round((float)$s->amount, 2);

                    // UI total behavior mirrors purchase flow
                    if ($bill->bill_sundry_type == 'additive') {
                        $uiTotal += $amt;
                    } else {
                        $uiTotal -= $amt;
                    }

                    // Purchase main posting amount behavior
                    if ($bill->adjust_purchase_amt == 'Yes') { // DIFF FROM SALE: adjust_purchase_amt
                        if ($bill->bill_sundry_type == 'additive') {
                            $expectedPurchase += $amt;
                        } else {
                            $expectedPurchase -= $amt;
                        }
                    } else {
                        // Purchase sundry ledger behavior from PurchaseController
                        $expectedSundryLedger[] = [
                            'account_id' => (int)$bill->purchase_amt_account, // DIFF FROM SALE: purchase_amt_account
                            'debit'      => $bill->nature_of_sundry == 'ROUNDED OFF (-)' ? 0 : $amt,
                            'credit'     => $bill->nature_of_sundry == 'ROUNDED OFF (-)' ? $amt : 0,
                        ];
                    }
                }

                $calculatedTotal = round($itemTotal + $uiTotal, 2);
                if (round((float)$purchase->total, 2) != $calculatedTotal) {
                    $isMismatch = true;
                    $reasons[] = 'Total mismatch with items and sundries';
                }

                $partyLedgers = $ledgers->where('account_id', $purchase->party);

                if ($partyLedgers->count() !== 1) { // duplicate/missing party ledger detection
                    $isMismatch = true;
                    $reasons[] = 'Party ledger missing or duplicate';
                } else {
                    $partyLedger = $partyLedgers->first();

                    if (
                        round((float)$partyLedger->debit, 2) != 0 || // DIFF FROM SALE: reversed
                        round((float)$partyLedger->credit, 2) != round((float)$purchase->total, 2)
                    ) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger amount mismatch';
                    }
                }

                $purchaseLedgers = $ledgers->where('account_id', 36); 

                if ($purchaseLedgers->count() !== 1) { 
                    $isMismatch = true;
                    $reasons[] = 'Main account ledger mismatch';
                } else {
                    $purchaseLedger = $purchaseLedgers->first();

                    if (
                        round((float)$purchaseLedger->debit, 2) != round($expectedPurchase, 2) || 
                        round((float)$purchaseLedger->credit, 2) != 0
                    ) {
                        $isMismatch = true;
                        $reasons[] = 'Main account ledger mismatch';
                    }
                }

                $actualSundry = $ledgers
                    ->whereNotIn('account_id', [$purchase->party, 36])
                    ->map(fn($r) => [
                        'account_id' => (int)$r->account_id,
                        'debit'      => round((float)$r->debit, 2),
                        'credit'     => round((float)$r->credit, 2),
                    ])
                    ->sortBy('account_id')
                    ->values()
                    ->toArray();

                $expectedSundry = collect($expectedSundryLedger)
                    ->sortBy('account_id')
                    ->values()
                    ->toArray();

                $normalize = function ($rows) {
                    return collect($rows)
                        ->groupBy('account_id')
                        ->map(function ($group, $accountId) {
                            return [
                                'account_id' => (int)$accountId,
                                'debit'      => round($group->sum('debit'), 2),
                                'credit'     => round($group->sum('credit'), 2),
                            ];
                        })
                        ->sortBy('account_id')
                        ->values()
                        ->toArray();
                };

                $actualCheck = $normalize($actualSundry);
                $expectedCheck = $normalize($expectedSundry);

                if ($actualCheck != $expectedCheck) {
                    $isMismatch = true;
                    $reasons[] = 'Bill sundry ledger mismatch';
                }

                $validAccounts = collect([$purchase->party, 36]) 
                    ->merge(collect($expectedSundryLedger)->pluck('account_id'))
                    ->merge(
                        $sundries->map(function ($s) use ($bills) {
                            $bill = $bills[$s->bill_sundry] ?? null;
                            return $bill ? $bill->sale_amt_account ?? $bill->purchase_amt_account : null;
                        })
                    )
                    ->filter()
                    ->unique();

                $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

                if ($invalidLedger->isNotEmpty()) {
                    $isMismatch = true;
                    if (!in_array('Bill sundry ledger mismatch', $reasons)) {
                        $reasons[] = 'Invalid/orphan ledger entries found';
                    }
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $purchase->party_name = $parties[$purchase->party] ?? '';
                    $purchase->module = 'Purchase';
                    $purchase->reference = $purchase->voucher_no ?? '';
                    $purchase->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('purchase', $purchase)) {
                        $purchase->edit_url = $this->getEditUrl('purchase', $purchase->id);
                    } else {
                        $purchase->edit_url = null;
                    }
                    $data[] = $purchase;
                }
            }
        }
        // =========================
        // CREDIT NOTE (SALE RETURN)
        // =========================
        if ($type == 'credit_note') {

            $returns = \App\Models\SalesReturn::where('company_id', $company_id)
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                ->get();
        
            if ($returns->isEmpty()) {
            } else {
        
                $returnIds = $returns->pluck('id')->toArray();

                $crItems = \App\Models\SaleReturnDescription::whereIn('sale_return_id', $returnIds)
                    ->get()->groupBy('sale_return_id');
        
                $crItemLedgers = ItemLedger::where('source', 4)
                    ->whereIn('source_id', $returnIds)
                    ->get()->groupBy('source_id');
        
                $crSundries = \App\Models\SaleReturnSundry::whereIn('sale_return_id', $returnIds)
                    ->get()->groupBy('sale_return_id');
        
                $crWithoutEntries = \App\Models\SaleReturnWithoutGstEntry::whereIn('sale_return_id', $returnIds)
                    ->get()->groupBy('sale_return_id');
        
                $crLedgers3  = AccountLedger::where('entry_type', 3)
                    ->whereIn('entry_type_id', $returnIds)
                    ->get()->groupBy('entry_type_id');
        
                $crLedgers9  = AccountLedger::where('entry_type', 9)
                    ->whereIn('entry_type_id', $returnIds)
                    ->get()->groupBy('entry_type_id');
        
                $crLedgers10 = AccountLedger::where('entry_type', 10)
                    ->whereIn('entry_type_id', $returnIds)
                    ->get()->groupBy('entry_type_id');

                $crBillIds = \App\Models\SaleReturnSundry::whereIn('sale_return_id', $returnIds)
                    ->pluck('bill_sundry')
                    ->unique()
                    ->toArray();
        
                $gstIds = BillSundrys::whereIn('nature_of_sundry', ['IGST', 'CGST', 'SGST'])
                    ->pluck('id')
                    ->toArray();
        
                $crBillIds = array_unique(array_merge($crBillIds, $gstIds));
        
                $crBills = BillSundrys::whereIn('id', $crBillIds)
                    ->get()
                    ->keyBy('id');
        
                $crPartyIds = $returns->pluck('party')->unique()->toArray();
                $crParties  = Accounts::whereIn('id', $crPartyIds)->pluck('account_name', 'id');

                $crLinkedInvoiceIds = $returns->pluck('sale_bill_id')   
                    ->filter(fn($id) => !empty($id))
                    ->unique()
                    ->values()
                    ->toArray();
        
                $crSaleInvoiceIds = empty($crLinkedInvoiceIds)
                    ? []
                    : \App\Models\Sales::whereIn('id', $crLinkedInvoiceIds)
                        ->pluck('id')
                        ->map(fn($id) => (int)$id)
                        ->toArray();
        
                $crPurchaseInvoiceIds = empty($crLinkedInvoiceIds)
                    ? []
                    : \App\Models\Purchase::whereIn('id', $crLinkedInvoiceIds)
                        ->pluck('id')
                        ->map(fn($id) => (int)$id)
                        ->toArray();
        
                $crSaleInvoiceIdSet     = array_fill_keys($crSaleInvoiceIds,     true);
                $crPurchaseInvoiceIdSet = array_fill_keys($crPurchaseInvoiceIds, true);

                foreach ($returns as $cr) {
        
                    $isMismatch = false;
                    $reasons = [];

                    if ($cr->status == 2 || $cr->delete == 1) {
        
                        $hasActiveItems = \App\Models\SaleReturnDescription::where('sale_return_id', $cr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveSundries = \App\Models\SaleReturnSundry::where('sale_return_id', $cr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveWithoutEntries = \App\Models\SaleReturnWithoutGstEntry::where('sale_return_id', $cr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveAccountLedger = AccountLedger::where('entry_type_id', $cr->id)
                            ->whereIn('entry_type', [3, 9, 10])
                            ->where('delete_status', '0')
                            ->exists();
        
                        $hasActiveItemLedger = ItemLedger::where('source', 4)
                            ->where('source_id', $cr->id)
                            ->where('delete_status', '0')
                            ->exists();
        
                        if ($cr->status == 2) {
                            if (
                                $hasActiveItems ||
                                $hasActiveSundries ||
                                $hasActiveWithoutEntries ||
                                $hasActiveAccountLedger ||
                                $hasActiveItemLedger ||
                                round((float)$cr->total, 2) != 0
                            ) {
                                $isMismatch = true;
                                $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                            }
                        } elseif ($cr->delete == 1) {
                            if (
                                $hasActiveItems ||
                                $hasActiveSundries ||
                                $hasActiveWithoutEntries ||
                                $hasActiveAccountLedger ||
                                $hasActiveItemLedger
                            ) {
                                $isMismatch = true;
                                $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                            }
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $cr->party_name = $crParties[$cr->party] ?? '';
                            $cr->module     = 'Credit Note';
                            $cr->reference  = $cr->sr_prefix ?? '';
                            $cr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('credit_note', $cr)) {
                                $cr->edit_url = $this->getEditUrl('credit_note', $cr->id);
                            } else {
                                $cr->edit_url = null;
                            }

                            $data[] = $cr;
                        }
                        continue;
                    }
        
                    // CASE 1 — WITHOUT GST  (entry_type = 9)
                    if ($cr->sr_nature == 'WITHOUT GST') {
        
                        $ledgers = $crLedgers9[$cr->id] ?? collect();
        
                        if ($ledgers->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Ledger entries missing';
                        }
        
                        $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                        $totalCredit = round((float)$ledgers->sum('credit'), 2);
                        if ($totalDebit !== $totalCredit) {
                            $isMismatch = true;
                            $reasons[] = 'Debit and Credit not equal';
                        }
        
                        $items = $crItems[$cr->id] ?? collect();
                        if ($items->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        }
        
                        $itemLedger = $crItemLedgers[$cr->id] ?? collect();
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        }

                        $entries = $crWithoutEntries[$cr->id] ?? collect();
        
                        if ($entries->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        } else {
                            $expectedTotal = 0;
                            $expected = [];
        
                            foreach ($entries as $e) {
                                $expected[] = [
                                    'account_id' => (int)$e->account_name,
                                    'debit'      => round((float)$e->debit, 2),
                                    'credit'     => 0.0,
                                ];
                                $expectedTotal += round((float)$e->debit, 2);
                            }
        
                            $expected[] = [
                                'account_id' => (int)$cr->party,
                                'debit'      => 0.0,
                                'credit'     => round($expectedTotal, 2),
                            ];
        
                            $normalize = function ($rows) {
                                return collect($rows)
                                    ->groupBy('account_id')
                                    ->map(fn($g, $id) => [
                                        'account_id' => (int)$id,
                                        'debit'      => round($g->sum('debit'),  2),
                                        'credit'     => round($g->sum('credit'), 2),
                                    ])
                                    ->sortBy('account_id')
                                    ->values()
                                    ->toArray();
                            };
        
                            $actualArr = $normalize(
                                $ledgers->map(fn($l) => [
                                    'account_id' => (int)$l->account_id,
                                    'debit'      => round((float)$l->debit,  2),
                                    'credit'     => round((float)$l->credit, 2),
                                ])->toArray()
                            );
                            $expectedArr = $normalize($expected);
        
                            if ($actualArr != $expectedArr) {
                                $isMismatch = true;
                                $reasons[] = 'Without GST structure mismatch';
                            }
        
                            if (round((float)$cr->total, 2) !== round($expectedTotal, 2)) {
                                $isMismatch = true;
                                $reasons[] = 'Without GST structure mismatch';
                            }
                        }
        
                        $validAccountsWog = collect([$cr->party])
                            ->merge(($crWithoutEntries[$cr->id] ?? collect())->pluck('account_name'))
                            ->unique();
                        $orphanWog = $ledgers->whereNotIn('account_id', $validAccountsWog);
                        if ($orphanWog->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Invalid/orphan ledger entries found';
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $cr->party_name = $crParties[$cr->party] ?? '';
                            $cr->module     = 'Credit Note';
                            $cr->reference  = $cr->sr_prefix ?? '';
                            $cr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('credit_note', $cr)) {
                                $cr->edit_url = $this->getEditUrl('credit_note', $cr->id);
                            } else {
                                $cr->edit_url = null;
                            }

                            $data[] = $cr;
                        }
                        continue;
                    }
        
                    // CASE 2 — WITH GST + WITHOUT ITEM  (entry_type = 10)
                    if ($cr->sr_nature == 'WITH GST' && $cr->sr_type == 'WITHOUT ITEM') {
        
                        $ledgers = $crLedgers10[$cr->id] ?? collect();
        
                        if ($ledgers->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Ledger entries missing';
                        }
        
                        $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                        $totalCredit = round((float)$ledgers->sum('credit'), 2);
                        if ($totalDebit !== $totalCredit) {
                            $isMismatch = true;
                            $reasons[] = 'Debit and Credit not equal';
                        }
        
                        $items = $crItems[$cr->id] ?? collect();
                        if ($items->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        $itemLedger = $crItemLedgers[$cr->id] ?? collect();
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        $entries = $crWithoutEntries[$cr->id] ?? collect();

                        $crInvoiceType     = (strtoupper((string)$cr->voucher_type) === 'PURCHASE') ? 'purchase' : 'sale';
                        $crLinkedInvoiceId = (int)($cr->sale_bill_id ?? 0);
                        if ($crLinkedInvoiceId > 0) {
                            if (isset($crSaleInvoiceIdSet[$crLinkedInvoiceId])) {
                                $crInvoiceType = 'sale';
                            } elseif (isset($crPurchaseInvoiceIdSet[$crLinkedInvoiceId])) {
                                $crInvoiceType = 'purchase';
                            }
                        }
        
                        $expected = [];
        
                        $expected[] = [
                            'account_id' => (int)$cr->party,
                            'debit'      => 0.0,
                            'credit'     => round((float)$cr->total, 2),
                        ];
        
                        foreach ($entries as $e) {
                            $expected[] = [
                                'account_id' => (int)$e->account_name,
                                'debit'      => round((float)$e->debit, 2),
                                'credit'     => 0.0,
                            ];
                        }

                        $gstList = [];
        
                        if ($cr->tax_igst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'IGST')->value('id');
                            if ($id) $gstList[] = ['id' => $id, 'amt' => $cr->tax_igst];
                        }
        
                        if ($cr->tax_cgst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'CGST')->value('id');
                            if ($id) $gstList[] = ['id' => $id, 'amt' => $cr->tax_cgst];
                        }
        
                        if ($cr->tax_sgst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'SGST')->value('id');
                            if ($id) $gstList[] = ['id' => $id, 'amt' => $cr->tax_sgst];
                        }
        
                        foreach ($gstList as $g) {
                            $bill = $crBills[$g['id']] ?? null;
                            if (!$bill) {
                                $isMismatch = true;
                                $reasons[] = 'GST calculation mismatch';
                                continue;
                            }
        
                            $amt = round((float)$g['amt'], 2);
        
                            $effectiveType = strtolower(trim((string)$cr->voucher_type));

                            if ($effectiveType === 'sale') {
                                $account = $bill->sale_amt_account;
                            } else {
                                $account = $bill->purchase_amt_account;
                            }
        
                            $expected[] = [
                                'account_id' => (int)$account,
                                'debit'      => $amt,
                                'credit'     => 0.0,
                            ];
                        }

                        $roundOffPlusId  = BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (+)')->value('id');
                        $roundOffMinusId = BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (-)')->value('id');

                        $roundOffAccounts = [];

                        $effectiveType = strtolower(trim((string)$cr->voucher_type));

                        if ($roundOffPlusId) {
                            $bill = BillSundrys::find($roundOffPlusId);
                            $account = ($effectiveType === 'sale')
                                ? $bill->sale_amt_account
                                : $bill->purchase_amt_account;

                            $roundOffAccounts[] = $account;
                        }

                        if ($roundOffMinusId) {
                            $bill = BillSundrys::find($roundOffMinusId);
                            $account = ($effectiveType === 'sale')
                                ? $bill->sale_amt_account
                                : $bill->purchase_amt_account;

                            $roundOffAccounts[] = $account;
                        }

                        $roundOffLedgers = $ledgers->whereIn('account_id', $roundOffAccounts);

                        foreach ($roundOffLedgers as $r) {
                            $expected[] = [
                                'account_id' => (int)$r->account_id,
                                'debit'      => round((float)$r->debit, 2),
                                'credit'     => round((float)$r->credit, 2),
                            ];
                        }
                        $normalize = function ($rows) {
                            return collect($rows)
                                ->groupBy('account_id')
                                ->map(fn($g, $id) => [
                                    'account_id' => (int)$id,
                                    'debit'      => round($g->sum('debit'),  2),
                                    'credit'     => round($g->sum('credit'), 2),
                                ])
                                ->sortBy('account_id')
                                ->values()
                                ->toArray();
                        };
        
                        $actualArr = $normalize(
                            $ledgers->map(fn($l) => [
                                'account_id' => (int)$l->account_id,
                                'debit'      => round((float)$l->debit,  2),
                                'credit'     => round((float)$l->credit, 2),
                            ])->toArray()
                        );
                        $expectedArr = $normalize($expected);
        
                        if ($actualArr != $expectedArr) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $cr->party_name = $crParties[$cr->party] ?? '';
                            $cr->module     = 'Credit Note';
                            $cr->reference  = $cr->sr_prefix ?? '';
                            $cr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('credit_note', $cr)) {
                                $cr->edit_url = $this->getEditUrl('credit_note', $cr->id);
                            } else {
                                $cr->edit_url = null;
                            }

                            $data[] = $cr;
                        }
                        continue;
                    }
        
                    // CASE 3 — WITH GST + WITH ITEM or RATE DIFFERENCE  (entry_type = 3)
        
                    $mainAccount = ($cr->voucher_type == 'PURCHASE') ? 36 : 35;
        

                    $crLinkedInvoiceId = (int)($cr->sale_bill_id ?? 0);
        
                    if ($crLinkedInvoiceId > 0) {
                        if (isset($crSaleInvoiceIdSet[$crLinkedInvoiceId])) {
                            $crInvoiceType = 'sale';
                        } elseif (isset($crPurchaseInvoiceIdSet[$crLinkedInvoiceId])) {
                            $crInvoiceType = 'purchase';
                        } else {
                            $isMismatch    = true;
                            $reasons[] = 'Main account ledger mismatch';
                            $crInvoiceType = 'sale'; // safe fallback
                        }
                    } else {
                        $crInvoiceType = strtolower(trim((string)$cr->voucher_type)) === 'purchase'
                            ? 'purchase'
                            : 'sale';
                    }
        
                    $ledgers    = $crLedgers3[$cr->id]     ?? collect();
                    $items      = $crItems[$cr->id]        ?? collect();
                    $itemLedger = $crItemLedgers[$cr->id]  ?? collect();
                    $sundries   = $crSundries[$cr->id]     ?? collect();
        
                    // Ledger must exist for active record
                    if ($ledgers->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Ledger entries missing';
                    }
        
                    $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                    $totalCredit = round((float)$ledgers->sum('credit'), 2);
                    if ($totalDebit !== $totalCredit) {
                        $isMismatch = true;
                        $reasons[] = 'Debit and Credit not equal';
                    }
        
                    // Items must exist
                    if ($items->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Items missing';
                    }
        

                    if ($cr->sr_type == 'WITH ITEM') {
        
                        if ($itemLedger->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Item ledger missing';
                        }
        
                        if ($items->count() !== $itemLedger->count()) {
                            $isMismatch = true;
                            $reasons[] = 'Item count mismatch';
                        }
        
                        $itemsArr = $items->map(fn($r) => [
                            'item_id' => (int)$r->goods_discription,
                            'qty'     => round((float)$r->qty,    2),
                            'price'   => round((float)$r->price,  2),
                            'amount'  => round((float)$r->amount, 2),
                        ])->sortBy('item_id')->values()->toArray();
        
                        $ledgerArr = $itemLedger->map(fn($r) => [
                            'item_id' => (int)$r->item_id,
                            'qty'     => round((float)$r->in_weight,   2), 
                            'price'   => round((float)$r->price,       2),
                            'amount'  => round((float)$r->total_price, 2),
                        ])->sortBy('item_id')->values()->toArray();
        
                        if (json_encode($itemsArr) !== json_encode($ledgerArr)) {
                            $isMismatch = true;
                            $reasons[] = 'Item details mismatch with ledger';
                        }
        
                        foreach ($items as $it) {
                            if ((float)$it->qty == 0 || (float)$it->price == 0) {
                                $isMismatch = true;
                                $reasons[] = 'Item details mismatch with ledger';
                                break;
                            }
                        }
        
                        $itemTotal       = round((float)$items->sum('amount'),           2);
                        $itemLedgerTotal = round((float)$itemLedger->sum('total_price'), 2);
                        if ($itemTotal !== $itemLedgerTotal) {
                            $isMismatch = true;
                            $reasons[] = 'Item total mismatch';
                        }
        
                    } elseif ($cr->sr_type == 'RATE DIFFERENCE') {
        
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Item ledger missing';
                        }
                    }

                    $effectiveType = strtolower(trim((string)$cr->voucher_type));
        
                    $itemTotal            = round((float)$items->sum('amount'), 2);
                    $uiTotal              = 0;
                    $expectedMain         = $itemTotal; // taxable_amt base
                    $expectedSundryLedger = [];
        
                    foreach ($sundries as $s) {
        
                        $bill = $crBills[$s->bill_sundry] ?? null;
                        if (!$bill) {
                            $isMismatch = true;
                            $reasons[] = 'Bill sundry not found';
                            continue;
                        }
        
                        $amt = round((float)$s->amount, 2);

                        if ($bill->bill_sundry_type == 'additive') {
                            $uiTotal += $amt;
                        } else {
                            $uiTotal -= $amt;
                        }

                        $isAdjustToMain = false;
        
                        if ($crInvoiceType === 'sale' && $bill->adjust_sale_amt == 'Yes') {
                            $isAdjustToMain = true;
                        }
        
                        if ($crInvoiceType === 'purchase' && $bill->adjust_purchase_amt == 'Yes') {
                            $isAdjustToMain = true;
                        }

                        if ($isAdjustToMain) {
                            if ($bill->bill_sundry_type == 'additive') {
                                $expectedMain += $amt;
                            } else {
                                $expectedMain -= $amt;
                            }
                            // No separate ledger entry
                            continue;
                        }

                        if ($effectiveType === 'sale') {
                            $account = $bill->sale_amt_account;
                        } else {
                            $account = $bill->purchase_amt_account;
                        }
        
                        if ($bill->nature_of_sundry == 'ROUNDED OFF (-)') {
                            $expectedSundryLedger[] = [
                                'account_id' => (int)$account,
                                'debit'      => 0.0,
                                'credit'     => $amt,
                            ];
                        } else {
                            $expectedSundryLedger[] = [
                                'account_id' => (int)$account,
                                'debit'      => $amt,
                                'credit'     => 0.0,
                            ];
                        }
                    }
        
                    $calculatedTotal = round($itemTotal + $uiTotal, 2);
                    if (round((float)$cr->total, 2) != $calculatedTotal) {
                        $isMismatch = true;
                        $reasons[] = 'Total mismatch with items and sundries';
                    }

                    $partyLedgers = $ledgers->where('account_id', $cr->party);
        
                    if ($partyLedgers->count() !== 1) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger missing or duplicate';
                    } else {
                        $partyLedger = $partyLedgers->first();
                        if (
                            round((float)$partyLedger->debit,  2) != 0 ||
                            round((float)$partyLedger->credit, 2) != round((float)$cr->total, 2)
                        ) {
                            $isMismatch = true;
                            $reasons[] = 'Party ledger amount mismatch';
                        }
                    }

                    $mainLedgers = $ledgers->where('account_id', $mainAccount);
        
                    if ($mainLedgers->count() !== 1) {
                        $isMismatch = true;
                        $reasons[] = 'Main account ledger mismatch';
                    } else {
                        $mainLedger = $mainLedgers->first();
                        if (
                            round((float)$mainLedger->debit,  2) != round($expectedMain, 2) ||
                            round((float)$mainLedger->credit, 2) != 0
                        ) {
                            $isMismatch = true;
                            $reasons[] = 'Main account ledger mismatch';
                        }
                    }

                    $crActualSundry = $ledgers
                        ->whereNotIn('account_id', [$cr->party, $mainAccount])
                        ->map(fn($r) => [
                            'account_id' => (int)$r->account_id,
                            'debit'      => round((float)$r->debit,  2),
                            'credit'     => round((float)$r->credit, 2),
                        ])
                        ->sortBy('account_id')
                        ->values()
                        ->toArray();
        
                    $normalize = function ($rows) {
                        return collect($rows)
                            ->groupBy('account_id')
                            ->map(fn($g, $id) => [
                                'account_id' => (int)$id,
                                'debit'      => round($g->sum('debit'),  2),
                                'credit'     => round($g->sum('credit'), 2),
                            ])
                            ->sortBy('account_id')
                            ->values()
                            ->toArray();
                    };
        
                    $crActualCheck   = $normalize($crActualSundry);
                    $crExpectedCheck = $normalize($expectedSundryLedger);
        
                    if ($crActualCheck != $crExpectedCheck) {
                        $isMismatch = true;
                        $reasons[] = 'Bill sundry ledger mismatch';
                    }

                    $validAccounts = collect([$cr->party, $mainAccount])
                        ->merge(collect($expectedSundryLedger)->pluck('account_id'))
                        ->merge(
                            $sundries->map(function ($s) use ($crBills) {
                                $bill = $crBills[$s->bill_sundry] ?? null;
                                return $bill ? $bill->sale_amt_account ?? $bill->purchase_amt_account : null;
                            })
                        )
                        ->filter()
                        ->unique();
        
                    $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);
                    if ($invalidLedger->isNotEmpty()) {
                        $isMismatch = true;
                        if (!in_array('Bill sundry ledger mismatch', $reasons)) {
                            $reasons[] = 'Invalid/orphan ledger entries found';
                        }
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $cr->party_name = $crParties[$cr->party] ?? '';
                        $cr->module     = 'Credit Note';
                        $cr->reference  = $cr->sr_prefix ?? '';
                        $cr->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('credit_note', $cr)) {
                            $cr->edit_url = $this->getEditUrl('credit_note', $cr->id);
                        } else {
                            $cr->edit_url = null;
                        }

                        $data[] = $cr;
                    }
        
                } 
            } 
        }
        // =========================
        // DEBIT NOTE (PURCHASE RETURN)
        // =========================
        if ($type == 'debit_note') {
        
            $dreturns = \App\Models\PurchaseReturn::where('company_id', $company_id)
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                ->get();
        
            if ($dreturns->isEmpty()) {
            } else {
        
                $dreturnIds = $dreturns->pluck('id')->toArray();
        
                $drItems = \App\Models\PurchaseReturnDescription::whereIn('purchase_return_id', $dreturnIds)
                    ->get()->groupBy('purchase_return_id');
        
                $drItemLedgers = ItemLedger::where('source', 5)
                    ->whereIn('source_id', $dreturnIds)
                    ->get()->groupBy('source_id');
        
                $drSundries = \App\Models\PurchaseReturnSundry::whereIn('purchase_return_id', $dreturnIds)
                    ->get()->groupBy('purchase_return_id');
        
                $drWithoutEntries = \App\Models\PurchaseReturnEntry::whereIn('purchase_return_id', $dreturnIds)
                    ->get()->groupBy('purchase_return_id');
        
                // Account ledgers — all three entry_types
                $drLedgers4  = AccountLedger::where('entry_type', 4)
                    ->whereIn('entry_type_id', $dreturnIds)
                    ->get()->groupBy('entry_type_id');
        
                $drLedgers12 = AccountLedger::where('entry_type', 12)
                    ->whereIn('entry_type_id', $dreturnIds)
                    ->get()->groupBy('entry_type_id');
        
                $drLedgers13 = AccountLedger::where('entry_type', 13)
                    ->whereIn('entry_type_id', $dreturnIds)
                    ->get()->groupBy('entry_type_id');
        
                $drBillIds = \App\Models\PurchaseReturnSundry::whereIn('purchase_return_id', $dreturnIds)
                    ->pluck('bill_sundry')
                    ->unique()
                    ->toArray();

                $gstIds = BillSundrys::whereIn('nature_of_sundry', ['IGST','CGST','SGST'])
                    ->pluck('id')
                    ->toArray();

                $drBillIds = array_unique(array_merge($drBillIds, $gstIds));

                $drBills = BillSundrys::whereIn('id', $drBillIds)
                    ->get()
                    ->keyBy('id');
        
                // Party names
                $drPartyIds = $dreturns->pluck('party')->unique()->toArray();
                $drParties  = Accounts::whereIn('id', $drPartyIds)->pluck('account_name', 'id');

                // Linked invoice type resolution (selected invoice id -> SALE/PURCHASE)
                $drLinkedInvoiceIds = $dreturns->pluck('purchase_bill_id')
                    ->filter(fn($id) => !empty($id))
                    ->unique()
                    ->values()
                    ->toArray();
                $drSaleInvoiceIds = empty($drLinkedInvoiceIds)
                    ? []
                    : Sales::whereIn('id', $drLinkedInvoiceIds)->pluck('id')->map(fn($id) => (int)$id)->toArray();
                $drPurchaseInvoiceIds = empty($drLinkedInvoiceIds)
                    ? []
                    : \App\Models\Purchase::whereIn('id', $drLinkedInvoiceIds)->pluck('id')->map(fn($id) => (int)$id)->toArray();
                $drSaleInvoiceIdSet = array_fill_keys($drSaleInvoiceIds, true);
                $drPurchaseInvoiceIdSet = array_fill_keys($drPurchaseInvoiceIds, true);

                foreach ($dreturns as $dr) {
        
                    $isMismatch = false;
                    $reasons = [];
        
                    if ($dr->status == 2 || $dr->delete == 1) {
        
                        $hasActiveItems = \App\Models\PurchaseReturnDescription::where('purchase_return_id', $dr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveSundries = \App\Models\PurchaseReturnSundry::where('purchase_return_id', $dr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveWithoutEntries = \App\Models\PurchaseReturnEntry::where('purchase_return_id', $dr->id)
                            ->where('delete', '0')->exists();
        
                        $hasActiveAccountLedger = AccountLedger::where('entry_type_id', $dr->id)
                            ->whereIn('entry_type', [4, 12, 13])
                            ->where('delete_status', '0')
                            ->exists();
        
                        $hasActiveItemLedger = ItemLedger::where('source', 5)
                            ->where('source_id', $dr->id)
                            ->where('delete_status', '0')
                            ->exists();
        
                        if ($dr->status == 2) {
                            if (
                                $hasActiveItems ||
                                $hasActiveSundries ||
                                $hasActiveWithoutEntries ||
                                $hasActiveAccountLedger ||
                                $hasActiveItemLedger ||
                                round((float)$dr->total, 2) != 0
                            ) {
                                $isMismatch = true;
                                $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                            }
                        } elseif ($dr->delete == 1) {
                            if (
                                $hasActiveItems ||
                                $hasActiveSundries ||
                                $hasActiveWithoutEntries ||
                                $hasActiveAccountLedger ||
                                $hasActiveItemLedger
                            ) {
                                $isMismatch = true;
                                $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                            }
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $dr->party_name = $drParties[$dr->party] ?? '';
                            $dr->module     = 'Debit Note';
                            $dr->reference  = $dr->sr_prefix ?? '';
                            $dr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('debit_note', $dr)) {
                                $dr->edit_url = $this->getEditUrl('debit_note', $dr->id);
                            } else {
                                $dr->edit_url = null;
                            }
                            $data[] = $dr;
                        }
                        continue;
                    }
        
                    // CASE 1 — WITHOUT GST  (entry_type = 12)
                    if ($dr->sr_nature == 'WITHOUT GST') {
        
                        $ledgers = $drLedgers12[$dr->id] ?? collect();
        
                        // Ledger must exist
                        if ($ledgers->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Ledger entries missing';
                        }
        
                        $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                        $totalCredit = round((float)$ledgers->sum('credit'), 2);
                        if ($totalDebit !== $totalCredit) {
                            $isMismatch = true;
                            $reasons[] = 'Debit and Credit not equal';
                        }
        
                        $items = $drItems[$dr->id] ?? collect();
                        if ($items->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        }
        
                        $itemLedger = $drItemLedgers[$dr->id] ?? collect();
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        }
        
                        $entries = $drWithoutEntries[$dr->id] ?? collect();
        
                        if ($entries->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without GST structure mismatch';
                        } else {
                            $expectedTotal = 0;
                            $expected = [];
        
                            foreach ($entries as $e) {
                                $expected[] = [
                                    'account_id' => (int)$e->account_name,
                                    'debit'      => 0.0,
                                    'credit'     => round((float)$e->credit, 2), 
                                ];
                                $expectedTotal += round((float)$e->credit, 2);
                            }
        
                            $expected[] = [
                                'account_id' => (int)$dr->party,
                                'debit'      => round($expectedTotal, 2), 
                                'credit'     => 0.0,
                            ];
        
                            $normalize = function ($rows) {
                                return collect($rows)
                                    ->groupBy('account_id')
                                    ->map(fn($g, $id) => [
                                        'account_id' => (int)$id,
                                        'debit'      => round($g->sum('debit'),  2),
                                        'credit'     => round($g->sum('credit'), 2),
                                    ])
                                    ->sortBy('account_id')
                                    ->values()
                                    ->toArray();
                            };
        
                            $actualArr = $normalize(
                                $ledgers->map(fn($l) => [
                                    'account_id' => (int)$l->account_id,
                                    'debit'      => round((float)$l->debit,  2),
                                    'credit'     => round((float)$l->credit, 2),
                                ])->toArray()
                            );
                            $expectedArr = $normalize($expected);
        
                            if ($actualArr != $expectedArr) {
                                $isMismatch = true;
                                $reasons[] = 'Without GST structure mismatch';
                            }
        
                            if (round((float)$dr->total, 2) !== round($expectedTotal, 2)) {
                                $isMismatch = true;
                                $reasons[] = 'Without GST structure mismatch';
                            }
                        }
        
                        $validAccountsDrWog = collect([$dr->party])
                            ->merge(($drWithoutEntries[$dr->id] ?? collect())->pluck('account_name'))
                            ->unique();
                        $orphanDrWog = $ledgers->whereNotIn('account_id', $validAccountsDrWog);
                        if ($orphanDrWog->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Invalid/orphan ledger entries found';
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $dr->party_name = $drParties[$dr->party] ?? '';
                            $dr->module     = 'Debit Note';
                            $dr->reference  = $dr->sr_prefix ?? '';
                            $dr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('debit_note', $dr)) {
                                $dr->edit_url = $this->getEditUrl('debit_note', $dr->id);
                            } else {
                                $dr->edit_url = null;
                            }
                            $data[] = $dr;
                        }
                        continue;
                    }
        
                    // CASE 2 — WITH GST + WITHOUT ITEM  (entry_type = 13)
                    if ($dr->sr_nature == 'WITH GST' && $dr->sr_type == 'WITHOUT ITEM') {
        
                        $ledgers = $drLedgers13[$dr->id] ?? collect();
        
                        // Ledger must exist
                        if ($ledgers->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Ledger entries missing';
                        }
        
                        $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                        $totalCredit = round((float)$ledgers->sum('credit'), 2);
                        if ($totalDebit !== $totalCredit) {
                            $isMismatch = true;
                            $reasons[] = 'Debit and Credit not equal';
                        }
        
                        $items = $drItems[$dr->id] ?? collect();
                        if ($items->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        $itemLedger = $drItemLedgers[$dr->id] ?? collect();
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        $entries = $drWithoutEntries[$dr->id] ?? collect();
                        $drInvoiceType = (strtoupper((string)$dr->voucher_type) === 'PURCHASE') ? 'purchase' : 'sale';
                        $drLinkedInvoiceId = (int)($dr->purchase_bill_id ?? 0);
                        if ($drLinkedInvoiceId > 0) {
                            if (isset($drSaleInvoiceIdSet[$drLinkedInvoiceId])) {
                                $drInvoiceType = 'sale';
                            } elseif (isset($drPurchaseInvoiceIdSet[$drLinkedInvoiceId])) {
                                $drInvoiceType = 'purchase';
                            }
                        }
        
                        $expected = [];
        
                        $expected[] = [
                            'account_id' => (int)$dr->party,
                            'debit'      => round((float)$dr->total, 2),
                            'credit'     => 0.0,
                        ];
        
                        foreach ($entries as $e) {
                            $expected[] = [
                                'account_id' => (int)$e->account_name,
                                'debit'      => 0.0,
                                'credit'     => round((float)$e->debit, 2), 
                            ];
                        }
        
                        $gstList = [];

                        if ($dr->tax_igst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'IGST')->value('id');
                            if ($id) $gstList[] = ['id'=>$id,'amt'=>$dr->tax_igst];
                        }

                        if ($dr->tax_cgst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'CGST')->value('id');
                            if ($id) $gstList[] = ['id'=>$id,'amt'=>$dr->tax_cgst];
                        }

                        if ($dr->tax_sgst > 0) {
                            $id = BillSundrys::where('nature_of_sundry', 'SGST')->value('id');
                            if ($id) $gstList[] = ['id'=>$id,'amt'=>$dr->tax_sgst];
                        }

                        foreach ($gstList as $g) {

                            $bill = BillSundrys::find($g['id']);
                            if (!$bill) {
                                $isMismatch = true;
                                $reasons[] = 'GST calculation mismatch';
                                continue;
                            }

                            $amt = round((float)$g['amt'], 2);

                        $effectiveType = strtolower(trim((string)$dr->voucher_type));

                        if ($effectiveType === 'sale') {
                            $account = $bill->sale_amt_account;
                        } else {
                            $account = $bill->purchase_amt_account;
                        }

                            $expected[] = [
                                'account_id' => (int)$account,
                                'debit'      => 0.0,
                                'credit'     => $amt,
                            ];
                        }

                        $roundOffPlusId  = BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (+)')->value('id');
                        $roundOffMinusId = BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (-)')->value('id');

                        $roundOffAccounts = [];

                        if ($roundOffPlusId) {
                            $bill = BillSundrys::find($roundOffPlusId);

                            $effectiveType = strtolower(trim((string)$dr->voucher_type));
                            $account = ($effectiveType === 'sale')
                                ? $bill->sale_amt_account
                                : $bill->purchase_amt_account;

                            $roundOffAccounts[] = $account;
                        }

                        if ($roundOffMinusId) {
                            $bill = BillSundrys::find($roundOffMinusId);

                            $effectiveType = strtolower(trim((string)$dr->voucher_type));
                            $account = ($effectiveType === 'sale')
                                ? $bill->sale_amt_account
                                : $bill->purchase_amt_account;

                            $roundOffAccounts[] = $account;
                        }

                        $roundOffLedgers = $ledgers->whereIn('account_id', $roundOffAccounts);

                        foreach ($roundOffLedgers as $r) {
                            $expected[] = [
                                'account_id' => (int)$r->account_id,
                                'debit'      => round((float)$r->debit, 2),
                                'credit'     => round((float)$r->credit, 2),
                            ];
                        } 
                        $normalize = function ($rows) {
                            return collect($rows)
                                ->groupBy('account_id')
                                ->map(fn($g, $id) => [
                                    'account_id' => (int)$id,
                                    'debit'      => round($g->sum('debit'),  2),
                                    'credit'     => round($g->sum('credit'), 2),
                                ])
                                ->sortBy('account_id')
                                ->values()
                                ->toArray();
                        };
        
                        $actualArr = $normalize(
                            $ledgers->map(fn($l) => [
                                'account_id' => (int)$l->account_id,
                                'debit'      => round((float)$l->debit,  2),
                                'credit'     => round((float)$l->credit, 2),
                            ])->toArray()
                        );
                        $expectedArr = $normalize($expected);
        
                        if ($actualArr != $expectedArr) {
                            $isMismatch = true;
                            $reasons[] = 'Without item ledger mismatch';
                        }
        
                        if ($isMismatch) {
                            $reasons = array_unique($reasons);
                            $dr->party_name = $drParties[$dr->party] ?? '';
                            $dr->module     = 'Debit Note';
                            $dr->reference  = $dr->sr_prefix ?? '';
                            $dr->reason = implode(', ', $reasons);
                            if ($this->canEditTransaction('debit_note', $dr)) {
                                $dr->edit_url = $this->getEditUrl('debit_note', $dr->id);
                            } else {
                                $dr->edit_url = null;
                            }
                            $data[] = $dr;
                        }
                        continue;
                    }
        
                    // CASE 3 — WITH GST + WITH ITEM or RATE DIFFERENCE  (entry_type = 4)
                    $drMainAccount = ($dr->voucher_type == 'PURCHASE') ? 36 : 35;
                    $drLinkedInvoiceId = (int)($dr->purchase_bill_id ?? 0);

                    if ($drLinkedInvoiceId > 0) {

                        if (isset($drSaleInvoiceIdSet[$drLinkedInvoiceId])) {
                            $drInvoiceType = 'sale';

                        } elseif (isset($drPurchaseInvoiceIdSet[$drLinkedInvoiceId])) {
                            $drInvoiceType = 'purchase';

                        } else {
                            $isMismatch = true;
                            $reasons[] = 'Main account ledger mismatch';

                            $drInvoiceType = strtolower(trim((string)$dr->voucher_type)) === 'purchase'
                                ? 'purchase'
                                : 'sale';
                        }

                    } else {

                        $drInvoiceType = strtolower(trim((string)$dr->voucher_type)) === 'purchase'
                            ? 'purchase'
                            : 'sale';
                    }
        
                    $ledgers    = $drLedgers4[$dr->id]    ?? collect();
                    $items      = $drItems[$dr->id]        ?? collect();
                    $itemLedger = $drItemLedgers[$dr->id]  ?? collect();
                    $sundries   = $drSundries[$dr->id]     ?? collect();
        
                    if ($ledgers->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Ledger entries missing';
                    }
        
                    $totalDebit  = round((float)$ledgers->sum('debit'),  2);
                    $totalCredit = round((float)$ledgers->sum('credit'), 2);
                    if ($totalDebit !== $totalCredit) {
                        $isMismatch = true;
                        $reasons[] = 'Debit and Credit not equal';
                    }
        
                    if ($items->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Items missing';
                    }
        
                    if ($dr->sr_type == 'WITH ITEM') {
        
                        if ($itemLedger->isEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Item ledger missing';
                        }
        
                        if ($items->count() !== $itemLedger->count()) {
                            $isMismatch = true;
                            $reasons[] = 'Item count mismatch';
                        }

                        $itemsArr = $items->map(fn($r) => [
                            'item_id' => (int)$r->goods_discription,
                            'qty'     => round((float)$r->qty,    2),
                            'price'   => round((float)$r->price,  2),
                            'amount'  => round((float)$r->amount, 2),
                        ])->sortBy('item_id')->values()->toArray();
        
                        $ledgerArr = $itemLedger->map(fn($r) => [
                            'item_id' => (int)$r->item_id,
                            'qty'     => round((float)$r->out_weight,  2), 
                            'price'   => round((float)$r->price,       2),
                            'amount'  => round((float)$r->total_price, 2),
                        ])->sortBy('item_id')->values()->toArray();
        
                        if (json_encode($itemsArr) !== json_encode($ledgerArr)) {
                            $isMismatch = true;
                            $reasons[] = 'Item details mismatch with ledger';
                        }
        
                        foreach ($items as $it) {
                            if ((float)$it->qty == 0 || (float)$it->price == 0) {
                                $isMismatch = true;
                                $reasons[] = 'Item details mismatch with ledger';
                                break;
                            }
                        }
        
                        $drItemTotal       = round((float)$items->sum('amount'),           2);
                        $drItemLedgerTotal = round((float)$itemLedger->sum('total_price'), 2);
                        if ($drItemTotal !== $drItemLedgerTotal) {
                            $isMismatch = true;
                            $reasons[] = 'Item total mismatch';
                        }
        
                    } elseif ($dr->sr_type == 'RATE DIFFERENCE') {
        
                        if ($itemLedger->isNotEmpty()) {
                            $isMismatch = true;
                            $reasons[] = 'Item ledger missing';
                        }
                    }
        
                    $drItemTotal      = round((float)$items->sum('amount'), 2);
                    $drUiTotal        = 0;
                    $drExpectedMain   = $drItemTotal; 
                    $drExpectedSundryLedger = [];
        
                    foreach ($sundries as $s) {

                        $bill = $drBills[$s->bill_sundry] ?? null;
                        if (!$bill) {
                            $isMismatch = true;
                            $reasons[] = 'Bill sundry not found';
                            continue;
                        }

                        $amt = round((float)$s->amount, 2);

                        if ($bill->bill_sundry_type == 'additive') {
                            $drUiTotal += $amt;
                        } else {
                            $drUiTotal -= $amt;
                        }

                        $isAdjustToMain = false;

                        if ($drInvoiceType === 'sale' && $bill->adjust_sale_amt == 'Yes') {
                            $isAdjustToMain = true;
                        }

                        if ($drInvoiceType === 'purchase' && $bill->adjust_purchase_amt == 'Yes') {
                            $isAdjustToMain = true;
                        }

                        if ($isAdjustToMain) {

                            if ($bill->bill_sundry_type == 'additive') {
                                $drExpectedMain += $amt;
                            } else {
                                $drExpectedMain -= $amt;
                            }

                            continue;
                        }

                        $effectiveType = strtolower(trim((string)$dr->voucher_type));

                        if ($effectiveType === 'sale') {
                            $account = $bill->sale_amt_account;
                        } else {
                            $account = $bill->purchase_amt_account;
                        }

                        if ($bill->nature_of_sundry == 'ROUNDED OFF (-)') {
                            $drExpectedSundryLedger[] = [
                                'account_id' => (int)$account,
                                'debit'      => $amt,
                                'credit'     => 0.0,
                            ];

                        } elseif ($bill->nature_of_sundry == 'ROUNDED OFF (+)') {
                            $drExpectedSundryLedger[] = [
                                'account_id' => (int)$account,
                                'debit'      => 0.0,
                                'credit'     => $amt,
                            ];

                        } else {
                            $drExpectedSundryLedger[] = [
                                'account_id' => (int)$account,
                                'debit'      => 0.0,
                                'credit'     => $amt,
                            ];
                        }
                    }
        
                    $drCalculatedTotal = round($drItemTotal + $drUiTotal, 2);
                    if (round((float)$dr->total, 2) != $drCalculatedTotal) {
                        $isMismatch = true;
                        $reasons[] = 'Total mismatch with items and sundries';
                    }

                    $drPartyLedgers = $ledgers->where('account_id', $dr->party);
        
                    if ($drPartyLedgers->count() !== 1) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger missing or duplicate';
                    } else {
                        $drPartyLedger = $drPartyLedgers->first();
                        if (
                            round((float)$drPartyLedger->debit,  2) != round((float)$dr->total, 2) || 
                            round((float)$drPartyLedger->credit, 2) != 0
                        ) {
                            $isMismatch = true;
                            $reasons[] = 'Party ledger amount mismatch';
                        }
                    }

                    $drMainLedgers = $ledgers->where('account_id', $drMainAccount);
        
                    if ($drMainLedgers->count() !== 1) {
                        $isMismatch = true;
                        $reasons[] = 'Main account ledger mismatch';
                    } else {
                        $drMainLedger = $drMainLedgers->first();
                        if (
                            round((float)$drMainLedger->debit,  2) != 0 ||
                            round((float)$drMainLedger->credit, 2) != round($drExpectedMain, 2) 
                        ) {
                            $isMismatch = true;
                            $reasons[] = 'Main account ledger mismatch';
                        }
                    }

                    $drExpectedAccountIds = collect($drExpectedSundryLedger)
                        ->pluck('account_id')
                        ->unique()
                        ->values()
                        ->toArray();

                    $drActualSundry = $ledgers
                        ->whereNotIn('account_id', [$dr->party, $drMainAccount])
                        ->map(fn($r) => [
                            'account_id' => (int)$r->account_id,
                            'debit'      => round((float)$r->debit,  2),
                            'credit'     => round((float)$r->credit, 2),
                        ])
                        ->sortBy('account_id')
                        ->values()
                        ->toArray();

                    $normalize = function ($rows) {
                        return collect($rows)
                            ->groupBy('account_id')
                            ->map(fn($g, $id) => [
                                'account_id' => (int)$id,
                                'debit'      => round($g->sum('debit'),  2),
                                'credit'     => round($g->sum('credit'), 2),
                            ])
                            ->sortBy('account_id')->values()->toArray();
                    };
        
                    $drActualCheck   = $normalize($drActualSundry);
                    $drExpectedCheck = $normalize($drExpectedSundryLedger);
        
                    if ($drActualCheck != $drExpectedCheck) {
                        $isMismatch = true;
                        $reasons[] = 'Bill sundry ledger mismatch';
                    }

                    $drValidAccounts = collect([$dr->party, $drMainAccount])
                        ->merge(collect($drExpectedSundryLedger)->pluck('account_id'))
                        ->merge(
                            $sundries->map(function ($s) use ($drBills) {
                                $bill = $drBills[$s->bill_sundry] ?? null;
                                return $bill ? $bill->sale_amt_account ?? $bill->purchase_amt_account : null;
                            })
                        )
                        ->filter()
                        ->unique();
        
                    $drInvalidLedger = $ledgers->whereNotIn('account_id', $drValidAccounts);
                    if ($drInvalidLedger->isNotEmpty()) {
                        $isMismatch = true;
                        if (!in_array('Bill sundry ledger mismatch', $reasons)) {
                            $reasons[] = 'Invalid/orphan ledger entries found';
                        }
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $dr->party_name = $drParties[$dr->party] ?? '';
                        $dr->module     = 'Debit Note';
                        $dr->reference  = $dr->sr_prefix ?? '';
                        $dr->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('debit_note', $dr)) {
                            $dr->edit_url = $this->getEditUrl('debit_note', $dr->id);
                        } else {
                            $dr->edit_url = null;
                        }
                        $data[] = $dr;
                    }
        
                } 
            } 
        } 
        // =========================
        // PAYMENT INTEGRITY
        // =========================
        if ($type == 'payment') {

            $payments = \App\Models\Payment::where('company_id', $company_id);
                                        

            if ($from) $payments->whereDate('date', '>=', $from);
            if ($to)   $payments->whereDate('date', '<=', $to);

            $payments = $payments->get();

            foreach ($payments as $payment) {

                $isMismatch = false;
                $reasons = [];

                if ($payment->delete == 1) {

                    $hasActiveDetails = \App\Models\PaymentDetails::where('payment_id', $payment->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveLedger = \App\Models\AccountLedger::where('entry_type', 5)
                        ->where('entry_type_id', $payment->id)
                        ->where('delete_status', '0')
                        ->exists();

                    if ($hasActiveDetails || $hasActiveLedger) {
                        $isMismatch = true;
                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $payment->module = 'Payment';
                        $payment->reference = $payment->voucher_no ?? '';
                        $payment->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('payment', $payment)) {
                            $payment->edit_url = $this->getEditUrl('payment', $payment->id);
                        } else {
                            $payment->edit_url = null;
                        }
                        $data[] = $payment;
                    }

                    continue;
                }

                $details = \App\Models\PaymentDetails::where('payment_id', $payment->id)->get();

                $detailsArr = $details->map(function ($d) {
                    return [
                        'type'    => $d->type,
                        'account' => (int)$d->account_name,
                        'debit'   => (float)$d->debit,
                        'credit'  => (float)$d->credit,
                        'narration' => (string)$d->narration,
                    ];
                })->values()->toArray();

                $debit_arr = [];
                $credit_arr = [];

                $credit_id = '';
                $credit_narration = '';

                foreach ($details as $d) {
                    if ($d->type == 'Credit') {
                        $credit_id = $d->account_name;
                        $credit_narration = $d->narration ?? '';
                        break;
                    }
                }

                foreach ($details as $d) {

                    if ($d->type == 'Debit') {

                        $debit_arr[] = [
                            'account_id' => (int)$d->account_name,
                            'debit'      => (float)$d->debit,
                            'credit'     => 0.0,
                            'map_account_id' => (int)$credit_id,
                            'narration'  => (string)$d->narration,
                        ];

                        if (isset($credit_arr[$d->account_name])) {
                            $credit_arr[$d->account_name]['credit'] += (float)$d->debit;
                        } else {
                            $credit_arr[$d->account_name] = [
                                'account_id' => (int)$credit_id,
                                'debit'      => 0.0,
                                'credit'     => (float)$d->debit,
                                'map_account_id' => (int)$d->account_name,
                                'narration'  => (string)$credit_narration,
                            ];
                        }
                    }
                }

                $expectedLedger = array_merge($debit_arr, array_values($credit_arr));

                $expectedLedger = collect($expectedLedger)
                    ->map(function ($e) {
                        return [
                            'account_id' => (int)$e['account_id'],
                            'debit'      => round((float)$e['debit'], 2),
                            'credit'     => round((float)$e['credit'], 2),
                            'map_account_id' => (int)$e['map_account_id'],
                            'narration'  => trim((string)$e['narration']),
                        ];
                    })
                    ->sort()
                    ->values()
                    ->toArray();

                $actualLedger = \App\Models\AccountLedger::where([
                    'entry_type'    => 5,
                    'entry_type_id' => $payment->id
                ])
                ->where('delete_status', '0')->get()->map(function ($l) {
                    return [
                        'account_id' => (int)$l->account_id,
                        'debit'      => round((float)$l->debit, 2),
                        'credit'     => round((float)$l->credit, 2),
                        'map_account_id' => (int)$l->map_account_id,
                        'narration'  => trim((string)$l->entry_narration),
                    ];
                })->sort()->values()->toArray();
                $ledgers = collect($actualLedger);

                if ($ledgers->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                $totalDebit = round($ledgers->sum('debit'), 2);
                $totalCredit = round($ledgers->sum('credit'), 2);

                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }

                $creditDetails = $details->where('type', 'Credit');

                if ($creditDetails->count() !== 1) {
                    $isMismatch = true;
                    $reasons[] = 'Party ledger missing or duplicate';
                } else {

                    $credit_id = (int)$creditDetails->first()->account_name;

                    $creditLedgers = $ledgers->where('account_id', $credit_id);

                    if ($creditLedgers->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger missing or duplicate';
                    } else {

                        $ledgerCreditTotal = round($creditLedgers->sum('credit'), 2);
                        $detailsCredit = round($details->sum('credit'), 2);

                        if ($ledgerCreditTotal !== $detailsCredit) {
                            $isMismatch = true;
                            $reasons[] = 'Party ledger amount mismatch';
                        }
                    }
                }

                $debitDetails = $details->where('type', 'Debit');

                foreach ($debitDetails as $d) {

                    $accountId = (int)$d->account_name;

                    $ledgerRows = $ledgers->where('account_id', $accountId);

                    if ($ledgerRows->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Ledger entries missing';
                        continue;
                    }

                    $ledgerDebit = round($ledgerRows->sum('debit'), 2);
                    $detailDebit = round((float)$d->debit, 2);

                    if ($ledgerDebit !== $detailDebit) {
                        $isMismatch = true;
                        $reasons[] = 'Item details mismatch with ledger';
                    }
                }

                $validAccounts = $details->pluck('account_name')->unique();

                $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

                if ($invalidLedger->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }

                if ($expectedLedger !== $actualLedger) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $payment->party_name = ''; 
                    $payment->module = 'Payment';
                    $payment->reference = $payment->voucher_no ?? '';
                    $payment->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('payment', $payment)) {
                        $payment->edit_url = $this->getEditUrl('payment', $payment->id);
                    } else {
                        $payment->edit_url = null;
                    }
                    $data[] = $payment;
                }
            }
        }
        // =========================
        // RECEIPT INTEGRITY
        // =========================
        if ($type == 'receipt') {

            $receipts = \App\Models\Receipt::where('company_id', $company_id);
                        
            if ($from) $receipts->whereDate('date', '>=', $from);
            if ($to)   $receipts->whereDate('date', '<=', $to);

            $receipts = $receipts->get();

            foreach ($receipts as $receipt) {

                $isMismatch = false;
                $reasons = [];

                if ($receipt->delete == 1) {

                    $hasActiveDetails = \App\Models\ReceiptDetails::where('receipt_id', $receipt->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveLedger = \App\Models\AccountLedger::where('entry_type', 6)
                        ->where('entry_type_id', $receipt->id)
                        ->where('delete_status', '0')
                        ->exists();

                    if ($hasActiveDetails || $hasActiveLedger) {
                        $isMismatch = true;
                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $receipt->module = 'Receipt';
                        $receipt->reference = $receipt->voucher_no ?? '';
                        $receipt->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('receipt', $receipt)) {
                            $receipt->edit_url = $this->getEditUrl('receipt', $receipt->id);
                        } else {
                            $receipt->edit_url = null;
                        }
                        $data[] = $receipt;
                    }

                    continue;
                }

                $details = \App\Models\ReceiptDetails::where('receipt_id', $receipt->id)->get();

                $detailsArr = $details->map(function ($d) {
                    return [
                        'type'    => $d->type,
                        'account' => (int)$d->account_name,
                        'debit'   => (float)$d->debit,
                        'credit'  => (float)$d->credit,
                        'narration' => (string)$d->narration,
                    ];
                })->values()->toArray();

                $debit_arr = [];
                $credit_arr = [];

                $debit_id = '';
                $debit_narration = '';

                foreach ($details as $d) {
                    if ($d->type == 'Debit') {
                        $debit_id = $d->account_name;
                        $debit_narration = $d->narration ?? '';
                        break;
                    }
                }

                foreach ($details as $d) {

                    if ($d->type == 'Credit') {

                        $credit_arr[] = [
                            'account_id' => (int)$d->account_name,
                            'debit'      => 0.0,
                            'credit'     => (float)$d->credit,
                            'map_account_id' => (int)$debit_id,
                            'narration'  => (string)$d->narration,
                        ];

                        if (isset($debit_arr[$d->account_name])) {
                            $debit_arr[$d->account_name]['debit'] += (float)$d->credit;
                        } else {
                            $debit_arr[$d->account_name] = [
                                'account_id' => (int)$debit_id,
                                'debit'      => (float)$d->credit,
                                'credit'     => 0.0,
                                'map_account_id' => (int)$d->account_name,
                                'narration'  => (string)$debit_narration,
                            ];
                        }
                    }
                }

                $expectedLedger = array_merge(array_values($debit_arr), $credit_arr);

                $expectedLedger = collect($expectedLedger)
                    ->map(function ($e) {
                        return [
                            'account_id' => (int)$e['account_id'],
                            'debit'      => round((float)$e['debit'], 2),
                            'credit'     => round((float)$e['credit'], 2),
                            'map_account_id' => (int)$e['map_account_id'],
                            'narration'  => trim((string)$e['narration']),
                        ];
                    })
                    ->sort()
                    ->values()
                    ->toArray();

                $actualLedger = \App\Models\AccountLedger::where([
                    'entry_type'    => 6,
                    'entry_type_id' => $receipt->id
                ])->where('delete_status', '0')->get()->map(function ($l) {
                    return [
                        'account_id' => (int)$l->account_id,
                        'debit'      => round((float)$l->debit, 2),
                        'credit'     => round((float)$l->credit, 2),
                        'map_account_id' => (int)$l->map_account_id,
                        'narration'  => trim((string)$l->entry_narration),
                    ];
                })->sort()->values()->toArray();
                $ledgers = collect($actualLedger);

                if ($ledgers->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                $totalDebit = round($ledgers->sum('debit'), 2);
                $totalCredit = round($ledgers->sum('credit'), 2);

                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }

                $debitDetails = $details->where('type', 'Debit');

                if ($debitDetails->count() !== 1) {
                    $isMismatch = true;
                    $reasons[] = 'Party ledger missing or duplicate';
                } else {

                    $debit_id = (int)$debitDetails->first()->account_name;

                    $debitLedgers = $ledgers->where('account_id', $debit_id);

                    if ($debitLedgers->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Party ledger missing or duplicate';
                    } else {

                        $ledgerDebitTotal = round($debitLedgers->sum('debit'), 2);
                        $detailsDebit = round($details->sum('debit'), 2);

                        if ($ledgerDebitTotal !== $detailsDebit) {
                            $isMismatch = true;
                            $reasons[] = 'Party ledger amount mismatch';
                        }
                    }
                }

                $creditDetails = $details->where('type', 'Credit');

                foreach ($creditDetails as $d) {

                    $accountId = (int)$d->account_name;

                    $ledgerRows = $ledgers->where('account_id', $accountId);

                    if ($ledgerRows->isEmpty()) {
                        $isMismatch = true;
                        $reasons[] = 'Ledger entries missing';
                        continue;
                    }

                    $ledgerCredit = round($ledgerRows->sum('credit'), 2);
                    $detailCredit = round((float)$d->credit, 2);

                    if ($ledgerCredit !== $detailCredit) {
                        $isMismatch = true;
                        $reasons[] = 'Item details mismatch with ledger';
                    }
                }

                $validAccounts = $details->pluck('account_name')->unique();

                $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

                if ($invalidLedger->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }

                if ($expectedLedger !== $actualLedger) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $receipt->party_name = ''; // optional
                    $receipt->module = 'Receipt';
                    $receipt->reference = $receipt->voucher_no ?? '';
                    $receipt->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('receipt', $receipt)) {
                        $receipt->edit_url = $this->getEditUrl('receipt', $receipt->id);
                    } else {
                        $receipt->edit_url = null;
                    }
                    $data[] = $receipt;
                }
            }
        }
        // =========================
        // JOURNAL INTEGRITY
        // =========================
        if ($type == 'journal') {

            $journals = \App\Models\Journal::where('company_id', $company_id);
                                        

            if ($from) $journals->whereDate('date', '>=', $from);
            if ($to)   $journals->whereDate('date', '<=', $to);

            $journals = $journals->get();

            foreach ($journals as $journal) {

                $isMismatch = false;
                $reasons = [];

                if ($journal->delete == 1) {

                    $hasActiveDetails = \App\Models\JournalDetails::where('journal_id', $journal->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveLedger = \App\Models\AccountLedger::where('entry_type', 7)
                        ->where('entry_type_id', $journal->id)
                        ->where('delete_status', '0')
                        ->exists();

                    if ($hasActiveDetails || $hasActiveLedger) {
                        $isMismatch = true;
                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $journal->module = 'Journal';
                        $journal->reference = $journal->voucher_no ?? '';
                        $journal->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('journal', $journal)) {
                            $journal->edit_url = $this->getEditUrl('journal', $journal->id);
                        } else {
                            $journal->edit_url = null;
                        }
                        $data[] = $journal;
                    }

                    continue;
                }

                $details = \App\Models\JournalDetails::where('journal_id', $journal->id)->get();

                if ($details->count() == 0) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                $expected = $details->map(function ($d) {
                    return [
                        'account_id' => (int)$d->account_name,
                        'debit'      => round((float)$d->debit, 2),
                        'credit'     => round((float)$d->credit, 2),
                    ];
                });

                $expected = collect($expected)
                    ->sortBy([
                        ['account_id', 'asc'],
                        ['debit', 'asc'],
                        ['credit', 'asc'],
                    ])
                    ->values()
                    ->toArray();

                $ledgerRows = \App\Models\AccountLedger::where([
                    'entry_type'    => 7,
                    'entry_type_id' => $journal->id
                ])->where('delete_status', '0')->get();

                if ($ledgerRows->count() == 0) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                $actual = $ledgerRows->map(function ($l) {
                    return [
                        'account_id' => (int)$l->account_id,
                        'debit'      => round((float)$l->debit, 2),
                        'credit'     => round((float)$l->credit, 2),
                    ];
                });

                $actual = collect($actual)
                    ->sortBy([
                        ['account_id', 'asc'],
                        ['debit', 'asc'],
                        ['credit', 'asc'],
                    ])
                    ->values()
                    ->toArray();
                $ledgers = collect($actual);

                $totalDebit = round($ledgers->sum('debit'), 2);
                $totalCredit = round($ledgers->sum('credit'), 2);

                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }
                $validAccounts = $details->pluck('account_name')->unique();

                $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

                if ($invalidLedger->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }

                if ($expected !== $actual) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                if (count($expected) !== count($actual)) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $journal->module = 'Journal';
                    $journal->reference = $journal->voucher_no ?? '';
                    $journal->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('journal', $journal)) {
                        $journal->edit_url = $this->getEditUrl('journal', $journal->id);
                    } else {
                        $journal->edit_url = null;
                    }
                    $data[] = $journal;
                }
            }
        }
        if ($type == 'contra') {

            $contras = \App\Models\Contra::where('company_id', $company_id);

            if ($from) $contras->whereDate('date', '>=', $from);
            if ($to)   $contras->whereDate('date', '<=', $to);

            $contras = $contras->get();

            foreach ($contras as $contra) {

                $isMismatch = false;
                $reasons = [];

                if ($contra->delete == 1) {

                    $hasActiveDetails = \App\Models\ContraDetails::where('contra_id', $contra->id)
                        ->where('delete', '0')
                        ->exists();

                    $hasActiveLedger = \App\Models\AccountLedger::where('entry_type', 8)
                        ->where('entry_type_id', $contra->id)
                        ->where('delete_status', '0')
                        ->exists();

                    if ($hasActiveDetails || $hasActiveLedger) {
                        $isMismatch = true;
                        $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                    }

                    if ($isMismatch) {
                        $reasons = array_unique($reasons);
                        $contra->module = 'Contra';
                        $contra->reference = $contra->voucher_no ?? '';
                        $contra->reason = implode(', ', $reasons);
                        if ($this->canEditTransaction('contra', $contra)) {
                            $contra->edit_url = $this->getEditUrl('contra', $contra->id);
                        } else {
                            $contra->edit_url = null;
                        }
                        $data[] = $contra;
                    }

                    continue;
                }

                $details = \App\Models\ContraDetails::where('contra_id', $contra->id)->get();

                if ($details->count() == 0) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                $expected = $details->map(function ($d) {
                    return [
                        'detail_id' => (int)$d->id,
                        'account_id'=> (int)$d->account_name,
                        'debit'     => round((float)$d->debit, 2),
                        'credit'    => round((float)$d->credit, 2),
                        'narration' => trim((string)$d->narration),
                    ];
                });

                $expected = collect($expected)
                    ->sortBy([
                        ['detail_id','asc']
                    ])
                    ->values()
                    ->toArray();

                $ledgerRows = \App\Models\AccountLedger::where([
                    'entry_type'    => 8,
                    'entry_type_id' => $contra->id
                ])->where('delete_status', '0')->get();

                if ($ledgerRows->count() == 0) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                $actual = $ledgerRows->map(function ($l) {
                    return [
                        'detail_id' => (int)$l->entry_type_detail_id,
                        'account_id'=> (int)$l->account_id,
                        'debit'     => round((float)$l->debit, 2),
                        'credit'    => round((float)$l->credit, 2),
                        'narration' => trim((string)$l->entry_narration),
                    ];
                });

                $actual = collect($actual)
                    ->sortBy([
                        ['detail_id','asc']
                    ])
                    ->values()
                    ->toArray();
                $ledgers = collect($actual);

                $totalDebit = round($ledgers->sum('debit'), 2);
                $totalCredit = round($ledgers->sum('credit'), 2);

                if ($totalDebit !== $totalCredit) {
                    $isMismatch = true;
                    $reasons[] = 'Debit and Credit not equal';
                }
                $validAccounts = $details->pluck('account_name')->unique();

                $invalidLedger = $ledgers->whereNotIn('account_id', $validAccounts);

                if ($invalidLedger->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }

                if ($expected !== $actual) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                if (count($expected) !== count($actual)) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                foreach ($actual as $row) {

                    if ($row['debit'] > 0 && $row['credit'] > 0) {
                        $isMismatch = true;
                        $reasons[] = 'Debit and Credit not equal';
                    }

                    if ($row['debit'] == 0 && $row['credit'] == 0) {
                        $isMismatch = true;
                        $reasons[] = 'Without GST structure mismatch';
                    }
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $contra->module = 'Contra';
                    $contra->reference = $contra->voucher_no ?? '';
                    $contra->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('contra', $contra)) {
                        $contra->edit_url = $this->getEditUrl('contra', $contra->id);
                    } else {
                        $contra->edit_url = null;
                    }
                                        $data[] = $contra;
                }
            }
        }
        if ($type == 'stock_journal') {

            $journals = \App\Models\StockJournal::where('company_id', $company_id);

            if ($from) $journals->whereDate('jdate', '>=', $from);
            if ($to)   $journals->whereDate('jdate', '<=', $to);

            $journals = $journals->get();

            foreach ($journals as $sj) {

                $isMismatch = false;
                $reasons = [];

                $allDetails = \App\Models\StockJournalDetail::where('parent_id', $sj->id)
                    ->where('company_id', $company_id)
                    ->get();

                $allLedger = \App\Models\ItemLedger::where('source', 3)
                    ->where('source_id', $sj->id)
                    ->where('company_id', $company_id)
                    ->get();

                if ($allDetails->where('delete', 1)->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                }

                if ($allLedger->where('delete_status', 1)->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Cancelled/Deleted entry has active data or invalid total';
                }

                if ($allDetails->count() > 0 && $allLedger->count() == 0) {
                    $isMismatch = true;
                    $reasons[] = 'Ledger entries missing';
                }

                if ($allDetails->count() == 0 && $allLedger->count() > 0) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                if (!$sj->jdate || $sj->jdate == '0000-00-00') {
                    $isMismatch = true;
                    $reasons[] = 'Without GST structure mismatch';
                }

                $details = \App\Models\StockJournalDetail::where('parent_id', $sj->id)
                    ->where('company_id', $company_id)
                    ->get();

                if ($details->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Items missing';
                }

                $expected = [];

                foreach ($details as $d) {

                    if (!empty($d->consume_item)) {
                        $expected[] = [
                            'item_id'    => (int)$d->consume_item,
                            'qty'        => round((float)$d->consume_weight, 2),
                            'price'      => round((float)$d->consume_price, 2),
                            'amount'     => round((float)$d->consume_amount, 2),
                            'type'       => 'OUT',
                        ];
                    }

                    if (!empty($d->new_item)) {
                        $expected[] = [
                            'item_id'    => (int)$d->new_item,
                            'qty'        => round((float)$d->new_weight, 2),
                            'price'      => round((float)$d->new_price, 2),
                            'amount'     => round((float)$d->new_amount, 2),
                            'type'       => 'IN',
                        ];
                    }
                }

                $expected = collect($expected)->sortBy([
                    ['item_id', 'asc'],
                    ['type',    'asc'],
                    ['qty',     'asc'],
                    ['price',   'asc'],
                    ['amount',  'asc'],
                ])->values()->toArray();

                $ledgerRows = \App\Models\ItemLedger::where('source', 3)
                    ->where('source_id', $sj->id)
                    ->where('company_id', $company_id)
                    ->get();

                if ($ledgerRows->isEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Item ledger missing';
                }

                $actual = $ledgerRows->map(function ($l) {

                    $isIn = $l->in_weight !== null;

                    return [
                        'item_id' => (int)$l->item_id,
                        'qty'     => round($isIn ? (float)$l->in_weight : (float)$l->out_weight, 2),
                        'price'   => round((float)$l->price, 2),
                        'amount'  => round((float)$l->total_price, 2),
                        'type'    => $isIn ? 'IN' : 'OUT',
                    ];
                });

                $actual = collect($actual)->sortBy([
                    ['item_id', 'asc'],
                    ['type',    'asc'],
                    ['qty',     'asc'],
                    ['price',   'asc'],
                    ['amount',  'asc'],
                ])->values()->toArray();

                if (count($expected) !== count($actual)) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                $expectedPairs = collect($expected)->map(function ($e) {
                    return $e['item_id'] . '_' . $e['type'];
                });

                $actualPairs = collect($actual)->map(function ($a) {
                    return $a['item_id'] . '_' . $a['type'];
                });

                if ($expectedPairs->sort()->values()->toArray() !== $actualPairs->sort()->values()->toArray()) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                        $validPairs = collect($expected)->map(function ($e) {
                    return $e['item_id'] . '_' . $e['type'];
                });

                $orphans = collect($actual)->filter(function ($a) use ($validPairs) {
                    return !$validPairs->contains($a['item_id'] . '_' . $a['type']);
                });
                if (count($actual) !== count(array_unique($actual, SORT_REGULAR))) {
                    $isMismatch = true;
                    $reasons[] = 'Item count mismatch';
                }

                if ($orphans->isNotEmpty()) {
                    $isMismatch = true;
                    $reasons[] = 'Invalid/orphan ledger entries found';
                }

                foreach ($details as $d) {

                    if (!empty($d->consume_item)) {

                        $match = collect($actual)->first(function ($row) use ($d) {
                            return $row['item_id'] === (int)$d->consume_item
                                && $row['type']    === 'OUT'
                                && $row['qty']     === round((float)$d->consume_weight, 2)
                                && $row['price']   === round((float)$d->consume_price, 2)
                                && $row['amount']  === round((float)$d->consume_amount, 2);
                        });

                        if (!$match) {
                            $isMismatch = true;
                            $reasons[] = 'Item details mismatch with ledger';
                        }
                    }

                    if (!empty($d->new_item)) {

                        $match = collect($actual)->first(function ($row) use ($d) {
                            return $row['item_id'] === (int)$d->new_item
                                && $row['type']    === 'IN'
                                && $row['qty']     === round((float)$d->new_weight, 2)
                                && $row['price']   === round((float)$d->new_price, 2)
                                && $row['amount']  === round((float)$d->new_amount, 2);
                        });

                        if (!$match) {
                            $isMismatch = true;
                            $reasons[] = 'Item details mismatch with ledger';
                        }
                    }
                }

                if ($expected !== $actual) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                foreach ($ledgerRows as $l) {
                    $hasIn  = $l->in_weight !== null;
                    $hasOut = $l->out_weight !== null;

                    if ($hasIn && $hasOut) {
                        $isMismatch = true;
                        $reasons[] = 'Item details mismatch with ledger';
                    }

                    if (!$hasIn && !$hasOut) {
                        $isMismatch = true;
                        $reasons[] = 'Item details mismatch with ledger';
                    }
                }

                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $sj->date      = $sj->jdate;
                    $sj->module    = 'Stock Journal';
                    $sj->reference = $sj->voucher_no ?? '';
                    $sj->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('stock_journal', $sj)) {
                        $sj->edit_url = $this->getEditUrl('stock_journal', $sj->id);
                    } else {
                        $sj->edit_url = null;
                    }
                    $data[] = $sj;
                }
            }
            $orphanLedgers = \App\Models\ItemLedger::where('source', 3)
                ->where('company_id', $company_id)
                ->get()
                ->filter(function ($l) {
                    return !\App\Models\StockJournal::where('id', $l->source_id)->exists();
                });

            foreach ($orphanLedgers as $l) {

                $fake = new \stdClass();

                $fake->date      = $l->txn_date ?? null;
                $fake->module    = 'Stock Journal';
                $fake->reference = 'Deleted Stock Journal';

                $fake->reason = 'Ledger exists but Stock Journal deleted (partial delete)';
                $fake->edit_url = null;
                $data[] = $fake;
            }
        }
        if ($type == 'stock_transfer') {

            $transfers = \App\Models\StockTransfer::where('delete_status', '0')
                                                ->where('company_id', $company_id);

            if ($from) $transfers->whereDate('transfer_date', '>=', $from);
            if ($to)   $transfers->whereDate('transfer_date', '<=', $to);

            $transfers = $transfers->get();

            foreach ($transfers as $st) {

                $isMismatch = false;
                $reasons = [];

                // =========================
                // 1. ITEMS CHECK
                // =========================
                $items = \App\Models\StockTransferDescription::where('stock_transfer_id', $st->id)
                                                            ->where('company_id', $company_id)
                                                            ->get();

                $ledger = ItemLedger::where([
                    'source'    => 6,
                    'source_id' => $st->id,
                    'company_id'=> $company_id
                ])->get();

                $expectedItems = [];

                foreach ($items as $i) {

                    // OUT
                    $expectedItems[] = [
                        'item_id' => (int)$i->goods_discription,
                        'qty'     => (float)$i->qty,
                        'price'   => (float)$i->price,
                        'amount'  => (float)$i->amount,
                        'type'    => 'OUT',
                    ];

                    // IN
                    $expectedItems[] = [
                        'item_id' => (int)$i->goods_discription,
                        'qty'     => (float)$i->qty,
                        'price'   => (float)$i->price,
                        'amount'  => (float)$i->amount,
                        'type'    => 'IN',
                    ];
                }

                $expectedItems = collect($expectedItems)
                    ->sortBy(['item_id','qty','price','amount','type'])
                    ->values()
                    ->toArray();

                $actualItems = $ledger->map(function ($l) {
                    return [
                        'item_id' => (int)$l->item_id,
                        'qty'     => $l->in_weight ? (float)$l->in_weight : (float)$l->out_weight,
                        'price'   => (float)$l->price,
                        'amount'  => (float)$l->total_price,
                        'type'    => $l->in_weight ? 'IN' : 'OUT',
                    ];
                });

                $actualItems = collect($actualItems)
                    ->sortBy(['item_id','qty','price','amount','type'])
                    ->values()
                    ->toArray();

                if ($expectedItems !== $actualItems) {
                    $isMismatch = true;
                    $reasons[] = 'Item details mismatch with ledger';
                }

                // =========================
                // 2. SUNDRY LEDGER CHECK (MATCH STORE EXACTLY)
                // =========================
                $sundries = \App\Models\StockTransferSundry::where('stock_transfer_id', $st->id)
                                                            ->where('company_id', $company_id)
                                                            ->get();

                $expectedLedger = [];

                foreach ($sundries as $s) {

                    $bill = \App\Models\BillSundrys::find($s->bill_sundry);
                    if (!$bill) {
                        $isMismatch = true;
                        $reasons[] = 'Bill sundry not found';
                        continue;
                    }

                    if ($bill->adjust_sale_amt == 'No') {

                        // CREDIT (from_series)
                        $expectedLedger[] = [
                            'account_id' => (int)$bill->sale_amt_account,
                            'debit'      => 0.0,
                            'credit'     => (float)$s->amount,
                        ];

                        // DEBIT (to_series)
                        $expectedLedger[] = [
                            'account_id' => (int)$bill->sale_amt_account,
                            'debit'      => (float)$s->amount,
                            'credit'     => 0.0,
                        ];
                    }
                }

                $actualLedger = AccountLedger::where([
                    'entry_type'    => 11,
                    'entry_type_id' => $st->id,
                    'company_id'    => $company_id
                ])->get()->map(function ($l) {
                    return [
                        'account_id' => (int)$l->account_id,
                        'debit'      => (float)$l->debit,
                        'credit'     => (float)$l->credit,
                    ];
                });

                $expectedLedger = collect($expectedLedger)
                    ->sortBy(['account_id','debit','credit'])
                    ->values()
                    ->toArray();

                $actualLedger = collect($actualLedger)
                    ->sortBy(['account_id','debit','credit'])
                    ->values()
                    ->toArray();

                if ($expectedLedger !== $actualLedger) {
                    $isMismatch = true;
                    $reasons[] = 'Bill sundry ledger mismatch';
                }

                // =========================
                // FINAL PUSH
                // =========================
                if ($isMismatch) {
                    $reasons = array_unique($reasons);
                    $st->date = $st->transfer_date;
                    $st->module = 'Stock Transfer';
                    $st->reference = $st->voucher_no ?? '';
                    $st->reason = implode(', ', $reasons);
                    if ($this->canEditTransaction('stock_transfer', $st)) {
                        $st->edit_url = $this->getEditUrl('stock_transfer', $st->id);
                    } else {
                        $st->edit_url = null;
                    }
                    $data[] = $st;
                }
            }
        }
        return view('TransactionIntegrity.index', compact('data'));
    }
}