<!-- accordion -->
<aside class="col-lg-2 d-none d-lg-block bg-blue sidebar p-0">
   <div class="sidebar-sticky ">
      <div id="accordion">
         <div class="card rounded-0 bg-blue py-20 px-2 border-bottom-divider">
            <div class="card-header p-0 border-0 rounded-0 d-flex p-0 border-0" id="dashboardHeading">
               <img src="{{ URL::asset('public/assets/imgs/dashboard.svg')}}" alt="">
               <a class="nav-link text-white fw-500 font-14 ms-2 p-0" href="{{ route('dashboard') }}">Dashboard</a>
            </div>
         </div>
         <div class="card bg-blue py-20 px-2 rounded-0 border-bottom-divider">
            <div class="card-header p-0 border-0 rounded-0 d-flex" id="companyHeading">
               <img src="{{ URL::asset('public/assets/imgs/company.svg')}}" alt="">
               <a class="nav-link text-white font-14 fw-500 ms-2 p-0" href="#" data-bs-toggle="collapse" data-bs-target="#companyCollapse" aria-expanded="true" aria-controls="companyCollapse">Company</a>
               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="ms-auto img-fluid " viewBox="0 0 18 18" fill="none">
                  <path d="M12.4425 12L9 8.5575L5.5575 12L4.5 10.935L9 6.435L13.5 10.935L12.4425 12Z" fill="#E4E4E4" />
               </svg>
            </div>
            <div id="companyCollapse" class="collapse" aria-labelledby="companyHeading" data-bs-parent="#accordion">
               <ul class="nav flex-column">
                  <a href="{{ route('add-company') }}">
                     <li class="font-14 text-blue fw-500 m-0 py-12 px-2 text-blue bg-white border-radius-4">Add Company</li>
                  </a>
                  <?php
                  if (Session::get('user_company_id') != '') { ?>
                     <a href="{{ route('view-company') }}">
                        <li class="font-14 text-white fw-500 m-0 py-12 px-2 "> View Company</li>
                     </a>
                     <a href="{{ route('manage-financial-year') }}">
                        <li class="font-14 text-white fw-500 m-0 py-12 px-2 "> Manage Financial Year</li>
                     </a>
                     <a href="{{ route('manage-merchant-employee.index') }}">
                        <li class="font-14 text-white fw-500 m-0 py-12 px-2 "> Manage User</li>
                     </a>
                     <?php 
                  } ?>
               </ul>
            </div>
         </div>
         <?php
         if(Session::get('user_company_id') != ''){?>
            <!-- Administrator ------------>
            <div class="card  bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
               <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="administratorHeading">
                  <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#adminCollapse" aria-expanded="true" aria-controls="adminCollapse">
                  <img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Master</a>
               </div>
               <div id="adminCollapse" class="collapse" aria-labelledby="administratorHeading" data-bs-parent="#accordion">
                  <ul class="nav flex-column">
                     <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4 bg-white">
                        <a class=" text-decoration-none  d-flex  text-blue " href="{{ route('account-heading.index') }}">
                                    Account Heading
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-group.index') }}">
                                    Account Group
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account.index') }}">
                           Account
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex  text-white  " href="{{ route('account-unit.index') }}">
                                    Manage Unit
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-item-group.index') }}">
                                    Item Group
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('account-manage-item.index') }}">
                                    Manage Item
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex    text-white" href="{{ route('account-bill-sundry.index') }}">
                                    Bill Sundry
                        </a>
                     </li>
                     <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                        <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="importMasterDataHeading">
                           <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#importMasterDataCollapse" aria-expanded="true" aria-controls="importMasterDataCollapse">
                            <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">Import Master Data
                           </a>
                        </div>
                        <div id="importMasterDataCollapse" class="collapse" aria-labelledby="importMasterDataHeading" data-bs-parent="#importMasterDataHeading">
                           <ul class="nav flex-column">
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('account-group-import-view') }}">Import Account Group</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-account-view') }}">Import Account</a>
                              </li>
                              <!-- <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('import-account-view') }}">Import Item Group</a>
                              </li> -->
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('item-import-view') }}">Import Item</a>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </ul>
               </div>
            </div>
            <!-- Transactions -->
            <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
               <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="transactionsHeading">
                  <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#transactonCollapse" aria-expanded="true" aria-controls="transactonCollapse">
                     <img src="{{ URL::asset('public/assets/imgs/transactions.svg')}}" class="me-2" alt="">
                            Transactions
                  </a>
               </div>
               <div id="transactonCollapse" class="collapse" aria-labelledby="transactionsHeading" data-bs-parent="#accordion">
                  <ul class="nav flex-column">
                     <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                        <a class=" text-decoration-none  d-flex  text-blue " href="{{ route('sale.index') }}">
                                    Sales
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('purchase.index') }}">
                                    Purchase
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('sale-return.index') }}">
                                    Credit Note
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('purchase-return.index') }}">
                                    Debit Note
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('payment.index') }}">
                                    Payment
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('receipt.index') }}">
                                    Receipt
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('journal.index') }}">
                                    Journal
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('contra.index') }}">
                                    Contra
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('stock-journal') }}">
                                    Stock Journal
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2  ">
                        <a class=" text-decoration-none  d-flex   text-white" href="{{ route('stock-transfer.index') }}">
                           Stock Transfer
                        </a>
                     </li>
                     <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
                        <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="importDataHeading">
                           <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#importDataCollapse" aria-expanded="true" aria-controls="importDataCollapse">
                            <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">Import Data
                           </a>
                        </div>
                        <div id="importDataCollapse" class="collapse" aria-labelledby="importDataHeading" data-bs-parent="#importDataHeading">
                           <ul class="nav flex-column">
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('sale-import-view') }}">Import Sale</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('purchase-import-view') }}">Import Purchase</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('payment-import-view') }}">Import Payment</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('receipt-import-view') }}">Import Receipt</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('journal-import-view') }}">Import Journal</a>
                              </li>
                              <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                                 <a class=" text-decoration-none d-flex text-blue" href="{{ route('contra-import-view') }}">Import Contra</a>
                              </li>
                           </ul>
                        </div>
                     </div>
                  </ul>
               </div>
            </div>
            <!-- Display -->
            <div class="card bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
               <div class="card-header py-12 px-2 border-0 rounded-0 d-flex" id="displayHeading">
                  <a class="nav-link text-white font-14 fw-500 dropdown-icon-img p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#displayCollapse" aria-expanded="true" aria-controls="displayCollapse">
                     <img src="{{ URL::asset('public/assets/imgs/display.svg')}}" class="me-2" alt="">
                            Display
                  </a>
               </div>
               <div id="displayCollapse" class="collapse" aria-labelledby="displayHeading" data-bs-parent="#accordion">
                  <ul class="nav flex-column">
                     <li class="font-14  fw-500 m-0 py-12 px-2  bg-white border-radius-4">
                        <a class=" text-decoration-none d-flex text-blue" href="{{ route('balancesheet.index') }}">
                                    Balance Sheet
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2 ">
                        <a class=" text-decoration-none d-flex text-white" href="{{ route('profitloss.index') }}">
                                    Profit & Loss
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2">
                        <a class=" text-decoration-none d-flex text-white" href="{{ route('trialbalance.index') }}">
                                    Trial Balance
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2">
                        <a class=" text-decoration-none d-flex text-white" href="{{ route('accountledger.index') }}">
                                    Account Ledger
                        </a>
                     </li>
                     <li class="font-14  fw-500 m-0 py-12 px-2">
                        <a class=" text-decoration-none d-flex text-white" href="{{ route('itemledger.index') }}">
                                    Items Ledger
                        </a>
                     </li>
                      <li class="font-14  fw-500 m-0 py-12 px-2">
                        <a class=" text-decoration-none d-flex text-white" href="{{ route('report.filter.data') }}">
                                    GSTR-1
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
            <?php 
         } ?>
      </div>
   </div>
</aside>