<?php
// ============================================
// FILE: index.php
// Deskripsi: Halaman utama - Menampilkan semua
// laporan kerusakan dalam bentuk tabel
// ============================================

require_once 'koneksi.php';

// Ambil semua data laporan, diurutkan terbaru
$query  = "SELECT * FROM laporan_kerusakan ORDER BY tanggal_lapor DESC";
$result = mysqli_query($koneksi, $query);

// Pesan sukses/error dari proses lain (redirect)
$pesan = '';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'tambah_sukses') {
        $pesan = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Laporan berhasil ditambahkan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
    } elseif ($_GET['pesan'] === 'edit_sukses') {
        $pesan = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-pencil-fill me-2"></i> Laporan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
    } elseif ($_GET['pesan'] === 'hapus_sukses') {
        $pesan = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Laporan berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor-Sekolah | Portal Pelaporan Fasilitas</title>
    <meta name="description" content="Portal pelaporan kerusakan fasilitas sekolah. Laporkan kerusakan dengan mudah dan pantau statusnya secara real-time.">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ===== DESIGN SYSTEM ===== */
        :root {
            --primary:      #4f46e5;
            --primary-dark: #3730a3;
            --primary-light:#818cf8;
            --success:      #10b981;
            --warning:      #f59e0b;
            --danger:       #ef4444;
            --dark-bg:      #0f172a;
            --card-bg:      #1e293b;
            --border:       #334155;
            --text-primary: #f1f5f9;
            --text-muted:   #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 1rem 0;
        }
        .navbar-brand-custom {
            font-size: 1.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-light), #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        .navbar-brand-custom span {
            -webkit-text-fill-color: var(--text-primary);
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);
            border-bottom: 1px solid var(--border);
            padding: 3rem 0;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(79,70,229,0.15) 0%, transparent 60%),
                        radial-gradient(circle at 70% 50%, rgba(192,132,252,0.10) 0%, transparent 60%);
        }
        .hero-section .container { position: relative; z-index: 1; }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(79,70,229,0.2);
            border: 1px solid rgba(79,70,229,0.4);
            color: var(--primary-light);
            padding: 0.3rem 0.9rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .hero-title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 0.75rem;
        }
        .hero-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            max-width: 500px;
        }

        /* ===== STATS CARDS ===== */
        .stat-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            text-align: center;
            backdrop-filter: blur(8px);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.3rem;
        }
        .stat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

        /* ===== MAIN CONTENT ===== */
        .main-content { padding: 2rem 0; }

        /* ===== TABLE ===== */
        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }
        .table-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .table-card-header h5 {
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
        }
        .table-responsive { overflow-x: auto; }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .table-custom thead th {
            background: rgba(79,70,229,0.15);
            color: var(--primary-light);
            font-weight: 600;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .table-custom tbody tr {
            border-bottom: 1px solid rgba(51,65,85,0.5);
            transition: background 0.15s;
        }
        .table-custom tbody tr:last-child { border-bottom: none; }
        .table-custom tbody tr:hover { background: rgba(79,70,229,0.07); }
        .table-custom tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            color: var(--text-primary);
        }
        .table-custom tbody td .text-muted-custom {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* ===== BADGE STATUS ===== */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-menunggu  { background: rgba(245,158,11,0.15);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
        .badge-diproses  { background: rgba(59,130,246,0.15);  color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }
        .badge-selesai   { background: rgba(16,185,129,0.15);  color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }

        /* ===== FOTO THUMBNAIL ===== */
        .foto-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--border);
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s;
        }
        .foto-thumb:hover {
            transform: scale(1.08);
            border-color: var(--primary-light);
        }
        .no-foto {
            width: 52px;
            height: 52px;
            background: rgba(255,255,255,0.05);
            border: 2px dashed var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* ===== TOMBOL AKSI ===== */
        .btn-action {
            border: none;
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }
        .btn-edit {
            background: rgba(59,130,246,0.15);
            color: #93c5fd;
            border: 1px solid rgba(59,130,246,0.3);
        }
        .btn-edit:hover { background: rgba(59,130,246,0.3); color: #bfdbfe; }
        .btn-hapus {
            background: rgba(239,68,68,0.15);
            color: #fca5a5;
            border: 1px solid rgba(239,68,68,0.3);
        }
        .btn-hapus:hover { background: rgba(239,68,68,0.3); color: #fecaca; }
        .btn-tambah {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white;
            border-radius: 10px;
            padding: 0.6rem 1.4rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
        }
        .btn-tambah:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.5);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4; }
        .empty-state p { font-size: 0.95rem; }

        /* ===== NOMOR BARIS ===== */
        .row-num {
            background: rgba(79,70,229,0.15);
            color: var(--primary-light);
            border-radius: 6px;
            padding: 0.2rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* ===== FOOTER ===== */
        footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            padding: 1.5rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* ===== MODAL FOTO ===== */
        #fotoModal .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
        }
        #fotoModal .modal-header { border-bottom: 1px solid var(--border); }
        #fotoModal .modal-title { color: var(--text-primary); font-weight: 600; }
        #fotoModal .btn-close { filter: invert(1); }
        #modalFotoImg { max-width: 100%; border-radius: 10px; }

        @media (max-width: 768px) {
            .hero-section { padding: 2rem 0; }
            .table-custom thead th,
            .table-custom tbody td { padding: 0.7rem 0.9rem; }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand-custom" href="index.php">
            <i class="bi bi-shield-exclamation me-2" style="-webkit-text-fill-color: #818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" style="font-size:0.8rem;">
                <i class="bi bi-clock me-1"></i><?= date('d M Y') ?>
            </span>
        </div>
    </div>
</nav>

<!-- ===== HERO SECTION ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <div class="hero-badge">
                    <i class="bi bi-broadcast-pin"></i> Portal Aktif
                </div>
                <h1 class="hero-title">
                    Portal Laporan<br>
                    <span style="background: linear-gradient(135deg,#818cf8,#c084fc); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
                        Kerusakan Fasilitas
                    </span>
                </h1>
                <p class="hero-subtitle">
                    Laporkan kerusakan fasilitas sekolah dengan mudah. Setiap laporan akan segera ditindaklanjuti oleh tim pengelola.
                </p>
            </div>
            <div class="col-md-5">
                <?php
                // Hitung statistik
                $total    = mysqli_num_rows($result);
                mysqli_data_seek($result, 0);

                $menunggu = $diproses = $selesai = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['status'] === 'Menunggu')  $menunggu++;
                    if ($row['status'] === 'Diproses')  $diproses++;
                    if ($row['status'] === 'Selesai')   $selesai++;
                }
                mysqli_data_seek($result, 0); // Reset pointer
                ?>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" style="color:#818cf8;"><?= $total ?></div>
                            <div class="stat-label">Total Laporan</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" style="color:#fcd34d;"><?= $menunggu ?></div>
                            <div class="stat-label">Menunggu</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" style="color:#93c5fd;"><?= $diproses ?></div>
                            <div class="stat-label">Diproses</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" style="color:#6ee7b7;"><?= $selesai ?></div>
                            <div class="stat-label">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<main class="main-content">
    <div class="container">

        <?= $pesan ?>

        <!-- Tabel Laporan -->
        <div class="table-card">
            <div class="table-card-header">
                <h5><i class="bi bi-table me-2" style="color:var(--primary-light);"></i>Daftar Laporan Kerusakan</h5>
                <a href="tambah.php" class="btn-tambah">
                    <i class="bi bi-plus-lg"></i> Buat Laporan Baru
                </a>
            </div>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h6 style="color:var(--text-primary); font-weight:600; margin-bottom:0.5rem;">Belum Ada Laporan</h6>
                    <p>Belum ada laporan kerusakan. Klik tombol "Buat Laporan Baru" untuk menambahkan.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pelapor</th>
                                <th>Fasilitas</th>
                                <th>Deskripsi</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($laporan = mysqli_fetch_assoc($result)):
                                // Tentukan class badge berdasarkan status
                                $badge_class = 'badge-menunggu';
                                $badge_icon  = 'bi-hourglass-split';
                                if ($laporan['status'] === 'Diproses') {
                                    $badge_class = 'badge-diproses';
                                    $badge_icon  = 'bi-arrow-repeat';
                                } elseif ($laporan['status'] === 'Selesai') {
                                    $badge_class = 'badge-selesai';
                                    $badge_icon  = 'bi-check2-circle';
                                }
                            ?>
                            <tr>
                                <td><span class="row-num"><?= $no++ ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($laporan['nama_pelapor']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($laporan['fasilitas']) ?></td>
                                <td>
                                    <span title="<?= htmlspecialchars($laporan['deskripsi']) ?>">
                                        <?= htmlspecialchars(mb_substr($laporan['deskripsi'], 0, 60)) ?>
                                        <?= mb_strlen($laporan['deskripsi']) > 60 ? '...' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                                             alt="Foto bukti"
                                             class="foto-thumb"
                                             data-bs-toggle="modal"
                                             data-bs-target="#fotoModal"
                                             data-src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                                             data-nama="<?= htmlspecialchars($laporan['fasilitas']) ?>">
                                    <?php else: ?>
                                        <div class="no-foto" title="Tidak ada foto">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= $badge_class ?>">
                                        <i class="bi <?= $badge_icon ?>"></i>
                                        <?= $laporan['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= date('d M Y', strtotime($laporan['tanggal_lapor'])) ?></div>
                                    <div class="text-muted-custom"><?= date('H:i', strtotime($laporan['tanggal_lapor'])) ?> WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit.php?id=<?= $laporan['id'] ?>" class="btn-action btn-edit">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?= $laporan['id'] ?>"
                                           class="btn-action btn-hapus"
                                           onclick="return confirm('⚠️ Yakin ingin menghapus laporan ini?\nData dan foto akan hilang permanen!')">
                                            <i class="bi bi-trash-fill"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- ===== MODAL LIHAT FOTO ===== -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image me-2"></i><span id="modalFotoLabel">Foto Bukti</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalFotoImg" src="" alt="Foto Bukti Laporan">
            </div>
        </div>
    </div>
</div>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container">
        <p>
            <i class="bi bi-shield-check me-1" style="color:var(--primary-light);"></i>
            <strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?>
        </p>
        <p class="mt-1" style="font-size:0.75rem; opacity:0.6;">Dibuat dengan PHP Native & Bootstrap 5</p>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Tampilkan foto di modal saat thumbnail diklik
const fotoModal = document.getElementById('fotoModal');
fotoModal.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    document.getElementById('modalFotoImg').src   = trigger.getAttribute('data-src');
    document.getElementById('modalFotoLabel').textContent = trigger.getAttribute('data-nama');
});

// Auto-hide alert setelah 4 detik
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        const bsAlert = new bootstrap.Alert(el);
        bsAlert.close();
    });
}, 4000);
</script>

</body>
</html>
