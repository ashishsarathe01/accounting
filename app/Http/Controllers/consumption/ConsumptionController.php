<?php

namespace App\Http\Controllers\consumption;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\ItemGroups;
use App\Models\Units;
use App\Models\item_gst_rate;
use App\Models\ItemLedger;
use App\Models\StockJournal;
use App\Models\StockJournalDetail;
use App\Models\Companies;
use App\Models\ItemSizeStock;
use App\Models\VoucherSeriesConfiguration;
use App\Models\GstBranch;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemAverageDetail;
use App\Models\Consumption;
use App\Models\DeckleProcess;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Gate;

class ConsumptionController extends Controller
{
    public function index(Request $request)
    {
      $completed_deckles = DeckleProcess::with([
                    'quality' => function ($q) {
                        $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                                    $q->select('deckle_process_qualities.id','parent_id','item_id','manage_items.name','production_in_kg');
                        
                        $q->withSum('item_stock', 'weight');
                    }
                ])
                ->where('deckle_processes.company_id', Session('user_company_id'))
                ->where('deckle_processes.stock_journal_status', 0)
                ->where('deckle_processes.status','!=', 1)
                ->where('deckle_processes.status','!=', 5)
                ->select('deckle_processes.id', 'deckle_no', 'start_time_stamp', 'end_time_stamp', 'reel_generated_at','status','deckle_price')
                ->get();
      $pending_stock_journal_consumption_deckles = [];
      foreach ($completed_deckles as $item) {
         $end_time_stamp = $item['end_time_stamp'];
         $end_time_stamp = \Carbon\Carbon::parse($end_time_stamp);
         $end_time_stamp = $end_time_stamp->format('Y-m-d');
         //$reel_generated_at = \Carbon\Carbon::parse($item->reel_generated_at)->toDateString();         
         $pending_stock_journal_consumption_deckles[$end_time_stamp][] = $item->toArray();
      }
      //Complete Stock General Data

      $from_date = date("Y-m-")."01";
      $to_date = date("Y-m-t");
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
         $from_date = $request->from_date;
         $to_date = $request->to_date;
      }
      $completed_stocl_journal_deckles = DeckleProcess::with([
                    'quality' => function ($q) {
                        $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                                    $q->select('deckle_process_qualities.id','parent_id','item_id','manage_items.name');
                        
                        $q->withSum('item_stock', 'weight');
                    }
                ])
                ->where('deckle_processes.company_id', Session('user_company_id'))
                ->where('deckle_processes.stock_journal_status','!=', 0)
                ->where('deckle_processes.status','!=', 1)
                ->where('deckle_processes.status','!=', 5)
                ->whereDate('end_time_stamp','>=',$from_date)
                ->whereDate('end_time_stamp','<=',$to_date)
                ->select('deckle_processes.id', 'deckle_no', 'start_time_stamp', 'end_time_stamp', 'reel_generated_at','stock_journal_status')
                ->get();      
      $completeStockJournalItems = [];
      foreach ($completed_stocl_journal_deckles as $item) {
         //$end_time_stamp = $item['end_time_stamp'];
         $end_time_stamp = \Carbon\Carbon::parse($item->end_time_stamp)->toDateString();
         if (!isset($completeStockJournalItems[$end_time_stamp])) {
            $completeStockJournalItems[$end_time_stamp] = [];
         }
         $completeStockJournalItems[$end_time_stamp][] = $item->toArray();
      }


      // echo "<pre>";
      // print_r($pending_stock_journal_consumption_deckles);
      // die;
      return view('consumption.index',["pending_stock_journal_consumption_deckles"=>$pending_stock_journal_consumption_deckles,"completeStockJournalItems"=>$completeStockJournalItems,"from_date"=>$from_date,"to_date"=>$to_date]); 
    }
    public function manage(Request $request){
      $date = $request->input('date');
      $financial_year = Session::get('default_fy');
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*','units.s_name as unit'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->where('manage_items.status','1')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();
      $bill_date = date('Y-m-d');
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $y =  explode("-",$financial_year);
         $bill_date = $y[1]."-03-31";
         $bill_date = date('Y-m-d',strtotime($bill_date));
      }
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      if($companyData->gst_config_type == "single_gst"){
         $series_list = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series_list[0]->id])
                           ->get();
         if(count($branch)>0){
            $series_list = $series_list->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series_list = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series_list as $key => $value) {
               $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
               if(count($branch)>0){
                  $series_list = $series_list->merge($branch);
               }
         }         
      }
      foreach ($series_list as $key => $value) {         
         $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$value->series)
               ->where('configuration_for','STOCK JOURNAL')
               ->where('status','1')
               ->first();
         $voucher_no = StockJournal::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',Session::get('default_fy'))
                           ->where('series_no','=',$value->series)
                           ->max(\DB::raw("cast(voucher_no as SIGNED)"));
                           if(!$voucher_no){
                              if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                                 $series_list[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
                              }else{
                                 $series_list[$key]->invoice_start_from =  "001";
                              }            
                           }else{
                              $invc = $voucher_no + 1;
                              $invc = sprintf("%'03d", $invc);
                              $series_list[$key]->invoice_start_from =  $invc;
                           }
         $invoice_prefix = "";
         $duplicate_voucher = "";
         $blank_voucher = "";
         $manual_enter_invoice_no = "0";
         if($series_configuration && $series_configuration->manual_numbering=="YES"){
            $manual_enter_invoice_no = "1";
            $duplicate_voucher = $series_configuration->duplicate_voucher;
            $blank_voucher = $series_configuration->blank_voucher;
         }
         if($series_configuration && $series_configuration->manual_numbering=="NO"){
               $manual_enter_invoice_no = "0";
               if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!=""){
                  $invoice_prefix.=$series_configuration->prefix_value;
               }        
               if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!="" && $series_configuration->separator_1!=""){
                  $invoice_prefix.=$series_configuration->separator_1;
               }
               if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!=""){
                  if($series_configuration->year_format=="YY-YY"){
                     $invoice_prefix.=Session::get('default_fy');
                  }else if($series_configuration->year_format=="YYYY-YY"){
                     $default_fy = Session::get('default_fy');  // 23-24
                     $fy_parts = explode('-', $default_fy);     // [23, 24]
                     $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
                  }
               }            
               if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
               }
               $invoice_prefix.=$series_list[$key]->invoice_start_from;
               if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!="" &&  $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
               }
               if($series_configuration->year=="SUFFIX TO NUMBER" &&                        $series_configuration->year_format!=""){
                  if($series_configuration->year_format=="YY-YY"){
                     $invoice_prefix.=Session::get('default_fy');
                  }else if($series_configuration->year_format=="YYYY-YY"){
                     $default_fy = Session::get('default_fy');  // 23-24
                     $fy_parts = explode('-', $default_fy);     // [23, 24]
                     $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];   
                  }
               }       
               if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!="" && $series_configuration->separator_3!=""){
                  $invoice_prefix.=$series_configuration->separator_3;
               }
               if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!=""){
                  $invoice_prefix.=$series_configuration->suffix_value;
               } 
         }
         $series_list[$key]->manual_enter_invoice_no =  $manual_enter_invoice_no;
         $series_list[$key]->duplicate_voucher =  $duplicate_voucher;
         $series_list[$key]->blank_voucher =  $blank_voucher;
         $series_list[$key]->invoice_prefix =  $invoice_prefix;
        }

   //  $item_stock_size = ItemSizeStock::whereDate('item_size_stocks.created_at', $date)
   //                       ->join('manage_items','manage_items.id','=','item_size_stocks.item_id')
   //                       ->join('units', 'units.id', '=', 'manage_items.u_name')
   //                      ->where('item_size_stocks.company_id', Session::get('user_company_id'))
   //                      ->where(function($query) {
   //                          $query->whereNotNull('item_size_stocks.deckle_id')
   //                              ->orwhere('item_size_stocks.deckle_id', '!=', 0);
   //                      })
   //                      ->select('manage_items.id','manage_items.name',DB::raw('SUM(item_size_stocks.weight) as weight'),'units.id as u_name', 'units.name as unit')
   //                      ->groupby('manage_items.name')
   //                      ->get();
            $reel_generated_from = $date . " 08:00:00";
            $reel_generated_to = \Carbon\Carbon::parse($date)
               ->copy()
               ->addDay()
               ->format('Y-m-d') . " 08:00:00";

            $item_stock_size = DeckleProcess::with([
                        'quality' => function ($q) {
                           $q->join('manage_items', 'deckle_process_qualities.item_id', 'manage_items.id')
                              ->select(
                                 'deckle_process_qualities.id',
                                 'deckle_process_qualities.parent_id',
                                 'deckle_process_qualities.item_id',
                                 'manage_items.name','units.id as u_name', 'units.s_name as unit','production_in_kg'
                              )->join('units', 'units.id', '=', 'manage_items.u_name')
                              ->withSum('item_stock', 'weight');
                        }
                     ])
                     ->where('deckle_processes.company_id', Session('user_company_id'))
                     ->where('deckle_processes.status','!=', 1)
                     ->where('deckle_processes.status','!=', 5)
                     ->where('deckle_processes.stock_journal_status', 0)
                     ->where('deckle_processes.status', 4)
                     ->whereDate('deckle_processes.end_time_stamp', $date)                                
                     ->select('deckle_processes.id', 'deckle_no', 'start_time_stamp', 'end_time_stamp', 'reel_generated_at','status','deckle_price')
                     ->get();
            // echo "<pre>";
            // print_r($item_stock_size->toArray());die;
            // ✅ Group by item_id and sum weights
            $groupedItems = [];
            foreach ($item_stock_size as $deckle) {
               foreach ($deckle->quality as $q) {
                  if($deckle->status==4){
                     $weight = $q->item_stock_sum_weight ?? 0;
                  }else{
                     $weight = $q->production_in_kg ?? 0;
                  }
                  $itemId = $q->item_id;
                  $itemName = $q->name;
                  $unit_id = $q->u_name;
                  $unit_name = $q->unit;
                  if (!isset($groupedItems[$itemId])) {
                        $groupedItems[$itemId] = [
                           'item_id' => $itemId,
                           'name' => $itemName,
                           'weight' => $weight,
                           'unit_id' => $unit_id,
                           'unit_name' => $unit_name,
                           'deckle_price'=>$deckle->deckle_price,
                           'production_in_kg'=>$q->production_in_kg
                        ];
                  }else{
                     $groupedItems[$itemId]['weight'] += $weight;
                  }
               }
            }
      // echo "<pre>";
      // print_r($groupedItems);
      // die;
    $previousElectricity = DB::table('consumption')
        ->where('company_id', Session::get('user_company_id'))
        ->orderBy('id', 'desc')
        ->first();

    // Send it to the view
    return view('consumption.manage')
        ->with('items', $items)
        ->with('date', $date)
        ->with('series_list', $series_list)
        ->with('item_stock_size', $groupedItems)
        ->with('previousElectricity', $previousElectricity);
}
                


//return view('consumption.manage')->with('items', $items)->with('date', $bill_date)->with('series_list', $series_list)->with('item_stock_size',$item_stock_size);


public function getItemAveragePrice(Request $request)
{
    $item_id = $request->input('item_id');
    $series_no = $request->input('series_no');
    $company_id = Session::get('user_company_id');
    $date = $request->input('date');;
    $yesterday = \Carbon\Carbon::yesterday()->format('Y-m-d');

    $priceData = DB::table('item_averages')
        ->where('company_id', $company_id)
        ->when($series_no, function ($query, $series_no) {
            return $query->where('series_no', $series_no);
        })
        ->where('item_id', $item_id)
        ->whereDate('stock_date', $date)
        ->orderBy('id', 'desc')
        ->first();

    if ($priceData) {
        return response()->json(['price' => $priceData->price]);
    }

    // Fallback: latest available entry
    $latest = DB::table('item_averages')
        ->where('company_id', $company_id)
        ->when($series_no, function ($query, $series_no) {
            return $query->where('series_no', $series_no);
        })
        ->where('item_id', $item_id)
        ->orderBy('stock_date', 'desc')
        ->first();

    return response()->json(['price' => $latest ? round($latest->price,2) : null]);
}


public function store(Request $request)
{
    
      // echo "<pre>";
      // print_r($request->all());
      // die;
      $financial_year = Session::get('default_fy');
      $date = $request->input('date');
      $narration = $request->input('narration');
      $series_no = $request->input('series_no');
      $voucher_prefix = $request->input('voucher_prefix');
      $voucher_no = $request->input('voucher_no');
      $manual_enter_invoice_no = $request->input('manual_enter_invoice_no');
      $material_center = $request->input('material_center');

      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');
      $consume_units = $request->input('consume_units');
      $consume_unit_name = $request->input('consume_unit_name');

      $generated_item = $request->input('generated_item_id');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');
      $generated_units = $request->input('generated_units');
      $generated_unit_name = $request->input('generated_unit_name');

      $stockjournal = new StockJournal;
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->series_no = $series_no;
      $stockjournal->material_center = $material_center;
      $stockjournal->voucher_no_prefix = $voucher_prefix;
      $stockjournal->voucher_no = $voucher_no;
      $stockjournal->consumption_entry_status = 1;
      $stockjournal->company_id = Session::get('user_company_id');
      $stockjournal->created_by = Session::get('user_id');
      $stockjournal->financial_year = $financial_year;
      $stockjournal->created_at = date('d-m-Y H:i:s');
      if($stockjournal->save()){
         foreach ($consume_item as $key => $value){
            if($value==""){
               continue;
            }
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
            $stockjournaldetail->consume_item_unit = $consume_units[$key];
            $stockjournaldetail->consume_item_unit_name = $consume_unit_name[$key];
            $stockjournaldetail->consume_weight = $consume_weight[$key];
            $stockjournaldetail->consume_price = $consume_price[$key];
            $stockjournaldetail->consume_amount = $consume_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $consume_item[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->out_weight = $consume_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $consume_price[$key];
            $item_ledger->total_price = $consume_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $consume_item[$key];
            $average_detail->type = 'STOCK JOURNAL CONSUME';
            $average_detail->stock_journal_out_id = $stockjournal->id;
            $average_detail->stock_journal_out_weight = $consume_weight[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$consume_item[$key],$request->input('series_no'));
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->new_item = $generated_item[$key];
            $stockjournaldetail->new_item_unit = $generated_units[$key];
            $stockjournaldetail->new_item_unit_name = $generated_unit_name[$key];
            $stockjournaldetail->new_weight = $generated_weight[$key];
            $stockjournaldetail->new_price = $generated_price[$key];
            $stockjournaldetail->new_amount = $generated_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //Update Item Ledger
            ItemLedger::where('item_id',$generated_item[$key])
                        ->where('txn_date',$request->input('date'))
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('source','3')
                        ->where('source_id',null)
                        ->update([
                           "source_id"=>$stockjournal->id,
                           "price"=>$generated_price[$key],
                           "total_price"=>DB::raw("in_weight * ".$generated_price[$key])
                        ]);
            //Update Data In Average Details table
            ItemAverageDetail::where('item_id',$generated_item[$key])
                        ->where('entry_date',$request->input('date'))
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('type','STOCK JOURNAL GENERATE')
                        ->where('stock_journal_in_id',null)
                        ->update([
                           "stock_journal_in_id"=>$stockjournal->id,
                           "stock_journal_in_amount"=>DB::raw("stock_journal_in_weight * ".$generated_price[$key])
                        ]);

            
            CommonHelper::RewriteItemAverageByItem($request->date,$generated_item[$key],$request->input('series_no'));
         }

         $consumption = new Consumption;
         $consumption->stock_journal_id = $stockjournal->id;
         $consumption->electricity_units = $request->input('electricity_consumed_units');
         $consumption->unit_price = $request->input('electricity_unit_price');
         $consumption->fixed_cost = $request->input('fixed_cost');
         $consumption->date = $request->date;
         $consumption->company_id = Session::get('user_company_id');
         $consumption->status = 1;
         $consumption->created_by = Session::get('user_id');
         $consumption->created_at = now();
         $consumption->save();
         DeckleProcess::whereDate('end_time_stamp', $request->date)
                        ->where('company_id', Session::get('user_company_id'))
                        ->where('stock_journal_status',0)
                        ->UPDATE(['stock_journal_status'=>$stockjournal->id]);
         return redirect('stock-journal')->withSuccess('Stock Journal Added Successfully!'); 
      }
}

}