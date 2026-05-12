<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\CompanyStatutorySetting;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
class GSTComplianceController extends Controller
{
    protected $gstCredentials;

    public function __construct()
    {
        $this->gstCredentials = json_decode(
            CommonHelper::gstApiCredentials('GST')
        );
    }
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
    
        $finalData = [];
    
        // Convert selected month to API format
        // 2026-04 => 042026
        $returnPeriod = date('mY', strtotime($month . '-01'));
    
        // Financial Year
        $year = date('Y', strtotime($month));
        $monthNum = date('m', strtotime($month));
    
        if ($monthNum >= 4) {
            $fy = $year . '-' . substr($year + 1, -2);
        } else {
            $fy = ($year - 1) . '-' . substr($year, -2);
        }
    
        foreach ($companies as $row) {
    
            if (!$row->company) {
                continue;
            }
    
            $company = \App\Models\Companies::select('gst_config_type')
                ->where('id', $row->company->id)
                ->first();
    
            if (!$company) {
                continue;
            }
    
            // ================= GST LIST =================
    
            if ($company->gst_config_type == "single_gst") {
    
                $gstList = DB::table('gst_settings')
                    ->select('gst_no')
                    ->where([
                        'company_id' => $row->company->id,
                        'gst_type'   => "single_gst",
                        'delete'     => '0',
                        'status'     => '1'
                    ])
                    ->get();
    
            } else if ($company->gst_config_type == "multiple_gst") {
    
                $gstList = DB::table('gst_settings_multiple')
                    ->select('gst_no')
                    ->where([
                        'company_id' => $row->company->id,
                        'gst_type'   => "multiple_gst",
                        'delete'     => '0',
                        'status'     => '1'
                    ])
                    ->get();
    
            } else {
    
                $gstList = collect();
    
            }
    
            // ================= EACH GST =================
    
            foreach ($gstList as $gst) {
    
                $gstr1Status = '-';
                $gstr1Arn = '-';
                $gstr1Date = '-';
    
                $gstr3bStatus = '-';
                $gstr3bArn = '-';
                $gstr3bDate = '-';
    
                try {
                    //Gst Credenatial
                    if(!$this->gstCredentials){
                        $response = [
                                        'success' => false,
                                        'data'    => "",
                                        'message' => "Api Credentails Not Found ",
                                    ];
                        return response()->json($response, 200);
                    }
                    if($this->gstCredentials->status != 1){
                        $response = [
                                        'success' => false,
                                        'data'    => "",
                                        'message' => "Api Credentails Not Found ",
                                    ];
                        return response()->json($response, 200);
                    }
                    $base_url = $this->gstCredentials->base_url;
                    $email_id = $this->gstCredentials->email_id;
                    $client_id = $this->gstCredentials->client_id;
                    $client_secret = $this->gstCredentials->client_secret;
                    $ip_address = $this->gstCredentials->ip_address;
                    $url = $base_url."/public/rettrack?gstin="
                        . $gst->gst_no
                        . "&fy="
                        . $fy
                        . "&email="
                        . urlencode($email_id);
    
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER => [
                            "Accept: application/json",
                            "client_id: ".$client_id,
                            "client_secret: ".$client_secret,
                        ],
                    ]);
    
                    $response = curl_exec($ch);
    
                    curl_close($ch);
    
                    $responseData = json_decode($response, true);
    
                    if (
                        isset($responseData['data']['EFiledlist']) &&
                        is_array($responseData['data']['EFiledlist'])
                    ) {
    
                        foreach ($responseData['data']['EFiledlist'] as $return) {
    
                            // MATCH MONTH
                            if (($return['ret_prd'] ?? '') != $returnPeriod) {
                                continue;
                            }
    
                            // GSTR1
                            if (($return['rtntype'] ?? '') == "GSTR1") {
    
                                $gstr1Status = $return['status'] ?? '-';
                                $gstr1Arn = $return['arn'] ?? '-';
                                $gstr1Date = $return['dof'] ?? '-';
    
                            }
    
                            // GSTR3B
                            if (($return['rtntype'] ?? '') == "GSTR3B") {
    
                                $gstr3bStatus = $return['status'] ?? '-';
                                $gstr3bArn = $return['arn'] ?? '-';
                                $gstr3bDate = $return['dof'] ?? '-';
    
                            }
                        }
                    }
    
                } catch (\Exception $e) {
    
                }
    
                $finalData[] = [
                    'company_name' => $row->company->company_name ?? '-',
                    'gst_no'       => $gst->gst_no ?? '-',
    
                    // GSTR1
                    'gstr1_status' => $gstr1Status,
                    'gstr1_arn'    => $gstr1Arn,
                    'gstr1_date'   => $gstr1Date,
    
                    // GSTR3B
                    'gstr3b_status' => $gstr3bStatus,
                    'gstr3b_arn'    => $gstr3bArn,
                    'gstr3b_date'   => $gstr3bDate,
                ];
            }
        }
    
        return view(
            'admin-module.GSTCompliance.GSTComplianceReport',
            compact('finalData', 'month')
        );
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