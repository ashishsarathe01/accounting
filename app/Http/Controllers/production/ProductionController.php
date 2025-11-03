<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;
use Carbon\Carbon;
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
class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company_id = Session::get('user_company_id');
        // $completed_deckles = DeckleProcess::with(['quality'=>function($q){
        //                             $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
        //                             $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
        //                         }])->join('manage_items','deckle_processes.item_id','manage_items.id')
        //                         ->where('deckle_processes.company_id',Session('user_company_id'))
        //                         ->where('deckle_processes.status',2)
        //                         ->select('deckle_processes.id','deckle_no','bf','gsm','name','production_in_kg','start_time_stamp','end_time_stamp')
        //                         ->get();
        // echo "<pre>";
        // print_r($completed_deckles->toArray());die;
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
        $deckle_no = DeckleProcess::where('company_id',Session::get('user_company_id'))->max('deckle_no');
        if($deckle_no==""){
            $deckle_no = 1;
        }else{
            $deckle_no++;
        }
        return view('production/add_deckle',["items"=>$items,"deckle_no"=>$deckle_no,"quality_id"=>$quality_id]);
    }
    public function stopDeckleProcess(Request $request)
    {

        // echo "<pre>";
        // print_r($request->all());die;
        $update = DeckleProcess::where('company_id',Session::get('user_company_id'))
                            ->where('id',$request->id)
                            ->update(['stopped_by'=>Session('user_id'),'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);
        DeckleProcessQuality::where('company_id',Session::get('user_company_id'))
                            ->where('id',$request->last_row_id)
                            ->update(["production_in_kg"=>$request->production_in_kg,"speed"=>$request->new_actual_speed,'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);
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
       
        
        $deckle = new DeckleProcess;
        $deckle->deckle_no = $request->deckle_no;
        $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));       
        $deckle->started_by = Session::get('user_id');
        $deckle->company_id = Session::get('user_company_id');
        $deckle->created_at = Carbon::now();
        if($deckle->save()){
            $deckle_quality = new DeckleProcessQuality;
            $deckle_quality->parent_id = $deckle->id;
            $deckle_quality->item_id = $request->item_id;
            $deckle_quality->bf = $request->item_bf;
            $deckle_quality->gsm = $request->item_gsm;
            $deckle_quality->deckle_no = $request->deckle_no;
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
        $company_id = Session::get('user_company_id');
        $item = ProductionItem::findOrFail($id);

        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        // Exclude items already added except the current one
        $addedItemIds = ProductionItem::where('company_id', $company_id)
                                ->where('id', '!=', $id)
                                ->pluck('item_id')
                                ->toArray();

        $availableItemIds = array_diff($allowedItemIds, $addedItemIds);

        $groups = ItemGroups::where('company_id', $company_id)
            ->with(['items' => function($query) use ($availableItemIds) {
                $query->whereIn('id', $availableItemIds)
                      ->where('status', '1');
            }])
            ->get()
            ->filter(fn($group) => $group->items->count() > 0);

        return view('production.edit_set_item', compact('item', 'groups'));
    }

    /**
     * Update set item
     */
    public function updateItem(Request $request, $id)
    {
        $company_id = Session::get('user_company_id');
        $item = ProductionItem::findOrFail($id);

        $request->validate([
            'item_id' => 'required|integer',
            'bf' => 'required|integer',
            'gsm' => 'required|integer',
            'speed' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        // Prevent duplicate item except current
        $exists = ProductionItem::where('company_id', $company_id)
                    ->where('item_id', $request->item_id)
                    ->where('id', '!=', $id)
                    ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Item already added.');
        }

        $item->update([
            'item_id' => $request->item_id,
            'bf' => $request->bf,
            'gsm' => $request->gsm,
            'speed' => $request->speed,
            'status' => intval($request->status),
            'updated_at' => now(),
        ]);

        return redirect()->route('production.set_item')->with('success', 'Item updated successfully.');
    }

    /**
     * Delete set item
     */
    public function destroyItem($id)
    {
        $item = ProductionItem::find($id);
        if ($item) {
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
                                ->update(["production_in_kg"=>$request->actual_production_in_kg,"speed"=>$request->actual_speed]);
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
    public function storeItem(Request $request)
    {
        $company_id = Session::get('user_company_id');
        $created_by = Session::get('user_id');

        $request->validate([
            'item_id' => 'required|integer',
            'bf' => 'required|integer',
            'gsm' => 'required|integer',
            'speed' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        // Prevent duplicate item
        $exists = ProductionItem::where('company_id', $company_id)
                    ->where('item_id', $request->item_id)
                    ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Item already added.');
        }

        ProductionItem::create([
            'item_id' => $request->item_id,
            'bf' => $request->bf,
            'gsm' => $request->gsm,
            'speed' => $request->speed,
            'status' => intval($request->status),
            'company_id' => $company_id,
            'created_by' => $created_by,
            'created_at' => now(),
        ]);

        return redirect()->route('production.set_item')->with('success', 'Item added successfully.');
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
        if(isset($request->from_date) && isset($request->to_date)){
            $from_date = date('Y-m-d',strtotime($request->from_date));
            $to_date = date('Y-m-d',strtotime($request->to_date));
        }
        $completed_deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
                                    $q->with(['items'=>function(){
                                        
                                    }]);
                                }])
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',4)
                                ->whereDate('deckle_processes.reel_generated_at','>=',$from_date)
                                ->whereDate('deckle_processes.reel_generated_at','<=',$to_date)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp','reel_generated_at')
                                ->get();
        foreach ($completed_deckles as $deckle) {
            $deckle->quality->load([
                'items' => function($q) use ($deckle) {
                    $q->where('deckle_id', $deckle->id);
                }
            ]);
        }

        $reel_no = ItemSizeStock::where('company_id',Session::get('user_company_id'))->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        // echo "<pre>";
        // print_r($completed_deckles->toArray());die;
        $completed_poprolls = DeckleProcess::where('company_id', Session('user_company_id'))
        ->where('status', 2)
        ->select('id', 'deckle_no')
        ->get();
        return view("production/deckle_reel_process",["deckles"=>$deckles,"reel_no"=>$reel_no,"start_deckle"=>$start_deckles,"completed_deckles"=>$completed_deckles,"from_date"=>$from_date,"to_date"=>$to_date,"completed_poprolls" => $completed_poprolls]);
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
            DeckleProcess::where('company_id', $company_id)
                ->where('id', $currentPopRollId)
                ->update(['status' =>4,'reel_generated_at'=>date('Y-m-d H:i:s')]);
               


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
                                $stock->created_at = Carbon::now();
                                $stock->save();
                            }
                        }
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
            ->select('size', 'reel_no', 'weight')
            ->where('item_id', $item_id)
            ->where('status', '1')
            ->where('company_id',Session::get('user_company_id'))
            ->get();

        if ($reels->isEmpty()) {
            return response()->json(['error' => 'No data found'], 404);
        }
        $grouped = $reels->groupBy('size')->map(function ($group) {
            return [
                'size' => $group->first()->size,
                'count' => $group->count(),
                'reels' => $group->pluck('reel_no')->toArray(),
                'weights' => $group->pluck('weight')->toArray(),
            ];
        })->values();
        return response()->json($grouped);
    }
    public function cancelPopRollReel(Request $request){
        DeckleItem::where('deckle_id',$request->pop_roll_id)->update(['status'=>0]);
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)->delete();
        DeckleProcess::where('id',$request->pop_roll_id)->update(['status'=>2,'reel_generated_at'=>null]);
        return redirect()->back()->with('success', 'Pop Roll Cancel Successfully.');
    }
    public function editPopRollReel(Request $request){
        $start_deckle = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->find($request->id);
        $item_reel = DeckleItem::where('deckle_id',$request->id)->where('status',1)->get(); 
        $reel_no = ItemSizeStock::where('company_id',Session::get('user_company_id'))->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        return view("production/edit_pop_roll_reel", ["start_deckle" => $start_deckle,"reel_no"=>$reel_no,"item_reel"=>$item_reel]);
    }
    public function updatePopRollReel(Request $request){        
        // echo "<pre>";
        // print_r($request->all());
        $item_id_arr = DeckleItem::where('deckle_id',$request->pop_roll_id)->where('status',1)->pluck('id')->toArray(); 
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)->delete();
        foreach ($request->pop_rolls as $key => $value) {
            foreach ($value['reels'] as $k => $v) {
                if(!empty($v['quality_id'])){
                    if(!empty($v['row_id'])){
                        // Update existing record
                        $deckleItem = DeckleItem::find($v['row_id']);
                        if ($deckleItem) {
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
                    $stock->created_at = Carbon::now();
                    $stock->save();
                }
            }
        }
        DeckleItem::whereIn('id',$item_id_arr)->update(['status'=>0]);
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
            $deckle->deckle_no = $request->deckle_no ?? null;
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
        $stock->size       = $reel['size'];
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
            ->with('item')
            ->orderBy('item_id')
            ->orderBy('reel_no', 'asc')
            ->get()
            ->groupBy('item.name'); // group by item name

        return view('production.view_manual_reels', compact('stocks'));
    }

    /**
     * Show edit form for a stock entry (only if status = 1).
     */
    public function editManual($id)
    {
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
            $stock->size = $request->size;
            $stock->weight = $request->weight;
           $stock->bf = $request->bf;
            $stock->gsm = $request->gsm;
            $stock->unit = $request->unit;
            $stock->updated_at = now();
   

              $stock->save();

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
                $status = false;
            }
        return response()->json(['status' => $status]);
    }
    
}
