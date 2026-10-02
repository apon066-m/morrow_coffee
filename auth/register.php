<?php
require 'includes/db.php'; require 'includes/auth.php';
$error = ''; $name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $error = 'Enter a name between 2 and 100 characters.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
    else {
        try {
            // Public sign-up always creates a customer; role is never read from the form.
            $s = $pdo->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
            $s->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1'); exit;
        } catch (PDOException $ex) { $error = 'That email is already registered. Try signing in instead.'; }
    }
}
$title = 'Create account'; include 'includes/header.php';
?>
<main id="main" class="auth-page"><div class="auth-shell">
  <div class="auth-photo"><img src="assets/images/coffee.jpg" alt=""><div><h2>Skip the queue.<br>Order ahead.</h2></div></div>
  <form class="auth-form" method="post" data-validate>
    <a class="brand auth-brand" href="index.php"><span class="brand-mark" aria-hidden="true">M</span><span><b>morrow</b><small>Coffee bar</small></span></a>
    <h1>Create account</h1><p>Join Morrow to order and track your drinks.</p>
    <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field"><label for="name">Full name</label><input class="input" id="name" name="name" value="<?= e($name) ?>" autocomplete="name" required></div>
    <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></div>
    <div class="field"><label for="password">Password</label><input class="input" id="password" type="password" name="password" minlength="6" autocomplete="new-password" required><p class="hint">At least 6 characters.</p></div>
    <button class="btn dark wide" type="submit">Create account</button>
    <p class="auth-note">Already have an account? <a href="login.php">Sign in</a></p>
  </form>
</div></main>
<?php include 'includes/footer.php'; ?>
