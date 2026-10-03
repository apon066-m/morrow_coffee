# Morrow Coffee — ICT203 Assessment 3

Full-stack PHP 8 + MySQL (PDO) web app for a small café: customers browse drinks, build a cart and place orders, staff run the menu and orders, admins manage users and see the audit log.

**Live site:** _add your public URL here_  ·  **Repo:** https://github.com/apon066-m/morrow_coffee

## Setup (XAMPP)
1. Copy this folder into `htdocs`, start Apache + MySQL.
2. phpMyAdmin → import `database/cafe_app.sql` (existing DB? run `database/upgrade_existing.sql` once instead).
3. Open `http://localhost/cafe-app-xampp-simple/setup_admin.php` once, then **delete that file**.
4. For a web host: copy `includes/config.sample.php` → `includes/config.local.php` and enter the host's DB details.

## Demo accounts
| Role | Email | Password |
|---|---|---|
| Admin | admin@cafe.local | admin123 (change after first login) |
| Staff / Customer | create from Workspace → Users & roles, or Sign up | — |

## Requirement coverage
- **Auth + RBAC:** sessions, `password_hash`, roles customer/staff/admin, `can()` permission map, session ID regenerated on login.
- **CRUD (3 entities):** Menu items (create/edit/archive/restore), Users (create/role change/delete), Orders (multi-item cart checkout, staff status update, customer cancel).
- **Search + filter + pagination:** menu (keyword + category, 6 per page), plus admin orders, users, activity log.
- **Validation:** JS (`assets/js/app.js`) and PHP on every form.
- **Security:** prepared statements, `htmlspecialchars` output escaping, CSRF tokens on all POSTs, access-control checks, HttpOnly/SameSite cookies.
- **Auditability:** `activity_logs` table + `created_by/updated_by` and timestamps on products and orders; admin Activity log page.
- **Responsive/accessible:** mobile-first CSS, skip link, labelled fields, visible focus, reduced-motion support.

## Intelligent / AI Feature Use Statement
**Feature:** Smart Taste Match (AI Option 2 — Smart Search). **How it works:** the customer's words are lower-cased and expanded with a hand-written synonym table in `menu.php` (e.g. "sweet" → chocolate, mocha); drinks are ranked by a simple score (name match = 2, description match = 1) and each result shows which words matched. **No AI/ML model or external AI service is used, and no personal data is processed.** **User control:** results are suggestions only; users can edit the search, filter by category or browse the full menu. **Limitations:** keyword-based, English only, no spelling correction, and only finds words the synonym table or descriptions contain. **AI assistance in development:** Claude was used to help restyle the UI and review code; the team reviewed and can explain all of it (record this in your AI Declaration Table).

## Deploying to free hosting (InfinityFree)
1. Create the account and database in the host's control panel. Note the MySQL host, database name, username and password.
2. phpMyAdmin → select **that** database → Import `database/cafe_app_hosting.sql` (this file has no CREATE DATABASE/USE lines, which shared hosts block).
3. Copy `includes/config.sample.php` to `includes/config.local.php`, fill in the host's details.
4. Upload everything into the host's `htdocs` folder, `config.local.php` included.
5. Visit `/setup_admin.php` once, then delete it from the host. Sign in and change the admin password.

## Menu photos
Staff and admins can upload a photo for each drink under **Workspace → Menu items** (JPG/PNG/WebP, max 2 MB). Uploads are validated, resized with GD and saved to `assets/uploads/` (script execution is blocked there). On a web host make sure `assets/uploads` exists and is writable. For an existing database, run the last block of `database/upgrade_existing.sql` to give the seeded drinks their own photos.
