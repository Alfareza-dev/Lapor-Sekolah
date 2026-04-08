<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>Lapor-Sekolah | Portal Pelaporan Fasilitas</title>
    <meta name="description" content="Portal digital pelaporan kerusakan fasilitas sekolah. Laporkan dengan mudah, pantau prosesnya secara real-time.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5; --primary-light: #818cf8; --primary-dark: #3730a3;
            --violet: #7c3aed; --pink: #c084fc;
            --dark-bg: #0f172a; --card-bg: #1e293b; --border: #334155;
            --text-primary: #f1f5f9; --text-muted: #94a3b8;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        .bg-glow {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(79,70,229,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 70%, rgba(124,58,237,0.10) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 50% 90%, rgba(192,132,252,0.07) 0%, transparent 50%);
        }
        .navbar-custom {
            position: relative; z-index: 10;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
            padding: 1rem 0;
        }
        .navbar-brand-custom {
            font-size: 1.5rem; font-weight: 900;
            background: linear-gradient(135deg, var(--primary-light), var(--pink));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; text-decoration: none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color: var(--text-primary); }
        .hero {
            position: relative; z-index: 1;
            min-height: calc(100vh - 73px);
            display: flex; align-items: center;
            padding: 4rem 0;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(79,70,229,0.15);
            border: 1px solid rgba(79,70,229,0.35);
            color: var(--primary-light);
            padding: 0.4rem 1rem; border-radius: 50px;
            font-size: 0.8rem; font-weight: 700;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease both;
        }
        .hero-eyebrow i { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900; line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
            animation: fadeInUp 0.7s 0.1s ease both;
        }
        .hero-title .gradient-text {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--pink) 60%, #f472b6 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-subtitle {
            font-size: 1.1rem; color: var(--text-muted); line-height: 1.7;
            max-width: 520px; margin-bottom: 2.5rem;
            animation: fadeInUp 0.7s 0.2s ease both;
        }
        .hero-cta {
            display: flex; gap: 1rem; flex-wrap: wrap;
            animation: fadeInUp 0.7s 0.3s ease both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .btn-primary-custom {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--violet));
            color: white; text-decoration: none;
            padding: 0.85rem 2rem; border-radius: 12px;
            font-weight: 700; font-size: 1rem;
            transition: all 0.25s;
            box-shadow: 0 4px 20px rgba(79,70,229,0.4);
        }
        .btn-primary-custom:hover {
            color: white; transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(79,70,229,0.6);
        }
        .btn-outline-custom {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.06);
            color: var(--text-primary); text-decoration: none;
            padding: 0.85rem 2rem; border-radius: 12px;
            font-weight: 600; font-size: 1rem;
            border: 1px solid var(--border);
            transition: all 0.25s;
        }
        .btn-outline-custom:hover {
            color: var(--text-primary);
            background: rgba(255,255,255,0.1);
            border-color: var(--primary-light);
            transform: translateY(-2px);
        }
        .hero-visual {
            position: relative;
            animation: fadeInUp 0.8s 0.3s ease both;
        }
        .preview-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .preview-card-header {
            display: flex; align-items: center; gap: 0.6rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .preview-row {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 10px; padding: 0.85rem 1rem;
            margin-bottom: 0.6rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 0.85rem;
        }
        .preview-row:last-child { margin-bottom: 0; }
        .preview-avatar {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0;
        }
        .preview-badge {
            margin-left: auto; padding: 0.2rem 0.6rem;
            border-radius: 50px; font-size: 0.7rem; font-weight: 700;
        }
        .badge-y { background:rgba(245,158,11,0.15); color:#fcd34d; border:1px solid rgba(245,158,11,0.3); }
        .badge-b { background:rgba(59,130,246,0.15);  color:#93c5fd; border:1px solid rgba(59,130,246,0.3); }
        .badge-g { background:rgba(16,185,129,0.15);  color:#6ee7b7; border:1px solid rgba(16,185,129,0.3); }
        .floating-badge {
            position: absolute;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 0.6rem 1rem;
            font-size: 0.8rem; font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            white-space: nowrap;
            animation: floatBadge 3s ease-in-out infinite;
        }
        @keyframes floatBadge {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        .fb-1 { top: -18px; right: 20px; animation-delay: 0s; }
        .fb-2 { bottom: -14px; left: 10px; animation-delay: 1.5s; }
        .features-section {
            position: relative; z-index: 1;
            padding: 5rem 0;
            border-top: 1px solid var(--border);
        }
        .section-label {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: var(--primary-light); margin-bottom: 0.5rem;
        }
        .section-title { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; margin-bottom: 0.5rem; }
        .section-sub   { color: var(--text-muted); font-size: 1rem; }
        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px; padding: 1.75rem;
            height: 100%;
            transition: border-color 0.25s, transform 0.25s;
        }
        .feature-card:hover {
            border-color: rgba(79,70,229,0.5);
            transform: translateY(-4px);
        }
        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 1rem;
        }
        .feature-card h5 { font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem; }
        .feature-card p  { font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; margin: 0; }
        footer {
            position: relative; z-index: 1;
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            padding: 1.75rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<div class="bg-glow"></div>
<nav class="navbar-custom">
    <div class="container d-flex align-items-center justify-content-between">
        <a class="navbar-brand-custom" href="/">
            <i class="bi bi-shield-exclamation me-1" style="-webkit-text-fill-color:#818cf8;"></i>Lapor<span>-Sekolah</span>
        </a>
        <div class="d-flex gap-2">
            <a href="/login" class="btn-outline-custom py-2 px-3" style="font-size:0.875rem;">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </a>
            <a href="/register" class="btn-primary-custom py-2 px-3" style="font-size:0.875rem;">
                <i class="bi bi-person-plus-fill"></i> Daftar
            </a>
        </div>
    </div>
</nav>
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-eyebrow">
                    <i class="bi bi-broadcast-pin"></i> Platform Aktif — SMK Telkom Malang
                </div>
                <h1 class="hero-title">
                    Laporkan Kerusakan<br>
                    <span class="gradient-text">Fasilitas Sekolah</span>
                </h1>
                <p class="hero-subtitle">
                    Portal digital untuk melaporkan kerusakan fasilitas sekolah dengan cepat, mudah, dan transparan. Setiap laporan langsung ditindaklanjuti.
                </p>
                <div class="hero-cta">
                    <a href="/register" class="btn-primary-custom">
                        <i class="bi bi-rocket-takeoff-fill"></i> Mulai Lapor Sekarang
                    </a>
                    <a href="/login" class="btn-outline-custom">
                        <i class="bi bi-person-circle"></i> Sudah Punya Akun
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual">
                    <span class="floating-badge fb-1">
                        <i class="bi bi-check-circle-fill me-1" style="color:#6ee7b7;"></i> 3 Laporan Selesai Hari Ini
                    </span>
                    <span class="floating-badge fb-2">
                        <i class="bi bi-lightning-charge-fill me-1" style="color:#fcd34d;"></i> Respon Cepat
                    </span>
                    <div class="preview-card">
                        <div class="preview-card-header">
                            <div class="dot" style="background:#ef4444;"></div>
                            <div class="dot" style="background:#f59e0b;"></div>
                            <div class="dot" style="background:#10b981;"></div>
                            <span style="margin-left:0.5rem;font-size:0.8rem;color:var(--text-muted);">Dashboard Laporan</span>
                        </div>
                        <div class="preview-row">
                            <div class="preview-avatar" style="background:rgba(239,68,68,0.15);">🚽</div>
                            <div><div style="font-weight:600;font-size:0.85rem;">Toilet Lantai 2</div><div style="font-size:0.75rem;color:var(--text-muted);">Kran bocor — Budi S.</div></div>
                            <span class="preview-badge badge-y">Menunggu</span>
                        </div>
                        <div class="preview-row">
                            <div class="preview-avatar" style="background:rgba(59,130,246,0.15);">💻</div>
                            <div><div style="font-weight:600;font-size:0.85rem;">Lab Komputer A</div><div style="font-size:0.75rem;color:var(--text-muted);">PC #7 mati — Siti R.</div></div>
                            <span class="preview-badge badge-b">Diproses</span>
                        </div>
                        <div class="preview-row">
                            <div class="preview-avatar" style="background:rgba(16,185,129,0.15);">🏀</div>
                            <div><div style="font-weight:600;font-size:0.85rem;">Lapangan Basket</div><div style="font-size:0.75rem;color:var(--text-muted);">Ring miring — Ahmad F.</div></div>
                            <span class="preview-badge badge-g">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Kenapa Lapor-Sekolah?</div>
            <h2 class="section-title">Semua yang Kamu Butuhkan</h2>
            <p class="section-sub">Fitur lengkap untuk pelaporan yang efektif dan transparan.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(79,70,229,0.15);">
                        <i class="bi bi-send-fill" style="color:#818cf8;"></i>
                    </div>
                    <h5>Lapor Kilat</h5>
                    <p>Buat laporan dalam hitungan detik. Isi form sederhana, lampirkan foto bukti, dan kirim. Selesai!</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(16,185,129,0.15);">
                        <i class="bi bi-graph-up-arrow" style="color:#6ee7b7;"></i>
                    </div>
                    <h5>Pantau Status Real-time</h5>
                    <p>Lacak status laporanmu dari Menunggu → Diproses → Selesai secara transparan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(245,158,11,0.15);">
                        <i class="bi bi-shield-lock-fill" style="color:#fcd34d;"></i>
                    </div>
                    <h5>Aman & Terproteksi</h5>
                    <p>Login aman dengan enkripsi password bcrypt. Setiap akun hanya bisa akses datanya sendiri.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<footer>
    <div class="container">
        <p>
            <i class="bi bi-shield-check me-1" style="color:var(--primary-light);"></i>
            <strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?>
        </p>
        <p class="mt-1" style="font-size:0.75rem;opacity:0.6;">SMK Telkom Malang &bull; PHP Native &amp; Bootstrap 5</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
