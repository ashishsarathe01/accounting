@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>

    .get_info:hover {
    color: blue; 
}

</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Set Item Wise Consuption Rate</h5>


                    <a href="{{ route('ConsumptionRate.add') }}" class="btn btn-xs-primary">
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
                                <th class="w-min-120 border-none bg-light-pink text-body ">S.no.</th> 
                                <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Staus </th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                             $i=0; 
                            foreach ($list as $value) { ?>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120"><?php echo ++$i ?></td>
                                    <td class="w-min-120"><?php echo $value->name ?></td>
                                    
                                    <td class="w-min-120">
                                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                            <?php
                                            if ($value->status == 1)
                                                echo 'Enable';
                                            else
                                                echo 'Disable'; ?></span>
                                    </td>
                                    <td class="w-min-120 text-center">
                                        @can('action-module',51)
                                            <a href="{{ URL::to('consumption-rate/edit/' . $value->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="" title="Edit"></a>
                                        @endcan
                                        @can('action-module',52)
                                                <a href="{{ route('consumption_rate.delete', $value->id) }}"
                                                        onclick="return confirm('Are you sure you want to delete this consumption rate?');">
                                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="" title="Delete">
                                                        </a>

                                            
                                        @endcan                                        
                                           
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>


                
                

            </div> <!-- END content col -->

        </div> <!-- END row -->
    </section>
</div>

@include('layouts.footer')
<!-- Show/Hide Group Dropdown -->
<script>
    $(document).ready(function () {
        $('.select2-single').select2();
    });

  
</script>
@endsection