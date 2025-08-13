<?php
require "koneksi.php";

$queryKategoriBerita = mysqli_query($con, "SELECT * FROM kategori_berita");

// PAGINATION SETUP START
$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
// PAGINATION SETUP END

// CEK APAKAH ADA PARAMETER KATEGORI
if (isset($_GET['kategori'])) {
    $kategoriNama = mysqli_real_escape_string($con, $_GET['kategori']);
    $kategori = mysqli_query($con, "SELECT id FROM kategori_berita WHERE nama='$kategoriNama'");
    $kategoriData = mysqli_fetch_array($kategori);
    $kategoriId = $kategoriData['id'];

    $totalData = mysqli_num_rows(mysqli_query($con, "SELECT id FROM produk_berita WHERE kategori_id='$kategoriId'"));
    $queryBerita = mysqli_query(
        $con,
        "SELECT a.*, b.nama AS nama_kategori FROM produk_berita a JOIN kategori_berita b ON a.kategori_id=b.id WHERE a.kategori_id='$kategoriId' ORDER BY a.id DESC LIMIT $start, $limit"
    );
} else {
    $totalData = mysqli_num_rows(mysqli_query($con, "SELECT id FROM produk_berita"));
    $queryBerita = mysqli_query(
        $con,
        "SELECT a.*, b.nama AS nama_kategori 
         FROM produk_berita a 
         JOIN kategori_berita b ON a.kategori_id=b.id 
         ORDER BY a.id DESC 
         LIMIT $start, $limit"
    );
}

$totalPage = ceil($totalData / $limit);

$countData = mysqli_num_rows($queryBerita);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi</title>
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

    <link rel="stylesheet" href="css/informasi.css">
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
            <h6 class="mb-5 line-pertama fs-6 fs-md-5 fs-lg-4">Informasi Penting Terbaru Kami</h6>
            <h1 class="mb-5 display-5 line-kedua fs-3 fs-md-2">KUMPULAN INFORMASI</h1>
            <p class="mb-5 line-ketiga fs-6 fs-md-5 fs-lg-4">Jelajahi berbagai berita dan informasi terkini dari Universitas Bakrie Press, <br> Dapatkan informasi terkini! </p>

        </div>
    </section>

    <!-- JUMBOTRON HERO SECTION END -->


    <!-- LIST INFORMASI SECTION START -->
    <section class="section-news-list py-5">
        <div class="container">
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-3 mb-4">
                    <h5 class="fw-bold mb-3">Kategori Berita</h5>
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
                                        <a href="informasi.php" class="kategori-link d-block py-1 px-2">
                                            <li><strong>Semua Kategori</strong></li>
                                        </a>

                                        <?php while ($kategoriBerita = mysqli_fetch_array($queryKategoriBerita)) { ?>
                                            <a href="informasi.php?kategori=<?= urlencode($kategoriBerita['nama']); ?>" class="kategori-link d-block py-1 px-2">
                                                <li><?= htmlspecialchars($kategoriBerita['nama']); ?></li>
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
                    <h5 class="fw-bold mb-4">Berita Terkini</h5>
                    <div class="row g-4">
                        <?php if ($countData < 1) { ?>
                            <div class="col-12">
                                <div class="text-center p-5 bg-light rounded shadow-sm">
                                    <i class="bi bi-newspaper display-4 text-secondary mb-3"></i>
                                    <h4 class="fw-semibold text-secondary">Berita Tidak Tersedia</h4>
                                    <p class="text-muted">Belum ada berita dalam kategori ini untuk saat ini.</p>
                                </div>
                            </div>
                        <?php }
                        ?>
                        <!-- Card Berita -->
                        <?php $jumlah = 0; ?>

                        <?php while ($berita = mysqli_fetch_array($queryBerita)) { ?>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $jumlah * 100 ?>">
                                <div class="card shadow-sm border-0">
                                    <div class="ratio ratio-16x9 bg-light rounded overflow-hidden">
                                        <a href="detail-berita.php?nama=<?= urlencode($berita['nama']) ?>">
                                            <img src="cover-berita/<?php echo $berita['foto']; ?>" alt="" class="w-100 h-100 object-fit-cover">
                                        </a>
                                    </div>
                                    <div class="card-body">

                                        <a href="detail-berita.php?nama=<?= urlencode($berita['nama']) ?>"
                                            class="stretched-link d-block mb-1 link-judul-berita"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            title="<?= htmlspecialchars(ucwords(strtolower($berita['nama']))); ?>">
                                            <?= htmlspecialchars(mb_strimwidth(ucwords(strtolower($berita['nama'])), 0, 30, "...")); ?>
                                        </a>


                                        <small class="text-muted"><?php echo $berita['nama_kategori']; ?></small>
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
                                            <a class="page-link" href="informasi.php?page=<?php echo $i; ?><?php echo isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : ''; ?>">
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
    <!-- LIST INFORMASI SECTION END -->

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

    <!-- TOOLTIPS JUDUL BERITA START -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        });
    </script>

    <!-- TOOLTIPS JUDUL BERITA END -->

    <script src="js-aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>

</body>

</html>