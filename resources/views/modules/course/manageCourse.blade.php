<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Courses</title>

  <!-- CSRF -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font-Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Page Styles -->
  <link rel="stylesheet" href="{{ asset('css/pages/course/manageCourse.css') }}"/>
</head>
<body class="bg-light">

<div class="container-fluid">
  <div class="row">
    <main class="col-12 px-md-4 py-4">

      {{-- ===== breadcrumb + create ===== --}}
      <div class="rounded-3 p-4 mb-4 glass-effect">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="#"><i class="fas fa-graduation-cap me-1"></i>Course</a>
            </li>
            <li class="breadcrumb-item active">Manage</li>
          </ol>
          <div class="d-flex align-items-center gap-3">
            <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-2 bg-white rounded-3 text-muted">
              <i class="far fa-calendar-alt"></i>
              <span id="currentDate"></span>
            </div>
            <button class="btn gradient-btn text-white hover-lift" onclick="openCreate()">
              <i class="fas fa-plus me-2"></i>New Course
            </button>
          </div>
        </div>
      </div>

      {{-- ===== stats ===== --}}
      <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
          <div class="glass-effect p-4 rounded-3 hover-lift h-100 text-center">
            <p class="text-muted small mb-1">Total Courses</p>
            <h3 id="totalCourses" class="fw-bold mb-0">0</h3>
            <div class="mt-2"><i class="fas fa-layer-group text-primary fs-3"></i></div>
          </div>
        </div>
        {{-- <div class="col-md-4 mb-3 mb-md-0">
          <div class="glass-effect p-4 rounded-3 hover-lift h-100 text-center">
            <p class="text-muted small mb-1">Free Courses</p>
            <h3 id="freeCourses" class="fw-bold text-success mb-0">0</h3>
            <div class="mt-2"><i class="fas fa-tag text-success fs-3"></i></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="glass-effect p-4 rounded-3 hover-lift h-100 text-center">
            <p class="text-muted small mb-1">Paid Courses</p>
            <h3 id="paidCourses" class="fw-bold text-warning mb-0">0</h3>
            <div class="mt-2"><i class="fas fa-dollar-sign text-warning fs-3"></i></div>
          </div>
        </div> --}}
      </div>

      {{-- ===== filters ===== --}}
      <div class="glass-effect p-4 rounded-3 mb-4 shadow-sm">
        <div class="row g-3">
          <div class="col-md-8 position-relative">
            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            <input id="searchInput" class="form-control ps-5" placeholder="Search courses by title…">
          </div>
          <div class="col-md-4">
            <select id="pricingFilter" class="form-select">
              <option value="all">All Pricing</option>
              <option value="free">Free</option>
              <option value="paid">Paid</option>
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
                <th>Course</th>
                <th>Title</th>
                <th>Created</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="tbody"></tbody>
            <tbody id="spinnerRow">
              <tr>
                <td colspan="4" class="py-4 text-center">
                  <div class="d-flex flex-column align-items-center gap-3">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted fw-medium mb-0">Loading courses…</p>
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

{{-- ===== inline CREATE/EDIT MODAL ===== --}}
<div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="courseForm" class="modal-content" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="_method" id="courseFormMethod" value="POST">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="courseModalTitle">New Course</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="courseId" name="id">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input id="courseTitle" name="title" class="form-control text-uppercase" required>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="courseDescription" name="description" class="form-control" rows="3"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Image</label>
            <input id="courseImage" name="image" type="file" accept="image/*" class="form-control">
          </div>
          <div class="col-12 text-center">
            <img id="imagePreview" class="img-fluid rounded-3 shadow-sm mt-3" style="display:none; max-height:200px">
          </div>
        </div>
      </div>

      <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="saveCourseBtn" type="button" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const token = localStorage.getItem('token') || sessionStorage.getItem('token'),
        headers = { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` };

  let courses = [];
  const tbody       = document.getElementById('tbody'),
        spinner     = document.getElementById('spinnerRow'),
        courseModal = new bootstrap.Modal(document.getElementById('courseModal'));

  // Show current date
  document.getElementById('currentDate').textContent =
    new Date().toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});

  // Uppercase enforcement
  document.getElementById('courseTitle')
    .addEventListener('input', e => { e.target.value = e.target.value.toUpperCase(); });

  // Stats
  function updateStats() {
    const total = courses.length,
          free  = courses.filter(c=>!c.sale_price).length,
          paid  = total - free;
    document.getElementById('totalCourses').textContent = total;
    document.getElementById('freeCourses').textContent  = free;
    document.getElementById('paidCourses').textContent  = paid;
  }

  // Fetch
  async function fetchCourses() {
    try {
      let res  = await fetch('/api/courses',{ headers }),
          json = await res.json();
      if(!res.ok||!json.success) throw new Error(json.message||'Failed');
      courses = json.courses;
      renderTable(courses);
      updateStats();
    } catch(e) {
      spinner.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-danger">${e.message}</td></tr>`;
    }
  }

  // Render
  function renderTable(list) {
    tbody.innerHTML = list.map(c=>{
      const badge = c.sale_price
        ? '<span class="badge badge-paid">Paid</span>'
        : '<span class="badge badge-free">Free</span>';
      const created = new Date(c.created_at).toLocaleDateString();
      return `
        <tr data-title="${c.title.toLowerCase()}"
            data-pricing="${c.sale_price?'paid':'free'}">
          <td>
            <div class="d-flex align-items-center gap-3">
              ${c.image?`<img src="{{ asset('') }}${c.image}" class="course-img">`:``}
            </div>
          </td>
          <td>${c.title}</td>
          <td class="text-muted">${created}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary me-1" onclick="openEdit(${c.id})">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${c.id})">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>`;
    }).join('');
    spinner.classList.add('d-none');
  }

  // Filters
  document.getElementById('searchInput')
    .addEventListener('input', filterRows);
  document.getElementById('pricingFilter')
    .addEventListener('change', filterRows);

  function filterRows() {
    const q = document.getElementById('searchInput').value.toLowerCase(),
          p = document.getElementById('pricingFilter').value;
    tbody.querySelectorAll('tr').forEach(r=>{
      const okText  = r.dataset.title.includes(q),
            okPrice = p==='all' || r.dataset.pricing===p;
      r.classList.toggle('d-none', !(okText && okPrice));
    });
  }

  // Open Create
  function openCreate(){
    document.getElementById('courseModalTitle').textContent = 'New Course';
    document.getElementById('courseForm').reset();
    document.getElementById('courseFormMethod').value = 'POST';
    document.getElementById('courseId').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    courseModal.show();
  }

  // Open Edit
  function openEdit(id){
    const c = courses.find(x=>x.id===id);
    if(!c) return;
    document.getElementById('courseModalTitle').textContent = 'Edit Course';
    document.getElementById('courseFormMethod').value = 'PUT';
    document.getElementById('courseId').value         = c.id;
    document.getElementById('courseTitle').value      = c.title;
    document.getElementById('courseDescription').value  = c.description;
    if(c.image){
      let img = document.getElementById('imagePreview');
      img.src = `{{ asset('') }}${c.image}`;
      img.style.display = 'block';
    }
    courseModal.show();
  }

  // Preview Image
  document.getElementById('courseImage')
    .addEventListener('change', e=>{
      let f = e.target.files[0];
      if(!f) return;
      let img = document.getElementById('imagePreview');
      img.src = URL.createObjectURL(f);
      img.style.display = 'block';
    });

  // Save
  document.getElementById('saveCourseBtn')
    .addEventListener('click', async ()=>{
      const form  = document.getElementById('courseForm'),
            fd    = new FormData(form),
            method= document.getElementById('courseFormMethod').value,
            id    = document.getElementById('courseId').value,
            url   = id?`/api/courses/${id}`:'/api/courses';

      let btn = document.getElementById('saveCourseBtn');
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Saving…`;

      try {
        let res  = await fetch(url, {
              method: 'POST',
              headers:{
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Authorization': `Bearer ${token}`
              },
              body: fd
            }),
            json = await res.json();
        if(!res.ok||!json.success) throw new Error(json.message||'Failed');
        await Swal.fire('Success','Course saved','success');
        courseModal.hide();
        fetchCourses();
      } catch(e) {
        Swal.fire('Error', e.message, 'error');
      } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save';
      }
    });

  // Delete
  async function deleteCourse(id){
    let ok = await Swal.fire({
      title:'Delete this course?',
      text:'This cannot be undone',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Delete',
      confirmButtonColor:'#dc3545'
    });
    if(!ok.isConfirmed) return;

    try {
      let res  = await fetch(`/api/courses/${id}`, {
            method:'DELETE',
            headers:{
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
              'Authorization': `Bearer ${token}`
            }
          }),
          json = await res.json();
      if(!res.ok||!json.success) throw new Error(json.message||'Failed');
      courses = courses.filter(c=>c.id!==id);
      renderTable(courses);
      updateStats();
      Swal.fire('Deleted','Course removed','success');
    } catch(e) {
      Swal.fire('Error', e.message, 'error');
    }
  }

  // Init
  fetchCourses();
</script>
</body>
</html>
