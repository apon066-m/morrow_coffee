<?php include '_top.php'; require_permission('order.read_all');
$statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $_POST['status'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    if (in_array($st, $statuses, true)) {
        $s = $pdo->prepare('UPDATE orders SET status=?, updated_by=? WHERE id=?'); $s->execute([$st, $_SESSION['user']['id'], $id]);
        log_action($pdo, 'Updated order #' . $id . ' to ' . $st);
    }
    header('Location: orders.php?saved=1'); exit;
}
$filter = in_array($_GET['status'] ?? '', $statuses, true) ? $_GET['status'] : '';
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 10;
$cnt = $pdo->prepare('SELECT COUNT(*) FROM orders' . ($filter ? ' WHERE status=?' : '')); $cnt->execute($filter ? [$filter] : []);
$pages = max(1, (int)ceil($cnt->fetchColumn() / $per)); $page = min($page, $pages); $off = ($page - 1) * $per;
$s = $pdo->prepare('SELECT o.*, u.name AS editor FROM orders o LEFT JOIN users u ON u.id=o.updated_by' . ($filter ? ' WHERE o.status=?' : '') . " ORDER BY o.id DESC LIMIT $per OFFSET $off");
$s->execute($filter ? [$filter] : []); $orders = $s->fetchAll(); ?>
<div class="page-head"><div><h1>Orders</h1><p>Update the status as drinks are made.</p></div>
<form method="get" class="inline-form"><label class="skip" for="st">Filter by status</label><select id="st" name="status"><option value="">All statuses</option><?php foreach ($statuses as $x): ?><option <?= $filter === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn small dark">Filter</button></form></div>
<?php if (isset($_GET['saved'])): ?><div class="alert success" role="status">Order status saved.</div><?php endif; ?>
<?php if (!$orders): ?><div class="empty"><h2>No orders found</h2><p class="muted">New orders will appear here as customers place them.</p></div><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">#</th><th scope="col">Customer</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Placed</th><th scope="col">Last updated by</th><th scope="col">Update</th></tr></thead><tbody>
<?php foreach ($orders as $o): ?><tr><td><?= (int)$o['id'] ?></td><td><?= e($o['customer_name']) ?></td><td>$<?= number_format($o['total'], 2) ?></td><td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td><td><?= date('d M, g:ia', strtotime($o['created_at'])) ?></td><td><?= e($o['editor'] ?? '—') ?></td>
<td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><label class="skip" for="s<?= (int)$o['id'] ?>">Status for order <?= (int)$o['id'] ?></label><select id="s<?= (int)$o['id'] ?>" name="status"><?php foreach ($statuses as $x): ?><option <?= $o['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn small">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pager" aria-label="Order pages"><?php for ($n = 1; $n <= $pages; $n++): ?><?php if ($n === $page): ?><span class="cur" aria-current="page"><?= $n ?></span><?php else: ?><a href="?<?= e(http_build_query(array_filter(['status' => $filter, 'page' => $n]))) ?>"><?= $n ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; endif; ?>
<?php include '_bottom.php'; ?>
