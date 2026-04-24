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
@if(session('company_mismatch'))
    <div class="alert alert-danger alert-dismissible fade show m-3">
        {{ session('company_mismatch') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
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
            <form id="change_fy_form"  method="POST" action="{{ route('change-financial-year') }}" 
      class="d-flex align-items-center bg-white border-divider border-radius-8 px-3 me-3" style="height:48px; margin-left:20px;">
    @csrf
    <span class="font-12 text-muted me-1">FY:</span>
    <!--<select name="current_finacial_year_header" id="current_finacial_year_header" class="form-select form-select-sm border-0 p-0 font-14" style="width:auto; background:none;">-->
        <select name="current_finacial_year_header" 
        id="current_finacial_year_header" 
        class="form-select form-select-sm border-0 font-14"
        style="width:90px; background-color:transparent; cursor:pointer;">
        <!-- OPTIONS WILL BE ADDED BY AJAX -->
    </select>
</form>
@php
    use Illuminate\Support\Str;

    $user_id = Session::get('user_id');

    /*
    |--------------------------------------------------------------------------
    | 1️⃣ Unread Tasks
    |--------------------------------------------------------------------------
    */
    $unreadTasks = DB::table('tasks as t')
    ->join('companies as c','t.company_id','=','c.id')
    ->where('t.assigned_to', $user_id)
    ->where('t.is_notification_read', 0)
    ->whereNull('t.deleted_at')
    ->select(
        't.id',
        't.title',
        't.company_id',
        'c.company_name'
    )
    ->orderBy('t.created_at','DESC')
    ->get();
    /*
    |--------------------------------------------------------------------------
    | 2️⃣ Unread Messages
    |--------------------------------------------------------------------------
    */
    $unreadMessages = DB::table('task_responses as r')
    ->join('tasks as t','r.task_id','=','t.id')
    ->whereNull('t.deleted_at')
    ->join('companies as c','t.company_id','=','c.id')
    ->join('users as u','r.user_id','=','u.id')
        ->where(function($q) use ($user_id){
            $q->where('t.created_by',$user_id)
              ->orWhere('t.assigned_to',$user_id);
        })
        ->where('r.user_id','!=',$user_id)
        ->where('r.is_read',0)
        ->select(
    'r.task_id',
    'r.message',
    't.title as task_title',
    'u.name as sender_name',
    't.company_id',
    'c.company_name'
)
        ->orderBy('r.created_at','DESC')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | 3️⃣ Unread Activity Logs (Status / Delegate / Approve)
    |--------------------------------------------------------------------------
    */
    $unreadLogs = DB::table('task_logs as l')
    ->join('tasks as t','l.task_id','=','t.id')
    ->whereNull('t.deleted_at')
    ->join('companies as c','t.company_id','=','c.id')
    ->join('users as u','l.user_id','=','u.id')
        ->where(function($q) use ($user_id){
            $q->where('t.created_by',$user_id)
              ->orWhere('t.assigned_to',$user_id);
        })
        ->where('l.user_id','!=',$user_id)
        ->where('l.is_read',0)
        ->select(
    'l.task_id',
    'l.description',
    't.title as task_title',
    'u.name as actor_name',
    't.company_id',
    'c.company_name'
)
        ->orderBy('l.created_at','DESC')
        ->get();


    /*
    |--------------------------------------------------------------------------
    | 🔔 Total Notification Count
    |--------------------------------------------------------------------------
    */
    $notificationCount =
          $unreadTasks->count()
        + $unreadMessages->count()
        + $unreadLogs->count();
@endphp


<div class="dropdown me-3">
    <button class="position-relative d-flex align-items-center justify-content-center"
            style="width:48px;height:48px;background:white;border:1px solid #e0e0e0;border-radius:8px;"
            type="button"
            data-bs-toggle="dropdown">

        <!-- Bell Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#333" viewBox="0 0 24 24">
            <path d="M12 2C10.343 2 9 3.343 9 5V6.09C6.165 6.561 4 9.031 4 12V17L2 19V20H22V19L20 17V12C20 9.031 17.835 6.561 15 6.09V5C15 3.343 13.657 2 12 2ZM12 22C13.657 22 15 20.657 15 19H9C9 20.657 10.343 22 12 22Z"/>
        </svg>

        @if($notificationCount > 0)
            <span style="
                position:absolute;
                top:-6px;
                right:-6px;
                background:#dc3545;
                color:white;
                font-size:11px;
                padding:2px 6px;
                border-radius:50%;
                min-width:18px;
                text-align:center;
            ">
                {{ $notificationCount }}
            </span>
        @endif
    </button>

    <ul class="dropdown-menu dropdown-menu-end p-2"
    style="width:320px; max-height:400px; overflow-y:auto; overflow-x:hidden;">

        @if($notificationCount == 0)
            <li class="text-center text-muted py-2">
                No new notifications
            </li>
        @endif


        {{-- 📩 UNREAD MESSAGES --}}
        @foreach($unreadMessages as $msg)
            <li>
                <a class="dropdown-item small text-wrap"
   style="white-space: normal; word-break: break-word;"
                   href="{{ route('task.detail', $msg->task_id) }}">
                    📩 <strong>{{ $msg->sender_name }}</strong>
({{ $msg->company_name }})<br>
                    {{ Str::limit($msg->message, 40) }}<br>
                    <small class="text-muted">
                        Task: {{ $msg->task_title }}
                    </small>
                </a>
            </li>
        @endforeach


        {{-- 🔔 UNREAD LOGS --}}
        @foreach($unreadLogs as $log)
            <li>
                <a class="dropdown-item small text-wrap"
   style="white-space: normal; word-break: break-word;"
                   href="{{ route('task.detail', $log->task_id) }}">
                    🔔 <strong>{{ $log->actor_name }}</strong>
({{ $log->company_name }})<br>
                    {{ $log->description }}<br>
                    <small class="text-muted">
                        Task: {{ $log->task_title }}
                    </small>
                </a>
            </li>
        @endforeach


        {{-- 📌 UNREAD TASKS --}}
        @foreach($unreadTasks as $task)
            <li>
                <a class="dropdown-item small text-wrap"
   style="white-space: normal; word-break: break-word;"
                   href="{{ route('task.detail', $task->id) }}">
                    📌 New Task Assigned ({{ $task->company_name }}):<br>
                    <strong>{{ $task->title }}</strong>
                </a>
            </li>
        @endforeach

    </ul>
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
                        <li><a class="dropdown-item" href="{{ route('purchase.configuration.index') }}">Purchase Configuration Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('configuration.settings') }}">Dashboard Configuration Settings</a></li>
                        <li><a class="dropdown-item" href="{{ route('payroll.configuration.index') }}">Payroll Configuration Settings</a></li>
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
    document.addEventListener('DOMContentLoaded', function () {

    fetch('{{ route("ajax.manage-financial-year") }}')
        .then(res => res.json())
        .then(data => {

            let defaultFy = data.default_fy;  
                         // ex: "23-24"
            let currentFy  = data.current_financial_year;  // ex: "22-23"
// alert(currentFy);
            // Extract start year
            let fyParts = currentFy.split('-');
            let startY = parseInt(
               fyParts[0]
            );

            // Determine current FY end as per Indian FY (April–March)
            let year  = new Date().getFullYear();
            let month = new Date().getMonth();

            // FY ending year
            let currentFYEnd = (month >= 3) ? year : year - 1;

            // convert to 2-digit
            let yy = currentFYEnd.toString().slice(-2);

            let dropdown = document.getElementById('current_finacial_year_header');
            dropdown.innerHTML = "";

            while (startY <= yy) {

                let next = startY + 1;
                let fyStr = startY + '-' + next.toString().slice(-2);

                let option = document.createElement('option');
                option.value = fyStr;
                option.text  = fyStr;

                if (defaultFy == fyStr) {
                    option.selected = true;
                }

                dropdown.append(option);
                startY++;
            }
        });
});

// auto submit FY
document.addEventListener('change', function (e) {
    if (e.target.id === 'current_finacial_year_header') {
        document.getElementById('change_fy_form').submit();

        setTimeout(() => {
            e.target.disabled = true;
        }, 10);
    }
});


</script>