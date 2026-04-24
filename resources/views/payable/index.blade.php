@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
/* ================= GLOBAL ================= */
td, th {
    font-size: 14px !important;
    vertical-align: middle !important;
}

.table {
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

/* Header gradient */
.table thead {
    background: linear-gradient(90deg,#5f6df5,#8f94fb);
    color: #fff;
    font-weight: 600;
}

.table thead th {
    border: none !important;
    padding: 12px;
}

.table tbody td {
    padding: 10px 12px;
}

/* Alternate rows */
.table-striped tbody tr:nth-child(even) {
    background: #f9fbff;
}

/* Hover */
.table tbody tr:hover {
    background: #eef4ff !important;
    transition: 0.2s ease;
}

/* ================= FILTER CARD ================= */
.filter-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
}

/* ================= AMOUNT STYLE ================= */
.amount {
    text-align: right;
    font-weight: 600;
}

/* Party values normal */
.overdue {
    color: #dc3545 !important;
    font-weight: 400;
}

.get_info {
    color: #2d6cdf;
    font-weight: 400;
}

.get_info:hover {
    text-decoration: underline;
}

/* ================= BUTTON CLEAN STYLE ================= */
.btn {
    border-radius: 6px;
    padding: 7px 18px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #2563eb;
    border: none;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(37,99,235,0.25);
}

.btn-outline-secondary {
    border: 1px solid #cbd5e1;
    color: #475569;
    background: #f8fafc;
}

.btn-outline-secondary:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

/* ================= TOTAL ROW ================= */
tfoot tr {
    background: #f1f5ff !important;
    font-size: 15px;
}

tfoot td {
    padding: 12px !important;
}

/* ================= FORM ================= */
.form-control {
    border-radius: 8px;
    border: 1px solid #e0e6f5;
}

.form-control:focus {
    border-color: #5f6df5;
    box-shadow: 0 0 0 2px rgba(95,109,245,0.1);
}
/* ================= BUTTON STYLE (Same as Receivable) ================= */
.btn-modern {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    transition: 0.2s ease;
}

.btn-response {
    background: linear-gradient(90deg,#007bff,#4da3ff);
    color: #fff;
}

.btn-response:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.btn-log {
    background: linear-gradient(90deg,#2e7d32,#66bb6a);
    color: #fff;
}

.btn-log:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(46,125,50,0.3);
}

/* ================= REPORT RADIO STYLE ================= */
.report-label {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.report-options {
    display: grid;
    grid-template-columns: repeat(2, auto);
    row-gap: 10px;
    column-gap: 25px;
}

.report-radio {
    display: flex;
    align-items: center;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    gap: 6px;
}

.report-radio input[type="radio"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
/* ================= GROUP EXPAND STYLE ================= */

.hidden {
    display: none;
}

.group-row {
    cursor: pointer;
    user-select: none;
}

.group-row:hover {
    background: #f3f7ff !important;
}

.arrow {
    display: inline-block;
    margin-right: 6px;
    font-size: 14px;
    transition: transform 0.2s ease, color 0.2s ease;
    cursor: pointer;
    color: #5f6df5;
}

.group-row:hover .arrow {
    color: #2d3edb;
}

.arrow.rotate {
    transform: rotate(90deg);
}
/* Make group name bold */
.group-row td:nth-child(2) {
    font-weight: 700;
    color: #1e293b;
}
/* Make Payable & Overdue bold only for group rows */
.group-row td:nth-child(3),
.group-row td:nth-child(4) {
    font-weight: 700 !important;
}
/* Overdue clickable style */
.overdue-clickable {
    cursor: pointer;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Payable Report</h5>
                </div>

                <!-- ================= FILTER FORM ================= -->
                 <div class="filter-card mt-3">
                <form method="GET" action="{{ url('payable/index') }}" class="mt-3">

                    <div class="row align-items-end">

                    <!-- Date -->
                    <div class="col-md-3">
                        <label class="fw-bold">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $today }}">
                    </div>

                    <!-- Show Report -->
                    <div class="col-md-4">
                        <label class="fw-bold d-block mb-1">Show Report</label>

                        <div class="report-options">
                    <label class="report-radio">
                        <input type="radio" name="show_type" value="all"
                            {{ request('show_type', 'all') == 'all' ? 'checked' : '' }}>
                        <span>All Parties</span>
                    </label>

                    <label class="report-radio">
                        <input type="radio" name="show_type" value="party"
                            {{ request('show_type') == 'party' ? 'checked' : '' }}>
                        <span>Party-wise</span>
                    </label>

                    <label class="report-radio">
                        <input type="radio" name="show_type" value="allgroup"
                            {{ request('show_type') == 'allgroup' ? 'checked' : '' }}>
                        <span>All Group</span>
                    </label>

                    <label class="report-radio">
                        <input type="radio" name="show_type" value="group"
                            {{ request('show_type') == 'group' ? 'checked' : '' }}>
                        <span>Group-wise</span>
                    </label>
                </div>
                    </div>

                    <!-- Group Dropdown -->
                    <div class="col-md-4" id="groupDiv"
                        style="display: {{ request('show_type') == 'group' ? 'block' : 'none' }};">

                        <label class="fw-bold mb-1 d-block">Select Group</label>
                        <select name="group_id" class="form-control select2-single">
                            <option value="">Select Group</option>

                            @foreach($allGroupsList as $grp)
                                <option value="{{ $grp->id }}"
                                    {{ request('group_id') == $grp->id ? 'selected' : '' }}>
                                    {{ $grp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Party Dropdown -->
                <div class="col-md-4" id="partyDiv"
                    style="display: {{ request('show_type') == 'party' ? 'block' : 'none' }};">

                    <label class="fw-bold mb-1 d-block">Select Party</label>
                    <select name="party_id" class="form-control select2-single">
                        <option value="">Select Party</option>

                        @foreach($allParties as $p)
                            <option value="{{ $p->id }}"
                                {{ request('party_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                </div>

                    <!-- GROUP DROPDOWN (only when Group-wise selected) -->


                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            Filter
                        </button>

                        <a href="{{ url('payable-report') }}" class="btn btn-outline-secondary px-4 ms-2">
                            Reset
                        </a>
                    </div>

                </form>
                </div>
                <!-- ================= END FILTER FORM ================= -->


                <!-- ================= TABLE ================= -->
                <table class="table table-bordered table-striped mt-4">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Party Name</th>
                            <th style="text-align:right;">Payable</th>
                            <th style="text-align:right;">Overdue</th>
                            <th style="text-align:center; width:20%;">Remark</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @php
                    $i = 1;
                    $totalReceivable = 0;
                    $totalOverdue = 0;
                    @endphp

                    {{-- ================= ALL PARTIES ================= --}}
                    @if(request('show_type','all') == 'all')
                        @foreach($data as $row)
                            @php
                                $totalReceivable += $row->receivable;
                                $totalOverdue += $row->overdue;
                            @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day ?? '-'}})<br>{{ $row->mobile }}</td>
                                <td class="get_info text-end" data-id="{{ $row->id }}">
                                    {{ formatIndianNumber($row->receivable, 2) }}
                                </td>
                                <td class="text-end text-danger fw-bold overdue-clickable"
                                    onclick="window.location='{{ route('payable.overdue.report', $row->id) }}?date={{ request('date', $today) }}'">
                                    {{ formatIndianNumber($row->overdue, 2) }}
                                </td>
                                <td style="text-align:center;">
    <span class="resp-text-{{ $row->id }}">
        {{ $row->response ?? '' }}
        @if ($row->response_date ?? '')
            ({{ date('d-m-Y', strtotime($row->response_date)) }})
        @endif
    </span>
</td>

<td style="text-align:center;">

    <span class="record_response"
        data-id="{{ $row->id }}"
        data-name="{{ $row->party_name }}"
        style="background:#007bff;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Response
    </span>

    <span class="log-btn"
        data-account-id="{{ $row->id }}"
        data-account-name="{{ $row->party_name }}"
        style="background:#385E3C;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Log
    </span>

</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ================= PARTY-WISE ================= --}}
                    @if(request('show_type') == 'party')
                        @foreach($data as $row)
                            @php
                                $totalReceivable += $row->receivable;
                                $totalOverdue += $row->overdue;
                            @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day ?? '-'}})<br>{{ $row->mobile }}</td>
                                <td class="get_info text-end" data-id="{{ $row->id }}">
                                    {{ formatIndianNumber($row->receivable, 2) }}
                                </td>
                                <td class="text-end text-danger fw-bold overdue-clickable"
                                    onclick="window.location='{{ route('payable.overdue.report', $row->id) }}'">
                                    {{ formatIndianNumber($row->overdue, 2) }}
                                </td>
                                <td style="text-align:center;">
    <span class="resp-text-{{ $row->id }}">
        {{ $row->response ?? '' }}
        @if ($row->response_date ?? '')
            ({{ date('d-m-Y', strtotime($row->response_date)) }})
        @endif
    </span>
</td>

<td style="text-align:center;">

    <span class="record_response"
        data-id="{{ $row->id }}"
        data-name="{{ $row->party_name }}"
        style="background:#007bff;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Response
    </span>

    <span class="log-btn"
        data-account-id="{{ $row->id }}"
        data-account-name="{{ $row->party_name }}"
        style="background:#385E3C;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Log
    </span>

</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- ================= ALL GROUP ================= --}}
                    @if(request('show_type') == 'allgroup')

    @php
        foreach($groupWiseData as $grp){
            $totalReceivable += $grp['total_receivable'];
            $totalOverdue += $grp['total_overdue'];
        }
    @endphp

    @foreach($groupWiseData as $grp)
        @include('components.group-row', [
            'grp' => $grp,
            'level' => 0,
            'overdueRoute' => 'payable.overdue.report'
        ])
    @endforeach

@endif

                    {{-- ================= GROUP-WISE ================= --}}
                    @if(request('show_type') == 'group')
                        @foreach($data as $row)
                            @php
                                $totalReceivable += $row->receivable;
                                $totalOverdue += $row->overdue;
                            @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day ?? '-'}})<br>{{ $row->mobile }}</td>
                                <td class="get_info text-end" data-id="{{ $row->id }}">
                                    {{ formatIndianNumber($row->receivable, 2) }}
                                </td>
                                <td class="text-end text-danger fw-bold overdue-clickable"
                                    onclick="window.location='{{ route('payable.overdue.report', $row->id) }}'">
                                    {{ formatIndianNumber($row->overdue, 2) }}
                                </td>
                                <td style="text-align:center;">
    <span class="resp-text-{{ $row->id }}">
        {{ $row->response ?? '' }}
        @if ($row->response_date ?? '')
            ({{ date('d-m-Y', strtotime($row->response_date)) }})
        @endif
    </span>
</td>

<td style="text-align:center;">

    <span class="record_response"
        data-id="{{ $row->id }}"
        data-name="{{ $row->party_name }}"
        style="background:#007bff;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Response
    </span>

    <span class="log-btn"
        data-account-id="{{ $row->id }}"
        data-account-name="{{ $row->party_name }}"
        style="background:#385E3C;color:#fff;padding:4px 10px;border-radius:6px;font-weight:bold;cursor:pointer;">
        Log
    </span>

</td>
                            </tr>
                        @endforeach
                    @endif

                    </tbody>

                    <!-- ===== TOTAL ROW ===== -->
                    <tfoot>
<tr>
    <td colspan="2" style="text-align:center;">TOTAL</td>

    <td style="text-align:right;">
        {{ formatIndianNumber($totalReceivable, 2) }}
    </td>

    <td style="text-align:right; color:red;">
        {{ formatIndianNumber($totalOverdue, 2) }}
    </td>

    <td></td>
    <td></td>
</tr>
</tfoot>
                </table>

            </div> <!-- END content col -->

        </div> <!-- END row -->
    </section>
</div>
<!-- RESPONSE MODAL -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="responseTitle">Response</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" id="responseAccountId">

                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" id="responseDate" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Remark</label>
                    <textarea id="responseRemark" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="saveResponseBtn">Save</button>
            </div>

        </div>
    </div>
</div>
<!-- LOG MODAL -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalAccountName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Response</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@include('layouts.footer')
<!-- Show/Hide Group Dropdown -->
<script>
$(document).ready(function () {

    // Initialize Select2
    $('.select2-single').select2({
        width: '100%'
    });

    // Show / Hide Party & Group dropdowns
    $("input[name='show_type']").on("change", function () {

        let type = $(this).val();

        // Toggle party dropdown
        $("#partyDiv").toggle(type === "party");

        // Toggle group dropdown
        $("#groupDiv").toggle(type === "group");

        // Reinitialize select2 (important after toggle)
        $('.select2-single').select2({
            width: '100%'
        });
    });

    // Account Ledger redirect
    $(".get_info").click(function () {

        window.location =
            "{{url('accountledger-filter')}}/?party=" +
            $(this).attr('data-id') +
            "&from_date={{$firstDate}}&to_date={{$today}}";

    });

});

// OPEN RESPONSE MODAL
$(document).on("click", ".record_response", function () {

    let accId = $(this).data("id");
    let accName = $(this).data("name");

    $("#responseTitle").text("Response for: " + accName);
    $("#responseAccountId").val(accId);
    $("#responseDate").val("{{ $today }}");
    $("#responseRemark").val("");

    $("#responseModal").modal("show");
});


// SAVE RESPONSE
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
        success: function () {

            let html = formData.response;

            if (formData.response_date) {
                let d = new Date(formData.response_date);
                let day = d.getDate().toString().padStart(2, '0');
                let month = (d.getMonth() + 1).toString().padStart(2, '0');
                let year = d.getFullYear();
                html += ` (${day}-${month}-${year})`;
            }

            $(".resp-text-" + formData.account_id).html(html);
            $("#responseModal").modal('hide');
        }
    });

});


// LOG BUTTON
$(document).on('click', '.log-btn', function () {

    let acc_id = $(this).data('account-id');
    let acc_name = $(this).data('account-name');

    $("#modalAccountName").text("Last 5 Responses - " + acc_name);
    $("#logTableBody").html('<tr><td colspan="2">Loading...</td></tr>');

    $.ajax({
        url: "{{ url('/account/last-responses') }}/" + acc_id,
        type: "GET",
        success: function(data) {

            let rows = "";

            if (data.length === 0) {
                rows = "<tr><td colspan='2'>No records</td></tr>";
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

document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".group-row").forEach(function (row) {

        row.addEventListener("click", function (event) {

            // Prevent toggle when clicking buttons
            if (event.target.closest("input, button, a, span.record_response, span.log-btn"))
                return;

            let groupId = this.dataset.group;

            let arrow = this.querySelector(".arrow");
            arrow.classList.toggle("rotate");

            let children = document.querySelectorAll(".child-of-" + groupId);

            children.forEach(function (tr) {

                tr.classList.toggle("hidden");

                if (tr.classList.contains("hidden")) {
                    collapseAllNested(tr.dataset.group);
                }
            });
        });
    });

    function collapseAllNested(parentId) {
        let nested = document.querySelectorAll(".child-of-" + parentId);

        nested.forEach(function (tr) {

            tr.classList.add("hidden");

            let arrow = tr.querySelector(".arrow");
            if (arrow) arrow.classList.remove("rotate");

            if (tr.dataset.group) {
                collapseAllNested(tr.dataset.group);
            }
        });
    }
});
</script>
@endsection