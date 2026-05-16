<?php

namespace App\Http\Controllers\ReceiptRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\AccountGroups;
use App\Helpers\CommonHelper;
use Session;
use DB;
class ReceiptRegisterController extends Controller
{

    public function index(Request $request)
    {
        $top_groups = [11];

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

        $query = DB::table('receipt_details')

                    ->join(
                        'receipts',
                        'receipts.id',
                        '=',
                        'receipt_details.receipt_id'
                    )

                    ->join(
                        'accounts',
                        'accounts.id',
                        '=',
                        'receipt_details.account_name'
                    )

                    ->where(
                        'receipt_details.type',
                        'Credit'
                    )

                    ->where(
                        'receipt_details.company_id',
                        Session::get('user_company_id')
                    )

                    ->where(
                        'receipt_details.delete',
                        '0'
                    )

                    ->whereBetween(
                        'receipts.date',
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
                        DB::raw('SUM(receipt_details.credit) as amount')
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

                        $receiptAmount = DB::table('receipt_details')

                                        ->join(
                                            'receipts',
                                            'receipts.id',
                                            '=',
                                            'receipt_details.receipt_id'
                                        )

                                        ->where(
                                            'receipt_details.type',
                                            'Credit'
                                        )

                                        ->where(
                                            'receipt_details.company_id',
                                            Session::get('user_company_id')
                                        )

                                        ->where(
                                            'receipt_details.account_name',
                                            $acc->id
                                        )

                                        ->where(
                                            'receipt_details.delete',
                                            '0'
                                        )

                                        ->whereBetween(
                                            'receipts.date',
                                            [
                                                $request->from_date ?? date('Y-m-01'),
                                                $request->to_date ?? date('Y-m-d')
                                            ]
                                        )

                                        ->sum('receipt_details.credit');

                        if($receiptAmount == 0)
                        {
                            continue;
                        }

                        $groupTotal += $receiptAmount;

                        $accountData[] = [
                            'id' => $acc->id,
                            'party_name' => $acc->account_name,
                            'amount' => $receiptAmount
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

            $groupWiseData = $buildGroupTree([11]);
        }
        return view(
            'ReceiptRegister.index',
            compact(
                'allParties',
                'allGroups',
                'data',
                'groupWiseData'
            )
        );
    }

    public function receiptRegisterModalDetails(Request $request)
{
    $query = DB::table('receipt_details')

                ->join(
                    'receipts',
                    'receipts.id',
                    '=',
                    'receipt_details.receipt_id'
                )

                ->join(
                    'accounts',
                    'accounts.id',
                    '=',
                    'receipt_details.account_name'
                )

                ->where(
                    'receipt_details.type',
                    'Credit'
                )

                ->where(
                    'receipt_details.company_id',
                    Session::get('user_company_id')
                )

                ->where(
                    'receipt_details.delete',
                    '0'
                )

                ->whereBetween(
                    'receipts.date',
                    [
                        $request->from_date,
                        $request->to_date
                    ]
                );

    // ACCOUNT FILTER
    if (!empty($request->account_id))
    {
        $query->where(
            'accounts.id',
            $request->account_id
        );
    }

    // GROUP FILTER
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

                'receipts.id as receipt_id',

                'receipts.date',

                'receipts.voucher_no',

                DB::raw("
                    CASE

                        WHEN receipts.mode = '0'
                        THEN 'IMPS/NEFT/RTGS'

                        WHEN receipts.mode = '1'
                        THEN 'CASH'

                        WHEN receipts.mode = '2'
                        THEN 'CHEQUE'

                        ELSE receipts.mode

                    END as mode
                "),

                'accounts.account_name',

                'receipt_details.credit as amount'

            )

            ->orderBy('receipts.date')

            ->get();

    return response()->json($data);
}
}