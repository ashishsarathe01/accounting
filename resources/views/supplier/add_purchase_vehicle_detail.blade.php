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
                            <label for="vehicle_bo" class="form-label font-14 font-heading">Vehicle No.</label>
                            <input type="text" class="form-control"  placeholder="Enter Vehicle No." id="vehicle_no" name="vehicle_no" required>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="group" class="form-label font-14 font-heading">Group</label>
                            <select class="form-select" name="group" id="group" required>
                                <option value="">Select Group</option>
                                @foreach($item_groups as $key => $group)
                                    <option value="{{$group->id}}">{{$group->group_name}}</option>
                                @endforeach
                                
                            </select>
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
    });
</script>
@endsection