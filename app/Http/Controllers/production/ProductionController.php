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

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $deckles = DeckleProcess::join('manage_items','deckle_processes.item_id','manage_items.id')
                                ->where('deckle_processes.company_id',Session('user_company_id'))
                                ->where('deckle_processes.status',1)
                                ->select('deckle_processes.*','name')
                                ->get();
        $running_deckle = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->first();
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id','name','bf','gsm','speed')
                                ->where('production_items.company_id',Session::get('user_company_id'))
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        return view('production/index',["deckles"=>$deckles,"running_deckle"=>$running_deckle,"items"=>$items]);
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
                                  ->with('item')
                                  ->get();

        return view('production.set_item', compact('setItems'));
    }

    /**
     * Show add set item page with all items from manage_items (excluding already added)
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
                                ->select('production_items.id','name','bf','gsm','speed')
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
        $update = DeckleProcess::where('company_id',Session::get('user_company_id'))
                                ->where('id',$request->id)
                                ->update(['stopped_by'=>Session('user_id'),'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);
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
        $deckle->item_id = $request->item_id;
        $deckle->bf = $request->item_bf;
        $deckle->gsm = $request->item_gsm;
        $deckle->deckle_no = $request->deckle_no;
        $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));
        $deckle->speed = $request->speed;
        $deckle->started_by = Session::get('user_id');
        $deckle->company_id = Session::get('user_company_id');
        $deckle->created_at = Carbon::now();
        if($deckle->save()){
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

    /**
     * Edit set item
     */
    public function edit($id)
    {
        $company_id = Session::get('user_company_id');
        $item = ProductionItem::findOrFail($id);

        // Fetch IDs of items already added excluding current
        $addedItemIds = ProductionItem::where('company_id', $company_id)
                                      ->where('id', '!=', $id)
                                      ->pluck('item_id')
                                      ->toArray();

        // Fetch all active items excluding already added ones
        $items = ManageItems::where('company_id', $company_id)
                            ->where('status', '1')
                            ->where('delete', '0')
                            ->whereNotIn('id', $addedItemIds)
                            ->orderBy('name', 'asc')
                            ->get();

        $groups = ItemGroups::where('company_id', $company_id)->get();

        return view('production.edit_set_item', compact('item', 'items', 'groups'));
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
            'speed' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        // Prevent duplicate except current
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
            'new_production_in_kg' => 'required',
            'new_speed' => 'required',
            'deckle_no' => 'required',
        ]);
        $deckle = new DeckleProcessQuality;
        $deckle->parent_id = $request->deckle_id;
        $deckle->item_id = $request->new_item_id;
        $deckle->bf = $request->new_item_bf;
        $deckle->gsm = $request->new_item_gsm;
        $deckle->deckle_no = $request->deckle_no;
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
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'item_bf' => 'required',
            'item_gsm' => 'required',
            'deckle_no' => 'required',
            'start_time_stamp' => 'required',
            'speed' => 'required',
        ]);
        $deckle = new DeckleProcess;
        $deckle->item_id = $request->item_id;
        $deckle->bf = $request->item_bf;
        $deckle->gsm = $request->item_gsm;
        $deckle->deckle_no = $request->deckle_no;
        $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));
        $deckle->speed = $request->speed;
        $deckle->started_by = Session::get('user_id');
        $deckle->company_id = Session::get('user_company_id');
        $deckle->created_at = Carbon::now();
        if($deckle->save()){
            return redirect()->route('deckle-process.index')->with('success','Deckle added successfully');
        }
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
     * Delete set item
     */
 
}
