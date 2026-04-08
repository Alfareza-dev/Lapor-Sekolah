<?php
// ============================================
// FILE: auth_check.php
// Deskripsi: Pengecekan "Ghost Session" di level server.
//
// CARA PAKAI: require_once file ini SETELAH
// session_start() dan require_once 'koneksi.php'
// di setiap halaman yang diproteksi.
//
// Urutan yang benar di file tujuan:
//   session_start();
//   require_once 'koneksi.php';
//   require_once 'auth_check.php';  ← di sini
//
// ============================================

// ── Langkah 1: Pastikan session ada ──
// Jika tidak ada user_id di session sama sekali,
// redirect ke login (guard dasar).
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit; // ← WAJIB: hentikan eksekusi script setelah redirect
}

// ── Langkah 2: Cek apakah user MASIH ADA di database ──
// Ini mengatasi "Ghost Session": kondisi di mana
// Admin menghapus user, tapi user itu masih punya
// session aktif dan bisa terus mengakses halaman.
// Tanpa exit di sini, script pemanggil (mis. tambah.php)
// akan terus mengeksekusi INSERT dan memicu FK error.
$ghost_check_id = (int) $_SESSION['user_id'];

$ghost_sql    = "SELECT id FROM users WHERE id = $ghost_check_id LIMIT 1";
$ghost_result = mysqli_query($koneksi, $ghost_sql);

if (!$ghost_result || mysqli_num_rows($ghost_result) === 0) {
    // ⚠️ User tidak ditemukan di DB = akun sudah dihapus admin
    // Hancurkan session agar tidak ada bekas data lama
    session_unset();
    session_destroy();

    // Redirect ke login dengan parameter khusus
    // login.php akan menampilkan SweetAlert2 dari parameter ini
    header('Location: /login?pesan=akun_dihapus');
    exit; // ← KRITIS: tanpa ini, script tetap berjalan → FK constraint error!
}

// ── Langkah 3: Bersihkan variabel sementara ──
// Agar tidak bocor ke scope file yang meng-include ini
unset($ghost_check_id, $ghost_sql, $ghost_result);
?>
