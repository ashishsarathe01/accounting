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
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">B2B</h5>
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
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">B2C(Large)</h5>
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
           class="btn btn-sm btn-light text-success border-white">
           View State-wise B2C Normal Details
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Taxable Value:</div>
            <div class="col-md-6">{{ number_format($b2c_normal_taxable_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">Invoice Value:</div>
            <div class="col-md-6">{{ number_format($b2c_normal_sale_amt, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">CGST:</div>
            <div class="col-md-6">{{ number_format($b2c_normal_cgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">SGST:</div>
            <div class="col-md-6">{{ number_format($b2c_normal_sgst, 2) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-6 font-weight-bold">IGST:</div>
            <div class="col-md-6">{{ number_format($b2c_normal_igst, 2) }}</div>
        </div>
    </div>
</div>

