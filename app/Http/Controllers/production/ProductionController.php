<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductionItem;
use App\Models\DeckleProcess;
use App\Models\ItemGroups;
use App\Models\SaleOrderSetting;
use App\Models\DeckleProcessQuality;
use App\Models\ManageItems;
use App\Models\DeckleMachineStopLog;
use App\Models\DeckleItem;
use App\Models\ItemSizeStock;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemLedger;
use App\Models\ItemAverageDetail;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\Consumption_rate_item_wise;
use App\Models\ConsumptionItems;
class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function getDeckleNo($date)
    {
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        if ($month >= 4) {
            $startDate = $year . '-04-01';
            $endDate   = ($year + 1) . '-03-31';
        } else {
            $startDate = ($year - 1) . '-04-01';
            $endDate   = $year . '-03-31';
        }        
        if($startDate && $endDate){
            $from_date = Carbon::parse($startDate)->startOfDay();
            $to_date   = Carbon::parse($endDate)->endOfDay();
            $deckle_no = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->max('deckle_no');
        }else{
            $deckle_no = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                    ->max('deckle_no');
        }        
        if($deckle_no==""){
            $deckle_no = 1;
        }else{
            $deckle_no++;
        }
        return $deckle_no;
    }
    public function index(Request $request)
    {
        $company_id = Session::get('user_company_id');
        
        $running_deckle = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->first();
               
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id','name','bf','gsm','speed','manage_items.id as item_id')
                                ->where('production_items.company_id',Session::get('user_company_id'))
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        $deckle = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','start_time_stamp','end_time_stamp','speed','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',1)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','stop_machine_status')
                                ->first();
        $completed_deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
                                }])
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',2)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->get();
        $qualities = DeckleProcessQuality::join('manage_items', 'deckle_process_qualities.item_id', '=', 'manage_items.id')
            ->where('deckle_process_qualities.company_id', $company_id)
            ->select('deckle_process_qualities.item_id', 'manage_items.name')
            ->distinct()
            ->get();

        $summary_records = collect();

        if ($request->has('quality_id') && $request->quality_id != '') {

            $query = DeckleProcessQuality::join('manage_items', 'deckle_process_qualities.item_id', '=', 'manage_items.id')
                ->where('deckle_process_qualities.company_id', $company_id)
                ->whereNotNull('deckle_process_qualities.end_time_stamp'); // only completed rolls

            if ($request->quality_id !== 'all') {
                $query->where('deckle_process_qualities.item_id', $request->quality_id);
            }

            if ($request->from_date) {
                $query->whereDate('deckle_process_qualities.start_time_stamp', '>=', $request->from_date);
            }

            if ($request->to_date) {
                $query->whereDate('deckle_process_qualities.start_time_stamp', '<=', $request->to_date);
            }

            $summary_records = $query->select(
                'manage_items.name as quality_name',
                'deckle_process_qualities.bf',
                'deckle_process_qualities.gsm',
                'deckle_process_qualities.production_in_kg'
            )->get();
        }

        return view('production.index', [
            "deckle" => $deckle,
            "running_deckle" => $running_deckle,
            "items" => $items,
            "completed_deckles" => $completed_deckles,
            "qualities" => $qualities,
            "summary_records" => $summary_records,
            "selected_quality" => $request->quality_id ?? 'all', 
            "from_date" => $request->from_date ?? date('Y-m-d'),
            "to_date" => $request->to_date ?? date('Y-m-d')
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * Display all saved set items for this company
     */
    public function setItems()
    {
        $company_id = Session::get('user_company_id');

        $setItems = ProductionItem::where('company_id', $company_id)
                                  ->with('item') // eager load item details
                                  ->get();

        return view('production.set_item', compact('setItems'));
    }
    /**
     * Show the add set item page with only allowed and not yet added items
     */
    public function create(Request $request)
    {
        $deckle = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->first();
        if($deckle){
            return redirect()->route('deckle-process.index')->with('success','Pop Roll Already In Running');
        }
        $quality_id = $request->quality_id;
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id as item_id','name','bf','gsm','speed')
                                ->select('production_items.id','name','bf','gsm','speed','item_id')
                                ->where('production_items.company_id',Session::get('user_company_id'))
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        $deckle_no = $this->getDeckleNo(date('Y-m-d'));        
        return view('production/add_deckle',["items"=>$items,"deckle_no"=>$deckle_no,"quality_id"=>$quality_id]);
    }
    public function stopDeckleProcess(Request $request)
    {
        $end_time_stamp = Carbon::parse($request->new_actual_end_time_stamp);
        if ($end_time_stamp->hour < 8) {
            $end_time_stamp->subDay();
        }
        $end_time_stamp = $end_time_stamp->format('Y-m-d H:i:s');
        $update = DeckleProcess::where('company_id',Session::get('user_company_id'))
                            ->where('id',$request->id)
                            ->update(['stopped_by'=>Session('user_id'),'end_time_stamp'=>$end_time_stamp,"status"=>2]);
        DeckleProcessQuality::where('company_id',Session::get('user_company_id'))
                            ->where('id',$request->last_row_id)
                            ->update(["production_in_kg"=>$request->production_in_kg,"speed"=>$request->new_actual_speed,"gsm"=>$request->new_actual_gsm,'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);
        if($update){
            return json_encode(array("status"=>true));
        }else{
            return json_encode(array("status"=>false));
        }
        
    }
    public function stopDeckleMachine(Request $request)
    {
        $update = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('id',$request->id)
                                ->update(['stop_machine_status'=>1]);
        if($update){
            $log = new DeckleMachineStopLog;
            $log->deckle_id = $request->id;
            $log->deckle_no = $request->deckle_no;
            $log->stopped_by = Session::get('user_id');
            $log->stopped_at = date('Y-m-d H:i:s');
            $log->reason = $request->reason;
            $log->remark = $request->remark;
            $log->company_id = Session::get('user_company_id');
            $log->created_at = Carbon::now();
            $log->save();
            return json_encode(array("status"=>true));
        }else{
            return json_encode(array("status"=>false));
        }        
    }
    public function startDeckleMachine(Request $request)
    {
        $update = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('id',$request->id)
                                ->update(['stop_machine_status'=>0]);
        if($update){
            DeckleMachineStopLog::where('deckle_id',$request->id)
                                ->where('deckle_no',$request->deckle_no)
                                ->where('start_at',null)
                                ->update(['start_by'=>Session::get('user_id'),'start_at'=>date('Y-m-d H:i:s')]);
            
            return json_encode(array("status"=>true));
        }else{
            return json_encode(array("status"=>false));
        }        
    }    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'item_bf' => 'required',
            'item_gsm' => 'required',
            'deckle_no' => 'required',
            'start_time_stamp' => 'required',
            'speed' => 'required',
        ]);
       
        $start_time_stamp = Carbon::parse($request->start_time_stamp);
        if ($start_time_stamp->hour < 8) {
            $start_time_stamp->subDay();
        }
        $start_time_stamp = $start_time_stamp->format('Y-m-d H:i:s');
        $deckle_no = $this->getDeckleNo(date('Y-m-d',strtotime($request->start_time_stamp)));
        $deckle = new DeckleProcess;
        $deckle->deckle_no = $deckle_no;
        $deckle->start_time_stamp = $start_time_stamp;
        $deckle->started_by = Session::get('user_id');
        $deckle->company_id = Session::get('user_company_id');
        $deckle->created_at = Carbon::now();
        if($deckle->save()){
            $deckle_quality = new DeckleProcessQuality;
            $deckle_quality->parent_id = $deckle->id;
            $deckle_quality->item_id = $request->item_id;
            $deckle_quality->bf = $request->item_bf;
            $deckle_quality->gsm = $request->item_gsm;
            $deckle_quality->deckle_no = $deckle_no;
            $deckle_quality->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));
            $deckle_quality->speed = $request->speed;
            $deckle_quality->started_by = Session::get('user_id');
            $deckle_quality->company_id = Session::get('user_company_id');
            $deckle_quality->created_at = Carbon::now();
            $deckle_quality->save();
            return redirect()->route('deckle-process.index')->with('success','Deckle added successfully');
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
        $deckle = DeckleProcess::join('manage_items','deckle_processes.item_id','=','manage_items.id')
                                ->select('deckle_processes.*','name')
                                ->find($id);
        $deckle_quality = DeckleProcessQuality::join('manage_items','deckle_process_qualities.item_id','=','manage_items.id')
                                ->select('deckle_process_qualities.*','name')
                                ->where('parent_id',$id)
                                ->get();
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id','name','bf','gsm','speed')
                                ->where('production_items.company_id',Session::get('user_company_id'))
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        return view('production/view_deckle_process',["deckle"=>$deckle,"items"=>$items,"deckle_qualitys"=>$deckle_quality]);
    }   
   public function editItem($id)
    {

    $companyId = Session::get('user_company_id');
    $companyData = Companies::where('id', $companyId)->first();
    $item_id = ProductionItem::where('id',$id)->value('item_id');
    $production = ProductionItem::where('production_items.id', $id)
    ->join('manage_items', 'production_items.item_id', '=', 'manage_items.id')
    ->select('manage_items.name', 'production_items.*')
    ->first();

    // -------------------
    // FETCH SERIES (same logic as store)
    // -------------------
    $series = collect();
    if ($companyData->gst_config_type === "single_gst") {

        $series = DB::table('gst_settings')
                    ->where(['company_id' => $companyId, 'gst_type' => "single_gst"])
                    ->get();

        $branch = GstBranch::select('id','branch_series as series')
                    ->where(['delete' => '0', 'company_id' => $companyId,'gst_setting_id'=>$series[0]->id])
                    ->get();

        if($branch->count() > 0){
            $series = $series->merge($branch);
        }

    } elseif ($companyData->gst_config_type === "multiple_gst") {

        $series = DB::table('gst_settings_multiple')
                    ->select('id','series')
                    ->where(['company_id' => $companyId, 'gst_type' => "multiple_gst"])
                    ->get();

        foreach ($series as $value) {
            $branch = GstBranch::select('id','branch_series as series')
                    ->where(['delete' => '0', 'company_id' => $companyId,'gst_setting_multiple_id'=>$value->id])
                    ->get();
            if($branch->count() > 0){
                $series = $series->merge($branch);
            }
        }
    }

    // -------------------
    // FETCH ITEM
    // -------------------
    $item = ManageItems::findOrFail($item_id);

    // -------------------
    // FETCH SERIES OPENING (ItemBalanceBySeries)
    // -------------------
    $series_open = ItemBalanceBySeries::select('series','opening_amount','opening_quantity','type')
                    ->where('item_id', $item_id)
                    ->get();

    $grouped = $series_open->groupBy('series')->toArray();

    // attach opening values (if exist) to series list
    foreach ($series as $key => $value) {
        if (isset($grouped[$value->series])) {
            $series[$key]->opening_amount = $grouped[$value->series][0]['opening_amount'];
            $series[$key]->opening_quantity = $grouped[$value->series][0]['opening_quantity'];
            $series[$key]->type = $grouped[$value->series][0]['type'];
        } else {
            $series[$key]->opening_amount = "";
            $series[$key]->opening_quantity = "";
            $series[$key]->type = "";
        }
    }

    // -------------------
    // FETCH REELS (ItemSizeStock / itemstocksize)
    // deckle_id = 0  (we show all reels; only status==1 will be editable)
    // -------------------
    $reels = ItemSizeStock::where('item_id', $item_id)
                ->where('deckle_id', 0)
                ->where('company_id', $companyId)
                ->orderBy('id','asc')
                ->get();

    // return blade view (not JSON) because you asked for edit page
    return view('production.edit_set_item', [
        'item'   => $item,
        'series' => $series,
        'reels'  => $reels,
        'production'=>$production,
        // pass any other required data like $groups, etc, used in select dropdowns
        'groups' => $this->getGroupsForItemDropdown($companyId) // implement or replace appropriately
    ]);


    }

    protected function getGroupsForItemDropdown($companyId)
{
    // implement per your app; temporary example:
    return DB::table('item_groups') // replace with real relationship
            ->where('company_id', $companyId)
            ->get(); // adapt structure used in blade (optgroup->items)
}

    /**
     * Update set item
     */
   public function updateItem(Request $request, $id)
    {
           
    $production_id = ProductionItem::where('item_id',$id)->value('id');
    $company_id = Session::get('user_company_id');
    $created_by = Session::get('user_id');

    // Basic validation for main fields
    $validator = Validator::make($request->all(), [
        'item_id' => 'required|integer',
        'bf'      => 'required|numeric',
        'gsm'     => 'required|numeric',
        'speed'   => 'nullable|numeric',
        'status'  => 'required|in:0,1',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();
    try {
        // 1) Update Manage Items basic fields
        $item =  ProductionItem::findOrFail($production_id);
        $item->item_id = $request->id;
        $item->bf = $request->bf;
        $item->gsm = $request->gsm;
        $item->speed = $request->speed;
        $item->status = $request->status;
        $item->save();

        // 2) HANDLE REELS (itemstocksize)
        // request fields expected:
        // reels[] => existing reels rows: each contains id, size, weight, reel_no, bf, gsm, unit, keep (1 or 0)
        // new_reels[] => newly added reels (no id)
        // deleted_reels[] => ids of existing reels to delete

        // DELETE requested existing reels
        $deletedReels = json_decode($request->deleted_reels, true) ?? [];

if (!is_array($deletedReels)) {
    $deletedReels = [];
}
        if (!empty($deletedReels)) {
            ItemSizeStock::whereIn('id', $deletedReels)
                ->where('company_id', $company_id)
                ->where('item_id', $id)
                ->delete();
        }

        // UPDATE existing reels (only if status == 1 in DB)
        $reelsInput = $request->input('reels', []);
        foreach ($reelsInput as $r) {
            if (empty($r['id'])) continue;
            $reel = ItemSizeStock::where('id', $r['id'])
                        ->where('company_id', $company_id)
                        ->where('item_id', $id)
                        ->first();
            if (!$reel) continue;

            // Only editable if status == 1
            if ($reel->status == 1) {
                $reel->size = $r['size'] ?? $reel->size;
                $reel->weight = is_numeric($r['weight']) ? $r['weight'] : $reel->weight;
                $reel->reel_no = $r['reel_no'] ?? $reel->reel_no;
                $reel->bf = $r['bf'] ?? $reel->bf;
                $reel->gsm = $r['gsm'] ?? $reel->gsm;
                $reel->unit = $r['unit'] ?? $reel->unit;
                // keep deckle_id & status & created_by, company_id unchanged
                $reel->save();
            }
        }

        // INSERT new reels (new_reels)
        $newReels = $request->input('new_reels', []);

        foreach ($newReels as $nr) {
            // basic validation for new row
            if (empty($nr['size']) || !isset($nr['weight'])) continue;
            ItemSizeStock::create([
                'item_id'    => $id,
                'size'       => strtoupper($nr['size']),
                'weight'     => $nr['weight'],
                'reel_no'    => $nr['reel_no'] ?? null,
                'bf'         => $nr['bf'] ?? $request->bf,
                'gsm'        => $nr['gsm'] ?? $request->gsm,
                'unit'       => $nr['unit'] ?? null,
                'deckle_id'  => 0,
                'status'     => 1,
                'company_id' => $company_id,
                'created_by' => $created_by,
                'created_at' => now(),
            ]);
        }

        // 3) HANDLE SERIES (ItemBalanceBySeries) and ItemLedger opening entries
        // expected input from form:
        // series_inputs[] each with keys: series, amt, qty, type

        $series_inputs = $request->input('series_inputs', []); // array of arrays

        if (!empty($series_inputs)) {
            // delete existing openings & ledger opening rows
            ItemBalanceBySeries::where('item_id', $id)->delete();
            ItemLedger::where('item_id', $id)->where('source', '-1')->delete();

            foreach ($series_inputs as $s) {
                // skip empty
                $amt = isset($s['amt']) ? trim(str_replace(',', '', $s['amt'])) : 0;
                $qty = isset($s['qty']) ? trim(str_replace(',', '', $s['qty'])) : 0;

                $series_balance = new ItemBalanceBySeries();
                $series_balance->item_id = $id;
                $series_balance->series = $s['series'];
                $series_balance->opening_amount = $amt;
                $series_balance->opening_quantity = $qty;
                $series_balance->type = $s['type'] ?? null;
                $series_balance->company_id = $company_id;
                $series_balance->created_at = now();
                $series_balance->updated_at = now();
                $series_balance->save();

                // ledger entry
                $ledger = new ItemLedger();
                $ledger->item_id = $id;
                if (($s['type'] ?? '') == 'Debit') {
                    $ledger->in_weight = $qty;
                } else if (($s['type'] ?? '') == 'Credit') {
                    $ledger->out_weight = $qty;
                }
                $ledger->series_no = $s['series'];
                $ledger->total_price = $amt;
                $ledger->company_id = $company_id;
                $ledger->source = -1;
                $ledger->created_by = $created_by;
                $ledger->created_at = date('Y-m-d H:i:s');
                $default_fy = explode("-", Session::get('default_fy'));
                $txn_date = $default_fy[0] . "-04-01";
                $ledger->txn_date = date('Y-m-d', strtotime($txn_date));
                $ledger->save();
            }
        }

        // 4) Recalculate and update closing quantities in ItemBalanceBySeries based on sum of all ItemLedger entries for each series
    
        DB::commit();

        return redirect()->route('production.set_item')->with('success', 'Item updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('updateItem error: '.$e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage())->withInput();
    }
    }
    
    
    /**
     * Delete set item
     */
     public function destroyItem($id)
    {
       
        $item = ProductionItem::find($id);
        if ($item) {
            $manage_item_id = $item->value('item_id');
            $exist = ItemSizeStock::where('item_id',$manage_item_id)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->first();
                                   
            if($exist){
               
                 return redirect()->route('production.set_item')->with('error', 'Reels already exist. Cannot delete Item');
            }                          
            $item->delete();

            return redirect()->route('production.set_item')->with('success', 'Item deleted successfully.');
        }
        return redirect()->route('production.set_item')->with('error', 'Item not found.');
    }
    
    
    
    
    public function addQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_item_id' => 'required',
            'new_item_bf' => 'required',
            'new_item_gsm' => 'required',
            'deckle_id' => 'required',
            'new_start_time_stamp' => 'required',
            'actual_production_in_kg' => 'required',
            'actual_production_in_kg' => 'required',
            'new_speed' => 'required',
            'deckle_number' => 'required',
        ]);
        // echo "<pre>";
        // print_r($request->all());die;
         $item = $request->new_item_id;
        $item_get = ProductionItem::where('id',$item)->first();
        $manage_item_id = $item_get->item_id;
        $deckle_process = DeckleProcess::find($request->deckle_id);
        if($deckle_process){
            DeckleProcessQuality::where('company_id',Session::get('user_company_id'))
                                ->where('id',$request->last_row_id)
                                 ->update(["production_in_kg"=>$request->actual_production_in_kg,"speed"=>$request->actual_speed,"gsm"=>$request->actual_gsm]);
            $deckle = new DeckleProcessQuality;
            $deckle->parent_id = $request->deckle_id;
            $deckle->item_id = $manage_item_id;
            $deckle->bf = $request->new_item_bf;
            $deckle->gsm = $request->new_item_gsm;
            $deckle->deckle_no = $request->deckle_number;
            $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->new_start_time_stamp));
            $deckle->production_in_kg = $request->new_production_in_kg;
            $deckle->speed = $request->new_speed;
            $deckle->started_by = Session::get('user_id');
            $deckle->company_id = Session::get('user_company_id');
            $deckle->created_at = Carbon::now();
            if($deckle->save()){
                return redirect()->back()->with('success', 'Quality added successfully');
            }
            
        }
        
        
        // echo "<pre>";print_r($request->all());die;
        $deckle_process = DeckleProcess::find($request->deckle_id);
        if($deckle_process){
            $deckle_process->production_in_kg = $request->actual_production_in_kg;
            $deckle_process->speed = $request->actual_speed;
            if($deckle_process->save()){
                $deckle = new DeckleProcessQuality;
                $deckle->parent_id = $request->deckle_id;
                $deckle->item_id = $request->new_item_id;
                $deckle->bf = $request->new_item_bf;
                $deckle->gsm = $request->new_item_gsm;
                $deckle->deckle_no = $request->deckle_number;
                $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->new_start_time_stamp));
                $deckle->speed = $request->new_speed;
                $deckle->started_by = Session::get('user_id');
                $deckle->company_id = Session::get('user_company_id');
                $deckle->created_at = Carbon::now();
                if($deckle->save()){
                    return redirect()->route('deckle-process.index')->with('success', 'Quality added successfully.');
                }
            }
        }
        
        
    }
    public function addItem()
    {
        $company_id = Session::get('user_company_id');

        // Fetch IDs of items already added in production_items
        $addedItemIds = ProductionItem::where('company_id', $company_id)->pluck('item_id')->toArray();

        // Fetch all active items of this company from manage_items table excluding already added ones
        $items = ManageItems::where('company_id', $company_id)
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->whereNotIn('id', $addedItemIds)
                            ->orderBy('name', 'asc')
                            ->get();

        // Fetch item groups for category display if needed
        $groups = ItemGroups::where('company_id', $company_id)->get();

        return view('production.add_set_item', compact('items', 'groups'));
    }
    
    
   public function updateQtycreate($id){

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

            $manageitems = ManageItems::find($id);
        $item_gst_rate = $manageitems->gst_rate;
        $series_open = ItemBalanceBySeries::select('series','opening_amount','opening_quantity','type')->where('item_id',$id)->get();
      $grouped = $series_open->groupBy('series')->toArray();
      foreach ($series as $key => $value) {
         if(isset($grouped[$value->series])){
            $series[$key]->opening_amount = $grouped[$value->series][0]['opening_amount'];
            $series[$key]->opening_quantity = $grouped[$value->series][0]['opening_quantity'];
            $series[$key]->type = $grouped[$value->series][0]['type'];
         }else{
            $series[$key]->opening_amount = "";
            $series[$key]->opening_quantity = "";
            $series[$key]->type = "";
         }
      }

     return response()->json([
    'manageitems' => $manageitems,
    'series' => $series
]);


    }

 
    
    public function storeItem(Request $request)
{
    $company_id = Session::get('user_company_id');
    $created_by = Session::get('user_id');

    // 1) Validate main form inputs (basic)
    $request->validate([
        'item_id' => 'required|integer',
        'bf'      => 'required|integer',
        'gsm'     => 'required|integer',
        'speed'   => 'required|integer',
        'status'  => 'required|in:0,1',
    ]);

    // prepare collectors
    $errors = [];        // validation errors to show user
    $csv_rows = [];      // CSV rows (validated) to insert later
    $manual_reels = [];  // manual reels from request to insert later
    $series_inputs = []; // series inputs to insert later

    // 0) Prevent duplicate production item for the company (strict)
    $alreadyProd = ProductionItem::where('company_id', $company_id)
                    ->where('item_id', $request->item_id)
                    ->exists();
    if ($alreadyProd) {
        return redirect()->back()->with('error', 'Item already added.')->withInput();
    }

    // ----------------------------
    // VALIDATE CSV (first, NO DB INSERT)
    // ----------------------------
    if ($request->hasFile('csv_file')) {
        $validatorFile = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        if ($validatorFile->fails()) {
            return redirect()->back()->withErrors($validatorFile)->withInput();
        }

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ",");
            $index = 1;

            while (($data = fgetcsv($handle, 10000, ',')) !== false) {
                $index++;
                $data = array_map('trim', $data);
                if (empty(array_filter($data))) continue; // skip blank row

                // expected format: item_name,size,weight,reel_no,bf,gsm,unit
                $item_name = $data[0] ?? null;
                $size      = isset($data[1]) ? strtoupper($data[1]) : null;
                $weight    = $data[2] ?? null;
                $reel_no   = $data[3] ?? null;
                $bf        = $data[4] ?? null;
                $gsm       = $data[5] ?? null;
                $unit      = $data[6] ?? null;

                // row-level validation
                if (!$item_name || !$size || !$weight || !$reel_no || !$unit) {
                    $errors[] = "CSV Row $index: Missing required fields (Item: {$item_name})";
                    continue;
                }

                // find manage_items row
                $item = DB::table('manage_items')
                    ->where('name', $item_name)
                    ->where('company_id', $company_id)
                    ->where('delete', '0')
                    ->select('id')
                    ->first();

                if (!$item) {
                    $errors[] = "CSV Row $index: Item '{$item_name}' not found in Manage Items";
                    continue;
                }

                // If production item for this CSV row's item does not exist,
                // allow it only if it matches the main selected item ($request->item_id)
                $prodExists = DB::table('production_items')
                    ->where('company_id', $company_id)
                    ->where('item_id', $item->id)
                    ->exists();

                if (!$prodExists && $item->id != $request->item_id) {
                    $errors[] = "CSV Row $index: Item '{$item_name}' not found in Production Items (and not the selected main item)";
                    continue;
                }

                // collect validated csv row (we'll check reel duplicates later together)
                $csv_rows[] = [
                    'item_id' => $item->id,
                    'size'    => $size,
                    'weight'  => $weight,
                    'reel_no' => $reel_no,
                    'bf'      => $bf,
                    'gsm'     => $gsm,
                    'unit'    => $unit,
                    'row_no'  => $index,
                ];
            } // while
            fclose($handle);
        } else {
            $errors[] = "Unable to read uploaded CSV file.";
        }
    }

    // ----------------------------
    // VALIDATE MANUAL REELS (if present)
    // ----------------------------
    if ($request->has('reels')) {
        // basic validation using Validator
        $validator = Validator::make($request->all(), [
            'reels.*.item_id' => 'required|integer',
            'reels.*.reel_no' => 'required',
            'reels.*.size'    => 'required',
            'reels.*.unit'    => 'required',
            'reels.*.weight'  => 'required|numeric',
        ], [
            'reels.*.reel_no.required' => 'Reel number is required for each reel.',
        ]);

        if ($validator->fails()) {
            // merge validator errors into errors array
            foreach ($validator->errors()->all() as $ve) {
                $errors[] = "Manual Reels: " . $ve;
            }
        } else {
            // collect manual reels
            foreach ($request->reels as $idx => $r) {
                $manual_reels[] = [
                    'item_id' => (int) $r['item_id'],
                    'size'    => isset($r['size']) ? strtoupper($r['size']) : null,
                    'weight'  => $r['weight'],
                    'reel_no' => $r['reel_no'],
                    'bf'      => $r['bf'] ?? $request->bf,
                    'gsm'     => $r['gsm'] ?? $request->gsm,
                    'unit'    => $r['unit'],
                    'index'   => $idx,
                ];
            }
        }
    }

    // ----------------------------
    // VALIDATE SERIES (if present)
    // ----------------------------
    if ($request->has('series')) {
        $series = $request->input('series', []);
        $opening_amount = $request->input('opening_amount', []);
        $opening_qty = $request->input('opening_qty', []);
        $opening_balance_type = $request->input('opening_balance_type', []);

        foreach ($series as $key => $value) {
            $amt = isset($opening_amount[$key]) ? trim(str_replace(",", "", $opening_amount[$key])) : null;
            $qty = isset($opening_qty[$key]) ? trim(str_replace(",", "", $opening_qty[$key])) : null;
            $type = $opening_balance_type[$key] ?? null;

            if ($amt === "" || $qty === "" || $amt === null || $qty === null) {
                // skip empty series rows silently (same as your previous behaviour)
                continue;
            }

            // additional simple checks
            if (!is_numeric($amt) || !is_numeric($qty)) {
                $errors[] = "Series Row {$key}: Opening amount/qty must be numeric.";
            } else {
                $series_inputs[] = [
                    'series' => $value,
                    'amt'    => $amt,
                    'qty'    => $qty,
                    'type'   => $type,
                ];
            }
        }
    }

    // ----------------------------
    // CROSS-VALIDATIONS (duplicates across CSV + manual)
    // ----------------------------
    // collect all reel numbers from CSV + manual for duplicate detection
    $allReelNos = [];
    foreach ($csv_rows as $r) $allReelNos[] = $r['reel_no'];
    foreach ($manual_reels as $r) $allReelNos[] = $r['reel_no'];

    // check duplicates within uploaded data (CSV+manual)
    $dupsInUpload = array_unique(array_diff_assoc($allReelNos, array_unique($allReelNos)));
    if (!empty($dupsInUpload)) {
        foreach ($dupsInUpload as $d) {
            $errors[] = "Duplicate Reel No in uploaded data: '{$d}'";
        }
    }

    // check if any reel nos already exist in DB
    if (!empty($allReelNos)) {
        $existingInDb = ItemSizeStock::whereIn('reel_no', $allReelNos)
            ->where('company_id', $company_id)
            ->where('deckle_id', 0)
            ->pluck('reel_no')
            ->toArray();

        if (!empty($existingInDb)) {
            foreach ($existingInDb as $e) {
                $errors[] = "Reel No already exists in DB: '{$e}'";
            }
        }
    }

    // ----------------------------
    // FINAL DECISION: If any errors -> DON'T INSERT ANYTHING
    // ----------------------------
    if (!empty($errors)) {
        // return all errors and do not save anything
        // prefer flash arrays so blade can list them
        session()->flash('csv_summary', 'CSV/Upload validation failed. Fix the errors and retry.');
        session()->flash('csv_errors', $errors);

        return redirect()->back()->withInput();
    }

    // ----------------------------
    // NO ERRORS: proceed to insert everything inside a transaction
    // ----------------------------
    DB::beginTransaction();
    try {
        // create production item (main)
        $productionItem = ProductionItem::create([
            'item_id'    => $request->item_id,
            'bf'         => $request->bf,
            'gsm'        => $request->gsm,
            'speed'      => $request->speed,
            'status'     => intval($request->status),
            'company_id' => $company_id,
            'created_by' => $created_by,
            'created_at' => now(),
        ]);

        $item_id = $request->item_id;

        // INSERT CSV rows
        foreach ($csv_rows as $r) {
            ItemSizeStock::create([
                'item_id'    => $r['item_id'],
                'size'       => $r['size'],
                'weight'     => $r['weight'],
                'reel_no'    => $r['reel_no'],
                'bf'         => $r['bf'],
                'gsm'        => $r['gsm'],
                'unit'       => $r['unit'],
                'deckle_id'  => 0,
                'status'     => 1,
                'company_id' => $company_id,
                'created_by' => $created_by,
                'created_at' => now(),
            ]);
        }

        // INSERT MANUAL REELS
        foreach ($manual_reels as $r) {
            ItemSizeStock::create([
                'item_id'    => $r['item_id'],
                'size'       => $r['size'],
                'weight'     => $r['weight'],
                'reel_no'    => $r['reel_no'],
                'bf'         => $r['bf'],
                'gsm'        => $r['gsm'],
                'unit'       => $r['unit'],
                'deckle_id'  => 0,
                'status'     => 1,
                'company_id' => $company_id,
                'created_by' => $created_by,
                'created_at' => now(),
            ]);
        }

        // INSERT SERIES and ItemLedger
        if (!empty($series_inputs)) {
            // remove existing
            ItemBalanceBySeries::where('item_id', $item_id)->delete();
            ItemLedger::where('item_id', $item_id)->where('source', '-1')->delete();

            foreach ($series_inputs as $s) {
                $series_balance = new ItemBalanceBySeries();
                $series_balance->item_id = $item_id;
                $series_balance->series = $s['series'];
                $series_balance->opening_amount = $s['amt'];
                $series_balance->opening_quantity = $s['qty'];
                $series_balance->type = $s['type'];
                $series_balance->company_id = $company_id;
                $series_balance->created_at = now();
                $series_balance->updated_at = now();
                $series_balance->save();

                // ledger entry
                $ledger = new ItemLedger();
                $ledger->item_id = $item_id;
                if ($s['type'] == 'Debit') {
                    $ledger->in_weight = $s['qty'];
                } else if ($s['type'] == 'Credit') {
                    $ledger->out_weight = $s['qty'];
                }
                $ledger->series_no = $s['series'];
                $ledger->total_price = $s['amt'];
                $ledger->company_id = $company_id;
                $ledger->source = -1;
                $ledger->created_by = $created_by;
                $ledger->created_at = date('Y-m-d H:i:s');
                $default_fy = explode("-", Session::get('default_fy'));
                $txn_date = $default_fy[0] . "-04-01";
                $ledger->txn_date = date('Y-m-d', strtotime($txn_date));
                $ledger->save();
            }
        }

        DB::commit();

        // success flash: you can include counts if desired
        $successMsg = 'Item added successfully.';
        if (!empty($csv_rows)) {
            $successMsg .= ' CSV rows: ' . count($csv_rows) . ' inserted.';
        }
        if (!empty($manual_reels)) {
            $successMsg .= ' Manual reels: ' . count($manual_reels) . ' inserted.';
        }
        if (!empty($series_inputs)) {
            $successMsg .= ' Series: ' . count($series_inputs) . ' saved.';
        }

        return redirect()->route('production.set_item')->with('success', $successMsg);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('storeItem error: '.$e->getMessage());
        return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
    }
}





    public function deckleReelProcess(Request $request)
    {
        
        $deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
                                }])
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',2)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->get();
        $start_deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',3)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->first();
        $from_date = date('Y-m-d');
        $to_date = date('Y-m-d');
        $search_type = "";
        if(isset($request->from_date) && isset($request->to_date)){
            $from_date = date('Y-m-d',strtotime($request->from_date));
            $to_date = date('Y-m-d',strtotime($request->to_date));
            Session::put('complete_deckle_reel_from_date',$from_date);
            Session::put('complete_deckle_reel_to_date',$to_date);
            
        }else if(Session::get('complete_deckle_reel_from_date')!=''){
            $from_date = Session::get('complete_deckle_reel_from_date');
            $to_date = Session::get('complete_deckle_reel_to_date');
            
        }
        if(isset($request->search_type) && !empty($request->search_type)){
            $search_type = $request->search_type;
            Session::put('search_type',$request->search_type);
        }else{
            $search_type = Session::get('search_type');
        }
        $completed_deckles = DeckleProcess::with([
                    'quality' => function ($q) {
                        $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                                    $q->select('deckle_process_qualities.id','parent_id','item_id','bf','gsm','production_in_kg','manage_items.name');
                        $q->with([ // gets name from manage_items
                            'item_stock' => function ($q2) {
                                $q2->select('id', 'deckle_id', 'quality_row_id', 'reel_no', 'size', 'weight', 'status');
                            }
                        ]);
                    }
                ])

                ->where('deckle_processes.company_id', Session('user_company_id'))
                ->where('deckle_processes.status', 4)
                ->when($search_type === 'by_reel_cutting_date', function ($q) use ($from_date, $to_date) {
                        $q->whereDate('deckle_processes.reel_generated_at', '>=', $from_date)
                        ->whereDate('deckle_processes.reel_generated_at', '<=', $to_date);
                    })
                    ->when($search_type === 'by_pop_roll_date', function ($q) use ($from_date, $to_date) {
                        $q->whereDate('deckle_processes.end_time_stamp', '>=', $from_date)
                        ->whereDate('deckle_processes.end_time_stamp', '<=', $to_date);
                    })
                    ->when($search_type == '', function ($q) use ($from_date, $to_date) {
                        $q->whereDate('deckle_processes.reel_generated_at', '>=', $from_date)
                        ->whereDate('deckle_processes.reel_generated_at', '<=', $to_date);
                    })
                // ->whereDate('deckle_processes.reel_generated_at', '>=', $from_date)
                // ->whereDate('deckle_processes.reel_generated_at', '<=', $to_date)
                ->select(
                    'deckle_processes.id',
                    'deckle_no',
                    'start_time_stamp',
                    'end_time_stamp',
                    'reel_generated_at',
                    'stock_journal_status',
                    DB::raw('(select id from item_ledger where item_ledger.deckle_id = deckle_processes.id and item_ledger.source_id IS NOT NULL limit 1) as ledger_id')
                )
                ->get();
        if($start_deckles){
            $date = date('Y-m-d', strtotime($start_deckles->start_time_stamp));
        }else{
            $date = date('Y-m-d');
        }
        
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        if ($month >= 4) {
            $startDate = $year . '-04-01';
            $endDate   = ($year + 1) . '-03-31';
        } else {
            $startDate = ($year - 1) . '-04-01';
            $endDate   = $year . '-03-31';
        }
        $from_date = Carbon::parse($startDate)->startOfDay();
        $to_date   = Carbon::parse($endDate)->endOfDay();
        $deckle_arr = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->pluck('id');
        $reel_no = ItemSizeStock::where('company_id', Session::get('user_company_id'))
                                ->where('deckle_id', '!=', 0)
                                ->whereIn('deckle_id', $deckle_arr)
                                ->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        // echo "<pre>";
        // print_r($completed_deckles->toArray());die;
        $completed_poprolls = DeckleProcess::where('company_id', Session('user_company_id'))
        ->where('status', 2)
        ->select('id', 'deckle_no')
        ->get();
        $items = ProductionItem::join(
                'manage_items',
                'production_items.item_id',
                '=',
                'manage_items.id'
            )
            ->select(
                'production_items.id as production_item_id',
                'manage_items.name',
                'production_items.bf',
                'production_items.gsm',
                'production_items.speed',
                'manage_items.id'
            )
            ->where('production_items.company_id', Session::get('user_company_id'))
            ->where('production_items.status', 1)
            ->orderBy('manage_items.name')
            ->get();

            // echo "<pre>";
            // print_r($completed_deckles->toArray());die;


        return view("production/deckle_reel_process",["deckles"=>$deckles,"items"=>$items,"reel_no"=>$reel_no,"start_deckle"=>$start_deckles,"completed_deckles"=>$completed_deckles,"from_date"=>$from_date,"to_date"=>$to_date,"completed_poprolls" => $completed_poprolls]);
    }
    public function qualityByPoproll(Request $request)
    {
        $pop_roll_quality = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
                                }])->join('manage_items','deckle_processes.item_id','manage_items.id')
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.id',$request->pop_roll_id)
                                ->select('deckle_processes.id','deckle_no','bf','gsm','name','production_in_kg','start_time_stamp','end_time_stamp')
                                ->get();
        
        return json_encode(array("status"=>true,"pop_roll_quality"=>$pop_roll_quality));
        
    }
    public function storeDeckleItem(Request $request)
    {
        // Basic guard
        $company_id = Session::get('user_company_id');
        // Required: pop_roll_id should always be present (the current running pop roll)
        if (empty($request->pop_roll_id)) {
            return redirect()->back()->with('error', 'Current Pop Roll ID is missing.');
        }        
        // echo "<pre>";print_r($request->all());die;
        $currentPopRollId = $request->pop_roll_id;
        $newPopRollId = $request->new_pop_roll_id ?? null;

        // Use transaction so updates + inserts are atomic
        // DB::beginTransaction();
        // try {
            
            // 1) Mark the current pop roll as completed (status = 4)
            $now = now();
            if ($now->hour < 8) {
                $now = $now->subDay();
            }
            $reel_generated_at = $now->format('Y-m-d H:i:s');
            DeckleProcess::where('company_id', $company_id)
                ->where('id', $currentPopRollId)
                ->update(['status' =>4,'reel_generated_at'=>$reel_generated_at]);

            // 2) If a new pop roll selected in modal -> mark it as running (status = 3)
            if (!empty($newPopRollId)) {
                // Ensure the selected pop roll belongs to same company and is in a switchable state (status = 2)
                $candidate = DeckleProcess::where('company_id', $company_id)
                    ->where('id', $newPopRollId)
                    ->first();

                if ($candidate) {
                    $candidate->status = 3;
                    $candidate->save();
                }
            }
            // 3) Save reels/items (these belong to the pop_roll_id you submitted)
            if (!empty($request->pop_rolls) && is_array($request->pop_rolls)) {
                $quality_weight_arr = [];
                foreach ($request->pop_rolls as $key => $value) {
                    if (!empty($value['reels']) && is_array($value['reels'])) {
                        foreach ($value['reels'] as $k => $v) {
                            if (!empty($v['quality_id']) && !empty($v['reel_no'])) {
                                $deckleItem = new DeckleItem;
                                $deckleItem->deckle_id = $currentPopRollId;
                                $deckleItem->quality_id = $v['quality_id'] ?? null;
                                $deckleItem->quality_row_id = $v['quality_row_id'] ?? null;
                                $deckleItem->size = $v['size'] ?? null;
                                $deckleItem->reel_no = $v['reel_no'] ?? null;
                                $deckleItem->weight = $v['weight'] ?? null;
                                $deckleItem->bf = $v['bf'] ?? null;
                                $deckleItem->gsm = $v['gsm'] ?? null;
                                $deckleItem->unit = $v['unit'] ?? null;
                                $deckleItem->company_id = $company_id;
                                $deckleItem->created_by = Session::get('user_id');
                                $deckleItem->created_at = Carbon::now();
                                $deckleItem->save();

                                $stock = new ItemSizeStock;
                                $stock->item_id = $v['quality_id'] ?? null;
                                $stock->quality_row_id = $v['quality_row_id'] ?? null;
                                $stock->weight = $v['weight'] ?? null;
                                $stock->reel_no = $v['reel_no'] ?? null;
                                $stock->size = $v['size'] ?? null;
                                $stock->bf = $v['bf'] ?? null;
                                $stock->gsm = $v['gsm'] ?? null;
                                $stock->unit = $v['unit'] ?? null;
                                $stock->deckle_id = $currentPopRollId;
                                $stock->company_id = $company_id;
                                $stock->created_by = Session::get('user_id');
                                $stock->created_at = $reel_generated_at;
                                $stock->save();
                                //Add Weight For Item Ledger
                                if (!empty($v['quality_id']) && !empty($v['reel_no'])) {
                                    if(isset($quality_weight_arr[$v['quality_id']])){
                                        $quality_weight_arr[$v['quality_id']] =  $quality_weight_arr[$v['quality_id']] + $v['weight'];
                                    }else{
                                        $quality_weight_arr[$v['quality_id']] =  $v['weight'];
                                    }
                                }
                            }
                        }
                    }
                }
                //Store Item In Item Ledger And Average Detail tables
                if(count($quality_weight_arr)>0){
                    $companyData = Companies::where('id', Session::get('user_company_id'))->first();
                    if($companyData->gst_config_type == "single_gst"){
                        $GstSettings = DB::table('gst_settings')
                                        ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                                        ->get();
                        $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$GstSettings[0]->id])
                                        ->get();
                        if(count($branch)>0){
                            $GstSettings = $GstSettings->merge($branch);
                        }
                        
                    }else if($companyData->gst_config_type == "multiple_gst"){
                        $GstSettings = DB::table('gst_settings_multiple')
                                        ->select('id','gst_no','mat_center','series','invoice_start_from')
                                        ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                                        ->get();
                        foreach ($GstSettings as $key => $value) {
                            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                                        ->get();
                            if(count($branch)>0){
                            $GstSettings = $GstSettings->merge($branch);
                            }
                        }         
                    }
                    $series = $GstSettings[0]->series;
                    $deckle = DeckleProcess::find($currentPopRollId);
                    foreach ($quality_weight_arr as $key => $value) {
                        //ADD IN Stock
                        $item_ledger = new ItemLedger();
                        $item_ledger->item_id = $key;
                        $item_ledger->series_no = $series;
                        $item_ledger->in_weight = $value;
                        $item_ledger->txn_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                        $item_ledger->price = 1;
                        $item_ledger->total_price = $value;
                        $item_ledger->company_id = Session::get('user_company_id');
                        $item_ledger->source = 7;
                        $item_ledger->deckle_id = $currentPopRollId;
                        $item_ledger->created_by = Session::get('user_id');
                        $item_ledger->created_at = date('d-m-Y H:i:s');
                        $item_ledger->save();
                         //Add Data In Average Details table
                        $average_detail = new ItemAverageDetail;
                        $average_detail->series_no = $series;
                        $average_detail->entry_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                        $average_detail->item_id = $key;
                        $average_detail->type = 'PRODUCTION GENERATE';
                        $average_detail->deckle_id = $currentPopRollId;
                        $average_detail->production_in_weight = $value;
                        $average_detail->production_in_amount = $value;
                        $average_detail->company_id = Session::get('user_company_id');
                        $average_detail->created_at = Carbon::now();
                        $average_detail->save();
                        CommonHelper::RewriteItemAverageByItem(date('Y-m-d',strtotime($deckle->end_time_stamp)),$key,$series);
                    }
                }
                
            }
            //DB::commit();

            // If modal was used (we switched rolls) we give appropriate message
            if (!empty($newPopRollId)) {
                return redirect()->back()->with('success', 'Pop roll switched and items saved successfully.');
            }

            return redirect()->back()->with('success', 'Item added and pop roll completed successfully.');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::error('storeDeckleItem error: '.$e->getMessage().' -- '.$e->getTraceAsString());
        //     return redirect()->back()->with('error', 'Something went wrong while saving. Please try again.');
        // }
    }
    public function startDeckle(Request $request)
    {
       
        $pop_roll_quality = DeckleProcess::where('id',$request->pop_rolls)->update(['status'=>3]);
        
        return redirect()->back()->with('success', 'Pop roll start successfully.');
        
    }
    public function manageStock(Request $request){
        // Fetch stocks with item details
        $stocks = ItemSizeStock::join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
            ->select(
                DB::raw('COUNT(item_size_stocks.id) as total_stock'),
                DB::raw('SUM(item_size_stocks.weight) as total_stock_kg'),
                'manage_items.name',
                'item_size_stocks.item_id as new_item_id'
            )
            ->where('item_size_stocks.company_id', Session::get('user_company_id'))
            ->where('item_size_stocks.status', 1)
            ->groupBy('item_size_stocks.item_id', 'manage_items.name')
            ->get();

        
        return view("production/manage_stock", ["stocks" => $stocks]);
    }
    public function getReelDetails($item_id){
        $reels = DB::table('item_size_stocks') // replace with your actual table name
            ->select('size', 'reel_no', 'weight','deckle_id','id')
            ->where('item_id', $item_id)
            ->where('status', '1')
            ->where('company_id',Session::get('user_company_id'))
            ->get();

        if ($reels->isEmpty()) {
            return response()->json(['error' => 'No data found'], 404);
        }
       
        $grouped = $reels->groupBy('size')->map(function ($group){
            return [
                'size' => $group->first()->size,
                'count' => $group->count(),
                'reels' => $group->pluck('reel_no')->toArray(),
                'weights' => $group->pluck('weight')->toArray(),
                'deckle_id' => $group->pluck('deckle_id')->toArray(),
                'id' => $group->pluck('id')->toArray(),
            ];
        })->values();
        return response()->json($grouped);
    }
    public function cancelPopRollReel(Request $request){
        DeckleItem::where('deckle_id',$request->pop_roll_id)->update(['status'=>0]);
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)->delete();
        DeckleProcess::where('id',$request->pop_roll_id)->update(['status'=>2,'reel_generated_at'=>null]);
        ItemLedger::where('deckle_id',$request->pop_roll_id)->delete();
        ItemAverageDetail::where('deckle_id',$request->pop_roll_id)->delete();
        return redirect()->back()->with('success', 'Pop Roll Cancel Successfully.');
    }
    public function editPopRollReel(Request $request){
        Session::put('redirect_url',$request->from);
        $start_deckle = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->find($request->id);
        $item_reel = DeckleItem::where('deckle_id',$request->id)
                                ->where('status',1)
                                ->get(); 
        foreach ($item_reel as $reel) {
            $reel->is_used = ItemSizeStock::where([
                'deckle_id' => $reel->deckle_id,
                'reel_no'   => $reel->reel_no,
                'size'      => $reel->size,
                'item_id'   => $reel->quality_id,
                'status'    => 0
            ])->exists();
        }
        $date = date('Y-m-d', strtotime($start_deckle->start_time_stamp));
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        if ($month >= 4) {
            $startDate = $year . '-04-01';
            $endDate   = ($year + 1) . '-03-31';
        } else {
            $startDate = ($year - 1) . '-04-01';
            $endDate   = $year . '-03-31';
        }
        $from_date = Carbon::parse($startDate)->startOfDay();
        $to_date   = Carbon::parse($endDate)->endOfDay();
        $deckle_arr = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->pluck('id');
        $reel_no = ItemSizeStock::where('company_id',Session::get('user_company_id'))
                                ->where('deckle_id',"!=",0)
                                ->whereIn('deckle_id',$deckle_arr)
                                ->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        return view("production/edit_pop_roll_reel", ["start_deckle" => $start_deckle,"reel_no"=>$reel_no,"item_reel"=>$item_reel]);
    }
    public function updatePopRollReel(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        // die;
        $item_id_arr = DeckleItem::where('deckle_items.deckle_id',$request->pop_roll_id)
            ->join('item_size_stocks', 'item_size_stocks.reel_no', '=', 'deckle_items.reel_no')
            ->where('deckle_items.status',1)
            ->where('item_size_stocks.status',1)
            ->where('item_size_stocks.deckle_id',$request->pop_roll_id)
            ->pluck('deckle_items.id')
            ->map(fn($id) => (int)$id)
            ->toArray();
        // print_r($item_id_arr);die;
        
        
        $redirect_url = Session::get('redirect_url');
        
        $sizeStock = ItemSizeStock::where('deckle_id',$request->pop_roll_id)->first();
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)
                    ->where('status',1)
                    ->delete();
        $quality_weight_arr = [];
        foreach ($request->pop_rolls as $key => $value) {
            foreach ($value['reels'] as $k => $v) {
                if (isset($v['deleted']) && $v['deleted'] == 1) {
                    continue;
                }
                
                if(!empty($v['quality_id'])){
                    //Check Reel Sold Status
                    $size_sale_status = ItemSizeStock::where('status',0)
                            ->where('reel_no',$v['reel_no'])
                            ->where('item_id',$v['quality_id'])
                            ->where('company_id',$v['quality_id'])
                            ->where('deckle_id',$request->pop_roll_id)
                            ->first();
                    if(!empty($v['row_id'])){
                        // Update existing record
                        $deckleItem = DeckleItem::find($v['row_id']);
                        if ($deckleItem) {
                            if(isset($v['sold']) && $v['sold']!=1 && !$size_sale_status){
                                $deckleItem->quality_id = $v['quality_id'];
                                $deckleItem->quality_row_id = $v['quality_row_id'];
                                $deckleItem->size = $v['size'];
                                $deckleItem->reel_no = $v['reel_no'];
                                $deckleItem->weight = $v['weight'];
                                $deckleItem->bf = $v['bf'];
                                $deckleItem->gsm = $v['gsm'];
                                $deckleItem->unit = $v['unit'];
                                $deckleItem->updated_at = Carbon::now();
                                $deckleItem->save();
                            }
                            
                        }
                        // Remove from the array to keep track of processed IDs
                        if (($key = array_search($v['row_id'], $item_id_arr)) !== false) {
                            unset($item_id_arr[$key]);
                        }
                    } else {
                        // Create new record
                        $deckle = new DeckleItem;
                        $deckle->deckle_id = $request->pop_roll_id;
                        $deckle->quality_id = $v['quality_id'];
                        $deckle->quality_row_id = $v['quality_row_id'];
                        $deckle->size = $v['size'];
                        $deckle->reel_no = $v['reel_no'];
                        $deckle->weight = $v['weight'];
                        $deckle->bf = $v['bf'];
                        $deckle->gsm = $v['gsm'];
                        $deckle->unit = $v['unit'];
                        $deckle->company_id =  Session::get('user_company_id');
                        $deckle->created_by = Session::get('user_id');
                        $deckle->created_at = Carbon::now();
                        $deckle->save();
                    }
                    if($v['sold']!=1 && !$size_sale_status){
                        $stock = new ItemSizeStock;
                        $stock->item_id = $v['quality_id'];
                        $stock->quality_row_id = $v['quality_row_id'];
                        $stock->weight = $v['weight'];
                        $stock->reel_no = $v['reel_no'];
                        $stock->size = $v['size'];
                        $stock->bf = $v['bf'];
                        $stock->gsm = $v['gsm'];
                        $stock->unit = $v['unit'];
                        $stock->deckle_id = $request->pop_roll_id;
                        $stock->company_id = Session::get('user_company_id');
                        $stock->created_by = Session::get('user_id');
                        $stock->created_at = $sizeStock->created_at;
                        $stock->save();
                    }                   

                    //Add Weight For Item Ledger
                    if (!empty($v['quality_id']) && !empty($v['reel_no'])) {
                        if(isset($quality_weight_arr[$v['quality_id']])){
                            $quality_weight_arr[$v['quality_id']] =  $quality_weight_arr[$v['quality_id']] + $v['weight'];
                        }else{
                            $quality_weight_arr[$v['quality_id']] =  $v['weight'];
                        }
                    }
                }else{
                    if(isset($v['sold']) && $v['sold']==1){
                        $ditem = DeckleItem::find($v['row_id']);
                        if($ditem){
                            if(isset($quality_weight_arr[$ditem->quality_id])){
                                $quality_weight_arr[$ditem->quality_id] =  $quality_weight_arr[$ditem->quality_id] + $v['weight'];
                            }else{
                                $quality_weight_arr[$ditem->quality_id] =  $v['weight'];
                            }
                        }
                        if (($key = array_search($v['row_id'], $item_id_arr)) !== false) {
                            unset($item_id_arr[$key]);
                        }
                    }
                }
            }
        }
        //Store Item In Item Ledger And Average Detail tables
        ItemLedger::where('deckle_id',$request->pop_roll_id)->delete();
        ItemAverageDetail::where('deckle_id',$request->pop_roll_id)->delete();
        if(count($quality_weight_arr)>0){
            $companyData = Companies::where('id', Session::get('user_company_id'))->first();
            if($companyData->gst_config_type == "single_gst"){
                $GstSettings = DB::table('gst_settings')
                                ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                                ->get();
                $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$GstSettings[0]->id])
                                ->get();
                if(count($branch)>0){
                    $GstSettings = $GstSettings->merge($branch);
                }
                
            }else if($companyData->gst_config_type == "multiple_gst"){
                $GstSettings = DB::table('gst_settings_multiple')
                                ->select('id','gst_no','mat_center','series','invoice_start_from')
                                ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                                ->get();
                foreach ($GstSettings as $key => $value) {
                    $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                                ->get();
                    if(count($branch)>0){
                    $GstSettings = $GstSettings->merge($branch);
                    }
                }         
            }
            $series = $GstSettings[0]->series;
            $deckle = DeckleProcess::find($request->pop_roll_id);
            foreach ($quality_weight_arr as $key => $value) {
                //ADD IN Stock
                $item_ledger = new ItemLedger();
                $item_ledger->item_id = $key;
                $item_ledger->series_no = $series;
                $item_ledger->in_weight = $value;
                $item_ledger->txn_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                $item_ledger->price = 1;
                $item_ledger->total_price = $value;
                $item_ledger->company_id = Session::get('user_company_id');
                $item_ledger->source = 7;
                $item_ledger->deckle_id = $request->pop_roll_id;
                $item_ledger->created_by = Session::get('user_id');
                $item_ledger->created_at = date('d-m-Y H:i:s');
                $item_ledger->save();
                    //Add Data In Average Details table
                $average_detail = new ItemAverageDetail;
                $average_detail->series_no = $series;
                $average_detail->entry_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                $average_detail->item_id = $key;
                $average_detail->type = 'PRODUCTION GENERATE';
                $average_detail->deckle_id = $request->pop_roll_id;
                $average_detail->production_in_weight = $value;
                $average_detail->production_in_amount = $value;
                $average_detail->company_id = Session::get('user_company_id');
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
                CommonHelper::RewriteItemAverageByItem(date('Y-m-d',strtotime($deckle->end_time_stamp)),$key,$series);
            }
        }
        DeckleItem::whereIn('id',$item_id_arr)->update(['status'=>0]);
        if ($request->deleted_row_ids) {
            $ids = explode(',', $request->deleted_row_ids);
            DeckleItem::whereIn('id', $ids)->update(['status' => 0]);
        }

        if($redirect_url!=""){
            Session::put('redirect_url','');
            return redirect($redirect_url);
        }
        return redirect()->route('deckle-process.manage-reel')->with('success', 'Updated Successfully.');
        
    }
    public function edit($id)
    {
        $company_id = Session::get('user_company_id');

        $deckle = DeckleProcess::with(['quality' => function($q){
            $q->join('manage_items','deckle_process_qualities.item_id','=','manage_items.id')
            ->select('deckle_process_qualities.*','manage_items.name');
        }])->where('company_id', $company_id)
        ->findOrFail($id);

        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                    ->select('production_items.id','name','bf','gsm','speed','manage_items.id as item_id')
                                    ->where('production_items.company_id',Session::get('user_company_id'))
                                    ->where('production_items.status','1')
                                    ->orderBy('name')
                                    ->get();

        return view('production.edit', compact('deckle', 'items'));
    }
    public function update(Request $request, $id)
    {
        
        $company_id = Session::get('user_company_id');

        // 🧩 1️⃣ Delete all previous qualities for this deckle
        DeckleProcessQuality::where('parent_id', $id)
            ->where('company_id', $company_id)
            ->delete();

        // 🧩 2️⃣ Combine all qualities (existing + new) into one array
        $allQualities = [];

        if ($request->has('qualities')) {
            foreach ($request->qualities as $qData) {
                $allQualities[] = $qData;
            }
        }

        if ($request->has('new_qualities')) {
            foreach ($request->new_qualities as $newData) {
                $allQualities[] = $newData;
            }
        }
        
        $deckle = DeckleProcess::find($id);
        $start_time_stamp_previous = $deckle->start_time_stamp;
        $start_time_stamp_previous = date('Y-m-d',strtotime($start_time_stamp_previous));
        $timestamp = strtotime($start_time_stamp_previous);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        if ($month >= 4) {
            $startDate = $year . '-04-01';
            $endDate   = ($year + 1) . '-03-31';
        } else {
            $startDate = ($year - 1) . '-04-01';
            $endDate   = $year . '-03-31';
        }
        $date = Carbon::parse($request->start_time);
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        if ($date->between($start, $end)) {
            $deckle_no = $deckle->deckle_no;
        } else {
            $deckle_no = $this->generateDeckleNo(date('Y-m-d', strtotime($request->start_time)));
        }
        // $end_time_stamp_previous = $deckle->end_time_stamp;
        // $end_time_stamp_previous = date('H:i:s', strtotime($end_time_stamp_previous));
        // $end_time_stamp = Carbon::parse($request->end_time." ".$end_time_stamp_previous);
        $end_time_stamp = date('Y-m-d H:i:s', strtotime($request->end_time));
        
        DeckleProcess::where('id', $id)
            ->where('company_id', $company_id)
            ->update([
                'start_time_stamp' => date('Y-m-d H:i:s', strtotime($request->start_time)),
                'end_time_stamp' => $end_time_stamp,
                'updated_at' => Carbon::now(),
                'deckle_no' => $deckle_no ?? null
            ]);
        // 🧩 3️⃣ Insert each quality again
        foreach ($allQualities as $data) {

            // Skip if item not selected
            if (empty($data['item_id'])) {
                continue;
            }

            // ✅ Try to get ProductionItem record (to find manage_items.id)
            $productionItem = ProductionItem::find($data['item_id']);

            // ✅ Convert to manage_items.id (if ProductionItem exists)
            $manage_item_id = $productionItem ? $productionItem->item_id : $data['item_id'];

            // ✅ Auto-fill BF & GSM if not manually provided
            $bf = $data['bf'] ?? ($productionItem->bf ?? null);
            $gsm = $data['gsm'] ?? ($productionItem->gsm ?? null);

            // 🧩 Create new DeckleProcessQuality entry
            $deckle = new DeckleProcessQuality();
            $deckle->parent_id = $id;
            $deckle->company_id = $company_id;
            $deckle->item_id = $manage_item_id;
            $deckle->bf = $bf;
            $deckle->gsm = $gsm;
            $deckle->speed = $data['speed'] ?? null;
            $deckle->production_in_kg = $data['production_in_kg'] ?? null;
            $deckle->deckle_no = $deckle_no ?? null;
            $deckle->start_time_stamp = date('Y-m-d H:i:s', strtotime($request->start_time));
            $deckle->end_time_stamp = date('Y-m-d H:i:s', strtotime($request->end_time));
            $deckle->started_by = Session::get('user_id');
            $deckle->created_at = Carbon::now();

            $deckle->save();
        }

        return redirect()->route('deckle-process.index')->with('success', 'Deckle updated successfully!');
    }
    public function getItemDetails($item_id)
    {
        $company_id = Session::get('user_company_id');

        // Fetch BF and GSM for the selected item
        $item = DB::table('production_items')
            ->where('company_id', $company_id)
            ->where('id', $item_id)
            ->select('bf', 'gsm')
            ->first();

        if ($item) {
            return response()->json([
                'bf' => $item->bf,
                'gsm' => $item->gsm
            ]);
        } else {
            return response()->json(['bf' => '', 'gsm' => '']);
        }
    }
    public function createNewReel()
    {
        $company_id = Session::get('user_company_id');

        // Fetch items which exist in production_items
        $items = DB::table('production_items')
            ->join('manage_items', 'production_items.item_id', '=', 'manage_items.id')
            ->where('production_items.company_id', $company_id)
            ->select('manage_items.id as item_id', 'manage_items.name', 'production_items.bf', 'production_items.gsm',DB::raw('(SELECT SUM(opening_quantity) 
                  FROM item_balance_by_series 
                  WHERE item_balance_by_series.item_id = manage_items.id 
                  AND item_balance_by_series.company_id = '.$company_id.'
                ) as opening_quantity'))
            ->get();
        
        return view('production.add_stock', compact('items'));
    }
    // ✅ Store Reel in item_size_stocks
    public function storeNewReel(Request $request)
    {
        $request->validate([
            'reels.*.item_id' => 'required',
            'reels.*.reel_no' => 'required|distinct|unique:item_size_stocks,reel_no',
            'reels.*.size'    => 'required',
            'reels.*.unit'    => 'required',
            'reels.*.weight'  => 'required|numeric',
        ]);
        
        foreach ($request->reels as $reel) {
            $stock = new ItemSizeStock();
            $stock->item_id    = $reel['item_id'];
            $stock->size       = isset($reel['size']) ? strtoupper($reel['size']) : null;
            $stock->weight     = $reel['weight'];
            $stock->reel_no    = $reel['reel_no'];
            $stock->bf         = $reel['bf'];
            $stock->gsm        = $reel['gsm'];
            $stock->unit       = $reel['unit'];
            $stock->deckle_id  = 0;
            $stock->status     = 1;
            $stock->company_id = Session::get('user_company_id');
            $stock->created_by = Session::get('user_id');
            $stock->save();
        }

        return redirect()->back()->with('success', 'Reels added successfully!');
    }
    // ✅ Ajax check if reel_no exists
    public function checkReel(Request $request)
    {
        $exists = ItemSizeStock::where('reel_no', $request->reel_no)
        ->where('deckle_id',0)
        ->exists();
        return response()->json(['exists' => $exists]);
    }
    public function indexManual()
    {
        $stocks = ItemSizeStock::where('deckle_id', 0)
            ->where('company_id', Session::get('user_company_id'))
            ->with('item')
            ->orderBy('item_id')
            ->orderBy('reel_no', 'asc')
            ->get()
            ->groupBy('item.name'); // group by item name
    
        // Calculate totals
        $summary = [];
        $grand_total_reels = 0;
        $grand_total_weight = 0;
    
        foreach ($stocks as $itemName => $group) {
            $total_reels = $group->count();
            $total_weight = $group->sum('weight'); // assuming 'weight' is in kg
    
            $summary[$itemName] = [
                'total_reels' => $total_reels,
                'total_weight' => $total_weight,
            ];
    
            $grand_total_reels += $total_reels;
            $grand_total_weight += $total_weight;
        }
    
        return view('production.view_manual_reels', compact('stocks', 'summary', 'grand_total_reels', 'grand_total_weight'));
    }
    /**
     * Show edit form for a stock entry (only if status = 1).
     */
    public function editManual(Request $request, $id)
    {
        Session::put('redirect_url',$request->from);
        $stock = ItemSizeStock::findOrFail($id);

        if ($stock->status == 0) {
            return redirect()->route('item-size-stocks.index')
                ->with('error', 'Cannot edit sold reel.');
        }

        $items = ManageItems::orderBy('name')->get();

        return view('production.edit_manual_reels', compact('stock', 'items'));
    }
    /**
     * Update the stock record.
     */
    public function updateManual(Request $request, $id)
    {
        $redirect_url = Session::get('redirect_url');
        $stock = ItemSizeStock::findOrFail($id);

        if ($stock->status == 0) {
            return redirect()->route('item-size-stocks.index')
                ->with('error', 'Cannot update sold reel.');
        }

        $request->validate([
            'item_id' => 'required|integer',
            'size' => 'required|string',
            'weight' => 'required|numeric',
            'bf' => 'required|numeric',
            'gsm' => 'required|numeric',
            'unit' => 'required|string'
        ]);

       
            $stock->item_id = $request->item_id;
            $stock->size = strtoupper($request->size ?? '');
            $stock->weight = $request->weight;
           $stock->bf = $request->bf;
            $stock->gsm = $request->gsm;
            $stock->unit = $request->unit;
            $stock->updated_at = now();
   

              $stock->save();
        if($redirect_url!=""){
            Session::put('redirect_url','');
            return redirect($redirect_url);
        }
        return redirect()->route('item-size-stocks.index')
            ->with('success', 'Stock updated successfully!');
    }
    public function validateStockWeight(Request $request)
    {
        $weight = ItemBalanceBySeries::where('item_id', $request->item_id)
            ->where('company_id', Session::get('user_company_id'))
            ->where('status','1')
            ->sum('opening_quantity');
            if($weight==""){
                $weight = 0;
            }
            $status = true;
            if($request->item_weight!=$weight){
                $status = true;
            }
        return response()->json(['status' => $status]);
    }
    public function reelImportView()
    {
        return view('production.add_reel_csv_import');
    }
    public function reelImportProcess(Request $request)
    {
        ini_set('max_execution_time', 600);

        // 1️⃣ Validate file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        $error_arr = [];
        $duplicate_arr = [];
        $success_count = 0;
        $failed_count = 0;

        $company_id = Session::get('user_company_id');
        $user_id = Session::get('user_id');
        $now = Carbon::now();

        // 2️⃣ Open CSV
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ","); // Skip header
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $total_row = count($fp) - 1;
            $index = 1;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $data = array_map('trim', $data);
                $index++;

                // Skip empty rows
                if (empty(array_filter($data))) continue;

                // CSV Format: item_name,size,weight,reel_no,bf,gsm,unit
                $item_name = $data[0] ?? null;
                $size = isset($data[1]) ? strtoupper($data[1]) : null;
                $weight    = $data[2] ?? null;
                $reel_no   = $data[3] ?? null;
                $bf        = $data[4] ?? null;
                $gsm       = $data[5] ?? null;
                $unit      = $data[6] ?? null;

                // 🧾 Validate required fields
                if (!$item_name || !$size || !$weight || !$reel_no || !$unit) {
                    $error_arr[] = "Missing required fields at Row $index (Item: $item_name)";
                    $failed_count++;
                    continue;
                }

                // dd("Row $index", $item_name, strlen($item_name), $company_id);


                // 3️⃣ Find item_id from manage_items
                $item = DB::table('manage_items')
                    ->where('name', $item_name)
                    ->where('company_id', $company_id)
                    ->where('delete', '0')
                    ->select('id')
                    ->first();

                

                if (!$item) {
                    $error_arr[] = "Item '$item_name' not found in Manage Items table (Row $index)";
                    $failed_count++;
                    continue;
                }

                $exist = DB::table('production_items')
                                ->where('company_id', $company_id)
                                ->where('item_id', $item->id)
                                ->first();

                    if (!$exist) {
                    $error_arr[] = "Item '$item_name' not found in Production Items table (Row $index)";
                    $failed_count++;
                    continue;
                }

                $item_id = $item->id;

                // 4️⃣ Check for duplicate reel_no
                $exists = ItemSizeStock::where('reel_no', $reel_no)
                    ->where('company_id', $company_id)
                    ->where('deckle_id',0)
                    ->exists();

                if ($exists) {
                    $duplicate_arr[] = "Reel No. '$reel_no' already exists (Row $index)";
                    $failed_count++;
                    continue;
                }

                // 5️⃣ Insert record
                try {
                    ItemSizeStock::create([
                        'item_id'    => $item_id,
                        'size'       => $size,
                        'weight'     => $weight,
                        'reel_no'    => $reel_no,
                        'bf'         => $bf,
                        'gsm'        => $gsm,
                        'unit'       => $unit,
                        'deckle_id'  => 0,
                        'status'     => 1,
                        'company_id' => $company_id,
                        'created_by' => $user_id,
                        'created_at' => $now,
                    ]);
                    $success_count++;
                } catch (\Exception $e) {
                    $error_arr[] = "Error at Row $index (Reel: $reel_no): " . $e->getMessage();
                    $failed_count++;
                }
            }

            fclose($handle);
        }

        // 6️⃣ Prepare Summary
        $total = $success_count + $failed_count;
        $summary = "Total Rows: $total | Successful: $success_count | Failed: $failed_count";

        if (count($duplicate_arr) > 0) {
            $summary .= " | Duplicates: " . count($duplicate_arr);
        }

        // 7️⃣ Return with result
        return redirect()->back()
            ->with('success', $summary)
            ->withErrors(array_merge($error_arr, $duplicate_arr));
    }
    public function quality()
    {
        return $this->hasMany(DeckleProcessQuality::class, 'parent_id', 'id')
                    ->with('item'); // eager load item for name
    }
    public function revertPopRoll(Request $request)
    {
        DeckleProcess::where('id', $request->id)->update(['status' => 2]);
        return response()->json(['success' => true]);
    }    
    public function exportReelCSV(Request $request)
{
    $from_date = $request->from_date;
    $to_date   = $request->to_date;

    // Normal full day concept (00:00:00 to 23:59:59)
    $from = $from_date . ' 00:00:00';
    $to   = $to_date . ' 23:59:59';
    
     // Convert into full datetime (8 AM to next day 7:59 AM logic)
   // $from = $from_date . ' 08:00:00';
    //$to   = date('Y-m-d 07:59:59', strtotime($to_date . ' +1 day'));

    $reels = ItemSizeStock::where('item_size_stocks.company_id', Session::get('user_company_id'))
        ->join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
        ->join('deckle_processes', 'item_size_stocks.deckle_id', '=', 'deckle_processes.id')
        ->select(
            'manage_items.name as item_name',
            'item_size_stocks.reel_no',
            'item_size_stocks.size',
            'item_size_stocks.weight',
            'deckle_processes.end_time_stamp as deckle_date'
        )
        ->whereBetween('deckle_processes.end_time_stamp', [$from, $to]) // ✅ filter on deckle date
        ->whereNotNull('item_size_stocks.deckle_id')
        ->where('item_size_stocks.deckle_id', '!=', 0)
        ->orderBy('deckle_processes.end_time_stamp')
        ->orderBy('manage_items.name')
        ->orderBy('item_size_stocks.reel_no')
        ->get();

    // Group by Deckle Date
    $grouped = $reels->groupBy(function ($r) {
        return \Carbon\Carbon::parse($r->deckle_date)->format('d-m-Y');
    })->map(function ($items) {
        return $items->groupBy('item_name');
    });

    $filename = "reels_export_" . date('YmdHis') . ".csv";

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate",
        "Expires"             => "0"
    ];

    $callback = function () use ($grouped) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            "Date",
            "Item Name",
            "Size",
            "Reel No",
            "Weight",
            "Weight",
            "Number"
        ]);

        foreach ($grouped as $date => $items) {
            $printedDate = false;

            foreach ($items as $itemName => $rows) {
                $printedItem = false;

                foreach ($rows as $row) {
                    fputcsv($file, [
                        $printedDate ? "" : $date,
                        $printedItem ? "" : $itemName,
                        $row->size,
                        $row->reel_no,
                        $row->weight,
                        $row->weight,
                        1
                    ]);

                    $printedDate = true;
                    $printedItem = true;
                }
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}   
    public function readCsvWeight(Request $request)
    {
        if (!$request->hasFile('csv_file')) {
            return response()->json(['success' => false, 'message' => 'CSV file missing']);
        }

        $selectedItem = trim($request->selected_item);  // Selected item name
        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        $total = 0;

        foreach ($rows as $i => $row) {

            if ($i == 0) continue; // skip header

            $csvItemName = trim($row[0] ?? ''); // First column = item name

            // ❌ If CSV item name does not match selected item
            if (strcasecmp($csvItemName, $selectedItem) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => "CSV contains item name '$csvItemName' which does not match selected item '$selectedItem'. Please correct your CSV."
                ]);
            }

            $weight = floatval($row[2] ?? 0); // weight column
            $total += $weight;
        }

        return response()->json([
            'success' => true,
            'total_weight' => $total
        ]);
    }
    public function updateDeckleEndTime(Request $request)
    {
        // echo "<pre>";
        // print_r($request->all());
        // die;
        //Gate::authorize('action-module',241);
        DB::beginTransaction();
        try {
            /* -----------------------------
            | 0. Fetch Deckle
            ----------------------------- */
            $deckle = DeckleProcess::find($request->deckle_id);
            if (!$deckle) {
                return response()->json([
                    'success' => false,
                    'message' => "Deckle not found."
                ]);
            }
            /* -----------------------------
            | 1. Update Deckle End Time
            ----------------------------- */            
            $end_time_stamp_previous = $deckle->end_time_stamp;
            $end_time_stamp_previous = date('H:i:s', strtotime($end_time_stamp_previous));
            $end_time_stamp = Carbon::parse($request->end_time_stamp." ".$end_time_stamp_previous);
            $end_time_stamp = $end_time_stamp->format('Y-m-d H:i:s');
            $deckle->end_time_stamp = $end_time_stamp;
            $deckle->updated_at = now();
            if (!$deckle->save()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Failed to update deckle end time."
                ]);
            }
            /* -----------------------------
            | 2. Update Existing Qualities
            |    (ONLY status = 1)
            ----------------------------- */
            
            if (!empty($request->quality_data)){
                foreach ($request->quality_data as $row) {
                    $existingQuality = DB::table('deckle_process_qualities')
                        ->where('id', $row['quality_row_id'])
                        ->where('parent_id', $deckle->id)
                        ->first();
                    if (!$existingQuality) {
                        continue;
                    }
                    $isChanged =
                        $existingQuality->item_id != $row['item_id'] ||
                        $existingQuality->bf != $row['bf'] ||
                        $existingQuality->gsm != $row['gsm'] ||
                        $existingQuality->speed != $row['speed'] ||
                        $existingQuality->production_in_kg != $row['production_in_kg'];
                    if ($isChanged) {
                        $soldReelExists = ItemSizeStock::where('quality_row_id', $row['quality_row_id'])
                            ->where('deckle_id', $deckle->id)
                            ->where('company_id', Session::get('user_company_id'))
                            ->where('status', 0)
                            ->exists();
                        if ($soldReelExists) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Cannot update quality. Some reels of this quality are already sold.'
                            ]);
                        }
                    }
                    $manage_item_id = $row['item_id'];
                    DB::table('deckle_process_qualities')
                        ->where('id', $row['quality_row_id'])
                        ->where('parent_id', $deckle->id)
                        ->update([
                            'item_id'          => $manage_item_id,
                            'bf'               => $row['bf'],
                            'gsm'              => $row['gsm'],
                            'speed'            => $row['speed'],
                            'production_in_kg' => $row['production_in_kg'],
                            'updated_at'       => now()
                        ]);
                    $deckle_items = DeckleItem::where('deckle_id',$deckle->id)
                                ->where('quality_row_id',$row['quality_row_id'])
                                ->get();
                    foreach($deckle_items as $deckle_item){
                        $parts = explode('X', $deckle_item->size);
                        $parts[1] = $row['gsm'];
                        $updatedSize = implode('X', $parts);
                        DeckleItem::where('id',$deckle_item->id)
                                    ->update([
                                        "quality_id"=>$manage_item_id,
                                        "bf"=>$row['bf'],
                                        "gsm"=>$row['gsm'],
                                        "size"=>$updatedSize,
                                        'updated_at'=> now()
                                    ]);
                    }
                    $item_size_stocks = ItemSizeStock::where('deckle_id',$deckle->id)
                                ->where('quality_row_id',$row['quality_row_id'])
                                ->where('status',1)
                                ->get();
                    foreach($item_size_stocks as $item_size_stock){
                        $parts = explode('X', $item_size_stock->size);
                        $parts[1] = $row['gsm'];
                        $updatedSize = implode('X', $parts);
                        ItemSizeStock::where('id',$item_size_stock->id)
                                    ->update([
                                        "item_id"=>$manage_item_id,
                                        "bf"=>$row['bf'],
                                        "gsm"=>$row['gsm'],
                                        "size"=>$updatedSize,
                                        'updated_at'=> now()
                                    ]);
                    }
                    
                }
            }
            if (!empty($request->new_quality)) {
                DB::table('deckle_process_qualities')->insert([
                    'parent_id'        => $deckle->id,
                    'deckle_no'        => $deckle->deckle_no,
                    'item_id'          => $request->new_quality['item_id'],
                    'bf'               => $request->new_quality['bf'],
                    'gsm'              => $request->new_quality['gsm'],
                    'speed'            => $request->new_quality['speed'],
                    'production_in_kg' => $request->new_quality['production_in_kg'],
                    'start_time_stamp' => date(
                                            'Y-m-d H:i:s',
                                            strtotime($request->new_quality['start_time_stamp'])
                                        ),
                    'status'           => 1,
                    'company_id'       => Session::get('user_company_id'),
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Deckle updated successfully."
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }    
    public function ConsumptionRateIndex(){

        $list = Consumption_rate_item_wise::where('consumption_rate_item_wise.company_id',Session::get('user_company_id'))
                                        ->join('manage_items','consumption_rate_item_wise.item_id','manage_items.id')
                                        ->select('consumption_rate_item_wise.*','manage_items.name')
                                        ->get();
        return view('production.set_item_wise_consumption')->with('list',$list);
    }
    public function AddConsumptionRate(){

        $Production_item = ProductionItem::where('production_items.company_id', Session::get('user_company_id'))
                                ->join('manage_items', 'production_items.item_id', '=', 'manage_items.id')
                                ->whereNotIn('production_items.item_id', function($q){
                                    $q->select('item_id')->from('consumption_rate_item_wise');
                                })
                                ->select('manage_items.id as item_id', 'manage_items.name')
                                ->get();

        
        $manage_items = ManageItems::where('company_id',Session::get('user_company_id'))
                                    ->where('delete','0')
                                    ->select('manage_items.*')
                                    ->get();

        
        return view('production.add_item_wise_consumption')->with('Production_items',$Production_item)->with('manage_items',$manage_items);

    }
    public function StoreConsumptionRate(Request $request)
    {
    
        $request->validate([
            'production_item_id' => 'required|integer',
            'per_kg' => 'required|numeric',
            'variance' => 'required|numeric',
            'material_item_id' => 'required|array',
            'material_qty' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            // Step 1: Insert main consumption definition
            $main = Consumption_rate_item_wise::create([
                'item_id'       => $request->production_item_id,
                'variance_rate' => $request->variance,
                'per_kg'        => $request->per_kg,  // ADD this column in table if not exists
                'status'        => 1,
                'created_by'    => Session::get('user_id'),
                'company_id'    => Session::get('user_company_id'),
            ]);

            // Step 2: Insert material items
            foreach ($request->material_item_id as $index => $itemId) {

                ConsumptionItems::create([
                    'compsumption_item_wise_rate_id' => $main->id,
                    'item_id'             => $itemId,
                    'consumption_rate'    => $request->material_qty[$index],
                    'company_id'          => Session::get('user_company_id'),
                    'created_by'          => Session::get('user_id'),
                ]);
            }

            DB::commit();

            return redirect()->route('ConsumptionRate')->with('success', 'Consumption Rate Saved Successfully');

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function EditConsumptionRate($id)
    {
        $companyId = Session::get('user_company_id');

        // Main consumption record
        $editData = Consumption_rate_item_wise::where('company_id', $companyId)
                        ->where('id', $id)
                        ->firstOrFail();

        // Materials used for this consumption rate
        $editMaterials = ConsumptionItems::where('compsumption_item_wise_rate_id', $id)
                            ->where('company_id', $companyId)
                            ->get();

        // All production items
        $Production_items = ProductionItem::where('production_items.company_id',$companyId)
                            ->join('manage_items','production_items.item_id','manage_items.id')
                            ->select('production_items.item_id as item_id','manage_items.name')
                            ->get();

        // All items for material dropdown
        $manage_items = ManageItems::where('company_id', $companyId)->get();

        return view('production.edit_item_wise_consumption_rate', compact(
            'editData',
            'editMaterials',
            'Production_items',
            'manage_items'
        ));
    }
    public function UpdateConsumptionRate(Request $request, $id)
    {
        $request->validate([
            'per_kg' => 'required|numeric',
            'variance' => 'required|numeric',
            'material_item_id' => 'required|array',
            'material_qty' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            $companyId = Session::get('user_company_id');

            // Update main record
            $main = Consumption_rate_item_wise::where('id', $id)
                        ->where('company_id', $companyId)
                        ->firstOrFail();

            $main->update([
                'per_kg'        => $request->per_kg,
                'variance_rate' => $request->variance,
                'updated_by'    => Session::get('user_id')
            ]);

            // DELETE OLD MATERIAL ITEMS
            ConsumptionItems::where('compsumption_item_wise_rate_id', $id)
                ->where('company_id', $companyId)
                ->delete();

            // INSERT NEW MATERIAL ITEMS
            foreach ($request->material_item_id as $index => $itemId) {

                ConsumptionItems::create([
                    'compsumption_item_wise_rate_id' => $id,
                    'item_id'             => $itemId,
                    'consumption_rate'    => $request->material_qty[$index],
                    'company_id'          => $companyId,
                    'created_by'          => Session::get('user_id'),
                ]);
            }

            DB::commit();

            return redirect()->route('ConsumptionRate')->with('success', 'Consumption Rate Updated Successfully');

        } catch (\Exception $e) {

            DB::rollback();
        

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function delete($id)
    {
        // Check main record
        $main = Consumption_rate_item_wise::where('company_id', Session::get('user_company_id'))
                    ->where('id', $id)
                    ->first();

        if (!$main) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        // Delete child material rows
        ConsumptionItems::where('compsumption_item_wise_rate_id', $id)->delete();

        // Delete main row
        $main->delete();

        return redirect()->back()->with('success', 'Consumption rate deleted successfully.');
    }
    public function getConsumptionItems($item_id)
    {
        $k = "shubh";
        // 1. Find consumption definition for this generated item
        $rate = DB::table('consumption_rate_item_wise')
            ->where('item_id', $item_id)
            ->where('status', 1)
            ->first();

        if (!$rate) {
            return response()->json(['status' => false, 'data' => []]);
        }

        // 2. Fetch consumption items linked with this definition
        $items = DB::table('consumption_items')
            ->join('manage_items', 'manage_items.id', '=', 'consumption_items.item_id')
            ->join('units', 'units.id', '=', 'manage_items.u_name')
            ->where('compsumption_item_wise_rate_id', $rate->id)
            ->select(
                'consumption_items.item_id',
                'consumption_items.consumption_rate',
                'manage_items.name as item_name',
                'units.s_name as unit_name',
                'manage_items.u_name as unit_id'
            )
            ->get();

        return response()->json([
            'status' => true,
            'per_kg' => $rate->per_kg,
            'variance_rate' => $rate->variance_rate,
            'items' => $items,
            'k' => $k
        ]);
    }
    public function CancelCompletedDeckle($id)
    {
        $companyId = Session::get('user_company_id');

        DB::beginTransaction();

        try {

            // Update status from 2 → 5 (cancel)
            $updated = DeckleProcess::where('company_id', $companyId)
                ->where('id', $id)
                ->where('status', 2)
                ->update(['status' => 5]);

            DB::commit();

            if ($updated) {
                return back()->with('success', 'Cancelled successfully');
            } else {
                return back()->with('error', 'Cannot cancel: Already in Process for Reel Cutting');
            }

        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Error cancelling record: '.$e->getMessage());
        }
    }
    public function getDeckleQualityProduction($deckle_id)
    {
        $qualities = DB::table('deckle_process_qualities')
            ->join('manage_items', 'manage_items.id', '=', 'deckle_process_qualities.item_id')
            ->where('deckle_process_qualities.parent_id', $deckle_id)
            //->where('deckle_process_qualities.status', 1)   // ✅ ONLY ACTIVE
            ->select(
                // 'deckle_process_qualities.id as quality_row_id',
                // 'manage_items.name as quality_name',
                // 'deckle_process_qualities.production_in_kg'
                'deckle_process_qualities.id as quality_row_id',
                'deckle_process_qualities.item_id',
                'deckle_process_qualities.bf',
                'deckle_process_qualities.gsm',
                'deckle_process_qualities.speed',
                'deckle_process_qualities.production_in_kg'
            )
            ->get();

        return response()->json($qualities);
    }

    public function machineTimeLoss(Request $request){
        $companyId = Session::get('user_company_id');
        $from_date = $request->from_date ?? Carbon::today()->format('Y-m-d');
        $to_date   = $request->to_date   ?? Carbon::today()->format('Y-m-d');
        $fromDateTime = Carbon::parse($from_date)->setTime(8, 0);
        $toDateTime   = Carbon::parse($to_date)->addDay()->setTime(8, 0);
        $logs = DB::table('deckle_machine_stop_logs')
        ->where('company_id', $companyId)
        ->where(function ($q) use ($fromDateTime, $toDateTime) {
            $q->whereBetween('stopped_at', [$fromDateTime, $toDateTime])
              ->orWhereBetween('start_at', [$fromDateTime, $toDateTime]);
        })
        ->orderBy('stopped_at')
        ->get();
        $report = [];
        foreach ($logs as $row) {
            $stopStart = Carbon::parse($row->stopped_at);
            $stopEnd   = $row->start_at
                ? Carbon::parse($row->start_at)
                : Carbon::now();
            $current = $stopStart->copy();
            while ($current < $stopEnd) {
                // Determine shift
                $time = $current->format('H:i');
    
                if ($time >= '08:00' && $time < '20:00') {
                    // Shift A
                    $shift = 'A';
                    $segmentEnd = min(
                        $current->copy()->setTime(20, 0),
                        $stopEnd
                    );
                } else {
                    // Shift B
                    $shift = 'B';
    
                    if ($time >= '20:00') {
                        $segmentEnd = min(
                            $current->copy()->addDay()->setTime(8, 0),
                            $stopEnd
                        );
                    } else {
                        $segmentEnd = min(
                            $current->copy()->setTime(8, 0),
                            $stopEnd
                        );
                    }
                }
    
                $seconds = $current->diffInSeconds($segmentEnd);
                $minutes = $seconds / 60;
                
                            // Production date logic (inline, no helper)
                            // Production date logic
                if ($current->format('H:i') < '08:00') {
                    $prodDate = $current->copy()->subDay()->format('Y-m-d');
                } else {
                    $prodDate = $current->format('Y-m-d');
                }
                
                // ✅ STRICT production-date filter (prevents extra next-day rows)
                if ($prodDate < $from_date || $prodDate > $to_date) {
                    $current = $segmentEnd;
                    continue;
                }
                
                if (!isset($report[$prodDate])) {
                    $report[$prodDate] = [
                        'shift_a' => 0,
                        'shift_b' => 0,
                        'details' => [
                            'A' => [],
                            'B' => []
                        ]
                    ];
                }
                if ($shift === 'A') {
                    $report[$prodDate]['shift_a'] += $minutes;
                } else {
                    $report[$prodDate]['shift_b'] += $minutes;
                }
                $report[$prodDate]['details'][$shift][] = [
                    'id' => $row->id,
                    'deckle_no' => $row->deckle_no,
                    'stopped_at' => $current->format('Y-m-d H:i:s'),
                    'start_at' => $segmentEnd->format('Y-m-d H:i:s'),
                    'minutes' => $minutes,
                    'reason' => $row->reason,
                    'remark' => $row->remark,
                ];
                $current = $segmentEnd;
            }
    }
        return view('production.machine_time_loss', compact(
            'report',
            'from_date',
            'to_date'
        ));
    }
    public function getMachineLoss($id)
{
    $company_id = Session::get('user_company_id');

    $data = DeckleMachineStopLog::where('company_id',$company_id)
                ->where('id',$id)
                ->first();

    if($data){
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    return response()->json([
        'status' => false
    ]);
}

public function updateMachineLoss(Request $request)
{
    $company_id = Session::get('user_company_id');

    $log = DeckleMachineStopLog::where('company_id',$company_id)
            ->where('id',$request->id)
            ->first();

    if(!$log){
        return response()->json([
            'status'=>false,
            'message'=>'Record not found'
        ]);
    }

    $log->stopped_at = $request->stopped_at;
    $log->start_at   = $request->started_at;

    $log->stopped_by = Session::get('user_id');
    $log->start_by   = Session::get('user_id');

    $log->reason     = $request->reason;
    $log->remark     = $request->remark;

    $log->updated_at = now();
    $log->save();

    return response()->json([
        'status'=>true,
        'message'=>'Updated successfully'
    ]);
}
    public function storeMachineLoss(Request $request)
    {
        $request->validate([
            'stopped_at' => 'required|date',
            'started_at' => 'required|date',
            'reason' => 'required',
            'remark' => 'required'
        ]);

        $userId = Session::get('user_id');

        DB::table('deckle_machine_stop_logs')->insert([
            'deckle_id'   => 0,
            'deckle_no'   => 0,

            'stopped_by'  => $userId,
            'stopped_at'  => $request->stopped_at,

            'start_by'    => $userId,
            'start_at'    => $request->started_at,

            'reason'      => $request->reason,
            'remark'      => $request->remark,

            'company_id'  => Session::get('user_company_id'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'status' => true
        ]);
    }
    public function deleteDeckleQuality(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);
        
        $delete = DeckleProcessQuality::where('id',$request->id)->delete();
        if($delete){
            return response()->json([
                'status' => true
            ]);
        }else{
            return response()->json([
                'status' => false
            ]);
        }
        
    }
    public function deleteMachineLoss(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $log = DeckleMachineStopLog::where('company_id',$company_id)
                ->where('id',$request->id)
                ->first();

        if(!$log){
            return response()->json([
                'status'=>false,
                'message'=>'Record not found'
            ]);
        }

        $log->delete();

        return response()->json([
            'status'=>true,
            'message'=>'Deleted successfully'
        ]);
    }
}
