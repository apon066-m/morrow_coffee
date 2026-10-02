<?php
require 'includes/db.php'; require 'includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $s->execute([trim($_POST['email'] ?? '')]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true); // prevents session fixation
        $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
        log_action($pdo, 'Logged in');
        header('Location: ' . (in_array($u['role'], ['admin', 'staff'], true) ? 'admin/dashboard.php' : 'menu.php'));
        exit;
    }
    $error = 'Incorrect email or password. Check both and try again.';
}
$title = 'Sign in'; include 'includes/header.php';
?>
<main id="main" class="auth-page"><div class="auth-shell">
  <div class="auth-photo"><img src="assets/images/coffee3.jpg" alt=""><div><h2>Your coffee,<br>your way.</h2></div></div>
  <form class="auth-form" method="post" data-validate>
    <a class="brand auth-brand" href="index.php"><span class="brand-mark" aria-hidden="true">M</span><span><b>morrow</b><small>Coffee bar</small></span></a>
    <h1>Welcome back</h1><p>Sign in to order and track your drinks.</p>
    <?php if (isset($_GET['registered'])): ?><div class="alert success" role="status">Account created. You can now sign in.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" autocomplete="email" required></div>
    <div class="field"><label for="password">Password</label><input class="input" id="password" type="password" name="password" autocomplete="current-password" required></div>
    <button class="btn dark wide" type="submit">Sign in</button>
    <p class="auth-note">New customer? <a href="register.php">Create an account</a></p>
  </form>
</div></main>
<?php include 'includes/footer.php'; ?>
