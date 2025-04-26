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
      return view('itemledger')->with('item_list', $item_list)->with('items', $items)->with('opening', 0)->with('fdate', $fdate)->with('tdate',$tdate);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

   public function filter(Request $request){
      $item_id = $request->items_id;
      $item_list = ManageItems::join('units', 'units.id', '=', 'manage_items.u_name')
                              ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                              ->where(['manage_items.delete' => '0', 'manage_items.company_id' => Session::get('user_company_id')])
                              ->select(['manage_items.id','manage_items.name'])
                              ->orderBy('manage_items.name')
                              ->get();
      if($item_id=="all"){
         $opening = 0;
         $financial_year = Session::get('default_fy');
         $fdate = date('Y-m-d',strtotime($request->from_date));
         $tdate = date('Y-m-d',strtotime($request->to_date));
         $y =  explode("-",$financial_year);
         $open_date = $y[0]."-04-01";
         $open_date = date('Y-m-d',strtotime($open_date));     
         $sub = DB::table('item_averages')
                     ->select(DB::raw('MAX(id) as latest_id'))
                     ->where('stock_date', '<=', $request->to_date)
                     ->groupBy('item_id');
                 
         $item = ItemAverage::join('manage_items', 'item_averages.item_id', '=', 'manage_items.id')
                     ->join('units', 'manage_items.u_name', '=', 'units.id')
                     ->whereIn('item_averages.id', $sub)
                     ->select(
                         'item_averages.item_id',
                         'item_averages.average_weight',
                         'item_averages.amount',
                         'item_averages.stock_date',
                         'manage_items.name as item_name',
                         'units.name as unit_name'
                     )
                     ->orderBy('stock_date', 'desc')
                     ->get();
         //print_r($data->toArray());die;
         return view('itemledger')->with('item_list', $item_list)->with('items', $item)->with('item_id', $item_id)->with('opening', $opening)->with('fdate', $open_date)->with('tdate',$tdate);
      }
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){         
         $item = DB::select(DB::raw("SELECT * FROM item_ledger WHERE item_id='".$item_id."' and source!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status=1 and delete_status='0' order by STR_TO_DATE(txn_date, '%Y-%m-%d')"));
      }else{
         $item = ItemLedger::where('item_id',$item_id)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->where('source','!=','-1')
                                 ->where('delete_status','=','0')
                                 ->orderBy(DB::raw("STR_TO_DATE(txn_date, '%Y-%m-%d')"))
                                 ->get();
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
            }else{
               $item[$key]->bill_no = "";
               $item[$key]->account_name = "";
               $item[$key]->type = "";
               $item[$key]->einvoice_status = 0;
            }
         }
      }
      $opening = 0;
      if(isset($request->from_date) && !empty($request->from_date)){
         $financial_year = Session::get('default_fy');
         $y =  explode("-",$financial_year);
         $open_date = $y[0]."-04-01";
         $open_date = date('Y-m-d',strtotime($open_date));  
         if($request->from_date!=$open_date){
            $open_ledger = DB::select(DB::raw("SELECT SUM(in_weight) as debit,SUM(out_weight) as credit FROM item_ledger WHERE item_id='".$item_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and status=1 and delete_status='0'"));
         }else{
            $open_ledger = DB::select(DB::raw("SELECT SUM(in_weight) as debit,SUM(out_weight) as credit FROM item_ledger WHERE item_id='".$item_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and status=1 and delete_status='0' and source='-1'"));
         }
         if(count($open_ledger)>0){
               $opening = $open_ledger[0]->debit - $open_ledger[0]->credit;
         }
      }else{
         $open_ledger = ItemLedger::where('item_id',$item_id)
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->where('source','-1')
                                 ->first();
         if($open_ledger){
            if($open_ledger->out_weight!=""){
               $opening = -$open_ledger->out_weight;
            }else if($open_ledger->in_weight!=""){
               $opening = $open_ledger->in_weight;
            }
         }
      } 
      $collection = new Collection($item);
      //$item = $collection->sortByDesc('date');
      $item = json_decode($collection, true);
      return view('itemledger')->with('item_list', $item_list)->with('items', $item)->with('item_id', $item_id)->with('opening', $opening);
   }
   public function itemLedgerAverage(Request $request){
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
                  ->whereRaw("STR_TO_DATE(stock_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(stock_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d')")
                  ->get();
      $average_opening = ItemAverage::where('item_id',$item_id)
                     ->where('stock_date','<',$request->from_date)
                     ->first();
      if($average_opening){
         $opening_amount = $average_opening->amount;
         $opening_weight = $average_opening->average_weight;
      }else{
         $opening = ItemLedger::where('item_id',$item_id)
                                    ->where('source','-1')
                                    ->first();
         $opening_amount = $opening->total_price;
         $opening_weight = $opening->in_weight;
      }      
      return view('item_ledger_average')->with('item_list', $item_list)->with('fdate', $fdate)->with('tdate',$tdate)->with('item_id', $item_id)->with('opening_amount', $opening_amount)->with('opening_weight', $opening_weight)->with('average_data', $average_data);
   }
   public function itemAverageDetails(Request $request){
      $average_detail = ItemAverageDetail::where('item_id',$request->items_id)
                     ->where('entry_date',$request->date)
                     ->get();
      $opening_amount = 0;$opening_weight = 0;
      $average_opening = ItemAverage::where('item_id',$request->items_id)
                     ->where('stock_date','<',$request->date)
                     ->first();
      if($average_opening){
         $opening_amount = $average_opening->amount;
         $opening_weight = $average_opening->average_weight;
      }else{
         $opening = ItemLedger::where('item_id',$request->items_id)
                                    ->where('source','-1')
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
   
}
