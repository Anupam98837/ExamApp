<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Registration</title>
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <!-- Main vars & floating blobs -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common/login.css') }}">
  <!-- Registration overrides -->
  <link rel="stylesheet" href="{{ asset('css/common/register.css') }}">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">

            <!-- Logo + Title + Back -->
            <div class="text-center mb-4">
              <img src="{{ asset('assets/web_assets/logo.png') }}"
                   alt="Horizon Alienz"
                   width="100"
                   class="mb-3 float-up" />
              <h2 id="formTitle" class="h4 fw-bold">Student Registration</h2>
              <a href="#" id="btnBack" class="btn btn-back mt-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
              </a>              
            </div>

            <div id="flash-messages"></div>

            <!-- Multi-step form -->
            <form id="multiStepForm" novalidate>
              @csrf

              <!-- Progress bar -->
              <div class="progress mb-4">
                <div class="progress-bar bg-primary"
                     id="progressBar"
                     role="progressbar"
                     style="width:20%"></div>
              </div>

              <!-- STEP 1: Basic Details -->
              <div class="step active" id="step-1">
                <div class="row g-3">
                  <!-- Name -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-user text-primary me-2"></i>
                      Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           class="form-control"
                           placeholder="Enter your full name"
                           required maxlength="255" />
                  </div>
                  <!-- Phone -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-phone text-primary me-2"></i>
                      Phone <span class="text-danger">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone"
                           class="form-control"
                           placeholder="Enter phone number"
                           required maxlength="20" />
                  </div>
                  <!-- Email -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-envelope text-primary me-2"></i>
                      Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           placeholder="Enter email address"
                           required />
                  </div>
                  <!-- Password + toggle -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-lock text-primary me-2"></i>
                      Password <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <input type="password" id="password" name="password"
                             class="form-control"
                             placeholder="Choose a password"
                             required minlength="8" />
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- STEP 2: Additional Details -->
              <div class="step d-none" id="step-2">
                <div class="row g-3">
                  <!-- Alt Phone -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-phone-square-alt text-primary me-2"></i>
                      Alternative Phone
                    </label>
                    <input type="tel" id="alternative_phone" name="alternative_phone"
                           class="form-control"
                           placeholder="Enter alternative phone"
                           maxlength="20" />
                  </div>
                  <!-- Alt Email -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-envelope-open-text text-primary me-2"></i>
                      Alternative Email
                    </label>
                    <input type="email" id="alternative_email" name="alternative_email"
                           class="form-control"
                           placeholder="Enter alternative email" />
                  </div>
                  <!-- WhatsApp -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fab fa-whatsapp text-success me-2"></i>
                      WhatsApp No
                    </label>
                    <input type="text" id="whatsapp_no" name="whatsapp_no"
                           class="form-control"
                           placeholder="Enter WhatsApp number"
                           maxlength="20" />
                  </div>
                  <!-- DOB -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-calendar-alt text-primary me-2"></i>
                      Date of Birth
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           class="form-control" />
                  </div>
                  <!-- Place of Birth -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-map-marker-alt text-primary me-2"></i>
                      Place of Birth
                    </label>
                    <input type="text" id="place_of_birth" name="place_of_birth"
                           class="form-control"
                           placeholder="City or town of birth"
                           maxlength="255" />
                  </div>
                  <!-- Religion -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-globe text-primary me-2"></i>
                      Religion
                    </label>
                    <input list="religions" id="religion" name="religion"
                           class="form-control"
                           placeholder="Enter or select" />
                    <datalist id="religions">
                      <option>Islam</option><option>Hinduism</option>
                      <option>Christianity</option><option>Buddhism</option>
                      <option>Sikhism</option><option>Other</option>
                    </datalist>
                  </div>
                  <!-- Caste -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-layer-group text-primary me-2"></i>
                      Caste
                    </label>
                    <input list="castes" id="caste" name="caste"
                           class="form-control"
                           placeholder="Enter or select" />
                    <datalist id="castes">
                      <option>General</option><option>OBC</option>
                      <option>SC</option><option>ST</option><option>Other</option>
                    </datalist>
                  </div>
                  <!-- Blood Group -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-tint text-primary me-2"></i>
                      Blood Group
                    </label>
                    <input list="bgs" id="blood_group" name="blood_group"
                           class="form-control"
                           placeholder="Select or enter" />
                    <datalist id="bgs">
                      <option>A+</option><option>A-</option>
                      <option>B+</option><option>B-</option>
                      <option>O+</option><option>O-</option>
                      <option>AB+</option><option>AB-</option>
                    </datalist>
                  </div>
                  <!-- Identity Type -->
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-id-card text-primary me-2"></i>
                      Identity Type
                    </label>
                    <select id="identity_type" name="identity_type" class="form-select">
                      <option value="">None</option>
                      <option>Aadhar</option>
                      <option>Voter ID</option>
                      <option>PAN</option>
                    </select>
                  </div>
                  <!-- Identity Details -->
                  <div class="col-md-6 d-none" id="identity_details_wrap">
                    <label class="form-label">
                      <i class="fas fa-hashtag text-primary me-2"></i>
                      Identity Details
                    </label>
                    <input type="text" id="identity_details" name="identity_details"
                           class="form-control"
                           placeholder="Enter ID number"
                           maxlength="20" />
                  </div>
                </div>
              </div>

              <!-- STEP 3: Address -->
              <div class="step d-none" id="step-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-city text-primary me-2"></i>
                      City
                    </label>
                    <input type="text" id="city" name="city"
                           class="form-control"
                           placeholder="Enter city"
                           maxlength="100" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-postcard text-primary me-2"></i>
                      Post Office
                    </label>
                    <input type="text" id="po" name="po"
                           class="form-control"
                           placeholder="Enter post office"
                           maxlength="100" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-shield-alt text-primary me-2"></i>
                      Police Station
                    </label>
                    <input type="text" id="ps" name="ps"
                           class="form-control"
                           placeholder="Enter police station"
                           maxlength="100" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-flag text-primary me-2"></i>
                      State
                    </label>
                    <input type="text" id="state" name="state"
                           class="form-control"
                           placeholder="Enter state"
                           maxlength="100" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-globe text-primary me-2"></i>
                      Country
                    </label>
                    <input type="text" id="country" name="country"
                           class="form-control"
                           placeholder="Enter country"
                           maxlength="100" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-map-pin text-primary me-2"></i>
                      Pin Code
                    </label>
                    <input type="text" id="pin" name="pin"
                           class="form-control"
                           placeholder="Enter pin code"
                           maxlength="6" />
                  </div>
                </div>
              </div>

              <!-- STEP 4: Educational Details -->
              <div class="step d-none" id="step-4">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">
                      <i class="fas fa-layer-group text-primary me-2"></i>
                      Department
                    </label>
                    <input type="text" id="department" name="department"
                           class="form-control"
                           placeholder="Enter department"
                           maxlength="255" />
                  </div>
                  <div class="col-md-6">  
                    <label class="form-label">
                      <i class="fas fa-book text-primary me-2"></i>
                      Course
                    </label>
                    <input type="text" id="course" name="course"
                           class="form-control"
                           placeholder="Enter course name"
                           maxlength="255" />
                  </div>
                </div>
              </div>

              <!-- NAVIGATION -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" id="prevBtn" class="btn btn-outline-secondary">
                  <i class="fas fa-arrow-left me-1 text-danger"></i> Previous
                </button>
                <button type="button" id="nextBtn" class="btn btn-outline-primary">
                  Next <i class="fas fa-arrow-right ms-1 text-danger"></i>
                </button>
                <button type="submit" id="submitBtn" class="btn btn-success d-none">
                  Submit <i class="fas fa-paper-plane ms-1 text-danger"></i>
                </button>
              </div>
            </form>

            <div class="text-center mt-4 text-muted small">
              &copy; 2025 Exam Management Portal
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Dynamically wire up the Back link
    document.addEventListener('DOMContentLoaded', () => {
      const backBtn = document.getElementById('btnBack');
      const tokenType = sessionStorage.getItem('token_type');
      // pick admin or student
      const url = tokenType === 'admin'
        ? '/admin/dashboard'
        : '/student/dashboard';
      backBtn.setAttribute('href', url);
});

    document.addEventListener('DOMContentLoaded', () => {
      const steps = [...document.querySelectorAll('.step')];
      const progress = document.getElementById('progressBar');
      const titleEl  = document.getElementById('formTitle');
      const submitBtn= document.getElementById('submitBtn');
      let current   = 0;
      let isEdit    = false;

      function showStep(i) {
        steps.forEach((s, idx) => s.classList.toggle('d-none', idx !== i));
        document.getElementById('prevBtn').disabled = (i === 0);
        document.getElementById('nextBtn').classList.toggle('d-none', i === steps.length - 1);
        submitBtn.classList.toggle('d-none', i !== steps.length - 1);

        // On the last step, adjust button label
        if (i === steps.length - 1) {
          submitBtn.textContent = isEdit ? 'Update Profile' : 'Register';
          submitBtn.innerHTML += ' <i class="fas fa-paper-plane ms-1 text-danger"></i>';
        }
        progress.style.width = `${((i + 1) / steps.length) * 100}%`;
      }

      // Eye-toggle for password
      document.getElementById('togglePassword').addEventListener('click', () => {
        const pwd = document.getElementById('password');
        const icon = document.querySelector('#togglePassword i');
        if (pwd.type === 'password') {
          pwd.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          pwd.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });

      // Prefill via /api/me
      const token = sessionStorage.getItem('token');
      const tokenType = sessionStorage.getItem('token_type');
      if (token && tokenType !== 'admin') {
        fetch('/api/me', { headers: { 'Authorization': `Bearer ${token}`} })
          .then(r => r.json())
          .then(json => {
            if (json.success && json.data.user_data) {
              isEdit = true;
              titleEl.textContent = 'Complete Profile';
              submitBtn.innerHTML = 'Update Profile <i class="fas fa-paper-plane ms-1 text-danger"></i>';

              const u = json.data.user_data;
              Object.keys(u).forEach(k => {
                const el = document.getElementById(k);
                if (el) el.value = u[k];
              });
              if (u.email) document.getElementById('email').readOnly = true;
              if (u.phone) document.getElementById('phone').readOnly = true;
              if (u.identity_type) {
                document.getElementById('identity_details_wrap').classList.remove('d-none');
              }
            }
          })
          .catch(() => { /* ignore */ });
      }

      // Next / Prev
      document.getElementById('nextBtn').onclick = () => {
        const inv = steps[current].querySelector(':invalid');
        if (inv) { inv.focus(); return; }
        if (current < steps.length - 1) showStep(++current);
      };
      document.getElementById('prevBtn').onclick = () => {
        if (current > 0) showStep(--current);
      };

      // Identity details toggle
      document.getElementById('identity_type')?.addEventListener('change', e => {
        document.getElementById('identity_details_wrap')
          .classList.toggle('d-none', !e.target.value);
      });

      // Form submit
      document.getElementById('multiStepForm').addEventListener('submit', async e => {
        e.preventDefault();
        const data = {};
        new FormData(e.target).forEach((v, k) => { if (v) data[k] = v; });

        // Show loading state
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Submitting…';

        try {
          const res = await fetch('/api/student/register', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
          });
          const json = await res.json();
          if (!res.ok) throw json;

          Swal.fire({
            icon: 'success',
            title: isEdit ? 'Profile Updated' : 'Registered',
            text: json.message,
            timer: 1500,
            showConfirmButton: false
          }).then(() => window.location.href = '/student/dashboard');

        } catch(err) {
          document.getElementById('flash-messages').innerHTML = `
            <div class="alert alert-danger">
              ${err.errors
                ? Object.values(err.errors).flat().join('<br>')
                : err.message || 'Submission failed'}
            </div>`;
        } finally {
          // Restore button
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHTML;
        }
      });

      showStep(current);
    });
  </script>
</body>
</html>
