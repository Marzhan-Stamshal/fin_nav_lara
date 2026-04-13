# Changelog

## 2026-04-13 - Laravel migration release

### Added
- Rebuilt project on Laravel 13 + Blade in `fin_nav_laravel`.
- Auth flow: register/login/logout.
- Full loan lifecycle: create, edit, show, delete.
- Dashboard with:
  - totals (early payoff / full payoff / savings / monthly)
  - filters (bank/group/status/term/min/max)
  - sorting
  - progress by paid/remaining months
  - forecast by bank (expandable)
  - reminders 3/1 day
  - next 30 days grouped payments
  - calendar payments by month/date
  - risk assessment by monthly income
  - recommendation block
  - what-if +X scenario
  - mass actions (paid/close early/assign group/clear group)
  - sticky selected-actions bar
  - mobile cards mode
- Payments pages:
  - payments history
  - schedule page with checkboxes and one-click monthly payment mark
- Scenarios page:
  - selectable loans
  - refinance simulation
  - scenario save/delete
- Settings page:
  - profile update
  - password change
  - preferences (income/currency/locale)
- Data portability:
  - CSV export/import + sample template
  - full JSON backup export/import (loans/payments/scenarios)
- API layer:
  - `/api/auth/*`
  - `/api/loans*`
  - `/api/payments`

### Database
- Added tables:
  - `loans`
  - `payments`
  - `scenarios`
  - `scenario_loans`
  - `user_settings`

### Docs
- Replaced default Laravel README with project-specific docs.
- Added/updated deployment docs for Plesk.
- Added production checklist.

### Notes
- Built with practical parity to previous Next.js version and adapted UX for mobile.
