<?php

// ===================================================================
// BAGIAN BACKEND PHP (MURNI TRIE, TANPA LEVENSHTEIN)
// ===================================================================

// --- KONFIGURASI DATABASE ---
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'csv_db 9';
$dbTable = 'booksdatasetclean___copy';
// --------------------------

if (isset($_GET['query'])) {
    $startTime = microtime(true);

    $cacheFile = 'trie_cache_pure.dat';

    if (file_exists($cacheFile)) {
        $trie = unserialize(file_get_contents($cacheFile));
    } else {
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

        file_put_contents($cacheFile, serialize($trie));
    }

    $query = strtolower(trim($_GET['query']));
    $queryWords = array_filter(preg_split('/\s+/', $query));

    $suggestions = [];

    if (!empty($queryWords)) {
        $lastWord = array_pop($queryWords);
        $completeWords = $queryWords;

        // Langkah 1: Cari semua judul yang katanya dimulai dengan kata terakhir (prefix search)
        $lastWordResults = $trie->search($lastWord);

        // Langkah 2: Saring hasilnya dengan kata-kata filter sebelumnya
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
    $sql = "SELECT judul FROM `$table` WHERE judul IS NOT NULL AND judul != ''";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $titles[] = $row['judul'];
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

// =============================================================
// ### PERUBAHAN KUNCI: KELAS TRIE SEKARANG MURNI PREFIX SEARCH ###
// =============================================================
class Trie
{
    private $root;
    private $stopWords = ['the', 'a', 'an', 'of', 'in', 'and', 'to', 'for', 'is', 'on', 'by'];

    public function __construct()
    {
        $this->root = new TrieNode();
    }

    // Fungsi insert tidak berubah
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

    /**
     * FUNGSI SEARCH BARU: Murni pencarian prefix, tanpa Levenshtein.
     * @param string $prefix
     * @return array
     */
    public function search(string $prefix): array
    {
        $node = $this->root;
        $prefixLower = strtolower($prefix);

        // 1. Navigasi ke node terakhir dari prefix
        for ($i = 0; $i < strlen($prefixLower); $i++) {
            $char = $prefixLower[$i];
            if (!isset($node->children[$char])) {
                // Jika prefix tidak ada di Trie, tidak ada hasil.
                return [];
            }
            $node = $node->children[$char];
        }

        // 2. Kumpulkan semua kata dari node tersebut ke bawah
        $results = [];
        $this->collectWords($node, $results);

        // Gunakan array_keys untuk mendapatkan judul unik
        return array_keys($results);
    }

    /**
     * FUNGSI HELPER BARU: Mengumpulkan semua judul dari sebuah node secara rekursif.
     * @param TrieNode $node
     * @param array $results
     */
    private function collectWords(TrieNode $node, array &$results): void
    {
        // Jika node ini menandai akhir dari sebuah kata yang diindeks
        if ($node->isEndOfWord) {
            foreach ($node->words as $fullTitle) {
                // Gunakan judul sebagai key untuk memastikan hasil yang unik
                $results[$fullTitle] = true;
            }
        }

        // Lanjutkan ke semua anak dari node ini (Depth First Search)
        foreach ($node->children as $childNode) {
            $this->collectWords($childNode, $results);
        }
    }
}

// ===================================================================
// BAGIAN FRONTEND (BOOTSTRAP 5 - Tidak ada perubahan)
// ===================================================================
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Katalog Pustaka (Trie Murni)</title>
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
            <div class="col-md-8 mx-auto">
                <h1 class="text-center mb-4">📚 Pencarian Katalog Pustaka 📚</h1>
                <h5 class="text-center text-muted mb-4">Metode: Trie</h5>

                <div class="position-relative">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0" placeholder="Cari judul buku (pencocokan awal kata)..." autocomplete="off">
                    </div>
                    <ul id="suggestions-list" class="list-group position-absolute w-100 shadow" style="display: none;"></ul>
                </div>
                <div id="response-time" class="form-text text-center mt-2"></div>
            </div>
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

            if (query.length < 2) {
                suggestionsList.innerHTML = '';
                suggestionsList.style.display = 'none';
                responseTimeDiv.textContent = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`trie-only.php?query=${encodeURIComponent(query)}`)
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