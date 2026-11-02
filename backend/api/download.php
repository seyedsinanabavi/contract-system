<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
start_session();

$u = require_login();
if (!in_array($u['role'], ['admin', 'team_lead'], true)) {
    http_response_code(403);
    exit('دسترسی غیرمجاز');
}

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM contracts WHERE id=?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c || !$c['file_path'] || !file_exists($c['file_path'])) {
    http_response_code(404);
    exit('فایل یافت نشد');
}

$name = $c['file_original_name'] ?: ('contract-' . $c['id'] . '.pdf');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($name) . '"');
header('Content-Length: ' . filesize($c['file_path']));
readfile($c['file_path']);
exit;