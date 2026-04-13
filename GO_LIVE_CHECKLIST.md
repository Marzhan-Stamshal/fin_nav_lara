# Go-Live Checklist (Day 0 / Day 1)

## A. Pre go-live (before DNS/public switch)
- [ ] Production `.env` configured (`APP_ENV=production`, `APP_DEBUG=false`).
- [ ] DB credentials validated.
- [ ] `php artisan migrate --force` completed.
- [ ] Caches built:
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`
  - [ ] `php artisan view:cache`
- [ ] `storage` and `bootstrap/cache` writable.
- [ ] HTTPS certificate active.
- [ ] Test JSON backup exported (`/backup/export/json`).

## B. Smoke tests right after release
- [ ] Register new user.
- [ ] Login/logout.
- [ ] Add a loan manually.
- [ ] Edit loan and save.
- [ ] Mark selected loans as paid from dashboard.
- [ ] Close one loan early.
- [ ] Open schedule page and mark payments there.
- [ ] Save scenario and delete scenario.
- [ ] Export CSV and JSON.
- [ ] Import CSV and JSON (on test account).
- [ ] Open settings and update profile/password/preferences.

## C. Day 1 monitoring
- [ ] Check `storage/logs/laravel.log` every 2-3 hours.
- [ ] Check server error logs in Plesk.
- [ ] Watch DB size and disk quota.
- [ ] Verify no 500 errors on:
  - [ ] `/dashboard`
  - [ ] `/payments/schedule`
  - [ ] `/scenarios`
  - [ ] `/settings`
  - [ ] `/api/auth/login`
  - [ ] `/api/loans`
- [ ] Validate average response time manually on main pages.

## D. Rollback safety
- [ ] Keep latest JSON backup file off-server.
- [ ] Keep latest SQL dump.
- [ ] Keep previous release artifact/folder.
- [ ] Document rollback command sequence in Plesk notes.

## E. First week priorities
- [ ] Add scheduled DB backups.
- [ ] Add uptime monitoring.
- [ ] Add basic audit logging for critical actions (loan delete/import).
- [ ] Gather user feedback on dashboard flow and mobile UX.
