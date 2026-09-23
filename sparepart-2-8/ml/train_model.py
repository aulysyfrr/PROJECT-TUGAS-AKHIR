"""
train_model.py
================
Melatih model Decision Tree untuk prediksi risiko kerusakan alat berat,
mengevaluasi performanya, lalu menyimpan model + encoder ke file .pkl
supaya bisa dipakai oleh predict_kerusakan.py / Flask API.

Jalankan:  python3 train_model.py
Output   :  model_decision_tree.pkl, label_encoders.pkl, evaluation_report.txt
"""

import json
import joblib
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.tree import DecisionTreeClassifier, export_text
from sklearn.metrics import (accuracy_score, confusion_matrix,
                              classification_report, precision_score,
                              recall_score, f1_score)

FEATURES = ['umur_alat', 'jam_operasional', 'kondisi_terakhir',
            'jumlah_perbaikan', 'lokasi_operasi', 'jenis_alat']
TARGET = 'label_risiko'
CATEGORICAL = ['kondisi_terakhir', 'lokasi_operasi', 'jenis_alat']

df = pd.read_csv('dataset_alat_berat.csv')
print(f"Total data: {len(df)} baris")

# ---- Encode kolom kategorikal jadi numerik ----
encoders = {}
df_enc = df.copy()
for col in CATEGORICAL:
    le = LabelEncoder()
    df_enc[col] = le.fit_transform(df_enc[col])
    encoders[col] = le

target_encoder = LabelEncoder()
df_enc[TARGET] = target_encoder.fit_transform(df_enc[TARGET])
encoders[TARGET] = target_encoder

X = df_enc[FEATURES]
y = df_enc[TARGET]

# ---- Split 80:20 (sesuai proposal) ----
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)
print(f"Data latih : {len(X_train)} baris")
print(f"Data uji   : {len(X_test)} baris")

# ---- Training ----
# max_depth dibatasi supaya pohon tetap mudah diinterpretasikan
# (salah satu alasan utama pemilihan Decision Tree di proposal).
model = DecisionTreeClassifier(
    criterion='entropy',
    max_depth=6,
    min_samples_leaf=5,
    random_state=42
)
model.fit(X_train, y_train)

# ---- Evaluasi ----
y_pred = model.predict(X_test)

acc = accuracy_score(y_test, y_pred)
cm = confusion_matrix(y_test, y_pred)
report = classification_report(y_test, y_pred, target_names=target_encoder.classes_)
prec = precision_score(y_test, y_pred, average='weighted')
rec = recall_score(y_test, y_pred, average='weighted')
f1 = f1_score(y_test, y_pred, average='weighted')

print("\n=== HASIL EVALUASI MODEL ===")
print(f"Accuracy  : {acc:.4f} ({acc*100:.2f}%)")
print(f"Precision : {prec:.4f}")
print(f"Recall    : {rec:.4f}")
print(f"F1-Score  : {f1:.4f}")
print("\nConfusion Matrix (baris=aktual, kolom=prediksi):")
print(f"Urutan kelas: {list(target_encoder.classes_)}")
print(cm)
print("\nClassification Report:")
print(report)

# ---- Simpan model & encoder ----
joblib.dump(model, 'model_decision_tree.pkl')
joblib.dump(encoders, 'label_encoders.pkl')
joblib.dump(FEATURES, 'feature_order.pkl')

# ---- Simpan laporan evaluasi ke file teks (untuk lampiran BAB IV) ----
with open('evaluation_report.txt', 'w') as f:
    f.write("LAPORAN EVALUASI MODEL DECISION TREE\n")
    f.write("Sistem Prediksi Risiko Kerusakan Alat Berat - sparepart-2\n")
    f.write("=" * 60 + "\n\n")
    f.write(f"Jumlah data total   : {len(df)}\n")
    f.write(f"Jumlah data latih   : {len(X_train)} (80%)\n")
    f.write(f"Jumlah data uji     : {len(X_test)} (20%)\n")
    f.write(f"Fitur yang digunakan: {FEATURES}\n")
    f.write(f"Kelas target        : {list(target_encoder.classes_)}\n\n")
    f.write(f"Accuracy  : {acc:.4f} ({acc*100:.2f}%)\n")
    f.write(f"Precision : {prec:.4f}\n")
    f.write(f"Recall    : {rec:.4f}\n")
    f.write(f"F1-Score  : {f1:.4f}\n\n")
    f.write("Confusion Matrix (baris=aktual, kolom=prediksi):\n")
    f.write(f"Urutan kelas: {list(target_encoder.classes_)}\n")
    f.write(str(cm) + "\n\n")
    f.write("Classification Report:\n")
    f.write(report + "\n")
    f.write("\nStruktur Pohon Keputusan (lengkap sampai daun terakhir):\n")
    f.write(export_text(model, feature_names=FEATURES))

print("\nModel & encoder tersimpan: model_decision_tree.pkl, label_encoders.pkl")
print("Laporan evaluasi tersimpan: evaluation_report.txt")