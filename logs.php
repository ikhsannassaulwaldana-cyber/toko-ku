<?php
require 'config.php';require_role(['admin','kasir']);require 'partials.php';
$rows=$pdo->query('SELECT t.*,u.nama,u.role FROM transactions t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC')->fetchAll();
layout_start('Log Kasir','log'); ?>
<div class="panel"><div class="panel-head"><h3>Riwayat seluruh transaksi</h3><span class="badge"><?= count($rows) ?> transaksi</span></div><div class="table-wrap"><table><thead><tr><th>Kode transaksi</th><th>Nama pengguna</th><th>Role</th><th>Total</th><th>Waktu</th><th>Struk</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><b><?= e($r['kode']) ?></b></td><td><?= e($r['nama']) ?></td><td><?= e(ucfirst($r['role'])) ?></td><td><?= rupiah($r['total']) ?></td><td><?= e($r['created_at']) ?></td><td><a class="btn btn-soft" href="receipt.php?id=<?= $r['id'] ?>">Lihat struk</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6">Belum ada transaksi.</td></tr><?php endif; ?></tbody></table></div></div>
<?php layout_end(); ?>
