@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">GSTR-1</h5> 
                </div>


                <div class="card mt-4 shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">B2B</h5>
        <a href="{{ route('gst.b2b.detailed.billwise', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white " style="min-width: 300px;">
           View Detailed B2B bill-wise
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Taxable Value:</div>
            <div class="col-md-6">{{ number_format($total_taxable_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Invoice Value:</div>
            <div class="col-md-6">{{ number_format($total_sale_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">CGST:</div>
            <div class="col-md-6">{{ number_format($total_cgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">SGST:</div>
            <div class="col-md-6">{{ number_format($total_sgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">IGST:</div>
            <div class="col-md-6">{{ number_format($total_igst, 2) }}</div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">B2C(Large)</h5>
         <a href="{{ route('gst.b2c.large.detailed', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white min-w-30" style="min-width: 300px;">
           View  B2C Large Details
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Taxable Value:</div>
            <div class="col-md-6">{{ number_format($b2c_large_taxable_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Invoice Value:</div>
            <div class="col-md-6">{{ number_format($b2c_large_sale_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">CGST:</div>
            <div class="col-md-6">{{ number_format($b2c_large_cgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">SGST:</div>
            <div class="col-md-6">{{ number_format($b2c_large_sgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">IGST:</div>
            <div class="col-md-6">{{ number_format($b2c_large_igst, 2) }}</div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">B2C</h5>
        <a href="{{ route('gst.b2c.normal.statewise', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white" style="min-width: 300px;">
           View State-wise B2C Normal Details
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Taxable Value:</div>
            <div class="col-md-6">{{ number_format($b2c_statewise_taxable, 2) }}</div>
        </div>
        
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">CGST:</div>
            <div class="col-md-6">{{ number_format($b2c_statewise_cgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">SGST:</div>
            <div class="col-md-6">{{ number_format($b2c_statewise_sgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">IGST:</div>
            <div class="col-md-6">{{ number_format($b2c_statewise_igst, 2) }}</div>
        </div>
    </div>
</div>

 
<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nil Rated Reg Inter</h5>
         <a href="{{ route('nilratedreginter', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white min-w-30" style="min-width: 300px;">
           View Detailed
        </a>
    </div> 
</div> 




<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Debit/Credit  Note Registered</h5>
         <a href="{{ route('debitNote', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white min-w-30" style="min-width: 300px;">
           View Detailed
        </a>
    </div> 
</div>

<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Debit/Credit Note Unregistered</h5>
         <a href="{{ route('debitNoteUnreg', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white min-w-30" style="min-width: 300px;">
           View Detailed
        </a>
    </div> 
</div>

<div class="card mt-4 shadow-sm border-0">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Hsn Summary</h5>
         <a href="{{ route('hsnSummary', ['merchant_gst' => $merchant_gst, 'company_id' => $company_id, 'from_date' => $from_date, 'to_date' => $to_date]) }}" 
           class="btn btn-sm btn-light text-success border-white min-w-30" style="min-width: 300px;">
           View Detailed
        </a>
    </div> 
</div>









