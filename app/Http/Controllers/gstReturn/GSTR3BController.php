<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Session;
use DB;
use App\Models\State;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\gstToken;


class GSTR3BController extends Controller
{


    public function filterform()
{
      $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }else if($company->gst_config_type == "multiple_gst"){
            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }
        return view('gstReturn.filterIndex3b',['gst'=>$gst]);

}





    public function index(Request $request){
       $merchant_gst = $request->series;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
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
  $url = "https://api.mastergst.com/gstr3b/retsum";
    
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'gst_username' => $gst_user_name,
        'state_cd' => $state_code,
        'ip_address' => '152.25.59.138',
        'txn' => $txn,
        'client_id' => 'GSPdea8d6fb-aed1-431a-b589-f1c541424580',
        'client_secret' => 'GSP4c44b790-ef11-4725-81d9-5f8504279d67',
    ])->get($url, [
        'gstin' => $merchant_gst,
        'retperiod' => $month,
        'email' => $email,
    ]);

    $data = $response->json();
        return view('gstReturn.GSTR3B', ['data' => $data['data'], 'merchant_gst' => $merchant_gst, 'from_date' =>  $from_date , 'to_date' =>  $to_date ]);
    
    
     }






  public function OutwardDetails(Request $request){
   
    $merchant_gst = $request->series;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
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
  $email = 'pram92500@gmail.com';

     $url = "https://api.mastergst.com/gstr3b/retsum";
    
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'gst_username' => $gst_user_name,
        'state_cd' => $state_code,
        'ip_address' => '152.25.59.138',
        'txn' => $txn,
        'client_id' => 'GSPdea8d6fb-aed1-431a-b589-f1c541424580',
        'client_secret' => 'GSP4c44b790-ef11-4725-81d9-5f8504279d67',
    ])->get($url, [
        'gstin' => $merchant_gst,
        'retperiod' => $month,
        'email' => $email,
    ]);

    $data = $response->json();




     $response1 = Http::withHeaders([
            'Accept'        => 'application/json',
            'gst_username'  => $gst_user_name,
            'state_cd'      => $state_code,
            'ip_address'    => '152.25.59.138',
            'txn'           => $txn,
            'client_id'     => 'GSPdea8d6fb-aed1-431a-b589-f1c541424580',
            'client_secret' => 'GSP4c44b790-ef11-4725-81d9-5f8504279d67',
        ])->get('https://api.mastergst.com/gstr3b/autoliab', [
            'gstin'     => $merchant_gst,
            'retperiod' => $month,
            'email'     => $email,
        ]);

        if ($response1->failed()) {
            abort(500, 'GST API request failed.');
        }

        $json = $response1->json(); // Parses response body as array :contentReference[oaicite:1]{index=1}

        $data1 = $json['data']['r3bautopop']['liabitc'] ?? [];

    

       $company_id = Session::get('user_company_id');
       

         $sundries = BillSundrys::where('company_id', $company_id)->get()->keyBy('id');

        $saleIds = DB::table('sales')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('delete', '0')
            ->where('status', '1')
            ->pluck('id');


        if ($saleIds->isEmpty()) {
            // No sales for this type, return early
            $finalData = [];
         
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
                        ->where('company_id', $company_id)
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

        $grouped_sale = [];
        $taxable_value_sale = 0; 
        $cgst_sale = 0; 
        $sgst_sale = 0;
        $igst_sale =0;
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
            $taxable_value_sale += $taxable_value;

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
            $cgst_sale += $cgst;
            $sgst_sale += $sgst;
            $igst_sale += $igst;
        
        }


         $creditSales = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
            ->where('sr_type', 'WITH ITEM')
            ->where('sales_returns.delete', '0')
            ->where('sales_returns.status', '1')
            ->pluck('sales_returns.id');

        $creditSales1 = DB::table('sales_returns')
            ->where('sales_returns.merchant_gst', $merchant_gst)
            ->where('sales_returns.company_id', $company_id)
            ->whereBetween('sales_returns.date', [$from_date, $to_date])
            ->where('voucher_type', 'SALE')
            ->where('sr_nature', 'WITH GST')
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
                'manage_items.hsn_code'
            )
            ->get();

        $creditItems1 = DB::table('sale_return_descriptions')
            ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
            ->join('states', 'sales_returns.billing_state', '=', 'states.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
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
                'manage_items.hsn_code'
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
                        ->where('company_id', $company_id)
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
                        ->where('company_id', $company_id)
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




         $grouped_credit = [];
         $taxable_value_credit = 0;
         $igst_credit = 0;
         $cgst_credit = 0;
         $sgst_credit = 0;
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
            $taxable_value_credit += $taxable_value;

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

           $igst_credit += $igst;
           $cgst_credit += $cgst;
           $sgst_credit += $sgst;
        }

        // --------- Group and calculate for debit notes (add) -----------
        $grouped_debit = [];
        $taxable_value_debit = 0;
        $igst_debit = 0;
        $cgst_debit = 0; 
        $sgst_debit = 0;
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
            $taxable_value_debit += $taxable_value;

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
            
            $igst_debit += $igst;
            $cgst_debit += $cgst;
            $sgst_debit += $sgst;
            
        }
         
        $net_note_taxable = $taxable_value_debit - $taxable_value_credit ;
        $net_note_igst = $igst_debit - $igst_credit;
        $net_note_cgst = $cgst_debit - $cgst_credit;
        $net_note_sgst = $sgst_debit - $sgst_credit;
    return  view('gstReturn.gst3b_outward_table' , ['data' => $data['data'], 'data1' => $data1 ,'taxable_value_sale' => $taxable_value_sale , 'cgst_sale' => $cgst_sale , 'sgst_sale' => $sgst_sale , 'igst_sale' => $igst_sale , 'net_note_taxable' => $net_note_taxable , 'net_note_igst' => $net_note_igst , 'net_note_cgst' => $net_note_cgst , 'net_note_sgst' => $net_note_sgst ,'series' => $merchant_gst, 'from_date' =>  $from_date , 'to_date' =>  $to_date]);

  }



  public function itcDetails(Request $request){

$response = Http::withHeaders([
            'Accept'        => 'application/json',
            'gst_username'  => 'KRAFTPAPER1991',
            'state_cd'      => '07',
            'ip_address'    => '152.25.59.138',
            'txn'           => '3396251fbb8446ac9ba89cca8a1ac862',
            'client_id'     => 'GSPdea8d6fb-aed1-431a-b589-f1c541424580',
            'client_secret' => 'GSP4c44b790-ef11-4725-81d9-5f8504279d67',
        ])->get('https://api.mastergst.com/gstr3b/autoliab', [
            'gstin'     => '07AAJCK4433F1ZM',
            'retperiod' => '042025',
            'email'     => 'pram92500@gmail.com',
        ]);

        if ($response->failed()) {
            abort(500, 'GST API request failed.');
        }

        $json = $response->json(); // Parses response body as array :contentReference[oaicite:1]{index=1}

        $data = $json['data']['r3bautopop']['liabitc'] ?? [];


       $company_id = Session::get('user_company_id');
       $merchant_gst = $request->series;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

         $sundries = BillSundrys::where('company_id', $company_id)->get()->keyBy('id');

        $saleIds = DB::table('purchases')
            ->where('merchant_gst', $merchant_gst)
            ->where('company_id', $company_id)
            ->whereBetween('date', [$from_date, $to_date])
            ->where('delete', '0')
            ->where('status', '1')
            ->pluck('id');


        if ($saleIds->isEmpty()) {
            // No sales for this type, return early
            $finalData = [];
            return;
        }
        

        // ----- SALES ITEMS -----
        $items_sale = DB::table('purchase_descriptions')
            ->join('purchases', 'purchase_descriptions.purchase_id', '=', 'purchases.id')
            ->join('accounts', 'purchases.party', '=', 'accounts.id')
            ->join('states', 'accounts.state', '=', 'states.id')
            ->join('manage_items', 'purchase_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->where('purchase_descriptions.status', '1')
            ->where('purchase_descriptions.delete', '0')
            ->whereIn('purchase_id', $saleIds)
            ->select(
                'purchase_descriptions.purchase_id',
                'states.name as state_name',
                'purchase_descriptions.qty',
                'purchase_descriptions.amount',
                'manage_items.gst_rate',
                'units.unit_code',
                'manage_items.hsn_code',
                'manage_items.item_type'
            )
            ->get();

        // Sundries and ledger adjustments for purchases
        $sundryDetails_sales = DB::table('purchase_sundries')
            ->whereIn('purchase_id', $saleIds)
            ->select('purchase_id', 'bill_sundry', 'amount')
            ->get()
            ->groupBy('purchase_id');

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
                        ->sum($bs->bill_sundry_type == 'subtractive' ? 'credit' : 'debit');

                    $ledgerAdjustments_sales[$saleId][] = [
                        'amount' => $ledger_amount,
                        'type' => $bs->bill_sundry_type
                    ];
                }
            }
        }

        $grouped_sale = [];
        $taxable_value_sale = 0; 
        $cgst_sale = 0; 
        $sgst_sale = 0;
        $igst_sale =0;
        foreach ($items_sale as $item) {
            $sale_id = $item->purchase_id;
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
            $taxable_value_sale += $taxable_value;

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
            $cgst_sale += $cgst;
            $sgst_sale += $sgst;
            $igst_sale += $igst;
        
        }

    return  view('gstReturn.gst3b_itc_table' , ['portal' => $data , 'taxable_value_sale' => $taxable_value_sale , 'taxable_value_sale' => $taxable_value_sale  ]);

  }
    
}
