<?php

namespace App\Http\Controllers\PaymentRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\AccountGroups;
use App\Helpers\CommonHelper;
use Session;
use DB;
class PaymentRegisterReportController extends Controller
{
        public function index(Request $request)
    {
        $top_groups = [3];

        $all_groups = [];

        foreach ($top_groups as $group_id)
        {
            $all_groups[] = $group_id;

            $all_groups = array_merge(
                $all_groups,
                CommonHelper::getAllChildGroupIds(
                    $group_id,
                    Session::get('user_company_id')
                )
            );
        }

        $group_ids = array_unique($all_groups);

        // Parties
        $allParties = Accounts::whereIn(
                            'company_id',
                            [Session::get('user_company_id'),0]
                        )
                        ->where('delete', '0')
                        ->where('status', '1')
                        ->whereIn('under_group', $group_ids)
                        ->select(
                            'id',
                            'account_name'
                        )
                        ->orderBy('account_name')
                        ->get();

        // Groups
        $allGroups = AccountGroups::whereIn(
                    'company_id',
                    [Session::get('user_company_id'),0]
                )
                ->where('delete', '0')
                ->where(function($q) use ($group_ids){

                    $q->whereIn(
                        'id',
                        $group_ids
                    )

                    ->orWhereIn(
                        'heading',
                        $group_ids
                    );

                })
                ->orderBy('name')
                ->get();

        $query = DB::table('payment_details')

                    ->join(
                        'payments',
                        'payments.id',
                        '=',
                        'payment_details.payment_id'
                    )

                    ->join(
                        'accounts',
                        'accounts.id',
                        '=',
                        'payment_details.account_name'
                    )

                    ->where(
                        'payment_details.type',
                        'Debit'
                    )

                    ->where(
                        'payment_details.company_id',
                        Session::get('user_company_id')
                    )

                    ->where(
                        'payment_details.delete',
                        '0'
                    )

                    ->whereBetween(
                        'payments.date',
                        [
                            $request->from_date ?? date('Y-m-01'),
                            $request->to_date ?? date('Y-m-d')
                        ]
                    );


        // Party Wise Filter
        if(
            $request->show_type == 'party'
            &&
            !empty($request->party_id)
        )
        {
            $query->where(
                'accounts.id',
                $request->party_id
            );
        }
        // Group Wise Filter
        if(
            $request->show_type == 'group'
            &&
            !empty($request->group_id)
        )
        {

            $selected_groups = [];

            $selected_groups[] = $request->group_id;

            $selected_groups = array_merge(
                $selected_groups,
                CommonHelper::getAllChildGroupIds(
                    $request->group_id,
                    Session::get('user_company_id')
                )
            );

            $selected_groups = array_unique($selected_groups);

            $query->whereIn(
                'accounts.under_group',
                $selected_groups
            );
        }

        $data = $query->select(
                        'accounts.id',
                        'accounts.account_name',
                        DB::raw('SUM(payment_details.debit) as amount')
                    )

                    ->groupBy(
                        'accounts.id',
                        'accounts.account_name'
                    )

                    ->orderBy('accounts.account_name')

                    ->get();
        $groupWiseData = [];

        if ($request->show_type == 'allgroup')
        {

            $buildGroupTree = function($groupIds) use (&$buildGroupTree, $request) {

                $groups = DB::table('account_groups')
                            ->whereIn('id', $groupIds)
                            ->where('delete', '0')
                            ->select('id', 'name', 'heading')
                            ->orderBy('name')
                            ->get();

                $tree = [];

                foreach ($groups as $grp)
                {

                    $childGroups = DB::table('account_groups')
                                        ->where('heading', $grp->id)
                                        ->where('company_id', Session::get('user_company_id'))
                                        ->where('delete', '0')
                                        ->pluck('id')
                                        ->toArray();

                    $accounts = DB::table('accounts')
                                    ->where('company_id', Session::get('user_company_id'))
                                    ->where('delete', '0')
                                    ->where('under_group', $grp->id)
                                    ->select('id', 'account_name')
                                    ->get();

                    $accountData = [];

                    $groupTotal = 0;

                    foreach ($accounts as $acc)
                    {

                        $paymentAmount = DB::table('payment_details')

                                        ->join(
                                            'payments',
                                            'payments.id',
                                            '=',
                                            'payment_details.payment_id'
                                        )

                                        ->where(
                                            'payment_details.type',
                                            'Debit'
                                        )

                                        ->where(
                                            'payment_details.company_id',
                                            Session::get('user_company_id')
                                        )

                                        ->where(
                                            'payment_details.account_name',
                                            $acc->id
                                        )

                                        ->where(
                                            'payment_details.delete',
                                            '0'
                                        )

                                        ->whereBetween(
                                            'payments.date',
                                            [
                                                $request->from_date ?? date('Y-m-01'),
                                                $request->to_date ?? date('Y-m-d')
                                            ]
                                        )

                                        ->sum('payment_details.debit');

                        if($paymentAmount == 0)
                        {
                            continue;
                        }

                        $groupTotal += $paymentAmount;

                        $accountData[] = [
                            'id' => $acc->id,
                            'party_name' => $acc->account_name,
                            'amount' => $paymentAmount
                        ];
                    }

                    // Child Recursion
                    $children = [];

                    $childTotal = 0;

                    if (!empty($childGroups))
                    {
                        $children = $buildGroupTree($childGroups);

                        foreach ($children as $cg)
                        {
                            $childTotal += $cg['total_amount'];
                        }
                    }

                    $finalTotal = $groupTotal + $childTotal;

                    $tree[] = [
                        'group_id' => $grp->id,
                        'group_name' => $grp->name,
                        'total_amount' => $finalTotal,
                        'accounts' => $accountData,
                        'children' => $children,
                        'parent_id' => $grp->heading ?? null,
                    ];
                }

                return $tree;
            };

            $groupWiseData = $buildGroupTree([3]);
        }
        return view(
            'PaymentRegister.index',
            compact(
                'allParties',
                'allGroups',
                'data',
                'groupWiseData'
            )
        );
    }

    public function paymentRegisterModalDetails(Request $request)
    {
        $query = DB::table('payment_details')

                    ->join(
                        'payments',
                        'payments.id',
                        '=',
                        'payment_details.payment_id'
                    )

                    ->join(
                        'accounts',
                        'accounts.id',
                        '=',
                        'payment_details.account_name'
                    )

                    ->where(
                        'payment_details.type',
                        'Debit'
                    )

                    ->where(
                        'payment_details.company_id',
                        Session::get('user_company_id')
                    )

                    ->where(
                        'payment_details.delete',
                        '0'
                    )

                    ->whereBetween(
                        'payments.date',
                        [
                            $request->from_date,
                            $request->to_date
                        ]
                    );

        if (!empty($request->account_id))
        {
            $query->where(
                'accounts.id',
                $request->account_id
            );
        }

        if (!empty($request->group_id))
        {

            $selected_groups = [];

            $selected_groups[] = $request->group_id;

            $selected_groups = array_merge(
                $selected_groups,
                CommonHelper::getAllChildGroupIds(
                    $request->group_id,
                    Session::get('user_company_id')
                )
            );

            $selected_groups = array_unique($selected_groups);

            $query->whereIn(
                'accounts.under_group',
                $selected_groups
            );
        }

        $data = $query

                ->select(

                    'payments.id as payment_id',

                    'payments.date',

                    'payments.voucher_no',

                    DB::raw("
                        CASE

                            WHEN payments.mode = '0'
                            THEN 'IMPS/NEFT/RTGS'

                            WHEN payments.mode = '1'
                            THEN 'CASH'

                            WHEN payments.mode = '2'
                            THEN 'CHEQUE'

                            ELSE payments.mode

                        END as mode
                    "),

                    'accounts.account_name',

                    'payment_details.debit as amount'

                )

                ->orderBy('payments.date')

                ->get();

        return response()->json($data);
    }
}