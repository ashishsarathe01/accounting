@extends('layouts.app')
@section('content')
    <!-- header-section -->
    @include('layouts.header')
<style>
.dashboard-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e5e5e5;
}

.dashboard-card .card-header {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    background: #f9f9f9;
}

.dashboard-card .card-body {
    padding: 15px;
    font-size: 14px;
}
</style>

    <!-- list-view-company-section -->
    <div class="list-of-view-company ">
        <section class="list-of-view-company-section container-fluid">
            <div class="row vh-100">
            @include('layouts.leftnav')
                <!-- view-table-Content -->
                <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <nav>
                        <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                            <li class="breadcrumb-item">Dashboard</li>
                            <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                        </ol>
                    </nav>
                   <div class="row mt-4">
                        {{-- SALES --}}
                        @if($showSalesCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Sales</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- ADD -->
                                        @if($salesShowAdd)
                                        @can('view-module', 10)
                                        <a href="{{ route('sale.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                    fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif

                                        <!-- VIEW -->
                                        @if($salesShowView)
                                        @can('view-module', 85)
                                        <a href="{{ route('sale.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>
                                <div class="card-body">

                                    @if($salesDashboardData['show_total_sales_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Sales</span>
                                            <strong>{{ $salesDashboardData['total_sales_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($salesDashboardData['show_total_sales_qty'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Qty</span>
                                            <strong>{{ number_format($salesDashboardData['total_sales_qty'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($salesDashboardData['show_total_sales_amount'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Amount</span>
                                            <strong>₹ {{ number_format($salesDashboardData['total_sales_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($salesDashboardData['show_sales_with_gst'])
                                        <div class="d-flex justify-content-between text-success mb-2">
                                            <span>With GST</span>
                                            <strong>₹ {{ number_format($salesDashboardData['sales_with_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($salesDashboardData['show_sales_without_gst'])
                                        <div class="d-flex justify-content-between text-primary">
                                            <span>Without GST</span>
                                            <strong>₹ {{ number_format($salesDashboardData['sales_without_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif


                        {{-- PURCHASE --}}
                        @if($showPurchaseCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Purchase</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- ADD -->
                                         @if($purchaseShowAdd)
                                        @can('view-module', 83)
                                        <a href="{{ route('purchase.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                    fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif
                                        <!-- VIEW -->
                                         @if($purchaseShowView)
                                        @can('view-module', 11)
                                        <a href="{{ route('purchase.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>
                                <div class="card-body">

                                    @if($purchaseDashboardData['show_total_purchase_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Purchase</span>
                                            <strong>{{ $purchaseDashboardData['total_purchase_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseDashboardData['show_total_purchase_qty'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Qty</span>
                                            <strong>{{ number_format($purchaseDashboardData['total_purchase_qty'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseDashboardData['show_total_purchase_amount'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Amount</span>
                                            <strong>₹ {{ number_format($purchaseDashboardData['total_purchase_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseDashboardData['show_purchase_with_gst'])
                                        <div class="d-flex justify-content-between text-success mb-2">
                                            <span>With GST</span>
                                            <strong>₹ {{ number_format($purchaseDashboardData['purchase_with_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseDashboardData['show_purchase_without_gst'])
                                        <div class="d-flex justify-content-between text-primary">
                                            <span>Without GST</span>
                                            <strong>₹ {{ number_format($purchaseDashboardData['purchase_without_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif


                        {{-- SALE RETURN --}}
                        @if($showSaleReturnCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Sale Return</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        @if($saleReturnShowAdd)
                                        <!-- ADD -->
                                        @can('view-module', 76)
                                        <a href="{{ route('sale-return.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                    fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif

                                        <!-- VIEW -->
                                         @if($saleReturnShowView)
                                        @can('view-module', 12)
                                        <a href="{{ route('sale-return.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>
                                <div class="card-body">

                                    @if($saleReturnDashboardData['show_total_sale_return_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Returns</span>
                                            <strong>{{ $saleReturnDashboardData['total_sale_return_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($saleReturnDashboardData['show_total_sale_return_qty'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Qty</span>
                                            <strong>{{ number_format($saleReturnDashboardData['total_sale_return_qty'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($saleReturnDashboardData['show_total_sale_return_amount'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Amount</span>
                                            <strong>₹ {{ number_format($saleReturnDashboardData['total_sale_return_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($saleReturnDashboardData['show_sale_return_with_gst'])
                                        <div class="d-flex justify-content-between text-success mb-2">
                                            <span>With GST</span>
                                            <strong>₹ {{ number_format($saleReturnDashboardData['sale_return_with_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($saleReturnDashboardData['show_sale_return_without_gst'])
                                        <div class="d-flex justify-content-between text-primary">
                                            <span>Without GST</span>
                                            <strong>₹ {{ number_format($saleReturnDashboardData['sale_return_without_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- PURCHASE RETURN --}}
                        @if($showPurchaseReturnCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Purchase Return</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- ADD -->
                                         @if($purchaseReturnShowAdd)
                                        @can('action-module',77)
                                        <a href="{{ route('purchase-return.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif

                                        <!-- VIEW -->
                                         @if($purchaseReturnShowView)
                                        @can('action-module',13)
                                        <a href="{{ route('purchase-return.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>

                                <div class="card-body">

                                    @if($purchaseReturnDashboardData['show_total_purchase_return_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Returns</span>
                                            <strong>{{ $purchaseReturnDashboardData['total_purchase_return_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseReturnDashboardData['show_total_purchase_return_qty'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Qty</span>
                                            <strong>{{ number_format($purchaseReturnDashboardData['total_purchase_return_qty'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseReturnDashboardData['show_total_purchase_return_amount'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Amount</span>
                                            <strong>₹ {{ number_format($purchaseReturnDashboardData['total_purchase_return_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseReturnDashboardData['show_purchase_return_with_gst'])
                                        <div class="d-flex justify-content-between text-success mb-2">
                                            <span>With GST</span>
                                            <strong>₹ {{ number_format($purchaseReturnDashboardData['purchase_return_with_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($purchaseReturnDashboardData['show_purchase_return_without_gst'])
                                        <div class="d-flex justify-content-between text-primary">
                                            <span>Without GST</span>
                                            <strong>₹ {{ number_format($purchaseReturnDashboardData['purchase_return_without_gst_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- PAYMENT --}}
                        @if($showPaymentCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Payment</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- ADD -->
                                         @if($paymentShowAdd)
                                        @can('action-module',82)
                                        <a href="{{ route('payment.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif

                                        <!-- VIEW -->
                                         @if($paymentShowView)
                                        @can('action-module',15)
                                        <a href="{{ route('payment.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>

                                <div class="card-body">

                                    @if($paymentDashboardData['show_total_paid_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Paid</span>
                                            <strong>{{ $paymentDashboardData['total_paid_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($paymentDashboardData['show_total_received_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Received</span>
                                            <strong>{{ $paymentDashboardData['total_received_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($paymentDashboardData['show_total_paid_amount'])
                                        <div class="d-flex justify-content-between text-danger mb-2">
                                            <span>Total Paid Amount</span>
                                            <strong>₹ {{ number_format($paymentDashboardData['total_paid_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($paymentDashboardData['show_total_received_amount'])
                                        <div class="d-flex justify-content-between text-success">
                                            <span>Total Received Amount</span>
                                            <strong>₹ {{ number_format($paymentDashboardData['total_received_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- RECEIPT --}}
                        @if($showReceiptCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between">
                                    <h6 class="fw-bold mb-0">Receipt</h6>
                                    <div class="d-flex align-items-center gap-4">
                                        <!-- ADD -->
                                         @if($receiptShowAdd)
                                        @can('action-module',84)
                                        <a href="{{ route('receipt.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif

                                        <!-- VIEW -->
                                         @if($receiptShowView)
                                        @can('action-module',16)
                                        <a href="{{ route('receipt.index') }}" title="View" class="icon-action">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                    <span class="badge bg-light text-dark">Today</span>
                                </div>

                                <div class="card-body">

                                    @if($receiptDashboardData['show_total_received_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Received</span>
                                            <strong>{{ $receiptDashboardData['total_received_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($receiptDashboardData['show_total_paid_count'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Paid</span>
                                            <strong>{{ $receiptDashboardData['total_paid_count'] }}</strong>
                                        </div>
                                    @endif

                                    @if($receiptDashboardData['show_total_received_amount'])
                                        <div class="d-flex justify-content-between text-success mb-2">
                                            <span>Received Amount</span>
                                            <strong>₹ {{ number_format($receiptDashboardData['total_received_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                    @if($receiptDashboardData['show_total_paid_amount'])
                                        <div class="d-flex justify-content-between text-danger">
                                            <span>Paid Amount</span>
                                            <strong>₹ {{ number_format($receiptDashboardData['total_paid_amount'],2) }}</strong>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                        @endif
                        @if($showJournalCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Journal</h6>

                                    <div class="d-flex align-items-center gap-4">
                                        @if($journalShowAdd)
                                        @can('view-module', 80)
                                    <a href="{{ route('journal.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif
                                        @if($journalShowView)
                                        @can('view-module', 14)
                                        <a href="{{ route('journal.index') }}" title="View">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body text-muted">
                                    Manage journal entries
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($showContraCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Contra</h6>

                                    <div class="d-flex align-items-center gap-4">
                                        @if($contraShowAdd)
                                        @can('action-module',75)
                                    <a href="{{ route('contra.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif
                                        @if($contraShowView)
                                        @can('view-module', 29)
                                        <a href="{{ route('contra.index') }}" title="View">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body text-muted">
                                    Manage contra entries
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($showStockJournalCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Stock Journal</h6>

                                    <div class="d-flex align-items-center gap-4">
                                        @if($stockJournalShowAdd)
                                        @can('view-module', 86)
                                    <a href="{{ route('add-stock-journal') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif
                                        @if($stockJournalShowView)
                                        @can('view-module', 30)
                                        <a href="{{ route('stock-journal') }}" title="View">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body text-muted">
                                    Manage stock journal entries
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($showStockTransferCard)
                        <div class="col-md-4">
                            <div class="dashboard-card shadow-sm mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0">Stock Transfer</h6>

                                    <div class="d-flex align-items-center gap-4">
                                        @if($stockTransferShowAdd)
                                        @can('view-module', 87)
                                    <a href="{{ route('stock-transfer.create') }}" title="Add" class="icon-action">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                width="18" height="18"
                                                viewBox="0 0 20 20"
                                                fill="none">
                                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                                        fill="#000"/>
                                            </svg>
                                        </a>
                                        @endcan
                                        @endif
                                        @if($stockTransferShowView)
                                        @can('view-module', 31)
                                        <a href="{{ route('stock-transfer.index') }}" title="View">
                                            <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                                width="18" alt="View">
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body text-muted">
                                    Manage stock transfer entries
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>                    
                </div>
            </div>
        </section>
    </div>
</body>
@include('layouts.footer')
</html>
@endsection
