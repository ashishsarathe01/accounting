@extends('layouts.app')

@section('content')
    @include('layouts.header')
  <style>
     p {
    font-size: 18px;
  }

  table {
      width: 100%;
      border-collapse: collapse;
      font-family: Arial, sans-serif;
      font-size: 14px;
    
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px 12px;
      text-align: left;
      font-size: 18px;
    }
    th {
      color: white;
    }
    .section-header {
      background-color: #f0f0f0;
      font-weight: bold;
    }
    </style>

    <div class="list-of-view-company">
        <section class="list-of-view-company-section container-fluid">
            <div class="  min-vh-100 row ">
                @include('layouts.leftnav')

                <div class="col-md-10 col-sm-12 px-0">
                    <div class="container-fluid">
                        <ul class="nav nav-fill nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="fill-tab-3" data-bs-toggle="tab" href="#fill-tabpanel-3" role="tab" aria-controls="fill-tabpanel-3" aria-selected="true">
                                    GSTR-3B Book
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link " id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true">
                                    GSTR-3B Comparison
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false">
                                    GSTR-3B Filing
                                </a>
                            </li>

                        <div class="w-100 mt-0">
                            <div class="tab-content mt-2">
                                <!-- View Tab -->
                                <div class="tab-pane active" id="fill-tabpanel-3" role="tabpanel" aria-labelledby="fill-tab-3">
                                    <div id="view2" class="view-content" style="height:100vh;">
                                      <div class=" min-vh-100 w-100 px-4 bg-light py-4 ">
                                            <h2>GSTR-3B Summary</h2>
                                                <table >
                                                    <thead>
                                                        <tr class=" bg-info">
                                                            <th>Particular</th>
                                                            <th>Books</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- Section 3.1 --}}
                                                        <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                                        <tr class="section-header">
                                                            <td >3.1 Tax on outward and reverse charge inward supplies </td>
                                                            @php
                                                             
                                                              $url = route('OutwardDetails.view', [
                                                                        'series' => $merchant_gst, 
                                                                        'from_date' => $from_date, 
                                                                        'to_date' => $to_date
                                                                    ]);
                                                                @endphp
                                                             <td>Books
                                                             <a class=" btn-primary" href="{{ $url }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                             </td>
                                                             
                                                        </tr>
                                                       <tr>
                                                                <td>Taxable</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['TAXABLE'], 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>Integrated Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['IGST'] ?? 0, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>Central Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['CGST'] ?? 0, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>State/UT Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['SGST'] ?? 0, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>CESS</td>
                                                                <td>₹0.00</td>
                                                            </tr>

                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                                                
                                                        {{-- Section 3.1.1 --}}
                                                        <tr class="section-header">
                                                            <td >3.1.1 Supplies notified under section 9(5)</td>
                                                                <td>Books</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                        </tr>

                                                        <tr>
                                                                    <td colspan="2" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="2" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 3.2 --}}
                                                        <tr class="section-header">
                                                            <td >3.2 Inter-state supplies</td>
                                                                <td>Books</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Taxable Value</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>

                                                                <tr>
                                                                    <td colspan="2" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="2" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 4 --}}
                                                        <tr class="section-header">
                                                            @php
                                                                    $url_itc = route('itcDetails.view', [
                                                                        'series' => $merchant_gst, 
                                                                        'from_date' => $from_date, 
                                                                        'to_date' => $to_date,
                                                                         'data'       => json_encode($data)
                                                                    ]);
                                                                    $books_igst_amount = $books_igst_amount ?? 0;
                                                                    $books_cgst_amount = $books_cgst_amount ?? 0;
                                                                    $books_sgst_amount = $books_sgst_amount ?? 0;

                                                                @endphp
                                                            <td >4. Eligible ITC</td>
                                                                <td>Books
                                                                <a class="btn btn-primary" href="{{ $url_itc }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                                </td>
                                                                
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['IGST'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['CGST'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['SGST'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                        </tr>

                                                                <tr>
                                                                    <td colspan="2" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="2" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 5 --}}
                                                        <tr class="section-header">
                                                            <td >5. Exempt, nil and Non GST inward supplies</td>
                                                                <td>Books</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Inter-state supplies</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Intra-state supplies</td>
                                                            <td>₹0.00</td>
                                                        </tr>

                                                        <tr>
                                                                    <td colspan="2" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="2" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 5.1 --}}
                                                        <tr class="section-header">
                                                                <td>5.1 Interest and Late fee for previous tax period</td>
                                                                <td>Books</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                        </tr>


                                                        <tr>
                                                                    <td colspan="2" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="2" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        @php
                                                            $url_payment_tax = route('paymentOfTax.view', [
                                                                'series'    => $merchant_gst,
                                                                'from_date' => $from_date,
                                                                'to_date'   => $to_date,
                                                                'data'      => json_encode($data)
                                                            ]);
                                                        @endphp

                                                        <tr class="section-header">
                                                            <td>6.1 Payment of tax</td>
                                                            <td>
                                                                Books
                                                                <a class="btn btn-primary" href="{{ $url_payment_tax }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Balance Liability</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Paid through Cash</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Paid through Credit</td>
                                                            <td>₹0.00</td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                

                                                <div style="height:20px;"></div>

                                    </div>
                                    </div>
                                    </div>
                                <div class="tab-pane" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                                    <div id="view2" class="view-content" style="height:100vh;">
                                      <div class=" min-vh-100 w-100 px-4 bg-light py-4 ">
                                            <h2>GSTR-3B Summary</h2>
                                                <table >
                                                    <thead>
                                                        <tr class=" bg-info">
                                                            <th>Particular</th>
                                                            <th>Books</th>
                                                            <th>Portal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- Section 3.1 --}}
                                                        <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                                        <tr class="section-header">
                                                            <td >3.1 Tax on outward and reverse charge inward supplies </td>
                                                             <td>Books</td>
                                                             @php
                                                             
                                                              $supDetails = $data['sup_details'] ?? [];
                                                            
                                                                $totalTxval = collect($supDetails)->sum('txval');
                                                                $totalIamt  = collect($supDetails)->sum('iamt');
                                                                $totalCamt  = collect($supDetails)->sum('camt');
                                                                $totalSamt  = collect($supDetails)->sum('samt');
                                                                $totalCsamt = collect($supDetails)->sum('csamt');

                                                                    $url = route('OutwardDetails.view', [
                                                                        'series' => $merchant_gst, 
                                                                        'from_date' => $from_date, 
                                                                        'to_date' => $to_date
                                                                    ]);
                                                                @endphp
                                                            <td class="d-flex justify-content-between align-items-center">
                                                                <span>Portal</span>
                                                                <a class=" btn-primary" href="{{ $url }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                       <tr>
                                                                <td>Taxable</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['TAXABLE'], 2) }}</td>
                                                                <td>₹{{ formatIndianNumber($totalTxval, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>Integrated Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['IGST'] ?? 0, 2) }}</td>
                                                                <td>₹{{ formatIndianNumber($totalIamt, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>Central Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['CGST'] ?? 0, 2) }}</td>
                                                                <td>₹{{ formatIndianNumber($totalCamt, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>State/UT Tax</td>
                                                                <td>₹ {{ formatIndianNumber($Data31['SGST'] ?? 0, 2) }}</td>
                                                                <td>₹{{ formatIndianNumber($totalSamt, 2) }}</td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td>CESS</td>
                                                                <td>₹0.00</td>
                                                                <td>₹{{ formatIndianNumber($totalCsamt, 2) }}</td>
                                                            </tr>

                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>
                                                                
                                                        {{-- Section 3.1.1 --}}
                                                        <tr class="section-header">
                                                            <td >3.1.1 Supplies notified under section 9(5)</td>
                                                                <td>Books</td>
                                                            <td>Portal </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['osup_n9']['iamt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['osup_n9']['camt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['osup_n9']['samt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['osup_n9']['csamt'] ?? 0, 2) }}</td>
                                                        </tr>

                                                        <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 3.2 --}}
                                                        <tr class="section-header">
                                                            <td >3.2 Inter-state supplies</td>
                                                                <td>Books</td>
                                                            <td>Portal </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Taxable Value</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['isup_details']['txval'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['sup_details']['isup_details']['iamt'] ?? 0, 2) }}</td>
                                                        </tr>

                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 4 --}}
                                                        <tr class="section-header">
                                                            <td >4. Eligible ITC</td>
                                                                <td>Books</td>
                                                                @php
                                                                    $url_itc = route('itcDetails.view', [
                                                                        'series' => $merchant_gst, 
                                                                        'from_date' => $from_date, 
                                                                        'to_date' => $to_date,
                                                                         'data'       => json_encode($data)
                                                                    ]);
                                                                    $books_igst_amount = $books_igst_amount ?? 0;
                                                                    $books_cgst_amount = $books_cgst_amount ?? 0;
                                                                    $books_sgst_amount = $books_sgst_amount ?? 0;

                                                                @endphp
                                                            <td class="d-flex justify-content-between align-items-center">
                                                                <span>Portal</span>
                                                                <a class="btn btn-primary" href="{{ $url_itc }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['IGST'] ?? 0, 2) }}</td>
                                                            <td>₹{{ formatIndianNumber($data['itc_elg']['itc_net']['iamt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['CGST'] ?? 0, 2) }}</td>
                                                            <td>₹{{ formatIndianNumber($data['itc_elg']['itc_net']['camt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹ {{ formatIndianNumber($Data4['SGST'] ?? 0, 2) }}</td>
                                                            <td>₹{{ formatIndianNumber($data['itc_elg']['itc_net']['samt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['itc_elg']['itc_net']['csamt'] ?? 0, 2) }}</td>
                                                        </tr>

                                                                <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 5 --}}
                                                        <tr class="section-header">
                                                            <td >5. Exempt, nil and Non GST inward supplies</td>
                                                                <td>Books</td>
                                                            <td>Portal </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Inter-state supplies</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['inward_sup']['isup']['inter'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Intra-state supplies</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['inward_sup']['isup']['intra'] ?? 0, 2) }}</td>
                                                        </tr>

                                                        <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        {{-- Section 5.1 --}}
                                                        <tr class="section-header">
                                                                <td>5.1 Interest and Late fee for previous tax period</td>
                                                                <td>Books</td>
                                                            <td>Portal </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Integrated Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['interest_latefee']['intr']['iamt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Central Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['interest_latefee']['intr']['camt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>State/UT Tax</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['interest_latefee']['intr']['samt'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CESS</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['interest_latefee']['intr']['csamt'] ?? 0, 2) }}</td>
                                                        </tr>


                                                        <tr>
                                                                    <td colspan="3" style="border-left: none; border-right: none;  border-top:none; border-bottom:none;"></td>
                                                                </tr>


                                                                <tr >
                                                                    <td colspan="3" style="border-left: none; border-right: none; border-top:none; border-bottom:none;"></td>
                                                                </tr>

                                                        <tr class="section-header">
                                                            <td>6.1 Payment of tax</td>
                                                            <td>Books</td>
                                                            <td class="d-flex justify-content-between align-items-center">
                                                                <span>Portal</span>
                                                                <a class="btn btn-primary" href="{{ $url_payment_tax }}">
                                                                    <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                                                                </a>
                                                            </td>
                                                        </tr>
                                                            <td>Balance Liability</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['tax_pay']['bal_liab'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Paid through Cash</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['tax_pay']['cash_paid'] ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Paid through Credit</td>
                                                            <td>₹0.00</td>
                                                            <td>₹{{ formatIndianNumber($data['tax_pay']['itc_availed'] ?? 0, 2) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!--<div class="text-end mt-3">-->
                                                <!--    <button class="btn btn-danger">Cancel</button>-->
                                                <!--    <button class="btn btn-success">Continue</button>-->
                                                <!--</div>-->

                                                <div style="height:20px;"></div>

                                    </div>
                                    </div>
                                    </div>

                                <!-- Filing Tab -->
                                <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1" >
                                    <div id="view2" class="view-content" style="height:100vh;">
                                        <div class="w-100 px-4 bg-light py-4  h-100">
                                            <h1 class="text-primary mb-2">GSTR-3B Summary</h1>
                                            <div class="row g-4">
                                                <!-- 3.1 Tax on outward and reverse charge inward supplies -->
                                                                                            @php
                                                $url = route('OutwardDetails.view', [
                                                    'series' => $merchant_gst,
                                                    'from_date' => $from_date,
                                                    'to_date' => $to_date
                                                ]);
                                            @endphp
                                            
                                            <div class="col-md-4">
                                                <a href="{{ $url }}" style="text-decoration: none; color: inherit;">
                                                    
                                                    <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">
                                                        3.1 Tax on outward and reverse charge inward supplies
                                                    </div>
                                            
                                                    <div class="bg-white border p-3 rounded-bottom">
                                                        <p>
                                                            Integrated Tax: 
                                                            ₹{{ formatIndianNumber($data['sup_details']['osup_det']['iamt'] ?? 0, 2) }}
                                                        </p>
                                            
                                                        <p>
                                                            Central Tax: 
                                                            ₹{{ formatIndianNumber($data['sup_details']['osup_det']['camt'] ?? 0, 2) }}
                                                        </p>
                                            
                                                        <p>
                                                            State/UT Tax: 
                                                            ₹{{ formatIndianNumber($data['sup_details']['osup_det']['samt'] ?? 0, 2) }}
                                                        </p>
                                            
                                                        <p>
                                                            CESS (₹): ₹0.00
                                                        </p>
                                                    </div>
                                            
                                                </a>
                                            </div>

                                                <!-- 3.1.1 Supplies under sec 9(5) -->
                                                <div class="col-md-4">
                                                    <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">3.1.1 Supplies notified under section 9(5)</div>
                                                    <div class="bg-white border p-3 rounded-bottom">
                                                        <p class="mt-2">Integrated Tax: ₹0.00</p>
                                                        <p>Central Tax: ₹0.00</p>
                                                        <p>State/UT Tax: ₹0.00</p>
                                                        <p>CESS (₹): ₹0.00</p>
                                                    </div>
                                                </div>

                                                <!-- 3.2 Inter-state supplies -->
                                                <div class="col-md-4">
                                                    <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">3.2 Inter-state supplies</div>
                                                    <div class="bg-white border p-3 rounded-bottom">
                                                        <p>Taxable Value: ₹0.00</p>
                                                        <p>Integrated Tax: ₹0.00</p>
                                                    </div>
                                                </div>

                                                <!-- 4. Eligible ITC -->
                                              <div class="col-md-4">
                                                            @php
                                                                $url_itc = route('itcDetails.view', [
                                                                    'series'    => $merchant_gst,
                                                                    'from_date' => $from_date,
                                                                    'to_date'   => $to_date,
                                                                    'data'      => json_encode($data)
                                                                ]);
                                                            @endphp
                                                        
                                                            <a href="{{ $url_itc }}" style="text-decoration: none;">
                                                                <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6 d-flex justify-content-between align-items-center">
                                                                    <span>4. Eligible ITC</span>
                                                        
                                                                </div>
                                                        
                                                                <div class="bg-white border p-3 rounded-bottom text-dark">
                                                                    <p>Integrated Tax: ₹{{ formatIndianNumber($data['itc_elg']['itc_net']['iamt'] ?? 0, 2) }}</p>
                                                        
                                                                    <p>Central Tax: ₹{{ formatIndianNumber($data['itc_elg']['itc_net']['camt'] ?? 0, 2) }}</p>
                                                        
                                                                    <p>State/UT Tax: ₹{{ formatIndianNumber($data['itc_elg']['itc_net']['samt'] ?? 0, 2) }}</p>
                                                        
                                                                    <p>CESS: ₹{{ formatIndianNumber($data['itc_elg']['itc_net']['csamt'] ?? 0, 2) }}</p>
                                                                </div>
                                                            </a>
                                                        </div>

                                                <!-- 5. Exempt, nil and non-GST -->
                                                <div class="col-md-4">
                                                    <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">5. Exempt, nil and Non GST inward supplies</div>
                                                    <div class="bg-white border p-3 rounded-bottom">
                                                        <p>Inter-state supplies: ₹0.00</p>
                                                        <p>Intra-state supplies: ₹0.00</p>
                                                    </div>
                                                </div>

                                                <!-- 5.1 Interest and Late Fee -->
                                                <div class="col-md-4">
                                                    <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">5.1 Interest and Late fee for previous tax period</div>
                                                    <div class="bg-white border p-3 rounded-bottom">
                                                        <p>Integrated Tax: ₹0.00</p>
                                                        <p>Central Tax: ₹0.00</p>
                                                        <p>State/UT Tax: ₹0.00</p>
                                                        <p>CESS (₹): ₹0.00</p>
                                                    </div>
                                                </div>

                                                <!-- 6.1 Payment of Tax -->
                                                <div class="col-md-12">
                                                    <a href="{{ $url_payment_tax }}" style="text-decoration:none;color:inherit;">
                                                        <div class="bg-primary text-white p-2 fw-bold rounded-top fs-6">
                                                            6.1 Payment of tax
                                                        </div>

                                                        <div class="bg-white border p-3 rounded-bottom">
                                                            <p>Balance Liability: ₹0.00</p>
                                                            <p>Paid through Cash: ₹0.00</p>
                                                            <p>Paid through Credit: ₹0.00</p>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div> <!-- row -->
                                            <div class="text-end mt-3">
                                                    @if($check_2b_status)
                                                        <button type="button" class="btn btn-success">File</button>
                                                    @else
                                                        <h5 style="color: red;">Verification Of  GSTR2B Pending</h5>
                                                    @endif
                                                </div>
                                        </div> <!-- inner content -->
                                    </div> <!-- tab view -->
                                </div> <!-- tab pane -->
                            </div> <!-- tab content -->
                        </div> <!-- container -->
                    </div> <!-- container-fluid -->
                </div> <!-- col -->
            </div> <!-- row -->
        </section>
    </div>

    @include('layouts.footer')
@endsection
