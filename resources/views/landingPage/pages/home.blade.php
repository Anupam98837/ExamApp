@extends('landingPage.layout.structure')
 
@section('title', 'Welcome to Horizon Allienz - Premier Exam Portal')
 
@push('styles')
<link rel="stylesheet" href="{{ asset('css/common/welcome.css') }}">
@endpush
 
 
@section('content')
    {{-- Top nav, main body, and footer are now clean partials --}}
    {{-- resources/views/landingPage/partials/body.blade.php --}}
{{-- HERO ---------------------------------------------------------------- --}}
<section class="hero-section d-flex align-items-center">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="hero-text-content fade-in-up">
                    <div class="hero-badge mb-3">
                        <i class="fas fa-star me-2"></i>
                        India's #1 Exam Portal
                    </div>
                    <h1 class="hero-title float-up">Master Your Dreams with <span class="text-gradient">Horizon Allienz</span></h1>
                    <p class="hero-subtitle">The ultimate destination for competitive exam preparation. Join thousands of successful students who achieved their goals with our comprehensive mock tests and expert guidance.</p>
                    <div class="hero-stats mb-4">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="stat-mini">
                                    <div class="stat-number">50K+</div>
                                    <div class="stat-label">Students</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-mini">
                                    <div class="stat-number">1500+</div>
                                    <div class="stat-label">Tests</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-mini">
                                    <div class="stat-number">95%</div>
                                    <div class="stat-label">Success</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="stat-mini">
                                    <div class="stat-number">24/7</div>
                                    <div class="stat-label">Support</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions d-flex gap-3 flex-wrap">
                        <button class="btn cta-button" onclick="scrollToExams()">
                            <i class="fas fa-rocket me-2"></i>Start Your Journey
                        </button>
                        <button class="btn btn-outline-light px-4 py-3" onclick="scrollToFeatures()">
                            <i class="fas fa-play-circle me-2"></i>Learn More
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="hero-image-container">
                    <div class="hero-image-wrapper">
                        <img src="{{ asset('assets/web_assets/banner.png') }}" alt="Horizon Allienz Logo" class="img-fluid hero-main-image">
                        <div class="floating-card card-1">
                            <i class="fas fa-trophy"></i>
                            <span>Top Ranked</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="fas fa-users"></i>
                            <span>50K+ Students</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="fas fa-chart-line"></i>
                            <span>95% Success</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
   
 
    <!-- Floating icons behind hero -->
    <div class="hero-bg-elements">
        <div class="floating-element element-1"><i class="fas fa-book-open"></i></div>
        <div class="floating-element element-2"><i class="fas fa-graduation-cap"></i></div>
        <div class="floating-element element-3"><i class="fas fa-lightbulb"></i></div>
        <div class="floating-element element-4"><i class="fas fa-medal"></i></div>
        <div class="floating-element element-5"><i class="fas fa-atom"></i></div>
    </div>
</section>
 
{{-- STATS --------------------------------------------------------------- --}}
 
   <section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="stat-counter">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="counter-number" data-count="50000">0</span>
                    <span class="counter-label">Students Enrolled</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-counter">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <span class="counter-number" data-count="1500">0</span>
                    <span class="counter-label">Mock Tests</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-counter">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="counter-number" data-count="95">0</span>
                    <span class="counter-label">Success Rate %</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-counter">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <span class="counter-number" data-count="24">0</span>
                    <span class="counter-label">Expert Faculty</span>
                </div>
            </div>
        </div>
    </div>
</section>
 
 
{{-- EXAM CARDS ---------------------------------------------------------- --}}
<section class="exam-cards-section" id="exams">
    {{-- <div class=""> --}}
        <div class="section-header text-center ">
            <h2 class="cta-title fade-in-up">Available Competitive Exams</h2>
            <p class="cta-subtitle fade-in-up">Choose from our comprehensive collection of mock tests designed by experts</p>
        </div>
 
        {{-- Existing card grid pulled in as before --}}
        @include('modules.exam.viewExam')
 
    {{-- </div> --}}
</section>
 
{{-- FEATURES / WHY CHOOSE US ------------------------------------------ --}}
 
    <!-- Why Choose Us Section -->
{{-- UPDATED WHY CHOOSE US SECTION ------------------------------------------ --}}
<section class="why-choose-section-new" id="features">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title text-white fade-in-up">Why Choose Horizon Allienz?</h2>
            <p class="section-subtitle text-white-50 fade-in-up">Experience the difference with our cutting-edge exam preparation platform</p>
        </div>
       
        <!-- First Feature: Thumbs Up Guy -->
        <div class="feature-row mb-5">
            <div class="row align-items-center">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="feature-content fade-in-left">
                        <div class="feature-icon-badge mb-3">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="feature-title">AI-Powered Learning Experience</h3>
                        <p class="feature-description">
                            Our advanced AI algorithms analyze your learning patterns and provide personalized recommendations to optimize your study path. Get intelligent insights that adapt to your strengths and help you overcome your weaknesses with precision-targeted practice sessions.
                        </p>
                        <div class="feature-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Personalized Study Plans</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Smart Question Recommendations</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Adaptive Learning Technology</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="feature-image-container fade-in-right">
                        <img src="{{ asset('assets/web_assets/thumbsUp.png') }}" alt="Happy Student" class="feature-image">
                        <div class="image-overlay">
                            <div class="overlay-badge">
                                <i class="fas fa-star"></i>
                                <span>95% Success Rate</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Second Feature: Young Guy Giving Exam -->
        <div class="feature-row mb-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="feature-image-container fade-in-left">
                        <img src="{{ asset('assets/web_assets/why_exam.png') }}" alt="Student Taking Exam" class="feature-image">
                        <div class="image-overlay">
                            <div class="overlay-badge">
                                <i class="fas fa-clock"></i>
                                <span>Real-Time Testing</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="feature-content fade-in-right">
                        <div class="feature-icon-badge mb-3">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3 class="feature-title">Realistic Exam Simulation</h3>
                        <p class="feature-description">
                            Experience the actual exam environment with our advanced simulation technology. Our platform replicates the exact interface, timing, and pressure of real competitive exams, ensuring you're fully prepared for the big day.
                        </p>
                        <div class="feature-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Exact Exam Interface</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Timed Practice Sessions</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Instant Result Analysis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Third Feature: Real-Time Evaluation -->
        <div class="feature-row">
            <div class="row align-items-center">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="feature-content fade-in-left">
                        <div class="feature-icon-badge mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Real-Time Evaluation & Progress Tracking</h3>
                        <p class="feature-description">
                            Get instant feedback on your performance with our comprehensive evaluation system. Track your progress with detailed analytics, performance graphs, and milestone achievements that keep you motivated throughout your preparation journey.
                        </p>
                        <div class="feature-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Instant Performance Feedback</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Detailed Analytics Dashboard</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Progress Milestone Tracking</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="feature-image-container fade-in-right">
                        <img src="{{ asset('assets/web_assets/progress2.png') }}" alt="Progress Tracking" class="feature-image">
                        <div class="image-overlay">
                            <div class="overlay-badge">
                                <i class="fas fa-trophy"></i>
                                <span>Track Progress</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
 
{{-- CTA ---------------------------------------------------------------- --}}
<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-wrapper">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="cta-content">
                        <h2 class="cta-title">Ready to Start Your Success Journey?</h2>
                        <p class="cta-subtitle">Join thousands of students who are already achieving their dreams with Horizon Allienz</p>
                        <div class="cta-actions">
                            <button class="btn btn-light btn-lg cta-primary">
                                <i class="fas fa-user-plus me-2"></i>Sign Up Free
                            </button>
                            <button class="btn btn-outline-light btn-lg cta-secondary">
                                <i class="fas fa-phone me-2"></i>Contact Us
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
 
@push('scripts')
<!-- BEGIN corrected script -->
<script>
/* ----------------------------------------------------------
   0.  Initialise all page features once DOM is ready
---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    initializeNavigation();      // ① mobile menu, hide-on-scroll, smooth anchors
    initializeAnimations();      // ② intersection-observer fade-ins
    initializeCounters();        // ③ number counters
    initializeScrollEffects();   // ④ parallax icons
    initializeCTAButton();       // ⑤ navbar CTA text
    initializeRippleEffects();   // ⑥ ripple click effect
});
 
/* ----------------------------------------------------------
   1.  Navigation / Navbar behaviour
---------------------------------------------------------- */
function initializeNavigation () {
    const navCollapseEl = document.getElementById('navbarNav');
    if (!navCollapseEl) return;
 
    /* --- ONE persistent Bootstrap Collapse controller --- */
    const navCollapse = bootstrap.Collapse.getOrCreateInstance(navCollapseEl, { toggle:false });
 
    /* 1-A  close the mobile menu after any nav-link tap */
    document.querySelectorAll('.navbar .nav-link').forEach(link => {
        link.addEventListener('click', () => navCollapse.hide());
    });
 
    /* 1-B  smooth-scroll for in-page anchors */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior:'smooth', block:'start' });
            }
        });
    });
 
    /* 1-C  shrink / slide-up navbar on scroll */
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
        const navbar   = document.querySelector('.navbar');
        const y        = window.pageYOffset;
        const menuOpen = navCollapseEl.classList.contains('show');
 
        navbar.classList.toggle('navbar-scrolled', y > 100);
 
        if (!menuOpen && y > lastScroll && y > 300) {
            navbar.style.transform = 'translateY(-100%)';   // hide
        } else {
            navbar.style.transform = 'translateY(0)';       // show
        }
        lastScroll = y;
    });
}
 
/* ----------------------------------------------------------
   2.  Intersection-observer animations
---------------------------------------------------------- */
function initializeAnimations () {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
 
            const el    = entry.target;
            const delay = el.dataset.delay || 0;
 
            setTimeout(() => el.classList.add('animate-in'), delay);
 
            if (el.classList.contains('stats-section')) animateCounters();
            observer.unobserve(el);
        });
    }, { threshold:0.1, rootMargin:'0px 0px -50px 0px' });
 
    // Updated selector to include new animation classes
    document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .stats-section, .feature-card')
            .forEach(el => observer.observe(el));
}
 
/* ----------------------------------------------------------
   3.  Counter animation
---------------------------------------------------------- */
function initializeCounters () {
    window.animateCounters = function () {
        document.querySelectorAll('.counter-number').forEach(counter => {
            const target    = +counter.dataset.count;
            const duration  = 2000;
            const step      = target / (duration / 16);
            let   current   = 0;
 
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current).toLocaleString();
            }, 16);
        });
    };
}
 
/* ----------------------------------------------------------
   4.  Parallax floating icons
---------------------------------------------------------- */
function initializeScrollEffects () {
    const elems = document.querySelectorAll('.hero-bg-elements .floating-element');
    window.addEventListener('scroll', () => {
        const y = window.pageYOffset * -0.5;
        elems.forEach((el, i) => {
            const speed = (i + 1) * 0.3;
            el.style.transform = `translateY(${y * speed}px) rotate(${window.pageYOffset * 0.1}deg)`;
        });
    });
}
 
/* ----------------------------------------------------------
   5.  CTA button text / href swap
---------------------------------------------------------- */
function initializeCTAButton () {
    const btn   = document.getElementById('ctaButtonNav');
    const label = btn?.querySelector('.cta-text');
    if (!btn || !label) return;
 
    if (sessionStorage.getItem('token')) {
        label.textContent = 'Dashboard';
        btn.href          = '/dashboard';
    } else {
        label.textContent = 'Login';
        btn.href          = '/selectRole';
    }
}
 
/* ----------------------------------------------------------
   6.  Ripple click effect (buttons)
---------------------------------------------------------- */
function initializeRippleEffects () {
    document.querySelectorAll('.btn, .cta-button').forEach(button => {
        button.addEventListener('click', e => {
            const rect   = button.getBoundingClientRect();
            const size   = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
 
            ripple.className = 'ripple';
            ripple.style.width  = ripple.style.height = `${size}px`;
            ripple.style.left   = `${e.clientX - rect.left - size/2}px`;
            ripple.style.top    = `${e.clientY - rect.top  - size/2}px`;
 
            button.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
}
 
/* ----------------------------------------------------------
   7.  Utility helpers
---------------------------------------------------------- */
window.scrollToExams    = () => document.getElementById('exams')   .scrollIntoView({ behavior:'smooth' });
window.scrollToFeatures = () => document.getElementById('features').scrollIntoView({ behavior:'smooth' });
 
/* global error handler */
window.addEventListener('error', e => console.error('JavaScript error:', e.error));
 
/* simple debounce helper (if you want to reuse it elsewhere) */
const debounce = (fn, wait = 16) => {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
};
</script>
<!-- END corrected script -->
@endpush
 
 
@endsection

 