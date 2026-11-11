CREATE DATABASE IF NOT EXISTS web_transaksi;
USE web_transaksi;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir','pelanggan') NOT NULL DEFAULT 'pelanggan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jika tabel products sudah ada sebelumnya, jalankan:
-- ALTER TABLE products ADD COLUMN gambar VARCHAR(255) NULL DEFAULT NULL;

CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(40) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transaction_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transaction_id INT NOT NULL,
  product_id INT NULL,
  nama_produk VARCHAR(120) NOT NULL,
  harga DECIMAL(12,2) NOT NULL,
  qty INT NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Password default semua akun: password
-- Ganti password setelah login pertama kali.
INSERT INTO users (nama,username,password,role) VALUES
('Administrator','admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llS3SxkL4y2K8VQ4vV5eK', 'admin'),
('Kasir Utama','kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llS3SxkL4y2K8VQ4vV5eK', 'kasir'),
('Pelanggan','pelanggan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llS3SxkL4y2K8VQ4vV5eK', 'pelanggan');

INSERT INTO products (nama,harga,stok) VALUES
('Kopi Susu',15000,30),('Roti Cokelat',12000,25),('Air Mineral',5000,50),('Mie Goreng',14000,20);
