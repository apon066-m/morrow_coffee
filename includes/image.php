<?php
// Serves a menu photo that was uploaded through the admin (stored in the product_images table).
require 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$row = false;
try { $s = $pdo->prepare('SELECT mime, data FROM product_images WHERE product_id=?'); $s->execute([$id]); $row = $s->fetch(); }
catch (Throwable $ex) { $row = false; }
if (!$row) { http_response_code(404); exit; }
header('Content-Type: ' . $row['mime']);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . strlen($row['data']));
echo $row['data'];
