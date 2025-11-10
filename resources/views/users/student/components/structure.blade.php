{{-- resources/views/layouts/admin/structure.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','Admin Dashboard')</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet"/>

  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet"/>

  <!-- Main variables & typography -->
  <link rel="stylesheet" href="{{ asset('css/common/main.css') }}"/>

  <!-- Layout styles -->
  <link rel="stylesheet" href="{{ asset('css/layout/structure.css') }}"/>

  @stack('styles')
  @yield('styles')
</head>
<body>
  <div class="layout">
    {{-- =========================  SIDEBAR  ========================= --}}
    <aside class="dashboard-sidebar" id="sidebar">
      <button id="closeSidebar" class="close-sidebar">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="sidebar-logo text-center">
        <img src="{{ asset('assets/web_assets/logo.png') }}" alt="Logo"/>
      </div>

      <nav class="sidebar-nav flex-grow-1">
        <a href="/student/dashboard" class="nav-link">
          <i class="fas fa-home"></i><span>Dashboard</span>
        </a>

        {{-- ==== Exam Management ==== --}}
        <div class="nav-group">
          <a href="#" class="nav-link group-toggle" data-target="examsMenu">
            <i class="fas fa-file-alt"></i><span>Exams</span>
            <i class="fas fa-chevron-down ms-auto"></i>
          </a>
          <div id="examsMenu" class="submenu">
            {{-- <a href="/student/exams/suggested"    class="nav-link">Suggested Exams</a> --}}
            <a href="/student/exams"    class="nav-link">Exams</a>
            <a href="/student/result"    class="nav-link">Results</a>
          </div>
        </div>

        {{-- ==== Settings ==== --}}
        <div class="nav-group">
          <a href="#" class="nav-link group-toggle" data-target="settings">
            <i class="fa fa-cog me-1"></i><span>Settings</span>
            <i class="fas fa-chevron-down ms-auto"></i>
          </a>
          <div id="settings" class="submenu">
            <a href="/student/profile"    class="nav-link">Profile</a>
          </div>
        </div>
      </nav>

      <div class="sidebar-auth p-3">
        <a href="#" id="logoutBtn" class="auth-link">
          <i class="fas fa-sign-out-alt me-2"></i><span>Logout</span>
        </a>
      </div>
    </aside>

    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    {{-- =========================  MAIN PANEL  ========================= --}}
    <div class="right-panel" id="rightPanel">
      <header class="admin-header d-flex align-items-center px-3 shadow-sm">
        <button class="btn btn-link d-block d-lg-none p-0 me-2" id="toggleSidebar">
          <i class="bi bi-list fs-3"></i>
        </button>

        {{-- Slot for page-specific heading if you need it --}}
        {{-- <h5 class="mb-0">@yield('header','Admin Area')</h5> --}}

        {{-- User dropdown --}}
        <div class="ms-auto dropdown">
          <span class="d-flex align-items-center dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user ah_usericon me-2"></i>
            <span id="userName">
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              Loading...
            </span>
          </span>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li class="dropdown-header" id="userRole">
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              Loading...
            </li>
            <li><a class="dropdown-item" href="/student/profile" id="profileLink">Profile</a></li>
            <li><a class="dropdown-item" href="#">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" id="logoutBtnMobile">Logout</a></li>
          </ul>
        </div>
      </header>

      <main class="main-content">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- =========================  SCRIPTS  ========================= --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  {{-- <script src="{{ asset('js/common/apiService.js') }}"></script> --}}

  @stack('scripts')
  @yield('scripts')

  <script>
    document.addEventListener('DOMContentLoaded', async () => {
      /* -------------------------------------------
       * 1.  USER  INFO
       * ----------------------------------------- */
      const userNameEl  = document.getElementById('userName');
      const userRoleEl  = document.getElementById('userRole');
      const profileLink = document.getElementById('profileLink');

      try {
        const token = localStorage.getItem('token') || sessionStorage.getItem('token');
        const res   = await fetch('/api/me', { headers: { 'Accept':'application/json','Authorization':`Bearer ${token}` }});
        if (res.status === 403 || res.status === 401) {
            return window.location.href = '/unauthorized';
          }
        if (!res.ok) throw new Error('Failed to fetch user info');

        const { data } = await res.json();

        userNameEl.textContent = data.user_data.name;
        userRoleEl.textContent = data.user_type.replace(/^./, c => c.toUpperCase());
        profileLink.href       = `/${data.user_type}/profile`;

        window.currentUser = data.user_data;
        window.userType    = data.user_type;
      } catch (err) {
        console.error(err);
        userNameEl.innerHTML = '<i class="fa-solid fa-user me-2"></i>User';
        userRoleEl.textContent = 'Unknown';
      }

      /* -------------------------------------------
       * 2.  SIDEBAR  LOGIC
       * ----------------------------------------- */
      const sidebar   = document.getElementById('sidebar');
      const overlay   = document.getElementById('sidebarOverlay');
      const rightPane = document.getElementById('rightPanel');

      const openSidebar  = () => { sidebar.classList.add('active'); overlay.classList.add('active'); rightPane.classList.add('shifted'); };
      const closeSidebar = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); rightPane.classList.remove('shifted'); };

      document.getElementById('toggleSidebar')?.addEventListener('click', openSidebar);
      document.getElementById('closeSidebar').addEventListener('click', closeSidebar);
      overlay.addEventListener('click', closeSidebar);

      /* Sub-menu toggles */
      document.querySelectorAll('.group-toggle').forEach(btn => {
        btn.addEventListener('click', e => {
          e.preventDefault();
          const menu = document.getElementById(btn.dataset.target);
          const icon = btn.querySelector('.fa-chevron-down, .fa-chevron-up');
          const open = menu.classList.toggle('open');
          menu.style.display = open ? 'flex' : 'none';
          icon.classList.toggle('fa-chevron-up', open);
          icon.classList.toggle('fa-chevron-down', !open);
        });
      });

      /* Highlight active link */
      (function highlight() {
        const path = window.location.pathname;
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.submenu').forEach(m => (m.classList.remove('open'), m.style.display='none'));
        document.querySelectorAll('.group-toggle .fa-chevron-down').forEach(i => (i.classList.add('fa-chevron-down'), i.classList.remove('fa-chevron-up')));

        document.querySelectorAll('.sidebar-nav .nav-link[href]').forEach(link => {
          const href = link.getAttribute('href');
          if (path === href || (path.startsWith(href) && href !== '/')) {
            link.classList.add('active');
            const group   = link.closest('.nav-group');
            if (group) {
              group.querySelector('.submenu').style.display = 'flex';
              group.querySelector('.submenu').classList.add('open');
              const icon = group.querySelector('.group-toggle .fa-chevron-down');
              icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
          }
        });
      })();

      /* -------------------------------------------
       * 3.  LOGOUT
       * ----------------------------------------- */
      const logout = async () => {
        Swal.fire({ title:'Logging out...', allowOutsideClick:false, didOpen:() => Swal.showLoading() });
        try {
          const token = localStorage.getItem('token') || sessionStorage.getItem('token');
          const res   = await fetch('/api/student/logout',{ method:'POST', headers:{ 'Content-Type':'application/json','Authorization':`Bearer ${token}` } });
          if (res.status === 403 || res.status === 401) {
            return window.location.href = '/unauthorized';
          }
          if (!res.ok) throw new Error('Logout failed');
        //   localStorage.removeItem('token'); sessionStorage.removeItem('token');
          sessionStorage.clear();
          window.location.href = '/';
        } catch (err) {
          Swal.fire('Error', err.message, 'error');
        }
      };
      ['logoutBtn','logoutBtnMobile'].forEach(id => document.getElementById(id)?.addEventListener('click', e => { e.preventDefault(); logout(); }));

      /* -------------------------------------------
       * 4.  USER DROPDOWN
       * ----------------------------------------- */
      const userBtn  = document.getElementById('userDropdown');
      const userMenu = document.querySelector('#userDropdown + .dropdown-menu');
      userBtn.addEventListener('click',  e => { e.preventDefault(); const sh = userMenu.classList.toggle('show'); userBtn.setAttribute('aria-expanded', sh); });
      document.addEventListener('click', e => { if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) { userMenu.classList.remove('show'); userBtn.setAttribute('aria-expanded', 'false'); }});
    });
  </script>
</body>
</html>
