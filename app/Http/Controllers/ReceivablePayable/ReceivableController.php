<?php

namespace App\Http\Controllers\ReceivablePayable;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\CommonHelper;
use App\Models\Companies;
use App\Models\AgingBucket;
use Session; 
use DB;
use Gate;
class ReceivableController extends Controller
{
    public function index(Request $request)
{
    Gate::authorize('action-module',155);
    $company_id = Session::get('user_company_id');
    $today = $request->date ?? Carbon::today()->toDateString();
    $showType = $request->show_type ?? 'all';

    /* ====================== COMMON VARIABLES ====================== */
    $firstDate = now()->startOfMonth()->format('Y-m-d');

    /* ====================== DROP-DOWN LISTS ====================== */
    // PARTIES LIST
   

    // MAIN TOP GROUPS
    $top_groups_list = [11];
    $all_groups_list = [];

    foreach ($top_groups_list as $gid) {
        $all_groups_list[] = $gid;
        $all_groups_list = array_merge($all_groups_list,
            CommonHelper::getAllChildGroupIds($gid, $company_id));
        
    }
    $all_groups_list = array_unique($all_groups_list);

    // GROUP LIST (for dropdown)
    $allGroupsList = DB::table('account_groups')
        ->where('company_id', $company_id)
        ->whereIn('id', $all_groups_list)
        ->where('delete', '0')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

         $allParties = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('delete', '0')
        ->whereIn('under_group',$all_groups_list)
        ->select('id', 'account_name', 'due_day','credit_days','mobile')
        ->orderBy('account_name')
        ->get();
    /* ====================================================================
       1️⃣ SHOW TYPE = ALL PARTIES
    ==================================================================== */
    if ($showType == 'all') {

        $partyData = [];

        foreach ($allParties as $acc) {


             $allResponses = DB::table('overdue_response')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $company_id)
                    ->orderBy('id', 'DESC')
                    ->get();

                // Latest record
                $latest = $allResponses->first();

                    $response = $latest->response ?? null;
                    $response_date = $latest->response_date ?? null;
            // Ledger debit/credit
            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $today)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open_ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', "-1")
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $receivable = ($ledger->dr ?? 0) - ($ledger->cr ?? 0)
                        + ($open_ledger->dr ?? 0) - ($open_ledger->cr ?? 0);

            if ($receivable == 0) continue;

            /* overdue */
            $creditDays = $acc->due_day;
            $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

            $sales = DB::table('sales')
                ->where('company_id', $company_id)
                ->where('party', $acc->id)
                ->where('delete', '0')
                ->where('status', '1')
                ->whereBetween('date', [$fromDate, $today])
                ->select('total')
                ->get();

            $totalSalesInCreditPeriod = $sales->sum('total');
            $overdue = max(0, $receivable - $totalSalesInCreditPeriod);

            $partyData[] = (object) [
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => round($receivable,2),
                'overdue' => round($overdue,2),
                 'response' => $response,
                'response_date' => $response_date,
                'credit_days' => $acc->credit_days,
                'due_day' => $acc->due_day,
                'mobile' => $acc->mobile
            ];
        }

        return view('receiable.index', [
            'today' => $today,
            'firstDate' => $firstDate,
            'showType' => 'all',
            'data' => $partyData,
            'groupWiseData' => [],
            'allGroupsList' => $allGroupsList,
            'allParties' => $allParties
        ]);
    }

    /* ====================================================================
       2️⃣ SHOW TYPE = PARTY-WISE
    ==================================================================== */
    if ($showType == 'party' && $request->party_id) {

        $allResponses = DB::table('overdue_response')
                    ->where('account_id', $request->party_id)
                    ->where('company_id', $company_id)
                    ->orderBy('id', 'DESC')
                    ->get();

                // Latest record
                $latest = $allResponses->first();

                    $response = $latest->response ?? null;
                    $response_date = $latest->response_date ?? null;

        $acc = DB::table('accounts')
            ->where('company_id', $company_id)
            ->where('id', $request->party_id)
            ->first();

        if (!$acc) {
            return redirect()->back()->with('error', 'Party not found');
        }

        // Ledger
        $ledger = DB::table('account_ledger')
            ->where('company_id', $company_id)
            ->where('account_id', $acc->id)
            ->where('txn_date', '<=', $today)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
            ->first();

        $open_ledger = DB::table('account_ledger')
            ->where('company_id', $company_id)
            ->where('account_id', $acc->id)
            ->where('entry_type', "-1")
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
            ->first();

        $receivable = ($ledger->dr ?? 0) - ($ledger->cr ?? 0)
                    + ($open_ledger->dr ?? 0) - ($open_ledger->cr ?? 0);

        /* overdue */
        $fromDate = Carbon::parse($today)->subDays($acc->due_day)->format('Y-m-d');
        $sales = DB::table('sales')
            ->where('company_id', $company_id)
            ->where('party', $acc->id)
            ->where('delete', '0')
            ->where('status', '1')
            ->whereBetween('date', [$fromDate, $today])
            ->select('total')
            ->get();

        $overdue = max(0,$receivable - $sales->sum('total'));
       


        return view('receiable.index', [
            'today' => $today,
            'firstDate' => $firstDate,
            'showType' => 'party',
            'selectedParty' => $acc,
            'data' => [
                (object)[
                    'id' => $acc->id,
                    'party_name' => $acc->account_name,
                    'receivable' => round($receivable,2),
                    'overdue' => round($overdue,2),
                     'response' => $response,
                     'response_date' => $response_date,
                     'credit_days' => $acc->credit_days,
                     'due_day' => $acc->due_day,
                    'mobile' => $acc->mobile
                ]
            ],
            'groupWiseData' => [],
            'allGroupsList' => $allGroupsList,
            'allParties' => $allParties
        ]);
    }

    /* ====================================================================
       3️⃣ SHOW TYPE = GROUP-WISE (single group selected)
    ==================================================================== */
    if ($showType == 'group' && $request->group_id) {

        $groupId = $request->group_id;

        $childGroups = CommonHelper::getAllChildGroupIds($groupId, $company_id);
        $childGroups[] = $groupId;

        $parties = DB::table('accounts')
            ->where('company_id', $company_id)
            ->whereIn('under_group', $childGroups)
            ->where('delete', '0')
            ->select('id', 'account_name', 'due_day','credit_days','mobile')
            ->get();

        $partyData = [];

        foreach ($parties as $acc) {

             $allResponses = DB::table('overdue_response')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $company_id)
                    ->orderBy('id', 'DESC')
                    ->get();

                // Latest record
                $latest = $allResponses->first();

                    $response = $latest->response ?? null;
                    $response_date = $latest->response_date ?? null;

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $today)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open_ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', "-1")
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $receivable = ($ledger->dr ?? 0) - ($ledger->cr ?? 0)
                        + ($open_ledger->dr ?? 0) - ($open_ledger->cr ?? 0);

            if ($receivable == 0) continue;

            $fromDate = Carbon::parse($today)->subDays($acc->due_day)->format('Y-m-d');
            $sales = DB::table('sales')
                ->where('company_id', $company_id)
                ->where('party', $acc->id)
                ->where('delete', '0')
                ->where('status', '1')
                ->whereBetween('date', [$fromDate, $today])
                ->select('total')
                ->get();

            $salesTotal = $sales->sum('total');

if ($receivable < 0) {
    $overdue = $receivable; // show same negative value
} else {
    $overdue = max(0, $receivable - $salesTotal);
}

            $partyData[] = (object)[
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => round($receivable,2),
                'overdue' => round($overdue,2),
                 'response' => $response,
                'response_date' => $response_date,
                'credit_days' => $acc->credit_days,
                'due_day' => $acc->due_day,
                'mobile' => $acc->mobile
            ];
        }

        return view('receiable.index', [
            'today' => $today,
            'firstDate' => $firstDate,
            'showType' => 'group',
            'data' => $partyData,
            'groupWiseData' => [],
            'allGroupsList' => $allGroupsList,
            'allParties' => $allParties
        ]);
    }

    /* ====================================================================
       4️⃣ SHOW TYPE = ALL GROUPS (your original logic)
    ==================================================================== */
 if ($showType == 'allgroup') {

    // -----------------------------------------------------------
    // RECURSIVE FUNCTION MOVED HERE (as closure)
    // -----------------------------------------------------------
 $buildGroupTree = function($groupIds, $company_id, $today) use (&$buildGroupTree) {

   $groups = DB::table('account_groups')
    ->whereIn('id', $groupIds)
    ->where('delete', '0')
    ->select('id', 'name', 'heading')   // ← ADD THIS
    ->orderBy('name')
    ->get();


    $tree = [];

    foreach ($groups as $grp) {

        // --------------------------
        // FETCH DIRECT CHILD GROUPS
        // --------------------------
        $childGroups = DB::table('account_groups')
            ->where('heading', $grp->id)
            ->where('company_id',$company_id)
            ->where('delete', '0')
            ->pluck('id')
            ->toArray();

        // --------------------------
        // FETCH DIRECT ACCOUNTS
        // --------------------------
        $accounts = DB::table('accounts')
            ->where('company_id', $company_id)
            ->where('delete', '0')
            ->where('under_group', $grp->id)
            ->select('id', 'account_name', 'due_day','credit_days','mobile')
            ->get();

        $accountData = [];
        $groupR = 0;  // direct group's receivable total
        $groupO = 0;  // direct group's overdue total

        foreach ($accounts as $acc) {

             $allResponses = DB::table('overdue_response')
                    ->where('account_id', $acc->id)
                    ->where('company_id', $company_id)
                    ->orderBy('id', 'DESC')
                    ->get();

                // Latest record
                $latest = $allResponses->first();

                $response = $latest->response ?? null;
                $response_date = $latest->response_date ?? null;

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $today)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open_ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', -1)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $receivable = ($ledger->dr ?? 0) - ($ledger->cr ?? 0)
                        + ($open_ledger->dr ?? 0) - ($open_ledger->cr ?? 0);

            if ($receivable == 0) continue;

            $fromDate = Carbon::parse($today)->subDays($acc->due_day)->format('Y-m-d');

            $sales = DB::table('sales')
                ->where('company_id', $company_id)
                ->where('party', $acc->id)
                ->where('status', '1')
                ->where('delete', '0')
                ->whereBetween('date', [$fromDate, $today])
                ->select('total')
                ->get();

            $salesTotal = $sales->sum('total');

if ($receivable < 0) {
    $overdue = $receivable; // show same negative value
} else {
    $overdue = max(0, $receivable - $salesTotal);
}

            // add to DIRECT group totals
            $groupR += $receivable;
            $groupO += $overdue;

            $accountData[] = [
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => round($receivable,2),
                'overdue' => round($overdue,2),
                 'response' => $response,
                'response_date' => $response_date,
                'credit_days' => $acc->credit_days,
                'due_day' => $acc->due_day,
                'mobile' => $acc->mobile
            ];
        }

        // -----------------------------------------------------
        // RECURSION (GET CHILD GROUPS)
        // -----------------------------------------------------
        $children = [];
        $childReceivable = 0;
        $childOverdue = 0;

        if (!empty($childGroups)) {
            $children = $buildGroupTree($childGroups, $company_id, $today);

            // ADD CHILD GROUP TOTALS TO PARENT TOTALS
            foreach ($children as $cg) {
                $childReceivable += $cg['total_receivable'];
                $childOverdue += $cg['total_overdue'];
            }
        }

        // -----------------------------------------------------
        // FINAL TOTAL = direct accounts + all subgroup totals
        // -----------------------------------------------------
        $finalReceivable = $groupR + $childReceivable;
        $finalOverdue = $groupO + $childOverdue;

        $tree[] = [
            'group_id' => $grp->id,
            'group_name' => $grp->name,
            'total_receivable' => round($finalReceivable,2),
            'total_overdue' => round($finalOverdue,2),
            'accounts' => $accountData,
            'children' => $children,
            'parent_id' => $grp->heading ?? null,
        ];
    }

    return $tree;
};


    // -----------------------------------------------------------
    // CALL THE FUNCTION
    // -----------------------------------------------------------
    $groupWiseData = $buildGroupTree([11], $company_id, $today);


    return view('receiable.index', [
        'today' => $today,
        'firstDate' => $firstDate,
        'showType' => 'allgroup',
        'data' => [],
        'groupWiseData' => $groupWiseData,
        'allGroupsList' => $allGroupsList,
        'allParties' => $allParties
    ]);
}



}


public function overdueDetails(Request $request, $account_id)
{
    
    $company_id = Session::get('user_company_id');
    $today = $request->query('date') ?? Carbon::today()->toDateString();
    $openingDate = Companies::where('id', $company_id)
                ->value('books_start_from');

    $openingDate = Carbon::parse($openingDate)->format('d-m-Y');
    $oDate = Carbon::parse($openingDate)->format('Y-m-d');

    // Fetch account
    $acc = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('id', $account_id)
        ->first();

    if (!$acc) {
        abort(404, "Account not found.");
    }

    $creditDays = $acc->due_day ?? 0;

    // Date limit for overdue bills
    $dueDateLimit = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

    // All bills older than credit days
    $bills = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
        ->where('status','1')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'desc')
        ->select('id','voucher_no_prefix','date','total')
        ->get();

            
           
    // Receivable
     $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date','<=',$today)
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

    $open_ledger = DB::table('account_ledger')
    ->where('company_id', $company_id)
    ->where('account_id', $acc->id)
    ->where('entry_type',"-1")
    ->where('delete_status','0')
    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
    ->first();

     $opening_balance = ($open_ledger->total_debit ?? 0) - ($open_ledger->total_credit ?? 0);
     
    $receivable = ($ledger->total_debit ?? 0) - ($ledger->total_credit ?? 0) + ($open_ledger->total_debit) - ($open_ledger->total_credit);

    // Sales inside credit period (same logic as index)
    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');
    $toDate = Carbon::parse($today)->format('Y-m-d');

    $salesInPeriod = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete','0')
         ->where('status','1')
        ->whereBetween('date', [$fromDate, $toDate])
        ->sum('total');

    // Overdue
    $overdueAmount = $receivable - $salesInPeriod;

    // Allocate overdue amount bill-wise
    $remaining = $overdueAmount;
    $allocated = [];

    foreach ($bills as $b) {
        if ($remaining <= 0) break;

        $alloc = min($b->total, $remaining);

        $allocated[] = [
            'id'      => $b->id,
            'date'    => $b->date,
            'bill_no' => $b->voucher_no_prefix,
            'total'   => $b->total,
            'overdue' => $alloc,
            'remaining_part' => $b->total - $alloc
        ];

        $remaining -= $alloc;
    }
    
    
        if ($remaining > 0) {
        $allocated[] = [
            'id'      => null,
            'date'    => $openingDate,
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance,
            'overdue' => $remaining,
            'remaining_part' => $opening_balance - $remaining
        ];
    }

    return view('receiable.overdue_details', compact('acc','overdueAmount','allocated','today','oDate','account_id'));
}

public function overdueReportPdf(Request $request, $account_id)
{
   $company_id = Session::get('user_company_id');
   $today = $request->query('date') ?? Carbon::today()->toDateString();

    // Fetch account
    $acc = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('id', $account_id)
        ->first();

    if (!$acc) {
        abort(404, "Account not found.");
    }

    $creditDays = $acc->due_day ?? 0;

    // Date limit for overdue bills
    $dueDateLimit = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

    // All bills older than credit days
    $bills = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
         ->where('status','1')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'desc')
        ->select('id','voucher_no_prefix','date','total')
        ->get();

    // Receivable
     $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date','<=',$today)
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

                $open_ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type',"-1")
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();
            
            $opening_balance = ($open_ledger->total_debit ?? 0) - ($open_ledger->total_credit ?? 0);
            $receivable = ($ledger->total_debit ?? 0) - ($ledger->total_credit ?? 0) + ($open_ledger->total_debit) - ($open_ledger->total_credit);
    // Sales inside credit period (same logic as index)
    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');
    $toDate = Carbon::parse($today)->format('Y-m-d');

    $salesInPeriod = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete','0')
         ->where('status','1')
        ->whereBetween('date', [$fromDate, $toDate])
        ->sum('total');

    // Overdue
    $overdueAmount = $receivable - $salesInPeriod;

    // Allocate overdue amount bill-wise
    $remaining = $overdueAmount;
    $allocated = [];

    foreach ($bills as $b) {
        if ($remaining <= 0) break;

        $alloc = min($b->total, $remaining);

        $allocated[] = [
            'id'      => $b->id,
            'date'    => $b->date,
            'bill_no' => $b->voucher_no_prefix,
            'total'   => $b->total,
            'overdue' => $alloc,
            'remaining_part' => $b->total - $alloc
        ];

        $remaining -= $alloc;
    }
    
    if ($remaining > 0) {
        $allocated[] = [
            'id'      => null,
            'date'    => 'Opening',
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance,
            'overdue' => $remaining,
            'remaining_part' => $opening_balance - $remaining
        ];
    }

    $pdf = PDF::loadView('receiable.overdue_details_pdf', [
        'acc' => $acc,
        'overdueAmount' => $overdueAmount,
        'allocated' => $allocated,
    ])->setPaper('A4', 'portrait');

    return $pdf->download('Overdue_Report_'.$acc->account_name.'.pdf');
}

public function runningBalace(Request $request){
   $company_id = Session::get('user_company_id');
   $from_date = $request->from_date;
   $to_date = $request->to_date;

    $top_groups_list = [11];
        $all_groups_list = [];
      foreach ($top_groups_list as $group_id) {
         $all_groups_list[] = $group_id; // include the top group itself
         $all_groups_list = array_merge($all_groups_list, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $all_groups_list = array_unique($all_groups_list);
        $allGroupsList = DB::table('account_groups')
    ->where('company_id', $company_id)
    ->whereIn('id', $all_groups_list)
    ->where('delete', '0')
    ->select('id', 'name')
    ->get();

       if ($request->has('group_id') && $request->group_id != '') {
    // Use selected group instead of default 11
    $top_groups = [$request->group_id];
} else {
    // Default
    $top_groups = [11];
} 

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $all_groups = array_unique($all_groups);


}


private function buildGroupTree($groupIds, $company_id, $today)
{
    $groups = DB::table('account_groups')
        ->where('company_id', $company_id)
        ->whereIn('id', $groupIds)
        ->where('delete', '0')
        ->select('id','name')
        ->orderBy('name')
        ->get();

    $tree = [];

    foreach ($groups as $grp) {

        // Get direct child groups
        $childGroups = CommonHelper::getAllChildGroupIds($grp->id, $company_id);

        // Get accounts directly under this group
        $accounts = DB::table('accounts')
            ->where('company_id', $company_id)
            ->where('delete','0')
            ->where('under_group', $grp->id)
            ->select('id','account_name','due_day')
            ->get();

        $accountData = [];
        $groupR = 0;
        $groupO = 0;

        foreach ($accounts as $acc) {

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('txn_date', '<=', $today)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $open_ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('entry_type', -1)
                ->where('delete_status', '0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $receivable = ($ledger->dr ?? 0) - ($ledger->cr ?? 0)
                        + ($open_ledger->dr ?? 0) - ($open_ledger->cr ?? 0);

            if ($receivable == 0) continue;

            $fromDate = Carbon::parse($today)->subDays($acc->due_day)->format('Y-m-d');

            $sales = DB::table('sales')
                ->where('company_id', $company_id)
                ->where('party', $acc->id)
                ->where('status', 1)
                ->where('delete', 0)
                ->whereBetween('date', [$fromDate, $today])
                ->select('total')
                ->get();

            $overdue = $receivable - $sales->sum('total');

            $groupR += $receivable;
            $groupO += $overdue;

            $accountData[] = [
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => $receivable,
                'overdue' => $overdue
            ];
        }

        // 🔥 FIXED RECURSION
        $children = [];
        if (!empty($childGroups)) {
            $children = $this->buildGroupTree($childGroups, $company_id, $today);
        }

        $tree[] = [
            'group_id' => $grp->id,
            'group_name' => $grp->name,
            'total_receivable' => $groupR,
            'total_overdue' => $groupO,
            'accounts' => $accountData,
            'children' => $children
        ];
    }

    return $tree;
}


public function storeResponse(Request $request)
{
    $request->validate([
        'account_id'     => 'required|integer',
        'response_date'  => 'required|string|max:11',
        'response'       => 'nullable|string|max:600',
    ]);

    DB::table('overdue_response')->insert([
        'account_id'   => $request->account_id,
        'response_date'=> $request->response_date,
        'response'     => $request->response,
        'company_id'   => Session::get('user_company_id'),
        'status'       => 1,
        'created_by'   => Session::get('user_id'),
        'created_at'   => now(),
    ]);

    // 🚀 Fetch only the 6 newest IDs
    $ids = DB::table('overdue_response')
        ->where('account_id', $request->account_id)
        ->orderBy('id', 'DESC')
        ->limit(6)
        ->pluck('id');

    // If more than 5 → delete only the 6th (oldest among latest)
    if ($ids->count() > 5) {
        DB::table('overdue_response')
            ->whereIn('id', [$ids->last()])
            ->delete();
    }

    return response()->json(['success' => true]);
}


public function lastResponses($id)
{
    $data = DB::table('overdue_response')
    ->where('account_id', $id)
    ->orderBy('id', 'DESC')
    ->limit(5)
    ->get()
    ->map(function($row){
        if ($row->response_date) {
            $row->response_date = date('d-m-Y', strtotime($row->response_date));
        }
        return $row;
    });

    return response()->json($data);
}



public function createAging()
{
    $company_id = Session::get('user_company_id');

    $buckets = AgingBucket::where('company_id', $company_id)
                          ->orderBy('order_num', 'asc')
                          ->get();

    return view('receiable.aging_create', compact('buckets'));
}

public function storeAging(Request $request)
{
    $company_id = Session::get('user_company_id');

    if (!$request->has('buckets')) {
        return back()->with('error', 'Please add at least one bucket.');
    }

    $order = 1;

    foreach ($request->buckets as $bucket) {
        AgingBucket::create([
            'company_id' => $company_id,
            'from_days'  => $bucket['from_days'],
            'to_days'    => $bucket['to_days'],
            'order_num'  => $order++,
        ]);
    }

    return redirect()->route('AgingReport')->with('success', 'Buckets created successfully');
}

public function updateAging(Request $request)
{
    $company_id = Session::get('user_company_id');

    if (!$request->has('buckets')) {
        return back()->with('error', 'Please add at least one bucket.');
    }

    // Delete old rows
    AgingBucket::where('company_id', $company_id)->delete();

    // Insert new rows
    $order = 1;

    foreach ($request->buckets as $bucket) {
        AgingBucket::create([
            'company_id' => $company_id,
            'from_days'  => $bucket['from_days'],
            'to_days'    => $bucket['to_days'],
            'order_num'  => $order++,
        ]);
    }

    return redirect()->route('AgingReport')->with('success', 'Buckets updated successfully');
}



  public function AgingReport(Request $request)
{
    Gate::authorize('action-module',157);
    $company_id = Session::get('user_company_id');
    $today = $request->date ?? Carbon::today()->toDateString();
 
    $buckets = AgingBucket::where('company_id', $company_id)
        ->orderBy('from_days')
        ->get();

     if ($buckets->count() == 0) {
        return redirect()->route('bucket.set')
            ->with('error', 'Please set aging buckets first.');
    }

    $company = DB::table('companies')->find($company_id);
    $book_start = $company->books_start_from;

    // ------ GET ALL RECEIVABLE ACCOUNT ------- //
    
    
     $top_groups_list = [11];
    $all_groups_list = [];

    foreach ($top_groups_list as $gid) {
        $all_groups_list[] = $gid;
        $all_groups_list = array_merge($all_groups_list,
            CommonHelper::getAllChildGroupIds($gid, $company_id));
        
    }
    $all_groups_list = array_unique($all_groups_list);

    $accounts = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('delete', '0')
        ->whereIn('under_group', $all_groups_list)
        ->get();

    $data = [];

    foreach ($accounts as $acc) {

        // Closing balance
        $ledger = DB::table('account_ledger')
            ->where('company_id', $company_id)
            ->where('account_id', $acc->id)
            ->where('txn_date', '<=', $today)
            ->where('delete_status', '0')
            ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
            ->first();

        // Opening balance
        $open = DB::table('account_ledger')
            ->where('company_id', $company_id)
            ->where('account_id', $acc->id)
            ->where('entry_type', '-1')
            ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
            ->first();

        $total = ($ledger->dr - $ledger->cr) + ($open->dr - $open->cr);

        if ($total <= 0) continue;

        /* -----------------------------------
            BUILD FIFO AGING ROWS
        ------------------------------------*/

        $agingRows = [];

        // opening row
        if (($open->dr - $open->cr) > 0) {
            $agingRows[] = [
                'age' => Carbon::parse($book_start)->diffInDays($today),
                'amount' => ($open->dr - $open->cr)
            ];
        }

        // invoice rows
        $sales = DB::table('sales')
            ->where('company_id', $company_id)
            ->where('party', $acc->id)
            ->where('date', '<=', $today)
            ->where('status', '1')
            ->where('delete', '0')
            ->get();

        foreach ($sales as $inv) {
            $agingRows[] = [
                'age' => Carbon::parse($inv->date)->diffInDays($today),
                'amount' => $inv->total
            ];
        }

        // SORT descending age = oldest first
        usort($agingRows, function ($a, $b) {
            return $b['age'] <=> $a['age'];
        });

        /* -----------------------------------
            FIFO PAYMENT ADJUSTMENT
        ------------------------------------*/

        $originalTotal = array_sum(array_column($agingRows, 'amount'));
        $payment = $originalTotal - $total; // amount to deduct

        foreach ($agingRows as $key => $row) {
            if ($payment <= 0) break;

            $deduct = min($row['amount'], $payment);
            $agingRows[$key]['amount'] -= $deduct;
            $payment -= $deduct;
        }

        /* -----------------------------------
            ALLOCATE TO BUCKETS
        ------------------------------------*/
        $bucketAmt = [];
        foreach ($buckets as $b) {
            $bucketAmt[$b->id] = 0;
        }
        $moreThan = 0;

        foreach ($agingRows as $row) {
            if ($row['amount'] <= 0) continue;

            $age = $row['age'];
            $amt = $row['amount'];
            $allocated = false;

            foreach ($buckets as $b) {
                if ($age >= $b->from_days && $age <= $b->to_days) {
                    $bucketAmt[$b->id] += $amt;
                    $allocated = true;
                    break;
                }
            }

            if (!$allocated) {
                $moreThan += $amt;
            }
        }

        // ----------------------------------

        $data[] = [
            'party' => $acc->account_name,
            'total' => $total,
            'buckets' => $bucketAmt,
            'moreThan' => $moreThan
        ];
    }

    return view('receiable.aging_report', compact('today', 'buckets', 'data'));
}



}
