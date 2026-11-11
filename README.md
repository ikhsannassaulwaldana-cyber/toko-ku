WEB TRANSAKSIKU - PHP + MYSQL

FITUR
- Login dengan 3 role: admin, kasir, pelanggan.
- Admin dan kasir dapat tambah, edit, dan hapus barang.
- Pelanggan hanya dapat melihat barang dan membeli.
- Kasir/admin dapat melihat log seluruh transaksi.
- Admin dapat melihat laporan penjualan harian, mingguan, bulanan, dan tahunan,
  memfilter tanggal, mencetak laporan, serta mengekspor rincian ke CSV untuk Excel.
- Setiap pembelian membuat transaksi, mengurangi stok, dan menghasilkan struk.
- Struk dapat dicetak melalui tombol Cetak struk.
- UI responsif, sidebar mobile dengan animasi, hover dan transisi.

CARA MENJALANKAN (XAMPP + VS CODE)
1. Ekstrak folder web_transaksi_kasir ke C:\xampp\htdocs\
2. Buka XAMPP, jalankan Apache dan MySQL.
3. Buka http://localhost/phpmyadmin
4. Pilih tab Import, pilih file database.sql dari folder ini, lalu klik Import/Go.
5. Buka config.php. Sesuaikan $user dan $pass MySQL jika berbeda.
6. Buka VS Code, Open Folder: C:\xampp\htdocs\web_transaksi_kasir
7. Buka browser: http://localhost/web_transaksi_kasir/

AKUN DEMO
Admin:      username admin      password password
Kasir:      username kasir      password password
Pelanggan:  username pelanggan  password password

CATATAN PENTING
- Akun demo memakai hash password yang disediakan untuk password "password".
- Untuk penggunaan sungguhan, ganti semua password demo dan tambahkan CSRF protection,
  validasi pembayaran, serta aturan audit yang sesuai.
- Sistem demo ini menyelesaikan pembelian satu jenis barang per transaksi agar alurnya
  mudah dipelajari. Fitur keranjang multi-item dapat ditambahkan berikutnya.
