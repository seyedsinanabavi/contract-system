<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
apply_cors();
start_session();

$u = require_login();
$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (in_array($u['role'], ['admin', 'team_lead'], true)) {
        $rows = $db->query("
            SELECT r.*, u.full_name AS author, rv.full_name AS reviewer
            FROM work_reports r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN users rv ON rv.id = r.reviewed_by
            ORDER BY r.created_at DESC
        ")->fetchAll();
    } else {
        $stmt = $db->prepare("
            SELECT r.*, rv.full_name AS reviewer
            FROM work_reports r
            LEFT JOIN users rv ON rv.id = r.reviewed_by
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$u['id']]);
        $rows = $stmt->fetchAll();
    }
    json_ok(['reports' => $rows]);
}

if ($method === 'POST') {
    verify_csrf();
    $body = json_body();
    $action = $body['action'] ?? 'create';

    if ($action === 'create') {
        $title = trim($body['title'] ?? '');
        $desc = trim($body['description'] ?? '');
        $date = $body['work_date'] ?? '';
        $hours = ($body['hours_spent'] ?? '') !== '' ? (float)$body['hours_spent'] : null;

        if ($title === '' || $desc === '' || $date === '') json_error('فیلدهای الزامی خالی است');

        $db->prepare("INSERT INTO work_reports (user_id, title, description, work_date, hours_spent) VALUES (?,?,?,?,?)")
           ->execute([$u['id'], $title, $desc, $date, $hours]);
        json_ok(['id' => (int)$db->lastInsertId()]);
    }

    if ($action === 'review') {
        if (!in_array($u['role'], ['admin', 'team_lead'], true)) json_error('دسترسی غیرمجاز', 403);
        $id = (int)($body['id'] ?? 0);
        $status = $body['status'] ?? '';
        $note = trim($body['review_note'] ?? '');

        if (!in_array($status, ['approved', 'rejected'], true)) json_error('وضعیت نامعتبر');

        $db->prepare("UPDATE work_reports SET status=?, reviewed_by=?, review_note=?, reviewed_at=NOW() WHERE id=?")
           ->execute([$status, $u['id'], $note, $id]);
        json_ok();
    }

    json_error('اکشن نامعتبر');
}

json_error('متد نامعتبر', 405);