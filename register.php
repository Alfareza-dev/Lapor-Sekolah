<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

require_once 'config/koneksi.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password = trim($_POST['password']         ?? '');
    $konfirm  = trim($_POST['konfirm_password'] ?? '');

    if (empty($nama))     $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($email))    $errors[] = 'Email wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirm) $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $email_esc = mysqli_real_escape_string($koneksi, $email);
        $cek = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email_esc' LIMIT 1");

        if (mysqli_num_rows($cek) > 0) {
            $errors[] = 'Email sudah terdaftar. Gunakan email lain atau langsung masuk.';
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $nama_esc  = mysqli_real_escape_string($koneksi, $nama);
            $hash_esc  = mysqli_real_escape_string($koneksi, $pass_hash);

            $sql = "INSERT INTO users (nama, email, password, role)
                    VALUES ('$nama_esc', '$email_esc', '$hash_esc', 'user')";

            if (mysqli_query($koneksi, $sql)) {
                $success = true;
                $new_id = mysqli_insert_id($koneksi);
                $_SESSION['user_id'] = $new_id;
                $_SESSION['nama']    = $nama;
                $_SESSION['email']   = $email;
                $_SESSION['role']    = 'user';
                session_regenerate_id(true);

                header('Location: /dashboard?pesan=register_sukses');
                exit;
            } else {
                $errors[] = 'Gagal menyimpan akun: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>Daftar Akun | Lapor-Sekolah</title>
    <meta name="description" content="Buat akun baru di Lapor-Sekolah untuk mulai melaporkan kerusakan fasilitas.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:#4f46e5; --primary-light:#818cf8; --violet:#7c3aed;
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
            background:radial-gradient(ellipse 70% 60% at 85% 30%, rgba(124,58,237,0.1) 0%, transparent 60%),
                        radial-gradient(ellipse 50% 50% at 15% 70%, rgba(79,70,229,0.08) 0%, transparent 55%);
        }
        nav {
            position:relative; z-index:10;
            background:rgba(15,23,42,0.8); backdrop-filter:blur(20px);
            border-bottom:1px solid rgba(51,65,85,0.5); padding:1rem 0;
        }
        .brand {
            font-size:1.4rem; font-weight:900;
            background:linear-gradient(135deg,#818cf8,#c084fc);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; text-decoration:none;
        }
        .brand span { -webkit-text-fill-color:var(--text-primary); }
        .auth-container { flex:1; display:flex; align-items:center; justify-content:center; padding:clamp(1.5rem, 5vw, 2.5rem) 1rem; position:relative; z-index:1; }
        .auth-card { background:var(--card-bg); border:1px solid var(--border); border-radius:20px; padding:clamp(1.5rem, 5vw, 2.5rem); width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.4); }
        .auth-logo { width:56px; height:56px; background:linear-gradient(135deg,var(--violet),#c084fc); border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin-bottom:1.5rem; box-shadow:0 8px 20px rgba(124,58,237,0.4); }
        .auth-title { font-size:1.6rem; font-weight:800; margin-bottom:0.4rem; }
        .auth-sub   { color:var(--text-muted); font-size:0.9rem; margin-bottom:2rem; }
        .form-group { margin-bottom:1.1rem; }
        .form-label-custom { display:block; font-weight:600; font-size:0.85rem; color:var(--primary-light); margin-bottom:0.4rem; }
        .input-wrapper { position:relative; }
        .input-wrapper i.input-icon { position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:1rem; pointer-events:none; }
        .form-control-custom {
            width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border);
            border-radius:10px; padding:0.75rem 1rem 0.75rem 2.75rem;
            color:var(--text-primary); font-size:0.9rem; font-family:'Inter',sans-serif;
            transition:border-color 0.2s, box-shadow 0.2s; outline:none;
        }
        .form-control-custom::placeholder { color:var(--text-muted); }
        .form-control-custom:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(79,70,229,0.15); background:rgba(79,70,229,0.05); }
        .toggle-pass { position:absolute; right:1rem; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--text-muted); transition:color 0.2s; background:none; border:none; padding:0; }
        .toggle-pass:hover { color:var(--primary-light); }
        .strength-bar { margin-top:0.5rem; }
        .strength-track { height:4px; background:rgba(255,255,255,0.08); border-radius:2px; overflow:hidden; }
        .strength-fill  { height:100%; border-radius:2px; transition:width 0.3s, background 0.3s; width:0%; }
        .strength-label { font-size:0.72rem; margin-top:0.3rem; }
        .btn-submit {
            width:100%; background:linear-gradient(135deg,var(--violet),#c084fc);
            color:white; border:none; border-radius:10px;
            padding:0.85rem; font-weight:700; font-size:1rem;
            cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;
            transition:all 0.25s; box-shadow:0 4px 15px rgba(124,58,237,0.35); margin-top:0.5rem;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(124,58,237,0.5); }
        .divider { display:flex; align-items:center; gap:1rem; margin:1.5rem 0; color:var(--text-muted); font-size:0.8rem; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border); }
        .link-login { text-align:center; font-size:0.9rem; color:var(--text-muted); }
        .link-login a { color:var(--primary-light); font-weight:600; text-decoration:none; }
        .link-login a:hover { text-decoration:underline; }
        .alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.35); border-radius:10px; padding:0.85rem 1rem; margin-bottom:1.25rem; color:#fca5a5; font-size:0.875rem; }
        .alert-error ul { margin:0.4rem 0 0; padding-left:1.2rem; }
        .alert-error li { margin-bottom:0.2rem; }
        footer { position:relative; z-index:1; background:var(--card-bg); border-top:1px solid var(--border); padding:1.25rem 0; text-align:center; color:var(--text-muted); font-size:0.8rem; }
    </style>
</head>
<body>
<div class="bg-glow"></div>
<nav>
    <div class="container">
        <a href="/" class="brand">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">🚀</div>
        <h1 class="auth-title">Buat Akun Baru</h1>
        <p class="auth-sub">Daftar gratis dan mulai laporkan kerusakan fasilitas sekolah.</p>

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Perbaiki kesalahan berikut:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label class="form-label-custom" for="nama">Nama Lengkap</label>
                <div class="input-wrapper">
                    <i class="bi bi-person-fill input-icon"></i>
                    <input type="text" id="nama" name="nama" class="form-control-custom"
                           placeholder="Nama lengkap kamu"
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                           maxlength="100" autocomplete="name">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom" for="email">Alamat Email</label>
                <div class="input-wrapper">
                    <i class="bi bi-envelope-fill input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control-custom"
                           placeholder="email@kamu.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom" for="password">Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" id="password" name="password" class="form-control-custom"
                           placeholder="Minimal 6 karakter"
                           autocomplete="new-password"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="toggle-pass" onclick="togglePass('password','eye1')">
                        <i class="bi bi-eye-fill" id="eye1"></i>
                    </button>
                </div>
                <div class="strength-bar">
                    <div class="strength-track"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel" style="color:var(--text-muted);"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom" for="konfirm_password">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" id="konfirm_password" name="konfirm_password" class="form-control-custom"
                           placeholder="Ulangi password kamu"
                           autocomplete="new-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('konfirm_password','eye2')">
                        <i class="bi bi-eye-fill" id="eye2"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-person-check-fill"></i> Buat Akun Sekarang
            </button>
        </form>

        <div class="divider">atau</div>
        <div class="link-login">
            Sudah punya akun? <a href="/login">Masuk di sini</a>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p><strong>Lapor-Sekolah</strong> &copy; <?= date('Y') ?> &bull; SMK Telkom Malang</p>
    </div>
</footer>

<script>
function togglePass(inputId, iconId) {
    const input   = document.getElementById(inputId);
    const icon    = document.getElementById(iconId);
    const visible = input.type === 'text';
    input.type    = visible ? 'password' : 'text';
    icon.className = visible ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
}
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { pct: 0,   color: '',        text: '' },
        { pct: 20,  color: '#ef4444', text: '😰 Sangat Lemah' },
        { pct: 40,  color: '#f59e0b', text: '😐 Lemah' },
        { pct: 60,  color: '#eab308', text: '🙂 Cukup' },
        { pct: 80,  color: '#22c55e', text: '😎 Kuat' },
        { pct: 100, color: '#10b981', text: '🔒 Sangat Kuat' },
    ];
    const lv = levels[score];
    fill.style.width      = lv.pct + '%';
    fill.style.background = lv.color;
    label.style.color     = lv.color;
    label.textContent     = lv.text;
}
</script>
</body>
</html>
