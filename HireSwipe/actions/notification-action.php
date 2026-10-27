<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/employer-functions.php';
$user = require_employer();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'notification_id', FILTER_VALIDATE_INT);
if ($id) { $stmt = db()->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?'); $stmt->execute([$id,(int)$user['id']]); }
redirect('employer/notifications.php');
