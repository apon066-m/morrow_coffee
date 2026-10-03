<?php
ob_start();
$base = '../'; require '../includes/db.php'; require '../includes/auth.php'; require_workspace();
$current = basename($_SERVER['PHP_SELF']);
csrf_check(); // every workspace POST must carry a valid CSRF token
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0b0b0b"><meta name="color-scheme" content="dark">
<title>Workspace · Morrow Coffee</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>"><script src="../assets/js/app.js" defer></script><style>
/* Critical workspace layout (kept here so the admin area works even with a stale or cached stylesheet) */
.workspace{display:grid;grid-template-columns:250px minmax(0,1fr);min-height:100vh;align-items:start}
.workspace>.side{position:sticky;top:0;height:100vh;overflow-y:auto}
.workspace>.main{min-width:0}
.badge.admin{display:inline-block;min-height:0;height:auto;grid-template-columns:none;background:var(--yellow,#ffd60a);color:var(--on-yellow,#0b0b0b)}
@media(max-width:900px){.workspace{grid-template-columns:1fr}.workspace>.side{position:relative;height:auto}}
</style>
</head>
<body><a class="skip" href="#main">Skip to content</a>
<div class="workspace"><aside class="side"><a class="workspace-brand" href="dashboard.php"><span class="brand-mark" aria-hidden="true">M</span><span><b>morrow</b><small>Workspace</small></span></a>
<div class="role-chip"><?= e(ucfirst(role())) ?></div>
<nav aria-label="Workspace">
  <a class="<?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
  <a class="<?= $current === 'products.php' ? 'active' : '' ?>" href="products.php">Menu items</a>
  <a class="<?= $current === 'orders.php' ? 'active' : '' ?>" href="orders.php">Orders</a>
  <?php if (is_admin()): ?><div class="side-label">Administration</div>
  <a class="<?= $current === 'users.php' ? 'active' : '' ?>" href="users.php">Users &amp; roles</a>
  <a class="<?= $current === 'activity.php' ? 'active' : '' ?>" href="activity.php">Activity log</a><?php endif; ?>
  <div class="side-label">Account</div><a href="../index.php">View website</a><a href="../logout.php">Log out</a>
</nav></aside>
<main class="main" id="main"><div class="workspace-top"><span class="muted">Signed in as</span><span class="workspace-user"><?= e($_SESSION['user']['name']) ?></span></div>
