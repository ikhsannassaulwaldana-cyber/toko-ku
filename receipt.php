<?php
require 'config.php';require_login();$id=(int)($_GET['id']??0);
$q=$pdo->prepare('SELECT t.*,u.nama FROM transactions t JOIN users u ON u.id=t.user_id WHERE t.id=?');$q->execute([$id]);$trx=$q->fetch();
if(!$trx){http_response_code(404);exit('Struk tidak ditemukan.');}
if(user()['role']==='pelanggan' && (int)$trx['user_id']!==(int)user()['id']){http_response_code(403);exit('Anda tidak dapat melihat struk ini.');}
$q=$pdo->prepare('SELECT * FROM transaction_items WHERE transaction_id=?');$q->execute([$id]);$items=$q->fetchAll();
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Struk <?= e($trx['kode']) ?></title><link rel="stylesheet" href="style.css"></head><body>
<div class="receipt"><h2>TRANSAKSIKU</h2><div style="text-align:center;font-size:11px">Bukti pembayaran transaksi</div><hr><div class="receipt-row"><span>Kode</span><b><?= e($trx['kode']) ?></b></div><div class="receipt-row"><span>Tanggal</span><span><?= e($trx['created_at']) ?></span></div><div class="receipt-row"><span>Kasir/Pembeli</span><span><?= e($trx['nama']) ?></span></div><hr>
<?php foreach($items as $item): ?><div class="receipt-row"><span><?= e($item['nama_produk']) ?><br><?= (int)$item['qty'] ?> x <?= rupiah($item['harga']) ?></span><b><?= rupiah($item['subtotal']) ?></b></div><?php endforeach; ?><hr><div class="receipt-row" style="font-size:16px"><b>TOTAL</b><b><?= rupiah($trx['total']) ?></b></div><hr><div style="text-align:center;font-size:12px">Terima kasih telah berbelanja!</div></div>
<div class="actions no-print" style="justify-content:center"><button class="btn" onclick="window.print()">🖨 Cetak struk</button><a class="btn btn-soft" href="index.php">Kembali</a></div></body></html>
