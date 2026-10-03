<?php include '_top.php'; require_admin();
$error = ''; $success = ''; $roles = ['customer', 'staff', 'admin']; $me = (int)$_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pw = $_POST['password'] ?? ''; $r = $_POST['role'] ?? 'customer';
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6 || !in_array($r, $roles, true)) $error = 'Enter a name, a valid email, a password of 6+ characters and a role.';
        else { try {
            $s = $pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)'); $s->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $r]);
            log_action($pdo, 'Admin created ' . $r . ' account for ' . $email); $success = 'User created.';
        } catch (PDOException $ex) { $error = 'That email address is already registered.'; } }
    }
    if ($action === 'role') {
        $id = (int)($_POST['id'] ?? 0); $r = $_POST['role'] ?? '';
        if (!in_array($r, $roles, true)) $error = 'Choose a valid role.';
        elseif ($id === $me && $r !== 'admin') $error = 'You can’t remove your own admin role.';
        else { $s = $pdo->prepare('UPDATE users SET role=? WHERE id=?'); $s->execute([$r, $id]); log_action($pdo, 'Changed user #' . $id . ' role to ' . $r); $success = 'Role updated.'; }
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === $me) $error = 'You can’t delete your own account.';
        else { $s = $pdo->prepare('DELETE FROM users WHERE id=?'); $s->execute([$id]); log_action($pdo, 'Deleted user #' . $id); $success = 'User deleted. Their past orders are kept.'; }
    }
}
$q = trim($_GET['q'] ?? ''); $page = max(1, (int)($_GET['page'] ?? 1)); $per = 10;
$like = '%' . $q . '%';
$c = $pdo->prepare('SELECT COUNT(*) FROM users WHERE name LIKE ? OR email LIKE ?'); $c->execute([$like, $like]);
$total = (int)$c->fetchColumn(); $pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages); $off = ($page - 1) * $per;
$s = $pdo->prepare("SELECT id,name,email,role,created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT $per OFFSET $off"); $s->execute([$like, $like]); $users = $s->fetchAll(); ?>
<div class="page-head"><div><h1>Users &amp; roles</h1><p>Create accounts and control who can do what.</p></div><button class="btn gold" type="button" onclick="document.getElementById('addUser').classList.toggle('hidden')">Add user</button></div>
<?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert success" role="status"><?= e($success) ?></div><?php endif; ?>
<div class="rbac-cards"><div><b>Customer</b><span>Browse menu, order, view own orders</span></div><div><b>Staff</b><span>Manage menu items and all orders</span></div><div><b>Admin</b><span>Everything, plus users and activity log</span></div></div>
<section id="addUser" class="panel <?= $error && ($_POST['action'] ?? '') === 'create' ? '' : 'hidden' ?>"><div class="panel-title"><h2>Add user</h2></div>
<form method="post" class="form-grid" data-validate><?= csrf_field() ?><input type="hidden" name="action" value="create">
<div class="field"><label for="n">Full name</label><input class="input" id="n" name="name" required></div>
<div class="field"><label for="em">Email</label><input class="input" id="em" type="email" name="email" required></div>
<div class="field"><label for="pw">Temporary password</label><input class="input" id="pw" type="password" name="password" minlength="6" required></div>
<div class="field"><label for="ro">Role</label><select id="ro" name="role"><?php foreach ($roles as $r): ?><option value="<?= $r ?>"><?= ucfirst($r) ?></option><?php endforeach; ?></select></div>
<div class="field full"><button class="btn dark">Create user</button></div></form></section>
<section class="panel"><div class="panel-title"><h2>User directory</h2><form method="get" class="inline-form"><label class="skip" for="uq">Search users</label><input class="input" id="uq" name="q" value="<?= e($q) ?>" placeholder="Search name or email"><button class="btn small dark">Search</button></form></div>
<?php if (!$users): ?><p class="muted">No users match “<?= e($q) ?>”.</p><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">User</th><th scope="col">Email</th><th scope="col">Role</th><th scope="col">Joined</th><th scope="col">Access</th></tr></thead><tbody>
<?php foreach ($users as $u): ?><tr><td><div class="user-cell"><span class="avatar" aria-hidden="true"><?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?></span><b><?= e($u['name']) ?></b></div></td><td><?= e($u['email']) ?></td><td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td><td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
<td><div class="actions"><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><label class="skip" for="r<?= (int)$u['id'] ?>">Role for <?= e($u['name']) ?></label><select id="r<?= (int)$u['id'] ?>" name="role"><?php foreach ($roles as $r): ?><option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option><?php endforeach; ?></select><button class="btn small">Save</button></form>
<?php if ((int)$u['id'] !== $me): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="btn danger small" onclick="return confirm('Delete <?= e(addslashes($u['name'])) ?>? This can’t be undone.')">Delete</button></form><?php endif; ?></div></td></tr><?php endforeach; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pager" aria-label="User pages"><?php for ($n = 1; $n <= $pages; $n++): ?><?php if ($n === $page): ?><span class="cur" aria-current="page"><?= $n ?></span><?php else: ?><a href="?<?= e(http_build_query(array_filter(['q' => $q, 'page' => $n]))) ?>"><?= $n ?></a><?php endif; ?><?php endfor; ?></nav><?php endif; endif; ?></section>
<?php include '_bottom.php'; ?>
