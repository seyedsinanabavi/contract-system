<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
apply_cors();
start_session();

$u = require_role(['admin']);
$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $users = $db->query("SELECT id, full_name, username, email, role, team_id, is_active, last_login, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    $teams = $db->query("SELECT id, name FROM teams ORDER BY name")->fetchAll();
    json_ok(['users' => $users, 'teams' => $teams]);
}

if ($method === 'POST') {
    verify_csrf();
    $body = json_body();
    $action = $body['action'] ?? 'create';

    if ($action === 'create') {
        $full = trim($body['full_name'] ?? '');
        $user = trim($body['username'] ?? '');
        $email = trim($body['email'] ?? '');
        $pass = $body['password'] ?? '';
        $role = $body['role'] ?? 'user';
        $team = !empty($body['team_id']) ? (int)$body['team_id'] : null;

        if ($full === '' || $user === '' || $email === '' || strlen($pass) < 8) {
            json_error('همه فیلدها الزامی است و رمز حداقل 8 کاراکتر');
        }
        if (!in_array($role, ['admin','team_lead','user'], true)) json_error('نقش نامعتبر');

        try {
            $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("INSERT INTO users (full_name, username, email, password_hash, role, team_id) VALUES (?,?,?,?,?,?)")
               ->execute([$full, $user, $email, $hash, $role, $team]);
            json_ok(['id' => (int)$db->lastInsertId()]);
        } catch (PDOException $e) {
            json_error('نام کاربری یا ایمیل تکراری است');
        }
    }

    if ($action === 'toggle') {
        $id = (int)($body['id'] ?? 0);
        if ($id === (int)$u['id']) json_error('نمی‌توانید خودتان را غیرفعال کنید');
        $db->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        json_ok();
    }

    if ($action === 'role') {
        $id = (int)($body['id'] ?? 0);
        $role = $body['role'] ?? '';
        if (!in_array($role, ['admin','team_lead','user'], true)) json_error('نقش نامعتبر');
        if ($id === (int)$u['id']) json_error('نمی‌توانید نقش خودتان را تغییر دهید');
        $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $id]);
        json_ok();
    }

    json_error('اکشن نامعتبر');
}

json_error('متد نامعتبر', 405);