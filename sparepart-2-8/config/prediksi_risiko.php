<?php
/*
|====================================================
| ALGORITMA DECISION TREE - PREDIKSI RISIKO ALAT
|====================================================
| File bersama, dipakai oleh alat_berat.php & database/alat_berat_history.php
| supaya logika prediksi risiko konsisten di semua halaman.
|
| Bobot fitur (sama dengan form Tambah/Edit Data, lihat
| database/alat_berat_tambah.php):
|   1) Tahun Unit / Umur Alat   -> bobot maks 6
|   2) Jam Operasional          -> bobot maks 5
|   3) Kondisi Terakhir         -> bobot maks 4
|   4) Jumlah Perbaikan         -> bobot maks 3
|   5) Lokasi Operasi           -> bobot maks 2
|   6) Jenis Kendaraan          -> bobot maks 1
|
| Setiap node pohon melakukan SATU pengujian biner terhadap satu
| fitur, lalu mengarah ke node anak (yes/no) sampai mencapai leaf
| (label risiko akhir).
*/

if (!function_exists('bangunPohonKeputusanRisiko')) {
    function bangunPohonKeputusanRisiko(): array
    {
        return [
            // Root Node: Umur alat sudah kategori TUA?
            'feature' => 'kategoriUmur',
            'test'    => fn($v) => $v === 'tua',
            'yes'     => ['leaf' => 'tinggi'], // Leaf: TUA selalu Risiko Tinggi
            'no'      => [
                // Node 2: Jam operasional sudah KRITIS?
                'feature' => 'kategoriJam',
                'test'    => fn($v) => $v === 'kritis',
                'yes'     => ['leaf' => 'tinggi'], // Leaf: Jam Kritis selalu Risiko Tinggi
                'no'      => [
                    // Node 3: Skor gabungan (skala 0-10) <= 3 ?
                    'feature' => 'skorTampil',
                    'test'    => fn($v) => $v <= 3,
                    'yes'     => ['leaf' => 'rendah'], // Leaf: Risiko Rendah
                    'no'      => [
                        // Node 4: Skor gabungan <= 7 ?
                        'feature' => 'skorTampil',
                        'test'    => fn($v) => $v <= 7,
                        'yes'     => ['leaf' => 'sedang'], // Leaf: Risiko Sedang
                        'no'      => ['leaf' => 'tinggi'], // Leaf: sisanya Risiko Tinggi
                    ],
                ],
            ],
        ];
    }
}

if (!function_exists('telusuriPohonKeputusan')) {
    // Telusuri (traverse) pohon keputusan dari root sampai ke leaf
    function telusuriPohonKeputusan(array $node, array $data): string
    {
        while (!isset($node['leaf'])) {
            $nilaiFitur = $data[$node['feature']] ?? null;
            $node = ($node['test'])($nilaiFitur) ? $node['yes'] : $node['no'];
        }
        return $node['leaf'];
    }
}

if (!function_exists('prediksiRisikoAlat')) {
    /*
    | Hitung skor bobot fitur (sama seperti form Tambah/Edit),
    | lalu klasifikasikan lewat pohon keputusan di atas.
    */
    function prediksiRisikoAlat(array $row): array
    {
        $tahunSekarang = (int) date('Y');
        $tahunUnit     = (int) ($row['tahun_unit'] ?? $tahunSekarang);
        $umur          = $tahunSekarang - $tahunUnit;

        $jam     = min(8000, (int) ($row['jam_operasional'] ?? 0));
        $kondisi = strtolower(trim($row['kondisi_terakhir'] ?? ''));
        $jenis   = strtolower(trim($row['jenis_kendaraan'] ?? ''));
        $lokasi  = strtolower(trim($row['lokasi'] ?? ''));
        $prb     = min(10, (int) ($row['jumlah_perbaikan'] ?? 0));

        $skor = 0;

        // 1) Tahun Unit / Umur Alat (bobot 6)
        if ($umur <= 7)       { $skor += 0; $kategoriUmur = 'baru'; }
        elseif ($umur <= 12)  { $skor += 3; $kategoriUmur = 'normal'; }
        else                  { $skor += 6; $kategoriUmur = 'tua'; }

        // 2) Jam Operasional (bobot 5)
        if ($jam < 3000)      { $skor += 0; $kategoriJam = 'normal'; }
        elseif ($jam <= 5000) { $skor += 3; $kategoriJam = 'waspada'; }
        else                  { $skor += 5; $kategoriJam = 'kritis'; }

        // 3) Kondisi Terakhir (bobot 4)
        if ($kondisi === 'baik')            $skor += 0;
        elseif ($kondisi === 'dalam perbaikan') $skor += 2;
        elseif ($kondisi === 'rusak')       $skor += 4;

        // 4) Jumlah Perbaikan (bobot 3)
        if ($prb <= 2)      $skor += 0;
        elseif ($prb <= 6)  $skor += 2;
        else                $skor += 3;

        // 5) Lokasi Operasi (bobot 2)
        if ($lokasi === 'risiko rendah')      $skor += 0;
        elseif ($lokasi === 'risiko sedang')  $skor += 1;
        elseif ($lokasi === 'risiko tinggi')  $skor += 2;

        // 6) Jenis Kendaraan (bobot 1)
        if (str_contains($jenis, 'crane') || str_contains($jenis, 'bulldozer')) $skor += 1;

        $skorMaks   = 21; // 6 + 5 + 4 + 3 + 2 + 1
        $skorTampil = (int) round(($skor / $skorMaks) * 10);

        // Klasifikasi akhir lewat pohon keputusan
        $pohon = bangunPohonKeputusanRisiko();
        $leaf  = telusuriPohonKeputusan($pohon, [
            'kategoriUmur' => $kategoriUmur,
            'kategoriJam'  => $kategoriJam,
            'skorTampil'   => $skorTampil,
        ]);

        // Leaf -> tampilan (lookup table, tanpa switch/if-else)
        $petaLabelRisiko = [
            'tinggi' => ['level' => '🔴 RISIKO TINGGI', 'rclass' => 'r-tinggi', 'icon' => 'bi-shield-x'],
            'sedang' => ['level' => '🟡 RISIKO SEDANG', 'rclass' => 'r-sedang', 'icon' => 'bi-shield-exclamation'],
            'rendah' => ['level' => '🟢 RISIKO RENDAH', 'rclass' => 'r-rendah', 'icon' => 'bi-shield-check'],
        ];

        return $petaLabelRisiko[$leaf] + ['skor' => $skorTampil];
    }
}
