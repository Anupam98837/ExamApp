<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Results Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/pages/result/manageResult.css') }}"/>
  
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <main class="col-md-12 ms-sm-auto px-md-4 py-4">
        <div class="rounded-3 p-4 mb-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#"><i class="fas fa-user-graduate me-1"></i>Student</a></li>
              <li class="breadcrumb-item active" aria-current="page">Results</li>
            </ol>
            <div class="d-flex align-items-center gap-3">
              <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 text-muted">
                <i class="far fa-calendar-alt"></i>
                <span id="currentDate"></span>
              </div>
              <div class="d-flex gap-2">
                <button id="export-all" class="btn btn-success text-white">
                  <i class="fa-solid fa-file-csv me-1"></i>
                  All CSV
                </button>
                <button id="export-filtered" class="btn gradient-btn text-white">
                  <i class="fa-solid fa-filter me-1"></i>
                  Filtered
                </button>
              </div>
            </div>
          </div>
          <hr>
        </div>

        <!-- Filters Section -->
        <div class="glass-effect rounded-3 p-4 mb-4 shadow-sm">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="position-relative">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input id="search-input" type="text" class="form-control ps-5" placeholder="Search by Name / Exam...">
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="position-relative">
                <i class="fas fa-book position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <select id="exam-filter" class="form-select ps-5">
                  <option value="">Filter by Exam</option>
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="position-relative">
                <i class="fas fa-calendar position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input id="date-filter" type="date" class="form-control ps-5">
              </div>
            </div>
            
            <div class="col-md-1">
              <button onclick="applyFiltersAndRender()" class="btn btn-info w-100 text-white">
                <i class="fas fa-sync-alt me-1"></i>
                Apply
              </button>
            </div>
          </div>
        </div>

        <!-- Results Table -->
        <div class="glass-effect rounded-3 shadow-sm overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-header">
                <tr>
                  <th scope="col" class="sortable" data-sort="student_name">Student Name <i class="fas fa-sort ms-1"></i></th>
                  <th scope="col" class="sortable" data-sort="examName">Exam Name <i class="fas fa-sort ms-1"></i></th>
                  <th scope="col" class="text-center sortable" data-sort="total_attempts">Attempt Info <i class="fas fa-sort ms-1"></i></th>
                  <th scope="col" class="sortable" data-sort="created_at">Submitted At <i class="fas fa-sort ms-1"></i></th>
                  <th scope="col" class="text-center sortable" data-sort="publish_to_student">Status <i class="fas fa-sort ms-1"></i></th>
                  <th scope="col" class="text-center"><i class="fa-solid fa-cog"></i></th>
                </tr>
              </thead>
              <tbody id="result-body" class="border-top-0"></tbody>
              <tbody id="loading-row">
                <tr>
                  <td colspan="10" class="py-4 text-center">
                    <div class="d-flex flex-column align-items-center gap-3">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="text-muted fw-medium">Loading exam results...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-center gap-2 mt-4">
          <button id="first-page" class="pagination-btn" title="First">
            <i class="fas fa-angle-double-left"></i>
          </button>
          <button id="prev-page" class="pagination-btn" title="Previous">
            <i class="fas fa-angle-left"></i>
          </button>
          <span id="page-info" class="mx-3 text-muted fw-medium">1/1</span>
          <button id="next-page" class="pagination-btn" title="Next">
            <i class="fas fa-angle-right"></i>
          </button>
          <button id="last-page" class="pagination-btn" title="Last">
            <i class="fas fa-angle-double-right"></i>
          </button>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    /* ========== State ========= */
    let allResults = [], filteredResults = [], currentPage = 1;
    const itemsPerPage = 10;
    let currentSort = null, sortAscending = true;
    const studentId = sessionStorage.getItem('student_id'); // Get student_id from session storage

    /* ========== DOM Elements ========= */
    const resultBody = document.getElementById('result-body');
    const loadingRow = document.getElementById('loading-row');
    const searchInput = document.getElementById('search-input');
    const examFilter = document.getElementById('exam-filter');
    const dateFilter = document.getElementById('date-filter');
    const pageInfo = document.getElementById('page-info');
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');

    /* ========== Initialize ========= */
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric'
    });

    window.addEventListener('DOMContentLoaded', async () => {
      try {
        let apiUrl = '/api/exam/students/quizzes/results';
        
        // If student_id exists in session storage, append it to the API URL
        if (studentId) {
          apiUrl += `?student_id=${studentId}`;
        }

        const res = await fetch(apiUrl, {
          headers: { 'Accept': 'application/json' ,
                      'Authorization':`Bearer ${token}`
          }
        });
        if (res.status === 403 || res.status === 401) {
            return window.location.href = '/unauthorized';
          }
        if (!res.ok) throw new Error();
        
        const json = await res.json();
        const rows = Array.isArray(json) ? json : (json.data ?? json.results ?? []);
        
        if (!rows.length) {
          showNoResults();
          return;
        }
        
        allResults = rows;
        populateExamDropdown(rows);
        applyFiltersAndRender();
      } catch (err) {
        console.error(err);
        showError();
      }
    });

    function showNoResults() {
      loadingRow.innerHTML = `
        <tr>
          <td colspan="10" class="px-6 py-8 text-center">
            <div class="d-flex flex-column align-items-center gap-3">
              <i class="fas fa-inbox text-4xl text-gray-300"></i>
              <p class="text-gray-500 fw-medium">No Results Available</p>
              <p class="text-gray-400 small">Results will appear here once students complete exams</p>
            </div>
          </td>
        </tr>
      `;
    }

    function showError() {
      loadingRow.innerHTML = `
        <tr>
          <td colspan="10" class="px-6 py-8 text-center">
            <div class="d-flex flex-column align-items-center gap-3">
              <i class="fas fa-exclamation-triangle text-4xl text-danger"></i>
              <p class="text-danger fw-medium">Error fetching results</p>
              <p class="text-muted small">Please refresh the page or contact support</p>
            </div>
          </td>
        </tr>
      `;
    }

    function populateExamDropdown(rows) {
      const uniqueExams = [...new Set(rows.map(r => r.examName).filter(Boolean))];
      uniqueExams.forEach(exam => {
        const option = document.createElement('option');
        option.value = exam;
        option.textContent = exam;
        examFilter.appendChild(option);
      });
    }

    function applyFiltersAndRender() {
      const searchText = searchInput.value.toLowerCase();
      const examValue = examFilter.value;
      const dateValue = dateFilter.value;
      
      filteredResults = allResults.filter(r => {
        const dateMatch = !dateValue || new Date(r.created_at).toISOString().slice(0, 10) === dateValue;
        const examMatch = !examValue || r.examName === examValue;
        const textMatch = (r.name || '').toLowerCase().includes(searchText) || 
                         (r.examName || '').toLowerCase().includes(searchText);
        
        return dateMatch && examMatch && textMatch;
      });
      
      sortResults();
      renderTable();
      renderPagination();
    }

    function sortResults() {
      if (!currentSort) return;
      
      filteredResults.sort((a, b) => {
        let valueA, valueB;
        
        switch (currentSort) {
          case 'student_name':
            valueA = (a.name || '').toLowerCase();
            valueB = (b.name || '').toLowerCase();
            break;
          case 'examName':
            valueA = (a.examName || '').toLowerCase();
            valueB = (b.examName || '').toLowerCase();
            break;
          case 'marks_obtained':
            valueA = a.marks_obtained || 0;
            valueB = b.marks_obtained || 0;
            break;
          case 'total_attempts':
            valueA = a.total_attempts || 0;
            valueB = b.total_attempts || 0;
            break;
          case 'attempted_questions':
            valueA = a.attempted_questions || 0;
            valueB = b.attempted_questions || 0;
            break;
          case 'not_attempted_questions':
            valueA = a.not_attempted_questions || 0;
            valueB = b.not_attempted_questions || 0;
            break;
          case 'created_at':
            valueA = new Date(a.created_at);
            valueB = new Date(b.created_at);
            break;
          case 'publish_to_student':
            valueA = a.publish_to_student;
            valueB = b.publish_to_student;
            break;
        }
        
        if (typeof valueA === 'string') {
          return sortAscending ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
        }
        if (valueA instanceof Date) {
          return sortAscending ? valueA - valueB : valueB - valueA;
        }
        return sortAscending ? valueA - valueB : valueB - valueA;
      });
    }

    function renderTable() {
  const start      = (currentPage - 1) * itemsPerPage;
  const pageResults = filteredResults.slice(start, start + itemsPerPage);

  if (pageResults.length === 0) {
    resultBody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-8 text-center">
          <div class="d-flex flex-column align-items-center gap-3">
            <i class="fas fa-search text-4xl text-gray-300"></i>
            <p class="text-gray-500 fw-medium">No matching records found</p>
          </div>
        </td>
      </tr>`;
    return;
  }

  resultBody.innerHTML = pageResults.map(r => {
    // If student is viewing and name is missing, show profile prompt:
    if (studentId && !r.name) {
      return `
      <tr>
        <td colspan="6" class="text-center py-5">
          <div class="d-flex flex-column align-items-center gap-3">
            <i class="fas fa-user-edit fa-2x text-warning"></i>
            <p class="fw-medium">Complete your profile to view your results</p>
            <a href="/student/register" class="btn btn-primary">Complete Profile</a>
          </div>
        </td>
      </tr>`;
    }

    // Otherwise (admin or student with name), render the normal row:
    return `
      <tr data-id="${r.id}" class="table-row">
        <td class="px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <div class="student-avatar">${(r.name||'?').charAt(0).toUpperCase()}</div>
            <span class="fw-medium">${r.name || '—'}</span>
          </div>
        </td>
        <td class="px-4 py-3"><span class="fw-medium">${r.examName||'—'}</span></td>
        <td class="px-4 py-3 text-center">
          ${r.total_attempts ?? '—'}
          <button
            class="btn btn-sm btn-outline-secondary info-btn"
            data-name="${r.name||''}"
            data-exam="${r.examName||''}"
            data-attempts="${r.total_attempts||0}"
            data-attempted="${r.attempted_questions||0}"
            data-not="${r.not_attempted_questions||0}"
            data-marks="${r.marks_obtained||0}"
            title="View attempt info">
            <i class="fas fa-info-circle"></i>
          </button>
        </td>
        <td class="px-4 py-3">
          <div class="d-flex flex-column small text-muted">
            <span><i class="fas fa-calendar-alt me-1"></i>${new Date(r.created_at).toLocaleDateString()}</span>
            <span><i class="fas fa-clock me-1"></i>${new Date(r.created_at).toLocaleTimeString()}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-center">
          <span class="badge ${r.publish_to_student ? 'badge-published' : 'badge-unpublished'}">
            ${r.publish_to_student ? 'Published' : 'Unpublished'}
          </span>
        </td>
        <td class="px-4 py-3 text-center">
          <div class="d-flex justify-content-center gap-2">
            <button class="download-sheet btn btn-sm btn-outline-primary"
                    data-id="${r.id}"
                    data-name="${(r.name||'').replace(/\s+/g,'_')}"
                    title="Download answer sheet">
              <i class="fa-solid fa-download"></i>
            </button>
            <!-- only admin sees publish-toggle -->
            ${!studentId ? `
            <button class="toggle-publish btn btn-sm ${r.publish_to_student ? 'btn-outline-success' : 'btn-outline-secondary'}"
                    title="${r.publish_to_student ? 'Unpublish result' : 'Publish result'}">
              <i class="fas ${r.publish_to_student ? 'fa-eye' : 'fa-eye-slash'}"></i>
            </button>` : ''}
          </div>
        </td>
      </tr>`;
  }).join('');

  loadingRow.classList.add('d-none');
  resultBody.classList.remove('d-none');
}


    // Fire the SweetAlert for attempt info
    resultBody.addEventListener('click', e => {
      const btn = e.target.closest('.info-btn');
      if (!btn) return;

      const name     = btn.dataset.name;
      const exam     = btn.dataset.exam;
      const attempts = btn.dataset.attempts;
      const done     = btn.dataset.attempted;
      const notDone  = btn.dataset.not;
      const marks    = btn.dataset.marks;

      Swal.fire({
        title: `<strong>${name} — ${exam}</strong>`,
        html: `
          <div class="text-start">
            <p><i class="fas fa-list-ol text-primary me-2"></i>
              <strong>Exam Attempts:</strong> ${attempts}
            </p>
            <p><i class="fas fa-check text-success me-2"></i>
              <strong>Questions Attempted:</strong> ${done}
            </p>
            <p><i class="fas fa-times text-danger me-2"></i>
              <strong>Not Attempted:</strong> ${notDone}
            </p>
            <p><i class="fas fa-star text-warning me-2"></i>
              <strong>Marks Obtained:</strong> ${marks}
            </p>
          </div>
        `,
        showCloseButton: true,
        focusConfirm: false,
        confirmButtonText: 'Close',
        width: '400px'
      });
    });

    function renderPagination() {
      const totalPages = Math.max(1, Math.ceil(filteredResults.length / itemsPerPage));
      pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
      
      document.getElementById('first-page').disabled = currentPage === 1;
      document.getElementById('prev-page').disabled = currentPage === 1;
      document.getElementById('next-page').disabled = currentPage === totalPages;
      document.getElementById('last-page').disabled = currentPage === totalPages;
    }

    /* ========== Event Listeners ========= */
    
    // Pagination
    document.getElementById('first-page').onclick = () => {
      currentPage = 1;
      renderTable();
      renderPagination();
    };
    
    document.getElementById('prev-page').onclick = () => {
      if (currentPage > 1) currentPage--;
      renderTable();
      renderPagination();
    };
    
    document.getElementById('next-page').onclick = () => {
      const maxPage = Math.ceil(filteredResults.length / itemsPerPage) || 1;
      if (currentPage < maxPage) currentPage++;
      renderTable();
      renderPagination();
    };
    
    document.getElementById('last-page').onclick = () => {
      currentPage = Math.ceil(filteredResults.length / itemsPerPage) || 1;
      renderTable();
      renderPagination();
    };

    // CSV Export
    function downloadCSV(data, filename) {
      if (!data.length) return;
      
      const headers = ['Student Name', 'Exam Name', 'Marks', 'Attempts', 'Attempted Qs', 'Not Attempted', 'Submitted At'];
      const rows = data.map(r => [
        r.name || '-',
        r.examName || '-',
        r.marks_obtained ?? '-',
        r.total_attempts ?? '-',
        r.attempted_questions ?? '-',
        r.not_attempted_questions ?? '-',
        new Date(r.created_at).toLocaleString()
      ]);
      
      const csvContent = 'data:text/csv;charset=utf-8,' 
        + [headers, ...rows].map(row => row.map(x => `"${x}"`).join(',')).join('\n');
      
      const link = document.createElement('a');
      link.href = encodeURI(csvContent);
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
    
    document.getElementById('export-all').onclick = () => downloadCSV(allResults, 'all_student_results.csv');
    document.getElementById('export-filtered').onclick = () => downloadCSV(filteredResults, 'filtered_student_results.csv');

    // Search/filters
    searchInput.addEventListener('input', () => {
      currentPage = 1;
      applyFiltersAndRender();
    });
    
    examFilter.addEventListener('change', () => {
      currentPage = 1;
      applyFiltersAndRender();
    });
    
    dateFilter.addEventListener('change', () => {
      currentPage = 1;
      applyFiltersAndRender();
    });

    // Sort
    document.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        
        if (currentSort === key) {
          sortAscending = !sortAscending;
        } else {
          currentSort = key;
          sortAscending = true;
        }
        
        // Update sort icons
        document.querySelectorAll('th.sortable i').forEach(i => {
          i.className = 'fas fa-sort ms-1 text-muted';
        });
        
        const icon = th.querySelector('i');
        icon.className = sortAscending 
          ? 'fas fa-sort-up ms-1 text-primary' 
          : 'fas fa-sort-down ms-1 text-primary';
        
        applyFiltersAndRender();
      });
    });

    // Row actions
    resultBody.addEventListener('click', async (e) => {
      const downloadBtn = e.target.closest('.download-sheet');
      if (downloadBtn) return downloadSheet(downloadBtn);
      
      const publishBtn = e.target.closest('.toggle-publish');
      if (publishBtn) return togglePublish(publishBtn);
    });

    async function downloadSheet(btn) {
      const id = btn.dataset.id;
      btn.disabled = true;
      
      Swal.fire({
        title: 'Preparing Download...',
        html: '<i class="fas fa-spinner fa-spin text-2xl text-primary"></i>',
        allowOutsideClick: false,
        showConfirmButton: false,
        background: 'rgba(255, 255, 255, 0.9)',
        backdrop: 'rgba(0, 0, 0, 0.1)'
      });
      
      try {
        const res = await fetch(`/api/exam/results/${id}/answer-sheet`);
        if (!res.ok) throw new Error();
        
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `answer_sheet_${btn.dataset.name}_${id}.html`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
        
        Swal.fire({
          title: 'Success!',
          text: 'Download completed successfully',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          background: 'rgba(255, 255, 255, 0.9)'
        });
      } catch {
        Swal.fire({
          title: 'Error',
          text: 'Download failed. Please try again.',
          icon: 'error',
          confirmButtonColor: '#dc3545',
          background: 'rgba(255, 255, 255, 0.9)'
        });
      } finally {
        btn.disabled = false;
      }
    }

    async function togglePublish(btn) {
      // Skip if this is a student view
      if (studentId) return;
      
      const row = btn.closest('tr');
      const id = row.dataset.id;
      const isPublished = btn.classList.contains('btn-outline-success');
      const action = isPublished ? 'unpublish' : 'publish';
      
      const confirm = await Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Result?`,
        text: `Are you sure you want to ${action} this result?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: isPublished ? '#dc3545' : '#198754',
        background: 'rgba(255, 255, 255, 0.9)'
      });
      
      if (!confirm.isConfirmed) return;
      
      btn.disabled = true;
      
      try {
        const res = await fetch(`/api/exam/results/${id}/publish-toggle`, {
          method: 'PATCH'
        });
        
        if (!res.ok) throw new Error();
        
        const { publish_to_student } = await res.json();
        
        // Update local data
        const index = allResults.findIndex(r => r.id == id);
        if (index > -1) allResults[index].publish_to_student = publish_to_student;
        
        applyFiltersAndRender();
        
        Swal.fire({
          title: 'Success!',
          text: `Result ${action}ed successfully`,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          background: 'rgba(255, 255, 255, 0.9)'
        });
      } catch {
        Swal.fire({
          title: 'Error',
          text: `Failed to ${action} result`,
          icon: 'error',
          confirmButtonColor: '#dc3545',
          background: 'rgba(255, 255, 255, 0.9)'
        });
      } finally {
        btn.disabled = false;
      }
    }
  </script>
</body>
</html>