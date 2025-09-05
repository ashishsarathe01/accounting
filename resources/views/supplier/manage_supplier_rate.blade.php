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
<style>
.blink {
  animation: blinker 1s linear infinite;
  color: red; /* optional */
  font-weight: bold;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
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
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Manage Supplier Rate</h5>
                    <button class="btn btn-primary btn-sm d-flex align-items-center supplier_bonus" >Supplier Bonus</button>
                    <button class="btn btn-primary btn-sm d-flex align-items-center manage_rate_difference" >Manage Rate Difference</button>
                    <button class="btn btn-primary btn-sm d-flex align-items-center manage_location">Manage Location</button>
                </div>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('store-supplier-rate') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{$rate_date}}" required>
                        </div>
                        <div class="mb-9 col-md-9">
                            @if(count($advance_rate)>0)
                                <p class="blink">Already Set Rate</p>
                                @foreach($advance_rate as $key => $value)
                                    <a href="{{route('manage-supplier-rate')}}/{{$value}}"><button type="button" class="btn btn-info">{{date('d-m-Y',strtotime($value))}}</button></a>
                                @endforeach
                            @endif
                        </div>
                        <div class="clearfix"></div>
                        @foreach($locations as $key => $location)
                            <div class="mb-4 col-md-4">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">Location</label>@endif
                                <input type="text" class="form-control" name="location[]" value="{{$location->name}}" readonly>
                                <input type="hidden" class="form-control" name="location_id[]" value="{{$location->id}}">
                            </div>
                            @foreach($heads as $k => $value)
                                @php 
                                $array_id = $location->id."_".$value->id;
                                $length = strlen($value->name); 
                                $col = 1;
                                if($length>8){
                                    $col = 2;
                                }
                                @endphp
                                <div  class="mb-{{$col}} col-md-{{$col}}">
                                    @if($key==0)<label for="name" class="form-label font-14 font-heading ">{{$value->name}}</label>@endif
                                    <input type="hidden" name="head_id_{{$location->id}}[]" value="{{$value->id}}">
                                    <input type="number" step="any" class="form-control @if($k==0)first_rate @else other_rate_{{$location->id}} @endif" name="head_value_{{$location->id}}[]" data-location_id="{{$location->id}}" data-head_id="{{$value->id}}" placeholder="Enter {{$value->name}} RATE" required value="<?php if(isset($supplier_rates) &&  isset($supplier_rates[$array_id]) && count($supplier_rates)>0){ echo $supplier_rates[$array_id]; }?>">
                                </div>
                            @endforeach
                            <div class="clearfix"></div>
                        @endforeach
                        
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
                        </button>
                    </div>
                </form>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    View Supplier Rate
                </h5>
                <div class="bg-white table-view shadow-sm">
                <table class="table table-striped table m-0 shadow-sm">
                    <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                            <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                            <th class="w-min-120 border-none bg-light-pink text-body ">Rates</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($all_rate as $key => $value)
                            @php 
                                $ressult = [];
                                foreach ($value as $row) {
                                    $ressult[$row['name']][] = $row;
                                }
                                // echo "<pre>";
                                //     print_r($ressult);
                                //     echo "</pre>";
                            @endphp
                            <tr>
                                <td>{{date('d-m-Y',strtotime($key))}}</td>
                                <td>
                                    <table class="table table-borderd">
                                        <thead>
                                            <tr>
                                                <th>Location</th>
                                                @foreach($ressult as $k => $v)                                                   
                                                    @foreach($v as $k1 => $v1)
                                                        <th>{{$v1['head_name']}}</th>
                                                    @endforeach
                                                    @break
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ressult as $k => $v)
                                                <tr>
                                                    <td>{{$k}}</td>
                                                    @foreach($v as $k1 => $v1)
                                                        {{-- <td>{{$v1['head_name']}}</td> --}}
                                                        <td>{{$v1['head_rate']}}</td>
                                                    @endforeach                                                    
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </section>
</div>
@php  
                           
    $grouped = [];$grouped_action = [];
    foreach ($difference_rate->toArray() as $row) {
        $grouped[$row['head_id']] = $row['head_rate'];
        $grouped_action[$row['head_id']] = $row['head_action'];
    }
   
@endphp
<div class="modal fade" id="rate_difference_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Rate Difference</h5>
         <br><br>
        <div class="row">
            <div class="mb-2 col-md-2">
                <label for="name" class="form-label font-14 font-heading"><strong>Head Name</strong></label>
            </div>
            <div class="mb-2 col-md-2">
                <label for="name" class="form-label font-14 font-heading"><strong>Difference Rate</strong></label>
            </div>
            <div class="mb-2 col-md-2">
                <label for="name" class="form-label font-14 font-heading"><strong>Action</strong></label>
            </div>
            <div class="clearfix"></div>
            @foreach($heads as $k => $value)
                <div class="mb-4 col-md-4">
                    <input type="text" class="form-control" value="{{$value->name}}" readonly>
                    <input type="hidden"  value="{{$value->id}}" class="head_id">
                </div>
                <div class="mb-2 col-md-2">
                    <input type="text"  class="form-control head_rate_{{$value->id}}" placeholder="Enter Price" value="@if(isset($grouped[$value->id])) {{$grouped[$value->id]}} @endif">
                </div>
                <div class="mb-2 col-md-2">
                    <input type="radio"  class="head_action_{{$value->id}}" name="head_action_{{$value->id}}" value="1" @if(isset($grouped_action[$value->id]) && $grouped_action[$value->id]==1) checked @endif> <strong style="font-size: 20px;">+</strong><br>
                    <input type="radio"  class="head_action_{{$value->id}}" name="head_action_{{$value->id}}" value="0" @if(isset($grouped_action[$value->id]) && $grouped_action[$value->id]==0) checked @endif> <strong style="font-size: 20px;">-</strong>
                </div>
                <div class="clearfix"></div>
            @endforeach
        </div><br>
        <div class="text-start">
            <button type="button" class="btn  btn-xs-primary save_difference">
                SAVE
            </button>
        </div>
    </div>
   </div>
</div>
<div class="modal fade" id="location_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Location</h5>
         <br>
        <div class="row">
            <div class="mb-4 col-md-4">
                <label for="name" class="form-label font-14 font-heading"><strong>Location Name</strong></label>
                <input type="text" class="form-control" placeholder="Enter Location" id="location_name">
                <input type="hidden" class="form-control" placeholder="Enter Location" id="location_edit_id">
            </div>
            <div class="mb-4 col-md-4">
                <label for="name" class="form-label font-14 font-heading"><strong>Status</strong></label>
                <select class="form-select" id="location_status">
                    <option value="1">Enable</option>
                    <option value="0">Disable</option>
                </select>
            </div>
        </div><br>
        <div class="text-start">
            <button type="button" class="btn  btn-xs-primary save_location">
                SAVE
            </button>
        </div>
        <br>
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">View Location</h5>
         <br>
        <div class="row">
            <table class="table table-bordered location_tbl">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
   </div>
</div>
<div class="modal fade" id="bonus_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>  
               
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Supplier Bonus</h5>
        <div class="modal-body">
        <div class="row">
            <table class="table table-bordered bonus_tbl">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="all_check" value="1"> All</th>
                        <th>Supplier Name</th>
                        <th>Bonus</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-primary reset_bonus">Reset Bonus</button>
      </div>
    </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
$(document).ready(function(){
    var grouped = @json($grouped);
    var grouped_action = @json($grouped_action);
    $(".manage_rate_difference").click(function(){
        $("#rate_difference_modal").modal('toggle');
    });
    $(".save_difference").click(function(){
        let arr = [];
        $(".head_id").each(function(){
            let head_id = $(this).val();
            let head_rate = $(".head_rate_"+head_id).val();
            let head_action = $(".head_action_"+head_id+":checked").val();
            arr.push({'head_id':head_id,'head_rate':head_rate,'head_action':head_action});
        });
        $.ajax({
            url:"{{url('store-rate-difference')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}','data':JSON.stringify(arr)},
            success:function(res){
                if(res!=""){
                    if(res.status==1){
                        alert("Rate Save Successfully.");
                        $("#rate_difference_modal").modal('toggle');
                        location.reload();
                    }else{
                        alert("Something Went Wrong.")
                    }
                }
            }
        });
    });
    $(".first_rate").keyup(function(){
        let location_id = $(this).attr('data-location_id');
        let head_id = $(this).attr('data-head_id');
        let rate = $(this).val();
        $(".other_rate_"+location_id).each(function(){
            if(grouped[$(this).attr('data-head_id')] && grouped[$(this).attr('data-head_id')]!=""){
                let diff = rate;
                if(grouped_action[$(this).attr('data-head_id')]==1){
                    diff = parseFloat(rate) + parseFloat(grouped[$(this).attr('data-head_id')]);
                }else{
                    diff = parseFloat(rate) - parseFloat(grouped[$(this).attr('data-head_id')]);
                }
                $(this).val(diff);
            }
        });
    });
    $("#date").change(function(){
        window.location = "{{ url('manage-supplier-rate') }}/" + $(this).val();
    });
    $(".manage_location").click(function(){
        getLocation();
        $("#location_modal").modal('toggle');
    });
    $(".save_location").click(function(){
        let location_name = $("#location_name").val();
        let location_edit_id = $("#location_edit_id").val();
        let location_status = $("#location_status").val();
        if(location_name==""){
            alert("Please Enter Name");
            return;
        }
        $.ajax({
            url:"{{url('store-supplier-location')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}','location_name':location_name,'location_edit_id':location_edit_id,'location_status':location_status},
            success:function(res){
                if(res!=""){
                    if(res.status==1){
                        if(location_edit_id!=""){
                            alert("Location Updated Successfully")
                        }else{
                            alert("Location Add Successfully")
                        }
                        $("#location_name").val('');
                        $("#location_edit_id").val('');
                        getLocation();
                    }
                }
            }
        });
    })
    
    function getLocation(){
        $.ajax({
            url:"{{url('get-supplier-location')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}'},
            success:function(res){
                if(res!=""){
                    let html = "";
                    if(res.location.length>0){
                        res.location.forEach(function(e){
                            html+='<tr><td>'+e.name+'</td><td><button class="btn btn-info set_edit" data-id="'+e.id+'" data-name="'+e.name+'">Edit</button></td></tr>';
                        });
                        $(".location_tbl tbody").html(html);
                    }
                }
            }
        });
    }
    $(".supplier_bonus").click(function(){
        $.ajax({
            url:"{{url('get-supplier-bonus')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}'},
            success:function(res){
                if(res!=""){
                    let arr = [];let html = "";
                    if(res.bonus.length>0){
                        const groupedByCategory = Object.groupBy(res.bonus, product => product.account_name);
                        for (let key in groupedByCategory) {
                            let bonus = "<table class='table table-bordered'>";
                            let account_id = "";
                            groupedByCategory[key].forEach(function(e){
                                bonus+="<tr><td>"+e.name+"</td><td>"+e.bonus+"</td></tr>";
                                account_id = e.account_id;
                            });
                            bonus+="</table>";
                            html+="<tr><td><input type='checkbox' value='"+account_id+"' class='bonus_reset_supplier'></td><td>"+key+"</td><td>"+bonus+"</td></tr>";
                                console.log()
                        }
                    }
                    $(".bonus_tbl tbody").html(html);
                    $("#bonus_modal").modal('toggle');
                }
            }
        });
    });
    $(".all_check").click(function(){
        if($(this).prop('checked')==true){
            $(".bonus_reset_supplier").prop('checked',true);
        }else{
            $(".bonus_reset_supplier").prop('checked',false);
        }
    });
    $(".reset_bonus").click(function(){
        let check_supplier = [];
        $(".bonus_reset_supplier").each(function(){
            if($(this).prop('checked')==true){
                check_supplier.push($(this).val());
            }
        })
        if(check_supplier.length==0){
           alert("Please Select Supplier");
           return;
        }
        $.ajax({
            url:"{{url('reset-supplier-bonus')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}','supplier':JSON.stringify(check_supplier) },
            
            success:function(res){
                if(res!=""){
                    if(res.status==1){
                        alert("Reset Successfully");
                        $("#bonus_modal").modal('toggle');
                    }
                }else{
                    alert("Something went wrong");
                }
            }
        });
    });
    
});
$(document).on('click','.set_edit',function(){
    let id = $(this).attr('data-id');
    $("#location_name").val($(this).attr('data-name'));
    $("#location_edit_id").val(id);
});
</script>
@endsection