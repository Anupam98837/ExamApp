<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>

  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
    crossorigin="anonymous"
  />

  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    rel="stylesheet"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <!-- Main color variables -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}">

  <!-- Login-specific styles -->
  <link rel="stylesheet" href="{{ asset('css/common/login.css') }}">
</head>

<body class="login-body">
  <div class="background-pattern">
    <div class="pattern-circle pattern-circle-1"></div>
    <div class="pattern-circle pattern-circle-2"></div>
    <div class="pattern-circle pattern-circle-3"></div>
  </div>

  <div class="container-fluid h-100">
    <div class="row h-100 gx-0">
      {{-- Left Admin Branding Panel --}}
      <aside class="col-lg-7 d-none d-lg-flex admin-brand-panel align-items-center">
        <div class="brand-content text-center text-white mx-auto">
          <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="brand-logo mb-4">
          <h1 class="brand-title mb-1">Admin Portal</h1>
          <p class="brand-subtitle mb-5">Manage your educational platform with confidence</p>

          <div class="row gx-4">
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-tachometer-alt fa-2x mb-2"></i>
              <h5>Dashboard Control</h5>
              <p>Monitor system performance and user activities in real-time.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-users-cog fa-2x mb-2"></i>
              <h5>User Management</h5>
              <p>Efficiently manage students, instructors, and staff accounts.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-chart-line fa-2x mb-2"></i>
              <h5>Analytics & Reports</h5>
              <p>Access comprehensive analytics and generate detailed reports.</p>
            </div>
          </div>
        </div>
      </aside>

      {{-- Right Admin Login Form --}}
      <main class="col-lg-5 d-flex login-form-panel align-items-center justify-content-center">
        <div class="admin-login-card">
          <div class="login-header text-center mb-4">
            <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="mobile-logo mb-3 d-lg-none">
            <h2 class="login-title mb-1">Admin Access</h2>
            <p class="login-subtitle mb-0">Sign in to your admin account</p>
          </div>

          <form id="loginForm" class="login-form">
            <div class="mb-3">
              <label for="email" class="form-label">
                <i class="fas fa-envelope label-icon"></i>
                Email address <span class="text-danger">*</span>
              </label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="admin@example.com"
                required
              />
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">
                <i class="fas fa-lock label-icon"></i>
                Password <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input
                  type="password"
                  class="form-control"
                  id="password"
                  name="password"
                  placeholder="********"
                  required
                />
                <span class="input-group-text admin-password-toggle" id="togglePassword">
                  <i class="fas fa-eye"></i>
                </span>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="rememberMe"
                  name="remember"
                />
                <label class="form-check-label admin-check-label" for="rememberMe">
                  Remember me
                </label>
              </div>
              <a href="#" class="admin-forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn submit-btn w-100">
              <i class="fas fa-sign-in-alt"></i>
              Sign In
            </button>

            <div class="text-center mt-3">
              <a href="/" class="text-secondary btn-back">
                <i class="fas fa-arrow-left"></i> Back to Home
              </a>
            </div>
          </form>

          <footer class="login-footer text-center mt-4">
            <small>&copy; 2025 Exam Management Portal. All rights reserved.</small>
          </footer>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap Bundle JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
    crossorigin="anonymous"
  ></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom JS -->
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
      const pw = document.getElementById('password');
      const icon = this.querySelector('i');
      if (pw.type === 'password') {
        pw.type = 'text'; icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        pw.type = 'password'; icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });

    // Handle login submission with SweetAlert feedback
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
      submitBtn.disabled = true;

      fetch('/api/admin/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: this.email.value, password: this.password.value })
      })
      .then(res => res.json())
      .then(json => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign In';

        if (json.status === 'success') {
          
          Swal.fire({
            icon: 'success',
            title: 'Login Successful!',
            text: json.message || 'Welcome back!',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            background: '#fff',
            color: 'var(--primary-color)'
          }).then(() => {
            sessionStorage.setItem('token', json.access_token);
            sessionStorage.setItem('token_type', json.tokenable_type);            
            window.location.href = '/admin/dashboard';
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: json.message || 'Invalid credentials. Please try again.',
            confirmButtonColor: 'var(--primary-color)'
          });
        }
      })
      .catch(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Sign In';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Unable to connect to server. Please try later.',
          confirmButtonColor: 'var(--primary-color)'
        });
      });
    });
  </script>
</body>
</html>