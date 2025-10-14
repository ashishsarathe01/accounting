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
                
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('supplier.update',$supplier->id) }}">
                    
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="account" class="form-label font-14 font-heading">Account</label>
                            <select class="form-select select2-single" id="account" name="account" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $key => $account)
                                    <option value="{{$account->id}}" @if($supplier->account_id==$account->id) selected @endif>{{$account->account_name}}</option>
                                @endforeach                                
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        
                        @php  
                            $location_index = 0;$r_date = "";
                            $grouped = [];
                            foreach ($supplier->latestLocationRate->toArray() as $row) {
                                $grouped[$row['location']][] = $row;
                                $r_date = $row['r_date'];
                            }
                        @endphp
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{$r_date}}" required>
                        </div>
                        <input type="hidden" id="rate_date" name="rate_date" value="{{$r_date}}">
                        @foreach($grouped as $key => $value)
                            @php $bonus = ""; @endphp
                            <div class="clearfix"></div>
                            {{-- <div class="row mt-3 new-row"> --}}
                                <div class="mb-3 col-md-3 new-row_{{$location_index}}">
                                    <label for="location_{{$location_index}}" class="form-label font-14 font-heading">Location</label>
                                    <select class="form-select location" name="location[]" id="location_{{$location_index}}" data-id="{{$location_index}}" data-selected="{{$key}}" required>
                                        <option value="">Select Location</option>
                                        <option value="add_new">Add New</option>
                                    </select>
                                </div>
                                @foreach($value as $k => $v)
                                    @php
                                        $length = strlen($v['name']); 
                                        $col = 1;
                                        if($length>8){
                                            $col = 2;
                                        }
                                        if(!empty($v['bonus'])){
                                            $bonus = $v['bonus'];
                                        }
                                    @endphp
                                    <div class="mb-{{$col}} col-md-{{$col}} new-row_{{$location_index}}">
                                        <label for="head_id_{{$location_index}}" class="form-label font-14 font-heading">{{$v['name']}}</label>
                                        <input type="hidden"  name="head_id_{{$key}}[]" value="{{$v['head_id']}}" class="head_id_{{$location_index}}" required>
                                        <input type="number" step="any" class="form-control head_rate_{{$location_index}}" name="head_rate_{{$key}}[]"  value="{{$v['head_rate']}}" data-rate="{{$v['head_rate']}}" required data-head_id="{{$v['head_id']}}">
                                    </div>
                                @endforeach
                                <div class="mb-1 col-md-1 new-row_{{$location_index}}">
                                    <label for="bonus" class="form-label font-14 font-heading">Bonus</label>
                                    <input type="number" step="any" class="form-control bonus head_bonus_{{$location_index}}"  placeholder="Bonus"  data-index="{{$location_index}}" value="{{$bonus}}" name="bonus_{{$key}}[]">
                                </div>
                                <div class="mb-1 col-md-1 d-flex align-items-end new-row_{{$location_index}}">
                                    @if($location_index==0)
                                        <button type="button" class="btn btn-success add_more">+</button>
                                    @else
                                        <button type="button" class="btn btn-danger remove_row1" data-id="{{$location_index}}">X</button>
                                    @endif
                                </div>
                            {{-- </div> --}}
                            @php $location_index++; @endphp
                        @endforeach
                        <span class="add_div"></span>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option value="1" @if($supplier->status==1) selected @endif>Enable</option>
                                <option value="0" @if($supplier->status==0) selected @endif>Disable</option>
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
$(document).ready(function(){
    var heads = @json($heads);
    $( ".select2-single" ).select2();
    let location_index = {{$location_index}};
    var selected_location_arr = [];
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
                    <input type="hidden" name="head_id_`+location_index+`[]" value="`+e.id+`" required class="head_id_`+location_index+`">
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
    $(document).on("click", ".remove_row1", function(){
        
        $(".new-row_"+$(this).attr('data-id')).remove();
    });
    
    $(document).on("change", ".location", function(){
        var id = $(this).attr("data-id");
        $("#row_id").val(id);
        var value = $(this).val();
        
        if(value!=""){
            let index = $(this).val();
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
            if (selected_location_arr.includes($(this).val())) {
                // alert("Already Selected");
                // $(this).val('')
                // return;
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
                                let head_bonus = $(".head_bonus_"+id).val();
                                let hrate = grouped[$(this).attr('data-head_id')];
                                if(head_bonus!=""){
                                    hrate = parseFloat(hrate) + parseFloat(head_bonus);
                                }
                                $(this).val(hrate);
                                $(this).attr('data-rate',grouped[$(this).attr('data-head_id')]);
                            }
                        });
                        $("#rate_date").val(res.latestDate);
                    }
                    // $(".location").html(location_list);
                }
            });
        }
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
            $(".head_id_"+row_id).attr('name','head_id_'+location_name+'[]');
            $(".head_rate_"+row_id).attr('name','head_rate_'+location_name+'[]');
            $(".head_bonus_"+row_id).attr('name','bonus_'+location_name+'[]');
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
                // set options for each select
                $(".location").each(function(){
                    let selectedVal = $(this).attr("data-selected"); // get saved location_id
                    $(this).html(location_list); // append all options
                    if(selectedVal){
                        $(this).val(selectedVal); // mark saved option as selected
                    }
                });
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
    })
});
$("#date").change(function(){
    selected_location_arr = [];
    $(".location").each(function(){
        $(this).change();
    });
});

</script>
@endsection