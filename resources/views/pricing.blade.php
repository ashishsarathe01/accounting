@extends('layouts.landingapp')

@section('title', 'MeriAccounting Pricing | TechBridge Software')

<style>
    :root {
        --pricing-navy: #0f172a;
        --pricing-blue: #0f6cbd;
        --pricing-cyan: #3ab7bf;
        --pricing-soft: #eef6ff;
        --pricing-surface: #ffffff;
        --pricing-surface-muted: #f8fbff;
        --pricing-text: #18324b;
        --pricing-text-soft: #5f7186;
        --pricing-border: rgba(15, 23, 42, 0.09);
        --pricing-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        --pricing-shadow-strong: 0 24px 60px rgba(15, 23, 42, 0.16);
    }

    .pricing-hero {
        position: relative;
        overflow: hidden;
        padding: 110px 0 88px;
        background:
            radial-gradient(circle at top left, rgba(58, 183, 191, 0.28), transparent 34%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.22), transparent 34%),
            linear-gradient(135deg, #07111f 0%, #0f2740 54%, #0f6cbd 100%);
    }

    .pricing-hero::before,
    .pricing-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        opacity: 0.95;
    }

    .pricing-hero::before {
        width: 380px;
        height: 380px;
        top: -140px;
        right: -90px;
        background: rgba(255, 255, 255, 0.08);
    }

    .pricing-hero::after {
        width: 320px;
        height: 320px;
        bottom: -120px;
        left: -100px;
        background: rgba(58, 183, 191, 0.18);
    }

    .pricing-hero .container {
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
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .hero-title {
        font-size: clamp(2.6rem, 4.6vw, 4.8rem);
        line-height: 1.04;
        font-weight: 800;
        color: #ffffff;
        margin: 18px 0 0;
    }

    .hero-title .text-gradient {
        background: linear-gradient(90deg, #a5f3fc 0%, #ffffff 55%, #bfdbfe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-copy {
        max-width: 660px;
        color: rgba(226, 232, 240, 0.92);
        font-size: 1.06rem;
        line-height: 1.85;
        margin: 18px auto 0;
    }

    .hero-actions .btn {
        border-radius: 14px;
        padding: 14px 22px;
        font-weight: 800;
    }

    .hero-actions .btn-light {
        color: var(--pricing-navy);
    }

    .section-padding {
        padding: 92px 0;
    }

    .section-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(15, 108, 189, 0.1);
        color: var(--pricing-blue);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

	    .section-title {
	        font-size: clamp(2rem, 3vw, 3.2rem);
	        font-weight: 900;
	        line-height: 1.12;
	        color: var(--pricing-navy);
	    }

	    .section-copy {
	        font-size: 1.02rem;
	        line-height: 1.8;
	        color: var(--pricing-text-soft);
	    }

	    .pricing-section {
	        position: relative;
	        overflow: hidden;
	    }

	    .pricing-section::before,
	    .pricing-section::after {
	        content: "";
	        position: absolute;
	        inset: auto;
	        border-radius: 50%;
	        filter: blur(18px);
	        opacity: 0.55;
	        pointer-events: none;
	    }

	    .pricing-section::before {
	        width: 420px;
	        height: 420px;
	        top: -160px;
	        left: -200px;
	        background: rgba(58, 183, 191, 0.28);
	    }

	    .pricing-section::after {
	        width: 460px;
	        height: 460px;
	        bottom: -200px;
	        right: -220px;
	        background: rgba(15, 108, 189, 0.22);
	    }

	    .pricing-card,
	    .erp-card,
	    .snapshot-card,
	    .showcase-card {
	        border-radius: 18px;
	        background: var(--pricing-surface);
	        border: 1px solid var(--pricing-border);
	        box-shadow: var(--pricing-shadow);
	        transition: transform 0.25s ease, box-shadow 0.25s ease;
	    }

	    .pricing-card:hover,
	    .erp-card:hover,
	    .snapshot-card:hover {
	        transform: translateY(-6px);
	        box-shadow: var(--pricing-shadow-strong);
	    }

	    .pricing-card--featured {
	        border: 2px solid rgba(15, 108, 189, 0.55);
	        background: linear-gradient(180deg, rgba(15, 108, 189, 0.08), #ffffff 55%);
	        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.14);
	    }

	    .plan-tag {
	        display: inline-flex;
	        align-items: center;
	        justify-content: center;
	        gap: 10px;
	        padding: 9px 14px;
	        border-radius: 999px;
	        background: rgba(15, 108, 189, 0.1);
	        color: var(--pricing-blue);
	        font-weight: 900;
	        letter-spacing: 0.02em;
	        font-size: 0.86rem;
	        margin: 0 auto 12px;
	        width: fit-content;
	    }

	    .pricing-card--featured .plan-tag {
	        background: rgba(15, 108, 189, 0.16);
	    }

    .pricing-card .card-body {
        padding: 34px 30px;
    }

    .pricing-card .badge {
        border-radius: 999px;
        padding: 10px 14px;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .pricing-card .plan-price {
        font-size: 2.4rem;
        font-weight: 900;
        color: var(--pricing-navy);
    }

    .pricing-card .plan-price span {
        font-weight: 700;
        color: var(--pricing-text-soft);
    }

	    .pricing-card .plan-list li {
	        display: flex;
	        gap: 10px;
	        align-items: flex-start;
	        padding: 8px 0;
	        color: var(--pricing-text);
	    }

	    .pricing-card .plan-list i {
	        color: var(--pricing-blue);
	        margin-top: 2px;
	    }

	    .snapshot-card {
	        height: 100%;
	        padding: 22px 20px;
	        display: grid;
	        gap: 10px;
	    }

	    .snapshot-icon {
	        width: 46px;
	        height: 46px;
	        border-radius: 16px;
	        display: inline-flex;
	        align-items: center;
	        justify-content: center;
	        background: rgba(15, 108, 189, 0.12);
	        color: var(--pricing-blue);
	        font-size: 1.15rem;
	    }

	    .snapshot-card h5 {
	        margin: 0;
	        font-weight: 900;
	        color: var(--pricing-navy);
	        font-size: 1.06rem;
	    }

	    .snapshot-card p {
	        margin: 0;
	        color: var(--pricing-text-soft);
	        line-height: 1.65;
	        font-size: 0.98rem;
	    }

	    .showcase-card {
	        border-radius: 28px;
	        background: rgba(255, 255, 255, 0.85);
	        border: 1px solid rgba(226, 232, 240, 0.95);
	        box-shadow: var(--pricing-shadow-strong);
	        padding: 18px;
	        position: relative;
	        overflow: hidden;
	    }

	    .showcase-frame {
	        overflow: hidden;
	        border-radius: 22px;
	        background: rgba(255, 255, 255, 0.8);
	    }

	    .showcase-frame img {
	        width: 100%;
	        height: 100%;
	        min-height: 420px;
	        object-fit: cover;
	        display: block;
	    }

	    .showcase-mini {
	        position: absolute;
	        left: 26px;
	        right: 26px;
	        bottom: 26px;
	        padding: 14px 16px;
	        border-radius: 18px;
	        background: rgba(255, 255, 255, 0.92);
	        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.18);
	    }

	    .showcase-mini strong {
	        display: block;
	        font-weight: 900;
	        color: var(--pricing-navy);
	        margin-bottom: 4px;
	    }

	    .showcase-mini span {
	        color: var(--pricing-text-soft);
	        font-size: 0.95rem;
	        line-height: 1.5;
	    }

	    .faq-accordion .accordion-item {
	        border-radius: 18px;
	        border: 1px solid var(--pricing-border);
	        overflow: hidden;
	        box-shadow: var(--pricing-shadow);
	        margin-bottom: 14px;
	        background: var(--pricing-surface);
	    }

	    .faq-accordion .accordion-button {
	        font-weight: 900;
	        color: var(--pricing-navy);
	        background: var(--pricing-surface);
	        padding: 18px 18px;
	    }

	    .faq-accordion .accordion-button:not(.collapsed) {
	        background: rgba(15, 108, 189, 0.08);
	        box-shadow: none;
	    }

	    .faq-accordion .accordion-body {
	        color: var(--pricing-text-soft);
	        line-height: 1.85;
	        padding: 18px 18px 20px;
	    }

    .cta-panel {
        border-radius: 26px;
        padding: 46px 36px;
        background: linear-gradient(135deg, rgba(15, 108, 189, 0.14), rgba(58, 183, 191, 0.12));
        border: 1px solid rgba(15, 108, 189, 0.16);
        box-shadow: var(--pricing-shadow);
    }

    .cta-panel h3 {
        font-weight: 900;
        color: var(--pricing-navy);
        margin-bottom: 10px;
    }

    .cta-panel p {
        color: var(--pricing-text-soft);
        margin-bottom: 0;
        line-height: 1.8;
    }

	    @media (max-width: 575px) {
	        .pricing-card .card-body {
	            padding: 30px 22px;
	        }
	    }
	</style>

	@section('content')

	<section class="pricing-hero text-center">
	    <div class="container">
	        <div class="hero-pill justify-content-center mb-3">
	            <i class="bi bi-lightning-charge-fill"></i>
            Flexible plans for startups, SMEs and enterprises
        </div>
        <h1 class="hero-title">
            Pricing that scales with <span class="text-gradient">your business</span>
        </h1>
        <p class="hero-copy">
            Choose a plan that fits your operations today, then grow into advanced compliance, payroll, inventory and reporting as you expand.
        </p>

	        <div class="hero-actions d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
	            <a href="{{ route('ContactUs') }}" class="btn btn-light">
	                Schedule Free Demo
	            </a>
	            <a href="{{ route('features') }}" class="btn btn-outline-light">
	                View Features
	            </a>
	        </div>
	    </div>
	</section>

	<section class="section-padding bg-light pricing-section">
	    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Plans</span>
            <h2 class="section-title mb-3">Simple monthly pricing</h2>
            <p class="section-copy col-lg-8 mx-auto">
                Start lean, then unlock more modules as your team grows. All plans come with modern UI, clear reporting and a secure foundation.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
	            <div class="col-lg-3 col-md-6">
	                <div class="card pricing-card h-100 text-center">
	                    <div class="card-body">
	                        <div class="d-flex justify-content-center mb-3">
	                            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="58" alt="Starter plan">
	                        </div>
	                        <div class="plan-tag"><i class="bi bi-rocket-takeoff"></i> Best to start</div>
	                        <h4 class="fw-bold mb-1">Starter</h4>
	                        <div class="plan-price my-3">₹600<span class="fs-6">/month</span></div>
                        <ul class="plan-list list-unstyled mt-3 mb-4 text-start">
                            <li><i class="bi bi-check-circle-fill"></i> Smart billing</li>
                            <li><i class="bi bi-check-circle-fill"></i> Reports dashboard</li>
                            <li><i class="bi bi-check-circle-fill"></i> Easy data management</li>
                        </ul>
                        <a href="{{ route('ContactUs') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                            Get Started
                        </a>
                    </div>
                </div>
            </div>

	            <div class="col-lg-3 col-md-6">
	                <div class="card pricing-card pricing-card--featured h-100 text-center position-relative">
	                    <span class="badge bg-primary position-absolute top-0 start-50 translate-middle">
	                        Most Popular
	                    </span>
	                    <div class="card-body">
	                        <div class="d-flex justify-content-center mb-3">
	                            <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" width="58" alt="Business plan">
	                        </div>
	                        <div class="plan-tag"><i class="bi bi-stars"></i> Best value</div>
	                        <h4 class="fw-bold mb-1">Business</h4>
	                        <div class="plan-price my-3">₹1200<span class="fs-6">/month</span></div>
                        <ul class="plan-list list-unstyled mt-3 mb-4 text-start">
                            <li><i class="bi bi-check-circle-fill"></i> GST & billing</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced reports</li>
                            <li><i class="bi bi-check-circle-fill"></i> Multi-user access</li>
                            <li><i class="bi bi-check-circle-fill"></i> Email & WhatsApp support</li>
                        </ul>
                        <a href="{{ route('ContactUs') }}" class="btn btn-primary w-100 fw-bold py-2">
                            Choose Plan
                        </a>
                    </div>
                </div>
            </div>

	            <div class="col-lg-3 col-md-6">
	                <div class="card pricing-card h-100 text-center">
	                    <div class="card-body">
	                        <div class="d-flex justify-content-center mb-3">
	                            <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" width="58" alt="Pro plan">
	                        </div>
	                        <div class="plan-tag"><i class="bi bi-graph-up-arrow"></i> For growth</div>
	                        <h4 class="fw-bold mb-1">Pro</h4>
	                        <div class="plan-price my-3">₹1,999<span class="fs-6">/month</span></div>
                        <ul class="plan-list list-unstyled mt-3 mb-4 text-start">
                            <li><i class="bi bi-check-circle-fill"></i> Inventory + accounting</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced analytics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Multi-user collaboration</li>
                            <li><i class="bi bi-check-circle-fill"></i> Priority support</li>
                        </ul>
                        <a href="{{ route('ContactUs') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                            Upgrade
                        </a>
                    </div>
                </div>
            </div>

	            <div class="col-lg-3 col-md-6">
	                <div class="card pricing-card h-100 text-center">
	                    <div class="card-body">
	                        <div class="d-flex justify-content-center mb-3">
	                            <img src="https://cdn-icons-png.flaticon.com/512/3063/3063825.png" width="58" alt="Enterprise plan">
	                        </div>
	                        <div class="plan-tag"><i class="bi bi-buildings"></i> For scale</div>
	                        <h4 class="fw-bold mb-1">Enterprise</h4>
	                        <div class="plan-price my-3">₹2,999+</div>
                        <ul class="plan-list list-unstyled mt-3 mb-4 text-start">
                            <li><i class="bi bi-check-circle-fill"></i> Full business suite</li>
                            <li><i class="bi bi-check-circle-fill"></i> Multi-branch management</li>
                            <li><i class="bi bi-check-circle-fill"></i> API integration</li>
                            <li><i class="bi bi-check-circle-fill"></i> Dedicated support</li>
                        </ul>
                        <a href="{{ route('ContactUs') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                            Contact Us
                        </a>
	        </div>

	        <div class="text-center mt-4">
	            <p class="text-muted small mb-0">
	                Prices shown per month in INR. Need multi-branch rollout, custom modules, or ERP development?
	                <a href="{{ route('ContactUs') }}" class="text-decoration-none fw-semibold">Talk to us</a>.
	            </p>
	        </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-5">
            <div class="text-center mb-5">
                <span class="section-eyebrow mb-3">ERP</span>
                <h2 class="section-title mb-3">Custom ERP development</h2>
                <p class="section-copy col-lg-8 mx-auto">Tailored solutions built for your business workflows.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="erp-card h-100 text-center p-4">
                        <div class="mb-3">
                            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828817.png" width="54" alt="Basic customization">
                        </div>
                        <h5 class="fw-bold">Basic customization</h5>
                        <p class="text-muted small mb-2">Perfect for small process improvements.</p>
                        <h3 class="fw-bold mt-3 text-primary">₹25K – ₹50K</h3>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="erp-card h-100 text-center p-4 border-primary position-relative">
                        <span class="badge bg-primary position-absolute top-0 start-50 translate-middle">
                            Recommended
                        </span>
                        <div class="mb-3">
                            <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" width="54" alt="Medium ERP">
                        </div>
                        <h5 class="fw-bold">Medium ERP</h5>
                        <p class="text-muted small mb-2">Best for growing businesses.</p>
                        <h3 class="fw-bold mt-3 text-primary">₹75K – ₹1.5L</h3>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="erp-card h-100 text-center p-4">
                        <div class="mb-3">
                            <img src="https://cdn-icons-png.flaticon.com/512/3063/3063825.png" width="54" alt="Advanced ERP">
                        </div>
                        <h5 class="fw-bold">Advanced ERP</h5>
                        <p class="text-muted small mb-2">Fully customized enterprise solutions.</p>
                        <h3 class="fw-bold mt-3 text-primary">₹2L – ₹5L+</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-panel text-center mt-5">
            <h3>Not sure which plan fits?</h3>
            <p class="mb-4">Tell us your workflow and we’ll suggest the most cost-effective setup.</p>
            <a href="{{ route('ContactUs') }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold">
                Talk to an expert
            </a>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="section-eyebrow mb-3">Features</span>
                <h2 class="section-title mb-3">A complete platform, not just pricing</h2>
                <p class="section-copy mb-4">
                    Every plan starts with strong foundations and can grow into GST (2A/2B, R-1, 3B), compliance handling, payroll and attendance.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-receipt"></i></div>
                            <h5>Billing + e-Invoicing</h5>
                            <p>Invoices, e-invoice and e-way bill workflows.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-journal-text"></i></div>
                            <h5>Ledgers in real time</h5>
                            <p>Customer & vendor balances updated instantly.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-box-seam"></i></div>
                            <h5>Inventory control</h5>
                            <p>Stock alerts, reorders and product tracking.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-clipboard-check"></i></div>
                            <h5>GST-ready suite</h5>
                            <p>2A/2B matching, R-1 filing and 3B outputs.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-calendar-check"></i></div>
                            <h5>Payroll + attendance</h5>
                            <p>Payroll management with attendance inputs.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="snapshot-card">
                            <div class="snapshot-icon"><i class="bi bi-person-lock"></i></div>
                            <h5>Controls + audit trail</h5>
                            <p>Role-based access and edit logs for security.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a href="{{ route('pricing') }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold">
                        View all features
                    </a>
                    <a href="{{ route('ContactUs') }}" class="btn btn-outline-primary btn-lg rounded-pill px-5 fw-bold">
                        Get a demo
                    </a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="showcase-card">
                    <div class="showcase-frame">
                        <img
                            src="https://images.unsplash.com/photo-1554224154-26032ffc0d07?q=80&w=1400&auto=format&fit=crop"
                            alt="Pricing and product highlights"
                            loading="lazy"
                        >
                    </div>
                    <div class="showcase-mini">
                        <strong>GST-ready workflows</strong>
                        <span>Matching, filing and compliance visibility with clear month-end reporting.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">FAQ</span>
            <h2 class="section-title mb-3">Common questions</h2>
            <p class="section-copy col-lg-8 mx-auto">
                Here are quick answers to the questions we get most often when teams evaluate MeriAccounting.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion faq-accordion" id="pricingFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqOneHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqOne" aria-expanded="false" aria-controls="faqOne">
                                Can I upgrade my plan later?
                            </button>
                        </h2>
                        <div id="faqOne" class="accordion-collapse collapse" aria-labelledby="faqOneHeading" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Yes. You can start with the plan that fits your current workflow and upgrade as your team size and module requirements grow.
                                If you’re unsure, we can recommend the best plan after a quick call.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqTwoHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
                                Do you offer a demo before purchase?
                            </button>
                        </h2>
                        <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqTwoHeading" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Yes. We offer a guided demo so you can see billing, ledgers, GST workflows (2A/2B, R-1, 3B),
                                and any modules you’re evaluating—end to end.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqThreeHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                How does pricing work for multiple users or branches?
                            </button>
                        </h2>
                        <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqThreeHeading" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Multi-user and multi-branch setups depend on your workflow and access requirements.
                                Share your expected users/branches and we’ll recommend the most cost-effective plan.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqFourHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                                Can you build custom ERP modules?
                            </button>
                        </h2>
                        <div id="faqFour" class="accordion-collapse collapse" aria-labelledby="faqFourHeading" data-bs-parent="#pricingFaq">
                            <div class="accordion-body">
                                Yes. If you need custom workflows, integrations or additional modules, we can build and deploy tailored ERP features based on your process.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('ContactUs') }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold">
                        Ask a question
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
