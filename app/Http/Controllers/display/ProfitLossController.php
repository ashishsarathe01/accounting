<?php

namespace App\Http\Controllers\display;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountGroups;
use App\Models\Sales;
use App\Models\Purchase;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ClosingStock;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Helpers\CommonHelper;
use DB;
use Session;
use DateTime;
class ProfitLossController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      $from_date = $y[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = date('Y-m-t');
      if($financial_year!=$current_year){
         $y =  explode("-",$financial_year);
         $from_date = $y[0]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = $y[1]."-03-31";
         $to_date = date('Y-m-d',strtotime($to_date));
      }      
      //Purchase
      $tot_purchase_amt = DB::table('purchases')
         ->join('purchase_descriptions','purchases.id','=','purchase_descriptions.purchase_id')
         ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $tot_purchase_sundry_amt = 0;
      $purchase_sundry = DB::table('purchases')
         ->join('purchase_sundries','purchases.id','=','purchase_sundries.purchase_id')
         ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_sundry)>0){
         foreach ($purchase_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_sundry_amt = $tot_purchase_sundry_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_sundry_amt = $tot_purchase_sundry_amt - $value->amount;
            }
         }
      }
      //Sale
      $tot_sale_amt = DB::table('sales')
         ->join('sale_descriptions','sales.id','=','sale_descriptions.sale_id')
         ->where(['sales.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $tot_sale_sundry_amt = 0;
      $sale_sundry = DB::table('sales')
         ->join('sale_sundries','sales.id','=','sale_sundries.sale_id')
         ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales.delete' => '0', 'sales.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_sundry)>0){
         foreach ($sale_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_sundry_amt = $tot_sale_sundry_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_sundry_amt = $tot_sale_sundry_amt - $value->amount;
            }
         }
      }
      //Purchase Return
      $tot_purchase_return_amt = DB::table('purchase_returns')
         ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
         ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $purchase_return_sundry = DB::table('purchase_returns')
         ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
         ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE','adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_return_sundry)>0){
         foreach ($purchase_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt - $value->amount;
            }
         }
      }
   
      //Sale Return Type Purchase
      $tot_sale_return_amt_purchase = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $sale_return_sundry_purchase = DB::table('sales_returns')
         ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
         ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_return_sundry_purchase)>0){
         foreach ($sale_return_sundry_purchase as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase - $value->amount;
            }
         }
      }
      //Sale Return
      $tot_sale_return_amt = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $sale_return_sundry = DB::table('sales_returns')
         ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
         ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_return_sundry)>0){
         foreach ($sale_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_return_amt = $tot_sale_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_return_amt = $tot_sale_return_amt - $value->amount;
            }
         }
      }
      //Purchase Return Type Sale
      $tot_purchase_return_amt_sale = DB::table('purchase_returns')
         ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
         ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $purchase_return_sundry_sale = DB::table('purchase_returns')
         ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
         ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE','adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_return_sundry_sale)>0){
         foreach ($purchase_return_sundry_sale as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale - $value->amount;
            }
         }
      }
      //Direct Expensess
      $direct_expenses_account_id = Accounts::where('under_group','12')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','12')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $group_ids = $this->getAllChildGroupIds(12,Session::get('user_company_id'));
      $group_ids[] = 12; // Include current group
      $direct_expenses_account_id1 = Accounts::whereIn('under_group',$group_ids)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $direct_expenses_account_id = $direct_expenses_account_id->merge($direct_expenses_account_id1);      
      $direct_expenses = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      $direct_expenses_credit = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      //InDirect Expensess
      $indirect_expenses_account_id = Accounts::where('under_group','15')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','15')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $group_ids = $this->getAllChildGroupIds(15,Session::get('user_company_id'));
      $group_ids[] = 15; // Include current group

      $indirect_expenses_account_id1 = Accounts::whereIn('under_group',$group_ids)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $indirect_expenses_account_id = $indirect_expenses_account_id->merge($indirect_expenses_account_id1);   
      $indirect_expenses = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      $indirect_expenses_credit = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      //Direct Income
      $direct_income_account_id = Accounts::where('under_group','13')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','13')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $group_ids = $this->getAllChildGroupIds(13,Session::get('user_company_id'));
      $group_ids[] = 13; // Include current group
      $direct_income_account_id1 = Accounts::whereIn('under_group',$group_ids)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      
      $direct_income_account_id = $direct_income_account_id->merge($direct_income_account_id1);  
      $direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      $debit_direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      //InDirect Income
      $indirect_income_account_id = Accounts::where('under_group','14')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','14')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $group_ids = $this->getAllChildGroupIds(14,Session::get('user_company_id'));
      $group_ids[] = 14; // Include current group
      $indirect_income_account_id1 = Accounts::whereIn('under_group',$group_ids)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      
      $indirect_income_account_id = $indirect_income_account_id->merge($indirect_income_account_id1);  
      $indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      $debit_indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->where(function($q) use ($from_date, $to_date) {
                        $q->whereBetween('txn_date', [$from_date, $to_date])
                           ->orWhere('entry_type', '-1'); // <-- your OR condition here
                     })
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      //Opening Stock 
      $opening_stock = 0;    
      $previous_date = Carbon::parse($from_date)->subDay();
      $opening_stock = CommonHelper::ClosingStock($previous_date);
      $closing_stock = CommonHelper::ClosingStock($to_date);
      $closing_stock = round($closing_stock,2);
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])
                                ->where('delete', '=', '0')
                                ->orderBy('account_name')
                                ->get();
      //Check Profit & Loss Account Entry
      $journal = Journal::with(['journal_details'=>function($q){
                                 $q->select('journal_id','type','journal_details.account_name','debit','credit','narration');
                                 $q->with(['account_details'=>function($q1){
                                    $q1->select('id','accounts.account_name');
                                 }]);
                              }])
               ->where('journals.company_id',Session::get('user_company_id'))
               ->where('journals.financial_year',Session::get('default_fy'))
               ->where('form_source','profitloss')
               ->select('journals.id','series_no','voucher_no','long_narration')
               ->get();
      return  view('display/profitLoss')->with('data', ['tot_purchase_amt' => $tot_purchase_amt+$tot_purchase_sundry_amt, 'tot_sale_amt' => $tot_sale_amt+$tot_sale_sundry_amt, 'tot_purchase_return_amt' => $tot_purchase_return_amt,'tot_sale_return_amt_purchase'=>$tot_sale_return_amt_purchase, 'tot_sale_return_amt' => $tot_sale_return_amt,'tot_purchase_return_amt_sale'=>$tot_purchase_return_amt_sale,'financial_year' => $financial_year,'direct_expenses' => $direct_expenses,'direct_income' => $direct_income,'opening_stock' => $opening_stock,'closing_stock' => $closing_stock,'indirect_expenses' => $indirect_expenses,'indirect_income' => $indirect_income,'series'=>''])->with('from_date',$from_date)->with('to_date',$to_date)->with('opening_stock',$opening_stock)->with('indirect_expenses_credit',$indirect_expenses_credit)->with('direct_expenses_credit',$direct_expenses_credit)->with('debit_indirect_income',$debit_indirect_income)->with('debit_direct_income',$debit_direct_income)->with('current_year',$current_year)->with('mat_series',$mat_series)->with('party_list',$party_list)->with('journal',$journal);
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
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      $req_series = $request->series;
      //Opening Stock 
      $opening_stock = 0;     
      $previous_date = Carbon::parse($from_date)->subDay();
      $opening_stock = CommonHelper::ClosingStock($previous_date, $req_series);  
      //Purchase
      
      $tot_purchase_amt = DB::table('purchases')
         ->join('purchase_descriptions','purchases.id','=','purchase_descriptions.purchase_id')
         ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchases.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $tot_purchase_sundry_amt = 0;
      $purchase_sundry = DB::table('purchases')
         ->join('purchase_sundries','purchases.id','=','purchase_sundries.purchase_id')
         ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchases.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_sundry)>0){
         foreach ($purchase_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_sundry_amt = $tot_purchase_sundry_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_sundry_amt = $tot_purchase_sundry_amt - $value->amount;
            }
         }
      }
      //Sale
      $tot_sale_amt = DB::table('sales')
         ->join('sale_descriptions','sales.id','=','sale_descriptions.sale_id')
         ->where(['sales.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $tot_sale_sundry_amt = 0;
      $sale_sundry = DB::table('sales')
         ->join('sale_sundries','sales.id','=','sale_sundries.sale_id')
         ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales.delete' => '0', 'sales.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_sundry)>0){
         foreach ($sale_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_sundry_amt = $tot_sale_sundry_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_sundry_amt = $tot_sale_sundry_amt - $value->amount;
            }
         }
      }
      //Purchase Return
      $tot_purchase_return_amt = DB::table('purchase_returns')
         ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
         ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchase_returns.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $purchase_return_sundry = DB::table('purchase_returns')
         ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
         ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchase_returns.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_return_sundry)>0){
         foreach ($purchase_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt - $value->amount;
            }
         }
      }
       //Sale Return pURCHASE
      $tot_sale_return_amt_purchase = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales_returns.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $sale_return_sundry = DB::table('sales_returns')
         ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
         ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'PURCHASE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales_returns.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_return_sundry)>0){
         foreach ($sale_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_return_amt_purchase = $tot_sale_return_amt_purchase - $value->amount;
            }
         }
      }
      //Sale Return
      $tot_sale_return_amt = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales_returns.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $sale_return_sundry = DB::table('sales_returns')
         ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
         ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('sales_returns.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_return_sundry)>0){
         foreach ($sale_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_return_amt = $tot_sale_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_return_amt = $tot_sale_return_amt - $value->amount;
            }
         }
      }
      //Purchase Return Type Sale
      $tot_purchase_return_amt_sale = DB::table('purchase_returns')
         ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
         ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchase_returns.series_no', $req_series);
         })
         ->get()
         ->sum("amount");
      $purchase_return_sundry = DB::table('purchase_returns')
         ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
         ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes','voucher_type'=>'SALE'])
         ->whereBetween('date', [$from_date, $to_date])
         ->when(!empty($req_series), function ($query) use ($req_series) {
            return $query->where('purchase_returns.series_no', $req_series);
         })
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_return_sundry)>0){
         foreach ($purchase_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_return_amt_sale = $tot_purchase_return_amt_sale - $value->amount;
            }
         }
      }      
      //Direct Expensess
      $direct_expenses_account_id = Accounts::where('under_group','12')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','12')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $direct_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $direct_expenses_account_id = $direct_expenses_account_id->merge($direct_expenses_account_id1);      
      $direct_expenses = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('debit');
      $direct_expenses_credit = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('credit');
      //InDirect Expensess
      $indirect_expenses_account_id = Accounts::where('under_group','15')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','15')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $indirect_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $indirect_expenses_account_id = $indirect_expenses_account_id->merge($indirect_expenses_account_id1);   
      $indirect_expenses = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('debit');
      $indirect_expenses_credit = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('credit');
      //Direct Income
      $direct_income_account_id = Accounts::where('under_group','13')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','13')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $direct_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $direct_income_account_id = $direct_income_account_id->merge($direct_income_account_id1);  
      $direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('credit');
      $debit_direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('debit');
      //InDirect Income
      $indirect_income_account_id = Accounts::where('under_group','14')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','14')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $indirect_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $indirect_income_account_id = $indirect_income_account_id->merge($indirect_income_account_id1);  
      $indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('credit');
      $debit_indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->when(!empty($req_series), function ($query) use ($req_series) {
                     return $query->where('account_ledger.series_no', $req_series);
                  })
                  ->sum('debit');
      $closing_stock = CommonHelper::ClosingStock($to_date,$req_series);
      $closing_stock = round($closing_stock,2);

      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])
                                ->where('delete', '=', '0')
                                ->orderBy('account_name')
                                ->get();
      //Check Profit & Loss Account Entry
      $journal = Journal::with(['journal_details'=>function($q){
                           $q->select('journal_id','type','journal_details.account_name','debit','credit','narration');
                           $q->with(['account_details'=>function($q1){
                              $q1->select('id','accounts.account_name');
                           }]);
                        }])
                  ->where('journals.company_id',Session::get('user_company_id'))
                  ->where('journals.financial_year',Session::get('default_fy'))
                  ->where('form_source','profitloss')
                  ->select('journals.id','series_no','voucher_no','long_narration')
                  ->get();
      return  view('display/profitLoss')->with('data', ['tot_purchase_amt' => $tot_purchase_amt+$tot_purchase_sundry_amt, 'tot_sale_amt' => $tot_sale_amt+$tot_sale_sundry_amt, 'tot_purchase_return_amt' => $tot_purchase_return_amt, 'tot_sale_return_amt' => $tot_sale_return_amt, 'financial_year' => $financial_year,'direct_expenses' => $direct_expenses,'direct_income' => $direct_income,'opening_stock' => $opening_stock,'closing_stock' => $closing_stock,'indirect_expenses' => $indirect_expenses,'indirect_income' => $indirect_income,'series'=>$req_series,'tot_purchase_return_amt_sale'=>$tot_purchase_return_amt_sale,'tot_sale_return_amt_purchase'=>$tot_sale_return_amt_purchase,])->with('from_date',$from_date)->with('to_date',$to_date)->with('opening_stock',$opening_stock)->with('indirect_expenses_credit',$indirect_expenses_credit)->with('direct_expenses_credit',$direct_expenses_credit)->with('debit_indirect_income',$debit_indirect_income)->with('debit_direct_income',$debit_direct_income)->with('current_year',$current_year)->with('mat_series',$mat_series)->with('party_list',$party_list)->with('journal',$journal);
   }
   public function saleByMonth(Request $request,$financial_year){
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      $data = [];$total_debit = 0;$total_credit = 0;$opning_bal = 0;
      $month_arr = array("04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December","01"=>"January","02"=>"February","03"=>"March");
      foreach($month_arr as $key => $value){
         $date = $y[0]."-".$key."-01";
         if($key<=3){
            $date = $y[1]."-".$key."-01";
         }
         $date = date('Y-m',strtotime($date));
         //Sale
         $tot_sale_amt = DB::table('sales')
         ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->where('date','like',"{$date}%")
         ->get()
         ->sum("taxable_amt");
         //Sale Return
         $tot_sale_return_amt = DB::table('sales_returns')
         ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->where('date','like',"{$date}%")
         ->get()
         ->sum("taxable_amt");
         $opning_bal = $opning_bal + $tot_sale_return_amt - $tot_sale_amt;
         if($opning_bal<0){ 
            $balance = str_replace("-","",number_format($opning_bal,2)).' Cr'; 
         }else{ 
            $balance = number_format($opning_bal,2)." Dr";
         }
         array_push($data,array("month"=>$value,"debit"=>number_format($tot_sale_return_amt,2),"credit"=>number_format($tot_sale_amt,2),"balance"=>$balance,"date"=>$date));
         $total_debit = $total_debit + $tot_sale_return_amt;
         $total_credit = $total_credit + $tot_sale_amt;
         if($key==date('m') && $current_year==$financial_year){
            break;
         }
      }
      return  view('display/sale-by-month')->with('data',$data)->with('total_debit',number_format($total_debit,2))->with('total_credit',number_format($total_credit,2));
   }
   public function saleByMonthDetail(Request $request,$financial_year,$from_date,$to_date,$search_type=null){
      //Sale Data
      $sale = Sales::with(['saleSundry','account' => function($q) {
                                 $q->select('id', 'account_name');
                              }])                     
                     ->withSum('saleDescription', 'amount')
                     ->where('sales.delete','0')
                     ->where('sales.company_id',Session::get('user_company_id'))
                     ->whereBetween('sales.date', [$from_date, $to_date])
                     ->orderBy('sales.date')
                     ->orderBy('sales.voucher_no')
                     ->get(); 
      //Sale Return Data
      $sale_return = SalesReturn::with([
                                       'saleReturnSundry',
                                       'account' => function($q) {
                                          $q->select('id', 'account_name');
                                       }
                                    ])
                        ->withSum('saleReturnDescriptions', 'amount')
                        ->where('sales_returns.delete', '0')
                        ->where('sales_returns.voucher_type', 'SALE')
                        ->where('sales_returns.company_id', Session::get('user_company_id'))
                        ->whereBetween('sales_returns.date', [$from_date, $to_date])
                        ->get();
      //Purchase Return Data
      $purchase_return = PurchaseReturn::with([
                                       'purchaseReturnSundry',
                                       'account' => function($q) {
                                          $q->select('id', 'account_name');
                                       }
                                    ])
                        ->withSum('purchaseReturnDescription', 'amount')
                        ->where('purchase_returns.delete', '0')
                        ->where('purchase_returns.voucher_type', 'SALE')
                        ->where('purchase_returns.company_id', Session::get('user_company_id'))
                        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
                        ->get();
      
      $bill_sundray = BillSundrys::where('company_id',Session::get('user_company_id'))
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->orderBy('sequence')
                                       ->get();
      return view('display/sale-by-month-detail')->with('sale',$sale)->with('from_date',$from_date)->with('to_date',$to_date)->with('selected_year',$financial_year)->with('bill_sundray',$bill_sundray)->with('purchase_return',$purchase_return)->with('sale_return',$sale_return)->with("search_type",$search_type);
   }
   public function purchaseByMonth(Request $request,$financial_year){
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      $data = [];$total_debit = 0;$total_credit = 0;$opning_bal = 0;
      $month_arr = array("04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December","01"=>"January","02"=>"February","03"=>"March");
      foreach($month_arr as $key => $value){
         $date = $y[0]."-".$key."-01";
         if($key<=3){
            $date = $y[1]."-".$key."-01";
         }
         $date = date('Y-m',strtotime($date));
         //Sale
         $tot_purchase_amt = DB::table('purchases')
         ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->where('date','like',"{$date}%")
         ->get()
         ->sum("taxable_amt");
         //Sale Return
         $tot_purchase_return_amt = DB::table('purchase_returns')
         ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->where('date','like',"{$date}%")
         ->get()
         ->sum("taxable_amt");
         $opning_bal = $opning_bal + $tot_purchase_return_amt - $tot_purchase_amt;
         if($opning_bal<0){ 
            $balance = str_replace("-","",number_format($opning_bal,2)).' Cr'; 
         }else{ 
            $balance = number_format($opning_bal,2)." Dr";
         }
         array_push($data,array("month"=>$value,"debit"=>number_format($tot_purchase_amt,2),"credit"=>number_format($tot_purchase_return_amt,2),"balance"=>$balance,"date"=>$date));
         $total_debit = $total_debit + $tot_purchase_amt;
         $total_credit = $total_credit + $tot_purchase_return_amt;
         if($key==date('m') && $current_year==$financial_year){
            break;
         }
      }
      return  view('display/purchase_by_month')->with('data',$data)->with('total_debit',number_format($total_debit,2))->with('total_credit',number_format($total_credit,2));
   }
   public function purchaseByMonthDetail(Request $request,$financial_year,$from_date,$to_date,$search_type=null){     
      //Purchase Data
      $purchase = Purchase::with([
                           'purchaseSundry',
                              'account' => function($q) {
                                 $q->select('id', 'account_name');
                              }
                           ])
                     ->withSum('purchaseDescription', 'amount')
                     ->where('purchases.delete', '0')
                     ->where('purchases.company_id', Session::get('user_company_id'))
                     ->whereBetween('purchases.date', [$from_date, $to_date])
                     ->get();
      //Purchase Return Data
      $purchase_return = PurchaseReturn::with([
                                       'purchaseReturnSundry',
                                       'account' => function($q) {
                                          $q->select('id', 'account_name');
                                       }
                                    ])
                        ->withSum('purchaseReturnDescription', 'amount')
                        ->where('purchase_returns.delete', '0')
                        ->where('purchase_returns.voucher_type', 'PURCHASE')
                        ->where('purchase_returns.company_id', Session::get('user_company_id'))
                        ->whereBetween('purchase_returns.date', [$from_date, $to_date])
                        ->get();
      $sale_return = SalesReturn::with([
                                       'saleReturnSundry',
                                       'account' => function($q) {
                                          $q->select('id', 'account_name');
                                       }
                                    ])
                        ->withSum('saleReturnDescriptions', 'amount')
                        ->where('sales_returns.delete', '0')
                        ->where('sales_returns.voucher_type', 'PURCHASE')
                        ->where('sales_returns.company_id', Session::get('user_company_id'))
                        ->whereBetween('sales_returns.date', [$from_date, $to_date])
                        ->get();
      $bill_sundray = BillSundrys::where('company_id',Session::get('user_company_id'))
                                       ->where('delete','0')
                                       ->where('status','1')
                                    ->orderBy('sequence')
                                    ->get();
     
        
      return view('display/purchase_by_month_detail')->with('purchase',$purchase)->with('from_date',$from_date)->with('to_date',$to_date)->with('selected_year',$financial_year)->with('bill_sundray',$bill_sundray)->with('purchase_return',$purchase_return)->with('sale_return',$sale_return)->with("search_type",$search_type);
   }
   public function accountBalanceByGroup(Request $request,$id,$financial_year,$from_date,$to_date){
      $type = 'debit';
      if($id==13 || $id==14){
         $type = 'credit';
      }
      $group = AccountGroups::select('name')
                              ->where('id',$id)
                              ->first();
      $account_group = AccountGroups::select('id','name as account_name')->where('heading',$id)
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->where('delete','0')
                     ->where('status','1')
                     ->get();
      // echo "<pre>";
      // print_r($account_group->toArray());
      // echo "</pre>";
      foreach ($account_group as $key => $group) {
            $company_id = Session::get('user_company_id');

            // 1. Get current + child group IDs
            $group_ids = $this->getAllChildGroupIds($group->id, $company_id);
            $group_ids[] = $group->id; // Include current group

            // 2. Get all accounts under these groups
            $account_ids = Accounts::whereIn('under_group', $group_ids)
                           ->where('accounts.delete', '0')
                           ->whereIn('accounts.company_id', [$company_id, 0])
                           ->pluck('id');

            // 3. Get debit and credit sums
            $debit_sum = AccountLedger::whereIn('account_id', $account_ids)
                           ->where('financial_year', $financial_year)
                           ->where('delete_status', '0')
                           ->whereBetween('txn_date', [$from_date, $to_date])
                           ->whereIn('company_id', [$company_id, 0])
                           ->orWhere(function($query) use ($account_ids) {
                                 $query->whereIn('account_id', $account_ids)
                                       ->where('entry_type', '-1');
                           })
                           ->sum('debit');

            $credit_sum = AccountLedger::whereIn('account_id', $account_ids)
                           ->where('financial_year', $financial_year)
                           ->where('delete_status', '0')
                           ->whereBetween('txn_date', [$from_date, $to_date])
                           ->whereIn('company_id', [$company_id, 0])
                           ->orWhere(function($query) use ($account_ids) {
                                 $query->whereIn('account_id', $account_ids)
                                       ->where('entry_type', '-1');
                           })
                           ->sum('credit');

            // 4. Assign
            $account_group[$key]->account_ledger_sum_debit = $debit_sum;
            $account_group[$key]->account_ledger_sum_credit = $credit_sum;
            $account_group[$key]->type = 1;
         }

      // foreach ($account_group as $key => $value) {
      //    $account_id = Accounts::where('under_group',$value->id)
      //                            ->where('accounts.delete','0')
      //                            ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
      //                            ->pluck('id');
         
      //    $debit_sum = AccountLedger::whereIn('account_id',$account_id)
      //                   ->where('financial_year',$financial_year)
      //                   ->where('delete_status','0')
      //                   ->whereBetween('txn_date', [$from_date, $to_date])
      //                   ->whereIn('company_id',[Session::get('user_company_id'),0])
      //                   ->orWhere(function($query)use($account_id) {
      //                      $query->whereIn('account_id',$account_id)
      //                      ->Where('entry_type','-1');
      //                   }) 
      //                   ->sum('debit');
      //    $credit_sum = AccountLedger::whereIn('account_id',$account_id)
      //                   ->where('financial_year',$financial_year)
      //                   ->where('delete_status','0')
      //                   ->whereBetween('txn_date', [$from_date, $to_date])
      //                   ->whereIn('company_id',[Session::get('user_company_id'),0])
      //                   ->orWhere(function($query)use($account_id) {
      //                      $query->whereIn('account_id',$account_id)
      //                      ->Where('entry_type','-1');
      //                   })
      //                   ->sum('credit');



      //    $account_group[$key]->account_ledger_sum_debit = $debit_sum;
      //    $account_group[$key]->account_ledger_sum_credit = $credit_sum;
      //    $account_group[$key]->type = 1;
      // }
      
      $account = Accounts::withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where('financial_year', $financial_year);
                              $query->whereBetween('txn_date', [$from_date, $to_date]);
                              $query->where('delete_status','0');    
                              $query->whereIn('company_id',[Session::get('user_company_id'),0]);                       
                              $query->orWhere(function($q1)use($financial_year,$from_date, $to_date) {
                                 $q1->Where('entry_type','-1');
                                 $q1->where('financial_year', $financial_year);
                                 $q1->whereBetween('txn_date', [$from_date, $to_date]);
                                 $q1->where('delete_status','0');
                                 $q1->whereIn('company_id',[Session::get('user_company_id'),0]); 
                              });
                            }], 'debit')
                           ->withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where('financial_year', $financial_year);
                              $query->whereBetween('txn_date', [$from_date, $to_date]);
                              $query->where('delete_status','0');
                              $query->whereIn('company_id',[Session::get('user_company_id'),0]);  
                              $query->orWhere(function($q1)use($financial_year,$from_date, $to_date) {
                                 $q1->Where('entry_type','-1');
                                 $q1->where('financial_year', $financial_year);
                                 $q1->whereBetween('txn_date', [$from_date, $to_date]);
                                 $q1->where('delete_status','0');  
                              });
                            }], 'credit')
                           ->where('under_group',$id)
                           ->where('accounts.delete','0')                           
                           ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get();
            // echo "<pre>";
            //       print_r($account->toArray());
            //       echo "</pre>";
            //       exit;
      $account = $account->merge($account_group); 
      return view('display/account_balance_by_group')->with('data',$account)->with('group',$group)->with('financial_year',$financial_year)->with('type',$type)->with('from_date',$from_date)->with('to_date',$to_date);
   }
   function getAllChildGroupIds($group_id, $company_id) {
      $child_ids = AccountGroups::where('heading', $group_id)
                     ->where('delete', '0')
                     ->whereIn('company_id', [$company_id, 0])
                     ->pluck('id')
                     ->toArray();

      $all_ids = $child_ids;

      foreach ($child_ids as $child_id) {
         $all_ids = array_merge($all_ids, $this->getAllChildGroupIds($child_id, $company_id));
      }

      return $all_ids;
   }
   public function accountMonthlySummary(Request $request,$id,$financial_year){
      $y = explode("-",$financial_year);
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      $data = [];$total_debit = 0;$total_credit = 0;$opning_bal = 0;
      $month_arr = array("04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December","01"=>"January","02"=>"February","03"=>"March");
      foreach($month_arr as $key => $value){
         $date = $y[0]."-".$key."-01";
         if($key<=3){
            $date = $y[1]."-".$key."-01";
         }
         $from_date = $date;
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = date('Y-m-t',strtotime($date));
         $date = date('Y-m',strtotime($date));
         //Ledger
         $credit = DB::table('account_ledger')
         ->where(['delete_status' => '0', 'company_id' => Session::get('user_company_id')])
         ->where('txn_date','like',"{$date}%")
         ->where('account_id',$id)
         ->get()
         ->sum("credit");
         $debit = DB::table('account_ledger')
         ->where(['delete_status' => '0', 'company_id' => Session::get('user_company_id')])
         ->where('txn_date','like',"{$date}%")
         ->where('account_id',$id)
         ->get()
         ->sum("debit");
         
         $balance = $opning_bal + $debit - $credit;         
         $opning_bal = $opning_bal + $balance;
         array_push($data,array("month"=>$value,"debit"=>number_format($debit,2),"credit"=>number_format($credit,2),"balance"=>$balance,"from_date"=>$from_date,"to_date"=>$to_date));
         $total_debit = $total_debit + $debit;
         $total_credit = $total_credit + $credit;
         if($key==date('m') && $current_year==$financial_year){
            break;
         }
      }  
      return view('display/account_monthly_summary')->with('data',$data)->with('financial_year',$financial_year)->with('total_debit',$total_debit)->with('total_credit',$total_credit)->with("account_id",$id);
   }
   public function moneyFormat($number){
      $decimal = (string)($number - floor($number));
              $money = floor($number);
              $length = strlen($money);
              $delimiter = '';
              $money = strrev($money);

              for($i=0;$i<$length;$i++){
                  if(( $i==3 || ($i>3 && ($i-1)%2==0) )&& $i!=$length){
                      $delimiter .=',';
                  }
                  $delimiter .=$money[$i];
              }

              $result = strrev($delimiter);
              $decimal = preg_replace("/0\./i", ".", $decimal);
              $decimal = substr($decimal, 0, 3);

              if( $decimal != '0'){
                  $result = $result.$decimal;
              }

              return $result;
   }
}
