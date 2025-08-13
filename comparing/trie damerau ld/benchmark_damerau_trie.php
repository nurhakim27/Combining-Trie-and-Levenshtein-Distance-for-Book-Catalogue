<?php

// Nonaktifkan batas waktu eksekusi karena proses ini bisa lama
set_time_limit(0);
// Tingkatkan batas memori jika perlu
ini_set('memory_limit', '512M');

// ==========================================================
// PENTING: Sertakan file yang berisi definisi kelas Trie Anda
// Ganti 'damerau-trie.php' jika nama file Anda berbeda.
// ==========================================================
require_once 'damerau-trie.php';

echo "<pre>"; // Untuk tampilan output yang lebih rapi di browser

// ======================
// KONFIGURASI
// ======================
$jumlah_pengujian_per_judul = 10;
$file_output_csv = 'hasil_benchmark_damerau_trie.csv';

// Ambil Trie yang sudah di-cache agar tidak membangun ulang
$cacheFile = 'trie_cache_damerau_fixed.dat';
if (!file_exists($cacheFile)) {
    die("File cache '{$cacheFile}' tidak ditemukan. Jalankan dulu skrip damerau-trie.php sekali dari browser untuk membuatnya.");
}
echo "Memuat Trie dari cache...\n";
$trie = unserialize(file_get_contents($cacheFile));
echo "Trie berhasil dimuat.\n\n";

// Ambil semua judul dari DB untuk dijadikan query pengujian
echo "Mengambil semua judul dari database untuk pengujian...\n";
$daftar_judul_tes = getTitlesFromDB($host, $user, $pass, $dbname, $table);
if (empty($daftar_judul_tes)) {
    die("Gagal mengambil judul dari database.");
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
echo "Memulai benchmark...\n";
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

        // --- INTI LOGIKA PENCARIAN (disalin dari damerau-trie.php) ---
        $suggestions = [];
        $queryWords = array_filter(preg_split('/\s+/', $query));
        if (!empty($queryWords)) {
            $lastWord = array_pop($queryWords);
            $completeWords = $queryWords;

            $lastWordResults = $trie->searchDamerau($lastWord, 2);

            if (!empty($completeWords)) {
                $filteredSuggestions = [];
                foreach ($lastWordResults as $suggestion) {
                    $suggestionLower = strtolower($suggestion);
                    $allWordsFound = true;
                    foreach ($completeWords as $filterWord) {
                        if (!preg_match("/\b" . preg_quote($filterWord, '/') . "\b/", $suggestionLower)) {
                            $allWordsFound = false;
                            break;
                        }
                    }
                    if ($allWordsFound) {
                        $filteredSuggestions[] = $suggestion;
                    }
                }
                $suggestions = $filteredSuggestions;
            } else {
                $suggestions = $lastWordResults;
            }
        }
        // --- AKHIR LOGIKA PENCARIAN ---

        $endTime = microtime(true);
        $waktu_eksekusi = round(($endTime - $startTime) * 1000, 4);
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
