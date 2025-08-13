<?php
require "koneksi.php";

$nama = htmlspecialchars($_GET['nama']);
$queryProdukBerita = mysqli_query($con, "SELECT produk_berita.*, kategori_berita.nama AS nama_kategori FROM produk_berita JOIN kategori_berita ON produk_berita.kategori_id = kategori_berita.id WHERE produk_berita.nama = '$nama'");
$produkBerita = mysqli_fetch_array($queryProdukBerita);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?php echo $produkBerita['nama']; ?> </title>
    <link rel="icon" href="image/favicon.png" type="image/png">


    <!-- FONT START -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- FONT END -->

    <!-- BOOTSTRAP & CSS START -->
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"> <!-- UNTUK ICON -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet"> <!-- UNTUK ANIMATION -->

    <link rel="stylesheet" href="css/detail-berita.css">
    <!-- BOOTSTRAP & CSS END -->

</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require 'navbar.php';
    ?>
    <!-- REQUIRE NAVBAR END -->

    <!-- DETAIL BERITA START -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <!-- Gambar Berita -->
            <div class="rounded overflow-hidden mb-4 shadow-sm">
                <img src="cover-berita/<?php echo $produkBerita['foto']; ?>" alt="Gambar Berita" class="img-fluid w-100">
            </div>

            <!-- Judul -->
            <h1 class="fw-bold mb-3">
                <?php echo $produkBerita['nama']; ?>
            </h1>

            <!-- Info Tanggal dan Kategori -->
            <div class="d-flex flex-wrap text-muted mb-3">
                <div class="me-3"><i class="bi bi-calendar-event me-1"></i> <?php echo date('d F Y', strtotime($produkBerita['tanggal'])); ?></div>
                <div><i class="bi bi-folder2-open me-1"></i> <?php echo $produkBerita['nama_kategori']; ?></div>
            </div>

            <!-- Isi Berita -->
            <div class=" bg-white rounded shadow-sm">
                <p style="text-align: justify; line-height: 1.8;" class="">
                    <?php echo nl2br($produkBerita['detail']); ?>
                </p>
            </div>
        </div>
    </div>
    <!-- DETAIL BERITA END -->


    <!-- REQUIRE FOOTER COPYRIGHT START -->
    <?php
    require 'footer.php'
    ?>
    <!-- REQUIRE FOOTER COPYRIGHT END -->



    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>