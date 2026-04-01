-- ============================================
-- PROJECT: Lapor-Sekolah
-- Deskripsi: Query SQL untuk membuat database
-- dan tabel laporan_kerusakan
-- ============================================

-- 1. Buat dan pilih database
CREATE DATABASE IF NOT EXISTS lapor_sekolah
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lapor_sekolah;

-- 2. Buat tabel laporan_kerusakan
CREATE TABLE IF NOT EXISTS laporan_kerusakan (
    id            INT(11)       NOT NULL AUTO_INCREMENT,
    nama_pelapor  VARCHAR(100)  NOT NULL,
    fasilitas     VARCHAR(150)  NOT NULL,
    deskripsi     TEXT          NOT NULL,
    foto_bukti    VARCHAR(255)  DEFAULT NULL,
    status        ENUM('Menunggu', 'Diproses', 'Selesai') NOT NULL DEFAULT 'Menunggu',
    tanggal_lapor TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. (Opsional) Insert data contoh untuk testing
INSERT INTO laporan_kerusakan (nama_pelapor, fasilitas, deskripsi, status) VALUES
('Budi Santoso', 'Toilet Lantai 2', 'Kran air bocor dan tidak bisa dimatikan sepenuhnya.', 'Menunggu'),
('Siti Rahayu',  'Lab Komputer A',  'PC nomor 7 tidak bisa menyala, kemungkinan PSU rusak.', 'Diproses'),
('Ahmad Fauzi',  'Lapangan Basket', 'Ring basket sebelah timur miring dan hampir jatuh.', 'Selesai');
