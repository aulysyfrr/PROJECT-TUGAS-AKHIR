"""
generate_dataset.py
====================
Membuat dataset training untuk model Decision Tree prediksi risiko
kerusakan alat berat.

KENAPA DATA INI DIBUTUHKAN:
Sistem sparepart-2 belum memiliki data historis kerusakan aktual
(real failure records) yang berlabel, karena perusahaan (PT. Sarana
Karya Dua Satu) belum pernah mencatat "alat ini akhirnya rusak / tidak"
secara sistematis. Untuk tetap bisa melatih model Machine Learning
secara sah (supervised learning butuh label), label risiko pada data
training di bawah ini DIHASILKAN menggunakan sistem skor berbasis
aturan (rule-based scoring) yang telah dirancang berdasarkan pedoman
perawatan pabrikan alat berat (Komatsu, Caterpillar) dan referensi
akademis terkait threshold jam operasional & usia alat.

Pendekatan ini disebut "weak supervision" / rule-based label
generation -- teknik yang lazim dipakai ketika data historis berlabel
belum tersedia. Model Decision Tree kemudian dilatih untuk MEMPELAJARI
pola dari aturan tersebut, sehingga ke depannya model bisa
digeneralisasi ulang begitu data kerusakan aktual sudah terkumpul
lebih banyak dari histori alat_berat_history.

Ini harus dijelaskan secara eksplisit & jujur di BAB IV laporan TA --
JANGAN diklaim sebagai data historis kerusakan asli.
"""

import random
import csv

random.seed(42)

JENIS_OPTIONS = ['Excavator', 'Backhoe Loader', 'Bulldozer', 'Motor Grader',
                  'Compactor', 'Crane', 'Forklift', 'Wheel Loader', 'Dump Truck', 'Lainnya']
KONDISI_OPTIONS = ['Baik', 'Rusak', 'Dalam Perbaikan']
LOKASI_OPTIONS = ['Risiko Rendah', 'Risiko Sedang', 'Risiko Tinggi']
TAHUN_SEKARANG = 2026


def kategori_umur(umur):
    if umur <= 7:
        return 'baru', 0
    elif umur <= 12:
        return 'normal', 3
    else:
        return 'tua', 6


def kategori_jam(jam):
    if jam < 3000:
        return 'normal', 0
    elif jam <= 5000:
        return 'waspada', 3
    else:
        return 'kritis', 5


def skor_kondisi(kondisi):
    return {'Baik': 0, 'Dalam Perbaikan': 2, 'Rusak': 4}[kondisi]


def skor_perbaikan(jml):
    if jml <= 2:
        return 0
    elif jml <= 6:
        return 2
    else:
        return 3


def skor_lokasi(lokasi):
    return {'Risiko Rendah': 0, 'Risiko Sedang': 1, 'Risiko Tinggi': 2}[lokasi]


def skor_jenis(jenis):
    return 1 if jenis in ('Crane', 'Bulldozer') else 0


SKOR_MAKS = 21  # 6+5+4+3+2+1


def label_risiko(tahun_unit, jam_operasional, kondisi, jumlah_perbaikan, lokasi, jenis):
    """Sama persis dengan logika predictRisiko() di alat_berat_tambah.php,
    supaya label training konsisten dengan aturan yang sudah berjalan
    di sistem sekarang."""
    umur = TAHUN_SEKARANG - tahun_unit
    kat_umur, skor_umur = kategori_umur(umur)
    kat_jam, skor_jam = kategori_jam(jam_operasional)

    skor = (skor_umur + skor_jam + skor_kondisi(kondisi) +
            skor_perbaikan(jumlah_perbaikan) + skor_lokasi(lokasi) + skor_jenis(jenis))
    skor_tampil = round((skor / SKOR_MAKS) * 10)

    # Aturan override: Tahun Unit Tua ATAU Jam Kritis -> otomatis Tinggi
    if kat_umur == 'tua' or kat_jam == 'kritis':
        return 'Risiko Tinggi'
    elif skor_tampil <= 3:
        return 'Risiko Rendah'
    elif skor_tampil <= 7:
        return 'Risiko Sedang'
    else:
        return 'Risiko Tinggi'


def generate(n=3000):
    rows = []
    for _ in range(n):
        tahun_unit = random.randint(2000, TAHUN_SEKARANG)
        jam_operasional = random.randint(0, 8000)
        kondisi = random.choice(KONDISI_OPTIONS)
        jumlah_perbaikan = random.randint(0, 10)
        lokasi = random.choice(LOKASI_OPTIONS)
        jenis = random.choice(JENIS_OPTIONS)

        umur = TAHUN_SEKARANG - tahun_unit
        label = label_risiko(tahun_unit, jam_operasional, kondisi,
                              jumlah_perbaikan, lokasi, jenis)

        rows.append({
            'umur_alat': umur,
            'jam_operasional': jam_operasional,
            'kondisi_terakhir': kondisi,
            'jumlah_perbaikan': jumlah_perbaikan,
            'lokasi_operasi': lokasi,
            'jenis_alat': jenis,
            'label_risiko': label,
        })
    return rows


if __name__ == '__main__':
    data = generate(3000)
    fields = ['umur_alat', 'jam_operasional', 'kondisi_terakhir',
              'jumlah_perbaikan', 'lokasi_operasi', 'jenis_alat', 'label_risiko']
    with open('dataset_alat_berat.csv', 'w', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=fields)
        writer.writeheader()
        writer.writerows(data)

    from collections import Counter
    dist = Counter(r['label_risiko'] for r in data)
    print(f"Dataset dibuat: {len(data)} baris -> dataset_alat_berat.csv")
    print("Distribusi label:", dict(dist))
