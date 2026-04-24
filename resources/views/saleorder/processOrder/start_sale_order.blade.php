@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    /* .remove-item-btn {
        position: absolute;
        top: 155px;
        right: 10px;
    }
    .remove-gsm-btn {
        background: #ff9933;
        border: none;
        color: white;
        padding: 2px 6px;
        cursor: pointer;
        border-radius: 3px;
        margin-left: 5px;
    }
    .remove-gsm-btn:hover {
        background: #e68a00;
    } */
    /* Increase height of select2 single box */
    .select2-container .select2-selection--single {
        height: 48px;              /* set your desired height */
        line-height: 45px;         /* aligns text vertically */
        padding: 6px 12px;         /* optional, adds spacing inside */
        font-size: 14px;           /* optional, bigger text */
        border-radius: 8px;
        width: 200px;
    }

    /* Adjust the arrow alignment */
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 50%;
        transform: translateY(-50%);
    }
    
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Start Sale Order</h5>

                <div class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Bill To</label>
                            <input type="text" class="form-control" value="{{$saleOrder->billTo->account_name}}" id="bill_to" readonly>
                            <input type="hidden" id="bill_to_id" value="{{$saleOrder->billTo->id}}">
                        </div>
                        <div class="col-md-3">
                            <label for="shipp_to" class="form-label font-14 font-heading">Shipp To</label>
                            <input type="text" class="form-control" value="{{$saleOrder->shippTo->account_name}}" id="shipp_to" readonly>
                            <input type="hidden" id="shipp_to_id" value="{{$saleOrder->shippTo->id}}">
                        </div>
                        <div class="col-md-3">
                            <label for="purchase_order_no" class="form-label font-14 font-heading">Purchase Order No.</label>
                            <input type="text" class="form-control" value="{{$saleOrder->purchase_order_no}}" id="purchase_order_no" readonly>
                        </div>
                        <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Purchase Order Date</label>
                                <input type="text"
                                       class="form-control"
                                       style="text-align:left; overflow:visible;"
                                       value="{{ !empty($saleOrder->purchase_order_date) 
                                           ? date('d-m-Y', strtotime($saleOrder->purchase_order_date)) 
                                           : '' }}"
                                       readonly>
                            </div>
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Freight</label>
                            <input type="text"  class="form-control" value="@if($saleOrder->freight==1) Yes @else No @endif" readonly>
                            <input type="hidden" id="freight" value="{{$saleOrder->freight}}">
                        </div>
                    </div>
                     <div id="items_container">
                        @php $item_index = 1; @endphp
                        @foreach($saleOrder->items as $key => $value)
                            @php $sizeArr = []; @endphp
                            @foreach($value->itemSize as $size_key => $size)
                                @php $sizeArr[$size->size][] = array("weight"=>$size->weight,"reel_no"=>$size->reel_no,"id"=>$size->id);  @endphp
                            @endforeach
                            
                            <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-14 font-heading">Item</label>
                                        <input type="text" class="form-control" value="{{$value->item->name}}" readonly id="item_id_{{$item_index}}">
                                        <input type="hidden" class="item" value="{{$value->item->id}}" data-item_index="{{$item_index}}" data-item_name="{{$value->item->name}}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="price_{{$item_index}}" class="form-label font-14 font-heading">Price</label>
                                        <input type="text" class="form-control" value="{{$value->price}}" readonly>
                                        <input type="hidden" class="price" id="price_{{$item_index}}" value="@if($value->bill_price){{$value->bill_price}}@else{{$value->price}}@endif">
                                    </div>
                                    {{-- <div class="clearfix"></div> --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="unit_{{$item_index}}" class="form-label font-14 font-heading">Unit</label>
                                        <input type="text" class="form-control" value="{{$value->unitMaster->s_name}}" id="unit_{{$item_index}}" readonly>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="sub_unit_{{$item_index}}" class="form-label font-14 font-heading">Sub Unit</label>
                                        <input type="text" class="form-control" value="{{$value->sub_unit}}" readonly id="sub_unit_{{$item_index}}">
                                    </div>
                                </div>

                                <!-- GSM / Sizes / Reels -->
                                <div class="row" id="dynamic_gsm_1">
                                    @php $gsm_index = 1; @endphp
                                    @foreach($value->gsms as $k1 => $gsm)
                                         <div class="col-md-6 gsm-block" id="gsm_block_1_1">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td style="width: 40%;">GSM</td>
                                                    <td style="text-align: center">{{$gsm->gsm}}<input type="hidden" class="gsm_{{$item_index}}" data-index="{{$gsm_index}}" value="{{$gsm->gsm}}"></td>
                                                </tr>
                                            </table>
                                            <table class="table table-bordered" id="table_1_1">
                                                <tr>
                                                    <td style="width:20%">SIZES</td>
                                                    <td style="width:20%" class="qty_title_1">{{$value->unitMaster->s_name}}</td>
                                                    <td style="width:60%"></td>
                                                </tr>
                                                @php $qty_total = 0; $detail_index = 1;@endphp
                                                @foreach($gsm->details as $k2 => $detail)
                                                    @php                                                    
                                                    $reel_count = '';
                                                    if(isset($selected_weight[$value->item->id."X".$detail->size."X".$gsm->gsm])){
                                                        $reel_count = count($selected_weight[$value->item->id."X".$detail->size."X".$gsm->gsm]);
                                                    }
                                                    
                                                    @endphp
                                                     <tr>
                                                        <td>
                                                            <input type="text" name="items[1][gsms][1][details][{{ $k2 }}][size]" class="form-control size size_1_1 size_value_{{$item_index}}_{{$gsm_index}}" value="{{$detail->size}}" data-detail_index={{$detail_index}} readonly >
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[1][gsms][1][details][{{ $k2 }}][reel]" class="form-control quantity quantity_1_1" value="{{$detail->quantity}}" data-item_id="1" data-gsm_id="1" data-quantity_id="{{ $k2 }}"  readonly>
                                                        </td>
                                                        @php $reel_weight_arr = [];@endphp
                                                        <td style="display: inline-flex">
                                                            @if(isset($sizeArr[$detail->size."X".$gsm->gsm]))
                                                                @foreach($sizeArr[$detail->size."X".$gsm->gsm] as $reel_key => $reel_weight)
                                                                    @php array_push($reel_weight_arr,$reel_weight);@endphp
                                                                    
                                                                @endforeach
                                                            @endif
                                                            @if($value->SaleOrderSettingUnitMaster->unit_type=="KG1")
                                                            
                                                                <select class="form-select order_quantity @if($value->SaleOrderSettingUnitMaster->unit_type=="KG") order_weight_kg_{{$item_index}}_{{$gsm_index}} order_weight_kg_{{$item_index}}_{{$gsm_index}}_{{$detail_index}} order_weight_detail_kg_{{$detail->id}}@endif size_weight_{{$item_index}}_{{$gsm_index}}" data-actual_qty="{{$detail->quantity}}" data-unit_type="{{$value->SaleOrderSettingUnitMaster->unit_type}}" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" data-current_index="1" data-detail_row_id="{{$detail->id}}">
                                                                    <option value="">Select Weight</option>
                                                                    @if(isset($sizeArr[$detail->size."X".$gsm->gsm]))
                                                                        @foreach($sizeArr[$detail->size."X".$gsm->gsm] as $reel_key => $reel_weight)
                                                                            <option value="{{$reel_weight['weight']}}" data-id="{{$reel_weight['id']}}">Reel No.-{{$reel_weight['reel_no']}} ({{$reel_weight['weight']}})</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                                <input type="hidden" id='reel_weight_id_{{$item_index}}_{{$gsm_index}}_{{$detail_index}}_1' class='reel_weight_id_{{$detail->id}}'>
                                                            @elseif ($value->SaleOrderSettingUnitMaster->unit_type=="REEL" || $value->SaleOrderSettingUnitMaster->unit_type=="KG")
                                                                
                                                                <input type="text" style="padding:3px;" class="form-control order_quantity @if($value->SaleOrderSettingUnitMaster->unit_type=="KG") order_weight_kg_{{$item_index}}_{{$gsm_index}} order_weight_kg_{{$item_index}}_{{$gsm_index}}_{{$detail_index}} order_weight_detail_kg_{{$detail->id}}@endif" data-actual_qty="{{$detail->quantity}}" data-unit_type="{{$value->SaleOrderSettingUnitMaster->unit_type}}" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" placeholder="Reel" data-detail_row_id="{{$detail->id}}" data-weight="{{json_encode($reel_weight_arr)}}"  data-selected_weight="@if($reel_count!=''){{json_encode($selected_weight[$value->item->id."X".$detail->size."X".$gsm->gsm])}}@endif" value="{{$detail->estimate_quantity}}">
                                                            @endif
                                                            @if($value->SaleOrderSettingUnitMaster->unit_type=="REEL" || $value->SaleOrderSettingUnitMaster->unit_type=="KG")
                                                                <span id="weight_box_{{$item_index}}_{{$gsm_index}}_{{$detail_index}}"></span>
                                                            @elseif($value->SaleOrderSettingUnitMaster->unit_type=="KG1")
                                                                <svg style="color: green;cursor:pointer;margin-left:5px;float:right" class="bg-primary rounded-circle add_weight" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" data-actual_qty="{{$detail->quantity}}" data-detail_row_id="{{$detail->id}}" data-weight="{{json_encode($reel_weight_arr)}}" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/></svg>
                                                                <span id="weight_box_{{$item_index}}_{{$gsm_index}}_{{$detail_index}}"></span>
                                                            @endif
                                                            
                                                        </td>
                                                    </tr>
                                                    @php $qty_total+= $detail->quantity; $detail_index++; @endphp
                                                @endforeach
                                                <tr>
                                                    <th style="text-align: center">Total</th>
                                                    <td>
                                                        <input type="number" class="form-control quantity_total" id="quantity_total_1_1" value="{{$qty_total}}" readonly>
                                                    </td>
                                                    <td><input type="number" class="form-control qualty_total_qty order_quantity_total_{{$item_index}}" id="order_quantity_total_{{$item_index}}_{{$gsm_index}}"  readonly></td>
                                                </tr>
                                            </table>
                                            {{-- <span class="add_row" data-item="1" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span> --}}
                                        </div>
                                        @php $gsm_index++; @endphp
                                    @endforeach
                                   
                                    
                                </div>
                            </div>
                            @php $item_index++; @endphp
                        @endforeach
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-end">
                            <h4>Total Weight: <span id="total_weight_all">0</span></h4>
                        </div>
                    </div>
                    <br>
                    <div class="d-flex">
                        <div class="ms-auto">
                            <input type="button" value="NEXT" class="btn btn-primary start_order">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
<!-- ========== Review Sale Order Modal ========== -->
<div class="modal fade" id="nextStepModal" tabindex="-1" aria-labelledby="nextStepModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-semibold" id="nextStepModalLabel">Review Sale Order Details ({{ $saleOrder->billTo->account_name ?? '-' }} - {{ \Carbon\Carbon::parse($saleOrder->created_at)->format('d M Y') ?? '-' }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">                
                <div class="table-responsive">
                    <table class="table table-bordered align-middle challan_table">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Size</th>
                                <th>Weight</th>
                                <th>Reel</th>
                                <th>kgs</th>
                                <th>Price</th>
                                <th>Amount(₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <h3 class="fw-semibold text-primary mb-1 text-center">Vehicle Details</h3>                    
                        <select class="form-select" id="vehicle_info">
                            <option value="">SELECT VEHICLE</option>
                            <option value="TO PAY" data-type="to_pay" @if($saleOrder->freight_type=="to_pay") selected @endif>TO PAY</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{$vehicle->id}}" data-type="vehicle" @if($saleOrder->freight_vehicle_id==$vehicle->id) selected @endif>VEHICLE - {{$vehicle->vehicle_no}}</option>
                            @endforeach
                            @foreach($selectedTransporters as $transporter)
                                <option value="{{$transporter->account_id}}" data-type="transporter" @if($saleOrder->freight_transporter_id==$transporter->account_id) selected @endif>TRANSPORTER - {{$transporter->account_name}}</option>  
                            @endforeach
                            <option value="PARTY VEHICLE" data-type="party_vehicle" @if($saleOrder->freight_type=="party_vehicle") selected @endif>PARTY VEHICLE</option>
                        </select>
                    </div>
                    <div class="mb-3" style="display: none" id="to_pay_freight_div">
                        TO PAY FREIGHT : 
                        <input type="text" class="form-control" id="to_pay_freight"  style="width: 200px; display: inline-block; margin-left: 10px;" value="{{$saleOrder->location_price}}">
                        OTHER CHARGES : 
                        <input type="text" class="form-control" id="to_pay_other_charges" style="width: 200px; display: inline-block; margin-left: 10px;" placeholder="OTHER CHARGES" value="{{$saleOrder->other_freight_amount}}">
                    </div>
                    <div class="mb-3" style="display: none" id="vehicle_freight_div">
                        {{$saleOrder->shippTo->location}} FREIGHT : 
                        <input type="text" class="form-control" id="vehicle_freight"  style="width: 200px; display: inline-block; margin-left: 10px;" value="{{$saleOrder->location_price}}">
                    </div>
                    <div class="mb-3" style="display: none" id="transporter_freight_div">
                        {{$saleOrder->shippTo->location}} FREIGHT : 
                        <input type="text" class="form-control" id="transporter_freight"  style="width: 200px; display: inline-block; margin-left: 10px;" value="{{$saleOrder->location_price}}">
                        OTHER CHARGES : 
                        <input type="text" class="form-control" id="transporter_other_charges" style="width: 200px; display: inline-block; margin-left: 10px;" placeholder="OTHER CHARGES" value="{{$saleOrder->other_freight_amount}}">
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                <button type="button" id="reviewNextBtn" class="btn btn-success px-4">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- ========== Pending Order Modal ========== -->
<div class="modal fade" id="pendingOrderModal" tabindex="-1" aria-labelledby="pendingOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="pendingOrderLabel">Pending Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="mb-3 fw-medium text-secondary">
                        Do you want to create an order for pending items?
                    </p>
                    <div class="d-flex justify-content-center gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="order_confirmation" id="confirmYes" value="1">
                            <label class="form-check-label" for="confirmYes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="order_confirmation" id="confirmNo" value="0">
                            <label class="form-check-label" for="confirmNo">No</label>
                        </div>
                    </div>
                    <input type="hidden" id="sale_order_url">
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-success px-4 confirmation_btn">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>





@include('layouts.footer')

<script>
    var current_index = 1;
    var sale_id = "{{$sale_id}}";
    var expense_arr = JSON.parse('@json($expense)');
    var account_location = "{{$saleOrder->shippTo->location}}";
    var location_price = "{{$saleOrder->location_price}}";
    if(account_location==""){
        alert("Please Update Shipp To Account Location");
    }
    if(account_location!="" && location_price==""){
        
        alert("Please Update "+account_location+" Location Price");
    }
    function calculateTotalWeight() {
        let total_weight = 0;
        $(".qualty_total_qty").each(function(){
            let val = parseFloat($(this).val()) || 0;
            total_weight += val;
        });
        $("#total_weight_all").text(total_weight);
    }
$(document).ready(function(){
    initSelect2();
    var sale_order_status = "{{$saleOrder->status}}";
   
    $(".order_quantity").each(function(){
        $(this).change();
    });
    $(".add_weight").click(function(){
        current_index++;
        let item_index = $(this).attr('data-item_index');
        let gsm_index = $(this).attr('data-gsm_index');
        let detail_index = $(this).attr('data-detail_index');
        let actual_qty = $(this).attr('data-actual_qty');
        let detail_row_id = $(this).attr('data-detail_row_id');
        let size_weights = JSON.parse($(this).attr('data-weight'));
        weight_box_html = "";
        let weight_box_option = "";
        size_weights.forEach(function(weight){
            weight_box_option+="<option value='"+weight['weight']+"' data-id='"+weight['id']+"'>Reel No.-"+weight['reel_no']+"("+weight['weight']+")</option>";
        });
        weight_box_html+="<select class='form-select order_quantity order_weight_kg_"+item_index+"_"+gsm_index+" order_weight_kg_"+item_index+"_"+gsm_index+"_"+detail_index+" order_weight_detail_kg_"+detail_row_id+"' data-item_index="+item_index+" data-gsm_index="+gsm_index+" data-detail_index="+detail_index+" data-actual_qty="+actual_qty+" data-detail_row_id="+detail_row_id+" data-unit_type='KG' data-current_index="+current_index+"><option value=''>Select Weight</option>"+weight_box_option+"</select> <input type='hidden' id='reel_weight_id_"+item_index+"_"+gsm_index+"_"+detail_index+"_"+current_index+"' class='reel_weight_id_"+detail_row_id+"'><br>";

        // weight_box_html+="<input type='text' class='form-control order_quantity order_weight_kg_"+item_index+"_"+gsm_index+" order_weight_kg_"+item_index+"_"+gsm_index+"_"+detail_index+" order_weight_detail_kg_"+detail_row_id+"' style='margin-left: 6px;' placeholder='KG' data-item_index='"+item_index+"' data-gsm_index='"+gsm_index+"' data-detail_index='"+detail_index+"' data-actual_qty='"+actual_qty+"' data-detail_row_id='"+detail_row_id+"' data-unit_type='KG'>";
        $("#weight_box_"+item_index+"_"+gsm_index+"_"+detail_index).append(weight_box_html);
        initSelect2("#weight_box_" + item_index + "_" + gsm_index + "_" + detail_index);

    });
    $("#reviewNextBtn").click(function(){
        let bill_to_id = $("#bill_to_id").val();
        let shipp_to_id = $("#shipp_to_id").val();
        let freight = $("#freight").val();
        let item_arr = [];
        let sale_order_id = "{{$saleOrder->id}}";
        let vehicle_info = $("#vehicle_info").val();
        let vehicle_info_type = $("#vehicle_info option:selected").attr('data-type');
        
        if(vehicle_info==""){
            alert("Please select vehicle information.");
            return;
        }
        if(vehicle_info_type=="to_pay"){
            if($("#to_pay_freight").val()==""){
                alert("Please enter to pay freight.");
                return;
            }
        }
        if(vehicle_info_type=="transporter"){
            if($("#transporter_freight").val()==""){
                alert("Please enter transporter freight.");
                return;
            }
            if(expense_arr==null || expense_arr.length==0){                
                alert("Please add transporter freight expense account.");
                    return;
            }
        }
        if(vehicle_info_type=="vehicle"){
             if($("#vehicle_freight").val()==""){
                alert("Please enter vehicle freight.");
                return;
            }
        }
        $(".item").each(function(){
            let item_id = $(this).val();
            let item_index = $(this).attr('data-item_index');
            let price = $("#price_"+item_index).val();
            let total_weight = 0;
            $(".order_quantity_total_" + item_index).each(function() {
                let val = parseFloat($(this).val()) || 0;
                total_weight += val;
            });
            if(total_weight>0){
                item_arr.push({'item_id':item_id,'price':price,'total_weight':total_weight})
            }
        });
        let sale_enter_data = [];let pending_order_html = "";pending_order_status = 0;
        $(".order_quantity").each(function(){
            let item_index = $(this).attr('data-item_index');
            let gsm_index = $(this).attr('data-gsm_index');
            let detail_index = $(this).attr('data-detail_index');
            let unit_type = $(this).attr('data-unit_type');
            let detail_row_id = $(this).attr('data-detail_row_id');
            let actual_qty = $(this).attr('data-actual_qty');
            let enter_qty = $(this).val();
            let reel_weight_arr = [];
            let reel_weight_id = [];
            let current_index = $(this).attr('data-current_index');
            if(unit_type=="REEL"){
                $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        reel_weight_arr.push($(this).val());
                    }
                });
                $(".reel_weight_id_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        reel_weight_id.push($(this).val());
                    }
                });
            }else if(unit_type=="KG"){
                $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        reel_weight_arr.push($(this).val());
                    }
                });
                $(".reel_weight_id_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        reel_weight_id.push($(this).val());
                    }
                });
                    
            }
            
            sale_enter_data.push({'detail_row_id':detail_row_id,'enter_qty':enter_qty,'unit_type':unit_type,'reel_weight_arr':reel_weight_arr,"reel_weight_id":reel_weight_id,"index":item_index+"_1"});
            
            //Pending Order
            if(unit_type=="REEL"){
                let detail_row = 0;
                $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        detail_row++;
                    }
                });
                if(actual_qty>detail_row){
                    pending_order_status = 1;
                }
            }else{
                let order_weight_detail_kg_sum = 0;
                $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        order_weight_detail_kg_sum = parseFloat(order_weight_detail_kg_sum) + parseFloat($(this).val());
                    }
                });
                if(actual_qty>order_weight_detail_kg_sum){
                    pending_order_status = 1;
                }
            }
        });
       
        if(item_arr.length==0){
            alert("Please enter at least one item quantity.");
            return;
        }
        if(bill_to_id=="" || shipp_to_id=="" || sale_order_id==""){
            alert("Something went wrong. Please try again later.");
            return;
        }
        
        let sale_order_url = "";
        if(sale_id!=""){
            sale_order_url = "{{url('edit-sale')}}/"+sale_id+"?bill_to_id="+bill_to_id+"&shipp_to_id="+shipp_to_id+"&sale_order_id="+sale_order_id+"&freight="+freight+"&item_arr="+JSON.stringify(item_arr)+"&sale_enter_data="+JSON.stringify(sale_enter_data)+"&vehicle_info="+vehicle_info+"&vehicle_info_type="+vehicle_info_type+
            "&to_pay_freight="+$("#to_pay_freight").val()+"&to_pay_other_charges="+$("#to_pay_other_charges").val()+"&vehicle_freight="+$("#vehicle_freight").val()+"&transporter_freight="+$("#transporter_freight").val()+"&transporter_other_charges="+$("#transporter_other_charges").val()+"&vehicle_transporter="+$("#vehicle_info option:selected").text();
        }else{
            // sale_order_url = "{{url('sale/create')}}?bill_to_id="+bill_to_id+"&shipp_to_id="+shipp_to_id+"&sale_order_id="+sale_order_id+"&freight="+freight+"&item_arr="+JSON.stringify(item_arr)+"&sale_enter_data="+JSON.stringify(sale_enter_data)+"&vehicle_info="+vehicle_info+"&vehicle_info_type="+vehicle_info_type+"&to_pay_freight="+$("#to_pay_freight").val()+"&to_pay_other_charges="+$("#to_pay_other_charges").val()+"&vehicle_freight="+$("#vehicle_freight").val()+"&transporter_freight="+$("#transporter_freight").val()+"&transporter_other_charges="+$("#transporter_other_charges").val();
            sale_order_url = "{{url('order-preview')}}?bill_to_id="+bill_to_id+"&shipp_to_id="+shipp_to_id+"&sale_order_id="+sale_order_id+"&freight="+freight+"&item_arr="+JSON.stringify(item_arr)+"&sale_enter_data="+JSON.stringify(sale_enter_data)+"&vehicle_info="+vehicle_info+"&vehicle_info_type="+vehicle_info_type+"&to_pay_freight="+$("#to_pay_freight").val()+"&to_pay_other_charges="+$("#to_pay_other_charges").val()+"&vehicle_freight="+$("#vehicle_freight").val()+"&transporter_freight="+$("#transporter_freight").val()+"&transporter_other_charges="+$("#transporter_other_charges").val()+"&vehicle_transporter="+$("#vehicle_info option:selected").text();
        }
        if(pending_order_status==1){
            $("#sale_order_url").val(sale_order_url);
            $("#pendingOrderModal").modal('toggle');
            return;
        }
        window.location = sale_order_url+"&new_order=0";
    });
// MAIN "Next" button → ONLY open Review Sale Order Modal
    $(document).on('click', '.start_order', function() {
        let html_content = "";let net_amount = 0;
        $(".item").each(function(){
            let item_index = $(this).attr('data-item_index');
            let item_name = $(this).attr('data-item_name');
            html_content+="<tr><td style='background-color: #f2f2f2; font-weight: bold; vertical-align: top;'>"+item_name+"</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
            let weight = "";let reel_count = 0;let weight_total = 0;
            $(".gsm_" + item_index).each(function() {
                let gsm_index = $(this).attr('data-index');
                let gsm = $(this).val();
                //
                $(".size_value_" + item_index + "_" + gsm_index).each(function() {
                    let size = $(this).val();
                    let detail_index = $(this).attr('data-detail_index');
                    let quantity = $(this).closest('tr').find('.quantity_1_1').val();
                    let order_weight_total = $("#order_quantity_total_" + item_index + "_" + gsm_index).val();
                    $(".size_weight_" + item_index + "_" + gsm_index+"_"+detail_index).each(function() {
                        if($(this).val()!=""){
                            html_content+=size+"X"+gsm+"<br>";
                            weight+=$(this).val()+"<br>";
                            reel_count ++;
                            weight_total = parseFloat(weight_total) + parseFloat($(this).val());
                        }
                    });
                });
                
            });
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
            html_content+=weight;
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold; vertical-align: bottom;'>";
            html_content+=reel_count;
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold; vertical-align: bottom;'>";
            html_content+=weight_total;
            html_content+="</td>";
            let price = $("#price_"+item_index).val();
            let amount = parseFloat(price) * parseFloat(weight_total);
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold; vertical-align: bottom;'>";
            html_content+=price;
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold; vertical-align: bottom;'>";
            html_content+=amount;
            html_content+="</td>";
            html_content+="</tr>";
            net_amount = parseFloat(net_amount) + parseFloat(amount);
        });
        html_content+="<tr class='table-success fw-bold'><td colspan='6' class='text-end'>Total</td><td>"+net_amount+"</td></tr>";
        $(".challan_table tbody").html(html_content);
        $("#nextStepModal").modal('show');
    });

    //"Next" inside Review Sale Order Modal
    

    // Inside Pending Order Modal → redirect based on radio selection
    $(document).on('click', '.confirmation_btn', function() {
        var selected = $('input[name="order_confirmation"]:checked').val();
        let sale_order_url = $("#sale_order_url").val();

        if (!selected) {
            alert("Please select a radio button option");
            return;
        }

        // Hide pending modal
        $("#pendingOrderModal").modal('hide');

        // Redirect to final sale order URL
        window.location.href = sale_order_url + "&new_order=" + selected;
    });
    calculateTotalWeight();
});
$(document).on('change', '.order_quantity', function () {
    let unit_type = $(this).data('unit_type');
    let actual_qty = $(this).data('actual_qty');
    let item_index = $(this).data('item_index');
    let gsm_index = $(this).data('gsm_index');
    let detail_index = $(this).data('detail_index');
    let detail_row_id = $(this).data('detail_row_id');

    let order_qty = parseInt($(this).val());
    let size_weights = JSON.parse($(this).attr('data-weight'));

    let selected_weight = [];
    if ($(this).attr('data-selected_weight')) {
        selected_weight = JSON.parse($(this).attr('data-selected_weight'));
    }

    // prepare options
    let weight_box_option = "";
    size_weights.forEach(w => {
        weight_box_option += "<option value='" + w.weight + "' data-id='" + w.id + "'>Reel No.-" + w.reel_no + "(" + w.weight + ")</option>";
    });
    selected_weight.forEach(w => {
        weight_box_option += "<option value='" + w.weight + "' data-id='" + w.id + "'>Reel No.-" + w.reel_no + "(" + w.weight + ")</option>";
    });

    let weight_box_html = "";
    for (let i = 1; i <= order_qty; i++) {
        weight_box_html += `
            <select style="width:200px;"
                class="form-select reel_weight select2-single reel_weight_${item_index}_${gsm_index}
                size_weight_${item_index}_${gsm_index}_${detail_index}
                reel_weight_detail_row_${detail_row_id}"
                data-item_index="${item_index}"
                data-gsm_index="${gsm_index}"
                data-detail_index="${detail_index}"
                data-curr_index="${i}"
                data-actual_qty="${actual_qty}"
                data-unit_type="REEL">
                    <option value="">Weight</option>
                    ${weight_box_option}
            </select>
            <input type='hidden'
                id='reel_weight_id_${item_index}_${gsm_index}_${detail_index}_${i}'
                class='reel_weight_id_${detail_row_id}'>
            <br>
        `;
        
    }

    // Put HTML in container
    let container = "#weight_box_" + item_index + "_" + gsm_index + "_" + detail_index;
    $(container).html(weight_box_html);
   // $(container).find('.select2-single').select2('destroy');
    initSelect2(container);

    
    selected_weight.forEach((w, index) => {
        let selects = $(container + " .size_weight_" + item_index + "_" + gsm_index + "_" + detail_index);
        if (!selects.length || index >= selects.length) return;

        let select = selects.eq(index);

        // Clear selection first
        select.prop('selectedIndex', 0);

        // Select option by data-id (not value)
        select.find('option').each(function () {
            if ($(this).data('id') == w.id) {
                $(this).prop('selected', true);
                return false; // break loop
            }
        });

        select.trigger('change');
    });


});

$(document).on('change', '.reel_weight', function () {
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    let curr_index = $(this).attr('data-curr_index');
    let weight_id = $(this).find(':selected').attr('data-id');

    // Save previous value before change (optional for undo)
    let prev_value = $(this).data('prev_value');
    $(this).data('prev_value', weight_id);

    // === Update total weight calculation ===
    let total = 0;
    $(".reel_weight_" + item_index + "_" + gsm_index).each(function () {
        let val = $(this).val();
        console.log(val);
        if (val !== "") {
            total += parseFloat(val);
        }
    });
    $("#reel_weight_id_" + item_index + "_" + gsm_index + "_" + detail_index + "_" + curr_index).val(weight_id);
    
    $("#order_quantity_total_" + item_index + "_" + gsm_index).val(total);

    // ===Manage duplicate prevention ===
    let groupSelector = ".reel_weight[data-item_index='" + item_index + "'][data-gsm_index='" + gsm_index + "'][data-detail_index='" + detail_index + "']";
    //if(sale_id==""){
        // Re-enable the previously selected value in all dropdowns
        if (prev_value) {
            $(groupSelector).not(this).find("option[data-id='" + prev_value + "']").prop("disabled", false).show();
        }
    
        // Disable the newly selected option in other dropdowns
        if (weight_id) {
            $(groupSelector).not(this).find("option[data-id='" + weight_id + "']").prop("disabled", true).hide();
        }
    //}
    calculateTotalWeight();
    
});
$(document).on('change','.order_quantity',function(){
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    let current_index = $(this).attr('data-current_index');
    let weight_id = $(this).find(':selected').attr('data-id');
    if($(this).attr('data-unit_type')=="KG"){
        let total = 0;
        // $(".order_weight_kg_"+item_index+"_"+gsm_index).each(function(){
        //     if($(this).val()!=""){
        //         total = parseFloat(total) + parseFloat($(this).val());
        //     }        
        // });
        $(".reel_weight_" + item_index + "_" + gsm_index).each(function () {
            let val = $(this).val();
            if (val !== "") {
                total += parseFloat(val);
            }
        });
        $("#order_quantity_total_"+item_index+"_"+gsm_index).val(total);
        
        $("#reel_weight_id_"+item_index+"_"+gsm_index+"_"+detail_index+"_"+current_index).val(weight_id);
    }
    calculateTotalWeight();
});
function initSelect2(context = document) {
    $(context).find('.select2-single').select2({
        width: '100%'
    });
}
$("#vehicle_info").change(function(){
    $("#to_pay_freight_div").hide();
    $("#vehicle_freight_div").hide();
    $("#transporter_freight_div").hide();
    $("#other_charges").val("");
    $("#to_pay_other_charges").val("");
    $("#transporter_other_charges").val("");
    $("#to_pay_freight").val("");
    if($(this).val()=="TO PAY"){
        $("#to_pay_freight").val("{{$saleOrder->location_price}}");
        $("#to_pay_freight_div").show();
    }else if($(this).find(':selected').data('type')=="vehicle"){
        $("#vehicle_freight").val("{{$saleOrder->location_price}}");
        $("#vehicle_freight_div").show();
    }else if($(this).find(':selected').data('type')=="transporter"){
        $("#transporter_freight").val("{{$saleOrder->location_price}}");
        $("#transporter_freight_div").show();
    }
});
if(sale_id!=""){
    let freight_type = "{{$saleOrder->freight_type}}";
    if(freight_type=="to_pay"){
        $("#to_pay_freight").val("{{$saleOrder->location_price}}");
        $("#to_pay_freight_div").show();
    }else if(freight_type=="vehicle"){
        $("#vehicle_freight").val("{{$saleOrder->location_price}}");
        $("#vehicle_freight_div").show();
    }else if(freight_type=="transporter"){
        $("#transporter_freight").val("{{$saleOrder->location_price}}");
        $("#transporter_freight_div").show();
    }
}
</script>
@endsection