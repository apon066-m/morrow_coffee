<?php include '_top.php'; require_once '../includes/upload.php'; require_permission('menu.update');
$error = ''; $imgErr = ''; $newImg = null; $uid = $_SESSION['user']['id'];
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM products WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['archive']) || isset($_POST['restore'])) {
        $on = isset($_POST['restore']) ? 1 : 0;
        $s = $pdo->prepare('UPDATE products SET available=?, updated_by=? WHERE id=?'); $s->execute([$on, $uid, $id]);
        log_action($pdo, ($on ? 'Restored' : 'Archived') . ' menu item #' . $id);
        header('Location: products.php?saved=1'); exit;
    }
    $name = trim($_POST['name'] ?? ''); $category = trim($_POST['category'] ?? ''); $desc = trim($_POST['description'] ?? ''); $price = $_POST['price'] ?? '';
    // Server-side validation (client-side JS is only a convenience)
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $error = 'Name must be 2–120 characters.';
    elseif (mb_strlen($category) < 2 || mb_strlen($category) > 60) $error = 'Category must be 2–60 characters.';
    elseif (mb_strlen($desc) < 10) $error = 'Description needs at least 10 characters.';
    elseif (!is_numeric($price) || $price <= 0 || $price > 100) $error = 'Price must be between $0.01 and $100.';
    if (!$error) { $newImg = process_product_image($_FILES['image'] ?? null, $imgErr); if ($imgErr) $error = $imgErr; }
    if (!$error) {
        try {
            if ($id) {
                $s = $pdo->prepare('UPDATE products SET name=?,category=?,description=?,price=?,updated_by=? WHERE id=?'); $s->execute([$name, $category, $desc, $price, $uid, $id]);
                if ($newImg) store_product_image($pdo, $id, $newImg);
                log_action($pdo, 'Updated menu item #' . $id . ($newImg ? ' (new image)' : ''));
            } else {
                $s = $pdo->prepare('INSERT INTO products(name,category,description,price,image,created_by,updated_by) VALUES(?,?,?,?,?,?,?)'); $s->execute([$name, $category, $desc, $price, 'assets/images/coffee.jpg', $uid, $uid]);
                $newId = (int)$pdo->lastInsertId();
                if ($newImg) store_product_image($pdo, $newId, $newImg);
                log_action($pdo, 'Created menu item "' . $name . '"');
            }
            header('Location: products.php?saved=1'); exit;
        } catch (Throwable $ex) { $error = 'The item could not be saved: ' . $ex->getMessage(); }
    }
    if ($error) { $edit = ['id' => $id, 'name' => $name, 'category' => $category, 'description' => $desc, 'price' => $price, 'image' => $_POST['current_image'] ?? '']; }
}
$items = $pdo->query('SELECT p.*, u.name AS editor FROM products p LEFT JOIN users u ON u.id=p.updated_by ORDER BY p.available DESC, p.id DESC')->fetchAll(); ?>
<div class="page-head"><div><h1>Menu items</h1><p>Add, edit and archive what customers see on the menu.</p></div></div>
<?php if (isset($_GET['saved'])): ?><div class="alert success" role="status">Saved.</div><?php endif; ?>
<section class="panel"><div class="panel-title"><h2><?= !empty($edit['id']) ? 'Edit menu item' : 'Add menu item' ?></h2><?php if (!empty($edit['id'])): ?><a class="link-arrow" href="products.php">Cancel edit</a><?php endif; ?></div>
<?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-grid" data-validate enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
  <div class="field"><label for="name">Name</label><input class="input" id="name" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
  <div class="field"><label for="category">Category</label><input class="input" id="category" name="category" list="cats" required value="<?= e($edit['category'] ?? 'Coffee') ?>"><datalist id="cats"><option>Coffee</option><option>Iced</option></datalist></div>
  <div class="field full"><label for="description">Description</label><textarea id="description" name="description" required><?= e($edit['description'] ?? '') ?></textarea><p class="hint">Customers search this text, so mention flavours like “chocolate” or “strong”.</p></div>
  <div class="field full"><label for="image">Photo</label>
    <div class="img-upload"><img id="imgPreview" src="../<?= e(!empty($edit['image']) ? $edit['image'] : 'assets/images/coffee.jpg') ?>" alt="Current photo"><div>
      <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? '') ?>">
      <input class="input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-max="8388608">
      <p class="hint">JPG, PNG or WebP. Big phone photos are shrunk automatically. Landscape photos work best. Leave empty to keep the current photo.</p></div></div></div>
  <div class="field"><label for="price">Price (AUD)</label><input class="input" id="price" type="number" step="0.01" min="0.01" max="100" name="price" required value="<?= e($edit['price'] ?? '') ?>"></div>
  <div class="field" style="align-self:end"><button class="btn gold">Save item</button></div>
</form></section>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Photo</th><th scope="col">Name</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Status</th><th scope="col">Last updated</th><th scope="col">Actions</th></tr></thead><tbody>
<?php foreach ($items as $i): ?><tr><td><img class="thumb" src="../<?= e($i['image']) ?>" alt="" loading="lazy"></td><td><b><?= e($i['name']) ?></b></td><td><?= e($i['category']) ?></td><td>$<?= number_format($i['price'], 2) ?></td><td><span class="badge <?= $i['available'] ? 'ready' : 'cancelled' ?>"><?= $i['available'] ? 'On menu' : 'Archived' ?></span></td><td><?= date('d M Y', strtotime($i['updated_at'])) ?> by <?= e($i['editor'] ?? '—') ?></td>
<td><div class="actions"><a class="btn small" href="?edit=<?= (int)$i['id'] ?>#name">Edit</a>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><?php if ($i['available']): ?><button class="btn danger small" name="archive" value="1" onclick="return confirm('Archive this item? It will disappear from the menu.')">Archive</button><?php else: ?><button class="btn small" name="restore" value="1">Restore</button><?php endif; ?></form></div></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '_bottom.php'; ?>
