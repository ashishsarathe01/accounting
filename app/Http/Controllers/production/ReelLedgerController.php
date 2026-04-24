<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;
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

class ReelLedgerController extends Controller
{
    
    
     public function filter(Request $request){   
        $company_id = Session::get('user_company_id');
        $item_id    = $request->item_id;
        $f_date = $request->f_date ?? Carbon::today()->format('Y-m-d');
        $fr_date = Carbon::parse($f_date)->startOfDay();   // 00:00:00        
        $t_date = $request->t_date ?? Carbon::today()->format('Y-m-d');
        $tr_date = Carbon::parse($t_date)->endOfDay();     // 23:59:59
        /* --------------------------------------------
            1️⃣ GET ITEM LIST (for dropdown)
        ---------------------------------------------*/
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id','name','bf','gsm','speed','manage_items.id as item_id')
                                ->where('production_items.company_id',$company_id)
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        if($item_id){
            $itemName = ManageItems::where('id',$item_id)
                                ->value('name');
        }else{
            $itemName=null;  
        }
        /* ============================================================
            🔵 PART 1 — OPENING BALANCE  (before from_date)
        ============================================================*/

        /** 1. Opening Reels */
        $openingReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                                        ->join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
                                        ->leftJoin('production_items', 'manage_items.id', '=', 'production_items.item_id')
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->where('item_size_stocks.deckle_id', 0)
                                        ->select(
                                            'item_size_stocks.*',
                                            'production_items.id as production_id'
                                        )
                                        ->get();

        
        $CreatedReels = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNotNull('item_size_stocks.deckle_id')
                                        ->where('item_size_stocks.deckle_id', '>', 0)
                                        ->where('deckle_processes.end_time_stamp', '<', $fr_date) // ✅ IMPORTANT CHANGE
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 3. Purchased Reels */
        $PurchasedReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                                        ->join('purchases','item_size_stocks.purchase_id','purchases.id')
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('item_size_stocks.deckle_id')
                                        ->where('purchases.date', '<', $f_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 4. Generated Reels */
        $GeneratedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('sj_generated_id')
                                        ->where('stock_journal.jdate', '<', $f_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 5. Sale Return Reels */
        $SaleReturnReels = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('sale_return_id')
                                        ->where('sales_returns.date', '<', $f_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 6. Purchase Return Reels */
        $PurchaseReturnReels = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('purchase_return_id')
                                        ->where('purchase_returns.date', '<', $f_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 7. Sold Reels */
        $SoldReels = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                                    ->where('item_size_stocks.company_id', $company_id)
                                    ->where('item_size_stocks.item_id', $item_id)
                                    ->whereNotNull('sale_id')
                                    ->where('sales.date', '<', $f_date)
                                    ->select('item_size_stocks.*')
                                    ->get();

        /** 8. Consumed Reels */
        $ConsumedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNotNull('sj_consumption_id')
                                        ->where('stock_journal.jdate', '<', $f_date)
                                        ->select('item_size_stocks.*')
                                        ->get();


        $OpeningStock = collect();
        $OpeningNegative = collect();

        /* ---- PROCESS ALL ADDITIONS FIRST ---- */
        $AllOpeningAdds = $openingReels
                            ->merge($CreatedReels)
                            ->merge($GeneratedReels)
                            ->merge($SaleReturnReels)
                            ->merge($PurchasedReels)
                            ->unique('id');

        /* ---- PROCESS ALL REMOVALS ---- */
        $AllOpeningRemovals = $SoldReels
                                ->merge($ConsumedReels)
                                ->merge($PurchaseReturnReels)
                                ->unique('id');

        /* ---- APPLY MOVEMENT LIKE LEDGER ---- */
        foreach ($AllOpeningAdds as $reel) {
            $OpeningStock->push($reel);
        }
        foreach ($AllOpeningRemovals as $reel) {
            $exists = $OpeningStock->firstWhere('id', $reel->id);
            if ($exists) {
                $OpeningStock = $OpeningStock->reject(function ($r) use ($reel) {
                    return $r->id == $reel->id;
                });
            } else {
                // Sold before creation → negative opening
                $OpeningNegative->push($reel);
            }
        }

        /* ---- FINAL OPENING ---- */
        $OpeningReelCount = $OpeningStock->count() - $OpeningNegative->count();
        $OpeningTotalWeight = $OpeningStock->sum('weight')
                                - $OpeningNegative->sum('weight');
        $ActualOpeningReels = $OpeningStock;
        
    
        /* ============================================================
            🔵 PART 2 — DATE WISE REEL LEDGER (In / Out / Closing)
        ============================================================*/
        $datePeriod = CarbonPeriod::create($f_date, $t_date);
        $ledger = [];
        $RunningOpening = $ActualOpeningReels->values();   // keep opening for next day closing
        $NegativeReels = collect();
        $date_index = 0;
        foreach ($datePeriod as $dt) {
            // $day = $dt->format('Y-m-d');
            // $dayStart = $day . ' 08:00:00';
            // $dayE = Carbon::parse($day)->addDay()->format('Y-m-d');
            // $dayEnd   = $dayE . ' 07:59:59';        
            $day = $dt->format('Y-m-d');
            $dayStart = $day . ' 00:00:00';
            $dayEnd   = $day . ' 23:59:59';

            $InCreated = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNotNull('item_size_stocks.deckle_id')
                                        ->where('item_size_stocks.deckle_id', '>', 0)
                                        ->whereBetween('deckle_processes.end_time_stamp', [$dayStart, $dayEnd]) // ✅ IMPORTANT CHANGE
                                        ->select('item_size_stocks.*')
                                        ->get();
        
            $InPurchased = ItemSizeStock::join('purchases','item_size_stocks.purchase_id','purchases.id')
                            ->where('item_size_stocks.company_id', $company_id)
                            ->where('item_size_stocks.item_id', $item_id)
                            ->whereNull('deckle_id')
                            ->where('purchases.date', $day)
                            ->select('item_size_stocks.*')
                            ->get();

            $InGenerated = ItemSizeStock::join('stock_journal','item_size_stocks.sj_generated_id','stock_journal.id')
                            ->where('item_size_stocks.company_id',$company_id)
                            ->where('item_size_stocks.item_id',$item_id)
                            ->where('stock_journal.jdate', $day)
                            ->select('item_size_stocks.*')
                            ->get();

            $InSaleReturn = ItemSizeStock::join('sales_returns','item_size_stocks.sale_return_id','sales_returns.id')
                            ->where('item_size_stocks.company_id',$company_id)
                            ->where('item_size_stocks.item_id',$item_id)
                            ->where('sales_returns.date', $day)
                            ->select('item_size_stocks.*')
                            ->get();

            $InPurchaseReturn = ItemSizeStock::join('purchase_returns','item_size_stocks.purchase_return_id','purchase_returns.id')
                            ->where('item_size_stocks.company_id',$company_id)
                            ->where('item_size_stocks.item_id',$item_id)
                            ->where('purchase_returns.date', $day)
                            ->select('item_size_stocks.*')
                            ->get();


            $InReels = collect()
                            ->merge($InCreated)
                            ->merge($InPurchased)
                            ->merge($InGenerated)
                            ->merge($InSaleReturn)
                            ->unique('id');


            /* ---- OUTWARD REELS (Removal on this day) ---- */
            $OutSold = ItemSizeStock::join('sales','item_size_stocks.sale_id','sales.id')
                            ->where('item_size_stocks.company_id',$company_id)
                            ->where('item_size_stocks.item_id',$item_id)
                            ->where('sales.date', $day)
                            ->select('item_size_stocks.*')
                            ->get();

            $OutConsumed = ItemSizeStock::join('stock_journal','item_size_stocks.sj_consumption_id','stock_journal.id')
                            ->where('item_size_stocks.company_id',$company_id)
                            ->where('item_size_stocks.item_id',$item_id)
                            ->where('stock_journal.jdate', $day)
                            ->select('item_size_stocks.*')
                            ->get();


            $OutReels = collect()
                            ->merge($OutSold)
                            ->merge($OutConsumed)
                            ->merge($InPurchaseReturn)
                            ->unique('id');

    
            // BEFORE MOVEMENT — store opening snapshot
            $OpeningSnapshot = $RunningOpening->values();
            $OpeningWeightSnapshot = $OpeningSnapshot->sum('weight');

            /* -----------------------------
            PROCESS OUT FIRST
            ------------------------------*/
            foreach ($OutReels as $outReel) {

                $exists = $RunningOpening->firstWhere('id', $outReel->id);

                if ($exists) {

                    $RunningOpening = $RunningOpening->reject(function ($r) use ($outReel) {
                        return $r->id == $outReel->id;
                    });

                } else {

                    // Sold before creation → negative
                    $NegativeReels->push($outReel);
                }
            }

            /* -----------------------------
            PROCESS IN
            ------------------------------*/
            foreach ($InReels as $inReel) {

                $negativeMatch = $NegativeReels->firstWhere('id', $inReel->id);

                if ($negativeMatch) {

                    // Adjust negative stock
                    $NegativeReels = $NegativeReels->reject(function ($r) use ($inReel) {
                        return $r->id == $inReel->id;
                    });

                } else {

                    $RunningOpening->push($inReel);
                }
            }

            /* -----------------------------
            FINAL CALCULATION
            ------------------------------*/
            $PositiveStock = $RunningOpening->count();
            $NegativeStock = $NegativeReels->count();
            if($date_index==0){
                $opening_count = $OpeningReelCount;
                $ClosingCount = $OpeningReelCount + $InReels->count() - $OutReels->count();
                $date_opening_weight = $OpeningTotalWeight;
                $ClosingWeight = $OpeningTotalWeight + $InReels->sum('weight') - $OutReels->sum('weight');
            }else{
                $opening_count = $ClosingCount;
                $ClosingCount = $PositiveStock - $NegativeStock;
                
                $date_opening_weight = $ClosingWeight;
                $ClosingWeight = $RunningOpening->sum('weight')
                            - $NegativeReels->sum('weight');
            }
            /* -----------------------------
            STORE LEDGER ROW
            ------------------------------*/
            
            $date_index++;
            // echo "<pre>";
            //         print_r($OpeningSnapshot->toArray());die;
            $ledger[] = [
                'date' => $day,

                'opening_count' => $opening_count,
                'opening_weight' => $date_opening_weight,

                'in_count' => $InReels->count(),
                'in_weight' => $InReels->sum('weight'),

                'out_count' => $OutReels->count(),
                'out_weight' => $OutReels->sum('weight'),

                'closing_count' => $ClosingCount,
                'closing_weight' => $ClosingWeight,

                'opening' => [
                    'reels' => $OpeningSnapshot,
                    'weight'=> $date_opening_weight,
                ],

                'in' => [
                    'reels' => $InReels,
                    'weight'=> $InReels->sum('weight'),
                ],

                'out' => [
                    'reels' => $OutReels,
                    'weight'=> $OutReels->sum('weight'),
                ],

                'closing' => [
                    'reels' => $RunningOpening,
                    'weight'=> $ClosingWeight,
                ],
            ];
        }
        $openingReelsC = ItemSizeStock::where('company_id', $company_id)
                                ->where('item_id', $item_id)
                                ->where('deckle_id', 0)
                                ->get();                                    
        $CreatedReelsC = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNotNull('item_size_stocks.deckle_id')
                                        ->where('item_size_stocks.deckle_id', '>', 0)
                                        ->where('deckle_processes.end_time_stamp', '<=', $tr_date) // ✅ IMPORTANT CHANGE
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 3. Purchased Reels */
        $PurchasedReelsC = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                                        ->join('purchases','item_size_stocks.purchase_id','purchases.id')
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('item_size_stocks.deckle_id')
                                        ->where('purchases.date', '<=', $t_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 4. Generated ReelsC */
        $GeneratedReelsC = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('sj_generated_id')
                                        ->where('stock_journal.jdate', '<=', $t_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 5. Sale Return ReelsC */
        $SaleReturnReelsC = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                                            ->where('item_size_stocks.company_id', $company_id)
                                            ->where('item_size_stocks.item_id', $item_id)
                                            ->whereNull('deckle_id')
                                            ->whereNotNull('sale_return_id')
                                            ->where('sales_returns.date', '<=', $t_date)
                                            ->select('item_size_stocks.*')
                                            ->get();

        /** 6. Purchase Return ReelsC */
        $PurchaseReturnReelsC = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('purchase_return_id')
                                        ->where('purchase_returns.date', '<=', $t_date)
                                        ->select('item_size_stocks.*')
                                        ->get();

        /** 7. Sold ReelsC */
        $SoldReelsC = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                                    ->where('item_size_stocks.company_id', $company_id)
                                    ->where('item_size_stocks.item_id', $item_id)
                                    ->whereNotNull('sale_id')
                                    ->where('sales.date', '<=', $t_date)
                                    ->select('item_size_stocks.*')
                                    ->get();

        /** 8. Consumed ReelsC */
        $ConsumedReelsC = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item_id)
                                        ->whereNotNull('sj_consumption_id')
                                        ->where('stock_journal.jdate', '<=', $t_date)
                                        ->select('item_size_stocks.*')
                                        ->get();


            /* ============================================================
        🔵 FINAL CLOSING WITH NEGATIVE SUPPORT
        ============================================================ */

        $ClosingStock = collect();
        $ClosingNegative = collect();

        /* ---- ALL ADDITIONS TILL TO_DATE ---- */
        $AllClosingAdds = $openingReelsC
                            ->merge($CreatedReelsC)
                            ->merge($GeneratedReelsC)
                            ->merge($SaleReturnReelsC)
                            ->merge($PurchasedReelsC)
                            ->unique('id');

        /* ---- ALL REMOVALS TILL TO_DATE ---- */
        $AllClosingRemovals = $SoldReelsC
                                ->merge($ConsumedReelsC)
                                ->merge($PurchaseReturnReelsC)
                                ->unique('id');

        /* ---- PROCESS ADDITIONS ---- */
        foreach ($AllClosingAdds as $reel) {
            $ClosingStock->push($reel);
        }

        /* ---- PROCESS REMOVALS ---- */
        foreach ($AllClosingRemovals as $reel) {

            $exists = $ClosingStock->firstWhere('id', $reel->id);

            if ($exists) {

                $ClosingStock = $ClosingStock->reject(function ($r) use ($reel) {
                    return $r->id == $reel->id;
                });

            } else {

                // Sold before creation → negative
                $ClosingNegative->push($reel);
            }
        }

        /* ---- FINAL CALCULATION ---- */
        $ClosingReelCount  = $ClosingStock->count() - $ClosingNegative->count();

        $ClosingTotalWeight = $ClosingStock->sum('weight')
                                - $ClosingNegative->sum('weight');

        $ActualClosingReels = $ClosingStock;

        /* --------------------------------------------
            RETURN TO BLADE
        ---------------------------------------------*/
        // echo $OpeningTotalWeight;
        // die();
        return view('ReelLedger.reel_ledger', [
            'items' => $items,
            'item_id' => $item_id,
            'itemName' => $itemName,

            'OpeningReels' => $ActualOpeningReels,
            'opening_count' => $OpeningReelCount,
            'opening_weight' => $OpeningTotalWeight,
            'closing_count' => $ClosingReelCount,
            'closing_weight' => $ClosingTotalWeight,

            'ledgerRows' => $ledger,
            'f_date' => $f_date,
            't_date' => $t_date,
        ]);
    }





public function ManageStock(Request $request)
{
    $company_id = Session::get('user_company_id');

    // Only one date
    $date = ($request->date ? Carbon::parse($request->date): Carbon::today())->addDay()->format('Y-m-d');
   $A_date = Carbon::parse($date)->subday()->format('Y-m-d');

    // $cutoff = $date . ' 07:59:59';
    $date1 = $request->date 
            ? Carbon::parse($request->date) 
            : Carbon::today();

   $cutoff = $date1->endOfDay(); // 23:59:59

    // All items
    $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
        ->select('manage_items.id','name','bf','gsm','speed')
        ->where('production_items.company_id',$company_id)
        ->where('production_items.status','1')
        ->orderBy('name')
        ->get();

    $stockData = [];

    foreach($items as $item)
    {

         $openingReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
    ->join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
    ->leftJoin('production_items', 'manage_items.id', '=', 'production_items.item_id')
    ->where('item_size_stocks.item_id', $item->id)
    ->where('item_size_stocks.deckle_id', 0)
    ->select(
        'item_size_stocks.*',
        'production_items.id as production_id'
    )
    ->get();

    /** 2. Created Reels */
    // $CreatedReels = ItemSizeStock::where('company_id', $company_id)
    //                                 ->where('item_id', $item->id)
    //                                 ->whereNotNull('deckle_id')
    //                                 ->where('deckle_id', '>', 0)
    //                                 ->where('created_at', '<=', $cutoff)
    //                                 ->get();
                                    
                                    $CreatedReels = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
    ->where('item_size_stocks.company_id', $company_id)
    ->where('item_size_stocks.item_id', $item->id)
    ->whereNotNull('item_size_stocks.deckle_id')
    ->where('item_size_stocks.deckle_id', '>', 0)
    ->where('deckle_processes.end_time_stamp', '<=', $cutoff) // ✅ Correct Logic
    ->select('item_size_stocks.*')
    ->get();

    /** 3. Purchased Reels */
    $PurchasedReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                                    ->join('purchases','item_size_stocks.purchase_id','purchases.id')
                                    ->where('item_size_stocks.item_id', $item->id)
                                    ->whereNull('item_size_stocks.deckle_id')
                                    ->where('purchases.date', '<', $date)
                                    ->select('item_size_stocks.*')
                                    ->get();

    /** 4. Generated Reels */
    $GeneratedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                                    ->where('item_size_stocks.company_id', $company_id)
                                    ->where('item_size_stocks.item_id', $item->id)
                                    ->whereNull('deckle_id')
                                    ->whereNotNull('sj_generated_id')
                                    ->where('stock_journal.jdate', '<', $date)
                                    ->select('item_size_stocks.*')
                                    ->get();

    /** 5. Sale Return Reels */
    $SaleReturnReels = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                                        ->where('item_size_stocks.company_id', $company_id)
                                        ->where('item_size_stocks.item_id', $item->id)
                                        ->whereNull('deckle_id')
                                        ->whereNotNull('sale_return_id')
                                        ->where('sales_returns.date', '<', $date)
                                        ->select('item_size_stocks.*')
                                        ->get();

    /** 6. Purchase Return Reels */
    $PurchaseReturnReels = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                                    ->where('item_size_stocks.company_id', $company_id)
                                    ->where('item_size_stocks.item_id', $item->id)
                                    ->whereNull('deckle_id')
                                    ->whereNotNull('purchase_return_id')
                                    ->where('purchase_returns.date', '<', $date)
                                    ->select('item_size_stocks.*')
                                    ->get();

    /** 7. Sold Reels */
    $SoldReels = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                                ->where('item_size_stocks.company_id', $company_id)
                                ->where('item_size_stocks.item_id', $item->id)
                                ->whereNotNull('sale_id')
                                ->where('sales.date', '<', $date)
                                ->select('item_size_stocks.*')
                                ->get();

    /** 8. Consumed Reels */
    $ConsumedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                                    ->where('item_size_stocks.company_id', $company_id)
                                    ->where('item_size_stocks.item_id', $item->id)
                                    ->whereNotNull('sj_consumption_id')
                                    ->where('stock_journal.jdate', '<', $date)
                                    ->select('item_size_stocks.*')
                                    ->get();


    /** Final Opening = (Add) - (Minus) */
    $OpeningPlus = $openingReels
                        ->merge($CreatedReels)
                        ->merge($GeneratedReels)
                        ->merge($SaleReturnReels)
                        ->merge($PurchasedReels);

    $OpeningMinus = $SoldReels
                        ->merge($ConsumedReels)
                        ->merge($PurchaseReturnReels);

    $ActualOpeningReels = $OpeningPlus->diff($OpeningMinus);

    $OpeningReelCount  = $ActualOpeningReels->count();
    $OpeningTotalWeight = $ActualOpeningReels->sum('weight');
        
        $stockData[] = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'reel_count' => $OpeningReelCount,
            'weight' => $OpeningTotalWeight,
        ];
    }

    return view('ReelLedger.manage_stock', compact('stockData','A_date'));
}

                                
 public function ItemWiseReelStock(Request $request){
   $company_id = Session::get('user_company_id');

    /* -------------------------------
       1️⃣ DATE
    --------------------------------*/
    $date = ($request->date ? Carbon::parse($request->date) : Carbon::today())
            ->addDay()
            ->format('Y-m-d');

    
    $aA_date = Carbon::parse($date)->subDay()->format('Y-m-d');
    $date1 = $request->date 
            ? Carbon::parse($request->date) 
            : Carbon::today();

    $cutoff = $date1->endOfDay();

    $date = $date1->copy()->addDay()->format('Y-m-d');

    $A_date = $date1->format('d-m-Y');

    /* -------------------------------
       2️⃣ PARTICULAR ITEM 
    --------------------------------*/
    $item_id = $request->item_id;

    if(!$item_id){
        return "Item ID missing.";
    }

    $item_name = ManageItems::where('company_id',$company_id)
                            ->where('id',$item_id)
                            ->value('name');
    /* -------------------------------
       3️⃣ REEL FETCH LOGIC (SAME AS YOURS)
    --------------------------------*/
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

    /* -----------------------------------------
       4️⃣ FINAL AVAILABLE REELS
    ------------------------------------------*/
    $OpeningPlus  = $openingReels
                        ->merge($CreatedReels)
                        ->merge($GeneratedReels)
                        ->merge($SaleReturnReels)
                        ->merge($PurchasedReels);

    $OpeningMinus = $SoldReels
                        ->merge($ConsumedReels)
                        ->merge($PurchaseReturnReels);

    $ActualOpeningReels = $OpeningPlus->diff($OpeningMinus);

    /* -----------------------------------------
       5️⃣ GROUP BY SIZE → Reel_No → (weight, unit)
    ------------------------------------------*/

    // Extract size number before X
   $ActualOpeningReels = $ActualOpeningReels->map(function($r){

    // Extract first number before "X" → convert to int
    // Example: "52X300" → 52
    $sizeParts = explode("X", $r->size);
    $r->size_order = intval($sizeParts[0]);

    return $r;
});

// Sort by size_order ASC then reel_no ASC
$ActualOpeningReels = $ActualOpeningReels->sortBy([
    ['size_order', 'asc'],
    ['reel_no', 'asc']
]);

// Group by Size → then Reel No
$GroupedData = $ActualOpeningReels
    ->groupBy('size')
    ->map(function($sizeGroup){
        return $sizeGroup->groupBy('reel_no');
    });

// Return to Blade
return view('ReelLedger.manage_stock_detail', [
    'GroupedData' => $GroupedData,
    'A_date'      => $A_date,
    'aA_date' => $aA_date,
    'item_id' => $item_id,
    'item_name' => $item_name
]);
}




public function downloadReelStockPDF(Request $request)
{
    $company_id = Session::get('user_company_id');

    /* --- 1. DATE LOGIC --- */
    $date1 = $request->date 
        ? Carbon::parse($request->date) 
        : Carbon::today();

    $cutoff = $date1->endOfDay();

    $date = $date1->copy()->addDay()->format('Y-m-d');

    $A_date = $date1->format('d-m-Y');

    /* --- 2. ITEM CHECK --- */
    $item_id = $request->item_id;
    if (!$item_id) {
        return "Item ID missing.";
    }

    /* ------------ 3. FETCH ALL REELS ------------ */

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

    /* ------------ 4. AVAILABLE REELS ------------ */

    $OpeningPlus  = $openingReels
                        ->merge($CreatedReels)
                        ->merge($GeneratedReels)
                        ->merge($SaleReturnReels)
                        ->merge($PurchasedReels);

    $OpeningMinus = $SoldReels
                        ->merge($ConsumedReels)
                        ->merge($PurchaseReturnReels);

    $ActualOpeningReels = $OpeningPlus->diff($OpeningMinus);

    /* ------------ 5. SORT BY SIZE + REEL NO ------------ */

    $ActualOpeningReels = $ActualOpeningReels->map(function($r){
        $sizeParts = explode("X", $r->size);
        $r->size_order = intval($sizeParts[0]);
        return $r;
    });

    $ActualOpeningReels = $ActualOpeningReels->sortBy([
        ['size_order','asc'],
        ['reel_no','asc']
    ]);

    /* ------------ 6. FLATTEN FOR PDF TABLE ------------ */
    $FinalList = [];
    $sn = 1;
    $total_weight = 0;

    foreach ($ActualOpeningReels as $r) {
        $FinalList[] = [
            'sn'      => $sn++,
            'reel_no' => $r->reel_no,
            'size'    => $r->size,
            'weight'  => $r->weight,
            'unit'    => $r->unit
        ];

        $total_weight += $r->weight;
    }

    /* ------------ 7. ITEM NAME ------------ */
    $ItemName = ManageItems::where('id', $item_id)->value('name');

    /* ------------ 8. LOAD PDF BLADE ------------ */
    $pdf = Pdf::loadView('ReelLedger.reel_stock_item_pdf', [
        'FinalList'    => $FinalList,
        'total_weight' => $total_weight,
        'A_date'       => $A_date,
        'ItemName'     => $ItemName
    ]);

    return $pdf->download("Reel_Stock_$A_date.pdf");
}
}
