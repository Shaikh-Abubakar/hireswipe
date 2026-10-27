<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login();
if (($_SESSION['onboarding_role'] ?? '') !== 'employer') redirect('onboarding/index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $company = trim($_POST['company_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $employment = $_POST['employment_type'] ?? '';
    $salaryMin = $_POST['salary_min'] !== '' ? (float)$_POST['salary_min'] : null;
    $salaryMax = $_POST['salary_max'] !== '' ? (float)$_POST['salary_max'] : null;
    $details = trim($_POST['job_details'] ?? '');
    $logo = null;
    if ($company === '' || $location === '' || $industry === '' || $position === '' || $details === '') $error = 'Please complete all required fields.';
    elseif (!in_array($employment, ['full_time','part_time','contract','internship','temporary'], true)) $error = 'Choose a valid employment type.';
    elseif ($salaryMin !== null && $salaryMax !== null && $salaryMax < $salaryMin) $error = 'Maximum salary must be greater than or equal to minimum salary.';
    else {
        if (!empty($_FILES['logo']['name'])) {
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['logo']['tmp_name']);
            if (!isset($allowed[$mime]) || $_FILES['logo']['size'] > 2*1024*1024) $error = 'Logo must be JPG, PNG or WEBP and under 2 MB.';
            else {
                $dir = __DIR__ . '/../uploads/logos';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $file = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $dir . '/' . $file)) $logo = 'uploads/logos/' . $file;
                else $error = 'Could not upload the logo.';
            }
        }
        if (!$error) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO employer_profiles (user_id,company_name,location,industry,position,employment_type,salary_min,salary_max,job_details,logo_path) VALUES (?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$user['id'],$company,$location,$industry,$position,$employment,$salaryMin,$salaryMax,$details,$logo]);
                $pdo->prepare("UPDATE users SET role='employer', onboarding_complete=1 WHERE id=?")->execute([$user['id']]);
                $pdo->commit();
                unset($_SESSION['onboarding_role']);
                sign_in(load_user((int)$user['id']));
                redirect('home.php');
            } catch (Throwable $ex) { $pdo->rollBack(); $error = 'Could not save your profile. Check the database schema and try again.'; }
        }
    }
}
require __DIR__ . '/../includes/auth-layout-top.php';
?>
<div class="auth-heading"><span class="eyebrow">STEP 2 OF 2 · EMPLOYER</span><h1>Build your <span>company profile.</span></h1><p>Tell people what you're hiring for. Fields marked * are required.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-stack"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Company name *<input name="company_name" required value="<?= e($_POST['company_name'] ?? '') ?>" placeholder="Acme Studio"></label>
<div class="form-grid"><label>Location *<input name="location" required value="<?= e($_POST['location'] ?? '') ?>" placeholder="Ahmedabad, India"></label><label>Industry *<input name="industry" required value="<?= e($_POST['industry'] ?? '') ?>" placeholder="Technology"></label></div>
<label>Position hiring for *<input name="position" required value="<?= e($_POST['position'] ?? '') ?>" placeholder="PHP Developer"></label>
<label>Employment type *<select name="employment_type" required><option value="">Choose type</option><?php foreach (['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','temporary'=>'Temporary'] as $v=>$label): ?><option value="<?= e($v) ?>" <?= ($_POST['employment_type'] ?? '')===$v?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<div class="form-grid"><label>Minimum salary<input type="number" min="0" step="0.01" name="salary_min" value="<?= e($_POST['salary_min'] ?? '') ?>" placeholder="30000"></label><label>Maximum salary<input type="number" min="0" step="0.01" name="salary_max" value="<?= e($_POST['salary_max'] ?? '') ?>" placeholder="60000"></label></div>
<label>Job details *<textarea name="job_details" rows="4" required placeholder="Describe the role, responsibilities and ideal candidate..."><?= e($_POST['job_details'] ?? '') ?></textarea></label>
<label>Company logo <span class="field-note">(JPG, PNG or WEBP · max 2 MB)</span><input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
<button class="btn btn-primary btn-wide" type="submit">Finish setup <span>→</span></button></form>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
