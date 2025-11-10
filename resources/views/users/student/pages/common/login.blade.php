{{-- resources/views/pages/student/studentLogin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Login – Exam Portal</title>
 
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
 
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
 
  <!-- Page Styles -->
  <link rel="stylesheet" href="{{ asset('css/common/login.css') }}">
 
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
 
<body class="login-body">
  <div class="background-pattern">
    <div class="pattern-circle pattern-circle-1"></div>
    <div class="pattern-circle pattern-circle-2"></div>
    <div class="pattern-circle pattern-circle-3"></div>
  </div>
 
  <div class="container-fluid h-100">
    <div class="row h-100 gx-0">
      {{-- Left Branding Panel --}}
      <aside class="col-lg-7 d-none d-lg-flex login-brand-panel align-items-center">
        <div class="brand-content text-center text-white mx-auto">
          <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="brand-logo mb-4">
          <h1 class="brand-title mb-1">Student Portal</h1>
          <p class="brand-subtitle mb-5">Access your academic journey with ease</p>
 
          <div class="row gx-4">
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-graduation-cap fa-2x mb-2"></i>
              <h5>Track Progress</h5>
              <p>Monitor your academic performance and achievements.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-book-open fa-2x mb-2"></i>
              <h5>Access Resources</h5>
              <p>Get instant access to study materials and assignments.</p>
            </div>
            <div class="col-md-4 feature-item mb-4">
              <i class="fas fa-users fa-2x mb-2"></i>
              <h5>Connect & Learn</h5>
              <p>Collaborate with peers and instructors seamlessly.</p>
            </div>
          </div>
        </div>
      </aside>
 
      {{-- Right Login Form --}}
      <main class="col-lg-5 d-flex login-form-panel align-items-center justify-content-center">
        <div class="login-card">
          <div class="login-header text-center mb-4">
            <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="mobile-logo mb-3 d-lg-none">
            <h2 class="login-title mb-1">Welcome Back</h2>
            <p class="login-subtitle mb-0">Sign in to your student account</p>
          </div>
 
          <form id="loginForm" class="login-form needs-validation" novalidate>
            <div class="mb-3">
              <label for="login" class="form-label">
                <i class="fas fa-user label-icon"></i>
                Phone or Email <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-2">
                <input
                  type="text"
                  class="form-control flex-grow-1"
                  id="login"
                  name="login"
                  placeholder="Enter phone or email"
                  required
                />
                <button type="button" class="btn otp-btn d-flex justify-content-center align-items-center gap-1" id="sendOtpBtn">
                  <i class="fas fa-paper-plane"></i> <span>OTP</span>
                </button>
              </div>
              <div class="invalid-feedback">
                Please enter a valid phone number or email.
              </div>
            </div>
 
            <div class="mb-3 otp-group collapse" id="otpWrapper">
              <label for="otp" class="form-label">
                <i class="fas fa-shield-alt label-icon"></i>
                One-Time Password
              </label>
              <input
                type="text"
                class="form-control"
                id="otp"
                placeholder="6-digit code"
                readonly
              />
            </div>
 
            <button type="submit" class="btn submit-btn w-100 mt-3">
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
 
  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 
  <script>
    (() => {
      let sentOtp = null;
 
      // Utility to show validation feedback
      const form = document.getElementById('loginForm');
      form.addEventListener('submit', e => {
        e.preventDefault();
        if (!form.checkValidity() || !sentOtp) {
          form.classList.add('was-validated');
          return;
        }
        // OTP always correct (auto-filled). Proceed to login…
        const submitBtn = form.querySelector('button[type=submit]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in…';
        submitBtn.disabled = true;
 
        fetch('/api/student/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ login: form.login.value.trim() })
        })
        .then(r => r.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
          if (data.success) {
            sessionStorage.setItem('token', data.student_token);
            sessionStorage.setItem('token_type', data.tokenable_type);
            sessionStorage.setItem('student_id', data.student_id || '');
            Swal.fire({
              icon: 'success',
              title: `Welcome ${data.name || 'Student'}!`,
              timer: 1200,
              showConfirmButton: false
            }).then(() => window.location.href = '/student/dashboard');
          } else {
            Swal.fire({ icon: 'error', title: 'Login Failed', text: data.message });
          }
        })
        .catch(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
          Swal.fire({ icon: 'error', title: 'Connection Error', text: 'Please try again later.' });
        });
      });
 
      // Send OTP Handler
      document.getElementById('sendOtpBtn').addEventListener('click', () => {
        const loginVal = form.login.value.trim();
        if (!loginVal) {
          return Swal.fire({ icon: 'warning', title: 'Required', text: 'Enter phone or email first.' });
        }
 
        // generate OTP
        sentOtp = String(Math.floor(100000 + Math.random() * 900000));
        const isEmail = /^\S+@\S+\.\S+$/.test(loginVal);
        const isPhone = /^[6-9]\d{9}$/.test(loginVal);
 
        // show loading
        Swal.fire({
          title: 'Sending OTP…',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => Swal.showLoading()
        });
 
        setTimeout(() => {
          Swal.close();
 
          // Auto-fill OTP for both email & phone for demo
          document.getElementById('otp').value = sentOtp;
          const otpGroup = new bootstrap.Collapse(document.getElementById('otpWrapper'), {
            show: true
          });
 
          Swal.fire({
            icon: 'success',
            title: 'OTP Ready',
            text: 'We’ve auto-filled your code.',
            timer: 1200,
            showConfirmButton: false
          });
        }, 1500);
      });
    })();
  </script>
</body>
</html>
 
 