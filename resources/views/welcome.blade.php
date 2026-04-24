@extends('layouts.landingapp')

<style>
:root {
    --brand-navy: #0f172a;
    --brand-blue: #0f6cbd;
    --brand-cyan: #3ab7bf;
    --brand-soft: #eef6ff;
    --surface: #ffffff;
    --surface-muted: #f8fbff;
    --text-main: #18324b;
    --text-soft: #5f7186;
    --border-soft: rgba(15, 23, 42, 0.08);
    --shadow-soft: 0 18px 40px rgba(15, 23, 42, 0.08);
    --shadow-strong: 0 24px 60px rgba(15, 23, 42, 0.18);
}

body {
    color: var(--text-main);
    background: #f4f8fc;
}

.section-padding {
    padding: 96px 0;
}

.section-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(15, 108, 189, 0.1);
    color: var(--brand-blue);
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.section-title {
    font-size: clamp(2rem, 3vw, 3.4rem);
    font-weight: 800;
    line-height: 1.12;
    color: var(--brand-navy);
}

.section-copy {
    font-size: 1.02rem;
    line-height: 1.8;
    color: var(--text-soft);
}

.hero-dashboard {
    position: relative;
    overflow: hidden;
    padding: 110px 0 88px;
    background:
        radial-gradient(circle at top left, rgba(58, 183, 191, 0.28), transparent 34%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.22), transparent 34%),
        linear-gradient(135deg, #07111f 0%, #0f2740 54%, #0f6cbd 100%);
}

.hero-dashboard::before,
.hero-dashboard::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(10px);
}

.hero-dashboard::before {
    width: 360px;
    height: 360px;
    top: -120px;
    right: -80px;
    background: rgba(255, 255, 255, 0.08);
}

.hero-dashboard::after {
    width: 300px;
    height: 300px;
    bottom: -110px;
    left: -90px;
    background: rgba(58, 183, 191, 0.18);
}

.hero-dashboard .container {
    position: relative;
    z-index: 2;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #e0f2fe;
    font-size: 0.86rem;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.hero-title {
    font-size: clamp(2.7rem, 4.8vw, 4.8rem);
    line-height: 1.04;
    font-weight: 800;
    color: #ffffff;
}

.text-gradient {
    background: linear-gradient(90deg, #a5f3fc 0%, #ffffff 55%, #bfdbfe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-copy {
    max-width: 620px;
    color: rgba(226, 232, 240, 0.92);
    font-size: 1.06rem;
    line-height: 1.85;
}

.hero-actions .btn {
    border-radius: 14px;
    padding: 14px 24px;
    font-weight: 700;
}

.hero-actions .btn-light {
    color: var(--brand-navy);
}

.hero-actions .btn-outline-light {
    border-width: 1.5px;
}

.hero-stat-strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-top: 34px;
}

.hero-stat-card {
    padding: 18px 18px 16px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.09);
    border: 1px solid rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(12px);
}

.hero-stat-card strong {
    display: block;
    font-size: 1.25rem;
    color: #ffffff;
}

.hero-stat-card span {
    color: rgba(226, 232, 240, 0.88);
    font-size: 0.92rem;
}

.dashboard-shell {
    position: relative;
    padding: 22px;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: var(--shadow-strong);
    backdrop-filter: blur(16px);
}

.dashboard-frame {
    overflow: hidden;
    border-radius: 22px;
    background: #fff;
}

.dashboard-frame img {
    width: 100%;
    height: 100%;
    min-height: 460px;
    object-fit: cover;
}

.floating-insight {
    position: absolute;
    padding: 14px 16px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 18px 32px rgba(15, 23, 42, 0.16);
    min-width: 200px;
}

.floating-insight small {
    display: block;
    color: var(--text-soft);
    margin-bottom: 4px;
}

.floating-insight strong {
    color: var(--brand-navy);
    font-size: 1rem;
}

.insight-top {
    top: 20px;
    right: -22px;
}

.insight-bottom {
    bottom: 26px;
    left: -26px;
}

.grid-section {
    background:
        linear-gradient(180deg, #f4f8fc 0%, #ffffff 100%);
}

.why-panel,
.feature-card,
.gst-card,
.ops-card,
.platform-card,
.compliance-card,
.cta-panel {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    box-shadow: var(--shadow-soft);
}

.why-panel {
    padding: 28px;
    border-radius: 24px;
}

.why-panel img {
    border-radius: 20px;
    min-height: 420px;
    object-fit: cover;
}

.why-point {
    display: flex;
    gap: 14px;
    padding: 16px 0;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.why-point:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.icon-pill {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(15, 108, 189, 0.12), rgba(58, 183, 191, 0.18));
    color: var(--brand-blue);
    font-size: 1.2rem;
}

.why-point h6,
.feature-card h5,
.gst-card h5,
.ops-card h5,
.platform-card h5,
.compliance-card h5 {
    margin-bottom: 6px;
    font-weight: 700;
    color: var(--brand-navy);
}

.why-point p,
.feature-card p,
.gst-card p,
.ops-card p,
.platform-card p,
.compliance-card p {
    margin: 0;
    color: var(--text-soft);
    line-height: 1.7;
}

.feature-card {
    height: 100%;
    border-radius: 24px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.feature-card:hover,
.gst-card:hover,
.ops-card:hover,
.platform-card:hover,
.compliance-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 45px rgba(15, 23, 42, 0.12);
}

.feature-image {
    position: relative;
    height: 210px;
    background-size: cover;
    background-position: center;
}

.feature-image::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(7, 17, 31, 0.12), rgba(15, 108, 189, 0.4));
}

.feature-icon {
    position: absolute;
    left: 24px;
    bottom: -24px;
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand-blue);
    font-size: 1.35rem;
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
    z-index: 2;
}

.feature-body {
    padding: 42px 24px 24px;
}

.feature-list {
    margin: 0;
    padding-left: 18px;
    color: var(--text-soft);
}

.feature-list li + li {
    margin-top: 8px;
}

.gst-section {
    background:
        radial-gradient(circle at top right, rgba(58, 183, 191, 0.14), transparent 30%),
        linear-gradient(180deg, #ebf5ff 0%, #f8fbff 100%);
}

.gst-showcase {
    position: sticky;
    top: 100px;
}

.gst-visual {
    position: relative;
    padding: 20px;
    border-radius: 26px;
    background: linear-gradient(180deg, #ffffff, #eef6ff);
    border: 1px solid rgba(15, 108, 189, 0.1);
    box-shadow: var(--shadow-soft);
}

.gst-visual img {
    width: 100%;
    min-height: 460px;
    object-fit: cover;
    border-radius: 20px;
}

.gst-mini-card {
    position: absolute;
    right: 24px;
    bottom: 24px;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.14);
}

.gst-mini-card strong {
    display: block;
    color: var(--brand-navy);
}

.gst-mini-card span {
    color: var(--text-soft);
    font-size: 0.92rem;
}

.gst-card {
    height: 100%;
    padding: 24px;
    border-radius: 22px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.gst-card .icon-pill {
    margin-bottom: 18px;
}

.gst-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
    margin-top: 24px;
}

.gst-summary-item {
    padding: 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(15, 108, 189, 0.08);
}

.gst-summary-item strong {
    display: block;
    font-size: 1.05rem;
    color: var(--brand-navy);
}

.gst-summary-item span {
    color: var(--text-soft);
    font-size: 0.92rem;
}

.ops-card,
.platform-card,
.compliance-card {
    height: 100%;
    border-radius: 24px;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.ops-card img,
.compliance-card img {
    width: 100%;
    height: 210px;
    object-fit: cover;
}

.ops-body,
.compliance-body {
    padding: 24px;
}

.platform-card {
    padding: 28px;
}

.platform-kpi {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 62px;
    height: 62px;
    border-radius: 18px;
    margin-bottom: 18px;
    background: linear-gradient(135deg, rgba(15, 108, 189, 0.14), rgba(58, 183, 191, 0.18));
    color: var(--brand-blue);
    font-size: 1.35rem;
}

.cta-wrapper {
    background: linear-gradient(135deg, #08111f 0%, #0f2740 48%, #0f6cbd 100%);
}

.cta-panel {
    border-radius: 28px;
    padding: 42px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.04));
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: none;
}

.cta-panel h2,
.cta-panel p,
.cta-list {
    color: #ffffff;
}

.cta-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 20px;
    margin-top: 18px;
    font-size: 0.95rem;
}

.cta-list span {
    color: rgba(226, 232, 240, 0.92);
}

@media (max-width: 991.98px) {
    .hero-dashboard {
        padding: 90px 0 70px;
    }

    .hero-stat-strip,
    .gst-summary {
        grid-template-columns: 1fr;
    }

    .gst-showcase {
        position: static;
    }

    .dashboard-frame img,
    .gst-visual img,
    .why-panel img {
        min-height: 320px;
    }

    .insight-top,
    .insight-bottom {
        position: static;
        margin-top: 16px;
    }
}

@media (max-width: 767.98px) {
    .section-padding {
        padding: 72px 0;
    }

    .hero-title {
        font-size: 2.45rem;
    }

    .hero-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .hero-actions .btn {
        width: 100%;
    }

    .hero-stat-strip {
        gap: 12px;
    }

    .dashboard-shell,
    .why-panel,
    .gst-card,
    .platform-card,
    .ops-card,
    .compliance-card,
    .cta-panel {
        border-radius: 22px;
    }

    .cta-panel {
        padding: 30px 24px;
    }
}
</style>

@section('title', 'MeriAccounting ERP | Smart Business Accounting')

@section('content')
@php
    $heroStats = [
        ['value' => '100%', 'label' => 'GST-ready workflows'],
        ['value' => '24/7', 'label' => 'Cloud dashboard access'],
        ['value' => '1 View', 'label' => 'Finance, tax and ops data'],
    ];

    $whyPoints = [
        [
            'icon' => 'bi-shield-check',
            'title' => 'Trusted controls for finance teams',
            'copy' => 'Approval layers, audit visibility and secure access keep every transaction accountable.',
        ],
        [
            'icon' => 'bi-lightning-charge',
            'title' => 'Faster daily execution',
            'copy' => 'Automate GST, invoicing, receivables and reconciliations from one operational workspace.',
        ],
        [
            'icon' => 'bi-graph-up-arrow',
            'title' => 'Live business visibility',
            'copy' => 'Track collections, tax exposure, stock movement and team activity with real-time reporting.',
        ],
        [
            'icon' => 'bi-grid-1x2',
            'title' => 'One connected platform',
            'copy' => 'Replace disconnected spreadsheets and tools with a unified ERP built for growing businesses.',
        ],
    ];

    $features = [
        [
            'title' => 'Sales Invoice & GST',
            'points' => ['Create branded invoices', 'Generate e-invoices and e-way bills', 'Stay aligned with tax rules'],
            'icon' => 'bi-receipt-cutoff',
            'image' => asset('public/assets/imgs/invoicepic.png'),
        ],
        [
            'title' => 'Ledger Management',
            'points' => ['Track every customer and vendor ledger', 'Monitor live balances', 'Keep books clean and searchable'],
            'icon' => 'bi-journal-text',
            'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Inventory Management',
            'points' => ['Monitor stock across items', 'Set low stock alerts', 'Link inventory to billing and purchase flow'],
            'icon' => 'bi-box-seam',
            'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Receivables Management',
            'points' => ['Track overdue invoices', 'Send payment follow-ups faster', 'Improve collection efficiency'],
            'icon' => 'bi-cash-coin',
            'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Payment Tracking',
            'points' => ['Record receipts instantly', 'Reconcile payment entries', 'View cash movement in one timeline'],
            'icon' => 'bi-phone',
            'image' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Team Management',
            'points' => ['Assign tasks clearly', 'Track workload and progress', 'Collaborate around shared records'],
            'icon' => 'bi-people',
            'image' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1200&auto=format&fit=crop',
        ],
    ];

    $gstModules = [
        [
            'icon' => 'bi-files',
            'title' => 'GST 2A Matching',
            'copy' => 'Compare purchase data with supplier uploads to quickly identify missing or mismatched entries.',
        ],
        [
            'icon' => 'bi-check2-square',
            'title' => 'GST 2B Matching',
            'copy' => 'Validate eligible ITC with statement-based matching before you finalize monthly filings.',
        ],
        [
            'icon' => 'bi-arrow-left-right',
            'title' => 'GST Reconciliation',
            'copy' => 'Reconcile books, purchase registers and portal data to reduce errors and notices.',
        ],
        [
            'icon' => 'bi-file-earmark-bar-graph',
            'title' => 'GST 3B Filing',
            'copy' => 'Prepare summary returns with tax liability, ITC utilization and payment tracking in one flow.',
        ],
        [
            'icon' => 'bi-receipt',
            'title' => 'GSTR-1 Filing',
            'copy' => 'Upload outward supplies with cleaner invoice data and streamlined return preparation.',
        ],
    ];

    $operations = [
        [
            'title' => 'Compliance Monitoring',
            'copy' => 'Watch deadlines, filing status and exception queues from a single compliance command center.',
            'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Audit Trail',
            'copy' => 'Track user actions, document updates and approval history for stronger internal controls.',
            'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Role-based Access',
            'copy' => 'Give finance, sales and management the exact level of access they need without overlap.',
            'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop',
        ],
    ];

    $platformCards = [
        [
            'icon' => 'bi-file-earmark-spreadsheet',
            'title' => 'Replace manual sheets',
            'copy' => 'Move from spreadsheet chaos to structured workflows with searchable business data.',
        ],
        [
            'icon' => 'bi-speedometer2',
            'title' => 'Real-time dashboards',
            'copy' => 'Watch sales, collections, compliance and inventory health without waiting for reports.',
        ],
        [
            'icon' => 'bi-diagram-3',
            'title' => 'Connected business modules',
            'copy' => 'Invoices, ledgers, GST and controls stay linked so teams work from the same truth.',
        ],
        [
            'icon' => 'bi-cloud-check',
            'title' => 'Cloud-first access',
            'copy' => 'Run operations from office, home or on the move with secure browser-based access.',
        ],
    ];

    $complianceCards = [
        [
            'title' => 'GST Compliance',
            'copy' => 'Handle returns, matching, reconciliation and reporting with a structured monthly process.',
            'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Income Tax Compliance',
            'copy' => 'Support deductions, submissions and reporting with better data discipline and visibility.',
            'image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'ROC & Company Compliance',
            'copy' => 'Manage recurring corporate filings, records and board-led obligations more confidently.',
            'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Internal Audit & Controls',
            'copy' => 'Strengthen governance with documented trails, exception checks and review-ready records.',
            'image' => 'https://images.unsplash.com/photo-1518186285589-2f7649de83e0?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'Payroll Compliance',
            'copy' => 'Support statutory payroll processes, benefits handling and employee compliance tracking.',
            'image' => 'https://images.unsplash.com/photo-1554224154-26032ffc0d07?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'TDS & TCS Handling',
            'copy' => 'Track deductions, tax collection records and periodic filing requirements in one system.',
            'image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=1200&auto=format&fit=crop',
        ],
    ];
@endphp

<section class="hero-dashboard">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge mb-4">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    Smart ERP for finance-led growth
                </span>

                <h1 class="hero-title mb-4">
                   Accounting, GST, compliance, payroll and operations in 
                    <span class="text-gradient">single secure system</span>
                </h1>

                <p class="hero-copy mb-4">
                    MeriAccounting brings invoicing, compliance, reconciliation, controls and live reporting
                    together so your team can move faster with cleaner data and stronger visibility.
               </p>
	
	                <div class="hero-actions d-flex flex-wrap gap-3">
	                    @auth
	                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg">
	                            Go to Dashboard
	                        </a>
	                    @else
	                        <a href="{{ route('login') }}" class="btn btn-light btn-lg">
	                            Login
	                        </a>
	                    @endauth
	                    <a href="{{ route('ContactUs') }}" class="btn btn-outline-light btn-lg">
	                        Request Demo
	                    </a>
	                    <a href="{{ route('pricing') }}" class="btn btn-outline-light btn-lg">
	                        Explore Features
	                    </a>
	                </div>

                <div class="hero-stat-strip">
                    @foreach ($heroStats as $stat)
                        <div class="hero-stat-card">
                            <strong>{{ $stat['value'] }}</strong>
                            <span>{{ $stat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6">
                <div class="dashboard-shell">
                    <div class="dashboard-frame">
                        <img
                            src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=1400&auto=format&fit=crop"
                            alt="Business analytics dashboard"
                            loading="lazy"
                        >
                    </div>

                    <div class="floating-insight insight-top">
                        <small>Compliance status</small>
                        <strong>Returns on track</strong>
                    </div>

                    <div class="floating-insight insight-bottom">
                        <small>Collections + tax visibility</small>
                        <strong>One dashboard, faster decisions</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding grid-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="why-panel">
                    <img
                      src="{{ URL::asset('public/assets/imgs/Whypic.png') }}" 
                        class="img-fluid w-100"
                        alt="Business dashboard interface"
                    >
                </div>
            </div>

            <div class="col-lg-6">
                <span class="section-eyebrow mb-3">Why MeriAccounting</span>
                <h2 class="section-title mb-3">Built for teams that want control, speed and cleaner reporting</h2>
                <p class="section-copy mb-4">
                    The welcome experience now mirrors a modern dashboard product: sharper visual hierarchy,
                    more confidence-building content and dedicated coverage for GST-heavy workflows.
                </p>

                <div class="why-panel">
                    @foreach ($whyPoints as $point)
                        <div class="why-point">
                            <span class="icon-pill">
                                <i class="bi {{ $point['icon'] }}"></i>
                            </span>
                            <div>
                                <h6>{{ $point['title'] }}</h6>
                                <p>{{ $point['copy'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Core Modules</span>
            <h2 class="section-title mb-3">Powerful features across accounting, billing and operations</h2>
            <p class="section-copy col-lg-8 mx-auto">
                Every module is presented with a more polished card system so the page feels closer to a SaaS dashboard
                than a generic services page.
            </p>
        </div>

	        <div class="row g-4">
	            @foreach ($features as $feature)
	                <div class="col-md-6 col-xl-4">
	                    <div class="feature-card">
                        <div class="feature-image" style="background-image: url('{{ $feature['image'] }}');">
                            <div class="feature-icon">
                                <i class="bi {{ $feature['icon'] }}"></i>
                            </div>
                        </div>

                        <div class="feature-body">
                            <h5>{{ $feature['title'] }}</h5>
                            <ul class="feature-list">
                                @foreach ($feature['points'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>

		                            <a href="{{ route('features') }}" class="btn btn-outline-primary mt-4 rounded-pill px-4">
		                                Learn More
		                            </a>
	                        </div>
	                    </div>
	                </div>
	            @endforeach
	        </div>

		        <div class="text-center mt-5">
		            <a href="{{ route('features') }}" class="btn btn-primary btn-lg rounded-pill px-5">
		                Explore More Features
		            </a>
		        </div>
		    </div>
		</section>

<section class="section-padding gst-section">
    <div class="container">
        <div class="row align-items-start g-5">
            <div class="col-lg-5">
                <div class="gst-showcase">
                    <span class="section-eyebrow mb-3">GST Workspace</span>
                    <h2 class="section-title mb-3">A stronger GST section with the workflows finance teams actually need</h2>
                    <p class="section-copy mb-4">
                        The GST area now highlights the full monthly cycle, from matching and reconciliation to return filing,
                        so visitors immediately understand the platform depth.
                    </p>

                    <div class="gst-summary">
                        <div class="gst-summary-item">
                            <strong>2A + 2B checks</strong>
                            <span>Quick vendor mismatch visibility</span>
                        </div>
                        <div class="gst-summary-item">
                            <strong>3B-ready outputs</strong>
                            <span>Prepare summary return data faster</span>
                        </div>
                        <div class="gst-summary-item">
                            <strong>ITC tracking</strong>
                            <span>Stay on top of eligible credits</span>
                        </div>
                        <div class="gst-summary-item">
                            <strong>Reconciliation flow</strong>
                            <span>Books, portal and return alignment</span>
                        </div>
                    </div>

                    <div class="gst-visual mt-4">
                        <img
                            src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop"
                            alt="Tax compliance and financial review"
                            loading="lazy"
                        >
                        <div class="gst-mini-card">
                            <strong>Monthly GST cockpit</strong>
                            <span>Matching, returns and reconciliation in one view</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-4">
                    @foreach ($gstModules as $module)
                        <div class="col-md-6 {{ $loop->last ? 'col-md-12' : '' }}">
                            <div class="gst-card">
                                <span class="icon-pill">
                                    <i class="bi {{ $module['icon'] }}"></i>
                                </span>
                                <h5>{{ $module['title'] }}</h5>
                                <p>{{ $module['copy'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Control Center</span>
            <h2 class="section-title mb-3">Professional operational controls for a modern finance desk</h2>
            <p class="section-copy col-lg-8 mx-auto">
                These sections were refreshed to feel more executive-facing, with cleaner spacing, stronger imagery
                and clearer value statements.
            </p>
        </div>

        <div class="row g-4">
            @foreach ($operations as $operation)
                <div class="col-md-4">
                    <div class="ops-card">
                        <img src="{{ $operation['image'] }}" alt="{{ $operation['title'] }}" loading="lazy">
                        <div class="ops-body">
                            <h5>{{ $operation['title'] }}</h5>
                            <p>{{ $operation['copy'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding grid-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Unified Platform</span>
            <h2 class="section-title mb-3">Everything points back to one connected dashboard</h2>
            <p class="section-copy col-lg-8 mx-auto">
                Instead of isolated marketing blocks, this section now reinforces a platform narrative that feels
                closer to enterprise software positioning.
            </p>
        </div>

        <div class="row g-4">
            @foreach ($platformCards as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="platform-card">
                        <span class="platform-kpi">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                        <h5>{{ $card['title'] }}</h5>
                        <p>{{ $card['copy'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Compliance Coverage</span>
            <h2 class="section-title mb-3">End-to-end support across essential compliance areas</h2>
            <p class="section-copy col-lg-8 mx-auto">
                The closing coverage section has been restyled for consistency and now reads as a proper solution matrix.
            </p>
        </div>

        <div class="row g-4">
            @foreach ($complianceCards as $card)
                <div class="col-md-6 col-xl-4">
                    <div class="compliance-card">
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy">
                        <div class="compliance-body">
                            <h5>{{ $card['title'] }}</h5>
                            <p>{{ $card['copy'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding cta-wrapper">
    <div class="container">
        <div class="cta-panel">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="section-eyebrow mb-3" style="background: rgba(255,255,255,0.12); color: #e0f2fe;">
                        Ready to streamline finance operations
                    </span>
                    <h2 class="section-title mb-3" style="color: #ffffff;">
                        Bring your GST, accounting and business workflows into one sharper system
                    </h2>
                    <p class="mb-0" style="color: rgba(226, 232, 240, 0.9); line-height: 1.8;">
                        Give your team a faster way to manage filings, visibility and operational controls without juggling multiple tools.
                    </p>
                    <div class="cta-list">
                        <span><i class="bi bi-check2-circle me-2"></i>GST-ready workflows</span>
                        <span><i class="bi bi-check2-circle me-2"></i>Professional dashboard experience</span>
                        <span><i class="bi bi-check2-circle me-2"></i>Cloud access for growing teams</span>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('ContactUs') }}" class="btn btn-light btn-lg rounded-pill px-4">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
