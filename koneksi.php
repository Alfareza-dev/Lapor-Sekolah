<?php
// ============================================
// FILE: koneksi.php
// Deskripsi: Konfigurasi koneksi ke database MySQL
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Ganti sesuai user MySQL kamu
define('DB_PASS', '');            // Ganti sesuai password MySQL kamu
define('DB_NAME', 'lapor_sekolah');

// Membuat koneksi
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">
        <h3>❌ Koneksi Database Gagal!</h3>
        <p>' . mysqli_connect_error() . '</p>
     </div>');
}

// Set encoding agar karakter Indonesia tampil dengan benar
mysqli_set_charset($koneksi, 'utf8mb4');
?>
