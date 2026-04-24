@extends('layouts.landingapp')

@section('title', 'MeriAccounting Features | TechBridge Software')

<style>
    :root {
        --feature-navy: #0f172a;
        --feature-blue: #0f6cbd;
        --feature-cyan: #3ab7bf;
        --feature-soft: #eef6ff;
        --feature-surface: #ffffff;
        --feature-text: #18324b;
        --feature-text-soft: #5f7186;
        --feature-border: rgba(15, 23, 42, 0.09);
        --feature-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        --feature-shadow-strong: 0 24px 60px rgba(15, 23, 42, 0.16);
    }

    .features-hero {
        position: relative;
        overflow: hidden;
        padding: 106px 0 86px;
        background:
            radial-gradient(circle at top left, rgba(58, 183, 191, 0.26), transparent 34%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.22), transparent 34%),
            linear-gradient(135deg, #07111f 0%, #0f2740 54%, #0f6cbd 100%);
    }

    .features-hero::before,
    .features-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        opacity: 0.95;
    }

    .features-hero::before {
        width: 380px;
        height: 380px;
        top: -140px;
        right: -90px;
        background: rgba(255, 255, 255, 0.08);
    }

    .features-hero::after {
        width: 320px;
        height: 320px;
        bottom: -120px;
        left: -110px;
        background: rgba(58, 183, 191, 0.18);
    }

    .features-hero .container {
        position: relative;
        z-index: 2;
    }

    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: rgba(226, 232, 240, 0.96);
        font-size: 0.86rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        width: fit-content;
    }

    .hero-title {
        font-size: clamp(2.6rem, 4.6vw, 4.6rem);
        line-height: 1.06;
        font-weight: 900;
        color: #ffffff;
        margin: 18px 0 0;
    }

    .hero-title .text-gradient {
        background: linear-gradient(90deg, #a5f3fc 0%, #ffffff 55%, #bfdbfe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-copy {
        color: rgba(226, 232, 240, 0.92);
        font-size: 1.06rem;
        line-height: 1.85;
        margin: 18px 0 0;
    }

    .hero-actions .btn {
        border-radius: 14px;
        padding: 14px 22px;
        font-weight: 800;
    }

    .hero-showcase {
        position: relative;
        border-radius: 26px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 28px 70px rgba(2, 6, 23, 0.42);
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(14px);
        padding: 18px;
        height: 100%;
    }

    .hero-showcase__frame {
        overflow: hidden;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.04);
    }

    .hero-showcase img {
        width: 100%;
        height: 100%;
        min-height: 360px;
        object-fit: cover;
        display: block;
    }

    .hero-mini {
        position: absolute;
        bottom: 24px;
        left: 24px;
        right: 24px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
    }

    .hero-mini strong {
        display: block;
        color: var(--feature-navy);
        font-weight: 900;
        margin-bottom: 4px;
    }

    .hero-mini span {
        color: var(--feature-text-soft);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .section-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(15, 108, 189, 0.1);
        color: var(--feature-blue);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .section-title {
        font-size: clamp(2rem, 3vw, 3.2rem);
        font-weight: 900;
        line-height: 1.12;
        color: var(--feature-navy);
    }

    .section-copy {
        font-size: 1.02rem;
        line-height: 1.8;
        color: var(--feature-text-soft);
    }

    .group-shell {
        border-radius: 26px;
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: var(--feature-shadow);
        padding: 34px 28px;
    }

    .feature-card {
        border-radius: 18px;
        background: var(--feature-surface);
        border: 1px solid var(--feature-border);
        box-shadow: var(--feature-shadow);
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .feature-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--feature-shadow-strong);
    }

    .feature-card__media {
        position: relative;
        height: 170px;
        overflow: hidden;
    }

    .feature-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .feature-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.04) 0%, rgba(15, 23, 42, 0.7) 100%);
        pointer-events: none;
    }

    .feature-card__icon {
        position: absolute;
        left: 18px;
        bottom: -22px;
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(15, 108, 189, 0.98) 0%, rgba(58, 183, 191, 0.96) 100%);
        color: #ffffff;
        border: 4px solid #ffffff;
        box-shadow: 0 18px 42px rgba(2, 6, 23, 0.22);
    }

    .feature-card__icon i {
        font-size: 1.3rem;
    }

    .feature-card__code {
        position: absolute;
        top: 14px;
        right: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: var(--feature-blue);
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        font-size: 0.92rem;
        border: 1px solid rgba(255, 255, 255, 0.9);
    }

    .feature-card__body {
        padding: 38px 22px 22px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1 1 auto;
    }

    .feature-card__body h4 {
        margin: 0;
        font-weight: 900;
        font-size: 1.1rem;
        line-height: 1.35;
        color: var(--feature-navy);
    }

    .feature-card__body p {
        margin: 0;
        color: var(--feature-text-soft);
        line-height: 1.65;
        font-size: 0.98rem;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 8px 0 0;
        display: grid;
        gap: 10px;
    }

    .feature-list li {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        color: var(--feature-text);
        line-height: 1.55;
        font-size: 0.98rem;
    }

    .feature-list i {
        margin-top: 3px;
        color: var(--feature-cyan);
    }

    .cta-panel {
        border-radius: 26px;
        padding: 46px 36px;
        background: linear-gradient(135deg, rgba(15, 108, 189, 0.14), rgba(58, 183, 191, 0.12));
        border: 1px solid rgba(15, 108, 189, 0.16);
        box-shadow: var(--feature-shadow);
    }

    .cta-panel h3 {
        font-weight: 900;
        color: var(--feature-navy);
        margin-bottom: 10px;
    }

    .cta-panel p {
        color: var(--feature-text-soft);
        margin-bottom: 0;
        line-height: 1.8;
    }

    @media (max-width: 575px) {
        .feature-card__media {
            height: 160px;
        }
        .feature-card__body {
            padding: 38px 18px 20px;
        }
    }
</style>

@section('content')

@php

    $featureGroups = [
        [
            'title' => 'Core highlights',
            'copy' => 'Designed for everyday users and finance teams—clean UI, strong security and fast operations.',
            'items' => [
                [
                    'code' => '1',
                    'icon' => 'bi-shield-check',
                    'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Easy to use, secure and accessible',
                    'subtitle' => 'A user-friendly interface that works on both PC and smartphone.',
                    'points' => [
                        'User-friendly interface simple enough for anyone to navigate',
                        'Securely protects your sensitive data with robust controls',
                        'Accessible on both PC and smartphone',
                    ],
                ],
                [
                    'code' => 'UNI',
                    'icon' => 'bi-columns-gap',
                    'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'One unified platform',
                    'subtitle' => 'Eliminate dependency on Excel or Google Sheets—manage everything in one place.',
                    'points' => [
                        'Centralised workspace for billing, accounts, inventory and compliance',
                        'Cleaner reporting with one consistent dataset',
                        'Reduced manual work across tools and spreadsheets',
                    ],
                ],
                [
                    'code' => 'INS',
                    'icon' => 'bi-graph-up-arrow',
                    'image' => 'https://images.unsplash.com/photo-1556155092-490a1ba16284?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Instant financial insights',
                    'subtitle' => 'Access real-time financial insights and reports instantly.',
                    'points' => [
                        'Fast dashboards for key financial signals',
                        'Better decision making with updated numbers',
                        'Reports that don’t require manual consolidation',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Sales, billing and collections',
            'copy' => 'Create invoices, track receivables and keep customer/vendor ledgers accurate in real time.',
            'items' => [
                [
                    'code' => '2',
                    'icon' => 'bi-receipt',
                    'image' => asset('public/assets/imgs/invoicepic.png'),
                    'title' => 'Sale invoices, e-invoicing and GST e-way bill',
                    'subtitle' => 'Create professional invoices and stay compliant.',
                    'points' => [
                        'Quickly generate professional sale invoices for your business',
                        'Create and manage e-invoices for seamless GST compliance',
                        'Easily generate GST e-way bills for goods transport',
                    ],
                ],
                [
                    'code' => '3',
                    'icon' => 'bi-journal-text',
                    'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Real-time customer and vendor ledgers',
                    'subtitle' => 'Keep books accurate and always up to date.',
                    'points' => [
                        'Maintain up-to-date records of all customer and vendor transactions',
                        'Track payments and balances seamlessly as they update in real-time',
                        'Stay informed about account activity for better decisions',
                    ],
                ],
                [
                    'code' => '5',
                    'icon' => 'bi-cash-coin',
                    'image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Receivables management for faster collections',
                    'subtitle' => 'Identify overdue invoices and speed up follow-ups.',
                    'points' => [
                        'Easily identify overdue invoices and focus on follow-ups',
                        'Streamline reminders and follow-ups on pending payments',
                        'Improve cash flow by speeding up payment collection',
                    ],
                ],
                [
                    'code' => '6',
                    'icon' => 'bi-phone',
                    'image' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Payment receipts updated from anywhere',
                    'subtitle' => 'Record receipts instantly and stay updated.',
                    'points' => [
                        'Instantly record and update payment receipts as transactions occur',
                        'Get real-time updates so you’re always aware of incoming payments',
                        'Access updated payment records on both PC and smartphone',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Inventory and operations',
            'copy' => 'Real-time stock visibility with streamlined product tracking and alerts.',
            'items' => [
                [
                    'code' => '4',
                    'icon' => 'bi-box-seam',
                    'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Advanced real-time inventory management',
                    'subtitle' => 'Stock visibility that prevents surprises.',
                    'points' => [
                        'Monitor stock levels in real-time to avoid overstocking or stockouts',
                        'Receive alerts for low stock and manage reorders efficiently',
                        'Keep all products and supplies organized in a streamlined system',
                    ],
                ],
                [
                    'code' => 'WF',
                    'icon' => 'bi-kanban',
                    'image' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Efficient workflow management',
                    'subtitle' => 'Integrated task-style management for streamlined productivity.',
                    'points' => [
                        'Keep operational tasks aligned with finance workflows',
                        'Improve productivity with structured follow-ups',
                        'Better visibility across work items and deadlines',
                    ],
                ],
            ],
        ],
        [
            'title' => 'GST and compliance suite',
            'copy' => 'Matching, filing and compliance controls designed for GST-heavy workflows.',
            'items' => [
                [
                    'code' => '2A',
                    'icon' => 'bi-check2-square',
                    'image' => 'https://images.unsplash.com/photo-1554224154-26032ffc0d07?q=80&w=1200&auto=format&fit=crop',
                    'title' => '2A / 2B input tax credit matching',
                    'subtitle' => 'Quick mismatch visibility and ITC tracking.',
                    'points' => [
                        'Fast and accurate ITC matching to reduce credit leakage',
                        'Vendor-wise mismatch tracking to follow up quickly',
                        'Organised ITC tracking for monthly compliance readiness',
                    ],
                ],
                [
                    'code' => 'R1',
                    'icon' => 'bi-file-earmark-text',
                    'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'One-click GST R-1 filing',
                    'subtitle' => 'Simplified GST compliance and return preparation.',
                    'points' => [
                        'One-click GST R-1 filing flow for faster submissions',
                        'Create return-ready data from your sales and invoices',
                        'Keep compliance organised across periods',
                    ],
                ],
                [
                    'code' => '3B',
                    'icon' => 'bi-clipboard-check',
                    'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'GST 3B-ready outputs',
                    'subtitle' => 'Prepare summary return data faster.',
                    'points' => [
                        '3B-ready summaries for quicker preparation',
                        'Reconciliation flow to align books with portal data',
                        'Clear month-end GST cockpit view',
                    ],
                ],
                [
                    'code' => 'CMP',
                    'icon' => 'bi-shield-exclamation',
                    'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Comprehensive compliance monitoring',
                    'subtitle' => 'End-to-end monitoring with clear visibility.',
                    'points' => [
                        'End-to-end compliance monitoring across key filings',
                        'Better visibility on pending tasks and follow-ups',
                        'Reduced compliance risk with structured tracking',
                    ],
                ],
                [
                    'code' => 'TDS',
                    'icon' => 'bi-currency-rupee',
                    'image' => 'https://images.unsplash.com/photo-1554224154-22dec7ec8818?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'TDS and TCS compliance handling',
                    'subtitle' => 'Accurate filing and payments tracking.',
                    'points' => [
                        'Manage TDS and TCS filings thoroughly and accurately',
                        'Maintain payment schedules with clear tracking',
                        'Reduce errors with structured compliance workflows',
                    ],
                ],
            ],
        ],
        [
            'title' => 'Payroll and controls',
            'copy' => 'Payroll, attendance, statutory compliance and system controls for secure operations.',
            'items' => [
                [
                    'code' => 'PAY',
                    'icon' => 'bi-credit-card-2-front',
                    'image' => 'https://images.unsplash.com/photo-1450101215322-bf5cd27642fc?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Payroll management',
                    'subtitle' => 'Run payroll with predictable processes.',
                    'points' => [
                        'Payroll calculations with organised records',
                        'Pay slip and payroll register visibility',
                        'Aligned compliance tracking for payroll activities',
                    ],
                ],
                [
                    'code' => 'ATT',
                    'icon' => 'bi-calendar-check',
                    'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Attendance tracking',
                    'subtitle' => 'Maintain attendance records for payroll readiness.',
                    'points' => [
                        'Attendance entries for employee working days and leave',
                        'Month-wise attendance visibility for HR teams',
                        'Better payroll accuracy with cleaner inputs',
                    ],
                ],
                [
                    'code' => 'ESI',
                    'icon' => 'bi-file-medical',
                    'image' => 'https://images.unsplash.com/photo-1588200908342-23b585c03e26?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'ESI and PF compliance management',
                    'subtitle' => 'Manage filings and payments efficiently.',
                    'points' => [
                        'Manage ESI and PF filings and payments in full compliance',
                        'Maintain clear records for audits and reviews',
                        'Reduce manual tracking with centralised workflows',
                    ],
                ],
                [
                    'code' => 'ACL',
                    'icon' => 'bi-person-lock',
                    'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Role-based access control',
                    'subtitle' => 'Data security and relevance based on user roles.',
                    'points' => [
                        'Role-wise access for admin, sales and accounts users',
                        'Protect sensitive financial data with permissions',
                        'Clear separation of duties for better governance',
                    ],
                ],
                [
                    'code' => 'ATR',
                    'icon' => 'bi-journal-check',
                    'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=1200&auto=format&fit=crop',
                    'title' => 'Comprehensive audit trail',
                    'subtitle' => 'Full transparency on edits and deletions.',
                    'points' => [
                        'Track modifications with detailed edit logs',
                        'Delete logs for complete data transparency',
                        'Stronger control for approvals and reviews',
                    ],
                ],
            ],
        ],
    ];
@endphp

<section class="features-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-pill mb-3">
                    <i class="bi bi-stars"></i>
                    Modules that cover finance, GST, compliance and payroll
                </div>
                <h1 class="hero-title">
                    All the <span class="text-gradient">features</span> your business needs
                </h1>
                <p class="hero-copy">
                    Explore everything included in MeriAccounting—from billing and inventory to GST (2A/2B, R-1, 3B), compliance, payroll and attendance.
                </p>

                <div class="hero-actions d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a href="{{ route('pricing') }}" class="btn btn-light fw-bold">
                        View Pricing
                    </a>
                    <a href="{{ route('ContactUs') }}" class="btn btn-outline-light fw-bold">
                        Book a Demo
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-showcase">
                    <div class="hero-showcase__frame">
                        <img
                            src="https://images.unsplash.com/photo-1556761175-129418cb2dfe?q=80&w=1400&auto=format&fit=crop"
                            alt="Modern finance and operations dashboard"
                            loading="lazy"
                        >
                    </div>
                    <div class="hero-mini">
                        <strong>Built for Indian compliance workflows</strong>
                        <span>2A/2B matching, R-1 filing, 3B-ready summaries, payroll, attendance and controls.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light" id="all-features">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Feature Library</span>
            <h2 class="section-title mb-3">Everything in one platform</h2>
            <p class="section-copy col-lg-9 mx-auto">
                Each card below shows the capability set with publicly available imagery and the exact modules requested (including 2A/2B, 3B, payroll management and attendance).
            </p>
        </div>

        @foreach ($featureGroups as $group)
            <div class="group-shell mb-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
                    <div>
                        <h3 class="h4 fw-bold mb-1" style="color: var(--feature-navy);">{{ $group['title'] }}</h3>
                        <p class="section-copy mb-0">{{ $group['copy'] }}</p>
                    </div>
                </div>

                <div class="row g-4">
                    @foreach ($group['items'] as $item)
                        <div class="col-md-6 col-xl-4">
                            <div class="feature-card">
                                <div class="feature-card__media">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                    <div class="feature-card__overlay"></div>
                                    <span class="feature-card__code">{{ $item['code'] }}</span>
                                    <span class="feature-card__icon">
                                        <i class="bi {{ $item['icon'] }}"></i>
                                    </span>
                                </div>
                                <div class="feature-card__body">
                                    <h4>{{ $item['title'] }}</h4>
                                    <p>{{ $item['subtitle'] }}</p>
                                    <ul class="feature-list">
                                        @foreach ($item['points'] as $point)
                                            <li>
                                                <i class="bi bi-check-circle-fill"></i>
                                                <span>{{ $point }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="cta-panel text-center mt-5">
            <h3>Want a tailored rollout plan?</h3>
            <p class="mb-4">Tell us your industry and workflow—we’ll recommend the right modules and setup.</p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-3">
                <a href="{{ route('ContactUs') }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold">
                    Talk to an expert
                </a>
                <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-lg rounded-pill px-5 fw-bold">
                    See Pricing
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
