<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Results by Email</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
   
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
   
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/pages/result/viewResultByEmail.css') }}"/>
</head>
<body>
    <div class="background-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>
   
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="main-card">
                    <div class="card-header-custom">
                        <div class="icon-container">
                            <div class="logo-wrapper">
                             <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo" class="company-logo">
                            </div>    
                        </div>
                        <h1 class="main-title">Get Your Results</h1>
                        <p class="subtitle">Secure email verification required</p>
                    </div>
                   
                    <div class="card-body-custom">
                        <div id="email-form" class="form-section">
                            <div class="input-group-custom">
                                <label for="email" class="form-label-custom">
                                    <i class="fas fa-at"></i>
                                    Email Address
                                </label>
                                <div class="input-wrapper">
                                    <input type="email" id="email" class="form-control-custom" placeholder="you@example.com">
                                    <div class="input-border"></div>
                                </div>
                            </div>
                            <button id="send-otp-btn" class="btn-custom btn-primary-custom">
                                <span class="btn-text">Send Verification Code</span>
                                <i class="fas fa-paper-plane btn-icon"></i>
                            </button>
                        </div>
                       
                        <div id="otp-form" class="form-section d-none">
                            <div class="otp-intro">
                                <div class="otp-sent-indicator">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Code sent successfully!</span>
                                </div>
                            </div>
                           
                            <div class="input-group-custom">
                                <label for="otp" class="form-label-custom">
                                    <i class="fas fa-shield-alt"></i>
                                    Verification Code
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" id="otp" class="form-control-custom" placeholder="000000" maxlength="6">
                                    <div class="input-border"></div>
                                </div>
                                <div class="form-help">
                                    <i class="fas fa-info-circle"></i>
                                    Check your inbox for the 6-digit code
                                </div>
                            </div>
                            <button id="verify-otp-btn" class="btn-custom btn-success-custom">
                                <span class="btn-text">Verify & Get Results</span>
                                <i class="fas fa-lock-open btn-icon"></i>
                            </button>
                        </div>
                       
                        <div class="security-notice">
                            <div class="security-badge">
                                <i class="fas fa-shield-check"></i>
                                <div class="security-text">
                                    <strong>Secure Verification</strong>
                                    <small>Your email will be verified before sending results</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailForm = document.getElementById('email-form');
            const otpForm = document.getElementById('otp-form');
            const sendOtpBtn = document.getElementById('send-otp-btn');
            const verifyOtpBtn = document.getElementById('verify-otp-btn');
            const emailInput = document.getElementById('email');
            const otpInput = document.getElementById('otp');
           
            let sentOtp = null;
            const urlParams = new URLSearchParams(window.location.search);
            const examId = urlParams.get('exam_id');
            const studentId = sessionStorage.getItem('student_id');
            const token = sessionStorage.getItem('student_token') || sessionStorage.getItem('token');
           
            if (!examId || !studentId || !token) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Required information is missing. Please try again from the exam page.',
                }).then(() => {
                    window.location.href = '/student/exam';
                });
                return;
            }
           
            // Send OTP button click handler
            sendOtpBtn.addEventListener('click', async function() {
                const email = emailInput.value.trim();
               
                if (!/^\S+@\S+\.\S+$/.test(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Email',
                        text: 'Please enter a valid email address',
                    });
                    return;
                }
               
                try {
                    sendOtpBtn.disabled = true;
                    sendOtpBtn.innerHTML = '<span class="btn-text"><i class="fas fa-spinner fa-spin me-2"></i>Sending...</span>';
                   
                    // Generate OTP
                    sentOtp = String(Math.floor(100000 + Math.random() * 900000));
                   
                    // Send OTP email
                    const response = await fetch('/api/send-mail', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            to: email,
                            subject: 'Your Exam Verification Code',
                            message: `Your verification code is: ${sentOtp}`
                        })
                    });
                   
                    if (!response.ok) {
                        throw new Error('Failed to send OTP');
                    }
                   
                    // Show OTP form with animation
                    emailForm.style.transform = 'translateX(-100%)';
                    emailForm.style.opacity = '0';
                   
                    setTimeout(() => {
                        emailForm.classList.add('d-none');
                        otpForm.classList.remove('d-none');
                        otpForm.style.transform = 'translateX(0)';
                        otpForm.style.opacity = '1';
                    }, 300);
                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Code Sent',
                        text: 'A 6-digit verification code has been sent to your email',
                        timer: 3000,
                        showConfirmButton: false
                    });
                   
                } catch (error) {
                    console.error('Error sending OTP:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to send verification code. Please try again.',
                    });
                } finally {
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.innerHTML = '<span class="btn-text">Send Verification Code</span><i class="fas fa-paper-plane btn-icon"></i>';
                }
            });
           
            // Verify OTP button click handler
            verifyOtpBtn.addEventListener('click', async function() {
                const otp = otpInput.value.trim();
                const email = emailInput.value.trim();
               
                if (otp.length !== 6 || otp !== sentOtp) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Code',
                        text: 'Please enter the correct 6-digit code',
                    });
                    return;
                }
               
                try {
                    verifyOtpBtn.disabled = true;
                    verifyOtpBtn.innerHTML = '<span class="btn-text"><i class="fas fa-spinner fa-spin me-2"></i>Processing...</span>';
                   
                    // Send the exam results
                    const resultResponse = await fetch('/api/exam/result-to-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify({
                            student_id: studentId,
                            email: email,
                            exam_id: examId
                        })
                    });
                   
                    const resultData = await resultResponse.json();
                   
                    if (!resultResponse.ok || !resultData.success) {
                        throw new Error(resultData.message || 'Failed to send results');
                    }
                   
                    // Success
                    await Swal.fire({
                        icon: 'success',
                        title: 'Results Sent!',
                        html: `Your exam results have been sent to <strong>${email}</strong>.
                               <br>Please check your inbox (and spam folder if you don't see it).`,
                        confirmButtonText: 'Go to Dashboard'
                    });
                   
                    window.location.href = '/student/dashboard';
                   
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to send results. Please try again.',
                    });
                } finally {
                    verifyOtpBtn.disabled = false;
                    verifyOtpBtn.innerHTML = '<span class="btn-text">Verify & Get Results</span><i class="fas fa-lock-open btn-icon"></i>';
                }
            });
 
            // Auto-format OTP input
            otpInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
 
            // Focus animations
            [emailInput, otpInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
               
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
</body>
</html>
 