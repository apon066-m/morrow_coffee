<?php
require 'includes/db.php'; require 'includes/auth.php';

// ---- Smart Taste Match: rule-based keyword expansion (no external AI service, no personal data used) ----
$synonyms = [
    'iced' => ['iced', 'ice', 'cold'], 'cold' => ['iced', 'ice', 'cold'], 'chilled' => ['iced', 'ice', 'cold'], 'refreshing' => ['iced', 'ice', 'cold'],
    'chocolate' => ['chocolate', 'mocha'], 'sweet' => ['chocolate', 'mocha'], 'dessert' => ['chocolate', 'mocha'],
    'strong' => ['double', 'espresso', 'black'], 'bold' => ['double', 'espresso', 'black'], 'black' => ['black', 'double'],
    'milk' => ['milk', 'foam', 'latte'], 'creamy' => ['milk', 'foam', 'velvety'], 'smooth' => ['smooth', 'milk', 'foam'], 'light' => ['milk', 'smooth'],
];
$q = trim($_GET['q'] ?? ''); $cat = trim($_GET['category'] ?? '');
$words = array_slice(array_unique(preg_split('/[^a-z0-9]+/', strtolower($q), -1, PREG_SPLIT_NO_EMPTY)), 0, 6);
$terms = [];
foreach ($words as $w) foreach ($synonyms[$w] ?? [$w] as $t) $terms[$t] = true;
$terms = array_keys($terms);

$where = 'available=1'; $wp = [];
if ($terms) {
    $where .= ' AND (' . implode(' OR ', array_fill(0, count($terms), '(name LIKE ? OR description LIKE ?)')) . ')';
    foreach ($terms as $t) { $wp[] = "%$t%"; $wp[] = "%$t%"; }
}
if ($cat !== '') { $where .= ' AND category=?'; $wp[] = $cat; }

// ---- Pagination ----
$perPage = 6; $page = max(1, (int)($_GET['page'] ?? 1));
$c = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $where"); $c->execute($wp);
$total = (int)$c->fetchColumn(); $pages = max(1, (int)ceil($total / $perPage)); $page = min($page, $pages);
$offset = ($page - 1) * $perPage;

// Rank: name matches count double, description matches once (basic scoring)
$score = '0'; $sp = [];
foreach ($terms as $t) { $score .= ' + (name LIKE ?)*2 + (description LIKE ?)'; $sp[] = "%$t%"; $sp[] = "%$t%"; }
$s = $pdo->prepare("SELECT *, ($score) AS score FROM products WHERE $where ORDER BY score DESC, id DESC LIMIT $perPage OFFSET $offset");
$s->execute(array_merge($sp, $wp)); $items = $s->fetchAll();
$cats = $pdo->query('SELECT DISTINCT category FROM products WHERE available=1 ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

function why($row, $words, $synonyms) { // explains each match so the user can judge the suggestion
    $hay = strtolower($row['name'] . ' ' . $row['description']); $hit = [];
    foreach ($words as $w) foreach ($synonyms[$w] ?? [$w] as $t) if (strpos($hay, $t) !== false) { $hit[] = $w; break; }
    return $hit;
}
function page_url($p, $q, $cat) { return '?' . http_build_query(array_filter(['q' => $q, 'category' => $cat, 'page' => $p > 1 ? $p : null])); }

$title = 'Menu'; include 'includes/header.php';
?>
<main id="main" class="section" style="padding-top:56px"><div class="wrap">
  <?php if (isset($_GET['denied'])): ?><div class="alert error" role="alert">You don’t have permission to open that page.</div><?php endif; ?>
  <?php if (isset($_GET['added'])): ?><div class="alert success" role="status">Added to your cart. <a href="cart.php" style="text-decoration:underline">View cart</a></div><?php endif; ?>
  <h1 class="page-title">House menu</h1>
  <p class="muted">Coffee made to order. Describe what you feel like and we’ll rank the menu for you.</p>

  <form class="toolbar" method="get" role="search">
    <div><label class="skip" for="q">Search the menu</label><input class="input" id="q" name="q" value="<?= e($q) ?>" placeholder="Search: iced, strong, chocolate, creamy…"></div>
    <div><label class="skip" for="category">Category</label><select id="category" name="category"><option value="">All categories</option><?php foreach ($cats as $k): ?><option <?= $cat === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
    <button class="btn dark" type="submit">Search</button>
  </form>

  <aside class="smart" aria-label="Smart Taste Match">
    <div class="spark" aria-hidden="true">✦</div>
    <h2>Smart Taste Match</h2>
    <?php if ($words): ?>
      <p class="why">Showing <?= $total ?> match<?= $total === 1 ? '' : 'es' ?> for “<?= e(implode(' ', $words)) ?>”, best matches first.</p>
    <?php else: ?>
      <p>Try a mood word like <b>iced</b>, <b>chocolate</b>, <b>milk</b> or <b>strong</b>. We match your words to drink names and descriptions using simple rules.</p>
    <?php endif; ?>
    <p>Suggestions are keyword-based and can miss drinks. Browse the full menu if nothing fits. We never use your personal details for this.</p>
  </aside>

  <?php if (!$items): ?>
    <div class="empty"><h2>No drinks match that yet</h2><p class="muted">Check the spelling or try a broader word like “milk” or “iced”.</p><a class="btn gold" href="menu.php">Clear search</a></div>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($items as $i): $hit = why($i, $words, $synonyms); ?>
    <article class="card">
      <img src="<?= e($i['image']) ?>" alt="<?= e($i['name']) ?>" loading="lazy">
      <div class="card-body">
        <span class="tag <?= $hit ? 'match' : '' ?>"><?= $hit ? 'Matches: ' . e(implode(', ', $hit)) : e($i['category']) ?></span>
        <h3><?= e($i['name']) ?></h3>
        <p class="muted" style="margin:0"><?= e($i['description']) ?></p>
        <div class="card-foot">
          <span class="price">$<?= number_format($i['price'], 2) ?></span>
          <?php if (logged_in()): ?>
            <form method="post" action="cart.php"><?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$i['id'] ?>"><input type="hidden" name="back" value="<?= e($_SERVER['QUERY_STRING'] ?? '') ?>"><button class="btn dark small">Add to cart</button></form>
          <?php else: ?><a class="btn small" href="login.php">Sign in to order</a><?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Menu pages">
    <?php if ($page > 1): ?><a href="<?= e(page_url($page - 1, $q, $cat)) ?>" rel="prev">Previous</a><?php endif; ?>
    <?php for ($n = 1; $n <= $pages; $n++): ?><?php if ($n === $page): ?><span class="cur" aria-current="page"><?= $n ?></span><?php else: ?><a href="<?= e(page_url($n, $q, $cat)) ?>"><?= $n ?></a><?php endif; ?><?php endfor; ?>
    <?php if ($page < $pages): ?><a href="<?= e(page_url($page + 1, $q, $cat)) ?>" rel="next">Next</a><?php endif; ?>
  </nav>
  <?php endif; endif; ?>
</div></main>
<?php include 'includes/footer.php'; ?>
