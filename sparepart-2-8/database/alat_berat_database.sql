CREATE DATABASE sparepart_db;
USE sparepart_db;

-- TABLE USER
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

-- TABLE SPAREPART
CREATE TABLE spareparts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tgl_invoice DATE,
    no_invoice VARCHAR(100),
    vendor VARCHAR(100),
    rencana_alokasi VARCHAR(100),
    project VARCHAR(50),
    part_number VARCHAR(100),
    nama_barang VARCHAR(150),
    qty INT,
    satuan VARCHAR(20),
    harga BIGINT,
    jumlah BIGINT,
    diskon BIGINT,
    pajak BIGINT,
    total BIGINT,
    lokasi VARCHAR(100),
    sisa INT
);
