<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\ItemLedger;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\StockJournal;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use DB;
use Session;

class ItemLedgerController extends Controller
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
      $item_list = ManageItems::join('units', 'units.id', '=', 'manage_items.u_name')
                                ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                                ->where(['manage_items.delete' => '0', 'manage_items.company_id' => Session::get('user_company_id')])
                                ->select(['manage_items.id','manage_items.name'])
                                ->orderBy('manage_items.name')
                                ->get();
      $items = array();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }         
      }
      return view('itemledger')->with('item_list', $item_list)->with('items', $items)->with('opening', 0)->with('fdate', $fdate)->with('tdate',$tdate)->with('series',$series);
   }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

   public function filter(Request $request){
      $item_id = $request->items_id;
      $selected_series = $request->selected_series;


      if($selected_series!="all" && $item_id!="all" ){
          $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
        return redirect(url('item-ledger-average?items_id=' . $request->items_id . '&from_date=' . $fdate . '&to_date=' . $tdate . '&series=' . $request->selected_series));
      }

      
      if($selected_series=="all"){
         $selected_series_query = "";
      }else{
         $selected_series_query = " and item_ledger.series_no='".$selected_series."'";
      }
      $item_list = ManageItems::join('units', 'units.id', '=', 'manage_items.u_name')
                              ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                              ->where(['manage_items.delete' => '0', 'manage_items.company_id' => Session::get('user_company_id')])
                              ->select(['manage_items.id','manage_items.name'])
                              ->orderBy('manage_items.name')
                              ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }         
      }
      $series_list = $series;



      //my new code


      if($item_id!="all" && $selected_series=="all"){
           $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
      return redirect(url('item-ledger-average-by-godown?items_id=' . $request->items_id . '&from_date=' . $fdate . '&to_date=' . $tdate));
      }
      
     
      if($item_id=="all"){
         $opening = 0;
         $financial_year = Session::get('default_fy');
         $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
         $y =  explode("-",$financial_year);
         $open_date = $y[0]."-04-01";
         $open_date = date('Y-m-d',strtotime($open_date));

         if($request->selected_series!="all"){
            $series_list = [];              
            $object = new \stdClass();
            $object->series = $request->selected_series;
            $series_list[] = $object;            
         }         
         $allArrays = [];
         foreach ($series_list as $s1 => $s) {           
            $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
                                       ->join('units', 'manage_items.u_name', '=', 'units.id')
                                       ->select('item_id','in_weight as average_weight','txn_date as stock_date','total_price as amount','manage_items.name as item_name','units.s_name as unit_name')
                              ->where('item_ledger.company_id',Session::get('user_company_id'))
                              ->where('source','-1')
                              ->where('series_no',$s->series)
                              ->where('delete_status','0')
                              ->groupBy('item_id')
                              ->get();

            $sub = DB::table('item_averages')
                  ->select(DB::raw('MAX(id) as latest_id'))
                  ->where('stock_date', '<=', $request->to_date)
                  ->where('series_no',$s->series)
                  ->where('company_id',Session::get('user_company_id'))
                  ->groupBy('item_id');
            $item = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
                  ->join('units', 'manage_items.u_name', '=', 'units.id')
                  ->whereIn('item_averages.id', $sub)
                  ->where('series_no',$s->series)
                  ->select(
                  'item_averages.item_id',
                  'item_averages.average_weight',
                  'item_averages.amount',
                  'item_averages.stock_date',
                  'manage_items.name as item_name',
                  'units.s_name as unit_name'
                  )
                  ->orderBy('stock_date', 'desc')
                  ->get();
         //          echo "<pre>";
         // print_r($item->toArray());
            foreach ($item_ledger as $key => $value) {  
               if(count($item)==0){
                  $item->push($value);
                  continue;
               }    
               $exists = 0;  
               $exists = $item->contains(function ($row)use ($value,$item) {
                  if ($row['item_id']==$value['item_id']) {
                     return 1;
                  }               
               });            
               if ($exists==0) {
                  $item->push($value);
               }
            }            
            array_push($allArrays, $item->toArray());
            // Initialize result array            
         }       
         $result = [];
         
         foreach ($allArrays as $array) {
            foreach ($array as $item) {
               $id = $item['item_id'];
               if (!isset($result[$id])) {
                     // Initialize if not set
                     $result[$id] = $item;
               } else {
                     // Merge values
                     $result[$id]['average_weight'] += $item['average_weight'];
                     $result[$id]['amount'] += $item['amount'];
               }
            }
         }
         //die;
         // Re-index array
         $result = array_values($result);
         
         return view('itemledger')->with('item_list', $item_list)->with('items', collect($result))->with('item_id', $item_id)->with('opening', $opening)->with('fdate', $open_date)->with('tdate',$tdate)->with('series',$series)->with('selected_series',$selected_series);
      }
      //Particular Item Ledger

      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
         $item = DB::select(DB::raw("SELECT * FROM item_ledger WHERE item_id='".$item_id."' and source!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status=1 and delete_status='0' ".$selected_series_query." order by STR_TO_DATE(txn_date, '%Y-%m-%d')"));
      }     
      if(count($item)>0){
         foreach ($item as $key => $value) {
            if($value->source==1){
               $action = Sales::join('accounts',"sales.party","=","accounts.id")
               ->where('sales.id',$value->source_id)
               ->select(['voucher_no','account_name','sales.series_no','sales.financial_year','e_invoice_status','e_waybill_status'])
               ->first();
               $item[$key]->einvoice_status = 0;
               $item[$key]->bill_no = $action->series_no."/".$action->financial_year."/".$action->voucher_no;
               $item[$key]->account_name = $action->account_name;
               $item[$key]->type = "SupO";
               if($action->e_invoice_status==1 || $action->e_waybill_status==1){
                  $item[$key]->einvoice_status = 1;
               }
            }else if($value->source==2){
               $action = Purchase::join('accounts',"purchases.party","=","accounts.id")
               ->where('purchases.id',$value->source_id)
               ->select(['voucher_no','account_name'])
               ->first();
               if($action){
                  $item[$key]->bill_no = $action->voucher_no;
                  $item[$key]->account_name = $action->account_name;
                  $item[$key]->type = "SupI";
               }else{
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = "";
                  $item[$key]->type = "";
               }
               $item[$key]->einvoice_status = 0;
            }else if($value->source==3){
               $action = StockJournal::where('stock_journal.id',$value->source_id)
               ->select(['narration'])
               ->first();
               if($action){
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = $action->narration;
                  $item[$key]->type = "Stock Journal";
               }else{
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = "";
                  $item[$key]->type = "";
               }  
               $item[$key]->einvoice_status = 0;
            }else if($value->source==4){
               $action = SalesReturn::join('accounts',"sales_returns.party","=","accounts.id")
                           ->where('sales_returns.id',$value->source_id)
                           ->select(['sr_prefix','account_name'])
                           ->first();
               if($action){
                  $item[$key]->bill_no = $action->sr_prefix;
                  $item[$key]->account_name = $action->account_name;
                  $item[$key]->type = "Sale Return";
               }else{
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = "";
                  $item[$key]->type = "Sale Return";
               }                  
               $item[$key]->einvoice_status = 0; 
            }else if($value->source==5){
               $action = PurchaseReturn::join('accounts',"purchase_returns.party","=","accounts.id")
                           ->where('purchase_returns.id',$value->source_id)
                           ->select(['sr_prefix','account_name'])
                           ->first();
               if($action){
                  $item[$key]->bill_no = $action->sr_prefix;
                  $item[$key]->account_name = $action->account_name;
                  $item[$key]->type = "Purchase Return";
               }else{
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = "";
                  $item[$key]->type = "Purchase Return";
               } 
               $item[$key]->einvoice_status = 0; 
            }else if($value->source==6){
                $action = StockTransfer::find($value->source_id);
                if($action){
                  $item[$key]->bill_no = $action->voucher_no_prefix;
                  if(!empty($value->in_weight)){
                      $item[$key]->account_name = $action->material_center_from;
                  }else if(!empty($value->out_weight)){
                      $item[$key]->account_name = $action->material_center_to;
                  }
                  
                  $item[$key]->type = "Stock Transfer";
               }else{
                  $item[$key]->bill_no = "";
                  $item[$key]->account_name = "";
                  $item[$key]->type = "Stock Transfer";
               }
               $item[$key]->einvoice_status = 0;
            }else{
               $item[$key]->bill_no = "";
               $item[$key]->account_name = "";
               $item[$key]->type = "";
               $item[$key]->einvoice_status = 0;
            }
         }
      }
      //Opening Balance
      $opening = 0;
      if(isset($request->from_date) && !empty($request->from_date)){ 
            $from_date = date('Y-m-d', strtotime($request->from_date . ' -1 day'));
            $open_ledger = DB::select(DB::raw("SELECT SUM(in_weight) as debit,SUM(out_weight) as credit FROM item_ledger WHERE item_id='".$item_id."' and ( STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$from_date."', '%Y-%m-%d') || source='-1') and status=1 and delete_status='0' ".$selected_series_query.""));         
         if(count($open_ledger)>0){
               $opening = $open_ledger[0]->debit - $open_ledger[0]->credit;
         }
      }
      $collection = new Collection($item);
      //$item = $collection->sortByDesc('date');
      $item = json_decode($collection, true);
      // echo "<pre>";
      // print_r($item);die;
      return view('itemledger')->with('item_list', $item_list)->with('items', $item)->with('item_id', $item_id)->with('opening', $opening)->with('series',$series)->with('selected_series',$selected_series);
   }
   public function itemLedgerAverage(Request $request){
     
       $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }
      }
      $item_id = "";
      if(isset($request->items_id)){
         $item_id = $request->items_id;
      }
      $financial_year = Session::get('default_fy');
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){ 
         $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
      }else{
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
      }
      $item_list = ManageItems::join('units', 'units.id', '=', 'manage_items.u_name')
                                ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                                ->where(['manage_items.delete' => '0', 'manage_items.company_id' => Session::get('user_company_id')])
                                ->select(['manage_items.id','manage_items.name'])
                                ->orderBy('manage_items.name')
                                ->get();
      $opening_amount = 0;$opening_weight = 0;      
      $average_data = ItemAverage::select('sale_weight','purchase_weight','average_weight','price','amount','stock_date')->where('item_id',$item_id)
                  ->where('series_no',$request->series)
                  ->whereRaw("STR_TO_DATE(stock_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(stock_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d')")
                  ->get();
      $average_opening = ItemAverage::where('item_id',$item_id)
                     ->where('series_no',$request->series)
                     ->where('stock_date','<',$request->from_date)
                     ->first();
      if($average_opening){
         $opening_amount = $average_opening->amount;
         $opening_weight = $average_opening->average_weight;
      }else{
         $opening = ItemLedger::where('item_id',$item_id)
                                    ->where('source','-1')
                                    ->where('series_no',$request->series)
                                    ->where('delete_status','0')
                                    ->first();
         if($opening){
            $opening_amount = $opening->total_price;
            $opening_weight = $opening->in_weight;
         }
         
      }      
      $selected_series = $request->series;
      return view('item_ledger_average')->with('item_list', $item_list)->with('fdate', $fdate)->with('tdate',$tdate)->with('item_id', $item_id)->with('opening_amount', $opening_amount)->with('opening_weight', $opening_weight)->with('average_data', $average_data)->with('selected_series', $selected_series)->with('series', $series);
   }
   public function itemAverageDetails(Request $request){
      // $average_detail = ItemAverageDetail::where('item_id',$request->items_id)
                  
      //                ->where('entry_date',$request->date)
      //                ->where('series_no',$request->series)                     
      //                ->get();
      $average_detail = ItemAverageDetail::where('item_average_details.item_id', $request->items_id)
                                          ->where('item_average_details.entry_date', $request->date)
                                          ->where('item_average_details.series_no', $request->series)
                                             // Join with Sales
                                          ->leftJoin('sales', 'item_average_details.sale_id', '=', 'sales.id')
                                          ->leftJoin('accounts as sales_account', 'sales.party', '=', 'sales_account.id')
                                             // Join with Purchases
                                          ->leftJoin('purchases', 'item_average_details.purchase_id', '=', 'purchases.id')
                                          ->leftJoin('accounts as purchase_account', 'purchases.party', '=', 'purchase_account.id')
                                             // Join with Sales Returns
                                          ->leftJoin('sales_returns', 'item_average_details.sale_return_id', '=', 'sales_returns.id')
                                          ->leftJoin('accounts as sr_account', 'sales_returns.party', '=', 'sr_account.id')
                                             // Join with Purchase Returns
                                          ->leftJoin('purchase_returns', 'item_average_details.purchase_return_id', '=', 'purchase_returns.id')
                                          ->leftJoin('accounts as pr_account', 'purchase_returns.party', '=', 'pr_account.id')
                                             // Join with Stock Transfers (out)
                                          ->leftJoin('stock_transfers as st_out', 'item_average_details.stock_transfer_id', '=', 'st_out.id')
                                             // Join with Stock Transfers (in)
                                          ->leftJoin('stock_transfers as st_in', 'item_average_details.stock_transfer_in_id', '=', 'st_in.id')
                                             // Select all fields
                                          ->select(
                                             'item_average_details.*',
                                             'sales.voucher_no_prefix as sale_voucher',
                                             'sales_account.account_name as sale_account',

                                             'purchases.voucher_no as purchase_voucher',
                                             'purchase_account.account_name as purchase_account',

                                             'sales_returns.sr_prefix as sr_voucher',
                                             'sr_account.account_name as sr_account',

                                             'purchase_returns.sr_prefix as pr_voucher',
                                             'pr_account.account_name as pr_account',

                                             'st_out.voucher_no_prefix as st_out_voucher',
                                             'st_out.material_center_from as st_ot_account',

                                             'st_in.voucher_no_prefix as st_in_voucher',
                                             'st_in.material_center_to as st_in_account'
                                             
                                          )
                                          ->get();
      $opening_amount = 0;$opening_weight = 0;
      $average_opening = ItemAverage::where('item_id',$request->items_id)
                     ->where('stock_date','<',$request->date)
                     ->where('series_no',$request->series)  
                     ->first();
      if($average_opening){
         $opening_amount = $average_opening->amount;
         $opening_weight = $average_opening->average_weight;
      }else{
         $opening = ItemLedger::where('item_id',$request->items_id)
                                    ->where('source','-1')
                                    ->where('series_no',$request->series)  
                                    ->where('delete_status','0')
                                    ->first();
         $opening_amount = $opening->total_price;
         $opening_weight = $opening->in_weight;
      }
      $response = array(
         'status' => true,
         'data' => $average_detail,
         'opening_amount' => $opening_amount,
         'opening_weight' => $opening_weight,
      );
      return json_encode($response);
   }
   public function itemLedgerAverageByGodown(Request $request){
      $item = ManageItems::find($request->items_id);
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }
      }
      $allArrays = [];
      foreach ($series as $s1 => $s) {           
         $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
                                    ->join('units', 'manage_items.u_name', '=', 'units.id')
                                    ->select('item_id','in_weight as average_weight','txn_date as stock_date','total_price as amount','manage_items.name as item_name','units.s_name as unit_name')
                           ->where('item_ledger.company_id',Session::get('user_company_id'))
                           ->where('source','-1')
                           ->where('item_id',$request->items_id)
                           ->where('series_no',$s->series)
                           ->where('delete_status','0')
                           ->get();
         $sub = DB::table('item_averages')
               ->select(DB::raw('MAX(id) as latest_id'))
               ->where('stock_date', '<=', $request->to_date)
               ->where('series_no',$s->series)
               ->where('company_id',Session::get('user_company_id'))
               ->where('item_id',$request->items_id)
               ->pluck('latest_id');
         $item1 = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
               ->join('units', 'manage_items.u_name', '=', 'units.id')
               ->whereIn('item_averages.id', $sub)
               ->where('series_no',$s->series)
               ->select(
               'item_averages.item_id',
               'item_averages.average_weight',
               'item_averages.amount',
               'item_averages.stock_date',
               'manage_items.name as item_name',
               'units.s_name as unit_name'
               )
               ->orderBy('stock_date', 'desc')
               ->get();
         foreach ($item_ledger as $key => $value) {  
            if(count($item1)==0){
               $item1->push($value);
               continue;
            }    
            $exists = 0;  
            $exists = $item1->contains(function ($row)use ($value,$item1) {
               if ($row['item_id']==$value['item_id']) {
                  return 1;
               }               
            });            
            if ($exists==0) {
               $item1->push($value);
            }
         } 
         if(count($item1)>0){
            $series[$s1]->weight = $item1[0]->average_weight;
            $series[$s1]->amount = $item1[0]->amount;
            $series[$s1]->unit = $item1[0]->unit_name;
            $series[$s1]->price = round($item1[0]->amount/$item1[0]->average_weight,6);
         }else{
            $series[$s1]->weight = "";
            $series[$s1]->amount = "";
            $series[$s1]->unit = "";
            $series[$s1]->price = "";
         }
      }
      return view('item_average_by_godown',['item'=>$item,"series"=>$series]);
   }
   
}
