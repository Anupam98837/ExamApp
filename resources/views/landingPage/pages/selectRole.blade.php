{{-- resources/views/selectRole.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Select Your Role - Exam Management Portal</title>
 
    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
          crossorigin="anonymous"/>
 
    {{-- Font-Awesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet"
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
 
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
 
    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('css/common/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common/role.css') }}">
</head>
 
<body>
    {{-- Animated Background --}}
    <div class="bg-grid"></div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
    <div class="bg-noise"></div>
 
    <div class="main-container">
        {{-- Floating Navigation --}}
        {{-- <nav class="floating-nav">
            <div class="nav-brand">
                <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="nav-logo">
                <span class="nav-title">Horizon Allienz Exam Portal</span>
            </div>
            <div class="nav-status">
                <div class="status-indicator"></div>
                <span>System Online</span>
            </div>
        </nav> --}}
 
        {{-- HERO + ROLE CARDS (now stacked) --}}
        <div class="hero-section">
            <div class="content-grid">
                {{-- ===== HERO CONTENT ===== --}}
                <div class="content-left">
                    <div class="hero-badge">
                        <span class="badge-text">SECURE PORTAL</span>
                        <div class="badge-glow"></div>
                    </div>
 
                    <h1 class="hero-title">
                        <span class="title-line">Choose Your</span>
                        <span class="title-accent">Access Level</span>
                    </h1>
 
                    <p class="hero-description">
                        Experience next-generation exam management with enterprise-grade security
                        and intuitive design built for the modern era.
                    </p>
 
                    <div class="stats-container">
                        <div class="stat-item">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">256-bit</div>
                            <div class="stat-label">Encryption</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                </div>
 
                {{-- ===== ROLE CARDS ===== --}}
                <div class="content-right">
                    <div class="role-cards-container">
                        {{-- Administrator Card --}}
                        <a href="{{ url('/admin/login') }}" class="role-card-link">
                            <div class="role-card role-admin" data-role="admin">
                                <div class="card-glow"></div>
                                <div class="card-border"></div>
 
                                <div class="card-header">
                                    <div class="role-icon-wrapper">
                                        <div class="icon-bg"></div>
                                        <i class="fas fa-crown role-icon"></i>
                                    </div>
                                    <div class="card-badge">ADMIN</div>
                                </div>
 
                                <div class="card-content">
                                    <h3 class="card-title">System Administrator</h3>
                                    <p class="card-description">
                                        Full system control with advanced analytics and user management capabilities
                                    </p>
 
                                    <div class="card-features">
                                        <div class="feature-item">
                                            <i class="fas fa-shield-alt"></i>
                                            <span>Security Control</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Analytics Dashboard</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-users-cog"></i>
                                            <span>User Management</span>
                                        </div>
                                    </div>
                                </div>
 
                                <div class="card-footer">
                                    <div class="access-level">
                                        <span>Access Level: </span><strong>MAXIMUM</strong>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
 
                        {{-- Student Card --}}
                        <a href="{{ url('/student/login') }}" class="role-card-link">
                            <div class="role-card role-student" data-role="student">
                                <div class="card-glow"></div>
                                <div class="card-border"></div>
 
                                <div class="card-header">
                                    <div class="role-icon-wrapper">
                                        <div class="icon-bg"></div>
                                        <i class="fas fa-rocket role-icon"></i>
                                    </div>
                                    <div class="card-badge">STUDENT</div>
                                </div>
 
                                <div class="card-content">
                                    <h3 class="card-title">Student Portal</h3>
                                    <p class="card-description">
                                        Streamlined exam experience with real-time progress tracking and instant results
                                    </p>
 
                                    <div class="card-features">
                                        <div class="feature-item">
                                            <i class="fas fa-play-circle"></i>
                                            <span>Interactive Exams</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-bolt"></i>
                                            <span>Instant Results</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-trophy"></i>
                                            <span>Achievement System</span>
                                        </div>
                                    </div>
                                </div>
 
                                <div class="card-footer">
                                    <div class="access-level">
                                        <span>Access Level: </span><strong>STANDARD</strong>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> {{-- /content-right --}}
            </div> {{-- /content-grid --}}
        </div> {{-- /hero-section --}}
 
        {{-- Footer --}}
        <footer class="modern-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div class="footer-brand">Horizon Alienz</div>
                    <div class="footer-year">© 2025</div>
                </div>
                <div class="footer-links">
                    <a href="#" class="footer-link">Privacy</a>
                    <a href="#" class="footer-link">Terms</a>
                    <a href="#" class="footer-link">Support</a>
                </div>
                <div class="footer-right">
                    <div class="version-badge">v2.1.0</div>
                </div>
            </div>
        </footer>
    </div>{{-- /main-container --}}
 
    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
            crossorigin="anonymous"></script>
</body>
</html>
 
 