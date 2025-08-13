<?php
require_once 'trieLD-v1ubpress.php';

// Inisialisasi judul uji
$testTitles = [
    "Panduan Membaca Buku untuk Semua Usia",
    "Cara Menulis Buku dengan Mudah",
    "Strategi Manajemen SDM Modern",
];

// Buat objek Trie dan masukkan data
$trie = new Trie();
foreach ($testTitles as $title) {
    $trie->insert($title);
}

// Akses properti privat root Trie
$refTrie = new ReflectionClass($trie);
$rootProp = $refTrie->getProperty('root');
$rootProp->setAccessible(true);
$rootNode = $rootProp->getValue($trie);

// Siapkan parameter untuk pengujian searchRecursive
$query = "buko"; // typo dari 'buku'
$maxDistance = 2;
$currentRow = range(0, strlen($query));
$results = [];

// Panggil searchRecursive secara langsung
$searchRecursiveMethod = $refTrie->getMethod('searchRecursive');
$searchRecursiveMethod->setAccessible(true);

foreach ($rootNode->children as $char => $childNode) {
    $searchRecursiveMethod->invokeArgs($trie, [
        $childNode,     // node saat ini
        $char,          // karakter awal
        $query,         // kata yang dicari
        $currentRow,    // array baris pertama
        &$results,      // hasil pencarian (akan diisi oleh referensi)
        $maxDistance    // batas toleransi kesalahan
    ]);
}

// Tampilkan hasil pengujian
echo "=== HASIL UJI searchRecursive() ===\n";
echo "Query typo: \"$query\"\n";
echo "Hasil ditemukan:\n";

if (empty($results)) {
    echo "❌ Tidak ada hasil ditemukan.\n";
} else {
    foreach ($results as $item) {
        echo "✅ " . $item['word'] . " (jarak: " . $item['dist'] . ")\n";
    }
}
