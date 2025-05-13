<?php

namespace App\Http\Controllers\display;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\ItemLedger;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Helpers\CommonHelper;
use DB;
use Session;

class TrialBalanceController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(Request $request){      
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('Y')-1);
      }else{
         $current_year = date('Y');
      }
      $from_date = $current_year."-04-01";
      $to_date = date('Y-m-t');
      if($financial_year!=$current_year){
         $y =  explode("-",$financial_year);
         $from_date = $y[0]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = $y[1]."-03-31";
         $to_date = date('Y-m-d',strtotime($to_date));
      }
      $account = Accounts::select('id','account_name')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();
                           
      foreach ($account as $key => $value){
         $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1 )");
         $debit = $debit_credit[0]->debit;
         $credit = $debit_credit[0]->credit;
         $account[$key]->debit = $debit;
         $account[$key]->credit = $credit;
      }
      $stock_in_hand = CommonHelper::ClosingStock($to_date);
      $stock_in_hand = round($stock_in_hand,2);
      if($stock_in_hand<0){
         $newCompete = collect([
            [
               'id' => '',
               'account_name' => 'Stock In Hand',
               'debit' => '0',
               'credit' => $stock_in_hand
           ]
        ]);
      }else{
         $newCompete = collect([
            [
               'id' => '',
               'account_name' => 'Stock In Hand',
               'debit' => $stock_in_hand,
               'credit' => '0'
           ]
        ]);
      }      
      $account = $account
      ->concat($newCompete)
      ->sortBy('account_name');
      
      return view('display/trialbalance')->with('account', $account)->with('type','open')->with('to_date',$to_date);
   }
   public function filter(Request $request){     
      $req = $request->all();   
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('Y')-1);
      }else{
         $current_year = date('Y');
      }
      $from_date = $current_year."-04-01";
      $to_date = $req['to_date'];
      if($financial_year!=$current_year){
         $y =  explode("-",$financial_year);
         $from_date = $y[0]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         if($to_date==""){
            $to_date = $y[1]."-03-31";
            $to_date = date('Y-m-d',strtotime($to_date));
         }  
      }
      //Data By Group
      $account = AccountHeading::with(['accountWithHead'=>function($query){
                                    $query->select('id','account_name','under_group');
                                 },'accountGroup'=>function($q)use($from_date,$to_date){
                                    $q->select('id','name','heading');
                                    $q->with(['account'=>function($q1){
                                       $q1->select('id','account_name','under_group');
                                    }]);
                                 }])
                                 ->select('id','name')
                                 ->get();      
      foreach ($account as $key => $value) {
         if(count($value->accountWithHead)>0){
            $account_id = implode(',', $value->accountWithHead->pluck('id')->toArray());
            if($account_id!=""){                 
               if($req['type']=="open"){    
                  $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));             
                  $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
               }else if($req['type']=="close"){
                  
                  $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
               }
               $debit = $debit_credit[0]->debit;
               $credit = $debit_credit[0]->credit;
               $balance = $debit - $credit;
               if($balance>=0){
                  $account[$key]->debit = $balance;
                  $account[$key]->credit = 0;
               }else if($balance<0){
                  $account[$key]->debit = 0;
                  $account[$key]->credit = abs($balance);
               }
            }else{
               $account[$key]->debit = 0;
               $account[$key]->credit = 0;
            }
         }else{
            $account[$key]->debit = 0;
            $account[$key]->credit = 0;
         }
         if(count($value->accountGroup)>0){
            foreach ($value->accountGroup as $k2 => $v2) {   
               if($v2->id==30){                  
                  $stock_in_hand = CommonHelper::ClosingStock($to_date);
                  $stock_in_hand = round($stock_in_hand,2);
                  if($stock_in_hand<0){
                     $value->accountGroup[$k2]->debit = 0;
                     $value->accountGroup[$k2]->credit = $stock_in_hand;                     
                  }else{
                     $value->accountGroup[$k2]->debit = $stock_in_hand;
                     $value->accountGroup[$k2]->credit = 0;  
                  }
               }else{
                  $account_id = implode(',', $v2->account->pluck('id')->toArray());
                  if($account_id!=""){                 
                     if($req['type']=="open"){    
                        $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));                 
                        $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
                     }else if($req['type']=="close"){
                        
                        $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
                     }
                     $debit = $debit_credit[0]->debit;
                     $credit = $debit_credit[0]->credit;
                     $balance = $debit - $credit;
                     if($balance>=0){
                        $value->accountGroup[$k2]->debit = $balance;
                        $value->accountGroup[$k2]->credit = 0;
                     }else if($balance<0){
                        $value->accountGroup[$k2]->debit = 0;
                        $value->accountGroup[$k2]->credit = abs($balance);
                     }
                  }else{
                     $value->accountGroup[$k2]->debit = 0;
                     $value->accountGroup[$k2]->credit = 0;
                  }
               }           
               
            }
         }
      }
      $group_primary_yes = AccountGroups::with(['account'=>function($q1){
            $q1->select('id','account_name','under_group');
         }])
         ->select('id','name')
         ->where('primary','Yes')
         ->get();
      foreach ($group_primary_yes as $k2 => $v2) {               
         $account_id = implode(',', $v2->account->pluck('id')->toArray());
         if($account_id!=""){                 
            if($req['type']=="open"){  
               $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));               
               $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
            }else if($req['type']=="close"){
               
               $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
            }
            $debit = $debit_credit[0]->debit;
            $credit = $debit_credit[0]->credit;
            $balance = $debit - $credit;
            if($balance>=0){
               $group_primary_yes[$k2]->debit = $balance;
               $group_primary_yes[$k2]->credit = 0;
            }else if($balance<0){
               $group_primary_yes[$k2]->debit = 0;
               $group_primary_yes[$k2]->credit = abs($balance);
            }
         }else{
            $group_primary_yes[$k2]->debit = 0;
            $group_primary_yes[$k2]->credit = 0;
         }
      }
     
      if($request->trial_balance_by && $request->trial_balance_by=="by_group"){
         return view('display/trialbalance')->with('account', $account)->with('group_primary_yes', $group_primary_yes)->with('type',$req['type'])->with('to_date',$to_date);
      }
      //Data By Account
      if($request->group_id && !empty($request->group_id)){
         $account = Accounts::select('id','account_name')
                           ->where('delete','0')
                           ->where('under_group',$request->group_id)
                           ->where('under_group_type',$request->under)
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')->get();
      }else{
         $account = Accounts::select('id','account_name')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')->get();
      }
      foreach ($account as $key => $value) {         
         if($req['type']=="open"){
            $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
            $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
         }else if($req['type']=="close"){            
            $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
         }
         $debit = $debit_credit[0]->debit;
         $credit = $debit_credit[0]->credit;
         $account[$key]->debit = $debit;
         $account[$key]->credit = $credit;         
      }
      $stock_in_hand = CommonHelper::ClosingStock($to_date);
      $stock_in_hand = round($stock_in_hand,2);
      if($stock_in_hand<0){
         $newCompete = collect([
            [
               'id' => '',
               'account_name' => 'Stock In Hand',
               'debit' => '0',
               'credit' => $stock_in_hand
           ]
        ]);
      }else{
         $newCompete = collect([
            [
               'id' => '',
               'account_name' => 'Stock In Hand',
               'debit' => $stock_in_hand,
               'credit' => '0'
           ]
        ]);
      }
      $account = $account->sortBy('account_name');
      if(!$request->group_id){
         $account = $account->concat($newCompete)->sortBy('account_name');
      }
      
      return view('display/trialbalance')->with('account', $account)->with('type',$req['type'])->with('to_date',$to_date);
   }
}
