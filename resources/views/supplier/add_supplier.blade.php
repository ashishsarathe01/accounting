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
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('supplier.store') }}">
                    @csrf
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
                            <label for="name" class="form-label font-14 font-heading">Location</label>
                            <select class="form-select location" name="location[]" id="location_1" data-id="1" required>
                                <option value="">Select Location</option>
                                <option value="add_new">Add New</option>
                            </select>
                        </div>
                        @foreach($heads as $key => $value)
                            <div class="mb-2 col-md-2">
                                <label for="name" class="form-label font-14 font-heading">{{$value->name}} RATE</label>
                                <input type="hidden"  name="" value="{{$value->id}}" class="head_id_1" required>
                                <input type="number" step="any" class="form-control head_rate_1" name="" placeholder="Enter {{$value->name}} RATE" required>
                            </div>
                        @endforeach
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
    let selected_location_arr = [];
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
                html+=`<div class="mb-2 col-md-2">
                    <label class="form-label font-14 font-heading">`+e.name+` RATE</label>
                    <input type="hidden" name="" value="`+e.id+`" required class="head_id_`+location_index+`">
                    <input type="number" step="any" class="form-control head_rate_`+location_index+`" name="" placeholder="Enter`+e.name+` RATE" required>
                </div>`;
            });
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
        if(value!=""){
            let index = parseInt(id)-1;
            $(".head_id_"+id).attr('name','head_id_'+index+'[]');
            $(".head_rate_"+id).attr('name','head_rate_'+index+'[]');
        }else{
            $(".head_id_"+id).attr('name','');
            $(".head_rate_"+id).attr('name','');
        }        
        if(value == 'add_new'){
            $("#location_modal").modal('show');
        }else{
            if (selected_location_arr.includes($(this).val())) {
                alert($(this).val()+" Already Selected");
                $(this).val('')
                return;
            }
            $(".location").each(function(){
                selected_location_arr.push($(this).val())
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
});

</script>
@endsection