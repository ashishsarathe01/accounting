<!-- accordion -->
<aside class="col-lg-2 d-none d-lg-block bg-blue sidebar p-0">
   <div class="sidebar-sticky ">
      <div id="accordion">
         <div class="card rounded-0 bg-blue py-20 px-2 border-bottom-divider">
            <div class="card-header p-0 border-0 rounded-0 d-flex p-0 border-0" id="dashboardHeading">
               <img src="{{ URL::asset('public/assets/imgs/dashboard.svg')}}" alt="">
               <a class="nav-link text-white fw-500 font-14 ms-2 p-0" href="{{ route('admin.dashboard') }}">Dashboard</a>
            </div>
         </div>
         <div class="card  bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
            <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="administratorHeading">
               <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#merchantCollapse" aria-expanded="true" aria-controls="merchantCollapse">
               <img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Manage Merchant</a>
            </div>
            <div id="merchantCollapse" class="collapse" aria-labelledby="administratorHeading" data-bs-parent="#accordion">
               <ul class="nav flex-column">
                  <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4 bg-white">
                     <a class=" text-decoration-none  d-flex  text-blue " href="{{ route('admin.merchant.index') }}">
                        View Merchant
                     </a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="card  bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
            <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="administratorHeading">
               <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#adminCollapse" aria-expanded="true" aria-controls="adminCollapse">
               <img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Master</a>
            </div>
            <div id="adminCollapse" class="collapse" aria-labelledby="administratorHeading" data-bs-parent="#accordion">
               <ul class="nav flex-column">
                  <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4 bg-white">
                     <a class=" text-decoration-none  d-flex  text-blue" href="{{ route('admin.account-head.index') }}">Account Heading</a>
                  </li>
                  <li class="font-14  fw-500 m-0 py-12 px-2  ">
                     <a class=" text-decoration-none  d-flex   text-white" href="{{ route('admin.account-group.index') }}">Account Group</a>
                  </li>
                  <li class="font-14  fw-500 m-0 py-12 px-2  ">
                     <a class=" text-decoration-none  d-flex   text-white" href="{{ route('admin.account.index') }}">Account
                     </a>
                  </li>
                  <li class="font-14  fw-500 m-0 py-12 px-2  ">
                     <a class=" text-decoration-none  d-flex   text-white" href="{{ route('admin.merchant-privilege.index') }}">Merchant Panel Privileges
                     </a>
                  </li>
                  <li class="font-14  fw-500 m-0 py-12 px-2  ">
                     <a class=" text-decoration-none  d-flex   text-white" href="{{ route('admin.merchant-module-permission') }}">Merchant Modules Permission
                     </a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="card  bg-blue pt-2 px-2 rounded-0 aside-bottom-divider">
            <div class="card-header py-12 px-2 border-0 d-flex rounded-0" id="administratorHeading">
               <a class="nav-link text-white font-14 dropdown-icon-img d-flex fw-500  p-0 collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#adminCollapse1" aria-expanded="true" aria-controls="adminCollapse">
               <img src="{{ URL::asset('public/assets/imgs/administrator.svg')}}" class="me-2" alt="">Manage User</a>
            </div>
            <div id="adminCollapse1" class="collapse" aria-labelledby="administratorHeading" data-bs-parent="#accordion">
               <ul class="nav flex-column">
                  <li class="font-14  fw-500 m-0 py-12 px-2  border-radius-4 bg-white">
                     <a class=" text-decoration-none  d-flex  text-blue" href="{{ route('admin.manageUser.index') }}">User Master</a>
                  </li>
               </ul>
            </div>
         </div>
      </div>
   </div>
</aside>