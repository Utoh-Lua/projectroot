<?php
session_start();

// Check if the user is logged in and has the 'manajer' role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manajer') {
    $_SESSION['error'] = "Anda harus login sebagai Manajer untuk mengakses halaman ini.";
    header("Location: ../index.php#login-form");
    exit();
}

// Get user's name for display
$loggedInUserName = $_SESSION['user_name'] ?? 'Manajer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Manajer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts: Inter & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --accent: #06b6d4;
            --danger: #ef4444;
            --warning: #f59e42;
            --success: #22c55e;
            --bg: #f3f6fa;
            --card-bg: #fff;
            --text: #1e293b;
            --muted: #64748b;
            --radius: 18px;
            --shadow: 0 6px 32px rgba(30,41,59,0.09);
            --shadow-hover: 0 12px 36px rgba(30,41,59,0.13);
            --navbar-height: 64px;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background: var(--bg);
            font-family: 'Inter', 'Poppins', Arial, sans-serif;
            color: var(--text);
            min-height: 100vh;
        }
        .navbar {
            height: var(--navbar-height);
            background: linear-gradient(90deg, var(--primary) 60%, var(--accent) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            font-family: 'Poppins', 'Inter', Arial, sans-serif;
            font-size: 1.25em;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 16px rgba(30,41,59,0.08);
            border-bottom-left-radius: var(--radius);
            border-bottom-right-radius: var(--radius);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .navbar #clock {
            font-size: 1em;
            font-weight: 500;
            letter-spacing: 1px;
        }
        .container {
            max-width: 1100px;
            margin: 48px auto 0 auto;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px 36px 32px 36px;
            min-height: 60vh;
        }
        h1 {
            color: var(--primary-dark);
            font-family: 'Poppins', 'Inter', Arial, sans-serif;
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }
        .cards {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            margin-top: 32px;
        }
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 32px 28px 28px 28px;
            flex: 1 1 340px;
            min-width: 270px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            position: relative;
            transition: box-shadow 0.18s, transform 0.18s;
        }
        .card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-3px) scale(1.012);
        }
        .card h2 {
            font-size: 1.18em;
            color: var(--primary-dark);
            margin: 0 0 10px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Poppins', 'Inter', Arial, sans-serif;
        }
        .statistik-list {
            display: flex;
            gap: 18px;
            margin-top: 8px;
        }
        .statistik-item {
            background: linear-gradient(120deg, #f1f5f9 60%, #e0e7ef 100%);
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 1px 6px rgba(30,41,59,0.04);
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .statistik-item .label {
            color: var(--muted);
            font-size: 1em;
            margin-bottom: 2px;
            font-weight: 500;
        }
        .statistik-item .value {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 1px;
        }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.05em;
            font-weight: 600;
            font-family: 'Inter', Arial, sans-serif;
            transition: background 0.16s, box-shadow 0.16s, transform 0.13s;
            box-shadow: 0 2px 10px rgba(30,41,59,0.09);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn:hover:not([disabled]) {
            background: var(--primary-dark);
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 4px 18px rgba(30,41,59,0.13);
        }
        .btn-accent {
            background: var(--accent);
        }
        .btn-danger {
            background: var(--danger);
        }
        /* Toggle Switch Modern */
        .toggle-realtime {
            position: absolute;
            top: 18px;
            right: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1em;
            color: var(--muted);
            z-index: 2;
        }
        .toggle-switch {
            width: 40px;
            height: 22px;
            background: #e0e7ef;
            border-radius: 12px;
            position: relative;
            transition: background 0.2s;
            cursor: pointer;
            flex-shrink: 0;
            outline: none;
        }
        .toggle-switch[data-checked="true"] {
            background: var(--primary);
        }
        .toggle-knob {
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: left 0.2s;
            box-shadow: 0 1px 6px rgba(30,41,59,0.10);
        }
        .toggle-switch[data-checked="true"] .toggle-knob {
            left: 20px;
        }
        .toggle-label {
            font-size: 1em;
            color: var(--muted);
            margin-left: 1px;
            font-weight: 500;
        }
        /* Chart Container */
        .chart-container {
            background: linear-gradient(120deg, #f1f5f9 60%, #e0e7ef 100%);
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(30,41,59,0.05);
            padding: 18px 10px 10px 10px;
            margin-top: 14px;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 180px;
        }
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(30,41,59,0.13);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .modal-content {
            background: #fff;
            border-radius: 14px;
            padding: 36px 28px;
            box-shadow: 0 10px 36px rgba(30,41,59,0.13);
            max-width: 340px;
            text-align: center;
            font-family: 'Inter', Arial, sans-serif;
        }
        .modal-content h3 {
            margin: 18px 0 10px 0;
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.25em;
        }
        .modal-content .modal-desc {
            color: var(--muted);
            font-size: 1.05em;
            margin-bottom: 18px;
        }
        .modal-content .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        /* Responsive */
        @media (max-width: 1100px) {
            .container {
                max-width: 98vw;
                padding: 18px 6vw 16px 6vw;
            }
            .cards {
                gap: 18px;
            }
        }
        @media (max-width: 900px) {
            .container {
                padding: 12px 2vw 10px 2vw;
            }
            .cards {
                flex-direction: column;
                gap: 16px;
            }
            .toggle-realtime {
                top: 10px;
                right: 10px;
            }
            .chart-container {
                padding: 8px 2px 2px 2px;
            }
        }
        @media (max-width: 600px) {
            .navbar {
                padding: 0 10px;
                font-size: 1em;
                height: 54px;
            }
            h1 {
                font-size: 1.1em;
            }
            .container {
                padding: 4px 1vw 4px 1vw;
            }
            .card {
                padding: 18px 8px 14px 8px;
            }
            .modal-content {
                padding: 18px 8px;
            }
        }
        @media (max-width: 400px) {
            .container {
                padding: 2px 0.5vw 2px 0.5vw;
            }
            .card {
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <span class="brand"><i data-feather="bar-chart-2"></i>Dashboard Manajer</span>
        <div class="navbar-actions">
            <span id="clock"></span>
            <button class="btn" type="button" id="logoutBtn">
                <i data-feather="log-out"></i>Logout
            </button>
        </div>
    </div>
    <div class="container">
        <h1>Selamat Datang, Manajer!</h1>
        <div class="cards">
            <div class="card">
                <div class="toggle-realtime" title="Aktifkan update otomatis">
                    <span class="toggle-label">Realtime</span>
                    <div class="toggle-switch" id="realtimeToggle" data-checked="false" tabindex="0" role="switch" aria-checked="false">
                        <div class="toggle-knob"></div>
                    </div>
                </div>
                <h2><i data-feather="activity"></i>Statistik Proyek</h2>
                <div id="statistik" class="statistik-list">
                    <div class="statistik-item">
                        <div class="label">Aktif</div>
                        <div class="value" id="aktif">-</div>
                    </div>
                    <div class="statistik-item">
                        <div class="label">Selesai</div>
                        <div class="value" id="selesai">-</div>
                    </div>
                    <div class="statistik-item">
                        <div class="label">Tertunda</div>
                        <div class="value" id="tertunda">-</div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="statistikChart" width="320" height="140"></canvas>
                </div>
                <button class="btn" type="button" onclick="refreshStatistik()" id="refreshBtn">
                    <i data-feather="refresh-cw"></i>Refresh Statistik
                </button>
            </div>
            <div class="card">
                <h2><i data-feather="file-text"></i>Laporan Bulanan</h2>
                <p style="color:var(--muted);margin:0 0 10px 0;">Lihat ringkasan performa proyek bulan ini.</p>
                <button class="btn btn-accent" type="button" onclick="showAlert()">
                    <i data-feather="eye"></i>Lihat Laporan
                </button>
            </div>
        </div>
    </div>
    <script>
        // Statistik update logic
        let realtime = false;
        let realtimeInterval = null;

        // Chart.js setup
        let chart = null;
        let chartData = {
            labels: [],
            datasets: [
                {
                    label: 'Aktif',
                    data: [],
                    backgroundColor: 'rgba(37,99,235,0.10)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#2563eb'
                },
                {
                    label: 'Selesai',
                    data: [],
                    backgroundColor: 'rgba(34,197,94,0.10)',
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e'
                },
                {
                    label: 'Tertunda',
                    data: [],
                    backgroundColor: 'rgba(245,158,66,0.10)',
                    borderColor: '#f59e42',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#f59e42'
                }
            ]
        };

        function setLoadingStatistik() {
            document.getElementById('aktif').textContent = '...';
            document.getElementById('selesai').textContent = '...';
            document.getElementById('tertunda').textContent = '...';
        }

        // Simulasi fetch data statistik (bisa diganti AJAX)
        function getRandomStatistik() {
            return {
                aktif: 4 + Math.floor(Math.random() * 4),
                selesai: 10 + Math.floor(Math.random() * 6),
                tertunda: 1 + Math.floor(Math.random() * 4)
            };
        }

        function refreshStatistik() {
            setLoadingStatistik();
            setTimeout(function() {
                const data = getRandomStatistik();
                document.getElementById('aktif').textContent = data.aktif;
                document.getElementById('selesai').textContent = data.selesai;
                document.getElementById('tertunda').textContent = data.tertunda;
                updateChart(data);
                showUpdateNotification();
            }, 700);
        }

        function updateChart(data) {
            const now = new Date();
            const label = now.getHours().toString().padStart(2, '0') + ':' +
                          now.getMinutes().toString().padStart(2, '0') + ':' +
                          now.getSeconds().toString().padStart(2, '0');
            if (chartData.labels.length >= 10) {
                chartData.labels.shift();
                chartData.datasets[0].data.shift();
                chartData.datasets[1].data.shift();
                chartData.datasets[2].data.shift();
            }
            chartData.labels.push(label);
            chartData.datasets[0].data.push(data.aktif);
            chartData.datasets[1].data.push(data.selesai);
            chartData.datasets[2].data.push(data.tertunda);
            chart.update();
        }

        function setRealtimeMode(on) {
            realtime = on;
            const toggle = document.getElementById('realtimeToggle');
            toggle.setAttribute('data-checked', on ? 'true' : 'false');
            toggle.setAttribute('aria-checked', on ? 'true' : 'false');
            document.getElementById('refreshBtn').disabled = on;
            if (on) {
                refreshStatistik();
                realtimeInterval = setInterval(refreshStatistik, 5000);
            } else {
                if (realtimeInterval) clearInterval(realtimeInterval);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('realtimeToggle');
            toggle.addEventListener('click', function() {
                setRealtimeMode(!realtime);
            });
            toggle.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    setRealtimeMode(!realtime);
                    e.preventDefault();
                }
            });

            document.getElementById('logoutBtn').addEventListener('click', function() {
                showLogoutModal();
            });
        });

        function showAlert() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <i data-feather="info" style="color:var(--primary);width:36px;height:36px;"></i>
                    <h3>Segera Tersedia</h3>
                    <div class="modal-desc">
                        Fitur laporan bulanan akan segera tersedia.
                    </div>
                    <button class="btn" type="button" style="width:100%;" onclick="this.closest('.modal-overlay').remove();">
                        <i data-feather="x"></i>Tutup
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
            feather.replace();
        }

        function showLogoutModal() {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <i data-feather="log-out" style="color:var(--danger);width:36px;height:36px;"></i>
                    <h3>Konfirmasi Logout</h3>
                    <div class="modal-desc">
                        Apakah Anda yakin ingin logout dari dashboard?
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-danger" type="button" style="flex:1;" onclick="window.location.href='../index.php'">
                            <i data-feather="log-out"></i>Logout
                        </button>
                        <button class="btn" type="button" style="flex:1;" onclick="this.closest('.modal-overlay').remove();">
                            <i data-feather="x"></i>Batal
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            feather.replace();
        }

        function updateClock() {
            const now = new Date();
            const pad = n => n.toString().padStart(2, '0');
            document.getElementById('clock').textContent =
                pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        }

        function showUpdateNotification() {
            if (document.getElementById('updateNotif')) {
                document.getElementById('updateNotif').remove();
            }
            const notif = document.createElement('div');
            notif.id = 'updateNotif';
            notif.style.position = 'fixed';
            notif.style.bottom = '32px';
            notif.style.right = '32px';
            notif.style.background = '#fff';
            notif.style.color = 'var(--primary-dark)';
            notif.style.padding = '14px 22px';
            notif.style.borderRadius = '10px';
            notif.style.boxShadow = '0 6px 24px rgba(30,41,59,0.13)';
            notif.style.fontWeight = '600';
            notif.style.fontSize = '1.05em';
            notif.style.display = 'flex';
            notif.style.alignItems = 'center';
            notif.style.gap = '10px';
            notif.style.zIndex = 10000;
            notif.innerHTML = `<i data-feather="check-circle" style="color:var(--success);"></i> Statistik berhasil diupdate`;
            document.body.appendChild(notif);
            feather.replace();
            setTimeout(() => {
                notif.style.opacity = 0;
                setTimeout(() => notif.remove(), 350);
            }, 1200);
        }

        window.onload = function() {
            const ctx = document.getElementById('statistikChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#1e293b',
                                font: { size: 14, family: 'Inter' }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#fff',
                            titleColor: '#1e293b',
                            bodyColor: '#1e293b',
                            borderColor: '#e0e7ef',
                            borderWidth: 1,
                            padding: 12
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#64748b', font: { family: 'Inter' } },
                            grid: { color: 'rgba(37,99,235,0.06)' }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#64748b', font: { family: 'Inter' } },
                            grid: { color: 'rgba(37,99,235,0.06)' }
                        }
                    }
                }
            });
            refreshStatistik();
            feather.replace();
            updateClock();
            setInterval(updateClock, 1000);
        };
    </script>
</body>
</html>
