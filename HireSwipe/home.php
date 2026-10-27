<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();
if (($user['role'] ?? '') === 'employer' && !empty($user['onboarding_complete'])) redirect('employer/home.php');
if (empty($user['onboarding_complete'])) redirect('onboarding/index.php');
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Home — HireSwipe</title><link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>"></head><body class="blank-home"><header class="topbar"><a class="brand" href="<?= e(url('home.php')) ?>"><span class="brand-mark">H</span>HireSwipe</a><div class="top-actions"><span class="user-chip"><?= e($user['name'] ?: $user['email']) ?></span><a class="btn btn-small btn-ghost" href="<?= e(url('auth/logout.php')) ?>">Sign out</a></div></header><main class="blank-canvas"><h1>Your HireSwipe profile is ready.</h1><p>Employer discovery is available to employer accounts.</p></main></body></html>
