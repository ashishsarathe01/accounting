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
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use DB;
use Session;
use Gate;
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
        $group_list = DB::table('item_groups')
             ->where('company_id', Session::get('user_company_id'))
             ->where('delete', '0')
             ->orderBy('group_name')
             ->get();
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
    return view('itemledger')
        ->with('item_list', $item_list)
        ->with('items', $items)
        ->with('groups', []) // IMPORTANT
        ->with('opening', 0)
        ->with('fdate', $fdate)
        ->with('tdate',$tdate)
        ->with('group_list',$group_list)
        ->with('series',$series);   
       
   }
   
   public function recalculateStock()
    {
        CommonHelper::RewriteAllItemAverage();
        return back()->with('success', 'Stock recalculated successfully');
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function filter(Request $request){
      Gate::authorize('action-module',27);
      $show_type = $request->show_type;

      if($show_type == 'all'){
         $item_id = 'all';
      }
      elseif($show_type == 'all_groups'){
         $item_id = 'all_groups';
      }
      elseif($show_type == 'item'){
         $item_id = $request->item_id;
      }
      elseif($show_type == 'group'){
         $item_id = 'all_groups'; 
      }
      else{
         $item_id = 'all'; 
      }
      $selected_series = $request->selected_series;
      $tdate = isset($request->to_date)
         ? date('Y-m-d', strtotime($request->to_date))
         : date('Y-m-d');

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
      $group_list = DB::table('item_groups')
         ->where('company_id', Session::get('user_company_id'))
         ->where('delete', '0')
         ->orderBy('group_name')
         ->get();
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
      if (
         !in_array($item_id, ['all', 'all_groups'], true) &&
            $selected_series === 'all'
      ) {
         $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
         return redirect(
            url('item-ledger-average-by-godown?items_id=' . $item_id . '&from_date=' . $fdate . '&to_date=' . $tdate)
         );
      }
      $finalItemMap = [];
      $seriesToUse = ($selected_series === 'all')
         ? $series_list
         : [(object)['series' => $selected_series]];
      $allArrays = [];
      foreach ($seriesToUse as $s) {
         $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->select(
                  'item_id',
                  'in_weight as average_weight',
                  'total_price as amount',
                  'manage_items.name as item_name',
                  'units.s_name as unit_name'
            )
            ->where('item_ledger.company_id', Session::get('user_company_id'))
            ->where('source', '-1')
            ->where('series_no', $s->series)
            ->where('delete_status', '0')
            ->orderBy('manage_items.name')
            ->get();
         $sub = DB::table('item_averages')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where('stock_date', '<=', $tdate)
            ->where('series_no', $s->series)
            ->where('company_id', Session::get('user_company_id'))
            ->groupBy('item_id');
         $avgItems = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('item_averages.id', $sub)
            ->where('series_no', $s->series)
            ->select(
                  'item_averages.item_id',
                  'item_averages.average_weight',
                  'item_averages.amount',
                  'manage_items.name as item_name',
                  'units.s_name as unit_name'
            )
            ->orderBy('manage_items.name')
            ->get();
         foreach ($item_ledger as $row) {
            if (!$avgItems->contains('item_id', $row->item_id)) {
                  $avgItems->push($row);
            }
         }
         $allArrays[] = $avgItems->toArray();
      }
      foreach ($allArrays as $array) {
         foreach ($array as $item) {
            if (!isset($finalItemMap[$item['item_id']])) {
                  $finalItemMap[$item['item_id']] = $item;
            } else {
                  $finalItemMap[$item['item_id']]['average_weight'] += $item['average_weight'];
                  $finalItemMap[$item['item_id']]['amount'] += $item['amount'];
            }
         }
      }
      if ($item_id === 'all_groups') {
         $opening = 0;
         $financial_year = Session::get('default_fy');
         $y = explode('-', $financial_year);
         $fdate = $y[0] . '-04-01';
         $groupsRaw = DB::table('item_groups')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('group_name')
            ->get();
         $groups = [];
         foreach ($groupsRaw as $grp) {
            if($show_type == 'group' && $request->group_id && $grp->id != $request->group_id){
               continue;
            }
            $items = DB::table('manage_items')
               ->join('units', 'units.id', '=', 'manage_items.u_name')
               ->where('manage_items.g_name', $grp->id)
               ->where('manage_items.delete', '0')
               ->where('manage_items.company_id', Session::get('user_company_id'))
               ->select('manage_items.id', 'manage_items.name', 'units.s_name')
               ->orderBy('manage_items.name')
               ->get();
            $groupQty = 0;
            $groupAmount = 0;
            $itemRows = [];
            $groupQty = 0;
            $groupAmount = 0;
            $itemRows = [];
            foreach ($items as $it) {
               if (!isset($finalItemMap[$it->id])) {
                  continue;
               }
               $row = $finalItemMap[$it->id];
               $groupQty += $row['average_weight'];
               $groupAmount += $row['amount'];
               $itemRows[] = [
                  'item_id' => $it->id,
                  'item_name' => $row['item_name'],
                  'average_weight' => $row['average_weight'],
                  'amount' => $row['amount'],
                  'unit_name' => $row['unit_name'],
               ];
            }
            // skip empty groups
            //   if (count($itemRows) === 0) {
            //       continue;
            //   }
            $groups[] = [
                  'group_id' => $grp->id,
                  'group_name' => $grp->group_name,
                  'qty' => $groupQty,
                  'amount' => $groupAmount,
                  'items' => $itemRows,
            ];
         }
         return view('itemledger')
            ->with('item_list', $item_list)
            ->with('groups', $groups)
            ->with('items', []) 
            ->with('item_id', $item_id)
            ->with('group_list', $group_list)
            ->with('opening', $opening)
            ->with('fdate', $fdate)
            ->with('tdate', $tdate)
            ->with('series', $series)
            ->with('selected_series', $selected_series);
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
         $result = collect($result)
            ->sortBy('item_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();         
         return view('itemledger')->with('item_list', $item_list)->with('group_list', $group_list)->with('items', collect($result))->with('item_id', $item_id)->with('opening', $opening)->with('fdate', $open_date)->with('tdate',$tdate)->with('series',$series)->with('selected_series',$selected_series);
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
      return view('itemledger')->with('item_list', $item_list)->with('items', $item)->with('group_list', $group_list)->with('item_id', $item_id)->with('opening', $opening)->with('series',$series)->with('selected_series',$selected_series);
   }
   public function filter_bkp(Request $request){
      $item_id = $request->items_id;
      $selected_series = $request->selected_series;
      $tdate = isset($request->to_date)
         ? date('Y-m-d', strtotime($request->to_date))
         : date('Y-m-d');

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


      if (
         !in_array($item_id, ['all', 'all_groups'], true) &&
            $selected_series === 'all'
      ) {
         $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
         return redirect(
            url('item-ledger-average-by-godown?items_id=' . $item_id . '&from_date=' . $fdate . '&to_date=' . $tdate)
         );
      }
      $finalItemMap = [];
      $seriesToUse = ($selected_series === 'all')
         ? $series_list
         : [(object)['series' => $selected_series]];
      $allArrays = [];
      foreach ($seriesToUse as $s) {
         $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->select(
                  'item_id',
                  'in_weight as average_weight',
                  'total_price as amount',
                  'manage_items.name as item_name',
                  'units.s_name as unit_name'
            )
            ->where('item_ledger.company_id', Session::get('user_company_id'))
            ->where('source', '-1')
            ->where('series_no', $s->series)
            ->where('delete_status', '0')
            ->get();
         $sub = DB::table('item_averages')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where('stock_date', '<=', $tdate)
            ->where('series_no', $s->series)
            ->where('company_id', Session::get('user_company_id'))
            ->groupBy('item_id');
         $avgItems = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('item_averages.id', $sub)
            ->where('series_no', $s->series)
            ->select(
                  'item_averages.item_id',
                  'item_averages.average_weight',
                  'item_averages.amount',
                  'manage_items.name as item_name',
                  'units.s_name as unit_name'
            )
            ->get();

         foreach ($item_ledger as $row) {
            if (!$avgItems->contains('item_id', $row->item_id)) {
                  $avgItems->push($row);
            }
         }

         $allArrays[] = $avgItems->toArray();
      }
      foreach ($allArrays as $array) {
         foreach ($array as $item) {
            if (!isset($finalItemMap[$item['item_id']])) {
                  $finalItemMap[$item['item_id']] = $item;
            } else {
                  $finalItemMap[$item['item_id']]['average_weight'] += $item['average_weight'];
                  $finalItemMap[$item['item_id']]['amount'] += $item['amount'];
            }
         }
      }
   if ($item_id === 'all_groups') {
      $opening = 0;
      $financial_year = Session::get('default_fy');
      $y = explode('-', $financial_year);
      $fdate = $y[0] . '-04-01';
      $groupsRaw = DB::table('item_groups')
         ->where('company_id', Session::get('user_company_id'))
         ->orderBy('group_name')
         ->get();
      $groups = [];
      foreach ($groupsRaw as $grp) {
        $items = DB::table('manage_items')
            ->join('units', 'units.id', '=', 'manage_items.u_name')
            ->where('manage_items.g_name', $grp->id)
            ->where('manage_items.delete', '0')
            ->where('manage_items.company_id', Session::get('user_company_id'))
            ->select('manage_items.id', 'manage_items.name', 'units.s_name')
            ->orderBy('manage_items.name')
            ->get();
        $groupQty = 0;
        $groupAmount = 0;
        $itemRows = [];

        $groupQty = 0;
         $groupAmount = 0;
         $itemRows = [];
         foreach ($items as $it) {
            if (!isset($finalItemMap[$it->id])) {
               continue;
            }
            $row = $finalItemMap[$it->id];
            $groupQty += $row['average_weight'];
            $groupAmount += $row['amount'];
            $itemRows[] = [
               'item_id' => $it->id,
               'item_name' => $row['item_name'],
               'average_weight' => $row['average_weight'],
               'amount' => $row['amount'],
               'unit_name' => $row['unit_name'],
            ];
         }
               // skip empty groups
               //   if (count($itemRows) === 0) {
               //       continue;
               //   }
               $groups[] = [
                     'group_id' => $grp->id,
                     'group_name' => $grp->group_name,
                     'qty' => $groupQty,
                     'amount' => $groupAmount,
                     'items' => $itemRows,
               ];
      }
            return view('itemledger')
               ->with('item_list', $item_list)
               ->with('groups', $groups)
               ->with('items', []) 
               ->with('item_id', $item_id)
               ->with('opening', $opening)
               ->with('fdate', $fdate)
               ->with('tdate', $tdate)
               ->with('series', $series)
               ->with('selected_series', $selected_series);
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
        //           echo "<pre>";
        //   print_r($item_ledger->toArray());die;
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
         $result = collect($result)
    ->sortBy('item_name', SORT_NATURAL | SORT_FLAG_CASE)
    ->values()
    ->toArray();
         
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
     
            // run query
            $from_date = date('Y-m-d', strtotime($request->from_date));
            $average_opening = ItemAverage::where('item_id', $item_id)
               ->where('series_no', $request->series)
               ->whereRaw("STR_TO_DATE(stock_date, '%Y-%m-%d')<STR_TO_DATE('".$request->from_date."', '%Y-%m-%d')")
               ->orderBy('stock_date','desc')
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
      // echo "<pre>";
      // print_r($average_data->toArray());die;
      return view('item_ledger_average')->with('item_list', $item_list)->with('fdate', $fdate)->with('tdate',$tdate)->with('item_id', $item_id)->with('opening_amount', $opening_amount)->with('opening_weight', $opening_weight)->with('average_data', $average_data)->with('selected_series', $selected_series)->with('series', $series);
   }



   public function itemAverageDetails(Request $request){
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

    //Join with Stock Transfers (Note: Use alias to avoid duplicate table)
    ->leftJoin('stock_transfers as st_out', 'item_average_details.stock_transfer_id', '=', 'st_out.id')
    ->leftJoin('stock_transfers as st_in', 'item_average_details.stock_transfer_in_id', '=', 'st_in.id')

    // Join with Stock Journal
    ->leftJoin('stock_journal as sj_out', 'item_average_details.stock_journal_out_id', '=', 'sj_out.id')
    ->leftJoin('stock_journal as sj_in', 'item_average_details.stock_journal_in_id', '=', 'sj_in.id')

    // Join with Production
    ->leftJoin('account_productions as ap_out', 'item_average_details.production_out_id', '=', 'ap_out.id')
    ->leftJoin('account_productions as ap_in', 'item_average_details.production_in_id', '=', 'ap_in.id')

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
        'st_out.material_center_to as st_ot_account',

        'st_in.material_center_from as st_in_account',
        'st_in.voucher_no_prefix as st_in_voucher',

        'sj_out.series_no as sj_out_account',
        'sj_out.voucher_no_prefix as sj_out_voucher',

        'sj_in.series_no as sj_in_account',
        'sj_in.voucher_no_prefix as sj_in_voucher',

        'ap_out.series_no as ap_out_account',
        'ap_out.voucher_no_prefix as ap_out_voucher',

        'ap_in.series_no as ap_in_account',
        'ap_in.voucher_no_prefix as ap_in_voucher'
    )

    ->get();

    $average_detail = collect($average_detail)
    ->groupBy(function ($row) {
        // group only PRODUCTION GENERATE by date
        if ($row->type === 'PRODUCTION GENERATE') {
            return 'PG_' . $row->entry_date;
        }

        // keep others unique
        return uniqid();
    })
    ->map(function ($rows) {

        $first = $rows->first();

        // merge PRODUCTION GENERATE rows
        if ($first->type === 'PRODUCTION GENERATE') {

            $first->production_in_weight = $rows->sum(function ($r) {
                return (float) $r->production_in_weight;
            });

            $first->production_in_amount = $rows->sum(function ($r) {
                return (float) $r->production_in_amount;
            });

            // optional: keep deckle ids if needed
            $first->deckle_ids = $rows->pluck('deckle_id')->values();

            return $first;
        }

        // other types unchanged
        return $first;
    })
    ->values();

      $opening_amount = 0;$opening_weight = 0;
      $average_opening = ItemAverage::where('item_id',$request->items_id)
                     ->where('stock_date','<',$request->date)
                     ->where('series_no',$request->series)
                     ->orderBy('stock_date','desc')
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
         if($opening){
            $opening_amount = $opening->total_price;
            $opening_weight = $opening->in_weight;
         }else{
            $opening_amount = 0;
            $opening_weight = 0;
         }
         
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
            if($item1[0]->average_weight!="" && $item1[0]->average_weight!=0){
               $series[$s1]->price = round($item1[0]->amount/$item1[0]->average_weight,6);
            }else{
               $series[$s1]->price = 0;
            }
           
            
            
         }else{
            $series[$s1]->weight = "";
            $series[$s1]->amount = "";
            $series[$s1]->unit = "";
            $series[$s1]->price = "";
         }
      }
      return view('item_average_by_godown',['item'=>$item,"series"=>$series]);
   }
   
   public function exportCsv(Request $request)
{
    $item_id = $request->items_id;
    $from = $request->from_date;
    $to = $request->to_date;
    $series = $request->series;

    $company = Companies::find(Session::get('user_company_id'));
    $item = ManageItems::find($item_id);

    $data = ItemAverage::where('item_id', $item_id)
        ->where('series_no', $series)
        ->whereBetween('stock_date', [$from, $to])
        ->get();

    $opening_amount = 0;
    $opening_weight = 0;

    $average_opening = ItemAverage::where('item_id', $item_id)
        ->where('series_no', $series)
        ->where('stock_date', '<', $from)
        ->orderBy('stock_date', 'desc')
        ->first();

    if ($average_opening) {
        $opening_amount = $average_opening->amount;
        $opening_weight = $average_opening->average_weight;
    } else {
        $opening = ItemLedger::where('item_id', $item_id)
            ->where('source', '-1')
            ->where('series_no', $series)
            ->where('delete_status', '0')
            ->first();

        if ($opening) {
            $opening_amount = $opening->total_price;
            $opening_weight = $opening->in_weight;
        }
    }

    $filename = "item_ledger.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function () use ($company, $item, $series, $from, $to, $data, $opening_weight, $opening_amount) {

        $file = fopen('php://output', 'w');

        fputcsv($file, [$company->company_name]);
        fputcsv($file, [$company->address ?? '']);
        fputcsv($file, ["CIN: " . ($company->cin ?? '')]);

        fputcsv($file, []);

        fputcsv($file, ["Item Ledger Report"]);
        fputcsv($file, ["Item: " . ($item->name ?? '')]);
        fputcsv($file, ["Series: " . $series]);
        fputcsv($file, ["From: $from To: $to"]);

        fputcsv($file, []);

        fputcsv($file, [
            "Date",
            "Qty In",
            "Qty Out",
            "Balance",
            "Average Rate",
            "Amount"
        ]);

        $avg_rate = ($opening_weight != 0) ? round($opening_amount / $opening_weight, 6) : 0;

        fputcsv($file, [
            "Opening",
            "",
            "",
            $opening_weight,
            $avg_rate,
            $opening_amount
        ]);

        $total_in = 0;
        $total_out = 0;

        foreach ($data as $row) {

            $in = $row->purchase_weight ?? 0;
            $out = $row->sale_weight ?? 0;

            $total_in += $in;
            $total_out += $out;

            fputcsv($file, [
                $row->stock_date,
                $in,
                $out,
                $row->average_weight,
                $row->price,
                $row->amount
            ]);
        }

        fputcsv($file, [
            "Total",
            $total_in,
            $total_out,
            "",
            "",
            ""
        ]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportStockCsv(Request $request)
{
    $item_id = $request->items_id;
    $from = $request->from_date;
    $to = $request->to_date;

    $company = Companies::find(Session::get('user_company_id'));
    $item = ManageItems::find($item_id);

    $series = [];

    $companyData = Companies::where('id', Session::get('user_company_id'))->first();

    if ($companyData->gst_config_type == "single_gst") {
        $series = DB::table('gst_settings')
            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
            ->get();

        $branch = GstBranch::select('id','branch_series as series')
            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
            ->get();

        if(count($branch)>0){
            $series = $series->merge($branch);
        }

    } else {
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

    foreach ($series as $s1 => $s) {

        $sub = DB::table('item_averages')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where('stock_date', '<=', $to)
            ->where('series_no', $s->series)
            ->where('company_id', Session::get('user_company_id'))
            ->where('item_id', $item_id)
            ->pluck('latest_id');

        $item1 = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('item_averages.id', $sub)
            ->where('series_no', $s->series)
            ->select(
                'item_averages.average_weight',
                'item_averages.amount',
                'units.s_name as unit_name'
            )
            ->orderBy('item_averages.stock_date', 'desc')
            ->get();

        if(count($item1)>0){
            $series[$s1]->weight = $item1[0]->average_weight;
            $series[$s1]->amount = $item1[0]->amount;
            $series[$s1]->unit = $item1[0]->unit_name;

            if($item1[0]->average_weight != 0){
                $series[$s1]->price = round($item1[0]->amount / $item1[0]->average_weight, 6);
            } else {
                $series[$s1]->price = 0;
            }

        } else {
            $series[$s1]->weight = 0;
            $series[$s1]->amount = 0;
            $series[$s1]->unit = '';
            $series[$s1]->price = 0;
        }
    }

    $filename = "item_stock.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function () use ($company, $item, $from, $to, $series) {

        $file = fopen('php://output', 'w');

        fputcsv($file, [$company->company_name]);
        fputcsv($file, [$company->address ?? '']);
        fputcsv($file, ["CIN: " . ($company->cin ?? '')]);

        fputcsv($file, []);

        fputcsv($file, ["Item Stock Report"]);
        fputcsv($file, ["Item: " . ($item->name ?? '')]);
        fputcsv($file, ["From: $from To: $to"]);

        fputcsv($file, []);

        fputcsv($file, [
            "Material Center",
            "Qty",
            "Unit",
            "Price",
            "Amount"
        ]);

        $total_qty = 0;
        $total_amount = 0;

        foreach ($series as $row) {

            $total_qty += (float)$row->weight;
            $total_amount += (float)$row->amount;

            fputcsv($file, [
                $row->series,
                $row->weight,
                $row->unit,
                $row->price,
                $row->amount
            ]);
        }

        fputcsv($file, [
            "Total",
            $total_qty,
            "",
            "",
            $total_amount
        ]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportMainLedgerCsv(Request $request)
{
    $item_id = $request->items_id;
    $from = $request->from_date;
    $to = $request->to_date;
    $selected_series = $request->selected_series;

    $company = Companies::find(Session::get('user_company_id'));

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
    } else {
        $series = DB::table('gst_settings_multiple')
            ->select('id','series')
            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
            ->get();

        foreach ($series as $value) {
            $branch = GstBranch::select('id','branch_series as series')
                ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                ->get();

            if(count($branch)>0){
                $series = $series->merge($branch);
            }
        }
    }

    $seriesToUse = ($selected_series === 'all')
        ? $series
        : [(object)['series' => $selected_series]];

    $allArrays = [];

    foreach ($seriesToUse as $s) {

        $item_ledger = ItemLedger::join('manage_items', 'item_ledger.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->select(
                'item_id',
                'in_weight as average_weight',
                'total_price as amount',
                'manage_items.name as item_name',
                'units.s_name as unit_name'
            )
            ->where('item_ledger.company_id', Session::get('user_company_id'))
            ->where('source', '-1')
            ->where('series_no', $s->series)
            ->where('delete_status', '0')
            ->get();

        $sub = DB::table('item_averages')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->where('stock_date', '<=', $to)
            ->where('series_no', $s->series)
            ->where('company_id', Session::get('user_company_id'))
            ->groupBy('item_id');

        $avgItems = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->whereIn('item_averages.id', $sub)
            ->where('series_no', $s->series)
            ->select(
                'item_averages.item_id',
                'item_averages.average_weight',
                'item_averages.amount',
                'manage_items.name as item_name',
                'units.s_name as unit_name'
            )
            ->get();

        foreach ($item_ledger as $row) {
            if (!$avgItems->contains('item_id', $row->item_id)) {
                $avgItems->push($row);
            }
        }

        $allArrays[] = $avgItems->toArray();
    }

    $finalItemMap = [];

    foreach ($allArrays as $array) {
        foreach ($array as $item) {
            if (!isset($finalItemMap[$item['item_id']])) {
                $finalItemMap[$item['item_id']] = $item;
            } else {
                $finalItemMap[$item['item_id']]['average_weight'] += $item['average_weight'];
                $finalItemMap[$item['item_id']]['amount'] += $item['amount'];
            }
        }
    }

    // ================= CSV =================

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=item_ledger_main.csv",
    ];

    $callback = function () use ($company, $item_id, $finalItemMap) {

        $file = fopen('php://output', 'w');

        fputcsv($file, [$company->company_name]);
        fputcsv($file, [$company->address ?? '']);
        fputcsv($file, ["CIN: " . ($company->cin ?? '')]);

        fputcsv($file, []);
        fputcsv($file, ["Item Ledger Report"]);
        fputcsv($file, []);

        // ================= ALL GROUPS =================
         if ($item_id === 'all_groups') {

            fputcsv($file, ["Group", "Type", "Qty", "Unit", "Amount"]);

            $groupsRaw = DB::table('item_groups')
               ->where('company_id', Session::get('user_company_id'))
               ->orderBy('group_name')
               ->get();

            $total_qty = 0;
            $total_amount = 0;

            foreach ($groupsRaw as $grp) {

               $items = DB::table('manage_items')
                     ->join('units', 'units.id', '=', 'manage_items.u_name')
                     ->where('manage_items.g_name', $grp->id)
                     ->where('manage_items.delete', '0')
                     ->where('manage_items.company_id', Session::get('user_company_id'))
                     ->select('manage_items.id', 'manage_items.name', 'units.s_name')
                     ->get();

               $groupQty = 0;
               $groupAmount = 0;
               $itemRows = [];

               foreach ($items as $it) {

                     if (!isset($finalItemMap[$it->id])) continue;

                     $row = $finalItemMap[$it->id];

                     $groupQty += $row['average_weight'];
                     $groupAmount += $row['amount'];

                     $itemRows[] = [
                        'name' => $row['item_name'],
                        'qty' => $row['average_weight'],
                        'unit' => $row['unit_name'],
                        'amount' => $row['amount'],
                     ];
               }

               if (count($itemRows) == 0) continue;

               fputcsv($file, [
                     $grp->group_name,
                     "Group",
                     $groupQty,
                     "",
                     $groupAmount
               ]);

               foreach ($itemRows as $item) {
                     fputcsv($file, [
                        $item['name'],
                        "Item",
                        $item['qty'],
                        $item['unit'],
                        $item['amount']
                     ]);
               }

               $total_qty += $groupQty;
               $total_amount += $groupAmount;
            }

            fputcsv($file, [
               "Total",
               "",
               $total_qty,
               "",
               $total_amount
            ]);
         }

        // ================= ALL ITEMS =================
        elseif ($item_id === 'all') {

            fputcsv($file, ["Item", "Type", "Qty", "Unit", "Amount"]);

            $total_qty = 0;
            $total_amount = 0;

            foreach ($finalItemMap as $row) {

                fputcsv($file, [
                    $row['item_name'],
                    "Item",
                    $row['average_weight'],
                    $row['unit_name'],
                    $row['amount']
                ]);

                $total_qty += $row['average_weight'];
                $total_amount += $row['amount'];
            }

            fputcsv($file, ["Total", "", $total_qty, "", $total_amount]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
   
}
