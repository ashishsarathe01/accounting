@extends('layouts.app')

@section('content')
@include('layouts.header')
<style>
    td {
        font-size : 20px;
    }
    </style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-10 col-sm-12 px-4">
                <div class="container-fluid">

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <strong>
                                3.1 Details of Outward Supplies and inward supplies liable to reverse charge 
                                (other than those covered by Table 3.1.1)
                            </strong>
                        </div>

                        <div class="card-body p-3">
                            <div class="alert alert-info p-2 mb-3" role="alert" style="font-size: 14px;">
                                <i class="bi bi-info-circle"></i>
                                Table 3.1(a), (b), (c) and (e) are auto-drafted based on values provided in GSTR-1. 
                                Whereas Table 3.1(d) is auto-drafted based on GSTR-2B.
                            </div>
              
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" style="font-size: 14px;" aria-label="Outward and Inward Supplies Table">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 30%;">Nature of Supplies</th>
                                            <th style="width: 8%;">Source</th>
                                            <th style="width: 12%;">Total Taxable Value</th>
                                            <th style="width: 12%;">Integrated Tax</th>
                                            <th style="width: 12%;">Central Tax</th>
                                            <th style="width: 12%;">State/UT Tax</th>
                                            <th style="width: 12%;">Cess</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        {{-- Row (a) Gross Outward taxable supplies --}}
                                        <tr>
                                            
                                        @php
                                            $net_taxable = ($taxable_value_sale ?? 0) + ($net_note_taxable ?? 0);
                                            $net_igst    = ($igst_sale ?? 0) + ($net_note_igst ?? 0);
                                            $net_cgst    = ($cgst_sale ?? 0) + ($net_note_cgst ?? 0);
                                            $net_sgst    = ($sgst_sale ?? 0) + ($net_note_sgst ?? 0);
                                        @endphp

                                        {{-- Row (a) Net Outward taxable supplies --}}
                                        <tr>
                                            <td rowspan="2" class="align-middle">
                                                <strong>(a)</strong> Net Outward taxable supplies (other than zero rated, nil rated and exempted)
                                            </td>
                                            <td class="text-center"><strong>Books</strong></td>
                                            <td>₹{{ formatIndianNumber($net_taxable ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($net_igst ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($net_cgst ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($net_sgst ?? 0, 2) }}</td>
                                            <td>₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><strong>Portal</strong></td>
                                            <td>₹{{ formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['txval'] ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['iamt'] ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['camt'] ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['samt'] ?? 0, 2) }}</td>
                                            <td>₹{{ formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['csamt'] ?? 0, 2) }}</td>
                                        </tr>

                                        {{-- Row (b) Outward taxable supplies (zero rated) --}}
                                        <tr>
                                            <td rowspan="2" class="align-middle bg-light">
                                                <strong>(b)</strong> Outward taxable supplies (zero rated)
                                            </td>
                                            <td class="text-center bg-light"><strong>Books</strong></td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center bg-light"><strong>Portal</strong></td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                        </tr>

                                        {{-- Row (c) Other outward supplies (Nil rated, exempted) --}}
                                        <tr>
                                            <td rowspan="2" class="align-middle">
                                                <strong>(c)</strong> Other outward supplies (Nil rated, exempted)
                                            </td>
                                            <td class="text-center"><strong>Books</strong></td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><strong>Portal</strong></td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                        </tr>

                                        {{-- Row (d) Inward supplies (liable to reverse charge) --}}
                                        <tr>
                                            <td rowspan="2" class="align-middle bg-light">
                                                <strong>(d)</strong> Inward supplies (liable to reverse charge)
                                            </td>
                                            <td class="text-center bg-light"><strong>Books</strong></td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                            <td class="bg-light">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center bg-light"><strong>Portal</strong></td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['sup_details']['isup_rev']['txval'] ?? 0, 2) }}</td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['sup_details']['isup_rev']['iamt'] ?? 0, 2) }}</td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['sup_details']['isup_rev']['camt'] ?? 0, 2) }}</td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['sup_details']['isup_rev']['samt'] ?? 0, 2) }}</td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['sup_details']['isup_rev']['csamt'] ?? 0, 2) }}</td>
                                        </tr>

                                        {{-- Row (e) Non-GST outward supplies --}}
                                        <tr>
                                            <td rowspan="2" class="align-middle">
                                                <strong>(e)</strong> Non-GST outward supplies
                                            </td>
                                            <td class="text-center"><strong>Books</strong></td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center"><strong>Portal</strong></td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                            <td>₹0.00</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection
