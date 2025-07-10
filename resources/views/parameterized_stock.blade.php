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
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Parameterized Stock
                    </h5>
                    <form  action="{{ route('parameterized-stock') }}" method="GET">
                        @csrf
                        <div class="d-md-flex d-block">
                            <div class="ms-md-4">
                                <select class="form-select" name="item" style="width: 120%;" required>
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{$item->id}}" @if($item_id && $item_id==$item->id) selected @endif>{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ms-md-4">
                                <select class="form-select" name="series" style="width: 120%;" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($series as $branch)
                                        <option value="{{$branch->series}}" @if($selected_series && $selected_series==$branch->series) selected @endif>{{$branch->series}}</option>
                                    @endforeach
                                    series
                                </select>
                            </div>
                            <div class="calender-administrator ms-md-4">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="@if($to_date && !empty($to_date)){{$to_date}}@else{{date('Y-m-d')}}@endif">
                            </div>
                            <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                        </div>
                    </form>
                    
                    
                </div>
               <div class="transaction-table bg-white table-view shadow-sm purchase_table">
                  <table class="table-bodered table m-0 shadow-sm">
                    
                    @if($parameter_header && count($parameter_header)>0)
                        <thead>
                            <tr>
                                @php $total_header = "Total "; @endphp
                                @foreach($parameter_header as $key => $value)
                                    <th>{{ $value->paremeter_name }}</th>
                                    @if($value->conversion_factor==1)
                                        @php $total_header.=$value->paremeter_name; @endphp
                                    @endif
                                @endforeach
                                <th>{{$total_header}}</th>
                            </tr>                        
                        </thead>
                        <tbody>
                            @foreach($parameter_value as $key => $value) 
                                @php $alternative_value = 0;$conversion_value = 0;@endphp
                               <tr>                                                      
                                    @if($value->parameter1_id!=null)
                                        <td>
                                            {{$value->parameter1_value}} 
                                            @if($value->alternative_unit1==1)
                                                {{$value->paremeter_name1}} 
                                                @php $alternative_value = $value->parameter1_value @endphp
                                            @endif
                                            @if($value->conversion_factor1==1)
                                                {{$item_unit->s_name}}
                                                @php $conversion_value = $value->parameter1_value @endphp
                                            @endif
                                        </td>
                                    @endif
                                    @if($value->parameter2_id!=null)
                                        <td>
                                            {{$value->parameter2_value}}
                                            @if($value->alternative_unit2==1)
                                                {{$value->paremeter_name2}} 
                                                @php $alternative_value = $value->parameter2_value @endphp
                                            @endif
                                            @if($value->conversion_factor2==1)
                                                {{$item_unit->s_name}}  
                                                @php $conversion_value = $value->parameter2_value @endphp
                                            @endif
                                        </td>
                                    @endif
                                    @if($value->parameter3_id!=null)
                                        <td>
                                            {{$value->parameter3_value}}
                                            @if($value->alternative_unit3==1)
                                                {{$value->paremeter_name3}} 
                                                @php $alternative_value = $value->parameter3_value @endphp
                                            @endif
                                            @if($value->conversion_factor3==1)
                                                {{$item_unit->s_name}} 
                                                @php $conversion_value = $value->parameter3_value @endphp
                                            @endif
                                        </td>
                                    @endif
                                    @if($value->parameter4_id!=null)
                                        <td>
                                            {{$value->parameter4_value}}
                                            @if($value->alternative_unit4==1)
                                                {{$value->paremeter_name4}} 
                                                @php $alternative_value = $value->parameter4_value @endphp
                                            @endif
                                            @if($value->conversion_factor4==1)
                                                {{$item_unit->s_name}}  
                                                @php $conversion_value = $value->parameter4_value @endphp
                                            @endif
                                        </td>
                                    @endif
                                    @if($value->parameter5_id!=null)
                                        <td>
                                            {{$value->parameter5_value}}
                                            @if($value->alternative_unit5==1)
                                                {{$value->paremeter_name5}} 
                                                @php $alternative_value = $value->parameter5_value @endphp
                                            @endif
                                            @if($value->conversion_factor5==1)
                                                {{$item_unit->s_name}} 
                                                @php $conversion_value = $value->parameter5_value @endphp
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{$alternative_value*$conversion_value}} {{$item_unit->s_name}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                  </table>
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
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>
   
</script>
@endsection