<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pengarsipan_digital";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: {$conn->connect_error}");
}

$logoutMessage = '';
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logoutMessage = '<div class="logout-alert" id="logoutAlert">
                        <i class="material-icons">check_circle</i>
                        Anda telah berhasil logout.
                        <button type="button" onclick="document.getElementById(\'logoutAlert\').style.display=\'none\';">×</button>
                      </div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PT Wifiku Indonesia | Company Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e293b;
            --secondary: #f59e42;
            --accent: #0ea5e9;
            --bg: #f8fafc;
            --white: #fff;
            --gray: #e5e7eb;
            --gray-dark: #64748b;
            --success: #22c55e;
            --error: #ef4444;
            --radius: 18px;
            --shadow: 0 6px 32px rgba(37,99,235,0.08);
            --font-main: 'Inter', sans-serif;
            --font-alt: 'Roboto', sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-main);
            background: var(--bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container {
            width: 92%;
            max-width: 1200px;
            margin: 0 auto;
        }
        /* Navbar */
        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: box-shadow 0.2s;
        }
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }
        .logo img {
            width: 48px; height: 48px; border-radius: 12px; box-shadow: 0 2px 8px rgba(37,99,235,0.10);
        }
        .logo-text {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: 1px;
            letter-spacing: 0.5px;
        }
        .logo-text span {
            color: var(--primary);
            font-weight: 400;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 0;
        }
        .nav-links a {
            color: var(--gray-dark);
            text-decoration: none;
            margin-left: 32px;
            font-weight: 500;
            font-family: var(--font-alt);
            font-size: 1.05rem;
            transition: color 0.2s;
            position: relative;
            padding-bottom: 2px;
        }
        .nav-links a.active, .nav-links a:hover {
            color: var(--primary);
        }
        .nav-links .btn-login-nav {
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--white);
            padding: 10px 28px;
            border-radius: var(--radius);
            margin-left: 38px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(37,99,235,0.10);
            font-size: 1.07rem;
            letter-spacing: 0.5px;
            border: none;
        }
        .nav-links .btn-login-nav:hover {
            background: linear-gradient(90deg, var(--accent) 0%, var(--primary) 100%);
        }
        /* Hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            margin-left: 18px;
        }
        .hamburger span {
            height: 3px;
            width: 28px;
            background: var(--primary-dark);
            margin: 4px 0;
            border-radius: 2px;
            transition: all 0.3s;
        }
        /* Hero */
        .hero {
            background: linear-gradient(120deg, var(--primary) 60%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 90px 0 70px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero .container {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 22px;
            letter-spacing: 1px;
            line-height: 1.18;
        }
        .hero p {
            font-size: 1.22rem;
            margin-bottom: 36px;
            opacity: 0.96;
            font-weight: 500;
        }
        .hero .cta-btn {
            background: var(--secondary);
            color: var(--white);
            font-weight: 700;
            font-size: 1.13rem;
            padding: 15px 44px;
            border: none;
            border-radius: var(--radius);
            box-shadow: 0 2px 12px rgba(245,158,66,0.13);
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            letter-spacing: 0.5px;
        }
        .hero .cta-btn:hover {
            background: #e67e22;
            transform: translateY(-2px) scale(1.03);
        }
        .hero .project-title-hero {
            margin-top: 38px;
            font-size: 1.13rem;
            color: #e0e7ff;
            opacity: 0.88;
            font-weight: 500;
        }
        /* Hero Decorative Circles */
        .hero::before, .hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            opacity: 0.13;
            z-index: 1;
        }
        .hero::before {
            width: 420px; height: 420px;
            background: var(--accent);
            top: -120px; left: -120px;
        }
        .hero::after {
            width: 320px; height: 320px;
            background: var(--secondary);
            bottom: -100px; right: -100px;
        }
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 60px 0 0 0;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 48px;
            align-items: flex-start;
        }
        /* Card Section */
        .section-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 48px;
            padding: 38px 36px 32px 36px;
            transition: box-shadow 0.2s;
        }
        .section-card:hover {
            box-shadow: 0 10px 36px rgba(37,99,235,0.13);
        }
        .section-card h2 {
            font-size: 1.55rem;
            color: var(--primary-dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }
        .section-card .material-icons-outlined {
            color: var(--primary);
            font-size: 1.35em;
        }
        .section-card .desc {
            font-size: 1.09rem;
            color: #334155;
            margin-bottom: 22px;
            line-height: 1.7;
            font-weight: 500;
        }
        /* Visi Misi */
        .visi-misi {
            display: flex;
            gap: 36px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }
        .visi-misi .vm-card {
            background: #f1f5fd;
            border-radius: 12px;
            padding: 22px 24px;
            flex: 1 1 220px;
            min-width: 180px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.04);
        }
        .visi-misi .vm-title {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 9px;
            font-size: 1.08rem;
        }
        .visi-misi ul {
            margin-left: 18px;
            color: #444;
            font-size: 1.01rem;
        }
        /* Layanan */
        .layanan-list {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .layanan-card {
            background: linear-gradient(120deg, #f1f5fd 60%, #e0e7ff 100%);
            border-radius: 12px;
            padding: 28px 22px;
            flex: 1 1 180px;
            min-width: 170px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.06);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 10px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .layanan-card:hover {
            box-shadow: 0 8px 28px rgba(37,99,235,0.10);
            transform: translateY(-3px) scale(1.03);
        }
        .layanan-card .material-icons {
            font-size: 2.3em;
            color: var(--primary);
            margin-bottom: 12px;
        }
        .layanan-card .layanan-title {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 7px;
            font-size: 1.09rem;
        }
        .layanan-card .layanan-desc {
            font-size: 1.01rem;
            color: #444;
            font-weight: 500;
        }
        /* Kontak */
        .kontak-info {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .kontak-item {
            flex: 1 1 180px;
            min-width: 170px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 1.07em;
            color: #334155;
        }
        .kontak-item .material-icons {
            color: var(--primary);
            font-size: 1.5em;
        }
        .kontak-map {
            margin-top: 22px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        /* Login */
        .login-section {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 42px 32px;
            position: sticky;
            top: 110px;
            min-width: 320px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .login-section h3 {
            text-align: center;
            font-size: 1.35rem;
            color: var(--primary-dark);
            margin-bottom: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .error-message {
            color: var(--error);
            background: #fee2e2;
            border-radius: 10px;
            padding: 12px 15px;
            text-align: center;
            margin-bottom: 22px;
            font-size: 1.01rem;
            border: 1px solid #fecaca;
            font-weight: 600;
        }
        .input-group {
            margin-bottom: 22px;
        }
        .input-group label {
            display: block;
            font-weight: 600;
            color: #222;
            margin-bottom: 8px;
            font-size: 1.01rem;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.25em;
        }
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 13px 13px 13px 44px;
            border: 1.7px solid var(--gray);
            border-radius: 10px;
            font-size: 1.05rem;
            background: #f8fafc;
            transition: border-color 0.2s, background 0.2s;
            outline: none;
            font-family: var(--font-main);
        }
        input[type="text"]:focus, input[type="password"]:focus, select:focus {
            border-color: var(--primary);
            background: var(--white);
        }
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' fill='none' stroke='%232563eb' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 13px center;
            background-size: 18px;
            padding-right: 38px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
            color: var(--white);
            font-size: 1.09rem;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg, var(--accent) 0%, var(--primary) 100%);
            transform: translateY(-2px) scale(1.03);
        }
        /* Logout Alert */
        .logout-alert {
            position: fixed;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 340px;
            max-width: 95vw;
            background: var(--success);
            color: var(--white);
            border-radius: 10px;
            padding: 15px 26px;
            box-shadow: 0 8px 32px rgba(34,197,94,0.13);
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            animation: fadeInDown 0.5s ease forwards;
        }
        .logout-alert i {
            margin-right: 12px;
            font-size: 1.4rem;
        }
        .logout-alert button {
            background: none;
            border: none;
            margin-left: auto;
            font-size: 1.6em;
            color: var(--white);
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .logout-alert button:hover { opacity: 1; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -20px);}
            to { opacity: 1; transform: translate(-50%, 0);}
        }
        /* Footer */
        .footer {
            background: var(--primary-dark);
            color: #e0e7ff;
            text-align: center;
            padding: 38px 0 26px 0;
            font-size: 1.01rem;
            margin-top: 60px;
        }
        .footer a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .footer a:hover { text-decoration: underline; color: var(--accent);}
        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid { grid-template-columns: 1.3fr 1fr; }
        }
        @media (max-width: 950px) {
            .content-grid { grid-template-columns: 1fr; }
            .login-section { margin-top: 38px; position: static; }
        }
        @media (max-width: 800px) {
            .navbar .container { flex-direction: row; }
            .nav-links { display: none; flex-direction: column; background: var(--white); position: absolute; top: 70px; right: 0; width: 220px; box-shadow: 0 8px 32px rgba(37,99,235,0.10); border-radius: 0 0 0 18px; padding: 18px 0;}
            .nav-links.open { display: flex; }
            .nav-links a { margin: 0 0 18px 0; padding: 0 28px;}
            .nav-links .btn-login-nav { margin-left: 0; }
            .hamburger { display: flex; }
        }
        @media (max-width: 700px) {
            .navbar .container { flex-direction: row; align-items: center; gap: 0;}
            .hero { padding: 54px 0 38px 0;}
            .hero h1 { font-size: 2rem;}
            .section-card, .login-section { padding: 18px 10px;}
            .visi-misi, .layanan-list, .kontak-info { flex-direction: column; gap: 14px;}
            .login-section { min-width: unset; max-width: 100%; }
        }
        @media (max-width: 480px) {
            .logo-text { font-size: 1.08rem;}
            .hero h1 { font-size: 1.3rem;}
            .footer { font-size: 0.93rem;}
            .logout-alert { min-width: 90%; font-size: 0.93rem; padding: 10px 10px; top: 70px;}
            .section-card, .login-section { padding: 10px 4px;}
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">
                <img src="images/wifiku.png" alt="PT Wifiku Indonesia Logo">
                <span class="logo-text">Wifiku<span>Indonesia</span></span>
            </a>
            <div class="hamburger" id="hamburgerBtn" aria-label="Menu" tabindex="0">
                <span></span><span></span><span></span>
            </div>
            <div class="nav-links" id="navLinks">
                <a href="#about">Tentang Kami</a>
                <a href="#layanan">Layanan</a>
                <a href="#kontak">Kontak</a>
                <a href="#login-form" class="btn-login-nav">Login</a>
            </div>
        </div>
    </nav>
    <?= $logoutMessage ?>

    <header class="hero">
        <div class="container">
            <h1>Optimalisasi Manajemen Informasi Dokumen Teknis Jaringan</h1>
            <p>Solusi Pengarsipan Digital Berbasis Web Terdepan dari PT Wifiku Indonesia.</p>
            <a href="#layanan" class="cta-btn">Lihat Layanan Kami</a>
            <div class="project-title-hero">
                Pengembangan Sistem Pengarsipan Digital Dokumen Teknis Jaringan Berbasis Web untuk Optimalisasi Manajemen Informasi
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <section>
                    <div class="section-card" id="about">
                        <h2><span class="material-icons-outlined">corporate_fare</span> Tentang Kami</h2>
                        <div class="desc">
                            <b>PT Wifiku Indonesia</b> adalah perusahaan nasional yang bergerak di bidang layanan jaringan dan teknologi informasi. Kami berfokus pada pengembangan solusi digital inovatif untuk mendukung efisiensi, keamanan, dan optimalisasi pengelolaan dokumen teknis jaringan. Dengan tim profesional dan teknologi terkini, kami berkomitmen memberikan layanan terbaik untuk kebutuhan digitalisasi dan manajemen informasi perusahaan Anda.
                        </div>
                        <div class="visi-misi">
                            <div class="vm-card">
                                <div class="vm-title"><span class="material-icons-outlined">visibility</span> Visi</div>
                                <div>Menjadi pelopor solusi digital terintegrasi untuk pengelolaan dokumen teknis jaringan di Indonesia.</div>
                            </div>
                            <div class="vm-card">
                                <div class="vm-title"><span class="material-icons-outlined">flag</span> Misi</div>
                                <ul>
                                    <li>Menghadirkan sistem pengarsipan digital yang aman, mudah diakses, dan efisien.</li>
                                    <li>Mendukung transformasi digital perusahaan melalui inovasi teknologi.</li>
                                    <li>Memberikan layanan prima dan solusi terbaik bagi seluruh stakeholder.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="section-card" id="layanan">
                        <h2><span class="material-icons-outlined">miscellaneous_services</span> Layanan Kami</h2>
                        <div class="layanan-list">
                            <div class="layanan-card">
                                <span class="material-icons">cloud_upload</span>
                                <div class="layanan-title">Pengarsipan Digital</div>
                                <div class="layanan-desc">Sistem penyimpanan dokumen teknis berbasis cloud yang aman dan mudah diakses kapan saja.</div>
                            </div>
                            <div class="layanan-card">
                                <span class="material-icons">security</span>
                                <div class="layanan-title">Keamanan Data</div>
                                <div class="layanan-desc">Proteksi dokumen dengan enkripsi dan kontrol akses multi-level.</div>
                            </div>
                            <div class="layanan-card">
                                <span class="material-icons">search</span>
                                <div class="layanan-title">Pencarian Cepat</div>
                                <div class="layanan-desc">Fitur pencarian dokumen secara instan berdasarkan kategori, tanggal, atau kata kunci.</div>
                            </div>
                            <div class="layanan-card">
                                <span class="material-icons">group</span>
                                <div class="layanan-title">Manajemen User</div>
                                <div class="layanan-desc">Pengelolaan hak akses dan monitoring aktivitas pengguna secara real-time.</div>
                            </div>
                        </div>
                    </div>
                    <div class="section-card" id="kontak">
                        <h2><span class="material-icons-outlined">contact_mail</span> Kontak</h2>
                        <div class="kontak-info">
                            <div class="kontak-item">
                                <span class="material-icons">location_on</span>
                                <span>Ruko Mutiara Taman Palem Blok - A8 No.1 Cengkareng - Jakarta 11730</span>
                            </div>
                            <div class="kontak-item">
                                <span class="material-icons">email</span>
                                <span>info@wifiku.co.id</span>
                            </div>
                            <div class="kontak-item">
                                <span class="material-icons">phone</span>
                                <span>+62 21 1234 5678</span>
                            </div>
                        </div>
                        <div class="kontak-map">
                            <iframe src="https://www.google.com/maps?q=Jakarta,Indonesia&output=embed" width="100%" height="180" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </section>
                <aside class="login-section" id="login-form">
                    <h3>Login Akun</h3>
                    <?php
                    if (isset($_SESSION['error'])) {
                        echo "<div class='error-message'>".htmlspecialchars($_SESSION['error'])."</div>";
                        unset($_SESSION['error']);
                    }
                    ?>
                    <form action="login.php" method="post" autocomplete="off">
                        <div class="input-group">
                            <label for="username">Username</label>
                            <div class="input-wrapper">
                                <span class="material-icons input-icon">person_outline</span>
                                <input type="text" name="username" id="username" required placeholder="Masukkan username Anda">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <span class="material-icons input-icon">lock_outline</span>
                                <input type="password" name="password" id="password" required placeholder="Masukkan password">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="jabatan">Jabatan</label>
                            <div class="input-wrapper">
                                <span class="material-icons input-icon">badge_outline</span>
                                <select name="jabatan" id="jabatan" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    <option value="1">Administrator Sistem</option>
                                    <option value="2">Manajer Jaringan</option>
                                    <option value="3">Staf Teknisi Jaringan</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit">Login Sistem</button>
                    </form>
                </aside>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container" style="display: flex; flex-direction: column; align-items: center; gap: 22px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="images/wifiku.png" alt="PT Wifiku Indonesia Logo" style="width:40px;height:40px;border-radius:10px;box-shadow:0 2px 8px rgba(52,152,219,0.10);background:#fff;">
                <span style="font-size:1.18rem;font-weight:700;color:#fff;letter-spacing:0.5px;">
                    PT Wifiku <span style="font-weight:400;color:#f59e42;">Indonesia</span>
                </span>
            </div>
            <div style="color:#e0e7ff;font-size:1.01rem;text-align:center;">
                © <?= date('Y') ?> PT Wifiku Indonesia — All Rights Reserved.<br>
                <span style="color:#f59e42;">Pengembangan Sistem Pengarsipan Digital</span>
            </div>
            <div style="display:flex;gap:18px;">
                <a href="#">Privacy Policy</a>
                <span style="color:#7f8c8d;">|</span>
                <a href="#">Terms of Service</a>
            </div>
            <div style="margin-top:10px;">
                <span style="font-size:1.2em;color:#22c55e;vertical-align:middle;" class="material-icons-outlined">verified_user</span>
                <span style="color:#e0e7ff;font-size:0.97em;">Your Data is Secure</span>
            </div>
        </div>
    </footer>
    <script>
        // Hamburger menu
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const navLinks = document.getElementById('navLinks');
        hamburgerBtn && hamburgerBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
        hamburgerBtn && hamburgerBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') navLinks.classList.toggle('open');
        });
        // Close nav on link click (mobile)
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 800) navLinks.classList.remove('open');
            });
        });
        // Auto-hide logout alert
        const logoutAlert = document.getElementById('logoutAlert');
        if (logoutAlert) {
            setTimeout(() => {
                logoutAlert.style.transition = 'opacity 0.5s ease';
                logoutAlert.style.opacity = '0';
                setTimeout(() => logoutAlert.style.display = 'none', 500);
            }, 5000);
        }
        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    let offset = 0;
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    offset = navbarHeight + (targetId === '#login-form' ? 20 : 0);
                    const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                    const offsetPosition = elementPosition - offset;
                    window.scrollTo({ top: offsetPosition, behavior: "smooth" });
                }
            });
        });
    </script>
</body>
</html>