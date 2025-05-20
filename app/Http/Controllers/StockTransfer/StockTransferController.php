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
use Carbon\Carbon;
use Session;
use DB;
class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stock_transfers = StockTransfer::where('company_id', Session::get('user_company_id'))
                                        ->where('status', '1')
                                        ->where('delete_status', '0')
                                        ->orderBy('id', 'desc')
                                        ->get();
        // return view('stock_transfer.index', compact('stock_transfers'));
        return view('stockTransfer.index',['stock_transfers'=>$stock_transfers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
                                 ->where('nature_of_sundry', '=', 'other')
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
            foreach($goods_discriptions as $key => $good){
                if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
                   continue;
                }
                $desc = new StockTransferDescription;
                $desc->stock_transfer_id = $stock_transfer->id;
                $desc->goods_discription = $good;
                $desc->qty = $qtys[$key];
                $desc->unit = $units[$key];
                $desc->price = $prices[$key];
                $desc->amount = $amounts[$key];
                $desc->save();
                //ADD ITEM LEDGER
                // $item_ledger = new ItemLedger();
                // $item_ledger->item_id = $good;
                // $item_ledger->out_weight = $qtys[$key];
                // $item_ledger->txn_date = $request->input('date');
                // $item_ledger->price = $prices[$key];
                // $item_ledger->total_price = $amounts[$key];
                // $item_ledger->company_id = Session::get('user_company_id');
                // $item_ledger->source = 1;
                // $item_ledger->source_id = $sale->id;
                // $item_ledger->created_by = Session::get('user_id');
                // $item_ledger->created_at = date('d-m-Y H:i:s');
                // $item_ledger->save();
                
            }
            // Bill Sundry 
            $bill_sundrys = $request->input('bill_sundry');
            $tax_amts = $request->input('tax_rate');
            $bill_sundry_amounts = $request->input('bill_sundry_amount');
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
                   $ledger->created_by = Session::get('user_id');
                   $ledger->created_at = date('d-m-Y H:i:s');
                   $ledger->save();
                }
            }
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stock_transfer =  StockTransfer::find($id);
        $stock_transfer->delete_status = '1';
        $stock_transfer->deleted_at = Carbon::now();
        $stock_transfer->deleted_by = Session::get('user_id');
        $stock_transfer->update();
        if($stock_transfer) {
            StockTransferDescription::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            StockTransferSundry::where('stock_transfer_id',$id)
                        ->update(['delete_status'=>'1']);
            AccountLedger::where('entry_type',11)
                        ->where('entry_type_id',$id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);            
            return redirect('stock-transfer')->withSuccess('Deleted Successfully!');
        }        
    }
}
