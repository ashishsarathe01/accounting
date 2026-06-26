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
use App\Models\Companies;
use App\Models\TradePayableAccountMapping;
use App\Models\BalanceSheetGroupMapping;
use App\Models\Journal;
use App\Helpers\CommonHelper;
;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Session;
use DB;
class BalanceSheetController extends Controller{
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
    public function index(Request $request){
        ini_set('memory_limit', '-1');
        $financialYear = Session::get('default_fy');
        $companyId     = Session::get('user_company_id');
        [$startYear, $endYear] = explode('-', $financialYear);
        
        /*
        |--------------------------------------------------------------------------
        | Financial Year Date Range
        |--------------------------------------------------------------------------
        */
        $currentYear = date('m') <= 3
            ? (date('y') - 1) . '-' . date('y')
            : date('y') . '-' . (date('y') + 1);
        
        if ($financialYear == $currentYear) {
            $fromDate = $startYear . '-04-01';
            $toDate   = date('Y-m-d');
        } else {
            $fromDate = $startYear . '-04-01';
            $toDate   = $endYear . '-03-31';
        }
        
        $fromDate = Carbon::parse($fromDate)->format('Y-m-d');
        $toDate   = Carbon::parse($toDate)->format('Y-m-d');
        if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
            
            $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        }
        /*
        |--------------------------------------------------------------------------
        | Load All Heads Once
        |--------------------------------------------------------------------------
        */
        $heads = AccountHeading::select('id', 'name', 'bs_profile','show_in_balance_sheet')
            ->where('status', '1')
            ->where('delete', '0')
            ->where('id', '!=','4')
            ->where('company_id', 0)
            ->get();
        
        /*
        |--------------------------------------------------------------------------
        | Preload Groups Once
        |--------------------------------------------------------------------------
        */
        $allGroups = AccountGroups::select('id', 'heading', 'heading_type','stock_in_hand')
            ->where(function ($q) {
                        $q->whereNull('heading_type')
                          ->orWhere('heading_type', 'head')
                          ->orWhere('heading_type', '');
                    })
            ->whereIn('company_id', [$companyId, 0])
            ->where('status', '1')
            ->where('delete', '0')
            ->get();
        /*
        |--------------------------------------------------------------------------
        | Preload Groups Once Stock In Hand
        |--------------------------------------------------------------------------
        */
        $stockInHandGroups = AccountGroups::select('id')
                            ->where('stock_in_hand', '1')
                            ->whereIn('company_id', [$companyId, 0])
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->first();
        
        /*
        |--------------------------------------------------------------------------
        | Preload Accounts Once
        |--------------------------------------------------------------------------
        */
        $allAccounts = Accounts::select('id', 'under_group', 'under_group_type')
            ->whereIn('company_id', [$companyId, 0])
            ->where('status', '1')
            ->where('delete', '0')
            ->get();
       
        /*
        |--------------------------------------------------------------------------
        | Preload Ledger Sum Once
        |--------------------------------------------------------------------------
        */
       
        $ledgerSums = DB::table('account_ledger')
            ->selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
            ->where('company_id', $companyId)
            ->where('status', '1')
            ->where('delete_status', '0')
            ->where(function ($q) use ($toDate) {
                $q->where('txn_date', '<=', $toDate)
                  ->orWhere('entry_type', '-1');
            })
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');
/*
|--------------------------------------------------------------------------
| Vertical Balance Sheet — Current Year
|--------------------------------------------------------------------------
*/
[$verticalBalances, $verticalMappedKeys, ] = $this->getVerticalBalances(
    $fromDate,
    $toDate,
    $companyId,
    $allAccounts,
    $heads,
    $stockInHandGroups
);

/*
|--------------------------------------------------------------------------
| Vertical Balance Sheet — Previous Year (exact same logic, shifted dates)
|--------------------------------------------------------------------------
*/
// Always use full previous FY: 01-Apr-(startYear-1) to 31-Mar-(startYear)
$prevFromDate = Carbon::createFromDate((int)('20'.$startYear) - 1, 4, 1)->format('Y-m-d');
$prevToDate   = Carbon::createFromDate((int)('20'.$startYear), 3, 31)->format('Y-m-d');

[$verticalBalancesPrevious, $verticalMappedKeysPrevious, ] = $this->getVerticalBalances(
    $prevFromDate,
    $prevToDate,
    $companyId,
    $allAccounts,
    $heads,
    $stockInHandGroups
);
        /*
        |--------------------------------------------------------------------------
        | Stock In hand
        |--------------------------------------------------------------------------
        */
        $stock_in_hand = CommonHelper::ClosingStock($toDate);
        $stock_in_hand = round($stock_in_hand,2);
        $baseQuery = DB::table('purchases')
                        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$toDate])
                        ->whereDate('stock_entry_date', '>', $toDate)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('status', '1')
                        ->where('delete', '0');
        $purchase_in_transit_ids = (clone $baseQuery)->pluck('id')->toArray();
    
        $stock_in_transit_value = (clone $baseQuery)
                        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                        ->value('total');
    
        $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);
        $stock_in_hand = $stock_in_hand + $stock_in_transit_value;
          
        /*
        |--------------------------------------------------------------------------
        | Process Each Head
        |--------------------------------------------------------------------------
        */
        
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
        
            /*
            |--------------------------------------------------------------
            | Balance Calculation
            |--------------------------------------------------------------
            */
            $debit  = 0;
            $credit = 0;
        
            foreach ($accountIds as $accId) {
                if (isset($ledgerSums[$accId])) {
                    $debit  += $ledgerSums[$accId]->debit;
                    $credit += $ledgerSums[$accId]->credit;
                }
            }
            if(in_array($stockInHandGroups->id,$finalGroupIds)){
                $debit  += $stock_in_hand;
            }
            $balance = round($debit - $credit, 2);
        
            $head->balance = $balance;
        }
        $grouped = $heads->groupBy('bs_profile');

        // echo "<pre>";
        // print_r($grouped->toArray());
        // die;
        // Check Current Year Profit & Loss Journal Entry
        $current_journal_amount = Journal::where('journals.company_id', Session::get('user_company_id'))
                                        ->where('journals.financial_year', $financialYear)
                                        ->where('journals.delete', '0')
                                        ->where('form_source', 'profitloss')
                                        ->join('journal_details', 'journals.id', '=', 'journal_details.journal_id')
                                        ->where('journal_details.delete', '0')
                                        ->where('journal_details.id', '!=', 13319)
                                        ->sum('journal_details.debit');
        $profit_loss_amount = 0;$prev_year_profitloss = 0;
        
        //profit & Loss
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
        $company_info = Companies::find(Session::get('user_company_id'));
        return view('display/balanceSheet',[
            'heads'=>$heads,
            'from_date'=>$fromDate,
            'to_date'=>$toDate,
            'current_journal_amount'=>$current_journal_amount,
            'profit_loss_amount'=>$profit_loss_amount,
            'prev_year_profitloss'=>$prev_year_profitloss,
            'company_info'=>$company_info,
            'prev_year_profit_status'=>$prev_year_profit_status,
            'prevFy'=>$prevFy,
            'stock_in_hand'=>$stock_in_hand,
            'verticalBalances'     => $verticalBalances,
    'verticalMappedKeys'   => $verticalMappedKeys,
    'verticalBalancesPrevious'   => $verticalBalancesPrevious,
'verticalMappedKeysPrevious' => $verticalMappedKeysPrevious,
'prevFromDate' => $prevFromDate,
'prevToDate'   => $prevToDate,
        ]);
    }
   public function index1(){
       ini_set('memory_limit', '1024M');
       ini_set('memory_limit', '-1');
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      $from_date = $y['0']."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = date('Y-m-t');      
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $from_date = $y[0]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = $y[1]."-03-31";
         $to_date = date('Y-m-d',strtotime($to_date));
      }
      

         $to_date = Carbon::parse($to_date)->format('Y-m-d');

         $ledgerFilters = function ($q) use ($to_date) {
            $q->where('company_id', Session::get('user_company_id'))
               ->where('status', '1')
               ->where('delete_status', '0')
               ->where(function($q2) use ($to_date) {
                  $q2->where('txn_date', '<=', $to_date)
                     ->orWhere('entry_type', '-1');
               });
         };

         $liability = AccountHeading::with([
            'accountGroup.account.accountLedger' => $ledgerFilters,
            'accountGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
            'accountGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
            'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
            'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
            'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
            'accountWithHead.accountLedger' => $ledgerFilters,
            'accountGroup' => fn($q) => $q->where('heading_type', 'head'),
         ])
         ->where('bs_profile', '1')
         ->where('status', '1')
         ->where('delete', '0')
         ->where('company_id', '0')
         ->get();
           
      // $liability = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
      //                               $q->where(function($q1) use ($to_date){
      //                                  $q1->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q1->where('status', '1');
      //                                  $q1->where('delete_status', '0');
      //                               })->where(function($q2) use ($to_date){
      //                                  $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q2->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
      //                               $q->where(function($q1) use ($to_date){
      //                                  $q1->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q1->where('status', '1');
      //                                  $q1->where('delete_status', '0');
      //                               })->where(function($q2) use ($to_date){
      //                                  $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q2->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
      //                               $q->where(function($q1) use ($to_date){
      //                                  $q1->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q1->where('status', '1');
      //                                  $q1->where('delete_status', '0');
      //                               })->where(function($q2) use ($to_date){
      //                                  $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q2->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
      //                               $q4->where(function($q5) use ($to_date){
      //                                  $q5->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q5->where('status', '1');
      //                                  $q5->where('delete_status', '0');
      //                               })->where(function($q6) use ($to_date){                                       
      //                                  $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q6->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountGroup'=> function($q3){
      //                               $q3->where('heading_type','head');
      //                            }
      //                ])
      //                ->where('bs_profile','1')
      //                ->where('status','1')
      //                ->where('delete','0')
      //                ->where('company_id','0')
      //                ->get();      
            $assets = AccountHeading::with([
                           'accountGroup.account.accountLedger' => $ledgerFilters,
                           'accountGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
                           'accountGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
                           'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
                           'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
                           'accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
                           'accountWithHead.accountLedger' => $ledgerFilters,
                           'accountGroup' => fn($q) => $q->where('heading_type', 'head'),
                        ])
                        ->where('bs_profile', '2')  // for Assets
                        ->where('status', '1')
                        ->where('delete', '0')
                        ->where('company_id', '0')
                        ->get();
                     //    echo "<pre>";
                     // print_r($assets->toArray());
                     // echo "</pre>";
                        
      // $assets = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
      //                               $q->where(function($q1) use ($to_date){
      //                                  $q1->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q1->where('status', '1');
      //                                  $q1->where('delete_status', '0');
      //                               })->where(function($q2) use ($to_date){
      //                                  $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q2->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
      //                               $q->where(function($q1) use ($to_date){
      //                                  $q1->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q1->where('status', '1');
      //                                  $q1->where('delete_status', '0');
      //                               })->where(function($q2) use ($to_date){
      //                                  $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q2->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
      //                               $q4->where(function($q5) use ($to_date){
      //                                  $q5->where('company_id', '=', Session::get('user_company_id'));
      //                                  $q5->where('status', '1');
      //                                  $q5->where('delete_status', '0');
      //                               })->where(function($q6) use ($to_date){
      //                                  $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
      //                                  $q6->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountGroup'=> function($q3){
      //                               $q3->where('heading_type','head');
      //                            }
      //                ])
      //                ->where('bs_profile','2')
      //                ->where('status','1')
      //                ->where('delete','0')
      //                ->where('company_id','0')
      //                ->get();      
      $stock_in_hand = CommonHelper::ClosingStock($to_date);
     
      $stock_in_hand = round($stock_in_hand,2); 
      $profitloss = CommonHelper::profitLoss($financial_year);
      $baseQuery = DB::table('purchases')
                        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to_date])
                        ->whereDate('stock_entry_date', '>', $to_date)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('status', '1')
                        ->where('delete', '0');
        $purchase_in_transit_ids = (clone $baseQuery)->pluck('id')->toArray();
    
        $stock_in_transit_value = (clone $baseQuery)
                        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                        ->value('total');
    
        $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);
        $stock_in_hand = $stock_in_hand + $stock_in_transit_value;
      
      //Check Current Year Profit & Loss Account Entry
      $current_jouranl = Journal::select('id')
                     ->withSum(['journal_details' => function ($query) {
                        $query->where('id','!=','13319')
                        ->where('journal_details.delete','0');
                     }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                     ->where('journals.financial_year',$financial_year)
                     ->where('journals.delete','0')
                     ->where('form_source','profitloss')
                     ->get();
      $current_journal_amount = 0;
      if(count($current_jouranl)>0){
         foreach ($current_jouranl as $key => $value) {
            $current_journal_amount = $current_journal_amount + $value->journal_details_sum_debit;
         }
      }
      //Prevoius year profit & loss
      list($start, $end) = explode('-', $financial_year);
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
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss])->with('prev_year_profitloss',$prev_year_profitloss)->with('prev_year_profit_status',$prev_year_profit_status)->with('prevFy',$prevFy)->with('current_journal_amount',$current_journal_amount);
   }
   public function filter(Request $request){
      $financial_year = $request->financial_year;
      $y = explode("-",$financial_year);
      $from_date = $y[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $y[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
         $from_date = $request->from_date;
         $to_date = $request->to_date;
      }
      $liability = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                 }
                     ])
                     ->where('bs_profile','1')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();
                     // echo '<pre>';
                     // print_r($liability->toArray());die;
      // $ledgerFilters = function ($q) use ($to_date) {
      //       $q->where('company_id', Session::get('user_company_id'))
      //          ->where('status', '1')
      //          ->where('delete_status', '0')
      //          ->where(function($q2) use ($to_date) {
      //             $q2->where('txn_date', '<=', $to_date)
      //                ->orWhere('entry_type', '-1');
      //          });
      //    };
      //         $assets = AccountHeading::with([
      //                      'accountGroup.account.accountLedger' => $ledgerFilters,
      //                      'accountGroup.accountUnderGroup.account.accountLedger' => $ledgerFilters,
      //                      'accountWithHead.accountLedger' => $ledgerFilters,
      //                      'accountGroup' => fn($q) => $q->where('heading_type', 'head'),
      //                   ])
      //                   ->where('bs_profile', '2')  // for Assets
      //                   ->where('status', '1')
      //                   ->where('delete', '0')
      //                   ->where('company_id', '0')
      //                   ->get();       
      $assets = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                    
                                 }
                     ])
                     ->where('bs_profile','2')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();
                     // echo "<pre>";
                     // print_r($assets->toArray());
                     // echo "</pre>";
      
      $stock_in_hand = CommonHelper::ClosingStock($to_date);
      $stock_in_hand = round($stock_in_hand,2);
      //transit
      $baseQuery = DB::table('purchases')
                        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to_date])
                        ->whereDate('stock_entry_date', '>', $to_date)
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('status', '1')
                        ->where('delete', '0');
        $purchase_in_transit_ids = (clone $baseQuery)->pluck('id')->toArray();
    
        $stock_in_transit_value = (clone $baseQuery)
                        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
                        ->value('total');
    
        $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);
        $stock_in_hand = $stock_in_hand + $stock_in_transit_value;
      $profitloss = CommonHelper::profitLoss($financial_year,$from_date,$to_date);
      
      //Check Current Year Profit & Loss Account Entry
      $current_jouranl = Journal::select('id')
                     ->withSum(['journal_details' => function ($query) {
                        $query->where('id','!=','13319')
                        ->where('journal_details.delete','0');
                     }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                     ->where('journals.financial_year',$financial_year)
                     ->where('journals.delete','0')
                     ->where('form_source','profitloss')
                     ->get();
      $current_journal_amount = 0;
      if(count($current_jouranl)>0){
         foreach ($current_jouranl as $key => $value) {
            $current_journal_amount = $current_journal_amount + $value->journal_details_sum_debit;
         }
      }
      //Previous year profit & loss
      list($start, $end) = explode('-', $financial_year);
      $prevFy = str_pad($start - 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($end - 1, 2, '0', STR_PAD_LEFT);
      $prev_year_profitloss =  CommonHelper::profitLoss($prevFy);
      //Check Previous Profit & Loss Account Entry
      $jouranl = Journal::select('id')
                     ->withSum(['journal_details' => function ($query) {
                        $query->where('id','!=','13319')
                        ->where('delete','0');
                     }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                     ->where('journals.financial_year',$prevFy)
                     ->where('delete','0')
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
      
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss])->with('prev_year_profitloss',$prev_year_profitloss)->with('prev_year_profit_status',$prev_year_profit_status)->with('prevFy',$prevFy)->with('current_journal_amount',$current_journal_amount);
   }
   public function groupBalanceByHead($id,$from_date,$to_date){
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      $head = AccountHeading::select('name')
                              ->where('id',$id)
                              ->first(); 
                              
      $group = AccountGroups::with(['account'=>function($query)use($to_date){
                                    $query->select('id','account_name','under_group');
                                    $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status','1');
                                       $q1->where('delete_status','0');
                                       $q1->where(function($q2)use ($to_date){
                                          $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                          $q2->orWhere('entry_type','-1');
                                       });
                                    }], 'debit');
                                       $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                          $q1->where('company_id', '=', Session::get('user_company_id'));
                                          $q1->where('status','1');
                                          $q1->where('delete_status','0');
                                          $q1->where(function($q2)use ($to_date){
                                             $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                             $q2->orWhere('entry_type','-1');
                                          });
                                       }], 'credit');
                                 }])
                                 ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                                 ->where('heading',$id)
                                 ->where('status','1')
                                 ->where('delete','0')
                                 ->where('heading_type','head')
                                 ->orWhere('heading_type','=','')
                                 ->select('id','name','stock_in_hand','heading_type')
                                 ->orderBy('name')
                                 ->get();
            $undergroup = AccountGroups::with(['accountUnderGroup'=>function($q)use($to_date){
                                 $q->select('id','name','heading','heading_type');                                 
                                 $q->where('status','1');
                                 $q->where('delete','0');
                                 $q->with(['account'=>function($query)use($to_date){
                                    $query->select('id','account_name','under_group');
                                    $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status','1');
                                       $q1->where('delete_status','0');
                                       $q1->where(function($q2)use ($to_date){
                                          $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                          $q2->orWhere('entry_type','-1');
                                       });
                                    }], 'debit');
                                       $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                          $q1->where('company_id', '=', Session::get('user_company_id'));
                                          $q1->where('status','1');
                                          $q1->where('delete_status','0');
                                          $q1->where(function($q2)use ($to_date){
                                             $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                             $q2->orWhere('entry_type','-1');
                                          });
                                       }], 'credit');
                                 }]);
                                 $q->with(['accountUnderGroup'=>function($qa)use($to_date){
                                    $qa->select('id','name','heading','heading_type');
                                    $qa->where('status','1');
                                    $qa->where('delete','0');
                                    $qa->with(['account'=>function($query)use($to_date){
                                       $query->select('id','account_name','under_group');
                                       $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                          $q1->where('company_id', '=', Session::get('user_company_id'));
                                          $q1->where('status','1');
                                          $q1->where('delete_status','0');
                                          $q1->where(function($q2)use ($to_date){
                                             $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                             $q2->orWhere('entry_type','-1');
                                          });
                                       }], 'debit');
                                          $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                             $q1->where('company_id', '=', Session::get('user_company_id'));
                                             $q1->where('status','1');
                                             $q1->where('delete_status','0');
                                             $q1->where(function($q2)use ($to_date){
                                                $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                $q2->orWhere('entry_type','-1');
                                             });
                                          }], 'credit');
                                    }]);
                                    $qa->with(['accountUnderGroup'=>function($qb)use($to_date){
                                       $qb->select('id','name','heading','heading_type');
                                       $qb->where('status','1');
                                       $qb->where('delete','0');
                                       $qb->with(['account'=>function($query)use($to_date){
                                          $query->select('id','account_name','under_group');
                                          $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                             $q1->where('company_id', '=', Session::get('user_company_id'));
                                             $q1->where('status','1');
                                             $q1->where('delete_status','0');
                                             $q1->where(function($q2)use ($to_date){
                                                $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                $q2->orWhere('entry_type','-1');
                                             });
                                          }], 'debit');
                                             $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                                $q1->where('company_id', '=', Session::get('user_company_id'));
                                                $q1->where('status','1');
                                                $q1->where('delete_status','0');
                                                $q1->where(function($q2)use ($to_date){
                                                   $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                   $q2->orWhere('entry_type','-1');
                                                });
                                             }], 'credit');
                                       }]);
                                       $qb->with(['accountUnderGroup'=>function($qc)use($to_date){
                                          $qc->select('id','name','heading','heading_type');
                                          $qc->where('status','1');
                                          $qc->where('delete','0');
                                          $qc->with(['account'=>function($query)use($to_date){
                                             $query->select('id','account_name','under_group');
                                             $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                                $q1->where('company_id', '=', Session::get('user_company_id'));
                                                $q1->where('status','1');
                                                $q1->where('delete_status','0');
                                                $q1->where(function($q2)use ($to_date){
                                                   $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                   $q2->orWhere('entry_type','-1');
                                                });
                                             }], 'debit');
                                                $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                                   $q1->where('company_id', '=', Session::get('user_company_id'));
                                                   $q1->where('status','1');
                                                   $q1->where('delete_status','0');
                                                   $q1->where(function($q2)use ($to_date){
                                                      $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                      $q2->orWhere('entry_type','-1');
                                                   });
                                                }], 'credit');
                                          }]);
                                          $qc->with(['accountUnderGroup'=>function($qd)use($to_date){
                                             $qd->select('id','name','heading','heading_type');
                                             $qd->where('status','1');
                                             $qd->where('delete','0');
                                             $qd->with(['account'=>function($query)use($to_date){
                                                $query->select('id','account_name','under_group');
                                                $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                                   $q1->where('company_id', '=', Session::get('user_company_id'));
                                                   $q1->where('status','1');
                                                   $q1->where('delete_status','0');
                                                   $q1->where(function($q2)use ($to_date){
                                                      $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                      $q2->orWhere('entry_type','-1');
                                                   });
                                                }], 'debit');
                                                   $query->withSum(['accountLedger'=>function($q1)use($to_date){
                                                      $q1->where('company_id', '=', Session::get('user_company_id'));
                                                      $q1->where('status','1');
                                                      $q1->where('delete_status','0');
                                                      $q1->where(function($q2)use ($to_date){
                                                         $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                                         $q2->orWhere('entry_type','-1');
                                                      });
                                                   }], 'credit');
                                             }]);
                                          }]);
                                       }]);
                                    }]);
                                 }]);
                              }
                           ])
                        ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                        ->where('heading',$id)
                        ->where('status','1')
                        ->where('delete','0')
                        ->select('id','name','stock_in_hand','heading_type')
                        ->where('heading_type','head')
                        //->orWhere('heading_type','=','')
                        ->orderBy('name')
                        ->get();                              
      $head_account = Accounts::withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where(function($q1) use ($to_date,$from_date,$financial_year){
                                // $q1->where('financial_year', $financial_year);
                                 $q1->where('delete_status','0');
                                 $q1->where('company_id',Session::get('user_company_id'));
                              })->where(function($q2) use ($to_date,$from_date){
                                 $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                 $q2->orWhere('entry_type','-1');
                              });                                 
                            }], 'debit')
                           ->withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where(function($q1) use ($to_date,$from_date,$financial_year){
                               //  $q1->where('financial_year', $financial_year);
                                 $q1->where('delete_status','0');
                                 $q1->where('company_id',Session::get('user_company_id'));
                              })->where(function($q2) use ($to_date,$from_date){
                                 $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                 $q2->orWhere('entry_type','-1');
                              });                                 
                            }], 'credit')
                           ->where('under_group',$id)
                           ->where('under_group_type','head')
                           ->where('accounts.delete','0')
                           ->where('accounts.status','1')                           
                           ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();
      //Closing Stock      
      $open_date = $y[0]."-04-01";
      $open_date = date('Y-m-d',strtotime($open_date));
      $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));
      
      $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2) || (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=-1) GROUP BY item_id"));
      foreach ($item_in_data as $key => $value) {
         $check = ItemLedger::select('id')
                     ->where('item_id',$value->item_id)
                     ->where('total_price',$value->total_price)
                     ->where('in_weight',$value->in_weight)
                     ->where('source','-1')
                     ->where('delete_status','0')
                     ->where('company_id',Session::get('user_company_id'))
                     ->first();
         if($check){
            $item_in_data[$key]->opening = 1;
         }else{
            $item_in_data[$key]->opening = 0;
         }
      }
      $result = array();
     
      foreach ($item_in_data as $element){
         if($element->opening==1){
          $result[$element->item_id][] = $element->total_price/$element->in_weight;
         }else{
            if(!empty($element->in_weight)){
               $result[$element->item_id][] = round($element->total_price/$element->in_weight,2);
            }else{
               $result[$element->item_id][] = 0;
            }
            
         }
      }
      $stock_in_hand = 0;$total_weight = 0;
      foreach ($item as $key => $value){
         $remaining_weight = $value->in_weight - $value->out_weight;
         if (array_key_exists($value->item_id,$result)){
            $stock_in_hand = $stock_in_hand + $remaining_weight*$result[$value->item_id][0];
            $total_weight = $total_weight + $remaining_weight;
         }
      }
      $stock_in_hand = CommonHelper::ClosingStock($to_date);
      $stock_in_hand = round($stock_in_hand,2);
      
      $baseQuery = DB::table('purchases')
    ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$to_date])
    ->where('company_id',Session::get('user_company_id'))
    ->whereDate('stock_entry_date', '>', $to_date)
    ->where('status', '1')
    ->where('delete', '0');

    $purchase_in_transit_ids = (clone $baseQuery)->pluck('id')->toArray();
    
    $stock_in_transit_value = (clone $baseQuery)
        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
        ->value('total');
    
    $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);

   $total_closing_stock = $stock_in_hand + $stock_in_transit_value;
   //   echo "<pre>";
   //   print_r($undergroup->toArray());
   //   echo "</pre>";
      return view('display/group_balance_by_head',["financial_year"=>$financial_year,"stock_in_transit_value"=>$stock_in_transit_value,"total_closing_stock"=>$total_closing_stock,"from_date"=>$from_date,"to_date"=>$to_date,"head"=>$head,"group"=>$group,"head_account"=>$head_account,"stock_in_hand"=>$stock_in_hand,"undergroup"=>$undergroup]);
   }
   public function accountBalanceByGroup(Request $request,$id,$from_date,$to_date,$type){
      $financial_year = Session::get('default_fy');
      
      if($type=="head"){
         $group = AccountHeading::select('name')
                              ->where('id',$id)
                               ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                              ->first();
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    //$q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'debit')
                              ->withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    //$q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'credit')
                              ->where('under_group',$id)
                              ->where('under_group_type',$type)
                              ->where('accounts.delete','0')
                              ->where('accounts.status','1')                           
                              ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
      }else if($type=="group"){         
         $group = AccountGroups::select('name')
                                 ->where('id',$id)
                                 ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                                 ->first();
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    //$q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'debit')
                              ->withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    //$q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'credit')
                              ->where('under_group',$id)
                              ->where('under_group_type',$type)
                              ->where('accounts.delete','0')
                              ->where('accounts.status','1')                           
                              ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
         //echo "<pre>";
        
         //Inner Group
         $inner_group = AccountGroups::select('id','name as account_name')
                                       ->where('heading',$id)
                                       ->where('delete','0')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type','group')
                                      ->get();
                                       // print_r($inner_group->toArray());
                                       //  echo "</pre>";
         foreach ($inner_group as $key => $value) {
            $account_id = Accounts::where('under_group',$value->id)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
                                    
            $sub_group = AccountGroups::where('heading',$value->id)
                                    ->where('heading_type',"group")
                                    ->pluck('id');
            $account_id1 = Accounts::whereIn('under_group',$sub_group)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');

            $sub_group2 = AccountGroups::whereIn('heading',$sub_group)
                                    ->where('heading_type',"group")
                                    ->pluck('id');
            $account_id2 = Accounts::whereIn('under_group',$sub_group2)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');

            $sub_group3 = AccountGroups::whereIn('heading',$sub_group2)
                                    ->where('heading_type',"group")
                                    ->pluck('id');
            $account_id3 = Accounts::whereIn('under_group',$sub_group3)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');

            $sub_group4 = AccountGroups::whereIn('heading',$sub_group3)
                                    ->where('heading_type',"group")
                                    ->pluck('id');
            $account_id4 = Accounts::whereIn('under_group',$sub_group4)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');


            $account_id = $account_id->merge($account_id1);
            $account_id = $account_id->merge($account_id2);
            $account_id = $account_id->merge($account_id3);
            $account_id = $account_id->merge($account_id4);
            $debit_sum = AccountLedger::whereIn('account_id',$account_id)
                           //->where('financial_year',$financial_year)
                           ->where('delete_status','0')
                           ->where('txn_date', '<=', $to_date)
                           ->where('delete_status','0')
                           ->orWhere(function($query)use($account_id) {
                              $query->whereIn('account_id',$account_id)
                              ->Where('entry_type','-1');
                           })
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                        ->sum('debit');
            $credit_sum = AccountLedger::whereIn('account_id',$account_id)
                        //->where('financial_year',$financial_year)
                        ->where('delete_status','0')
                        ->where('txn_date', '<=', $to_date)
                        ->where('delete_status','0')
                        ->orWhere(function($query)use($account_id) {
                           $query->whereIn('account_id',$account_id)
                           ->Where('entry_type','-1');
                        })
                        ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->sum('credit');
                                 
            $inner_group[$key]->account_ledger_sum_debit = $debit_sum;         
            $inner_group[$key]->account_ledger_sum_credit = round($credit_sum,2);                     
            $inner_group[$key]->type = 1;
         }
         $account = $account->merge($inner_group);
      }
      return view('display/account_balance_by_group_bs')->with('data',$account)->with('group',$group)->with('financial_year',$financial_year)->with('type',$type)->with('from_date',$from_date)->with('to_date',$to_date);
   }

   public function balanceSheetGroupMapping()
   {
      $com_id = Session::get('user_company_id');

      $company_info = Companies::find($com_id);

      $groups = AccountGroups::whereIn(
                     'company_id',
                     [$com_id,0]
                  )
                  ->where('delete','0')
                  ->get()
                  ->map(function($item){

                     $item->record_type = 'group';
                     $item->unique_key = 'group_'.$item->id;

                     return $item;
                  });

      $headings = AccountHeading::whereIn(
                     'company_id',
                     [$com_id,0]
                  )
                  ->where('delete','0')
                  ->get()
                  ->map(function($item){

                     $item->record_type = 'heading';
                     $item->unique_key = 'heading_'.$item->id;

                     return $item;
                  });

      $allGroups = AccountGroups::whereIn(
            'company_id',
            [$com_id,0]
         )
         ->where('delete','0')
         ->get();

      $groups = collect();

      foreach($headings->sortBy('name') as $heading)
      {
         $heading->record_type = 'heading';
         $heading->unique_key  = 'heading_'.$heading->id;
         $heading->level       = 0;

         $groups->push($heading);

         $this->buildGroupTree(
            $heading->id,
            'head',
            $allGroups,
            $groups,
            1
         );
      }

      $mappings = BalanceSheetGroupMapping::where(
         'company_id',
         $com_id
      )
      ->get()
      ->mapWithKeys(function($row){

         return [
            $row->record_type.'_'.$row->group_id
            => [
                  'mapping_name' => $row->mapping_name
            ]
         ];

      })
      ->toArray();

      $balanceSheetOptions = [];

      if($company_info->business_type == 3)
      {
         $balanceSheetOptions[] = 'Share capital';
         $balanceSheetOptions[] = 'Reserves and surplus';
      }
      elseif($company_info->business_type == 2)
      {
         $balanceSheetOptions[] = "Partner's capital account";
         $balanceSheetOptions[] = 'Profit and loss account';
      }
      elseif($company_info->business_type == 1)
      {
         $balanceSheetOptions[] = "Proprietor's capital account";
         $balanceSheetOptions[] = 'Profit and loss account';
      }

      $balanceSheetOptions = array_merge(
         $balanceSheetOptions,
         [

               'Long-term borrowings',
               'Deferred tax liabilities (Net)',
               'Other long term liabilities',
               'Long-term provisions',

               'Short-term borrowings',
               'Trade payables',
               'Other current liabilities',
               'Short-term provisions',

               'Property, Plant and Equipment',
               'Intangible assets',
               'Capital work-in-progress',
               'Intangible assets under development',

               'Non-current investments',
               'Deferred tax assets (Net)',
               'Long-term loans and advances',
               'Other non-current assets',

               'Current investments',
               'Inventories',
               'Trade receivables',
               'Cash and cash equivalents',
               'Short-term loans and advances',
               'Other current assets'
         ]
      );

      return view(
         'display.balance_sheet_group_mapping',
         compact(
               'groups',
               'mappings',
               'balanceSheetOptions',
               'company_info'
         )
      );
   }
   public function saveBalanceSheetGroupMapping(Request $request)
   {
      $companyId = Session::get('user_company_id');
      
      foreach($request->mapping ?? [] as $key => $mappingName)
      {
         $parts = explode('_', $key, 2);

         $recordType = $parts[0];
         $recordId   = $parts[1];


         if(empty($mappingName))
         {
               BalanceSheetGroupMapping::where('company_id',$companyId)
                  ->where('record_type',$recordType)
                  ->where('group_id',$recordId)
                  ->delete();

               continue;
         }

         BalanceSheetGroupMapping::updateOrCreate(

         [
            'company_id'  => $companyId,
            'record_type' => $recordType,
            'group_id'    => $recordId
         ],

         [
            'mapping_name' => $mappingName
         ]
         );
      }

      return redirect()->back()
               ->withSuccess('Mapping Saved Successfully');
   }
public function tradePayableAccountMapping()
{
    $companyId = Session::get('user_company_id');

    // Get Trade Payables Heading ID
    $tradeHeadingId = BalanceSheetGroupMapping::where('company_id', $companyId)
        ->where('record_type', 'heading')
        ->where('mapping_name', 'Trade payables')
        ->value('group_id');

    // Get Groups mapped under Trade Payables
    $tradePayableGroupIds = BalanceSheetGroupMapping::where('company_id', $companyId)
        ->where('record_type', 'group')
        ->where('mapping_name', 'Trade payables')
        ->pluck('group_id')
        ->toArray();

    $accounts = Accounts::select(
            'accounts.id',
            'accounts.account_name',
            'accounts.under_group',
            'accounts.under_group_type',
            'account_groups.name as group_name'
        )
        ->leftJoin(
            'account_groups',
            'account_groups.id',
            '=',
            'accounts.under_group'
        )
        ->where('accounts.company_id', $companyId)
        ->where('accounts.delete', '0')
        ->where(function ($query) use ($tradeHeadingId, $tradePayableGroupIds) {

            // Accounts directly under Trade Payables Heading
            if ($tradeHeadingId) {
                $query->where(function ($q) use ($tradeHeadingId) {
                    $q->where('accounts.under_group_type', 'head')
                      ->where('accounts.under_group', $tradeHeadingId);
                });
            }

            // Accounts under Groups mapped to Trade Payables
            if (!empty($tradePayableGroupIds)) {
                $query->orWhere(function ($q) use ($tradePayableGroupIds) {
                    $q->where('accounts.under_group_type', 'group')
                      ->whereIn('accounts.under_group', $tradePayableGroupIds);
                });
            }
        })
        ->orderBy('accounts.account_name')
        ->get();
$accounts->transform(function ($account) {

    if ($account->under_group_type == 'head') {
        $account->group_name = 'Trade Payables';
    }

    return $account;
});
    $existingMappings = TradePayableAccountMapping::where('company_id', $companyId)
        ->pluck('trade_payable_type', 'account_id')
        ->toArray();

    return view(
        'display.trade_payable_account_mapping',
        compact(
            'accounts',
            'existingMappings'
        )
    );
}
   public function saveTradePayableAccountMapping(Request $request)
   {
      $companyId = Session::get('user_company_id');

      foreach($request->trade_type ?? [] as $accountId => $type)
      {
         if(empty($type))
         {
               continue;
         }

         TradePayableAccountMapping::updateOrCreate(

               [
                  'company_id' => $companyId,
                  'account_id' => $accountId
               ],

               [
                  'trade_payable_type' => $type
               ]
         );
      }

      return redirect()
         ->back()
         ->withSuccess(
               'Trade Payable Mapping Saved Successfully'
         );
   }
   private function buildGroupTree($parentId, $parentType, $allGroups, &$rows, $level = 0)
   {
      $children = $allGroups
         ->where('heading', $parentId)
         ->where('heading_type', $parentType)
         ->sortBy('name');

      foreach ($children as $child)
      {
         $child->record_type = 'group';
         $child->unique_key  = 'group_'.$child->id;
         $child->level       = $level;

         $rows->push($child);

         $this->buildGroupTree(
               $child->id,
               'group',
               $allGroups,
               $rows,
               $level + 1
         );
      }
   }
/**
 * Vertical Balance Sheet — Drill Down Page
 * URL: /vertical-bs-drilldown?mapping_name=Inventories&from_date=...&to_date=...
 */
public function verticalDrillDown(Request $request)
{
    $companyId    = Session::get('user_company_id');
    $mappingName  = $request->mapping_name;
    $liabilityMappings = [
    'Share capital',
    'Reserves and surplus',
    "Partner's capital account",
    'Profit and loss account',
    "Proprietor's capital account",
    'Long-term borrowings',
    'Deferred tax liabilities (Net)',
    'Other long term liabilities',
    'Long-term provisions',
    'Short-term borrowings',
    'Trade payables',
    'Other current liabilities',
    'Short-term provisions',
];

$isLiabilitySection = in_array($mappingName, $liabilityMappings)
    || $mappingName == 'Trade payables (A) Micro enterprises and small enterprises'
    || $mappingName == 'Trade payables (B) Others';
$financialYear = Session::get('default_fy');

[$startYY, $endYY] = explode('-', $financialYear);

$fromDate = '20' . $startYY . '-04-01';
$toDate   = '20' . $endYY . '-03-31';
    // Load mapping rows for this specific mapping_name
// Handle Trade Payables (A) & (B) separately
if (
    $mappingName == 'Trade payables (A) Micro enterprises and small enterprises' ||
    $mappingName == 'Trade payables (B) Others'
) {

    $tradeType = str_contains($mappingName, '(A)') ? 'A' : 'B';

    $tradeAccountIds = TradePayableAccountMapping::where('company_id', $companyId)
        ->where('trade_payable_type', $tradeType)
        ->pluck('account_id');

    $mappingRows = collect();

} else {

    $mappingRows = BalanceSheetGroupMapping::where('company_id', $companyId)
        ->where('mapping_name', $mappingName)
        ->get();
}
    // Preload accounts
    $allAccounts = Accounts::select('id', 'account_name', 'under_group', 'under_group_type')
        ->whereIn('company_id', [$companyId, 0])
        ->where('status', '1')
        ->where('delete', '0')
        ->get();

    // Preload ledger sums
    $verticalLedgerSums = DB::table('account_ledger')
    ->selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
    ->where('company_id', $companyId)
    ->where('status', '1')
    ->where('delete_status', '0')
    ->where(function ($q) use ($fromDate, $toDate) {

        $q->where(function ($q1) use ($fromDate, $toDate) {

            $q1->whereBetween('txn_date', [
                $fromDate,
                $toDate
            ]);

        })
        ->orWhere('entry_type', '-1');

    })
    ->groupBy('account_id')
    ->get()
    ->keyBy('account_id');

    // Stock in hand group
    $stockInHandGroup = AccountGroups::select('id')
        ->where('stock_in_hand', '1')
        ->whereIn('company_id', [$companyId, 0])
        ->where('status', '1')
        ->where('delete', '0')
        ->first();

    $stock_in_hand = \App\Helpers\CommonHelper::ClosingStock($toDate);
    $stock_in_hand = round($stock_in_hand, 2);

    // In-transit stock
    $stockInTransit = DB::table('purchases')
        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$toDate])
        ->whereDate('stock_entry_date', '>', $toDate)
        ->where('company_id', $companyId)
        ->where('status', '1')
        ->where('delete', '0')
        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
        ->value('total');

    $stock_in_hand += round($stockInTransit ?? 0, 2);

    // Build drill-down sections
    $sections = [];
if (
    $mappingName == 'Trade payables (A) Micro enterprises and small enterprises' ||
    $mappingName == 'Trade payables (B) Others'
) {

    $accounts = $allAccounts->whereIn('id', $tradeAccountIds);

    $accountDetails = [];
    $sectionDebit = 0;
    $sectionCredit = 0;

    foreach ($accounts as $acc) {

        $d = $verticalLedgerSums[$acc->id]->debit ?? 0;
        $c = $verticalLedgerSums[$acc->id]->credit ?? 0;

        $sectionDebit += $d;
        $sectionCredit += $c;

        $accountDetails[] = [
            'id' => $acc->id,
            'account_name' => $acc->account_name,
            'debit' => round($d, 2),
            'credit' => round($c, 2),
            'balance' => round($c - $d, 2),
        ];
    }

    $sections[] = [
        'record_type' => 'Trade Payable',
        'group_id' => 0,
        'label' => $mappingName,
        'accounts' => $accountDetails,
        'stock_adj' => 0,
        'total_debit' => round($sectionDebit, 2),
        'total_credit' => round($sectionCredit, 2),
        'balance' => round($sectionCredit - $sectionDebit, 2),
    ];
}
    foreach ($mappingRows as $mapRow) {

        $recordType = $mapRow->record_type;
        $recordId   = (int) $mapRow->group_id;

        if ($recordType === 'heading') {
            $parentLabel = AccountHeading::where('id', $recordId)->value('name');
$parentLabel = $parentLabel ? $parentLabel : 'Heading '.$recordId;
            $accounts = $allAccounts->filter(function($acc) use ($recordId) {
    return $acc->under_group_type === 'head' &&
           (int)$acc->under_group === $recordId;
});
        } else {
            $parentLabel = AccountGroups::where('id', $recordId)->value('name');
$parentLabel = $parentLabel ? $parentLabel : 'Group '.$recordId;
            $accounts = $allAccounts->filter(function($acc) use ($recordId) {
    return $acc->under_group_type === 'group' &&
           (int)$acc->under_group === $recordId;
});
        }

        $accountDetails = [];
        $sectionDebit   = 0;
        $sectionCredit  = 0;

        foreach ($accounts as $acc) {
            $d = $verticalLedgerSums[$acc->id]->debit ?? 0;
$c = $verticalLedgerSums[$acc->id]->credit ?? 0;
            $sectionDebit  += $d;
            $sectionCredit += $c;
            $accountDetails[] = [
                'id'           => $acc->id,
                'account_name' => $acc->account_name,
                'debit'        => round($d, 2),
                'credit'       => round($c, 2),
                'balance'      => round($isLiabilitySection ? ($c - $d) : ($d - $c), 2),
            ];
        }

        // stock adjustment
        $stockAdj = 0;
        if ($recordType === 'group' && $stockInHandGroup && $recordId === (int)$stockInHandGroup->id) {
            $stockAdj       = $stock_in_hand;
            $sectionDebit  += $stock_in_hand;
        }

        $sections[] = [
            'record_type'  => $recordType,
            'group_id'     => $recordId,
            'label'        => $parentLabel,
            'accounts'     => $accountDetails,
            'stock_adj'    => $stockAdj,
            'total_debit'  => round($sectionDebit, 2),
            'total_credit' => round($sectionCredit, 2),
            'balance'      => round($isLiabilitySection ? ($sectionCredit - $sectionDebit) : ($sectionDebit - $sectionCredit), 2),
        ];
    }

    $grandTotal = round(array_sum(array_column($sections, 'balance')), 2);

    return view('display.vertical_bs_drilldown', compact(
        'mappingName',
        'sections',
        'grandTotal',
        'fromDate',
        'toDate'
    ));
}
private function getVerticalBalances($fromDate, $toDate, $companyId, $allAccounts, $heads, $stockInHandGroups)
{
    // Stock in hand for THIS specific date range (uses your existing helper, unchanged)
    $stock_in_hand = CommonHelper::ClosingStock($toDate);
    $stock_in_hand = round($stock_in_hand, 2);

    $baseQuery = DB::table('purchases')
        ->whereRaw("STR_TO_DATE(date, '%Y-%m-%d') <= ?", [$toDate])
        ->whereDate('stock_entry_date', '>', $toDate)
        ->where('company_id', $companyId)
        ->where('status', '1')
        ->where('delete', '0');

    $stock_in_transit_value = (clone $baseQuery)
        ->selectRaw("SUM(CAST(taxable_amt AS DECIMAL(15,2))) as total")
        ->value('total');

    $stock_in_transit_value = round($stock_in_transit_value ?? 0, 2);
    $stock_in_hand = $stock_in_hand + $stock_in_transit_value;

    // Ledger sums for this date range
    $verticalLedgerSums = DB::table('account_ledger')
        ->selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
        ->where('company_id', $companyId)
        ->where('status', '1')
        ->where('delete_status', '0')
        ->where(function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('txn_date', [$fromDate, $toDate])
              ->orWhere('entry_type', '-1');
        })
        ->groupBy('account_id')
        ->get()
        ->keyBy('account_id');

    $allMappingRows = BalanceSheetGroupMapping::where('company_id', $companyId)->get();

    $verticalBalances   = [];
    $verticalMappedKeys = [];
// These mapping names belong to EQUITY AND LIABILITIES.
// For these, the correct accounting sign is Credit - Debit.
// Everything else (ASSETS side) keeps Debit - Credit.
$liabilityMappings = [
    'Share capital',
    'Reserves and surplus',
    "Partner's capital account",
    'Profit and loss account',
    "Proprietor's capital account",
    'Long-term borrowings',
    'Deferred tax liabilities (Net)',
    'Other long term liabilities',
    'Long-term provisions',
    'Short-term borrowings',
    'Trade payables',
    'Other current liabilities',
    'Short-term provisions',
];
    foreach ($allMappingRows as $mapRow) {

        $mappingName = $mapRow->mapping_name;
        $recordType  = $mapRow->record_type;
        $recordId    = (int) $mapRow->group_id;

        if ($recordType === 'heading') {
            $accountIds = $allAccounts
                ->filter(function ($acc) use ($recordId) {
                    return $acc->under_group_type === 'head' &&
                           (int)$acc->under_group === $recordId;
                })
                ->pluck('id')
                ->toArray();

            $headRecord = $heads->firstWhere('id', $recordId);
            $label = $headRecord ? $headRecord->name : 'Heading '.$recordId;

        } else { // 'group'
            $accountIds = $allAccounts
                ->filter(function ($acc) use ($recordId) {
                    return $acc->under_group_type === 'group' &&
                           (int)$acc->under_group === $recordId;
                })
                ->pluck('id')
                ->toArray();

            $grp = AccountGroups::select('name')->where('id', $recordId)->first();
            $label = $grp ? $grp->name : 'Group '.$recordId;
        }

        $debit  = 0;
        $credit = 0;

        foreach ($accountIds as $accId) {
            if (isset($verticalLedgerSums[$accId])) {
                $debit  += $verticalLedgerSums[$accId]->debit;
                $credit += $verticalLedgerSums[$accId]->credit;
            }
        }

        if (
            $recordType === 'group' &&
            $stockInHandGroups &&
            $recordId === (int)$stockInHandGroups->id
        ) {
            $debit += $stock_in_hand;
        }

        if (in_array($mappingName, $liabilityMappings)) {
    // Liabilities & Equity: Credit - Debit
    $rowBalance = round($credit - $debit, 2);
} else {
    // Assets: Debit - Credit
    $rowBalance = round($debit - $credit, 2);
}

        if (!isset($verticalBalances[$mappingName])) {
            $verticalBalances[$mappingName]   = 0;
            $verticalMappedKeys[$mappingName] = [];
        }

        $verticalBalances[$mappingName] += $rowBalance;

        $verticalMappedKeys[$mappingName][] = [
            'record_type' => $recordType,
            'group_id'    => $recordId,
            'name'        => $label,
            'balance'     => $rowBalance,
            'account_ids' => $accountIds,
        ];
    }

    foreach ($verticalBalances as $k => $v) {
        $verticalBalances[$k] = round($v, 2);
    }

    /*
    |--------------------------------------------------------------
    | Trade Payables (A) Micro/Small enterprises  &  (B) Others
    | Split is based on TradePayableAccountMapping.trade_payable_type
    |--------------------------------------------------------------
    */
    $tradeTypeByAccount = TradePayableAccountMapping::where('company_id', $companyId)
        ->pluck('trade_payable_type', 'account_id');

    $tradeA = 0;
    $tradeB = 0;

    foreach (($verticalMappedKeys['Trade payables'] ?? []) as $detail) {
        foreach ($detail['account_ids'] as $accId) {
            $d = $verticalLedgerSums[$accId]->debit  ?? 0;
            $c = $verticalLedgerSums[$accId]->credit ?? 0;
            $bal = $c - $d;

            $type = $tradeTypeByAccount[$accId] ?? null;

            if ($type === 'A') {
                $tradeA += $bal;
            } elseif ($type === 'B') {
                $tradeB += $bal;
            }
        }
    }

    $verticalBalances['Trade payables (A)'] = round($tradeA, 2);
    $verticalBalances['Trade payables (B)'] = round($tradeB, 2);

    return [$verticalBalances, $verticalMappedKeys, $stock_in_hand];
}
}