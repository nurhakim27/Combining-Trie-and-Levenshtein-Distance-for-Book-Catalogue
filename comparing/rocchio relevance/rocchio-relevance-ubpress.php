<?php
// ===================================================================
// BACKEND PHP: ROCCHIO RELEVANCE FEEDBACK IMPLEMENTATION
// ===================================================================

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ubpress';
$dbTable = 'produk_buku';


if (isset($_GET['query'])) {
    $startTime = microtime(true);
    $cacheFile = 'rocchio_cache_ubpress.dat';

    if (file_exists($cacheFile)) {
        $engine = unserialize(file_get_contents($cacheFile));
    } else {
        $titles = getTitlesFromDB($dbHost, $dbUser, $dbPass, $dbName, $dbTable);
        if (isset($titles['error'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $titles['error']]);
            exit;
        }
        $engine = new RocchioSearchEngine($titles);
        file_put_contents($cacheFile, serialize($engine));
    }

    $query = strtolower(trim($_GET['query']));
    $suggestions = $engine->searchWithRocchio($query);

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    $response = ['suggestions' => array_slice($suggestions, 0, 20), 'time' => $responseTime];
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

class RocchioSearchEngine
{
    private $vocabulary = [];
    private $documents = [];
    private $vectors = [];

    public function __construct(array $docs)
    {
        $this->documents = $docs;
        $this->buildVectorSpaceModel();
    }

    private function tokenize($text)
    {
        preg_match_all('/\\b\\w+\\b/', strtolower($text), $matches);
        return $matches[0];
    }

    private function buildVectorSpaceModel()
    {
        $allWords = [];
        $docWords = [];
        foreach ($this->documents as $id => $doc) {
            $words = $this->tokenize($doc);
            $docWords[$id] = array_count_values($words);
            $allWords = array_merge($allWords, $words);
        }
        $this->vocabulary = array_values(array_unique($allWords));

        foreach ($this->documents as $id => $doc) {
            $vector = [];
            foreach ($this->vocabulary as $word) {
                $vector[] = $docWords[$id][$word] ?? 0;
            }
            $this->vectors[$id] = $vector;
        }
    }

    public function searchWithRocchio($query, $alpha = 1.0, $beta = 0.75, $gamma = 0.15)
    {
        $queryWords = array_count_values($this->tokenize($query));
        $originalQueryVector = [];
        foreach ($this->vocabulary as $word) {
            $originalQueryVector[] = $queryWords[$word] ?? 0;
        }

        // Hitung kemiripan awal
        $similarities = [];
        foreach ($this->vectors as $id => $docVector) {
            $similarities[$id] = $this->cosineSimilarity($originalQueryVector, $docVector);
        }

        arsort($similarities);
        $relevant = array_slice(array_keys($similarities), 0, 5);
        $irrelevant = array_slice(array_keys($similarities), -5);

        // Bangun Rocchio-modified vector
        $modifiedQuery = [];
        for ($i = 0; $i < count($this->vocabulary); $i++) {
            $val = $alpha * $originalQueryVector[$i];
            foreach ($relevant as $r) {
                $val += ($beta / count($relevant)) * $this->vectors[$r][$i];
            }
            foreach ($irrelevant as $ir) {
                $val -= ($gamma / count($irrelevant)) * $this->vectors[$ir][$i];
            }
            $modifiedQuery[$i] = $val;
        }

        $finalScores = [];
        foreach ($this->vectors as $id => $vec) {
            $finalScores[$id] = $this->cosineSimilarity($modifiedQuery, $vec);
        }

        arsort($finalScores);
        $results = [];
        foreach ($finalScores as $id => $score) {
            if ($score > 0) $results[] = $this->documents[$id];
        }
        return $results;
    }

    private function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0;
        $magA = 0;
        $magB = 0;
        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $magA += $vecA[$i] ** 2;
            $magB += $vecB[$i] ** 2;
        }
        if ($magA == 0 || $magB == 0) return 0;
        return $dotProduct / (sqrt($magA) * sqrt($magB));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Katalog (Rocchio)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <h5 class="text-center text-muted mb-4">Metode: Rocchio Relevance</h5>

                <div class="position-relative">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg></span>
                        <input type="text" id="search-input" class="form-control border-start-0" placeholder="Cari judul buku..." autocomplete="off">
                    </div>
                    <ul id="suggestions-list" class="list-group position-absolute w-100 shadow" style="display: none;"></ul>
                </div>
                <div id="response-time" class="form-text text-center mt-2"></div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
                // SESUAIKAN NAMA FILE INI MENJADI NAMA FILE ANDA SAAT INI
                fetch(`rocchio-relevance-ubpress.php?query=${encodeURIComponent(query)}`)
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
                    }).catch(error => {
                        console.error('Error:', error);
                        responseTimeDiv.textContent = 'Terjadi kesalahan.';
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