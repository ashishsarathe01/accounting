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
use App\Models\GstBranch;
use App\Models\Companies;



class gstR1Controller extends Controller
{


public function filterform()
{
    $companyId = Session::get('user_company_id');
    $companyData = Companies::where('id', $companyId)->first();
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

    return view('gstReturn.filterIndex', compact('seriesList'));
}





    public function gstmain(Request $request)
    {
        $input = $request->all();
        $merchant_gst = $request->series;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $company_id = Session::get('user_company_id');


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

if ($b2cNormalStateSaleIds->isNotEmpty()) {
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


    
        // Return result
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

            'b2c_statewise_taxable' => $b2cTaxableTotal,
'b2c_statewise_cgst' => $b2cCGST,
'b2c_statewise_sgst' => $b2cSGST,
'b2c_statewise_igst' => $b2cIGST, // likely 0 unless interstate

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
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    if (!$merchant_gst || !$company_id || !$from_date || !$to_date) {
        return response()->json(['error' => 'Missing required filters'], 400);
    }

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

    // Return the grouped data to the view
    
    return view('gstReturn.b2bDetailed', ['grouped' => $grouped]);

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

    // Helper function to calculate taxable value
    $calculateTaxable = function ($sales, $item_type_filter) use ($user_company_id, $sundries) {
        if ($sales->pluck('id')->isEmpty()) return 0;

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

        $sundryDetails = DB::table('sale_sundries')
            ->whereIn('sale_id', $sales->pluck('id'))
            ->select('sale_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('sale_id');

        $ledgerAdjustments = [];
        foreach ($sundryDetails as $saleId => $entries) {
            foreach ($entries as $entry) {
                $bs = $sundries[$entry->bill_sundry] ?? null;
                if (!$bs || $bs->adjust_sale_amt == 'Yes' || $bs->nature_of_sundry !== 'OTHER') continue;

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

        $total_taxable_value = 0;

        foreach ($items as $item) {
            $sale_id = $item->sale_id;
            $item_amount = $item->amount;
            $item_type = $item->item_type;
            $total_item_amount = $items->where('sale_id', $sale_id)->sum('amount');
            if ($total_item_amount == 0) continue;

            $adjusted_value = 0;

            if (isset($sundryDetails[$sale_id])) {
                foreach ($sundryDetails[$sale_id] as $sundryEntry) {
                    $bs = $sundries[$sundryEntry->bill_sundry] ?? null;
                    if (!$bs || $bs->adjust_sale_amt != 'Yes' || $bs->nature_of_sundry != 'OTHER') continue;

                    $share = ($item_amount / $total_item_amount) * $sundryEntry->amount;
                    $adjusted_value += ($bs->bill_sundry_type == 'subtractive') ? -$share : $share;
                }
            }

            if (isset($ledgerAdjustments[$sale_id])) {
                foreach ($ledgerAdjustments[$sale_id] as $adj) {
                    $share = ($item_amount / $total_item_amount) * $adj['amount'];
                    $adjusted_value += ($adj['type'] == 'subtractive') ? -$share : $share;
                }
            }

            $taxable_value = $item_amount + $adjusted_value;

            if ($item_type === $item_type_filter) {
                $total_taxable_value += $taxable_value;
            }
        }

        return $total_taxable_value;
    };

    // Helper: Fetch sales
    $buildSalesQuery = function ($billing_gst_null, $is_intra) use ($merchant_gst, $company_id, $from_date, $to_date, $merchant_state_id) {
        $query = DB::table('sales')
            ->where('sales.merchant_gst', $merchant_gst)
            ->join('accounts', 'accounts.id', '=', 'sales.party')
            ->join('states', 'states.id', '=', 'sales.billing_state')
            ->where('sales.company_id', $company_id)
            ->whereDate('sales.date', '>=', $from_date)
            ->whereDate('sales.date', '<=', $to_date)
            ->where('sales.delete', '0')
            ->where('sales.status', '1');

      if ($billing_gst_null) {
    // For NULL or empty string
    $query->where(function ($q) {
        $q->whereNull('sales.billing_gst')
          ->orWhere('sales.billing_gst', '');
    });
} else {
    // For NOT NULL and not empty string
    $query->where(function ($q) {
        $q->whereNotNull('sales.billing_gst')
          ->where('sales.billing_gst', '!=', '');
    });
}


        if ($is_intra) {
            $query->where('sales.billing_state', '=', $merchant_state_id);
        } else {
            $query->where('sales.billing_state', '!=', $merchant_state_id);
        }

        return $query->select(
            'sales.id',
            'sales.date',
            'sales.voucher_no_prefix',
            'sales.total',
            'sales.billing_gst',
            'accounts.account_name as name',
            'states.name as POS',
            'sales.reverse_charge'
        )->get();
    };

    // Fetch all sales
    $reg_intra_sales = $buildSalesQuery(false, true);
    $reg_inter_sales = $buildSalesQuery(false, false);
    $unreg_intra_sales = $buildSalesQuery(true, true);
    $unreg_inter_sales = $buildSalesQuery(true, false);

    // Nil Rated Calculation
    $nil_rated_reg_intra = $calculateTaxable($reg_intra_sales, 'nil_rated');
    $nil_rated_reg_inter = $calculateTaxable($reg_inter_sales, 'nil_rated');
    $nil_rated_unreg_intra = $calculateTaxable($unreg_intra_sales, 'nil_rated');
    $nil_rated_unreg_inter = $calculateTaxable($unreg_inter_sales, 'nil_rated');

    //Exempted Calculation
    $exempted_reg_intra = $calculateTaxable($reg_intra_sales, 'exempted');
    $exempted_reg_inter = $calculateTaxable($reg_inter_sales, 'exempted');
    $exempted_unreg_intra = $calculateTaxable($unreg_intra_sales, 'exempted');
    $exempted_unreg_inter = $calculateTaxable($unreg_inter_sales, 'exempted');

    //Return Blade View
    return view('gstReturn.nilRatedExempted', compact(
        'nil_rated_reg_intra',
        'nil_rated_reg_inter',
        'nil_rated_unreg_intra',
        'nil_rated_unreg_inter',
        'exempted_reg_intra',
        'exempted_reg_inter',
        'exempted_unreg_intra',
        'exempted_unreg_inter',
        'merchant_gst',
        'company_id',
        'from_date',
        'to_date'
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


    return view('gstReturn.debitcreditnote', ['grouped' => $sortedGrouped]);
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


    return view('gstReturn.debitcreditnoteunreg', ['grouped' => $sortedGrouped]);
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
         ->join('sales','sales.id','=','sales_returns.sale_bill_id')
        ->where('sales.total','<=', 250000)
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
         ->join('sales','sales.id','=','purchase_returns.purchase_bill_id')
        ->where('sales.total','<=', 250000)
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
    return view('gstReturn.b2c_statewise', ['data' => array_values($finalData)]);
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
        if ($type === 'B2B') {
            $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
        } else {
            $q->whereNull('billing_gst')->orWhere('billing_gst', '');
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
        $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
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
         if ($type === 'B2B') {
        $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
         } else {
        $q->whereNull('billing_gst')->orWhere('billing_gst', '');
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
                                if ($type === 'B2B') {
                                    $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                                } else {
                                    $q->whereNull('billing_gst')->orWhere('billing_gst', '');
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
                if ($type === 'B2B') {
                    $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                } else {
                    $q->whereNull('billing_gst')->orWhere('billing_gst', '');
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
                if ($type === 'B2B') {
                    $q->whereNotNull('billing_gst')->where('billing_gst', '!=', '');
                } else {
                    $q->whereNull('billing_gst')->orWhere('billing_gst', '');
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
    return view('gstReturn.hsnSummary', ['data' => array_values($finalData)]);


}
}





