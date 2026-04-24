<?php

namespace App\Http\Controllers\ReceivablePayable;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\CommonHelper;
use Session; 
use DB;

class PayableController extends Controller
{
    public function index(Request $request)
    {

        $company_id = Session::get('user_company_id');
        $today = $request->date ?? Carbon::today()->toDateString();

         $top_groups_list = [3];
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
        ->where('heading_type','group')
        ->where('delete', '0')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

       if ($request->has('group_id') && $request->group_id != '') {
    // Use selected group instead of default 11
            $top_groups = [$request->group_id];
             $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }
        $all_groups = array_unique($all_groups);
        } else {
            // Default
        $all_groups = $all_groups_list;
        } 

      // Step 2: Get all child group IDs recursively
     

      // Remove duplicates just in case
    
        // Step 1: Get all debtors (accounts with credit_days > 0)
        
        
        
        $accounts = DB::table('accounts')
            ->where('company_id', $company_id)
             ->whereIn('under_group', $all_groups)
             ->where('delete','0')
            ->select('id', 'account_name', 'due_day','credit_days','mobile')
            ->get();

        $data = [];

        
        foreach ($accounts as $acc) {

            // Step 2: Total debit & credit from accountledger
            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

                $receivable = ($ledger->total_credit ?? 0) - ($ledger->total_debit ?? 0);

                        if ($receivable == 0) {
                // Skip this account
                continue;
                            }
                            // Credit days
                $creditDays = $acc->due_day;
                
                // Date range: FROM = today - creditDays days
                $fromDate = \Carbon\Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');
                
                // Upto yesterday (exclude today's sales)
                $toDate = \Carbon\Carbon::parse($today)->format('Y-m-d');
                
                // Fetch sales in the date range
                $sales = DB::table('purchases')
                    ->where('company_id', $company_id)
                    ->where('party', $acc->id)
                    ->where('delete', '0')
                    ->whereBetween('date', [$fromDate, $toDate])
                    ->select('id', 'total', 'date')
                    ->get();
                
                
                
                // Total of sales amount
                $totalSalesInCreditPeriod = $sales->sum('total');

            // Step 3: Overdue calculation → loop for each sale
            

            $overdue = $receivable-$totalSalesInCreditPeriod;

            


            $data[] = (object)[
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => $receivable,
                'overdue'    => $overdue,
                'credit_days'    => $acc->credit_days,
                'mobile'    => $acc->mobile,
            ];
        }

        $firstDate = now()->startOfMonth()->format('Y-m-d');
        return view('payable.index', compact('data', 'allGroupsList','today','firstDate'));
    }

    public function overdueDetails($account_id)
{
    
    $company_id = Session::get('user_company_id');
    $today = Carbon::today()->toDateString();

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
    $bills = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'asc')
        ->select('id','voucher_no','date','total')
        ->get();

    // Receivable
    $ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $receivable = ($ledger->total_credit ?? 0) - ($ledger->total_debit ?? 0);

    // Sales inside credit period (same logic as index)
    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');
    $toDate = Carbon::parse($today)->format('Y-m-d');

    $salesInPeriod = DB::table('purchases')
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
            'bill_no' => $b->voucher_no,
            'total'   => $b->total,
            'overdue' => $alloc,
            'remaining_part' => $b->total - $alloc
        ];

        $remaining -= $alloc;
    }

    return view('payable.overdue_details', compact('acc','overdueAmount','allocated','today','openingDate'));
}



public function overdueReportPdf($account_id)
{
   $company_id = Session::get('user_company_id');
    $today = Carbon::today()->toDateString();

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
    $bills = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
         ->where('status','1')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'asc')
        ->select('id','voucher_no','date','total')
        ->get();

    // Receivable
    $ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $receivable = ($ledger->total_credit ?? 0) - ($ledger->total_debit ?? 0);

    // Sales inside credit period (same logic as index)
    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');
    $toDate = Carbon::parse($today)->format('Y-m-d');

    $salesInPeriod = DB::table('purchases')
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
            'bill_no' => $b->voucher_no,
            'total'   => $b->total,
            'overdue' => $alloc,
            'remaining_part' => $b->total - $alloc
        ];

        $remaining -= $alloc;
    }
    $pdf = PDF::loadView('payable.overdue_details_pdf', [
        'acc' => $acc,
        'overdueAmount' => $overdueAmount,
        'allocated' => $allocated,
    ])->setPaper('A4', 'portrait');

    return $pdf->download('Overdue_Report_'.$acc->account_name.'.pdf');
}
}
