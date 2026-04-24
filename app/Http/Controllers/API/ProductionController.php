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
use App\Models\ItemLedger;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\SaleOrderSetting;
use App\Models\DeckleProcessQuality;
use App\Models\ManageItems;
use App\Models\Companies;
use App\Models\DeckleMachineStopLog;
use App\Models\DeckleItem;
use App\Helpers\CommonHelper;
use App\Models\ItemSizeStock;
use App\Models\GstBranch;
use App\Models\PrivilegesModuleMapping;
class ProductionController extends Controller
{    
    function getDeckleNo($date,$compnay_id)
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
            $deckle_no = DeckleProcess::where('company_id',$compnay_id)
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->max('deckle_no');
        }else{
            $deckle_no = DeckleProcess::where('company_id',$compnay_id)
                                    ->max('deckle_no');
        }        
        if($deckle_no==""){
            $deckle_no = 1;
        }else{
            $deckle_no++;
        }
        return $deckle_no;
    }
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
                                ->select('production_items.id as production_item_id','manage_items.id as id','name','bf','gsm','speed')
                                ->where('production_items.company_id',$request->company_id)
                                ->where('production_items.status','1')
                                ->orderBy('name')
                                ->get();
        $pop_roll_no = $this->getDeckleNo(date('Y-m-d'),$request->company_id); 
        // $pop_roll_no = DeckleProcess::where('company_id',$request->company_id)->max('deckle_no');
        // if($pop_roll_no==""){
        //     $pop_roll_no = 1;
        // }else{
        //     $pop_roll_no++;
        // }
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
            'start_time_stamp.required' => 'Start time is required.',
            'speed.required' => 'Speed is required.',
            'user_id.required' => 'User id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // $pop_roll_no = DeckleProcess::where('company_id',$request->company_id)->max('deckle_no');
        // if($pop_roll_no==""){
        //     $pop_roll_no = 1;
        // }else{
        //     $pop_roll_no++;
        // }
        $start_time_stamp = Carbon::parse($request->start_time_stamp);
        if ($start_time_stamp->hour < 8) {
            $start_time_stamp->subDay();
        }
        $pop_roll_no = $this->getDeckleNo(date('Y-m-d',strtotime($start_time_stamp)),$request->company_id);
        $deckle = new DeckleProcess;
        $deckle->deckle_no = $pop_roll_no;
        $deckle->start_time_stamp = $start_time_stamp;
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
        $stop_machine_status = 0;
        $running_deckle = DeckleProcess::with(['quality'=>function($q){
                                    $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
                                    $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','start_time_stamp','end_time_stamp','speed','deckle_process_qualities.id as quality_row_id');
                                }])
                                ->where('deckle_processes.company_id',$request->company_id)
                                ->where('deckle_processes.status',1)
                                ->select('deckle_processes.id','deckle_no','start_time_stamp','stop_machine_status')
                                ->first();
        if($running_deckle){
            $stop_machine_status = $running_deckle->stop_machine_status;
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
        
        //Privileges
        $privileges_arr = [];
        if(isset($request->user_id) && $request->user_id!=""){
            //241-Running Pop Roll All Action
            $privileges = PrivilegesModuleMapping::where('module_id',241)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'Running Pop Roll All Action');
            }
        }
        return response()->json(['code' => 200, 'message' => "Running Pop Roll.","stop_machine_status"=>$stop_machine_status,'data'=>$running_deckle,"machine_logs"=>$machine_logs,'privileges'=>$privileges_arr]);
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
        $now = now();
        if ($now->hour < 8) {
            $now = $now->subDay();
        }
        $end_time_stamp = $now->format('Y-m-d H:i:s');
        $update_poproll = DeckleProcess::where('company_id',$request->company_id)
                            ->where('id',$request->pop_roll_id)
                            ->update(['stopped_by'=>$request->user_id,'end_time_stamp'=>$end_time_stamp,"status"=>2]);
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
        //Privileges
        $privileges_arr = [];
        if(isset($request->user_id) && $request->user_id!=""){
            //134-Edit
            $privileges = PrivilegesModuleMapping::where('module_id',134)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'EDIT');
            }
            //239-Delete
            $privileges = PrivilegesModuleMapping::where('module_id',239)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'DELETE');
            }
            //240-START
            $privileges = PrivilegesModuleMapping::where('module_id',240)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'START');
            }
        }
        return response()->json(['code' => 200, 'message' => "Completed Pop Roll List.",'data'=>$completed_deckles,'privileges'=>$privileges_arr]);
        
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
        
        $update = DeckleProcess::where('id',$request->pop_roll_id)
                ->update(['status'=>3]);
        if($update){
            DeckleProcess::where('id','!=',$request->pop_roll_id)
                            ->where('status',3)
                            ->where('company_id',$request->company_id)
                            ->update(['status'=>2]);
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
        $start_deckles = DeckleProcess::with(['quality' => function ($q) {
                            $q->join('manage_items', 'deckle_process_qualities.item_id', '=', 'manage_items.id')
                              ->select(
                                  'parent_id',
                                  'item_id',
                                  'bf',
                                  'gsm',
                                  'production_in_kg',
                                  DB::raw("CONCAT(manage_items.name, ' (', deckle_process_qualities.gsm, ' GSM)') as name"),
                                  'manage_items.id',
                                  'deckle_process_qualities.id as quality_row_id'
                              );
                        }])
                        ->where('deckle_processes.company_id', $request->company_id)
                        ->where('deckle_processes.status', 3)
                        ->select('deckle_processes.id', 'deckle_no', 'start_time_stamp', 'end_time_stamp')
                        ->first();

        // $start_deckles = DeckleProcess::with(['quality'=>function($q){
        //                             $q->join('manage_items','deckle_process_qualities.item_id','manage_items.id');
        //                             $q->select('parent_id','item_id','bf','gsm','production_in_kg','manage_items.name','manage_items.id','deckle_process_qualities.id as quality_row_id');
        //                         }])
        //                         ->where('deckle_processes.company_id',$request->company_id)
        //                         ->where('deckle_processes.status',3)
        //                         ->select('deckle_processes.id','deckle_no','start_time_stamp','end_time_stamp')
        //                         ->first();
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
        $deckle_arr = DeckleProcess::where('company_id',$request->company_id)
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->pluck('id');
        $reel_no = ItemSizeStock::where('company_id',$request->company_id)
                                ->where('deckle_id','!=','0')
                                ->whereIn('deckle_id', $deckle_arr)
                                ->max('reel_no');
        $reel_no = ($reel_no ?? 0) + 1;
        return response()->json(['code' => 200, 'message' => "Start Pop Roll List.",'data'=>$start_deckles,"reel_no"=>$reel_no]);
        
    }
    public function storePopRollReelDetail(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'pop_roll_id'              => 'required',
            'company_id'               => 'required',
            'user_id'                  => 'required',
            'reel_detail'              => 'required|array|min:1',

            'reel_detail.*.item_id'    => 'required|integer',
            'reel_detail.*.size'       => 'required',
            'reel_detail.*.weight'     => 'required',
            'reel_detail.*.unit'       => 'required',

            // optional fields
            'reel_detail.*.quality_row_id' => 'required|integer',
            'reel_detail.*.bf'             => 'required',
            'reel_detail.*.gsm'            => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }   
        DB::beginTransaction();
        try {
            $now = now();
            if ($now->hour < 8) {
                $now = $now->subDay();
            }
            $reel_generated_at = $now->format('Y-m-d H:i:s');
            $deckle = DeckleProcess::find($request->pop_roll_id);
        
            DeckleProcess::where('company_id', $request->company_id)
                    ->where('id', $request->pop_roll_id)
                    ->update(['status' => 4,'reel_generated_at'=>$reel_generated_at]);
            $date = date('Y-m-d', strtotime($deckle->start_time_stamp));
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
            $deckle_arr = DeckleProcess::where('company_id',$request->company_id)
                                        ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                        ->pluck('id');
            $reel_no = ItemSizeStock::where('company_id',$request->company_id)
                                    ->where('deckle_id','!=','0')
                                    ->whereIn('deckle_id',$deckle_arr)
                                    ->max('reel_no');
            if($reel_no==""){
                $reel_no = 0;
            }
            $quality_weight_arr = [];
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
                $stock->created_at = $reel_generated_at;
                $stock->save();
                //Add Weight For Item Ledger
                if (!empty($reel['item_id']) && !empty($reel_no)) {
                    if(isset($quality_weight_arr[$reel['item_id']])){
                        $quality_weight_arr[$reel['item_id']] =  ($quality_weight_arr[$reel['item_id']] ?? 0) + (int) $reel['weight'];
                    }else{
                        $quality_weight_arr[$reel['item_id']] =  (int) $reel['weight'];
                    }
                }
            }
            //Store Item In Item Ledger And Average Detail tables
            if(count($quality_weight_arr)>0){
                $companyData = Companies::where('id', $request->company_id)->first();
                if($companyData->gst_config_type == "single_gst"){
                    $GstSettings = DB::table('gst_settings')
                                    ->where(['company_id' => $request->company_id, 'gst_type' => "single_gst"])
                                    ->get();
                    $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                    ->where(['delete' => '0', 'company_id' => $request->company_id,'gst_setting_id'=>$GstSettings[0]->id])
                                    ->get();
                    if(count($branch)>0){
                        $GstSettings = $GstSettings->merge($branch);
                    }
                    
                }else if($companyData->gst_config_type == "multiple_gst"){
                    $GstSettings = DB::table('gst_settings_multiple')
                                    ->select('id','gst_no','mat_center','series','invoice_start_from')
                                    ->where(['company_id' => $request->company_id, 'gst_type' => "multiple_gst"])
                                    ->get();
                    foreach ($GstSettings as $key => $value) {
                        $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                    ->where(['delete' => '0', 'company_id' => $request->company_id,'gst_setting_multiple_id'=>$value->id])
                                    ->get();
                        if(count($branch)>0){
                        $GstSettings = $GstSettings->merge($branch);
                        }
                    }         
                }
                $series = $GstSettings[0]->series;
                foreach ($quality_weight_arr as $key => $value) {
                    //ADD IN Stock
                    $itemId = (int) $key;
                    $weight = (int) $value;
                    $item_ledger = new ItemLedger();
                    $item_ledger->item_id = $itemId;
                    $item_ledger->series_no = $series;
                    $item_ledger->in_weight = $weight;
                    $item_ledger->txn_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                    $item_ledger->price = 1;
                    $item_ledger->total_price = $weight;
                    $item_ledger->company_id = $request->company_id;
                    $item_ledger->source = 7;
                    $item_ledger->deckle_id = $request->pop_roll_id;
                    $item_ledger->created_by = $request->user_id;
                    $item_ledger->created_at = date('Y-m-d H:i:s');
                    $item_ledger->save();
                        //Add Data In Average Details table
                    $average_detail = new ItemAverageDetail;
                    $average_detail->series_no = $series;
                    $average_detail->entry_date = date('Y-m-d',strtotime($deckle->end_time_stamp));
                    $average_detail->item_id = $itemId;
                    $average_detail->type = 'PRODUCTION GENERATE';
                    $average_detail->deckle_id = $request->pop_roll_id;
                    $average_detail->production_in_weight = $weight;
                    $average_detail->production_in_amount = $weight;
                    $average_detail->company_id = $request->company_id;
                    $average_detail->created_at = Carbon::now();
                    $average_detail->save();
                    CommonHelper::RewriteItemAverageByItemApi(date('Y-m-d',strtotime($deckle->end_time_stamp)),$itemId,$series,$request->company_id);
                }
                
            }
            DB::commit();
            return response()->json([
                'code'    => 200,
                'message' => 'Reel Detail Store Successfully.',
                'data'    => []
            ]);
        }catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'code'    => 500,
                'message' => 'Something went wrong. All changes rolled back.',
                'error'   => $e->getMessage()
            ], 500);
        }
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

                ->where('deckle_processes.company_id', $request->company_id)
                ->where('deckle_processes.status', 4)                
                ->whereDate('deckle_processes.reel_generated_at', '>=', $from_date)
                ->whereDate('deckle_processes.reel_generated_at', '<=', $to_date)
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
        //Privileges
        $privileges_arr = [];
        if(isset($request->user_id) && $request->user_id!=""){
            //135-EDIT
            $privileges = PrivilegesModuleMapping::where('module_id',135)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'EDIT');
            }
            
        }
        return response()->json(['code' => 200, 'message' => "Reel Detail Fetch Successfully.",'data'=>$completed_deckles,'privileges'=>$privileges_arr]);
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
    
    public function editPopRollReel(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'deckle_id'  => 'required|integer'
        ]);
    
        // 1️⃣ Get Pop Roll (Deckle) with Qualities
        $start_deckle = DeckleProcess::with([
            'quality' => function ($q) {
                $q->join(
                    'manage_items',
                    'deckle_process_qualities.item_id',
                    '=',
                    'manage_items.id'
                )
                ->select(
                    'deckle_process_qualities.id as quality_row_id',
                    'deckle_process_qualities.item_id',
                    'manage_items.id',
                    'manage_items.name',
                    'deckle_process_qualities.bf',
                    'deckle_process_qualities.gsm',
                    'deckle_process_qualities.production_in_kg',
                    'deckle_process_qualities.parent_id'
                );
            }
        ])
        ->select('id', 'deckle_no', 'start_time_stamp', 'end_time_stamp')
        ->find($request->deckle_id);
    
        if (!$start_deckle) {
            return response()->json([
                'status'  => false,
                'message' => 'Pop Roll not found'
            ], 404);
        }
    
        // 2️⃣ Get existing reels
        $item_reel = DeckleItem::where('deckle_id', $request->deckle_id)
            ->where('status', 1)
            ->get();
    
        // 3️⃣ Check reel usage
        foreach ($item_reel as $reel) {
            $reel->is_used = ItemSizeStock::where([
                'deckle_id' => $reel->deckle_id,
                'reel_no'   => $reel->reel_no,
                'size'      => $reel->size,
                'item_id'   => $reel->quality_id,
                'status'    => 0
            ])->exists();
        }
    
        // 4️⃣ Next Reel No
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
        $deckle_arr = DeckleProcess::where('company_id',$request->company_id)
                                    ->whereBetween('start_time_stamp', [$from_date, $to_date])
                                    ->pluck('id');
        $reel_no = ItemSizeStock::where('company_id', $request->company_id)
            ->where('deckle_id', '!=', 0)
            ->whereIn('deckle_id',$deckle_arr)
            ->max('reel_no');
    
        $next_reel_no = $reel_no ? $reel_no + 1 : 1;
    
        // 5️⃣ Final API Response
        return response()->json([
            'status' => true,
            'data' => [
                'pop_roll' => [
                    'id'         => $start_deckle->id,
                    'deckle_no'  => $start_deckle->deckle_no,
                    'start_time' => $start_deckle->start_time_stamp,
                    'end_time'   => $start_deckle->end_time_stamp
                ],
                'qualities' => $start_deckle->quality,
                'reels'     => $item_reel,
                'next_reel_no' => $next_reel_no
            ]
        ]);
    }

    
    public function updatePopRollReel(Request $request)
{
   

        /* -------------------------------------------------
         | 1️⃣ FETCH EXISTING ACTIVE DECKLE ITEM IDS
         -------------------------------------------------*/
        $item_id_arr = DeckleItem::where('deckle_items.deckle_id', $request->pop_roll_id)
            ->join('item_size_stocks', 'item_size_stocks.reel_no', '=', 'deckle_items.reel_no')
            ->where('deckle_items.status', 1)
            ->where('item_size_stocks.status', 1)
            ->where('item_size_stocks.company_id', $request->company_id)
            ->where('item_size_stocks.deckle_id',$request->pop_roll_id)
            ->where('item_size_stocks.deckle_id','!=','0')
            ->pluck('deckle_items.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        /* -------------------------------------------------
         | 2️⃣ DELETE EXISTING STOCK (KEEP CREATED_AT)
         -------------------------------------------------*/
        $sizeStock = ItemSizeStock::where('deckle_id', $request->pop_roll_id)->first();

        ItemSizeStock::where('deckle_id', $request->pop_roll_id)
            ->where('status', 1)
            ->delete();

        $quality_weight_arr = [];

        /* -------------------------------------------------
         | 3️⃣ PROCESS POP ROLL → REELS
         -------------------------------------------------*/
        foreach ($request->pop_rolls as $value) {
            foreach ($value['reels'] as $v) {

                if (!empty($v['deleted']) && $v['deleted'] == 1) {
                    continue;
                }

                /* -------------------------------
                 | QUALITY PRESENT
                 --------------------------------*/
                if (!empty($v['quality_id'])) {

                    // Check if reel already sold
                    $size_sale_status = ItemSizeStock::where('status', 0)
                        ->where('reel_no', $v['reel_no'])
                        ->where('item_id', $v['quality_id'])
                        ->where('company_id', $request->company_id)
                        ->where('deckle_id',$request->pop_roll_id)
                        ->first();

                    /* ---- UPDATE EXISTING ---- */
                    if (!empty($v['row_id'])) {

                        $deckleItem = DeckleItem::find($v['row_id']);

                        if (
                            $deckleItem &&
                            (!isset($v['sold']) || $v['sold'] != 1) &&
                            !$size_sale_status
                        ) {
                            $deckleItem->quality_id     = $v['quality_id'];
                            $deckleItem->quality_row_id= $v['quality_row_id'];
                            $deckleItem->size           = $v['size'];
                            $deckleItem->reel_no        = $v['reel_no'];
                            $deckleItem->weight         = $v['weight'];
                            $deckleItem->bf             = $v['bf'];
                            $deckleItem->gsm            = $v['gsm'];
                            $deckleItem->unit           = $v['unit'];
                            $deckleItem->updated_at     = Carbon::now();
                            $deckleItem->save();
                       }

                        if (($k = array_search($v['row_id'], $item_id_arr)) !== false) {
                            unset($item_id_arr[$k]);
                        }

                    } 
                    /* ---- CREATE NEW ---- */
                    else {
                        $deckle = new DeckleItem;
                        $deckle->deckle_id      = $request->pop_roll_id;
                        $deckle->quality_id     = $v['quality_id'];
                        $deckle->quality_row_id = $v['quality_row_id'];
                        $deckle->size           = $v['size'];
                        $deckle->reel_no        = $v['reel_no'];
                        $deckle->weight         = $v['weight'];
                        $deckle->bf             = $v['bf'];
                        $deckle->gsm            = $v['gsm'];
                        $deckle->unit           = $v['unit'];
                        $deckle->company_id     = $request->company_id;
                        $deckle->created_by     = $request->user_id;
                        $deckle->created_at     = Carbon::now();
                        $deckle->save();
                    }

                    /* ---- RECREATE STOCK ---- */
                    if (
                        (!isset($v['sold']) || $v['sold'] != 1) &&
                        !$size_sale_status
                    ) {
                        $stock = new ItemSizeStock;
                        $stock->item_id        = $v['quality_id'];
                        $stock->quality_row_id = $v['quality_row_id'];
                        $stock->weight         = $v['weight'];
                        $stock->reel_no        = $v['reel_no'];
                        $stock->size           = $v['size'];
                        $stock->bf             = $v['bf'];
                        $stock->gsm            = $v['gsm'];
                        $stock->unit           = $v['unit'];
                        $stock->deckle_id      = $request->pop_roll_id;
                        $stock->company_id     = $request->company_id;
                        $stock->created_by     = $request->user_id;
                        $stock->created_at     = $sizeStock->created_at ?? Carbon::now();
                        $stock->save();
                    }

                    /* ---- WEIGHT FOR LEDGER ---- */
                    $quality_weight_arr[$v['quality_id']] =
                        ($quality_weight_arr[$v['quality_id']] ?? 0) + $v['weight'];
                }

                /* -------------------------------
                 | QUALITY EMPTY BUT SOLD
                 --------------------------------*/
                else if (!empty($v['sold']) && $v['sold'] == 1) {

                    $ditem = DeckleItem::find($v['row_id']);
                    if ($ditem) {
                        $quality_weight_arr[$ditem->quality_id] =
                            ($quality_weight_arr[$ditem->quality_id] ?? 0) + $v['weight'];
                    }

                    if (($k = array_search($v['row_id'], $item_id_arr)) !== false) {
                        unset($item_id_arr[$k]);
                    }
                }
            }
        }

        /* -------------------------------------------------
         | 4️⃣ RESET LEDGER & AVERAGE
         -------------------------------------------------*/
        ItemLedger::where('deckle_id', $request->pop_roll_id)->delete();
        ItemAverageDetail::where('deckle_id', $request->pop_roll_id)->delete();

        if (!empty($quality_weight_arr)) {

            $companyData = Companies::find($request->company_id);
             if($companyData->gst_config_type == "single_gst"){
                $GstSettings = DB::table('gst_settings')
                                ->where(['company_id' => $request->company_id, 'gst_type' => "single_gst"])
                                ->get();
                $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                ->where(['delete' => '0', 'company_id' => $request->company_id,'gst_setting_id'=>$GstSettings[0]->id])
                                ->get();
                if(count($branch)>0){
                    $GstSettings = $GstSettings->merge($branch);
                }
                
            }else if($companyData->gst_config_type == "multiple_gst"){
                $GstSettings = DB::table('gst_settings_multiple')
                                ->select('id','gst_no','mat_center','series','invoice_start_from')
                                ->where(['company_id' => $request->company_id, 'gst_type' => "multiple_gst"])
                                ->get();
                foreach ($GstSettings as $key => $value) {
                    $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                                ->where(['delete' => '0', 'company_id' => $request->company_id,'gst_setting_multiple_id'=>$value->id])
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
                $item_ledger->company_id = $request->company_id;
                $item_ledger->source = 7;
                $item_ledger->deckle_id = $request->pop_roll_id;
                $item_ledger->created_by = $request->user_id;
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
                $average_detail->company_id = $request->company_id;
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
              
                CommonHelper::RewriteItemAverageByItemApi(date('Y-m-d',strtotime($deckle->reel_generated_at)),$key,$series,$request->company_id);
                  
            }
            
        }

       
        /* -------------------------------------------------
         | 5️⃣ DEACTIVATE REMOVED REELS
         -------------------------------------------------*/
        if (!empty($item_id_arr)) {
            DeckleItem::whereIn('id', $item_id_arr)->update(['status' => 0]);
        }

        if (!empty($request->deleted_row_ids)) {
            DeckleItem::whereIn(
                'id',
                explode(',', $request->deleted_row_ids)
            )->update(['status' => 0]);
        }

     

        return response()->json([
            'status'  => true,
            'message' => 'Pop Roll Reel updated successfully'
        ]);


}

public function cancelPopRollReelApi(Request $request)
{
    $request->validate([
        'pop_roll_id' => 'required|integer'
    ]);

    DB::beginTransaction();

    try {

        /* -------------------------------------------------
         | 1️⃣ CHECK IF ANY REEL IS ALREADY USED / SOLD
         -------------------------------------------------*/
        $blockedStock = ItemSizeStock::where('deckle_id', $request->pop_roll_id)
                                    ->where('company_id',$request->company_id)
            ->where('status', 0)
            ->first();

        if ($blockedStock) {

            // if sale_id exists → SOLD
            if (!empty($blockedStock->sale_id)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Reel of the Pop Roll already sold.'
                ], 400);
            }

            // otherwise → CONSUMED
            return response()->json([
                'status'  => false,
                'message' => 'Reel already consumed.'
            ], 400);
        }

        /* -------------------------------------------------
         | 2️⃣ PROCEED WITH CANCELLATION
         -------------------------------------------------*/
        DeckleItem::where('deckle_id', $request->pop_roll_id)
                    ->where('company_id',$request->company_id)
            ->update(['status' => 0]);

        ItemSizeStock::where('deckle_id', $request->pop_roll_id)
                            ->where('company_id',$request->company_id)
                            ->delete();

        DeckleProcess::where('id', $request->pop_roll_id)
            ->update([
                'status' => 2,
                'reel_generated_at' => null
            ]);

        ItemLedger::where('deckle_id', $request->pop_roll_id)
                        ->where('company_id',$request->company_id)
                        ->delete();

        ItemAverageDetail::where('deckle_id', $request->pop_roll_id)
                            ->where('company_id',$request->company_id)
                            ->delete();

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Pop Roll cancelled successfully.'
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong while cancelling Pop Roll.',
            'error'   => $e->getMessage()
        ], 500);
    }
}


public function CancelCompletedDeckle(Request $request)
{
    $companyId = $request->company_id;
    $user_id = $request->user_id;
    $pop_roll_id = $request->pop_roll_id;
    DB::beginTransaction();

    try {
        // Only cancel if status = 2 (Completed)
        $updated = DeckleProcess::where('company_id', $companyId)
            ->where('id', $pop_roll_id)
            ->where('status', 2)
            ->update([
                'status'     => 5, // Cancelled
                'deleted_by' => $user_id,
                'deleted_at' => Carbon::now(),
            ]);

        if (!$updated) {
            DB::rollBack();
            
            return response()->json([
                'status'  => false,
                'message' => 'Cannot cancel: Already in process for reel cutting.',
                'error'   => 'Cannot cancel: Already in process for reel cutting.'
            ]);
        }

        DB::commit();
        return response()->json([
            'status'  => false,
            'message' => 'Cancelled successfully.',
            'error'   => ''
        ]);

    } catch (\Exception $e) {

        DB::rollBack();
        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'error'   => $e->getMessage()
        ]);
    }
}


public function editApi(Request $request)
{
    $company_id = $request->company_id;

    try {

        /* --------------------------------------
         | 1️⃣ FETCH DECKLE WITH QUALITY DETAILS
         --------------------------------------*/
        $deckle = DeckleProcess::with([
            'quality' => function ($q) {
                $q->join(
                    'manage_items',
                    'deckle_process_qualities.item_id',
                    '=',
                    'manage_items.id'
                )
                ->select(
                    'deckle_process_qualities.*',
                    'manage_items.name'
                );
            }
        ])
        ->where('company_id', $company_id)
        ->where('status', '!=', 5) // optional: skip deleted
        ->find($request->pop_roll_id);

        if (!$deckle) {
            return response()->json([
                'status'  => false,
                'message' => 'Deckle record not found'
            ], 404);
        }

        /* --------------------------------------
         | 2️⃣ FETCH PRODUCTION ITEMS
         --------------------------------------*/
        $items = ProductionItem::join(
                    'manage_items',
                    'production_items.item_id',
                    '=',
                    'manage_items.id'
                )
                ->select(
                    'production_items.id',
                    'manage_items.name',
                    'production_items.bf',
                    'production_items.gsm',
                    'production_items.speed',
                    'manage_items.id as item_id'
                )
                ->where('production_items.company_id', $company_id)
                ->where('production_items.status', 1)
                ->orderBy('manage_items.name')
                ->get();

        /* --------------------------------------
         | 3️⃣ SUCCESS RESPONSE
         --------------------------------------*/
        return response()->json([
            'status' => true,
            'data'   => [
                'deckle' => $deckle,
                'items'  => $items
            ]
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function updateApi(Request $request)
{
    $company_id = $request->company_id;
    $user_id    = $request->user_id;
    $id = $request->pop_roll_id;
    DB::beginTransaction();

    try {

        /* --------------------------------------
         | 1️⃣ DELETE OLD QUALITIES
         --------------------------------------*/
        DeckleProcessQuality::where('parent_id', $id)
            ->where('company_id', $company_id)
            ->delete();

        /* --------------------------------------
         | 2️⃣ MERGE OLD + NEW QUALITIES
         --------------------------------------*/
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

        /* --------------------------------------
         | 3️⃣ UPDATE DECKLE PROCESS
         --------------------------------------*/
        $updated = DeckleProcess::where('id', $id)
            ->where('company_id', $company_id)
            ->update([
                'start_time_stamp' => date('Y-m-d H:i:s', strtotime($request->start_time)),
                'end_time_stamp'   => date('Y-m-d H:i:s', strtotime($request->end_time)),
                'updated_at'       => Carbon::now(),
            ]);

        if (!$updated) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Deckle process not found or not updated'
            ], 404);
        }

        /* --------------------------------------
         | 4️⃣ RE-INSERT QUALITIES
         --------------------------------------*/
        foreach ($allQualities as $data) {

            // ⛔ Skip if item not selected
            if (empty($data['item_id'])) {
                continue;
            }

            // 🔍 Find ProductionItem (if exists)
           

            // 🎯 Resolve manage_items.id
            $manage_item_id = $data['item_id'];

            // 🎯 Resolve BF & GSM
            $bf  = $data['bf']  ?? ($productionItem->bf  ?? null);
            $gsm = $data['gsm'] ?? ($productionItem->gsm ?? null);

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
            $deckle->started_by = $user_id;
            $deckle->created_at = Carbon::now();
            $deckle->save();
        }

        DB::commit();

        /* --------------------------------------
         | 5️⃣ SUCCESS RESPONSE
         --------------------------------------*/
        return response()->json([
            'status'  => true,
            'message' => 'Deckle updated successfully'
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage()
        ], 500);
    }
}


}
