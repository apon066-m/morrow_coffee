# Install on XAMPP (Mac)

1. Copy `cafe-app-xampp-simple` into XAMPP `htdocs`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin.
4. If you already imported the previous `cafe_app`, select it, open **SQL**, paste the contents of `database/upgrade_existing.sql`, and run it. This adds the `staff` role without deleting your data.
5. For a brand-new setup, import `database/cafe_app.sql`.
6. Open `http://localhost/cafe-app-xampp-simple/setup_admin.php` once to create the demo admin.
7. Sign in with `admin@cafe.local` / `admin123`.
8. Go to Workspace → Users & RBAC → Add user to create customer, staff, or admin accounts.

Public Sign Up remains customer-only by design.
