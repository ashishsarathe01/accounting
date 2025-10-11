@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')            
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mb-4">
                    <h5 class="master-table-title m-0 py-2">Purchase Settings</h5>
                </div>
                <form action="{{ route('store-supplier-purchase-setting') }}" method="POST">
                    @csrf
                    <!-- Items Section -->
                    <div class="mb-4">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" style="font-size:1.1rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50%;">Item Group</th>
                                        <th style="width:50%;">Item Group Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item_groups as $group)
                                    <tr>
                                        <!-- Unit checkbox -->
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" name="group[]" value="{{ $group->id }}" class="form-check-input group-checkbox" @if(in_array($group->id, $selectedGroups)) checked @endif>
                                                <label class="form-check-label">{{ $group->group_name }}</label>
                                            </div>
                                        </td>
                                        <!-- Unit type radio buttons -->
                                        <td>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" name="group_type_{{ $group->id }}"  value="WASTE KRAFT" class="form-check-input group_type_{{ $group->id }}" @if(isset($selectedGroupType[$group->id]) && $selectedGroupType[$group->id]=="WASTE KRAFT") checked @endif>
                                                <label class="form-check-label">WASTE KRAFT</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" name="group_type_{{ $group->id }}" value="BOILER FUEL" class="form-check-input group_type_{{ $group->id }}" @if(isset($selectedGroupType[$group->id]) && $selectedGroupType[$group->id]=="BOILER FUEL") checked @endif>
                                                <label class="form-check-label">BOILER FUEL</label>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                    <div class="mb-3 text-start">
                        <button type="submit" class="btn btn-primary px-4">Save Settings</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<script>
    $(document).ready(function(){
        $(".group-checkbox").click(function(){
            if($(this).prop('checked')==true){
                $(".group_type_"+$(this).val()).attr('required',true);
            }else{
                $(".group_type_"+$(this).val()).attr('required',false);
            }  
        });
    });
</script>

@endsection
