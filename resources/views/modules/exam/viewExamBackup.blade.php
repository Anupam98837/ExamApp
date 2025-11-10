{{-- resources/views/exam/viewExam.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Available Exams</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}">
  <link href="{{ asset('css/pages/exam/viewExam.css') }}" rel="stylesheet">
</head>
<body class="bg-light">

  <!-- Free Attempt Banner -->
  <div class="container" id="attemptAlert" style="display:none;"></div>

  <!-- Loading Spinner -->
  <div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content text-center">
      <div class="spinner-grow text-primary mb-3" style="width:3rem; height:3rem;"></div>
      <p class="loading-text">Loading exams...</p>
    </div>
  </div>

  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modern-modal">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold"><i class="fas fa-phone me-2 text-primary"></i>Enter Phone Number</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="phoneLoginForm">
            <div class="mb-4">
              <label for="phoneNumber" class="form-label fw-medium">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text bg-primary text-white"><i class="fas fa-phone"></i></span>
                <input type="tel" id="phoneNumber" class="form-control form-control-lg modern-input" required placeholder="Enter your phone number">
              </div>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg modern-btn"><i class="fas fa-arrow-right me-2"></i>Continue</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Container -->
  <div class="container" id="examContainer" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="text-muted d-none d-md-block calendar-date-wrapper">
        <i class="far fa-calendar-alt me-2"></i><span id="currentDate"></span>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3 mb-4 border-end pe-md-4">
        <div class="list-group" id="courseSidebar">
          <a href="#" class="list-group-item list-group-item-action active" data-course="all">
            All Courses <i class="fas fa-angle-right float-end"></i>
          </a>
        </div>
      </div>
      <div class="col-md-9 ps-md-4">
        <div class="row g-4" id="examCardsRow"></div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let allExams = [];
    const tokenKey = 'token';
    let pendingExam = null;
    let remainingAttempts = null;
    let freeExamAttempts = null;
    let currentAttemptCount = null;


    const isStudentLoggedIn = () => !!sessionStorage.getItem(tokenKey);
    const storeStudentToken = (t, type, id) => {
      sessionStorage.setItem(tokenKey, t);
      sessionStorage.setItem('token_type', type);
      sessionStorage.setItem('student_id', id);
    };

    async function fetchRemainingAttempts() {
      const studentId = sessionStorage.getItem('student_id');
      if (!studentId) return;
      const resp = await fetch('/api/student/check-attempt', {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'Accept':'application/json',
          'Authorization': `Bearer ${sessionStorage.getItem(tokenKey)}`
        },
        body: JSON.stringify({ student_id: studentId })
      });
      const json = await resp.json();
      if (!resp.ok) return;
      remainingAttempts = json.remaining_attempts;
      freeExamAttempts = json.free_exam_attempts;
      currentAttemptCount = json.current_attempt_count;
      renderAttemptBanner();
    }

    function renderAttemptBanner() {
      const c = document.getElementById('attemptAlert');
      if (!isStudentLoggedIn()) {
        c.style.display = 'none';
        return;
      }
      if (remainingAttempts > 0) {
        c.innerHTML = `<div class="alert alert-info text-center mb-0">
          You have <strong>${remainingAttempts}</strong> free attempt${remainingAttempts>1?'s':''} remaining.
        </div>`;
      } 
      else if (freeExamAttempts === 1 && remainingAttempts === 0){
        c.innerHTML = `<div class="alert alert-warning text-center mb-0">
          You have reached your free attempt limit for <strong>premium exams</strong> . <a href="/student/register" class="alert-link">Complete your profile</a>
      to get <strong>3</strong> more free attempts!
        </div>`;
      }
      else {
        c.innerHTML = `<div class="alert alert-warning text-center mb-0">
          You have reached your free attempt limit for <strong>premium exams</strong>.
        </div>`;
      }
      c.style.display = 'block';
    }

    function createExamCard(exam) {
      const badge = exam.pricing_model === 'free'
        ? `<span class="badge bg-success">Free</span>`
        : `<span class="badge bg-info">${exam.sale_price? '₹'+exam.sale_price : '₹'+exam.regular_price}</span>`;

      // Determine which button to show:
      let actionHtml;

      // Not logged in → Start (always opens login)
      if (!isStudentLoggedIn()) {
        actionHtml = `<button class="btn btn-sm btn-primary start-exam-btn"
                        data-exam-id="${exam.id}"
                        data-duration="${exam.totalTime||0}">
                        <i class="fas fa-play me-1"></i>Start
                      </button>`;

      } else {
        // Logged in:
        if (exam.pricing_model === 'free') {
          // Free always Start
          actionHtml = `<button class="btn btn-sm btn-primary start-exam-btn"
                          data-exam-id="${exam.id}"
                          data-duration="${exam.totalTime||0}">
                          <i class="fas fa-play me-1"></i>Start
                        </button>`;

        } else {
          // Premium → if attempts left (or infinite) show Start, otherwise Buy Now
          if (remainingAttempts==='infinite' || remainingAttempts>0) {
            actionHtml = `<button class="btn btn-sm btn-primary start-exam-btn"
                            data-exam-id="${exam.id}"
                            data-duration="${exam.totalTime||0}">
                            <i class="fas fa-play me-1"></i>Start
                          </button>`;
          } else {
            actionHtml = `<button class="btn btn-sm btn-warning buy-exam-btn"
                            data-exam-id="${exam.id}">
                            <i class="fas fa-shopping-cart me-1"></i>Buy Now
                          </button>`;
          }
        }
      }

      return `
        <div class="col-lg-4 col-md-6">
          <div class="card h-100 shadow-sm border-0 exam-card">
            <div class="card-body d-flex flex-column">
              <img src="{{ asset('') }}${exam.examImg}" class="card-img-top exam-card-img" alt="${exam.examName}">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="card-title mb-0">${exam.examName}</h5>
              </div>
              <p class="card-text text-muted mb-4">
                ${exam.examDescription?.substring(0,100)||'No description'}${exam.examDescription?.length>100?'...':''}
              </p>
              <p class="text-muted mb-4"><strong>Course:</strong> ${exam.associated_course||'N/A'}</p>
              <div class="mt-auto d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="far fa-clock me-1"></i>${exam.totalTime||'N/A'} min</small>
                ${(isStudentLoggedIn())?
                `${(exam.user_attempt_count >= exam.total_attempts)?
              `<div class="text-danger">${exam.user_attempt_count}/${exam.total_attempts}</div>`:`<div>${actionHtml}</div>`}
                `:`<div>${actionHtml}</div>`
                }
              </div>
            </div>
          </div>
        </div>`;
    }

    function renderCards(exams) {
      const row = document.getElementById('examCardsRow');
      row.innerHTML = exams.map(createExamCard).join('');

      document.querySelectorAll('.start-exam-btn').forEach(btn =>
        btn.addEventListener('click', () => {
          handleStartExamAction(btn.dataset.examId, btn.dataset.duration);
        })
      );
      document.querySelectorAll('.buy-exam-btn').forEach(btn =>
        btn.addEventListener('click', () => {
          window.location.href = `/buy-exam?exam_id=${btn.dataset.examId}`;
        })
      );
    }

    function handleStartExamAction(id, duration) {
      const exam = allExams.find(e => e.id == id);

      // If not logged in, pop login
      if (!isStudentLoggedIn()) {
        pendingExam = { id, duration };
        loginModal.show();
        return;
      }

      // Free always starts
      if (exam.pricing_model === 'free') {
        return startExam(id, duration);
      }

      // Premium: only start if attempts remain
      if (remainingAttempts==='infinite' || remainingAttempts>0) {
        return startExam(id, duration);
      }

      // Otherwise send to buy
      window.location.href = `/buy-exam?exam_id=${id}`;
    }

    function startExam(id, duration) {
      if (sessionStorage.getItem(tokenKey)) {
        localStorage.setItem('examId', id);
      }
      localStorage.setItem('examDuration', duration);
      localStorage.setItem('examTimeLeft', duration*60);
      localStorage.setItem('examAnswers', JSON.stringify({}));
      localStorage.setItem('examReviews', JSON.stringify({}));
      localStorage.setItem('examVisited', JSON.stringify({}));
      document.cookie = `exam_id=${id}; path=/; max-age=86400; SameSite=Lax`;
      window.location.href = `/exam/start?exam_id=${id}`;
    }

    async function fetchExams() {
      try {
        const resp = await fetch('/api/exam', {
          headers: {
            'Accept':'application/json',
            'X-User-Role':'student',
            'Authorization': `Bearer ${sessionStorage.getItem(tokenKey)||''}`
          }
        });
        const json = await resp.json();
        if (!json.success) {
          // log the API-level error
          console.error('API error fetching exams:', json.message);
          throw new Error(json.message);
        }        
        allExams = json.exams.filter(e=>e.is_public==='yes');

        // build sidebar
        const courses = [...new Set(allExams.map(e=>e.associated_course).filter(c=>c))].sort();
        const sidebar = document.getElementById('courseSidebar');
        courses.forEach(c => {
          const a = document.createElement('a');
          a.href = '#';
          a.className = 'list-group-item list-group-item-action';
          a.dataset.course = c;
          a.innerHTML = `${c} <i class="fas fa-angle-right float-end"></i>`;
          sidebar.appendChild(a);
        });
        sidebar.querySelectorAll('a').forEach(a =>
          a.addEventListener('click', e => {
            e.preventDefault();
            sidebar.querySelectorAll('a').forEach(x=>x.classList.remove('active'));
            e.currentTarget.classList.add('active');
            const sel = e.currentTarget.dataset.course;
            renderCards(sel==='all' ? allExams : allExams.filter(ex=>ex.associated_course===sel));
          })
        );

        await fetchRemainingAttempts();
        renderCards(allExams);
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('examContainer').style.display = 'block';
      } catch(err) {
        document.getElementById('loadingOverlay').innerHTML = `
          <div class="loading-content text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <p class="loading-text text-danger">${err.message}</p>
            <button onclick="location.reload()" class="btn btn-primary">Try Again</button>
          </div>`;
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US',{
        weekday:'short', month:'short', day:'numeric'
      });
      fetchExams();
    });

    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    document.getElementById('phoneLoginForm').addEventListener('submit', async e => {
      e.preventDefault();
      const phone = document.getElementById('phoneNumber').value.trim();
      const resp = await fetch('/api/student/login', {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({ login: phone })
      });
      const res = await resp.json();
      if (!res.success) return alert(res.message||'Login failed');
      storeStudentToken(res.student_token, res.tokenable_type, res.student_id);
      loginModal.hide();
      await fetchRemainingAttempts();
      if (pendingExam) {
        const { id, duration } = pendingExam;
        pendingExam = null;
        handleStartExamAction(id, duration);
      }
    });
  </script>
</body>
</html>
