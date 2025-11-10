{{-- resources/views/landingPage/partials/navbar.blade.php --}}
<nav class="custom-navbar">
    <div class="custom-navbar-container">
        <!-- Brand/Logo -->
        <a class="custom-navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Horizon Allienz" class="navbar-logo">
            <span class="navbar-brand-text">Horizon Allienz</span>
        </a>
 
        <!-- Desktop Navigation Links -->
        <div class="custom-navbar-links">
            <a class="custom-nav-link" href="{{ url('/') }}" data-page="home">Home</a>
            <a class="custom-nav-link" href="/exams" data-page="exam">Exams</a>
            <a class="custom-nav-link" href="#features" data-page="features">Features</a>
            <a class="custom-nav-link" href="#about" data-page="about">About</a>
            <a class="custom-nav-link" href="#contact" data-page="contact">Contact</a>
            <a class="custom-nav-link" href="/selectRole" data-page="account">Account</a>
        </div>
 
        <!-- CTA Button -->
        <div class="custom-navbar-cta">
            <a href="/selectRole" id="customCtaButton" class="custom-cta-btn">
                <i class="fas fa-play"></i>
                <span class="cta-text">Login</span>
            </a>
        </div>
 
        <!-- Mobile Menu Toggle -->
        <button class="custom-mobile-toggle" id="mobileToggle">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
 
    <!-- Mobile Sidebar -->
    <div class="custom-mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-header">
            <div class="mobile-sidebar-brand">
                <img src="{{ asset('assets/images/logo.webp') }}" alt="Horizon Allienz" class="mobile-logo">
                <span class="mobile-brand-text">Horizon Allienz</span>
            </div>
            <button class="mobile-close-btn" id="mobileClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
       
        <div class="mobile-sidebar-content">
            <div class="mobile-nav-links">
                <a class="mobile-nav-link" href="{{ url('/') }}" data-page="home">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a class="mobile-nav-link" href="/exams" data-page="exam">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Exams</span>
                </a>
                <a class="mobile-nav-link" href="#features" data-page="features">
                    <i class="fas fa-star"></i>
                    <span>Features</span>
                </a>
                <a class="mobile-nav-link" href="#about" data-page="about">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
                <a class="mobile-nav-link" href="#contact" data-page="contact">
                    <i class="fas fa-phone"></i>
                    <span>Contact</span>
                </a>
                <a class="mobile-nav-link" href="/selectRole" data-page="account">
                    <i class="fas fa-user"></i>
                    <span>Account</span>
                </a>
            </div>
           
            <div class="mobile-cta-section">
                <a href="/selectRole" class="mobile-cta-btn">
                    <i class="fas fa-play"></i>
                    <span>Get Started</span>
                </a>
            </div>
        </div>
    </div>
 
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
</nav>
 
<script>
// Mobile Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle   = document.getElementById('mobileToggle');
    const mobileClose    = document.getElementById('mobileClose');
    const mobileSidebar  = document.getElementById('mobileSidebar');
    const mobileOverlay  = document.getElementById('mobileOverlay');
    const customCtaBtn   = document.getElementById('customCtaButton');
    const ctaText        = customCtaBtn.querySelector('.cta-text');
    const mobileCtaBtn   = document.querySelector('.mobile-cta-btn');
    const mobileCtaText  = mobileCtaBtn.querySelector('span');
 
    // 1️⃣ Configure CTA (Login → Dashboard/admin vs student)
    const token     = sessionStorage.getItem('token');
    const tokenType = sessionStorage.getItem('token_type');
    if (token) {
        ctaText.textContent = 'Dashboard';
        customCtaBtn.href   = tokenType === 'admin'
                             ? '/admin/dashboard'
                             : '/student/dashboard';
        mobileCtaText.textContent = 'Dashboard';
        mobileCtaBtn.href   = tokenType === 'admin'
                             ? '/admin/dashboard'
                             : '/student/dashboard';
    }
 
    // 2️⃣ Set Active Navigation State
    function setActiveNavigation() {
        const currentPath = window.location.pathname;
        const currentHash = window.location.hash;
       
        // Remove all active classes first
        document.querySelectorAll('.custom-nav-link, .mobile-nav-link').forEach(link => {
            link.classList.remove('active');
        });
       
        // Determine which page is active
        let activePage = '';
       
        if (currentPath === '/' || currentPath === '') {
            activePage = 'home';
        } else if (currentPath.includes('/exams')) {
            activePage = 'exam';
        } else if (currentPath.includes('/selectRole')) {
            activePage = 'account';
        } else if (currentHash === '#features') {
            activePage = 'features';
        } else if (currentHash === '#about') {
            activePage = 'about';
        } else if (currentHash === '#contact') {
            activePage = 'contact';
        }
       
        // Set active class on matching links
        if (activePage) {
            document.querySelectorAll(`[data-page="${activePage}"]`).forEach(link => {
                link.classList.add('active');
            });
           
            // Store current page in sessionStorage for persistence
            sessionStorage.setItem('currentPage', activePage);
        }
    }
   
    // 3️⃣ Handle Navigation Clicks
    document.querySelectorAll('.custom-nav-link, .mobile-nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const page = this.getAttribute('data-page');
           
            // For hash links, don't prevent default but update active state
            if (this.getAttribute('href').startsWith('#')) {
                setTimeout(() => {
                    setActiveNavigation();
                }, 100);
            } else {
                // Store the page we're navigating to
                sessionStorage.setItem('currentPage', page);
            }
        });
    });
   
    // 4️⃣ Initialize active state on page load
    setActiveNavigation();
   
    // 5️⃣ Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        setActiveNavigation();
    });
   
    // 6️⃣ Handle hash changes for same-page navigation
    window.addEventListener('hashchange', function() {
        setActiveNavigation();
    });
   
    // Open mobile sidebar
    function openMobileSidebar() {
        mobileSidebar.classList.add('active');
        mobileOverlay.classList.add('active');
        document.body.classList.add('mobile-menu-open');
    }
   
    // Close mobile sidebar
    function closeMobileSidebar() {
        mobileSidebar.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.classList.remove('mobile-menu-open');
    }
   
    // Event listeners
    mobileToggle.addEventListener('click', openMobileSidebar);
    mobileClose.addEventListener('click', closeMobileSidebar);
    mobileOverlay.addEventListener('click', closeMobileSidebar);
   
    // Close sidebar when clicking on nav links
    document.querySelectorAll('.mobile-nav-link').forEach(link => {
        link.addEventListener('click', closeMobileSidebar);
    });
   
    // Handle navbar scroll effect
    let lastScrollTop = 0;
    const navbar = document.querySelector('.custom-navbar');
   
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
       
        // Add/remove scrolled class
        if (scrollTop > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
       
        // Hide/show navbar on scroll (only on desktop)
        if (window.innerWidth > 991) {
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                navbar.classList.add('navbar-hidden');
            } else {
                navbar.classList.remove('navbar-hidden');
            }
        }
       
        lastScrollTop = scrollTop;
    });
   
    // Handle smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
   
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileSidebar.classList.contains('active')) {
            closeMobileSidebar();
        }
    });
   
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 991 && mobileSidebar.classList.contains('active')) {
            closeMobileSidebar();
        }
    });
});
</script>