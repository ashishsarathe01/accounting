<?php

namespace App\Http\Controllers\display;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
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
      $account = Accounts::select('id','account_name')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();
                           
      foreach ($account as $key => $value){
         $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || entry_type=-1 )");
         
         $debit = $debit_credit[0]->debit;
         $credit = $debit_credit[0]->credit;
         $account[$key]->debit = $debit;
         $account[$key]->credit = $credit;
      }
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
      $account = Accounts::select('id','account_name')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')->get();
      
      foreach ($account as $key => $value) {         
         if($req['type']=="open"){
            $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || entry_type=-1 )");
         }else if($req['type']=="close"){
            $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id='".$value['id']."' and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || entry_type=-1)");
         }
         $debit = $debit_credit[0]->debit;
         $credit = $debit_credit[0]->credit;
         $account[$key]->debit = $debit;
         $account[$key]->credit = $credit;
         
      }
      return view('display/trialbalance')->with('account', $account)->with('type',$req['type'])->with('to_date',$to_date);
   }
}
