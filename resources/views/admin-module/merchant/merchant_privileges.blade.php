@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<style>
    ul {
        list-style: none;
        margin-left: 20px;
    }
    .toggle {
        cursor: pointer;
        font-weight: bold;
        margin-right: 5px;
    }
    .hidden {
        display: none;
    }
</style>

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">

         @include('admin-module.layouts.leftnav')

         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            @if (session('error'))
               <div class="alert alert-danger" role="alert">
                   {{ session('error') }}
               </div>
            @endif

            @if (session('success'))
               <div class="alert alert-success" role="alert">
                   {{ session('success') }}
               </div>
            @endif

            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                Set Privileges
            </h5>

            {{-- Company Tabs --}}
            <ul class="nav nav-fill nav-tabs" role="tablist">
               @foreach ($company as $key=>$value)
                  <li class="nav-item" role="presentation">
                     <a class="nav-link @if($key==0) active @endif"
                        id="fill-tab-{{$value->id}}"
                        data-bs-toggle="tab"
                        href="#fill-tabpanel-{{$value->id}}"
                        role="tab">
                        {{$value->company_name}}
                     </a>
                  </li>
               @endforeach
            </ul>

            <div class="tab-content pt-5" id="tab-content">

               @foreach ($company as $key=>$value)

                  <div class="tab-pane @if($key==0) active @endif"
                       id="fill-tabpanel-{{$value->id}}"
                       role="tabpanel">

                     <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm"
                           method="POST"
                           action="{{ route('admin.set-merchant-module-privileges') }}">

                        @csrf

                        <input type="hidden"
                               name="merchant_id"
                               value="{{$merchant_id}}">

                        <input type="hidden"
                               name="company_id"
                               value="{{$value->id}}">

                        @php
    $assigned = \App\Models\MerchantPrivilegeMapping::where('merchant_id',$merchant_id)
        ->where('company_id',$value->id)
        ->pluck('module_id')
        ->toArray();
@endphp

<ul>
    @foreach ($privileges as $module)
        @include('admin-module.merchant.merchant_privilege_module', [
            'module' => $module,
            'assigned' => $assigned
        ])
    @endforeach
</ul>


                        <div style="text-align: center !important; margin-top:20px;">

                           @if(count($company) > 1)
                              <input type="checkbox" name="apply_all">
                              <strong>APPLY ON ALL COMPANY</strong>
                           @endif

                           <button type="submit"
                                   class="btn btn-xs-primary">
                               SUBMIT
                           </button>

                        </div>

                     </form>

                  </div>

               @endforeach

            </div>

         </div>

      </div>
   </section>
</div>

@include('admin-module.layouts.footer')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Expand / Collapse only
    document.querySelectorAll('.toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ul = this.closest('li').querySelector('ul');
            if (ul) {
                ul.classList.toggle('hidden');
                this.textContent = ul.classList.contains('hidden') ? '[+]' : '[-]';
            }
        });
    });

});
</script>


@endsection
