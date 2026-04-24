<?php

namespace App\Http\Controllers\JobWorkLedger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Session;
use App\Models\Accounts;
use App\Helpers\CommonHelper;

class JobWorkLedgerController extends Controller
{

    private function getSharedData(): array
    {
        $companyId = Session::get('user_company_id');
        $financial_year = Session::get('default_fy');

        [$startYY, $endYY] = explode('-', $financial_year);
        $fy_start_date = '20' . $startYY . '-04-01';
        $fy_end_date   = '20' . $endYY   . '-03-31';

        $top_groups = [3, 11];
        $all_groups = [];
        foreach ($top_groups as $gid) {
            $all_groups[] = $gid;
            $all_groups   = array_merge($all_groups, CommonHelper::getAllChildGroupIds($gid, $companyId));
        }
        $groups = array_unique($all_groups);

        $parties = Accounts::leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->whereIn('accounts.company_id', [$companyId, 0])
            ->whereIn('accounts.under_group', $groups)
            ->select('accounts.id', 'accounts.account_name')
            ->orderBy('accounts.account_name')
            ->get();

        $items = DB::table('manage_items')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '0')
            ->where('manage_items.status', '1')
            ->where('manage_items.company_id', $companyId)
            ->orderBy('manage_items.name')
            ->select(
                'manage_items.id',
                'manage_items.name',
                'units.s_name as unit',
                'item_groups.parameterized_stock_status',
                'item_groups.config_status'
            )
            ->get();

        return compact('parties', 'items', 'fy_start_date', 'fy_end_date', 'companyId');
    }

    public function index()
    {
        $shared = $this->getSharedData();

        return view('JobWorkLedger.index', array_merge($shared, [
            'ledgerData' => null,
            'filters'    => [],
        ]));
    }

    public function fetch(Request $request)
    {
        $shared    = $this->getSharedData();
        $companyId = $shared['companyId'];

        $fromDate = $request->from_date;
        $toDate   = $request->to_date;
        $partyId  = $request->party_id;   
        $itemId   = $request->item_id;    
        if ($itemId === 'all' || $itemId === '') $itemId = null;
        if ($partyId === 'all' || $partyId === '') $partyId = null;

        $jobQuery = DB::table('job_work_descriptions as jwd')
            ->join('job_works as jw',      'jw.id',  '=', 'jwd.job_work_id')
            ->join('accounts as a',        'jw.party_id', '=', 'a.id')
            ->join('manage_items as mi',   'jwd.goods_discription', '=', 'mi.id')
            ->where('jw.company_id', $companyId)
            ->where('jw.delete', 0)
            ->where('jwd.delete', 0)
            ->whereBetween('jw.date', [$fromDate, $toDate])
            ->when($partyId, fn($q) => $q->where('jw.party_id', $partyId))
            ->when($itemId,  fn($q) => $q->where('jwd.goods_discription', $itemId))
            ->select(
                'jw.date',
                'jw.job_work_type',
                'jw.job_work_in_id',
                'jw.job_work_out_id',
                'a.account_name as party_name',
                'mi.id   as item_id',
                'mi.name as item_name',
                'jwd.qty',
                'jwd.unit',
                'jw.voucher_no'
            )
            ->get();

        $journalQuery = DB::table('job_work_stock_journal_items as sji')
            ->join('job_work_stock_journals as sj', 'sj.id', '=', 'sji.job_work_stock_journal_id')
            ->join('accounts as a', 'sj.party_id', '=', 'a.id')
            ->leftJoin('manage_items as mi1', 'mi1.id', '=', 'sji.consume_item_id')
            ->leftJoin('manage_items as mi2', 'mi2.id', '=', 'sji.generated_item_id')
            ->where('sj.company_id', $companyId)
            ->where('sj.delete', 0)
            ->whereBetween('sj.date', [$fromDate, $toDate])
            ->when($partyId, fn($q) => $q->where('sj.party_id', $partyId))
            ->select(
                'sj.date',
                'a.account_name as party_name',
                'sj.voucher_no',
                'sji.consume_item_id',
                'mi1.name as consume_item_name',
                'sji.consume_qty',
                'sji.consume_unit',
                'sji.generated_item_id',
                'mi2.name as generated_item_name',
                'sji.generated_qty',
                'sji.generated_unit'
            )
            ->get();

        $raw = collect();

        foreach ($jobQuery as $row) {
            $raw->push([
                'date'            => $row->date,
                'party_name'      => $row->party_name,
                'item_name'       => $row->item_name,
                'source'          => 'JOB',
                'job_work_type'   => $row->job_work_type,
                'job_work_in_id'  => $row->job_work_in_id,
                'job_work_out_id' => $row->job_work_out_id,
                'qty'             => $row->qty,
                'unit'            => $row->unit,
                'voucher_no'      => $row->voucher_no,
            ]);
        }

        foreach ($journalQuery as $j) {
            if ($j->consume_item_id) {
                $raw->push([
                    'date'       => $j->date,
                    'party_name' => $j->party_name,
                    'item_name'  => $j->consume_item_name,
                    'source'     => 'CONSUME',
                    'qty'        => $j->consume_qty,
                    'unit'       => $j->consume_unit,
                    'voucher_no' => $j->voucher_no,
                ]);
            }
            if ($j->generated_item_id) {
                $raw->push([
                    'date'       => $j->date,
                    'party_name' => $j->party_name,
                    'item_name'  => $j->generated_item_name,
                    'source'     => 'GENERATED',
                    'qty'        => $j->generated_qty,
                    'unit'       => $j->generated_unit,
                    'voucher_no' => $j->voucher_no,
                ]);
            }
        }

        $raw = $raw->sortBy('date')->values();

        $balances   = [];  
        $ledgerData = [];   

        foreach ($raw as $r) {
            $party = $r['party_name'];
            $item  = $r['item_name'];
            $key   = $party . '|' . $item;

            if (!isset($balances[$key])) $balances[$key] = 0;

            if (!isset($ledgerData[$party][$item])) {
                $ledgerData[$party][$item] = [
                    'rows'   => [],
                    'totals' => ['in' => 0, 'out' => 0],
                    'unit'   => $r['unit'],
                ];
            }

            $inQty  = 0;
            $outQty = 0;
            $type   = '';

            if ($r['source'] === 'JOB') {
                if ($r['job_work_type'] === 'OUT') {
                    $outQty = $r['qty'];
                    $balances[$key] += $r['qty'];
                    $type = is_null($r['job_work_in_id'])  ? 'OUT RAW' : 'OUT FINISHED';
                } else {
                    $inQty = $r['qty'];
                    $balances[$key] -= $r['qty'];
                    $type = is_null($r['job_work_out_id']) ? 'IN RAW'  : 'IN FINISHED';
                }
            } elseif ($r['source'] === 'CONSUME') {
                $outQty = $r['qty'];
                $balances[$key] += $r['qty'];
                $type = 'CONSUMED';
            } elseif ($r['source'] === 'GENERATED') {
                $inQty = $r['qty'];
                $balances[$key] -= $r['qty'];
                $type = 'GENERATED';
            }

            $ledgerData[$party][$item]['totals']['in']  += $inQty;
            $ledgerData[$party][$item]['totals']['out']  += $outQty;

            $ledgerData[$party][$item]['rows'][] = [
                'date'       => $r['date'],
                'party_name' => $party,
                'item_name'  => $item,
                'type'       => $type,
                'in_qty'     => $inQty,
                'out_qty'    => $outQty,
                'unit'       => $r['unit'],
                'balance'    => $balances[$key],
                'voucher_no' => $r['voucher_no'],
            ];

            // keep last balance in totals
            $ledgerData[$party][$item]['totals']['balance'] = $balances[$key];
            $ledgerData[$party][$item]['totals']['unit']    = $r['unit'];
        }

        ksort($ledgerData); // sort parties alphabetically

        return view('JobWorkLedger.index', array_merge($shared, [
            'ledgerData' => $ledgerData,
            'filters'    => $request->all(),
        ]));
    }
}