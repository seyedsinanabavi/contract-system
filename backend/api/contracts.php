<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
apply_cors();
start_session();

$u = require_login();
$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!in_array($u['role'], ['admin', 'team_lead'], true)) json_error('دسترسی غیرمجاز', 403);
    $rows = $db->query("
        SELECT c.*, u.full_name AS creator
        FROM contracts c
        LEFT JOIN users u ON u.id = c.created_by
        ORDER BY c.created_at DESC
    ")->fetchAll();
    json_ok(['contracts' => $rows]);
}

if ($method === 'POST') {
    if ($u['role'] !== 'admin') json_error('دسترسی غیرمجاز', 403);
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $party = trim($_POST['party_name'] ?? '');
    if ($title === '' || $party === '') json_error('عنوان و طرف قرارداد الزامی است');

    $amount = ($_POST['amount'] ?? '') !== '' ? (float)$_POST['amount'] : null;
    $sd = $_POST['start_date'] ?: null;
    $ed = $_POST['end_date'] ?: null;
    $desc = trim($_POST['description'] ?? '');

    $filePath = null; $originalName = null; $fileSize = null; $mimeType = null;

    if (!empty($_FILES['file']['tmp_name'])) {
        $f = $_FILES['file'];
        $cfg = require __DIR__ . '/../app/config.php';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $cfg['upload']['allowed'], true)) json_error('فقط PDF مجاز است');
        if ($f['size'] > $cfg['upload']['max_size']) json_error('حجم فایل زیاد است');

        $name = bin2hex(random_bytes(16)) . '.pdf';
        $dest = $cfg['upload']['dir'] . '/' . $name;
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $filePath = $dest;
            $originalName = $f['name'];
            $fileSize = $f['size'];
            $mimeType = $mime;
        }
    }

    $db->prepare("INSERT INTO contracts (title, party_name, amount, start_date, end_date, description, file_path, file_original_name, file_size, mime_type, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
       ->execute([$title, $party, $amount, $sd, $ed, $desc, $filePath, $originalName, $fileSize, $mimeType, $u['id']]);

    json_ok(['id' => (int)$db->lastInsertId()]);
}

if ($method === 'DELETE') {
    if ($u['role'] !== 'admin') json_error('دسترسی غیرمجاز', 403);
    verify_csrf();

    $body = json_body();
    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) json_error('شناسه نامعتبر');

    $stmt = $db->prepare("SELECT file_path FROM contracts WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['file_path'] && file_exists($row['file_path'])) @unlink($row['file_path']);

    $db->prepare("DELETE FROM contracts WHERE id=?")->execute([$id]);
    json_ok();
}

json_error('متد نامعتبر', 405);