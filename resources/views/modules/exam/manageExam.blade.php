<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Manage Exams</title>

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Bootstrap -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Font-Awesome -->
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      rel="stylesheet"
    />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Summernote -->
    <link
      href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css"
      rel="stylesheet"
    />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <link
      rel="stylesheet"
      href="{{ asset('css/pages/exam/manageExam.css') }}"
    />
  </head>
  <body class="bg-light">
    <div class="container-fluid">
      <div class="row">
        <main class="col-12 px-md-4 py-4">
          {{-- ===== breadcrumb + create ===== --}}
          <div class="rounded-3 p-4 mb-4">
            <div
              class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"
            >
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                  <a href="#"><i class="fas fa-file-alt me-1"></i>Exam</a>
                </li>
                <li class="breadcrumb-item active">Manage</li>
              </ol>
              <div class="d-flex align-items-center gap-3">
                <div
                  class="d-none d-sm-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 text-muted"
                >
                  <i class="far fa-calendar-alt"></i>
                  <span id="currentDate"></span>
                </div>
                <a
                  href="{{ route('exam.add') }}"
                  class="btn gradient-btn text-white hover-lift"
                >
                  <i class="fas fa-plus me-2"></i>Create New Exam
                </a>
              </div>
            </div>
            <hr />
          </div>

          {{-- ===== stats ===== --}}
          <div class="row mb-4">
            <div class="col-md-4">
              <div class="glass-effect p-4 rounded-3 hover-lift h-100">
                <div class="d-flex justify-content-between">
                  <div>
                    <p class="text-muted small mb-1">Total Exams</p>
                    <h3 id="totalExams" class="fw-bold mb-0">0</h3>
                  </div>
                  <div class="p-3 bg-light rounded-3">
                    <i class="far fa-file-alt text-primary fs-3"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="glass-effect p-4 rounded-3 hover-lift h-100">
                <div class="d-flex justify-content-between">
                  <div>
                    <p class="text-muted small mb-1">Published</p>
                    <h3 id="publishedExams" class="fw-bold text-success mb-0">
                      0
                    </h3>
                  </div>
                  <div class="p-3 bg-light rounded-3">
                    <i class="far fa-check-circle text-success fs-3"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="glass-effect p-4 rounded-3 hover-lift h-100">
                <div class="d-flex justify-content-between">
                  <div>
                    <p class="text-muted small mb-1">Draft</p>
                    <h3 id="draftExams" class="fw-bold text-warning mb-0">
                      0
                    </h3>
                  </div>
                  <div class="p-3 bg-light rounded-3">
                    <i class="far fa-edit text-warning fs-3"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- ===== filters ===== --}}
          <div class="glass-effect p-4 rounded-3 mb-4 shadow-sm">
            <div class="row">
              <div class="col-md-8 mb-3 mb-md-0 position-relative">
                <i
                  class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                ></i>
                <input
                  id="searchInput"
                  class="form-control ps-5"
                  placeholder="Search exams by title…"
                />
              </div>
              <div class="col-md-4">
                <select id="statusFilter" class="form-select">
                  <option value="all">All Status</option>
                  <option value="yes">Published</option>
                  <option value="no">Draft</option>
                </select>
              </div>
            </div>
          </div>

          {{-- ===== table ===== --}}
          <div class="glass-effect rounded-3 shadow-sm overflow-hidden">
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Exam</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Pricing</th>
                    <th>Attempt</th>
                    <th>Created</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="tbody"></tbody>
                <tbody id="spinnerRow">
                  <tr>
                    <td colspan="6" class="py-4 text-center">
                      <div
                        class="d-flex flex-column align-items-center gap-3"
                      >
                        <div class="spinner-border text-primary"></div>
                        <p class="text-muted fw-medium mb-0">
                          Loading your exams…
                        </p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>

    {{-- ====== INLINE EDIT MODAL ====== --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 id="modalTitle" class="modal-title">Edit Exam</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <form id="editForm">
            <div class="modal-body">
              <input type="hidden" id="editExamId" />
          
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Exam Name</label>
                  <input id="editName" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <select id="editStatus" class="form-select">
                    <option value="yes">Published</option>
                    <option value="no">Draft</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Total Time (mins)</label>
                  <input id="editTotalTime" type="number" min="1" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Total Attempts</label>
                  <input id="editTotalAttempts" type="number" min="1" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Pricing</label>
                  <div class="d-flex gap-3">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="pricing_model" id="editPricingFree" value="free" />
                      <label class="form-check-label" for="editPricingFree">Free</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="pricing_model" id="editPricingPaid" value="paid" />
                      <label class="form-check-label" for="editPricingPaid">Paid</label>
                    </div>
                  </div>
                </div>
              </div>
          
              <div id="editPricingInputs" class="row g-3 mt-3" style="display:none">
                <div class="col-md-6">
                  <label class="form-label">Regular Price</label>
                  <input id="editRegularPrice" type="number" min="0" step="0.01" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Sale Price</label>
                  <input id="editSalePrice" type="number" min="0" step="0.01" class="form-control" />
                </div>
              </div>
          
              <div class="row g-3 mt-3">
                <div class="col-md-6">
                  <label class="form-label">Result Release</label>
                  <select id="editReleaseType" class="form-select">
                    <option value="Immediately">Immediately</option>
                    <option value="Schedule">Schedule</option>
                  </select>
                </div>
                <div id="editReleaseDate" class="col-md-6" style="display:none">
                  <label class="form-label">Release On</label>
                  <input id="editReleaseOn" type="date" class="form-control" />
                </div>
              </div>
          
              <div class="row g-3 mt-3">
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea id="editDescription" class="form-control summernote" rows="2"></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label">Instructions</label>
                  <textarea id="editInstructions" class="form-control summernote" rows="2"></textarea>
                </div>
              </div>
          
              <div class="row g-3 mt-3">
                <div class="col-md-6">
                  <label class="form-label">Exam Image</label>
                  <input id="editImg" type="file" accept="image/*" class="form-control" />
                </div>
                <div class="col-md-6 d-flex align-items-center">
                  <img id="editImgPreview" class="img-fluid rounded-3 shadow-sm" style="max-height:200px; display:none" />
                </div>
              </div>
            </div>
          
            <div class="modal-footer border-top">
              <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
              <button id="saveEditBtn" class="btn btn-primary" type="button">Save Changes</button>
            </div>
          </form>
          
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    ></script>

    <script>
      // initialize Summernote
      jQuery(document).ready(() => {
        jQuery(".summernote").summernote({ height: 150 });
      });

      // Helpers
      const qs = (sel) => document.querySelector(sel);
      const qsa = (sel) => Array.from(document.querySelectorAll(sel));

      qs("#currentDate").textContent = new Date().toLocaleDateString(
        "en-US",
        { weekday: "short", month: "short", day: "numeric" }
      );

      let exams = [];

      function updateStats(list = exams) {
        const total = list.length;
        const published = list.filter((e) => e.is_public === "yes").length;
        qs("#totalExams").textContent = total;
        qs("#publishedExams").textContent = published;
        qs("#draftExams").textContent = total - published;
      }

      function renderTable(list) {
        const tbody = qs("#tbody");
        const spinner = qs("#spinnerRow");
        if (!list.length) {
          tbody.innerHTML =
            '<tr><td colspan="6" class="py-4 text-center text-muted">No exams found.</td></tr>';
          spinner.classList.add("d-none");
          return;
        }
        tbody.innerHTML = list
          .map((e) => {
            const badgeStatus =
              e.is_public === "yes"
                ? '<span class="badge badge-published">Published</span>'
                : '<span class="badge badge-draft">Draft</span>';
            const badgePrice =
              e.pricing_model === "paid"
                ? '<span class="badge badge-paid">Paid</span>'
                : '<span class="badge badge-free">Free</span>';
            const rel =
              e.result_set_up_type === "Schedule" && e.result_release_date
                ? new Date(e.result_release_date).toLocaleDateString()
                : "Immediate";
            return `
              <tr data-title="${e.examName.toLowerCase()}" data-status="${e.is_public}">
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('') }}${e.examImg}" class="exam-img" />
                    <span class="fw-semibold">${e.examName}</span>
                  </div>
                </td>
                <td>${e.associated_course}</td>
                <td>${badgeStatus}</td>
                <td>${badgePrice}</td>
                <td class="text-muted">${e.total_attempts}</td>
                <td class="text-muted">${new Date(e.created_at).toLocaleDateString()}</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="openEdit(${e.id})">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteExam(${e.id})">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <a class="btn btn-sm btn-outline-info" title="Add Question" href="/question/add?exam_id=${e.id}">
                    <i class="fas fa-plus"></i> Questions(${e.total_questions?e.total_questions:0})
                  </a>
                </td>
              </tr>
            `;
          })
          .join("");
        spinner.classList.add("d-none");
      }

      async function fetchExams() {
        try {
          const res = await fetch("/api/exam", {
            headers: {
              Accept: "application/json",
              "X-User-Role": "admin",
              Authorization: `Bearer ${localStorage.getItem("token") || sessionStorage.getItem("token")}`,
            },
          });
          const json = await res.json();
          if (res.status === 401 || res.status === 403) {
            window.location.href = "/unauthorized";
            return;
          }
          if (!res.ok || !json.success) throw new Error(json.message || "Failed");
          exams = json.exams;
          renderTable(exams);
          updateStats();
        } catch (err) {
          qs("#spinnerRow").innerHTML = `<tr><td colspan="6" class="py-4 text-center text-danger">${err.message}</td></tr>`;
        }
      }

      function filterRows() {
        const q = qs("#searchInput").value.toLowerCase();
        const status = qs("#statusFilter").value;
        const filtered = exams.filter(
          (e) =>
            e.examName.toLowerCase().includes(q) &&
            (status === "all" || e.is_public === status)
        );
        renderTable(filtered);
        updateStats(filtered);
      }

      qs("#searchInput").addEventListener("input", filterRows);
      qs("#statusFilter").addEventListener("change", filterRows);

      async function deleteExam(id) {
        const result = await Swal.fire({
          title: "Delete this exam?",
          text: "This action is irreversible",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#ef4444",
          confirmButtonText: "Delete",
        });
        if (!result.isConfirmed) return;

        try {
          const res = await fetch(`/api/exam/${id}`, {
            method: "DELETE",
            headers: {
              "X-CSRF-TOKEN": qs('meta[name="csrf-token"]').content,
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token") || sessionStorage.getItem("token")}`,
            },
          });
          const json = await res.json();
          if (res.status === 401 || res.status === 403) {
            window.location.href = "/unauthorized";
            return;
          }
          if (!res.ok || !json.success) throw new Error(json.message || "Failed");
          exams = exams.filter((e) => e.id !== id);
          renderTable(exams);
          updateStats();
          Swal.fire("Deleted!", "Exam removed", "success");
        } catch (err) {
          Swal.fire("Error", err.message, "error");
        }
      }

      // Modal & edit/save logic (unchanged except using qs/qsa instead of $ alias)

      const editModal = new bootstrap.Modal(qs("#editModal"));

      function openEdit(id) {
        const e = exams.find((x) => x.id === id);
        if (!e) return;
        qs("#modalTitle").textContent = `Edit: ${e.examName}`;
        qs("#editExamId").value = id;
        qs("#editName").value = e.examName;
        jQuery("#editDescription").summernote("code", e.examDescription || "");
        jQuery("#editInstructions").summernote("code", e.Instructions || "");
        qs('#editTotalTime').value      = e.totalTime       || '';
        qs('#editTotalAttempts').value  = e.total_attempts  || '';
        qs("#editStatus").value = e.is_public;

        if (e.pricing_model === "paid") {
          qs("#editPricingPaid").checked = true;
          qs("#editPricingInputs").style.display = "flex";
          qs("#editRegularPrice").value = e.regular_price;
          qs("#editSalePrice").value = e.sale_price;
        } else {
          qs("#editPricingFree").checked = true;
          qs("#editPricingInputs").style.display = "none";
        }

        if (e.result_set_up_type === "Schedule") {
          qs("#editReleaseType").value = "Schedule";
          qs("#editReleaseDate").style.display = "block";
          qs("#editReleaseOn").value = e.result_release_date?.split(" ")[0] || "";
        } else {
          qs("#editReleaseType").value = "Immediately";
          qs("#editReleaseDate").style.display = "none";
        }

        const imgPrev = qs("#editImgPreview");
        imgPrev.src = `{{ asset("") }}${e.examImg}`;
        imgPrev.style.display = "block";

        editModal.show();
      }

      qs("#editPricingPaid").addEventListener("change", () => {
        qs("#editPricingInputs").style.display = "flex";
      });
      qs("#editPricingFree").addEventListener("change", () => {
        qs("#editPricingInputs").style.display = "none";
      });
      qs("#editReleaseType").addEventListener("change", (ev) => {
        qs("#editReleaseDate").style.display =
          ev.target.value === "Schedule" ? "block" : "none";
      });
      qs("#editImg").addEventListener("change", (ev) => {
        const f = ev.target.files[0];
        if (f) {
          const prev = qs("#editImgPreview");
          prev.src = URL.createObjectURL(f);
          prev.style.display = "block";
        }
      });

      function closeModal() {
        editModal.hide();
        qs("#editForm").reset();
        jQuery("#editDescription").summernote("reset");
        jQuery("#editInstructions").summernote("reset");
        qs("#editImgPreview").style.display = "none";
      }

      qs("#saveEditBtn").addEventListener("click", async () => {
        const id = qs("#editExamId").value;
        const form = new FormData();
        form.append("_method", "PUT");
        form.append("examName", qs("#editName").value);
        form.append("examDescription", jQuery("#editDescription").summernote("code"));
        form.append("Instructions", jQuery("#editInstructions").summernote("code"));
        form.append("is_public", qs("#editStatus").value);
        form.append('totalTime',       qs('#editTotalTime').value);
        form.append('total_attempts',  qs('#editTotalAttempts').value);

        const pm = document.querySelector('input[name="pricing_model"]:checked').value;
        form.append("pricing_model", pm);
        if (pm === "paid") {
          form.append("regular_price", qs("#editRegularPrice").value);
          form.append("sale_price", qs("#editSalePrice").value);
        }

        const rt = qs("#editReleaseType").value;
        form.append("result_set_up_type", rt);
        if (rt === "Schedule") {
          form.append("result_release_date", qs("#editReleaseOn").value);
        }

        const img = qs("#editImg").files[0];
        if (img) form.append("examImg", img);

        const btn = qs("#saveEditBtn");
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';

        try {
          const res = await fetch(`/api/exam/${id}`, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": qs('meta[name="csrf-token"]').content,
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token") || sessionStorage.getItem("token")}`,
            },
            body: form,
          });
          const json = await res.json();
          if (res.status === 401 || res.status === 403) {
            window.location.href = "/unauthorized";
            return;
          }
          if (!res.ok || !json.success) throw new Error(json.message || "Failed");
          await Swal.fire("Success", "Exam updated", "success");
          closeModal();
          fetchExams();
        } catch (err) {
          Swal.fire("Error", err.message, "error");
        } finally {
          btn.disabled = false;
          btn.innerHTML = originalText;
        }
      });

      // initialize
      fetchExams();
    </script>
  </body>
</html>
