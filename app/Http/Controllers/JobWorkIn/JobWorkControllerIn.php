<?php

namespace App\Http\Controllers\JobWorkIn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\MerchantModuleMapping;
use App\Models\JobWork;
use App\Models\GstBranch;
use App\Models\VoucherSeriesConfiguration;
use Session;
use DB;

class JobWorkControllerIn extends Controller
{

    public function index(Request $request)
    {
        $type = request()->route()->defaults['type'] ?? 'raw';
        $input = $request->all();
        $from_date = null;
        $to_date = null;

        if (!empty($input['from_date']) && !empty($input['to_date'])) {
            $from_date = date('d-m-Y', strtotime($input['from_date']));
            $to_date   = date('d-m-Y', strtotime($input['to_date']));
            session([
                'jobworkin_from_date' => $from_date,
                'jobworkin_to_date'   => $to_date
            ]);
        } elseif (session()->has('jobworkin_from_date') && session()->has('jobworkin_to_date')) {
            $from_date = session('jobworkin_from_date');
            $to_date   = session('jobworkin_to_date');
        }

        $companyId = Session::get('user_company_id');

        $query = DB::table('job_works')
            ->select(
                'job_works.id',
                'job_works.date',
                'job_works.voucher_no',
                'job_works.voucher_no_prefix',
                'job_works.total',
                'job_works.series_no',
                DB::raw('(select account_name from accounts where accounts.id = job_works.party_id limit 1) as account_name')
            )
            ->where('job_works.company_id', $companyId)
            ->where('job_works.job_work_type', 'IN') // 🔥 ONLY IN
            ->where('job_works.status', 1)      
            ->where('job_works.delete', 0);
        // RAW vs FINISHED FILTER
        if($type == 'raw'){
            $query->whereNull('job_works.job_work_out_id');
        }

        if($type == 'finished'){
            $query->whereNotNull('job_works.job_work_out_id');
        }
            if ($from_date && $to_date) {
                $query->whereBetween(
                    DB::raw("STR_TO_DATE(job_works.date, '%Y-%m-%d')"),
                    [
                        date('Y-m-d', strtotime($from_date)),
                        date('Y-m-d', strtotime($to_date))
                    ]
                )
                ->orderBy('job_works.date', 'ASC')
                ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
            } else {
                $query->orderBy('job_works.date', 'DESC')
                    ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
                    ->limit(10);
            }

            $jobWorks = $query->get();

            if (!$from_date && !$to_date) {
                $jobWorks = $jobWorks->reverse()->values();
            }

        return view('JobWorkIn.index')
            ->with('type', $type)
            ->with('jobWorks', $jobWorks)
            ->with('from_date', $from_date)
            ->with('to_date', $to_date);
    }


    public function create()
    {
        $type = request()->route()->defaults['type'] ?? 'raw';
        $companyId = Session::get('user_company_id');
        $financial_year = Session::get('default_fy');

        [$startYY, $endYY] = explode('-', $financial_year);
        $fy_start_date = '20' . $startYY . '-04-01';
        $fy_end_date   = '20' . $endYY   . '-03-31';

        // Default date
        $job_work_date = date('Y-m-d');


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

        $party_list = \App\Models\Accounts::leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->whereIn('accounts.company_id', [$companyId, 0])
            ->whereIn('accounts.under_group', $groups)
            ->select(
                'accounts.id',
                'accounts.account_name',
                'accounts.gstin',
                'accounts.address',
                'accounts.pin_code',
                'states.state_code'
            )
            ->orderBy('accounts.account_name')
            ->get();


        $items = DB::table('manage_items')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '0')
            ->where('manage_items.status', '1')
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


        $units = DB::table('units')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();


        $transport = [
            'vehicle_no'     => '',
            'transport_name' => '',
            'reverse_charge' => '',
            'gr_rr_no'       => '',
            'station'        => '',
        ];


        $comp = Companies::select('user_id')
            ->where('id', $companyId)
            ->first();

        $production_module_status = 0;
        if ($comp) {
            $production_module_status = MerchantModuleMapping::where('module_id', 4)
                ->where('merchant_id', $comp->user_id)
                ->exists() ? 1 : 0;
        }
        $companyData = Companies::where('id', $companyId)->first();

        if ($companyData->gst_config_type == "single_gst") {

            $GstSettings = DB::table('gst_settings')
                ->where([
                    'company_id' => $companyId,
                    'gst_type' => "single_gst"
                ])
                ->get();

            $branch = GstBranch::select(
                    'id',
                    'gst_number as gst_no',
                    'branch_matcenter as mat_center',
                    'branch_series as series'
                )
                ->where([
                    'delete' => '0',
                    'company_id' => $companyId,
                    'gst_setting_id' => $GstSettings[0]->id ?? null
                ])
                ->get();

            if ($branch->count()) {
                $GstSettings = $GstSettings->merge($branch);
            }

        } else {

            $GstSettings = DB::table('gst_settings_multiple')
                ->select('id', 'gst_no', 'mat_center', 'series')
                ->where([
                    'company_id' => $companyId,
                    'gst_type' => "multiple_gst"
                ])
                ->get();

            foreach ($GstSettings as $value) {

                $branch = GstBranch::select(
                        'id',
                        'gst_number as gst_no',
                        'branch_matcenter as mat_center',
                        'branch_series as series'
                    )
                    ->where([
                        'delete' => '0',
                        'company_id' => $companyId,
                        'gst_setting_multiple_id' => $value->id
                    ])
                    ->get();

                if ($branch->count()) {
                    $GstSettings = $GstSettings->merge($branch);
                }
            }
        }

        if (!$companyData->gst_config_type || !$GstSettings || $GstSettings->isEmpty()) {
            return redirect()->route(
            $type == 'raw' ? 'jobworkin.raw' : 'jobworkin.finished'
        )
                ->with('error', 'Please Enter GST Configuration!');
        }
        foreach ($GstSettings as $key => $value) {

            $config = VoucherSeriesConfiguration::where('company_id', $companyId)
                ->where('series', $value->series)
                ->where('configuration_for', $type === 'finished'
                    ? 'JOB WORK FINISHED GOODS'
                    : 'JOB WORK RAW MATERIAL')
                                ->where('status', 1)
                                ->first();

            if (!$config) {
                $value->manual_enter_invoice_no = "";
                $value->duplicate_voucher = "";
                $value->blank_voucher = "";
                $value->invoice_prefix = "";
                continue; 
            }

            $voucher_no = DB::table('job_works')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$fy_start_date, $fy_end_date])
                ->where('series_no', $value->series)
                ->where('delete', 0)
                ->max(DB::raw("CAST(voucher_no AS SIGNED)"));

            $value->invoice_start_from = $voucher_no
                ? sprintf("%'03d", $voucher_no + 1)
                : sprintf("%'03d", $config->invoice_start ?? 1);

            $invoice_prefix = "";
            $manual = ($config->manual_numbering == "YES") ? "1" : "0";

            if ($manual == "0") {

                if ($config->prefix == "ENABLE" && $config->prefix_value) {
                    $invoice_prefix .= $config->prefix_value;
                    $invoice_prefix .= $config->separator_1 ?? '';
                }

                if ($config->year == "PREFIX TO NUMBER") {
                    $invoice_prefix .= Session::get('default_fy');
                    $invoice_prefix .= $config->separator_2 ?? '';
                }

                $invoice_prefix .= $value->invoice_start_from;

                if ($config->suffix == "ENABLE" && $config->suffix_value) {
                    $invoice_prefix .= $config->separator_3 ?? '';
                    $invoice_prefix .= $config->suffix_value;
                }
            }

            $value->manual_enter_invoice_no = $manual;
            $value->duplicate_voucher = $config->duplicate_voucher;
            $value->blank_voucher = $config->blank_voucher;
            $value->invoice_prefix = $invoice_prefix;
        }

        $GstSettings = $GstSettings->values();

        return view('JobWorkIn.create')->with([
            'type' => $type,
            'fy_start_date'            => $fy_start_date,
            'fy_end_date'              => $fy_end_date,
            'job_work_date'            => $job_work_date,
            'party_list'               => $party_list,
            'items'                    => $items,
            'units'                    => $units,
            'transport'                => $transport,
            'production_module_status' => $production_module_status,
            'GstSettings'   => $GstSettings,
        ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'series_no'         => 'required',
            'date'              => 'required',
            'voucher_no'        => 'required',
            'party_id'          => 'required',
            'material_center'   => 'required',
            'goods_discription' => 'required|array',
            'qty'               => 'required|array',
            'units'             => 'required|array',
            'price'             => 'required|array',
            'total'             => 'required',
            'vehicle_no'      => 'nullable|string|max:50',
            'transport_name'  => 'nullable|string|max:100',
            'reverse_charge'  => 'nullable|in:Yes,No',
            'gr_rr_no'        => 'nullable|string|max:50',
            'station'         => 'nullable|string|max:100',
        ]);

        $companyId = session('user_company_id');
        $userId    = session('user_id');

        DB::beginTransaction();

        try {

            $jobWorkId = DB::table('job_works')->insertGetId([
                'company_id'        => $companyId,
                'series_no'         => $request->series_no,
                'job_work_type'     => 'IN',
                'job_work_out_id'   => $request->job_work_out_id,
                'date'              => $request->date,

                'voucher_no_prefix' => $request->voucher_prefix ?? null,
                'voucher_no'        => $request->voucher_no,

                'party_id'          => $request->party_id,
                'material_center'   => $request->material_center,
                'total'             => $request->total,

                'vehicle_no'        => $request->vehicle_no,
                'transport_name'    => $request->transport_name,
                'reverse_charge'    => $request->reverse_charge,
                'gr_rr_no'          => $request->gr_rr_no,
                'station'           => $request->station,

                'status'            => 1,
                'delete'            => 0,
                'created_by'        => $userId,
                'created_at'        => now(),
            ]);

            foreach ($request->goods_discription as $i => $value) {

                if (!$value || !$request->qty[$i] || !$request->price[$i]) {
                    continue;
                }

                $qty    = (float) $request->qty[$i];
                $price  = (float) $request->price[$i];
                $amount = $qty * $price;

                if ($request->job_work_out_id) {

                    $outRow = DB::table('job_work_descriptions')
                        ->where('id', $value)
                        ->where('delete', 0)
                        ->first();

                    if (!$outRow) {
                        continue;
                    }

                    $itemId = $outRow->goods_discription;
                    $jwOutDescId = $value;

                } 

                else {

                    $itemId = $value;
                    $jwOutDescId = null;
                }

                $descId = DB::table('job_work_descriptions')->insertGetId([
                    'job_work_id'           => $jobWorkId,
                    'goods_discription'     => $itemId,
                    'jw_out_description_id' => $jwOutDescId,
                    'item_description'      => $request->item_description[$i] ?? null,
                    'qty'                   => $qty,
                    'unit'                  => $request->units[$i],
                    'price'                 => $price,
                    'amount'                => $amount,
                    'company_id'            => $companyId,
                    'status'                => 1,
                    'delete'                => 0,
                    'created_by'            => $userId,
                    'created_at'            => now(),
                ]);

            }

            if ($request->job_work_out_id) {

                $outId = $request->job_work_out_id;

                $outRows = DB::table('job_work_descriptions')
                    ->where('job_work_id', $outId)
                    ->where('status', 1)   // only active
                    ->where('delete', 0)
                    ->get();

                $allCompleted = true;

                foreach ($outRows as $out) {

            $totalOutQty = (float) $out->qty;

            $totalInQty = DB::table('job_work_descriptions')
                ->where('jw_out_description_id', $out->id)
                ->where('delete', 0)
                ->sum('qty');

            if ($totalInQty >= $totalOutQty) {
                DB::table('job_work_descriptions')
                    ->where('id', $out->id)
                    ->update(['status' => 2]);
            } else {
                $allCompleted = false;

                DB::table('job_work_descriptions')
                    ->where('id', $out->id)
                    ->update(['status' => 1]);
            }


                }

                if ($allCompleted) {
                    DB::table('job_works')
                        ->where('id', $outId)
                        ->update(['status' => 2]);
                }
            }

            DB::commit();

            $type = $request->page_type ?? 'raw';

            return redirect()
                ->route($type == 'raw' ? 'jobworkin.raw' : 'jobworkin.finished')
                        ->with('success', 'Job Work IN saved successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Error saving Job Work IN: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $type = request()->route()->defaults['type'] ?? 'raw';
        $companyId = Session::get('user_company_id');
        $financial_year = Session::get('default_fy');
        [$startYY, $endYY] = explode('-', $financial_year);
        $fy_start_date = '20' . $startYY . '-04-01';
        $fy_end_date   = '20' . $endYY   . '-03-31';
        $jobWork = DB::table('job_works')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('job_work_type', 'IN')
            ->where('delete', 0)
            ->first();

        if (!$jobWork) {
            abort(404);
        }

        $jobWorkDescriptions = DB::table('job_work_descriptions')
            ->where('job_work_id', $id)
            ->where('company_id', $companyId)
            ->where('delete', 0)
            ->select(
                'id',
                'goods_discription as item_id',
                'jw_out_description_id',  
                'item_description',
                'qty',
                'unit',
                'price',
                'amount'
            )
            ->get();


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

        $party_list = \App\Models\Accounts::leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->whereIn('accounts.company_id', [$companyId, 0])
            ->whereIn('accounts.under_group', $groups)
            ->select(
                'accounts.id',
                'accounts.account_name',
                'accounts.gstin',
                'accounts.address',
                'accounts.pin_code',
                'states.state_code'
            )
            ->orderBy('accounts.account_name')
            ->get();

        $items = DB::table('manage_items')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '0')
            ->where('manage_items.status', '1')
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
        $companyData = Companies::where('id', $companyId)->first();

            if ($companyData->gst_config_type == "single_gst") {
                $GstSettings = DB::table('gst_settings')
                    ->where([
                        'company_id' => $companyId,
                        'gst_type' => "single_gst"
                    ])
                    ->get();

                $branch = GstBranch::select(
                        'id',
                        'gst_number as gst_no',
                        'branch_matcenter as mat_center',
                        'branch_series as series'
                    )
                    ->where([
                        'delete' => '0',
                        'company_id' => $companyId,
                        'gst_setting_id' => $GstSettings[0]->id ?? null
                    ])
                    ->get();

                if ($branch->count()) {
                    $GstSettings = $GstSettings->merge($branch);
                }

            } else {
                $GstSettings = DB::table('gst_settings_multiple')
                    ->select('id', 'gst_no', 'mat_center', 'series')
                    ->where([
                        'company_id' => $companyId,
                        'gst_type' => "multiple_gst"
                    ])
                    ->get();

                foreach ($GstSettings as $value) {
                    $branch = GstBranch::select(
                            'id',
                            'gst_number as gst_no',
                            'branch_matcenter as mat_center',
                            'branch_series as series'
                        )
                        ->where([
                            'delete' => '0',
                            'company_id' => $companyId,
                            'gst_setting_multiple_id' => $value->id
                        ])
                        ->get();

                    if ($branch->count()) {
                        $GstSettings = $GstSettings->merge($branch);
                    }
                }
            }

        if (!$companyData->gst_config_type || !$GstSettings || $GstSettings->isEmpty()) {
            return redirect()->route(
                $type == 'raw' ? 'jobworkin.raw.index' : 'jobworkin.finished.index'
            )
                ->with('error', 'Please Enter GST Configuration!');
        }

        foreach ($GstSettings as $key => $value) {
            $config = VoucherSeriesConfiguration::where('company_id', $companyId)
                ->where('series', $value->series)
                ->where('configuration_for', $type === 'finished'
    ? 'JOB WORK FINISHED GOODS'
    : 'JOB WORK RAW MATERIAL')
                ->where('status', 1)
                ->first();

            if (!$config) {
                continue;
            }

            $voucher_no = DB::table('job_works')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$fy_start_date, $fy_end_date])
                ->where('series_no', $value->series)
                ->where('delete', 0)
                ->max(DB::raw("CAST(voucher_no AS SIGNED)"));

            $value->invoice_start_from = $voucher_no
                ? sprintf("%'03d", $voucher_no + 1)
                : sprintf("%'03d", $config->invoice_start ?? 1);

            $invoice_prefix = "";
            $manual = ($config->manual_numbering == "YES") ? "1" : "0";

            if ($manual == "0") {
                if ($config->prefix == "ENABLE" && $config->prefix_value) {
                    $invoice_prefix .= $config->prefix_value;
                    $invoice_prefix .= $config->separator_1 ?? '';
                }

                if ($config->year == "PREFIX TO NUMBER") {
                    $invoice_prefix .= Session::get('default_fy');
                    $invoice_prefix .= $config->separator_2 ?? '';
                }

                $invoice_prefix .= $value->invoice_start_from;

                if ($config->suffix == "ENABLE" && $config->suffix_value) {
                    $invoice_prefix .= $config->separator_3 ?? '';
                    $invoice_prefix .= $config->suffix_value;
                }
            }

            $value->manual_enter_invoice_no = $manual;
            $value->duplicate_voucher = $config->duplicate_voucher;
            $value->blank_voucher = $config->blank_voucher;
            $value->invoice_prefix = $invoice_prefix;
        }

        $GstSettings = $GstSettings->values();

        return view('JobWorkIn.edit', compact(
            'type',
            'jobWork',
            'jobWorkDescriptions',
            'party_list',
            'items',
            'GstSettings',
        ));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $companyId = Session::get('user_company_id');

        DB::beginTransaction();

        try {

            $request->validate([
                'series_no'          => 'required',
                'date'               => 'required',
                'voucher_no'         => 'required',
                'party_id'           => 'required',
                'material_center'    => 'required',
                'goods_discription'  => 'required|array|min:1',
                'item_description'   => 'nullable|array',
                'qty'                => 'required|array',
                'units'              => 'required|array',
                'price'              => 'required|array',
                'total'              => 'required',
            ]);


            DB::table('job_works')
                ->where('id', $id)
                ->where('company_id', $companyId)
                ->where('job_work_type', 'IN')
                ->update([
                    'series_no'        => $request->series_no,
                    'date'             => $request->date,
                    'voucher_no'       => $request->voucher_no,
                    'party_id'         => $request->party_id,
                    'job_work_out_id'  => $request->job_work_out_id,
                    'material_center'  => $request->material_center,
                    'total'            => $request->total,
                    'vehicle_no'       => $request->vehicle_no,
                    'transport_name'   => $request->transport_name,
                    'reverse_charge'   => $request->reverse_charge,
                    'gr_rr_no'         => $request->gr_rr_no,
                    'station'          => $request->station,
                    'updated_by'       => Session::get('user_id'),
                    'updated_at'       => now(),
                ]);

            

            $existingDescriptions = DB::table('job_work_descriptions')
                ->where('job_work_id', $id)
                ->where('company_id', $companyId)
                ->where('delete', 0)
                ->get()
                ->keyBy('id');


            $usedItemIds = [];

            foreach ($request->goods_discription as $index => $value) {

                if ($request->job_work_out_id) {

                    $outRow = DB::table('job_work_descriptions')
                        ->where('id', $value)
                        ->where('delete', 0)
                        ->first();

                    if ($outRow) {
                        $itemId    = $outRow->goods_discription;
                        $jwOutDesc = $value;
                    } else {
                        // fallback if edit sends item_id
                        $itemId    = $value;
                        $jwOutDesc = null;
                    }
                }
                else {

                    $itemId    = $value;              
                    $mapKey    = $value;
                    $jwOutDesc = null;
                }

                $qty    = (float) $request->qty[$index];
                $price  = (float) $request->price[$index];
                $amount = $qty * $price;
                $unit   = $request->units[$index];

                $incomingDescId = $request->desc_id[$index] ?? null;

                if ($incomingDescId && isset($existingDescriptions[$incomingDescId])) {

                    DB::table('job_work_descriptions')
                        ->where('id', $incomingDescId)
                        ->update([
                            'goods_discription' => $itemId,
                            'item_description' => $request->item_description[$index] ?? null,
                            'qty'              => $qty,
                            'unit'             => $unit,
                            'price'            => $price,
                            'amount'           => $amount,
                            'updated_by'       => Session::get('user_id'),
                            'updated_at'       => now(),
                        ]);

                    $usedItemIds[] = $incomingDescId;

                } else {

                    $newId = DB::table('job_work_descriptions')->insertGetId([
                        'job_work_id'           => $id,
                        'goods_discription'     => $itemId,
                        'jw_out_description_id' => $jwOutDesc,
                        'item_description'      => $request->item_description[$index] ?? null,
                        'qty'                   => $qty,
                        'unit'                  => $unit,
                        'price'                 => $price,
                        'amount'                => $amount,
                        'company_id'            => $companyId,
                        'status'                => 1,
                        'delete'                => 0,
                        'created_by'            => Session::get('user_id'),
                        'created_at'            => now(),
                    ]);

                    $usedItemIds[] = $newId;
                }

            }

            $submittedDescIds = collect($request->desc_id ?? [])
                ->filter()
                ->map(fn ($v) => (int) $v)
                ->values();

            $itemsToDelete = collect($existingDescriptions)
                ->keys()
                ->diff($submittedDescIds);

            if ($itemsToDelete->isNotEmpty()) {
                DB::table('job_work_descriptions')
                    ->where('job_work_id', $id)
                    ->whereIn('id', $itemsToDelete)
                    ->update([
                        'status' => 0,
                        'delete' => 1,
                    ]);
            }
            if ($request->job_work_out_id) {

                $outId = $request->job_work_out_id;

                $outRows = DB::table('job_work_descriptions')
                    ->where('job_work_id', $outId)
                    ->where('delete', 0)
                    ->get();

                $allCompleted = true;

                foreach ($outRows as $out) {

                    $totalOutQty = (float) $out->qty;

                    $totalInQty = DB::table('job_work_descriptions')
                        ->where('jw_out_description_id', $out->id)
                        ->where('delete', 0)
                        ->sum('qty');

                    if ($totalInQty >= $totalOutQty) {
                        DB::table('job_work_descriptions')
                            ->where('id', $out->id)
                            ->update(['status' => 2]);
                    } else {
                        $allCompleted = false;

                        DB::table('job_work_descriptions')
                            ->where('id', $out->id)
                            ->update(['status' => 1]);
                    }


                }

                DB::table('job_works')
                    ->where('id', $outId)
                    ->update([
                        'status' => $allCompleted ? 2 : 1
                    ]);
            }


            DB::commit();

            $type = $request->page_type ?? 'raw';

            return redirect()
                ->route($type == 'raw' ? 'jobworkin.raw' : 'jobworkin.finished')
                ->with('success', 'Job Work IN updated successfully');

                } catch (\Throwable $e) {
                    DB::rollBack();

                return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
    public function delete(Request $request)
    {
        $jobWorkId = $request->job_work_id;
        $companyId = Session::get('user_company_id');

        DB::transaction(function () use ($jobWorkId, $companyId) {

            DB::table('job_works')
                ->where('id', $jobWorkId)
                ->where('company_id', $companyId)
                ->update([
                    'status' => 0,
                    'delete' => 1,
                ]);

            DB::table('job_work_descriptions')
                ->where('job_work_id', $jobWorkId)
                ->where('company_id', $companyId)
                ->update([
                    'status' => 0,
                    'delete' => 1,
                ]);

            
        });

        $type = request('type', 'raw');

        return redirect()
            ->route($type == 'raw' ? 'jobworkin.raw' : 'jobworkin.finished')
            ->with('success', 'Job Work IN deleted successfully.');
    }

    public function view($id)
    {
        $companyId = Session::get('user_company_id');

        $jobWork = DB::table('job_works')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('job_work_type', 'IN')
            ->where('delete', 0)
            ->first();

        if (!$jobWork) {
            abort(404);
        }


        $jobWorkDescriptions = DB::table('job_work_descriptions')
            ->where('job_work_id', $id)
            ->where('company_id', $companyId)
            ->where('delete', 0)
            ->select(
                'id',
                'goods_discription as item_id',
                'item_description',
                'qty',
                'unit',
                'price',
                'amount'
            )
            ->get();

        

        foreach ($jobWorkDescriptions as $desc) {
            $sizes = [];

            if (isset($sizeStocks[$desc->id])) {
                foreach ($sizeStocks[$desc->id] as $row) {
                    $sizes[] = [
                        'id'      => $row->id,      
                        'size'    => $row->size,
                        'reel_no' => $row->reel_no,
                        'weight'  => $row->weight,
                        'status'  => (int) $row->status,
                    ];
                }
            }

            $desc->item_size_info = json_encode($sizes);
        }

        $party = DB::table('accounts')
            ->leftJoin('states', 'accounts.state', '=', 'states.id')
            ->where('accounts.id', $jobWork->party_id)
            ->select(
                'accounts.account_name',
                'accounts.gstin',
                'accounts.address',
                'accounts.pin_code',
                'states.state_code'
            )
            ->first();

        return view('JobWorkIn.view', compact(
            'jobWork',
            'jobWorkDescriptions',
            'party'
        ));
    }
    public function checkVoucher(Request $request)
    {
        $request->validate([
            'voucher_no' => 'required',
            'party_id'   => 'required',
            'series_no'  => 'required',
        ]);

        $query = \DB::table('job_works')
            ->where('voucher_no', $request->voucher_no)
            ->where('series_no', $request->series_no)
            ->where('party_id', $request->party_id)
            ->where('job_work_type', 'IN')
            ->where('delete', 0);

        if ($request->id) {
            $query->where('id', '!=', $request->id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }
    public function getOutVouchers(Request $request)
    {
        $companyId = Session::get('user_company_id');

        $query = DB::table('job_works')
            ->where('company_id', $companyId)
            ->where('job_work_type', 'OUT')
            ->whereNull('job_work_in_id') 
            ->where('party_id', $request->party_id)
            ->where('delete', 0);

        if ($request->filled('current_out_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('status', 1)
                ->orWhere('id', $request->current_out_id);
            });
        } 
        else {
            $query->where('status', 1);
        }

        $vouchers = $query
            ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC')
            ->select('id', 'voucher_no_prefix')
            ->get();

        return response()->json($vouchers);
    }

    public function getOutItems(Request $request)
    {
        $outId = $request->job_work_out_id;
        $companyId = Session::get('user_company_id');


        $rows = DB::table('job_work_descriptions as d')
            ->join('manage_items as i', 'i.id', '=', 'd.goods_discription')
            ->join('item_groups as g', 'g.id', '=', 'i.g_name')
            ->join('units as u', 'u.id', '=', 'i.u_name')

            ->leftJoin('job_work_descriptions as in_d', function ($join) {
                $join->on('in_d.jw_out_description_id', '=', 'd.id')
                    ->where('in_d.delete', 0);
            })

            ->where('d.job_work_id', $outId)
            ->where('d.delete', 0)

            ->groupBy(
                'd.id',
                'd.qty',
                'i.id',
                'i.name',
                'u.s_name',
                'g.parameterized_stock_status'
            )

            ->select(
                'd.id as out_desc_id',
                'i.id as item_id',
                'i.name as item_name',
                'u.s_name as unit',
                DB::raw('
                    (d.qty - IFNULL(SUM(in_d.qty), 0)) as pending_qty
                '),
                'g.parameterized_stock_status'
            )

            ->havingRaw('(d.qty - IFNULL(SUM(in_d.qty), 0)) > 0')

            ->get();

        return response()->json($rows);
    }


}
