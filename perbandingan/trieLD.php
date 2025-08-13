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
if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $startTime = microtime(true);

    $cacheFile = 'trie_cache_final_v2.dat'; // Nama cache baru lagi
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
    $queryWords = array_filter(preg_split('/\s+/', $query));

    if (!empty($queryWords)) {
        // ### LOGIKA PENCARIAN BARU DAN TERAKHIR ###

        // 1. Ambil kata pertama untuk pencarian cepat via Trie
        $firstWord = $queryWords[0];
        $candidateTitles = $trie->searchByFuzzyWord($firstWord); // pakai LD juga di awal
        array_shift($queryWords); // hapus kata pertama dari array setelah dipakai

        // 2. Sisa kata menjadi filter dengan Levenshtein
        $filterWords = $queryWords;

        if (empty($filterWords)) {
            // Jika hanya ada satu kata, hasilnya langsung dari Trie
            $suggestions = $candidateTitles;
        } else {
            // Jika ada lebih dari satu kata, saring kandidat
            $finalSuggestions = [];
            foreach ($candidateTitles as $suggestion) {
                $suggestionLower = strtolower($suggestion);
                $suggestionWords = array_filter(preg_split('/\s+/', preg_replace("/[^a-zA-Z0-9\s'-]/", " ", $suggestionLower)));
                $allWordsFound = true;

                foreach ($filterWords as $filterWord) {
                    $foundMatchForFilter = false;
                    foreach ($suggestionWords as $sWord) {
                        $maxDist = strlen($filterWord) > 4 ? 2 : 1; // Toleransi typo
                        if (levenshtein($sWord, $filterWord) <= $maxDist) {
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
                    $finalSuggestions[] = $suggestion;
                }
            }
            $suggestions = $finalSuggestions;
        }
    }

    $time = round((microtime(true) - $startTime) * 1000, 2);
    echo json_encode(['suggestions' => array_slice($suggestions, 0, 10), 'time' => $time]);
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
// KELAS TRIE (PREFIX SEARCH)
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

    public function searchPrefix(string $prefix): array
    {
        $node = $this->root;
        $prefixLower = strtolower($prefix);
        for ($i = 0; $i < strlen($prefixLower); $i++) {
            $char = $prefixLower[$i];
            if (!isset($node->children[$char])) {
                return [];
            }
            $node = $node->children[$char];
        }
        $results = [];
        $this->collectWords($node, $results);
        return array_keys($results);
    }

    private function collectWords(TrieNode $node, array &$results): void
    {
        if ($node->isEndOfWord) {
            foreach ($node->words as $fullTitle) {
                $results[$fullTitle] = true;
            }
        }
        foreach ($node->children as $childNode) {
            $this->collectWords($childNode, $results);
        }
    }

    public function searchByFuzzyWord(string $word): array
    {
        $results = [];
        $maxDist = strlen($word) > 4 ? 2 : 1;
        $visited = [];

        // Cek setiap node kata yang sudah dimasukkan ke trie
        $this->collectFuzzyMatches($this->root, '', $word, $maxDist, $results, $visited);

        return array_keys($results);
    }

    private function collectFuzzyMatches(TrieNode $node, string $currentWord, string $target, int $maxDist, array &$results, array &$visited)
    {
        if ($node->isEndOfWord) {
            $dist = levenshtein($currentWord, $target);
            if ($dist <= $maxDist) {
                foreach ($node->words as $title) {
                    $results[$title] = true;
                }
            }
        }

        foreach ($node->children as $char => $childNode) {
            $this->collectFuzzyMatches($childNode, $currentWord . $char, $target, $maxDist, $results, $visited);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pencarian Hybrid Final V2</title>
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
        <h5 class="text-center text-muted mb-4">Metode: Trie + Levenshtein Distance</h5>
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
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                list.style.display = 'none';
            }
        });
    </script>
</body>

</html>