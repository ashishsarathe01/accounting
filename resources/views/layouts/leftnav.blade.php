<!-- accordion -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
   .report-menu-item {
  border: 1px solid #007bff;
  border-radius: 4px;
  padding: 12px 8px;
  margin: 0;
  background-color: white;
  font-size: 14px;
  font-weight: 500;
}
.py-1{
   border: 1px solid #007bff;
    border-radius: 5px;
    padding: 12px 8px;
}

      /* ðŸ‘‡ Hover effect on table rows */
.clickable-row:hover {
    background-color: rgb(254, 254, 254) !important;
}

.clickable-row:hover {
    color: rgb(0, 14, 79) !important;
}

.clickable-row:hover a {
    color: rgb(0, 14, 79) !important;
}

.clickable-row-blue:hover {
    background-color: #cce5ff !important; /* Light blue */
    cursor: pointer;
}
</style>
<aside class="col-lg-2  bg-blue sidebar p-0">
{{-- <aside class="col-lg-2 d-none d-lg-block bg-blue sidebar p-0"> --}}
   <div class="sidebar-sticky ">
      <div id="accordion">
         <div class="card rounded-0 bg-blue py-20 px-2 border-bottom-divider">
            <div class="card-header p-0 border-0 rounded-0 d-flex p-0 border-0" id="dashboardHeading">
               <img src="{{ URL::asset('public/assets/imgs/dashboard.svg')}}" alt="">
               <a class="nav-link text-white fw-500 font-14 ms-2 p-0" href="{{ route('dashboard') }}">Dashboard</a>
            </div>
         </div>
         
         @can('view-module', 1)
            <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                  <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="companyHeading">
                     <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#companyCollapse" aria-expanded="true" aria-controls="companyCollapse"><img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Company</a>
                  </div>
                  <div id="companyCollapse" class="collapse" aria-labelledby="companyHeading" data-bs-parent="#accordion">
                     <ul class="nav flex-column">
                        @can('view-module', 17)
                        <a href="{{ route('add-company') }}">
                           <li class="font-14  fw-500 m-0 py-12 px-2 text-white  border-radius-4 clickable-row">Add Company</li>
                        </a>
                     @endcan
                     <?php
                     if (Session::get('user_company_id') != '') { ?>
                        @can('view-module', 18)
                           <a href="{{ route('view-company') }}">
                              <li class="font-14 text-white fw-500 m-0 py-12 px-2 clickable-row"> View Company</li>
                           </a>
                        @endcan
                        @can('view-module', 20)
                           <a href="{{ route('manage-merchant-employee.index') }}">
                              <li class="font-14 text-white fw-500 m-0 py-12 px-2 clickable-row"> Manage User</li>
                           </a>
                        @endcan
                       
                        <?php 
                     } ?>
                     </ul>
                  </div>
               </div>
         @endcan
         <?php
         if(Session::get('user_company_id') != ''){?>
            @can('view-module', 21)
               <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                  <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="administratorHeading">
                     <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#adminCollapse" aria-expanded="true" aria-controls="adminCollapse"><img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Master</a>
                  </div>
                  <div id="adminCollapse" class="collapse" aria-labelledby="administratorHeading" data-bs-parent="#accordion">
                     <ul class="nav flex-column">
                      <!--  @can('view-module', 2)
                           <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4  clickable-row">
                              <a class=" text-decoration-none  d-flex  text-white " href="{{ route('account-heading.index') }}">
                                          Account Heading
                              </a>
                           </li>
                        @endcan-->
                        @can('view-module', 5)
                           <li class="font-14  fw-500 m-0 py-12 px-2  clickable-row">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account.index') }}">
                                 Account
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 3)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-group.index') }}">
                                          Account Group
                              </a>
                           </li>
                        @endcan
                         @can('view-module', 8)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-manage-item.index') }}">
                                          Manage Item
                              </a>
                           </li>
                        @endcan
                         @can('view-module', 7)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-item-group.index') }}">
                                          Item Group
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 6)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex  text-white  " href="{{ route('account-unit.index') }}">
                                          Manage Unit
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 9)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex    text-white" href="{{ route('account-bill-sundry.index') }}">
                                          Bill Sundry
                              </a>
                           </li>
                        @endcan
                        <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row " >
                              <a class=" text-decoration-none  d-flex    text-white" href="{{ route('account.bulk.update') }}">
                                          Account Bulk Update
                              </a>
                           </li>
                        <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                           <div class="card-header py-12 px-2 border-0 rounded-0 d-flex " id="importMasterDataHeading">
                              <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#importMasterDataCollapse" aria-expanded="true" aria-controls="importMasterDataCollapse">
                              <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">Import Master Data
                              </a>
                           </div>
                           <div id="importMasterDataCollapse" class="collapse" aria-labelledby="importMasterDataHeading" data-bs-parent="#importMasterDataHeading">
                              <ul class="nav flex-column">
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('account-group-import-view') }}">Import Account Group</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-account-view') }}">Import Account</a>
                                 </li>
                                 <!-- <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-account-view') }}">Import Item Group</a>
                                 </li> -->
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('item-import-view') }}">Import Item</a>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </ul>
                  </div>
               </div>
            @endcan
            <!-- Transactions -->
            @can('view-module', 22)
               <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                  <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="transactionsHeading">
                     <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#transactonCollapse" aria-expanded="true" aria-controls="transactonCollapse">
                        <img src="{{ URL::asset('public/assets/imgs/transactions.svg')}}" class="me-2" alt="">
                              Transactions
                     </a>
                  </div>
                  <div id="transactonCollapse" class="collapse" aria-labelledby="transactionsHeading" data-bs-parent="#accordion">
                     <ul class="nav flex-column">
                        @can('view-module', 10)
                           <li class="font-14  fw-500 m-0 py-12 px-2   border-radius-4 clickable-row">
                              <a class=" text-decoration-none  d-flex  text-white " href="{{ route('sale.index') }}">
                                          Sales
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 11)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('purchase.index') }}">
                                          Purchase
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 12)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('sale-return.index') }}">
                                          Credit Note
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 13)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('purchase-return.index') }}">
                                          Debit Note
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 15)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('payment.index') }}">
                                          Payment
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 16)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('receipt.index') }}">
                                          Receipt
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 14)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('journal.index') }}">
                                          Journal
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 29)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('contra.index') }}">
                                          Contra
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 30)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('stock-journal') }}">
                                          Stock Journal
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 31)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('stock-transfer.index') }}">
                                 Stock Transfer
                              </a>
                           </li>
                        @endcan
                        @can('view-module', 246)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#accountProductionSubmenu"
                              aria-expanded="false"
                              aria-controls="accountProductionSubmenu"> 
                              Production
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="accountProductionSubmenu">
                              <ul class="nav flex-column">
                                 <li class="py-1 clickable-row-blue">
                                    <a href="{{ route('account-production.index') }}" class="text-blue">Manage Production</a>
                                 </li>
                              </ul>
                           </div>
                        </li>
                         @endcan
                        <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                           <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="importDataHeading">
                              <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#importDataCollapse" aria-expanded="true" aria-controls="importDataCollapse">
                              <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">Import Data
                              </a>
                           </div>
                           <div id="importDataCollapse" class="collapse" aria-labelledby="importDataHeading" data-bs-parent="#importDataHeading">
                              <ul class="nav flex-column">
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('sale-import-view') }}">Import Sale</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('purchase-import-view') }}">Import Purchase</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('payment-import-view') }}">Import Payment</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('receipt-import-view') }}">Import Receipt</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('journal-import-view') }}">Import Journal</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('contra-import-view') }}">Import Contra</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('debit-note-import-view') }}">Import Debit Note</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('credit-note-import-view') }}">Import Credit Note</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-stock-transfer-view') }}">Import Stock Transfer</a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-stock-journal-view') }}">Import Stock Journal</a>
                                 </li>
                              </ul>
                           </div>
                        </div>
                        <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                           <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="exportDataHeading">
                              <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#"
                                 data-bs-toggle="collapse" data-bs-target="#exportDataCollapse"
                                 aria-expanded="true" aria-controls="exportDataCollapse">
                                 <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                                 Export Data
                              </a>
                           </div>

                           <div id="exportDataCollapse" class="collapse" aria-labelledby="exportDataHeading" data-bs-parent="#exportDataHeading">
                              <ul class="nav flex-column">
                                 <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('sale-export-view') }}">
                                       Export Sale Challan
                                    </a>
                                 </li>
                                 <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue mt-2">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('sale-bill-export-view') }}">
                                       Export Sale Bill
                                    </a>
                                 </li>
                                    <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('purchase-export-view') }}">
                                       Export Purchase Challan
                                    </a>
                                 </li>

                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('purchase-bill-export-view') }}">
                                       Export Purchase Bill
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('payment-export-view') }}">
                                       Export Payments
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('receipt-export-view') }}">
                                       Export Receipts
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('sale-return-export-view') }}">
                                       Export Sale Return
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('purchase-return-export-view') }}">
                                       Export Purchase Return
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('contra-export-view') }}">
                                       Export Contra
                                    </a>
                                 </li>
                                 <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4 clickable-row-blue">
                                    <a class="text-decoration-none d-flex text-blue" href="{{ route('journal.export.view') }}">
                                       Export Journal
                                    </a>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </ul>
                  </div>
               </div>
            @endcan
            <!-- Display -->
            @can('view-module', 23)
               <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                  <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="displayHeading">
                     <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#displayCollapse" aria-expanded="true" aria-controls="displayCollapse">
                        <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                              Display
                     </a>
                  </div>
                  <div id="displayCollapse" class="collapse" aria-labelledby="displayHeading" data-bs-parent="#accordion">
                     <ul class="nav flex-column">
                         @can('view-module', 137)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#financialSubmenu"
                              aria-expanded="false"
                              aria-controls="financialSubmenu"> 
                              Financial Reports
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="financialSubmenu">
                              <ul class="nav flex-column">
                                 @can('view-module', 24)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('balancesheet.index') }}" class="text-blue">Balance Sheet</a>
                                    </li>
                                 @endcan
                                 @can('view-module', 25)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('profitloss.index') }}" class="text-blue">Profit & Loss</a>
                                    </li>
                                 @endcan
                                 @can('view-module', 32)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('trialbalance.index') }}" class="text-blue">Trial Balance</a>
                                    </li>
                                 @endcan
                              </ul>
                           </div>
                        </li>
                        @endcan
                        @can('view-module', 138)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue " style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed "
                              href="#"
                              role="button"
                              tabindex="0"
                              data-bs-toggle="collapse"
                              data-bs-target="#statutorySubmenu"
                              aria-expanded="false"
                              aria-controls="statutorySubmenu">
                              Statutory Reports
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="statutorySubmenu">
                              <ul class="nav flex-column">
                                   @can('view-module', 139)
                                 <li class="report-menu-item">
                                    <a class="text-decoration-none d-flex text-blue collapsed"
                                       href="#"
                                       data-bs-toggle="collapse"
                                       data-bs-target="#gstReportSubmenu"
                                       aria-expanded="false"
                                       aria-controls="gstReportSubmenu">
                                       GST Reports
                                       <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                    </a>
                                    <!-- Submenu under Balance Sheet -->
                                    <div class="collapse ps-3" id="gstReportSubmenu">
                                       <ul class="nav flex-column">
                                            @can('view-module', 140)
                                          <li class="report-menu-item">
                                             <a class="text-decoration-none d-flex text-blue collapsed"
                                                href="#"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#returnsSubmenu"
                                                aria-expanded="false"
                                                aria-controls="returnsSubmenu">
                                                Returns
                                                <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                             </a>
                                             <div class="collapse ps-3" id="returnsSubmenu">
                                                <ul class="nav flex-column">
                                                   @can('view-module', 28)
                                                      <li class="py-1" style="">
                                                         <a href="{{ route('report.filter.data') }}" class="text-blue">GSTR-1</a>
                                                      </li>
                                                   @endcan
                                                     @can('view-module', 142)
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('gst2a') }}" class="text-blue">GSTR-2A</a>
                                                   </li>
                                                   @endcan
                                                     @can('view-module', 143)
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('gst2b') }}" class="text-blue">GSTR-2B</a>
                                                   </li>
                                                   @endcan
                                                     @can('view-module', 144)
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('report.filter.data.3b') }}" class="text-blue">GSTR-3B</a>
                                                   </li>
                                                   @endcan
                                                     @can('view-module', 145)
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-9</a>
                                                   </li>
                                                   @endcan
                                                     @can('view-module', 146)
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-9C</a>
                                                   </li>
                                                   @endcan
                                                </ul>
                                             </div>
                                          </li>
                                          @endcan
                                           @can('view-module', 180)
                                          <li class="report-menu-item">
                                             <a class="text-decoration-none d-flex text-blue collapsed"
                                                href="#"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#reconsiliationSubmenu"
                                                aria-expanded="false"
                                                aria-controls="reconsiliationSubmenu">
                                                Reconciliation
                                                <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                             </a>
                                             <div class="collapse ps-3" id="reconsiliationSubmenu">
                                                <ul class="nav flex-column">
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-1 Reconciliation</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-2A Reconciliation</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-2B Reconciliation</a>
                                                   </li>
                                                </ul>
                                             </div>
                                          </li>
                                          @endcan
                                           @can('view-module', 181)
                                          <li class="report-menu-item">
                                             <a href="javascript:void(0)" class="text-blue">Credit Ledger Balance</a>
                                          </li>
                                          @endcan
                                           @can('view-module', 182)
                                          <li class="report-menu-item">
                                             <a href="javascript:void(0)" class="text-blue">Challan</a>
                                          </li>
                                          @endcan
                                           @can('view-module', 183)
                                          <li class="report-menu-item">
                                             <a class="text-decoration-none d-flex text-blue collapsed"
                                                href="#"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#otherReportsSubmenu"
                                                aria-expanded="false"
                                                aria-controls="otherReportsSubmenu">
                                                Other Reports
                                                <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                             </a>
                                             <div class="collapse ps-3" id="otherReportsSubmenu">
                                                <ul class="nav flex-column">
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">Track Return Activities</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">Interest On Late Payment Of GST</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">Late Return Filling Fee</a>
                                                   </li>
                                                </ul>
                                             </div>
                                          </li>
                                          @endcan
                                       </ul>
                                    </div>
                                 </li>
                                 @endcan
                                 @can('view-module', 147)
                                    <li class="report-menu-item">
                                    <a class="text-decoration-none d-flex text-blue collapsed"
                                       href="#"
                                       data-bs-toggle="collapse"
                                       data-bs-target="#tdsTcsSubmenu"
                                       aria-expanded="false"
                                       aria-controls="tdsTcsSubmenu">
                                       TDS/TCS Reports
                                       <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                    </a>
                                    <!-- Submenu under Balance Sheet -->
                                    <div class="collapse ps-3" id="tdsTcsSubmenu">
                                       <ul class="nav flex-column">
                                          
                                       </ul>
                                    </div>
                                 </li>
                                  @endcan
                                 @can('view-module', 148)
                                 <li class="report-menu-item">
                                    <a class="text-decoration-none d-flex text-blue collapsed"
                                       href="#"
                                       data-bs-toggle="collapse"
                                       data-bs-target="#esicSubmenu"
                                       aria-expanded="false"
                                       aria-controls="esicSubmenu">
                                       ESIC Reports
                                       <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                    </a>
                                    <!-- Submenu under Balance Sheet -->
                                    <div class="collapse ps-3" id="esicSubmenu">
                                       <ul class="nav flex-column">
                                          
                                       </ul>
                                    </div>
                                 </li>
                                 @endcan
                                 @can('view-module', 149)
                                 <li class="report-menu-item">
                                    <a class="text-decoration-none d-flex text-blue collapsed"
                                       href="#"
                                       data-bs-toggle="collapse"
                                       data-bs-target="#pfSubmenu"
                                       aria-expanded="false"
                                       aria-controls="pfSubmenu">
                                       PF Reports
                                       <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                    </a>
                                    <!-- Submenu under Balance Sheet -->
                                    <div class="collapse ps-3" id="pfSubmenu">
                                       <ul class="nav flex-column">
                                          
                                       </ul>
                                    </div>
                                    </li>
                                 @endcan
                              </ul>
                           </div>
                        </li>
                     @endcan
                        @can('view-module', 150)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#accountBookSubmenu"
                              aria-expanded="false"
                              aria-controls="accountBookSubmenu">
                              Account Book
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="accountBookSubmenu">
                              <ul class="nav flex-column">
                                 @can('view-module', 26)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('accountledger.index') }}" class="text-blue">Account Ledger</a>
                                    </li>
                                       @endcan
                                    @can('view-module', 141) 
                                       <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('salebook.index') }}" class="text-blue">Sale Register</a>
                                    </li>
                                    @endcan
                                    @can('view-module', 151)
                                       <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('purchasebook.index') }}" class="text-blue">Purchase Register</a>
                                    </li>
                                 @endcan
                                 <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('account.summary') }}" class="text-blue">Account Summary</a>
                                    </li>
                              </ul>
                           </div>
                        </li>
                        @endcan
                        @can('view-module', 152)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#inventorySubmenu"
                              aria-expanded="false"
                              aria-controls="inventorySubmenu">
                              Inventory Reports
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="inventorySubmenu">
                              <ul class="nav flex-column">
                                 @can('view-module', 27)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('itemledger.index') }}" class="text-blue">Items Ledger</a>
                                    </li>
                                 @endcan
                                 @can('view-module', 153)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('parameterized-stock') }}" class="text-blue">Parameterized Stock</a>
                                    </li>
                                    @endcan
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('item-summary.index') }}" class="text-blue">Items Summary</a>
                                    </li>
                                    
                              </ul>
                           </div>
                        </li>
                   @endcan
                        @can('view-module', 154)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#receivablesPayablesSubmenu"
                              aria-expanded="false"
                              aria-controls="receivablesPayablesSubmenu">
                              Receivables & Payables
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                              <div class="collapse ps-3" id="receivablesPayablesSubmenu">
                              <ul class="nav flex-column">
                                   @can('view-module', 155)
                                  <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('receiable.index') }}" class="text-blue">Receiable Report</a>
                                    </li>
                                    @endcan
                                     @can('view-module', 156)
                                     <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('payable.index') }}" class="text-blue">Payable Report</a>
                                    </li>
                                    @endcan
                                     @can('view-module', 157)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('AgingReport') }}" class="text-blue">Aging Report</a>
                                    </li>
                                    @endcan
                              </ul>
                           </div>
                        </li>
                        @endcan
                        @can('view-module', 158)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#auditControlSubmenu"
                              aria-expanded="false"
                              aria-controls="auditControlSubmenu">
                              Audit & Control Reports
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="auditControlSubmenu">
                              <ul class="nav flex-column">
                                @can('view-module', 250)
                                    <li class="py-1 clickable-row-blue">
                                       <a class="text-decoration-none d-flex text-blue"
                                          href="{{ url('activity-logs') }}">
                                          Activity Logs
                                       </a>
                                    </li> 
                                @endcan
                                @can('view-module', 251)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('business.activity.logs') }}" class="text-blue">
                                          Business Activity Logs
                                       </a>
                                    </li>
                                @endcan
                                @can('view-module', 252)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('transaction.report') }}" class="text-blue">
                                          Transactions Approval
                                       </a>
                                    </li>
                                @endcan
                                @can('view-module', 253)
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ url('transaction-integrity') }}" class="text-blue">
                                          Transactions Integrity
                                       </a>
                                    </li>
                                @endcan
                              </ul>
                           </div>
                        </li>
                       @endcan
                       @can('view-module', 159)
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#hrpayrollSubmenu"
                              aria-expanded="false"
                              aria-controls="hrpayrollSubmenu">
                              HR & Payroll
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="hrpayrollSubmenu">
                              <ul class="nav flex-column">
                                 <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('payroll.index') }}" class="text-blue">Manage Payroll Heads</a>
                                          </li>
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('payroll.register') }}" class="text-blue">Payroll Sheet</a>
                                          </li>
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('payroll.esic') }}" class="text-blue">Manage Esic</a>
                                          </li>
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('payroll.pf') }}" class="text-blue">Manage Pf</a>
                                          </li>
                              </ul>
                           </div>
                        </li>
                        @endcan
                     </ul>
                  </div>
               </div>               
            @endcan
            @can('module-permission',1)
               @can('view-module', 94)
                  <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                     <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="displayHeading">
                        <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#businessCollapse" aria-expanded="true" aria-controls="businessCollapse">
                           <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                                 Business Management
                        </a>
                     </div>                     
                     <div id="businessCollapse" class="collapse" aria-labelledby="displayHeading" data-bs-parent="#accordion">
                        <ul class="nav flex-column">
                           @can('view-module', 95)
                           <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">

                              <!-- Parent Toggle -->
                              <a class="text-decoration-none d-flex text-blue collapsed"
                                 href="#"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#purchaseSubmenu"
                                 aria-expanded="false"
                                 aria-controls="purchaseSubmenu">
                                 Purchase Management
                                 <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                              </a>

                              <div class="collapse ps-3" id="purchaseSubmenu">
                                 <ul class="nav flex-column">

                                       {{-- Manage Sub Head --}}
                                       @can('view-module', 97)
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('supplier-sub-head.index') }}" class="text-blue">Manage Sub Head</a>
                                          </li>
                                       @endcan
                                       @can('view-module', 100)
                                          {{-- Add Purchase Vehicle Entry --}}
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('add-purchase-info') }}" class="text-blue">
                                                Add Vehicle Entry
                                             </a>
                                          </li>
                                        @endcan
                                        @can('view-module', 160)
                                       {{--MANAGE WASTE KRAFT--}}
                                       <li class="py-1 clickable-row-blue">
                                          <a class="text-decoration-none d-flex text-blue collapsed"
                                             href="#"
                                             data-bs-toggle="collapse"
                                             data-bs-target="#wasteKraftMenu"
                                             aria-expanded="false"
                                             aria-controls="wasteKraftMenu">
                                             Manage Waste Kraft
                                             <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                          </a>

                                          <div class="collapse ps-3" id="wasteKraftMenu">
                                             <ul class="nav flex-column">
                                                 @can('view-module', 163)
                                                <li class="py-1">
                                                   <a href="{{ route('supplier.waste_kraft') }}" class="text-blue">Manage Purchase</a>
                                                </li>
                                                @endcan
                                                @can('view-module', 98)
                                                   <li class="py-1">
                                                      <a href="{{ route('supplier.waste_kraft_supplier') }}" class="text-blue">Manage Supplier</a>
                                                   </li>
                                                   @endcan
                                                @can('view-module', 99)
                                                   <li class="py-1">
                                                      <a href="{{ route('manage-supplier-rate.wastekraft') }}" class="text-blue">Manage Rate</a>
                                                   </li>
                                                   @endcan
                                                   @can('view-module', 164)
                                                   <li class="py-1">
                                                      <a href="{{ route('wastekraft-purchase-report') }}" class="text-blue">Report</a>
                                                   </li>
                                                   @endcan
                                             </ul>
                                          </div>
                                       </li>
                                       @endcan
                                        @can('view-module', 161)
                                       {{--MANAGE BOILER FUEL--}}
                                       <li class="py-1 clickable-row-blue">
                                          <a class="text-decoration-none d-flex text-blue collapsed"
                                             href="#"
                                             data-bs-toggle="collapse"
                                             data-bs-target="#boilerFuelMenu"
                                             aria-expanded="false"
                                             aria-controls="boilerFuelMenu">
                                             Manage Boiler Fuel
                                             <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                          </a>

                                          <div class="collapse ps-3" id="boilerFuelMenu">
                                             <ul class="nav flex-column">
                                                  @can('view-module', 165)
                                                <li class="py-1">
                                                   <a href="{{ route('supplier.boiler_fuel') }}" class="text-blue">Manage Purchase</a>
                                                </li>
                                                @endcan
                                                 @can('view-module', 166)
                                                   <li class="py-1">
                                                      <a href="{{ route('supplier.boiler_fuel_supplier') }}" class="text-blue">Manage Supplier</a>
                                                   </li>
                                                   @endcan
                                                     @can('view-module', 167)
                                                   <li class="py-1">
                                                      <a href="{{ route('manage-supplier-rate.boilerfuel') }}" class="text-blue">Manage Rate</a>
                                                   </li>
                                                   @endcan
                                                    @can('view-module', 168)
                                                   <li class="py-1">
                                                      <a href="{{ route('boilerfuel-purchase-report') }}" class="text-blue">Report</a>
                                                   </li>
                                                   @endcan
                                             </ul>
                                          </div>
                                       </li>
                                        @endcan
                                        @can('view-module', 162)
                                       {{--MANAGE SPARE PART--}}
                                       <li class="py-1 clickable-row-blue">
                                          <a class="text-decoration-none d-flex text-blue collapsed"
                                             href="#"
                                             data-bs-toggle="collapse"
                                             data-bs-target="#sparePartMenu"
                                             aria-expanded="false"
                                             aria-controls="sparePartMenu">
                                             Manage Spare Part
                                             <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                          </a>

                                          <div class="collapse ps-3" id="sparePartMenu">
                                             <ul class="nav flex-column">
                                                  @can('view-module', 169)
                                                   <li class="py-1">
                                                      <a href="{{ route('spare-part.index') }}" class="text-blue">Manage Purchase</a>
                                                   </li>
                                                   @endcan
                                                   <li class="py-1">
                                                      <a href="{{ route('spare-part.suppliers') }}" class="text-blue">Manage Supplier</a>
                                                   </li>
                                                    @can('view-module', 170)
                                                   <li class="py-1">
                                                      <a href="{{ route('spare-part.items') }}" class="text-blue">
                                                         Manage Item
                                                      </a>
                                                   </li>
                                                   @endcan
                                                   <li class="py-1 clickable-row-blue">
                                                      <a href="{{ route('supplier.sparepart.configuration') }}" class="text-blue">
                                                         Configuration
                                                      </a>
                                                   </li>
                                                    
                                             </ul>
                                          </div>
                                       </li>
                                       @endcan
                                       @can('view-module', 101)
                                        <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('supplier.purchase.reports.dashboard') }}" class="text-blue">
                                            Daily Report
                                          </a>
                                       </li>
                                       @endcan
                                       {{-- Settings --}}
                                       @can('view-module', 171)
                                          <li class="py-1 clickable-row-blue">
                                             <a href="{{ route('supplier-purchase-setting') }}" class="text-blue">Settings</a>
                                          </li>
                                       @endcan

                                 </ul>
                              </div>
                           </li>
                              
                           @endcan
                           @can('view-module', 96)
                              <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                 <!-- Toggle for Balance Sheet submenu -->
                                 <a class="text-decoration-none d-flex text-blue collapsed"
                                    href="#"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#saleOrderSubmenu"
                                    aria-expanded="false"
                                    aria-controls="saleOrderSubmenu"> 
                                    Sale Order
                                    <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                 </a>
                                 <!-- Submenu under Balance Sheet -->
                                 <div class="collapse ps-3" id="saleOrderSubmenu">
                                    <ul class="nav flex-column">
                                       @can('view-module', 114)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('sale-order.index') }}" class="text-blue">Manage Sale Order</a>
                                       </li>
                                       @endcan
                                       @can('view-module', 115)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('deal.index') }}" class="text-blue">Manage Deal</a>
                                       </li>
                                       @endcan
                                       @can('view-module', 116)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('sale-order.settings') }}" class="text-blue">Sales Order Settings</a>
                                       </li>
                                       @endcan
                                       @can('view-module', 172)
                                        <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('sale-order.credit-days') }}" class="text-blue">Manage Credit Days</a>
                                       </li>
                                        @endcan
                                       @can('view-module', 173)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('sale-order.credit-days.rates') }}" class="text-blue">Manage Credit Rates</a>
                                       </li>
                                        @endcan
                                        <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('vehicle.report') }}" class="text-blue">Vehicle Report</a>
                                       </li>
                                    </ul>
                                 </div>
                              </li>
                           @endcan
                            @can('view-module', 117)
                           <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                 <!-- Toggle for Balance Sheet submenu -->
                                 <a class="text-decoration-none d-flex text-blue collapsed"
                                    href="#"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#productionSubmenu"
                                    aria-expanded="false"
                                    aria-controls="productionSubmenu"> 
                                    Manage Production
                                    <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                 </a>
                                 <!-- Submenu under Balance Sheet -->
                                 <div class="collapse ps-3" id="productionSubmenu">
                                    <ul class="nav flex-column">
                                       @can('view-module', 118)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('production.set_item') }}" class="text-blue">Set Items</a>
                                       </li>
                                       @endcan
                                       @can('view-module', 119)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('deckle-process.index') }}" class="text-blue">Manage Pop Roll</a>
                                       </li>
                                       @endcan
                                       @can('view-module', 120)
                                       <li class="py-1 clickable-row-blue">
                                          <a href="{{ route('deckle-process.manage-reel') }}" class="text-blue">Manage Reel</a>
                                       </li>
                                       @endcan
                                       <!--@can('view-module', 121)-->
                                       <!--<li class="py-1 clickable-row-blue">-->
                                       <!--   <a href="{{ route('deckle-process.manage-stock') }}" class="text-blue">Manage Stock</a>-->
                                       <!--</li>-->
                                       <!--@endcan-->
                                    </ul>
                                 </div>
                              </li>
                              @endcan
                              @can('view-module', 174)
                              <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                  <!-- Toggle for Consumption submenu -->
                                  <a class="text-decoration-none d-flex text-blue collapsed"
                                     href="#"
                                     data-bs-toggle="collapse"
                                     data-bs-target="#consumptionSubmenu"
                                     aria-expanded="false"
                                     aria-controls="consumptionSubmenu">
                                     Manage Consumption
                                     <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                  </a>
    
                                  <!-- Submenu under Manage Consumption -->
                                 <div class="collapse ps-3" id="consumptionSubmenu">
                                     <ul class="nav flex-column">
                                         @can('view-module', 175)
                                        <li class="py-1 clickable-row-blue">
                                           <a href="{{ route('consumption.index') }}" class="text-blue">Raw Material Consumption</a>
                                        </li>
                                        @endcan
                                        <li class="py-1 clickable-row-blue">
                                           <a href="{{ route('part-life.entries') }}" class="text-blue">Spare Part Consumption</a>
                                        </li>
                                       
                                     </ul>
                                  </div>
                               </li>
                               @endcan
                                @can('view-module', 177)
                               <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                  <!-- Toggle for Consumption Stock -->
                                  <a class="text-decoration-none d-flex text-blue collapsed"
                                     href="#"
                                     data-bs-toggle="collapse"
                                     data-bs-target="#ManageStock"
                                     aria-expanded="false"
                                     aria-controls="ManageStockSubmenu">
                                     Manage Stock
                                     <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                                  </a>
    
                                  <!-- Submenu under Manage Stock -->
                                  <div class="collapse ps-3" id="ManageStock">
                                     <ul class="nav flex-column">
                                          @can('view-module', 178)
                                        <li class="py-1 clickable-row-blue">
                                           <a href="{{ route('ManageStock.filter') }}" class="text-blue">Reel Closing Stock </a>
                                        </li>
                                        @endcan
                                         @can('view-module', 179)
                                        <li class="py-1 clickable-row-blue">
                                           <a href="{{ route('openingstock.filter') }}" class="text-blue">Reel Ledger </a>
                                        </li>
                                        @endcan
                                     </ul>
                                  </div>
                               </li>
                               @endcan
                               
                               <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                          <a href="{{ route('machine.time.loss') }}" class="text-blue">Machine Time Loss</a>
                                       </li>
                                <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                    <a href="{{ route('dara.report') }}" class="text-blue">Dara Report</a>
                                </li>
                        </ul>
                     </div>
                  </div>
               @endcan
            @endcan
            @can('view-module', 242)
            <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                     <div class="card-header py-12 px-2 border-0 rounded-0 " id="transactionsHeading">
                        <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#TaskCollapse" aria-expanded="true" aria-controls="TaskCollapse">
                           <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                                Task Manager
                        </a>
                     </div>                     
                     <div id="TaskCollapse" class="collapse" aria-labelledby="displayHeading" data-bs-parent="#accordion">
                        <ul class="nav flex-column">
                           @can('view-module', 243)
                              <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                 <!-- Toggle for Balance Sheet submenu -->
                                 <a class="text-decoration-none d-flex text-blue "
                                    href="{{ route('task.index') }}">
                                  
                                    Assign Task by you
                                 </a>
                                 <!-- Submenu under Balance Sheet -->
                                
                              </li>
                           @endcan
                           @can('view-module', 244)
                              <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                 <!-- Toggle for Balance Sheet submenu -->
                                 <a class="text-decoration-none d-flex text-blue "
                                    href="{{ route('task.myTasks') }}"> 
                                    Tasks Assigned to you
                                    
                                 </a>
                                 <!-- Submenu under Balance Sheet -->
                                 
                              </li>
                           @endcan
                           @can('view-module', 245)
                           <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                                 <!-- Toggle for Balance Sheet submenu -->
                                 <a class="text-decoration-none d-flex text-blue "
                                    href="{{ route('task.monthly.index') }}"> 
                                    Monthly Task
                                    
                                 </a>
                                 <!-- Submenu under Balance Sheet -->
                                 
                              </li>
                              @endcan
                        </ul>
                     </div>
                  </div>
                  @endcan
            @can('view-module', 257)
                <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                    <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="jobWorkHeading">
                        <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#jobWorkCollapse" aria-expanded="true" aria-controls="jobWorkCollapse"><img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Job Work</a>
                    </div>
                    <div id="jobWorkCollapse" class="collapse" aria-labelledby="jobWorkHeading" data-bs-parent="#accordion">
                        <ul class="nav flex-column">
                            @can('view-module', 258)
                                <a href="{{ route('jobwork.out.raw') }}">
                                   <li class="font-14  fw-500 m-0 py-12 px-2 text-white  border-radius-4 clickable-row">Job Work Out (RAW)</li>
                                </a>
                            @endcan
                            @can('view-module', 259)
                                <a href="{{ route('jobwork.out.finished') }}">
                                   <li class="font-14  fw-500 m-0 py-12 px-2 text-white  border-radius-4 clickable-row">Job Work Out (FINISHED)</li>
                                </a>
                            @endcan
                            @can('view-module', 260)
                                <a href="{{ route('jobworkin.raw') }}">
                                   <li class="font-14 fw-500 m-0 py-12 px-2 text-white border-radius-4 clickable-row">
                                      Job Work In (RAW)
                                   </li>
                                </a>
                            @endcan
                            @can('view-module', 261)
                                <a href="{{ route('jobworkin.finished') }}">
                                   <li class="font-14 fw-500 m-0 py-12 px-2 text-white border-radius-4 clickable-row">
                                      Job Work In (FINISHED)
                                   </li>
                                </a>
                            @endcan
                            @can('view-module', 262)
                                <a href="{{ route('jobwork.stockjournal.index') }}">
                                   <li class="font-14  fw-500 m-0 py-12 px-2 text-white  border-radius-4 clickable-row">Job Work Stock Journal</li>
                                </a>
                            @endcan
                            @can('view-module', 263)
                                <a href="{{ route('job_work_ledger.index') }}">
                                   <li class="font-14  fw-500 m-0 py-12 px-2 text-white  border-radius-4 clickable-row">Job Work Ledger</li>
                                </a>
                            @endcan
                        </ul>
                    </div>
                </div>
            @endcan
            <?php 
         } ?>
         
         
          <?php
                     if (Session::get('user_id') == '1' || Session::get('user_id') == '3') { ?>
                                        <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                          <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="RetailManagementHeading">
                            
                            <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500 p-0 collapsed"
                               href="#"
                               data-bs-toggle="collapse"
                               data-bs-target="#RetailManagementCollapse"
                               aria-expanded="true"
                               aria-controls="RetailManagementCollapse">
                               
                               <img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">
                               Retail Business Management
                            </a>
                            
                          </div>
                        
                          <div id="RetailManagementCollapse"
                               class="collapse"
                               aria-labelledby="RetailManagementHeading"
                               data-bs-parent="#accordion">
                               
                            <ul class="nav flex-column">
                              <a href="{{ route('retail-item-rate.index') }}">
                                <li class="font-14 fw-500 m-0 py-12 px-2 text-white border-radius-4 clickable-row">
                                  Manage Rate
                                </li>
                              </a>
                            </ul>
                        
                          </div>
                        </div>
               <?php } ?>
            
      </div>
   </div>
</aside>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const collapses = document.querySelectorAll('[data-bs-toggle="collapse"]');

    collapses.forEach(toggle => {
        const icon = toggle.querySelector('.arrow-icon');
        const targetId = toggle.getAttribute('data-bs-target');
        const collapseEl = document.querySelector(targetId);

        collapseEl.addEventListener('show.bs.collapse', () => {
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        });

        collapseEl.addEventListener('hide.bs.collapse', () => {
            if (icon) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});

</script>


