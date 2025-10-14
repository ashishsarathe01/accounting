@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="fw-bold mb-3">Day Book</h2>

    <!-- Sales -->
    <h4>Sales</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Total Qty</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales->groupBy('item_name') as $item => $rows)
               @php
                    $totalQty = $rows->sum(fn($r) => (float)$r->qty);
                    $amountTotal = $rows->sum(fn($r) => (float)$r->amount);
                @endphp

                <tr class="expandable" data-item="{{ $item }}">
                    <td>{{ $item }}</td>
                    <td>{{ $totalQty }}</td>
                    <td>{{ number_format((float)$amountTotal,2) }}</td>
                </tr>
                <tr class="details-row d-none" data-item="{{ $item }}">
                    <td colspan="3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $r)
                                    <tr>
                                        <td>{{ $r->date }}</td>
                                        <td>{{ $r->voucher_no }}</td>
                                        <td>{{ $r->qty }}</td>
                                        <td>{{ number_format((float)$r->amount,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Purchases -->
    <h4>Purchases</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Total Qty</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases->groupBy('item_name') as $item => $rows)
                @php
                    $totalQty = $rows->sum('qty');
                    $amountTotal = $rows->sum('amount');
                @endphp
                <tr class="expandable" data-item="{{ $item }}">
                    <td>{{ $item }}</td>
                    <td>{{ $totalQty }}</td>
                    <td>{{ number_format((float)$amountTotal,2) }}</td>
                </tr>
                <tr class="details-row d-none" data-item="{{ $item }}">
                    <td colspan="3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $r)
                                    <tr>
                                        <td>{{ $r->date }}</td>
                                        <td>{{ $r->voucher_no }}</td>
                                        <td>{{ $r->qty }}</td>
                                        <td>{{ number_format((float)$r->amount,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Journal -->
    <h4>Journal</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journals as $j)
                <tr>
                    <td>{{ $j->date }}</td>
                    <td>{{ $j->account_name }}</td>
                    <td>{{ strtoupper($j->type) }}</td>
                    <td>{{ number_format((float)$j->debit,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Receipts -->
    <h4>Receipts</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Account</th>
                <th>Type</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipts as $r)
                <tr>
                    <td>{{ $r->date }}</td>
                    <td>{{ $r->voucher_no }}</td>
                    <td>{{ $r->account_name }}</td>
                    <td>{{ strtoupper($r->type) }}</td>
                    <td>{{ number_format((float)$r->debit,2) }}</td>
                    <td>{{ number_format((float)$r->credit,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Payments -->
    <h4>Payments</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Account</th>
                <th>Type</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
                <tr>
                    <td>{{ $p->date }}</td>
                    <td>{{ $p->voucher_no }}</td>
                    <td>{{ $p->account_name }}</td>
                    <td>{{ strtoupper($p->type) }}</td>
                    <td>{{ number_format((float)$p->debit,2) }}</td>
                    <td>{{ number_format((float)$p->credit,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Contra -->
    <h4>Contra</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Account</th>
                <th>Type</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contra as $c)
                <tr>
                    <td>{{ $c->date }}</td>
                    <td>{{ $c->voucher_no }}</td>
                    <td>{{ $c->account_name }}</td>
                    <td>{{ strtoupper($c->type) }}</td>
                    <td>{{ number_format((float)$c->debit,2) }}</td>
                    <td>{{ number_format((float)$c->credit,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Stock Transfer -->
    <h4>Stock Transfers</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Amount</th>
                <th>From Series</th>
                <th>To Series</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfers as $s)
                <tr>
                    <td>{{ $s->transfer_date }}</td>
                    <td>{{ $s->voucher_no }}</td>
                    <td>{{ $s->item_name }}</td>
                    <td>{{ $s->qty }}</td>
                    <td>{{ number_format((float)$s->amount,2) }}</td>
                    <td>{{ $s->series_no }}</td>
                    <td>{{ $s->series_no_to }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    // Expand / Collapse item rows
    document.querySelectorAll('.expandable').forEach(row => {
        row.addEventListener('click', function() {
            let item = this.dataset.item;
            let details = document.querySelector('.details-row[data-item="'+item+'"]');
            details.classList.toggle('d-none');
        });
    });
</script>
@endsection
