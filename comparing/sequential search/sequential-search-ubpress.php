<?php
// ===================================================================
// BAGIAN BACKEND PHP (LOGIKA SEQUENTIAL SEARCH) - VERSI FINAL
// ===================================================================

// --- KONFIGURASI DATABASE (SESUAIKAN JIKA PERLU) ---
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ubpress';
$dbTable = 'produk_buku';

// ----------------------------------------------------

// Hanya jalankan logika PHP jika ada parameter 'query' dari JavaScript
if (isset($_GET['query'])) {
    $startTime = microtime(true);

    // 1. Ambil semua judul dari database. Ini adalah inti dari Sequential Search,
    //    yaitu memproses seluruh data setiap kali ada permintaan.
    $titles = getTitlesFromDB($dbHost, $dbUser, $dbPass, $dbName, $dbTable);
    if (isset($titles['error'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $titles['error']]);
        exit;
    }

    $query = strtolower(trim($_GET['query']));
    $suggestions = [];

    // 2. Lakukan Sequential Search jika query tidak kosong
    if (!empty($query)) {
        // Loop melalui setiap judul buku satu per satu
        foreach ($titles as $title) {
            // Cek apakah query yang diketik terkandung di dalam judul buku.
            // stripos() digunakan untuk pencarian yang tidak case-sensitive (tidak peduli huruf besar/kecil).
            // Inilah logika utama Sequential Search untuk autocomplete.
            if (stripos($title, $query) !== false) {
                // Jika cocok, tambahkan judul buku ke dalam array hasil
                $suggestions[] = $title;
            }
        }
    }

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);

    // 3. Kirim hasil dalam format JSON kembali ke JavaScript
    $response = ['suggestions' => array_slice($suggestions, 0, 500), 'time' => $responseTime];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Fungsi untuk mengambil semua judul dari database.
 */
function getTitlesFromDB($host, $user, $pass, $dbname, $table)
{
    mysqli_report(MYSQLI_REPORT_OFF);
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

// ===================================================================
// BAGIAN FRONTEND (HTML & JAVASCRIPT)
// ===================================================================
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Katalog Pustaka (Sequential Search)</title>
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
                <h5 class="text-center text-muted mb-4">Metode: Sequential Search</h5>
                <div class="position-relative">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" class="form-control border-start-0" placeholder="Cari judul buku..." autocomplete="off">
                    </div>
                    <ul id="suggestions-list" class="list-group position-absolute w-100 shadow" style="display: none;"></ul>
                </div>
                <div id="response-time" class="form-text text-center mt-2"></div>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search-input');
        const suggestionsList = document.getElementById('suggestions-list');
        const responseTimeDiv = document.getElementById('response-time');
        // Variabel searchTimeout tidak diperlukan lagi
        // let searchTimeout; 

        searchInput.addEventListener('keyup', () => {
            // clearTimeout(searchTimeout); // Ini juga tidak diperlukan lagi
            const query = searchInput.value;

            if (query.length < 2) {
                suggestionsList.innerHTML = '';
                suggestionsList.style.display = 'none';
                responseTimeDiv.textContent = '';
                return;
            }

            // TANPA JEDA: Langsung panggil fetch setiap kali event 'keyup' terjadi
            fetch(`sequential-search-ubpress.php?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
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
                    responseTimeDiv.textContent = 'Terjadi kesalahan saat mencari. Cek console log.';
                });
        });

        // Menutup daftar saran jika klik di luar area pencarian
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.position-relative')) {
                suggestionsList.style.display = 'none';
            }
        });
    </script>
</body>

</html>