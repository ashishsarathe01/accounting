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

.about-hero {
    position: relative;
    overflow: hidden;
    padding: 112px 0 88px;
    background:
        radial-gradient(circle at top left, rgba(58, 183, 191, 0.24), transparent 34%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.2), transparent 34%),
        linear-gradient(135deg, #07111f 0%, #0f2740 52%, #0f6cbd 100%);
}

.about-hero::before,
.about-hero::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    filter: blur(12px);
}

.about-hero::before {
    width: 320px;
    height: 320px;
    top: -110px;
    right: -70px;
    background: rgba(255, 255, 255, 0.08);
}

.about-hero::after {
    width: 260px;
    height: 260px;
    bottom: -90px;
    left: -70px;
    background: rgba(58, 183, 191, 0.18);
}

.about-hero .container {
    position: relative;
    z-index: 1;
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
    font-size: clamp(2.7rem, 4.7vw, 4.7rem);
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
    font-size: 1.05rem;
    line-height: 1.85;
}

.hero-stat-strip,
.story-metrics,
.coverage-grid,
.audience-grid {
    display: grid;
    gap: 16px;
}

.hero-stat-strip {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 34px;
}

.hero-stat-card,
.story-metric,
.coverage-card,
.audience-card,
.principle-card,
.journey-card,
.workflow-card,
.highlight-card,
.cta-panel {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    box-shadow: var(--shadow-soft);
}

.hero-stat-card {
    padding: 18px 18px 16px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.09);
    border-color: rgba(255, 255, 255, 0.14);
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

.hero-shell {
    position: relative;
    padding: 24px;
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: var(--shadow-strong);
    backdrop-filter: blur(16px);
}

.hero-frame {
    overflow: hidden;
    border-radius: 24px;
    background: #fff;
}

.hero-frame img {
    width: 100%;
    min-height: 470px;
    object-fit: cover;
}

.floating-panel {
    position: absolute;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 18px 32px rgba(15, 23, 42, 0.16);
    min-width: 200px;
}

.floating-panel small {
    display: block;
    color: var(--text-soft);
    margin-bottom: 4px;
}

.floating-panel strong {
    color: var(--brand-navy);
    font-size: 1rem;
}

.floating-panel.top {
    top: 22px;
    right: -22px;
}

.floating-panel.bottom {
    left: -24px;
    bottom: 26px;
}

.story-panel,
.principle-card,
.journey-card,
.workflow-card,
.highlight-card,
.coverage-card,
.audience-card,
.cta-panel {
    border-radius: 28px;
}

.story-panel {
    padding: 34px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid var(--border-soft);
    box-shadow: var(--shadow-soft);
}

.story-metrics {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-top: 28px;
}

.story-metric {
    padding: 22px;
    border-radius: 20px;
}

.story-metric strong {
    display: block;
    font-size: 1.35rem;
    color: var(--brand-navy);
}

.story-metric span {
    color: var(--text-soft);
    font-size: 0.95rem;
}

.principle-card,
.journey-card,
.workflow-card,
.highlight-card,
.coverage-card,
.audience-card {
    height: 100%;
    padding: 28px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.principle-card:hover,
.journey-card:hover,
.workflow-card:hover,
.highlight-card:hover,
.coverage-card:hover,
.audience-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 24px 46px rgba(15, 23, 42, 0.12);
}

.icon-pill {
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

.journey-card {
    overflow: hidden;
    padding: 0;
}

.journey-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.journey-body {
    padding: 24px 26px 26px;
}

.workflow-card p,
.highlight-card p,
.coverage-card p,
.audience-card p,
.principle-card p,
.journey-body p {
    color: var(--text-soft);
    line-height: 1.75;
    margin-bottom: 0;
}

.workflow-list {
    display: grid;
    gap: 14px;
    margin-top: 20px;
}

.workflow-item {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(15, 108, 189, 0.05);
    border: 1px solid rgba(15, 108, 189, 0.08);
}

.workflow-item span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #fff;
    color: var(--brand-blue);
    font-weight: 800;
    flex-shrink: 0;
}

.workflow-item strong {
    display: block;
    color: var(--brand-navy);
    margin-bottom: 4px;
}

.coverage-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.coverage-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.coverage-card h5,
.audience-card h5,
.principle-card h5,
.journey-body h5,
.workflow-card h5,
.highlight-card h5 {
    color: var(--brand-navy);
    margin-bottom: 12px;
}

.coverage-card ul,
.audience-card ul {
    list-style: none;
    padding: 0;
    margin: 18px 0 0;
    display: grid;
    gap: 10px;
}

.coverage-card li,
.audience-card li {
    color: var(--text-soft);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.coverage-card li i,
.audience-card li i {
    color: var(--brand-blue);
    margin-top: 4px;
}

.audience-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.cta-section {
    background: linear-gradient(135deg, #08111f 0%, #0f2740 48%, #0f6cbd 100%);
}

.cta-panel {
    padding: 42px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.04));
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: none;
}

.cta-panel h2,
.cta-panel p,
.cta-checks {
    color: #ffffff;
}

.cta-checks {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 20px;
    margin-top: 18px;
    font-size: 0.95rem;
}

.cta-checks span {
    color: rgba(226, 232, 240, 0.92);
}

@media (max-width: 991.98px) {
    .about-hero {
        padding: 92px 0 72px;
    }

    .hero-stat-strip,
    .story-metrics,
    .coverage-grid,
    .audience-grid {
        grid-template-columns: 1fr;
    }

    .floating-panel.top,
    .floating-panel.bottom {
        position: static;
        margin-top: 16px;
    }

    .hero-frame img {
        min-height: 340px;
    }
}

@media (max-width: 767.98px) {
    .section-padding {
        padding: 72px 0;
    }

    .hero-title {
        font-size: 2.4rem;
    }

    .hero-shell,
    .story-panel,
    .principle-card,
    .journey-card,
    .workflow-card,
    .highlight-card,
    .coverage-card,
    .audience-card,
    .cta-panel {
        border-radius: 22px;
    }

    .story-panel,
    .principle-card,
    .workflow-card,
    .highlight-card,
    .coverage-card,
    .audience-card {
        padding: 24px;
    }

    .cta-panel {
        padding: 30px 24px;
    }
}
</style>

@section('title', 'About MeriAccounting | Smart ERP for Finance & Compliance Teams')

@section('content')
@php
    $heroStats = [
        ['value' => '1 Platform', 'label' => 'Finance, GST and operations in sync'],
        ['value' => 'Clear Mission', 'label' => 'Built to simplify business control'],
        ['value' => '24/7', 'label' => 'Cloud access for modern teams'],
    ];

    $values = [
        [
            'icon' => 'bi-shield-check',
            'title' => 'Integrity',
            'copy' => 'We believe financial systems should build trust through clean records, transparent workflows and accountable access.',
        ],
        [
            'icon' => 'bi-lightning-charge',
            'title' => 'Clarity',
            'copy' => 'We turn scattered processes into structured workflows so teams can understand what is happening and what needs action.',
        ],
        [
            'icon' => 'bi-graph-up-arrow',
            'title' => 'Efficiency',
            'copy' => 'We design the platform to reduce manual follow-ups, duplicate work and reporting friction across departments.',
        ],
        [
            'icon' => 'bi-people',
            'title' => 'Business-first thinking',
            'copy' => 'Every feature is shaped around the practical needs of growing teams, not just technical complexity.',
        ],
    ];

    $storyCards = [
        [
            'title' => 'Why MeriAccounting exists',
            'copy' => 'Many businesses still manage accounting, GST, collections and reporting through disconnected tools. MeriAccounting was created to bring those workflows into one dependable operating system.',
            'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'What the platform stands for',
            'copy' => 'The goal is simple: give businesses cleaner data, sharper visibility and more control over daily financial operations without making the software feel heavy or confusing.',
            'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'title' => 'How it helps teams grow',
            'copy' => 'By connecting accounting, tax and operational records, MeriAccounting helps founders, managers and finance teams make decisions from one trusted source of truth.',
            'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=1200&auto=format&fit=crop',
        ],
    ];

    $aboutPillars = [
        [
            'title' => 'Mission',
            'copy' => 'To simplify accounting, GST and operational control through one connected ERP experience that helps businesses work with more confidence.',
            'icon' => 'bi-bullseye',
        ],
        [
            'title' => 'Vision',
            'copy' => 'To become the platform businesses rely on when they want finance, compliance and workflow visibility to operate as one system.',
            'icon' => 'bi-eye',
        ],
        [
            'title' => 'Approach',
            'copy' => 'We focus on practical workflow design, business clarity and clean user experiences so powerful processes remain easy to use.',
            'icon' => 'bi-diagram-3',
        ],
    ];

    $trustPoints = [
        [
            'icon' => 'bi-journal-check',
            'title' => 'Structured accounting foundations',
            'points' => ['Cleaner invoicing and ledgers', 'More reliable financial records', 'Sharper daily bookkeeping discipline'],
        ],
        [
            'icon' => 'bi-file-earmark-check',
            'title' => 'Compliance-friendly operations',
            'points' => ['GST-focused workflows', 'Clearer reporting follow-through', 'Better visibility into recurring compliance work'],
        ],
        [
            'icon' => 'bi-speedometer2',
            'title' => 'Decision-ready dashboards',
            'points' => ['Live business summaries', 'Faster review of collections and activity', 'One place to monitor financial operations'],
        ],
        [
            'icon' => 'bi-shield-lock',
            'title' => 'Control and accountability',
            'points' => ['Role-based access support', 'Audit-friendly process visibility', 'More dependable internal coordination'],
        ],
    ];

    $audiences = [
        [
            'icon' => 'bi-buildings',
            'title' => 'Growing businesses',
            'points' => ['Need a more professional operating setup', 'Want to move away from spreadsheet-heavy processes', 'Need cleaner visibility across functions'],
        ],
        [
            'icon' => 'bi-calculator',
            'title' => 'Finance and accounts teams',
            'points' => ['Need cleaner books and ledgers', 'Want stronger process control', 'Need better reporting confidence'],
        ],
        [
            'icon' => 'bi-file-check',
            'title' => 'Compliance-led teams',
            'points' => ['Need GST-aligned workflows', 'Want clearer exception visibility', 'Need more reliable monthly execution'],
        ],
    ];

    $highlights = [
        [
            'icon' => 'bi-stars',
            'title' => 'Designed to feel professional',
            'copy' => 'MeriAccounting is intentionally presented as a product platform, giving businesses a stronger sense of trust, structure and readiness.',
        ],
        [
            'icon' => 'bi-cloud-check',
            'title' => 'Built for the way teams work today',
            'copy' => 'Cloud access and connected workflows help teams stay aligned across office operations, remote work and growing business complexity.',
        ],
    ];
@endphp

<section class="about-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge mb-4">
                    <i class="bi bi-buildings-fill"></i>
                    About MeriAccounting
                </span>

                <h1 class="hero-title mb-4">
                    Built to help businesses manage finance and compliance with
                    <span class="text-gradient">more clarity, control and confidence</span>
                </h1>

                <p class="hero-copy mb-4">
                    MeriAccounting is a business ERP platform created for teams that want accounting,
                    GST and operational workflows to feel connected, professional and easier to manage every day.
                </p>

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
                <div class="hero-shell">
                    <div class="hero-frame">
                        <img
                            src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=1400&auto=format&fit=crop"
                            alt="Finance professionals reviewing analytics"
                            loading="lazy"
                        >
                    </div>

                    <div class="floating-panel top">
                        <small>About the platform</small>
                        <strong>Business software shaped around clarity</strong>
                    </div>

                    <div class="floating-panel bottom">
                        <small>Core idea</small>
                        <strong>One system for finance-led operations</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="section-eyebrow mb-3">Who We Are</span>
                <h2 class="section-title mb-3">MeriAccounting is more than a feature list, it is a product built around better business discipline</h2>
                <p class="section-copy mb-4">
                    We see MeriAccounting as a platform for businesses that are ready to move from fragmented,
                    reactive processes to a more organized and dependable operating model.
                </p>
                <p class="section-copy mb-0">
                    The focus is not only on completing accounting or GST tasks, but on helping teams build habits of
                    visibility, control, cleaner collaboration and more confident decision-making across the business.
                </p>

                <div class="story-metrics">
                    <div class="story-metric">
                        <strong>Product-led</strong>
                        <span>A platform mindset instead of a generic company brochure</span>
                    </div>
                    <div class="story-metric">
                        <strong>Process-driven</strong>
                        <span>Focused on cleaner workflows and stronger follow-through</span>
                    </div>
                    <div class="story-metric">
                        <strong>Business-ready</strong>
                        <span>Made for teams that need confidence in day-to-day operations</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="story-panel">
                    <span class="section-eyebrow mb-3">What We Believe</span>
                    <h5 class="mb-3" style="color: var(--brand-navy);">Good business software should make control feel simpler, not heavier</h5>
                    <p class="section-copy mb-0">
                        That belief shapes how MeriAccounting is presented, structured and continuously improved:
                        practical where teams need speed, and disciplined where businesses need accountability.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Mission & Vision</span>
            <h2 class="section-title mb-3">The direction behind the platform</h2>
            <p class="section-copy col-lg-8 mx-auto">
                A strong About page should explain not just what the product does, but why it exists and what it is trying to improve for businesses.
            </p>
        </div>

        <div class="row g-4">
            @foreach ($aboutPillars as $principle)
                <div class="col-md-6 col-xl-4">
                    <div class="principle-card">
                        <span class="icon-pill">
                            <i class="bi {{ $principle['icon'] }}"></i>
                        </span>
                        <h5>{{ $principle['title'] }}</h5>
                        <p>{{ $principle['copy'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Our Story</span>
            <h2 class="section-title mb-3">How MeriAccounting positions itself in the market</h2>
            <p class="section-copy col-lg-8 mx-auto">
                These blocks explain the thinking behind the platform and make the page feel like an actual About experience, not a second homepage.
            </p>
        </div>

        <div class="row g-4">
            @foreach ($storyCards as $card)
                <div class="col-md-6 col-xl-4">
                    <div class="journey-card">
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy">
                        <div class="journey-body">
                            <h5>{{ $card['title'] }}</h5>
                            <p>{{ $card['copy'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding" style="background: linear-gradient(135deg, #08111f 0%, #0f2740 54%, #0f6cbd 100%);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-eyebrow mb-3" style="background: rgba(255,255,255,0.12); color: #e0f2fe;">What Makes It Different</span>
                <h2 class="section-title mb-3" style="color: #ffffff;">The platform is designed around how teams actually operate</h2>
                <p class="mb-0" style="color: rgba(226, 232, 240, 0.9); line-height: 1.8;">
                    MeriAccounting is not presented as abstract software. It is built around the real expectations businesses have from an accounting and compliance platform.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="workflow-card" style="background: rgba(255,255,255,0.96);">
                    <span class="icon-pill">
                        <i class="bi bi-layers"></i>
                    </span>
                    <h5>What businesses expect from a serious ERP</h5>

                    <div class="workflow-list">
                        @foreach ($trustPoints as $index => $step)
                            <div class="workflow-item">
                                <span>{{ $index + 1 }}</span>
                                <div>
                                    <strong>{{ $step['title'] }}</strong>
                                    <div class="section-copy" style="font-size: 0.95rem; margin-bottom: 0;">{{ implode(', ', $step['points']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4 mb-4">
            @foreach ($highlights as $highlight)
                <div class="col-md-6">
                    <div class="highlight-card">
                        <span class="icon-pill">
                            <i class="bi {{ $highlight['icon'] }}"></i>
                        </span>
                        <h5>{{ $highlight['title'] }}</h5>
                        <p>{{ $highlight['copy'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Core Values</span>
            <h2 class="section-title mb-3">The principles that shape how MeriAccounting is presented and built</h2>
            <p class="section-copy col-lg-8 mx-auto">
                These values help the About page feel more human, more credible and more aligned with what users expect from a product company.
            </p>
        </div>

        <div class="coverage-grid">
            @foreach ($values as $card)
                <div class="coverage-card">
                    <span class="icon-pill">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </span>
                    <h5>{{ $card['title'] }}</h5>
                    <p>{{ $card['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding" style="background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Built For</span>
            <h2 class="section-title mb-3">Who this platform is designed to support</h2>
            <p class="section-copy col-lg-8 mx-auto">
                A strong About page should also make it clear who benefits most from the product and why it matters to them.
            </p>
        </div>

        <div class="audience-grid">
            @foreach ($audiences as $audience)
                <div class="audience-card">
                    <span class="icon-pill">
                        <i class="bi {{ $audience['icon'] }}"></i>
                    </span>
                    <h5>{{ $audience['title'] }}</h5>
                    <ul>
                        @foreach ($audience['points'] as $point)
                            <li>
                                <i class="bi bi-dot"></i>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-padding cta-section">
    <div class="container">
        <div class="cta-panel">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="section-eyebrow mb-3" style="background: rgba(255,255,255,0.12); color: #e0f2fe;">
                        Ready to learn more
                    </span>
                    <h2 class="section-title mb-3" style="color: #ffffff;">
                        See how MeriAccounting can support a more organized and professional way of working
                    </h2>
                    <p class="mb-0" style="color: rgba(226, 232, 240, 0.9); line-height: 1.8;">
                        If your business wants accounting, GST and financial operations to feel more connected, more visible and easier to manage, MeriAccounting is built for that shift.
                    </p>
                    <div class="cta-checks">
                        <span><i class="bi bi-check2-circle me-2"></i>Mission-driven product story</span>
                        <span><i class="bi bi-check2-circle me-2"></i>About-page structure users expect</span>
                        <span><i class="bi bi-check2-circle me-2"></i>UI aligned with the main dashboard</span>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('ContactUs') }}" class="btn btn-light btn-lg rounded-pill px-4">
                        Request Demo
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
