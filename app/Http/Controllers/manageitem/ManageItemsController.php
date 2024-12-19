<?php

namespace App\Http\Controllers\manageitem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\ItemGroups;
use App\Models\Units;
use App\Models\ItemLedger;
use App\Models\StockJournal;
use App\Models\StockJournalDetail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
class ManageItemsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $com_id = Session::get('user_company_id');
        $manageitems = DB::table('manage_items')
        ->select('units.name as unit_name', 'item_groups.group_name','manage_items.*')
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->get();
        //$manageitems = ManageItems::where('company_id', $com_id)->get();
        return view('manageitem/accountManageItem')->with('manageitems', $manageitems);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $com_id = Session::get('user_company_id');
        $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
        $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        return view('manageitem/addAccountManageItem')->with('accountunit', $accountunit)->with('itemGroups', $itemGroups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $items = new ManageItems;
      $items->company_id =  Session::get('user_company_id');
      $items->name = $request->input('name');
      $items->p_name = $request->input('p_name');
      $items->g_name = $request->input('g_name');
      $items->u_name = $request->input('u_name');
      $items->hsn_code = $request->input('hsn_code');
      $items->gst_rate = $request->input('gst_rate');
      $items->opening_balance_qty = $request->input('opening_balance_qty');
      $items->opening_balance_qt_type = $request->input('opening_balance_qt_type');
      $items->opening_balance = $request->input('opening_balance');
      $items->opening_balance_type = $request->input('opening_balance_type');      
      $items->status = $request->input('status');
      $items->save();
      if ($items->id) {
         if(!empty($request->input('opening_balance_qty')) && !empty($request->input('opening_balance_qt_type'))){
            $ledger = new ItemLedger();
            $ledger->item_id = $items->id;
            if($request->input('opening_balance_qt_type')=='Debit'){
               $ledger->in_weight = $request->input('opening_balance_qty');
            }else if($request->input('opening_balance_qt_type')=='Credit'){
               $ledger->out_weight = $request->input('opening_balance_qty');
            }
            $ledger->total_price = $request->input('opening_balance');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->source = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $default_fy = explode("-",Session::get('default_fy'));
            $txn_date = $default_fy[0]."-04-01";
            $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
            $ledger->save();
         }
         return redirect('account-manage-item')->withSuccess('Items added successfully!');
      }else{
         $this->failedMessage();
      }
   }

    public function edit($id)
    {

        $manageitems = ManageItems::find($id);
        $com_id = Session::get('user_company_id');
        $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
        $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        return view('manageitem/editAccountManageItems')->with('accountunit', $accountunit)->with('itemGroups', $itemGroups)->with('manageitems', $manageitems);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }        
      $items =  ManageItems::find($request->mangeitem_id);
      $items->name = $request->input('name');
      $items->p_name = $request->input('p_name');
      $items->g_name = $request->input('g_name');
      $items->u_name = $request->input('u_name');
      $items->opening_balance_qty = $request->input('opening_balance_qty');
      $items->opening_balance_qt_type = $request->input('opening_balance_qt_type');
      $items->opening_balance = $request->input('opening_balance');
      $items->opening_balance_type = $request->input('opening_balance_type');
      $items->gst_rate = $request->input('gst_rate');
      $items->hsn_code = $request->input('hsn_code');
      $items->status = $request->input('status');
      $items->updated_at = Carbon::now();
      $items->update();
      if(!empty($request->input('opening_balance_qty')) && !empty($request->input('opening_balance_qt_type'))){
         $check = ItemLedger::where('item_id',$request->mangeitem_id)
                                 ->where('source','-1')
                                 ->first();
         if($check){          
            $ledger = ItemLedger::find($check->id);
            if($request->input('opening_balance_qt_type')=='Debit'){
               $ledger->in_weight = $request->input('opening_balance_qty');
               $ledger->out_weight = "";
            }else if($request->input('opening_balance_qt_type')=='Credit'){
               $ledger->out_weight = $request->input('opening_balance_qty');
               $ledger->in_weight = "";
            }
            $ledger->updated_by = Session::get('user_id');
            $ledger->updated_at = date('d-m-Y H:i:s');
            $ledger->save();
         }else{
            $ledger = new ItemLedger();
            $ledger->item_id = $request->mangeitem_id;
            if($request->input('opening_balance_qt_type')=='Debit'){
               $ledger->in_weight = $request->input('opening_balance_qty');
            }else if($request->input('opening_balance_qt_type')=='Credit'){
               $ledger->out_weight = $request->input('opening_balance_qty');
            }
            $ledger->total_price = $request->input('opening_balance');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->source = -1;
            $default_fy = explode("-",Session::get('default_fy'));
            $txn_date = $default_fy[0]."-04-01";
            $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
      }else{
         $check = ItemLedger::where('item_id',$request->mangeitem_id)
                                 ->where('entry_type','-1')
                                 ->first();
         if($check){
            ItemLedger::where('source','-1')
                           ->where('item_id',$request->mangeitem_id)
                           ->delete();
         }
      }
      return redirect('account-manage-item')->withSuccess('item updated successfully!');
   }
   public function delete(Request $request){
      $account =  ManageItems::find($request->heading_id);
      $account->delete = '1';
      $account->deleted_at = Carbon::now();
      $account->update();
      if($account) {
         return redirect('account-manage-item')->withSuccess('Item deleted successfully!');
      }
   }
    /**
     * Generates failed response and message.
     */
   public function failedMessage(){
      return response()->json([
         'code' => 422,
         'message' => 'Something went wrong, please try again after some time.',
      ]);
   }
   public function stockJournal(Request $request){
      Session::put('redirect_url','');
      $from_date = date('Y-m')."-01";  
      $to_date = date("Y-m-t");
      if($request->input('from_date')!="" && $request->input('to_date')!=""){
         $from_date = $request->input('from_date');
         $to_date = $request->input('to_date');
      }
      $financial_year = Session::get('default_fy');      
      $y =  explode("-",$financial_year);
      $from = $y[0];
      $from = DateTime::createFromFormat('y', $from);
      $from = $from->format('Y');
      $to = $y[1];
      $to = DateTime::createFromFormat('y', $to);
      $to = $to->format('Y');
      $month_arr = array($from.'-04',$from.'-05',$from.'-06',$from.'-07',$from.'-08',$from.'-09',$from.'-10',$from.'-11',$from.'-12',$to.'-01',$to.'-02',$to.'-03');
      $journal = DB::select(DB::raw("SELECT stock_journal_detail.parent_id as id,journal_date,consume_weight,new_weight,manage_items.name,new.name as new_item,consume_price,consume_amount,new_price,new_amount FROM stock_journal_detail left join manage_items on stock_journal_detail.consume_item=manage_items.id left join manage_items as new on stock_journal_detail.new_item=new.id WHERE stock_journal_detail.status=1 and stock_journal_detail.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(journal_date, '%Y-%m-%d')>=STR_TO_DATE('".$from_date."', '%Y-%m-%d') and STR_TO_DATE(journal_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d')"));
      return view('manageitem/stock-journal')->with('journals', $journal)->with('month_arr', $month_arr);
   }
   public function addStockJournal(){
      $financial_year = Session::get('default_fy');
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*'])
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
      return view('manageitem/add-stock-journal')->with('items', $items)->with('date', $bill_date);
   }
   public function saveStockJournal(Request $request){
      $financial_year = Session::get('default_fy');
      $date = $request->input('date');
      $narration = $request->input('narration');
      
      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');

      $generated_item = $request->input('generated_item');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');

      $stockjournal = new StockJournal;
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->company_id = Session::get('user_company_id');
      $stockjournal->created_by = Session::get('user_id');
      $stockjournal->financial_year = $financial_year;
      $stockjournal->created_at = date('d-m-Y H:i:s');
      if($stockjournal->save()){         
         foreach ($consume_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
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
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->new_item = $generated_item[$key];
            $stockjournaldetail->new_weight = $generated_weight[$key];
            $stockjournaldetail->new_price = $generated_price[$key];
            $stockjournaldetail->new_amount = $generated_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->in_weight = $generated_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $generated_price[$key];
            $item_ledger->total_price = $generated_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
         }
         return redirect('stock-journal')->withSuccess('Stock Journal Added Successfully!'); 
      }
   }
   public function deleteStockJournal(Request $request){
      $id = $request->input('del_id');
      $delete = StockJournal::where('id',$id)
                  ->delete();
      if($delete){
         StockJournalDetail::where('parent_id',$id)->delete();
         ItemLedger::where('source',3)
                     ->where('source_id',$id)
                     ->delete();
         return redirect('stock-journal')->withSuccess('Stock Journal Deleted Successfully!');          
      }      
   }  
   public function editStockJournal(Request $request,$id){
      $journal = StockJournal::find($id); 
      $journal_details = StockJournalDetail::where('parent_id',$id)->get();   
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->where('manage_items.status','1')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();     
      return view('manageitem/edit-stock-journal')->with('journal', $journal)->with('items', $items)->with('journal_details', $journal_details);     
   }
   public function updateStockJournal(Request $request){
      $date = $request->input('date');
      $narration = $request->input('narration');
      
      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');

      $generated_item = $request->input('generated_item');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');

      $stockjournal = StockJournal::find($request->input('edit_id'));
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->updated_by = Session::get('user_id');
      $stockjournal->updated_at = date('d-m-Y H:i:s');
      if($stockjournal->save()){  
         StockJournalDetail::where('parent_id',$request->input('edit_id'))->delete();
         ItemLedger::where('source',3)->where('source_id',$request->input('edit_id'))->delete();
         foreach ($consume_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
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
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->new_item = $generated_item[$key];
            $stockjournaldetail->new_weight = $generated_weight[$key];
            $stockjournaldetail->new_price = $generated_price[$key];
            $stockjournaldetail->new_amount = $generated_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->in_weight = $generated_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $generated_price[$key];
            $item_ledger->total_price = $generated_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
         }
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('stock-journal')->withSuccess('Stock Journal Updated Successfully!');
         }
          
      }
   }
}
