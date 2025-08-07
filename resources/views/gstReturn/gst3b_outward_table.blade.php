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
                                            <th style="width: 35%;">Nature of Supplies</th>
                                            <th style="width: 23%;" > Particulars </th> 
                                            <th style="width: 21%;">Books</th>
                                            <th style="width: 21%;">Portal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                                             

                                        {{-- Row (a) --}}
                                        <tr>
                                            <td rowspan="5" class="align-middle"><strong>(a)</strong>  Gross Outward taxable supplies (other than zero rated, nil rated and exempted)</td>
                                            <td> Total Taxable Value</td>
                                            <td> ₹{{  formatIndianNumber($taxable_value_sale ?? 0, 2) }}</td>
                                            <td>₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl4a']['txval'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Integrated Tax</td>
                                            <td> ₹{{formatIndianNumber($igst_sale ?? 0, 2) }}</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl4a']['iamt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Central Tax</td>
                                            <td> ₹{{formatIndianNumber($cgst_sale ?? 0, 2) }}</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl4a']['camt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>State/UT Tax</td>
                                            <td>₹{{formatIndianNumber($sgst_sale ?? 0, 2) }}</td>
                                            <td>₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl4a']['samt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Cess </td>
                                            <td>₹0.00</td>
                                            <td>₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl4a']['csamt'] ?? 0, 2) }}</td>
                                        </tr>

                                         <tr>
                                            <td rowspan="5" class="align-middle"><strong>(a)</strong> Debit Credit note Related to Outward taxable supplies (other than zero rated, nil rated and exempted)</td>
                                            <td> Total Taxable Value</td>
                                            <td> ₹{{ formatIndianNumber($net_note_taxable ?? 0, 2) }}</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl9b']['txval'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Integrated Tax</td>
                                            <td> ₹{{ formatIndianNumber($net_note_igst ?? 0, 2) }}</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl9b']['iamt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Central Tax</td>
                                            <td> ₹{{ formatIndianNumber($net_note_cgst ?? 0, 2) }} </td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl9b']['camt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>State/UT Tax</td>
                                            <td> ₹{{ formatIndianNumber($net_note_sgst ?? 0, 2) }} </td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl9b']['samt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> Cess </td>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['det']['tbl9b']['csamt'] ?? 0, 2) }}</td>
                                        </tr>


                                         <tr>
                                            <td rowspan="5" class="align-middle"><strong>(a)</strong> Net Outward taxable supplies (other than zero rated, nil rated and exempted)</td>
                                              <td> Total Taxable Value</td>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['txval'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Integrated Tax</td>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['iamt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Central Tax</td>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['camt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>State/UT Tax</td>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['samt'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td> ₹0.00</td>
                                            <td> ₹{{  formatIndianNumber($data1['sup_details']['osup_3_1a']['subtotal']['csamt'] ?? 0, 2) }}</td>
                                        </tr>


                                                            <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                        {{-- Row (b) --}}
                                        <tr > 
                                            <td rowspan="3" class="align-middle bg-light"><strong>(b)</strong> Outward taxable supplies (zero rated)</td>
                                               <td> Total Taxable Value</td>
                                            <td class="bg-light" > ₹0.00</td>
                                            <td class="bg-light"> ₹0.00</td>
                                        </tr>
                                        <tr>
                                             <td>Integrated Tax</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹0.00</td>
                                        </tr>
                                        <tr>
                                             <td>Cess</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹0.00</td>
                                        </tr>


                                                                      <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                        {{-- Row (c) --}}
                                        <tr>
                                            <td rowspan="1" class="align-middle"><strong>(c)</strong> Other outward supplies (Nil rated, exempted)</td>
                                               <td> Total Taxable Value</td>
                                            <td> ₹0.00</td>
                                            <td> ₹0.00</td>
                                        </tr>


                                                                      <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                        {{-- Row (d) --}}
                                        <tr>
                                            <td rowspan="5" class="align-middle bg-light"><strong>(d)</strong> Inward supplies (liable to reverse charge)</td>
                                               <td> Total Taxable Value</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹{{ formatIndianNumber($data['data']['sup_details']['isup_rev']['txval'] ?? 0, 2)  }}</td>
                                        </tr>
                                        <tr>
                                               <td> Integrated Tax</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹{{ formatIndianNumber($data['data']['sup_details']['isup_rev']['iamt'] ?? 0, 2)  }}</td>
                                        </tr>
                                        <tr>
                                               <td> Central Tax</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹{{ formatIndianNumber($data['data']['sup_details']['isup_rev']['camt'] ?? 0, 2)  }}</td>
                                        </tr>
                                        <tr>
                                               <td> State/UT Tax</td>
                                            <td class="bg-light"> ₹0.00</td>
                                            <td class="bg-light"> ₹{{ formatIndianNumber($data['data']['sup_details']['isup_rev']['samt'] ?? 0, 2)  }}</td>
                                        </tr>
                                        <tr>
                                               <td> Cess</td>
                                            <td class="bg-light" >₹0.00</td>
                                            <td class="bg-light">₹{{ formatIndianNumber($data['data']['sup_details']['isup_rev']['csamt'] ?? 0, 2)  }}</td>
                                        </tr> 


                                                                      <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                        {{-- Row (e) --}}
                                        <tr>
                                            <td rowspan="1" class="align-middle"><strong>(e)</strong> Non-GST outward supplies</td>
                                            <td> Total Taxable Value</td>
                                            <td> ₹0.00</td>
                                            <td> ₹0.00</td>
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
