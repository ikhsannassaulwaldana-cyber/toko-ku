<?php
require 'config.php';require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $id=(int)($_POST['product_id']??0);$qty=max(1,(int)($_POST['qty']??1));
 $q=$pdo->prepare('SELECT * FROM products WHERE id=?');$q->execute([$id]);$p=$q->fetch();
 if($p&&$p['stok']>=$qty){
  $pdo->beginTransaction();
  try{$kode='TRX'.date('ymdHis').random_int(10,99);$total=$p['harga']*$qty;
   $pdo->prepare('INSERT INTO transactions(kode,user_id,total) VALUES(?,?,?)')->execute([$kode,user()['id'],$total]);$tid=(int)$pdo->lastInsertId();
   $pdo->prepare('INSERT INTO transaction_items(transaction_id,product_id,nama_produk,harga,qty,subtotal) VALUES(?,?,?,?,?,?)')->execute([$tid,$p['id'],$p['nama'],$p['harga'],$qty,$total]);
   $pdo->prepare('UPDATE products SET stok=stok-? WHERE id=?')->execute([$qty,$id]);$pdo->commit();go('receipt.php?id='.$tid);
  }catch(Throwable $e){$pdo->rollBack();exit('Transaksi gagal diproses.');}
 }
 $error='Stok tidak cukup atau barang tidak ditemukan.';
}
$products=$pdo->query('SELECT * FROM products WHERE stok>0 ORDER BY nama')->fetchAll();require 'partials.php';layout_start('Transaksi','transaksi');
?>
<?php if(!empty($error)): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<div class="panel-head"><div><h3>Pilih barang untuk dibeli</h3><div class="sub">Tekan tombol beli untuk menyelesaikan transaksi.</div></div></div>
<?php if($products): ?>
<div class="shop-search">
    <label class="shop-search-label" for="productSearch">Cari barang</label>
    <div class="shop-search-control">
        <span class="shop-search-icon" aria-hidden="true">⌕</span>
        <input class="input shop-search-input" type="search" id="productSearch" placeholder="Ketik nama atau merek barang..." autocomplete="off" aria-controls="shopProductGrid">
        <button class="shop-search-clear" id="clearProductSearch" type="button" aria-label="Hapus pencarian" title="Hapus pencarian" hidden>&times;</button>
    </div>
    <div class="shop-search-hint">Hasil akan muncul otomatis saat Anda mengetik.</div>
</div>
<div class="product-grid" id="shopProductGrid"><?php foreach($products as $p): ?><div class="product-card" data-product-name="<?= e($p['nama']) ?>"><div class="product-art"><?php if(!empty($p['gambar'])): ?><img class="shop-product-image" src="<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>"><?php else: ?>No Image<?php endif; ?></div><h4><?= e($p['nama']) ?></h4><div class="muted">Stok tersedia: <?= (int)$p['stok'] ?></div><div class="price"><?= rupiah($p['harga']) ?></div><form method="post" class="actions"><input type="hidden" name="product_id" value="<?= $p['id'] ?>"><input class="input" style="width:75px" type="number" name="qty" min="1" max="<?= (int)$p['stok'] ?>" value="1"><button class="btn">Beli sekarang</button></form></div><?php endforeach; ?></div>
<div class="shop-search-empty" id="productSearchEmpty" role="status" aria-live="polite" hidden>Barang tidak ditemukan</div>
<script>
(() => {
    const searchInput = document.getElementById('productSearch');
    const clearButton = document.getElementById('clearProductSearch');
    const emptyMessage = document.getElementById('productSearchEmpty');
    const cards = Array.from(document.querySelectorAll('#shopProductGrid .product-card'));

    function filterProducts() {
        const query = searchInput.value.trim().toLocaleLowerCase('id-ID');
        let visibleCount = 0;

        cards.forEach((card) => {
            const name = card.dataset.productName.toLocaleLowerCase('id-ID');
            const matches = name.includes(query);
            card.hidden = !matches;
            if (matches) visibleCount++;
        });

        clearButton.hidden = query.length === 0;
        emptyMessage.hidden = visibleCount > 0;
    }

    searchInput.addEventListener('input', filterProducts);
    clearButton.addEventListener('click', () => {
        searchInput.value = '';
        filterProducts();
        searchInput.focus();
    });
})();
</script>
<?php elseif(!$products): ?><div class="panel">Belum ada barang yang tersedia.</div><?php endif; ?>
<?php layout_end(); ?>