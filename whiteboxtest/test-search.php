<?php
require_once 'trieLD-v1ubpress.php';

// Daftar judul yang akan dimasukkan ke Trie
$testTitles = [
    "Panduan Membaca Buku untuk Semua Usia",
    "Cara Mudah Menulis Buku",
    "Buku Panduan Pemilu 2024: Edisi Pemilih Disabilitas",
    "Manajemen Sumber Daya Manusia",
    "Ali dan Lili"
];

// Buat objek Trie
$trie = new Trie();

// Insert semua judul ke dalam Trie
foreach ($testTitles as $title) {
    $trie->insert($title);
}

// Daftar query pencarian dan hasil yang diharapkan
$tests = [
    [
        "query" => "buku",
        "expected" => ["Panduan Membaca Buku untuk Semua Usia", "Cara Mudah Menulis Buku", "Buku Panduan Pemilu 2024: Edisi Pemilih Disabilitas"]
    ],
    [
        "query" => "manajemen",
        "expected" => ["Manajemen Sumber Daya Manusia"]
    ],
    [
        "query" => "ali",
        "expected" => ["Ali dan Lili"]
    ],
    [
        "query" => "xyz",
        "expected" => [] // Tidak ada hasil yang cocok
    ]
];

// Jalankan pengujian
echo "=== HASIL UJI search() ===\n";

foreach ($tests as $test) {
    $results = $trie->search($test['query'], 1);
    $matched = [];

    // Cek apakah hasil yang diharapkan muncul di dalam hasil pencarian
    foreach ($test['expected'] as $expectedTitle) {
        if (in_array($expectedTitle, $results)) {
            $matched[] = "✅";
        } else {
            $matched[] = "❌";
        }
    }

    echo "- Query: '{$test['query']}'\n";
    echo "  Diharapkan: " . json_encode($test['expected']) . "\n";
    echo "  Didapat   : " . json_encode($results) . "\n";
    echo "  Status    : " . implode(" ", $matched) . "\n\n";
}
