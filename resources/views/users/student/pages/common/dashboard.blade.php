@extends('users.student.components.structure')

@section('title','Student Dashboard')
@section('header','Dashboard')

@section('content')
<div class="container">
  {{-- ==========  Welcome Section  ========== --}}
  <div class="p-4 bg-white rounded shadow-sm mb-4 border border-light text-center text-md-start">
    <h2 id="welcomeMessage" class="fw-bold text-primary mb-2">
      <span class="spinner-border spinner-border-sm" role="status"></span> Loading...
    </h2>
    <p class="text-muted mb-0" id="dashboardMessage">
      Welcome to your learning dashboard. Track your progress and access exams here.
    </p>
    <div id="incompleteProfileAlert" class="alert alert-warning mt-3 d-none">
      <i class="fas fa-exclamation-triangle me-2"></i>
      Your profile is incomplete. Please complete your registration to access all features.
      <div class="mt-2">
        <a href="/student/register" class="btn btn-primary">Complete Profile</a>
      </div>
    </div>
  </div>

  {{-- ==========  Student Info & Quick Actions  ========== --}}
  <div class="row g-4 mb-4">
    {{-- Profile Card --}}
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-light fw-semibold">
          <i class="fas fa-user-graduate me-2 text-primary"></i> My Profile
        </div>
        <div class="card-body px-4 py-3">
          <ul class="list-group list-group-flush" id="studentDetails">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-user me-2 text-secondary"></i> Name</span>
              <span id="studentName" class="text-dark">Loading...</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-envelope me-2 text-secondary"></i> Email</span>
              <span id="studentEmail" class="text-dark">Loading...</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-phone me-2 text-secondary"></i> Phone</span>
              <span id="studentPhone" class="text-dark">Loading...</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-calendar-check me-2 text-secondary"></i> Registration</span>
              <span id="studentApproval" class="badge bg-secondary">Loading...</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-light fw-semibold">
          <i class="fas fa-bolt me-2 text-warning"></i> Quick Actions
        </div>
        <div class="card-body d-grid gap-3 px-4 py-3">
          <a href="/student/exams" class="btn btn-outline-primary">
            <i class="fas fa-book-open me-2"></i> Available Exams
          </a>
          <a href="/student/result" class="btn btn-outline-success">
            <i class="fas fa-chart-line me-2"></i> View Results
          </a>
          <a href="/student/profile" class="btn btn-outline-info">
            <i class="fas fa-user-edit me-2"></i> Update Profile
          </a>
          <a href="/student/help" class="btn btn-outline-warning">
            <i class="fas fa-question-circle me-2"></i> Get Help
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ==========  Statistics Section  ========== --}}
  <div class="row g-4">
    {{-- Stats Summary --}}
    <div class="col-12">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-light fw-semibold">
          <i class="fas fa-trophy me-2 text-warning float-pulse"></i> Your Stats
        </div>
        <div class="card-body">
          <div class="row g-3 text-center">
            <div class="col-6">
              <div class="p-3 border rounded bg-light">
                <h3 class="text-primary mb-1" id="totalExams">0</h3>
                <small class="text-muted">Exams Taken</small>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded bg-light">
                <h3 class="text-success mb-1" id="avgScore">0%</h3>
                <small class="text-muted">Average Score</small>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded bg-light">
                <h3 class="text-info mb-1" id="highestScore">0</h3>
                <small class="text-muted">Highest Score</small>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 border rounded bg-light">
                <h3 class="text-warning mb-1" id="totalAttempts">0</h3>
                <small class="text-muted">Total Attempts</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', async () => {
    // Check auth token
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    if (!token) {
      window.location.href = '/student/login';
      return;
    }

    try {
      // Fetch student dashboard data
      const response = await fetch('/api/student/dashboard', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      if (!response.ok) throw new Error('Failed to fetch dashboard data');
      const { data } = await response.json();
      const student = data.student;
      const stats = data.stats;

      // Update welcome message
      const welcomeEl = document.getElementById('welcomeMessage');
      if (student.name) {
        welcomeEl.innerHTML = `Welcome back, <strong>${student.name}</strong>!`;
      } else {
        welcomeEl.innerHTML = 'Welcome to your dashboard!';
        document.getElementById('incompleteProfileAlert').classList.remove('d-none');
      }

      // Update profile details
      document.getElementById('studentName').textContent = student.name || 'Not provided';
      document.getElementById('studentEmail').textContent = student.email;
      document.getElementById('studentPhone').textContent = student.phone || 'Not provided';
      
      const approvalBadge = document.getElementById('studentApproval');
      approvalBadge.textContent = student.status ? 'Complete' : 'Pending';
      approvalBadge.className = `badge bg-${student.status ? 'success' : 'warning'}`;

      // Update stats summary
      document.getElementById('totalExams').textContent = stats.total_exams_taken;
      document.getElementById('avgScore').textContent = stats.average_score + '%';
      document.getElementById('highestScore').textContent = stats.highest_score;
      document.getElementById('totalAttempts').textContent = stats.total_attempts;

    } catch (error) {
      console.error('Dashboard error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load dashboard data',
        timer: 2000,
        showConfirmButton: false
      });
    }
  });
</script>
@endsection
