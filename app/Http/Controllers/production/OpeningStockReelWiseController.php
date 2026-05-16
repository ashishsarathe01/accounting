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

class OpeningStockReelWiseController extends Controller
{
    public function filter(Request $request)
    {
        $company_id = Session::get('user_company_id');
        $item_id    = $request->item_id;
        $f_date     = $request->f_date ?? Carbon::today()->format('Y-m-d');
        $t_date     = $request->t_date ?? Carbon::today()->format('Y-m-d');

        $items = ProductionItem::join('manage_items', 'production_items.item_id', '=', 'manage_items.id')
            ->select('production_items.id', 'name', 'bf', 'gsm', 'speed', 'manage_items.id as item_id')
            ->where('production_items.company_id', $company_id)
            ->where('production_items.status', '1')
            ->orderBy('name')
            ->get();

        $itemName = $item_id
            ? ManageItems::where('id', $item_id)->value('name')
            : null;

        if (!$item_id) {
            return view('ReelLedger.reel_ledger_new', [
                'items'          => $items,
                'item_id'        => null,
                'itemName'       => null,
                'OpeningReels'   => collect(),
                'opening_count'  => 0,
                'opening_weight' => 0,
                'closing_count'  => 0,
                'closing_weight' => 0,
                'ledgerRows'     => [],
                'f_date'         => $f_date,
                't_date'         => $t_date,
            ]);
        }
        $openingSeed = ItemSizeStock::where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->where('deckle_id', 0)
            ->selectRaw('COUNT(*) as reel_count, COALESCE(SUM(weight),0) as total_weight')
            ->first();

        $openingSummary = DB::table('item_daily_reel_stock')
            ->where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->where('stock_date', '<', $f_date)
            ->selectRaw('
                COALESCE(SUM(in_reels),  0) as total_in_reels,
                COALESCE(SUM(in_weight), 0) as total_in_weight,
                COALESCE(SUM(out_reels), 0) as total_out_reels,
                COALESCE(SUM(out_weight),0) as total_out_weight
            ')
            ->first();

        $OpeningReelCount   = ($openingSeed->reel_count   ?? 0)
                            + ($openingSummary->total_in_reels  ?? 0)
                            - ($openingSummary->total_out_reels ?? 0);

        $OpeningTotalWeight = ($openingSeed->total_weight  ?? 0)
                            + ($openingSummary->total_in_weight  ?? 0)
                            - ($openingSummary->total_out_weight ?? 0);
        $summaryRows = DB::table('item_daily_reel_stock')
            ->where('company_id', $company_id)
            ->where('item_id', $item_id)
            ->whereBetween('stock_date', [$f_date, $t_date])
            ->get()
            ->keyBy('stock_date');   // ['2026-05-14' => {row}, ...]

        $ledger       = [];
        $runningCount  = $OpeningReelCount;
        $runningWeight = $OpeningTotalWeight;

        foreach (CarbonPeriod::create($f_date, $t_date) as $dt) {
            $day = $dt->format('Y-m-d');
            $row = $summaryRows->get($day); 

            $inCount  = $row ? (int)   $row->in_reels   : 0;
            $inWeight = $row ? (float) $row->in_weight  : 0;
            $outCount = $row ? (int)   $row->out_reels  : 0;
            $outWeight= $row ? (float) $row->out_weight : 0;

            $openCount  = $runningCount;
            $openWeight = $runningWeight;

            $closingCount  = $openCount  + $inCount  - $outCount;
            $closingWeight = $openWeight + $inWeight - $outWeight;

            $ledger[] = [
                'date'           => $day,
                'opening_count'  => $openCount,
                'opening_weight' => $openWeight,
                'in_count'       => $inCount,
                'in_weight'      => $inWeight,
                'out_count'      => $outCount,
                'out_weight'     => $outWeight,
                'closing_count'  => $closingCount,
                'closing_weight' => $closingWeight,
            ];

            $runningCount  = $closingCount;
            $runningWeight = $closingWeight;
        }
        $lastRow = end($ledger);
        $ClosingReelCount   = $lastRow ? $lastRow['closing_count']  : $OpeningReelCount;
        $ClosingTotalWeight = $lastRow ? $lastRow['closing_weight'] : $OpeningTotalWeight;

        return view('ReelLedger.reel_ledger_new', [
            'items'          => $items,
            'item_id'        => $item_id,
            'itemName'       => $itemName,
            'OpeningReels'   => collect(),   
            'opening_count'  => $OpeningReelCount,
            'opening_weight' => $OpeningTotalWeight,
            'closing_count'  => $ClosingReelCount,
            'closing_weight' => $ClosingTotalWeight,
            'ledgerRows'     => $ledger,
            'f_date'         => $f_date,
            't_date'         => $t_date,
        ]);
    }
    public function modalDetail(Request $request)
    {
        $company_id = Session::get('user_company_id');
        $item_id    = $request->item_id;
        $date       = $request->date;              
        $type       = $request->type;             
        $f_date     = $request->f_date;             

        if (!$item_id || !$date || !$type) {
            return response()->json(['error' => 'Missing parameters'], 422);
        }

        $reels = collect();
        if ($type === 'in') {

            $dayStart = $date . ' 00:00:00';
            $dayEnd   = $date . ' 23:59:59';

            $InCreated = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('item_size_stocks.deckle_id')
                ->where('item_size_stocks.deckle_id', '>', 0)
                ->whereBetween('deckle_processes.end_time_stamp', [$dayStart, $dayEnd])
                ->select('item_size_stocks.*')
                ->get();

            $InPurchased = ItemSizeStock::join('purchases', 'item_size_stocks.purchase_id', 'purchases.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->where('purchases.date', $date)
                ->select('item_size_stocks.*')
                ->get();

            $InGenerated = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->where('stock_journal.jdate', $date)
                ->whereNotNull('sj_generated_id')
                ->select('item_size_stocks.*')
                ->get();

            $InSaleReturn = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->where('sales_returns.date', $date)
                ->select('item_size_stocks.*')
                ->get();

            $reels = collect()
                ->merge($InCreated)
                ->merge($InPurchased)
                ->merge($InGenerated)
                ->merge($InSaleReturn)
                ->unique('id')
                ->values();
        }
        elseif ($type === 'out') {

            $OutSold = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->where('sales.date', $date)
                ->select('item_size_stocks.*')
                ->get();

            $OutConsumed = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->where('stock_journal.jdate', $date)
                ->whereNotNull('sj_consumption_id')
                ->select('item_size_stocks.*')
                ->get();

            $OutPurchaseReturn = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->where('purchase_returns.date', $date)
                ->select('item_size_stocks.*')
                ->get();

            $reels = collect()
                ->merge($OutSold)
                ->merge($OutConsumed)
                ->merge($OutPurchaseReturn)
                ->unique('id')
                ->values();
        }
        elseif ($type === 'opening') {

            $boundary = $f_date ?? $date;
            $fr_date  = Carbon::parse($boundary)->startOfDay();

            $openingReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                ->join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
                ->leftJoin('production_items', 'manage_items.id', '=', 'production_items.item_id')
                ->where('item_size_stocks.item_id', $item_id)
                ->where('item_size_stocks.deckle_id', 0)
                ->select('item_size_stocks.*', 'production_items.id as production_id')
                ->get();

            $CreatedReels = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('item_size_stocks.deckle_id')
                ->where('item_size_stocks.deckle_id', '>', 0)
                ->where('deckle_processes.end_time_stamp', '<', $fr_date)
                ->select('item_size_stocks.*')
                ->get();

            $PurchasedReels = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                ->join('purchases', 'item_size_stocks.purchase_id', 'purchases.id')
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('item_size_stocks.deckle_id')
                ->where('purchases.date', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $GeneratedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('sj_generated_id')
                ->where('stock_journal.jdate', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $SaleReturnReels = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('sale_return_id')
                ->where('sales_returns.date', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $SoldReels = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('sale_id')
                ->where('sales.date', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $ConsumedReels = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('sj_consumption_id')
                ->where('stock_journal.jdate', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $PurchaseReturnReels = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('purchase_return_id')
                ->where('purchase_returns.date', '<', $boundary)
                ->select('item_size_stocks.*')
                ->get();

            $OpeningStock = collect();

            $AllAdds = $openingReels
                ->merge($CreatedReels)
                ->merge($GeneratedReels)
                ->merge($SaleReturnReels)
                ->merge($PurchasedReels)
                ->unique('id');

            $AllRemovals = $SoldReels
                ->merge($ConsumedReels)
                ->merge($PurchaseReturnReels)
                ->unique('id');

            foreach ($AllAdds as $reel) {
                $OpeningStock->push($reel);
            }
            foreach ($AllRemovals as $reel) {
                $OpeningStock = $OpeningStock->reject(fn($r) => $r->id == $reel->id);
            }

            $reels = $OpeningStock->values();
        }
        elseif ($type === 'closing') {

            $tr_date = Carbon::parse($date)->endOfDay();

            $openingReelsC = ItemSizeStock::where('company_id', $company_id)
                ->where('item_id', $item_id)
                ->where('deckle_id', 0)
                ->get();

            $CreatedReelsC = ItemSizeStock::join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('item_size_stocks.deckle_id')
                ->where('item_size_stocks.deckle_id', '>', 0)
                ->where('deckle_processes.end_time_stamp', '<=', $tr_date)
                ->select('item_size_stocks.*')
                ->get();

            $PurchasedReelsC = ItemSizeStock::where('item_size_stocks.company_id', $company_id)
                ->join('purchases', 'item_size_stocks.purchase_id', 'purchases.id')
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('item_size_stocks.deckle_id')
                ->where('purchases.date', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $GeneratedReelsC = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_generated_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('sj_generated_id')
                ->where('stock_journal.jdate', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $SaleReturnReelsC = ItemSizeStock::join('sales_returns', 'item_size_stocks.sale_return_id', 'sales_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('sale_return_id')
                ->where('sales_returns.date', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $PurchaseReturnReelsC = ItemSizeStock::join('purchase_returns', 'item_size_stocks.purchase_return_id', 'purchase_returns.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNull('deckle_id')
                ->whereNotNull('purchase_return_id')
                ->where('purchase_returns.date', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $SoldReelsC = ItemSizeStock::join('sales', 'item_size_stocks.sale_id', 'sales.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('sale_id')
                ->where('sales.date', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $ConsumedReelsC = ItemSizeStock::join('stock_journal', 'item_size_stocks.sj_consumption_id', 'stock_journal.id')
                ->where('item_size_stocks.company_id', $company_id)
                ->where('item_size_stocks.item_id', $item_id)
                ->whereNotNull('sj_consumption_id')
                ->where('stock_journal.jdate', '<=', $date)
                ->select('item_size_stocks.*')
                ->get();

            $ClosingStock = collect();

            $AllClosingAdds = $openingReelsC
                ->merge($CreatedReelsC)
                ->merge($GeneratedReelsC)
                ->merge($SaleReturnReelsC)
                ->merge($PurchasedReelsC)
                ->unique('id');

            $AllClosingRemovals = $SoldReelsC
                ->merge($ConsumedReelsC)
                ->merge($PurchaseReturnReelsC)
                ->unique('id');

            foreach ($AllClosingAdds as $reel) {
                $ClosingStock->push($reel);
            }
            foreach ($AllClosingRemovals as $reel) {
                $ClosingStock = $ClosingStock->reject(fn($r) => $r->id == $reel->id);
            }

            $reels = $ClosingStock->values();
        }

        return response()->json([
            'reels'        => $reels,
            'total_weight' => $reels->sum('weight'),
            'total_count'  => $reels->count(),
        ]);
    }
}