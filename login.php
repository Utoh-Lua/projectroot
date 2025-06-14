<?php
session_start();

// Database Configuration
$host = "localhost";
$dbname = "pengarsipan_digital";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For a real application, log this error instead of showing it to the user
    error_log("Database connection failed: " . $e->getMessage());
    // Set a generic error message for the user
    $_SESSION['error'] = "Sistem sedang mengalami gangguan. Silakan coba lagi nanti.";
    header("Location: index.php");
    exit();
}

// Get data from the form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$id_jabatan = $_POST['jabatan'] ?? '';

// Input validation
if (empty($username) || empty($password) || empty($id_jabatan)) {
    $_SESSION['error'] = "Semua kolom (username, password, dan jabatan) harus diisi!";
    header("Location: index.php#login-form"); // Redirect back to the login form
    exit();
}

try {
    // Query the user and join with the 'jabatan' table to get the role name
    $stmt = $pdo->prepare(
        "SELECT p.*, j.nama_jabatan
         FROM pengguna p
         JOIN jabatan j ON p.id_jabatan = j.id_jabatan
         WHERE p.username = :username AND p.id_jabatan = :id_jabatan"
    );
    $stmt->execute(['username' => $username, 'id_jabatan' => $id_jabatan]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id_pengguna'];
        $_SESSION['user_name'] = $user['nama_lengkap']; // Store the user's full name
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role_id'] = $user['id_jabatan'];
        $_SESSION['user_role'] = strtolower(explode(' ', $user['nama_jabatan'])[0]); // e.g., 'admin', 'manajer', 'teknisi'

        // Set a success toast message for the dashboard
        $_SESSION['toast_message'] = 'Selamat datang kembali, ' . htmlspecialchars($user['nama_lengkap']) . '!';
        $_SESSION['toast_type'] = 'success';


        // Redirect based on the role
        switch ($user['id_jabatan']) {
            case 1: // Administrator Sistem
                header("Location: dashboard/admin.php");
                break;
            case 2: // Manajer Jaringan
                header("Location: dashboard/manajer.php");
                break;
            case 3: // Staf Teknisi Jaringan
                header("Location: dashboard/teknisi.php");
                break;
            default:
                // Fallback for any other roles
                $_SESSION['error'] = "Dashboard untuk peran Anda belum tersedia.";
                header("Location: index.php#login-form");
                break;
        }
        exit();
    } else {
        // Invalid credentials
        $_SESSION['error'] = "Username, password, atau jabatan yang Anda masukkan salah!";
        header("Location: index.php#login-form");
        exit();
    }
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan pada database saat mencoba login.";
    header("Location: index.php#login-form");
    exit();
}
?>