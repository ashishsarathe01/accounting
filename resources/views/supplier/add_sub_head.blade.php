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
                
                <!-- Display validation errors -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Add Sub Head
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('supplier-sub-head.store') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="group" class="form-label font-14 font-heading">Group</label>
                            <select class="form-select" id="group" name="group" required>
                                <option value="">Select Group</option>
                                @foreach($groups as $key => $value)
                                    <option value="{{$value->id}}">{{$value->group_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name"  required/>
                        </div>
                        <div class="mb-2 col-md-2">
                            <label for="sequence" class="form-label font-14 font-heading">Sequence No.</label>
                            <input type="text" class="form-control" id="sequence" name="sequence" placeholder="Enter Sequence No"  required/>
                        </div>
                        <div class="mb-2 col-md-2">
                            <label for="status" class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>
</section>
</div>
</body>

</html>
@endsection