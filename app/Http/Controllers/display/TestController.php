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
use App\Helpers\CommonHelper;
;
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
    public function index(){
        
        $companyId = Session::get('user_company_id');
        $financialYear = Session::get('default_fy');
    
        [$startYear, $endYear] = explode('-', $financialYear);
    
        $fromDate = $startYear . '-04-01';
        $toDate   = date('Y-m-d');
        if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
            $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        }
        /*
        |--------------------------------------------------------------------------
        | Load Data
        |--------------------------------------------------------------------------
        */
        
                        
                        die;
        $heads = AccountHeading::select('id', 'name', 'bs_profile','show_in_balance_sheet')
            ->where('status','1')
            ->where('delete','0')
            ->where('company_id',0)
            ->get();
    
        $allGroups = AccountGroups::select('id', 'heading', 'heading_type','stock_in_hand')
                                    ->whereIn('company_id', [$companyId, 0])
                                    ->where('status', '1')
                                    ->where('delete', '0')
                                    ->get();
        
        $allAccounts = Accounts::select('id', 'under_group', 'under_group_type')
                    ->whereIn('company_id', [$companyId, 0])
                    ->where('status', '1')
                    ->where('delete', '0')
                    ->orderBy('account_name')
                    ->get();
        /*
        |--------------------------------------------------------------------------
        | Ledger Sum
        |--------------------------------------------------------------------------
        */
    
        $ledgerSums = DB::table('account_ledger as al')
            ->join('accounts as a', 'a.id', '=', 'al.account_id')
            ->selectRaw('
                al.account_id,
                a.account_name,
                SUM(al.debit) as debit,
                SUM(al.credit) as credit
            ')
            ->where('al.company_id', $companyId)
            ->where('al.status', '1')
            ->where('al.delete_status', '0')
            ->where(function ($q) use ($toDate) {
                $q->where('al.txn_date', '<=', $toDate)
                  ->orWhere('al.entry_type', '-1');
            })
            ->groupBy('al.account_id', 'a.account_name')
            ->get()
            ->keyBy('account_id');
    
        /*
        |--------------------------------------------------------------------------
        | Build Tree
        |--------------------------------------------------------------------------
        */
        // $stock_in_hand = CommonHelper::ClosingStock($fromDate);
        // $stock_in_hand = round($stock_in_hand,2);
        
            $account_id_arr = [];
            foreach ($heads as $head) {
                /*
                |--------------------------------------------------------------
                | Head Main Groups
                |--------------------------------------------------------------
                */
                $groupIds = $allGroups
                    ->filter(function ($row) use ($head) {
                        return $row->heading == $head->id;
                    })
                    ->pluck('id')
                    ->toArray();
            
                /*
                |--------------------------------------------------------------
                | Recursive Child Groups
                |--------------------------------------------------------------
                */
                $childIds = $this->getAllChildGroups1($groupIds, $companyId);
            
                $finalGroupIds = array_unique(array_merge($groupIds, $childIds));
        
                /*
                |--------------------------------------------------------------
                | Accounts Under Groups + Head
                |--------------------------------------------------------------
                */
                $accountIds = $allAccounts
                    ->filter(function ($acc) use ($finalGroupIds, $head) {
                        return (
                            ($acc->under_group_type == 'group' &&
                             in_array($acc->under_group, $finalGroupIds))
                            ||
                            ($acc->under_group_type == 'head' &&
                             $acc->under_group == $head->id)
                        );
                    })
                    ->pluck('id')
                    ->toArray();
                $account_id_arr = array_unique(array_merge($account_id_arr,$accountIds));
            }
            $final_account = [];
            foreach ($account_id_arr as $accId) {
                if (isset($ledgerSums[$accId])) {
                    array_push($final_account,array("id"=>$accId,"account_name"=>$ledgerSums[$accId]->account_name,"debit"=>$ledgerSums[$accId]->debit,"credit"=>$ledgerSums[$accId]->credit));
                }
            }
       // echo "<pre>";print_r($ledgerSums);
        //die;
        $current_journal_amount = Journal::where('journals.company_id', Session::get('user_company_id'))
                                        ->where('journals.financial_year', $financialYear)
                                        ->where('journals.delete', '0')
                                        ->where('form_source', 'profitloss')
                                        ->join('journal_details', 'journals.id', '=', 'journal_details.journal_id')
                                        ->where('journal_details.delete', '0')
                                        ->where('journal_details.id', '!=', 13319)
                                        ->sum('journal_details.debit');
        $profit_loss_amount = CommonHelper::profitLoss($financialYear,$fromDate,$toDate);
       //Prevoius year profit & loss
        list($start, $end) = explode('-', $financialYear);
        $prevFy = str_pad($start - 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($end - 1, 2, '0', STR_PAD_LEFT);
        $prev_year_profitloss =  CommonHelper::profitLoss($prevFy);
        //Check Profit & Loss Account Entry
        $jouranl = Journal::select('id')
                         ->withSum(['journal_details' => function ($query) {
                            $query->where('id','!=','13319')
                            ->where('journal_details.delete','0');
                         }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                         ->where('journals.financial_year',$prevFy)
                         ->where('journals.delete','0')
                         ->where('form_source','profitloss')
                         ->get();
                         
        $journal_amount = 0;
        if(count($jouranl)>0){
            foreach ($jouranl as $key => $value) {
                $journal_amount = $journal_amount + $value->journal_details_sum_debit;
            }
        }
        $prev_year_profit_status = 0;
        if($prev_year_profitloss<0){
            $prev_year_profit_status = 1;
        }
        $prev_year_profitloss = abs($prev_year_profitloss) - $journal_amount;
        
        
        return view('display/test',[
            'accounts'=>$final_account,
            "prev_year_profitloss"=>$prev_year_profitloss,
            "prevFy"=>$prevFy,
            "prev_year_profit_status"=>$prev_year_profit_status,
            'stock_in_hand'=>$stock_in_hand,
            'current_journal_amount'=>$current_journal_amount,
            'profit_loss_amount'=>$profit_loss_amount,
            
        ]);
    }
}