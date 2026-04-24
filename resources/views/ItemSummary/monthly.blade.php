@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-3">
                    <h5 class="transaction-table-title m-0">
                        Monthly Summary - {{ $item->name }}
                    </h5>
                </div>

                <form method="GET" class="row g-3 align-items-end mt-3">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date"
                               name="from_date"
                               value="{{ $from_date }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date"
                               name="to_date"
                               value="{{ $to_date }}"
                               class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Apply
                        </button>
                    </div>

                </form>

                <div class="table-responsive mt-4 mb-4">
                    <table class="table table-bordered table-hover table-sm align-middle bg-white shadow-sm">

                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Opening</th>
                                <th class="text-end text-success">Debit</th>
                                <th class="text-end text-danger">Credit</th>
                                <th class="text-end">Closing</th>
                            </tr>
                        </thead>

                        @php
                            $grandOpening = 0;
                            $grandDebit   = 0;
                            $grandCredit  = 0;
                            $grandClosing = 0;
                        @endphp

                        <tbody>
                            @forelse($monthly as $row)

                                @php
                                    $grandOpening += $row->opening;
                                    $grandDebit   += $row->debit;
                                    $grandCredit  += $row->credit;
                                    $grandClosing += $row->closing;

                                    $openingType = $row->opening < 0 ? 'Cr' : 'Dr';
                                    $closingType = $row->closing < 0 ? 'Cr' : 'Dr';

                                    $start = \Carbon\Carbon::parse($row->month_key.'-01')->startOfMonth()->format('Y-m-d');
                                    $end   = \Carbon\Carbon::parse($row->month_key.'-01')->endOfMonth()->format('Y-m-d');
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ url('/item-ledger-average-by-godown') }}?items_id={{ $item->id }}&from_date={{ $start }}&to_date={{ $end }}"
                                           class="text-decoration-none text-primary">
                                            {{ $row->month_name }}
                                        </a>
                                    </td>

                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($row->opening)) }} {{ $openingType }}
                                    </td>

                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($row->debit) }}
                                    </td>

                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($row->credit) }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ formatIndianNumber(abs($row->closing)) }} {{ $closingType }}
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No transactions found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if($monthly->count() > 0)

                            @php
                                $grandOpeningType = $grandOpening < 0 ? 'Cr' : 'Dr';
                                $grandClosingType = $grandClosing < 0 ? 'Cr' : 'Dr';
                            @endphp

                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end">TOTAL</td>

                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($grandOpening)) }} {{ $grandOpeningType }}
                                    </td>

                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($grandDebit) }}
                                    </td>

                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($grandCredit) }}
                                    </td>

                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($grandClosing)) }} {{ $grandClosingType }}
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

@endsection