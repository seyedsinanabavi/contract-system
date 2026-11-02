<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
apply_cors();
start_session();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $action === 'me') {
    $u = current_user();
    if (!$u) json_error('وارد نشده', 401);
    json_ok(['user' => $u, 'csrf' => csrf_token()]);
}

if ($method === 'GET' && $action === 'csrf') {
    json_ok(['csrf' => csrf_token()]);
}

if ($method === 'POST' && $action === 'login') {
    $body = json_body();
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if ($username === '' || $password === '') {
        json_error('نام کاربری و رمز الزامی است');
    }

    $r = login_user($username, $password);
    if (!$r['ok']) json_error($r['msg'], 401);

    $u = current_user();
    json_ok(['user' => $u, 'csrf' => csrf_token()]);
}

if ($method === 'POST' && $action === 'logout') {
    if (!empty($_SESSION['user_id'])) {
        db()->prepare("INSERT INTO access_logs (user_id, action, ip_address, success) VALUES (?,?,?,1)")
            ->execute([$_SESSION['user_id'], 'logout', client_ip()]);
    }
    $_SESSION = [];
    session_destroy();
    json_ok();
}

json_error('عملیات نامعتبر', 404);