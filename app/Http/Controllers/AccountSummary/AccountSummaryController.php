<?php

namespace App\Http\Controllers\AccountSummary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountGroups;
use App\Models\Accounts;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\CommonHelper;

class AccountSummaryController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from_date ?? Carbon::now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $companyId = Session::get('user_company_id');
        $type = $request->type;
        $id   = $request->id;
        $allGroups = collect();
        $groupFilter = $request->group_id ?? null;
        if (!$type && !$id) {
            
            $heads = DB::table('account_headings')
                ->where('status', '1')
                ->where('delete', '0')
                ->orderBy('name')
                ->get();

            $stock_opening_total = 0;
            $stock_closing_total = 0;
            
            // calculate once
            $from_subday = $from 
                ? Carbon::parse($from)->subDay()->toDateString() 
                : Carbon::now()->startOfMonth()->subDay()->toDateString();
            
            /* ================== OPENING STOCK ================== */
            
            // Base closing stock
            $stock_opening_total = round(CommonHelper::ClosingStock($from_subday), 2);
            
            // Add transit (opening)
            $baseQueryOpening = DB::table('purchases')
                ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$from_subday])
                ->whereDate('stock_entry_date', '>', $from_subday)
                ->where('status', '1')
                ->where('delete', '0');
            
            $stock_in_transit_opening = (clone $baseQueryOpening)
                ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                ->value('total');
            
            $stock_in_transit_opening = round($stock_in_transit_opening ?? 0, 2);
            
            // ✅ FINAL OPENING
            $stock_opening_total += $stock_in_transit_opening;
            
            
            /* ================== CLOSING STOCK ================== */
            
            // Base closing stock
            $stock_closing_total = round(CommonHelper::ClosingStock($to), 2);
            
            // Add transit (closing)
            $baseQuery = DB::table('purchases')
                ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to])
                ->whereDate('stock_entry_date', '>', $to)
                ->where('status', '1')
                ->where('delete', '0');
            
            $stock_in_transit_value = (clone $baseQuery)
                ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                ->value('total');
            
            $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);
            
            // ✅ FINAL CLOSING
            $stock_closing_total += $stock_in_transit_value;


            foreach ($heads as $head) {
                
                $groupIds = AccountGroups::where('heading', $head->id)
                    ->where(function ($q) {
                        $q->whereNull('heading_type')
                          ->orWhere('heading_type', 'head')
                          ->orWhere('heading_type', '');
                    })
                    ->whereIn('company_id', [$companyId, 0])
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->pluck('id')
                    ->toArray();

                
                $childGroupIds = $this->getAllChildGroups1($groupIds, $companyId);
                $finalGroupIds = array_merge($groupIds, $childGroupIds);
              // array_push($finalGroupIds,$head->id);
                //$allGroupIds = $groupIds;
                //$this->getAllChildGroups($groupIds, $allGroupIds);
                //$groupIds = $allGroupIds;
        
                if (empty($finalGroupIds)&& !$head) {
                    $head->debit  = 0;
                    $head->credit = 0;
                    continue;
                }
                
                // $accountIds = Accounts::whereIn('under_group', $finalGroupIds)
                //     ->whereIn('company_id', [$companyId, 0])
                //     ->where('status', '1')
                //     ->where('delete', '0')
                //     ->pluck('id');
                    
                    
                    $accountIds = Accounts::where(function ($q) use ($finalGroupIds, $head, $companyId) {

                    // ✅ GROUP ACCOUNTS
                    $q->where(function ($q1) use ($finalGroupIds, $companyId) {
                        $q1->whereIn('under_group', $finalGroupIds)
                           ->where('under_group_type', 'group')
                           ->whereIn('company_id', [$companyId, 0]);
                    });
                    // ✅ HEAD ACCOUNTS
                    $q->orWhere(function ($q2) use ($head, $companyId) {
                        $q2->where('under_group', $head->id)
                           ->where('under_group_type', 'head')
                           ->whereIn('company_id', [$companyId, 0]);
                    });
                
                })
                ->where('status', '1')   // ✅ applied to both
                ->where('delete', '0')   // ✅ applied to both
                ->pluck('id');


            
                            if ($accountIds->isEmpty()) {
                                $head->debit  = 0;
                                $head->credit = 0;
                                continue;
                            }
            
                            $ledgerQuery = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->where('status', '1')
                ->where('delete_status', '0')
                ->where(function ($q) use ($to) {
                    $q->where('txn_date', '<=', $to)
                      ->orWhere('entry_type', '-1');
                });
            
            $ledgerData = $ledgerQuery
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
            
            $totalDebit  = (float) ($ledgerData->debit ?? 0);
            $totalCredit = (float) ($ledgerData->credit ?? 0);
            
            $closing = $totalDebit - $totalCredit;
            
            //             // ✅ SPECIAL CASE: PROFIT & LOSS (HEAD ID = 4)
            // if ($head->id == 4) {
            
            //     $profitloss = CommonHelper::profitLoss("25-26", $from, $to);
            //     $profitloss = round($profitloss, 2);
            
            //     // Opening = 0 (or keep if you want cumulative)
            //     $opening = 0;
            
            //     // No debit/credit split (optional)
            //     $head->debit  = 0;
            //     $head->credit = 0;
            
            //     // Assign closing
            //     $head->opening = 0;
            //     $head->opening_type = 'Dr';
            
            //     $head->closing = abs($profitloss);
            //     $head->closing_type = $profitloss >= 0 ? 'Cr' : 'Dr';
            
            //     continue; // 🔥 IMPORTANT → skip normal logic
            // }
            // Opening = before from_date
            
            // ✅ SPECIAL CASE: PROFIT & LOSS (HEAD ID = 4)
            if ($head->id == 4) {
            
                // Financial year start (adjust if needed)
                $fyStart = date('Y-04-01', strtotime($from));
                if (date('m', strtotime($from)) < 4) {
                    $fyStart = date('Y-04-01', strtotime('-1 year', strtotime($from)));
                }
            
                // 👉 OPENING P&L (FY start → one day before from date)
                $from_subday = Carbon::parse($from)->subDay()->toDateString();
            
                $openingPL = CommonHelper::profitLoss(Session::get('default_fy'), $fyStart, $from_subday);
                $openingPL = round($openingPL, 2);
            
                // 👉 CLOSING P&L (FY start → to date)
                $closingPL = CommonHelper::profitLoss(Session::get('default_fy'), $fyStart, $to);
                $closingPL = round($closingPL, 2);
            
                // No period debit/credit for P&L head
                $head->debit  = 0;
                $head->credit = 0;
            
                // ✅ Assign Opening
                $head->opening = abs($openingPL);
                $head->opening_type = $openingPL >= 0 ? 'Cr' : 'Dr';
            
                // ✅ Assign Closing
                $head->closing = abs($closingPL);
                $head->closing_type = $closingPL >= 0 ? 'Cr' : 'Dr';
            
                continue; // 🚀 VERY IMPORTANT (skip normal ledger logic)
            }
            $openingData = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->where('status', '1')
                ->where('delete_status', '0')
                ->where(function ($q) use ($from) {
                    $q->where('txn_date', '<', $from)
                      ->orWhere('entry_type', '-1');
                })
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
            
            $opening = (float)($openingData->debit ?? 0) - (float)($openingData->credit ?? 0);
            
            // Period movement
            $periodData = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->where('status', '1')
                ->where('delete_status', '0')
                ->whereBetween('txn_date', [$from, $to])
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
            
            $periodDebit  = (float)($periodData->debit ?? 0);
            $periodCredit = (float)($periodData->credit ?? 0);
            
            $periodDebit = round($periodDebit,2);
                            $head->debit  = $periodDebit;
                            $periodCredit = round($periodCredit,2);
                            $head->credit = $periodCredit;
              
               $opening = round($opening,2);
                            $head->opening = abs($opening);
                            $head->opening_type = $opening >= 0 ? 'Dr' : 'Cr';
            
            $closing = round($closing,2);
                            $head->closing = abs($closing);
                            
                            $head->closing_type = $closing >= 0 ? 'Dr' : 'Cr';
                            
                                                    // ✅ ADD STOCK INTO CURRENT ASSETS (HEAD ID = 7)
                        if ($head->id == 7) {
                        
                            // Opening
                            $head->opening += $stock_opening_total;
                        
                            // Closing
                            $head->closing += $stock_closing_total;
                        
                            // Force type (stock is always Dr)
                            $head->opening_type = 'Dr';
                            $head->closing_type = 'Dr';
                        }
                        }
            
                        return view('AccountSummary.index', compact('heads', 'from', 'to'));
                    }
                    
                    
                    
                    
                    
                    

        if ($type === 'head') {

            $heading = DB::table('account_headings')
                ->where('id', $id)
                ->where('status', '1')
                ->where('delete', '0')
                ->first();

            if (!$heading) {
                abort(404);
            }


            $groups = AccountGroups::where('heading', $id)
                ->where(function ($q) {
                        $q->whereNull('heading_type')
                          ->orWhere('heading_type', 'head');
                    })
                ->where('status', '1')
                ->where('delete', '0')
                ->orderBy('name')
                ->get();

         
                
            //                 // ✅ SPECIAL CASE: STOCK IN HAND (HEAD ID = 7)
            $stockHandled = false;
            $allowedGroupIds = [];
            if ($groupFilter) {
                $allowedGroupIds[] = $groupFilter;

                $parentId = $groupFilter;
                $checkedIds = []; // 🔥 prevent infinite loop

                while ($parentId && !in_array($parentId, $checkedIds)) {

                    $checkedIds[] = $parentId;

                    $parent = AccountGroups::find($parentId);
                    if (!$parent) break;

                    if (!empty($parent->heading)) {
                        $allowedGroupIds[] = $parent->heading;
                    }

                    $parentId = $parent->heading;
                }
            }
            if ($groupFilter) {

                $groups = AccountGroups::where('id', $groupFilter)
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->get();

            }
            foreach ($groups as $g) {
                if ($groupFilter) {

                    // show only if this group is selected OR it is parent of selected
                    if ($g->id != $groupFilter) {

                        // check if selected group belongs under this group
                        $childIds = [];
                        $this->getChildGroups($g->id, $childIds);

                        if (!in_array($groupFilter, $childIds)) {
                            continue;
                        }
                    }
                }
                // ✅ HANDLE STOCK ONLY ONCE (ID = 30)
                if (!$stockHandled && $g->id == 30) {

                    $from_subday = $request->from_date 
                        ? Carbon::parse($request->from_date)->subDay()->toDateString() 
                        : Carbon::now()->startOfMonth()->subDay()->toDateString();

                    /* ================= OPENING ================= */

                    $stock_in_hand_opening = round(CommonHelper::ClosingStock($from_subday), 2);
                

                    // 👉 ADD TRANSIT (OPENING)
                    $baseQueryOpening = DB::table('purchases')
                        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$from_subday])
                        ->whereDate('stock_entry_date', '>', $from_subday)
                        ->where('status', '1')
                        ->where('delete', '0');

                    $stock_in_transit_opening = (clone $baseQueryOpening)
                        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                        ->value('total');

                    $stock_in_transit_opening = round($stock_in_transit_opening ?? 0, 2);

                    $stock_in_hand_opening += $stock_in_transit_opening;


                    /* ================= CLOSING ================= */

                    $stock_in_hand = round(CommonHelper::ClosingStock($to), 2);

                    $baseQuery = DB::table('purchases')
                        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to])
                        ->whereDate('stock_entry_date', '>', $to)
                        ->where('status', '1')
                        ->where('delete', '0');

                    $stock_in_transit_value = (clone $baseQuery)
                        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                        ->value('total');

                    $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);

                    $stock_in_hand += $stock_in_transit_value;


                    /* ================= ASSIGN ================= */

                    $g->debit  = 0;
                    $g->credit = 0;

                    $g->opening = $stock_in_hand_opening;
                    $g->opening_type = 'Dr';

                    $g->closing = $stock_in_hand;
                    $g->closing_type = 'Dr';

                    $stockHandled = true;

                    continue;
                }
                if ($stockHandled && $g->id == 30) {
                    continue;
                }
            

                $isProfitLoss = ($heading->id == 4);
                
                $fyStart = date('Y-04-01', strtotime($from));
                
                // If date is Jan–Mar, FY started last year
                if (date('m', strtotime($from)) < 4) {
                    $fyStart = date('Y-04-01', strtotime($from . ' -1 year'));
                }
                $allGroupIds = [$g->id];
                $this->getChildGroups($g->id, $allGroupIds);

                $accountIds = Accounts::whereIn('under_group', $allGroupIds)
                    ->where('under_group_type', 'group')
                    ->where('delete','0')
                    ->whereIn('company_id', [$companyId, 0])
                    ->pluck('id');

                // Period totals
                $period = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->whereBetween('txn_date', [$from, $to])
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();

                // Opening totals
              $openingQuery = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->where('delete_status', '0');

                if ($isProfitLoss) {
                    // Only from FY start to FROM DATE
                    $openingQuery->whereBetween('txn_date', [$fyStart, date('Y-m-d', strtotime($from . ' -1 day'))]);
                } else {
                    // Normal balance sheet logic
                    $openingQuery->where('txn_date', '<', $from);
                }

                $openingData = $openingQuery
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();

                                $periodDebit  = (float)($period->debit ?? 0);
                                $periodCredit = (float)($period->credit ?? 0);

                                $openingDebit  = (float)($openingData->debit ?? 0);
                                $openingCredit = (float)($openingData->credit ?? 0);

                                $opening = $openingDebit - $openingCredit;

                                // ===== ADD BASIC OPENING ENTRY =====
                                $basicOpening = DB::table('account_ledger')
                                    ->whereIn('account_id', $accountIds)
                                    ->where('company_id', $companyId)
                                    ->where('entry_type', -1)
                                    ->where('delete_status', '0')
                                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                                    ->first();

                                // if ($basicOpening) {
                                //     $opening += (float)($basicOpening->debit ?? 0);
                                //     $opening -= (float)($basicOpening->credit ?? 0);
                                // }
                                if (!$isProfitLoss && $basicOpening) {
                    $opening += (float)($basicOpening->debit ?? 0);
                    $opening -= (float)($basicOpening->credit ?? 0);
                }

                $closing = $opening + $periodDebit - $periodCredit;
                 $opening = round($opening, 2);
                    $closing = round($closing, 2);
                    
                    if (abs($opening) < 0.0001) {
                        $opening = 0;
                    }
                    
                    if (abs($closing) < 0.0001) {
                        $closing = 0;
                    }

                $periodDebit = round($periodDebit, 2);
                $g->debit  = $periodDebit;
                $periodCredit = round($periodCredit, 2);
                $g->credit = $periodCredit;

                $g->opening = abs($opening);
                $g->opening_type = $opening >= 0 ? 'Dr' : 'Cr';

                $g->closing = abs($closing);
                $g->closing_type = $closing >= 0 ? 'Dr' : 'Cr';
            }
                $accounts = Accounts::where('under_group', $heading->id)
                    ->where('under_group_type', 'head')
                    ->where('delete', '0')
                    ->whereIn('company_id', [$companyId, 0])
                    ->get();
            $allGroupsForDropdown = AccountGroups::where('heading', $id)
                ->where(function ($q) {
                    $q->whereNull('heading_type')
                    ->orWhere('heading_type', 'head');
                })
                ->where('status', '1')
                ->where('delete', '0')
                ->orderBy('name')
                ->get();

                $allGroups = collect();

                foreach ($allGroupsForDropdown as $g) {

                    $checkIds = [$g->id];
                    $this->getChildGroups($g->id, $checkIds);

                    $checkAccountIds = Accounts::whereIn('under_group', $checkIds)
                        ->where('under_group_type', 'group')
                        ->where('delete', '0')
                        ->whereIn('company_id', [$companyId, 0])
                        ->pluck('id');

                    if ($checkAccountIds->isEmpty()) {
                        continue; // skip empty groups
                    }

                    $hasData = DB::table('account_ledger')
                        ->whereIn('account_id', $checkAccountIds)
                        ->where('company_id', $companyId)
                        ->where('delete_status', '0')
                        ->exists();

                    if (!$hasData) {
                        continue; // skip groups with no ledger entries
                    }

                    $allGroups->push($g);

                    $childIds = [];
                    $this->getChildGroups($g->id, $childIds);

                    if (!empty($childIds)) {
                        $childGroups = AccountGroups::whereIn('id', $childIds)
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->get();

                        foreach ($childGroups as $child) {

                            $childCheckIds = [$child->id];
                            $this->getChildGroups($child->id, $childCheckIds);

                            $childAccountIds = Accounts::whereIn('under_group', $childCheckIds)
                                ->where('under_group_type', 'group')
                                ->where('delete', '0')
                                ->whereIn('company_id', [$companyId, 0])
                                ->pluck('id');

                            if ($childAccountIds->isEmpty()) continue;

                            $childHasData = DB::table('account_ledger')
                                ->whereIn('account_id', $childAccountIds)
                                ->where('company_id', $companyId)
                                ->where('delete_status', '0')
                                ->exists();

                            if (!$childHasData) continue;

                            $allGroups->push($child);
                        }
                    }
                }
                if (!$groupFilter) {
                    foreach ($accounts as $acc) {
                    
                    $isProfitLoss = ($heading->id == 4);

                    // ✅ FY start calculation
                    $fyStart = date('Y-04-01', strtotime($from));
                    if (date('m', strtotime($from)) < 4) {
                        $fyStart = date('Y-04-01', strtotime($from . ' -1 year'));
                    }

                    /* ================= PERIOD ================= */
                    $period = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->whereBetween('txn_date', [$from, $to])
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();

                    /* ================= OPENING ================= */
                    $openingQuery = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->where('delete_status', '0');

                    if ($isProfitLoss) {
                        // ✅ ONLY FY DATA
                        $openingQuery->whereBetween('txn_date', [
                            $fyStart,
                            date('Y-m-d', strtotime($from . ' -1 day'))
                        ]);
                    } else {
                        // ✅ FULL HISTORY
                        $openingQuery->where('txn_date', '<', $from);
                    }

                    $openingData = $openingQuery
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();

                    /* ================= VALUES ================= */
                    $periodDebit  = (float)($period->debit ?? 0);
                    $periodCredit = (float)($period->credit ?? 0);

                    $openingDebit  = (float)($openingData->debit ?? 0);
                    $openingCredit = (float)($openingData->credit ?? 0);

                    $opening = $openingDebit - $openingCredit;

                    /* ================= OPENING ENTRY ================= */
                    $basicOpening = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->where('entry_type', -1)
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();

                    // ❗ IMPORTANT: Skip for P&L
                    if (!$isProfitLoss && $basicOpening) {
                        $opening += (float)($basicOpening->debit ?? 0);
                        $opening -= (float)($basicOpening->credit ?? 0);
                    }

                    /* ================= CLOSING ================= */
                    $closing = $opening + $periodDebit - $periodCredit;

                    /* ================= ROUND ================= */
                    $opening = round($opening, 2);
                    $closing = round($closing, 2);

                    if (abs($opening) < 0.0001) $opening = 0;
                    if (abs($closing) < 0.0001) $closing = 0;

                    /* ================= ASSIGN ================= */
                    $acc->debit  = round($periodDebit, 2);
                    $acc->credit = round($periodCredit, 2);

                    $acc->opening = abs($opening);
                    $acc->opening_type = $opening >= 0 ? 'Dr' : 'Cr';

                    $acc->closing = abs($closing);
                    $acc->closing_type = $closing >= 0 ? 'Dr' : 'Cr';
                }   
                }
                // $accounts = collect();
                return view('AccountSummary.details', compact(
                    'heading',
                    'groups',
                    'accounts', 
                    'from',
                    'to',
                    'allGroups'
                ));
            }


            if ($type === 'group') {

                $group = AccountGroups::where('id', $id)
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->firstOrFail();
                $parentGroup = $group;
                $allGroupIds = [$group->id];
                $this->getChildGroups($group->id, $allGroupIds);
                $groups = AccountGroups::where('heading', $group->id)
                    ->where('status', '1')
                    ->where('heading_type', 'group')
                    ->where('delete', '0')
                    ->orderBy('name')
                    ->get();

                $accounts = Accounts::where('under_group', $group->id)
                    ->where('under_group_type', 'group')
                    ->whereIn('company_id', [$companyId, 0])
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->orderBy('account_name')
                    ->get();
    
                // ✅ Detect P&L correctly even for nested groups
                $headingId = $this->resolveHeadingIdForGroup($group);
                $isProfitLoss = ((int) $headingId === 4);
    
                // ✅ FY start (needed for P&L opening logic)
                $fyStart = date('Y-04-01', strtotime($from));
                if (date('m', strtotime($from)) < 4) {
                    $fyStart = date('Y-04-01', strtotime($from . ' -1 year'));
                }
                foreach ($groups as $g) {
                    $allGroupIds = [$g->id];
                    $this->getChildGroups($g->id, $allGroupIds);
            
                    $accountIds = Accounts::whereIn('under_group', $allGroupIds)
                        ->where('under_group_type', 'group')
                        ->where('delete','0')
                        ->whereIn('company_id', [$companyId, 0])
                        ->pluck('id');
            
                    if ($accountIds->isEmpty()) {
                        $g->debit = 0;
                        $g->credit = 0;
                        $g->opening = 0;
                        $g->opening_type = 'Dr';
                        $g->closing = 0;
                        $g->closing_type = 'Dr';
                        continue;
                    }

                    /* ================= PERIOD ================= */
                    $period = DB::table('account_ledger')
                        ->whereIn('account_id', $accountIds)
                        ->where('company_id', $companyId)
                        ->whereBetween('txn_date', [$from, $to])
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
            
                    /* ================= OPENING ================= */
                    $openingQuery = DB::table('account_ledger')
                        ->whereIn('account_id', $accountIds)
                        ->where('company_id', $companyId)
                        ->where('delete_status', '0');
            
                    if ($isProfitLoss) {
                        // ✅ ONLY FY DATA
                        $openingQuery->whereBetween('txn_date', [
                            $fyStart,
                            date('Y-m-d', strtotime($from . ' -1 day'))
                        ]);
                    } else {
                        // ✅ FULL HISTORY
                        $openingQuery->where('txn_date', '<', $from);
                    }

                    $openingData = $openingQuery
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
            
                    /* ================= VALUES ================= */
                    $periodDebit  = (float)($period->debit ?? 0);
                    $periodCredit = (float)($period->credit ?? 0);
            
                    $openingDebit  = (float)($openingData->debit ?? 0);
                    $openingCredit = (float)($openingData->credit ?? 0);
            
                    $opening = $openingDebit - $openingCredit;

                    /* ================= OPENING ENTRY ================= */
                    $basicOpening = DB::table('account_ledger')
                        ->whereIn('account_id', $accountIds)
                        ->where('company_id', $companyId)
                        ->where('entry_type', -1)
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
            
                    // ❗ IMPORTANT FIX
                    if (!$isProfitLoss && $basicOpening) {
                        $opening += (float)($basicOpening->debit ?? 0);
                        $opening -= (float)($basicOpening->credit ?? 0);
                    }

                    /* ================= CLOSING ================= */
                    $closing = $opening + $periodDebit - $periodCredit;
            
                    /* ================= ROUND ================= */
                    $opening = round($opening, 2);
                    $closing = round($closing, 2);
            
                    if (abs($opening) < 0.0001) $opening = 0;
                    if (abs($closing) < 0.0001) $closing = 0;
            
                    /* ================= ASSIGN ================= */
                    $g->debit  = round($periodDebit, 2);
                    $g->credit = round($periodCredit, 2);
            
                    $g->opening = abs($opening);
                    $g->opening_type = $opening >= 0 ? 'Dr' : 'Cr';
            
                    $g->closing = abs($closing);
                    $g->closing_type = $closing >= 0 ? 'Dr' : 'Cr';
                }
                foreach ($accounts as $acc) {
                    /* ================= PERIOD ================= */
                    $period = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->whereBetween('txn_date', [$from, $to])
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
                
                    /* ================= OPENING ================= */
                    $openingQuery = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->where('delete_status', '0');
                
                    if ($isProfitLoss) {
                        // ✅ ONLY FY DATA
                
                        $openingQuery->whereBetween('txn_date', [
                            $fyStart,
                            date('Y-m-d', strtotime($from . ' -1 day'))
                        ]);
                    } else {
                        // ✅ FULL HISTORY
                        $openingQuery->where('txn_date', '<', $from);
                    }
                
                    $openingData = $openingQuery
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
                
                    /* ================= VALUES ================= */
                    $periodDebit  = (float)($period->debit ?? 0);
                    $periodCredit = (float)($period->credit ?? 0);
                
                    $openingDebit  = (float)($openingData->debit ?? 0);
                    $openingCredit = (float)($openingData->credit ?? 0);
                
                    $opening = $openingDebit - $openingCredit;
                
                    /* ================= OPENING ENTRY ================= */
                    $basicOpening = DB::table('account_ledger')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $companyId)
                        ->where('entry_type', -1)
                        ->where('delete_status', '0')
                        ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                        ->first();
                
                    // ❗ IMPORTANT: Skip for P&L
                    if (!$isProfitLoss && $basicOpening) {
                        $opening += (float)($basicOpening->debit ?? 0);
                        $opening -= (float)($basicOpening->credit ?? 0);
                    }
                
                    /* ================= CLOSING ================= */
                    $closing = $opening + $periodDebit - $periodCredit;
                
                    /* ================= ROUND ================= */
                    $opening = round($opening, 2);
                    $closing = round($closing, 2);
                
                    if (abs($opening) < 0.0001) $opening = 0;
                    if (abs($closing) < 0.0001) $closing = 0;
                
                    /* ================= ASSIGN ================= */
                    $acc->debit  = round($periodDebit, 2);
                    $acc->credit = round($periodCredit, 2);
                
                    $acc->opening = abs($opening);
                    $acc->opening_type = $opening >= 0 ? 'Dr' : 'Cr';
                
                    $acc->closing = abs($closing);
                    $acc->closing_type = $closing >= 0 ? 'Dr' : 'Cr';
                }
            }
                                                
            if ($request->ajax()) {
                    return view('AccountSummary.partials.group_rows', [
                    'groups' => $groups,
                    'accounts' => $accounts,
                    'from' => $from,
                    'to' => $to,
                    'level' => request('level', 2)
                ]);
            }
            $allGroups = $groups;
            return view('AccountSummary.details', compact(
            'group',
            'groups',
            'accounts',
            'from',
            'to',
            'allGroups'
        ));
    }
    private function getChildGroups($parentId, &$groupIds)
    {
        $companyId = session('user_company_id');

        $children = AccountGroups::where('heading', $parentId)
            ->where('heading_type', 'group')   // IMPORTANT
            ->whereIn('company_id', [$companyId, 0])
            ->where('status', '1')
            ->where('delete', '0')
            ->pluck('id');

        foreach ($children as $childId) {
            if (!in_array($childId, $groupIds)) {
                $groupIds[] = $childId;
                $this->getChildGroups($childId, $groupIds);
            }
        }
    }
    /**
     * Resolve the root account_heading id for a group (works for nested groups).
     */
    private function resolveHeadingIdForGroup(AccountGroups $group): ?int
    {
        $visited = [];
        $current = $group;
        $safetyCounter = 0;

        while ($current && ($current->heading_type ?? null) === 'group') {
            if ($safetyCounter++ > 50) {
                break;
            }

            if (in_array($current->id, $visited, true)) {
                break;
            }

            $visited[] = $current->id;

            $parentId = $current->heading;
            if (!$parentId) {
                break;
            }

            $current = AccountGroups::select('id', 'heading', 'heading_type')->find($parentId);
        }

        return $current && $current->heading ? (int) $current->heading : null;
    }
    private function getAllChildGroups($parentIds, &$allIds = [])
    {
        $children = AccountGroups::whereIn('heading', $parentIds)
        ->where('heading_type', 'group') // IMPORTANT
        ->where('status', '1')
        ->where('delete', '0')
        ->pluck('id')
        ->toArray();

    if (empty($children)) {
        return;
    }

    $newChildren = array_diff($children, $allIds);

    if (!empty($newChildren)) {
        $allIds = array_merge($allIds, $newChildren);
        $this->getAllChildGroups($newChildren, $allIds);
    }
}
    private function getAllChildGroups1($parentIds, $companyId)
    {
        $allIds = [];
    
        $children = AccountGroups::whereIn('heading', $parentIds)
            ->where('heading_type', 'group')
            ->where('delete', '0')
            ->whereIn('company_id', [$companyId, 0])
            ->pluck('id')
            ->toArray();
    
        if (!empty($children)) {
    
            $allIds = array_merge($children, 
                $this->getAllChildGroups1($children, $companyId)
            );
        }
    
        return $allIds;
    }
    public function monthSummary(Request $request)
    {
        $accountId = $request->account_id;
        $from      = $request->from_date;
        $to        = $request->to_date;
        $companyId = Session::get('user_company_id');

        $account = Accounts::where('id', $accountId)
            ->whereIn('company_id', [$companyId, 0])
            ->where('status', '1')
            ->where('delete', '0')
            ->firstOrFail();

        $openingData = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('company_id', $companyId)
            ->where('txn_date', '<', $from)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();

        $openingDebit  = (float)($openingData->debit ?? 0);
        $openingCredit = (float)($openingData->credit ?? 0);

        $opening = $openingDebit - $openingCredit;

        $basicOpening = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('company_id', $companyId)
            ->where('entry_type', -1)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();

        if ($basicOpening) {
            $opening += (float)($basicOpening->debit ?? 0);
            $opening -= (float)($basicOpening->credit ?? 0);
        }

        $account->opening = abs($opening);
        $account->opening_type = $opening >= 0 ? 'Dr' : 'Cr';
        $months = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->whereBetween('txn_date', [$from, $to])
            ->where('delete_status', '0')
            ->selectRaw("
                DATE_FORMAT(txn_date, '%Y-%m') as month,
                SUM(debit) as debit,
                SUM(credit) as credit
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('AccountSummary.month', compact(
            'account',
            'months',
            'from',
            'to'
        ));
    }
    public function ledger(Request $request)
    {
        $accountId = $request->account_id;
        $month     = $request->month; 
        $companyId = Session::get('user_company_id');

        $account = Accounts::where('id', $accountId)
            ->whereIn('company_id', [$companyId, 0])
            ->where('status', '1')
            ->where('delete', '0')
            ->firstOrFail();

        $ledgers = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->whereRaw("DATE_FORMAT(txn_date, '%Y-%m') = ?", [$month])
            ->where('delete_status', '0')
            ->orderBy('txn_date')
            ->orderBy('id')
            ->get();

        return view('AccountSummary.ledger', compact(
            'account',
            'ledgers',
            'month'
        ));
    }
    public function exportCSV(Request $request)
    {
        $from = $request->from_date ?? Carbon::now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $companyId = Session::get('user_company_id');
    
        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first();
    
        $heads = DB::table('account_headings')
            ->where('status', '1')
            ->where('delete', '0')
            ->orderBy('name')
            ->get();
    
        $totalDebit  = 0;
        $totalCredit = 0;
    
        foreach ($heads as $head) {
    
            $groupIds = AccountGroups::where('heading', $head->id)
                ->where(function ($q) {
                    $q->whereNull('heading_type')
                      ->orWhere('heading_type', 'head');
                })
                ->whereIn('company_id', [$companyId, 0])
                ->where('status', '1')
                ->where('delete', '0')
                ->pluck('id')
                ->toArray();
    
            $allGroupIds = $groupIds;
            $this->getAllChildGroups($groupIds, $allGroupIds);
    
            $accountIds = Accounts::where(function ($q) use ($allGroupIds, $head, $companyId) {
                $q->where(function ($q1) use ($allGroupIds, $companyId) {
                    $q1->whereIn('under_group', $allGroupIds)
                       ->where('under_group_type', 'group')
                       ->whereIn('company_id', [$companyId, 0]);
                });
    
                $q->orWhere(function ($q2) use ($head, $companyId) {
                    $q2->where('under_group', $head->id)
                       ->where('under_group_type', 'head')
                       ->whereIn('company_id', [$companyId, 0]);
                });
            })->pluck('id');
    
            $period = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->whereBetween('txn_date', [$from, $to])
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
    
            $openingData = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->where('txn_date', '<', $from)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
    
            $periodDebit  = (float)($period->debit ?? 0);
            $periodCredit = (float)($period->credit ?? 0);
    
            $openingDebit  = (float)($openingData->debit ?? 0);
            $openingCredit = (float)($openingData->credit ?? 0);
    
            $opening = $openingDebit - $openingCredit;
    
            $basicOpening = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('entry_type', -1)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
    
            if ($basicOpening) {
                $opening += (float)($basicOpening->debit ?? 0);
                $opening -= (float)($basicOpening->credit ?? 0);
            }
    
            $closing = $opening + $periodDebit - $periodCredit;
    
            $head->opening = abs($opening) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr');
            $head->debit   = $periodDebit;
            $head->credit  = $periodCredit;
            $head->closing = abs($closing) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr');
    
            $totalDebit  += $periodDebit;
            $totalCredit += $periodCredit;
        }
    
        $fileName = 'account_summary_' . now()->format('Ymd_His') . '.csv';
    
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];
    
        $callback = function () use ($heads, $company, $from, $to, $totalDebit, $totalCredit) {
    
            $file = fopen('php://output', 'w');
    
            fputcsv($file, [$company->company_name ?? '']);
            fputcsv($file, [$company->address ?? '']);
            fputcsv($file, ['CIN: ' . ($company->cin ?? '')]);
    
    
            fputcsv($file, []);
    
            fputcsv($file, [
                'Account / Group',
                'Type',
                'Opening',
                'Debit',
                'Credit',
                'Closing'
            ]);
    
            foreach ($heads as $head) {
                if($head->opening==0 && $head->debit==0 && $head->credit==0){
                    continue;
                }
                fputcsv($file, [
                    $head->name,
                    'HEAD',
                    $head->opening,
                    formatIndianNumber($head->debit, 2),
                    formatIndianNumber($head->credit, 2),
                    $head->closing
                ]);
            }
    
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                formatIndianNumber($totalDebit, 2),
                formatIndianNumber($totalCredit, 2),
                ''
            ]);
    
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }
    public function exportDetailsCSV(Request $request)
    {
        $from = $request->from_date;
        $to   = $request->to_date;
        $companyId = Session::get('user_company_id');
        $type = $request->type;
        $id   = $request->id;
        $company = DB::table('companies')->where('id', $companyId)->first();
        $title = '';
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalOpening = 0;
        $totalClosing = 0;
        if ($type === 'head') {
            $heading = DB::table('account_headings')->where('id', $id)->first();
            $title = $heading->name ?? '';
            // ===== GROUPS =====
            $groups = AccountGroups::where('heading', $id)
                ->where(function ($q) {
                    $q->whereNull('heading_type')
                      ->orWhere('heading_type', 'head');
                })
                ->where('status', '1')
                ->where('delete', '0')
                ->get();
            foreach ($groups as $g) {
                $groupIds = [$g->id];
                $this->getChildGroups($g->id, $groupIds);
    
                $accountIds = Accounts::whereIn('under_group', $groupIds)
                    ->where('under_group_type', 'group')
                    ->whereIn('company_id', [$companyId, 0])
                    ->pluck('id');
    
                $period = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->whereBetween('txn_date', [$from, $to])
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                $openingData = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->where('txn_date', '<', $from)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                $periodDebit  = (float)($period->debit ?? 0);
                $periodCredit = (float)($period->credit ?? 0);
    
                $openingDebit  = (float)($openingData->debit ?? 0);
                $openingCredit = (float)($openingData->credit ?? 0);
    
                $opening = $openingDebit - $openingCredit;
    
                $basicOpening = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('entry_type', -1)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                if ($basicOpening) {
                    $opening += (float)($basicOpening->debit ?? 0);
                    $opening -= (float)($basicOpening->credit ?? 0);
                }
    
                $closing = $opening + $periodDebit - $periodCredit;
    
                $rows[] = [
                    $g->name,
                    'GROUP',
                    formatIndianNumber(abs($opening), 2) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr'),
                    formatIndianNumber($periodDebit, 2),
                    formatIndianNumber($periodCredit, 2),
                    formatIndianNumber(abs($closing), 2) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr')
                ];
    
                $totalDebit  += $periodDebit;
                $totalCredit += $periodCredit;
                $totalOpening +=abs($opening);
                $totalClosing +=abs($closing);
            }
            $accounts = Accounts::where('under_group', $id)
                ->where('under_group_type', 'head')
                ->whereIn('company_id', [$companyId, 0])
                ->get();
            if ($accounts->isNotEmpty()) {
                $rows[] = ['Accounts', '', '', '', '', ''];
            }
            foreach ($accounts as $acc) {
                $period = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $companyId)
                    ->whereBetween('txn_date', [$from, $to])
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                $openingData = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $companyId)
                    ->where('txn_date', '<', $from)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                $periodDebit  = (float)($period->debit ?? 0);
                $periodCredit = (float)($period->credit ?? 0);
    
                $openingDebit  = (float)($openingData->debit ?? 0);
                $openingCredit = (float)($openingData->credit ?? 0);
    
                $opening = $openingDebit - $openingCredit;
    
                $basicOpening = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('entry_type', -1)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                if ($basicOpening) {
                    $opening += (float)($basicOpening->debit ?? 0);
                    $opening -= (float)($basicOpening->credit ?? 0);
                }
    
                $closing = $opening + $periodDebit - $periodCredit;
                if($opening==0 && $periodDebit==0 && $periodCredit==0){
                    continue;
                }
                $rows[] = [
                    $acc->account_name,
                    'ACCOUNT',
                    formatIndianNumber(abs($opening), 2) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr'),
                    formatIndianNumber($periodDebit, 2),
                    formatIndianNumber($periodCredit, 2),
                    formatIndianNumber(abs($closing), 2) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr')
                ];
                $totalOpening +=abs($opening);
                $totalClosing +=abs($closing);
                $totalDebit  += $periodDebit;
                $totalCredit += $periodCredit;
            }
        }elseif ($type === 'group') {
            $group = AccountGroups::where('id', $id)->first();
            $title = $group->name ?? '';
            $groups = AccountGroups::where('heading', $group->id)
                ->where('heading_type', 'group')
                ->where('status', '1')
                ->where('delete', '0')
                ->get();
            foreach ($groups as $g) {
                $groupIds = [$g->id];
                $this->getChildGroups($g->id, $groupIds);
                $accountIds = Accounts::whereIn('under_group', $groupIds)
                    ->where('under_group_type', 'group')
                    ->whereIn('company_id', [$companyId, 0])
                    ->pluck('id');
                $period = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->whereBetween('txn_date', [$from, $to])
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
                $openingData = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('company_id', $companyId)
                    ->where('txn_date', '<', $from)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
    
                $periodDebit  = (float)($period->debit ?? 0);
                $periodCredit = (float)($period->credit ?? 0);
        
                $openingDebit  = (float)($openingData->debit ?? 0);
                $openingCredit = (float)($openingData->credit ?? 0);
        
                $opening = $openingDebit - $openingCredit;
        
                $basicOpening = DB::table('account_ledger')
                    ->whereIn('account_id', $accountIds)
                    ->where('entry_type', -1)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
        
                if ($basicOpening) {
                    $opening += (float)($basicOpening->debit ?? 0);
                    $opening -= (float)($basicOpening->credit ?? 0);
                }
    
                $closing = $opening + $periodDebit - $periodCredit;
                if($opening==0 && $periodDebit==0 && $periodCredit==0){
                        continue;
                    }
                $rows[] = [
                    $g->name,
                    'GROUP',
                    formatIndianNumber(abs($opening), 2) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr'),
                    formatIndianNumber($periodDebit, 2),
                    formatIndianNumber($periodCredit, 2),
                    formatIndianNumber(abs($closing), 2) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr')
                ];
                $totalOpening +=abs($opening);
                $totalClosing +=abs($closing);
                $totalDebit  += $periodDebit;
                $totalCredit += $periodCredit;
            }
    
            $accounts = Accounts::where('under_group', $group->id)
                ->where('under_group_type', 'group')
                ->whereIn('company_id', [$companyId, 0])
                ->get();
        
            if ($accounts->isNotEmpty()) {
                $rows[] = ['Accounts', '', '', '', '', ''];
            }
    
            foreach ($accounts as $acc) {
                $period = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $companyId)
                    ->whereBetween('txn_date', [$from, $to])
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
        
                $openingData = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $companyId)
                    ->where('txn_date', '<', $from)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
        
                $periodDebit  = (float)($period->debit ?? 0);
                $periodCredit = (float)($period->credit ?? 0);
        
                $openingDebit  = (float)($openingData->debit ?? 0);
                $openingCredit = (float)($openingData->credit ?? 0);
        
                $opening = $openingDebit - $openingCredit;
        
                $basicOpening = DB::table('account_ledger')
                    ->where('account_id', $acc->id)
                    ->where('entry_type', -1)
                    ->where('delete_status', '0')
                    ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                    ->first();
        
                if ($basicOpening) {
                    $opening += (float)($basicOpening->debit ?? 0);
                    $opening -= (float)($basicOpening->credit ?? 0);
                }
        
                $closing = $opening + $periodDebit - $periodCredit;
                if($opening==0 && $periodDebit==0 && $periodCredit==0){
                        continue;
                    }
                $rows[] = [
                    $acc->account_name,
                    'ACCOUNT',
                    formatIndianNumber(abs($opening), 2) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr'),
                    formatIndianNumber($periodDebit, 2),
                    formatIndianNumber($periodCredit, 2),
                    formatIndianNumber(abs($closing), 2) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr')
                ];
                $totalOpening +=abs($opening);
                $totalClosing +=abs($closing);
                $totalDebit  += $periodDebit;
                $totalCredit += $periodCredit;
            }
        }
        $fileName = 'account_summary_details_' . now()->format('Ymd_His') . '.csv';
    
        return response()->stream(function () use ($company, $title, $rows, $totalDebit, $totalCredit, $from, $to,$totalOpening,$totalClosing) {
    
            $file = fopen('php://output', 'w');
    
            fputcsv($file, [$company->company_name ?? '']);
            fputcsv($file, [$company->address ?? '']);
            fputcsv($file, ['CIN: ' . ($company->cin ?? '')]);
            fputcsv($file, ['FROM DATE : ' .$from." TO DATE : ".$to]);
            fputcsv($file, []);
    
            fputcsv($file, ["Account Summary : $title"]);
            fputcsv($file, []);
    
            fputcsv($file, [
                'Account / Group',
                'Type',
                'Opening',
                'Debit',
                'Credit',
                'Closing'
            ]);
    
            // Data
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
    
            // Total
            fputcsv($file, [
                'TOTAL',
                '',
                formatIndianNumber($totalOpening, 2),
                formatIndianNumber($totalDebit, 2),
                formatIndianNumber($totalCredit, 2),
                formatIndianNumber($totalClosing, 2),
            ]);
    
            fclose($file);
    
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ]);
    }
    public function exportMonthCSV(Request $request)
    {
        $accountId = $request->account_id;
        $from      = $request->from_date;
        $to        = $request->to_date;
        $companyId = Session::get('user_company_id');
    
        $company = DB::table('companies')->where('id', $companyId)->first();
    
        $account = Accounts::where('id', $accountId)
            ->whereIn('company_id', [$companyId, 0])
            ->firstOrFail();
    
        $openingData = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('company_id', $companyId)
            ->where('txn_date', '<', $from)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();
    
        $opening = (float)($openingData->debit ?? 0) - (float)($openingData->credit ?? 0);
    
        $basicOpening = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('entry_type', -1)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();
    
        if ($basicOpening) {
            $opening += (float)($basicOpening->debit ?? 0);
            $opening -= (float)($basicOpening->credit ?? 0);
        }
    
        $months = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->whereBetween('txn_date', [$from, $to])
            ->where('delete_status', '0')
            ->selectRaw("
                DATE_FORMAT(txn_date, '%Y-%m') as month,
                SUM(debit) as debit,
                SUM(credit) as credit
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalOpening = 0;
        $totalClosing = 0;
        $runningBalance = $opening;
    
        foreach ($months as $m) {
    
            $monthDebit  = (float)$m->debit;
            $monthCredit = (float)$m->credit;
    
            $openingBalance = $runningBalance;
            $closingBalance = $openingBalance + $monthDebit - $monthCredit;
    
            $runningBalance = $closingBalance;
            if($openingBalance==0 && $monthDebit==0 && $monthCredit==0){
                    continue;
                }
            $rows[] = [
                Carbon::createFromFormat('Y-m', $m->month)->format('M Y'),
                formatIndianNumber(abs($openingBalance), 2) . ' ' . ($openingBalance < 0 ? 'Cr' : 'Dr'),
                formatIndianNumber($monthDebit, 2),
                formatIndianNumber($monthCredit, 2),
                formatIndianNumber(abs($closingBalance), 2) . ' ' . ($closingBalance < 0 ? 'Cr' : 'Dr'),
            ];
            $totalOpening +=abs($openingBalance);
            $totalClosing +=abs($closingBalance);
            $totalDebit  += $monthDebit;
            $totalCredit += $monthCredit;
        }
    
        $fileName = 'account_month_summary_' . now()->format('Ymd_His') . '.csv';
    
        return response()->stream(function () use ($company, $account, $rows, $totalDebit, $totalCredit, $from, $to,$totalOpening,$totalClosing) {
    
            $file = fopen('php://output', 'w');
    
            fputcsv($file, [$company->company_name ?? '']);
            fputcsv($file, [$company->address ?? '']);
            fputcsv($file, ['CIN: ' . ($company->cin ?? '')]);
    
            fputcsv($file, []);
            fputcsv($file, ["Account Summary : " . $account->account_name]);
            fputcsv($file, []);
    
            fputcsv($file, ['Month', 'Opening', 'Debit', 'Credit', 'Closing']);
    
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
    
            fputcsv($file, [
                'TOTAL',
                formatIndianNumber($totalOpening, 2),
                formatIndianNumber($totalDebit, 2),
                formatIndianNumber($totalCredit, 2),
                formatIndianNumber($totalClosing, 2),
            ]);
    
            fclose($file);
    
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ]);
    }
    public function exportPDF(Request $request)
    {
        $from = $request->from_date ?? Carbon::now()->startOfMonth()->toDateString();
        $to   = $request->to_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $companyId = Session::get('user_company_id');
    
        $company = DB::table('companies')->where('id', $companyId)->first();
    
        $heads = DB::table('account_headings')
            ->where('status', '1')
            ->where('delete', '0')
            ->orderBy('name')
            ->get();
    
        $totalDebit = 0;
        $totalCredit = 0;
    
        foreach ($heads as $head) {
    
            $groupIds = AccountGroups::where('heading', $head->id)
                ->where(function ($q) {
                    $q->whereNull('heading_type')
                      ->orWhere('heading_type', 'head');
                })
                ->whereIn('company_id', [$companyId, 0])
                ->pluck('id')
                ->toArray();
    
            $allGroupIds = $groupIds;
            $this->getAllChildGroups($groupIds, $allGroupIds);
    
            $accountIds = Accounts::whereIn('under_group', $allGroupIds)
                ->pluck('id');
    
            $period = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->whereBetween('txn_date', [$from, $to])
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                ->first();
    
            $openingData = DB::table('account_ledger')
                ->whereIn('account_id', $accountIds)
                ->where('company_id', $companyId)
                ->where('txn_date', '<', $from)
                ->where('delete_status', '0')
                ->first();
    
            $periodDebit  = (float)($period->debit ?? 0);
            $periodCredit = (float)($period->credit ?? 0);
    
            $opening = (float)($openingData->debit ?? 0) - (float)($openingData->credit ?? 0);
    
            $closing = $opening + $periodDebit - $periodCredit;
            if($opening==0 && $periodDebit==0 && $periodCredit==0){
                $head->opening = 0;
                $head->debit   = 0;
                $head->credit  = 0;
                $head->closing = 0;
                    continue;
                }
            $head->opening = abs($opening) . ' ' . ($opening >= 0 ? 'Dr' : 'Cr');
            $head->debit   = $periodDebit;
            $head->credit  = $periodCredit;
            $head->closing = abs($closing) . ' ' . ($closing >= 0 ? 'Dr' : 'Cr');
    
            $totalDebit  += $periodDebit;
            $totalCredit += $periodCredit;
        }
        // echo "<pre>";
        // print_r($heads);die;
        $pdf = Pdf::loadView('AccountSummary.index_pdf', compact(
            'company',
            'heads',
            'totalDebit',
            'totalCredit',
            'from',
            'to'
        ))->setPaper('A4', 'portrait');
    
        return $pdf->download('account_summary.pdf');
    }
    public function exportDetailsPDF(Request $request)
    {
        $rows = json_decode($request->rows, true);

        $from = $request->from_date;
        $to   = $request->to_date;
        $title = $request->title;
        $companyId = Session::get('user_company_id');
        $company = DB::table('companies')->where('id', $companyId)->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'AccountSummary.details_pdf',
            compact('rows', 'company', 'from', 'to', 'title')
        )->setPaper('A4', 'portrait');

        return $pdf->stream('account_summary.pdf');
    }
    public function exportMonthPDF(Request $request)
    {
        $accountId = $request->account_id;
        $from      = $request->from_date;
        $to        = $request->to_date;
        $companyId = Session::get('user_company_id');
    
        $company = DB::table('companies')->where('id', $companyId)->first();
    
        $account = Accounts::where('id', $accountId)
            ->whereIn('company_id', [$companyId, 0])
            ->firstOrFail();
    
        $openingData = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('company_id', $companyId)
            ->where('txn_date', '<', $from)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();
    
        $opening = (float)($openingData->debit ?? 0) - (float)($openingData->credit ?? 0);
    
        $basicOpening = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->where('entry_type', -1)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
            ->first();
    
        if ($basicOpening) {
            $opening += (float)($basicOpening->debit ?? 0);
            $opening -= (float)($basicOpening->credit ?? 0);
        }
    
        $months = DB::table('account_ledger')
            ->where('account_id', $accountId)
            ->whereBetween('txn_date', [$from, $to])
            ->where('delete_status', '0')
            ->selectRaw("
                DATE_FORMAT(txn_date, '%Y-%m') as month,
                SUM(debit) as debit,
                SUM(credit) as credit
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalOpening = 0;
        $totalClosing = 0;
        $runningBalance = $opening;
        foreach ($months as $m) {
            $monthDebit  = (float)$m->debit;
            $monthCredit = (float)$m->credit;
            $openingBalance = $runningBalance;
            $closingBalance = $openingBalance + $monthDebit - $monthCredit;
            $runningBalance = $closingBalance;
            if($openingBalance==0 && $monthDebit==0 && $monthCredit==0){
                    continue;
                }
            $rows[] = [
                Carbon::createFromFormat('Y-m', $m->month)->format('M Y'),
                formatIndianNumber(abs($openingBalance), 2) . ' ' . ($openingBalance < 0 ? 'Cr' : 'Dr'),
                formatIndianNumber($monthDebit, 2),
                formatIndianNumber($monthCredit, 2),
                formatIndianNumber(abs($closingBalance), 2) . ' ' . ($closingBalance < 0 ? 'Cr' : 'Dr'),
            ];
            $totalOpening += $openingBalance;
            $totalClosing += $closingBalance;
            $totalDebit  += $monthDebit;
            $totalCredit += $monthCredit;
        }
    
        // PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('AccountSummary.month_pdf', compact(
            'company',
            'account',
            'rows',
            'totalDebit',
            'totalCredit',
            'from',
            'to',
            'totalOpening',
            'totalClosing'
        ))->setPaper('A4', 'portrait');
    
        return $pdf->download('account_month_summary.pdf');
    }
    public function exportDetailsExcel(Request $request)
    {
        //dd($request->all());
        $rows = json_decode($request->rows, true);

        $from = $request->from_date 
            ? Carbon::parse($request->from_date)->format('d-m-Y') 
            : '';

        $to = $request->to_date 
            ? Carbon::parse($request->to_date)->format('d-m-Y') 
            : '';
        $title = $request->title;

        $companyId = Session::get('user_company_id');
        $company = DB::table('companies')->where('id', $companyId)->first();

        $headers = [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=account_summary.xls"
        ];

        return response()->view(
            'AccountSummary.details_excel',
            compact('rows', 'company', 'from', 'to', 'title') // ✅ FIXED
        )
        ->header('Content-Type', $headers["Content-Type"])
        ->header('Content-Disposition', $headers["Content-Disposition"]);
    }
}
