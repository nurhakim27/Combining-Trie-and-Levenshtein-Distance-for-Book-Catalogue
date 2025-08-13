<?php
require "koneksi.php";

$nama = htmlspecialchars($_GET['nama']);
$queryProdukBuku = mysqli_query($con, "SELECT produk_buku.*, kategori_buku.nama AS nama_kategori FROM produk_buku JOIN kategori_buku ON produk_buku.kategori_id = kategori_buku.id WHERE produk_buku.nama = '$nama'");
$produkBuku = mysqli_fetch_array($queryProdukBuku);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?php echo $produkBuku['nama'] ?> </title>
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

    <link rel="stylesheet" href="css/detail-produk.css">
    <!-- BOOTSTRAP & CSS END -->
</head>

<body>
    <!-- REQUIRE NAVBAR START -->
    <?php
    require 'navbar.php';
    ?>
    <!-- REQUIRE NAVBAR END -->

    <!-- DETAIL PRODUK START -->
    <div class="container mt-5 mb-5">
        <div class="row g-4"> <!-- g-4 = gutter spacing antar kolom -->
            <!-- Kolom kiri: Gambar -->
            <div class="col-md-4">
                <img src="cover-buku/<?php echo $produkBuku['cover']; ?>" alt="Cover Buku" class="img-fluid mb-3">
            </div>

            <!-- Kolom kanan: Info buku -->
            <div class="col-md-8">
                <h3 class="fw-bold">
                    <?php echo $produkBuku['nama'] ?>
                </h3>
                <p class="fs-4 fw-bold mt-3">
                    Rp <?php echo number_format($produkBuku['harga'], 0, ',', '.') ?>
                </p> <!-- harga diperbesar & bold -->

                <table class="table mt-4"> <!-- Jarak antara harga dan tabel -->
                    <tbody>
                        <tr>
                            <td><strong>ISBN</strong></td>
                            <td> <?php echo $produkBuku['isbn'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Kategori</strong></td>
                            <td><?php echo $produkBuku['nama_kategori'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Penulis</strong></td>
                            <td><?php echo $produkBuku['penulis'] ?></td>
                        </tr>

                        <tr>
                            <td><strong>Tahun Terbit</strong></td>
                            <td><?php echo $produkBuku['tahun'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Bahasa</strong></td>
                            <td><?php echo $produkBuku['bahasa'] ?></td>
                        </tr>
                    </tbody>
                </table>

                <a href="<?php echo $produkBuku['akses'] ?>" class="btn btn-akses mt-3">Akses</a>
            </div>

        </div>
        <div class="row mt-5"> <!-- SINOPSIS SECTION -->
            <div class="col-12">
                <h4 class="fw-bold ">Sinopsis</h4>
                <p class="mt-3">
                    <?php echo $produkBuku['sinopsis'] ?>
                </p>
            </div>
        </div>
    </div>

    <!-- DETAIL PRODUK END -->

    <!-- SINOPSIS START -->

    <!-- REQUIRE FOOTER COPYRIGHT START -->
    <?php
    require 'footer.php'
    ?>
    <!-- REQUIRE FOOTER COPYRIGHT END -->



    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>