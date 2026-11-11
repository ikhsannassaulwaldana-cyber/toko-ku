<?php
require 'config.php';
if(user()) go('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $q=$pdo->prepare('SELECT * FROM users WHERE username=? LIMIT 1');$q->execute([trim($_POST['username']??'')]);$row=$q->fetch();
 if($row && password_verify($_POST['password']??'',$row['password'])){session_regenerate_id(true);$_SESSION['user']=$row;go('index.php');}
 $error='Username atau password salah.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><link rel="stylesheet" href="style.css"></head><body class="auth"><form class="auth-card" method="post"><div class="brand" style="color:#171a35;padding:0 0 24px">Transaksi<span>Ku.</span></div><h1>Selamat datang 👋</h1><p class="sub" style="margin-bottom:25px">Masuk untuk melanjutkan ke aplikasi transaksi.</p><?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><div class="field" style="margin-bottom:15px"><label>Username</label><input class="input" name="username" required autofocus></div><div class="field" style="margin-bottom:20px"><label>Password</label><input class="input" type="password" name="password" required></div><button class="btn" style="width:100%;padding:13px">Masuk ke aplikasi →</button><p class="muted" style="margin-top:20px">Demo: admin / kasir / pelanggan · password: password</p></form></body></html>
