<?php
require 'config.php';
require_login();

try {
    $columnCheck = $pdo->query("SHOW COLUMNS FROM products LIKE 'gambar'");
    $hasImageColumn = $columnCheck && $columnCheck->fetch() !== false;
    if (!$hasImageColumn) {
        $pdo->exec("ALTER TABLE products ADD COLUMN gambar VARCHAR(255) NULL DEFAULT NULL");
    }
} catch (Throwable $e) {
    error_log('Gagal memastikan kolom gambar produk: ' . $e->getMessage());
}

$canManage = in_array(user()['role'], ['admin', 'kasir'], true);
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function delete_product_image(?string $imagePath): void {
    if (!$imagePath) {
        return;
    }

    $fullPath = __DIR__ . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $imagePath), DIRECTORY_SEPARATOR);
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role(['admin', 'kasir']);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $existing = $pdo->prepare('SELECT gambar FROM products WHERE id=?');
        $existing->execute([$id]);
        $row = $existing->fetch();

        if ($row && $row['gambar']) {
            delete_product_image($row['gambar']);
        }

        $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $harga = (float)($_POST['harga'] ?? 0);
        $stok = (int)($_POST['stok'] ?? 0);

        if ($nama !== '' && $harga >= 0 && $stok >= 0) {
            $gambar = null;
            if ($id) {
                $current = $pdo->prepare('SELECT gambar FROM products WHERE id=?');
                $current->execute([$id]);
                $gambar = $current->fetchColumn() ?: null;
            }

            $removeGambar = isset($_POST['remove_gambar']) && $_POST['remove_gambar'] === '1';
            if ($removeGambar) {
                delete_product_image($gambar);
                $gambar = null;
            }

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['gambar'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowedExt, true) && $file['size'] <= 2 * 1024 * 1024) {
                        $newName = 'product_' . uniqid('', true) . '.' . $ext;
                        $target = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            if ($gambar) {
                                delete_product_image($gambar);
                            }
                            $gambar = 'uploads/products/' . $newName;
                        }
                    }
                }
            }

            if ($id) {
                $pdo->prepare('UPDATE products SET nama=?, harga=?, stok=?, gambar=? WHERE id=?')->execute([$nama, $harga, $stok, $gambar, $id]);
            } else {
                $pdo->prepare('INSERT INTO products(nama, harga, stok, gambar) VALUES(?,?,?,?)')->execute([$nama, $harga, $stok, $gambar]);
            }
        }
    }

    header('Location: products.php');
    exit;
}

$edit = null;
if (isset($_GET['edit']) && $canManage) {
    $q = $pdo->prepare('SELECT * FROM products WHERE id=?');
    $q->execute([(int)$_GET['edit']]);
    $edit = $q->fetch();
}

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();
require 'partials.php';
layout_start('Daftar Barang', 'produk');
?>
<?php if ($canManage): ?>
<div class="panel">
    <div class="panel-head"><h3><?= $edit ? 'Edit Barang' : 'Tambah Barang' ?></h3></div>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="form-grid">
            <div class="field">
                <label>Nama barang</label>
                <input class="input" name="nama" required value="<?= e($edit['nama'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Harga (Rp)</label>
                <input class="input" type="number" min="0" name="harga" required value="<?= e($edit['harga'] ?? '') ?>">
            </div>
            <div class="field">
                <label>Stok</label>
                <input class="input" type="number" min="0" name="stok" required value="<?= e($edit['stok'] ?? '') ?>">
            </div>
            <div class="field field-full">
                <label>Gambar produk</label>
                <input class="input" type="file" name="gambar" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                <?php if (!empty($edit['gambar'])): ?>
                    <div class="current-image-preview">
                        <img src="<?= e($edit['gambar']) ?>" alt="Foto produk">
                        <label class="checkbox-inline"><input type="checkbox" name="remove_gambar" value="1"> Hapus foto</label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="actions" style="margin-top:15px">
            <button class="btn">Simpan barang</button>
            <?php if ($edit): ?><a class="btn btn-soft" href="products.php">Batal</a><?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>
<div class="panel">
    <div class="panel-head"><h3>Semua Barang</h3><span class="badge"><?= count($products) ?> item</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <?php if ($canManage): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['gambar'])): ?>
                            <img class="product-thumb" src="<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>">
                        <?php else: ?>
                            <div class="product-thumb placeholder">No Image</div>
                        <?php endif; ?>
                    </td>
                    <td><b><?= e($p['nama']) ?></b></td>
                    <td><?= rupiah($p['harga']) ?></td>
                    <td><?= (int)$p['stok'] ?></td>
                    <?php if ($canManage): ?>
                        <td>
                            <div class="actions">
                                <a class="btn btn-soft" href="?edit=<?= $p['id'] ?>">Edit</a>
                                <form method="post" onsubmit="return confirm('Hapus barang ini?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button class="btn btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$canManage): ?><p class="muted">Mode pelanggan: hanya dapat melihat barang dan melakukan pembelian.</p><?php endif; ?>
</div>
<?php layout_end(); ?>