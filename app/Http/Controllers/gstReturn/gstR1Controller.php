<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Session;
use DateTime;
use App\Models\BillSundrys;
use App\Models\SaleSundry;
use App\Models\State;
use App\Models\Sales;
use App\Models\SaleReturn;
use App\Models\Accounts;
use App\Helpers\CommonHelper;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\gstToken;
use Carbon\Carbon;
use Illuminate\Support\Str;



class gstR1Controller extends Controller
{


public function filterform()
{
    $companyId = Session::get('user_company_id');
    $companyData = Companies::where('id', $companyId)->first();
    $fy = Session::get('default_fy');
    $seriesList = [];

    if ($companyData->gst_config_type == "single_gst") {
        // Fetch single GST setting
        $setting = DB::table('gst_settings')
            ->where(['company_id' => $companyId, 'gst_type' => "single_gst"])
            ->first();

        if ($setting) {
            // Add gst_settings main record to seriesList
            $seriesList[] = [
                'series_name' => $setting->series ?? 'Default Series',
                'gst_no' => $setting->gst_no ?? ''
            ];

            // Fetch all branches under the same setting
            $branches = GstBranch::select('branch_series as series_name', 'gst_number as gst_no')
                ->where([
                    'delete' => '0',
                    'company_id' => $companyId,
                    'gst_setting_id' => $setting->id
                ])->get();

            $seriesList = array_merge($seriesList, $branches->toArray());
        }

    } elseif ($companyData->gst_config_type == "multiple_gst") {
        // Fetch all multiple GST settings
        $settings = DB::table('gst_settings_multiple')
            ->select('id', 'series', 'gst_no')
            ->where(['company_id' => $companyId, 'gst_type' => "multiple_gst"])
            ->get();

        foreach ($settings as $setting) {
            // Add gst_settings_multiple main record to seriesList
            $seriesList[] = [
                'series_name' => $setting->series ?? 'Default Series',
                'gst_no' => $setting->gst_no ?? ''
            ];

            // Fetch all branches under this multiple setting
            $branches = GstBranch::select('branch_series as series_name', 'gst_number as gst_no')
                ->where([
                    'delete' => '0',
                    'company_id' => $companyId,
                    'gst_setting_multiple_id' => $setting->id
                ])->get();

            $seriesList = array_merge($seriesList, $branches->toArray());
        }
    }

    return view('gstReturn.filterIndex', compact('seriesList','fy'));
}


public function gstr1Detail(Request $request)
{
    $from_date = $request->from_date;
    $to_date = $request->to_date;
    $month = Carbon::parse($from_date)->format('mY');

    $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->series
                            ])
                            ->first();
            $gst_username = $gst->gst_username;
        }else if($company->gst_config_type == "multiple_gst"){            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->series
                            ])
                            ->first();
            $gst_username = $gst->gst_username;
        }
        if($gst_username==""){
            $response = array(
                    'status' => false,
                    'message' => 'Please Enter GST User Name In GST Configuration.'
                );
            return json_encode($response);
        }

   
    $state_code = substr(trim($request->series), 0, 2);

    $gst_token = gstToken::select('txn','created_at')
        ->where('company_gstin',$request->series)
        ->where('company_id',Session::get('user_company_id'))
        ->where('status',1)
        ->orderBy('id','desc')
        ->first();

    if ($gst_token) {
        $token_expiry = Carbon::parse($gst_token->created_at)->addHours(6);
        $current_time = Carbon::now();

        if ($token_expiry < $current_time) {
            $token_res = CommonHelper::gstTokenOtpRequest($state_code, $gst_username, $request->series);
            if ($token_res == 0) {
                return response()->json(['status' => false, 'message' => 'Something Went Wrong In Token Generation']);
            }
            return response()->json(['status' => true, 'message' => 'TOKEN-OTP']);
        }
    } else {
        $token_res = CommonHelper::gstTokenOtpRequest($state_code, $gst_username, $request->series);
        if ($token_res == 0) {
            return response()->json(['status' => false, 'message' => 'Something Went Wrong In Token Generation']);
        }
        return response()->json(['status' => true, 'message' => 'TOKEN-OTP']);
    }

    return response()->json(['status' => true, 'message' => 'TOKEN-VALID']);
}



    public function gstmain(Request $request){
        
        $input = $request->all();
        $merchant_gst = $request->series;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $company_id = Session::get('user_company_id');
        $fy = Session::get('default_fy');        
        $comp_details = DB::table('companies')
          ->where('id', $company_id)
          ->first();
        // Validate required fields
        if (!$merchant_gst || !$company_id || !$from_date || !$to_date) {
            return response()->json(['error' => 'Missing required filters'], 400);
        }
        // Fetch summary from sales table
        $summary = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereDate('date', '>=', $from_date)
            ->whereDate('date', '<=', $to_date)
            ->where('delete', '0')
            ->where('status', '1')
            ->whereNotNull('billing_gst') // <- this ensures billing_gst is not null
            ->Where('billing_gst','!=','')
            ->selectRaw('SUM(taxable_amt) as total_taxable_amt, SUM(total) as total_sale_amt')
            ->first();
        // Get matching sale IDs
        $saleIds = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereDate('date', '>=', $from_date)
            ->whereDate('date', '<=', $to_date)
            ->whereNotNull('billing_gst') 
            ->Where('billing_gst','!=','')
            ->where('delete', '0')
            ->where('status', '1')
            ->pluck('id');
        $saleCount = $saleIds->count();    
        // Get IDs of tax sundries
        $companyId = Session::get('user_company_id');    
        $CGST_id = BillSundrys::where('company_id', $companyId)
            ->where('nature_of_sundry', 'CGST')
            ->value('id');    
        $SGST_id = BillSundrys::where('company_id', $companyId)
            ->where('nature_of_sundry', 'SGST')
            ->value('id');    
        $IGST_id = BillSundrys::where('company_id', $companyId)
            ->where('nature_of_sundry', 'IGST')
            ->value('id');
    
        // Get tax totals from SaleSundry
        $taxSummary = DB::table('sale_sundries')
            ->whereIn('sale_id', $saleIds)
            ->whereIn('bill_sundry', [$CGST_id, $SGST_id, $IGST_id])
            ->selectRaw("
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_cgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_sgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_igst
            ", [$CGST_id, $SGST_id, $IGST_id])
            ->first();
        // Get all B2C sales (billing_gst is NULL)
        $b2cSales = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereDate('date', '>=', $from_date)
            ->whereDate('date', '<=', $to_date)
            ->where('delete', '0')
            ->where('status', '1')
            ->where(function($query) {
                $query->whereNull('billing_gst')
                    ->orWhere('billing_gst', '');
            })
            ->select('id', 'total', 'taxable_amt')
            ->get();    
        // Split B2C Large and Normal based on total > 250000
        $b2cLargeIds = $b2cSales->where('total', '>', 250000)->pluck('id')->toArray();
        $b2cNormalIds = $b2cSales->where('total', '<=', 250000)->pluck('id')->toArray();
        $b2cLargeCount = count($b2cLargeIds);
        $b2cNormalCount = count($b2cNormalIds);
        // Summarize B2C Large Sales
        $summaryB2CLarge = DB::table('sales')
            ->whereIn('id', $b2cLargeIds)
            ->selectRaw('SUM(taxable_amt) as total_taxable_amt, SUM(total) as total_sale_amt')
            ->first();
        // Tax totals for B2C Large
        $taxSummaryB2CLarge = DB::table('sale_sundries')
            ->whereIn('sale_id', $b2cLargeIds)
            ->whereIn('bill_sundry', [$CGST_id, $SGST_id, $IGST_id])
            ->selectRaw("
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_cgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_sgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_igst
            ", [$CGST_id, $SGST_id, $IGST_id])
            ->first();
        // Summarize B2C Normal Sales
        $summaryB2CNormal = DB::table('sales')
            ->whereIn('id', $b2cNormalIds)
            ->selectRaw('SUM(taxable_amt) as total_taxable_amt, SUM(total) as total_sale_amt')
            ->first();
        // Tax totals for B2C Normal
        $taxSummaryB2CNormal = DB::table('sale_sundries')
            ->whereIn('sale_id', $b2cNormalIds)
            ->whereIn('bill_sundry', [$CGST_id, $SGST_id, $IGST_id])
            ->selectRaw("
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_cgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_sgst,
                SUM(CASE WHEN bill_sundry = ? THEN amount ELSE 0 END) as total_igst
            ", [$CGST_id, $SGST_id, $IGST_id])
            ->first();
        //shubham code
        // ----------- B2C Normal State-wise Totals (from B2Cstatewise) ------------
        $user_company_id = Session::get('user_company_id');
        // Get all B2C Normal Sale IDs
        $b2cNormalStateSaleIds = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('delete', '0')
            ->where('status', '1')
        ->where(function($query) {
            $query->whereNull('billing_gst')
                ->orWhere('billing_gst', '');
        })
        ->where('total', '<=', 250000)
        ->pluck('id');
        $b2cTaxableTotal = 0;
        $b2cCGST = 0;
        $b2cSGST = 0;
        $b2cIGST = 0;
        if($b2cNormalStateSaleIds->isNotEmpty()) {
            $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
            $items = DB::table('sale_descriptions')
                ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
                ->join('accounts', 'sales.party', '=', 'accounts.id')
                ->join('states', 'accounts.state', '=', 'states.id')
                ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
                ->whereIn('sale_id', $b2cNormalStateSaleIds)
                ->select(
                    'sale_descriptions.id as sale_desc_id',
                    'sale_descriptions.sale_id',
                    'states.name as state_name',
                    'manage_items.gst_rate',
                    'sale_descriptions.amount'
                )
                ->get();
            $sundryDetails = DB::table('sale_sundries')
                ->whereIn('sale_id', $b2cNormalStateSaleIds)
                ->select('sale_id', 'bill_sundry', 'amount')
                ->get()
                ->groupBy('sale_id');

            $ledgerAdjustments = [];
            foreach ($sundryDetails as $saleId => $entries) {
                foreach ($entries as $entry) {
                    $bs = $sundries[$entry->bill_sundry] ?? null;
                    if (!$bs) continue;

                    if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                        $ledger_amount = DB::table('account_ledger')
                            ->where('company_id', $user_company_id)
                            ->where('entry_type', 1)
                            ->where('entry_type_id', $saleId)
                            ->where('account_id', $bs->sale_amt_account)
                            ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                        $ledgerAdjustments[$saleId][] = [
                            'amount' => $ledger_amount,
                            'type' => $bs->bill_sundry_type
                        ];
                    }
                }
            }
            foreach ($items as $item) {
                $sale_id = $item->sale_id;
                $rate = $item->gst_rate;
                $item_amount = $item->amount;    
                $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
                if ($total_item_amount == 0) continue;    
                $adjusted_value = 0;    
                // Adjust using bill sundries with adjust_sale_amt = Yes
                if (isset($sundryDetails[$sale_id])) {
                    foreach ($sundryDetails[$sale_id] as $sundryEntry) {
                        $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                        if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
            
                        $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                        $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                    }
                }    
                // Adjust using ledger entries
                if (isset($ledgerAdjustments[$sale_id])) {
                    foreach ($ledgerAdjustments[$sale_id] as $adj) {
                        $share = ($item_amount / $total_item_amount) * $adj['amount'];
                        $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                    }
                }    
                $taxable_value = $item_amount + $adjusted_value;
                $b2cTaxableTotal += $taxable_value;    
                // âœ… Updated Tax Calculation
                $merchant_state_code = substr($merchant_gst, 0, 2); 
                $customer_state_code = State::where('name', $item->state_name)->value('state_code');    
                if ($merchant_state_code == $customer_state_code) {
                    $b2cCGST += ($rate / 2 / 100) * $taxable_value;
                    $b2cSGST += ($rate / 2 / 100) * $taxable_value;
                } else {
                    $b2cIGST += ($rate / 100) * $taxable_value;
                }
            }
        }
        $from = \DateTime::createFromFormat('Y-m-d', $from_date);
        $month = $from->format('mY'); // MMYYYY => 042025
        $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->series
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }else if($company->gst_config_type == "multiple_gst"){            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->series
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }
        if($gst_user_name==""){
            $response = array(
                    'status' => false,
                    'message' => 'Please Enter GST User Name In GST Configuration.'
                );
            return json_encode($response);
        }
        $state_code = substr(trim($request->series), 0, 2); // e.g., "07"
        $gst_token = gstToken::select('txn','created_at')
                                ->where('company_gstin',$request->series)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('id','desc')
                                ->first();
        if($gst_token){
            $token_expiry = date('d-m-Y H:i:s',strtotime('+6 hour',strtotime($gst_token->created_at)));
            $current_time = date('d-m-Y H:i:s');
            if(strtotime($token_expiry)<strtotime($current_time)){
                $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->series);
                if($token_res==0){
                    $response = array(
                        'status' => false,
                        'message' => 'Something Went Wrong In Token Generation'
                    );
                    return json_encode($response);
                }
                $response = array(
                    'status' => true,
                    'message' => 'TOKEN-OTP'
                );
                return json_encode($response);
            }
            $txn = $gst_token->txn;
        }else{
            $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->series);
            if($token_res==0){
                    $response = array(
                        'status' => false,
                        'message' => 'Something Went Wrong In Token Generation'
                    );
                    return json_encode($response);
                }
            $response = array(
                    'status' => true,
                    'message' => 'TOKEN-OTP'
            );
            return json_encode($response);
        } 
        $email = 'pram92500@gmail.com'; // ✅ Registered MasterGST email
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mastergst.com/gstr1/b2b?' . http_build_query([
                'email'     => $email,
                'gstin'     => $request->series,
                'retperiod' => $month,
                // 'smrytyp' => 'L' // Optional for long summary
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'gst_username: ' . $gst_user_name,
                'state_cd: ' . $state_code,
                'ip_address: 152.59.25.138', // Use your public server IP
                'txn: ' . $txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true); // Convert to array
        // Debug response
        
        if (isset($result['status_cd']) && $result['status_cd'] == 0 && $result['error']['error_cd']!='RET11416') {
            return response()->json(["status" => 0, "message" => $result['error']['message']]);
        }
        // Example: extract invoice summaries
        $sale = Sales::where('sales.company_id', Session::get('user_company_id'))
            ->whereBetween('sales.date', [$from_date, $to_date])
            ->where('sales.merchant_gst', $request->series)
            ->where('sales.delete', '0' )
            ->whereNotNull('sales.billing_gst')
            ->Where('billing_gst','!=','')
            ->select('sales.billing_gst', 'sales.total')
            ->get();
        $saleTotals = $sale->groupBy('billing_gst')->map(function ($group) {
            return $group->sum('total');
        });
        $invoiceSummaries = [];
        if (isset($result['data']['b2b']) && is_array($result['data']['b2b'])) {
            foreach ($result['data']['b2b'] as $party) {
                $ctin = $party['ctin'];        
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');

                
                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';
                $apiInvoices = collect($party['inv'] ?? []);
                $apiTotal = $apiInvoices->sum('val');

            
                $dbInvoices = Sales::where('billing_gst', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->whereBetween('date', [$from_date, $to_date])
                    ->get(['id', 'voucher_no_prefix', 'date', 'total']);

                
                // Match by invoice number
                $matched = [];
                $onlyInApi = [];
                $onlyInBooks = [];

                foreach ($apiInvoices as $apiInv) {
                    $match = $dbInvoices->firstWhere('voucher_no_prefix', $apiInv['inum']);
                    if ($match) {
                        $matched[] = [
                            'invoice_no' => $apiInv['inum'],
                            'api_value' => $apiInv['val'],
                            'db_value' => $match->total,
                            'match' => round($apiInv['val'], 2) == round($match->total, 2)
                        ];
                    } else {
                        $onlyInApi[] = [
                            'invoice_no' => $apiInv['inum'],
                            'api_value' => $apiInv['val'],
                        ];
                    }
                }

                // Now find DB invoices not in API
                $apiInvoiceNumbers = $apiInvoices->pluck('inum')->toArray();
                foreach ($dbInvoices as $dbInv) {
                    if (!in_array($dbInv->voucher_no_prefix, $apiInvoiceNumbers)) {
                        $onlyInBooks[] = [
                            'invoice_no' => $dbInv->voucher_no_prefix,
                            'db_value' => $dbInv->total,
                        ];
                    }
                }
                $invoiceSummaries[] = [
                    'ctin' => $name,
                    'gstin' => $ctin,
                    'total_value' => $apiTotal,
                    'db_value' => $saleTotals[$ctin] ?? 0,
                    'match' => round($apiTotal, 2) == round($saleTotals[$ctin] ?? 0, 2),
                    'matched_invoices' => $matched,
                    'only_in_api' => $onlyInApi,
                    'only_in_books' => $onlyInBooks,
                ];
            }
        }
        // Step 1: Get all CTINs from API
        $apiCtins = [];
        if(isset($result['data']['b2b'])){
            $apiCtins = collect($result['data']['b2b'])->pluck('ctin')->toArray();
        }
        

        // Step 2: Loop through book GSTINs and find missing ones
        foreach ($saleTotals as $ctin => $dbValue) {
            if (!in_array($ctin, $apiCtins)) {
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');

                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';

                $dbInvoices = Sales::where('billing_gst', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->whereBetween('date', [$from_date, $to_date])
                    ->get(['voucher_no_prefix', 'total']);     

                $matched = [];          // No matched invoices since this CTIN is not in API
                $onlyInApi = [];        // No API data for this CTIN
                $onlyInBooks = [];      // List all DB invoices here

                foreach ($dbInvoices as $inv) {
                    $onlyInBooks[] = [
                        'invoice_no' => $inv->voucher_no_prefix,
                        'db_value' => $inv->total
                    ];
                }

                $invoiceSummaries[] = [
                    'ctin' => $name,
                    'gstin' => $ctin,
                    'total_value' => 0, // Portal has no value
                    'db_value' => $dbValue,
                    'match' => false,
                    'matched_invoices' => $matched,
                    'only_in_api' => $onlyInApi,
                    'only_in_books' => $onlyInBooks,
                ];
            }
        }
        // === 1. CURL to fetch CDNR from API ===
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mastergst.com/gstr1/cdnr?' . http_build_query([
                'email'     => $email,
                'gstin'     => $request->series,
                'retperiod' => $month
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'gst_username: ' . $gst_user_name,
                'state_cd: ' . $state_code,
                'ip_address: 152.59.25.138',
                'txn: ' . $txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);
        
        if (isset($result['status_cd']) && $result['status_cd'] == 0 && $result['error']['error_cd']!='RET11416') {
            return response()->json(["status" => 0, "message" => $result['error']['message']]);
        }
        $returns = DB::table('sales_returns')
            ->where('sales_returns.company_id', Session::get('user_company_id'))
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('sales_returns.merchant_gst', $request->series)
            ->where('voucher_type', 'SALE')
            ->where('delete', '0')
            ->whereNotNull('billing_gst')
            ->where('billing_gst', '!=', '')
            ->select('billing_gst', 'sr_prefix', 'total')
            ->get();
        $returnTotals = $returns->groupBy('billing_gst')->map(function ($group) {
            return $group->sum('total');
        });
        $creditNoteSummaries = [];
        if (isset($result['data']['cdnr']) && is_array($result['data']['cdnr'])) {
            foreach ($result['data']['cdnr'] as $party) {
                $ctin = $party['ctin'];

                // Skip if there are no notes
                if (!isset($party['nt']) || !is_array($party['nt'])) continue;

                // Filter notes where ntty == 'C' (Credit Notes only)
                $creditNotes = array_filter($party['nt'], function ($note) {
                    return isset($note['ntty']) && $note['ntty'] === 'C';
                });

                if (empty($creditNotes)) continue; // skip if no credit notes
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');
                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';

                $apiNotes = collect($creditNotes);
                $apiTotal = $apiNotes->sum('val');

                $dbNotes = $returns->where('billing_gst', $ctin);

                $matched = [];
                $onlyInApi = [];
                $onlyInBooks = [];

                foreach ($apiNotes as $note) {
                    $inum = $note['nt_num'] ?? '';
                    $match = $dbNotes->firstWhere('sr_prefix', $inum);

                    if ($match) {
                        $matched[] = [
                            'invoice_no' => $inum,
                            'api_value' => $note['val'],
                            'db_value' => $match->total,
                            'match' => round($note['val'], 2) == round($match->total, 2)
                        ];
                    } else {
                        $onlyInApi[] = [
                            'invoice_no' => $inum,
                            'api_value' => $note['val'],
                        ];
                    }
                }

                $apiNoteNumbers = $apiNotes->pluck('nt_num')->toArray();

                foreach ($dbNotes as $dbNote) {
                    if (!in_array($dbNote->sr_prefix, $apiNoteNumbers)) {
                        $onlyInBooks[] = [
                            'invoice_no' => $dbNote->sr_prefix,
                            'db_value' => $dbNote->total
                        ];
                    }
                }

                $creditNoteSummaries[] = [
                    'gstin' => $ctin,
                    'ctin' => $name,
                    'total_value' => $apiTotal,
                    'db_value' => $returnTotals[$ctin] ?? 0,
                    'match' => round($apiTotal, 2) == round($returnTotals[$ctin] ?? 0, 2),
                    'matched_invoices' => $matched,
                    'only_in_api' => $onlyInApi,
                    'only_in_books' => $onlyInBooks
                ];
            }
        }
        $apiCtins = [];
        if(isset($result['data']['cdnr'])){
            $apiCtins = collect($result['data']['cdnr'])
            ->filter(function ($party) {
                return isset($party['nt']) && is_array($party['nt']) &&
                    collect($party['nt'])->contains(function ($note) {
                        return isset($note['ntty']) && $note['ntty'] === 'C';
                    });
            })
            ->pluck('ctin')
            ->toArray();
        }
            
        
        foreach ($returnTotals as $ctin => $dbValue) {
            if (!in_array($ctin, $apiCtins)) {
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');

                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';

                $dbNotes = $returns->where('billing_gst', $ctin);
                $onlyInBooks = [];

                foreach ($dbNotes as $note) {
                    $onlyInBooks[] = [
                        'invoice_no' => $note->sr_prefix,
                        'db_value' => $note->total
                    ];
                }

                $creditNoteSummaries[] = [
                    'gstin' => $ctin,
                    'ctin' => $name,
                    'total_value' => 0,
                    'db_value' => $dbValue,
                    'match' => false,
                    'matched_invoices' => [],
                    'only_in_api' => [],
                    'only_in_books' => $onlyInBooks
                ];
            }
        }
        $debitReturns = DB::table('purchase_returns')
            ->where('purchase_returns.company_id', Session::get('user_company_id'))
            ->whereBetween('purchase_returns.date', [$from_date, $to_date])
            ->where('purchase_returns.merchant_gst', $request->series)
            ->where('voucher_type', 'SALE')
            ->where('delete', '0')
            ->whereNotNull('billing_gst')
            ->where('billing_gst', '!=', '')
            ->select('billing_gst', 'sr_prefix', 'total')
            ->get();
        $debitNoteTotals = $debitReturns->groupBy('billing_gst')->map(function ($group) {
            return $group->sum('total');
        });
        $debitNoteSummaries = [];
        if (isset($result['data']['cdnr']) && is_array($result['data']['cdnr'])) {
            foreach ($result['data']['cdnr'] as $party) {
                $ctin = $party['ctin'];

                // Ensure notes exist and are an array
                if (!isset($party['nt']) || !is_array($party['nt'])) continue;

                // ✅ Filter only debit notes from API
                $debitNotes = array_filter($party['nt'], function ($note) {
                    return isset($note['ntty']) && $note['ntty'] === 'D';
                });

                if (empty($debitNotes)) continue;
            
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');
                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';

                $apiNotes = collect($debitNotes);
                $apiTotal = $apiNotes->sum('val');

                $dbNotes = $debitReturns->where('billing_gst', $ctin);

                $matched = [];
                $onlyInApi = [];
                $onlyInBooks = [];

                foreach ($apiNotes as $note) {
                    $inum = $note['nt_num'] ?? '';
                    $match = $dbNotes->firstWhere('sr_prefix', $inum); // Check pr_prefix in DB, use alias if needed

                    if ($match) {
                        $matched[] = [
                            'invoice_no' => $inum,
                            'api_value' => $note['val'],
                            'db_value' => $match->total,
                            'match' => round($note['val'], 2) == round($match->total, 2)
                        ];
                    } else {
                        $onlyInApi[] = [
                            'invoice_no' => $inum,
                            'api_value' => $note['val'],
                        ];
                    }
                }

                $apiNoteNumbers = $apiNotes->pluck('nt_num')->toArray();

                foreach ($dbNotes as $dbNote) {
                    if (!in_array($dbNote->sr_prefix, $apiNoteNumbers)) {
                        $onlyInBooks[] = [
                            'invoice_no' => $dbNote->sr_prefix,
                            'db_value' => $dbNote->total
                        ];
                    }
                }

                $debitNoteSummaries[] = [
                    'gstin' => $ctin,
                    'ctin' => $name,
                    'total_value' => $apiTotal,
                    'db_value' => $debitNoteTotals[$ctin] ?? 0,
                    'match' => round($apiTotal, 2) == round($debitNoteTotals[$ctin] ?? 0, 2),
                    'matched_invoices' => $matched,
                    'only_in_api' => $onlyInApi,
                    'only_in_books' => $onlyInBooks
                ];
            }
        }
        // Handle entries only in books (DB side but not in API)
        $apiCtins = [];
        if (isset($result['data']['cdnr'])){
            $apiCtins = collect($result['data']['cdnr'])->pluck('ctin')->toArray();
        }
        
        foreach ($debitNoteTotals as $ctin => $dbValue) {
            if (!in_array($ctin, $apiCtins)) {
                $accountName = Accounts::where('gstin', $ctin)
                    ->where('company_id', Session::get('user_company_id'))
                    ->value('account_name');

                $name = $accountName ?? 'NOT FOUND (' . $ctin . ')';

                $dbNotes = $debitReturns->where('billing_gst', $ctin);
                $onlyInBooks = [];

                foreach ($dbNotes as $note) {
                    $onlyInBooks[] = [
                        'invoice_no' => $note->sr_prefix,
                        'db_value' => $note->total
                    ];
                }

                $debitNoteSummaries[] = [
                    'gstin' => $ctin,
                    'ctin' => $name,
                    'total_value' => 0,
                    'db_value' => $dbValue,
                    'match' => false,
                    'matched_invoices' => [],
                    'only_in_api' => [],
                    'only_in_books' => $onlyInBooks
                ];
            }
        }
        $totalCreditNotes = $returns->count();
        $totalDebitNotes = $debitReturns->count();
        $totalNotes = $totalCreditNotes + $totalDebitNotes;
        // Return data to Blade or frontend    
        // Return result
        //hsnWiseSummaryCount
        $user_company_id = Session::get('user_company_id');
        $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
        // -------- STEP 1: Get B2C Sale IDs --------
        $b2cSaleIds = DB::table('sales')
                            ->where('merchant_gst', $merchant_gst)
                            ->where('company_id', $company_id)
                            ->whereBetween('date', [$from_date, $to_date])
                            ->where('delete', '0')
                            ->where('status', '1')                            
                            ->pluck('id');
        if ($b2cSaleIds->isEmpty()) {
            return view('gstReturn.hsnSummary', ['data' => []]);
        }
        // ----- SALES ITEMS -----
        $items_sale = DB::table('sale_descriptions')
            ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
            ->join('accounts', 'sales.party', '=', 'accounts.id')
            ->join('states', 'accounts.state', '=', 'states.id')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units','manage_items.u_name','=','units.id')
            ->where('sale_descriptions.status','1')
            ->where('sale_descriptions.delete','0')
            ->whereIn('sale_id', $b2cSaleIds)
            ->select(
                'sale_descriptions.sale_id',
                'states.name as state_name',
                'sale_descriptions.qty',
                'sale_descriptions.amount',
                'manage_items.gst_rate',
                'units.unit_code',
                'manage_items.hsn_code',
                'manage_items.item_type'
            )
            ->get();
        // Sundrys and ledger adjustments for sales
        $sundryDetails_sales = DB::table('sale_sundries')
            ->whereIn('sale_id', $b2cSaleIds)
            ->select('sale_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_id');
        $ledgerAdjustments_sales = [];
        foreach ($sundryDetails_sales as $saleId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs) continue;
                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $saleId)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_sales[$saleId][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }
        // --------- CREDIT NOTES (Sales returns) -----------
        $creditSales = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type','WITH ITEM')
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');
        $creditSales1 = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where(function($q){
                $q->where('sr_type','WITHOUT ITEM')
                ->orWhere('sr_type','RATE DIFFERENCE');
            })
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');       
        $creditItems = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units','manage_items.u_name','=','units.id')
            ->whereIn('sale_return_id', $creditSales)
            ->where('sale_return_descriptions.status','1')
            ->where('sale_return_descriptions.delete','0')
            ->select(
                'sale_return_descriptions.sale_return_id',
                'states.name as state_name',
                'sale_return_descriptions.qty',
                'manage_items.gst_rate',
                'sale_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code',
                
            )
            ->get();
        $creditItems1 = DB::table('sale_return_descriptions')
                        ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
                        ->join('states', 'sales_returns.billing_state', '=', 'states.id')
                        ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
                            ->join('units','manage_items.u_name','=','units.id')
                            
                        ->whereIn('sale_return_id', $creditSales1)
                        ->where('sale_return_descriptions.status','1')
                        ->where('sale_return_descriptions.delete','0')
                        ->select(
                            'sale_return_descriptions.sale_return_id',
                            'states.name as state_name',
                            DB::raw('0 as qty'), // Override qty with 0
                            'manage_items.gst_rate',
                            'sale_return_descriptions.amount',
                            'units.unit_code',
                            'manage_items.hsn_code',
                        )
                        ->get();
    

        $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
                        ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
                        ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
                        ->where('sale_return_without_gst_entry.percentage', '>', 0)
                        ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales1)
                        ->where('sale_return_without_gst_entry.status','1')
                        ->where('sale_return_without_gst_entry.delete','0')
                        ->select(
                            'sale_return_without_gst_entry.sale_return_id as sale_return_id',
                            'states.name as state_name',
                                DB::raw('0 as qty'),
                            'sale_return_without_gst_entry.percentage as gst_rate',
                            'sale_return_without_gst_entry.debit as amount',
                            'sale_return_without_gst_entry.hsn_code',
                            'sale_return_without_gst_entry.unit_code'
                        )
                        ->get();
        $creditItems =  $creditItems->merge($creditItems1)->merge($creditWithoutGstItems);
        $creditSundryDetails = DB::table('sale_return_sundries')
            ->whereIn('sale_return_id', $creditSales)
            ->select('sale_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_return_id');
        $ledgerAdjustments_credit = [];
        foreach ($creditSundryDetails as $return_id => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs) continue;
                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $return_id)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_credit[$return_id][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }
        // ---------- DEBIT NOTES (Purchase returns) -----------
        $debitSales = DB::table('purchase_returns')
            ->where('purchase_returns.merchant_gst', $merchant_gst)
            ->where('purchase_returns.company_id', $company_id)
            ->whereBetween('purchase_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type','WITH ITEM')            
            ->where('purchase_returns.delete', '0')
            ->where('purchase_returns.status', '1')
            ->pluck('purchase_returns.id');

            $debitSales1 = DB::table('purchase_returns')
            ->where('purchase_returns.merchant_gst', $merchant_gst)
            ->where('purchase_returns.company_id', $company_id)
            ->whereBetween('purchase_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where(function($q){
                $q->where('sr_type','WITHOUT ITEM')
                ->orWhere('sr_type','RATE DIFFERENCE');
            })
            
            ->where('purchase_returns.delete', '0')
            ->where('purchase_returns.status', '1')
            ->pluck('purchase_returns.id');

        $debitItems = DB::table('purchase_return_descriptions')
            ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
            ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units','manage_items.u_name','=','units.id')
            ->whereIn('purchase_return_id', $debitSales)
            ->where('purchase_return_descriptions.status','1')
            ->where('purchase_return_descriptions.delete','0')
            ->select(
                'purchase_return_descriptions.purchase_return_id',
                'states.name as state_name',
                'manage_items.gst_rate',
                'purchase_return_descriptions.qty',
                'purchase_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code'
            )
            ->get();

        $debitItems1 = DB::table('purchase_return_descriptions')
            ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
            ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units','manage_items.u_name','=','units.id')
            ->where('purchase_return_descriptions.status','1')
            ->where('purchase_return_descriptions.delete','0')
            ->whereIn('purchase_return_id', $debitSales1)
            ->select(
                'purchase_return_descriptions.purchase_return_id',
                'states.name as state_name',
                'manage_items.gst_rate',
                DB::raw('0 as qty'),
                'purchase_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code'
            )
            ->get();

        $debitWithoutGstItems = DB::table('purchase_return_entries')
                    ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
                    ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
                    ->where('purchase_return_entries.percentage', '>', 0)
                    ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->pluck('id'))
                    ->where('purchase_return_entries.status','1')
                    ->where('purchase_return_entries.delete','0')
                    ->select(
                        'purchase_return_entries.purchase_return_id as purchase_return_id',
                        'states.name as state_name',
                        'purchase_return_entries.percentage as gst_rate',
                        DB::raw('0 as qty'),
                        'purchase_return_entries.debit as amount',
                        'purchase_return_entries.hsn_code',
                        'purchase_return_entries.unit_code'
                        
                    )
                    ->get();
        $debitItems = $debitItems->merge($debitItems1)->merge($debitWithoutGstItems);
        $debitSundryDetails = DB::table('purchase_return_sundries')
            ->whereIn('purchase_return_id', $debitSales)
            ->select('purchase_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('purchase_return_id');

        $debitSundryDetails1 = DB::table('purchase_return_sundries')
            ->whereIn('purchase_return_id', $debitSales1)
            ->select('purchase_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('purchase_return_id');

        $debitSundryDetails = $debitSundryDetails->merge($debitSundryDetails1);
        $ledgerAdjustments_debit = [];
        foreach ($debitSundryDetails as $return_id => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;

                if (!$bs) continue;

                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $return_id)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_debit[$return_id][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }
        // --------- Group and calculate for sales --------
        $grouped_sale = [];
        foreach ($items_sale as $item) {
            $sale_id = $item->sale_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $amount = $item->amount;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            $total_sale_amount = $items_sale->where('sale_id', $sale_id)->sum('amount');
            if ($total_sale_amount == 0) continue;
            $adjusted_value = 0;
            // Adjust sundry
            if (isset($sundryDetails_sales[$sale_id])) {
                foreach ($sundryDetails_sales[$sale_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                    $share = ($amount / $total_sale_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }
            // Adjust ledger
            if (isset($ledgerAdjustments_sales[$sale_id])) {
                foreach ($ledgerAdjustments_sales[$sale_id] as $adj) {
                    $share = ($amount / $total_sale_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }
            $taxable_value = $amount + $adjusted_value;

            // Tax calculation
            $igst = 0; $cgst = 0; $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');

            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }

            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_sale[$key])) {
                $grouped_sale[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }        
            $grouped_sale[$key]['qty'] += $qty;
            $grouped_sale[$key]['taxable_value'] += $taxable_value;
            $grouped_sale[$key]['igst'] += $igst;
            $grouped_sale[$key]['cgst'] += $cgst;
            $grouped_sale[$key]['sgst'] += $sgst;
        }
        // ---------- Group and calculate for credit notes (subtract) ----------
        $grouped_credit = [];
        foreach ($creditItems as $item) {
            $return_id = $item->sale_return_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            $amount = $item->amount;
            $total_credit_amount = $creditItems->where('sale_return_id', $return_id)->sum('amount');
            if ($total_credit_amount == 0) continue;
            $adjusted_value = 0;
            if (isset($creditSundryDetails[$return_id])) {
                foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                    $share = ($amount / $total_credit_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }
            // Adjust ledger
            if (isset($ledgerAdjustments_credit[$return_id])) {
                foreach ($ledgerAdjustments_credit[$return_id] as $adj) {
                    $share = ($amount / $total_credit_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }
            $taxable_value = $amount + $adjusted_value;
            $igst = 0; $cgst = 0; $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');
            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }  
            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_credit[$key])) {
                $grouped_credit[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }
            $grouped_credit[$key]['qty'] += $qty;
            $grouped_credit[$key]['taxable_value'] += $taxable_value;
            $grouped_credit[$key]['igst'] += $igst;
            $grouped_credit[$key]['cgst'] += $cgst;
            $grouped_credit[$key]['sgst'] += $sgst;        
        }
        // --------- Group and calculate for debit notes (add) -----------
        $grouped_debit = [];
        foreach ($debitItems as $item) {
            $return_id = $item->purchase_return_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            $amount = $item->amount;
            $total_debit_amount = $debitItems->where('purchase_return_id', $return_id)->sum('amount');
            if ($total_debit_amount == 0) continue;
            $adjusted_value = 0;
            if (isset($debitSundryDetails[$return_id])) {
                foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                    $share = ($amount / $total_debit_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }

            // Adjust ledger
            if (isset($ledgerAdjustments_debit[$return_id])) {
                foreach ($ledgerAdjustments_debit[$return_id] as $adj) {
                    $share = ($amount / $total_debit_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }

            $taxable_value = $amount + $adjusted_value;

            $igst = 0; $cgst = 0; $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');

            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }

            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_debit[$key])) {
                $grouped_debit[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }

            $grouped_debit[$key]['qty'] += $qty;
            $grouped_debit[$key]['taxable_value'] += $taxable_value;
            $grouped_debit[$key]['igst'] += $igst;
            $grouped_debit[$key]['cgst'] += $cgst;
            $grouped_debit[$key]['sgst'] += $sgst;
        }
        // -------- Combine final adjusted data: sales + debit - credit -------
        $finalData = [];
        $allKeys = collect(array_unique(array_merge(array_keys($grouped_sale), array_keys($grouped_credit), array_keys($grouped_debit))));
        foreach ($allKeys as $key) {
            $hsn = $grouped_sale[$key]['hsn'] ?? $grouped_credit[$key]['hsn'] ?? $grouped_debit[$key]['hsn'] ?? null;
            $rate = $grouped_sale[$key]['rate'] ?? $grouped_credit[$key]['rate'] ?? $grouped_debit[$key]['rate'] ?? null;
            $unit_code = $grouped_sale[$key]['unit_code'] ?? $grouped_credit[$key]['unit_code'] ?? $grouped_debit[$key]['unit_code'] ?? null;

            $salesqty = $grouped_sale[$key]['qty'] ?? 0;
            $salesTaxable = $grouped_sale[$key]['taxable_value'] ?? 0;
            $salesIGST = $grouped_sale[$key]['igst'] ?? 0;
            $salesCGST = $grouped_sale[$key]['cgst'] ?? 0;
            $salesSGST = $grouped_sale[$key]['sgst'] ?? 0;

            $creditqty = $grouped_credit[$key]['qty'] ?? 0;
            $creditTaxable = $grouped_credit[$key]['taxable_value'] ?? 0;
            $creditIGST = $grouped_credit[$key]['igst'] ?? 0;
            $creditCGST = $grouped_credit[$key]['cgst'] ?? 0;
            $creditSGST = $grouped_credit[$key]['sgst'] ?? 0;

            $debitqty = $grouped_debit[$key]['qty'] ?? 0;
            $debitTaxable = $grouped_debit[$key]['taxable_value'] ?? 0;
            $debitIGST = $grouped_debit[$key]['igst'] ?? 0;
            $debitCGST = $grouped_debit[$key]['cgst'] ?? 0;
            $debitSGST = $grouped_debit[$key]['sgst'] ?? 0;

            $finalData[$key] = [
                'rate' => $rate,
                'hsn' => $hsn,
                'unit_code' => $unit_code,
                'qty' => $salesqty + $debitqty - $creditqty,
                'taxable_value' => $salesTaxable + $debitTaxable - $creditTaxable,
                'igst' => $salesIGST + $debitIGST - $creditIGST,
                'cgst' => $salesCGST + $debitCGST - $creditCGST,
                'sgst' => $salesSGST + $debitSGST - $creditSGST,
            ];
        }
        $hsnWiseSummaryCount  = count($finalData);
        $turnover_amount = 0;
        $turnover = DB::table('company_turnovers')
                        ->select('amount')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year', Session::get('default_fy'))
                        ->where('financial_year', $fy)
                        ->first();
        if($turnover){
            $turnover_amount = $turnover->amount;
        }
        
        return view('gstReturn.gstR1', [
            // B2B
            'total_taxable_amt' => $summary->total_taxable_amt ?? 0,
            'total_sale_amt' => $summary->total_sale_amt ?? 0,
            'total_cgst' => $taxSummary->total_cgst ?? 0,
            'total_sgst' => $taxSummary->total_sgst ?? 0,
            'total_igst' => $taxSummary->total_igst ?? 0,
        
            // B2C Large
            'b2c_large_taxable_amt' => $summaryB2CLarge->total_taxable_amt ?? 0,
            'b2c_large_sale_amt' => $summaryB2CLarge->total_sale_amt ?? 0,
            'b2c_large_cgst' => $taxSummaryB2CLarge->total_cgst ?? 0,
            'b2c_large_sgst' => $taxSummaryB2CLarge->total_sgst ?? 0,
            'b2c_large_igst' => $taxSummaryB2CLarge->total_igst ?? 0,
        
            // B2C Normal
            // 'b2c_normal_taxable_amt' => $summaryB2CNormal->total_taxable_amt ?? 0,
            // 'b2c_normal_sale_amt' => $summaryB2CNormal->total_sale_amt ?? 0,
            // 'b2c_normal_cgst' => $taxSummaryB2CNormal->total_cgst ?? 0,
            // 'b2c_normal_sgst' => $taxSummaryB2CNormal->total_sgst ?? 0,
            // 'b2c_normal_igst' => $taxSummaryB2CNormal->total_igst ?? 0,
        
            // Required for Route Links
            'merchant_gst' => $merchant_gst,
            'company_id' => $company_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'fy' => $fy,
            'comp_details' => $comp_details,
            'invoiceSummaries' => $invoiceSummaries,
            'creditNoteSummaries' => $creditNoteSummaries,
            'debitNoteSummaries' => $debitNoteSummaries,
            'b2c_statewise_taxable' => $b2cTaxableTotal,
            'b2c_statewise_cgst' => $b2cCGST,
            'b2c_statewise_sgst' => $b2cSGST,
            'b2c_statewise_igst' => $b2cIGST,
            'saleCountB2B' =>$saleCount,
            'b2cLargeCount' => $b2cLargeCount,
            'b2cNormalCount' => $b2cLargeCount,
            'totalCreditNotes' => $totalCreditNotes,
            'totalDebitNotes' => $totalCreditNotes,
            'totalNotes' => $totalNotes, // likely 0 unless interstate
            'hsnWiseSummaryCount' => $hsnWiseSummaryCount,
            'turnover_amount'=>$turnover_amount
    ]);
        
        
        
        // FOR API 
        // return response()->json([
        //     'total_taxable_amt' => $summary->total_taxable_amt ?? 0,
        //     'total_sale_amt' => $summary->total_sale_amt ?? 0,
        //     'total_cgst' => $taxSummary->total_cgst ?? 0,
        //     'total_sgst' => $taxSummary->total_sgst ?? 0,
        //     'total_igst' => $taxSummary->total_igst ?? 0,
        // ]);
        
        
    }

    













//     public function B2Cstatewise(Request $request)
// {
//     $merchant_gst = $request->merchant_gst;
//     $company_id = $request->company_id;
//     $from_date = $request->from_date;
//     $to_date = $request->to_date;

//     $user_company_id = Session::get('user_company_id');

//     // Step 1: Fetch all Bill Sundrys for the company
//     $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
   

//     // Step 2: Get B2C Sale IDs
//     $b2cSaleIds = DB::table('sales')
//         ->where('merchant_gst', $merchant_gst)
//         ->where('company_id', $company_id)
//         ->whereBetween('date', [$from_date, $to_date])
//         ->where('delete', '0')
//         ->where('status', '1')
//        ->where(function($query) {
//         $query->whereNull('billing_gst')
//               ->orWhere('billing_gst', '');
//                        })
//         ->where('total', '<=', 250000)
//         ->pluck('id');

//     if ($b2cSaleIds->isEmpty()) {
//         return view('gstReturn.b2c_statewise', ['data' => []]);
//     }

//     // Step 3: Get sale item details
//     $items = DB::table('sale_descriptions')
//         ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
//         ->join('accounts', 'sales.party', '=', 'accounts.id')
//         ->join('states', 'accounts.state', '=', 'states.id')
//         ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
//         ->whereIn('sale_id', $b2cSaleIds)
//        // ->where('manage_items.item_type', 'taxable')// added now but ye isliye comment kar diya verna taxable bhi 
//         ->select(
//             'sale_descriptions.id as sale_desc_id',
//             'sale_descriptions.sale_id',
//             'states.name as state_name',
//             'manage_items.gst_rate', 
//             'manage_items.item_type',
//             'sale_descriptions.amount'
//         )
//         ->get();

//     // Step 4: Get all sundries
//     $sundryDetails = DB::table('sale_sundries')
//         ->whereIn('sale_id', $b2cSaleIds)
//         ->select('sale_id', 'bill_sundry', 'amount')
//         ->get()
//         ->groupBy('sale_id');
      
      

//     // Step 5: Get ledger entries for bill_sundry_type='Other' and adjust_sale_amt='No'
//     $ledgerAdjustments = [];
//     foreach ($sundryDetails as $saleId => $entries) {
//         foreach ($entries as $entry) {
           
//             $bs = $sundries[$entry->bill_sundry] ?? null;
            
//             if (!$bs) continue;
           

//             if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
          
//                 $ledger_amount = DB::table('account_ledger')
//                     ->where('company_id', $user_company_id)
//                     ->where('entry_type', 1)
//                     ->where('entry_type_id', $saleId)
//                     ->where('account_id', $bs->sale_amt_account)
//                     ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

        

//                 $ledgerAdjustments[$saleId][] = [
//                     'amount' => $ledger_amount,
//                     'type' => $bs->bill_sundry_type
//                 ];
//             }
//         }
//     }

//     // Step 6: Group and calculate values
//     $grouped = [];

//     foreach ($items as $item) {
//         $sale_id = $item->sale_id;
//         $state = $item->state_name;
//         $rate = $item->gst_rate;
//         $item_amount = $item->amount;
//         $item_type = $item->item_type;

//         $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
//         if ($total_item_amount == 0) continue;

//         $adjusted_value = 0;

//         // Adjust from sundries where adjust_sale_amt = 'Yes' and bill_sundry_type = 'Other'
//         if (isset($sundryDetails[$sale_id])) {
//             foreach ($sundryDetails[$sale_id] as $sundryEntry) {
//                 $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
//                 if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

//                 $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
//                 if ($bs->bill_sundry_type == 'subtractive') {
//                     $adjusted_value -= $share;
//                 } else {
//                     $adjusted_value += $share;
//                 }
//             }
//         }

//         // Adjust from ledgers where adjust_sale_amt = 'No' and bill_sundry_type = 'Other'
//         if (isset($ledgerAdjustments[$sale_id])) {
//             foreach ($ledgerAdjustments[$sale_id] as $adj) {
//                 $share = ($item_amount / $total_item_amount) * $adj['amount'];
//                 if ($adj['type'] == 'subtractive') {
//                     $adjusted_value -= $share;
//                 } else {
//                     $adjusted_value += $share;
//                 }
//             }
//         }

//         $taxable_value = $item_amount + $adjusted_value;
       
//         // Compute taxes
//         $igst = 0;
//         $cgst = 0;
//         $sgst = 0;
//         $merchant_state_code = substr($merchant_gst, 0, 2); // e.g. '07'
//         $customer_state_code = State::where('name', $state)->value('state_code'); // assuming states table has GST code
        
//         if ($merchant_state_code == $customer_state_code) {
//             $cgst = ($rate / 2 / 100) * $taxable_value;
//             $sgst = ($rate / 2 / 100) * $taxable_value;
//         } else {
//             $igst = ($rate / 100) * $taxable_value;
//         }
        
//        if ($item_type === 'taxable') {
//         $key = $state . '|' . $rate;

//         if (!isset($grouped[$key])) {
//             $grouped[$key] = [
//                 'state' => $state,
//                 'rate' => $rate,
//                 'taxable_value' => 0,
//                 'igst' => 0,
//                 'cgst' => 0,
//                 'sgst' => 0,
//             ];
//         }

//         $grouped[$key]['taxable_value'] += $taxable_value;
//         $grouped[$key]['igst'] += $igst;
//         $grouped[$key]['cgst'] += $cgst;
//         $grouped[$key]['sgst'] += $sgst;
//     }
// }

//     $data = array_values($grouped);

//     return view('gstReturn.b2c_statewise', compact('data', 'merchant_gst', 'company_id', 'from_date', 'to_date'));
// }


   










public function B2Bdetailed(Request $request)
{
    // Validate required filters
    $merchant_gst = $request->merchant_gst;
    $company_id = Session::get('user_company_id');
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    if (!$merchant_gst || !$company_id || !$from_date || !$to_date) {
        return response()->json(['error' => 'Missing required filters'], 400);
    }

    $groupIds = CommonHelper::getAllGroupIds([3, 11]);

    $accountDropdown = DB::table('accounts')
    ->where('company_id', $company_id)
    ->whereIn('under_group', $groupIds)
    ->select('account_name', 'gstin')
    ->orderBy('account_name')
    ->get();

    $user_company_id = Session::get('user_company_id');

    // Step 1: Fetch all Bill Sundries for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');

    // Step 2: Get matching sales data
    $sales = DB::table('sales')
        ->where('sales.merchant_gst', $merchant_gst)
        ->join('accounts', 'accounts.id', '=', 'sales.party')
        ->join('states', 'states.id', '=', 'sales.billing_state')
        ->where('sales.company_id', $company_id)
        ->whereDate('sales.date', '>=', $from_date)
        ->whereDate('sales.date', '<=', $to_date)
        ->whereNotNull('sales.billing_gst')
        ->Where('billing_gst','!=','')
        ->where('sales.delete', '0')
        ->where('sales.status', '1')
        ->select(
            'sales.id',
            'sales.date',
            'sales.voucher_no_prefix',
            'sales.total',
            'sales.billing_gst',
            'accounts.account_name as name',
            'states.name as POS',
            'sales.reverse_charge'
        )
        ->get();

    // Step 3: Fetch sale descriptions (items)
    $items = DB::table('sale_descriptions')
        ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
        ->join('accounts', 'sales.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_id', $sales->pluck('id'))
        ->select(
            'sale_descriptions.id as sale_desc_id',
            'sale_descriptions.sale_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_descriptions.amount',
            'manage_items.item_type',
            'sales.voucher_no_prefix'
        )
        ->get();

    // Step 4: Fetch all sundries related to the sales
    $sundryDetails = DB::table('sale_sundries')
        ->whereIn('sale_id', $sales->pluck('id'))
        ->select('sale_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_id');

    // Step 5: Calculate ledger adjustments based on 'adjust_sale_amt' and 'OTHER' nature
    $ledgerAdjustments = [];
    foreach ($sundryDetails as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;

            if (!$bs) continue;

            // Include only if sundry is 'OTHER' and adjust_sale_amt = 'No'
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1) // Entry type for sale
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // Step 6: Group items by voucher_no_prefix and rate, and calculate taxes
    $grouped = [];
    foreach ($items as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->voucher_no_prefix;
         $item_type = $item->item_type;

        // Sum all items in the sale to get the total amount for allocation of sundries
        $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
        if ($total_item_amount == 0) continue; // Skip if no total amount

        $adjusted_value = 0;

        // Adjust from sundries where adjust_sale_amt = 'Yes' and bill_sundry_type = 'Other'
        if (isset($sundryDetails[$sale_id])) {
            foreach ($sundryDetails[$sale_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledgers where adjust_sale_amt = 'No' and bill_sundry_type = 'Other'
        if (isset($ledgerAdjustments[$sale_id])) {
            foreach ($ledgerAdjustments[$sale_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;

        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2); // Get first 2 digits of merchant's GSTIN
        $customer_state_code = State::where('name', $state)->value('state_code'); // assuming states table has GST code

        // Tax calculation logic based on GSTIN state match
        if ($merchant_state_code == $customer_state_code) {
            // Same state - CGST and SGST
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            // Different state - IGST
            $igst = ($rate / 100) * $taxable_value;
        }

       if ($item_type === 'taxable') {
        $sale = $sales->where('id', $sale_id)->first();
        $key = $sale->voucher_no_prefix . '|' . $rate;

        if (!isset($grouped[$key])) {
        $grouped[$key] = [
        'voucher_no_prefix' => $sale->voucher_no_prefix,
        'rate' => $rate,
        'invoice_date' => $sale->date,
        'sales_id' => $sale->id,
        'billing_gst' => $sale->billing_gst,
        'name' => $sale->name,
        'total' => $sale->total,
        'POS' => $sale->POS,
        'reverse_charge' => $sale->reverse_charge,
        'taxable_value' => 0,
        'igst' => 0,
        'cgst' => 0,
        'sgst' => 0,
        ];
        }


        // Accumulate the values for the group
        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }
}

// At the bottom of controller, before return:
$billing_gst_filter = $request->billing_gst;
$name_filter = $request->name;
$invoice_date_filter = $request->invoice_date;
$rate_filter = $request->rate;

// Filter grouped array by these values
if ($billing_gst_filter || $name_filter || $invoice_date_filter || $rate_filter) {
    $grouped = collect($grouped)->filter(function ($row) use ($billing_gst_filter, $name_filter, $invoice_date_filter, $rate_filter) {
        return (!$billing_gst_filter || str_contains($row['billing_gst'], $billing_gst_filter)) &&
               (!$name_filter || str_contains(strtolower($row['name']), strtolower($name_filter))) &&
               (!$invoice_date_filter || \Carbon\Carbon::parse($row['invoice_date'])->format('Y-m-d') === $invoice_date_filter) &&
               (!$rate_filter || $row['rate'] == $rate_filter);
    })->all(); // return as array again
}

    // Return the grouped data to the view
    
    return view('gstReturn.b2bDetailed', ['grouped' => $grouped, 'accountDropdown' => $accountDropdown, 'merchant_gst' => $merchant_gst , 'from_date' => $from_date , 'to_date' => $to_date ,'company_id' => $company_id ]);

}




public function sendGstr1ToGSTMaster(Request $request){
    ini_set('serialize_precision','-1');
    $b2b_arr = [];
    $merchant_gst = $request->merchant_gst;
    $company_id = Session::get('user_company_id');
    $from_date = $request->from_date;
    $to_date = $request->to_date;
    if (!$merchant_gst || !$company_id || !$from_date || !$to_date) {
        return response()->json(['error' => 'Missing required filters'], 400);
    }
    $groupIds = CommonHelper::getAllGroupIds([3, 11]);
    $accountDropdown = DB::table('accounts')
        ->where('company_id', $company_id)
        ->whereIn('under_group', $groupIds)
        ->select('account_name', 'gstin')
        ->orderBy('account_name')
        ->get();
    $user_company_id = Session::get('user_company_id');
    // Step 1: Fetch all Bill Sundries for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
    // Step 2: Get matching sales data
    $sales = DB::table('sales')
        ->where('sales.merchant_gst', $merchant_gst)
        ->join('accounts', 'accounts.id', '=', 'sales.party')
        ->join('states', 'states.id', '=', 'sales.billing_state')
        ->where('sales.company_id', $company_id)
        ->whereDate('sales.date', '>=', $from_date)
        ->whereDate('sales.date', '<=', $to_date)
        ->whereNotNull('sales.billing_gst')
        ->Where('billing_gst','!=','')
        ->where('sales.delete', '0')
        ->where('sales.status', '1')
        ->select(
            'sales.id',
            'sales.date',
            'sales.voucher_no_prefix',
            'sales.total',
            'sales.billing_gst',
            'accounts.account_name as name',
            'states.name as POS',
            'sales.reverse_charge'
        )
        //->where('voucher_no_prefix',307)
        ->get();
    
    // Step 3: Fetch sale descriptions (items)
    $items = DB::table('sale_descriptions')
        ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
        ->join('accounts', 'sales.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_id', $sales->pluck('id'))
        ->select(
            'sale_descriptions.id as sale_desc_id',
            'sale_descriptions.sale_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_descriptions.amount',
            'manage_items.item_type',
            'sales.voucher_no_prefix'
        )
        ->get();
        
    // Step 4: Fetch all sundries related to the sales
    $sundryDetails = DB::table('sale_sundries')
        ->whereIn('sale_id', $sales->pluck('id'))
        ->select('sale_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_id');

    // Step 5: Calculate ledger adjustments based on 'adjust_sale_amt' and 'OTHER' nature
    $ledgerAdjustments = [];
    foreach ($sundryDetails as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            // Include only if sundry is 'OTHER' and adjust_sale_amt = 'No'
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1) // Entry type for sale
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    // Step 6: Group items by voucher_no_prefix and rate, and calculate taxes
    $grouped = [];
   
    foreach ($items as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->voucher_no_prefix;
         $item_type = $item->item_type;
        // Sum all items in the sale to get the total amount for allocation of sundries
        $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
        if ($total_item_amount == 0) continue; // Skip if no total amount
        $adjusted_value = 0;
        // Adjust from sundries where adjust_sale_amt = 'Yes' and bill_sundry_type = 'Other'
        if (isset($sundryDetails[$sale_id])) {
            foreach ($sundryDetails[$sale_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }
        // Adjust from ledgers where adjust_sale_amt = 'No' and bill_sundry_type = 'Other'
        if (isset($ledgerAdjustments[$sale_id])) {
            foreach ($ledgerAdjustments[$sale_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }
        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;
        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2); // Get first 2 digits of merchant's GSTIN
        $customer_state_code = State::where('name', $state)->value('state_code'); // assuming states table has GST code
        // Tax calculation logic based on GSTIN state match
        if ($merchant_state_code == $customer_state_code) {
            // Same state - CGST and SGST
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            // Different state - IGST
            $igst = ($rate / 100) * $taxable_value;
        }
        
        if ($item_type === 'taxable') {
            $sale = $sales->where('id', $sale_id)->first();
            $ctin = $sale->billing_gst;
            $inum = $sale->voucher_no_prefix;
            if (!isset($grouped[$ctin])) {
                $grouped[$ctin] = [];
            }
            $invoice_key = $inum;
            $existing_invoice = collect($grouped[$ctin])->firstWhere('inum', $invoice_key);
            if (!$existing_invoice) {
                
                $invoice_data = [
                    "inum" =>$inum,
                    "idt" =>\Carbon\Carbon::parse($sale->date)->format('d-m-Y'),
                    "val" =>(float)$sale->total,
                    "pos" =>State::where('name', $sale->POS)->value('state_code'),
                    "rchrg" =>$sale->reverse_charge ? 'Y' : 'N',
                    //"etin" =>$ctin,
                    "inv_typ" =>'R',
                    //"diff_percent"=>0.65, // if applicable,
                    "itms" =>[]
                ];
                array_push($grouped[$ctin], $invoice_data);
                
                //$grouped[$ctin][] = $invoice_data;
            }
            
            // Find the current invoice again to push items
            foreach ($grouped[$ctin] as &$invoice_ref) {

                if ($invoice_ref['inum'] === $inum) {
                    if($igst!=""){
                        $invoice_ref['itms'][] = [
                            "num" => count($invoice_ref['itms']) + 1,
                            "itm_det" => [
                                "rt" => (float)$rate, 
                                "txval" => (float)round($taxable_value, 2),
                                "iamt" => (float)round($igst, 2),
                                "csamt"=> (float)0

                            ]
                        ];
                    }else{
                        $invoice_ref['itms'][] = [
                            "num" => count($invoice_ref['itms']) + 1,
                            "itm_det" => [                                
                                "txval" => (float)round($taxable_value, 2),
                                "rt" => (float)$rate,
                                "camt" => (float)round($cgst, 2),
                                "samt" => (float)round($sgst, 2),
                                "iamt" => (float)round($igst, 2),
                                "csamt"=> (float)0

                            ]
                        ];
                    }
                    
                    //$b2b_arr[$ctin] = $invoice_ref;
                    array_push($b2b_arr, array("ctin"=>$ctin,"inv"=>$invoice_ref));
                    break;
                }
            }
            
            
        }
    }
    $check_inv_arr = [];
    foreach ($b2b_arr as $in=>$invs) {        
        $k = $invs['ctin']."_".$invs['inv']['inum'];
        $check_inv_arr[$k] = $invs;
    }
    foreach ($check_inv_arr as $key => &$invoice) {
            if (!isset($invoice['inv']['itms']) || !is_array($invoice['inv']['itms'])) {
                continue;
            }
            $item_ind = 1;
            $merged = [];

            foreach ($invoice['inv']['itms'] as $item) {
                $rt = $item['itm_det']['rt'];

                if (!isset($merged[$rt])) {
                    // First time seeing this rate
                    $merged[$rt] = [
                        'num' => $item_ind, // or keep original numbering if needed
                        'itm_det' => [
                            'txval' => 0,
                            'rt' => $rt,
                            'camt' => 0,
                            'samt' => 0,
                            'iamt' => 0,
                            'csamt' => 0
                        ]
                    ];
                    $item_ind++;
                }

                // Add values
                $merged[$rt]['itm_det']['txval'] += $item['itm_det']['txval'];
                if(isset($item['itm_det']['camt'])){
                    $merged[$rt]['itm_det']['camt']  += $item['itm_det']['camt'];
                }
                if(isset($item['itm_det']['samt'])){
                    $merged[$rt]['itm_det']['samt']  += $item['itm_det']['samt'];
                }
                if(isset($item['itm_det']['iamt'])){
                    $merged[$rt]['itm_det']['iamt']  += $item['itm_det']['iamt'];
                }
                $merged[$rt]['itm_det']['txval'] = round($merged[$rt]['itm_det']['txval'],2);
                $merged[$rt]['itm_det']['camt'] = round($merged[$rt]['itm_det']['camt'],2);
                $merged[$rt]['itm_det']['samt'] = round($merged[$rt]['itm_det']['samt'],2);
                $merged[$rt]['itm_det']['iamt'] = round($merged[$rt]['itm_det']['iamt'],2);
                
                
                $merged[$rt]['itm_det']['csamt'] += $item['itm_det']['csamt'];
                
            }

            // Reset array keys (optional)
            $invoice['inv']['itms'] = array_values($merged);
        }
        unset($invoice);

    // foreach ($check_inv_arr as $key => $data) {
    //     if (!empty($data['inv']['itms']) && count($data['inv']['itms']) > 1) {
    //         $merged = [
    //             'num' => 1,
    //             'itm_det' => [
    //                 'txval' => 0,
    //                 'rt'    => $data['inv']['itms'][0]['itm_det']['rt'], // keep first rt
    //                 'camt'  => 0,
    //                 'samt'  => 0,
    //                 'iamt'  => 0,
    //                 'csamt' => 0
    //             ]
    //         ];

    //         foreach ($data['inv']['itms'] as $item) {
    //             $merged['itm_det']['txval'] += $item['itm_det']['txval'];
    //             if(isset($item['itm_det']['camt'])){
    //                 $merged['itm_det']['camt']  += $item['itm_det']['camt'];
    //             }
    //             if(isset($item['itm_det']['samt'])){
    //                 $merged['itm_det']['samt']  += $item['itm_det']['samt'];
    //             }
    //             if(isset($item['itm_det']['iamt'])){
    //                 $merged['itm_det']['iamt']  += $item['itm_det']['iamt'];
    //             }
    //             $merged['itm_det']['txval'] = round($merged['itm_det']['txval'],2);
    //             $merged['itm_det']['camt'] = round($merged['itm_det']['camt'],2);
    //             $merged['itm_det']['samt'] = round($merged['itm_det']['samt'],2);
    //             $merged['itm_det']['iamt'] = round($merged['itm_det']['iamt'],2);
    //             $merged['itm_det']['csamt'] += $item['itm_det']['csamt'];
    //         }

    //         // Replace with merged single item
    //         $check_inv_arr[$key]['inv']['itms'] = [$merged];
    //     }
    // }

//    echo "<pre>";
//     print_r($check_inv_arr);
//     echo "</pre>";
  

    // Output the result

   
    $new_arr = [];
    $ctin_seen = [];
    foreach ($check_inv_arr as $ke=>$entry) {
        $ctin = $entry['ctin'];
        $inv  = $entry['inv'];
        if (!isset($ctin_seen[$ctin])) {
            $ctin_seen[$ctin] = [
                'ctin' => $ctin,
                'inv' => []
            ];
        }
        array_push($ctin_seen[$ctin]['inv'], $inv);
    }
    //$b2b_arr = array_values($ctin_seen);
    foreach ($ctin_seen as $ctin => $data) {
        $new_arr[] = [
            "ctin" => $data['ctin'],
            "inv" => $data['inv']
        ];
    }
//    echo "<pre>";
//             print_r($new_arr);
//             echo "</pre>";
//     die;
    //for debit credit note registered 
    // Step 1: Fetch all Bill Sundries for the company   

    // -------------------- CREDIT NOTES FETCH --------------------
    $creditSales = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type', 'WITH ITEM')
            ->whereNotNull('sales_returns.billing_gst')
        ->Where('billing_gst','!=','')
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');

    $creditSales1 = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->whereNotNull('sales_returns.billing_gst')
        ->Where('billing_gst','!=','')
            ->where(function ($q) {
                $q->where('sr_type', 'WITHOUT ITEM')
                    ->orWhere('sr_type', 'RATE DIFFERENCE');
            })
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');

    $creditItems = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('sale_return_id', $creditSales)
            ->where('sale_return_descriptions.status', '1')
            ->where('sale_return_descriptions.delete', '0')
            ->select(
                'sale_return_descriptions.sale_return_id',
                'states.name as state_name',
                'sale_return_descriptions.qty',
                'manage_items.gst_rate',
                'sale_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code',
            )
            ->get();

    $creditItems1 = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereNotNull('sales_returns.billing_gst')
        ->Where('billing_gst','!=','')
            ->whereIn('sale_return_id', $creditSales1)
            ->where('sale_return_descriptions.status', '1')
            ->where('sale_return_descriptions.delete', '0')
            ->select(
                'sale_return_descriptions.sale_return_id',
                'states.name as state_name',
                DB::raw('0 as qty'), // Override qty with 0
                'manage_items.gst_rate',
                'sale_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code',
            )
            ->get();

    $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
            ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
            ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
            ->where('sale_return_without_gst_entry.percentage', '>', 0)
            ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales1)
            ->where('sale_return_without_gst_entry.status', '1')
            ->where('sale_return_without_gst_entry.delete', '0')
            ->select(
                'sale_return_without_gst_entry.sale_return_id as sale_return_id',
                'states.name as state_name',
                DB::raw('0 as qty'),
                'sale_return_without_gst_entry.percentage as gst_rate',
                'sale_return_without_gst_entry.debit as amount',
                'sale_return_without_gst_entry.hsn_code',
                'sale_return_without_gst_entry.unit_code'
            )
            ->get();

    $creditItems = $creditItems->merge($creditItems1)->merge($creditWithoutGstItems);
    $creditSundryDetails = DB::table('sale_return_sundries')
            ->whereIn('sale_return_id', $creditSales)
            ->select('sale_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_return_id');

    $ledgerAdjustments_credit = [];
    foreach ($creditSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_credit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    // ---------- DEBIT NOTES (Purchase returns) -----------
    $debitSales = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->where('purchase_returns.company_id', $company_id)
        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where('sr_type', 'WITH ITEM')
        ->whereNotNull('purchase_returns.billing_gst')
    ->Where('billing_gst','!=','')
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->pluck('purchase_returns.id');

    $debitSales1 = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->where('purchase_returns.company_id', $company_id)
        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where(function ($q) {
            $q->where('sr_type', 'WITHOUT ITEM')
                ->orWhere('sr_type', 'RATE DIFFERENCE');
        })
        ->whereNotNull('purchase_returns.billing_gst')
    ->Where('billing_gst','!=','')
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->pluck('purchase_returns.id');

    $debitItems = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('units', 'manage_items.u_name', '=', 'units.id')
        ->whereIn('purchase_return_id', $debitSales)
        ->where('purchase_return_descriptions.status', '1')
        ->where('purchase_return_descriptions.delete', '0')
        ->select(
            'purchase_return_descriptions.purchase_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'purchase_return_descriptions.qty',
            'purchase_return_descriptions.amount',
            'units.unit_code',
            'manage_items.hsn_code'
        )
        ->get();

    $debitItems1 = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('units', 'manage_items.u_name', '=', 'units.id')
        ->where('purchase_return_descriptions.status', '1')
        ->where('purchase_return_descriptions.delete', '0')
        ->whereIn('purchase_return_id', $debitSales1)
        ->select(
            'purchase_return_descriptions.purchase_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            DB::raw('0 as qty'),
            'purchase_return_descriptions.amount',
            'units.unit_code',
            'manage_items.hsn_code'
        )
        ->get();

    $debitWithoutGstItems = DB::table('purchase_return_entries')
            ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
            ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->where('purchase_return_entries.percentage', '>', 0)
            ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->merge($debitSales1)) // Use merged IDs
            ->where('purchase_return_entries.status', '1')
            ->where('purchase_return_entries.delete', '0')
            ->select(
                'purchase_return_entries.purchase_return_id as purchase_return_id',
                'states.name as state_name',
                'purchase_return_entries.percentage as gst_rate',
                DB::raw('0 as qty'),
                'purchase_return_entries.debit as amount',
                'purchase_return_entries.hsn_code',
                'purchase_return_entries.unit_code'
            )
            ->get();
    $debitItems = $debitItems->merge($debitItems1)->merge($debitWithoutGstItems);
    $debitSundryDetails = DB::table('purchase_return_sundries')
            ->whereIn('purchase_return_id', $debitSales->merge($debitSales1)) // Use merged IDs
            ->select('purchase_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('purchase_return_id');

    $ledgerAdjustments_debit = [];
    foreach ($debitSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_debit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    $cdnr = [];
    // Helper function to add item rows to cdnr
    $addToCdnr = function ($entry, $ntty) use (&$cdnr, $merchant_gst) {
        $ctin = $entry['ctin'];
        $nt_num = $entry['nt_num'];
        $nt_dt = $entry['nt_dt'];
        $pos = $entry['pos'];
        $rchrg = $entry['rchrg'];
        $inv_typ = $entry['inv_typ'];
        $val = $entry['val'];
        $itemRow = $entry['itemRow'];
        if (!isset($cdnr[$ctin])) {
            $cdnr[$ctin] = [];
        }
        $existing = collect($cdnr[$ctin])->firstWhere('nt_num', $nt_num);
        if (!$existing) {
            $cdnr[$ctin][] = [
                'ntty' => $ntty,
                'nt_num' => $nt_num,
                'nt_dt' => $nt_dt,
                'pos' => $pos,
                'rchrg' => $rchrg,
                'inv_typ' => $inv_typ,
                'val' => $val,
                //'diff_percent' => null,
                'itms' => [$itemRow]
            ];
        } else {
            foreach ($cdnr[$ctin] as &$note) {
                if ($note['nt_num'] === $nt_num) {
                    $itemRow['num'] = count($note['itms']) + 1;
                    $note['itms'][] = $itemRow;
                }
            }
        }
    };
    $debit_note_arr = [];
    $addToDebitNote = function ($entry, $ntty) use (&$debit_note_arr, $merchant_gst) {
        $ctin = $entry['ctin'];
        $nt_num = "DR/".$entry['nt_num'];
        $nt_dt = $entry['nt_dt'];
        $pos = $entry['pos'];
        $rchrg = $entry['rchrg'];
        $inv_typ = $entry['inv_typ'];
        $val = $entry['val'];
        $itemRow = $entry['itemRow'];
        if (!isset($debit_note_arr[$ctin])) {
            $debit_note_arr[$ctin] = [];
        }
        $existing = collect($debit_note_arr[$ctin])->firstWhere('nt_num', $nt_num);
        if (!$existing) {
            $debit_note_arr[$ctin][] = [
                'ntty' => $ntty,
                'nt_num' => $nt_num,
                'nt_dt' => $nt_dt,
                'pos' => $pos,
                'rchrg' => $rchrg,
                'inv_typ' => $inv_typ,
                'val' => $val,
                //'diff_percent' => null,
                'itms' => [$itemRow]
            ];
        } else {
            foreach ($debit_note_arr[$ctin] as &$note) {
                if ($note['nt_num'] === $nt_num) {
                    $itemRow['num'] = count($note['itms']) + 1;
                    $note['itms'][] = $itemRow;
                }
            }
        }
    };
    // 📌 Process credit notes (sales_returns)
    foreach ($creditItems as $item) {
        $return_id = $item->sale_return_id;
        $item_amount = $item->amount;
        $rate = $item->gst_rate;
        $state = $item->state_name;

        $total_item_amount = $creditItems->where('sale_return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        if (isset($creditSundryDetails[$return_id])) {
            foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        if (isset($ledgerAdjustments_credit[$return_id])) {
            foreach ($ledgerAdjustments_credit[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $item_amount + $adjusted_value;

        $sale = DB::table('sales_returns')->where('id', $return_id)->first();
        if (!$sale) continue;

        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('id', $sale->billing_state)->value('state_code');
        $igst = $cgst = $sgst = 0;

        if ($merchant_state_code == $customer_state_code) {
            $cgst = round(($rate / 2 / 100) * $taxable_value, 2);
            $sgst = round(($rate / 2 / 100) * $taxable_value, 2);
        } else {
            $igst = round(($rate / 100) * $taxable_value, 2);
        }

        $entry = [
            'ctin' => $sale->billing_gst,
            'nt_num' => $sale->sr_prefix,
            'nt_dt' => \Carbon\Carbon::parse($sale->date)->format('d-m-Y'),
            'pos' => $customer_state_code,
            'rchrg' => 'N',
            'inv_typ' => 'R',
            'val' => round($sale->total, 2),
            'itemRow' => [
                'num' => 1,
                'itm_det' => [
                    'rt' => (float)$rate,
                    'txval' => round($taxable_value, 2),
                    'iamt' => $igst,
                    'camt' => $cgst,
                    'samt' => $sgst,
                    "csamt" => 0
                ]
            ]
        ];

        $addToCdnr($entry, 'C'); // Add as credit note
    }
    // 📌 Process debit notes (purchase_returns)
    foreach ($debitItems as $item) {
        $return_id = $item->purchase_return_id;
        $item_amount = $item->amount;
        $rate = $item->gst_rate;
        $state = $item->state_name;

        $total_item_amount = $debitItems->where('purchase_return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        if (isset($debitSundryDetails[$return_id])) {
            foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        if (isset($ledgerAdjustments_debit[$return_id])) {
            foreach ($ledgerAdjustments_debit[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $item_amount + $adjusted_value;

        $return = DB::table('purchase_returns')->where('id', $return_id)->first();
        if (!$return) continue;

        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('id', $return->billing_state)->value('state_code');
        $igst = $cgst = $sgst = 0;

        if ($merchant_state_code == $customer_state_code) {
            $cgst = round(($rate / 2 / 100) * $taxable_value, 2);
            $sgst = round(($rate / 2 / 100) * $taxable_value, 2);
        } else {
            $igst = round(($rate / 100) * $taxable_value, 2);
        }

        $entry = [
            'ctin' => $return->billing_gst,
            'nt_num' => $return->sr_prefix,
            'nt_dt' => \Carbon\Carbon::parse($return->date)->format('d-m-Y'),
            'pos' => $customer_state_code,
            'rchrg' => 'N',
            'inv_typ' => 'R',
            'val' => round($return->total, 2),
            'itemRow' => [
                'num' => 1,
                'itm_det' => [
                    'rt' => (float)$rate,
                    'txval' => round($taxable_value, 2),
                    'iamt' => $igst,
                    'camt' => $cgst,
                    'samt' => $sgst,
                    "csamt" => 0
                ]
            ]
        ];

        $addToDebitNote($entry, 'D'); // Add as debit note
    }
    // Final formatting
    $finalData = [];

    $final_notes = [];
    // Merge credit notes
    foreach ($cdnr as $ctin => $notes) {
        if (!isset($final_notes[$ctin])) {
            $final_notes[$ctin] = [];
        }
        $final_notes[$ctin] = array_merge($final_notes[$ctin], $notes);
    }

    // Merge debit notes
    foreach ($debit_note_arr as $ctin => $notes) {
        if (!isset($final_notes[$ctin])) {
            $final_notes[$ctin] = [];
        }
        $final_notes[$ctin] = array_merge($final_notes[$ctin], $notes);
    }

   

    foreach ($final_notes as $ctin => $notes) {
        $finalData[] = [
            'ctin' => $ctin,
            'nt' => $notes
        ];
    }
    // echo "<pre>";
    // print_r($final_notes);
    // echo "</pre>";
    // die;
    // Initialize arrays to hold data for B2B and B2C
    $finalDataB2B = [];
    $finalDataB2C = [];
    // --- Process for B2B ---
    $this->processHsnSummary(
        $merchant_gst,
        $company_id,
        $from_date,
        $to_date,
        $user_company_id,
        $sundries,
        true, // isB2B
        $finalDataB2B
    );
    // --- Process for B2C ---
    $this->processHsnSummary(
        $merchant_gst,
        $company_id,
        $from_date,
        $to_date,
        $user_company_id,
        $sundries,
        false, // isB2B
        $finalDataB2C
    );
    // Format the final output as requested for the API
    $formattedHsnData = [        
        "hsn_b2b" => [],
        "hsn_b2c" => []        
    ];
    $numB2B = 1;
    $hsn_arr = [];
    foreach ($finalDataB2B as $data) {
        $formattedHsnData["hsn_b2b"][] = [
        //$hsn_arr[] = [
            "num" => $numB2B++,
            "hsn_sc" => (String)$data['hsn'],
            "desc" => "Goods Description", // Placeholder as per example
            //"user_desc" => "Taxpayer Description", // Placeholder as per example
            "uqc" => substr($data['unit_code'], 0, 3),
            "qty" => round($data['qty'], 2),
            "rt" =>(int) round($data['rate'], 2),
            "txval" => (float)round($data['taxable_value'], 2),
            "iamt" => (float)round($data['igst'], 2),
            "camt" => (float)round($data['cgst'], 2),
            "samt" => (float)round($data['sgst'], 2),
            "csamt" => (float)0 // Not calculated in original logic, setting to 0 as per example
            
        ];
    }
    $numB2C = 1;
    foreach ($finalDataB2C as $data) {
        $formattedHsnData["hsn_b2c"][] = [
        //$hsn_arr[] = [
            "num" => $numB2C++,
            "hsn_sc" => (String)$data['hsn'],
            "desc" => "Goods Description", // Placeholder as per example
            //"user_desc" => "Taxpayer Description", // Placeholder as per example
            "uqc" => substr($data['unit_code'], 0, 3),
            "qty" => round($data['qty'], 2),
            "rt" => (int)round($data['rate'], 2),
            "txval" => (float)round($data['taxable_value'], 2),
            "iamt" => (float)round($data['igst'], 2),
            "camt" => (float)round($data['cgst'], 2),
            "samt" => (float)round($data['sgst'], 2),
            "csamt" => (float)0// Not calculated in original logic, setting to 0 as per example
            
        ];
    }
    // return response()->json($formattedHsnData);
    $salesGrouped = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('merchant_gst',$merchant_gst)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0') // Exclude soft-deleted records
        ->whereNotNull('voucher_no_prefix') // Ensure voucher_no_prefix is present
        ->select('voucher_no_prefix', 'series_no', 'status')
        ->orderBy('series_no')
        ->orderBy('voucher_no_prefix')
        ->get()
        ->groupBy('series_no');

    $SalesdocumentSummary = [];
    foreach ($salesGrouped as $series => $records) {
        $total = $records->count();
        $cancelled = $records->where('status', 2)->count();
        $from = $records->first()->voucher_no_prefix ?? '-';
        $to = $records->last()->voucher_no_prefix ?? '-';

        $SalesdocumentSummary[] = [
            'series_no' => $series ?? '-',
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'cancelled' => $cancelled,
            'net_issued' => $total - $cancelled,
        ];
    }
    $allCreditNotes = DB::table('sales_returns')
        ->where('company_id', $company_id)
        ->where('merchant_gst',$merchant_gst)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0')
        ->whereNotNull('sr_prefix')
        ->select('sr_prefix', 'series_no', 'status', 'voucher_type','sr_nature')
        ->orderBy('series_no')
        ->orderBy('sr_prefix')
        ->get()
        ->groupBy('series_no');
    $CreditNotedocumentSummary = [];
    foreach ($allCreditNotes as $series => $records) {
        $from = $records->first()->sr_prefix ?? '-';
        $to = $records->last()->sr_prefix ?? '-';

        // Filter only SALE voucher_type for total and cancelled
        $saleRecords = $records->where('voucher_type', 'SALE')
                            ->where('sr_nature', 'WITH GST');
                    

        $total = $saleRecords->count();
        $cancelled = $saleRecords->where('status', 2)->count();

        $CreditNotedocumentSummary[] = [
            'series_no'   => $series ?? '-',
            'from'        => $from,
            'to'          => $to,
            'total'       => $total,
            'cancelled'   => $cancelled,
            'net_issued'  => $total - $cancelled,
        ];
    }
    $allDebitNotes = DB::table('purchase_returns')
        ->where('company_id', $company_id)
        ->where('merchant_gst',$merchant_gst)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0')
        ->whereNotNull('sr_prefix')
        ->select('sr_prefix', 'series_no', 'status', 'voucher_type','sr_nature')
        ->orderBy('series_no')
        ->orderBy('sr_prefix')
        ->get()
        ->groupBy('series_no');
    $DebitNotedocumentSummary = [];
    foreach ($allDebitNotes as $series => $records) {
        $from = $records->first()->sr_prefix ?? '-';
        $to = $records->last()->sr_prefix ?? '-';

        // Filter only SALE voucher_type for total and cancelled
        $saleRecords = $records->where('voucher_type', 'SALE')
                            ->where('sr_nature', 'WITH GST');

        $total = $saleRecords->count();
        $cancelled = $saleRecords->where('status', 2)->count();

        $DebitNotedocumentSummary[] = [
            'series_no'   => $series ?? '-',
            'from'        => $from,
            'to'          => $to,
            'total'       => $total,
            'cancelled'   => $cancelled,
            'net_issued'  => $total - $cancelled,
        ];
    }
    
    $docIssue = ['doc_det' => []];
    // Helper function to map summaries to required doc format
    function mapSummaryToDocs($summaryArray) {
        $docs = [];
        $i = 1;
        foreach ($summaryArray as $summary) {
            $docs[] = [
                'num' => $i++,
                'from' => $summary['from'],
                'to' => $summary['to'],
                'totnum' => $summary['total'],
                'cancel' => $summary['cancelled'],
                'net_issue' => $summary['net_issued']
            ];
        }
        return $docs;
    }
    // Add each doc type to the final array
    $docIssue_index = 1;
    if (count($SalesdocumentSummary) > 0) {
        $docIssue['doc_det'][] = [
            'doc_num' => 1,
            'docs' => mapSummaryToDocs($SalesdocumentSummary)
        ];
        $docIssue_index++;
    } 
    if (count($CreditNotedocumentSummary) > 0) {
        $docIssue['doc_det'][] = [
            'doc_num' => 5,
            'docs' => mapSummaryToDocs($CreditNotedocumentSummary)
        ];
        $docIssue_index++;
    }
    if (count($DebitNotedocumentSummary) > 0) {
        $docIssue['doc_det'][] = [
            'doc_num' => 4,
            'docs' => mapSummaryToDocs($DebitNotedocumentSummary)
        ];
        $docIssue_index++;
    }
    $txn = "";
    //Get GST Username
    $company = Companies::select('gst_config_type')
                            ->where('id', Session::get('user_company_id'))
                            ->first();
    if($company->gst_config_type == "single_gst"){
        $gst = DB::table('gst_settings')
                        ->select('gst_username','einvoice')
                        ->where([
                            'company_id' => Session::get('user_company_id'),
                            'gst_no' => $merchant_gst
                        ])
                        ->first();
        $gst_user_name = $gst->gst_username;
        $einvoice_status = $gst->einvoice;
    }else if($company->gst_config_type == "multiple_gst"){            
        $gst = DB::table('gst_settings_multiple')
                        ->select('gst_username','einvoice')
                        ->where([
                            'company_id' => Session::get('user_company_id'),
                            'gst_no' => $merchant_gst
                        ])
                        ->first();
        $gst_user_name = $gst->gst_username;
        $einvoice_status = $gst->einvoice;
    }
    if($gst_user_name==""){
        $response = array(
                'status' => false,
                'message' => 'Please Enter GST User Name In GST Configuration.'
            );
        return json_encode($response);
    }
    $turnover = DB::table('company_turnovers')
                        ->select('amount')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('gstin',$merchant_gst)
                        ->where('financial_year', Session::get('default_fy'))
                        ->first();
    if(!$turnover){
        $response = array(
                'status' => false,
                'message' => 'Please Add Gross TurnOver'
            );
        return json_encode($response);
    }
    if($einvoice_status==1){
        $gstr1_request = array(
            "gstin"=>$merchant_gst,
            "fp"=>date('mY', strtotime($from_date)),
            "gt"=>(float)$turnover->amount,
            "cur_gt"=>0,
            "doc_issue"=>$docIssue,
            "hsn"=>$formattedHsnData
        );
    }else{
        $gstr1_request = array(
            "gstin"=>$merchant_gst,
            "fp"=>date('mY', strtotime($from_date)),
            "gt"=>(float)$turnover->amount,
            "cur_gt"=>0,
            "b2b"=>$new_arr,
            "cdnr"=>$finalData,
            "doc_issue"=>$docIssue,
            "hsn"=>$formattedHsnData
        );
        
    }
    
    // echo "<pre>";
    // echo json_encode($gstr1_request);
    //die;
    //Call retsave Api 
    
    $ret_period = date('mY',strtotime($from_date));
    $state_code = substr($merchant_gst,0,2);
    //Check and generate token
    $gst_token = gstToken::select('txn','created_at')
                        ->where('company_gstin',$merchant_gst)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('status',1)
                        ->orderBy('id','desc')
                        ->first();
    if($gst_token){
        $token_expiry = date('d-m-Y H:i:s',strtotime('+6 hour',strtotime($gst_token->created_at)));
        $current_time = date('d-m-Y H:i:s');
        if(strtotime($token_expiry)<strtotime($current_time)){
            $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$merchant_gst);
            if($token_res==0){
                $response = array(
                    'status' => false,
                    'message' => 'Something Went Wrong In Token Generation'
                );
                return json_encode($response);
            }
            $response = array(
                'status' => true,
                'message' => 'TOKEN-OTP'
            );
            return json_encode($response);
        }
        $txn = $gst_token->txn;
    }else{
        $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$merchant_gst);
        if($token_res==0){
                $response = array(
                    'status' => false,
                    'message' => 'Something Went Wrong In Token Generation'
                );
                return json_encode($response);
            }
        $response = array(
                'status' => true,
                'message' => 'TOKEN-OTP'
        );
        return json_encode($response);
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mastergst.com/gstr1/retsave?email=pram92500@gmail.com',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS =>json_encode($gstr1_request),
        CURLOPT_HTTPHEADER => array(
            'gstin:'.$merchant_gst,
            'ret_period:'.$ret_period,
            'gst_username:'.$gst_user_name,
            'state_cd:'.$state_code,
            'ip_address: 162.241.85.89',
            'txn:'.$txn,
            'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
            'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67',        
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if($response){
        $result = json_decode($response);
        // echo "<pre>";
        // print_r($result);echo "**".$result->data->reference_id."--".$result->status_cd;
        // die;
        if(isset($result->status_cd) && $result->status_cd==1 ){
            $curl = curl_init();
            curl_setopt_array($curl, array(
               CURLOPT_URL => 'https://api.mastergst.com/gstr/retstatus?email=pram92500@gmail.com&gstin='.$merchant_gst.'&returnperiod='.$ret_period.'&refid='.$result->data->reference_id,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => '',
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 0,
               CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_CUSTOMREQUEST => 'GET',
               //CURLOPT_POSTFIELDS =>json_encode($gstr1_requset),
               CURLOPT_HTTPHEADER => array(
                  'gst_username:'.$gst_user_name,
                  'state_cd:'.$state_code,
                  'ip_address: 162.241.85.89',
                  'txn:'.$txn,
                  'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                  'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67',         
                  'Content-Type: application/json'
               ),
            ));
            $res = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if($res){
               $rult = json_decode($res);
               // echo "<pre>";
               // print_r($rult);die;
               if(isset($rult->status_cd) && $rult->status_cd==1){
                  if($rult->data->status_cd=="P"){                     
                     echo json_encode(array("status"=>true,"message"=>"Data Saved Successfully."));
                  }else if($rult->data->status_cd=="IP"){                     
                     echo json_encode(array("status"=>true,"message"=>"In Processing....Please Wait Processed Within 20 minutes"));
                  }else{
                     echo json_encode(array("status"=>false,"message"=>$rult->errorReport));
                  }                  
               }else{
                  echo json_encode(array("status"=>false,"message"=>$rult->errorReport));
               }
            }
        }else{
            echo json_encode(array("status"=>false,"message"=>$result->error));            
        }
    }
    // curl_close($curl); 
    // echo "<pre>";
    
    // print_r($response);
    // die;
    // dd($docIssue);
    // return response()->json($docIssue);

}








public function b2cLargedetailed(Request $request){
     $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $user_company_id = Session::get('user_company_id');

    // Step 1: Fetch all Bill Sundrys for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
   

    // Step 2: Get B2C Sale IDs
    $b2cSaleIds = DB::table('sales')
        ->where('merchant_gst', $merchant_gst)
        ->where('company_id', $company_id)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0')
        ->where('status', '1')
         ->where(function($query) {
            $query->whereNull('billing_gst')
                  ->orWhere('billing_gst', '');
                                  })
        ->where('total', '>', 250000)
        ->pluck('id');

        
    if ($b2cSaleIds->isEmpty()) {
        return view('gstReturn.b2c_large_detailed', ['data' => []]);
    }

    // Step 3: Get sale item details
    $items = DB::table('sale_descriptions')
    ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
    ->join('accounts', 'sales.party', '=', 'accounts.id')
    ->join('states as account_state', 'accounts.state', '=', 'account_state.id') // alias 1
    ->join('states as billing_state', 'sales.billing_state', '=', 'billing_state.id') // alias 2
    ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
    ->whereIn('sale_descriptions.sale_id', $b2cSaleIds)
    ->select(
        'sale_descriptions.id as sale_desc_id',
        'sale_descriptions.sale_id',
        'account_state.name as state_name', // recipient state
        'billing_state.name as POS', // POS (place of supply)
        'billing_state.id as billing_state_id',
        'manage_items.gst_rate',
        'manage_items.item_type',
        'sale_descriptions.amount',
        'sales.voucher_no_prefix',
        'sales.date as date',
        'sales.total as total'
       
    )
    ->get();

    // Step 4: Get all sundries
    $sundryDetails = DB::table('sale_sundries')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select('sale_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_id');
      
      

    // Step 5: Get ledger entries for bill_sundry_type='Other' and adjust_sale_amt='No'
    $ledgerAdjustments = [];
    foreach ($sundryDetails as $saleId => $entries) {
        foreach ($entries as $entry) {
           
            $bs = $sundries[$entry->bill_sundry] ?? null;
            
            if (!$bs) continue;
           

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
          
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

        

                $ledgerAdjustments[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // Step 6: Group and calculate values
    $grouped = [];

    foreach ($items as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->voucher_no_prefix;
        $item_type = $item->item_type;


        $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust from sundries where adjust_sale_amt = 'Yes' and bill_sundry_type = 'Other'
        if (isset($sundryDetails[$sale_id])) {
            foreach ($sundryDetails[$sale_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledgers where adjust_sale_amt = 'No' and bill_sundry_type = 'Other'
        if (isset($ledgerAdjustments[$sale_id])) {
            foreach ($ledgerAdjustments[$sale_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        $taxable_value = $item_amount + $adjusted_value;
       
        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2); // e.g. '07'
        $customer_state_code = State::where('name', $state)->value('state_code'); // assuming states table has GST code
        
        if ($merchant_state_code == $customer_state_code) {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }
        

         if ($item_type === 'taxable') {
        $sale =  $b2cSaleIds->where('id', $sale_id)->first();
        $key = $item->voucher_no_prefix . '|' . $rate;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'voucher_no_prefix' =>  $item->voucher_no_prefix,
                'rate' => $rate,
                'total' => $item->total,
                'sales_id' => $item->sale_id,
                'date'=> $item->date,
                'POS' => $item->POS,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }
}

    $data = array_values($grouped);

    return view('gstReturn.b2c_large_detailed', compact('data', 'merchant_gst', 'company_id', 'from_date', 'to_date'));


}

public function B2Cstatewise(Request $request)
{
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $user_company_id = Session::get('user_company_id');

    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');

    // -------- STEP 1: Get B2C Sale IDs --------
    $b2cSaleIds = DB::table('sales')
        ->where('merchant_gst', $merchant_gst)
        ->where('company_id', $company_id)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0')
        ->where('status', '1')
        ->where(function($query) {
            $query->whereNull('billing_gst')
                  ->orWhere('billing_gst', '');
        })
        ->where('total', '<=', 250000)
        ->pluck('id');

    if ($b2cSaleIds->isEmpty()) {
        return view('gstReturn.b2c_statewise', ['data' => []]);
    }

    // ----- SALES ITEMS -----
    $items_sale = DB::table('sale_descriptions')
        ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
        ->join('accounts', 'sales.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->where('manage_items.item_type', 'taxable')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select(
            'sale_descriptions.sale_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'manage_items.item_type',
            'sale_descriptions.amount'
        )
        ->get();

        

    // Sundrys and ledger adjustments for sales
    $sundryDetails_sales = DB::table('sale_sundries')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select('sale_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_id');

        
    $ledgerAdjustments_sales = [];

    foreach ($sundryDetails_sales as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;

            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_sales[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // --------- CREDIT NOTES (Sales returns) -----------
    $creditSales = DB::table('sales_returns')
        ->where('sales_returns.merchant_gst', $merchant_gst)
        ->where('sales_returns.company_id', $company_id)
        ->whereBetween('sales_returns.date', [$from_date, $to_date])
        ->where(function($query) {
            $query->whereNull('sales_returns.billing_gst')
                ->orWhere('sales_returns.billing_gst', '');
        })
        ->where('sales_returns.original_invoice_value','<=', 250000)
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where('sales_returns.delete', '0')
        ->where('sales_returns.status', '1')
        ->pluck('sales_returns.id');

    $creditItems = DB::table('sale_return_descriptions')
        ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
        ->join('states', 'sales_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_return_id', $creditSales)
        ->where('manage_items.item_type', 'taxable')
        ->select(
            'sale_return_descriptions.sale_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_return_descriptions.amount'
        )
        ->get();

    $creditSundryDetails = DB::table('sale_return_sundries')
        ->whereIn('sale_return_id', $creditSales)
        ->select('sale_return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_return_id');

    $ledgerAdjustments_credit = [];

    foreach ($creditSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;

            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_credit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // ---------- DEBIT NOTES (Purchase returns) -----------
    $debitSales = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->where('purchase_returns.company_id', $company_id)
        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
        ->where(function($query) {
            $query->whereNull('purchase_returns.billing_gst')
                  ->orWhere('purchase_returns.billing_gst', '');
        })
        ->where('purchase_returns.original_invoice_value','<=', 250000)
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->pluck('purchase_returns.id');

    $debitItems = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('purchase_return_id', $debitSales)
        ->where('manage_items.item_type', 'taxable')
        ->select(
            'purchase_return_descriptions.purchase_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'purchase_return_descriptions.amount'
        )
        ->get();

    $debitSundryDetails = DB::table('purchase_return_sundries')
        ->whereIn('purchase_return_id', $debitSales)
        ->select('purchase_return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('purchase_return_id');

    $ledgerAdjustments_debit = [];

    foreach ($debitSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;

            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_debit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // --------- Group and calculate for sales --------
    $grouped_sale = [];
    foreach ($items_sale as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $amount = $item->amount;
        $total_sale_amount = $items_sale->where('sale_id', $sale_id)->sum('amount');

        if ($total_sale_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust sundry
        if (isset($sundryDetails_sales[$sale_id])) {
            foreach ($sundryDetails_sales[$sale_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($amount / $total_sale_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        // Adjust ledger
        if (isset($ledgerAdjustments_sales[$sale_id])) {
            foreach ($ledgerAdjustments_sales[$sale_id] as $adj) {
                $share = ($amount / $total_sale_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $amount + $adjusted_value;

        // Tax calculation
        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $state . '|' . $rate;
        if (!isset($grouped_sale[$key])) {
            $grouped_sale[$key] = [
                'state' => $state,
                'rate' => $rate,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        $grouped_sale[$key]['taxable_value'] += $taxable_value;
        $grouped_sale[$key]['igst'] += $igst;
        $grouped_sale[$key]['cgst'] += $cgst;
        $grouped_sale[$key]['sgst'] += $sgst;
    }

    // ---------- Group and calculate for credit notes (subtract) ----------
    $grouped_credit = [];
    foreach ($creditItems as $item) {
        $return_id = $item->sale_return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $amount = $item->amount;

        $total_credit_amount = $creditItems->where('sale_return_id', $return_id)->sum('amount');
        if ($total_credit_amount == 0) continue;

        $adjusted_value = 0;

        if (isset($creditSundryDetails[$return_id])) {
            foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($amount / $total_credit_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        // Adjust ledger
        if (isset($ledgerAdjustments_credit[$return_id])) {
            foreach ($ledgerAdjustments_credit[$return_id] as $adj) {
                $share = ($amount / $total_credit_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $amount + $adjusted_value;

        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $state . '|' . $rate;
        if (!isset($grouped_credit[$key])) {
            $grouped_credit[$key] = [
                'state' => $state,
                'rate' => $rate,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        $grouped_credit[$key]['taxable_value'] += $taxable_value;
        $grouped_credit[$key]['igst'] += $igst;
        $grouped_credit[$key]['cgst'] += $cgst;
        $grouped_credit[$key]['sgst'] += $sgst;
    }

    // --------- Group and calculate for debit notes (add) -----------
    $grouped_debit = [];
    foreach ($debitItems as $item) {
        $return_id = $item->purchase_return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $amount = $item->amount;

        $total_debit_amount = $debitItems->where('purchase_return_id', $return_id)->sum('amount');
        if ($total_debit_amount == 0) continue;

        $adjusted_value = 0;

        if (isset($debitSundryDetails[$return_id])) {
            foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($amount / $total_debit_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        // Adjust ledger
        if (isset($ledgerAdjustments_debit[$return_id])) {
            foreach ($ledgerAdjustments_debit[$return_id] as $adj) {
                $share = ($amount / $total_debit_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $amount + $adjusted_value;

        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $state . '|' . $rate;
        if (!isset($grouped_debit[$key])) {
            $grouped_debit[$key] = [
                'state' => $state,
                'rate' => $rate,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        $grouped_debit[$key]['taxable_value'] += $taxable_value;
        $grouped_debit[$key]['igst'] += $igst;
        $grouped_debit[$key]['cgst'] += $cgst;
        $grouped_debit[$key]['sgst'] += $sgst;
    }

    // -------- Combine final adjusted data: sales + debit - credit -------
    $finalData = [];

    $allKeys = collect(array_unique(array_merge(array_keys($grouped_sale), array_keys($grouped_credit), array_keys($grouped_debit))));

    foreach ($allKeys as $key) {
        $state = $grouped_sale[$key]['state'] ?? $grouped_credit[$key]['state'] ?? $grouped_debit[$key]['state'] ?? null;
        $rate = $grouped_sale[$key]['rate'] ?? $grouped_credit[$key]['rate'] ?? $grouped_debit[$key]['rate'] ?? null;

        $salesTaxable = $grouped_sale[$key]['taxable_value'] ?? 0;
        $salesIGST = $grouped_sale[$key]['igst'] ?? 0;
        $salesCGST = $grouped_sale[$key]['cgst'] ?? 0;
        $salesSGST = $grouped_sale[$key]['sgst'] ?? 0;

        $creditTaxable = $grouped_credit[$key]['taxable_value'] ?? 0;
        $creditIGST = $grouped_credit[$key]['igst'] ?? 0;
        $creditCGST = $grouped_credit[$key]['cgst'] ?? 0;
        $creditSGST = $grouped_credit[$key]['sgst'] ?? 0;

        $debitTaxable = $grouped_debit[$key]['taxable_value'] ?? 0;
        $debitIGST = $grouped_debit[$key]['igst'] ?? 0;
        $debitCGST = $grouped_debit[$key]['cgst'] ?? 0;
        $debitSGST = $grouped_debit[$key]['sgst'] ?? 0;

        $finalData[$key] = [
            'state' => $state,
            'rate' => $rate,
            'taxable_value' => $salesTaxable + $debitTaxable - $creditTaxable,
            'igst' => $salesIGST + $debitIGST - $creditIGST,
            'cgst' => $salesCGST + $debitCGST - $creditCGST,
            'sgst' => $salesSGST + $debitSGST - $creditSGST,
        ];
    }

    // Pass final adjusted data to view
    return view('gstReturn.b2c_statewise', ['data' => array_values($finalData), 'merchant_gst' => $merchant_gst , 'from_date' => $from_date , 'to_date' => $to_date ,'$company_id' => $company_id]);
}

public function nilRatedAndExemptedCombined(Request $request)
{
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;
    $user_company_id = Session::get('user_company_id');

    $merchant_state_code = substr($merchant_gst, 0, 2);
    $merchant_state_id = State::where('state_code', $merchant_state_code)->value('id');

    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');

    $calculateCombined = function ($sales, $creditSales, $debitSales, $item_type_filter) use ($user_company_id, $sundries) {
        $getItemTaxable = function ($items, $sundryDetails, $ledgerAdjustments, $type_key, $type_value) use ($sundries) {
            $total = 0;
            foreach ($items as $item) {
                if ($item->item_type !== $type_value) continue;
                $amount = $item->amount;
                $sale_id = $item->{$type_key};
                $total_amount = $items->where($type_key, $sale_id)->sum('amount');
                if ($total_amount == 0) continue;

                $adjusted = 0;
                if (isset($sundryDetails[$sale_id])) {
                    foreach ($sundryDetails[$sale_id] as $sundry) {
                        $bs = $sundries[$sundry->bill_sundry] ?? null;
                        if (!$bs || $bs->adjust_sale_amt !== 'Yes' || $bs->nature_of_sundry !== 'OTHER') continue;
                        $share = ($amount / $total_amount) * $sundry->amount;
                        $adjusted += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                    }
                }
                if (isset($ledgerAdjustments[$sale_id])) {
                    foreach ($ledgerAdjustments[$sale_id] as $adj) {
                        $share = ($amount / $total_amount) * $adj['amount'];
                        $adjusted += ($adj['type'] == 'subtractive') ? -$share : $share;
                    }
                }
                $total += ($amount + $adjusted);
            }
            return $total;
        };

        $salesItems = DB::table('sale_descriptions')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->whereIn('sale_id', $sales->pluck('id'))
            ->select('sale_descriptions.amount', 'sale_descriptions.sale_id', 'manage_items.item_type')
            ->get();

        $salesSundryDetails = DB::table('sale_sundries')
            ->whereIn('sale_id', $sales->pluck('id'))
            ->select('sale_id', 'bill_sundry', 'amount')
            ->get()->groupBy('sale_id');

        $salesLedgerAdjustments = [];
        foreach ($salesSundryDetails as $saleId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt !== 'Yes' || $bs->nature_of_sundry !== 'OTHER') continue;
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');
                $salesLedgerAdjustments[$saleId][] = ['amount' => $ledger_amount, 'type' => $bs->bill_sundry_type];
            }
        }

        $creditItems = DB::table('sale_return_descriptions')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->whereIn('sale_return_id', $creditSales->pluck('id'))
            ->select('sale_return_descriptions.amount', 'sale_return_descriptions.sale_return_id', 'manage_items.item_type')
            ->get();

        $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
            ->where('percentage', '=', 0)
            ->whereIn('sale_return_id', $creditSales->pluck('id'))
            ->select('debit as amount', 'sale_return_id')
            ->get();

        $creditItems = $creditItems->merge($creditWithoutGstItems);

        $creditSundryDetails = DB::table('sale_return_sundries')
            ->whereIn('sale_return_id', $creditItems->pluck('sale_return_id'))
            ->select('sale_return_id', 'bill_sundry', 'amount')
            ->get()->groupBy('sale_return_id');

        $creditAdjustments = [];
        foreach ($creditSundryDetails as $returnId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt !== 'Yes' || $bs->nature_of_sundry !== 'OTHER') continue;
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $returnId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');
                $creditAdjustments[$returnId][] = ['amount' => $ledger_amount, 'type' => $bs->bill_sundry_type];
            }
        }

        $debitItems = DB::table('purchase_return_descriptions')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->whereIn('purchase_return_id', $debitSales->pluck('id'))
            ->select('purchase_return_descriptions.amount', 'purchase_return_descriptions.purchase_return_id', 'manage_items.item_type')
            ->get();

        $debitWithoutGstItems = DB::table('purchase_return_entries')
            ->where('percentage', '=', 0)
            ->whereIn('purchase_return_id', $debitSales->pluck('id'))
            ->select('debit as amount', 'purchase_return_id')
            ->get();

        $debitItems = $debitItems->merge($debitWithoutGstItems);

        $debitSundryDetails = DB::table('purchase_return_sundries')
            ->whereIn('purchase_return_id', $debitItems->pluck('purchase_return_id'))
            ->select('purchase_return_id', 'bill_sundry', 'amount')
            ->get()->groupBy('purchase_return_id');

        $debitAdjustments = [];
        foreach ($debitSundryDetails as $returnId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt !== 'Yes' || $bs->nature_of_sundry !== 'OTHER') continue;
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $returnId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');
                $debitAdjustments[$returnId][] = ['amount' => $ledger_amount, 'type' => $bs->bill_sundry_type];
            }
        }

        return $getItemTaxable($salesItems, $salesSundryDetails, $salesLedgerAdjustments, 'sale_id', $item_type_filter)
            + $getItemTaxable($debitItems, $debitSundryDetails, $debitAdjustments, 'purchase_return_id', $item_type_filter)
            - $getItemTaxable($creditItems, $creditSundryDetails, $creditAdjustments, 'sale_return_id', $item_type_filter);
    };

    $buildSalesQuery = function ($billing_gst_null, $is_intra) use ($merchant_gst, $company_id, $from_date, $to_date, $merchant_state_id) {
        $query = DB::table('sales')
            ->join('accounts', 'accounts.id', '=', 'sales.party')
            ->join('states', 'states.id', '=', 'sales.billing_state')
            ->where('sales.merchant_gst', $merchant_gst)
            ->where('sales.company_id', $company_id)
            ->whereBetween('sales.date', [$from_date, $to_date])
            ->where('sales.delete', '0')
            ->where('sales.status', '1');

        $query->where(function ($q) use ($billing_gst_null) {
            if ($billing_gst_null) {
                $q->whereNull('sales.billing_gst')->orWhere('sales.billing_gst', '');
            } else {
                $q->whereNotNull('sales.billing_gst')->where('sales.billing_gst', '!=', '');
            }
        });

        $query->where('sales.billing_state', $is_intra ? '=' : '!=', $merchant_state_id);

        return $query->select('sales.id')->get();
    };

    $reg_intra_sales = $buildSalesQuery(false, true);
    $reg_inter_sales = $buildSalesQuery(false, false);
    $unreg_intra_sales = $buildSalesQuery(true, true);
    $unreg_inter_sales = $buildSalesQuery(true, false);

    // Fetch credit and debit notes once
   
    $nil_rated_reg_intra = $calculateCombined($reg_intra_sales, $creditSales, $debitSales, 'nil_rated');
    $nil_rated_reg_inter = $calculateCombined($reg_inter_sales, $creditSales, $debitSales, 'nil_rated');
    $nil_rated_unreg_intra = $calculateCombined($unreg_intra_sales, $creditSales, $debitSales, 'nil_rated');
    $nil_rated_unreg_inter = $calculateCombined($unreg_inter_sales, $creditSales, $debitSales, 'nil_rated');

    $exempted_reg_intra = $calculateCombined($reg_intra_sales, $creditSales, $debitSales, 'exempted');
    $exempted_reg_inter = $calculateCombined($reg_inter_sales, $creditSales, $debitSales, 'exempted');
    $exempted_unreg_intra = $calculateCombined($unreg_intra_sales, $creditSales, $debitSales, 'exempted');
    $exempted_unreg_inter = $calculateCombined($unreg_inter_sales, $creditSales, $debitSales, 'exempted');

    return view('gstReturn.nilRatedExempted', compact(
        'nil_rated_reg_intra', 'nil_rated_reg_inter', 'nil_rated_unreg_intra', 'nil_rated_unreg_inter',
        'exempted_reg_intra', 'exempted_reg_inter', 'exempted_unreg_intra', 'exempted_unreg_inter',
        'merchant_gst', 'company_id', 'from_date', 'to_date'
    ));
}






 public function combinedNoteRegister(Request $request)
{
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $user_company_id = Session::get('user_company_id');

    // Step 1: Fetch all Bill Sundries for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');

    // -------------------- CREDIT NOTES FETCH --------------------
    $creditSales = DB::table('sales_returns')
        ->where('sales_returns.merchant_gst', $merchant_gst)
        ->join('accounts', 'accounts.id', '=', 'sales_returns.party')
        ->join('states', 'states.id', '=', 'sales_returns.billing_state')
        ->where('sales_returns.company_id', $company_id)
        ->whereDate('sales_returns.date', '>=', $from_date)
        ->whereDate('sales_returns.date', '<=', $to_date)
        ->whereNotNull('sales_returns.billing_gst')
        ->where('billing_gst', '!=', '')
        ->where('voucher_type', 'SALE')
         ->where('sr_nature', 'WITH GST')
         ->where('sales_returns.delete', '0')
        ->where('sales_returns.status', '1')
        ->select(
            'sales_returns.id',
            'sales_returns.date',
            'sales_returns.sr_prefix',
            'sales_returns.total',
            'sales_returns.billing_gst',
            'accounts.account_name as name',
            'states.name as POS'
        )
        ->get();

    // Fetch credit note items (with GST)
    $creditItems = DB::table('sale_return_descriptions')
        ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
        ->join('accounts', 'sales_returns.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_return_id', $creditSales->pluck('id'))
        ->select(
            'sale_return_descriptions.id as sale_desc_id',
            'sale_return_descriptions.sale_return_id as return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_return_descriptions.amount',
            'manage_items.item_type',
            'sales_returns.sr_prefix',
            'sale_return_descriptions.created_at'
        )
        ->get();

    // Fetch credit note items without GST
    $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
        ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
        ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
        ->where('sale_return_without_gst_entry.percentage', '>', 0)
        ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales->pluck('id'))
        ->select(
            'sale_return_without_gst_entry.id as sale_desc_id',
            'sale_return_without_gst_entry.sale_return_id as return_id',
            'states.name as state_name',
            'sale_return_without_gst_entry.percentage as gst_rate',
            'sale_return_without_gst_entry.debit as amount',
            DB::raw("'taxable' as item_type"),
            'sales_returns.sr_prefix'
        )
        ->get();

    $creditItems = $creditItems->merge($creditWithoutGstItems);

    // Fetch sundry details for credit notes
    $creditSundryDetails = DB::table('sale_return_sundries')
        ->whereIn('sale_return_id', $creditSales->pluck('id'))
        ->select('sale_return_id as return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('return_id');

    // Calculate ledger adjustments for credit notes
    $creditLedgerAdjustments = [];
    foreach ($creditSundryDetails as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $creditLedgerAdjustments[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // -------------------- DEBIT NOTES FETCH --------------------
    $debitSales = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->join('accounts', 'accounts.id', '=', 'purchase_returns.party')
        ->join('states', 'states.id', '=', 'purchase_returns.billing_state')
        ->where('purchase_returns.company_id', $company_id)
        ->whereDate('purchase_returns.date', '>=', $from_date)
        ->whereDate('purchase_returns.date', '<=', $to_date)
        ->whereNotNull('purchase_returns.billing_gst')
        ->where('billing_gst', '!=', '')
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->select(
            'purchase_returns.id',
            'purchase_returns.date',
            'purchase_returns.sr_prefix',
            'purchase_returns.total',
            'purchase_returns.billing_gst',
            'accounts.account_name as name',
            'states.name as POS'
        )
        ->get();

    // Fetch debit note items (with GST)
    $debitItems = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->where('purchase_return_descriptions.delete', '0')
        ->where('purchase_return_descriptions.status', '1')
        ->whereIn('purchase_return_id', $debitSales->pluck('id'))
        ->select(
            'purchase_return_descriptions.id as sale_desc_id',
            'purchase_return_descriptions.purchase_return_id as return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'purchase_return_descriptions.amount',
            'manage_items.item_type',
            'purchase_returns.sr_prefix',
            'purchase_return_descriptions.created_at'
        )
        ->get();

    // Fetch debit note without items with GST
    $debitWithoutGstItems = DB::table('purchase_return_entries')
        ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
        ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->where('purchase_return_entries.percentage', '>', 0)
        ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->pluck('id'))
        ->select(
            'purchase_return_entries.id as sale_desc_id',
            'purchase_return_entries.purchase_return_id as return_id',
            'states.name as state_name',
            'purchase_return_entries.percentage as gst_rate',
            'purchase_return_entries.debit as amount',
            DB::raw("'taxable' as item_type"),
            'purchase_returns.sr_prefix'
        )
        ->get();

    $debitItems = $debitItems->merge($debitWithoutGstItems);

    // Fetch sundry details for debit notes
    $debitSundryDetails = DB::table('purchase_return_sundries')
        ->whereIn('purchase_return_id', $debitSales->pluck('id'))
        ->select('purchase_return_id as return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('return_id');

    // Calculate ledger adjustments for debit notes
    $debitLedgerAdjustments = [];
    foreach ($debitSundryDetails as $purchaseId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $purchaseId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $debitLedgerAdjustments[$purchaseId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // Prepare to group the results
    $grouped = [];

    // Process credit items
    foreach ($creditItems as $item) {
        $return_id = $item->return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->sr_prefix;
        $isCredit = true;

        // Calculate total item amount for the corresponding note type
        $total_item_amount = collect($creditItems)->where('return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust from sundries
        if (isset($creditSundryDetails[$return_id])) {
            foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledger adjustments
        if (isset($creditLedgerAdjustments[$return_id])) {
            foreach ($creditLedgerAdjustments[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;

        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        // Use sr_prefix|rate|note_type as key to separate groups clearly
        $note_type = 'C'; // Credit note
        $key = $voucher_no_prefix . '|' . $rate . '|' . $note_type;

        if (!isset($grouped[$key])) {
            $noteData = $creditSales->where('id', $return_id)->first();
            $grouped[$key] = [
                'sr_prefix' => $voucher_no_prefix,
                'rate' => $rate,
                'note_date' => $noteData->date,
                'billing_gst' => $noteData->billing_gst,
                'name' => $noteData->name,
                'total' => $noteData->total,
                'POS' => $noteData->POS,
                'note_type' => $note_type,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        // Accumulate values
        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }

    // Process debit items
    foreach ($debitItems as $item) {
        $return_id = $item->return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->sr_prefix;
        $isCredit = false;

        // Calculate total item amount for the corresponding note type
        $total_item_amount = collect($debitItems)->where('return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust from sundries
        if (isset($debitSundryDetails[$return_id])) {
            foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledger adjustments
        if (isset($debitLedgerAdjustments[$return_id])) {
            foreach ($debitLedgerAdjustments[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;

        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        // Use sr_prefix|rate|note_type as key to separate groups clearly
        $note_type = 'D'; // Debit note
        $key = $voucher_no_prefix . '|' . $rate . '|' . $note_type;

        if (!isset($grouped[$key])) {
            $noteData = $debitSales->where('id', $return_id)->first();
            $grouped[$key] = [
                'sr_prefix' => $voucher_no_prefix,
                'rate' => $rate,
                'note_date' => $noteData->date,
                'billing_gst' => $noteData->billing_gst,
                'name' => $noteData->name,
                'total' => $noteData->total,
                'POS' => $noteData->POS,
                'note_type' => $note_type,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        // Accumulate values
        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }

    // Convert grouped data to a collection for sorting by note_type (credit first), then sr_prefix, then note_date
$sortedGrouped = collect($grouped)->sortBy(function ($item) {
    // For note_type: 'D' is credit (should come first), 'C' is debit
    // So we assign numeric rank: 'D' = 0, 'C' = 1 for sorting
    $noteTypeRank = $item['note_type'] === 'C' ? 0 : 1;
    
    return [$noteTypeRank, $item['sr_prefix'], $item['note_date']];
})->values()->all();


    return view('gstReturn.debitcreditnote', ['grouped' => $sortedGrouped , 'merchant_gst' => $merchant_gst,'company_id' => $company_id,'from_date' => $from_date,'to_date' => $to_date]);
}







public function combinedNoteUnreegister(Request $request)
{
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $user_company_id = Session::get('user_company_id');

    // Step 1: Fetch all Bill Sundries for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');

    // -------------------- CREDIT NOTES FETCH --------------------
    $creditSales = DB::table('sales_returns')
        ->where('sales_returns.merchant_gst', $merchant_gst)
        ->join('sales','sales.id','=','sales_returns.sale_bill_id')
        ->where('sales.total','>', 250000)
        ->join('accounts', 'accounts.id', '=', 'sales_returns.party')
        ->join('states', 'states.id', '=', 'sales_returns.billing_state')
        ->where('sales_returns.company_id', $company_id)
        ->whereDate('sales_returns.date', '>=', $from_date)
        ->whereDate('sales_returns.date', '<=', $to_date)
       ->where(function($q) {
        $q->whereNull('sales_returns.billing_gst')
          ->orWhere('sales_returns.billing_gst', '');
                })
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where('sales_returns.delete', '0')
        ->where('sales_returns.status', '1')
        ->select(
            'sales_returns.id',
            'sales_returns.date',
            'sales_returns.sr_prefix',
            'sales_returns.total',
            'accounts.account_name as name',
            'states.name as POS'
        )
        ->get();

    // Fetch credit note items (with GST)
    $creditItems = DB::table('sale_return_descriptions')
        ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
        ->join('accounts', 'sales_returns.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_return_id', $creditSales->pluck('id'))
        ->select(
            'sale_return_descriptions.id as sale_desc_id',
            'sale_return_descriptions.sale_return_id as return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_return_descriptions.amount',
            'manage_items.item_type',
            'sales_returns.sr_prefix',
            'sale_return_descriptions.created_at'
        )
        ->get();

    // Fetch credit note items without item with GST
    $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
        ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
        ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
        ->where('sale_return_without_gst_entry.percentage', '>', 0)
        ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales->pluck('id'))
        ->select(
            'sale_return_without_gst_entry.id as sale_desc_id',
            'sale_return_without_gst_entry.sale_return_id as return_id',
            'states.name as state_name',
            'sale_return_without_gst_entry.percentage as gst_rate',
            'sale_return_without_gst_entry.debit as amount',
            DB::raw("'taxable' as item_type"),
            'sales_returns.sr_prefix'
        )
        ->get();

    $creditItems = $creditItems->merge($creditWithoutGstItems);

    // Fetch sundry details for credit notes
    $creditSundryDetails = DB::table('sale_return_sundries')
        ->whereIn('sale_return_id', $creditSales->pluck('id'))
        ->select('sale_return_id as return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('return_id');

    // Calculate ledger adjustments for credit notes
    $creditLedgerAdjustments = [];
    foreach ($creditSundryDetails as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $creditLedgerAdjustments[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // -------------------- DEBIT NOTES FETCH --------------------
    $debitSales = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->join('sales','sales.id','=','purchase_returns.purchase_bill_id')
        ->where('sales.total','>', 250000)
        ->join('accounts', 'accounts.id', '=', 'purchase_returns.party')
        ->join('states', 'states.id', '=', 'purchase_returns.billing_state')
        ->where('purchase_returns.company_id', $company_id)
        ->whereDate('purchase_returns.date', '>=', $from_date)
        ->whereDate('purchase_returns.date', '<=', $to_date)
       ->where(function($q) {
                            $q->whereNull('purchase_returns.billing_gst')
                            ->orWhere('purchase_returns.billing_gst', '');
                        })
       ->where('voucher_type', 'SALE')
                        ->where('sr_nature', 'WITH GST')
                
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->select(
            'purchase_returns.id',
            'purchase_returns.date',
            'purchase_returns.sr_prefix',
            'purchase_returns.total',
            //'purchase_returns.billing_gst',
            'accounts.account_name as name',
            'states.name as POS'
        )
        ->get();

    // Fetch debit note items (with GST)
    $debitItems = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->where('purchase_return_descriptions.delete', '0')
        ->where('purchase_return_descriptions.status', '1')
        ->whereIn('purchase_return_id', $debitSales->pluck('id'))
        ->select(
            'purchase_return_descriptions.id as sale_desc_id',
            'purchase_return_descriptions.purchase_return_id as return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'purchase_return_descriptions.amount',
            'manage_items.item_type',
            'purchase_returns.sr_prefix',
            'purchase_return_descriptions.created_at'
        )
        ->get();

    // Fetch debit note items without GST
    $debitWithoutGstItems = DB::table('purchase_return_entries')
        ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
        ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->where('purchase_return_entries.percentage', '>', 0)
        ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->pluck('id'))
        ->select(
            'purchase_return_entries.id as sale_desc_id',
            'purchase_return_entries.purchase_return_id as return_id',
            'states.name as state_name',
            'purchase_return_entries.percentage as gst_rate',
            'purchase_return_entries.debit as amount',
            DB::raw("'taxable' as item_type"),
            'purchase_returns.sr_prefix'
        )
        ->get();

    $debitItems = $debitItems->merge($debitWithoutGstItems);

    // Fetch sundry details for debit notes
    $debitSundryDetails = DB::table('purchase_return_sundries')
        ->whereIn('purchase_return_id', $debitSales->pluck('id'))
        ->select('purchase_return_id as return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('return_id');

    // Calculate ledger adjustments for debit notes
    $debitLedgerAdjustments = [];
    foreach ($debitSundryDetails as $purchaseId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $purchaseId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $debitLedgerAdjustments[$purchaseId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }

    // Prepare to group the results
    $grouped = [];

    // Process credit items
    foreach ($creditItems as $item) {
        $return_id = $item->return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->sr_prefix;
        $isCredit = true;

        // Calculate total item amount for the corresponding note type
        $total_item_amount = collect($creditItems)->where('return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust from sundries
        if (isset($creditSundryDetails[$return_id])) {
            foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledger adjustments
        if (isset($creditLedgerAdjustments[$return_id])) {
            foreach ($creditLedgerAdjustments[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;

        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        // Use sr_prefix|rate|note_type as key to separate groups clearly
        $note_type = 'C'; // Credit note
        $key = $voucher_no_prefix . '|' . $rate . '|' . $note_type;

        if (!isset($grouped[$key])) {
            $noteData = $creditSales->where('id', $return_id)->first();
            $grouped[$key] = [
                'sr_prefix' => $voucher_no_prefix,
                'rate' => $rate,
                'note_date' => $noteData->date,
              //  'billing_gst' => $noteData->billing_gst,
                'name' => $noteData->name,
                'total' => $noteData->total,
                'POS' => $noteData->POS,
                'note_type' => $note_type,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        // Accumulate values
        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }

    // Process debit items
    foreach ($debitItems as $item) {
        $return_id = $item->return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;
        $voucher_no_prefix = $item->sr_prefix;
        $isCredit = false;

        // Calculate total item amount for the corresponding note type
        $total_item_amount = collect($debitItems)->where('return_id', $return_id)->sum('amount');
        if ($total_item_amount == 0) continue;

        $adjusted_value = 0;

        // Adjust from sundries
        if (isset($debitSundryDetails[$return_id])) {
            foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                if ($bs->bill_sundry_type == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Adjust from ledger adjustments
        if (isset($debitLedgerAdjustments[$return_id])) {
            foreach ($debitLedgerAdjustments[$return_id] as $adj) {
                $share = ($item_amount / $total_item_amount) * $adj['amount'];
                if ($adj['type'] == 'subtractive') {
                    $adjusted_value -= $share;
                } else {
                    $adjusted_value += $share;
                }
            }
        }

        // Calculate taxable value
        $taxable_value = $item_amount + $adjusted_value;

        // Compute taxes
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        // Use sr_prefix|rate|note_type as key to separate groups clearly
        $note_type = 'D'; // Debit note
        $key = $voucher_no_prefix . '|' . $rate . '|' . $note_type;

        if (!isset($grouped[$key])) {
            $noteData = $debitSales->where('id', $return_id)->first();
            $grouped[$key] = [
                'sr_prefix' => $voucher_no_prefix,
                'rate' => $rate,
                'note_date' => $noteData->date,
                //'billing_gst' => $noteData->billing_gst,
                'name' => $noteData->name,
                'total' => $noteData->total,
                'POS' => $noteData->POS,
                'note_type' => $note_type,
                'taxable_value' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        // Accumulate values
        $grouped[$key]['taxable_value'] += $taxable_value;
        $grouped[$key]['igst'] += $igst;
        $grouped[$key]['cgst'] += $cgst;
        $grouped[$key]['sgst'] += $sgst;
    }

    // Convert grouped data to a collection for sorting by note_type (credit first), then sr_prefix, then note_date
$sortedGrouped = collect($grouped)->sortBy(function ($item) {
    // For note_type: 'D' is credit (should come first), 'C' is debit
    // So we assign numeric rank: 'D' = 0, 'C' = 1 for sorting
    $noteTypeRank = $item['note_type'] === 'C' ? 0 : 1;
    
    return [$noteTypeRank, $item['sr_prefix'], $item['note_date']];
})->values()->all();


    return view('gstReturn.debitcreditnoteunreg', ['grouped' => $sortedGrouped ,'merchant_gst' => $merchant_gst,'company_id' => $company_id,'from_date' => $from_date,'to_date' => $to_date,]);
}




public function hsnSummary(Request $request){
    $type = $request->input('type');
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;
    $user_company_id = Session::get('user_company_id');
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
    // -------- STEP 1: Get B2C Sale IDs --------
    $b2cSaleIds = DB::table('sales')
                        ->where('merchant_gst', $merchant_gst)
                        ->where('company_id', $company_id)
                        ->whereBetween('date', [$from_date, $to_date])
                        ->where('delete', '0')
                        ->where('status', '1')
                        ->where(function($q) use ($type) {
                            if ($type === 'B2C') {
                                $q->whereNull('billing_gst')->orWhere('billing_gst', '');
                            } else {
                                $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                            }
                        })
                        ->pluck('id');
    if ($b2cSaleIds->isEmpty()) {
        return view('gstReturn.hsnSummary', ['data' => []]);
    }
    // ----- SALES ITEMS -----
    $items_sale = DB::table('sale_descriptions')
        ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
        ->join('accounts', 'sales.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('units','manage_items.u_name','=','units.id')
        ->where('sale_descriptions.status','1')
        ->where('sale_descriptions.delete','0')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select(
            'sale_descriptions.sale_id',
            'states.name as state_name',
            'sale_descriptions.qty',
            'sale_descriptions.amount',
            'manage_items.gst_rate',
            'units.unit_code',
            'manage_items.hsn_code',
             'manage_items.item_type'
        )
        ->get();
    // Sundrys and ledger adjustments for sales
    $sundryDetails_sales = DB::table('sale_sundries')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select('sale_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_id');
    $ledgerAdjustments_sales = [];
    foreach ($sundryDetails_sales as $saleId => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $saleId)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_sales[$saleId][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    // --------- CREDIT NOTES (Sales returns) -----------
    $creditSales = DB::table('sales_returns')
        ->where('sales_returns.merchant_gst', $merchant_gst)
        ->where('sales_returns.company_id', $company_id)
        ->whereBetween('sales_returns.date', [$from_date, $to_date])
         ->where('voucher_type', 'SALE')
         ->where('sr_nature', 'WITH GST')
          ->where('sr_type','WITH ITEM')
          ->where(function($q) use ($type) {
                if ($type === 'B2B') {
                    $q->whereNotNull('billing_gst')
                        ->where('billing_gst', '!=', '');
                } else {
                    $q->whereNull('billing_gst')->orWhere('billing_gst', '');
                }
            })
        ->where('sales_returns.delete', '0')
        ->where('sales_returns.status', '1')
        ->pluck('sales_returns.id');
    $creditSales1 = DB::table('sales_returns')
        ->where('sales_returns.merchant_gst', $merchant_gst)
        ->where('sales_returns.company_id', $company_id)
        ->whereBetween('sales_returns.date', [$from_date, $to_date])
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
        ->where(function($q) use ($type) {
            if ($type === 'B2C') {
                $q->whereNull('billing_gst')->orWhere('billing_gst', '');
            } else {
                $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
            }
        })
        ->where(function($q){
            $q->where('sr_type','WITHOUT ITEM')
              ->orWhere('sr_type','RATE DIFFERENCE');
        })
        ->where('sales_returns.delete', '0')
        ->where('sales_returns.status', '1')
        ->pluck('sales_returns.id');       
    $creditItems = DB::table('sale_return_descriptions')
        ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
        ->join('states', 'sales_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
         ->join('units','manage_items.u_name','=','units.id')
        ->whereIn('sale_return_id', $creditSales)
        ->where('sale_return_descriptions.status','1')
        ->where('sale_return_descriptions.delete','0')
        ->select(
            'sale_return_descriptions.sale_return_id',
            'states.name as state_name',
            'sale_return_descriptions.qty',
            'manage_items.gst_rate',
            'sale_return_descriptions.amount',
            'units.unit_code',
            'manage_items.hsn_code',
             
        )
        ->get();
    $creditItems1 = DB::table('sale_return_descriptions')
                    ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
                    ->join('states', 'sales_returns.billing_state', '=', 'states.id')
                    ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
                        ->join('units','manage_items.u_name','=','units.id')
                        ->where(function($q) use ($type) {
                            if ($type === 'B2C') {
                                $q->whereNull('billing_gst')->orWhere('billing_gst', '');
                            } else {
                                $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                            }
                        })
                    ->whereIn('sale_return_id', $creditSales1)
                    ->where('sale_return_descriptions.status','1')
                    ->where('sale_return_descriptions.delete','0')
                    ->select(
                        'sale_return_descriptions.sale_return_id',
                        'states.name as state_name',
                        DB::raw('0 as qty'), // Override qty with 0
                        'manage_items.gst_rate',
                        'sale_return_descriptions.amount',
                        'units.unit_code',
                        'manage_items.hsn_code',
                    )
                    ->get();
 

    $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
                    ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
                    ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
                    ->where('sale_return_without_gst_entry.percentage', '>', 0)
                    ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales1)
                    ->where('sale_return_without_gst_entry.status','1')
                    ->where('sale_return_without_gst_entry.delete','0')
                    ->select(
                        'sale_return_without_gst_entry.sale_return_id as sale_return_id',
                        'states.name as state_name',
                            DB::raw('0 as qty'),
                        'sale_return_without_gst_entry.percentage as gst_rate',
                        'sale_return_without_gst_entry.debit as amount',
                        'sale_return_without_gst_entry.hsn_code',
                        'sale_return_without_gst_entry.unit_code'
                    )
                    ->get();
    $creditItems =  $creditItems->merge($creditItems1)->merge($creditWithoutGstItems);
    $creditSundryDetails = DB::table('sale_return_sundries')
        ->whereIn('sale_return_id', $creditSales)
        ->select('sale_return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('sale_return_id');
    $ledgerAdjustments_credit = [];
    foreach ($creditSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;
            if (!$bs) continue;
            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_credit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    // ---------- DEBIT NOTES (Purchase returns) -----------
    $debitSales = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->where('purchase_returns.company_id', $company_id)
        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
         ->where('voucher_type', 'SALE')
         ->where('sr_nature', 'WITH GST')
         ->where('sr_type','WITH ITEM')
          ->where(function($q) use ($type) {
                if ($type === 'B2C') {
                    $q->whereNull('billing_gst')->orWhere('billing_gst', '');
                } else {
                    $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                }
            })
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->pluck('purchase_returns.id');

          $debitSales1 = DB::table('purchase_returns')
        ->where('purchase_returns.merchant_gst', $merchant_gst)
        ->where('purchase_returns.company_id', $company_id)
        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
        ->where('voucher_type', 'SALE')
        ->where('sr_nature', 'WITH GST')
           ->where(function($q){
            $q->where('sr_type','WITHOUT ITEM')
              ->orWhere('sr_type','RATE DIFFERENCE');
        })
        ->where(function($q) use ($type) {
            if ($type === 'B2C') {
                $q->whereNull('billing_gst')->orWhere('billing_gst', '');
            } else {
                $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
            }
        })
        ->where('purchase_returns.delete', '0')
        ->where('purchase_returns.status', '1')
        ->pluck('purchase_returns.id');

    $debitItems = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('units','manage_items.u_name','=','units.id')
        ->whereIn('purchase_return_id', $debitSales)
        ->where('purchase_return_descriptions.status','1')
        ->where('purchase_return_descriptions.delete','0')
        ->select(
            'purchase_return_descriptions.purchase_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'purchase_return_descriptions.qty',
            'purchase_return_descriptions.amount',
            'units.unit_code',
            'manage_items.hsn_code'
        )
        ->get();

    $debitItems1 = DB::table('purchase_return_descriptions')
        ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
        ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
        ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
         ->join('units','manage_items.u_name','=','units.id')
          ->where('purchase_return_descriptions.status','1')
          ->where('purchase_return_descriptions.delete','0')
        ->whereIn('purchase_return_id', $debitSales1)
        ->select(
            'purchase_return_descriptions.purchase_return_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            DB::raw('0 as qty'),
            'purchase_return_descriptions.amount',
            'units.unit_code',
            'manage_items.hsn_code'
        )
        ->get();

    $debitWithoutGstItems = DB::table('purchase_return_entries')
                ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
                ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
                ->where('purchase_return_entries.percentage', '>', 0)
                ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->pluck('id'))
                ->where('purchase_return_entries.status','1')
                ->where('purchase_return_entries.delete','0')
                ->select(
                    'purchase_return_entries.purchase_return_id as purchase_return_id',
                    'states.name as state_name',
                    'purchase_return_entries.percentage as gst_rate',
                    DB::raw('0 as qty'),
                    'purchase_return_entries.debit as amount',
                    'purchase_return_entries.hsn_code',
                    'purchase_return_entries.unit_code'
                    
                )
                ->get();
    $debitItems = $debitItems->merge($debitItems1)->merge($debitWithoutGstItems);
    $debitSundryDetails = DB::table('purchase_return_sundries')
        ->whereIn('purchase_return_id', $debitSales)
        ->select('purchase_return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('purchase_return_id');

    $debitSundryDetails1 = DB::table('purchase_return_sundries')
        ->whereIn('purchase_return_id', $debitSales1)
        ->select('purchase_return_id', 'bill_sundry', 'amount')
        ->get()
        ->groupBy('purchase_return_id');

    $debitSundryDetails = $debitSundryDetails->merge($debitSundryDetails1);
    $ledgerAdjustments_debit = [];
    foreach ($debitSundryDetails as $return_id => $entries) {
        foreach ($entries as $entry) {
            $bs = $sundries[$entry->bill_sundry] ?? null;

            if (!$bs) continue;

            if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                $ledger_amount = DB::table('account_ledger')
                    ->where('company_id', $user_company_id)
                    ->where('entry_type', 1)
                    ->where('entry_type_id', $return_id)
                    ->where('account_id', $bs->sale_amt_account)
                    ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                $ledgerAdjustments_debit[$return_id][] = [
                    'amount' => $ledger_amount,
                    'type' => $bs->bill_sundry_type
                ];
            }
        }
    }
    // --------- Group and calculate for sales --------
    $grouped_sale = [];
    foreach ($items_sale as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $amount = $item->amount;
        $qty = $item->qty;
        $hsn = $item->hsn_code;
        $unit_code = $item->unit_code;
        $total_sale_amount = $items_sale->where('sale_id', $sale_id)->sum('amount');
        if ($total_sale_amount == 0) continue;
        $adjusted_value = 0;
        // Adjust sundry
        if (isset($sundryDetails_sales[$sale_id])) {
            foreach ($sundryDetails_sales[$sale_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                $share = ($amount / $total_sale_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }
        // Adjust ledger
        if (isset($ledgerAdjustments_sales[$sale_id])) {
            foreach ($ledgerAdjustments_sales[$sale_id] as $adj) {
                $share = ($amount / $total_sale_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }
        $taxable_value = $amount + $adjusted_value;

        // Tax calculation
        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $hsn . '|' . $unit_code . '|' . $rate;
        if (!isset($grouped_sale[$key])) {
            $grouped_sale[$key] = [
                'hsn' => $hsn,
                'unit_code' => $unit_code,
                'rate' => $rate,
                'taxable_value' => 0,
                'qty' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }        
        $grouped_sale[$key]['qty'] += $qty;
        $grouped_sale[$key]['taxable_value'] += $taxable_value;
        $grouped_sale[$key]['igst'] += $igst;
        $grouped_sale[$key]['cgst'] += $cgst;
        $grouped_sale[$key]['sgst'] += $sgst;
    }
    // ---------- Group and calculate for credit notes (subtract) ----------
    $grouped_credit = [];
    foreach ($creditItems as $item) {
        $return_id = $item->sale_return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $qty = $item->qty;
        $hsn = $item->hsn_code;
        $unit_code = $item->unit_code;
        $amount = $item->amount;
        $total_credit_amount = $creditItems->where('sale_return_id', $return_id)->sum('amount');
        if ($total_credit_amount == 0) continue;
        $adjusted_value = 0;
        if (isset($creditSundryDetails[$return_id])) {
            foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($amount / $total_credit_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }
        // Adjust ledger
        if (isset($ledgerAdjustments_credit[$return_id])) {
            foreach ($ledgerAdjustments_credit[$return_id] as $adj) {
                $share = ($amount / $total_credit_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }
        $taxable_value = $amount + $adjusted_value;
        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');
        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }  
        $key = $hsn . '|' . $unit_code . '|' . $rate;
        if (!isset($grouped_credit[$key])) {
            $grouped_credit[$key] = [
                'hsn' => $hsn,
                'unit_code' => $unit_code,
                'rate' => $rate,
                'taxable_value' => 0,
                'qty' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }
        $grouped_credit[$key]['qty'] += $qty;
        $grouped_credit[$key]['taxable_value'] += $taxable_value;
        $grouped_credit[$key]['igst'] += $igst;
        $grouped_credit[$key]['cgst'] += $cgst;
        $grouped_credit[$key]['sgst'] += $sgst;        
    }
    // --------- Group and calculate for debit notes (add) -----------
    $grouped_debit = [];
    foreach ($debitItems as $item) {
        $return_id = $item->purchase_return_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $qty = $item->qty;
        $hsn = $item->hsn_code;
        $unit_code = $item->unit_code;
        $amount = $item->amount;
        $total_debit_amount = $debitItems->where('purchase_return_id', $return_id)->sum('amount');
        if ($total_debit_amount == 0) continue;
        $adjusted_value = 0;
        if (isset($debitSundryDetails[$return_id])) {
            foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                $share = ($amount / $total_debit_amount) * $sundryEntry->amount;
                $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
            }
        }

        // Adjust ledger
        if (isset($ledgerAdjustments_debit[$return_id])) {
            foreach ($ledgerAdjustments_debit[$return_id] as $adj) {
                $share = ($amount / $total_debit_amount) * $adj['amount'];
                $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
            }
        }

        $taxable_value = $amount + $adjusted_value;

        $igst = 0; $cgst = 0; $sgst = 0;
        $merchant_state_code = substr($merchant_gst, 0, 2);
        $customer_state_code = State::where('name', $state)->value('state_code');

        if ($merchant_state_code == $customer_state_code) {
            $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $hsn . '|' . $unit_code . '|' . $rate;
        if (!isset($grouped_debit[$key])) {
            $grouped_debit[$key] = [
                'hsn' => $hsn,
                'unit_code' => $unit_code,
                'rate' => $rate,
                'taxable_value' => 0,
                'qty' => 0,
                'igst' => 0,
                'cgst' => 0,
                'sgst' => 0,
            ];
        }

        $grouped_debit[$key]['qty'] += $qty;
        $grouped_debit[$key]['taxable_value'] += $taxable_value;
        $grouped_debit[$key]['igst'] += $igst;
        $grouped_debit[$key]['cgst'] += $cgst;
        $grouped_debit[$key]['sgst'] += $sgst;
    }
    // -------- Combine final adjusted data: sales + debit - credit -------
    $finalData = [];
    $allKeys = collect(array_unique(array_merge(array_keys($grouped_sale), array_keys($grouped_credit), array_keys($grouped_debit))));
    foreach ($allKeys as $key) {
        $hsn = $grouped_sale[$key]['hsn'] ?? $grouped_credit[$key]['hsn'] ?? $grouped_debit[$key]['hsn'] ?? null;
        $rate = $grouped_sale[$key]['rate'] ?? $grouped_credit[$key]['rate'] ?? $grouped_debit[$key]['rate'] ?? null;
        $unit_code = $grouped_sale[$key]['unit_code'] ?? $grouped_credit[$key]['unit_code'] ?? $grouped_debit[$key]['unit_code'] ?? null;

        $salesqty = $grouped_sale[$key]['qty'] ?? 0;
        $salesTaxable = $grouped_sale[$key]['taxable_value'] ?? 0;
        $salesIGST = $grouped_sale[$key]['igst'] ?? 0;
        $salesCGST = $grouped_sale[$key]['cgst'] ?? 0;
        $salesSGST = $grouped_sale[$key]['sgst'] ?? 0;

        $creditqty = $grouped_credit[$key]['qty'] ?? 0;
        $creditTaxable = $grouped_credit[$key]['taxable_value'] ?? 0;
        $creditIGST = $grouped_credit[$key]['igst'] ?? 0;
        $creditCGST = $grouped_credit[$key]['cgst'] ?? 0;
        $creditSGST = $grouped_credit[$key]['sgst'] ?? 0;

        $debitqty = $grouped_debit[$key]['qty'] ?? 0;
        $debitTaxable = $grouped_debit[$key]['taxable_value'] ?? 0;
        $debitIGST = $grouped_debit[$key]['igst'] ?? 0;
        $debitCGST = $grouped_debit[$key]['cgst'] ?? 0;
        $debitSGST = $grouped_debit[$key]['sgst'] ?? 0;

        $finalData[$key] = [
            'rate' => $rate,
            'hsn' => $hsn,
            'unit_code' => $unit_code,
            'qty' => $salesqty + $debitqty - $creditqty,
            'taxable_value' => $salesTaxable + $debitTaxable - $creditTaxable,
            'igst' => $salesIGST + $debitIGST - $creditIGST,
            'cgst' => $salesCGST + $debitCGST - $creditCGST,
            'sgst' => $salesSGST + $debitSGST - $creditSGST,
        ];
    }
    // Pass final adjusted data to view
    return view('gstReturn.hsnSummary', ['data' => array_values($finalData) ,'merchant_gst' => $merchant_gst, 'company_id' => $company_id,'from_date' => $from_date,'to_date' => $to_date]);
}

public function documentIssuedSummary(REQUEST $request){

    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $salesGrouped = DB::table('sales')
        ->where('company_id', $company_id)
        ->where('merchant_gst',$merchant_gst)
        ->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0') // Exclude soft-deleted records
        ->whereNotNull('voucher_no_prefix') // Ensure voucher_no_prefix is present
        ->select('voucher_no_prefix', 'series_no', 'status')
        ->orderBy('series_no')
        ->orderBy('voucher_no_prefix')
        ->get()
        ->groupBy('series_no');
    // echo "<pre>";
    // print_r($salesGrouped->toArray());
    // die;
    $SalesdocumentSummary = [];

    foreach ($salesGrouped as $series => $records) {
        $total = $records->count();
        $cancelled = $records->where('status', 2)->count();
        // $from = $records->first()->voucher_no_prefix ?? '-';
        // $to = $records->last()->voucher_no_prefix ?? '-';
        // Find min and max voucher_no_prefix numerically
        // Extract numeric part from voucher_no_prefix
        // Map voucher_no_prefix with extracted number
        $voucherMap = $records->map(function ($item) {
            preg_match('/(\d+)$/', $item->voucher_no_prefix, $matches);
            return [
                'original' => $item->voucher_no_prefix,
                'number'   => isset($matches[1]) ? (int)$matches[1] : null
            ];
        })->filter(function ($v) {
            return $v['number'] !== null;
        });

        // Find min and max based on number
        $minVoucher = $voucherMap->sortBy('number')->first();
        $maxVoucher = $voucherMap->sortByDesc('number')->first();

        $from = $minVoucher['original'] ?? '-';
        $to   = $maxVoucher['original'] ?? '-';

        
        $SalesdocumentSummary[] = [
            'series_no' => $series ?? '-',
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'cancelled' => $cancelled,
            'net_issued' => $total - $cancelled,
        ];
    }


    $allCreditNotes = DB::table('sales_returns')
    ->where('company_id', $company_id)
    ->where('merchant_gst',$merchant_gst)
    ->whereBetween('date', [$from_date, $to_date])
    ->where('delete', '0')
    ->whereNotNull('sr_prefix')
    ->select('sr_prefix', 'series_no', 'status', 'voucher_type','sr_nature')
    ->orderBy('series_no')
    ->orderBy('sr_prefix')
    ->get()
    ->groupBy('series_no');

$CreditNotedocumentSummary = [];

foreach ($allCreditNotes as $series => $records) {
    $from = $records->first()->sr_prefix ?? '-';
    $to = $records->last()->sr_prefix ?? '-';

    // Filter only SALE voucher_type for total and cancelled
    $saleRecords = $records->where('voucher_type', 'SALE')
                           ->where('sr_nature', 'WITH GST');
                   

    $total = $saleRecords->count();
    $cancelled = $saleRecords->where('status', 2)->count();

    $CreditNotedocumentSummary[] = [
        'series_no'   => $series ?? '-',
        'from'        => $from,
        'to'          => $to,
        'total'       => $total,
        'cancelled'   => $cancelled,
        'net_issued'  => $total - $cancelled,
    ];
}




 $allDebitNotes = DB::table('purchase_returns')
    ->where('company_id', $company_id)
    ->where('merchant_gst',$merchant_gst)
    ->whereBetween('date', [$from_date, $to_date])
    ->where('delete', '0')
    ->whereNotNull('sr_prefix')
    ->select('sr_prefix', 'series_no', 'status', 'voucher_type','sr_nature')
    ->orderBy('series_no')
    ->orderBy('sr_prefix')
    ->get()
    ->groupBy('series_no');

    $DebitNotedocumentSummary = [];

foreach ($allDebitNotes as $series => $records) {
    // $from = $records->first()->sr_prefix ?? '-';
    // $to = $records->last()->sr_prefix ?? '-';
    $voucherMap = $records->map(function ($item) {
            preg_match('/(\d+)$/', $item->sr_prefix, $matches);
            return [
                'original' => $item->sr_prefix,
                'number'   => isset($matches[1]) ? (int)$matches[1] : null
            ];
        })->filter(function ($v) {
            return $v['number'] !== null;
        });

        // Find min and max based on number
        $minVoucher = $voucherMap->sortBy('number')->first();
        $maxVoucher = $voucherMap->sortByDesc('number')->first();

        $from = $minVoucher['original'] ?? '-';
        $to   = $maxVoucher['original'] ?? '-';
    // Filter only SALE voucher_type for total and cancelled
    $saleRecords = $records->where('voucher_type', 'SALE')
                           ->where('sr_nature', 'WITH GST');

    $total = $saleRecords->count();
    $cancelled = $saleRecords->where('status', 2)->count();

    $DebitNotedocumentSummary[] = [
        'series_no'   => $series ?? '-',
        'from'        => $from,
        'to'          => $to,
        'total'       => $total,
        'cancelled'   => $cancelled,
        'net_issued'  => $total - $cancelled,
    ];
}

$payments = DB::table('payments')
    ->where('company_id', $company_id)
    ->whereBetween('date', [$from_date, $to_date])
    ->where('delete', '0')
    ->whereNotNull('voucher_no')
    ->select('voucher_no', 'series_no', 'status')
    ->orderBy('series_no')
    ->orderBy('voucher_no')
    ->get()
    ->groupBy('series_no');

      $paymentsDocumentSummary = [];

    foreach ($payments as $series => $records) {
        $total = $records->count();
        $cancelled = $records->where('status', 2)->count();
        $from = $records->first()->voucher_no ?? '-';
        $to = $records->last()->voucher_no ?? '-';

        $paymentsDocumentSummary[] = [
            'series_no' => $series ?? '-',
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'cancelled' => $cancelled,
            'net_issued' => $total - $cancelled,
        ];
    }

    $receipts = DB::table('receipts')
    ->where('company_id', $company_id)
    ->whereBetween('date', [$from_date, $to_date])
    ->where('delete', '0')
    ->whereNotNull('voucher_no')
    ->select('voucher_no', 'series_no', 'status')
    ->orderBy('series_no')
    ->orderBy('voucher_no')
    ->get()
    ->groupBy('series_no');

      $receiptsDocumentSummary = [];

    foreach ($receipts as $series => $records) {
        $total = $records->count();
        $cancelled = $records->where('status', 2)->count();
        $from = $records->first()->voucher_no ?? '-';
        $to = $records->last()->voucher_no ?? '-';

        $receiptsDocumentSummary[] = [
            'series_no' => $series ?? '-',
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'cancelled' => $cancelled,
            'net_issued' => $total - $cancelled,
        ];
    }

    return view('gstReturn.documentIssuedSummary', compact('SalesdocumentSummary','DebitNotedocumentSummary','CreditNotedocumentSummary','paymentsDocumentSummary','receiptsDocumentSummary', 'from_date', 'to_date','merchant_gst'));

}


    /**
     * Helper function to process HSN summary for B2B or B2C.
     *
     * @param string $merchant_gst
     * @param int $company_id
     * @param string $from_date
     * @param string $to_date
     * @param int $user_company_id
     * @param \Illuminate\Support\Collection $sundries
     * @param bool $isB2B True for B2B, false for B2C
     * @param array $finalData Reference to the array to store the final grouped data
     */
    private function processHsnSummary(
        $merchant_gst,
        $company_id,
        $from_date,
        $to_date,
        $user_company_id,
        $sundries,
        $isB2B,
        &$finalData
    ) {
        $typeCondition = function ($q) use ($isB2B) {
            if ($isB2B) {
                $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
            } else {
                $q->whereNull('billing_gst')->orWhere('billing_gst', '');
            }
        };

        // -------- STEP 1: Get Sale IDs --------
        $saleIds = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('delete', '0')
            ->where('status', '1')
            ->where($typeCondition)
            ->pluck('id');

        if ($saleIds->isEmpty()) {
            // No sales for this type, return early
            $finalData = [];
            return;
        }

        // ----- SALES ITEMS -----
        $items_sale = DB::table('sale_descriptions')
            ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
            ->join('accounts', 'sales.party', '=', 'accounts.id')
            ->join('states', 'accounts.state', '=', 'states.id')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->where('sale_descriptions.status', '1')
            ->where('sale_descriptions.delete', '0')
            ->whereIn('sale_id', $saleIds)
            ->select(
                'sale_descriptions.sale_id',
                'states.name as state_name',
                'sale_descriptions.qty',
                'sale_descriptions.amount',
                'manage_items.gst_rate',
                'units.unit_code',
                'manage_items.hsn_code',
                'manage_items.item_type'
            )
            ->get();

        // Sundries and ledger adjustments for sales
        $sundryDetails_sales = DB::table('sale_sundries')
            ->whereIn('sale_id', $saleIds)
            ->select('sale_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_id');

        $ledgerAdjustments_sales = [];
        foreach ($sundryDetails_sales as $saleId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs) continue;

                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $saleId)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_sales[$saleId][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }

        // --------- CREDIT NOTES (Sales returns) -----------
        $creditSales = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type', 'WITH ITEM')
            ->where($typeCondition)
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');

        $creditSales1 = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where($typeCondition)
            ->where(function ($q) {
                $q->where('sr_type', 'WITHOUT ITEM')
                    ->orWhere('sr_type', 'RATE DIFFERENCE');
            })
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');

        $creditItems = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('sale_return_id', $creditSales)
            ->where('sale_return_descriptions.status', '1')
            ->where('sale_return_descriptions.delete', '0')
            ->select(
                'sale_return_descriptions.sale_return_id',
                'states.name as state_name',
                'sale_return_descriptions.qty',
                'manage_items.gst_rate',
                'sale_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code',
            )
            ->get();

        $creditItems1 = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->where($typeCondition)
            ->whereIn('sale_return_id', $creditSales1)
            ->where('sale_return_descriptions.status', '1')
            ->where('sale_return_descriptions.delete', '0')
            ->select(
                'sale_return_descriptions.sale_return_id',
                'states.name as state_name',
                DB::raw('0 as qty'), // Override qty with 0
                'manage_items.gst_rate',
                'sale_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code',
            )
            ->get();

        $creditWithoutGstItems = DB::table('sale_return_without_gst_entry')
            ->join('sales_returns', 'sale_return_without_gst_entry.sale_return_id', '=', 'sales_returns.id')
            ->leftJoin('states', 'sales_returns.billing_state', '=', 'states.id')
            ->where('sale_return_without_gst_entry.percentage', '>', 0)
            ->whereIn('sale_return_without_gst_entry.sale_return_id', $creditSales1)
            ->where('sale_return_without_gst_entry.status', '1')
            ->where('sale_return_without_gst_entry.delete', '0')
            ->select(
                'sale_return_without_gst_entry.sale_return_id as sale_return_id',
                'states.name as state_name',
                DB::raw('0 as qty'),
                'sale_return_without_gst_entry.percentage as gst_rate',
                'sale_return_without_gst_entry.debit as amount',
                'sale_return_without_gst_entry.hsn_code',
                'sale_return_without_gst_entry.unit_code'
            )
            ->get();

        $creditItems = $creditItems->merge($creditItems1)->merge($creditWithoutGstItems);

        $creditSundryDetails = DB::table('sale_return_sundries')
            ->whereIn('sale_return_id', $creditSales)
            ->select('sale_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_return_id');

        $ledgerAdjustments_credit = [];
        foreach ($creditSundryDetails as $return_id => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs) continue;

                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $return_id)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_credit[$return_id][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }

        // ---------- DEBIT NOTES (Purchase returns) -----------
        $debitSales = DB::table('purchase_returns')
            ->where('purchase_returns.merchant_gst', $merchant_gst)
            ->where('purchase_returns.company_id', $company_id)
            ->whereBetween('purchase_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type', 'WITH ITEM')
            ->where($typeCondition)
            ->where('purchase_returns.delete', '0')
            ->where('purchase_returns.status', '1')
            ->pluck('purchase_returns.id');

        $debitSales1 = DB::table('purchase_returns')
            ->where('purchase_returns.merchant_gst', $merchant_gst)
            ->where('purchase_returns.company_id', $company_id)
            ->whereBetween('purchase_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where(function ($q) {
                $q->where('sr_type', 'WITHOUT ITEM')
                    ->orWhere('sr_type', 'RATE DIFFERENCE');
            })
            ->where($typeCondition)
            ->where('purchase_returns.delete', '0')
            ->where('purchase_returns.status', '1')
            ->pluck('purchase_returns.id');

        $debitItems = DB::table('purchase_return_descriptions')
            ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
            ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('purchase_return_id', $debitSales)
            ->where('purchase_return_descriptions.status', '1')
            ->where('purchase_return_descriptions.delete', '0')
            ->select(
                'purchase_return_descriptions.purchase_return_id',
                'states.name as state_name',
                'manage_items.gst_rate',
                'purchase_return_descriptions.qty',
                'purchase_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code'
            )
            ->get();

        $debitItems1 = DB::table('purchase_return_descriptions')
            ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
            ->join('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->where('purchase_return_descriptions.status', '1')
            ->where('purchase_return_descriptions.delete', '0')
            ->whereIn('purchase_return_id', $debitSales1)
            ->select(
                'purchase_return_descriptions.purchase_return_id',
                'states.name as state_name',
                'manage_items.gst_rate',
                DB::raw('0 as qty'),
                'purchase_return_descriptions.amount',
                'units.unit_code',
                'manage_items.hsn_code'
            )
            ->get();

        $debitWithoutGstItems = DB::table('purchase_return_entries')
            ->join('purchase_returns', 'purchase_return_entries.purchase_return_id', '=', 'purchase_returns.id')
            ->leftJoin('states', 'purchase_returns.billing_state', '=', 'states.id')
            ->where('purchase_return_entries.percentage', '>', 0)
            ->whereIn('purchase_return_entries.purchase_return_id', $debitSales->merge($debitSales1)) // Use merged IDs
            ->where('purchase_return_entries.status', '1')
            ->where('purchase_return_entries.delete', '0')
            ->select(
                'purchase_return_entries.purchase_return_id as purchase_return_id',
                'states.name as state_name',
                'purchase_return_entries.percentage as gst_rate',
                DB::raw('0 as qty'),
                'purchase_return_entries.debit as amount',
                'purchase_return_entries.hsn_code',
                'purchase_return_entries.unit_code'
            )
            ->get();
        $debitItems = $debitItems->merge($debitItems1)->merge($debitWithoutGstItems);

        $debitSundryDetails = DB::table('purchase_return_sundries')
            ->whereIn('purchase_return_id', $debitSales->merge($debitSales1)) // Use merged IDs
            ->select('purchase_return_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('purchase_return_id');

        $ledgerAdjustments_debit = [];
        foreach ($debitSundryDetails as $return_id => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs) continue;

                if ($bs->adjust_sale_amt == 'No' && $bs->nature_of_sundry == 'OTHER') {
                    $ledger_amount = DB::table('account_ledger')
                        ->where('company_id', $user_company_id)
                        ->where('entry_type', 1)
                        ->where('entry_type_id', $return_id)
                        ->where('account_id', $bs->sale_amt_account)
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'debit' : 'credit');

                    $ledgerAdjustments_debit[$return_id][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }
        
        // --------- Group and calculate for sales --------
        $grouped_sale = [];
        foreach ($items_sale as $item) {
            $sale_id = $item->sale_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $amount = $item->amount;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            // $item_type = $item->item_type; // Not used in final calculation

            $total_sale_amount = $items_sale->where('sale_id', $sale_id)->sum('amount');

            if ($total_sale_amount == 0) continue;

            $adjusted_value = 0;

            // Adjust sundry
            if (isset($sundryDetails_sales[$sale_id])) {
                foreach ($sundryDetails_sales[$sale_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;
                    $share = ($amount / $total_sale_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }

            // Adjust ledger
            if (isset($ledgerAdjustments_sales[$sale_id])) {
                foreach ($ledgerAdjustments_sales[$sale_id] as $adj) {
                    $share = ($amount / $total_sale_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }

            $taxable_value = $amount + $adjusted_value;

            // Tax calculation
            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');

            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }

            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_sale[$key])) {
                $grouped_sale[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }

            $grouped_sale[$key]['qty'] += $qty;
            $grouped_sale[$key]['taxable_value'] += $taxable_value;
            $grouped_sale[$key]['igst'] += $igst;
            $grouped_sale[$key]['cgst'] += $cgst;
            $grouped_sale[$key]['sgst'] += $sgst;
        }

        // ---------- Group and calculate for credit notes (subtract) ----------
        $grouped_credit = [];
        foreach ($creditItems as $item) {
            $return_id = $item->sale_return_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            $amount = $item->amount;

            $total_credit_amount = $creditItems->where('sale_return_id', $return_id)->sum('amount');
            if ($total_credit_amount == 0) continue;

            $adjusted_value = 0;

            if (isset($creditSundryDetails[$return_id])) {
                foreach ($creditSundryDetails[$return_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                    $share = ($amount / $total_credit_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }

            // Adjust ledger
            if (isset($ledgerAdjustments_credit[$return_id])) {
                foreach ($ledgerAdjustments_credit[$return_id] as $adj) {
                    $share = ($amount / $total_credit_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }

            $taxable_value = $amount + $adjusted_value;

            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');

            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }

            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_credit[$key])) {
                $grouped_credit[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }

            $grouped_credit[$key]['qty'] += $qty;
            $grouped_credit[$key]['taxable_value'] += $taxable_value;
            $grouped_credit[$key]['igst'] += $igst;
            $grouped_credit[$key]['cgst'] += $cgst;
            $grouped_credit[$key]['sgst'] += $sgst;
        }

        // --------- Group and calculate for debit notes (add) -----------
        $grouped_debit = [];
        foreach ($debitItems as $item) {
            $return_id = $item->purchase_return_id;
            $state = $item->state_name;
            $rate = $item->gst_rate;
            $qty = $item->qty;
            $hsn = $item->hsn_code;
            $unit_code = $item->unit_code;
            $amount = $item->amount;

            $total_debit_amount = $debitItems->where('purchase_return_id', $return_id)->sum('amount');
            if ($total_debit_amount == 0) continue;

            $adjusted_value = 0;

            if (isset($debitSundryDetails[$return_id])) {
                foreach ($debitSundryDetails[$return_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                    $share = ($amount / $total_debit_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }

            // Adjust ledger
            if (isset($ledgerAdjustments_debit[$return_id])) {
                foreach ($ledgerAdjustments_debit[$return_id] as $adj) {
                    $share = ($amount / $total_debit_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }

            $taxable_value = $amount + $adjusted_value;

            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            $merchant_state_code = substr($merchant_gst, 0, 2);
            $customer_state_code = State::where('name', $state)->value('state_code');

            if ($merchant_state_code == $customer_state_code) {
                $cgst = $sgst = ($rate / 2 / 100) * $taxable_value;
            } else {
                $igst = ($rate / 100) * $taxable_value;
            }

            $key = $hsn . '|' . $unit_code . '|' . $rate;
            if (!isset($grouped_debit[$key])) {
                $grouped_debit[$key] = [
                    'hsn' => $hsn,
                    'unit_code' => $unit_code,
                    'rate' => $rate,
                    'taxable_value' => 0,
                    'qty' => 0,
                    'igst' => 0,
                    'cgst' => 0,
                    'sgst' => 0,
                ];
            }

            $grouped_debit[$key]['qty'] += $qty;
            $grouped_debit[$key]['taxable_value'] += $taxable_value;
            $grouped_debit[$key]['igst'] += $igst;
            $grouped_debit[$key]['cgst'] += $cgst;
            $grouped_debit[$key]['sgst'] += $sgst;
        }
        // echo "<pre>";
        // print_r($grouped_debit);
        // echo "</pre>";
        // -------- Combine final adjusted data: sales + debit - credit -------
        $allKeys = collect(array_unique(array_merge(array_keys($grouped_sale), array_keys($grouped_credit), array_keys($grouped_debit))));

        foreach ($allKeys as $key) {
            $hsn = $grouped_sale[$key]['hsn'] ?? $grouped_credit[$key]['hsn'] ?? $grouped_debit[$key]['hsn'] ?? null;
            $rate = $grouped_sale[$key]['rate'] ?? $grouped_credit[$key]['rate'] ?? $grouped_debit[$key]['rate'] ?? null;
            $unit_code = $grouped_sale[$key]['unit_code'] ?? $grouped_credit[$key]['unit_code'] ?? $grouped_debit[$key]['unit_code'] ?? null;

            $salesqty = $grouped_sale[$key]['qty'] ?? 0;
            $salesTaxable = $grouped_sale[$key]['taxable_value'] ?? 0;
            $salesIGST = $grouped_sale[$key]['igst'] ?? 0;
            $salesCGST = $grouped_sale[$key]['cgst'] ?? 0;
            $salesSGST = $grouped_sale[$key]['sgst'] ?? 0;

            $creditqty = $grouped_credit[$key]['qty'] ?? 0;
            $creditTaxable = $grouped_credit[$key]['taxable_value'] ?? 0;
            $creditIGST = $grouped_credit[$key]['igst'] ?? 0;
            $creditCGST = $grouped_credit[$key]['cgst'] ?? 0;
            $creditSGST = $grouped_credit[$key]['sgst'] ?? 0;

            $debitqty = $grouped_debit[$key]['qty'] ?? 0;
            $debitTaxable = $grouped_debit[$key]['taxable_value'] ?? 0;
            $debitIGST = $grouped_debit[$key]['igst'] ?? 0;
            $debitCGST = $grouped_debit[$key]['cgst'] ?? 0;
            $debitSGST = $grouped_debit[$key]['sgst'] ?? 0;

            $finalData[$key] = [
                'rate' => $rate,
                'hsn' => $hsn,
                'unit_code' => $unit_code,
                'qty' => $salesqty + $debitqty - $creditqty,
                'taxable_value' => $salesTaxable + $debitTaxable - $creditTaxable,
                'igst' => $salesIGST + $debitIGST - $creditIGST,
                'cgst' => $salesCGST + $debitCGST - $creditCGST,
                'sgst' => $salesSGST + $debitSGST - $creditSGST,
            ];
        }
    }

    public function storeTurnOver(Request $request){
        DB::table('company_turnovers')->updateOrInsert(
            ['financial_year' => $request->fy,'company_id'=>$request->company_id,'gstin'=>$request->merchant_gst], // condition
            [
                'amount' => $request->amount,
                'gstin'      => $request->merchant_gst,
                'company_id'      => $request->company_id,
                'financial_year'      => $request->fy,
                'updated_at'      => now(),
                'created_at'      => now(),
            ]
        );
        $response = array(
            'status' => true,
            'message' => 'TurnOver Saved Successfully.',
        );
        return json_encode($response);


    }
}








