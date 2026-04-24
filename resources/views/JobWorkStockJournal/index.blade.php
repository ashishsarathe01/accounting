@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

@if (session('success'))
   <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
   <h5 class="m-0">Job Work Stock Journal</h5>

   <form method="GET" action="{{ route('jobwork.stockjournal.index') }}">
      <div class="d-flex">
         <input type="date" class="form-control"
            name="from_date"
            value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">

         <input type="date" class="form-control ms-2"
            name="to_date"
            value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">

         <button class="btn btn-info ms-2">Search</button>
      </div>
   </form>

   <div>
      <input type="text" id="search" class="form-control" placeholder="Search">
   </div>

   <a href="{{ route('jobwork.stockjournal.create') }}" class="btn btn-xs-primary">
      ADD
   </a>
</div>

<div class="transaction-table bg-white shadow-sm">
<table class="table sale_table">
<thead>
<tr class="bg-light-pink">
   <th>Date</th>
   <th>Voucher No</th>
   <th>Party Name</th>
   <th class="text-end">Amount</th>
   <th class="text-center">Action</th>
</tr>
</thead>
<tbody>

@foreach($journals as $row)
<tr>
   <td>{{ date('d-m-Y', strtotime($row->date)) }}</td>
   <td class="text-center">{{ $row->voucher_no }}</td>
   <td>{{ $row->account_name }}</td>
   <td class="text-end">{{ number_format($row->total, 2) }}</td>
   <td class="text-center">
      <a href="{{ route('jobwork.stockjournal.edit', $row->id) }}">
         <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit">
      </a>

      {{-- VIEW (optional later) --}}
      {{-- 
      <a href="{{ route('jobwork.stockjournal.view', $row->id) }}" target="_blank">
         <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1">
      </a>
      --}}
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
$("#search").keyup(function () {
   var value = this.value.toLowerCase();
   $(".sale_table tr").each(function (index) {
      if (!index) return;
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
   });
});
</script>

@endsection
