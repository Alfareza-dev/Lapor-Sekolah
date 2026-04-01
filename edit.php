<?php
// ============================================
// FILE: edit.php
// Deskripsi: Form edit laporan + proses UPDATE
// data di database dan ganti foto jika ada
// ============================================

session_start();
require_once 'koneksi.php';
require_once 'auth_check.php'; // ← Guard ghost session terpusat

// ── GUARD tambahan: harus admin ──
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// ── Validasi: ID harus ada di URL ──
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// ── Ambil data laporan yang akan diedit ──
$sql_select = "SELECT * FROM laporan_kerusakan WHERE id = $id LIMIT 1";
$result     = mysqli_query($koneksi, $sql_select);

if (mysqli_num_rows($result) === 0) {
    // Data tidak ditemukan, kembali ke index
    header('Location: dashboard.php');
    exit;
}

$laporan = mysqli_fetch_assoc($result);
$errors  = [];

// ── GUARD: Tolak akses jika laporan milik user yang sudah dihapus (user_id NULL) ──
// Ini mencegah blank page atau error saat data relasi tidak lengkap
if (is_null($laporan['user_id'])) {
    header('Location: dashboard.php?pesan=laporan_terkunci');
    exit;
}

// ── Proses form UPDATE (HANYA kolom 'status') ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil & validasi status saja
    $status = trim(mysqli_real_escape_string($koneksi, $_POST['status'] ?? ''));

    if (!in_array($status, ['Menunggu', 'Diproses', 'Selesai'])) {
        $errors[] = 'Pilih status yang valid.';
    }

    // Jalankan UPDATE — HANYA kolom status, data pelapor tidak disentuh
    if (empty($errors)) {
        $sql_update = "UPDATE laporan_kerusakan SET status = '$status' WHERE id = $id";

        if (mysqli_query($koneksi, $sql_update)) {
            header('Location: dashboard.php?pesan=edit_sukses');
            exit;
        } else {
            $errors[] = 'Gagal memperbarui status: ' . mysqli_error($koneksi);
        }
    }

    // Refresh nilai status dari POST supaya form tetap sinkron
    $laporan['status'] = $_POST['status'] ?? $laporan['status'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinjau Laporan #<?= $id ?> | Lapor-Sekolah</title>
    <meta name="description" content="Admin meninjau dan memperbarui status laporan kerusakan fasilitas.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:#4f46e5; --primary-light:#818cf8;
            --dark-bg:#0f172a; --card-bg:#1e293b; --border:#334155;
            --text-primary:#f1f5f9; --text-muted:#94a3b8;
        }
        * { box-sizing: border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark-bg); color:var(--text-primary); min-height:100vh; }

        .navbar-custom { background:rgba(15,23,42,0.95); backdrop-filter:blur(12px); border-bottom:1px solid var(--border); padding:1rem 0; }
        .navbar-brand-custom { font-size:1.4rem; font-weight:800; background:linear-gradient(135deg,var(--primary-light),#c084fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; text-decoration:none; }
        .navbar-brand-custom span { -webkit-text-fill-color:var(--text-primary); }

        .page-header { background:linear-gradient(135deg,#1c2541,#2a254b); border-bottom:1px solid var(--border); padding:2rem 0; }
        .page-header h1 { font-size:1.8rem; font-weight:800; margin:0; }
        .page-header p { color:var(--text-muted); margin:0.5rem 0 0; }

        .form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:2rem; margin:2rem auto; max-width:750px; }

        .form-label-custom { font-weight:600; font-size:0.875rem; margin-bottom:0.4rem; color:var(--primary-light); display:block; }
        .badge-required { background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid rgba(239,68,68,0.3); font-size:0.65rem; padding:0.15rem 0.4rem; border-radius:4px; margin-left:0.3rem; }

        .form-control-custom, .form-select-custom {
            width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border);
            border-radius:10px; padding:0.75rem 1rem; color:var(--text-primary);
            font-size:0.9rem; font-family:'Inter',sans-serif;
            transition:border-color 0.2s,box-shadow 0.2s; outline:none;
        }
        .form-control-custom::placeholder { color:var(--text-muted); }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(79,70,229,0.15); background:rgba(79,70,229,0.05);
        }
        .form-select-custom option { background:var(--card-bg); color:var(--text-primary); }

        /* Foto saat ini */
        .foto-current-box {
            background:rgba(255,255,255,0.03); border:1px solid var(--border);
            border-radius:12px; padding:1rem; margin-bottom:1rem;
        }
        .foto-current-img { max-width:180px; border-radius:10px; border:2px solid var(--border); }
        .checkbox-hapus { display:flex; align-items:center; gap:0.5rem; margin-top:0.75rem; cursor:pointer; }
        .checkbox-hapus input[type=checkbox] { accent-color:#ef4444; width:16px; height:16px; }
        .checkbox-hapus span { font-size:0.85rem; color:#fca5a5; }

        .upload-area {
            border:2px dashed var(--border); border-radius:12px; padding:2rem; text-align:center;
            cursor:pointer; transition:all 0.2s; background:rgba(255,255,255,0.02);
        }
        .upload-area:hover { border-color:var(--primary-light); background:rgba(79,70,229,0.05); }
        .upload-area i { font-size:2rem; color:var(--text-muted); margin-bottom:0.5rem; display:block; }
        .upload-area p { color:var(--text-muted); font-size:0.85rem; margin:0; }
        #foto_bukti { display:none; }

        #preview-container { margin-top:1rem; display:none; }
        #preview-img { max-width:200px; border-radius:10px; border:2px solid var(--border); }

        .btn-submit { background:linear-gradient(135deg,var(--primary),#7c3aed); color:white; border:none; border-radius:10px; padding:0.8rem 2rem; font-weight:700; font-size:0.95rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.2s; box-shadow:0 4px 15px rgba(79,70,229,0.35); width:100%; justify-content:center; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(79,70,229,0.5); }
        .btn-back { background:rgba(255,255,255,0.05); color:var(--text-muted); border:1px solid var(--border); border-radius:10px; padding:0.8rem 2rem; font-weight:600; font-size:0.95rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.2s; }
        .btn-back:hover { color:var(--text-primary); background:rgba(255,255,255,0.08); }

        .alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.5rem; color:#fca5a5; }
        .alert-error ul { margin:0.5rem 0 0; padding-left:1.2rem; }
        .alert-error li { font-size:0.875rem; margin-bottom:0.25rem; }

        .section-divider { border:none; border-top:1px solid var(--border); margin: 1.75rem 0; }
        .form-group { margin-bottom:1.25rem; }

        .id-badge { background:rgba(79,70,229,0.2); border:1px solid rgba(79,70,229,0.4); color:var(--primary-light); padding:0.3rem 0.8rem; border-radius:8px; font-size:0.85rem; font-weight:700; }

        /* Read-only field styling */
        .form-control-readonly {
            width:100%; background:rgba(255,255,255,0.03); border:1px solid rgba(51,65,85,0.6);
            border-radius:10px; padding:0.75rem 1rem; color:var(--text-muted);
            font-size:0.9rem; font-family:'Inter',sans-serif; cursor:not-allowed;
            user-select:none;
        }
        footer { background:var(--card-bg); border-top:1px solid var(--border); padding:1.5rem 0; text-align:center; color:var(--text-muted); font-size:0.85rem; margin-top:3rem; }
</head>
<body>

<nav class="navbar-custom sticky-top">
    <div class="container d-flex align-items-center">
        <a class="navbar-brand-custom" href="dashboard.php">
            <i class="bi bi-shield-exclamation me-2" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:rgba(129,140,248,0.15);border:1px solid rgba(129,140,248,0.4);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-eye-fill" style="color:#818cf8;font-size:1.3rem;"></i>
            </div>
            <div>
                <h1>Tinjau Laporan <span class="id-badge">ID #<?= $id ?></span></h1>
                <p>Data laporan bersifat <strong style="color:#fcd34d;">read-only</strong>. Admin hanya dapat mengubah status.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="form-card">

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan! Harap perbaiki:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>

            <!-- ══ INFO PELAPOR (READ-ONLY) ══ -->
            <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;">
                <p style="font-size:0.75rem;color:#fcd34d;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem;">
                    <i class="bi bi-lock-fill me-1"></i> Data Asli Pelapor — Read Only
                </p>
                <div class="form-group">
                    <label class="form-label-custom">Nama Pelapor</label>
                    <div class="form-control-readonly"><?= htmlspecialchars($laporan['nama_pelapor']) ?></div>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Fasilitas yang Rusak</label>
                    <div class="form-control-readonly"><?= htmlspecialchars($laporan['fasilitas']) ?></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label-custom">Deskripsi Kerusakan</label>
                    <div class="form-control-readonly" style="min-height:80px;line-height:1.6;"><?= htmlspecialchars($laporan['deskripsi']) ?></div>
                </div>
            </div>

            <hr class="section-divider">

            <!-- ══ STATUS (BISA DIUBAH ADMIN) ══ -->
            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-arrow-repeat me-1"></i> Ubah Status Laporan
            </h6>
            <div class="form-group">
                <label class="form-label-custom" for="status">Status Saat Ini</label>
                <select id="status" name="status" class="form-select-custom">
                    <option value="Menunggu" <?= $laporan['status'] === 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu Ditindaklanjuti</option>
                    <option value="Diproses" <?= $laporan['status'] === 'Diproses' ? 'selected' : '' ?>>🔄 Sedang Diproses</option>
                    <option value="Selesai"  <?= $laporan['status'] === 'Selesai'  ? 'selected' : '' ?>>✅ Selesai Diperbaiki</option>
                </select>
            </div>

            <hr class="section-divider">

            <div class="d-flex gap-3">
                <a href="dashboard.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check2-circle"></i> Simpan Status
                </button>
            </div>

        </form>
    </div>
</div>

<footer>
    <div class="container">
        <p><i class="bi bi-shield-check me-1" style="color:#818cf8;"></i><strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
