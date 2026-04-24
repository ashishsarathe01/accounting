<?php

namespace App\Http\Controllers\PayrollConfiguration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use App\Models\PayrollConfiguration;
use App\Models\Accounts;
use App\Helpers\CommonHelper;

class PayrollConfigurationController extends Controller
{
public function index()
{
    Gate::authorize('action-module', 81);

    $companyId = Session::get('user_company_id');

    // ===== Statutory Root Group (PF / TDS) =====
    $statutory_root_groups = [7];
    $statutory_group_ids = [];

    foreach ($statutory_root_groups as $gid) {
        $statutory_group_ids[] = $gid;
        $statutory_group_ids = array_merge(
            $statutory_group_ids,
            CommonHelper::getAllChildGroupIds($gid, $companyId)
        );
    }

    $statutory_group_ids = array_unique($statutory_group_ids);

    // ===== Accounts List =====
    $statutory_account_list = Accounts::whereIn('company_id', [$companyId, 0])
        ->where('delete', '0')
        ->where('status', '1')
        ->whereIn('under_group', $statutory_group_ids)
        ->select('id', 'account_name as name')
        ->orderBy('name')
        ->get();

    // ===== Fetch Saved Payroll Configuration =====
    $savedConfigs = PayrollConfiguration::where('company_id', $companyId)
        ->pluck('account_id', 'type')
        ->toArray();

    $tds_account_id = $savedConfigs['tds'] ?? null;
    $pf_account_id  = $savedConfigs['pf'] ?? null;

    return view('PayrollConfiguration.index', compact(
        'statutory_account_list',
        'tds_account_id',
        'pf_account_id'
    ));
}
    public function store(Request $request)
{
    Gate::authorize('action-module', 81);

    $companyId = Session::get('user_company_id');

    // ===== Validation =====
    $request->validate([
        'tds_account_id' => 'nullable|integer',
        'pf_account_id'  => 'nullable|integer',
    ]);

    // ===== Save / Update TDS =====
    if ($request->tds_account_id) {
        PayrollConfiguration::updateOrCreate(
            [
                'type' => 'tds',
                'company_id' => $companyId,
            ],
            [
                'account_id' => $request->tds_account_id,
            ]
        );
    }

    // ===== Save / Update PF =====
    if ($request->pf_account_id) {
        PayrollConfiguration::updateOrCreate(
            [
                'type' => 'pf',
                'company_id' => $companyId,
            ],
            [
                'account_id' => $request->pf_account_id,
            ]
        );
    }

    return redirect()
        ->back()
        ->with('success', 'Payroll configuration saved successfully');
}
}