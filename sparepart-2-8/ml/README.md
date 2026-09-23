# Modul Machine Learning — Prediksi Risiko Kerusakan Alat Berat

Modul ini berisi implementasi **Decision Tree (Scikit-learn)** yang
sebelumnya dijanjikan di Proposal Tugas Akhir, menggantikan sistem
skor manual (rule-based) yang sempat dipakai sementara di sistem web.

## Catatan Kejujuran Data (WAJIB dibaca & dicantumkan di BAB IV)

PT. Sarana Karya Dua Satu belum memiliki data historis kerusakan alat
berat yang berlabel (real failure records). Karena supervised learning
membutuhkan label, dataset training (`dataset_alat_berat.csv`) dibuat
dengan cara **rule-based label generation / weak supervision**: label
risiko dihasilkan dari sistem aturan pakar (bobot Tahun Unit, Jam
Operasional, dst — merujuk pedoman perawatan Komatsu & Caterpillar),
BUKAN dari histori kerusakan aktual. Model Decision Tree kemudian
dilatih untuk mempelajari pola dari aturan tersebut.

Ini pendekatan yang sah dan lazim dipakai ketika data berlabel belum
tersedia, TAPI **harus dijelaskan secara eksplisit di laporan TA**
sebagai keterbatasan penelitian (bukan diklaim sebagai data historis
kerusakan asli). Ke depan, begitu tabel `alat_berat_history` terisi
data nyata dalam jumlah cukup, model bisa dilatih ulang dengan data
yang lebih valid.

## Struktur File

| File | Fungsi |
|---|---|
| `generate_dataset.py` | Membuat dataset training (3000 baris) dari aturan skor |
| `train_model.py` | Melatih Decision Tree, evaluasi, simpan model `.pkl` |
| `predict_kerusakan.py` | Flask API yang melayani prediksi (`/predict`) |
| `dataset_alat_berat.csv` | Dataset hasil generate |
| `model_decision_tree.pkl` | Model terlatih |
| `label_encoders.pkl` | Encoder untuk fitur & label kategorikal |
| `feature_order.pkl` | Urutan fitur yang dipakai model |
| `evaluation_report.txt` | Hasil evaluasi (accuracy, confusion matrix, dst) — untuk lampiran BAB IV |

## Cara Menjalankan

```bash
cd ml
pip install -r requirements.txt

# 1. Generate dataset (opsional, sudah ada hasilnya)
python3 generate_dataset.py

# 2. Latih ulang model (opsional, sudah ada hasilnya)
python3 train_model.py

# 3. Jalankan API — WAJIB, harus tetap jalan agar tombol
#    "Prediksikan Risiko" di web berfungsi
python3 predict_kerusakan.py
```

API akan berjalan di `http://127.0.0.1:5000`. Selama pengembangan/demo,
pastikan proses ini tetap berjalan (buka terminal terpisah) bersamaan
dengan XAMPP/Apache untuk PHP.

## Hasil Evaluasi Model (Real, dari `train_model.py`)

- **Accuracy**: 96.00%
- **Precision (weighted)**: 96.39%
- **Recall (weighted)**: 96.00%
- **F1-Score (weighted)**: 95.99%
- Data latih: 2400 baris (80%), Data uji: 600 baris (20%)

Detail lengkap (confusion matrix per kelas, classification report,
struktur pohon keputusan) ada di `evaluation_report.txt`.

## Integrasi dengan PHP

`database/proses_prediksi.php` menjembatani form web (Javascript) ke
API ini via `cURL`. Alur lengkap:

```
Tombol "Prediksikan Risiko" (JS fetch)
        -> proses_prediksi.php (PHP, cURL)
        -> predict_kerusakan.py (Flask, port 5000)
        -> model_decision_tree.pkl (Decision Tree)
        -> hasil balik ke PHP -> tampil di web
```

Kalau `predict_kerusakan.py` tidak berjalan, tombol prediksi di web
akan menampilkan pesan error yang jelas (bukan gagal diam-diam),
supaya gampang di-debug.
