<style>
    .dropdown-submenu {
    /* position: relative; */
}

.dropdown-submenu > .dropdown-menu {
    /* display: none; */
    /* position: absolute; */
    top:44px;
    left: -55%;
    margin-top: 0;
}
</style>
<header class="header-section px-4 py-3  justify-content-between align-items-center" style="display:flex">
    <div class="meri-accounting-logo">
        <a href="{{ route('dashboard') }}">
            <img src="{{ URL::asset('public/assets/imgs/logo.svg') }}" alt="Dashboard">
        </a>
    </div>
    <div class="d-flex align-items-center">
        <div class="dropdown header-dropdown">
            <form id="change_company_frm" method="POST" action="{{ route('company.change') }}">
                @csrf
                <select name="change_company" id="change_company" class="form-select form-select-lg py-12 px-3 me-sm-3 font-14 bg-white border-divider border-radius-8 hedaer-dropdown-option" aria-label="form-select-lg example" required style="height: 48px;">
                    <option value="" disabled>Select Company</option>
                    <?php
                    foreach ($company_list as $val) {
                        $sel = '';
                        if (Session::get('user_company_id') == $val->id)
                            $sel = 'selected'; ?>
                        <option  <?php echo $sel; ?> value="<?= $val->id; ?>"><?= $val->company_name; ?></option>
                    <?php } ?>
                </select>
            </form>
        </div>
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
            <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#" id="tdsConfigMenu">Configuration</a>
                    <ul class="dropdown-menu" aria-labelledby="tdsConfigMenu">
                        <li><a class="dropdown-item" href="{{ route('parameterized-configuration') }}">Parameterized Configuration</a></li>
                        <li><a class="dropdown-item" href="{{ route('gst-setting.index') }}">GST Configuration</a></li>
                        <li><a class="dropdown-item" href="{{ route('voucher-series-configuration') }}">Voucher Series Configuration</a></li>
                        <li><a class="dropdown-item" href="{{ route('sale-invoice-configuration') }}">Sale Invoice Configuration</a></li>
                        <li><a class="dropdown-item" href="#">TDS Configuration</a></li>
                        <li><a class="dropdown-item" href="#">ESI</a></li>
                        <li><a class="dropdown-item" href="#">PF</a></li>
                    </ul>
                </li>
               
              
               <li><a class="dropdown-item" href="{{ route('change-password-view') }}">Change Password</a></li>
               <li><a class="dropdown-item" href="{{ route('logout') }}">Log Out</a></li>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tdsMenu = document.getElementById('tdsConfigMenu');
        const submenu = tdsMenu.nextElementSibling;

        tdsMenu.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent Bootstrap from closing the dropdown
            submenu.classList.toggle('show');
        });
    });
</script>