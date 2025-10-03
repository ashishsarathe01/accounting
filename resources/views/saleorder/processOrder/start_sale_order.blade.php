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

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-order.store')}}" id="saleOrderForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Bill To</label>
                            <input type="text" class="form-control" value="{{$saleOrder->billTo->account_name}}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Shipp To</label>
                            <input type="text" class="form-control" value="{{$saleOrder->shippTo->account_name}}" readonly>
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
                            <input type="text" class="form-control" value="@if($saleOrder->freight==1) Yes @else No @endif" readonly>
                        </div>
                    </div>
                     <div id="items_container">
                        @foreach($saleOrder->items as $key => $value)
                            <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-14 font-heading">Item</label>
                                        <input type="text" class="form-control" value="{{$value->item->name}}" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="price_1" class="form-label font-14 font-heading">Price</label>
                                        <input type="text" class="form-control" value="{{$value->price}}" readonly>
                                    </div>
                                    <div class="clearfix"></div>
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
                                    @foreach($value->gsms as $k1 => $gsm)
                                         <div class="col-md-3 gsm-block" id="gsm_block_1_1">
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
                                                </tr>
                                                @php $qty_total = 0; @endphp
                                                @foreach($gsm->details as $k2 => $detail)
                                                     <tr>
                                                        <td>
                                                            <input type="text" name="items[1][gsms][1][details][{{ $k2 }}][size]" class="form-control size size_1_1" value="{{$detail->size}}" onkeyup="approxCalculation(1,1)">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[1][gsms][1][details][{{ $k2 }}][reel]" class="form-control quantity quantity_1_1" value="{{$detail->quantity}}" data-item_id="1" data-gsm_id="1" data-quantity_id="{{ $k2 }}" onkeyup="approxCalculation(1,1)">
                                                        </td>
                                                    </tr>
                                                    @php $qty_total+= $detail->quantity; @endphp
                                                @endforeach
                                                <tr>
                                                    <th style="text-align: center">Total</th>
                                                    <td>
                                                        <input type="number" class="form-control quantity_total" id="quantity_total_1_1" value="{{$qty_total}}" readonly>
                                                    </td>
                                                </tr>
                                            </table>
                                            <span class="add_row" data-item="1" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
                                        </div>
                                    @endforeach
                                   
                                    
                                </div>
                            </div>
                        @endforeach
                        
                        
                    </div>
                    <div class="d-flex">
                        <div class="ms-auto">
                            {{-- <input type="submit" value="SAVE" class="btn btn-primary">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a> --}}
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>

</script>
@endsection