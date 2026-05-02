<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\CompanyStatutorySetting;

class GSTComplianceController extends Controller
{
  
    public function create()
    {
        $companies = Companies::where('delete', '0')->get();

        $settings = CompanyStatutorySetting::all();

        return view('admin-module.GSTCompliance.addGSTCompliance', compact('companies', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|array',
            'company_id.*' => 'required|exists:companies,id',
        ]);
        $submittedCompanyIds = $request->company_id;

        CompanyStatutorySetting::whereNotIn('company_id', $submittedCompanyIds)->delete();
        foreach ($request->company_id as $companyId) {

            CompanyStatutorySetting::updateOrCreate(
                [
                    'company_id' => $companyId
                ],
                [
                    'gst'  => isset($request->gst[$companyId]) ? 1 : 0,
                    'esic' => isset($request->esic[$companyId]) ? 1 : 0,
                    'tds'  => isset($request->tds[$companyId]) ? 1 : 0,
                    'pf'   => isset($request->pf[$companyId]) ? 1 : 0,
                ]
            );
        }

        return redirect()->back()->with('success', 'GST Compliance settings saved successfully!');
    }
    public function report(Request $request)
    {
        $month = $request->month ?? date('Y-m');

        $companies = \App\Models\CompanyStatutorySetting::where('gst', 1)
            ->with('company') 
            ->get();

        return view('admin-module.GSTCompliance.GSTComplianceReport', compact('companies', 'month'));
    }
    public function tdsReport(Request $request)
    {
        $month = $request->month ?? date('Y-m');

        $companies = \App\Models\CompanyStatutorySetting::where('tds', 1)
            ->with('company')
            ->get();

        return view('admin-module.GSTCompliance.TDSComplianceReport', compact('companies', 'month'));
    }
    public function esicPfReport(Request $request)
    {
        $month = $request->month ?? date('Y-m');

        $companies = \App\Models\CompanyStatutorySetting::where(function ($q) {
                $q->where('esic', 1)
                ->orWhere('pf', 1);
            })
            ->with('company')
            ->get();

        return view('admin-module.GSTCompliance.ESICPFComplianceReport', compact('companies', 'month'));
    }


}