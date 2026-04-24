@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid py-3">
      <div class="row">
         @include('layouts.leftnav')

         <div class="col-md-12 col-lg-9 px-md-4 bg-light">

            @if (session('error'))
               <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            @if (session('success'))
               <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif

            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Export Sale Return
            </h5>

            <div class="card shadow-sm border-0 mb-4">
               <div class="card-body">
                  <form method="POST" action="{{ route('sale-return-export') }}" class="row g-4">
                     @csrf
                        
                     <div class="col-md-4">
                        <label for="from_date" class="form-label fw-semibold">From Date</label>
                        <input type="date"
                               id="from_date"
                               name="from_date"
                               class="form-control"
                               required
                               value="{{ old('from_date', date('Y-m-d')) }}">
                     </div>

                     <div class="col-md-4">
                        <label for="to_date" class="form-label fw-semibold">To Date</label>
                        <input type="date"
                               id="to_date"
                               name="to_date"
                               class="form-control"
                               required
                               value="{{ old('to_date', date('Y-m-d')) }}">
                     </div>

                     <div class="col-md-4">
                        <label class="form-label fw-semibold">Sale Return Type</label>
                        <div class="d-flex mt-2">

                           <div class="form-check me-4">
                              <input class="form-check-input"
                                     type="radio"
                                     name="sr_type"
                                     id="sr_with_item"
                                     value="WITH ITEM"
                                     {{ old('sr_type','WITH ITEM') == 'WITH ITEM' ? 'checked' : '' }}>
                              <label class="form-check-label" for="sr_with_item">
                                 WITH ITEM
                              </label>
                           </div>

                           <div class="form-check">
                              <input class="form-check-input"
                                     type="radio"
                                     name="sr_type"
                                     id="sr_rate_diff"
                                     value="RATE DIFFERENCE"
                                     {{ old('sr_type') == 'RATE DIFFERENCE' ? 'checked' : '' }}>
                              <label class="form-check-label" for="sr_rate_diff">
                                 RATE DIFFERENCE
                              </label>
                           </div>

                        </div>
                     </div>

                     <div class="col-md-4">
   <label class="form-label fw-semibold">Sale Area</label>
   <div class="d-flex mt-2">

      <div class="form-check me-4">
         <input class="form-check-input"
                type="radio"
                name="sale_area"
                id="sale_local"
                value="LOCAL"
                {{ old('sale_area','LOCAL') == 'LOCAL' ? 'checked' : '' }}>
         <label class="form-check-label" for="sale_local">
            LOCAL
         </label>
      </div>

      <div class="form-check">
         <input class="form-check-input"
                type="radio"
                name="sale_area"
                id="sale_center"
                value="CENTER"
                {{ old('sale_area') == 'CENTER' ? 'checked' : '' }}>
         <label class="form-check-label" for="sale_center">
            CENTER
         </label>
      </div>

   </div>
</div>


                     <div class="col-md-12 d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                           Download CSV
                        </button>
                     </div>

                  </form>
               </div>
            </div>

            @if($errors->any())
               <div class="alert alert-danger">
                  <strong>Errors:</strong>
                  <ul class="mt-2 mb-0">
                     @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                     @endforeach
                  </ul>
               </div>
            @endif

         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
                <div class="shortcut-key w-100">
                <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
                <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                    <span class="border-bottom-black">F1</span><span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                    <span class="border-bottom-black">F2</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">F3
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                    <span class="border-bottom-black">F3</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                    <span class="border-bottom-black">F5</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                    <span class="border-bottom-black">F6</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                    <span class="border-bottom-black">F7</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                    <span class="border-bottom-black">F8</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                    <span class="border-bottom-black">F9</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                    <span class="border-bottom-black">B</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                    <span class="border-bottom-black">T</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                    <span class="border-bottom-black">S</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                    <span class="border-bottom-black">L</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                    <span class="border-bottom-black">I</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                    <span class="border-bottom-black">D</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                    <span class="border-bottom-black">G</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                    <span class="border-bottom-black">U</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                    <span class="border-bottom-black">F</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                    <span class="border-bottom-black">K</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
                </button>
                <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">Search Menu
                </button>
                </div>
            </div>
      </div>
   </section>
</div>

@include('layouts.footer')
@endsection
