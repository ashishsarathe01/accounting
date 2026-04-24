@extends('layouts.app')
@section('content')
<style>
.group-row:hover {
    background-color: #f2f2f2;
}
.item-row:hover {
    background-color: #fafafa;
}
/* ===== Sticky Header for Item Ledger ===== */

.table-scroll-wrapper {
    max-height: 70vh;
    overflow-y: auto;
}

#acc_table1 thead th {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #f8f9fa;   /* match your bg-light-pink */
}
@media print {

    body * {
        visibility: hidden;
    }

    #printArea, #printArea * {
        visibility: visible;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .table-scroll-wrapper {
        max-height: none !important;
        overflow: visible !important;
    }

    /* remove sticky header (causes cut in print) */
    #acc_table1 thead th {
        position: static !important;
    }

    /* Hide unwanted UI */
    form, .btn, .select2, .leftnav, header, footer {
        display: none !important;
    }

    table {
        font-size: 12px;
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 6px !important;
        border: 1px solid #000;
    }

    thead {
        display: table-header-group; /* repeat header on each page */
    }

    tr {
        page-break-inside: avoid;
    }
}
.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-box {
    display: flex;
    flex-direction: column;
    min-width: 140px;
}

.filter-box label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
}

.filter-box .form-control,
.filter-box .form-select {
    height: 34px;
}

.radio-line {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 34px;
}

.radio-line label {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0;
    font-weight: 500;
}

.filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
</style>

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
               
               <form id="frm" method="GET" action="{{ route('itemledger.filter') }}">
                  @csrf

                  <div class="filter-row">

                     <!-- Show Report -->
                     <div class="filter-box">
                           <label>Show</label>

                           <div class="radio-line">
                              <label><input type="radio" name="show_type" value="all"
                                 {{ request('show_type','all') == 'all' ? 'checked' : '' }}> All Items</label>

                              <label><input type="radio" name="show_type" value="item"
                                 {{ request('show_type') == 'item' ? 'checked' : '' }}> Item</label>

                              <label><input type="radio" name="show_type" value="all_groups"
                                 {{ request('show_type') == 'all_groups' ? 'checked' : '' }}>All Groups</label>

                              <label><input type="radio" name="show_type" value="group"
                                 {{ request('show_type') == 'group' ? 'checked' : '' }}> Group</label>
                           </div>
                     </div>

                     <!-- Item -->
                     <div id="itemDiv" class="filter-box"
                           style="display: {{ request('show_type') == 'item' ? 'block' : 'none' }};">
                           <label>Item</label>
                           <select name="item_id" class="form-select select2-single">
                              <option value="">Select</option>
                              @foreach($item_list as $value)
                                 <option value="{{ $value->id }}"
                                       {{ request('item_id') == $value->id ? 'selected' : '' }}>
                                       {{ $value->name }}
                                 </option>
                              @endforeach
                           </select>
                     </div>

                     <!-- Group -->
                     <div id="groupDiv" class="filter-box"
                           style="display: {{ request('show_type') == 'group' ? 'block' : 'none' }};">
                           <label>Group</label>
                           <select name="group_id" class="form-select select2-single">
                              <option value="">Select</option>
                              @foreach($group_list as $grp)
                                 <option value="{{ $grp->id }}"
                                       {{ request('group_id') == $grp->id ? 'selected' : '' }}>
                                       {{ $grp->group_name }}
                                 </option>
                              @endforeach
                           </select>
                     </div>

                     <!-- Series -->
                     <div class="filter-box">
                           <label>Series</label>
                           <select class="form-select select2-single" id="selected_series" name="selected_series">
                              <option value="all">All</option>
                              @foreach($series as $value)
                                 <option value="{{ $value->series }}"
                                       {{ request('selected_series') == $value->series ? 'selected' : '' }}>
                                       {{ $value->series }}
                                 </option>
                              @endforeach
                           </select>
                     </div>

                     <!-- From -->
                     <div class="filter-box from_date_div">
                           <label>From</label>
                           <input type="date" id="from_date" class="form-control"
                              name="from_date"
                              value="{{ request('from_date', $fdate) }}">
                     </div>

                     <!-- To -->
                     <div class="filter-box">
                           <label>To</label>
                           <input type="date" id="to_date" class="form-control"
                              name="to_date"
                              value="{{ request('to_date', $tdate) }}">
                     </div>

                     <!-- Buttons -->
                     <div class="filter-actions">
                           <button type="button" class="btn btn-primary" id="serachBtn">Apply</button>
                           <button type="button" onclick="printLedger()" class="btn btn-success">Print</button>

                           <a href="{{ url('item-ledger-main-csv?items_id='.request()->items_id.'&selected_series='.request()->selected_series.'&from_date='.request()->from_date.'&to_date='.request()->to_date) }}"
                              class="btn btn-dark">
                              CSV
                           </a>
                     </div>

                  </div>
               </form>
                  
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Items Ledger</h5>               
               <span class="ms-auto font-14 fw-bold font-heading">
                   Opening Bal. : {{formatIndianNumber(abs($opening))}} @if($opening<0)  @else  @endif
               </span>
            </div>
            <div id="printArea">
            <div class="display-sale-month bg-white table-view shadow-sm table-scroll-wrapper">
               <table id="acc_table1" class="table-striped table-bordered table m-0 shadow-sm ">                  
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        @if(isset($item_id) && ($item_id=='all' || $item_id=='all_groups'))
                           <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Type</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Amount</th>
                        @else
                           <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Type</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: center;">Vch/Bill No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Particulars</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. In</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. Out</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Balance</th>
                        @endif                        
                     </tr>
                  </thead>
                  
                  <tbody>
                     @php
                     $tot_blance = $opening;
                     $qty = 0;
                     $in = 0;
                     $out = 0;
                     @endphp

                     {{-- ===================== ALL GROUPS ===================== --}}
                     @if(isset($item_id) && $item_id === 'all_groups')

                        @foreach($groups as $grp)
                           @if($grp['qty']==0 && $grp['amount']==0)
                              @continue
                           @endif
                           {{-- GROUP ROW (NO REDIRECT) --}}
                           <tr class="font-14 fw-bold bg-light group-row"
                        data-group-id="{{ $grp['group_id'] }}"
                        data-expanded="0"
                        style="cursor:pointer;">
                                 <td>{{ $grp['group_name'] }}</td>
                                 <td>Group</td>
                                 <td class="text-end">{{ formatIndianNumber($grp['qty']) }}</td>
                                 <td></td>
                                 <td class="text-end">{{ formatIndianNumber($grp['amount']) }}</td>
                           </tr>

                           @php
                                 $qty += $grp['qty'];
                                 $tot_blance += $grp['amount'];
                           @endphp

                           {{-- ITEM ROWS (REDIRECT ENABLED) --}}
                           @foreach($grp['items'] as $item)
                                 <tr class="font-14 bg-white redirect_average_page item-row d-none"
                        data-group-id="{{ $grp['group_id'] }}"
                        data-item_id="{{ $item['item_id'] }}"
                        data-from_date="{{ $fdate }}"
                        data-to_date="{{ request('to_date') }}"
                        style="cursor:pointer;">
                                    <td class="ps-4 text-muted">↳ {{ $item['item_name'] }}</td>
                                    <td>Item</td>
                                    <td class="text-end">{{ formatIndianNumber($item['average_weight']) }}</td>
                                    <td>{{ $item['unit_name'] }}</td>
                                    <td class="text-end">{{ formatIndianNumber($item['amount']) }}</td>
                                 </tr>
                           @endforeach

                        @endforeach

                     {{-- ===================== ALL ITEMS ===================== --}}
                     @elseif(isset($item_id) && $item_id === 'all')

                        @foreach($items as $value)
                           <tr class="font-14 bg-white redirect_average_page"
                                 data-item_id="{{ $value['item_id'] }}"
                                 data-from_date="{{ $fdate }}"
                                 data-to_date="{{ request('to_date') }}"
                                 style="cursor:pointer;">
                                 <td>{{ $value['item_name'] }}</td>
                                 <td>Item</td>
                                 <td class="text-end">{{ formatIndianNumber($value['average_weight']) }}</td>
                                 <td>{{ $value['unit_name'] }}</td>
                                 <td class="text-end">{{ formatIndianNumber($value['amount']) }}</td>
                           </tr>

                           @php
                                 $qty += $value['average_weight'];
                                 $tot_blance += $value['amount'];
                           @endphp
                        @endforeach

                     {{-- ===================== SINGLE ITEM LEDGER ===================== --}}
                     @else

                        @foreach($items as $value)
                           @php
                                 $inWeight = $value['in_weight'] ?? 0;
                                 $outWeight = $value['out_weight'] ?? 0;
                                 $in += $inWeight;
                                 $out += $outWeight;
                                 $tot_blance += ($inWeight - $outWeight);
                                 $red = $tot_blance < 0 ? 'color:red;' : '';
                           @endphp
                           <tr class="font-14 bg-white account_tr"
                                 data-type="{{ $value['source'] }}"
                                 data-id="{{ $value['source_id'] }}"
                                 data-einvoice_status="{{ $value['einvoice_status'] }}"
                                 style="cursor:pointer;">
                                 <td style="{{ $red }}">{{ date('d-m-Y', strtotime($value['txn_date'])) }}</td>
                                 <td style="{{ $red }}">{{ $value['type'] }}</td>
                                 <td style="text-align:center;{{ $red }}">{{ $value['bill_no'] }}</td>
                                 <td style="{{ $red }}">{{ $value['account_name'] }}</td>
                                 <td class="text-end" style="{{ $red }}">{{ formatIndianNumber($inWeight) }}</td>
                                 <td class="text-end" style="{{ $red }}">{{ formatIndianNumber($outWeight) }}</td>
                                 <td class="text-end" style="{{ $red }}">{{ formatIndianNumber($tot_blance) }}</td>
                           </tr>
                        @endforeach

                     @endif
                  </tbody>

                  <?php 
                  if(isset($item_id) && !in_array($item_id, ['all','all_groups'])){?>
                     <div>
                        <tr class=" font-14 font-heading bg-white">
                           <td class="w-min-120" colspan="4"></td>
                           <td class="w-min-120 fw-bold" style="text-align: right;">
                              <?php 
                              echo formatIndianNumber($in);?>
                           </td>
                           <td class="w-min-120 fw-bold" style="text-align: right;">
                              <?php 
                              echo formatIndianNumber($out);                             
                              ?>
                           </td>
                           <td></td>
                        </tr>
                     </div>
                     <?php 
                  } ?>
                  <div>
                  <?php 
                  if(isset($item_id) && !in_array($item_id, ['all','all_groups'])){?>
                     <tr class="font-14 fw-bold font-heading">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="text-align: right;">Closing Bal</th>
                        <th class="text-end" style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($tot_blance);                         
                           ?>
                        </th>
                        
                     </tr>
                     
                     <?php 
                  }else{ ?>
                     <tr class="font-14 fw-bold font-heading">
                        <th></th>
                        <th></th>
                        
                        <th style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($qty);
                           ?>                           
                        </th>
                        <th></th>
                        <th class="text-end" style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($tot_blance);                          
                           ?>
                        </th>                        
                     </tr>
                     <?php 
                  }?>
                  
                  </div>
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
   $(document).ready(function() {
      changeItem();
      let params = new URLSearchParams(window.location.search);
       if (
          params.has('items_id') &&
          params.has('from_date') &&
          params.has('to_date') &&
          !params.has('_token')   
       ) {
          $("#frm").submit();
       }
   });
    
   $(document).ready(function() {      
      $("#serachBtn").click(function(){
         getLedger();
      });
      $(".account_tr").click(function(){
         let type = $(this).attr('data-type');
         let id = $(this).attr('data-id');
         let einvoice_status = $(this).attr('data-einvoice_status');
         if(type!='' && id!=''){
            $.ajax({
               url: '{{url("set-redircet-url")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  url: window.location.href
               },
               success: function(data){
                  if(type==1){
                     if(einvoice_status==1){
                        window.location = "sale-invoice/"+id;
                     }else{
                        window.location = "edit-sale/"+id;
                     }
                  }else if(type==2){
                     window.location = "purchase-edit/"+id;
                  }else if(type==3){
                     window.location = "edit-stock-journal/"+id;
                  }
               }
            });
         }
      });   
   });
   $(document).ready(function(){
      function toggleFields(){
         let type = $("input[name='show_type']:checked").val();
         $("#itemDiv").hide();
         $("#groupDiv").hide();
         $(".from_date_div").show();

         if(type === 'item'){
               $("#itemDiv").show();
         }
         else if(type === 'group'){
               $("#groupDiv").show();
         }
         else if(type === 'all' || type === 'all_groups'){
               $(".from_date_div").hide();
         }
      }
      toggleFields();
      $("input[name='show_type']").change(function(){
         toggleFields();
      });
   });
   $("#items_id").change(function(){
      changeItem();
   });
   function changeItem(){
      let val = $("#items_id").val();

      // default
      $(".from_date_div").show();

      // hide date for All Items and All Groups
      if(val === 'all' || val === 'all_groups'){
         $(".from_date_div").hide();
      }
   }

   function getLedger(){
      var id = $("#items_id").val();
      if(id != ''){
         $("#frm").submit();
      }else{
         alert("Please select item.");
      }
   }
   $(".select2-single").select2();
   $(".redirect_average_page").click(function(){
      let item_id = $(this).attr('data-item_id');
      let from_date = $(this).attr('data-from_date');
      let to_date = $(this).attr('data-to_date');
      if($("#selected_series").val()=="all"){
         window.location = "{{url('item-ledger-average-by-godown')}}/?items_id="+item_id+"&from_date="+from_date+"&to_date="+to_date;
      }else{
         window.location = "{{url('item-ledger-average')}}/?items_id="+item_id+"&from_date="+from_date+"&to_date="+to_date+"&series="+$("#selected_series").val();
      }
      
   })
   $(document).on('click', '.group-row', function () {

      let $groupRow = $(this);
      let gid = $groupRow.data('group-id');
      let expanded = $groupRow.data('expanded') || 0;

      let $items = $('.item-row[data-group-id="' + gid + '"]');

      if (expanded === 0) {
         $items.removeClass('d-none');
         $groupRow.data('expanded', 1);
      } else {
         $items.addClass('d-none');
         $groupRow.data('expanded', 0);
      }
   });
function printLedger() {
      window.print();
   }
</script>
@endsection