<?php require_once __DIR__ . '/auth.php'; $root = isset($base) ? $base : ''; $page = basename($_SERVER['PHP_SELF']); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0b0b0b"><meta name="color-scheme" content="dark">
<title><?= isset($title) ? e($title) . ' · ' : '' ?>Morrow Coffee</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
<script src="<?= $root ?>assets/js/app.js" defer></script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header"><div class="wrap nav">
  <a class="brand" href="<?= $root ?>index.php" aria-label="Morrow Coffee home"><span class="brand-mark" aria-hidden="true">M</span><span><b>morrow</b><small>Coffee bar</small></span></a>
  <button class="mobile-toggle" type="button" aria-label="Toggle menu" aria-expanded="false" aria-controls="nav-links">☰</button>
  <nav class="links" id="nav-links" aria-label="Main">
    <a href="<?= $root ?>menu.php" <?= $page === 'menu.php' ? 'aria-current="page"' : '' ?>>Menu</a>
    <a href="<?= $root ?>index.php#approach">Our approach</a>
    <a href="<?= $root ?>index.php#visit">Visit</a>
    <?php if (logged_in()): ?>
      <a href="<?= $root ?>orders.php" <?= $page === 'orders.php' ? 'aria-current="page"' : '' ?>>My orders</a>
      <a href="<?= $root ?>cart.php" <?= $page === 'cart.php' ? 'aria-current="page"' : '' ?>>Cart<?php $n = array_sum($_SESSION['cart'] ?? []); if ($n): ?> <span class="cart-count" aria-label="<?= (int)$n ?> items"><?= (int)$n ?></span><?php endif; ?></a>
      <?php if (is_workspace_user()): ?><a class="nav-pill" href="<?= $root ?>admin/dashboard.php">Workspace</a><?php endif; ?>
      <a href="<?= $root ?>logout.php">Log out</a>
    <?php else: ?>
      <a href="<?= $root ?>register.php">Sign up</a><a class="nav-pill" href="<?= $root ?>login.php">Sign in</a>
    <?php endif; ?>
  </nav>
</div></header>
