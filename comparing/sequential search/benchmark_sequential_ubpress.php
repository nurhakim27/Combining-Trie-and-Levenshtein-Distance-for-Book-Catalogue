<?php

// Nonaktifkan batas waktu eksekusi karena proses ini bisa lama
set_time_limit(0);
// Tingkatkan batas memori jika perlu
ini_set('memory_limit', '512M');

// ==========================================================
// PENTING: Sertakan file yang berisi fungsi database Anda
// Ganti 'sequential-search-ubpress.php' jika nama file Anda berbeda.
// ==========================================================
$algorithm_file = 'sequential-search-ubpress.php';
if (!file_exists($algorithm_file)) {
    die("Error: File '{$algorithm_file}' tidak ditemukan. Pastikan nama file sudah benar dan berada di folder yang sama.");
}
require_once $algorithm_file;

echo "<pre>"; // Untuk tampilan output yang lebih rapi di browser

// ======================
// KONFIGURASI
// ======================
$jumlah_pengujian_per_judul = 10;
$file_output_csv = 'hasil_benchmark_sequential_ubpress.csv';

// Ambil daftar judul dari database sekali saja
echo "Mengambil semua judul dari database UBPRESS untuk pengujian...\n";
$titles_for_testing = getTitlesFromDB($dbHost, $dbUser, $dbPass, $dbName, $dbTable);
if (isset($titles_for_testing['error'])) {
    die("Gagal mengambil judul dari database: " . $titles_for_testing['error']);
}
$jumlah_total_judul = count($titles_for_testing);
echo "{$jumlah_total_judul} judul akan diuji.\n\n";

// ======================
// PROSES PENGUJIAN
// ======================

// Buka file CSV untuk ditulis
$fp = fopen($file_output_csv, 'w');

// Tulis header untuk file CSV
$header = ['Judul yang Diuji'];
for ($i = 1; $i <= $jumlah_pengujian_per_judul; $i++) {
    $header[] = "Waktu Tes {$i} (md)";
}
$header[] = 'Waktu Rata-rata (md)';
fputcsv($fp, $header);

// Mulai proses benchmark
echo "Memulai benchmark (Metode Sequential Search)... Ini akan memakan waktu.\n";
$judul_ke = 0;
foreach ($titles_for_testing as $judul) {
    $judul_ke++;
    $query = strtolower(trim($judul));

    $catatan_waktu = [];
    $total_waktu = 0;

    echo "Menguji judul {$judul_ke}/{$jumlah_total_judul}: \"{$judul}\"\n";

    if (empty($query)) {
        continue;
    }

    // Lakukan pengujian sebanyak 10x untuk setiap judul
    for ($i = 0; $i < $jumlah_pengujian_per_judul; $i++) {
        $startTime = microtime(true);

        // --- INTI LOGIKA SEQUENTIAL SEARCH (disalin dari sequential-search-ubpress.php) ---
        $suggestions = [];
        if (!empty($query)) {
            foreach ($titles_for_testing as $title_to_scan) {
                if (stripos($title_to_scan, $query) !== false) {
                    $suggestions[] = $title_to_scan;
                }
            }
        }
        // --- AKHIR LOGIKA PENCARIAN ---

        $endTime = microtime(true);
        $waktu_eksekusi = round(($endTime - $startTime) * 1000, 4); // presisi 4 angka desimal
        $catatan_waktu[] = $waktu_eksekusi;
        $total_waktu += $waktu_eksekusi;
    }

    $rata_rata_waktu = $total_waktu / $jumlah_pengujian_per_judul;

    // Tulis hasil ke file CSV
    $csv_row = [$judul];
    foreach ($catatan_waktu as $waktu) {
        $csv_row[] = $waktu;
    }
    $csv_row[] = round($rata_rata_waktu, 4);
    fputcsv($fp, $csv_row);
}

fclose($fp);

echo "\n===================================\n";
echo "Benchmark Selesai!\n";
echo "Hasil telah disimpan di file: {$file_output_csv}\n";
echo "</pre>";
