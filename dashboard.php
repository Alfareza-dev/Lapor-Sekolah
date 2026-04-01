<?php
// ============================================
// FILE: dashboard.php
// Deskripsi: Halaman utama setelah login.
// Admin: lihat semua laporan + Edit/Hapus/Status
// User : lihat laporan miliknya sendiri saja
// ============================================

session_start();
require_once 'koneksi.php';

// ── GUARD: wajib login ──────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil variabel session
$session_id   = (int) $_SESSION['user_id'];
$session_nama = $_SESSION['nama'];
$session_role = $_SESSION['role']; // 'admin' atau 'user'
$is_admin     = ($session_role === 'admin');

// ── Query laporan berdasarkan role ──────────
if ($is_admin) {
    // Admin melihat SEMUA laporan dari semua user
    $query = "SELECT lk.*, u.nama AS nama_user
              FROM laporan_kerusakan lk
              LEFT JOIN users u ON lk.user_id = u.id
              ORDER BY lk.tanggal_lapor DESC";
} else {
    // User biasa hanya melihat laporan MILIKNYA
    $query = "SELECT lk.*, u.nama AS nama_user
              FROM laporan_kerusakan lk
              LEFT JOIN users u ON lk.user_id = u.id
              WHERE lk.user_id = $session_id
              ORDER BY lk.tanggal_lapor DESC";
}

$result = mysqli_query($koneksi, $query);

// ── Pesan notifikasi dari redirect ──────────
$pesan = '';
if (isset($_GET['pesan'])) {
    $map = [
        'tambah_sukses'    => ['success', '<i class="bi bi-check-circle-fill me-2"></i> Laporan berhasil ditambahkan!'],
        'edit_sukses'      => ['info',    '<i class="bi bi-arrow-repeat me-2"></i> Status laporan berhasil diperbarui!'],
        'hapus_sukses'     => ['warning', '<i class="bi bi-trash-fill me-2"></i> Laporan berhasil dihapus!'],
        'hapus_user_sukses'=> ['warning', '<i class="bi bi-person-x-fill me-2"></i> Akun user berhasil dihapus. Laporan mereka tetap tersimpan sebagai Anonim.'],
        'register_sukses'  => ['success', '<i class="bi bi-person-check-fill me-2"></i> Akun berhasil dibuat! Selamat datang, ' . htmlspecialchars($session_nama) . '!'],
    ];
    $key = $_GET['pesan'];
    if (isset($map[$key])) {
        [$type, $msg] = $map[$key];
        $pesan = "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">{$msg}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
    }
}

// ── Hitung statistik ────────────────────────
$total = mysqli_num_rows($result);
mysqli_data_seek($result, 0);
$menunggu = $diproses = $selesai = 0;
while ($r = mysqli_fetch_assoc($result)) {
    if ($r['status'] === 'Menunggu') $menunggu++;
    if ($r['status'] === 'Diproses') $diproses++;
    if ($r['status'] === 'Selesai')  $selesai++;
}
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Lapor-Sekolah</title>
    <meta name="description" content="Dashboard Lapor-Sekolah — kelola laporan kerusakan fasilitas.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:#4f46e5; --primary-dark:#3730a3; --primary-light:#818cf8;
            --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
            --dark-bg:#0f172a; --card-bg:#1e293b; --border:#334155;
            --text-primary:#f1f5f9; --text-muted:#94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark-bg); color:var(--text-primary); min-height:100vh; }

        /* ===== NAVBAR ===== */
        .navbar-custom {
            background:rgba(15,23,42,0.95); backdrop-filter:blur(12px);
            border-bottom:1px solid var(--border); padding:0.85rem 0;
            position:sticky; top:0; z-index:100;
        }
        .navbar-brand-custom {
            font-size:1.3rem; font-weight:900;
            background:linear-gradient(135deg,var(--primary-light),#c084fc);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; text-decoration:none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color:var(--text-primary); }

        /* User info pill */
        .user-pill {
            display:inline-flex; align-items:center; gap:0.5rem;
            background:rgba(255,255,255,0.05);
            border:1px solid var(--border); border-radius:50px;
            padding:0.3rem 0.9rem; font-size:0.825rem; font-weight:600;
        }
        .user-pill .avatar {
            width:26px; height:26px; border-radius:50%;
            background:linear-gradient(135deg,var(--primary),#7c3aed);
            display:inline-flex; align-items:center; justify-content:center;
            font-size:0.75rem; font-weight:800; color:white; flex-shrink:0;
        }
        .role-badge-admin {
            background:rgba(192,132,252,0.15); color:#c084fc;
            border:1px solid rgba(192,132,252,0.3);
            font-size:0.65rem; font-weight:700; padding:0.15rem 0.5rem;
            border-radius:50px; text-transform:uppercase; letter-spacing:0.05em;
        }
        .role-badge-user {
            background:rgba(129,140,248,0.15); color:#818cf8;
            border:1px solid rgba(129,140,248,0.3);
            font-size:0.65rem; font-weight:700; padding:0.15rem 0.5rem;
            border-radius:50px; text-transform:uppercase; letter-spacing:0.05em;
        }
        .btn-logout {
            background:rgba(239,68,68,0.1); color:#fca5a5;
            border:1px solid rgba(239,68,68,0.25); border-radius:8px;
            padding:0.35rem 0.85rem; font-size:0.8rem; font-weight:600;
            text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;
            transition:all 0.2s;
        }
        .btn-logout:hover { background:rgba(239,68,68,0.2); color:#fecaca; }

        /* ===== HERO ===== */
        .hero-section {
            background:linear-gradient(135deg,#1e1b4b,#312e81,#1e1b4b);
            border-bottom:1px solid var(--border); padding:2.5rem 0;
            position:relative; overflow:hidden;
        }
        .hero-section::before {
            content:''; position:absolute; inset:0;
            background:radial-gradient(circle at 30% 50%,rgba(79,70,229,0.15),transparent 60%),
                        radial-gradient(circle at 70% 50%,rgba(192,132,252,0.10),transparent 60%);
        }
        .hero-section .container { position:relative; z-index:1; }
        .hero-title  { font-size:clamp(1.5rem,3vw,2rem); font-weight:800; margin-bottom:0.5rem; }
        .hero-sub    { color:var(--text-muted); font-size:0.9rem; }

        /* Admin badge strip */
        .admin-strip {
            background:rgba(192,132,252,0.1); border:1px solid rgba(192,132,252,0.25);
            border-radius:10px; padding:0.6rem 1rem; margin-bottom:1rem;
            font-size:0.85rem; color:#c084fc; font-weight:600;
            display:inline-flex; align-items:center; gap:0.5rem;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background:rgba(255,255,255,0.04); border:1px solid var(--border);
            border-radius:12px; padding:1rem 1.25rem; text-align:center;
            backdrop-filter:blur(8px);
        }
        .stat-number { font-size:1.8rem; font-weight:800; line-height:1; margin-bottom:0.25rem; }
        .stat-label  { font-size:0.75rem; color:var(--text-muted); font-weight:500; }

        /* ===== MAIN ===== */
        .main-content { padding:2rem 0; }
        .table-card   { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; }
        .table-card-header {
            padding:1.25rem 1.5rem; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:0.75rem;
        }
        .table-card-header h5 { font-weight:700; font-size:1rem; margin:0; }

        .table-responsive { overflow-x:auto; }
        .table-custom { width:100%; border-collapse:collapse; font-size:0.875rem; }
        .table-custom thead th {
            background:rgba(79,70,229,0.15); color:var(--primary-light);
            font-weight:600; padding:0.85rem 1.25rem; border-bottom:1px solid var(--border);
            white-space:nowrap; text-transform:uppercase; font-size:0.72rem; letter-spacing:0.05em;
        }
        .table-custom tbody tr { border-bottom:1px solid rgba(51,65,85,0.5); transition:background 0.15s; }
        .table-custom tbody tr:last-child { border-bottom:none; }
        .table-custom tbody tr:hover { background:rgba(79,70,229,0.07); }
        .table-custom tbody td { padding:1rem 1.25rem; vertical-align:middle; color:var(--text-primary); }
        .text-muted-custom { color:var(--text-muted); font-size:0.78rem; }

        .badge-status { display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; border-radius:50px; font-size:0.72rem; font-weight:700; }
        .badge-menunggu { background:rgba(245,158,11,0.15); color:#fcd34d; border:1px solid rgba(245,158,11,0.3); }
        .badge-diproses { background:rgba(59,130,246,0.15);  color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .badge-selesai  { background:rgba(16,185,129,0.15);  color:#6ee7b7; border:1px solid rgba(16,185,129,0.3); }

        .foto-thumb {
            width:50px; height:50px; object-fit:cover; border-radius:8px;
            border:2px solid var(--border); cursor:pointer;
            transition:transform 0.2s, border-color 0.2s;
        }
        .foto-thumb:hover { transform:scale(1.08); border-color:var(--primary-light); }
        .no-foto {
            width:50px; height:50px; background:rgba(255,255,255,0.04);
            border:2px dashed var(--border); border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            color:var(--text-muted); font-size:1rem;
        }

        .btn-action { border:none; border-radius:8px; padding:0.35rem 0.75rem; font-size:0.78rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; transition:all 0.2s; }
        .btn-edit   { background:rgba(59,130,246,0.15); color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .btn-edit:hover  { background:rgba(59,130,246,0.3); color:#bfdbfe; }
        .btn-hapus  { background:rgba(239,68,68,0.15); color:#fca5a5; border:1px solid rgba(239,68,68,0.3); }
        .btn-hapus:hover { background:rgba(239,68,68,0.3); color:#fecaca; }
        .btn-tambah {
            background:linear-gradient(135deg,var(--primary),#7c3aed); color:white;
            border-radius:10px; padding:0.55rem 1.25rem; text-decoration:none;
            font-weight:700; font-size:0.875rem; display:inline-flex; align-items:center; gap:0.4rem;
            transition:all 0.2s; box-shadow:0 4px 15px rgba(79,70,229,0.3);
        }
        .btn-tambah:hover { color:white; transform:translateY(-2px); box-shadow:0 6px 20px rgba(79,70,229,0.5); }

        .row-num { background:rgba(79,70,229,0.15); color:var(--primary-light); border-radius:6px; padding:0.15rem 0.5rem; font-size:0.72rem; font-weight:700; }

        .empty-state { padding:4rem 2rem; text-align:center; color:var(--text-muted); }
        .empty-state i { font-size:3rem; margin-bottom:1rem; opacity:0.4; display:block; }

        /* Modal foto */
        #fotoModal .modal-content { background:var(--card-bg); border:1px solid var(--border); border-radius:16px; }
        #fotoModal .modal-header  { border-bottom:1px solid var(--border); }
        #fotoModal .modal-title   { color:var(--text-primary); font-weight:600; }
        #fotoModal .btn-close     { filter:invert(1); }
        #modalFotoImg             { max-width:100%; border-radius:10px; }

        footer { background:var(--card-bg); border-top:1px solid var(--border); padding:1.5rem 0; text-align:center; color:var(--text-muted); font-size:0.85rem; }

        @media(max-width:768px) {
            .hero-section { padding:1.5rem 0; }
            .table-custom thead th, .table-custom tbody td { padding:0.65rem 0.9rem; }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar-custom">
    <div class="container d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <a class="navbar-brand-custom" href="dashboard.php">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Link Kelola User (Admin Only) -->
            <?php if ($is_admin): ?>
            <a href="kelola_user.php" style="
                background:rgba(192,132,252,0.1); color:#c084fc;
                border:1px solid rgba(192,132,252,0.3); border-radius:8px;
                padding:0.35rem 0.85rem; font-size:0.8rem; font-weight:600;
                text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; transition:all 0.2s;">
                <i class="bi bi-people-fill"></i> Kelola User
            </a>
            <?php endif; ?>
            <!-- User info -->
            <div class="user-pill">
                <div class="avatar"><?= mb_strtoupper(mb_substr($session_nama, 0, 1)) ?></div>
                <span><?= htmlspecialchars(mb_substr($session_nama, 0, 18)) ?></span>
                <?php if ($is_admin): ?>
                    <span class="role-badge-admin">Admin</span>
                <?php else: ?>
                    <span class="role-badge-user">User</span>
                <?php endif; ?>
            </div>
            <!-- Logout -->
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <?php if ($is_admin): ?>
                <div class="admin-strip">
                    <i class="bi bi-star-fill"></i> Mode Admin — Akses penuh ke semua laporan
                </div>
                <?php endif; ?>
                <h1 class="hero-title">
                    <?= $is_admin ? 'Panel Admin' : 'Dashboard' ?> —
                    <span style="background:linear-gradient(135deg,#818cf8,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                        Lapor-Sekolah
                    </span>
                </h1>
                <p class="hero-sub">
                    <?= $is_admin
                        ? 'Kelola semua laporan kerusakan fasilitas dari seluruh pengguna.'
                        : 'Selamat datang, <strong>' . htmlspecialchars($session_nama) . '</strong>! Pantau laporan kamu di sini.'
                    ?>
                </p>
            </div>
            <div class="col-md-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-card">
                            <div class="stat-number" style="color:#818cf8;"><?= $total ?></div>
                            <div class="stat-label"><?= $is_admin ? 'Total Laporan' : 'Laporan Saya' ?></div>
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

        <div class="table-card">
            <div class="table-card-header">
                <h5>
                    <i class="bi bi-table me-2" style="color:var(--primary-light);"></i>
                    <?= $is_admin ? 'Semua Laporan Kerusakan' : 'Laporan Saya' ?>
                </h5>
                <a href="tambah.php" class="btn-tambah">
                    <i class="bi bi-plus-lg"></i> Buat Laporan
                </a>
            </div>

            <?php if (mysqli_num_rows($result) === 0): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h6 style="color:var(--text-primary);font-weight:600;margin-bottom:0.5rem;">
                        <?= $is_admin ? 'Belum Ada Laporan' : 'Kamu Belum Membuat Laporan' ?>
                    </h6>
                    <p><?= $is_admin
                        ? 'Belum ada laporan dari pengguna manapun.'
                        : 'Klik "Buat Laporan" untuk melaporkan kerusakan fasilitas.'
                    ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <?php if ($is_admin): ?>
                                <th>Pelapor</th>
                                <?php endif; ?>
                                <th>Fasilitas</th>
                                <th>Deskripsi</th>
                                <th>Foto</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while ($laporan = mysqli_fetch_assoc($result)):
                            $badge_class = 'badge-menunggu'; $badge_icon = 'bi-hourglass-split';
                            if ($laporan['status'] === 'Diproses') { $badge_class = 'badge-diproses'; $badge_icon = 'bi-arrow-repeat'; }
                            if ($laporan['status'] === 'Selesai')  { $badge_class = 'badge-selesai';  $badge_icon = 'bi-check2-circle'; }
                        ?>
                        <tr>
                            <td><span class="row-num"><?= $no++ ?></span></td>

                            <?php if ($is_admin): ?>
                            <td>
                                <?php
                                // Jika user sudah dihapus, nama_pelapor tetap ada.
                                // Tampilkan akun user sebagai 'Anonim' jika user_id NULL.
                                $nama_pelapor_display = htmlspecialchars($laporan['nama_pelapor'] ?? 'Anonim');
                                if (empty($laporan['nama_pelapor'])) {
                                    $nama_pelapor_display = '<em style="color:var(--text-muted);">Anonim</em>';
                                }
                                ?>
                                <strong style="font-size:0.85rem;"><?= $nama_pelapor_display ?></strong>
                                <?php if (!empty($laporan['nama_user'])): ?>
                                <div class="text-muted-custom"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($laporan['nama_user']) ?></div>
                                <?php else: ?>
                                <div class="text-muted-custom"><i class="bi bi-person-slash me-1"></i><em>User dihapus</em></div>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>

                            <td><?= htmlspecialchars($laporan['fasilitas']) ?></td>
                            <td>
                                <span title="<?= htmlspecialchars($laporan['deskripsi']) ?>">
                                    <?= htmlspecialchars(mb_substr($laporan['deskripsi'], 0, 55)) ?>
                                    <?= mb_strlen($laporan['deskripsi']) > 55 ? '...' : '' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($laporan['foto_bukti']) && file_exists('uploads/' . $laporan['foto_bukti'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                                         alt="Foto" class="foto-thumb"
                                         data-bs-toggle="modal" data-bs-target="#fotoModal"
                                         data-src="uploads/<?= htmlspecialchars($laporan['foto_bukti']) ?>"
                                         data-nama="<?= htmlspecialchars($laporan['fasilitas']) ?>">
                                <?php else: ?>
                                    <div class="no-foto" title="Tidak ada foto"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status <?= $badge_class ?>">
                                    <i class="bi <?= $badge_icon ?>"></i> <?= $laporan['status'] ?>
                                </span>
                            </td>
                            <td>
                                <div><?= date('d M Y', strtotime($laporan['tanggal_lapor'])) ?></div>
                                <div class="text-muted-custom"><?= date('H:i', strtotime($laporan['tanggal_lapor'])) ?> WIB</div>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <?php if ($is_admin): ?>
                                    <!-- Admin: tampilkan Edit & Hapus -->
                                    <a href="edit.php?id=<?= $laporan['id'] ?>" class="btn-action btn-edit">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </a>
                                    <button type="button"
                                            class="btn-action btn-hapus tombol-hapus"
                                            data-id="<?= $laporan['id'] ?>"
                                            data-nama="<?= htmlspecialchars($laporan['fasilitas']) ?>">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                    <?php else: ?>
                                    <!-- User: hanya tampilkan status badge, tidak ada Edit/Hapus -->
                                    <span style="color:var(--text-muted);font-size:0.78rem;">
                                        <i class="bi bi-lock-fill me-1"></i>Read Only
                                    </span>
                                    <?php endif; ?>
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

<!-- ===== MODAL FOTO ===== -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image me-2"></i><span id="modalFotoLabel">Foto Bukti</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalFotoImg" src="" alt="Foto Bukti">
            </div>
        </div>
    </div>
</div>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container">
        <p><i class="bi bi-shield-check me-1" style="color:var(--primary-light);"></i>
           <strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?></p>
        <p class="mt-1" style="font-size:0.75rem;opacity:0.6;">PHP Native &amp; Bootstrap 5 &bull; Logged in as: <strong><?= htmlspecialchars($session_nama) ?></strong> (<?= $session_role ?>)</p>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── 1. Foto Modal ──
const fotoModal = document.getElementById('fotoModal');
fotoModal.addEventListener('show.bs.modal', function(e) {
    const t = e.relatedTarget;
    document.getElementById('modalFotoImg').src           = t.getAttribute('data-src');
    document.getElementById('modalFotoLabel').textContent = t.getAttribute('data-nama');
});

// ── 2. Auto-hide alert ──
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        try { new bootstrap.Alert(el).close(); } catch(e) {}
    });
}, 4500);

// ── 3. Konfirmasi Hapus SweetAlert2 (Admin Only) ──
document.querySelectorAll('.tombol-hapus').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id   = this.getAttribute('data-id');
        const nama = this.getAttribute('data-nama');

        Swal.fire({
            title: 'Hapus Laporan?',
            html: `Menghapus laporan fasilitas:<br><strong style="color:#f87171;">${nama}</strong><br><br>
                   <span style="font-size:0.83rem;color:#94a3b8;">Data dan foto bukti akan hilang permanen.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
            cancelButtonText:  '<i class="bi bi-x-lg"></i> Batal',
            reverseButtons: true,
            background: '#1e293b', color: '#f1f5f9', iconColor: '#f59e0b',
            customClass: {
                popup:         'swal-popup-custom',
                title:         'swal-title-custom',
                confirmButton: 'swal-btn-confirm',
                cancelButton:  'swal-btn-cancel',
            },
            buttonsStyling: false,
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = 'hapus.php?id=' + id;
            }
        });
    });
});
</script>

<style>
.swal-popup-custom { border:1px solid #334155!important; border-radius:16px!important; font-family:'Inter',sans-serif!important; box-shadow:0 20px 60px rgba(0,0,0,0.6)!important; }
.swal-title-custom { font-size:1.25rem!important; font-weight:800!important; color:#f1f5f9!important; }
.swal-btn-confirm  { background:linear-gradient(135deg,#dc2626,#991b1b)!important; color:white!important; border:none!important; border-radius:10px!important; padding:0.6rem 1.4rem!important; font-weight:700!important; font-size:0.875rem!important; cursor:pointer!important; display:inline-flex!important; align-items:center!important; gap:0.4rem!important; transition:all 0.2s!important; box-shadow:0 4px 14px rgba(220,38,38,0.4)!important; }
.swal-btn-confirm:hover { transform:translateY(-2px)!important; }
.swal-btn-cancel   { background:rgba(255,255,255,0.07)!important; color:#94a3b8!important; border:1px solid #334155!important; border-radius:10px!important; padding:0.6rem 1.4rem!important; font-weight:600!important; font-size:0.875rem!important; cursor:pointer!important; display:inline-flex!important; align-items:center!important; gap:0.4rem!important; transition:all 0.2s!important; }
.swal-btn-cancel:hover  { background:rgba(255,255,255,0.12)!important; color:#f1f5f9!important; }
.swal2-icon.swal2-warning { border-color:#f59e0b!important; }
</style>

</body>
</html>
