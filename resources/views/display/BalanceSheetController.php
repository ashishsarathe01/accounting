<?php

namespace App\Http\Controllers\display;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\Accounts;
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
      if(date('m')<=3){
         $current_year = (date('Y')-1);
      }else{
         $current_year = date('Y');
      }
      $from_date = $current_year."-".date('m')."-01";
      $to_date = $current_year."-".date('m-t');
      $liability = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q2) use ($to_date){
                                       $q2->where('txn_date', '<=', $to_date);
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q6) use ($to_date){
                                       $q6->where('txn_date', '<=', $to_date);
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
                                    })->where(function($q2) use ($to_date){
                                       $q2->where('txn_date', '<=', $to_date);
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q6) use ($to_date){
                                       $q6->where('txn_date', '<=', $to_date);
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
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date]);
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
                                    })->where(function($q2) use ($to_date){
                                       $q2->where('txn_date', '<=', $to_date);
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q6) use ($to_date){
                                       $q6->where('txn_date', '<=', $to_date);
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
                                    })->where(function($q2) use ($to_date){
                                       $q2->where('txn_date', '<=', $to_date);
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q6) use ($to_date){
                                       $q6->where('txn_date', '<=', $to_date);
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
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date]);
   }
   public function groupBalanceByHead($id,$from_date,$to_date){
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
                           ->where('heading',$id)
                           ->where('status','1')
                           ->where('delete','0')
                           ->where('heading_type','head')
                           ->orWhere('heading_type','=','')
                           ->orderBy('name')
                           ->get();
                           
      $heading = AccountHeading::with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                    })->where(function($q6) use ($to_date){
                                       $q6->where('txn_date', '<=', $to_date);
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead'=> function($q1){
                                 $q1->whereIn('company_id',[Session::get('user_company_id',0)]);
                              }
                           ])
                           ->where('id',$id) 
                           ->where('status','1')
                           ->where('delete','0')                             
                           ->orderBy('name')
                           ->get();
      return view('display/group_balance_by_head',["from_date"=>$from_date,"to_date"=>$to_date,"head"=>$head,"group"=>$group,"heading"=>$heading]);
   }
   public function accountBalanceByGroup(Request $request,$id,$from_date,$to_date,$type){
      $financial_year = Session::get('default_fy');
      $group = AccountGroups::select('name')
                              ->where('id',$id)
                              ->first();
      if($type=="head"){
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
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
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
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
         // $inner_group = AccountGroups::select('id','name as account_name')
         //                               ->where('heading',$id)
         //                               ->whereIn('company_id',[Session::get('user_company_id'),0])
         //                               ->where('heading_type','group')
         //                              ->get();

      }
      // echo "<pre>";
      // print_r($account->toArray());die;
      //$account = $account->merge($account_group);



                          
      // foreach ($account_group as $key => $value) {
      //    $account_id = Accounts::where('under_group',$value->id)->where('accounts.delete','0')->whereIn('accounts.company_id',[Session::get('user_company_id'),0])->pluck('id');
      //    $leger = AccountLedger::whereIn('account_id',$account_id)
      //                   ->where('financial_year',$financial_year)
      //                   ->where('delete_status','0')
      //                   ->where('txn_date', '<=', $to_date)
      //                   ->where('delete_status','0')
      //                   ->whereIn('company_id',[Session::get('user_company_id'),0])
      //                   ->sum($type);
      //    if($type=="debit"){
      //       $account_group[$key]->account_ledger_sum_debit = $leger;
      //    }else{
      //       $account_group[$key]->account_ledger_sum_credit = $leger;
      //    }         
      //    $account_group[$key]->type = 1;
      // }
      // $account = Accounts::withSum([
      //                       'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
      //                         $query->where('financial_year', $financial_year);
      //                         $query->where('txn_date', '<=', $to_date);
      //                         $query->orWhere('entry_type','-1');
      //                         $query->where('delete_status','0');
      //                       }], $type)
      //                      ->where('under_group',$id)
      //                      ->where('accounts.delete','0')                           
      //                      ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
      //                      ->orderBy('account_name')
      //                      ->get();
      // $account = $account->merge($account_group);           
      return view('display/account_balance_by_group')->with('data',$account)->with('group',$group)->with('financial_year',$financial_year)->with('type',$type)->with('from_date',$from_date)->with('to_date',$to_date);
   }
}