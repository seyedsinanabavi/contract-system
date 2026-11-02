<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/auth.php';
apply_cors();
start_session();

$u = require_login();
$db = db();

if ($u['role'] === 'admin') {
    $contracts = (int)$db->query("SELECT COUNT(*) FROM contracts")->fetchColumn();
    $pending   = (int)$db->query("SELECT COUNT(*) FROM work_reports WHERE status='pending'")->fetchColumn();
    $users     = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
    $approved  = (int)$db->query("SELECT COUNT(*) FROM work_reports WHERE status='approved'")->fetchColumn();
} elseif ($u['role'] === 'team_lead') {
    $contracts = (int)$db->query("SELECT COUNT(*) FROM contracts")->fetchColumn();
    $pending   = (int)$db->query("SELECT COUNT(*) FROM work_reports WHERE status='pending'")->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE team_id = ?");
    $stmt->execute([$u['team_id']]);
    $users = (int)$stmt->fetchColumn();
    $approved = (int)$db->query("SELECT COUNT(*) FROM work_reports WHERE status='approved'")->fetchColumn();
} else {
    $contracts = 0; $users = 0;
    $stmt = $db->prepare("SELECT COUNT(*) FROM work_reports WHERE user_id=? AND status='pending'");
    $stmt->execute([$u['id']]); $pending = (int)$stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM work_reports WHERE user_id=? AND status='approved'");
    $stmt->execute([$u['id']]); $approved = (int)$stmt->fetchColumn();
}

$chartQuery = "SELECT DATE(work_date) AS d, COUNT(*) AS c FROM work_reports WHERE work_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$params = [];
if ($u['role'] === 'user') { $chartQuery .= " AND user_id = ?"; $params[] = $u['id']; }
$chartQuery .= " GROUP BY DATE(work_date) ORDER BY d";

$stmt = $db->prepare($chartQuery);
$stmt->execute($params);
$chartData = $stmt->fetchAll();

$days = []; $counts = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('m/d', strtotime($day));
    $found = 0;
    foreach ($chartData as $row) if ($row['d'] === $day) { $found = (int)$row['c']; break; }
    $counts[] = $found;
}

json_ok([
    'stats' => [
        'contracts' => $contracts,
        'pending'   => $pending,
        'approved'  => $approved,
        'users'     => $users,
    ],
    'chart' => ['days' => $days, 'counts' => $counts],
]);