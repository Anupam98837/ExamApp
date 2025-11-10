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

  <!-- Dynamic Remaining-Attempts Banner -->
  <div class="container py-2" id="attemptAlert" style="display: none;"></div>

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
          <h5 class="modal-title fw-bold">
            <i class="fas fa-phone me-2 text-primary"></i>Enter Phone Number
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="phoneLoginForm">
            <div class="mb-4">
              <label for="phoneNumber" class="form-label fw-medium">Phone Number</label>
              <div class="input-group">
                <span class="input-group-text bg-primary text-white">
                  <i class="fas fa-phone"></i>
                </span>
                <input type="tel" id="phoneNumber" class="form-control form-control-lg modern-input" required placeholder="Enter your phone number">
              </div>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg modern-btn">
                <i class="fas fa-arrow-right me-2"></i>Continue
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Container -->
  <div class="container py-5" id="examContainer" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="text-muted d-none d-md-block calendar-date-wrapper">
        <i class="far fa-calendar-alt me-2"></i>
        <span id="currentDate"></span>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3 mb-4 border-end pe-md-4">
        <div class="list-group" id="courseSidebar">
          <a href="#" class="list-group-item list-group-item-action active" data-course="all">All Courses</a>
        </div>
      </div>
      <div class="col-md-9 ps-md-4">
        <div id="examScrollArea" class="position-relative">
          <div class="row g-4" id="examCardsRow"></div>
          <div class="text-center my-5" id="viewMoreContainer" style="display:none;">
            <button class="btn btn-outline-primary btn-lg px-5" id="viewMoreBtn">
              <i class="fas fa-plus me-2"></i>View More Exams
              <span class="badge bg-primary ms-2" id="remainingCount">0</span>
            </button>
            <div class="text-center mt-4" id="loadingMore" style="display:none;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading more exams...</span>
              </div>
              <p class="mt-2 text-muted">Loading more exams...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // --- Globals ---
    let allExams = [], displayedExams = [], currentPage = 0;
    const examsPerPage = 6;
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    let freeExamAttempts = 0, currentAttemptCount = 0, remainingAttempts = null;
    let pendingExam = null;

    const isStudentLoggedIn = () => !!sessionStorage.getItem('token');
    const storeStudentToken = (t, type, id) => {
      sessionStorage.setItem('token', t);
      sessionStorage.setItem('token_type', type);
      sessionStorage.setItem('student_id', id);
    };

    async function fetchRemainingAttempts() {
      const studentId = sessionStorage.getItem('student_id');
      if (!studentId) return null;
      const resp = await fetch('/api/student/check-attempt', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ student_id: studentId })
      });
      const json = await resp.json();
      if (!resp.ok) return null;
      freeExamAttempts    = json.free_exam_attempts;
      currentAttemptCount = json.current_attempt_count;
      remainingAttempts   = json.remaining_attempts;
      return remainingAttempts;
    }

    function renderAttemptBanner() {
      const c = document.getElementById('attemptAlert');
      if (remainingAttempts === 'infinite') {
        c.style.display = 'none'; return;
      }
      if (typeof remainingAttempts === 'number' && remainingAttempts > 0) {
        c.innerHTML = `
          <div class="alert alert-info text-center mb-0">
            You have <strong>${remainingAttempts}</strong> free attempt${remainingAttempts>1?'s':''}.
            ${remainingAttempts===1 && freeExamAttempts===1
              ? 'Complete your profile to get <strong>3 more</strong> free attempts.'
              : ''}
          </div>`; c.style.display = 'block'; return;
      }
      let extra = '';
      if (freeExamAttempts === 1) {
        extra = ` Complete your profile to get more free attempts:
                 <a href="/student/register" class="alert-link">Register Now</a>.`;
      }
      c.innerHTML = `
        <div class="alert alert-warning text-center mb-0">
          You have used <strong>${currentAttemptCount}</strong> of 
          <strong>${freeExamAttempts}</strong> free attempt${freeExamAttempts>1?'s':''}.${extra}
        </div>`; c.style.display = 'block';
    }

    function createExamCard(exam) {
      let badge = '';
      if (isStudentLoggedIn()) {
        badge = (remainingAttempts === 0)
          ? `<span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="Complete profile or subscribe for unlimited attempts">Paid</span>`
          : `<span class="badge bg-success" data-bs-toggle="tooltip" title="You have ${remainingAttempts} free attempt${remainingAttempts > 1 ? 's' : ''} remaining">Free</span>`;
      }
      let actionHtml = '';
      if (!isStudentLoggedIn()) {
        actionHtml = `<button class="btn btn-sm btn-primary start-exam-btn" data-exam-id="${exam.id}"><i class="fas fa-play me-1"></i>Start</button>`;
      } else if (remainingAttempts === 0) {
        actionHtml = `<button class="btn btn-sm btn-success buy-now-btn" data-exam-id="${exam.id}">Buy Now</button>`;
      } else {
        actionHtml = `<button class="btn btn-sm btn-primary start-exam-btn" data-exam-id="${exam.id}"><i class="fas fa-play me-1"></i>Start</button>`;
      }
      return `
        <div class="col-lg-4 col-md-6">
          <div class="card h-100 shadow-sm border-0 exam-card" data-exam-duration="${exam.totalTime||0}">
            <div class="card-body d-flex flex-column">
              <img src="{{ asset('') }}${exam.examImg}" class="card-img-top exam-card-img" alt="${exam.examName}">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="card-title mb-0">${exam.examName}</h5>${badge}
              </div>
              <p class="card-text text-muted mb-4">${exam.examDescription?.substring(0,100)||'No description'}${exam.examDescription?.length>100?'...':''}</p>
              <p class="text-muted mb-4"><strong>Course:</strong> ${exam.associated_course || 'N/A'}</p>
              <div class="mt-auto d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="far fa-clock me-1"></i>${exam.totalTime||'N/A'} min</small>
                <div>${actionHtml}</div>
              </div>
            </div>
          </div>
        </div>`;
    }

    function addExamEventListeners() {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
      document.querySelectorAll('.start-exam-btn').forEach(btn =>
        btn.addEventListener('click', e => {
          const id  = e.currentTarget.dataset.examId;
          const dur = e.currentTarget.closest('.exam-card').dataset.examDuration;
          handleStartExamAction(id, dur);
        })
      );
      document.querySelectorAll('.buy-now-btn').forEach(btn =>
        btn.addEventListener('click', e =>
          window.location.href = `/buy-exam?exam_id=${e.currentTarget.dataset.examId}`
        )
      );
    }

    function renderCards(exams, append=false) {
      const row = document.getElementById('examCardsRow');
      if (!append) row.innerHTML = '';
      if (exams.length === 0 && !append) {
        row.innerHTML = `
          <div class="col-12">
            <div class="card shadow-sm border-0">
              <div class="card-body text-center py-5">
                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No exams available</h5>
              </div>
            </div>
          </div>`;
        return;
      }
      const html = exams.map(createExamCard).join('');
      append ? row.insertAdjacentHTML('beforeend', html) : row.innerHTML = html;
      addExamEventListeners();
      updateViewMoreButton();
    }

    function updateViewMoreButton() {
      const rem = allExams.length - displayedExams.length;
      document.getElementById('remainingCount').textContent = rem;
      document.getElementById('viewMoreContainer').style.display = rem > 0 ? 'block' : 'none';
    }
    function loadMoreExams() {
      document.getElementById('loadingMore').style.display = 'block';
      document.getElementById('viewMoreBtn').disabled = true;
      setTimeout(() => {
        const start = currentPage * examsPerPage;
        const next  = allExams.slice(start, start + examsPerPage);
        displayedExams = [...displayedExams, ...next];
        currentPage++;
        renderCards(next, start > 0);
        document.getElementById('loadingMore').style.display = 'none';
        document.getElementById('viewMoreBtn').disabled = false;
      }, 500);
    }
    function initializePagination(exams) {
      allExams = exams;
      displayedExams = [];
      currentPage = 0;
      loadMoreExams();
    }

    async function handleStartExamAction(id, duration) {
      if (!isStudentLoggedIn()) {
        pendingExam = { id, duration };
        loginModal.show();
        return;
      }
      const ok = await fetchRemainingAttempts();
      if (!ok || (typeof remainingAttempts === 'number' && remainingAttempts <= 0)) return;
      localStorage.setItem('examId', id);
      localStorage.setItem('examDuration', duration);
      localStorage.setItem('examTimeLeft', duration * 60);
      localStorage.setItem('examAnswers', JSON.stringify({}));
      localStorage.setItem('examReviews', JSON.stringify({}));
      localStorage.setItem('examVisited', JSON.stringify({}));
      document.cookie = `exam_id=${id}; path=/; max-age=86400; SameSite=Lax`;
      window.location.href = `/exam/start?exam_id=${id}`;
    }

    async function fetchExams() {
      try {
        const resp = await fetch('/api/exam/suggested', {
          headers: {
            'Accept': 'application/json',
            'X-User-Role': 'student',
            'Authorization': `Bearer ${token}`
          }
        });
        const json = await resp.json();
        if (!resp.ok || !json.success) throw new Error(json.message || 'Failed to load exams');
        const published = json.exams.filter(e => e.is_public === 'yes');

        const courses = Array.from(new Set(published.map(e => e.associated_course).filter(c => c))).sort();
        const sidebar = document.getElementById('courseSidebar');
        courses.forEach(c => {
          const a = document.createElement('a');
          a.href = '#';
          a.className = 'list-group-item list-group-item-action';
          a.dataset.course = c;
          a.innerHTML = `${c}<i class="fas fa-angle-right me-2"></i>`;
          sidebar.appendChild(a);
        });
        sidebar.querySelectorAll('a').forEach(a =>
          a.addEventListener('click', e => {
            e.preventDefault();
            sidebar.querySelectorAll('a').forEach(x => x.classList.remove('active'));
            e.currentTarget.classList.add('active');
            const sel = e.currentTarget.dataset.course;
            initializePagination(sel === 'all'
              ? published
              : published.filter(ex => ex.associated_course === sel)
            );
          })
        );

        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('examContainer').style.display    = 'block';

        if (isStudentLoggedIn()) {
          await fetchRemainingAttempts();
          renderAttemptBanner();
        }
        initializePagination(published);

      } catch (err) {
        document.getElementById('loadingOverlay').innerHTML = `
          <div class="loading-content text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <p class="loading-text text-danger">${err.message}</p>
            <button onclick="window.location.reload()" class="btn btn-primary modern-btn mt-3">
              <i class="fas fa-sync-alt me-2"></i>Try Again
            </button>
          </div>`;
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('currentDate').textContent =
        new Date().toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});
      document.getElementById('viewMoreBtn').addEventListener('click', loadMoreExams);
      fetchExams();
    });

    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    document.getElementById('phoneLoginForm').addEventListener('submit', async e => {
      e.preventDefault();
      const phone = document.getElementById('phoneNumber').value.trim();
      try {
        const resp = await fetch('/api/student/login', {
          method: 'POST',
          headers: { 'Content-Type':'application/json','Accept':'application/json' },
          body: JSON.stringify({ login: phone })
        });
        const res = await resp.json();
        if (!resp.ok || !res.success) throw new Error(res.message || 'Login failed');

        storeStudentToken(res.student_token, res.tokenable_type, res.student_id);
        loginModal.hide();

        // DIRECTLY START EXAM if pending and attempts remain
        if (pendingExam && (res.remaining_attempts === 'infinite' || res.remaining_attempts > 0)) {
          const { id, duration } = pendingExam;
          localStorage.setItem('examId', id);
          localStorage.setItem('examDuration', duration);
          localStorage.setItem('examTimeLeft', duration * 60);
          localStorage.setItem('examAnswers', JSON.stringify({}));
          localStorage.setItem('examReviews', JSON.stringify({}));
          localStorage.setItem('examVisited', JSON.stringify({}));
          document.cookie = `exam_id=${id}; path=/; max-age=86400; SameSite=Lax`;
          window.location.href = `/exam/start?exam_id=${id}`;
          return;
        }

        // otherwise just refresh UI
        await fetchRemainingAttempts();
        renderAttemptBanner();
        renderCards(displayedExams);

      } catch (err) {
        alert(err.message);
      }
    });
  </script>
</body>
</html>
