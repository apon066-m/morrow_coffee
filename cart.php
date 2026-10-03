<?php
require 'includes/db.php'; require 'includes/auth.php'; require_login(); require_permission('order.create');
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];   // [product_id => quantity]
$cart = &$_SESSION['cart'];
$error = ''; $notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add' && $pid > 0) {
        $s = $pdo->prepare('SELECT id FROM products WHERE id=? AND available=1'); $s->execute([$pid]);
        if ($s->fetch()) { $cart[$pid] = min(20, ($cart[$pid] ?? 0) + 1); }
        // return to the menu page the user came from (only whitelisted params are kept)
        parse_str($_POST['back'] ?? '', $b);
        $keep = array_filter(['q' => $b['q'] ?? '', 'category' => $b['category'] ?? '', 'page' => $b['page'] ?? '', 'added' => $pid], fn($v) => $v !== '');
        header('Location: menu.php?' . http_build_query($keep)); exit;
    }
    if ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $id => $q) {
            $id = (int)$id; $q = (int)$q;
            if ($q <= 0) unset($cart[$id]); elseif (isset($cart[$id])) $cart[$id] = min(20, $q);
        }
        $notice = 'Cart updated.';
    }
    if ($action === 'remove') { unset($cart[$pid]); $notice = 'Item removed.'; }
    if ($action === 'clear') { $cart = []; $notice = 'Cart emptied.'; }

    if ($action === 'checkout') {
        if (!$cart) { $error = 'Your cart is empty. Add a drink first.'; }
        else {
            // Prices always come from the database, never from the browser.
            $ids = array_keys($cart);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $s = $pdo->prepare("SELECT * FROM products WHERE available=1 AND id IN ($in)"); $s->execute($ids);
            $rows = $s->fetchAll(); $total = 0;
            foreach ($rows as $r) $total += $r['price'] * $cart[$r['id']];
            if (count($rows) !== count($ids)) { $error = 'A drink in your cart is no longer on the menu. Review your cart and try again.'; $cart = array_intersect_key($cart, array_column($rows, null, 'id')); }
            else {
                try {
                    $pdo->beginTransaction();
                    $o = $pdo->prepare('INSERT INTO orders(user_id,customer_name,total) VALUES(?,?,?)');
                    $o->execute([$_SESSION['user']['id'], $_SESSION['user']['name'], $total]);
                    $oid = $pdo->lastInsertId();
                    $it = $pdo->prepare('INSERT INTO order_items(order_id,product_id,product_name,quantity,price) VALUES(?,?,?,?,?)');
                    foreach ($rows as $r) $it->execute([$oid, $r['id'], $r['name'], $cart[$r['id']], $r['price']]);
                    $pdo->commit();
                    log_action($pdo, 'Created order #' . $oid . ' (' . count($rows) . ' items, $' . number_format($total, 2) . ')');
                    $cart = []; header('Location: orders.php?created=1'); exit;
                } catch (Throwable $ex) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $error = 'We couldn’t place your order. Nothing was charged. Please try again.';
                }
            }
        }
    }
}

$lines = []; $total = 0;
if ($cart) {
    $ids = array_keys($cart); $in = implode(',', array_fill(0, count($ids), '?'));
    $s = $pdo->prepare("SELECT * FROM products WHERE available=1 AND id IN ($in) ORDER BY name"); $s->execute($ids);
    foreach ($s->fetchAll() as $r) { $q = $cart[$r['id']]; $r['qty'] = $q; $r['line'] = $q * $r['price']; $total += $r['line']; $lines[] = $r; }
    // drop anything archived since it was added
    $cart = array_intersect_key($cart, array_column($lines, null, 'id'));
}
$title = 'Your cart'; include 'includes/header.php';
?>
<main id="main" class="section" style="padding-top:56px"><div class="wrap">
  <div class="page-head"><div><h1>Your cart</h1><p>Check your drinks, then place the order.</p></div><a class="btn" href="menu.php">Keep browsing</a></div>
  <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <?php if ($notice): ?><div class="alert success" role="status"><?= e($notice) ?></div><?php endif; ?>

  <?php if (!$lines): ?>
    <div class="empty"><h2>Your cart is empty</h2><p class="muted">Add a drink from the menu and it will show up here.</p><a class="btn gold" href="menu.php">Browse the menu</a></div>
  <?php else: ?>
  <div class="cart-layout">
    <form method="post" class="cart-items"><?= csrf_field() ?><input type="hidden" name="action" value="update">
      <?php foreach ($lines as $l): ?>
      <div class="cart-row">
        <img src="<?= e($l['image']) ?>" alt="">
        <div class="cart-info"><b><?= e($l['name']) ?></b><span class="muted">$<?= number_format($l['price'], 2) ?> each</span></div>
        <div class="cart-qty"><label class="skip" for="q<?= (int)$l['id'] ?>">Quantity for <?= e($l['name']) ?></label>
          <input class="input" id="q<?= (int)$l['id'] ?>" type="number" name="qty[<?= (int)$l['id'] ?>]" value="<?= (int)$l['qty'] ?>" min="0" max="20"></div>
        <b class="cart-line">$<?= number_format($l['line'], 2) ?></b>
        <button class="btn danger small" type="submit" form="rm<?= (int)$l['id'] ?>">Remove</button>
      </div>
      <?php endforeach; ?>
      <div class="actions" style="margin-top:16px"><button class="btn small dark" type="submit">Update quantities</button>
      <button class="btn small" type="submit" form="clearForm">Empty cart</button></div>
    </form>

    <aside class="cart-summary" aria-label="Order summary"><h2>Order summary</h2>
      <dl><?php foreach ($lines as $l): ?><div><dt><?= (int)$l['qty'] ?> × <?= e($l['name']) ?></dt><dd>$<?= number_format($l['line'], 2) ?></dd></div><?php endforeach; ?></dl>
      <div class="cart-total"><span>Total</span><strong>$<?= number_format($total, 2) ?></strong></div>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="checkout"><button class="btn gold wide">Place order</button></form>
      <p class="hint">Pay at the counter when you collect. Prices are confirmed at checkout.</p>
    </aside>
  </div>
  <?php foreach ($lines as $l): ?><form id="rm<?= (int)$l['id'] ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$l['id'] ?>"></form><?php endforeach; ?>
  <form id="clearForm" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="clear"></form>
  <?php endif; ?>
</div></main>
<?php include 'includes/footer.php'; ?>
