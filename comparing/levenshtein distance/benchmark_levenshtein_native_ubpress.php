<?php

// Nonaktifkan batas waktu eksekusi karena proses ini bisa sangat lama
set_time_limit(0);
// Tingkatkan batas memori jika perlu
ini_set('memory_limit', '512M');

echo "<pre>"; // Untuk tampilan output yang lebih rapi di browser

// ==========================================
// KONFIGURASI & FUNGSI YANG DIBUTUHKAN
// Semua didefinisikan di sini agar skrip mandiri
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ubpress";
$table = "produk_buku";
$jumlah_pengujian_per_judul = 10;
$file_output_csv = 'hasil_benchmark_levenshtein_native_ubpress.csv';

function getTitlesFromDB($host, $user, $pass, $dbname, $table)
{
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) return ['error' => "Koneksi Gagal: " . $conn->connect_error];
    $titles = [];
    // Ganti 'judul' menjadi 'nama' sesuai dengan struktur tabel ubpress
    $sql = "SELECT nama FROM `$table` WHERE nama IS NOT NULL AND nama != ''";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['nama'];
        }
    }
    $conn->close();
    return $titles;
}

function native_levenshtein(string $str1, string $str2): int
{
    $len1 = mb_strlen($str1, 'UTF-8');
    $len2 = mb_strlen($str2, 'UTF-8');
    if ($len1 == 0) return $len2;
    if ($len2 == 0) return $len1;

    $str1_chars = mb_str_split($str1);
    $str2_chars = mb_str_split($str2);
    $currentRow = range(0, $len2);

    for ($i = 0; $i < $len1; $i++) {
        $previousRow = $currentRow;
        $currentRow = [$i + 1];
        for ($j = 0; $j < $len2; $j++) {
            $insertCost = $currentRow[$j] + 1;
            $deleteCost = $previousRow[$j + 1] + 1;
            $replaceCost = $previousRow[$j] + (($str1_chars[$i] !== $str2_chars[$j]) ? 1 : 0);
            $currentRow[] = min($insertCost, $deleteCost, $replaceCost);
        }
    }
    return end($currentRow);
}

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $length = 1)
    {
        return preg_split('/(?<!^)(?!$)/u', $string);
    }
}

// ======================
// PERSIAPAN PENGUJIAN
// ======================
echo "Mengambil semua judul dari database UBPRESS untuk pengujian...\n";
$titles_for_testing = getTitlesFromDB($host, $user, $pass, $dbname, $table);

if (isset($titles_for_testing['error'])) {
    die("Gagal mengambil judul dari database: " . $titles_for_testing['error']);
}
if (empty($titles_for_testing)) {
    die("Tidak ada judul yang ditemukan di database.");
}
$jumlah_total_judul = count($titles_for_testing);
echo "{$jumlah_total_judul} judul akan diuji.\n\n";

// ======================
// PROSES PENGUJIAN
// ======================
$fp = fopen($file_output_csv, 'w');
$header = ['Judul yang Diuji'];
for ($i = 1; $i <= $jumlah_pengujian_per_judul; $i++) {
    $header[] = "Waktu Tes {$i} (md)";
}
$header[] = 'Waktu Rata-rata (md)';
fputcsv($fp, $header);

echo "Memulai benchmark (Metode Levenshtein Native)... Ini akan SANGAT LAMA.\n";
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

    $queryWords = array_filter(preg_split('/\s+/', $query));

    for ($i = 0; $i < $jumlah_pengujian_per_judul; $i++) {
        $startTime = microtime(true);

        // --- INTI LOGIKA SEQUENTIAL SEARCH + LEVENSHTEIN NATIVE ---
        $matches = [];
        foreach ($titles_for_testing as $title_to_scan) {
            $lowerJudul = strtolower($title_to_scan);
            $titleWords = array_filter(preg_split('/\s+/', preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $lowerJudul)));

            $allQueryWordsFound = true;
            foreach ($queryWords as $queryWord) {
                $foundMatch = false;
                foreach ($titleWords as $titleWord) {
                    $maxDist = strlen($queryWord) > 4 ? 2 : 1;
                    if (native_levenshtein($queryWord, $titleWord) <= $maxDist) {
                        $foundMatch = true;
                        break;
                    }
                }
                if (!$foundMatch) {
                    $allQueryWordsFound = false;
                    break;
                }
            }
            if ($allQueryWordsFound) {
                $matches[] = $title_to_scan;
            }
        }
        // --- AKHIR LOGIKA PENCARIAN ---

        $endTime = microtime(true);
        $waktu_eksekusi = round(($endTime - $startTime) * 1000, 4);
        $catatan_waktu[] = $waktu_eksekusi;
        $total_waktu += $waktu_eksekusi;
    }

    $rata_rata_waktu = $total_waktu > 0 ? $total_waktu / $jumlah_pengujian_per_judul : 0;

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
