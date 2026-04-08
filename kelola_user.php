<?php
session_start();
require_once 'config/koneksi.php';
require_once 'config/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: /dashboard');
    exit;
}

$admin_id    = (int) $_SESSION['user_id'];
$admin_nama  = $_SESSION['nama'];
$admin_role  = $_SESSION['role'];

$query = "SELECT u.id, u.nama, u.email, u.role, u.created_at,
                 COUNT(lk.id) AS jumlah_laporan
          FROM users u
          LEFT JOIN laporan_kerusakan lk ON lk.user_id = u.id
          GROUP BY u.id
          ORDER BY u.created_at DESC";

$result = mysqli_query($koneksi, $query);
$total_user = mysqli_num_rows($result);

$pesan = '';
if (isset($_GET['pesan']) && $_GET['pesan'] === 'hapus_user_sukses') {
    $pesan = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-person-x-fill me-2"></i>
                Akun user berhasil dihapus. Laporan yang pernah dibuat tetap tersimpan sebagai <strong>Anonim</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>Kelola User | Lapor-Sekolah Admin</title>
    <meta name="description" content="Panel admin untuk mengelola akun user yang terdaftar di Lapor-Sekolah.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:#4f46e5; --primary-light:#818cf8;
            --violet:#7c3aed; --pink:#c084fc;
            --dark-bg:#0f172a; --card-bg:#1e293b; --border:#334155;
            --text-primary:#f1f5f9; --text-muted:#94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--dark-bg); color:var(--text-primary); min-height:100vh; }
        .navbar-custom {
            background:rgba(15,23,42,0.95); backdrop-filter:blur(12px);
            border-bottom:1px solid var(--border); padding:0.85rem 0;
            position:sticky; top:0; z-index:100;
        }
        .navbar-brand-custom {
            font-size:1.3rem; font-weight:900;
            background:linear-gradient(135deg,var(--primary-light),var(--pink));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; text-decoration:none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color:var(--text-primary); }
        .btn-back-nav {
            background:rgba(255,255,255,0.05); color:var(--text-muted);
            border:1px solid var(--border); border-radius:8px;
            padding:0.35rem 0.85rem; font-size:0.8rem; font-weight:600;
            text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;
            transition:all 0.2s;
        }
        .btn-back-nav:hover { color:var(--text-primary); background:rgba(255,255,255,0.08); }
        .page-header {
            background:linear-gradient(135deg,#1e1b4b,#2e1065,#1e1b4b);
            border-bottom:1px solid var(--border); padding:2rem 0;
            position:relative; overflow:hidden;
        }
        .page-header::before {
            content:''; position:absolute; inset:0;
            background:radial-gradient(circle at 20% 50%,rgba(192,132,252,0.12),transparent 60%),
                        radial-gradient(circle at 80% 50%,rgba(79,70,229,0.10),transparent 60%);
        }
        .page-header .container { position:relative; z-index:1; }
        .page-icon {
            width:52px; height:52px; border-radius:14px;
            background:linear-gradient(135deg,rgba(192,132,252,0.2),rgba(124,58,237,0.2));
            border:1px solid rgba(192,132,252,0.4);
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; flex-shrink:0;
        }
        .page-title { font-size:1.6rem; font-weight:800; margin:0; }
        .page-sub   { color:var(--text-muted); font-size:0.875rem; margin:0.3rem 0 0; }
        .stat-inline {
            display:inline-flex; align-items:center; gap:0.5rem;
            background:rgba(192,132,252,0.1); border:1px solid rgba(192,132,252,0.25);
            color:var(--pink); border-radius:10px;
            padding:0.5rem 1rem; font-size:0.85rem; font-weight:600;
        }
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
            background:rgba(124,58,237,0.15); color:var(--pink);
            font-weight:600; padding:0.85rem 1.25rem;
            border-bottom:1px solid var(--border);
            white-space:nowrap; text-transform:uppercase;
            font-size:0.72rem; letter-spacing:0.05em;
        }
        .table-custom tbody tr { border-bottom:1px solid rgba(51,65,85,0.5); transition:background 0.15s; }
        .table-custom tbody tr:last-child { border-bottom:none; }
        .table-custom tbody tr:hover { background:rgba(124,58,237,0.06); }
        .table-custom tbody td { padding:0.95rem 1.25rem; vertical-align:middle; color:var(--text-primary); }
        .text-muted-sm { color:var(--text-muted); font-size:0.78rem; }
        .user-avatar {
            width:36px; height:36px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:0.875rem; font-weight:800; color:white; flex-shrink:0;
        }
        .badge-role-admin {
            background:rgba(192,132,252,0.15); color:#c084fc;
            border:1px solid rgba(192,132,252,0.3);
            padding:0.25rem 0.7rem; border-radius:50px;
            font-size:0.7rem; font-weight:700; text-transform:uppercase;
        }
        .badge-role-user {
            background:rgba(129,140,248,0.15); color:#818cf8;
            border:1px solid rgba(129,140,248,0.3);
            padding:0.25rem 0.7rem; border-radius:50px;
            font-size:0.7rem; font-weight:700; text-transform:uppercase;
        }
        .badge-count {
            display:inline-flex; align-items:center; gap:0.3rem;
            background:rgba(79,70,229,0.12); color:var(--primary-light);
            border:1px solid rgba(79,70,229,0.25);
            padding:0.2rem 0.6rem; border-radius:50px;
            font-size:0.75rem; font-weight:700;
        }
        .btn-hapus-user {
            background:rgba(239,68,68,0.12); color:#fca5a5;
            border:1px solid rgba(239,68,68,0.25); border-radius:8px;
            padding:0.35rem 0.75rem; font-size:0.78rem; font-weight:600;
            cursor:pointer; display:inline-flex; align-items:center; gap:0.3rem;
            transition:all 0.2s;
        }
        .btn-hapus-user:hover { background:rgba(239,68,68,0.25); color:#fecaca; }
        .self-row td { opacity:0.6; }
        .badge-self {
            background:rgba(16,185,129,0.12); color:#6ee7b7;
            border:1px solid rgba(16,185,129,0.25);
            padding:0.2rem 0.6rem; border-radius:50px; font-size:0.7rem; font-weight:700;
        }
        .row-num { background:rgba(124,58,237,0.15); color:var(--pink); border-radius:6px; padding:0.15rem 0.5rem; font-size:0.72rem; font-weight:700; }
        .empty-state { padding:4rem 2rem; text-align:center; color:var(--text-muted); }
        .empty-state i { font-size:3rem; margin-bottom:1rem; opacity:0.4; display:block; }
        .info-box {
            background:rgba(245,158,11,0.07); border:1px solid rgba(245,158,11,0.2);
            border-radius:12px; padding:0.9rem 1.25rem; margin-bottom:1.5rem;
            font-size:0.85rem; color:#fcd34d;
            display:flex; align-items:flex-start; gap:0.75rem;
        }
        .info-box i { font-size:1.1rem; flex-shrink:0; margin-top:0.1rem; }
        footer { background:var(--card-bg); border-top:1px solid var(--border); padding:1.5rem 0; text-align:center; color:var(--text-muted); font-size:0.85rem; margin-top:0; }
    </style>
</head>
<body>
<nav class="navbar-custom">
    <div class="container d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <a class="navbar-brand-custom" href="/dashboard">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:0.8rem;color:var(--text-muted);">
                <i class="bi bi-person-fill-gear me-1" style="color:#c084fc;"></i>
                Admin: <strong style="color:var(--text-primary);"><?= htmlspecialchars($admin_nama) ?></strong>
            </span>
            <a href="/dashboard" class="btn-back-nav">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">👥</div>
            <div>
                <h1 class="page-title">Kelola User</h1>
                <p class="page-sub">Daftar seluruh akun yang terdaftar di Lapor-Sekolah.</p>
            </div>
            <div class="ms-auto">
                <span class="stat-inline">
                    <i class="bi bi-people-fill"></i>
                    <?= $total_user ?> Akun Terdaftar
                </span>
            </div>
        </div>
    </div>
</div>

<main class="main-content">
    <div class="container">
        <?= $pesan ?>
        <div class="info-box">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Catatan Penting:</strong>
                Jika akun user dihapus, <strong>semua laporan yang pernah mereka buat akan tetap tersimpan</strong>
                di database. Laporan tersebut akan ditampilkan atas nama <em>"Anonim"</em>.
                Kamu tidak dapat menghapus akun Admin yang sedang aktif (akunmu sendiri).
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <h5>
                    <i class="bi bi-people me-2" style="color:var(--pink);"></i>
                    Daftar User Terdaftar
                </h5>
                <span class="badge-count">
                    <i class="bi bi-person-fill"></i> <?= $total_user ?> total
                </span>
            </div>

            <?php if ($total_user === 0): ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h6 style="color:var(--text-primary);font-weight:600;">Belum Ada User</h6>
                    <p>Belum ada akun yang terdaftar.</p>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Laporan</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    mysqli_data_seek($result, 0);
                    while ($user = mysqli_fetch_assoc($result)):
                        $is_self   = ($user['id'] == $admin_id);
                        $is_admin_row = ($user['role'] === 'admin');
                        $avatar_style = $is_admin_row
                            ? 'background:linear-gradient(135deg,#7c3aed,#c084fc);'
                            : 'background:linear-gradient(135deg,#4f46e5,#818cf8);';
                    ?>
                    <tr <?= $is_self ? 'class="self-row"' : '' ?>>
                        <td><span class="row-num"><?= $no++ ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar" style="<?= $avatar_style ?>">
                                    <?= mb_strtoupper(mb_substr($user['nama'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:0.875rem;">
                                        <?= htmlspecialchars($user['nama']) ?>
                                        <?php if ($is_self): ?>
                                            <span class="badge-self ms-1">Kamu</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted-sm">ID: #<?= $user['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size:0.85rem;"><?= htmlspecialchars($user['email']) ?></span>
                        </td>
                        <td>
                            <?php if ($is_admin_row): ?>
                                <span class="badge-role-admin"><i class="bi bi-star-fill me-1"></i>Admin</span>
                            <?php else: ?>
                                <span class="badge-role-user"><i class="bi bi-person-fill me-1"></i>User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-count">
                                <i class="bi bi-file-text"></i>
                                <?= (int) $user['jumlah_laporan'] ?> laporan
                            </span>
                        </td>
                        <td>
                            <div><?= date('d M Y', strtotime($user['created_at'])) ?></div>
                            <div class="text-muted-sm"><?= date('H:i', strtotime($user['created_at'])) ?> WIB</div>
                        </td>
                        <td>
                            <?php if ($is_self): ?>
                                <span style="color:var(--text-muted);font-size:0.78rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Akun Aktif
                                </span>
                            <?php else: ?>
                                <button type="button"
                                        class="btn-hapus-user tombol-hapus-user"
                                        data-id="<?= $user['id'] ?>"
                                        data-nama="<?= htmlspecialchars($user['nama']) ?>"
                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-laporan="<?= (int) $user['jumlah_laporan'] ?>">
                                    <i class="bi bi-person-x-fill"></i> Hapus
                                </button>
                            <?php endif; ?>
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

<footer>
    <div class="container">
        <p>
            <i class="bi bi-shield-check me-1" style="color:var(--primary-light);"></i>
            <strong>Lapor-Sekolah</strong> &mdash; Panel Admin &copy; <?= date('Y') ?>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        try { new bootstrap.Alert(el).close(); } catch(e) {}
    });
}, 5000);

document.querySelectorAll('.tombol-hapus-user').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id      = this.getAttribute('data-id');
        const nama    = this.getAttribute('data-nama');
        const email   = this.getAttribute('data-email');
        const laporan = parseInt(this.getAttribute('data-laporan'));

        const warningMsg = laporan > 0
            ? `<span style="font-size:0.83rem;color:#fcd34d;">
                   ⚠️ User ini memiliki <strong>${laporan} laporan</strong>.
                   Laporan-laporan tersebut <u>tidak akan terhapus</u>, melainkan namanya berubah menjadi <strong>Anonim</strong>.
               </span>`
            : `<span style="font-size:0.83rem;color:#94a3b8;">User ini belum membuat laporan apapun.</span>`;

        Swal.fire({
            title: 'Hapus Akun User?',
            html: `Kamu akan menghapus akun:<br>
                   <strong style="color:#f87171;">${nama}</strong><br>
                   <span style="font-size:0.8rem;color:#94a3b8;">${email}</span><br><br>
                   ${warningMsg}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-person-x-fill"></i> Ya, Hapus Akun!',
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
                window.location.href = '/hapus-user/' + id;
            }
        });
    });
});
</script>
<style>
.swal-popup-custom { border:1px solid #334155!important; border-radius:16px!important; font-family:'Inter',sans-serif!important; box-shadow:0 20px 60px rgba(0,0,0,0.6)!important; }
.swal-title-custom { font-size:1.2rem!important; font-weight:800!important; color:#f1f5f9!important; }
.swal-btn-confirm  { background:linear-gradient(135deg,#dc2626,#991b1b)!important; color:white!important; border:none!important; border-radius:10px!important; padding:0.6rem 1.4rem!important; font-weight:700!important; font-size:0.875rem!important; cursor:pointer!important; display:inline-flex!important; align-items:center!important; gap:0.4rem!important; transition:all 0.2s!important; box-shadow:0 4px 14px rgba(220,38,38,0.4)!important; }
.swal-btn-confirm:hover { transform:translateY(-2px)!important; }
.swal-btn-cancel   { background:rgba(255,255,255,0.07)!important; color:#94a3b8!important; border:1px solid #334155!important; border-radius:10px!important; padding:0.6rem 1.4rem!important; font-weight:600!important; font-size:0.875rem!important; cursor:pointer!important; display:inline-flex!important; align-items:center!important; gap:0.4rem!important; transition:all 0.2s!important; }
.swal-btn-cancel:hover  { background:rgba(255,255,255,0.12)!important; color:#f1f5f9!important; }
.swal2-icon.swal2-warning { border-color:#f59e0b!important; }
</style>
</body>
</html>
