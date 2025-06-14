<?php

// --- PENGATURAN DASAR ---
// Definisikan PROJECT_ROOT_PATH untuk path absolut ke direktori root proyek.
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}

// --- PENGATURAN DATABASE ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'pengarsipan_digital');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- PENGATURAN APLIKASI ---
// Ganti BASE_URL sesuai dengan URL root aplikasi Anda.
define('BASE_URL', 'http://localhost//');
define('UPLOAD_DIR', 'uploads/');

// Sinyal file upload admin
if (!defined('ADMIN_UPLOAD_SIGNAL_FILE')) {
    define('ADMIN_UPLOAD_SIGNAL_FILE', PROJECT_ROOT_PATH . '/admin_upload_signal.txt');
}

// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- KONEKSI DATABASE (PDO) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Untuk production, log error saja
    // error_log("Koneksi Gagal: " . $e->getMessage());
    // die("Tidak dapat terhubung ke database. Silakan coba lagi nanti.");
    // Untuk development, throw exception
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// --- FUNGSI BANTUAN ---
if (!function_exists('escape_html')) {
    /**
     * Escape string untuk output HTML
     * @param mixed $string
     * @return string
     */
    function escape_html($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// --- SIMULASI SESI PENGGUNA (HANYA UNTUK DEVELOPMENT) ---
if (!isset($_SESSION['user_id'])) {
    try {
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT id_pengguna, nama_lengkap, username FROM pengguna WHERE username = 'admin' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user_id']   = $user['id_pengguna'];
                $_SESSION['user_name'] = $user['nama_lengkap'];
                $_SESSION['username']  = $user['username'];
            } else {
                $_SESSION['user_id']   = 1;
                $_SESSION['user_name'] = "Admin Contoh (Fallback)";
                $_SESSION['username']  = "admin_contoh_fallback";
            }
        } else {
            $_SESSION['user_id']   = 1;
            $_SESSION['user_name'] = "Admin PDO Not Set";
            $_SESSION['username']  = "admin_pdo_not_set";
        }
    } catch (PDOException $e) {
        $_SESSION['user_id']   = 1;
        $_SESSION['user_name'] = "Admin DB Error";
        $_SESSION['username']  = "admin_db_error";
        error_log("Error saat simulasi user di config.php: " . $e->getMessage());
    }
}

// --- VARIABEL GLOBAL OPSIONAL UNTUK USER LOGIN ---
// $loggedInUserId = $_SESSION['user_id'] ?? null;
// $loggedInUserName = $_SESSION['user_name'] ?? 'Guest';
