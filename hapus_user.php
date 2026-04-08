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

$admin_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /kelola-user');
    exit;
}

$target_id = (int) $_GET['id'];

if ($target_id === $admin_id) {
    header('Location: /kelola-user');
    exit;
}

$sql_cek = "SELECT id, nama, email, role FROM users WHERE id = $target_id LIMIT 1";
$result  = mysqli_query($koneksi, $sql_cek);

if (mysqli_num_rows($result) === 0) {
    header('Location: /kelola-user');
    exit;
}

$user_target = mysqli_fetch_assoc($result);

if ($user_target['role'] === 'admin') {
    header('Location: /kelola-user');
    exit;
}

$sql_hapus = "DELETE FROM users WHERE id = $target_id";

if (mysqli_query($koneksi, $sql_hapus)) {
    header('Location: /kelola-user?pesan=hapus_user_sukses');
    exit;
} else {
    die('
    <!DOCTYPE html><html lang="id"><head>
    <meta charset="UTF-8"><title>Error Hapus User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-dark text-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center p-4" style="max-width:500px;">
        <div style="font-size:3rem;">❌</div>
        <h4 class="mt-3">Gagal Menghapus Akun User</h4>
        <p class="text-muted mt-2">' . htmlspecialchars(mysqli_error($koneksi)) . '</p>
        <a href="/kelola-user" class="btn btn-outline-light mt-3">
            ← Kembali ke Kelola User
        </a>
    </div>
    </body></html>
    ');
}
?>
