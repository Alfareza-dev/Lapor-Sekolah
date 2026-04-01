<?php
// ============================================
// FILE: detail_laporan.php
// Deskripsi: Halaman user untuk melihat detail
// laporan miliknya + Catatan Admin.
// Jika status 'Menunggu' → tampilkan form edit.
// Jika status 'Diproses'/'Selesai' → read-only.
// ============================================

session_start();
require_once 'koneksi.php';
require_once 'auth_check.php'; // Guard: cek login + ghost session

// ── GUARD: Hanya user biasa yang boleh akses ──
// Admin punya edit.php, jadi redirect agar tidak rancu
if ($_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

// ── Validasi parameter ID ──
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id         = (int) $_GET['id'];
$session_uid = (int) $_SESSION['user_id'];

// ── Ambil laporan HANYA jika milik user yang login ──
// Ini mencegah user A mengakses laporan user B dengan menebak ID
$sql = "SELECT * FROM laporan_kerusakan WHERE id = ? AND user_id = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $id, $session_uid);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if (mysqli_num_rows($result) === 0) {
    // Laporan tidak ada ATAU bukan milik user ini → tolak akses
    header('Location: dashboard.php');
    exit;
}

$laporan     = mysqli_fetch_assoc($result);
$is_editable = ($laporan['status'] === 'Menunggu'); // Hanya bisa diedit jika masih Menunggu
$errors      = [];
$success_msg = '';

// ── Proses UPDATE jika user submit form edit ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_editable) {

    // 1. Ambil & sanitasi input teks
    $fasilitas = trim($_POST['fasilitas'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($fasilitas)) $errors[] = 'Nama fasilitas wajib diisi.';
    if (empty($deskripsi)) $errors[] = 'Deskripsi kerusakan wajib diisi.';

    // 2. Proses upload foto baru (jika ada)
    $foto_lama    = $laporan['foto_bukti'];
    $nama_file_db = $foto_lama; // default: pakai foto lama

    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file      = $_FILES['foto_bukti'];
        $file_tmp  = $file['tmp_name'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_error= $file['error'];

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $allowed_exts  = ['jpg', 'jpeg', 'png'];
        $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi MIME type dengan finfo (anti spoofing)
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = 'Kesalahan upload file. Kode error: ' . $file_error;
        } elseif (!in_array($file_ext, $allowed_exts)) {
            $errors[] = 'Ekstensi tidak diizinkan. Hanya .jpg, .jpeg, .png.';
        } elseif (!in_array($mime_type, $allowed_types)) {
            $errors[] = 'Tipe file tidak valid. File harus berupa gambar asli.';
        } elseif ($file_size > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran file melebihi 5 MB.';
        } else {
            $nama_file_baru = 'foto_' . time() . '_' . uniqid() . '.' . $file_ext;
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);

            if (move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                // Hapus foto lama dari server agar tidak menumpuk
                if (!empty($foto_lama) && file_exists('uploads/' . $foto_lama)) {
                    unlink('uploads/' . $foto_lama);
                }
                $nama_file_db = $nama_file_baru;
            } else {
                $errors[] = 'Gagal menyimpan foto. Periksa izin folder uploads/.';
            }
        }
    }

    // 3. Simpan ke database jika tidak ada error
    if (empty($errors)) {
        $stmt_upd = mysqli_prepare(
            $koneksi,
            "UPDATE laporan_kerusakan SET fasilitas = ?, deskripsi = ?, foto_bukti = ? WHERE id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmt_upd, 'sssii', $fasilitas, $deskripsi, $nama_file_db, $id, $session_uid);

        if (mysqli_stmt_execute($stmt_upd)) {
            mysqli_stmt_close($stmt_upd);

            // Refresh data laporan setelah update
            $stmt_ref = mysqli_prepare($koneksi, "SELECT * FROM laporan_kerusakan WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt_ref, 'i', $id);
            mysqli_stmt_execute($stmt_ref);
            $res_ref = mysqli_stmt_get_result($stmt_ref);
            $laporan = mysqli_fetch_assoc($res_ref);
            mysqli_stmt_close($stmt_ref);

            $success_msg = 'Laporan berhasil diperbarui!';
        } else {
            $errors[] = 'Gagal menyimpan perubahan: ' . mysqli_stmt_error($stmt_upd);
            mysqli_stmt_close($stmt_upd);
        }
    }
}

// Helper: badge status
function get_status_badge(string $status): string {
    return match($status) {
        'Diproses' => '<span style="background:rgba(59,130,246,0.15);color:#93c5fd;border:1px solid rgba(59,130,246,0.3);padding:0.3rem 0.8rem;border-radius:50px;font-size:0.78rem;font-weight:700;">🔄 Sedang Diproses</span>',
        'Selesai'  => '<span style="background:rgba(16,185,129,0.15);color:#6ee7b7;border:1px solid rgba(16,185,129,0.3);padding:0.3rem 0.8rem;border-radius:50px;font-size:0.78rem;font-weight:700;">✅ Selesai</span>',
        default    => '<span style="background:rgba(245,158,11,0.15);color:#fcd34d;border:1px solid rgba(245,158,11,0.3);padding:0.3rem 0.8rem;border-radius:50px;font-size:0.78rem;font-weight:700;">⏳ Menunggu</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan #<?= $id ?> | Lapor-Sekolah</title>
    <meta name="description" content="Detail laporan kerusakan fasilitas sekolah yang kamu kirimkan.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5; --primary-light: #818cf8; --violet: #7c3aed;
            --pink: #c084fc; --green: #10b981;
            --dark-bg: #0f172a; --card-bg: #1e293b; --border: #334155;
            --text-primary: #f1f5f9; --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg); color: var(--text-primary);
            min-height: 100vh; display: flex; flex-direction: column;
        }

        /* ── Navbar ── */
        .navbar-custom {
            background: rgba(15,23,42,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border); padding: 0.9rem 0;
            position: sticky; top: 0; z-index: 100;
        }
        .navbar-brand-custom {
            font-size: 1.35rem; font-weight: 900;
            background: linear-gradient(135deg, var(--primary-light), var(--pink));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; text-decoration: none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color: var(--text-primary); }
        .btn-back-nav {
            background: rgba(255,255,255,0.05); color: var(--text-muted);
            border: 1px solid var(--border); border-radius: 8px;
            padding: 0.35rem 0.85rem; font-size: 0.8rem; font-weight: 600;
            text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;
            transition: all 0.2s;
        }
        .btn-back-nav:hover { color: var(--text-primary); background: rgba(255,255,255,0.09); }

        /* ── Page Header ── */
        .page-header {
            background: linear-gradient(135deg, #0d2137, #1a1f3a, #0d2137);
            border-bottom: 1px solid var(--border); padding: 1.75rem 0;
            position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(circle at 15% 50%, rgba(16,185,129,0.1), transparent 50%),
                radial-gradient(circle at 85% 50%, rgba(79,70,229,0.08), transparent 50%);
        }
        .page-header .container { position: relative; z-index: 1; }
        .page-icon {
            width: 48px; height: 48px;
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .page-header h1 { font-size: 1.55rem; font-weight: 800; margin: 0 0 0.3rem; }
        .page-header p  { color: var(--text-muted); font-size: 0.875rem; margin: 0; }
        .id-badge {
            background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
            color: #6ee7b7; padding: 0.25rem 0.75rem; border-radius: 8px;
            font-size: 0.8rem; font-weight: 700;
        }

        /* ── Layout ── */
        .main-content { flex: 1; padding: 2rem 0; }
        .detail-card {
            background: var(--card-bg); border: 1px solid var(--border);
            border-radius: 16px; padding: 0; overflow: hidden;
            max-width: 780px; margin: 0 auto;
        }
        .card-section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
        }
        .card-section:last-child { border-bottom: none; }
        .section-title {
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; margin-bottom: 1.1rem;
            display: flex; align-items: center; gap: 0.4rem;
        }

        /* ── Field Display ── */
        .field-label {
            font-size: 0.78rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;
        }
        .field-value {
            font-size: 0.925rem; color: var(--text-primary);
            line-height: 1.65; word-break: break-word;
        }
        .field-group { margin-bottom: 1.1rem; }
        .field-group:last-child { margin-bottom: 0; }

        /* ── Read-only form fields (for edit form) ── */
        .form-label-custom {
            display: block; font-weight: 600; font-size: 0.83rem;
            color: var(--primary-light); margin-bottom: 0.4rem;
        }
        .form-group-edit { margin-bottom: 1.1rem; }
        .form-control-custom {
            width: 100%; background: rgba(255,255,255,0.05);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 0.75rem 1rem; color: var(--text-primary);
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control-custom::placeholder { color: var(--text-muted); }
        .form-control-custom:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
            background: rgba(79,70,229,0.05);
        }

        /* ── Upload area ── */
        .upload-area {
            border: 2px dashed var(--border); border-radius: 12px;
            padding: 1.5rem; text-align: center; cursor: pointer;
            transition: all 0.2s; background: rgba(255,255,255,0.02);
        }
        .upload-area:hover { border-color: var(--primary-light); background: rgba(79,70,229,0.05); }
        .upload-area i { font-size: 1.8rem; color: var(--text-muted); margin-bottom: 0.4rem; display: block; }
        #foto_bukti { display: none; }
        #preview-container { margin-top: 0.75rem; display: none; }
        #preview-img { max-width: 200px; border-radius: 10px; border: 2px solid var(--border); }

        /* ── Catatan Admin box ── */
        .catatan-box {
            background: rgba(79,70,229,0.07); border: 1px solid rgba(79,70,229,0.25);
            border-radius: 12px; padding: 1.1rem 1.25rem;
        }
        .catatan-box-empty {
            background: rgba(100,116,139,0.07); border: 1px solid rgba(100,116,139,0.2);
            border-radius: 12px; padding: 1rem 1.25rem;
            color: var(--text-muted); font-size: 0.875rem; font-style: italic;
        }
        .catatan-admin-label {
            font-size: 0.72rem; font-weight: 700; color: var(--primary-light);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem;
        }
        .catatan-text {
            font-size: 0.9rem; line-height: 1.7; color: var(--text-primary);
            white-space: pre-line;
        }

        /* ── Status badge inline ── */
        .status-row {
            display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
        }

        /* ── Alerts ── */
        .alert-error {
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px; padding: 0.9rem 1.1rem; margin-bottom: 1.25rem;
            color: #fca5a5; font-size: 0.875rem;
            display: flex; align-items: flex-start; gap: 0.6rem;
        }
        .alert-success {
            background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 10px; padding: 0.9rem 1.1rem; margin-bottom: 1.25rem;
            color: #6ee7b7; font-size: 0.875rem;
            display: flex; align-items: center; gap: 0.6rem;
        }

        /* ── Lock banner (status non-Menunggu) ── */
        .lock-banner {
            background: rgba(100,116,139,0.08); border: 1px solid rgba(100,116,139,0.2);
            border-radius: 10px; padding:0.85rem 1.1rem;
            color: var(--text-muted); font-size: 0.875rem;
            display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.25rem;
        }

        /* ── Buttons ── */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--violet));
            color: white; border: none; border-radius: 10px;
            padding: 0.8rem 2rem; font-weight: 700; font-size: 0.95rem;
            cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
            transition: all 0.2s; box-shadow: 0 4px 15px rgba(79,70,229,0.35);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,70,229,0.5); }
        .btn-back {
            background: rgba(255,255,255,0.05); color: var(--text-muted);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 0.8rem 2rem; font-weight: 600; font-size: 0.95rem;
            text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-back:hover { color: var(--text-primary); background: rgba(255,255,255,0.09); }

        footer {
            background: var(--card-bg); border-top: 1px solid var(--border);
            padding: 1.4rem 0; text-align: center; color: var(--text-muted); font-size: 0.82rem;
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
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:0.8rem;color:var(--text-muted);">
                <i class="bi bi-person-fill me-1" style="color:#818cf8;"></i>
                <?= htmlspecialchars($_SESSION['nama']) ?>
            </span>
            <a href="dashboard.php" class="btn-back-nav">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>
</nav>

<!-- ── PAGE HEADER ── -->
<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-file-earmark-text-fill" style="color:#6ee7b7;"></i>
            </div>
            <div>
                <h1>Detail Laporan <span class="id-badge">ID #<?= $id ?></span></h1>
                <p>
                    Dikirim pada <?= date('d M Y, H:i', strtotime($laporan['tanggal_lapor'])) ?> WIB
                    &bull; Status: <?= get_status_badge($laporan['status']) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ── MAIN CONTENT ── -->
<main class="main-content">
    <div class="container">
        <div class="detail-card">

            <!-- ═══ SECTION 1: INFO LAPORAN (selalu read-only) ═══ -->
            <div class="card-section">
                <div class="section-title" style="color:#6ee7b7;">
                    <i class="bi bi-info-circle-fill"></i> Informasi Laporan
                </div>

                <div class="field-group">
                    <div class="field-label">Nama Pelapor</div>
                    <div class="field-value"><?= htmlspecialchars($laporan['nama_pelapor']) ?></div>
                </div>
                <div class="field-group">
                    <div class="field-label">Fasilitas yang Rusak</div>
                    <div class="field-value"><?= htmlspecialchars($laporan['fasilitas']) ?></div>
                </div>
                <div class="field-group">
                    <div class="field-label">Deskripsi Kerusakan</div>
                    <div class="field-value" style="white-space:pre-line;"><?= htmlspecialchars($laporan['deskripsi']) ?></div>
                </div>

                <!-- Foto Bukti -->
                <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
                <div class="field-group">
                    <div class="field-label">Foto Bukti</div>
                    <div style="margin-top:0.5rem;">
                        <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                             alt="Foto Bukti"
                             style="max-width:280px;border-radius:12px;border:2px solid var(--border);cursor:pointer;"
                             onclick="showFoto(this.src)">
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.4rem;">
                            <i class="bi bi-zoom-in me-1"></i>Klik foto untuk memperbesar
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="field-group">
                    <div class="field-label">Foto Bukti</div>
                    <div style="color:var(--text-muted);font-size:0.875rem;font-style:italic;">
                        <i class="bi bi-image me-1"></i>Tidak ada foto
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ═══ SECTION 2: CATATAN ADMIN ═══ -->
            <div class="card-section">
                <div class="section-title" style="color:var(--primary-light);">
                    <i class="bi bi-chat-left-text-fill"></i> Catatan dari Admin
                </div>

                <?php if (!empty($laporan['catatan_admin'])): ?>
                <div class="catatan-box">
                    <div class="catatan-admin-label">
                        <i class="bi bi-person-fill-gear"></i> Pesan Admin
                    </div>
                    <div class="catatan-text"><?= htmlspecialchars($laporan['catatan_admin']) ?></div>
                </div>
                <?php else: ?>
                <div class="catatan-box-empty">
                    <i class="bi bi-hourglass-split me-2"></i>
                    Belum ada catatan dari Admin untuk laporan ini.
                </div>
                <?php endif; ?>
            </div>

            <!-- ═══ SECTION 3: FORM EDIT (hanya jika status 'Menunggu') ═══ -->
            <div class="card-section">
                <div class="section-title" style="color:<?= $is_editable ? '#fcd34d' : 'var(--text-muted)' ?>;">
                    <i class="bi bi-<?= $is_editable ? 'pencil-square' : 'lock-fill' ?>"></i>
                    <?= $is_editable ? 'Edit Laporan' : 'Laporan Terkunci' ?>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0;"></i>
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

                <?php if (!empty($success_msg)): ?>
                <div class="alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= htmlspecialchars($success_msg) ?></span>
                </div>
                <?php endif; ?>

                <?php if ($is_editable): ?>
                <!-- ── FORM EDIT (status masih Menunggu) ── -->
                <div style="background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.15);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1.25rem;font-size:0.83rem;color:#fcd34d;">
                    <i class="bi bi-info-circle me-1"></i>
                    Laporan masih bisa diedit karena statusnya <strong>Menunggu</strong>.
                    Setelah Admin memproses laporan, form ini akan terkunci.
                </div>

                <form method="POST" enctype="multipart/form-data" novalidate>

                    <div class="form-group-edit">
                        <label class="form-label-custom" for="fasilitas">
                            Fasilitas yang Rusak <span style="color:#fca5a5;font-size:0.7rem;">*Wajib</span>
                        </label>
                        <input type="text" id="fasilitas" name="fasilitas"
                               class="form-control-custom"
                               value="<?= htmlspecialchars($laporan['fasilitas']) ?>"
                               maxlength="150"
                               placeholder="Nama fasilitas yang rusak">
                    </div>

                    <div class="form-group-edit">
                        <label class="form-label-custom" for="deskripsi">
                            Deskripsi Kerusakan <span style="color:#fca5a5;font-size:0.7rem;">*Wajib</span>
                        </label>
                        <textarea id="deskripsi" name="deskripsi"
                                  class="form-control-custom"
                                  rows="5" maxlength="1000"
                                  placeholder="Jelaskan kondisi kerusakan secara detail..."
                                  style="resize:vertical;"><?= htmlspecialchars($laporan['deskripsi']) ?></textarea>
                    </div>

                    <div class="form-group-edit">
                        <label class="form-label-custom">
                            <i class="bi bi-image me-1"></i>Ganti Foto Bukti
                            <span style="font-size:0.72rem;font-weight:400;color:var(--text-muted);margin-left:0.3rem;">(opsional)</span>
                        </label>

                        <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
                        <div style="margin-bottom:0.75rem;padding:0.75rem;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px;">
                            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:0.5rem;">
                                <i class="bi bi-image me-1"></i>Foto saat ini:
                            </div>
                            <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                                 alt="Foto saat ini"
                                 style="max-width:160px;border-radius:8px;border:2px solid var(--border);">
                            <div style="font-size:0.75rem;color:#fca5a5;margin-top:0.5rem;">
                                <i class="bi bi-exclamation-circle me-1"></i>Upload foto baru akan menggantikan foto ini secara permanen.
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="upload-area" onclick="document.getElementById('foto_bukti').click()">
                            <i class="bi bi-cloud-upload"></i>
                            <p><strong style="color:var(--primary-light);">Klik untuk memilih foto</strong></p>
                            <p style="font-size:0.75rem;opacity:0.6;margin-top:0.25rem;">JPG, JPEG, PNG &bull; Maks. 5 MB</p>
                        </div>
                        <input type="file" id="foto_bukti" name="foto_bukti" accept=".jpg,.jpeg,.png">

                        <div id="preview-container">
                            <img id="preview-img" src="" alt="Preview">
                            <p id="preview-name" style="color:var(--text-muted);font-size:0.8rem;margin-top:0.4rem;"></p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-3">
                        <a href="dashboard.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-save-fill"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>

                <?php else: ?>
                <!-- ── READ-ONLY (status Diproses / Selesai) ── -->
                <div class="lock-banner">
                    <i class="bi bi-lock-fill" style="color:#64748b;font-size:1.1rem;flex-shrink:0;"></i>
                    <div>
                        Form edit tidak tersedia karena laporan ini sudah berstatus
                        <strong style="color:var(--text-primary);"><?= htmlspecialchars($laporan['status']) ?></strong>.
                        Hubungi Admin jika ada pertanyaan.
                    </div>
                </div>
                <div>
                    <a href="dashboard.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /.detail-card -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Preview foto sebelum upload ──
const fotoInput = document.getElementById('foto_bukti');
if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader  = new FileReader();
        const preview = document.getElementById('preview-container');
        const img     = document.getElementById('preview-img');
        const nameEl  = document.getElementById('preview-name');
        reader.onload = (e) => {
            img.src = e.target.result;
            nameEl.textContent = `📎 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}

// ── Drag & Drop ──
const uploadArea = document.querySelector('.upload-area');
if (uploadArea) {
    uploadArea.addEventListener('dragover',  (e) => { e.preventDefault(); uploadArea.style.borderColor = '#818cf8'; });
    uploadArea.addEventListener('dragleave', ()  => { uploadArea.style.borderColor = '#334155'; });
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.borderColor = '#334155';
        const file = e.dataTransfer.files[0];
        if (file && fotoInput) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fotoInput.files = dt.files;
            fotoInput.dispatchEvent(new Event('change'));
        }
    });
}

// ── Lightbox foto ──
function showFoto(src) {
    Swal.fire({
        imageUrl: src,
        imageAlt: 'Foto Bukti Laporan',
        imageWidth: '100%',
        showConfirmButton: false,
        showCloseButton: true,
        background: '#1e293b',
        customClass: { popup: 'swal-foto-popup' },
        width: 'auto',
        padding: '1rem',
    });
}

<?php if (!empty($success_msg)): ?>
// Auto-fade pesan sukses setelah 4 detik
setTimeout(() => {
    const el = document.querySelector('.alert-success');
    if (el) el.style.transition = 'opacity 0.5s', el.style.opacity = '0';
}, 4000);
<?php endif; ?>
</script>

<style>
.swal-foto-popup { border: 1px solid #334155 !important; border-radius: 16px !important; max-width: 90vw !important; }
</style>

</body>
</html>
