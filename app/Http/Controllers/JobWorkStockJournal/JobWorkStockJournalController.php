<?php

namespace App\Http\Controllers\JobWorkStockJournal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Session;

class JobWorkStockJournalController extends Controller
{

public function index(Request $request)
{
    $input = $request->all();
    $from_date = null;
    $to_date   = null;

    // Date filter persistence (same pattern)
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date   = date('d-m-Y', strtotime($input['to_date']));
        session([
            'jwstock_from_date' => $from_date,
            'jwstock_to_date'   => $to_date
        ]);
    } elseif (session()->has('jwstock_from_date') && session()->has('jwstock_to_date')) {
        $from_date = session('jwstock_from_date');
        $to_date   = session('jwstock_to_date');
    }

    $companyId = Session::get('user_company_id');

    $query = DB::table('job_work_stock_journals as j')
        ->select(
            'j.id',
            'j.date',
            'j.voucher_no',
            'j.total',
            DB::raw('(select account_name from accounts where accounts.id = j.party_id limit 1) as account_name')
        )
        ->where('j.company_id', $companyId)
        ->where('j.status', 1)
        ->where('j.delete', 0);

    if ($from_date && $to_date) {
        $query->whereBetween(
            DB::raw("STR_TO_DATE(j.date, '%Y-%m-%d')"),
            [
                date('Y-m-d', strtotime($from_date)),
                date('Y-m-d', strtotime($to_date))
            ]
        )
        ->orderBy('j.date', 'ASC')
        ->orderBy(DB::raw("CAST(j.voucher_no AS SIGNED)"), 'ASC');
    } else {
        $query->orderBy('j.date', 'DESC')
              ->orderBy(DB::raw("CAST(j.voucher_no AS SIGNED)"), 'DESC')
              ->limit(10);
    }

    $journals = $query->get();

    if (!$from_date && !$to_date) {
        $journals = $journals->reverse()->values();
    }

    return view('JobWorkStockJournal.index')
        ->with('journals', $journals)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

    /**
     * Show create Job Work Stock Journal screen
     */
    public function create()
    {
        $companyId = Session::get('user_company_id');
        $financial_year = Session::get('default_fy');

        [$startYY, $endYY] = explode('-', $financial_year);
        $fy_start_date = '20' . $startYY . '-04-01';
        $fy_end_date   = '20' . $endYY   . '-03-31';

        $top_groups = [3, 11]; 
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                \App\Helpers\CommonHelper::getAllChildGroupIds($group_id, $companyId)
            );
        }

        $groups = array_unique($all_groups);

        $parties = \App\Models\Accounts::leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->whereIn('accounts.company_id', [$companyId, 0])
            ->whereIn('accounts.under_group', $groups)
            ->select(
                'accounts.id',
                'accounts.account_name as name'
            )
            ->orderBy('accounts.account_name')
            ->get();

        $items = DB::table('manage_items')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id', $companyId)
            ->orderBy('manage_items.name')
            ->select([
                'units.s_name as unit',
                'manage_items.id',
                'manage_items.u_name',
                'manage_items.gst_rate',
                'manage_items.name',
                'item_groups.parameterized_stock_status',
                'item_groups.config_status',
                'item_groups.id as group_id',
            ])
            ->get();

        return view('JobWorkStockJournal.create', compact('parties', 'items'));
    }

    /**
     * NEW: Get party vouchers (same logic as JobWork.getInVouchers)
     */
    public function getPartyVouchers(Request $request)
    {
        $companyId = Session::get('user_company_id');
        $type = strtolower($request->type ?? 'in');
        $jobWorkType = $type === 'out' ? 'OUT' : 'IN';

        $vouchers = DB::table('job_works')
            ->where('party_id', $request->party_id)
            ->where('company_id', $companyId)
            ->where('job_work_type', $jobWorkType)
            ->where('status', 1)  
            ->where('delete', 0)
            ->select('id', 'voucher_no')
            ->orderBy('voucher_no')
            ->get();

        return response()->json(['vouchers' => $vouchers]);
    }

    /**
     * MODIFIED: Get pending items based on party/voucher
     */
    public function getPendingItems(Request $request)
    {
        $companyId = Session::get('user_company_id');
        $partyId   = $request->party_id;
        $voucherId = $request->party_voucher_no;
        $date      = $request->date;
        $type      = strtolower($request->type ?? 'in');

        if ($type === 'out') {
            $rows = DB::table('job_work_descriptions as d')
                ->join('job_works as jw', 'jw.id', '=', 'd.job_work_id')
                ->join('manage_items as i', 'i.id', '=', 'd.goods_discription')
                ->join('units as u', 'u.id', '=', 'i.u_name')
                ->where('d.delete', 0)
                ->where('d.status', 1)
                ->where('i.company_id', $companyId)
                ->where('jw.company_id', $companyId)
                ->where('jw.job_work_type', 'OUT')
                ->where('jw.delete', 0)
                ->whereDate('jw.date', '<=', $date);

            if ($partyId) {
                $rows->where('jw.party_id', $partyId);
            }

            if ($voucherId) {
                $rows->where('d.job_work_id', $voucherId);
            }

            $rows->leftJoin('job_work_descriptions as outd', function ($join) {
                $join->on('outd.jw_out_description_id', '=', 'd.id')
                    ->where('outd.delete', 0);
            });

            $rows->leftJoin('job_works as jw_out', function ($join) use ($date) {
                $join->on('jw_out.id', '=', 'outd.job_work_id')
                    ->where('jw_out.job_work_type', 'OUT')
                    ->where('jw_out.delete', 0)
                    ->whereDate('jw_out.date', '<=', $date);
            });

            $rows->groupBy(
                'd.id',
                'd.qty',
                'i.id',
                'i.name',
                'i.u_name',
                'u.s_name'
            );

            $rows->select(
                'd.id as id',
                'i.id as item_id',
                'i.name as name',
                'u.s_name as unit',
                'i.u_name',
                'd.qty as original_qty',
                DB::raw('SUM(
                    CASE
                        WHEN jw_out.id IS NOT NULL
                        THEN outd.qty
                        ELSE 0
                    END
                ) as used_qty'),
                DB::raw('(d.qty - SUM(
                    CASE
                        WHEN jw_out.id IS NOT NULL
                        THEN outd.qty
                        ELSE 0
                    END
                )) as pending_qty'),
                DB::raw('0 as price')
            );

            $rows->havingRaw('
                (d.qty - SUM(
                    CASE
                        WHEN jw_out.id IS NOT NULL
                        THEN outd.qty
                        ELSE 0
                    END
                )) > 0
            ');

            return response()->json($rows->get());
        }

        $rows = DB::table('job_work_descriptions as d')
            ->join('job_works as jw', 'jw.id', '=', 'd.job_work_id') 
            ->join('manage_items as i', 'i.id', '=', 'd.goods_discription')
            ->join('units as u', 'u.id', '=', 'i.u_name')
            ->join('item_groups as g', 'g.id', '=', 'i.g_name')
            ->where('d.delete', 0)
            ->where('d.status', 1) 
            ->where('i.company_id', $companyId)
            ->where('jw.company_id', $companyId)
            ->where('jw.job_work_type', 'IN')
            ->where('jw.delete', 0)
            ->whereDate('jw.date', '<=', $date); 

        if ($partyId) {
            $rows->where('jw.party_id', $partyId);
        }

        if ($voucherId) {
            $rows->where('d.job_work_id', $voucherId);
        }

        $rows->leftJoin('job_work_descriptions as outd', function ($join) {
            $join->on('outd.jw_in_description_id', '=', 'd.id')
                ->where('outd.delete', 0);
        });

        $rows->leftJoin('job_works as jw_out', function ($join) use ($date) {
            $join->on('jw_out.id', '=', 'outd.job_work_id')
                ->where('jw_out.job_work_type', 'OUT')
                ->where('jw_out.delete', 0)
                ->whereDate('jw_out.date', '<=', $date); 
        });

        $rows->groupBy(
            'd.id',
            'd.qty',
            'i.id',
            'i.name',
            'i.u_name',
            'u.s_name'
        );

        $rows->select(
        'd.id as id',
        'i.id as item_id',
        'i.name as name',
        'u.s_name as unit',
        'i.u_name',
        'd.qty as original_qty',

        DB::raw('SUM(
            CASE 
                WHEN jw_out.id IS NOT NULL 
                THEN outd.qty 
                ELSE 0 
            END
        ) as used_qty'),

        DB::raw('(d.qty - SUM(
            CASE 
                WHEN jw_out.id IS NOT NULL 
                THEN outd.qty 
                ELSE 0 
            END
        )) as pending_qty'),

        DB::raw('0 as price')
    );


        $rows->havingRaw('
        (d.qty - SUM(
            CASE 
                WHEN jw_out.id IS NOT NULL 
                THEN outd.qty 
                ELSE 0 
            END
        )) > 0
    ');

        return response()->json($rows->get());
    }

    /**
     * MODIFIED: Store - Remove job_work_in_id dependency
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'party_id'      => 'required',
            'type'          => 'required|in:in,out',
            'voucher_no'    => 'required|string|max:50',
            'consume_item'  => 'required|array',
            'consume_qty'   => 'required|array',
            'consume_price' => 'required|array',
        ]);
        $type = strtolower($request->type ?? 'in');

        $companyId = session('user_company_id');
        $userId    = session('user_id');

        DB::beginTransaction();

        try {

            $journalId = DB::table('job_work_stock_journals')->insertGetId([
                'company_id'        => $companyId,
                'party_id'          => $request->party_id,
                'party_voucher_no'  => $request->party_voucher_no ?: null, 
                'voucher_no'        => $request->voucher_no,
                'date'              => $request->date,
                'narration'         => $request->narration,
                'total'             => 0,
                'status'            => 1,
                'delete'            => 0,
                'created_by'        => $userId,
                'created_at'        => now(),
            ]);

            $grandTotal = 0;
            $usedDescIds = [];

            foreach ($request->consume_item as $i => $descId) {

                $qty   = (float) ($request->consume_qty[$i] ?? 0);
                $price = (float) ($request->consume_price[$i] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $desc = DB::table('job_work_descriptions')
                    ->where('id', $descId)
                    ->where('delete', 0)
                    ->first();

                if (!$desc) {
                    continue;
                }

                $amount = $qty * $price;
                $grandTotal += $amount;

                DB::table('job_work_stock_journal_items')->insert([
                    'job_work_stock_journal_id'   => $journalId,
                    'job_work_in_description_id'  => $type === 'in' ? $descId : null,
                    'job_work_out_description_id' => $type === 'out' ? $descId : null,
                    'consume_item_id'             => $desc->goods_discription,
                    'consume_unit'                => $desc->unit,
                    'consume_qty'                 => $qty,
                    'consume_price'               => $price,
                    'consume_amount'              => $amount,
                    'company_id'                  => $companyId,
                    'status'                      => 1,
                    'created_at'                  => now(),
                ]);

                $usedDescIds[] = $descId;
            }

            DB::table('job_work_stock_journals')
                ->where('id', $journalId)
                ->update([
                    'total'      => $grandTotal,
                    'updated_at' => now(),
                ]);

            if (!empty($usedDescIds)) {
                DB::table('job_work_descriptions')
                    ->whereIn('id', $usedDescIds)
                    ->update([
                        'status'     => 2,
                        'updated_at' => now(),
                    ]);
            }
            $jobWorkIds = DB::table('job_work_descriptions')
                ->whereIn('id', $usedDescIds)
                ->pluck('job_work_id')
                ->unique();

            foreach ($jobWorkIds as $jwId) {

                $remaining = DB::table('job_work_descriptions')
                    ->where('job_work_id', $jwId)
                    ->where('delete', 0)
                    ->where('status', 1) // still open
                    ->count();

                if ($remaining == 0) {
                    DB::table('job_works')
                        ->where('id', $jwId)
                        ->update([
                            'status' => 2,
                            'updated_at' => now()
                        ]);
                }
            }

            if ($request->has('generated_item')) {

                foreach ($request->generated_item as $i => $itemId) {

                    if (!$itemId) {
                        continue;
                    }

                    $qty   = (float) ($request->generated_weight[$i] ?? 0);
                    $price = (float) ($request->generated_price[$i] ?? 0);

                    if ($qty <= 0) {
                        continue;
                    }

                    $amount = $qty * $price;
                    $unitId = $request->generated_units[$i] ?? null;

                    $unitName = null;
                    if ($unitId) {
                        $unitName = DB::table('units')
                            ->where('id', $unitId)
                            ->value('s_name');
                    }
                    DB::table('job_work_stock_journal_items')->insert([
                        'job_work_stock_journal_id' => $journalId,

                        // 🔴 NOT APPLICABLE
                        'job_work_in_description_id' => null,
                        'job_work_out_description_id' => null,
                        'consume_item_id'            => null,
                        'consume_unit'               => null,
                        'consume_qty'                => null,
                        'consume_price'              => null,
                        'consume_amount'             => null,

                        // ✅ GENERATED
                        'generated_item_id' => $itemId,
                        'generated_unit'    => $unitName,
                        'generated_qty'     => $qty,
                        'generated_price'   => $price,
                        'generated_amount'  => $amount,

                        'company_id' => $companyId,
                        'status'     => 1,
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('jobwork.stockjournal.create')
                ->with('success', 'Job Work Stock Journal saved successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

/**
 * Show edit Job Work Stock Journal screen
 */
    public function edit($id)
    {
        $companyId = Session::get('user_company_id');

        // Get the journal with all related items
        $journal = DB::table('job_work_stock_journals as j')
            ->leftJoin('accounts', 'accounts.id', '=', 'j.party_id')
            ->where('j.id', $id)
            ->where('j.company_id', $companyId)
            ->where('j.status', 1)
            ->where('j.delete', 0)
            ->select(
                'j.*',
                'accounts.account_name as party_name'
            )
            ->first();
        $journalType = 'in';

        if (!$journal) {
            return redirect()->route('jobwork.stockjournal.index')
                ->with('error', 'Journal not found!');
        }

        // Get consume items (supports both IN and OUT description links)
        $consume_items = DB::table('job_work_stock_journal_items as sji')
        ->join('job_work_descriptions as d', 'd.id', '=', DB::raw('COALESCE(sji.job_work_in_description_id, sji.job_work_out_description_id)'))
        ->join('manage_items as i', 'i.id', '=', 'd.goods_discription')
        ->join('units as u', 'u.id', '=', 'i.u_name')
        ->where('sji.job_work_stock_journal_id', $id)
        ->where(function ($query) {
            $query->whereNotNull('sji.job_work_in_description_id')
                ->orWhereNotNull('sji.job_work_out_description_id');
        })
        ->where('sji.status', 1)
        ->select(
            'd.id as desc_id',
            'i.name as item_name',
            'u.s_name as unit',
            'sji.consume_qty as qty',
            'sji.consume_price as price',
            'sji.consume_amount as amount'
        )
        ->get();
        $hasOutLinkedItems = DB::table('job_work_stock_journal_items')
            ->where('job_work_stock_journal_id', $id)
            ->whereNotNull('job_work_out_description_id')
            ->exists();
        if ($hasOutLinkedItems) {
            $journalType = 'out';
        }


        // Get generated items (those with generated_item_id)
        $generated_items = DB::table('job_work_stock_journal_items')
            ->where('job_work_stock_journal_id', $id)
            ->whereNotNull('generated_item_id')
            ->where('status', 1)
            ->select('id', 'generated_item_id as item_id', 'generated_unit as unit', 
                    'generated_qty as weight', 'generated_price as price', 'generated_amount as amount',
                    'generated_unit as unit_id')
            ->get();

        // Attach items to journal (for blade access)
        $journal->consume_items = $consume_items;
        $journal->generated_items = $generated_items;
        $journal->type = old('type', $journalType);

        // Same parties and items as create
        $top_groups = [3, 11]; 
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                \App\Helpers\CommonHelper::getAllChildGroupIds($group_id, $companyId)
            );
        }

        $groups = array_unique($all_groups);

        $parties = \App\Models\Accounts::leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->whereIn('accounts.company_id', [$companyId, 0])
            ->whereIn('accounts.under_group', $groups)
            ->select(
                'accounts.id',
                'accounts.account_name as name'
            )
            ->orderBy('accounts.account_name')
            ->get();

        $items = DB::table('manage_items')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id', $companyId)
            ->orderBy('manage_items.name')
            ->select([
                'units.s_name as unit',
                'manage_items.id',
                'manage_items.u_name',
                'manage_items.gst_rate',
                'manage_items.name',
                'item_groups.parameterized_stock_status',
                'item_groups.config_status',
                'item_groups.id as group_id',
            ])
            ->get();

        return view('JobWorkStockJournal.edit', compact('journal', 'parties', 'items'));
    }

/**
 * Update Job Work Stock Journal
 */
    public function update(Request $request, $id)
    {
        //dd($request->all());
        $request->validate([
            'party_id'      => 'required',
            'type'          => 'required|in:in,out',
            'voucher_no'    => 'required|string|max:50',
            'consume_item'  => 'sometimes|array',
            'consume_qty'   => 'sometimes|array',
            'consume_price' => 'sometimes|array',
        ]);
        $type = strtolower($request->type ?? 'in');

        $companyId = session('user_company_id');
        $userId    = session('user_id');

        DB::beginTransaction();

        try {
            $journal = DB::table('job_work_stock_journals')
                ->where('id', $id)
                ->where('company_id', $companyId)
                ->where('status', 1)
                ->where('delete', 0)
                ->first();

            if (!$journal) {
                throw new \Exception('Journal not found!');
            }

            $previouslyConsumed = DB::table('job_work_stock_journal_items')
                ->where('job_work_stock_journal_id', $id)
                ->where(function ($query) {
                    $query->whereNotNull('job_work_in_description_id')
                        ->orWhereNotNull('job_work_out_description_id');
                })
                ->select(DB::raw('COALESCE(job_work_in_description_id, job_work_out_description_id) as description_id'))
                ->pluck('description_id');

            if ($previouslyConsumed->isNotEmpty()) {
                DB::table('job_work_descriptions')
                    ->whereIn('id', $previouslyConsumed)
                    ->update(['status' => 1]); // Re-open for editing
            }

            DB::table('job_work_stock_journal_items')
                ->where('job_work_stock_journal_id', $id)
                ->delete();

            $grandTotal = 0;
            $usedDescIds = [];

            DB::table('job_work_stock_journals')
                ->where('id', $id)
                ->update([
                    'company_id'        => $companyId,
                    'party_id'          => $request->party_id,
                    'party_voucher_no'  => $request->party_voucher_no ?: null,
                    'voucher_no'        => $request->voucher_no,
                    'date'              => $request->date,
                    'narration'         => $request->narration,
                    'total'             => 0, // Will update later
                    'updated_by'        => $userId,
                    'updated_at'        => now(),
                ]);

            if ($request->has('consume_item')) {
                foreach ($request->consume_item as $i => $descId) {
                    $qty   = (float) ($request->consume_qty[$i] ?? 0);
                    $price = (float) ($request->consume_price[$i] ?? 0);

                    if ($qty <= 0) {
                        continue;
                    }

                    $desc = DB::table('job_work_descriptions')
                        ->where('id', $descId)
                        ->where('delete', 0)
                        ->first();

                    if (!$desc) {
                        continue;
                    }

                    $amount = $qty * $price;
                    $grandTotal += $amount;

                    DB::table('job_work_stock_journal_items')->insert([
                        'job_work_stock_journal_id'   => $id,
                        'job_work_in_description_id'  => $type === 'in' ? $descId : null,
                        'job_work_out_description_id' => $type === 'out' ? $descId : null,
                        'consume_item_id'             => $desc->goods_discription,
                        'consume_unit'                => $desc->unit,
                        'consume_qty'                 => $qty,
                        'consume_price'               => $price,
                        'consume_amount'              => $amount,
                        'company_id'                  => $companyId,
                        'status'                      => 1,
                        'created_at'                  => now(),
                    ]);

                    $usedDescIds[] = $descId;
                }
            }

            if ($request->has('generated_item')) {
                foreach ($request->generated_item as $i => $itemId) {
                    if (!$itemId) {
                        continue;
                    }

                    $qty   = (float) ($request->generated_weight[$i] ?? 0);
                    $price = (float) ($request->generated_price[$i] ?? 0);

                    if ($qty <= 0) {
                        continue;
                    }

                    $amount = $qty * $price;
                    $unitId = $request->generated_units[$i] ?? null;

                    $unitName = null;
                    if ($unitId) {
                        $unitName = DB::table('units')
                            ->where('id', $unitId)
                            ->value('s_name');
                    }
                    DB::table('job_work_stock_journal_items')->insert([
                        'job_work_stock_journal_id' => $id,
                        'job_work_in_description_id' => null,
                        'job_work_out_description_id' => null,
                        'consume_item_id'           => null,
                        'consume_unit'              => null,
                        'consume_qty'               => null,
                        'consume_price'             => null,
                        'consume_amount'            => null,
                        'generated_item_id'         => $itemId,
                        'generated_unit'            => $unitName,
                        'generated_qty'             => $qty,
                        'generated_price'           => $price,
                        'generated_amount'          => $amount,
                        'company_id'                => $companyId,
                        'status'                    => 1,
                        'created_at'                => now(),
                    ]);
                }
            }

            DB::table('job_work_stock_journals')
                ->where('id', $id)
                ->update([
                    'total'      => $grandTotal,
                    'updated_at' => now(),
                ]);

            if (!empty($usedDescIds)) {
                DB::table('job_work_descriptions')
                    ->whereIn('id', $usedDescIds)
                    ->update([
                        'status'     => 2, // Close used descriptions
                        'updated_at' => now(),
                    ]);
            }
            $jobWorkIds = DB::table('job_work_descriptions')
                ->whereIn('id', $usedDescIds)
                ->pluck('job_work_id')
                ->unique();

            foreach ($jobWorkIds as $jwId) {

                $remaining = DB::table('job_work_descriptions')
                    ->where('job_work_id', $jwId)
                    ->where('delete', 0)
                    ->where('status', 1)
                    ->count();

                if ($remaining == 0) {
                    DB::table('job_works')
                        ->where('id', $jwId)
                        ->update([
                            'status' => 2,
                            'updated_at' => now()
                        ]);
                }
            }
            DB::commit();

            return redirect()
                ->route('jobwork.stockjournal.index')
                ->with('success', 'Job Work Stock Journal updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

}
