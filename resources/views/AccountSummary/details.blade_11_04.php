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

                {{-- Page Header --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-3">

                    <h5 class="transaction-table-title m-0">
                        Account Summary :
                        <span class="text-warning">
                            {{ $group->name ?? ($heading->name ?? '') }}
                        </span>
                    </h5>
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('account.summary.export.details.csv', [
                            'type'      => request('type'),
                            'id'        => request('id'),
                            'from_date' => $from,
                            'to_date'   => $to
                        ]) }}" class="btn btn-outline-success px-4 py-2">
                            Export CSV
                        </a>
                        <a href="{{ route('account.summary.export.details.pdf', [
                            'type'      => request('type'),
                            'id'        => request('id'),
                            'from_date' => $from,
                            'to_date'   => $to
                        ]) }}" class="btn btn-outline-danger px-4 py-2">
                            Export PDF
                        </a>            
                    </div>
                </div>

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('account.summary') }}" class="row g-3 mt-3 mb-4">
                    @if(isset($group))
                        <input type="hidden" name="type" value="group">
                        <input type="hidden" name="id" value="{{ $group->id }}">
                    @elseif(isset($heading))
                        <input type="hidden" name="type" value="head">
                        <input type="hidden" name="id" value="{{ $heading->id }}">
                    @endif



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

                                <tr class="fw-semibold">
                                    <td>
                                        <a href="{{ route('account.summary', [
                                            'type'      => 'group',
                                            'id'        => $g->id,
                                            'from_date' => $from,
                                            'to_date'   => $to
                                        ]) }}"
                                        class="text-decoration-none text-primary">
                                             {{ $g->name }}
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary">GROUP</span>
                                    </td>

                                    {{-- Opening --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber($g->opening ?? 0, 2) }}
                                        <small class="fw-semibold">
                                            {{ $g->opening_type ?? 'Dr' }}
                                        </small>
                                    </td>

                                    {{-- Period Debit --}}
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($g->debit ?? 0, 2) }}
                                    </td>

                                    {{-- Period Credit --}}
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($g->credit ?? 0, 2) }}
                                    </td>

                                    {{-- Closing --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber($g->closing ?? 0, 2) }}
                                        <small class="fw-semibold">
                                            {{ $g->closing_type ?? 'Dr' }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- ACCOUNTS HEADER --}}
                            @if(isset($accounts) && $accounts->isNotEmpty())
                                <tr class="table-light fw-semibold">
                                    <td colspan="6">Accounts</td>
                                </tr>
                            @endif

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

                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('account.month.summary', [
                                            'account_id' => $acc->id,
                                            'from_date'  => $from,
                                            'to_date'    => $to
                                        ]) }}"
                                           class="text-decoration-none">
                                             {{ $acc->account_name }}
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-info text-dark">ACCOUNT</span>
                                    </td>

                                    {{-- Opening --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber($acc->opening ?? 0, 2) }}
                                        <small class="fw-semibold">
                                            {{ $acc->opening_type ?? 'Dr' }}
                                        </small>
                                    </td>

                                    {{-- Period Debit --}}
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($acc->debit ?? 0, 2) }}
                                    </td>

                                    {{-- Period Credit --}}
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($acc->credit ?? 0, 2) }}
                                    </td>

                                    {{-- Closing --}}
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

@endsection
