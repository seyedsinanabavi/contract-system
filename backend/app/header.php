    <?php
$u = current_user();
$current = basename($_SERVER['PHP_SELF']);
$roleLabels = ['admin' => 'مدیر', 'team_lead' => 'سر تیم', 'user' => 'کاربر'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'داشبورد') ?> | سیستم مدیریت</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <i class="fa-solid fa-briefcase"></i>
    <span>مدیریت قرارداد</span>
  </div>

  <ul class="sidebar-menu">
    <li>
      <a href="/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">
        <i class="fa-solid fa-gauge-high"></i>
        <span>داشبورد</span>
      </a>
    </li>

    <?php if (in_array($u['role'], ['admin','team_lead'], true)): ?>
    <li>
      <a href="/contracts.php" class="<?= $current==='contracts.php'?'active':'' ?>">
        <i class="fa-solid fa-file-contract"></i>
        <span>قراردادها</span>
      </a>
    </li>
    <?php endif; ?>

    <li>
      <a href="/reports.php" class="<?= $current==='reports.php'?'active':'' ?>">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>گزارش‌های کار</span>
      </a>
    </li>

    <?php if ($u['role'] === 'admin'): ?>
    <li>
      <a href="/users.php" class="<?= $current==='users.php'?'active':'' ?>">
        <i class="fa-solid fa-users-gear"></i>
        <span>کاربران</span>
      </a>
    </li>
    <?php endif; ?>

    <li class="mt-auto">
      <a href="/logout.php" class="text-danger">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>خروج</span>
      </a>
    </li>
  </ul>
</aside>

<div class="main-wrapper">
  <header class="topbar">
    <button class="btn btn-icon" id="toggleSidebar"><i class="fa-solid fa-bars"></i></button>
    <h5 class="topbar-title mb-0"><?= e($pageTitle ?? 'داشبورد') ?></h5>

    <div class="topbar-actions">
      <button class="btn btn-icon" id="themeToggle" title="حالت شب/روز">
        <i class="fa-solid fa-moon"></i>
      </button>

      <div class="dropdown">
        <button class="user-chip" data-bs-toggle="dropdown">
          <div class="avatar"><?= e(mb_substr($u['full_name'], 0, 1)) ?></div>
          <div class="user-info">
            <div class="user-name"><?= e($u['full_name']) ?></div>
            <div class="user-role"><?= e($roleLabels[$u['role']]) ?></div>
          </div>
          <i class="fa-solid fa-chevron-down"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="/logout.php"><i class="fa-solid fa-right-from-bracket"></i> خروج</a></li>
        </ul>
      </div>
    </div>
  </header>

  <main class="content">
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show">
        <i class="fa-solid fa-circle-info"></i> <?= e($_SESSION['flash']['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>