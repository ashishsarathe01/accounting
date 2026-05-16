@extends('layouts.app')

@section('content')
@include('layouts.header')
<style>

.filter-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
}

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

.modern-select {
    height: 42px;
    font-size: 15px;
    border-radius: 6px;
}
td, th {
    font-size: 14px !important;
    vertical-align: middle !important;
}

.table {
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

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

.table-striped tbody tr:nth-child(even) {
    background: #f9fbff;
}

.table tbody tr:hover {
    background: #eef4ff !important;
    transition: 0.2s ease;
}
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
    transition: transform 0.2s ease;
    color: #5f6df5;
}

.arrow.rotate {
    transform: rotate(90deg);
}
</style>
<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="table-title-bottom-line position-relative d-flex justify-content-between
            align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

    <h5 class="transaction-table-title m-0 py-2">
        Receipt Register
    </h5>

</div>

<div class="filter-card mt-4">

<form method="GET" action="{{ route('receipt_register') }}">

    <div class="row">

        <div class="col-md-2">

            <label><strong>From Date</strong></label>

            <input type="date"
                   name="from_date"
                   class="form-control"
                   value="{{ request('from_date') }}">

        </div>

        <div class="col-md-2">

            <label><strong>To Date</strong></label>

            <input type="date"
                   name="to_date"
                   class="form-control"
                   value="{{ request('to_date') }}">

        </div>

        <div class="col-md-4">

            <label class="report-label">
                Show Report
            </label>

            <div class="report-options">

                <label class="report-radio">

                    <input type="radio"
                           name="show_type"
                           value="all"
                           {{ request('show_type','all') == 'all' ? 'checked' : '' }}>

                    <span>All Parties</span>

                </label>

                <label class="report-radio">

                    <input type="radio"
                           name="show_type"
                           value="party"
                           {{ request('show_type') == 'party' ? 'checked' : '' }}>

                    <span>Party-wise</span>

                </label>

                <label class="report-radio">

                    <input type="radio"
                           name="show_type"
                           value="allgroup"
                           {{ request('show_type') == 'allgroup' ? 'checked' : '' }}>

                    <span>All Group</span>

                </label>

                <label class="report-radio">

                    <input type="radio"
                           name="show_type"
                           value="group"
                           {{ request('show_type') == 'group' ? 'checked' : '' }}>

                    <span>Group-wise</span>

                </label>

            </div>

        </div>
        <div class="col-md-4"
            id="partyDiv"
            style="display: {{ request('show_type') == 'party' ? 'block' : 'none' }};">

            <label class="report-label">
                Select Party
            </label>

            <select name="party_id"
                    class="form-control modern-select select2-single">
                <option value="">
                    Select Party
                </option>

                @foreach($allParties as $party)

                    <option value="{{ $party->id }}"
                        {{ request('party_id') == $party->id ? 'selected' : '' }}>
                        {{ $party->account_name }}

                    </option>

                @endforeach

            </select>

        </div>

        <div class="col-md-4"
            id="groupDiv"
            style="display: {{ request('show_type') == 'group' ? 'block' : 'none' }};">

            <label class="report-label">
                Select Group
            </label>

            <select name="group_id"
                    class="form-control modern-select select2-single">

                <option value="">
                    Select Group
                </option>

                @foreach($allGroups as $group)

                    <option value="{{ $group->id }}"
                        {{ request('group_id') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}

                    </option>

                @endforeach

            </select>

        </div>
    </div>

    <div class="mt-3">

        <button class="btn btn-primary">
            Filter
        </button>
    </div>

</form>

</div>
<div class="table-responsive mt-4">

    <table class="table table-bordered table-striped">

        <thead>

            <tr>

                <th width="10%">
                    Sr. No.
                </th>

                <th>
                    Party / Group
                </th>

                <th style="text-align:right;">
                    Amount
                </th>

            </tr>

        </thead>

        <tbody>

            @php
                $i = 1;
                $totalAmount = 0;
            @endphp

            {{-- ================= ALL GROUP ================= --}}
            @if(request('show_type') == 'allgroup')

                @php
                    foreach($groupWiseData as $grp)
                    {
                        $totalAmount += $grp['total_amount'];
                    }
                @endphp

                @foreach($groupWiseData as $grp)

                    @include(
                        'ReceiptRegister.group-row',
                        [
                            'grp' => $grp,
                            'level' => 0,
                            'parentGroup' => 0
                        ]
                    )

                @endforeach

            {{-- ================= OTHER REPORTS ================= --}}
            @else

                @forelse($data as $row)

                    @php
                        $totalAmount += $row->amount;
                    @endphp

                    <tr>

                        <td>
                            {{ $i++ }}
                        </td>

                        <td>
                            {{ $row->account_name }}
                        </td>

                        <td style="text-align:right;">

                            <a href="javascript:void(0)"
                                class="receipt-register-details"
                                data-account="{{ $row->id }}"
                                data-from="{{ request('from_date') }}"
                                data-to="{{ request('to_date') }}"
                                style="text-decoration:none;font-weight:600;">
                                    {{ formatIndianNumber($row->amount,2) }}
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3" style="text-align:center;">

                            No Data Found

                        </td>

                    </tr>

                @endforelse

            @endif

        </tbody>

        <tfoot>

            <tr>

                <th colspan="2" style="text-align:center;">

                    TOTAL

                </th>

                <th style="text-align:right;">

                    {{ formatIndianNumber($totalAmount,2) }}

                </th>

            </tr>

        </tfoot>

    </table>

</div>
</div>
</div>
</section>
</div>
<div class="modal fade"
     id="receiptRegisterModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Receipt Register Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>Date</th>

                                <th>Voucher No</th>

                                <th>Mode</th>

                                <th>Party</th>

                                <th style="text-align:right;">
                                    Amount
                                </th>

                            </tr>

                        </thead>

                        <tbody id="receiptRegisterModalBody">

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
@include('layouts.footer')
<script>

$(document).ready(function () {

    $('.select2-single').select2();

    $("input[name='show_type']").on("change", function(){

        $("#partyDiv").toggle(this.value === "party");

        $("#groupDiv").toggle(this.value === "group");

        $('.select2-single').select2();

    });

});
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".group-row").forEach(function (row) {

        row.addEventListener("click", function () {

            let groupId = this.dataset.group;

            // Rotate arrow
            let arrow = this.querySelector(".arrow");

            arrow.classList.toggle("rotate");

            // Find direct children
            let children = document.querySelectorAll(".child-of-" + groupId);

            children.forEach(function (tr) {

                tr.classList.toggle("hidden");

                // If collapsing, collapse nested also
                if (tr.classList.contains("hidden"))
                {
                    collapseNested(tr.dataset.group);
                }

            });

        });

    });


    function collapseNested(parentId)
    {
        let nested = document.querySelectorAll(".child-of-" + parentId);

        nested.forEach(function (tr) {

            tr.classList.add("hidden");

            let arrow = tr.querySelector(".arrow");

            if (arrow)
            {
                arrow.classList.remove("rotate");
            }

            if (tr.dataset.group)
            {
                collapseNested(tr.dataset.group);
            }

        });
    }

});
$(document).on(
    'click',
    '.receipt-register-details',
    function () {

        let account_id = $(this).data('account');

        let group_id = $(this).data('group');

        let from_date = $(this).data('from');

        let to_date = $(this).data('to');

        $.ajax({

            url: "{{ route('receipt.register.modal.details') }}",

            type: "GET",

            data: {
                account_id : account_id,
                group_id   : group_id,
                from_date  : from_date,
                to_date    : to_date
            },

            success: function (response) {

                let html = '';

                if(response.length > 0)
                {

                    response.forEach(function(row){

                        html += `
                            <tr>

                                <td>
                                    ${row.date}
                                </td>

                                <td>

                                    <a href="{{ url('receipt') }}/${row.receipt_id}/edit"
                                       target="_blank"
                                       style="text-decoration:none;font-weight:600;">

                                        ${row.voucher_no ?? ''}

                                    </a>

                                </td>

                                <td>
                                    ${row.mode ?? ''}
                                </td>

                                <td>
                                    ${row.account_name ?? ''}
                                </td>

                                <td style="text-align:right;">

                                    ${parseFloat(row.amount).toFixed(2)}

                                </td>

                            </tr>
                        `;
                    });

                }
                else
                {

                    html += `
                        <tr>

                            <td colspan="5" class="text-center">

                                No Data Found

                            </td>

                        </tr>
                    `;
                }

                $('#receiptRegisterModalBody').html(html);

                $('#receiptRegisterModal').modal('show');

            }

        });

    }
);
</script>
@endsection