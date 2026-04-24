<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Session;
use App\Models\Payroll;
use App\Models\Accounts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function index(Request $request)
{
    $monthYear = $request->month_year ?? date('Y-m');
    $payrolls = Payroll::where('company_id', Session::get('user_company_id'))
->where('month_year', $monthYear)
->get()
->keyBy('employee_user_id');
    $users = User::where('company_id', Session::get('user_company_id'))
        ->where('type', 'EMPLOYEE')
        ->whereIn('status', ['1', '0'])
        ->where('delete_status', '0')
        ->select(
            'id',
            'type',
            'name',
            'branch',
            'salary',
            'tds_applicable',
            'esi_applicable',
            'pf_applicable'
        )
        ->get();


    return view('payroll.payroll_sheet', compact('users', 'payrolls', 'monthYear'));
}



public function store(Request $request)
{
    DB::beginTransaction();

    try {

        foreach ($request->employee_id as $index => $empId) {

            $payrollId = $request->payroll_id[$index] ?? null;

if ($payrollId) {
    // UPDATE existing payroll row
    Payroll::where('id', $payrollId)->update([
        'branch'             => $request->branch[$index] ?? null,
        'salary'             => $request->salary[$index] ?? 0,
        'absent'             => $request->absent[$index] ?? 0,
        'basic_salary'       => $request->basic_salary[$index] ?? 0,
        'dearness_allowance' => $request->da[$index] ?? 0,
        'incentive'          => $request->incentive[$index] ?? 0,
        'gross_salary'       => $request->gross_salary[$index] ?? 0,
        'tds'                => $request->tds[$index] ?? 0,
        'esi'                => $request->esi[$index] ?? 0,
        'pf'                 => $request->pf[$index] ?? 0,
        'lwf'                => $request->lwf[$index] ?? 0,
        'other_deductions'   => $request->other_deductions[$index] ?? 0,
        'net_payment'        => $request->net_payment[$index] ?? 0,
        'updated_by'         => Session::get('user_id'),
    ]);
} else {
    // INSERT new payroll row (first time only)
    Payroll::create([
        'month_year'         => $request->month_year,
        'employee_user_id'   => $empId,
        'branch'             => $request->branch[$index] ?? null,
        'salary'             => $request->salary[$index] ?? 0,
        'absent'             => $request->absent[$index] ?? 0,
        'basic_salary'       => $request->basic_salary[$index] ?? 0,
        'dearness_allowance' => $request->da[$index] ?? 0,
        'incentive'          => $request->incentive[$index] ?? 0,
        'gross_salary'       => $request->gross_salary[$index] ?? 0,
        'tds'                => $request->tds[$index] ?? 0,
        'esi'                => $request->esi[$index] ?? 0,
        'pf'                 => $request->pf[$index] ?? 0,
        'lwf'                => $request->lwf[$index] ?? 0,
        'other_deductions'   => $request->other_deductions[$index] ?? 0,
        'net_payment'        => $request->net_payment[$index] ?? 0,
        'company_id'         => Session::get('user_company_id'),
        'created_by'         => Session::get('user_id'),
    ]);
}
        }

        DB::commit();

        return redirect()->back()->with('success', 'Payroll saved successfully');

    } catch (\Exception $e) {
        DB::rollback();
        dd([
            'failed_employee_id' => $empId,
            'row_index'          => $index,
            'row_data'           => [
                'salary' => $request->salary[$index] ?? null,
                'gross'  => $request->gross_salary[$index] ?? null,
            ],
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
        ]);
    }
}

public function settings()
{
    $companyId = Session::get('user_company_id');

    $accounts = Accounts::where('company_id', $companyId)
        ->where('status', '1')
        ->where('delete', '0')
        ->get();

    $settings = DB::table('payroll_statutory_accounts')
        ->where('company_id', $companyId)
        ->get()
        ->keyBy('type');

    return view('payroll.Settings', compact('accounts', 'settings'));
}

public function saveSettings(Request $request)
{
    $companyId = Session::get('user_company_id');

    $request->validate([
        'pf_account_id'   => $request->has_pf == 1 ? 'required|exists:accounts,id' : 'nullable',
        'esic_account_id' => $request->has_esic == 1 ? 'required|exists:accounts,id' : 'nullable',
        'tds_account_id'  => $request->has_tds == 1 ? 'required|exists:accounts,id' : 'nullable',
    ], [
        'pf_account_id.required'   => 'Please select PF account.',
        'esic_account_id.required' => 'Please select ESIC account.',
        'tds_account_id.required'  => 'Please select TDS account.',
    ]);

    DB::beginTransaction();

    try {

        if ($request->has_pf == 1) {

            DB::table('payroll_statutory_accounts')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'type'       => 'pf'
                ],
                [
                    'account_id' => $request->pf_account_id,
                    'status'     => 1,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

        } else {

            DB::table('payroll_statutory_accounts')
                ->where('company_id', $companyId)
                ->where('type', 'pf')
                ->update([
                    'status'     => 0,
                    'updated_at' => now()
                ]);
        }


        if ($request->has_esic == 1) {

            DB::table('payroll_statutory_accounts')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'type'       => 'esic'
                ],
                [
                    'account_id' => $request->esic_account_id,
                    'status'     => 1,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

        } else {

            DB::table('payroll_statutory_accounts')
                ->where('company_id', $companyId)
                ->where('type', 'esic')
                ->update([
                    'status'     => 0,
                    'updated_at' => now()
                ]);
        }
        // ================= TDS =================

        if ($request->has_tds == 1) {

            DB::table('payroll_statutory_accounts')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'type'       => 'tds'
                ],
                [
                    'account_id' => $request->tds_account_id,
                    'status'     => 1,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

        } else {

            DB::table('payroll_statutory_accounts')
                ->where('company_id', $companyId)
                ->where('type', 'tds')
                ->update([
                    'status'     => 0,
                    'updated_at' => now()
                ]);
        }
        DB::commit();

        return back()->with('success', 'Settings saved successfully.');

    } catch (\Exception $e) {

        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

}
