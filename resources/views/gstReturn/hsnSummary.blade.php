@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
                <div class="container col-md-10 justify-content-between py-4 px-2 align-items-center" style="background-color: #F6F7FF;">
                    <!-- Heading -->
                    <div class="text-center mb-4">
                        <h3 class="mb-5">HSN-wise Summary</h3>

                        <!-- B2B / B2C Toggle Buttons -->
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button
                                    class="btn btn-lg btn-block {{ request('type') == 'B2B' ? 'btn-primary' : 'btn-outline-primary' }}"
                                    style="font-size: 1.1rem; padding: 10px; width:90%;"
                                    onclick="redirectToType('B2B')">
                                    B2B
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button
                                    class="btn btn-lg btn-block {{ request('type') == 'B2C' ? 'btn-success' : 'btn-outline-success' }}"
                                    style="font-size: 1.1rem; padding: 10px;  width:90%;"
                                    onclick="redirectToType('B2C')">
                                    B2C
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- HSN Table -->
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr style="background-color: #003366;">
                                <th style="color: white;">S. No.</th>
                                <th style="color: white;">HSN Code</th>
                                <th style="color: white;">Description</th>
                                <th style="color: white;">UQC</th>
                                <th style="color: white;">Total Quantity</th>
                                <th style="color: white;">Rate of Tax</th>
                                <th style="color: white;">Taxable Value</th>
                                <th style="color: white;">CGST</th>
                                <th style="color: white;">SGST</th>
                                <th style="color: white;">IGST</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['hsn'] }}</td>
                                    <td>..</td>
                                    <td>{{ $row['unit_code'] }}</td>
                                    <td>{{ $row['qty'] }}</td>
                                    <td>{{ $row['rate'] }}%</td>
                                    <td>{{ formatIndianNumber($row['taxable_value']) }}</td>
                                    <td>{{ formatIndianNumber($row['cgst']) }}</td>
                                    <td>{{ formatIndianNumber($row['sgst']) }}</td>
                                    <td>{{ formatIndianNumber($row['igst']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center">No Data Available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                     
      </div>
   </section>
</div>
</body>

@include('layouts.footer')

<!-- JavaScript Redirect -->
<script>
    function redirectToType(type) {
        const baseUrl = "{{ url('/report/hsn') }}";

        const params = new URLSearchParams({
            merchant_gst: "{{ request('merchant_gst') }}",
            company_id: "{{ request('company_id') }}",
            from_date: "{{ request('from_date') }}",
            to_date: "{{ request('to_date') }}",
            type: type
        });

        window.location.href = `${baseUrl}?${params.toString()}`;
    }
</script>
@endsection
