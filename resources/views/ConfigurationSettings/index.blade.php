@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mb-4">
                    <h5 class="master-table-title m-0 py-2">Configuration Settings</h5>
                </div>

                <form method="POST" action="{{ route('configuration.settings.save') }}">
                    @csrf
                    {{-- SALES CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Sales</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="sales">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="sales">[+]</span>

                                <strong class="ms-2">Sales</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-sales"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="sales"
                                    name="sales_actions[add]"
                                    value="1"
                                        {{ ($selectedSalesActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="sales"
                                    name="sales_actions[view]"
                                    value="1"
                                        {{ ($selectedSalesActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sales"
                                        name="sales[]"
                                        value="total_sales_count"
                                        {{ ($selectedSales['total_sales_count'] ?? false) ? 'checked' : '' }}>
                                    Total Sales (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sales"
                                        name="sales[]"
                                        value="total_sales_qty"
                                        {{ ($selectedSales['total_sales_qty'] ?? false) ? 'checked' : '' }}>
                                    Total Sales Quantity
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sales"
                                        name="sales[]"
                                        value="total_sales_amount"
                                        {{ ($selectedSales['total_sales_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Sales Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sales"
                                        name="sales[]"
                                        value="sales_with_gst_amount"
                                        {{ ($selectedSales['sales_with_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Sales With GST (Amount)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sales"
                                        name="sales[]"
                                        value="sales_without_gst_amount"
                                        {{ ($selectedSales['sales_without_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Sales Without GST (Amount)
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    {{-- PURCHASE CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Purchase</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="purchase">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="purchase">[+]</span>

                                <strong class="ms-2">Purchase</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-purchase"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="purchase"
                                    name="purchase_actions[add]"
                                    value="1"
                                        {{ ($selectedPurchaseActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="purchase"
                                    name="purchase_actions[view]"
                                    value="1"
                                        {{ ($selectedPurchaseActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase"
                                        name="purchase[]"
                                        value="total_purchase_count"
                                        {{ ($selectedPurchase['total_purchase_count'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase"
                                        name="purchase[]"
                                        value="total_purchase_qty"
                                        {{ ($selectedPurchase['total_purchase_qty'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase Quantity
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase"
                                        name="purchase[]"
                                        value="total_purchase_amount"
                                        {{ ($selectedPurchase['total_purchase_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase"
                                        name="purchase[]"
                                        value="purchase_with_gst_amount"
                                        {{ ($selectedPurchase['purchase_with_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Purchase With GST (Amount)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase"
                                        name="purchase[]"
                                        value="purchase_without_gst_amount"
                                        {{ ($selectedPurchase['purchase_without_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Purchase Without GST (Amount)
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- SALE RETURN CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Sale Return</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="sale_return">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="sale_return">[+]</span>

                                <strong class="ms-2">Sale Return</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-sale_return"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="sale_return"
                                    name="sale_return_actions[add]"
                                    value="1"
                                        {{ ($selectedSaleReturnActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="sale_return"
                                    name="sale_return_actions[view]"
                                    value="1"
                                        {{ ($selectedSaleReturnActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sale_return"
                                        name="sale_return[]"
                                        value="total_sale_return_count"
                                        {{ ($selectedSaleReturn['total_sale_return_count'] ?? false) ? 'checked' : '' }}>
                                    Total Sale Return (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sale_return"
                                        name="sale_return[]"
                                        value="total_sale_return_qty"
                                        {{ ($selectedSaleReturn['total_sale_return_qty'] ?? false) ? 'checked' : '' }}>
                                    Total Sale Return Quantity
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sale_return"
                                        name="sale_return[]"
                                        value="total_sale_return_amount"
                                        {{ ($selectedSaleReturn['total_sale_return_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Sale Return Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sale_return"
                                        name="sale_return[]"
                                        value="sale_return_with_gst_amount"
                                        {{ ($selectedSaleReturn['sale_return_with_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Sale Return With GST (Amount)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="sale_return"
                                        name="sale_return[]"
                                        value="sale_return_without_gst_amount"
                                        {{ ($selectedSaleReturn['sale_return_without_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Sale Return Without GST (Amount)
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- PURCHASE RETURN CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Purchase Return</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="purchase_return">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="purchase_return">[+]</span>

                                <strong class="ms-2">Purchase Return</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-purchase_return"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="purchase_return"
                                    name="purchase_return_actions[add]"
                                    value="1"
                                        {{ ($selectedPurchaseReturnActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="purchase_return"
                                    name="purchase_return_actions[view]"
                                    value="1"
                                        {{ ($selectedPurchaseReturnActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase_return"
                                        name="purchase_return[]"
                                        value="total_purchase_return_count"
                                        {{ ($selectedPurchaseReturn['total_purchase_return_count'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase Return (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase_return"
                                        name="purchase_return[]"
                                        value="total_purchase_return_qty"
                                        {{ ($selectedPurchaseReturn['total_purchase_return_qty'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase Return Quantity
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase_return"
                                        name="purchase_return[]"
                                        value="total_purchase_return_amount"
                                        {{ ($selectedPurchaseReturn['total_purchase_return_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Purchase Return Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase_return"
                                        name="purchase_return[]"
                                        value="purchase_return_with_gst_amount"
                                        {{ ($selectedPurchaseReturn['purchase_return_with_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Purchase Return With GST (Amount)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="purchase_return"
                                        name="purchase_return[]"
                                        value="purchase_return_without_gst_amount"
                                        {{ ($selectedPurchaseReturn['purchase_return_without_gst_amount'] ?? false) ? 'checked' : '' }}>
                                    Purchase Return Without GST (Amount)
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- PAYMENT CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Payment</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="payment">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="payment">[+]</span>

                                <strong class="ms-2">Payment</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-payment"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="payment"
                                    name="payment_actions[add]"
                                    value="1"
                                        {{ ($selectedPaymentActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="payment"
                                    name="payment_actions[view]"
                                    value="1"
                                        {{ ($selectedPaymentActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="payment"
                                        name="payment[]"
                                        value="total_paid_count"
                                        {{ ($selectedPayment['total_paid_count'] ?? false) ? 'checked' : '' }}>
                                    Total Paid (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="payment"
                                        name="payment[]"
                                        value="total_received_count"
                                        {{ ($selectedPayment['total_received_count'] ?? false) ? 'checked' : '' }}>
                                    Total Received (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="payment"
                                        name="payment[]"
                                        value="total_paid_amount"
                                        {{ ($selectedPayment['total_paid_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Paid Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="payment"
                                        name="payment[]"
                                        value="total_received_amount"
                                        {{ ($selectedPayment['total_received_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Received Amount
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- RECEIPT CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Receipt</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="receipt">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="receipt">[+]</span>

                                <strong class="ms-2">Receipt</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-receipt"
                                style="display:none;">
                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="receipt"
                                    name="receipt_actions[add]"
                                    value="1"
                                        {{ ($selectedReceiptActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="receipt"
                                    name="receipt_actions[view]"
                                    value="1"
                                        {{ ($selectedReceiptActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                                <hr>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="receipt"
                                        name="receipt[]"
                                        value="total_received_count"
                                        {{ ($selectedReceipt['total_received_count'] ?? false) ? 'checked' : '' }}>
                                    Total Received (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="receipt"
                                        name="receipt[]"
                                        value="total_paid_count"
                                        {{ ($selectedReceipt['total_paid_count'] ?? false) ? 'checked' : '' }}>
                                    Total Paid (Count)
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="receipt"
                                        name="receipt[]"
                                        value="total_received_amount"
                                        {{ ($selectedReceipt['total_received_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Received Amount
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="receipt"
                                        name="receipt[]"
                                        value="total_paid_amount"
                                        {{ ($selectedReceipt['total_paid_amount'] ?? false) ? 'checked' : '' }}>
                                    Total Paid Amount
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- JOURNAL CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Journal</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="journal">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="journal">[+]</span>

                                <strong class="ms-2">Journal</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-journal"
                                style="display:none;">

                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="journal"
                                        name="journal_actions[add]"
                                        value="1"
                                        {{ ($selectedJournalActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="journal"
                                        name="journal_actions[view]"
                                        value="1"
                                        {{ ($selectedJournalActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CONTRA CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Contra</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="contra">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="contra">[+]</span>

                                <strong class="ms-2">Contra</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-contra"
                                style="display:none;">

                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="contra"
                                        name="contra_actions[add]"
                                        value="1"
                                        {{ ($selectedContraActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="contra"
                                        name="contra_actions[view]"
                                        value="1"
                                        {{ ($selectedContraActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- STOCK JOURNAL CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Stock Journal</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="stock_journal">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="stock_journal">[+]</span>

                                <strong class="ms-2">Stock Journal</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-stock_journal"
                                style="display:none;">

                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="stock_journal"
                                    name="stock_journal_actions[add]"
                                    value="1"
                                    {{ ($selectedStockJournalActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="stock_journal"
                                        name="stock_journal_actions[view]"
                                        value="1"
                                        {{ ($selectedStockJournalActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- STOCK TRANSFER CONFIGURATION --}}
                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Stock Transfer</h6>

                        <div class="mb-2 border-bottom pb-2">
                            <div class="d-flex align-items-center group-label">
                                <input type="checkbox"
                                    class="group-checkbox me-2"
                                    data-group="stock_transfer">

                                <span class="toggle-items ms-2 cursor-pointer"
                                    data-group="stock_transfer">[+]</span>

                                <strong class="ms-2">Stock Transfer</strong>
                            </div>

                            <div class="ms-4 items-list"
                                id="group-stock_transfer"
                                style="display:none;">

                                <div class="fw-bold text-decoration-underline mb-2">Actions</div>

                                <div>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        data-group="stock_transfer"
                                        name="stock_transfer_actions[add]"
                                        value="1"
                                        {{ ($selectedStockTransferActions['add'] ?? false) ? 'checked' : '' }}>
                                    Show Add Button
                                </div>

                                <div>
                                    <input type="checkbox"
                                    class="item-checkbox"
                                    data-group="stock_transfer"
                                    name="stock_transfer_actions[view]"
                                    value="1"
                                    {{ ($selectedStockTransferActions['view'] ?? false) ? 'checked' : '' }}>
                                    Show View Button
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-start">
                        <button type="submit" class="btn btn-primary px-4">
                            Save Settings
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<style>
.group-label strong {
    font-size: 1.5rem;
}
.toggle-items {
    font-size: 1.5rem;
    cursor: pointer;
}
.items-list div {
    font-size: 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.toggle-items').forEach(function(toggle) {
        toggle.addEventListener('click', function () {
            let groupId = this.dataset.group;
            let itemsDiv = document.getElementById('group-' + groupId);

            if (itemsDiv.style.display === 'none') {
                itemsDiv.style.display = 'block';
                this.textContent = '[-]';
            } else {
                itemsDiv.style.display = 'none';
                this.textContent = '[+]';
            }
        });
    });

    function updateGroupCheckbox(groupId) {
        let itemCheckboxes = document.querySelectorAll(`#group-${groupId} .item-checkbox`);
        let groupCheckbox = document.querySelector(`.group-checkbox[data-group="${groupId}"]`);

        let allChecked = [...itemCheckboxes].every(cb => cb.checked);
        let someChecked = [...itemCheckboxes].some(cb => cb.checked);

        groupCheckbox.checked = allChecked;
        groupCheckbox.indeterminate = !allChecked && someChecked;
    }

    document.querySelectorAll('.item-checkbox').forEach(function(itemCb) {
        itemCb.addEventListener('change', function() {
            updateGroupCheckbox(this.dataset.group);
        });
    });

    document.querySelectorAll('.group-checkbox').forEach(function(groupCb) {
        groupCb.addEventListener('change', function() {
            let groupId = this.dataset.group;
            document.querySelectorAll(`#group-${groupId} .item-checkbox`)
                .forEach(cb => cb.checked = groupCb.checked);
            updateGroupCheckbox(groupId);
        });
    });

    document.querySelectorAll('.group-checkbox').forEach(function(groupCb) {
        updateGroupCheckbox(groupCb.dataset.group);
    });
});
</script>
@endsection
