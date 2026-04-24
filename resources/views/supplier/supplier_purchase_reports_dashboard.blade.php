@extends('layouts.app')

@section('content')
    @include('layouts.header')

    <style>
        @media print {

            /* Hide everything by default */
            body * {
                visibility: hidden;
            }

            /* Show only print area */
            #print-area,
            #print-area * {
                visibility: visible;
            }

            /* Position print area properly */
            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Page setup */
            @page {
                margin: 12mm;
            }

            body {
                font-size: 12px;
                color: #000;
            }

            /* Table styling */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 6px;
            }

            th {
                background: #f2f2f2 !important;
                font-weight: bold;
            }

            /* Headings */
            .print-title {
                text-align: center;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .print-subtitle {
                text-align: center;
                margin-bottom: 10px;
                font-size: 11px;
            }

            /* Avoid row splitting */
            tr {
                page-break-inside: avoid;
            }
        }
.table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 100;
}

    </style>

    <div class="list-of-view-company">
        <section class="list-of-view-company-section container-fluid">
            <div class="row vh-100">

                @include('layouts.leftnav')

                <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                    {{-- ================= HEADER + FILTER BAR ================= --}}
                    <form method="GET" class="mb-3">
                        <div
                            class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                            <h5 class="transaction-table-title m-0 py-2">
                                Report
                            </h5>

                            {{-- RIGHT : FILTERS --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <input
                                    type="date"
                                    name="from_date"
                                    value="{{ $from_date }}"
                                    class="form-control form-control-sm"
                                    style="width:150px"
                                >

                                <input
                                    type="date"
                                    name="to_date"
                                    value="{{ $to_date }}"
                                    class="form-control form-control-sm"
                                    style="width:150px"
                                >

                                <select
                                    name="item_group"
                                    class="form-select form-select-sm"
                                    style="width:180px"
                                >
                                    <option value="all">All</option>
                                    @foreach ($itemGroupOptions as $group)
                                        <option
                                            value="{{ $group }}"
                                            {{ request('item_group') == $group ? 'selected' : '' }}
                                        >
                                            {{ $group }}
                                        </option>
                                    @endforeach
                                </select>

                                <button class="btn btn-info btn-sm px-4">
                                    Submit
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm px-4"
                                    onclick="printReport()"
                                >
                                    Print
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- ================= SINGLE TABLE ================= --}}
                    @if ($grouped->isEmpty())
                        <div class="alert alert-warning mt-3">
                            No records found.
                        </div>
                    @else
                        <div id="print-area">
                            @php
                                $totalGross = 0;
                                $totalTare  = 0;
                                $totalNet   = 0;
                            @endphp

                            {{-- REPORT TITLE --}}
                            <h4 class="print-title d-none d-print-block mb-3">
                                Report
                            </h4>

                            {{-- DATE RANGE --}}
                            <p class="d-none d-print-block mb-2">
                                <strong>From:</strong> {{ $from_date }}
                                &nbsp;&nbsp;
                                <strong>To:</strong> {{ $to_date }}
                            </p>

                            {{-- TABLE --}}
                            <table class="table table-bordered table-sm bg-white shadow-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Account Name</th>
                                        <th>Item Group</th>
                                        <th>Vehicle No</th>
                                        <th>Gross Weight</th>
                                        <th>Tare Weight</th>
                                        <th>Slip No</th>
                                        <th>Net Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($grouped as $date => $typeGroups)
                                        @foreach ($typeGroups as $groupNames)
                                            @foreach ($groupNames as $rows)
                                                @foreach ($rows as $row)
                                                    @php
                                                        $gross = $row->gross_weight ?? 0;
                                                        $tare  = $row->tare_weight ?? 0;
                                                        $net = ($tare == 0) ? 0 : ($gross - $tare);
                                                        $totalGross += $gross;
                                                        $totalTare  += $tare;
                                                        $totalNet   += $net;
                                                    @endphp


                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($row->entry_date)->format('d-m-Y') }}</td>
                                                        <td>{{ $row->account_name }}</td>
                                                        <td>{{ $row->group_name }}</td>
                                                        <td>{{ $row->vehicle_no }}</td>
                                                        <td class="text-end">{{ number_format($gross, 2) }}</td>
                                                        <td class="text-end">{{ number_format($tare, 2) }}</td>
                                                        <td>{{ $row->voucher_no }}</td>
                                                        <td class="text-end">{{ number_format($net, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                    <tr class="fw-bold bg-light">
                                        <td colspan="4" class="text-end">TOTAL</td>
                                        <td class="text-end">{{ number_format($totalGross, 2) }}</td>
                                        <td class="text-end">{{ number_format($totalTare, 2) }}</td>
                                        <td></td>
                                        <td class="text-end">{{ number_format($totalNet, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </section>
    </div>

    @include('layouts.footer')

    <script>
        function printReport() {
            window.print();
        }
    </script>
@endsection
