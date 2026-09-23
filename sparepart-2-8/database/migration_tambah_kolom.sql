-- ============================================================
-- MIGRATION: Tambah Kolom Baru ke Tabel alat_berat
-- PT. Sarana Karya Dua Satu
-- ============================================================

USE sparepart_db;

-- Tambah kolom jam operasional
ALTER TABLE alat_berat 
    ADD COLUMN IF NOT EXISTS jam_operasional INT DEFAULT 0 COMMENT 'Total jam operasional (HM)' AFTER tahun_unit;

-- Tambah kolom kondisi terakhir
ALTER TABLE alat_berat 
    ADD COLUMN IF NOT EXISTS kondisi_terakhir ENUM('Baik','Sedang','Rusak Ringan','Rusak Berat','Dalam Perbaikan') DEFAULT 'Baik' COMMENT 'Kondisi terakhir alat' AFTER jam_operasional;

-- Tambah kolom lokasi operasi (terpisah dari lokasi aset)
ALTER TABLE alat_berat 
    ADD COLUMN IF NOT EXISTS lokasi_operasi VARCHAR(200) DEFAULT NULL COMMENT 'Lokasi operasi saat ini' AFTER lokasi;

-- Tambah kolom jumlah perbaikan (dihitung otomatis dari tabel riwayat)
ALTER TABLE alat_berat 
    ADD COLUMN IF NOT EXISTS jumlah_perbaikan INT DEFAULT 0 COMMENT 'Total jumlah perbaikan' AFTER lokasi_operasi;

-- ============================================================
-- TABEL RIWAYAT PERBAIKAN (baru)
-- ============================================================
CREATE TABLE IF NOT EXISTS riwayat_perbaikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alat_berat_id INT NOT NULL,
    tanggal_perbaikan DATE NOT NULL,
    jenis_perbaikan VARCHAR(200) NOT NULL COMMENT 'Misal: Ganti oli, Perbaikan hidrolik, dll',
    deskripsi TEXT DEFAULT NULL COMMENT 'Detail perbaikan',
    biaya BIGINT DEFAULT 0 COMMENT 'Biaya perbaikan (Rp)',
    teknisi VARCHAR(100) DEFAULT NULL COMMENT 'Nama teknisi / mekanik',
    vendor_bengkel VARCHAR(150) DEFAULT NULL COMMENT 'Nama vendor / bengkel',
    status ENUM('Selesai','Dalam Proses','Ditunda') DEFAULT 'Selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alat_berat_id) REFERENCES alat_berat(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Riwayat perbaikan alat berat';

-- ============================================================
-- INDEX untuk performa query
-- ============================================================
ALTER TABLE riwayat_perbaikan ADD INDEX idx_alat_berat_id (alat_berat_id);
ALTER TABLE riwayat_perbaikan ADD INDEX idx_tanggal (tanggal_perbaikan);
