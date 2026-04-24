@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            {{-- Success --}}
            @if(session('success'))
               <div class="alert alert-success alert-dismissible fade show">
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
               <div class="alert alert-danger alert-dismissible fade show">
                  <ul class="mb-0">
                     @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                     @endforeach
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
            @endif

            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-3 px-4 mb-4">
               <h5 class="table-title m-0 py-2">Edit TDS Section</h5>
            </div>

            <div class="bg-white table-view shadow-sm rounded-3">
               <div class="card-body p-4">

                  <form action="{{ route('admin.tds.update', $tds->id) }}" method="POST">
                     @csrf

                     <div class="row g-4">

                        {{-- Section --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Section *</label>
                           <input type="text" name="section"
                              class="form-control"
                              value="{{ old('section', $tds->section) }}" required>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Description</label>
                           <input type="text" name="description"
                              class="form-control"
                              value="{{ old('description', $tds->description) }}">
                        </div>
{{-- TDS Account Name --}}
<div class="col-md-6">
   <label class="form-label font-14 font-heading mb-2">
      Account Name <span class="text-danger">*</span>
   </label>

   <input type="text"
      name="account_name"
      class="form-control"
      value="{{ old('account_name', $account->account_name ?? '') }}"
      required>
</div>
                        {{-- Rate Individual --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Rate (Individual / HUF) % *</label>
                           <input type="number" step="0.01"
                              name="rate_individual_huf"
                              class="form-control"
                              value="{{ old('rate_individual_huf', $tds->rate_individual_huf) }}" required>
                        </div>

                        {{-- Rate Others --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Rate (Others) % *</label>
                           <input type="number" step="0.01"
                              name="rate_others"
                              class="form-control"
                              value="{{ old('rate_others', $tds->rate_others) }}" required>
                        </div>

                        {{-- Single Limit --}}
                        <div class="col-md-6">
                           <label class="form-label">Single Transaction Limit (₹)</label>
                           <input type="number" step="0.01"
                              name="single_transaction_limit"
                              class="form-control"
                              value="{{ old('single_transaction_limit', $tds->single_transaction_limit) }}">
                        </div>

                        {{-- Aggregate Limit --}}
                        <div class="col-md-6">
                           <label class="form-label">Aggregate Transaction Limit (₹)</label>
                           <input type="number" step="0.01"
                              name="aggregate_transaction_limit"
                              class="form-control"
                              value="{{ old('aggregate_transaction_limit', $tds->aggregate_transaction_limit) }}">
                        </div>

                        {{-- Applicable On --}}
                        <div class="col-md-6">
                           <label class="form-label">Applicable On *</label>
                           <select name="applicable_on" class="form-select" required>
                              <option value="above_limit" {{ old('applicable_on', $tds->applicable_on) == 'above_limit' ? 'selected' : '' }}>Above Limit</option>
                              <option value="on_total" {{ old('applicable_on', $tds->applicable_on) == 'on_total' ? 'selected' : '' }}>On Total</option>
                           </select>
                        </div>

                        {{-- Repeated --}}
                        <div class="col-md-6">
                           <label class="form-label">Repeated Transaction Calculation *</label>
                           <select name="repeated_transaction_applicable" class="form-select" required>
                              <option value="yes" {{ old('repeated_transaction_applicable', $tds->repeated_transaction_applicable) == 'yes' ? 'selected' : '' }}>Yes</option>
                              <option value="no" {{ old('repeated_transaction_applicable', $tds->repeated_transaction_applicable) == 'no' ? 'selected' : '' }}>No</option>
                           </select>
                        </div>

                        {{-- Applicable When --}}
                        <div class="col-md-6">
                           <label class="form-label">Applicable When *</label>
                           <select name="applicable_when" class="form-select" required>
                              <option value="payment" {{ old('applicable_when', $tds->applicable_when) == 'payment' ? 'selected' : '' }}>Payment</option>
                              <option value="credit" {{ old('applicable_when', $tds->applicable_when) == 'credit' ? 'selected' : '' }}>Credit</option>
                              <option value="payment_or_credit" {{ old('applicable_when', $tds->applicable_when) == 'payment_or_credit' ? 'selected' : '' }}>
                                 Payment or Credit (Whichever is Earlier)
                              </option>
                           </select>
                        </div>

                        {{-- Exemption --}}
                        <div class="col-md-6">
                           <label class="form-label">Exemption Applicable *</label>
                           <select name="exemption_applicable" class="form-select" required>
                              <option value="yes" {{ old('exemption_applicable', $tds->exemption_applicable) == 'yes' ? 'selected' : '' }}>Yes</option>
                              <option value="no" {{ old('exemption_applicable', $tds->exemption_applicable) == 'no' ? 'selected' : '' }}>No</option>
                           </select>
                        </div>

                     </div>

                     <div class="text-end mt-5 pt-3 border-top">
                        <button type="submit" class="btn btn-xs-primary px-4 me-2">
                           Update
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4">
                           Cancel
                        </a>
                     </div>

                  </form>

               </div>
            </div>

         </div>
      </div>
   </section>
</div>

@include('layouts.footer')
@endsection