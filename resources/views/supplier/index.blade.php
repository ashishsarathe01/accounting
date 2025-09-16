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
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif          
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">List of Supplier</h5>
                    <button class="btn btn-primary btn-sm d-flex align-items-center supplier_bonus" >Supplier Bonus</button>
                    <a href="{{ route('supplier.create') }}" class="btn btn-xs-primary">ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" /></svg>
                    </a>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm">
                    <table class="table-striped table m-0 shadow-sm receipt_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Supplier Name</th>
                        @foreach($locations as $key => $location)
                            <th class="w-min-120 border-none bg-light-pink text-body">{{$location->name}}</th>
                        @endforeach
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($suppliers as $key => $supplier)
                        @php                             
                           $grouped = [];$r_date = "";
                           foreach ($supplier->latestLocationRate->toArray() as $row) {
                              $grouped[$row['location']][] = $row;
                              $r_date = $row['r_date'];
                           }
                           
                        @endphp
                        <tr class="font-14 text-body">
                            <td class="w-min-120 border-none ">
                                <span class="text-body font-12">({{ $supplier->account ? $supplier->account->account_name : '' }})</span>
                            </td>
                            <td>{{date('d-m-Y',strtotime($r_date))}}</td>
                            @foreach($locations as $key => $location)
                                <td class="w-min-120 border-none ">
                                    <table class="table table-borderless m-0">
                                       @php 
                                       if(isset($grouped[$location->id]) && count($grouped[$location->id])>0){ 
                                          foreach($grouped[$location->id] as $v){ @endphp
                                             <tr>
                                                <td class="font-12 text-body">{{$v['name']}}</td>
                                                <td class="font-12 text-body">{{$v['head_rate']}}</td>
                                             </tr>
                                             @php
                                          }
                                       }
                                       @endphp
                                    </table>
                                   
                                        {{-- <div class="font-12 text-body">-</div> --}}
                                    
                                </td>
                            @endforeach
                            <td class="w-min-120 border-none text-center">
                                <a href="{{ URL::to('supplier/'.$supplier->id.'/edit') }}">  <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                 <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $supplier->id;?>">
                                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                 </button>
                            </td>
                    @endforeach                    
                  </tbody>
               </table>
            </div>
         </div>
         <!-- <div class="col-lg-1 d-flex justify-content-center">
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
         </div> -->
      </div>
   </section>
</div>
   <!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="supplierDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="" id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="bonus_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>  
               
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Supplier Bonus</h5>
        <div class="modal-body">
        <div class="row">
            <table class="table table-bordered bonus_tbl">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Bonus</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        </div>
    </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).on("click", ".delete", function(){
      let url = "{{ route('supplier.destroy','id')}}";
      url = url.replace('id', $(this).attr('data-id'));
      $("#deleteForm").attr('action', url);
      $("#supplierDeleteModal").modal('show');
   });
   $(document).on("click", ".cancel", function(){
      $("#supplierDeleteModal").modal('hide');
   });
   $(".supplier_bonus").click(function(){
        $.ajax({
            url:"{{url('get-supplier-bonus')}}",
            type:"POST",
            data:{_token:'{{csrf_token()}}'},
            success:function(res){
                if(res!=""){
                    let arr = [];let html = "";
                    if(res.bonus.length>0){
                        const groupedByCategory = Object.groupBy(res.bonus, product => product.account_name);
                        for (let key in groupedByCategory) {
                            let bonus = "<table class='table table-bordered'>";
                            let account_id = "";
                            groupedByCategory[key].forEach(function(e){
                                bonus+="<tr><td>"+e.name+"</td><td>"+e.bonus+"</td></tr>";
                                account_id = e.account_id;
                            });
                            bonus+="</table>";
                            html+="<tr><td>"+key+"</td><td>"+bonus+"</td></tr>";
                                console.log()
                        }
                    }
                    $(".bonus_tbl tbody").html(html);
                    $("#bonus_modal").modal('toggle');
                }
            }
        });
    });
</script>
@endsection