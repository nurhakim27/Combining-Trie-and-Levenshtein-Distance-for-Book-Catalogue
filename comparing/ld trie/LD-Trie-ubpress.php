<?php

// ==========================================
// KONFIGURASI DATABASE
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ubpress";
$table = "produk_buku";

// ==========================================
// AJAX HANDLER
// ==========================================
if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $startTime = microtime(true);

    $titlesCacheFile = 'titles_cache_ubpress.dat';
    if (file_exists($titlesCacheFile) && (time() - filemtime($titlesCacheFile) < 86400)) {
        $titles = unserialize(file_get_contents($titlesCacheFile));
    } else {
        $titles = getTitlesFromDB($host, $user, $pass, $dbname, $table);
        if (empty($titles)) {
            exit(json_encode(['suggestions' => [], 'time' => 0]));
        }
        file_put_contents($titlesCacheFile, serialize($titles));
    }

    $query = strtolower(trim($_GET['query']));
    $suggestions = [];
    $queryWords = array_filter(preg_split('/\s+/', $query));

    if (!empty($queryWords)) {
        $matchedTitles = [];
        foreach ($titles as $title) {
            $suggestionLower = strtolower($title);
            $suggestionWords = array_filter(preg_split('/\s+/', preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $suggestionLower)));
            $allWordsFound = true;

            foreach ($queryWords as $queryWord) {
                $foundMatchForFilter = false;
                foreach ($suggestionWords as $sWord) {
                    $maxDist = strlen($queryWord) > 4 ? 2 : 1;

                    // ### PERUBAHAN UTAMA: MENGGUNAKAN FUNGSI LEVENSHTEIN NATIVE ###
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
                $matchedTitles[] = $title;
            }
        }

        if (!empty($matchedTitles)) {
            $trie = new Trie();
            foreach ($matchedTitles as $title) {
                $trie->insert($title);
            }
            $suggestions = $trie->sortUsingPrefix($matchedTitles, $queryWords[0]);
        }
    }

    $time = round((microtime(true) - $startTime) * 1000, 2);
    echo json_encode(['suggestions' => array_slice($suggestions, 0, 10), 'time' => $time]);
    exit;
}

// ==========================================
// ### FUNGSI LEVENSHTEIN DISTANCE NATIVE ###
// ==========================================
function native_levenshtein(string $str1, string $str2): int
{
    $len1 = strlen($str1);
    $len2 = strlen($str2);

    // Inisialisasi matriks (disimulasikan dengan 2 baris untuk efisiensi memori)
    $currentRow = range(0, $len2);
    $previousRow = [];

    for ($i = 0; $i < $len1; $i++) {
        $previousRow = $currentRow;
        $currentRow = [$i + 1]; // Kolom pertama pada baris saat ini

        for ($j = 0; $j < $len2; $j++) {
            $insertCost = $currentRow[$j] + 1;
            $deleteCost = $previousRow[$j + 1] + 1;
            $replaceCost = $previousRow[$j] + (($str1[$i] !== $str2[$j]) ? 1 : 0);

            $currentRow[] = min($insertCost, $deleteCost, $replaceCost);
        }
    }

    return end($currentRow); // Jaraknya adalah nilai terakhir di baris terakhir
}


// ==========================================
// FUNGSI DATABASE
// ==========================================
function getTitlesFromDB($host, $user, $pass, $dbname, $table)
{
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) return [];
    $titles = [];
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

// ==========================================
// KELAS TRIE (Digunakan untuk Langkah 2)
// ==========================================
class TrieNode
{
    public $children = [];
    public $isEndOfWord = false;
    public $words = [];
}

class Trie
{
    private $root;
    public function __construct()
    {
        $this->root = new TrieNode();
    }

    private function cleanAndSplit(string $text): array
    {
        $cleanedText = strtolower(preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $text));
        return array_filter(preg_split('/\s+/', $cleanedText));
    }

    public function insert(string $fullTitle): void
    {
        $words = $this->cleanAndSplit($fullTitle);
        foreach ($words as $word) {
            if (strlen($word) > 1) {
                $node = $this->root;
                for ($i = 0; $i < strlen($word); $i++) {
                    $char = $word[$i];
                    if (!isset($node->children[$char])) {
                        $node->children[$char] = new TrieNode();
                    }
                    $node = $node->children[$char];
                }
                $node->isEndOfWord = true;
                if (!in_array($fullTitle, $node->words)) {
                    $node->words[] = $fullTitle;
                }
            }
        }
    }

    public function sortUsingPrefix(array $titles, string $prefix): array
    {
        $prefixMatches = [];
        $otherMatches = [];
        $prefixLower = strtolower($prefix);
        foreach ($titles as $title) {
            $words = $this->cleanAndSplit($title);
            $foundPrefix = false;
            foreach ($words as $word) {
                if (strpos($word, $prefixLower) === 0) {
                    $prefixMatches[] = $title;
                    $foundPrefix = true;
                    break;
                }
            }
            if (!$foundPrefix) {
                $otherMatches[] = $title;
            }
        }
        return array_merge($prefixMatches, $otherMatches);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pencarian Levenshtein (Native) + Trie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

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
        <h1 class="text-center mb-4">📚 Pencarian Katalog Pustaka 📚</h1>
        <h5 class="text-center text-muted mb-4">Metode: Levenshtein (Native) + Trie</h5>
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