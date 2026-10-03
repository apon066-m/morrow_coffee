<?php include '_top.php';
$users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$products = $pdo->query('SELECT COUNT(*) FROM products WHERE available=1')->fetchColumn();
$orders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$open = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','preparing')")->fetchColumn();
$sales = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status!='cancelled'")->fetchColumn();
$logs = $pdo->query('SELECT l.*, u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 8')->fetchAll(); ?>
<div class="page-head"><div><h1>Dashboard</h1><p>Welcome back, <?= e($_SESSION['user']['name']) ?>.</p></div><a class="btn gold" href="orders.php">Open orders (<?= (int)$open ?>)</a></div>
<div class="stats">
  <div class="stat hl"><span>Sales</span><strong>$<?= number_format($sales, 2) ?></strong></div>
  <div class="stat"><span>Orders</span><strong><?= (int)$orders ?></strong></div>
  <div class="stat"><span>Menu items</span><strong><?= (int)$products ?></strong></div>
  <div class="stat"><span>Users</span><strong><?= (int)$users ?></strong></div>
</div>
<section class="panel"><div class="panel-title"><h2>Recent activity</h2><span>Latest 8 actions</span></div>
<?php if (!$logs): ?><p class="muted">No activity yet.</p><?php else: ?><div class="table-wrap"><table class="table"><thead><tr><th scope="col">Action</th><th scope="col">By</th><th scope="col">When</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?><tr><td><?= e($l['action']) ?></td><td><?= e($l['name'] ?? 'System') ?></td><td><?= date('d M Y, g:ia', strtotime($l['created_at'])) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php include '_bottom.php'; ?>
