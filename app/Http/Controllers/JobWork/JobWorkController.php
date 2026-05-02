<?php

namespace App\Http\Controllers\JobWork;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\MerchantModuleMapping;
use App\Models\JobWork;
use App\Models\GstBranch;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\JobWorkInvoiceConfiguration;
use App\Models\JobWorkInvoiceTermCondition;
use App\Models\Bank;
use App\Models\Accounts;
use Session;
use DB;

class JobWorkController extends Controller
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
            session(['jobwork_from_date' => $from_date, 'jobwork_to_date' => $to_date]);
        } elseif (session()->has('jobwork_from_date') && session()->has('jobwork_to_date')) {
            $from_date = session('jobwork_from_date');
            $to_date   = session('jobwork_to_date');
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
            ->where('job_works.job_work_type', 'OUT')
            ->where('job_works.delete', 0);

        // RAW vs FINISHED FILTER
        if($type == 'raw'){
            $query->whereNull('job_works.job_work_in_id');
        }

        if($type == 'finished'){
            $query->whereNotNull('job_works.job_work_in_id');
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
            ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(voucher_no_prefix, '/', -1) AS UNSIGNED)"), 'ASC');
        } else {
            $query->orderBy('job_works.date', 'DESC')
                ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(voucher_no_prefix, '/', -1) AS UNSIGNED)"), 'DESC')
                ->limit(10);
        }

        $jobWorks = $query->get();

        if (!$from_date && !$to_date) {
            $jobWorks = $jobWorks->reverse()->values();
        }

        return view('JobWork.index')
            ->with('jobWorks', $jobWorks)
            ->with('from_date', $from_date)
            ->with('to_date', $to_date)
            ->with('type', $type);
    }

    public function create()
    {
        $type = request()->route()->defaults['type'] ?? 'raw';
        $companyId = Session::get('user_company_id');
        $financial_year = Session::get('default_fy');

        [$startYY, $endYY] = explode('-', $financial_year);
        $fy_start_date = '20' . $startYY . '-04-01';
        $fy_end_date   = '20' . $endYY   . '-03-31';

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
                'accounts.pan',
                'states.state_code'
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

        $itemGroups = \DB::table('item_groups')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('group_name')
            ->get();

        $units = \DB::table('units')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('name')
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

            if($type === 'finished'){
                return redirect()->route('jobwork.out.finished')
                    ->with('error', 'Please Enter GST Configuration!');
            }

            return redirect()->route('jobwork.out.raw')
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
                $value->invoice_start_from = "";
                continue;
            }
            $voucher_no = DB::table('job_works')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$fy_start_date, $fy_end_date]) 
                ->where('series_no', $value->series)
                ->where('job_work_type', 'OUT') 
                ->where('delete', 0)
                ->where(function ($q) use ($type) {
                    if ($type === 'finished') {
                        $q->whereNotNull('job_work_in_id'); // FINISHED
                    } else {
                        $q->whereNull('job_work_in_id'); // RAW
                    }
                })
                ->max(DB::raw("CAST(voucher_no AS UNSIGNED)")); 

            $next = $voucher_no ? $voucher_no + 1 : ($config->invoice_start ?? 1);

            $value->invoice_start_from = str_pad(
                $next,
                $config->number_digit ?? 3,
                '0',
                STR_PAD_LEFT
            );

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


        return view('JobWork.create')->with([
            'type' => $type,
            'fy_start_date'            => $fy_start_date,
            'fy_end_date'              => $fy_end_date,
            'job_work_date'            => $job_work_date,
            'party_list'               => $party_list,
            'items'                    => $items,
            'itemGroups'               => $itemGroups,
            'units'                    => $units,
            'transport'                => $transport,
            'GstSettings'   => $GstSettings,
        ]);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'party_id' => 'required',

            'job_work_in_id' => $request->page_type === 'finished'
                ? 'required|exists:job_works,id'
                : 'nullable',

            'in_desc_id' => 'nullable|array',
            'material_center' => 'required',
            'total' => 'required',

            'goods_discription' => 'required|array|min:1',
            'qty' => 'required|array|min:1',
            'units' => 'required|array|min:1',
            'price' => 'required|array|min:1',

            'vehicle_no' => 'nullable|string|max:50',
            'transport_name' => 'nullable|string|max:100',
            'reverse_charge' => 'nullable|in:Yes,No',
            'gr_rr_no' => 'nullable|string|max:50',
            'station' => 'nullable|string|max:100',
        ]);

        $companyId = Session::get('user_company_id');

        $duplicate = \DB::table('job_works')
            ->where('company_id', $companyId)
            ->where('job_work_type', 'OUT')
            ->where('series_no', $request->series_no)
            ->where('voucher_no_prefix', $request->voucher_no)
            ->where('delete', '0')
            ->first();

        if ($duplicate) {
            return back()->withErrors(['voucher_no' => 'Duplicate Voucher No']);
        }

        $config = \App\Models\VoucherSeriesConfiguration::where('company_id', $companyId)
            ->where('series', $request->series_no)
            ->where('configuration_for', $request->page_type === 'finished'
    ? 'JOB WORK FINISHED GOODS'
    : 'JOB WORK RAW MATERIAL')
            ->where('status', 1)
            ->first();

        $manual = ($config && $config->manual_numbering == "YES");

        $type = $request->page_type;
        $series = $request->series_no;

        if (!$manual) {

            $last = DB::table('job_works')
                ->where('company_id', $companyId)
                ->where('series_no', $series)
                ->where('job_work_type', 'OUT') 
                ->where('delete', 0)
                ->where(function ($q) use ($type) {
                    if ($type === 'finished') {
                        $q->whereNotNull('job_work_in_id'); 
                    } else {
                        $q->whereNull('job_work_in_id'); 
                    }
                })
                ->max(DB::raw("CAST(voucher_no AS UNSIGNED)"));

            $next = $last ? $last + 1 : ($config->invoice_start ?? 1);

            $voucherNo = str_pad(
                $next,
                $config->number_digit ?? 3,
                '0',
                STR_PAD_LEFT
            );

        } else {
            $voucherNo = $request->voucher_no;
        }
        $voucherPrefix = $voucherNo;

        if (!$manual) {

            $prefix = '';

            if ($config->prefix == "ENABLE") {
                $prefix .= $config->prefix_value;
                $prefix .= $config->separator_1 ?? '';
            }

            if ($config->year == "PREFIX TO NUMBER") {
                $prefix .= Session::get('default_fy');
                $prefix .= $config->separator_2 ?? '';
            }

            $prefix .= $voucherNo;

            if ($config->suffix == "ENABLE") {
                $prefix .= ($config->separator_3 ?? '') . $config->suffix_value;
            }

            $voucherPrefix = $prefix;
        }
        $account = Accounts::select('id', 'account_name', 'address', 'pin_code', 'gstin', 'pan','state')
                            ->where('id', $request->party_id)
                            ->first();
        $jobWorkId = DB::table('job_works')->insertGetId([
            'company_id'        => $companyId,
            'job_work_type'     => 'OUT', 
            'series_no'         => $request->series_no,
            'date'              => $request->date,
            'voucher_no_prefix' => $voucherPrefix,
            'voucher_no'        => $voucherNo,
            'party_id'          => $request->party_id,   
            'material_center'   => $request->material_center,
            'total'             => $request->total,
            'job_work_in_id' => $request->job_work_in_id ?? null,
            'vehicle_no'        => $request->vehicle_no,
            'transport_name'    => $request->transport_name,
            'reverse_charge'    => $request->reverse_charge,
            'gr_rr_no'          => $request->gr_rr_no,
            'station'           => $request->station,
            'shipping_name'     => $request->shipping_name ?: null,
            'shipping_address'  => $request->shipping_address ?: null,
            'shipping_pincode'  => $request->shipping_pincode ?: null,
            'shipping_gst'      => $request->shipping_gst ?: null,
            'shipping_pan'      => $request->shipping_pan ?: null,
            'shipping_state'    => $request->shipping_state ?: null,
            'billing_name'     => $account->account_name ?: null,
            'billing_address'  => $account->address ?: null,
            'billing_pincode'  => $account->pin_code ?: null,
            'billing_gst'      => $account->gstin ?: null,
            'billing_pan'      => $account->pan ?: null,
            'billing_state'    => $account->state ?: null,
            'merchant_gst'      => $request->merchant_gst ?: null,
            'status'            => 1,
            'delete'            => 0,
            'created_by'        => Session::get('user_id'),
            'created_at'        => now(),
        ]);

        $description_lines = $request->description_lines ?? [];
        foreach ($request->goods_discription as $key => $itemId) {

            if (
                empty($itemId) ||
                empty($request->qty[$key]) ||
                empty($request->units[$key]) ||
                empty($request->price[$key])
            ) {
                continue;
            }

            $qty    = (float) $request->qty[$key];
            $price  = (float) $request->price[$key];
            $amount = $qty * $price;

            $inDescId = $request->in_desc_id[$key] ?? null;

            $detailId = DB::table('job_work_descriptions')->insertGetId([
                'job_work_id'          => $jobWorkId,
                'goods_discription'    => $itemId,      // item_id
                'jw_in_description_id' => $inDescId,    // IN desc id
                'qty'                  => $qty,
                'unit'                 => $request->units[$key],
                'price'                => $price,
                'amount'               => $amount,
                'company_id'           => $companyId,
                'status'               => 1,
                'delete'               => 0,
                'created_by'           => Session::get('user_id'),
                'created_at'           => now(),
            ]);
            if (isset($description_lines[$key]) && is_array($description_lines[$key])) {

                foreach ($description_lines[$key] as $lineIndex => $lineText) {

                    if (!empty($lineText)) {

                        DB::table('jobwork_description_lines')->insert([
                            'job_work_id'         => $jobWorkId,
                            'job_work_detail_id'  => $detailId,
                            'line_text'           => $lineText,
                            'sort_order'          => $lineIndex + 1,
                            'company_id'          => $companyId,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }
                }
            }
        }



        if (!empty($request->job_work_in_id)) {

            $inId = $request->job_work_in_id;

            $inRows = DB::table('job_work_descriptions')
                ->where('job_work_id', $inId)
                ->where('delete', 0)
                ->get();

            $allCompleted = true;

            foreach ($inRows as $inRow) {

                $usedQty = DB::table('job_work_descriptions')
                    ->where('jw_in_description_id', $inRow->id)
                    ->where('delete', 0)
                    ->sum('qty');

                if ($usedQty >= $inRow->qty) {
                    DB::table('job_work_descriptions')
                        ->where('id', $inRow->id)
                        ->update([
                            'status' => 2,
                            'updated_at' => now(),
                        ]);
                } else {
                    $allCompleted = false;
                    DB::table('job_work_descriptions')
                        ->where('id', $inRow->id)
                        ->update([
                            'status' => 1,
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('job_works')
                ->where('id', $inId)
                ->update([
                    'status' => $allCompleted ? 2 : 1,
                    'updated_at' => now(),
                ]);
        }

        if (!empty($request->job_work_in_id)) {

            $inId = $request->job_work_in_id;

            $pendingCount = DB::table('job_work_descriptions')
                ->where('job_work_id', $inId)
                ->where('delete', 0)
                ->where('status', 1)   
                ->count();

            if ($pendingCount === 0) {
                DB::table('job_works')
                    ->where('id', $inId)
                    ->update([
                        'status' => 2,   
                        'updated_at' => now(),
                    ]);
            }
        }

        if($request->page_type === 'finished'){
            return redirect()->route('jobwork.out.finished')
                ->withSuccess('Job Work saved');
        }else{
            return redirect()->route('jobwork.out.raw')
                ->withSuccess('Job Work saved');
        }
    }

    public function edit($id)
    {
        $type = request()->route()->defaults['type'] ?? 'raw';
        $companyId = Session::get('user_company_id');

        $jobWork = DB::table('job_works')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('delete', '0')
            ->first();

        if (!$jobWork) {
            abort(404);
        }

        $jobWorkDescriptions = DB::table('job_work_descriptions')
            ->where('job_work_id', $id)
            ->where('company_id', $companyId)
            ->where('delete', '0')
            ->select([
                'id',
                'goods_discription as item_id',
                'jw_in_description_id',  
                'item_description',
                'qty',
                'unit',
                'price',
                'amount'
            ])
            ->get();

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
                'accounts.pan',
                'states.state_code'
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

        $itemGroups = \DB::table('item_groups')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('group_name')
            ->get();

        $units = \DB::table('units')
            ->where('delete', '0')
            ->where('company_id', $companyId)
            ->orderBy('name')
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
            if($type === 'finished'){
                return redirect()->route('jobwork.out.finished')
                    ->with('error', 'Please Enter GST Configuration!');
            }

            return redirect()->route('jobwork.out.raw')
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
                $value->invoice_start_from = "";
                continue;
            }

            $manual = ($config->manual_numbering == "YES") ? "1" : "0";

            if ($jobWork->series_no == $value->series) {

                $value->invoice_start_from = $jobWork->voucher_no;

            } else {

                $last = DB::table('job_works')
                    ->where('company_id', $companyId)
                    ->where('series_no', $value->series)
                    ->where('job_work_type', 'OUT') // IMPORTANT
                    ->where('delete', 0)
                    ->where(function ($q) use ($type) {
                        if ($type === 'finished') {
                            $q->whereNotNull('job_work_in_id'); // FINISHED
                        } else {
                            $q->whereNull('job_work_in_id'); // RAW
                        }
                    })
                    ->max(DB::raw("CAST(voucher_no AS UNSIGNED)"));

                $next = $last ? $last + 1 : ($config->invoice_start ?? 1);

                $value->invoice_start_from = str_pad(
                    $next,
                    $config->number_digit ?? 3,
                    '0',
                    STR_PAD_LEFT
                );
            }

            $invoice_prefix = "";

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

            $value->invoice_prefix = $invoice_prefix;
            $value->manual_enter_invoice_no = $manual;
            $value->duplicate_voucher = $config->duplicate_voucher;
            $value->blank_voucher = $config->blank_voucher;
        }

        $GstSettings = $GstSettings->values();

        $transport = [
            'vehicle_no'     => $jobWork->vehicle_no ?? '',
            'transport_name' => $jobWork->transport_name ?? '',
            'reverse_charge' => $jobWork->reverse_charge ?? '',
            'gr_rr_no'       => $jobWork->gr_rr_no ?? '',
            'station'        => $jobWork->station ?? '',
        ];

        $selectedInId = $jobWork->job_work_in_id;
        $inVouchers = DB::table('job_works')
            ->where('company_id', $companyId)
            ->where('party_id', $jobWork->party_id)
            ->where('job_work_type', 'IN')
            ->where('delete', 0)
            ->select('id', 'voucher_no')
            ->orderBy('voucher_no')
            ->get();
        $jobWorkDescLines = DB::table('jobwork_description_lines')
            ->where('job_work_id', $id)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('job_work_detail_id');
        return view('JobWork.edit')->with([
            'type'              => $type,
            'jobWork'           => $jobWork,
            'jobWorkDescriptions' => $jobWorkDescriptions,
            'fy_start_date'     => $fy_start_date,
            'fy_end_date'       => $fy_end_date,
            'party_list'        => $party_list,
            'items'             => $items,
            'itemGroups'        => $itemGroups,
            'units'             => $units,
            'transport'         => $transport,
            'jobWorkDescLines'         => $jobWorkDescLines,
            'GstSettings'       => $GstSettings,
            'inVouchers'        => $inVouchers,
            'selectedInId'      => $selectedInId,
        ]);
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'party_id' => 'required',
            'material_center' => 'required',
            'total' => 'required',
            'goods_discription' => 'required|array|min:1',
            'qty' => 'required|array|min:1',
            'units' => 'required|array|min:1',
            'price' => 'required|array|min:1',
            'vehicle_no'      => 'nullable|string|max:50',
            'transport_name'  => 'nullable|string|max:100',
            'reverse_charge'  => 'nullable|in:Yes,No',
            'gr_rr_no'        => 'nullable|string|max:50',
            'station'         => 'nullable|string|max:100',
            'shipping_name'     => 'nullable|exists:accounts,id',
            'shipping_address'  => 'nullable|string|max:255',
            'shipping_pincode'  => 'nullable|string|max:10',
            'shipping_gst'      => 'nullable|string|max:20',
            'shipping_pan'      => 'nullable|string|max:20',
            'shipping_state'    => 'nullable|string|max:10',
        ]);

        $companyId = Session::get('user_company_id');

        $jobWork = DB::table('job_works')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->where('delete', '0')
            ->first();

        if (!$jobWork) {
            return back()->withErrors(['error' => 'Job Work not found']);
        }

        $duplicate = DB::table('job_works')
        ->where('company_id', $companyId)
        ->where('job_work_type', 'OUT')
        ->where('series_no', $request->series_no)
        ->where('voucher_no_prefix', $request->voucher_no)
        ->where('id','!=',$id)
        ->where('delete', '0')
        ->first();
        if ($duplicate) {
            return back()->withErrors(['voucher_no' => 'Duplicate Voucher No']);
        }


        $config = \App\Models\VoucherSeriesConfiguration::where('company_id', $companyId)
            ->where('series', $request->series_no)
            ->where('configuration_for', $request->page_type === 'finished'
                ? 'JOB WORK FINISHED GOODS'
                : 'JOB WORK RAW MATERIAL')
            ->where('status', 1)
            ->first();

        $manual = ($config && $config->manual_numbering == "YES");

        $type = $request->page_type;
        $series = $request->series_no;

        if ($jobWork->series_no == $series) {

            $voucherNo = $jobWork->voucher_no;
            $voucherPrefix = $jobWork->voucher_no_prefix;

        } else {

            if (!$manual) {

                $last = DB::table('job_works')
                    ->where('company_id', $companyId)
                    ->where('series_no', $series)
                    ->where('job_work_type', 'OUT')
                    ->where('delete', 0)
                    ->where(function ($q) use ($type) {
                        if ($type === 'finished') {
                            $q->whereNotNull('job_work_in_id');
                        } else {
                            $q->whereNull('job_work_in_id');
                        }
                    })
                    ->max(DB::raw("CAST(voucher_no AS UNSIGNED)"));

                $next = $last ? $last + 1 : ($config->invoice_start ?? 1);

                $voucherNo = str_pad(
                    $next,
                    $config->number_digit ?? 3,
                    '0',
                    STR_PAD_LEFT
                );

            } else {
                $voucherNo = $request->voucher_no;
            }

            $voucherPrefix = $voucherNo;

            if (!$manual) {

                $prefix = '';

                if ($config->prefix == "ENABLE") {
                    $prefix .= $config->prefix_value . ($config->separator_1 ?? '');
                }

                if ($config->year == "PREFIX TO NUMBER") {
                    $prefix .= Session::get('default_fy') . ($config->separator_2 ?? '');
                }

                $prefix .= $voucherNo;

                if ($config->suffix == "ENABLE") {
                    $prefix .= ($config->separator_3 ?? '') . $config->suffix_value;
                }

                $voucherPrefix = $prefix;
            }
        }
        $account = Accounts::select('id', 'account_name', 'address', 'pin_code', 'gstin', 'pan','state')
                            ->where('id', $request->party_id)
                            ->first();
        DB::table('job_works')
            ->where('id', $id)
            ->update([
                'series_no'          => $request->series_no,
                'date'               => $request->date,
                'voucher_no'         => $voucherNo,
                'voucher_no_prefix'  => $voucherPrefix,
                    'party_id'           => $request->party_id,
                    'material_center'    => $request->material_center,
                    'total'              => $request->total,
                    'vehicle_no'         => $request->vehicle_no,
                    'transport_name'     => $request->transport_name,
                    'reverse_charge'     => $request->reverse_charge,
                    'gr_rr_no'           => $request->gr_rr_no,
                    'station'            => $request->station,
                    'shipping_name'     => $request->shipping_name ?: null,
                    'shipping_address'  => $request->shipping_address ?: null,
                    'shipping_pincode'  => $request->shipping_pincode ?: null,
                    'shipping_gst'      => $request->shipping_gst ?: null,
                    'shipping_pan'      => $request->shipping_pan ?: null,
                    'shipping_state'    => $request->shipping_state ?: null,
                    'billing_address'  => $account->address ?: null,
                    'billing_name'     => $account->account_name ?: null,
                    'billing_pincode'  => $account->pin_code ?: null,
                    'billing_gst'      => $account->gstin ?: null,
                    'billing_pan'      => $account->pan ?: null,
                    'billing_state'    => $account->state ?: null,
                    'merchant_gst'      => $request->merchant_gst ?: null,
                    'updated_by'         => Session::get('user_id'),
                    'updated_at'         => now(),
                ]);

        $hasJobWorkIn = !empty($request->job_work_in_id);
            DB::table('job_works')
                ->where('id', $id)
                ->update([
                    'job_work_in_id' => $request->job_work_in_id ?? null
                ]);
            DB::table('job_work_descriptions')
                ->where('job_work_id', $id)
                ->delete();

            DB::table('jobwork_description_lines')
                ->where('job_work_id', $id)
                ->delete();
                $description_lines = $request->description_lines ?? [];

            foreach ($request->goods_discription as $key => $itemId) {

                if (
                    empty($itemId) ||
                    empty($request->qty[$key]) ||
                    empty($request->units[$key]) ||
                    empty($request->price[$key])
                ) {
                    continue;
                }

                $qty    = (float) $request->qty[$key];
                $price  = (float) $request->price[$key];
                $amount = $qty * $price;

                $inDescId = $request->in_desc_id[$key] ?? null;

                $detailId = DB::table('job_work_descriptions')->insertGetId([
                    'job_work_id'          => $id,
                    'goods_discription'    => $itemId,
                    'jw_in_description_id' => $inDescId,
                    'qty'                  => $qty,
                    'unit'                 => $request->units[$key],
                    'price'                => $price,
                    'amount'               => $amount,
                    'company_id'           => $companyId,
                    'status'               => 1,
                    'delete'               => 0,
                    'created_by'           => Session::get('user_id'),
                    'created_at'           => now(),
                ]);

                if (isset($description_lines[$key])) {

                    foreach ($description_lines[$key] as $lineIndex => $lineText) {

                        if (!empty(trim($lineText))) {

                            DB::table('jobwork_description_lines')->insert([
                                'job_work_id'         => $id,
                                'job_work_detail_id'  => $detailId,
                                'line_text'           => $lineText,
                                'sort_order'          => $lineIndex + 1,
                                'company_id'          => $companyId,
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ]);
                        }
                    }
                }
            }
            if ($hasJobWorkIn) {

                $inId = $request->job_work_in_id;

                DB::table('job_work_descriptions')
                    ->where('job_work_id', $inId)
                    ->where('delete', 0)
                    ->update([
                        'status' => 1,
                        'updated_at' => now(),
                    ]);


            if ($hasJobWorkIn) {

                $inId = $request->job_work_in_id;

                $inRows = DB::table('job_work_descriptions')
                    ->where('job_work_id', $inId)
                    ->where('delete', 0)
                    ->get();

                $allCompleted = true;

                foreach ($inRows as $inRow) {

                    $usedQty = DB::table('job_work_descriptions')
                        ->where('jw_in_description_id', $inRow->id)
                        ->where('delete', 0)
                        ->sum('qty');

                    if ($usedQty >= $inRow->qty) {
                        DB::table('job_work_descriptions')
                            ->where('id', $inRow->id)
                            ->update([
                                'status' => 2,
                                'updated_at' => now(),
                            ]);
                    } else {
                        $allCompleted = false;
                        DB::table('job_work_descriptions')
                            ->where('id', $inRow->id)
                            ->update([
                                'status' => 1,
                                'updated_at' => now(),
                            ]);
                    }
                }

                DB::table('job_works')
                    ->where('id', $inId)
                    ->update([
                        'status' => $allCompleted ? 2 : 1,
                        'updated_at' => now(),
                    ]);
            }


            $pendingCount = DB::table('job_work_descriptions')
                ->where('job_work_id', $inId)
                ->where('delete', 0)
                ->where('status', 1)
                ->count();

            DB::table('job_works')
                ->where('id', $inId)
                ->update([
                    'status' => $pendingCount === 0 ? 2 : 1,
                    'updated_at' => now(),
                ]);
        }

        if($request->page_type === 'finished'){
            return redirect()->route('jobwork.out.finished')
                ->withSuccess('Job Work updated successfully!');
        }

        return redirect()->route('jobwork.out.raw')
            ->withSuccess('Job Work updated successfully!');
    }

    public function delete(Request $request)
    {
        $jobWorkId = $request->job_work_id;
        $companyId = Session::get('user_company_id');

        DB::transaction(function () use ($jobWorkId, $companyId) {


            DB::table('job_work_descriptions')
                ->where('job_work_id', $jobWorkId)
                ->update([
                    'status' => 0,
                    'delete' => 1,
                ]);

            DB::table('job_works')
                ->where('id', $jobWorkId)
                ->update([
                    'status' => 0,
                    'delete' => 1,
                ]);
        });

        return redirect()->back()->with('success', 'Job Work deleted successfully');
    }

    public function view($type, $id)
    {
        $jobwork = JobWork::with([
            'descriptions.item',           
            'party'
        ])->findOrFail($id);




        $companyId = Session::get('user_company_id');
        $company_data = Companies::join('states','companies.state','=','states.id')
            ->where('companies.id', $companyId)
            ->select(['companies.*','states.name as sname'])
            ->first();
        
        $configuration = JobWorkInvoiceConfiguration::with(['terms'])
            ->where('company_id', $companyId)
            ->first();
        
        $seller_info = (object)[
            'gst_no' => $company_data->gst ?? '',
            'address' => $company_data->address ?? $company_data->billing_address ?? '',
            'pincode' => $company_data->pin_code ?? '',
            'sname' => $company_data->sname ?? ''
        ];
        $lines = DB::table('jobwork_description_lines')
            ->where('job_work_id', $id)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('job_work_detail_id');

        foreach ($jobwork->descriptions as $desc) {
            $desc->lines = $lines[$desc->id] ?? [];
        }
        $configuration = JobWorkInvoiceConfiguration::with(['terms','bank'])
            ->where('company_id', $companyId)
            ->first();
        return view('JobWork.view', compact('jobwork',  'company_data', 'seller_info',  'configuration', 'type', ));
    }

    public function getItemSizeQuantity(Request $request)
    {
        $companyId = Session::get('user_company_id');
        $jobWorkId = $request->job_work_id;
        $itemId    = $request->item_id; 

        $query = DB::table('item_size_stocks')
            ->where('item_id', $itemId)
            ->where('company_id', $companyId)
            ->where('delete', 0)
            ->where(function ($q) use ($request, $jobWorkId) {

                if (!$request->boolean('include_used_stocks')) {
                    $q->where('status', 1);
                }

                else {
                    $q->where('status', 1)
                    ->orWhere(function ($qq) use ($jobWorkId) {
                        $qq->where('jw_out_id', $jobWorkId);
                    });
                }
            });

        return response()->json(
            $query->get(['id', 'size', 'weight', 'reel_no'])
        );
    }

    public function checkVoucher(Request $request)
    {
        $companyId = Session::get('user_company_id');

        $query = \DB::table('job_works')
            ->where('company_id', $companyId)
            ->where('job_work_type', 'OUT')
            ->where('series_no', $request->series_no)
            ->where('voucher_no_prefix', $request->voucher_no)
            ->where('delete', 0);

        if (!empty($request->edit_id)) {
            $query->where('id', '!=', $request->edit_id);
        }

        return response()->json([
            'exists' => $query->exists()
        ]);
    }
    public function getInVouchers(Request $request)
    {
        $companyId = Session::get('user_company_id');

        return DB::table('job_works')
            ->where('party_id', $request->party_id)
            ->where('company_id', $companyId)
            ->where('job_work_type', 'IN')
            ->whereNull('job_work_out_id')
            ->where('status', 1)  
            ->where('delete', 0)
            ->select('id', 'voucher_no')
            ->orderBy('voucher_no')
            ->get();
    }

    public function getInItems(Request $request)
    {
        $inId = $request->job_work_in_id;

        $rows = DB::table('job_work_descriptions as d')
            ->join('manage_items as i', 'i.id', '=', 'd.goods_discription')
            ->join('item_groups as g', 'g.id', '=', 'i.g_name')
            ->join('units as u', 'u.id', '=', 'i.u_name')
            ->where('d.job_work_id', $inId)
            ->where('d.delete', 0)
            ->where('d.status', 1) 
            ->select(
                'd.id as in_desc_id',
                'i.id as item_id',
                'i.name as item_name',
                'u.s_name as unit',
                'd.qty as original_qty',
                DB::raw('(
                    d.qty - IFNULL((
                        SELECT SUM(od.qty)
                        FROM job_work_descriptions od
                        WHERE od.jw_in_description_id = d.id
                        AND od.delete = 0
                    ), 0)
                ) as pending_qty'),
                'g.parameterized_stock_status'
            )
            ->having('pending_qty', '>', 0)
            ->get();

        return response()->json($rows);
    }
    public function jobWorkInvoiceConfiguration(Request $request)
    {
        $configuration = JobWorkInvoiceConfiguration::with(['terms'])
            ->where('company_id', Session::get('user_company_id'))
            ->first();

        $bank = Bank::where('company_id', Session::get('user_company_id'))
            ->where('status', '1')
            ->where('delete', '0')
            ->get();

        return view('JobWork.invoice_configuration', [
            'jobwork_configuration' => $configuration,
            'banks' => $bank
        ]);
    }
    public function addJobWorkInvoiceConfiguration(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $check_conf = JobWorkInvoiceConfiguration::where('company_id', $company_id)->first();

        if (!$check_conf) {

            // LOGO
            if ($request->company_logo_status == 1 && $request->company_logo) {
                $logo = "logo_" . time() . '.' . $request->company_logo->extension();
                $request->company_logo->move(public_path('images'), $logo);
            } else {
                $logo = "";
            }

            // SIGNATURE
            if ($request->signature_status == 1 && $request->signature) {
                $signature = "signature_" . time() . '.' . $request->signature->extension();
                $request->signature->move(public_path('images'), $signature);
            } else {
                $signature = "";
            }

            $conf = new JobWorkInvoiceConfiguration();

            $conf->company_logo_status = $request->company_logo_status;
            $conf->company_logo = $logo;
            $conf->logo_position_left = $request->logo_position_left ? 1 : 0;
            $conf->logo_position_right = $request->logo_position_right ? 1 : 0;
            $conf->bank_detail_status = $request->bank_detail_status;
            $conf->bank_name = $request->bank_name;
            $conf->term_status = $request->term_status;
            $conf->invoice_header_text = $request->invoice_header_text;
            $conf->purchase_order_status = $request->purchase_order_status;
            $conf->purchase_order_info_show_in_ledger = $request->purchase_order_info_show_in_ledger;
            $conf->show_description = $request->show_description ? 1 : 0;
            $conf->show_item_name = $request->show_item_name ? 1 : 0;
            $conf->company_name_color = $request->company_name_color;
            $conf->company_name_font_size = $request->company_name_font_size;
            $conf->address_color = $request->address_color;
            $conf->signature_status = $request->signature_status;
            $conf->signature = $signature;
            $conf->company_id = $company_id;
            $conf->created_at = now();

            if ($conf->save()) {
                if ($request->terms) {
                    foreach ($request->terms as $value) {
                        if (!empty($value)) {
                            $term = new JobWorkInvoiceTermCondition();
                            $term->parent_id = $conf->id;
                            $term->term = $value;
                            $term->status = 1;
                            $term->company_id = $company_id;
                            $term->save();
                        }
                    }
                }
            }

        } else {


            $logo = $check_conf->company_logo;
            $signature = $check_conf->signature;

            // LOGO UPDATE
            if ($request->company_logo && !empty($request->company_logo)) {
                $logo = "logo_" . time() . '.' . $request->company_logo->extension();
                $request->company_logo->move(public_path('images'), $logo);
            }

            // SIGNATURE UPDATE
            if ($request->signature && !empty($request->signature)) {
                $signature = "signature_" . time() . '.' . $request->signature->extension();
                $request->signature->move(public_path('images'), $signature);
            }

            if ($request->company_logo_status == 0) {
                $logo = "";
            }

            $conf = JobWorkInvoiceConfiguration::find($check_conf->id);

            $conf->company_logo_status = $request->company_logo_status;
            $conf->company_logo = $logo;
            $conf->logo_position_left = $request->logo_position_left ? 1 : 0;
            $conf->logo_position_right = $request->logo_position_right ? 1 : 0;
            $conf->bank_detail_status = $request->bank_detail_status;
            $conf->bank_name = $request->bank_name;
            $conf->term_status = $request->term_status;
            $conf->invoice_header_text = $request->invoice_header_text;
            $conf->purchase_order_status = $request->purchase_order_status;
            $conf->purchase_order_info_show_in_ledger = $request->purchase_order_info_show_in_ledger;
            $conf->show_description = $request->show_description ? 1 : 0;
            $conf->show_item_name = $request->show_item_name ? 1 : 0;
            $conf->company_name_font_size = $request->company_name_font_size;
            $conf->company_name_color = $request->company_name_color;
            $conf->address_color = $request->address_color;
            $conf->signature_status = $request->signature_status;
            $conf->signature = $signature;
            $conf->updated_at = now();

            if ($conf->save()) {

                JobWorkInvoiceTermCondition::where('parent_id', $conf->id)->delete();

                if ($request->terms) {
                    foreach ($request->terms as $value) {
                        if (!empty($value)) {
                            $term = new JobWorkInvoiceTermCondition();
                            $term->parent_id = $conf->id;
                            $term->term = $value;
                            $term->status = 1;
                            $term->company_id = $company_id;
                            $term->save();
                        }
                    }
                }
            }
        }

        return redirect('job-work-invoice-configuration')
            ->withSuccess('Job Work Invoice Configuration Saved Successfully!');
    }

}