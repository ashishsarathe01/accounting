<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductionItem;
use App\Models\DeckleProcess;
use App\Models\ItemGroups;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleOrderSetting;
use App\Models\DeckleProcessQuality;
use App\Models\ManageItems;
use App\Models\DeckleMachineStopLog;
use App\Models\DeckleItem;
use App\Models\ItemSizeStock;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemLedger;
use App\Models\ItemAverageDetail;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\Consumption_rate_item_wise;
use App\Models\ConsumptionItems;
use Carbon\CarbonPeriod;

class StockController extends Controller
{
    public function manageStock(Request $request)
{
    $company_id = $request->company_id;

    $date = ($request->date ? Carbon::parse($request->date): Carbon::today())
        ->addDay()
        ->format('Y-m-d');

    $date1 = $request->date
        ? Carbon::parse($request->date)
        : Carbon::today();

    $cutoff = $date1->endOfDay();

    $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
        ->select('manage_items.id','name','bf','gsm','speed')
        ->where('production_items.company_id',$company_id)
        ->where('production_items.status','1')
        ->orderBy('name')
        ->get();

    $stockData = [];

    foreach($items as $item)
    {

        $openingReels = ItemSizeStock::where('company_id',$company_id)
            ->where('item_id',$item->id)
            ->where('deckle_id',0)
            ->get();

        $CreatedReels = ItemSizeStock::join('deckle_processes','item_size_stocks.deckle_id','=','deckle_processes.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNotNull('item_size_stocks.deckle_id')
            ->where('item_size_stocks.deckle_id','>',0)
            ->where('deckle_processes.end_time_stamp','<=',$cutoff)
            ->select('item_size_stocks.*')
            ->get();

        $PurchasedReels = ItemSizeStock::join('purchases','item_size_stocks.purchase_id','purchases.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNull('item_size_stocks.deckle_id')
            ->where('purchases.date','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $GeneratedReels = ItemSizeStock::join('stock_journal','item_size_stocks.sj_generated_id','stock_journal.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNull('deckle_id')
            ->whereNotNull('sj_generated_id')
            ->where('stock_journal.jdate','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $SaleReturnReels = ItemSizeStock::join('sales_returns','item_size_stocks.sale_return_id','sales_returns.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNull('deckle_id')
            ->whereNotNull('sale_return_id')
            ->where('sales_returns.date','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $PurchaseReturnReels = ItemSizeStock::join('purchase_returns','item_size_stocks.purchase_return_id','purchase_returns.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNull('deckle_id')
            ->whereNotNull('purchase_return_id')
            ->where('purchase_returns.date','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $SoldReels = ItemSizeStock::join('sales','item_size_stocks.sale_id','sales.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNotNull('sale_id')
            ->where('sales.date','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $ConsumedReels = ItemSizeStock::join('stock_journal','item_size_stocks.sj_consumption_id','stock_journal.id')
            ->where('item_size_stocks.company_id',$company_id)
            ->where('item_size_stocks.item_id',$item->id)
            ->whereNotNull('sj_consumption_id')
            ->where('stock_journal.jdate','<',$date)
            ->select('item_size_stocks.*')
            ->get();

        $OpeningPlus = $openingReels
            ->merge($CreatedReels)
            ->merge($GeneratedReels)
            ->merge($SaleReturnReels)
            ->merge($PurchasedReels);

        $OpeningMinus = $SoldReels
            ->merge($ConsumedReels)
            ->merge($PurchaseReturnReels);

        $ActualOpeningReels = $OpeningPlus->diff($OpeningMinus);

        $stockData[] = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'reel_count' => $ActualOpeningReels->count(),
            'weight' => $ActualOpeningReels->sum('weight')
        ];
    }

    return response()->json([
        'code' => 200,
        'date' => $request->date,
        'data' => $stockData
    ]);
}


public function itemWiseReelStock(Request $request)
{
    $company_id = $request->company_id;
    $item_id = $request->item_id;

    if(!$item_id){
        return response()->json([
            'status'=>false,
            'message'=>"Item ID missing"
        ]);
    }

    $date = ($request->date ? Carbon::parse($request->date): Carbon::today())
        ->addDay()
        ->format('Y-m-d');

    $date1 = $request->date
        ? Carbon::parse($request->date)
        : Carbon::today();

    $cutoff = $date1->endOfDay();

    $item_name = ManageItems::where('company_id',$company_id)
        ->where('id',$item_id)
        ->value('name');

    // SAME REEL FETCH LOGIC AS YOUR FUNCTION
    // (shortened here but same queries)

    $openingReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->where('item_size_stocks.deckle_id', 0)
        ->get();

    // $CreatedReels = ItemSizeStock::where('company_id', $company_id)
    //     ->where('item_id', $item_id)
    //     ->whereNotNull('deckle_id')
    //     ->where('deckle_id', '>', 0)
    //     ->where('created_at', '<=', $cutoff)
    //     ->get();
        $CreatedReels = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
    ->where('item_size_stocks.company_id', $company_id)
    ->where('item_size_stocks.item_id', $item_id)
    ->whereNotNull('item_size_stocks.deckle_id')
    ->where('item_size_stocks.deckle_id', '>', 0)
    ->where('deckle_processes.end_time_stamp', '<=', $cutoff) // ✅ Correct Logic
    ->select('item_size_stocks.*')
    ->get();

    $PurchasedReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
        ->join('purchases', 'item_size_stocks.purchase_id', 'purchases.id')
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNull('item_size_stocks.deckle_id')
        ->where('purchases.date', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $GeneratedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
        ->where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNull('deckle_id')
        ->whereNotNull('sj_generated_id')
        ->where('stock_journal.jdate', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $SaleReturnReels = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
        ->where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNull('deckle_id')
        ->whereNotNull('sale_return_id')
        ->where('sales_returns.date', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $PurchaseReturnReels = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
        ->where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNull('deckle_id')
        ->whereNotNull('purchase_return_id')
        ->where('purchase_returns.date', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $SoldReels = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
        ->where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNotNull('sale_id')
        ->where('sales.date', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $ConsumedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
        ->where('item_size_stocks.company_id', $company_id)
        ->where('item_size_stocks.item_id', $item_id)
        ->whereNotNull('sj_consumption_id')
        ->where('stock_journal.jdate', '<', $date)
        ->select('item_size_stocks.*')
        ->get();

    $OpeningPlus = $openingReels
        ->merge($CreatedReels)
        ->merge($GeneratedReels)
        ->merge($SaleReturnReels)
        ->merge($PurchasedReels);

    $OpeningMinus = $SoldReels
        ->merge($ConsumedReels)
        ->merge($PurchaseReturnReels);

    $ActualOpeningReels = $OpeningPlus->diff($OpeningMinus);

    $ActualOpeningReels = $ActualOpeningReels->map(function($r){

        $sizeParts = explode("X",$r->size);
        $r->size_order = intval($sizeParts[0]);

        return $r;
    });

    $ActualOpeningReels = $ActualOpeningReels->sortBy([
        ['size_order','asc'],
        ['reel_no','asc']
    ]);


        $GroupedData = $ActualOpeningReels->groupBy('size')->map(function ($group) {
    return $group->values();
});

    return response()->json([
        'code'=>200,
        'item_id'=>$item_id,
        'item_name'=>$item_name,
        'date'=>$request->date,
        'data'=>$GroupedData
    ]);
}


}
