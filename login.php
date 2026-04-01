<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/koneksi.php';

$error = '';
$pesan_url = $_GET['pesan'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $email_esc = mysqli_real_escape_string($koneksi, $email);
        $sql  = "SELECT id, nama, email, password, role FROM users WHERE email = '$email_esc' LIMIT 1";
        $res  = mysqli_query($koneksi, $sql);

        if (mysqli_num_rows($res) === 1) {
            $user = mysqli_fetch_assoc($res);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];
                session_regenerate_id(true);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Password salah. Silakan coba lagi.';
            }
        } else {
            $error = 'Email tidak ditemukan. Silakan daftar terlebih dahulu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Lapor-Sekolah</title>
    <meta name="description" content="Masuk ke akun Lapor-Sekolah kamu untuk mulai membuat laporan.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:#4f46e5; --primary-light:#818cf8; --violet:#7c3aed; --pink:#c084fc;
            --dark-bg:#0f172a; --card-bg:#1e293b; --border:#334155;
            --text-primary:#f1f5f9; --text-muted:#94a3b8;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Inter',sans-serif; background:var(--dark-bg); color:var(--text-primary);
            min-height:100vh; display:flex; flex-direction:column;
        }
        .bg-glow {
            position:fixed; inset:0; pointer-events:none; z-index:0;
            background:
                radial-gradient(ellipse 70% 60% at 15% 30%, rgba(79,70,229,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 85% 70%, rgba(124,58,237,0.09) 0%, transparent 55%);
        }
        nav {
            position:relative; z-index:10;
            background:rgba(15,23,42,0.8); backdrop-filter:blur(20px);
            border-bottom:1px solid rgba(51,65,85,0.5);
            padding:1rem 0;
        }
        .brand {
            font-size:1.4rem; font-weight:900;
            background:linear-gradient(135deg,#818cf8,#c084fc);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; text-decoration:none;
        }
        .brand span { -webkit-text-fill-color:var(--text-primary); }
        .auth-container {
            flex:1; display:flex; align-items:center; justify-content:center;
            padding:clamp(1.5rem, 5vw, 3rem) 1rem; position:relative; z-index:1;
        }
        .auth-card {
            background:var(--card-bg); border:1px solid var(--border);
            border-radius:20px; padding:clamp(1.5rem, 5vw, 2.5rem); width:100%; max-width:440px;
            box-shadow:0 20px 60px rgba(0,0,0,0.4);
        }
        .auth-logo {
            width:56px; height:56px;
            background:linear-gradient(135deg,var(--primary),var(--violet));
            border-radius:14px; display:flex; align-items:center; justify-content:center;
            font-size:1.6rem; margin-bottom:1.5rem;
            box-shadow:0 8px 20px rgba(79,70,229,0.4);
        }
        .auth-title { font-size:1.6rem; font-weight:800; margin-bottom:0.4rem; }
        .auth-sub   { color:var(--text-muted); font-size:0.9rem; margin-bottom:2rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-label-custom {
            display:block; font-weight:600; font-size:0.85rem;
            color:var(--primary-light); margin-bottom:0.4rem;
        }
        .input-wrapper { position:relative; }
        .input-wrapper i.input-icon {
            position:absolute; left:1rem; top:50%; transform:translateY(-50%);
            color:var(--text-muted); font-size:1rem; pointer-events:none;
        }
        .form-control-custom {
            width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border);
            border-radius:10px; padding:0.75rem 1rem 0.75rem 2.75rem;
            color:var(--text-primary); font-size:0.9rem; font-family:'Inter',sans-serif;
            transition:border-color 0.2s, box-shadow 0.2s; outline:none;
        }
        .form-control-custom::placeholder { color:var(--text-muted); }
        .form-control-custom:focus {
            border-color:var(--primary-light);
            box-shadow:0 0 0 3px rgba(79,70,229,0.15);
            background:rgba(79,70,229,0.05);
        }
        .toggle-pass {
            position:absolute; right:1rem; top:50%; transform:translateY(-50%);
            cursor:pointer; color:var(--text-muted); transition:color 0.2s;
            background:none; border:none; padding:0;
        }
        .toggle-pass:hover { color:var(--primary-light); }
        .btn-submit {
            width:100%; background:linear-gradient(135deg,var(--primary),var(--violet));
            color:white; border:none; border-radius:10px;
            padding:0.85rem; font-weight:700; font-size:1rem;
            cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;
            transition:all 0.25s; box-shadow:0 4px 15px rgba(79,70,229,0.35);
            margin-top:0.5rem;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(79,70,229,0.5); }
        .btn-submit:active { transform:translateY(0); }
        .divider {
            display:flex; align-items:center; gap:1rem; margin:1.5rem 0;
            color:var(--text-muted); font-size:0.8rem;
        }
        .divider::before, .divider::after {
            content:''; flex:1; height:1px; background:var(--border);
        }
        .link-register {
            text-align:center; font-size:0.9rem; color:var(--text-muted);
        }
        .link-register a {
            color:var(--primary-light); font-weight:600; text-decoration:none;
        }
        .link-register a:hover { text-decoration:underline; }
        .alert-error {
            background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.35);
            border-radius:10px; padding:0.85rem 1rem; margin-bottom:1.25rem;
            color:#fca5a5; font-size:0.875rem;
            display:flex; align-items:center; gap:0.5rem;
        }
        .alert-error i { flex-shrink:0; }
        footer { position:relative; z-index:1; background:var(--card-bg); border-top:1px solid var(--border); padding:1.25rem 0; text-align:center; color:var(--text-muted); font-size:0.8rem; }
    </style>
</head>
<body>
<div class="bg-glow"></div>
<nav>
    <div class="container">
        <a href="index.php" class="brand">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">🛡️</div>
        <h1 class="auth-title">Selamat Datang!</h1>
        <p class="auth-sub">Masuk ke akun kamu untuk membuat dan memantau laporan.</p>

        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label-custom" for="email">Alamat Email</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope-fill input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control-custom"
                           placeholder="email@kamu.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           autocomplete="email" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom" for="password">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control-custom"
                           placeholder="Password kamu"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-pass" onclick="togglePassword()" id="toggleBtn">
                        <i class="bi bi-eye-fill" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i> Masuk ke Dashboard
            </button>
        </form>

        <div class="divider">atau</div>
        <div class="link-register">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p><strong>Lapor-Sekolah</strong> &copy; <?= date('Y') ?> &bull; SMK Telkom Malang</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function togglePassword() {
    const input   = document.getElementById('password');
    const icon    = document.getElementById('eyeIcon');
    const visible = input.type === 'text';
    input.type  = visible ? 'password' : 'text';
    icon.className = visible ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
}
<?php if ($pesan_url === 'akun_dihapus'): ?>
window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Login Invalid!',
        html: 'Akun Anda telah <strong style="color:#f87171;">dihapus oleh Admin</strong>.<br><span style="font-size:0.85rem;color:#94a3b8;">Hubungi Administrator sekolah jika ada pertanyaan.</span>',
        icon: 'error',
        confirmButtonText: '<i class="bi bi-arrow-left"></i> Kembali ke Halaman Login',
        background: '#1e293b',
        color: '#f1f5f9',
        iconColor: '#ef4444',
        customClass: {
            popup:         'swal-login-popup',
            title:         'swal-login-title',
            confirmButton: 'swal-login-confirm',
        },
        buttonsStyling: false,
    });
});
<?php endif; ?>
</script>

<style>
.swal-login-popup   { border:1px solid #334155!important; border-radius:16px!important; font-family:'Inter',sans-serif!important; box-shadow:0 20px 60px rgba(0,0,0,0.6)!important; }
.swal-login-title   { font-size:1.2rem!important; font-weight:800!important; color:#f1f5f9!important; }
.swal-login-confirm { background:linear-gradient(135deg,#4f46e5,#7c3aed)!important; color:white!important; border:none!important; border-radius:10px!important; padding:0.65rem 1.4rem!important; font-weight:700!important; font-size:0.875rem!important; cursor:pointer!important; display:inline-flex!important; align-items:center!important; gap:0.4rem!important; transition:all 0.2s!important; }
.swal-login-confirm:hover { transform:translateY(-2px)!important; }
</style>
</body>
</html>
