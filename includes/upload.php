<?php
// Menu photos are stored IN THE DATABASE (table product_images), so no folder permissions are needed
// on XAMPP/Mac or on a web host. Photos are validated and re-encoded with GD, then served by image.php.
const MAX_IMAGE_BYTES = 8 * 1024 * 1024; // 8 MB upload limit (photos are shrunk to ~1000px anyway)

// Validate + shrink an uploaded photo. Returns ['mime'=>..., 'data'=>binary] or null (sets $err when something is wrong).
function process_product_image($file, &$err) {
    $err = '';
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null; // nothing chosen
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > MAX_IMAGE_BYTES) { $err = 'That image is too large (server limit ' . ini_get('upload_max_filesize') . ', app limit 8 MB). Choose a smaller photo.'; return null; }
    if ($file['error'] === UPLOAD_ERR_NO_TMP_DIR || $file['error'] === UPLOAD_ERR_CANT_WRITE) { $err = 'PHP has no writable temporary folder for uploads (error ' . $file['error'] . ').'; return null; }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) { $err = 'The image could not be uploaded (PHP error code ' . (int)$file['error'] . '). Please try again.'; return null; }

    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_WEBP => 'image/webp'];
    if (!$info || !isset($types[$info[2]])) { $err = 'Please upload a JPG, PNG or WebP image.'; return null; }

    // Preferred path: resize to max 1000px wide and re-encode as JPEG (also strips hidden payloads)
    $src = false;
    if (extension_loaded('gd')) {
        if ($info[2] === IMAGETYPE_JPEG) $src = @imagecreatefromjpeg($file['tmp_name']);
        elseif ($info[2] === IMAGETYPE_PNG) $src = @imagecreatefrompng($file['tmp_name']);
        elseif ($info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($file['tmp_name']);
    }
    if ($src) {
        $w = imagesx($src); $h = imagesy($src); $nw = min($w, 1000); $nh = (int)round($h * $nw / $w);
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // flatten transparency
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        ob_start(); $ok = imagejpeg($dst, null, 85); $data = ob_get_clean();
        imagedestroy($src); imagedestroy($dst);
        if ($ok && $data !== '' && $data !== false) return ['mime' => 'image/jpeg', 'data' => $data];
    }
    // Fallback if GD is not available: keep the validated file as-is (must be small enough for the database)
    $raw = file_get_contents($file['tmp_name']);
    if ($raw === false || strlen($raw) > 1500000) { $err = 'This server cannot resize images (GD is off). Choose a photo under 1.5 MB.'; return null; }
    return ['mime' => $types[$info[2]], 'data' => $raw];
}

// Save the photo for a product and point products.image at image.php (creates the table on first use).
function store_product_image($pdo, $productId, $img) {
    $pdo->exec('CREATE TABLE IF NOT EXISTS product_images (product_id INT NOT NULL PRIMARY KEY, mime VARCHAR(40) NOT NULL, data MEDIUMBLOB NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)');
    $s = $pdo->prepare('INSERT INTO product_images(product_id,mime,data) VALUES(?,?,?) ON DUPLICATE KEY UPDATE mime=VALUES(mime), data=VALUES(data)');
    $s->bindValue(1, (int)$productId, PDO::PARAM_INT);
    $s->bindValue(2, $img['mime']);
    $s->bindValue(3, $img['data'], PDO::PARAM_LOB);
    $s->execute();
    $path = 'image.php?id=' . (int)$productId . '&v=' . time(); // v= busts the browser cache after a change
    $u = $pdo->prepare('UPDATE products SET image=? WHERE id=?');
    $u->execute([$path, (int)$productId]);
    return $path;
}
