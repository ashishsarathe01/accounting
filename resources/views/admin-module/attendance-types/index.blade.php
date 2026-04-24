@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('admin-module.layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Title Bar --}}
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2">Set Attendance Types</h5>
                </div>

                {{-- Form Card --}}
                <div class="bg-white table-view shadow-sm">

                    <form class="bg-white px-4 py-4 border-divider rounded-bottom-8 shadow-sm"
                          method="POST"
                          action="{{ route('admin.attendance.types.store') }}">
                        @csrf

                        <div id="attendance-wrapper">

                            <div class="row align-items-end mb-3 attendance-row">
                                <div class="col-md-6">
                                    <label class="form-label font-14 font-heading">
                                        Attendance Type
                                    </label>
                                    <input type="text"
                                           name="type_name[]"
                                           class="form-control"
                                           placeholder="Enter Attendance Type"
                                           required>
                                </div>

                                <div class="col-md-2">
                                    <button type="button"
                                            class="btn btn-success add-more mt-4">
                                        +
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="text-start mt-3">
                            <button type="submit" class="btn btn-xs-primary">
                                SAVE
                            </button>
                        </div>

                    </form>
                </div>

                {{-- Existing Types Table --}}
                <div class="bg-white table-view shadow-sm mt-4">

                    <div class="px-4 py-3 border-divider">
                        <h6 class="m-0">Existing Attendance Types</h6>
                    </div>

                    <div class="table-responsive px-4 py-3">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="60">Sr No.</th>
                                    <th>Type Name</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($types as $key => $type)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $type->type_name }}</td>
                                        <td>
                                            <a href="{{ route('admin.attendance.types.delete',$type->id) }}"
                                               onclick="return confirm('Are you sure?')"
                                               class="btn btn-danger btn-sm">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            No attendance types added yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </section>
</div>

{{-- Dynamic Add/Remove Script --}}
<script>
document.addEventListener('click', function(e){

    // Add More
    if(e.target.classList.contains('add-more')){

        let wrapper = document.getElementById('attendance-wrapper');

        let div = document.createElement('div');
        div.classList.add('row','align-items-end','mb-3','attendance-row');

        div.innerHTML = `
            <div class="col-md-6">
                <label class="form-label font-14 font-heading">
                    Attendance Type
                </label>
                <input type="text"
                       name="type_name[]"
                       class="form-control"
                       placeholder="Enter Attendance Type"
                       required>
            </div>

            <div class="col-md-2">
                <button type="button"
                        class="btn btn-danger remove-field mt-4">
                    -
                </button>
            </div>
        `;

        wrapper.appendChild(div);
    }

    // Remove
    if(e.target.classList.contains('remove-field')){
        e.target.closest('.attendance-row').remove();
    }

});
</script>

@include('layouts.footer')
@endsection