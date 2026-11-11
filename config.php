<?php
declare(strict_types=1);
session_start();

$host = 'localhost';
$db   = 'web_transaksi';
$user = 'root';
$pass = ''; // sesuaikan dengan password MySQL Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Koneksi database gagal. Periksa config.php dan pastikan MySQL aktif.');
}

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rupiah($value): string { return 'Rp ' . number_format((float)$value, 0, ',', '.'); }
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!user()) { header('Location: login.php'); exit; }
}
function require_role(array $roles): void {
    require_login();
    if (!in_array(user()['role'], $roles, true)) { http_response_code(403); exit('Akses ditolak.'); }
}
function go(string $url): never { header("Location: $url"); exit; }
