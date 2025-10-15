@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Breadcrumb --}}
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Production</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Set Items</li>
                    </ol>
                </nav>

                {{-- Header Section --}}
                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4">
                    <h5 class="m-0 py-2">List of Set Items</h5>
                    <a href="{{ route('production.set_item.create') }}" class="btn btn-xs-primary">ADD +</a>
                </div>

                {{-- Table --}}
                <div class="bg-white table-view shadow-sm mt-3">
                    <table class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th>Item Name</th>
                                <th>BF</th>
                                <th>GSM</th>
                                <th>Speed</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($setItems as $item)
                                <tr class="font-14 bg-white">
                                    <td>{{ $item->item->name ?? 'N/A' }}</td>
                                    <td>{{ $item->bf }}</td>
                                    <td>{{ $item->gsm }}</td>
                                    <td>{{ $item->speed ?? '-' }}</td>
                                    <td>
                                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                            {{ $item->status == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Edit --}}
                                        <a href="{{ route('production.set_item.edit', $item->id) }}">
                                            <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" alt="Edit">
                                        </a>

                                        {{-- Delete --}}
                                        <button type="button" class="border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteAction('{{ route('production.set_item.destroy', $item->id) }}')">
                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" alt="Delete">
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No items added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog w-360 modal-dialog-centered">
        <div class="modal-content p-4 border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-0">
                    <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body">Do you really want to delete this record? This process cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>
    function setDeleteAction(url) {
        document.getElementById('deleteForm').action = url;
    }
</script>

@endsection
