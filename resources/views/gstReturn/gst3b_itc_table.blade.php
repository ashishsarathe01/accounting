@extends('layouts.app')

@section('content')
@include('layouts.header')

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 14px;
        min-width: 1400px;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 8px 12px;
        text-align: left;
        font-size: 16px;
        white-space: nowrap;
    }

    th {
        color: white;
        background: #0dcaf0;
    }

    .text-right {
        text-align: right;
    }
    .table-responsive {
        max-height: 700px;
        overflow: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1400px;
    }

    thead th {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #0dcaf0 !important;
        color: #fff;
    }

    tfoot td {
        position: sticky;
        bottom: 0;
        z-index: 99;
        background: #f8f9fa !important;
        font-weight: bold;
    }

</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="min-vh-100 row">

            @include('layouts.leftnav')

            <div class="col-md-10 col-sm-12 px-0">

                <div class="container-fluid">

                    <div class="w-100 px-4 bg-light py-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2>GSTR-3B - Eligible ITC Details</h2>

                        </div>

                        <div class="table-responsive">
                        <table class="table table-bordered">

                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>GSTIN</th>
                                    <th>Party Name</th>
                                    <th>Invoice No.</th>
                                    <th>Invoice Date</th>
                                    <th>Invoice Type</th>
                                    <th>Invoice Value</th>
                                    <th>Taxable Value</th>
                                    <th>IGST</th>
                                    <th>CGST</th>
                                    <th>SGST</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $invoiceTotal = 0;
                                    $taxableTotal = 0;
                                    $igstTotal = 0;
                                    $cgstTotal = 0;
                                    $sgstTotal = 0;
                                    $sr = 1;
                                @endphp
                                @if(count($data))
                                @foreach(['Purchase','Purchase Debit Note','Purchase Credit Note','Journal'] as $type)
                                    @foreach($data->where('invoice_type', $type) as $row)
                                        @php
                                            if($row->invoice_type == 'Purchase Debit Note')
                                            {
                                                $invoiceTotal -= (float)($row->invoice_value ?? 0);
                                                $taxableTotal -= (float)($row->taxable_value ?? 0);
                                                $igstTotal -= (float)($row->igst ?? 0);
                                                $cgstTotal -= (float)($row->cgst ?? 0);
                                                $sgstTotal -= (float)($row->sgst ?? 0);
                                            }
                                            else
                                            {
                                                $invoiceTotal += (float)($row->invoice_value ?? 0);
                                                $taxableTotal += (float)($row->taxable_value ?? 0);
                                                $igstTotal += (float)($row->igst ?? 0);
                                                $cgstTotal += (float)($row->cgst ?? 0);
                                                $sgstTotal += (float)($row->sgst ?? 0);
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $sr++ }}</td>
                                            <td>{{ $row->gstin }}</td>
                                            <td>{{ $row->party_name }}</td>
                                            <td>
                                                @php
                                                    $url = '#';
                                                    if(($row->voucher_source ?? '') == 'purchase'){
                                                        $url = url('purchase-edit/'.$row->voucher_id);
                                                    }
                                                    elseif(($row->voucher_source ?? '') == 'purchase_return'){
                                                        $url = url('purchase-return-edit/'.$row->voucher_id);
                                                    }
                                                    elseif(($row->voucher_source ?? '') == 'sale_return'){
                                                        $url = url('sale-return-edit/'.$row->voucher_id);
                                                    }
                                                    elseif(($row->voucher_source ?? '') == 'journal'){
                                                        $url = url('journal/'.$row->voucher_id.'/edit');
                                                    }
                                                @endphp
                                                <a href="{{ $url }}" target="_blank">
                                                    {{ $row->invoice_no }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ !empty($row->invoice_date) ? date('d-m-Y', strtotime($row->invoice_date)) : '' }}
                                            </td>
                                            <td>{{ $row->invoice_type }}</td>
                                            <td class="text-right">
                                                {{ number_format((float)($row->invoice_value ?? 0), 2) }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format((float)($row->taxable_value ?? 0), 2) }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format((float)($row->igst ?? 0), 2) }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format((float)($row->cgst ?? 0), 2) }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format((float)($row->sgst ?? 0), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="11" class="text-center">
                                        No Records Found
                                    </td>
                                </tr>
                                @endif
                            </tbody>

                            <tfoot>

                                <tr style="font-weight:bold;background:#f8f9fa;">

                                    <td colspan="6" style="text-align:right;font-weight:bold;">
                                        Total
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($invoiceTotal,2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($taxableTotal,2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($igstTotal,2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($cgstTotal,2) }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($sgstTotal,2) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>
</div>

@include('layouts.footer')
@endsection