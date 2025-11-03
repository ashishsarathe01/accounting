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
                            <input type="text" class="form-control" value="{{$saleOrder->billTo->account_name}}" readonly>
                            <input type="hidden" id="bill_to_id" value="{{$saleOrder->billTo->id}}">
                        </div>
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Shipp To</label>
                            <input type="text" class="form-control" value="{{$saleOrder->shippTo->account_name}}" readonly>
                            <input type="hidden" id="shipp_to_id" value="{{$saleOrder->shippTo->id}}">
                        </div>
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Purchase Order No.</label>
                            <input type="text" class="form-control" value="{{$saleOrder->purchase_order_no}}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Purchase Order Date</label>
                            <input type="text" class="form-control" value="@empty(!$saleOrder->purchase_order_date)
                                {{date('d-m-Y',strtotime($saleOrder->purchase_order_date))}}
                            @endempty " readonly>
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
                                        <label for="price_1" class="form-label font-14 font-heading">Price</label>
                                        <input type="text" class="form-control" value="{{$value->price}}" readonly>
                                        <input type="hidden" class="price" id="price_{{$value->item->id}}" value="@if($value->bill_price){{$value->bill_price}}@else{{$value->price}}@endif">
                                    </div>
                                    {{-- <div class="clearfix"></div> --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="unit_1" class="form-label font-14 font-heading">Unit</label>
                                        <input type="text" class="form-control" value="{{$value->unitMaster->s_name}}" id="unit_{{$item_index}}" readonly>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="sub_unit_1" class="form-label font-14 font-heading">Sub Unit</label>
                                        <input type="text" class="form-control" value="{{$value->sub_unit}}" readonly>
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
                                                    <td>SIZES</td>
                                                    <td class="qty_title_1">{{$value->unitMaster->s_name}}</td>
                                                    <td style="width:55%"></td>
                                                </tr>
                                                @php $qty_total = 0; $detail_index = 1;@endphp
                                                @foreach($gsm->details as $k2 => $detail)
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
                                                                
                                                                <input type="text" class="form-control order_quantity @if($value->SaleOrderSettingUnitMaster->unit_type=="KG") order_weight_kg_{{$item_index}}_{{$gsm_index}} order_weight_kg_{{$item_index}}_{{$gsm_index}}_{{$detail_index}} order_weight_detail_kg_{{$detail->id}}@endif" data-actual_qty="{{$detail->quantity}}" data-unit_type="{{$value->SaleOrderSettingUnitMaster->unit_type}}" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" placeholder="Reel" data-detail_row_id="{{$detail->id}}" data-weight="{{json_encode($reel_weight_arr)}}">
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
                                                    <td><input type="number" class="form-control order_quantity_total_{{$item_index}}" id="order_quantity_total_{{$item_index}}_{{$gsm_index}}"  readonly></td>
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
                <h5 class="modal-title fw-semibold" id="nextStepModalLabel">Review Sale Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="fw-semibold text-primary mb-1">Party Details</h6>
                    <p class="mb-0">
                        <strong>Party Name:</strong> {{ $saleOrder->billTo->account_name ?? '-' }}<br>
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($saleOrder->created_at)->format('d M Y') ?? '-' }}
                    </p>
                </div>
                <hr>
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
                </div>
            </div>

            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                <button type="button" id="reviewNextBtn" class="btn btn-success px-4">Next</button>
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
$(document).ready(function(){
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
    });
    $("#reviewNextBtn").click(function(){
        let bill_to_id = $("#bill_to_id").val();
        let shipp_to_id = $("#shipp_to_id").val();
        let freight = $("#freight").val();
        let item_arr = [];
        let sale_order_id = "{{$saleOrder->id}}";
        
        $(".item").each(function(){
            let item_id = $(this).val();
            let item_index = $(this).attr('data-item_index');
            let price = $("#price_"+item_id).val();
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
            sale_enter_data.push({'detail_row_id':detail_row_id,'enter_qty':enter_qty,'unit_type':unit_type,'reel_weight_arr':reel_weight_arr,"reel_weight_id":reel_weight_id});
            
            //Pending Order
            if(unit_type=="REEL"){
                let detail_row = 0;
                $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                    if($(this).val()!=""){
                        detail_row++;
                    }
                });
                if(actual_qty>detail_row){
                    //pending_order_html+="<tr><td>Item</td><td>"+$("#item_id_"+item_index).val()+"</td></tr>";
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
                    //pending_order_html+="<tr><td>Item</td><td>"+$("#item_id_"+item_index).val()+"</td></tr>";
                    pending_order_status = 1;
                }
            }
        });
        
        let sale_order_url = "{{url('sale/create')}}?bill_to_id="+bill_to_id+"&shipp_to_id="+shipp_to_id+"&sale_order_id="+sale_order_id+"&freight="+freight+"&item_arr="+JSON.stringify(item_arr)+"&sale_enter_data="+JSON.stringify(sale_enter_data);
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
            html_content+="<tr><td style='background-color: #f2f2f2; font-weight: bold;'>"+item_name+"</td>";
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
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
            html_content+=reel_count;
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
            html_content+=weight_total;
            html_content+="</td>";
            let price = $("#price_"+$(this).val()).val();
            let amount = parseFloat(price) * parseFloat(weight_total);
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
            html_content+=price;
            html_content+="</td>";
            html_content+="<td style='background-color: #e6e6e6; font-weight: bold;'>";
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

});
$(document).on('change','.order_quantity',function(){
    let unit_type = $(this).attr('data-unit_type');
    let actual_qty = $(this).attr('data-actual_qty');
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    let order_qty = $(this).val();
    let detail_row_id = $(this).attr('data-detail_row_id');
    if(unit_type=="REEL" && order_qty>actual_qty){
        //alert("Invalid Quantity");
        // $(this).val('');
        // return;
    }else if(unit_type=="KG"){
        let weight_total = 0;
        $(".order_weight_kg_"+item_index+"_"+gsm_index+"_"+detail_index).each(function(){
            weight_total = parseFloat(weight_total) + parseFloat($(this).val());
        });
        if(actual_qty<weight_total){            
            //alert("Invalid Quantity");
            // $(this).val('');
            // return;
        }
    }
    if(unit_type=="REEL" || unit_type=="KG"){
        let weight_box_html = "";
        let size_weights = JSON.parse($(this).attr('data-weight'));
        let weight_box_option = "";
        size_weights.forEach(function(weight){
            //weight_box_option+="<option value='"+weight+"'>"+weight+"</option>";
            weight_box_option+="<option value='"+weight['weight']+"' data-id='"+weight['id']+"'>Reel No.-"+weight['reel_no']+"("+weight['weight']+")</option>";
        });
        let i = 1;
        while(order_qty>0){
            weight_box_html+="<select style='width: 150px;' class='form-select reel_weight reel_weight_"+item_index+"_"+gsm_index+" size_weight_"+item_index+"_"+gsm_index+"_"+detail_index+" reel_weight_detail_row_"+detail_row_id+"' data-item_index='"+item_index+"' data-gsm_index='"+gsm_index+"' data-detail_index='"+detail_index+"' data-curr_index='"+i+"' data-actual_qty='"+actual_qty+"' data-unit_type='REEL'><option value=''>Weight</option>"+weight_box_option+"</select><input type='hidden' id='reel_weight_id_"+item_index+"_"+gsm_index+"_"+detail_index+"_"+i+"' class='reel_weight_id_"+detail_row_id+"''><br>";

            // weight_box_html+="<input type='text' class='form-control reel_weight reel_weight_"+item_index+"_"+gsm_index+" reel_weight_detail_row_"+detail_row_id+"' style='margin-left: 6px;' placeholder='Weight' data-item_index='"+item_index+"' data-gsm_index='"+gsm_index+"' data-detail_index='"+detail_index+"' data-actual_qty='"+actual_qty+"' data-unit_type='REEL'><br>";
            i++;
            order_qty--
        }
        $("#weight_box_"+item_index+"_"+gsm_index+"_"+detail_index).html(weight_box_html);
    }
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
        if (val !== "") {
            total += parseFloat(val);
        }
    });
    $("#reel_weight_id_" + item_index + "_" + gsm_index + "_" + detail_index + "_" + curr_index).val(weight_id);
    $("#order_quantity_total_" + item_index + "_" + gsm_index).val(total);

    // ===Manage duplicate prevention ===
    let groupSelector = ".reel_weight[data-item_index='" + item_index + "'][data-gsm_index='" + gsm_index + "'][data-detail_index='" + detail_index + "']";

    // Re-enable the previously selected value in all dropdowns
    if (prev_value) {
        $(groupSelector).not(this).find("option[data-id='" + prev_value + "']").prop("disabled", false).show();
    }

    // Disable the newly selected option in other dropdowns
    if (weight_id) {
        $(groupSelector).not(this).find("option[data-id='" + weight_id + "']").prop("disabled", true).hide();
    }
});
$(document).on('change','.order_quantity',function(){
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    let current_index = $(this).attr('data-current_index');
    let weight_id = $(this).find(':selected').attr('data-id');
    if($(this).attr('data-unit_type')=="KG"){
        let total = 0;
        $(".order_weight_kg_"+item_index+"_"+gsm_index).each(function(){
            if($(this).val()!=""){
                total = parseFloat(total) + parseFloat($(this).val());
            }        
        });
        $("#order_quantity_total_"+item_index+"_"+gsm_index).val(total);
        $("#reel_weight_id_"+item_index+"_"+gsm_index+"_"+detail_index+"_"+current_index).val(weight_id);
    }
    
});
</script>
@endsection