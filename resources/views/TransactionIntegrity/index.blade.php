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
                        Transaction Integrity Report
                    </h5>
                </div>

                {{-- Filters --}}
                <form method="GET" action="{{ url('transaction-integrity') }}" class="row g-3 mt-3 mb-4">

                    {{-- From Date --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" required>
                    </div>

                    {{-- To Date --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" required>
                    </div>

                    {{-- Transaction Type --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Transaction Type</label>
                        <select name="type" class="form-select" required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="sale" {{ request('type')=='sale'?'selected':'' }}>Sale</option>
                            <option value="purchase" {{ request('type')=='purchase'?'selected':'' }}>Purchase</option>
                            <option value="credit_note" {{ request('type')=='credit_note'?'selected':'' }}>Credit Note</option>
                            <option value="debit_note" {{ request('type')=='debit_note'?'selected':'' }}>Debit Note</option>
                            <option value="payment" {{ request('type')=='payment'?'selected':'' }}>Payment</option>
                            <option value="receipt" {{ request('type')=='receipt'?'selected':'' }}>Receipt</option>
                            <option value="journal" {{ request('type')=='journal'?'selected':'' }}>Journal</option>
                            <option value="contra" {{ request('type')=='contra'?'selected':'' }}>Contra</option>
                            <option value="stock_journal" {{ request('type')=='stock_journal'?'selected':'' }}>Stock Journal</option>
                            <option value="stock_transfer" {{ request('type')=='stock_transfer'?'selected':'' }}>Stock Transfer</option>
                        </select>
                    </div>

                    {{-- Button --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            Apply Filter
                        </button>
                    </div>

                </form>

                {{-- Table --}}
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover table-sm align-middle bg-white shadow-sm">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Module</th>
                                <th>Reference</th>
                                <th>Particulars</th>
                                <th>Reason</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $from = request('from_date');
                                $to   = request('to_date');
                                $type = request('type');
                            @endphp

                            @if($from && $to && $type)

                                @if(isset($data) && count($data) > 0)
                                    @foreach($data as $row)
                                        <tr>
                                            <td>{{ date('d-m-Y', strtotime($row->date)) }}</td>
                                            <td>{{ $row->module ?? '' }}</td>
                                            <td>
                                                @if(!empty($row->edit_url))
                                                    <a href="{{ $row->edit_url }}" target="_blank" class="text-primary fw-semibold">
                                                        {{ $row->reference ?? '' }}
                                                    </a>
                                                @else
                                                    {{ $row->reference ?? '' }}
                                                @endif
                                            </td>
                                            <td>{{ $row->party_name ?? '' }}</td>
                                            <td>{{ $row->reason ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            No mismatch records found
                                        </td>
                                    </tr>
                                @endif

                            @endif

                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

@endsection