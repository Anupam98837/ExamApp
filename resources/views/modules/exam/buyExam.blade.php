<!DOCTYPE html>
<!-- resources/views/exam/buyExam.blade.php -->
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Purchase Exam</title>

  <!-- Bootstrap & FontAwesome -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    rel="stylesheet"
  >
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}" />
  <link rel="stylesheet" href="{{ asset('css/pages/exam/buyExam.css') }}" />
</head>
<body class="bg-light">

  <!-- FULL-SCREEN LOADING OVERLAY -->
  <div
    id="loadingOverlay"
    class="d-flex align-items-center justify-content-center"
    style="position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(255,255,255,0.8); z-index:1050;"
  >
    <div class="text-center">
      <div
        class="spinner-grow text-primary"
        style="width:4rem; height:4rem;"
        role="status"
      ></div>
      <p class="mt-3 fs-5 text-secondary">Loading exam details…</p>
    </div>
  </div>

  <!-- ERROR MESSAGE -->
  <div id="errorMsg" class="container py-5" style="display:none;">
    <div class="alert alert-danger text-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <span id="errorText">Failed to load exam.</span>
    </div>
  </div>

  <!-- EXAM DETAILS (hidden until loaded) -->
  <div
    id="examContainer"
    class="d-none min-vh-100 d-flex align-items-center justify-content-center"
  >
    <div class="container d-flex justify-content-center">
      <div class="card-wrapper p-4 mx-auto">
        <div class="row g-4">
          <!-- IMAGE -->
          <div class="col-md-6 text-center">
            <div class="exam-image mb-3" id="examImageContainer">
              <i class="fas fa-book-open default-exam-img fa-3x text-secondary"></i>
            </div>
          </div>
          <!-- DETAILS -->
          <div class="col-md-6">
            <h1 id="examName" class="mb-3 exam-title"></h1>
            <p id="examDescription" class="text-muted mb-3"></p>
            <ul class="stats-list mb-4 list-unstyled">
              <li class="mb-2">
                <i class="fas fa-graduation-cap me-1"></i>
                <strong>Course:</strong>
                <span id="examCourse"></span>
              </li>
              <li class="mb-2">
                <i class="fas fa-clock me-1"></i>
                <strong>Duration:</strong>
                <span id="examDuration"></span> min
              </li>
              <li class="mb-2">
                <i class="fas fa-question-circle me-1"></i>
                <strong>Questions:</strong>
                <span id="examQuestionCount"></span>
              </li>
              <li>
                <i class="fas fa-users me-1"></i>
                <strong>Students:</strong>
                <span id="examStudentCount"></span>
              </li>
            </ul>
            <div class="pricing-section mb-4">
              <div id="examRegularPrice" class="original-price"></div>
              <div id="examSalePrice" class="current-price"></div>
              <div id="examDiscountPercent" class="discount-badge"></div>
            </div>

            <!-- PAY NOW -->
            <button
              type="button"
              id="payBtn"
              class="btn btn-success btn-lg w-100 modern-btn"
            >
              <i class="fas fa-shopping-cart me-2"></i>Pay Now
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS + Logic -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const params = new URLSearchParams(window.location.search);
      const examId = params.get('exam_id');
      if (!examId) return showError('No exam specified.');

      fetch(`/api/exam/${examId}/payment-info`, {
        headers: {
          'Accept': 'application/json',
          'X-User-Role': 'student',
          'Authorization': `Bearer ${sessionStorage.getItem('token')||''}`
        }
      })
      .then(r => r.json().then(json => ({ ok: r.ok, json })))
      .then(({ ok, json }) => {
        if (!ok || !json.success) throw new Error(json.message || 'Could not load exam.');
        renderExam(json.exam, examId);
      })
      .catch(err => showError(err.message));
    });

    function renderExam(exam, examId) {
      // remove loading overlay
      const overlay = document.getElementById('loadingOverlay');
      if (overlay) overlay.remove();

      // show exam container
      const cont = document.getElementById('examContainer');
      cont.classList.remove('d-none');

      // populate fields
      document.getElementById('examName').innerText = exam.examName;
      document.getElementById('examDescription').innerHTML = exam.examDescription || 'No description.';

      // image handling
      const imgC = document.getElementById('examImageContainer');
      if (exam.examImg) {
        const src = exam.examImg.startsWith('http') ? exam.examImg : `/${exam.examImg}`;
        imgC.innerHTML = `<img src="${src}" class="img-fluid rounded" alt="Exam Image">`;
      }

      // stats
      document.getElementById('examCourse').innerText        = exam.associated_course || 'N/A';
      document.getElementById('examDuration').innerText      = exam.totalTime       || '0';
      document.getElementById('examQuestionCount').innerText = exam.question_count  || '0';
      document.getElementById('examStudentCount').innerText  = exam.student_count   || '0';

      // pricing
      const regular  = parseFloat(exam.regular_price)   || 0;
      const sale     = parseFloat(exam.sale_price)      || regular;
      const discount = parseInt(exam.discount_percent)  || 0;
      document.getElementById('examRegularPrice').innerText    = regular  > 0 ? `₹${regular.toFixed(2)}` : '';
      document.getElementById('examSalePrice').innerText       = `₹${sale.toFixed(2)}`;
      document.getElementById('examDiscountPercent').innerText = discount > 0 ? `${discount}% off` : '';

      // pay button
      document.getElementById('payBtn').onclick = () => {
        alert("process started")
        // window.location.href = `/buy-exam/checkout?exam_id=${encodeURIComponent(examId)}`;
      };
    }

    function showError(msg) {
      const overlay = document.getElementById('loadingOverlay');
      if (overlay) overlay.remove();
      document.getElementById('errorText').innerText = msg;
      document.getElementById('errorMsg').style.display = 'block';
    }
  </script>
</body>
</html>
