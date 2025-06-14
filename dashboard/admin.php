<?php
session_start();
$toast_message = $_SESSION['toast_message'] ?? null;
$toast_type = $_SESSION['toast_type'] ?? 'info';
unset($_SESSION['toast_message'], $_SESSION['toast_type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pengarsipan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --accent: #fbbf24;
            --bg: #f4f7fb;
            --glass: rgba(255,255,255,0.92);
            --shadow: 0 8px 32px rgba(30,41,59,0.10);
            --radius: 18px;
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Inter', 'Montserrat', Arial, sans-serif;
            background: linear-gradient(120deg, #e3eafc 0%, #f4f7fb 100%);
            min-height: 100vh;
            color: #1e293b;
        }
        .glass {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(120deg, #f7faff 0%, #e3eafc 100%);
            border-right: 1px solid #e5e7eb;
            padding: 2rem 1.2rem;
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-direction: column;
            transition: all .3s;
        }
        .sidebar .profile-img {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(30,41,59,0.10);
            margin-bottom: 0.7rem;
            border: 3px solid var(--primary);
        }
        .sidebar .nav-link {
            color: #334155;
            font-weight: 500;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: background .15s, color .15s;
            padding: 0.8rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 1.08rem;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
        }
        .sidebar .nav-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .sidebar .profile-section {
            margin-bottom: 2.5rem;
        }
        .sidebar .profile-section h5 {
            font-family: 'Montserrat', Arial, sans-serif;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .main-content {
            padding: 2.5rem 2rem;
            min-height: 100vh;
            background: transparent;
        }
        .card, .alert, .modal-content {
            border-radius: var(--radius) !important;
            border: none;
            box-shadow: var(--shadow);
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: box-shadow .15s, background .15s;
        }
        .btn:focus, .btn:hover {
            box-shadow: 0 2px 12px rgba(37,99,235,0.10);
        }
        .alert-primary {
            background: linear-gradient(90deg, #e3eafc 0%, #f7faff 100%);
            color: #1e293b;
            border: none;
        }
        .alert-warning {
            background: linear-gradient(90deg, #fffbe6 0%, #fef3c7 100%);
            color: #b45309;
            border: none;
        }
        .badge {
            font-size: 0.85em;
            font-weight: 600;
            border-radius: 8px;
        }
        .card-title i {
            vertical-align: middle;
        }
        .card-body {
            padding: 1.7rem 1.5rem;
        }
        a {
            text-decoration: none !important;
        }
        .help-modal .modal-header {
            border-bottom: none;
            background: linear-gradient(90deg, #e3eafc 0%, #f7faff 100%);
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .help-modal .modal-title {
            font-weight: 700;
            color: var(--primary);
        }
        .help-list li {
            margin-bottom: 1.1rem;
        }
        .help-list i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        .help-modal .modal-footer {
            border-top: none;
            background: #f4f7fb;
            border-radius: 0 0 var(--radius) var(--radius);
        }
        .toast-container {
            position: fixed;
            top: 1.2rem;
            right: 1.2rem;
            z-index: 2000;
            max-width: 90vw;
        }
        @media (max-width: 991px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 1rem 0.5rem;
                position: static;
                border-radius: 0 0 var(--radius) var(--radius);
                box-shadow: none;
            }
            .main-content {
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 767px) {
            .sidebar {
                display: none;
            }
            .main-content {
                padding: 1rem 0.2rem;
            }
            .mobile-nav {
                display: flex !important;
            }
            .toast-container {
                top: auto;
                bottom: 4.5rem;
                right: 50%;
                left: 50%;
                transform: translateX(-50%);
                width: 95vw;
                max-width: 95vw;
            }
        }
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0; right: 0;
            background: var(--glass);
            box-shadow: 0 -2px 16px rgba(30,41,59,0.08);
            z-index: 999;
            border-radius: 18px 18px 0 0;
            justify-content: space-around;
            padding: 0.5rem 0;
        }
        .mobile-nav .nav-link {
            color: #334155;
            font-size: 1.3rem;
            padding: 0.5rem 0.7rem;
            border-radius: 10px;
        }
        .mobile-nav .nav-link.active, .mobile-nav .nav-link:hover {
            background: var(--primary);
            color: #fff;
        }
        ::-webkit-scrollbar {
            width: 8px;
            background: #e3eafc;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }
        /* Modern Card Hover */
        .card.glass:hover {
            box-shadow: 0 8px 32px rgba(37,99,235,0.13), 0 1.5px 8px rgba(30,41,59,0.07);
            transform: translateY(-2px) scale(1.01);
            transition: all .18s;
        }
        /* Responsive grid for fitur baru */
        @media (max-width: 991px) {
            .row.g-4 > [class^="col-"] {
                margin-bottom: 1.5rem;
            }
        }
        /* Modern shadow for sidebar */
        @media (min-width: 992px) {
            .sidebar {
                box-shadow: 0 8px 32px rgba(30,41,59,0.10);
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <!-- Sidebar -->
        <nav class="col-lg-2 d-none d-lg-block sidebar glass shadow-sm">
            <div class="profile-section text-center">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin" class="profile-img shadow-sm">
                <h5 class="mb-0 mt-2">Admin</h5>
                <span class="text-muted small">PT Wifiku Indonesia</span>
            </div>
            <ul class="nav flex-column mb-auto">
                <li><a href="#" class="nav-link active"><i class="bi bi-house-door-fill"></i>Dashboard</a></li>
                <li><a href="/projectroot/admin/manage_documents.php" class="nav-link"><i class="bi bi-folder2-open"></i>Kelola Dokumen</a></li>
                <li><a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true"><i class="bi bi-people"></i>Manajemen Pengguna</a></li>
                <li><a href="/projectroot/admin/statistik_documents.php" class="nav-link"><i class="bi bi-bar-chart-fill"></i>Statistik</a></li>
                <li><a href="notifications.php" class="nav-link" onclick="showNotification('Anda memiliki 2 notifikasi baru!', 'info'); return false;"><i class="bi bi-bell-fill"></i>Notifikasi</a></li>
                <li>
                    <a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true">
                        <i class="bi bi-clock-history"></i>Riwayat
                    </a>
                </li>
                <li><a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#helpModal"><i class="bi bi-question-circle"></i>Bantuan</a></li>
            </ul>
            <div class="mt-auto">
                <a href="../index.php" class="btn btn-outline-danger w-100" onclick="return confirm('Yakin ingin logout?')">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </nav>
        <!-- End Sidebar -->

        <!-- Main Content -->
        <main class="col-lg-10 main-content">
            <!-- Update Feature -->
            <div class="alert alert-primary d-flex align-items-center mb-4 glass shadow-sm" role="alert">
                <div class="me-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px; background:#fff; border-radius:50%; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                    <i class="bi bi-megaphone-fill fs-3 text-primary"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-semibold d-flex align-items-center" style="font-family:'Montserrat',sans-serif;">
                        Update Sistem
                        <span class="badge bg-secondary ms-2">Baru</span>
                    </h6>
                    <span>
                        Kini hadir <b>Manajemen Dokumen Lebih Cepat</b> dan <b>Statistik Pengarsipan</b> interaktif.<br>
                        <small class="text-muted">Cek menu di bawah untuk mencoba fitur terbaru.</small>
                    </span>
                </div>
                <div class="ms-auto d-flex align-items-center">
                    <a href="#fitur-baru" class="btn btn-outline-primary btn-sm me-2">Lihat Fitur</a>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="bi bi-question-circle me-1"></i>Help
                    </button>
                </div>
            </div>
            <!-- End Update Feature -->

            <!-- Notifikasi Fitur Belum Tersedia -->
            <div class="alert alert-warning d-flex align-items-center mb-4 glass" role="alert" style="border-left: 6px solid #fbbf24;">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-3"></i>
                <div>
                    <strong>Manajemen Pengguna</strong> masih <span class="badge bg-warning text-dark">Belum Tersedia</span>.<br>
                    <span class="small">Fitur ini sedang dalam tahap pengembangan dan akan segera hadir dengan pengalaman yang lebih baik dan aman.</span>
                </div>
            </div>
            <div class="alert alert-warning d-flex align-items-center mb-4 glass" role="alert" style="border-left: 6px solid #fbbf24;">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning me-3"></i>
                <div>
                    <strong>Riwayat</strong> masih <span class="badge bg-warning text-dark">Belum Tersedia</span>.<br>
                    <span class="small">Fitur ini sedang dalam tahap pemeliharaan dan akan segera tersedia kembali.</span>
                </div>
            </div>
            <!-- End Notifikasi -->

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4 glass">
                        <div class="card-body">
                            <h2 class="mb-2 fw-bold" style="font-size:1.8rem;font-family:'Montserrat',sans-serif;">Dashboard Admin</h2>
                            <div class="mb-3 text-secondary" style="font-size:1rem;">
                                Pengembangan Sistem Pengarsipan Digital Dokumen Teknis Jaringan Berbasis Web untuk Optimalisasi Manajemen Informasi di PT Wifiku Indonesia
                            </div>
                            <p class="mb-3">Selamat datang, <strong>Admin</strong>!<br>
                            Anda dapat mengelola dokumen teknis jaringan dan memantau aktivitas pengarsipan digital di sini.</p>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="/projectroot/admin/manage_documents.php" class="btn btn-primary"><i class="bi bi-folder2-open me-2"></i>Kelola Dokumen</a>
                                <a href="#" class="btn btn-outline-secondary disabled" tabindex="-1" aria-disabled="true" data-bs-toggle="tooltip" data-bs-title="Fitur belum tersedia"><i class="bi bi-people me-2"></i>Manajemen Pengguna</a>
                                <a href="/projectroot/admin/statistik_documents.php" class="btn btn-outline-success"><i class="bi bi-bar-chart-fill me-2"></i>Statistik</a>
                                <a href="notifications.php" class="btn btn-outline-warning text-dark" onclick="showNotification('Anda memiliki 2 notifikasi baru!', 'info'); return false;"><i class="bi bi-bell-fill me-2"></i>Notifikasi</a>
                                <a href="#" class="btn btn-outline-info text-dark disabled" tabindex="-1" aria-disabled="true" data-bs-toggle="tooltip" data-bs-title="Fitur masih maintenance"><i class="bi bi-clock-history me-2"></i>Riwayat</a>
                                <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#helpModal"><i class="bi bi-question-circle me-2"></i>Bantuan</button>
                            </div>
                        </div>
                    </div>
                    <!-- Section Fitur Baru -->
                    <div id="fitur-baru" class="mt-4">
                        <h5 class="fw-bold mb-3" style="font-family:'Montserrat',sans-serif;"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Fitur Terbaru</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card h-100 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-speedometer2 text-primary me-2"></i>Manajemen Cepat</h6>
                                        <p class="card-text small">Upload, pencarian, dan pengelolaan dokumen kini lebih efisien.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-bar-chart-fill text-success me-2"></i>Statistik</h6>
                                        <p class="card-text small">Pantau statistik pengarsipan secara real-time dengan grafik interaktif.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-bell-fill text-warning me-2"></i>Notifikasi</h6>
                                        <p class="card-text small">Dapatkan pemberitahuan otomatis terkait aktivitas penting.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 mt-3 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-clock-history text-info me-2"></i>Riwayat Aktivitas</h6>
                                        <p class="card-text small text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>Fitur belum tersedia. Nantikan update berikutnya!.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 mt-3 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-question-circle text-secondary me-2"></i>Bantuan</h6>
                                        <p class="card-text small">Akses dokumentasi dan panduan penggunaan sistem.</p>
                                        <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#helpModal">
                                            <i class="bi bi-journal-text me-1"></i>Lihat Panduan
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 mt-3 glass">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2"><i class="bi bi-people text-secondary me-2"></i>Manajemen Pengguna</h6>
                                        <p class="card-text small text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i>Fitur belum tersedia. Nantikan update berikutnya!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Section Fitur Baru -->
                </div>
                <div class="col-lg-4">
                    <!-- Quick Info Card -->
                    <div class="card mb-4 glass">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle text-primary me-2"></i>Info Singkat</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li><i class="bi bi-check-circle-fill text-success me-2"></i>Backup Otomatis Harian</li>
                                <li><i class="bi bi-shield-lock-fill text-warning me-2"></i>Keamanan Data</li>
                                <li><i class="bi bi-cloud-arrow-up-fill text-info me-2"></i>Akses Cloud 24/7</li>
                            </ul>
                        </div>
                    </div>
                    <!-- End Quick Info Card -->
                </div>
            </div>
        </main>
        <!-- End Main Content -->
    </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container" id="toastContainer"></div>
<!-- End Toast Notification -->

<!-- Mobile Navigation -->
<nav class="mobile-nav d-flex d-lg-none glass">
    <a href="#" class="nav-link active"><i class="bi bi-house-door-fill"></i></a>
    <a href="/projectroot/admin/manage_documents.php" class="nav-link"><i class="bi bi-folder2-open"></i></a>
    <a href="#" class="nav-link disabled" tabindex="-1" aria-disabled="true"><i class="bi bi-people"></i></a>
    <a href="/projectroot/admin/statistik_documents.php" class="nav-link"><i class="bi bi-bar-chart-fill"></i></a>
    <a href="notifications.php" class="nav-link" onclick="showNotification('Anda memiliki 2 notifikasi baru!', 'info'); return false;"><i class="bi bi-bell-fill"></i></a>
    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#helpModal"><i class="bi bi-question-circle"></i></a>
</nav>
<!-- End Mobile Navigation -->

<!-- Help Modal -->
<div class="modal fade help-modal" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content glass">
      <div class="modal-header">
        <h5 class="modal-title" id="helpModalLabel"><i class="bi bi-journal-text me-2"></i>Panduan & Dokumentasi Sistem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <ul class="help-list list-unstyled">
          <li>
            <i class="bi bi-folder2-open"></i>
            <strong>Kelola Dokumen:</strong> Upload, edit, hapus, dan cari dokumen teknis jaringan dengan mudah melalui menu <b>Kelola Dokumen</b>.
          </li>
          <li>
            <i class="bi bi-bar-chart-fill"></i>
            <strong>Statistik:</strong> Pantau statistik pengarsipan dokumen secara real-time dan visualisasi data dalam bentuk grafik.
          </li>
          <li>
            <i class="bi bi-bell-fill"></i>
            <strong>Notifikasi:</strong> Dapatkan pemberitahuan otomatis terkait aktivitas penting, seperti upload dokumen baru atau perubahan status.
          </li>
          <li>
            <i class="bi bi-clock-history"></i>
            <strong>Riwayat:</strong> (Maintenance) Fitur sedang dalam pemeliharaan oleh developer.
          </li>
          <li>
            <i class="bi bi-people"></i>
            <strong>Manajemen Pengguna:</strong> (Segera Hadir) Kelola akun pengguna dan hak akses secara terpusat.
          </li>
          <li>
            <i class="bi bi-shield-lock-fill"></i>
            <strong>Keamanan Data:</strong> Sistem dilengkapi backup otomatis harian dan enkripsi data untuk menjaga keamanan informasi.
          </li>
          <li>
            <i class="bi bi-question-circle"></i>
            <strong>Bantuan Lainnya:</strong> Jika mengalami kendala, hubungi admin IT atau cek dokumentasi lengkap di <a href="mailto:support@wifiku.co.id" class="text-primary">support@wifiku.co.id</a>.
          </li>
        </ul>
        <hr>
        <div class="text-center">
            <a href="#" class="btn btn-outline-primary" disabled><i class="bi bi-file-earmark-text me-1"></i>Download Dokumentasi (Segera Hadir)</a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<!-- End Help Modal -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll for fitur baru
document.querySelectorAll('a[href^="#fitur-baru"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#fitur-baru').scrollIntoView({ behavior: 'smooth' });
    });
});
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Elegant Notification (Toast) Function
function showNotification(message, type = 'info') {
    const icons = {
        info: 'bi-info-circle-fill text-info',
        success: 'bi-check-circle-fill text-success',
        warning: 'bi-exclamation-triangle-fill text-warning',
        danger: 'bi-x-circle-fill text-danger'
    };
    const titles = {
        info: 'Info',
        success: 'Sukses',
        warning: 'Peringatan',
        danger: 'Kesalahan'
    };
    const icon = icons[type] || icons.info;
    const title = titles[type] || titles.info;
    const toastId = 'toast-' + Date.now();

    const toastHtml = `
    <div id="${toastId}" class="toast align-items-center glass border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center">
          <i class="bi ${icon} fs-5 me-2"></i>
          <div>
            <strong class="me-2">${title}:</strong> ${message}
          </div>
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
    `;
    const container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', toastHtml);

    var toastEl = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastEl);
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', function () {
        toastEl.remove();
    });
}

// Tampilkan notifikasi dari session (jika ada)
<?php if ($toast_message): ?>
window.addEventListener('DOMContentLoaded', function() {
    showNotification(<?php echo json_encode($toast_message); ?>, <?php echo json_encode($toast_type); ?>);
});
<?php endif; ?>
</script>
</body>
</html>
