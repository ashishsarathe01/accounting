<?php

namespace App\Http\Controllers\DutiesCompliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\CompanyStatutorySetting;
use Illuminate\Support\Facades\DB;
use App\Models\Companies;
use App\Helpers\CommonHelper;
class DutiesComplianceController extends Controller
{
    protected $gstCredentials;

    public function __construct()
    {
        $this->gstCredentials = json_decode(
            CommonHelper::gstApiCredentials('GST')
        );
    }
   public function index(Request $request)
{
    $financial_year = $request->financial_year ?? Session::get('default_fy');

    $companyId = Session::get('user_company_id');

    // ================= COMPANY =================

    $company = \App\Models\Companies::select('gst_config_type')
        ->where('id', $companyId)
        ->first();

    // ================= GST LIST =================

    if ($company && $company->gst_config_type == "single_gst") {

        $gstList = DB::table('gst_settings')
            ->select('gst_no')
            ->where([
                'company_id' => $companyId,
                'gst_type'   => "single_gst",
                'delete'     => '0',
                'status'     => '1'
            ])
            ->pluck('gst_no');

    } else if ($company && $company->gst_config_type == "multiple_gst") {

        $gstList = DB::table('gst_settings_multiple')
            ->select('gst_no')
            ->where([
                'company_id' => $companyId,
                'gst_type'   => "multiple_gst",
                'delete'     => '0',
                'status'     => '1'
            ])
            ->pluck('gst_no');

    } else {

        $gstList = collect();

    }

    // ================= SELECTED GST =================

    $selectedGst = $request->gst_no ?? ($gstList[0] ?? '');

    // ================= FY =================

    $y = explode("-", $financial_year);

    $from = \DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = \DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from . '-04',
        $from . '-05',
        $from . '-06',
        $from . '-07',
        $from . '-08',
        $from . '-09',
        $from . '-10',
        $from . '-11',
        $from . '-12',
        $to . '-01',
        $to . '-02',
        $to . '-03'
    ];

    $currentMonth = date('Y-m');

    $filteredMonths = array_filter($month_arr, function ($month) use ($currentMonth) {
        return $month <= $currentMonth;
    });

    // ================= API DATA =================

    $apiData = [];

    if ($selectedGst != '') {
        // 25-26 => 2025-26
        $fy = "20" . $financial_year;
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
                . $selectedGst
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

                    $retPrd = $return['ret_prd'] ?? '';

                    // 042025 => 2025-04
                    $monthKey =
                        substr($retPrd, 2, 4)
                        . '-'
                        . substr($retPrd, 0, 2);

                    $type = $return['rtntype'] ?? '';

                    $apiData[$monthKey][$type] = [
                        'status' => $return['status'] ?? '-',
                        'arn'    => $return['arn'] ?? '-',
                        'date'   => $return['dof'] ?? '-',
                    ];
                }
            }

        } catch (\Exception $e) {

        }
    }

    // ================= FY DROPDOWN =================

    $fyOptions = [];

$currentYear = date('Y');
$currentMonth = date('m');

// Current Financial Year Logic
if ($currentMonth >= 4) {

    // Example: May 2026 => 26-27
    $fyStart = $currentYear;

} else {

    // Example: Feb 2026 => 25-26
    $fyStart = $currentYear - 1;
}

// Generate Current FY + Previous FYs
for ($i = 0; $i < 5; $i++) {

    $start = substr(($fyStart - $i), -2);
    $end   = substr(($fyStart - $i + 1), -2);

    $fyOptions[] = $start . '-' . $end;
}
    $setting = CompanyStatutorySetting::where('company_id', $companyId)->first();

    return view(
    'DutiesCompliance.DutiesCompliance',
    compact(
        'filteredMonths',
        'setting',
        'gstList',
        'selectedGst',
        'financial_year',
        'fyOptions',
        'apiData'
    )
);
}


}