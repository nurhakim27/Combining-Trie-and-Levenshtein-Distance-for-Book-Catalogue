<?php
require_once 'trieLD-v1ubpress.php';

// Data uji judul buku
$testTitles = [
    "Diplomasi Indonesia dalam Mendukung Kemerdekaan Palestina",
    "Ali dan Lili",
    "Statistik Pertanian Organik Indonesia",
    "Arunika: Bunga Rampai Ilmu Komunikasi Perilaku Komunikasi Gen Z",
    "Temukan Kekuatan dalam Tulisan"
];

// Buat objek Trie baru
$trie = new Trie();

// Masukkan setiap judul ke dalam Trie
foreach ($testTitles as $title) {
    $trie->insert($title);
}

// Fungsi bantu: periksa apakah kata dari judul tersimpan di dalam Trie
function testWordExistsInTrie($trie, $wordToCheck, $originalTitle)
{
    $node = new ReflectionProperty($trie, 'root');
    $node->setAccessible(true);
    $current = $node->getValue($trie);

    $word = strtolower($wordToCheck);
    for ($i = 0; $i < strlen($word); $i++) {
        $char = $word[$i];
        if (!isset($current->children[$char])) {
            return false;
        }
        $current = $current->children[$char];
    }

    return in_array($originalTitle, $current->words);
}

// Daftar kata yang ingin diuji dari judul
$tests = [
    ["kata" => "diplomasi", "judul" => $testTitles[0]],
    ["kata" => "ali",       "judul" => $testTitles[1]],
    ["kata" => "statistik", "judul" => $testTitles[2]],
    ["kata" => "komunikasi", "judul" => $testTitles[3]],
    ["kata" => "tulisan",   "judul" => $testTitles[4]],
];

// Jalankan uji satu per satu
echo "=== HASIL UJI insert() ===\n";
foreach ($tests as $test) {
    $result = testWordExistsInTrie($trie, $test["kata"], $test["judul"]);
    echo "Apakah kata '{$test['kata']}' dari judul '{$test['judul']}' berhasil disimpan? ";
    echo $result ? "✅ TRUE\n" : "❌ FALSE\n";
}
