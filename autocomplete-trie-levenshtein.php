<?php

// ===================================================================
// BAGIAN BACKEND PHP (LOGIKA PENCARIAN FILTER + ACTIVE WORD)
// ===================================================================

// --- KONFIGURASI DATABASE ---
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ubpress';
$dbTable = 'produk_buku';

// --------------------------

if (isset($_GET['qeury'])) {
    $startTime = microtime(true);

    $cacheFile = 'trie_cache_ubpress.dat';

    // Langkah 1: Cek file cache
    if (file_exists($cacheFile)) {
        // Jika ada, langsung load dari cache. Ini sangat cepat!
        $trie = unserialize(file_get_contents($cacheFile));
    } else {
        // Jika cache tidak ada, lakukan proses mahal sekali saja.
        $titles = getTitlesFromDB($dbHost, $dbUser, $dbPass, $dbName, $dbTable);
        if (isset($titles['error'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $titles['error']]);
            exit;
        }

        $trie = new Trie();
        foreach ($titles as $title) {
            $trie->insert($title);
        }

        // Langkah 2: Simpan Trie yang sudah jadi ke dalam file cache
        file_put_contents($cacheFile, serialize($trie));
    }

    // Dari sini ke bawah, proses pencarian tetap sama
    $query = strtolower(trim($_GET['query']));
    $queryWords = array_filter(preg_split('/\s+/', $query));

    $suggestions = [];

    if (!empty($queryWords)) {
        $lastWord = array_pop($queryWords);
        $completeWords = $queryWords;

        $maxDistanceForLastWord = strlen($lastWord) < 4 ? 1 : 2;
        $lastWordResults = $trie->search($lastWord, $maxDistanceForLastWord);

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

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    $response = ['suggestions' => array_slice($suggestions, 0, 500), 'time' => $responseTime];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function getTitlesFromDB($host, $user, $pass, $dbname, $table)
{
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        return ['error' => "Koneksi Gagal: " . $conn->connect_error];
    }
    $titles = [];
    $sql = "SELECT nama FROM `$table` WHERE nama IS NOT NULL AND nama != ''";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['nama'];
        }
    }
    $conn->close();
    return $titles;
}

class TrieNode
{
    public $children = [];
    public $isEndOfWord = false;
    public $words = [];
}

class Trie
{
    private $root;
    private $stopWords = ['dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'dalam'];

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    public function insert(string $fullTitle): void
    {
        $words = preg_split('/\s+/', preg_replace("/[^A-Za-z0-9\s]/", ' ', $fullTitle));

        foreach ($words as $word) {
            $wordLower = strtolower($word);
            if (strlen($wordLower) > 1 && !in_array($wordLower, $this->stopWords)) {
                $node = $this->root;
                for ($i = 0; $i < strlen($wordLower); $i++) {
                    $char = $wordLower[$i];
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

    public function search(string $query, int $maxDistance): array
    {
        $currentRow = range(0, strlen($query));
        $results = [];

        foreach ($this->root->children as $char => $node) {
            $this->searchRecursive($node, $char, $query, $currentRow, $results, $maxDistance);
        }

        $finalResults = array_values($results);

        usort($finalResults, function ($a, $b) {
            if ($a['dist'] == $b['dist']) {
                return 0;
            }
            return ($a['dist'] < $b['dist']) ? -1 : 1;
        });

        return array_map(function ($r) {
            return $r['word'];
        }, $finalResults);
    }

    private function searchRecursive(TrieNode $node, string $char, string $query, array $previousRow, array &$results, int $maxDistance): void
    {
        $queryLen = strlen($query);
        $currentRow = [$previousRow[0] + 1];

        for ($i = 1; $i <= $queryLen; $i++) {
            $insertCost = $currentRow[$i - 1] + 1;
            $deleteCost = $previousRow[$i] + 1;
            $replaceCost = ($query[$i - 1] === $char) ? $previousRow[$i - 1] : $previousRow[$i - 1] + 1;
            $currentRow[$i] = min($insertCost, $deleteCost, $replaceCost);
        }

        if ($currentRow[$queryLen] <= $maxDistance && $node->isEndOfWord) {
            foreach ($node->words as $fullTitle) {
                // Simpan juga jaraknya untuk sorting nanti
                if (!isset($results[$fullTitle]) || $results[$fullTitle]['dist'] > $currentRow[$queryLen]) {
                    $results[$fullTitle] = ['word' => $fullTitle, 'dist' => $currentRow[$queryLen]];
                }
            }
        }

        if (min($currentRow) <= $maxDistance) {
            foreach ($node->children as $nextChar => $childNode) {
                $this->searchRecursive($childNode, $nextChar, $query, $currentRow, $results, $maxDistance);
            }
        }
    }
}

// ===================================================================
// BAGIAN FRONTEND (BOOTSTRAP 5)
// ===================================================================
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Katalog Pustaka (Trie + Levenshtein)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }

        #suggestions-list {
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        const searchInput = document.getElementById('search-input');
        const suggestionsList = document.getElementById('suggestions-list');
        const responseTimeDiv = document.getElementById('response-time');
        let searchTimeout;

        searchInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value;

            // PERUBAHAN: Dikembalikan ke 2 karakter untuk memulai pencarian
            if (query.length < 1) {
                suggestionsList.innerHTML = '';
                suggestionsList.style.display = 'none';
                responseTimeDiv.textContent = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`autocomplete-trie-levenshtein.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            responseTimeDiv.textContent = `Error: ${data.error}`;
                            return;
                        }

                        suggestionsList.innerHTML = '';
                        if (data.suggestions.length > 0) {
                            data.suggestions.forEach(suggestion => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.textContent = suggestion;
                                li.style.cursor = 'pointer';
                                li.onclick = () => {
                                    searchInput.value = suggestion;
                                    suggestionsList.style.display = 'none';
                                };
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.style.display = 'block';
                            responseTimeDiv.textContent = `Ditemukan dalam ${data.time} ms`;
                        } else {
                            suggestionsList.style.display = 'none';
                            responseTimeDiv.textContent = `Tidak ada hasil untuk "${query}" (waktu: ${data.time} ms)`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        responseTimeDiv.textContent = 'Terjadi kesalahan saat mencari.';
                    });
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                suggestionsList.style.display = 'none';
            }
        });
    </script>
</body>

</html>