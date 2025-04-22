<?php
namespace App\Helpers;
use App\Models\ItemAverage;
use App\Models\ItemLedger;
use App\Models\Purchase;
use App\Models\PurchaseDescription;
class CommonHelper
{
    public static function RewriteItemAverage($date)
    {
        //Total Sale
        $totalSale = ItemLedger::where('company_id', auth()->user()->company_id)
            ->where('source', '1')
            ->where('txn_date', '=', $date)
            ->sum('out_weight');
         
    }
}