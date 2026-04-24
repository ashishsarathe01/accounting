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
                <th>Overdue Amount </th>
                <th class="noprint">View</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($allocated as $b)
            <tr>
                <td>{{ \Carbon\Carbon::parse($b['date'])->format('d-m-Y') }}</td>
                <td>
                    {{ $b['bill_no'] }}

                    @if($b['remaining_part'] > 0)
                        <span class="text-muted">( {{ number_format($b['total'],2) }}  )</span>
                    @endif
                </td>

                <td class="text-danger font-weight-bold">
                    {{ number_format($b['overdue'],2) }}
                </td>

                <td class="noprint">
                    <a href="{{ url('purchase-invoice/' .$b['id']) }}" class="btn  btn-sm">
                        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="">
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-warning noprint">Print</button>
      <a href="{{ route('payable.overdue.pdf.download', $acc->id) }}" class="btn btn-success">Download PDF</a>
    </div>

</div>
 </div> <!-- END row -->
    </section>
</div>
@include('layouts.footer')
@endsection
