<?php

use FFI\Exception;
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: ../login.php');
    exit;
}
$loggedInUserId = $_SESSION['user_id'];
$loggedInUserName = $_SESSION['user_name'];

if (!function_exists('escape_html')) {
    function escape_html($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$pageTitle = "Manajemen Dokumen Teknis Jaringan - PT Wifiku Indonesia";
$systemTitle = "Pengembangan Sistem Pengarsipan Digital Dokumen Teknis Jaringan Berbasis Web untuk Optimalisasi Manajemen Informasi di PT Wifiku Indonesia";

$messages = [];
if (isset($_SESSION['flash_messages'])) {
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
}

try {
    if (!isset($pdo)) {
        throw new Exception("Koneksi PDO tidak terdefinisi.");
    }
    $stmt_types = $pdo->query("SELECT id_tipe_dokumen, nama_tipe FROM tipe_dokumen ORDER BY nama_tipe ASC");
    $db_document_types = $stmt_types->fetchAll();
} catch (PDOException $e) {
    $messages[] = ['type' => 'error', 'text' => 'Gagal mengambil daftar tipe dokumen: ' . $e->getMessage()];
    $db_document_types = [];
} catch (Exception $e) {
    $messages[] = ['type' => 'error', 'text' => 'Kesalahan konfigurasi: ' . $e->getMessage()];
    $db_document_types = [];
}

// Upload logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_document'])) {
    $upload_messages = [];
    $document_name_original = trim($_POST['document_name'] ?? '');
    $document_type_id = $_POST['document_type_id'] ?? '';
    $device_location = trim($_POST['device_location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $document_version = trim($_POST['document_version'] ?? '1.0');
    $uploaded_by_id = $loggedInUserId;
    $upload_date = date("Y-m-d H:i:s");

    if (empty($document_name_original) || empty($document_type_id) || !isset($_FILES['document_file']['name']) || $_FILES['document_file']['error'] == UPLOAD_ERR_NO_FILE || empty($document_version)) {
        $upload_messages[] = ['type' => 'error', 'text' => 'Nama dokumen, jenis dokumen, versi, dan file wajib diisi.'];
    } elseif (!$uploaded_by_id) {
        $upload_messages[] = ['type' => 'error', 'text' => 'Sesi pengguna tidak valid. Silakan login kembali.'];
    } else {
        if (!defined('UPLOAD_DIR')) {
            $upload_messages[] = ['type' => 'error', 'text' => 'Konstanta UPLOAD_DIR tidak terdefinisi.'];
        } else {
            $target_dir_relative = UPLOAD_DIR;
            if (!defined('PROJECT_ROOT_PATH')) {
                $upload_messages[] = ['type' => 'error', 'text' => 'Konstanta PROJECT_ROOT_PATH tidak terdefinisi di config.php.'];
            } else {
                $target_dir_absolute = rtrim(PROJECT_ROOT_PATH, '/') . '/' . trim($target_dir_relative, '/') . '/';
                if (!is_dir($target_dir_absolute)) {
                    if (!mkdir($target_dir_absolute, 0755, true)) {
                        $upload_messages[] = ['type' => 'error', 'text' => "Gagal membuat direktori unggahan: " . escape_html($target_dir_absolute)];
                    }
                }
                if (empty($upload_messages) && is_dir($target_dir_absolute) && is_writable($target_dir_absolute)) {
                    $file_tmp_name = $_FILES["document_file"]["tmp_name"];
                    $file_original_name = basename($_FILES["document_file"]["name"]);
                    $file_size = $_FILES["document_file"]["size"];
                    $file_mime_type = mime_content_type($file_tmp_name);
                    $file_extension = strtolower(pathinfo($file_original_name, PATHINFO_EXTENSION));
                    $unique_file_name = date("YmdHis") . "_" . uniqid() . "." . $file_extension;
                    $target_file_path_relative = trim($target_dir_relative, '/') . '/' . $unique_file_name;
                    $target_file_path_absolute = "$target_dir_absolute$unique_file_name";
                    $allowed_extensions = ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "jpg", "jpeg", "png", "vsdx", "drawio", "pkt"];
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $upload_messages[] = ['type' => 'error', 'text' => 'Maaf, hanya format file ' . implode(", ", $allowed_extensions) . ' yang diizinkan. Ekstensi Anda: ' . $file_extension];
                    } elseif ($file_size > 50000000) {
                        $upload_messages[] = ['type' => 'error', 'text' => 'Maaf, ukuran file Anda terlalu besar. Maksimal 50MB.'];
                    } elseif ($file_size === 0) {
                        $upload_messages[] = ['type' => 'error', 'text' => 'File yang diunggah kosong atau rusak.'];
                    } else {
                        if (move_uploaded_file($file_tmp_name, $target_file_path_absolute)) {
                            try {
                                $sql = "INSERT INTO dokumen (nama_dokumen_asli, nama_file_unik, path_file, id_tipe_dokumen, versi_dokumen, lokasi_perangkat, deskripsi, ukuran_file, tipe_file_mime, id_pengguna_unggah, tanggal_unggah)
                                        VALUES (:nama_dokumen_asli, :nama_file_unik, :path_file, :id_tipe_dokumen, :versi_dokumen, :lokasi_perangkat, :deskripsi, :ukuran_file, :tipe_file_mime, :id_pengguna_unggah, :tanggal_unggah)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':nama_dokumen_asli', $document_name_original);
                                $stmt->bindParam(':nama_file_unik', $unique_file_name);
                                $stmt->bindParam(':path_file', $target_file_path_relative);
                                $stmt->bindParam(':id_tipe_dokumen', $document_type_id, PDO::PARAM_INT);
                                $stmt->bindParam(':versi_dokumen', $document_version);
                                $stmt->bindParam(':lokasi_perangkat', $device_location);
                                $stmt->bindParam(':deskripsi', $description);
                                $stmt->bindParam(':ukuran_file', $file_size, PDO::PARAM_INT);
                                $stmt->bindParam(':tipe_file_mime', $file_mime_type);
                                $stmt->bindParam(':id_pengguna_unggah', $uploaded_by_id, PDO::PARAM_INT);
                                $stmt->bindParam(':tanggal_unggah', $upload_date);
                                if ($stmt->execute()) {
                                    $upload_messages[] = ['type' => 'success', 'text' => 'Dokumen "' . escape_html($document_name_original) . '" berhasil diunggah dan disimpan ke database.'];
                                    if (defined('ADMIN_UPLOAD_SIGNAL_FILE')) {
                                        file_put_contents(ADMIN_UPLOAD_SIGNAL_FILE, time());
                                    }
                                } else {
                                    $upload_messages[] = ['type' => 'error', 'text' => 'Gagal menyimpan metadata dokumen ke database.'];
                                    if (file_exists($target_file_path_absolute)) unlink($target_file_path_absolute);
                                }
                            } catch (PDOException $e) {
                                $upload_messages[] = ['type' => 'error', 'text' => 'Database error saat menyimpan: ' . $e->getMessage()];
                                if (file_exists($target_file_path_absolute)) unlink($target_file_path_absolute);
                            }
                        } else {
                            $upload_messages[] = ['type' => 'error', 'text' => 'Maaf, terjadi kesalahan saat memindahkan file unggahan. Kode Error PHP: ' . $_FILES['document_file']['error']];
                        }
                    }
                } elseif (is_dir($target_dir_absolute) && !is_writable($target_dir_absolute)) {
                    $upload_messages[] = ['type' => 'error', 'text' => "Direktori unggahan tidak writable: " . escape_html($target_dir_absolute)];
                }
            }
        }
    }
    $_SESSION['flash_messages'] = $upload_messages;
    header("Location: manage_documents.php");
    exit;
}

// Delete logic
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_messages = [];
    $document_id_to_delete = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($document_id_to_delete === false || $document_id_to_delete <= 0) {
        $delete_messages[] = ['type' => 'error', 'text' => 'ID Dokumen tidak valid.'];
    } else {
        try {
            $pdo->beginTransaction();
            $stmt_select = $pdo->prepare("SELECT path_file FROM dokumen WHERE id_dokumen = :id_dokumen");
            $stmt_select->bindParam(':id_dokumen', $document_id_to_delete, PDO::PARAM_INT);
            $stmt_select->execute();
            $doc_to_delete = $stmt_select->fetch();
            if ($doc_to_delete) {
                if (!defined('PROJECT_ROOT_PATH')) {
                    throw new Exception("Konstanta PROJECT_ROOT_PATH tidak terdefinisi untuk penghapusan file.");
                }
                $file_path_to_delete_absolute = rtrim(PROJECT_ROOT_PATH, '/') . '/' . trim($doc_to_delete['path_file'], '/');
                $stmt_delete = $pdo->prepare("DELETE FROM dokumen WHERE id_dokumen = :id_dokumen");
                $stmt_delete->bindParam(':id_dokumen', $document_id_to_delete, PDO::PARAM_INT);
                if ($stmt_delete->execute()) {
                    $file_deleted_successfully = false;
                    $file_exists_before_delete = file_exists($file_path_to_delete_absolute);
                    if ($file_exists_before_delete) {
                        if (unlink($file_path_to_delete_absolute)) {
                            $file_deleted_successfully = true;
                            $delete_messages[] = ['type' => 'success', 'text' => 'Dokumen berhasil dihapus dari database dan filesystem.'];
                        } else {
                            $delete_messages[] = ['type' => 'warning', 'text' => 'Dokumen berhasil dihapus dari database, tetapi GAGAL menghapus file fisik: ' . escape_html($file_path_to_delete_absolute) . '. Periksa izin file/direktori.'];
                        }
                    } else {
                        $delete_messages[] = ['type' => 'warning', 'text' => 'Dokumen berhasil dihapus dari database, tetapi file fisik tidak ditemukan (mungkin sudah terhapus atau path salah): ' . escape_html($file_path_to_delete_absolute)];
                    }
                    $pdo->commit();
                    if (defined('ADMIN_UPLOAD_SIGNAL_FILE')) {
                        file_put_contents(ADMIN_UPLOAD_SIGNAL_FILE, time());
                    }
                } else {
                    $pdo->rollBack();
                    $delete_messages[] = ['type' => 'error', 'text' => 'Gagal menghapus dokumen dari database.'];
                }
            } else {
                $delete_messages[] = ['type' => 'error', 'text' => 'Dokumen dengan ID ' . escape_html($document_id_to_delete) . ' tidak ditemukan.'];
                if ($pdo->inTransaction()) $pdo->rollBack();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $delete_messages[] = ['type' => 'error', 'text' => 'Database error saat menghapus: ' . $e->getMessage()];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $delete_messages[] = ['type' => 'error', 'text' => 'Kesalahan konfigurasi saat menghapus: ' . $e->getMessage()];
        }
    }
    $_SESSION['flash_messages'] = $delete_messages;
    header("Location: manage_documents.php");
    exit;
}

// Search/filter logic
$search_keyword = trim($_GET['search_keyword'] ?? '');
$filter_type_id = $_GET['filter_type_id'] ?? '';
$sql_documents = "SELECT d.*, td.nama_tipe AS nama_tipe_dokumen, p.nama_lengkap AS nama_pengunggah
                  FROM dokumen d
                  JOIN tipe_dokumen td ON d.id_tipe_dokumen = td.id_tipe_dokumen
                  JOIN pengguna p ON d.id_pengguna_unggah = p.id_pengguna";
$params = [];
$where_clauses = [];
if (!empty($search_keyword)) {
    $where_clauses[] = "(d.nama_dokumen_asli LIKE :keyword OR d.lokasi_perangkat LIKE :keyword OR d.deskripsi LIKE :keyword OR p.nama_lengkap LIKE :keyword)";
    $params[':keyword'] = "%$search_keyword%";
}
if (!empty($filter_type_id) && is_numeric($filter_type_id)) {
    $where_clauses[] = "d.id_tipe_dokumen = :filter_type_id";
    $params[':filter_type_id'] = $filter_type_id;
}
if (!empty($where_clauses)) {
    $sql_documents .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql_documents .= " ORDER BY d.tanggal_unggah DESC";

$documents = [];
if (isset($pdo)) {
    try {
        $stmt_docs = $pdo->prepare($sql_documents);
        $stmt_docs->execute($params);
        $documents = $stmt_docs->fetchAll();
    } catch (PDOException $e) {
        $messages[] = ['type' => 'error', 'text' => 'Gagal mengambil daftar dokumen: ' . $e->getMessage()];
    }
} else {
    if(empty($messages)) {
        $messages[] = ['type' => 'error', 'text' => 'Koneksi database tidak tersedia untuk mengambil daftar dokumen.'];
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
    $messages[] = ['type' => 'warning', 'text' => 'Konstanta BASE_URL tidak terdefinisi di config.php. Link unduhan mungkin tidak berfungsi dengan benar. Harap definisikan sebagai URL root aplikasi Anda (misal: http://localhost/proyekku/).'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape_html($pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
:root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --primary-dark: #1e293b;
    --accent: #fbbf24;
    --bg: #f7f9fb;
    --card: #fff;
    --border: #e5e7eb;
    --text: #22223b;
    --muted: #6b7280;
    --success-bg: #e0f7ef;
    --success-text: #059669;
    --error-bg: #ffe5e9;
    --error-text: #e11d48;
    --warning-bg: #fffbeb;
    --warning-text: #d97706;
    --radius: 14px;
    --shadow: 0 4px 24px rgba(37,99,235,0.08), 0 1.5px 4px rgba(30,41,59,0.06);
    --font-main: 'Inter', 'Segoe UI', Arial, sans-serif;
}
body {
    font-family: var(--font-main);
    background: var(--bg);
    color: var(--text);
    margin: 0;
    min-height: 100vh;
    line-height: 1.7;
}
header {
    background: linear-gradient(90deg, var(--primary-dark) 0%, var(--primary) 70%, var(--primary-light) 100%);
    color: #fff;
    padding: 40px 20px 28px 20px;
    text-align: center;
    border-radius: 0 0 var(--radius) var(--radius);
    box-shadow: var(--shadow);
}
header h1 {
    margin: 0 0 10px 0;
    font-size: 2.2em;
    font-weight: 700;
    letter-spacing: -1px;
}
header p {
    margin: 0;
    font-size: 1.08em;
    color: #e0e7ef;
    font-weight: 400;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
nav {
    background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 0.7em 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}
nav .nav-brand {
    font-weight: 700;
    color: var(--primary);
    font-size: 1.25em;
    text-decoration: none;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}
nav .nav-links {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
nav .nav-links span {
    color: var(--muted);
    font-size: 0.97em;
    margin-right: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}
nav .nav-links a {
    color: var(--primary-dark);
    text-decoration: none;
    font-weight: 600;
    padding: 9px 18px;
    border-radius: var(--radius);
    transition: background 0.18s, color 0.18s;
    font-size: 1em;
    display: flex;
    align-items: center;
    gap: 7px;
}
nav .nav-links a:hover, nav .nav-links a.active {
    background: var(--primary-light);
    color: #fff;
}
nav .nav-links a i {
    font-size: 1.1em;
}

.container {
    width: 96%;
    max-width: 1280px;
    margin: 36px auto 0 auto;
    padding: 0;
}
.content-section {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 32px 32px 28px 32px;
    margin-bottom: 36px;
    border: 1px solid var(--border);
}
.content-section h2 {
    color: var(--primary);
    margin-top: 0;
    font-size: 1.45em;
    font-weight: 700;
    border-bottom: 2px solid var(--accent);
    padding-bottom: 13px;
    margin-bottom: 28px;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 22px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    color: var(--primary-dark);
    font-size: 1em;
}
.form-group input[type="text"],
.form-group input[type="file"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 13px 16px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    background: #f9fafb;
    font-size: 1em;
    transition: border-color 0.18s, box-shadow 0.18s;
    box-sizing: border-box;
    color: var(--text);
}
.form-group input[type="text"]:focus,
.form-group input[type="file"]:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary-light);
    outline: none;
    box-shadow: 0 0 0 2.5px rgba(59, 130, 246, 0.13);
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}
.form-group small {
    font-size: 0.89em;
    color: var(--muted);
    display: block;
    margin-top: 6px;
}
.required-star {
    color: #ef4444;
    font-weight: bold;
    margin-left: 2px;
}
.btn-submit, .btn-filter, .search-filter a.reset-filter {
    background: var(--primary);
    color: #fff;
    padding: 13px 28px;
    border: none;
    border-radius: var(--radius);
    font-size: 1.08em;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.18s, box-shadow 0.18s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 9px;
}
.btn-submit:hover, .btn-filter:hover, .search-filter a.reset-filter:hover {
    background: var(--primary-light);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.btn-submit i, .btn-filter i, .search-filter a.reset-filter i {
    font-size: 1.13em;
}
.messages { margin-bottom: 22px; }
.messages div {
    padding: 15px 22px;
    margin-bottom: 13px;
    border-radius: var(--radius);
    border: 1.5px solid transparent;
    font-size: 1em;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}
.messages div::before {
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 1.25em;
}
.messages .success { background: var(--success-bg); color: var(--success-text); border-color: #b9fbc0; }
.messages .success::before { content: "\f058"; }
.messages .error { background: var(--error-bg); color: var(--error-text); border-color: #fda4af; }
.messages .error::before { content: "\f071"; }
.messages .warning { background: var(--warning-bg); color: var(--warning-text); border-color: #fde68a; }
.messages .warning::before { content: "\f06a"; }

.search-filter-form {
    margin-bottom: 28px;
    padding: 22px;
    background: #f8fafc;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    align-items: flex-end;
}
.search-filter-form .form-group {
    flex: 1 1 220px;
    margin-bottom: 0;
}
.search-filter-form .btn-filter, .search-filter-form a.reset-filter {
    margin-top: 25px;
    height: 48px;
}
.search-filter-form a.reset-filter {
    background: var(--border);
    color: var(--primary-dark);
}
.search-filter-form a.reset-filter:hover {
    background: #d1d5db;
}
.table-responsive {
    overflow-x: auto;
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1.5px solid var(--border);
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.99em;
    min-width: 900px;
}
th, td {
    padding: 13px 16px;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1.5px solid var(--border);
}
th {
    background: #f3f6fa;
    color: var(--primary-dark);
    font-weight: 700;
    white-space: nowrap;
    font-size: 1em;
}
tr:last-child td { border-bottom: none; }
tr:hover { background-color: #f1f5f9; }
td.actions {
    white-space: nowrap;
    text-align: right;
}
.actions a, .actions button {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: var(--radius);
    font-size: 0.97em;
    font-weight: 500;
    border: 1.5px solid transparent;
    cursor: pointer;
    transition: all 0.18s;
    margin-left: 6px;
    gap: 6px;
}
.actions a i, .actions button i { margin-right: 5px; }
.actions .view { background-color: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
.actions .view:hover { background-color: #c7d2fe; }
.actions .download { background-color: var(--success-bg); color: var(--success-text); border-color: #b9fbc0; }
.actions .download:hover { background-color: #a7f3d0; }
.actions .edit { background-color: var(--warning-bg); color: var(--warning-text); border-color: #fde68a; }
.actions .edit:hover { background-color: #fef08a; }
.actions .delete { background-color: var(--error-bg); color: var(--error-text); border-color: #fda4af; }
.actions .delete:hover { background-color: #fecaca; }
.actions .disabled {
    background-color: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
    border-color: #d1d5db;
}
.actions .disabled:hover { background-color: #e5e7eb; }
small.file-info { color: var(--muted); font-size: 0.89em; }
.total-docs {
    margin-top: 18px;
    font-weight: 500;
    color: var(--muted);
    font-size: 1em;
}
@media (max-width: 1024px) {
    .container { max-width: 98vw; }
    .content-section { padding: 22px 10px 18px 10px; }
    .form-grid { grid-template-columns: 1fr; }
    table { min-width: 700px; }
}
@media (max-width: 768px) {
    header h1 { font-size: 1.5em; }
    header p { font-size: 0.97em; }
    nav { flex-direction: column; align-items: stretch; padding: 0.7em 10px; }
    nav .nav-brand { margin-bottom: 10px; width: 100%; text-align: center; justify-content: center; }
    nav .nav-links { width: 100%; justify-content: center; }
    .container { width: 99vw; padding: 0 2vw; }
    .content-section { padding: 16px 4vw 12px 4vw; }
    .form-grid { grid-template-columns: 1fr; }
    .search-filter-form { flex-direction: column; align-items: stretch; gap: 10px; padding: 12px; }
    .search-filter-form .form-group { width: 100%; }
    .search-filter-form .btn-filter, .search-filter-form a.reset-filter { width: 100%; margin-top: 10px; justify-content: center;}
    td.actions { display: flex; flex-direction: column; gap: 7px; align-items: flex-start; }
    .actions a, .actions button { width: 100%; justify-content: center; margin-left: 0; }
    table { min-width: 500px; }
}
@media (max-width: 600px) {
    .container { width: 100vw; padding: 0 1vw; }
    .content-section { padding: 8px 2vw 8px 2vw; }
    table { font-size: 0.93em; min-width: 350px; }
    th, td { padding: 8px 7px; }
}
footer {
    background: var(--primary-dark);
    color: #e5e7eb;
    text-align: center;
    padding: 28px 10px 18px 10px;
    margin-top: 44px;
    font-size: 0.97em;
    border-radius: var(--radius) var(--radius) 0 0;
}
footer a { color: var(--accent); text-decoration: none; }
footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header>
        <h1><?= escape_html($pageTitle); ?></h1>
        <p><?= escape_html($systemTitle); ?></p>
    </header>
    <nav>
        <a href="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.' ?>/dashboard.php" class="nav-brand">
            <i class="fa fa-archive"></i> Arsip Digital Wifiku
        </a>
        <div class="nav-links">
            <span><i class="fa fa-user-circle"></i> <?= escape_html($loggedInUserName); ?></span>
            <a href="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.' ?>/admin/manage_documents.php" class="active"><i class="fa fa-folder-open"></i> Dokumen</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.' ?>/admin/user_management.php"><i class="fa fa-users-cog"></i> Pengguna</a>
            <a href="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.' ?>/admin/settings.php"><i class="fa fa-cogs"></i> Pengaturan</a>
            <?php endif; ?>
            <a href="<?= defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.' ?>/projectroot/index.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($messages)): ?>
            <div class="messages">
                <?php foreach ($messages as $message): ?>
                    <div class="<?= escape_html($message['type']); ?>">
                        <?= escape_html($message['text']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="content-section">
            <h2><i class="fa fa-plus-circle"></i> Tambah Dokumen Teknis Baru</h2>
            <form action="manage_documents.php" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="document_name">Nama Dokumen <span class="required-star">*</span></label>
                        <input type="text" id="document_name" name="document_name" required>
                    </div>
                    <div class="form-group">
                        <label for="document_type_id">Jenis Dokumen <span class="required-star">*</span></label>
                        <select id="document_type_id" name="document_type_id" required>
                            <option value="">-- Pilih Jenis Dokumen --</option>
                            <?php foreach ($db_document_types as $type): ?>
                            <option value="<?= escape_html($type['id_tipe_dokumen']); ?>"><?= escape_html($type['nama_tipe']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="document_version">Versi Dokumen <span class="required-star">*</span></label>
                        <input type="text" id="document_version" name="document_version" value="1.0" required placeholder="Contoh: 1.0, 1.1, 2.0-alpha">
                    </div>
                    <div class="form-group">
                        <label for="device_location">Perangkat/Lokasi Terkait</label>
                        <input type="text" id="device_location" name="device_location" placeholder="Contoh: Router Core Gedung A">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi Singkat / Catatan Perubahan</label>
                    <textarea id="description" name="description" placeholder="Deskripsi singkat mengenai dokumen atau catatan perubahan versi..."></textarea>
                </div>
                <div class="form-group">
                    <label for="document_file">Pilih File Dokumen <span class="required-star">*</span></label>
                    <input type="file" id="document_file" name="document_file" required>
                    <small>Format: PDF, DOC(X), XLS(X), PPT(X), TXT, JPG, PNG, VSDX, DRAWIO, PKT. Maks 50MB.</small>
                </div>
                <button type="submit" name="submit_document" class="btn-submit"><i class="fa fa-upload"></i> Unggah Dokumen</button>
            </form>
        </div>

        <div class="content-section">
            <h2><i class="fa fa-list-alt"></i> Daftar Dokumen Teknis Jaringan</h2>
            <form action="manage_documents.php" method="get" class="search-filter-form">
                <div class="form-group">
                    <label for="search_keyword">Kata Kunci</label>
                    <input type="text" id="search_keyword" name="search_keyword" placeholder="Cari nama, lokasi, deskripsi..." value="<?= escape_html($search_keyword); ?>">
                </div>
                <div class="form-group">
                    <label for="filter_type_id">Jenis Dokumen</label>
                    <select id="filter_type_id" name="filter_type_id">
                        <option value="">-- Semua Jenis --</option>
                        <?php foreach ($db_document_types as $type): ?>
                        <option value="<?= escape_html($type['id_tipe_dokumen']); ?>" <?= ($filter_type_id == $type['id_tipe_dokumen']) ? 'selected' : ''; ?>>
                            <?= escape_html($type['nama_tipe']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Filter</button>
                <a href="manage_documents.php" class="reset-filter"><i class="fa fa-sync-alt"></i> Reset</a>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Dokumen</th>
                            <th>Jenis</th>
                            <th>Lokasi/Perangkat</th>
                            <th>Versi</th>
                            <th>Tgl Unggah</th>
                            <th>Pengunggah</th>
                            <th>Ukuran</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php $counter = 1; ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><?= $counter++; ?></td>
                                    <td><?= escape_html($doc['nama_dokumen_asli']); ?><br><small class="file-info"><?= escape_html($doc['deskripsi'] ?: 'Tidak ada deskripsi'); ?></small></td>
                                    <td><?= escape_html($doc['nama_tipe_dokumen']); ?></td>
                                    <td><?= escape_html($doc['lokasi_perangkat'] ?: '-'); ?></td>
                                    <td><?= escape_html($doc['versi_dokumen']); ?></td>
                                    <td><?= escape_html(date('d M Y, H:i', strtotime($doc['tanggal_unggah']))); ?></td>
                                    <td><?= escape_html($doc['nama_pengunggah']); ?></td>
                                    <td><?= ($doc['ukuran_file'] > 0) ? round($doc['ukuran_file'] / 1024, 1) . ' KB' : '0 KB'; ?></td>
                                    <td class="actions">
                                        <?php
                                            $download_url = rtrim(BASE_URL, '/') . '/' . ltrim($doc['path_file'], '/');
                                        ?>
                                        <a href="<?= escape_html($download_url); ?>" download="<?= escape_html($doc['nama_dokumen_asli']); ?>" class="download" title="Unduh Dokumen"><i class="fa fa-download"></i> Unduh</a>
                                        <a href="edit_document.php?id=<?= $doc['id_dokumen']; ?>" class="edit" title="Edit Metadata"><i class="fa fa-edit"></i> Edit</a>
                                        <a href="manage_documents.php?action=delete&id=<?= $doc['id_dokumen']; ?>" class="delete" title="Hapus Dokumen" onclick="return confirm('PERINGATAN!\nAnda akan menghapus dokumen \'<?= escape_html(addslashes($doc['nama_dokumen_asli'])); ?>\' secara permanen.\n\nOperasi ini tidak dapat dibatalkan.\nLanjutkan?');"><i class="fa fa-trash-alt"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding: 20px;">Belum ada dokumen yang diarsipkan atau tidak ditemukan berdasarkan filter Anda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="total-docs">Total Dokumen: <?= count($documents); ?></p>
        </div>
    </div>

    <footer>
        &copy; <?= date("Y"); ?> PT Wifiku Indonesia. All rights reserved. <br>
        Pengembangan Sistem Pengarsipan Digital Dokumen Teknis Jaringan. <a href="#">Kontak Dukungan</a>.
    </footer>
</body>
</html>
