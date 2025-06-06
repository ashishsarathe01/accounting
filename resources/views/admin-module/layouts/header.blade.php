<header class="header-section px-4 py-3 d-flex justify-content-between align-items-center">
    <div class="meri-accounting-logo">
        <a href="{{ route('dashboard') }}">
            <img src="{{ URL::asset('public/assets/imgs/logo.svg') }}" alt="Dashboard">
        </a>
    </div>
    <div class="d-flex align-items-center">
        
        <div class="dropdown  d-flex ">
            <button class="font-14 bg-white border-0  d-flex align-items-center p-1" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                <img height="40" width="60" src="{{ URL::asset('public/assets/imgs/profile-default.svg')}}" class="ps-3">
                <div class="ps-2 d-lg-block d-none">
                    <span class="font-14  text-start text-nowrap">
                        {{Session::get('user_name');}}
                    </span>
                    <span class="d-block font-12 text-start">Admin</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="ms-2 d-lg-block d-none" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M5.5575 6.43555L9 9.87805L12.4425 6.43555L13.5 7.50055L9 12.0005L4.5 7.50055L5.5575 6.43555Z" fill="#474448" />
                </svg>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
               
                <li><a class="dropdown-item" href="{{ route('admin.logout') }}">log Out</a></li>
            </ul>
        </div>
    </div>
    <!--<div class="d-lg-flex d-none align-items-center">
            <div class="dropdown mb-3 mb-md-0">
                <button class="p-3 font-14 bg-white border-divider border-radius-8" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Kraft paperz India PVT. LTD...
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path
                            d="M5.5575 6.43555L9 9.87805L12.4425 6.43555L13.5 7.50055L9 12.0005L4.5 7.50055L5.5575 6.43555Z"
                            fill="#474448" />
                    </svg>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="#">option 1</a></li>
                    <li><a class="dropdown-item" href="#">option 2</a></li>
                </ul>
            </div>
            <img src="{{ URL::asset('public/assets/imgs/profile-img.svg')}}" class="ps-3">
            <div class="dropdown  d-flex">
                <button class="font-14 bg-white border-0 d-flex align-items-center p-1" type="button"
                    id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="ps-2">
                        <span class="font-14  text-start text-nowrap">
                            John Bentley
                        </span>
                        <span class="d-block font-12 text-start">Admin</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="ms-2" width="18" height="18" viewBox="0 0 18 18"
                        fill="none">
                        <path
                            d="M5.5575 6.43555L9 9.87805L12.4425 6.43555L13.5 7.50055L9 12.0005L4.5 7.50055L5.5575 6.43555Z"
                            fill="#474448" />
                    </svg>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                    <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                    
                </ul>
            </div>
        </div>-->
    <div class=" d-block d-lg-none">
        <span class="material-symbols-outlined ">
            menu
        </span>
    </div>
</header>