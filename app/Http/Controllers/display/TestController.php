<?php

namespace App\Http\Controllers\display;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\Accounts;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ClosingStock;
use App\Models\Journal;
use App\Models\AccountProduction;
use App\Models\AccountProductionDetail;
use App\Models\ItemAverageDetail;
use App\Models\DeckleProcess;
use App\Helpers\CommonHelper;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Session;
use DB;
class TestController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
    public function index(){
        $sales = DB::table('sales')
                    ->where('deleted_by', 104)
                    ->where('company_id', '!=', 12)
                    ->where(function ($query) {
                        $query->where('deleted_at', 'like', '2026-05-27%')
                            ->orWhere('deleted_at', 'like', '2026-05-28%');
                    })
                    ->get();
        echo "<pre>";
        print_r($sales);
        die;
    }
    private function getAllChildGroups1($parentIds, $companyId)
    {
        $rows = AccountGroups::where('heading_type', 'group')
            ->where('delete', '0')
            ->whereIn('company_id', [$companyId, 0])
            ->get(['id', 'heading']);
    
        // Build parent => children map
        $map = [];
    
        foreach ($rows as $row) {
            $map[(int)$row->heading][] = (int)$row->id;
        }
    
        $result = [];
        $queue = array_map('intval', (array)$parentIds);
        $visited = [];
    
        while (!empty($queue)) {
            $parent = array_shift($queue);
    
            if (isset($visited[$parent])) {
                continue;
            }
    
            $visited[$parent] = true;
    
            foreach ($map[$parent] ?? [] as $childId) {
                $result[] = $childId;
                $queue[] = $childId;
            }
        }
    
        return array_values(array_unique($result));
    }
    
}