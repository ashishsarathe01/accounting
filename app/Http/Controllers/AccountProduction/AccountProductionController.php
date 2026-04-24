<?php

namespace App\Http\Controllers\AccountProduction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Gate;
use App\Helpers\CommonHelper;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\VoucherSeriesConfiguration;
use App\Models\DeckleProcess;
use App\Models\AccountProduction;
use App\Models\AccountProductionDetail;
use App\Models\ItemAverageDetail;
use App\Models\Consumption;
use App\Models\ItemLedger;
class AccountProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         $input = $request->all();
         $from_date = null;
         $to_date = null;
         // Handle date selection from input or session
         if (!empty($input['from_date']) && !empty($input['to_date'])) {
            $from_date = date('Y-m-d', strtotime($input['from_date']));
            $to_date = date('Y-m-d', strtotime($input['to_date']));
         }
         Session::put('redirect_url', '');
         // Financial Year Month Array
         $financial_year = Session::get('default_fy');
         $y = explode("-", $financial_year);
         $from = DateTime::createFromFormat('y', $y[0])->format('Y');
         $to = DateTime::createFromFormat('y', $y[1])->format('Y');

         $month_arr = [
            $from . '-04', $from . '-05', $from . '-06', $from . '-07',
            $from . '-08', $from . '-09', $from . '-10', $from . '-11',
            $from . '-12', $to . '-01', $to . '-02', $to . '-03'
         ];

         $company_id = Session::get('user_company_id');
         $lastProductionIds = DB::table('account_productions')
                ->where('company_id', $company_id)
                ->orderBy('production_date', 'desc')
                ->limit(10)
                ->pluck('id')
                ->toArray();
         // Start base query
         $query = DB::table('account_production_details')
                     ->leftJoin('manage_items', 'account_production_details.consume_item', '=', 'manage_items.id')
                     ->leftJoin('manage_items as new', 'account_production_details.new_item', '=', 'new.id')
                     ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
                     ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
                     ->Join('account_productions', 'account_production_details.parent_id', '=', 'account_productions.id')
                     ->select(
                           'account_production_details.parent_id as id',
                           'account_production_details.id as detail_id',
                           'account_production_details.production_date',
                           'consume_weight',
                           'new_weight',
                           'manage_items.name',
                           'new.name as new_item',
                           'consume_price',
                           'consume_amount',
                           'new_price',
                           'new_amount',
                           'consumption_entry_status',
                           'account_productions.voucher_no_prefix',
                           'units.s_name',
                           'new_unit.s_name as new_unit'
                     )
                     ->where('account_production_details.status', 1)
                     ->where('account_production_details.company_id', $company_id);
                     // Apply date filter or limit to 10
                     if ($from_date && $to_date) {
                         // Date based fetch (NO limit)
                        $query->whereBetween(
                            DB::raw('DATE(account_production_details.production_date)'),
                            [$from_date, $to_date]
                        );
                        // $query->whereRaw("STR_TO_DATE(account_production_details.production_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$from_date])
                        //       ->whereRaw("STR_TO_DATE(account_production_details.journal_production_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$to_date])
                        //       ->orderBy('account_production_details.production_date', 'asc');
                     } else {
                         $query->whereIn('account_production_details.parent_id', $lastProductionIds);
                        // $query->orderByRaw("STR_TO_DATE(account_production_details.production_date, '%Y-%m-%d') desc");
                     }
                    $journals = $query
                                ->orderBy('account_production_details.production_date', 'asc')
                                ->orderBy('account_production_details.parent_id')
                                ->orderByRaw('new.name IS NULL')   // generated items first
                                ->orderBy('new.name')
                                ->orderBy('manage_items.name')
                            ->get();
                   
                    
                    $grouped = [];
                    foreach ($journals->toArray() as $row) {
                        $grouped[$row->id][] = $row;
                    }
                    // echo "<pre>";
                    // print_r($journal->toArray());
                    // print_r($grouped);
                    // die;
                     
                     return view('AccountProduction.index')
                     ->with('journals', $journals)
                     ->with('month_arr', $month_arr)
                     ->with('from_date', $from_date)
                     ->with('to_date', $to_date);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
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
      foreach($series_list as $k=>$ser){
              $voucher_no = AccountProduction::select('voucher_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('series_no','=',$ser->series)
                        ->where('status','=','1')
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
                        if($voucher_no==""){
                            $voucher_no = 1;
                        }else{
                            $voucher_no++;
                        }
          $series_list[$k]->voucher_no = $voucher_no;
      }

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
    return view('AccountProduction.add_account_production')
        ->with('items', $items)
        ->with('date', $date)
        ->with('series_list', $series_list)
        ->with('item_stock_size', $groupedItems)
        ->with('previousElectricity', $previousElectricity);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
      // echo "<pre>";
      // print_r($request->all());
      // die;
      $financial_year = Session::get('default_fy');
      $date = $request->input('date');
      $date = date('y-m-d', strtotime($date));
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
        
      $check = ItemLedger::where('txn_date',$request->input('date'))
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('source','7')
                        ->where('source_id',null)
                        ->first();
     if(!$check){
        return redirect('account-production')->withError('Entry Not Found In Item Ledger');
     }
      $stockjournal = new AccountProduction;
      $stockjournal->production_date = $date;
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
            $stockjournaldetail = new AccountProductionDetail;
            $stockjournaldetail->production_date = $date;
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
            $item_ledger->source = 7;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $consume_item[$key];
            $average_detail->type = 'PRODUCTION CONSUME';
            $average_detail->production_out_id = $stockjournal->id;
            $average_detail->production_out_weight = $consume_weight[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$consume_item[$key],$request->input('series_no'));
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new AccountProductionDetail;
            $stockjournaldetail->production_date = $date;
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
                        ->where('source','7')
                        ->where('source_id',null)
                        ->update([
                           "source_id"=>$stockjournal->id,
                           "price"=>$generated_price[$key],
                           //"total_price"=>DB::raw("in_weight * ".$generated_price[$key])
                           'total_price' => DB::raw('ROUND(in_weight * ' . (float) $generated_price[$key] . ', 2)')
                        ]);
            //Update Data In Average Details table
            ItemAverageDetail::where('item_id',$generated_item[$key])
                        ->where('entry_date',$request->input('date'))
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('type','PRODUCTION GENERATE')
                        ->where('production_in_id',null)
                        ->update([
                           "production_in_id"=>$stockjournal->id,
                           //"production_in_amount"=>DB::raw("production_in_weight * ".$generated_price[$key])
                           'production_in_amount' => DB::raw('ROUND(production_in_weight * ' . (float) $generated_price[$key] . ', 2)')
                        ]);
            CommonHelper::RewriteItemAverageByItem($request->date,$generated_item[$key],$request->input('series_no'));
         }
            DeckleProcess::whereDate('end_time_stamp', $request->date)
                        ->where('company_id', Session::get('user_company_id'))
                        ->where('stock_journal_status',0)
                        ->UPDATE(['stock_journal_status'=>$stockjournal->id]);
         $consumption = new Consumption;
         $consumption->stock_journal_id = $stockjournal->id;
         $consumption->electricity_units = $request->input('electricity_consumed_units');
          $consumption->electricity_unit_night = $request->input('electricity_consumed_units_night');
         $consumption->unit_price = $request->input('electricity_unit_price');
         $consumption->fixed_cost = $request->input('fixed_cost');
         $consumption->date = $request->date;
         $consumption->company_id = Session::get('user_company_id');
         $consumption->status = 1;
         $consumption->created_by = Session::get('user_id');
         $consumption->created_at = Carbon::now();
         $consumption->save();
         
         return redirect('account-production')->withSuccess('Production Entry Added Successfully!'); 
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function edit($id)
   {
      $companyId = Session::get('user_company_id');

      // Validate ID and ownership
      $production = AccountProduction::with('productionDetail')
                    ->where('id', $id)
                    ->where('company_id', $companyId)
         //->where('delete_status', '0') // if you use soft delete flag
                    ->first();
        $production = AccountProduction::with(['productionDetail' => function ($q) {
                $q->leftJoin('manage_items', 'account_production_details.new_item', '=', 'manage_items.id')
                  ->select('account_production_details.*', 'manage_items.name as item_name');
            }])
            ->where('account_productions.id', $id)
            ->where('account_productions.company_id', $companyId)
            ->first();

      if (!$production) {
         return redirect()
               ->back()
               ->with('error', 'Invalid or unauthorized record.');
      }
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
      $previousElectricity = DB::table('consumption')
        ->where('company_id', Session::get('user_company_id'))
        ->where('stock_journal_id','!=',$id)
        ->orderBy('id', 'desc')
        ->first();
      //   echo "<pre>";
      //   print_r($previousElectricity->toArray());die;
      $electricity = DB::table('consumption')
                                 ->where('stock_journal_id',$id)
                                 ->orderBy('id', 'desc')
                                 ->first();
        
      //echo "<pre>";print_r($production->toArray());die;
      return view('AccountProduction.edit_account_production', compact('production','series_list','items','electricity','previousElectricity'));
   }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      // echo $id;
      // echo "<pre>";
      // print_r($request->all());
      // die;
      DB::beginTransaction();
      try {
         $financial_year = Session::get('default_fy');
         $date = date('Y-m-d', strtotime($request->date));

         $stockjournal = AccountProduction::findOrFail($id);

         // ================= Parent Update =================
         $stockjournal->production_date = $date;
         $stockjournal->narration = $request->narration;
         $stockjournal->series_no = $request->series_no;
         $stockjournal->material_center = $request->material_center;
         $stockjournal->voucher_no_prefix = $request->voucher_prefix;
         $stockjournal->voucher_no = $request->voucher_no;
         $stockjournal->financial_year = $financial_year;
         $stockjournal->updated_by = Session::get('user_id');
         $stockjournal->updated_at = now();
         $stockjournal->save();

         // ================= Clear Old Data =================
         $desc_item_arr = AccountProductionDetail::where('parent_id',$id)
                                                ->pluck('consume_item')
                                                ->toArray();
         AccountProductionDetail::where('parent_id', $id)->delete();
         ItemLedger::where('source', 7)->where('source_id', $id)->where('out_weight','!=','')->delete();         
         ItemAverageDetail::where('type','PRODUCTION CONSUME')->where('production_out_id',$id)->delete();
         Consumption::where('stock_journal_id', $id)->delete();

         // ================= Consume Items =================
         $consume_item_array = [];
         foreach ($request->consume_item as $key => $itemId) {
               if (!$itemId) continue;
               if(array_key_exists($itemId,$consume_item_array)){
                  $consume_item_array[$itemId] = $consume_item_array[$itemId] + $request->consume_weight[$key];
               }else{
                  $consume_item_array[$itemId] = $request->consume_weight[$key];
               }
               AccountProductionDetail::create([
                  'production_date' => $date,
                  'parent_id' => $id,
                  'consume_item' => $itemId,
                  'consume_item_unit' => $request->consume_units[$key],
                  'consume_item_unit_name' => $request->consume_unit_name[$key],
                  'consume_weight' => $request->consume_weight[$key],
                  'consume_price' => $request->consume_price[$key],
                  'consume_amount' => $request->consume_amount[$key],
                  'company_id' => Session::get('user_company_id'),
                  'created_by' => Session::get('user_id'),
                  'created_at' => now(),
               ]);

               ItemLedger::create([
                  'item_id' => $itemId,
                  'series_no' => $request->series_no,
                  'out_weight' => $request->consume_weight[$key],
                  'txn_date' => $date,
                  'price' => $request->consume_price[$key],
                  'total_price' => $request->consume_amount[$key],
                  'company_id' => Session::get('user_company_id'),
                  'source' => 7,
                  'source_id' => $id,
                  'created_by' => Session::get('user_id'),
                  'created_at' => now(),
               ]);

               ItemAverageDetail::create([
                  'entry_date' => $date,
                  'series_no' => $request->series_no,
                  'item_id' => $itemId,
                  'type' => 'PRODUCTION CONSUME',
                  'production_out_id' => $id,
                  'production_out_weight' => $request->consume_weight[$key],
                  'company_id' => Session::get('user_company_id'),
                  'created_at' => Carbon::now(),
               ]);

               CommonHelper::RewriteItemAverageByItem(
                  $date, $itemId, $request->series_no
               );
         }
         foreach ($desc_item_arr as $key => $value) {
            if(!array_key_exists($value, $consume_item_array)){
               CommonHelper::RewriteItemAverageByItem($date,$value,$request->input('series_no'));
            }
         }
         // ================= Generated Items =================
         foreach ($request->generated_item_id as $key => $itemId) {

               AccountProductionDetail::create([
                  'production_date' => $date,
                  'parent_id' => $id,
                  'new_item' => $itemId,
                  'new_item_unit' => $request->generated_units[$key],
                  'new_item_unit_name' => $request->generated_unit_name[$key],
                  'new_weight' => $request->generated_weight[$key],
                  'new_price' => $request->generated_price[$key],
                  'new_amount' => $request->generated_amount[$key],
                  'company_id' => Session::get('user_company_id'),
                  'created_by' => Session::get('user_id'),
                  'created_at' => now(),
               ]);

               ItemLedger::where('item_id', $itemId)
                  ->where('txn_date', $date)
                  ->where('company_id', Session::get('user_company_id'))
                  ->where('source', 7)
                  ->update([
                     'source_id' => $id,
                     'price' => $request->generated_price[$key],
                     'total_price' => DB::raw(
                           'ROUND(in_weight * ' . (float)$request->generated_price[$key] . ',2)'
                     ),
                  ]);

               ItemAverageDetail::where('item_id', $itemId)
                  ->where('entry_date', $date)
                  ->where('company_id', Session::get('user_company_id'))
                  ->where('type', 'PRODUCTION GENERATE')
                  ->update([
                     'production_in_id' => $id,
                     'production_in_amount' => DB::raw(
                           'ROUND(production_in_weight * ' . (float)$request->generated_price[$key] . ',2)'
                     ),
                  ]);

               CommonHelper::RewriteItemAverageByItem(
                  $date, $itemId, $request->series_no
               );
         }

         // ================= Consumption =================
         Consumption::create([
               'stock_journal_id' => $id,
               'electricity_units' => $request->electricity_consumed_units_day,
               'electricity_unit_night' => $request->electricity_consumed_units_night,
               'unit_price' => $request->electricity_unit_price,
               'fixed_cost' => $request->fixed_cost,
               'date' => $date,
               'company_id' => Session::get('user_company_id'),
               'status' => 1,
               'created_by' => Session::get('user_id'),
               'created_at' => now(),
         ]);

         DB::commit();

         return redirect('account-production')
               ->withSuccess('Production Entry Updated Successfully!');

      } catch (\Exception $e) {
         echo $e->getMessage();die;
         DB::rollBack();
         return back()->withErrors($e->getMessage());
      }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $stock_journal = AccountProduction::find($id);
      $delete = AccountProduction::where('id',$id)->delete();
      if($delete){
         if($stock_journal->consumption_entry_status==1){
            ItemAverageDetail::where('company_id',Session::get('user_company_id'))
                        ->where('type','PRODUCTION GENERATE')
                        ->where('production_in_id',$id)
                        ->update([
                           "production_in_id"=>null,
                           "production_in_amount"=>DB::raw("production_in_weight * 1")
                        ]);
         }
         ItemAverageDetail::where('production_out_id',$id)
                        ->where('type','PRODUCTION CONSUME')
                        ->delete();
         $desc = AccountProductionDetail::where('parent_id',$id)->get();
         foreach ($desc as $key => $value) {
            if(!empty($value->consume_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->production_date,$value->consume_item,$stock_journal->series_no);
            }else if(!empty($value->new_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->production_date,$value->new_item,$stock_journal->series_no);
            }               
         }
         AccountProductionDetail::where('parent_id',$id)->delete();
         if($stock_journal->consumption_entry_status==1){            
            ItemLedger::where('company_id',Session::get('user_company_id'))
                  ->where('source','7')
                  ->where('source_id',$id)
                 // ->where('item_id','!=',$item->id)
                  ->where('in_weight','!=','')
                  ->update([
                     "source_id"=>null,
                     "price"=>1,
                     "total_price"=>DB::raw("in_weight * 1")
                  ]);
            ItemLedger::where('source_id',$id)
                        ->where('source','7')
                        ->where('out_weight','!=','')
                        ->delete();
         }
         //Consumption Entry Revert
         if($stock_journal->consumption_entry_status==1){
            DeckleProcess::whereDate('end_time_stamp', $stock_journal->production_date)
                        ->where('company_id', Session::get('user_company_id'))
                        ->UPDATE(['stock_journal_status'=>0]);
            Consumption::where('stock_journal_id',$stock_journal->id)->delete();
         }
         return redirect('account-production')->withSuccess('Stock Journal Deleted Successfully!');          
      }   
    }
    
    public function exportCsv1(Request $request)
{
    $from_date = date('Y-m-d', strtotime($request->from_date));
    $to_date   = date('Y-m-d', strtotime($request->to_date));

    $company_id = Session::get('user_company_id');

    $journals = DB::table('account_production_details')
        ->leftJoin('manage_items', 'account_production_details.consume_item', '=', 'manage_items.id')
        ->leftJoin('manage_items as new', 'account_production_details.new_item', '=', 'new.id')
        ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
        ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
        ->join('account_productions', 'account_production_details.parent_id', '=', 'account_productions.id')
        ->select(
            'account_production_details.parent_id as id',
            'account_production_details.production_date',
            'account_productions.voucher_no_prefix',
            'manage_items.name as consume_item',
            'consume_weight',
            'units.s_name as consume_unit',
            'consume_price',
            'consume_amount',
            'new.name as generated_item',
            'new_weight',
            'new_unit.s_name as generated_unit',
            'new_price',
            'new_amount'
        )
        ->where('account_production_details.status', 1)
        ->where('account_production_details.company_id', $company_id)
        ->whereBetween(DB::raw('DATE(account_production_details.production_date)'), [$from_date, $to_date])
        ->orderBy('account_production_details.production_date', 'asc')
        ->orderBy('account_production_details.parent_id')
        ->get();

    // CSV Headers
    $fileName = "production_export_" . date('YmdHis') . ".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = [
        'Date',
        'Voucher No',
        'Consume Item',
        'Consume Qty',
        'Unit',
        'Rate',
        'Amount',
        'Generated Item',
        'Generated Qty',
        'Unit',
        'Rate',
        'Amount'
    ];

    $callback = function() use ($journals, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($journals as $row) {
            fputcsv($file, [
                $row->production_date,
                $row->voucher_no_prefix,
                $row->consume_item,
                $row->consume_weight,
                $row->consume_unit,
                $row->consume_price,
                $row->consume_amount,
                $row->generated_item,
                $row->new_weight,
                $row->generated_unit,
                $row->new_price,
                $row->new_amount
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportCsv2(Request $request)
{
    $from_date = date('Y-m-d', strtotime($request->from_date));
    $to_date   = date('Y-m-d', strtotime($request->to_date));
    $company_id = Session::get('user_company_id');

    $journals = DB::table('account_production_details')
        ->leftJoin('manage_items', 'account_production_details.consume_item', '=', 'manage_items.id')
        ->leftJoin('manage_items as new', 'account_production_details.new_item', '=', 'new.id')
        ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
        ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
        ->join('account_productions', 'account_production_details.parent_id', '=', 'account_productions.id')
        ->select(
            'account_production_details.parent_id',
            'account_production_details.production_date',
            'account_productions.voucher_no_prefix',
            'manage_items.name as consume_item',
            'consume_weight',
            'units.s_name as consume_unit',
            'consume_price',
            'consume_amount',
            'new.name as generated_item',
            'new_weight',
            'new_unit.s_name as generated_unit',
            'new_price',
            'new_amount'
        )
        ->where('account_production_details.status', 1)
        ->where('account_production_details.company_id', $company_id)
        ->whereBetween(DB::raw('DATE(account_production_details.production_date)'), [$from_date, $to_date])
        ->orderBy('account_production_details.parent_id')
        ->get();

    // Group by voucher
    $grouped = [];
    foreach ($journals as $row) {
        $grouped[$row->parent_id][] = $row;
    }

    $fileName = "production_export_" . date('YmdHis') . ".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
    ];

    $callback = function() use ($grouped) {

        $file = fopen('php://output', 'w');

        foreach ($grouped as $voucherId => $rows) {

            $first = $rows[0];

            // Header
            fputcsv($file, ['Production No', $voucherId]);
            fputcsv($file, ['Date', $first->production_date]);
            fputcsv($file, ['Voucher', $first->voucher_no_prefix]);
            fputcsv($file, []);

            // ================= CONSUMED =================
            fputcsv($file, ['ITEMS CONSUMED']);
            fputcsv($file, ['S.No', 'Item', 'Qty', 'Unit', 'Rate', 'Amount']);

            $i = 1;
            $consume_total = 0;

            foreach ($rows as $row) {
                if ($row->consume_item) {
                    fputcsv($file, [
                        $i++,
                        $row->consume_item,
                        $row->consume_weight,
                        $row->consume_unit,
                        $row->consume_price,
                        $row->consume_amount
                    ]);
                    $consume_total += $row->consume_amount;
                }
            }

            fputcsv($file, ['', 'Total', '', '', '', $consume_total]);
            fputcsv($file, []);

            // ================= GENERATED =================
            fputcsv($file, ['ITEMS GENERATED']);
            fputcsv($file, ['S.No', 'Item', 'Qty', 'Unit', 'Rate', 'Amount']);

            $i = 1;
            $gen_total = 0;

            foreach ($rows as $row) {
                if ($row->generated_item) {
                    fputcsv($file, [
                        $i++,
                        $row->generated_item,
                        $row->new_weight,
                        $row->generated_unit,
                        $row->new_price,
                        $row->new_amount
                    ]);
                    $gen_total += $row->new_amount;
                }
            }

            fputcsv($file, ['', 'Total', '', '', '', $gen_total]);

            // spacing between vouchers
            fputcsv($file, []);
            fputcsv($file, []);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportCsv(Request $request)
{

    if (!$request->from_date || !$request->to_date) {
        return back()->with('error', 'Please select date range');
    }
  

    $from_date = date('Y-m-d', strtotime($request->from_date));
    $to_date   = date('Y-m-d', strtotime($request->to_date));
    $company_id = Session::get('user_company_id');

    $journals = DB::table('account_production_details')
        ->leftJoin('manage_items', 'account_production_details.consume_item', '=', 'manage_items.id')
        ->leftJoin('manage_items as new', 'account_production_details.new_item', '=', 'new.id')
        ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
        ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
        ->join('account_productions', 'account_production_details.parent_id', '=', 'account_productions.id')
        ->select(
            'account_production_details.parent_id',
            'account_production_details.production_date',
            'account_productions.voucher_no_prefix',
            'manage_items.name as consume_item',
            'consume_weight',
            'units.s_name as consume_unit',
            'consume_price',
            'consume_amount',
            'new.name as generated_item',
            'new_weight',
            'new_unit.s_name as generated_unit',
            'new_price',
            'new_amount'
        )
        ->where('account_production_details.status', 1)
        ->where('account_production_details.company_id', $company_id)
        ->whereBetween(DB::raw('DATE(account_production_details.production_date)'), [$from_date, $to_date])
        ->orderBy('account_production_details.parent_id')
        ->get();
   

    // Group by voucher
    $grouped = [];
    foreach ($journals as $row) {
        $grouped[$row->parent_id][] = $row;
    }

    $fileName = "production_export_" . date('YmdHis') . ".csv";

    // 🔥 VERY IMPORTANT
    if (ob_get_level()) {
        ob_end_clean();
    }

    return response()->streamDownload(function () use ($grouped) {

        $file = fopen('php://output', 'w');

        foreach ($grouped as $voucherId => $rows) {

            $first = $rows[0];

            fputcsv($file, ['Production No', $voucherId]);
            fputcsv($file, ['Date', $first->production_date]);
            fputcsv($file, ['Voucher', $first->voucher_no_prefix]);
            fputcsv($file, []);

            // CONSUMED
            fputcsv($file, ['ITEMS CONSUMED']);
            fputcsv($file, ['S.No', 'Item', 'Qty', 'Unit', 'Rate', 'Amount']);

            $i = 1;
            $consume_total = 0;

            foreach ($rows as $row) {
                if ($row->consume_item) {
                    fputcsv($file, [
                        $i++,
                        $row->consume_item,
                        $row->consume_weight,
                        $row->consume_unit,
                        $row->consume_price,
                        $row->consume_amount
                    ]);
                    $consume_total += $row->consume_amount;
                }
            }

            fputcsv($file, ['', 'Total', '', '', '', $consume_total]);
            fputcsv($file, []);

            // GENERATED
            fputcsv($file, ['ITEMS GENERATED']);
            fputcsv($file, ['S.No', 'Item', 'Qty', 'Unit', 'Rate', 'Amount']);

            $i = 1;
            $gen_total = 0;

            foreach ($rows as $row) {
                if ($row->generated_item) {
                    fputcsv($file, [
                        $i++,
                        $row->generated_item,
                        $row->new_weight,
                        $row->generated_unit,
                        $row->new_price,
                        $row->new_amount
                    ]);
                    $gen_total += $row->new_amount;
                }
            }

            fputcsv($file, ['', 'Total', '', '', '', $gen_total]);

            fputcsv($file, []);
            fputcsv($file, []);
        }

        fclose($file);

    }, $fileName, [
        'Content-Type' => 'text/csv',
    ]);
}

}
