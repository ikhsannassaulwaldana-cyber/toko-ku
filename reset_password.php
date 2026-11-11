
<?php
require 'config.php';

$password = password_hash('password', PASSWORD_DEFAULT);

$akun = ['admin', 'kasir', 'pelanggan'];

foreach ($akun as $username) {
    $stmt = $pdo->prepare(
        "UPDATE users SET password = ? WHERE username = ?"
    );

    $stmt->execute([$password, $username]);
}

echo "Password semua akun berhasil diperbaiki!";
?>
