<?php

namespace App\Http\Controllers\ReceivablePayable;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\CommonHelper;
use Session; 
use DB;
use Gate;
class PayableController extends Controller
{
public function index(Request $request)
{
    Gate::authorize('action-module',156);

    $company_id = Session::get('user_company_id');
    $today = $request->date ?? Carbon::today()->toDateString();
    $showType = $request->show_type ?? 'all';
    $firstDate = now()->startOfMonth()->format('Y-m-d');

    $top_groups_list = [3]; // Sundry Creditors group
    $all_groups_list = [];

    foreach ($top_groups_list as $gid) {
        $all_groups_list[] = $gid;
        $all_groups_list = array_merge(
            $all_groups_list,
            CommonHelper::getAllChildGroupIds($gid, $company_id)
        );
    }

    $all_groups_list = array_unique($all_groups_list);

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
        ->whereIn('under_group', $all_groups_list)
        ->select('id', 'account_name', 'due_day','credit_days','mobile')
        ->orderBy('account_name')
        ->get();

    if ($showType == 'all') {

        $data = [];

        foreach ($allParties as $acc) {
            $latest = DB::table('overdue_response')
                ->where('account_id', $acc->id)
                ->where('company_id', $company_id)
                ->orderBy('id','DESC')
                ->first();

            $response = $latest->response ?? null;
            $response_date = $latest->response_date ?? null;

            $ledger = DB::table('account_ledger')
                ->where('company_id', $company_id)
                ->where('account_id', $acc->id)
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $payable = ($ledger->cr ?? 0) - ($ledger->dr ?? 0);
            if ($payable == 0) continue;

            $creditDays = $acc->due_day ?? 0;
            $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

            $purchaseSum = DB::table('purchases')
                ->where('company_id', $company_id)
                ->where('party', $acc->id)
                ->where('delete','0')
                ->whereBetween('date', [$fromDate, $today])
                ->sum('total');

            $overdue = max(0, $payable - $purchaseSum);

            $data[] = (object)[
                'id' => $acc->id,
                'party_name' => $acc->account_name,
                'receivable' => round($payable,2),
                'overdue' => round($overdue,2),
                'response' => $response,
                'response_date' => $response_date,
                'credit_days' => $acc->credit_days,
                'due_day' => $acc->due_day,
                'mobile' => $acc->mobile
            ];
        }

        return view('payable.index', compact(
            'today','firstDate','data',
            'allGroupsList','allParties'
        ))->with(['groupWiseData'=>[]]);
    }

    if ($showType == 'party' && $request->party_id) {

        $acc = $allParties->where('id', $request->party_id)->first();
        if (!$acc) {
            return back()->with('error','Party not found');
        }
        $latest = DB::table('overdue_response')
            ->where('account_id', $acc->id)
            ->where('company_id', $company_id)
            ->orderBy('id','DESC')
            ->first();

        $response = $latest->response ?? null;
        $response_date = $latest->response_date ?? null;
        $ledger = DB::table('account_ledger')
            ->where('company_id',$company_id)
            ->where('account_id',$acc->id)
            ->where('delete_status','0')
            ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
            ->first();

        $payable = ($ledger->cr ?? 0) - ($ledger->dr ?? 0);

        $creditDays = $acc->due_day ?? 0;
        $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

        $purchaseSum = DB::table('purchases')
            ->where('company_id',$company_id)
            ->where('party',$acc->id)
            ->where('delete','0')
            ->whereBetween('date', [$fromDate,$today])
            ->sum('total');

        $overdue = max(0,$payable - $purchaseSum);

        $data = [(object)[
            'id'=>$acc->id,
            'party_name'=>$acc->account_name,
            'receivable'=>round($payable,2),
            'overdue'=>round($overdue,2),
            'response'=>$response,
            'response_date'=>$response_date,
            'credit_days'=>$acc->credit_days,
            'due_day'=>$acc->due_day,
            'mobile'=>$acc->mobile
        ]];

        return view('payable.index', compact(
            'today','firstDate','data',
            'allGroupsList','allParties'
        ))->with(['groupWiseData'=>[]]);
    }

    if ($showType == 'group' && $request->group_id) {

        $childGroups = CommonHelper::getAllChildGroupIds(
            $request->group_id,$company_id
        );
        $childGroups[] = $request->group_id;

        $accounts = DB::table('accounts')
            ->where('company_id',$company_id)
            ->whereIn('under_group',$childGroups)
            ->where('delete','0')
            ->get();

        $data = [];

        foreach ($accounts as $acc) {
            $latest = DB::table('overdue_response')
                ->where('account_id', $acc->id)
                ->where('company_id', $company_id)
                ->orderBy('id','DESC')
                ->first();

            $response = $latest->response ?? null;
            $response_date = $latest->response_date ?? null;
            $ledger = DB::table('account_ledger')
                ->where('company_id',$company_id)
                ->where('account_id',$acc->id)
                ->where('delete_status','0')
                ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                ->first();

            $payable = ($ledger->cr ?? 0) - ($ledger->dr ?? 0);
            if ($payable == 0) continue;

            $creditDays = $acc->due_day ?? 0;

            $fromDate = Carbon::parse($today)
                ->subDays($creditDays)
                ->format('Y-m-d');

            $purchaseSum = DB::table('purchases')
                ->where('company_id',$company_id)
                ->where('party',$acc->id)
                ->where('delete','0')
                ->whereBetween('date', [$fromDate,$today])
                ->sum('total');

            if ($payable < 0) {
    $overdue = round($payable,2);
} else {
    $overdue = round(max(0, $payable - $purchaseSum),2);
}

            $data[] = (object)[
                'id'=>$acc->id,
                'party_name'=>$acc->account_name,
                'receivable'=>round($payable,2),
                'overdue'=>round($overdue,2),
                'response'=>$response,
                'response_date'=>$response_date,
                'mobile'=>$acc->mobile ?? '',
                'credit_days'=>$acc->credit_days ?? '',
                'due_day'=>$acc->due_day ?? ''
            ];
        }

        return view('payable.index', compact(
            'today','firstDate','data',
            'allGroupsList','allParties'
        ))->with(['groupWiseData'=>[]]);
    }

    if ($showType == 'allgroup') {

        $buildGroupTree = function($groupIds) use (&$buildGroupTree, $company_id, $today,$request) {

            $groups = DB::table('account_groups')
                ->whereIn('id', $groupIds)
                ->where('delete', '0')
                ->select('id', 'name', 'heading')
                ->get();

            $tree = [];

            foreach ($groups as $grp) {

                $childGroups = DB::table('account_groups')
                    ->where('heading', $grp->id)
                    ->where('company_id', $company_id)
                    ->where('delete', '0')
                    ->pluck('id')
                    ->toArray();

                $accounts = DB::table('accounts')
                    ->where('company_id',$company_id)
                    ->where('under_group',$grp->id)
                    ->where('delete','0')
                    ->select('id','account_name','mobile','credit_days','due_day')
                    ->get();

                $groupTotal = 0;
                $groupOverdue = 0;
                $accountData = [];

                foreach ($accounts as $acc) {
                    $latest = DB::table('overdue_response')
                        ->where('account_id', $acc->id)
                        ->where('company_id', $company_id)
                        ->orderBy('id','DESC')
                        ->first();
                       
                    $response = $latest->response ?? null;
                    $response_date = $latest->response_date ?? null;
                    $ledger = DB::table('account_ledger')
                        ->where('company_id',$company_id)
                        ->where('account_id',$acc->id)
                        ->where(function ($q) use ($request) {
                                $q->where('txn_date', '<=', $request->date)
                                  ->orWhere('entry_type', '-1');
                            })
                        ->where('delete_status','0')
                        ->selectRaw('SUM(debit) as dr, SUM(credit) as cr')
                        ->first();

                    $payable = ($ledger->cr ?? 0) - ($ledger->dr ?? 0);
                    if ($payable == 0) continue;

                    $creditDays = $acc->due_day ?? 0;

                    $fromDate = Carbon::parse($today)
                        ->subDays($creditDays)
                        ->format('Y-m-d');

                    $purchaseSum = DB::table('purchases')
                        ->where('company_id',$company_id)
                        ->where('party',$acc->id)
                        ->where('delete','0')
                        ->whereBetween('date', [$fromDate,$today])
                        ->sum('total');

                    $salesTotal = $purchaseSum;
                    if ($payable < 0) {
                        $overdue = round($payable,2); 
                    } else {
                        $overdue = round(max(0, $payable - $salesTotal),2);
                    }
                    $groupTotal += $payable;
                    $groupOverdue += $overdue;

                    $accountData[] = [
                        'id' => $acc->id,
                        'party_name' => $acc->account_name,
                        'receivable' => round($payable,2),
                        'overdue' => round($overdue,2),
                        'response' => $response,
                        'response_date' => $response_date,
                        'mobile' => $acc->mobile ?? '',
                        'credit_days' => $acc->credit_days ?? '',
                        'due_day' => $acc->due_day ?? ''
                    ];
                }

                $children = [];
                $childTotal = 0;
                $childOverdue = 0;

                if (!empty($childGroups)) {
                    $children = $buildGroupTree($childGroups);
                    foreach ($children as $cg) {
                        $childTotal += $cg['total_receivable'];
                        $childOverdue += $cg['total_overdue'];
                    }
                }

                $total = $groupTotal + $childTotal;
                $totalOverdue = $groupOverdue + $childOverdue;

                if ($total != 0 || !empty($children)) {

                    $tree[] = [
                        'group_id'=>$grp->id,
                        'group_name'=>$grp->name,
                        'total_receivable'=>round($total,2),
                        'total_overdue'=>round($totalOverdue,2),
                        'accounts'=>$accountData,
                        'children'=>$children,
                        'parent_id'=>$grp->heading ?? null
                    ];
                }
            }

            return $tree;
        };

        $groupWiseData = $buildGroupTree($top_groups_list);
        
        return view('payable.index', compact(
            'today','firstDate','allGroupsList','allParties'
        ))->with([
            'data'=>[],
            'groupWiseData'=>$groupWiseData
        ]);
    }

    return redirect()->route('payable.index');
}

 public function overdueDetails(Request $request, $account_id)
{
    $company_id = Session::get('user_company_id');
    $today = $request->query('date') ?? Carbon::today()->toDateString();

    $acc = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('id', $account_id)
        ->first();

    if (!$acc) {
        abort(404, "Account not found.");
    }

    $creditDays = $acc->due_day ?? 0;

    $dueDateLimit = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

    // Latest bills first
    $bills = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
        ->where('status','1')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'DESC')   // IMPORTANT CHANGE
        ->select('id','voucher_no','date','total')
        ->get();

    // Ledger balance
    $ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();
    $open_ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('entry_type', "-1")
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $opening_balance = ($open_ledger->total_credit ?? 0) - ($open_ledger->total_debit ?? 0);
   $receivable = ($ledger->total_credit ?? 0) - ($ledger->total_debit ?? 0);
    // Purchases within credit period (not overdue)
    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

    $recentPurchases = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete','0')
        ->where('status','1')
        ->whereBetween('date', [$fromDate, $today])
        ->sum('total');

   $overdueAmount = $receivable - $recentPurchases;

    if ($overdueAmount < 0) {
        $overdueAmount = 0;
    }

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
    if ($opening_balance != 0) {

        $allocated[] = [
            'id'      => null,
            'date'    => 'Opening',
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance,
            'overdue' => ($remaining > 0) 
                            ? min(abs($opening_balance), $remaining)
                            : 0,
            'remaining_part' => $opening_balance
        ];
    }
    if (empty($allocated) && $overdueAmount > 0) {

        $allocated[] = [
            'id'      => null,
            'date'    => 'Opening',
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance ?: $overdueAmount,
            'overdue' => $overdueAmount,
            'remaining_part' => 0
        ];
    }
    $openingDate = $dueDateLimit;

    $company = DB::table('companies')
        ->where('id', $company_id)
        ->first();

    $oDate = $company->books_start_from; 

    return view('payable.overdue_details', compact(
        'acc','overdueAmount','allocated','today','openingDate','oDate'
    ));
}

public function overdueReportPdf(Request $request, $account_id)
{
    $company_id = Session::get('user_company_id');
    $today = $request->query('date') ?? Carbon::today()->toDateString();

    $acc = DB::table('accounts')
        ->where('company_id', $company_id)
        ->where('id', $account_id)
        ->first();

    if (!$acc) {
        abort(404, "Account not found.");
    }

    $creditDays = $acc->due_day ?? 0;

    $dueDateLimit = Carbon::parse($today)
        ->subDays($creditDays)
        ->format('Y-m-d');

    $bills = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete', '0')
        ->where('status','1')
        ->whereDate('date', '<=', $dueDateLimit)
        ->orderBy('date', 'DESC') 
        ->select('id','voucher_no','date','total')
        ->get();

    $ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $open_ledger = DB::table('account_ledger')
        ->where('company_id', $company_id)
        ->where('account_id', $account_id)
        ->where('entry_type', "-1")
        ->where('delete_status','0')
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $opening_balance = ($open_ledger->total_credit ?? 0) - ($open_ledger->total_debit ?? 0);

    $receivable = ($ledger->total_credit ?? 0) - ($ledger->total_debit ?? 0);

    $fromDate = Carbon::parse($today)->subDays($creditDays)->format('Y-m-d');

    $recentPurchases = DB::table('purchases')
        ->where('company_id', $company_id)
        ->where('party', $account_id)
        ->where('delete','0')
        ->where('status','1')
        ->whereBetween('date', [$fromDate, $today])
        ->sum('total');

    $overdueAmount = $receivable - $recentPurchases;

    if ($overdueAmount < 0) {
        $overdueAmount = 0;
    }

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

    if ($opening_balance != 0) {
        $allocated[] = [
            'id'      => null,
            'date'    => 'Opening',
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance,
            'overdue' => ($remaining > 0) 
                            ? min(abs($opening_balance), $remaining)
                            : 0,
            'remaining_part' => $opening_balance
        ];
    }

    if (empty($allocated) && $overdueAmount > 0) {
        $allocated[] = [
            'id'      => null,
            'date'    => 'Opening',
            'bill_no' => 'Opening Balance',
            'total'   => $opening_balance ?: $overdueAmount,
            'overdue' => $overdueAmount,
            'remaining_part' => 0
        ];
    }

    $pdf = PDF::loadView('payable.overdue_details_pdf', [
        'acc' => $acc,
        'overdueAmount' => $overdueAmount,
        'allocated' => $allocated,
    ])->setPaper('A4', 'portrait');

    return $pdf->download('Overdue_Report_'.$acc->account_name.'.pdf');
}
}
