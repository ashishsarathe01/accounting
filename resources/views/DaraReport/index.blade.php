@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
.table a {
    color: #000 !important;
    text-decoration: none;
}
@media print {
    table {
        width: 100% !important;
    }

    .header-section {
        display: none !important; /* hide buttons only */
    }
    .sidebar {
        display: none !important; /* hide buttons only */
    }
}
@page { size: auto;  margin: 0mm; }
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid py-3">
        <div class="row">

            @include('layouts.leftnav')

            <div class="col-md-12 col-lg-9 px-md-4 bg-light">

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
                    title-border-redius border-divider shadow-sm">
                    DARA Report <button class="btn btn-info header-section" onclick="printpage();">Print Bill</button>
                </h5>
                
                <div class="card shadow-sm border-0 mt-3 mb-4">
                    <div class="card-body">

                        <form method="GET" action="{{ route('dara.report') }}" class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">From Date</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ $from_date }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">To Date</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ $to_date }}">
                            </div>

                            <div class="col-md-4 d-flex align-items-end justify-content-end gap-3">

                            <div class="form-check header-section">
                                <input class="form-check-input" type="checkbox" name="detailed" value="1"
                                    {{ request('detailed') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    Detailed
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 header-section">
                                Search
                            </button>

                        </div>

                        </form>

                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <h6 class="mb-3 fw-semibold">Summary</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered">

                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width:50%">Sales</th>
                                        <th style="width:50%">Purchase Wastekraft</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <!-- AMOUNT -->
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Sale Amount</span>
                                                <a href="javascript:void(0)" class="text-dark open-sales-modal">
                                                    {{ formatIndianNumber($total_sales ?? 0,2) }}
                                                </a>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <span>(-) Credit Note</span>
                                                <a href="javascript:void(0)" class="text-dark open-cn-modal">
                                                    {{ formatIndianNumber($creditNote ?? 0,2) }}
                                                </a>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <span>(-) Freight</span>
                                                <a href="javascript:void(0)" class="text-dark open-freight-modal">
                                                    {{ formatIndianNumber($total_freight ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Purchase Wastekraft Amt</span>
                                                <a href="javascript:void(0)" class="text-dark open-purchase-amount-modal">
                                                    {{ formatIndianNumber($purchase_wastekraft_amount ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- TOTAL AMOUNT -->
                                    <tr>
                                        <td class="fw-bold">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Amount</span>
                                                <a href="javascript:void(0)" class="text-dark open-total-amount-modal">
                                                    {{ formatIndianNumber($total_amount ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>

                                    <!-- WEIGHT -->
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Sales Weight</span>
                                                <a href="javascript:void(0)" class="text-dark open-sale-weight-modal">
                                                    {{ formatIndianNumber($salesWeight ?? 0,2) }}
                                                </a>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <span>(-) CN Weight</span>
                                                <a href="javascript:void(0)" class="text-dark open-cn-weight-modal">
                                                    {{ formatIndianNumber($creditWeight ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Purchase Wastekraft Wt</span>
                                                <a href="javascript:void(0)" class="text-dark open-purchase-weight-modal">
                                                    {{ formatIndianNumber($purchase_wastekraft_weight ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- TOTAL WEIGHT -->
                                    <tr>
                                        <td class="fw-bold">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Weight</span>
                                                <a href="javascript:void(0)" class="text-dark open-total-weight-modal">
                                                    {{ formatIndianNumber($total_weight ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>

                                    <!-- RATES -->
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Avg Sale Rate</span>
                                                <a href="javascript:void(0)" class="text-dark open-avg-rate-modal">
                                                    {{ number_format($avg_rate ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-between">
                                                <span>Waste Purchase Rate</span>
                                                <a href="javascript:void(0)" class="text-dark open-waste-rate-modal">
                                                    {{ number_format($waste_purchase_price ?? 0,2) }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- DARA -->
                                    <tr>
                                        <th colspan="2" class="text-center">
                                            <strong>DARA Price :</strong>
                                            <a href="javascript:void(0)" class="text-dark open-dara-modal">
                                                <span class="{{ $dara_price < 0 ? 'text-danger' : 'text-success' }}">
                                                    <strong>{{ number_format($dara_price ?? 0,2) }}</strong>
                                                </span>
                                            </a>
                                        </th>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if(request('detailed') && !empty($detailedData))
                        <div class="card shadow-sm border-0 mt-4">
                            <div class="card-body">
                                <h6 class="mb-3 fw-semibold">Detailed (Date-wise)</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center">

                                        <thead class="table-light">
                                            <th>Date</th>
                                            <th>Total Amount</th>
                                            <th>Total Weight</th>
                                            <th>Avg Sale Rate</th>
                                            <th>Purchase Wastekraft Amt</th>
                                            <th>Purchase Wastekraft Wt</th>
                                            <th>Avg Waste Rate</th>

                                            <th>DARA Price</th>
                                        </thead>

                                        <tbody>
                                            @php 
                                                $sale_total_amount = 0; $sale_total_weight = 0;
                                                $purchase_total_amount = 0; $purchase_total_weight = 0;
                                            @endphp
                                            @foreach($detailedData as $row)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>

                                                    <td>{{ formatIndianNumber($row['total_amount'],2) }}</td>
                                                    <td>{{ formatIndianNumber($row['total_weight'],2) }}</td>
                                                    <td>{{ number_format($row['avg_rate'],2) }}</td>

                                                    <td>{{ formatIndianNumber($row['purchase_amount'],2) }}</td>
                                                    <td>{{ formatIndianNumber($row['purchase_weight'],2) }}</td>
                                                    <td>{{ number_format($row['waste_rate'],2) }}</td>

                                                    <td class="fw-bold {{ $row['dara'] < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($row['dara'],2) }}
                                                    </td>
                                                </tr>
                                                @php 
                                                    $sale_total_amount = $sale_total_amount + $row['total_amount']; 
                                                    $sale_total_weight = $sale_total_weight + $row['total_weight'];
                                                    $purchase_total_amount = $purchase_total_amount + $row['purchase_amount']; 
                                                    $purchase_total_weight = $purchase_total_weight + $row['purchase_weight'];
                                                @endphp
                                            @endforeach
                                            @php
                                                $sale_ave = $sale_total_amount/$sale_total_weight;
                                                $sale_ave = round($sale_ave,2);
                                                
                                                $purchase_ave = $purchase_total_amount/$purchase_total_weight;
                                                $purchase_ave = round($purchase_ave,2);
                                                $over_all_ave = $sale_ave - $purchase_ave;
                                            @endphp
                                                <tr>
                                                    <th>Total</th>
                                                    <th>{{ formatIndianNumber($sale_total_amount,2) }}</th>
                                                    <th>{{ formatIndianNumber($sale_total_weight,2) }}</th>
                                                    <th>{{ formatIndianNumber($sale_ave,2) }}</td>
                                                    <th>{{ formatIndianNumber($purchase_total_amount,2) }}</th>
                                                    <th>{{ formatIndianNumber($purchase_total_weight,2) }}</th>
                                                    <th>{{ formatIndianNumber($purchase_ave,2) }}</th>
                                                    <th class="fw-bold {{ $over_all_ave < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ formatIndianNumber($over_all_ave,2) }}
                                                    </th>
                                                </tr>
                                        </tbody>

                                    </table>
                                </div>

                            </div>
                        </div>
                    @endif
                </div>

            </div>
            
        <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key w-100">
                <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>

                <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" 
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">
                    F1 <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                    <span class="border-bottom-black">F1</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                    <span class="border-bottom-black">F2</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">
                    F3 <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                    <span class="border-bottom-black">F3</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                    <span class="border-bottom-black">F5</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                    <span class="border-bottom-black">F6</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                    <span class="border-bottom-black">F7</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                    <span class="border-bottom-black">F8</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                    <span class="border-bottom-black">F9</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                    <span class="border-bottom-black">B</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                    <span class="border-bottom-black">T</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                    <span class="border-bottom-black">S</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                    <span class="border-bottom-black">L</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                    <span class="border-bottom-black">I</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                    <span class="border-bottom-black">D</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                    <span class="border-bottom-black">G</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                    <span class="border-bottom-black">U</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                    <span class="border-bottom-black">F</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                    <span class="border-bottom-black">K</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
                </button>

                <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block"
                        data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">
                    Search Menu
                </button>

            </div>
         </div>
        </div>
    </section>
</div>
<div class="modal fade" id="salesModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sales Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher No</th>
                                <th>Party</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody id="salesModalBody">
                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="cnModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Credit Note Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher No</th>
                                <th>Party</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody id="cnModalBody"></tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="freightModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Freight (Journal) Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher No</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody id="freightModalBody"></tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="saleWeightModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sale Weight Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Party</th>
                                <th>Weight</th>
                            </tr>
                        </thead>

                        <tbody id="saleWeightModalBody"></tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="cnWeightModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Credit Note Weight Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Voucher</th>
                                <th>Party</th>
                                <th>Weight</th>
                            </tr>
                        </thead>

                        <tbody id="cnWeightModalBody"></tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="totalAmountModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Total Amount Calculation</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p><b>Sales:</b> {{ formatIndianNumber($total_sales,2) }}</p>
                <p><b>Credit Note:</b> - {{ formatIndianNumber($creditNote,2) }}</p>
                <p><b>Freight:</b> - {{ formatIndianNumber($total_freight,2) }}</p>

                <hr>

                <h5>Total = {{ formatIndianNumber($total_amount,2) }}</h5>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="totalWeightModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Total Weight Calculation</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p><b>Sale Weight:</b> {{ formatIndianNumber($salesWeight,2) }}</p>
                <p><b>CN Weight:</b> - {{ formatIndianNumber($creditWeight,2) }}</p>

                <hr>

                <h5>Total = {{ formatIndianNumber($total_weight,2) }}</h5>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="avgRateModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Average Sale Rate</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p><b>Total Amount:</b> {{ formatIndianNumber($total_amount,2) }}</p>
                <p><b>Total Weight:</b> {{ formatIndianNumber($total_weight,2) }}</p>

                <hr>

                <h5>Avg = {{ number_format($avg_rate,2) }}</h5>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="daraModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>DARA Price Calculation</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p><b>Avg Sale Rate:</b> {{ number_format($avg_rate,2) }}</p>
                <p><b>Waste Purchase Rate:</b> {{ number_format($waste_purchase_price,2) }}</p>

                <hr>

                <h5>DARA = {{ number_format($dara_price,2) }}</h5>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="purchaseAmountModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Purchase Wastekraft Amount Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account Name</th>
                            <th>Slip No.</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseAmountBody"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="purchaseWeightModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Purchase Wastekraft Weight Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account Name</th>
                            <th>Slip No.</th>
                            <th>Weight</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseWeightBody"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="wasteRateModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Waste Purchase Rate Calculation</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <p><b>Purchase Wastekraft Amount:</b> 
                    {{ formatIndianNumber($purchase_wastekraft_amount ?? 0,2) }}
                </p>

                <p><b>Purchase Wastekraft Weight:</b> 
                    {{ formatIndianNumber($purchase_wastekraft_weight ?? 0,2) }}
                </p>

                <hr>

                <h5>
                    Rate = {{ number_format($waste_purchase_price ?? 0,2) }}
                </h5>

            </div>

        </div>
    </div>
</div>
@include('layouts.footer')

<script>
    let salesData = @json($salesDetails);
    let cnData = @json($creditNoteDetails);
    let freightData = @json($freightDetails ?? []);
    let saleWeightData = @json($saleWeightDetails ?? []);
    let cnWeightData = @json($cnWeightDetails ?? []);
    let purchaseData = @json($purchaseDetails ?? []);
    $(document).on('click', '.open-sales-modal', function () {

        let html = '';
        let total = 0;

        const formatIndian = (num) => {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };

        salesData.forEach(row => {

            let amount = parseFloat(row.amount) || 0;
            total += amount;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${row.party_name ?? ''}</td>
                    <td>${formatIndian(amount)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${formatIndian(total)}</td>
            </tr>
        `;

        $('#salesModalBody').html(html);
        $('#salesModal').modal('show');
    });
    $(document).on('click', '.open-cn-modal', function () {

        let html = '';
        let total = 0;

        const formatIndian = (num) => {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };

        cnData.forEach(row => {

            let amount = parseFloat(row.amount) || 0;
            total += amount;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${row.party_name ?? ''}</td>
                    <td>${formatIndian(amount)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${formatIndian(total)}</td>
            </tr>
        `;

        $('#cnModalBody').html(html);
        $('#cnModal').modal('show');
    });
    $(document).on('click', '.open-freight-modal', function () {

        let html = '';
        let total = 0;

        const formatIndian = (num) => {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };

        freightData.forEach(row => {

            let amount = parseFloat(row.amount) || 0;
            if(amount==0){
                return;
            }
            total += amount;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${formatIndian(amount)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="2">Total</td>
                <td>${formatIndian(total)}</td>
            </tr>
        `;

        $('#freightModalBody').html(html);
        $('#freightModal').modal('show');
    });
    $(document).on('click', '.open-sale-weight-modal', function () {

        let html = '';
        let total = 0;

        const formatIndian = (num) => {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };

        saleWeightData.forEach(row => {

            let weight = parseFloat(row.weight) || 0;
            total += weight;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${row.party_name ?? ''}</td>
                    <td>${formatIndian(weight)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${formatIndian(total)}</td>
            </tr>
        `;

        $('#saleWeightModalBody').html(html);
        $('#saleWeightModal').modal('show');
    });
    $(document).on('click', '.open-cn-weight-modal', function () {

        let html = '';
        let total = 0;

        const formatIndian = (num) => {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };

        cnWeightData.forEach(row => {

            let weight = parseFloat(row.weight) || 0;
            total += weight;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${row.party_name ?? ''}</td>
                    <td>${formatIndian(weight)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${formatIndian(total)}</td>
            </tr>
        `;

        $('#cnWeightModalBody').html(html);
        $('#cnWeightModal').modal('show');
    });
    $(document).on('click', '.open-purchase-amount-modal', function () {

        let html = '';
        let total = 0;

        purchaseData.forEach(row => {

            let amount = parseFloat(row.amount) || 0;
            total += amount;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.account_name ?? ''}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${amount.toFixed(2)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${total.toFixed(2)}</td>
            </tr>
        `;

        $('#purchaseAmountBody').html(html);
        $('#purchaseAmountModal').modal('show');
    });
    $(document).on('click', '.open-purchase-weight-modal', function () {

        let html = '';
        let total = 0;

        purchaseData.forEach(row => {

            let weight = parseFloat(row.weight) || 0;
            total += weight;

            html += `
                <tr>
                    <td>${new Date(row.date).toLocaleDateString('en-GB')}</td>
                    <td>${row.account_name ?? ''}</td>
                    <td>${row.voucher_no ?? ''}</td>
                    <td>${weight.toFixed(2)}</td>
                </tr>
            `;
        });

        html += `
            <tr class="fw-bold bg-light">
                <td colspan="3">Total</td>
                <td>${total.toFixed(2)}</td>
            </tr>
        `;

        $('#purchaseWeightBody').html(html);
        $('#purchaseWeightModal').modal('show');
    });
    $(document).on('click', '.open-total-amount-modal', function () {
        $('#totalAmountModal').modal('show');
    });

    $(document).on('click', '.open-total-weight-modal', function () {
        $('#totalWeightModal').modal('show');
    });

    $(document).on('click', '.open-avg-rate-modal', function () {
        $('#avgRateModal').modal('show');
    });

    $(document).on('click', '.open-dara-modal', function () {
        $('#daraModal').modal('show');
    });
    $(document).on('click', '.open-waste-rate-modal', function () {
        $('#wasteRateModal').modal('show');
    });
    function printpage(){
       window.print();
    }
</script>
@endsection