<?php
// statistik_documents.php (Redesigned Modern UI)
// Real-time Document Statistics Page with Chart.js, AJAX polling, and modern, elegant, responsive design

require_once __DIR__ . '/../config/config.php';

if (!defined('ADMIN_UPLOAD_SIGNAL_FILE')) {
    error_log("Critical Error: ADMIN_UPLOAD_SIGNAL_FILE not defined in statistik_documents.php.");
    http_response_code(500);
    exit(json_encode(['error' => 'Server configuration error (signal file path).']));
}

if (!isset($pdo)) {
    error_log("Critical Error: PDO connection not available in statistik_documents.php.");
    http_response_code(500);
    exit(json_encode(['error' => 'Server configuration error (database connection).']));
}

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'data') {
        try {
            $sql = "
                SELECT DATE_FORMAT(tanggal_unggah, '%Y-%m') AS periode, COUNT(*) AS total
                FROM dokumen
                WHERE tanggal_unggah >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH) 
                      AND tanggal_unggah < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                GROUP BY periode
                ORDER BY periode ASC
            ";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            $labels = [];
            $data = [];
            $currentDate = new DateTime('first day of this month');
            for ($i = 11; $i >= 0; $i--) {
                $monthKey = (clone $currentDate)->modify("-{$i} months")->format('Y-m');
                $labels[] = $monthKey;
                $data[$monthKey] = 0;
            }
            foreach ($rows as $r) {
                if (isset($data[$r['periode']])) {
                    $data[$r['periode']] = (int)$r['total'];
                }
            }
            echo json_encode([
                'labels' => $labels,
                'values' => array_values($data)
            ]);
        } catch (PDOException $e) {
            error_log("PDOException statistik: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch statistics from database.']);
        }
        exit;
    } elseif ($_GET['action'] === 'check_signal') {
        $signalTimestamp = 0;
        if (file_exists(ADMIN_UPLOAD_SIGNAL_FILE)) {
            $signalContent = file_get_contents(ADMIN_UPLOAD_SIGNAL_FILE);
            if ($signalContent !== false) {
                $signalTimestamp = (int)$signalContent;
            }
        }
        echo json_encode(['signal_timestamp' => $signalTimestamp]);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Dokumen - Sistem Pengarsipan Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --accent: #38bdf8;
            --bg: #f8fafc;
            --card-bg: #fff;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: var(--bg);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
        }
        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 2rem 1rem;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(30, 64, 175, 0.08), 0 1.5px 4px rgba(0,0,0,0.03);
            max-width: 600px;
            width: 100%;
            padding: 2.5rem 2rem 2rem 2rem;
            margin: 0 auto;
            position: relative;
        }
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .stat-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        .stat-title i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        .switch-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.3em;
            background-color: #e0e7ef;
            border: 1.5px solid var(--border);
            transition: background 0.2s;
        }
        .form-switch .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .form-switch .form-check-label {
            font-weight: 500;
            color: var(--muted);
            font-size: 1rem;
            margin-left: 0.5rem;
        }
        .chart-area {
            min-height: 320px;
            padding: 0;
            margin-bottom: 1.5rem;
        }
        #chartUploads {
            width: 100% !important;
            height: 320px !important;
            background: #f1f5f9;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            box-shadow: 0 2px 8px rgba(59,130,246,0.04);
        }
        .loading-indicator {
            text-align: center;
            padding: 2rem 0;
            color: var(--muted);
            font-size: 1.1rem;
        }
        .btn-custom-refresh {
            background: linear-gradient(90deg, var(--primary) 60%, var(--accent) 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.7rem 1.5rem;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px rgba(59,130,246,0.07);
            transition: background 0.18s, box-shadow 0.18s;
        }
        .btn-custom-refresh:hover, .btn-custom-refresh:focus {
            background: linear-gradient(90deg, var(--primary-dark) 60%, var(--primary) 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(59,130,246,0.13);
        }
        .btn-custom-refresh i {
            margin-right: 8px;
        }
        #notificationArea {
            position: fixed;
            top: 24px;
            right: 24px;
            min-width: 220px;
            background: var(--success);
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(16,185,129,0.13);
            z-index: 1050;
            display: none;
            opacity: 0;
            font-weight: 600;
            font-size: 1.05rem;
            transition: opacity 0.4s, transform 0.4s;
            transform: translateY(-20px);
        }
        #notificationArea.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        @media (max-width: 900px) {
            .stat-card { max-width: 98vw; padding: 1.5rem 0.7rem 1.2rem 0.7rem; }
            .stat-title { font-size: 1.3rem; }
            .chart-area { min-height: 220px; }
            #chartUploads { height: 220px !important; }
        }
        @media (max-width: 600px) {
            .main-wrapper { padding: 1rem 0.2rem; }
            .stat-card { padding: 1rem 0.3rem 0.7rem 0.3rem; }
            .stat-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .stat-title { font-size: 1.1rem; }
            #notificationArea { top: 10px; right: 10px; min-width: 120px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
<div class="main-wrapper">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-title">
                <i class="fas fa-chart-line"></i>
                Statistik Pengarsipan Dokumen
            </div>
            <div class="switch-wrap">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="realtimeToggle" checked>
                    <label class="form-check-label" for="realtimeToggle">Real-time</label>
                </div>
            </div>
        </div>
        <div class="chart-area">
            <canvas id="chartUploads"></canvas>
            <div id="loadingIndicator" class="loading-indicator" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Memuat data statistik...
            </div>
        </div>
        <button id="refreshBtn" class="btn btn-custom-refresh w-100">
            <i class="fas fa-sync-alt"></i>
            Refresh Data
        </button>
    </div>
</div>
<div id="notificationArea">Data berhasil diperbarui!</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    const ctx = document.getElementById('chartUploads').getContext('2d');
    const loadingIndicator = document.getElementById('loadingIndicator');
    let uploadsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Dokumen Diunggah',
                data: [],
                fill: {
                    target: 'origin',
                    above: 'rgba(37,99,235,0.13)',
                },
                borderColor: 'rgb(37,99,235)',
                backgroundColor: 'rgba(37,99,235,0.09)',
                tension: 0.45,
                borderWidth: 3,
                pointBackgroundColor: 'rgb(37,99,235)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(37,99,235)',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        color: '#64748b',
                        font: { size: 13, weight: 600 }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        color: '#f1f5f9'
                    }
                },
                x: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 13, weight: 600 }
                    },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#334155',
                        font: { size: 14, weight: 600 },
                        usePointStyle: true,
                        boxWidth: 10
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(30,41,59,0.93)',
                    titleColor: '#f1f5f9',
                    bodyColor: '#e2e8f0',
                    titleFont: { weight: 'bold', size: 15 },
                    bodyFont: { size: 14 },
                    padding: 13,
                    cornerRadius: 7,
                    displayColors: true,
                    borderColor: 'rgba(37,99,235,0.13)',
                    borderWidth: 1.2
                }
            },
            animation: {
                duration: 700,
                easing: 'easeOutCubic'
            }
        }
    });

    const STATS_FETCH_INTERVAL = 10000;
    const SIGNAL_CHECK_INTERVAL = 3000;
    let statsIntervalId;
    let signalIntervalId;
    let lastKnownSignalTimestamp = 0;
    const notificationArea = document.getElementById('notificationArea');

    function showLoading(show) {
        if (loadingIndicator) loadingIndicator.style.display = show ? 'block' : 'none';
        if (document.getElementById('chartUploads')) document.getElementById('chartUploads').style.opacity = show ? '0.2' : '1';
    }

    function showNotification(message, type = 'success', duration = 3500) {
        notificationArea.textContent = message;
        notificationArea.className = '';
        notificationArea.classList.add('show');
        if (type === 'error') {
            notificationArea.style.backgroundColor = 'var(--danger)';
        } else if (type === 'warning') {
            notificationArea.style.backgroundColor = 'var(--warning)';
        } else {
            notificationArea.style.backgroundColor = 'var(--success)';
        }
        setTimeout(() => {
            notificationArea.classList.remove('show');
        }, duration);
    }

    async function fetchStats(isTriggeredBySignal = false) {
        showLoading(true);
        try {
            const res = await fetch('?action=data');
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP error! status: ${res.status}, response: ${errorText}`);
            }
            const json = await res.json();
            if (json.error) throw new Error(json.error);

            uploadsChart.data.labels = json.labels;
            uploadsChart.data.datasets[0].data = json.values;
            uploadsChart.update('none');
            if (isTriggeredBySignal) showNotification('Data statistik diperbarui (Unggahan Admin Baru).');
        } catch (err) {
            showNotification(`Error memuat statistik: ${err.message}`, 'error', 5000);
        } finally {
            showLoading(false);
        }
    }

    async function checkUploadSignal() {
        try {
            const res = await fetch('?action=check_signal');
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP error! status: ${res.status}, response: ${errorText}`);
            }
            const json = await res.json();
            if (json.error) throw new Error(json.error);

            const currentSignalTimestamp = parseInt(json.signal_timestamp, 10);
            if (currentSignalTimestamp > 0 && currentSignalTimestamp > lastKnownSignalTimestamp) {
                lastKnownSignalTimestamp = currentSignalTimestamp;
                await fetchStats(true);
            }
        } catch (err) {
            // Silent fail for signal check
        }
    }

    function startRealtimeUpdates() {
        stopRealtimeUpdates();
        fetchStats();
        checkUploadSignal();
        statsIntervalId = setInterval(fetchStats, STATS_FETCH_INTERVAL);
        signalIntervalId = setInterval(checkUploadSignal, SIGNAL_CHECK_INTERVAL);
        document.getElementById('realtimeToggle').disabled = false;
    }

    function stopRealtimeUpdates() {
        clearInterval(statsIntervalId);
        clearInterval(signalIntervalId);
        document.getElementById('realtimeToggle').disabled = false;
    }

    document.getElementById('realtimeToggle').addEventListener('change', function() {
        this.disabled = true;
        if (this.checked) startRealtimeUpdates();
        else stopRealtimeUpdates();
    });

    document.getElementById('refreshBtn').addEventListener('click', async () => {
        showNotification('Merefresh data...', 'info', 2000);
        await fetchStats();
        await checkUploadSignal();
    });

    if (document.getElementById('realtimeToggle').checked) {
        startRealtimeUpdates();
    } else {
        fetchStats();
        checkUploadSignal();
    }
</script>
</body>
</html>
