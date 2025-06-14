-- Database: pengarsipan_digital

CREATE DATABASE pengarsipan_digital;

USE pengarsipan_digital;

-- Tabel Jabatan
CREATE TABLE jabatan (
    id_jabatan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jabatan VARCHAR(50) NOT NULL
);

INSERT INTO jabatan (nama_jabatan) VALUES
('Admin'),
('Teknisi'),
('Manajer'),
('Pengguna');

-- Tabel Pengguna
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_jabatan INT NOT NULL,
    FOREIGN KEY (id_jabatan) REFERENCES jabatan(id_jabatan)
);

-- Contoh data pengguna (password default: "password")
INSERT INTO pengguna (username, password, id_jabatan) VALUES
('admin_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('teknisi_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('manajer_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('pengguna_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4);