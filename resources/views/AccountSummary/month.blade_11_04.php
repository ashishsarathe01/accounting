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
                        <span class="text-warning">{{ $account->account_name }}</span>
                    </h5>
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('account.summary.export.month.csv', [
                            'account_id' => $account->id,
                            'from_date'  => $from,
                            'to_date'    => $to
                        ]) }}" class="btn btn-outline-success px-4 py-2">
                            Export CSV
                        </a>
                        <a href="{{ route('account.summary.export.month.pdf', [
                            'account_id' => $account->id,
                            'from_date'  => $from,
                            'to_date'    => $to
                        ]) }}" class="btn btn-outline-danger px-4 py-2">
                            Export PDF
                        </a>
                    </div>
                </div>

                {{-- Month Summary Table --}}
                <div class="table-responsive mt-4 mb-4">
                    <table class="table table-bordered table-hover table-sm align-middle bg-white shadow-sm">

                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Month</th>
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

                                // Starting Opening Balance
                                $runningBalance = (float) ($account->opening ?? 0);

                                if(isset($account->opening_type) && $account->opening_type == 'Cr'){
                                    $runningBalance = -$runningBalance;
                                }
                            @endphp

                            @forelse($months as $m)
                                @php
                                    $totalDebit  += $m->debit;
                                    $totalCredit += $m->credit;

                                    $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $m->month)
                                                    ->startOfMonth()
                                                    ->format('Y-m-d');

                                    $monthEnd   = \Carbon\Carbon::createFromFormat('Y-m', $m->month)
                                                    ->endOfMonth()
                                                    ->format('Y-m-d');
                                @endphp

                                @php
                                    $monthDebit  = (float) $m->debit;
                                    $monthCredit = (float) $m->credit;

                                    $openingBalance = $runningBalance;

                                    $closingBalance = $openingBalance + $monthDebit - $monthCredit;

                                    $runningBalance = $closingBalance;
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ url('accountledger-filter') }}?party={{ $account->id }}&from_date={{ $monthStart }}&to_date={{ $monthEnd }}">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M Y') }}
                                        </a>
                                    </td>

                                    {{-- Opening --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($openingBalance), 2) }}
                                        {{ $openingBalance < 0 ? 'Cr' : 'Dr' }}
                                    </td>

                                    {{-- Debit --}}
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($monthDebit, 2) }}
                                    </td>

                                    {{-- Credit --}}
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($monthCredit, 2) }}
                                    </td>

                                    {{-- Closing --}}
                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($closingBalance), 2) }}
                                        {{ $closingBalance < 0 ? 'Cr' : 'Dr' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        No transactions found for this account
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- TOTAL --}}
                        @if($months->isNotEmpty())
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="text-end">TOTAL</td>
                                <td></td>
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
