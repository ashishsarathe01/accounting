@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between
                    align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="transaction-table-title m-0 py-2">
                        Spare Part – Add Supplier
                    </h5>

                    <a href="{{ route('spare-part.suppliers') }}" class="btn btn-border-body">
                        BACK
                    </a>
                </div>

                {{-- Form --}}
                <div class="transaction-table bg-white shadow-sm mt-4 p-4">
                    <form method="POST" action="{{ route('spare-part.suppliers.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Select Account <span class="text-danger">*</span>
                                </label>

                                <select name="account_id"
                                        class="form-select select2-single"
                                        required>
                                    <option value="">-- Select Account --</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-xs-primary">
                                SAVE
                            </button>

                            <a href="{{ route('spare-part.suppliers') }}"
                               class="btn btn-border-body ms-2">
                                CANCEL
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<script>
    $(document).ready(function(){
        $(".select2-single").select2();
    })
</script>
@endsection
