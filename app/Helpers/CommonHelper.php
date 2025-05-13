<?php
namespace App\Helpers;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\ItemLedger;
use Carbon\Carbon;
use DB;
use Session;
class CommonHelper
{
    public static function ClosingStock($date)
    {
        $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
                                    ->join('units', 'manage_items.u_name', '=', 'units.id')
                                    ->select('item_id','in_weight as average_weight','txn_date as stock_date','total_price as amount','manage_items.name as item_name',
                         'units.name as unit_name')
                                    ->where('item_ledger.company_id',Session::get('user_company_id'))
                                    ->where('source','-1')
                                    ->where('delete_status','0')
                                    ->groupBy('item_id')
                                    ->get();
        // $sub = DB::table('item_averages')
        //              ->select(DB::raw('MAX(id) as latest_id'))
        //              ->where('stock_date', '<=', $date)
        //              ->where('company_id',Session::get('user_company_id'))
        //              ->groupBy('item_id');
        $sub = DB::table('item_averages')
                     ->select(DB::raw('MAX(id) as latest_id'))
                     ->where('stock_date', '<=', $date)
                     ->where('company_id', Session::get('user_company_id'))
                     ->groupBy('item_id')
                     ->pluck('latest_id'); 
        $stock_id = ItemAverage::whereIn('item_averages.id', $sub)
                     ->select('item_id')
                     ->orderBy('stock_date', 'desc')
                     ->pluck('item_id');      
        $stock = ItemAverage::whereIn('item_averages.id', $sub)
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
          
        foreach ($item_ledger as $key => $value) {
            if(count($stock_id)==0){
                $stock += $value['amount'];
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
            }
        }     
        //print_r($stock);die;          
        return $stock;
         
    }
    public static function RewriteItemAverageByItem($date,$item)
    {
        
        $max_date = ItemAverage::where('item_id',$item)->max('stock_date');
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($max_date);
        if($endDate < $startDate){
            $endDate = $startDate;
        }
        //die('RewriteItemAverageByItem'.$startDate."-".$endDate);
        // Loop through the date range
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            ItemAverage::where('item_id',$item)
                    ->where('stock_date',$date->toDateString())
                    ->delete();          
            $average_detail = ItemAverageDetail::where('item_id',$item)
                                                ->where('entry_date',$date->toDateString())
                                                ->get();
            if(count($average_detail)>0){
                $purchase_weight = $average_detail->sum('purchase_weight');
                $purchase_amount = $average_detail->sum('purchase_total_amount');
                $sale_weight = $average_detail->sum('sale_weight');
                $purchase_return_weight = $average_detail->sum('purchase_return_weight');
                $purchase_return_amount = $average_detail->sum('purchase_return_amount');
                $purchase_return_amount = $purchase_return_amount*2;
                $sale_return_weight = $average_detail->sum('sale_return_weight');
                $on_date_purchase_weight = $purchase_weight + $sale_return_weight;
                $average = ItemAverage::where('item_id',$item)
                        ->where('stock_date','<',$date->toDateString())
                        ->orderBy('stock_date','desc')
                        ->orderBy('id','desc')
                        ->first();
                if($average){
                    $purchase_weight = $purchase_weight - $purchase_return_weight + $average->average_weight;
                    $purchase_amount = $purchase_amount - $purchase_return_amount + $average->amount;
                }else{
                    $opening = ItemLedger::where('item_id',$item)
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
                $stock_average_amount = ($purchase_weight - $sale_weight + $sale_return_weight) * $average_price;
                $stock_average_amount =  round($stock_average_amount,2);
                $average = new ItemAverage;
                $average->item_id = $item;
                $average->sale_weight = $sale_weight + $purchase_return_weight;
                $average->purchase_weight = $on_date_purchase_weight;
                $average->average_weight = $purchase_weight - $sale_weight  + $sale_return_weight;
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
}