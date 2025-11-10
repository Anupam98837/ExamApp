@extends('users.admin.components.structure')

@section('title','Dashboard')
@section('header','Dashboard')

@section('content')
<div class="container py-4">

  {{-- Welcome --}}
  <div class="p-4 bg-white rounded shadow-sm mb-5 text-center">
    <h2 id="welcomeMessage" class="fw-bold text-primary mb-1">
      <span class="spinner-border spinner-border-sm"></span> Loading…
    </h2>
    <p class="text-muted" id="dashboardMessage">
      Hang tight while we gather your stats.
    </p>
  </div>

  {{-- Summary cards --}}
  <div class="row g-4 mb-5" id="statsRow">
    {{-- injected by JS --}}
  </div>

  {{-- Charts --}}
  <div class="row g-4 mb-5">
    <div class="col-md-6">
      <div class="card bg-white rounded shadow-sm h-100">
        <div class="card-header border-0 bg-light">
          <i class="fas fa-chart-bar me-2 text-info"></i> Exams by Pricing Model
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="examsByTypeChart" style="max-height:250px; width:100%;"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card bg-white rounded shadow-sm h-100">
        <div class="card-header border-0 bg-light">
          <i class="fas fa-users me-2 text-success"></i> Student Status Breakdown
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <canvas id="studentsStatusChart" style="max-height:250px; width:100%;"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
  {{-- Chart.js CDN --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', async () => {
    // 1) Auth check
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    if (!token || sessionStorage.getItem('token_type') !== 'admin') {
      return window.location.href = '/';
    }

    // 2) Fetch dashboard data
    let data;
    try {
      const resp = await fetch('/api/admin/dashboard', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      if (resp.status === 403 || resp.status === 401) {
            return window.location.href = '/unauthorized';
          }
      if (!resp.ok) throw new Error(resp.status);
      const json = await resp.json();
      if (!json.success) throw new Error(json.message);
      data = json.data;
    } catch (err) {
      console.error(err);
      document.getElementById('welcomeMessage').textContent = 'Failed to load dashboard';
      return;
    }

    // 3) Populate welcome
    document.getElementById('welcomeMessage').innerHTML =
      `Welcome, <strong>Admin</strong>!`;
    document.getElementById('dashboardMessage').textContent =
      'Here’s an overview of your portal.';

    // 4) Render summary cards with solid pastel backgrounds
    const s = data.students, e = data.exams;
    const row = document.getElementById('statsRow');
    row.innerHTML = `
      <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0" style="background: #cfe2ff;">
          <div class="card-body text-center">
            <i class="fas fa-user fa-2x mb-2 text-info"></i>
            <h6 class="text-muted">Total Students</h6>
            <h2 class="fw-bold">${s.total}</h2>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0" style="background: #d1e7dd;">
          <div class="card-body text-center">
            <i class="fas fa-clipboard-check fa-2x mb-2 text-success"></i>
            <h6 class="text-muted">Registered</h6>
            <h2 class="fw-bold">${s.registered}</h2>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0" style="background: #fff3cd;">
          <div class="card-body text-center">
            <i class="fas fa-star fa-2x mb-2 text-warning"></i>
            <h6 class="text-muted">Subscribed</h6>
            <h2 class="fw-bold">${s.subscribed}</h2>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="card h-100 border-0" style="background: #e1f5fe;">
          <div class="card-body text-center">
            <i class="fas fa-book fa-2x mb-2 text-info"></i>
            <h6 class="text-muted">Total Exams</h6>
            <h2 class="fw-bold">${e.total}</h2>
          </div>
        </div>
      </div>
    `;

    // 5) Solid pastel colors for bar chart
    const ctx1 = document.getElementById('examsByTypeChart').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: Object.keys(e.by_type),
        datasets: [{
          label: 'Count',
          data: Object.values(e.by_type),
          backgroundColor: ['#cfe2ff','#e2d4f0','#d1e7dd','#fff3cd'],
          borderColor: ['#9ec5fe','#c0a6da','#a3d3c1','#ffe599'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: { 
          y: { beginAtZero:true, ticks:{ color:'#555' } },
          x: { ticks:{ color:'#555' } }
        },
        plugins: { legend: { display: false } }
      }
    });

    // 6) Doughnut chart with solid pastel ring
    const ctx2 = document.getElementById('studentsStatusChart').getContext('2d');
    new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: ['Registered','Not Registered','Subscribed'],
        datasets: [{
          data: [s.registered, s.not_registered, s.subscribed],
          backgroundColor: ['#cfe2ff','#f8d7da','#d1e7dd'],
          borderColor: ['#9ec5fe','#f1a9b6','#a3d3c1'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          legend: { position:'bottom', labels:{ color:'#555' } }
        }
      }
    });
  });
  </script>
@endsection
