@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] { -moz-appearance: textfield; }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- ERRORS --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                {{-- TITLE --}}
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
                    title-border-redius border-divider shadow-sm">Add Supplier (Waste Kraft)</h5>

                {{-- FORM --}}
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm mt-3"
                      method="POST"
                      action="{{ route('supplier.store') }}">

                    @csrf
                    <input type="hidden" id="rate_date" name="rate_date">

                    <div class="row">

                        {{-- ACCOUNT --}}
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Account</label>
                            <select class="form-select select2-single" id="account" name="account" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DATE --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>

                        {{-- LOCATION --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Location</label>
                            <select class="form-select location"
                                    name="location[0]"
                                    id="location_1"
                                    data-id="1"
                                    required>
                                <option value="">Select Location</option>
                                <option value="add_new">Add New</option>
                            </select>
                        </div>

                        {{-- HEAD RATES --}}
                        @foreach($heads as $h)
                            @php $col = strlen($h->name) > 8 ? 2 : 1; @endphp

                            <div class="mb-{{ $col }} col-md-{{ $col }}">
                                <label class="form-label font-14 font-heading">{{ $h->name }}</label>

                                {{-- FIXED NAME ATTRIBUTE --}}
                                <input type="hidden"
                                       name="head_id_0[]"
                                       value="{{ $h->id }}"
                                       class="head_id_1">

                                <input type="number"
                                       step="any"
                                       class="form-control head_rate_1"
                                       name="head_rate_0[]"
                                       placeholder="RATE"
                                       data-head_id="{{ $h->id }}">
                            </div>
                        @endforeach

                        {{-- BONUS --}}
                        <div class="mb-1 col-md-1">
                            <label class="form-label font-14 font-heading">Bonus</label>

                            {{-- FIXED NAME ATTRIBUTE --}}
                            <input type="number"
                                   step="any"
                                   class="form-control bonus head_bonus_1"
                                   name="bonus_0[]"
                                   placeholder="Bonus"
                                   data-index="1">
                        </div>

                        {{-- ADD ROW BUTTON --}}
                        <div class="mb-1 col-md-1">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                data-id="1"
                                class="bg-primary rounded-circle add_more"
                                width="30" height="30"
                                viewBox="0 0 24 24" fill="none"
                                style="cursor:pointer;margin-top:35px">
                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                            </svg>
                        </div>

                        <span class="add_div"></span>

                        {{-- STATUS --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select</option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-xs-primary mt-2">SUBMIT</button>

                </form>
                {{-- END FORM --}}
            </div>
        </div>
    </section>

    {{-- ADD LOCATION MODAL --}}
    <div class="modal fade" id="location_modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content p-4 border-divider border-radius-8">

                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
                    title-border-redius border-divider shadow-sm">Add Location</h5>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label font-14 font-heading">Location</label>
                        <input type="text" id="location_name" class="form-control" placeholder="Enter Location">
                        <input type="hidden" id="row_id">
                    </div>
                </div>

                <button class="btn btn-xs-primary mt-3 save_location">SAVE</button>
            </div>
        </div>
    </div>

</div>

@include('layouts.footer')

{{-- FULL WORKING JS --}}
<script>

var selected_location_arr = [];
var location_index = 1;
var location_list =
    "<option value=''>Select Location</option>" +
    "<option value='add_new'>Add New</option>";

var heads = @json($heads);

$(document).ready(function(){

    // SELECT2
    $(".select2-single").select2();

    loadLocations(); // populate dropdowns with AJAX options

    // ADD MORE Rows
    $('.add_more').click(function(){

        location_index++;
        let idx = location_index - 1; // index used in names

        let html = `
        <div class="row mt-3 new-row">

            <div class="mb-3 col-md-3">
                <label class="form-label font-14 font-heading">Location</label>
                <select class="form-select location"
                        name="location[${idx}]"
                        id="location_${location_index}"
                        data-id="${location_index}"
                        required>
                    ${location_list}
                </select>
            </div>`;

        heads.forEach(function(h){
            let col = h.name.length > 8 ? 2 : 1;

            html += `
            <div class="mb-${col} col-md-${col}">
                <label class="form-label font-14 font-heading">${h.name}</label>

                <input type="hidden"
                       name="head_id_${idx}[]"
                       value="${h.id}"
                       class="head_id_${location_index}">

                <input type="number"
                       step="any"
                       class="form-control head_rate_${location_index}"
                       name="head_rate_${idx}[]"
                       placeholder="RATE"
                       data-head_id="${h.id}">
            </div>`;
        });

        html += `
        <div class="mb-1 col-md-1">
            <label class="form-label font-14 font-heading">Bonus</label>
            <input type="number"
                   step="any"
                   class="form-control bonus head_bonus_${location_index}"
                   name="bonus_${idx}[]"
                   placeholder="Bonus"
                   data-index="${location_index}">
        </div>

        <div class="mb-1 col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger remove_row">X</button>
        </div>

        </div>`;

        $(".add_div").append(html);
    });

    // REMOVE ROW
    $(document).on("click", ".remove_row", function(){
        $(this).closest(".new-row").remove();
    });

    // LOCATION CHANGE
    $(document).on("change", ".location", function(){

        let id = $(this).data("id");
        let idx = id - 1;
        let val = $(this).val();

        $("#row_id").val(id);

        if(val === "add_new"){
            $("#location_modal").modal('show');
            return;
        }

        // Prevent duplicate selection
        if(val !== "" && selected_location_arr.includes(val)){
            alert("Already selected!");
            $(this).val('');
            return;
        }

        selected_location_arr = [];
        $(".location").each(function(){
            let v = $(this).val();
            if(v) selected_location_arr.push(v);
        });

        // Fetch rate (only if date is selected)
        if(!$("#date").val()){
            // If user didn't select date, clear rates and prompt
            $(".head_rate_"+id).val('');
            alert("Please select Date first");
            $(this).val('');
            return;
        }

        $.post("{{ url('rate-by-location') }}", {
            _token : "{{ csrf_token() }}",
            location_id : val,
            date : $("#date").val()
        }, function(res){

            if(res && res.rate && res.rate.length > 0){

                let group = [];
                res.rate.forEach(e => group[e.head_id] = e.head_rate);

                $(`.head_rate_${id}`).each(function(){
                    let hid = $(this).data("head_id");
                    if(group[hid] !== undefined){
                        $(this).val(group[hid]);
                        $(this).attr("data-rate", group[hid]);
                    } else {
                        $(this).val('');
                        $(this).removeAttr('data-rate');
                    }
                });

                $("#rate_date").val(res.latestDate || '');
            } else {
                // no rates found: clear and remove data-rate
                $(`.head_rate_${id}`).each(function(){
                    $(this).val('');
                    $(this).removeAttr('data-rate');
                });
                $("#rate_date").val('');
            }
        });

    });

    // BONUS CALCULATION
    $(document).on("keyup", ".bonus", function(){

        let dataIndex = $(this).data("index"); // this is numeric row id, matches class suffix
        let bonus = parseFloat($(this).val() || 0);

        $(`.head_rate_${dataIndex}`).each(function(){

            if($(this).attr("data-rate")){
                $(this).val( parseFloat($(this).attr("data-rate")) + bonus );
            }
        });
    });

});

// LOAD LOCATIONS (uses the global location_list)
function loadLocations(){
    $.post("{{ url('get-supplier-location') }}", {
        _token : "{{ csrf_token() }}"
    }, function(res){

        if(res && Array.isArray(res.location) && res.location.length > 0){
            res.location.forEach(function(e){
                location_list += `<option value="${e.id}">${e.name}</option>`;
            });
        }

        // Populate ALL existing selects with class .location
        $(".location").each(function(){
            // preserve 'add_new' and '' at top: set html to location_list
            $(this).html(location_list);
        });
    });
}

// SAVE NEW LOCATION
$(document).on("click", ".save_location", function(){

    let name = $("#location_name").val();
    let row = $("#row_id").val();

    if(name === ""){
        alert("Enter location");
        return;
    }

    let option = `<option value="${name}">${name}</option>`;

    location_list += option;
    $(".location").append(option);

    // set the specific select to this new value
    $("#location_"+row).val(name).trigger('change');

    selected_location_arr.push(name);

    $("#location_modal").modal('hide');
    $("#location_name").val('');
});

// on date change, refresh each location (re-fetch rates)
$(document).on("change", "#date", function(){
    selected_location_arr = [];
    $(".location").each(function(){ $(this).change(); });
});

</script>

@endsection
