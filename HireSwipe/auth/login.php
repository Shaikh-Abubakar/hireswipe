<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_user()) redirect('home.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id,password_hash FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $phone = preg_replace('/[\s()-]/', '', $identity);
    $stmt->execute([strtolower($identity), $phone]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        $error = 'The email/phone or password is incorrect.';
    } else {
        $user = load_user((int)$row['id']);
        if ($user && !$user['onboarding_complete']) {
            sign_in($user);
            redirect('onboarding/index.php');
        }
        sign_in($user);
        redirect('home.php');
    }
}
require __DIR__ . '/../includes/auth-layout-top.php';
?>
<div class="auth-heading"><span class="eyebrow">WELCOME BACK</span><h1>Good to see <span>you.</span></h1><p>Sign in and pick up where your next chapter begins.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Email or phone number<input name="identity" autocomplete="username" placeholder="Email address or phone" required value="<?= e($_POST['identity'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" autocomplete="current-password" placeholder="Your password" required></label>
<button class="btn btn-primary btn-wide" type="submit">Sign in <span>→</span></button>
</form>
<div class="oauth-row"><button class="oauth-btn" type="button" data-oauth="Google">G&nbsp; Continue with Google</button><button class="oauth-btn" type="button" data-oauth="Facebook">f&nbsp; Continue with Facebook</button></div>
<p class="auth-switch">New to HireSwipe? <a href="<?= e(url('auth/register.php')) ?>">Create an account</a></p>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
