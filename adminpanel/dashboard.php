<?php
require "session.php";
require "../koneksi.php";

// MENGAMBIL JUMLAH KATEGORI BUKU
$queryKategoriBuku = mysqli_query($con, "SELECT * FROM kategori_buku");
$jumlahKategoriBuku = mysqli_num_rows($queryKategoriBuku);

// MENGAMBIL JUMLAH KATEGORI BERITA
$queryKategoriBerita = mysqli_query($con, "SELECT * FROM kategori_berita");
$jumlahKategoriBerita = mysqli_num_rows($queryKategoriBerita);

// MENGAMBIL JUMLAH PRODUK BUKU
$queryProdukBuku = mysqli_query($con, "SELECT * FROM produk_buku");
$jumlahProdukBuku = mysqli_num_rows($queryProdukBuku);

// MENGAMBIL JUMLAH INFORMASI BERITA
$queryProdukBerita = mysqli_query($con, "SELECT * FROM produk_berita");
$jumlahProdukBerita = mysqli_num_rows($queryProdukBerita);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/fontawesome.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

    <?php
    require "navbarAdmin.php";
    ?>
    <h2 class="text-center fw-bold mb-4 mt-5">Halo <?php echo $_SESSION['username']; ?></h2>

    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page"><i class="bi bi-house-door-fill"></i> Home</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-content">
                        <div class="icon-placeholder">
                            <i class="bi bi-bookmark-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Kategori Buku</h5>
                            <p><?php echo $jumlahKategoriBuku; ?> Buku</p>
                            <a href="kategori-buku.php" style="text-decoration: none; color: white;"><button class="btn btn-secondary btn-sm">Detail</button></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-content">
                        <div class="icon-placeholder">
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Buku</h5>
                            <p><?php echo $jumlahProdukBuku; ?> Buku</p>
                            <a href="produk-buku.php" style="text-decoration: none; color: white;"><button class="btn btn-secondary btn-sm">Detail</button></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-content">
                        <div class="icon-placeholder">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                        <div class="card-body">
                            <h5>Kategori Berita</h5>
                            <p><?php echo $jumlahKategoriBerita; ?> Berita</p>
                            <a href="kategori-berita.php" style="text-decoration: none; color: white;"><button class="btn btn-secondary btn-sm">Detail</button></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3">
                    <div class="card-content">
                        <div class="icon-placeholder">
                            <i class="bi bi-newspaper"></i>
                        </div>
                        <div class="card-body">
                            <h5>Berita</h5>
                            <p><?php echo $jumlahProdukBerita; ?> Berita</p>
                            <a href="informasi-berita.php" style="text-decoration: none; color: white;"><button class="btn btn-secondary btn-sm">Detail</button></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require "footerAdmin.php";
    ?>
    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="../fontawesome/js/all.min.js"></script>
</body>

</html>