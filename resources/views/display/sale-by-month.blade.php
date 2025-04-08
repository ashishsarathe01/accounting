@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="{{url('profitloss')}}">Profit & Loss</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Sale By Month</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Sale By Month</h5>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th style="text-align:center;">Month</th>
                        <th style="text-align:right;">Debit(Rs.)</th>
                        <th style="text-align:right;">Credit(Rs.)</th>
                        <th style="text-align:right;">Balance(Rs.)</th>
                     </tr>                     
                  </thead>
                  <tbody>
                     @php $balance = 0; @endphp
                     @foreach($data as $key => $value)
                        <tr class="get_info" data-date="{{$value['date']}}" style="cursor: pointer;">
                           <td style="text-align:center;">{{$value['month']}}</td>
                           <td style="text-align:right;">{{$value['debit']}}</td>
                           <td style="text-align:right;">{{$value['credit']}}</td>
                           <td style="text-align:right;">{{$value['balance']}}</td>
                        </tr>
                         @php $balance = $value['balance']; @endphp
                     @endforeach  
                     <tr>
                        <th style="text-align:center;">Total</th>
                        <th style="text-align:right;">{{$total_debit}}</th>
                        <th style="text-align:right;">{{$total_credit}}</th>
                        <th style="text-align:right;">{{$balance}}</th>
                     </tr>                      
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(".get_info").click(function(){
         window.location = "{{route('sale-by-month-detail')}}/"+$(this).attr('data-date');
      });
   });
</script>
@endsection