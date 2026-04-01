<?php
// ============================================
// FILE: hapus.php
// Deskripsi: Proses DELETE data laporan dari
// database dan hapus file foto dari server
// ============================================

require_once 'koneksi.php';

// ── 1. Validasi parameter ID ──
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// ── 2. Ambil data laporan untuk mendapatkan nama file foto ──
$sql_select = "SELECT foto_bukti FROM laporan_kerusakan WHERE id = $id LIMIT 1";
$result     = mysqli_query($koneksi, $sql_select);

if (mysqli_num_rows($result) === 0) {
    // ID tidak ditemukan, kembali ke halaman utama
    header('Location: index.php');
    exit;
}

$laporan   = mysqli_fetch_assoc($result);
$foto_file = $laporan['foto_bukti'];

// ── 3. Hapus data dari database ──
$sql_hapus = "DELETE FROM laporan_kerusakan WHERE id = $id";

if (mysqli_query($koneksi, $sql_hapus)) {

    // ── 4. Hapus file foto dari server jika ada ──
    if (!empty($foto_file)) {
        $foto_path = 'uploads/' . $foto_file;
        if (file_exists($foto_path)) {
            unlink($foto_path);
        }
    }

    // Redirect ke index dengan pesan sukses
    header('Location: index.php?pesan=hapus_sukses');
    exit;

} else {
    // Jika query gagal, tampilkan pesan error sederhana
    die('
    <!DOCTYPE html><html lang="id"><head>
    <meta charset="UTF-8"><title>Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-dark text-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center p-4">
        <i class="fs-1">❌</i>
        <h4 class="mt-3">Gagal Menghapus Laporan</h4>
        <p class="text-muted">' . mysqli_error($koneksi) . '</p>
        <a href="index.php" class="btn btn-outline-light mt-2">← Kembali ke Daftar</a>
    </div>
    </body></html>
    ');
}
?>
