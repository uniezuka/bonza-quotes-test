# Bonza Quote Plugin – Milestones & Progress

Reference: see `PROJECT_STRUCTURE.md` for architecture and file responsibilities.

## Status Legend
- [ ] Not started
- [~] In progress
- [x] Completed

## High-level Goals
- [x] Frontend `[bonza_quote_form]` collects quotes and saves as `pending`
- [x] Admin “Bonza Quotes” page lists quotes and allows Approve/Reject
- [x] OOP structure using WordPress Plugin Boilerplate patterns
- [x] Sanitization, escaping, nonces, capability checks
- [x] Optional: email notification on new submission
- [ ] At least one PHPUnit test runnable via README instructions

---

## Milestone M1: Data Layer & Activation
Scope: database table, repository/service, activation routine.

- [x] Define schema `{$wpdb->prefix}bonza_quotes` (id, name, email, service_type, notes, status, created_at, updated_at)
- [x] Create table via `dbDelta` in `includes/class-bonza_quote-activator.php`
- [x] Add repository `includes/class-bonza_quote-quote-repository.php`
  - [x] `insert_quote( name, email, service_type, notes )`
  - [x] `get_quotes( args )` with pagination/search
  - [x] `update_status( id, status )`
  - [x] Internal sanitization and `$wpdb->prepare` usage
  - [x] Optional uninstall cleanup in `uninstall.php`

Acceptance:
- Table exists after activation, repository methods callable without fatal errors.

---

## Milestone M2: Frontend Shortcode Form
Scope: user form, validation, persistence, success UX.

- [x] Register shortcode in `public/class-bonza_quote-public.php`
- [x] Render via partial `public/partials/bonza_quote-form.php`
- [x] Fields: Name, Email, Service Type, Notes; include nonce
- [x] Server-side validate/sanitize; save with status `pending`
- [x] Success message after submission; prevent double posts (redirect)
- [x] Escape all outputs; enqueue minimal CSS/JS as needed

Acceptance:
- Visiting a page with `[bonza_quote_form]` allows submission and shows success message; row visible in DB as `pending`.

---

## Milestone M3: Admin Area UI
Scope: menu page, list table, status actions.

- [x] Add top-level menu “Bonza Quotes” in `admin/class-bonza_quote-admin.php`
- [x] Implement `WP_List_Table` subclass `admin/class-bonza_quote-list-table.php`
- [x] Columns: Name, Email, Service Type, Status, Date
- [x] Actions: Approve/Reject (row) with nonces + `manage_options` capability
- [x] Pagination and basic search (name/email/service type)
- [x] Admin notices for action results

Acceptance:
- Admin can view and change status with nonce/cap checks; UI paginates and searches.

---

## Milestone M4: Security, i18n, Code Quality
Scope: hardening and internationalization.

- [x] Nonces on all forms and admin actions
- [x] Capability checks on all admin ops
- [x] Prepared statements for all queries
- [x] Escape outputs (`esc_html`, `esc_attr`, `wp_kses` as appropriate)
- [x] Load textdomain in `includes/class-bonza_quote-i18n.php`

Acceptance:
- Security checks present; strings are translation-ready.

---

## Milestone M5: Extensibility & Email (Optional)
Scope: hooks and notifications.

- [x] Hook: `do_action( 'bonza_quote/submitted', $quote_id, $data )`
- [x] Hook: `do_action( 'bonza_quote/status_changed', $quote_id, $old, $new )`
- [x] Filter: `apply_filters( 'bonza_quote/service_types', $types )`
- [x] Email admin on new submission via `wp_mail`
- [x] Filters for email subject/body (`bonza_quote/admin_email_subject`, `bonza_quote/admin_email_body`)

Acceptance:
- New submissions trigger email; hooks fire with documented params.

---

## Milestone M6: Testing
Scope: minimal but runnable tests.

- [x] Add `tests/`, `phpunit.xml.dist`, `bin/install-wp-tests.sh`
- [x] Unit test: repository sanitization and status transitions
- [x] Optional integration test: activator creates table
- [x] Document test setup and run commands in `README.txt`

Acceptance:
- `phpunit` runs at least one passing test with documented setup.

---

## Milestone M7: Docs & GitHub
Scope: developer and user documentation, repo hygiene.

- [ ] Update `README.txt` with setup, shortcode usage, admin UI overview
- [ ] Document DB schema and uninstall behavior
- [ ] Add section for tests: how to install and run
- [ ] Push to public GitHub with descriptive commits

Acceptance:
- Clear README and visible commit history in the public repository.

---

## Progress Log
Use this section to record noteworthy changes. Link to commits/PRs when available.

- 2025-08-10: Implemented DB table via activator, repository CRUD methods, frontend shortcode with form and submission handling, admin page with list, actions, search/pagination; updated milestones.

---

## How to Update This File
- Mark tasks with [x] as they are completed; use [~] for in-progress if helpful.
- Add dates and brief notes under “Progress Log”.
- Keep items aligned with actual file touch-points (e.g., `includes/class-bonza_quote-activator.php`).


