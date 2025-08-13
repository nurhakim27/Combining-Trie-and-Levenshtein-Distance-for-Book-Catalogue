<?php

// ==========================================
// KONFIGURASI DATABASE
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "csv_db 9";
$table = "booksdatasetclean___copy";

// ==========================================
// AJAX HANDLER
// ==========================================
if (isset($_GET['ajax']) && isset($_GET['query'])) {
    header('Content-Type: application/json');
    $startTime = microtime(true);

    // Implementasi Caching untuk Performa
    $cacheFile = 'trie_cache_damerau_fixed.dat'; // Nama cache baru untuk versi yang sudah benar
    if (file_exists($cacheFile)) {
        $trie = unserialize(file_get_contents($cacheFile));
    } else {
        $titles = getTitlesFromDB($host, $user, $pass, $dbname, $table);
        if (empty($titles)) {
            exit(json_encode(['suggestions' => [], 'time' => 0]));
        }

        $trie = new Trie();
        foreach ($titles as $title) {
            $trie->insert($title);
        }
        file_put_contents($cacheFile, serialize($trie));
    }

    $query = strtolower(trim($_GET['query']));
    $suggestions = [];

    // ### LOGIKA PENCARIAN MULTI-KATA YANG DIPERBAIKI ###
    $queryWords = array_filter(preg_split('/\s+/', $query));
    if (!empty($queryWords)) {
        $lastWord = array_pop($queryWords);
        $completeWords = $queryWords;

        // Langkah 1: Cari kandidat berdasarkan kata terakhir dengan Damerau-Levenshtein
        $lastWordResults = $trie->searchDamerau($lastWord, 2);

        // Langkah 2: Saring kandidat dengan kata-kata filter yang sudah lengkap
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
            // Jika hanya ada satu kata, hasilnya adalah hasil pencarian kata tersebut
            $suggestions = $lastWordResults;
        }
    }

    $time = round((microtime(true) - $startTime) * 1000, 2);
    echo json_encode(['suggestions' => array_slice($suggestions, 0, 500), 'time' => $time]);
    exit;
}

// ==========================================
// FUNGSI DATABASE
// ==========================================
function getTitlesFromDB($host, $user, $pass, $dbname, $table)
{
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) return [];
    $titles = [];
    $sql = "SELECT judul FROM `$table` WHERE judul IS NOT NULL AND judul != ''";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['judul'];
        }
    }
    $conn->close();
    return $titles;
}

// ==========================================
// KELAS TRIE + DAMERAU-LEVENSHTEIN
// ==========================================
class TrieNode
{
    public $children = [];
    public $titles = [];
    public $isEnd = false;
}

class Trie
{
    private $root;

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    public function insert($title)
    {
        $cleaned_title = preg_replace("/[^a-zA-Z0-9\s]/", " ", $title);
        $words = preg_split('/\s+/', strtolower($cleaned_title));
        foreach ($words as $word) {
            if (strlen($word) < 2) continue;
            $node = $this->root;
            for ($i = 0; $i < strlen($word); $i++) {
                $ch = $word[$i];
                if (!isset($node->children[$ch])) {
                    $node->children[$ch] = new TrieNode();
                }
                $node = $node->children[$ch];
            }
            $node->isEnd = true;
            if (!in_array($title, $node->titles)) {
                $node->titles[] = $title;
            }
        }
    }

    public function searchDamerau($prefix, $threshold)
    {
        $results = [];
        // Menggunakan mb_str_split untuk penanganan karakter multi-byte yang aman
        $queryChars = mb_str_split($prefix);
        $currentRow = range(0, count($queryChars));

        // Array untuk menyimpan baris sebelumnya, dibutuhkan untuk cek transposisi
        $prevPrevRow = null;

        foreach ($this->root->children as $char => $child) {
            $this->damerauRecursive($child, $char, $queryChars, $currentRow, $prevPrevRow, $results, $threshold);
        }

        // PERBAIKAN: Fungsi sorting lebih kompatibel (tidak pakai arrow function)
        usort($results, function ($a, $b) {
            if ($a['dist'] == $b['dist']) {
                return 0;
            }
            return ($a['dist'] < $b['dist']) ? -1 : 1;
        });

        return array_unique(array_column($results, 'title'));
    }

    private function damerauRecursive($node, $char, $queryChars, $prevRow, $prevPrevRow, &$results, $threshold)
    {
        $len = count($queryChars);
        $currRow = [$prevRow[0] + 1];

        for ($i = 1; $i <= $len; $i++) {
            $insertCost = $currRow[$i - 1] + 1;
            $deleteCost = $prevRow[$i] + 1;
            $replaceCost = $prevRow[$i - 1] + (($queryChars[$i - 1] === $char) ? 0 : 1);

            $currRow[$i] = min($insertCost, $deleteCost, $replaceCost);

            // ### IMPLEMENTASI KUNCI: Logika Damerau-Levenshtein (Transposisi) ###
            if ($i > 1 && $prevPrevRow !== null && $queryChars[$i - 1] !== $char && $queryChars[$i - 1] === ($node->parentChar ?? null) && $queryChars[$i - 2] === $char) {
                $currRow[$i] = min($currRow[$i], $prevPrevRow[$i - 2] + 1);
            }
        }

        if ($node->isEnd && end($currRow) <= $threshold) {
            foreach ($node->titles as $title) {
                $results[] = ['title' => $title, 'dist' => end($currRow)];
            }
        }

        if (min($currRow) <= $threshold) {
            foreach ($node->children as $nextChar => $child) {
                // Menyimpan karakter parent untuk cek transposisi di level berikutnya
                $child->parentChar = $char;
                // $currRow menjadi $prevRow, dan $prevRow menjadi $prevPrevRow
                $this->damerauRecursive($child, $nextChar, $queryChars, $currRow, $prevRow, $results, $threshold);
            }
        }
    }
}

// Polyfill untuk mb_str_split
if (!function_exists('mb_str_split')) {
    function mb_str_split($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pencarian Buku (Trie + Damerau-Levenshtein)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #suggestions-list {
            position: absolute;
            z-index: 1000;
            background: white;
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
        <h3 class="text-center mb-4">📚 Pencarian Katalog Pustaka 📚 </h3>
        <h5 class="text-center text-muted mb-4">Metode: Trie + Damerau-Levenshtein Distance</h5>
        <div class="col-md-8 mx-auto">
            <div class="position-relative">
                <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Cari judul buku...">
                <ul id="suggestions-list" class="list-group"></ul>
            </div>
            <div id="response-time" class="form-text text-center mt-2 text-muted"></div>
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
                // Pastikan nama file ini sesuai dengan nama file Anda saat disimpan
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
                            timeBox.textContent = `Ditemukan dalam ${data.time} ms`;
                        } else {
                            list.style.display = 'none';
                            timeBox.textContent = `Tidak ada hasil (${data.time} ms)`;
                        }
                    }).catch(err => {
                        console.error(err);
                        list.style.display = 'none';
                        timeBox.textContent = 'Terjadi kesalahan.';
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