@extends('users.student.components.structure')

@section('title','Student Profile')
@section('header','Profile')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/pages/student/profile.css') }}"/>
@endpush

@section('content')
<div class="container py-5">
  <div id="profile-container" class="position-relative d-flex justify-content-center">
    <div class="blob blob1"></div>
    <div class="blob blob2"></div>

    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading…</span>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const token        = sessionStorage.student_token || sessionStorage.token;
  const studentId    = sessionStorage.student_id;
  const defaultPhoto = "{{ asset('assets/student/photos/profileImg.png') }}";
  const storageBase  = "{{ asset('storage') }}";

  if (!token || !studentId) {
    return window.location.href = '/student/login';
  }

  try {
    const res  = await fetch(`/api/student/details?student_id=${studentId}`, {
      headers: { 'Accept':'application/json', 'Authorization':`Bearer ${token}` }
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Failed to load profile');

    const s = json.student;
    const photoUrl = s.photo ? `${storageBase}/${s.photo}` : defaultPhoto;
    const freeLeft = Math.max(0, (s.free_exam_attempts || 0) - (s.current_attempt_count || 0));

    document.getElementById('profile-container').innerHTML = `
      <div class="card glass-effect profile-card">
        <div class="card-header gradient-bg text-center py-3">
          <span class="h5 text-white mb-0">Your Profile</span>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <!-- Left column: photo + name -->
            <div class="col-12 col-md-4 text-center">
              <img src="${photoUrl}"
                   alt="Profile Photo"
                   class="rounded-circle profile-img mb-3">
              <div class="h6 text-dark">${s.name
                  ? `<div class="h6 text-dark">${s.name}</div>`
                  : `<a href="/student/register" class="btn btn-primary">Complete Profile</a>`
              }</div>
            </div>
            <!-- Right column: details table -->
            <div class="col-12 col-md-8">
              <div class="table-responsive">
                <table class="profile-table table table-hover table-striped table-borderless">
                  <tbody>
                    <tr>
                      <th>
                        <i class="fa fa-envelope"></i>
                        <span class="label-text">Email</span>
                      </th>
                      <td>${s.email || '—'}</td>
                    </tr>
                    <tr>
                      <th>
                        <i class="fa fa-phone"></i>
                        <span class="label-text">Phone</span>
                      </th>
                      <td>${s.phone || '—'}</td>
                    </tr>
                    ${s.date_of_birth ? `
                    <tr>
                      <th>
                        <i class="fa fa-calendar"></i>
                        <span class="label-text">D.O.B</span>
                      </th>
                      <td>${new Date(s.date_of_birth).toLocaleDateString()}</td>
                    </tr>` : ''}
                    ${s.place_of_birth ? `
                    <tr>
                      <th>
                        <i class="fa fa-map-marker-alt"></i>
                        <span class="label-text">Birthplace</span>
                      </th>
                      <td>${s.place_of_birth}</td>
                    </tr>` : ''}
                    ${s.department ? `
                    <tr>
                      <th>
                        <i class="fa fa-building"></i>
                        <span class="label-text">Department</span>
                      </th>
                      <td>${s.department}</td>
                    </tr>` : ''}
                    ${s.course ? `
                    <tr>
                      <th>
                        <i class="fa fa-book"></i>
                        <span class="label-text">Course</span>
                      </th>
                      <td>${s.course}</td>
                    </tr>` : ''}
                    <tr>
                      <th>
                        <i class="fa fa-map"></i>
                        <span class="label-text">Address</span>
                      </th>
                       <td>
                        ${s.po || s.ps ? `${s.po || ''}${(s.po && s.ps) ? ', ' : ''}${s.ps || ''}<br>` : ''}
                        ${s.city || s.state ? `${s.city || ''}${(s.city && s.state) ? ', ' : ''}${s.state || ''}<br>` : ''}
                        ${s.country || s.pin ? `${s.country || ''}${(s.country && s.pin) ? ' — ' : ''}${s.pin || ''}` : ''}
                        ${!(s.po||s.ps||s.city||s.state||s.country||s.pin) ? '<span class="text-danger">Not provided</span>': ''}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        <i class="fa fa-hourglass-half"></i>
                        <span class="label-text">Free Attempts</span>
                      </th>
                      <td>${freeLeft}</td>
                    </tr>
                    <tr>
                      <th>
                        <i class="fa fa-check-circle"></i>
                        <span class="label-text">Subscribed</span>
                      </th>
                      <td>
                        ${s.is_subscribed
                          ? '<span class="badge badge-paid">Yes</span>'
                          : '<a href="/getmembership" class="btn btn-sm btn-outline-primary">Get Membership</a>'}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        <i class="fa fa-toggle-on"></i>
                        <span class="label-text">Status</span>
                      </th>
                      <td>
                        <span class="status-indicator ${
                          s.status==='active'?'status-active':'status-inactive'
                        }"></span>
                        ${(s.status||'').replace(/^./, c=>c.toUpperCase())}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="mt-4 text-center">
            <a href="/student/edit-profile" class="btn action-btn btn-outline-primary me-2">
              <i class="fa fa-edit me-1"></i>Edit Profile
            </a>
            <a href="/" class="btn action-btn btn-secondary">
              <i class="fa fa-home me-1"></i>Home
            </a>
          </div>
        </div>
      </div>
    `;
  } catch (err) {
    console.error(err);
    document.getElementById('profile-container').innerHTML = `
      <div class="alert alert-danger text-center">
        <i class="fa fa-exclamation-circle me-2"></i>${err.message}
      </div>
    `;
  }
});
</script>
@endsection
