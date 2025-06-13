@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Nil Rated & Exempted Sales Report</h2>

    <p><strong>Merchant GSTIN:</strong> {{ $merchant_gst }}</p>
    <p><strong>Company ID:</strong> {{ $company_id }}</p>
    <p><strong>From Date:</strong> {{ $from_date }}</p>
    <p><strong>To Date:</strong> {{ $to_date }}</p>

    <div class="row mt-4">
        <div class="col-md-6">
            <h4>Nil Rated Supplies</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Intra-State</th>
                        <th>Inter-State</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Registered</td>
                        <td>{{ number_format($nil_rated_reg_intra, 2) }}</td>
                        <td>{{ number_format($nil_rated_reg_inter, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Unregistered</td>
                        <td>{{ number_format($nil_rated_unreg_intra, 2) }}</td>
                        <td>{{ number_format($nil_rated_unreg_inter, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h4>Exempted Supplies</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Intra-State</th>
                        <th>Inter-State</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Registered</td>
                        <td>{{ number_format($exempted_reg_intra, 2) }}</td>
                        <td>{{ number_format($exempted_reg_inter, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Unregistered</td>
                        <td>{{ number_format($exempted_unreg_intra, 2) }}</td>
                        <td>{{ number_format($exempted_unreg_inter, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

