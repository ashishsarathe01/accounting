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
                    Add Supplier
                </h5>
                <ul class="nav nav-fill nav-tabs" role="tablist">
                  @foreach($group_list as $key => $value)
                     <li class="nav-item" role="presentation">
                        <a class="nav-link @if($key==0) active @endif" id="fill-tab-{{$value->item_id}}" data-bs-toggle="tab" href="#fill-tabpanel-{{$value->item_id}}" role="tab" aria-controls="fill-tabpanel-{{$value->item_id}}" aria-selected="true"><h4>{{$value->group_type}}</h4></a>
                     </li>
                  @endforeach
                </ul>
                <div class="tab-content pt-5" id="tab-content">
                    @foreach($group_list as $key => $value)
                        <div class="tab-pane @if($key==0) active @endif" id="fill-tabpanel-{{$value->item_id}}" role="tabpanel" aria-labelledby="fill-tab-{{$value->item_id}}">
                            @if($value->group_type=="WASTE KRAFT")
                                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('supplier.store') }}">
                                    @csrf
                                    <input type="hidden" id="rate_date" name="rate_date">
                                    <div class="row">
                                        <div class="mb-4 col-md-4">
                                            <label for="name" class="form-label font-14 font-heading">Account</label>
                                            <select class="form-select select2-single" id="account" name="account" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $key => $account)
                                                    <option value="{{$account->id}}">{{$account->account_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label for="name" class="form-label font-14 font-heading">Date</label>
                                            <input type="date" class="form-control" id="date" name="date" value="" required>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label for="name" class="form-label font-14 font-heading">Location</label>
                                            <select class="form-select location" name="location[]" id="location_1" data-id="1" required>
                                                <option value="">Select Location</option>
                                                <option value="add_new">Add New</option>
                                            </select>
                                        </div>
                                        @foreach($heads as $key => $value)
                                            @php                                 
                                                $length = strlen($value->name); 
                                                $col = 1;
                                                if($length>8){
                                                    $col = 2;
                                                }
                                            @endphp
                                            <div class="mb-{{$col}} col-md-{{$col}}">
                                                <label for="name" class="form-label font-14 font-heading">{{$value->name}}</label>
                                                <input type="hidden"  name="" value="{{$value->id}}" class="head_id_1" required>
                                                <input type="number" step="any" class="form-control head_rate_1" name="" placeholder="RATE" required data-head_id="{{$value->id}}">
                                            </div>
                                        @endforeach
                                        <div class="mb-1 col-md-1">
                                            <label for="bonus" class="form-label font-14 font-heading">Bonus</label>
                                            <input type="number" step="any" class="form-control bonus head_bonus_1"  placeholder="Bonus"  data-index="1">
                                        </div>
                                        <div class="mb-1 col-md-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" data-id="1" class="bg-primary rounded-circle add_more" width="30" height="30" viewBox="0 0 24 24" fill="none" style="cursor: pointer;margin-top:35px" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                        </div>
                                        <span class="add_div"></span>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label class="form-label font-14 font-heading">Status</label>
                                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                                <option value="">Select </option>
                                                <option value="1">Enable</option>
                                                <option value="0">Disable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-start">
                                        <button type="submit" class="btn  btn-xs-primary ">
                                            SUBMIT
                                        </button>
                                    </div>
                                </form>
                            @elseif($value->group_type=="BOILER FUEL")
                                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('fuel-supplier.store') }}">
                                    @csrf
                                    <input type="hidden" id="fule_rate_date" name="fule_rate_date">
                                    <div class="row">
                                        <div class="mb-4 col-md-4">
                                            <label for="name" class="form-label font-14 font-heading">Account</label>
                                            <select class="form-select select2-single" id="account" name="account" required>
                                                <option value="">Select Account</option>
                                                @foreach($accounts as $key => $account)
                                                    <option value="{{$account->id}}">{{$account->account_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label for="fule_date" class="form-label font-14 font-heading">Date</label>
                                            <input type="date" class="form-control" id="fule_date" name="fule_date" value="" required>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label for="item_1" class="form-label font-14 font-heading">Item</label>
                                            <select class="form-select item" name="item[]" id="item_1" data-id="1" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $key => $item)
                                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label for="item_price_1" class="form-label font-14 font-heading">Price</label>
                                            <input type="text" class="form-control item_price" name="item_price[]" id="item_price_1" data-id="1" required placeholder="Price">
                                        </div>
                                        <div class="mb-1 col-md-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" data-id="1" class="bg-primary rounded-circle add_fuel_row" width="30" height="30" viewBox="0 0 24 24" fill="none" style="cursor: pointer;margin-top:35px" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                        </div>
                                        <span class="add_fuel_div"></span>
                                        <div class="clearfix"></div>
                                        <div class="mb-3 col-md-3">
                                            <label class="form-label font-14 font-heading">Status</label>
                                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                                <option value="">Select </option>
                                                <option value="1">Enable</option>
                                                <option value="0">Disable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-start">
                                        <button type="submit" class="btn  btn-xs-primary ">
                                            SUBMIT
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endforeach
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
        var heads = @json($heads);
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
        let location_index = 1;
        var location_list = "<option value=''>Select Location</option>";
        location_list+="<option value='add_new'>Add New</option>";
        getLocationList();
        $('.add_more').click(function(){
            location_index++;
            var html = `
            <div class="row mt-3 new-row">
                <div class="mb-3 col-md-3">
                    <label class="form-label font-14 font-heading">Location</label>
                    <select class="form-select location" name="location[]" id="location_`+location_index+`" data-id="`+location_index+`" required>
                        `+location_list+`
                    </select>
                </div>`;
                heads.forEach(function(e){
                    let length = e.name.length; 
                    let col = 1;
                    if(length>8){
                        col = 2;
                    }
                    html+=`<div class="mb-`+col+` col-md-`+col+`">
                        <label class="form-label font-14 font-heading">`+e.name+`</label>
                        <input type="hidden" name="" value="`+e.id+`" required class="head_id_`+location_index+`">
                        <input type="number" step="any" class="form-control head_rate_`+location_index+`" name="" placeholder=" RATE" required data-head_id="`+e.id+`">
                    </div>`;
                });
                html+=`<div class="mb-1 col-md-1">
                                <label for="bonus" class="form-label font-14 font-heading">Bonus</label>
                                <input type="number" step="any" class="form-control bonus head_bonus_`+location_index+`"  placeholder="Bonus"  data-index="`+location_index+`">
                            </div>`;
                html+=`<div class="mb-1 col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove_row">X</button>
                </div>
            </div>`;
            $(this).closest(".row").find(".add_div").append(html);
        });
        // Remove row
        $(document).on("click", ".remove_row", function(){
            $(this).closest(".new-row").remove();
        });
        $(document).on("change", ".location", function(){
            var id = $(this).attr("data-id");
            $("#row_id").val(id);
            var value = $(this).val();
            var date = $("#date").val();
            if(date==""){
                alert("Please Select Date");
                $(this).val('');
                return;
            }
            if(value!=""){
                let index = parseInt(id)-1;
                $(".head_id_"+id).attr('name','head_id_'+index+'[]');
                $(".head_rate_"+id).attr('name','head_rate_'+index+'[]');
                $(".head_bonus_"+id).attr('name','bonus_'+index+'[]');
            }else{
                $(".head_id_"+id).attr('name','');
                $(".head_rate_"+id).attr('name','');
                $(".head_bonus_"+id).attr('name','');
            }        
            if(value == 'add_new'){
                $("#location_modal").modal('show');
            }else{
                console.log(selected_location_arr);
                if (selected_location_arr.includes($(this).val())) {
                    alert(" Already Selected");
                    $(this).val('')
                    return;
                }
                selected_location_arr = [];
                $(".location").each(function(){
                    selected_location_arr.push($(this).val())
                });
            }
            
            //get rate
            if(value != 'add_new'){
                $(".head_rate_"+id).each(function(){
                    $(this).val('');
                });
                $.ajax({
                    url : "{{url('rate-by-location')}}",
                    method : "POST",
                    data: {
                        _token: '<?php echo csrf_token() ?>',
                        location_id : value,
                        date : $("#date").val()
                    },
                    success:function(res){
                        if(res.rate.length>0){
                            let grouped = [];
                            res.rate.forEach(function(e){
                                grouped[e.head_id] = e.head_rate
                            });
                            $(".head_rate_"+id).each(function(){
                                if(grouped[$(this).attr('data-head_id')]){
                                    $(this).val(grouped[$(this).attr('data-head_id')]);
                                    $(this).attr('data-rate',grouped[$(this).attr('data-head_id')]);
                                }
                            });
                            $("#rate_date").val(res.latestDate);
                        }
                        // $(".location").html(location_list);
                    }
                });
            }
            
            //
        });
        $(".save_location").click(function(){
            var location_name = $("#location_name").val();
            let row_id = $("#row_id").val();        
            if(location_name != ''){
                var new_option = `<option value="${location_name}">${location_name}</option>`;
                location_list+=`<option value="${location_name}">${location_name}</option>`;
                $(".location").append(new_option);
                $("#location_"+row_id).val(location_name);
                $("#location_modal").modal('hide');
                $("#location_name").val('');
                selected_location_arr.push(location_name);
            } else {
                alert("Please enter a location name.");
            }
        });
        function getLocationList(){
            $.ajax({
                url : "{{url('get-supplier-location')}}",
                method : "POST",
                data: {
                    _token: '<?php echo csrf_token() ?>'
                },
                success:function(res){            
                    if(res.location.length>0){
                        location_arr = res.location;
                        res.location.forEach(function(e){
                            location_list+="<option value="+e.id+">"+e.name+"</option>";
                        });
                    }
                    $(".location").html(location_list);
                }
            });
        }
        $(document).on('keyup','.bonus',function(){
            let index = $(this).attr('data-index');
            let bonus = $(this).val();
            if(bonus=="" || bonus==null){
                bonus = 0;
            }
            $(".head_rate_"+index).each(function(){
                if($(this).val()!=""){
                    $(this).val(parseFloat($(this).attr('data-rate')) + parseFloat(bonus));
                }
            });
        });
        //Fuel Code....
        let fuel_index = 1;
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
    $("#date").change(function(){
        
        $(".location").each(function(){
            selected_location_arr = [];
            $(this).change();
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
    document.addEventListener('DOMContentLoaded', function () {
        // Restore the last active tab from localStorage
        let activeTab = localStorage.getItem('addSupplierActiveTab');
        if (activeTab) {
            let triggerEl = document.querySelector(`[data-bs-toggle="tab"][href="${activeTab}"]`);
            if (triggerEl) {
                new bootstrap.Tab(triggerEl).show();
            }
        }

        // Save the active tab on click
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('addSupplierActiveTab', event.target.getAttribute('href'));
            });
        });
    });
</script>
@endsection