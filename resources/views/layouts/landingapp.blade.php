<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TechBridge Software | ERP & Business Automation')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --tb-primary: #0b5ed7; --tb-dark: #0f172a; --tb-soft: #eaf2ff; }
	        body { font-family: 'Inter', sans-serif; color: #1e293b; background: #f8fafc; }
	        .section-padding { padding: 88px 0; }
        .top-strip {
            background:
                linear-gradient(90deg, rgba(8, 17, 31, 0.98), rgba(15, 39, 64, 0.94)),
                #0f172a;
            color: #d9e7f7;
            font-size: 13px;
        }
        .top-strip .header-shell {
            min-height: 42px;
            gap: 14px;
        }
        .header-shell { width: min(100% - 24px, 1440px); margin: 0 auto; }
        .main-navbar {
            background: rgba(248, 250, 252, 0.82);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(18px);
        }
	        .main-navbar .navbar-inner {
	            display: flex;
	            align-items: center;
	            padding: 18px 0;
	            gap: 28px;
	            flex-wrap: nowrap;
	        }
        .brand-mark {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #0b5ed7 0%, #3ab7bf 100%);
            color: #fff;
            box-shadow: 0 16px 28px rgba(11, 94, 215, 0.22);
            font-size: 1.15rem;
        }
        .brand-lockup { gap: 3px; }
        .brand-title { font-size: 1.2rem; font-weight: 800; color: var(--tb-dark); line-height: 1.15; }
        .brand-subtitle { font-size: .72rem; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
        .brand-note {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 2px;
            color: #64748b;
            font-size: 0.8rem;
        }
        .brand-note i { color: #0b5ed7; }
        .navbar-panel {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .navbar-menu-wrap {
            flex: 1 1 auto;
            display: flex;
            justify-content: center;
        }
        .navbar-nav {
            gap: 8px;
            align-items: center;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }
        .nav-link {
            color: #0f172a !important;
            font-weight: 600;
            padding: 10px 16px !important;
            border-radius: 999px;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .nav-link:focus,
        .nav-link:hover {
            background: var(--tb-soft);
            color: var(--tb-primary) !important;
            transform: translateY(-1px);
        }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(11, 94, 215, 0.12), rgba(58, 183, 191, 0.16));
            color: var(--tb-primary) !important;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .navbar-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            color: #64748b;
            line-height: 1.2;
        }
        .navbar-meta strong {
            color: #0f172a;
            font-size: 0.82rem;
        }
        .navbar-meta span {
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .navbar-cta {
            padding: 11px 22px;
            font-weight: 700;
            border-radius: 999px;
            box-shadow: 0 14px 24px rgba(11, 94, 215, 0.18);
        }
        .btn-primary { background: var(--tb-primary); border-color: var(--tb-primary); }
        .btn-outline-primary { border-color: var(--tb-primary); color: var(--tb-primary); }
        .hero-gradient { background: linear-gradient(140deg, #0f172a 0%, #0b5ed7 55%, #22c55e 100%); color: #fff; }
        footer { background: #0b1220; color: #cbd5e1; }
	        footer h5, footer h6 { color: #fff; }
		        @media (max-width: 991.98px) {
		            .header-shell { width: min(100% - 20px, 1440px); }
		            .top-strip .header-shell { justify-content: center !important; text-align: center; }
		            .main-navbar .navbar-inner {
		                padding: 12px 0;
		                display: grid;
		                grid-template-columns: 1fr auto;
		                grid-template-areas:
		                    "brand toggler"
		                    "collapse collapse";
		                align-items: center;
		                gap: 12px;
		            }
		            .main-navbar .navbar-brand { grid-area: brand; min-width: 0; }
		            .main-navbar .navbar-toggler {
		                grid-area: toggler;
		                justify-self: end;
		                border-radius: 14px;
		                padding: 10px 12px;
		                background: rgba(255, 255, 255, 0.94);
		                border: 1px solid rgba(226, 232, 240, 0.95);
		                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
		            }
		            .brand-mark { width: 46px; height: 46px; border-radius: 14px; }
		            .navbar-panel {
		                display: block;
		                width: 100%;
		            }
		            .main-navbar .navbar-collapse {
		                grid-area: collapse;
		                flex-basis: 100%;
		                width: 100%;
		                margin-top: 14px;
		                padding: 16px;
		                border: 1px solid #e2e8f0;
		                border-radius: 24px;
		                background: rgba(255, 255, 255, 0.92);
	                box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
	            }
	            .navbar-menu-wrap { display: block; width: 100%; }
	            .navbar-nav {
	                gap: 4px;
	                align-items: stretch;
	                width: 100%;
	                padding: 0;
	                border-radius: 0;
	                background: transparent;
	                border: 0;
	                box-shadow: none;
	            }
	            .navbar-nav .nav-item { width: 100%; }
		            .nav-link {
		                border-radius: 14px;
		                width: 100%;
		                display: flex;
		                justify-content: space-between;
		                align-items: center;
		                background: rgba(248, 250, 252, 0.92);
		                border: 1px solid rgba(226, 232, 240, 0.92);
		                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
		            }
		            .navbar-nav .nav-link.bg-transparent {
		                background: rgba(248, 250, 252, 0.92) !important;
		                border: 1px solid rgba(226, 232, 240, 0.92) !important;
		            }
		            .navbar-actions {
		                margin-top: 14px;
		                flex-direction: column;
		                align-items: stretch;
		            }
	            .navbar-meta { align-items: flex-start; }
	            .navbar-cta { margin-top: 0; display: inline-flex; justify-content: center; }
	        }
        @media (max-width: 767.98px) {
            .brand-note,
            .navbar-meta { display: none; }
        }
        
        /* ===== GLOBAL FIXES ===== */
img {
    max-width: 100%;
    height: auto;
}

/* Prevent horizontal scroll */
body {
    overflow-x: hidden;
}

/* ===== NAVBAR IMPROVEMENTS ===== */
	.navbar-brand img {
	    height: 60px;
	    width: auto;
	    max-width: 100%;
	}

@media (max-width: 991.98px) {
    .navbar-brand img {
        height: 50px;
    }
}

@media (max-width: 575.98px) {
    .navbar-brand img {
        height: 42px;
    }

    .brand-title {
        font-size: 1rem;
    }
}

/* Better spacing on tablet */
@media (max-width: 1199px) {
    .navbar-inner {
        gap: 16px;
    }

    .navbar-nav {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Fix CTA + meta alignment */
@media (max-width: 991.98px) {
    .navbar-actions {
        width: 100%;
    }

    .navbar-cta {
        width: 100%;
    }
}

/* ===== TOP STRIP FIX ===== */
@media (max-width: 576px) {
    .top-strip span {
        display: block;
        width: 100%;
        text-align: center;
    }
}

/* ===== FOOTER FIX ===== */
footer .row > div {
    text-align: left;
}

@media (max-width: 767.98px) {
    footer .row > div {
        text-align: center;
    }
}

/* ===== SECTION SPACING ===== */
@media (max-width: 768px) {
    .section-padding {
        padding: 50px 0;
    }
}

    </style>
    @stack('styles')
</head>
<body>
    <div class="top-strip py-2">
        <div class="header-shell d-flex justify-content-between align-items-center flex-wrap">
            <span><i class="bi bi-shield-check me-1"></i> Secure cloud ERP for modern businesses</span>
            <span><i class="bi bi-envelope me-1"></i> techbridgepartnersindiapvtltd@gmail.com</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg sticky-top main-navbar">
	        <div class="header-shell navbar-inner">
             <a class="navbar-brand d-flex align-items-center gap-3 me-0" href="{{ url('/') }}">
                <span >
<img src="{{ URL::asset('public/assets/imgs/malogo.png') }}" alt="Dashboard">
                </span>
                <span class="d-flex flex-column brand-lockup">
                  <span class="brand-title">
    <span style="color:#1d4ed8; font-weight:800;">MERI</span>
    <span style="color:#16a34a; font-weight:800;">ACCOUNTING</span>
    ERP Platform
</span>
                   
                    <span class="brand-note">
                        <i class="bi bi-graph-up-arrow"></i>
                        Finance, GST and operations in one system
                    </span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse navbar-panel" id="mainNav">
                <div class="navbar-menu-wrap">
	                    <ul class="navbar-nav">
	                        <li class="nav-item"><a class="nav-link {{ request()->url() === url('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a></li>
	                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Pricing</a></li>
	                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('features') ? 'active' : '' }}" href="{{ route('features') }}">Features</a></li>
	                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
	                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('ContactUs') ? 'active' : '' }}" href="{{ route('ContactUs') }}">Contact</a></li>
	                        @auth
	                            <li class="nav-item">
	                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
	                            </li>
	                            <li class="nav-item">
	                                <form method="GET" action="{{ route('logout') }}" class="m-0 p-0">
	                                    @csrf
	                                    <button type="submit" class="nav-link border-0 bg-transparent">Logout</button>
	                                </form>
	                            </li>
	                          @else
		                            <li class="nav-item">
		                                <a class="nav-link {{ (request()->routeIs('login') || request()->routeIs('password.login')) ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
		                            </li>
		                            <!--<li class="nav-item">-->
		                            <!--    <a class="nav-link {{ request()->routeIs('register.user') ? 'active' : '' }}" href="{{ route('register.user') }}">Register</a>-->
		                            <!--</li>-->
		                        @endauth
		                    </ul>
	                </div>
                <div class="navbar-actions">
                    <div class="navbar-meta">
                        <strong>Book a guided walkthrough</strong>
                        <span>Fast onboarding for your team</span>
                    </div>
                    <a href="{{ route('ContactUs') }}" class="btn btn-primary navbar-cta">Book Demo</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-vh-100">@yield('content')</main>

	    <footer class="py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5>TechBridge Partners India Private Limited</h5>
                    <p>We build business management and Line Management ERP solutions that automate finance, operations, compliance, and team workflows.</p>
                </div>
                <div class="col-md-4">
                    <h6>MeriAccounting Highlights</h6>
                    <ul class="list-unstyled mb-0">
                        <li>Real-time inventory and ledgers</li>
                        <li>GST, TDS, TCS, ESI, PF compliance</li>
                        <li>Role-based access with audit trail</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Contact</h6>
                    <p class="mb-1">Email: techbridgepartnersindiapvtltd@gmail.com</p>
                    <p class="mb-1">Phone: +91-7404661205</p>
                    <p class="mb-0">Available for demos across India and global teams.</p>
                </div>
            </div>
            <hr class="border-secondary-subtle my-4">
            <p class="mb-0 small">&copy; {{ date('Y') }} TechBridge Partners India Private Limited. All rights reserved.</p>
        </div>
    </footer>

	    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	    <script>
	        document.addEventListener('DOMContentLoaded', function () {
	            const collapseEl = document.getElementById('mainNav');
	            if (!collapseEl || typeof bootstrap === 'undefined' || !bootstrap.Collapse) return;

	            const isMobile = () =>
	                typeof window.matchMedia === 'function' &&
	                window.matchMedia('(max-width: 991.98px)').matches;

	            const collapse =
	                typeof bootstrap.Collapse.getOrCreateInstance === 'function'
	                    ? bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false })
	                    : (bootstrap.Collapse.getInstance(collapseEl) ||
	                        new bootstrap.Collapse(collapseEl, { toggle: false }));

	            let lastScrollY = window.scrollY;

	            window.addEventListener(
	                'scroll',
	                function () {
	                    if (!isMobile()) return;
	                    if (!collapseEl.classList.contains('show')) return;

	                    const currentScrollY = window.scrollY;
	                    const delta = Math.abs(currentScrollY - lastScrollY);
	                    lastScrollY = currentScrollY;

	                    if (delta < 6) return;
	                    collapse.hide();
	                },
	                { passive: true }
	            );
	        });
	    </script>
	    @stack('scripts')
	</body>
	</html>
