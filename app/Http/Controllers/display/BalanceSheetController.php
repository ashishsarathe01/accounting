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
        return view('display/balanceSheet',[
            'heads'=>$heads,
            'from_date'=>$fromDate,
            'to_date'=>$toDate,
            'current_journal_amount'=>$current_journal_amount,
            'profit_loss_amount'=>$profit_loss_amount,
            'prev_year_profitloss'=>$prev_year_profitloss,
            'prev_year_profit_status'=>$prev_year_profit_status,
            'prevFy'=>$prevFy,
            'stock_in_hand'=>$stock_in_hand
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
                                 $q1->where('financial_year', $financial_year);
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
                                 $q1->where('financial_year', $financial_year);
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
}