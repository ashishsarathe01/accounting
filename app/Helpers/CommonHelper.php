<?php
namespace App\Helpers;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\ItemLedger;
use Carbon\Carbon;
use DB;
class CommonHelper
{
    public static function ClosingStock($date)
    {
        $sub = DB::table('item_averages')
                     ->select(DB::raw('MAX(id) as latest_id'))
                     ->where('stock_date', '<=', $date)
                     ->groupBy('item_id');
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
       
        return $stock;
         
    }
    public static function RewriteItemAverageByItem($date,$item)
    {
        $max_date = ItemAverage::where('item_id',$item)->max('stock_date');
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($max_date);
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
                $on_date_purchase_weight = $purchase_weight;
                $average = ItemAverage::where('item_id',$item)
                        ->where('stock_date','<',$date->toDateString())
                        ->orderBy('stock_date','desc')
                        ->orderBy('id','desc')
                        ->first();
                if($average){
                    $purchase_weight = $purchase_weight + $average->average_weight;
                    $purchase_amount = $purchase_amount + $average->amount;
                }else{
                    $opening = ItemLedger::where('item_id',$item)
                                    ->where('source','-1')
                                    ->first();
                    if($opening){
                        $purchase_weight = $purchase_weight + $opening->in_weight;
                        $purchase_amount = $purchase_amount + $opening->total_price;                        
                    }
                }                 
                $average_price = $purchase_amount / $purchase_weight;
                $average_price =  round($average_price,6);
                $stock_average_amount = ($purchase_weight-$sale_weight) * $average_price;
                $stock_average_amount =  round($stock_average_amount,2);

                $average = new ItemAverage;
                $average->item_id = $item;
                $average->sale_weight = $sale_weight;
                $average->purchase_weight = $on_date_purchase_weight;
                $average->average_weight = $purchase_weight - $sale_weight;
                $average->price = $average_price;
                $average->amount = $stock_average_amount;
                $average->stock_date = $date->toDateString();
                $average->created_at = Carbon::now();
                $average->save();
            }          
        }         
    }
}