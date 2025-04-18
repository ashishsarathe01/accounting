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
         $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));

         $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2) || (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=-1) GROUP BY item_id"));
         foreach ($item_in_data as $key => $value) {
            echo $value->total_price;
            $check = ItemLedger::select('id')
                        ->where('item_id',$value->item_id)
                        ->whereRaw("REPLACE(total_price, '.00', '')=$value->total_price")
                        ->whereRaw("REPLACE(in_weight, '.00', '')=$value->in_weight")
                        //->where('total_price',$value->total_price)
                        //->where('in_weight',$value->in_weight)
                        ->where('source','-1')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
            if($check){
               $item_in_data[$key]->opening = 1;
            }else{
               $item_in_data[$key]->opening = 0;
            }
         }
            // echo "<pre>";
            // print_r($item_in_data);die;
         return view('itemledger')->with('item_list', $item_list)->with('items', $item)->with('item_id', $item_id)->with('opening', $opening)->with('item_in_data', $item_in_data)->with('fdate', $open_date)->with('tdate',$tdate);
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
         $y =  explode("-",$financial_year);
         $open_date = $y[0]."-04-01";
         $open_date = date('Y-m-d',strtotime($open_date));
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
      $item_data = [];
      $opening_amount = 0;$opening_weight = 0;
      $item_in_data = [];
      if(isset($request->items_id)){
         $opening_value = DB::select(DB::raw("SELECT SUM(total_price) as total_price,sum(in_weight) as in_weight FROM item_ledger WHERE item_id='".$item_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and source='-1' and status='1' and delete_status='0' and in_weight!=''"));
         $total_price = $opening_value[0]->total_price;
         $in_weight = $opening_value[0]->in_weight;
         $out_weight = 0;
         $second_total_amount = 0;
         if($open_date!=$request->from_date){           
            $opening_in_value = DB::select(DB::raw("SELECT sum(in_weight) as in_weight,SUM(total_price) as total_price FROM item_ledger WHERE item_id='".$item_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d', strtotime($request->from_date. " - 1 days"))."', '%Y-%m-%d') and source!='-1' and status='1' and delete_status='0'  and in_weight!=''"));
            if($opening_in_value[0]->in_weight!=0 && $opening_in_value[0]->in_weight!=''){
               $total_price = $total_price + $opening_in_value[0]->total_price;
               $in_weight = $in_weight + $opening_in_value[0]->in_weight; 
               $second_total_amount = 1;              
            }
            $opening_out_value = DB::select(DB::raw("SELECT SUM(out_weight) as out_weight FROM item_ledger WHERE item_id='".$item_id."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and source!='-1' and status='1' and delete_status='0' and out_weight!=''")); 
            $out_weight = $out_weight + $opening_out_value[0]->out_weight;       
         }
         if($in_weight!='' && $total_price!=''){
            $closing_price = $total_price/$in_weight;
            $closing_price = round($closing_price,2); 
            $opening_weight = $in_weight - $out_weight;

            if($second_total_amount==0 && $open_date==$request->from_date){
               $opening_amount = round($total_price,2);
            }else{               
               $opening_amount = $opening_weight * $closing_price;
               $opening_amount = round($opening_amount,2); 
            }            
         }else{
            $opening_weight = 0 - $out_weight;
            $opening_amount = 0;
         }         
         $item_data = DB::select(DB::raw("SELECT sum(in_weight) as in_weight,sum(out_weight) as out_weight,SUM(total_price) as total_price,txn_date FROM item_ledger WHERE item_id='".$item_id."' and source!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status='1' and delete_status='0'  GROUP BY txn_date order by STR_TO_DATE(txn_date, '%Y-%m-%d') "));
         
         $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,txn_date FROM item_ledger WHERE item_id='".$item_id."' and source!=-1 and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$request->from_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2 GROUP BY txn_date order by STR_TO_DATE(txn_date, '%Y-%m-%d')"));
      }
      return view('item_ledger_average')->with('item_list', $item_list)->with('opening', 0)->with('fdate', $fdate)->with('tdate',$tdate)->with('item_id', $item_id)->with('item_data', $item_data)->with('opening_amount', $opening_amount)->with('opening_weight', $opening_weight)->with('item_in_data', $item_in_data)->with('second_total_amount', $second_total_amount);
   }
}
