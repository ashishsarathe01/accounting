@extends('layouts.app')

@section('content')
@include('layouts.header')







<style>
.get_info:hover { color: blue; cursor:pointer; }
.overdue:hover { color: blue; cursor:pointer; }
/* S.No Column */
.serial-col {
    width: 60px;
    text-align: center;
}

/* GROUP HEADER */
.group-row {
    background: #e9edf5;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.25s ease;
}
.group-row:hover {
    background: #dae2f3;
}

/* EVEN/ODD alternate */
.table-striped tbody tr:nth-child(odd):not(.group-child) {
    background: #ffffff;
}
.table-striped tbody tr:nth-child(even):not(.group-child) {
    background: #f7f9fc;
}

/* CHILD ROWS: completely different look */
.group-child {
    display: none;
    background: #E1F3FF !important;
    transition: background 0.3s ease;
}
.group-child:hover {
    background: #CCF1FF  !important;
}

/* CHILDS: subtle border + indentation */
.group-child td {
    border-left: 4px solid #ffcf85 !important;
}

/* Icons */
.chevron {
    transition: transform 0.25s ease;
    font-size: 12px;
}
.chevron.rotate {
    transform: rotate(90deg);
}
.arrow {
    color: #b36b00;
    font-weight: bold;
}

/* Amount fields */
.amount { text-align:right; }
.overdue { color:#d9534f !important; font-weight:bold; }

/* Hover pointer */
.get_info:hover, .overdue:hover {
    color: blue !important;
    cursor:pointer;
}

 td, th {
        font-size: 15px !important;
    }

.hidden { display: none; }

.arrow {
    display:inline-block;
    transition: transform 0.25s ease;
}

.arrow.rotate {
    transform: rotate(90deg);
}


</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- ======================= PAGE TITLE ======================= -->
<div class="table-title-bottom-line position-relative d-flex justify-content-between
            align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">Receivable Report</h5>
</div>


<!-- ======================= FILTER FORM ======================= -->
<form method="GET" action="{{ url('receiable/index') }}" class="mt-3">

    <div class="row">

        <!-- Date -->
        <div class="col-md-3">
            <label><strong>Date</strong></label>
            <input type="date" name="date" class="form-control" value="{{ $today }}">
        </div>

        <!-- Show Type -->
        <div class="col-md-4">
            <label><strong>Show Report</strong></label><br>

            <label class="mr-3">
                <input type="radio" name="show_type" value="all"
                       {{ request('show_type', 'all') == 'all' ? 'checked' : '' }}>
                All Parties
            </label>

            <label class="mr-3">
                <input type="radio" name="show_type" value="party"
                       {{ request('show_type') == 'party' ? 'checked' : '' }}>
                Party-wise
            </label>

            <label class="mr-3">
                <input type="radio" name="show_type" value="allgroup"
                       {{ request('show_type') == 'allgroup' ? 'checked' : '' }}>
                All-Group
            </label>

            <label class="mr-3">
                <input type="radio" name="show_type" value="group"
                       {{ request('show_type') == 'group' ? 'checked' : '' }}>
                Group-wise
            </label>
        </div>


        <!-- Party Dropdown -->
        <div class="col-md-4" id="partyDiv"
             style="display: {{ request('show_type') == 'party' ? 'block' : 'none' }};">
            <label><strong>Select Party</strong></label>
            <select name="party_id" class="form-control select2-single">
                <option value="">Select Party</option>
                @foreach($allParties as $p)
                    <option value="{{ $p->id }}" {{ request('party_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->account_name }}
                    </option>
                @endforeach
            </select>
        </div>


        <!-- Group Dropdown -->
        <div class="col-md-4" id="groupDiv"
             style="display: {{ request('show_type') == 'group' ? 'block' : 'none' }};">
            <label><strong>Select Group</strong></label>
            <select name="group_id" class="form-control select2-single">
                <option value="">Select Group</option>
                @foreach($allGroupsList as $grp)
                    <option value="{{ $grp->id }}" {{ request('group_id') == $grp->id ? 'selected' : '' }}>
                        {{ $grp->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ url('receivable-report') }}" class="btn btn-secondary">Reset</a>
    </div>

</form>
<!-- ======================= END FILTER FORM ======================= -->


<!-- ======================= REPORT TABLE ======================= -->
<table class="table table-bordered table-striped mt-4">
    <thead>
        <tr>
            <th >S.No</th>
            <th>Party / Group</th>
            <th style="text-align:right;">Receivable</th>
            <th style="text-align:right;">Overdue</th>
            <th style="text-align: center; padding-left: 0; padding-right: 0; width:20%;">Remark</th>
            <th style="text-align:center;">Action</th>
        </tr>
    </thead>

    <tbody>

@php
$i = 1;
$totalReceivable = 0;
$totalOverdue = 0;
@endphp


<!-- ============================================================ -->
<!-- =============== CASE 1: ALL PARTIES ======================== -->
<!-- ============================================================ -->
@if(request('show_type','all') == 'all')
    @foreach($data as $row)
        @php
            $totalReceivable += $row->receivable;
            $totalOverdue += $row->overdue;
        @endphp

        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day?? '-'}})<br>{{ $row->mobile }}</td>

            <td class="get_info"
                data-id="{{ $row->id }}"
                style="text-align:right; cursor:pointer;">
                {{ formatIndianNumber($row->receivable,2) }}
            </td>

            <td class="overdue"
                onclick="window.location='{{ route('overdue.report',$row->id) }}?date={{ $today }}'"
                style="text-align:right; color:red; font-weight:bold;">
                {{ formatIndianNumber($row->overdue,2) }}
            </td>
                <td class="latest-response"
                    data-account-id="{{ $row->id }}"
                    style="text-align:center; padding-left:0; padding-right:0; font-size:16px!important;">
                    
                    <span class="resp-text-{{ $row->id }}">
                        {{ $row->response ?? '' }}
                        @if ($row->response_date ?? '')
                            ({{ date('d-m-Y', strtotime($row->response_date)) }})
                        @endif
                    </span>
                </td>


            <td class="action" style="text-align:center;">
                <span class="record_response"
                        data-id="{{ $row->id ?? '' }}"
                        data-name="{{ $row->party_name ?? '' }}"
                    style="
                        background:#007bff;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">
                    Response
                </span>
                <span class="log-btn"
                data-account-id="{{ $row->id }}"
                data-account-name="{{ $row->party_name }}"
                style="
                        background:#385E3C;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">log</span>
            </td>


        </tr>
    @endforeach
@endif



<!-- ============================================================ -->
<!-- =============== CASE 2: PARTY-WISE ========================= -->
<!-- ============================================================ -->
@if(request('show_type') == 'party')
    @foreach($data as $row)
        @php
            $totalReceivable += $row->receivable;
            $totalOverdue += $row->overdue;
        @endphp

        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day ?? '-'}})<br>{{ $row->mobile }}</td>

            <td class="get_info"
                data-id="{{ $row->id }}"
                style="text-align:right; cursor:pointer;">
                {{ formatIndianNumber($row->receivable,2) }}
            </td>

            <td class="overdue"
                onclick="window.location='{{ route('overdue.report',$row->id) }}?date={{ $today }}'"
                style="text-align:right; color:red; font-weight:bold;">
                {{ formatIndianNumber($row->overdue,2) }}
            </td>
            <td class="latest-response"
                    data-account-id="{{ $row->id }}"
                    style="text-align:center; padding-left:0; padding-right:0; font-size:16px!important;">
                    
                    <span class="resp-text-{{ $row->id }}">
                        {{ $row->response ?? '' }}
                        @if ($row->response_date ?? '')
                            ({{ date('d-m-Y', strtotime($row->response_date)) }})
                        @endif
                    </span>
                </td>
            <td class="action" style="text-align:center;">
                <span class="record_response"
                 data-id="{{ $row->id ?? '' }}"
                        data-name="{{ $row->party_name ?? '' }}"
                    style="
                        background:#007bff;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">
                    Response
                </span>
                <span class="log-btn"
                data-account-id="{{ $row->id }}"
                data-account-name="{{ $row->party_name }}"
                style="
                        background:#385E3C;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">log</span>
            </td>
        </tr>
    @endforeach
@endif



<!-- ============================================================ -->
<!-- =============== CASE 3: ALL GROUPS ========================= -->
<!-- ============================================================ -->


@php $i = 1; @endphp

@if(request('show_type') == 'allgroup')
    @foreach($groupWiseData as $grp)
        @include('components.group-row', ['grp' => $grp, 'level' => 0])
    @endforeach
@endif








<!-- ============================================================ -->
<!-- =============== CASE 4: GROUP-WISE ========================= -->
<!-- ============================================================ -->
@if(request('show_type') == 'group')
    @foreach($data as $acc)
        @php
            $totalReceivable += $acc->receivable;
            $totalOverdue += $acc->overdue;
        @endphp
        @if($acc->overdue<=0)
            @continue;
        @endif
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $acc->party_name }} ({{$acc->credit_days ?? '-'}}/{{$acc->due_day ?? '-'}})<br>{{ $acc->mobile }}</td>

            <td class="get_info"
                data-id="{{ $acc->id }}"
                style="text-align:right; cursor:pointer;">
                {{ formatIndianNumber($acc->receivable,2) }}
            </td>

            <td class="overdue"
                onclick="window.location='{{ route('overdue.report',$acc->id) }}?date={{ $today }}'"
                style="text-align:right; color:red; font-weight:bold;">
                {{ formatIndianNumber($acc->overdue,2) }}
            </td>
             <td class="latest-response"
                    data-account-id="{{ $acc->id }}"
                    style="text-align:center; padding-left:0; padding-right:0; font-size:16px!important;">
                   <span class="resp-text-{{ $acc->id }}">
                    {{ $acc->response ?? '' }}
                    @if ($acc->response_date ?? '')
                        ({{ date('d-m-Y', strtotime($acc->response_date)) }})
                    @endif
                     </span>
                </td>
            <td class="action" style="text-align:center;">
                <span class="record_response"
                 data-id="{{ $acc->id ?? '' }}"
                        data-name="{{ $acc->party_name ?? ''}}"
                    style="
                        background:#007bff;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">
                    Response
                </span>
                <span class="log-btn"
                data-account-id="{{ $acc->id }}"
                data-account-name="{{ $acc->party_name }}"
                style="
                        background:#385E3C;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">log</span>
            </td>
        </tr>
    @endforeach
@endif


</tbody>

<tfoot>
<tr style="background:#f0f0f0; font-weight:bold;">
    <td colspan="2" style="text-align:center;">TOTAL</td>
    <td style="text-align:right;">{{ formatIndianNumber($totalReceivable,2) }}</td>
    <td style="text-align:right; color:red;">{{ formatIndianNumber($totalOverdue,2) }}</td>
    <td></td>
    <td></td>

</tr>
</tfoot>

</table>

</div>
</div>





                <!-- ================= RESPONSE MODAL ================= -->
                <div class="modal fade" id="responseModal" tabindex="-1">
                    <div class="modal-dialog modal-md">
                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">
                                <h5 style="color:white;"class="modal-title" id="responseTitle">Response</h5>
                                
                            </div>


                        
                            <div class="modal-body">
                                

                                        <input type="hidden" name="account_id" id="responseAccountId">

                                        <div class="form-group">
                                            <label><strong>Date</strong></label>
                                            <input type="date" class="form-control" name="response_date" id="responseDate" required>
                                        </div>

                                        <div class="form-group">
                                            <label><strong>Remark</strong></label>
                                            <textarea class="form-control" name="response" id="responseRemark" rows="3" required></textarea>
                                        </div>

                                   
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary close-model" data-dismiss="modal">Close</button>
                                 <button type="button" id="saveResponseBtn" class="btn btn-primary">Save</button>

                            </div>
                        
                        </div>
                    </div>
                </div>


                                    <!-- LOG Modal -->
                    <div class="modal fade" id="logModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalAccountName"></h5> 
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr style="background:#f1f5f9;">
                                                <th>Date</th>
                                                <th>Response</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody id="logTableBody">
                                            <!-- Dynamic rows will come here -->
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>


</section>
</div>

@include('layouts.footer')


<!-- ======================= SCRIPT ======================= -->
<script>

$(document).ready(function () {
    $('.select2-single').select2();


    // Show/Hide dropdowns
    $("input[name='show_type']").on("change", function(){
        $("#partyDiv").toggle(this.value === "party");
        $("#groupDiv").toggle(this.value === "group");
        $('.select2-single').select2();
    });


    // Account Ledger redirect
    $(document).on("click",".get_info",function(){
        window.location = "{{url('accountledger-filter')}}/?party=" 
            + $(this).data('id') 
            + "&from_date={{$firstDate}}&to_date={{$today}}";
    });



});


document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".group-row").forEach(function (row) {

        row.addEventListener("click", function (event) {

            // Prevent expansion when clicking on inputs/buttons inside row
            if (event.target.closest("input, button, a")) return;

            let groupId = this.dataset.group;

            // toggle arrow
            let arrow = this.querySelector(".arrow");
            arrow.classList.toggle("rotate");

            // Find direct children of this group
            let children = document.querySelectorAll(".child-of-" + groupId);

            children.forEach(function (tr) {

                // toggle visibility of direct children
                tr.classList.toggle("hidden");

                // If we are collapsing → collapse ALL nested children
                if (tr.classList.contains("hidden")) {
                    collapseAllNested(tr.dataset.group);
                }
            });

        });

    });

    // Recursive collapse function
    function collapseAllNested(parentId) {
        let nested = document.querySelectorAll(".child-of-" + parentId);
        nested.forEach(function (tr) {
            tr.classList.add("hidden");

            // also collapse arrow if subgroup row
            let arrow = tr.querySelector(".arrow");
            if (arrow) arrow.classList.remove("rotate");

            // collapse deeper levels
            if (tr.dataset.group) {
                collapseAllNested(tr.dataset.group);
            }
        });
    }

});


// OPEN RESPONSE MODAL
$(document).on("click", ".record_response", function () {

    let accId = $(this).data("id");
    let accName = $(this).data("name");

    // set title
    $("#responseTitle").text("Response for: " + accName);

    // fill form values
    $("#responseAccountId").val(accId);
    $("#responseDate").val("{{ $today }}");

    // clear remark
    $("#responseRemark").val("");

    // open modal
    $("#responseModal").modal("show");
});



$(document).on('click', '.log-btn', function () {

    let acc_id   = $(this).data('account-id');
    let acc_name = $(this).data('account-name');

    $("#modalAccountName").text("Last 5 Responses - " + acc_name);

    // Clear previous data
    $("#logTableBody").html('<tr><td colspan="2">Loading...</td></tr>');

    $.ajax({
        url: "{{ url('/account/last-responses') }}/" + acc_id,
        type: "GET",
        success: function(data) {

            let rows = "";

            if (data.length === 0) {
                rows = "<tr><td colspan='2'>No records found</td></tr>";
            } else {
                data.forEach(function(item) {
                    rows += `
                        <tr>
                            <td>${item.response_date}</td>
                            <td>${item.response}</td>
                        </tr>
                    `;
                });
            }

            $("#logTableBody").html(rows);
            $("#logModal").modal('show');
        }
    });

});


$(document).on("click", ".close-model", function () {

    // Hide modal
    $("#responseModal").modal("hide");


    // Reset hidden fields
    $("#responseAccountId").val("");

    // Clear modal title (if you change it dynamically)
    $("#responseTitle").text("Response");

});

$(document).ready(function () {

    $("#saveResponseBtn").click(function () {

        let formData = {
            account_id: $("#responseAccountId").val(),
            response_date: $("#responseDate").val(),
            response: $("#responseRemark").val(),
            _token: "{{ csrf_token() }}"
        };

        $.ajax({
            url: "{{ route('response.store') }}",
            type: "POST",
            data: formData,
            success: function (res) {

                // Update latest response LIVE
                  let html = formData.response;
                    
                    // Convert date → DD-MM-YYYY
                    if (formData.response_date) {
                    
                        let d = new Date(formData.response_date);
                    
                        // format with padStart
                        let day     = d.getDate().toString().padStart(2, '0');
                        let month   = (d.getMonth() + 1).toString().padStart(2, '0');
                        let year    = d.getFullYear();
                    
                        let formattedDate = `${day}-${month}-${year}`;
                    
                        html += ` (${formattedDate})`;
                    }
                    
                    // Update UI instantly
                    $(".resp-text-" + formData.account_id).html(html);


                // Close modal
                $("#responseModal").modal('hide');

                
            },
            error: function (xhr) {

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let msg = "";
                    for (let key in errors) msg += errors[key][0] + "\n";
                    alert(msg);
                } else {
                    alert("Something went wrong!");
                }
            }
        });

    });

});





</script>



@endsection
