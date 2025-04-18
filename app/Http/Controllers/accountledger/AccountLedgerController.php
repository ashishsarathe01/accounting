<?php

namespace App\Http\Controllers\accountledger;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Accounts;
use Illuminate\Support\Collection;
use App\Models\AccountLedger;
use App\Models\Sales;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Payment;
use App\Models\PaymentDetails;
use App\Models\Receipt;
use App\Models\ReceiptDetails;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\Contra;
use App\Models\ContraDetails;
use DB;
use Session;

class AccountLedgerController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

   public function index(){
      $financial_year = Session::get('default_fy');
      $fdate = date('Y-m-')."01";
      $tdate = date('Y-m-t');
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $y =  explode("-",$financial_year);
         $fdate = $y[1]."-03-01";
         $fdate = date('Y-m-d',strtotime($fdate));
         $tdate = $y[1]."-03-31";
         $tdate = date('Y-m-d',strtotime($tdate));
      }
      $party_id = '2';
      $party_list = Accounts::where('delete','=','0')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
      $ledger = array();
      return view('accountledger/accountledger')->with('party_list', $party_list)->with('ledger', $ledger)->with('opening', 0)->with('fdate', $fdate)->with('tdate',$tdate);
   }
   public function filter(Request $request){
      Session::put('redirect_url','');
      $party_id = $request->party;
      $party_list = Accounts::where('delete','=','0')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){         
         $ledger = DB::select(DB::raw("SELECT * FROM account_ledger WHERE account_id='".$party_id."' and entry_type!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status=1 and delete_status='0' and company_id='".Session::get('user_company_id')."' order by STR_TO_DATE(txn_date, '%Y-%m-%d'),entry_type,entry_type_id"));
      }else{
         $ledger = AccountLedger::where('account_id',$party_id)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->where('entry_type','!=','-1')
                                 ->where('delete_status','=','0')
                                 ->orderBy(DB::raw("STR_TO_DATE(txn_date, '%Y-%m-%d')"))
                                 ->get();
      }      
      if(count($ledger)>0){
         foreach ($ledger as $key => $value) {
            $ledger[$key]->account = "";
            $ledger[$key]->bill_no = '';
            $ledger[$key]->narration = '';
            $ledger[$key]->long_narration = '';
            $ledger[$key]->einvoice_status = 0;
            if(!empty($value->map_account_id)){
               $account = Accounts::select('account_name')->where('id',$value->map_account_id)->first();
               $ledger[$key]->account = $account->account_name;
            }
            if($value->entry_type==1){
               $action = Sales::select('voucher_no','series_no','financial_year','e_invoice_status','e_waybill_status')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = $action->series_no."/".$action->financial_year."/".$action->voucher_no;
               $ledger[$key]->einvoice_status = 0;
               if($action->e_invoice_status==1 || $action->e_waybill_status==1){
                  $ledger[$key]->einvoice_status = 1;
               }
               
            }else if($value->entry_type==2){
               $action = Purchase::select('voucher_no')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = $action->voucher_no;
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==3){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix.$action->sale_return_no;                  
               }
            }else if($value->entry_type==4){
               $action = PurchaseReturn::select('invoice_no','series_no','financial_year')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = $action->series_no."/".$action->financial_year."/DR".$action->invoice_no;
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==5){
               $action = Payment::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = PaymentDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==6){
               $action = Receipt::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ReceiptDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==7){
               $action = Journal::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = JournalDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==8){
               $action = Contra::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ContraDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==9){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix.$action->sale_return_no;
               } 
            }else if($value->entry_type==10){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix.$action->sale_return_no;
               }
            }else{               
               $ledger[$key]->bill_no = '';
               $ledger[$key]->einvoice_status = 0;
            }            
         }
      }
      $collection = new Collection($ledger);
      //$ledger = $collection->sortByAsc('date');
      $opening = 0;
      if(isset($request->from_date) && !empty($request->from_date)){
         $open_ledger = DB::select(DB::raw("SELECT SUM(debit) as debit,SUM(credit) as credit FROM account_ledger WHERE account_id='".$party_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and status=1 and delete_status='0' and company_id='".Session::get('user_company_id')."'"));
         if(count($open_ledger)>0){
            if($open_ledger[0]->debit=="" && $open_ledger[0]->credit==""){
               $open_ledger = AccountLedger::where('account_id',$party_id)
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->where('entry_type','-1')
                                       ->first();
               if($open_ledger){
                  if($open_ledger->credit!=""){
                     $opening = -$open_ledger->credit;
                  }else if($open_ledger->debit!=""){
                     $opening = $open_ledger->debit;
                  }
               }
            }else{

               $balance = $open_ledger[0]->debit - $open_ledger[0]->credit;
               $basic_open_ledger = AccountLedger::where('account_id',$party_id)
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->where('entry_type','-1')
                                       ->first();
               if($basic_open_ledger){
                  if($basic_open_ledger->credit!=""){
                     $balance = $balance - $basic_open_ledger->credit;
                  }else if($basic_open_ledger->debit!=""){
                     $balance = $balance + $basic_open_ledger->debit;
                  }
               }
               if($balance<0){
                  $opening = $balance;
               }else{
                  $opening = $balance;
               }
            }            
         }
      }else{
         $open_ledger = AccountLedger::where('account_id',$party_id)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->where('entry_type','-1')
                                 ->first();
         if($open_ledger){
            if($open_ledger->credit!=""){
               $opening = -$open_ledger->credit;
            }else if($open_ledger->debit!=""){
               $opening = $open_ledger->debit;
            }
         }
      }      
      $ledger = json_decode($collection, true);
      
      return view('accountledger/accountledger')->with('party_list', $party_list)->with('ledger', $ledger)->with('party_id', $party_id)->with('opening', $opening);
   }
}
