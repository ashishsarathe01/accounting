<?php

namespace App\Http\Controllers\itemgroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemGroups;
use App\Models\ItemGroupParameterList;
use App\Models\ItemGroupParameterPredefinedValue;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use Gate;
class ItemGroupsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(){
      Gate::authorize('view-module', 7);
      $com_id = Session::get('user_company_id');
      $itemgroups = ItemGroups::where('company_id', $com_id)->where('delete', '=', '0')->get();
      return view('itemgroup/accountItemGroup')->with('itemgroups', $itemgroups);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         Gate::authorize('view-module', 78);
        return view('itemgroup/addAccountItemGroup');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      // echo "<pre>";
      // print_r($request->all());die;
      Gate::authorize('view-module', 78);
      $validator = Validator::make($request->all(), [
         'group_name' => 'required|string',
      ],[
         'group_name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      
      $items = new ItemGroups;
      $items->company_id =  Session::get('user_company_id');
      $items->group_name = $request->input('group_name');
      $items->parameterized_stock_status = $request->input('parameterized_stock_status');
      $items->config_status = $request->input('config_status');
      $items->no_of_parameter = $request->input('no_of_parameter');      
      if($request->input('alternative_qty')){         
         $items->alternative_qty = $request->input('alternative_qty');
      }    
      $items->status = $request->input('status');
      $items->save();
      if($items->id){
         if($request->input('parameterized_stock_status')==1 && $request['parameter_list'] && count($request['parameter_list'])>0){
            foreach ($request['parameter_list'] as $key => $value){
               $item_parameter_list = new ItemGroupParameterList;
               $item_parameter_list->parent_id = $items->id;
               $item_parameter_list->paremeter_name = $value;
               $item_parameter_list->parameter_type = $request['parameter_type_'.$key];
               if($request['parameter_conversion_factor_'.$key]){
                  $item_parameter_list->conversion_factor = $request['parameter_conversion_factor_'.$key];
               }               
               if($request['alternative_unit_'.$key]){
                  $item_parameter_list->alternative_unit = $request['alternative_unit_'.$key];
               }
               $item_parameter_list->company_id = Session::get('user_company_id');
               if($item_parameter_list->save()){
                  if($request['parameter_type_'.$key]=="PREDEFINED"){
                     $defined_value = explode(",", $request['defined_value_'.$key]);
                     $defined_value_alias = explode(",", $request['defined_value_alias_'.$key]);
                     foreach ($defined_value as $k1 => $v1) {
                        $item_parameter_predefined_value = new ItemGroupParameterPredefinedValue;
                        $item_parameter_predefined_value->super_parent_id = $items->id;
                        $item_parameter_predefined_value->predefined_value = $v1;
                        if(!empty($defined_value_alias[$k1]) && $defined_value_alias[$k1]!='null'){
                           $item_parameter_predefined_value->predefined_value_alias = $defined_value_alias[$k1];
                        }
                        $item_parameter_predefined_value->parent_id = $item_parameter_list->id;
                        $item_parameter_predefined_value->com_id = Session::get('user_company_id');
                        $item_parameter_predefined_value->save();
                     }
                  }
               }
            }
         }
         return redirect('account-item-group')->withSuccess('Items group added successfully!');
      }else{
         $this->failedMessage();
      }
   }

   public function edit($id){
      Gate::authorize('view-module', 49);
      $itemsgroup = ItemGroups::with(['parameters.predefinedValue'])->find($id);
      return view('itemgroup/editAccountItemsGroup')->with('itemsgroup', $itemsgroup);
   }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      // echo "<pre>";
      // print_r($request->all());die;
      Gate::authorize('view-module', 49);
      $validator = Validator::make($request->all(), [
         'group_name' => 'required|string',

      ], [
         'group_name.required' => 'Name is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }        
      $items =  ItemGroups::find($request->itemgroup_id);
      $items->group_name = $request->input('group_name');
      $items->parameterized_stock_status = $request->input('parameterized_stock_status');
      $items->config_status = $request->input('config_status');
      $items->no_of_parameter = $request->input('no_of_parameter');
      $items->alternative_qty = $request->input('alternative_qty');
      $items->status = $request->input('status');
      $items->updated_at = Carbon::now();
      if($items->update()){
         ItemGroupParameterList::where('parent_id',$request->itemgroup_id)->update(['status'=>0]);
         ItemGroupParameterPredefinedValue::where('super_parent_id',$request->itemgroup_id)->update(['status'=>0]);
         if($request->input('parameterized_stock_status')==1 && $request['parameter_list'] && count($request['parameter_list'])>0 ){
            foreach ($request['parameter_list'] as $key => $value){
               $item_parameter_list = new ItemGroupParameterList;
               $item_parameter_list->parent_id = $items->id;
               $item_parameter_list->paremeter_name = $value;
               $item_parameter_list->parameter_type = $request['parameter_type_'.$key];
               if($request['parameter_conversion_factor_'.$key]){
                  $item_parameter_list->conversion_factor = $request['parameter_conversion_factor_'.$key];
               }               
               if($request['alternative_unit_'.$key]){
                  $item_parameter_list->alternative_unit = $request['alternative_unit_'.$key];
               }
               $item_parameter_list->company_id = Session::get('user_company_id');
               if($item_parameter_list->save()){                     
                  if($request['parameter_type_'.$key]=="PREDEFINED"){
                     $defined_value = explode(",", $request['defined_value_'.$key]);
                     $defined_value_alias = explode(",", $request['defined_value_alias_'.$key]);
                     foreach ($defined_value as $k1 => $v1) {
                        $item_parameter_predefined_value = new ItemGroupParameterPredefinedValue;
                        $item_parameter_predefined_value->super_parent_id = $items->id;
                        $item_parameter_predefined_value->predefined_value = $v1;
                        if(!empty($defined_value_alias[$k1]) && $defined_value_alias[$k1]!='null'){
                           $item_parameter_predefined_value->predefined_value_alias = $defined_value_alias[$k1];
                        }
                        $item_parameter_predefined_value->parent_id = $item_parameter_list->id;
                        $item_parameter_predefined_value->com_id = Session::get('user_company_id');
                        $item_parameter_predefined_value->save();
                     }
                  }
               }
            }
         }
      }

      return redirect('account-item-group')->withSuccess('group item updated successfully!');
   }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
      Gate::authorize('view-module', 50);
        $items =  ItemGroups::find($request->item_id);
        $items->delete = '1';
        $items->deleted_at = Carbon::now();
        $items->update();
        if ($items) {
            return redirect('account-item-group')->withSuccess('Group item deleted successfully!');
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return redirect('account-item-group')->withError('Something went wrong, please try again after some time.');
    }
}
