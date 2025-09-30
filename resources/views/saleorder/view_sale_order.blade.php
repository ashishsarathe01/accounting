@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
    table{
      width:100%;
      /* padding:10px; */
      border-spacing: 0;
      border:1px solid #dadada;
   }
   table tr th, table tr td{
      border:1px solid #dadada;
      margin: 0;
      padding: 2px 15px;
   }
   .text-right{
      text-align: right;
   }
   .text-left{
      text-align: left;
   }
   p{
      margin:5px 0px; 
   }
   h1, h2, h3, h4, h5, h6{
      margin: 5px 0px;
   }
     .bil_logo{
      width: 70px;
      height: 70px;
      overflow: hidden;
      position: absolute;
      /* left: 10px; */
      /* top: 10px; */
      margin-top: 10px;
      margin-left: 10px;
   }
   .bil_logo img{
      max-width:100%;
   }
    @media print{
      .noprint{
         display:none;
      }
   }
   @page { 
   size: A4;        /* Always A4 size page (210mm x 297mm) */
   margin: 5mm;     /* Outer margin around content */
}

.importantRule { 
   display: none !important;  /* Force hide anything with this class */
}
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 header-section">
               <h5 class="transaction-table-title m-0 py-2"></h5>
               <button class="btn btn-sm btn-primary" onclick="printpage();"><i class="fa fa-print"></i> Print</button>
               <button class="btn btn-sm btn-primary" onclick="window.history.back();"><i class="fa fa-arrow-left"></i> Back</button>
               
               
               
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table id="heading" style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
   <tbody><tr>
      <th colspan="8">
         <div class="bil_logo">
            @if($configuration && $configuration->company_logo_status==1 && $configuration->company_logo && !empty($configuration->company_logo))

            @endif
           {{-- <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}" 
         alt="My Logo" 
         style="max-height: 100%; max-width: 100%; object-fit: contain; display: block;"> --}}
         </div>
         <div style="clear:both"></div>
         <h2 style="margin-top:0;" class="text-center">{{$company_data->company_name}}</h2>
         <h4 style="margin:0px" class="text-center">Sale Order</h4>
         <p>&nbsp;</p>                        
      </th>
   </tr>
   <tr class="rowFont">
      <td colspan="4">
         <p><span class="width25">ACCOUNT NAME</span> :  <span class="lft_mar151">{{$saleOrder->billTo->account_name}}</span></p>
         <p>&nbsp;</p>
      </td>
      <td colspan="4">
         <p><span class="width25">Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($saleOrder->created_at))}}</span> </p>
         <p>&nbsp;</p>
         {{-- <p><span class="width25">PO NO</span>: <span class="lft_mar15">SHUKLA09264</span> </p> --}}
      </td>
   </tr>
   <tr class="rowFont">
      <td colspan="4">
         <i><p><strong>Bill to :</strong></p></i>
         <p>
            {{$saleOrder->billTo->account_name}}<br>{{$saleOrder->billTo->address}}              
         </p>
         <p>GSTIN / UIN : {{$saleOrder->billTo->gstin}}                  
         </p>
               </td>
      <td colspan="4">
         <i><p><strong>Shipp to :</strong></p></i>
         <p>
            {{$saleOrder->shippTo->account_name}}<br>{{$saleOrder->billTo->address}}</p>
         <p>GSTIN / UIN : 
            {{$saleOrder->shippTo->gstin}}                  
         </p>
         
      </td>
        
        @foreach ($saleOrder->items as $item) 
            </tr>
                <tr style="height: 9px;background-color:#BF360C;">
                <td></td>
                <td style="text-align:left"></td>
                <th></th>
                <th></th>
                <th style="text-align:left;"></th>
                <th style="text-align:left"></th>
                <th style="text-align:right"></th>
                <th style="text-align:right"></th>
            </tr>
            <tr class="rowFont">
                <th>ITEM</th>
                <td>{{$item->item->name}}</td>
                <th>RATE</th>
                <td>{{$item->price}}</td>         
                <th>BILLING RATE</th>
                <td>@empty(!$item->bill_price) {{$item->bill_price}}  @else {{$item->price}} @endif</td>
                <td colspan="2"></td>
            </tr>
            <tr class="rowFont">
                <th>Unit</th>
                <td>{{$item->unitMaster->s_name}}</td>
                <th>FREIGHT</th>
                <td>@if($saleOrder->freight==1) Yes  @else No @endif</td>
                <td ></td>
                <td ></td>
                <td ></td>
            </tr>
            <tr style="height: 9px;background-color: #3E2723;">
                <td colspan="8"></td>
            </tr>
            {{-- GSM headers --}}
            <tr class="rowFont fw-bold">                
                @foreach ($item->gsms as $gsm)
                    <th colspan="2" class="text-center">{{$gsm->gsm}} GSM</th>
                @endforeach
                @php $gam_count = $item->gsms->count(); @endphp
                @while ($gam_count < 4)
                    <th colspan="2" class="text-center"></th>
                    @php $gam_count++; @endphp
                @endwhile
                
                
            </tr>
            {{-- Sub-headers: Size + KG --}}
            <tr class="rowFont fw-bold">
                @foreach ($item->gsms as $gsm)
                    <th>Size ({{$item->sub_unit}})</th>
                    <th>{{$item->unitMaster->s_name}}</th>
                @endforeach
                @php $gam_count = $item->gsms->count(); @endphp
                @while ($gam_count < 4)
                    <th colspan="2" class="text-center"></th>
                    @php $gam_count++; @endphp
                @endwhile
            </tr>
            {{-- Max rows = GSM with the most details --}}
            @php
                $maxRows = $item->gsms->map(fn($g) => $g->details->count())->max();
                
            @endphp
            @for ($i = 0; $i < $maxRows; $i++)
                <tr class="rowFont">
                    @foreach ($item->gsms as $gsm)
                        <td>{{ $gsm->details[$i]->size ?? '' }}</td>
                        <td>{{ $gsm->details[$i]->quantity ?? '' }}</td>
                    @endforeach
                     @php $gam_count = $item->gsms->count(); @endphp
                @while ($gam_count < 4)
                    <th colspan="2" class="text-center"></th>
                    @php $gam_count++; @endphp
                @endwhile
                </tr>
            @endfor
            <tr class="rowFont fw-bold">
                @foreach ($item->gsms as $gsm)
                    @php                   
                        $totalQuantity =  $gsm->details->sum('quantity');
                    @endphp
                    <th>Total</th>
                    <th>{{$totalQuantity}}</th>
                @endforeach
                 @php $gam_count = $item->gsms->count(); @endphp
                @while ($gam_count < 4)
                    <th colspan="2" class="text-center"></th>
                    @php $gam_count++; @endphp
                @endwhile
            </tr>
        @endforeach
        <tr style="background-color: #BBDEFB;" class="rowFont">
            <th colspan="2">GRAND TOTAL</th>
            <th colspan="7" style="text-align:left;">{{$item->gsms->flatMap(fn($gsm) => $gsm->details)->sum('quantity')}} {{$item->unitMaster->s_name}}</th>
        </tr>
   <tr class="rowFont">
      <td colspan="4">
        @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)
         <h4>Terms &amp; Conditions</h4>
         @php $i = 1; @endphp
        @foreach($configuration->terms as $k => $t)
            <p style="margin: 0; line-height: 1;"><small>{{$i}}. {{$t->term}}</small></p>
            @php $i++; @endphp
        @endforeach
         @endif
      </td>
      <td colspan="4">
         <br><br>
         <p style="text-align:right"><strong>Checked By</strong></p>
         <hr style="border-top: 1px solid #000000">
         <br>
      <p style="text-align:right"><strong>Authorised By</strong></p>
       </td>
   </tr>
   <tr>
      <td colspan="3" width="33%">
         <h5>Prepared By : {{$saleOrder->orderCreatedBy->name}}</h5>
      </td>
      <td colspan="3" width="33%">
         <h5>Confirmed By : </h5>
      </td>
      <td colspan="3" width="33%">
         <h5>Received By : </h5>
      </td>
   </tr>
</tbody></table>
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
   
      function printpage(){
      $('.header-section').addClass('importantRule');
      $('.sidebar').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
      $('.sidebar').removeClass('importantRule');
   }

      
</script>
@endsection