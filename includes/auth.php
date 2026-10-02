<?php
// Session + role-based access control + security helpers
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
function logged_in() { return isset($_SESSION['user']); }
function role() { return $_SESSION['user']['role'] ?? 'guest'; }
function is_admin() { return role() === 'admin'; }
function is_staff() { return role() === 'staff'; }
function is_workspace_user() { return in_array(role(), ['admin', 'staff'], true); }
function require_login() { if (!logged_in()) { header('Location: login.php'); exit; } }
function require_workspace() { if (!is_workspace_user()) { http_response_code(403); header('Location: ../menu.php?denied=1'); exit; } }
function require_admin() { if (!is_admin()) { http_response_code(403); header('Location: ../menu.php?denied=1'); exit; } }
function can($permission) {
    $map = [
        'customer' => ['menu.read', 'order.create', 'order.read_own'],
        'staff'    => ['menu.read', 'menu.create', 'menu.update', 'order.create', 'order.read_all', 'order.update'],
        'admin'    => ['*'],
    ];
    $r = role();
    return in_array('*', $map[$r] ?? [], true) || in_array($permission, $map[$r] ?? [], true);
}
function require_permission($permission) {
    if (!can($permission)) { http_response_code(403); die('403 Forbidden - You do not have permission to access this page.'); }
}
// Output escaping (XSS protection)
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
// CSRF protection for every POST form
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
        if (!$ok) { http_response_code(400); die('Invalid or expired form. Go back, refresh the page and try again.'); }
    }
}
// Audit trail
function log_action($pdo, $action) {
    $uid = $_SESSION['user']['id'] ?? null;
    $s = $pdo->prepare('INSERT INTO activity_logs(user_id,action) VALUES(?,?)');
    $s->execute([$uid, $action]);
}
