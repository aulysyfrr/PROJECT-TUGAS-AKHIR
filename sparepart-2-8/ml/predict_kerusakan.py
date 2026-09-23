"""
predict_kerusakan.py
======================
Flask REST API yang menjembatani sistem web PHP dengan model
Decision Tree yang telah dilatih (model_decision_tree.pkl).

Alur (sesuai Diagram Konteks BAB III proposal):
  1. PHP (proses_prediksi.php) mengirim data alat berat via HTTP POST
     dalam format JSON ke endpoint /predict.
  2. Flask menerima & memvalidasi data, mengubahnya menjadi vektor
     fitur numerik menggunakan label encoder yang sama seperti saat
     training.
  3. Model Decision Tree memproses data dan menghasilkan klasifikasi
     risiko + confidence score.
  4. Flask mengembalikan hasil dalam format JSON ke PHP.

Menjalankan server:
  pip install flask flask-cors joblib scikit-learn pandas
  python3 predict_kerusakan.py
  -> berjalan di http://127.0.0.1:5000
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import os

app = Flask(__name__)
CORS(app)  # izinkan PHP (origin/port berbeda) mengakses API ini

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

model = joblib.load(os.path.join(BASE_DIR, 'model_decision_tree.pkl'))
encoders = joblib.load(os.path.join(BASE_DIR, 'label_encoders.pkl'))
feature_order = joblib.load(os.path.join(BASE_DIR, 'feature_order.pkl'))

TAHUN_SEKARANG = 2026


def safe_encode(encoder, value, field_name):
    """Encode nilai kategorikal; kalau ada nilai baru yang belum pernah
    dilihat model saat training, jangan crash -- fallback ke kelas
    yang paling umum supaya API tetap merespons."""
    try:
        return encoder.transform([value])[0]
    except ValueError:
        # nilai tidak dikenal -> pakai kelas pertama sebagai fallback
        return encoder.transform([encoder.classes_[0]])[0]


@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json(silent=True)
    if not data:
        return jsonify({'error': 'Body request harus JSON'}), 400

    required = ['tahun_unit', 'jam_operasional', 'kondisi_terakhir',
                'jumlah_perbaikan', 'lokasi_operasi', 'jenis_alat']
    missing = [f for f in required if f not in data]
    if missing:
        return jsonify({'error': f'Field wajib belum diisi: {missing}'}), 400

    try:
        tahun_unit = int(data['tahun_unit'])
        jam_operasional = max(0, min(8000, int(data['jam_operasional'])))
        jumlah_perbaikan = max(0, min(10, int(data['jumlah_perbaikan'])))
        umur_alat = TAHUN_SEKARANG - tahun_unit

        kondisi_enc = safe_encode(encoders['kondisi_terakhir'], data['kondisi_terakhir'], 'kondisi_terakhir')
        lokasi_enc = safe_encode(encoders['lokasi_operasi'], data['lokasi_operasi'], 'lokasi_operasi')
        jenis_enc = safe_encode(encoders['jenis_alat'], data['jenis_alat'], 'jenis_alat')

        row = {
            'umur_alat': umur_alat,
            'jam_operasional': jam_operasional,
            'kondisi_terakhir': kondisi_enc,
            'jumlah_perbaikan': jumlah_perbaikan,
            'lokasi_operasi': lokasi_enc,
            'jenis_alat': jenis_enc,
        }
        X = np.array([[row[f] for f in feature_order]])

        pred_idx = model.predict(X)[0]
        pred_label = encoders['label_risiko'].inverse_transform([pred_idx])[0]

        proba = model.predict_proba(X)[0]
        confidence = {
            cls: round(float(p), 4)
            for cls, p in zip(encoders['label_risiko'].classes_, proba)
        }

        return jsonify({
            'status': 'success',
            'prediksi_risiko': pred_label,
            'confidence': confidence,
            'umur_alat': umur_alat,
            'input_diterima': data,
        })

    except (ValueError, TypeError) as e:
        return jsonify({'error': f'Data tidak valid: {str(e)}'}), 400


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model_loaded': model is not None})


if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
