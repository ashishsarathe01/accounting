@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            {{-- Left Navigation --}}
            @include('layouts.leftnav')

            {{-- Main Content --}}
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Page Header --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-3">
                    <h5 class="transaction-table-title m-0">
                        Account Summary
                    </h5>
                    <div class="d-flex justify-content-end mb-2">

                        <a href="{{ route('account.summary.export.csv', [
                            'from_date' => $from,
                            'to_date'   => $to
                        ]) }}" 
                        class="btn btn-outline-success px-4 py-2">
                            Export CSV
                        </a>

                        <a href="{{ route('account.summary.export.pdf', [
                            'from_date' => $from,
                            'to_date'   => $to
                        ]) }}" 
                        class="btn btn-outline-danger px-4 py-2">
                            Export PDF
                        </a>

                    </div>
                </div>

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('account.summary') }}" class="row g-3 mt-3 mb-4">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" value="{{ $from }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" value="{{ $to }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            Apply Filter
                        </button>
                    </div>

                </form>

                {{-- Account Summary Table --}}
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
                                $totalDebit  = 0;
                                $totalCredit = 0;
                            @endphp

                            @forelse($heads as $head)
                                @php
                                    $totalDebit  += $head->debit ?? 0;
                                    $totalCredit += $head->credit ?? 0;
                                @endphp

                                <tr class="fw-semibold">
                                    <td>
                                        <a href="{{ route('account.summary', [
                                            'type'      => 'head',
                                            'id'        => $head->id,
                                            'from_date' => $from,
                                            'to_date'   => $to
                                        ]) }}"
                                        class="text-decoration-none text-primary">
                                            {{ $head->name }}
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary">HEAD</span>
                                    </td>

                                    {{-- Opening --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber($head->opening ?? 0, 2) }}
                                        <small class="fw-semibold">
                                            {{ $head->opening_type ?? 'Dr' }}
                                        </small>
                                    </td>

                                    {{-- Period Debit --}}
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($head->debit ?? 0, 2) }}
                                    </td>

                                    {{-- Period Credit --}}
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($head->credit ?? 0, 2) }}
                                    </td>

                                    {{-- Closing --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber($head->closing ?? 0, 2) }}
                                        <small class="fw-semibold">
                                            {{ $head->closing_type ?? 'Dr' }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No account headings found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- Grand Total --}}
                        @if($heads->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">
                                    TOTAL
                                </td>

                                <td class="text-end text-danger">
                                    {{ formatIndianNumber($totalDebit, 2) }}
                                </td>

                                <td class="text-end text-success">
                                    {{ formatIndianNumber($totalCredit, 2) }}
                                </td>

                                <td></td>
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
