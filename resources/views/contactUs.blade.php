@extends('layouts.landingapp')

@section('title', 'Contact | TechBridge Software')

<style>
    :root {
        --contact-navy: #0f172a;
        --contact-blue: #0f6cbd;
        --contact-cyan: #3ab7bf;
        --contact-soft: #eef6ff;
        --contact-surface: #ffffff;
        --contact-text: #18324b;
        --contact-text-soft: #5f7186;
        --contact-border: rgba(15, 23, 42, 0.1);
        --contact-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        --contact-shadow-strong: 0 24px 60px rgba(15, 23, 42, 0.16);
    }

    .contact-hero {
        position: relative;
        overflow: hidden;
        padding: 108px 0 88px;
        background:
            radial-gradient(circle at top left, rgba(58, 183, 191, 0.26), transparent 34%),
            radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.22), transparent 34%),
            linear-gradient(135deg, #07111f 0%, #0f2740 54%, #0f6cbd 100%);
    }

    .contact-hero::before,
    .contact-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        opacity: 0.95;
    }

    .contact-hero::before {
        width: 380px;
        height: 380px;
        top: -140px;
        right: -90px;
        background: rgba(255, 255, 255, 0.08);
    }

    .contact-hero::after {
        width: 320px;
        height: 320px;
        bottom: -120px;
        left: -110px;
        background: rgba(58, 183, 191, 0.18);
    }

    .contact-hero .container {
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
        max-width: 620px;
    }

    .hero-actions .btn {
        border-radius: 14px;
        padding: 14px 22px;
        font-weight: 800;
    }

    .hero-actions .btn-light {
        color: var(--contact-navy);
    }

    .section-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(15, 108, 189, 0.1);
        color: var(--contact-blue);
        font-size: 0.82rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .section-title {
        font-size: clamp(2rem, 3vw, 3.2rem);
        font-weight: 900;
        line-height: 1.12;
        color: var(--contact-navy);
    }

    .section-copy {
        font-size: 1.02rem;
        line-height: 1.8;
        color: var(--contact-text-soft);
    }

    .hero-stat-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 28px;
        max-width: 640px;
    }

    .hero-stat {
        padding: 16px 16px 14px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.09);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(12px);
    }

    .hero-stat strong {
        display: block;
        color: #ffffff;
        font-size: 1.15rem;
        font-weight: 900;
    }

    .hero-stat span {
        display: block;
        color: rgba(226, 232, 240, 0.88);
        font-size: 0.92rem;
        margin-top: 4px;
        line-height: 1.35;
    }

    .contact-showcase {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 28px 70px rgba(2, 6, 23, 0.42);
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(14px);
        padding: 18px;
        height: 100%;
    }

    .contact-showcase__frame {
        overflow: hidden;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.04);
    }

    .contact-showcase img {
        width: 100%;
        height: 100%;
        min-height: 380px;
        object-fit: cover;
        display: block;
    }

    .contact-mini {
        position: absolute;
        bottom: 24px;
        left: 24px;
        right: 24px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
    }

    .contact-mini strong {
        display: block;
        color: var(--contact-navy);
        font-weight: 900;
        margin-bottom: 4px;
    }

    .contact-mini span {
        color: var(--contact-text-soft);
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .contact-card {
        border-radius: 22px;
        background: var(--contact-surface);
        border: 1px solid var(--contact-border);
        box-shadow: var(--contact-shadow);
    }

    .contact-card--dark {
        background: linear-gradient(155deg, #0b1220 0%, #0f2740 55%, rgba(15, 108, 189, 0.95) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: rgba(226, 232, 240, 0.94);
        box-shadow: var(--contact-shadow-strong);
    }

    .contact-card h3,
    .contact-card h4 {
        color: var(--contact-navy);
        font-weight: 900;
    }

    .contact-card--dark h4,
    .contact-card--dark h6 {
        color: #ffffff;
        font-weight: 900;
    }

    .form-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(15, 108, 189, 0.12);
        color: var(--contact-blue);
        font-weight: 900;
        font-size: 0.86rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .contact-methods {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .method-card {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 14px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: rgba(226, 232, 240, 0.92);
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .method-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 34px rgba(2, 6, 23, 0.22);
        color: #ffffff;
    }

    .method-icon {
        width: 44px;
        height: 44px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        flex-shrink: 0;
    }

    .method-card strong {
        display: block;
        font-weight: 900;
        margin-bottom: 2px;
    }

    .method-card span {
        display: block;
        font-size: 0.93rem;
        line-height: 1.45;
        opacity: 0.9;
    }

    .helper-card {
        border-radius: 18px;
        background: var(--contact-surface);
        border: 1px solid var(--contact-border);
        box-shadow: var(--contact-shadow);
        height: 100%;
        padding: 26px 22px;
        text-align: left;
    }

    .helper-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 108, 189, 0.1);
        color: var(--contact-blue);
        margin-bottom: 14px;
        font-size: 1.35rem;
    }

    .helper-card h5 {
        font-weight: 900;
        color: var(--contact-navy);
        margin-bottom: 8px;
    }

    .helper-card p {
        color: var(--contact-text-soft);
        margin-bottom: 0;
        line-height: 1.7;
    }

    @media (max-width: 991.98px) {
        .hero-stat-strip {
            grid-template-columns: 1fr;
        }
    }
</style>

@section('content')
@php
    $primaryEmail = 'techbridgepartnersindiapvtltd@gmail.com';
    $primaryPhone = '+91-7404661205';
    $whatsAppLink = 'https://wa.me/917404661205';
@endphp

<section class="contact-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-pill mb-3">
                    <i class="bi bi-chat-dots-fill"></i>
                    Let’s talk about your workflow
                </div>
                <h1 class="hero-title">
                    Contact <span class="text-gradient">TechBridge Software</span>
                </h1>
                <p class="hero-copy">
                    Whether you’re evaluating MeriAccounting for billing, GST compliance (2A/2B, R-1, 3B),
                    inventory, payroll or reports—share your requirement and we’ll guide you to the right plan.
                </p>

                <div class="hero-actions d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a href="#contact-form" class="btn btn-light fw-bold">
                        Send a message
                    </a>
                    <a href="{{ $whatsAppLink }}" target="_blank" rel="noopener" class="btn btn-outline-light fw-bold">
                        WhatsApp us
                    </a>
                </div>

                <div class="hero-stat-strip">
                    <div class="hero-stat">
                        <strong>Fast response</strong>
                        <span>We reply as soon as possible during working hours.</span>
                    </div>
                    <div class="hero-stat">
                        <strong>India-first GST</strong>
                        <span>Built around the monthly GST cycle & matching.</span>
                    </div>
                    <div class="hero-stat">
                        <strong>Multi-location</strong>
                        <span>Delhi • Mumbai • Bangalore + global network.</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="contact-showcase">
                    <div class="contact-showcase__frame">
                        <img
                            src="https://images.unsplash.com/photo-1556761175-129418cb2dfe?q=80&w=1400&auto=format&fit=crop"
                            alt="Support team collaborating"
                            loading="lazy"
                        >
                    </div>
                    <div class="contact-mini">
                        <strong>MeriAccounting ERP platform</strong>
                        <span>Accounting, GST, compliance, payroll and operations in one secure system.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light" id="contact-form">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-eyebrow mb-3">Get in touch</span>
            <h2 class="section-title mb-3">Tell us what you need</h2>
            <p class="section-copy col-lg-9 mx-auto">
                Fill the form and we’ll come back with the best next step—demo, pricing recommendation, or a quick call.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="contact-card">
                    <div class="p-4 p-md-5">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                            <div>
                                <h3 class="mb-1">Send us a message</h3>
                                <p class="text-muted mb-0">We’ll respond as soon as possible.</p>
                            </div>
                            <span class="form-badge">
                                <i class="bi bi-shield-lock-fill"></i>
                                Secure form
                            </span>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
                                <i class="bi bi-check-circle-fill mt-1"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                                <div>Please check the highlighted fields and try again.</div>
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST" class="mt-3">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full name</label>
                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        name="name"
                                        value="{{ old('name') }}"
                                        placeholder="Enter your name"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email address</label>
                                    <input
                                        type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="name@company.com"
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile number</label>
                                    <input
                                        type="tel"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        placeholder="e.g. +91 98765 43210"
                                        required
                                    >
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Topic</label>
                                    <select class="form-select" name="topic">
                                        <option value="" {{ old('topic') === '' ? 'selected' : '' }}>Select a topic (optional)</option>
                                        <option value="pricing" {{ old('topic') === 'pricing' ? 'selected' : '' }}>Pricing & plans</option>
                                        <option value="demo" {{ old('topic') === 'demo' ? 'selected' : '' }}>Request a demo</option>
                                        <option value="gst" {{ old('topic') === 'gst' ? 'selected' : '' }}>GST (2A/2B, R-1, 3B)</option>
                                        <option value="payroll" {{ old('topic') === 'payroll' ? 'selected' : '' }}>Payroll & attendance</option>
                                        <option value="inventory" {{ old('topic') === 'inventory' ? 'selected' : '' }}>Inventory & operations</option>
                                        <option value="other" {{ old('topic') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Your message</label>
                                    <textarea
                                        name="message"
                                        class="form-control @error('message') is-invalid @enderror"
                                        rows="5"
                                        placeholder="Share your requirement, company size, and the modules you want..."
                                        required
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mt-4">
                                Send message
                            </button>

                            <p class="small text-muted mt-3 mb-0">
                                By submitting this form, you agree that we may contact you back at the details provided.
                            </p>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="contact-card contact-card--dark h-100">
                    <div class="p-4 p-md-5 d-flex flex-column h-100">
                        <div>
                            <h4 class="mb-2">Direct contact</h4>
                            <p class="mb-0" style="color: rgba(226, 232, 240, 0.86); line-height: 1.7;">
                                Prefer a faster channel? Use WhatsApp, call us, or drop an email.
                            </p>

                            <div class="contact-methods">
                                <a class="method-card" href="{{ $whatsAppLink }}" target="_blank" rel="noopener">
                                    <span class="method-icon"><i class="bi bi-whatsapp"></i></span>
                                    <div>
                                        <strong>WhatsApp</strong>
                                        <span>Chat with our team on WhatsApp</span>
                                    </div>
                                </a>

                                <a class="method-card" href="tel:+917015753354">
                                    <span class="method-icon"><i class="bi bi-telephone-fill"></i></span>
                                    <div>
                                        <strong>{{ $primaryPhone }}</strong>
                                        <span>Call us for quick questions</span>
                                    </div>
                                </a>

                                <a class="method-card" href="mailto:{{ $primaryEmail }}">
                                    <span class="method-icon"><i class="bi bi-envelope-fill"></i></span>
                                    <div>
                                        <strong>{{ $primaryEmail }}</strong>
                                        <span>Email for detailed requirements</span>
                                    </div>
                                </a>
                            </div>

                            <hr class="border-light my-4">

                            <h6 class="mb-3">Locations & coverage</h6>
                            <p class="small mb-3" style="color: rgba(226, 232, 240, 0.86); line-height: 1.7;">
                                India (Delhi, Mumbai, Bangalore) • Australia • Ireland • UK • EU and more.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark fw-semibold px-3 py-2 rounded-pill">GST compliance</span>
                                <span class="badge bg-light text-dark fw-semibold px-3 py-2 rounded-pill">Payroll</span>
                                <span class="badge bg-light text-dark fw-semibold px-3 py-2 rounded-pill">Inventory</span>
                                <span class="badge bg-light text-dark fw-semibold px-3 py-2 rounded-pill">Reporting</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <h6 class="mb-3">Follow us</h6>
                            <div class="d-flex align-items-center gap-3">
                                <a href="https://www.instagram.com/crestfin_tipsers?utm_source=qr&igsh=YW12d2gwa3dvbTMw" class="text-white fs-5" aria-label="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <a href="https://www.linkedin.com/in/ca-ajay-dahiya-64b599123?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=ios_app" class="text-white fs-5" aria-label="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                                <a href="{{ $whatsAppLink }}" target="_blank" rel="noopener" class="text-white fs-5" aria-label="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="helper-card">
                    <div class="helper-icon"><i class="bi bi-clock-history"></i></div>
                    <h5>Business hours</h5>
                    <p>Mon–Fri: 9:00 AM – 6:00 PM<br>Sat: 10:00 AM – 2:00 PM</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="helper-card">
                    <div class="helper-icon"><i class="bi bi-headset"></i></div>
                    <h5>Support-first approach</h5>
                    <p>We help you pick the right modules and rollout plan based on your compliance cycle and team size.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="helper-card">
                    <div class="helper-icon"><i class="bi bi-calendar-check"></i></div>
                    <h5>Schedule a demo</h5>
                    <p>Prefer a guided walkthrough? Book a demo and see the GST and payroll workflows end to end.</p>
                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary rounded-pill mt-3 fw-bold px-4">
                        View pricing
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
