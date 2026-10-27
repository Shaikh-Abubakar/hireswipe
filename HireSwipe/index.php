<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('home.php');
redirect('auth/register.php');
