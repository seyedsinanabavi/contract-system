// آدرس بک‌اند
const API_BASE = 'http://localhost/contract-system/backend/api';

let csrfToken = '';

async function apiFetch(endpoint, options = {}) {
  const opts = {
    credentials: 'include',
    headers: {},
    ...options,
  };

  // برای JSON
  if (opts.body && !(opts.body instanceof FormData)) {
    opts.headers['Content-Type'] = 'application/json';
    if (typeof opts.body === 'object') opts.body = JSON.stringify(opts.body);
  }

  // CSRF
  if (opts.method && opts.method !== 'GET' && csrfToken) {
    opts.headers['X-CSRF-Token'] = csrfToken;
  }

  const url = `${API_BASE}${endpoint}`;
  const res = await fetch(url, opts);
  const data = await res.json().catch(() => ({ ok: false, error: 'پاسخ نامعتبر' }));

  if (!res.ok || !data.ok) {
    const err = new Error(data.error || 'خطا در ارتباط با سرور');
    err.status = res.status;
    throw err;
  }

  if (data.data && data.data.csrf) csrfToken = data.data.csrf;
  return data.data;
}

async function requireAuth() {
  try {
    const data = await apiFetch('/auth.php?action=me');
    if (data.csrf) csrfToken = data.csrf;
    return data.user;
  } catch (e) {
    location.href = '/index.html';
    throw e;
  }
}

function logout() {
  apiFetch('/auth.php?action=logout', { method: 'POST' })
    .finally(() => location.href = '/index.html');
}