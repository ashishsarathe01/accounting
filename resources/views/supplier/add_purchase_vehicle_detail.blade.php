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
                    Add Purchase Info
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('store-purchase-info') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="date" class="form-label font-14 font-heading">Date</label>
                            <input type="date" class="form-control"  id="date" name="date" required value="{{date('Y-m-d')}}">
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="vehicle_bo" class="form-label font-14 font-heading">Vehicle No.</label>
                            <input type="text" class="form-control"  placeholder="Enter Vehicle No." id="vehicle_no" name="vehicle_no" required>
                        </div>
                        <div class="clearfix"></div>
                       <div class="mb-3 col-md-3">
    <label for="group" class="form-label font-14 font-heading">Group</label>
    <select class="form-select select2-single" name="group" id="group" required>
        <option value="">Select Group</option>
        @foreach($item_groups as $group)
            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
        @endforeach
    </select>
</div>
<div class="clearfix"></div>
<div class="mb-3 col-md-3" id="item_div">
    <label for="item" class="form-label font-14 font-heading">Item</label>
    <select class="form-select select2-single" name="item" id="item">
        <option value="">Select Item</option>
    </select>
</div>
<div class="mb-3 col-md-3 d-none" id="bill_no_div">
    <label class="form-label font-14 font-heading">Bill No</label>
    <input type="text" class="form-control" name="bill_no" id="bill_no" placeholder="Bill No.">
</div>

<div class="mb-3 col-md-3 d-none" id="amount_div">
    <label class="form-label font-14 font-heading">Bill Amount</label>
    <input type="number" step="0.01" class="form-control" name="amount" id="amount" placeholder="Bill Amount">
</div>

                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="gross_weight" class="form-label font-14 font-heading">Gross Weight</label>
                            <input type="text" class="form-control"  placeholder="Enter Gross Weight." id="gross_weight" name="gross_weight" required>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Account</label>
                            <select class="form-select select2-single" id="account" name="account" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $key => $account)
                                    <option value="{{$account->id}}">{{$account->account_name}}</option>
                                @endforeach
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

function initSelect2() {
    $(".select2-single").select2({
        width: '100%',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }

            function normalize(str) {
                return (str || '')
                    .toLowerCase()
                    .replace(/[.\s]/g, '');
            }

            var term = normalize(params.term);
            var text = normalize(data.text);

            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
}

$(document).ready(function () {
    initSelect2();
});

    $(document).ready(function(){
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
        $("#group").change(function(){
            let group_id = $(this).val();
            $.ajax({
                url : "{{url('item-by-group')}}",
                method : "POST",
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    group_id : group_id
                },
                success:function(obj){
                    if(obj!=""){
                        let res = JSON.parse(obj);
                        if(res.data.length>0){
                            let html = "<option value=''>Select Item</option>";
                            res.data.forEach(function(e){
                                html+='<option value="'+e.id+'">'+e.name+'</option>';
                            });
                            $("#item")
                .html(html)
                .trigger('change')
                .select2('destroy');

            initSelect2();
                        }
                    }
                    
                }
            });

$.ajax({
        url : "{{url('accounts-by-group')}}",
        method : "POST",
        data: {
            _token: '{{ csrf_token() }}',
            group_id : group_id
        },
        success:function(accounts){
            let html = "<option value=''>Select Account</option>";
            accounts.forEach(function(acc){
                html += '<option value="'+acc.id+'">'+acc.account_name+'</option>';
            });
            $("#account").html(html).trigger('change'); // update select2
        }
    });


        });





    });



    // Update Accounts dropdown dynamically
    

$('#group').on('change', function () {
    let groupId = $(this).val();

    if (!groupId) {
        $('#item').prop('required', true);
        return;
    }

    $.ajax({
        url: "{{ url('get-group-type') }}/" + groupId,
        type: 'GET',
        success: function (response) {

            if (response.group_type === 'SPARE PART') {
                // ❌ Item NOT required
                // Hide Item dropdown
                $('#item_div').addClass('d-none');
                $('#item').prop('required', false).val('').trigger('change');

                // Show Bill No + Amount
                $('#bill_no_div').removeClass('d-none');
                $('#amount_div').removeClass('d-none');

                $('#bill_no').prop('required', true);
                $('#amount').prop('required', true);

                // Optional: disable item dropdown
                
            } else {
                // Show Item
                $('#item_div').removeClass('d-none');
                $('#item').prop('required', true);

                // Hide Bill fields
                $('#bill_no_div').addClass('d-none');
                $('#amount_div').addClass('d-none');

                $('#bill_no').prop('required', false);
                $('#amount').prop('required', false);
               
            }
        }
    });
});
</script>
@endsection