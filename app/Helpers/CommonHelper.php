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
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\Models\GstApiCredentials;
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
          $endDate = Carbon::today();
        // $endDate = Carbon::parse($max_date);
        // if($endDate < $startDate){
        //     $endDate = $startDate;
        // }
        //die('RewriteItemAverageByItem'.$startDate."-".$endDate);
        // Loop through the date range
        $purchaseWeightLog = [];
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
                $purchase_return_weight = $average_detail->sum(function ($item) {
                    return (float) $item->purchase_return_weight;
                });
                $purchase_return_amount = $average_detail->sum('purchase_return_amount');
                $purchase_return_amount = $purchase_return_amount*2;
                $sale_return_weight = $average_detail->pluck('sale_return_weight')
                                     ->filter(fn($val) => is_numeric($val))
                                     ->sum();
                if($sale_return_weight==''){
                    $sale_return_weight = 0;
                }
                $stock_transfer_in_weight = $average_detail->sum('stock_transfer_in_weight');
                $stock_transfer_in_amount = $average_detail->sum('stock_transfer_in_amount');
                
                $stock_journal_out_weight = $average_detail->sum(function ($item) {
                    return (float) $item->stock_journal_out_weight;
                });
                $stock_journal_in_weight = $average_detail->sum(function ($item) {
                    return (float) $item->stock_journal_in_weight;
                });
                $stock_journal_in_amount = $average_detail->sum(function ($item) {
                    return (float) $item->stock_journal_in_amount;
                });
                // $stock_journal_out_weight = $average_detail->sum('stock_journal_out_weight');
                // $stock_journal_in_weight = $average_detail->sum('stock_journal_in_weight');
                // $stock_journal_in_amount = $average_detail->sum('stock_journal_in_amount');
                 if(!empty($stock_journal_in_weight)){
                    $purchase_weight = $purchase_weight + $stock_journal_in_weight;
                }
                if(!empty($stock_journal_in_amount)){
                    $purchase_amount = $purchase_amount + $stock_journal_in_amount;
                }
                //Production
                $production_out_weight = $average_detail->sum('production_out_weight');
                $production_in_weight = $average_detail->sum('production_in_weight');
                $production_in_amount = $average_detail->sum('production_in_amount');
                 if(!empty($production_in_weight)){
                    $purchase_weight = $purchase_weight + $production_in_weight;
                }
                if(!empty($production_in_amount)){
                    $purchase_amount = $purchase_amount + $production_in_amount;
                }
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
                    
                }
               
                $purchase_weight1=0;
                if($average){
                   if($average->price == 0){ 
                     $avgWeight = max(0, $average->average_weight ?? 0);
                     $purchase_weight1 = $purchase_weight - $purchase_return_weight + abs($avgWeight);
                                        }else{
                                        $purchase_weight1 = $purchase_weight - $purchase_return_weight + abs($average->average_weight);
                                        }
                    $purchase_weight = $purchase_weight - $purchase_return_weight + $average->average_weight;
                     
                    $purchase_amount = $purchase_amount - $purchase_return_amount + abs($average->amount);
                }else{
                    $opening = ItemLedger::where('item_id',$item)
                                    ->where('series_no',$series)
                                    ->where('source','-1')
                                    ->first();
                    if($opening){
                        $purchase_weight1 = $purchase_weight - $purchase_return_weight + $opening->in_weight;
                        $purchase_weight = $purchase_weight - $purchase_return_weight + $opening->in_weight;
                        $purchase_amount = $purchase_amount - $purchase_return_amount + $opening->total_price;                        
                    }else{
                        $purchase_weight1 = $purchase_weight - $purchase_return_weight;
                        $purchase_weight = $purchase_weight - $purchase_return_weight;
                        $purchase_amount = $purchase_amount - $purchase_return_amount; 
                    }
                }  
                      
                if($purchase_amount != 0 && $purchase_amount != "" && $purchase_weight != 0 && $purchase_weight != "" && $purchase_weight1 != 0){
                    $average_price = round($purchase_amount / $purchase_weight1,6);
                    $average_price =  abs($average_price);
                }else{
                    $average_price = 0;
                }    

                $stock_average_amount = ($purchase_weight - $sale_weight - $stock_transfer_weight - $stock_journal_out_weight - $production_out_weight + $sale_return_weight) * $average_price;
                $stock_average_amount =  round($stock_average_amount,2);
                $average = new ItemAverage;
                $average->item_id = $item;
                $average->series_no = $series;
                $average->sale_weight = $sale_weight + $stock_transfer_weight + $stock_journal_out_weight + $production_out_weight + $purchase_return_weight;
                $average->purchase_weight = $on_date_purchase_weight;
                $average->average_weight = $purchase_weight - $sale_weight - $stock_transfer_weight  - $production_out_weight - $stock_journal_out_weight + $sale_return_weight;
                $average->price = $average_price;
                $average->company_id = Session::get('user_company_id');
                $average->amount = $stock_average_amount;
                $average->stock_date = $date->toDateString();
                $average->created_at = Carbon::now();
                $average->save();
            }  
                
        }     
      
    
    }
    public static function RewriteItemAverageByItemApi($date,$item,$series=null,$company_id)
    {
        
        $max_date = ItemAverage::where('item_id',$item)->where('series_no',$series)->max('stock_date');
        $startDate = Carbon::parse($date);
         $endDate = Carbon::today();
        // $endDate = Carbon::parse($max_date);
        // if($endDate < $startDate){
        //     $endDate = $startDate;
        // }
        
        //die('RewriteItemAverageByItem'.$startDate."-".$endDate);
        // Loop through the date range
        $purchaseWeightLog = [];
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
                $purchase_return_weight = $average_detail->sum(function ($item) {
                    return (float) $item->purchase_return_weight;
                });
                $purchase_return_amount = $average_detail->sum('purchase_return_amount');
                $purchase_return_amount = $purchase_return_amount*2;
                $sale_return_weight = $average_detail->pluck('sale_return_weight')
                                     ->filter(fn($val) => is_numeric($val))
                                     ->sum();
                if($sale_return_weight==''){
                    $sale_return_weight = 0;
                }
                $stock_transfer_in_weight = $average_detail->sum('stock_transfer_in_weight');
                $stock_transfer_in_amount = $average_detail->sum('stock_transfer_in_amount');

                $stock_journal_out_weight = $average_detail->sum('stock_journal_out_weight');
                $stock_journal_in_weight = $average_detail->sum('stock_journal_in_weight');
                $stock_journal_in_amount = $average_detail->sum('stock_journal_in_amount');
                 if(!empty($stock_journal_in_weight)){
                    $purchase_weight = $purchase_weight + $stock_journal_in_weight;
                }
                if(!empty($stock_journal_in_amount)){
                    $purchase_amount = $purchase_amount + $stock_journal_in_amount;
                }
                //Production
                $production_out_weight = $average_detail->sum('production_out_weight');
                $production_in_weight = $average_detail->sum('production_in_weight');
                $production_in_amount = $average_detail->sum('production_in_amount');
                 if(!empty($production_in_weight)){
                    $purchase_weight = $purchase_weight + $production_in_weight;
                }
                if(!empty($production_in_amount)){
                    $purchase_amount = $purchase_amount + $production_in_amount;
                }
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
                    
                }
               
                $purchase_weight1=0;
                if($average){
                   if($average->price == 0){ 
                     $avgWeight = max(0, $average->average_weight ?? 0);
                     $purchase_weight1 = $purchase_weight - $purchase_return_weight + abs($avgWeight);
                                        }else{
                                        $purchase_weight1 = $purchase_weight - $purchase_return_weight + abs($average->average_weight);
                                        }
                    $purchase_weight = $purchase_weight - $purchase_return_weight + $average->average_weight;
                     
                    $purchase_amount = $purchase_amount - $purchase_return_amount + abs($average->amount);
                }else{
                    $opening = ItemLedger::where('item_id',$item)
                                    ->where('series_no',$series)
                                    ->where('source','-1')
                                    ->first();
                    if($opening){
                        $purchase_weight1 = $purchase_weight - $purchase_return_weight + $opening->in_weight;
                        $purchase_weight = $purchase_weight - $purchase_return_weight + $opening->in_weight;
                        $purchase_amount = $purchase_amount - $purchase_return_amount + $opening->total_price;                        
                    }else{
                        $purchase_weight1 = $purchase_weight - $purchase_return_weight;
                        $purchase_weight = $purchase_weight - $purchase_return_weight;
                        $purchase_amount = $purchase_amount - $purchase_return_amount; 
                    }
                }  
                    
                if($purchase_amount != 0 && $purchase_amount != "" && $purchase_weight != 0 && $purchase_weight != "" && $purchase_weight1 != 0){
                    $average_price = round($purchase_amount / $purchase_weight1,6);
                    $average_price =  abs($average_price);
                }else{
                    $average_price = 0;
                }    

                $stock_average_amount = ($purchase_weight - $sale_weight - $stock_transfer_weight - $stock_journal_out_weight - $production_out_weight + $sale_return_weight) * $average_price;
                $stock_average_amount =  round($stock_average_amount,2);
                $average = new ItemAverage;
                $average->item_id = $item;
                $average->series_no = $series;
                $average->sale_weight = $sale_weight + $stock_transfer_weight + $stock_journal_out_weight + $production_out_weight + $purchase_return_weight;
                $average->purchase_weight = $on_date_purchase_weight;
                $average->average_weight = $purchase_weight - $sale_weight - $stock_transfer_weight - $production_out_weight - $stock_journal_out_weight + $sale_return_weight;
                $average->price = $average_price;
                $average->company_id = $company_id;
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
    public static function profitLoss($financial_year, $filter_from_date = null, $filter_to_date = null, $detailed = false)
    {
        $y = explode("-", $financial_year);
        $from_date = date('Y-m-d', strtotime($y[0] . "-04-01"));
        $to_date   = date('Y-m-d', strtotime($y[1] . "-03-31"));

        if (!empty($filter_from_date)) {
            $from_date = date('Y-m-d', strtotime($filter_from_date));
        }
        if (!empty($filter_to_date)) {
            $to_date = date('Y-m-d', strtotime($filter_to_date));
        }

        $company_id = Session::get('user_company_id');
        $closing_stock = round(CommonHelper::ClosingStock($to_date), 2);

        $baseQuery = DB::table('purchases')
            ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to_date])
            ->whereDate('stock_entry_date', '>', $to_date)
            ->where('company_id', $company_id)
            ->where('status', '1')
            ->where('delete', '0');

        $stock_in_transit_value = round((clone $baseQuery)
            ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
            ->value('total') ?? 0, 2);

        $total_closing_stock = $closing_stock + $stock_in_transit_value;

        $previous_date = Carbon::parse($from_date)->subDay();
        $opening_stock1 = round(CommonHelper::ClosingStock($previous_date), 2);

        $baseQuery1 = DB::table('purchases')
            ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') < ?", [$from_date])
            ->whereDate('stock_entry_date', '>=', $from_date)
            ->where('company_id', $company_id)
            ->where('status', '1')
            ->where('delete', '0');

        $stock_in_transit_opening_value = round((clone $baseQuery1)
            ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
            ->value('total') ?? 0, 2);

        $total_opening_stock = $opening_stock1 + $stock_in_transit_opening_value;

        $purchaseGroupIds = array_unique(array_merge(
            [23],
            CommonHelper::getAllChildGroupIds(23, $company_id)
        ));

        $purchaseAccountIds = Accounts::whereIn('under_group', $purchaseGroupIds)
            ->whereIn('company_id', [$company_id, 0])
            ->where('delete', '0')
            ->pluck('id');

        $purchase_debit = AccountLedger::whereIn('account_id', $purchaseAccountIds)
            ->where('delete_status', '0')
            ->where('status', '1')
            ->where('financial_year', $financial_year)
            ->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->sum('debit');

        $purchase_credit = AccountLedger::whereIn('account_id', $purchaseAccountIds)
            ->where('delete_status', '0')
            ->where('status', '1')
            ->where('financial_year', $financial_year)
            ->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->sum('credit');

        $purchase_ledger_amount = $purchase_debit - $purchase_credit;

        $saleGroupIds = array_unique(array_merge(
            [24],
            CommonHelper::getAllChildGroupIds(24, $company_id)
        ));

        $saleAccountIds = Accounts::whereIn('under_group', $saleGroupIds)
            ->whereIn('company_id', [$company_id, 0])
            ->where('delete', '0')
            ->pluck('id');

        $sale_debit = AccountLedger::whereIn('account_id', $saleAccountIds)
            ->where('delete_status', '0')
            ->where('status', '1')
            ->where('financial_year', $financial_year)
            ->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->sum('debit');

        $sale_credit = AccountLedger::whereIn('account_id', $saleAccountIds)
            ->where('delete_status', '0')
            ->where('status', '1')
            ->where('financial_year', $financial_year)
            ->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->sum('credit');

        $sale_ledger_amount = $sale_credit - $sale_debit;

        $direct_expenses_account_id = Accounts::where('under_group', '12')
            ->whereIn('company_id', [$company_id, 0])->pluck('id');
        $account_group = AccountGroups::where('heading', '12')
            ->whereIn('company_id', [$company_id, 0])
            ->where('heading_type', 'group')->pluck('id');
        $direct_expenses_account_id = $direct_expenses_account_id->merge(
            Accounts::whereIn('under_group', $account_group)
                ->whereIn('company_id', [$company_id, 0])->pluck('id')
        );

        $direct_expenses = AccountLedger::whereIn('account_id', $direct_expenses_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('debit');

        $direct_expenses_credit = AccountLedger::whereIn('account_id', $direct_expenses_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('credit');

        $indirect_expenses_account_id = Accounts::where('under_group', '15')
            ->whereIn('company_id', [$company_id, 0])->pluck('id');
        $account_group = AccountGroups::where('heading', '15')
            ->whereIn('company_id', [$company_id, 0])
            ->where('heading_type', 'group')->pluck('id');
        $indirect_expenses_account_id = $indirect_expenses_account_id->merge(
            Accounts::whereIn('under_group', $account_group)
                ->whereIn('company_id', [$company_id, 0])->pluck('id')
        );

        $indirect_expenses = AccountLedger::whereIn('account_id', $indirect_expenses_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('debit');

        $indirect_expenses_credit = AccountLedger::whereIn('account_id', $indirect_expenses_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('credit');

        $direct_income_account_id = Accounts::where('under_group', '13')
            ->whereIn('company_id', [$company_id, 0])->pluck('id');
        $account_group = AccountGroups::where('heading', '13')
            ->whereIn('company_id', [$company_id, 0])
            ->where('heading_type', 'group')->pluck('id');
        $direct_income_account_id = $direct_income_account_id->merge(
            Accounts::whereIn('under_group', $account_group)
                ->whereIn('company_id', [$company_id, 0])->pluck('id')
        );

        $direct_income = AccountLedger::whereIn('account_id', $direct_income_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('credit');

        $debit_direct_income = AccountLedger::whereIn('account_id', $direct_income_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('debit');

        $indirect_income_account_id = Accounts::where('under_group', '14')
            ->whereIn('company_id', [$company_id, 0])->pluck('id');
        $account_group = AccountGroups::where('heading', '14')
            ->whereIn('company_id', [$company_id, 0])
            ->where('heading_type', 'group')->pluck('id');
        $indirect_income_account_id = $indirect_income_account_id->merge(
            Accounts::whereIn('under_group', $account_group)
                ->whereIn('company_id', [$company_id, 0])->pluck('id')
        );

        $indirect_income = AccountLedger::whereIn('account_id', $indirect_income_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('credit');

        $debit_indirect_income = AccountLedger::whereIn('account_id', $indirect_income_account_id)
            ->where('delete_status', '0')->whereIn('company_id', [$company_id, 0])
            ->whereBetween('txn_date', [$from_date, $to_date])
            ->where('status', '1')->where('financial_year', $financial_year)->sum('debit');

        $total_net_purchase = $total_opening_stock
            + $purchase_ledger_amount
            + $direct_expenses - $direct_expenses_credit;

        $total_net_sale = $total_closing_stock
            + $sale_ledger_amount
            + $direct_income - $debit_direct_income;

        $balance = $total_net_purchase - $total_net_sale;

        $gross_profit = 0;
        $gross_loss   = 0;
        if ($balance < 0) {
            $gross_profit = abs($balance);
        } else {
            $gross_loss = $balance;
        }

        $trading_total = max($total_net_purchase, $total_net_sale);

        $nett_expenses_total = ($indirect_expenses - $indirect_expenses_credit) + $gross_loss;
        $nett_income_total   = abs(($indirect_income - $debit_indirect_income) + $gross_profit);
        $nett_diff            = $nett_expenses_total - $nett_income_total;

        $nett_profit = 0;
        $nett_loss   = 0;
        if ($nett_diff > 0) {
            $nett_loss = $nett_diff;
        } elseif ($nett_diff < 0) {
            $nett_profit = abs($nett_diff);
        }

        $pnl_total = max($nett_expenses_total, $nett_income_total);

        $profitloss = $nett_profit > 0 ? -$nett_profit : $nett_loss;
        $profitloss = round($profitloss, 2);

        if (!$detailed) {
            return $profitloss;
        }

        return [
            'opening_stock'            => $opening_stock1,
            'stock_in_transit_opening' => $stock_in_transit_opening_value,
            'total_opening_stock'      => $total_opening_stock,
            'closing_stock'            => $closing_stock,
            'stock_in_transit'         => $stock_in_transit_value,
            'total_closing_stock'      => $total_closing_stock,
            'purchase'                 => $purchase_ledger_amount,
            'sale'                     => $sale_ledger_amount,
            'direct_expenses'          => $direct_expenses,
            'direct_expenses_credit'   => $direct_expenses_credit,
            'direct_income'            => $direct_income,
            'debit_direct_income'      => $debit_direct_income,
            'indirect_expenses'        => $indirect_expenses,
            'indirect_expenses_credit' => $indirect_expenses_credit,
            'indirect_income'          => $indirect_income,
            'debit_indirect_income'    => $debit_indirect_income,
            'gross_profit'             => round($gross_profit, 2),
            'gross_loss'               => round($gross_loss, 2),
            'trading_total'            => round($trading_total, 2),
            'net_profit'               => round($nett_profit, 2),
            'net_loss'                 => round($nett_loss, 2),
            'pnl_total'                => round($pnl_total, 2),
            'profitloss'               => $profitloss,
        ];
    }
    public static function gstTokenOtpRequest($state_code,$gst_username,$gstin){
        $credentials = json_decode(self::gstApiCredentials('GST'));
        if(!$credentials){
            return 0;
        }
        if($credentials->status != 1){
            return 0;
        }
        $base_url = $credentials->base_url;
        $email_id = $credentials->email_id;
        $client_id = $credentials->client_id;
        $client_secret = $credentials->client_secret;
        $ip_address = $credentials->ip_address;
        $gst_request = array(
            "gstin" => $gstin,
            "userName" => $gst_username,
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $base_url.'/authentication/otprequest?email='.urlencode($email_id),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($gst_request),
            CURLOPT_HTTPHEADER => array(
                'accept: */*', 
                'Content-Type: application/json',
                'env: production',
                'client_id: ' . $client_id,
                'client_secret: ' . $client_secret,
            ),
            
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        // echo "<pre>";
        // print_r($result);
        if(isset($result->status_cd) && $result->status_cd=='1'){
            return 1;
            // if(isset($result->header->txn) && !empty($result->header->txn)){
            //     $gstToken = new gstToken;
            //     $gstToken->txn = $result->header->txn;
            //     $gstToken->created_at = Carbon::now();
            //     $gstToken->status = 0;
            //     $gstToken->company_id = Session::get('user_company_id');
            //     $gstToken->company_gstin = $gstin;
            //     $gstToken->save();
                
            // }else{
            //     return 0;
            // }         
        }else{
            echo "<pre>";
            echo "....";
            echo $base_url.'/authentication/otprequest?email='.urlencode($email_id);
        print_r($result);die;
                return 0;
            
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
    public static function getAllChildGroupIds($group_id, $company_id, &$visited = [])
    {
        // STOP if already visited (prevents infinite loop)
        if (in_array($group_id, $visited)) {
            return [];
        }

        // mark this group as visited
        $visited[] = $group_id;

        $child_ids = AccountGroups::where('heading', $group_id)
            ->where('delete', '0')
            ->where('heading_type', 'group')
            ->whereIn('company_id', [$company_id, 0])
            ->pluck('id')
            ->toArray();

        $all_ids = [];

        foreach ($child_ids as $child_id) {
            $all_ids[] = $child_id;

            $all_ids = array_merge(
                $all_ids,
                self::getAllChildGroupIds($child_id, $company_id, $visited)
            );
        }

        return $all_ids;
    }
    public static function RewriteAllItemAverage()
    {
        $companyId = Session::get('user_company_id');

        // 1️⃣ Get company book start date
        $company = Companies::find($companyId);
        if (!$company || empty($company->books_start_from)) {
            return false;
        }

        $startDate = Carbon::parse($company->books_start_from)->toDateString();

        // 2️⃣ Get all SERIES (same logic as ClosingStock)
        if ($company->gst_config_type == "single_gst") {

            $series = DB::table('gst_settings')
                ->where([
                    'company_id' => $companyId,
                    'gst_type' => 'single_gst'
                ])->pluck('series')->toArray();

            $branchSeries = GstBranch::where([
                    'delete' => '0',
                    'company_id' => $companyId
                ])
                ->pluck('branch_series')
                ->toArray();

            $series = array_merge($series, $branchSeries);

        } else {

            $series = DB::table('gst_settings_multiple')
                ->where([
                    'company_id' => $companyId,
                    'gst_type' => 'multiple_gst'
                ])
                ->pluck('series')
                ->toArray();

            $branchSeries = GstBranch::where([
                    'delete' => '0',
                    'company_id' => $companyId
                ])
                ->pluck('branch_series')
                ->toArray();

            $series = array_merge($series, $branchSeries);
        }

        $series = array_unique(array_filter($series));

        // 3️⃣ Get all items which have ANY stock movement
        $items = ItemAverageDetail::where('company_id', $companyId)
            ->distinct()
            ->pluck('item_id');

        // 4️⃣ Recalculate item-wise, series-wise
        foreach ($items as $itemId) {
            foreach ($series as $seriesNo) {

                self::RewriteItemAverageByItem(
                    $startDate,
                    $itemId,
                    $seriesNo
                );

            }
        }

        return true;
    }
    public static function getAllChildGroupIdsOptimizeCode($group_id, $company_id)
    {
        $rows = AccountGroups::where('delete', '0')
            ->whereIn('heading_type', ['group', 'head'])
            ->whereIn('company_id', [$company_id, 0])
            ->get(['id', 'heading']);

        $map = [];

        foreach ($rows as $row) {
            $map[$row->heading][] = $row->id;
        }

        $result = [];
        $visited = [];

        $walk = function ($parent) use (&$walk, &$map, &$result, &$visited) {
            if (isset($visited[$parent])) {
                return;
            }

            $visited[$parent] = true;

            foreach ($map[$parent] ?? [] as $childId) {
                $result[] = $childId;
                $walk($childId);
            }
        };

        $walk($group_id);

        return array_unique($result);
    }
    public static function getFinancialYear($date)
    {
        $year = date('Y', strtotime($date));
        $month = date('n', strtotime($date));
    
        if ($month >= 4) {
            return date('y', strtotime($date)) . '-' . substr($year + 1, -2);
        } else {
            return substr($year - 1, -2) . '-' . date('y', strtotime($date));
        }
    }
    public static function gstApiCredentials($type)
    {
        $credenatial = GstApiCredentials::where('type', $type)->first();
        if ($credenatial) {
            try {
                $client_id = Crypt::decryptString($credenatial->client_id);
            } catch (DecryptException $e) {
                // handle error
                $client_id = null;
            }
            try {
                $client_secret = Crypt::decryptString($credenatial->client_secret);
            } catch (DecryptException $e) {
                // handle error
                $client_secret = null;
            }
            if($client_id == null || $client_secret == null){
                return json_encode(array("status" => false));
            }
            return json_encode(array(
                "status" => true,
                "base_url" => $credenatial->base_url,
                "email_id" => $credenatial->email_id,
                "client_id" => $client_id,
                "client_secret" => $client_secret,
                "ip_address" => $credenatial->ip_address
            ));
        } else {
            return json_encode(array("status" => false));
        }
    }
    public static function updateDailyReelStock(
        $company_id,
        $item_id,
        $date,
        $in_reels = 0,
        $in_weight = 0,
        $out_reels = 0,
        $out_weight = 0
    ) {

        $isParameterized = DB::table('manage_items')
            ->join('item_groups', 'manage_items.g_name', '=', 'item_groups.id')
            ->where('manage_items.id', $item_id)
            ->where('manage_items.company_id', $company_id)
            ->where('item_groups.parameterized_stock_status', 1)
            ->exists();

        if (!$isParameterized) {
            return;
        }

        $row = DB::table('item_daily_reel_stock')
            ->where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->where('stock_date', $date)
            ->first();

        if ($row) {
            $new_in_reels   = $row->in_reels + $in_reels;
            $new_in_weight  = $row->in_weight + $in_weight;
            $new_out_reels  = $row->out_reels + $out_reels;
            $new_out_weight = $row->out_weight + $out_weight;
            $new_in_reels   = max(0, $new_in_reels);
            $new_in_weight  = max(0, $new_in_weight);
            $new_out_reels  = max(0, $new_out_reels);
            $new_out_weight = max(0, $new_out_weight);
            if (
                $new_in_reels == 0 &&
                $new_in_weight == 0 &&
                $new_out_reels == 0 &&
                $new_out_weight == 0
            ) {
                DB::table('item_daily_reel_stock')
                    ->where('id', $row->id)
                    ->delete();
            } else {
                DB::table('item_daily_reel_stock')
                    ->where('id', $row->id)
                    ->update([
                        'in_reels'   => $new_in_reels,
                        'in_weight'  => $new_in_weight,
                        'out_reels'  => $new_out_reels,
                        'out_weight' => $new_out_weight,
                        'updated_at' => now(),
                    ]);
            }
        } else {
            if (
                $in_reels != 0 ||
                $in_weight != 0 ||
                $out_reels != 0 ||
                $out_weight != 0
            ) {
                DB::table('item_daily_reel_stock')
                    ->insert([
                        'company_id' => $company_id,
                        'item_id' => $item_id,
                        'stock_date' => $date,

                        'in_reels'   => max(0, $in_reels),
                        'in_weight'  => max(0, $in_weight),

                        'out_reels'  => max(0, $out_reels),
                        'out_weight' => max(0, $out_weight),

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}