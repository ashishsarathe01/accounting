@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-light py-4">

                <h1 class="text-primary mb-4">GSTR-3B Summary</h1>

                <div class="row g-4">

                    <!-- 3.1 Tax on outward and reverse charge inward supplies -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">3.1 Tax on outward and reverse charge inward supplies</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Integrated Tax: ₹17,280.00</p>
                            <p>Central Tax: ₹0.00</p>
                            <p>State/UT Tax: ₹0.00</p>
                            <p>CESS (₹): ₹0.00</p>
                        </div>
                    </div>

                    <!-- 3.1.1 Supplies under sec 9(5) -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">3.1.1 Supplies notified under section 9(5)</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Integrated Tax: ₹0.00</p>
                            <p>Central Tax: ₹0.00</p>
                            <p>State/UT Tax: ₹0.00</p>
                            <p>CESS (₹): ₹0.00</p>
                        </div>
                    </div>

                    <!-- 3.2 Inter-state supplies -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">3.2 Inter-state supplies</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Taxable Value: ₹0.00</p>
                            <p>Integrated Tax: ₹0.00</p>
                        </div>
                    </div>

                    <!-- 4. Eligible ITC -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">4. Eligible ITC</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Integrated Tax: ₹2,345.34</p>
                            <p>Central Tax: ₹-3.89</p>
                            <p>State/UT Tax: ₹-3.89</p>
                            <p>CESS (₹): ₹0.00</p>
                        </div>
                    </div>

                    <!-- 5. Exempt, nil and non-GST -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">5. Exempt, nil and Non GST inward supplies</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Inter-state supplies: ₹0.00</p>
                            <p>Intra-state supplies: ₹0.00</p>
                        </div>
                    </div>

                    <!-- 5.1 Interest and Late Fee -->
                    <div class="col-md-4">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">5.1 Interest and Late fee for previous tax period</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Integrated Tax: ₹0.00</p>
                            <p>Central Tax: ₹0.00</p>
                            <p>State/UT Tax: ₹0.00</p>
                            <p>CESS (₹): ₹0.00</p>
                        </div>
                    </div>

                    <!-- 6.1 Payment of Tax -->
                    <div class="col-md-12">
                        <div class="bg-primary text-white p-2 fw-bold rounded-top">6.1 Payment of tax</div>
                        <div class="bg-white border p-3 rounded-bottom">
                            <p>Balance Liability: ₹0.00</p>
                            <p>Paid through Cash: ₹14,943.00</p>
                            <p>Paid through Credit: ₹2,345.00</p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
</div>
@endsection
