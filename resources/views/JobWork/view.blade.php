@extends('layouts.app')
@section('content')
@include('layouts.header')

{{-- SAME EXACT STYLES AS SALE INVOICE --}}
<style type="text/css">
   /* Copy ALL styles from sale invoice - already perfect */
   .dataTables_filter{ float:right; }
   table{ width:100%; border-spacing: 0; border:1px solid #dadada; }
   table tr th, table tr td{ border:1px solid #000000; margin: 0; padding: 2px 5px; }
   hr{ border:1px solid #000000; }
   .text-right{ text-align: right; }
   .text-left{ text-align: left; }
   p{ margin:0px; margin-bottom:0rem !important; }
   .width25{ width:35%; }
   .lft_mar15{ margin-left:15px; }
   .bil_logo{ width: 120px; height: 90px; overflow: hidden; position: absolute; margin-top: 20px; margin-left: 4px; }
   .bil_logo img{ max-width:100%; }
   @media print{ .noprint{ display:none; } }
   @page { size: auto; margin: 0mm; }
   .no-border td {
    border-top: 0 !important;
    border-bottom: 0 !important;
}

.item-end td {
    border-top: 0 !important;
    border-bottom: 1px solid #000 !important;
}
@media print {

   /* Hide everything */
   body * {
      visibility: hidden;
   }

   /* Show only print area */
   .print-area,
   .print-area * {
      visibility: visible;
   }

   /* Position table at top-left */
   .print-area {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
   }

   /* Extra safety */
   .noprint {
      display: none !important;
   }
}

</style>

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
            
            {{-- BUTTONS --}}
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section noprint">
               <div class="calender-administrator my-2 my-md-0">
               @if($type == 'raw')
               <button type="button" class="btn btn-danger"
                     onclick="window.location='{{ route('jobwork.out.raw') }}'">
                  QUIT
               </button>
               @else
               <button type="button" class="btn btn-danger"
                     onclick="window.location='{{ route('jobwork.out.finished') }}'">
                  QUIT
               </button>
               @endif                  
               <button class="btn btn-info" onclick="window.print();">Print</button>
                  
               </div>
            </div>
<div class="print-area">
            <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
               <tbody>
                  <tr>
                     <th colspan="8" style="padding: 0;">
                        <div style="min-height: 120px; position: relative;">
                           <div style="width:auto; float:left; text-align:left;">
                              <strong>GSTIN: {{ $seller_info->gst_no }}</strong>
                           </div>

                           @if($configuration && $configuration->company_logo_status==1 && !empty($configuration->company_logo))
                           <div class="bil_logo" style="float: left; margin-left: 10px;">
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}" 
                                   alt="Logo" 
                                   style="max-height: 100%; max-width: 100%; object-fit: contain; display: block;">
                           </div>
                           @endif

                           <div style="width:auto; float:right; text-align:right;">
                              <strong>PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
                              <small>O/D/T</small>
                           </div>

                           <div style="clear:both;"></div>

                           @php
                              $companyName = $company_data->company_name;
                              $fontSize = strlen($companyName) > 30 ? '18px' : '24px';
                           @endphp

                           <div style="text-align:center; line-height:1; margin:0; padding:0;">
                              <p style="margin:0;"><u>Delivery Challan</u></p>
                              <p style="margin:0; font-size: {{ $fontSize }}; font-weight: bold;">{{ $companyName }}</p>
                              <p style="margin:0;">
                                 <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;">
                                    {{ $seller_info->address }}
                                 </small>
                              </p>
                              <p style="margin:0;">
                                 <small style="font-size: 12px;">Phone: {{ $company_data->mobile_no }} Email: {{ $company_data->email_id }}</small>
                              </p>
                           </div>
                        </div>
                     </th>
                  </tr>

                  {{-- INVOICE DETAILS - SAME LAYOUT --}}
                  <tr>
                     <td colspan="4">
                        <p><span class="width25">Delivery No. </span>: {{ $jobwork->voucher_no_prefix ?? $jobwork->voucher_no ?? '' }}</p>
                        <p><span class="width25">Date of Invoice </span>: {{ date('d-m-Y', strtotime($jobwork->date)) }}</p>
                        <p><span class="width25">Place of Supply </span>: {{ $jobwork->party->state_code ?? '' }}</p>
                        <p><span class="width25">Reverse Charge </span>: {{ $jobwork->reverse_charge ?? 'No' }}</p>
                        <p><span class="width25">GR/RR No. </span>: {{ $jobwork->gr_rr_no ?? '' }}</p>
                     </td>
                     <td colspan="4">
                        <p><span class="width25">Transport </span>: <span class="lft_mar15">{{ $jobwork->transport_name ?? '' }}</span></p>
                        <p><span class="width25">Vehicle No. </span>: {{ $jobwork->vehicle_no ?? '' }}</p>
                        <p><span class="width25">Station </span>: {{ $jobwork->station ?? '' }}</p>
                        <p><span class="width25">E-Way Bill No. </span>: <span>-</span></p>
                        <p>&nbsp;</p>
                     </td>
                  </tr>

                  {{-- BILLED TO / SHIPPED TO - SAME STRUCTURE --}}
                  <tr>
                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;"><strong>Billed to :</strong></p>
                        <div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;">
                              {{ $jobwork->party->account_name ?? '-' }}<br>
                              {{ $jobwork->party->address ?? '' }} {{ $jobwork->party->address2 ?? '' }} {{ $jobwork->party->address3 ?? '' }}<br>

                           </p>
                        </div>
                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;">
                              GSTIN/UIN:{{ $jobwork->party->gstin ?? '' }} 
                           <span style="float: right;">
                              PAN:{{ $jobwork->party->pan ?? substr($jobwork->party->gstin ?? '', 2, 10) }}
                           </span>
                           </p>
                        </div>
                     </td>

                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        @php
                           $useShipping = $type == 'finished' && !empty($jobwork->job_work_in_id);

                           $hasShipping = $useShipping && (
                              !empty(trim($jobwork->shipping_name)) ||
                              !empty(trim($jobwork->shipping_address)) ||
                              !empty(trim($jobwork->shipping_gst))
                           );

                           $shippingAccount = null;

                           if($hasShipping && is_numeric($jobwork->shipping_name)){
                              $shippingAccount = \App\Models\Accounts::find($jobwork->shipping_name);
                           }
                           $billingState = null;
                           $shippingState = null;

                           if(!empty($jobwork->party->state)){
                              $billingState = \App\Models\State::find($jobwork->party->state);
                           }

                           if($shippingAccount && !empty($shippingAccount->state)){
                              $shippingState = \App\Models\State::find($shippingAccount->state);
                           }
                        @endphp

                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                           <strong>Shipped to :</strong>
                        </p>

                        <div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;">

                              @if($hasShipping && $shippingAccount)
                                    {{ $shippingAccount->account_name ?? '-' }}<br>
                                    {{ $shippingAccount->address ?? '' }} {{ $shippingAccount->address2 ?? '' }} {{ $shippingAccount->address3 ?? '' }}<br>


                              @elseif($hasShipping)
                                    {{ $jobwork->shipping_name ?? '-' }}<br>
                                    {{ $jobwork->shipping_address ?? '' }}

                              @else
                                    {{ $jobwork->party->account_name ?? '-' }}<br>
                                    {{ $jobwork->party->address ?? '' }} {{ $jobwork->party->address2 ?? '' }} {{ $jobwork->party->address3 ?? '' }}<br>

                              @endif

                           </p>
                        </div>

                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;">

                              @if($hasShipping && $shippingAccount)
                                    GSTIN/UIN:{{ $shippingAccount->gstin ?? '' }}
                                    <span style="float: right;">
                                       PAN:{{ $shippingAccount->pan ?? substr($shippingAccount->gstin ?? '', 2, 10) }}
                                    </span>

                              @elseif($hasShipping)
                                    GSTIN/UIN:{{ $jobwork->shipping_gst ?? '' }}
                                    <span style="float: right;">
                                       PAN:{{ substr($jobwork->shipping_gst ?? '', 2, 10) }}
                                    </span>

                              @else
                                    GSTIN/UIN:{{ $jobwork->party->gstin ?? '' }}
                                    <span style="float: right;">
                                       PAN:{{ $jobwork->party->pan ?? substr($jobwork->party->gstin ?? '', 2, 10) }}
                                    </span>
                              @endif

                           </p>
                        </div>
                     </td>
                  </tr>

                  <tr>
                     <th style="width:2%;padding: 0px 3px;">S. No.</th>
                     <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                     <th style="text-align:center; width:3%;">HSN/SAC Code</th>
                     <th style="text-align:right; width:11%;">Qty.</th>
                     <th style="text-align:center; width:2%;">Unit</th>
                     <th style="text-align:right; width:12%;">Price</th>
                     <th style="text-align:right; width:15%;">Amount (₹)</th>
                  </tr>

                  {{-- ✅ MAIN LOOP WITH TOTALS --}}
                  @php 
                     $i = 1; 
                     $item_total = 0; 
                     $qty_total = 0; 
                  @endphp

                  @forelse($jobwork->descriptions ?? [] as $desc)
                  @php 
                     $item_total += floatval($desc->amount ?? 0); 
                     $qty_total  += floatval($desc->qty ?? 0); 
                  @endphp

                  {{-- MAIN ITEM ROW --}}
                  <tr>
                     <td style="text-align:center;">{{ $i }}</td>

                     <td colspan="2" style="text-align:left; padding:2px 5px;">
                        <strong>{{ $desc->item->name ?? $desc->goods_discription }}</strong>
                     </td>

                     <td style="text-align:center;">
                        {{ $desc->item->hsn_code ?? $desc->hsn_code }}
                     </td>

                     <td style="text-align:right;">
                        {{ number_format($desc->qty ?? 0, 0) }}
                     </td>

                     <td style="text-align:center;">
                        {{ $desc->unit }}
                     </td>

                     <td style="text-align:right;">
                        {{ number_format($desc->price ?? 0, 2) }}
                     </td>

                     <td style="text-align:right;">
                        <strong>{{ number_format($desc->amount ?? 0, 2) }}</strong>
                     </td>
                  </tr>

                  {{-- DESCRIPTION ROW --}}
                  @if(!empty($desc->item_description))
                  <tr class="no-border">
                     <td></td>
                     <td colspan="2" style="font-size:11px; font-style:italic; padding-left:10px;">
                        {{ $desc->item_description }}
                     </td>

                     {{-- KEEP GRID --}}
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                  </tr>
                  @endif

                  {{-- SIZE ROWS --}}
                  @if($desc->sizeMaps && $desc->sizeMaps->count())
                  @foreach($desc->sizeMaps as $map)
                  @if($map->sizeStock)
                  <tr class="{{ $loop->last ? 'item-end' : 'no-border' }}">
                     <td></td>
                     <td colspan="2" style="font-size:10px; padding-left:10px; color:#666;">
                        size: {{ $map->sizeStock->size }},
                        weight: {{ $map->sizeStock->weight }},
                        reel: {{ $map->sizeStock->reel_no }}
                     </td>

                     {{-- KEEP GRID --}}
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                  </tr>
                  @endif
                  @endforeach
                  @else
                  <tr class="item-end">
                     <td></td>
                     <td colspan="2"></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                  </tr>
                  @endif

                  @php $i++; @endphp
                  @empty
                  <tr>
                     <td colspan="8" style="text-align:center; padding:20px;">
                        No items found
                     </td>
                  </tr>
                  @endforelse


                  @php $spacerRows = max(0, 12 - ($i - 1) * 3); @endphp
                  @for($s = 0; $s < $spacerRows; $s++)
                     <tr style="height: 15px;"><td>&nbsp;</td><td colspan="2">&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                  @endfor

                  <tr>
                     <td colspan="6" style="border-bottom:0; border-right:0"></td>
                     <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                     <td style="text-align:right; border-bottom:0;"><strong>₹{{ number_format($item_total, 2) }}</strong></td>
                  </tr>

                  <tr>
                     <td colspan="4" style="text-align:right; border-right: 0; border-bottom: 0;">
                        <p style="margin:0;"><strong>Grand Total</strong></p>
                     </td> 
                     <td style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">
                        <p style="margin:0;"><strong>{{ number_format($qty_total, 0) }}</strong></p>
                     </td> 
                     <td colspan="2" style="border-right: 0; border-bottom: 0;border-left: 0;"></td> 
                     <td style="text-align:right; border-left: 0;">
                        <p style="margin:0;"><strong>₹{{ number_format($item_total, 2) }}</strong></p>
                     </td>
                  </tr>

                  <tr>
                     <td colspan="4" style="vertical-align: top; padding: 5px;">
                        @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)
                           <p style="margin: 0;"><small><b>Terms & Conditions</b></small></p>
                           <p style="margin: 0;"><small>E.& O.E.</small></p>
                           @php $i = 1; @endphp
                           @foreach($configuration->terms as $t)
                              <p style="margin: 0; line-height: 1;"><small>{{ $i }}. {{ $t->term }}</small></p>
                              @php $i++; @endphp
                           @endforeach
                        @endif
                     </td>
                     <td colspan="4">
                        <p style="height:40px; margin:0; padding:0;"><small>Receiver's Signature :</small></p>
                        <hr style="margin:0; padding:0;">
                        <p style="text-align:right; padding:0; margin:0;"><strong>for {{ $company_data->company_name }}</strong></p>
                        @if($configuration && $configuration->signature_status == 1 && !empty($configuration->signature))
                           <p style="text-align:right; margin:0; padding:0;">
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->signature }}" style="width: 145px; height:70px;">
                           </p>
                           @else
                           <p style="text-align:right; margin:0; padding:0;width: 145px; height:70px;">
                           </p>
                        @endif
                        <p style="text-align:right; margin:0; padding:0;"><strong>Authorised Signatory</strong></p>
                     </td>
                  </tr>
               </tbody>
            </table>
<div>
         </div>
      </div>
   </section>
</div>

@include('layouts.footer')
@endsection
