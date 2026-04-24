<?php
namespace App\Http\Controllers\manageitem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\ItemGroups;
use App\Models\Units;
use App\Models\item_gst_rate;
use App\Models\ItemGstRate;
use App\Models\ItemLedger;
use App\Models\StockJournal;
use App\Models\StockJournalDetail;
use App\Models\Companies;
use App\Models\VoucherSeriesConfiguration;
use App\Models\GstBranch;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemAverageDetail;
use App\Models\SubItem;
use App\Models\ActivityLog;
use App\Models\MerchantModuleMapping;
use App\Models\ProductionItem;
use App\Models\ItemSizeStock;
use App\Models\DeckleProcess;
use App\Models\Consumption;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Gate;
use Yajra\DataTables\Facades\DataTables;
class ManageItemsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index1(Request $request){
      Gate::authorize('view-module', 8);
      $com_id = Session::get('user_company_id');
      $status = ['1','0'];
      if($request->filter){
         if($request->filter=="Disable"){
            $status = ['0'];
         }else if($request->filter=="Enable"){
            $status = ['1'];
         }
      }
      if($request->filter=="InComplete"){
         $manageitems = DB::table('manage_items')
        ->select('units.name as unit_name', 'item_groups.group_name','manage_items.*','manage_items.gst_rate as gstrate')
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->where(function($q){
            $q->orWhere('manage_items.u_name', '');
            $q->orWhere('manage_items.hsn_code', '');
            $q->orWhere('manage_items.gst_rate', '');
            $q->orWhere('manage_items.gst_rate',null);
            $q->orWhere('manage_items.u_name',null);
            $q->orWhere('manage_items.hsn_code',null);
         })
        ->whereIn('manage_items.status',$status)
        ->get();
      }else{
         $manageitems = DB::table('manage_items')
        ->select('units.name as unit_name', 'item_groups.group_name','manage_items.*','manage_items.gst_rate as gstrate')
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->whereIn('manage_items.status',$status)
        ->get();
      }
      $manageitems = $manageitems->map(function ($item, $key) {
         $item->series_open = ItemBalanceBySeries::where('item_id',$item->id)->get();
         $item->item_delete_btn_view = 1;
         $exist = ItemLedger::where('item_id', $item->id)
                           ->where('source', '!=', '-1')
                           ->where('delete_status', '=', '0')
                           ->first();
         if($exist){
            $item->item_delete_btn_view = 0;
         }
         return $item;
     });  
      $comp = Companies::select('user_id')->where('id',Session::get('user_company_id'))->first();
      $purchase_management_module_status = MerchantModuleMapping::where('module_id',2)->where('company_id', Session()->get('user_company_id'))->where('merchant_id',$comp->user_id)->first();
      $purchase_management_module_status = $purchase_management_module_status ? 1 : 0;
      return view('manageitem/accountManageItem')->with('manageitems', $manageitems)->with('purchase_management_module_status',$purchase_management_module_status);
   }
      public function index(Request $request)
    {
        Gate::authorize('view-module', 8);
    
        $comp = Companies::select('user_id')
            ->where('id', Session::get('user_company_id'))
            ->first();
    
        $purchase_management_module_status = MerchantModuleMapping::where('module_id',2)
            ->where('merchant_id',$comp->user_id)
            ->where('company_id', Session()->get('user_company_id'))
            ->first();
    
        $purchase_management_module_status = $purchase_management_module_status ? 1 : 0;
    
        return view('manageitem/accountManageItem')
            ->with('purchase_management_module_status',$purchase_management_module_status);
    }
public function datatable(Request $request)
{
   
    $com_id = Session::get('user_company_id');

    $status = ['1','0'];

    if ($request->filter === 'Enable') {
        $status = ['1'];
    } elseif ($request->filter === 'Disable') {
        $status = ['0'];
    }

    $query = DB::table('manage_items')
        ->select(
            'manage_items.id',
            'manage_items.name',
            'manage_items.hsn_code',
            'manage_items.gst_rate',
            'manage_items.status',
            'units.name as unit_name',
            'item_groups.group_name'
        )
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->whereIn('manage_items.status', $status);

        if ($request->filter === 'InComplete') {
    
            $query->where(function($q){
                $q->orWhere('manage_items.u_name', '')
                  ->orWhereNull('manage_items.u_name')
                  ->orWhere('manage_items.hsn_code', '')
                  ->orWhereNull('manage_items.hsn_code')
                  ->orWhere('manage_items.gst_rate', '')
                  ->orWhereNull('manage_items.gst_rate');
            });
    
        }

    return DataTables::of($query)

        ->addColumn('opening_stock', function ($row) {
            $series = ItemBalanceBySeries::where('item_id',$row->id)->get();

            if ($series->count() == 0) {
                return '';
            }

            $html = '<table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Series</th>
                                <th>Amount</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($series as $s) {

                $html .= '<tr>
                            <td>'.$s->series.'</td>
                            <td style="text-align:right;">'.$s->opening_amount.'</td>
                            <td style="text-align:right;">'.$s->opening_quantity.'</td>
                        </tr>';

            }

            $html .= '</tbody></table>';

            return $html;
        })

        ->addColumn('status_label', function ($row) {

            if ($row->status == 1) {
                return '<span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">Enable</span>';
            }

            return '<span class="bg-danger-opacity-16 border-radius-4 text-danger py-1 px-2 fw-bold">Disable</span>';
        })

        ->addColumn('action', function ($row) {

            $html = '';

            if (Gate::allows('action-module', 51)) {

                $html .= '<a href="'.url('account-manage-item/'.$row->id.'/edit').'">
                            <img src="'.asset('public/assets/imgs/edit-icon.svg').'" title="Edit">
                          </a>';
            }

            $exist = ItemLedger::where('item_id', $row->id)
                ->where('source', '!=', '-1')
                ->where('delete_status', '0')
                ->first();

            if (!$exist && Gate::allows('action-module', 52)) {

                $html .= '<button type="button" class="border-0 bg-transparent delete_partner" data-id="'.$row->id.'">
                            <img src="'.asset('public/assets/imgs/delete-icon.svg').'" title="Delete">
                          </button>';
            }

            return $html;
        })

        ->rawColumns(['opening_stock','status_label','action'])

        ->make(true);
}
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){   
      Gate::authorize('view-module', 79);   
      $com_id = Session::get('user_company_id');
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
      $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
      $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        return view('manageitem/addAccountManageItem')->with('accountunit', $accountunit)->with('itemGroups', $itemGroups)->with('series',$series);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      Gate::authorize('view-module', 79);
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      // echo "<pre>";
      // print_r($request->all());die;

      $items = new ManageItems;
      $items->company_id =  Session::get('user_company_id');
      $items->name = $request->input('name');
      $items->p_name = $request->input('p_name');
      $items->g_name = $request->input('g_name');
      $items->u_name = $request->input('u_name');
      $items->hsn_code = $request->input('hsn_code');
      $items->item_type = $request->input('item_type');
      $items->status = $request->input('status');
      $items->section = $request->input('section');
      $items->rate_of_tcs = $request->input('rate_of_tcs');
      $items->gst_rate = $request->input('gst_rate');
      $items->created_by = Session::get('user_id');
      $items->created_at = Carbon::now();
      if($items->save()) {
         $gst_rate = new ItemGstRate;
         $gst_rate->item_id = $items->id;
         $gst_rate->gst_rate = $request->input('gst_rate');
         $gst_rate->item_type = $request->input('item_type');
         $gst_rate->comp_id = Session::get('user_company_id');
         $gst_rate->effective_from = $request->input('gst_rate_effective_date');
         $gst_rate->created_by = Session::get('user_id');
         $gst_rate->created_at = Carbon::now();
         $gst_rate->save();
         $series = $request->input('series');
         $opening_amount = $request->input('opening_amount');
         $opening_qty = $request->input('opening_qty');
         $opening_balance_type = $request->input('opening_balance_type');
         foreach ($series as $key => $value) {
            $opening_amount[$key] = trim(str_replace(",","",$opening_amount[$key]));
            $opening_qty[$key] = trim(str_replace(",","",$opening_qty[$key]));
            if(!empty($opening_amount[$key]) && !empty($opening_qty[$key])){
               $series_balance = new ItemBalanceBySeries;
               $series_balance->item_id = $items->id;
               $series_balance->series = $value;
               $series_balance->opening_amount = $opening_amount[$key];
               $series_balance->opening_quantity = $opening_qty[$key];
               $series_balance->type = $opening_balance_type[$key];
               $series_balance->company_id = Session::get('user_company_id');
               $series_balance->created_at =  Carbon::now();
               $series_balance->save();
               //Add In Item Ledger
               $ledger = new ItemLedger();
               $ledger->item_id = $items->id;
               if($opening_balance_type[$key]=='Debit'){
                  $ledger->in_weight = $opening_qty[$key];
               }else if($opening_balance_type[$key]=='Credit'){
                  $ledger->out_weight = $opening_qty[$key];
               }
               $ledger->series_no = $value;
               $ledger->total_price = $opening_amount[$key];
               $ledger->company_id = Session::get('user_company_id');
               $ledger->source = -1;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $default_fy = explode("-",Session::get('default_fy'));
               $txn_date = $default_fy[0]."-04-01";
               $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
               $ledger->save();
            }
         }
         if ($request->ajax()) {
            // 🔥 fetch unit short name (KG / NOS / etc)
            $unitName = \DB::table('units')
               ->where('id', $items->u_name)
               ->value('s_name');

            return response()->json([
               'status'  => true,
               'message' => 'Item added successfully',
               'item'    => [
                     'id'    => $items->id,
                     'name'  => $items->name,

                     // ✅ REQUIRED FOR SALE SCREEN
                     'u_name' => $items->u_name,        // unit ID
                     'unit'   => $unitName,              // unit text
                     'gst_rate' => $items->gst_rate,

                     // safe defaults (used in sale JS)
                     'parameterized_stock_status' => 0,
                     'config_status' => 0,
                     'group_id' => $items->g_name
               ]
            ]);
         }
         return redirect('account-manage-item')->withSuccess('Items added successfully!');
      }else{
         $this->failedMessage();
      }
   }
   public function edit($id){
      Gate::authorize('view-module', 51);
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
        $item_gst_rate = ItemGstRate::select('effective_from')
                              ->where('item_id',$id)
                              ->where('gst_rate', $manageitems->gst_rate)
                              ->orderBy('id','desc')
                              ->first();
         $manageitems->effective_from = $item_gst_rate->effective_from;
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
        $com_id = Session::get('user_company_id');
        $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
        $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        $production_item = DB::table('production_items')
                                ->where('item_id', $id)
                                ->where('status', 1)
                                ->where('company_id', $com_id)
                                ->exists();

        return view('manageitem/editAccountManageItems')->with('production_item',$production_item)->with('accountunit', $accountunit)->with('itemGroups', $itemGroups)->with('manageitems', $manageitems)->with('series',$series);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      Gate::authorize('view-module', 51);
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
      $items->item_type = $request->input('item_type');
      $items->hsn_code = $request->input('hsn_code');
      $items->status = $request->input('status');
      $items->gst_rate = $request->input('gst_rate');
      $items->updated_at = Carbon::now();
      $items->updated_by = Session::get('user_id');
      if($items->update()){
         $gst_rate = ItemGstRate::where('item_id',$request->mangeitem_id)
                                 ->where('gst_rate',$request->input('gst_rate'))
                                 ->where('effective_from',$request->input('gst_rate_effective_date'))
                                 ->first();
         if(!$gst_rate){
            $item_gst_rate = new ItemGstRate;
            $item_gst_rate->item_id = $items->id;
            $item_gst_rate->gst_rate = $request->input('gst_rate');
            $item_gst_rate->item_type = $request->input('item_type');
            $item_gst_rate->comp_id = Session::get('user_company_id');
            $item_gst_rate->effective_from = $request->input('gst_rate_effective_date');
            $item_gst_rate->created_at = Carbon::now();
            $item_gst_rate->created_by = Session::get('user_id');
            $item_gst_rate->save();
         }
         ItemBalanceBySeries::where('item_id',$items->id)->delete();
         ItemLedger::where('item_id',$items->id)->where('source','-1')->delete();
         $series = $request->input('series');
         $opening_amount = $request->input('opening_amount');
         $opening_qty = $request->input('opening_qty');
         $opening_balance_type = $request->input('opening_balance_type');
         foreach ($series as $key => $value) {
            $opening_amount[$key] = trim(str_replace(",","",$opening_amount[$key]));
            $opening_qty[$key] = trim(str_replace(",","",$opening_qty[$key]));
            if(!empty($opening_amount[$key]) && !empty($opening_qty[$key])){
               $series_balance = new ItemBalanceBySeries;
               $series_balance->item_id = $items->id;
               $series_balance->series = $value;
               $series_balance->opening_amount = $opening_amount[$key];
               $series_balance->opening_quantity = $opening_qty[$key];
               $series_balance->type = $opening_balance_type[$key];
               $series_balance->company_id = Session::get('user_company_id');
               $series_balance->created_at =  Carbon::now();;
               $series_balance->save();
               //Add In Item Ledger
               $ledger = new ItemLedger();
               $ledger->item_id = $items->id;
               if($opening_balance_type[$key]=='Debit'){
                  $ledger->in_weight = $opening_qty[$key];
               }else if($opening_balance_type[$key]=='Credit'){
                  $ledger->out_weight = $opening_qty[$key];
               }
               $ledger->series_no = $value;
               $ledger->total_price = $opening_amount[$key];
               $ledger->company_id = Session::get('user_company_id');
               $ledger->source = -1;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $default_fy = explode("-",Session::get('default_fy'));
               $txn_date = $default_fy[0]."-04-01";
               $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
               $ledger->save();
            }
         }
      }
      return redirect('account-manage-item')->withSuccess('item updated successfully!');
   }
   public function delete(Request $request){
      Gate::authorize('view-module', 52);
      $exist = ItemLedger::where('item_id', $request->heading_id)
      ->where('source', '!=', -1)
      ->where('delete_status', '=', '0')
      ->first();
      // If no ItemLedger exists except source = -1
      if (!$exist) {
         $account = ManageItems::find($request->heading_id);
         if ($account) {
            $account->delete = '1'; // Optional custom flag, avoid using "delete" as column name
            $account->deleted_at = Carbon::now();
            $account->update(); // Use save() to persist model changes
         }
         $del = ItemLedger::where('item_id', $request->heading_id)
               ->where('source', -1)
               ->first();
         if ($del) {
            $del->delete_status = '1'; // Optional custom flag
            $del->deleted_at = Carbon::now();
            $del->update(); // Persist changes
         }
         return redirect('account-manage-item')->withSuccess('Item deleted successfully!');
      } else {
         return redirect('account-manage-item')->withErrors('Item cannot be deleted. Transactions exist.');
      }
   }
   public function failedMessage(){
      return response()->json([
         'code' => 422,
         'message' => 'Something went wrong, please try again after some time.',
      ]);
   }
   public function stockJournal(Request $request)
   {
      Gate::authorize('view-module', 30);         
      $input = $request->all();
      $from_date = null;
      $to_date = null;
      // Handle date selection from input or session
      if (!empty($input['from_date']) && !empty($input['to_date'])) {
         $from_date = date('Y-m-d', strtotime($input['from_date']));
         $to_date = date('Y-m-d', strtotime($input['to_date']));
         session(['stockJournal_from_date' => $from_date, 'stockJournal_to_date' => $to_date]);
      } elseif (session()->has('stockJournal_from_date') && session()->has('stockJournal_to_date')) {
         $from_date = session('stockJournal_from_date');
         $to_date = session('stockJournal_to_date');
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
      $sizeStatusSub = DB::table('item_size_stocks')
         ->select(
            'sj_generated_detail_id','status',
            DB::raw('MIN(status) as size_status')
         )
         ->groupBy('sj_generated_detail_id');

      // Start base query
      $lastProductionIds = DB::table('stock_journal')
                ->where('company_id', $company_id)
                ->orderByRaw("STR_TO_DATE(jdate, '%Y-%m-%d') desc")
                //->orderBy('id', 'desc')
                ->limit(10)
                ->pluck('id')
                ->toArray();
      $query = DB::table('stock_journal_detail')
         ->leftJoin('manage_items', 'stock_journal_detail.consume_item', '=', 'manage_items.id')
         ->leftJoin('manage_items as new', 'stock_journal_detail.new_item', '=', 'new.id')
         ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
         
         ->leftJoin('units as new_unit', 'new.u_name', '=', 'new_unit.id')
         //->leftJoin('item_size_stocks', 'stock_journal_detail.id', '=', 'item_size_stocks.sj_generated_detail_id')
         ->leftJoinSub($sizeStatusSub, 'iss', function ($join) {
            $join->on(
                     'stock_journal_detail.id',
                     '=',
                     'iss.sj_generated_detail_id'
                  );
            })
         ->Join('stock_journal', 'stock_journal_detail.parent_id', '=', 'stock_journal.id')
         ->select(
               'stock_journal_detail.parent_id as id',
               'stock_journal_detail.id as detail_id',
               'journal_date',
               'consume_weight',
               'new_weight',
               'manage_items.name',
               'stock_journal.voucher_no_prefix',
               'units.s_name',
               'new_unit.s_name as new_unit',
               'new.name as new_item',
               'consume_price',
               'consume_amount',
               'new_price',
               'new_amount',
               'consumption_entry_status',
                'stock_journal.approved_status',
                'stock_journal.approved_by',
                'stock_journal.approved_at',
                'stock_journal.created_by',
                DB::raw("(SELECT name FROM users WHERE users.id = stock_journal.approved_by LIMIT 1) as approved_by_name"),
                DB::raw("(SELECT name FROM users WHERE users.id = stock_journal.created_by LIMIT 1) as created_by_name"),
               DB::raw('COALESCE(iss.size_status, 1) as size_status')
         )
         ->where('stock_journal_detail.status', 1)
         ->where('stock_journal_detail.company_id', $company_id);

      // Apply date filter or limit to 10
      if ($from_date && $to_date) {
         $query->whereRaw(
            "STR_TO_DATE(stock_journal_detail.journal_date, '%Y-%m-%d') BETWEEN ? AND ?",
            [$from_date, $to_date]
        );
         // $query->whereRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$from_date])
         //       ->whereRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$to_date])
         //       ->orderBy('journal_date', 'asc')
         //       ->orderBy('stock_journal.id', 'asc');
      } else {
         $query->whereIn('stock_journal_detail.parent_id', $lastProductionIds);
         //$query->orderByRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') desc")->limit(10);
      }
        $query->whereNotNull('stock_journal_detail.journal_date')
                ->where('stock_journal_detail.journal_date', '!=', '');
      $journals = $query
                ->orderByRaw("STR_TO_DATE(stock_journal_detail.journal_date, '%Y-%m-%d') ASC")
                ->orderBy('stock_journal_detail.parent_id')
                ->orderByRaw('new.name IS NULL')
                ->orderBy('new.name')
                ->orderBy('manage_items.name')
                ->get();
      
      $hideDeleteFor = $journals
      ->filter(function ($row) {
               return $row->size_status === 0 || $row->size_status === '0';
         })
         ->unique()
            ->pluck('id');
    //   echo "<pre>";
    //   print_r($journals->toArray());
    //     //print_r($hideDeleteFor->toArray());
    //   die;
      //->flip();
      //$hideDeleteFor = [];
      return view('manageitem/stock-journal')
         ->with('journals', $journals)
         ->with('month_arr', $month_arr)
         ->with('from_date', $from_date)
         ->with('to_date', $to_date)
         ->with('hideDeleteFor',$hideDeleteFor->toArray());
   }

   public function addStockJournal(){
      Gate::authorize('view-module', 86);
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31'; 
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
      $comp = Companies::select('user_id')->where('id',Session::get('user_company_id'))->first();
      $production_module_status = MerchantModuleMapping::where('module_id',4)->where('company_id', Session()->get('user_company_id'))->where('merchant_id',$comp->user_id)->first();
      $production_module_status = $production_module_status ? 1 : 0;
      $production_items = ProductionItem::where('company_id',Session::get('user_company_id'))
                                          ->where('status',1)
                                          ->select('item_id')
                                          ->get();
      $itemIds = $production_items->pluck('item_id')->toArray();
      return view('manageitem/add-stock-journal')->with('fy_start_date', $fy_start_date)->with('fy_end_date', $fy_end_date)->with('items', $items)->with('date', $bill_date)->with('series_list', $series_list)->with('production_module_status',$production_module_status)->with('itemIds',$itemIds);
   }
   public function saveStockJournal(Request $request){
      Gate::authorize('view-module', 86);
      // echo "<pre>";
      // print_r($request->all());
       //die;     
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $date = $request->input('date');
      $part_life_entry_id = $request->input('part_life_entry_id');
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

      $generated_item = $request->input('generated_item');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');
      $generated_units = $request->input('generated_units');
      $generated_unit_name = $request->input('generated_unit_name');

      //Check Item Size 
      $size_exists = 1;
      $generated_size_info_arr = $request->input('generated_size_info', []);
      foreach ($generated_item as $key => $value){
         if (isset($generated_size_info_arr[$key])) {
            $generated_size_info = json_decode($generated_size_info_arr[$key], true);
            if(empty($generated_size_info) || count($generated_size_info)==0) {
                  $size_exists = 0;
            }
         }
      }
      if($size_exists==0){
         return redirect()->back()
        ->with('error', 'Please add item size information.')
        ->withInput();
      }

      $stockjournal = new StockJournal;
      $stockjournal->jdate = $date;
      $stockjournal->part_life_entry_id = $part_life_entry_id;
      $stockjournal->narration = $narration;
      $stockjournal->series_no = $series_no;
      $stockjournal->material_center = $material_center;
      $stockjournal->voucher_no_prefix = $voucher_prefix;
      $stockjournal->voucher_no = $voucher_no;
      $stockjournal->company_id = Session::get('user_company_id');
      $stockjournal->created_by = Session::get('user_id');
      $stockjournal->financial_year = $financial_year;
      $stockjournal->created_at = date('d-m-Y H:i:s');
      if($stockjournal->save()){
          if(!empty($part_life_entry_id)){
            DB::table('part_life_entries')
                  ->where('entry_group_id', $part_life_entry_id)
                  ->update([
                     'stock_journal_id' => $stockjournal->id,
                     'status' => 2
                  ]);
         }
         foreach ($consume_item as $key => $value){
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
            if($request->item_size_info[$key]!=""){
               $item_size_info = json_decode($request->item_size_info[$key], true);
               if(count($item_size_info) > 0){
                  ItemSizeStock::whereIn('id', $item_size_info)
                        ->update([
                           'status' => 0,
                           'sj_consumption_id' =>$stockjournal->id,
                        ]);
               }
            }
            
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
            $generated_size_info_arr = $request->input('generated_size_info', []);
            // Make sure the size info exists for this item
            if (isset($generated_size_info_arr[$key])) {

               // Decode JSON for this item's size info
               $generated_size_info = json_decode($generated_size_info_arr[$key], true);

               if (!empty($generated_size_info)) {

                     // If 'sizes' is an array, loop through each size
                     foreach ($generated_size_info['sizes'] as $index => $size) {

                        // Extract value after 'X'
                        $size = strtoupper($size); // e.g., "12X120"
                        $parts = explode('X', $size);
                        $valueAfterX = $parts[1] ?? null;

                        // Get weight, reel, unit for this size
                        $weight = $generated_size_info['weights'][$index] ?? null;
                        $reel_no = $generated_size_info['reels'][$index] ?? null;
                        $unit = $generated_size_info['units'][$index] ?? null;

                        // Get bf from ProductionItem
                        $bf = ProductionItem::where('item_id', $value)
                                             ->value('bf');

                        // Create new ItemSizeStock record
                        $new_reel = new ItemSizeStock;
                        $new_reel->item_id = $value;
                        $new_reel->size = $size;
                        $new_reel->weight = $weight;
                        $new_reel->reel_no = $reel_no;
                        $new_reel->sj_generated_id = $stockjournal->id;
                        $new_reel->sj_generated_detail_id = $stockjournaldetail->id;
                        $new_reel->gsm = $valueAfterX;
                        $new_reel->bf = $bf;
                        $new_reel->status = 1;
                        $new_reel->unit = $unit;
                        $new_reel->company_id = Session::get('user_company_id');
                        $new_reel->created_by = Session::get('user_id');
                        $new_reel->created_at = date('Y-m-d H:i:s',strtotime($request->input('date')));
                        $new_reel->save(); // don't forget to save
                     }
               }
            }
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->series_no = $request->input('series_no');
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
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->entry_date = $request->date;
            $average_detail->item_id = $generated_item[$key];
            $average_detail->type = 'STOCK JOURNAL GENERATE';
            $average_detail->stock_journal_in_id = $stockjournal->id;
            $average_detail->stock_journal_in_weight = $generated_weight[$key];
            $average_detail->stock_journal_in_amount = $generated_amount[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$generated_item[$key],$request->input('series_no'));
         }
         return redirect('stock-journal')->withSuccess('Stock Journal Added Successfully!'); 
      }
   }
   public function deleteStockJournal(Request $request){
      
      Gate::authorize('view-module', 64);
      $id = $request->input('del_id');
      $stock_journal = StockJournal::find($id);
      $part_life_entry_id = $stock_journal->part_life_entry_id ?? null;
      $oldSnapshot = [
         'stock_journal' => $stock_journal->toArray(),
         'consume_details' => StockJournalDetail::where('parent_id', $id)
            ->whereNotNull('consume_item')
            ->get()
            ->toArray(),
         'generate_details' => StockJournalDetail::where('parent_id', $id)
            ->whereNotNull('new_item')
            ->get()
            ->toArray(),
         // ðŸ”¥ CONSUMED REELS (MISSING PIECE)
         'size_consumed' => ItemSizeStock::where('sj_consumption_id', $id)
            ->get()
            ->toArray(),

         // ðŸ”¥ GENERATED REELS
         'size_generated' => ItemSizeStock::where('sj_generated_id', $id)
            ->get()
            ->toArray(),
      ];
     
      $delete = StockJournal::where('id',$id)->delete();
      if($delete){
          if(!empty($part_life_entry_id)){
            DB::table('part_life_entries')
                  ->where('entry_group_id', $part_life_entry_id)
                  ->update([
                     'stock_journal_id' => NULL,
                     'status' => 1
                  ]);
         }
         if($stock_journal->consumption_entry_status==0){
            ItemAverageDetail::where('stock_journal_in_id',$id)
                              ->where('type','STOCK JOURNAL GENERATE')
                              ->delete();
         }else if($stock_journal->consumption_entry_status==1){
            ItemAverageDetail::where('company_id',Session::get('user_company_id'))
                        ->where('type','STOCK JOURNAL GENERATE')
                        ->where('stock_journal_in_id',$id)
                        ->update([
                           "stock_journal_in_id"=>null,
                           "stock_journal_in_amount"=>DB::raw("stock_journal_in_weight * 1")
                        ]);
         }
         ItemAverageDetail::where('stock_journal_out_id',$id)
                        ->where('type','STOCK JOURNAL CONSUME')
                        ->delete();
         $desc = StockJournalDetail::where('parent_id',$id)->get();
         foreach ($desc as $key => $value) {
            if(!empty($value->consume_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->consume_item,$stock_journal->series_no);
            }else if(!empty($value->new_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->new_item,$stock_journal->series_no);
            }               
         }
         StockJournalDetail::where('parent_id',$id)->delete();
         if($stock_journal->consumption_entry_status==0){
            ItemLedger::where('source',3)
                     ->where('source_id',$id)
                     ->delete();
         }else if($stock_journal->consumption_entry_status==1){
            $item = ManageItems::select('id')
                              ->where('name','Pop Roll')
                              ->where('company_id',0)
                              ->first();
            ItemLedger::where('source',3)
                     ->where('source_id',$id)
                     ->where('item_id','!=',$item->id)
                     ->where('out_weight','!=','')
                     ->delete();
            ItemLedger::where('source',3)
                     ->where('source_id',$id)
                     ->where('item_id',$item->id)
                     ->delete();
            ItemLedger::where('company_id',Session::get('user_company_id'))
                  ->where('source','3')
                  ->where('source_id',$id)
                  ->where('item_id','!=',$item->id)
                  ->where('in_weight','!=','')
                  ->update([
                     "source_id"=>null,
                     "price"=>1,
                     "total_price"=>DB::raw("in_weight * 1")
                  ]);
         }
         
         ItemSizeStock::where('company_id', Session::get('user_company_id'))
               ->where('sj_consumption_id', $id)
               ->update([
                  'status' => 1,
                  'sj_consumption_id' => NULL
               ]);
         ItemSizeStock::where('company_id', Session::get('user_company_id'))
                        ->where('sj_generated_id', $id)
                        ->delete();
         ActivityLog::create([
            'module_type' => 'stock_journal',
            'module_id'   => $id,
            'action'      => 'delete',
            'old_data'    => $oldSnapshot,
            'new_data'    => null,
            'action_by'   => Session::get('user_id'),
            'company_id'  => Session::get('user_company_id'),
            'action_at'   => now(),
         ]);
         //Consumption Entry Revert
         if($stock_journal->consumption_entry_status==1){
            DeckleProcess::whereDate('reel_generated_at', $stock_journal->jdate)
                        ->where('company_id', Session::get('user_company_id'))
                        ->UPDATE(['stock_journal_status'=>0]);
            Consumption::where('stock_journal_id',$stock_journal->id)->delete();
         }
         return redirect('stock-journal')->withSuccess('Stock Journal Deleted Successfully!');          
      }      
   }  
   public function editStockJournal(Request $request,$id){
      Gate::authorize('view-module', 63);
      $financial_year = Session::get('default_fy'); // e.g. 26-27
      [$startYY, $endYY] = explode('-', $financial_year);
      $fy_start_date = '20' . $startYY . '-04-01';
      $fy_end_date   = '20' . $endYY   . '-03-31';
      $journal = StockJournal::find($id); 
      $journal_details = StockJournalDetail::where('parent_id',$id)
                                             ->get();
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
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*','units.s_name as unit'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->where('manage_items.status','1')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();
                        
       $comp = Companies::select('user_id')->where('id', Session::get('user_company_id'))->first();
      $production_module_status = MerchantModuleMapping::where('module_id',4)
                                        ->where('merchant_id', $comp->user_id)
                                        ->where('company_id', Session()->get('user_company_id'))
                                        ->exists() ? 1 : 0;
      $production_items = ProductionItem::where('company_id', Session::get('user_company_id'))
                                       ->where('status', 1)
                                       ->select('item_id')
                                       ->get();

      $consumed_reels = ItemSizeStock::where('company_id',Session::get('user_company_id'))
                                       ->where('sj_consumption_id',$id)
                                       ->whereNull('sj_generated_detail_id')
                                       ->select('item_id','size','weight','reel_no','id','unit')
                                       ->get();

      $generated_reels = ItemSizeStock::where('company_id',Session::get('user_company_id'))
                                       ->where('sj_generated_id',$id)
                                       ->whereNotNull('sj_generated_detail_id')
                                       ->select('item_id','size','weight','reel_no','id','unit','sj_generated_detail_id','status')
                                       ->get();

      $available = ItemSizeStock::select('id','item_id', 'size', 'weight', 'reel_no','unit')
        ->where('company_id', Session::get('user_company_id'))
        ->where('status', 1)
        ->where(function($q) use ($id) {
                                  $q->where('sj_generated_id', '!=', $id)
                                    ->orWhereNull('sj_generated_id');
                                       })
        ->whereNull('sale_id')
        ->get();
        $itemIds = $production_items->pluck('item_id')->toArray();
    // If you have production items
      // echo "<pre>";
      // print_r($journal_details->toArray());die;

      return view('manageitem/edit-stock-journal')->with('production_items', $production_items)->with('production_module_status', $production_module_status)->with('journal', $journal)->with('items', $items)->with('journal_details', $journal_details)->with('series_list', $series_list)->with('consumed_reels',$consumed_reels)->with('generated_reels',$generated_reels)->with('availableReels',$available)->with("itemIds",$itemIds)->with('fy_start_date', $fy_start_date)->with('fy_end_date', $fy_end_date);     
   }
   public function updateStockJournal(Request $request){
      // echo "<pre>";
      // print_r($request->all());
      // die;
      Gate::authorize('view-module', 63);
      $date = $request->input('date');
      $request->validate([
         'consume_item.*' => 'nullable|integer',
         'consume_weight.*' => 'nullable|numeric',
      ]);

      $narration = $request->input('narration');
      $series_no = $request->input('series_no');
      $voucher_prefix = $request->input('voucher_prefix');
      $voucher_no = $request->input('voucher_no');
      $material_center = $request->input('material_center');
      
      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');
      $consume_units = $request->input('consume_units');
      $consume_unit_name = $request->input('consume_unit_name');

      $generated_item = $request->input('generated_item', []);
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');
      $generated_units = $request->input('generated_units');
      $generated_unit_name = $request->input('generated_unit_name');
      $generated_size_info_arr = $request->item_size_info_gen ?? [];

      $stockjournal = StockJournal::find($request->input('edit_id'));
      $oldSnapshot = [
         'stock_journal' => $stockjournal->toArray(),

         'consume_details' => StockJournalDetail::where('parent_id', $stockjournal->id)
            ->whereNotNull('consume_item')
            ->get()
            ->toArray(),

         'generate_details' => StockJournalDetail::where('parent_id', $stockjournal->id)
            ->whereNotNull('new_item')
            ->get()
            ->toArray(),

         // âœ… OLD REELS
         'size_generated' => ItemSizeStock::where('sj_generated_id', $stockjournal->id)
            ->get()
            ->toArray(),

         'size_consumed' => ItemSizeStock::where('sj_consumption_id', $stockjournal->id)
            ->get()
            ->toArray(),
      ];
      $last_date =  $stockjournal->jdate;
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->series_no = $series_no;
      $stockjournal->material_center = $material_center;
      $stockjournal->voucher_no_prefix = $voucher_prefix;
      $stockjournal->voucher_no = $voucher_no;
      $stockjournal->updated_by = Session::get('user_id');
      $stockjournal->updated_at = date('d-m-Y H:i:s');

      
      if($stockjournal->save()){  
         //$desc_item_arr = StockJournalDetail::where('parent_id',$id)->pluck('goods_discription')->toArray();
         $id = $request->input('edit_id');
         ItemAverageDetail::where('stock_journal_in_id',$request->input('edit_id'))
                           ->where('type','STOCK JOURNAL GENERATE')
                           ->delete();    
         ItemAverageDetail::where('stock_journal_out_id',$request->input('edit_id'))
                        ->where('type','STOCK JOURNAL CONSUME')
                        ->delete();

         ItemSizeStock::where('company_id', Session::get('user_company_id'))
                           ->where('sj_consumption_id', $request->input('edit_id'))
                           ->update([
                              'status' => 1,
                              'sj_consumption_id' => NULL
                           ]);

         ItemSizeStock::where('company_id', Session::get('user_company_id'))
                        ->where('sj_generated_id', $request->input('edit_id'))
                        ->where('status','1')
                        ->delete();

         $desc = StockJournalDetail::where('parent_id',$request->input('edit_id'))->get();
         foreach ($desc as $key => $value) {
            if(!empty($value->consume_item)){
               CommonHelper::RewriteItemAverageByItem($stockjournal->jdate,$value->consume_item,$stockjournal->series_no);
            }else if(!empty($value->new_item)){
               CommonHelper::RewriteItemAverageByItem($stockjournal->jdate,$value->new_item,$stockjournal->series_no);
            }               
         }

         StockJournalDetail::where('parent_id',$request->input('edit_id'))->delete();
         ItemLedger::where('source',3)->where('source_id',$request->input('edit_id'))->delete();
         foreach ($consume_item as $key => $value) {
            if (empty($consume_item[$key])) {
               continue;
            }
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
            $stockjournaldetail->consume_item_unit = $consume_units[$key] ?? null;
            $stockjournaldetail->consume_item_unit_name = $consume_unit_name[$key] ?? null;
            $stockjournaldetail->consume_weight = $consume_weight[$key] ?? 0;
            $stockjournaldetail->consume_price = $consume_price[$key] ?? 0;
            $stockjournaldetail->consume_amount = $consume_amount[$key] ?? 0;
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');
            $stockjournaldetail->created_at = now();
            $stockjournaldetail->save();
            if (
               isset($request->item_size_info[$key]) &&
               !empty($request->item_size_info[$key])
            ) {
               $decoded = json_decode($request->item_size_info[$key], true);
               if (is_array($decoded)) {
                     $reelIds = [];
                     foreach ($decoded as $row) {
                        if (isset($row['id'])) {
                           $reelIds[] = $row['id'];
                        }
                     }
                     if (!empty($reelIds)) {
                        ItemSizeStock::whereIn('id', $reelIds)->update([
                           'status' => 0,
                           'sj_consumption_id' => $stockjournal->id
                        ]);
                     }
               }
            }
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $consume_item[$key];
            $item_ledger->out_weight = $consume_weight[$key];
            $item_ledger->series_no = $request->input('series_no');
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
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$consume_item[$key],$request->input('series_no'));
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
            
            if (!empty($generated_size_info_arr[$key])) {
               $sizes = json_decode($generated_size_info_arr[$key], true); // each row is array of objects
               foreach ($sizes as $row) {
                  
                  if($row['status']==1){
                     $check_sale = ItemSizeStock::where('sj_generated_id', $stockjournal->id)
                                 ->where('reel_no', $row['reel_no'])
                                 ->where('item_id', $value)
                                 ->where('status', '0')
                                 ->first();
                     if($check_sale){
                        ItemSizeStock::where('id', $check_sale->id)
                              ->update([
                                    'sj_generated_detail_id' => $stockjournaldetail->id
                                 ]);
                        continue;
                     }
                     // row contains: size, reel_no, weight, unit
                     $fullSize = strtoupper($row['size']);  // e.g. "12X120"
                     $parts = explode('X', $fullSize);
                     $valueAfterX = $parts[1] ?? null;
                     $bf = ProductionItem::where('item_id', $value)->value('bf');
                     // Create new Reel
                     $new_reel = new ItemSizeStock;
                     $new_reel->item_id = $value;
                     $new_reel->size = $row['size'];
                     $new_reel->weight = $row['weight'];
                     $new_reel->reel_no = $row['reel_no'];
                     $new_reel->unit = $row['unit'];
                     $new_reel->gsm = $valueAfterX;
                     $new_reel->bf = $bf;
                     $new_reel->status = 1;
                     $new_reel->sj_generated_id = $stockjournal->id;
                     $new_reel->sj_generated_detail_id = $stockjournaldetail->id;
                     $new_reel->company_id = Session::get('user_company_id');
                     $new_reel->created_by = Session::get('user_id');
                     $new_reel->created_at = date('Y-m-d H:i:s',strtotime($request->input('date')));
                     $new_reel->save();
                  }else if($row['status']==0){
                     // Revert existing reel
                     ItemSizeStock::where('sj_generated_id', $stockjournal->id)
                                 ->where('reel_no', $row['reel_no'])
                                 ->where('item_id', $value)
                              ->update([
                                    'sj_generated_detail_id' => $stockjournaldetail->id
                                 ]);
                  }
                  
               }
            }
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->in_weight = $generated_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->price = $generated_price[$key];
            $item_ledger->total_price = $generated_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();

            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->entry_date = $request->date;
            $average_detail->item_id = $generated_item[$key];
            $average_detail->type = 'STOCK JOURNAL GENERATE';
            $average_detail->stock_journal_in_id = $stockjournal->id;
            $average_detail->stock_journal_in_weight = $generated_weight[$key];
            $average_detail->stock_journal_in_amount = $generated_amount[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$generated_item[$key],$request->input('series_no'));
         }
         $newSnapshot = [
            'stock_journal' => StockJournal::find($stockjournal->id)->toArray(),

            'consume_details' => StockJournalDetail::where('parent_id', $stockjournal->id)
               ->whereNotNull('consume_item')
               ->get()
               ->toArray(),

            'generate_details' => StockJournalDetail::where('parent_id', $stockjournal->id)
               ->whereNotNull('new_item')
               ->get()
               ->toArray(),

            // âœ… NEW REELS
            'size_generated' => ItemSizeStock::where('sj_generated_id', $stockjournal->id)
               ->get()
               ->toArray(),

            'size_consumed' => ItemSizeStock::where('sj_consumption_id', $stockjournal->id)
               ->get()
               ->toArray(),
         ];

         ActivityLog::create([
            'module_type' => 'stock_journal',
            'module_id'   => $stockjournal->id,
            'action'      => 'edit',
            'old_data'    => $oldSnapshot,
            'new_data'    => $newSnapshot,
            'action_by'   => Session::get('user_id'),
            'company_id'  => Session::get('user_company_id'),
            'action_at'   => now(),
         ]);
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('stock-journal')->withSuccess('Stock Journal Updated Successfully!');
         }
          
      }
   }
   public function itemImportView(Request $request){      
      return view('manageitem/item_import_view');
   }
   public function itemImportProcess(Request $request) {       
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;      
           
      $already_exists_error_arr = [];$already_exists_item_arr = [];$error_arr = [];$data_arr = [];
      $all_error_arr = [];
      if($duplicate_voucher_status==0){
         $file = $request->file('csv_file');  
         $filePath = $file->getRealPath();      
         $final_result = array();
         if(($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ",");
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $index = 1;
            $series_no = "";
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
               $data = array_map('trim', $data);
               if($data[0]!="" && $data[2]!=""){
                  $name = $data[0];
                  $item = ManageItems::select('id')
                                       ->where('name',trim($name))
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($item){
                     array_push($already_exists_error_arr, 'Item '.$name.' already exists');                     
                     // if(in_array($name, $already_exists_item_arr)){
                     //    array_push($already_exists_error_arr, 'Item '.$name.' already exists');
                     // }
                     // array_push($already_exists_item_arr,$name);
                  }
               }
            }
         }
         if(count($already_exists_error_arr)>0){
            $res = array(
               'status' => false,
               'data' => $already_exists_error_arr,
               "message"=>"Already Exists."
            );
            return json_encode($res);
         }
      }
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_row = $total_row - 1;
         $success_row = 0;
         $index = 1;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map('trim', $data);
            $error_arr = [];
            $name      = $data[0]; 
            $group     = $data[1]; 
            $series    = $data[2]; 
            $amount    = $data[3]; 
            $quantity  = $data[4]; 
            $unit      = $data[5]; 
            $gst_rate  = $data[6]; 
            $hsn_code  = $data[7]; 
            if($name=="" && $group=="" && $amount=="" && $quantity=="" && $unit=="" && $gst_rate=="" && $hsn_code==""){
               $index++;
               continue;
            }
            if($name=="" || $group==""){
               array_push($error_arr, 'Item Name or Group Name Not Found In Row -'.$index);
            }               
            $groups = ItemGroups::where('group_name',trim($group))
                     ->where('company_id',trim(Session::get('user_company_id')))
                     ->first();
            if(!$groups){
               array_push($error_arr, 'Group Name - '.$group.' not found');
            } 
            $units = Units::where('s_name',trim($unit))
                     ->where('company_id',trim(Session::get('user_company_id')))
                     ->first();
            if(!$units){
               array_push($error_arr, 'Unit - '.$unit.' not found');
            }                
            if($duplicate_voucher_status!=2){
               $check_item = ManageItems::select('id')
                           ->where('name',trim($name))
                           ->where('company_id',trim(Session::get('user_company_id')))
                           ->first();
               if($check_item){
                  array_push($error_arr, 'Item '.$name.' already exists');
               }
            }
            array_push($data_arr,array("name"=>$name,"group"=>$group,"series"=>$series,"amount"=>$amount,"quantity"=>$quantity,"unit"=>$unit,"gst_rate"=>$gst_rate,"hsn_code"=>$hsn_code,"error_arr"=>$error_arr));            
            $index++;
         } 
         fclose($handle);
         $total_invoice_count = count($data_arr);
         // echo "<pre>";
         // print_r($data_arr);
         // die;
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         if(count($data_arr)>0){
            foreach ($data_arr as $key => $value) {
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }
               $name = $value['name'];
               $group = $value['group'];
               $amount = $value['amount'];
               $amount = trim(str_replace(",","",$amount)); 
               $quantity = $value['quantity'];
               $quantity = trim(str_replace(",","",$quantity));
               $unit = $value['unit'];
               $gst_rate = $value['gst_rate'];
               $hsn_code = $value['hsn_code'];
               $series   = $value['series'];
               $groups = ItemGroups::where('group_name',trim($group))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();               
               $units = Units::where('s_name',trim($unit))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               $opening_balance_qty = "";
               $opening_balance_qt_type = "";
               $opening_balance = "";
               $opening_balance_type = "";
               if(!empty($amount) && $amount!=0){
                  if($amount<0){
                     $opening_balance = abs($amount);
                     $opening_balance_type = "Credit";
                  }else{
                     $opening_balance = $amount;
                     $opening_balance_type = "Debit";
                  }                  
               }
               $quantity = (float)$quantity;
               if(!empty($quantity) && $quantity!=0){
                  if($quantity<0){
                     $opening_balance_qty = abs($quantity);
                     $opening_balance_qt_type = "Credit";
                  }else{
                     $opening_balance_qty = $quantity;
                     $opening_balance_qt_type = "Debit"; 
                  }
                  
               }
               if($duplicate_voucher_status==2){
                  $check_items = ManageItems::select('id')
                              ->where('name',trim($name))
                              ->where('company_id',trim(Session::get('user_company_id')))
                              ->first();
                  if($check_items){
                     $items = ManageItems::find($check_items->id);
                     $items->company_id =  Session::get('user_company_id');
                     $items->name = $name;
                     $items->p_name = $name;
                     $items->g_name = $groups->id;
                     $items->u_name = $units->id;
                     $items->hsn_code = $hsn_code;
                     $items->gst_rate = $gst_rate;
                     // $items->opening_balance_qty = $opening_balance_qty;
                     // $items->opening_balance_qt_type = $opening_balance_qt_type;
                     // $items->opening_balance = $opening_balance;
                     // $items->opening_balance_type = $opening_balance_type;
                     $items->updated_at = Carbon::now();
                     $items->update();
                     if($items){
                        $success_invoice_count++;
                        ItemBalanceBySeries::where('item_id',$items->id)->delete();
                        $series_balance = new ItemBalanceBySeries();
                        $series_balance->item_id = $items->id;
                        $series_balance->series = $series;
                        $series_balance->opening_amount = $opening_balance;
                        $series_balance->opening_quantity = $opening_balance_qty;
                        $series_balance->type = $opening_balance_qt_type;
                        $series_balance->company_id = Session::get('user_company_id');
                        $series_balance->created_at = now();
                        $series_balance->save();
                        if(!empty($opening_balance_qty) && !empty($opening_balance_qt_type)){
                           $check = ItemLedger::where('item_id',$check_items->id)
                                                   ->where('series_no',$series)
                                                   ->where('source','-1')
                                                   ->first();
                           if($check){
                              $ledger = ItemLedger::find($check->id);
                              if($opening_balance_qt_type=='Debit'){
                                 $ledger->in_weight = $opening_balance_qty;
                                 $ledger->out_weight = "";
                              }else if($opening_balance_qt_type=='Credit'){
                                 $ledger->out_weight = $opening_balance_qty;
                                 $ledger->in_weight = "";
                              }
                              $ledger->total_price = $opening_balance;
                              $ledger->updated_by = Session::get('user_id');
                              $ledger->updated_at = date('d-m-Y H:i:s');
                              $ledger->save();
                           }else{
                              $ledger = new ItemLedger();
                              $ledger->item_id = $check_items->id;
                              if($opening_balance_qt_type=='Debit'){
                                 $ledger->in_weight = $opening_balance_qty;
                              }else if($opening_balance_qt_type=='Credit'){
                                 $ledger->out_weight = $opening_balance_qty;
                              }
                              $ledger->series_no = $series;
                              $ledger->total_price = $opening_balance;
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
                           $check = ItemLedger::where('item_id',$check_items->id)
                                                   ->where('source','-1')
                                                   ->first();
                           if($check){
                              ItemLedger::where('source','-1')
                                             ->where('item_id',$check_items->id)
                                             ->delete();
                           }
                        }
                        continue;
                     }
                  }                  
               }
               $items = new ManageItems;
               $items->company_id =  Session::get('user_company_id');
               $items->name = $name;
               $items->p_name = $name;
               $items->g_name = $groups->id;
               $items->u_name = $units->id;
               $items->hsn_code = $hsn_code;
               $items->gst_rate = $gst_rate;
               // $items->opening_balance_qty = $opening_balance_qty;
               // $items->opening_balance_qt_type = $opening_balance_qt_type;
               // $items->opening_balance = $opening_balance;
               // $items->opening_balance_type = $opening_balance_type;      
               $items->status = '1';
               $items->save();
               if ($items->id){
                  if(!empty($opening_balance_qty) && !empty($opening_balance_qt_type)){

                     $series_balance = new ItemBalanceBySeries();
                     $series_balance->item_id = $items->id;
                     $series_balance->series = $series;
                     $series_balance->opening_amount = $opening_balance;
                     $series_balance->opening_quantity = $opening_balance_qty;
                     $series_balance->type = $opening_balance_qt_type;
                     $series_balance->company_id = Session::get('user_company_id');
                     $series_balance->created_at = now();
                     $series_balance->save();

                     $ledger = new ItemLedger();
                     $ledger->item_id = $items->id;
                     if($opening_balance_qt_type=='Debit'){
                        $ledger->in_weight = $opening_balance_qty;
                     }else if($opening_balance_qt_type=='Credit'){
                        $ledger->out_weight = $opening_balance_qty;
                     }
                     $ledger->total_price = $opening_balance;
                     $ledger->series_no = $series; 
                     $ledger->company_id = Session::get('user_company_id');
                     $ledger->source = -1;
                     $ledger->created_by = Session::get('user_id');
                     $ledger->created_at = date('d-m-Y H:i:s');
                     $default_fy = explode("-",Session::get('default_fy'));
                     $txn_date = $default_fy[0]."-04-01";
                     $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
                     $ledger->save();
                  }
                  $success_invoice_count++;
               }
            }
         }
      }
      $res = array("total_count"=>$total_invoice_count,"success_count"=>$success_invoice_count,"failed_count"=>$failed_invoice_count,"error_message"=>$all_error_arr);
      $res = array(
         'status' => true,
         'data' => $res,
         "message"=>"Uploaded Successfully."
      );
      return json_encode($res);
      
   }
   public function subItemList(Request $request,$id) {
      $sub_items = SubItem::where('parent_item_id',$id)->where('status',1)->get();
      return view("manageitem.sub_item_list",["item_id"=>$id,"sub_items"=>$sub_items]);
   }
   public function addSubItem(Request $request,$id) {
      return view("manageitem.add_sub_item",["item_id"=>$id]);
   }
   public function storeSubItem(Request $request) {
      try {
         DB::beginTransaction();
         foreach($request->items as $item) {
            $name = trim($item['name']);
            $quantity = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
            if ($name === '') {
               continue;
            }
            // find existing sub-item for this company
            $existing = SubItem::where('name', $name)
                              ->where('parent_item_id', $request->item_id)
                              ->where('company_id', Session::get('user_company_id'))
                              ->first();
            if ($existing) {
               // update quantity (add incoming quantity)
               $existing->quantity = (float)$existing->quantity + $quantity;
               $existing->updated_at = Carbon::now();
               $existing->save();
            } else {
               $new_item = new SubItem;
               $new_item->name  = $name;
               $new_item->parent_item_id = $request->item_id;
               $new_item->quantity = $quantity;
               $new_item->company_id = Session::get('user_company_id');
               $new_item->created_by = Session::get('user_id');
               $new_item->created_at = Carbon::now();
               $new_item->save();
            }
         }
         DB::commit();
         return response()->json(['success' => true]);
      } catch(\Exception $e) {
         DB::rollback();
         return response()->json(['success' => false, 'message' => $e->getMessage()]);
      }
   }
   public function importStockJournalView(Request $request){
      return view('manageitem/stock_journal_import_view');
   }
   public function stockJournalImportProcess(Request $request) {
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;
      $financial_year = Session::get('default_fy');
      $fy = explode('-',$financial_year);
      $from_date = $fy[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $fy[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      $company_data = Companies::where('id', Session::get('user_company_id'))->first(); 
      $already_exists_error_arr = [];
      $already_exists_item_arr = [];
      $error_arr = [];
      $data_arr = [];
      $all_error_arr = [];
      if($duplicate_voucher_status==0){
         $file = $request->file('csv_file');
         $filePath = $file->getRealPath();
         $final_result = array();
         if(($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ",");
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $index = 1;
            $series_no = "";
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
               $data = array_map('trim', $data);
               if($data[0]!="" && $data[1]!="" && $data[2]!="" && $data[3]!=""){
                  $series = $data[1];
                  $bill_no = $data[3];
                  $journal = StockJournal::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('status','1')
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($journal){
                     array_push($already_exists_error_arr, 'Stock Jouranl on bill no. - '.$bill_no.' already exists');
                  }
               }
            }
         }
         if(count($already_exists_error_arr)>0){
            $res = array(
               'status' => false,
               'data' => $already_exists_error_arr,
               "message"=>"Already Exists."
            );
            return json_encode($res);
         }
      }
      if($company_data->gst_config_type == "single_gst"){
         $gst_data = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$gst_data[0]->id])
                           ->get();
         if(count($branch)>0){
            $gst_data = $gst_data->merge($branch);
         }         
      }else if($company_data->gst_config_type == "multiple_gst"){
         $gst_data = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($gst_data as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $gst_data = $gst_data->merge($branch);
            }
         }         
      }
      foreach ($gst_data as $key => $value) {
         $series_arr[] = $value->series;
         $material_center_arr[] = $value->mat_center;
         $gst_no_arr[] = $value->gst_no;
      }
      $bill_date = "";
      $file = $request->file('csv_file');
      $filePath = $file->getRealPath();
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_row = $total_row - 1;
         $success_row = 0;
         $index = 1;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map('trim', $data);
            if($data[0]=="" && $data[1]=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;
            }
            if($data[0]!="" && $data[1]!="" && $data[2]!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"material_center"=>$material_center,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $txn_arr = [];
               $error_arr = [];
               $bill_date = $data[0];
               $series = $data[1];
               $material_center = $data[2];
               $bill_no = $data[3];
               
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($bill_date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($bill_date)))){
                  array_push($error_arr, 'Date '.$bill_date.' Not In Financial Year - Row '.$index);
               }
               if(!in_array($series, $series_arr)){
                  array_push($error_arr, 'Series No. '.$series.' Not Found - Row '.$index); 
               }
               if($duplicate_voucher_status!=2 && !empty($bill_no)){
                  $journal = StockJournal::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('status','1')
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($journal){
                     array_push($error_arr, 'Stock Jounral on bill no. - '.$bill_no.' already exists');
                  }
               }
            }
            $items = $data[4];
            $generate_qty = $data[5];
            $generate_unit = $data[6];
            $generate_price = $data[7];
            $generate_amount = $data[8];
            $generate_amount = str_replace(",","",$generate_amount);

            $consume_qty = $data[9];
            $consume_unit = $data[10];
            $consume_price = $data[11];
            $consume_amount = $data[12];
            $consume_amount = str_replace(",","",$consume_amount);

            $check_item = ManageItems::where('name',trim($items))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_item){
               array_push($error_arr, 'Item Name '.$items.' Not Found - Row '.$index);
            }
            $generate_unit_id = "";
            if(!empty($generate_unit)){
               $check_unit = Units::where('s_name',trim($generate_unit))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               if(!$check_unit){
                  array_push($error_arr, 'Unit Name '.$generate_unit.' Not Found - Row '.$index);
               }else{
                  $generate_unit_id = $check_unit->id;
               }
            }
            $consume_unit_id = "";
            if(!empty($consume_unit)){
               $check_unit = Units::where('s_name',trim($consume_unit))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               if(!$check_unit){
                  array_push($error_arr, 'Unit Name '.$consume_unit.' Not Found - Row '.$index);
               }else{
                  $consume_unit_id = $check_unit->id;
               }
            }

            if($check_item){
               array_push($txn_arr,
                  array("item"=>$check_item->id,
                  "generate_qty"=>$generate_qty,
                  "generate_unit"=>$generate_unit,
                  "generate_unit_id"=>$generate_unit_id,
                  "generate_price"=>$generate_price,
                  "generate_amount"=>$generate_amount,
                  "consume_qty"=>$consume_qty,
                  "consume_unit"=>$consume_unit,
                  "consume_unit_id"=>$consume_unit_id,
                  "consume_price"=>$consume_price,
                  "consume_amount"=>$consume_amount)
               );
            }else{
               array_push($txn_arr,
                  array("item"=>$items,
                  "generate_qty"=>$generate_qty,
                  "generate_unit"=>$generate_unit,
                  "generate_unit_id"=>$generate_unit_id,
                  "generate_price"=>$generate_price,
                  "generate_amount"=>$generate_amount,
                  "consume_qty"=>$consume_qty,
                  "consume_unit"=>$consume_unit,
                  "consume_unit_id"=>$consume_unit_id,
                  "consume_price"=>$consume_price,
                  "consume_amount"=>$consume_amount)
               );
            }
            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"material_center"=>$material_center,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
            }   
            $index++;
         } 
         fclose($handle);
         $total_invoice_count = count($data_arr);
         // echo "<pre>";
         // print_r($data_arr);
         // die;
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         if(count($data_arr)>0){
            foreach ($data_arr as $key => $value) {
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }               
               $bill_date = $value['bill_date'];
               $bill_date = date('Y-m-d',strtotime($bill_date));
               $series = $value['series'];
               $bill_no = $value['bill_no'];
               $material_center = $value['material_center'];
               $txn_arr = $value['txn_arr'];
               if($duplicate_voucher_status==2){
                  $check_rec = StockJournal::select('id')
                                             ->where('voucher_no',$bill_no)
                                             ->where('series_no',trim($series))
                                             ->where('financial_year',$financial_year)
                                             ->where('company_id',trim(Session::get('user_company_id')))
                                             ->first();
                  if($check_rec){              
                     $updated_payment = StockJournal::find($check_rec->id);
                     if($updated_payment->delete()){
                        ItemAverageDetail::where('stock_journal_in_id',$check_rec->id)
                           ->where('type','STOCK JOURNAL GENERATE')
                           ->delete();    
                        ItemAverageDetail::where('stock_journal_out_id',$check_rec->id)
                        ->where('type','STOCK JOURNAL CONSUME')
                        ->delete();
                        $desc = StockJournalDetail::where('parent_id',$check_rec->id)->get();
                        foreach ($desc as $key => $value) {
                           if(!empty($value->consume_item)){
                              CommonHelper::RewriteItemAverageByItem($updated_payment->jdate,$value->consume_item,$updated_payment->series_no);
                           }else if(!empty($value->new_item)){
                              CommonHelper::RewriteItemAverageByItem($updated_payment->jdate,$value->new_item,$updated_payment->series_no);
                           }               
                        }
                        StockJournalDetail::where('parent_id',$check_rec->id)->delete();
                        ItemLedger::where('source',3)
                                    ->where('source_id',$check_rec->id)
                                    ->delete();

                     }
                  }
               }

               $stockjournal = new StockJournal;
               $stockjournal->jdate = $bill_date;
               $stockjournal->narration = "";
               $stockjournal->series_no = $series;
               $stockjournal->material_center = $material_center;
               $stockjournal->voucher_no_prefix = $bill_no;
               $stockjournal->voucher_no = $bill_no;
               $stockjournal->company_id = Session::get('user_company_id');
               $stockjournal->created_by = Session::get('user_id');
               $stockjournal->financial_year = $financial_year;
               $stockjournal->created_at = date('d-m-Y H:i:s');
               $i = 0;
               if($stockjournal->save()){
                  foreach($txn_arr as $key => $data){
                     if(!empty($data['consume_qty']) && !empty($data['consume_amount'])){
                        $stockjournaldetail = new StockJournalDetail;
                        $stockjournaldetail->journal_date = $bill_date;
                        $stockjournaldetail->parent_id = $stockjournal->id;
                        $stockjournaldetail->consume_item = $data['item'];
                        $stockjournaldetail->consume_item_unit = $data['consume_unit_id'];
                        $stockjournaldetail->consume_item_unit_name = $data['consume_unit'];
                        $stockjournaldetail->consume_weight = $data['consume_qty'];
                        $stockjournaldetail->consume_price = $data['consume_price'];
                        $stockjournaldetail->consume_amount = $data['consume_amount'];
                        $stockjournaldetail->company_id = Session::get('user_company_id');
                        $stockjournaldetail->created_by = Session::get('user_id');;
                        $stockjournaldetail->created_at = date('d-m-Y H:i:s');
                        $stockjournaldetail->save();
                        //ADD IN Stock
                        $item_ledger = new ItemLedger();
                        $item_ledger->item_id = $data['item'];
                        $item_ledger->series_no = $series;
                        $item_ledger->out_weight = $data['consume_qty'];
                        $item_ledger->txn_date = $bill_date;
                        $item_ledger->price = $data['consume_price'];
                        $item_ledger->total_price = $data['consume_amount'];
                        $item_ledger->company_id = Session::get('user_company_id');
                        $item_ledger->source = 3;
                        $item_ledger->source_id = $stockjournal->id;
                        $item_ledger->created_by = Session::get('user_id');
                        $item_ledger->created_at = date('d-m-Y H:i:s');
                        $item_ledger->save();
                        //Add Data In Average Details table
                        $average_detail = new ItemAverageDetail;
                        $average_detail->entry_date = $bill_date;
                        $average_detail->series_no = $series;
                        $average_detail->item_id = $data['item'];
                        $average_detail->type = 'STOCK JOURNAL CONSUME';
                        $average_detail->stock_journal_out_id = $stockjournal->id;
                        $average_detail->stock_journal_out_weight = $data['consume_qty'];
                        $average_detail->company_id = Session::get('user_company_id');
                        $average_detail->created_at = Carbon::now();
                        $average_detail->save();
                        CommonHelper::RewriteItemAverageByItem($bill_date,$data['item'],$series);
                     }
                     if(!empty($data['generate_qty']) && !empty($data['generate_qty'])){
                        $stockjournaldetail = new StockJournalDetail;
                        $stockjournaldetail->journal_date = $bill_date;
                        $stockjournaldetail->parent_id = $stockjournal->id;
                        $stockjournaldetail->new_item = $data['item'];
                        $stockjournaldetail->new_item_unit = $data['generate_unit_id'];
                        $stockjournaldetail->new_item_unit_name = $data['generate_unit'];
                        $stockjournaldetail->new_weight = $data['generate_qty'];
                        $stockjournaldetail->new_price = $data['generate_price'];
                        $stockjournaldetail->new_amount = $data['generate_amount'];
                        $stockjournaldetail->company_id = Session::get('user_company_id');
                        $stockjournaldetail->created_by = Session::get('user_id');;
                        $stockjournaldetail->created_at = date('d-m-Y H:i:s');
                        $stockjournaldetail->save();
                        //ADD IN Stock
                        $item_ledger = new ItemLedger();
                        $item_ledger->item_id = $data['item'];
                        $item_ledger->series_no = $series;
                        $item_ledger->in_weight = $data['generate_qty'];
                        $item_ledger->txn_date = $bill_date;
                        $item_ledger->price = $data['generate_price'];
                        $item_ledger->total_price = $data['generate_amount'];
                        $item_ledger->company_id = Session::get('user_company_id');
                        $item_ledger->source = 3;
                        $item_ledger->source_id = $stockjournal->id;
                        $item_ledger->created_by = Session::get('user_id');
                        $item_ledger->created_at = date('d-m-Y H:i:s');
                        $item_ledger->save();
                        //Add Data In Average Details table
                        $average_detail = new ItemAverageDetail;
                        $average_detail->series_no = $series;
                        $average_detail->entry_date = $bill_date;
                        $average_detail->item_id = $data['item'];
                        $average_detail->type = 'STOCK JOURNAL GENERATE';
                        $average_detail->stock_journal_in_id = $stockjournal->id;
                        $average_detail->stock_journal_in_weight = $data['generate_qty'];
                        $average_detail->stock_journal_in_amount = $data['generate_amount'];
                        $average_detail->company_id = Session::get('user_company_id');
                        $average_detail->created_at = Carbon::now();
                        $average_detail->save();
                        CommonHelper::RewriteItemAverageByItem($bill_date,$data['item'],$series);
                     }
                     $i++;
                  }
                  $success_invoice_count++;
               }         
            }
         }
      }
      $res = array("total_count"=>$total_invoice_count,"success_count"=>$success_invoice_count,"failed_count"=>$failed_invoice_count,"error_message"=>$all_error_arr);
      $res = array(
         'status' => true,
         'data' => $res,
         "message"=>"Uploaded Successfully."
      );
      return json_encode($res);
      
   }
   public function getItemPrice(Request $request)
   {
      $itemId    = $request->item_id;
      $seriesNo  = $request->series_no;

      $date = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');

      $price = DB::table('item_averages')
      ->where('item_id', $itemId)
      ->where('series_no', $seriesNo)
      ->whereDate('stock_date', $date)
      ->orderBy('id', 'DESC')
      ->value('price');

         if (!$price) {
            $lastDate = DB::table('item_averages')
               ->where('item_id', $itemId)
               ->where('series_no', $seriesNo)
               ->whereDate('stock_date', '<', $date)
               ->orderBy('stock_date', 'DESC')
               ->value('stock_date');

            $price = DB::table('item_averages')
               ->where('item_id', $itemId)
               ->where('series_no', $seriesNo)
               ->whereDate('stock_date', $lastDate)
               ->orderBy('id', 'DESC')
               ->value('price');
         }

      return response()->json([
         'price' => $price,
         'date' => $date
      ]);
   }
   
   public function itemByGroup(Request $request)
    {
        /* -------------------------------
           Step 1: Validate Request
        -------------------------------- */
        $validator = Validator::make($request->all(), [
            'group_id'  => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        /* -------------------------------
           Step 2: Fetch Items
        -------------------------------- */
        $items = ManageItems::select('id', 'name')
            ->where('company_id', $request->company_id ?? null) // optional if app sends company_id
            ->where('g_name', $request->group_id)
            ->where('status', '1')
            ->where('delete', '0')
            ->orderBy('name')
            ->get();

        /* -------------------------------
           Step 3: Response
        -------------------------------- */
        if ($items->isEmpty()) {
            return response()->json([
                'status'  => true,
                'message' => 'No items found for this group',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Items fetched successfully',
            'data'    => $items
        ], 200);
    }
     public function exportItems()
{
    $company_id = Session::get('user_company_id');

    $items = ManageItems::where('company_id',$company_id)
        ->where('delete','0')
        ->get();

    $groups = ItemGroups::pluck('group_name','id');
    $units  = Units::pluck('s_name','id');

    // get all series balances
    $seriesData = ItemBalanceBySeries::where('company_id',$company_id)
                    ->get()
                    ->groupBy('item_id');

    // find max series count
    $maxSeries = 0;
    foreach($seriesData as $row){
        if(count($row) > $maxSeries){
            $maxSeries = count($row);
        }
    }

    $filename = "items_export.csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $file = fopen('php://output','w');

    // HEADER
    $header = ['Name','Group Name'];

    for($i=1;$i<=$maxSeries;$i++){
        $header[] = 'Series';
        $header[] = 'Op Amount';
        $header[] = 'Op Stock';
    }

    $header[] = 'Unit';
    $header[] = 'GST Rate';
    $header[] = 'HSN Code';

    fputcsv($file,$header);

    foreach($items as $item){

        $row = [];

        $row[] = $item->name;
        $row[] = $groups[$item->g_name] ?? '';

        $itemSeries = $seriesData[$item->id] ?? [];

        $count = 0;

        foreach($itemSeries as $series){

            $row[] = $series->series;
            $row[] = $series->opening_amount;
            $row[] = $series->opening_quantity;

            $count++;
        }

        // fill remaining series columns
        for($i=$count;$i<$maxSeries;$i++){
            $row[]='';
            $row[]='';
            $row[]='';
        }

        $row[] = $units[$item->u_name] ?? '';
        $row[] = $item->gst_rate;
        $row[] = $item->hsn_code;

        fputcsv($file,$row);
    }

    fclose($file);
    exit;
}
   public function migrateItemGST()
{
    try {

        $items = DB::table('manage_items')
            ->where('delete', '0')
            ->where('company_id',35)
            ->get();

        $count = 0;

        foreach ($items as $item) {

            $exists = DB::table('item_gst_rate')
                ->where('item_id', $item->id)
                ->exists();

            if (!$exists) {

                DB::table('item_gst_rate')->insert([
                    'item_id'        => $item->id,
                    'gst_rate'       => $item->gst_rate ?? 0,
                    'item_type'      => $item->item_type ?? 'taxable',
                    'comp_id'        => $item->company_id ?? 0,
                    'effective_from' => '2025-09-22',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $count++;
            }
        }

        //return back()->with('success', "$count items synced!");

    } catch (\Exception $e) {
        dd($e->getMessage());
    }
}
public function getItemGstRate(Request $request){
      $gst_rate = ItemGstRate::select('gst_rate')
               ->where('item_id', $request->item_id)
               ->where('comp_id', Session::get('user_company_id'))
               ->whereDate('effective_from', '<=', $request->txn_date)
               ->orderBy('effective_from', 'desc') // 👈 key fix
               ->first();
      if($gst_rate){
         return response()->json([
            'status'  => true,
            'gst_rate'    => $gst_rate->gst_rate
        ]);
      }else{
         return response()->json([
            'status'  => true,
            'gst_rate'    => 0
        ]);
      }
   }
}