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
    cursor: pointer;
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
<aside class="col-lg-2 d-none d-lg-block bg-blue sidebar p-0">
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
                        @can('view-module', 19)
                           <a href="{{ route('manage-financial-year') }}">
                              <li class="font-14 text-white fw-500 m-0 py-12 px-2 clickable-row"> Manage Financial Year</li>
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
                        @can('view-module', 2)
                           <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4  clickable-row">
                              <a class=" text-decoration-none  d-flex  text-white " href="{{ route('account-heading.index') }}">
                                          Account Heading
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
                        @can('view-module', 5)
                           <li class="font-14  fw-500 m-0 py-12 px-2  clickable-row">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account.index') }}">
                                 Account
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
                        @can('view-module', 7)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-item-group.index') }}">
                                          Item Group
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
                        @can('view-module', 9)
                           <li class="font-14  fw-500 m-0 py-12 px-2 clickable-row ">
                              <a class=" text-decoration-none  d-flex    text-white" href="{{ route('account-bill-sundry.index') }}">
                                          Bill Sundry
                              </a>
                           </li>
                        @endcan
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
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('gst2a') }}" class="text-blue">GSTR-2A</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('gst2b') }}" class="text-blue">GSTR-2B</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="{{ route('gstr3B.view') }}" class="text-blue">GSTR-3B</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-9</a>
                                                   </li>
                                                   <li class="py-1" style="">
                                                      <a href="javascript:void(0)" class="text-blue">GSTR-9C</a>
                                                   </li>
                                                </ul>
                                             </div>
                                          </li>
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
                                          <li class="report-menu-item">
                                             <a href="javascript:void(0)" class="text-blue">Credit Ledger Balance</a>
                                          </li>
                                          <li class="report-menu-item">
                                             <a href="javascript:void(0)" class="text-blue">Challan</a>
                                          </li>
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
                                       </ul>
                                    </div>
                                 </li>
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
                                 
                              </ul>
                           </div>
                        </li>
                     
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
                                 
                              </ul>
                           </div>
                        </li>
                        
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
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('parameterized-stock') }}" class="text-blue">Parameterized Stock</a>
                                    </li>
                              </ul>
                           </div>
                        </li>
                   
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
                                 
                              </ul>
                           </div>
                        </li>
                  
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
                                 
                              </ul>
                           </div>
                        </li>
                       
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
                                 
                              </ul>
                           </div>
                        </li>
                     </ul>
                  </div>
               </div>               
            @endcan
            @can('module-permission',1)
            <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                  <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="displayHeading">
                     <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#businessCollapse" aria-expanded="true" aria-controls="businessCollapse">
                        <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                              Business Management
                     </a>
                  </div>
                  
                  <div id="businessCollapse" class="collapse" aria-labelledby="displayHeading" data-bs-parent="#accordion">
                     <ul class="nav flex-column">
                        <li class="font-14 fw-500 m-0 py-12 px-2 bg-white border-radius-4 clickable-row-blue" style="border: 1px solid #007bff;">
                           <!-- Toggle for Balance Sheet submenu -->
                           <a class="text-decoration-none d-flex text-blue collapsed"
                              href="#"
                              data-bs-toggle="collapse"
                              data-bs-target="#financialSubmenu"
                              aria-expanded="false"
                              aria-controls="financialSubmenu"> 
                              Purchase Management
                              <i class="arrow-icon fa fa-chevron-down ms-auto"></i>
                           </a>
                           <!-- Submenu under Balance Sheet -->
                           <div class="collapse ps-3" id="financialSubmenu">
                              <ul class="nav flex-column">
                                   
                                   <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('supplier-sub-head.index') }}" class="text-blue">Manage Sub Head</a>
                                    </li>
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('supplier.index') }}" class="text-blue">Manage Supplier</a>
                                    </li>
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('manage-supplier-rate') }}" class="text-blue">Manage Rate</a>
                                    </li>
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('manage-supplier-purchase') }}" class="text-blue"> Manage Purchase </a>
                                    </li>
                                    <li class="py-1 clickable-row-blue">
                                       <a href="{{ route('manage-supplier-purchase-report') }}" class="text-blue">Report </a>
                                    </li>
                              </ul>
                           </div>
                        </li>                        
                     </ul>
                  </div>
                  
               </div>
               @endcan
            <?php 
         } ?>
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

