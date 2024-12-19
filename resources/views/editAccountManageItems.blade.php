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
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Item</li>
                    </ol>
                </nav>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                Edit Manage Item
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-manage-item.update') }}">
                    @csrf
                    <input type="hidden" value="{{ $manageitems->id }}" id="mangeitem_id" name="mangeitem_id" />
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Item Name</label>
                            <input type="text" class="form-control" name="name" id="name" value="{{ $manageitems->name }}" placeholder="Enter item name" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Print Name</label>
                            <input type="text" class="form-control" name="p_name" id="p_name" value="{{ $manageitems->p_name }}" placeholder="Enter print name" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Under Group</label>
                            <select class="form-select form-select-lg" name="g_name" id="g_name"  aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $manageitems->g_name ==1 ? 'selected':'';?> >One</option>
                                <option <?php echo $manageitems->g_name ==2 ? 'selected':'';?> >Two</option>
                                <option <?php echo $manageitems->g_name ==3 ? 'selected':'';?> >Three</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Unit</label>
                            <select class="form-select form-select-lg " name="u_name" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $manageitems->u_name ==1 ? 'selected':'';?> value="1">One</option>
                                <option <?php echo $manageitems->u_name ==2 ? 'selected':'';?> value="2">Two</option>
                                <option <?php echo $manageitems->u_name ==3 ? 'selected':'';?> value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Opening Balance(DR./CR.)</label>
                            <select class="form-select form-select-lg " id="opening_balance_cr" name="opening_balance_cr" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $manageitems->opening_balance_cr ==1 ? 'selected':'';?> value="1">One</option>
                                <option <?php echo $manageitems->opening_balance_cr ==2 ? 'selected':'';?> value="2">Two</option>
                                <option <?php echo $manageitems->opening_balance_cr ==3 ? 'selected':'';?> value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Opening Balance(Quantity)</label>
                            <select class="form-select form-select-lg " id="opening_balance_qty" name="opening_balance_qty" aria-label="form-select-lg example">
                                <option selected>Select </option>
                                <option <?php echo $manageitems->opening_balance_qty ==1 ? 'selected':'';?> value="1">One</option>
                                <option <?php echo $manageitems->opening_balance_qty ==2 ? 'selected':'';?> value="2">Two</option>
                                <option <?php echo $manageitems->opening_balance_qty ==3 ? 'selected':'';?> value="3">Three</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">GST Rate</label>
                            <input type="text" class="form-control" id="gst_rate" name="gst_rate" value="{{ $manageitems->gst_rate }}" placeholder="Enter GST rate" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">HSN Code</label>
                            <input type="text" class="form-control" id="hsn_code" name="hsn_code" value="{{ $manageitems->hsn_code }}" placeholder="Enter HSN code" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option <?php echo $manageitems->status ==1 ? 'selected':'';?> value="1">Enable</option>
                                <option <?php echo $manageitems->status ==0 ? 'selected':'';?> value="0">Disable</option>
                            </select>
                        </div>
                    </div>

                <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            UPDATE
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>
</section>
</div>
</body>
@endsection