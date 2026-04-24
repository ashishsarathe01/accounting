@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style>
/* hide in normal screen */
.print-only {
   display: none;
}

@media print {
   body {
      font-size: 12px;
   }

   /* show only during print */
   .print-only {
      display: block;
   }

   table {
      width: 100%;
      border-collapse: collapse;
   }
   table, th, td {
      border: 1px solid #000;
   }
   th, td {
      padding: 5px;
      text-align: right;
   }
   th:first-child, td:first-child {
      text-align: left;
   }
}
</style>
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
               <button onclick="printReport()" class="btn btn-primary btn-sm">
                  Print
               </button>
               <a href="{{ url('item-stock-csv?items_id='.request()->items_id.'&from_date='.request()->from_date.'&to_date='.request()->to_date) }}" 
                  class="btn btn-success btn-sm">
                  Export CSV
               </a>
            </div>
            <div class="display-sale-month  bg-white table-view shadow-sm">
                <div id="printArea">
                    <div class="text-center mb-3 print-only">
                      <h4>Item Stock Report</h4>
                      <h5>{{$item->name}}</h5>
                      <p>
                         From: {{ request()->from_date }} 
                         To: {{ request()->to_date }}
                      </p>
                    </div>
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
    });
    function printReport() {
        let printContents = document.getElementById('printArea').innerHTML;
        let originalContents = document.body.innerHTML;
    
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    
        location.reload();
    }
</script>
@endsection