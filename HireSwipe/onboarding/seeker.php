<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
$user = require_login();
if (($_SESSION['onboarding_role'] ?? '') !== 'seeker') redirect('onboarding/index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience = (float)($_POST['experience_years'] ?? 0);
    $salary = $_POST['expected_salary'] !== '' ? (float)$_POST['expected_salary'] : null;
    $employment = $_POST['employment_type'] ?? '';
    $industries = trim($_POST['industries'] ?? '');
    $picture = null; $cv = null;
    if ($name === '' || $location === '' || $nationality === '' || $qualification === '' || $industries === '') $error = 'Please complete all required fields.';
    elseif ($age < 16 || $age > 100) $error = 'Age must be between 16 and 100.';
    elseif ($experience < 0 || $experience > 80) $error = 'Enter a valid number of experience years.';
    elseif (!in_array($employment, ['full_time','part_time','contract','internship','temporary'], true)) $error = 'Choose a valid employment type.';
    else {
        foreach (['picture'=>['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'],'cv'=>['application/pdf'=>'pdf']] as $field=>$types) {
            if (!empty($_FILES[$field]['name'])) {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
                $max = $field === 'picture' ? 2*1024*1024 : 5*1024*1024;
                if (!isset($types[$mime]) || $_FILES[$field]['size'] > $max) { $error = $field === 'picture' ? 'Picture must be JPG, PNG or WEBP under 2 MB.' : 'CV must be a PDF under 5 MB.'; break; }
                $dir = __DIR__ . '/../uploads/' . ($field === 'picture' ? 'profiles' : 'cvs');
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $file = bin2hex(random_bytes(16)) . '.' . $types[$mime];
                if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $file)) { $error = 'File upload failed.'; break; }
                if ($field === 'picture') $picture = 'uploads/profiles/' . $file; else $cv = 'uploads/cvs/' . $file;
            }
        }
        if (!$error) {
            $pdo = db(); $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE users SET name=?, role="seeker", onboarding_complete=1 WHERE id=?')->execute([$name,$user['id']]);
                $stmt = $pdo->prepare('INSERT INTO seeker_profiles (user_id,full_name,age,location,nationality,qualification,experience_years,expected_salary,employment_type,industries,picture_path,cv_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$user['id'],$name,$age,$location,$nationality,$qualification,$experience,$salary,$employment,$industries,$picture,$cv]);
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
<div class="auth-heading"><span class="eyebrow">STEP 2 OF 2 · JOB SEEKER</span><h1>Let opportunity <span>find you.</span></h1><p>Set up your profile so your next move starts with the right details.</p></div>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-stack"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Full name *<input name="full_name" required value="<?= e($_POST['full_name'] ?? $user['name']) ?>" placeholder="Your full name"></label>
<div class="form-grid"><label>Age *<input type="number" name="age" min="16" max="100" required value="<?= e($_POST['age'] ?? '') ?>" placeholder="22"></label><label>Nationality *<input name="nationality" required value="<?= e($_POST['nationality'] ?? '') ?>" placeholder="Indian"></label></div>
<label>Location *<input name="location" required value="<?= e($_POST['location'] ?? '') ?>" placeholder="Ahmedabad, India"></label>
<label>Qualification *<input name="qualification" required value="<?= e($_POST['qualification'] ?? '') ?>" placeholder="B.Tech, BCA, Diploma..."></label>
<div class="form-grid"><label>Experience (years) *<input type="number" name="experience_years" min="0" max="80" step="0.5" required value="<?= e($_POST['experience_years'] ?? '0') ?>"></label><label>Expected salary<input type="number" name="expected_salary" min="0" step="0.01" value="<?= e($_POST['expected_salary'] ?? '') ?>" placeholder="Annual amount"></label></div>
<label>Employment type *<select name="employment_type" required><option value="">Choose type</option><?php foreach (['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','temporary'=>'Temporary'] as $v=>$label): ?><option value="<?= e($v) ?>" <?= ($_POST['employment_type'] ?? '')===$v?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>Industries of interest *<input name="industries" required value="<?= e($_POST['industries'] ?? '') ?>" placeholder="Technology, education, design"></label>
<label>Profile picture <span class="field-note">(JPG, PNG or WEBP · max 2 MB)</span><input type="file" name="picture" accept="image/jpeg,image/png,image/webp"></label>
<label>CV / Resume <span class="field-note">(PDF · max 5 MB)</span><input type="file" name="cv" accept="application/pdf"></label>
<button class="btn btn-primary btn-wide" type="submit">Finish setup <span>→</span></button></form>
<?php require __DIR__ . '/../includes/auth-layout-bottom.php'; ?>
