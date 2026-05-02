<?php

namespace App\Http\Controllers\DutiesCompliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\CompanyStatutorySetting;
class DutiesComplianceController extends Controller
{

    public function index(Request $request)
    {
        $financial_year = Session::get('default_fy');

        $y = explode("-", $financial_year);

        $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
        $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

        $month_arr = [
            $from . '-04', $from . '-05', $from . '-06',
            $from . '-07', $from . '-08', $from . '-09',
            $from . '-10', $from . '-11', $from . '-12',
            $to . '-01', $to . '-02', $to . '-03'
        ];

        $currentMonth = date('Y-m');

        $filteredMonths = array_filter($month_arr, function ($month) use ($currentMonth) {
            return $month <= $currentMonth;
        });

        $months = array_map(function ($month) {
            return [
                'key' => $month,
                'label' => date('F Y', strtotime($month . '-01'))
            ];
        }, $filteredMonths);

        $companyId = Session::get('user_company_id');

        $setting = CompanyStatutorySetting::where('company_id', $companyId)->first();
        return view('DutiesCompliance.DutiesCompliance', compact('months', 'setting'));
    } 

}