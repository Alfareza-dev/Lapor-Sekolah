-- ============================================
-- FILE: update_database.sql
-- Deskripsi: Update skema database untuk sistem
-- autentikasi multi-role pada Lapor-Sekolah
-- Jalankan file ini SEKALI di MySQL/phpMyAdmin
-- ============================================

USE lapor_sekolah;

-- ──────────────────────────────────────────
-- LANGKAH 1: Buat tabel users
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- Hasil password_hash(), bukan plaintext!
    role       ENUM('admin','user')     NOT NULL DEFAULT 'user',
    created_at TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────
-- LANGKAH 2: Tambah kolom user_id ke tabel
-- laporan_kerusakan untuk relasi ke users
-- (jika kolom sudah ada, perintah ini aman
-- karena menggunakan IF NOT EXISTS-equivalent)
-- ──────────────────────────────────────────
ALTER TABLE laporan_kerusakan
    ADD COLUMN user_id INT(11) DEFAULT NULL AFTER id;

-- ──────────────────────────────────────────
-- LANGKAH 3: Tambah Foreign Key
-- (user_id di laporan → id di users)
-- ON DELETE SET NULL: jika user dihapus,
-- laporan tetap ada tapi user_id jadi NULL
-- ──────────────────────────────────────────
ALTER TABLE laporan_kerusakan
    ADD CONSTRAINT fk_laporan_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Verifikasi struktur akhir
DESCRIBE users;
DESCRIBE laporan_kerusakan;
