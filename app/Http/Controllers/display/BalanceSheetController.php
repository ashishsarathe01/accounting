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
use Illuminate\Support\Facades\Validator;
use Session;
class BalanceSheetController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);

      $from_date = date('Y-')."04-01";
      $to_date = date('Y-m-t');      
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $from_date = $y[1]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = $y[1]."-03-31";
         $to_date = date('Y-m-d',strtotime($to_date));
      }
      // if(date('m')<=3){
      //    $current_year = (date('Y')-1);
      // }else{
      //    $current_year = date('Y');
      // }
      // $from_date = $current_year."-".date('m')."-01";
      // $to_date = $current_year."-".date('m-t');
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
      // print_r($assets->toArray());die;
      //Closing Stock      
      $stock_in_hand = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where(function($query) use ($to_date){
                     //$query->where('txn_date','<=',$to_date);
                     $query->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                     $query->orWhere('source','=','-1');
                  })->sum('total_price');
      $item_account = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','!=','-1')
                  //->where('txn_date','<=',$to_date)
                  ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                  ->orderBy('txn_date')
                  ->get();
      $sale = $item_account->sum('out_weight');
      $purchase = $item_account->sum('in_weight');
      $item_balance = $purchase - $sale;
      if($item_balance>0){
         $weight = 0;$price_arr = [];
         foreach ($item_account as $key => $value) {
            if($item_balance>$weight){
               array_push($price_arr,$value['price']);
            }else{
               break;
            }
            $weight = $weight + $value['in_weight'];
         }         
         $price_arr = array_filter($price_arr);
         $average = array_sum($price_arr)/count($price_arr);
         $average = round($average,2);
         $stock_in_hand = $stock_in_hand + ($item_balance*$average);
      }
      $profitloss = 0;
      $check_stock = ClosingStock::where('status',1)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->whereRaw("STR_TO_DATE(to_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                                 ->orderBy('id','desc')
                                 ->first();
      if($check_stock){
         //$profitloss = $check_stock->closing_amount;
      }
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss]);
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
                     // print_r($assets->toArray());die;
      //Closing Stock      
      $stock_in_hand = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where(function($query) use ($to_date){
                     $query->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                     $query->Where('source','=','-1');
                  })->sum('total_price');
      $item_account = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','!=','-1')
                  ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                  ->orderBy('txn_date')
                  ->get();
      $sale = $item_account->sum('out_weight');
      $purchase = $item_account->sum('in_weight');
      $item_balance = $purchase - $sale;
      if($item_balance>0){
         $weight = 0;$price_arr = [];
         foreach ($item_account as $key => $value) {
            if($item_balance>$weight){
               array_push($price_arr,$value['price']);
            }else{
               break;
            }
            $weight = $weight + $value['in_weight'];
         }         
         $price_arr = array_filter($price_arr);
         $average = array_sum($price_arr)/count($price_arr);
         $average = round($average,2);
         $stock_in_hand = $stock_in_hand + ($item_balance*$average);
      }
      $profitloss = 0;
      $check_stock = ClosingStock::where('status',1)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->whereRaw("STR_TO_DATE(to_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                                 ->orderBy('id','desc')
                                 ->first();
      if($check_stock){
         $profitloss = $check_stock->closing_amount;
      }
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss]);
   }
   public function groupBalanceByHead($id,$from_date,$to_date){
      $financial_year = Session::get('default_fy');
      $head = AccountHeading::select('name')
                              ->where('id',$id)
                              ->first();                              
      $group = AccountGroups::with(['account.accountLedger'=>function($q)use($to_date){
                                 $q->where(function($q1) use ($to_date){
                                    $q1->where('company_id', '=', Session::get('user_company_id'));
                                    $q1->where('status','1');
                                    $q1->where('delete_status','0'); 
                                 })->where(function($q6) use ($to_date){
                                    $q6->where('txn_date', '<=', $to_date);
                                    $q6->orWhere('entry_type','-1');
                                 });
                              }
                              ])->with(['account'=> function($q2){
                                 $q2->whereIn('company_id',[Session::get('user_company_id',0)]);
                                 $q2->where('status','1');
                                 $q2->where('delete','0');
                                 //$q2->orWhere('company_id',0);
                              }
                           ])
                           ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                           ->where('heading',$id)
                           ->where('status','1')
                           ->where('delete','0')
                           ->where('heading_type','head')
                           ->orWhere('heading_type','=','')
                           ->orderBy('name')
                           ->get();   
      $head_account = Accounts::withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where(function($q1) use ($to_date,$financial_year){
                                 $q1->where('financial_year', $financial_year);
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
                                 $q1->where('financial_year', $financial_year);
                                 $q1->where('delete_status','0');
                                 $q1->where('company_id',Session::get('user_company_id'));
                              })->where(function($q2) use ($to_date){
                                 $q2->where('txn_date', '<=', $to_date);
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
                                              
      // $heading = AccountHeading::with(['accountWithHead.accountLedger' => function($q4)use($to_date){
      //                               $q4->where(function($q5) use ($to_date){
      //                                  $q5->where('company_id', '=', Session::get('user_company_id'));
      //                               })->where(function($q6) use ($to_date){
      //                                  $q6->where('txn_date', '<=', $to_date);
      //                                  $q6->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountWithHead'=> function($q1){
      //                            $q1->whereIn('company_id',[Session::get('user_company_id',0)]);
      //                         }
      //                      ])
      //                      ->whereIn('company_id',[Session::get('user_company_id'),0])
      //                      ->where('id',$id) 
      //                      ->where('status','1')
      //                      ->where('delete','0')                             
      //                      ->orderBy('name')
      //                      ->get();
      //Closing Stock      
      $stock_in_hand = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where(function($query) use ($to_date){
                     $query->where('txn_date','<=',$to_date);
                     $query->Where('source','=','-1');
                  })->sum('total_price');
      $item_account = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','!=','-1')
                  ->where('txn_date','<=',$to_date)
                  ->orderBy('txn_date')
                  ->get();
      $sale = $item_account->sum('out_weight');
      $purchase = $item_account->sum('in_weight');
      $item_balance = $purchase - $sale;
      if($item_balance>0){
         $weight = 0;$price_arr = [];
         foreach ($item_account as $key => $value) {
            if($item_balance>$weight){
               array_push($price_arr,$value['price']);
            }else{
               break;
            }
            $weight = $weight + $value['in_weight'];
         }         
         $price_arr = array_filter($price_arr);
         $average = array_sum($price_arr)/count($price_arr);
         $average = round($average,2);
         $stock_in_hand = $stock_in_hand + ($item_balance*$average);
      }
      return view('display/group_balance_by_head',["from_date"=>$from_date,"to_date"=>$to_date,"head"=>$head,"group"=>$group,"head_account"=>$head_account,"stock_in_hand"=>$stock_in_hand]);
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
                                    $q1->where('financial_year', $financial_year);
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
                                    $q1->where('financial_year', $financial_year);
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
                                    $q1->where('financial_year', $financial_year);
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
                                    $q1->where('financial_year', $financial_year);
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
         //Inner Group
         $inner_group = AccountGroups::select('id','name as account_name')
                                       ->where('heading',$id)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type','group')
                                      ->get();
         foreach ($inner_group as $key => $value) {
            $account_id = Accounts::where('under_group',$value->id)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
                                    
                                    
            $leger = AccountLedger::whereIn('account_id',$account_id)
                           ->where('financial_year',$financial_year)
                           ->where('delete_status','0')
                           ->where('txn_date', '<=', $to_date)
                           ->where('delete_status','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->get();                       
            $inner_group[$key]->account_ledger_sum_debit = $leger->sum('debit');         
            $inner_group[$key]->account_ledger_sum_credit = $leger->sum('credit');                     
            $inner_group[$key]->type = 1;
         }
         //$account = $account->merge($account_group);
      }
      return view('display/account_balance_by_group')->with('data',$account)->with('group',$group)->with('financial_year',$financial_year)->with('type',$type)->with('from_date',$from_date)->with('to_date',$to_date);
   }
}