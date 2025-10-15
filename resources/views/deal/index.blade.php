@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')


<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
           @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3 mx-3" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3 mx-3" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif          
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">List of Deal</h5>
               {{-- <form  action="{{ route('sale.index') }}" method="GET">
                  @csrf
                  <div class="d-md-flex d-block">                  
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                     </div>
                     <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                  </div>
               </form> --}}
               <div class="d-md-flex d-block"> 
                  <input type="text" id="search" class="form-control" placeholder="Search">
               </div>
               @can('action-module',85)
                  <a href="{{ route('deal.create') }}" class="btn btn-xs-primary">
                  ADD
                  <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" /></svg>
               </a>
               @endcan
               
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm sale_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th style="width: 9%;">Date </th>
                        <th>Account</th>
                        <th style="text-align: left;">item</th>
                        <th style="text-align: right;">Rate</th>
                        <th>Type</th>
                        <th style="text-align: right;">Quantity</th>
                        <th style="text-align: right;">Pending</th>
                        <th style="text-align: right;">Complete</th>
                        <th style="text-align: right;">Balance</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                     </tr>
                  </thead>
                 <tbody>
@forelse ($deals as $deal)
<tr>
   <td>{{ \Carbon\Carbon::parse($deal->created_at)->format('d-m-Y') }}</td>
   <td>{{ $deal->account_name }}</td>

   {{-- Items (shown vertically within the same cell) --}}
   <td style="text-align: left;">
      @foreach ($deal->items as $item)
         <div>{{ $item->name }}</div>
      @endforeach
   </td>

   <td style="text-align: right;">
      @foreach ($deal->items as $item)
         <div>{{ number_format($item->rate ?? 0, 2) }}</div>
      @endforeach
   </td>

   <td>{{ $deal->deal_type }}</td>
   <td style="text-align: right;">{{ $deal->qty }}</td>
  <td style="text-align: right;">{{ number_format($deal->total_pending ?? 0, 2) }}</td>
 <td style="text-align: right;">{{ number_format($deal->total_complete ?? 0, 2) }}</td>
   <td style="text-align: right;">{{ number_format($deal->balance_qty ?? 0, 2) }}</td>
   <td class="text-center">
    <a href="{{ route('deal.show', $deal->id) }}" ><img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View"></a>
    <a href="{{ route('deal.edit', $deal->id) }}" ><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="Edit"></a>
    <a href="{{ route('deal.destroy', $deal->id) }}" class="delete-form" ><img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="Delete"></a>
    
</td>

</tr>
@empty
<tr>
   <td colspan="10" class="text-center text-muted">No deals found</td>
</tr>
@endforelse


</tbody>

               </table>
            </div>
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">
                  F1
                  <span class="ps-1 fw-normal text-body">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F2</span>
                     <span class="ps-1 fw-normal text-body">Add Item</span>
               </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     F3
                     <span class="ps-1 fw-normal text-body">Add Master</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F3</span>
                     <span class="ps-1 fw-normal text-body">Add Voucher</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F5</span>
                     <span class="ps-1 fw-normal text-body">Add Payment</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F6</span>
                     <span class="ps-1 fw-normal text-body">Add Receipt</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F7</span>
                     <span class="ps-1 fw-normal text-body">Add Journal</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F8</span>
                     <span class="ps-1 fw-normal text-body">Add Sales</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-4 ">
                     <span class="border-bottom-black">F9</span>
                     <span class="ps-1 fw-normal text-body">Add Purchase</span>
                 </button>

                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">B</span>
                     <span class="ps-1 fw-normal text-body">Balance Sheet</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">T</span>
                     <span class="ps-1 fw-normal text-body">Trial Balance</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">S</span>
                     <span class="ps-1 fw-normal text-body">Stock Status</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">L</span>
                     <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">I</span>
                     <span class="ps-1 fw-normal text-body">Item Summary</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">D</span>
                     <span class="ps-1 fw-normal text-body">Item Ledger</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">G</span>
                     <span class="ps-1 fw-normal text-body">GST Summary</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">U</span>
                     <span class="ps-1 fw-normal text-body">Switch User</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">F</span>
                     <span class="ps-1 fw-normal text-body">Configuration</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="border-bottom-black">K</span>
                     <span class="ps-1 fw-normal text-body">Lock Program</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="ps-1 fw-normal text-body">Training Videos</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-2 ">
                     <span class="ps-1 fw-normal text-body">GST Portal</span>
                 </button>
                 <button class="p-2 transaction-shortcut-btn mb-4 ">
                     Search Menu
                 </button>
            </div>
         </div>
      </div>
   </section>
</div>

</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      
$(document).ready(function() {
    $('.delete-form').on('click', function(e) {
        e.preventDefault(); // stop page reload

        var url = $(this).attr('href'); // get delete route

        if (confirm('Are you sure you want to delete this deal? This action cannot be undone.')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload(); // refresh to update list
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Something went wrong while deleting the deal.');
                }
            });
        }
    });
});

   });
</script>
@endsection