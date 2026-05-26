@extends('admin-module.layouts.app')

@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">

   <section class="list-of-view-company-section container-fluid">

      <div class="row vh-100">

         @include('admin-module.layouts.leftnav')

         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            {{-- Success Message --}}

            @if(session('success'))

               <div class="alert alert-success alert-dismissible fade show" role="alert">

                  {{ session('success') }}

                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="alert"
                  >
                  </button>

               </div>

            @endif

            {{-- Error Message --}}

            @if ($errors->any())

               <div class="alert alert-danger alert-dismissible fade show" role="alert">

                  <ul class="mb-0">

                     @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                     @endforeach

                  </ul>

                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="alert"
                  >
                  </button>

               </div>

            @endif

            {{-- Title Bar --}}

            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-3 px-4 mb-4">

               <h5 class="table-title m-0 py-2">
                  Edit Company Sale Type
               </h5>

            </div>

            {{-- Form Card --}}

            <div class="bg-white table-view shadow-sm rounded-3">

               <div class="card-body p-4">

                  <form
                     action="{{ route('admin.company-sale-types.update',$saleType->id) }}"
                     method="POST"
                  >

                     @csrf

                     <div class="row">

                        <div class="col-md-6">

                           <div class="mb-3">

                              <label class="form-label">
                                 Company Sale Type
                              </label>

                              <input
                                 type="text"
                                 name="sale_type"
                                 class="form-control"
                                 placeholder="Enter Company Sale Type"
                                 value="{{ $saleType->sale_type }}"
                                 required
                              >

                           </div>

                        </div>

                     </div>

                     <div class="mt-4">

                        <button
                           type="submit"
                           class="btn btn-xs-primary"
                        >
                           UPDATE
                        </button>

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