<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/employer-functions.php';
$user = require_employer();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
verify_csrf();
$candidateId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
$action = (string)($_POST['action'] ?? '');
$location = substr(trim((string)($_POST['return_location'] ?? '')), 0, 180);
$industry = substr(trim((string)($_POST['return_industry'] ?? '')), 0, 120);
if (!$candidateId || $candidateId === (int)$user['id'] || !in_array($action, ['interest','rejected'], true)) {
    flash('error', 'That candidate action was not valid.');
    redirect('employer/home.php');
}
$check = db()->prepare("SELECT u.id FROM users u JOIN seeker_profiles sp ON sp.user_id = u.id WHERE u.id = ? AND u.role = 'seeker' AND u.status = 'active' AND u.onboarding_complete = 1");
$check->execute([$candidateId]);
if (!$check->fetchColumn()) { flash('error', 'This candidate is no longer available.'); redirect('employer/home.php'); }
try {
    $stmt = db()->prepare('INSERT INTO candidate_interactions (employer_id, seeker_user_id, action) VALUES (?, ?, ?)');
    $stmt->execute([(int)$user['id'], $candidateId, $action]);
    flash('success', $action === 'interest' ? 'Interest saved. This candidate will not appear again in your discovery feed.' : 'Candidate passed. They will not appear again in your discovery feed.');
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') flash('success', 'You have already reviewed this candidate.');
    else { error_log('HireSwipe candidate action failed: ' . $ex->getMessage()); flash('error', 'Could not save that action. Please try again.'); }
}
$query = http_build_query(array_filter(['location'=>$location,'industry'=>$industry], static fn($v) => $v !== ''));
redirect('employer/home.php' . ($query ? '?' . $query : ''));
