<?php
// ======================
// KONFIGURASI DATABASE
// ======================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ubpress";
$table = "produk_buku";

// ==========================================
// FUNGSI DAMERAU-LEVENSHTEIN DISTANCE NATIVE
// ==========================================
function native_damerau_levenshtein(string $str1, string $str2): int
{
    $len1 = mb_strlen($str1, 'UTF-8');
    $len2 = mb_strlen($str2, 'UTF-8');

    // Inisialisasi matriks DP
    $d = [];
    for ($i = 0; $i <= $len1; $i++) {
        $d[$i][0] = $i;
    }
    for ($j = 0; $j <= $len2; $j++) {
        $d[0][$j] = $j;
    }

    $str1_chars = mb_str_split($str1);
    $str2_chars = mb_str_split($str2);

    for ($i = 1; $i <= $len1; $i++) {
        for ($j = 1; $j <= $len2; $j++) {
            $cost = ($str1_chars[$i - 1] === $str2_chars[$j - 1]) ? 0 : 1;

            // Operasi standar Levenshtein
            $d[$i][$j] = min(
                $d[$i - 1][$j] + 1,          // Deletion
                $d[$i][$j - 1] + 1,          // Insertion
                $d[$i - 1][$j - 1] + $cost   // Substitution
            );

            // Operasi Transposisi (kunci Damerau-Levenshtein)
            if ($i > 1 && $j > 1 && $str1_chars[$i - 1] === $str2_chars[$j - 2] && $str1_chars[$i - 2] === $str2_chars[$j - 1]) {
                $d[$i][$j] = min(
                    $d[$i][$j],
                    $d[$i - 2][$j - 2] + 1 // Biaya transposisi
                );
            }
        }
    }

    return $d[$len1][$len2];
}

// Polyfill untuk mb_str_split jika versi PHP < 7.4
if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $length = 1)
    {
        return preg_split('/(?<!^)(?!$)/u', $string);
    }
}

// ======================
// HANDLE AJAX REQUEST
// ======================
if (isset($_GET['ajax']) && isset($_GET['query'])) {
    header('Content-Type: application/json');

    $start = microtime(true);
    $query = strtolower(trim($_GET['query']));
    $suggestions = [];

    if ($query !== '') {
        $con = new mysqli($host, $user, $pass, $dbname);
        if ($con->connect_error) {
            echo json_encode(['error' => 'Koneksi gagal']);
            exit;
        }

        $sql = "SELECT nama FROM `$table` WHERE nama IS NOT NULL AND nama != ''";
        $res = $con->query($sql);

        if ($res) {
            $matches = [];
            $queryWords = array_filter(preg_split('/\s+/', $query));

            while ($row = $res->fetch_assoc()) {
                $nama = $row['nama'];
                $lowerJudul = strtolower($nama);
                $titleWords = array_filter(preg_split('/\s+/', preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $lowerJudul)));

                $allQueryWordsFound = true;
                $totalDistance = 0;

                foreach ($queryWords as $queryWord) {
                    $bestDistForQueryWord = PHP_INT_MAX;
                    $foundMatch = false;

                    foreach ($titleWords as $titleWord) {
                        $maxDist = strlen($queryWord) > 4 ? 2 : 1;

                        // ### PERUBAHAN UTAMA: MENGGUNAKAN FUNGSI DAMERAU-LEVENSHTEIN NATIVE ###
                        $dist = native_damerau_levenshtein($queryWord, $titleWord);

                        if ($dist <= $maxDist && $dist < $bestDistForQueryWord) {
                            $bestDistForQueryWord = $dist;
                            $foundMatch = true;
                        }
                    }

                    if ($foundMatch) {
                        $totalDistance += $bestDistForQueryWord;
                    } else {
                        $allQueryWordsFound = false;
                        break;
                    }
                }

                if ($allQueryWordsFound) {
                    $matches[] = ['judul' => $nama, 'dist' => $totalDistance];
                }
            }

            // Urutkan berdasarkan total jarak
            usort($matches, function ($a, $b) {
                return $a['dist'] <=> $b['dist'];
            });

            $suggestions = array_column($matches, 'judul');
        }
        $con->close();
    }

    $time = round((microtime(true) - $start) * 1000, 2);
    echo json_encode(['suggestions' => array_slice($suggestions, 0, 10), 'time' => $time]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Autocomplete Damerau-Levenshtein (Native)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #suggestions-list {
            position: absolute;
            z-index: 1000;
            background-color: white;
            width: 100%;
            border: 1px solid #ccc;
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">📚 Pencarian Katalog Pustaka 📚</h1>
        <h5 class="text-center text-muted mb-4">Metode: Damerau Levenshtein Distance Native</h5>
        <div class="col-md-8 mx-auto">
            <div class="position-relative">
                <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Cari judul buku...">
                <ul id="suggestions-list" class="list-group"></ul>
            </div>
            <div id="response-time" class="form-text mt-2 text-center text-muted"></div>
        </div>
    </div>
    <script>
        const input = document.getElementById('search-input');
        const list = document.getElementById('suggestions-list');
        const timeBox = document.getElementById('response-time');
        let timeout;

        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const query = input.value.trim();
            if (query.length < 2) {
                list.innerHTML = '';
                list.style.display = 'none';
                timeBox.textContent = '';
                return;
            }

            timeout = setTimeout(() => {
                fetch(`?ajax=1&query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (data.suggestions.length > 0) {
                            data.suggestions.forEach(item => {
                                const li = document.createElement('li');
                                li.className = "list-group-item list-group-item-action";
                                li.textContent = item;
                                li.onclick = () => {
                                    input.value = item;
                                    list.style.display = 'none';
                                };
                                list.appendChild(li);
                            });
                            list.style.display = 'block';
                            timeBox.textContent = `Ditemukan dalam ${data.time} ams`;
                        } else {
                            list.style.display = 'none';
                            timeBox.textContent = `Tidak ada hasil (${data.time} ms)`;
                        }
                    }).catch(err => {
                        console.error(err);
                        list.style.display = 'none';
                        timeBox.textContent = 'Gagal memuat saran.';
                    });
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                list.style.display = 'none';
            }
        });
    </script>
</body>

</html>