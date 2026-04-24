@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
   /* Force table to fit container */
.stock_journal_table {
    table-layout: fixed;
    width: 100%;
}

/* Compact columns */
.col-date {
    width: 90px;
    white-space: nowrap;
}

.col-unit {
    width: 60px;
    white-space: nowrap;
}

.col-price,
.col-amount {
    width: 90px;
    white-space: nowrap;
    text-align: right;
}

/* Item details can wrap */
.col-item {
    width: 250px;
    /*width: auto;*/
    word-wrap: break-word;
}
/* Highlight first row of each production entry */
.first-row {
    background-color: #fff3cd !important; /* light highlight */
    font-weight: 600;
}

</style>

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}} </div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">
                  List of Production Entry
               </h5>
               <form method="GET" action="{{ route('account-production.index') }}">
    <div class="d-md-flex d-block">

        <div class="calender-administrator my-2 my-md-0">
            <input type="date" name="from_date" class="form-control"
                value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
        </div>

        <div class="calender-administrator ms-md-4">
            <input type="date" name="to_date" class="form-control"
                value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
        </div>

        <div class="ms-md-2 d-flex gap-2">

            <!-- Normal Filter -->
            <button type="submit" class="btn btn-info">
                Next
            </button>

            <!-- Export CSV -->
            <button type="submit"
                formaction="{{ route('account.production.export.csv') }}"
                formmethod="GET"
                class="btn btn-success">
                Export CSV
            </button>

        </div>
    </div>
</form>
                  <!--<a href="{{ route('account-production.create') }}" class="btn btn-xs-primary">ADD-->
                  <!--   <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">-->
                  <!--      <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />-->
                  <!--   </svg>-->
                  <!--</a>-->
               
               
            </div>
            <div class="bg-white table-view shadow-sm">
               <table class="table-striped table m-0 table-bordered shadow-sm stock_journal_table" >
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="col-date">Date</th>
                        <th class="col-date">Voucher No.</th>
                        <th class="col-item">Item Details</th>
                        <th style="text-align:right;">Qty. Generated</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-price" style="text-align:right;">Price</th>
                        <th class="col-amount" style="text-align:right;">Amount</th>
                        <th style="text-align:right;">Qty. Consumed</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-price" style="text-align:right;">Price</th>
                        <th class="col-amount" style="text-align:right;">Amount</th>
                        <th style="width:92px"> Action</th>
                     </tr>
                  </thead>
                  <tbody>

@php
    $currentParent = null;

    // Per entry totals
    $genQty = $genAmt = $conQty = $conAmt = 0;

    // Overall totals
    $overallGenQty = $overallGenAmt = 0;
    $overallConQty = $overallConAmt = 0;
@endphp

@foreach($journals as $journal)

    @php
        $isNewParent = $currentParent !== $journal->id;

        // Print previous entry total
        if ($isNewParent && $currentParent !== null) {
    @endphp
        <tr class="bg-light fw-bold">
            <td colspan="3" class="text-end">Entry Total</td>
            <td class="text-end">{{ formatIndianNumber($genQty) }}</td>
            <td></td>
            <td></td>
            <td class="text-end">{{ formatIndianNumber($genAmt) }}</td>
            <td class="text-end">{{ formatIndianNumber($conQty) }}</td>
            <td></td>
            <td></td>
            <td class="text-end">{{ formatIndianNumber($conAmt) }}</td>
            <td></td>
        </tr>
    @php
            // Reset per entry totals
            $genQty = $genAmt = $conQty = $conAmt = 0;
        }
    @endphp

    {{-- Data Row --}}
    <tr class="font-14 font-heading {{ $isNewParent ? 'first-row' : '' }}">
        <td>{{ $isNewParent ? date('d-m-Y', strtotime($journal->production_date)) : '' }}</td>
        <td>{{ $isNewParent ? $journal->voucher_no_prefix : '' }}</td>
        <td>{{ $journal->name != '' ? $journal->name : $journal->new_item }}</td>

        <td class="text-end">{{ $journal->new_weight }}</td>
        <td>{{ $journal->new_item != '' ? $journal->new_unit : '' }}</td>
        <td class="text-end">{{ $journal->new_price }}</td>
        <td class="text-end">{{ $journal->new_amount }}</td>

        <td class="text-end">{{ $journal->consume_weight }}</td>
        <td>{{ $journal->name != '' ? $journal->s_name : '' }}</td>
        <td class="text-end">{{ $journal->consume_price }}</td>
        <td class="text-end">{{ $journal->consume_amount }}</td>

        <td>
            @if(in_array(date('Y-m',strtotime($journal->production_date)),$month_arr) && $isNewParent)
                <a href="{{ route('account-production.edit',$journal->id) }}">
                    <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1">
                </a>
                <button type="button" class="border-0 bg-transparent delete" data-id="{{ $journal->id }}">
                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1">
                </button>
            @endif
        </td>
    </tr>

    @php
        // Add per entry totals
        $genQty += $journal->new_weight;
        $genAmt += $journal->new_amount;
        $conQty += $journal->consume_weight;
        $conAmt += $journal->consume_amount;

        // Add overall totals
        $overallGenQty += $journal->new_weight;
        $overallGenAmt += $journal->new_amount;
        $overallConQty += $journal->consume_weight;
        $overallConAmt += $journal->consume_amount;

        $currentParent = $journal->id;
    @endphp

@endforeach

{{-- Last entry total --}}
@if($currentParent !== null)
<tr class="bg-light fw-bold">
    <td colspan="3" class="text-end">Entry Total</td>
    <td class="text-end">{{ formatIndianNumber($genQty) }}</td>
    <td></td>
    <td></td>
    <td class="text-end">{{ formatIndianNumber($genAmt) }}</td>
    <td class="text-end">{{ formatIndianNumber($conQty) }}</td>
    <td></td>
    <td></td>
    <td class="text-end">{{ formatIndianNumber($conAmt) }}</td>
    <td></td>
</tr>
@endif

{{-- OVERALL TOTAL --}}
<tr class="bg-warning fw-bold">
    <td colspan="3" class="text-end">Overall Total</td>
    <td class="text-end">{{ formatIndianNumber($overallGenQty) }}</td>
    <td></td>
    <td></td>
    <td class="text-end">{{ formatIndianNumber($overallGenAmt) }}</td>
    <td class="text-end">{{ formatIndianNumber($overallConQty) }}</td>
    <td></td>
    <td></td>
    <td class="text-end">{{ formatIndianNumber($overallConAmt) }}</td>
    <td></td>
</tr>

</tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="delete_journal">
   <div class="modal-dialog">
      <form method="POST" class="delete_id">
         @csrf
         @method('DELETE')

         <div class="modal-content">
            <div class="modal-body">
               Are you sure you want to delete this production entry?
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-danger">Delete</button>
            </div>
         </div>
      </form>
   </div>
</div>

</body>
@include('layouts.footer')
<script>
   $(".cancel").click(function() {
      $("#delete_journal").modal("hide");
   });
   $(document).on('click','.delete',function(){
      let id = $(this).data('id');
      $('.delete_id').attr('action', '{{ url("account-production") }}/' + id);
      $('#delete_journal').modal('show');
   });





</script>
@endsection