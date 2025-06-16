@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
           
            
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2">List of Stock Journal</h5>
               <form class="" id="frm" method="get" action="{{ route('stock-journal') }}">
                  @csrf
                  <div class="d-md-flex d-block">
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{!empty($from_date) ? date('Y-m-d', strtotime($from_date)) : ''}}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{!empty($to_date) ? date('Y-m-d', strtotime($to_date)) : ''}}">
                     </div>
                     <div class="calender-administrator ms-md-2">
                        <button type="submit" class="btn btn-info next">Next</button>
                     </div>
                  </div>
               </form>
               <div class="d-md-flex d-block"> 
                       <input type="text" id="search" class="form-control" placeholder="Search">
                    </div>
                    @can('action-module',86)
                       <a href="{{ route('add-stock-journal') }}" class="btn btn-xs-primary">ADD
                           <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                              <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                           </svg>
                        </a>
                    @endcan
               
            </div>
            <div class="bg-white table-view shadow-sm" style="overflow: scroll;">
               <table class="table-striped table m-0 table-bordered shadow-sm stock_journal_table" >
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Item Details</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Qty. Generated</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Price</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Qty. Consumed</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Price</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                     </tr>
                  </thead>
                  <tbody>       
                     @php 
                        $parent_arr = []; 
                        $generated_weight_total = 0;
                        $generated_amount_total = 0;
                        $consumed_weight_total = 0;
                        $consumed_amount_total = 0;
                     @endphp              
                     @foreach($journals as $journal)
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120">
                              @php 
                                 if(!in_array($journal->id,$parent_arr)){
                                    echo date('d-m-Y',strtotime($journal->journal_date));
                                 }
                              @endphp                              
                           </td>
                           <td class="w-min-120">
                              @if($journal->name!='')
                                 {{$journal->name}}
                              @else
                                 {{$journal->new_item}}
                              @endif
                           </td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->new_weight}}</td>
                           <td class="w-min-120" style="text-align:right;">
                              @if($journal->new_item!='')
                                 Kgs.                              
                              @endif
                           </td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->new_price}}</td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->new_amount}}</td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->consume_weight}}</td>
                           <td class="w-min-120" style="text-align:right;">
                              @if($journal->name!='')
                                 Kgs.                              
                              @endif
                           </td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->consume_price}}</td>
                           <td class="w-min-120" style="text-align:right;">{{$journal->consume_amount}}</td>
                           <td>
                              <?php 
                              if(in_array(date('Y-m',strtotime($journal->journal_date)),$month_arr)){
                                
                                    if(!in_array($journal->id,$parent_arr)){?>
                                       
                                 @can('action-module',63)
                                    <a href="{{ URL::to('edit-stock-journal/' . $journal->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                 @endcan
                                 @can('action-module',64)
                                    <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $journal->id;?>">
                                       <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                    </button>
                                 @endcan
                                 <?php 
                              }
                              }?>
                           </td>
                        </tr>
                        @php 
                           array_push($parent_arr,$journal->id);
                           $generated_weight_total = $generated_weight_total + $journal->new_weight;
                           $generated_amount_total = $generated_amount_total + $journal->new_amount;
                           $consumed_weight_total = $consumed_weight_total + $journal->consume_weight;
                           $consumed_amount_total = $consumed_amount_total + $journal->consume_amount;
                        @endphp
                     @endforeach
                     <tr>
                        <th></th>
                        <th></th>
                        <th style="text-align:right;">{{number_format($generated_weight_total,2)}}</th>
                        <th></th>
                        <th></th>
                        <th style="text-align:right;">{{number_format($generated_amount_total,2)}}</th>
                        <th style="text-align:right;">{{number_format($consumed_weight_total,2)}}</th>
                        <th></th>
                        <th></th>
                        <th style="text-align:right;">{{number_format($consumed_amount_total,2)}}</th>
                        <th></th>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="delete_journal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('delete-stock-journal') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this processcannot be undone. </p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <input type="hidden" name="del_id" id="del_id">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(".cancel").click(function() {
      $("#delete_journal").modal("hide");
   });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#del_id").val(id);
      $("#delete_journal").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".stock_journal_table tr").each(function (index) {
         if (!index) return;
         $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
         });
      });
   });
</script>
@endsection