<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
$id = (int)($_SESSION['pending_user_id'] ?? 0);
if (!$id) redirect('auth/register.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (time() > (int)($_SESSION['otp_expires'] ?? 0)) {
        $error = 'That code expired. Please register again to generate a new demo code.';
    } elseif (!hash_equals((string)($_SESSION['pending_otp'] ?? ''), trim($_POST['otp'] ?? ''))) {
        $error = 'That code does not match. Try again.';
    } else {
        $stmt = db()->prepare('UPDATE users SET phone_verified_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
        $user = load_user($id);
        unset($_SESSION['pending_user_id'], $_SESSION['pending_otp'], $_SESSION['otp_expires']);
        sign_in($user);
        redirect('onboarding/index.php');
    }
}
require __DIR__ . '/../includes/auth-layout-top.php';
?>
<div class="auth-heading"><span class="eyebrow">PHONE CHECK</span><h1>Verify your <span>number.</span></h1><p>Enter the six-digit code to continue. In demo mode, the code appears below.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if (OTP_DEMO_MODE): ?><div class="alert alert-demo"><strong>Demo OTP:</strong> <?= e($_SESSION['pending_otp'] ?? '') ?><br><small>For local testing only. Real SMS delivery is not configured.</small></div><?php endif; ?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>6-digit code<input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="••••••" required></label>
<button class="btn btn-primary btn-wide" type="submit">Verify and continue <span>→</span></button>
</form>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
