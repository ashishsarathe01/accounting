@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
    /* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}

</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                <!-- Display validation errors -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Supplier
                </h5>
                <div class="tab-content pt-5" id="tab-content">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('fuel-supplier.update',$fuel_supplier->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="fule_rate_date" name="fule_rate_date" value="{{$fuel_supplier->itemRates[0]->price_date}}">
                        <div class="row">
                            <div class="mb-4 col-md-4">
                                <label for="name" class="form-label font-14 font-heading">Account</label>
                                <select class="form-select select2-single" id="account" name="account" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $key => $account)
                                        <option value="{{$account->id}}" @if($account->id==$fuel_supplier->account_id) selected @endif>{{$account->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div class="mb-3 col-md-3">
                                <label for="fule_date" class="form-label font-14 font-heading">Date</label>
                                <input type="date" class="form-control" id="fule_date" name="fule_date" value="{{$fuel_supplier->itemRates[0]->price_date}}" required>
                            </div>
                            <div class="clearfix"></div>
                            @foreach($fuel_supplier->itemRates as $k => $item_info)
                                <div class="mb-3 col-md-3">
                                    <label for="item_{{$k}}" class="form-label font-14 font-heading">Item</label>
                                    <select class="form-select item" name="item[]" id="item_{{$k}}" data-id="{{$k}}" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $key => $item)
                                            <option value="{{$item->id}}" @if($item_info->item_id==$item->id) selected @endif>{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="item_price_{{$k}}" class="form-label font-14 font-heading">Price</label>
                                    <input type="text" class="form-control item_price" name="item_price[]" id="item_price_{{$k}}" data-id="{{$k}}" required placeholder="Price" value="{{$item_info->price}}">
                                </div>
                                @if($k==0)
                                    <div class="mb-1 col-md-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" data-id="{{$k}}" class="bg-primary rounded-circle add_fuel_row" width="30" height="30" viewBox="0 0 24 24" fill="none" style="cursor: pointer;margin-top:35px" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                    </div>
                                @else
                                    <div class="mb-1 col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove_fuel_row">X</button>
                                    </div>
                                @endif
                                <div class="clearfix"></div>
                            @endforeach
                            <span class="add_fuel_div"></span>
                            <div class="clearfix"></div>
                            <div class="mb-3 col-md-3">
                                <label class="form-label font-14 font-heading">Status</label>
                                <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                    <option value="">Select </option>
                                    <option value="1" @if(1==$fuel_supplier->status) selected @endif>Enable</option>
                                    <option value="0" @if(0==$fuel_supplier->status) selected @endif>Disable</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-start">
                            <button type="submit" class="btn  btn-xs-primary ">
                                SUBMIT
                            </button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </section>
    <div class="modal fade" id="location_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4 border-divider border-radius-8">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Loaction</h5>
                <br>
                <div class="row">                    
                    <div class="mb-12 col-md-12">
                        <label for="name" class="form-label font-14 font-heading">Location</label>
                        <input type="text" id="location_name" class="form-control" placeholder="Enter Location"/>
                        <input type="hidden" id="row_id">
                    </div>                    
                </div>
                <br>
                <div class="text-start">
                    <button type="button" class="btn  btn-xs-primary save_location">
                        SAVE
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
@include('layouts.footer')
<script>
    var selected_location_arr = [];
    $(document).ready(function(){
        var items = @json($items);
        $( ".select2-single" ).select2({
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                // Normalize: remove dots + spaces, lowercase everything
                function normalize(str) {
                    return (str || '')
                        .toLowerCase()
                        .replace(/[.\s]/g, ''); // remove '.' and spaces
                }
                var term = normalize(params.term);
                var text = normalize(data.text);
                if (text.indexOf(term) > -1) {
                    return data;
                }
                return null;
            }
        });
        
        //Fuel Code....
        let fuel_index = "{{$k}}";
        $('.add_fuel_row').click(function(){
            fuel_index++;
            var html = `
            <div class="row mt-3 new-row">
            <div class="mb-3 col-md-3">
                <label for="item_${fuel_index}" class="form-label font-14 font-heading">Item</label>
                <select class="form-select item" name="item[]" id="item_${fuel_index}" data-id="${fuel_index}" required="">
                    <option value="">Select Item</option>`;
                    items.forEach(function(e){
                        html+= `<option value="${e.id}">${e.name}</option>`;
                    });
            html+= `</select>
            </div>
            <div class="mb-3 col-md-3">
                <label for="item_price_${fuel_index}" class="form-label font-14 font-heading">Price</label>
                <input type="text" class="form-control item_price" name="item_price[]" id="item_price_${fuel_index}" data-id="${fuel_index}" required="" placeholder="Price">
            </div>
            `;
            html+=`<div class="mb-1 col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove_fuel_row">X</button>
                </div>
            </div></div>`;
            $(this).closest(".row").find(".add_fuel_div").append(html);
        });
        $(document).on("click", ".remove_fuel_row", function(){
            $(this).closest(".new-row").remove();
        });
    });
    $("#fule_date").change(function(){
        $(".item").each(function(){
            $(this).change();
        });
    })
    $(document).on("change", ".item", function(){
        let index = $(this).attr('data-id');
        let item_id = $(this).val();
        $.ajax({
            url : "{{url('fuel_price-by-item')}}",
            method : "POST",
            data: {
                _token: '<?php echo csrf_token() ?>',
                item_id : item_id,
                account_id : $("#account").val(),
                date : $("#fule_date").val()
            },
            success:function(res){
                if(res.rate){
                    $("#item_price_"+index).val(res.rate.item_price);
                    $("#fule_date").val(res.latestDate);
                }
                // $(".location").html(location_list);
            }
        });    
    });
</script>
@endsection