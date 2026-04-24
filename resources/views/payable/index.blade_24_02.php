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

                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Payable Report</h5>
                </div>

                <!-- ================= FILTER FORM ================= -->
                <form method="GET" action="{{ url('payable/index') }}" class="mt-3">

                    <div class="row">

                        <!-- Date From -->
                        <div class="col-md-3">
                            <label><strong>Date</strong></label>
                            <input type="date" name="date" class="form-control" value="{{ $today }}">
                        </div>



                        <!-- Radio Options -->
                        <!-- Radio Options -->
                        <div class="col-md-2">
                            <label><strong>Show Report</strong></label><br>

                            <label class="mr-3">
                                <input type="radio" name="show_type" value="all"
                                    {{ request('show_type', 'all') == 'all' ? 'checked' : '' }}>
                                All Parties
                            </label>

                            <label>
                                <input type="radio" name="show_type" value="group"
                                    {{ request('show_type') == 'group' ? 'checked' : '' }}>
                                Group-wise
                            </label>
                        </div>

                        <div class="col-md-7" id="groupDiv"
                            style="display: {{ request('show_type') == 'group' ? 'block' : 'none' }};">
                            <div class="col-md-4">
                                <label><strong>Select Group</strong></label>
                                <select name="group_id" class="form-control select2-single">
                                    <option value="">Select Group</option>

                                    @foreach($allGroupsList as $grp)
                                        <option value="{{ $grp->id }}"
                                            {{ request('group_id') == $grp->id ? 'selected' : '' }}>
                                            {{ $grp->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                    </div>

                    <!-- GROUP DROPDOWN (only when Group-wise selected) -->


                    <div class="mt-3">
                        <button class="btn btn-primary">Filter</button>
                        <a href="{{ url('receivable-report') }}" class="btn btn-secondary">Reset</a>
                    </div>

                </form>
                <!-- ================= END FILTER FORM ================= -->


                <!-- ================= TABLE ================= -->
                <table class="table table-bordered table-striped mt-4">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Party Name</th>
                            <th style="text-align:right;">Payable</th>
                            <th style="text-align:right;">Overdue</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $i = 1;
                            $totalReceivable = 0;
                            $totalOverdue = 0;
                        @endphp

                        @foreach($data as $row)
                            @php
                                $totalReceivable += $row->receivable;
                                $totalOverdue += $row->overdue;
                            @endphp

                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $row->party_name }} ({{$row->credit_days ?? '-'}}/{{$row->due_day ?? '-'}})<br>{{ $row->mobile }}</td>
                                <td style="text-align:right; cursor:pointer;" class="get_info" data-id="{{ $row->id }}">{{ number_format($row->receivable, 2) }}</td>
                                <td style="text-align:right; color:red; font-weight:bold; cursor:pointer;"
                                        onclick="window.location='{{ route('payable.overdue.report', $row->id) }}'">
                                        {{ number_format($row->overdue, 2) }}
                                    </td>

                            </tr>
                        @endforeach
                    </tbody>

                    <!-- ===== TOTAL ROW ===== -->
                    <tfoot>
                        <tr style="background:#f0f0f0; font-weight:bold;">
                            <td colspan="2" style="text-align:center;">TOTAL</td>
                            <td style="text-align:right;">{{ number_format($totalReceivable, 2) }}</td>
                            <td style="text-align:right; color:red;">{{ number_format($totalOverdue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

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
    document.querySelectorAll("input[name='show_type']").forEach(radio => {
        radio.addEventListener("change", function () {
            let div = document.getElementById("groupDiv");
            div.style.display = (this.value === "group") ? "block" : "none";
        });
    });
 $(document).ready(function(){
      $(".get_info").click(function(){
         
            window.location = "{{url('accountledger-filter')}}/?party="+$(this).attr('data-id')+"&from_date={{$firstDate}}&to_date={{$today}}";            
                  
      });
   });


  
</script>
@endsection