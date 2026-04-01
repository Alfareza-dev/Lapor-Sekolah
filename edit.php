<?php
// ============================================
// FILE: edit.php
// Deskripsi: Form edit laporan + proses UPDATE
// data di database dan ganti foto jika ada
// ============================================

require_once 'koneksi.php';

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
    header('Location: index.php');
    exit;
}

$laporan = mysqli_fetch_assoc($result);
$errors  = [];

// ── Proses form UPDATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Sanitasi input teks
    $nama_pelapor = trim(mysqli_real_escape_string($koneksi, $_POST['nama_pelapor'] ?? ''));
    $fasilitas    = trim(mysqli_real_escape_string($koneksi, $_POST['fasilitas']    ?? ''));
    $deskripsi    = trim(mysqli_real_escape_string($koneksi, $_POST['deskripsi']    ?? ''));
    $status       = trim(mysqli_real_escape_string($koneksi, $_POST['status']       ?? ''));

    // 2. Validasi
    if (empty($nama_pelapor)) $errors[] = 'Nama pelapor wajib diisi.';
    if (empty($fasilitas))    $errors[] = 'Nama fasilitas wajib diisi.';
    if (empty($deskripsi))    $errors[] = 'Deskripsi kerusakan wajib diisi.';
    if (!in_array($status, ['Menunggu', 'Diproses', 'Selesai'])) {
        $errors[] = 'Status tidak valid.';
    }

    // 3. Proses foto baru (jika ada)
    $foto_lama    = $laporan['foto_bukti']; // simpan nama foto lama
    $nama_file_db = $foto_lama;             // default: pakai foto lama

    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file      = $_FILES['foto_bukti'];
        $file_tmp  = $file['tmp_name'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_error= $file['error'];

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $allowed_exts  = ['jpg', 'jpeg', 'png'];
        $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $finfo         = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type     = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat mengunggah file baru. Kode error: ' . $file_error;
        } elseif (!in_array($file_ext, $allowed_exts)) {
            $errors[] = 'Ekstensi file tidak diizinkan. Hanya .jpg, .jpeg, dan .png.';
        } elseif (!in_array($mime_type, $allowed_types)) {
            $errors[] = 'Tipe file tidak valid. File harus berupa gambar JPG atau PNG asli.';
        } elseif ($file_size > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 5 MB.';
        } else {
            $nama_file_baru = 'foto_' . time() . '_' . uniqid() . '.' . $file_ext;

            if (!is_dir('uploads')) mkdir('uploads', 0755, true);

            if (move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                // Hapus foto lama dari server jika ada
                if (!empty($foto_lama) && file_exists('uploads/' . $foto_lama)) {
                    unlink('uploads/' . $foto_lama);
                }
                $nama_file_db = $nama_file_baru;
            } else {
                $errors[] = 'Gagal menyimpan foto baru. Pastikan folder uploads/ memiliki izin tulis.';
            }
        }
    }

    // 4. Hapus foto (jika user centang checkbox hapus_foto)
    if (isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1') {
        if (!empty($foto_lama) && file_exists('uploads/' . $foto_lama)) {
            unlink('uploads/' . $foto_lama);
        }
        $nama_file_db = ''; // Kosongkan di database
    }

    // 5. Jalankan UPDATE jika tidak ada error
    if (empty($errors)) {
        $sql_update = "UPDATE laporan_kerusakan
                       SET nama_pelapor = '$nama_pelapor',
                           fasilitas    = '$fasilitas',
                           deskripsi    = '$deskripsi',
                           foto_bukti   = '$nama_file_db',
                           status       = '$status'
                       WHERE id = $id";

        if (mysqli_query($koneksi, $sql_update)) {
            header('Location: index.php?pesan=edit_sukses');
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
        }
    }

    // Refresh data laporan agar form menampilkan nilai terbaru dari POST
    $laporan['nama_pelapor'] = $_POST['nama_pelapor'] ?? $laporan['nama_pelapor'];
    $laporan['fasilitas']    = $_POST['fasilitas']    ?? $laporan['fasilitas'];
    $laporan['deskripsi']    = $_POST['deskripsi']    ?? $laporan['deskripsi'];
    $laporan['status']       = $_POST['status']       ?? $laporan['status'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan #<?= $id ?> | Lapor-Sekolah</title>
    <meta name="description" content="Form untuk mengedit laporan kerusakan fasilitas yang sudah ada.">

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

        footer { background:var(--card-bg); border-top:1px solid var(--border); padding:1.5rem 0; text-align:center; color:var(--text-muted); font-size:0.85rem; margin-top:3rem; }
    </style>
</head>
<body>

<nav class="navbar-custom sticky-top">
    <div class="container d-flex align-items-center">
        <a class="navbar-brand-custom" href="index.php">
            <i class="bi bi-shield-exclamation me-2" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:rgba(59,130,246,0.2);border:1px solid rgba(59,130,246,0.4);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-pencil-fill" style="color:#93c5fd;font-size:1.3rem;"></i>
            </div>
            <div>
                <h1>Edit Laporan <span class="id-badge">ID #<?= $id ?></span></h1>
                <p>Perbarui informasi laporan kerusakan fasilitas.</p>
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

        <form method="POST" enctype="multipart/form-data" novalidate>

            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-person me-1"></i> Informasi Pelapor
            </h6>

            <div class="form-group">
                <label class="form-label-custom" for="nama_pelapor">
                    Nama Lengkap <span class="badge-required">Wajib</span>
                </label>
                <input type="text" id="nama_pelapor" name="nama_pelapor"
                       class="form-control-custom"
                       value="<?= htmlspecialchars($laporan['nama_pelapor']) ?>"
                       maxlength="100">
            </div>

            <hr class="section-divider">

            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-wrench me-1"></i> Detail Kerusakan
            </h6>

            <div class="form-group">
                <label class="form-label-custom" for="fasilitas">
                    Fasilitas yang Rusak <span class="badge-required">Wajib</span>
                </label>
                <input type="text" id="fasilitas" name="fasilitas"
                       class="form-control-custom"
                       value="<?= htmlspecialchars($laporan['fasilitas']) ?>"
                       maxlength="150">
            </div>

            <div class="form-group">
                <label class="form-label-custom" for="deskripsi">
                    Deskripsi Kerusakan <span class="badge-required">Wajib</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" class="form-control-custom"
                          rows="4" maxlength="1000"><?= htmlspecialchars($laporan['deskripsi']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label-custom" for="status">Status Laporan</label>
                <select id="status" name="status" class="form-select-custom">
                    <option value="Menunggu" <?= $laporan['status'] === 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu Ditindaklanjuti</option>
                    <option value="Diproses" <?= $laporan['status'] === 'Diproses' ? 'selected' : '' ?>>🔄 Sedang Diproses</option>
                    <option value="Selesai"  <?= $laporan['status'] === 'Selesai'  ? 'selected' : '' ?>>✅ Selesai Diperbaiki</option>
                </select>
            </div>

            <hr class="section-divider">

            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-image me-1"></i> Foto Bukti
            </h6>

            <!-- Tampilkan foto yang sudah ada -->
            <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
            <div class="foto-current-box">
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.7rem;">
                    <i class="bi bi-check-circle-fill me-1" style="color:#6ee7b7;"></i> Foto saat ini:
                </p>
                <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                     alt="Foto bukti saat ini" class="foto-current-img">
                <label class="checkbox-hapus">
                    <input type="checkbox" name="hapus_foto" value="1" id="hapus_foto">
                    <span><i class="bi bi-trash me-1"></i>Hapus foto ini (foto tidak bisa dikembalikan)</span>
                </label>
            </div>
            <p style="color:var(--text-muted);font-size:0.8rem;margin-bottom:0.75rem;">
                Atau ganti dengan foto baru:
            </p>
            <?php else: ?>
            <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:0.75rem;">
                <i class="bi bi-info-circle me-1"></i> Laporan ini belum memiliki foto. Upload foto baru:
            </p>
            <?php endif; ?>

            <div class="form-group">
                <div class="upload-area" onclick="document.getElementById('foto_bukti').click()">
                    <i class="bi bi-cloud-upload"></i>
                    <p><strong style="color:var(--primary-light);">Klik untuk memilih foto baru</strong></p>
                    <p style="font-size:0.75rem;opacity:0.6;margin-top:0.3rem;">Format: JPG, JPEG, PNG &bull; Maks. 5 MB</p>
                </div>
                <input type="file" id="foto_bukti" name="foto_bukti" accept=".jpg,.jpeg,.png">

                <div id="preview-container">
                    <img id="preview-img" src="" alt="Preview foto baru">
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;" id="preview-name"></p>
                </div>
            </div>

            <hr class="section-divider">

            <div class="d-flex gap-3">
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-save-fill"></i> Simpan Perubahan
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
<script>
document.getElementById('foto_bukti').addEventListener('change', function () {
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
</script>

</body>
</html>
