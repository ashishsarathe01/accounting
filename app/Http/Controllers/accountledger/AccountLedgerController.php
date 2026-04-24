<?php

namespace App\Http\Controllers\accountledger;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Accounts;
use Illuminate\Support\Collection;
use App\Models\AccountLedger;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Sales;
use App\Models\Purchase;
use App\Models\Companies;
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
use App\Models\AccountGroups;
use App\Models\SupplierPurchaseVehicleDetail;
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
      $configuration = SaleInvoiceConfiguration::select('purchase_order_info_show_in_ledger')->where('company_id',Session::get('user_company_id'))->first();
      return view('accountledger/accountledger')->with('party_list', $party_list)->with('ledger', $ledger)->with('opening', 0)->with('fdate', $fdate)->with('tdate',$tdate)->with('configuration',$configuration);
   }
   public function filter(Request $request){
      Session::put('redirect_url','');
      $party_id = $request->party;
      $party_list = Accounts::where('delete','=','0')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){         
         // $ledger = DB::select(DB::raw("SELECT * FROM account_ledger WHERE account_id='".$party_id."' and entry_type!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status=1 and delete_status='0' and company_id='".Session::get('user_company_id')."' order by STR_TO_DATE(txn_date, '%Y-%m-%d'),entry_type,entry_type_id"));
         $ledger = AccountLedger::where('account_id', $party_id)
                                 ->where('entry_type', '!=', -1)
                                 ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$request->from_date])
                                 ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$request->to_date])
                                 ->where('status', 1)
                                 ->where('delete_status', '0')
                                 ->where('company_id', Session::get('user_company_id'))
                                 ->orderByRaw("STR_TO_DATE(txn_date, '%Y-%m-%d')")
                                 ->orderBy('entry_type')
                                 ->orderBy('entry_type_id')
                                 ->get();
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
            $ledger[$key]->slip_no = '';
            $ledger[$key]->narration = '';
            $ledger[$key]->long_narration = '';
            $ledger[$key]->invoice_no = '';
            $ledger[$key]->einvoice_status = 0;
            if(!empty($value->map_account_id)){
               $account = Accounts::select('account_name')->where('id',$value->map_account_id)->first();
               $ledger[$key]->account = $account->account_name;
            }
            if($value->entry_type==1){
                $action = Sales::select(
                                    'sales.voucher_no_prefix',
                                    'sales.voucher_no',
                                    'sales.series_no',
                                    'sales.financial_year',
                                    'sales.e_invoice_status',
                                    'sales.e_waybill_status',
                                    'sales.po_no',
                                    'sales.address_id',
                        
                                    'account_other_addresses.location as address_location',
                                    'accounts.acc_location as account_location'
                                )
                                ->leftJoin('account_other_addresses', 'account_other_addresses.id', '=', 'sales.address_id')
                                ->leftJoin('accounts', 'accounts.id', '=', 'sales.party') // ⚠️ make sure this column exists
                                ->where('sales.id', $value->entry_type_id)
                                ->first();

                if ($action) {
                    $ledger[$key]->bill_no = $action->voucher_no_prefix;
                    $ledger[$key]->einvoice_status = 0;
                    $ledger[$key]->po_no = $action->po_no ?? '';
            
                    // ✅ FINAL LOCATION LOGIC
                    $ledger[$key]->location = !empty($action->address_id)
                        ? ($action->address_location ?? '')
                        : ($action->account_location ?? '');
                    if ($action->e_invoice_status == 1 || $action->e_waybill_status == 1) {
                        $ledger[$key]->einvoice_status = 1;
                    }
                }
            }else if($value->entry_type==2){
               $action = Purchase::select('voucher_no')->where('id',$value->entry_type_id)->first();
               $slip_no = SupplierPurchaseVehicleDetail::select('voucher_no as slip_no')->where('map_purchase_id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = $action->voucher_no;
               $ledger[$key]->slip_no = $slip_no->slip_no ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==3){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix;                  
               }
            }else if($value->entry_type==4 || $value->entry_type==12 || $value->entry_type==13){
               $action = PurchaseReturn::select('sr_prefix','series_no','financial_year')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = $action->sr_prefix;
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==5){
                $action = Payment::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = PaymentDetails::where('id', $value->entry_type_detail_id)->value('narration');
                if(!$narration){
                    $narration = $value->entry_narration;
                }
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==6){
               $action = Receipt::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ReceiptDetails::where('id', $value->entry_type_detail_id)->value('narration');
                if(!$narration){
                    $narration = $value->entry_narration;
                }
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==7){
                $action = Journal::select('voucher_no', 'long_narration','invoice_no')->where('id', $value->entry_type_id)->first();
                $narration = JournalDetails::where('id', $value->entry_type_detail_id)->value('narration');
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
                $ledger[$key]->invoice_no = $action->invoice_no ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==8){
               $action = Contra::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ContraDetails::where('id', $value->entry_type_detail_id)->value('narration');
                if(!$narration){
                    $narration = $value->entry_narration;
                }
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
               $ledger[$key]->einvoice_status = 0;
            }else if($value->entry_type==9){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix;
               } 
            }else if($value->entry_type==10 ){
               $action = SalesReturn::select('sale_return_no','sr_prefix')->where('id',$value->entry_type_id)->first();
               $ledger[$key]->bill_no = "";
               $ledger[$key]->einvoice_status = 0;
               if($action){
                  $ledger[$key]->bill_no = $action->sr_prefix;
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
      //Check Profit & Loss Account
      $profitloss_account_status = 0;
      $account = Accounts::select('under_group','under_group_type')
                     ->where('id',$party_id)
                     ->first();
      if($account->under_group==4 && $account->under_group_type=='head'){
         $profitloss_account_status = 1;
      }else{
         $group = AccountGroups::select('heading','heading_type')
                        ->where('id',$account->under_group)
                        ->first();
         if($group && $group->heading==4 && $group->heading_type=='head'){
            $profitloss_account_status = 1;
         }else if($group->heading_type=='group'){
            $inner_group = AccountGroups::select('heading','heading_type')
                        ->where('id',$group->heading)
                        ->first();
            if($inner_group && $inner_group->heading==4 && $inner_group->heading_type=='head'){
               $profitloss_account_status = 1;
            }
         }
      }
      
      if($profitloss_account_status==0){
         if(isset($request->from_date) && !empty($request->from_date)){
            //$open_ledger = DB::select(DB::raw("SELECT SUM(debit) as debit,SUM(credit) as credit FROM account_ledger WHERE account_id='".$party_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and status=1 and delete_status='0' and company_id='".Session::get('user_company_id')."'"));
            $open_ledger = DB::table('account_ledger')
                            ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                            ->where('account_id', $party_id)
                            ->where('txn_date', '<', $request->from_date)
                            ->where('status', '1')
                            ->where('delete_status', '0')
                            ->where('company_id', Session::get('user_company_id'))
                            ->first();
            if($open_ledger){
               if($open_ledger->debit=="" && $open_ledger->credit==""){
                  $open_ledger = AccountLedger::where('account_id',$party_id)
                                          ->where('company_id',Session::get('user_company_id'))
                                          ->where('entry_type','-1')
                                          ->where('delete_status', '0')
                                          ->first();
                  if($open_ledger){
                     if($open_ledger->credit!=""){
                        $opening = -$open_ledger->credit;
                     }else if($open_ledger->debit!=""){
                        $opening = $open_ledger->debit;
                     }
                  }
               }else{
                  $balance = $open_ledger->debit - $open_ledger->credit;
                  $basic_open_ledger = AccountLedger::where('account_id',$party_id)
                                          ->where('company_id',Session::get('user_company_id'))
                                          ->where('entry_type','-1')
                                          ->where('delete_status', '0')
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
                                    ->where('delete_status', '0')
                                    ->first();
            if($open_ledger){
               if($open_ledger->credit!=""){
                  $opening = -$open_ledger->credit;
               }else if($open_ledger->debit!=""){
                  $opening = $open_ledger->debit;
               }
            }
         }
      }else{
         if(isset($request->from_date) && !empty($request->from_date)){
            $financial_year = Session::get('default_fy');
            $y = explode("-",$financial_year);
            $from_date = $y[0]."-04-01";
            $from_date = date('Y-m-d',strtotime($from_date));

            $open_ledger = AccountLedger::where('account_id', $party_id)
               ->where('company_id', Session::get('user_company_id'))
               ->where('status', '1')
               ->where('delete_status', '0')
               ->where('txn_date', '>=', $from_date)
               ->where('txn_date', '<', $request->from_date)
               ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
               ->first();
               
            //print_r($open_ledger->toArray());die;
            if($open_ledger){
               
                  $balance = $open_ledger->debit - $open_ledger->credit;
                  if($balance<0){
                     $opening = $balance;
                  }else{
                     $opening = $balance;
                  }
               
            }
         }
      }
      $ledger = json_decode($collection, true);
      $configuration = SaleInvoiceConfiguration::select('purchase_order_info_show_in_ledger')->where('company_id',Session::get('user_company_id'))->first();
      return view('accountledger/accountledger')->with('party_list', $party_list)->with('ledger', $ledger)->with('party_id', $party_id)->with('opening', $opening)->with('configuration', $configuration);
   }
   public function exportPdf(Request $request)
    {
        $party_id = $request->input('party');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        // Fetch account name
        $accounts = Accounts::find($party_id);

        $comp = Companies::where('id', Session::get('user_company_id'))->first();


        // Fetch ledger entries
        $ledger = DB::select(DB::raw("SELECT * FROM account_ledger WHERE account_id='".$party_id."' and entry_type!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status=1 and delete_status='0' and company_id='".Session::get('user_company_id')."' order by STR_TO_DATE(txn_date, '%Y-%m-%d'),entry_type,entry_type_id"));
        
        if(count($ledger)>0){
         foreach ($ledger as $key => $value) {
            $ledger[$key]->account = "";
        
            if (!empty($value->map_account_id)) {
                $account = Accounts::select('account_name')->where('id', $value->map_account_id)->first();
                $ledger[$key]->account = $account->account_name ?? '';
            }
        
            $ledger[$key]->bill_no = '';
            $ledger[$key]->narration = '';
            $ledger[$key]->long_narration = '';
        
            if ($value->entry_type == 1) {  // Sales
                $action = Sales::select(
            'sales.voucher_no_prefix',
            'sales.voucher_no',
            'sales.series_no',
            'sales.financial_year',
            'sales.e_invoice_status',
            'sales.e_waybill_status',
            'sales.po_no',
            'sales.address_id',

            'account_other_addresses.location as address_location',
            'accounts.acc_location as account_location'
        )
        ->leftJoin('account_other_addresses', 'account_other_addresses.id', '=', 'sales.address_id')
        ->leftJoin('accounts', 'accounts.id', '=', 'sales.party') // ⚠️ make sure this column exists
        ->where('sales.id', $value->entry_type_id)
        ->first();
        
                if ($action) {
                    $ledger[$key]->bill_no = $action->series_no . "/" . $action->financial_year . "/" . $action->voucher_no;
                    $ledger[$key]->po_no   = $action->po_no ?? ''; 
                    $ledger[$key]->location = !empty($action->address_id)
            ? ($action->address_location ?? '')
            : ($action->account_location ?? '');
                }
        
            } else if ($value->entry_type == 2) {  // Purchase
                $action = Purchase::select('voucher_no')->where('id', $value->entry_type_id)->first();
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
        
            } else if ($value->entry_type == 3) {  // Sales Return
                $action = SalesReturn::select('sale_return_no', 'sr_prefix')->where('id', $value->entry_type_id)->first();
                if ($action) {
                    $ledger[$key]->bill_no = $action->sr_prefix . $action->sale_return_no;
                }
        
            } else if ($value->entry_type == 4) {  // Purchase Return
                $action = PurchaseReturn::select('invoice_no', 'series_no', 'financial_year')->where('id', $value->entry_type_id)->first();
                if ($action) {
                    $ledger[$key]->bill_no = $action->series_no . "/" . $action->financial_year . "/DR" . $action->invoice_no;
                }
        
            } else if ($value->entry_type == 5) {  // Payment
                $action = Payment::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = PaymentDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration =  $narration ?? '';
        
            } else if ($value->entry_type == 6) {  // Receipt
                $action = Receipt::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ReceiptDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
        
            } else if ($value->entry_type == 7) {  // Journal
                $action = Journal::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = JournalDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
        
            } else if ($value->entry_type == 8) {  // Contra
                $action = Contra::select('voucher_no', 'long_narration')->where('id', $value->entry_type_id)->first();
                $narration = ContraDetails::where('id', $value->entry_type_detail_id)->value('narration');
        
                $ledger[$key]->bill_no = $action->voucher_no ?? '';
                $ledger[$key]->long_narration = $action->long_narration ?? '';
                $ledger[$key]->narration = $narration ?? '';
        
            } else if (in_array($value->entry_type, [9,10])) {  // Other Sales Returns
                $action = SalesReturn::select('sale_return_no', 'sr_prefix')->where('id', $value->entry_type_id)->first();
                if ($action) {
                    $ledger[$key]->bill_no = $action->sr_prefix . $action->sale_return_no;
                }
            }
        }
        
      }
        // Opening balance logic (optional)
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
        
        $configuration = SaleInvoiceConfiguration::where('company_id',Session::get('user_company_id'))->first();

        $pdf = Pdf::loadView('accountledger.ledger', compact('ledger', 'opening', 'accounts', 'from_date', 'to_date','accounts','comp','configuration'));
        return $pdf->download('Account-Ledger-'.$accounts->account_name.'.pdf');
    }
    public function exportCsv(Request $request)
   {
      $party_id = $request->party;

      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){         
         $ledger = AccountLedger::where('account_id', $party_id)
               ->where('entry_type', '!=', -1)
               ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$request->from_date])
               ->whereRaw("STR_TO_DATE(txn_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$request->to_date])
               ->where('status', 1)
               ->where('delete_status', '0')
               ->where('company_id', Session::get('user_company_id'))
               ->orderByRaw("STR_TO_DATE(txn_date, '%Y-%m-%d')")
               ->orderBy('entry_type')
               ->orderBy('entry_type_id')
               ->get();
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
               $ledger[$key]->slip_no = '';
               $ledger[$key]->narration = '';
               $ledger[$key]->long_narration = '';

               if(!empty($value->map_account_id)){
                  $account = Accounts::select('account_name')->where('id',$value->map_account_id)->first();
                  $ledger[$key]->account = $account->account_name ?? '';
               }

               if($value->entry_type==1){
                  $action = Sales::select('voucher_no_prefix')->where('id',$value->entry_type_id)->first();
                  $ledger[$key]->bill_no = $action->voucher_no_prefix ?? '';

               }else if($value->entry_type==2){
                  $action = Purchase::select('voucher_no')->where('id',$value->entry_type_id)->first();
                  $ledger[$key]->bill_no = $action->voucher_no ?? '';

               }else if($value->entry_type==3){
                  $action = SalesReturn::select('sr_prefix')->where('id',$value->entry_type_id)->first();
                  $ledger[$key]->bill_no = $action->sr_prefix ?? '';

               }else if($value->entry_type==4 || $value->entry_type==12 || $value->entry_type==13){
                  $action = PurchaseReturn::select('sr_prefix')->where('id',$value->entry_type_id)->first();
                  $ledger[$key]->bill_no = $action->sr_prefix ?? '';

               }else if($value->entry_type==5){
                  $action = Payment::select('voucher_no','long_narration')->where('id',$value->entry_type_id)->first();
                  $narration = PaymentDetails::where('id',$value->entry_type_detail_id)->value('narration');

                  if(!$narration){
                     $narration = $value->entry_narration;
                  }

                  $ledger[$key]->bill_no = $action->voucher_no ?? '';
                  $ledger[$key]->long_narration = $action->long_narration ?? '';
                  $ledger[$key]->narration = $narration ?? '';

               }else if($value->entry_type==6){
                  $action = Receipt::select('voucher_no','long_narration')->where('id',$value->entry_type_id)->first();
                  $narration = ReceiptDetails::where('id',$value->entry_type_detail_id)->value('narration');

                  if(!$narration){
                     $narration = $value->entry_narration;
                  }

                  $ledger[$key]->bill_no = $action->voucher_no ?? '';
                  $ledger[$key]->long_narration = $action->long_narration ?? '';
                  $ledger[$key]->narration = $narration ?? '';

               }else if($value->entry_type==7){
                  $action = Journal::select('voucher_no','long_narration')->where('id',$value->entry_type_id)->first();
                  $narration = JournalDetails::where('id',$value->entry_type_detail_id)->value('narration');

                  $ledger[$key]->bill_no = $action->voucher_no ?? '';
                  $ledger[$key]->long_narration = $action->long_narration ?? '';
                  $ledger[$key]->narration = $narration ?? '';

               }else if($value->entry_type==8){
                  $action = Contra::select('voucher_no','long_narration')->where('id',$value->entry_type_id)->first();
                  $narration = ContraDetails::where('id',$value->entry_type_detail_id)->value('narration');

                  if(!$narration){
                     $narration = $value->entry_narration;
                  }

                  $ledger[$key]->bill_no = $action->voucher_no ?? '';
                  $ledger[$key]->long_narration = $action->long_narration ?? '';

               }else if($value->entry_type==9 || $value->entry_type==10){
                  $action = SalesReturn::select('sr_prefix')->where('id',$value->entry_type_id)->first();
                  $ledger[$key]->bill_no = $action->sr_prefix ?? '';
               }
         }
      }

      $opening = 0;

      $opening = 0;

         $profitloss_account_status = 0;
         $account = Accounts::select('under_group','under_group_type')
            ->where('id',$party_id)
            ->first();

         if($account->under_group==4 && $account->under_group_type=='head'){
            $profitloss_account_status = 1;
         }else{
            $group = AccountGroups::select('heading','heading_type')
               ->where('id',$account->under_group)
               ->first();

            if($group && $group->heading==4 && $group->heading_type=='head'){
               $profitloss_account_status = 1;
            }else if($group->heading_type=='group'){
               $inner_group = AccountGroups::select('heading','heading_type')
                     ->where('id',$group->heading)
                     ->first();

               if($inner_group && $inner_group->heading==4 && $inner_group->heading_type=='head'){
                     $profitloss_account_status = 1;
               }
            }
         }

         if($profitloss_account_status==0){

            if(isset($request->from_date) && !empty($request->from_date)){

               $open_ledger = DB::select(DB::raw("
                     SELECT SUM(debit) as debit, SUM(credit) as credit 
                     FROM account_ledger 
                     WHERE account_id='".$party_id."' 
                     AND STR_TO_DATE(txn_date, '%Y-%m-%d') < STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') 
                     AND status=1 AND delete_status='0' 
                     AND company_id='".Session::get('user_company_id')."'
               "));

               if(count($open_ledger)>0){

                     if($open_ledger[0]->debit=="" && $open_ledger[0]->credit==""){

                        $basic_open_ledger = AccountLedger::where('account_id',$party_id)
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('delete_status', '0')
                           ->where('entry_type','-1')
                           ->first();

                        if($basic_open_ledger){
                           if($basic_open_ledger->credit!=""){
                                 $opening = -$basic_open_ledger->credit;
                           }else if($basic_open_ledger->debit!=""){
                                 $opening = $basic_open_ledger->debit;
                           }
                        }

                     }else{

                        $balance = $open_ledger[0]->debit - $open_ledger[0]->credit;

                        $basic_open_ledger = AccountLedger::where('account_id',$party_id)
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('delete_status', '0')
                           ->where('entry_type','-1')
                           ->first();

                        if($basic_open_ledger){
                           if($basic_open_ledger->credit!=""){
                                 $balance = $balance - $basic_open_ledger->credit;
                           }else if($basic_open_ledger->debit!=""){
                                 $balance = $balance + $basic_open_ledger->debit;
                           }
                        }

                        $opening = $balance;
                     }
               }

            }else{

               $open_ledger = AccountLedger::where('account_id',$party_id)
                     ->where('company_id',Session::get('user_company_id'))
                     ->where('entry_type','-1')
                     ->where('delete_status', '0')
                     ->first();

               if($open_ledger){
                     if($open_ledger->credit!=""){
                        $opening = -$open_ledger->credit;
                     }else if($open_ledger->debit!=""){
                        $opening = $open_ledger->debit;
                     }
               }
            }

         }else{

            if(isset($request->from_date) && !empty($request->from_date)){

               $financial_year = Session::get('default_fy');
               $y = explode("-",$financial_year);

               $from_date = $y[0]."-04-01";
               $from_date = date('Y-m-d',strtotime($from_date));

               $open_ledger = AccountLedger::where('account_id', $party_id)
                     ->where('company_id', Session::get('user_company_id'))
                     ->where('status', '1')
                     ->where('delete_status', '0')
                     ->where('txn_date', '>=', $from_date)
                     ->where('txn_date', '<', $request->from_date)
                     ->selectRaw('SUM(debit) as debit, SUM(credit) as credit')
                     ->first();

               if($open_ledger){
                     $balance = $open_ledger->debit - $open_ledger->credit;
                     $opening = $balance;
               }
            }
         }
         $company = Companies::where('id', Session::get('user_company_id'))->first();
         $accountName = Accounts::where('id',$party_id)->value('account_name');

         $fromDate = $request->from_date ? date('d-m-Y', strtotime($request->from_date)) : '';
         $toDate   = $request->to_date ? date('d-m-Y', strtotime($request->to_date)) : '';
      $headers = [
         "Content-type" => "text/csv",
         "Content-Disposition" => "attachment; filename=Account-Ledger.csv",
      ];

      $callback = function () use ($ledger, $opening, $company, $accountName, $fromDate, $toDate) {

         $file = fopen('php://output', 'w');
            fputcsv($file, [$company->company_name ?? '']);
            fputcsv($file, [$company->address ?? '']);
            fputcsv($file, ['CIN: '.($company->cin ?? '')]);

            fputcsv($file, []); 

            fputcsv($file, ['Account Ledger']);

            fputcsv($file, ['Account: '.$accountName]);
            fputcsv($file, ['From: '.$fromDate.'  To: '.$toDate]);

            fputcsv($file, []); 
         fputcsv($file, ['Date','Type','Vch/Bill No','Account','Debit','Credit','Balance','Short Narration']);

         $balance = $opening;
            $totalDebit = 0;
            $totalCredit = 0;
         fputcsv($file, ['', '', 'Opening Bal.', '', '', '', number_format(abs($balance),2).' '.($balance>=0?'Dr':'Cr'), '']);

         foreach ($ledger as $row) {
               $balance += ($row->debit - $row->credit);
               $totalDebit += (float)$row->debit;
               $totalCredit += (float)$row->credit;
               $type = '';

               if($row->entry_type==1){
                  $type = "SupO";
               }else if($row->entry_type==2){
                  $type = "SupI";
               }else if($row->entry_type==3){
                  $type = "Credit Note";
               }else if($row->entry_type==4){
                  $type = "Debit Note";
               }else if($row->entry_type==5){
                  $type = "Payment";
               }else if($row->entry_type==6){
                  $type = "Receipt";
               }else if($row->entry_type==7){
                  $type = "Journal";
               }else if($row->entry_type==8){
                  $type = "Contra";
               }else if($row->entry_type==9){
                  $type = "Credit Note";
               }else if($row->entry_type==10){
                  $type = "Credit Note";
               }else if($row->entry_type==11){
                  $type = "Stock Transfer";
               }else if($row->entry_type==12){
                  $type = "Debit Note";
               }else if($row->entry_type==13){
                  $type = "Debit Note";
               }
               fputcsv($file, [
                  date('d-m-Y', strtotime($row->txn_date)),
                  $type,
                  $row->bill_no,
                  $row->account,
                  $row->debit ? number_format($row->debit,2) : '',
                  $row->credit ? number_format($row->credit,2) : '',
                  number_format(abs($balance),2).' '.($balance>=0?'Dr':'Cr'),
                  $row->narration
               ]);

               if(!empty($row->long_narration)){
                  fputcsv($file, ['', '', '', '', '', '', '', 'Long Narration: '.$row->long_narration]);
               }
         }

            fputcsv($file, [
               '', 
               '', 
               'Total :',  
               '', 
               number_format($totalDebit,2),
               number_format($totalCredit,2),
               '',
               ''
            ]);

            fputcsv($file, [
               '',
               '',
               'Closing Bal. :',
               '',
               '',
               '',
               number_format(abs($balance),2).' '.($balance>=0?'Dr':'Cr'),
               ''
            ]);
         fclose($file);
      };

      return response()->stream($callback, 200, $headers);
   }
   
   public function checkPartyGroup(Request $request)
{
    $party_id = $request->party_id;

    $isSpecialGroup = false;

    $party = DB::table('accounts')
        ->where('id', $party_id)
        ->select('under_group')
        ->first();

    if ($party) {

        $group_id = $party->under_group;

        while ($group_id != 0) {

            $group = DB::table('account_groups')
                ->where('id', $group_id)
                ->select('id', 'heading') // heading = parent
                ->first();

            if (!$group) break;

            // ✅ CHECK CURRENT GROUP
            if (in_array($group->id, [3, 11])) {
                $isSpecialGroup = true;
                break;
            }

            // ✅ MOVE TO PARENT (VERY IMPORTANT)
            $group_id = $group->heading;
        }
    }

    return response()->json([
        'status' => true,
        'isSpecialGroup' => $isSpecialGroup
    ]);
}
}
