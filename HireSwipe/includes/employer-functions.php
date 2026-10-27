<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function require_employer(): array {
    $user = require_login();
    if (($user['role'] ?? '') !== 'employer' || empty($user['onboarding_complete'])) {
        if (($user['role'] ?? '') === 'seeker') redirect('home.php');
        redirect('onboarding/index.php');
    }
    $stmt = db()->prepare("SELECT id FROM users WHERE id = ? AND role = 'employer' AND onboarding_complete = 1 AND status = 'active'");
    $stmt->execute([(int)$user['id']]);
    if (!$stmt->fetchColumn()) { unset($_SESSION['user']); redirect('auth/login.php'); }
    return $user;
}
function employment_label(?string $value): string {
    return ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','temporary'=>'Temporary'][$value ?? ''] ?? 'Not specified';
}
function salary_label(mixed $salary): string {
    if ($salary === null || $salary === '') return 'Not specified';
    $n = (float)$salary;
    if ($n >= 100000) return '₹' . rtrim(rtrim(number_format($n / 100000, 2), '0'), '.') . ' LPA';
    return '₹' . number_format($n, 0) . ' / year';
}
function candidate_query(int $employerId, string $location = '', string $industry = '', ?int $candidateId = null): PDOStatement {
    $sql = "SELECT sp.*, u.id AS candidate_user_id, u.name AS account_name, u.status
            FROM seeker_profiles sp JOIN users u ON u.id = sp.user_id
            WHERE u.role = 'seeker' AND u.onboarding_complete = 1 AND u.status = 'active'
              AND NOT EXISTS (SELECT 1 FROM candidate_interactions ci WHERE ci.employer_id = ? AND ci.seeker_user_id = u.id)";
    $params = [$employerId];
    if ($location !== '') { $sql .= ' AND sp.location LIKE ?'; $params[] = '%' . $location . '%'; }
    if ($industry !== '') { $sql .= ' AND sp.industries LIKE ?'; $params[] = '%' . $industry . '%'; }
    if ($candidateId !== null) { $sql .= ' AND u.id = ?'; $params[] = $candidateId; }
    $sql .= ' ORDER BY sp.created_at DESC, sp.id DESC LIMIT 1';
    $stmt = db()->prepare($sql); $stmt->execute($params); return $stmt;
}
function profile_photo_url(?string $path): string {
    if (!$path) return url('assets/images/default-profile.svg');
    $path = str_replace('\\', '/', $path);
    if (str_contains($path, '..') || str_starts_with($path, '/') || !preg_match('~^uploads/profiles/[a-zA-Z0-9._-]+$~', $path)) return url('assets/images/default-profile.svg');
    return url($path);
}
