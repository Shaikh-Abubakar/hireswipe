# HireSwipe Employer Discovery

This feature extends the existing HireSwipe PHP/MySQL application. It uses the existing `users` and `seeker_profiles` tables and does not create a second candidate database. No JavaScript is used by the new employer feature; forms submit to PHP and CSS handles visual transitions.

## Setup

1. Back up the `hireswipe` database.
2. Import `database/employer-features.sql` into the existing database. In phpMyAdmin, select the `hireswipe` database, choose **Import**, select `database/employer-features.sql`, and run it. Alternatively, from a terminal run:

   ```bash
   mysql -u root -p hireswipe < database/employer-features.sql
   ```

   Omit `-p` if your local MySQL root account has no password. The migration creates only `candidate_interactions` and `notifications`.
3. Check database settings in `config/config.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) and `BASE_URL`. The provided base URL assumes the folder is served as `/HireSwipe`.
4. Serve the folder using Apache/PHP and MySQL, sign in as an employer whose onboarding is complete, and open `http://localhost/HireSwipe/employer/home.php` (adjust host/path to your installation).
5. To see candidate cards, create and complete at least one seeker account with the existing onboarding flow. The discovery feed excludes only candidates that this employer has already reviewed.

## Employer pages

- `employer/home.php`: one-at-a-time discovery card and X / tick actions.
- `employer/search.php`: optional location and industry filters.
- `employer/candidate-profile.php?id=<candidate-user-id>`: full candidate profile.
- `employer/notifications.php`: employer-specific notifications and mark-as-read controls.
- `actions/candidate-action.php`: authenticated, CSRF-protected candidate decision endpoint.
- `actions/notification-action.php`: authenticated, CSRF-protected notification read endpoint.

## Behavior and security

- TRUE is stored as `interest`; FALSE is stored as `rejected`.
- A unique database constraint prevents a second decision for the same employer/candidate pair.
- Candidate history is scoped to the employer's user ID. Other employers still see the candidate.
- Search uses both filters together when both are entered and excludes already-reviewed candidates.
- The interaction handler accepts POST only, validates the candidate, checks the action and CSRF token, and obtains employer identity from the authenticated session.
- Profile image paths are restricted to generated filenames under `uploads/profiles`; missing/invalid paths use `assets/images/default-profile.svg`.
- Notification pages read only rows owned by the authenticated user.

## CSS-only interaction limitation

The X and tick forms are fully server-submitted controls. Native swipe detection and instantaneous card replacement require client-side scripting and are intentionally not implemented. CSS entrance, hover, focus, and reduced-motion styles are included.

## Testing notes

All PHP files pass `php -l` syntax checks. Full database integration tests require a running MySQL server and the `pdo_mysql` PHP extension. The current development runtime used to package this feature did not have `pdo_mysql`, so live SQL execution and browser/device testing could not be performed here.
