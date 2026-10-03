<?php include '_top.php'; require_admin();
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 15;
$total = (int)$pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn(); $pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages); $off = ($page - 1) * $per;
$logs = $pdo->query("SELECT l.*, u.name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT $per OFFSET $off")->fetchAll(); ?>
<div class="page-head"><div><h1>Activity log</h1><p>Who did what and when. <?= $total ?> recorded actions.</p></div></div>
<?php if (!$logs): ?><div class="empty"><h2>Nothing logged yet</h2><p class="muted">Sign-ins, orders and edits will appear here.</p></div><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Action</th><th scope="col">User</th><th scope="col">When</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?><tr><td><?= e($l['action']) ?></td><td><?= e($l['name'] ?? 'System / deleted user') ?></td><td><?= date('d M Y, g:i:sa', strtotime($l['created_at'])) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pager" aria-label="Log pages"><?php for ($n = 1; $n <= $pages; $n++): ?><?php if ($n === $page): ?><span class="cur" aria-current="page"><?= $n ?></span><?php else: ?><a href="?page=<?= $n ?>"><?= $n ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; endif; ?>
<?php include '_bottom.php'; ?>
