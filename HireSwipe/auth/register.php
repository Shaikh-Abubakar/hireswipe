<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_user()) redirect('home.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '') $error = 'Please enter your name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (!valid_phone($phone)) $error = 'Enter a valid phone number with 10–15 digits.';
    elseif (!password_is_strong($password)) $error = 'Password must be 8+ characters and include a letter plus a number or symbol.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name,email,phone,password_hash) VALUES (?,?,?,?)');
            $stmt->execute([$name,$email,preg_replace('/[\s()-]/', '', $phone),password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int)db()->lastInsertId();
            $_SESSION['pending_user_id'] = $id;
            $_SESSION['pending_otp'] = (string)random_int(100000,999999);
            $_SESSION['otp_expires'] = time() + 300;
            redirect('auth/verify-phone.php');
        } catch (PDOException $ex) {
            $error = $ex->getCode() === '23000' ? 'That email or phone number is already registered.' : 'Unable to create account. Check your database setup.';
        }
    }
}
require __DIR__ . '/../includes/auth-layout-top.php';
?>
<div class="auth-heading"><span class="eyebrow">YOUR NEXT CHAPTER</span><h1>Create your <span>HireSwipe</span> account</h1><p>One account. Two paths. A world of opportunity.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack" data-validate>
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Full name<input name="name" autocomplete="name" placeholder="Your name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
<label>Email address<input type="email" name="email" autocomplete="email" placeholder="you@example.com" required value="<?= e($_POST['email'] ?? '') ?>"></label>
<label>Phone number<input name="phone" inputmode="tel" autocomplete="tel" placeholder="+91 98765 43210" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
<label>Password<input type="password" name="password" autocomplete="new-password" minlength="8" placeholder="8+ characters, letter + number/symbol" required></label>
<label>Confirm password<input type="password" name="confirm_password" autocomplete="new-password" placeholder="Repeat your password" required></label>
<button class="btn btn-primary btn-wide" type="submit">Create account <span>→</span></button>
</form>
<div class="oauth-row"><button class="oauth-btn" type="button" data-oauth="Google">G&nbsp; Continue with Google</button><button class="oauth-btn" type="button" data-oauth="Facebook">f&nbsp; Continue with Facebook</button></div>
<p class="auth-switch">Already have an account? <a href="<?= e(url('auth/login.php')) ?>">Sign in</a></p>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
