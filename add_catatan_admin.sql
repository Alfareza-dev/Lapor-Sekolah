-- ============================================
-- FILE: add_catatan_admin.sql
-- Jalankan SEKALI di MySQL untuk menambahkan
-- kolom catatan_admin ke tabel laporan_kerusakan
-- ============================================

USE lapor_sekolah;

ALTER TABLE laporan_kerusakan
    ADD COLUMN catatan_admin TEXT NULL DEFAULT NULL
    COMMENT 'Catatan dari Admin untuk pelapor'
    AFTER status;

-- Verifikasi
DESCRIBE laporan_kerusakan;
