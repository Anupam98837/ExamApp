{{-- resources/views/exam/viewAllExam.blade.php --}}
@extends('landingPage.layout.structure')
 
@section('title', 'Available Exams – Horizon Allienz')
 
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/common/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/exam/viewAllExam.css') }}">
@endpush
 
@section('content')
@php  $assetBase = asset('');  @endphp
 
{{-- ───────── ENHANCED HERO SECTION ───────── --}}
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-pattern"></div>
        <div class="floating-elements">
            <div class="floating-element element-1"></div>
            <div class="floating-element element-2"></div>
            <div class="floating-element element-3"></div>
            <div class="floating-element element-4"></div>
            <div class="floating-element element-5"></div>
        </div>
    </div>
    
    <div class="container">
        <div class="row align-items-center min-vh-75">
 
            {{-- Content Column --}}
            <div class="col-lg-7 col-xl-6">
                <div class="hero-content">
                    <div class="hero-badge-container">
                        <div class="brand-badge">
                            <div class="badge-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <span class="badge-text">Student Exam Portal</span>
                            <div class="badge-pulse"></div>
                        </div>
                    </div>
                    
                    <h1 class="hero-title">
                        <span class="title-line-1">Available</span>
                        <span class="title-line-2">Exams</span>
                        <div class="title-decoration"></div>
                    </h1>
                    
                    <p class="hero-subtitle">
                        Choose from our comprehensive collection of practice exams
                        and boost your academic performance with expert-crafted questions
                    </p>
                    
                    <div class="hero-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span>Secure Testing</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Instant Results</span>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <span>Performance Tracking</span>
                        </div>
                    </div>
                    
                    <div class="hero-cta">
                        <button class="cta-button primary" onclick="document.getElementById('examCatalog').scrollIntoView({behavior: 'smooth'})">
                            <span class="btn-text">Explore Exams</span>
                            <i class="fas fa-arrow-right btn-icon"></i>
                            <div class="btn-ripple"></div>
                        </button>
                        <button class="cta-button secondary" onclick="window.location.href='#examCatalog'">
                            <span class="btn-text">View Catalog</span>
                            <i class="fas fa-book btn-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
 
            {{-- Image & Stats Column --}}
            <div class="col-lg-5 col-xl-6">
                <div class="hero-visual">
                    <div class="hero-image-container">
                        
                        <div class="image-decorations">
                            <div class="decoration-circle circle-1"></div>
                            <div class="decoration-circle circle-2"></div>
                            <div class="decoration-square square-1"></div>
                        </div>
                    </div>
                    
                    {{-- Enhanced Stats Cards --}}
                    <div class="stats-container">
                        <div class="stat-card modern-stat">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="stat-glow"></div>
                            </div>
                            <div class="stat-content">
                                <div id="totalExamsCount" class="stat-number">0</div>
                                <div class="stat-label">Total Exams</div>
                                <div class="stat-trend">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>+12 this month</span>
                                </div>
                            </div>
                        </div>
 
                        <div class="stat-card modern-stat">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div class="stat-glow"></div>
                            </div>
                            <div class="stat-content">
                                <div id="freeExamsCount" class="stat-number">0</div>
                                <div class="stat-label">Free Exams</div>
                                <div class="stat-trend">
                                    <i class="fas fa-heart"></i>
                                    <span>No cost</span>
                                </div>
                            </div>
                        </div>
 
                        <div class="stat-card modern-stat">
                            <div class="stat-icon-wrapper">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <div class="stat-glow"></div>
                            </div>
                            <div class="stat-content">
                                <div id="paidExamsCount" class="stat-number">0</div>
                                <div class="stat-label">Premium</div>
                                <div class="stat-trend">
                                    <i class="fas fa-star"></i>
                                    <span>Advanced</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 
        </div>
    </div>
</section>
 
{{-- ───────── CATALOG WITH INTEGRATED FILTERS ───────── --}}
<section class="exams-section" id="examCatalog">
    <div class="container">
 
        {{-- Integrated Filter Bar --}}
        <div class="filters-card">
            <div class="row g-3 align-items-end">
 
                <div class="col-lg-3 col-md-6">
                    <label class="filter-label">
                        <i class="fas fa-tags me-2"></i>Category
                    </label>
                    <select id="categoryFilter" class="form-select modern-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
 
                <div class="col-lg-2 col-md-6">
                    <label class="filter-label">
                        <i class="fas fa-money-bill me-2"></i>Pricing
                    </label>
                    <select id="pricingFilter" class="form-select modern-select">
                        <option value="">All Types</option>
                        <option value="free">Free</option>
                        <option value="paid">Premium</option>
                    </select>
                </div>
 
                <div class="col-lg-5 col-md-8">
                    <label class="filter-label">
                        <i class="fas fa-search me-2"></i>Search Exams
                    </label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input
                            id="searchFilter"
                            type="text"
                            class="form-control modern-input"
                            placeholder="Search by exam name or description…"
                        >
                        <div id="searchLoading" class="search-loading" style="display:none;">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </div>
                </div>
 
                <div class="col-lg-2 col-md-4">
                    <button id="clearFilters" class="btn btn-outline-secondary w-100 modern-btn">
                        <i class="fas fa-times me-2"></i>Clear All
                    </button>
                </div>
 
            </div>
        </div>

        {{-- *** Cards / modals / login handlers come from this include *** --}}
        @include('modules.exam.viewExam')
 
        {{-- "No results" message – shown by JS when needed --}}
        <div id="noResults" class="no-results-container" style="display:none;">
            <div class="no-results-content">
                <div class="no-results-icon"><i class="fas fa-search-minus"></i></div>
                <h4 class="no-results-title">No exams found</h4>
                <p class="no-results-text">
                    We couldn't find any exams matching your criteria.
                    Try adjusting filters or search terms.
                </p>
                <button class="btn btn-primary modern-btn" onclick="clearAllFilters()">
                    <i class="fas fa-refresh me-2"></i>Show All Exams
                </button>
            </div>
        </div>
 
    </div>
</section>
@endsection
 
@push('scripts')
<script>
/* ======================================================
   Filtering & UI helpers
   ====================================================== */
let originalExamsData = [];            // immutable master list
let isFilterActive     = false;        // tracks any active filter
 
const debounce = (fn, ms = 300) => {
    let t; return (...args) => {
        clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms);
    };
};
 
function updateHeroStats(list) {
    // Add count-up animation
    animateValue(document.getElementById('totalExamsCount'), 0, list.length, 1000);
    animateValue(document.getElementById('freeExamsCount'), 0, list.filter(e => e.pricing_model === 'free').length, 1200);
    animateValue(document.getElementById('paidExamsCount'), 0, list.filter(e => e.pricing_model === 'paid').length, 1400);
}

function animateValue(element, start, end, duration) {
    let startTime = null;
    
    function animate(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const progress = Math.min(timeElapsed / duration, 1);
        
        element.textContent = Math.floor(progress * (end - start) + start);
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    requestAnimationFrame(animate);
}
 
function populateCategories(list) {
    categoryFilter.innerHTML = '<option value="">All Categories</option>';
    [...new Set(list.map(e => e.category).filter(Boolean))]
        .sort()
        .forEach(cat => categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`);
}
 
function getFilteredExams() {
    const cat   = categoryFilter.value;
    const price = pricingFilter.value;
    const term  = searchFilter.value.trim().toLowerCase();
 
    return originalExamsData.filter(exam => {
        const catOk   = !cat   || exam.category      === cat;
        const priceOk = !price || exam.pricing_model === price;
        const termOk  = !term  ||
            exam.examName.toLowerCase().includes(term) ||
            (exam.examDescription||'').toLowerCase().includes(term) ||
            (exam.category||'').toLowerCase().includes(term);
        return catOk && priceOk && termOk;
    });
}
 
function applyFilters() {
    const showSpinner = searchFilter.value.trim();
    searchLoading.style.display = showSpinner ? 'block' : 'none';
 
    const filtered = getFilteredExams();
 
    isFilterActive = categoryFilter.value || pricingFilter.value || searchFilter.value.trim();
 
    if (filtered.length === 0) {
        noResults.style.display = 'block';
        examCardsRow.innerHTML  = '';
        viewMoreContainer.style.display = 'none';
    } else {
        noResults.style.display = 'none';
 
        /* reset pagination provided by viewExam.js */
        allExams       = [...filtered];
        displayedExams = [];
        currentPage    = 0;
        initializePagination(filtered);
    }
    searchLoading.style.display = 'none';
}
 
function clearAllFilters(render = true) {
    categoryFilter.value = '';
    pricingFilter.value  = '';
    searchFilter.value   = '';
    if (render) applyFilters();
}
 
/* ======================================================
   Override (decorate) fetchExams once it exists
   ====================================================== */
if (typeof fetchExams === 'function') {
    const originalFetchExams = fetchExams;
 
    fetchExams = async function () {
        /* 1️⃣  let the original build everything (sidebar, cards, etc.) */
        await originalFetchExams();
 
        /* 2️⃣  capture the master list created by viewExam.js */
        originalExamsData = [...allExams];
 
        /* 3️⃣  enrich the header & filters */
        updateHeroStats(originalExamsData);
        populateCategories(originalExamsData);
 
        /* 4️⃣  ensure noResults is hidden */
        noResults.style.display = 'none';
    };
}
 
/* ======================================================
   DOM ready – bind events (after viewExam.js is loaded)
   ====================================================== */
document.addEventListener('DOMContentLoaded', () => {
    /* filter events */
    categoryFilter.addEventListener('change', applyFilters);
    pricingFilter .addEventListener('change', applyFilters);
    searchFilter  .addEventListener('input', debounce(applyFilters, 300));
    clearFilters  .addEventListener('click', () => clearAllFilters());
});
 
/* expose to inline onclick */
window.clearAllFilters = clearAllFilters;
</script>
@endpush