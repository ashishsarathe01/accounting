@extends('admin-module.layouts.app')
@section('content')
<!-- header-section -->
@include('admin-module.layouts.header')
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')
            <div class="col-md-12 col-lg-10 px-md-4 bg-light">         
                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{session('error')}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{session('success')}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif         
                {{-- Header --}}
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white rounded-top">
                        <h5 class="mb-0">Modules Permission</h5>                        
                    </div>
                    {{-- Modules List --}}
                    <div class="card-body bg-white">                        
                            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('admin.store-merchant-module') }}">
                                @csrf
                                <div class="row">
                                    <div class="mb-4 col-md-4">
                                        <label for="merchant_id" class="form-label font-14 font-heading">Merchant</label>
                                        <select class="form-select form-select-lg merchant select2" name="merchant_id" aria-label="form-select-lg example" required>
                                            <option selected value="">Choose Merchant</option>
                                            @foreach($merchants as $merchant)
                                                <option value="{{$merchant->id}}" @if($merchant->id==$id) selected @endif>
                                                    {{$merchant->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul style="color: red;">
                                            @error('merchant_id') Please Select Merchant @enderror                        
                                        </ul> 
                                    </div>
                                    <div class="clearfix"></div>
                                    
                                        <label for="module" class="form-label font-14 font-heading">Modules</label>
                                        @foreach ($modules as $value)
                                        <div class="mb-4 col-md-4">
                                            <div class="col">
                                                <div class="form-check p-3 border rounded shadow-sm h-100">
                                                    <input class="form-check-input" name="modules[]" type="checkbox" id="module{{$value->id}}" value="{{$value->id}}" @if(in_array($value->id,$selected_modules)) checked @endif style="margin-left: 5px;">
                                                    &nbsp;
                                                    <label class="form-check-label fw-semibold" for="module{{$value->id}}">{{$value->name}}</label>
                                                </div>
                                            </div>
                                            </div>
                                        @endforeach
                                    
                                </div>
                                <br>
                                <div class="text-start">
                                    <button type="submit" class="btn  btn-xs-primary ">
                                        SUBMIT
                                    </button>
                                </div>
                            </form>
                        
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
        $(".select2").select2();
        $('.merchant').change(function(){
           var id = $(this).val();
            window.location.href = "{{url('admin/merchant-module-permission')}}/"+id;
            
        });
    });
</script>
@endsection