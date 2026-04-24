@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')

         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            @if(session('success'))
               <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2">Activate Merchant</h5>
            </div>

            <div class="bg-white table-view shadow-sm">
                <input type="text"
                    id="searchMerchant"
                    class="form-control mb-3"
                    placeholder="Search by Name or Mobile">
               <table class="table-striped table m-0 shadow-sm">
                  <thead>
                     <tr class="font-12 text-body bg-light-pink">
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach($merchants as $merchant)
                    <tr class="font-14 font-heading bg-white">
                    <td>{{ $merchant->name }}</td>
                    <td>{{ $merchant->mobile_no }}</td>

                    <td>
                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                            {{ $merchant->status == '1' ? 'Enable' : 'Disable' }}
                        </span>
                    </td>

                    <td>
                        {{-- ACTIVATE BUTTON --}}
                        <form method="POST"
                                action="{{ route('admin.update.merchant.status') }}"
                                style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="merchant_id" value="{{ $merchant->id }}">
                            <input type="hidden" name="status" value="1">

                            <button type="submit"
                                    class="btn btn-success btn-sm"
                                    {{ $merchant->status == '1' ? 'disabled' : '' }}>
                                Activate
                            </button>
                        </form>

                        {{-- DEACTIVATE BUTTON --}}
                        <form method="POST"
                                action="{{ route('admin.update.merchant.status') }}"
                                style="display:inline-block; margin-left:6px;">
                            @csrf
                            <input type="hidden" name="merchant_id" value="{{ $merchant->id }}">
                            <input type="hidden" name="status" value="0">

                            <button type="submit"
                                    class="btn btn-danger btn-sm"
                                    {{ $merchant->status == '0' ? 'disabled' : '' }}>
                                Deactivate
                            </button>
                        </form>
                    </td>
                    </tr>
                    @endforeach
                  </tbody>
               </table>
            </div>

         </div>
      </div>
   </section>
</div>

@include('layouts.footer')
<script>
document.getElementById('searchMerchant').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});
</script>
@endsection