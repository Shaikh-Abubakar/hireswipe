# HireSwipe — PHP + MySQL starter

A responsive, animated job-portal starter with employer and job-seeker registration, login, onboarding, logout, database schema, and blank role-specific home pages.

## Requirements
- PHP 8.1+
- MySQL 8+ / MariaDB
- Apache (XAMPP/Laragon works)
- PHP PDO MySQL extension

## Setup
1. Extract `HireSwipe.zip` into your web root, e.g. `C:\xampp\htdocs\HireSwipe`.
2. Create a MySQL database by importing `database/schema.sql` in phpMyAdmin.
3. Open `config/config.php` and set your database username/password.
4. Visit `http://localhost/HireSwipe/`.
5. Register, choose Employer or Job Seeker, then complete onboarding.

## Important production notes
- The included OTP flow is a **development demo**: it displays a generated OTP on screen. It does not send SMS. Connect an SMS provider (such as Twilio or an Indian SMS gateway) before production.
- Google/Facebook buttons are UI placeholders. OAuth requires provider credentials, redirect URLs, and a vetted OAuth implementation; do not treat these buttons as working authentication.
- Email is format-validated but no verification email is sent yet.
- Use HTTPS and configure production secrets, mail/SMS delivery, rate limiting, CSRF review, logging, and privacy/retention rules before deployment.
- The demo OTP is stored in the PHP session and is not suitable for production.
