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
                @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center" style="height: 48px;">
                    @foreach ($errors->all() as $error)
                    <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="height: 48px; display: flex; align-items: center;">
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        List of Sub Item
                    </h5>
                    <a href="{{ route('add-sub-item',$item_id)}}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Quantity </th>                                
                                <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sub_items as $key => $value)
                                <tr>
                                    <td>{{$value->name}}</td>
                                    <td>{{$value->quantity}}</td>
                                    <td></td>
                                </tr>
                            @endforeach
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
    
</script>
@endsection