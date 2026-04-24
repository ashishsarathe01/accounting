@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
     @media print {

    /* Hide header */
    .header, 
    header,
    #header,
    .top-header,
    .noprint,
    .leftnav,
    .sidebar,
    .footer,
    footer,
    #footer,
    .list-of-view-company-section .row > :first-child
    {
        display: none !important;
        visibility: hidden !important;
    }

    /* Make main content full width */
    .col-lg-9,
    .bg-mint,
    .col-md-12 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 10px !important;
    }

    body {
        background: #fff !important;
    }

}
   @page { 
   size: A4;        /* Always A4 size page (210mm x 297mm) */
   margin: 5mm;     /* Outer margin around content */
}</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint ">

    <h3>{{ Session::get('company_name') }}</h3>

    <h4 class="mt-2 text-center font-weight-bold">
    {{ $acc->account_name }} Overdue Bills Report
</h4>


    <table class="table table-bordered mt-4">
        <thead class="bg-light text-white">
            <tr>
                <th>Date</th>
                <th>Bill No</th>
                <th style="text-align: right;">Overdue Amount </th>
                <th class="noprint">View</th>
            </tr>
        </thead>

        <tbody>
            @php $total_overdue = 0; @endphp
            @foreach ($allocated as $b)
            @php $total_overdue += $b['overdue']; @endphp
            <tr>
                <td>
                    {{ strtotime($b['date']) ? \Carbon\Carbon::parse($b['date'])->format('d-m-Y') : 'Opening Balance' }}
                </td>
                <td>
                    {{ $b['bill_no'] }}

                    @if($b['remaining_part'] > 0)
                        <span class="text-muted">( {{ formatIndianNumber($b['total'],2) }}  )</span>
                    @endif
                </td>

                <td class="text-danger font-weight-bold text-end">
                    {{ formatIndianNumber($b['overdue'],2) }}
                </td>

<td class="noprint">
    @if(empty($b['id']))
        <a href="{{ url('accountledger-filter') }}?party={{ $account_id }}&from_date={{ $oDate }}&to_date={{ $today }}"
           class="btn btn-sm">
            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
        </a>
    @else
        <a href="{{ url('sale-invoice/' . $b['id']) }}" class="btn btn-sm">
            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
        </a>
    @endif
</td>

            </tr>
            @endforeach
        </tbody>
      <tr>
            <td colspan="2" class="text-center fw-bold">Total</td>
            <td class="text-end fw-bold">
                {{ formatIndianNumber($total_overdue, 2) }}
            </td>
            <td></td>
        </tr>

    </table>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-warning noprint">Print</button>
      <a href="{{ route('overdue.pdf.download', $acc->id) }}?date={{ $today }}" class="btn btn-success noprint">Download PDF</a>
    </div>

</div>
 </div> <!-- END row -->
    </section>
</div>
@include('layouts.footer')
@endsection
