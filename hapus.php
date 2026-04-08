<?php
session_start();
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: /dashboard');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /');
    exit;
}

$id = (int) $_GET['id'];

$sql_select = "SELECT foto_bukti FROM laporan_kerusakan WHERE id = $id LIMIT 1";
$result     = mysqli_query($koneksi, $sql_select);

if (mysqli_num_rows($result) === 0) {
    header('Location: /dashboard');
    exit;
}

$laporan   = mysqli_fetch_assoc($result);
$foto_file = $laporan['foto_bukti'];

$sql_hapus = "DELETE FROM laporan_kerusakan WHERE id = $id";

if (mysqli_query($koneksi, $sql_hapus)) {
    if (!empty($foto_file)) {
        $foto_path = 'uploads/' . $foto_file;
        if (file_exists($foto_path)) {
            unlink($foto_path);
        }
    }

    header('Location: /dashboard?pesan=hapus_sukses');
    exit;

} else {
    die('
    <!DOCTYPE html><html lang="id"><head>
    <meta charset="UTF-8"><title>Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-dark text-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center p-4">
        <i class="fs-1">❌</i>
        <h4 class="mt-3">Gagal Menghapus Laporan</h4>
        <p class="text-muted">' . mysqli_error($koneksi) . '</p>
        <a href="/dashboard" class="btn btn-outline-light mt-2">← Kembali ke Dashboard</a>
    </div>
    </body></html>
    ');
}
?>
