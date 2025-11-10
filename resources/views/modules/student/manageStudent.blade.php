<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Manage Students</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/pages/student/manageStudent.css') }}"/>
</head>
<body class="bg-light">
  <div class="container-fluid">
    <main class="px-md-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h1 class="h2"><i class="fas fa-user-graduate"></i> Student Management</h1>
        <button id="btnRegister" class="btn btn-primary">
          <i class="fas fa-user-plus me-1"></i> Register a Student
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="row mb-4">
        <div class="col-md-4 mb-2 position-relative">
          <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ms-3"></i>
          <input id="searchBox" oninput="applySearch()" type="text"
                 class="form-control ps-5" placeholder="Search ID/name/email/phone…">
        </div>
        <div class="col-md-3 mb-2">
          <input type="date" id="filterDate" class="form-control" onchange="applySearch()">
        </div>
        <div class="col-md-5 mb-2 d-flex justify-content-end">
          <button onclick="refreshStudents()" class="btn btn-info me-2">
            <i class="fas fa-sync-alt me-1"></i> Refresh
          </button>
          <button onclick="exportCSV()" class="btn btn-success">
            <i class="fas fa-file-export me-1"></i> Export CSV
          </button>
        </div>
      </div>

      <!-- Data Table -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th class="sortable" onclick="sortBy('id')">ID <i class="fas fa-sort ms-1"></i></th>
                  <th class="sortable" onclick="sortBy('name')">Name <i class="fas fa-sort ms-1"></i></th>
                  <th class="sortable" onclick="sortBy('email')">Email <i class="fas fa-sort ms-1"></i></th>
                  <th class="sortable" onclick="sortBy('phone')">Phone <i class="fas fa-sort ms-1"></i></th>
                  <th class="sortable" onclick="sortBy('status')">Status <i class="fas fa-sort ms-1"></i></th>
                  <th class="sortable" onclick="sortBy('updated_at')">Updated <i class="fas fa-sort ms-1"></i></th>
                </tr>
              </thead>
              <tbody id="studentTbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item">
            <button class="page-link" onclick="firstPage()" id="first-page-btn">
              <i class="fas fa-angle-double-left"></i>
            </button>
          </li>
          <li class="page-item">
            <button class="page-link" onclick="prevPage()" id="prev-page-btn">
              <i class="fas fa-angle-left"></i>
            </button>
          </li>
          <li class="page-item disabled">
            <span class="page-link" id="pageInfo">1/1</span>
          </li>
          <li class="page-item">
            <button class="page-link" onclick="nextPage()" id="next-page-btn">
              <i class="fas fa-angle-right"></i>
            </button>
          </li>
          <li class="page-item">
            <button class="page-link" onclick="lastPage()" id="last-page-btn">
              <i class="fas fa-angle-double-right"></i>
            </button>
          </li>
        </ul>
      </nav>
    </main>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    /* -------- GLOBAL -------- */
    const apiBase = '/api/student';
    const perPage = 10;
    let allStudents = [];
    let filteredStudents = [];
    let currentPage = 1;
    let currentSort = null;
    let sortAsc = true;
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    const authHeaders = () => ({
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    });

    /* -------- LOAD -------- */
    async function loadStudents() {
      currentPage = 1; currentSort = null; sortAsc = true;
      $('#searchBox').value = '';
      $('#filterDate').value = '';

      const res = await fetch(`${apiBase}/all`, { headers: authHeaders() });
      if (res.status === 401 || res.status === 403) {
        return window.location.href = '/unauthorized';
      }
      const data = await res.json();
      if (!res.ok || !data.success) {
        return Swal.fire('Error', data.message || 'Failed to load students', 'error');
      }

      allStudents = data.students;
      filteredStudents = [...allStudents];
      renderStudents();
    }

    /* -------- SEARCH & FILTER -------- */
    function applySearch() {
      const q = $('#searchBox').value.trim().toLowerCase();
      const date = $('#filterDate').value;
      filteredStudents = allStudents.filter(s => {
        const matchText =
          s.id.toString().includes(q) ||
          (s.name || '').toLowerCase().includes(q) ||
          (s.email || '').toLowerCase().includes(q) ||
          (s.phone || '').toLowerCase().includes(q);
        const matchDate = !date || (s.updated_at?.split(' ')[0] === date);
        return matchText && matchDate;
      });
      currentPage = 1;
      currentSort ? sortStudents(currentSort) : renderStudents();
    }

    /* -------- SORT -------- */
    function sortBy(field) {
      if (currentSort === field) sortAsc = !sortAsc;
      else { currentSort = field; sortAsc = true; }
      sortStudents(field);
    }
    function sortStudents(field) {
      filteredStudents.sort((a,b) => {
        let av = a[field], bv = b[field];
        if (field === 'updated_at') {
          av = new Date(av||0); bv = new Date(bv||0);
        } else if (typeof av === 'string') {
          av = av.toLowerCase(); bv = bv.toLowerCase();
        }
        return av < bv ? (sortAsc? -1:1) : av > bv ? (sortAsc?1:-1) : 0;
      });
      renderStudents();
    }

    /* -------- RENDER -------- */
    function renderStudents() {
      const tbody = $('#studentTbody');
      tbody.innerHTML = '';
      const start = (currentPage-1)*perPage;
      const slice = filteredStudents.slice(start, start+perPage);

      if (!slice.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">
          <i class="fas fa-search fa-2x mb-2"></i><br>No students found</td></tr>`;
        updatePageInfo();
        return;
      }

      for (const s of slice) {
        const nameCell = s.name
          ? s.name
          : '<em class="text-warning">Profile not completed</em>';
        const emailCell = s.email
          ? s.email
          : '<em class="text-warning">Email not uploaded</em>';

        const badgeClass = s.status === 'pending'
          ? 'bg-warning text-dark'
          : 'bg-success';

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${s.id}</td>
          <td>${nameCell}</td>
          <td>${emailCell}</td>
          <td>${s.phone || '-'}</td>
          <td><span class="badge ${badgeClass}">${s.status}</span></td>
          <td>${s.updated_at ? new Date(s.updated_at).toLocaleDateString() : '-'}</td>`;
        tbody.appendChild(tr);
      }
      updatePageInfo();
    }

    function updatePageInfo() {
      const total = Math.max(1, Math.ceil(filteredStudents.length / perPage));
      $('#pageInfo').textContent = `${currentPage}/${total}`;
      $('#first-page-btn').parentElement.classList.toggle('disabled', currentPage===1);
      $('#prev-page-btn').parentElement.classList.toggle('disabled', currentPage===1);
      $('#next-page-btn').parentElement.classList.toggle('disabled', currentPage===total);
      $('#last-page-btn').parentElement.classList.toggle('disabled', currentPage===total);
    }

    /* -------- PAGINATION -------- */
    function firstPage(){ if (currentPage>1){ currentPage=1; renderStudents(); }}
    function prevPage(){ if (currentPage>1){ currentPage--; renderStudents(); }}
    function nextPage(){
      const tp = Math.ceil(filteredStudents.length / perPage);
      if (currentPage < tp){ currentPage++; renderStudents(); }
    }
    function lastPage(){
      const tp = Math.ceil(filteredStudents.length / perPage);
      if (currentPage < tp){ currentPage = tp; renderStudents(); }
    }

    /* -------- CSV EXPORT -------- */
    function exportCSV() {
      if (!filteredStudents.length) {
        return Swal.fire('No Data','No students to export','info');
      }
      const headers = ['ID','Name','Email','Phone','Status','Updated'];
      let csv = headers.join(',') + '\n';
      filteredStudents.forEach(s => {
        const name = s.name ? s.name : 'Profile not completed';
        const email = s.email ? s.email : 'Email not uploaded';
        csv += [
          s.id,
          `"${name.replace(/"/g,'""')}"`,
          `"${email.replace(/"/g,'""')}"`,
          `"${s.phone||''}"`,
          s.status,
          s.updated_at ? new Date(s.updated_at).toLocaleDateString() : ''
        ].join(',') + '\n';
      });
      const blob = new Blob([csv],{ type:'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `students_${new Date().toISOString().split('T')[0]}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }

    /* -------- UTIL -------- */
    const $ = s => document.querySelector(s);

    function refreshStudents() { loadStudents(); }

    /* -------- INIT -------- */
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('btnRegister').addEventListener('click', () => {
        window.location.href = '/student/register';
      });
      loadStudents();
    });
  </script>
</body>
</html>
