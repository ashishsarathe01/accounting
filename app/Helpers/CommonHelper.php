<?php
namespace App\Helpers;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\ItemLedger;
use App\Models\Accounts;
use App\Models\AccountGroups;
use App\Models\AccountLedger;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\gstToken;
use Carbon\Carbon;
use DB;
use Session;
class CommonHelper
{
    public static function ClosingStock($date,$req_series=null)
    {
        
        $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
        if($companyData->gst_config_type == "single_gst"){
            $series = DB::table('gst_settings')
                                ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                                ->get();
            $branch = GstBranch::select('id','branch_series as series')
                                ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                                ->get();
            if(count($branch)>0){
                $series = $series->merge($branch);
            }         
        }else if($companyData->gst_config_type == "multiple_gst"){
            $series = DB::table('gst_settings_multiple')
                                ->select('id','series')
                                ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                                ->get();
            foreach ($series as $key => $value) {
                $branch = GstBranch::select('id','branch_series as series')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                            ->get();
                if(count($branch)>0){
                    $series = $series->merge($branch);
                }
            }         
        }
        $final_stock_value = 0;
        
        foreach ($series as $s1 => $s) {  
            if($req_series!=null && $req_series!=''){
                if($s->series != $req_series){
                    continue;
                }
            }
            $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
                                    ->join('units', 'manage_items.u_name', '=', 'units.id')
                                    ->select('item_id','in_weight as average_weight','txn_date as stock_date','total_price as amount','manage_items.name as item_name',
                         'units.name as unit_name')
                                    ->where('item_ledger.company_id',Session::get('user_company_id'))
                                    ->where('source','-1')
                                    ->where('series_no',$s->series)
                                    ->where('delete_status','0')
                                    ->groupBy('item_id')
                                    ->get();
                
            $sub = DB::table('item_averages')
                                    ->select(DB::raw('MAX(id) as latest_id'))
                                    ->where('stock_date', '<=', $date)
                                    ->where('series_no',$s->series)
                                    ->where('company_id', Session::get('user_company_id'))
                                    ->groupBy('item_id')
                                    ->pluck('latest_id'); 
            $stock_id = ItemAverage::whereIn('item_averages.id', $sub)
                                    ->where('series_no',$s->series)
                                    ->select('item_id')
                                    ->orderBy('stock_date', 'desc')
                                    ->pluck('item_id'); 
            $stock = ItemAverage::whereIn('item_averages.id', $sub)
                                    ->where('series_no',$s->series)
                                    ->select(
                                        'item_averages.item_id',
                                        'item_averages.average_weight',
                                        'item_averages.amount',
                                        'item_averages.stock_date',
                                        'manage_items.name as item_name',
                                        'units.name as unit_name'
                                    )
                                    ->orderBy('stock_date', 'desc')
                                    ->sum('amount');
            $final_stock_value = $final_stock_value + $stock;
            foreach ($item_ledger as $key => $value) {
                if(count($stock_id)==0){
                    $stock += $value['amount']; 
                   
                    $final_stock_value = $final_stock_value + $value['amount'];                  
                    continue;
                }
                $exists = 0;
                $exists = $stock_id->contains(function ($row)use ($value) {
                    if ($row==$value['item_id']) {
                        return 1;
                    }              
                });
                if ($exists==0) {
                    $stock += $value['amount'];
                    $final_stock_value = $final_stock_value + $value['amount'];
                }                
            }
        }  
        
        return $final_stock_value;
         
    }
    public static function RewriteItemAverageByItem($date,$item,$series=null)
    {      
        $max_date = ItemAverage::where('item_id',$item)->where('series_no',$series)->max('stock_date');
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($max_date);
        if($endDate < $startDate){
            $endDate = $startDate;
        }
        //die('RewriteItemAverageByItem'.$startDate."-".$endDate);
        // Loop through the date range
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            ItemAverage::where('item_id',$item)
                    ->where('series_no',$series)
                    ->where('stock_date',$date->toDateString())
                    ->delete();         
            $average_detail = ItemAverageDetail::where('item_id',$item)
                                                ->where('series_no',$series)
                                                ->where('entry_date',$date->toDateString())
                                                ->get();
            if(count($average_detail)>0){
                $purchase_weight = $average_detail->sum('purchase_weight');
                $purchase_amount = $average_detail->sum('purchase_total_amount');
                $sale_weight = $average_detail->sum('sale_weight');
                $stock_transfer_weight = $average_detail->sum('stock_transfer_weight');
                $purchase_return_weight = $average_detail->sum('purchase_return_weight');
                $purchase_return_amount = $average_detail->sum('purchase_return_amount');
                $purchase_return_amount = $purchase_return_amount*2;
                $sale_return_weight = $average_detail->sum('sale_return_weight');
                $stock_transfer_in_weight = $average_detail->sum('stock_transfer_in_weight');
                $stock_transfer_in_amount = $average_detail->sum('stock_transfer_in_amount');
                if(!empty($stock_transfer_in_weight)){
                    $purchase_weight = $purchase_weight + $stock_transfer_in_weight;
                }
                if(!empty($stock_transfer_in_amount)){
                    $purchase_amount = $purchase_amount + $stock_transfer_in_amount;
                }
                $on_date_purchase_weight = $purchase_weight + $sale_return_weight;
                $average = ItemAverage::where('item_id',$item)
                        ->where('stock_date','<',$date->toDateString())
                        ->where('series_no',$series)
                        ->orderBy('stock_date','desc')
                        ->orderBy('id','desc')
                        ->first();
                if($average){
                    $purchase_weight = $purchase_weight - $purchase_return_weight + $average->average_weight;
                    $purchase_amount = $purchase_amount - $purchase_return_amount + $average->amount;
                }else{
                    $opening = ItemLedger::where('item_id',$item)
                                    ->where('series_no',$series)
                                    ->where('source','-1')
                                    ->first();
                    if($opening){
                        $purchase_weight = $purchase_weight - $purchase_return_weight + $opening->in_weight;
                        $purchase_amount = $purchase_amount - $purchase_return_amount + $opening->total_price;                        
                    }
                }        
                if($purchase_amount != 0 && $purchase_amount != "" && $purchase_weight != 0 && $purchase_weight != ""){
                    $average_price = $purchase_amount / $purchase_weight;
                    $average_price =  round($average_price,6);
                }else{
                    $average_price = 0;
                }               
                $stock_average_amount = ($purchase_weight - $sale_weight - $stock_transfer_weight + $sale_return_weight) * $average_price;
                $stock_average_amount =  round($stock_average_amount,2);
                $average = new ItemAverage;
                $average->item_id = $item;
                $average->series_no = $series;
                $average->sale_weight = $sale_weight + $stock_transfer_weight + $purchase_return_weight;
                $average->purchase_weight = $on_date_purchase_weight;
                $average->average_weight = $purchase_weight - $sale_weight - $stock_transfer_weight + $sale_return_weight;
                $average->price = $average_price;
                $average->company_id = Session::get('user_company_id');
                $average->amount = $stock_average_amount;
                $average->stock_date = $date->toDateString();
                $average->created_at = Carbon::now();
                $average->save();
            }        
        }         
    }
    public static function sendWhatsappMessage($request){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.interakt.ai/v1/public/message/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$request,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic X2l6dWY4eU1CcFQ0T2pDUEJkVlkzT3NpTXVzZWwtQ0JxYWdaN1FxREgwdzo=',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);        
    }
    public static function profitLoss($financial_year){
        $y = explode("-",$financial_year);
        $from_date = $y['0']."-04-01";
        $from_date = date('Y-m-d',strtotime($from_date));
        $to_date = $y['1']."-03-31";  
        $to_date = date('Y-m-d',strtotime($to_date));
        $profitloss = 0;
        $stock_in_hand = CommonHelper::ClosingStock($to_date);
        $stock_in_hand = round($stock_in_hand,2);    
        $previous_date = Carbon::parse($from_date)->subDay();        
        $opening_stock = CommonHelper::ClosingStock($previous_date);
        $opening_stock = round($opening_stock,2);
        //Purchase
        $tot_purchase_amt = DB::table('purchases')
                            ->join('purchase_descriptions','purchases.id','=','purchase_descriptions.purchase_id')
                            ->where(['purchases.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
                            ->whereBetween('date', [$from_date, $to_date])
                            ->get()
                            ->sum("amount");
        $purchase_sundry = DB::table('purchases')
                            ->join('purchase_sundries','purchases.id','=','purchase_sundries.purchase_id')
                            ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                            ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
                            ->whereBetween('date', [$from_date, $to_date])
                            ->select('bill_sundry_type','amount')
                            ->get();
        if(count($purchase_sundry)>0){
            foreach ($purchase_sundry as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                    $tot_purchase_amt = $tot_purchase_amt + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                    $tot_purchase_amt = $tot_purchase_amt - $value->amount;
                }
            }
        }
        //Sale
        $tot_sale_amt = DB::table('sales')
                            ->join('sale_descriptions','sales.id','=','sale_descriptions.sale_id')
                            ->where(['sales.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
                            ->whereRaw("STR_TO_DATE(sales.date,'%Y-%m-%d')>=STR_TO_DATE('".$from_date."','%Y-%m-%d')")
                            ->whereRaw("STR_TO_DATE(sales.date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                            ->get()
                            ->sum("amount");
        $sale_sundry = DB::table('sales')
                            ->join('sale_sundries','sales.id','=','sale_sundries.sale_id')
                            ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                            ->where(['sales.delete' => '0', 'sales.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
                            ->whereBetween('date', [$from_date, $to_date])
                            ->select('bill_sundry_type','amount')
                            ->get();
        if(count($sale_sundry)>0){
            foreach ($sale_sundry as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                    $tot_sale_amt = $tot_sale_amt + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                    $tot_sale_amt = $tot_sale_amt - $value->amount;
                }
            }
        }
        //Purchase Return
        $tot_purchase_return_amt = DB::table('purchase_returns')
                                        ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
                                        ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
                                        ->whereBetween('date', [$from_date, $to_date])
                                        ->get()
                                        ->sum("amount");
        $purchase_return_sundry = DB::table('purchase_returns')
                                        ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
                                        ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
                                        ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'PURCHASE'])
                                        ->whereBetween('date', [$from_date, $to_date])
                                        ->select('bill_sundry_type','amount')
                                        ->get();
        if(count($purchase_return_sundry)>0){
            foreach ($purchase_return_sundry as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                    $tot_purchase_return_amt = $tot_purchase_return_amt + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                    $tot_purchase_return_amt = $tot_purchase_return_amt - $value->amount;
                }
            }
        }
         //Sale Return With  PURCHASE
        $tot_sale_return_amt_purchase = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
        $sale_return_sundry_purchase = DB::table('sales_returns')
            ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
            ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
            ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'PURCHASE'])
            ->whereBetween('date', [$from_date, $to_date])
            ->select('bill_sundry_type','amount')
            ->get();
        if(count($sale_return_sundry_purchase)>0){
            foreach ($sale_return_sundry_purchase as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase - $value->amount;
                }
            }
        }
        //Sale Return
        $tot_sale_return_amt = DB::table('sales_returns')
                                    ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
                                    ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
                                    ->whereBetween('date', [$from_date, $to_date])
                                    ->get()
                                    ->sum("amount");
        $sale_return_sundry = DB::table('sales_returns')
                                    ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
                                    ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
                                    ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'SALE'])
                                    ->whereBetween('date', [$from_date, $to_date])
                                    ->select('bill_sundry_type','amount')
                                    ->get();
        if(count($sale_return_sundry)>0){
            foreach ($sale_return_sundry as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                    $tot_sale_return_amt = $tot_sale_return_amt + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                    $tot_sale_return_amt = $tot_sale_return_amt - $value->amount;
                }
            }
        }
        //Purchase Return With Sale
        $tot_purchase_return_amt_sale = DB::table('purchase_returns')
            ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
            ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
            ->whereBetween('date', [$from_date, $to_date])
            ->get()
            ->sum("amount");
        $purchase_return_sundry_sale = DB::table('purchase_returns')
            ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
            ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
            ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE','adjust_purchase_amt'=>'Yes'])
            ->whereBetween('date', [$from_date, $to_date])
            ->select('bill_sundry_type','amount')
            ->get();
        if(count($purchase_return_sundry_sale)>0){
            foreach ($purchase_return_sundry_sale as $key => $value) {
                if($value->bill_sundry_type=="additive"){
                $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale + $value->amount;
                }else if($value->bill_sundry_type=="subtractive"){
                $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale - $value->amount;
                }
            }
        }
        //Direct Expensess
        $direct_expenses_account_id = Accounts::where('under_group','12')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
        $account_group = AccountGroups::where('heading','12')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
        $direct_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
        $direct_expenses_account_id = $direct_expenses_account_id->merge($direct_expenses_account_id1);      
        $direct_expenses = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');

        $direct_expenses_credit = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
        //InDirect Expensess
        $indirect_expenses_account_id = Accounts::where('under_group','15')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
        $account_group = AccountGroups::where('heading','15')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
        $indirect_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
        $indirect_expenses_account_id = $indirect_expenses_account_id->merge($indirect_expenses_account_id1);   
        $indirect_expenses = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
        $indirect_expenses_credit = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
        //Direct Income
        $direct_income_account_id = Accounts::where('under_group','13')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
        $account_group = AccountGroups::where('heading','13')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
        $direct_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
        $direct_income_account_id = $direct_income_account_id->merge($direct_income_account_id1);  
        $direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
        $debit_direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
        //InDirect Income
        $indirect_income_account_id = Accounts::where('under_group','14')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
        $account_group = AccountGroups::where('heading','14')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
        $indirect_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
        $indirect_income_account_id = $indirect_income_account_id->merge($indirect_income_account_id1);  
        $indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
        $debit_indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
        $total_net_sale = $stock_in_hand + $tot_sale_amt - $tot_sale_return_amt+ $tot_purchase_return_amt_sale + $direct_income - $debit_direct_income + $indirect_income - $debit_indirect_income;
        $total_net_purchase = $opening_stock + $tot_purchase_amt - $tot_purchase_return_amt + $tot_sale_return_amt_purchase + $direct_expenses - $direct_expenses_credit + $indirect_expenses - $indirect_expenses_credit;
        $profitloss = $total_net_purchase - $total_net_sale;
        $profitloss = round($profitloss,2);
        return $profitloss;
    }
    public static function gstTokenOtpRequest($state_code,$gst_username,$gstin){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mastergst.com/authentication/otprequest?email=pram92500@gmail.com',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_username,
                'state_cd: '.$state_code,
                'ip_address: 162.215.254.201',
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
         
        if(isset($result->status_cd) && $result->status_cd=='1'){
            if(isset($result->header->txn) && !empty($result->header->txn)){
                $gstToken = new gstToken;
                $gstToken->txn = $result->header->txn;
                $gstToken->created_at = Carbon::now();
                $gstToken->status = 0;
                $gstToken->company_id = Session::get('user_company_id');
                $gstToken->company_gstin = $gstin;
                $gstToken->save();
                return 1;
            }else{
                return 0;
            }         
        }else{
            if(isset($result->error)){
                return 0;
            }
        }       
    }



    public static function getAllGroupIds($parentIds)
{
    $allGroups = DB::table('account_groups')->get();
    $groupMap = $allGroups->groupBy('heading');

    $result = collect($parentIds);
    $queue = $parentIds;

    while (!empty($queue)) {
        $current = array_shift($queue);
        if (isset($groupMap[$current])) {
            foreach ($groupMap[$current] as $child) {
                if (!$result->contains($child->id)) {
                    $result->push($child->id);
                    $queue[] = $child->id;
                }
            }
        }
    }

    return $result->toArray();
}

}