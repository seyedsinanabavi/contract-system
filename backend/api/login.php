<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (current_user()) redirect('/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'نام کاربری و رمز عبور الزامی است';
    } else {
        $r = login_user($username, $password);
        if ($r['ok']) redirect('/dashboard.php');
        $error = $r['msg'];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود | سیستم مدیریت</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="login-logo">
      <i class="fa-solid fa-briefcase"></i>
    </div>
    <h1>ورود به سیستم</h1>
    <p class="subtitle">مدیریت قرارداد و گزارش کار</p>

    <?php if ($error): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label small fw-semibold">نام کاربری</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fa-solid fa-user text-muted"></i></span>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold">رمز عبور</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fa-solid fa-lock text-muted"></i></span>
          <input type="password" name="password" id="password" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
            <i class="fa-solid fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="fa-solid fa-right-to-bracket"></i>
        ورود
      </button>
    </form>
  </div>
</div>

<script>
function togglePass() {
  const input = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>
</body>
</html>