<?php

// Nonaktifkan batas waktu eksekusi karena proses ini bisa sangat lama
set_time_limit(0);
// Tingkatkan batas memori jika perlu
ini_set('memory_limit', '512M');

// ==========================================================
// PENTING: Sertakan file yang berisi definisi algoritma Anda
// Ganti 'LD-Trie.php' jika nama file Anda berbeda.
// ==========================================================
require_once 'LD-Trie.php';

echo "<pre>"; // Untuk tampilan output yang lebih rapi di browser

// ======================
// KONFIGURASI
// ======================
$jumlah_pengujian_per_judul = 10;
$file_output_csv = 'hasil_benchmark_levenshtein_trie.csv';

// Ambil daftar judul dari cache atau DB untuk diuji
$titlesCacheFile = 'titles_cache.dat';
if (file_exists($titlesCacheFile)) {
    echo "Memuat daftar judul dari cache...\n";
    $titles = unserialize(file_get_contents($titlesCacheFile));
} else {
    echo "Mengambil daftar judul dari database...\n";
    $titles = getTitlesFromDB($host, $user, $pass, $dbname, $table);
    file_put_contents($titlesCacheFile, serialize($titles));
}

if (empty($titles)) {
    die("Gagal mendapatkan daftar judul.");
}
$jumlah_total_judul = count($titles);
echo "Daftar judul berhasil dimuat. {$jumlah_total_judul} judul akan diuji.\n\n";

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
echo "Memulai benchmark (Metode Levenshtein Dulu)... Ini akan memakan waktu lama.\n";
$judul_ke = 0;
foreach ($titles as $judul) {
    $judul_ke++;
    $query = strtolower(trim($judul));
    $queryWords = array_filter(preg_split('/\s+/', $query));

    $catatan_waktu = [];
    $total_waktu = 0;

    echo "Menguji judul {$judul_ke}/{$jumlah_total_judul}: \"{$judul}\"\n";

    if (empty($queryWords)) {
        continue;
    }

    // Lakukan pengujian sebanyak 10x untuk setiap judul
    for ($i = 0; $i < $jumlah_pengujian_per_judul; $i++) {
        $startTime = microtime(true);

        // --- INTI LOGIKA PENCARIAN (disalin dari LD-Trie.php) ---
        $matchedTitles = [];
        foreach ($titles as $title_scan) {
            $suggestionLower = strtolower($title_scan);
            $suggestionWords = array_filter(preg_split('/\s+/', preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $suggestionLower)));
            $allWordsFound = true;

            foreach ($queryWords as $queryWord) {
                $foundMatchForFilter = false;
                foreach ($suggestionWords as $sWord) {
                    $maxDist = strlen($queryWord) > 4 ? 2 : 1;
                    if (native_levenshtein($sWord, $queryWord) <= $maxDist) {
                        $foundMatchForFilter = true;
                        break;
                    }
                }
                if (!$foundMatchForFilter) {
                    $allWordsFound = false;
                    break;
                }
            }
            if ($allWordsFound) {
                $matchedTitles[] = $title_scan;
            }
        }
        // Trie hanya digunakan untuk sorting, jadi tidak dimasukkan dalam pengukuran inti
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
