<?php

namespace App\Http\Controllers\StockTransfer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\ManageItems;
use App\Models\StockTransfer;
use App\Models\StockTransferDescription;
use App\Models\StockTransferSundry;
use App\Models\AccountLedger;
use App\Models\VoucherSeriesConfiguration;
use App\Models\ItemLedger;
use App\Models\ItemAverageDetail;
use App\Models\SaleInvoiceConfiguration;
use App\Models\ItemAverage;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\URL;
use Session;
use DB;
use DateTime;
use Gate;
class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    Gate::authorize('action-module', 31); // Keep module ID as per stock transfer permission

    $input = $request->all();
    $from_date = null;
    $to_date = null;

    // Manage session + input dates
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['stock_transfer_from_date' => $from_date, 'stock_transfer_to_date' => $to_date]);
    } elseif (session()->has('stock_transfer_from_date') && session()->has('stock_transfer_to_date')) {
        $from_date = session('stock_transfer_from_date');
        $to_date = session('stock_transfer_to_date');
    }

    Session::put('redirect_url', '');

    // Financial year month array
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07',
        $from . '-08', $from . '-09', $from . '-10', $from . '-11',
        $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];

    $com_id = Session::get('user_company_id');

    // Base query for stock transfers
    $query = StockTransfer::where('company_id', $com_id)
                ->where('status', '1')
                ->where('delete_status', '0');

    if ($from_date && $to_date) {
        $query->whereRaw("STR_TO_DATE(transfer_date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "', '%Y-%m-%d')")
              ->whereRaw("STR_TO_DATE(transfer_date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "', '%Y-%m-%d')")
              ->orderBy('transfer_date', 'asc')
              ->orderBy('voucher_no_prefix', 'asc');
    } else {
        $query->orderBy('id', 'desc')->limit(10);
    }

    $stock_transfers = $query->get()->reverse()->values();

    return view('stockTransfer.index')
        ->with('stock_transfers', $stock_transfers)
        ->with('month_arr', $month_arr)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

 

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('action-module',87);
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
                  ->where('configuration_for','STOCK TRANSFER')
                  ->where('status','1')
                  ->first();
            $voucher_no = StockTransfer::select('voucher_no')                     
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('financial_year','=',Session::get('default_fy'))
                            ->where('series_no','=',$value->series)
                            ->where('delete_status','=','0')
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
        //Item List
        $item = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.id','manage_items.u_name','manage_items.gst_rate','manage_items.name','parameterized_stock_status','config_status','item_groups.id as group_id'])
            ->get(); 
        foreach($item as $key=>$row){
            $item_in_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('in_weight');
            $item_out_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('out_weight');
            $available_item = $item_in_weight-$item_out_weight;
            $item[$key]->available_item = $available_item;        
        }
        
        //Bill Sundry List
        $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->where('adjust_sale_amt', '=', 'No')
                                 ->where('adjust_purchase_amt', '=', 'No')
                                // ->where('nature_of_sundry', '=', 'other')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 ->orderBy('name')
                                 ->get();
        return view('stockTransfer.add_stock_transfer',['series_list'=>$series_list,'item_list'=>$item,"billsundry"=>$billsundry]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Gate::authorize('action-module',87);
        // echo "<pre>";
        // print_r($request->all());die;
        //ashish  
        $validatedData = $request->validate([
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'material_center_from' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'units' => 'required',
            'price' => 'required',
            'amount' => 'required',
        ], [
            'series_no,' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'material_center_from' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'units' => 'required',
            'price' => 'required',
            'amount' => 'required',
        ]);
        //Check Dulicate Invoice Number
        $check_invoice = StockTransfer::where('company_id',Session::get('user_company_id'))
                                ->where('voucher_no',$request->input('voucher_no'))
                                ->where('series_no',$request->input('series_no'))
                                ->where('financial_year','=',Session::get('default_fy'))
                                ->where('delete_status','0')
                                ->first();
        if($check_invoice){
            return $this->failedMessage('Duplicate Invoice No.','stock-transfer/create');
        }
        if($request->input('manual_enter_invoice_no')=='0'){
            $voucher_no = StockTransfer::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('series_no',$request->input('series_no'))
                           ->where('financial_year','=',Session::get('default_fy'))
                           ->where('delete_status','=','0')
                           ->max(\DB::raw("cast(voucher_no as SIGNED)"));
            if(!$voucher_no){
                $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
                    ->where('series',$request->input('series_no'))
                    ->where('configuration_for','STOCK TRANSFER')
                    ->where('status','1')
                    ->first();
                if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                    $voucher_no =  sprintf("%'03d",$series_configuration->invoice_start);
                }else{
                    $voucher_no = "001";
                }
            }else{
               $voucher_no++;
               $voucher_no = sprintf("%'03d", $voucher_no);
            }
        }else{
            $voucher_no = $request->input('voucher_no');
        } 
        $voucher_prefix = $request->input('voucher_prefix');
        $stock_transfer = new StockTransfer();
        $stock_transfer->voucher_no_prefix = $voucher_prefix;
        $stock_transfer->company_id = Session::get('user_company_id');
        $stock_transfer->series_no = $request->input('series_no');
        $stock_transfer->series_no_to = $request->input('to_series');
        $stock_transfer->merchant_gst = $request->input('merchant_gst');
        $stock_transfer->transfer_date = $request->input('date');
        $stock_transfer->voucher_no = $voucher_no;
        $stock_transfer->material_center_from = $request->input('material_center_from');
        $stock_transfer->material_center_to = $request->input('material_center_to');
        $stock_transfer->vehicle_no = $request->input('vehicle_no');
        $stock_transfer->transport_id = $request->input('transport_id');
        $stock_transfer->other_detail = $request->input('other_details');
        $stock_transfer->item_total = $request->input('item_total');
        $stock_transfer->grand_total = $request->input('grand_total');        
        $stock_transfer->financial_year = Session::get('default_fy');
        $stock_transfer->created_by = Session::get('user_id');
        $stock_transfer->created_at = Carbon::now();
        if($stock_transfer->save()){
            $goods_discriptions = $request->input('goods_discription');
            $qtys = $request->input('qty');
            $units = $request->input('units');
            $prices = $request->input('price');
            $amounts = $request->input('amount');
            $sale_item_array = [];$item_average_arr = [];$item_average_total = 0;
            foreach($goods_discriptions as $key => $good){
                if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
                   continue;
                }
                if(array_key_exists($good,$sale_item_array)){
                    $sale_item_array[$good] = $sale_item_array[$good] + $qtys[$key];
                }else{
                    $sale_item_array[$good] = $qtys[$key];
                }
                //From Series Avearge Rate
                $average = ItemAverage::where('item_id',$good)
                        ->where('stock_date','<=',$request->input('date')->toDateString())//$date->toDateString()
                        ->where('series_no',$request->input('series_no'))
                        ->orderBy('stock_date','desc')
                        ->orderBy('id','desc')
                        ->first();
                if($average){
                    $from_price = $average->price;
                    $from_amount = $qtys[$key] * $from_price;
                }else{
                    $opening = ItemLedger::where('item_id',$good)
                                    ->where('series_no',$request->input('series_no'))
                                    ->where('source','-1')
                                    ->first();
                    if($opening){
                        $from_price = $opening->total_price/$opening->in_weight;
                        $from_price = round($from_price,2);
                        $from_amount = $qtys[$key] * $from_price;
                    }else{
                        $from_price = $prices[$key]; 
                        $from_amount = $qtys[$key] * $from_price;
                    }
                }
                array_push($item_average_arr,array("item"=>$good,"quantity"=>$qtys[$key],"price"=>$from_price,"amount"=>$from_amount));
                $item_average_total = $item_average_total + $amounts[$key];
                $desc = new StockTransferDescription;
                $desc->stock_transfer_id = $stock_transfer->id;
                $desc->goods_discription = $good;
                $desc->qty = $qtys[$key];
                $desc->unit = $units[$key];
                $desc->price = $prices[$key];
                $desc->amount = $amounts[$key];
                $desc->save();
                //Remove ITEM LEDGER
                $item_ledger = new ItemLedger();
                $item_ledger->item_id = $good;
                $item_ledger->out_weight = $qtys[$key];
                $item_ledger->series_no = $request->input('series_no');
                $item_ledger->txn_date = $request->input('date');
                $item_ledger->price = $prices[$key];
                $item_ledger->total_price = $amounts[$key];
                $item_ledger->company_id = Session::get('user_company_id');
                $item_ledger->source = 6;
                $item_ledger->source_id = $stock_transfer->id;
                $item_ledger->created_by = Session::get('user_id');
                $item_ledger->created_at = date('d-m-Y H:i:s');
                $item_ledger->save();
                //Add ITEM LEDGER
                $item_ledger = new ItemLedger();
                $item_ledger->item_id = $good;
                $item_ledger->in_weight = $qtys[$key];
                $item_ledger->series_no = $request->input('to_series');
                $item_ledger->txn_date = $request->input('date');
                $item_ledger->price = $prices[$key];
                $item_ledger->total_price = $amounts[$key];
                $item_ledger->company_id = Session::get('user_company_id');
                $item_ledger->source = 6;
                $item_ledger->source_id = $stock_transfer->id;
                $item_ledger->created_by = Session::get('user_id');
                $item_ledger->created_at = date('d-m-Y H:i:s');
                $item_ledger->save();
                
            }
            
            // Bill Sundry 
            $bill_sundrys = $request->input('bill_sundry');
            $tax_amts = $request->input('tax_rate');
            $bill_sundry_amounts = $request->input('bill_sundry_amount');
            $additive_sundry_amount_first = 0;
            $subtractive_sundry_amount_first = 0;
            foreach($bill_sundrys as $key => $bill){
                if($bill_sundry_amounts[$key]=="" || $bill==""){
                   continue;
                }
                $sundry = new StockTransferSundry;
                $sundry->stock_transfer_id = $stock_transfer->id;
                $sundry->bill_sundry = $bill;
                $sundry->rate = $tax_amts[$key];
                $sundry->amount = $bill_sundry_amounts[$key];
                $sundry->save();
                //ADD DATA IN ACCOUNT
                $billsundry = BillSundrys::where('id', $bill)->first();    
                if($billsundry->adjust_sale_amt=='No'){
                   $ledger = new AccountLedger();
                   $ledger->account_id = $billsundry->sale_amt_account;
                   $ledger->credit = $bill_sundry_amounts[$key];
                   $ledger->txn_date = $request->input('date');
                   $ledger->company_id = Session::get('user_company_id');
                   $ledger->financial_year = Session::get('default_fy');
                   $ledger->entry_type = 11;
                   $ledger->entry_type_id = $stock_transfer->id;
                   //$ledger->map_account_id = $request->input('party_id');
                   $ledger->series_no = $request->input('from_series');
                   $ledger->created_by = Session::get('user_id');
                   $ledger->created_at = date('d-m-Y H:i:s');
                   $ledger->save();
                }  

                if($billsundry->adjust_sale_amt=='No'){
                   $ledger = new AccountLedger();
                   $ledger->account_id = $billsundry->sale_amt_account;
                   $ledger->debit = $bill_sundry_amounts[$key];
                   $ledger->txn_date = $request->input('date');
                   $ledger->company_id = Session::get('user_company_id');
                   $ledger->financial_year = Session::get('default_fy');
                   $ledger->entry_type = 11;
                   $ledger->entry_type_id = $stock_transfer->id;
                   //$ledger->map_account_id = $request->input('party_id');
                   $ledger->series_no = $request->input('to_series');
                   $ledger->created_by = Session::get('user_id');
                   $ledger->created_at = date('d-m-Y H:i:s');
                   $ledger->save();
                }  

                if($billsundry->nature_of_sundry=="OTHER"){
                    if($billsundry->bill_sundry_type=="additive"){
                        $additive_sundry_amount_first = $additive_sundry_amount_first + $bill_sundry_amounts[$key];
                    }else if($billsundry->bill_sundry_type=="subtractive"){
                        $subtractive_sundry_amount_first = $subtractive_sundry_amount_first + $bill_sundry_amounts[$key];
                    }
                }
            }
            //Add Remove Data In Average Details table
            foreach ($sale_item_array as $key => $value) {                
                $average_detail = new ItemAverageDetail;
                $average_detail->entry_date = $request->date;
                $average_detail->series_no = $request->input('series_no');
                $average_detail->item_id = $key;
                $average_detail->type = 'STOCK TRANSFER OUT';
                $average_detail->stock_transfer_id = $stock_transfer->id;
                $average_detail->stock_transfer_weight = $value;
                $average_detail->company_id = Session::get('user_company_id');
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
                CommonHelper::RewriteItemAverageByItem($request->date,$key,$request->input('series_no'));
            }
            //Add Add Data In Average Details table
            foreach ($item_average_arr as $key => $value) {
                $subtractive_sundry_amount = 0;$additive_sundry_amount = 0;
                if($additive_sundry_amount_first>0){
                   $additive_sundry_amount = ($value['amount']/$item_average_total)*$additive_sundry_amount_first;
                }
                if($subtractive_sundry_amount_first>0){
                   $subtractive_sundry_amount = ($value['amount']/$item_average_total)*$subtractive_sundry_amount_first;
                }
                $additive_sundry_amount = round($additive_sundry_amount,2);
                $subtractive_sundry_amount = round($subtractive_sundry_amount,2);
                $average_amount = $value['amount'] + $additive_sundry_amount - $subtractive_sundry_amount;
                $average_amount =  round($average_amount,2);
                $average_price = $average_amount/$value['quantity'];
                $average_price =  round($average_price,6);
                //Add Data In Average Details table
                $average_detail = new ItemAverageDetail;
                $average_detail->series_no = $request->input('to_series');
                $average_detail->entry_date = $request->date;
                $average_detail->item_id = $value['item'];
                $average_detail->type = 'STOCK TRANSFER IN';
                $average_detail->stock_transfer_in_id = $stock_transfer->id;
                $average_detail->stock_transfer_in_weight = $value['quantity'];
                $average_detail->stock_transfer_in_amount = $value['amount'];
                $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
                $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
                //$average_detail->purchase_total_amount = $average_amount;
                $average_detail->company_id = Session::get('user_company_id');
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
                CommonHelper::RewriteItemAverageByItem($request->date,$value['item'],$request->input('to_series'));
            }
            session(['previous_url_stock_transfer' => URL::previous()]);
            return redirect('stock-transfer')->withSuccess('Stock Transfer Successfully!');
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
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        $stock_transfer = StockTransfer::where('id', $id)->first();
        if($company_data->gst_config_type == "single_gst") {
            //$GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
             $GstSettings = DB::table('gst_settings')
                                    ->join('states','gst_settings.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $stock_transfer->merchant_gst])
                                    ->select(['states.name as sname','gst_settings.state'])
                                    ->first();
                      
            $seller_info = DB::table('gst_settings')
                            ->join('states','gst_settings.state','=','states.id')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $stock_transfer->merchant_gst,'series'=>$stock_transfer->series_no])
                            ->select(['gst_no','address','pincode','states.name as sname'])
                            ->first();
            if(!$seller_info){
                $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                            ->where(['delete' => '0', 'company_id' => $stock_transfer->company_id,'gst_number'=>$stock_transfer->merchant_gst,'branch_series'=>$stock_transfer->series_no])
                            ->first();
                $state_info = DB::table('states')
                            ->where('id',$GstSettings->state)
                            ->first();
                $seller_info->sname = $state_info->name;
            }
            

            //From Series Info
           
            $from_series_info = DB::table('gst_settings')
                                    ->join('states','gst_settings.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $stock_transfer->merchant_gst,'series'=>$stock_transfer->series_no])
                                    ->select(['gst_no','address','pincode','states.name as sname'])
                                    ->first();
            if(!$from_series_info){
                $from_series_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$stock_transfer->merchant_gst,'branch_series'=>$stock_transfer->series_no])
                            ->first();                
                $from_series_info->sname = $GstSettings->sname;
            }
            //To Series Info            
            $to_series_info = DB::table('gst_settings')
                                    ->join('states','gst_settings.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $stock_transfer->merchant_gst,'mat_center'=>$stock_transfer->material_center_to])
                                    ->select(['gst_no','address','pincode','states.name as sname'])
                                    ->first();
            if(!$to_series_info){
                $to_series_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$stock_transfer->merchant_gst,'branch_matcenter'=>$stock_transfer->material_center_to])
                            ->first();                
                $to_series_info->sname = 'ss';
            }
        }else if($company_data->gst_config_type == "multiple_gst") {
            //From Series Info
            $GstSettings = DB::table('gst_settings_multiple')
                                    ->join('states','gst_settings_multiple.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $stock_transfer->merchant_gst])
                                    ->select(['states.name as sname'])
                                    ->first();
            $from_series_info = DB::table('gst_settings_multiple')
                                    ->join('states','gst_settings_multiple.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $stock_transfer->merchant_gst,'series'=>$stock_transfer->series_no])
                                    ->select(['gst_no','address','pincode','states.name as sname'])
                                    ->first();
            if(!$from_series_info){
                $from_series_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$stock_transfer->merchant_gst,'branch_series'=>$stock_transfer->series_no])
                            ->first();                
                $from_series_info->sname = $GstSettings->sname;
            }
            //To Series Info            
            $to_series_info = DB::table('gst_settings_multiple')
                                    ->join('states','gst_settings_multiple.state','=','states.id')
                                    ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $stock_transfer->merchant_gst,'mat_center'=>$stock_transfer->material_center_to])
                                    ->select(['gst_no','address','pincode','states.name as sname'])
                                    ->first();
            if(!$to_series_info){
                $to_series_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$stock_transfer->merchant_gst,'branch_matcenter'=>$stock_transfer->material_center_to])
                            ->first();                
                $to_series_info->sname = $GstSettings->sname;
            }
        }
        $items_detail = DB::table('stock_transfer_descriptions')
                            ->where('stock_transfer_id', $id)
                            ->where('stock_transfer_descriptions.delete_status', '0')
                            ->where('stock_transfer_descriptions.status', '1')
                            ->select('units.s_name as unit', 'units.id as unit_id', 'stock_transfer_descriptions.qty', 'stock_transfer_descriptions.price', 'stock_transfer_descriptions.amount', 'manage_items.name as items_name', 'manage_items.id as item_id','manage_items.hsn_code','manage_items.gst_rate')
                            ->join('units', 'stock_transfer_descriptions.unit', '=', 'units.id')
                            ->join('manage_items', 'stock_transfer_descriptions.goods_discription', '=', 'manage_items.id')
                        ->get();
        $sundry = DB::table('stock_transfer_sundries')
                        ->join('bill_sundrys','stock_transfer_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('stock_transfer_id', $id)
                        ->where('stock_transfer_sundries.delete_status', '0')
                        ->select('stock_transfer_sundries.bill_sundry','stock_transfer_sundries.rate','stock_transfer_sundries.amount','bill_sundrys.name','nature_of_sundry','bill_sundry_type')
                        ->orderBy('sequence')
                        ->get();
        $gst_detail = DB::table('stock_transfer_sundries')
                        ->select('rate','amount')                     
                        ->where('stock_transfer_id', $id)
                        ->where('rate','!=','0')
                        ->distinct('rate')                       
                        ->get(); 
        $max_gst = DB::table('stock_transfer_sundries')
                        ->select('rate')                     
                        ->where('stock_transfer_id', $id)
                        ->where('rate','!=','0')
                        ->max(\DB::raw("cast(rate as SIGNED)"));
        if(count($gst_detail)>0){
            foreach ($gst_detail as $key => $value){
                $rate = $value->rate;
                $taxable_amount = 0;
                foreach($items_detail as $k1 => $item) {
                    if($item->gst_rate==$rate){
                        $taxable_amount = $taxable_amount + $item->amount;
                    }
                }
                $gst_detail[$key]->rate = $rate;
                if($max_gst==$rate){
                    $sun = StockTransferSundry::join('bill_sundrys','stock_transfer_sundries.bill_sundry','=','bill_sundrys.id')
                              ->select('amount','bill_sundry_type')
                              ->where('sale_id', $id)
                              ->where('nature_of_sundry','OTHER')
                              ->get();
                    foreach ($sun as $k1 => $v1) {
                        if($v1->bill_sundry_type=="additive"){
                            $taxable_amount = $taxable_amount + $v1->amount;
                        }else if($v1->bill_sundry_type=="subtractive"){
                            $taxable_amount = $taxable_amount - $v1->amount;
                        }
                    }
                }
                $gst_detail[$key]->taxable_amount = $taxable_amount;
            }
        }              
        $bank_detail = DB::table('banks')
                            ->where('company_id', Session::get('user_company_id'))
                            ->select('banks.*')
                            ->first(); 
        $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
        Session::put('redirect_url','');
                                                        $financial_year = Session::get('default_fy');      
                                                        $y =  explode("-",$financial_year);
                                                        $from = $y[0];
                                                        $from = DateTime::createFromFormat('y', $from);
                                                        $from = $from->format('Y');
                                                        $to = $y[1];
                                                        $to = DateTime::createFromFormat('y', $to);
                                                        $to = $to->format('Y');
                                                        $month_arr = array($from.'-04',$from.'-05',$from.'-06',$from.'-07',$from.'-08',$from.'-09',$from.'-10',$from.'-11',$from.'-12',$to.'-01',$to.'-02',$to.'-03');
        return view('stockTransfer.stock_transfer_invoice')->with(['items_detail' => $items_detail,'month_arr' => $month_arr, 'sundry' => $sundry, 'company_data' => $company_data, 'stock_transfer' => $stock_transfer,'bank_detail' => $bank_detail,'gst_detail'=>$gst_detail,'from_series_info'=>$from_series_info,'to_series_info'=>$to_series_info,'configuration'=>$configuration]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        Gate::authorize('action-module',65);
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
        
        //Item List
        $item = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.id','manage_items.u_name','manage_items.gst_rate','manage_items.name','parameterized_stock_status','config_status','item_groups.id as group_id'])
            ->get(); 
        foreach($item as $key=>$row){
            $item_in_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('in_weight');
            $item_out_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('out_weight');
            $available_item = $item_in_weight-$item_out_weight;
            $item[$key]->available_item = $available_item;        
        }
        //Bill Sundry List
        $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->where('adjust_sale_amt', '=', 'No')
                                 ->where('adjust_purchase_amt', '=', 'No')
                                // ->where('nature_of_sundry', '=', 'other')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 ->orderBy('name')
                                 ->get();
        $stock_transfer = StockTransfer::where('id', $id)->first();
        $stock_transfer_desc = StockTransferDescription::join('units','stock_transfer_descriptions.unit','=','units.id')
                                                        ->where('stock_transfer_id', $id)
                                                        ->where('stock_transfer_descriptions.delete_status','0')
                                                        ->where('stock_transfer_descriptions.status','1')
                                                        ->select(['stock_transfer_descriptions.*','units.s_name'])
                                                        ->get();
        $stock_transfer_sundry = StockTransferSundry::join('bill_sundrys','stock_transfer_sundries.bill_sundry','=','bill_sundrys.id')
                                                    ->where('stock_transfer_id', $id)
                                                    ->where('stock_transfer_sundries.delete_status','0')
                                                     ->where('stock_transfer_sundries.status','1')
                                                    ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','stock_transfer_sundries.*'])
                                                    ->get();

                                                    
        return view('stockTransfer.edit_stock_transfer',['series_list'=>$series_list,'item_list'=>$item,"billsundry"=>$billsundry,"stock_transfer"=>$stock_transfer,"stock_transfer_desc"=>$stock_transfer_desc,"stock_transfer_sundry"=>$stock_transfer_sundry]);
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
        Gate::authorize('action-module',65);
        // echo "<pre>";
        // print_r($request->all());die;
        $validatedData = $request->validate([
            'series_no' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'material_center_from' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'units' => 'required',
            'price' => 'required',
            'amount' => 'required',
        ], [
            'series_no,' => 'required',
            'date' => 'required',
            'voucher_no' => 'required',
            'material_center_from' => 'required',
            'goods_discription' => 'required',
            'qty' => 'required',
            'units' => 'required',
            'price' => 'required',
            'amount' => 'required',
        ]);
        $stock_transfer = StockTransfer::find($id);
        $last_date =  $stock_transfer->transfer_date;
        $stock_transfer->transfer_date = $request->input('date');
        $stock_transfer->vehicle_no = $request->input('vehicle_no');
        $stock_transfer->transport_id = $request->input('transport_id');
        $stock_transfer->other_detail = $request->input('other_details');
        $stock_transfer->item_total = $request->input('item_total');
        $stock_transfer->grand_total = $request->input('grand_total');        
        $stock_transfer->financial_year = Session::get('default_fy');
        $stock_transfer->updated_by = Session::get('user_id');
        $stock_transfer->updated_at = Carbon::now();
        if($stock_transfer->save()){
            $goods_discriptions = $request->input('goods_discription');
            $qtys = $request->input('qty');
            $units = $request->input('units');
            $prices = $request->input('price');
            $amounts = $request->input('amount');
            $desc_item_arr = StockTransferDescription::where('stock_transfer_id',$id)->pluck('goods_discription')->toArray();
            ItemAverageDetail::where('stock_transfer_in_id',$id)
                           ->where('type','STOCK TRANSFER IN')
                           ->delete();    
            ItemAverageDetail::where('stock_transfer_id',$id)
                           ->where('type','STOCK TRANSFER OUT')
                           ->delete();
            StockTransferDescription::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            StockTransferSundry::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            AccountLedger::where('entry_type',11)
                        ->where('entry_type_id',$id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemLedger::where('source',6)
                        ->where('source_id',$id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            $sale_item_array = [];$item_average_arr = [];$item_average_total = 0;
            foreach($goods_discriptions as $key => $good){
                if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
                   continue;
                }
                if(array_key_exists($good,$sale_item_array)){
                    $sale_item_array[$good] = $sale_item_array[$good] + $qtys[$key];
                }else{
                    $sale_item_array[$good] = $qtys[$key];
                }
                array_push($item_average_arr,array("item"=>$good,"quantity"=>$qtys[$key],"price"=>$prices[$key],"amount"=>$amounts[$key]));
                $item_average_total = $item_average_total + $amounts[$key];
                $desc = new StockTransferDescription;
                $desc->stock_transfer_id = $stock_transfer->id;
                $desc->goods_discription = $good;
                $desc->qty = $qtys[$key];
                $desc->unit = $units[$key];
                $desc->price = $prices[$key];
                $desc->amount = $amounts[$key];
                $desc->save();
                //Remove ITEM LEDGER
                $item_ledger = new ItemLedger();
                $item_ledger->item_id = $good;
                $item_ledger->out_weight = $qtys[$key];
                $item_ledger->series_no = $request->input('series_no');
                $item_ledger->txn_date = $request->input('date');
                $item_ledger->price = $prices[$key];
                $item_ledger->total_price = $amounts[$key];
                $item_ledger->company_id = Session::get('user_company_id');
                $item_ledger->source = 6;
                $item_ledger->source_id = $stock_transfer->id;
                $item_ledger->created_by = Session::get('user_id');
                $item_ledger->created_at = date('d-m-Y H:i:s');
                $item_ledger->save();
                //Add ITEM LEDGER
                $item_ledger = new ItemLedger();
                $item_ledger->item_id = $good;
                $item_ledger->in_weight = $qtys[$key];
                $item_ledger->series_no = $request->input('to_series');
                $item_ledger->txn_date = $request->input('date');
                $item_ledger->price = $prices[$key];
                $item_ledger->total_price = $amounts[$key];
                $item_ledger->company_id = Session::get('user_company_id');
                $item_ledger->source = 6;
                $item_ledger->source_id = $stock_transfer->id;
                $item_ledger->created_by = Session::get('user_id');
                $item_ledger->created_at = date('d-m-Y H:i:s');
                $item_ledger->save();
                
            }
            
            // Bill Sundry 
            $bill_sundrys = $request->input('bill_sundry');
            $tax_amts = $request->input('tax_rate');
            $bill_sundry_amounts = $request->input('bill_sundry_amount');
            $additive_sundry_amount_first = 0;
            $subtractive_sundry_amount_first = 0;
            if($request->input('bill_sundry')){
                foreach($bill_sundrys as $key => $bill){
                    if($bill_sundry_amounts[$key]=="" || $bill==""){
                       continue;
                    }
                    $sundry = new StockTransferSundry;
                    $sundry->stock_transfer_id = $stock_transfer->id;
                    $sundry->bill_sundry = $bill;
                    $sundry->rate = $tax_amts[$key];
                    $sundry->amount = $bill_sundry_amounts[$key];
                    $sundry->save();
                    //ADD DATA IN ACCOUNT
                     $billsundry = BillSundrys::where('id', $bill)->first();    
                if($billsundry->adjust_sale_amt=='No'){
                   $ledger = new AccountLedger();
                   $ledger->account_id = $billsundry->sale_amt_account;
                   $ledger->credit = $bill_sundry_amounts[$key];
                   $ledger->txn_date = $request->input('date');
                   $ledger->company_id = Session::get('user_company_id');
                   $ledger->financial_year = Session::get('default_fy');
                   $ledger->entry_type = 11;
                   $ledger->entry_type_id = $stock_transfer->id;
                   //$ledger->map_account_id = $request->input('party_id');
                   $ledger->series_no = $request->input('from_series');
                   $ledger->created_by = Session::get('user_id');
                   $ledger->created_at = date('d-m-Y H:i:s');
                   $ledger->save();
                }  

                if($billsundry->adjust_sale_amt=='No'){
                   $ledger = new AccountLedger();
                   $ledger->account_id = $billsundry->sale_amt_account;
                   $ledger->debit = $bill_sundry_amounts[$key];
                   $ledger->txn_date = $request->input('date');
                   $ledger->company_id = Session::get('user_company_id');
                   $ledger->financial_year = Session::get('default_fy');
                   $ledger->entry_type = 11;
                   $ledger->entry_type_id = $stock_transfer->id;
                   //$ledger->map_account_id = $request->input('party_id');
                   $ledger->series_no = $request->input('to_series');
                   $ledger->created_by = Session::get('user_id');
                   $ledger->created_at = date('d-m-Y H:i:s');
                   $ledger->save();
                }  
                    if($billsundry->nature_of_sundry=="OTHER"){
                        if($billsundry->bill_sundry_type=="additive"){
                            $additive_sundry_amount_first = $additive_sundry_amount_first + $bill_sundry_amounts[$key];
                        }else if($billsundry->bill_sundry_type=="subtractive"){
                            $subtractive_sundry_amount_first = $subtractive_sundry_amount_first + $bill_sundry_amounts[$key];
                        }
                    }
                }
            }
            
            //Add Remove Data In Average Details table
            foreach ($sale_item_array as $key => $value) {                
                $average_detail = new ItemAverageDetail;
                $average_detail->entry_date = $request->date;
                $average_detail->series_no = $request->input('series_no');
                $average_detail->item_id = $key;
                $average_detail->type = 'STOCK TRANSFER OUT';
                $average_detail->stock_transfer_id = $stock_transfer->id;
                $average_detail->stock_transfer_weight = $value;
                $average_detail->company_id = Session::get('user_company_id');
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
                 $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
                CommonHelper::RewriteItemAverageByItem($lower_date,$key,$request->input('series_no'));
            }
            //Add Add Data In Average Details table
            foreach ($item_average_arr as $key => $value) {
                $subtractive_sundry_amount = 0;$additive_sundry_amount = 0;
                if($additive_sundry_amount_first>0){
                   $additive_sundry_amount = ($value['amount']/$item_average_total)*$additive_sundry_amount_first;
                }
                if($subtractive_sundry_amount_first>0){
                   $subtractive_sundry_amount = ($value['amount']/$item_average_total)*$subtractive_sundry_amount_first;
                }
                $additive_sundry_amount = round($additive_sundry_amount,2);
                $subtractive_sundry_amount = round($subtractive_sundry_amount,2);
                $average_amount = $value['amount'] + $additive_sundry_amount - $subtractive_sundry_amount;
                $average_amount =  round($average_amount,2);
                $average_price = $average_amount/$value['quantity'];
                $average_price =  round($average_price,6);
                //Add Data In Average Details table
                $average_detail = new ItemAverageDetail;
                $average_detail->entry_date = $request->date;
                $average_detail->item_id = $value['item'];
                $average_detail->type = 'STOCK TRANSFER IN';
                $average_detail->series_no = $request->input('to_series');
                $average_detail->stock_transfer_in_id = $stock_transfer->id;
                $average_detail->stock_transfer_in_weight = $value['quantity'];
                $average_detail->stock_transfer_in_amount = $value['amount'];
                $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
                $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
                $average_detail->purchase_total_amount = $average_amount;
                $average_detail->company_id = Session::get('user_company_id');
                $average_detail->created_at = Carbon::now();
                $average_detail->save();
                $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
                CommonHelper::RewriteItemAverageByItem($lower_date,$value['item'],$request->input('to_series'));
            }
            foreach ($desc_item_arr as $key => $value) {
                if(!array_key_exists($value, $sale_item_array)){
                   CommonHelper::RewriteItemAverageByItem($request->date,$value);
                }
             }
             session(['previous_url_stock_transfer_edit' => URL::previous()]);
            return redirect('stock-transfer')->withSuccess('Stock Transfer Successfully!');
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
        Gate::authorize('action-module',66);
        $stock_transfer =  StockTransfer::find($id);
        $stock_transfer->delete_status = '1';
        $stock_transfer->deleted_at = Carbon::now();
        $stock_transfer->deleted_by = Session::get('user_id');
        $stock_transfer->update();
        if($stock_transfer) {
            ItemAverageDetail::where('stock_transfer_in_id',$id)
                           ->where('type','STOCK TRANSFER IN')
                           ->delete();    
            ItemAverageDetail::where('stock_transfer_id',$id)
                           ->where('type','STOCK TRANSFER OUT')
                           ->delete();  

            $desc = StockTransferDescription::where('stock_transfer_id',$id)
                              ->get();
            foreach ($desc as $key => $value) {
                CommonHelper::RewriteItemAverageByItem($stock_transfer->date,$value->goods_discription,$stock_transfer->series_no);
                CommonHelper::RewriteItemAverageByItem($stock_transfer->date,$value->goods_discription,$stock_transfer->series_no_to);
            }
            StockTransferDescription::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            StockTransferSundry::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            AccountLedger::where('entry_type',11)
                        ->where('entry_type_id',$id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemLedger::where('source',6)
                        ->where('source_id',$id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            return redirect('stock-transfer')->withSuccess('Deleted Successfully!');
        }        
    }
    public function failedMessage($msg,$url){
        return redirect($url)->withError($msg);
     }
}
