<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <title>404 — Halaman Tidak Ditemukan | Lapor-Sekolah</title>
    <meta name="description" content="Halaman yang Anda cari tidak ditemukan di Lapor-Sekolah.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:       #4f46e5;
            --primary-dark:  #3730a3;
            --primary-light: #818cf8;
            --violet:        #7c3aed;
            --pink:          #c084fc;
            --dark-bg:       #0f172a;
            --card-bg:       #1e293b;
            --border:        #334155;
            --text-primary:  #f1f5f9;
            --text-muted:    #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ── Ambient Background Glow ── */
        .bg-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 20% 20%, rgba(79,70,229,0.12), transparent),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(124,58,237,0.10), transparent),
                radial-gradient(ellipse 50% 60% at 50% 50%, rgba(192,132,252,0.05), transparent);
        }

        /* ── Navbar ── */
        .navbar-custom {
            background: rgba(15,23,42,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-brand-custom {
            font-size: 1.3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-light), var(--pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        .navbar-brand-custom span { -webkit-text-fill-color: var(--text-primary); }

        /* ── Main ── */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
            padding: 3rem 1.5rem;
        }

        .error-wrapper {
            text-align: center;
            max-width: 520px;
            width: 100%;
        }

        /* ── Glitchy 404 Number ── */
        .error-code {
            font-size: clamp(6rem, 18vw, 10rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--pink) 50%, var(--primary-light) 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 4s ease-in-out infinite;
            position: relative;
            display: inline-block;
            user-select: none;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50%       { background-position: 100% 50%; }
        }

        /* ── Floating Glitch Lines ── */
        .error-code::before,
        .error-code::after {
            content: '404';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            font-size: inherit;
            font-weight: inherit;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            -webkit-background-clip: text;
            opacity: 0;
        }
        .error-code::before {
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            background-clip: text;
            animation: glitch1 5s infinite;
        }
        .error-code::after {
            background: linear-gradient(135deg, #06b6d4, #818cf8);
            -webkit-background-clip: text;
            background-clip: text;
            animation: glitch2 5s infinite;
        }
        @keyframes glitch1 {
            0%, 90%, 100% { opacity: 0; transform: translate(0); }
            92%            { opacity: 0.6; transform: translate(-4px, 2px) skewX(-2deg); }
            94%            { opacity: 0.4; transform: translate(3px, -1px) skewX(1deg); }
            96%            { opacity: 0; transform: translate(0); }
        }
        @keyframes glitch2 {
            0%, 88%, 100% { opacity: 0; transform: translate(0); }
            90%            { opacity: 0.5; transform: translate(4px, -2px) skewX(2deg); }
            93%            { opacity: 0.3; transform: translate(-2px, 1px); }
            95%            { opacity: 0; transform: translate(0); }
        }

        /* ── Floating Orb ── */
        .orb {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, rgba(129,140,248,0.25), rgba(124,58,237,0.12), transparent 70%);
            border: 1px solid rgba(129,140,248,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: orbFloat 6s ease-in-out infinite;
            position: relative;
        }
        .orb::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1px dashed rgba(129,140,248,0.15);
            animation: orbSpin 20s linear infinite;
        }
        .orb i {
            font-size: 3.2rem;
            background: linear-gradient(135deg, var(--primary-light), var(--pink));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes orbFloat {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-12px); }
        }
        @keyframes orbSpin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* ── Badge ── */
        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            color: #fca5a5;
            border-radius: 50px;
            padding: 0.3rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 1.25rem;
        }

        /* ── Text ── */
        .error-title {
            font-size: clamp(1.3rem, 3vw, 1.75rem);
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }
        .error-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.75;
            margin-bottom: 2.25rem;
        }
        .error-desc strong {
            color: var(--primary-light);
        }

        /* ── Divider ── */
        .error-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--pink));
            border-radius: 99px;
            margin: 0 auto 1.5rem;
        }

        /* ── Buttons ── */
        .btn-group-error {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            background: linear-gradient(135deg, var(--primary), var(--violet));
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.75rem;
            font-weight: 700;
            font-size: 0.925rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s;
            box-shadow: 0 4px 18px rgba(79,70,229,0.35);
        }
        .btn-home:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(79,70,229,0.55);
        }
        .btn-back {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1.75rem;
            font-weight: 600;
            font-size: 0.925rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s;
        }
        .btn-back:hover {
            color: var(--text-primary);
            background: rgba(255,255,255,0.09);
            border-color: rgba(255,255,255,0.15);
        }

        /* ── Grid card hint ── */
        .hint-card {
            margin-top: 3rem;
            background: rgba(255,255,255,0.025);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            text-align: left;
        }
        .hint-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .hint-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .hint-link {
            background: rgba(79,70,229,0.1);
            border: 1px solid rgba(79,70,229,0.25);
            color: var(--primary-light);
            border-radius: 8px;
            padding: 0.3rem 0.8rem;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s;
        }
        .hint-link:hover {
            background: rgba(79,70,229,0.22);
            color: #c4b5fd;
        }

        /* ── Footer ── */
        footer {
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            padding: 1.4rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.82rem;
            position: relative;
            z-index: 1;
        }

        /* ── Particles ── */
        .particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            animation: particleDrift linear infinite;
        }
        @keyframes particleDrift {
            0%   { opacity: 0; transform: translateY(100vh) scale(0); }
            10%  { opacity: 1; }
            90%  { opacity: 0.4; }
            100% { opacity: 0; transform: translateY(-10vh) scale(1); }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>

<!-- Floating Particles -->
<div class="particles" id="particles"></div>

<!-- Navbar -->
<nav class="navbar-custom" style="position:relative;z-index:100;">
    <div class="container d-flex align-items-center" style="display:flex;align-items:center;max-width:1200px;margin:0 auto;padding:0 1rem;">
        <a class="navbar-brand-custom" href="/">
            <i class="bi bi-shield-exclamation" style="-webkit-text-fill-color:#818cf8;margin-right:0.3rem;"></i>Lapor<span>-Sekolah</span>
        </a>
    </div>
</nav>

<!-- Main Content -->
<main>
    <div class="error-wrapper">

        <!-- Floating Icon Orb -->
        <div class="orb">
            <i class="bi bi-compass"></i>
        </div>

        <!-- Error Code -->
        <div class="error-code">404</div>

        <!-- Spacer -->
        <br>

        <!-- Badge -->
        <div class="error-badge">
            <i class="bi bi-x-circle-fill"></i>
            Halaman Tidak Ditemukan
        </div>

        <!-- Divider -->
        <div class="error-divider"></div>

        <!-- Title & Description -->
        <h1 class="error-title">Oops! Jalur ini Buntu</h1>
        <p class="error-desc">
            Halaman yang Anda cari <strong>tidak ditemukan</strong> atau mungkin telah
            dipindahkan ke lokasi lain. Periksa kembali URL yang Anda ketikkan,
            atau gunakan tautan di bawah untuk kembali ke jalur yang benar.
        </p>

        <!-- CTA Buttons -->
        <div class="btn-group-error">
            <a href="/" class="btn-home">
                <i class="bi bi-house-door-fill"></i> Kembali ke Beranda
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left"></i> Halaman Sebelumnya
            </a>
        </div>

        <!-- Quick Links Hint -->
        <div class="hint-card">
            <div class="hint-title">
                <i class="bi bi-signpost-split"></i> Atau langsung ke halaman ini
            </div>
            <div class="hint-links">
                <a href="/" class="hint-link">
                    <i class="bi bi-globe"></i> Beranda
                </a>
                <a href="/login" class="hint-link">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
                <a href="/register" class="hint-link">
                    <i class="bi bi-person-plus-fill"></i> Daftar
                </a>
                <a href="/dashboard" class="hint-link">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </div>
        </div>

    </div>
</main>

<!-- Footer -->
<footer>
    <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1rem;">
        <p>
            <i class="bi bi-shield-check" style="color:#818cf8;margin-right:0.25rem;"></i>
            <strong>Lapor-Sekolah</strong> &mdash; Portal Pelaporan Fasilitas &copy; <?= date('Y') ?>
        </p>
    </div>
</footer>

<script>
// Generate floating particles
(function () {
    const container = document.getElementById('particles');
    const colors = ['rgba(129,140,248,', 'rgba(192,132,252,', 'rgba(79,70,229,'];
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.classList.add('particle');
        const size = Math.random() * 5 + 2;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const opacity = (Math.random() * 0.4 + 0.15).toFixed(2);
        p.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${Math.random() * 100}%;
            background: ${color}${opacity});
            animation-duration: ${Math.random() * 12 + 10}s;
            animation-delay: ${Math.random() * 8}s;
        `;
        container.appendChild(p);
    }
})();
</script>

</body>
</html>
