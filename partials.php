<?php
function layout_start(string $title, string $active=''): void {
    $u = user(); ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | TransaksiKu</title><link rel="stylesheet" href="style.css"></head><body>
<div class="layout"><aside class="sidebar" id="sidebar"><div class="brand">Transaksi<span>Ku.</span></div>
<div class="nav-title">MENU UTAMA</div>
<a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="index.php">▦ <span>Dashboard</span></a>
<a class="nav-link <?= $active==='produk'?'active':'' ?>" href="products.php">▣ <span>Daftar Barang</span></a>
<a class="nav-link <?= $active==='transaksi'?'active':'' ?>" href="shop.php">🛒 <span>Transaksi</span></a>
<?php if(in_array($u['role'],['admin','kasir'],true)): ?><a class="nav-link <?= $active==='log'?'active':'' ?>" href="logs.php">◷ <span>Log Kasir</span></a><?php endif; ?>
<?php if($u['role']==='admin'): ?><a class="nav-link <?= $active==='reports'?'active':'' ?>" href="reports.php">▥ <span>Laporan Penjualan</span></a><?php endif; ?>
<?php if($u['role']==='admin'): ?><a class="nav-link <?= $active==='users'?'active':'' ?>" href="users.php">♙ <span>Pengguna</span></a><?php endif; ?>
<div class="nav-title" style="margin-top:25px">AKUN</div><a class="nav-link" href="logout.php">↪ <span>Keluar</span></a></aside>
<main class="main"><header class="topbar"><div style="display:flex;align-items:center;gap:12px"><button class="menu-btn" onclick="toggleMenu()">☰</button><div><h1 class="page-title"><?= e($title) ?></h1><div class="sub">Kelola penjualan dengan lebih mudah</div></div></div><div class="user-pill">● <?= e($u['nama']) ?> · <?= e(ucfirst($u['role'])) ?></div></header>
<?php }
function layout_end(): void { ?>
</main></div><script>
function toggleMenu(){document.getElementById('sidebar').classList.toggle('open')}
document.querySelectorAll('.nav-link').forEach(link=>link.addEventListener('click',()=>{link.classList.add('active')}));
</script></body></html><?php }
