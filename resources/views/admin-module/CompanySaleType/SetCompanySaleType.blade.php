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

               <div class="alert alert-success alert-dismissible fade show">

                  {{ session('success') }}

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
                  Set Company Sale Type
               </h5>
                <a href="{{route('admin.company-sale-types.index')}}"><button class="btn btn-info">Manage Sale Type</button></a>
            </div>

            {{-- Table Card --}}

            <div class="bg-white table-view shadow-sm rounded-3 overflow-hidden">

               <form
                  action="{{ route('admin.company-sale-types.set.update') }}"
                  method="POST"
               >

                  @csrf

                  <div class="table-responsive">

                     <table
                        class="table align-middle mb-0"
                     >

                        <thead class="bg-light-pink">

                           <tr class="font-12 text-body">

                              <th width="80" class="ps-4 py-3">
                                 SR NO
                              </th>

                              <th class="py-3">
                                 COMPANY NAME
                              </th>

                              <th width="320" class="pe-4 py-3">
                                 COMPANY SALE TYPE
                              </th>

                           </tr>

                        </thead>

                        <tbody>

                           @foreach($companies as $key => $company)

                              <tr class="border-bottom">

                                 <td class="ps-4 fw-semibold text-muted">

                                    {{ $key + 1 }}

                                 </td>

                                 <td>

                                    <div class="fw-semibold text-dark">

                                       {{ strtoupper($company->company_name) }}

                                    </div>

                                 </td>

                                 <td class="pe-4">

                                    <select
                                       name="company_sale_type[{{ $company->id }}]"
                                       class="form-select shadow-none"
                                    >

                                       <option value="">
                                          SELECT SALE TYPE
                                       </option>

                                       @foreach($saleTypes as $saleType)

                                          <option
                                             value="{{ strtoupper($saleType->sale_type) }}"

                                             @if(
                                                strtoupper($company->company_sale_type)
                                                ==
                                                strtoupper($saleType->sale_type)
                                             )
                                                selected
                                             @endif
                                          >

                                             {{ strtoupper($saleType->sale_type) }}

                                          </option>

                                       @endforeach

                                    </select>

                                 </td>

                              </tr>

                           @endforeach

                        </tbody>

                     </table>

                  </div>

                  <div class="p-4 border-top bg-light">

                     <button
                        type="submit"
                        class="btn btn-xs-primary px-4"
                     >
                        UPDATE
                     </button>

                  </div>

               </form>

            </div>

         </div>

      </div>

   </section>

</div>

@include('layouts.footer')

@endsection