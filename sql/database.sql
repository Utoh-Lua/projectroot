-- Buat Database (jika belum ada)
CREATE DATABASE IF NOT EXISTS pengarsipan_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pengarsipan_digital;

-- Tabel Jabatan
DROP TABLE IF EXISTS jabatan;
CREATE TABLE jabatan (
    id_jabatan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jabatan VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO jabatan (nama_jabatan) VALUES
('Administrator Sistem'),
('Manajer Jaringan'),
('Staf Teknisi Jaringan'),
('Pengguna Terdaftar');

-- Tabel Pengguna
DROP TABLE IF EXISTS pengguna;
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Simpan password yang sudah di-hash
    email VARCHAR(100) UNIQUE,
    id_jabatan INT NOT NULL,
    tanggal_registrasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aktif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan)
);

-- Contoh data pengguna (password default: "password123" setelah di-hash)
-- Gunakan password_hash() di PHP untuk membuat hash ini. Contoh: password_hash("password123", PASSWORD_DEFAULT)
-- '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' adalah hash untuk 'password'
INSERT INTO pengguna (nama_lengkap, username, password, email, id_jabatan) VALUES
('Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@wifiku.co.id', 1),
('Teknisi', 'teknisi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'budi@wifiku.co.id', 3),
('Manajer', 'manajer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siti@wifiku.co.id', 2);


-- Tabel Tipe Dokumen
DROP TABLE IF EXISTS tipe_dokumen;
CREATE TABLE tipe_dokumen (
    id_tipe_dokumen INT AUTO_INCREMENT PRIMARY KEY,
    nama_tipe VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT
);

INSERT INTO tipe_dokumen (nama_tipe, deskripsi) VALUES
('Topologi Jaringan', 'Diagram yang menggambarkan tata letak fisik atau logis jaringan.'),
('Konfigurasi Perangkat', 'File konfigurasi untuk perangkat jaringan seperti router, switch, firewall.'),
('Manual Teknis', 'Panduan pengguna atau manual servis untuk perangkat keras atau perangkat lunak.'),
('SOP Jaringan', 'Standard Operating Procedure terkait operasional dan pemeliharaan jaringan.'),
('Dokumen Proyek Jaringan', 'Dokumentasi terkait perencanaan, implementasi, dan penyelesaian proyek jaringan.'),
('Lisensi Perangkat Lunak Jaringan', 'Informasi lisensi untuk software jaringan.'),
('Laporan Audit Jaringan', 'Hasil audit keamanan atau performa jaringan.'),
('Diagram Rack Server', 'Visualisasi penempatan perangkat di dalam rack server.'),
('Lainnya', 'Dokumen teknis jaringan lainnya yang tidak masuk kategori di atas.');

-- Tabel Dokumen
DROP TABLE IF EXISTS dokumen;
CREATE TABLE dokumen (
    id_dokumen INT AUTO_INCREMENT PRIMARY KEY,
    nama_dokumen_asli VARCHAR(255) NOT NULL, -- Nama file asli yang diupload pengguna
    nama_file_unik VARCHAR(255) NOT NULL UNIQUE, -- Nama file yang disimpan di server (dengan timestamp/hash)
    path_file VARCHAR(512) NOT NULL, -- Path relatif ke file di server
    id_tipe_dokumen INT NOT NULL,
    versi_dokumen VARCHAR(20) DEFAULT '1.0',
    lokasi_perangkat VARCHAR(255),
    deskripsi TEXT,
    ukuran_file INT, -- Dalam bytes
    tipe_file_mime VARCHAR(100), -- e.g., application/pdf
    tanggal_unggah TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_modifikasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_pengguna_unggah INT NOT NULL,
    FOREIGN KEY (id_tipe_dokumen) REFERENCES tipe_dokumen(id_tipe_dokumen),
    FOREIGN KEY (id_pengguna_unggah) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

-- Indeks untuk pencarian
CREATE INDEX idx_nama_dokumen ON dokumen(nama_dokumen_asli);
CREATE INDEX idx_lokasi_perangkat ON dokumen(lokasi_perangkat);

-- (Opsional) Tabel Riwayat Versi Dokumen jika Anda ingin implementasi versioning yang lebih kompleks
-- CREATE TABLE riwayat_versi_dokumen (
--     id_riwayat INT AUTO_INCREMENT PRIMARY KEY,
--     id_dokumen INT NOT NULL,
--     versi VARCHAR(20) NOT NULL,
--     path_file_lama VARCHAR(512) NOT NULL,
--     catatan_perubahan TEXT,
--     id_pengguna_update INT NOT NULL,
--     tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (id_dokumen) REFERENCES dokumen(id_dokumen) ON DELETE CASCADE,
--     FOREIGN KEY (id_pengguna_update) REFERENCES pengguna(id_pengguna)
-- );