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
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Item Stock ({{$item->name}})</h5>
            </div>
            <div class="display-sale-month  bg-white table-view shadow-sm">
               <table id="acc_table1" class="table-striped table-bordered table m-0 shadow-sm ">                  
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-120 border-none bg-light-pink text-body">Material Center</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Amount</th>
                     </tr>
                  </thead>
                  <tbody>
                    @php $qty_total = 0; $amount_total = 0; @endphp
                    @foreach ($series as $value)
                        <tr class="redirect_average_page" data-series="{{$value->series}}" data-item_id="{{$_GET['items_id']}}" data-from_date="{{$_GET['from_date']}}" data-to_date="{{$_GET['to_date']}}" style="cursor:pointer">
                            <td>{{$value->series}}</td>
                            <td style="text-align: right;">{{$value->weight}}</td>
                            <td>{{$value->unit}}</td>
                            <td style="text-align: right;">{{$value->price}}</td>
                            <td style="text-align: right;">{{formatIndianNumber($value->amount)}}</td>
                        </tr>
                        @php $qty_total = $qty_total + (float) $value->weight; $amount_total = $amount_total + (float) $value->amount; @endphp
                    @endforeach
                        <tr>
                            <th>Total</th>
                            <th style="text-align: right;">{{formatIndianNumber($qty_total)}}</th>
                            <td></td>
                            <td style="text-align: right;"></td>
                            <th style="text-align: right;">{{formatIndianNumber($amount_total)}}</th>
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
    $(".redirect_average_page").click(function(){
      let item_id = $(this).attr('data-item_id');
      let from_date = $(this).attr('data-from_date');
      let to_date = $(this).attr('data-to_date');
      let series = $(this).attr('data-series');
      window.location = "{{url('item-ledger-average')}}/?items_id="+item_id+"&from_date="+from_date+"&to_date="+to_date+"&series="+series;
      
   })
</script>
@endsection