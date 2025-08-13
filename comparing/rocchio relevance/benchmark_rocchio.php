<?php

// Nonaktifkan batas waktu eksekusi karena proses ini bisa sangat lama
set_time_limit(0);
// Tingkatkan batas memori jika perlu
ini_set('memory_limit', '512M');

// ==========================================================
// PENTING: Sertakan file yang berisi definisi kelas Rocchio Anda
// Ganti 'rocchio-relevance.php' jika nama file Anda berbeda.
// ==========================================================
require_once 'rocchio-relevance.php';

echo "<pre>"; // Untuk tampilan output yang lebih rapi di browser

// ======================
// KONFIGURASI
// ======================
$jumlah_pengujian_per_judul = 10;
$file_output_csv = 'hasil_benchmark_rocchio.csv';

// Ambil VSM yang sudah di-cache agar tidak membangun ulang
$cacheFile = 'rocchio_cache.dat';
if (!file_exists($cacheFile)) {
    die("File cache '{$cacheFile}' tidak ditemukan. Jalankan dulu skrip rocchio-relevance.php sekali dari browser untuk membuatnya.");
}
echo "Memuat Vector Space Model dari cache...\n";
$engine = unserialize(file_get_contents($cacheFile));
echo "VSM berhasil dimuat.\n\n";

// Ambil semua judul dari DB untuk dijadikan query pengujian
echo "Mengambil semua judul dari database untuk pengujian...\n";
$daftar_judul_tes = getTitlesFromDB($dbHost, $dbUser, $dbPass, $dbName, $dbTable);
if (isset($daftar_judul_tes['error'])) {
    die("Gagal mengambil judul dari database: " . $daftar_judul_tes['error']);
}
$jumlah_total_judul = count($daftar_judul_tes);
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
echo "Memulai benchmark (Metode Rocchio/VSM)... Ini akan memakan waktu.\n";
$judul_ke = 0;
foreach ($daftar_judul_tes as $judul) {
    $judul_ke++;
    $query = strtolower(trim($judul));

    $catatan_waktu = [];
    $total_waktu = 0;

    echo "Menguji judul {$judul_ke}/{$jumlah_total_judul}: \"{$judul}\"\n";

    // Lakukan pengujian sebanyak 10x untuk setiap judul
    for ($i = 0; $i < $jumlah_pengujian_per_judul; $i++) {
        $startTime = microtime(true);

        // --- INTI LOGIKA PENCARIAN (disalin dari rocchio-relevance.php) ---
        // Karena ini benchmark, kita hanya menjalankan pencarian VSM-nya
        // tanpa feedback loop yang sebenarnya.
        $suggestions = $engine->searchWithRocchio($query);
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
