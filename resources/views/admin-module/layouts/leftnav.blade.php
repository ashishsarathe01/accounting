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
         <div class="card bg-blue py-20 px-2 rounded-0 border-bottom-divider">
            <div class="card-header p-0 border-0 rounded-0 d-flex" id="companyHeading">
               <img src="{{ URL::asset('public/assets/imgs/company.svg')}}" alt="">
               <a class="nav-link text-white font-14 fw-500 ms-2 p-0" href="#" data-bs-toggle="collapse" data-bs-target="#companyCollapse" aria-expanded="true" aria-controls="companyCollapse">Manage Merchant</a>
               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" class="ms-auto img-fluid " viewBox="0 0 18 18" fill="none">
                  <path d="M12.4425 12L9 8.5575L5.5575 12L4.5 10.935L9 6.435L13.5 10.935L12.4425 12Z" fill="#E4E4E4" />
               </svg>
            </div>
            <div id="companyCollapse" class="collapse" aria-labelledby="companyHeading" data-bs-parent="#accordion">
               <ul class="nav flex-column">
                  <a href="{{ route('admin.merchant.index') }}">
                     <li class="font-14 text-blue fw-500 m-0 py-12 px-2 text-blue bg-white border-radius-4">View Merchant</li>
                  </a>
               </ul>
            </div>
         </div>
      </div>
   </div>
</aside>