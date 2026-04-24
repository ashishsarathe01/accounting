@extends('layouts.app')
<style>
    
/* SELECT2 */
.select2-container--default .select2-selection--single {
    height: 40px !important;
    border-radius: 8px !important;
    border: 1px solid #ced4da !important;
}
.select2-container--default .select2-selection__rendered {
    line-height: 38px !important;
    font-size: 14.5px;
}
.select2-container {
    width: 100% !important;
}

/* INPUT */
.form-control {
    height: 40px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    font-size: 14.5px;
}
.form-control:focus {
    border-color: #5e60ce;
    box-shadow: 0 0 0 0.1rem rgba(94,96,206,0.2);
}
</style>
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-3 px-4 mb-3">
        <h4 class="m-0">Manage Sale Rate</h4>

        <a href="{{ route('party-item-rate.create') }}" class="btn btn-success">
            + Add
        </a>
    </div>

    <!-- FILTER -->
   <div class="card mb-3 shadow-sm border-0" style="border-radius:12px;">
    <div class="card-body py-3 px-3">

        <form method="GET">
            <div class="row align-items-end">

                <!-- PARTY -->
                <div class="col-md-4">
                    <label class="form-label" style="font-weight:500; color:#444;">
                        View By Party
                    </label>

                    <select name="party_id" class="form-control select2">
                        <option value="">All Parties</option>
                        @foreach($party_list as $party)
                            <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                {{ $party->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTONS -->
                <div class="col-md-4">
                    <div class="d-flex gap-2 mt-2 mt-md-0">

                        <button class="btn btn-primary px-4">
                            Search
                        </button>

                        <a href="{{ route('party-item-rate.index') }}" 
                           class="btn btn-light border px-4">
                            Reset
                        </a>

                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

    <!-- TABLE -->
    <div class="bg-white shadow-sm">
        <table class="table table-striped m-0" style="font-size:16px;">

            <thead class="bg-info text-white">
                <tr>
                    <th>#</th>
                    <th>Party Name</th>
                    <th>Total Items</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($data as $key => $row)
                    <tr>
                        <td>{{ $key + 1 }}</td>

                        <td>
                            <b>{{ $row->account_name }}</b>
                        </td>

                        <td>
                            <span class="badge bg-primary">
                                {{ $row->total_items }} Items
                            </span>
                        </td>

                        <td>
                            {{ $row->last_updated ? date('d-m-Y', strtotime($row->last_updated)) : '-' }}
                        </td>

                        <td>
                            <span class="badge bg-success">Enabled</span>
                        </td>

                        <td class="text-center">

                            <a href="{{ route('party-item-rate.edit', $row->party_id) }}" 
                               class="btn btn-sm btn-warning">
                               <i class="fa fa-edit"></i> Edit
                            </a>
                            
                            <a href="#" class="btn btn-sm btn-danger deleteBtn" data-id="{{ $row->party_id }}">
                                Delete
                            </a>
                           



                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No Data Found</td>
                    </tr>
                @endforelse

            </tbody>

        </table>
        <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">

            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <h5>Delete this record?</h5>
                <p>This action cannot be undone.</p>
            </div>

            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary cancelBtn">Cancel</button>

                <a href="#" id="confirmDelete" class="btn btn-danger">
                    Delete
                </a>
            </div>

        </div>
    </div>
</div>
    </div>

</div>
</div>
</section>
</div>

@include('layouts.footer')

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){

    $('.select2').select2();

    // Delete (optional confirm)
    $(document).on('click', '.deleteBtn', function(){
        if(confirm('Delete this party rate?')){
            let id = $(this).data('id');
            window.location.href = '/party-item-rate/delete/' + id;
        }
    });

});
</script>

@endsection