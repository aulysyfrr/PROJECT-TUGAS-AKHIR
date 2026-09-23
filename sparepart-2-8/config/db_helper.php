<?php
/*
|====================================================
| HELPER: DETEKSI KOLOM TABEL (KOMPATIBILITAS SKEMA)
|====================================================
| Dipakai supaya query INSERT/UPDATE tetap jalan baik
| SEBELUM maupun SESUDAH migration_hapus_tahun_pembelian_terjual.sql
| dijalankan di database. Kalau kolom tahun_pembelian/tahun_terjual
| masih ada (belum di-drop) dan NOT NULL, kita tetap isi otomatis
| supaya query tidak gagal.
*/

if (!function_exists('kolomTabel')) {
    function kolomTabel(mysqli $conn, string $table): array
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $cols = [];
        $res  = mysqli_query($conn, "SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $cols[] = $r['Field'];
            }
        }
        return $cache[$table] = $cols;
    }
}

if (!function_exists('kolomAda')) {
    function kolomAda(mysqli $conn, string $table, string $kolom): bool
    {
        return in_array($kolom, kolomTabel($conn, $table), true);
    }
}

/*
| Bangun query INSERT secara dinamis dari daftar field.
| $fields: ['nama_kolom' => ['tipe_bind', $nilai], ...]
| $wajib: kolom yang HARUS selalu disertakan meski tidak ada di $fields
| (tidak dipakai di sini, disediakan untuk fleksibilitas ke depan)
|
| Return: [$sql, $types, $values] siap dipakai di mysqli_stmt_bind_param
*/
if (!function_exists('bangunInsertDinamis')) {
    function bangunInsertDinamis(mysqli $conn, string $table, array $fields, array $extraRaw = []): array
    {
        $kolomTersedia = kolomTabel($conn, $table);

        $cols   = [];
        $types  = '';
        $values = [];

        foreach ($fields as $kolom => [$tipe, $nilai]) {
            // Hanya sertakan kolom yang benar-benar ada di tabel saat ini
            if (in_array($kolom, $kolomTersedia, true)) {
                $cols[]   = $kolom;
                $types   .= $tipe;
                $values[] = $nilai;
            }
        }

        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $extraCols    = '';
        $extraVals    = '';
        foreach ($extraRaw as $kolom => $rawSql) {
            $extraCols .= ", `$kolom`";
            $extraVals .= ", $rawSql";
        }

        $sql = "INSERT INTO `$table` (" . implode(',', $cols) . "$extraCols) VALUES ($placeholders$extraVals)";

        return [$sql, $types, $values];
    }
}
