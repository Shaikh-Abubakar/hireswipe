<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login();
if ($user['onboarding_complete']) redirect('home.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $role = $_POST['role'] ?? '';
    if (!in_array($role, ['employer','seeker'], true)) $error = 'Choose one option to continue.';
    else {
        $_SESSION['onboarding_role'] = $role;
        redirect($role === 'employer' ? 'onboarding/employer.php' : 'onboarding/seeker.php');
    }
}
require __DIR__ . '/../includes/auth-layout-top.php';
?>
<div class="auth-heading"><span class="eyebrow">STEP 1 OF 2</span><h1>What brings you <span>here?</span></h1><p>Choose your path. We'll tailor the setup to you.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="role-options"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label class="role-card"><input type="radio" name="role" value="employer" required><span class="role-emoji">✦</span><span class="role-title">Looking for an employee</span><span class="role-desc">Build your company profile and publish opportunities.</span><span class="role-arrow">↗</span></label>
<label class="role-card"><input type="radio" name="role" value="seeker" required><span class="role-emoji">↗</span><span class="role-title">Looking for work</span><span class="role-desc">Show your strengths and find a role that fits.</span><span class="role-arrow">↗</span></label>
<button class="btn btn-primary btn-wide" type="submit">Continue <span>→</span></button></form>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
