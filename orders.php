<?php
require 'includes/db.php'; require 'includes/auth.php'; require_login();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // customers can cancel their own pending orders
    csrf_check();
    $id = (int)($_POST['cancel_id'] ?? 0);
    $s = $pdo->prepare("UPDATE orders SET status='cancelled', updated_by=? WHERE id=? AND user_id=? AND status='pending'");
    $s->execute([$_SESSION['user']['id'], $id, $_SESSION['user']['id']]);
    if ($s->rowCount()) { log_action($pdo, 'Cancelled own order #' . $id); $msg = 'Order #' . $id . ' cancelled.'; }
}
$s = $pdo->prepare('SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity,"× ",oi.product_name) SEPARATOR ", ") items FROM orders o LEFT JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id=? GROUP BY o.id ORDER BY o.id DESC');
$s->execute([$_SESSION['user']['id']]); $orders = $s->fetchAll();
$title = 'My orders'; include 'includes/header.php';
?>
<main id="main" class="section" style="padding-top:56px"><div class="wrap">
  <div class="page-head"><div><h1>My orders</h1><p>Track your drinks from pending to ready.</p></div><a class="btn gold" href="menu.php">Order a drink</a></div>
  <?php if (isset($_GET['created'])): ?><div class="alert success" role="status">Order placed. We’ll start on it shortly.</div><?php endif; ?>
  <?php if ($msg): ?><div class="alert success" role="status"><?= e($msg) ?></div><?php endif; ?>
  <?php if (!$orders): ?>
    <div class="empty"><h2>No orders yet</h2><p class="muted">Pick something from the menu and it will show up here.</p><a class="btn dark" href="menu.php">Browse the menu</a></div>
  <?php else: ?>
  <div class="table-wrap"><table class="table"><thead><tr><th scope="col">Order</th><th scope="col">Items</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Placed</th><th scope="col"><span class="skip" style="position:static;display:inline-block;width:1px;height:1px;overflow:hidden">Actions</span></th></tr></thead><tbody>
  <?php foreach ($orders as $o): ?>
    <tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['items']) ?></td><td>$<?= number_format($o['total'], 2) ?></td>
    <td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td><td><?= date('d M Y, g:ia', strtotime($o['created_at'])) ?></td>
    <td><?php if ($o['status'] === 'pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="cancel_id" value="<?= (int)$o['id'] ?>"><button class="btn danger small" onclick="return confirm('Cancel order #<?= (int)$o['id'] ?>?')">Cancel</button></form><?php endif; ?></td></tr>
  <?php endforeach; ?></tbody></table></div>
  <?php endif; ?>
</div></main>
<?php include 'includes/footer.php'; ?>
