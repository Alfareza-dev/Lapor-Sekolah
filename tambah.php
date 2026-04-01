<?php
// ============================================
// FILE: tambah.php
// Deskripsi: Form input laporan baru + proses
// INSERT data ke database + upload foto
// ============================================

session_start();
require_once 'koneksi.php';
require_once 'auth_check.php'; // ← Guard ghost session terpusat

$session_user_id = (int) $_SESSION['user_id'];
$session_nama    = $_SESSION['nama']; // Auto-fill nama pelapor

$errors  = [];
$success = false;

// Proses form ketika tombol Submit ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. Ambil & sanitasi input teks ──
    $nama_pelapor = trim(mysqli_real_escape_string($koneksi, $_POST['nama_pelapor'] ?? ''));
    $fasilitas    = trim(mysqli_real_escape_string($koneksi, $_POST['fasilitas']    ?? ''));
    $deskripsi    = trim(mysqli_real_escape_string($koneksi, $_POST['deskripsi']    ?? ''));
    // Status SELALU 'Menunggu' saat pertama dibuat — tidak bisa dipilih user
    $status = 'Menunggu';

    // ── 2. Validasi input wajib ──
    if (empty($nama_pelapor)) $errors[] = 'Nama pelapor wajib diisi.';
    if (empty($fasilitas))    $errors[] = 'Nama fasilitas wajib diisi.';
    if (empty($deskripsi))    $errors[] = 'Deskripsi kerusakan wajib diisi.';

    // ── 3. Proses Upload Foto ──
    $nama_file_db = ''; // Default: tidak ada foto

    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file      = $_FILES['foto_bukti'];
        $file_tmp  = $file['tmp_name'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_error= $file['error'];

        // Validasi: cek apakah file benar-benar gambar (bukan hanya extension)
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $allowed_exts  = ['jpg', 'jpeg', 'png'];

        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat mengunggah file. Kode error: ' . $file_error;
        } elseif (!in_array($file_ext, $allowed_exts)) {
            $errors[] = 'Ekstensi file tidak diizinkan. Hanya .jpg, .jpeg, dan .png yang diperbolehkan.';
        } elseif (!in_array($mime_type, $allowed_types)) {
            $errors[] = 'Tipe file tidak valid. File harus berupa gambar JPG atau PNG asli.';
        } elseif ($file_size > 5 * 1024 * 1024) { // Maks 5 MB
            $errors[] = 'Ukuran file terlalu besar. Maksimal 5 MB.';
        } else {
            // Buat nama file unik agar tidak tertimpa
            $nama_file_baru = 'foto_' . time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path    = 'uploads/' . $nama_file_baru;

            // Buat folder uploads jika belum ada
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            if (move_uploaded_file($file_tmp, $upload_path)) {
                $nama_file_db = $nama_file_baru;
            } else {
                $errors[] = 'Gagal menyimpan file. Pastikan folder uploads/ memiliki izin tulis.';
            }
        }
    }

    // ── 4. Simpan ke database jika tidak ada error ──
    if (empty($errors)) {
        $sql = "INSERT INTO laporan_kerusakan (user_id, nama_pelapor, fasilitas, deskripsi, foto_bukti, status)
                VALUES ($session_user_id, '$nama_pelapor', '$fasilitas', '$deskripsi', '$nama_file_db', '$status')";

        if (mysqli_query($koneksi, $sql)) {
            // Redirect ke dashboard dengan pesan sukses
            header('Location: dashboard.php?pesan=tambah_sukses');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan laporan ke database: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Laporan | Lapor-Sekolah</title>
    <meta name="description" content="Form untuk menambahkan laporan kerusakan fasilitas sekolah baru.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5; --primary-dark: #3730a3; --primary-light: #818cf8;
            --dark-bg: #0f172a; --card-bg: #1e293b; --border: #334155;
            --text-primary: #f1f5f9; --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--dark-bg); color: var(--text-primary); min-height: 100vh; }

        .navbar-custom { background: rgba(15,23,42,0.95); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); padding: 1rem 0; }
        .navbar-brand-custom { font-size:1.4rem; font-weight:800; background: linear-gradient(135deg,var(--primary-light),#c084fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; text-decoration:none; }
        .navbar-brand-custom span { -webkit-text-fill-color: var(--text-primary); }

        .page-header { background: linear-gradient(135deg,#1e1b4b,#312e81); border-bottom: 1px solid var(--border); padding: 2rem 0; }
        .page-header h1 { font-size:1.8rem; font-weight:800; margin:0; }
        .page-header p { color: var(--text-muted); margin: 0.5rem 0 0; }

        .form-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; margin: 2rem auto; max-width: 750px; }

        .form-label-custom { font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: var(--primary-light); display: block; }
        .badge-required { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.3rem; vertical-align: middle; }

        .form-control-custom, .form-select-custom {
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 10px; padding: 0.75rem 1rem; color: var(--text-primary);
            font-size: 0.9rem; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        .form-control-custom::placeholder { color: var(--text-muted); }
        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
            background: rgba(79,70,229,0.05);
        }
        .form-select-custom option { background: var(--card-bg); color: var(--text-primary); }

        .upload-area {
            border: 2px dashed var(--border); border-radius: 12px; padding: 2rem; text-align: center;
            cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.02);
        }
        .upload-area:hover { border-color: var(--primary-light); background: rgba(79,70,229,0.05); }
        .upload-area i { font-size: 2.5rem; color: var(--text-muted); margin-bottom: 0.75rem; display: block; }
        .upload-area p { color: var(--text-muted); font-size: 0.85rem; margin: 0; }
        .upload-area .upload-hint { font-size: 0.75rem; opacity: 0.6; margin-top: 0.3rem; }
        #foto_bukti { display: none; }

        #preview-container { margin-top: 1rem; display: none; }
        #preview-img { max-width: 200px; border-radius: 10px; border: 2px solid var(--border); }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white; border: none; border-radius: 10px;
            padding: 0.8rem 2rem; font-weight: 700; font-size: 0.95rem;
            cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;
            transition: all 0.2s; box-shadow: 0 4px 15px rgba(79,70,229,0.35);
            width: 100%;
            justify-content: center;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,70,229,0.5); }

        .btn-back {
            background: rgba(255,255,255,0.05); color: var(--text-muted);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 0.8rem 2rem; font-weight: 600; font-size: 0.95rem;
            text-decoration: none; display: inline-flex; align-items: center;
            gap: 0.5rem; transition: all 0.2s;
        }
        .btn-back:hover { color: var(--text-primary); background: rgba(255,255,255,0.08); }

        .alert-error {
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
            color: #fca5a5;
        }
        .alert-error ul { margin: 0.5rem 0 0; padding-left: 1.2rem; }
        .alert-error li { font-size: 0.875rem; margin-bottom: 0.25rem; }

        .section-divider { border: none; border-top: 1px solid var(--border); margin: 1.75rem 0; }

        .form-group { margin-bottom: 1.25rem; }

        footer { background: var(--card-bg); border-top: 1px solid var(--border); padding: 1.5rem 0; text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 3rem; }
    </style>
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
            <div style="width:48px;height:48px;background:rgba(79,70,229,0.2);border:1px solid rgba(79,70,229,0.4);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-plus-lg" style="color:var(--primary-light);font-size:1.4rem;"></i>
            </div>
            <div>
                <h1>Buat Laporan Baru</h1>
                <p>Isi formulir di bawah untuk melaporkan kerusakan fasilitas sekolah.</p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="form-card">

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan! Harap perbaiki sebelum melanjutkan:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>

            <!-- Informasi Pelapor -->
            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-person me-1"></i> Informasi Pelapor
            </h6>

            <div class="form-group">
                <label class="form-label-custom" for="nama_pelapor">
                    Nama Lengkap <span class="badge-required">Wajib</span>
                </label>
                <input type="text" id="nama_pelapor" name="nama_pelapor"
                       class="form-control-custom"
                       placeholder="Contoh: Budi Santoso"
                       value="<?= htmlspecialchars($_POST['nama_pelapor'] ?? $session_nama) ?>"
                       maxlength="100">
            </div>

            <hr class="section-divider">

            <!-- Detail Kerusakan -->
            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-wrench me-1"></i> Detail Kerusakan
            </h6>

            <div class="form-group">
                <label class="form-label-custom" for="fasilitas">
                    Fasilitas yang Rusak <span class="badge-required">Wajib</span>
                </label>
                <input type="text" id="fasilitas" name="fasilitas"
                       class="form-control-custom"
                       placeholder="Contoh: Toilet Lantai 2, Lab Komputer A, Lapangan Basket"
                       value="<?= htmlspecialchars($_POST['fasilitas'] ?? '') ?>"
                       maxlength="150">
            </div>

            <div class="form-group">
                <label class="form-label-custom" for="deskripsi">
                    Deskripsi Kerusakan <span class="badge-required">Wajib</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" class="form-control-custom"
                          placeholder="Jelaskan secara detail kondisi kerusakan yang terjadi..."
                          rows="4" maxlength="1000"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <!-- Status otomatis 'Menunggu' — tidak perlu dipilih user -->
            <!-- <hidden> field tidak diperlukan karena $status di-hardcode di PHP -->

            <hr class="section-divider">

            <!-- Upload Foto -->
            <h6 style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;">
                <i class="bi bi-image me-1"></i> Foto Bukti <small style="text-transform:none;font-weight:400;">(opsional)</small>
            </h6>

            <div class="form-group">
                <div class="upload-area" onclick="document.getElementById('foto_bukti').click()">
                    <i class="bi bi-cloud-upload"></i>
                    <p><strong style="color:var(--primary-light);">Klik untuk memilih foto</strong> atau seret & lepas di sini</p>
                    <p class="upload-hint">Format: JPG, JPEG, PNG &bull; Maks. 5 MB</p>
                </div>
                <input type="file" id="foto_bukti" name="foto_bukti" accept=".jpg,.jpeg,.png">

                <!-- Preview gambar yang dipilih -->
                <div id="preview-container">
                    <img id="preview-img" src="" alt="Preview foto">
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;" id="preview-name"></p>
                </div>
            </div>

            <hr class="section-divider">

            <!-- Tombol Aksi -->
            <div class="d-flex gap-3">
                <a href="dashboard.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Batal
                </a>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send-fill"></i> Kirim Laporan
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
// Preview foto sebelum upload
document.getElementById('foto_bukti').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader   = new FileReader();
    const preview  = document.getElementById('preview-container');
    const img      = document.getElementById('preview-img');
    const nameEl   = document.getElementById('preview-name');

    reader.onload = (e) => {
        img.src          = e.target.result;
        nameEl.textContent = `📎 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// Drag & drop pada upload area
const uploadArea = document.querySelector('.upload-area');
uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.style.borderColor = '#818cf8'; });
uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = '#334155'; });
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#334155';
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('foto_bukti').files = dt.files;
        document.getElementById('foto_bukti').dispatchEvent(new Event('change'));
    }
});
</script>

</body>
</html>
