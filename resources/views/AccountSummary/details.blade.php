@extends('layouts.app')

@section('content')

@include('layouts.header')
<style>
.table a {
    color: black !important;
    text-decoration: none !important;
}

.table .group-toggle,
.table .group-name {
    font-weight: 600 !important;
    color: black !important;
}

.table tr[data-level] td a:not(.group-toggle) {
    font-weight: 400 !important;
    color: black !important;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            {{-- Left Navigation --}}
            @include('layouts.leftnav')

            {{-- Main Content --}}
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Page Header --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-3 px-4 mt-3">

                    <h5 class="transaction-table-title m-0">
                        Account Summary :
                        <span class="text-warning">
                            {{ $group->name ?? ($heading->name ?? '') }}
                        </span>
                    </h5>

                    <div class="d-flex gap-2">
                        <button id="exportPdf" class="btn btn-outline-danger px-3">
                            Export PDF
                        </button>
                        <button id="exportExcel" class="btn btn-outline-success px-3">
                            Export Excel
                        </button>
                    </div>
                </div>


                {{-- Date Filter --}}
                <form method="GET" action="{{ route('account.summary') }}" 
                    class="row g-3 align-items-end mt-3 mb-4 bg-white p-4 rounded shadow-sm border">

                    @if(isset($group))
                        <input type="hidden" name="type" value="group">
                        <input type="hidden" name="id" value="{{ $group->id }}">
                    @elseif(isset($heading))
                        <input type="hidden" name="type" value="head">
                        <input type="hidden" name="id" value="{{ $heading->id }}">
                    @endif

                    {{-- From Date --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" value="{{ $from }}" class="form-control">
                    </div>

                    {{-- To Date --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" value="{{ $to }}" class="form-control">
                    </div>

                    {{-- Group Filter --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Select Group</label>

                        <select name="group_id" class="form-control select2">
                            <option value="">All Groups</option>

                            @foreach($allGroups ?? [] as $grp)
                                <option value="{{ $grp->id }}" 
                                    {{ request('group_id') == $grp->id ? 'selected' : '' }}>
                                    {{ $grp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-4 d-flex align-items-center gap-3">

                        {{-- Apply Button --}}
                        <button class="btn btn-primary px-4">
                            Apply Filter
                        </button>

                        {{-- Detailed View --}}
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="expandAll">
                            <label class="form-check-label fw-semibold" for="expandAll">
                                Detailed View
                            </label>
                        </div>

                    </div>
                </form>

                {{-- Summary Table --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover table-sm align-middle bg-white shadow-sm">

                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Account / Group</th>
                                <th style="width:120px" class="text-center">Type</th>
                                <th style="width:150px" class="text-end">Opening</th>
                                <th style="width:150px" class="text-end">Debit</th>
                                <th style="width:150px" class="text-end">Credit</th>
                                <th style="width:150px" class="text-end">Closing</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $openingDr = 0;
                                $openingCr = 0;

                                $totalDebit  = 0;
                                $totalCredit = 0;

                                $closingDr = 0;
                                $closingCr = 0;
                            @endphp

                            {{-- GROUPS --}}
                            @foreach($groups as $g)
                                @php
                                    if($g->opening==0 && $g->debit==0 && $g->credit==0){
                                        continue;
                                    }
                                    // Opening
                                    if(($g->opening_type ?? 'Dr') == 'Dr'){
                                        $openingDr += $g->opening ?? 0;
                                    } else {
                                        $openingCr += $g->opening ?? 0;
                                    }

                                    // Debit / Credit
                                    $totalDebit  += $g->debit ?? 0;
                                    $totalCredit += $g->credit ?? 0;

                                    // Closing
                                    if(($g->closing_type ?? 'Dr') == 'Dr'){
                                        $closingDr += $g->closing ?? 0;
                                    } else {
                                        $closingCr += $g->closing ?? 0;
                                    }
                                @endphp

                                <tr class="fw-semibold group-row" data-level="{{ $level ?? 1 }}" style="font-weight: bold;">
                                    <td style="padding-left: {{ ($level ?? 1) * 25 }}px;">
                                        <a href="javascript:void(0)"
                                            class="text-decoration-none group-toggle d-inline-flex align-items-center"
                                            style="color: black; font-weight: 600;"
                                             data-id="{{ $g->id }}">

                                            <span class="toggle-icon me-2" style="font-size:12px;">▶</span>

                                            <span class="group-name" style="color: black; font-weight: 600;font-size: 15px;">
                                                {{ $g->name }}
                                            </span>
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary">GROUP</span>
                                    </td>

                                    <td class="text-end">
                                        {{ formatIndianNumber($g->opening ?? 0, 2) }}
                                        <small class="fw-semibold">{{ $g->opening_type ?? 'Dr' }}</small>
                                    </td>

                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($g->debit ?? 0, 2) }}
                                    </td>

                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($g->credit ?? 0, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ formatIndianNumber($g->closing ?? 0, 2) }}
                                        <small class="fw-semibold">{{ $g->closing_type ?? 'Dr' }}</small>
                                    </td>
                                </tr>

                                <tr id="group-children-{{ $g->id }}" class="group-children" style="display:none;"></tr>

                            @endforeach

                            

                            {{-- ACCOUNTS --}}
                            @forelse($accounts ?? [] as $acc)
                                @php
                                    if($acc->opening==0 && $acc->debit==0 && $acc->credit==0){
                                        continue;
                                    }
                                   // Opening
                                    if(($acc->opening_type ?? 'Dr') == 'Dr'){
                                        $openingDr += $acc->opening ?? 0;
                                    } else {
                                        $openingCr += $acc->opening ?? 0;
                                    }

                                    // Debit / Credit
                                    $totalDebit  += $acc->debit ?? 0;
                                    $totalCredit += $acc->credit ?? 0;

                                    // Closing
                                    if(($acc->closing_type ?? 'Dr') == 'Dr'){
                                        $closingDr += $acc->closing ?? 0;
                                    } else {
                                        $closingCr += $acc->closing ?? 0;
                                    }
                                @endphp

                               <tr data-level="{{ $level ?? 1 }}">
                                    <td style="padding-left: {{ (($level ?? 1) * 25) + 25 }}px;">
                                            <a href="{{ route('account.month.summary', [
                                                    'account_id' => $acc->id,
                                                    'from_date'  => $from,
                                                    'to_date'    => $to
                                                ]) }}"
                                                class="text-decoration-none"
                                                style="color: black; font-weight: normal;">
                                                {{ $acc->account_name }}
                                            </a>
                                    </td>

                                    <td class="text-center">
                                            <span class="badge bg-info text-dark">ACCOUNT</span>
                                    </td>

                                    <td class="text-end">
                                            {{ formatIndianNumber($acc->opening ?? 0, 2) }}
                                            <small class="fw-semibold">
                                                {{ $acc->opening_type ?? 'Dr' }}
                                            </small>
                                    </td>

                                    <td class="text-end text-danger">
                                            {{ formatIndianNumber($acc->debit ?? 0, 2) }}
                                    </td>

                                    <td class="text-end text-success">
                                            {{ formatIndianNumber($acc->credit ?? 0, 2) }}
                                    </td>

                                    <td class="text-end">
                                            {{ formatIndianNumber($acc->closing ?? 0, 2) }}
                                            <small class="fw-semibold">
                                                {{ $acc->closing_type ?? 'Dr' }}
                                            </small>
                                    </td>
                                </tr>
                                @empty
                                @if($groups->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No sub-groups or accounts found
                                        </td>
                                    </tr>
                                @endif
                            @endforelse
                        </tbody>

                        {{-- TOTAL --}}
                        @if($groups->isNotEmpty() || $accounts->isNotEmpty())
                        @php
                            // Opening Net
                            $netOpening = $openingDr - $openingCr;
                            $netOpeningType = $netOpening >= 0 ? 'Dr' : 'Cr';

                            // Closing Net
                            $netClosing = $closingDr - $closingCr;
                            $netClosingType = $netClosing >= 0 ? 'Dr' : 'Cr';
                        @endphp
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end fw-bold">TOTAL</td>
                                    <td></td>

                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($netOpening), 2) }}
                                        <small>{{ $netOpeningType }}</small>
                                    </td>

                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($totalDebit, 2) }}
                                    </td>

                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($totalCredit, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($netClosing), 2) }}
                                        <small>{{ $netClosingType }}</small>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<script>
    let expandedGroups = new Set();
    $(document).on("click", ".group-toggle", function (e) {

        e.preventDefault();

        let $this = $(this);
        let groupId = $this.data("id");
        let parentRow = $this.closest("tr");
        let markerRow = $("#group-children-" + groupId);
        let icon = $this.find(".toggle-icon");

        let currentLevel = parseInt(parentRow.attr("data-level")) || 1;
        let isOpen = icon.text().trim() === "▼";

        if ($this.data("busy")) return;
        $this.data("busy", true);

        if (isOpen) {
            expandedGroups.delete(groupId);

            let next = markerRow.next();

            while (next.length) {

                let nextLevelAttr = next.attr("data-level");

                if (nextLevelAttr !== undefined && parseInt(nextLevelAttr) <= currentLevel) {
                    break;
                }

                next.hide();
                next.find(".toggle-icon").text("▶");

                let childToggle = next.find(".group-toggle");
                if (childToggle.length) {
                    let childId = childToggle.data("id");

                    $("#group-children-" + childId).removeData("loaded");
                    childToggle.removeData("busy");
                }

                next = next.next();
            }

            markerRow.removeData("loaded");

            icon.text("▶");
            $this.removeData("busy");
            return;
        }

        if (markerRow.data("loaded")) {

            let next = markerRow.next();

            while (next.length) {

                let nextLevelAttr = next.attr("data-level");

                if (nextLevelAttr !== undefined && parseInt(nextLevelAttr) <= currentLevel) {
                    break;
                }

                if (parseInt(nextLevelAttr) === currentLevel + 1) {
                    next.show();
                } else {
                    next.hide();
                }

                next = next.next();
            }

            icon.text("▼");
            $this.removeData("busy");
            return;
        }

        let next = markerRow.next();

        while (next.length) {

            let nextLevelAttr = next.attr("data-level");

            if (nextLevelAttr !== undefined && parseInt(nextLevelAttr) <= currentLevel) {
                break;
            }

            let temp = next.next();
            next.remove(); 
            next = temp;
        }

        markerRow.data("loading", true);

        $.ajax({
            url: "{{ route('account.summary') }}",
            type: "GET",
            data: {
                type: 'group',
                id: groupId,
                from_date: "{{ $from }}",
                to_date: "{{ $to }}",
                level: currentLevel + 1
            },
            success: function (res) {
                expandedGroups.add(groupId);

                markerRow.after(res);
                markerRow.data("loaded", true);
                icon.text("▼");
            },
            complete: function () {
                markerRow.removeData("loading");
                $this.removeData("busy"); 
            }
        });

    });


    $("#expandAll").on("change", function () {

        if ($(this).is(":checked")) {
            expandAllGroups();
        } else {
            collapseAllGroups();
        }

    });

    function expandAllGroups() {

        function expandLoop() {

            let closed = $(".group-toggle").filter(function () {
                return $(this).find(".toggle-icon").text().trim() === "▶";
            });

            if (closed.length === 0) {
                return; // done
            }

            let toggle = closed.first();

            let observer = new MutationObserver(function () {
                observer.disconnect();

                // after DOM updates → continue
                setTimeout(expandLoop, 100);
            });

            observer.observe(document.querySelector("tbody"), {
                childList: true,
                subtree: true
            });

            toggle.trigger("click");
        }

        expandLoop();
    }

    function collapseAllGroups() {

        $(".group-toggle").each(function () {

            let icon = $(this).find(".toggle-icon");

            if (icon.text().trim() === "▼") {
                $(this).trigger("click");
            }

        });

    }
    $(document).on("dblclick", ".group-toggle", function (e) {
        e.preventDefault();
    });
    function getExpandedGroups() {
        return Array.from(expandedGroups);
    }

    $("#exportCsv").click(function () {

        let groups = getExpandedGroups();

        let url = "{{ route('account.summary.export.details.csv') }}";

        url += "?type={{ request('type') }}";
        url += "&id={{ request('id') }}";
        url += "&from_date={{ $from }}";
        url += "&to_date={{ $to }}";
        url += "&expanded=" + groups.join(",");

        window.location.href = url;
    });
    $("#exportExcel").click(function () {

        let data = getVisibleTableData();
        let title = $(".transaction-table-title span").text().trim();

        $.ajax({
            url: "{{ route('account.summary.export.details.excel') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                rows: JSON.stringify(data),

                from_date: "{{ $from }}",
                to_date: "{{ $to }}",
                title: title
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {

                let blob = new Blob([response]);
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "account_summary.xls";
                link.click();
            }
        });

    });
    $("#exportPdf").click(function () {

        let data = getVisibleTableData();
        let title = $(".transaction-table-title span").text().trim();

        $.ajax({
            url: "{{ route('account.summary.export.details.pdf') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                rows: JSON.stringify(data),
                from_date: "{{ $from }}",
                to_date: "{{ $to }}",
                title: title
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {

                let blob = new Blob([response], { type: 'application/pdf' });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "account_summary.pdf";
                link.click();
            }
        });

    });
    function getVisibleTableData() {

        let rows = [];

        $("tbody tr:visible").each(function () {

            let cols = $(this).find("td");

            if (cols.length === 0) return;

            rows.push({
                name: $(cols[0]).text().trim(),
                type: $(cols[1]).text().trim(),
                opening: $(cols[2]).text().trim(),
                debit: $(cols[3]).text().trim(),
                credit: $(cols[4]).text().trim(),
                closing: $(cols[5]).text().trim(),
                level: $(this).data("level") || 1
            });
        });

        return rows;
    }
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            allowClear: false 
        });
    });
</script>
@endsection
