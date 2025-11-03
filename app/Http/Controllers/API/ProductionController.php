<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\Models\ProductionItem;
use App\Models\DeckleProcess;
use App\Models\ItemGroups;
use App\Models\SaleOrderSetting;
use App\Models\DeckleProcessQuality;
use App\Models\ManageItems;
use App\Models\DeckleMachineStopLog;
use App\Models\DeckleItem;
use App\Models\ItemSizeStock;
class ProductionController extends Controller
{    
    public function popRollItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            
        ], [
            'company_id.required' => 'Company id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $items = ProductionItem::join('manage_items','production_items.item_id','=','manage_items.id')
                                ->select('production_items.id','name','bf','gsm','speed')
                                ->where('production_items.company_id',$request->company_id)
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        $pop_roll_no = DeckleProcess::where('company_id',$request->company_id)->max('deckle_no');
        if($pop_roll_no==""){
            $pop_roll_no = 1;
        }else{
            $pop_roll_no++;
        }
        return response()->json(['code' => 200, 'message' => "Items List",'data'=> $items,"pop_roll_no"=>$pop_roll_no]);
    }
    public function addPopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'item_id' => 'required',
            'item_bf' => 'required',
            'item_gsm' => 'required',
            'pop_roll_no' => 'required',
            'start_time_stamp' => 'required',
            'speed' => 'required',
            'user_id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'item_id.required' => 'Item id is required.',
            'item_bf.required' => 'Item BF is required.',
            'item_gsm.required' => 'Item GSM is required.',
            'pop_roll_no.required' => 'POP Roll No. is required.',
            'start_time_stamp.required' => 'Strat time is required.',
            'speed.required' => 'Speed is required.',
            'user_id.required' => 'User id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $pop_roll_no = DeckleProcess::where('company_id',$request->company_id)->max('deckle_no');
        if($pop_roll_no==""){
            $pop_roll_no = 1;
        }else{
            $pop_roll_no++;
        }
        $deckle = new DeckleProcess;
        $deckle->deckle_no = $pop_roll_no;
        $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));
        $deckle->started_by = $request->user_id;
        $deckle->company_id = $request->company_id;
        $deckle->created_at = Carbon::now();
        if($deckle->save()){
            $deckle_quality = new DeckleProcessQuality;
            $deckle_quality->parent_id = $deckle->id;
            $deckle_quality->item_id = $request->item_id;
            $deckle_quality->bf = $request->item_bf;
            $deckle_quality->gsm = $request->item_gsm;
            $deckle_quality->deckle_no = $pop_roll_no;
            $deckle_quality->start_time_stamp = date('Y-m-d H:i:s',strtotime($request->start_time_stamp));
            $deckle_quality->speed = $request->speed;
            $deckle_quality->started_by = $request->user_id;
            $deckle_quality->company_id = $request->company_id;
            $deckle_quality->created_at = Carbon::now();
            $deckle_quality->save();
            return response()->json(['code' => 200, 'message' => "Pop Roll Added Successfully.",'data'=>""]);
        }
    }
    public function runningPopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id'
            
        ], [
            'company_id.required' => 'Company id is required.',
            
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $running_deckle = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','start_time_stamp','end_time_stamp','speed','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->where('deckle_processes.company_id',$request->company_id)
                                ->where('deckle_processes.status',1)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','stop_machine_status')
                                ->first();
        if($running_deckle){
            $machine_logs = DeckleMachineStopLog::join('users as u1','deckle_machine_stop_logs.stopped_by','u1.id')
                            ->leftJoin('users as u2','deckle_machine_stop_logs.start_by','u2.id')
                            ->select(
                                'deckle_machine_stop_logs.id',
                                'deckle_machine_stop_logs.stopped_at',
                                'deckle_machine_stop_logs.reason',
                                'deckle_machine_stop_logs.remark',
                                'u1.name as stopped_by_name',
                                'u2.name as start_by_name'
                            )
                            ->where('deckle_id', $running_deckle->id)
                            ->orderBy('stopped_at', 'desc')
                            ->get();
        }else{
            $machine_logs = [];
        }
        

        return response()->json(['code' => 200, 'message' => "Running Pop Roll.",'data'=>$running_deckle,"machine_logs"=>$machine_logs]);
    }
    public function addNewPopRollQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'item_id' => 'required',
            'item_bf' => 'required',
            'item_gsm' => 'required',
            'pop_roll_id' => 'required',
            'last_quality_row_id' => 'required',
            'last_quality_production_in_kg' => 'required',
            'last_quality_speed' => 'required',
            'speed' => 'required',
            'user_id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'item_id.required' => 'Item id is required.',
            'item_bf.required' => 'Item BF is required.',
            'item_gsm.required' => 'Item GSM is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'last_quality_row_id.required' => 'Quality id is required.',
            'last_quality_production_in_kg.required' => 'Quality production is required.',
            'last_quality_speed.required' => 'Quality speed is required.',
            'speed.required' => 'Speed is required.',
            'user_id.required' => 'User id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }        
        
        $deckle_process = DeckleProcess::find($request->pop_roll_id);
        if($deckle_process){
            DeckleProcessQuality::where('company_id',$request->company_id)
                                ->where('id',$request->last_quality_row_id)
                                ->update(["production_in_kg"=>$request->last_quality_production_in_kg,"speed"=>$request->last_quality_speed]);
            $deckle = new DeckleProcessQuality;
            $deckle->parent_id = $request->pop_roll_id;
            $deckle->item_id = $request->item_id;
            $deckle->bf = $request->item_bf;
            $deckle->gsm = $request->item_gsm;
            $deckle->deckle_no = $deckle_process->deckle_no;
            $deckle->start_time_stamp = date('Y-m-d H:i:s',strtotime(Carbon::now()));
            $deckle->speed = $request->speed;
            $deckle->started_by = $request->user_id;
            $deckle->company_id = $request->company_id;
            $deckle->created_at = Carbon::now();
            if($deckle->save()){
                return response()->json(['code' => 200, 'message' => "Quality Added Successfully.",'data'=>'']);
            }
        }
    }
    public function completePopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',            
            'pop_roll_id' => 'required',
            'last_quality_row_id' => 'required',
            'last_quality_production_in_kg' => 'required',
            'last_quality_speed' => 'required',
            'user_id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'last_quality_row_id.required' => 'Quality id is required.',
            'last_quality_production_in_kg.required' => 'Quality production is required.',
            'last_quality_speed.required' => 'Quality speed is required.',
            'user_id.required' => 'User id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $update_poproll = DeckleProcess::where('company_id',$request->company_id)
                            ->where('id',$request->pop_roll_id)
                            ->update(['stopped_by'=>$request->user_id,'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);
        if($update_poproll){
            DeckleProcessQuality::where('company_id',$request->company_id)
                            ->where('id',$request->last_quality_row_id)
                            ->update(["production_in_kg"=>$request->last_quality_production_in_kg,"speed"=>$request->last_quality_speed,'end_time_stamp'=>date('Y-m-d H:i:s'),"status"=>2]);

            return response()->json(['code' => 200, 'message' => "Pop Roll Completed Successfully.",'data'=>'']);
        }
    }
    public function completedPopRolls(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id'
            
        ], [
            'company_id.required' => 'Company id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $completed_deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id');
                                }])
                                ->where('deckle_processes.company_id',$request->company_id)
                                ->where('deckle_processes.status',2)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->get();
        return response()->json(['code' => 200, 'message' => "Completed Pop Roll List.",'data'=>$completed_deckles]);
        
    }
    public function stopPopRollMachine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required',
            'reason'=> 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.',
            'reason.required' => 'Reason is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deckle = DeckleProcess::find($request->pop_roll_id);
        if($deckle){
            $deckle->stop_machine_status = 1;
            if($deckle->save()){
                $log = new DeckleMachineStopLog;
                $log->deckle_id = $request->pop_roll_id;
                $log->deckle_no = $deckle->deckle_no;
                $log->stopped_by = $request->user_id;
                $log->stopped_at = date('Y-m-d H:i:s');
                $log->reason = $request->reason;
                $log->remark = $request->remark;
                $log->company_id = $request->company_id;
                $log->created_at = Carbon::now();
                $log->save();
                return response()->json(['code' => 200, 'message' => "Pop Roll Machine Stopped Successfully.",'data'=>'']);
            }
        }
    }
    public function startPopRollMachine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $update = DeckleProcess::where('company_id',$request->company_id)
                                ->where('id',$request->pop_roll_id)
                                ->update(['stop_machine_status'=>0]);
        if($update){
            DeckleMachineStopLog::where('deckle_id',$request->pop_roll_id)
                                ->where('start_at',null)
                                ->update(['start_by'=>$request->user_id,'start_at'=>date('Y-m-d H:i:s')]);
            
            return response()->json(['code' => 200, 'message' => "Pop Roll Machine Start Successfully.",'data'=>'']);
        }       
    }
    public function startPopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $update = DeckleProcess::where('id',$request->pop_roll_id)->update(['status'=>3]);
        if($update){
            return response()->json(['code' => 200, 'message' => "Pop Roll Start Successfully.",'data'=>'']);
        }
    }
    public function startPopRollList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $start_deckles = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->where('deckle_processes.company_id',$request->company_id)
                                ->where('deckle_processes.status',3)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
                                ->first();
        $reel_no = ItemSizeStock::where('company_id',$request->company_id)
                                ->where('deckle_id','!=','0')
                                ->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        return response()->json(['code' => 200, 'message' => "Start Pop Roll List.",'data'=>$start_deckles,"reel_no"=>$reel_no]);
        
    }
    public function storePopRollReelDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required',
            'reel_detail'=>'required|array'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.',
            'reel_detail.required' => 'Reel detail is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        DeckleProcess::where('company_id', $request->company_id)
                ->where('id', $request->pop_roll_id)
                ->update(['status' => 4,'reel_generated_at'=>date('Y-m-d H:i:s')]);
        $reel_no = ItemSizeStock::where('company_id',$request->company_id)
                                ->where('deckle_id','!=','0')
                                ->max('reel_no');
        if($reel_no==""){
            $reel_no = 0;
        }
        foreach($request->reel_detail as $reel){
            $reel_no++;
            $deckleItem = new DeckleItem;
            $deckleItem->deckle_id = $request->pop_roll_id;
            $deckleItem->quality_id = $reel['item_id'];
            $deckleItem->quality_row_id = $reel['quality_row_id'] ?? null;
            $deckleItem->size = $reel['size'];
            $deckleItem->reel_no = $reel_no;
            $deckleItem->weight = $reel['weight'];
            $deckleItem->bf = $reel['bf'] ?? null;
            $deckleItem->gsm = $reel['gsm'] ?? null;
            $deckleItem->unit = $reel['unit'];
            $deckleItem->company_id = $request->company_id;
            $deckleItem->created_by = $request->user_id;
            $deckleItem->created_at = Carbon::now();
            $deckleItem->save();

            $stock = new ItemSizeStock;
            $stock->item_id = $reel['item_id'];
            $stock->quality_row_id = $reel['quality_row_id'] ?? null;
            $stock->weight = $reel['weight'];
            $stock->reel_no = $reel_no;
            $stock->size = $reel['size'] ?? null;
            $stock->bf = $reel['bf'] ?? null;
            $stock->gsm = $reel['gsm'] ?? null;
            $stock->unit = $reel['unit'] ?? null;
            $stock->deckle_id = $request->pop_roll_id;
            $stock->company_id = $request->company_id;
            $stock->created_by = $request->user_id;
            $stock->created_at = Carbon::now();
            $stock->save();
        }        
        return response()->json(['code' => 200, 'message' => "Reel Detail Store Successfully.",'data'=>'']);        
    }
    public function generatedPopRollReelList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
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
                                ->where('deckle_processes.company_id',$request->company_id)
                                ->where('deckle_processes.status',4)
                                ->whereDate('deckle_processes.reel_generated_at','>=',$from_date)
                                ->whereDate('deckle_processes.reel_generated_at','<=',$to_date)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp','reel_generated_at',DB::raw('(SELECT COUNT(*) FROM item_size_stocks WHERE item_size_stocks.deckle_id = deckle_processes.id and item_size_stocks.status=0) as reel_sale_status'))
                                ->get();
        foreach ($completed_deckles as $deckle) {
            $deckle->quality->load([
                'items' => function($q) use ($deckle) {
                    $q->where('deckle_id', $deckle->id);
                }
            ]);
        }
        return response()->json(['code' => 200, 'message' => "Reel Detail Fetch Successfully.",'data'=>$completed_deckles]);
    }
    public function cancelGeneratedPopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        DeckleItem::where('deckle_id',$request->pop_roll_id)->update(['status'=>0]);
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)->delete();
        DeckleProcess::where('id',$request->pop_roll_id)->update(['status'=>2,'reel_generated_at'=>null]);
        return response()->json(['code' => 200, 'message' => "Pop Roll Cancelled Successfully."]);
    }
    public function updateGeneratedPopRoll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'pop_roll_id' => 'required',
            'user_id' => 'required',
            'reel_detail'=>'required|array'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'pop_roll_id.required' => 'POP Roll No. is required.',
            'user_id.required' => 'User id is required.',
            'reel_detail.required' => 'Reel detail is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $item_id_arr = DeckleItem::where('deckle_id',$request->pop_roll_id)
                                    ->where('status',1)
                                    ->pluck('id')
                                    ->toArray(); 
        ItemSizeStock::where('deckle_id',$request->pop_roll_id)->delete();
        foreach($request->reel_detail as $reel){
            if(!empty($reel['row_id'])){
                // Update existing record
                $deckleItem = DeckleItem::find($reel['row_id']);
                if ($deckleItem) {
                    $deckleItem->quality_id = $reel['item_id'];
                    $deckleItem->quality_row_id = $reel['quality_row_id'];
                    $deckleItem->size = $reel['size'];
                    $deckleItem->reel_no = $reel['reel_no'];
                    $deckleItem->weight = $reel['weight'];
                    $deckleItem->bf = $reel['bf'];
                    $deckleItem->gsm = $reel['gsm'];
                    $deckleItem->unit = $reel['unit'];
                    $deckleItem->updated_at = Carbon::now();
                    $deckleItem->save();
                }
                // Remove from the array to keep track of processed IDs
                if (($key = array_search($reel['row_id'], $item_id_arr)) !== false) {
                    unset($item_id_arr[$key]);
                }
            } else {
                // Create new record
                $deckle = new DeckleItem;
                $deckle->deckle_id = $request->pop_roll_id;
                $deckle->quality_id = $reel['item_id'];
                $deckle->quality_row_id = $reel['quality_row_id'];
                $deckle->size = $reel['size'];
                $deckle->reel_no = $reel['reel_no'];
                $deckle->weight = $reel['weight'];
                $deckle->bf = $reel['bf'];
                $deckle->gsm = $reel['gsm'];
                $deckle->unit = $reel['unit'];
                $deckle->company_id =  $request->company_id;
                $deckle->created_by = $request->user_id;
                $deckle->created_at = Carbon::now();
                $deckle->save();
            }
            $stock = new ItemSizeStock;
            $stock->item_id = $reel['item_id'];
            $stock->quality_row_id = $reel['quality_row_id'];
            $stock->weight = $reel['weight'];
            $stock->reel_no = $reel['reel_no'];
            $stock->size = $reel['size'];
            $stock->bf = $reel['bf'];
            $stock->gsm = $reel['gsm'];
            $stock->unit = $reel['unit'];
            $stock->deckle_id = $request->pop_roll_id;
            $stock->company_id = $request->company_id;
            $stock->created_by = $request->user_id;
            $stock->created_at = Carbon::now();
            $stock->save();
        }
        DeckleItem::whereIn('id',$item_id_arr)->update(['status'=>0]);
        return response()->json(['code' => 200, 'message' => "Pop Roll Updated Successfully."]);
    }
    public function completePopRollSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $from_date = date('Y-m-d');
        $to_date = date('Y-m-d');
        if(isset($request->from_date) && isset($request->to_date)){
            $from_date = date('Y-m-d',strtotime($request->from_date));
            $to_date = date('Y-m-d',strtotime($request->to_date));
        }
        $completed_deckles = DeckleProcessQuality::join('deckle_processes','deckle_process_qualities.parent_id','deckle_processes.id')
                                ->join('manage_items','deckle_process_qualities.item_id','manage_items.id')
                                ->where('deckle_process_qualities.company_id',$request->company_id)
                                ->where('deckle_processes.status','!=','1')
                                ->whereDate('deckle_processes.end_time_stamp','>=',$from_date)
                                ->whereDate('deckle_processes.end_time_stamp','<=',$to_date)
                                ->when(isset($request->item_id), function ($query) use ($request) {
                                    return $query->where('deckle_process_qualities.item_id', $request->item_id);
                                })
                                ->select('manage_items.name',DB::raw('SUM(deckle_process_qualities.production_in_kg) as total_production_kg'))
                                ->groupBy('deckle_process_qualities.item_id')
                                ->get();
        return response()->json(['code' => 200, 'message' => "Completed Pop Roll Summary.",'data'=>$completed_deckles]);
    }
    public function stopMachineReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required'
            
        ], [
            'company_id.required' => 'Company id is required.',
            'user_id.required' => 'User id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $reason_list = array(
            "Maintenance",
            "Breakdown",
            "Power Failure",
            "Shift Change",
            "Other"
        );
        return response()->json(['code' => 200, 'message' => "Machine Stop Reason List.",'data'=>$reason_list]);
    }
    
}
