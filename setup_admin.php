<?php
// ============================================
// FILE: setup_admin.php
// Deskripsi: Script satu kali untuk membuat
// akun Admin pertama dengan password ter-hash.
// PENTING: Hapus atau rename file ini setelah
// dijalankan agar tidak bisa diakses publik!
// ============================================

require_once 'koneksi.php';

// ── Data Admin yang akan diinsert ──
$nama_admin  = 'Administrator';
$email_admin = 'alfareza.dev@gmail.com';
$pass_plain  = 'Alfa_reza_512';

// ── Cek apakah email sudah terdaftar ──
$cek_email = mysqli_real_escape_string($koneksi, $email_admin);
$sql_cek   = "SELECT id FROM users WHERE email = '$cek_email' LIMIT 1";
$result    = mysqli_query($koneksi, $sql_cek);

if (mysqli_num_rows($result) > 0) {
    // Sudah ada, jangan insert duplikat
    $pesan   = 'warning';
    $judul   = '⚠️ Sudah Terdaftar!';
    $isi     = "Akun admin dengan email <strong>$email_admin</strong> sudah ada di database.<br>Tidak ada data baru yang diinsert.";
} else {
    // Hash password dengan bcrypt (PASSWORD_DEFAULT)
    $password_hash = password_hash($pass_plain, PASSWORD_DEFAULT);

    $nama_esc = mysqli_real_escape_string($koneksi, $nama_admin);
    $email_esc = $cek_email;
    $hash_esc  = mysqli_real_escape_string($koneksi, $password_hash);

    $sql_insert = "INSERT INTO users (nama, email, password, role)
                   VALUES ('$nama_esc', '$email_esc', '$hash_esc', 'admin')";

    if (mysqli_query($koneksi, $sql_insert)) {
        $pesan = 'success';
        $judul = '✅ Akun Admin Berhasil Dibuat!';
        $isi   = "Email: <strong>$email_admin</strong><br>
                  Password: <strong>$pass_plain</strong> (sudah ter-hash di database)<br>
                  Role: <strong>admin</strong><br><br>
                  <span style='color:#fca5a5;font-weight:600;'>⚠️ PENTING: Segera hapus atau rename file setup_admin.php ini!</span><br>
                  Di terminal: <code>rm setup_admin.php</code>";
    } else {
        $pesan = 'error';
        $judul = '❌ Gagal Insert!';
        $isi   = 'Error MySQL: ' . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin | Lapor-Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:#0f172a; color:#f1f5f9; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card-setup { background:#1e293b; border:1px solid #334155; border-radius:16px; padding:2.5rem; max-width:520px; width:100%; }
        .card-setup h1 { font-size:1.4rem; font-weight:800; margin-bottom:1.5rem; }
        .result-box { border-radius:12px; padding:1.25rem; margin-top:1rem; }
        .result-success { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7; }
        .result-warning { background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.3); color:#fcd34d; }
        .result-error   { background:rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.3);  color:#fca5a5; }
        code { background:rgba(255,255,255,0.1); color:#c084fc; padding:0.2rem 0.5rem; border-radius:4px; font-size:0.85rem; }
        .btn-login { display:inline-flex; align-items:center; gap:0.4rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); color:white; border:none; border-radius:10px; padding:0.65rem 1.5rem; font-weight:700; text-decoration:none; margin-top:1.5rem; transition:all 0.2s; }
        .btn-login:hover { color:white; transform:translateY(-2px); }
    </style>
</head>
<body>
    <div class="card-setup">
        <h1>🔧 Setup Akun Admin</h1>
        <p style="color:#94a3b8;font-size:0.9rem;">Script ini akan membuat akun admin pertama untuk aplikasi Lapor-Sekolah.</p>

        <div class="result-box result-<?= $pesan ?>">
            <strong><?= $judul ?></strong><br>
            <p style="margin:0.75rem 0 0;font-size:0.9rem;line-height:1.7;"><?= $isi ?></p>
        </div>

        <?php if ($pesan === 'success'): ?>
        <a href="login.php" class="btn-login">
            → Pergi ke Halaman Login
        </a>
        <?php endif; ?>
    </div>
</body>
</html>
