@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
                    <nav>
                        <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                            <li class="breadcrumb-item">Dashboard</li>
                            <img src="{{ URL::asset('public/assets/imgs/right-icon.svg') }}" class="px-1" alt="">
                            <li class="breadcrumb-item">
                                <a href="{{ url()->previous() }}">Balance Sheet</a>
                            </li>
                            <img src="{{ URL::asset('public/assets/imgs/right-icon.svg') }}" class="px-1" alt="">
                            <li class="breadcrumb-item fw-bold font-heading" aria-current="page">
                                {{ $mappingName }}
                            </li>
                        </ol>
                    </nav>
                    <div class="text-muted font-12">
                        Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
                        to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                    </div>
                </div>

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="master-table-title m-0 py-2">{{ $mappingName }} — Drill Down</h5>
                </div>

                <div class="mt-3">
                    @foreach($sections as $section)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold font-14 d-flex justify-content-between">
                                <span>
                                    {{ ucfirst($section['record_type']) }}:
                                    {{ $section['label'] }}
                                </span>
                                <span>Balance: ₹{{ formatIndianNumber(abs($section['balance']), 2) }}</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-hover mb-0 font-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50%">Account Name</th>
                                            <th width="17%" class="text-end">Debit (₹)</th>
                                            <th width="17%" class="text-end">Credit (₹)</th>
                                            <th width="16%" class="text-end">Balance (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($section['accounts'] as $acc)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('accountledger-filter') }}/?party={{ $acc['id'] }}&from_date={{ $fromDate }}&to_date={{ $toDate }}"
                                                       class="text-primary text-decoration-none">
                                                        {{ $acc['account_name'] }}
                                                    </a>
                                                </td>
                                                <td class="text-end">{{ formatIndianNumber($acc['debit'], 2) }}</td>
                                                <td class="text-end">{{ formatIndianNumber($acc['credit'], 2) }}</td>
                                                <td class="text-end fw-500">
                                                    @if($acc['balance'] < 0)
                                                        <span class="text-danger">
                                                            ({{ formatIndianNumber(abs($acc['balance']), 2) }})
                                                        </span>
                                                    @else
                                                        {{ formatIndianNumber($acc['balance'], 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    No accounts mapped.
                                                </td>
                                            </tr>
                                        @endforelse

                                        @if($section['stock_adj'] > 0)
                                            <tr class="table-warning">
                                                <td><em>Stock in Hand (Closing)</em></td>
                                                <td class="text-end">{{ formatIndianNumber($section['stock_adj'], 2) }}</td>
                                                <td class="text-end">—</td>
                                                <td class="text-end fw-bold">{{ formatIndianNumber($section['stock_adj'], 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot class="fw-bold">
                                        <tr class="table-secondary">
                                            <td>Total</td>
                                            <td class="text-end">{{ formatIndianNumber($section['total_debit'], 2) }}</td>
                                            <td class="text-end">{{ formatIndianNumber($section['total_credit'], 2) }}</td>
                                            <td class="text-end">
                                                @if($section['balance'] < 0)
                                                    <span class="text-danger">
                                                        ({{ formatIndianNumber(abs($section['balance']), 2) }})
                                                    </span>
                                                @else
                                                    {{ formatIndianNumber($section['balance'], 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    {{-- Grand Total --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body py-3 px-4 d-flex justify-content-between fw-bold font-15 bg-light">
                            <span>GRAND TOTAL — {{ $mappingName }}</span>
                            <span>
                                ₹
                                @if($grandTotal < 0)
                                    <span class="text-danger">({{ formatIndianNumber(abs($grandTotal), 2) }})</span>
                                @else
                                    {{ formatIndianNumber($grandTotal, 2) }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                            ← Back to Balance Sheet
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection