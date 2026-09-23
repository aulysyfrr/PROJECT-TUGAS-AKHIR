-- ============================================================
-- MIGRATION: Hapus Kolom tahun_pembelian & tahun_terjual
-- PT. Sarana Karya Dua Satu
-- ============================================================
-- Kolom ini sudah tidak dipakai lagi di form Tambah/Edit Data
-- Alat Berat, diganti fokus ke Tahun Unit + Hasil Prediksi
-- Risiko (Decision Tree). Migration ini permanen (DROP COLUMN),
-- pastikan sudah backup database sebelum menjalankan.
-- ============================================================

USE sparepart_db;

-- Tabel utama alat_berat
ALTER TABLE alat_berat
    DROP COLUMN IF EXISTS tahun_pembelian,
    DROP COLUMN IF EXISTS tahun_terjual;

-- Tabel arsip alat_berat_history (ikut disamakan)
ALTER TABLE alat_berat_history
    DROP COLUMN IF EXISTS tahun_pembelian,
    DROP COLUMN IF EXISTS tahun_terjual;
