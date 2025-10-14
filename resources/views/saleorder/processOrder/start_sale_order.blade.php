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
                            <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-14 font-heading">Item</label>
                                        <input type="text" class="form-control" value="{{$value->item->name}}" readonly id="item_id_{{$item_index}}">
                                        <input type="hidden" class="item" value="{{$value->item->id}}" data-item_index="{{$item_index}}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="price_1" class="form-label font-14 font-heading">Price</label>
                                        <input type="text" class="form-control" value="{{$value->price}}" readonly>
                                        <input type="hidden" class="price" id="price_{{$value->item->id}}" value="@if($value->bill_price){{$value->bill_price}}@else{{$value->price}}@endif">
                                    </div>
                                    {{-- <div class="clearfix"></div> --}}
                                    <div class="col-md-3 mb-3">
                                        <label for="unit_1" class="form-label font-14 font-heading">Unit</label>
                                        <input type="text" class="form-control" value="{{$value->unitMaster->s_name}}" readonly>
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
                                                    <td style="text-align: center">{{$gsm->gsm}}</td>
                                                </tr>
                                            </table>
                                            <table class="table table-bordered" id="table_1_1">
                                                <tr>
                                                    <td>SIZES</td>
                                                    <td class="qty_title_1">{{$value->unitMaster->s_name}}</td>
                                                    <td></td>
                                                </tr>
                                                @php $qty_total = 0; $detail_index = 1; 
                                                
                                                @endphp
                                                
                                                
                                                @foreach($gsm->details as $k2 => $detail)
                                                     <tr>
                                                        <td>
                                                            <input type="text" name="items[1][gsms][1][details][{{ $k2 }}][size]" class="form-control size size_1_1" value="{{$detail->size}}" onkeyup="approxCalculation(1,1)" readonly>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[1][gsms][1][details][{{ $k2 }}][reel]" class="form-control quantity quantity_1_1" value="{{$detail->quantity}}" data-item_id="1" data-gsm_id="1" data-quantity_id="{{ $k2 }}" onkeyup="approxCalculation(1,1)" readonly>
                                                        </td>
                                                        <td @if($value->SaleOrderSettingUnitMaster->unit_type=="REEL") style="display: inline-flex" @endif>
                                                            <input type="text" class="form-control order_quantity @if($value->SaleOrderSettingUnitMaster->unit_type=="KG") order_weight_kg_{{$item_index}}_{{$gsm_index}} order_weight_kg_{{$item_index}}_{{$gsm_index}}_{{$detail_index}} order_weight_detail_kg_{{$detail->id}}@endif" data-actual_qty="{{$detail->quantity}}" data-unit_type="{{$value->SaleOrderSettingUnitMaster->unit_type}}" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" placeholder="{{$value->unitMaster->s_name}}" data-detail_row_id="{{$detail->id}}">

                                                            @if($value->SaleOrderSettingUnitMaster->unit_type=="REEL")
                                                                <span id="weight_box_{{$item_index}}_{{$gsm_index}}_{{$detail_index}}"></span>
                                                            @elseif($value->SaleOrderSettingUnitMaster->unit_type=="KG")
                                                                <svg style="color: green;cursor:pointer;margin-left:5px;float:right" class="bg-primary rounded-circle add_weight" data-item_index="{{$item_index}}" data-gsm_index="{{$gsm_index}}" data-detail_index="{{$detail_index}}" data-actual_qty="{{$detail->quantity}}" data-detail_row_id="{{$detail->id}}" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/></svg>
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
                            <input type="button" value="SAVE" class="btn btn-primary start_order">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="pendingOrderModal" tabindex="-1" aria-labelledby="imageUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-3 border-radius-8">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="imageUploadLabel">Pending Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="image-inputs">
                    <div class="mb-3 align-items-center">
                        <strong>Are you want to create order for pending items?</strong>  <br><input type="radio" name="order_confirmation" value="1"> Yes <input type="radio" name="order_confirmation" value="0"> No
                        <input type="hidden" id="sale_order_url">
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="buttom" class="btn btn-success confirmation_btn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.footer')

<script>
$(document).ready(function(){
    $(".add_weight").click(function(){
        let item_index = $(this).attr('data-item_index');
        let gsm_index = $(this).attr('data-gsm_index');
        let detail_index = $(this).attr('data-detail_index');
        let actual_qty = $(this).attr('data-actual_qty');
        let detail_row_id = $(this).attr('data-detail_row_id');
        weight_box_html = "";
        weight_box_html+="<input type='text' class='form-control order_quantity order_weight_kg_"+item_index+"_"+gsm_index+" order_weight_kg_"+item_index+"_"+gsm_index+"_"+detail_index+" order_weight_detail_kg_"+detail_row_id+"' style='margin-left: 6px;' placeholder='KG' data-item_index='"+item_index+"' data-gsm_index='"+gsm_index+"' data-detail_index='"+detail_index+"' data-actual_qty='"+actual_qty+"' data-detail_row_id='"+detail_row_id+"' data-unit_type='KG'>";
        $("#weight_box_"+item_index+"_"+gsm_index+"_"+detail_index).append(weight_box_html);
    });
    $(".start_order").click(function(){
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
            //if(enter_qty!=""){
                if(unit_type=="REEL"){
                    $(".reel_weight_detail_row_"+detail_row_id).each(function(){
                        if($(this).val()!=""){
                            reel_weight_arr.push($(this).val());
                        }
                    });
                }
                sale_enter_data.push({'detail_row_id':detail_row_id,'enter_qty':enter_qty,'reel_weight_arr':reel_weight_arr});
            //}
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
                $(".order_weight_detail_kg_"+detail_row_id).each(function(){
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
    $(".confirmation_btn").click(function(){
        var selected = $('input[name="order_confirmation"]:checked').val();
        let sale_order_url = $("#sale_order_url").val();
        if(selected=="" || selected==undefined){
            alert("Please select radio button option")
            return;
        }
        window.location = sale_order_url+"&new_order="+selected;
    });
});
$(document).on('keyup','.order_quantity',function(){
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
    if(unit_type=="REEL"){ 
        let weight_box_html = "";
        while(order_qty>0){
            weight_box_html+="<input type='text' class='form-control reel_weight reel_weight_"+item_index+"_"+gsm_index+" reel_weight_detail_row_"+detail_row_id+"' style='margin-left: 6px;' placeholder='Weight' data-item_index='"+item_index+"' data-gsm_index='"+gsm_index+"' data-detail_index='"+detail_index+"' data-actual_qty='"+actual_qty+"' data-unit_type='REEL'><br>";
            order_qty--
        }
        $("#weight_box_"+item_index+"_"+gsm_index+"_"+detail_index).html(weight_box_html);
    }
});
$(document).on('keyup','.reel_weight',function(){
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    let total = 0;
    $(".reel_weight_"+item_index+"_"+gsm_index).each(function(){
        if($(this).val()!=""){
            total = parseFloat(total) + parseFloat($(this).val());
        }        
    });
    $("#order_quantity_total_"+item_index+"_"+gsm_index).val(total)
});
$(document).on('keyup','.order_quantity',function(){
    let item_index = $(this).attr('data-item_index');
    let gsm_index = $(this).attr('data-gsm_index');
    let detail_index = $(this).attr('data-detail_index');
    if($(this).attr('data-unit_type')=="KG"){
        let total = 0;
        $(".order_weight_kg_"+item_index+"_"+gsm_index).each(function(){
            if($(this).val()!=""){
                total = parseFloat(total) + parseFloat($(this).val());
            }        
        });
        $("#order_quantity_total_"+item_index+"_"+gsm_index).val(total);
    }
    
});
</script>
@endsection