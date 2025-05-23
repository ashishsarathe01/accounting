<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Session;
use DateTime;
use App\Models\BillSundrys;
use App\Models\SaleSundry;

class gstR1Controller extends Controller
{
    public function gstmain(Request $request)
    {
        $input = $request->all();
        $financial_year = Session::get('default_fy');
        $y = explode("-",$financial_year);
        $from_date = $y['0']."-04-01";
        $from_date = date('Y-m-d',strtotime($from_date));
        $to_date = date('Y-m-t'); 
        $merchant_gst = '07AEAPG0237M1ZR';
        $company_id = '8';


        // // Handle date input and session logic
        // if (!empty($input['from_date']) && !empty($input['to_date'])) {
        //     $from_date = date('Y-m-d', strtotime($input['from_date']));
        //     $to_date = date('Y-m-d', strtotime($input['to_date']));

        //     session(['gstr1_from_date' => $from_date, 'gstr1_to_date' => $to_date]);
        // } elseif (session()->has('gstr1_from_date') && session()->has('gstr1_to_date')) {
        //     $from_date = session('gstr1_from_date');
        //     $to_date = session('gstr1_to_date');
        // }

        // Get merchant_gst and company_id from input
       // $merchant_gst = $input['merchant_gst'] ?? null;
        //$company_id = $input['company_id'] ?? null;

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
        ->selectRaw('SUM(taxable_amt) as total_taxable_amt, SUM(total) as total_sale_amt')
        ->first();
    
    
    // Get matching sale IDs
    $saleIds = DB::table('sales')
        ->where('merchant_gst', $merchant_gst)
        ->where('company_id', $company_id)
        ->whereDate('date', '>=', $from_date)
        ->whereDate('date', '<=', $to_date)
        ->whereNotNull('billing_gst') 
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
->whereNull('billing_gst')
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
            'b2c_normal_taxable_amt' => $summaryB2CNormal->total_taxable_amt ?? 0,
            'b2c_normal_sale_amt' => $summaryB2CNormal->total_sale_amt ?? 0,
            'b2c_normal_cgst' => $taxSummaryB2CNormal->total_cgst ?? 0,
            'b2c_normal_sgst' => $taxSummaryB2CNormal->total_sgst ?? 0,
            'b2c_normal_igst' => $taxSummaryB2CNormal->total_igst ?? 0,
        
            // Required for Route Links
            'merchant_gst' => $merchant_gst,
            'company_id' => $company_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
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

    

    public function B2Cstatewise(Request $request)
{
    $merchant_gst = $request->merchant_gst;
    $company_id = $request->company_id;
    $from_date = $request->from_date;
    $to_date = $request->to_date;

    $user_company_id = Session::get('user_company_id');

    // Step 1: Fetch all Bill Sundrys for the company
    $sundries = BillSundrys::where('company_id', $user_company_id)->get()->keyBy('id');
    // echo "<pre>";
    // print_r($sundries->toArray());
    // Step 2: Get B2C Sale IDs
    $b2cSaleIds = DB::table('sales')
        //->where('merchant_gst', $merchant_gst)
        ->where('company_id', $user_company_id)
        //->whereBetween('date', [$from_date, $to_date])
        ->where('delete', '0')
        ->where('status', '1')
        //->whereNull('billing_gst')
        ->where('total', '<=', 250000)
        ->pluck('id');

    if ($b2cSaleIds->isEmpty()) {
        dd("ddd");
        return view('gstReturn.b2c_statewise', ['data' => []]);
    }
    //echo $b2cSaleIds;
    // Step 3: Get sale item details
    $items = DB::table('sale_descriptions')
        ->join('sales', 'sale_descriptions.sale_id', '=', 'sales.id')
        ->join('accounts', 'sales.party', '=', 'accounts.id')
        ->join('states', 'accounts.state', '=', 'states.id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->whereIn('sale_id', $b2cSaleIds)
        ->select(
            'sale_descriptions.id as sale_desc_id',
            'sale_descriptions.sale_id',
            'states.name as state_name',
            'manage_items.gst_rate',
            'sale_descriptions.amount'
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

            if ($bs->adjust_sale_amt == 'No' && $bs->bill_sundry_type == 'Other') {
                $ledger_amount = DB::table('account_ledgers')
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
    // echo "<pre>";print_r($items->toArray());
    // print_r($sundryDetails->toArray());
    foreach ($items as $item) {
        $sale_id = $item->sale_id;
        $state = $item->state_name;
        $rate = $item->gst_rate;
        $item_amount = $item->amount;

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
        if ($merchant_gst == '') {
            $cgst = ($rate / 2 / 100) * $taxable_value;
            $sgst = ($rate / 2 / 100) * $taxable_value;
        } else {
            $igst = ($rate / 100) * $taxable_value;
        }

        $key = $state . '|' . $rate;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'state' => $state,
                'rate' => $rate,
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

    $data = array_values($grouped);

    return view('gstReturn.b2c_statewise', compact('data', 'merchant_gst', 'company_id', 'from_date', 'to_date'));
}


}