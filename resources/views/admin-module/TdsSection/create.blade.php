@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
            {{-- Success/Error Messages --}}
            @if(session('success'))
               <div class="alert alert-success alert-dismissible fade show" role="alert">
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
            @endif
            
            @if ($errors->any())
               <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <ul class="mb-0">
                     @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                     @endforeach
                  </ul>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
               </div>
            @endif

            {{-- Title Bar --}}
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-3 px-4 mb-4">
               <h5 class="table-title m-0 py-2">Create TDS Section</h5>
               
            </div>

            {{-- Form Card --}}
            <div class="bg-white table-view shadow-sm rounded-3">
               <div class="card-body p-4">
                  <form action="{{ route('admin.tds.store') }}" method="POST">
                     @csrf
                     
                     <div class="row g-4">
                        {{-- Section --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Section <span class="text-danger">*</span></label>
                           <input type="text" name="section" class="form-control @error('section') is-invalid @enderror" 
                                  value="{{ old('section') }}" placeholder="e.g. 194C" required>
                           @error('section')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Description --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Description</label>
                           <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                                  value="{{ old('description') }}" placeholder="Enter description">
                           @error('description')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>
{{-- TDS Account Name --}}
<div class="col-md-6">
   <label class="form-label font-14 font-heading mb-2">
      Account Name <span class="text-danger">*</span>
   </label>

   <input type="text"
          name="account_name"
          class="form-control @error('account_name') is-invalid @enderror"
          value="{{ old('account_name') }}"
          placeholder="e.g. TDS Payable 194C"
          required>

   @error('account_name')
      <div class="invalid-feedback">{{ $message }}</div>
   @enderror
</div>
                        {{-- Rate Individual/HUF --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Rate (Individual / HUF) % <span class="text-danger">*</span></label>
                           <input type="number" step="0.01" name="rate_individual_huf" class="form-control @error('rate_individual_huf') is-invalid @enderror" 
                                  value="{{ old('rate_individual_huf') }}" required>
                           @error('rate_individual_huf')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Rate Others --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Rate (Others) % <span class="text-danger">*</span></label>
                           <input type="number" step="0.01" name="rate_others" class="form-control @error('rate_others') is-invalid @enderror" 
                                  value="{{ old('rate_others') }}" required>
                           @error('rate_others')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Single Transaction Limit --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Single Transaction Limit (₹)</label>
                           <input type="number" step="0.01" name="single_transaction_limit" 
                                  class="form-control @error('single_transaction_limit') is-invalid @enderror" 
                                  value="{{ old('single_transaction_limit') }}" placeholder="0.00">
                           @error('single_transaction_limit')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Aggregate Transaction Limit --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Aggregate Transaction Limit (₹)</label>
                           <input type="number" step="0.01" name="aggregate_transaction_limit" 
                                  class="form-control @error('aggregate_transaction_limit') is-invalid @enderror" 
                                  value="{{ old('aggregate_transaction_limit') }}" placeholder="0.00">
                           @error('aggregate_transaction_limit')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Applicable On --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Applicable On <span class="text-danger">*</span></label>
                           <select name="applicable_on" class="form-select @error('applicable_on') is-invalid @enderror" required>
                              <option value="">Select</option>
                              <option value="above_limit" {{ old('applicable_on') == 'above_limit' ? 'selected' : '' }}>Above Limit</option>
                              <option value="on_total" {{ old('applicable_on') == 'on_total' ? 'selected' : '' }}>On Total</option>
                           </select>
                           @error('applicable_on')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Repeated Transaction Calculation --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Repeated Transaction Calculation <span class="text-danger">*</span></label>
                           <select name="repeated_transaction_applicable" class="form-select @error('repeated_transaction_applicable') is-invalid @enderror" required>
                              <option value="">Select</option>
                              <option value="yes" {{ old('repeated_transaction_applicable') == 'yes' ? 'selected' : '' }}>Yes</option>
                              <option value="no" {{ old('repeated_transaction_applicable') == 'no' ? 'selected' : '' }}>No</option>
                           </select>
                           @error('repeated_transaction_applicable')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Applicable When --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Applicable When <span class="text-danger">*</span></label>
                           <select name="applicable_when" class="form-select @error('applicable_when') is-invalid @enderror" required>
                              <option value="">Select</option>
                              <option value="payment" {{ old('applicable_when') == 'payment' ? 'selected' : '' }}>Payment</option>
                              <option value="credit" {{ old('applicable_when') == 'credit' ? 'selected' : '' }}>Credit</option>
                              <option value="payment_or_credit" {{ old('applicable_when') == 'payment_or_credit' ? 'selected' : '' }}>
                                 Payment or Credit (Whichever is Earlier)
                              </option>
                           </select>
                           @error('applicable_when')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- Exemption Applicable --}}
                        <div class="col-md-6">
                           <label class="form-label font-14 font-heading mb-2">Exemption Applicable <span class="text-danger">*</span></label>
                           <select name="exemption_applicable" class="form-select @error('exemption_applicable') is-invalid @enderror" required>
                              <option value="">Select</option>
                              <option value="yes" {{ old('exemption_applicable') == 'yes' ? 'selected' : '' }}>Yes</option>
                              <option value="no" {{ old('exemption_applicable') == 'no' ? 'selected' : '' }}>No</option>
                           </select>
                           @error('exemption_applicable')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>

                     {{-- Action Buttons --}}
                     <div class="text-end mt-5 pt-3 border-top">
                        <button type="submit" class="btn btn-xs-primary px-4 me-2">
                           <i class="fas fa-save me-1"></i>Save
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4">
                           <i class="fas fa-times me-1"></i>Cancel
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
