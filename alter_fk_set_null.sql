-- ============================================
-- FILE: alter_fk_set_null.sql
-- Deskripsi: Ubah constraint Foreign Key agar
-- laporan TIDAK terhapus saat user dihapus.
-- Jalankan SEKALI di MySQL.
-- ============================================

USE lapor_sekolah;

-- ── STEP 1: Hapus constraint FK yang lama ──
-- Nama FK adalah 'fk_laporan_user' (sesuai saat CREATE dulu)
ALTER TABLE laporan_kerusakan
    DROP FOREIGN KEY fk_laporan_user;

-- ── STEP 2: Pasang kembali FK dengan ON DELETE SET NULL ──
-- Efek: jika user dihapus → user_id di laporan jadi NULL
-- Laporan TETAP ADA, tidak ikut terhapus.
ALTER TABLE laporan_kerusakan
    ADD CONSTRAINT fk_laporan_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Verifikasi hasilnya
SHOW CREATE TABLE laporan_kerusakan;
