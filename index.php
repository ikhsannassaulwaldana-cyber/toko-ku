<?php
require 'config.php';require_login();require 'partials.php';
$totalProducts=(int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalSales=(float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM transactions')->fetchColumn();
$totalTransactions=(int)$pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
$recent=$pdo->query('SELECT t.*,u.nama FROM transactions t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 8')->fetchAll();
layout_start('Dashboard','dashboard');
?>
<div class="grid">
<div class="stat"><span class="stat-icon">▣</span><div class="stat-label">Total Barang</div><div class="stat-value"><?= $totalProducts ?></div></div>
<div class="stat"><span class="stat-icon">↗</span><div class="stat-label">Total Penjualan</div><div class="stat-value" style="font-size:21px"><?= rupiah($totalSales) ?></div></div>
<div class="stat"><span class="stat-icon">▤</span><div class="stat-label">Transaksi</div><div class="stat-value"><?= $totalTransactions ?></div></div>
<div class="stat"><span class="stat-icon">♙</span><div class="stat-label">Login sebagai</div><div class="stat-value" style="font-size:21px"><?= e(ucfirst(user()['role'])) ?></div></div>
</div>
<div class="panel"><div class="panel-head"><h3>Transaksi Terbaru</h3><a class="btn btn-soft" href="logs.php">Lihat log</a></div><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Kasir/Pembeli</th><th>Total</th><th>Tanggal</th><th></th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><b><?= e($r['kode']) ?></b></td><td><?= e($r['nama']) ?></td><td><?= rupiah($r['total']) ?></td><td><?= e($r['created_at']) ?></td><td><a class="btn btn-soft" href="receipt.php?id=<?= $r['id'] ?>">Struk</a></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="5">Belum ada transaksi.</td></tr><?php endif; ?></tbody></table></div></div>
<?php layout_end(); ?>
