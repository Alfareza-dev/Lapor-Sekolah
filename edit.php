<?php
// ============================================
// FILE: edit.php
// Deskripsi: Admin meninjau laporan (read-only)
// dan mengubah status laporan.
// ============================================

session_start();
require_once 'koneksi.php';
require_once 'auth_check.php'; // Guard ghost session terpusat

// ── GUARD 1: Harus admin ──
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// ── GUARD 2: Parameter ID wajib ada dan numerik ──
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int) $_GET['id'];

// ── GUARD 3: Ambil data laporan, pastikan ada di DB ──
$sql_select = "SELECT * FROM laporan_kerusakan WHERE id = $id LIMIT 1";
$res_laporan = mysqli_query($koneksi, $sql_select);

if (!$res_laporan || mysqli_num_rows($res_laporan) === 0) {
    header('Location: dashboard.php');
    exit;
}

$laporan = mysqli_fetch_assoc($res_laporan);

// ── GUARD 4: Tolak akses jika laporan milik user yang sudah dihapus ──
// user_id NULL artinya akun pelapor sudah dihapus dari tabel users.
// Akses ditolak agar tidak ada crash saat merender data relasi NULL.
if (is_null($laporan['user_id'])) {
    header('Location: dashboard.php?pesan=laporan_terkunci');
    exit;
}

// ── Semua guard lolos — proses POST jika ada ──
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = trim($_POST['status'] ?? '');
    // Ambil catatan admin (boleh kosong)
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    if (!in_array($status, ['Menunggu', 'Diproses', 'Selesai'])) {
        $errors[] = 'Pilih status yang valid.';
    }

    if (empty($errors)) {
        // Gunakan Prepared Statement untuk keamanan penuh
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE laporan_kerusakan SET status = ?, catatan_admin = ? WHERE id = ?"
        );
        // s = string, s = string, i = integer
        mysqli_stmt_bind_param($stmt, 'ssi', $status, $catatan_admin, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php?pesan=edit_sukses');
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data: ' . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    // Sinkronkan ke $laporan agar form tetap menampilkan nilai POST
    $laporan['status']        = $status;
    $laporan['catatan_admin'] = $catatan_admin;
}

// ═══════════════════════════════════════════
// SEMUA LOGIKA PHP SELESAI DI SINI.
// HTML baru dimulai di bawah — TIDAK ada header() setelah ini.
// ═══════════════════════════════════════════
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
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --violet: #7c3aed;
            --pink: #c084fc;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text-primary: #f1f5f9;
            --text-muted: #94a3b8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Navbar ── */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-brand-custom {
            font-size: 1.35rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-light), var(--pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color: var(--text-primary); }
        .btn-back-nav {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }
        .btn-back-nav:hover { color: var(--text-primary); background: rgba(255,255,255,0.09); }

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(135deg, #1c1f3a, #252145, #1c1f3a);
            border-bottom: 1px solid var(--border);
            padding: 1.75rem 0;
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(129,140,248,0.1), transparent 55%),
                radial-gradient(circle at 80% 50%, rgba(124,58,237,0.08), transparent 55%);
        }
        .page-header .container { position: relative; z-index: 1; }

        .page-icon {
            width: 48px; height: 48px;
            background: rgba(129,140,248,0.15);
            border: 1px solid rgba(129,140,248,0.35);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0 0 0.3rem;
        }
        .page-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin: 0;
        }
        .id-badge {
            background: rgba(79,70,229,0.2);
            border: 1px solid rgba(79,70,229,0.4);
            color: var(--primary-light);
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* ── Form Card ── */
        .main-content { flex: 1; padding: 2rem 0; }
        .form-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            max-width: 760px;
            margin: 0 auto;
        }

        /* ── Form Elements ── */
        .form-section-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-label-custom {
            display: block;
            font-weight: 600;
            font-size: 0.83rem;
            color: var(--primary-light);
            margin-bottom: 0.4rem;
        }
        .form-group { margin-bottom: 1.1rem; }

        /* Read-only fields */
        .form-control-readonly {
            width: 100%;
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(51,65,85,0.7);
            border-radius: 10px;
            padding: 0.72rem 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            cursor: not-allowed;
            user-select: none;
            line-height: 1.6;
        }

        /* Editable select */
        .form-select-custom {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .form-select-custom:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
            background: rgba(79,70,229,0.05);
        }
        .form-select-custom option { background: var(--card-bg); color: var(--text-primary); }

        /* Editable textarea */
        .form-control-custom {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control-custom::placeholder { color: var(--text-muted); }
        .form-control-custom:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
            background: rgba(79,70,229,0.05);
        }

        /* Alert error */
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.5rem;
            color: #fca5a5;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }
        .alert-error i { flex-shrink: 0; margin-top: 0.1rem; }

        /* Read-only box wrapper */
        .readonly-box {
            background: rgba(245,158,11,0.05);
            border: 1px solid rgba(245,158,11,0.18);
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        .readonly-box-label {
            font-size: 0.72rem;
            color: #fcd34d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Divider */
        .section-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }

        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--violet));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(79,70,229,0.35);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,70,229,0.5); }
        .btn-back {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-back:hover { color: var(--text-primary); background: rgba(255,255,255,0.09); }

        /* Footer */
        footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            padding: 1.4rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.82rem;
        }
    </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav class="navbar-custom">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand-custom" href="dashboard.php">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
        <a href="dashboard.php" class="btn-back-nav">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</nav>

<!-- ── PAGE HEADER ── -->
<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-eye-fill" style="color:#818cf8;"></i>
            </div>
            <div>
                <h1>Tinjau Laporan <span class="id-badge">ID #<?= $id ?></span></h1>
                <p>Data laporan bersifat <strong style="color:#fcd34d;">read-only</strong>. Admin hanya dapat mengubah status penanganan.</p>
            </div>
        </div>
    </div>
</div>

<!-- ── MAIN CONTENT ── -->
<main class="main-content">
    <div class="container">
        <div class="form-card">

            <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Perbaiki kesalahan berikut:</strong>
                    <ul style="margin:0.4rem 0 0;padding-left:1.2rem;">
                        <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <!-- ══ DATA PELAPOR (READ-ONLY) ══ -->
                <div class="readonly-box">
                    <div class="readonly-box-label">
                        <i class="bi bi-lock-fill"></i> Data Asli Pelapor — Read Only
                    </div>

                    <div class="form-group">
                        <label class="form-label-custom">Nama Pelapor</label>
                        <div class="form-control-readonly"><?= htmlspecialchars($laporan['nama_pelapor'] ?? '—') ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-custom">Fasilitas yang Rusak</label>
                        <div class="form-control-readonly"><?= htmlspecialchars($laporan['fasilitas'] ?? '—') ?></div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label-custom">Deskripsi Kerusakan</label>
                        <div class="form-control-readonly" style="min-height:90px;"><?= htmlspecialchars($laporan['deskripsi'] ?? '—') ?></div>
                    </div>
                </div>

                <!-- Preview foto jika ada -->
                <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
                <div style="margin-bottom:1.5rem;">
                    <label class="form-label-custom"><i class="bi bi-image me-1"></i>Foto Bukti</label>
                    <div style="margin-top:0.5rem;">
                        <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                             alt="Foto Bukti"
                             style="max-width:220px;border-radius:10px;border:2px solid var(--border);">
                    </div>
                </div>
                <?php endif; ?>

                <hr class="section-divider">

                <!-- ══ STATUS + CATATAN ADMIN ══ -->
                <div class="form-section-label" style="color:var(--primary-light);">
                    <i class="bi bi-arrow-repeat"></i> Perbarui Status &amp; Catatan
                </div>

                <div class="form-group">
                    <label class="form-label-custom" for="status">Status Penanganan</label>
                    <select id="status" name="status" class="form-select-custom">
                        <option value="Menunggu" <?= ($laporan['status'] === 'Menunggu') ? 'selected' : '' ?>>⏳ Menunggu Ditindaklanjuti</option>
                        <option value="Diproses" <?= ($laporan['status'] === 'Diproses') ? 'selected' : '' ?>>🔄 Sedang Diproses</option>
                        <option value="Selesai"  <?= ($laporan['status'] === 'Selesai')  ? 'selected' : '' ?>>✅ Selesai Diperbaiki</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label-custom" for="catatan_admin">
                        <i class="bi bi-chat-left-text me-1"></i>Catatan Admin
                        <span style="font-size:0.72rem;font-weight:400;color:var(--text-muted);margin-left:0.4rem;">(opsional — akan ditampilkan ke pelapor)</span>
                    </label>
                    <textarea id="catatan_admin" name="catatan_admin"
                              class="form-control-custom"
                              rows="4"
                              maxlength="1000"
                              placeholder="Tulis catatan untuk pelapor, mis: 'Laporan sudah diteruskan ke bagian sarana'"
                              style="resize:vertical;"><?= htmlspecialchars($laporan['catatan_admin'] ?? '') ?></textarea>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.3rem;text-align:right;">
                        Maks. 1000 karakter
                    </div>
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
</main>

<!-- ── FOOTER ── -->
<footer>
    <div class="container">
        <p>
            <i class="bi bi-shield-check me-1" style="color:var(--primary-light);"></i>
            <strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
