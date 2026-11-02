// ساخت سایدبار و تاپ‌بار مشترک
function renderLayout(user, activePage = '') {
  const roleLabels = { admin: 'مدیر', team_lead: 'سر تیم', user: 'کاربر' };

  const sidebarHTML = `
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <i class="fa-solid fa-briefcase"></i>
        <span>مدیریت قرارداد</span>
      </div>
      <ul class="sidebar-menu">
        <li><a href="/dashboard.html" class="${activePage === 'dashboard' ? 'active' : ''}">
          <i class="fa-solid fa-gauge-high"></i><span>داشبورد</span></a></li>
        ${['admin', 'team_lead'].includes(user.role) ? `
          <li><a href="/contracts.html" class="${activePage === 'contracts' ? 'active' : ''}">
            <i class="fa-solid fa-file-contract"></i><span>قراردادها</span></a></li>
        ` : ''}
        <li><a href="/reports.html" class="${activePage === 'reports' ? 'active' : ''}">
          <i class="fa-solid fa-clipboard-list"></i><span>گزارش‌های کار</span></a></li>
        ${user.role === 'admin' ? `
          <li><a href="/users.html" class="${activePage === 'users' ? 'active' : ''}">
            <i class="fa-solid fa-users-gear"></i><span>کاربران</span></a></li>
        ` : ''}
        <li class="mt-3"><a href="#" class="text-danger" onclick="logout();return false">
          <i class="fa-solid fa-right-from-bracket"></i><span>خروج</span></a></li>
      </ul>
    </aside>
  `;

  const topbarHTML = `
    <div class="topbar">
      <button class="btn-icon" id="toggleSidebar"><i class="fa-solid fa-bars"></i></button>
      <h1>${document.title.split('|')[0].trim()}</h1>
      <div class="topbar-actions">
        <button class="btn-icon" id="themeToggle" title="تغییر تم">
          <i class="fa-solid fa-moon"></i>
        </button>
        <div class="user-chip">
          <div class="avatar">${user.full_name.charAt(0)}</div>
          <div class="user-info">
            <div class="user-name">${user.full_name}</div>
            <div class="user-role">${roleLabels[user.role]}</div>
          </div>
        </div>
      </div>
    </div>
  `;

  document.getElementById('app').insertAdjacentHTML('afterbegin', sidebarHTML + `
    <div class="main-wrapper">
      ${topbarHTML}
      <main class="content" id="content"></main>
      <footer class="footer">© ${new Date().getFullYear()} سیستم مدیریت قرارداد و گزارش کار</footer>
    </div>
  `);

  setupLayoutEvents();
}

function setupLayoutEvents() {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('toggleSidebar');
  const backdrop = document.createElement('div');
  backdrop.className = 'sidebar-backdrop';
  document.body.appendChild(backdrop);

  if (toggle) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      backdrop.classList.toggle('show');
    });
  }
  backdrop.addEventListener('click', () => {
    sidebar.classList.remove('open');
    backdrop.classList.remove('show');
  });

  // theme
  const themeToggle = document.getElementById('themeToggle');
  const saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  updateIcon(saved);

  themeToggle.addEventListener('click', () => {
    const cur = document.documentElement.getAttribute('data-theme');
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    updateIcon(next);
  });

  function updateIcon(t) {
    themeToggle.querySelector('i').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  }
}

function showToast(msg, type = 'success') {
  Swal.fire({
    toast: true, position: 'top-start', icon: type, title: msg,
    showConfirmButton: false, timer: 3000, timerProgressBar: true,
  });
}

function confirmAction(msg, onConfirm) {
  Swal.fire({
    title: 'مطمئنی؟', text: msg, icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'بله', cancelButtonText: 'انصراف',
    confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b',
    reverseButtons: true,
  }).then(r => { if (r.isConfirmed) onConfirm(); });
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}