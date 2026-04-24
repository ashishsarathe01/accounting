<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Session;
use App\Models\EsicPayroll;
use DB;
use Carbon\Carbon;

class EsicController extends Controller
{
public function esicPayrollSheet(Request $request)
{
    $month = $request->month ?? date('Y-m'); // YYYY-MM

    $daysInMonth = Carbon::createFromFormat('Y-m', $month)->daysInMonth;

    $payrolls = \DB::table('payrolls')
    ->join('users', 'users.id', '=', 'payrolls.employee_user_id')

    ->leftJoin('esic_payrolls', function ($join) use ($month) {
        $join->on('esic_payrolls.employee_user_id', '=', 'payrolls.employee_user_id')
             ->where('esic_payrolls.month_year', '=', $month)
             ->where('esic_payrolls.company_id', '=', Session::get('user_company_id'));
    })

    ->where('payrolls.company_id', Session::get('user_company_id'))
    ->where('payrolls.month_year', $month)
    ->where('users.type', 'EMPLOYEE')
    ->where('users.esi_applicable', 'Yes')
    ->whereIn('users.status', ['1', '0'])
    ->where('users.delete_status', '0')

    ->select(
        'users.id as user_id',
        'users.name',
        'users.branch',
        'users.esic_number',
        'users.esi_applicable',

        'payrolls.salary',        // Gross
        'payrolls.absent',        // Absent
        'payrolls.gross_salary', // ESIC Salary

        'esic_payrolls.reason_code',
        'esic_payrolls.last_working_day'
    )
    ->get();

    return view('payroll.esic_sheet', compact(
        'payrolls',
        'daysInMonth',
        'month'
    ));
}

public function saveEsicPayroll(Request $request)
{
    DB::beginTransaction();

    try {

        $lwdAllowedReasons = [2, 3, 4, 5, 6, 10];

        foreach ($request->employee_user_id as $i => $empId) {

            $absent       = (int) ($request->absent[$i] ?? 0);
            $daysInMonth  = (int) $request->days_in_month;
            $reasonCode   = $request->reason[$i] ?? null;

            if ($absent !== $daysInMonth) {
                continue;
            }

            EsicPayroll::updateOrCreate(
                [
                    'company_id'       => Session::get('user_company_id'),
                    'employee_user_id' => $empId,
                    'month_year'       => $request->month_year,
                ],
                [
                    'gross_salary' => $request->gross_salary[$i],

                    'absent' => $absent,

                    'reason_code' => $reasonCode !== '' ? $reasonCode : null,

                    'last_working_day' => (
                        $reasonCode !== null &&
                        in_array((int)$reasonCode, $lwdAllowedReasons)
                    )
                        ? ($request->last_working_day[$i] ?: null)
                        : null,
                ]
            );
        }

        DB::commit();
        return back()->with('success', 'ESIC details saved successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

public function exportEsicExcel(Request $request)
{
    $rows = json_decode($request->esic_rows, true);

    $filename = "ESIC_UPLOAD_" . date('dmY_His') . ".csv";

    $headers = [
        "Content-Type"        => "text/csv",
        "Content-Disposition" => "attachment; filename={$filename}",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate",
        "Expires"             => "0",
    ];

    $callback = function () use ($rows) {

        $file = fopen('php://output', 'w');

        // 🔴 EXACT ESIC HEADER ORDER
        fputcsv($file, [
            'IP Number',
            'IP Name',
            'No of Days',
            'Total Monthly Wages',
            'Reason Code',
            'Last Working Day'
        ]);

        foreach ($rows as $r) {

            // 🔒 ESIC safety rules (final guard)
            if ((int)$r['days'] > 0) {
                $r['reason'] = '';
                $r['lwd']    = '';
            }

            fputcsv($file, [
                $r['ip_number'],
                $r['ip_name'],
                $r['days'],
                number_format($r['wages'], 2, '.', ''),
                $r['reason'],
                $r['lwd'] ? date('d/m/Y', strtotime($r['lwd'])) : ''
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}
