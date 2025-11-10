<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $exam->examName ?? 'Exam Details' }}</title>

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    rel="stylesheet"
  />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}" />
  <link
    href="{{ asset('css/pages/exam/examDetails.css') }}"
    rel="stylesheet"
  />
</head>
<body>
  <!-- Loading Spinner -->
  <div id="loadingSpinner" class="loading-overlay">
    <div class="loading-content">
      <div
        class="spinner-grow text-primary mb-3"
        style="width: 3rem; height: 3rem;"
      ></div>
      <div
        class="spinner-grow text-secondary mb-3 ms-2"
        style="width: 2.5rem; height: 2.5rem; animation-delay: 0.1s;"
      ></div>
      <div
        class="spinner-grow text-accent mb-3 ms-2"
        style="width: 2rem; height: 2rem; animation-delay: 0.2s;"
      ></div>
      <p class="loading-text">Loading exam details…</p>
    </div>
  </div>

  <!-- Login Modal -->
  <div
    class="modal fade"
    id="loginModal"
    tabindex="-1"
    aria-labelledby="loginModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modern-modal">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="loginModalLabel">
            <i class="fas fa-phone me-2 text-primary"></i>Enter Phone Number
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
          ></button>
        </div>
        <div class="modal-body">
          <form id="phoneLoginForm">
            <div class="mb-4">
              <label
                for="phoneNumber"
                class="form-label fw-medium"
                >Phone Number</label
              >
              <div class="input-group">
                <span class="input-group-text bg-primary text-white">
                  <i class="fas fa-phone"></i>
                </span>
                <input
                  type="tel"
                  id="phoneNumber"
                  class="form-control form-control-lg modern-input"
                  required
                  placeholder="Enter your phone number"
                />
              </div>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary btn-lg modern-btn" type="submit">
                <i class="fas fa-arrow-right me-2"></i>Continue
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid" id="examContainer" style="display: none;">
    <!-- Breadcrumb -->
    <div class="row mb-4">
      <div class="col-12 bg-white rounded shadow-sm">
        <nav aria-label="breadcrumb" class="modern-breadcrumb">
          <ol class="breadcrumb mb-0 py-3 px-4">
            <li class="breadcrumb-item">
              <a
                href="#"
                onclick="history.back();"
                class="text-decoration-none text-dark"
                ><i class="fas fa-arrow-left me-2"></i>Back</a
              >
            </li>
            <li
              class="breadcrumb-item active fw-medium text-primary"
              aria-current="page"
              id="breadcrumbExamName"
            ></li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4 py-4 mb-4">
      <!-- Left Column -->
      <div class="col-xl-4 col-lg-5">
        <div class="exam-card glass-card h-100">
          <div class="exam-image-wrapper">
            <div id="examImageContainer" class="exam-image">
              <div class="image-overlay">
                <i class="fas fa-book-open float-pulse"></i>
              </div>
            </div>
            <div class="image-badges">
              <div id="statusBadge" class="badge-container"></div>
              <div id="pricingBadge" class="badge-container"></div>
            </div>
          </div>
          <div class="card-body p-4">
            <div id="pricingInfo" class="pricing-section mb-4"></div>
            <div class="action-buttons">
              <!-- initially hidden, until checks complete -->
              <button
                id="startExamBtn"
                class="btn btn-primary btn-lg w-100 modern-btn mb-3"
                style="display: none;"
              >
                <i class="fas fa-play-circle me-2"></i>Start Exam
                <span class="btn-ripple"></span>
              </button>
              <div
                id="attemptsInfo"
                class="attempts-warning text-danger mb-3"
                style="display: none;"
              ></div>
              <button
                id="purchaseBtn"
                class="btn btn-success btn-lg w-100 modern-btn mb-3"
                style="display: none;"
              >
                <i class="fas fa-shopping-cart me-2"></i>Buy Exam
                <span class="btn-ripple"></span>
              </button>
              <button
                id="subscribeBtn"
                class="btn btn-warning btn-lg w-100 modern-btn"
                style="display: none;"
              >
                <i class="fas fa-star me-2"></i>Get Subscription
                <span class="btn-ripple"></span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-xl-8 col-lg-7">
        <div class="exam-details-card glass-card h-100">
          <div class="card-body p-4">
            <div class="exam-header mb-4">
              <h1 class="exam-title" id="examName"></h1>
              <div class="title-underline"></div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats mb-4">
              <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                  <div class="stat-item">
                    <i class="fas fa-calendar-alt stat-icon"></i>
                    <div>
                      <div class="stat-label">Created</div>
                      <div class="stat-value" id="createdDate"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="stat-item">
                    <i class="fas fa-clock stat-icon"></i>
                    <div>
                      <div class="stat-label">Duration</div>
                      <div class="stat-value" id="examDuration"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="stat-item">
                    <i class="fas fa-clipboard-list stat-icon"></i>
                    <div>
                      <div class="stat-label">Questions</div>
                      <div class="stat-value" id="totalQuestions"></div>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 col-sm-6">
                  <div class="stat-item">
                    <i class="fas fa-redo stat-icon"></i>
                    <div>
                      <div class="stat-label">Attempts</div>
                      <div class="stat-value" id="totalAttempts"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Result Release Info -->
            <div class="info-alert mb-4">
              <i class="fas fa-info-circle alert-icon"></i>
              <div class="alert-content">
                <strong>Result Release:</strong>
                <span id="resultRelease"></span>
              </div>
            </div>

            <!-- Description / Instructions Tabs -->
            <div class="content-tabs">
              <ul
                class="nav nav-pills custom-nav-pills mb-4"
                id="contentTabs"
                role="tablist"
              >
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
                    id="description-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#description"
                    type="button"
                    role="tab"
                  >
                    <i class="fas fa-align-left me-2"></i>Description
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="instructions-tab"
                    data-bs-toggle="pill"
                    data-bs-target="#instructions"
                    type="button"
                    role="tab"
                  >
                    <i class="fas fa-list-ol me-2"></i>Instructions
                  </button>
                </li>
              </ul>
              <div class="tab-content" id="contentTabsContent">
                <div
                  class="tab-pane fade show active"
                  id="description"
                  role="tabpanel"
                >
                  <div
                    class="content-box"
                    id="examDescription"
                  ></div>
                </div>
                <div class="tab-pane fade" id="instructions" role="tabpanel">
                  <div
                    class="content-box"
                    id="examInstructions"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Associated Info -->
    <div class="row g-4 mt-2">
      <div class="col-md-6">
        <div class="info-card glass-card">
          <div class="card-body p-4">
            <i class="fas fa-book info-icon"></i>
            <h5 class="info-title">Associated Course</h5>
            <p class="info-content" id="associatedCourse">Not specified</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="info-card glass-card">
          <div class="card-body p-4">
            <i class="fas fa-building info-icon"></i>
            <h5 class="info-title">Associated Department</h5>
            <p class="info-content" id="associatedDepartment">Not specified</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentExam = null;
    let studentEligible = false;
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');

    function isStudentLoggedIn() {
      return !!sessionStorage.getItem("token");
    }
    function storeStudentToken(t, ty, id) {
      sessionStorage.setItem("token", t);
      sessionStorage.setItem("token_type", ty);
      sessionStorage.setItem("student_id", id);
    }

    async function checkFreeAttempts() {
      const resp = await fetch("/api/student/check-attempt", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
         'Authorization':`Bearer ${token}`
        },
        body: JSON.stringify({
          student_id: sessionStorage.getItem("student_id"),
        }),
      });
      if (resp.status === 403 || resp.status === 401) {
            return window.location.href = '/unauthorized';
          }
      const json = await resp.json();
      return (
        resp.ok &&
        json.success &&
        (json.remaining_attempts === "infinite" || json.remaining_attempts > 0)
      );
    }

    function updateActionButtons() {
      const startBtn = document.getElementById("startExamBtn");
      const purchaseBtn = document.getElementById("purchaseBtn");
      const subscribeBtn = document.getElementById("subscribeBtn");
      const attemptsInfo = document.getElementById("attemptsInfo");

      // Paid
      if (currentExam.pricing_model === "paid") {
        startBtn.style.display = "none";
        purchaseBtn.style.display = "block";
        subscribeBtn.style.display = "block";
        attemptsInfo.style.display = "none";
        return;
      }
      purchaseBtn.style.display = "none";

      // Free
      if (!isStudentLoggedIn()) {
        startBtn.style.display = "none";
        subscribeBtn.style.display = "block";
        attemptsInfo.style.display = "none";
        return;
      }
      if (!studentEligible) {
        startBtn.style.display = "none";
        attemptsInfo.style.display = "block";
        attemptsInfo.textContent =
          "No attempts left — please subscribe";
        subscribeBtn.style.display = "block";
      } else {
        startBtn.style.display = "block";
        attemptsInfo.style.display = "none";
        subscribeBtn.style.display = "none";
      }
    }

    function startExam() {
      localStorage.setItem("examId", currentExam.id);
      localStorage.setItem(
        "examDuration",
        currentExam.totalTime || 0
      );
      localStorage.setItem(
        "examTimeLeft",
        (currentExam.totalTime || 0) * 60
      );
      localStorage.setItem("examCurrentIndex", 0);
      if (!localStorage.getItem("examAnswers")) {
        localStorage.setItem("examAnswers", JSON.stringify({}));
        localStorage.setItem("examReviews", JSON.stringify({}));
        localStorage.setItem("examVisited", JSON.stringify({}));
      }
      document.cookie = `exam_id=${currentExam.id};path=/;max-age=86400;SameSite=Lax`;
      window.location.href = `/exam/start?exam_id=${currentExam.id}`;
    }

    document
      .getElementById("startExamBtn")
      .addEventListener("click", () => {
        if (!isStudentLoggedIn()) {
          loginModal.show();
          return;
        }
        if (studentEligible) startExam();
      });
    document
      .getElementById("purchaseBtn")
      .addEventListener("click", () => alert("Redirect to purchase"));
    document
      .getElementById("subscribeBtn")
      .addEventListener("click", () => {
        window.location.href = "/getsubscription";
      });

    const loginModal = new bootstrap.Modal(
      document.getElementById("loginModal")
    );
    document
      .getElementById("phoneLoginForm")
      .addEventListener("submit", async (e) => {
        e.preventDefault();
        const phone = document.getElementById("phoneNumber").value.trim();
        const resp = await fetch("/api/student/login", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({ login: phone }),
        });
        const res = await resp.json();
        if (!resp.ok || !res.success) {
          alert(res.message || "Login failed");
          return;
        }
        storeStudentToken(
          res.student_token,
          res.tokenable_type,
          res.student_id
        );
        loginModal.hide();
        studentEligible = await checkFreeAttempts();
        updateActionButtons();
      });

    async function fetchExamDetails() {
      // fetch attempts first
      if (isStudentLoggedIn()) {
        studentEligible = await checkFreeAttempts();
      }
      // then exam
      const params = new URLSearchParams(window.location.search);
      const id = params.get("exam_id");
      try {
        if (!id) throw new Error("Exam ID not specified");
        const resp = await fetch(`/api/exam/${id}`, {
          headers: { Accept: "application/json",'Authorization':`Bearer ${token}`        },
        });
        const json = await resp.json();
        if (resp.status === 403 || resp.status === 401) {
            return window.location.href = '/unauthorized';
          }
        if (!resp.ok || !json.success)
          throw new Error(json.message || "Failed to load exam details");
        currentExam = json.exam;

        // populate UI...
        document.getElementById("breadcrumbExamName").textContent =
          currentExam.examName;
        document.getElementById("examName").textContent =
          currentExam.examName;
        document.getElementById("examDescription").innerHTML =
          currentExam.examDescription ||
          "<em class='text-muted'>No description available</em>";
        document.getElementById("examInstructions").innerHTML =
          currentExam.Instructions ||
          "<em class='text-muted'>No instructions available</em>";
        document.getElementById(
          "createdDate"
        ).textContent = new Date(currentExam.created_at).toLocaleDateString();
        document.getElementById(
          "examDuration"
        ).textContent = currentExam.totalTime
          ? `${currentExam.totalTime} min`
          : "Not specified";
        document.getElementById(
          "totalQuestions"
        ).textContent = currentExam.total_questions || "Not specified";
        document.getElementById(
          "totalAttempts"
        ).textContent = currentExam.total_attempts || "Unlimited";
        document.getElementById("resultRelease").textContent =
          currentExam.result_set_up_type === "Schedule" &&
          currentExam.result_release_date
            ? `Scheduled for ${new Date(
                currentExam.result_release_date
              ).toLocaleDateString()}`
            : "Immediately after completion";
        document.getElementById(
          "associatedCourse"
        ).textContent = currentExam.associated_course || "Not specified";
        document.getElementById(
          "associatedDepartment"
        ).textContent =
          currentExam.associated_department || "Not specified";
        document.getElementById("statusBadge").innerHTML =
          currentExam.is_public === "yes"
            ? '<span class="status-badge published">Published</span>'
            : '<span class="status-badge draft">Draft</span>';
        document.getElementById("pricingBadge").innerHTML =
          currentExam.pricing_model === "paid"
            ? '<span class="pricing-badge paid">Paid</span>'
            : '<span class="pricing-badge free">Free</span>';
        const pi = document.getElementById("pricingInfo");
        if (currentExam.pricing_model === "paid") {
          pi.innerHTML = `<div class="pricing-display">
            <div class="current-price">$${currentExam.sale_price ||
              currentExam.regular_price}</div>
            ${
              currentExam.sale_price
                ? `<div class="original-price">$${currentExam.regular_price}</div>`
                : ""
            }
          </div>`;
          document.getElementById("purchaseBtn").style.display = "block";
          document.getElementById("subscribeBtn").style.display = "block";
        } else {
          pi.innerHTML = `<div class="pricing-display">
            <div class="current-price free-price">Free</div>
          </div>`;
        }
        if (currentExam.examImg) {
          const imgEl = document.createElement("img");
          imgEl.src = `/${currentExam.examImg}`;
          imgEl.className = "exam-header-img";
          imgEl.onerror = () => {
            document.getElementById("examImageContainer").innerHTML =
              '<div class="image-overlay"><i class="fas fa-book-open float-pulse"></i></div>';
          };
          const container = document.getElementById("examImageContainer");
          container.innerHTML = "";
          container.append(imgEl);
        }

        // finally, show container and buttons
        document.getElementById("loadingSpinner").style.display = "none";
        document.getElementById("examContainer").style.display = "block";
        updateActionButtons();
        if (currentExam.pricing_model === "free" && studentEligible) {
          document.getElementById("startExamBtn").style.display = "block";
        }
      } catch (err) {
        document.getElementById("loadingSpinner").innerHTML = `
          <div class="loading-content">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3 float-pulse"></i>
            <p class="loading-text text-danger">${err.message}</p>
            <a href="/exams" class="btn btn-primary modern-btn mt-3">Back to Exams</a>
          </div>`;
      }
    }

    fetchExamDetails();
  </script>
</body>
</html>
