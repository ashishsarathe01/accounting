<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Meri Accounting') }}</title>

    <!-- Fonts -->
    <!--<link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">-->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <link rel="stylesheet" href="{{ URL::asset('public/assets/css/vendors/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('public/assets/css/vendors/dataTables.bootstrap5.min.css')}}">
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/style.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/vendors/select2.min.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/vendors/select2-bootstrap.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('public/assets/css/vendors/screen.css')}}" />
        

        @yield('styles')
    <!-- Scripts -->
     <style>
        #cover-spin {
            position:fixed;
            width:100%;
            left:0;right:0;top:0;bottom:0;
            background-color: rgba(255,255,255,0.7);
            z-index:9999;
            display:none;
         }
         @-webkit-keyframes spin {
           from {-webkit-transform:rotate(0deg);}
           to {-webkit-transform:rotate(360deg);}
         }
         @keyframes spin {
            from {transform:rotate(0deg);}
            to {transform:rotate(360deg);}
         }
         #cover-spin::after {
            content:'';
            display:block;
            position:absolute;
            left:48%;top:40%;
            width:40px;height:40px;
            border-style:solid;
            border-color:black;
            border-top-color:transparent;
            border-width: 4px;
            border-radius:50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
         }
     </style>
    
</head>
<body>

    <div id="app">
    <div id="cover-spin"></div>
        <!--<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    
                    <ul class="navbar-nav me-auto">

                    </ul>

                   
                    <ul class="navbar-nav ms-auto">
                        
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

       <main class="py-4">
            
        </main>-->
        @yield('content')
    </div>
</body>
<script src="{{ URL::asset('public/assets/js/vendors/bootstrap.bundle.min.js')}}"></script>
</html>
