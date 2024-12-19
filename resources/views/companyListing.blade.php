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
                    <nav>
                        <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                            <li class="breadcrumb-item">Dashboard</li>
                            <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                            <li class="breadcrumb-item fw-bold font-heading" aria-current="page">View
                                Company</li>
                        </ol>
                    </nav>
                    <h5 class=" px-4 py-3 m-0 bg-plum-viloet position-relative">List of View
                        Company
                        <span class="table-title-bottom-line position-absolute d-inline-block"></span>
                    </h5>
                    <div class="overflow-auto shadow-sm">
                    <table  id="example"  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-120 border-none bg-light-pink text-body">Firm’s Name</th>
                                <th class="w-180 border-none bg-light-pink text-body ">Email ID</th>
                                <th class="w-120 border-none bg-light-pink text-body ">GSTN</th>
                                <th class="w-120 border-none bg-light-pink text-body ">State</th>
                                <th class="w-120 border-none bg-light-pink text-body ">Country</th>
                                <th class="w-120 border-none bg-light-pink text-body ">Director Name</th>
                                <th class="w-120 border-none bg-light-pink text-body ">Mobile No.</th>
                                <th class="w-120 border-none bg-light-pink text-body ">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($company as $value) { ?>
                               
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-120"><?php echo $value->company_name?></td>
                                <td class="w-180"><?php echo $value->email_id?></td>
                                <td class="w-120"><?php echo $value->gst?></td>
                                <td class="w-120"><?php echo $value->state?></td>
                                <td class="w-120"><?php echo $value->country_name?></td>
                                <td class="w-120">Andrew ansley</td>
                                <td class="w-120"><?php echo $value->mobile_no?></td>
                                <td class="w-120">
                                    <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                    <img src="public/assets/imgs/delete-icon.svg" class="px-1" alt="">
                                </td>
                            </tr>
                           
                          <?php } ?>

                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </section>
    </div>
</body>


</html>
@endsection
