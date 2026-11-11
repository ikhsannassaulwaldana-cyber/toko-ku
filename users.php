<?php
require 'config.php';require_role(['admin']);
if($_SERVER['REQUEST_METHOD']==='POST'){
 $nama=trim($_POST['nama']??'');$username=trim($_POST['username']??'');$role=$_POST['role']??'pelanggan';$password=$_POST['password']??'';
 if($nama&&$username&&strlen($password)>=6&&in_array($role,['admin','kasir','pelanggan'],true)){
  try{$s=$pdo->prepare('INSERT INTO users(nama,username,password,role) VALUES(?,?,?,?)');$s->execute([$nama,$username,password_hash($password,PASSWORD_DEFAULT),$role]);}catch(PDOException $e){$error='Username sudah digunakan.';}
 }else{$error='Lengkapi data. Password minimal 6 karakter.';}
}
$rows=$pdo->query('SELECT id,nama,username,role,created_at FROM users ORDER BY id DESC')->fetchAll();require 'partials.php';layout_start('Pengguna','users'); ?>
<?php if(!empty($error)): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<div class="panel"><div class="panel-head"><h3>Tambah akun pengguna</h3></div><form method="post"><div class="form-grid"><div class="field"><label>Nama lengkap</label><input class="input" name="nama" required></div><div class="field"><label>Username</label><input class="input" name="username" required></div><div class="field"><label>Password (min. 6 karakter)</label><input class="input" type="password" name="password" minlength="6" required></div><div class="field"><label>Role</label><select class="select" name="role"><option value="admin">Admin</option><option value="kasir">Kasir</option><option value="pelanggan" selected>Pelanggan</option></select></div></div><button class="btn" style="margin-top:15px">Buat akun</button></form></div>
<div class="panel"><div class="panel-head"><h3>Daftar pengguna</h3></div><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['nama']) ?></td><td><?= e($r['username']) ?></td><td><span class="badge"><?= e($r['role']) ?></span></td><td><?= e($r['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php layout_end(); ?>
