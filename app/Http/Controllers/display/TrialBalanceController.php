<?php

namespace App\Http\Controllers\display;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\ItemLedger;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\Journal;
use App\Helpers\CommonHelper;

use Carbon\Carbon;
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
        $req['type'] = "open";
        $account = Accounts::select('id','account_name','under_group','under_group_type')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();                           
                           foreach($account as $key => $value) {
                            // Step 1: Get the group info for this account
                            $isUnderHeading4 = false;
                            if($value->under_group==4 && $value->under_group_type=="head"){
                                $isUnderHeading4 = true;
                            }else{
                                $group = AccountGroups::select('id', 'heading','heading_type')
                                                    ->where('id', $value->under_group)
                                                    ->first();
                                if($group && $group->heading == 4 && $group->heading_type == 'head'){
                                    $isUnderHeading4 = true;
                                }else if($group->heading_type == 'group'){
                                    $inner_group1 = AccountGroups::select('id', 'heading','heading_type')
                                                    ->where('id', $group->heading)
                                                    ->first();
                                    if($inner_group1 && $inner_group1->heading == 4 && $inner_group1->heading_type == 'head'){
                                        $isUnderHeading4 = true;
                                    }else if($inner_group1->heading_type == 'group'){
                                        $inner_group2 = AccountGroups::select('id', 'heading','heading_type')
                                                    ->where('id', $inner_group1->heading)
                                                    ->first();
                                        if($inner_group2 && $inner_group2->heading == 4 && $inner_group2->heading_type == 'head'){
                                            $isUnderHeading4 = true;
                                        }else if($inner_group2->heading_type == 'group'){
                                            $inner_group3 = AccountGroups::select('id', 'heading','heading_type')
                                                    ->where('id', $inner_group2->heading)
                                                    ->first();
                                            if($inner_group3 && $inner_group3->heading == 4 && $inner_group3->heading_type == 'head'){
                                                $isUnderHeading4 = true;
                                            }else if($inner_group3->heading_type == 'group'){
                                                $inner_group4 = AccountGroups::select('id', 'heading','heading_type')
                                                    ->where('id', $inner_group3->heading)
                                                    ->first();
                                                if($inner_group4 && $inner_group4->heading == 4 && $inner_group4->heading_type == 'head'){
                                                    $isUnderHeading4 = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }                
                            // Step 2: Check if this account or its group is under heading 4
                            //$isUnderHeading4 = $group && $group->heading == 4;        
                            // Step 3: Now apply your logic
                            if($isUnderHeading4){
                                if($req['type'] == "open"){
                                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
                                    $debit_credit = DB::select("
                                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                                        FROM account_ledger 
                                        WHERE account_id = '".$value->id."' 
                                            AND status = '1' 
                                            AND delete_status = '0' 
                                            AND company_id = '".Session::get('user_company_id')."' 
                                            AND (
                                                STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE('".$from_date."', '%Y-%m-%d') 
                                                AND STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date1."', '%Y-%m-%d') 
                                                OR ( entry_type = -1  AND financial_year = '".$financial_year."')
                                            )
                                    ");
                                }
                            } else {
                                if($req['type'] == "open"){
                                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
                                    $debit_credit = DB::select("
                                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                                        FROM account_ledger 
                                        WHERE account_id = '".$value->id."' 
                                            AND status = '1' 
                                            AND delete_status = '0' 
                                            AND company_id = '".Session::get('user_company_id')."' 
                                            AND (
                                                STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date1."', '%Y-%m-%d') 
                                                OR entry_type = -1
                                            )
                                    "); 
                                }
                            }         
                            // Assigning debit/credit to account
                            $debit = $debit_credit[0]->debit ?? 0;
                            $credit = $debit_credit[0]->credit ?? 0;
                            $account[$key]->debit = $debit;
                            $account[$key]->credit = $credit;
                        } 
        $previous_date = Carbon::parse($from_date)->subDay(); 
        $stock_in_hand = CommonHelper::ClosingStock($previous_date);
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
         //Prevoius year profit & loss
      list($start, $end) = explode('-', $financial_year);
      $prevFy = str_pad($start - 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($end - 1, 2, '0', STR_PAD_LEFT);
      $prev_year_profitloss =  CommonHelper::profitLoss($prevFy);
      //Check Profit & Loss Account Entry
      $jouranl = Journal::select('id')
                     ->withSum(['journal_details' => function ($query) {
                        $query->where('id','!=','13319');
                     }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                     ->where('journals.financial_year',$prevFy)
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
        return view('display/trialbalance')->with('account', $account)->with('type','open')->with('to_date',$to_date)->with('prev_year_profitloss',$prev_year_profitloss)->with('prev_year_profit_status',$prev_year_profit_status)->with('prevFy',$prevFy);
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
                                    $query->select('id','account_name','under_group','under_group_type');
                                },'accountGroup'=>function($q)use($from_date,$to_date){
                                    $q->select('id','name','heading','heading_type');
                                    $q->with(['account'=>function($q1){
                                        $q1->select('id','account_name','under_group');
                                    }]);
                                }])
                                ->select('id','name')
                                ->get();      
        foreach ($account as $key => $value){
            if(count($value->accountWithHead)>0){
                $profit_loss_account = [];
                $balance_sheet_account = [];  
                foreach ($value->accountWithHead as $k3 => $v3){                                                  
                    if($v3->under_group==4 && $v3->under_group_type=='head'){
                        array_push($profit_loss_account,$v3->id);                                         
                    }else{
                        array_push($balance_sheet_account,$v3->id);   
                    }
                }
                if(count($balance_sheet_account)>0){   
                    $account_id = implode(',', $balance_sheet_account);     
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
                }else if(count($profit_loss_account)>0){
                    $account_id = implode(',', $profit_loss_account); 
                    if($req['type']=="open"){    
                        $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));             
                        $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."')) ");
                    }else if($req['type']=="close"){                  
                        $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$account_id.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."')) ");
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
                        $previous_date = Carbon::parse($from_date)->subDay();      
                        $stock_in_hand = CommonHelper::ClosingStock($previous_date);
                        $stock_in_hand = round($stock_in_hand,2);
                        if($stock_in_hand<0){
                            $value->accountGroup[$k2]->debit = 0;
                            $value->accountGroup[$k2]->credit = $stock_in_hand;                     
                        }else{
                            $value->accountGroup[$k2]->debit = $stock_in_hand;
                            $value->accountGroup[$k2]->credit = 0;  
                        }
                    }else{                       
                        $profit_loss_account = "";
                        $balance_sheet_account = "";                                     
                        if($v2->heading==4 && $v2->heading_type=='head'){
                            if(count($v2->account)>0){
                                $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                            }                        
                        }else{                            
                            if($v2->heading_type=='group'){
                                //Inner Group
                                $inner_group1 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $v2->heading)
                                    ->first();
                                if($inner_group1 && $inner_group1->heading == 4 && $inner_group1->heading_type == 'head'){
                                    if(count($v2->account)>0){
                                        $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                    }
                                }else if($inner_group1->heading_type == 'group'){
                                    $inner_group2 = AccountGroups::select('id', 'heading','heading_type')
                                                ->where('id', $inner_group1->heading)
                                                ->first();
                                    if($inner_group2 && $inner_group2->heading == 4 && $inner_group2->heading_type == 'head'){
                                        if(count($v2->account)>0){
                                            $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                        } 
                                    }else if($inner_group2->heading_type == 'group'){
                                        $inner_group3 = AccountGroups::select('id', 'heading','heading_type')
                                                ->where('id', $inner_group2->heading)
                                                ->first();
                                        if($inner_group3 && $inner_group3->heading == 4 && $inner_group3->heading_type == 'head'){
                                            if(count($v2->account)>0){
                                                $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                            } 
                                        }else if($inner_group3->heading_type == 'group'){
                                            $inner_group4 = AccountGroups::select('id', 'heading','heading_type')
                                                ->where('id', $inner_group3->heading)
                                                ->first();
                                            if($inner_group4 && $inner_group4->heading == 4 && $inner_group4->heading_type == 'head'){
                                                if(count($v2->account)>0){
                                                    $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                                } 
                                            }
                                        }
                                    }
                                }                                
                            }else{
                                $inner_group_account = [];
                                $inner_group1 = AccountGroups::select('id')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('heading', $v2->id)
                                            ->where('heading_type',"group")
                                            ->first();
                                if($inner_group1){
                                    $inner_group_account = Accounts::where('under_group',$inner_group1->id)
                                            ->where('under_group_type','group')
                                            ->where('status','1')
                                            ->where('delete','0')
                                            ->pluck('id')
                                            ->toArray();
                                    $inner_group2 = AccountGroups::select('id')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('heading', $inner_group1->id)
                                            ->where('heading_type',"group")
                                            ->first();
                                    if($inner_group2){
                                        $inner_group_account2 = Accounts::where('under_group',$inner_group2->id)
                                                ->where('under_group_type','group')
                                                ->where('status','1')
                                                ->where('delete','0')
                                                ->pluck('id')
                                                ->toArray();
                                        $inner_group_account = array_merge($inner_group_account, $inner_group_account2);
                                        $inner_group3 = AccountGroups::select('id')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('heading', $inner_group2->id)
                                            ->where('heading_type',"group")
                                            ->first();
                                        if($inner_group3){
                                            $inner_group_account3 = Accounts::where('under_group',$inner_group3->id)
                                                    ->where('under_group_type','group')
                                                    ->where('status','1')
                                                    ->where('delete','0')
                                                    ->pluck('id')
                                                    ->toArray();
                                            $inner_group_account = array_merge($inner_group_account, $inner_group_account3);
                                            $inner_group4 = AccountGroups::select('id')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('heading', $inner_group3->id)
                                            ->where('heading_type',"group")
                                            ->first();
                                            if($inner_group4){
                                                $inner_group_account4 = Accounts::where('under_group',$inner_group4->id)
                                                        ->where('under_group_type','group')
                                                        ->where('status','1')
                                                        ->where('delete','0')
                                                        ->pluck('id')
                                                        ->toArray();
                                                $inner_group_account = array_merge($inner_group_account, $inner_group_account4);
                                                $inner_group5 = AccountGroups::select('id')
                                                    ->where('company_id',Session::get('user_company_id'))
                                                    ->where('heading', $inner_group4->id)
                                                    ->where('heading_type',"group")
                                                    ->first();
                                                if($inner_group5){
                                                    $inner_group_account5 = Accounts::where('under_group',$inner_group5->id)
                                                            ->where('under_group_type','group')
                                                            ->where('status','1')
                                                            ->where('delete','0')
                                                            ->pluck('id')
                                                            ->toArray();
                                                    $inner_group_account = array_merge($inner_group_account, $inner_group_account5);
                                                }                                                    
                                            }
                                        }
                                    }
                                }
                                
                                if(count($v2->account)>0){
                                    if(count($inner_group_account)>0){
                                        $merged_accounts = array_merge($v2->account->pluck('id')->toArray(), $inner_group_account);
                                        $balance_sheet_account = implode(',', $merged_accounts);
                                    }else{
                                        $balance_sheet_account = implode(',', $v2->account->pluck('id')->toArray());
                                    }
                                    
                                }
                            }
                            
                        }
                        if($balance_sheet_account!=""){
                            if($req['type']=="open"){
                                $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));                 
                                $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$balance_sheet_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
                            }else if($req['type']=="close"){                        
                                $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$balance_sheet_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and (STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
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
                        }else if($profit_loss_account!=""){
                            if($req['type']=="open"){    
                                $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));                 
                                $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$profit_loss_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."'))  ");
                            }else if($req['type']=="close"){                        
                                $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$profit_loss_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."')) ");
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
                            $q1->select('id','account_name','under_group','under_group_type');
                        }])
                        ->select('id','name','heading','heading_type')
                        ->where('primary','Yes')
                        ->get();
        foreach ($group_primary_yes as $k2 => $v2) { 
            
            $profit_loss_account = "";
            $balance_sheet_account = "";                                     
            if($v2->heading==4 && $v2->heading_type=='head'){
                if(count($v2->account)>0){
                    $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                }                     
            }else{
                if($v2->heading_type=='group'){
                    //Inner Group
                    $inner_group1 = AccountGroups::select('id', 'heading','heading_type')
                        ->where('id', $v2->heading)
                        ->first();
                    if($inner_group1 && $inner_group1->heading == 4 && $inner_group1->heading_type == 'head'){
                        if(count($v2->account)>0){
                            $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                        }
                    }else if($inner_group1->heading_type == 'group'){
                        $inner_group2 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group1->heading)
                                    ->first();
                        if($inner_group2 && $inner_group2->heading == 4 && $inner_group2->heading_type == 'head'){
                            if(count($v2->account)>0){
                                $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                            } 
                        }else if($inner_group2->heading_type == 'group'){
                            $inner_group3 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group2->heading)
                                    ->first();
                            if($inner_group3 && $inner_group3->heading == 4 && $inner_group3->heading_type == 'head'){
                                if(count($v2->account)>0){
                                    $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                } 
                            }else if($inner_group3->heading_type == 'group'){
                                $inner_group4 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group3->heading)
                                    ->first();
                                if($inner_group4 && $inner_group4->heading == 4 && $inner_group4->heading_type == 'head'){
                                    if(count($v2->account)>0){
                                        $profit_loss_account = implode(',', $v2->account->pluck('id')->toArray());
                                    } 
                                }
                            }
                        }
                    }
                }else{
                    if(count($v2->account)>0){
                        $balance_sheet_account = implode(',', $v2->account->pluck('id')->toArray());
                    }
                }                
            }
            if($balance_sheet_account!=""){                 
                if($req['type']=="open"){  
                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));               
                    $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$balance_sheet_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d') || entry_type=-1 )");
                }else if($req['type']=="close"){               
                    $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$balance_sheet_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') || entry_type=-1)");
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
            }else if($profit_loss_account!=""){
                if($req['type']=="open"){    
                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));                 
                    $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$profit_loss_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date1."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."')) ");
                }else if($req['type']=="close"){                        
                    $debit_credit = DB::select("select sum(debit) as debit,sum(credit) as credit from account_ledger where account_id in (".$profit_loss_account.") and status='1' and delete_status='0' and company_id='".Session::get('user_company_id')."' and ((STR_TO_DATE(txn_date, '%Y-%m-%d')>= STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d')) || ( entry_type = -1  AND financial_year = '".$financial_year."'))");
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
                $group_primary_yes[$k2]->debit = 0;
                $group_primary_yes[$k2]->credit = 0;
            }
        }        
        if($request->trial_balance_by && $request->trial_balance_by=="by_group"){
            return view('display/trialbalance')->with('account', $account)->with('group_primary_yes', $group_primary_yes)->with('type',$req['type'])->with('to_date',$to_date);
        }
        //Data By Account
        $inner_group_account = [];$inner_group_name = "";$inner_group_id = "";
        if($request->group_id && !empty($request->group_id)){
            $account = Accounts::select('id','account_name','under_group','under_group_type')
                           ->where('delete','0')
                           ->where('under_group',$request->group_id)
                           ->where('under_group_type',$request->under)
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();
            //Inner Group 
            $inner_group1 = AccountGroups::select('id','name')
                                            ->where('company_id',Session::get('user_company_id'))
                                            ->where('heading', $request->group_id)
                                            ->where('heading_type',"group")
                                            ->first();
            if($inner_group1){
                $inner_group_name = $inner_group1->name;
                $inner_group_id = $inner_group1->id;
                $inner_group_account = Accounts::where('under_group',$inner_group1->id)
                        ->where('under_group_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->pluck('id')
                        ->toArray();
                $inner_group2 = AccountGroups::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('heading', $inner_group1->id)
                        ->where('heading_type',"group")
                        ->first();
                if($inner_group2){
                    $inner_group_account2 = Accounts::where('under_group',$inner_group2->id)
                            ->where('under_group_type','group')
                            ->where('status','1')
                            ->where('delete','0')
                            ->pluck('id')
                            ->toArray();
                    $inner_group_account = array_merge($inner_group_account, $inner_group_account2);
                    $inner_group3 = AccountGroups::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('heading', $inner_group2->id)
                        ->where('heading_type',"group")
                        ->first();
                    if($inner_group3){
                        $inner_group_account3 = Accounts::where('under_group',$inner_group3->id)
                                ->where('under_group_type','group')
                                ->where('status','1')
                                ->where('delete','0')
                                ->pluck('id')
                                ->toArray();
                        $inner_group_account = array_merge($inner_group_account, $inner_group_account3);
                        $inner_group4 = AccountGroups::select('id')
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('heading', $inner_group3->id)
                        ->where('heading_type',"group")
                        ->first();
                        if($inner_group4){
                            $inner_group_account4 = Accounts::where('under_group',$inner_group4->id)
                                    ->where('under_group_type','group')
                                    ->where('status','1')
                                    ->where('delete','0')
                                    ->pluck('id')
                                    ->toArray();
                            $inner_group_account = array_merge($inner_group_account, $inner_group_account4);
                            $inner_group5 = AccountGroups::select('id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('heading', $inner_group4->id)
                                ->where('heading_type',"group")
                                ->first();
                            if($inner_group5){
                                $inner_group_account5 = Accounts::where('under_group',$inner_group5->id)
                                        ->where('under_group_type','group')
                                        ->where('status','1')
                                        ->where('delete','0')
                                        ->pluck('id')
                                        ->toArray();
                                $inner_group_account = array_merge($inner_group_account, $inner_group_account5);
                            }                                                    
                        }
                    }
                }
                if(count($inner_group_account)>0){
                    if($req['type'] == "open"){
                        $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
                        $group_debit_credit = DB::select("
                            SELECT SUM(debit) as debit, SUM(credit) as credit 
                            FROM account_ledger 
                            WHERE account_id in (".implode(',', $inner_group_account).")
                                AND status = '1' 
                                AND delete_status = '0' 
                                AND company_id = '".Session::get('user_company_id')."' 
                                AND (
                                    STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date1."', '%Y-%m-%d') 
                                    OR entry_type = -1
                                )
                        "); 
                    }else if($req['type'] == "close"){
                        $group_debit_credit = DB::select("
                            SELECT SUM(debit) as debit, SUM(credit) as credit 
                            FROM account_ledger 
                            WHERE account_id in (".implode(',', $inner_group_account).")
                                AND status = '1' 
                                AND delete_status = '0' 
                                AND company_id = '".Session::get('user_company_id')."' 
                                AND (
                                    STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date."', '%Y-%m-%d') 
                                    OR entry_type = -1
                                )
                        ");
                    }
                    $debit = $group_debit_credit[0]->debit ?? 0;
                    $credit = $group_debit_credit[0]->credit ?? 0;
                    $inner_group_account = [];
                    array_push($inner_group_account,array("id"=>$inner_group_id,"account_name"=>$inner_group_name,"under_group"=>$inner_group_id,"under_group_type"=>"group","debit"=>$debit,"credit"=>$credit));
                }
                
            }
            
        }else{
            $account = Accounts::select('id','account_name','under_group','under_group_type')
                           ->where('delete','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')->get();
        }
        foreach($account as $key => $value) {
            // Step 1: Get the group info for this account
            $isUnderHeading4 = false;
            if($value->under_group==4 && $value->under_group_type=="head"){
                $isUnderHeading4 = true;
            }else{
                $group = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $value->under_group)
                                    ->first();
                if($group && $group->heading == 4 && $group->heading_type == 'head'){
                    $isUnderHeading4 = true;
                }else if($group->heading_type == 'group'){
                    $inner_group1 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $group->heading)
                                    ->first();
                    if($inner_group1 && $inner_group1->heading == 4 && $inner_group1->heading_type == 'head'){
                        $isUnderHeading4 = true;
                    }else if($inner_group1->heading_type == 'group'){
                        $inner_group2 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group1->heading)
                                    ->first();
                        if($inner_group2 && $inner_group2->heading == 4 && $inner_group2->heading_type == 'head'){
                            $isUnderHeading4 = true;
                        }else if($inner_group2->heading_type == 'group'){
                            $inner_group3 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group2->heading)
                                    ->first();
                            if($inner_group3 && $inner_group3->heading == 4 && $inner_group3->heading_type == 'head'){
                                $isUnderHeading4 = true;
                            }else if($inner_group3->heading_type == 'group'){
                                $inner_group4 = AccountGroups::select('id', 'heading','heading_type')
                                    ->where('id', $inner_group3->heading)
                                    ->first();
                                if($inner_group4 && $inner_group4->heading == 4 && $inner_group4->heading_type == 'head'){
                                    $isUnderHeading4 = true;
                                }
                            }
                        }
                    }
                }
            }                
            // Step 2: Check if this account or its group is under heading 4
            //$isUnderHeading4 = $group && $group->heading == 4;        
            // Step 3: Now apply your logic
            if($isUnderHeading4){
                if($req['type'] == "open"){
                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
                    $debit_credit = DB::select("
                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                        FROM account_ledger 
                        WHERE account_id = '".$value->id."' 
                            AND status = '1' 
                            AND delete_status = '0' 
                            AND company_id = '".Session::get('user_company_id')."' 
                            AND (
                                STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE('".$from_date."', '%Y-%m-%d') 
                                AND STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date1."', '%Y-%m-%d') 
                                OR ( entry_type = -1  AND financial_year = '".$financial_year."')
                            )
                    ");
                }else if($req['type'] == "close") {
                    $debit_credit = DB::select("
                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                        FROM account_ledger 
                        WHERE account_id = '".$value->id."' 
                            AND status = '1' 
                            AND delete_status = '0' 
                            AND company_id = '".Session::get('user_company_id')."' 
                            AND (
                                STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE('".$from_date."', '%Y-%m-%d') 
                                AND STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date."', '%Y-%m-%d')
                                OR ( entry_type = -1  AND financial_year = '".$financial_year."')
                            )
                    ");
                }
            } else {

                if($req['type'] == "open"){
                    $to_date1 = date('Y-m-d', strtotime($to_date . ' -1 day'));
                    $debit_credit = DB::select("
                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                        FROM account_ledger 
                        WHERE account_id = '".$value->id."' 
                            AND status = '1' 
                            AND delete_status = '0' 
                            AND company_id = '".Session::get('user_company_id')."' 
                            AND (
                                STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date1."', '%Y-%m-%d') 
                                OR entry_type = -1
                            )
                    "); 
                }else if($req['type'] == "close"){
                    $debit_credit = DB::select("
                        SELECT SUM(debit) as debit, SUM(credit) as credit 
                        FROM account_ledger 
                        WHERE account_id = '".$value->id."' 
                            AND status = '1' 
                            AND delete_status = '0' 
                            AND company_id = '".Session::get('user_company_id')."' 
                            AND (
                                STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE('".$to_date."', '%Y-%m-%d') 
                                OR entry_type = -1
                            )
                    ");
                } 
            }         
            // Assigning debit/credit to account
            $debit = $debit_credit[0]->debit ?? 0;
            $credit = $debit_credit[0]->credit ?? 0;
            $account[$key]->debit = $debit;
            $account[$key]->credit = $credit;
        }     
        // Add Stock In Hand
        $previous_date = Carbon::parse($from_date)->subDay();
        $stock_in_hand = round(CommonHelper::ClosingStock($previous_date), 2);     
        $newCompete = collect([
            [
                'id' => '',
                'account_name' => 'Stock In Hand',
                'debit' => $stock_in_hand > 0 ? $stock_in_hand : 0,
                'credit' => $stock_in_hand < 0 ? abs($stock_in_hand) : 0
            ]
        ]);     
        $account = $account->sortBy('account_name');     
        if (!$request->group_id) {
            $account = $account->concat($newCompete)->sortBy('account_name');
        } 
         //Prevoius year profit & loss
      list($start, $end) = explode('-', $financial_year);
      $prevFy = str_pad($start - 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($end - 1, 2, '0', STR_PAD_LEFT);
      $prev_year_profitloss =  CommonHelper::profitLoss($prevFy);
      //Check Profit & Loss Account Entry
      $jouranl = Journal::select('id')
                     ->withSum(['journal_details' => function ($query) {
                        $query->where('id','!=','13319');
                     }], 'debit')->where('journals.company_id',Session::get('user_company_id'))
                     ->where('journals.financial_year',$prevFy)
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
      $account = $account->concat(collect($inner_group_account));
    //   echo "<pre>";
    //   print_r($account->toArray());die;
      $prev_year_profitloss = abs($prev_year_profitloss) - $journal_amount;
        return view('display/trialbalance')
                 ->with('account', $account)
                 ->with('inner_group_account',$inner_group_account)
                 ->with('type', $req['type'])
                 ->with('to_date', $to_date)->with('prev_year_profitloss',$prev_year_profitloss)->with('prev_year_profit_status',$prev_year_profit_status)->with('prevFy',$prevFy);
    }
} 