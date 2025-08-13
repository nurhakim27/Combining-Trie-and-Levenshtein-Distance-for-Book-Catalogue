<?php
require "koneksi.php";


$queryKategoriBuku = mysqli_query($con, "SELECT * FROM kategori_buku");

// PAGINATION SETUP START
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
// PAGINATION END


// CEK APAKAH ADA PARAMETER KATEGORI
if (isset($_GET['kategori'])) {
    $kategoriNama = $_GET['kategori'];
    $kategori = mysqli_query($con, "SELECT id FROM kategori_buku WHERE nama='$kategoriNama'");
    $kategoriData = mysqli_fetch_array($kategori);
    $kategoriId = $kategoriData['id'];

    $totalData = mysqli_num_rows(mysqli_query($con, "SELECT a.id FROM produk_buku a WHERE a.kategori_id='$kategoriId'"));
    $queryBuku = mysqli_query($con, "SELECT a.*, b.nama AS nama_kategori FROM produk_buku a JOIN kategori_buku b ON a.kategori_id=b.id WHERE a.kategori_id='$kategoriId' LIMIT $start, $limit");
} else {
    $totalData = mysqli_num_rows(mysqli_query($con, "SELECT id FROM produk_buku"));
    $queryBuku = mysqli_query($con, "SELECT a.*, b.nama AS nama_kategori FROM produk_buku a JOIN kategori_buku b ON a.kategori_id=b.id LIMIT $start, $limit");
}

$totalPage = ceil($totalData / $limit);

$countData = mysqli_num_rows($queryBuku);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Buku</title>
    <link rel="icon" href="image/favicon.png" type="image/png">

    <!-- FONT START -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- FONT END -->

    <!-- BOOTSTRAP & CSS START -->
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- UNTUK ICON -->
    <link href="css/aos.css" rel="stylesheet"> <!-- UNTUK ANIMATION -->

    <link rel="stylesheet" href="css/produk.css">
    <link rel="stylesheet" href="css/footer-detail.css">
    <!-- BOOTSTRAP & CSS END -->
</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require 'navbar.php';
    ?>
    <!-- REQUIRE NAVBAR END -->



    <!-- Jumbotron / Hero Section -->
    <section class="jumbotron-section">
        <div class="jumbotron-overlay"></div>
        <div class="jumbotron-content">
            <h6 class="mb-5 line-pertama fs-6 fs-md-5 fs-lg-4">Buku Sumber Ilmu Pengetahuan</h6>
            <h1 class="mb-5 display-5 line-kedua fs-3 fs-md-2 fs-lg-1">KOLEKSI BUKU</h1>
            <p class="mb-5 line-ketiga fs-6 fs-md-5 fs-lg-4">Jelajahi beragam kategori buku berkualitas dari Universitas Bakrie Press, <br>Temukan bacaan favoritmu! </p>

        </div>
    </section>

    <!-- JUMBOTRON HERO SECTION END -->

    <!-- LIST BUKU SECTION START -->
    <section class="section-book-list py-5">
        <div class="container">

            <div class="row">
                <!-- Kolom Kiri -->

                <div class="col-md-3 mb-4">

                    <!-- Search dipindahkan ke sini -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="fw-bold mb-3 text-left">Search Buku</h5>
                            <input type="text" id="search-input" class="form-control mb-2" placeholder="Cari Buku..." autocomplete="off">
                            <ul id="suggestions-list" class="list-group position-absolute shadow custom-search "></ul>
                            <div id="response-time" class="form-text text-muted text-center mt-1"></div>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Kategori Buku</h5>
                    <div class="accordion" id="kategoriAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <?php
                                $kategoriAktif = isset($_GET['kategori']) ? htmlspecialchars($_GET['kategori']) : 'Pilih Kategori';
                                ?>
                                <button class="accordion-button collapsed kategori-first" type="button" data-bs-toggle="collapse" data-bs-target="#kategoriCollapse">
                                    <?= $kategoriAktif ?>
                                </button>
                            </h2>
                            <div id="kategoriCollapse" class="accordion-collapse collapse" data-bs-parent="#kategoriAccordion">
                                <div class="accordion-body p-2">
                                    <ul class="list-unstyled mb-0">
                                        <a href="produk.php" class="kategori-link d-block py-1 px-2">
                                            <li><strong>Semua Kategori</strong></li>
                                        </a>
                                        <?php while ($kategoriBuku = mysqli_fetch_array($queryKategoriBuku)) { ?>
                                            <a href="produk.php?kategori=<?php echo $kategoriBuku['nama']; ?>" class="kategori-link d-block py-1 px-2">
                                                <li><?php echo $kategoriBuku['nama'] ?></li>
                                            </a>
                                        <?php
                                        }
                                        ?>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-9">
                    <h5 class="fw-bold mb-4">Produk Buku</h5>
                    <div class="row g-4">
                        <?php if ($countData < 1) { ?>
                            <div class="col-12">
                                <div class="text-center p-5 bg-light rounded shadow-sm">
                                    <i class="bi bi-newspaper display-4 text-secondary mb-3"></i>
                                    <h4 class="fw-semibold text-secondary">Buku Tidak Tersedia</h4>
                                    <p class="text-muted">Belum ada Buku dalam kategori ini untuk saat ini.</p>
                                </div>
                            </div>
                        <?php }
                        ?>

                        <!-- Card Buku -->
                        <?php $jumlah = 0; ?>

                        <?php while ($buku = mysqli_fetch_array($queryBuku)) { ?>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $jumlah * 100 ?>">
                                <div class="card shadow-sm border-0">
                                    <div class="ratio ratio-1x1 bg-light rounded">
                                        <img src="cover-buku/<?php echo $buku['cover']; ?>" alt="">
                                    </div>
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2"><?php echo $buku['nama_kategori']; ?></small>
                                        <a href="detail-produk.php?nama=<?php echo $buku['nama']; ?>" class="d-block mb-2 link-judul-custom">
                                            <?php echo mb_strimwidth($buku['nama'], 0, 30, "..."); ?>
                                        </a>
                                        <p class="text-muted">Rp<?php echo number_format($buku['harga'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                            </div>


                        <?php $jumlah++;
                        }
                        ?>
                        <div class="col-12 mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPage; $i++) { ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="produk.php?page=<?php echo $i; ?><?php echo isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- LIST BUKU SECTION END -->


    <!-- REQUIRE FOOTER DETAIL START -->
    <?php
    require 'footer-detail.php';
    ?>
    <!-- REQUIRE FOOTER DETAIL END -->

    <!-- REQUIRE FOOTER COPYRIGHT START -->
    <?php
    require 'footer.php'
    ?>
    <!-- REQUIRE FOOTER COPYRIGHT END -->



    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>

    <script src="js-aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>




    <!-- JAVASCRIPT UNTUK AUTOCOMPLETE SEARCH -->
    <script>
        const searchInput = document.getElementById('search-input');
        const suggestionsList = document.getElementById('suggestions-list');
        const responseTimeDiv = document.getElementById('response-time');
        let searchTimeout;

        searchInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value;

            // PERUBAHAN: Dikembalikan ke 2 karakter untuk memulai pencarian
            if (query.length < 2) {
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

    <!-- JAVASCRIPT UNTUK AUTOCOMPLETE END -->
</body>

</html>