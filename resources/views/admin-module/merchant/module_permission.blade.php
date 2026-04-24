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
                                        <label for="company_id" class="form-label font-14 font-heading">Company</label>
                                        <select class="form-select form-select-lg company select2" name="company_id" aria-label="form-select-lg example" required>
                                            <option selected value="">Choose Company</option>
                                            @foreach($company_list as $company)
                                                <option value="{{$company->id}}" @if($company->id==$company_id) selected @endif>
                                                    {{$company->company_name}}
                                                </option>
                                            @endforeach
                                        </select>
                                        <ul style="color: red;">
                                            @error('company_id') Please Select Company @enderror
                                        </ul> 
                                    </div>
                                    
                                    <div class="clearfix"></div>
                                    <input type="hidden" name="merchant_id" value="{{$merchant_id}}">
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
        $('.company').change(function(){
            var company_id = $(this).val();
            var merchant_id = "{{$merchant_id}}";
            window.location.href = "{{url('admin/merchant-module-permission')}}/"+merchant_id+"/"+company_id;
        });
    });
</script>
@endsection