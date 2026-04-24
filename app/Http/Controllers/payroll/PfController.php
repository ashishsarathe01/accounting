<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class PfController extends Controller
{
    public function PayrollSheet(Request $request)
    {
        $monthYear = $request->month_year;

        if (!$monthYear) {
            return view('payroll.pf_sheet', [
                'pfData' => [],
                'monthYear' => null
            ]);
        }

        $companyId = Session::get('user_company_id') ?? 1;

        $rows = DB::table('payrolls')
            ->join('users', 'users.id', '=', 'payrolls.employee_user_id')
            ->where('payrolls.company_id', $companyId)
            ->where('payrolls.month_year', $monthYear)
            ->where('users.type', 'EMPLOYEE')
            ->where('users.pf_applicable', 'Yes')
            ->where(function ($q) {
                $q->where('users.delete_status', '0')
                ->orWhereNull('users.delete_status');
            })
            ->select(
                'users.uan_number',
                'users.name',
                'payrolls.basic_salary',
                'payrolls.dearness_allowance',
                'payrolls.gross_salary',
                'payrolls.absent'
            )
            ->orderBy('users.name')
            ->get();

        $pfData = [];
        $sno = 1;

        foreach ($rows as $row) {
            $epfWages = round($row->basic_salary + $row->dearness_allowance, 2);
            $epsWages = $epfWages > 15000 ? 15000 : $epfWages;
            $edliWages = $epfWages;

            $epfEE = round($epfWages * 0.12, 2);
            $epsER = round($epsWages * 0.0833, 2);
            $epfER = round($epfEE - $epsER, 2);

            $pfData[] = [
                'sno'        => $sno++,
                'uan'        => $row->uan_number,
                'name'       => $row->name,
                'gross'      => round($row->gross_salary, 2),
                'epf_wages'  => $epfWages,
                'eps_wages'  => $epsWages,
                'edli_wages' => $edliWages,
                'epf_ee'     => $epfEE,
                'eps_er'     => $epsER,
                'diff_er'    => $epfER,
                'ncp_days'   => $row->absent,
                'refund'     => 0.00,
            ];
        }

        return view('payroll.pf_sheet', compact('pfData', 'monthYear'));
    }
}